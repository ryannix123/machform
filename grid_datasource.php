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
	require('includes/entry-functions.php');
	
	
	/***
	The following options are sent to the server when server paging is enabled:
	  page - the page of data item to return (1 means the first page)
	  pageSize - the number of items to return
	  skip - how many data items to skip
	  take - the number of data items to return (the same as pageSize)
	**/
	$param_page_number = (int) ($_REQUEST['page'] ?? 0);
	$param_page_size   = (int) ($_REQUEST['pageSize'] ?? 0);

	//if no page size supplied, display 1000 rows by default
	if(empty($param_page_size)){
		$param_page_size = 1000;
	}
	

	$access_key = trim($_GET['key']);

	if(empty($access_key)){
		die("Error. Missing access key.");
	}

	$ssl_suffix = mf_get_ssl_suffix();
	$dbh = mf_connect_db();

	$mf_settings = mf_get_settings($dbh);
	$http_host = parse_url($mf_settings['base_url'], PHP_URL_HOST);

	//check the validity of the access key and get the chart property
	$query = "SELECT 
					chart_id,
					chart_type,
					form_id,
					chart_enable_filter,
					chart_filter_type,
					chart_grid_max_length,
					chart_grid_sort_by 
			    FROM
			    	".MF_TABLE_PREFIX."report_elements
			   WHERE
			   		access_key = ? and chart_status = 1";
	$params = array($access_key);
		
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	if(!empty($row['chart_id'])){
		$chart_id  	 = (int) $row['chart_id'];
		$form_id   	 = (int) $row['form_id'];
		$chart_type  = $row['chart_type'];

		$chart_enable_filter = (int) $row['chart_enable_filter'];
		$chart_filter_type 	 = $row['chart_filter_type'];

		$chart_grid_max_length = (int) $row['chart_grid_max_length'];

		$exploded = explode('-', $row['chart_grid_sort_by']);
		$options['sort_element'] = $exploded[0]; //the element name, e.g. element_2
		$options['sort_order']	 = $exploded[1]; //asc or desc

		//make sure the sort order string is valid and prevent SQL injection
		if($options['sort_order'] == 'asc'){
			$options['sort_order'] = 'ASC';
		}else{
			$options['sort_order'] = 'DESC';
		}

		if($chart_type != 'grid'){
			die("Invalid widget type.");
		}
	}else{
		die("This widget is no longer available (invalid access key).");
	}
	
	if(!empty($param_page_number)){
		$options['page_number'] = $param_page_number;
	}else{
		$options['page_number'] = 1;
	}

	
	$options['rows_per_page'] 				= $param_page_size;
	$options['display_incomplete_entries']  = false;

	//prepare filter data, if enabled
	$filter_data = array();

	if(!empty($chart_enable_filter)){
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

		$options['filter_data'] = $filter_data;
		$options['filter_type'] = $chart_filter_type;
	}

	//---------------------------------------------------------------------------------------------
	//from this point below, the code is pretty much similar as mf_display_entries_table() function
		
		//maximum length of column content
		if(!empty($chart_grid_max_length)){
			$max_data_length = $chart_grid_max_length;
		}else{
			$max_data_length = 99999999; //display all content
		}

		$pageno 	   = $options['page_number'] ?? false;
		$rows_per_page = $options['rows_per_page'] ?? false;
		$sort_element  = $options['sort_element'] ?? false;
		$sort_order	   = $options['sort_order'] ?? false;
		$filter_data   = $options['filter_data'] ?? false;
		$filter_type   = $options['filter_type'] ?? false;
		
		$display_incomplete_entries = $options['display_incomplete_entries'];

		if(empty($sort_element)){ //set the default sorting order
			$sort_element = 'id';
			$sort_order	  = 'desc';
		}

		$form_properties = mf_get_form_properties($dbh,$form_id,array('payment_currency','payment_enable_merchant','form_approval_enable'));
		$payment_currency = strtoupper($form_properties['payment_currency']);


		/******************************************************************************************/
		//prepare column header names lookup

		//get form element options first (checkboxes, choices, dropdown)
		$query = "select 
						element_id,
						option_id,
						`option`
					from 
						".MF_TABLE_PREFIX."element_options 
				   where 
				   		form_id=? and live=1 
				order by 
						element_id,position asc";
		$params = array($form_id);
		$sth = mf_do_query($query,$params,$dbh);
		
		while($row = mf_do_fetch_result($sth)){
			$element_id = $row['element_id'];
			$option_id  = $row['option_id'];
			
			$element_option_lookup[$element_id][$option_id] = htmlspecialchars(strip_tags($row['option']),ENT_QUOTES);
		}

		//get element options for matrix fields
		$query = "select 
						A.element_id,
						A.option_id,
						(select if(B.element_matrix_parent_id=0,A.option,
							(select 
									C.`option` 
							   from 
							   		".MF_TABLE_PREFIX."element_options C 
							  where 
							  		C.element_id=B.element_matrix_parent_id and 
							  		C.form_id=A.form_id and 
							  		C.live=1 and 
							  		C.option_id=A.option_id))
						) 'option_label'
					from 
						".MF_TABLE_PREFIX."element_options A left join ".MF_TABLE_PREFIX."form_elements B on (A.element_id=B.element_id and A.form_id=B.form_id)
				   where 
				   		A.form_id=? and A.live=1 and B.element_type='matrix' and B.element_status=1
				order by 
						A.element_id,A.option_id asc";
		$params = array($form_id);
		$sth = mf_do_query($query,$params,$dbh);
		
		while($row = mf_do_fetch_result($sth)){
			$element_id = $row['element_id'];
			$option_id  = $row['option_id'];
			
			$matrix_element_option_lookup[$element_id][$option_id] = htmlspecialchars(strip_tags($row['option_label']),ENT_QUOTES);
		}
		
		//get 'multiselect' status of matrix fields
		$query = "select 
						  A.element_id,
						  A.element_matrix_parent_id,
						  A.element_matrix_allow_multiselect,
						  (select if(A.element_matrix_parent_id=0,A.element_matrix_allow_multiselect,
						  			 (select B.element_matrix_allow_multiselect from ".MF_TABLE_PREFIX."form_elements B where B.form_id=A.form_id and B.element_id=A.element_matrix_parent_id)
						  			)
						  ) 'multiselect' 
					  from 
					 	  ".MF_TABLE_PREFIX."form_elements A
					 where 
					 	  A.form_id=? and A.element_status=1 and A.element_type='matrix'";
		$params = array($form_id);
		$sth = mf_do_query($query,$params,$dbh);
		
		while($row = mf_do_fetch_result($sth)){
			$matrix_multiselect_status[$row['element_id']] = $row['multiselect'];
		}


		/******************************************************************************************/
		//set column properties for basic fields
		$column_name_lookup['date_created']   = 'Date Created';
		$column_name_lookup['date_updated']   = 'Date Updated';
		$column_name_lookup['ip_address'] 	  = 'IP Address';
		
		$column_type_lookup['id'] 			= 'number';
		$column_type_lookup['row_num']		= 'number';
		$column_type_lookup['date_created'] = 'date';
		$column_type_lookup['date_updated'] = 'date';
		$column_type_lookup['ip_address'] 	= 'text';
		

		if($form_properties['payment_enable_merchant'] == 1){
			$column_name_lookup['payment_amount'] = 'Payment Amount';
			$column_name_lookup['payment_status'] = 'Payment Status';
			$column_name_lookup['payment_id']	  = 'Payment ID';
			
			$column_type_lookup['payment_amount'] = 'money';
			$column_type_lookup['payment_status'] = 'payment_status';
			$column_type_lookup['payment_id']	  = 'text';
		}

		if($form_properties['form_approval_enable'] == 1){
			$column_name_lookup['approval_status'] = 'Approval Status';		
			$column_type_lookup['approval_status'] = 'approval_status';
		}
		
		//get column properties for other fields
		$query  = "select 
						 element_id,
						 element_title,
						 element_type,
						 element_constraint,
						 element_choice_has_other,
						 element_choice_other_label,
						 element_time_showsecond,
						 element_time_24hour,
						 element_matrix_allow_multiselect,
						 ifnull(element_address_subfields_labels,'') element_address_subfields_labels    
				     from 
				         `".MF_TABLE_PREFIX."form_elements` 
				    where 
				    	 form_id=? and element_status=1 and element_type not in('section','page_break','media')
				 order by 
				 		 element_position asc";
		$params = array($form_id);
		$sth = mf_do_query($query,$params,$dbh);
		$element_radio_has_other = array();

		while($row = mf_do_fetch_result($sth)){

			$element_type 	    = $row['element_type'];
			$element_constraint = $row['element_constraint'];
			

			//get 'other' field label for checkboxes and radio button
			if($element_type == 'checkbox' || $element_type == 'radio'){
				if(!empty($row['element_choice_has_other'])){
					$element_option_lookup[$row['element_id']]['other'] = htmlspecialchars(strip_tags($row['element_choice_other_label']),ENT_QUOTES);
				
					if($element_type == 'radio'){
						$element_radio_has_other['element_'.$row['element_id']] = true;	
					}
				}
			}

			$row['element_title'] = htmlspecialchars(strip_tags($row['element_title']),ENT_QUOTES);

			if('address' == $element_type){ //address has 6 fields
				$column_name_lookup['element_'.$row['element_id'].'_1'] = $row['element_title'].' - Street Address';
				$column_name_lookup['element_'.$row['element_id'].'_2'] = 'Address Line 2';
				$column_name_lookup['element_'.$row['element_id'].'_3'] = 'City';
				$column_name_lookup['element_'.$row['element_id'].'_4'] = 'State/Province/Region';
				$column_name_lookup['element_'.$row['element_id'].'_5'] = 'Zip/Postal Code';
				$column_name_lookup['element_'.$row['element_id'].'_6'] = 'Country';
				
				//if there is custom label for address subfields, use it instead
				if(!empty($row['element_address_subfields_labels'])){
					$subfields_labels_obj = json_decode($row['element_address_subfields_labels']);
					
					if(!empty($subfields_labels_obj->street)){
						$column_name_lookup['element_'.$row['element_id'].'_1'] = $row['element_title'].' - '.$subfields_labels_obj->street;
					}
					if(!empty($subfields_labels_obj->street2)){
						$column_name_lookup['element_'.$row['element_id'].'_2'] = $subfields_labels_obj->street2;
					}
					if(!empty($subfields_labels_obj->city)){
						$column_name_lookup['element_'.$row['element_id'].'_3'] = $subfields_labels_obj->city;
					}
					if(!empty($subfields_labels_obj->state)){
						$column_name_lookup['element_'.$row['element_id'].'_4'] = $subfields_labels_obj->state;
					}
					if(!empty($subfields_labels_obj->postal)){
						$column_name_lookup['element_'.$row['element_id'].'_5'] = $subfields_labels_obj->postal; 
					}
					if(!empty($subfields_labels_obj->country)){
						$column_name_lookup['element_'.$row['element_id'].'_6'] = $subfields_labels_obj->country;
					}
				}	

				$column_type_lookup['element_'.$row['element_id'].'_1'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_2'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_3'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_4'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_5'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_6'] = $row['element_type'];
				
			}elseif ('simple_name' == $element_type){ //simple name has 2 fields
				$column_name_lookup['element_'.$row['element_id'].'_1'] = $row['element_title'].' - First';
				$column_name_lookup['element_'.$row['element_id'].'_2'] = $row['element_title'].' - Last';
				
				$column_type_lookup['element_'.$row['element_id'].'_1'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_2'] = $row['element_type'];
				
			}elseif ('simple_name_wmiddle' == $element_type){ //simple name with middle has 3 fields
				$column_name_lookup['element_'.$row['element_id'].'_1'] = $row['element_title'].' - First';
				$column_name_lookup['element_'.$row['element_id'].'_2'] = $row['element_title'].' - Middle';
				$column_name_lookup['element_'.$row['element_id'].'_3'] = $row['element_title'].' - Last';
				
				$column_type_lookup['element_'.$row['element_id'].'_1'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_2'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_3'] = $row['element_type'];
				
			}elseif ('name' == $element_type){ //name has 4 fields
				$column_name_lookup['element_'.$row['element_id'].'_1'] = $row['element_title'].' - Title';
				$column_name_lookup['element_'.$row['element_id'].'_2'] = $row['element_title'].' - First';
				$column_name_lookup['element_'.$row['element_id'].'_3'] = $row['element_title'].' - Last';
				$column_name_lookup['element_'.$row['element_id'].'_4'] = $row['element_title'].' - Suffix';
				
				$column_type_lookup['element_'.$row['element_id'].'_1'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_2'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_3'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_4'] = $row['element_type'];
				
			}elseif ('name_wmiddle' == $element_type){ //name with middle has 5 fields
				$column_name_lookup['element_'.$row['element_id'].'_1'] = $row['element_title'].' - Title';
				$column_name_lookup['element_'.$row['element_id'].'_2'] = $row['element_title'].' - First';
				$column_name_lookup['element_'.$row['element_id'].'_3'] = $row['element_title'].' - Middle';
				$column_name_lookup['element_'.$row['element_id'].'_4'] = $row['element_title'].' - Last';
				$column_name_lookup['element_'.$row['element_id'].'_5'] = $row['element_title'].' - Suffix';
				
				$column_type_lookup['element_'.$row['element_id'].'_1'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_2'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_3'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_4'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_5'] = $row['element_type'];
				
			}elseif('money' == $element_type){//money format
				$column_name_lookup['element_'.$row['element_id']] = $row['element_title'];
				if(!empty($element_constraint)){
					$column_type_lookup['element_'.$row['element_id']] = 'money_'.$element_constraint; //euro, pound, yen,etc
				}else{
					$column_type_lookup['element_'.$row['element_id']] = 'money_dollar'; //default is dollar
				}
			}elseif('checkbox' == $element_type){ //checkboxes, get childs elements
							
				$this_checkbox_options = $element_option_lookup[$row['element_id']];
				
				foreach ($this_checkbox_options as $option_id=>$option){
					$column_name_lookup['element_'.$row['element_id'].'_'.$option_id] = $option;
					$column_type_lookup['element_'.$row['element_id'].'_'.$option_id] = $row['element_type'];
				}
			}elseif ('time' == $element_type){
				
				if(!empty($row['element_time_showsecond']) && !empty($row['element_time_24hour'])){
					$column_type_lookup['element_'.$row['element_id']] = 'time_24hour';
				}else if(!empty($row['element_time_showsecond'])){
					$column_type_lookup['element_'.$row['element_id']] = 'time';
				}else if(!empty($row['element_time_24hour'])){
					$column_type_lookup['element_'.$row['element_id']] = 'time_24hour_noseconds';
				}else{
					$column_type_lookup['element_'.$row['element_id']] = 'time_noseconds';
				}
				
				$column_name_lookup['element_'.$row['element_id']] = $row['element_title'];
			}else if('matrix' == $element_type){ 
				
				if(empty($matrix_multiselect_status[$row['element_id']])){
					$column_name_lookup['element_'.$row['element_id']] = $row['element_title'];
					$column_type_lookup['element_'.$row['element_id']] = 'matrix_radio';
				}else{
					$this_checkbox_options = $matrix_element_option_lookup[$row['element_id']];
					
					foreach ($this_checkbox_options as $option_id=>$option){
						$option = $option.' - '.$row['element_title'];
						$column_name_lookup['element_'.$row['element_id'].'_'.$option_id] = $option;
						$column_type_lookup['element_'.$row['element_id'].'_'.$option_id] = 'matrix_checkbox';
					}
				}
			}else if('signature' == $element_type){
				//don't display signature field
				continue;
			}else{ //for other elements with only 1 field
				$column_name_lookup['element_'.$row['element_id']] = $row['element_title'];
				$column_type_lookup['element_'.$row['element_id']] = $row['element_type'];
			}

			
		}
		/******************************************************************************************/
		
		
		//get column preferences and store it into array
		if($display_incomplete_entries === true){
			$incomplete_status = 1;
		}else{
			$incomplete_status = 0;
		}

		$query = "select element_name from ".MF_TABLE_PREFIX."grid_columns where form_id=? and chart_id=? order by position asc";
		$params = array($form_id,$chart_id);
		$sth = mf_do_query($query,$params,$dbh);
		while($row = mf_do_fetch_result($sth)){
			if($row['element_name'] == 'id'){
				continue;
			}
			$column_prefs[] = $row['element_name'];
		}


		//if there is no column preferences, display the first 6 fields
		if(empty($column_prefs)){
			$temp_slice = array_slice($column_name_lookup,0,8);
			unset($temp_slice['date_updated']);
			unset($temp_slice['ip_address']);
			$column_prefs = array_keys($temp_slice);
		}
		
		//determine column labels
		//the first column is always id
		$column_labels = array();

		$column_labels[] = 'mf_id';
		
		foreach($column_prefs as $column_name){
			$column_labels[] = $column_name_lookup[$column_name];
		}

		$payment_table_columns = array('payment_amount','payment_status','payment_id');

		//determine if the filter data contain payment fields or not
		$filter_has_payment_field = false;
		if(!empty($filter_data)){
			foreach ($filter_data as $value) {
				$element_name = $value['element_name'];
				if(in_array($element_name, $payment_table_columns)){
					$filter_has_payment_field = true;
					break;
				}
			}
		}

		$sort_element_is_payment_field = false;
		if(in_array($sort_element, $payment_table_columns)){
			$sort_element_is_payment_field = true;
		}

		//get the entries from ap_form_x table and store it into array
		//but first we need to check if there is any column preferences from ap_form_payments table
		$payment_columns_prefs = array_intersect($payment_table_columns, $column_prefs);

		//if the user doesn't select any payment fields as a preference but the filter data or sorting preference refer to one of them, we need to manually include the payment fields as preference
		if((empty($payment_columns_prefs) && $filter_has_payment_field === true) || ($sort_element_is_payment_field === true)){
			$payment_columns_prefs = array('payment_amount','payment_status','payment_id');
		}

		$payment_table_query = '';
		if(!empty($payment_columns_prefs)){
			//there is one or more column from ap_form_payments
			//don't include this column into $column_prefs_joined variable
			$column_prefs_temp = array();
			foreach ($column_prefs as $value) {
				if(in_array($value, $payment_table_columns)){
					continue;
				}
				$column_prefs_temp[] = $value;
			}

			if(!empty($column_prefs_temp)){
				$column_prefs_joined = ',`'.implode("`,`",$column_prefs_temp).'`';
			}

			//build the query to ap_form_payments table
			foreach ($payment_columns_prefs as $column_name) {
				if($column_name == 'payment_status'){
					$payment_table_query .= ",ifnull((select 
													`{$column_name}` 
												 from ".MF_TABLE_PREFIX."form_payments 
												where 
													 form_id='{$form_id}' and record_id=A.id  and `status` = 1 
											 order by 
											 		 afp_id desc limit 1),'unpaid') {$column_name}";
				}else{
					$payment_table_query .= ",(select 
													`{$column_name}` 
												 from ".MF_TABLE_PREFIX."form_payments 
												where 
													 form_id='{$form_id}' and record_id=A.id  and `status` = 1 
											 order by 
											 		 afp_id desc limit 1) {$column_name}";
				}
			}

		}else{
			//there is no column from ap_form_payments
			$column_prefs_joined = ',`'.implode("`,`",$column_prefs).'`';
		}
		

		//if there is any radio fields which has 'other', we need to query that field as well
		if(!empty($element_radio_has_other)){
			$radio_has_other_array = array();
			foreach($element_radio_has_other as $element_name=>$value){
				$radio_has_other_array[] = $element_name.'_other';
			}
			$radio_has_other_joined = '`'.implode("`,`",$radio_has_other_array).'`';
			$column_prefs_joined = $column_prefs_joined.','.$radio_has_other_joined;
		}
		
		if($display_incomplete_entries === true){
			//only display incomplete entries
			$status_clause = "`status`=2";
		}else{
			//only display completed entries
			$status_clause = "`status`=1";
		}

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

				//if the filter is a column from ap_form_payments table
				//we need to replace $element_name with the subquery to ap_form_payments table
				if(!empty($payment_columns_prefs) && in_array($element_name, $payment_table_columns)){
					if($element_name == 'payment_status'){
						$element_name = "ifnull((select 
													`{$element_name}` 
												 from ".MF_TABLE_PREFIX."form_payments 
												where 
													 form_id='{$form_id}' and record_id=A.id  and `status` = 1 
											 order by 
											 		 afp_id desc limit 1),'unpaid')";
					}else{
						$element_name = "(select 
													`{$element_name}` 
												 from ".MF_TABLE_PREFIX."form_payments 
												where 
													 form_id='{$form_id}' and record_id=A.id  and `status` = 1 
											 order by 
											 		 afp_id desc limit 1)";
					}
				}
				
				
				if(in_array($filter_element_type, array('radio','select','matrix_radio'))){
					
					//these types need special steps to filter
					//we need to look into the ap_element_options first and do the filter there
					$null_clause = '';
					if($filter_condition == 'is'){
						$where_operand = '=';
						$where_keyword = "'{$filter_keyword}'";

						if(empty($filter_keyword)){
							$null_clause = "OR {$element_name} = 0";
						}
					}else if($filter_condition == 'is_not'){
						$where_operand = '<>';
						$where_keyword = "'{$filter_keyword}'";

						if(!empty($filter_keyword)){
							$null_clause = "OR {$element_name} = 0";
						}
					}else if($filter_condition == 'begins_with'){
						$where_operand = 'LIKE';
						$where_keyword = "'{$filter_keyword}%'";

						if(empty($filter_keyword)){
							$null_clause = "OR {$element_name} = 0";
						}
					}else if($filter_condition == 'ends_with'){
						$where_operand = 'LIKE';
						$where_keyword = "'%{$filter_keyword}'";

						if(empty($filter_keyword)){
							$null_clause = "OR {$element_name} = 0";
						}
					}else if($filter_condition == 'contains'){
						$where_operand = 'LIKE';
						$where_keyword = "'%{$filter_keyword}%'";

						if(empty($filter_keyword)){
							$null_clause = "OR {$element_name} = 0";
						}
					}else if($filter_condition == 'not_contain'){
						$where_operand = 'NOT LIKE';
						$where_keyword = "'%{$filter_keyword}%'";

						if(!empty($filter_keyword)){
							$null_clause = "OR {$element_name} = 0";
						}
					}

					//do a query to ap_element_options table
					$query = "select 
									option_id 
								from 
									".MF_TABLE_PREFIX."element_options 
							   where 
							   		form_id=? and
							   		element_id=? and
							   		live=1 and 
							   		`option` {$where_operand} {$where_keyword}";
					
					$params = array($form_id,$element_id);
			
					$filtered_option_id_array = array();
					$sth = mf_do_query($query,$params,$dbh);
					while($row = mf_do_fetch_result($sth)){
						$filtered_option_id_array[] = $row['option_id'];
					}

					$filtered_option_id = implode("','", $filtered_option_id_array);

					$filter_radio_has_other = false;
					if($filter_element_type == 'radio' && !empty($radio_has_other_array)){
						if(in_array($element_name.'_other', $radio_has_other_array)){
							$filter_radio_has_other = true;
						}else{
							$filter_radio_has_other = false;
						}
					}
					
					if($filter_radio_has_other){ //if the filter is radio button field with 'other'
						if(!empty($filtered_option_id_array)){
							$where_clause_array[] = "({$element_name} IN('{$filtered_option_id}') OR {$element_name}_other {$where_operand} {$where_keyword} {$null_clause})"; 
						}else{
							$where_clause_array[] = "({$element_name}_other {$where_operand} {$where_keyword} {$null_clause})";
						}
					}else{//otherwise, for the rest of the field types
						if(!empty($filtered_option_id_array)){							
							if(!empty($null_clause)){
								$where_clause_array[] = "({$element_name} IN('{$filtered_option_id}') {$null_clause})";
							}else{
								$where_clause_array[] = "{$element_name} IN('{$filtered_option_id}')";
							} 
						}else{
							if(!empty($null_clause)){
								$where_clause_array[] = str_replace("OR", '', $null_clause);
							}
						}
					}
				}else if(in_array($filter_element_type, array('date','europe_date'))){

					//if fixed date format is being used as the keyword
					if(strpos($filter_keyword,'/') !== false){
						$date_exploded = array();
						$date_exploded = explode('/', $filter_keyword); //the filter_keyword has format mm/dd/yyyy
						
						$filter_keyword = $date_exploded[2].'-'.$date_exploded[0].'-'.$date_exploded[1];
					}else{
						//if relative-date format is being used, we need to convert it first
						if(!empty($filter_keyword)){
							$filter_keyword = date("Y-m-d",strtotime($filter_keyword));
						}
					}
					
					if($filter_condition == 'is'){
						if(!empty($filter_keyword)){
							$where_operand = '=';
							$where_keyword = "'{$filter_keyword}'";
							$where_clause_array[] = "date({$element_name}) {$where_operand} {$where_keyword}"; 
						}else{
							$where_clause_array[] = "{$element_name} IS NULL OR {$element_name} = '' OR {$element_name} = '0000-00-00'"; 
						}
					}else if($filter_condition == 'is_before'){
						$where_operand = '<';
						$where_keyword = "'{$filter_keyword}'";
						$where_clause_array[] = "date({$element_name}) {$where_operand} {$where_keyword}";
					}else if($filter_condition == 'is_after'){
						$where_operand = '>';
						$where_keyword = "'{$filter_keyword}'";
						$where_clause_array[] = "date({$element_name}) {$where_operand} {$where_keyword}";
					}
					 
				}else{
					$null_clause = '';

					if($filter_condition == 'is'){
						$where_operand = '=';
						$where_keyword = "'{$filter_keyword}'";

						if(empty($filter_keyword)){
							$null_clause = "OR {$element_name} IS NULL";
						}
					}else if($filter_condition == 'is_not'){
						$where_operand = '<>';
						$where_keyword = "'{$filter_keyword}'";

						if(!empty($filter_keyword)){
							$null_clause = "OR {$element_name} IS NULL";
						}
					}else if($filter_condition == 'begins_with'){
						$where_operand = 'LIKE';
						$where_keyword = "'{$filter_keyword}%'";

						if(empty($filter_keyword)){
							$null_clause = "OR {$element_name} IS NULL";
						}
					}else if($filter_condition == 'ends_with'){
						$where_operand = 'LIKE';
						$where_keyword = "'%{$filter_keyword}'";

						if(empty($filter_keyword)){
							$null_clause = "OR {$element_name} IS NULL";
						}
					}else if($filter_condition == 'contains'){
						$where_operand = 'LIKE';
						$where_keyword = "'%{$filter_keyword}%'";

						if(empty($filter_keyword)){
							$null_clause = "OR {$element_name} IS NULL";
						}
					}else if($filter_condition == 'not_contain'){
						$where_operand = 'NOT LIKE';
						$where_keyword = "'%{$filter_keyword}%'";

						if(!empty($filter_keyword)){
							$null_clause = "OR {$element_name} IS NULL";
						}
					}else if($filter_condition == 'less_than' || $filter_condition == 'is_before'){
						$where_operand = '<';
						$where_keyword = "'{$filter_keyword}'";
					}else if($filter_condition == 'greater_than' || $filter_condition == 'is_after'){
						$where_operand = '>';
						$where_keyword = "'{$filter_keyword}'";
					}else if($filter_condition == 'is_one'){
						$where_operand = '=';
						$where_keyword = "'1'";
					}else if($filter_condition == 'is_zero'){
						$where_operand = '=';
						$where_keyword = "'0'";
					}else if($filter_condition == 'is_approved'){
						$where_operand = '=';
						$where_keyword = "'approved'";
					}else if($filter_condition == 'is_denied'){
						$where_operand = '=';
						$where_keyword = "'denied'";
					}else if($filter_condition == 'is_pending'){
						$where_operand = '=';
						$where_keyword = "'pending'";
					}
		 			
		 			if(!empty($null_clause)){
		 				$where_clause_array[] = "({$element_name} {$where_operand} {$where_keyword} {$null_clause})";
		 			}else{
		 				$where_clause_array[] = "{$element_name} {$where_operand} {$where_keyword}"; 
		 			}
					
				}
			}
			
			$where_clause = implode($condition_type, $where_clause_array);
			
			if(empty($where_clause)){
				$where_clause = "WHERE {$status_clause}";
			}else{
				$where_clause = "WHERE ({$where_clause}) AND {$status_clause}";
			}
			
						
		}else{
			$where_clause = "WHERE {$status_clause}";
		}
		
		//check the sorting element
		//if the element type is radio, select or matrix_radio, we need to add a sub query to the main query
		//so that the fields can be sorted properly (the sub query need to get values from ap_element_options table)
		$sort_element_type = $column_type_lookup[$sort_element];
		$sorting_query = '';

		if(in_array($sort_element_type, array('radio','select','matrix_radio'))){
			if($sort_element_type == 'radio' && !empty($radio_has_other_array)){
				if(in_array($sort_element.'_other', $radio_has_other_array)){
					$sort_radio_has_other = true;
				}
			}

			$temp = explode('_', $sort_element);
			$sort_element_id = $temp[1];

			if($sort_radio_has_other){ //if this is radio button field with 'other' enabled
				$sorting_query = ",(	
										select if(A.{$sort_element}=0,A.{$sort_element}_other,
													(select 
															`option` 
														from ".MF_TABLE_PREFIX."element_options 
													   where 
													   		form_id='{$form_id}' and 
													   		element_id='{$sort_element_id}' and 
													   		option_id=A.{$sort_element} and 
													   		live=1)
									   	)
								   ) {$sort_element}_key";
			}else{
				$sorting_query = ",(
									select 
											`option` 
										from ".MF_TABLE_PREFIX."element_options 
									   where 
									   		form_id='{$form_id}' and 
									   		element_id='{$sort_element_id}' and 
									   		option_id=A.{$sort_element} and 
									   		live=1
								 ) {$sort_element}_key";
			}

			//override the $sort_element
			$sort_element .= '_key';
		}


		/** pagination **/
		//identify how many database rows are available
		$query = "select count(*) total_row from (select 
						`id`,
						`id` as `row_num`
						{$column_prefs_joined} 
						{$sorting_query} 
						{$payment_table_query} 
				    from 
				    	".MF_TABLE_PREFIX."form_{$form_id} A 
				    	{$where_clause} ) B ";
		$params = array();
			
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		$numrows   = $row['total_row'];
		$lastpage  = ceil($numrows/$rows_per_page);
		$total_datasource_row = $numrows;					
							
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
		if(!empty($numrows)){
			$limit = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;
		}
		/** end pagination **/

		$query = "select 
						`id`,
						`id` as `row_num`
						{$column_prefs_joined} 
						{$sorting_query} 
						{$payment_table_query} 
				    from 
				    	".MF_TABLE_PREFIX."form_{$form_id} A 
				    	{$where_clause} 
				order by 
						{$sort_element} {$sort_order}
						{$limit}";
		
		$params = array();
		$sth = mf_do_query($query,$params,$dbh);
		$i=0;
		$data_obj_array = array();
		
		//prepend "id" into the column preferences
		array_unshift($column_prefs,"id");
		
		while($row = mf_do_fetch_result($sth)){
			$j=0;
			$data_obj = new stdClass();
			
			foreach($column_prefs as $column_name){
				$form_data[$i][$j] = '';
				
				//limit the data length, unless for file element
				if($column_type_lookup[$column_name] != 'file'){
					if(strlen($row[$column_name]) > $max_data_length){
						$row[$column_name] = substr($row[$column_name],0,$max_data_length).'...';
					}
				}
				
				if($column_type_lookup[$column_name] == 'time'){
					if(!empty($row[$column_name])){
						$form_data[$i][$j] = date("h:i:s A",strtotime($row[$column_name]));
					}else {
						$form_data[$i][$j] = '';
					}
				}elseif($column_type_lookup[$column_name] == 'time_noseconds'){ 
					if(!empty($row[$column_name])){
						$form_data[$i][$j] = date("h:i A",strtotime($row[$column_name]));
					}else {
						$form_data[$i][$j] = '';
					}
				}elseif($column_type_lookup[$column_name] == 'time_24hour_noseconds'){ 
					if(!empty($row[$column_name])){
						$form_data[$i][$j] = date("H:i",strtotime($row[$column_name]));
					}else {
						$form_data[$i][$j] = '';
					}
				}elseif($column_type_lookup[$column_name] == 'time_24hour'){ 
					if(!empty($row[$column_name])){
						$form_data[$i][$j] = date("H:i:s",strtotime($row[$column_name]));
					}else {
						$form_data[$i][$j] = '';
					}
				}elseif(substr($column_type_lookup[$column_name],0,5) == 'money'){ //set column formatting for money fields
					if(!empty($row[$column_name])){
						$form_data[$i][$j] =  (float) $row[$column_name];
					}else{
						$form_data[$i][$j] = '';
					}
				}elseif($column_type_lookup[$column_name] == 'date'){ //date with format MM/DD/YYYY
					if(!empty($row[$column_name]) && ($row[$column_name] != '0000-00-00')){
						$form_data[$i][$j] = date("Y-m-d H:i:s",strtotime($row[$column_name]));
					}
				}elseif($column_type_lookup[$column_name] == 'europe_date'){ //date with format DD/MM/YYYY
					
					if(!empty($row[$column_name]) && ($row[$column_name] != '0000-00-00')){
						$form_data[$i][$j] = date("Y-m-d H:i:s",strtotime($row[$column_name]));
					}
				}elseif($column_type_lookup[$column_name] == 'number'){ 
					if($column_name == 'id'){
						$form_data[$i][$j] = (int) $row[$column_name];	
					}else{
						$form_data[$i][$j] = (float) $row[$column_name];
					}
					
				}elseif (in_array($column_type_lookup[$column_name],array('radio','select'))){ //multiple choice or dropdown
					$exploded = array();
					$exploded = explode('_',$column_name);
					$this_element_id = $exploded[1];
					$this_option_id  = $row[$column_name];
					
					$form_data[$i][$j] = $element_option_lookup[$this_element_id][$this_option_id];
					
					if($column_type_lookup[$column_name] == 'radio'){
						if(isset($element_radio_has_other['element_'.$this_element_id]) && $element_radio_has_other['element_'.$this_element_id] === true && empty($form_data[$i][$j])){
							$form_data[$i][$j] = htmlspecialchars($row['element_'.$this_element_id.'_other'],ENT_QUOTES);
						}
					}
				}elseif(substr($column_type_lookup[$column_name],0,6) == 'matrix'){
					$exploded = array();
					$exploded = explode('_',$column_type_lookup[$column_name]);
					$matrix_type = $exploded[1];

					if($matrix_type == 'radio'){
						$exploded = array();
						$exploded = explode('_',$column_name);
						$this_element_id = $exploded[1];
						$this_option_id  = $row[$column_name];
						
						$form_data[$i][$j] = $matrix_element_option_lookup[$this_element_id][$this_option_id];
					}else if($matrix_type == 'checkbox'){
						if(!empty($row[$column_name])){
							$form_data[$i][$j]  = '<div class="me_center_div"><img src="images/icons/62_blue_16.png" align="absmiddle" /></div>';
						}else{
							$form_data[$i][$j]  = '';
						}
					}
				}elseif($column_type_lookup[$column_name] == 'checkbox'){
					
					if(!empty($row[$column_name])){
						if(substr($column_name,-5) == "other"){ //if this is an 'other' field, display the actual value
							$form_data[$i][$j] = htmlspecialchars($row[$column_name],ENT_QUOTES);
						}else{
							$form_data[$i][$j]  = '<div class="me_center_div"><img src="images/icons/62_blue_16.png" align="absmiddle" /></div>';
						}
					}else{
						$form_data[$i][$j]  = '';
					}
					
				}elseif(in_array($column_type_lookup[$column_name],array('phone','simple_phone'))){ 
					if(!empty($row[$column_name])){
						if($column_type_lookup[$column_name] == 'phone'){
							$form_data[$i][$j] = '('.substr($row[$column_name],0,3).') '.substr($row[$column_name],3,3).'-'.substr($row[$column_name],6,4);
						}else{
							$form_data[$i][$j] = $row[$column_name];
						}
					}
				}elseif($column_type_lookup[$column_name] == 'file'){
					if(!empty($row[$column_name])){
						$filename_record  = $row[$column_name];
						$filename_array  = array();
				
						if(!empty($filename_record)){
							$filename_array  = explode('|',$filename_record);
						}

						if(!empty($filename_array)){

							$exploded = array();
							$exploded = explode('_',$column_name);
							$this_element_id = $exploded[1];

							$entry_id = $row['id'];
							
							foreach ($filename_array as $filename_value){
								$filename_md5  = md5($filename_value);
								$filename_path = $mf_settings['upload_dir']."/form_{$form_id}/files/{$filename_value}";
								
								$file_size = @mf_format_bytes(filesize($filename_path));
								
								$file_1 	    =  substr($filename_value,strpos($filename_value,'-')+1);
								$filename_value = substr($file_1,strpos($file_1,'-')+1);
								
								//encode the long query string for more readibility
								$q_string = base64_encode("form_id={$form_id}&id={$entry_id}&el=element_{$this_element_id}&hash={$filename_md5}");
									
								$filename_value = htmlspecialchars($filename_value);
									
								//provide a markup to download the file
								$form_data[$i][$j] .= '<img src="images/icons/185.png" align="absmiddle" style="vertical-align: middle" />&nbsp;<a class="entry_link" href="http'.$ssl_suffix.'://'.$http_host.dirname($_SERVER['PHP_SELF']).'/download.php?q='.$q_string.'">'.$filename_value.'</a><br/>';
										
							}
							
						}
						
					}
				}elseif($column_type_lookup[$column_name] == 'payment_status'){
					if($row[$column_name] == 'paid'){
						$payment_status_color = 'style="color: green;font-weight: bold"';
						$payment_status_label = strtoupper($row[$column_name]);
					}else{
						$payment_status_color = '';
						$payment_status_label = ucfirst(strtolower($row[$column_name]));
					}

					$form_data[$i][$j] = '<span '.$payment_status_color.'>'.$payment_status_label.'</span>';
				}elseif($column_type_lookup[$column_name] == 'approval_status'){
					$form_data[$i][$j] = ucfirst($row[$column_name]);
				}else{
					$form_data[$i][$j] = htmlspecialchars(str_replace("\r","",str_replace("\n"," ",$row[$column_name])),ENT_QUOTES);
				}
				
				$data_obj->$column_name = $form_data[$i][$j];
				$j++;
			}
			$data_obj_array[$i] = $data_obj;
			$i++;
		}

	//---------------------------------------------------------------------------------------------
	//from this point above, the code is pretty much similar as mf_display_entries_table() function


	$results_obj = new stdClass();
	$results_obj->rows_data =  $data_obj_array;
	$results_obj->rows_total = $total_datasource_row;
	$json = json_encode($results_obj);

	header('content-type: application/json; charset=utf-8');
	echo isset($_GET['callback']) ? "{$_GET['callback']}($json)" : $json;

 ?>