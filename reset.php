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
	require('lib/password-hash.php');

	$ssl_suffix  = mf_get_ssl_suffix();

	$dbh 		 = mf_connect_db();
	
	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);

	if(empty($_GET['q']) && empty($_SESSION['is_valid_reset_link'])){
		die("Invalid request");
	}

	if(!empty($_GET['q'])){
		$query_string = trim($_GET['q']);
		parse_str(base64_decode($query_string),$params);

		$user_id 	= $params['user_id'];
		$reset_hash = $params['reset_hash'];
		$_SESSION['mf_reset_user_id'] 	 = $user_id;
		$_SESSION['is_valid_reset_link'] = false;

		$query = "SELECT 
						user_email,
						user_fullname  
					FROM 
						".MF_TABLE_PREFIX."users 
				   WHERE 
				   		user_id = ? and 
				   		`status`= 1 and 
				   		reset_hash = ? and 
				   		(reset_date > now() - interval 60 minute)";
		$params = array($user_id,$reset_hash);
	
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);	
		if(!empty($row['user_email'])){
			$_SESSION['is_valid_reset_link'] = true;

			$user_fullname_array = explode(' ',$row['user_fullname']);
			$user_firstname 	 = $user_fullname_array[0];
		}else{
			$_SESSION['MF_LOGIN_ERROR'] = 'Sorry, the link is invalid or expired.';
		}	
	}

	if(!empty($_POST['submit']) && $_SESSION['is_valid_reset_link'] === true){
		$input = mf_sanitize($_POST);

		if(empty($input['new_password']) || empty($input['confirm_new_password'])){
			$_SESSION['MF_LOGIN_ERROR'] = 'Error! Please fill out both password fields.';
		}else{
			if($input['new_password'] == $input['confirm_new_password']){
				$user_id = $_SESSION['mf_reset_user_id'];
				//password is valid
				//reset the old password and login the user to the system
				$hasher = new PasswordHash(8, FALSE);
				$new_password_hash = $hasher->HashPassword($input['new_password']);
				
				$query = "UPDATE ".MF_TABLE_PREFIX."users SET user_password = ?,reset_hash=NULL,reset_date=NULL WHERE user_id = ?";
				$params = array($new_password_hash,$user_id);
				mf_do_query($query,$params,$dbh);

				//reset login counter
				$query = "UPDATE ".MF_TABLE_PREFIX."users 
								  SET 
								  	 login_attempt_date = NULL,
								  	 login_attempt_count = 0 
							    WHERE 
							    	 user_id = ?";
				$params = array($user_id);
				mf_do_query($query,$params,$dbh);

				unset($_SESSION['is_valid_reset_link']);
				unset($_SESSION['mf_reset_user_id']);

				//regenerate session id for protection against session fixation
				session_regenerate_id();

				$query  = "SELECT 
							`user_id`,
							`priv_administer`,
							`priv_new_forms`,
							`priv_new_themes` 
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

				$query  = "UPDATE ".MF_TABLE_PREFIX."users set last_login_date=?,last_ip_address=? WHERE `user_id`=?";
				$params = array($last_login_date,$last_ip_address,$user_id);
				mf_do_query($query,$params,$dbh);

				$_SESSION['MF_SUCCESS'] = 'Your new password has been saved.';

				header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/manage_forms.php");
				exit;
			}else{
				$_SESSION['MF_LOGIN_ERROR'] = 'Error! Passwords do not match. <br/>Please re-enter your new password.';
			}
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Reset Password - MachForm Admin Panel</title>
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
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css<?php echo $mf_version_tag; ?>" rel="stylesheet" />
<link type="text/css" href="css/edit_form.css<?php echo $mf_version_tag; ?>" rel="stylesheet" />
<link type="text/css" href="js/datepick/smoothness.datepick.css<?php echo $mf_version_tag; ?>" rel="stylesheet" />
<link href="css/override.css<?php echo $mf_version_tag; ?>" rel="stylesheet" type="text/css" />
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
						<h3>Reset Password</h3>
						<p><?php echo "Hi {$user_firstname}! "; ?>Enter your new password below</p>
						<div style="clear:both; border-bottom: 1px dotted #CCCCCC;margin-top: 15px"></div>
					</div>
					<?php ?>
					<div style="margin-bottom: 10px;margin-top: 10px">
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

								<?php if($_SESSION['is_valid_reset_link'] === true){ ?>
								<li id="li_password">
									<label class="desc" for="new_password">New password </label>
									<div>
										<input id="new_password" name="new_password" autocomplete="off" class="element text large" type="password" maxlength="255" value=""/> 
									</div> 
								</li>		
								<li id="li_password">
									<label class="desc" for="confirm_new_password">Confirm new password </label>
									<div>
										<input id="confirm_new_password" name="confirm_new_password" autocomplete="off" class="element text large" type="password" maxlength="255" value=""/> 
									</div> 
								</li>
					    		<li id="li_submit" class="buttons" style="overflow: auto">
					    			<input type="hidden" name="submit" id="submit" value="1">
							    	<button type="submit" class="bb_button bb_green" id="submit_button" name="submit_button" style="float: left;border-radius: 4px">
								        <span class="icon-keyhole"></span>
								        Change Password
								    </button>
								</li>
								<?php } ?>

							</ul>
							</form>	
					</div>

				</div>
     
        	</div>  		 
		</div>		

<?php
	require('includes/footer.php'); 
?>