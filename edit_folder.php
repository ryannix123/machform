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
	

	$dbh = mf_connect_db();
	
	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);

	$folder_id = isset($_GET['id']) ? (int) $_GET['id'] : false;

	//get folder data
	$folder_name = '';
	$rule_all_any = '';

	$condition_date_picker_display = '';
	$condition_date_keyword_display = '';
	$condition_date_period_display = '';
	$condition_date_keyword = '';
	$condition_date_period = '';
	$mf_data_tag = '';

	if(!empty($folder_id)){
		//get folder name
		$query = "SELECT folder_name,rule_all_any FROM `".MF_TABLE_PREFIX."folders` WHERE user_id = ? and folder_id = ?";
		$params = array($_SESSION['mf_user_id'],$folder_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		$folder_name  = htmlspecialchars($row['folder_name'], ENT_QUOTES);
		$rule_all_any = $row['rule_all_any'];

		//get folder conditions
		$query = "SELECT 
						element_name,
						rule_condition,
						rule_keyword 
					FROM 
						`".MF_TABLE_PREFIX."folders_conditions` 
				   WHERE 
				   		user_id = ? AND folder_id = ? ORDER BY `id` ASC";
		$params = array($_SESSION['mf_user_id'],$folder_id);
		$sth = mf_do_query($query,$params,$dbh);

		$folder_rules = array();
		$i=0;$j=1;
		while($row = mf_do_fetch_result($sth)){
			$folder_rules[$i]['element_name'] 	= $row['element_name'];
			$folder_rules[$i]['rule_condition'] = $row['rule_condition'];
			$folder_rules[$i]['rule_keyword'] 	= $row['rule_keyword'];
			
			$folder_prop = new stdClass();
			$folder_prop->element_name 	 = $folder_rules[$i]['element_name'];
			$folder_prop->rule_condition = $folder_rules[$i]['rule_condition'];
			$folder_prop->rule_keyword 	 = $folder_rules[$i]['rule_keyword'];
			$folder_prop_json = json_encode($folder_prop);

			$mf_data_tag .= "mf_data.folder_rules[{$j}] = {$folder_prop_json};\n";
			$i++;$j++;
		}
	}

	//if there is no conditions, initialize the first condition
	if(empty($folder_rules)){
		$folder_rules[0]['element_name'] = 'title';
		$folder_rules[0]['rule_condition'] = 'is';
		$folder_rules[0]['rule_keyword'] = '';

		$mf_data_tag = "mf_data.folder_rules[1] = {element_name : 'title', rule_condition : 'is', rule_keyword : ''};";
	}

	//get users list
	$query = "SELECT user_id,user_fullname FROM `".MF_TABLE_PREFIX."users` WHERE `status` != 0 ORDER BY user_fullname ASC";
	$params = array();
	$sth = mf_do_query($query,$params,$dbh);
	
	$users_data = array();
	$i=0;
	while($row = mf_do_fetch_result($sth)){
		$users_data[$i]['user_id'] = $row['user_id'];
		$users_data[$i]['user_fullname'] = htmlspecialchars($row['user_fullname'],ENT_QUOTES);;
		$i++;
	}

	$header_data =<<<EOT
<link type="text/css" href="js/flatpickr/flatpickr.dark.css{$mf_version_tag}" rel="stylesheet" />
EOT;

	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post edit_folder">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<?php if(!empty($folder_id)){ ?>
								<h2><a class="breadcrumb" href='manage_forms.php'>Form Manager</a> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <a class="breadcrumb" href='manage_folders.php'>Folders</a> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Edit Folder</h2>
								<p>Editing smart folder <strong><?php echo $folder_name; ?></strong></p>
							<?php }else{ ?>
								<h2><a class="breadcrumb" href='manage_forms.php'>Form Manager</a> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <a class="breadcrumb" href='manage_folders.php'>Folders</a> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Add Folder</h2>
								<p>Create a new smart folder</p>
							<?php } ?>
						</div>
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>
				
				<?php mf_show_message(); ?>

				<div class="content_body">
					<div class="content_card content_card_primary" style="width: 635px">
						<label class="description" for="af_folder_name">Folder Name <span class="required">*</span></label>
						<input id="af_folder_name" name="af_folder_name" class="element text large" value="<?php echo $folder_name; ?>" type="text" style="width: 265px">
						
						<div id="folder_condition_pane" style="margin-bottom: 0px;">
							<h6 style="color: #000">Include forms matching 
									<select style="margin-left: 5px;margin-right: 5px" name="rule_all_any" id="rule_all_any" class="element select"> 
										<option <?php if($rule_all_any == 'all'){ echo 'selected="selected"'; } ?> value="all">all</option>
										<option <?php if($rule_all_any == 'any'){ echo 'selected="selected"'; } ?> value="any">any</option>
									</select> 
								of the following conditions:
							</h6>
							
							<ul>

							<?php 
								$i=0;
								foreach ($folder_rules as $value) {
									$condition_element_name = $value['element_name'];
									$condition_rule_condition = $value['rule_condition'];
									$condition_rule_keyword = htmlspecialchars($value['rule_keyword'],ENT_QUOTES);

									if($condition_element_name == 'created_date' || $condition_element_name == 'last_entry_date'){
										$condition_text_display = 'display: none';
										$condition_number_display = 'display: none';
										$condition_createdby_display = 'display: none';
										$condition_status_display = 'display: none';
										$condition_date_display = '';

										if($condition_rule_condition == 'exactly' || $condition_rule_condition == 'before' || $condition_rule_condition == 'after'){
											$condition_date_picker_display = '';
											$condition_date_keyword_display = 'display: none';
											$condition_date_period_display = 'display: none';
										}else if($condition_rule_condition == 'within_last'){
											$condition_date_picker_display = 'display: none';
											$condition_date_keyword_display = '';
											$condition_date_period_display = '';

											$exploded = array();
											$exploded = explode('-', $condition_rule_keyword); //the format: aa-bbbb (aa is number, bbbb is day/week/month/year)
											$condition_date_keyword = (int) $exploded[0];
											$condition_date_period = $exploded[1];
										}else{
											$condition_date_picker_display = 'display: none';
											$condition_date_keyword_display = 'display: none';
											$condition_date_period_display = 'display: none';
										}
									}else if($condition_element_name == 'created_by'){
										$condition_text_display = 'display: none';
										$condition_number_display = 'display: none';
										$condition_createdby_display = '';
										$condition_status_display = 'display: none';
										$condition_date_display = 'display: none';
									}else if($condition_element_name == 'status'){
										$condition_text_display = 'display: none';
										$condition_number_display = 'display: none';
										$condition_createdby_display = 'display: none';
										$condition_status_display = '';
										$condition_date_display = 'display: none';
									}else if($condition_element_name == 'total_entries' || $condition_element_name == 'today_entries'){
										$condition_text_display = 'display: none';
										$condition_number_display = '';
										$condition_createdby_display = 'display: none';
										$condition_status_display = 'display: none';
										$condition_date_display = 'display: none';
									}else{
										$condition_text_display = '';
										$condition_number_display = 'display: none';
										$condition_createdby_display = 'display: none';
										$condition_status_display = 'display: none';
										$condition_date_display = 'display: none';
									}

									$i++;
							?>
								<li id="li_<?php echo $i; ?>" class="filter_settings">
									<div class="condition_fieldname_container">
										<select name="filterfield_<?php echo $i; ?>" id="filterfield_<?php echo $i; ?>" class="element select condition_fieldname"> 
											<option <?php if($condition_element_name == 'title'){ echo 'selected="selected"'; } ?> value="title">Title</option>
											<option <?php if($condition_element_name == 'tag'){ echo 'selected="selected"'; } ?> value="tag">Tag</option>
											<option <?php if($condition_element_name == 'created_by'){ echo 'selected="selected"'; } ?> value="created_by">Created By</option>
											<option <?php if($condition_element_name == 'created_date'){ echo 'selected="selected"'; } ?> value="created_date">Created Date</option>
											<option <?php if($condition_element_name == 'status'){ echo 'selected="selected"'; } ?> value="status">Status</option>
											<option <?php if($condition_element_name == 'total_entries'){ echo 'selected="selected"'; } ?> value="total_entries">Total Entries</option>
											<option <?php if($condition_element_name == 'today_entries'){ echo 'selected="selected"'; } ?> value="today_entries">Today's Entries</option>
											<option <?php if($condition_element_name == 'last_entry_date'){ echo 'selected="selected"'; } ?> value="last_entry_date">Last Entry Date</option>
										</select>
									</div> 
									<div class="condition_text_container" style="<?php echo $condition_text_display; ?>">
										<select name="conditiontext_<?php echo $i; ?>" id="conditiontext_<?php echo $i; ?>" class="element select condition_text" style="width: 120px;">
											<option <?php if($condition_rule_condition == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
											<option <?php if($condition_rule_condition == 'is_not'){ echo 'selected="selected"'; } ?> value="is_not">Is Not</option>
											<option <?php if($condition_rule_condition == 'begins_with'){ echo 'selected="selected"'; } ?> value="begins_with">Begins with</option>
											<option <?php if($condition_rule_condition == 'ends_with'){ echo 'selected="selected"'; } ?> value="ends_with">Ends with</option>
											<option <?php if($condition_rule_condition == 'contains'){ echo 'selected="selected"'; } ?> value="contains">Contains</option>
											<option <?php if($condition_rule_condition == 'not_contain'){ echo 'selected="selected"'; } ?> value="not_contain">Does not contain</option>
										</select>
										
										<input name="filtertextkeyword_<?php echo $i; ?>" id="filtertextkeyword_<?php echo $i; ?>" class="element text filter_text_keyword" type="text" value="<?php echo $condition_rule_keyword; ?>" style="margin-left: 5px;">
									</div>
									<div class="condition_number_container" style="<?php echo $condition_number_display; ?>">
										<select name="conditionnumber_<?php echo $i; ?>" id="conditionnumber_<?php echo $i; ?>" class="element select condition_number" style="width: 120px;">
											<option <?php if($condition_rule_condition == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
											<option <?php if($condition_rule_condition == 'less_than'){ echo 'selected="selected"'; } ?> value="less_than">Less than</option>
											<option <?php if($condition_rule_condition == 'greater_than'){ echo 'selected="selected"'; } ?> value="greater_than">Greater than</option>
										</select>
										
										<input name="filternumberkeyword_<?php echo $i; ?>" id="filternumberkeyword_<?php echo $i; ?>" class="element text filter_number_keyword" type="text" value="<?php echo (int) $condition_rule_keyword; ?>" style="margin-left: 5px;">
									</div>
									<div class="condition_createdby_container" style="<?php echo $condition_createdby_display; ?>">
										<select name="conditioncreatedby_<?php echo $i; ?>" id="conditioncreatedby_<?php echo $i; ?>" class="element select condition_createdby" style="width: 250px;">
											<?php
												foreach ($users_data as $value) {
													$selected_tag = '';
													if($value['user_id'] == $condition_rule_keyword){
														$selected_tag = 'selected="selected"';
													}
													
													echo "<option {$selected_tag} value=\"{$value['user_id']}\">{$value['user_fullname']} (uid: {$value['user_id']})</option>";
												}
											?>
										</select>	
									</div>
									<div class="condition_status_container" style="<?php echo $condition_status_display; ?>">
										<select name="conditionstatus_<?php echo $i; ?>" id="conditionstatus_<?php echo $i; ?>" class="element select condition_status" style="width: 120px;">
											<option <?php if($condition_rule_condition == 'is_active'){ echo 'selected="selected"'; } ?> value="is_active">Form is active</option>
											<option <?php if($condition_rule_condition == 'is_disabled'){ echo 'selected="selected"'; } ?> value="is_disabled">Form is disabled</option>
										</select>	
									</div>
									<div class="condition_date_container" style="<?php echo $condition_date_display; ?>">
										<select name="conditiondate_<?php echo $i; ?>" id="conditiondate_<?php echo $i; ?>" class="element select condition_date" style="width: 120px">
											<option <?php if($condition_rule_condition == 'within_last'){ echo 'selected="selected"'; } ?> value="within_last">Within last</option>
											<option <?php if($condition_rule_condition == 'exactly'){ echo 'selected="selected"'; } ?> value="exactly">Exactly</option>
											<option <?php if($condition_rule_condition == 'before'){ echo 'selected="selected"'; } ?> value="before">Before</option>
											<option <?php if($condition_rule_condition == 'after'){ echo 'selected="selected"'; } ?> value="after">After</option>
											<option <?php if($condition_rule_condition == 'today'){ echo 'selected="selected"'; } ?> value="today">Today</option>
											<option <?php if($condition_rule_condition == 'yesterday'){ echo 'selected="selected"'; } ?> value="yesterday">Yesterday</option>
											<option <?php if($condition_rule_condition == 'this_week'){ echo 'selected="selected"'; } ?> value="this_week">This week</option>
											<option <?php if($condition_rule_condition == 'this_month'){ echo 'selected="selected"'; } ?> value="this_month">This month</option>
											<option <?php if($condition_rule_condition == 'this_year'){ echo 'selected="selected"'; } ?> value="this_year">This year</option>
										</select>
										<?php
											if(!empty($condition_date_display)){
												$condition_rule_keyword = '';
											}
										?>
										<input id="filterdatekeywordpicker_<?php echo $i; ?>" name="filterdatekeywordpicker_<?php echo $i; ?>" type="text" class="element text filter_date_keyword_picker" placeholder="mm/dd/yyyy" value="<?php echo $condition_rule_keyword; ?>" style="margin-left: 5px;width: 120px;<?php echo $condition_date_picker_display; ?>">

										<input id="filterdatekeyword_<?php echo $i; ?>" id="filterdatekeyword_<?php echo $i; ?>" type="text" class="element text filter_date_keyword" value="<?php echo $condition_date_keyword; ?>"  style="margin-left: 5px;width: 50px;<?php echo $condition_date_keyword_display; ?>">
										<select id="filterdateperiod_<?php echo $i; ?>" name="filterdateperiod_<?php echo $i; ?>"  class="element select condition_date_period" style="width: 70px;margin-left: 5px;<?php echo $condition_date_period_display; ?>">
											<option <?php if($condition_date_period == 'day'){ echo 'selected="selected"'; } ?> value="day">days</option>
											<option <?php if($condition_date_period == 'week'){ echo 'selected="selected"'; } ?> value="week">weeks</option>
											<option <?php if($condition_date_period == 'month'){ echo 'selected="selected"'; } ?> value="month">months</option>
											<option <?php if($condition_date_period == 'year'){ echo 'selected="selected"'; } ?> value="year">years</option>										
										</select>
									</div>
									<a href="#" id="deletefilter_<?php echo $i; ?>" class="filter_delete_a"><span class="icon-trash2"></span></a>
								</li>
							<?php } ?>
							
							<li id="li_filter_add" class="filter_add" style="text-align: right;margin-bottom: 0px">
									<a href="#" id="filter_add_a"><span class="icon-plus-circle"></span></a>
							</li>
							
							</ul>			
						</div>
						<input type="hidden" name="folder_id" id="folder_id" value="<?php echo $folder_id; ?>" />
					</div>
					<a href="#" id="button_save_folder" class="button_primary">
						<span class="icon-disk" style="margin-right: 5px"></span>Save Folder
					</a>
					

				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

 
<?php
	$footer_data =<<<EOT
<script type="text/javascript" src="js/axios.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/sweetalert2.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/flatpickr/flatpickr.min.js{$mf_version_tag}"></script>
<script>
	var mf_data = {folder_rules : []};

	{$mf_data_tag}
</script>
<script type="text/javascript" src="js/edit_folder.js{$mf_version_tag}"></script>
EOT;

	require('includes/footer.php'); 
?>