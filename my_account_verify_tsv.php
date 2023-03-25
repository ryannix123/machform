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
	require('lib/google-authenticator.php');
	
	$dbh = mf_connect_db();
	
	$input = mf_sanitize($_POST);
	
	$tsv_secret = $input['tsv_secret'];
	$tsv_code	= $input['tsv_code'];
	$csrf_token = $input['csrf_token'];

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);

	if(empty($tsv_secret) && empty($tsv_code)){
		die("Error! You can't open this file directly");
	}

	$authenticator = new PHPGangsta_GoogleAuthenticator();
	$tsv_result    = $authenticator->verifyCode($tsv_secret, $tsv_code, 8);  //8 means 4 minutes before or after
	
	if($tsv_result === true){
		//insert tsv code into ap_users table and enable tsv
		$user_id = $_SESSION['mf_user_id'];
		
		$query = "UPDATE ".MF_TABLE_PREFIX."users SET tsv_enable = 1,tsv_secret = ?,tsv_code_log = ? WHERE user_id = ?";
		$params = array($tsv_secret,$tsv_code,$user_id);
		mf_do_query($query,$params,$dbh);

	   	echo '{"status" : "ok"}';
	}else{
		echo '{"status" : "error"}';
	}
?>