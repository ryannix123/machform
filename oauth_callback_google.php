<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	require('includes/init.php');

	require('config.php');
	require('includes/db-core.php');
	require('includes/helper-functions.php');
	require('includes/check-session.php');
	
	require('includes/filter-functions.php');
	require('includes/users-functions.php');
	require('includes/entry-functions.php');
	require('lib/google-api-client/vendor/autoload.php');
	
	$dbh 		 = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	$auth_code	   = trim($_GET['code']);
	$error_message = trim($_GET['error']);
	$state_param   = trim($_GET['state']); 

	$ssl_suffix = mf_get_ssl_suffix();

	if(!empty($state_param)){
		//possible values 'sheets-xxxx-yyyy' or 'calendar-xxxx-yyyy' 
		//xxxx - form id, yyyy - user id
		$oauth_params = explode('-', $state_param); 
		
		$integration_type  = $oauth_params[0];
		$form_id 		   = (int) $oauth_params[1];
		$google_api_userid = $oauth_params[2]; 
	}

	if(!empty($error_message)){
		//if error message exist, redirect to integration page and display errors
		$_SESSION['MF_ERROR'] = "Unable to connect to Google ({$error_message}).";
		
		if($integration_type == 'sheets'){
			$_SESSION['MF_ERROR'] = "Unable to connect to Google Sheets ({$error_message}).";
		}else if($integration_type == 'calendar'){
			$_SESSION['MF_ERROR'] = "Unable to connect to Google Calendar ({$error_message}).";
		}

		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/integration_settings.php?id={$form_id}");
		exit;
	}else if(empty($auth_code)){
		die("Error! Missing code parameter.");
	}

	if(!empty($mf_settings['googleapi_clientid']) && !empty($mf_settings['googleapi_clientsecret'])){
		//get access/refresh token and save it
		$google_client = new Google_Client();

		$client_id 	   = trim($mf_settings['googleapi_clientid']);
		$client_secret = trim($mf_settings['googleapi_clientsecret']);

		$google_client->setClientId($client_id);
		$google_client->setClientSecret($client_secret);
		$google_client->setRedirectUri($mf_settings['base_url'].'oauth_callback_google.php');

		$response_token = $google_client->fetchAccessTokenWithAuthCode($auth_code);
		
		$refresh_token 	   = $response_token['refresh_token'];
		$access_token 	   = $response_token['access_token'];
		$token_create_date = date('Y-m-d H:i:s',$response_token['created']);

		if($integration_type == 'sheets'){
			//if refresh_token is missing, find it in existing records within ap_integrations table, for this specific user_id
			//since google only send refresh_token when the user granted the permission for the first time
			if(empty($refresh_token)){
				$query = "SELECT 
								gsheet_refresh_token 
							FROM 
								".MF_TABLE_PREFIX."integrations 
						   WHERE 
						   		gsheet_linked_user_id = ? AND 
						   		gsheet_refresh_token is not null AND 
						   		gsheet_refresh_token <> '' 
						   LIMIT 1";
				$params = array($google_api_userid);
					
				$sth = mf_do_query($query,$params,$dbh);
				$row = mf_do_fetch_result($sth);

				$refresh_token = $row['gsheet_refresh_token'];

				//if no records found, the user need to remove access to MachForm from Google account first
				if(empty($refresh_token)){
					$_SESSION['MF_ERROR'] = "Unable to get refresh_token. Remove access to MachForm from your <a href=\"https://myaccount.google.com/u/1/permissions\" target=\"_blank\">Google account</a> and try again.";
			
					header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/integration_settings.php?id={$form_id}");
					exit;
				}
			}

			//get form name
			$query = "SELECT form_name FROM ".MF_TABLE_PREFIX."forms WHERE form_id = ?";
			
			$params = array($form_id);
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);

			$form_name = $row['form_name'];
			if(empty($form_name)){
				$form_name = '-Untitled Form- (#'.$form_id.')';
			}

			//create the spreadsheet using the access token and get spreadsheet id
			$google_client->setAccessType("offline");
			$google_client->setIncludeGrantedScopes(true);  
			$google_client->addScope(Google_Service_Sheets::SPREADSHEETS);
			$google_client->setAccessToken($response_token);

			$google_service = new Google_Service_Sheets($google_client);
			$request_body = new Google_Service_Sheets_Spreadsheet();

			$spreadsheet_properties = new Google_Service_Sheets_SpreadsheetProperties();
			$spreadsheet_properties->setTitle($form_name);
			$request_body->setProperties($spreadsheet_properties);

			$sheet_properties = new Google_Service_Sheets_Sheet([
				                      'properties' => [
				                          'sheetId' => 0,
				                          'title' => 'Sheet1'
				                      ]
				                    ]);
			$request_body->setSheets($sheet_properties);

			$spreadsheet_response = $google_service->spreadsheets->create($request_body);
			
			$spreadsheet_id  = $spreadsheet_response->getSpreadsheetId();
			$spreadsheet_url = $spreadsheet_response-> getSpreadsheetUrl();

			//build the sheet header row
			//get column headers first for all fields
			$columns_meta  = mf_get_simple_columns_meta($dbh,$form_id);
			$columns_label = $columns_meta['name_lookup'];

			$form_properties = mf_get_form_properties($dbh,$form_id,array('payment_enable_merchant','form_resume_enable','form_approval_enable','payment_merchant_type'));
		
			//if payment enabled, add ap_form_payments columns into $columns_label
			if($form_properties['payment_enable_merchant'] == 1 && $form_properties['payment_merchant_type'] != 'check'){
				$columns_label['payment_amount'] = 'Payment Amount';
				$columns_label['payment_status'] = 'Payment Status';
				$columns_label['payment_id']	 = 'Payment ID';
			}

			//if approval workflow enabled, add Approval Status into $columns_label
			if($form_properties['form_approval_enable'] == 1){
				$columns_label['approval_status'] = 'Approval Status';
			}

			$sheet_header_labels = array();
			foreach ($columns_label as $column_name) {
				$sheet_header_labels[] = $column_name;
			}
			
			$sheet_row_values = [$sheet_header_labels];
			$request_body = new Google_Service_Sheets_ValueRange([
			    'values' => $sheet_row_values
			]);

			$request_params = [
								'valueInputOption' => 'RAW'
					  		  ];
			$sheet_range = 'Sheet1';

			$google_service->spreadsheets_values->append($spreadsheet_id, $sheet_range, $request_body, $request_params);

			//change the header font to bold and freeze the header row
			$google_requests = [
					    new Google_Service_Sheets_Request([
					        'repeatCell' => [
					            'range' => [
					                'sheetId' => 0,
					                'startRowIndex' => 0,
					                'endRowIndex' => 1
					             ],
					            'cell' => [
					              'userEnteredFormat' => [
					                  'backgroundColor' => [
					                    'red' => 1.0,
					                    'green' => 1.0,
					                    'blue' => 1.0
					                  ],
					                  'horizontalAlignment' => 'LEFT',
					                  'textFormat' => [
					                    'foregroundColor' => [
					                      'red' => 0.0,
					                      'green' => 0.0,
					                      'blue' => 0.0
					                    ],
					                    'fontSize' => 10,
					                    'bold' => true
					                  ]
					              ]
					            ],
					            'fields' => 'userEnteredFormat(backgroundColor,textFormat,horizontalAlignment)'
					        ]
					    ]),
					    new Google_Service_Sheets_Request([
					        'updateSheetProperties' => [
					            'properties' => [
					                'sheetId' => 0,
					                'gridProperties' => [
					                  'frozenRowCount' => 1
					                ] 
					            ],
					            'fields' => 'gridProperties.frozenRowCount'
					        ]
					    ])
					];

			$batch_update_request = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
				'requests' => $google_requests
			]);

			$google_service->spreadsheets->batchUpdate($spreadsheet_id, $batch_update_request);
			//end adding column headers to the first sheet

			//check for entry within ap_integrations table first
			//if exist, just update the record. otherwise, insert new record
			$integration_record_exist = false;

			$query = "select count(*) total_row from `".MF_TABLE_PREFIX."integrations` where `form_id`=?";
			$params = array($form_id);
					
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);

			if(!empty($row['total_row'])){
				$integration_record_exist = true;
			}

			if($integration_record_exist == true){
				//update the record
				$query = "UPDATE 
								".MF_TABLE_PREFIX."integrations 
							 SET 
							 	`gsheet_integration_status`=1, 
								`gsheet_spreadsheet_id`=?, 
								`gsheet_spreadsheet_url`=?, 
								`gsheet_elements`='', 
								`gsheet_create_new_sheet`=0, 
								`gsheet_refresh_token`=?, 
								`gsheet_access_token`=?, 
								`gsheet_token_create_date`=?,
								`gsheet_linked_user_id`=? 
						WHERE form_id = ?;";
				$params = array($spreadsheet_id,$spreadsheet_url,$refresh_token,$access_token,$token_create_date,$google_api_userid,$form_id);
				mf_do_query($query,$params,$dbh);
			}else{
				//insert new record
				$query = "INSERT INTO `".MF_TABLE_PREFIX."integrations` 
									( 
									 `form_id`, 
									 `gsheet_integration_status`, 
									 `gsheet_spreadsheet_id`, 
									 `gsheet_spreadsheet_url`, 
									 `gsheet_elements`, 
									 `gsheet_create_new_sheet`, 
									 `gsheet_refresh_token`, 
									 `gsheet_access_token`, 
									 `gsheet_token_create_date`,
									 `gsheet_linked_user_id`
									) VALUES (?,?,?,?,?,?,?,?,?,?)";
				
				$params = array($form_id,
								1, 
								$spreadsheet_id, 
								$spreadsheet_url, 
								'', 
								0, 
								$refresh_token, 
								$access_token, 
								$token_create_date,
								$google_api_userid);
					
				mf_do_query($query,$params,$dbh);
			}

			//redirect to success page
			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/integration_gsheets.php?id={$form_id}&success=1");
			exit;
		}elseif($integration_type == 'calendar'){
			//if refresh_token is missing, find it in existing records within ap_integrations table, for this specific user_id
			//since google only send refresh_token when the user granted the permission for the first time
			if(empty($refresh_token)){
				$query = "SELECT 
								gcal_refresh_token 
							FROM 
								".MF_TABLE_PREFIX."integrations 
						   WHERE 
						   		gcal_linked_user_id = ? AND 
						   		gcal_refresh_token is not null AND 
						   		gcal_refresh_token <> '' 
						   LIMIT 1";
				$params = array($google_api_userid);
					
				$sth = mf_do_query($query,$params,$dbh);
				$row = mf_do_fetch_result($sth);

				$refresh_token = $row['gcal_refresh_token'];

				//if no records found, the user need to remove access to MachForm from Google account first
				if(empty($refresh_token)){
					$_SESSION['MF_ERROR'] = "Unable to get refresh_token. Remove access to MachForm from your <a href=\"https://myaccount.google.com/u/1/permissions\" target=\"_blank\">Google account</a> and try again.";
			
					header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/integration_settings.php?id={$form_id}");
					exit;
				}
			}

	
			//check for entry within ap_integrations table first
			//if exist, just update the record. otherwise, insert new record
			$integration_record_exist = false;

			$query = "select count(*) total_row from `".MF_TABLE_PREFIX."integrations` where `form_id`=?";
			$params = array($form_id);
					
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);

			if(!empty($row['total_row'])){
				$integration_record_exist = true;
			}

			if($integration_record_exist == true){
				//update the record
				$query = "UPDATE 
								".MF_TABLE_PREFIX."integrations 
							 SET 
							 	`gcal_integration_status`=1,
							 	`gcal_calendar_id`='primary', 
								`gcal_refresh_token`=?, 
								`gcal_access_token`=?, 
								`gcal_token_create_date`=?,
								`gcal_linked_user_id`=? 
						WHERE form_id = ?;";
				$params = array($refresh_token,$access_token,$token_create_date,$google_api_userid,$form_id);
				mf_do_query($query,$params,$dbh);
			}else{
				//insert new record
				$query = "INSERT INTO `".MF_TABLE_PREFIX."integrations` 
									( 
									 `form_id`, 
									 `gcal_integration_status`,
									 `gcal_calendar_id`,  
									 `gcal_refresh_token`, 
									 `gcal_access_token`, 
									 `gcal_token_create_date`,
									 `gcal_linked_user_id`
									) VALUES (?,?,?,?,?,?,?)";
				
				$params = array($form_id,
								1, 
								'primary', 
								$refresh_token, 
								$access_token, 
								$token_create_date,
								$google_api_userid);
					
				mf_do_query($query,$params,$dbh);
			}

			//redirect to success page
			$_SESSION['MF_SUCCESS'] = "Save your calendar settings below to complete.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/integration_gcal.php?id={$form_id}&success=1");
			exit;
		}

	}
   
?>