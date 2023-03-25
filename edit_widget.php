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
	require('includes/users-functions.php');
	require('includes/entry-functions.php');
	require('includes/report-functions.php');
	
	$access_key = trim($_GET['key']);
	$form_id 	= (int) substr($access_key, 0, strpos($access_key, 'x'));
	
	$dbh = mf_connect_db();
	
	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);
	$mf_properties 	= mf_get_form_properties($dbh,$form_id,array('form_active'));
	
	
	//check inactive form, inactive form settings should not displayed
	if(empty($mf_properties) || $mf_properties['form_active'] == null){
		$_SESSION['MF_DENIED'] = "This is not valid URL.";

		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
		exit;
	}else{
		$form_active = (int) $mf_properties['form_active'];
	
		if($form_active !== 0 && $form_active !== 1){
			$_SESSION['MF_DENIED'] = "This is not valid URL.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_form permission
		if(empty($user_perms['edit_report'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to edit this report.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}

	//get widget properties
	$query 	= "select 
					chart_title,
					chart_height,
					chart_id,
					chart_type,
					chart_datasource,
					chart_enable_filter,
					chart_filter_type,
					chart_theme,
					chart_background,
					chart_title_position,
					chart_title_align,
					chart_labels_visible,
					chart_labels_template,
					chart_labels_position,
					chart_labels_align,
					chart_axis_is_date,
					chart_legend_visible,
					chart_legend_position,
					chart_tooltip_visible,
					chart_tooltip_template,
					chart_gridlines_visible,
					chart_is_stacked,
					chart_is_vertical,
					chart_bar_color,
					chart_line_style,
					chart_date_range,
					chart_date_period_value,
					chart_date_period_unit,
					chart_date_axis_baseunit,
					date_format(chart_date_range_start,'%c/%e/%Y') chart_date_range_start,
					date_format(chart_date_range_end,'%c/%e/%Y') chart_date_range_end,
					chart_grid_page_size,
					chart_grid_max_length,
					chart_grid_sort_by,
					chart_rating_size,
					chart_rating_bars_visible,
					chart_rating_total_visible    
			    from 
			     	 ".MF_TABLE_PREFIX."report_elements 
			    where 
			    	 access_key = ? and chart_status = 1";
	$params = array($access_key);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row)){
		$chart_type    		  = $row['chart_type'];

		$chart_title 		  = htmlspecialchars(trim($row['chart_title']));
		$chart_title_position = $row['chart_title_position'];
		$chart_title_align    = $row['chart_title_align'];

		$chart_height  		 = (int) $row['chart_height'];
		if(empty($chart_height)){
			$chart_height = 400;
		}

		$chart_id 			 = (int) $row['chart_id'];
		$chart_enable_filter = (int) $row['chart_enable_filter'];
		$chart_axis_is_date  = (int) $row['chart_axis_is_date'];
		$chart_is_stacked  	 = (int) $row['chart_is_stacked'];
		$chart_is_vertical   = (int) $row['chart_is_vertical'];
		
		$chart_datasource	 = $row['chart_datasource'];
		$chart_filter_type	 = $row['chart_filter_type'];
		$chart_theme	 	 = $row['chart_theme'];
		$chart_background    = $row['chart_background'];
		$chart_bar_color     = $row['chart_bar_color'];
		$chart_line_style    = $row['chart_line_style'];

		$chart_labels_visible  = (int) $row['chart_labels_visible'];
		$chart_labels_template = $row['chart_labels_template'];
		$chart_labels_position = $row['chart_labels_position'];
		$chart_labels_align    = $row['chart_labels_align'];

		$chart_legend_visible  = (int) $row['chart_legend_visible'];
		$chart_legend_position = $row['chart_legend_position'];

		$chart_tooltip_visible  = (int) $row['chart_tooltip_visible'];
		$chart_tooltip_template = $row['chart_tooltip_template'];
		
		$chart_gridlines_visible  = (int) $row['chart_gridlines_visible'];

		$chart_date_range 			= $row['chart_date_range']; //possible values: all - period - custom
		$chart_date_period_value 	= (int) $row['chart_date_period_value'];
		$chart_date_period_unit 	= $row['chart_date_period_unit']; //possible values: day - week - month - year
		$chart_date_axis_baseunit 	= $row['chart_date_axis_baseunit']; //possible values: day - week - month - year, or leave it empty
		$chart_date_range_start 	= $row['chart_date_range_start'];
		$chart_date_range_end 		= $row['chart_date_range_end'];

		$chart_grid_page_size  		= (int) $row['chart_grid_page_size'];
		$chart_grid_max_length  	= (int) $row['chart_grid_max_length'];
		$chart_grid_sort_by			= $row['chart_grid_sort_by'];

		$chart_rating_size			= $row['chart_rating_size'];
		$chart_rating_bars_visible  = (int) $row['chart_rating_bars_visible'];
		$chart_rating_total_visible = (int) $row['chart_rating_total_visible']; 
	}else{
		die("Error. Invalid key.");
	} 
	
	switch ($chart_type) {
		case 'pie': $chart_type_desc 	 = 'Pie Chart'; break;
		case 'bar': $chart_type_desc 	 = 'Bar Chart'; break;
		case 'donut': $chart_type_desc   = 'Donut Chart'; break;
		case 'line': $chart_type_desc 	 = 'Line Chart'; break;
		case 'area': $chart_type_desc 	 = 'Area Chart'; break;
		case 'counter': $chart_type_desc = 'Counter'; break;
		case 'grid': $chart_type_desc 	 = 'Entries Grid'; break;
		case 'rating': $chart_type_desc 	 = 'Rating Scorecard'; break;
		
	}

	//get form properties
	$query 	= "select 
					 form_name
			     from 
			     	 ".MF_TABLE_PREFIX."forms 
			    where 
			    	 form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row)){
		$row['form_name'] = mf_trim_max_length($row['form_name'],35);
		$form_name = htmlspecialchars($row['form_name']);
	}
	
	//get a list of all rating fields and the properties
	$query = "select 
					element_id,
					element_rating_max 
				from 
					".MF_TABLE_PREFIX."form_elements 
			   where 
			   		form_id = ? and 
			   		element_type = 'rating' and 
			   		element_status = 1";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);

	$rating_field_properties = array();
	while($row = mf_do_fetch_result($sth)){
		$rating_field_properties[$row['element_id']]['rating_max'] = (int) $row['element_rating_max'];
	}

	//get a list of all time fields and the properties
	$query = "select 
					element_id,
					element_time_showsecond,
					element_time_24hour 
				from 
					".MF_TABLE_PREFIX."form_elements 
			   where 
			   		form_id = ? and 
			   		element_type = 'time' and 
			   		element_status = 1";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);

	$time_field_properties = array();
	while($row = mf_do_fetch_result($sth)){
		$time_field_properties[$row['element_id']]['showsecond'] = (int) $row['element_time_showsecond'];
		$time_field_properties[$row['element_id']]['24hour'] 	 = (int) $row['element_time_24hour'];
	}

	//Get options list lookup for all choice and select field
	$query = "SELECT 
					element_id,
					option_id,
					`option` 
			    FROM 
			    	".MF_TABLE_PREFIX."element_options 
			   where 
			   		form_id = ? and live=1 
			order by 
					element_id asc,`position` asc";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$options_lookup = array();
	while($row = mf_do_fetch_result($sth)){
		$element_id = $row['element_id'];
		$option_id  = $row['option_id'];
		$options_lookup[$element_id][$option_id] = htmlspecialchars($row['option'],ENT_QUOTES);
	}

	$query = "SELECT 
					element_id 
			    FROM 
			    	".MF_TABLE_PREFIX."form_elements 
			   WHERE 
			   		form_id = ? and 
			   		element_type in('select','radio') and 
			   		element_status = 1";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$select_radio_fields_lookup = array();
	while($row = mf_do_fetch_result($sth)){
		$element_id = $row['element_id'];
		
		$select_radio_fields_lookup[$element_id] = $options_lookup[$element_id];
	}

	$jquery_data_code = '';

	//initialize chart background color and bar color picker
	$jquery_data_code .= "\$('#ew_chart_background').miniColors('value', '{$chart_background}');\n";
	$jquery_data_code .= "\$('#ew_chart_bar_color').miniColors('value', '{$chart_bar_color}');\n";

	//get all available columns label
	$columns_meta  = mf_get_columns_meta($dbh,$form_id);
	$columns_label = $columns_meta['name_lookup'];
	$columns_type  = $columns_meta['type_lookup'];

	$form_properties = mf_get_form_properties($dbh,$form_id,array('payment_enable_merchant','form_resume_enable','form_approval_enable'));

	//if payment enabled, add ap_form_payments columns into $columns_label
	if($form_properties['payment_enable_merchant'] == 1){
		$columns_label['payment_amount'] = 'Payment Amount';
		$columns_label['payment_status'] = 'Payment Status';
		$columns_label['payment_id']	 = 'Payment ID';

		$columns_type['payment_amount'] = 'money';
		$columns_type['payment_status'] = 'text';
		$columns_type['payment_id'] 	= 'text';
	}

	//if approval workflow enabled, prepare the labels for the rules condition
	if($form_properties['form_approval_enable'] == 1){
		$approval_labels['approval_status'] = 'Approval Status';
		$columns_type['approval_status']	= 'approval_status';
	}


	//prepare the jquery data for column type lookup
	foreach ($columns_type as $element_name => $element_type) {
		if($element_type == 'checkbox'){
			if(substr($element_name, -5) == 'other'){
				$element_type = 'checkbox_other';
			}
		}else if($element_type == 'time'){
			//there are several variants of time fields, we need to make it specific
			$temp = array();
			$temp = explode('_', $element_name);
			$time_element_id = $temp[1];

			if(!empty($time_field_properties[$time_element_id]['showsecond']) && !empty($time_field_properties[$time_element_id]['24hour'])){
				$element_type = 'time_showsecond24hour';
			}else if(!empty($time_field_properties[$time_element_id]['showsecond']) && empty($time_field_properties[$time_element_id]['24hour'])){
				$element_type = 'time_showsecond';
			}else if(empty($time_field_properties[$time_element_id]['showsecond']) && !empty($time_field_properties[$time_element_id]['24hour'])){
				$element_type = 'time_24hour';
			}

		}

		$jquery_data_code .= "\$('#widget_filter_pane').data('$element_name','$element_type');\n";
	}

	//get filter keywords from ap_report_filters table
	$query = "select
					element_name,
					filter_condition,
					filter_keyword
				from 
					".MF_TABLE_PREFIX."report_filters
			   where
			   		form_id = ? and chart_id = ? 
			order by 
			   		arf_id asc";
	$params = array($form_id,$chart_id);
	$sth = mf_do_query($query,$params,$dbh);
	$i = 0;
	while($row = mf_do_fetch_result($sth)){
		$filter_data[$i]['element_name'] 	 = $row['element_name'];
		$filter_data[$i]['filter_condition'] = $row['filter_condition'];
		$filter_data[$i]['filter_keyword'] 	 = $row['filter_keyword'];
		$i++;
	}

	//get current column preference for grid
	if($chart_type == 'grid'){
		$query = "select element_name from ".MF_TABLE_PREFIX."grid_columns where form_id=? and chart_id=?";
		$params = array($form_id,$chart_id);

		$sth = mf_do_query($query,$params,$dbh);
		while($row = mf_do_fetch_result($sth)){
			$current_column_preference[] = $row['element_name'];
		}
	}	

	//determine legend property visibility
	//legend only available for the following:
	//pie, donut, bar (allrows)
	//line + area (category axis only)
	$show_legend_property = false;
	if($chart_type == 'pie' || $chart_type == 'donut'){
		$show_legend_property = true;
	}else if($chart_type == 'bar'){
		if(strpos($chart_datasource,'allrows') !== false) {
		   $show_legend_property = true;
		}
	}else if($chart_type == 'line' || $chart_type == 'area'){
		if(empty($chart_axis_is_date)){
			$show_legend_property = true;
		}
	}

	//determine gridlines property visibility
	//gridlines only available for bar, line, area
	$show_gridlines_property = false;
	if(in_array($chart_type, array('bar','line','area'))){
		$show_gridlines_property = true;
	}	

	//determine stack property visibility
	//only available for 'allrows' for these fields: bar, line, area
	$show_stack_property = false;
	if(in_array($chart_type, array('bar','line','area'))){
		if(strpos($chart_datasource,'allrows') !== false) {
		   $show_stack_property = true;
		}
	}

	//determine chart is vertical property visibility
	//only available for bar chart
	$show_vertical_property = false;
	if($chart_type == 'bar'){
		$show_vertical_property = true;
	}

	//determine bar color property visibility
	//only available for single bar char (non allrows)
	$show_bar_color_property = false;											
	if($chart_type == 'bar'){
		if(strpos($chart_datasource,'allrows') === false) {
			$show_bar_color_property = true;
		}
	}

	//determine line style and date range property visibility
	//only available for line and area
	$show_line_style_property = false;
	$show_date_range_property = false;
	if($chart_type == 'line' || $chart_type == 'area'){
		$show_line_style_property = true;
		$show_date_range_property = true;
	}

	//determine background color, show labels and show tooltips property visibility
	//visible to all charts, not visible to grid and rating
	$show_background_color_property = false;
	$show_labels_property 			= false;
	$show_tooltip_property 			= false;
	$show_chart_theme_property		= false;
	$show_rating_theme_property		= false;
	$show_rating_bars_property		= false;
	$show_rating_total_entries_property	= false;

	if($chart_type == 'grid'){		
		$show_chart_theme_property	= true;
	}else if($chart_type == 'rating'){
		$show_rating_theme_property	= true;
		$show_rating_bars_property		= true;
		$show_rating_total_entries_property	= true;
	}else{
		$show_background_color_property = true;
		$show_labels_property 			= true;
		$show_tooltip_property 			= true;
		$show_chart_theme_property		= true;
	}									

	$header_data =<<<EOT
