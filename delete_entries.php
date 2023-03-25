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

	defined('MF_STORE_FILES_AS_BLOB') or define('MF_STORE_FILES_AS_BLOB',false);
	
	$form_id 		   	= (int) trim($_POST['form_id']);
	$selected_entries  	= isset($_POST['selected_entries']) ? mf_sanitize($_POST['selected_entries']) : false;
	$delete_all		   	= isset($_POST['delete_all']) ? (int) $_POST['delete_all'] : 0;
	$origin			   	= trim($_POST['origin'] ?? '');
	$user_id		   	= (int) $_SESSION['mf_user_id'];
	$csrf_token 		= trim($_POST['csrf_token']);
	
	$incomplete_entries = (int) ($_POST['incomplete_entries'] ?? 0); //if this is operation targetted to incomplete entries, this will contain '1'
	if(empty($incomplete_entries)){
		$incomplete_entries = 0;
	}

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);
	
	if(empty($form_id)){
		die("This file can't be opened directly.");
	}

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	$form_properties = mf_get_form_properties($dbh,$form_id,array('form_approval_enable'));

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_entries permission
		if(empty($user_perms['edit_entries'])){
			die("Access Denied. You don't have permission to edit this entry.");
		}
	}

	if(!empty($delete_all)){ //this is delete all entries operation
		//check if this form has filter enabled or not
		//if there is filter, delete all entries within the defined filter only
		$query 	= "select 
						 entries_enable_filter,
						 entries_incomplete_enable_filter 
				     from 
				     	 ".MF_TABLE_PREFIX."entries_preferences 
				    where 
				    	 form_id = ? and user_id = ?";
		$params = array($form_id,$user_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		if(!empty($row)){
			if(!empty($incomplete_entries)){
				$entries_enable_filter = $row['entries_incomplete_enable_filter'];
			}else{
				$entries_enable_filter = $row['entries_enable_filter'];
			}
		}

		if(empty($entries_enable_filter)){
			if(MF_CONF_TRUE_DELETE == true){
				//delete the entries from the form_x table
				if(!empty($incomplete_entries)){
					$query = "delete from `".MF_TABLE_PREFIX."form_{$form_id}` where `status` = 2";
				}else{
					$query = "delete from `".MF_TABLE_PREFIX."form_{$form_id}` where `status` <> 2";
				}

				$params = array();
				mf_do_query($query,$params,$dbh);

				//delete from ap_form_payments table
				if(empty($incomplete_entries)){
					$query = "delete from ".MF_TABLE_PREFIX."form_payments where form_id = ?";
					$params = array($form_id);
					mf_do_query($query,$params,$dbh);
				}

				//delete the row activity log
				if(empty($incomplete_entries)){
					$query = "delete from `".MF_TABLE_PREFIX."form_{$form_id}_log` ";
					$params = array();
					mf_do_query($query,$params,$dbh);
				}

				//delete records from ap_form_xxx_approvals, if enabled
				if(!empty($form_properties['form_approval_enable'])){
					$query = "delete from `".MF_TABLE_PREFIX."form_{$form_id}_approvals`";
					$params = array();
					mf_do_query($query,$params,$dbh);
				}

				//delete file uploads too
				if(MF_STORE_FILES_AS_BLOB !== true){
					//get the element id for file fields
					$file_element_id_array = array();

					$query = "select element_id from ".MF_TABLE_PREFIX."form_elements where element_type='file' and form_id=?";
					$params = array($form_id);
					
					$sth = mf_do_query($query,$params,$dbh);
					while($row = mf_do_fetch_result($sth)){
						$file_element_id_array[] = $row['element_id'];
					}
					
					//delete the files from data folder
					if(!empty($file_element_id_array)){
						foreach ($file_element_id_array as $element_id){
							$file_uploads = array();
							$file_uploads = glob($mf_settings['upload_dir']."/form_{$form_id}/files/element_{$element_id}_*");
							
							foreach ($file_uploads as $filename) {
								$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

								if(!empty($incomplete_entries)){
									//only remove files with .tmp extension for incomplete entries
									if($ext == 'tmp'){
										@unlink($filename);
									}
								}else{
									//remove any files having extension other than .tmp for completed entries
									if($ext != 'tmp'){
										@unlink($filename);
									}
								}
								
							}
						}
					}
				}else{
					if(!empty($incomplete_entries)){
						//only remove files with .tmp extension for incomplete entries
						$query = "DELETE FROM ".MF_TABLE_PREFIX."form_{$form_id}_files WHERE file_name LIKE '%.tmp';";
					}else{
						//remove any files having extension other than .tmp for completed entries
						$query = "DELETE FROM ".MF_TABLE_PREFIX."form_{$form_id}_files WHERE file_name NOT LIKE '%.tmp';";
					}

					$params = array();
					mf_do_query($query,$params,$dbh);
				}
			}else{
				//update ap_form_x table
				if(!empty($incomplete_entries)){
					$query = "update ".MF_TABLE_PREFIX."form_{$form_id} set `status`=0 where `status`=2";
				}else{
					$query = "update ".MF_TABLE_PREFIX."form_{$form_id} set `status`=0 where `status`=1";
				}

				$params = array();
				mf_do_query($query,$params,$dbh);

				//update ap_form_payments table
				if(empty($incomplete_entries)){
					$query = "update ".MF_TABLE_PREFIX."form_payments set `status`=0 where form_id = ?";
					$params = array($form_id);
					mf_do_query($query,$params,$dbh);
				}
			}
		}else{ //if there is filter enabled
			//get the entry_id of all rows within the filter
			$entries_options = array();
			if(!empty($incomplete_entries)){
				$entries_options['is_incomplete_entry'] = true;	
			}else{
				$entries_options['is_incomplete_entry'] = false;	
			}

			$target_entry_id_array = mf_get_filtered_entries_ids($dbh,$form_id,$entries_options);
			
			//delete them
			if(!empty($target_entry_id_array)){
				$target_entry_id_joined = implode("','", $target_entry_id_array);

				if(MF_CONF_TRUE_DELETE == true){
					//delete the records from a_form_x table
					$query = "delete from `".MF_TABLE_PREFIX."form_{$form_id}` where `id` in('{$target_entry_id_joined}')";
					$params = array();
					mf_do_query($query,$params,$dbh);

					//delete the row activity log
					$query = "delete from `".MF_TABLE_PREFIX."form_{$form_id}_log` where `record_id` in('{$target_entry_id_joined}')";
					$params = array();
					mf_do_query($query,$params,$dbh);

					//delete records from ap_form_payments table
					$query = "delete from `".MF_TABLE_PREFIX."form_payments` where form_id = ? and `record_id` in('{$target_entry_id_joined}')";
					$params = array($form_id);
					mf_do_query($query,$params,$dbh);

					//delete records from ap_form_xxx_approvals, if enabled
					if(!empty($form_properties['form_approval_enable'])){
						$query = "delete from `".MF_TABLE_PREFIX."form_{$form_id}_approvals` where `record_id` in('{$target_entry_id_joined}')";
						$params = array($form_id);
						mf_do_query($query,$params,$dbh);
					}

					//delete file uploads too
					//get the element id for file fields
					$file_element_id_array = array();

					$query = "select element_id from ".MF_TABLE_PREFIX."form_elements where element_type='file' and form_id=?";
					$params = array($form_id);
					
					$sth = mf_do_query($query,$params,$dbh);
					while($row = mf_do_fetch_result($sth)){
						$file_element_id_array[] = $row['element_id'];
					}
					
					//delete the files from data folder
					if(!empty($file_element_id_array)){
						foreach ($target_entry_id_array as $entry_id){
							foreach ($file_element_id_array as $element_id){
								$file_uploads = array();
								$file_uploads = glob($mf_settings['upload_dir']."/form_{$form_id}/files/element_{$element_id}_*-{$entry_id}-*");
								
								if(MF_STORE_FILES_AS_BLOB !== true){
									foreach ($file_uploads as $filename) {
										$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

										if(!empty($incomplete_entries)){
											//only remove files with .tmp extension for incomplete entries
											if($ext == 'tmp'){
												@unlink($filename);
											}
										}else{
											//remove any files having extension other than .tmp for completed entries
											if($ext != 'tmp'){
												@unlink($filename);
											}
										}
									}
								}else{
									if(!empty($incomplete_entries)){
										//only remove files with .tmp extension for incomplete entries
										$query = "DELETE FROM ".MF_TABLE_PREFIX."form_{$form_id}_files 
														WHERE 
															  file_name LIKE 'element_{$element_id}_%-{$entry_id}-%' AND 
															  file_name LIKE '%.tmp';";
									}else{
										//remove any files having extension other than .tmp for completed entries
										$query = "DELETE FROM ".MF_TABLE_PREFIX."form_{$form_id}_files 
														WHERE 
															  file_name LIKE 'element_{$element_id}_%-{$entry_id}-%' AND 
															  file_name NOT LIKE '%.tmp';";
									}

									$params = array();
									mf_do_query($query,$params,$dbh);
								}
							}
						}
					}

				}else{
					//simply set the status of the record on ap_form_x to 0
					$query = "update `".MF_TABLE_PREFIX."form_{$form_id}` set `status`=0 where `id` in('{$target_entry_id_joined}')";
					$params = array();
					mf_do_query($query,$params,$dbh);

					//set the status of the record on ap_form_payments as well
					$query = "update `".MF_TABLE_PREFIX."form_payments` set `status`=0 where form_id = ? and `record_id` in('{$target_entry_id_joined}')";
					$params = array($form_id);
					mf_do_query($query,$params,$dbh);
				}
			}
		}
		
	}else{ //only some selected entries being deleted
		
		if(!empty($selected_entries)){
			$target_entry_id_array = array();
			foreach ($selected_entries as $data) {
				$target_entry_id_array[] = (int) str_replace('entry_', '', $data['name']);
			}

			if(!empty($target_entry_id_array)){


				//if the request coming from view_entry.php page, only 1 entry being deleted
				if(!empty($origin) && ($origin == 'view_entry')){

					$_SESSION['MF_SUCCESS'] = "Entry #{$target_entry_id_array[0]} has been deleted.";

					//get the next entry_id
					$entries_options = array();
					if(!empty($incomplete_entries)){
						$entries_options['is_incomplete_entry'] = true;	
					}else{
						$entries_options['is_incomplete_entry'] = false;	
					}

					$all_entry_id_array = mf_get_filtered_entries_ids($dbh,$form_id,$entries_options);
					$entry_key = array_keys($all_entry_id_array,$target_entry_id_array[0]);
					$entry_key = $entry_key[0];
				
					$entry_key++;		

					$next_entry_id = $all_entry_id_array[$entry_key] ?? false;

					//if there is no entry_id, fetch the first member of the array
					if(empty($next_entry_id) && ($target_entry_id_array[0] != $all_entry_id_array[0])){
						$next_entry_id = $all_entry_id_array[0];
					}
	
				}else{
					$_SESSION['MF_SUCCESS'] = 'Selected entries has been deleted.';
				}

				$target_entry_id_joined = implode("','", $target_entry_id_array);
				
				if(MF_CONF_TRUE_DELETE == true){
					//delete the records from a_form_x table
					$query = "delete from `".MF_TABLE_PREFIX."form_{$form_id}` where `id` in('{$target_entry_id_joined}')";
					$params = array();
					mf_do_query($query,$params,$dbh);

					//delete the row activity log
					$query = "delete from `".MF_TABLE_PREFIX."form_{$form_id}_log` where `record_id` in('{$target_entry_id_joined}')";
					$params = array();
					mf_do_query($query,$params,$dbh);


					//delete records from ap_form_payments table
					$query = "delete from `".MF_TABLE_PREFIX."form_payments` where form_id = ? and `record_id` in('{$target_entry_id_joined}')";
					$params = array($form_id);
					mf_do_query($query,$params,$dbh);

					//delete records from ap_form_xxx_approvals, if enabled
					if(!empty($form_properties['form_approval_enable'])){
						$query = "delete from `".MF_TABLE_PREFIX."form_{$form_id}_approvals` where `record_id` in('{$target_entry_id_joined}')";
						$params = array($form_id);
						mf_do_query($query,$params,$dbh);
					}

					//delete file uploads too
					//get the element id for file fields
					$file_element_id_array = array();

					$query = "select element_id from ".MF_TABLE_PREFIX."form_elements where element_type='file' and form_id=?";
					$params = array($form_id);
					
					$sth = mf_do_query($query,$params,$dbh);
					while($row = mf_do_fetch_result($sth)){
						$file_element_id_array[] = $row['element_id'];
					}
					
					//delete the files from data folder
					if(!empty($file_element_id_array)){
						foreach ($target_entry_id_array as $entry_id){
							foreach ($file_element_id_array as $element_id){
								$file_uploads = array();
								$file_uploads = glob($mf_settings['upload_dir']."/form_{$form_id}/files/element_{$element_id}_*-{$entry_id}-*");
								
								if(MF_STORE_FILES_AS_BLOB !== true){
									foreach ($file_uploads as $filename) {
										$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

										if(!empty($incomplete_entries)){
											//only remove files with .tmp extension for incomplete entries
											if($ext == 'tmp'){
												@unlink($filename);
											}
										}else{
											//remove any files having extension other than .tmp for completed entries
											if($ext != 'tmp'){
												@unlink($filename);
											}
										}
									}
								}else{
									if(!empty($incomplete_entries)){
										//only remove files with .tmp extension for incomplete entries
										$query = "DELETE FROM ".MF_TABLE_PREFIX."form_{$form_id}_files 
														WHERE 
															  file_name LIKE 'element_{$element_id}_%-{$entry_id}-%' AND 
															  file_name LIKE '%.tmp';";
									}else{
										//remove any files having extension other than .tmp for completed entries
										$query = "DELETE FROM ".MF_TABLE_PREFIX."form_{$form_id}_files 
														WHERE 
															  file_name LIKE 'element_{$element_id}_%-{$entry_id}-%' AND 
															  file_name NOT LIKE '%.tmp';";
									}

									$params = array();
									mf_do_query($query,$params,$dbh);
								}
							}
						}
					}

				}else{
					//simply set the status of the record to 0
					$query = "update `".MF_TABLE_PREFIX."form_{$form_id}` set `status`=0 where `id` in('{$target_entry_id_joined}')";
					$params = array();
					mf_do_query($query,$params,$dbh);

					//set the status of the record on ap_form_payments as well
					$query = "update `".MF_TABLE_PREFIX."form_payments` set `status`=0 where form_id = ? and `record_id` in('{$target_entry_id_joined}')";
					$params = array($form_id);
					mf_do_query($query,$params,$dbh);
				}
			}
		}
	}	

	
	
	$response_data = new stdClass();
	$response_data->status    	= "ok";
	$response_data->form_id 	= $form_id;

	if(!empty($next_entry_id)){
		$response_data->entry_id = $next_entry_id;
	}else{
		$response_data->entry_id = 0;
	}

	$response_json = json_encode($response_data);
		
	echo $response_json;
?>