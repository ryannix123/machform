<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	//check if user logged in or not
	//if not redirect them into login page

	//first we need to check if the user has "remember me" cookie or not
	if(!empty($_COOKIE['mf_remember']) && empty($_SESSION['mf_logged_in'])){
		$dbh	= mf_connect_db();
		$query  = "SELECT 
						`user_id`,
						`priv_administer`,
						`priv_new_forms`,
						`priv_new_themes` 
					FROM 
						`".MF_TABLE_PREFIX."users` 
					WHERE 
						`cookie_hash`=? and `status`=1";
		$params = array($_COOKIE['mf_remember']);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		$mf_user_id 		  = $row['user_id'];
		$mf_priv_administer	  = (int) $row['priv_administer'];
		$mf_priv_new_forms	  = (int) $row['priv_new_forms'];
		$mf_priv_new_themes	  = (int) $row['priv_new_themes'];

		if(!empty($mf_user_id)){
			$_SESSION['mf_logged_in'] = true;
			$_SESSION['mf_user_id']	  = $mf_user_id;
			$_SESSION['mf_user_privileges']['priv_administer'] = $mf_priv_administer;
			$_SESSION['mf_user_privileges']['priv_new_forms']  = $mf_priv_new_forms;
			$_SESSION['mf_user_privileges']['priv_new_themes'] = $mf_priv_new_themes;
		}

	}

	if(empty($_SESSION['mf_logged_in'])){
		$current_dir = dirname($_SERVER['PHP_SELF']);
      	if($current_dir == "/" || $current_dir == "\\"){
			$current_dir = '';
		}
		
		$_SESSION['MF_LOGIN_ERROR'] = 'Your session has expired. Please login.';
		header("Location: ".$current_dir.'/index.php?from='.base64_encode($_SERVER['REQUEST_URI']));
		exit;
	}
	
?>