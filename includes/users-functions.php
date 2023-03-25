<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	
	//display a table which contain all users data
	function mf_display_users_table($dbh,$options){

		$max_data_length = 80; //maximum length of column content
		$pageno 	   = $options['page_number'];
		$rows_per_page = $options['rows_per_page'];
		$sort_element  = $options['sort_element'];
		$sort_order	   = $options['sort_order'];
		$filter_data   = $options['filter_data'];
		$filter_type   = $options['filter_type'];

		if(empty($sort_element)){ //set the default sorting order
			$sort_element = 'user_id';
			$sort_order	  = 'desc';
		}else{
			//make sure the sort order string is valid and prevent SQL injection
			if($sort_order == 'asc'){
				$sort_order = 'ASC';
			}else{
				$sort_order = 'DESC';
			}
		}


		//set column properties for basic fields
		$column_name_lookup['user_fullname']	= 'Name';
		$column_name_lookup['user_email']		= 'Email';
		$column_name_lookup['priv_administer']	= 'Admin Privileges';
		$column_name_lookup['status']			= 'Status';
		
		$column_type_lookup['user_fullname']	= 'text';
		$column_type_lookup['user_email']		= 'text';
		$column_type_lookup['priv_administer'] 	= 'admin';
		$column_type_lookup['status']			= 'status';
		
		
		$column_prefs = array('user_fullname','user_email','priv_administer','status');
		
		
		//determine column labels
		//the first 2 columns are always id and row_num
		$column_labels = array();

		$column_labels[] = 'mf_id';
		$column_labels[] = 'mf_row_num';
		
		foreach($column_prefs as $column_name){
			$column_labels[] = $column_name_lookup[$column_name];
		}

		//get the entries from ap_form_x table and store it into array
		$column_prefs_joined = '`'.implode("`,`",$column_prefs).'`';
		
		//check for filter data and build the filter query
		if(!empty($filter_data)){

			if($filter_type == 'all'){
				$condition_type = ' AND ';
			}else{
				$condition_type = ' OR ';
			}

			$where_clause_array = array();

			foreach ($filter_data as $value) {
				$element_name 	  = $value['element_name'];
				$filter_condition = $value['filter_condition'];
				$filter_keyword   = addslashes($value['filter_keyword']);

				$filter_element_type = $column_type_lookup[$element_name] ?? '';

				$temp = explode('_', $element_name);
				$element_id = $temp[1];
				
				
				if($filter_condition == 'is'){
						$where_operand = '=';
						$where_keyword = "'{$filter_keyword}'";
				}else if($filter_condition == 'is_not'){
						$where_operand = '<>';
						$where_keyword = "'{$filter_keyword}'";
				}else if($filter_condition == 'begins_with'){
						$where_operand = 'LIKE';
						$where_keyword = "'{$filter_keyword}%'";
				}else if($filter_condition == 'ends_with'){
						$where_operand = 'LIKE';
						$where_keyword = "'%{$filter_keyword}'";
				}else if($filter_condition == 'contains'){
						$where_operand = 'LIKE';
						$where_keyword = "'%{$filter_keyword}%'";
				}else if($filter_condition == 'not_contain'){
						$where_operand = 'NOT LIKE';
						$where_keyword = "'%{$filter_keyword}%'";
				}else if($filter_condition == 'less_than' || $filter_condition == 'is_before'){
						$where_operand = '<';
						$where_keyword = "'{$filter_keyword}'";
				}else if($filter_condition == 'greater_than' || $filter_condition == 'is_after'){
						$where_operand = '>';
						$where_keyword = "'{$filter_keyword}'";
				}else if($filter_condition == 'is_admin'){
						$where_operand = '=';
						$where_keyword = "'1'";
				}else if($filter_condition == 'is_not_admin'){
						$where_operand = '=';
						$where_keyword = "'0'";
				}else if($filter_condition == 'is_active'){
						$where_operand = '=';
						$where_keyword = "'1'";
				}else if($filter_condition == 'is_suspended'){
						$where_operand = '=';
						$where_keyword = "'2'";
				}
		 			
				$where_clause_array[] = "{$element_name} {$where_operand} {$where_keyword}"; 
				
			}
			
			$where_clause = implode($condition_type, $where_clause_array);
			
			if(empty($where_clause)){
				$where_clause = "WHERE `status` > 0";
			}else{
				$where_clause = "WHERE ({$where_clause}) AND `status` > 0";
			}
			
						
		}else{
			$where_clause = "WHERE `status` > 0";
		}


		/** pagination **/
		//identify how many database rows are available
		$query = "select count(*) total_row from (select 
						`user_id`,
						`user_id` as `row_num`,
						{$column_prefs_joined} 
				    from 
				    	".MF_TABLE_PREFIX."users A 
				    	{$where_clause} ) B ";
		$params = array();
			
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		$numrows   = $row['total_row'];
		$lastpage  = ceil($numrows/$rows_per_page);
							
							
		//ensure that $pageno is within range
		//this code checks that the value of $pageno is an integer between 1 and $lastpage
		$pageno = (int) $pageno;
							
		if ($pageno < 1) { 
		   $pageno = 1;
		}
		elseif ($pageno > $lastpage){
			$pageno = $lastpage;
		}
							
		//construct the LIMIT clause for the sql SELECT statement
		$limit = '';
		if(!empty($numrows)){
			$limit = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;
		}
		/** end pagination **/

		$query = "select 
						`user_id`,
						`user_id` as `row_num`,
						`user_fullname`,
						`user_email`,
						if(`priv_administer`=1,'Administrator','') `priv_administer`,
						if(`status`=1,'Active','Suspended') `status`
				    from 
				    	".MF_TABLE_PREFIX."users A 
				    	{$where_clause} 
				order by 
						`{$sort_element}` {$sort_order}
						{$limit}";
		
		$params = array();
		$sth = mf_do_query($query,$params,$dbh);
		$i=0;
		
		//prepend "id" and "row_num" into the column preferences
		array_unshift($column_prefs,"user_id","row_num");
		
		while($row = mf_do_fetch_result($sth)){
			$j=0;
			foreach($column_prefs as $column_name){
				$form_data[$i][$j] = '';
				$form_data[$i][$j] = htmlspecialchars(str_replace("\r","",str_replace("\n"," ",$row[$column_name])),ENT_QUOTES);
				
				$j++;
			}
			$i++;
		}
		
		//generate table markup for the entries
		$table_header_markup = '<thead><tr>'."\n";

		foreach($column_labels as $label_name){
			if($label_name == 'mf_id'){
				$table_header_markup .= '<th class="me_action" scope="col"><input type="checkbox" value="1" name="col_select" id="col_select" /></th>'."\n";
			}else if($label_name == 'mf_row_num'){
				$table_header_markup .= '<th class="me_number" scope="col">#</th>'."\n";
			}else{
				$table_header_markup .= '<th scope="col"><div title="'.$label_name.'">'.$label_name.'</div></th>'."\n";	
			}
			
		}

		$table_header_markup .= '</tr></thead>'."\n";

		$table_body_markup = '<tbody>'."\n";

		$toggle = false;
		
		$first_row_number = ($pageno -1) * $rows_per_page + 1;
		$last_row_number  = $first_row_number;

		if(!empty($form_data)){
			foreach($form_data as $row_data){
				if($toggle){
					$toggle = false;
					$row_style = 'class="alt"';
				}else{
					$toggle = true;
					$row_style = '';
				}

				$table_body_markup .= "<tr id=\"row_{$row_data[0]}\" {$row_style}>";
				foreach ($row_data as $key=>$column_data){
					
					if($row_data[0] == 1 && $column_data == 'Administrator'){
						$column_data = 'Administrator (Main)';
					}

					if($key == 0){ //this is "id" column
						$table_body_markup .= '<td class="me_action"><div><input type="checkbox" id="entry_'.$column_data.'" name="entry_'.$column_data.'" value="1" /></div></td>'."\n";
					}elseif ($key == 1){ //this is "row_num" column
						$table_body_markup .= '<td class="me_number"><div>'.$column_data.'</div></td>'."\n";
					}elseif ($key == 5){ //this is "Status" column
						if($column_data == 'Suspended'){
							$table_body_markup .= '<td class="mu_suspended"><div>'.$column_data.'</div></td>'."\n";
						}else{
							$table_body_markup .= '<td><div>'.$column_data.'</div></td>'."\n";
						}
					}else{
						$table_body_markup .= '<td><div>'.$column_data.'</div></td>'."\n";
					}
				}
				$table_body_markup .= "</tr>"."\n";
				$last_row_number++;
			}
		}else{
			$table_body_markup .= "<tr><td colspan=\"".count($column_labels)."\"> <div id=\"filter_no_results\"><h3>Your search returned no results.</h3></div></td></tr>";
		}

		$last_row_number--;

		$table_body_markup .= '</tbody>'."\n";
		$table_markup = '<table width="100%" cellspacing="0" cellpadding="0" border="0" id="entries_table">'."\n";
		$table_markup .= $table_header_markup.$table_body_markup;
		$table_markup .= '</table>'."\n";

		$entries_markup = '<div id="entries_container">';
		$entries_markup .= $table_markup;
		$entries_markup .= '</div>';

		$pagination_markup = '';
		


		if(!empty($lastpage) && $numrows > $rows_per_page){
			
			if ($pageno != 1) {
			   if($lastpage > 13 && $pageno > 7){	
			   		$pagination_markup .= "<li class=\"page\"><a href='{$_SERVER['PHP_SELF']}?pageno=1'>&#8676; First</a></li>";
			   }
			   $prevpage = $pageno-1;
			} 
			
			//middle navigation
			if($pageno == 1){
				$i=1;
				while(($i<=13) && ($i<=$lastpage)){
					if($i != 1){
							$active_style = '';
						}else{
							$active_style = 'current_page';
					}
					$pagination_markup .= "<li class=\"page {$active_style}\"><a href='{$_SERVER['PHP_SELF']}?pageno={$i}'>{$i}</a></li>";
					$i++;
				}
				if($lastpage > $i){
					$pagination_markup .= "<li class=\"page_more\">...</li>";
				}
			}elseif ($pageno == $lastpage){
				
				if(($lastpage - 13) > 1){
					$pagination_markup .= "<li class=\"page_more\">...</li>";
					$i=1;
					$j=$lastpage - 12;
					while($i<=13){
						if($j != $lastpage){
							$active_style = '';
						}else{
							$active_style = 'current_page';
						}
						$pagination_markup .= "<li class=\"page {$active_style}\"><a href='{$_SERVER['PHP_SELF']}?pageno={$j}'>{$j}</a></li>";
						$i++;
						$j++;
					}
				}else{
					$i=1;
					while(($i<=13) && ($i<=$lastpage)){
						if($i != $lastpage){
							$active_style = '';
						}else{
							$active_style = 'current_page';
						}
						$pagination_markup .= "<li class=\"page {$active_style}\"><a href='{$_SERVER['PHP_SELF']}?pageno={$i}'>{$i}</a></li>";
						$i++;
					}
				}
				
			}else{
				$next_pages = false;
				$prev_pages = false;
				
				if(($lastpage - ($pageno + 6)) >= 1){
					$next_pages = true;
				}
				if(($pageno - 6) > 1){
					$prev_pages = true;
				}
				
				if($prev_pages){ //if there are previous pages
					$pagination_markup .= "<li class=\"page_more\">...</li>";
					if($next_pages){ //if there are next pages
						$i=1;
						$j=$pageno - 6;
						while($i<=13){
							if($j != $pageno){
								$active_style = '';
							}else{
								$active_style = 'current_page';
							}
							$pagination_markup .= "<li class=\"page {$active_style}\"><a href='{$_SERVER['PHP_SELF']}?pageno={$j}'>{$j}</a></li>";
							$i++;
							$j++;
						}
						$pagination_markup .= "<li class=\"page_more\">...</li>";
					}else{
						
						$i=1;
						$j=$pageno - 9;
						while(($i<=13) && ($j <= $lastpage)){
							if($j != $pageno){
								$active_style = '';
							}else{
								$active_style = 'current_page';
							}
							$pagination_markup .= "<li class=\"page {$active_style}\"><a href='{$_SERVER['PHP_SELF']}?pageno={$j}'>{$j}</a></li>";
							$i++;
							$j++;
						}
					}	
				}else{ //if there aren't previous pages
				
					$i=1;
  					while(($i<=13) && ($i <= $lastpage)){
  						if($i != $pageno){
							$active_style = '';
						}else{
							$active_style = 'current_page';
						}
						$pagination_markup .= "<li class=\"page {$active_style}\"><a href='{$_SERVER['PHP_SELF']}?pageno={$i}'>{$i}</a></li>";
						$i++;	
					}
					if($next_pages){
						$pagination_markup .= "<li class=\"page_more\">...</li>";
					}
				}
				
				
			}
				
			if ($pageno != $lastpage) 
			{
			   $nextpage = $pageno+1;
			   if($lastpage > 13){
			   		$pagination_markup .= "<li class=\"page\"><a href='{$_SERVER['PHP_SELF']}?pageno=$lastpage'>Last &#8677;</a></li>";
			   }
			}
			
			$pagination_markup = '<ul class="pages bluesoft small" id="me_pagination">'.$pagination_markup.'</ul>';
			$pagination_markup .= "<div id=\"me_pagination_label\">Displaying <strong>{$first_row_number}-{$last_row_number}</strong> of <strong id=\"me_entries_total\">{$numrows}</strong> users</div>";
		}else{
			$pagination_markup = '<div style="width: 100%; height: 20px;"></div>';
		}
		
		
		$entries_markup .= $pagination_markup;
		
		return $entries_markup;

	}

	//get an array containing id number of all filtered users id within ap_users table, based on $filter_data
	function mf_get_filtered_users_ids($dbh,$filter_data,$exclude_admin=true){

		//set column properties for basic fields
		$column_name_lookup['user_fullname']	= 'Name';
		$column_name_lookup['user_email']		= 'Email';
		$column_name_lookup['priv_administer']	= 'Admin Privileges';
		$column_name_lookup['status']			= 'Status';
		
		$column_type_lookup['user_fullname']	= 'text';
		$column_type_lookup['user_email']		= 'text';
		$column_type_lookup['priv_administer'] 	= 'admin';
		$column_type_lookup['status']			= 'status';
		
		
		$column_prefs = array('user_fullname','user_email','priv_administer','status');
		
		
		//determine column labels
		//the first 2 columns are always id and row_num
		$column_labels = array();

		$column_labels[] = 'mf_id';
		$column_labels[] = 'mf_row_num';
		
		foreach($column_prefs as $column_name){
			$column_labels[] = $column_name_lookup[$column_name];
		}

		//get the entries from ap_form_x table and store it into array
		$column_prefs_joined = '`'.implode("`,`",$column_prefs).'`';
		
		//check for filter data and build the filter query
		if(!empty($filter_data)){

			if($filter_type == 'all'){
				$condition_type = ' AND ';
			}else{
				$condition_type = ' OR ';
			}

			$where_clause_array = array();

			foreach ($filter_data as $value) {
				$element_name 	  = $value['element_name'];
				$filter_condition = $value['filter_condition'];
				$filter_keyword   = addslashes($value['filter_keyword']);

				$filter_element_type = $column_type_lookup[$element_name];

				$temp = explode('_', $element_name);
				$element_id = $temp[1];
				
				
				if($filter_condition == 'is'){
						$where_operand = '=';
						$where_keyword = "'{$filter_keyword}'";
				}else if($filter_condition == 'is_not'){
						$where_operand = '<>';
						$where_keyword = "'{$filter_keyword}'";
				}else if($filter_condition == 'begins_with'){
						$where_operand = 'LIKE';
						$where_keyword = "'{$filter_keyword}%'";
				}else if($filter_condition == 'ends_with'){
						$where_operand = 'LIKE';
						$where_keyword = "'%{$filter_keyword}'";
				}else if($filter_condition == 'contains'){
						$where_operand = 'LIKE';
						$where_keyword = "'%{$filter_keyword}%'";
				}else if($filter_condition == 'not_contain'){
						$where_operand = 'NOT LIKE';
						$where_keyword = "'%{$filter_keyword}%'";
				}else if($filter_condition == 'less_than' || $filter_condition == 'is_before'){
						$where_operand = '<';
						$where_keyword = "'{$filter_keyword}'";
				}else if($filter_condition == 'greater_than' || $filter_condition == 'is_after'){
						$where_operand = '>';
						$where_keyword = "'{$filter_keyword}'";
				}else if($filter_condition == 'is_admin'){
						$where_operand = '=';
						$where_keyword = "'1'";
				}else if($filter_condition == 'is_not_admin'){
						$where_operand = '=';
						$where_keyword = "'0'";
				}else if($filter_condition == 'is_active'){
						$where_operand = '=';
						$where_keyword = "'1'";
				}else if($filter_condition == 'is_suspended'){
						$where_operand = '=';
						$where_keyword = "'2'";
				}
		 			
				$where_clause_array[] = "{$element_name} {$where_operand} {$where_keyword}"; 
				
			}
			
			$where_clause = implode($condition_type, $where_clause_array);
			
			if(empty($where_clause)){
				$where_clause = "WHERE `status` > 0";
			}else{
				$where_clause = "WHERE ({$where_clause}) AND `status` > 0";
			}
			
						
		}else{
			$where_clause = "WHERE `status` > 0";
		}


		$query = "select 
						`user_id`,
						`user_id` as `row_num`,
						`user_fullname`,
						`user_email`,
						if(`priv_administer`=1,'Administrator','') `priv_administer`,
						if(`status`=1,'Active','Suspended') `status`
				    from 
				    	".MF_TABLE_PREFIX."users A 
				    	{$where_clause} ";
		
		$params = array();
		$sth = mf_do_query($query,$params,$dbh);
		
		$filtered_user_id_array = array();
		while($row = mf_do_fetch_result($sth)){
			if($exclude_admin){
				if($row['user_id'] != 1){ 
					$filtered_user_id_array[] = $row['user_id'];
				}
			}else{
				$filtered_user_id_array[] = $row['user_id'];
			}
		}

		return $filtered_user_id_array;

	}

	//get an array containing user permission to one particular form
	function mf_get_user_permissions($dbh,$form_id,$user_id){

		$query = "SELECT 
						`edit_form`, `edit_report`, `edit_entries`,`view_entries` 
					FROM 
						`".MF_TABLE_PREFIX."permissions`
				   WHERE
				   		`user_id` = ? and `form_id` = ?";
		$params = array($user_id,$form_id);
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		$perms['edit_form'] 	= false;
		$perms['edit_report'] 	= false;
		$perms['edit_entries'] 	= false;
		$perms['view_entries'] 	= false;

		if(!empty($row['edit_form'])){
			$perms['edit_form'] = true;
		}
		if(!empty($row['edit_report'])){
			$perms['edit_report'] = true;
		}
		if(!empty($row['edit_entries'])){
			$perms['edit_entries'] = true;
		}
		if(!empty($row['view_entries'])){
			$perms['view_entries'] = true;
		}

		return $perms;
	}

	//get an array containing user permission to all forms
	function mf_get_user_permissions_all($dbh,$user_id){

		$query = "SELECT 
						`edit_form`, `edit_report`, `edit_entries`,`view_entries`,`form_id` 
					FROM 
						`".MF_TABLE_PREFIX."permissions`
				   WHERE
				   		`user_id` = ?";
		
		$params = array($user_id);
		$sth = mf_do_query($query,$params,$dbh);
		
		$perms = array();
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];

			$edit_form    = false;
			$edit_report  = false;
			$edit_entries = false;
			$view_entries = false;

			if(!empty($row['edit_form'])){
				$edit_form = true;
			}
			if(!empty($row['edit_report'])){
				$edit_report = true;
			}
			if(!empty($row['edit_entries'])){
				$edit_entries = true;
			}
			if(!empty($row['view_entries'])){
				$view_entries = true;
			}

			$perms[$form_id]['edit_form']    = $edit_form;
			$perms[$form_id]['edit_report']  = $edit_report;
			$perms[$form_id]['edit_entries'] = $edit_entries;
			$perms[$form_id]['view_entries'] = $view_entries;
		}

		return $perms;
	}
	
?>