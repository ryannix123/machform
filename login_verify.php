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

	
	//check for verify session, if not exist, redirect back to login page
	if(empty($_SESSION['mf_tsv_verify'])){
		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/index.php");
		exit;
	}
	
	//verify security code
	if(!empty($_POST['submit'])){
		$input 	  = mf_sanitize($_POST);
		
		$tsv_code = $input['tsv_code'];
		$user_id  = $_SESSION['mf_tsv_verify'];

		$query  = "SELECT 
						`priv_administer`,
						`priv_new_forms`,
						`priv_new_themes`,
						`tsv_secret`,
						`tsv_code_log`,
						`login_attempt_date`,
						`login_attempt_count`     
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
		$tsv_secret 		  = $row['tsv_secret'];
		$tsv_code_log 		  = $row['tsv_code_log'];
		$tsv_code_log_array   = explode(',', $tsv_code_log);
		$login_attempt_date   = $row['login_attempt_date'];
		$login_attempt_count  = $row['login_attempt_count'];

		//first make sure the tsv code haven't being used previously
		if(in_array($tsv_code, $tsv_code_log_array)){
			$_SESSION['MF_LOGIN_ERROR'] = 'Error! Code has already been used.';
		}else{
			$authenticator = new PHPGangsta_GoogleAuthenticator();
			$tsv_result    = $authenticator->verifyCode($tsv_secret, $tsv_code, 8);  //8 means 4 minutes before or after
			if($tsv_result === true){
				$login_is_valid = true;
			}else{
				$login_is_valid = false;
				$_SESSION['MF_LOGIN_ERROR'] = 'Error! Incorrect code.';

				//if account locking enabled, increase the login attempt counter
				if(!empty($mf_settings['enable_account_locking']) && !empty($user_id)){
					$query = "UPDATE ".MF_TABLE_PREFIX."users 
								  SET 
								  	 login_attempt_date=?,
								  	 login_attempt_count=(login_attempt_count + 1) 
							    WHERE 
							    	 user_id = ?";
					$new_login_attempt_date = date("Y-m-d H:i:s");
					$params = array($new_login_attempt_date,$user_id);
					mf_do_query($query,$params,$dbh);
				}
			}

			//check for account locking status
			if(!empty($mf_settings['enable_account_locking']) && !empty($user_id)){
				$account_lock_period	   = (int) $mf_settings['account_lock_period'];
				$account_lock_max_attempts = (int) $mf_settings['account_lock_max_attempts'];

				$account_blocked_message   = "Sorry, this account is temporarily blocked. Please try again after {$account_lock_period} minutes.";

				//check the lock period
				$login_attempt_date 	   = strtotime($login_attempt_date);
				$account_lock_expiry_date  = $login_attempt_date + (60 * $account_lock_period); 
				$current_datetime 		   = strtotime(date("Y-m-d H:i:s"));
				
				//if lock period still valid, check max attempts
				if($current_datetime < $account_lock_expiry_date){
				
					//if max attempts already exceed the limit, block the user
					if($login_attempt_count >= $account_lock_max_attempts){
						$login_is_valid = false;
						$_SESSION['MF_LOGIN_ERROR'] = $account_blocked_message;
					}
				}else{
					
					//else if lock period already expired
					$query = "UPDATE ".MF_TABLE_PREFIX."users 
								  SET 
								  	 login_attempt_date = ?,
								  	 login_attempt_count = ? 
							    WHERE 
							    	 user_id = ?";
					
					//if password is correct, reset to zero
					//else if password is incorrect, set counter to 1
					if($login_is_valid){
						$login_attempt_date  = '';
						$login_attempt_count = 0;
					}else{
						$login_attempt_date  = date("Y-m-d H:i:s");
						$login_attempt_count = 1;
					}
					
					$params = array($login_attempt_date,$login_attempt_count,$user_id);
					mf_do_query($query,$params,$dbh);
				}
			}

			//if login is validated
			if($login_is_valid){
				//save the code into the log
				if(count($tsv_code_log_array) >= 10){
					array_shift($tsv_code_log_array);
				}
				
				$tsv_code_log_array[] = $tsv_code;
				$tsv_code_log = implode(',', $tsv_code_log_array);
				$remember_me  = $_SESSION['mf_tsv_verify_remember_me'];
				
				//invalidate mf_tsv_verify session
				$_SESSION['mf_tsv_verify'] = '';
				$_SESSION['mf_tsv_verify_remember_me'] = '';
				unset($_SESSION['mf_tsv_verify']);
				unset($_SESSION['mf_tsv_verify_remember_me']);

				//reset login counter
				$query = "UPDATE ".MF_TABLE_PREFIX."users 
								  SET 
								  	 login_attempt_date = NULL,
								  	 login_attempt_count = 0 
							    WHERE 
							    	 user_id = ?";
				$params = array($user_id);
				mf_do_query($query,$params,$dbh);

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

				$query  = "UPDATE ".MF_TABLE_PREFIX."users set last_login_date=?,last_ip_address=?,tsv_code_log=? WHERE `user_id`=?";
				$params = array($last_login_date,$last_ip_address,$tsv_code_log,$user_id);
				mf_do_query($query,$params,$dbh);

				//if the user select the "remember me option"
				//set the cookie and make it active for the next 30 days
				if(!empty($remember_me)){
					$hasher 	   = new PasswordHash(8, FALSE);
					$cookie_hash = $hasher->HashPassword(mt_rand()); //generate random hash and save it into ap_users table

					$query = "update ".MF_TABLE_PREFIX."users set cookie_hash=? where `user_id`=?";
			   		$params = array($cookie_hash,$user_id);
			   		mf_do_query($query,$params,$dbh);

			   		//send the cookie
			   		setcookie('mf_remember',$cookie_hash, time()+3600*24*30, "/");
				}

				if(!empty($_SESSION['prev_referer'])){
					$next_page = $_SESSION['prev_referer'];
						
					unset($_SESSION['prev_referer']);
					header("Location: ".$next_page);
						
					exit;
				}else{
					header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/manage_forms.php");
					exit;
				}
			}
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
						<h3>Enter Security Code</h3>
						<p>Enter the code generated by your mobile application</p>
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

								<li id="li_email_address">
													
									<label class="desc" for="tsv_code">Enter your 6-digit code</label>
									<div>
										<input id="tsv_code" autocomplete="off" style="width: 125px" name="tsv_code" class="element text medium" type="text" maxlength="255" value=""/> 
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
	
	require('includes/footer.php'); 
?>