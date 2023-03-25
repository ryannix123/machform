<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	require('includes/init.php');
	
	require('config.php');
	require('includes/language.php');
	require('includes/db-core.php');
	require('includes/helper-functions.php');
	require('includes/check-session.php');

	require('includes/filter-functions.php');
	require('includes/entry-functions.php');
	require('includes/post-functions.php');
	require('lib/dompdf/autoload.inc.php');
	require('lib/google-api-client/vendor/autoload.php');
	require('lib/HttpClient.class.php');
	require('lib/swift-mailer/swift_required.php');
	require('includes/users-functions.php');
	
	$form_id  = (int) trim($_POST['form_id']);
	$entry_id = (int) trim($_POST['entry_id']);
	$approval_state = trim($_POST['approval_state'] ?? '');
	$approval_note 	= mf_sanitize($_POST['approval_note']);
	$show_message 	= (int) ($_POST['show_message'] ?? 0);
	$csrf_token 	= trim($_POST['csrf_token']);

	if(empty($form_id) || empty($entry_id) || empty($approval_state)){
		die("Invalid parameters.");
	}

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);
	
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_entries permission
		if(empty($user_perms['edit_entries'])){
			die("Access Denied. You don't have permission to access this page.");
		}
	}

	//check to ensure only authorized approvers able to execute this file
	//this user must be listed within 'approval_queue_user_id'
	$query = "SELECT approval_queue_user_id FROM ".MF_TABLE_PREFIX."form_{$form_id} WHERE `id`=?";
	$params = array($entry_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	$queue_user_id_array = explode('|', $row['approval_queue_user_id']);
	
	if(!in_array($_SESSION['mf_user_id'], $queue_user_id_array)){
		die("This entry has been approved/denied already.");
	}

	//sign the approval
	$signature_params = array();
	$signature_params['user_id'] 	= $_SESSION['mf_user_id'];
	$signature_params['ip_address'] = $_SERVER['REMOTE_ADDR'];
	$signature_params['approval_state']	= $approval_state;
	$signature_params['approval_note']	= $approval_note;

	$approval_result = mf_approval_sign($dbh,$form_id,$entry_id,$signature_params);

	if(!empty($show_message)){
		$_SESSION['MF_SUCCESS'] = 'Approval status has been updated.';
	}

	$response_data = new stdClass();
	$response_data->status    		 = "ok";
	$response_data->approval_result  = $approval_result;
	$response_data->entry_id 		 = $entry_id;
	$response_data->form_id 		 = $form_id;  
	
	$response_json = json_encode($response_data);

   	echo $response_json;

?>