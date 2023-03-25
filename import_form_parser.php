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
	require('includes/filter-functions.php');

	defined('MF_STORE_FILES_AS_BLOB') or define('MF_STORE_FILES_AS_BLOB',false);

	$csrf_token = trim($_POST['csrf_token']);

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);
	
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	$form_file_name = mf_sanitize($_POST['file_name']);
	$form_file_path = $mf_settings['data_dir']."/temp/".$form_file_name;

	$response_data = new stdClass();
	$response_data->status  = "error";
	
	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer']) && empty($_SESSION['mf_user_privileges']['priv_new_forms'])){
		$_SESSION['MF_DENIED'] = "You don't have permission to access this page.";
		
		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
		exit;
	}

	if(file_exists($form_file_path)){
		$handle = @fopen($form_file_path, "r");
		
		if($handle) {
			//get new form_id
		    $query = "select max(form_id) max_form_id from ".MF_TABLE_PREFIX."forms";
			$params = array();
			
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);
			
			if(empty($row['max_form_id'])){
				$last_form_id = 10000;
			}else{
				$last_form_id = $row['max_form_id'];
			}
			
			$form_id = $last_form_id + rand(100,1000);

		    while (($line_data = fgets($handle)) !== false) {
				//parse the data        
		    	$data_obj = new stdClass();
		    	$data_obj = json_decode($line_data);

		    	$is_parse_success = false;

		    	if(!empty($data_obj->machform_version)){
		    		//compare the file first
		    		//if the file being imported is using newer version than the current machform version, stop the operation
		    		if(version_compare($mf_settings['machform_version'], $data_obj->machform_version,">=")){
		    			$imported_form_version = $data_obj->machform_version;
		    			$imported_form_name	   = $data_obj->form_name;	
		    		}else{
		    			$response_data->status  = 'error';
						$response_data->message = 'The form is not compatible with your MachForm.<br/> Please update your MachForm and try again.';
						break;
		    		}
		    	}else if(!empty($data_obj->table_name)){
		    		$current_table_name = $data_obj->table_name;
		    		$is_parse_success = true;
		    	}else{
		    		//insert into tables
		    		$data_array 		 = array();
		    		$data_array 		 = get_object_vars($data_obj);
		    		
		    		$column_names_array  = array_keys($data_array);

		    		//we removed 'element_date_disable_weekend' column since v6
		    		//we removed 'element_file_enable_advance', 'element_file_enable_type_limit', 'element_file_block_or_allow' since v16
					//we removed 'element_address_hideline2' since v18
		    		$removed_columns = array('element_date_disable_weekend','element_file_enable_advance', 'element_file_enable_type_limit', 'element_file_block_or_allow', 'element_address_hideline2');
		    		if($current_table_name == 'form_elements'){
		    			foreach($removed_columns as $column_name){
		    				if(($key = array_search($column_name, $column_names_array)) !== false) {
    							unset($column_names_array[$key]);
							}
		    			}
					}

		    		$column_names_joined = implode("`,`", $column_names_array);
		    		
		    		$params = array();
		    		$old_form_id = $data_array['form_id'];

		    		foreach ($column_names_array as $column_name) {

		    			if($current_table_name == 'report_elements' || $current_table_name == 'reports'){
		    				if($column_name == 'access_key' || $column_name == 'report_access_key'){
		    					$data_array[$column_name] = str_replace($old_form_id.'x', $form_id.'x', $data_array[$column_name]);
		    				}
		    			}

		    			if($current_table_name == 'form_elements'){
		    				if($column_name == 'element_file_type_list'){
		    					if($data_array[$column_name] == 'php,php3,php4,php5,phtml,exe,pl,cgi,html,htm,js'){
		    						$data_array[$column_name] = 'jpg, jpeg, png, gif, bmp, heic, pdf, docx, doc, xlsx, xls, ppt, pptx, txt, csv, zip, mp3, wma, mpg, mpeg, mp4, avi';
		    					}
		    				}
		    			}

		    			$params[':'.$column_name] = $data_array[$column_name];
		    		}
		    		$param_names = implode(",", array_keys($params));
		    		$params[':form_id'] = $form_id;

		    		$query = "INSERT INTO ".MF_TABLE_PREFIX."{$current_table_name}(`{$column_names_joined}`) VALUES({$param_names})";
		    		mf_do_query($query,$params,$dbh);

		    		$is_parse_success = true;
		    	}
		    }

		    if($is_parse_success){
		    	//update some data within ap_forms table
				$query = "UPDATE `".MF_TABLE_PREFIX."forms` SET form_theme_id=0,form_created_by=?,form_created_date=now() WHERE form_id = ?";
				$params = array($_SESSION['mf_user_id'],$form_id);
				mf_do_query($query,$params,$dbh);

		    	//start createing form table----------
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
  													KEY `date_created` (`date_created`)
  													) DEFAULT CHARACTER SET utf8;";
				$params = array();
				mf_do_query($query,$params,$dbh);

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

				//Get the fields from ap_form_elements table with status = 1 and create the field column into the form's table
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
			   			   		form_id = ? and element_status=1 
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
						$query = "select option_id from ".MF_TABLE_PREFIX."element_options where form_id = ? and element_id = ? and live = 1 order by option_id asc";
						$params_checkbox = array($form_id,$element_id);
						
						$sth2 = mf_do_query($query,$params_checkbox,$dbh);
						while($row2 = mf_do_fetch_result($sth2)){
							table_add_field($dbh,$form_id,$element_id,$element_type,$row2['option_id']);
						}
					}elseif($element_type == 'matrix'){
						
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
							$query = "select option_id from ".MF_TABLE_PREFIX."element_options where form_id = ? and element_id = ? and live = 1 order by option_id asc";
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

		    	//end creating form table-------------

		    	//start creating review table---------
		    	$form_properties = mf_get_form_properties($dbh,$form_id, array('form_review','form_page_total'));
		    	$form_review = (int) $form_properties['form_review'];
		    	$form_page_total = (int) $form_properties['form_page_total'];

		    	if(!empty($form_review) || ($form_page_total > 1)){
		    		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_{$form_id}_review` like `".MF_TABLE_PREFIX."form_{$form_id}`";
					mf_do_query($query,array(),$dbh);
					
					$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}_review` ADD COLUMN `session_id` varchar(128) NULL";
					mf_do_query($query,array(),$dbh);
		    	}
		    	//end creating review table-----------

		    	//start creating approvals table---------
		    	$form_properties = mf_get_form_properties($dbh,$form_id, array('form_approval_enable'));
		    	$form_approval_enable = (int) $form_properties['form_approval_enable'];
		    	
		    	if(!empty($form_approval_enable)){
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
		    	}
		    	//end creating approvals table-----------

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

				//insert into permissions table
				$query = "delete from ".MF_TABLE_PREFIX."permissions where form_id=? and user_id=?";
				$params = array($form_id,$_SESSION['mf_user_id']);
				mf_do_query($query,$params,$dbh);

				$query = "insert into ".MF_TABLE_PREFIX."permissions(form_id,user_id,edit_form,edit_report,edit_entries,view_entries) values(?,?,1,1,1,1)";
				$params = array($form_id,$_SESSION['mf_user_id']);
				mf_do_query($query,$params,$dbh);

		    	//create data folder
		    	if(is_writable($mf_settings['data_dir'])){
			
					$old_mask = umask(0);
					mkdir($mf_settings['data_dir']."/form_{$form_id}",0755);
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

		    	//send success status
		    	$response_data->status = 'ok';
			    $response_data->new_form_name = htmlentities($imported_form_name,ENT_QUOTES);
			    $response_data->new_form_id   = $form_id;
		    }
		   

		    fclose($handle);

		    //delete the file
		    @unlink($form_file_path);
		}
	}else{
		$response_data->status  = 'error';
		$response_data->message = 'Invalid form file name.';
	}
	
	$response_json = json_encode($response_data);
	
	echo $response_json;




	/***************************************************************************************************************/	
	/* Functions																								   */
	/***************************************************************************************************************/
   	
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