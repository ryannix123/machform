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

	$dbh = mf_connect_db();

	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);

	$selected_form_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

	$user_permissions = mf_get_user_permissions_all($dbh,$_SESSION['mf_user_id']);
	
	if(!empty($_GET['hl'])){
		$highlight_selected_form_id = true;
	}else{
		$highlight_selected_form_id = false;
	}
	
	//refresh the content of ap_form_stats once, during the session
	//particularly reset the "today_entries" values for applicable records
	if(!isset($_SESSION['form_stats_refreshed'])){
		$query = "UPDATE `".MF_TABLE_PREFIX."form_stats` SET today_entries=0 where date(`last_entry_date`) <> curdate()";
		$params = array();
		mf_do_query($query,$params,$dbh);

		$_SESSION['form_stats_refreshed'] = true;
	}

	//determine selected form folder
	if(!empty($_GET['folder'])){
		
		$selected_folder_id = (int) $_GET['folder'];

		//clear any active folder
		$query = "UPDATE `".MF_TABLE_PREFIX."folders` SET folder_selected=0 WHERE user_id=?";
		$params = array($_SESSION['mf_user_id']);
		mf_do_query($query,$params,$dbh);

		//save the folder preference passed from the parameter
		$query = "UPDATE `".MF_TABLE_PREFIX."folders` SET folder_selected=1 WHERE user_id=? AND folder_id=?";
		$params = array($_SESSION['mf_user_id'],$selected_folder_id);
		mf_do_query($query,$params,$dbh);
	}

	$session_id = session_id();
	$jquery_data_code = '';

	$jquery_data_code .= "\$('.manage_forms').data('session_id','{$session_id}');\n";



	//determine the sorting order
	$form_sort_by_complete = 'date_created-desc'; //the default sort order
	
	if(!empty($_GET['sortby'])){
		$form_sort_by_complete = strtolower(trim($_GET['sortby'])); //the user select a new sort order
		
		//save the sort order into ap_form_sorts table
		$query = "delete from ".MF_TABLE_PREFIX."form_sorts where user_id=?";
		$params = array($_SESSION['mf_user_id']);
		mf_do_query($query,$params,$dbh);

		$query = "insert into ".MF_TABLE_PREFIX."form_sorts(user_id,sort_by) values(?,?)";
		$params = array($_SESSION['mf_user_id'],$form_sort_by_complete);
		mf_do_query($query,$params,$dbh);
		
	}else{ //load the previous saved sort order

		$query = "select sort_by from ".MF_TABLE_PREFIX."form_sorts where user_id=?";
		$params = array($_SESSION['mf_user_id']);
	
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		if(!empty($row)){
			$form_sort_by_complete = $row['sort_by'];
		}
	} 
	
	$exploded = array();
	$exploded = explode('-', $form_sort_by_complete);
	$form_sort_by 	 = $exploded[0];
	$form_sort_order = $exploded[1];
			
	if(empty($form_sort_order)){
		$form_sort_order = 'asc';
	}

	//lets hardcode it to make sure, to prevent SQL injection
	if($form_sort_order == 'desc'){
		$form_sort_order = 'desc';
	}else{
		$form_sort_order = 'asc';
	}

	$query_order_by_clause = '';
	
	if($form_sort_by == 'form_title'){
		$query_order_by_clause = " ORDER BY form_list.form_name {$form_sort_order}";
		$sortby_title = 'Form Title';
	}else if($form_sort_by == 'form_tags'){
		$query_order_by_clause = " ORDER BY form_list.form_tags {$form_sort_order}";
		$sortby_title = 'Form Tags';
	}else if($form_sort_by == 'today_entries'){
		$query_order_by_clause = " ORDER BY form_list.today_entries {$form_sort_order}";
		$sortby_title = "Today's Entries";
	}else if($form_sort_by == 'total_entries'){
		$query_order_by_clause = " ORDER BY form_list.total_entries {$form_sort_order}";
		$sortby_title = "Total Entries";
	}else{ //the default date created sort
		$query_order_by_clause = " ORDER BY form_list.form_id {$form_sort_order}";
		$sortby_title = "Date Created";
	}

	//get smart folders preference for this user
	$query = "SELECT folders_pinned FROM `".MF_TABLE_PREFIX."users` WHERE user_id=?";
	$params = array($_SESSION['mf_user_id']);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	$is_folder_pinned = (int) $row['folders_pinned'];

	//get the folder list for this user
	$query = "SELECT 
					folder_id,
					folder_name,
					folder_position,
					folder_selected,
					rule_all_any  
				FROM 
					".MF_TABLE_PREFIX."folders 
			   WHERE 
					user_id=? 
			order by 
					folder_position asc";	
	$params = array($_SESSION['mf_user_id']);
	$sth = mf_do_query($query,$params,$dbh);

	$folder_list_array = array();
	$i=0;
	while($row = mf_do_fetch_result($sth)){
		$folder_list_array[$i]['folder_id']   	  =	$row['folder_id']; 
		$folder_list_array[$i]['folder_selected'] = $row['folder_selected'];
		$folder_list_array[$i]['folder_name'] 	  = htmlspecialchars($row['folder_name']);
		$folder_list_array[$i]['rule_all_any'] 	  = $row['rule_all_any'];

		if(!empty($row['folder_selected'])){
			$selected_folder_name = $folder_list_array[$i]['folder_name'];
			$selected_folder_id   = $folder_list_array[$i]['folder_id'];  
			$selected_folder_rule_all_any = $folder_list_array[$i]['rule_all_any'];  
		}

		$i++;
	}

	//the number of forms being displayed on each page
	$rows_per_page = $mf_settings['form_manager_max_rows'];  
	
	//get the list of the form, put them into array

	//get the active folder data and build the WHERE clause for the selected folder
	if(empty($selected_folder_id)){
		$selected_folder_id = 1; //default selected folder 'All Forms'
	}

	$folder_conditions = array();

	$query  = "SELECT element_name,rule_condition,rule_keyword FROM `".MF_TABLE_PREFIX."folders_conditions` WHERE user_id=? AND folder_id=?";
	$params = array($_SESSION['mf_user_id'],$selected_folder_id);
	$sth    = mf_do_query($query,$params,$dbh);
	
	$i=0;
	while($row = mf_do_fetch_result($sth)){
		$folder_conditions[$i]['element_name'] 	 = $row['element_name'];
		$folder_conditions[$i]['rule_condition'] = $row['rule_condition'];
		$folder_conditions[$i]['rule_keyword'] 	 = $row['rule_keyword'];
		$i++;
	}

	if(!empty($folder_conditions)){
		$query_where_clause = '';
		$query_where_clause_array = array();

		foreach ($folder_conditions as $condition) {
			$rule_condition = $condition['rule_condition'];
			$rule_keyword   = addslashes($condition['rule_keyword']);

			switch ($condition['element_name']) {
				case 'title': 			$element_name = 'form_list.form_name'; break;
				case 'tag':   			$element_name = 'form_list.form_tags'; break;
				case 'created_by': 		$element_name = 'form_list.form_created_by'; break;
				case 'created_date': 	$element_name = 'form_list.form_created_date'; break;
				case 'status': 			$element_name = 'form_list.form_active'; break;
				case 'total_entries': 	$element_name = 'form_list.total_entries'; break;
				case 'today_entries': 	$element_name = 'form_list.today_entries'; break;
				case 'last_entry_date': $element_name = 'form_list.last_entry_date'; break;
			}
			
			//determine where_operand and where_keyword
			if($condition['element_name'] == 'title' || $condition['element_name'] == 'tag'){
				if($rule_condition == 'is'){
					$where_operand = '=';
					$where_keyword = "'{$rule_keyword}'";
				}else if($rule_condition == 'is_not'){
					$where_operand = '<>';
					$where_keyword = "'{$rule_keyword}'";
				}else if($rule_condition == 'begins_with'){
					$where_operand = 'LIKE';
					$where_keyword = "'{$rule_keyword}%'";
				}else if($rule_condition == 'ends_with'){
					$where_operand = 'LIKE';
					$where_keyword = "'%{$rule_keyword}'";
				}else if($rule_condition == 'contains'){
					$where_operand = 'LIKE';
					$where_keyword = "'%{$rule_keyword}%'";
				}else if($rule_condition == 'not_contain'){
					$where_operand = 'NOT LIKE';
					$where_keyword = "'%{$rule_keyword}%'";
				}
			}else if($condition['element_name'] == 'created_by'){
				$where_operand = '=';
				$where_keyword = "'{$rule_keyword}'";
			}else if($condition['element_name'] == 'status'){
				$where_operand = '=';
				if($rule_condition == 'is_active'){
					$where_keyword = "1";
				}else if($rule_condition == 'is_disabled'){
					$where_keyword = "0";
				}
			}else if($condition['element_name'] == 'total_entries' || $condition['element_name'] == 'today_entries'){
				$rule_keyword = (float) $rule_keyword;
				if($rule_condition == 'is'){
					$where_operand = '=';
					$where_keyword = "{$rule_keyword}";
				}else if($rule_condition == 'less_than'){
					$where_operand = '<';
					$where_keyword = "{$rule_keyword}";
				}else if($rule_condition == 'greater_than'){
					$where_operand = '>';
					$where_keyword = "{$rule_keyword}";
				}
			}else if($condition['element_name'] == 'created_date' || $condition['element_name'] == 'last_entry_date'){
				if($rule_condition == 'within_last'){
					$exploded = array();
					$exploded = explode('-',$rule_keyword); //rule_keyword format: xx-day,xx-month,xx-week,xx-year 

					$keyword_number = (int) $exploded[0];

					$keyword_period = $exploded[1];
					if(!in_array($keyword_period, array('day','week','month','year'))){
						$keyword_period = 'day'; //prevent SQL injection
					}

					$where_operand = '>=';
					$where_keyword = "(curdate() - INTERVAL {$keyword_number} {$keyword_period})";
				}else if($rule_condition == 'exactly' || $rule_condition == 'before' || $rule_condition == 'after'){
					$element_name = "date({$element_name})";

					if($rule_condition == 'exactly'){
						$where_operand = '=';
					}else if($rule_condition == 'before'){
						$where_operand = '<';
					}else if($rule_condition == 'after'){
						$where_operand = '>';
					}
					
					$exploded = array();
					$exploded = explode('/',$rule_keyword); //rule_keyword format: mm/dd/yyyy 

					$keyword_date = (int) $exploded[2].'-'.(int) $exploded[0].'-'.(int) $exploded[1];
					$where_keyword = "'{$keyword_date}'";
				}else if($rule_condition == 'today'){
					$element_name = "date({$element_name})";
					$where_operand = '=';
					$where_keyword = "curdate()";
				}else if($rule_condition == 'yesterday'){
					$element_name = "date({$element_name})";
					$where_operand = '=';
					$where_keyword = "(curdate() - INTERVAL 1 DAY)";
				}else if($rule_condition == 'this_week'){
					$where_keyword = "(week({$element_name})=week(curdate()) AND year({$element_name})=year(curdate()))";
					$element_name  = '';
					$where_operand = '';
				}else if($rule_condition == 'this_month'){
					$where_keyword = "(month({$element_name})=month(curdate()) AND year({$element_name})=year(curdate()))";
					$element_name  = '';
					$where_operand = '';
				}else if($rule_condition == 'this_year'){
					$where_keyword = "year({$element_name})=year(curdate())";
					$element_name  = '';
					$where_operand = '';
				}
			}

			$query_where_clause_array[] = "{$element_name} {$where_operand} {$where_keyword}";
		}

		if($selected_folder_rule_all_any == 'all'){
			$query_where_clause = implode(' AND ', $query_where_clause_array);
		}else{
			$query_where_clause = implode(' OR ', $query_where_clause_array);
		}
	}else{
		$query_where_clause = ' TRUE '; //default condition
	}

	$query = "SELECT * FROM
							(SELECT 
									A.form_id,
									A.form_name,
									ifnull(A.form_tags,'') form_tags,
									A.form_active,
									A.form_disabled_message,
									A.form_theme_id,
									A.form_approval_enable,
									A.form_created_by,
									A.form_created_date,
									B.total_entries,
									B.today_entries,
									B.last_entry_date    
								FROM 
									`".MF_TABLE_PREFIX."forms` A LEFT JOIN `".MF_TABLE_PREFIX."form_stats` B
								  ON 
								  	A.form_id=B.form_id
							   WHERE 
							   		A.form_active IN(0,1)
							) AS form_list
					  WHERE 
							{$query_where_clause} 
							{$query_order_by_clause}";
			
	$params = array();
	$sth = mf_do_query($query,$params,$dbh);
	
	$form_list_array = array();
	$i=0;
	$current_date = date("Y-m-d").' 00:00:00';

	while($row = mf_do_fetch_result($sth)){
		
		//check user permission to this form
		if(empty($_SESSION['mf_user_privileges']['priv_administer']) && empty($user_permissions[$row['form_id']])){
			continue;
		}
		
		$form_list_array[$i]['form_id']   	  = $row['form_id'];

		$row['form_name'] = mf_trim_max_length($row['form_name'],90);

		if(!empty($row['form_name'])){		
			$form_list_array[$i]['form_name'] = $row['form_name'];
		}else{
			$form_list_array[$i]['form_name'] = '-Untitled Form- (#'.$row['form_id'].')';
		}	
		
		$form_list_array[$i]['form_active']   			= $row['form_active'];
		$form_list_array[$i]['form_disabled_message']   = $row['form_disabled_message'];
		$form_list_array[$i]['form_theme_id'] 			= $row['form_theme_id'];
		$form_list_array[$i]['form_approval_enable']   	= (int) $row['form_approval_enable'];
		$form_list_array[$i]['today_entries']  			= $row['today_entries'];
		$form_list_array[$i]['latest_entry'] 			= mf_relative_date($row['last_entry_date']);
		$form_list_array[$i]['total_entries']  			= $row['total_entries'];
		$form_list_array[$i]['form_created_by']  		= $row['form_created_by'];
		
		$form_disabled_message = json_encode($row['form_disabled_message']);
		$jquery_data_code .= "\$('#liform_{$row['form_id']}').data('form_disabled_message',{$form_disabled_message});\n";

		
		//get form tags and split them into array
		if(!empty($row['form_tags'])){
			$form_tags_array = explode(',',$row['form_tags']);
			array_walk($form_tags_array, 'mf_trim_value');
			$form_list_array[$i]['form_tags'] = $form_tags_array;
		}
		
		$i++;
	}
	
	
	if(empty($selected_form_id) && !empty($form_list_array)){ //if there is no preference for which form being displayed, display the first form
		$selected_form_id = $form_list_array[0]['form_id'];
	}

	$selected_page_number = 1;
	
	//build pagination markup
	$total_rows = count($form_list_array);
	$total_page = ceil($total_rows / $rows_per_page);
	
	$total_active_forms = $total_rows;
	$pagination_markup = '';
	
	if($total_page > 1){
		
		$start_form_index = 0;
		$pagination_markup = '<ul id="mf_pagination" class="pages green small">'."\n";
		
		for($i=1;$i<=$total_page;$i++){
			
			//attach the data code into each pagination button
			$end_form_index = $start_form_index + $rows_per_page;
			$liform_ids_array = array();
			
			for ($j=$start_form_index;$j<$end_form_index;$j++) {
				if(!empty($form_list_array[$j]['form_id'])){
					$liform_ids_array[] = '#liform_'.$form_list_array[$j]['form_id'];
					
					//put the page number into the array
					$form_list_array[$j]['page_number'] = $i;
					
					//we need to determine on which page the selected_form_id being displayed
					if($selected_form_id == $form_list_array[$j]['form_id']){
						$selected_page_number = $i;
					}
				}
			}
			
			$liform_ids_joined = implode(',',$liform_ids_array);
			$start_form_index = $end_form_index;
			
			$jquery_data_code .= "\$('#pagebtn_{$i}').data('liform_list','{$liform_ids_joined}');\n";
			
			
			if($i == $selected_page_number){
				if($selected_page_number > 1){
					$pagination_markup = str_replace('current_page','',$pagination_markup);
				}
				
				$pagination_markup .= '<li id="pagebtn_'.$i.'" class="page current_page">'.$i.'</li>'."\n";
			}else{
				$pagination_markup .= '<li id="pagebtn_'.$i.'" class="page">'.$i.'</li>'."\n";
			}
			
		}
		
		$pagination_markup .= '</ul>';
	}else{
		//if there is only 1 page, set the page_number property for each form to 1
		foreach ($form_list_array as $key=>$value){
			$form_list_array[$key]['page_number'] = 1;
		}
	}

	//get the available tags
	$query = "select form_tags from ".MF_TABLE_PREFIX."forms where form_tags is not null and form_tags <> ''";
	$params = array();
	
	$sth = mf_do_query($query,$params,$dbh);
	$raw_tags = array();
	while($row = mf_do_fetch_result($sth)){
		$raw_tags = array_merge(explode(',',$row['form_tags']),$raw_tags);
	}

	$all_tagnames = array_unique($raw_tags);
	sort($all_tagnames);
	
	$jquery_data_code .= "\$('#dialog-enter-tagname-input').data('available_tags',".json_encode($all_tagnames).");\n";
	
	//get the available custom themes
	if(!empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$query = "SELECT theme_id,theme_name FROM ".MF_TABLE_PREFIX."form_themes WHERE theme_built_in=0 and status=1 ORDER BY theme_name ASC";
		$params = array();
	}else{
		$query = "SELECT 
						theme_id,
						theme_name 
					FROM 
						".MF_TABLE_PREFIX."form_themes 
				   WHERE 
					   	(theme_built_in=0 and status=1 and user_id=?) OR
					   	(theme_built_in=0 and status=1 and user_id <> ? and theme_is_private=0)
				ORDER BY 
						theme_name ASC";
		$params = array($_SESSION['mf_user_id'],$_SESSION['mf_user_id']);
	}	
	
	$sth = mf_do_query($query,$params,$dbh);

	$theme_list_array = array();
	while($row = mf_do_fetch_result($sth)){
		$theme_list_array[$row['theme_id']] = htmlspecialchars($row['theme_name']);
	}

	//get built-in themes
	$query = "SELECT theme_id,theme_name FROM ".MF_TABLE_PREFIX."form_themes WHERE theme_built_in=1 and status=1 ORDER BY theme_name ASC";
		
	$params = array();
	$sth = mf_do_query($query,$params,$dbh);

	$theme_builtin_list_array = array();
	while($row = mf_do_fetch_result($sth)){
		$theme_builtin_list_array[$row['theme_id']] = htmlspecialchars($row['theme_name']);
	}

	
		$header_data =<<<EOT
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="css/pagination_classic.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="css/dropui.css{$mf_version_tag}" rel="stylesheet" />
<style>
.dropui-menu li a{
 	padding: 2px 0 2px 27px;
 	font-size: 115%;
}
.dropui .dropui-tab{
 	font-size: 95%;
}
.uploadifive-queue-item { border: none !important; }
#uploadifive_upload_mf_form_import_file{z-index: 1999 !important;}
</style>
EOT;

		
		
		
	
	$current_nav_tab = 'manage_forms';
	
	require('includes/header.php'); 
	
