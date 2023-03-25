<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
 	require('config.php');
	require('lib/libsodium/autoload.php');
	require('lib/php-captcha/php-captcha.inc.php');
	require('includes/db-core.php');
	require('includes/helper-functions.php');

	session_start();	

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	$captcha_public_key  = base64_decode($mf_settings['captcha_public_key']);
	$captcha_private_key = base64_decode($mf_settings['captcha_private_key']);

	$captcha_encryption_keypair = \Sodium\crypto_box_keypair_from_secretkey_and_publickey($captcha_private_key,$captcha_public_key);

	$captcha_code = trim($_GET['c']);
	$captcha_code_plain = \Sodium\crypto_box_seal_open(base64_decode($captcha_code),$captcha_encryption_keypair);
	
   	$fonts = array('lib/php-captcha/VeraSeBd.ttf','lib/php-captcha/VeraBd.ttf');
   	$captcha = new PhpCaptcha($fonts, 200, 60);
   	$captcha->SetCode($captcha_code_plain);
   	$captcha->Create();
	
?>