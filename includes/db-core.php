<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
 	defined('MF_SQL_DEBUG_MODE') or define('MF_SQL_DEBUG_MODE',true);

	function mf_connect_db(){
		try {
		  $dbh = new PDO('mysql:host='.MF_DB_HOST.';dbname='.MF_DB_NAME, MF_DB_USER, MF_DB_PASSWORD,
			  				 array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true)
			  				 );
		  $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		  $dbh->query("SET NAMES utf8");
		  $dbh->query("SET sql_mode = ''");
		  
		  //apply system timezone automatically once connected
		  mf_set_system_timezone($dbh);

		  return $dbh;
		} catch(PDOException $e) {
			error_log("MySQL Error. Error connecting to the database: ".$e->getMessage());

			if(MF_SQL_DEBUG_MODE === true){
		    	die("Error connecting to the database: ".$e->getMessage());
			}else{
				die("Error connecting to the database.");
			}
		}
	}
	
	function mf_do_query($query,$params,$dbh){
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			error_log("MySQL Error. Query Failed: ".$e->getMessage());

			if(MF_SQL_DEBUG_MODE === true){
		    	$sth->debugDumpParams();
				die("Query Failed: ".$e->getMessage());
			}else{
				die("Query Failed.");
			}
		}
		
		return $sth;
	}
	
	function mf_do_fetch_result($sth){
		return $sth->fetch(PDO::FETCH_ASSOC);	
	}
	
	function mf_ap_forms_update($id,$data,$dbh){
		
		$update_values = '';
		$params = array();
		
		//dynamically create the sql update string, based on the input given
		foreach ($data as $key=>$value){
			if($key == 'form_id'){
				continue;
			}

			if($value === "null"){
					$value = null;
			}
			
			$update_values .= "`{$key}` = :{$key},";
			$params[':'.$key] = $value;
		}
		$update_values = rtrim($update_values,',');
		
		$params[':form_id'] = $id;
		
		$query = "UPDATE `".MF_TABLE_PREFIX."forms` set 
									$update_values
							  where 
						  	  		form_id = :form_id";

		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			error_log("MySQL Error. Query Failed: ".$e->getMessage());

			if(MF_SQL_DEBUG_MODE === true){
		    	$sth->debugDumpParams();
				echo "Query Failed: ".$e->getMessage();
				$error_message = "Query Failed: ".$e->getMessage();
			}else{
				echo "Query Failed";
				$error_message = "Query Failed";
			}

			return $error_message;
		}
		
		return true;
	}

	function mf_ap_settings_update($data,$dbh){
		
		$update_values = '';
		$params = array();
		
		//dynamically create the sql update string, based on the input given
		foreach ($data as $key=>$value){
			if($value === "null"){
					$value = null;
			}
			
			$update_values .= "`{$key}`= :{$key},";
			$params[':'.$key] = $value;
		}
		$update_values = rtrim($update_values,',');
		
		$query = "UPDATE `".MF_TABLE_PREFIX."settings` set $update_values";

		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			error_log("MySQL Error. Query Failed: ".$e->getMessage());

			if(MF_SQL_DEBUG_MODE === true){
		    	$sth->debugDumpParams();
				echo "Query Failed: ".$e->getMessage();
				$error_message = "Query Failed: ".$e->getMessage();
			}else{
				echo "Query Failed";
				$error_message = "Query Failed";
			}
			return $error_message;
		}
		
		return true;
	}
	
	function mf_ap_form_themes_update($id,$data,$dbh){
		
		$update_values = '';
		$params = array();
		
		//dynamically create the sql update string, based on the input given
		foreach ($data as $key=>$value){
			if($value === "null"){
					$value = null;
			}
			
			$update_values .= "`{$key}`= :{$key},";
			$params[':'.$key] = $value;
		}
		$update_values = rtrim($update_values,',');
		
		$params[':theme_id'] = $id;
		
		$query = "UPDATE `".MF_TABLE_PREFIX."form_themes` set 
									$update_values
							  where 
						  	  		theme_id = :theme_id";
		
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			error_log("MySQL Error. Query Failed: ".$e->getMessage());

			if(MF_SQL_DEBUG_MODE === true){
				$error_message = "Query Failed: ".$e->getMessage();
			}else{
				$error_message = "Query Failed";
			}

			return $error_message;
		}
		
		return true;
	}

	//insert file into ap_form_xxx_files table
	function mf_ap_form_files_insert($dbh,$form_id,$file_path,$options=array()){
		$query = "insert into ".MF_TABLE_PREFIX."form_{$form_id}_files(file_name,file_content) values(?,?)";
		$fp    = fopen($file_path, 'rb');
		
		$file_name = pathinfo($file_path,PATHINFO_BASENAME);

		$sth = $dbh->prepare($query);
		try{
			$sth->bindParam(1, $file_name);
			$sth->bindParam(2, $fp, PDO::PARAM_LOB);

			$dbh->beginTransaction();
			$sth->execute();
			$dbh->commit();

			//remove the file once saved into database
			@unlink($file_path);
		}catch(PDOException $e) {
			error_log("MySQL Error. Query Failed: ".$e->getMessage());

			if(MF_SQL_DEBUG_MODE === true){
		    	$sth->debugDumpParams();
				die("Query Failed: ".$e->getMessage());
			}else{
				die("Query Failed.");
			}
		}

		if(!empty($options['deleted_file'])){
			$file_to_delete = pathinfo($options['deleted_file'],PATHINFO_BASENAME);

			$query = "DELETE FROM ".MF_TABLE_PREFIX."form_{$form_id}_files WHERE file_name = ?";
			$sth = $dbh->prepare($query);

			try{
				$sth->execute(array($file_to_delete));
			}catch(PDOException $e) {
				error_log("MySQL Error. Query Failed: ".$e->getMessage());

				if(MF_SQL_DEBUG_MODE === true){
			    	$sth->debugDumpParams();
					die("Query Failed: ".$e->getMessage());
				}else{
					die("Query Failed.");
				}
			}
		}
	}

	//load file data from ap_form_xxx_files table into the filesystem on specified $file_path
	function mf_ap_form_files_load($dbh,$form_id,$file_path){
		$file_name = pathinfo($file_path,PATHINFO_BASENAME);

		$query = "SELECT file_content FROM ".MF_TABLE_PREFIX."form_{$form_id}_files WHERE file_name = ?";
		
		$sth = $dbh->prepare($query);
		try{
			$sth->execute(array($file_name));
			$sth->bindColumn(1, $file_data, PDO::PARAM_LOB);
			$sth->fetch(PDO::FETCH_BOUND);

			//save into file
			file_put_contents($file_path, $file_data);
		}catch(PDOException $e) {
			error_log("MySQL Error. Query Failed: ".$e->getMessage());

			if(MF_SQL_DEBUG_MODE === true){
		    	$sth->debugDumpParams();
				die("Query Failed: ".$e->getMessage());
			}else{
				die("Query Failed.");
			}
		}
	}

	//get binary data of the file from ap_form_xxx_files table
	function mf_read_ap_form_files_blob($dbh,$form_id,$file_path){
		$file_name = pathinfo($file_path,PATHINFO_BASENAME);

		$query = "SELECT file_content FROM ".MF_TABLE_PREFIX."form_{$form_id}_files WHERE file_name = ?";
		
		$sth = $dbh->prepare($query);
		try{
			$sth->execute(array($file_name));
			$sth->bindColumn(1, $file_data, PDO::PARAM_LOB);
			$sth->fetch(PDO::FETCH_BOUND);

			return $file_data;
		}catch(PDOException $e) {
			error_log("MySQL Error. Query Failed: ".$e->getMessage());

			if(MF_SQL_DEBUG_MODE === true){
		    	$sth->debugDumpParams();
				die("Query Failed: ".$e->getMessage());
			}else{
				die("Query Failed.");
			}
		}
	}

	//insert file into ap_form_themes_files table
	function mf_ap_form_themes_files_insert($dbh,$form_id,$file_path){
		$query = "insert into ".MF_TABLE_PREFIX."form_themes_files(file_name,file_content) values(?,?)";
		$fp    = fopen($file_path, 'rb');
		
		$file_name = pathinfo($file_path,PATHINFO_BASENAME);

		$sth = $dbh->prepare($query);
		try{
			$sth->bindParam(1, $file_name);
			$sth->bindParam(2, $fp, PDO::PARAM_LOB);

			$dbh->beginTransaction();
			$sth->execute();
			$dbh->commit();

			//remove the file once saved into database
			@unlink($file_path);
		}catch(PDOException $e) {
			error_log("MySQL Error. Query Failed: ".$e->getMessage());

			if(MF_SQL_DEBUG_MODE === true){
		    	$sth->debugDumpParams();
				die("Query Failed: ".$e->getMessage());
			}else{
				die("Query Failed.");
			}
		}
	}

	//insert file into ap_form_images_files table
	function mf_ap_form_images_files_insert($dbh,$form_id,$file_path){
		$query = "insert into ".MF_TABLE_PREFIX."form_images_files(form_id,file_name,file_content) values(?,?,?)";
		$fp    = fopen($file_path, 'rb');
		
		$file_name = pathinfo($file_path,PATHINFO_BASENAME);

		$sth = $dbh->prepare($query);
		try{
			$sth->bindParam(1, $form_id);
			$sth->bindParam(2, $file_name);
			$sth->bindParam(3, $fp, PDO::PARAM_LOB);

			$dbh->beginTransaction();
			$sth->execute();
			$dbh->commit();

			//remove the file once saved into database
			@unlink($file_path);
		}catch(PDOException $e) {
			error_log("MySQL Error. Query Failed: ".$e->getMessage());

			if(MF_SQL_DEBUG_MODE === true){
		    	$sth->debugDumpParams();
				die("Query Failed: ".$e->getMessage());
			}else{
				die("Query Failed.");
			}
		}
	}

	//insert file into ap_form_pdf_files table
	function mf_ap_form_pdf_files_insert($dbh,$form_id,$file_path){
		$query = "insert into ".MF_TABLE_PREFIX."form_pdf_files(form_id,file_name,file_content) values(?,?,?)";
		$fp    = fopen($file_path, 'rb');
		
		$file_name = pathinfo($file_path,PATHINFO_BASENAME);

		$sth = $dbh->prepare($query);
		try{
			$sth->bindParam(1, $form_id);
			$sth->bindParam(2, $file_name);
			$sth->bindParam(3, $fp, PDO::PARAM_LOB);

			$dbh->beginTransaction();
			$sth->execute();
			$dbh->commit();

			//remove the file once saved into database
			@unlink($file_path);
		}catch(PDOException $e) {
			error_log("MySQL Error. Query Failed: ".$e->getMessage());
			
			if(MF_SQL_DEBUG_MODE === true){
		    	$sth->debugDumpParams();
				die("Query Failed: ".$e->getMessage());
			}else{
				die("Query Failed.");
			}
		}
	}
	
	//check if a column name exist or not within a table
	//return true if column exist
	function mf_mysql_column_exist($table_name,$column_name,$dbh) {

		$query = "SHOW COLUMNS FROM $table_name LIKE '$column_name'";
		$sth = mf_do_query($query,array(),$dbh);
		$row = mf_do_fetch_result($sth);
		
		if(!empty($row)){
			return true;	
		}else{
			return false;
		}
	}
	
?>