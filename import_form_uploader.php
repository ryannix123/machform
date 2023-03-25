<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	require('includes/init.php');
	session_write_close(); //close the session from init.php file first
	
	ob_start();
	
	require('config.php');
	require('includes/db-core.php');
	require('includes/helper-functions.php');
	require('includes/filter-functions.php');
	
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);
	
	$upload_success = false;
	
	$machform_data_path = '';
	
	if(!empty($_FILES) && !empty($_POST['session_id'])){
		
		//get the session for this user
		$session_id  	 = trim($_POST['session_id']);
		$uploader_origin = isset($_POST['uploader_origin']) ? trim($_POST['uploader_origin']) : false;

		session_id($session_id);
		session_start();
		
		//check permission, is the user allowed to access this page?
		if(empty($_SESSION['mf_user_privileges']['priv_administer']) && empty($_SESSION['mf_user_privileges']['priv_new_forms'])){
			die("You don't have permission to import form");
		}

		if(!file_exists($machform_data_path.$mf_settings['data_dir']."/temp")){
			$old_mask = umask(0);
			mkdir($machform_data_path.$mf_settings['data_dir']."/temp",0755);
			umask($old_mask);

			@file_put_contents($machform_data_path.$mf_settings['data_dir']."/temp/index.html",' ');
		}

		if(!is_writable($machform_data_path.$mf_settings['data_dir']."/temp")){
			echo "Unable to write into data folder! (".$machform_data_path.$mf_settings['data_dir']."/temp)";
		}
		
		$file_enable_type_limit = 1;
		$file_block_or_allow 	= 'a';
		$file_type_list 		= 'json'; //only allow json file
			

		//validate file type
		$ext = pathinfo(strtolower($_FILES['Filedata']['name']), PATHINFO_EXTENSION);
		$ext = preg_replace( '/[^a-z0-9]/i', '', $ext); //make sure the extension only contain alphanumeric

		//if ext is empty, the file extension most likely malicious, reject the upload
		if(empty($ext)){
			die('Error! Filetype unknown!');
		}

		if(!empty($file_type_list) && !empty($file_enable_type_limit)){
		
			$file_type_array = explode(',',$file_type_list);
			$file_type_array = array_map('strtolower', $file_type_array);
			
			if($file_block_or_allow == 'b'){
				if(in_array($ext,$file_type_array)){
					die('Error! Filetype blocked!');
				}	
			}else if($file_block_or_allow == 'a'){
				if(!in_array($ext,$file_type_array)){
					die('Error! Only .json files allowed!');
				}
			}
		}
		
		$file_token = md5(uniqid(rand(), true));

		//move file and check for invalid file
		$tokenized_file_name = "form_{$file_token}-{$_FILES['Filedata']['name']}";
		$destination_file = $machform_data_path.$mf_settings['data_dir']."/temp/{$tokenized_file_name}";
		$destination_file = mf_sanitize($destination_file);

		$source_file	  = $_FILES['Filedata']['tmp_name'];
		if(move_uploaded_file($source_file,$destination_file)){
			$uploaded_file_name = $tokenized_file_name;	
			$upload_success = true;
		}else{
			$upload_success = false;
			$error_message  = "Unable to move file!";
		}
		
	}
	
	$response_data = new stdClass();
	
	if($upload_success){
		$response_data->status    	 	 = "ok";
		$response_data->file_name 	 	 = mf_sanitize($tokenized_file_name);
	}else{
		$response_data->status    	= "error";
		$response_data->message 	= $error_message;
	}
	
	$response_json = json_encode($response_data);
	
	echo $response_json;
	
	//we need to use output buffering to be able capturing error messages
	$output = ob_get_contents();
	ob_end_clean();
	
	echo $output;
?>