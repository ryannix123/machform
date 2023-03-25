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
	require('includes/entry-functions.php');
	require('includes/users-functions.php');

	$form_id = (int) trim($_POST['form_id']);
	$csrf_token = trim($_POST['csrf_token']);

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);

	if(empty($form_id)){
		die("This file can't be opened directly.");
	}

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_entries permission
		if(empty($user_perms['edit_entries'])){
			die("Access Denied. You don't have permission to edit this entry.");
		}
	}

	//update ap_integrations table
	$query = "UPDATE `".MF_TABLE_PREFIX."integrations` 
				 SET 
				 	gsheet_integration_status=0,
				 	gsheet_spreadsheet_id='',
				 	gsheet_spreadsheet_url='',
				 	gsheet_elements='',
				 	gsheet_create_new_sheet=0,
				 	gsheet_refresh_token='',
				 	gsheet_access_token='',
				 	gsheet_token_create_date='0000-00-00 00:00:00',
				 	gsheet_delay_notification_until_paid=1,
				 	gsheet_delay_notification_until_approved=1,
				 	gsheet_linked_user_id=NULL 
			   WHERE form_id=?";
	$params = array($form_id);
	mf_do_query($query,$params,$dbh);

	
	$response_data = new stdClass();
	$response_data->status    	= "ok";
	$response_data->form_id 	= $form_id;

	$response_json = json_encode($response_data);

	$_SESSION['MF_SUCCESS'] = 'Google Sheets integration has been removed.';
		
	echo $response_json;
?>