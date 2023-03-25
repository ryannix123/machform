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
	require('includes/users-functions.php');

	$form_id 	 = (int) trim($_REQUEST['id']);
	
	if(empty($form_id)){
		die("Invalid form ID.");
	}

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_form permission
		if(empty($user_perms['edit_form'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to edit this form.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}

	//get form properties
	$query 	= "select 
					form_name,
					form_frame_height,
					form_captcha
			     from 
			     	 ".MF_TABLE_PREFIX."forms 
			    where 
			    	 form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row)){
		$form_name 	= $row['form_name'];
		$clean_form_name = preg_replace("/[^A-Za-z0-9_-]/","",$form_name);
	}

	$ssl_suffix = mf_get_ssl_suffix();

	$current_dir 	  = rtrim(dirname($_SERVER['PHP_SELF']));
	if($current_dir == "/" || $current_dir == "\\"){
		$current_dir = '';
	}
	
	$absolute_dir_path = rtrim(dirname($_SERVER['SCRIPT_FILENAME'])); 
	$http_host = parse_url($mf_settings['base_url'], PHP_URL_HOST);

	$php_embed_form_code =<<<EOT
<?php 

date_default_timezone_set(@date_default_timezone_get());
session_start();

require("{$absolute_dir_path}/machform.php");

\$mf_param['form_id'] = {$form_id};
\$mf_param['base_path'] = 'http{$ssl_suffix}://{$http_host}{$current_dir}/';
\$mf_param['show_border'] = true;
display_machform(\$mf_param);

?>
EOT;
	
	if(empty($clean_form_name)){
		$clean_form_name = "form";
	}


	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public", false);
	header("Content-Description: File Transfer");
	header("Content-Type: text/plain");
	header("Content-Disposition: attachment; filename=\"{$clean_form_name}.php\"");
	        
	$output_stream = fopen('php://output', 'w');
	fwrite($output_stream, $php_embed_form_code);
	fclose($output_stream);
?>