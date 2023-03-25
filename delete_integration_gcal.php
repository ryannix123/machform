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
				 	gcal_integration_status=0,
				 	gcal_refresh_token='',
				 	gcal_access_token='',
				 	gcal_token_create_date='0000-00-00 00:00:00',
				 	gcal_calendar_id=NULL,
				 	gcal_event_title='',
				 	gcal_event_desc='',
				 	gcal_event_location='',
				 	gcal_event_allday=1,
				 	gcal_start_datetime=NULL,
				 	gcal_start_date_element=NULL,
				 	gcal_start_time_element=NULL,
				 	gcal_start_date_type='datetime',
				 	gcal_start_time_type='datetime',
				 	gcal_end_datetime=NULL,
				 	gcal_end_date_element=NULL,
				 	gcal_end_time_element=NULL,
				 	gcal_end_time_type='datetime',
				 	gcal_end_date_type='datetime',
				 	gcal_duration_type='period',
				 	gcal_duration_period_length=30,
				 	gcal_duration_period_unit='minute',
				 	gcal_attendee_email=NULL,
				 	gcal_linked_user_id=NULL 
			   WHERE form_id=?";
	$params = array($form_id);
	mf_do_query($query,$params,$dbh);

	
	$response_data = new stdClass();
	$response_data->status    	= "ok";
	$response_data->form_id 	= $form_id;

	$response_json = json_encode($response_data);

	$_SESSION['MF_SUCCESS'] = 'Google Calendar integration has been removed.';
		
	echo $response_json;
?>