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
	
	$form_id 				= (int) trim($_POST['form_id']);
	$user_id 				= (int) $_SESSION['mf_user_id'];
	$csrf_token 			= trim($_POST['csrf_token']);
	$integration_properties = mf_sanitize($_POST['integration_properties']);

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);
	
	if(empty($form_id)){
		die("This file can't be opened directly.");
	}

	$dbh = mf_connect_db();

	//check for max_input_vars
	mf_init_max_input_vars();

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_form permission
		if(empty($user_perms['edit_form'])){
			die("Access Denied. You don't have permission to edit this form.");
		}
	}

	$gcal_event_title 		 = $integration_properties['gcal_event_title'];
	$gcal_calendar_id 		 = $integration_properties['gcal_calendar_id'];
	$gcal_event_desc 		 = $integration_properties['gcal_event_desc'];
	$gcal_event_location 	 = $integration_properties['gcal_event_location'];
	$gcal_start_date_type 	 = $integration_properties['gcal_start_date_type'];//'datetime' or 'element'
	$gcal_start_time_type	 = $integration_properties['gcal_start_time_type']; //'datetime' or 'element'
	$gcal_start_date_element = (int) $integration_properties['gcal_start_date_element']; //integer
	$gcal_start_time_element = (int) $integration_properties['gcal_start_time_element']; //integer
	$gcal_start_date 		 = $integration_properties['gcal_start_date']; //yyyy-mm-dd
	$gcal_start_time 		 = $integration_properties['gcal_start_time']; //hh:ii:ss
	$gcal_end_date_type 	 = $integration_properties['gcal_end_date_type'];//'datetime' or 'element'
	$gcal_end_time_type	 	 = $integration_properties['gcal_end_time_type']; //'datetime' or 'element'
	$gcal_end_date_element 	 = (int) $integration_properties['gcal_end_date_element']; //integer
	$gcal_end_time_element 	 = (int) $integration_properties['gcal_end_time_element']; //integer
	$gcal_end_date 		 	 = $integration_properties['gcal_end_date']; //yyyy-mm-dd
	$gcal_end_time 		 	 = $integration_properties['gcal_end_time']; //hh:ii:ss
	$gcal_event_allday 		 = (int) $integration_properties['gcal_event_allday'];
	$gcal_duration_type 		 = $integration_properties['gcal_duration_type']; //period,datetime
	$gcal_duration_period_length = (int) $integration_properties['gcal_duration_period_length']; //integer
	$gcal_duration_period_unit 	 = $integration_properties['gcal_duration_period_unit']; //minute,hour,day
	$gcal_attendee_email 		 = (int) $integration_properties['gcal_attendee_email']; //integer
	$gcal_delay_notification_until_paid 	= (int) $integration_properties['gcal_delay_notification_until_paid']; //integer
	$gcal_delay_notification_until_approved = (int) $integration_properties['gcal_delay_notification_until_approved']; //integer

	//construct gcal_start_datetime, based on start date and start time, each can be fixed-datetime or dynamic from fields
	if($gcal_start_date_type == 'datetime'){
		//if start date is a fixed date
		$gcal_start_date_element = null;
		
		if($gcal_start_time_type == 'datetime'){
			$gcal_start_datetime = $gcal_start_date.' '.$gcal_start_time;
		}else if($gcal_start_time_type == 'element'){
			$gcal_start_datetime = $gcal_start_date.' '.'00:00:00';
		}
	}else if($gcal_start_date_type == 'element'){
		//if start date is based on field value
		$gcal_start_date = '0000-00-00';

		if($gcal_start_time_type == 'datetime'){
			$gcal_start_datetime = $gcal_start_date.' '.$gcal_start_time;
		}else if($gcal_start_time_type == 'element'){
			$gcal_start_datetime = $gcal_start_date.' '.'00:00:00';
		}
	}

	//construct gcal_end_datetime, based on end date and end time, each can be fixed-datetime or dynamic from fields
	if($gcal_end_date_type == 'datetime'){
		//if end date is a fixed date
		$gcal_end_date_element = null;
		
		if($gcal_end_time_type == 'datetime'){
			$gcal_end_datetime = $gcal_end_date.' '.$gcal_end_time;
		}else if($gcal_end_time_type == 'element'){
			$gcal_end_datetime = $gcal_end_date.' '.'00:00:00';
		}
	}else if($gcal_end_date_type == 'element'){
		//if end date is based on field value
		$gcal_end_date = '0000-00-00';

		if($gcal_end_time_type == 'datetime'){
			$gcal_end_datetime = $gcal_end_date.' '.$gcal_end_time;
		}else if($gcal_end_time_type == 'element'){
			$gcal_end_datetime = $gcal_end_date.' '.'00:00:00';
		}
	}


	//update ap_integrations table
	$query = "UPDATE ".MF_TABLE_PREFIX."integrations 
				 SET  
				 	gcal_calendar_id=?,
				 	gcal_event_title=?,
				 	gcal_event_desc=?,
				 	gcal_event_location=?,
				 	gcal_start_date_type=?,
				 	gcal_start_time_type=?,
				 	gcal_start_datetime=?,
				 	gcal_start_date_element=?,
				 	gcal_start_time_element=?,
				 	gcal_end_date_type=?,
				 	gcal_end_time_type=?,
				 	gcal_end_datetime=?,
				 	gcal_end_date_element=?,
				 	gcal_end_time_element=?,
				 	gcal_event_allday=?,
				 	gcal_duration_type=?,
				 	gcal_duration_period_length=?,
				 	gcal_duration_period_unit=?,
				 	gcal_attendee_email=?,
				 	gcal_delay_notification_until_paid=?,
				 	gcal_delay_notification_until_approved=?  
			   WHERE 
			   		form_id=?";
	$params = array($gcal_calendar_id,
					$gcal_event_title,
					$gcal_event_desc,
					$gcal_event_location,
					$gcal_start_date_type,
					$gcal_start_time_type,
					$gcal_start_datetime,
					$gcal_start_date_element,
					$gcal_start_time_element,
					$gcal_end_date_type,
					$gcal_end_time_type,
					$gcal_end_datetime,
					$gcal_end_date_element,
					$gcal_end_time_element,
					$gcal_event_allday,
					$gcal_duration_type,
					$gcal_duration_period_length,
					$gcal_duration_period_unit,
					$gcal_attendee_email,
					$gcal_delay_notification_until_paid,
					$gcal_delay_notification_until_approved,
					$form_id);
	mf_do_query($query,$params,$dbh);
	
	$response_data = new stdClass();
	$response_data->status    	= "ok";
	$response_data->form_id 	= $form_id;
	
	$response_json = json_encode($response_data);
	
	$_SESSION['MF_SUCCESS'] = 'Google Calendar integration settings has been saved.';

	echo $response_json;
?>