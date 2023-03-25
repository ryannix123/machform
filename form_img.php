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
	
	//get query string and parse it, query string is base64 encoded
	$query_string = trim($_GET['q']);
	parse_str(base64_decode($query_string),$params);
	
	$file_name 	= $params['file_name'];
	$form_id 	= (int) $params['form_id'];

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	$extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
		        
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
	         
	// Fix IE bug [0]
	$header_file = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ? preg_replace('/\./', '%2e', $filename_only, substr_count($filename_only, '.') - 1) : $filename_only;

	//Prepare headers
	header("Content-Type: " . $type);
	header("Content-Transfer-Encoding: binary");
	header("Content-Disposition: inline; filename=\"" . addslashes($file_name) . "\"");
	        
	        
    $query = "SELECT file_content FROM ".MF_TABLE_PREFIX."form_images_files WHERE form_id = ? AND file_name = ?";
	
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