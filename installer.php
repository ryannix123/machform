<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	define('NEW_MACHFORM_VERSION', '18.1');

	require('includes/init.php');

	require('config.php');
	require('includes/db-core.php');
	require('lib/password-hash.php');
	require('includes/setup-functions.php');

	$mf_version_tag = '?'.substr(md5(NEW_MACHFORM_VERSION),-6);
	
	//1. Check PHP version
	if(version_compare(PHP_VERSION,"7.2",">=")){
		$is_php_version_passed = true;
	}else{
		$is_php_version_passed = false;
		$pre_install_error = 'php_version_insufficient';
	}

	if($is_php_version_passed){
		//2. Check connection to Database
		try {
			  $dbh = new PDO('mysql:host='.MF_DB_HOST.';dbname='.MF_DB_NAME, MF_DB_USER, MF_DB_PASSWORD,
			  				 array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true)
			  				 );
			  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			  $dbh->query("SET NAMES utf8");
			  $dbh->query("SET sql_mode = ''");
		} catch(PDOException $e) {
			  $error_connecting =  "Error connecting to the database: ".$e->getMessage();
			  $pre_install_error = $error_connecting;
		}

		//3. Check MySQL version
		$params = array();
		if(empty($error_connecting)){
			$query = "select version() mysql_version_number";
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				echo "Check version failed: ".$e->getMessage();
			}
			
			$row = $sth->fetch(PDO::FETCH_ASSOC);
			$current_mysql_version = $row['mysql_version_number'];

			if(version_compare($current_mysql_version,"5.0","<")){
				$error_mysql_version = "Your current MySQL version ({$current_mysql_version}) is less than the minimum requirement (5.0)";
				$pre_install_error = $error_mysql_version;
			}

			//4. Check for existing MachForm installation
			if(empty($error_mysql_version)){
				$is_machform_installed = true;

				try{
					$query = "select count(*) from ".MF_TABLE_PREFIX."forms";
					$sth = $dbh->prepare($query);
					
					$sth->execute($params);
				}catch(PDOException $e) {
					$is_machform_installed = false;
				}

				if($is_machform_installed){
					$pre_install_error = 'machform_already_installed';
				}else{
					//5. Check the "data" folder
					if(!is_writable('./data')){
						$pre_install_error = 'data_dir_unwritable';
					}
				}
			}
		}
	}

	$installation_success = false;
	$installation_has_error = false;
	$is_invalid_admin_email = false;
	$is_invalid_license_key = false;

	$admin_username = '';
	$license_key = '';
	
	if(empty($pre_install_error) && !empty($_POST['run_install'])){

		$admin_username = isset($_POST['admin_username']) ? trim($_POST['admin_username']) : '';
		$license_key    = isset($_POST['license_key']) ? trim($_POST['license_key']) : '';
		
		//do the installation here
		//check the email address first
		$email_regex  = '/^[A-z0-9][\+\w.\'-]*@[A-z0-9][\w\-\.]*\.[A-z0-9]{2,}$/';
		$regex_result = preg_match($email_regex, $admin_username);
			
		if(empty($regex_result) || empty($admin_username)){
			$is_invalid_admin_email = true;
		}else{
			$is_invalid_admin_email = false;
		}

		//check license key
		if(empty($license_key)){
			$is_invalid_license_key = true;
		}else{
			$v_final_key = str_replace('-', '', $license_key);
			$v_key_hash  = substr($v_final_key, -4);
			$v_license_seed_hash = md5(substr($v_final_key,0,16));
			$v_license_seed_hash_4 = strtoupper(substr($v_license_seed_hash, -4));

			if($v_license_seed_hash_4 == $v_key_hash){
				$is_invalid_license_key = false;
			}else{
				$is_invalid_license_key = true;
			}

		}

		if(!$is_invalid_admin_email && !$is_invalid_license_key){
			//do installation tasks here
			$post_install_error = '';

			//1. Create ap_forms table
			$post_install_error .= create_ap_forms_table($dbh);

			//2. Create ap_column_preferences table
			$post_install_error .= create_ap_column_preferences_table($dbh);

			//3. Create ap_element_options table
			$post_install_error .= create_ap_element_options_table($dbh);

			//4. Create ap_element_prices table
			$post_install_error .= create_ap_element_prices_table($dbh);

			//5. Create ap_form_elements table
			$post_install_error .= create_ap_form_elements_table($dbh);
			
			//6. Create ap_form_filters table
			$post_install_error .= create_ap_form_filters_table($dbh);

			//7. Create ap_form_themes table
			$post_install_error .= create_ap_form_themes_table($dbh);

			//8. Insert into ap_form_themes table
			$post_install_error .= populate_ap_form_themes_table($dbh);
			
			//9. Create ap_fonts table
			$post_install_error .= create_ap_fonts_table($dbh);

			//10. Insert into ap_fonts table
			$post_install_error .= populate_ap_fonts_table($dbh);			

			
			//11. Create ap_settings table
			$post_install_error .= create_ap_settings_table($dbh);

			//12. Insert into ap_settings table
			$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
			$default_from_email = "no-reply@{$domain}";

			if(!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != 'off')){
				$ssl_suffix = 's';
			}else if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'){
			    $ssl_suffix = 's';
			}else if (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && $_SERVER['HTTP_FRONT_END_HTTPS'] == 'on'){
			    $ssl_suffix = 's';
			}else{
				$ssl_suffix = '';
			}
			
			$machform_base_url = 'http'.$ssl_suffix.'://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/';
			$upload_dir 	   = pathinfo($_SERVER['SCRIPT_FILENAME'],PATHINFO_DIRNAME).DIRECTORY_SEPARATOR.'data';

			$options = array();

			$options['default_from_email'] 	 = $default_from_email;
			$options['machform_base_url']  	 = $machform_base_url;
			$options['new_machform_version'] = NEW_MACHFORM_VERSION;
			$options['license_key'] 		 = $license_key;
			$options['upload_dir'] 		 	 = $upload_dir;

			$post_install_error .= populate_ap_settings_table($dbh,$options);

			//13. Create ap_users table
			$post_install_error .= create_ap_users_table($dbh);

			//14. Insert into ap_users table
			$hasher = new PasswordHash(8, FALSE);
			$default_password_hash = $hasher->HashPassword('machform');

			$options = array();
			
			$options['admin_username'] = $admin_username;
			$options['default_password_hash'] = $default_password_hash;

			$post_install_error .= populate_ap_users_table($dbh,$options);

			//15. Create ap_permissions table
			$post_install_error .= create_ap_permissions_table($dbh);

			//16. Create ap_entries_preferences table
			$post_install_error .= create_ap_entries_preferences_table($dbh);

			//17. Create ap_form_locks table
			$post_install_error .= create_ap_form_locks_table($dbh);

			//18. Create ap_form_sorts table
			$post_install_error .= create_ap_form_sorts_table($dbh);
			
			//19. Create ap_field_logic_elements table
			$post_install_error .= create_ap_field_logic_elements_table($dbh);

			//20. Create ap_field_logic_conditions table
			$post_install_error .= create_ap_field_logic_conditions_table($dbh);

			//21. Create ap_form_payments table
			$post_install_error .= create_ap_form_payments_table($dbh);

			//22. Create ap_page_logic table
			$post_install_error .= create_ap_page_logic_table($dbh);

			//22. Create ap_page_logic_conditions table
			$post_install_error .= create_ap_page_logic_conditions_table($dbh);

			//23. Create ap_email_logic table
			$post_install_error .= create_ap_email_logic_table($dbh);

			//24. Create ap_email_logic_conditions table
			$post_install_error .= create_ap_email_logic_conditions_table($dbh);

			//25. Create ap_webhook_options table
			$post_install_error .= create_ap_webhook_options_table($dbh);

			//26. Create ap_webhook_parameters table
			$post_install_error .= create_ap_webhook_parameters_table($dbh);

			//27. Create ap_webhook_logic_conditions table
			$post_install_error .= create_ap_webhook_logic_conditions_table($dbh);

			//28. Create ap_reports table
			$post_install_error .= create_ap_reports_table($dbh);

			//29. Create ap_report_elements table
			$post_install_error .= create_ap_report_elements_table($dbh);

			//30. Create ap_report_filters table
			$post_install_error .= create_ap_report_filters_table($dbh);

			//31. Create ap_grid_columns table
			$post_install_error .= create_ap_grid_columns_table($dbh);

			//32. Create ap_success_logic_conditions table
			$post_install_error .= create_ap_success_logic_conditions_table($dbh);

			//32. Create ap_success_logic_conditions table
			$post_install_error .= create_ap_success_logic_options_table($dbh);

			//33. Create ap_form_themes_files table
			$post_install_error .= create_ap_form_themes_files_table($dbh);

			//34. Create ap_approval_settings table
			$post_install_error .= create_ap_approval_settings_table($dbh);

			//35. Create ap_approvers table
			$post_install_error .= create_ap_approvers_table($dbh);

			//36. Create ap_approvers_conditions table
			$post_install_error .= create_ap_approvers_conditions_table($dbh);

			//38. Create ap_form_images_files table
			$post_install_error .= create_ap_form_images_files_table($dbh);

			//38. Create ap_integrations table
			$post_install_error .= create_ap_integrations_table($dbh);

			//39. Create ap_form_pdf_files table
			$post_install_error .= create_ap_form_pdf_files_table($dbh);

			//40. Create ap_folders table
			$post_install_error .= create_ap_folders_table($dbh);

			//41. Insert into ap_folders table
			$post_install_error .= populate_ap_folders_table($dbh);	

			//42. Create ap_folders_conditions table
			$post_install_error .= create_ap_folders_conditions_table($dbh);

			//43. Create ap_form_stats table
			$post_install_error .= create_ap_form_stats_table($dbh);

			//44. Create ap_sessions table
			$post_install_error .= create_ap_sessions_table($dbh);

			//check for errors ------------------------
			if(empty($post_install_error)){
				$installation_success = true;
			}else{
				$installation_has_error = true;
			}

			//Create "themes" folder
			if(is_writable("./data") && $installation_success){
				$old_mask = umask(0);
				mkdir("./data/themes",0755);
				umask($old_mask);
			}
			
		}
	}

	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>MachForm Installer</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="index, nofollow" />
