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
	require('lib/google-authenticator.php');

	$dbh = mf_connect_db();

	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);
	
	$user_id = $_SESSION['mf_user_id'];

	$query = "SELECT 
					user_email,
					user_fullname,
					user_admin_theme,
					tsv_enable,
					tsv_code_log 
				FROM 
					".MF_TABLE_PREFIX."users 
			   WHERE 
			   		user_id=? and `status`=1";
	$params = array($user_id);
			
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$stored_user_email = $row['user_email'];
	$user_fullname 	   = $row['user_fullname'];
	$user_admin_theme  = $row['user_admin_theme'];
	$tsv_enable		   = (int) $row['tsv_enable'];
	$tsv_code_log	   = $row['tsv_code_log'];

	$http_host = parse_url($mf_settings['base_url'], PHP_URL_HOST);

	if(empty($user_admin_theme)){
		$user_admin_theme = $mf_settings['admin_theme'];
	}
	
	//2-Step Verification
	//if TSV currently not enabled, prepare secret key for TSV setup
	if(empty($tsv_enable)){
		$authenticator = new PHPGangsta_GoogleAuthenticator();

		$tsv_secret = $authenticator->createSecret();
		$totp_data  = "otpauth://totp/MachForm-{$http_host}:{$stored_user_email}?secret={$tsv_secret}";
	}

	$ldap_enabled_exclusively = false;
	if(!empty($mf_settings['ldap_enable']) && !empty($mf_settings['ldap_exclusive'])){
		$ldap_enabled_exclusively = true;
	}

	//handle form submission if there is any
	if(!empty($_POST['submit_form'])){

		if($ldap_enabled_exclusively == false){
			$user_email = strtolower(trim($_POST['user_email']));
		}else{
			$user_email = $stored_user_email;
		}

		$user_admin_theme = $_POST['user_admin_theme'];
		
		//we need to check the email, ensure it's valid email address
		$email_regex  = '/^[A-z0-9][\+\w.\'-]*@[A-z0-9][\w\-\.]*\.[A-z0-9]{2,}$/';
		$regex_result = preg_match($email_regex, $user_email);
			
		if(empty($regex_result)){
			$_SESSION['MF_ERROR'] = 'Please enter valid email address!';
		}else{
			//check for duplicate
			$query = "select count(user_email) total_user from `".MF_TABLE_PREFIX."users` where user_email = ? and user_id <> ? and `status` > 0";
				
			$params = array($user_email,$user_id);
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);

			if(!empty($row['total_user'])){
				$_SESSION['MF_ERROR'] = 'This email address already being used.';
			}else{

				//update the email address and tsv setting
				if(!empty($_POST['tsv_enable'])){
					//only allow tsv_enable=1 when tsv has been verified previously
					$query 	= "SELECT tsv_code_log FROM ".MF_TABLE_PREFIX."users WHERE user_id=?";
					$params = array($user_id);
					
					$sth = mf_do_query($query,$params,$dbh);
					$row = mf_do_fetch_result($sth);

					$tsv_enable_status = 0;
					if(!empty($row['tsv_code_log'])){
						$tsv_enable_status = 1;
					}

					$query = "UPDATE ".MF_TABLE_PREFIX."users set user_email=?,tsv_enable=? where user_id=?";
					$params = array($user_email,$tsv_enable_status,$user_id);
					mf_do_query($query,$params,$dbh);
				}else{
					//if tsv being disabled, reset secret ket and previous tsv code log
					$query = "UPDATE ".MF_TABLE_PREFIX."users set user_email=?,tsv_enable=0,tsv_secret='',tsv_code_log='' where user_id=?";
					$params = array($user_email,$user_id);
					mf_do_query($query,$params,$dbh);
				}

				//update admin theme setting
				$query = "UPDATE ".MF_TABLE_PREFIX."users set user_admin_theme=? where user_id=?";
				$params = array($user_admin_theme,$user_id);
				mf_do_query($query,$params,$dbh);

				$_SESSION['mf_user_admin_theme'] = $user_admin_theme;

				$_SESSION['MF_SUCCESS'] = 'Your profile has been saved.';

				header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/my_account.php");
				exit;
			}
		}

	}else{
		$user_email = $stored_user_email;
	}
	

		$header_data =<<<EOT
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css{$mf_version_tag}" rel="stylesheet" />
EOT;

	$current_nav_tab = 'my_account';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post main_settings">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2>My Account <span class="icon-arrow-right2 breadcrumb_arrow"></span> <?php echo htmlspecialchars($user_fullname); ?></h2>
							<p>Change your password or login email address.</p>
						</div>	
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>

				<div class="content_body">
					
					<form id="ms_form" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
					<ul id="ms_main_list">
						
						<li>
							<div id="ms_box_account" data-csrftoken="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>" data-userid="<?php echo $user_id; ?>" class="ms_box_main gradient_blue">
								<div class="ms_box_title">
									<label class="choice">My Account Profile</label>
								</div>
								<div class="ms_box_email">
									<label class="description" for="admin_theme">Admin Panel Theme </label>
									
									<select class="element select medium" id="user_admin_theme" name="user_admin_theme"> 
										<option <?php if($user_admin_theme == 'vibrant'){ echo 'selected="selected"'; } ?> value="vibrant">Vibrant</option>
										<option <?php if($user_admin_theme == 'dark'){ echo 'selected="selected"'; } ?> value="dark">Dark</option>
										<option <?php if($user_admin_theme == 'light'){ echo 'selected="selected"'; } ?> value="light">Light</option>
										<option <?php if($user_admin_theme == 'blue'){ echo 'selected="selected"'; } ?> value="blue">Blue</option>			
									</select>
									<?php if($ldap_enabled_exclusively == false){ ?>
										<div class="clearfix"></div>

										<label class="description inline" for="user_email">Email Address <span class="required">*</span> </label>
										<span class="icon-question helpicon clearfix" data-tippy-content="This is the email address being used to login to the MachForm panel."></span>
										<input id="user_email" name="user_email" class="element text medium" value="<?php echo htmlspecialchars($user_email,ENT_QUOTES); ?>" type="text">
										
										<div class="clearfix" style="margin-bottom: 15px"></div>

										<a id="ms_change_password" href="#" class="blue_dotted" style="font-weight: bold"><span class="icon-key"></span> Change Password</a>
									<?php } ?>
								</div>
								<div>
								</div>
							</div>
						</li>
						<li>&nbsp;</li>
						

						<li>
							<div id="ms_box_user_tsv" class="ms_box_main gradient_red">
								<div class="ms_box_title">
									<input type="checkbox" value="1" class="checkbox" id="tsv_enable" name="tsv_enable" <?php if(!empty($tsv_enable)){ echo 'checked="checked"';} ?>>
									<label for="tsv_enable" class="choice inline">Enable 2-Step Verification</label>
									<span class="icon-question helpicon clearfix" data-tippy-content="2-Step Verification is an optional but highly recommended security feature that adds an extra layer of protection to your MachForm account.<br/><br/>Once enabled, MachForm will require a six-digit security code in addition to your password whenever you sign in."></span>
								</div>
								<div class="ms_box_email" <?php if(empty($tsv_enable)){ echo 'style="display: none"'; } ?>>
									<?php
										if(!empty($tsv_enable)){
											echo "<h6>2-Step Verification Status &#8674; Activated</h6>";
										}else{
									?>

									<ul>
										<li class="tsv_setup_title">Step 1. Open Authenticator mobile app</li>
										<li>
											Open your authenticator mobile app. If you don't have it yet, you can install any of the following apps:
											<div style="margin-top: 10px;padding-left: 20px">
											&#8674; <a class="app_link" href="https://authy.com/download/" target="_blank">Authy</a> (Android/iPhone/Mac/Windows/Linux)<br/>
											&#8674; <a class="app_link" href="http://support.google.com/accounts/bin/answer.py?hl=en&answer=1066447" target="_blank">Google Authenticator</a> (Android/iPhone)<br/>
											&#8674; <a class="app_link" href="http://guide.duosecurity.com/third-party-accounts" target="_blank">Duo Mobile</a> (Android/iPhone)<br/>
											&#8674; <a class="app_link" href="https://www.microsoft.com/en-US/store/apps/Authenticator/9WZDNCRFJ3RJ" target="_blank">Authenticator</a> (Windows Phone)
											</div>
										</li>
										<li class="tsv_setup_title">Step 2. Scan Barcode</li>
										<li>
											Use your authenticator app to scan the barcode below:
											<div style="width: 80%;padding: 20px;text-align: center">
												<div id="qrcode" data-totpdata="<?php echo $totp_data; ?>" data-tsvsecret="<?php echo $tsv_secret; ?>"></div>
											</div>
											or you can enter this secret key manually: <strong><?php echo $tsv_secret; ?></strong>
										</li>
										<li class="tsv_setup_title">Step 3. Verify Code</li>
										<li>
											Once your app is configured, enter the <strong>six-digit security code</strong> generated by your app to verify and enable 2-step verification:
											<label class="description" for="user_email">Security Code</label>
											<input id="tsv_confirm_token" name="tsv_confirm_token" class="element text small" value="" type="text">
											<br/>
											<a style="margin-top: 10px" href="#" id="button_verify_tsv" class="bb_button bb_small bb_blue">
												Verify Code
											</a>
										</li>
									</ul>
									
									<?php } ?>
								</div>
							</div>
						</li>
						<li>&nbsp;</li>
						<li style="padding-top: 20px">
							
							<a href="#" id="button_save_main_settings" class="bb_button bb_small bb_green">
								<span class="icon-disk" style="margin-right: 5px"></span>Save Changes
							</a>
							
						</li>		
					</ul>
					<input type="hidden" id="submit_form" name="submit_form" value="1">
					</form>

					<div id="dialog-change-password" title="Change My Password" class="buttons" style="display: none"> 
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

					<div id="dialog-password-changed" title="Success!" class="buttons" style="display: none">
						<img src="images/icons/62_green_48.png" title="Success" /> 
						<p id="dialog-password-changed-msg">
								The new password has been saved.
						</p>
					</div>

					<div id="dialog-tsv-verified" title="Success!" class="buttons" style="display: none">
						<img src="images/icons/62_green_48.png" title="Success" /> 
						<p id="dialog-tsv-verified-msg">
								Security Code verified.
						</p>
					</div>

					<div id="dialog-tsv-invalid" title="Error!" class="buttons" style="display: none">
						<img src="images/icons/warning.png" title="Error" /> 
						<p id="dialog-tsv-invalid-msg">
								Invalid Security Code. Please try again.
						</p>
					</div>

				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

 
<?php
	$footer_data =<<<EOT
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
<script type="text/javascript" src="js/qrcode/qrcode.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/my_account.js{$mf_version_tag}"></script>
<style>
.tippy-tooltip{
	font-size: 98%;
}
</style>
EOT;

	require('includes/footer.php'); 
?>