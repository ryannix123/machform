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

	require('includes/filter-functions.php');
	require('lib/google-authenticator.php');
	require('lib/password-hash.php');
	

	$ssl_suffix  = mf_get_ssl_suffix();

	$dbh 		 = mf_connect_db();
	
	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);
	
	//check for tsv setup session, if not exist, redirect back to login page
	if(empty($_SESSION['mf_tsv_setup'])){
		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/index.php");
		exit;
	}

	$user_id  = $_SESSION['mf_tsv_setup'];

	$query  = "SELECT 
					`priv_administer`,
					`priv_new_forms`,
					`priv_new_themes`,
					`user_email` 
				FROM 
					`".MF_TABLE_PREFIX."users` 
			   WHERE 
				   	`user_id`=? and `status`=1";
	$params = array($user_id);
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$priv_administer	  = (int) $row['priv_administer'];
	$priv_new_forms		  = (int) $row['priv_new_forms'];
	$priv_new_themes	  = (int) $row['priv_new_themes'];
	$stored_user_email 	  = $row['user_email'];
	
	$authenticator = new PHPGangsta_GoogleAuthenticator();

	//initialize tsv secret for qrcode	
	if(empty($_SESSION['mf_tsv_setup_secret'])){
		$_SESSION['mf_tsv_setup_secret'] =  $authenticator->createSecret();
	}
	$tsv_secret = $_SESSION['mf_tsv_setup_secret'];
	$totp_data  = "otpauth://totp/MachForm:{$stored_user_email}?secret={$tsv_secret}";

	//verify security code
	if(!empty($_POST['submit'])){
		$input 	  = mf_sanitize($_POST);
		$tsv_code = $input['tsv_code'];
		
		$tsv_result    = $authenticator->verifyCode($tsv_secret, $tsv_code, 8);  //8 means 4 minutes before or after

		if($tsv_result === true){
				$remember_me  = $_SESSION['mf_tsv_setup_remember_me'];
				
				//invalidate mf_tsv_setup session
				$_SESSION['mf_tsv_setup'] = '';
				$_SESSION['mf_tsv_setup_remember_me'] = '';
				unset($_SESSION['mf_tsv_setup']);
				unset($_SESSION['mf_tsv_setup_remember_me']);

				//regenerate session id for protection against session fixation
				session_regenerate_id();

				//set the session variables for the user=========
				$_SESSION['mf_logged_in'] = true;
				$_SESSION['mf_user_id']   = $user_id;
				$_SESSION['mf_user_privileges']['priv_administer'] = $priv_administer;
				$_SESSION['mf_user_privileges']['priv_new_forms']  = $priv_new_forms;
				$_SESSION['mf_user_privileges']['priv_new_themes'] = $priv_new_themes;
				//===============================================

				//update last_login_date and last_ip_address
				$last_login_date = date("Y-m-d H:i:s");
				$last_ip_address = $_SERVER['REMOTE_ADDR'];

				$query  = "UPDATE ".MF_TABLE_PREFIX."users set last_login_date=?,last_ip_address=?,tsv_code_log=?,tsv_secret=?,tsv_enable=1 WHERE `user_id`=?";
				$params = array($last_login_date,$last_ip_address,$tsv_code,$tsv_secret,$user_id);
				mf_do_query($query,$params,$dbh);

				//if the user select the "remember me option"
				//set the cookie and make it active for the next 30 days
				if(!empty($remember_me)){
					$hasher 	 = new PasswordHash(8, FALSE);
					$cookie_hash = $hasher->HashPassword(mt_rand()); //generate random hash and save it into ap_users table

					$query = "update ".MF_TABLE_PREFIX."users set cookie_hash=? where `user_id`=?";
			   		$params = array($cookie_hash,$user_id);
			   		mf_do_query($query,$params,$dbh);

			   		//send the cookie
			   		setcookie('mf_remember',$cookie_hash, time()+3600*24*30, "/");
				}

				$_SESSION['MF_SUCCESS'] = 'Account Successfully Verified.';

				if(!empty($_SESSION['prev_referer'])){
					$next_page = $_SESSION['prev_referer'];
						
					unset($_SESSION['prev_referer']);
					header("Location: ".$next_page);
						
					exit;
				}else{
					header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/manage_forms.php");
					exit;
				}
		}else{
			$_SESSION['MF_LOGIN_ERROR'] = 'Error! Incorrect code.';
		}
	}
	
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>MachForm Admin Panel</title>
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
<?php
	if(!empty($mf_settings['admin_theme'])){
		echo '<link href="css/themes/theme_'.$mf_settings['admin_theme'].'.css'.$mf_version_tag.'" rel="stylesheet" type="text/css" />';
	}
