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
	
	if(!empty($_GET['sortby'])){
		$sort_by = trim($_GET['sortby']);
		$_SESSION['sort_by'] = $sort_by;
	}elseif (!empty($_SESSION['sort_by'])) {
		$sort_by = $_SESSION['sort_by'];
	}else{
		$sort_by = 'user_id-desc';
	}

	//get page number for pagination
	if (isset($_REQUEST['pageno'])) {
	   $pageno = $_REQUEST['pageno'];
	}else{
	   $pageno = 1;
	}

	
	$dbh = mf_connect_db();
	
	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);

	//check user privileges, is this user has privilege to administer MachForm?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$_SESSION['MF_DENIED'] = "You don't have permission to administer MachForm.";

		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
		exit;
	}
	

	$jquery_data_code = '';

	//set columns label
	$columns_label['user_id'] 			= 'User ID#';
	$columns_label['user_fullname']		= 'Name';
	$columns_label['user_email']		= 'Email';
	$columns_label['priv_administer']	= 'Admin Privileges';
	$columns_label['status']			= 'Status';

	$columns_type['user_id'] 			= 'number';
	$columns_type['user_fullname']		= 'text';
	$columns_type['user_email']			= 'text';
	$columns_type['priv_administer'] 	= 'admin';
	$columns_type['status']			 	= 'status';
	
	//prepare the jquery data for column type lookup
	foreach ($columns_type as $element_name => $element_type) {
		$jquery_data_code .= "\$('#filter_pane').data('$element_name','$element_type');\n";
	}

	$filter_data = '';
	$entries_filter_type = 'all';

	if(!empty($_SESSION['filter_users'])){
		$filter_data = $_SESSION['filter_users'];
		$entries_filter_type = $_SESSION['filter_users_type'];
	}

	//check current license usage, if this is Standard or Professional
	$is_user_max = false;
	if($mf_settings['license_key'][0] == 'S' || $mf_settings['license_key'][0] == 'P'){
		$query = "select count(user_id) user_total from ".MF_TABLE_PREFIX."users where `status` > 0";
		
		$params = array();
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		$current_total_user = $row['user_total'];

		if($mf_settings['license_key'][0] == 'S'){
			$max_user = 5;
		}else if($mf_settings['license_key'][0] == 'P'){
			$max_user = 20;
		}

		if($current_total_user >= $max_user){
			$is_user_max = true;
		}

		$total_user_left = $max_user - $current_total_user;
	}


	$header_data =<<<EOT
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="css/pagination_classic.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="css/dropui.css{$mf_version_tag}" rel="stylesheet" />
EOT;
	
	$current_nav_tab = 'users';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post manage_users">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2>Users Manager</h2>
							<p>Create, edit and manage users permissions</p>
						</div>	
						
						<?php if((!$is_user_max && empty($mf_settings['ldap_enable'])) || 
							     (!$is_user_max && !empty($mf_settings['ldap_enable']) && empty($mf_settings['ldap_exclusive'])) 
							    ){ ?>
							<div id="button_add_user_div">
									<a href="add_user.php" id="button_add_user" class="bb_button bb_small bb_green">
										<span class="icon-user-plus" style="margin-right: 5px"></span>Create New User!
									</a>
							</div>
						<?php } ?>
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>

				<div class="content_body">
						
						<div id="entries_actions" class="gradient_red">
							<ul>
								<li>
									<a id="user_delete" href="#"><span class="icon-remove"></span>Delete</a>
								</li>
								<li>
									<div class="vline_separator" style="height: 35px;margin-top:5px"></div>
								</li>
								<li>
									<a id="user_suspend" href="#"><span class="icon-user-block"></span>Suspend</a>
								</li>
							</ul>
							<img id="entries_actions_arrow" src="images/icons/29.png" style="position: absolute;left:5px;top:100%" />
						</div>
						
						<div id="entries_options" data-csrftoken="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>" class="gradient_blue">
							<ul>
								<li>
									<a id="entry_filter" href="#"><span class="icon-binoculars"></span>Filter Users</a>
								</li>
							</ul>
						</div>

						<?php if(!empty($filter_data)){ ?>
							<div id="filter_info" style="margin-right: 148px">
								Displaying filtered users.  <a style="margin-left: 60px" id="me_edit_filter" href="#">Edit</a> or <a href="#" id="me_clear_filter">Clear Filter</a>
							</div>
						<?php } ?>
						
						<div style="clear: both"></div>
						
						<div id="filter_pane" style="display: none" class="gradient_blue">
							
							<h6>Display users that match 
									<select style="margin-left: 5px;margin-right: 5px" name="filter_all_any" id="filter_all_any" class="element select"> 
										<option value="all" <?php if($entries_filter_type == 'all'){ echo 'selected="selected"'; } ?>>all</option>
										<option value="any" <?php if($entries_filter_type == 'any'){ echo 'selected="selected"'; } ?>>any</option>
									</select> 
								of the following conditions:
							</h6>
							
							<ul>

								<?php
									if(empty($filter_data)){
										$field_labels = $columns_label;
										
										$condition_text_display = 'display:none';
										$condition_number_display = '';
										$condition_admin_display = 'display:none';
										$condition_status_display = 'display:none';
										$filter_keyword_display = '';

										//prepare the jquery data for the filter list
										$filter_properties = new stdClass();
										$filter_properties->element_name = 'user_id';
										$filter_properties->condition    = 'is';
										$filter_properties->keyword 	 = '';

										$json_filter_properties = json_encode($filter_properties);
										$jquery_data_code .= "\$('#li_1').data('filter_properties',{$json_filter_properties});\n";
								?>

								<li id="li_1" class="filter_settings">
									<select name="filterfield_1" id="filterfield_1" class="element select condition_fieldname" style="width: 260px"> 
											<?php
												foreach ($field_labels as $element_name => $element_label) {
													echo "<option value=\"{$element_name}\">{$element_label}</option>\n";
												}
											?>
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
									<select name="conditionadmin_1" id="conditionadmin_1" class="element select condition_admin" style="width: 180px;<?php echo $condition_admin_display; ?>">
										<option value="is_admin">Is Administrator</option>
										<option value="is_not_admin">Is not Administrator</option>
									</select>
									<select name="conditionstatus_1" id="conditionstatus_1" class="element select condition_status" style="width: 180px;<?php echo $condition_status_display; ?>">
										<option value="is_active">Is Active</option>
										<option value="is_suspended">Is Suspended</option>
									</select>
									
									<input type="text" class="element text filter_keyword" value="" id="filterkeyword_1" style="<?php echo $filter_keyword_display; ?>">
									
									<a href="#" id="deletefilter_1" class="filter_delete_a"><img src="images/icons/51_blue_32.png" width="16" height="16" /></a>

								</li>

								<?php 
									} else { 
										
										$field_labels = $columns_label;
										
										$i=1;
										$filter_properties = new stdClass();

										foreach ($filter_data as $value) {
											$field_element_type = $columns_type[$value['element_name']];
											
											if($field_element_type == 'number'){
												$condition_text_display = 'display:none';
												$condition_number_display = '';
												$condition_admin_display = 'display:none';
												$condition_status_display = 'display:none';
												$filter_keyword_display = '';
											}else if($field_element_type == 'text'){
												$condition_text_display = '';
												$condition_number_display = 'display:none';
												$condition_admin_display = 'display:none';
												$condition_status_display = 'display:none';
												$filter_keyword_display = '';
											}else if($field_element_type == 'admin'){
												$condition_text_display = 'display:none';
												$condition_number_display = 'display:none';
												$condition_admin_display = '';
												$condition_status_display = 'display:none';
												$filter_keyword_display = 'display:none';
											}else if($field_element_type == 'status'){
												$condition_text_display = 'display:none';
												$condition_number_display = 'display:none';
												$condition_admin_display = 'display:none';
												$condition_status_display = '';
												$filter_keyword_display = 'display:none';
											}

											//prepare the jquery data for the filter list
											$filter_properties->element_name = $value['element_name'];
											$filter_properties->condition    = $value['filter_condition'];
											$filter_properties->keyword 	 = $value['filter_keyword'];

											$json_filter_properties = json_encode($filter_properties);
											$jquery_data_code .= "\$('#li_{$i}').data('filter_properties',{$json_filter_properties});\n";
								?>			

								<li id="li_<?php echo $i; ?>" class="filter_settings">
									<select name="filterfield_<?php echo $i; ?>" id="filterfield_<?php echo $i; ?>" class="element select condition_fieldname" style="width: 260px"> 
											<?php
												foreach ($field_labels as $element_name => $element_label) {
													if($element_name == $value['element_name']){
														$selected_tag = 'selected="selected"';
													}else{
														$selected_tag = '';
													}
													echo "<option {$selected_tag} value=\"{$element_name}\">{$element_label}</option>\n";
												}
											?>
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
									<select name="conditionadmin_<?php echo $i; ?>" id="conditionadmin_<?php echo $i; ?>" class="element select condition_admin" style="width: 180px;<?php echo $condition_admin_display; ?>">
										<option <?php if($value['filter_condition'] == 'is_admin'){ echo 'selected="selected"'; } ?> value="is_admin">Is Administrator</option>
										<option <?php if($value['filter_condition'] == 'is_not_admin'){ echo 'selected="selected"'; } ?> value="is_not_admin">Is not Administrator</option>
									</select>
									<select name="conditionstatus_<?php echo $i; ?>" id="conditionstatus_<?php echo $i; ?>" class="element select condition_status" style="width: 180px;<?php echo $condition_status_display; ?>">
										<option <?php if($value['filter_condition'] == 'is_active'){ echo 'selected="selected"'; } ?> value="is_active">Is Active</option>
										<option <?php if($value['filter_condition'] == 'is_suspended'){ echo 'selected="selected"'; } ?> value="is_suspended">Is Suspended</option>
									</select>

									<input type="text" class="element text filter_keyword" value="<?php echo htmlspecialchars($value['filter_keyword'],ENT_QUOTES); ?>" id="filterkeyword_<?php echo $i; ?>" style="<?php echo $filter_keyword_display; ?>">
									
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
									<input type="button" id="me_filter_pane_submit" value="Apply Filter" class="bb_button bb_mini bb_blue"> <span style="margin-left: 5px" id="cancel_filter_pane_span">or <a href="#" id="filter_pane_cancel">Cancel</a></span>
							</div>
							<img style="position: absolute;right:35px;top:-12px" src="images/icons/29_blue.png" />
						</div>

						<?php 
							$entries_options['page_number']   = $pageno; //set the page number to be displayed
							$entries_options['rows_per_page'] = 20; //set the maximum rows to be displayed each page

							//set the sorting options
							$exploded = explode('-', $sort_by);
							$entries_options['sort_element'] = $exploded[0]; //the element name, e.g. element_2
							$entries_options['sort_order']	 = $exploded[1]; //asc or desc

							//set filter options
							$entries_options['filter_data'] = $filter_data;
							$entries_options['filter_type'] = $entries_filter_type;

							echo mf_display_users_table($dbh,$entries_options); 
						?>

						<?php if($mf_settings['license_key'][0] == 'S' || $mf_settings['license_key'][0] == 'P'){ ?>
						<div id="me_pagination_label">
							You have <strong style="color: #CB6852"><?php echo $total_user_left; ?> users</strong> left. &nbsp;&nbsp;<a href="main_settings.php" class="breadcrumb">Upgrade License Type</a> to add more users.
						</div>
						<?php } ?>	
						
						<div id="me_sort_option">
							<label class="description" for="me_sort_by">Sort By &#8674; </label>
							<select class="element select" id="me_sort_by" name="me_sort_by"> 
								<optgroup label="Ascending">
									<option <?php if($sort_by == 'user_id-asc'){ echo 'selected="selected"'; } ?> value="user_id-asc">User ID</option>
									<option <?php if($sort_by == 'user_fullname-asc'){ echo 'selected="selected"'; } ?> value="user_fullname-asc">Name</option>
									<option <?php if($sort_by == 'user_email-asc'){ echo 'selected="selected"'; } ?> value="user_email-asc">Email</option>
									<option <?php if($sort_by == 'priv_administer-asc'){ echo 'selected="selected"'; } ?> value="priv_administer-asc">Admin Privileges</option>
									<option <?php if($sort_by == 'status-asc'){ echo 'selected="selected"'; } ?> value="status-asc">Status</option>
								</optgroup>
								<optgroup label="Descending">
									<option <?php if($sort_by == 'user_id-desc'){ echo 'selected="selected"'; } ?> value="user_id-desc">User ID</option>
									<option <?php if($sort_by == 'user_fullname-desc'){ echo 'selected="selected"'; } ?> value="user_fullname-desc">Name</option>
									<option <?php if($sort_by == 'user_email-desc'){ echo 'selected="selected"'; } ?> value="user_email-desc">Email</option>
									<option <?php if($sort_by == 'priv_administer-desc'){ echo 'selected="selected"'; } ?> value="priv_administer-desc">Admin Privileges</option>
									<option <?php if($sort_by == 'status-desc'){ echo 'selected="selected"'; } ?> value="status-desc">Status</option>
								</optgroup>
							</select>
						</div>
					
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

<div id="dialog-warning" title="Error Title" class="buttons" style="display: none">
	<span class="icon-bubble-notification"></span>
	<p id="dialog-warning-msg">
		Error
	</p>
</div>
<div id="dialog-confirm-user-delete" title="Are you sure you want to delete selected users?" class="buttons" style="display: none">
	<span class="icon-bubble-notification"></span>
	<p id="dialog-confirm-user-delete-msg">
		This action cannot be undone.<br/>
		<strong id="dialog-confirm-user-delete-info">The user will be deleted permanently and no longer has access to MachForm.</strong><br/><br/>
		
	</p>			
</div>
<div id="dialog-confirm-user-suspend" title="Are you sure you want to suspend selected users?" class="buttons" style="display: none">
	<span class="icon-bubble-notification"></span>
	<p id="dialog-confirm-user-suspend-msg">
		
		<strong id="dialog-confirm-user-suspend-info">The user will be suspended and no longer has access to MachForm.</strong><br/><br/>
		
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
<script type="text/javascript" src="js/manage_users.js{$mf_version_tag}"></script>
EOT;

	require('includes/footer.php'); 
?>