<link rel="stylesheet" type="text/css" href="css/main.css<?php echo $mf_version_tag; ?>" media="screen" />   
    
<!--[if IE 7]>
	<link rel="stylesheet" type="text/css" href="css/ie7.css" media="screen" />
<![endif]-->
	
<!--[if IE 8]>
	<link rel="stylesheet" type="text/css" href="css/ie8.css" media="screen" />
<![endif]-->

<!--[if IE 9]>
	<link rel="stylesheet" type="text/css" href="css/ie9.css" media="screen" />
<![endif]-->
   
<link href="css/theme.css<?php echo $mf_version_tag; ?>" rel="stylesheet" type="text/css" />
<link href="css/bb_buttons.css<?php echo $mf_version_tag; ?>" rel="stylesheet" type="text/css" />
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css<?php echo $mf_version_tag; ?>" rel="stylesheet" />
<link type="text/css" href="css/edit_form.css<?php echo $mf_version_tag; ?>" rel="stylesheet" />
<link type="text/css" href="js/datepick/smoothness.datepick.css<?php echo $mf_version_tag; ?>" rel="stylesheet" />
<link href="css/override.css<?php echo $mf_version_tag; ?>" rel="stylesheet" type="text/css" />
</head>

<body>

<div id="bg" class="installer_page">