?>

		<div id="content" class="full" data-csrftoken="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>">
			<div class="post manage_forms">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2>Form Manager 
								<?php
									if(!empty($selected_folder_id) && $selected_folder_id != 1){
										echo "<span id=\"active_folder_name\">{$selected_folder_name} ({$total_active_forms})</span>";
									}
								?>
							</h2>
							<p>Create, edit and manage your forms</p>
						</div>
						
						<?php if(!empty($_SESSION['mf_user_privileges']['priv_new_forms'])){ ?>
						<div style="float: right;margin-right: 0px">
								<a href="#" title="Import Form Template" id="button_import_form" class="button_primary">
									<span class="icon-exit-down"></span>
								</a>
								<a href="edit_form.php" title="Create New Form" id="button_create_form" class="button_primary">
									<span class="icon-file-empty" style="margin-right: 5px"></span> Create New Form
								</a>
						</div>
						<?php } ?>

						<div style="clear: both; height: 1px"></div>
					</div>
				</div>
				
				<?php mf_show_message(); ?>
				
				<div class="content_body">
				<div class="content_body_sidebar <?php if($is_folder_pinned){ echo 'filter_list_expand_sidebar'; } ?>">
					<div id="smart_folder_container">
						<ul id="smart_folder_list">
							<li class="smart_folder_header">Smart Folders <a title="Manage Folders" class="manage_folders" href="manage_folders.php"><span class="icon-cog"></span></a><a id="pin_folders" title="Pin/Unpin Smart Folders" class="manage_folders <?php if($is_folder_pinned){ echo 'pinned'; } ?>" href="javascript:toggle_pin_folders()"><span class="icon-pushpin"></span></a></li>
							
							<?php
								if(!empty($folder_list_array)){
									foreach ($folder_list_array as $folder) {
										if(!empty($folder['folder_selected'])){
											$folder_selected_attr = 'class="selected_folder"';
										}else{
											$folder_selected_attr = '';
										}
										echo "<li {$folder_selected_attr}><a href=\"manage_forms.php?folder={$folder['folder_id']}\"><span class=\"icon-folder\"></span> {$folder['folder_name']}</a></li>";
									}
								}else{
									echo "<li style=\"text-align: center;font-style: italic;\">- you have no folder -</li>";
								}
							?>
							
							<li class="smart_folder_new"><a href="edit_folder.php" title="Create New Folder"><span class="icon-folder-plus"></span> New Folder</a></li>
						</ul>
					</div>
				</div>
				<div class="content_body_main <?php if($is_folder_pinned){ echo 'filter_list_expand_main'; } ?>">
					<?php if(!empty($form_list_array) || (empty($form_list_array) && !empty($selected_folder_id) && $selected_folder_id != 1 )){ ?>	
						
						<div id="mf_top_pane">
							<div id="mf_filters_toggle">
								<a id="mf_filters_toggle_button" href="#" title="Show/Hide Folders">
									<span class="icon-menu2"></span>
								</a>
							</div>
							<div id="mf_filters_toggle2" style="display: none;">
								<a id="mf_filters_toggle2_button" href="javascript:;" title="Show/Hide Folders">
									<span class="icon-menu2"></span>
								</a>
							</div>
							<template id="mf_filters_toggle2_content" style="display: none">
								<ul id="mf_filters_toggle2_list">
									<li class="sub_separator">Smart Folders</li>
									<?php
										if(!empty($folder_list_array)){
											foreach ($folder_list_array as $folder) {
												if(!empty($folder['folder_selected'])){
													$folder_selected_span = '<span class="icon-checkmark-circle" style="margin-left: 5px"></span>';
												}else{
													$folder_selected_span = '';
												}
												echo "<li><a href=\"manage_forms.php?folder={$folder['folder_id']}\"><span style=\"margin-right: 5px\" class=\"icon-folder\"></span> {$folder['folder_name']} {$folder_selected_span}</a></li>";
											}
										}else{
											echo "<li style=\"text-align: center;font-style: italic;\">- you have no folder -</li>";
										}
									?>
								</ul>
							</template>
							<div id="mf_search_pane">
								<div id="mf_search_box" class="">
									<input name="filter_form_input" id="filter_form_input" type="text" class="text" value="find form..."/>
									<div id="mf_search_title" class="mf_pane_selected"><a href="#">&#8674; form title</a></div>
									<div id="mf_search_tag"><a href="#">form tags</a></div>
								</div>
							</div>
							<div id="mf_sort_pane">
								<a id="mf_sort_pane_button" href="javascript:;" title="Sort Forms">
									<span class="icon-sort-amount-asc"></span>
								</a>
							</div>
							<template id="mf_sort_pane_content" style="display: none">
								<ul id="mf_sort_pane_list">
									<li class="sub_separator">Sort Ascending</li>
									<li><a id="sort_date_created_link" href="manage_forms.php?sortby=date_created-asc">Date Created <?php if($form_sort_by_complete == 'date_created-asc'){ echo '<span class="icon-checkmark-circle" style="margin-left: 5px"></span>'; } ?></a></li>
									<li><a id="sort_form_title_link" href="manage_forms.php?sortby=form_title-asc">Form Title <?php if($form_sort_by_complete == 'form_title-asc'){ echo '<span class="icon-checkmark-circle" style="margin-left: 5px"></span>'; } ?></a></li>
									<li><a id="sort_form_tag_link" href="manage_forms.php?sortby=form_tags-asc">Form Tags <?php if($form_sort_by_complete == 'form_tags-asc'){ echo '<span class="icon-checkmark-circle" style="margin-left: 5px"></span>'; } ?></a></li>
									<li><a id="sort_today_entries_link" href="manage_forms.php?sortby=today_entries-asc">Today's Entries <?php if($form_sort_by_complete == 'today_entries-asc'){ echo '<span class="icon-checkmark-circle" style="margin-left: 5px"></span>'; } ?></a></li>
									<li><a id="sort_total_entries_link" href="manage_forms.php?sortby=total_entries-asc">Total Entries <?php if($form_sort_by_complete == 'total_entries-asc'){ echo '<span class="icon-checkmark-circle" style="margin-left: 5px"></span>'; } ?></a></li>
									<li class="sub_separator">Sort Descending</li>
									<li><a id="sort_date_created_link" href="manage_forms.php?sortby=date_created-desc">Date Created <?php if($form_sort_by_complete == 'date_created-desc'){ echo '<span class="icon-checkmark-circle" style="margin-left: 5px"></span>'; } ?></a></li>
									<li><a id="sort_form_title_link" href="manage_forms.php?sortby=form_title-desc">Form Title <?php if($form_sort_by_complete == 'form_title-desc'){ echo '<span class="icon-checkmark-circle" style="margin-left: 5px"></span>'; } ?></a></li>
									<li><a id="sort_form_tag_link" href="manage_forms.php?sortby=form_tags-desc">Form Tags <?php if($form_sort_by_complete == 'form_tags-desc'){ echo '<span class="icon-checkmark-circle" style="margin-left: 5px"></span>'; } ?></a></li>
									<li><a id="sort_today_entries_link" href="manage_forms.php?sortby=today_entries-desc">Today's Entries <?php if($form_sort_by_complete == 'today_entries-desc'){ echo '<span class="icon-checkmark-circle" style="margin-left: 5px"></span>'; } ?></a></li>
									<li><a id="sort_total_entries_link" href="manage_forms.php?sortby=total_entries-desc">Total Entries <?php if($form_sort_by_complete == 'total_entries-desc'){ echo '<span class="icon-checkmark-circle" style="margin-left: 5px"></span>'; } ?></a></li>
								</ul>
							</template>
						</div>
						<div id="filtered_result_box">
							<div style="float: left">Filtered Results for &#8674; <span class="highlight"></span></div>
							<div id="filtered_result_box_right">
								<ul>
									<li><a href="#" id="mf_filter_reset" title="Clear filter"><span class="icon-cancel-circle"></span></a></li>
									<li id="filtered_result_total">Found 0 forms</li>
								</ul>
							</div>
						</div>
						<div id="filtered_result_none" <?php if(empty($form_list_array) && !empty($selected_folder_id)){ echo 'style="display: block"';  } ?>>
							There are no forms in this folder
							<br/><a href="manage_forms.php?folder=1" class="blue_dotted" style="font-size: 14px">view all forms</a>
						</div>
						<ul id="mf_form_list">
						<?php 
							
							$row_num = 1;
							
							foreach ($form_list_array as $form_data){
								$form_name   	 = htmlspecialchars($form_data['form_name']);
								$form_id   	 	 = $form_data['form_id'];
								$today_entries 	 = $form_data['today_entries'];
								$total_entries 	 = $form_data['total_entries'];
								$latest_entry 	 = $form_data['latest_entry'];
								$form_created_by = $form_data['form_created_by'];
								$theme_id		 = (int) $form_data['form_theme_id'];
								
								if(!empty($form_data['form_tags'])){
									$form_tags_array = array_reverse($form_data['form_tags']);
								}else{
									$form_tags_array = array();
								}
								
								
								$form_class = array();
								$form_class_tag = '';
								
								if($form_id == $selected_form_id){
									$form_class[] = 'form_selected';
								}
								
								if(empty($form_data['form_active'])){
									$form_class[] = 'form_inactive';
								}
								
								if($selected_page_number == $form_data['page_number']){
									$form_class[] = 'form_visible';
								}
								
								$form_class_joined = implode(' ',$form_class);
								$form_class_tag	   = 'class="'.$form_class_joined.'"';
								
								
						?>
							
							<li data-theme_id="<?php echo $theme_id; ?>" id="liform_<?php echo $form_id; ?>" <?php echo $form_class_tag; ?>>
								
								<div class="middle_form_bar">
									<h3><?php echo $form_name; ?></h3>
									<div class="form_meta">
										
										<?php if($form_sort_by == 'total_entries' && !empty($total_entries)){ ?>
										<div class="form_stat form_stat_total" title="<?php echo $today_entries." entries today. Latest entry ".$latest_entry."."; ?>">
											<div class="form_stat_count"><?php echo $total_entries; ?></div>
											<div class="form_stat_msg">total</div>
										</div>
										<?php }else if($form_sort_by != 'total_entries' && !empty($today_entries)){ ?>
										<div class="form_stat" title="<?php echo $today_entries." entries today. Latest entry ".$latest_entry."."; ?>">
											<div class="form_stat_count"><?php echo $today_entries; ?></div>
											<div class="form_stat_msg">today</div>
										</div>
										<?php } ?>
										
										<div class="form_actions">
											<a class="form_actions_toggle" data-formid="<?php echo $form_id; ?>" id="form_action_<?php echo $form_id; ?>" href="javascript:;"><span class="icon-cog"></span></a>
										</div>
										<div id="action_toggle_content_<?php echo $form_id; ?>" style="display: none">
											<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_permissions[$form_id]['edit_form'])){ ?>
											<div class="form_action_item mf_link_delete"><a href="#"><span class="icon-trash2"></span> Delete</a></div>
											<?php } ?>

											<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($_SESSION['mf_user_privileges']['priv_new_forms'])){ ?>
											<div class="form_action_item mf_link_duplicate"><a href="#"><span class="icon-copy1"></span> Duplicate</a></div>
											<?php } ?>

											<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_permissions[$form_id]['edit_form'])){ ?>
											<div class="form_action_item mf_link_disable">
												<?php 
													if(empty($form_data['form_active'])){
														echo '<a href="#"><span class="icon-play-circle"></span> Enable</a>';	
													}else{
														echo '<a href="#"><span class="icon-pause-circle"></span> Disable</a>';	
													}
												?>
											</div>
											<?php } ?>

											<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_permissions[$form_id]['edit_form'])){ ?>
											<div class="form_action_item mf_link_info"><a title="View Form Info" href="form_info.php?id=<?php echo $form_id; ?>"><span class="icon-file-charts"></span> Info</a></div>
											<?php } ?>

											<div class="form_action_item mf_link_export"><a title="Export Form Template" class="exportform" id="exportform_<?php echo $form_id; ?>" href="#"><span class="icon-exit-up"></span> Export</a></div>
										</div>

										<div class="form_tag">
											<ul class="form_tag_list">
												
												<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_permissions[$form_id]['edit_form'])){ ?>
												<li class="form_tag_list_icon">
													<a title="Add a Tag Name" class="addtag" id="addtag_<?php echo $form_id; ?>" href="#"><span class="icon-tag"></span></a>
												</li>
												<?php } ?>

												<?php 	
													if(!empty($form_tags_array)){
														foreach ($form_tags_array as $tagname){
															echo "<li>".htmlspecialchars($tagname)." <a class=\"removetag\" href=\"#\" title=\"Remove this tag.\"><span class=\"icon-cancel-circle\"></span></a></li>";
														}
													}
												?>
												
											</ul>
										</div>
									</div>
									<div style="height: 0px; clear: both;"></div>
								</div>
								<div class="bottom_form_bar">
									
									<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_permissions[$form_id]['edit_entries']) || !empty($user_permissions[$form_id]['view_entries'])){ ?>
									<div class="form_option fo_entries">
										<a href="manage_entries.php?id=<?php echo $form_id; ?>"><span class="icon-server"></span>Entries</a>
									</div>
									<?php } ?>

									<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_permissions[$form_id]['edit_form'])){ ?>
									<div class="form_option">
										<a href="edit_form.php?id=<?php echo $form_id; ?>"><span class="icon-pencil3"></span>Edit</a>
									</div>
									<?php } ?>

									<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_permissions[$form_id]['edit_form'])){ ?>
									<div class="form_option_separator"></div>

									<div class="form_option option_expandable">
										<a class="mf_link_theme" href="#" title="Theme"><span class="icon-palette1"></span><span class="option_text">Theme</span></a>
									</div>

									<div class="form_option option_expandable fo_notifications">
										<a href="notification_settings.php?id=<?php echo $form_id; ?>" title="Notifications"><span class="icon-envelope-open"></span><span class="option_text">Notifications</span></a>
									</div>

									<div class="form_option option_expandable">
										<a href="embed_code.php?id=<?php echo $form_id; ?>" title="Code"><span class="icon-paste1"></span><span class="option_text">Code</span></a>
									</div>
									
									<div class="form_option option_expandable">
										<a href="payment_settings.php?id=<?php echo $form_id; ?>" title="Payment"><span class="icon-cart1"></span><span class="option_text">Payment</span></a>
									</div>

									<div class="form_option option_expandable">
										<a href="logic_settings.php?id=<?php echo $form_id; ?>" title="Logic"><span class="icon-arrows-split"></span><span class="option_text">Logic</span></a>
									</div>

									<div class="form_option option_expandable">
										<a href="integration_settings.php?id=<?php echo $form_id; ?>" title="Integrations"><span class="icon-puzzle1"></span><span class="option_text">Integrations</span></a>
									</div>
									<?php } ?>

									<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_permissions[$form_id]['edit_report'])){ ?>
									<div class="form_option option_expandable">
										<a href="manage_report.php?id=<?php echo $form_id; ?>" title="Report"><span class="icon-chart-growth"></span><span class="option_text">Report</span></a>
									</div>
									<?php } ?>

									<div class="form_option option_expandable fo_view">
										<a target="_blank" href="view.php?id=<?php echo $form_id; ?>" title="View"><span class="icon-magnifier"></span><span class="option_text">View</span></a>
									</div>

									<?php if(!empty($form_data['form_approval_enable']) && (!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_permissions[$form_id]['edit_form']))){ ?>
									<div class="form_option option_expandable">
										<a href="approval_settings.php?id=<?php echo $form_id; ?>" title="Approval"><span class="icon-group-work"></span><span class="option_text">Approval</span></a>
									</div>
									<?php } ?>

								</div>
								
								<div style="height: 0px; clear: both;"></div>
							</li>
							
						<?php 
								$row_num++; 
							}//end foreach $form_list_array 
						?>
							
						</ul>
						
						<div id="result_set_show_more">
							<a href="#">Show More Results...</a>
						</div>
						
						<!-- start pagination -->
						
						<?php echo $pagination_markup; ?>
						
						<!-- end pagination -->
						<?php }else{ ?>
								
								<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($_SESSION['mf_user_privileges']['priv_new_forms'])){ ?>
								
								<div id="form_manager_empty">
									<img src="images/icons/arrow_up.png" />
									<h2>Welcome!</h2>
									<h3>You have no forms yet. Go create one by clicking the button above.</h3>
								</div>
								
								<?php } else{ ?>

								<div id="form_manager_empty">
									<h2 style="padding-top: 135px">Welcome!</h2>
									<h3>You currently have no access to any forms.</h3>
								</div>

								<?php } ?>	
						
						<?php } ?>
						
						
						<!-- start dialog boxes -->
						<div id="dialog-enter-tagname" title="Enter a Tag Name" class="buttons" style="display: none"> 
							<form id="dialog-enter-tagname-form" class="dialog-form" style="padding-left: 10px;padding-bottom: 10px">				
								<ul>
									<li>
										<div>
										<input type="text" value="" class="text" name="dialog-enter-tagname-input" id="dialog-enter-tagname-input" />
										<div class="infomessage"><span class="icon-info" style="color: #699a22;vertical-align: bottom;font-size: 16px;margin-righ: 5px"></span> Tag name is optional. Use it when you have many forms, to group them into categories.</div>
										</div> 
									</li>
								</ul>
							</form>
						</div>
						<div id="dialog-import-form" title="Import Form Template" class="buttons" style="display: none"> 
							<form id="dialog-import-form" class="dialog-form" style="padding-left: 10px;padding-bottom: 10px">				
								<ul>
									<li>
										<div style="margin-top: 15px; margin-bottom: 10px">
										<input id="mf_form_import_file" name="mf_form_import_file" class="element file" type="file" />
										<div class="infomessage" style="padding-top: 10px"><span class="icon-info" style="color: #699a22;vertical-align: bottom;font-size: 16px;margin-righ: 5px"></span> Upload form file with <strong>*.json</strong> extension only.</div>
										</div> 
									</li>
								</ul>
							</form>
						</div>
						<div id="dialog-form-import-success" title="Success! Import completed" class="buttons" style="display: none">
							<span class="icon-checkmark-circle"></span> 
							<p>
								<strong>The following form has been imported:</strong><br/>
								<a id="form-imported-link" target="_blank" style="color: #529214;font-size: 120%;border: none;background: none;float: none" href="#">x</a>
							</p>	
						</div>
						<div id="dialog-warning" title="Error! Import failed" class="buttons" style="display: none">
							<span class="icon-bubble-notification"></span>
							<p id="dialog-warning-msg" style="margin-bottom: 20px">
								The form file seems to be corrupted.<br/>Please try again with another file.
							</p>
						</div>
						<div id="dialog-confirm-form-delete" title="Are you sure you want to delete this form?" class="buttons" style="display: none">
							<span class="icon-bubble-notification"></span>
							<p>
								This action cannot be undone.<br/>
								<strong>All data and files collected by <span id="confirm_form_delete_name">this form</span> will be deleted as well.</strong><br/><br/>
								
							</p>
							
						</div>
						<div id="dialog-change-theme" title="Select a Theme" class="buttons" style="display: none"> 
							<form id="dialog-change-theme-form" class="dialog-form" style="padding-left: 10px;padding-bottom: 10px">				
								<ul>
									<li>
										<div>
											<select class="select full" id="dialog-change-theme-input" name="dialog-change-theme-input">
											<?php if(!empty($theme_list_array) || !empty($_SESSION['mf_user_privileges']['priv_new_themes'])){ ?>	
												<optgroup label="Your Themes">
													<?php 
														if(!empty($theme_list_array)){
															foreach ($theme_list_array as $theme_id=>$theme_name){
																echo "<option value=\"{$theme_id}\">{$theme_name}</option>";
															}
														}
													?>
													<?php if(!empty($_SESSION['mf_user_privileges']['priv_new_themes'])){ ?>
														<option value="new">&#8674; Create New Theme!</option>
													<?php } ?>
												</optgroup>
											<?php } ?>
												<optgroup label="Built-in Themes">
													<option value="0">White</option>
													<?php 
														if(!empty($theme_builtin_list_array)){
															foreach ($theme_builtin_list_array as $theme_id=>$theme_name){
																echo "<option value=\"{$theme_id}\">{$theme_name}</option>";
															}
														}
													?>
												</optgroup>
											</select>
										</div> 
									</li>
								</ul>
							</form>
						</div>
						<div id="dialog-disabled-message" title="Please Enter a Message" class="buttons" style="display: none"> 
							<form class="dialog-form">				
								<ul>
									<li>
										<label for="dialog-disabled-message-input" class="description">Your form will be closed and the message below will be displayed:</label>
										<div>
											<textarea cols="90" rows="8" class="element textarea medium" name="dialog-disabled-message-input" id="dialog-disabled-message-input"></textarea>
										</div>
									</li>
								</ul>
							</form>
						</div>
						<!-- end dialog boxes -->
				</div>
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->


 
<?php

	if($highlight_selected_form_id == true){
		$highlight_selected_form_id = $selected_form_id;
	}else{
		$highlight_selected_form_id = 0;
	}

	$footer_data =<<< EOT
<script type="text/javascript">
	var selected_form_id_highlight = {$highlight_selected_form_id};
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
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.autocomplete.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.effects.core.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.effects.scale.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.effects.highlight.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery.highlight.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/uploadifive/jquery.uploadifive.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/form_manager.js{$mf_version_tag}"></script>
EOT;

	require('includes/footer.php');
	
?>