<link type="text/css" href="css/jquery_minicolors.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="js/datepick/smoothness.datepick.css{$mf_version_tag}" rel="stylesheet" />
EOT;
	
	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full" data-csrftoken="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>">
			<div class="post edit_widget">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <a class="breadcrumb" href="manage_report.php?id=<?php echo $form_id; ?>">Report</a> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <?php echo 'Widget #'.$chart_id; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Settings</h2>
							<p>Editing widget #<?php echo "{$chart_id} &#8674; {$chart_type_desc}"; ?></p>
						</div>	
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>
				<div class="content_body">
					
					<ul id="ew_main_list" style="margin-bottom: 25px" data-formid="<?php echo $form_id; ?>" data-charttype="<?php echo $chart_type; ?>" data-chartid="<?php echo $chart_id; ?>">
						<li>
							<div id="ew_box_widget_data" class="ew_box_main gradient_blue">
								<div class="ew_box_meta" style="width: 120px">
									<h1>1.</h1>
									<h6>Widget Data</h6>
								</div>
								<div class="ew_box_content">
									<!-- start datasource reference lookup -->
									<select class="element select" id="ew_select_datasource" name="ew_select_datasource" style="display: none"> 
											<?php
												$params = array();
												$params['show_expanded_options'] = false;

												$options_markup = mf_get_chart_datasource_markup($dbh,$form_id,$params);
												echo $options_markup;
												
												$params['show_expanded_options'] = true;

												$options_markup = mf_get_chart_datasource_markup($dbh,$form_id,$params);
												echo $options_markup;
											?>
									</select>
									<!-- end datasource reference lookup -->
									
									<label class="description" style="margin-top: 2px">Data Source &#8674; <span id="ew_datasource_title"><?php echo $chart_datasource; ?></span></label> 
									<label class="description inline" for="ew_chart_enable_filter" style="margin-top: 20px">
										Data Option 
									</label>
									<span class="icon-question helpicon clearfix" data-tippy-content="By default, the widget will be based on all entries data. You can enable filter to generate the widget based on specific set of data."></span>
									<select class="select small" id="ew_chart_enable_filter" autocomplete="off">
										<option <?php if(empty($chart_enable_filter)){ echo 'selected="selected"'; } ?> value="0">Use all entries</option>
										<option <?php if(!empty($chart_enable_filter)){ echo 'selected="selected"'; } ?> value="1">Filter entries</option>
									</select>

									<!-- start widget filter pane -->
									<div id="widget_filter_pane" <?php if(empty($chart_enable_filter)){ echo 'style="display: none"'; } ?>>
										<h6>Use entries that match 
												<select style="margin-left: 5px;margin-right: 5px" name="filter_all_any" id="filter_all_any" class="element select"> 
													<option value="all" <?php if($chart_filter_type == 'all'){ echo 'selected="selected"'; } ?>>all</option>
													<option value="any" <?php if($chart_filter_type == 'any'){ echo 'selected="selected"'; } ?>>any</option>
												</select> 
											of the following conditions:
										</h6>
										
										<ul>

										<?php
											if(empty($filter_data)){
												//set empty filter default keyword as empty
												$filter_default_keyword = '';

												if($form_properties['payment_enable_merchant'] == 1){
													$field_labels = array_slice($columns_label, 4);
													$entry_info_labels = array_slice($columns_label, 0,4);
													$payment_info_labels = array_slice($columns_label, -3);

													$field_labels = array_diff($field_labels, $payment_info_labels);
												}else{
													$field_labels = array_slice($columns_label, 4);
													$entry_info_labels = array_slice($columns_label, 0,4);
												}

												$temp_keys = array_keys($field_labels);
												$first_field_element_name = $temp_keys[0];
												$first_field_element_type = $columns_type[$first_field_element_name];
												$first_field_element_id   = (int) str_replace('element_', '', $first_field_element_name); 

												if($first_field_element_type == 'checkbox'){
													if(substr($first_field_element_name, -5) == 'other'){
														$first_field_element_type = 'checkbox_other';
													}
													$filter_default_keyword = 'is_one';
												}else if($first_field_element_type == 'time'){
													//there are several variants of time fields, we need to make it specific
													$temp = array();
													$temp = explode('_', $first_field_element_name);
													$time_element_id = $temp[1];

													if(!empty($time_field_properties[$time_element_id]['showsecond']) && !empty($time_field_properties[$time_element_id]['24hour'])){
														$first_field_element_type = 'time_showsecond24hour';
													}else if(!empty($time_field_properties[$time_element_id]['showsecond']) && empty($time_field_properties[$time_element_id]['24hour'])){
														$first_field_element_type = 'time_showsecond';
													}else if(empty($time_field_properties[$time_element_id]['showsecond']) && !empty($time_field_properties[$time_element_id]['24hour'])){
														$first_field_element_type = 'time_24hour';
													}
												}else if($first_field_element_type == 'radio' || $first_field_element_type == 'select'){
													$field_select_radio_data = $select_radio_fields_lookup[$first_field_element_id];
													
													if(!empty($field_select_radio_data)){
														$first_key = array_key_first($field_select_radio_data) ;
														$filter_default_keyword = $field_select_radio_data[$first_key];												
													}
												}else if($first_field_element_type == 'rating') {
													$filter_default_keyword = '1';
												}

												$time_hour   = '';
												$time_minute = '';
												$time_second = '';
												$time_ampm   = 'AM';

												if(in_array($first_field_element_type, array('money','number'))){
													$condition_text_display = 'display:none';
													$condition_number_display = '';
													$condition_date_display = 'display:none';
													$condition_file_display = 'display:none';
													$condition_checkbox_display = 'display:none';
													$condition_keyword_display = '';
													$condition_approval_status_display = 'display:none';
													$condition_time_display = 'display:none';
													$condition_select_display = 'display:none';
													$condition_rating_display = 'display:none';
													$condition_ratingvalues_display = 'display:none';
												}else if(in_array($first_field_element_type, array('date','europe_date'))){
													$condition_text_display = 'display:none';
													$condition_number_display = 'display:none';
													$condition_date_display = '';
													$condition_file_display = 'display:none';
													$condition_checkbox_display = 'display:none';
													$condition_keyword_display = '';
													$filter_date_class = 'filter_date';
													$condition_approval_status_display = 'display:none';
													$condition_time_display = 'display:none';
													$condition_select_display = 'display:none';
													$condition_rating_display = 'display:none';
													$condition_ratingvalues_display = 'display:none';
												}else if(in_array($first_field_element_type, array('time','time_showsecond','time_24hour','time_showsecond24hour'))){
													$condition_text_display = 'display:none';
													$condition_number_display = 'display:none';
													$condition_date_display = '';
													$condition_time_display = '';
													$condition_checkbox_display = 'display:none';
													$condition_keyword_display = 'display:none';
													$condition_date_class = '';
													$condition_select_display = 'display:none';
													$condition_rating_display = 'display:none';
													$condition_ratingvalues_display = 'display:none';
													$condition_approval_status_display = 'display:none';
													$condition_file_display = 'display:none';

													//show or hide the second and AM/PM
													$condition_second_display = '';
													$condition_ampm_display   = '';
													
													if($first_field_element_type == 'time'){
														$condition_second_display = 'display:none';
													}else if($first_field_element_type == 'time_24hour'){
														$condition_second_display = 'display:none';
														$condition_ampm_display   = 'display:none';
													}else if($first_field_element_type == 'time_showsecond24hour'){
														$condition_ampm_display   = 'display:none';
													} 
												}else if($first_field_element_type == 'file'){
													$condition_text_display = 'display:none';
													$condition_number_display = 'display:none';
													$condition_date_display = 'display:none';
													$condition_file_display = '';
													$condition_checkbox_display = 'display:none';
													$condition_keyword_display = '';
													$condition_approval_status_display = 'display:none';
													$condition_time_display = 'display:none';
													$condition_select_display = 'display:none';
													$condition_rating_display = 'display:none';
													$condition_ratingvalues_display = 'display:none';
												}else if($first_field_element_type == 'checkbox'){
													$condition_text_display = 'display:none';
													$condition_number_display = 'display:none';
													$condition_date_display = 'display:none';
													$condition_file_display = 'display:none';
													$condition_checkbox_display = '';
													$condition_keyword_display = 'display:none';
													$condition_approval_status_display = 'display:none';
													$condition_time_display = 'display:none';
													$condition_select_display = 'display:none';
													$condition_rating_display = 'display:none';
													$condition_ratingvalues_display = 'display:none';
												}else if($first_field_element_type == 'radio' || $first_field_element_type == 'select'){
													$condition_text_display = '';
													$condition_number_display = 'display:none';
													$condition_date_display = 'display:none';
													$condition_time_display = 'display:none';
													$condition_checkbox_display = 'display:none';
													$condition_keyword_display = 'display:none';
													$condition_select_display = '';
													$condition_rating_display = 'display:none';
													$condition_ratingvalues_display = 'display:none';
													$condition_approval_status_display = 'display:none';
													$condition_file_display = 'display:none';
												}else if($first_field_element_type == 'rating'){
													$condition_text_display = 'display:none';
													$condition_number_display = 'display:none';
													$condition_date_display = 'display:none';
													$condition_time_display = 'display:none';
													$condition_checkbox_display = 'display:none';
													$condition_keyword_display = 'display:none';
													$condition_select_display = 'display:none';
													$condition_rating_display = '';
													$condition_ratingvalues_display = '';
													$condition_approval_status_display = 'display:none';
													$condition_file_display = 'display:none';
												}else if($first_field_element_type == 'approval_status'){
													$condition_text_display = 'display:none';
													$condition_number_display = 'display:none';
													$condition_date_display = 'display:none';
													$condition_file_display = 'display:none';
													$condition_checkbox_display = 'display:none';
													$condition_keyword_display = 'display:none';
													$condition_approval_status_display = '';
													$condition_time_display = 'display:none';
													$condition_select_display = 'display:none';
													$condition_rating_display = 'display:none';
													$condition_ratingvalues_display = 'display:none';
												}else{
													$condition_text_display = '';
													$condition_number_display = 'display:none';
													$condition_date_display = 'display:none';
													$condition_file_display = 'display:none';
													$condition_checkbox_display = 'display:none';
													$condition_keyword_display = '';
													$condition_approval_status_display = 'display:none';
													$condition_time_display = 'display:none';
													$condition_select_display = 'display:none';
													$condition_rating_display = 'display:none';
													$condition_ratingvalues_display = 'display:none';
												}

												//prepare the jquery data for the filter list
												$filter_properties = new stdClass();
												$filter_properties->element_name = $first_field_element_name;
												
												if($first_field_element_type == 'file'){
													$filter_properties->condition    = 'contains';
												}else if($first_field_element_type == 'checkbox'){
													$filter_properties->condition    = 'is_one';
												}else{
													$filter_properties->condition    = 'is';
												}
												
												$filter_properties->keyword 	 = $filter_default_keyword;

												$json_filter_properties = json_encode($filter_properties);
												$jquery_data_code .= "\$('#li_1').data('filter_properties',{$json_filter_properties});\n";
										?>

										<li id="li_1" class="filter_settings <?php echo $filter_date_class; ?>">
											<select name="filterfield_1" id="filterfield_1" class="element select condition_fieldname" style="width: 260px"> 
												<optgroup label="Form Fields">
													<?php
														foreach ($field_labels as $element_name => $element_label) {
															if($columns_type[$element_name] == 'signature'){
																continue;
															}

															if(strlen($element_label) > 40){
																$element_label = substr($element_label, 0, 40).'...';
															}
															
															echo "<option value=\"{$element_name}\">{$element_label}</option>\n";
														}
													?>
												</optgroup>
												<optgroup label="Entry Information">
													<?php
														foreach ($entry_info_labels as $element_name => $element_label) {
															echo "<option value=\"{$element_name}\">{$element_label}</option>\n";
														}
													?>
												</optgroup>
												
												<?php if(!empty($payment_info_labels)){ ?>
												<optgroup label="Payment Information">
													<?php
														foreach ($payment_info_labels as $element_name => $element_label) {
															echo "<option value=\"{$element_name}\">{$element_label}</option>\n";
														}
													?>
												</optgroup>
												<?php } ?>

												<?php if(!empty($form_properties['form_approval_enable'])){ ?>
												<optgroup label="Approval Workflow">
													<option value="approval_status">Approval Status</option>
												</optgroup>
												<?php } ?>
											</select> 
											<select name="conditiontext_1" id="conditiontext_1" class="element select condition_text" style="width: 120px;<?php echo $condition_text_display; ?>">
												<option value="is">Is</option>
												<option value="is_not">Is Not</option>
												<option value="begins_with">Begins with</option>
												<option value="ends_with">Ends with</option>
												<option value="contains">Contains</option>
												<option value="not_contain">Does not contain</option>
											</select>
											<select name="conditionnumber_1" id="conditionnumber_1" class="element select condition_number" style="width: 120px;<?php echo $condition_number_display; ?>">
												<option value="is">Is</option>
												<option value="less_than">Less than</option>
												<option value="greater_than">Greater than</option>
											</select>
											<select name="conditionrating_1" id="conditionrating_1" class="element select condition_rating" style="width: 120px;<?php echo $condition_rating_display; ?>">
												<option value="is">Is</option>
												<option value="is_not">Is Not</option>
												<option value="less_than">Less than</option>
												<option value="greater_than">Greater than</option>
											</select>
											<select name="conditiondate_1" id="conditiondate_1" class="element select condition_date" style="width: 120px;<?php echo $condition_date_display; ?>">
												<option value="is">Is</option>
												<option value="is_before">Is Before</option>
												<option value="is_after">Is After</option>
											</select>
											<select name="conditionfile_1" id="conditionfile_1" class="element select condition_file" style="width: 120px;<?php echo $condition_file_display; ?>">
												<option value="contains">Contains</option>
												<option value="not_contain">Does not contain</option>
											</select>
											<select name="conditioncheckbox_1" id="conditioncheckbox_1" class="element select condition_checkbox" style="width: 120px;<?php echo $condition_checkbox_display; ?>">
												<option value="is_one">Is Checked</option>
												<option value="is_zero">Is Empty</option>
											</select>
											<select id="conditionselect_1" name="conditionselect_1" autocomplete="off" class="element select condition_select" style="<?php echo $condition_select_display; ?>">
												<?php
													if(!empty($field_select_radio_data)){
														foreach ($field_select_radio_data as $option_title) {
															$option_value = $option_title;
															$option_title = strip_tags($option_title);
															
															if(strlen($option_title) > 40){
																$option_title = substr($option_title, 0, 40).'...';
															}
															
															echo "<option value=\"{$option_value}\">{$option_title}</option>\n";
														}
													}
												?>
											</select> 
											<select id="conditionratingvalues_1" name="conditionratingvalues_1" autocomplete="off" class="element select condition_ratingvalues" style="<?php echo $condition_ratingvalues_display; ?>">
												<?php

													$rating_max = $rating_field_properties[$first_field_element_id]['rating_max'] ?? 0;
																																	
													for($j=1;$j<=$rating_max;$j++) {
														
														if($rule_keyword == 1){
															$selected_tag = 'selected="selected"';
														}

														echo "<option {$selected_tag} value=\"{$j}\">{$j}</option>\n";
													}
														
												?>
											</select>  
											<span name="conditiontime_1" id="conditiontime_1" class="condition_time" style="<?php echo $condition_time_display; ?>">
												<input name="conditiontimehour_1" id="conditiontimehour_1" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_hour; ?>" placeholder="HH"> : 
												<input name="conditiontimeminute_1" id="conditiontimeminute_1" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_minute; ?>" placeholder="MM">  
												<span class="conditiontime_second" style="<?php echo $condition_second_display; ?>"> : <input name="conditiontimesecond_<?php echo $user_id.'_'.$i; ?>" id="conditiontimesecond_<?php echo $user_id.'_'.$i; ?>" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_second; ?>" placeholder="SS"> </span>
												<select class="element select conditiontime_ampm conditiontime_input" name="conditiontimeampm_<?php echo $user_id.'_'.$i; ?>" id="conditiontimeampm_<?php echo $user_id.'_'.$i; ?>" style="<?php echo $condition_ampm_display; ?>">
													<option value="AM">AM</option>
													<option value="PM">PM</option>
												</select>
											</span>
											<select name="conditionapprovalstatus_1" id="conditionapprovalstatus_1" class="element select condition_approval_status" style="width: 120px;<?php echo $condition_approval_status_display; ?>">
												<option value="is_approved">Is Approved</option>
												<option value="is_denied">Is Denied</option>
												<option value="is_pending">Is Pending</option>
											</select>
											<input type="text" class="element text filter_keyword" value="" id="filterkeyword_1" style="<?php echo $condition_keyword_display; ?>">
											<input type="hidden" value="" name="datepicker_1" id="datepicker_1">
											<span style="display:none"><img id="datepickimg_1" alt="Pick date." src="images/icons/calendar.png" class="trigger filter_date_trigger" style="vertical-align: top; cursor: pointer" /></span>
											<a href="#" id="deletefilter_1" class="filter_delete_a"><span class="icon-minus-circle2"></span></a>

										</li>

										<?php 
											} else { 
												
												if($form_properties['payment_enable_merchant'] == 1){
													$field_labels = array_slice($columns_label, 4);
													$entry_info_labels = array_slice($columns_label, 0,4);
													$payment_info_labels = array_slice($columns_label, -3);
													
													$field_labels = array_diff($field_labels, $payment_info_labels);
												}else{
													$field_labels = array_slice($columns_label, 4);
													$entry_info_labels = array_slice($columns_label, 0,4);
												}

												$i=1;
												$filter_properties = new stdClass();

												foreach ($filter_data as $value) {
													$field_element_type 	= $columns_type[$value['element_name']];
													$condition_element_name = $value['element_name'];
													$condition_element_id   = (int) str_replace('element_', '', $condition_element_name);
													$rule_condition 		= $value['filter_condition']; 
													$rule_keyword 			= htmlspecialchars($value['filter_keyword'],ENT_QUOTES);
											
													if($field_element_type == 'checkbox'){
														if(substr($value['element_name'], -5) == 'other'){
															$field_element_type = 'checkbox_other';
														}
													}

													if($field_element_type == 'time'){
														//there are several variants of time fields, we need to make it specific
														$temp = array();
														$temp = explode('_', $condition_element_name);
														$time_element_id = $temp[1];

														if(!empty($time_field_properties[$time_element_id]['showsecond']) && !empty($time_field_properties[$time_element_id]['24hour'])){
															$field_element_type = 'time_showsecond24hour';
														}else if(!empty($time_field_properties[$time_element_id]['showsecond']) && empty($time_field_properties[$time_element_id]['24hour'])){
															$field_element_type = 'time_showsecond';
														}else if(empty($time_field_properties[$time_element_id]['showsecond']) && !empty($time_field_properties[$time_element_id]['24hour'])){
															$field_element_type = 'time_24hour';
														}
													}else if($field_element_type == 'radio' || $field_element_type == 'select'){
														$field_select_radio_data = $select_radio_fields_lookup[$condition_element_id];
													}

													$filter_date_class = '';
													$time_hour   = '';
													$time_minute = '';
													$time_second = '';
													$time_ampm   = 'AM';
													
													if(in_array($field_element_type, array('money','number'))){
														$condition_text_display = 'display:none';
														$condition_number_display = '';
														$condition_date_display = 'display:none';
														$condition_file_display = 'display:none';
														$condition_checkbox_display = 'display:none';
														$condition_keyword_display = '';
														$condition_approval_status_display = 'display:none';
														$condition_time_display = 'display:none';
														$condition_select_display = 'display:none';
														$condition_rating_display = 'display:none';
														$condition_ratingvalues_display = 'display:none';
													}else if(in_array($field_element_type, array('date','europe_date'))){
														$condition_text_display = 'display:none';
														$condition_number_display = 'display:none';
														$condition_date_display = '';
														$condition_file_display = 'display:none';
														$condition_checkbox_display = 'display:none';
														$condition_keyword_display = '';
														$filter_date_class = 'filter_date';
														$condition_approval_status_display = 'display:none';
														$condition_time_display = 'display:none';
														$condition_select_display = 'display:none';
														$condition_rating_display = 'display:none';
														$condition_ratingvalues_display = 'display:none';
													}else if(in_array($field_element_type, array('time','time_showsecond','time_24hour','time_showsecond24hour'))){
														$condition_text_display = 'display:none';
														$condition_number_display = 'display:none';
														$condition_date_display = '';
														$condition_time_display = '';
														$condition_checkbox_display = 'display:none';
														$condition_keyword_display = 'display:none';
														$condition_date_class = '';
														$condition_select_display = 'display:none';
														$condition_rating_display = 'display:none';
														$condition_ratingvalues_display = 'display:none';
														$condition_approval_status_display = 'display:none';
														$condition_file_display = 'display:none';

														if(!empty($rule_keyword)){
															$exploded = array();
															$exploded = explode(':', $rule_keyword);

															$time_hour   = sprintf("%02s", $exploded[0]);
															$time_minute = sprintf("%02s", $exploded[1]);
															$time_second = sprintf("%02s", $exploded[2]);
															$time_ampm   = strtoupper($exploded[3]); 
														}
														
														//show or hide the second and AM/PM
														$condition_second_display = '';
														$condition_ampm_display   = '';
														
														if($field_element_type == 'time'){
															$condition_second_display = 'display:none';
														}else if($field_element_type == 'time_24hour'){
															$condition_second_display = 'display:none';
															$condition_ampm_display   = 'display:none';
														}else if($field_element_type == 'time_showsecond24hour'){
															$condition_ampm_display   = 'display:none';
														} 
													}else if($field_element_type == 'file'){
														$condition_text_display = 'display:none';
														$condition_number_display = 'display:none';
														$condition_date_display = 'display:none';
														$condition_file_display = '';
														$condition_checkbox_display = 'display:none';
														$condition_keyword_display = '';
														$condition_approval_status_display = 'display:none';
														$condition_time_display = 'display:none';
														$condition_select_display = 'display:none';
														$condition_rating_display = 'display:none';
														$condition_ratingvalues_display = 'display:none';
													}else if($field_element_type == 'checkbox'){
														$condition_text_display = 'display:none';
														$condition_number_display = 'display:none';
														$condition_date_display = 'display:none';
														$condition_file_display = 'display:none';
														$condition_checkbox_display = '';
														$condition_keyword_display = 'display:none';
														$condition_approval_status_display = 'display:none';
														$condition_time_display = 'display:none';
														$condition_select_display = 'display:none';
														$condition_rating_display = 'display:none';
														$condition_ratingvalues_display = 'display:none';
													}else if($field_element_type == 'approval_status'){
														$condition_text_display = 'display:none';
														$condition_number_display = 'display:none';
														$condition_date_display = 'display:none';
														$condition_file_display = 'display:none';
														$condition_checkbox_display = 'display:none';
														$condition_keyword_display = 'display:none';
														$condition_approval_status_display = '';
														$condition_time_display = 'display:none';
														$condition_select_display = 'display:none';
														$condition_rating_display = 'display:none';
														$condition_ratingvalues_display = 'display:none';
													}else if($field_element_type == 'radio' || $field_element_type == 'select'){
														if($rule_condition == 'is' || $rule_condition == 'is_not'){
															$condition_text_display = '';
															$condition_number_display = 'display:none';
															$condition_date_display = 'display:none';
															$condition_time_display = 'display:none';
															$condition_checkbox_display = 'display:none';
															$condition_keyword_display = 'display:none';
															$condition_select_display = '';
															$condition_rating_display = 'display:none';
															$condition_ratingvalues_display = 'display:none';
															$condition_approval_status_display = 'display:none';
															$condition_file_display = 'display:none';
														}else{
															$condition_text_display = '';
															$condition_number_display = 'display:none';
															$condition_date_display = 'display:none';
															$condition_time_display = 'display:none';
															$condition_checkbox_display = 'display:none';
															$condition_keyword_display = '';
															$condition_select_display = 'display:none';
															$condition_rating_display = 'display:none';
															$condition_ratingvalues_display = 'display:none';
															$condition_approval_status_display = 'display:none';
															$condition_file_display = 'display:none';
														}
													}else if($field_element_type == 'rating'){
														$condition_text_display = 'display:none';
														$condition_number_display = 'display:none';
														$condition_date_display = 'display:none';
														$condition_time_display = 'display:none';
														$condition_checkbox_display = 'display:none';
														$condition_keyword_display = 'display:none';
														$condition_select_display = 'display:none';
														$condition_rating_display = '';
														$condition_ratingvalues_display = '';
														$condition_approval_status_display = 'display:none';
														$condition_file_display = 'display:none';
													}else{
														$condition_text_display = '';
														$condition_number_display = 'display:none';
														$condition_date_display = 'display:none';
														$condition_file_display = 'display:none';
														$condition_checkbox_display = 'display:none';
														$condition_keyword_display = '';
														$condition_approval_status_display = 'display:none';
														$condition_time_display = 'display:none';
														$condition_select_display = 'display:none';
														$condition_rating_display = 'display:none';
														$condition_ratingvalues_display = 'display:none';
													}

													//prepare the jquery data for the filter list
													$filter_properties->element_name = $value['element_name'];
													$filter_properties->condition    = $value['filter_condition'];
													$filter_properties->keyword 	 = $value['filter_keyword'];

													$json_filter_properties = json_encode($filter_properties);
													$jquery_data_code .= "\$('#li_{$i}').data('filter_properties',{$json_filter_properties});\n";
										?>			

										<li id="li_<?php echo $i; ?>" class="filter_settings <?php echo $filter_date_class; ?>">
											<select name="filterfield_<?php echo $i; ?>" id="filterfield_<?php echo $i; ?>" class="element select condition_fieldname" style="width: 260px"> 
												<optgroup label="Form Fields">
													<?php
														foreach ($field_labels as $element_name => $element_label) {
															if($columns_type[$element_name] == 'signature'){
																continue;
															}
															
															if($element_name == $value['element_name']){
																$selected_tag = 'selected="selected"';
															}else{
																$selected_tag = '';
															}

															if(strlen($element_label) > 40){
																$element_label = substr($element_label, 0, 40).'...';
															}
															
															echo "<option {$selected_tag} value=\"{$element_name}\">{$element_label}</option>\n";
														}
													?>
												</optgroup>
												<optgroup label="Entry Information">
													<?php
														foreach ($entry_info_labels as $element_name => $element_label) {
															if($element_name == $value['element_name']){
																$selected_tag = 'selected="selected"';
															}else{
																$selected_tag = '';
															}

															echo "<option {$selected_tag} value=\"{$element_name}\">{$element_label}</option>\n";
														}
													?>
												</optgroup>
												
												<?php if(!empty($payment_info_labels)){ ?>
												<optgroup label="Payment Information">
													<?php
														foreach ($payment_info_labels as $element_name => $element_label) {
															if($element_name == $value['element_name']){
																$selected_tag = 'selected="selected"';
															}else{
																$selected_tag = '';
															}

															echo "<option {$selected_tag} value=\"{$element_name}\">{$element_label}</option>\n";
														}
													?>
												</optgroup>
												<?php } ?>

												<?php if(!empty($form_properties['form_approval_enable'])){ ?>
												<optgroup label="Approval Workflow">
													<option value="approval_status" <?php if($value['element_name'] == 'approval_status'){ echo 'selected="selected"'; } ?>>Approval Status</option>
												</optgroup>
												<?php } ?>
											</select> 
											<select name="conditiontext_<?php echo $i; ?>" id="conditiontext_<?php echo $i; ?>" class="element select condition_text" style="width: 120px;<?php echo $condition_text_display; ?>">
												<option <?php if($value['filter_condition'] == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
												<option <?php if($value['filter_condition'] == 'is_not'){ echo 'selected="selected"'; } ?> value="is_not">Is Not</option>
												<option <?php if($value['filter_condition'] == 'begins_with'){ echo 'selected="selected"'; } ?> value="begins_with">Begins with</option>
												<option <?php if($value['filter_condition'] == 'ends_with'){ echo 'selected="selected"'; } ?> value="ends_with">Ends with</option>
												<option <?php if($value['filter_condition'] == 'contains'){ echo 'selected="selected"'; } ?> value="contains">Contains</option>
												<option <?php if($value['filter_condition'] == 'not_contain'){ echo 'selected="selected"'; } ?> value="not_contain">Does not contain</option>
											</select>
											<select name="conditionnumber_<?php echo $i; ?>" id="conditionnumber_<?php echo $i; ?>" class="element select condition_number" style="width: 120px;<?php echo $condition_number_display; ?>">
												<option <?php if($value['filter_condition'] == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
												<option <?php if($value['filter_condition'] == 'less_than'){ echo 'selected="selected"'; } ?> value="less_than">Less than</option>
												<option <?php if($value['filter_condition'] == 'greater_than'){ echo 'selected="selected"'; } ?> value="greater_than">Greater than</option>
											</select>
											<select name="conditionrating_<?php echo $i; ?>" id="conditionrating_<?php echo $i; ?>" class="element select condition_rating" style="width: 120px;<?php echo $condition_rating_display; ?>">
												<option <?php if($value['filter_condition'] == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
												<option <?php if($value['filter_condition'] == 'is_not'){ echo 'selected="selected"'; } ?> value="is_not">Is Not</option>
												<option <?php if($value['filter_condition'] == 'less_than'){ echo 'selected="selected"'; } ?> value="less_than">Less than</option>
												<option <?php if($value['filter_condition'] == 'greater_than'){ echo 'selected="selected"'; } ?> value="greater_than">Greater than</option>
											</select>
											<select name="conditiondate_<?php echo $i; ?>" id="conditiondate_<?php echo $i; ?>" class="element select condition_date" style="width: 120px;<?php echo $condition_date_display; ?>">
												<option <?php if($value['filter_condition'] == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
												<option <?php if($value['filter_condition'] == 'is_before'){ echo 'selected="selected"'; } ?> value="is_before">Is Before</option>
												<option <?php if($value['filter_condition'] == 'is_after'){ echo 'selected="selected"'; } ?> value="is_after">Is After</option>
											</select>
											<select name="conditionfile_<?php echo $i; ?>" id="conditionfile_<?php echo $i; ?>" class="element select condition_file" style="width: 120px;<?php echo $condition_file_display; ?>">
												<option <?php if($value['filter_condition'] == 'contains'){ echo 'selected="selected"'; } ?> value="contains">Contains</option>
												<option <?php if($value['filter_condition'] == 'not_contain'){ echo 'selected="selected"'; } ?> value="not_contain">Does not contain</option>
											</select>
											<select name="conditioncheckbox_<?php echo $i; ?>" id="conditioncheckbox_<?php echo $i; ?>" class="element select condition_checkbox" style="width: 120px;<?php echo $condition_checkbox_display; ?>">
												<option <?php if($value['filter_condition'] == 'is_one'){ echo 'selected="selected"'; } ?> value="is_one">Is Checked</option>
												<option <?php if($value['filter_condition'] == 'is_zero'){ echo 'selected="selected"'; } ?> value="is_zero">Is Empty</option>
											</select>
											<select name="conditionapprovalstatus_<?php echo $i; ?>" id="conditionapprovalstatus_<?php echo $i; ?>" class="element select condition_approval_status" style="width: 120px;<?php echo $condition_approval_status_display; ?>">
												<option <?php if($value['filter_condition'] == 'is_approved'){ echo 'selected="selected"'; } ?> value="is_approved">Is Approved</option>
												<option <?php if($value['filter_condition'] == 'is_denied'){ echo 'selected="selected"'; } ?> value="is_denied">Is Denied</option>
												<option <?php if($value['filter_condition'] == 'is_pending'){ echo 'selected="selected"'; } ?> value="is_pending">Is Pending</option>
											</select>
											<select id="conditionselect_<?php echo $i; ?>" name="conditionselect_<?php echo $i; ?>" autocomplete="off" class="element select condition_select" style="<?php echo $condition_select_display; ?>">
												<?php
													if(!empty($field_select_radio_data)){
														foreach ($field_select_radio_data as $option_title) {
															$option_value = $option_title;
															$option_title = strip_tags($option_title);
															$rule_keyword = htmlspecialchars($value['filter_keyword'],ENT_QUOTES);

															if(strlen($option_title) > 40){
																$option_title = substr($option_title, 0, 40).'...';
															}
															
															if($rule_keyword == $option_value){
																$selected_tag = 'selected="selected"';
															}else{
																$selected_tag = '';
															}

															echo "<option {$selected_tag} value=\"{$option_value}\">{$option_title}</option>\n";
														}
													}
												?>
											</select> 
											<select id="conditionratingvalues_<?php echo $i; ?>" name="conditionratingvalues_<?php echo $i; ?>" autocomplete="off" class="element select condition_ratingvalues" style="<?php echo $condition_ratingvalues_display; ?>">
												<?php

													$rating_max   = $rating_field_properties[$condition_element_id]['rating_max'] ?? 0;
													$rule_keyword = htmlspecialchars($value['filter_keyword'],ENT_QUOTES);

													for($j=1;$j<=$rating_max;$j++) {
														
														if($rule_keyword == $j){
															$selected_tag = 'selected="selected"';
														}else{
															$selected_tag = '';
														}

														echo "<option {$selected_tag} value=\"{$j}\">{$j}</option>\n";
													}
														
												?>
											</select>  
											<span name="conditiontime_<?php echo $i; ?>" id="conditiontime_<?php echo $i; ?>" class="condition_time" style="<?php echo $condition_time_display; ?>">
												<input name="conditiontimehour_<?php echo $i; ?>" id="conditiontimehour_<?php echo $i; ?>" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_hour; ?>" placeholder="HH"> : 
												<input name="conditiontimeminute_<?php echo $i; ?>" id="conditiontimeminute_<?php echo $i; ?>" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_minute; ?>" placeholder="MM">  
												<span class="conditiontime_second" style="<?php echo $condition_second_display; ?>"> : <input name="conditiontimesecond_<?php echo $user_id.'_'.$i; ?>" id="conditiontimesecond_<?php echo $user_id.'_'.$i; ?>" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_second; ?>" placeholder="SS"> </span>
												<select class="element select conditiontime_ampm conditiontime_input" name="conditiontimeampm_<?php echo $i; ?>" id="conditiontimeampm_<?php echo $i; ?>" style="<?php echo $condition_ampm_display; ?>">
													<option <?php if($time_ampm == 'AM'){ echo 'selected="selected"'; } ?> value="AM">AM</option>
													<option <?php if($time_ampm == 'PM'){ echo 'selected="selected"'; } ?> value="PM">PM</option>
												</select>
											</span>
											<input type="text" class="element text filter_keyword" value="<?php echo htmlspecialchars($value['filter_keyword'],ENT_QUOTES); ?>" id="filterkeyword_<?php echo $i; ?>" style="<?php echo $condition_keyword_display; ?>">
											<input type="hidden" value="" name="datepicker_<?php echo $i; ?>" id="datepicker_<?php echo $i; ?>">
											<span style="display:none"><img id="datepickimg_<?php echo $i; ?>" alt="Pick date." src="images/icons/calendar.png" class="trigger filter_date_trigger" style="vertical-align: top; cursor: pointer" /></span>
											<a href="#" id="deletefilter_<?php echo $i; ?>" class="filter_delete_a"><span class="icon-minus-circle2"></span></a>
										</li>
													
										
											
										<?php 	
												$i++;
												}//end foreach filter_data
											} //end else
										?>

										<li id="li_filter_add" class="filter_add" style="text-align: right">
											<a href="#" id="filter_add_a"><span class="icon-plus-circle"></span></a>
										</li>
									</ul>
											
									</div>
									<!-- end widget filter pane -->

								</div>
							</div>
						</li>
						<li class="ps_arrow"><span class="icon-arrow-down11 spacer-icon"></span></li>
						<li>
							<div id="ew_box_widget_options" class="ew_box_main gradient_red">
								<div class="ew_box_meta">
									<h1>2.</h1>
									<h6>Widget Options</h6>
								</div>
								<div class="ew_box_content">
									<ul id="ew_widget_options_list">
										<?php if($show_chart_theme_property){ ?>
										<li id="li_chart_theme" style="width: 162px; float: left;"> 
											<label class="description inline" for="ew_chart_theme" style="margin-top: 2px"> Theme
											</label>
											<span class="icon-question helpicon clearfix" data-tippy-content="Select the color scheme of your widget."></span>
											<select class="select large" id="ew_chart_theme" name="ew_chart_theme" autocomplete="off">
												<option value="blueopal" <?php if($chart_theme == 'blueopal'){ echo 'selected="selected"'; } ?>>Blue Opal</option>
												<option value="bootstrap" <?php if($chart_theme == 'bootstrap'){ echo 'selected="selected"'; } ?>>Bootstrap</option>
												<option value="fiori" <?php if($chart_theme == 'fiori'){ echo 'selected="selected"'; } ?>>Fiori</option>
												<option value="flat" <?php if($chart_theme == 'flat'){ echo 'selected="selected"'; } ?>>Flat</option>
												<option value="material" <?php if($chart_theme == 'material'){ echo 'selected="selected"'; } ?>>Material</option>
												<option value="metro" <?php if($chart_theme == 'metro'){ echo 'selected="selected"'; } ?>>Metro</option>
												<option value="nova" <?php if($chart_theme == 'nova'){ echo 'selected="selected"'; } ?>>Nova</option>
												<option value="office365" <?php if($chart_theme == 'office365'){ echo 'selected="selected"'; } ?>>Office365</option>
												<option value="default" <?php if($chart_theme == 'default'){ echo 'selected="selected"'; } ?>>Orange</option>
												<option value="silver" <?php if($chart_theme == 'silver'){ echo 'selected="selected"'; } ?>>Silver</option>
												<option value="uniform" <?php if($chart_theme == 'uniform'){ echo 'selected="selected"'; } ?>>Uniform</option>
												<optgroup label="Use on Dark Backgrounds">
													<option value="black" <?php if($chart_theme == 'black'){ echo 'selected="selected"'; } ?>>Black</option>
													<option value="highcontrast" <?php if($chart_theme == 'highcontrast'){ echo 'selected="selected"'; } ?>>High Contrast</option>
													<option value="materialblack" <?php if($chart_theme == 'materialblack'){ echo 'selected="selected"'; } ?>>Material Black</option>
													<option value="metroblack" <?php if($chart_theme == 'metroblack'){ echo 'selected="selected"'; } ?>>Metro Black</option>
													<option value="moonlight" <?php if($chart_theme == 'moonlight'){ echo 'selected="selected"'; } ?>>Moonlight</option>
												</optgroup>
											</select>
										</li>
										<?php } ?>

										<li id="li_background_color" style="width: 160px; margin-left: 25px;float: left;display: <?php if($show_background_color_property){ echo 'block'; }else{ echo 'none'; } ?>"> 
											<div class="minicolors_container" >
												<label class="description inline" style="margin-top: 2px"> Background Color
												</label>
												<span class="icon-question helpicon clearfix" data-tippy-content="Leave this empty to use transparent background color (recommended)."></span>
												<input type="text" id="ew_chart_background" name="ew_chart_background" class="colors text" style="vertical-align: middle"  size="7" value="" />
											</div>
										</li>
										
										<?php if($show_rating_theme_property){ ?>
										<li id="li_rating_theme" style="width: 162px; float: left"> 
											<label class="description inline" for="ew_rating_theme" style="margin-top: 2px"> Theme
											</label>
											<span class="icon-question helpicon clearfix" data-tippy-content="Select the color scheme of your rating scorecard."></span>
											<select class="select large" id="ew_chart_theme" name="ew_chart_theme" autocomplete="off">
												<option value="rating-light" <?php if($chart_theme == 'rating-light'){ echo 'selected="selected"'; } ?>>Light</option>
												<option value="rating-dark" <?php if($chart_theme == 'rating-dark'){ echo 'selected="selected"'; } ?>>Dark</option>
											</select>
										</li>
										<?php } ?>
										
										<li id="li_line_style" style="width: 162px; clear: both; padding-top: 10px; margin-top: 10px;display: <?php if($show_line_style_property){ echo 'block'; }else{ echo 'none'; } ?>"> 
											<label class="description inline" for="ew_chart_line_style" style="margin-top: 2px"> Graph Line Style
											</label>
											<span class="icon-question helpicon clearfix" data-tippy-content="Select the line style of the graph."></span>
											<select class="select large" id="ew_chart_line_style" name="ew_chart_line_style" autocomplete="off">
												<option value="normal" <?php if($chart_line_style == 'normal'){ echo 'selected="selected"'; } ?>>Straight</option>
												<option value="smooth" <?php if($chart_line_style == 'smooth'){ echo 'selected="selected"'; } ?>>Smooth</option>
												<option value="step" <?php if($chart_line_style == 'step'){ echo 'selected="selected"'; } ?>>Step</option>
											</select>
										</li>
										<li id="li_show_title" style="clear: both;padding-top: 15px">
											<input id="ew_show_title" name="ew_show_title"  <?php if(!empty($chart_title)){ echo 'checked="checked"'; } ?>  class="checkbox" value="" type="checkbox" style="margin-left: 0px">
											<label class="choice" for="ew_show_title">Show Title</label>
											<div id="ew_show_title_div" <?php if(empty($chart_title)){ echo 'style="display: none"'; } ?>>
												<label class="description" for="ew_chart_title">Title</label>
												<input id="ew_chart_title" name="ew_chart_title" class="element text large" value="<?php echo $chart_title; ?>" type="text">

												<span style="display: <?php if($chart_type == 'rating'){ echo "none";  }else{ echo "block"; } ?>; float: left; width: 45%;">
													<label class="description" for="ew_chart_title_position"> Position</label>
													<select class="select large" id="ew_chart_title_position" name="ew_chart_title_position" autocomplete="off">
														<option value="top" <?php if($chart_title_position == 'top'){ echo 'selected="selected"'; } ?>>Top</option>
														<option value="bottom" <?php if($chart_title_position == 'bottom'){ echo 'selected="selected"'; } ?>>Bottom</option>
													</select>
												</span>

												<span style="display: block; float: <?php if($chart_type == 'rating'){ echo "left";  }else{ echo "right"; } ?>; width: 45%;">
													<label class="description" for="ew_chart_title_align"> Alignment</label>
													<select class="select large" id="ew_chart_title_align" name="ew_chart_title_align" autocomplete="off">
														<option value="center" <?php if($chart_title_align == 'center'){ echo 'selected="selected"'; } ?>>Center</option>
														<option value="left" <?php if($chart_title_align == 'left'){ echo 'selected="selected"'; } ?>>Left</option>
														<option value="right" <?php if($chart_title_align == 'right'){ echo 'selected="selected"'; } ?>>Right</option>
													</select>
												</span>
											</div>
										</li>
										<li id="li_show_labels" style="padding-top: 5px;display: <?php if($show_labels_property){ echo 'block'; }else{ echo 'none'; } ?>">
											<input id="ew_show_labels" name="ew_show_labels"  <?php if(!empty($chart_labels_visible)){ echo 'checked="checked"'; } ?>  class="checkbox" value="" type="checkbox" style="margin-left: 0px">
											<label class="choice" for="ew_show_labels">Show Labels</label>
											<div id="ew_show_labels_div" <?php if(empty($chart_labels_visible)){ echo 'style="display: none"'; } ?>>
												<label class="description" for="ew_chart_labels_template">Template</label>
												
												<?php if($chart_type == 'pie' || $chart_type == 'donut'){ ?>
												<select class="select large" id="ew_chart_labels_template" name="ew_chart_labels_template" autocomplete="off">
														<option value="#= kendo.format('{0:P}', percentage)#" <?php if($chart_labels_template == '#= kendo.format(\'{0:P}\', percentage)#'){ echo 'selected="selected"'; } ?>>{Percentage}</option>
														<option value="#= dataItem.entry # entries" <?php if($chart_labels_template == "#= dataItem.entry # entries"){ echo 'selected="selected"'; } ?>>{Total Entries}</option>
														<option value="#= dataItem.entry # entries - #= kendo.format('{0:P}', percentage)#" <?php if($chart_labels_template == '#= dataItem.entry # entries - #= kendo.format(\'{0:P}\', percentage)#'){ echo 'selected="selected"'; } ?>>{Total Entries} - {Percentage}</option>
														<option value="#= category #" <?php if($chart_labels_template == '#= category #'){ echo 'selected="selected"'; } ?>>{Category Name}</option>
														<option value="#= category # - #= kendo.format('{0:P}', percentage)#" <?php if($chart_labels_template == '#= category # - #= kendo.format(\'{0:P}\', percentage)#'){ echo 'selected="selected"'; } ?>>{Category Name} - {Percentage}</option>
														<option value="#= category # - #= dataItem.entry # entries" <?php if($chart_labels_template == '#= category # - #= dataItem.entry # entries'){ echo 'selected="selected"'; } ?>>{Category Name} - {Total Entries}</option>
														<option value="#= category # - #= dataItem.entry # entries - #= kendo.format('{0:P}', percentage)#" <?php if($chart_labels_template == '#= category # - #= dataItem.entry # entries - #= kendo.format(\'{0:P}\', percentage)#'){ echo 'selected="selected"'; } ?>>{Category Name} - {Total Entries} - {Percentage}</option>
												</select>
												
												<?php }else if($chart_type == 'bar' || ($chart_type == 'line' && empty($chart_axis_is_date)) || ($chart_type == 'area' && empty($chart_axis_is_date)) ){ ?>
												
												<select class="select large" id="ew_chart_labels_template" name="ew_chart_labels_template" autocomplete="off">
														<option value="#= dataItem.percentage #" <?php if($chart_labels_template == '#= dataItem.percentage #'){ echo 'selected="selected"'; } ?>>{Percentage}</option>
														<option value="#= value # entries" <?php if($chart_labels_template == "#= value # entries"){ echo 'selected="selected"'; } ?>>{Total Entries}</option>
														<option value="#= value # entries - #= dataItem.percentage #" <?php if($chart_labels_template == '#= value # entries - #= dataItem.percentage #'){ echo 'selected="selected"'; } ?>>{Total Entries} - {Percentage}</option>
														<option value="#= category #" <?php if($chart_labels_template == '#= category #'){ echo 'selected="selected"'; } ?>>{Category Name}</option>
														<option value="#= category # - #= dataItem.percentage #" <?php if($chart_labels_template == '#= category # - #= dataItem.percentage #'){ echo 'selected="selected"'; } ?>>{Category Name} - {Percentage}</option>
														<option value="#= category # - #= value # entries" <?php if($chart_labels_template == '#= category # - #= value # entries'){ echo 'selected="selected"'; } ?>>{Category Name} - {Total Entries}</option>
														<option value="#= category # - #= value # entries - #= dataItem.percentage #" <?php if($chart_labels_template == '#= category # - #= value # entries - #= dataItem.percentage #'){ echo 'selected="selected"'; } ?>>{Category Name} - {Total Entries} - {Percentage}</option>
												</select>
												
												<?php }else if( ($chart_type == 'line' && !empty($chart_axis_is_date)) || ($chart_type == 'area' && !empty($chart_axis_is_date)) ){ ?>
												
												<select class="select large" id="ew_chart_labels_template" name="ew_chart_labels_template" autocomplete="off">
														<option value="#= value # entries" <?php if($chart_labels_template == "#= value # entries"){ echo 'selected="selected"'; } ?>>{Total Entries}</option>
												</select>
												
												<?php } ?>

												<span style="display: block; float: left; width: 45%">
													<label class="description" for="ew_chart_labels_position"> Position</label>
													<select class="select large" id="ew_chart_labels_position" name="ew_chart_labels_position" autocomplete="off">
														
														<?php if($chart_type == 'pie' || $chart_type == 'donut'){ ?>
					
															<option value="right" <?php if($chart_labels_position == 'right'){ echo 'selected="selected"'; } ?>>Outside</option>
															<option value="outsideEnd" <?php if($chart_labels_position == 'outsideEnd'){ echo 'selected="selected"'; } ?>>Outside with Line</option>
															<option value="insideEnd" <?php if($chart_labels_position == 'insideEnd'){ echo 'selected="selected"'; } ?>>Inside</option>
															<option value="center" <?php if($chart_labels_position == 'center'){ echo 'selected="selected"'; } ?>>Center</option>
												
														<?php }else if($chart_type == 'bar'){ ?>
															
															<option value="outsideEnd" <?php if($chart_labels_position == 'outsideEnd'){ echo 'selected="selected"'; } ?>>Outside Top</option>
															<option value="insideEnd" <?php if($chart_labels_position == 'insideEnd'){ echo 'selected="selected"'; } ?>>Inside Top</option>
															<option value="center" <?php if($chart_labels_position == 'center'){ echo 'selected="selected"'; } ?>>Inside Center</option>
															<option value="insideBase" <?php if($chart_labels_position == 'insideBase'){ echo 'selected="selected"'; } ?>>Inside Bottom</option>
														
														<?php }else if($chart_type == 'line' || $chart_type == 'area'){ ?>
															
															<option value="above" <?php if($chart_labels_position == 'above'){ echo 'selected="selected"'; } ?>>Above</option>
															<option value="below" <?php if($chart_labels_position == 'below'){ echo 'selected="selected"'; } ?>>Below</option>
															<option value="center" <?php if($chart_labels_position == 'center'){ echo 'selected="selected"'; } ?>>Center</option>
															<option value="right" <?php if($chart_labels_position == 'right'){ echo 'selected="selected"'; } ?>>Right</option>
															<option value="left" <?php if($chart_labels_position == 'left'){ echo 'selected="selected"'; } ?>>Left</option>
															
														<?php } ?>
													</select>
												</span>

												<span id="ew_chart_labels_align_span" style="display: <?php if( ($chart_type == 'pie' || $chart_type == 'donut') && ($chart_labels_position == 'outsideEnd') ){ echo 'block'; }else{ echo 'none'; } ?>; float: right; width: 45%;">
													<label class="description" for="ew_chart_labels_align"> Alignment</label>
													<select class="select large" id="ew_chart_labels_align" name="ew_chart_labels_align" autocomplete="off">
														<option value="circle" <?php if($chart_labels_align == 'circle'){ echo 'selected="selected"'; } ?>>Aligned in Circle</option>
														<option value="column" <?php if($chart_labels_align == 'column'){ echo 'selected="selected"'; } ?>>Aligned in Column</option>
													</select>
												</span>
											</div>
										</li>
										<li id="li_show_legend" style="padding-top: 5px;display: <?php if($show_legend_property){ echo 'block'; }else{ echo 'none'; } ?>">
											<input id="ew_show_legend" name="ew_show_legend"  <?php if(!empty($chart_legend_visible)){ echo 'checked="checked"'; } ?>  class="checkbox" value="" type="checkbox" style="margin-left: 0px">
											<label class="choice" for="ew_show_legend">Show Legend</label>
											<div id="ew_show_legend_div" <?php if(empty($chart_legend_visible)){ echo 'style="display: none"'; } ?>>
												<label class="description" for="ew_chart_legend_position"> Position</label>
												<select class="select large" id="ew_chart_legend_position" name="ew_chart_legend_position" autocomplete="off">
													<option value="top" <?php if($chart_legend_position == 'top'){ echo 'selected="selected"'; } ?>>Top</option>
													<option value="bottom" <?php if($chart_legend_position == 'bottom'){ echo 'selected="selected"'; } ?>>Bottom</option>
													<option value="left" <?php if($chart_legend_position == 'left'){ echo 'selected="selected"'; } ?>>Left</option>
													<option value="right" <?php if($chart_legend_position == 'right'){ echo 'selected="selected"'; } ?>>Right</option>
												</select>
											</div>
										</li>
										<li id="li_show_tooltip" style="padding-top: 5px;display: <?php if($show_tooltip_property){ echo 'block'; }else{ echo 'none'; } ?>">
											<input id="ew_show_tooltip" name="ew_show_tooltip"  <?php if(!empty($chart_tooltip_visible)){ echo 'checked="checked"'; } ?>  class="checkbox" value="" type="checkbox" style="margin-left: 0px">
											<label class="choice" for="ew_show_tooltip">Show Tooltips</label>
											<div id="ew_show_tooltip_div" <?php if(empty($chart_tooltip_visible)){ echo 'style="display: none"'; } ?>>
												<label class="description" for="ew_chart_tooltip_template">Template</label>
												
												<?php if($chart_type == 'pie' || $chart_type == 'donut'){ ?>
												<select class="select large" id="ew_chart_tooltip_template" name="ew_chart_tooltip_template" autocomplete="off">
														<option value="#= kendo.format('{0:P}', percentage)#" <?php if($chart_tooltip_template == '#= kendo.format(\'{0:P}\', percentage)#'){ echo 'selected="selected"'; } ?>>{Percentage}</option>
														<option value="#= dataItem.entry # entries" <?php if($chart_tooltip_template == "#= dataItem.entry # entries"){ echo 'selected="selected"'; } ?>>{Total Entries}</option>
														<option value="#= dataItem.entry # entries - #= kendo.format('{0:P}', percentage)#" <?php if($chart_tooltip_template == '#= dataItem.entry # entries - #= kendo.format(\'{0:P}\', percentage)#'){ echo 'selected="selected"'; } ?>>{Total Entries} - {Percentage}</option>
														<option value="#= category #" <?php if($chart_tooltip_template == '#= category #'){ echo 'selected="selected"'; } ?>>{Category Name}</option>
														<option value="#= category # - #= kendo.format('{0:P}', percentage)#" <?php if($chart_tooltip_template == '#= category # - #= kendo.format(\'{0:P}\', percentage)#'){ echo 'selected="selected"'; } ?>>{Category Name} - {Percentage}</option>
														<option value="#= category # - #= dataItem.entry # entries" <?php if($chart_tooltip_template == '#= category # - #= dataItem.entry # entries'){ echo 'selected="selected"'; } ?>>{Category Name} - {Total Entries}</option>
														<option value="#= category # - #= dataItem.entry # entries - #= kendo.format('{0:P}', percentage)#" <?php if($chart_tooltip_template == '#= category # - #= dataItem.entry # entries - #= kendo.format(\'{0:P}\', percentage)#'){ echo 'selected="selected"'; } ?>>{Category Name} - {Total Entries} - {Percentage}</option>
												</select>
												
												<?php }else if($chart_type == 'bar' || ($chart_type == 'line' && empty($chart_axis_is_date)) || ($chart_type == 'area' && empty($chart_axis_is_date)) ){ ?>
												
												<select class="select large" id="ew_chart_tooltip_template" name="ew_chart_tooltip_template" autocomplete="off">
														<option value="#= dataItem.percentage #" <?php if($chart_tooltip_template == '#= dataItem.percentage #'){ echo 'selected="selected"'; } ?>>{Percentage}</option>
														<option value="#= value # entries" <?php if($chart_tooltip_template == "#= value # entries"){ echo 'selected="selected"'; } ?>>{Total Entries}</option>
														<option value="#= value # entries - #= dataItem.percentage #" <?php if($chart_tooltip_template == '#= value # entries - #= dataItem.percentage #'){ echo 'selected="selected"'; } ?>>{Total Entries} - {Percentage}</option>
														
														<option value="#= category #" <?php if($chart_tooltip_template == '#= category #'){ echo 'selected="selected"'; } ?>>{Category Name}</option>
														<option value="#= category # - #= dataItem.percentage #" <?php if($chart_tooltip_template == '#= category # - #= dataItem.percentage #'){ echo 'selected="selected"'; } ?>>{Category Name} - {Percentage}</option>
														<option value="#= category # - #= value # entries" <?php if($chart_tooltip_template == '#= category # - #= value # entries'){ echo 'selected="selected"'; } ?>>{Category Name} - {Total Entries}</option>
														<option value="#= category # - #= value # entries - #= dataItem.percentage #" <?php if($chart_tooltip_template == '#= category # - #= value # entries - #= dataItem.percentage #'){ echo 'selected="selected"'; } ?>>{Category Name} - {Total Entries} - {Percentage}</option>
														
														<option value="#= series.name #" <?php if($chart_tooltip_template == '#= series.name #'){ echo 'selected="selected"'; } ?>>{Series Name}</option>
														<option value="#= series.name # - #= dataItem.percentage #" <?php if($chart_tooltip_template == '#= series.name # - #= dataItem.percentage #'){ echo 'selected="selected"'; } ?>>{Series Name} - {Percentage}</option>
														<option value="#= series.name # - #= value # entries" <?php if($chart_tooltip_template == '#= series.name # - #= value # entries'){ echo 'selected="selected"'; } ?>>{Series Name} - {Total Entries}</option>
														<option value="#= series.name # - #= value # entries - #= dataItem.percentage #" <?php if($chart_tooltip_template == '#= series.name # - #= value # entries - #= dataItem.percentage #'){ echo 'selected="selected"'; } ?>>{Series Name} - {Total Entries} - {Percentage}</option>
												</select>
												
												<?php }else if( ($chart_type == 'line' && !empty($chart_axis_is_date)) || ($chart_type == 'area' && !empty($chart_axis_is_date)) ){ ?>
												
												<select class="select large" id="ew_chart_tooltip_template" name="ew_chart_tooltip_template" autocomplete="off">
														<option value="#= value # entries" <?php if($chart_tooltip_template == "#= value # entries"){ echo 'selected="selected"'; } ?>>{Total Entries}</option>
												</select>
												
												<?php } ?>

											</div>
										</li>
										<li id="li_show_rating_bars" style="padding-top: 5px;display: <?php if($show_rating_bars_property){ echo 'block'; }else{ echo 'none'; } ?>">
											<input id="ew_show_rating_bars" name="ew_show_rating_bars"  <?php if(!empty($chart_rating_bars_visible)){ echo 'checked="checked"'; } ?>  class="checkbox" value="" type="checkbox" style="margin-left: 0px">
											<label class="choice" for="ew_show_rating_bars">Show Rating Bars</label>
										</li>
										<li id="li_show_rating_total_entries" style="padding-top: 5px;display: <?php if($show_rating_total_entries_property){ echo 'block'; }else{ echo 'none'; } ?>">
											<input id="ew_show_rating_total_entries" name="ew_show_rating_total_entries"  <?php if(!empty($chart_rating_total_visible)){ echo 'checked="checked"'; } ?>  class="checkbox" value="" type="checkbox" style="margin-left: 0px">
											<label class="choice" for="ew_show_rating_total_entries">Show Total Entries</label>
										</li>
										<li id="li_show_gridlines" style="padding-top: 5px;display: <?php if($show_gridlines_property){ echo 'block'; }else{ echo 'none'; } ?>">  
											<input id="ew_show_gridlines" name="ew_show_gridlines"  <?php if(!empty($chart_gridlines_visible)){ echo 'checked="checked"'; } ?>  class="checkbox" value="" type="checkbox" style="margin-left: 0px">
											<label class="choice" for="ew_show_gridlines">Show Gridlines</label>
										</li>
										<li id="li_chart_stacked" style="padding-top: 5px;display: <?php if($show_stack_property){ echo 'block'; }else{ echo 'none'; } ?>">  
											<input id="ew_chart_is_stacked" name="ew_chart_is_stacked"  <?php if(!empty($chart_is_stacked)){ echo 'checked="checked"'; } ?>  class="checkbox" value="" type="checkbox" style="margin-left: 0px">
											<label class="choice" for="ew_chart_is_stacked">Use stacked chart mode</label>
										</li>
										<li id="li_chart_vertical" style="padding-top: 5px;display: <?php if($show_vertical_property){ echo 'block'; }else{ echo 'none'; } ?>">  
											<input id="ew_chart_is_vertical" name="ew_chart_is_vertical"  <?php if(!empty($chart_is_vertical)){ echo 'checked="checked"'; } ?>  class="checkbox" value="" type="checkbox" style="margin-left: 0px">
											<label class="choice" for="ew_chart_is_vertical">Plot bar graph horizontally</label>
										</li>
										<li id="li_bar_color" style="width: 362px;padding-top: 10px; margin-top: 15px; border-top: 1px dashed #DF8F7D;display: <?php if($show_bar_color_property){ echo 'block'; }else{ echo 'none'; } ?>"> 
											<div class="minicolors_container" >
												<label class="description inline" style="margin-top: 2px"> Bar Color
												</label>
												<span class="icon-question helpicon clearfix" data-tippy-content="Leave this empty to use default color provided by the widget theme."></span>
												<input type="text" id="ew_chart_bar_color" name="ew_chart_bar_color" class="colors text" style="vertical-align: middle"  size="7" value="" />
											</div>
										</li>
										<li id="li_date_range" style="width: 362px;padding-top: 10px; margin-top: 15px; border-top: 1px dashed #DF8F7D;display: <?php if($show_date_range_property){ echo 'block'; }else{ echo 'none'; } ?>"> 
											<label class="description inline" for="ew_chart_date_range" style="margin-top: 2px"> Date Range
											</label>
											<span class="icon-question helpicon clearfix" data-tippy-content="Select the range of your entries submission date that your chart will be based on. If you have set any filter previously, this date range will be applied on top of that filter."></span>
											<select class="select" style="width: 160px" id="ew_chart_date_range" name="ew_chart_date_range" autocomplete="off">
												<option value="all" <?php if($chart_date_range == 'all'){ echo 'selected="selected"'; } ?>>All</option>
												<option value="period" <?php if($chart_date_range == 'period'){ echo 'selected="selected"'; } ?>>Specific Period</option>
												<option value="custom" <?php if($chart_date_range == 'custom'){ echo 'selected="selected"'; } ?>>Custom Date Range</option>
											</select>
											<div id="ew_show_date_range_period_div" <?php if($chart_date_range != 'period'){ echo 'style="display: none"'; } ?>>
												<span>
													&#8674; Use data from the last 
													<select class="select small" style="width: 50px" id="ew_chart_date_period_value" name="ew_chart_date_period_value" autocomplete="off">
														<?php
															for($i=1;$i<=31;$i++){
																$selected_tag = '';
																if($chart_date_period_value == $i){
																	$selected_tag = 'selected="selected"';
																}
																echo "<option {$selected_tag} value=\"{$i}\">{$i}</option>\n";
															}
														?>
													</select>
													<select class="select small" id="ew_chart_date_period_unit" name="ew_chart_date_period_unit" autocomplete="off">
														<option value="day" <?php if($chart_date_period_unit == 'day'){ echo 'selected="selected"'; } ?>>Day</option>
														<option value="week" <?php if($chart_date_period_unit == 'week'){ echo 'selected="selected"'; } ?>>Week</option>
														<option value="month" <?php if($chart_date_period_unit == 'month'){ echo 'selected="selected"'; } ?>>Month</option>
														<option value="year" <?php if($chart_date_period_unit == 'year'){ echo 'selected="selected"'; } ?>>Year</option>
													</select>
												</span>
												<span style="margin-top: 10px">
													&#8674; Display 
													<select class="select small" style="" id="ew_chart_date_axis_baseunit_period" name="ew_chart_date_axis_baseunit_period" autocomplete="off">
														<option value="" <?php if($chart_date_axis_baseunit == ''){ echo 'selected="selected"'; } ?>>-auto-</option>
														<option value="day" <?php if($chart_date_axis_baseunit == 'day'){ echo 'selected="selected"'; } ?>>Days</option>
														<option value="week" <?php if($chart_date_axis_baseunit == 'week'){ echo 'selected="selected"'; } ?>>Weeks</option>
														<option value="month" <?php if($chart_date_axis_baseunit == 'month'){ echo 'selected="selected"'; } ?>>Months</option>
														<option value="year" <?php if($chart_date_axis_baseunit == 'year'){ echo 'selected="selected"'; } ?>>Years</option>
													</select> on horizontal axis
												</span>
											</div>
											<div id="ew_show_date_range_custom_div" <?php if($chart_date_range != 'custom'){ echo 'style="display: none"'; } ?>>
												<ul id="li_date_range_custom">
													<li style="float: left; width: 45%">
														<label class="description" for="ew_chart_date_range_start" style="margin-top: 0px"> Start Date</label>
														<input type="text" class="element text" style="width: 100px" value="<?php echo $chart_date_range_start; ?>" name="ew_chart_date_range_start" id="ew_chart_date_range_start">
														<input type="hidden" value="" name="datepicker_chart_date_range_start" id="datepicker_chart_date_range_start">
														<span style="display:none">
															<img id="datepickimg_chart_date_range_start" alt="Pick date." src="images/icons/calendar.png" class="trigger" style="vertical-align: top; cursor: pointer" />
														</span>
													</li>
													<li style="float: left; width: 45%">
														<label class="description" for="ew_chart_date_range_end" style="margin-top: 0px"> End Date</label>
														<input type="text" class="element text" style="width: 100px" value="<?php echo $chart_date_range_end; ?>" name="ew_chart_date_range_end" id="ew_chart_date_range_end">
														<input type="hidden" value="" name="datepicker_chart_date_range_end" id="datepicker_chart_date_range_end">
														<span style="display:none">
															<img id="datepickimg_chart_date_range_end" alt="Pick date." src="images/icons/calendar.png" class="trigger" style="vertical-align: top; cursor: pointer" />
														</span>
													</li>
													<li style="clear: both;padding-top: 15px">
														&#8674; Display 
														<select class="select small" style="" id="ew_chart_date_axis_baseunit_custom" name="ew_chart_date_axis_baseunit_custom" autocomplete="off">
															<option value="" <?php if($chart_date_axis_baseunit == ''){ echo 'selected="selected"'; } ?>>-auto-</option>
															<option value="day" <?php if($chart_date_axis_baseunit == 'day'){ echo 'selected="selected"'; } ?>>Days</option>
															<option value="week" <?php if($chart_date_axis_baseunit == 'week'){ echo 'selected="selected"'; } ?>>Weeks</option>
															<option value="month" <?php if($chart_date_axis_baseunit == 'month'){ echo 'selected="selected"'; } ?>>Months</option>
															<option value="year" <?php if($chart_date_axis_baseunit == 'year'){ echo 'selected="selected"'; } ?>>Years</option>
														</select> on horizontal axis
													</li>
												</ul>
											</div>
										</li>
										<li id="li_grid_page_size" style="width: 162px;margin-top:10px; float: left;display: <?php if($chart_type == 'grid'){ echo 'block'; }else{ echo 'none'; } ?>"> 
											<label class="description inline" for="ew_grid_page_size" style="margin-top: 2px">Rows Per Page
											</label>
											<span class="icon-question helpicon clearfix" data-tippy-content="The number of rows of data to appear on each page. Minimum value is 1."></span>
											<input id="ew_grid_page_size" name="ew_grid_page_size" class="element text small" value="<?php echo $chart_grid_page_size; ?>" type="text">
										</li>
										<li id="li_grid_max_length" style="width: 160px;margin-top:10px;margin-left: 25px;float: left;display: <?php if($chart_type == 'grid'){ echo 'block'; }else{ echo 'none'; } ?>"> 
											<label class="description inline" for="ew_grid_max_length" style="margin-top: 2px">Cell Max Length
											</label>
											<span class="icon-question helpicon clearfix" data-tippy-content="The maximum number of characters can be displayed in each cell of the grid. Leave this empty or enter 0 to remove any limit (NOTE: This might cause your grid to load slower)."></span>
											<input id="ew_grid_max_length" name="ew_grid_max_length" class="element text small" value="<?php echo $chart_grid_max_length; ?>" type="text">
										</li>
										<li id="li_grid_columns" style="width: 385px;clear: both;padding-top: 10px;padding-bottom: 10px;display: <?php if($chart_type == 'grid'){ echo 'block'; }else{ echo 'none'; } ?>">
											<h6>Select fields to be displayed:</h6>
											<ul>
												<?php
													if(!empty($form_properties['form_approval_enable'])){ 
														$columns_label['approval_status'] = 'Approval Status';
													}
													
													$checked_tag = '';

													foreach($columns_label as $element_name=>$element_label){
														//don't display signature field
														if($columns_type[$element_name] == 'signature'){
															continue;
														}
														if(!empty($current_column_preference)){
															if(in_array($element_name,$current_column_preference)){
																$checked_tag = 'checked="checked"';
															}else{
																$checked_tag = '';
															}
														}
												?>
													<li>
														<input type="checkbox" value="1" <?php echo $checked_tag; ?> class="element checkbox" name="<?php echo $element_name; ?>" id="<?php echo $element_name; ?>">
														<label for="<?php echo $element_name; ?>" title="<?php echo $element_label; ?>" class="choice"><?php echo $element_label; ?></label>
													</li>
												<?php } ?>		
											</ul>
										<li>
										<li id="li_grid_sorting" style="width: 385px;clear: both;padding-top: 10px;padding-bottom: 10px;display: <?php if($chart_type == 'grid'){ echo 'block'; }else{ echo 'none'; } ?>">
											<h6>Default sorting order</h6>

											<div id="ew_grid_sort_option" style="padding-top: 10px">
												<label class="description" for="ew_chart_grid_sort_by" style="display: inline">Sort By &#8674; </label>
												<select class="element select" id="ew_chart_grid_sort_by" name="ew_chart_grid_sort_by"> 
													<optgroup label="Ascending">
														<?php 
															foreach ($columns_label as $element_name => $element_label) {

																//don't display signature field
																if($columns_type[$element_name] == 'signature'){
																	continue;
																}

																//id is basically the same as date_created, but lot faster for sorting
																if($element_name == 'date_created'){
																	$element_name = 'id'; 
																}

																if(strlen($element_label) > 40){
																	$element_label = substr($element_label, 0, 40).'...';
																}

																if($chart_grid_sort_by == $element_name.'-asc'){
																	$selected_tag = 'selected="selected"';
																}else{
																	$selected_tag = '';
																}

																echo "<option {$selected_tag} value=\"{$element_name}-asc\">{$element_label}</option>\n";
															}
														?>
													</optgroup>
													<optgroup label="Descending">
														<?php 
															foreach ($columns_label as $element_name => $element_label) {

																//don't display signature field
																if($columns_type[$element_name] == 'signature'){
																	continue;
																}
																
																//id is basically the same as date_created, but lot faster for sorting
																if($element_name == 'date_created'){
																	$element_name = 'id';
																}

																if(strlen($element_label) > 40){
																	$element_label = substr($element_label, 0, 40).'...';
																}

																if($chart_grid_sort_by == $element_name.'-desc'){
																	$selected_tag = 'selected="selected"';
																}else{
																	$selected_tag = '';
																}

																echo "<option {$selected_tag} value=\"{$element_name}-desc\">{$element_label}</option>\n";
															}
														?>
													</optgroup>
												</select>
											</div>
										<li>	
									</ul>
								</div>
							</div>
						</li>
						<li class="ps_arrow"><span class="icon-arrow-down11 spacer-icon"></span></li>
						<li>
							<div id="ew_box_widget_size" class="ew_box_main gradient_green">
								<div class="ew_box_meta">
									<h1>3.</h1>
									<h6>Widget Size</h6>
								</div>
								<div class="ew_box_content">
									<?php if($chart_type != 'rating'){ ?>
									<label class="description inline" for="ew_chart_height" style="margin-top: 10px">
										Widget Height 
									</label>
									<span class="icon-question helpicon clearfix" data-tippy-content="The width of your widget will be automatically calculated based on the height you set here and based on the width of the container where you embed the widget."></span>
									<select class="select medium" id="ew_chart_height" autocomplete="off">
										<option <?php if($chart_height == 200){ echo 'selected="selected"'; } ?> value="200">Small (200px)</option>
										<option <?php if($chart_height == 400){ echo 'selected="selected"'; } ?> value="400">Medium (400px)</option>
										<option <?php if($chart_height == 600){ echo 'selected="selected"'; } ?> value="600">Large (600px)</option>
										<option <?php if(!in_array($chart_height, array(200,400,600))){ echo 'selected="selected"'; } ?> value="custom">Custom Height</option>
									</select>
									<div id="custom_widget_height_div" style="display: <?php if(!in_array($chart_height, array(200,400,600))){ echo 'block'; }else{ echo 'none'; } ?>">
										<label class="description" for="ew_chart_height_custom" style="margin-top: 10px">Height</label>
										<input id="ew_chart_height_custom" name="ew_chart_height_custom" class="element text small" style="width: 40px" value="<?php echo $chart_height; ?>" type="text">
									</div>
									<?php } ?>

									<?php if($chart_type == 'rating'){ ?>
									<div id="ew_rating_size_container">
										<span>
											<input id="ew_rating_small" name="ew_rating_size" class="radio" value="small" <?php if($chart_rating_size == 'small'){ echo 'checked="checked"'; } ?> type="radio">
											<label class="choice" for="ew_rating_small">Small</label>
										</span>
										<span>
											<input id="ew_rating_medium" name="ew_rating_size" class="radio" value="medium" <?php if($chart_rating_size == 'medium'){ echo 'checked="checked"'; } ?> type="radio">
											<label class="choice" for="ew_rating_medium">Medium</label>
										</span>
										<span>
											<input id="ew_rating_large" name="ew_rating_size" class="radio" value="large" <?php if($chart_rating_size == 'large'){ echo 'checked="checked"'; } ?> type="radio">
											<label class="choice" for="ew_rating_large">Large</label>
										</span>
									</div>
									<?php } ?>

								</div>
							</div>
						</li>
						<li class="ps_arrow"><span class="icon-arrow-down11 spacer-icon"></span></li>
						<li>
							<div style="width: 200px; margin: 0 auto">
								<a href="#" id="button_save_widget" class="bb_button bb_small bb_green">
									<span class="icon-disk" style="margin-right: 5px"></span>Save Settings
								</a>
							</div>
						</li>			
					</ul>
					
					<?php
						if(!empty($select_radio_fields_lookup)){
							foreach ($select_radio_fields_lookup as $element_id => $options) {
								echo "<select id=\"element_{$element_id}_lookup\" style=\"display: none\">\n";
								foreach ($options as $option_title) {
									echo "<option value=\"{$option_title}\">{$option_title}</option>\n";
								}
								echo "</select>\n";
							}
						}

						if(!empty($rating_field_properties)){
							foreach($rating_field_properties as $key=>$value){
								$element_id = $key;
								$rating_max = $value['rating_max'];

								echo "<select id=\"element_{$element_id}_lookup\" style=\"display: none\">\n";
								for ($j=1;$j<=$rating_max;$j++) {
									echo "<option value=\"{$j}\">{$j}</option>\n";
								}
								echo "</select>\n";
							}
						}
					?>
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

<div id="dialog-warning" title="Error Title" class="buttons" style="display: none">
	<img src="images/icons/warning.png" title="Warning" /> 
	<p id="dialog-warning-msg">
		Error
	</p>
</div>
 
<?php
	$footer_data =<<<EOT
<script type="text/javascript">
	$(function(){
		{$jquery_data_code}		
    });
</script>
<script src="js/popper.min.js{$mf_version_tag}"></script>
<script src="js/tippy.index.all.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.core.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.widget.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.tabs.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.mouse.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.sortable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.draggable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.position.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.dialog.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery.mini_colors.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/datepick/jquery.datepick.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/edit_widget.js{$mf_version_tag}"></script>
<style>
.tippy-tooltip{
	font-size: 98%;
}
</style>
EOT;

	require('includes/footer.php'); 
?>