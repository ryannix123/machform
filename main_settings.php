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
	
	$dbh = mf_connect_db();

	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);

	//check user privileges, is this user has privilege to administer MachForm?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$_SESSION['MF_DENIED'] = "You don't have permission to administer MachForm.";

		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
		exit;
	}

	//handle form submission if there is any
	if(!empty($_POST['submit_form'])){

		$admin_theme 			= $_POST['admin_theme'] ?? 0;
		$smtp_enable 			= $_POST['smtp_enable'] ?? 0;
		$smtp_host 	 			= $_POST['smtp_host'] ?? '';
		$smtp_auth   			= $_POST['smtp_auth'] ?? 0;
		$smtp_secure 			= $_POST['smtp_secure'] ?? 0;
		$smtp_username 			= $_POST['smtp_username'] ?? '';
		$smtp_password 			= $_POST['smtp_password'] ?? '';
		$smtp_port 	   			= $_POST['smtp_port'] ?? '';
		$admin_image_url   		= $_POST['admin_image_url'] ?? '';
		$base_url   			= $_POST['base_url'] ?? '';
		$timezone   			= $_POST['timezone'] ?? '';
		$default_from_name   	= $_POST['default_from_name'] ?? '';
		$default_from_email   	= $_POST['default_from_email'] ?? '';
		$upload_dir   			= $_POST['upload_dir'] ?? '';
		$form_manager_max_rows  = $_POST['form_manager_max_rows'] ?? 0;
		$disable_machform_link  = $_POST['disable_machform_link'] ?? 0;
		$disable_pdf_link  		= $_POST['disable_pdf_link'] ?? 0;
		$enforce_tsv  			= $_POST['enforce_tsv'] ?? 0;
		$enable_ip_restriction  = $_POST['enable_ip_restriction'] ?? 0;
		$ip_whitelist  			= $_POST['ip_whitelist'] ?? '';
		$recaptcha_site_key   	= trim($_POST['recaptcha_site_key'] ?? '');
		$recaptcha_secret_key   = trim($_POST['recaptcha_secret_key'] ?? '');
		$googleapi_clientid   	= trim($_POST['googleapi_clientid'] ?? '');
		$googleapi_clientsecret = trim($_POST['googleapi_clientsecret'] ?? '');
		$enable_account_locking		= $_POST['enable_account_locking'] ?? 0;
		$account_lock_period	    = (int) $_POST['account_lock_period'] ?? 0;
		$account_lock_max_attempts	= (int) $_POST['account_lock_max_attempts'] ?? 0;
		$default_form_theme_id	    = (int) $_POST['default_form_theme_id'] ?? 0;
		$enable_data_retention		= $_POST['enable_data_retention'] ?? 0;
		$data_retention_period	    = abs((int) $_POST['data_retention_period']);

		$ldap_enable			= (int) ($_POST['ldap_enable'] ?? 0);
		$ldap_type				= $_POST['ldap_type'] ?? '';
		$ldap_host				= trim($_POST['ldap_host'] ?? '');
		$ldap_port				= (int) ($_POST['ldap_port'] ?? 0);
		$ldap_encryption		= $_POST['ldap_encryption'] ?? '';
		$ldap_basedn			= trim($_POST['ldap_basedn'] ?? '');
		$ldap_account_suffix	= trim($_POST['ldap_account_suffix'] ?? '');
		$ldap_required_group	= trim($_POST['ldap_required_group'] ?? '');
		$ldap_exclusive			= $_POST['ldap_exclusive'] ?? 0;

		//if account lock settings empty, set the default max attempts = 6 and lock period = 30 minutes
		//these defaults are based on PCI DSS standard
		if(empty($account_lock_period)){
			$account_lock_period = 30;
		}
		if(empty($account_lock_max_attempts)){
			$account_lock_max_attempts = 6;
		}

		//if no data retention period, turn it off
		if(empty($data_retention_period)){
			$enable_data_retention = 0;
		}
		
		//save the settings	
		$settings['smtp_enable'] 			= (int) $smtp_enable;
		$settings['smtp_host'] 				= $smtp_host;
		$settings['smtp_auth'] 				= $smtp_auth;
		$settings['smtp_secure']		 	= $smtp_secure;
		$settings['smtp_username'] 			= $smtp_username;
		$settings['smtp_password'] 			= $smtp_password;
		$settings['smtp_port'] 				= $smtp_port;
		$settings['admin_image_url'] 		= $admin_image_url;
		$settings['base_url'] 				= $base_url;
		$settings['default_from_name'] 		= $default_from_name;
		$settings['default_from_email'] 	= $default_from_email;
		$settings['upload_dir'] 			= $upload_dir;
		$settings['form_manager_max_rows'] 	= $form_manager_max_rows;
		$settings['disable_machform_link'] 	= $disable_machform_link;
		$settings['disable_pdf_link'] 		= $disable_pdf_link;
		$settings['enforce_tsv'] 			= $enforce_tsv;
		$settings['admin_theme'] 			= $admin_theme;
		$settings['timezone'] 				= $timezone;
		$settings['enable_ip_restriction'] 	= $enable_ip_restriction;
		$settings['ip_whitelist'] 			= $ip_whitelist;
		$settings['enable_account_locking']	= $enable_account_locking;
		$settings['account_lock_period'] 	= $account_lock_period;
		$settings['account_lock_max_attempts'] 	= $account_lock_max_attempts;
		$settings['default_form_theme_id'] 	= $default_form_theme_id;
		$settings['recaptcha_site_key'] 	= $recaptcha_site_key;
		$settings['recaptcha_secret_key'] 	= $recaptcha_secret_key;
		$settings['googleapi_clientid'] 	= $googleapi_clientid;
		$settings['googleapi_clientsecret'] = $googleapi_clientsecret;
		$settings['enable_data_retention']	= $enable_data_retention;
		$settings['data_retention_period'] 	= $data_retention_period;

		if($mf_settings['license_key'][0] == 'U'){
			$settings['ldap_enable'] 			= $ldap_enable;
			$settings['ldap_type'] 				= $ldap_type;
			$settings['ldap_host'] 				= $ldap_host;
			$settings['ldap_port'] 				= $ldap_port;
			$settings['ldap_encryption'] 		= $ldap_encryption;
			$settings['ldap_basedn'] 			= $ldap_basedn;
			$settings['ldap_account_suffix'] 	= $ldap_account_suffix;
			$settings['ldap_required_group'] 	= $ldap_required_group;
			$settings['ldap_exclusive'] 		= $ldap_exclusive;
		}

		mf_ap_settings_update($settings,$dbh);
		$_SESSION['MF_SUCCESS'] = 'System settings has been saved.';

		$mf_settings = mf_get_settings($dbh);
		
	}else{

		$smtp_enable 			= $mf_settings['smtp_enable'];
		$smtp_host 	 			= $mf_settings['smtp_host'];
		$smtp_auth   			= $mf_settings['smtp_auth'];
		$smtp_secure 			= $mf_settings['smtp_secure'];
		$smtp_username 			= $mf_settings['smtp_username'];
		$smtp_password 			= $mf_settings['smtp_password'];
		$smtp_port 	   			= $mf_settings['smtp_port'];
		$admin_image_url   		= $mf_settings['admin_image_url'];
		$base_url   			= $mf_settings['base_url'];
		$default_from_name   	= $mf_settings['default_from_name'];
		$default_from_email   	= $mf_settings['default_from_email'];
		$upload_dir   			= $mf_settings['upload_dir'];
		$form_manager_max_rows  = $mf_settings['form_manager_max_rows'];
		$disable_machform_link  = $mf_settings['disable_machform_link'];
		$disable_pdf_link  		= $mf_settings['disable_pdf_link'];
		$admin_theme			= $mf_settings['admin_theme'];
		$enforce_tsv			= $mf_settings['enforce_tsv'];
		$enable_ip_restriction	= $mf_settings['enable_ip_restriction'];
		$ip_whitelist			= $mf_settings['ip_whitelist'];
		$recaptcha_site_key		= $mf_settings['recaptcha_site_key'];
		$recaptcha_secret_key	= $mf_settings['recaptcha_secret_key'];
		$googleapi_clientid		= $mf_settings['googleapi_clientid'];
		$googleapi_clientsecret	= $mf_settings['googleapi_clientsecret'];
		$ldap_enable			= $mf_settings['ldap_enable'];
		$ldap_type				= $mf_settings['ldap_type'];
		$ldap_host				= $mf_settings['ldap_host'];
		$ldap_port				= (int) $mf_settings['ldap_port'];
		$ldap_encryption		= $mf_settings['ldap_encryption'];
		$ldap_basedn			= $mf_settings['ldap_basedn'];
		$ldap_account_suffix	= $mf_settings['ldap_account_suffix'];
		$ldap_required_group	= $mf_settings['ldap_required_group'];
		$ldap_exclusive			= $mf_settings['ldap_exclusive'];
		$timezone				= $mf_settings['timezone'];

		if(empty($admin_theme)){
			$admin_theme = 'vibrant';
		}
		
		//prepare default ip whitelist
		if(empty($ip_whitelist)){
			$current_ip = $_SERVER['REMOTE_ADDR'];

			$exploded     = explode('.', $current_ip);
			$current_ip_2 = $exploded[0].'.'.$exploded[1].'.'.$exploded[2].'.*'; 

			$ip_whitelist = "{$current_ip}\n{$current_ip_2}";
		}
		
		$enable_account_locking		= (int) $mf_settings['enable_account_locking'];
		$account_lock_period	    = (int) $mf_settings['account_lock_period'];
		$account_lock_max_attempts	= (int) $mf_settings['account_lock_max_attempts'];
		$default_form_theme_id		= (int) $mf_settings['default_form_theme_id'];
		$enable_data_retention		= (int) $mf_settings['enable_data_retention'];
		$data_retention_period	    = (int) $mf_settings['data_retention_period'];

		//if account lock settings empty, set the default max attempts = 6 and lock period = 30 minutes
		//these defaults are based on PCI DSS standard
		if(empty($account_lock_period)){
			$account_lock_period = 30;
		}
		if(empty($account_lock_max_attempts)){
			$account_lock_max_attempts = 6;
		}

		//if no data retention period, turn it off
		if(empty($data_retention_period)){
			$enable_data_retention = 0;
		}

	}

	//get the available custom themes
	$query = "SELECT theme_id,theme_name FROM ".MF_TABLE_PREFIX."form_themes WHERE theme_built_in=0 and status=1 ORDER BY theme_name ASC";
	$params = array();

	$sth = mf_do_query($query,$params,$dbh);

	$theme_list_array = array();
	while($row = mf_do_fetch_result($sth)){
		$theme_list_array[$row['theme_id']] = htmlspecialchars($row['theme_name']);
	}

	//get built-in themes
	$query = "SELECT theme_id,theme_name FROM ".MF_TABLE_PREFIX."form_themes WHERE theme_built_in=1 and status=1 ORDER BY theme_name ASC";
		
	$params = array();
	$sth = mf_do_query($query,$params,$dbh);

	$theme_builtin_list_array = array();
	while($row = mf_do_fetch_result($sth)){
		$theme_builtin_list_array[$row['theme_id']] = htmlspecialchars($row['theme_name']);
	}
	
	$license_key = $mf_settings['license_key'];
	if($license_key[0] == 'S'){
		$license_type = 'MachForm Standard';
	}else if($license_key[0] == 'P'){
		$license_type = 'MachForm Professional';
	}elseif ($license_key[0] == 'U') {
		$license_type = 'MachForm Unlimited';
	}else{
		$license_type = "Invalid License";
	}

	//get the list of the form, put them into array
	$query = "SELECT 
					form_name,
					form_id
				FROM
					".MF_TABLE_PREFIX."forms
				WHERE
					form_active=0 or form_active=1
			 ORDER BY 
					form_name ASC";
	
	$params = array();
	$sth = mf_do_query($query,$params,$dbh);
	
	$form_list_array = array();
	$i=0;
	while($row = mf_do_fetch_result($sth)){
		$form_list_array[$i]['form_id']   	  = $row['form_id'];

		if(!empty($row['form_name'])){		
			$form_list_array[$i]['form_name'] = htmlspecialchars($row['form_name'])." (#{$row['form_id']})";
		}else{
			$form_list_array[$i]['form_name'] = '-Untitled Form- (#'.$row['form_id'].')';
		}
		$i++;
	}

	$session_id = session_id();
	$jquery_data_code = '';

	$jquery_data_code .= "\$('.main_settings').data('session_id','{$session_id}');\n";

		$header_data =<<<EOT
