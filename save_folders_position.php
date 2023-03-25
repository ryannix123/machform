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
	$folder_positions = $_POST['folder_positions'];

	$dbh = mf_connect_db();
	
	if(!empty($folder_positions) && is_array($folder_positions)){
		$folder_position = 1;
		foreach ($folder_positions as $folder_id) {
			$folder_id = (int) $folder_id;
			
			$query = "UPDATE ".MF_TABLE_PREFIX."folders SET folder_position = ? WHERE folder_id = ?";
			$params = array($folder_position,$folder_id);
			mf_do_query($query,$params,$dbh);
			$folder_position++;
		}
	}
	
	$response_data = new stdClass();
	$response_data->status = "ok";
	
	$response_json = json_encode($response_data);
	
	header("Content-Type: application/json");
	echo $response_json;
?>