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

	$_POST = json_decode(file_get_contents('php://input'), true);	
	$dbh = mf_connect_db();
	
	$folder_id = (int) $_POST['folder_id'];
	
	//delete from ap_folders table
	//don't allow deleting folder with id == 1 ('all forms' folder)
	if($folder_id != 1){
		$query = "delete from ".MF_TABLE_PREFIX."folders where folder_id=? and user_id=?";
		$params = array($folder_id,$_SESSION['mf_user_id']);
		mf_do_query($query,$params,$dbh);

		$query = "delete from ".MF_TABLE_PREFIX."folders_conditions where folder_id=? and user_id=?";
		$params = array($folder_id,$_SESSION['mf_user_id']);
		mf_do_query($query,$params,$dbh);
	}
  
   	$response_data = new stdClass();
	$response_data->status = "ok";
	
	$response_json = json_encode($response_data);
	
	header("Content-Type: application/json");
	echo $response_json;
	
?>