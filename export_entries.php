<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	require('includes/init.php');

	@ini_set("max_execution_time",1800);
	@ini_set("display_errors", false);
	
	require('config.php');
	require('includes/db-core.php');
	require('includes/helper-functions.php');
	require('includes/check-session.php');

	require('includes/entry-functions.php');
	require('includes/users-functions.php');
	
	ob_clean(); //clean the output buffer
	
	$form_id 	 = (int) trim($_REQUEST['form_id']);
	$export_type = trim($_REQUEST['type'] ?? '');
	$entries_id  = trim($_REQUEST['entries_id'] ?? '');
	$selected_fields = (int) $_REQUEST['selected_fields'];
	$csrf_token 	 = trim($_REQUEST['csrf_token']);

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);
	
	$incomplete_entries = (int) ($_REQUEST['incomplete_entries'] ?? 0); //if this is operation targetted to incomplete entries, this will contain '1'
	if(empty($incomplete_entries)){
		$incomplete_entries = 0;
	}

	if(empty($form_id)){
		die("Invalid form ID.");
	}

	$ssl_suffix = mf_get_ssl_suffix();
	
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);
	$mf_properties = mf_get_form_properties($dbh,$form_id,array('form_active'));
	
	$http_host = parse_url($mf_settings['base_url'], PHP_URL_HOST);

	//check inactive form, inactive form settings should not displayed
	if (empty($mf_properties) || $mf_properties['form_active'] == null) {
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

	$form_properties = mf_get_form_properties($dbh,$form_id,array('payment_enable_merchant','payment_ask_billing','payment_ask_shipping','form_approval_enable'));

	//prepare filename for the export
	$query = "select 
					A.form_name,
					ifnull(B.entries_sort_by,'id-desc') entries_sort_by,
					ifnull(B.entries_filter_type,'all') entries_filter_type,
					ifnull(B.entries_enable_filter,0) entries_enable_filter,
					ifnull(B.entries_incomplete_sort_by,'id-desc') entries_incomplete_sort_by,
					ifnull(B.entries_incomplete_filter_type,'all') entries_incomplete_filter_type,
					ifnull(B.entries_incomplete_enable_filter,0) entries_incomplete_enable_filter				  
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
		$form_name = $row['form_name'];
		$clean_form_name = preg_replace("/[^A-Za-z0-9_-]/","",$form_name);

		if(empty($clean_form_name)){
			$clean_form_name = 'UntitledForm';
		}
		
		if(empty($incomplete_entries)){
			$filter_type   			= $row['entries_filter_type'];
			$entries_enable_filter 	= $row['entries_enable_filter'];
			$sort_by 				= $row['entries_sort_by'];
		}else{
			$filter_type   			= $row['entries_incomplete_filter_type'];
			$entries_enable_filter 	= $row['entries_incomplete_enable_filter'];
			$sort_by 				= $row['entries_incomplete_sort_by'];	
		}
	}else{
		die("Invalid form ID.");
	}
	
	$exploded = explode('-', $sort_by);
	$sort_element = $exploded[0]; //the element name, e.g. element_2
	$sort_order	  = $exploded[1]; //asc or desc

	//make sure the sort order string is valid and prevent SQL injection
	if($sort_order == 'asc'){
		$sort_order = 'ASC';
	}else{
		$sort_order = 'DESC';
	}

	if(!empty($entries_id)){ //if this is an export of few selected entries, prepare the where condition
		$entries_id_joined = str_replace(',', "','", $entries_id);
		$selected_where_clause = "WHERE `id` IN('{$entries_id_joined}')";
	}else{ //otherwise, this is full export
		//if there is filter enabled, get filter data
		if(!empty($entries_enable_filter)){
				
				if(!empty($incomplete_entries)){
					$incomplete_status = 1;
				}else{
					$incomplete_status = 0;
				}

				//get filter keywords from ap_form_filters table
				$query = "select
								element_name,
								filter_condition,
								filter_keyword
							from 
								".MF_TABLE_PREFIX."form_filters
						   where
						   		form_id = ? and user_id = ? and incomplete_entries = ? 
						order by 
						   		aff_id asc";
				$params = array($form_id,$_SESSION['mf_user_id'],$incomplete_status);
				$sth = mf_do_query($query,$params,$dbh);
				$i = 0;
				while($row = mf_do_fetch_result($sth)){
					$filter_data[$i]['element_name'] 	 = $row['element_name'];
					$filter_data[$i]['filter_condition'] = $row['filter_condition'];
					$filter_data[$i]['filter_keyword'] 	 = $row['filter_keyword'];
					$i++;
				}
		}
	}

	
	//code below is pretty much the same as mf_display_entries_table function, with some adjustments
	
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
			
		$element_option_lookup[$element_id][$option_id] = $row['option'];
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
			
		$matrix_element_option_lookup[$element_id][$option_id] = $row['option_label'];
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
	$column_name_lookup['date_created'] = 'Date Created';
	$column_name_lookup['date_updated'] = 'Date Updated';
	$column_name_lookup['ip_address'] 	= 'IP Address';
	
	
	$column_type_lookup['id'] 			= 'number';
	$column_type_lookup['row_num']		= 'number';
	$column_type_lookup['date_created'] = 'date';
	$column_type_lookup['date_updated'] = 'date';
	$column_type_lookup['ip_address'] 	= 'text';
	

	if($form_properties['payment_enable_merchant'] == 1){
		//get default payment columns
		$column_name_lookup['payment_amount'] = 'Payment Amount';
		$column_name_lookup['payment_status'] = 'Payment Status';
		$column_name_lookup['payment_id']	  = 'Payment ID';

		$column_type_lookup['payment_amount'] = 'money';
		$column_type_lookup['payment_status'] = 'payment_status';
		$column_type_lookup['payment_id']	  = 'text';

		//get billing address columns, if enabled
		if($form_properties['payment_ask_billing'] == 1 && empty($selected_fields)){
			$column_name_lookup['billing_street'] 	= 'Billing - Street Address';
			$column_name_lookup['billing_city'] 	= 'Billing - City';
			$column_name_lookup['billing_state'] 	= 'Billing - State';
			$column_name_lookup['billing_zipcode'] 	= 'Billing - Zip Code';
			$column_name_lookup['billing_country'] 	= 'Billing - Country';

			$column_type_lookup['billing_street'] 	= 'text';
			$column_type_lookup['billing_city'] 	= 'text';
			$column_type_lookup['billing_state'] 	= 'text';
			$column_type_lookup['billing_zipcode'] 	= 'text';
			$column_type_lookup['billing_country'] 	= 'text';
		}

		//get shipping address columns, if enabled
		if($form_properties['payment_ask_shipping'] == 1 && empty($selected_fields)){
			$column_name_lookup['shipping_street'] 	= 'Shipping - Street Address';
			$column_name_lookup['shipping_city'] 	= 'Shipping - City';
			$column_name_lookup['shipping_state'] 	= 'Shipping - State';
			$column_name_lookup['shipping_zipcode'] = 'Shipping - Zip Code';
			$column_name_lookup['shipping_country'] = 'Shipping - Country';

			$column_type_lookup['shipping_street'] 	= 'text';
			$column_type_lookup['shipping_city'] 	= 'text';
			$column_type_lookup['shipping_state'] 	= 'text';
			$column_type_lookup['shipping_zipcode'] = 'text';
			$column_type_lookup['shipping_country'] = 'text';
		}
	}
	
	
	//check form table if approval feature enabled
	if ($form_properties['form_approval_enable'] == 1) {
		$column_name_lookup['approval_state'] 	= 'Approval Status';
		$column_name_lookup['approval_note'] 	= 'Approval Note';
		
		$column_type_lookup['approval_state'] 	= 'text';
		$column_type_lookup['approval_note'] 	= 'text';
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
					 element_guidelines,
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
				$element_option_lookup[$row['element_id']]['other'] = $row['element_choice_other_label'];
				
				if($element_type == 'radio'){
					$element_radio_has_other['element_'.$row['element_id']] = true;	
				}
			}
		}


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
				$column_name_lookup['element_'.$row['element_id'].'_'.$option_id] = $option.' -- '.$row['element_title'];
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
				if(!empty($row['element_guidelines'])){
					$matrix_title = $row['element_guidelines'];
				}

				$column_name_lookup['element_'.$row['element_id']] = $row['element_title'].' -- '.$matrix_title;
				$column_type_lookup['element_'.$row['element_id']] = 'matrix_radio';
			}else{
				$this_checkbox_options = $matrix_element_option_lookup[$row['element_id']];
				
				if(!empty($row['element_guidelines'])){
					$matrix_title = $row['element_guidelines'];
				}

				foreach ($this_checkbox_options as $option_id=>$option){
					$option = $option.' -- '.$row['element_title'].' -- '.$matrix_title;
					$column_name_lookup['element_'.$row['element_id'].'_'.$option_id] = $option;
					$column_type_lookup['element_'.$row['element_id'].'_'.$option_id] = 'matrix_checkbox';
				}

			}
		}else{ //for other elements with only 1 field
			$column_name_lookup['element_'.$row['element_id']] = $row['element_title'];
			$column_type_lookup['element_'.$row['element_id']] = $row['element_type'];
		}
		
	}
	/******************************************************************************************/
			
	if(empty($selected_fields)){		
		//by default, display all columns
		$column_prefs = array_keys($column_name_lookup);
	}else{
		//otherwise, check for column preferences
		//get column preferences and store it into array
		if(!empty($incomplete_entries)){
			$incomplete_status = 1;
		}else{
			$incomplete_status = 0;
		}

		$query = "select element_name from ".MF_TABLE_PREFIX."column_preferences where form_id=? and user_id=? and incomplete_entries=? order by position asc";
		
		$params = array($form_id,$_SESSION['mf_user_id'],$incomplete_status);
		$sth = mf_do_query($query,$params,$dbh);
		while($row = mf_do_fetch_result($sth)){
			if($row['element_name'] == 'id'){
				continue;
			}
			$column_prefs[] = $row['element_name'];
		}

		if(empty($column_prefs)){
			$column_prefs = array_keys($column_name_lookup);
		}
	}

	//determine column labels
	$column_labels = array();

	$column_labels[] = 'Entry #';
		
	foreach($column_prefs as $column_name){
		$column_labels[] = $column_name_lookup[$column_name];
	}

	//get the entries from ap_form_x table and store it into array
	//but first we need to check if there is any column preferences from ap_form_payments table
	$payment_table_columns = array('payment_amount',
								   'payment_status',
								   'payment_id');
	
	if($form_properties['payment_ask_billing'] == 1){
		$payment_table_columns[] = 'billing_street';
		$payment_table_columns[] = 'billing_city';
		$payment_table_columns[] = 'billing_state';
		$payment_table_columns[] = 'billing_zipcode';
		$payment_table_columns[] = 'billing_country';
	}

	if($form_properties['payment_ask_shipping'] == 1){
		$payment_table_columns[] = 'shipping_street';
		$payment_table_columns[] = 'shipping_city';
		$payment_table_columns[] = 'shipping_state';
		$payment_table_columns[] = 'shipping_zipcode';
		$payment_table_columns[] = 'shipping_country';
	}
	
	
	$payment_columns_prefs = array_intersect($payment_table_columns, $column_prefs);

	//get approval table column
	$approval_table_columns = array ('approval_state',
									 'approval_note');
									 
	$approval_columns_prefs = array_intersect($approval_table_columns, $column_prefs);


	
	if(!empty($payment_columns_prefs) || !empty($approval_columns_prefs)){
		//there is one or more column from ap_form_payments or approval table
		//don't include this column into $column_prefs_joined variable
		$column_prefs_temp = array();
		foreach ($column_prefs as $value) {
			if (!empty($payment_columns_prefs)) {
				if(in_array($value, $payment_table_columns)){
					continue;
				}
			}
			
			if (!empty($approval_columns_prefs)) {
				if(in_array($value, $approval_table_columns)){
					continue;
				}
			}
			
			$column_prefs_temp[] = $value;
		}
		
		$column_prefs_joined = '`'.implode("`,`",$column_prefs_temp).'`';
		

		//build the query to ap_form_payments table
		$payment_table_query = '';
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

		
		//build the query to ap_form_x_approvals table
		$approval_table_query = '';
		foreach ($approval_columns_prefs as $column_name) {
				if($column_name == 'approval_state'){
					$approval_table_query .= ",ifnull((select 
													`{$column_name}` 
												 from ".MF_TABLE_PREFIX."form_{$form_id}_approvals 
												where 
													record_id=A.id
											 order by 
													 aid desc limit 1),'pending') {$column_name}";
				} else {
					$approval_table_query .= ",(select 
													`{$column_name}` 
												 from ".MF_TABLE_PREFIX."form_{$form_id}_approvals 
												where 
													record_id=A.id
											 order by 
													 aid desc limit 1) {$column_name}";
				}
		}
	}else{
		//there is no column from ap_form_payments
		$column_prefs_joined = '`'.implode("`,`",$column_prefs).'`';
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

	if(!empty($incomplete_entries)){
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
			
			//filter contain approval status
			if(!empty($approval_columns_prefs)){
				if ($element_name == 'approval_status') {
					if ($filter_condition == 'is_approved') {
						$where_operand = '=';
						$where_keyword = "'approved'";
					} else if ($filter_condition == 'is_denied') {
						$where_operand = '=';
						$where_keyword = "'denied'";
					} else if ($filter_condition == 'is_pending') {
						$where_operand = '=';
						$where_keyword = "'pending'";
					}
				}
			}
				
			if(in_array($filter_element_type, array('radio','select','matrix_radio'))){
				//these types need special steps to filter
				//we need to look into the ap_element_options first and do the filter there
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
						$where_clause_array[] = "({$element_name}  IN('{$filtered_option_id}') OR {$element_name}_other {$where_operand} {$where_keyword})"; 
					}else{
						$where_clause_array[] = "{$element_name}_other {$where_operand} {$where_keyword}";
					}
				}else{//otherwise, for the rest of the field types
					if(!empty($filtered_option_id_array)){
						$where_clause_array[] = "{$element_name}  IN('{$filtered_option_id}')"; 
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
				}else if($filter_condition == 'is_one'){
					$where_operand = '=';
					$where_keyword = "'1'";
				}else if($filter_condition == 'is_zero'){
					$where_operand = '=';
					$where_keyword = "'0'";
				}
		 		
				$where_clause_array[] = "{$element_name} {$where_operand} {$where_keyword}"; 
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

	//if there is $selected_where_clause (the user select few entries to be exported)
	//overwrite any existing where_clause
	if(!empty($selected_where_clause)){
		$where_clause = $selected_where_clause;
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

	//if zlib compression enabled, prepare the header for gzip
	$zlib_compression = ini_get('zlib.output_compression');
	if(!empty($zlib_compression)){
		header('Content-Encoding: gzip');
	}
	
	//prepare the header response, based on the export type
	if($export_type == 'xls'){
		require('lib/phpexcel/PHPExcel.php');

		$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array( 
		    'memoryCacheSize' => '8MB'
		);
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);

		$worksheet_title = substr($clean_form_name,0,30); //must be less than 31 characters
		$objPHPExcel->getActiveSheet()->setTitle($worksheet_title);

		$styleThinBlackBorderOutline = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => 'FF000000'),
				),
			),
			'font' => array(
		        'bold' => true,
		    ),
		    'fill' => array(
		        'type' => PHPExcel_Style_Fill::FILL_SOLID,
		        'startcolor' => array(
		            'argb' => 'FFB0B0B0',
		        ),
		    ),
		);

		$i=0;
		foreach ($column_labels as $label){
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 1, $label);
			$objPHPExcel->getActiveSheet()->getCellByColumnAndRow($i, 1)->getStyle()->applyFromArray($styleThinBlackBorderOutline);
			$i++;
		}

	}else if ($export_type == 'csv') {
		header("Pragma: public");
	    header("Expires: 0");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Cache-Control: public", false);
	    header("Content-Description: File Transfer");
	    header("Content-Type: text/csv");
	    header("Content-Disposition: attachment; filename=\"{$clean_form_name}.csv\"");
	        
	    $output_stream = fopen('php://output', 'w');
	    fputcsv($output_stream, $column_labels,',');
		
	}elseif ($export_type == 'txt') {
		header("Pragma: public");
	    header("Expires: 0");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Cache-Control: public", false);
	    header("Content-Description: File Transfer");
	    header("Content-Type: text/plain");
	    header("Content-Disposition: attachment; filename=\"{$clean_form_name}.txt\"");
	        
	    $output_stream = fopen('php://output', 'w');
	    fputcsv($output_stream, $column_labels,"\t");
	}
		

	$query = "select 
					`id`,
					{$column_prefs_joined}
					{$sorting_query} 
					{$payment_table_query}
					{$approval_table_query}
			    from 
			    	".MF_TABLE_PREFIX."form_{$form_id} A 
			    	{$where_clause} 
			order by 
					{$sort_element} {$sort_order}";
					
	$params = array();
	$sth = mf_do_query($query,$params,$dbh);
	$i=0;
	$row_num = 2;
		
	//prepend "id" into the column preferences
	array_unshift($column_prefs,"id");
		
	while($row = mf_do_fetch_result($sth)){
		$j=0;
		foreach($column_prefs as $column_name){
			$form_data[$i][$j] = '';
		
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
					$form_data[$i][$j] = $row[$column_name];
				}else{
					$form_data[$i][$j] = '';
				}
			}elseif($column_type_lookup[$column_name] == 'date'){ //date with format MM/DD/YYYY
				if(!empty($row[$column_name]) && ($row[$column_name] != '0000-00-00')){
					$form_data[$i][$j]  = date('M d, Y',strtotime($row[$column_name]));
				}

				if($column_name == 'date_created' || $column_name == 'date_updated'){
					$form_data[$i][$j] = $row[$column_name];
				}
			}elseif($column_type_lookup[$column_name] == 'europe_date'){ //date with format DD/MM/YYYY
					
				if(!empty($row[$column_name]) && ($row[$column_name] != '0000-00-00')){
					$form_data[$i][$j]  = date('d M Y',strtotime($row[$column_name]));
				}
			}elseif($column_type_lookup[$column_name] == 'number'){ 
				$form_data[$i][$j] = $row[$column_name];
			}elseif (in_array($column_type_lookup[$column_name],array('radio','select'))){ //multiple choice or dropdown
				$exploded = array();
				$exploded = explode('_',$column_name);
				$this_element_id = $exploded[1];
				$this_option_id  = $row[$column_name];
				
				$form_data[$i][$j] = $element_option_lookup[$this_element_id][$this_option_id] ?? '';
				
				if($column_type_lookup[$column_name] == 'radio'){
					if(isset($element_radio_has_other['element_'.$this_element_id]) && $element_radio_has_other['element_'.$this_element_id] === true && empty($form_data[$i][$j])){
						$form_data[$i][$j] = $row['element_'.$this_element_id.'_other'];
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
						
					$form_data[$i][$j] = $matrix_element_option_lookup[$this_element_id][$this_option_id] ?? '';
				}else if($matrix_type == 'checkbox'){
					if(!empty($row[$column_name])){
						$form_data[$i][$j]  = 'Checked';
					}else{
						$form_data[$i][$j]  = '';
					}
				}
			}elseif($column_type_lookup[$column_name] == 'checkbox'){
					
				if(!empty($row[$column_name])){
					if(substr($column_name,-5) == "other"){ //if this is an 'other' field, display the actual value
						$form_data[$i][$j] = $row[$column_name];
					}else{
						$form_data[$i][$j] = 'Checked';
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
					$raw_files = array();
					$raw_files = explode('|',$row[$column_name]);
					$clean_filenames = array();

					foreach($raw_files as $hashed_filename){
						$file_1 	    =  substr($hashed_filename,strpos($hashed_filename,'-')+1);
						$filename_value = substr($file_1,strpos($file_1,'-')+1);
						$clean_filenames[] = $filename_value;
					}

					$clean_filenames_joined = implode(', ',$clean_filenames);
					$form_data[$i][$j]  = $clean_filenames_joined;
				}
			}elseif($column_type_lookup[$column_name] == 'signature'){
				if(!empty($row[$column_name])){
					$signature_hash = md5($row[$column_name]);

					//encode the long query string for more readibility
					$q_string = base64_encode("form_id={$form_id}&id={$row['id']}&el={$column_name}&hash={$signature_hash}");
					
					$form_data[$i][$j] = 'http'.$ssl_suffix.'://'.$http_host.mf_get_dirname($_SERVER['PHP_SELF']).'/signature.php?q='.$q_string;
				}
			}elseif($column_type_lookup[$column_name] == 'payment_status'){
				$form_data[$i][$j] = ucfirst(strtolower($row[$column_name]));
			}else{
				$form_data[$i][$j] = $row[$column_name];
			}
			$j++;
		}

		$row_data = $form_data[$i];

		if($export_type == 'xls'){
			$col_num = 0;
			foreach ($row_data as $data){
				$data = str_replace("\r","",$data ?? '');
				
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_num, $row_num, $data);
				$col_num++;	
			}
		}elseif ($export_type == 'csv') {
			fputcsv($output_stream, $row_data,',');
		}elseif ($export_type == 'txt') {
			fputcsv($output_stream, $row_data,"\t");
		}

		unset($form_data); //clear the memory
		$i++;
		$row_num++;
	}
		
	
	//close the stream
	if($export_type == 'xls'){
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$clean_form_name.'.xlsx"');
		header('Cache-Control: max-age=0');
		
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}else{
		fclose($output_stream);
	}


?>