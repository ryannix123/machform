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
	
	$form_id = (int) trim($_POST['form_id']);
	
	parse_str($_POST['widget_pos'],$parse_output); 
	$widget_positions = $parse_output['widget_pos']; //contain the positions of the widgets
	unset($el_pos);
	

	if(empty($form_id)){
		die("This file can't be opened directly.");
	}

	$dbh = mf_connect_db();
	
	//update widget positions
	$query = "UPDATE ".MF_TABLE_PREFIX."report_elements SET chart_position = ? WHERE form_id = ? AND chart_id = ?";

	$i = 1;
	foreach($widget_positions as $chart_id){
		$params = array($i,$form_id,$chart_id);
		mf_do_query($query,$params,$dbh);
		$i++;
	}

	$response_data = new stdClass();
	$response_data->status    	= "ok";
	$response_data->form_id 	= $form_id;
	
	$response_json = json_encode($response_data);
	
	echo $response_json;
?>