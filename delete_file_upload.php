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
	require('includes/users-functions.php');

	defined('MF_STORE_FILES_AS_BLOB') or define('MF_STORE_FILES_AS_BLOB',false);
	
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);
	
	
	$form_id		= (int) $_POST['form_id'];
	$holder_id		= trim($_POST['holder_id'] ?? '');
	$filename		= base64_decode(trim($_POST['filename']));
	$element_id		= (int) $_POST['element_id'];
	$is_db_live		= (int) $_POST['is_db_live'];	
	$key_id			= trim($_POST['key_id'] ?? '');
	$edit_key		= trim($_POST['edit_key'] ?? '');

	$machform_data_path = '';
	
	$is_delete_completed = false;
	$mf_properties = mf_get_form_properties($dbh,$form_id,array('form_entry_edit_enable','form_page_total','form_review'));

	if(!empty($is_db_live)){
		//if the file already inserted into the review table
		$entry_id = (int) $key_id;
		
		//if there is edit_key, make sure the key is valid
		$is_valid_edit_key = false;
		if(!empty($edit_key)){
			//get the associated entry id
			$query  = "SELECT `id` FROM `".MF_TABLE_PREFIX."form_{$form_id}` WHERE edit_key=?";
			$params = array($edit_key);
				
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);
			$edit_key_entry_id = $row['id'];
			if(!empty($edit_key_entry_id) && !empty($mf_properties['form_entry_edit_enable'])){
				$key_id = $edit_key_entry_id;
				$is_valid_edit_key = true; 
			}else{
				die("Invalid key.");
			}
		}

		//directory traversal prevention
		$filename = str_replace('.tmp', '', $filename);
		$filename = str_replace('..','',$filename);
		
		if(($is_db_live == 2 && $_SESSION['mf_logged_in'] === true) || 
		   ($is_valid_edit_key === true && !empty($mf_properties['form_entry_edit_enable']) && empty($mf_properties['form_review']) && $mf_properties['form_page_total'] == 1 )
		  ){
			//if this is edit_entry page or edit completed entry page (for single page form without review)
			$table_suffix = "";
		}else{
			$table_suffix = "_review";
		}

		//check if the file exist within the db or not
		$query = "select `element_{$element_id}` as file_record from ".MF_TABLE_PREFIX."form_{$form_id}{$table_suffix} where `id` = :entry_id and element_{$element_id} like :filename";
		$params = array('entry_id' => $entry_id,'filename' => '%'.$filename.'%');

		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		if(!empty($row['file_record'])){
			$file_record_array = explode('|',$row['file_record']);
			
			$regex    = '/^element_([0-9]*)_([0-9a-zA-Z]*)-([0-9]*)-(.*)$/';
			$new_files = array();
			foreach ($file_record_array as $current_file_record){
				$matches  = array();
				preg_match($regex, $current_file_record,$matches);
				$filename_noelement = $matches[4];
				
				if($filename_noelement == $filename){
					$complete_filename = $current_file_record;
				}else{
					$new_files[] = $current_file_record;
				}
			}
		}
		
		
		if(!empty($complete_filename)){
			
			if($is_db_live == 2 && $_SESSION['mf_logged_in'] === true){
				//if this is edit_entry page
				$file_tmp_suffix = "";

				//check permission, is the user allowed to access this page?
				if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
					$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

					//this page need edit_entries permission
					if(empty($user_perms['edit_entries'])){
						die("Access Denied. You don't have permission to edit this entry.");
					}
				}
			}else{
				$file_tmp_suffix = ".tmp";
			}

			$complete_filename = $machform_data_path.$mf_settings['upload_dir']."/form_{$form_id}/files/{$complete_filename}{$file_tmp_suffix}";
			
			if(MF_STORE_FILES_AS_BLOB === true){
				$file_name = pathinfo($complete_filename,PATHINFO_BASENAME);

				$query = "DELETE FROM ".MF_TABLE_PREFIX."form_{$form_id}_files WHERE file_name = ?";
				$params = array($file_name);
				mf_do_query($query,$params,$dbh);
			}else{
				if(file_exists($complete_filename)){
					unlink($complete_filename);
				}
			}

			//update the data within the table
			$new_files_joined = implode('|',$new_files);
			$query = "update ".MF_TABLE_PREFIX."form_{$form_id}{$table_suffix} set `element_{$element_id}` = ? where `id` = ?";
			$params = array($new_files_joined,$entry_id);
			mf_do_query($query,$params,$dbh);
			
			$is_delete_completed = true;
		}
		
	}else{
		//if the file not being saved into the table yet, only within the list file
		$file_token = $key_id;
		
		//directory traversal prevention
		$filename = str_replace('../','',$filename);
		
		//remove the file
		$complete_filename = $machform_data_path.$mf_settings['upload_dir']."/form_{$form_id}/files/element_{$element_id}_{$file_token}-{$filename}.tmp";
		if(file_exists($complete_filename)){
			unlink($complete_filename);
			$is_delete_completed = true;
		}
		
		//remove the file name from the list file
		$listfile_name = $machform_data_path.$mf_settings['upload_dir']."/form_{$form_id}/files/listfile_{$file_token}.txt";
		if(file_exists($listfile_name)){
			$current_files = file($listfile_name);
			$new_files = '';
			foreach ($current_files as $value){
				$current_line = trim($value);
				$target_file  = $complete_filename;
				
				if($target_file != $current_line){
					$new_files .= $value;
				}
			}
			
			if($new_files == "<?php\n?>"){
				unlink($listfile_name);
			}else{
				file_put_contents($listfile_name, $new_files, LOCK_EX);
			}
		}

		if(MF_STORE_FILES_AS_BLOB === true){
			//remove the file from ap_form_xxx_files table
			$target_file = "element_{$element_id}_{$file_token}-{$filename}.tmp";
			
			$query = "DELETE FROM `".MF_TABLE_PREFIX."form_{$form_id}_files` where file_name=?";
			$params = array($target_file);
			mf_do_query($query,$params,$dbh);

			//update ap_form_xxx_listfiles, remove the deleted file name from the table
			$query = "DELETE FROM `".MF_TABLE_PREFIX."form_{$form_id}_listfiles` where file_token=? and file_content=?";
			$params = array($file_token,$complete_filename);
			mf_do_query($query,$params,$dbh);
			
			$is_delete_completed = true;
		}
	}
	
	$response_data = new stdClass();
	
	if($is_delete_completed){
		$response_data->status    	= "ok";
		$response_data->holder_id	= $holder_id;
		$response_data->element_id  = $element_id;
	}else{
		$response_data->status    	= "error";
	}
	
	$response_json = json_encode($response_data);
	
	echo $response_json;
	
?>