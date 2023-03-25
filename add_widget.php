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
	require('includes/post-functions.php');
	require('includes/filter-functions.php');
	require('includes/users-functions.php');
	require('includes/report-functions.php');
	
	$form_id = (int) trim($_REQUEST['id']);
	
	if(empty($form_id)){
		die("Error. Missing form ID.");
	}

	$dbh = mf_connect_db();
	
	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);

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
		$row['form_name'] = mf_trim_max_length($row['form_name'],50);
		$form_name = htmlspecialchars($row['form_name']);		
	}
	
	if(mf_is_form_submitted()){ //if form submitted

		//validate CSRF token
		mf_verify_csrf_token($_POST['csrf_token']);
		
		//get all required inputs
		$user_input['chart_type'] 		= $_POST['aw_select_widget'];
		$user_input['chart_title']		= $_POST['aw_widget_title'];
		$user_input['horizontal_axis']	= $_POST['aw_horizontal_axis'];

		$chart_axis_is_date = 0;

		//determine the datasource
		if($user_input['chart_type'] == 'line' || $user_input['chart_type'] == 'area'){
			if($user_input['horizontal_axis'] == 'date'){
				$chart_axis_is_date = 1;
				$user_input['chart_datasource'] = $_POST['aw_select_datasource_expanded'];
			}else if($user_input['horizontal_axis'] == 'category'){
				$chart_axis_is_date = 0;
				$user_input['chart_datasource'] = $_POST['aw_select_datasource'];
			}
		}else if($user_input['chart_type'] == 'grid'){
			$user_input['chart_datasource'] = ''; //grid doesn't need specific datasource
		}else if($user_input['chart_type'] == 'rating'){
			$user_input['chart_datasource'] = $_POST['aw_select_datasource_rating_fields'];
		}else{
			$user_input['chart_datasource'] = $_POST['aw_select_datasource'];
		}

		//clean the inputs
		$user_input = mf_sanitize($user_input);

		//get chart_id for this new widget
		$query = "select ifnull(max(`chart_id`),0) + 1 as new_chart_id from ".MF_TABLE_PREFIX."report_elements where form_id = ?";
		$params = array($form_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		$chart_id = $row['new_chart_id'];

		//set chart labels and tooltip template
		$chart_labels_visible 	= 1;
		$chart_labels_template 	= '';
		$chart_tooltip_template = '';
		$chart_theme = 'blueopal'; //default theme

		if($user_input['chart_type'] == 'pie' || $user_input['chart_type'] == 'donut'){
			$chart_labels_visible = 1;
			$chart_labels_template 	= "#= kendo.format('{0:P}', percentage)#";
			$chart_tooltip_template = "#= category # - #= dataItem.entry # entries";
		}else if($user_input['chart_type'] == 'bar'){
			$chart_labels_visible = 1;
			$chart_labels_template 	= "#= dataItem.percentage #";
			$chart_tooltip_template = "#= category # - #= value # entries";
		}else if($user_input['chart_type'] == 'line' || $user_input['chart_type'] == 'area'){
			$chart_labels_visible = 0;
			$chart_labels_template = "";

			if($user_input['horizontal_axis'] == 'date'){
				$chart_tooltip_template = "#= value # entries";
			}else if($user_input['horizontal_axis'] == 'category'){
				$chart_tooltip_template = "#= category # - #= value # entries";
			}
		}else if($user_input['chart_type'] == 'grid'){
			$chart_theme = 'silver';
		}else if($user_input['chart_type'] == 'rating'){
			$chart_theme = 'rating_light';
		}

		//create the widget
		//insert into ap_report_elements table
		$widget_params = array();
		$widget_params[":access_key"] 				= $form_id.'x'.substr(strtolower(md5(uniqid(rand(), true))),0,10);
		$widget_params[":form_id"]					= $form_id;
		$widget_params[":chart_id"]					= $chart_id;
		$widget_params[":chart_datasource"]			= $user_input['chart_datasource'];
		$widget_params[":chart_type"]				= $user_input['chart_type'];
		$widget_params[":chart_enable_filter"]		= 0;
		$widget_params[":chart_filter_type"]		= 'all';
		$widget_params[":chart_title"]				= $user_input['chart_title'];
		$widget_params[":chart_title_position"]		= 'top';
		$widget_params[":chart_title_align"]		= 'center';
		$widget_params[":chart_width"]				= 0; //this will allow the chart width to automatically resize
		$widget_params[":chart_height"]				= 400;
		$widget_params[":chart_background"]			= ''; //transparent background  
		$widget_params[":chart_theme"]				= $chart_theme;
		$widget_params[":chart_legend_visible"]		= 1;
		$widget_params[":chart_legend_position"] 	= 'right';
		$widget_params[":chart_labels_visible"]	 	= $chart_labels_visible;
		$widget_params[":chart_labels_position"] 	= 'outsideEnd';
		$widget_params[":chart_labels_template"] 	= $chart_labels_template;
		$widget_params[":chart_labels_align"] 		= 'circle';
		$widget_params[":chart_tooltip_visible"] 	= 1;
		$widget_params[":chart_tooltip_template"] 	= $chart_tooltip_template;
		$widget_params[":chart_gridlines_visible"] 	= 1;
		$widget_params[":chart_bar_color"] 			= '';
		$widget_params[":chart_is_stacked"] 		= 0;
		$widget_params[":chart_is_vertical"] 		= 0; //for bar chart, the default axis is horizontal
		$widget_params[":chart_line_style"] 		= 'smooth';
		$widget_params[":chart_axis_is_date"] 		= $chart_axis_is_date;
		$widget_params[":chart_date_range"] 		= 'all';
		$widget_params[":chart_date_period_value"] 	= 1;
		$widget_params[":chart_date_period_unit"] 	= 'day';
		$widget_params[":chart_date_axis_baseunit"] = '';
		$widget_params[":chart_date_range_start"] 	= '';
		$widget_params[":chart_date_range_end"] 	= '';

		$query = "INSERT INTO 
							`".MF_TABLE_PREFIX."report_elements` (
										`access_key`, 
										`form_id`, 
										`chart_id`, 
										`chart_datasource`, 
										`chart_type`, 
										`chart_enable_filter`, 
										`chart_filter_type`, 
										`chart_title`, 
										`chart_title_position`, 
										`chart_title_align`, 
										`chart_width`, 
										`chart_height`, 
										`chart_background`, 
										`chart_theme`, 
										`chart_legend_visible`, 
										`chart_legend_position`, 
										`chart_labels_visible`, 
										`chart_labels_position`, 
										`chart_labels_template`, 
										`chart_labels_align`, 
										`chart_tooltip_visible`, 
										`chart_tooltip_template`, 
										`chart_gridlines_visible`, 
										`chart_bar_color`, 
										`chart_is_stacked`, 
										`chart_is_vertical`, 
										`chart_line_style`, 
										`chart_axis_is_date`, 
										`chart_date_range`, 
										`chart_date_period_value`, 
										`chart_date_period_unit`, 
										`chart_date_axis_baseunit`, 
										`chart_date_range_start`, 
										`chart_date_range_end`) 
								VALUES (
										:access_key, 
										:form_id, 
										:chart_id, 
										:chart_datasource, 
										:chart_type, 
										:chart_enable_filter, 
										:chart_filter_type, 
										:chart_title, 
										:chart_title_position, 
										:chart_title_align, 
										:chart_width, 
										:chart_height, 
										:chart_background, 
										:chart_theme, 
										:chart_legend_visible, 
										:chart_legend_position, 
										:chart_labels_visible, 
										:chart_labels_position, 
										:chart_labels_template, 
										:chart_labels_align, 
										:chart_tooltip_visible, 
										:chart_tooltip_template, 
										:chart_gridlines_visible, 
										:chart_bar_color, 
										:chart_is_stacked, 
										:chart_is_vertical, 
										:chart_line_style, 
										:chart_axis_is_date, 
										:chart_date_range, 
										:chart_date_period_value, 
										:chart_date_period_unit, 
										:chart_date_axis_baseunit, 
										:chart_date_range_start, 
										:chart_date_range_end
									);";
		mf_do_query($query,$widget_params,$dbh);
			
		//redirect to manage_report page and display success message
		$_SESSION['MF_SUCCESS'] = 'A new widget has been added.';

		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/manage_report.php?id={$form_id}");
		exit;
		
	}
	
	$disable_jquery_loading = true;
	
	$header_data =<<<EOT
<link href="js/kendoui/styles/kendo.common.min.css{$mf_version_tag}" rel="stylesheet">
<link href="js/kendoui/styles/kendo.blueopal.min.css{$mf_version_tag}" rel="stylesheet">
    
<link href="js/kendoui/styles/kendo.dataviz.min.css{$mf_version_tag}" rel="stylesheet">
<link href="js/kendoui/styles/kendo.dataviz.blueopal.min.css{$mf_version_tag}" rel="stylesheet">

<link href="css/rating_widget.css{$mf_version_tag}" rel="stylesheet">

<script src="js/kendoui/js/jquery.min.js{$mf_version_tag}"></script>
<script src="js/kendoui/js/kendo.custom.min.js{$mf_version_tag}"></script>
EOT;

	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post add_widget">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <?php echo "<a class=\"breadcrumb\" href='manage_report.php?id={$form_id}'>Report</a>"; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Add Widget</h2>
							<p>Add a new widget to report</p>
						</div>	
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>
				
				<?php mf_show_message(); ?>

				<div class="content_body">
					<form id="add_widget_form" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
					<ul id="aw_main_list">
						<li>
							<div id="aw_box_select_widget" class="aw_box_main gradient_blue">
								<div class="aw_box_meta">
									<h1>1.</h1>
									<h6>Select Widget</h6>
								</div>
								<div class="aw_box_content" style="padding-bottom: 15px">
									<label class="description" for="aw_select_widget" style="margin-top: 10px">
										Select Widget Type 
									</label>
									<select class="select small" id="aw_select_widget" name="aw_select_widget" autocomplete="off">
										<option value="pie">Pie Chart</option>
										<option value="donut">Donut Chart</option>
										<option value="bar">Bar Chart</option>
										<option value="line">Line Chart</option>
										<option value="area">Area Chart</option>
										<option value="grid">Entries Grid</option>
										<option value="rating">Rating Scorecard</option>
									</select>
									<div class="grid-wrapper" style="margin-top: 20px;display: none">
										<img src="images/grid-preview.png" width="488" height="226" style="border-radius: 2px;box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);"/>
									</div>
									<div class="rating-wrapper <?php if($active_admin_theme == 'dark'){ echo 'rating-dark'; } ?>" style="display: none;">
										<div class="rating-widget rating-medium">
											<span class="rating-heading">Rating Scorecard</span>
											<hr style="border:1px solid #f1f1f1">

											<div class="rating-score">
												<p  class="rating-total">400.000 ratings</p>
												<h1 class="rating-score-value">4.8</h1>
												<p  class="rating-score-sublabel">out of 5</p>
												<span class="icon-star-full2 mf rating-checked"></span>
												<span class="icon-star-full2 mf rating-checked"></span>
												<span class="icon-star-full2 mf rating-checked"></span>
												<span class="icon-star-full2 mf rating-checked"></span>
												<span class="icon-star-half mf rating-checked"></span>
											</div>

											<div class="rating-bar rating-max-5 row">
											  <div class="rating-side first-row">
											    <div><span class="icon-star-full2 mf rating-checked"></span><span class="icon-star-full2 mf rating-checked"></span><span class="icon-star-full2 mf rating-checked"></span><span class="icon-star-full2 mf rating-checked"></span><span class="icon-star-full2 mf rating-checked"></span></div>
											  </div>
											  <div class="rating-middle first-row">
											    <div class="bar-container">
											      <div class="bar bar-5"></div>
											    </div>
											  </div>
											  <div class="rating-side rating-right first-row">
											    <div>65%</div>
											  </div>
											  <div class="rating-side">
											    <div><span class="icon-star-full2 mf rating-checked"></span><span class="icon-star-full2 mf rating-checked"></span><span class="icon-star-full2 mf rating-checked"></span><span class="icon-star-full2 mf rating-checked"></span></div>
											  </div>
											  <div class="rating-middle">
											    <div class="bar-container">
											      <div class="bar bar-4"></div>
											    </div>
											  </div>
											  <div class="rating-side rating-right">
											    <div>20%</div>
											  </div>
											  <div class="rating-side">
											    <div><span class="icon-star-full2 mf rating-checked"></span><span class="icon-star-full2 mf rating-checked"></span><span class="icon-star-full2 mf rating-checked"></span></div>
											  </div>
											  <div class="rating-middle">
											    <div class="bar-container">
											      <div class="bar bar-3"></div>
											    </div>
											  </div>
											  <div class="rating-side rating-right">
											    <div>10%</div>
											  </div>
											  <div class="rating-side">
											    <div><span class="icon-star-full2 mf rating-checked"></span><span class="icon-star-full2 mf rating-checked"></span></div>
											  </div>
											  <div class="rating-middle">
											    <div class="bar-container">
											      <div class="bar bar-2"></div>
											    </div>
											  </div>
											  <div class="rating-side rating-right">
											    <div>6%</div>
											  </div>
											  <div class="rating-side">
											    <div><span class="icon-star-full2 mf rating-checked"></span></div>
											  </div>
											  <div class="rating-middle">
											    <div class="bar-container">
											      <div class="bar bar-1"></div>
											    </div>
											  </div>
											  <div class="rating-side rating-right">
											    <div>20%</div>
											  </div>
											</div>
										</div>
									</div>
									<div class="chart-wrapper" style="margin-top: 20px">
								        <div id="chart_preview" style="width: 520px"></div>
								    </div>
								</div>
							</div>
						</li>
						<li class="ps_arrow"><span class="icon-arrow-down11 spacer-icon"></span></li>
						<li>
							<div id="aw_box_widget_settings" class="aw_box_main gradient_green">
								<div class="aw_box_meta">
									<h1 id="widget_setting_header">2.</h1>
									<h6>Widget Setting</h6>
								</div>
								<div class="aw_box_content" style="min-height: 90px">
									<span style="display: block">
										<label class="description" for="aw_widget_title" style="margin-top: 10px">Widget Title</label>
										<input id="aw_widget_title" name="aw_widget_title" class="element text" style="width: 90%" value="" type="text">
									</span>
									<span id="aw_horizontal_axis_span" style="display: none; margin-bottom: 10px">
										<label class="description" for="aw_horizontal_axis" style="margin-top: 10px">
										Horizontal Axis 
										</label>
										<select class="select medium" id="aw_horizontal_axis" name="aw_horizontal_axis" autocomplete="off">
											<option value="date">Date</option>
											<option value="category">Category Name</option>
										</select>
									</span>									
								</div>
							</div>
						</li>
						<li class="ps_arrow select_datasource_group"><span class="icon-arrow-down11 spacer-icon"></span></li>
						<li class="select_datasource_group">
							<div id="aw_box_select_field" class="aw_box_main gradient_red">
								<div class="aw_box_meta">
									<h1>3.</h1>
									<h6>Select Field</h6>
								</div>
								<div class="aw_box_content" style="min-height: 90px;">
									<?php
										$params = array();
										$params['show_expanded_options'] = false;
										$options_markup_simple = mf_get_chart_datasource_markup($dbh,$form_id,$params);

										$params = array();
										$params['show_expanded_options'] = true;
										$options_markup_expanded = mf_get_chart_datasource_markup($dbh,$form_id,$params);

										$params = array();
										$params['show_rating_only'] = true;
										$options_markup_rating_fields = mf_get_chart_datasource_markup($dbh,$form_id,$params);
									?>
									<span id="select_datasource_span_simple">
										<?php
											if(empty($options_markup_simple)){
										?>
											<h6>Your form doesn't have any supported fields for this widget type.</h6>
											<h6 style="margin-top: 15px;">Please add one of the following fields into your form:</h6>
											<h6>Multiple Choice, Drop Down, Checkboxes, Matrix Choice.</h6>
										<?php
											}else{
										?>
										
										<label class="description" for="aw_select_datasource" style="margin-top: 10px">Field Name</label>
										<select class="element select" id="aw_select_datasource_lookup" name="aw_select_datasource_lookup" style="display: none"> 
											<?php echo $options_markup_simple; ?>
										</select>
										<select class="element select" id="aw_select_datasource" name="aw_select_datasource" style="width: 80%"> 
											<?php echo $options_markup_simple; ?>
										</select>

										<?php } ?>
									</span>

									<span id="select_datasource_span_rating_fields" style="display: none;">
										<?php
											if(empty($options_markup_rating_fields)){
										?>
											<h6>Your form doesn't have any supported fields for this widget type.</h6>
											<h6 style="margin-top: 15px;">Please add the following field into your form:</h6>
											<h6>Rating</h6>
										<?php
											}else{
										?>
										
										<label class="description" for="aw_select_datasource_rating_fields" style="margin-top: 10px">Field Name</label>
										<select class="element select" id="aw_select_datasource_rating_fields" name="aw_select_datasource_rating_fields" style="width: 80%"> 
											<?php echo $options_markup_rating_fields; ?>
										</select>

										<?php } ?>
									</span>

									<span id="select_datasource_span_expanded" style="display: none">
										<?php
											if(empty($options_markup_expanded)){
										?>
											<h6>Your form doesn't have any supported fields for this widget type.</h6>
											<h6 style="margin-top: 15px;">Please add one of the following fields into your form:</h6>
											<h6>Multiple Choice, Drop Down, Checkboxes, Matrix Choice.</h6>
										<?php
											}else{
										?>

										<label class="description" for="aw_select_datasource_expanded" style="margin-top: 10px">Field Name</label>
										<select class="element select" id="aw_select_datasource_expanded" name="aw_select_datasource_expanded" style="width: 80%"> 
											<?php echo $options_markup_expanded; ?>
										</select>

										<?php } ?>
									</span>
									<span id="select_datasource_span_allfield" style="display: none">
										<label class="description" style="margin-top: 10px;margin-bottom: 30px">All fields will be displayed. <br/><br/>You will be able to select individual fields after you create your widget.</label>
									</span>
									<p id="aw_select_field_info"><span style="vertical-align: middle" class="icon-info helpicon"></span> Select the form field you want the widget to be based on.</p>
								</div>
							</div>
						</li>						
						<li class="ps_arrow add_widget_group"><span class="icon-arrow-down11 spacer-icon"></span></li>
						
						<li class="add_widget_group">
							<div>
								<a href="#" id="button_add_widget" class="bb_button bb_small bb_green">
									<span class="icon-disk" style="margin-right: 5px"></span>Add Widget
								</a>
							</div>
						</li>	
					</ul>
					<input type="hidden" name="submit_form" value="1" />
					<input type="hidden" name="id" value="<?php echo $form_id; ?>" />
					<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>">
					</form>
					
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

 
<?php
	$footer_data =<<<EOT
<script type="text/javascript" src="js/add_widget.js{$mf_version_tag}"></script>
EOT;

	require('includes/footer.php'); 
?>