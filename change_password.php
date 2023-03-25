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
	require('lib/password-hash.php');
	require('lib/swift-mailer/swift_required.php');
	
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);
	
	$input = mf_sanitize($_POST);

	if(empty($input['np']) && empty($input['user_id'])){
		die("Error! You can't open this file directly");
	}else{
		$new_password_plain = $input['np'];
		$user_id = (int) $input['user_id'];
		$send_login_info = (int) ($input['send_login'] ?? 0);
		$csrf_token 	 = $input['csrf_token'];
	}

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);
	
	//check permissions and privileges
	//normal user should only be able to change his own password
	//check user privileges, is this user has privilege to administer MachForm?
	if(!empty($_SESSION['mf_user_privileges']['priv_administer'])){
		//this is administrator, allowed to change the password of any other user's password
		//except the main administrator password
		if($user_id == 1 && $_SESSION['mf_user_id'] != 1){
			die("Access Denied. You don't have permission to change Main Administrator password.");
		}
	}else{
		$user_id = $_SESSION['mf_user_id']; //this is normal user, make sure he only change his own password
	}

	$hasher = new PasswordHash(8, FALSE);
	$new_password_hash = $hasher->HashPassword($new_password_plain);
	
	$query = "UPDATE ".MF_TABLE_PREFIX."users SET user_password = ? WHERE user_id = ?";
	$params = array($new_password_hash,$user_id);
	mf_do_query($query,$params,$dbh);

	//if send_login parameter exist, resend the login information to user
	if(!empty($send_login_info)){
		mf_send_login_info($dbh,$user_id,$new_password_plain);
	}

   	echo '{"status" : "ok"}';
	
?>