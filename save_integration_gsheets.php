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
	
	$form_id 			= (int) trim($_POST['form_id']);
	$column_preferences = mf_sanitize($_POST['col_pref']);
	$user_id 			= (int) $_SESSION['mf_user_id'];
	$csrf_token 		= trim($_POST['csrf_token']);
	$gsheet_delay_notification_until_paid 		= (int) trim($_POST['gsheet_delay_notification_until_paid']);
	$gsheet_delay_notification_until_approved 	= (int) trim($_POST['gsheet_delay_notification_until_approved']);

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);
	
	if(empty($form_id)){
		die("This file can't be opened directly.");
	}

	$dbh = mf_connect_db();

	//update ap_integrations table
	$column_name_array = array();
	if(!empty($column_preferences)){
		foreach($column_preferences as $data){
			$column_name_array[] = $data['name'];
		}
	}
	$column_name_joined = implode(',', $column_name_array);

	//Each time we update the setting, Google Sheets integration must create a new sheet to keep columns consistency
	$query = "UPDATE ".MF_TABLE_PREFIX."integrations 
				 SET 
				 	gsheet_elements=?,
				 	gsheet_delay_notification_until_paid=?,
				 	gsheet_delay_notification_until_approved=?,
				 	gsheet_create_new_sheet=1 
			   WHERE 
			   		form_id=?";
	$params = array($column_name_joined,$gsheet_delay_notification_until_paid,$gsheet_delay_notification_until_approved,$form_id);
	mf_do_query($query,$params,$dbh);

	$response_data = new stdClass();
	$response_data->status    	= "ok";
	$response_data->form_id 	= $form_id;
	
	$response_json = json_encode($response_data);
	
	$_SESSION['MF_SUCCESS'] = 'Google Sheets integration settings has been saved.';

	echo $response_json;
?>