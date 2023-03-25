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
	require('includes/entry-functions.php');
	require('includes/users-functions.php');
	require('lib/google-api-client/vendor/autoload.php');
	
	$form_id = (int) trim($_REQUEST['id']);
	
	$dbh = mf_connect_db();
	$ssl_suffix = mf_get_ssl_suffix();
	
	$mf_settings = mf_get_settings($dbh);
	$mf_properties = mf_get_form_properties($dbh,$form_id,array('form_active'));
	
	
	//check inactive form, inactive form settings should not displayed
	if(empty($mf_properties) || $mf_properties['form_active'] == null){
		$_SESSION['MF_DENIED'] = "This is not valid URL.";

		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
		exit;
	}else{
		$form_active = (int) $mf_properties['form_active'];
	
		if($form_active !== 0 && $form_active !== 1){
			$_SESSION['MF_DENIED'] = "This is not valid URL.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_form permission
		if(empty($user_perms['edit_form'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to edit this form.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
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

	//check if there is an existing refresh token for this user or not
	//if exist, use the existing refresh token, otherwise redirect to google and request permission from user
	$query = "SELECT 
					A.gsheet_refresh_token,
					A.gsheet_access_token,
					A.gsheet_token_create_date  
				FROM 
					".MF_TABLE_PREFIX."integrations A LEFT JOIN ".MF_TABLE_PREFIX."forms B 
				  ON 
				  	A.form_id=B.form_id 
			   WHERE 
			   		gsheet_linked_user_id=? AND B.form_active IN(0,1) 
			ORDER BY 
					A.gsheet_token_create_date DESC 
			   LIMIT 1";
	$params = array($_SESSION['mf_user_id']);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	if(!empty($row['gsheet_refresh_token'])){
		//use existing refresh token and create spreadsheet
		if(!empty($mf_settings['googleapi_clientid']) && !empty($mf_settings['googleapi_clientsecret'])){
			$refresh_token 	   = $row['gsheet_refresh_token'];
			$access_token 	   = $row['gsheet_access_token'];
			$token_create_date = $row['gsheet_token_create_date'];
			
			$google_api_userid = $_SESSION['mf_user_id'];
			
			$client_id 	   = trim($mf_settings['googleapi_clientid']);
			$client_secret = trim($mf_settings['googleapi_clientsecret']);

			$response_token = array();
			$response_token['access_token']  = $access_token;
			$response_token['token_type'] 	 = 'Bearer';
			$response_token['expires_in'] 	 = 3600;
			$response_token['refresh_token'] = $refresh_token;
			$response_token['scope'] 		 = 'https://www.googleapis.com/auth/spreadsheets';
			$response_token['created'] 		 = 0;

			$google_client = new Google_Client();

			$google_client->setAccessToken($response_token);
			$google_client->setAccessType("offline");
			$google_client->setIncludeGrantedScopes(true);  
			$google_client->addScope(Google_Service_Sheets::SPREADSHEETS);
			$google_client->setClientId($client_id);
			$google_client->setClientSecret($client_secret);


			if($google_client->isAccessTokenExpired()) {
			    $new_access_token = $google_client->fetchAccessTokenWithRefreshToken($google_client->getRefreshToken());
			    $access_token = $new_access_token['access_token'];
			}

			//create the spreadsheet using the access token and get spreadsheet id
		
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

			//start adding column headers to the first sheet 
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
		}
	}else{
		//redirect to google oauth page
		if(!empty($mf_settings['googleapi_clientid']) && !empty($mf_settings['googleapi_clientsecret'])){
			
			$google_client = new Google_Client();

			$client_id 	   = trim($mf_settings['googleapi_clientid']);
			$client_secret = trim($mf_settings['googleapi_clientsecret']);

			$google_client->setClientId($client_id);
			$google_client->setClientSecret($client_secret);

			$google_client->setAccessType("offline");
			$google_client->setIncludeGrantedScopes(true);  
			$google_client->addScope(Google_Service_Sheets::SPREADSHEETS);
			$google_client->setApplicationName('MachForm');
			$google_client->setRedirectUri($mf_settings['base_url'].'oauth_callback_google.php');
			$google_client->setState('sheets-'.$form_id.'-'.$_SESSION['mf_user_id']);

			$auth_url = $google_client->createAuthUrl();

			header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
			exit;
		}
	}

	//get form name
	$query 	= "select 
					 form_name
			     from 
			     	 ".MF_TABLE_PREFIX."forms 
			    where 
			    	 form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row)){
		$row['form_name'] = mf_trim_max_length($row['form_name'],50);	
		$form_name = htmlspecialchars($row['form_name']);
	}

		
	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post integrations_settings" data-formid="<?php echo $form_id; ?>">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <a class="breadcrumb" href="integration_settings.php?id=<?php echo $form_id; ?>">Integrations</a> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Google Sheets</h2>
							<p>Connect your forms with Google Sheets</p>
						</div>	
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>

				<div class="content_body" style="text-align: center">
					<div id="integration_connect_gsheet_body">
						<span class="icon-bubble-notification" style="font-size: 60px;color: #3b699f"></span>
						<h3 style="color: #3b699f;margin-bottom: 30px">First time connecting to Google Sheets?</h3>
						<p>In order to connect to Google Sheets, you'll need to <a class="blue_dotted" style="font-weight: bold" target="_blank" href="https://www.machform.com/howto-get-your-google-clientid-and-clientsecret">generate your Google API Client ID and Client Secret</a>.</p> 
						<p>Then go to your <a style="font-weight: bold" href="main_settings.php" class="blue_dotted">Settings</a> page and save your credentials there.</p>
						<p style="margin-top: 30px;margin-bottom: 10px">You must add the following as the <strong>Authorized redirect URIs</strong> when asked:</p>
						<div><input style="font-size: 14px;border-style: solid;border-width: 1px;padding: 5px;border-radius: 3px;" type="text" readonly="readonly" onclick="javascript: this.select()" class="element text medium" value="<?php echo $mf_settings['base_url'].'oauth_callback_google.php'; ?>"></div>
						<a href="integration_connect_gsheets.php?id=<?php echo $form_id; ?>" class="bb_button bb_grey" style="margin-top: 35px"><span class="icon-spinner11"></span> Try Again</a>
					</div>
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

 
<?php
	$footer_data =<<<EOT
<script type="text/javascript">
	$(function(){
		{$jquery_data_code}		
    });
</script>
EOT;

	require('includes/footer.php'); 
?>