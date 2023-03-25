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

	//the default is not to store file upload as blob, unless defined otherwise within config.php file
	defined('MF_STORE_FILES_AS_BLOB') or define('MF_STORE_FILES_AS_BLOB',false);
	
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);
	
	$upload_success = false;
		
	if(!empty($_FILES) && !empty($_POST['session_id']) && !empty($_POST['form_id'])){
		
		//get the session for this user
		$session_id	= trim($_POST['session_id']);
		$form_id	= (int) $_POST['form_id'];	
		
		session_id($session_id);
		session_start();
		
		//validate the session and make sure to user is logged in
		if(empty($_SESSION['mf_logged_in'])){
			die("You don't have permission to upload PDF");
		}

		if(!file_exists($mf_settings['data_dir']."/form_{$form_id}/pdf")){
			//create data folder for this form if not exist
			if(is_writable($mf_settings['data_dir']) && !file_exists($mf_settings['data_dir']."/form_{$form_id}")){
				
				$old_mask = umask(0);
				mkdir($mf_settings['data_dir']."/form_{$form_id}",0755);
				
				if($mf_settings['data_dir'] != $mf_settings['upload_dir']){
					@mkdir($mf_settings['upload_dir']."/form_{$form_id}",0755);
				}
				umask($old_mask);
			}
			
			$old_mask = umask(0);
			mkdir($mf_settings['data_dir']."/form_{$form_id}/pdf",0755);
			umask($old_mask);

			@file_put_contents($mf_settings['data_dir']."/form_{$form_id}/pdf/index.html",' '); //write empty index.html
		}

		if(!is_writable($mf_settings['data_dir']."/form_{$form_id}/pdf")){
			echo "Unable to write into data folder! (".$mf_settings['data_dir']."/form_{$form_id}/pdf)";
		}
		
		$file_enable_type_limit = 1;
		$file_block_or_allow 	= 'a';
		$file_type_list 		= 'pdf'; //only allow pdf files
			

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
					die('Error! Only PDF files allowed!');
				}
			}
		}
		
		$file_token = md5(uniqid(rand(), true));

		//move file and check for invalid file
		$destination_file = $mf_settings['data_dir']."/form_{$form_id}/pdf/pdf_{$file_token}-{$_FILES['Filedata']['name']}";
		$destination_file = mf_sanitize($destination_file);

		$source_file	  = $_FILES['Filedata']['tmp_name'];
		if(move_uploaded_file($source_file,$destination_file)){
			$uploaded_file_url = str_replace('/./data/', '/data/', $mf_settings['base_url'].$destination_file);	
			$upload_success = true;

			if(MF_STORE_FILES_AS_BLOB === true){
				mf_ap_form_pdf_files_insert($dbh,$form_id,$destination_file);

				$file_name_only = pathinfo($destination_file,PATHINFO_BASENAME);
				$uploaded_file_url = $mf_settings['base_url'].'form_pdf.php?q='.base64_encode("form_id=".$form_id."&file_name=".$file_name_only);
			}
		}else{
			$upload_success = false;
			$error_message  = "Unable to move file!";
		}
		
	}
	
	$response_data = new stdClass();
	
	if($upload_success){
		$response_data->status    	 	 = "ok";
		$response_data->pdf_url 	 	 = mf_sanitize($uploaded_file_url);
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