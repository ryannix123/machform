<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	require('includes/init.php');

	@ini_set("memory_limit","-1");

	require('config.php');
	require('includes/db-core.php');
	require('includes/helper-functions.php');

	defined('MF_STORE_FILES_AS_BLOB') or define('MF_STORE_FILES_AS_BLOB',false);

	//get query string and parse it, query string is base64 encoded
	$query_string = trim($_GET['q']);
	parse_str(base64_decode($query_string),$params);
	
	$file_name 	= $params['file_name'];
	$form_id 	= (int) $params['form_id'];

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	$extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
		                 
	// Fix IE bug [0]
	$header_file = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ? preg_replace('/\./', '%2e', $filename_only, substr_count($filename_only, '.') - 1) : $filename_only;

	//Prepare headers
	header("Content-Type: application/pdf");
	header("Content-Transfer-Encoding: binary");
	header("Content-Disposition: inline; filename=\"" . addslashes($file_name) . "\"");
	        
	        
    $query = "SELECT file_content FROM ".MF_TABLE_PREFIX."form_pdf_files WHERE form_id = ? AND file_name = ?";
	
	$sth = $dbh->prepare($query);
	try{
		$sth->execute(array($form_id,$file_name));
		$sth->bindColumn(1, $file_data, PDO::PARAM_LOB);
		$sth->fetch(PDO::FETCH_BOUND);

		if(is_string($file_data)){
			echo $file_data;
		}else{
			fpassthru($file_data);
		}
	}catch(PDOException $e) {
		error_log("MySQL Error. Query Failed: ".$e->getMessage());
		
		$sth->debugDumpParams();
		die("Query Failed: ".$e->getMessage());
	}
    

?>