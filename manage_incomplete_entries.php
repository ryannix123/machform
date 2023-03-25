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

	require('includes/entry-functions.php');
	require('includes/users-functions.php');
	
	$form_id = (int) trim($_GET['id']);
	$sort_by = trim($_GET['sortby'] ?? '');

	//get page number for pagination
	if (isset($_REQUEST['pageno'])) {
	   $pageno = $_REQUEST['pageno'];
	}else{
	   $pageno = 1;
	}

	
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

		//this page need edit_entries or view_entries permission
		if(empty($user_perms['edit_entries']) && empty($user_perms['view_entries'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to access this page.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}
	
	$query = "select 
					A.form_name,
					ifnull(B.entries_incomplete_sort_by,'id-desc') entries_sort_by,
					ifnull(B.entries_incomplete_filter_type,'all') entries_filter_type,
					ifnull(B.entries_incomplete_enable_filter,0) entries_enable_filter			  
				from 
					".MF_TABLE_PREFIX."forms A left join ".MF_TABLE_PREFIX."entries_preferences B 
				  on 
				  	A.form_id=B.form_id and B.user_id=? 
			   where 
			   		A.form_id = ?";
	$params = array($_SESSION['mf_user_id'],$form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row)){
		
		if(!empty($row['form_name'])){		
			$form_name = htmlspecialchars($row['form_name']);
		}else{
			$form_name = 'Untitled Form (#'.$form_id.')';
		}	

		$entries_filter_type   = $row['entries_filter_type'];
		$entries_enable_filter = $row['entries_enable_filter'];
	}else{
		die("Error. Unknown form ID.");
	}

	if(empty($sort_by)){
		//get the default sort element from the table
		$sort_by = $row['entries_sort_by'];
	}else{
		//if sort by parameter exist, save it into the database
		$query = "select count(user_id) sort_count from ".MF_TABLE_PREFIX."entries_preferences where form_id=? and `user_id`=?";
		
		$params = array($form_id,$_SESSION['mf_user_id']);
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		$sort_count = $row['sort_count'];

		if(!empty($sort_count)){ //update existing record
			$query = "update ".MF_TABLE_PREFIX."entries_preferences set entries_incomplete_sort_by = ? where form_id = ? and `user_id` = ?";
			$params = array($sort_by,$form_id,$_SESSION['mf_user_id']);
			mf_do_query($query,$params,$dbh);
		}else{ //insert new one
			$query = "insert into ".MF_TABLE_PREFIX."entries_preferences(`entries_incomplete_sort_by`,`form_id`,`user_id`) values(?,?,?)";
			$params = array($sort_by,$form_id,$_SESSION['mf_user_id']);
			mf_do_query($query,$params,$dbh);
		}
		
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
			   		element_status = 1 and 
			   		element_is_private = 0";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$select_radio_fields_lookup = array();
	while($row = mf_do_fetch_result($sth)){
		$element_id = $row['element_id'];
		
		$select_radio_fields_lookup[$element_id] = $options_lookup[$element_id];
	}

	$jquery_data_code = '';

	//get all available columns label
	$columns_meta  = mf_get_columns_meta($dbh,$form_id);
	$columns_label = $columns_meta['name_lookup'];
	$columns_type  = $columns_meta['type_lookup'];

	$form_properties = mf_get_form_properties($dbh,$form_id,array('payment_enable_merchant'));
	
	//if payment enabled, add ap_form_payments columns into $columns_label
	if($form_properties['payment_enable_merchant'] == 1){
		$columns_label['payment_amount'] = 'Payment Amount';
		$columns_label['payment_status'] = 'Payment Status';
		$columns_label['payment_id']	 = 'Payment ID';

		$columns_type['payment_amount'] = 'money';
		$columns_type['payment_status'] = 'text';
		$columns_type['payment_id'] 	= 'text';
	}

	//get current column preference
	$query = "select element_name from ".MF_TABLE_PREFIX."column_preferences where form_id=? and user_id=? and incomplete_entries=1";
	$params = array($form_id,$_SESSION['mf_user_id']);

	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		$current_column_preference[] = $row['element_name'];
	}


	//check if the table has entries or not
	$query = "select count(*) total_row from `".MF_TABLE_PREFIX."form_{$form_id}` where `status`=2";
	$params = array();
			
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
		
	if(!empty($row['total_row'])){
		$form_has_entries = true;
	}else{
		$form_has_entries = false;
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

		$jquery_data_code .= "\$('#filter_pane').data('$element_name','$element_type');\n";
	}


	//get filter keywords from ap_form_filters table
	$query = "select
					element_name,
					filter_condition,
					filter_keyword
				from 
					".MF_TABLE_PREFIX."form_filters
			   where
			   		form_id = ? and user_id = ? and incomplete_entries = 1 
			order by 
			   		aff_id asc";
	$params = array($form_id,$_SESSION['mf_user_id']);
	$sth = mf_do_query($query,$params,$dbh);
	$i = 0;
	while($row = mf_do_fetch_result($sth)){
		$filter_data[$i]['element_name'] 	 = $row['element_name'];
		$filter_data[$i]['filter_condition'] = $row['filter_condition'];
		$filter_data[$i]['filter_keyword'] 	 = $row['filter_keyword'];
		$i++;
	}

			$header_data =<<<EOT
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="css/pagination_classic.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="css/dropui.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="js/datepick/smoothness.datepick.css{$mf_version_tag}" rel="stylesheet" />
EOT;
	
	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post manage_entries">
				<div class="content_header">
					<div class="content_header_title">
						<div id="me_form_title">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <a class="breadcrumb" href="manage_entries.php?id=<?php echo $form_id; ?>">Entries</a> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Incomplete</h2>
							<p>Edit and manage your form entries</p>
						</div>
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>

				<div class="content_body">
					
					<?php if($form_has_entries){ ?>
					
						<div id="entries_actions" class="gradient_red">
							<ul>
								
								<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_perms['edit_entries'])){ ?>
								<li>
									<a id="entry_delete" href="#"><span class="icon-remove"></span>Delete</a>
								</li>
								<?php } ?>

								<li>
									<div style="border-left: 1px dotted #CB6852;height: 35px;margin-top:5px"></div>
								</li>
								<li>
									<a id="entry_export" href="#"><span class="icon-file-download"></span>Export</a>
								</li>
							</ul>
							<img src="images/icons/29.png" style="position: absolute;left:5px;top:100%" />
						</div>
						<div id="entries_options" class="gradient_blue" data-csrftoken="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>" data-formid="<?php echo $form_id; ?>">
							<ul>
								<li>
									<a id="entry_select_field" href="#"><span class="icon-settings"></span>Select Fields</a>
								</li>
								<li>
									<div style="border-left: 1px dotted #3B699F;height: 35px;margin-top:5px"></div>
								</li>
								<li>
									<a id="entry_filter" href="#"><span class="icon-binoculars"></span>Filter Entries</a>
								</li>
							</ul>
						</div>

						<?php if(!empty($entries_enable_filter)){ ?>
							<div id="filter_info">
								Displaying filtered entries.  <a style="margin-left: 60px" id="me_edit_filter" href="#">Edit</a> or <a href="#" id="me_clear_filter">Clear Filter</a>
							</div>
						<?php } ?>
						
						<div style="clear: both"></div>
						<div id="field_selection" style="display: none" class="gradient_blue">
							<h6>Select fields to be displayed:</h6>
							<ul>
								<?php 
									foreach($columns_label as $element_name=>$element_label){
										//don't display signature or id field
										if($element_name == 'id' || ($columns_type[$element_name] == 'signature')){
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
							<div id="field_selection_apply">
									<input type="button" id="me_field_select_submit" value="Apply" class="bb_button bb_mini bb_blue"> <span style="margin-left: 5px" id="cancel_field_select_span">or <a href="#" id="field_selection_cancel">Cancel</a></span>
							</div>
							<img style="position: absolute;right:38px;top:-12px" src="images/icons/29_blue.png" />
						</div>

						<div id="filter_pane" style="display: none" class="gradient_blue">
							
							<h6>Display entries that match 
									<select style="margin-left: 5px;margin-right: 5px" name="filter_all_any" id="filter_all_any" class="element select"> 
										<option value="all" <?php if($entries_filter_type == 'all'){ echo 'selected="selected"'; } ?>>all</option>
										<option value="any" <?php if($entries_filter_type == 'any'){ echo 'selected="selected"'; } ?>>any</option>
									</select> 
								of the following conditions:
							</h6>
							
							<ul>

								<?php
									if(empty($filter_data)){
										
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
										}else{
											$condition_text_display = '';
											$condition_number_display = 'display:none';
											$condition_date_display = 'display:none';
											$condition_file_display = 'display:none';
											$condition_checkbox_display = 'display:none';
											$condition_keyword_display = '';
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
										}else{
											$filter_properties->condition    = 'is';
										}
										
										$filter_properties->keyword 	 = '';

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
									<input type="text" class="element text filter_keyword" value="" id="filterkeyword_1" style="<?php echo $condition_keyword_display; ?>">
									<input type="hidden" value="" name="datepicker_1" id="datepicker_1">
									<span style="display:none"><img id="datepickimg_1" alt="Pick date." src="images/icons/calendar.png" class="trigger filter_date_trigger" style="vertical-align: top; cursor: pointer" /></span>
									<a href="#" id="deletefilter_1" class="filter_delete_a"><img src="images/icons/51_blue_32.png" width="16" height="16" /></a>

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
									<a href="#" id="deletefilter_<?php echo $i; ?>" class="filter_delete_a"><img src="images/icons/51_blue_32.png" width="16" height="16" /></a>
								</li>
											
								
									
								<?php 	
										$i++;
										}//end foreach filter_data
									} //end else
								?>

								<li id="li_filter_add" class="filter_add">
									<a href="#" id="filter_add_a"><img src="images/icons/49_blue_32.png" width="16" height="16" /></a>
								</li>
							</ul>
							<div id="filter_pane_apply">
									<input type="button" id="me_filter_pane_submit" value="Apply Filter" class="bb_button bb_mini bb_blue"> <span id="cancel_filter_pane_span" style="margin-left: 5px">or <a href="#" id="filter_pane_cancel">Cancel</a></span>
							</div>
							<img style="position: absolute;right:130px;top:-12px" src="images/icons/29_blue.png" />
						</div>

						<?php 
							$entries_options['page_number']   = $pageno; //set the page number to be displayed
							$entries_options['rows_per_page'] = 15; //set the maximum rows to be displayed each page

							//set the sorting options
							$exploded = explode('-', $sort_by);
							$entries_options['sort_element'] = $exploded[0]; //the element name, e.g. element_2
							$entries_options['sort_order']	 = $exploded[1]; //asc or desc

							//set filter options
							$entries_options['filter_data'] = $filter_data ?? '';
							$entries_options['filter_type'] = $entries_filter_type;

							//only display incomplete entries
							$entries_options['display_incomplete_entries'] = true;
							
							//set the column preferences user_id
							$entries_options['column_preferences_user_id'] = $_SESSION['mf_user_id'];

							echo mf_display_entries_table($dbh,$form_id,$entries_options); 
						?>
						
						<div id="me_sort_option">
							<label class="description" for="me_sort_by">Sort By &#8674; </label>
							<select class="element select" id="me_sort_by" name="me_sort_by"> 
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

											if($sort_by == $element_name.'-asc'){
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
												$element_label .= ' (Default)';
											}

											if(strlen($element_label) > 40){
												$element_label = substr($element_label, 0, 40).'...';
											}

											if($sort_by == $element_name.'-desc'){
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
					
					<?php } else { ?>
						
						<div id="entries_manager_empty">
								<h2>No Entries.</h2>
								<h3>This form doesn't have any entries yet.</h3>
						</div>	

					<?php } ?>
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
<div id="dialog-export-entries" title="Select File Type" class="buttons" style="display: none">
	<div id="dialog-export-entries-option" style="overflow: auto;margin-bottom: 15px;margin-left: 25px">
		<label class="description" style="color: #000;font-family: globerbold;font-weight:400;float: left;line-height: 160%">Export Option:</label>
		<div style="float: left;margin-left: 15px">
			<span>
				<input id="export_all"  name="export_option" class="element radio" type="radio" value="1" checked="checked" />
				<label style="font-size: 13px" for="export_all">Export All Fields</label>
			</span>
			<span style="margin-left: 20px">
				<input id="export_selected"  name="export_option" class="element radio" type="radio" value="1" />
				<label style="font-size: 13px" for="export_selected">Export Selected Fields</label>
			</span>
		</div>
	</div>
	<ul>
		<li class="gradient_blue"><a id="export_as_excel" href="#" class="export_link">Excel File (.xls)</a></li>
		<li class="gradient_blue"><a id="export_as_csv" href="#" class="export_link">Comma Separated (.csv)</a></li>
		<li class="gradient_blue"><a id="export_as_txt" href="#" class="export_link">Tab Separated (.txt)</a></li>
	</ul>
</div>
<div id="dialog-confirm-entry-delete" title="Are you sure you want to delete selected entries?" class="buttons" style="display: none">
	<span class="icon-bubble-notification"></span>
	<p id="dialog-confirm-entry-delete-msg">
		This action cannot be undone.<br/>
		<strong id="dialog-confirm-entry-delete-info">Data and files associated with your selected entries will be deleted.</strong><br/><br/>
	</p>				
</div>
 
<?php
	$footer_data =<<<EOT
<script type="text/javascript">
	$(function(){
		{$jquery_data_code}		
    });
</script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.core.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.widget.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.tabs.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.mouse.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.sortable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.draggable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.position.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.dialog.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/datepick/jquery.datepick.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/manage_incomplete_entries.js{$mf_version_tag}"></script>
EOT;

	require('includes/footer.php'); 
?>