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

	$csrf_token 	= trim($_POST['csrf_token']);

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);

	//check user privileges, is this user has privilege to administer MachForm?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		die("You don't have permission to administer MachForm.");
	}

	$_SESSION['filter_users'] = array();
	unset($_SESSION['filter_users']);
	
	
	$response_data = new stdClass();
	$response_data->status    	= "ok";
	
	
	$response_json = json_encode($response_data);
	
	echo $response_json;
?>