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

	$_POST = json_decode(file_get_contents('php://input'), true);	
	$dbh = mf_connect_db();
	
	$folder_id 	  = (int) $_POST['folder_id']; //if empty, then this is creating new folder
	$folder_name  = mf_sanitize($_POST['folder_name']);
	$rule_all_any = mf_sanitize($_POST['rule_all_any']);
	$folder_rules = mf_sanitize($_POST['folder_rules']);

	if(empty($folder_id)){
		$is_creating_new_folder = true;
	}else{
		$is_creating_new_folder = false;
	}

	if(empty($folder_name)){
		die("Error. Folder name required.");
	}

	if($rule_all_any == 'any'){
		$rule_all_any = 'any';
	}else{
		$rule_all_any = 'all';
	}
	
   	$response_data = new stdClass();
	$response_data->status  = 'ok';

	
	if($is_creating_new_folder){
		//check for duplicate folder name
		$query = "SELECT count(*) folder_exist FROM `".MF_TABLE_PREFIX."folders` WHERE user_id=? and folder_name = ?";
		
		$params = array($_SESSION['mf_user_id'],$folder_name);
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		if(!empty($row['folder_exist'])){
			$response_data->status 	= 'error';
			$response_data->message = "You already have folder with the same name";
		}else{
			//insert into ap_folders
			//get folder_id and folder_position first
			$query = "SELECT max(folder_id)+1 new_folder_id,max(folder_position)+1 new_folder_position FROM `".MF_TABLE_PREFIX."folders` where user_id=?";
			$params = array($_SESSION['mf_user_id']);
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);

			$new_folder_id 		 = (int) $row['new_folder_id'];
			$new_folder_position = (int) $row['new_folder_position'];

			$query = "INSERT INTO `".MF_TABLE_PREFIX."folders` (
									`user_id`, 
									`folder_id`, 
									`folder_position`, 
									`folder_name`, 
									`folder_selected`, 
									`rule_all_any`) 
							VALUES (?, ?, ?, ?, '0', ?);";
			$params = array($_SESSION['mf_user_id'],$new_folder_id,$new_folder_position,$folder_name,$rule_all_any);
			mf_do_query($query,$params,$dbh);

			//insert into ap_folders_conditions
			if(!empty($folder_rules)){
				
				foreach ($folder_rules as $folder) {
					if(empty($folder)){
						continue;
					}

					//security measure, 'created_by' rule only allowed for admin
					if($folder['element_name'] == 'created_by' && empty($_SESSION['mf_user_privileges']['priv_administer'])){
						continue;
					}

					if(!empty($folder['element_name'])){
						$query = "INSERT INTO `".MF_TABLE_PREFIX."folders_conditions` (
									`user_id`, 
									`folder_id`, 
									`element_name`, 
									`rule_condition`, 
									`rule_keyword`) 
							VALUES (?, ?, ?, ?, ?);";

						$params = array($_SESSION['mf_user_id'],
										$new_folder_id,
										$folder['element_name'],
										$folder['rule_condition'],
										$folder['rule_keyword']);
						mf_do_query($query,$params,$dbh);
					}
				}
			}
		}
	}else{
		$is_valid_folder_update = true;

		//if this is updating a folder
		//make sure the folder is valid and exist and not renamed to a duplicate name
		$query = "SELECT count(*) folder_exist FROM `".MF_TABLE_PREFIX."folders` WHERE user_id=? and folder_id = ?";
		
		$params = array($_SESSION['mf_user_id'],$folder_id);
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		if(empty($row['folder_exist'])){
			$folder_update_error_message = 'This folder is not exist.';
			$is_valid_folder_update = false;
		}

		$query = "SELECT count(*) folder_exist FROM `".MF_TABLE_PREFIX."folders` WHERE user_id=? and folder_id <> ? and folder_name = ?";
		
		$params = array($_SESSION['mf_user_id'],$folder_id,$folder_name);
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		if(!empty($row['folder_exist'])){
			$folder_update_error_message = 'You already have folder with the same name';
			$is_valid_folder_update = false;
		}


		if($is_valid_folder_update){
			//update ap_folders
			$query = "UPDATE `".MF_TABLE_PREFIX."folders` SET folder_name=?,rule_all_any=? WHERE user_id=? and folder_id=?";
			$params = array($folder_name,$rule_all_any,$_SESSION['mf_user_id'],$folder_id);
			mf_do_query($query,$params,$dbh);
			
			//insert new records into ap_folders_conditions
			if(!empty($folder_rules)){
				//delete existing records from ap_folders_conditions
				$query = "DELETE FROM `".MF_TABLE_PREFIX."folders_conditions` WHERE user_id=? and folder_id=?";
				$params = array($_SESSION['mf_user_id'],$folder_id);
				mf_do_query($query,$params,$dbh);

				foreach ($folder_rules as $value) {
					if(empty($value)){
						continue;
					}

					$query = "INSERT INTO `".MF_TABLE_PREFIX."folders_conditions`(
											user_id,
											folder_id,
											element_name,
											rule_condition,
											rule_keyword) VALUES(?,?,?,?,?)";
					$params = array($_SESSION['mf_user_id'],
									$folder_id,
									$value['element_name'],
									$value['rule_condition'],
									$value['rule_keyword']);
					mf_do_query($query,$params,$dbh);
				}
			}
		}else{
			$response_data->status 	= 'error';
			$response_data->message = $folder_update_error_message; 
		}
	}


	if($response_data->status == 'ok'){
		$_SESSION['MF_SUCCESS'] = 'Folder has been saved.';
	}

	$response_json = json_encode($response_data);
	
	header("Content-Type: application/json");
	echo $response_json;
	
?>