<style>
.uploadifive-queue-item { border: none !important; }
</style>
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css{$mf_version_tag}" rel="stylesheet" />
EOT;

	$current_nav_tab = 'main_settings';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full" data-csrftoken="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>">
			<div class="post main_settings">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2>System Settings</h2>
							<p>Configure system wide settings.</p>
						</div>	
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>

				<div class="content_body">
					
					<form id="ms_form" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
					<ul id="ms_main_list">
						<li>
							<div id="ms_box_smtp" class="ms_box_main gradient_blue">
								<div class="ms_box_title">
									<input type="checkbox" <?php if(!empty($smtp_enable)){echo 'checked="checked"';} ?> value="1" class="checkbox" id="smtp_enable" name="smtp_enable">
									<label for="smtp_enable" class="choice inline">Use SMTP Server to Send Emails</label>
									<span class="icon-question helpicon" data-tippy-content="If your forms doesn't send the result to your email, most likely you'll need to enable this option. This will send all emails from MachForm through SMTP server."></span>
								</div>
								<div class="ms_box_email" <?php if(empty($smtp_enable)){echo 'style="display: none"';} ?>>
									<label class="description" for="smtp_host">SMTP Server <span class="required">*</span></label>
									<input id="smtp_host" name="smtp_host" class="element text medium" value="<?php echo htmlspecialchars($smtp_host,ENT_QUOTES); ?>" type="text">
									<label class="description" for="smtp_auth">Use Authentication</label>
									<select class="element select small" id="smtp_auth" name="smtp_auth"> 
										<option <?php if(empty($smtp_auth)){ echo 'selected="selected"'; } ?> value="0">No</option>
										<option <?php if(!empty($smtp_auth)){ echo 'selected="selected"'; } ?> value="1">Yes</option>				
									</select>
									<label class="description" for="smtp_secure">Use TLS/SSL</label>
									<select class="element select small" id="smtp_secure" name="smtp_secure"> 
										<option <?php if(empty($smtp_secure)){ echo 'selected="selected"'; } ?> value="0">No</option>
										<option <?php if(!empty($smtp_secure)){ echo 'selected="selected"'; } ?> value="1">Yes</option>						
									</select>
									<label class="description" for="smtp_username">SMTP User Name</label>
									<input id="smtp_username" name="smtp_username" class="element text medium" value="<?php echo htmlspecialchars($smtp_username,ENT_QUOTES); ?>" type="text">
									<label class="description" for="smtp_password">SMTP Password</label>
									<input id="smtp_password" name="smtp_password" class="element text medium" value="<?php echo htmlspecialchars($smtp_password,ENT_QUOTES); ?>" type="password">
									<label class="description" for="smtp_port">SMTP Port <span class="required">*</span></label>
									<input id="smtp_port" name="smtp_port" class="element text small" value="<?php echo htmlspecialchars($smtp_port,ENT_QUOTES); ?>" type="text" style="width: 50px">
								</div>
							</div>
						</li>
						<li>&nbsp;</li>
						
						<?php if($mf_settings['license_key'][0] == 'U'){ ?>
						<li>
							<div id="ms_box_ldap" class="ms_box_main gradient_blue">
								<div class="ms_box_title">
									<input type="checkbox" <?php if(!empty($ldap_enable)){echo 'checked="checked"';} ?> value="1" class="checkbox" id="ldap_enable" name="ldap_enable" style="margin: 3px 4px">
									<label for="ldap_enable" class="choice inline">Use LDAP Authentication for Users</label>
									<span class="icon-question helpicon" data-tippy-content="If enabled, all logins will be authenticated against LDAP server. Local MachForm user will be created automatically (if no existing machform user found) for authenticated LDAP login."></span>
								</div>
								<div class="ms_box_email" <?php if(empty($ldap_enable)){echo 'style="display: none"';} ?>>
									<label class="description" for="ldap_type">LDAP Server <span class="required">*</span></label>
									<select class="element select medium" id="ldap_type" name="ldap_type"> 
										<option <?php if($ldap_type == 'ad'){ echo 'selected="selected"'; } ?> value="ad">Active Directory</option>
										<option <?php if($ldap_type == 'openldap'){ echo 'selected="selected"'; } ?> value="openldap">OpenLDAP, ApacheDS, etc.</option>				
									</select>
									<label class="description" for="ldap_host">LDAP Hostname <span class="required">*</span></label>
									<input id="ldap_host" name="ldap_host" class="element text medium" value="<?php echo htmlspecialchars($ldap_host,ENT_QUOTES); ?>" type="text">
									
									<label class="description" for="ldap_port">LDAP Port <span class="required">*</span></label>
									<input id="ldap_port" name="ldap_port" class="element text small" value="<?php echo htmlspecialchars($ldap_port,ENT_QUOTES); ?>" type="text" style="width: 50px">
									
									<label class="description" for="ldap_encryption">Encryption Method <span class="required">*</span></label>
									<select class="element select medium" id="ldap_encryption" name="ldap_encryption"> 
										<option <?php if($ldap_encryption == 'none'){ echo 'selected="selected"'; } ?> value="none">None</option>
										<option <?php if($ldap_encryption == 'ssl'){ echo 'selected="selected"'; } ?> value="ssl">SSL (ldaps://)</option>
										<option <?php if($ldap_encryption == 'tls'){ echo 'selected="selected"'; } ?> value="tls">TLS</option>						
									</select>

									<div class="clearfix"></div>

									<label class="description" for="ldap_basedn" style="display: inline-block;">Base DN <span class="required">*</span> </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="Example, for subdomain: <em>users.example.com</em>, Use: <em>DC=users,DC=example,DC=com</em>"></span>
									<input id="ldap_basedn" name="ldap_basedn" class="element text large" value="<?php echo htmlspecialchars($ldap_basedn,ENT_QUOTES); ?>" type="text">
									
									<div class="clearfix" style="margin-bottom: 15px"></div>

									<span id="account_suffix_span" style="<?php if($ldap_type == 'openldap'){ echo "display: none"; } ?>">
										<label class="description inline" for="ldap_account_suffix">Account Suffix (for sAMAccountName only) </label>
										<span class="icon-question helpicon" data-tippy-content="Most likely, you can leave this empty. Users will be able to login using the User Principal Name (example: johndoe@domain.com)<br/><br/>If you prefer the user to login using sAMAccountName (example: johndoe), enter your domain here (example: @domain.com)"></span>
										<input id="ldap_account_suffix" name="ldap_account_suffix" class="element text medium" value="<?php echo htmlspecialchars($ldap_account_suffix,ENT_QUOTES); ?>" type="text">
									</span>

									<div class="clearfix" style="margin-bottom: 15px"></div>

									<label class="description inline" for="ldap_required_group">Required Group(s) </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="The groups, if any, that authenticating LDAP users must belong to. Use commas to separate multiple groups."></span>
									<input id="ldap_required_group" name="ldap_required_group" class="element text medium" value="<?php echo htmlspecialchars($ldap_required_group,ENT_QUOTES); ?>" type="text">
									<div style="clear: both"></div>

									<input type="checkbox" <?php if(!empty($ldap_exclusive)){echo 'checked="checked"';} ?> value="1" class="checkbox" id="ldap_exclusive" name="ldap_exclusive">
									<label class="description inline" for="ldap_exclusive">Use LDAP Exclusively </label> 
									<span class="icon-question helpicon" data-tippy-content="If enabled, all logins will be enforced against LDAP server only. It won't fallback to local MachForm database authentication.<br/><br/>TIP: You should only enable this option once you're sure that your MachForm is able to connect to the LDAP server properly."></span>

									<div style="clear: both"></div>

								</div>
							</div>
						</li>
						<li>&nbsp;</li>
						<?php } ?>

						<li>
							<div id="ms_box_misc" class="ms_box_main gradient_red">
								<div class="ms_box_title">
									<label class="choice">Miscellaneous Settings</label>
								</div>
								<div class="ms_box_email">
									<label class="description" for="timezone">System Time Zone</label>
									<select class="element select medium" id="timezone" name="timezone"> 
										<option value=""></option>
										<?php
											$timezone_array = mf_get_timezone_list();

										 	foreach ($timezone_array as $value) {
										 		if($timezone == $value['full_name']){
										 			$selected_tag = 'selected="selected"';
										 		}else{
										 			$selected_tag = '';
										 		}
										 		
										 		echo "<option {$selected_tag} value=\"{$value['full_name']}\">({$value['gmt_offset']}) {$value['simple_name']}</option>\n";
										 	}
										?>			
									</select>

									<div class="clearfix" style="margin-bottom: 15px"></div>

									<label class="description inline" for="admin_theme">Default Admin Panel Theme </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="The default theme for the admin panel. Any user can override this and choose their preferred theme from their profile setting."></span>
									<select class="element select medium" id="admin_theme" name="admin_theme"> 
										<option <?php if($admin_theme == 'vibrant'){ echo 'selected="selected"'; } ?> value="vibrant">Vibrant (Default)</option>
										<option <?php if($admin_theme == 'dark'){ echo 'selected="selected"'; } ?> value="dark">Dark</option>
										<option <?php if($admin_theme == 'light'){ echo 'selected="selected"'; } ?> value="light">Light</option>
										<option <?php if($admin_theme == 'blue'){ echo 'selected="selected"'; } ?> value="blue">Blue</option>			
									</select>

									<div class="clearfix" style="margin-bottom: 15px"></div>

									<label class="description inline" for="admin_image_url">Admin Panel Image URL </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="Provide a full URL to an image which is displayed on the admin panel header. A transparent PNG no larger than 150px wide by 55px high is recommended."></span>
									<input id="admin_image_url" name="admin_image_url" class="element text large" value="<?php echo htmlspecialchars($admin_image_url,ENT_QUOTES); ?>" type="text">

								</div>
								<div class="ms_box_more" style="display: none">
									<div class="clearfix" style="margin-bottom: 15px"></div>

									<label class="description inline" for="default_from_name">Default Email From Name </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="This is the default name being used to send all form notifications and system-related emails from MachForm (example: password reset email, form resume email)."></span>
									<input id="default_from_name" name="default_from_name" class="element text medium" value="<?php echo htmlspecialchars($default_from_name,ENT_QUOTES); ?>" type="text">

									<div class="clearfix" style="margin-bottom: 15px"></div>

									<label class="description inline" for="default_from_email">Default Email From Address </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="This is the default email address being used to send all form notifications and system-related emails from MachForm (example: password reset email, form resume email)."></span>
									<input id="default_from_email" name="default_from_email" class="element text medium" value="<?php echo htmlspecialchars($default_from_email,ENT_QUOTES); ?>" type="text">
									

									<div class="clearfix" style="margin-bottom: 15px"></div>

									<label class="description inline" for="base_url">MachForm URL </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="The URL to your MachForm admin panel. Normally you don't need to modify this setting. Don't change this setting if you aren't sure."></span>
									<input id="base_url" name="base_url" class="element text large" value="<?php echo htmlspecialchars($base_url,ENT_QUOTES); ?>" type="text">
									

									<div class="clearfix" style="margin-bottom: 15px"></div>

									<label class="description inline" for="upload_dir">File Upload Folder </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="The path for your file upload folder. If you change it, make sure to provide a full path to your upload folder. Don't change this setting if you aren't sure."></span>
									<input id="upload_dir" name="upload_dir" class="element text medium" value="<?php echo htmlspecialchars($upload_dir,ENT_QUOTES); ?>" type="text">
									

									<div class="clearfix" style="margin-bottom: 15px"></div>

									<label class="description inline" for="default_form_theme_id">Default Form Theme </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="The default theme being used on every new forms."></span>
									<select class="element select medium" id="default_form_theme_id" name="default_form_theme_id"> 
											<optgroup label="Built-in Themes">
												<option value="0">White</option>
												<?php 
													if(!empty($theme_builtin_list_array)){
														foreach ($theme_builtin_list_array as $theme_id=>$theme_name){
															$selected_tag = '';
															if($default_form_theme_id == $theme_id){
																$selected_tag = 'selected="selected"';
															}
															echo "<option value=\"{$theme_id}\" {$selected_tag}>{$theme_name}</option>";
														}
													}
												?>
											</optgroup>
										
											<?php if(!empty($theme_list_array)){ ?>	
											<optgroup label="Custom Themes">
												<?php 
													if(!empty($theme_list_array)){
														foreach ($theme_list_array as $theme_id=>$theme_name){
															$selected_tag = '';
															if($default_form_theme_id == $theme_id){
																$selected_tag = 'selected="selected"';
															}
															echo "<option value=\"{$theme_id}\" {$selected_tag}>{$theme_name}</option>";
														}
													}
												?>
											</optgroup>
											<?php } ?>
									</select>

									<div class="clearfix" style="margin-bottom: 15px"></div>

									<label class="description inline" for="form_manager_max_rows">Form Manager Max List </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="The number of forms to be displayed for each page on the Form Manager."></span>
									<input id="form_manager_max_rows" style="width: 50px" name="form_manager_max_rows" class="element text small" value="<?php echo htmlspecialchars($form_manager_max_rows,ENT_QUOTES); ?>" type="text">
									
									<div style="clear: both;width: 100%;border-bottom: 1px dashed #DF8F7D;margin-top: 20px;margin-bottom: 5px"></div>

									<label class="description inline" for="googleapi_clientid">Google API - Client ID </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="These keys are required to integrate your forms with Google services (e.g. Google Sheets)"></span>
									<input id="googleapi_clientid" name="googleapi_clientid" class="element text large" value="<?php echo htmlspecialchars($googleapi_clientid,ENT_QUOTES); ?>" type="text">

									<label class="description" for="googleapi_clientsecret">Google API - Client Secret</label>
									<input id="googleapi_clientsecret" name="googleapi_clientsecret" class="element text large" value="<?php echo htmlspecialchars($googleapi_clientsecret,ENT_QUOTES); ?>" type="text">

									<a href="https://www.machform.com/howto-get-your-google-clientid-and-clientsecret" target="_blank" class="blue_dotted" style="font-weight: bold;margin-top: 10px;clear: both;display: inline-block;font-size: 95%">Step-by-step instructions to get your Google Client ID and Client Secret</a>

									<div style="clear: both;width: 100%;border-bottom: 1px dashed #DF8F7D;margin-top: 20px;margin-bottom: 5px"></div>
									
									<label class="description inline" for="recaptcha_site_key">Google reCAPTCHA - Site Key </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="These are required keys to use reCAPTCHA on your forms. <br/>You can get these keys from https://www.google.com/recaptcha/admin"></span>
									<input id="recaptcha_site_key" name="recaptcha_site_key" class="element text large" value="<?php echo htmlspecialchars($recaptcha_site_key,ENT_QUOTES); ?>" type="text">

									<label class="description" for="recaptcha_secret_key">Google reCAPTCHA - Secret Key</label>
									<input id="recaptcha_secret_key" name="recaptcha_secret_key" class="element text large" value="<?php echo htmlspecialchars($recaptcha_secret_key,ENT_QUOTES); ?>" type="text">

									<div style="clear: both;width: 100%;border-bottom: 1px dashed #DF8F7D;margin-top: 20px;margin-bottom: 5px"></div>
									
									<input type="checkbox" <?php if(!empty($disable_machform_link)){echo 'checked="checked"';} ?> value="1" class="checkbox" id="disable_machform_link" name="disable_machform_link">
									<label class="description inline" for="disable_machform_link">Remove the "Powered by MachForm" link from all my forms</label>
									
									<div style="clear: both"></div>

									<input type="checkbox" <?php if(!empty($disable_pdf_link)){echo 'checked="checked"';} ?> value="1" class="checkbox" id="disable_pdf_link" name="disable_pdf_link">
									<label class="description inline" for="disable_pdf_link">Remove Links from PDF </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="Some mail server might incorrectly marked the PDF file attached with the form notification emails as spam/malware due to false positive when the PDF contain any hyperlink. <br/><br/>Enabling this option will remove any hyperlink within the PDF and avoid the false positive."></span>
									
									<div style="clear: both"></div>

									<input type="checkbox" <?php if(!empty($enforce_tsv)){echo 'checked="checked"';} ?> value="1" class="checkbox" id="enforce_tsv" name="enforce_tsv">
									<label class="description inline" for="enforce_tsv">Enforce 2-Step Verification on users </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="If enabled, all MachForm users are enrolled in 2-Step Verification. Once enabled, MachForm will require a six-digit security code (generated by TOTP authenticator mobile app) in addition to the standard password whenever they sign in to MachForm."></span>
									
									<div style="clear: both"></div>

									<input type="checkbox" <?php if(!empty($enable_ip_restriction)){echo 'checked="checked"';} ?> value="1" class="checkbox" id="enable_ip_restriction" name="enable_ip_restriction">
									<label class="description inline" for="enable_ip_restriction">Enable IP Address Restriction </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="If enabled, all users can only login to MachForm panel from IP address listed here. Users using other IP address will be blocked."></span>
									
									<div style="clear: both"></div>

									<div id="div_ip_whitelist" style="display: <?php if(!empty($enable_ip_restriction)){ echo 'block'; }else{ echo 'none'; } ?>">
										<label class="checkbox inline" for="ip_whitelist">Only allow login from these IP Addresses: </label>
										<span class="icon-question helpicon clearfix" data-tippy-content="You can enter multiple ip addresses, one ip address per line. Use the asterisk (*) as a wildcard to specify a range of address (examples: 192.168.1.*, 192.168.*, 192.168.*.120)"></span>
										<textarea class="element textarea small" style="width: 250px;margin-top: 5px" name="ip_whitelist" id="ip_whitelist"><?php echo htmlentities($ip_whitelist,ENT_QUOTES); ?></textarea>
									</div>
									
									<div style="clear: both"></div>

									<input type="checkbox" <?php if(!empty($enable_account_locking)){echo 'checked="checked"';} ?> value="1" class="checkbox" id="enable_account_locking" name="enable_account_locking">
									<label class="description inline" for="enable_account_locking">Enable Account Locking </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="If enabled, users account will be temporarily locked after several invalid login attempts."></span>

									<div style="clear: both"></div>

									<div id="div_account_locking" style="margin-left: 22px;margin-top: 10px;display: <?php if(!empty($enable_account_locking)){ echo 'block'; }else{ echo 'none'; } ?>">
											Lock account for
											<input type="text" maxlength="255" value="<?php echo htmlspecialchars($account_lock_period,ENT_QUOTES); ?>" class="text" style="width: 20px" id="account_lock_period" name="account_lock_period">
											minutes after
											<input type="text" maxlength="255" value="<?php echo htmlspecialchars($account_lock_max_attempts,ENT_QUOTES); ?>" class="text" style="width: 20px" id="account_lock_max_attempts" name="account_lock_max_attempts">
											invalid login attempts
									</div>

									<div style="clear: both"></div>

									<input type="checkbox" <?php if(!empty($enable_data_retention)){echo 'checked="checked"';} ?> value="1" class="checkbox" id="enable_data_retention" name="enable_data_retention">
									<label class="description inline" for="enable_data_retention">Enable Data Retention</label> 
									<span class="icon-question helpicon clearfix" data-tippy-content="You can specify how long MachForm retains form data before automatically deleting it. <br/><br/>When data reaches the end of the retention period, it is deleted automatically. <br/><br/>To exclude specific forms from data retention rule, apply the tagname: <b>skipdataretention</b>"></span>
									
									<div style="clear: both"></div>

									<div id="div_data_retention" style="margin-left: 22px;margin-top: 10px;display: <?php if(!empty($enable_data_retention)){ echo 'block'; }else{ echo 'none'; } ?>">
											Automatically delete form entries
											<input type="text" maxlength="255" value="<?php echo htmlspecialchars($data_retention_period,ENT_QUOTES); ?>" class="text" style="width: 25px" id="data_retention_period" name="data_retention_period">
											months after submission
											<span style="display: block;margin-top: 10px;font-size: 95%;background-color: #bd3d20;color: #fff;font-weight: bold;padding: 10px;width: 90%;border-radius: 2px;">IMPORTANT! This feature will PERMANENTLY DELETE form entries.</span>
									</div>
								</div>
								<div class="ms_box_more_switcher">
									<a id="more_option_misc_settings" href="#">advanced options</a>
								</div>
							</div>
						</li>
						<li>&nbsp;</li>
						<li>
							<div id="ms_box_export_tool" class="ms_box_main gradient_green">
								<div class="ms_box_title">
									<label class="choice inline">Form Export/Import Tool</label> 
									<span class="icon-question helpicon clearfix" data-tippy-content="Use this tool to export your form structure into a file and then import the form file into another instance of MachForm"></span>
								</div>
								<div class="ms_box_email" style="padding-top: 15px">
									<span>
										<input id="export_import_type_1"  name="export_import_type" class="element radio" type="radio" value="export" checked="checked" style="margin-left: 0px" />
										<label for="export_import_type_1">Export Form</label>
									</span>
									<span style="margin-left: 20px">
										<input id="export_import_type_2"  name="export_import_type" class="element radio" type="radio" value="import" />
										<label for="export_import_type_2">Import Form</label>
									</span>

									<div id="tab_export_form">
										<label style="margin-top: 10px" class="description" for="export_form_id">Choose Form to Export</label>
										<select class="element select" id="export_form_id" name="export_form_id" style="width: 300px;margin-right: 10px"> 
											<?php
												if(!empty($form_list_array)){
													foreach ($form_list_array as $value) {
														echo "<option value=\"{$value['form_id']}\">{$value['form_name']}</option>";
													}
												}
											?>				
										</select>
										<input type="button" id="ms_btn_export_form" value="Export Form" class="button_text">
									</div>
									<div id="tab_import_form" style="display: none">
										<div id="ms_form_import_upload">
											<label class="description" for="ms_form_import_file">Upload Form File</label>
											<input id="ms_form_import_file" name="ms_form_import_file" class="element file" type="file" />
										</div>
									</div>
								</div>
							</div>
						</li>
						<li style="padding-top: 20px">
							
							<a href="#" id="button_save_main_settings" class="bb_button bb_small bb_green">
								<span class="icon-disk" style="margin-right: 5px"></span>Save Settings
							</a>
							
						</li>		
					</ul>
					<input type="hidden" id="submit_form" name="submit_form" value="1">
					</form>

					<div id="license_box" data-licensekey="<?php echo $mf_settings['license_key']; ?>" data-machformversion="<?php echo $mf_settings['machform_version']; ?>">
						<table id="license_box_table" width="100%" border="0" cellspacing="0" cellpadding="0">
						  <tr>
						    <th colspan="2" scope="col">License Information</th>
						  </tr>
						  <tr>
						    <td class="ms_lic_left" width="50%" align="right">Customer ID</td>
						    <td class="ms_lic_right" width="50%"><span id="lic_customer_id"><?php if(!empty($mf_settings['customer_id'])){echo $mf_settings['customer_id'];}else{echo '-none-';} ?></span></td>
						  </tr>
						  <tr>
						    <td class="ms_lic_left" align="right">Name</td>
						    <td class="ms_lic_right"><span id="lic_customer_name"><?php if(!empty($mf_settings['customer_name'])){echo $mf_settings['customer_name'];}else{echo '-none-';} ?></span> <?php if(empty($mf_settings['customer_id'])){ echo '<a id="lic_activate" href="#">activate now</a>'; } ?></td>
						  </tr>
						  <tr>
						    <td class="ms_lic_left" align="right">License Type</td>
						    <td class="ms_lic_right"><span id="lic_type"><?php echo $license_type; ?></span></td>
						  </tr>
						  <tr>
						    <td class="ms_lic_left" align="right">MachForm Version</td>
						    <td class="ms_lic_right"><?php echo $mf_settings['machform_version']; ?></td>
						  </tr>
						  <tr>
						    <td class="ms_lic_left" align="right">&nbsp;</td>
						    <td class="ms_lic_right"><a href="#" class="blue_dotted" id="check_update_link">check for new version</a> <img id="check_update_loader" src="images/loader_small_grey.gif" style="vertical-align: middle;display: none"/></td>
						  </tr>
						  <tr>
						    <td colspan="2" align="middle">
						    	<a id="ms_change_license" href="#">Change License Key</a>
						    	
						    	<?php if($license_key[0] == 'S' || $license_key[0] == 'P'){ ?>	
						    	<a id="ms_upgrade_license" href="#">Upgrade License Type</a>
						    	<?php } ?>
						    </td>
						  </tr>
						</table>
					</div>

					<div id="dialog-change-password" title="Change Admin Password" class="buttons" style="display: none"> 
						<form id="dialog-change-password-form" class="dialog-form" style="margin-bottom: 10px">				
							<ul>
								<li>
									<label for="dialog-change-password-input1" class="description">Enter New Password</label>
									<input type="password" id="dialog-change-password-input1" name="dialog-change-password-input1" class="text large" value="">
									<label for="dialog-change-password-input2" style="margin-top: 15px" class="description">Confirm New Password</label>
									<input type="password" id="dialog-change-password-input2" name="dialog-change-password-input2" class="text large" value="">
									
								</li>
							</ul>
						</form>
					</div>

					<div id="dialog-change-license" title="Change License Key" class="buttons" style="display: none"> 
						<form id="dialog-change-license-form" class="dialog-form" style="margin-bottom: 10px">				
							<ul>
								<li>
									<label for="dialog-change-license-input" class="description">Enter New License Key</label>
									<input type="text" id="dialog-change-license-input" name="dialog-change-license-input" class="text large" value="">
								</li>
							</ul>
						</form>
					</div>

					<div id="dialog-upgrade-license" title="Upgrade License Type Pricing" class="buttons" style="display: none"> 
						<div id="ms-license-upgrade-container">
							<?php if($license_key[0] == 'S'){ ?>	
							<div id="ms-license-upgrade-pro" class="gradient_blue" style="margin-right: 10px">
								<ul style="padding: 20px">
									<li class="ms-license-title">MachForm Professional</li>
									<li class="ms-license-price">$100</li>
									<li style="padding: 5px 0">Use on 10 sites</li>
									<li style="padding: 5px 0">20 users</li>
									<li style="padding: 5px 0">-</li>
									<li class="li-ms-buy-now">
										<a href="https://sites.fastspring.com/appnitro/instant/machform_professional_upgrade" target="_blank" id="buy-now-btn-license" class="bb_button bb_small bb_blue">
											<span class="icon-coins" style="margin-right: 5px"></span>Buy Now
										</a>
									</li>
								</ul>
							</div>
							<div id="ms-license-upgrade-unlimited" class="gradient_blue" style="margin-left: 10px">
								<ul style="padding: 20px">
									<li class="ms-license-title">MachForm Unlimited</li>
									<li class="ms-license-price">$400</li>
									<li style="padding: 5px 0">Use on unlimited sites</li>
									<li style="padding: 5px 0">Unlimited users</li>
									<li style="padding: 5px 0">LDAP Integration</li>
									<li class="li-ms-buy-now">
										<a href="https://sites.fastspring.com/appnitro/instant/machform_unlimited_upgrade?coupon=MUFS91827354" target="_blank" id="buy-now-btn-license2" class="bb_button bb_small bb_blue">
											<span class="icon-coins" style="margin-right: 5px"></span>Buy Now
										</a>
									</li>
								</ul>
							</div>
							<?php }else if($license_key[0] == 'P'){ ?>
							<div id="ms-license-upgrade-unlimited" class="gradient_blue" style="margin-left: 10px">
								<ul style="padding: 20px">
									<li class="ms-license-title">MachForm Unlimited</li>
									<li class="ms-license-price">$300</li>
									<li style="padding: 5px 0">Use on unlimited sites</li>
									<li style="padding: 5px 0">Unlimited users</li>
									<li style="padding: 5px 0">LDAP Integration</li>
									<li class="li-ms-buy-now">
										<a href="https://sites.fastspring.com/appnitro/instant/machform_unlimited_upgrade?coupon=MUFP7364625" target="_blank" id="buy-now-btn-license2" class="bb_button bb_small bb_blue">
											<span class="icon-coins" style="margin-right: 5px"></span>Buy Now
										</a>
									</li>
								</ul>
							</div>
							<?php } ?>
						</div>
						<div id="license-upgrade-info" class="blue_box">
							<span class="icon-info" style="margin-right: 5px;font-size: 120%"></span> You can upgrade to higher license type and only need to pay the difference, as listed above. Upon completed order, you'll receive a new license key that you can activate by clicking the Change License Key button.
						</div>
					</div>
					
					<div id="dialog-form-import-success" title="Success! Import completed" class="buttons" style="display: none">
						<span class="icon-checkmark-circle"></span> 
						<p>
							<strong>The following form has been imported:</strong><br/>
							<a id="form-imported-link" target="_blank" style="color: #529214;font-size: 120%;border: none;background: none;float: none" href="#">x</a>
						</p>	
					</div>

					<div id="dialog-warning" title="Error! Import failed" class="buttons" style="display: none">
						<span class="icon-bubble-notification"></span>
						<p id="dialog-warning-msg" style="margin-bottom: 20px">
							The form file seems to be corrupted.<br/>Please try again with another file.
						</p>
					</div>

					<div id="dialog-get-new-update" title="New version available!" class="buttons" style="display: none">
						<span class="icon-bubble-notification"></span>
						<p>
							<strong>A new MachForm version is available: <span style="font-size: 120%" id="latest_version_span"></span></strong>
						</p>	
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
<script src="js/popper.min.js{$mf_version_tag}"></script>
<script src="js/tippy.index.all.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.core.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.widget.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.tabs.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.mouse.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.sortable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.draggable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.position.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.dialog.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/uploadifive/jquery.uploadifive.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/main_settings.js{$mf_version_tag}"></script>
<style>
.tippy-tooltip{
	font-size: 98%;
}
</style>
EOT;

	require('includes/footer.php'); 
?>