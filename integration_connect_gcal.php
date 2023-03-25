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
					A.gcal_refresh_token,
					A.gcal_access_token,
					A.gcal_token_create_date  
				FROM 
					".MF_TABLE_PREFIX."integrations A LEFT JOIN ".MF_TABLE_PREFIX."forms B 
				  ON 
				  	A.form_id=B.form_id 
			   WHERE 
			   		gcal_linked_user_id=? AND B.form_active IN(0,1) 
			ORDER BY 
					A.gcal_token_create_date DESC 
			   LIMIT 1";
	$params = array($_SESSION['mf_user_id']);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	if(!empty($row['gcal_refresh_token'])){
		//use existing refresh token 
		if(!empty($mf_settings['googleapi_clientid']) && !empty($mf_settings['googleapi_clientsecret'])){
			$refresh_token 	   = $row['gcal_refresh_token'];
			$access_token 	   = $row['gcal_access_token'];
			$token_create_date = $row['gcal_token_create_date'];
			
			$google_api_userid = $_SESSION['mf_user_id'];
			
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
			$google_client->addScope(Google_Service_Calendar::CALENDAR);
			$google_client->setApplicationName('MachForm');
			$google_client->setRedirectUri($mf_settings['base_url'].'oauth_callback_google.php');
			$google_client->setState('calendar-'.$form_id.'-'.$_SESSION['mf_user_id']);

			$auth_url = $google_client->createAuthUrl();

			header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
			exit;
		}
	}

		
	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post integrations_settings" data-formid="<?php echo $form_id; ?>">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <a class="breadcrumb" href="integration_settings.php?id=<?php echo $form_id; ?>">Integrations</a> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Google Calendar</h2>
							<p>Connect your forms with Google Calendar</p>
						</div>	
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>

				<div class="content_body" style="text-align: center">
					<div id="integration_connect_gcal_body">
						<span class="icon-bubble-notification" style="font-size: 60px;color: #3b699f"></span>
						<h3 style="color: #3b699f;margin-bottom: 30px">First time connecting to Google Calendar?</h3>
						<p>In order to connect to Google Calendar, you'll need to <a class="blue_dotted" style="font-weight: bold" target="_blank" href="https://www.machform.com/howto-get-your-google-clientid-and-clientsecret">generate your Google API Client ID and Client Secret</a>.</p> 
						<p>Then go to your <a style="font-weight: bold" href="main_settings.php" class="blue_dotted">Settings</a> page and save your credentials there.</p>
						<p style="margin-top: 30px;margin-bottom: 10px">You must add the following as the <strong>Authorized redirect URIs</strong> when asked:</p>
						<div><input style="font-size: 14px;border-style: solid;border-width: 1px;padding: 5px;border-radius: 3px;" type="text" readonly="readonly" onclick="javascript: this.select()" class="element text medium" value="<?php echo $mf_settings['base_url'].'oauth_callback_google.php'; ?>"></div>
						<a href="integration_connect_gcal.php?id=<?php echo $form_id; ?>" class="bb_button bb_grey" style="margin-top: 35px"><span class="icon-spinner11"></span> Try Again</a>
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