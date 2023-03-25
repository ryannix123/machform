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

	require('lib/libsodium/autoload.php');
	
	$csrf_token = trim($_POST['csrf_token']);

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);
	
	if(empty($_POST['form_id'])){
		die("Error! You can't open this file directly");
	}

	$encryption_keypair = \Sodium\crypto_box_keypair();

    $encryption_secretkey = \Sodium\crypto_box_secretkey($encryption_keypair);
    $encryption_publickey = \Sodium\crypto_box_publickey($encryption_keypair);

	
	$response_data = new stdClass();
	$response_data->status    	= 'ok';
	$response_data->public_key 	= base64_encode($encryption_publickey);
	$response_data->private_key = base64_encode($encryption_secretkey);
	
	$response_json = json_encode($response_data);
	echo $response_json;
   
?>