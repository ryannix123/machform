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
	require('includes/check-session.php');
	require('includes/users-functions.php');
	
	$dbh = mf_connect_db();
	
	$pin_folder	= (int) $_POST['pin_folder'];

	$params = array($pin_folder,$_SESSION['mf_user_id']);
	$query = "UPDATE `".MF_TABLE_PREFIX."users` SET folders_pinned=? WHERE user_id=?";
			
	mf_do_query($query,$params,$dbh);
			
	$response_data = new stdClass();
	
	$response_data->status    	= "ok";
	$response_json = json_encode($response_data);
	
	echo $response_json;
	
?>