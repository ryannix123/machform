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
	
	require('includes/filter-functions.php');
	require('includes/users-functions.php');
	
	$dbh = mf_connect_db();
	
	if(empty($_POST['form_id'])){
		die("Error! You can't open this file directly");
	}

	$csrf_token = trim($_POST['csrf_token']);

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);

	//check for max_input_vars
	mf_init_max_input_vars();

	$form_id = (int) trim($_POST['form_id']);
	
	$field_rule_properties 			= mf_sanitize($_POST['field_rule_properties'] ?? '');
	$field_rule_conditions 			= mf_sanitize($_POST['field_rule_conditions'] ?? '');
	
	$page_rule_properties  			= mf_sanitize($_POST['page_rule_properties'] ?? '');
	$page_rule_conditions  			= mf_sanitize($_POST['page_rule_conditions'] ?? '');
	
	$email_rule_properties 			= mf_sanitize($_POST['email_rule_properties'] ?? '');
	$email_rule_conditions 			= mf_sanitize($_POST['email_rule_conditions'] ?? '');
	
	$webhook_rule_properties 	  	= mf_sanitize($_POST['webhook_rule_properties'] ?? '');
	$webhook_rule_conditions 	  	= mf_sanitize($_POST['webhook_rule_conditions'] ?? '');
	$webhook_keyvalue_param_names  	= mf_sanitize($_POST['webhook_keyvalue_param_names'] ?? '');
	$webhook_keyvalue_param_values 	= mf_sanitize($_POST['webhook_keyvalue_param_values'] ?? '');

	$success_rule_properties 		= mf_sanitize($_POST['success_rule_properties'] ?? '');
	$success_rule_conditions 		= mf_sanitize($_POST['success_rule_conditions'] ?? '');

	$logic_statuses  		 		= mf_sanitize($_POST['logic_status'] ?? '');
	
	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_form permission
		if(empty($user_perms['edit_form'])){
			die("Access Denied. You don't have permission to edit this form.");
		}
	}
	
	$logic_field_enable   = (int) $logic_statuses['logic_field_enable'];
	$logic_page_enable    = (int) $logic_statuses['logic_page_enable'];
	$logic_email_enable   = (int) $logic_statuses['logic_email_enable'];
	$logic_webhook_enable = (int) $logic_statuses['logic_webhook_enable'];
	$logic_success_enable = (int) $logic_statuses['logic_success_enable'];

	
	/** Field Logic **/
	if(!empty($field_rule_properties) && !empty($field_rule_conditions)){
		//save field_rule_properties into ap_field_logic_elements table
		$query = "delete from ".MF_TABLE_PREFIX."field_logic_elements where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		$query = "insert into `".MF_TABLE_PREFIX."field_logic_elements`(form_id,element_id,rule_show_hide,rule_all_any) values(?,?,?,?)";
		foreach ($field_rule_properties as $data) {
			$params = array($form_id,$data['element_id'],strtolower($data['rule_show_hide']),strtolower($data['rule_all_any']));
			mf_do_query($query,$params,$dbh);
		}
	
		//save field_rule_conditions into ap_field_logic_conditions table
		$query = "delete from ".MF_TABLE_PREFIX."field_logic_conditions where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		$query = "insert into `".MF_TABLE_PREFIX."field_logic_conditions`(form_id,target_element_id,element_name,rule_condition,rule_keyword) values(?,?,?,?,?)";
		foreach ($field_rule_conditions as $data) {
			$target_element_id = (int) $data['target_element_id'];
			$element_name	   = strtolower(trim($data['element_name']));
			$rule_condition    = strtolower(trim($data['condition']));
			$rule_keyword	   = trim($data['keyword']);

			$params = array($form_id,$target_element_id,$element_name,$rule_condition,$rule_keyword);
			mf_do_query($query,$params,$dbh);
		}	
	}

	/** Page Logic **/
	if(!empty($page_rule_properties) && !empty($page_rule_conditions)){
		//save page_rule_properties into ap_page_logic table
		$query = "delete from ".MF_TABLE_PREFIX."page_logic where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		$query = "insert into `".MF_TABLE_PREFIX."page_logic`(form_id,page_id,rule_all_any) values(?,?,?)";
		foreach ($page_rule_properties as $data) {
			$page_id = substr($data['page_id'],4); //remove 'page' prefix
			
			$params = array($form_id,$page_id,strtolower($data['rule_all_any']));
			mf_do_query($query,$params,$dbh);
		}

		//save page_rule_conditions into ap_page_logic_conditions table
		$query = "delete from ".MF_TABLE_PREFIX."page_logic_conditions where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		$query = "insert into `".MF_TABLE_PREFIX."page_logic_conditions`(form_id,target_page_id,element_name,rule_condition,rule_keyword) values(?,?,?,?,?)";
		foreach ($page_rule_conditions as $data) {
			$target_page_id  = substr($data['target_page_id'],4); //remove 'page' prefix
			$element_name	 = strtolower(trim($data['element_name']));
			$rule_condition  = strtolower(trim($data['condition']));
			$rule_keyword	 = trim($data['keyword']);

			$params = array($form_id,$target_page_id,$element_name,$rule_condition,$rule_keyword);
			mf_do_query($query,$params,$dbh);
		}
	}

	/** Email Logic **/
	if(!empty($email_rule_properties) && !empty($email_rule_conditions)){
		//save email_rule_properties into ap_email_logic table
		$query = "delete from ".MF_TABLE_PREFIX."email_logic where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		$query = "insert into 
							`".MF_TABLE_PREFIX."email_logic`
							(form_id,
							rule_id,
							rule_all_any,
							target_email,
							template_name,
							custom_from_name,
							custom_from_email,
							custom_replyto_email,
							custom_bcc,
							custom_subject,
							custom_content,
							custom_pdf_content,
							custom_plain_text,
							custom_pdf_enable,
							delay_notification_until_paid) 
					 values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

		foreach ($email_rule_properties as $data) {
			$rule_id 		= $data['rule_id'];
			$rule_all_any 	= strtolower($data['rule_all_any']);
			$target_email	= trim(str_replace(';',',',$data['target_email']));
			$custom_bcc		= trim(str_replace(';',',',$data['custom_bcc']));
			$template_name	= $data['template_name'];
			$custom_from_name 	= $data['custom_from_name'];
			$custom_from_email 	= $data['custom_from_email'];
			$custom_replyto_email = $data['custom_replyto_email'];
			$custom_subject 	= $data['custom_subject'];
			$custom_content 	= $data['custom_content'];
			$custom_pdf_content = $data['custom_pdf_content'];
			$custom_plain_text 	= (int) $data['custom_plain_text'];
			$custom_pdf_enable 	= (int) $data['custom_pdf_enable'];
			$delay_notification_until_paid 	= (int) $data['delay_notification_until_paid'];

			$params = array($form_id,
							$rule_id,
							$rule_all_any,
							$target_email,
							$template_name,
							$custom_from_name,
							$custom_from_email,
							$custom_replyto_email,
							$custom_bcc,
							$custom_subject,
							$custom_content,
							$custom_pdf_content,
							$custom_plain_text,
							$custom_pdf_enable,
							$delay_notification_until_paid);
			mf_do_query($query,$params,$dbh);
		}
	
		//save into ap_email_logic_conditions table
		$query = "delete from ".MF_TABLE_PREFIX."email_logic_conditions where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		$query = "insert into `".MF_TABLE_PREFIX."email_logic_conditions`(form_id,target_rule_id,element_name,rule_condition,rule_keyword) values(?,?,?,?,?)";
		foreach ($email_rule_conditions as $data) {
			$target_rule_id  = substr($data['target_rule_id'],5); //remove 'email' prefix
			$element_name	 = strtolower(trim($data['element_name']));
			$rule_condition  = strtolower(trim($data['condition']));
			$rule_keyword	 = trim($data['keyword']);

			$params = array($form_id,$target_rule_id,$element_name,$rule_condition,$rule_keyword);
			mf_do_query($query,$params,$dbh);
		}
	}

	/** Webhook Logic **/
	if(!empty($webhook_rule_properties) && !empty($webhook_rule_conditions)){
		//save into ap_webhook_options table
		$query = "delete from ".MF_TABLE_PREFIX."webhook_options where form_id=? and rule_id > 0";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		$query = "insert into 
							`".MF_TABLE_PREFIX."webhook_options`
							(form_id,
							rule_id,
							rule_all_any,
							webhook_url,
							webhook_method,
							webhook_format,
							webhook_raw_data,
							enable_http_auth,
							http_username,
							http_password,
							enable_custom_http_headers,
							custom_http_headers,
							delay_notification_until_paid) 
					 values(?,?,?,?,?,?,?,?,?,?,?,?,?)";

		foreach ($webhook_rule_properties as $data) {
			$rule_id 		= $data['rule_id'];
			$rule_all_any 	= strtolower($data['rule_all_any']);
			$webhook_url	= trim($data['webhook_url']);
			$webhook_method   = $data['webhook_method'];
			$webhook_format   = $data['webhook_format'];
			$webhook_raw_data = $data['webhook_raw_data'];
			$enable_http_auth = $data['webhook_enable_http_auth'];
			$http_username 	  = $data['webhook_http_username'];
			$http_password 	  = $data['webhook_http_password'];
			$enable_custom_http_headers 	= (int) $data['webhook_enable_custom_http_headers'];
			$custom_http_headers 			= $data['webhook_custom_http_headers'];
			$delay_notification_until_paid 	= (int) $data['delay_notification_until_paid'];			

			$params = array($form_id,
							$rule_id,
							$rule_all_any,
							$webhook_url,
							$webhook_method,
							$webhook_format,
							$webhook_raw_data,
							$enable_http_auth,
							$http_username,
							$http_password,
							$enable_custom_http_headers,
							$custom_http_headers,
							$delay_notification_until_paid);
			mf_do_query($query,$params,$dbh);
		}

		//save into ap_webhook_logic_conditions table
		$query = "delete from ".MF_TABLE_PREFIX."webhook_logic_conditions where form_id=? and target_rule_id > 0";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		$query = "insert into `".MF_TABLE_PREFIX."webhook_logic_conditions`(form_id,target_rule_id,element_name,rule_condition,rule_keyword) values(?,?,?,?,?)";
		foreach ($webhook_rule_conditions as $data) {
			$target_rule_id  = substr($data['target_rule_id'],7); //remove 'webhook' prefix
			$element_name	 = strtolower(trim($data['element_name']));
			$rule_condition  = strtolower(trim($data['condition']));
			$rule_keyword	 = trim($data['keyword']);

			$params = array($form_id,$target_rule_id,$element_name,$rule_condition,$rule_keyword);
			mf_do_query($query,$params,$dbh);
		}
	}

	//save into ap_webhook_parameters table
	$query = "delete from ".MF_TABLE_PREFIX."webhook_parameters where form_id=? and rule_id > 0";
	$params = array($form_id);
	mf_do_query($query,$params,$dbh);

	$webhook_keyvalue_param_values_array = array();
	if(!empty($webhook_keyvalue_param_values)){
		foreach($webhook_keyvalue_param_values as $value){
			$key = str_replace('value', 'name', $value['name']);
			$webhook_keyvalue_param_values_array[$key] = $value['value'];
		}
	}

	$query = "insert into ".MF_TABLE_PREFIX."webhook_parameters(form_id,rule_id,param_name,param_value) values(?,?,?,?)";
	if(!empty($webhook_keyvalue_param_names)){
		foreach ($webhook_keyvalue_param_names as $value) {
			$key = $value['name'];

			$exploded = array();
			$exploded = explode('_', $key);
			$rule_id  = (int) $exploded[1];

			$param_name  = $value['value'];
			$param_value = $webhook_keyvalue_param_values_array[$key];

			$params = array($form_id,$rule_id,$param_name,$param_value);
			mf_do_query($query,$params,$dbh);
		}
	}

	/** Success Page Logic **/
	if(!empty($success_rule_properties) && !empty($success_rule_conditions)){
		//save into ap_success_logic_options table
		$query = "delete from ".MF_TABLE_PREFIX."success_logic_options where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		$query = "insert into 
							`".MF_TABLE_PREFIX."success_logic_options`
							(form_id,
							rule_id,
							rule_all_any,
							success_type,
							success_message,
							redirect_url) 
					 values(?,?,?,?,?,?)";

		foreach ($success_rule_properties as $data) {
			$rule_id 		 = $data['rule_id'];
			$rule_all_any 	 = strtolower($data['rule_all_any']);
			$success_type 	 = strtolower($data['success_type']);
			$success_message = $data['success_message'];
			$redirect_url	 = trim($data['redirect_url']);
			
			$params = array($form_id,
							$rule_id,
							$rule_all_any,
							$success_type,
							$success_message,
							$redirect_url);
			mf_do_query($query,$params,$dbh);
		}
	
		//save into ap_success_logic_conditions table
		$query = "delete from ".MF_TABLE_PREFIX."success_logic_conditions where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		$query = "insert into `".MF_TABLE_PREFIX."success_logic_conditions`(form_id,target_rule_id,element_name,rule_condition,rule_keyword) values(?,?,?,?,?)";
		foreach ($success_rule_conditions as $data) {
			$target_rule_id  = substr($data['target_rule_id'],7); //remove 'success' prefix
			$element_name	 = strtolower(trim($data['element_name']));
			$rule_condition  = strtolower(trim($data['condition']));
			$rule_keyword	 = trim($data['keyword']);

			$params = array($form_id,$target_rule_id,$element_name,$rule_condition,$rule_keyword);
			mf_do_query($query,$params,$dbh);
		}
	}

	//save logic statuses into ap_forms table
	$form_input = array();
	$form_input['logic_field_enable']   = $logic_field_enable;
	$form_input['logic_page_enable']    = $logic_page_enable;
	$form_input['logic_email_enable']   = $logic_email_enable;
	$form_input['logic_webhook_enable'] = $logic_webhook_enable;
	$form_input['logic_success_enable'] = $logic_success_enable;
	
	if(empty($field_rule_properties)){
		$form_input['logic_field_enable'] = 0; //always disable logics when target fields are empty
	}

	if(empty($page_rule_properties)){
		$form_input['logic_page_enable'] = 0; //always disable logics when target pages are empty
	}

	if(empty($email_rule_properties)){
		$form_input['logic_email_enable'] = 0; //always disable logics when target emails are empty
	}

	if(empty($webhook_rule_properties)){
		$form_input['logic_webhook_enable'] = 0; //always disable logics when target hooks are empty
	}

	if(empty($success_rule_properties)){
		$form_input['logic_success_enable'] = 0; //always disable logics when target hooks are empty
	}

	if(!empty($logic_success_enable)){
		$form_input['form_redirect_enable'] = 0; //override default redirect. turn it off and use message as default
	}

	mf_ap_forms_update($form_id,$form_input,$dbh);

	$_SESSION['MF_SUCCESS'] = 'Logics has been saved.';
   	echo '{ "status" : "ok", "form_id" : "'.$form_id.'" }';
   
?>