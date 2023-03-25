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
	require('includes/users-functions.php');
	
	$form_id 	= (int) trim($_POST['form_id']);
	
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_report permission
		if(empty($user_perms['edit_report'])){
			die("You don't have permission to edit this report.");
		}
	}
	
	//generate access key
	$report_access_key = $form_id.'x'.substr(strtolower(md5(uniqid(rand(), true))),0,10);
	
	//insert into ap_reports table
	$query = "insert into `".MF_TABLE_PREFIX."reports`(form_id,report_access_key) values(?,?)";
	$params = array($form_id,$report_access_key);
	mf_do_query($query,$params,$dbh);

	$report_shared_link = "<a href=\"{$mf_settings['base_url']}report.php?key={$report_access_key}\" target=\"blank\">{$mf_settings['base_url']}report.php?key={$report_access_key}</a>";
			
	$response_data = new stdClass();
	
	$response_data->status    	= "ok";
	$response_data->report_link = $report_shared_link;
	
	$response_json = json_encode($response_data);
	
	echo $response_json;
?>