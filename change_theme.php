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
	require('includes/theme-functions.php');
	require('includes/users-functions.php');
		
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);
	
	
	if(empty($_POST['form_id'])){
		die("Error! You can't open this file directly");
	}

	$form_id = (int) $_POST['form_id'];
	$theme_id = (int) $_POST['theme_id'];
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
	
	
	$query = "update ".MF_TABLE_PREFIX."forms set form_theme_id=? where form_id=?";
	$params = array($theme_id,$form_id);
	mf_do_query($query,$params,$dbh);
  
   	echo '{ "status" : "ok" }';
	
?>