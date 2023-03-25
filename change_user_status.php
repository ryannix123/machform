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
	require('includes/users-functions.php');
	
	$action 		   = isset($_POST['action']) ? trim($_POST['action']) : false;
	$selected_users    = isset($_POST['selected_users']) ? mf_sanitize($_POST['selected_users']) : false;
	$select_all		   = (int) ($_POST['delete_all'] ?? 0);
	$no_session_msg	   = (int) ($_POST['no_session_msg'] ?? 0);
	$origin			   = isset($_POST['origin']) ? trim($_POST['origin']) : false;
	$csrf_token 	   = isset($_POST['csrf_token']) ? trim($_POST['csrf_token']) : false;

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);
	
	if(empty($action)){
		die("This file can't be opened directly.");
	}else{
		if($action == 'delete'){
			$new_user_status = 0;
		}else if($action == 'suspend'){
			$new_user_status = 2;
		}else if($action == 'unsuspend'){
			$new_user_status = 1;
		}else{
			die("Invalid action value.");
		}
	}

	//check user privileges, is this user has privilege to administer MachForm?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		die("Access Denied. You don't have permission to administer MachForm.");
	}

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	if(!empty($select_all)){ //this is delete/suspend all entries operation
		//check if this form has filter enabled or not
		//if there is filter, delete/suspend all entries within the defined filter only

		if(empty($_SESSION['filter_users'])){
			if($action == 'delete'){
				if(MF_CONF_TRUE_DELETE == true){
					//empty the table
					$query = "delete from `".MF_TABLE_PREFIX."users` where `user_id` <> 1";
					$params = array($new_user_status);
					mf_do_query($query,$params,$dbh);

					//empty the table
					$query = "delete from `".MF_TABLE_PREFIX."permissions` where `user_id` <> 1";
					$params = array($new_user_status);
					mf_do_query($query,$params,$dbh);
				}else{
					//simply set the status of the record
					$query = "update `".MF_TABLE_PREFIX."users` set `status`=? where `user_id` <> 1";
					$params = array($new_user_status);
					mf_do_query($query,$params,$dbh);
				}
			}else if($action == 'suspend'){
				//simply set the status of the record
				$query = "update `".MF_TABLE_PREFIX."users` set `status`=? where `status`=1 and `user_id` <> 1";
				$params = array($new_user_status);
				mf_do_query($query,$params,$dbh);
			}
		}else{ //if there is filter enabled
			//get the entry_id of all rows within the filter
			$target_user_id_array = mf_get_filtered_users_ids($dbh,$_SESSION['filter_users']);
			
			//delete or suspend them
			if(!empty($target_user_id_array)){
				
				if($action == 'delete'){
					$_SESSION['MF_SUCCESS'] = 'Selected users has been deleted.';
				}else if($action == 'suspend'){
					$_SESSION['MF_SUCCESS'] = 'Selected users has been suspended.';
				}
				
				$target_user_id_joined = implode("','", $target_user_id_array);
				
				if($action == 'delete'){
					if(MF_CONF_TRUE_DELETE == true){
						//delete the records from a_form_x table
						$query = "delete from `".MF_TABLE_PREFIX."users` where `user_id` in('{$target_user_id_joined}')";
						$params = array();
						mf_do_query($query,$params,$dbh);

						//delete records from ap_permissions table
						$query = "delete from `".MF_TABLE_PREFIX."permissions` where `user_id` in('{$target_user_id_joined}')";
						$params = array();
						mf_do_query($query,$params,$dbh);
					}else{
						//simply set the status of the record
						$query = "update `".MF_TABLE_PREFIX."users` set `status`=? where `user_id` in('{$target_user_id_joined}')";
						$params = array($new_user_status);
						mf_do_query($query,$params,$dbh);
					}
				}else if($action == 'suspend'){
					//simply set the status of the record
					$query = "update `".MF_TABLE_PREFIX."users` set `status`=? where `user_id` in('{$target_user_id_joined}')";
					$params = array($new_user_status);
					mf_do_query($query,$params,$dbh);
				}
				
			}
		}
		
	}else{ //only some selected users being deleted
		
		if(!empty($selected_users)){
			$target_user_id_array = array();

			foreach ($selected_users as $data) {
				$user_id_value = (int) str_replace('entry_', '', $data['name']);
				
				//main administrator has user_id = 1 and should be excluded
				if($user_id_value !== 1){
					$target_user_id_array[] = $user_id_value;
				}
			}

			if(!empty($target_user_id_array)){


				//if the request coming from view_user.php page, only 1 entry being deleted
				if(!empty($origin) && ($origin == 'view_user')){

					$_SESSION['MF_SUCCESS'] = "User #{$target_user_id_array[0]} has been deleted.";

					//get the next entry_id
					$exclude_admin = false;

					$all_user_id_array = mf_get_filtered_users_ids($dbh,$_SESSION['filter_users'],$exclude_admin);
					$user_key = array_keys($all_user_id_array,$target_user_id_array[0]);
					$user_key = $user_key[0];
				
					$user_key++;		

					$next_user_id = $all_user_id_array[$user_key];

					//if there is no entry_id, fetch the first member of the array
					if(empty($next_user_id) && ($target_user_id_array[0] != $all_user_id_array[0])){
						$next_user_id = $all_user_id_array[0];
					}
	
				}else{
					if($action == 'delete'){
						$_SESSION['MF_SUCCESS'] = 'Selected users has been deleted.';
					}else if($action == 'suspend'){
						$_SESSION['MF_SUCCESS'] = 'Selected users has been suspended.';
					}
				}

				if(!empty($no_session_msg)){
					unset($_SESSION['MF_SUCCESS']);
				}

				$target_user_id_joined = implode("','", $target_user_id_array);
				
				if($action == 'delete'){
					if(MF_CONF_TRUE_DELETE == true){
						//delete the records from a_form_x table
						$query = "delete from `".MF_TABLE_PREFIX."users` where `user_id` in('{$target_user_id_joined}')";
						$params = array();
						mf_do_query($query,$params,$dbh);

						//delete records from ap_permissions table
						$query = "delete from `".MF_TABLE_PREFIX."permissions` where `user_id` in('{$target_user_id_joined}')";
						$params = array();
						mf_do_query($query,$params,$dbh);

						//delete records from ap_folders table
						$query = "delete from `".MF_TABLE_PREFIX."folders` where `user_id` in('{$target_user_id_joined}')";
						$params = array();
						mf_do_query($query,$params,$dbh);
					}else{
						//simply set the status of the record
						$query = "update `".MF_TABLE_PREFIX."users` set `status`=? where `user_id` in('{$target_user_id_joined}')";
						$params = array($new_user_status);
						mf_do_query($query,$params,$dbh);
					}
				}else if($action == 'suspend'){
					//simply set the status of the record
					$query = "update `".MF_TABLE_PREFIX."users` set `status`=? where `user_id` in('{$target_user_id_joined}')";
					$params = array($new_user_status);
					mf_do_query($query,$params,$dbh);
				}else if($action == 'unsuspend'){
					//simply set the status of the record
					//also unblock the user from account locking
					$query = "update `".MF_TABLE_PREFIX."users` set `status`=?,login_attempt_date=NULL,login_attempt_count=0 where `user_id` in('{$target_user_id_joined}') and `status`=2";
					$params = array($new_user_status);
					mf_do_query($query,$params,$dbh);
				}
				
			}
		}
	}	

	
	
	$response_data = new stdClass();
	$response_data->status    	= "ok";
	
	if(!empty($next_user_id)){
		$response_data->user_id = $next_user_id;
	}else{
		$response_data->user_id = 0;
	}

	$response_json = json_encode($response_data);
		
	echo $response_json;
?>