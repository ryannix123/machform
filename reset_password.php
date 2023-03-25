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
	require('lib/swift-mailer/swift_required.php');
	require('lib/password-hash.php');
	
	$target_email = strtolower(trim($_POST['target_email']));

	if(empty($target_email)){
		die("Invalid parameters.");
	}
	
	
	$dbh = mf_connect_db();
	$ssl_suffix = mf_get_ssl_suffix();
	$mf_settings = mf_get_settings($dbh);
	
	//validate the email address
	$is_registered_email = false;
	
	$query  = "SELECT user_id,user_fullname FROM `".MF_TABLE_PREFIX."users` WHERE `user_email`=? and `status`=1";
	$params = array($target_email);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row['user_id'])){
		$user_id = $row['user_id'];

		$user_fullname_array = explode(' ',$row['user_fullname']);
		$user_firstname 	 = $user_fullname_array[0];
	}else{
		echo '{"status" : "error", "message" : "Incorrect email address. Please try again."}';
		return true;
	}

	//generate reset hash
	$reset_hash = md5(uniqid(rand(), true)); 

	//save it into ap_users table
	$query 	= "UPDATE ".MF_TABLE_PREFIX."users SET reset_hash=?,reset_date=now() WHERE user_id=?"; 
	$params = array($reset_hash,$user_id);
					
	mf_do_query($query,$params,$dbh);

	$http_host = parse_url($mf_settings['base_url'], PHP_URL_HOST);
	
	//send reset link to user
	$q_string  = base64_encode("user_id={$user_id}&reset_hash={$reset_hash}");
	$reset_url = 'http'.$ssl_suffix.'://'.$http_host.mf_get_dirname($_SERVER['PHP_SELF']).'/reset.php?q='.$q_string;

	$ip_address = $_SERVER['REMOTE_ADDR'];

    $subject = "How to reset your MachForm password";
    $email_content =<<< EOT
Hello {$user_firstname},<br />
<br />
You recently initiated a password reset for your MachForm login.
To complete the process, click the link below.<br /><br/>
<a href="{$reset_url}"><strong style="font-size: 120%">Reset Password Now</strong></a>
<br/>
<br/>
This link will expire in one hour.
<br /><br />
If you didn't make this request, it's likely that another user has entered your email address by mistake and your account is still secure. You can ignore this email and your password will not be changed.
<br /><br />
<small>The request to reset the password was made from {$ip_address}</small>
EOT;
    	
    $subject = utf8_encode($subject);

    //create the mail transport
	if(!empty($mf_settings['smtp_enable'])){
		$s_transport = Swift_SmtpTransport::newInstance($mf_settings['smtp_host'], $mf_settings['smtp_port']);
		
		if(!empty($mf_settings['smtp_secure'])){
			//port 465 for (SSL), while port 587 for (TLS)
			if($mf_settings['smtp_port'] == '587'){
				$s_transport->setEncryption('tls');
			}else{
				$s_transport->setEncryption('ssl');
			}
		}
			
		if(!empty($mf_settings['smtp_auth'])){
			$s_transport->setUsername($mf_settings['smtp_username']);
  			$s_transport->setPassword($mf_settings['smtp_password']);
		}
	}else{
		$s_transport = Swift_MailTransport::newInstance(); //use PHP mail() transport
	}
    	
    //create mailer instance
	$s_mailer = Swift_Mailer::newInstance($s_transport);
		
	$from_name  = html_entity_decode($mf_settings['default_from_name'],ENT_QUOTES);
	$from_email = $mf_settings['default_from_email'];
		
	if(!empty($target_email)){
		$s_message = Swift_Message::newInstance()
		->setCharset('utf-8')
		->setMaxLineLength(1000)
		->setSubject($subject)
		->setFrom(array($from_email => $from_name))
		->setSender($from_email)
		->setReturnPath($from_email)
		->setTo($target_email)
		->setBody($email_content, 'text/html');

		//send the message
		$send_result = $s_mailer->send($s_message);
		if(empty($send_result)){
			echo '{"status" : "error", "message" : "Unable to send password reset link."}';
		}else{
			echo '{"status" : "ok", "message" : "A reset password link has been generated and sent to your email. Please check your email and follow the instruction."}';
		}
	}

?>