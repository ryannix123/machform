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
	require('includes/entry-functions.php');
	require('includes/users-functions.php');
	
	$form_id = (int) trim($_REQUEST['id']);
	
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
		if(empty($user_perms['edit_form'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to edit this form.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}

	//get form name
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

	//get users list
	//when a user is being added as an approver, the user will automatically assigned "edit entry" permission
	$query = "SELECT 
					user_id,
					trim(user_fullname) user_fullname,
					user_email,
					last_login_date
				FROM 
					".MF_TABLE_PREFIX."users 
			   WHERE 
			   		`status` = 1
			ORDER BY 
					user_fullname ASC";
	$params = array();
	
	$sth = mf_do_query($query,$params,$dbh);

	$user_list_array = array();
	$i=0;
	while($row = mf_do_fetch_result($sth)){
		$user_list_array[$i]['user_id'] 	  	= $row['user_id'];
		$user_list_array[$i]['user_fullname'] 	= htmlspecialchars($row['user_fullname'],ENT_QUOTES);
		$user_list_array[$i]['user_email'] 	  	= $row['user_email'];
		
		$user_list_array[$i]['last_login_date'] = mf_short_relative_date($row['last_login_date']);
		if(empty($user_list_array[$i]['last_login_date'])){
			$user_list_array[$i]['last_login_date'] = 'never';
		}

		$i++;
	}
	
	//load -- A. Approval Settings -- property
	$approval_settings = new stdClass();
	$jquery_data_code  = '';

	$query 	= "select 
					 workflow_type,
					 parallel_workflow   
			     from 
			     	 ".MF_TABLE_PREFIX."approval_settings 
			    where 
			    	 form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	if(!empty($row)){
		$approval_settings->form_id = $form_id;
		$approval_settings->workflow_type		= $row['workflow_type'];
		$approval_settings->parallel_workflow	= $row['parallel_workflow'];
	}else{
		$approval_settings->form_id = $form_id;
		$approval_settings->workflow_type		= 'parallel';
		$approval_settings->parallel_workflow	= 'any';
	}

	$json_approval_settings = json_encode($approval_settings);
	$jquery_data_code 	   .= "\$('#as_main_list').data('approval_properties',{$json_approval_settings});\n";

	//load -- B. Approvers --
	$query = "SELECT 
					  A.rule_all_any,
					  A.user_id,
					  A.user_position,
					  B.user_fullname,
					  B.user_email,
					  B.last_login_date 
				FROM 
				  	".MF_TABLE_PREFIX."approvers A
		   LEFT JOIN
		   			".MF_TABLE_PREFIX."users B
		   		  ON
		   		  	A.user_id = B.user_id
			   WHERE
				    form_id = ?
		    ORDER BY
				    user_position ASC";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);

	$approvers_list_array = array();
	$all_approvers_user_id = array();
	$i=0;
	while($row = mf_do_fetch_result($sth)){
		$approvers_list_array[$i]['rule_all_any']  = $row['rule_all_any'];
		$approvers_list_array[$i]['user_id'] 	   = $row['user_id'];
		$approvers_list_array[$i]['user_position'] = $i + 1;
		$approvers_list_array[$i]['user_fullname'] = $row['user_fullname'];
		$approvers_list_array[$i]['user_email'] 	 = $row['user_email'];
		$approvers_list_array[$i]['last_login_date'] = $row['last_login_date'];

		$all_approvers_user_id[] = $row['user_id'];
		$i++;
	}

	//get data from ap_approvers_conditions
	$query = "SELECT 
					target_user_id,
					element_name,
					rule_condition,
					rule_keyword 
				FROM 
					".MF_TABLE_PREFIX."approvers_conditions 
			   WHERE 
			   		form_id = ? 
			ORDER BY 
					aac_id asc";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);
		
	$prev_user_id = 0;
	$approvers_logic_conditions_array = array();

	$i=0;
	while($row = mf_do_fetch_result($sth)){
		$target_user_id = $row['target_user_id'];
		
		if($target_user_id != $prev_user_id){
			$i=0;
		}

		$approvers_logic_conditions_array[$target_user_id][$i]['element_name']   = $row['element_name'];
		$approvers_logic_conditions_array[$target_user_id][$i]['rule_condition'] = $row['rule_condition'];
		$approvers_logic_conditions_array[$target_user_id][$i]['rule_keyword']   = $row['rule_keyword'];

		$prev_user_id = $target_user_id;
		$i++;
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

	//get the list of all fields within the form (without any child elements)
	$query = "select 
					element_id,
					if(element_type = 'matrix',element_guidelines,element_title) element_title,
					element_type,
					element_page_number,
					element_position
 				from 
 					".MF_TABLE_PREFIX."form_elements 
			   where 
					form_id = ? and 
					element_status = 1 and 
					element_is_private = 0 and 
					element_type <> 'page_break' and 
					element_matrix_parent_id = 0 
		    order by 
		    		element_position asc";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);
	
	$all_fields_array = array();
	while($row = mf_do_fetch_result($sth)){
		$element_page_number = (int) $row['element_page_number'];
		$element_id 		 = (int) $row['element_id'];
		$element_position 	 = (int) $row['element_position'] + 1;

		$element_title = htmlspecialchars(strip_tags($row['element_title']));
		
		if(empty($element_title)){
			$element_title = '-untitled field-';
		}

		if(strlen($element_title) > 70){
			$element_title = substr($element_title, 0, 70).'...';
		}											
		

		$all_fields_array[$element_page_number][$element_id]['element_title'] = $element_position.'. '.$element_title;
		$all_fields_array[$element_page_number][$element_id]['element_type']  = $row['element_type'];
	}


	//get a list of all matrix checkboxes ids
	$query = "select 
					element_id,
					element_constraint 
				from 
					".MF_TABLE_PREFIX."form_elements 
			   where 
			   		element_type = 'matrix' and 
			   		element_matrix_parent_id = 0 and 
			   		element_matrix_allow_multiselect = 1 and 
			   		element_status = 1 and 
			   		form_id = ?";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);

	$matrix_checkboxes_id_array = array();
	while($row = mf_do_fetch_result($sth)){
		$matrix_checkboxes_id_array[] = $row['element_id'];
		if(!empty($row['element_constraint'])){
			$exploded = array();
			$exploded = explode(',', $row['element_constraint']);
			foreach ($exploded as $value) {
				$matrix_checkboxes_id_array[] = $value;
			}
		}
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


	//get the list of all fields within the form (including child elements for checkboxes, matrix, etc)
	$columns_meta  = mf_get_columns_meta($dbh,$form_id);
	$columns_label = $columns_meta['name_lookup'];
	$columns_type  = $columns_meta['type_lookup'];

	$field_labels = array_slice($columns_label, 4); //the first four labels are system field. we don't need it.

	//prepare the jquery data for column type lookup
	foreach ($columns_type as $element_name => $element_type) {
		if($element_type == 'matrix'){
			//if this is matrix field which allow multiselect, change the type to checkbox
			$temp = array();
			$temp = explode('_', $element_name);
			$matrix_element_id = $temp[1];

			if(in_array($matrix_element_id, $matrix_checkboxes_id_array)){
				$element_type = 'checkbox';
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

		$jquery_data_code .= "\$('#as_fields_lookup').data('$element_name','$element_type');\n";
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
	

	$header_data =<<<EOT
<link type="text/css" href="js/datepick/smoothness.datepick.css{$mf_version_tag}" rel="stylesheet" />
EOT;
	
	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post approval_settings" data-csrftoken="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>" data-formid="<?php echo $form_id; ?>">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Approval Workflow</h2>
							<p>Configure approval workflow and approvers</p>
						</div>	
						<div style="float: right;margin-right: 5px">
								<a href="#" id="button_save_approval_settings" class="bb_button bb_small bb_green">
									<span class="icon-disk" style="margin-right: 5px"></span>Save Settings
								</a>
						</div>
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>

				<div class="content_body">
					
					<ul id="as_main_list">
						<li>
							<div id="as_box_approval_settings" class="as_box_main gradient_blue">
								<div class="ps_box_meta">
									<h1>A.</h1>
									<h6>Approval Settings</h6>
								</div>
								<div class="as_box_content">
									<label class="description" for="ps_select_merchant" style="margin-top: 10px">
										Approval Workflow Type
									</label>
									<select class="select" id="as_select_workflow" style="width: 60%" autocomplete="off">
										<?php if(empty($approval_settings->workflow_type)){ ?>
										<option value=""></option>
										<?php } ?>

										<option <?php if($approval_settings->workflow_type == 'parallel'){ echo 'selected="selected"'; } ?> value="parallel">Single-Step Approval</option>
										<option <?php if($approval_settings->workflow_type == 'serial'){ echo 'selected="selected"'; } ?> value="serial">Multi-Step Approval</option>
									</select>

									<label id="single-step-option-label" class="description" style="display: <?php if($approval_settings->workflow_type == 'parallel'){ echo 'block'; }else{ echo 'none'; } ?>">Single-Step Approval Rule </label>
									<div id="single-step-option-div" style="display: <?php if($approval_settings->workflow_type == 'parallel'){ echo 'block'; }else{ echo 'none'; } ?>">
										<span style="display: block">
											<input id="parallel_workflow_any"  name="parallel_workflow" class="element radio" type="radio" value="any" <?php if($approval_settings->parallel_workflow == 'any'){ echo 'checked="checked"'; } ?>  />
											<label class="inline" for="parallel_workflow_any">Approve or deny based on the FIRST response </label>
											<span class="icon-question helpicon clearfix" data-tippy-content="The entry is approved or rejected immediately once any of the approvers approve or reject the request."></span>
										</span>
										<span style="display: block">
											<input id="parallel_workflow_all"  name="parallel_workflow" class="element radio" type="radio" value="all" <?php if($approval_settings->parallel_workflow == 'all'){ echo 'checked="checked"'; } ?>/>
											<label class="inline" for="parallel_workflow_all">Require unanimous approval from ALL approvers </label>
											<span class="icon-question helpicon clearfix" data-tippy-content="The entry is only approved if all of the approvers approve the request. The approval request is rejected if any of the approvers reject the request."></span>
										</span>
									</div>

									<div id="single-step-approval-info" class="blue_box" style="width: 85%; display: <?php if($approval_settings->workflow_type == 'parallel'){ echo 'block'; }else{ echo 'none'; } ?>;margin-bottom: 20px">
										<span class="icon-info" style="margin-right: 5px;font-size: 120%"></span> Single-Step Approval send the approval to all approvers at the same time. All approvers will be able to approve or deny entry simultaneously, 
										without the need to wait for other approvers to approve.
									</div>
									<div id="multi-step-approval-info" class="blue_box" style="width: 85%; display: <?php if($approval_settings->workflow_type == 'serial'){ echo 'block'; }else{ echo 'none'; } ?>;margin-bottom: 20px">
										<span class="icon-info"></span> Multi-Step Approval send the approval in a sequential process. After a user approve, it will move to the next user to approve. 
										In order for an entry to be marked as approved, every approver must individually approve the entry.
									</div>
									

								</div>
							</div>
						</li>
						<li class="ps_arrow"><span class="icon-arrow-down11 spacer-icon"></span></li>
						<li>
							<div id="as_box_approvers" class="as_box_main gradient_red">
								<div class="as_box_meta">
									<h1>B.</h1>
									<h6>Approvers</h6>
								</div>
								<div class="as_box_content">
									<div id="as_add_approvers_div">
										<label class="description inline" for="add_user_to_approver" style="margin-top: 2px">
										Add User to Approvers
										</label>
										<span class="icon-question helpicon clearfix" data-tippy-content="If you're using the Multi-Step Approval, the last person added here will become the final approver."></span>
										<select class="select medium" id="add_user_to_approver" name="add_user_to_approver" autocomplete="off">
											<option value="">--Select User--</option>
											<?php
												foreach ($user_list_array as $value) {
													if(!empty($all_approvers_user_id)){
														if(in_array($value['user_id'], $all_approvers_user_id)){
															continue;
														}
													}
													echo "<option value=\"{$value['user_id']}\">{$value['user_fullname']}</option>\n";		
												}	
											?>
										</select>
										<select class="select medium" id="add_user_to_approver_lookup" name="add_user_to_approver_lookup" autocomplete="off" style="display: none">
											<option value="">--Select User--</option>
											<?php
												foreach ($user_list_array as $value) {
													echo "<option data-email=\"{$value['user_email']}\" data-lastlogin=\"{$value['last_login_date']}\" value=\"{$value['user_id']}\">{$value['user_fullname']}</option>\n";		
												}	
											?>
										</select>
										<select id="as_fields_lookup" name="as_fields_lookup" class="element select condition_fieldname"  autocomplete="off" style="display:none">
										<?php
											foreach ($field_labels as $element_name => $element_label) {
												
												if($columns_type[$element_name] == 'signature' || $columns_type[$element_name] == 'file'){
													continue;
												}

												$element_label = strip_tags($element_label);
												if(strlen($element_label) > 40){
													$element_label = substr($element_label, 0, 40).'...';
												}
												
												echo "<option value=\"{$element_name}\">{$element_label}</option>\n";
											}
										?>
										</select>
									</div>

									<ul id="approvers_list" style="margin-top: 25px; margin-bottom: 25px">
									<?php 
										if(!empty($approvers_list_array)){
											foreach ($approvers_list_array as $value) {
												$user_id = $value['user_id'];
												$user_position = $value['user_position'];		
												$user_fullname = htmlspecialchars($value['user_fullname'],ENT_QUOTES);
												$user_email    = $value['user_email'];
												$rule_all_any    = $value['rule_all_any'];
												$user_position = (int) $value['user_position'];
												
												$last_login_date = mf_short_relative_date($value['last_login_date']);
												if(empty($last_login_date)){
													$last_login_date = 'never';
												}

												$jquery_data_code .= "\$(\"#liapproverrule_{$user_id}\").data('rule_properties',{\"user_id\": {$user_id},\"user_position\": {$user_position},\"rule_all_any\":\"{$rule_all_any}\"});\n";
									?>
												<li id="liapproverrule_<?php echo $user_id; ?>">
													<?php
															$current_user_conditions = array();
															$current_user_conditions = $approvers_logic_conditions_array[$user_id] ?? '';
													?>
													<div class="approver_no"><?php echo $user_position; ?></div>
													<div class="approver_info">
														<h3><?php echo $user_fullname; if(!empty($current_user_conditions)){ echo '*'; } ?></h3>
														<h6><?php echo $user_email; ?></h6>
														<em>Last logged in: <?php echo $last_login_date; ?></em>
													</div>
													<div class="approver_action">
														<div class="approver_action_delete"><a title="Remove Approver" class="delete_liapproverrule" id="deleteliapproverrule_<?php echo $user_id; ?>" href="#"><span class="icon-cancel-circle"></span> </a></div>
														<div class="approver_action_settings"><a title="Approver Rules" class="approver_rules_toggle" id="approverrulestoggle_<?php echo $user_id; ?>" href="#"><span class="icon-settings"></span> </a></div>
													</div>
													<div class="approver_rules" style="display: none">
						
														<?php if(!empty($current_user_conditions)){ ?>
														<h6>
															<span class="icon-arrow-right2"></span>
															Send the approval to this user if 
															<select style="margin-left: 5px;margin-right: 5px" name="approverruleallany_<?php echo $user_id; ?>" id="fieldruleallany_<?php echo $user_id; ?>" class="element select rule_all_any">
																<option value="all" <?php if($rule_all_any == 'all'){ echo 'selected="selected"'; } ?>>all</option>
																<option value="any" <?php if($rule_all_any == 'any'){ echo 'selected="selected"'; } ?>>any</option>
															</select> of the following conditions match: 
														</h6>
														<?php } ?>

														<!-- start rules conditions -->
														<ul class="as_approver_rules_conditions">
															<?php
																	if(!empty($current_user_conditions)){
																		$i = 1;
																		foreach ($current_user_conditions as $value) {
																			$condition_element_name = $value['element_name'];
																			$rule_condition 		= $value['rule_condition'];
																			$rule_keyword 			= htmlspecialchars($value['rule_keyword'],ENT_QUOTES);
																			$condition_element_id   = (int) str_replace('element_', '', $condition_element_name); 
																			
																			$field_element_type = $columns_type[$value['element_name']];
																			$field_select_radio_data = array();
														
																			if($field_element_type == 'matrix'){
																				//if this is matrix field which allow multiselect, change the type to checkbox
																				$temp = array();
																				$temp = explode('_', $condition_element_name);
																				$matrix_element_id = $temp[1];

																				if(in_array($matrix_element_id, $matrix_checkboxes_id_array)){
																					$field_element_type = 'checkbox';
																				}
																			}else if($field_element_type == 'time'){
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

																			$rule_condition_data = new stdClass();
																			$rule_condition_data->target_user_id 	= $user_id;
																			$rule_condition_data->element_name 		= $condition_element_name;
																			$rule_condition_data->condition 		= $rule_condition;
																			$rule_condition_data->keyword 			= htmlspecialchars_decode($rule_keyword,ENT_QUOTES);

																			$json_rule_condition = json_encode($rule_condition_data);

																			$jquery_data_code .= "\$(\"#liapproverrule_{$user_id}_{$i}\").data('rule_condition',{$json_rule_condition});\n";

																			$condition_date_class = '';
																			$time_hour   = '';
																			$time_minute = '';
																			$time_second = '';
																			$time_ampm   = 'AM';
																			
																			if(in_array($field_element_type, array('money','number'))){
																				$condition_text_display = 'display:none';
																				$condition_number_display = '';
																				$condition_date_display = 'display:none';
																				$condition_time_display = 'display:none';
																				$condition_checkbox_display = 'display:none';
																				$condition_keyword_display = '';
																				$condition_select_display = 'display:none';
																				$condition_rating_display = 'display:none';
																				$condition_ratingvalues_display = 'display:none';
																			}else if(in_array($field_element_type, array('date','europe_date'))){
																				$condition_text_display = 'display:none';
																				$condition_number_display = 'display:none';
																				$condition_date_display = '';
																				$condition_time_display = 'display:none';
																				$condition_checkbox_display = 'display:none';
																				$condition_keyword_display = '';
																				$condition_date_class = 'class="condition_date"';
																				$condition_select_display = 'display:none';
																				$condition_rating_display = 'display:none';
																				$condition_rating_values_display = 'display:none';
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
																				$condition_time_display = 'display:none';
																				$condition_checkbox_display = 'display:none';
																				$condition_keyword_display = '';
																				$condition_select_display = 'display:none';
																				$condition_rating_display = 'display:none';
																				$condition_ratingvalues_display = 'display:none';
																			}else if($field_element_type == 'checkbox'){
																				$condition_text_display = 'display:none';
																				$condition_number_display = 'display:none';
																				$condition_date_display = 'display:none';
																				$condition_time_display = 'display:none';
																				$condition_checkbox_display = '';
																				$condition_keyword_display = 'display:none';
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
																			}
															?>
															
																	<li id="liapproverrule_<?php echo $user_id.'_'.$i; ?>" <?php echo $condition_date_class; ?>>
																			<select id="conditionfield_<?php echo $user_id.'_'.$i; ?>" name="conditionfield_<?php echo $user_id.'_'.$i; ?>" autocomplete="off" class="element select condition_fieldname">
																				<?php
																					foreach ($field_labels as $element_name => $element_label) {
																						
																						if($columns_type[$element_name] == 'signature' || $columns_type[$element_name] == 'file'){
																							continue;
																						}

																						$element_label = strip_tags($element_label);
																						if(strlen($element_label) > 40){
																							$element_label = substr($element_label, 0, 40).'...';
																						}
																						
																						if($condition_element_name == $element_name){
																							$selected_tag = 'selected="selected"';
																						}else{
																							$selected_tag = '';
																						}

																						echo "<option {$selected_tag} value=\"{$element_name}\">{$element_label}</option>\n";
																					}
																				?>
																			</select>
																			<select name="conditiontext_<?php echo $user_id.'_'.$i; ?>" id="conditiontext_<?php echo $user_id.'_'.$i; ?>" class="element select condition_text" style="<?php echo $condition_text_display; ?>">
																				<option <?php if($value['rule_condition'] == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
																				<option <?php if($value['rule_condition'] == 'is_not'){ echo 'selected="selected"'; } ?> value="is_not">Is Not</option>
																				<option <?php if($value['rule_condition'] == 'begins_with'){ echo 'selected="selected"'; } ?> value="begins_with">Begins with</option>
																				<option <?php if($value['rule_condition'] == 'ends_with'){ echo 'selected="selected"'; } ?> value="ends_with">Ends with</option>
																				<option <?php if($value['rule_condition'] == 'contains'){ echo 'selected="selected"'; } ?> value="contains">Contains</option>
																				<option <?php if($value['rule_condition'] == 'not_contain'){ echo 'selected="selected"'; } ?> value="not_contain">Does not contain</option>
																			</select>
																			<select name="conditionnumber_<?php echo $user_id.'_'.$i; ?>" id="conditionnumber_<?php echo $user_id.'_'.$i; ?>" class="element select condition_number" style="<?php echo $condition_number_display; ?>">
																				<option <?php if($value['rule_condition'] == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
																				<option <?php if($value['rule_condition'] == 'less_than'){ echo 'selected="selected"'; } ?> value="less_than">Less than</option>
																				<option <?php if($value['rule_condition'] == 'greater_than'){ echo 'selected="selected"'; } ?> value="greater_than">Greater than</option>
																			</select>
																			<select name="conditionrating_<?php echo $user_id.'_'.$i; ?>" id="conditionrating_<?php echo $user_id.'_'.$i; ?>" class="element select condition_rating" style="width: 120px;<?php echo $condition_rating_display; ?>">
																				<option <?php if($value['rule_condition'] == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
																				<option <?php if($value['rule_condition'] == 'is_not'){ echo 'selected="selected"'; } ?> value="is_not">Is Not</option>
																				<option <?php if($value['rule_condition'] == 'less_than'){ echo 'selected="selected"'; } ?> value="less_than">Less than</option>
																				<option <?php if($value['rule_condition'] == 'greater_than'){ echo 'selected="selected"'; } ?> value="greater_than">Greater than</option>
																			</select>
																			<select name="conditiondate_<?php echo $user_id.'_'.$i; ?>" id="conditiondate_<?php echo $user_id.'_'.$i; ?>" class="element select condition_date" style="width: 120px;<?php echo $condition_date_display; ?>">
																				<option <?php if($value['rule_condition'] == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
																				<option <?php if($value['rule_condition'] == 'is_before'){ echo 'selected="selected"'; } ?> value="is_before">Is Before</option>
																				<option <?php if($value['rule_condition'] == 'is_after'){ echo 'selected="selected"'; } ?> value="is_after">Is After</option>
																			</select>
																			<select name="conditioncheckbox_<?php echo $user_id.'_'.$i; ?>" id="conditioncheckbox_<?php echo $user_id.'_'.$i; ?>" class="element select condition_checkbox" style="<?php echo $condition_checkbox_display; ?>">
																				<option <?php if($value['rule_condition'] == 'is_one'){ echo 'selected="selected"'; } ?> value="is_one">Is Checked</option>
																				<option <?php if($value['rule_condition'] == 'is_zero'){ echo 'selected="selected"'; } ?> value="is_zero">Is Empty</option>
																			</select>
																			<select id="conditionselect_<?php echo $user_id.'_'.$i; ?>" name="conditionselect_<?php echo $user_id.'_'.$i; ?>" autocomplete="off" class="element select condition_select" style="<?php echo $condition_select_display; ?>">
																				<?php
																					if(!empty($field_select_radio_data)){
																						foreach ($field_select_radio_data as $option_title) {
																							$option_value = $option_title;
																							$option_title = strip_tags($option_title);
																							
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
																			<select id="conditionratingvalues_<?php echo $user_id.'_'.$i; ?>" name="conditionratingvalues_<?php echo $user_id.'_'.$i; ?>" autocomplete="off" class="element select condition_ratingvalues" style="<?php echo $condition_ratingvalues_display; ?>">
																				<?php

																					$rating_max = $rating_field_properties[$condition_element_id]['rating_max'] ?? 0;
																																									
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
																			<span name="conditiontime_<?php echo $user_id.'_'.$i; ?>" id="conditiontime_<?php echo $user_id.'_'.$i; ?>" class="condition_time" style="<?php echo $condition_time_display; ?>">
																				<input name="conditiontimehour_<?php echo $user_id.'_'.$i; ?>" id="conditiontimehour_<?php echo $user_id.'_'.$i; ?>" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_hour; ?>" placeholder="HH"> : 
																				<input name="conditiontimeminute_<?php echo $user_id.'_'.$i; ?>" id="conditiontimeminute_<?php echo $user_id.'_'.$i; ?>" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_minute; ?>" placeholder="MM">  
																				<span class="conditiontime_second" style="<?php echo $condition_second_display; ?>"> : <input name="conditiontimesecond_<?php echo $user_id.'_'.$i; ?>" id="conditiontimesecond_<?php echo $user_id.'_'.$i; ?>" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_second; ?>" placeholder="SS"> </span>
																				<select class="element select conditiontime_ampm conditiontime_input" name="conditiontimeampm_<?php echo $user_id.'_'.$i; ?>" id="conditiontimeampm_<?php echo $user_id.'_'.$i; ?>" style="<?php echo $condition_ampm_display; ?>">
																					<option <?php if($time_ampm == 'AM'){ echo 'selected="selected"'; } ?> value="AM">AM</option>
																					<option <?php if($time_ampm == 'PM'){ echo 'selected="selected"'; } ?> value="PM">PM</option>
																				</select>
																			</span>
																			<input type="text" class="element text condition_keyword" value="<?php echo $rule_keyword; ?>" id="conditionkeyword_<?php echo $user_id.'_'.$i; ?>" name="conditionkeyword_<?php echo $user_id.'_'.$i; ?>" style="<?php echo $condition_keyword_display; ?>">
																			<input type="hidden" value="" class="rule_datepicker" name="datepicker_<?php echo $user_id.'_'.$i; ?>" id="datepicker_<?php echo $user_id.'_'.$i; ?>">
											 		 						<span style="display:none"><img id="datepickimg_<?php echo $user_id.'_'.$i; ?>" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>
																			<a href="#" id="deletecondition_<?php echo $user_id.'_'.$i; ?>" name="deletecondition_<?php echo $user_id.'_'.$i; ?>" class="a_delete_condition"><span class="icon-minus-circle2"></span></a>
																	</li>
																
															<?php 
																			$i++;
																		} //end foreach current_user_conditions 
															?>		
																									
																	<li class="as_add_condition">
																		<a href="#" id="addcondition_<?php echo $user_id; ?>" class="a_add_condition"><span class="icon-plus-circle"></span></a>
																	</li>

															<?php } //end if(!empty($current_user_conditions)) ?>
																	<li class="as_approver_logic_info" style="<?php if(!empty($current_user_conditions)){ echo 'display: none';}; ?>">
																		This user will receive the approval on any conditions.<br/>
																		You can <a id="addapproverlogic_<?php echo $user_id; ?>" class="as_add_approver_logic" href="#">Add Approver Logic</a> to include this user only when specific conditions are met. 
																	</li>
														</ul>
														<!-- end rules conditions -->
													</div>
												</li>
									<?php 	}
										}
									?>
									</ul>

								</div>
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
<script type="text/javascript" src="js/jquery-ui/ui/jquery.effects.core.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.effects.pulsate.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/datepick/jquery.datepick.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/approval_settings.js{$mf_version_tag}"></script>
<style>
.tippy-tooltip{
	font-size: 98%;
}
</style>
EOT;

	require('includes/footer.php'); 
?>