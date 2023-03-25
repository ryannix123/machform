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
	require('includes/users-functions.php');

	$csrf_token = trim($_POST['csrf_token']);

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);

	if(empty($_POST['form_id'])){
		die("Error! You can't open this file directly");
	}

	$form_id 	 = (int) $_POST['form_id'];
	$private_key = mf_sanitize($_POST['private_key']);

	$_SESSION['mf_encryption_private_key'][$form_id] = $private_key;
	
	$response_data = new stdClass();
	$response_data->status    	= 'ok';
	
	$response_json = json_encode($response_data);
	echo $response_json;
   
?>