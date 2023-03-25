<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}

	//if the user blocked cookies and mfsid parameter available, use it as session id
	//in this case, database handler is being used for session
	if(defined('MF_DB_NAME') && empty($_COOKIE['mf_has_cookie']) && !empty($_REQUEST['mfsid'])){
		//uses db handler
		$session = new MySqlSessionHandler();
		$session->setDbDetails();

		$session->setDbTable(MF_TABLE_PREFIX .'sessions');
		session_set_save_handler(array($session, 'open'),
		                         array($session, 'close'),
		                         array($session, 'read'),
		                         array($session, 'write'),
		                         array($session, 'destroy'),
		                         array($session, 'gc'));

		// The following prevents unexpected effects when using objects as save handlers.
		register_shutdown_function('session_write_close');

		$mfsid = trim($_REQUEST['mfsid']);
		session_id($mfsid);
	}

	//check if HTTPS enabled or not
	$is_https_enabled = false;
	if(!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != 'off')){
		$is_https_enabled = true;
	}else if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
		$is_https_enabled = true;
	}else if (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && $_SERVER['HTTP_FRONT_END_HTTPS'] == 'on'){
		$is_https_enabled = true;
	}

	//prevent CSRF
	if($is_https_enabled){
		if(PHP_VERSION_ID < 70300) {
			//for PHP version 7.2
			session_set_cookie_params(0, '/; samesite=Lax; Secure');
		}else{
			//for PHP version 7.3 or newer
			ini_set('session.cookie_samesite', 'Lax');
			ini_set('session.cookie_secure', true);
		}
	}
	session_start();
	
	date_default_timezone_set(@date_default_timezone_get());	
	error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE & ~E_WARNING);
	
	@header("Content-Type: text/html; charset=UTF-8");
?>
