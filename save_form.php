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

	require('includes/common-validator.php');
	require('includes/filter-functions.php');
		
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	//check for max_input_vars
	mf_init_max_input_vars();
	
	$element_properties_array  = isset($_POST['ep']) ? mf_sanitize($_POST['ep']) : false;
	$form_id				   = isset($_POST['form_id']) ? (int) $_POST['form_id'] : false;
	$form_properties		   = isset($_POST['fp']) ? mf_sanitize($_POST['fp']) : false;
	$last_pagebreak_properties = isset($_POST['lp']) ? mf_sanitize($_POST['lp']) : false;
	$csrf_token 			   = trim($_POST['csrf_token']);

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);
	
	parse_str($_POST['el_pos'],$parse_output);
	$element_positions = $parse_output['el_pos']; //contain the positions of the elements
	unset($el_pos);

	//initialize variables
	$logic_field_enable   = 0;
	$logic_page_enable    = 0;
	$logic_email_enable   = 0;
	$logic_webhook_enable = 0;
	$logic_success_enable = 0;

	/***************************************************************************************************************/	
	/* 1. Process form properties																			   	   */
	/***************************************************************************************************************/
	
	if($form_properties['active'] == 2){
		$is_new_form = true;
	}else{
		$is_new_form = false;
	}
	
	foreach ($form_properties as $key=>$value){
		
		if($key == 'schedule_start_hour' || $key == 'schedule_end_hour'){
			
			$exploded = array();
			$exploded = explode(':', $value);
			
			$hour_value   = $exploded[0]; 
			$minute_value = $exploded[1]; 
			$am_pm_value  = $exploded[2];
			
			$value = date("H:i:s",strtotime("{$hour_value}:{$minute_value} {$am_pm_value}"));
			
		}

		$form_input['form_'.$key] = $value;
	}
		
	//If this is new form, create the form table and form folder+css
   	$params = array();
   	if($is_new_form){
   		//check user privileges, is this user has privilege to create new form?
		if(empty($_SESSION['mf_user_privileges']['priv_new_forms'])){
			die('{ "status" : "error","message" : "Access Denied. You don\'t have permission to create new forms."}');
		}

		//get default form theme
		$query = "SELECT default_form_theme_id FROM ".MF_TABLE_PREFIX."settings";
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		$default_form_theme_id = (int) $row['default_form_theme_id'];


   		//update form status to 1 and set default theme
   		$form_input['form_active'] 		 = 1;
   		$form_input['form_theme_id'] 	 = $default_form_theme_id;
   		$form_input['form_created_by']   = $_SESSION['mf_user_id'];
   		$form_input['form_created_date'] = date("Y-m-d");

		mf_ap_forms_update($form_id,$form_input,$dbh);

		
		//create new table for this form
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_{$form_id}` (
  													`id` int(11) NOT NULL auto_increment,
  													`date_created` datetime default NULL,
  													`date_updated` datetime default NULL,
  													`ip_address` varchar(15) default NULL,
  													`status` int(4) unsigned NOT NULL DEFAULT '1',
  													`resume_key` varchar(64) default NULL,
  													`edit_key` varchar(64) default NULL,
  													PRIMARY KEY (`id`),
  													UNIQUE KEY `edit_key` (`edit_key`),
  													KEY `ip_address` (`ip_address`),
  													KEY `status` (`status`),
  													KEY `date_created` (`date_created`)
  													) DEFAULT CHARACTER SET utf8;";
		$params = array();
		mf_do_query($query,$params,$dbh);

		//create the log table
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_{$form_id}_log` (
												  `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
												  `record_id` int(11) NOT NULL DEFAULT '0',
												  `log_time` datetime NOT NULL,
												  `log_user` text NOT NULL,
												  `log_origin` text NOT NULL,
												  `log_message` text NOT NULL,
												  PRIMARY KEY (`log_id`),
												  KEY `record_id` (`record_id`)
												) DEFAULT CHARSET=utf8;";
		$params = array();
		mf_do_query($query,$params,$dbh);

		//the default is not to store file upload as blob, unless defined otherwise within config.php file
		defined('MF_STORE_FILES_AS_BLOB') or define('MF_STORE_FILES_AS_BLOB',false);
		
		if(MF_STORE_FILES_AS_BLOB === true){
			//create the table to store file uploads
			$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_{$form_id}_files` (
	  													  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
														  `file_name` text NOT NULL,
														  `file_content` longblob,
														  PRIMARY KEY (`id`),
														  KEY `file_name` (`file_name`(255))
	  													) DEFAULT CHARACTER SET utf8;";
			$params = array();
			mf_do_query($query,$params,$dbh);
		}	
		
		//the 'status' column on the form table above has 3 possible values:
		//0 - deleted, 1 - live, 2 - draft/incomplete
		
		//create data folder for this form
		if(is_writable($mf_settings['data_dir'])){
			
			$old_mask = umask(0);
			if(!file_exists($mf_settings['data_dir']."/form_{$form_id}")){
				mkdir($mf_settings['data_dir']."/form_{$form_id}",0755);
			}

			mkdir($mf_settings['data_dir']."/form_{$form_id}/css",0755);
			if($mf_settings['data_dir'] != $mf_settings['upload_dir']){
				@mkdir($mf_settings['upload_dir']."/form_{$form_id}",0755);
			}
			mkdir($mf_settings['upload_dir']."/form_{$form_id}/files",0755);
			@file_put_contents($mf_settings['upload_dir']."/form_{$form_id}/files/index.html",' '); //write empty index.html
			
			//copy default view.css to css folder
			if(copy("./view.css",$mf_settings['data_dir']."/form_{$form_id}/css/view.css")){
				//on success update 'form_has_css' field on ap_forms table
				$form_update_input['form_has_css'] = 1;
				mf_ap_forms_update($form_id,$form_update_input,$dbh);
			}
			
			umask($old_mask);
		}
   		
   	}else{ //If this is old form, only update ap_forms table

   		//make sure the form really exist first
   		$query = "select 
		   				form_id,
						logic_field_enable,
						logic_page_enable,
						logic_email_enable,
						logic_webhook_enable,
						logic_success_enable 
					from 
						`".MF_TABLE_PREFIX."forms` where form_id = ?";
   		$params = array($form_id);
   		
   		$sth = mf_do_query($query,$params,$dbh);
   		$row = mf_do_fetch_result($sth);

   		if(!empty($row)){
	   		$result = mf_ap_forms_update($form_id,$form_input,$dbh);		
			check_result($result);

			//get logic statuses
			$logic_field_enable   = (int) $row['logic_field_enable'];
			$logic_page_enable    = (int) $row['logic_page_enable'];
			$logic_email_enable   = (int) $row['logic_email_enable'];
			$logic_webhook_enable = (int) $row['logic_webhook_enable'];
			$logic_success_enable = (int) $row['logic_success_enable'];
		}else{
			die('{ "status" : "error","message" : "Unknown form id"}');
		}
		
   	}
	
	/***************************************************************************************************************/	
	/* 2. Process fields																					   	   */
	/***************************************************************************************************************/
   	
   	// 2.1 Process new fields
   	//Get the new fields from ap_form_elements table with status = 2, change the status to 1 and create the field column into the form's table
	$query = "SELECT 
   					element_id,
   					element_matrix_allow_multiselect 
   				FROM 
   					".MF_TABLE_PREFIX."form_elements 
   			   WHERE 
   			   		form_id = ? and element_type='matrix' and element_matrix_parent_id=0";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		$matrix_multiselect_settings[$row['element_id']] = $row['element_matrix_allow_multiselect'];
	}
	
	
	$matrix_child_array = array();
	$query = "SELECT 
   					element_id, element_type,
   					element_constraint,element_position,
   					element_matrix_parent_id,
   					element_matrix_allow_multiselect,
   					element_choice_has_other 
   				FROM 
   					".MF_TABLE_PREFIX."form_elements 
   			   WHERE 
   			   		form_id = ? and element_status=2 
   			ORDER BY 
   					element_position asc";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);
	
   	while($row = mf_do_fetch_result($sth)){
		$element_type = $row['element_type'];
		$element_id	  = $row['element_id'];
		$element_matrix_parent_id 		  = $row['element_matrix_parent_id'];
		$element_matrix_allow_multiselect = $row['element_matrix_allow_multiselect'];
		$element_choice_has_other 		  = $row['element_choice_has_other'];
		
		if($element_type == 'checkbox'){
			//get all child element of the checkboxes
			$query = "select option_id from ".MF_TABLE_PREFIX."element_options where form_id = ? and element_id = ? and live >= 1 order by option_id asc";
			$params_checkbox = array($form_id,$element_id);
			
			$sth2 = mf_do_query($query,$params_checkbox,$dbh);
			while($row2 = mf_do_fetch_result($sth2)){
				table_add_field($dbh,$form_id,$element_id,$element_type,$row2['option_id']);
			}
		}elseif($element_type == 'matrix'){
			//if the parent_id of this matrix has 'element_status' 1, skip it
			$query  = "select element_status from `".MF_TABLE_PREFIX."form_elements` where form_id = ? and element_id = ?";
			$params_mp = array($form_id,$element_matrix_parent_id);
			
			$sth_mp = mf_do_query($query,$params_mp,$dbh);
			$row_mp = mf_do_fetch_result($sth_mp);
			
			if(isset($row_mp['element_status']) && $row_mp['element_status'] == 1){
				continue;
			}
			
			//a matrix field can be a group of multiple choices or checkboxes
			//determine the matrix type
			if(empty($element_matrix_parent_id)){ //if this is the first row of the matrix
				$matrix_allow_multiselect = $element_matrix_allow_multiselect;
			}else{
				$matrix_allow_multiselect = $matrix_multiselect_settings[$element_matrix_parent_id];
				$matrix_child_array[$element_matrix_parent_id][] = $element_id; 
			}
		
			if(!empty($matrix_allow_multiselect)){ //if this is checkboxes matrix
				//get all child element of the checkboxes
				$query = "select option_id from ".MF_TABLE_PREFIX."element_options where form_id = ? and element_id = ? and live >= 1 order by option_id asc";
				$params3 = array($form_id,$element_id);
				
				$sth3 = mf_do_query($query,$params3,$dbh);
				while($row3 = mf_do_fetch_result($sth3)){
					table_add_field($dbh,$form_id,$element_id,'checkbox',$row3['option_id']);
				}
			}else{ //if this is multiple choice matrix
				table_add_field($dbh,$form_id,$element_id,'radio');
			}
		}else{ //other field types
			table_add_field($dbh,$form_id,$element_id,$element_type);
		}
		
		//check for 'other' field into checkboxes and multiple choices field
		//if the 'other' field is active, make sure to add the 'other' column into the table
		if ($element_type == 'checkbox' || $element_type == 'radio'){
			if(!empty($element_choice_has_other)){
				//add the 'other' field into the table, but check first, just in case the field already exist
				if(!mf_mysql_column_exist(MF_TABLE_PREFIX."form_{$form_id}","element_{$element_id}_other",$dbh)){
					$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD COLUMN `element_{$element_id}_other` text NULL COMMENT 'Choice - Other';";
					mf_do_query($query,array(),$dbh);
				}
			}
		}
	}

	//update ap_form_elements set status to 1
	$query = "update `".MF_TABLE_PREFIX."form_elements` set `element_status` = 1 where form_id = ? and element_status=2";
	$params = array($form_id);
	mf_do_query($query,$params,$dbh);
			
	//update ap_element_options set status to 1
	$query = "update `".MF_TABLE_PREFIX."element_options` set `live` = 1 where form_id = ? and live=2";
	mf_do_query($query,array($form_id),$dbh);
	
	//update matrix 'constraint' with the child ids
	if(!empty($matrix_child_array)){
		foreach($matrix_child_array as $m_parent_id=>$m_child_id_array){
			$m_child_id = implode(',',$m_child_id_array);
			
			$query = "update `".MF_TABLE_PREFIX."form_elements` set `element_constraint` = ? where form_id = ? and element_id = ?";
			$params = array($m_child_id,$form_id,$m_parent_id);
			mf_do_query($query,$params,$dbh);
		}
	}
	
	//2.2 Process old field
	//Get the old fields parameters from the ajax post
	
	$matrix_child_array = array();
	
	//loop through each element properties
	if(!empty($element_properties_array)){
		foreach($element_properties_array as $element_properties){
			
			$element_type = $element_properties['type'];
			$element_id	  = $element_properties['id'];
			
			unset($element_properties['is_db_live']);
			unset($element_properties['last_option_id']); //this property exist for choices field type
			unset($element_properties['deletion_confirmed']);

			$element_options = array();
			$element_options = isset($element_properties['options']) ? $element_properties['options'] : false;
			unset($element_properties['options']); 
			
			//2.2.1 Synch into ap_element_options table
			//This is only necessary for multiple choice, checkboxes, dropdown and matrix field
			if(!empty($element_options)){
				if(in_array($element_type,array('radio','checkbox','select'))){
					//set the live property of the options within ap_element_options to 0
					$query = "update `".MF_TABLE_PREFIX."element_options` set `live`=0 where form_id = ? and element_id = ?";
					$params = array($form_id,$element_id);
					mf_do_query($query,$params,$dbh);
					
					//there are 3 possibilities, new choice being added, old choice being deleted, old choice being updated
					//we need to handle all of those. update the ap_element_options and update the form's table as well
					foreach($element_options as $option_id=>$value){
							if(empty($value['is_db_live'])){ //this is new choice
								$query = "INSERT INTO 
													`".MF_TABLE_PREFIX."element_options` 
													(`form_id`,`element_id`,`option_id`,`position`,`option`,`option_is_default`,`option_is_hidden`,`live`) 
										   VALUES (?,?,?,?,?,?,?,'1');"; 
								$params = array($form_id,$element_id,$option_id,$value['position'],$value['option'],$value['is_default'],$value['is_hidden']);
								mf_do_query($query,$params,$dbh);
								
								//if this is checkbox and a new choice is being added, add a column into the form's table
								if($element_type == 'checkbox'){
									table_add_field($dbh,$form_id,$element_id,$element_type,$option_id);
								}
							}else{ //update existing choice
								//if the form has logics or report filters enabled, we need to update the choice labels being used for the logic first
								$is_report_filter_enabled = false;

								$query = "select count(*) total_row from `".MF_TABLE_PREFIX."report_filters` where form_id=?";
								$params = array($form_id);
								$sth = mf_do_query($query,$params,$dbh);
								$row = mf_do_fetch_result($sth);

								if(!empty($row['total_row'])){
									$is_report_filter_enabled = true;
								}

								$old_option = '';
								if(!empty($logic_field_enable) || !empty($logic_page_enable) || !empty($logic_email_enable) || !empty($logic_webhook_enable) || !empty($logic_success_enable) || !empty($is_report_filter_enabled)){
									//get existing choice label
									$query = "SELECT 
													`option` 
												FROM  
													`".MF_TABLE_PREFIX."element_options` 
											   WHERE 
													form_id = ? and element_id = ? and `option_id` = ?";
									$params = array($form_id,$element_id,$option_id);
									$sth = mf_do_query($query,$params,$dbh);
									$row = mf_do_fetch_result($sth);
									if(!empty($row['option'])){
										$old_option = $row['option'];
									}
								}

								//update choice option on ap_field_logic_conditions table
								if(!empty($logic_field_enable)){
									$query = "UPDATE 
													`".MF_TABLE_PREFIX."field_logic_conditions` 
												SET 
													`rule_keyword` = ?  
											WHERE 
													form_id = ? and 
													element_name = ? and 
													rule_keyword = ? and 
													rule_condition in('is','is_not')";
									$params = array($value['option'],$form_id,'element_'.$element_id,$old_option);
									mf_do_query($query,$params,$dbh);
								}

								//update choice option on ap_page_logic_conditions table
								if(!empty($logic_page_enable)){
									$query = "UPDATE 
													`".MF_TABLE_PREFIX."page_logic_conditions` 
												SET 
													`rule_keyword` = ?  
											WHERE 
													form_id = ? and 
													element_name = ? and 
													rule_keyword = ? and 
													rule_condition in('is','is_not')";
									$params = array($value['option'],$form_id,'element_'.$element_id,$old_option);
									mf_do_query($query,$params,$dbh);
								}

								//update choice option on ap_email_logic_conditions table
								if(!empty($logic_email_enable)){
									$query = "UPDATE 
													`".MF_TABLE_PREFIX."email_logic_conditions` 
												SET 
													`rule_keyword` = ?  
											WHERE 
													form_id = ? and 
													element_name = ? and 
													rule_keyword = ? and 
													rule_condition in('is','is_not')";
									$params = array($value['option'],$form_id,'element_'.$element_id,$old_option);
									mf_do_query($query,$params,$dbh);
								}

								//update choice option on ap_webhook_logic_conditions table
								if(!empty($logic_webhook_enable)){
									$query = "UPDATE 
													`".MF_TABLE_PREFIX."webhook_logic_conditions` 
												SET 
													`rule_keyword` = ?  
											WHERE 
													form_id = ? and 
													element_name = ? and 
													rule_keyword = ? and 
													rule_condition in('is','is_not')";
									$params = array($value['option'],$form_id,'element_'.$element_id,$old_option);
									mf_do_query($query,$params,$dbh);
								}

								//update choice option on ap_success_logic_conditions table
								if(!empty($logic_success_enable)){
									$query = "UPDATE 
													`".MF_TABLE_PREFIX."success_logic_conditions` 
												SET 
													`rule_keyword` = ?  
											WHERE 
													form_id = ? and 
													element_name = ? and 
													rule_keyword = ? and 
													rule_condition in('is','is_not')";
									$params = array($value['option'],$form_id,'element_'.$element_id,$old_option);
									mf_do_query($query,$params,$dbh);
								}

								//update choice option on ap_report_filters table
								if(!empty($is_report_filter_enabled)){
									$query = "UPDATE 
													`".MF_TABLE_PREFIX."report_filters` 
												SET 
													`filter_keyword` = ?  
											WHERE 
													form_id = ? and 
													element_name = ? and 
													filter_keyword = ? and 
													filter_condition in('is','is_not')";
									$params = array($value['option'],$form_id,'element_'.$element_id,$old_option);
									mf_do_query($query,$params,$dbh);
								}

								//finally, update existing choice
								$query = "UPDATE 
												`".MF_TABLE_PREFIX."element_options` 
											 SET 
											 	`live`=1,`option` = ?,`option_is_default` = ?,`position` = ?, `option_is_hidden` = ?  
										   WHERE 
										   		form_id = ? and element_id = ? and `option_id` = ?";
								$params = array($value['option'],$value['is_default'],$value['position'],$value['is_hidden'],$form_id,$element_id,$option_id);
								mf_do_query($query,$params,$dbh);
							}
						
					}
				}else if($element_type == 'matrix'){
					
					//get the correct constraint (child ids), make sure it include all new rows added to existing matrix field as well
					$matrix_constraint_array = array();
					foreach ($element_options as $m_element_id=>$value){
						if($m_element_id == $element_properties['id']){ //if this the first row of the matrix
							continue; //skip first row
						}

					   	$child_position = $value['position'];
					   	$matrix_child_array[$element_properties['id']][$child_position] = $m_element_id;

					   	foreach($matrix_child_array as $m_parent_id=>$m_child_id_array){
							ksort($m_child_id_array); //sort the matrix child based on position
							$m_child_id = implode(',',$m_child_id_array);
							$matrix_constraint_array[$element_id] = $m_child_id;
					  	}
					}
					
					if(!empty($matrix_constraint_array[$element_properties['id']])){
						$element_properties['constraint'] = $matrix_constraint_array[$element_properties['id']];
					}
					
					$matrix_all_row_ids = array();
					$matrix_all_row_ids = explode(',',$element_properties['constraint']);
					$matrix_all_row_ids[] = $element_properties['id'];
					
					$matrix_all_row_ids_placeholder = implode(',',array_pad(array(),count($matrix_all_row_ids),'?'));
					
					//first 'delete' all matrix rows and columns by setting the live property to 0
					$query = "update `".MF_TABLE_PREFIX."element_options` set `live`=0 where form_id = ? and element_id in({$matrix_all_row_ids_placeholder})";
					$params = array_merge((array)$form_id,$matrix_all_row_ids);
					mf_do_query($query,$params,$dbh);
							
					$query = "update `".MF_TABLE_PREFIX."form_elements` set `element_status`=0 where form_id = ? and element_id in({$matrix_all_row_ids_placeholder})";
					$params = array_merge((array)$form_id,$matrix_all_row_ids);
					mf_do_query($query,$params,$dbh);
					
					//process the first row of the matrix
					$first_row_matrix_data = array();
					$first_row_matrix_data = $element_options[$element_properties['id']];
					
					$query = "update `".MF_TABLE_PREFIX."form_elements` set `element_status`=1 where form_id=? and element_id=?";
					$params = array($form_id,$element_properties['id']);
					mf_do_query($query,$params,$dbh);
					
					//update/insert column data
					$matrix_column_data = array();
					$matrix_column_data = $first_row_matrix_data['column_data'];
					
					foreach ($matrix_column_data as $c_option_id=>$value){
						if(empty($value['is_db_live'])){ //this is new column, add the column 
							//insert into ap_element_options table, for all rows
							foreach ($matrix_all_row_ids as $m_row_element_id){
								$query = "INSERT INTO 
													`".MF_TABLE_PREFIX."element_options` 
													(`form_id`,`element_id`,`option_id`,`position`,`option`,`option_is_default`,`live`) 
										   	  VALUES 
										   	  		(?,?,?,?,?,'0','1')"; 
								$params = array($form_id,$m_row_element_id,$c_option_id,$value['position'],$value['column_title']);
								mf_do_query($query,$params,$dbh);
							
								//if this is checkbox matrix, add the column into the form's table as well
								if(!empty($element_properties['matrix_allow_multiselect'])){
									table_add_field($dbh,$form_id,$m_row_element_id,'checkbox',$c_option_id);
								}
							}
						}else{ //this is old column simply update the table
							$query = "UPDATE 
											`".MF_TABLE_PREFIX."element_options`
									     SET
									     	`live`=1,`position` = ?, `option` = ?
									   WHERE
									   		form_id = ? and element_id = ? and `option_id` = ?";
							$params = array($value['position'],$value['column_title'],$form_id,$element_properties['id'],$c_option_id);
							mf_do_query($query,$params,$dbh);
						}
					}
					
					//loop through other matrix rows
				
					foreach ($element_options as $m_element_id=>$value){
						if($m_element_id == $element_properties['id']){ //if this the first row of the matrix
							continue; //skip first row, we already process it above
						}
						
						$child_position = $value['position'];
						$matrix_child_array[$element_properties['id']][$child_position] = $m_element_id;
						
						if(empty($value['is_db_live'])){ //this is new row
							//update the status on ap_form_elements table
							$query = "update `".MF_TABLE_PREFIX."form_elements` set `element_status`=1 where form_id = ? and element_id = ?";
							$params = array($form_id,$m_element_id);
							mf_do_query($query,$params,$dbh);
							
							//update the status on ap_element_options table
							$query = "update `".MF_TABLE_PREFIX."element_options` set `live`=1 where form_id = ? and element_id = ?";
							$params = array($form_id,$m_element_id);
							mf_do_query($query,$params,$dbh);
							
							//add the new fields into the form's table
							if(empty($element_properties['matrix_allow_multiselect'])){ //if this is radio buttons matrix
								table_add_field($dbh,$form_id,$m_element_id,'radio');
							}else{ //this is checkboxes matrix
								//get all child element using the first row column data
								foreach ($matrix_column_data as $c_option_id=>$value){
									table_add_field($dbh,$form_id,$m_element_id,'checkbox',$c_option_id);
								}
							}
						}else{ //this is an existing row, just update
							$query = "update `".MF_TABLE_PREFIX."form_elements` set `element_status`=1 where form_id = ? and element_id = ?";
							$params = array($form_id,$m_element_id);
							mf_do_query($query,$params,$dbh);
							
							foreach ($matrix_column_data as $c_option_id=>$value){
								if(!empty($value['is_db_live'])){ 
										$query = "UPDATE 
														`".MF_TABLE_PREFIX."element_options`
												     SET
												     	`live`=1,`position` = ?,`option` = ?
												   WHERE
												   		form_id = ? and element_id = ? and `option_id` = ?";
										$params = array($value['position'],$value['column_title'],$form_id,$m_element_id,$c_option_id);
										mf_do_query($query,$params,$dbh);
								}
							}
							
						}
					} 
				}
			}
			

			//2.2.2 Synch into ap_form_elements table				
			$update_values = '';
			$params = array();
			
			$element_properties['status'] = 1;
			
			//dynamically create the sql update string, based on the input given
			foreach ($element_properties as $key=>$value){
				
				if($value == "null"){
					$value = null;
				}
				
				$update_values .= "`element_{$key}`= :element_{$key},";
				$params[':element_'.$key] = $value;
			}
			$update_values = rtrim($update_values,',');
			
			$query = "UPDATE `".MF_TABLE_PREFIX."form_elements` set 
										$update_values
								  where 
							  	  		form_id = :form_id and element_id = :w_element_id";
										
			$params[':form_id'] = $form_id;
			$params[':w_element_id'] = $element_properties['id'];
			
			mf_do_query($query,$params,$dbh);
			
			//if this is matrix field, the element title need to be updated again from the options, the position as well
			if($element_properties['type'] == 'matrix'){
				
				$query = "UPDATE 
								`".MF_TABLE_PREFIX."form_elements` 
							 SET 
								`element_title` = :element_title,
								`element_position` = :element_position		
						   WHERE 
								form_id = :form_id and element_id = :element_id";

				
				foreach ($element_options as $m_element_id=>$value){
					
					$params = array();
					$params[':element_title'] 		= $value['row_title'];
					$params[':element_position']	= $value['position'];
					$params[':form_id']				= $form_id;
					$params[':element_id']			= $m_element_id;
					
					mf_do_query($query,$params,$dbh);	
				} //end foreach element_options
			}
			
			//check for 'other' field into checkboxes and multiple choices field
			//if the 'other' field is active, make sure to add the 'other' column into the table
			if ($element_type == 'checkbox' || $element_type == 'radio'){
				
				if(!empty($element_properties['choice_has_other'])){
					//add the 'other' field into the table, but check first, just in case the field already exist
					if(!mf_mysql_column_exist(MF_TABLE_PREFIX."form_{$form_id}","element_{$element_id}_other",$dbh)){
						$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD COLUMN `element_{$element_id}_other` text NULL COMMENT 'Choice - Other';";
						mf_do_query($query,array(),$dbh);
					}
				}
			}
			
			
		} //end foreach element properties
		
		//update matrix 'constraint' with the child ids
		if(!empty($matrix_child_array)){
			foreach($matrix_child_array as $m_parent_id=>$m_child_id_array){
				ksort($m_child_id_array); //sort the matrix child based on position
				$m_child_id = implode(',',$m_child_id_array);
				$query = "update `".MF_TABLE_PREFIX."form_elements` set `element_constraint` = ? where form_id = ? and element_id = ?";
				$params = array($m_child_id,$form_id,$m_parent_id);
				mf_do_query($query,$params,$dbh);	
			}
		}
	} //end !empty element properties
	
	/***************************************************************************************************************/	
	/* 3. Additional calculations on ap_form_elements table														   */
	/***************************************************************************************************************/
	
	// 3.1 Calculate element positions, each matrix row is considered as separate field
	
	//first create a list of matrix fields on the current form, get the parent matrix only
	$matrix_parent_constraint = array();
	$query = "SELECT 
					element_id, 
					element_constraint 
				FROM 
					`".MF_TABLE_PREFIX."form_elements` 
			   WHERE 
			   		form_id = ? and 
			   		element_type='matrix' and 
			   		element_status=1 and
			   		element_matrix_parent_id=0 
		    ORDER BY 
		    		element_position asc";
	
	$sth = mf_do_query($query,array($form_id),$dbh);
	while($row = mf_do_fetch_result($sth)){
		$matrix_parent_constraint[$row['element_id']] = trim($row['element_constraint']);
	}
	
	$element_final_position = array();
	foreach ($element_positions as $element_id){
		$matrix_childs = '';
		
		$element_final_position[] = $element_id;
		$matrix_childs = isset($matrix_parent_constraint[$element_id]) ? $matrix_parent_constraint[$element_id] : false;
		
		if(!empty($matrix_childs)){
			$matrix_childs_array = array();
			$matrix_childs_array = explode(",",$matrix_childs);
			
			foreach ($matrix_childs_array as $child_element_id){
				$element_final_position[] = $child_element_id;
			}
		}
	}

	//update position into ap_form_elements table
	foreach ($element_final_position as $position=>$element_id){
		$query = "update `".MF_TABLE_PREFIX."form_elements` set element_position = ? where form_id = ? and element_id = ?";
		$params = array($position,$form_id,$element_id);
		mf_do_query($query,$params,$dbh);
	}
	
	// 3.2 Calculate element page number
	$query = "SELECT 
					element_id,element_position 
				FROM 
					".MF_TABLE_PREFIX."form_elements 
			   WHERE 
			   		form_id = ? and element_type='page_break' and element_status=1 
			ORDER BY 
					element_position asc";
	$params = array($form_id);
		
	$sth = mf_do_query($query,$params,$dbh);
	$page_number = 1;
	while($row = mf_do_fetch_result($sth)){
		$page_break_list[$page_number] = $row['element_position'];
		$page_number++;
	}
		
	$total_page = $page_number;
	if(!empty($page_break_list)){
		krsort($page_break_list);
	}
		
	//set the page number of all fields to the highest page number
	$query = "UPDATE 
					".MF_TABLE_PREFIX."form_elements 
				 SET 
					element_page_number = ?
			   WHERE
				    form_id = ? and element_status=1";
	$params = array($total_page,$form_id);
	mf_do_query($query,$params,$dbh);
		
	//then loop through each page break and set the page number of all fields below that page break
	if(!empty($page_break_list)){
		$query = "UPDATE 
						".MF_TABLE_PREFIX."form_elements 
					 SET 
					element_page_number = ?
				   WHERE
					   	form_id = ? and element_status=1 and element_position <= ?";
		foreach ($page_break_list as $page_number=>$position){
			$params = array($page_number,$form_id,$position);
			mf_do_query($query,$params,$dbh);
		}
	}
	
	//3.3 Make sure that all elements which have "range" properties doesn't have "range min" which is greater than "range max"
	$query = "update ".MF_TABLE_PREFIX."form_elements set element_range_min=0 where form_id = ? and element_range_min > element_range_max and element_range_max > 0";
	$params = array($form_id);
	mf_do_query($query,$params,$dbh);
	
	/***************************************************************************************************************/	
	/* 4. Additional calculations on ap_forms table														  		   */
	/***************************************************************************************************************/

	//Set form properties which related with multipage
	if(!empty($last_pagebreak_properties)){
		
		$last_pagebreak_properties['submit_use_image'] = isset($last_pagebreak_properties['submit_use_image']) ? (int) $last_pagebreak_properties['submit_use_image'] : 0;
		$last_pagebreak_properties['submit_primary_img'] = isset($last_pagebreak_properties['submit_primary_img']) ? $last_pagebreak_properties['submit_primary_img'] : '';
		$last_pagebreak_properties['submit_secondary_img'] = isset($last_pagebreak_properties['submit_secondary_img']) ? $last_pagebreak_properties['submit_secondary_img'] : '';

		if($last_pagebreak_properties['submit_primary_img'] === "null"){
			$last_pagebreak_properties['submit_primary_img'] = null;
		}
		if($last_pagebreak_properties['submit_secondary_img'] === "null"){
			$last_pagebreak_properties['submit_secondary_img'] = null;
		}

		$query = "UPDATE 
						".MF_TABLE_PREFIX."forms 
					 SET 
					 	form_page_total=?,form_lastpage_title=?,form_submit_primary_text=?,
					 	form_submit_secondary_text=?,form_submit_primary_img=?,
					 	form_submit_secondary_img=?,form_submit_use_image=? 
				   WHERE 
				   		form_id=?";
		$params = array($total_page,$last_pagebreak_properties['page_title'],$last_pagebreak_properties['submit_primary_text'],
						$last_pagebreak_properties['submit_secondary_text'],$last_pagebreak_properties['submit_primary_img'],
						$last_pagebreak_properties['submit_secondary_img'],$last_pagebreak_properties['submit_use_image'],
						$form_id);
		mf_do_query($query,$params,$dbh);
	}else if($total_page === 1){//if this is just a single page form
		$query = "update ".MF_TABLE_PREFIX."forms set form_page_total=1 where form_id=?";
		mf_do_query($query,array($form_id),$dbh);
	}
	
	/***************************************************************************************************************/	
	/* 5. Insert into permissions table																			   */
	/***************************************************************************************************************/
	
	if($is_new_form){
		$query = "delete from ".MF_TABLE_PREFIX."permissions where form_id=? and user_id=?";
		$params = array($form_id,$_SESSION['mf_user_id']);
		mf_do_query($query,$params,$dbh);

		$query = "insert into ".MF_TABLE_PREFIX."permissions(form_id,user_id,edit_form,edit_report,edit_entries,view_entries) values(?,?,1,1,1,1)";
		$params = array($form_id,$_SESSION['mf_user_id']);
		mf_do_query($query,$params,$dbh);
	}

	/***************************************************************************************************************/	
	/* 6. Create approval table (if any)																		   */
	/***************************************************************************************************************/

	//if 'Enable Approval Workflow' turned on, create the 'ap_form_xxx_approvals' table and add approval columns
	//to ap_form_xxx table
	if(!empty($form_properties['approval_enable'])){

		//check if ap_form_xxx_approvals already exist or not
		$is_approval_table_exist = true;
		try{
			$params = array();

			$query = "select count(*) from ".MF_TABLE_PREFIX."form_{$form_id}_approvals";
			$sth = $dbh->prepare($query);

			$sth->execute($params);
		}catch(PDOException $e) {
			$is_approval_table_exist = false;
		}

		if($is_approval_table_exist === false){
			
			//create table ap_form_xxx_approvals
			$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_{$form_id}_approvals` (
								  `aid` int(11) unsigned NOT NULL AUTO_INCREMENT,
								  `date_created` datetime NOT NULL,
								  `record_id` int(11) NOT NULL,
								  `user_id` int(11) NOT NULL,
								  `ip_address` varchar(15) DEFAULT NULL,
								  `approval_state` varchar(11) NOT NULL DEFAULT '' COMMENT 'approved,denied',
								  `approval_note` text,
								  PRIMARY KEY (`aid`))";
			mf_do_query($query,array(),$dbh);

			//add 'approval_status' and 'approval_queue_user_id' column
			$query = "ALTER TABLE 
								`".MF_TABLE_PREFIX."form_{$form_id}` 
					   ADD COLUMN `approval_status` varchar(11) NOT NULL DEFAULT 'pending' COMMENT 'pending,approved,denied',
  					   ADD COLUMN `approval_queue_user_id` text";
			mf_do_query($query,array(),$dbh);

			//if this is the first time approval being enabled and the form already have existing records
			//set all existing records status to approved
			$query = "UPDATE ".MF_TABLE_PREFIX."form_{$form_id} SET approval_status = 'approved'";
			mf_do_query($query,array(),$dbh);
		}
	}

	/***************************************************************************************************************/	
	/* 7. Process form review (on/off)																			   */
	/***************************************************************************************************************/

	//every time we save the form, the review table will be deleted
	//it needs to be created again when one of the following conditions happened:
	// 1) form review enabled
	// 2) the form has multiple pages
	// 3) the 'save and resume' option of the form is enabled
	// 4) the 'approval workflow' option of the form is enabled
	
	//delete review table if exists
	$query = "DROP TABLE IF EXISTS `".MF_TABLE_PREFIX."form_{$form_id}_review`";
	mf_do_query($query,array(),$dbh);
	
	//create review table
	if(!empty($form_properties['review']) || !empty($last_pagebreak_properties) || !empty($form_properties['resume_enable']) || !empty($form_properties['approval_enable'])){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_{$form_id}_review` like `".MF_TABLE_PREFIX."form_{$form_id}`";
		mf_do_query($query,array(),$dbh);
		
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}_review` ADD COLUMN `session_id` varchar(128) NULL";
		mf_do_query($query,array(),$dbh);
	}

	/***************************************************************************************************************/	
	/* 8. Update ap_integrations table																			   */
	/***************************************************************************************************************/

	//Each time we update the form, Google Sheets integration must create a new sheet to keep columns consistency
	$query = "update ".MF_TABLE_PREFIX."integrations set gsheet_create_new_sheet=1 where form_id=?";
	$params = array($form_id);
	mf_do_query($query,$params,$dbh);

	/***************************************************************************************************************/	
	/* 9. Unlock the form																						   */
	/***************************************************************************************************************/
	
	$query = "delete from ".MF_TABLE_PREFIX."form_locks where form_id=?";
	$params = array($form_id);
	mf_do_query($query,$params,$dbh);

   	
   	echo '{ "status" : "ok", "form_id" : "'.$form_id.'" }';
	
   	
   	/***************************************************************************************************************/	
	/* Functions																								   */
	/***************************************************************************************************************/
   	
   	function check_result($result){
		if($result !== true){
			if(!is_array($result)){ //if one line error message
				$error = '{ "status" : "error","message" : "'.$result.'"}';
				echo $error;
			}
		}
	}
	
	//add fields to the specified form table
	function table_add_field($dbh,$form_id,$element_id,$type,$option_id=0){
		$comment_desc['text'] 		= 'Single Line Text';
		$comment_desc['phone'] 		= 'Phone';
		$comment_desc['simple_phone'] = 'Phone';
		$comment_desc['url'] 		= 'Web Site';
		$comment_desc['email'] 		= 'Email';
		$comment_desc['file'] 		= 'File Upload';
		$comment_desc['textarea'] 	= 'Paragraph Text';
		$comment_desc['radio'] 		= 'Multiple Choice';
		$comment_desc['select'] 	= 'Drop Down';
		$comment_desc['time'] 		= 'Time';
		$comment_desc['date'] 		= 'Date';
		$comment_desc['europe_date'] = 'Europe Date';
		$comment_desc['money'] 		 = 'Price';
		$comment_desc['number'] 	 = 'Number';
		$comment_desc['simple_name'] = 'Normal Name';
		$comment_desc['simple_name_wmiddle'] = 'Normal Name with Middle';
		$comment_desc['name'] 		 		 = 'Extended Name';
		$comment_desc['name_wmiddle'] 		 = 'Extended Name with Middle';
		$comment_desc['address'] 	 = 'Address';
		$comment_desc['checkbox'] 	 = 'Checkbox';
		$comment_desc['signature'] 	 = 'Signature';
		$comment_desc['rating'] 	 = 'Rating';
		
		$comment = @$comment_desc[$type];
			
		if(('text' == $type) || ('phone' == $type) || ('simple_phone' == $type) || ('url' == $type) || ('email' == $type) || ('file' == $type)){
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD COLUMN `element_{$element_id}` text NULL COMMENT '{$comment}';";
			mf_do_query($query,array(),$dbh);
		}elseif ('textarea' == $type || 'signature' == $type){
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD COLUMN `element_{$element_id}` mediumtext NULL COMMENT '{$comment}';";
			mf_do_query($query,array(),$dbh);
		}elseif (('radio' == $type) || ('select' == $type)){
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD COLUMN `element_{$element_id}` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '{$comment}';";
			mf_do_query($query,array(),$dbh);
		}elseif ('rating' == $type){
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD COLUMN `element_{$element_id}` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '{$comment}';";
			mf_do_query($query,array(),$dbh);
		}elseif ('time' == $type){
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD COLUMN `element_{$element_id}` time NULL COMMENT '{$comment}';";
			mf_do_query($query,array(),$dbh);
		}elseif (('date' == $type) || ('europe_date' == $type)){
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD COLUMN `element_{$element_id}` date NULL COMMENT '{$comment}';";
			mf_do_query($query,array(),$dbh);
		}elseif ('money' == $type){
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD COLUMN `element_{$element_id}` decimal(62,2) NULL COMMENT '{$comment}';";
			mf_do_query($query,array(),$dbh);
		}elseif ('number' == $type){
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD COLUMN `element_{$element_id}` double NULL COMMENT '{$comment}';";
			mf_do_query($query,array(),$dbh);
		}elseif ('simple_name' == $type){
			//add two field, first and last name
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD COLUMN `element_{$element_id}_1` text NULL COMMENT '{$comment} - First', ADD COLUMN `element_{$element_id}_2` text NULL COMMENT '{$comment} - Last';";
			mf_do_query($query,array(),$dbh);
		}elseif ('simple_name_wmiddle' == $type){
			//add three fields, first, middle and last name
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD COLUMN `element_{$element_id}_1` text NULL COMMENT '{$comment} - First', ADD COLUMN `element_{$element_id}_2` text NULL COMMENT '{$comment} - Middle', ADD COLUMN `element_{$element_id}_3` text NULL COMMENT '{$comment} - Last';";
			mf_do_query($query,array(),$dbh);
		}elseif ('name' == $type){
			//add four field, title, first, last, suffix 
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD COLUMN `element_{$element_id}_1` text NULL COMMENT '{$comment} - Title', ADD COLUMN `element_{$element_id}_2` text NULL COMMENT '{$comment} - First', ADD COLUMN `element_{$element_id}_3` text NULL COMMENT '{$comment} - Last', ADD COLUMN `element_{$element_id}_4` text NULL COMMENT '{$comment} - Suffix';";
			mf_do_query($query,array(),$dbh);
		}elseif ('name_wmiddle' == $type){
			//add five fields, title, first, middle, last, suffix 
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD COLUMN `element_{$element_id}_1` text NULL COMMENT '{$comment} - Title', ADD COLUMN `element_{$element_id}_2` text NULL COMMENT '{$comment} - First', ADD COLUMN `element_{$element_id}_3` text NULL COMMENT '{$comment} - Middle', ADD COLUMN `element_{$element_id}_4` text NULL COMMENT '{$comment} - Last', ADD COLUMN `element_{$element_id}_5` text NULL COMMENT '{$comment} - Suffix';";
			mf_do_query($query,array(),$dbh);
		}elseif ('address' == $type){
			//add six field
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD COLUMN `element_{$element_id}_1` text NULL COMMENT '{$comment} - Street', ADD COLUMN `element_{$element_id}_2` text NULL COMMENT '{$comment} - Line 2', ADD COLUMN `element_{$element_id}_3` text NULL COMMENT '{$comment} - City', ADD COLUMN `element_{$element_id}_4` text NULL COMMENT '{$comment} - State/Province/Region', ADD COLUMN `element_{$element_id}_5` text NULL COMMENT '{$comment} - Zip/Postal Code', ADD COLUMN `element_{$element_id}_6` text NULL COMMENT '{$comment} - Country';";
			mf_do_query($query,array(),$dbh);
		}elseif ('checkbox' == $type){
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD COLUMN `element_{$element_id}_{$option_id}` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '{$comment} - {$option_id}';";
			mf_do_query($query,array(),$dbh);
		}
			
	}
?>