<div id="container">

	<div id="header">
	
		<div id="logo">
			<img class="title" src="images/machform_logo_vibrant.png" style="margin-left: 8px" width="158" alt="MachForm" />
		</div>	

		
		<div class="clear"></div>
		
	</div>
	<div id="main">
	
 
		<div id="content">
			<div class="post installer">

				<div style="padding-top: 10px">
					
					<?php if(empty($pre_install_error)){ ?>

					<?php 	if($installation_success){ ?>
								<div>
									<img src="images/icons/62_green_96.png" align="absmiddle" style="width: 48px; height: 48px;float: left;padding-right: 10px;padding-top: 10px"/>
									<h3>Success!</h3>
									<p>You have completed the installation.</p>
									<div style="clear:both; border-bottom: 1px dotted #CCCCCC;margin-top: 15px"></div>
								</div>
								
								<div style="margin-top: 10px;margin-bottom: 10px">
									<form id="form_installer" class="appnitro"  method="post" action="index.php">
										<ul>
											<li>
												<p>Below is your MachForm login information:</p>
												<p style="margin-top: 10px; margin-bottom: 20px">Email: <b><?php echo htmlspecialchars($admin_username); ?></b><br/>
												   Password: <b>machform</b></p>
												<p>Please change your password after logging in!</p>
											</li>
								    		<li id="li_submit" class="buttons" style="overflow: auto">
										    	<button type="submit" class="bb_button bb_green" id="submit_button" name="submit_button" style="float: left;border-radius: 4px">
											        <span class="icon-keyhole"></span>
											        Login to MachForm
											    </button>
											</li>
										</ul>
									</form>
								</div>	
					<?php	}else if($installation_has_error){ //if server meet the requirements but error during install (error while creating tables) ?>
								<div>
									<img src="images/icons/warning.png" align="absmiddle" style="width: 48px; height: 48px;float: left;padding-right: 10px;padding-top: 10px"/>
									<h3>Error During Installation!</h3>
									<p>Please fix the error below and try again.</p>
									<div style="clear:both; border-bottom: 1px dotted #CCCCCC;margin-top: 15px"></div>
								</div>
								
								<div style="margin-top: 10px;margin-bottom: 10px">
									<form id="form_installer" class="appnitro"  method="post" action="">
										<ul>
											<li id="li_installer_notification">
												<h5><?php echo $post_install_error; ?></h5>	
											</li>
											<li>
												Make sure that the database user is having enough privileges to create and alter tables.
											</li>
								    		<li id="li_submit" class="buttons" style="overflow: auto">
										    	<button type="submit" class="bb_button bb_green" id="submit_button" name="submit_button" style="float: left;border-radius: 4px">
											        <span class="icon-checkmark"></span>
											        Try Again
											    </button>
											</li>
										</ul>
									</form>
								</div>
					<?php   }else{ ?>
								<div>
									<img src="images/icons/advancedsettings.png" align="absmiddle" style="width: 48px; height: 48px;float: left;padding-right: 10px;padding-top: 10px"/>
									<h3>MachForm Ready to Install</h3>
									<p>Please fill the form below and click the install button.</p>
									<div style="clear:both; border-bottom: 1px dotted #CCCCCC;margin-top: 15px"></div>
								</div>
								
								<div style="margin-top: 10px;margin-bottom: 10px">
									<form id="form_installer" class="appnitro"  method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
										<ul>
											<?php if($is_invalid_admin_email){ ?>
												<li id="li_installer_notification">
													<h5>Error! Please enter valid email address!</h5>
												</li>
											<?php }else if($is_invalid_license_key){ ?>
												<li id="li_installer_notification">
													<h5>Error! Please enter valid License Number!</h5>
												</li>
											<?php } ?>

											<li id="li_email_address" style="margin-top: 25px;">		
												<label class="desc" for="admin_username">Your Email Address</label>
												<div>
													<input id="admin_username" name="admin_username" class="element text large" type="text" maxlength="255" value="<?php echo htmlspecialchars($admin_username); ?>"/>
													<span style="font-size: 85%;color: #444444">You will use this to login to the admin panel.</span> 
												</div>
											</li>
											<li id="li_license_key" style="margin-bottom: 10px">		
												<label class="desc" for="license_key">License Number</label>
												<div>
													<input id="license_key" name="license_key" class="element text large" type="text" maxlength="255" value="<?php echo htmlspecialchars($license_key); ?>"/> 
												</div>
											</li>		
								    		<li id="li_submit" class="buttons" style="overflow: auto">
								    			<input type="hidden" name="run_install" id="run_install" value="1">
										    	<button type="submit" class="bb_button bb_green" id="submit_button" name="submit_button" style="float: left;border-radius: 4px">
											        <span class="icon-checkmark"></span>
											        Install MachForm
											    </button>
											</li>
										</ul>
									</form>
								</div>	

					<?php   }
								
						
						 }else{ //else if there are pre install error 
							if($pre_install_error == 'php_version_insufficient' || $pre_install_error == 'data_dir_unwritable'){
								if($pre_install_error == 'php_version_insufficient'){
									$pre_install_error = "Your current PHP version (".PHP_VERSION.") is less than the minimum requirement (7.2.0)";
								}else{
									$pre_install_error = "The <strong><u>data</u></strong> folder under your machform folder is not writable. Please set the permission to writable (CHMOD 777)";
								}
					?>
								<div>
									<img src="images/icons/warning.png" align="absmiddle" style="width: 48px; height: 48px;float: left;padding-right: 10px;padding-top: 10px"/>
									<h3>Error! Unable to Install</h3>
									<p>Please fix the error below to continue.</p>
									<div style="clear:both; border-bottom: 1px dotted #CCCCCC;margin-top: 15px"></div>
								</div>
								
								<div style="margin-top: 10px;margin-bottom: 10px">
									<form id="form_installer" class="appnitro"  method="post" action="">
										<ul>
											<li id="li_installer_notification">
												<h5><?php echo $pre_install_error; ?></h5>	
											</li>
								    		<li id="li_submit" class="buttons" style="overflow: auto">
										    	<button type="submit" class="bb_button bb_green" id="submit_button" name="submit_button" style="float: left;border-radius: 4px">
											        <span class="icon-checkmark"></span> 
											        Check Again
											    </button>
											</li>
										</ul>
									</form>
								</div>	
					<?php	}else if($pre_install_error == 'machform_already_installed'){
					?>
								<div>
									<img src="images/icons/warning.png" align="absmiddle" style="width: 48px; height: 48px;float: left;padding-right: 10px;padding-top: 10px"/>
									<h3>MachForm Already Installed</h3>
									<p>Please login to the admin panel below.</p>
									<div style="clear:both; border-bottom: 1px dotted #CCCCCC;margin-top: 15px"></div>
								</div>
								
								<div style="margin-top: 10px;margin-bottom: 10px">
									<form id="form_installer" class="appnitro"  method="post" action="index.php">
										<ul>
											<li id="li_installer_notification">
												<h5>Your MachForm already installed and ready.</h5><h5>You can login to the admin panel to create/edit your forms.</h5>	
											</li>
								    		<li id="li_submit" class="buttons" style="overflow: auto">
										    	<button type="submit" class="bb_button bb_green" id="submit_button" name="submit_button" style="float: left;border-radius: 4px">
											        <span class="icon-keyhole"></span>
											        Login to MachForm
											    </button>
											</li>
										</ul>
									</form>
								</div>	

					<?php		
							}else{ //error connecting to database
					?>
								<div>
									<img src="images/icons/warning.png" align="absmiddle" style="width: 48px; height: 48px;float: left;padding-right: 10px;padding-top: 10px"/>
									<h3>Error Connecting to Database</h3>
									<p>Please fix the error below to continue.</p>
									<div style="clear:both; border-bottom: 1px dotted #CCCCCC;margin-top: 15px"></div>
								</div>
								
								<div style="margin-top: 10px;margin-bottom: 10px">
									<form id="form_installer" class="appnitro"  method="post" action="">
										<ul>
											<li id="li_installer_notification">
												<h5><?php echo $pre_install_error; ?></h5>
											</li>
											<li style="font-family: Arial, Helvetica, sans-serif">
												<p>There are few things you can try to fix this issue:
													<ul style="list-style-type:disc;">
														<li style="margin-left: 20px;padding-left: 0px;padding-bottom: 0px">Make sure you have the correct database username and password</li>
														<li style="margin-left: 20px;padding-left: 0px">Make sure you have the correct database hostname</li>
													</ul>
												</p>
												<p style="margin-top: 15px">If the problem persist, please <a style="text-decoration: underlin" href="http://www.appnitro.com/support/index.php?pg=request" target="_blank">contact us</a> and we'll be happy to help you!</p>	
											</li>
								    		<li id="li_submit" class="buttons" style="overflow: auto">
										    	<button type="submit" class="bb_button bb_green" id="submit_button" name="submit_button" style="float: left;border-radius: 4px">
											        <span class="icon-checkmark"></span>
											        Check Again
											    </button>
											</li>
										</ul>
									</form>
								</div>	


					<?php	}	
 						  } //end - else if there are pre install error
					?>
					
					
				</div>
     
        	</div>  		 
		</div>
	
		

<?php
	$footer_data =<<<EOT
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.core.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.widget.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.tabs.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.mouse.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.sortable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.draggable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.position.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.dialog.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.effects.core.js{$mf_version_tag}"></script>
EOT;
	require('includes/footer.php'); 
?>