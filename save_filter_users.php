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
	
	$filter_properties_array = mf_sanitize($_POST['filter_prop']);
	$filter_type = mf_sanitize($_POST['filter_type']);

	if(empty($filter_type) || empty($filter_properties_array)){
		die("This file can't be opened directly.");
	}

	//we only need to save the filter into session variable
	$_SESSION['filter_users'] = array();

	$i=0;
	foreach($filter_properties_array as $data){
		$_SESSION['filter_users'][$i]['element_name'] 	  = $data['element_name'];
		$_SESSION['filter_users'][$i]['filter_condition'] = $data['condition'];
		$_SESSION['filter_users'][$i]['filter_keyword']   = $data['keyword'];
		$i++;
	}
	
	$_SESSION['filter_users_type'] = $filter_type;

	$response_data = new stdClass();
	$response_data->status    	= "ok";
	
	$response_json = json_encode($response_data);
	
	echo $response_json;
?>