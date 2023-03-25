<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	require('includes/init.php');
	
	require('config.php');
	require('includes/language.php');
	require('includes/db-core.php');
	require('includes/helper-functions.php');
	require('includes/check-session.php');

	require('includes/filter-functions.php');
	require('includes/entry-functions.php');
	require('includes/post-functions.php');
	require('lib/dompdf/autoload.inc.php');
	require('lib/swift-mailer/swift_required.php');
	require('includes/users-functions.php');
	
	$form_id  = (int) trim($_POST['form_id']);
	$entry_id = (int) trim($_POST['entry_id']);
	$target_email 	= trim($_POST['target_email']);
	$email_template = mf_sanitize($_POST['email_template']);
	$email_note 	= mf_sanitize($_POST['email_note']);
	$csrf_token 	= trim($_POST['csrf_token']);

	if(empty($form_id) || empty($entry_id) || empty($target_email)){
		die("Invalid parameters.");
	}

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);
	
	if(empty($email_template)){
		$email_template = 'notification';
	}

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_entries or view_entries permission
		if(empty($user_perms['edit_entries']) && empty($user_perms['view_entries'])){
			die("Access Denied. You don't have permission to access this page.");
		}
	}

	//save recent email
	$recent_emails = explode(',',$target_email);
	$_SESSION['mf_email_entry_recent_email'] = trim($recent_emails[0]);

	//get form properties data
	$query 	= "select 
					 form_language,
					 form_email,
					 esl_enable,
					 esl_from_name,
					 esl_from_email_address,
					 esl_replyto_email_address,
					 esl_subject,
					 esl_content,
					 esl_plain_text,
					 esl_pdf_enable,
					 esl_pdf_content,
					 esr_enable,
					 esr_email_address,
					 esr_from_name,
					 esr_from_email_address,
					 esr_subject,
					 esr_content,
					 esr_plain_text,
					 esr_pdf_enable,
					 esr_pdf_content
			     from 
			     	 `".MF_TABLE_PREFIX."forms` 
			    where 
			    	 form_id=?";
	$params = array($form_id);
		
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
		
	if(!empty($row['form_language'])){
		mf_set_language($row['form_language']);
	}

	$esl_from_name 	= $row['esl_from_name'];
	$esl_from_email_address    = $row['esl_from_email_address'];
	$esl_replyto_email_address = $row['esl_replyto_email_address'];
	$esl_subject 	= $row['esl_subject'];
	$esl_content 	= $row['esl_content'];
	$esl_plain_text	= $row['esl_plain_text'];
	$esl_enable     = $row['esl_enable'];
	$esl_pdf_enable  = $row['esl_pdf_enable'];
	$esl_pdf_content = $row['esl_pdf_content'];
		
	$esr_email_address 	= $row['esr_email_address'];
	$esr_from_name 	= $row['esr_from_name'];
	$esr_from_email_address = $row['esr_from_email_address'];
	$esr_subject 	= $row['esr_subject'];
	$esr_content 	= $row['esr_content'];
	$esr_plain_text	= $row['esr_plain_text'];
	$esr_enable		= $row['esr_enable'];
	$esr_pdf_enable  = $row['esr_pdf_enable'];
	$esr_pdf_content = $row['esr_pdf_content'];
	
	//get parameters for the email based on the selected template
	if($email_template == 'notification'){
		//from name
		if(!empty($esl_from_name)){
			if(is_numeric($esl_from_name)){
				$admin_email_param['from_name'] = '{element_'.$esl_from_name.'}';
			}else{
				$admin_email_param['from_name'] = $esl_from_name;
			}
		}else{
			if(!empty($mf_settings['default_from_name'])){
	    		$admin_email_param['from_name'] = $mf_settings['default_from_name'];
	    	}else{
	    		$admin_email_param['from_name'] = 'MachForm';	
	    	}
		}
				
		//from email address
		if(!empty($esl_from_email_address)){
			if(is_numeric($esl_from_email_address)){
				$admin_email_param['from_email'] = '{element_'.$esl_from_email_address.'}';
			}else{
				$admin_email_param['from_email'] = $esl_from_email_address;
			}
		}else{
			if(!empty($mf_settings['default_from_email'])){
	    		$admin_email_param['from_email'] = $mf_settings['default_from_email'];
	    	}else{
		    	$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
				$admin_email_param['from_email'] = "no-reply@{$domain}";
			}
		}

		//reply-to email address
		if(!empty($esl_replyto_email_address)){
			if(is_numeric($esl_replyto_email_address)){
				$admin_email_param['replyto_email'] = '{element_'.$esl_replyto_email_address.'}';
			}else{
				$admin_email_param['replyto_email'] = $esl_replyto_email_address;
			}
		}else{
			if(!empty($mf_settings['default_from_email'])){
	    		$admin_email_param['replyto_email'] = $mf_settings['default_from_email'];
	    	}else{
		    	$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
				$admin_email_param['replyto_email'] = "no-reply@{$domain}";
			}
		}
				
		//subject
		if(!empty($esl_subject)){
			$admin_email_param['subject'] = $esl_subject;
		}else{
			$admin_email_param['subject'] = '{form_name} [#{entry_no}]';
		}
				
		//content
		if(!empty($esl_content)){
			$admin_email_param['content'] = $esl_content;
		}else{
			$admin_email_param['content'] = '{entry_data}';
		}

		//additional note
		if(!empty($email_note)){
			if(!empty($esl_plain_text)){
				$admin_email_param['content'] = $email_note."\n\n--------------------------------------------\n\n".$admin_email_param['content'];
			}else{
				$email_note = nl2br($email_note);
				$admin_email_param['content'] = $email_note.'<hr width="60%" align="left" size="1" noshade="noshade" style="margin-top: 20px;margin-bottom: 20px">'.$admin_email_param['content'];
			}
		}

		//pdf attachment
		if(!empty($esl_pdf_enable)){
			$admin_email_param['pdf_enable']  = true;
			$admin_email_param['pdf_content'] = $esl_pdf_content;
		}

		$admin_email_param['as_plain_text'] = $esl_plain_text;
		$admin_email_param['target_is_admin'] = true; 

		mf_send_notification($dbh,$form_id,$entry_id,$target_email,$admin_email_param);
	}else if($email_template == 'confirmation'){
		//from name
		if(!empty($esr_from_name)){			
			if(is_numeric($esr_from_name)){
				$user_email_param['from_name'] = '{element_'.$esr_from_name.'}';
			}else{
				$user_email_param['from_name'] = $esr_from_name;
			}
		}else{
			$user_email_param['from_name'] = 'MachForm';
		}
		
		//from email address
		if(!empty($esr_from_email_address)){
			if(is_numeric($esr_from_email_address)){
				$user_email_param['from_email'] = '{element_'.$esr_from_email_address.'}';
			}else{
				$user_email_param['from_email'] = $esr_from_email_address;
			}
		}else{
			$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
			$user_email_param['from_email'] = "no-reply@{$domain}";
		}

		//reply-to email address
		if(!empty($esr_replyto_email_address)){
			if(is_numeric($esr_replyto_email_address)){
				$user_email_param['replyto_email'] = '{element_'.$esr_replyto_email_address.'}';
			}else{
				$user_email_param['replyto_email'] = $esr_replyto_email_address;
			}
		}else{
			$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
			$user_email_param['replyto_email'] = "no-reply@{$domain}";
		}
		
		//subject
		if(!empty($esr_subject)){
			$user_email_param['subject'] = $esr_subject;
		}else{
			$user_email_param['subject'] = '{form_name} - Receipt';
		}
		
		//content
		if(!empty($esr_content)){
			$user_email_param['content'] = $esr_content;
		}else{
			$user_email_param['content'] = '{entry_data}';
		}

		//additional note
		if(!empty($email_note)){
			if(!empty($esr_plain_text)){
				$user_email_param['content'] = $email_note."\n\n--------------------------------------------\n\n".$user_email_param['content'];
			}else{
				$email_note = nl2br($email_note);
				$user_email_param['content'] = $email_note.'<hr width="60%" align="left" size="1" noshade="noshade" style="margin-top: 20px;margin-bottom: 20px">'.$user_email_param['content'];
			}
		}

		//pdf attachment
		if(!empty($esr_pdf_enable)){
			$user_email_param['pdf_enable']  = true;
			$user_email_param['pdf_content'] = $esr_pdf_content;
		}
		
		$user_email_param['as_plain_text'] = $esr_plain_text;
		$user_email_param['target_is_admin'] = false; 
		
		mf_send_notification($dbh,$form_id,$entry_id,$target_email,$user_email_param);
	}else{
		//if custom email template from the logic is being used
		$exploded = array();
		$exploded = explode("-",$email_template); //the value is like: custom-x
		$rule_id  = (int) $exploded[1];

		//get the settings for the selected rule_id
		$query = "SELECT 
						custom_from_name,
						custom_from_email,
						custom_replyto_email,
						custom_subject,
						custom_content,
						custom_plain_text,
						custom_pdf_enable,
						custom_pdf_content 
					FROM 
						".MF_TABLE_PREFIX."email_logic 
				   WHERE 
						form_id = ? and rule_id = ?";
		$params = array($form_id,$rule_id);
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		$custom_from_name  	  = $row['custom_from_name'];
		$custom_from_email 	  = $row['custom_from_email'];
		$custom_replyto_email = $row['custom_replyto_email'];
		$custom_subject	   	  = $row['custom_subject'];
		$custom_content	   	  = $row['custom_content'];
		$custom_plain_text 	  = (int) $row['custom_plain_text'];
		$custom_pdf_enable 	  = (int) $row['custom_pdf_enable'];
		$custom_pdf_content	  = $row['custom_pdf_content'];

		$admin_email_param = array();

		//from name
		if(!empty($custom_from_name)){
			if(is_numeric($custom_from_name)){
				$admin_email_param['from_name'] = '{element_'.$custom_from_name.'}';
			}else{
				$admin_email_param['from_name'] = $custom_from_name;
			}
		}else{
			$admin_email_param['from_name'] = 'MachForm';
		}
		
		//from email address
		if(!empty($custom_from_email)){
			if(is_numeric($custom_from_email)){
				$admin_email_param['from_email'] = '{element_'.$custom_from_email.'}';
			}else{
				$admin_email_param['from_email'] = $custom_from_email;
			}
		}else{
			$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
			$admin_email_param['from_email'] = "no-reply@{$domain}";
		}

		//reply-to email address
		if(!empty($custom_replyto_email)){
			if(is_numeric($custom_replyto_email)){
				$admin_email_param['replyto_email'] = '{element_'.$custom_replyto_email.'}';
			}else{
				$admin_email_param['replyto_email'] = $custom_replyto_email;
			}
		}else{
			$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
			$admin_email_param['replyto_email'] = "no-reply@{$domain}";
		}
		
		//subject
		if(!empty($custom_subject)){
			$admin_email_param['subject'] = $custom_subject;
		}else{
			$admin_email_param['subject'] = '{form_name} [#{entry_no}]';
		}
		
		//content
		if(!empty($custom_content)){
			$admin_email_param['content'] = $custom_content;
		}else{
			$admin_email_param['content'] = '{entry_data}';
		}

		//additional note
		if(!empty($email_note)){
			if(!empty($custom_plain_text)){
				$admin_email_param['content'] = $email_note."\n\n--------------------------------------------\n\n".$admin_email_param['content'];
			}else{
				$email_note = nl2br($email_note);
				$admin_email_param['content'] = $email_note.'<hr width="60%" align="left" size="1" noshade="noshade" style="margin-top: 20px;margin-bottom: 20px">'.$admin_email_param['content'];
			}
		}

		//pdf attachment
		if(!empty($custom_pdf_enable)){
			$admin_email_param['pdf_enable']  = true;
			$admin_email_param['pdf_content'] = $custom_pdf_content;
		}
		
		$admin_email_param['as_plain_text'] = $custom_plain_text;
		$admin_email_param['target_is_admin'] = true; 
		$admin_email_param['check_hook_file'] = false;
		
		mf_send_notification($dbh,$form_id,$entry_id,$target_email,$admin_email_param);
	}
	
   	echo '{"status" : "ok"}';

?>