?>
<link href="css/bb_buttons.css<?php echo $mf_version_tag; ?>" rel="stylesheet" type="text/css" />
</head>

<body>

<div id="bg" class="login_page">

<div id="container">

	<div id="header">
	<?php
		if(!empty($mf_settings['admin_image_url'])){
			$machform_logo_main = $mf_settings['admin_image_url'];
		}else{
			if(!empty($mf_settings['admin_theme'])){
				$machform_logo_main = 'images/machform_logo_'.$mf_settings['admin_theme'].'.png';
			}else{
				$machform_logo_main = 'images/machform_logo.png';
			}
		}
	?>
		<div id="logo">
			<img class="title" src="<?php echo $machform_logo_main; ?>" style="margin-left: 8px" width="158" alt="MachForm" />
		</div>	
		
	</div>
	<div id="main">
	
 
		<div id="content">
			<div class="post login_main">

				<div style="padding-top: 10px">
					
					<div>
						<span id="login_logo" class="icon-shield"></span>
						<h3>Verification Required</h3>
						<p>Please follow these steps below to continue:</p>
						<div style="clear:both; border-bottom: 1px dotted #CCCCCC;margin-top: 15px"></div>
					</div>
					
					<div style="margin-top: 10px">
							<form id="form_login" class="appnitro"  method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
							<ul>

								<?php if(!empty($_SESSION['MF_LOGIN_ERROR'])){ ?>
									<li id="li_login_notification">
										<h5><?php echo $_SESSION['MF_LOGIN_ERROR']; ?></h5>	
									</li>		
								<?php 
									   unset($_SESSION['MF_LOGIN_ERROR']);
									} 
								?>
								<li id="li_login_tsv_setup">
									<ul>
										<li class="tsv_setup_title">Step 1. Open Authenticator mobile app</li>
										<li>
											Open your authenticator mobile app. If you don't have it yet, you can install any of the following apps:
											<div style="margin-top: 10px;padding-left: 20px">
											&#8674; <a class="app_link" href="http://support.google.com/accounts/bin/answer.py?hl=en&answer=1066447" target="_blank">Google Authenticator</a> (Android/iPhone/BlackBerry)<br/>
											&#8674; <a class="app_link" href="http://guide.duosecurity.com/third-party-accounts" target="_blank">Duo Mobile</a> (Android/iPhone)<br/>
											&#8674; <a class="app_link" href="http://www.amazon.com/gp/product/B0061MU68M" target="_blank">Amazon AWS MFA</a> (Android)<br/>
											&#8674; <a class="app_link" href="http://www.windowsphone.com/en-US/apps/021dd79f-0598-e011-986b-78e7d1fa76f8" target="_blank">Authenticator</a> (Windows Phone 7)
											</div>
										</li>
										<li class="tsv_setup_title">Step 2. Scan Barcode</li>
										<li>
											Use your authenticator app to scan the barcode below:
											<div style="width: 80%;padding: 20px;text-align: center">
												<div id="qrcode"></div>
											</div>
											or you can enter this secret key manually: <strong><?php echo $tsv_secret; ?></strong>
										</li>
										<li class="tsv_setup_title">Step 3. Verify Code</li>
										<li>
											Once your app is configured, enter the <strong>six-digit security code</strong> generated by your app.
										</li>
									</ul>
								</li>
								<li id="li_tsv_code">		
									<label class="desc" for="tsv_code">Enter your 6-digit code</label>
									<div>
										<input id="tsv_code" style="width: 125px" name="tsv_code" class="element text medium" type="text" maxlength="255" value="<?php echo htmlspecialchars($username); ?>"/> 
									</div>
								</li>		
					    		<li id="li_submit" class="buttons" style="overflow: auto;margin-top: 5px;margin-bottom: 10px">
					    			<input type="hidden" name="submit" id="submit" value="1">
							    	<button type="submit" class="bb_button bb_green" id="submit_button" name="submit_button" style="float: left;border-radius: 4px">
								        <span class="icon-keyhole"></span>
								        Verify Code
								    </button>
								</li>
							</ul>
							</form>	
					</div>
					
				</div>
     
        	</div>  		 
		</div>

<?php
	$footer_data =<<<EOT
<script type="text/javascript" src="js/qrcode/qrcode.js{$mf_version_tag}"></script>
<script>
	$(function(){
		var qrcode = new QRCode(document.getElementById("qrcode"), { width : 140, height : 140 });
		qrcode.makeCode('{$totp_data}');
	});	
</script>
EOT;
	require('includes/footer.php'); 
?>