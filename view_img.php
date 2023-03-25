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

	defined('MF_STORE_FILES_AS_BLOB') or define('MF_STORE_FILES_AS_BLOB',false);

	ob_clean(); //clean the output buffer
	
	//get query string and parse it, query string is base64 encoded
	$query_string = trim($_GET['q']);
	parse_str(base64_decode($query_string),$params);
	
	$form_id 	= (int) $params['form_id'];
	$id      	= (int) $params['id'];
	$field_name = preg_replace("/\W/", '', $params['el']);
	$file_hash  = $params['hash'];
	
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	//get filename
	$query 	= "select `{$field_name}` from `".MF_TABLE_PREFIX."form_{$form_id}` where id=?";
	$params = array($id);
		
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$filename_array  = array();
	$filename_array  = explode('|',$row[$field_name]);
				
	$filename_md5_array = array();
	foreach ($filename_array as $value) {
		$filename_md5_array[] = md5($value);
	}

	$file_key = array_keys($filename_md5_array,$file_hash);
	if(empty($file_key)){
		die("Error. File Not Found!");
	}else{
		$file_key = $file_key[0];
	}
	
	$complete_filename = $filename_array[$file_key];
	
	//remove the element_x-xx- suffix we added to all uploaded files
	$file_1 	   	= substr($complete_filename,strpos($complete_filename,'-')+1);
	$filename_only 	= substr($file_1,strpos($file_1,'-')+1);
	 
	$target_file = $mf_settings['upload_dir']."/form_{$form_id}/files/{$complete_filename}";
	
	$extension = strtolower(pathinfo($filename_only, PATHINFO_EXTENSION));
		        
	// Determine correct MIME type
	switch($extension){
			case "asf":     $type = "video/x-ms-asf";                break;
	        case "avi":     $type = "video/x-msvideo";               break;
	        case "bin":     $type = "application/octet-stream";      break;
	        case "bmp":     $type = "image/bmp";                     break;
	        case "cgi":     $type = "magnus-internal/cgi";           break;
	        case "css":     $type = "text/css";                      break;
	        case "dcr":     $type = "application/x-director";        break;
	        case "dxr":     $type = "application/x-director";        break;
	        case "dll":     $type = "application/octet-stream";      break;
	        case "doc":     $type = "application/msword";            break;
	        case "exe":     $type = "application/octet-stream";      break;
	        case "gif":     $type = "image/gif";                     break;
			case "gtar":    $type = "application/x-gtar";            break;
			case "gz":      $type = "application/gzip";              break;
			case "htm":     $type = "text/html";                     break;
			case "html":    $type = "text/html";                     break;
			case "iso":     $type = "application/octet-stream";      break;
			case "jar":     $type = "application/java-archive";      break;
			case "java":    $type = "text/x-java-source";            break;
			case "jnlp":    $type = "application/x-java-jnlp-file";  break;
			case "js":      $type = "application/x-javascript";      break;
			case "jpg":     $type = "image/jpeg";                    break;
			case "jpe":     $type = "image/jpeg";                    break;
			case "jpeg":    $type = "image/jpeg";                    break;
			case "lzh":     $type = "application/octet-stream";      break;
			case "mdb":     $type = "application/mdb";               break;
			case "mid":     $type = "audio/x-midi";                  break;
			case "midi":    $type = "audio/x-midi";                  break;
			case "mov":     $type = "video/quicktime";               break;
			case "mp2":     $type = "audio/x-mpeg";                  break;
			case "mp3":     $type = "audio/mpeg";                    break;
			case "mpg":     $type = "video/mpeg";                    break;
			case "mpe":     $type = "video/mpeg";                    break;
			case "mpeg":    $type = "video/mpeg";                    break;
			case "pdf":     $type = "application/pdf";               break;
			case "php":     $type = "application/x-httpd-php";       break;
			case "php3":    $type = "application/x-httpd-php3";      break;
			case "php4":    $type = "application/x-httpd-php";       break;
			case "png":     $type = "image/png";                     break;
			case "ppt":     $type = "application/mspowerpoint";      break;
			case "qt":      $type = "video/quicktime";               break;
			case "qti":     $type = "image/x-quicktime";             break;
			case "rar":     $type = "encoding/x-compress";           break;
			case "ra":      $type = "audio/x-pn-realaudio";          break;
			case "rm":      $type = "audio/x-pn-realaudio";          break;
			case "ram":     $type = "audio/x-pn-realaudio";          break;
			case "rtf":     $type = "application/rtf";               break;
			case "swa":     $type = "application/x-director";        break;
			case "swf":     $type = "application/x-shockwave-flash"; break;
			case "tar":     $type = "application/x-tar";             break;
			case "tgz":     $type = "application/gzip";              break;
			case "tif":     $type = "image/tiff";                    break;
			case "tiff":    $type = "image/tiff";                    break;
			case "torrent": $type = "application/x-bittorrent";      break;
			case "txt":     $type = "text/plain";                    break;
			case "wav":     $type = "audio/wav";                     break;
			case "wma":     $type = "audio/x-ms-wma";                break;
			case "wmv":     $type = "video/x-ms-wmv";                break;
			case "xls":     $type = "application/vnd.ms-excel";      break;
			case "xml":     $type = "application/xml";               break;
			case "7z":      $type = "application/x-compress";        break;
			case "zip":     $type = "application/x-zip-compressed";  break;
			default:        $type = "application/force-download";    break; 
	}

	if(MF_STORE_FILES_AS_BLOB !== true){
		if(!file_exists($target_file)){
			$target_file .= '.tmp';
		}

		if(file_exists($target_file)){
			
			 
	         // Fix IE bug [0]
	         $header_file = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ? preg_replace('/\./', '%2e', $filename_only, substr_count($filename_only, '.') - 1) : $filename_only;

	         //Prepare headers
	         header("Content-Type: " . $type);
	         header("Accept-Ranges: bytes");
	         header("Content-Transfer-Encoding: binary");
	         header("Content-Length: " . filesize($target_file));
	         header("Content-Disposition: inline; filename=\"" . addslashes($header_file) . "\"");
	               
	         //send file data for display
	         if ($stream = fopen($target_file, 'rb')){
	         	while(!feof($stream) && connection_status() == 0){
	            	//reset time limit for big files
	                @set_time_limit(0);
	                print(fread($stream,1024*8));
	                flush();
	             }
	             fclose($stream);
	         }
		}else{
			echo 'File not found!';
		}
	}else{
		// Fix IE bug [0]
	    $header_file = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ? preg_replace('/\./', '%2e', $filename_only, substr_count($filename_only, '.') - 1) : $filename_only;

	    //Prepare headers
	    header("Content-Type: " . $type);
	    header("Content-Transfer-Encoding: binary");
	    header("Content-Disposition: inline; filename=\"" . addslashes($header_file) . "\"");

    	$query = "SELECT file_content FROM ".MF_TABLE_PREFIX."form_{$form_id}_files WHERE file_name = ? or file_name = ?";
		
		$sth = $dbh->prepare($query);
		try{
			$sth->execute(array($complete_filename,$complete_filename.'.tmp'));
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
    }

?>