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
	$chart_id				= (int) trim($_POST['chart_id']);
	$chart_enable_filter	= (int) trim($_POST['chart_enable_filter']);
	$chart_labels_visible	= (int) trim($_POST['chart_labels_visible']);
	$chart_legend_visible	= (int) trim($_POST['chart_legend_visible']);
	$chart_tooltip_visible	= (int) trim($_POST['chart_tooltip_visible']);
	$chart_gridlines_visible = (int) trim($_POST['chart_gridlines_visible']);
	$chart_is_stacked 		= (int) trim($_POST['chart_is_stacked']);
	$chart_is_vertical 		= (int) trim($_POST['chart_is_vertical']);
	$chart_rating_bars_visible 	= (int) trim($_POST['chart_rating_bars_visible']);
	$chart_rating_total_visible = (int) trim($_POST['chart_rating_total_visible']);

	$chart_grid_page_size 	= (int) trim($_POST['chart_grid_page_size']);
	$chart_grid_max_length 	= (int) trim($_POST['chart_grid_max_length']);

	$csrf_token = trim($_POST['csrf_token']);

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);

	if(empty($chart_grid_page_size)){
		$chart_grid_page_size = 1;
	}

	$chart_height = trim($_POST['chart_height'] ?? 0);
	if($chart_height == 'custom'){
		$chart_height = (int) trim($_POST['chart_height_custom']);
	}else{
		$chart_height = (int) $chart_height;
	}

	$filter_properties_array = isset($_POST['filter_prop']) ? mf_sanitize($_POST['filter_prop']) : false;
	$filter_type 			 = isset($_POST['filter_type']) ? mf_sanitize($_POST['filter_type']) : false;

	$chart_theme			 = isset($_POST['chart_theme']) ? mf_sanitize($_POST['chart_theme']) : false;
	$chart_line_style		 = isset($_POST['chart_line_style']) ? mf_sanitize($_POST['chart_line_style']) : false;
	$chart_background		 = isset($_POST['chart_background']) ? mf_sanitize($_POST['chart_background']) : false;
	$chart_title	 		 = isset($_POST['chart_title']) ? mf_sanitize($_POST['chart_title']) : false;
	$chart_rating_size	 	 = isset($_POST['chart_rating_size']) ? mf_sanitize($_POST['chart_rating_size']) : false;
	$chart_title_position    = isset($_POST['chart_title_position']) ? mf_sanitize($_POST['chart_title_position']) : false;
	$chart_title_align		 = isset($_POST['chart_title_align']) ? mf_sanitize($_POST['chart_title_align']) : false;

	$chart_labels_template	 = isset($_POST['chart_labels_template']) ? mf_sanitize($_POST['chart_labels_template']) : false;
	$chart_labels_position   = isset($_POST['chart_labels_position']) ? mf_sanitize($_POST['chart_labels_position']) : false;
	$chart_labels_align   	 = isset($_POST['chart_labels_align']) ? mf_sanitize($_POST['chart_labels_align']) : false;

	$chart_legend_position   = isset($_POST['chart_legend_position']) ? mf_sanitize($_POST['chart_legend_position']) : false;
	$chart_tooltip_template	 = isset($_POST['chart_tooltip_template']) ? mf_sanitize($_POST['chart_tooltip_template']) : false;

	$chart_bar_color		 = isset($_POST['chart_bar_color']) ? mf_sanitize($_POST['chart_bar_color']) : false;

	$chart_date_range		 = isset($_POST['chart_date_range']) ? mf_sanitize($_POST['chart_date_range']) : false;
	$chart_date_period_value = isset($_POST['chart_date_period_value']) ? mf_sanitize($_POST['chart_date_period_value']) : false;
	$chart_date_period_unit  = isset($_POST['chart_date_period_unit']) ? mf_sanitize($_POST['chart_date_period_unit']) : false;
	$chart_date_axis_baseunit_period = isset($_POST['chart_date_axis_baseunit_period']) ? mf_sanitize($_POST['chart_date_axis_baseunit_period']) : false;
	$chart_date_axis_baseunit_custom = isset($_POST['chart_date_axis_baseunit_custom']) ? mf_sanitize($_POST['chart_date_axis_baseunit_custom']) : false;

	$chart_date_axis_baseunit = '';
	if($chart_date_range == 'period'){
		$chart_date_axis_baseunit = $chart_date_axis_baseunit_period;
	}else if($chart_date_range == 'custom'){
		$chart_date_axis_baseunit = $chart_date_axis_baseunit_custom;
	}

	$chart_date_range_start = mf_sanitize($_POST['chart_date_range_start'] ?? ''); //format: mm/dd/yyyy
	$chart_date_range_end   = mf_sanitize($_POST['chart_date_range_end'] ?? ''); //format: mm/dd/yyyy

	$chart_grid_sort_by		= mf_sanitize($_POST['chart_grid_sort_by'] ?? '');

	$grid_column_preferences = mf_sanitize($_POST['grid_columns'] ?? '');

	//convert into yyyy-mm-dd
	if(!empty($chart_date_range_start)){
		$exploded = array();
		$exploded = explode('/', $chart_date_range_start);
		$chart_date_range_start = $exploded[2].'-'.$exploded[0].'-'.$exploded[1];
	}

	//convert into yyyy-mm-dd
	if(!empty($chart_date_range_end)){
		$exploded = array();
		$exploded = explode('/', $chart_date_range_end);
		$chart_date_range_end = $exploded[2].'-'.$exploded[0].'-'.$exploded[1];
	}
	
	if(empty($form_id)){
		die("This file can't be opened directly.");
	}


	$dbh = mf_connect_db();
	
	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_report permission
		if(empty($user_perms['edit_report'])){
			die("Access Denied. You don't have permission to edit this report.");
		}
	}

	/***************************************************************************************************************/	
	/* 1. Save Widget Data settings																   				   */
	/***************************************************************************************************************/
	
	//save filters
	if(!empty($chart_enable_filter)){
		//first delete all previous filter
		$query = "delete from `".MF_TABLE_PREFIX."report_filters` where form_id=? and chart_id=?";
		$params = array($form_id,$chart_id);
		mf_do_query($query,$params,$dbh);
		
		//save the new filters
		$query = "insert into `".MF_TABLE_PREFIX."report_filters`(form_id,chart_id,element_name,filter_condition,filter_keyword) values(?,?,?,?,?)";

		foreach($filter_properties_array as $data){
			$params = array($form_id,$chart_id,$data['element_name'],$data['condition'],$data['keyword']);
			mf_do_query($query,$params,$dbh);
		}
	}

	$query  = "UPDATE ".MF_TABLE_PREFIX."report_elements 
				   SET 
				   	  chart_enable_filter = ?,
				   	  chart_filter_type = ?,
				   	  chart_theme = ?,
				   	  chart_background = ?,
				   	  chart_title = ?,
				   	  chart_title_position = ?,
				   	  chart_title_align = ?,
				   	  chart_labels_template = ?,
				   	  chart_labels_visible = ?,
				   	  chart_labels_position = ?,
				   	  chart_labels_align = ?,
				   	  chart_legend_visible = ?,
				   	  chart_legend_position = ?,
				   	  chart_tooltip_template = ?,
				   	  chart_tooltip_visible = ?,
				   	  chart_gridlines_visible = ?,
				   	  chart_is_stacked = ?,
				   	  chart_is_vertical = ?,
				   	  chart_bar_color = ?,
				   	  chart_line_style = ?,
				   	  chart_date_range = ?,
				   	  chart_date_period_value = ?,
				   	  chart_date_period_unit = ?,
				   	  chart_date_axis_baseunit = ?,
				   	  chart_date_range_start = ?,
				   	  chart_date_range_end = ?,
				   	  chart_grid_page_size = ?,
				   	  chart_grid_max_length = ?,
				   	  chart_height = ?,
				   	  chart_grid_sort_by = ?,
				   	  chart_rating_bars_visible = ?,
				   	  chart_rating_total_visible = ?,
				   	  chart_rating_size = ?    
				 WHERE 
				 	  form_id = ? and chart_id = ?";
	$params = array($chart_enable_filter,
					$filter_type,
					$chart_theme,
					$chart_background,
					$chart_title,
					$chart_title_position,
					$chart_title_align,
					$chart_labels_template,
					$chart_labels_visible,
					$chart_labels_position,
					$chart_labels_align,
					$chart_legend_visible,
					$chart_legend_position,
					$chart_tooltip_template,
					$chart_tooltip_visible,
					$chart_gridlines_visible,
					$chart_is_stacked,
					$chart_is_vertical,
					$chart_bar_color,
					$chart_line_style,
					$chart_date_range,
					$chart_date_period_value,
					$chart_date_period_unit,
					$chart_date_axis_baseunit,
					$chart_date_range_start,
					$chart_date_range_end,
					$chart_grid_page_size,
					$chart_grid_max_length,
					$chart_height,
					$chart_grid_sort_by,
					$chart_rating_bars_visible,
					$chart_rating_total_visible,
					$chart_rating_size,

					$form_id,$chart_id);
	mf_do_query($query,$params,$dbh);

	//if this is grid, save column preferences
	$query = "SELECT 
					chart_type
			    FROM
			    	".MF_TABLE_PREFIX."report_elements
			   WHERE
			   		form_id = ? and chart_id = ?";
	$params = array($form_id,$chart_id);
		
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$chart_type  = $row['chart_type'];
	
	if($chart_type == 'grid'){
		//first delete all previous preferences
		$query = "delete from `".MF_TABLE_PREFIX."grid_columns` where form_id=? and chart_id=?";
		$params = array($form_id,$chart_id);
		mf_do_query($query,$params,$dbh);

		//save the new preference
		$query = "insert into `".MF_TABLE_PREFIX."grid_columns`(form_id,chart_id,element_name,position) values(?,?,?,?)";

		$position = 1;
		if(!empty($grid_column_preferences)){
			foreach($grid_column_preferences as $data){
				$column_name = $data['name'];
				
				$params = array($form_id,$chart_id,$column_name,$position);
				mf_do_query($query,$params,$dbh);

				$position++;
			}
		}
	}

	
	$_SESSION['MF_SUCCESS'] = 'Widget settings has been saved.';
	
	$response_data = new stdClass();
	$response_data->status    	= "ok";
	$response_data->form_id 	= $form_id;
	
	$response_json = json_encode($response_data);
	
	echo $response_json;
?>