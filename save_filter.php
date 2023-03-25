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
	
	$form_id 				 = (int) trim($_POST['form_id']);
	$filter_properties_array = mf_sanitize($_POST['filter_prop']);
	$filter_type 			 = mf_sanitize($_POST['filter_type']);
	$user_id				 = (int) $_SESSION['mf_user_id'];
	
	$incomplete_entries = isset($_POST['incomplete_entries']) ? (int) $_POST['incomplete_entries'] : false; //if this is operation targetted to incomplete entries, this will contain '1'
	if(empty($incomplete_entries)){
		$incomplete_entries = 0;
	}

	if(empty($form_id)){
		die("This file can't be opened directly.");
	}

	$dbh = mf_connect_db();
	
	//first delete all previous filter
	$query = "delete from `".MF_TABLE_PREFIX."form_filters` where form_id=? and user_id=? and incomplete_entries=?";
	$params = array($form_id,$user_id,$incomplete_entries);
	mf_do_query($query,$params,$dbh);
	
	//save the new filters
	$query = "insert into `".MF_TABLE_PREFIX."form_filters`(form_id,user_id,element_name,filter_condition,filter_keyword,incomplete_entries) values(?,?,?,?,?,?)";

	foreach($filter_properties_array as $data){
		$params = array($form_id,$user_id,$data['element_name'],$data['condition'],$data['keyword'],$incomplete_entries);
		mf_do_query($query,$params,$dbh);
	}

	$query = "select count(user_id) pref_count from ".MF_TABLE_PREFIX."entries_preferences where form_id=? and `user_id`=?";
		
	$params = array($form_id,$user_id);
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
		
	$pref_count = $row['pref_count'];
	if(!empty($pref_count)){ //update existing record within ap_entries_preferences
		
		//update record
		if(empty($incomplete_entries)){
			$query = "update ".MF_TABLE_PREFIX."entries_preferences set entries_enable_filter=1,entries_filter_type=? where form_id=? and user_id=?";
		}else{
			$query = "update ".MF_TABLE_PREFIX."entries_preferences set entries_incomplete_enable_filter=1,entries_incomplete_filter_type=? where form_id=? and user_id=?";	
		}

		$params = array($filter_type,$form_id,$user_id);
		mf_do_query($query,$params,$dbh);
	}else{ //insert new one
		if(empty($incomplete_entries)){
			$query = "insert into ".MF_TABLE_PREFIX."entries_preferences(`entries_enable_filter`,`entries_filter_type`,`form_id`,`user_id`) values(?,?,?,?)";
		}else{
			$query = "insert into ".MF_TABLE_PREFIX."entries_preferences(`entries_incomplete_enable_filter`,`entries_incomplete_filter_type`,`form_id`,`user_id`) values(?,?,?,?)";
		}

		$params = array(1,$filter_type,$form_id,$user_id);
		mf_do_query($query,$params,$dbh);
	}
	
	$response_data = new stdClass();
	$response_data->status    	= "ok";
	$response_data->form_id 	= $form_id;
	
	$response_json = json_encode($response_data);
	
	echo $response_json;
?>