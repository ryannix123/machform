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
	
	$dbh = mf_connect_db();
	
	if(empty($_POST['form_id'])){
		die("Error! You can't open this file directly");
	}

	//check for max_input_vars
	mf_init_max_input_vars();

	$form_id = (int) trim($_POST['form_id']);
	$csrf_token = trim($_POST['csrf_token']);

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_form permission
		if(empty($user_perms['edit_form'])){
			die("Access Denied. You don't have permission to edit this form.");
		}
	}
	
	$approver_rule_properties = mf_sanitize($_POST['approver_rule_properties'] ?? '');
	$approver_rule_conditions = mf_sanitize($_POST['approver_rule_conditions'] ?? '');
	$workflow_type			  = mf_sanitize($_POST['workflow_type'] ?? '');
	$parallel_workflow		  = mf_sanitize($_POST['parallel_workflow'] ?? '');

	if(empty($workflow_type)){
		$workflow_type = 'parallel';
	}
	if(empty($parallel_workflow)){
		$parallel_workflow = 'any';
	}
	
	//save into ap_approval_settings table
	
	//get current revision_no first
	$query = "SELECT revision_no FROM ".MF_TABLE_PREFIX."approval_settings WHERE form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	if(empty($row)){
		$new_revision_no = 1;
	}else{
		$new_revision_no = $row['revision_no'] + 1;
	}

	$query = "DELETE FROM ".MF_TABLE_PREFIX."approval_settings WHERE form_id = ?";
	$params = array($form_id);
	mf_do_query($query,$params,$dbh);

	$query = "INSERT INTO 
						 ".MF_TABLE_PREFIX."approval_settings(
						 	form_id,
						 	workflow_type,
						 	parallel_workflow,
						 	revision_no,
						 	revision_date,
						 	last_revised_by
						 ) VALUES(
						 	?,
						 	?,
						 	?,
						 	?,
						 	now(),
						 	?
						 );";
	$params = array($form_id,$workflow_type,$parallel_workflow,$new_revision_no,$_SESSION['mf_user_id']);
	mf_do_query($query,$params,$dbh);

	//save into ap_approvers and ap_approvers_conditions
		
	//save into ap_approvers table
	$query = "delete from ".MF_TABLE_PREFIX."approvers where form_id=?";
	$params = array($form_id);
	mf_do_query($query,$params,$dbh);

	if(!empty($approver_rule_properties)){
		foreach ($approver_rule_properties as $data) {
			$query = "insert into `".MF_TABLE_PREFIX."approvers`(form_id,user_id,user_position,rule_all_any) values(?,?,?,?)";
			$params = array($form_id,$data['user_id'],$data['user_position'],strtolower($data['rule_all_any']));
			mf_do_query($query,$params,$dbh);

			//when a user is being added as an approver, the user will automatically assigned "edit entry" permission
			$query = "select count(*) record_exist from ".MF_TABLE_PREFIX."permissions where form_id = ? and user_id = ?";
			$params = array($form_id,$data['user_id']);
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);
			
			if(!empty($row['record_exist'])){
				$query = "update ".MF_TABLE_PREFIX."permissions set edit_entries=1 where form_id=? and user_id=?";
				$params = array($form_id,$data['user_id']);
				mf_do_query($query,$params,$dbh);
			}else{
				$params = array($form_id, $data['user_id'], 0, 0,  1, 1);
				$query = "INSERT INTO 
									`".MF_TABLE_PREFIX."permissions` (
															`form_id`, 
															`user_id`, 
															`edit_form`, 
															`edit_report`, 
															`edit_entries`, 
															`view_entries`) 
								VALUES (?, ?, ?, ?, ?, ?);";
				mf_do_query($query,$params,$dbh);
			}
		}
	}
	
	//save into ap_approvers_conditions table
	$query = "delete from ".MF_TABLE_PREFIX."approvers_conditions where form_id=?";
	$params = array($form_id);
	mf_do_query($query,$params,$dbh);

	if(!empty($approver_rule_conditions)){
		$query = "insert into `".MF_TABLE_PREFIX."approvers_conditions`(form_id,target_user_id,element_name,rule_condition,rule_keyword) values(?,?,?,?,?)";
		foreach ($approver_rule_conditions as $data) {
			$target_user_id = (int) $data['target_user_id'];
			$element_name	   = strtolower(trim($data['element_name']));
			$rule_condition    = strtolower(trim($data['condition']));
			$rule_keyword	   = trim($data['keyword']);

			$params = array($form_id,$target_user_id,$element_name,$rule_condition,$rule_keyword);
			mf_do_query($query,$params,$dbh);
		}
	}
	
	
	

	$_SESSION['MF_SUCCESS'] = 'Approval workflow has been saved.';
   	echo '{ "status" : "ok", "form_id" : "'.$form_id.'" }';
   
?>