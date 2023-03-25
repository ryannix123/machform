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

	require('includes/language.php');
	require('includes/entry-functions.php');
	require('includes/post-functions.php');
	require('includes/users-functions.php');
	require('lib/libsodium/autoload.php');
	
	$form_id  = (int) trim($_GET['form_id'] ?? 0);
	$entry_id = (int) trim($_GET['entry_id'] ?? 0);
	$nav 	  = isset($_GET['nav']) ? trim($_GET['nav']) : false;

	if(empty($form_id) || empty($entry_id)){
		die("Invalid Request");
	}

	$dbh = mf_connect_db();
	
	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);
	$mf_properties 	= mf_get_form_properties($dbh,$form_id,array('form_active','form_entry_edit_enable'));
	
	
	//check inactive form, inactive form settings should not displayed
	if(empty($mf_properties) || $mf_properties['form_active'] == null){
		$_SESSION['MF_DENIED'] = "This is not valid URL.";

		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
		exit;
	}else{
		$form_active = (int) $mf_properties['form_active'];
	
		if($form_active !== 0 && $form_active !== 1){
			$_SESSION['MF_DENIED'] = "This is not valid URL.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_entries or view_entries permission
		if(empty($user_perms['edit_entries']) && empty($user_perms['view_entries'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to access this page.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}

	if(!empty($_GET['clear_privatekey'])){
		unset($_SESSION['mf_encryption_private_key'][$form_id]);
	}

	//determine the 'incomplete' status of current entry
	$query = "select 
					`status` 
				from 
					`".MF_TABLE_PREFIX."form_{$form_id}` 
			where id=?";
	$params = array($entry_id);

	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$entry_status = $row['status'];

	if(empty($entry_status)){
		$_SESSION['MF_DENIED'] = "Entry #{$entry_id} does not exist.";

		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
		exit;
	}

	$is_incomplete_entry = false;
	if($entry_status == 2){
		$is_incomplete_entry = true;
	}


	//if there is "nav" parameter, we need to determine the correct entry id and override the existing entry_id
	if(!empty($nav)){

		$entries_options = array();
		$entries_options['is_incomplete_entry'] = $is_incomplete_entry;
		
		$all_entry_id_array = mf_get_filtered_entries_ids($dbh,$form_id,$entries_options);
		$entry_key = array_keys($all_entry_id_array,$entry_id);
		$entry_key = $entry_key[0];

		if($nav == 'prev'){
			$entry_key--;
		}else{
			$entry_key++;
		}

		$entry_id = $all_entry_id_array[$entry_key];

		//if there is no entry_id, fetch the first/last member of the array
		if(empty($entry_id)){
			if($nav == 'prev'){
				$entry_id = array_pop($all_entry_id_array);
			}else{
				$entry_id = $all_entry_id_array[0];
			}
		}
	}

	//get entry information (date created/updated/ip address/resume key)
	$query = "select 
					date_format(date_created,'%e %b %Y - %r') date_created,
					date_created date_created_raw,
					date_format(date_updated,'%e %b %Y - %r') date_updated,
					ip_address,
					resume_key,
					edit_key  
				from 
					`".MF_TABLE_PREFIX."form_{$form_id}` 
			where id=?";
	$params = array($entry_id);

	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$date_created = $row['date_created'];
	$date_created_raw = $row['date_created_raw'];

	$ip_address   = $row['ip_address'];
	$form_resume_key = $row['resume_key'];
	$form_edit_key 	 = $row['edit_key'];

	if(!empty($row['date_updated'])){
		$date_updated = $row['date_updated'];

		//get the total activity log number
		$query = "SELECT count(*) total_log from `".MF_TABLE_PREFIX."form_{$form_id}_log` where record_id=?";
		$params = array($entry_id);
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		$total_log = $row['total_log'];

		if(!empty($total_log)){
			$date_updated .= " <a id=\"view_entry_log_link\" href=\"view_entry_log.php?form_id={$form_id}&entry_id={$entry_id}\" class=\"blue_dotted\" <span class=\"icon-history\"></span> view activity log ({$total_log})</a>";
		}
	}else{
		$date_updated = '&nbsp;';
	}

		
	//get form name
	$query 	= "select 
					 form_name,
					 payment_enable_merchant,
					 payment_merchant_type,
					 payment_price_type,
					 payment_price_amount,
					 payment_currency,
					 payment_ask_billing,
					 payment_ask_shipping,
					 payment_enable_tax,
					 payment_tax_rate,
					 payment_enable_discount,
					 payment_discount_type,
					 payment_discount_amount,
					 payment_discount_element_id,
					 form_resume_enable,
					 form_approval_enable,
					 form_encryption_enable,
					 logic_field_enable,
					 form_page_total  
			     from 
			     	 ".MF_TABLE_PREFIX."forms 
			    where 
			    	 form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row)){
		$row['form_name'] = mf_trim_max_length($row['form_name'],65);
		
		$form_name = htmlspecialchars($row['form_name']);
		$payment_enable_merchant = (int) $row['payment_enable_merchant'];
		$form_encryption_enable = (int) $row['form_encryption_enable'];
		
		$payment_price_amount = (double) $row['payment_price_amount'];
		$payment_merchant_type = $row['payment_merchant_type'];
		$payment_price_type = $row['payment_price_type'];
		$form_payment_currency = strtoupper($row['payment_currency']);
		$payment_ask_billing = (int) $row['payment_ask_billing'];
		$payment_ask_shipping = (int) $row['payment_ask_shipping'];

		$payment_enable_tax = (int) $row['payment_enable_tax'];
		$payment_tax_rate 	= (float) $row['payment_tax_rate'];

		$payment_enable_discount = (int) $row['payment_enable_discount'];
		$payment_discount_type 	 = $row['payment_discount_type'];
		$payment_discount_amount = (float) $row['payment_discount_amount'];
		$payment_discount_element_id = (int) $row['payment_discount_element_id'];
		$form_resume_enable = (int) $row['form_resume_enable'];
		$form_approval_enable = (int) $row['form_approval_enable'];
		$logic_field_enable = (int) $row['logic_field_enable'];
		$form_page_total = $row['form_page_total'];
	}else{
		die("Error. Unknown form ID.");
	}

	//if "Allow Users to Edit Completed Submission" enabled
	//check the "edit_key" on the table. prefill it if empty
	if(!empty($mf_properties['form_entry_edit_enable']) && empty($form_edit_key)){
		$form_edit_key = strtolower(md5(uniqid(rand(), true))).strtolower(md5(time()));

		$query = "UPDATE `".MF_TABLE_PREFIX."form_{$form_id}` SET `edit_key`=? WHERE `id`=?";
		$params = array($form_edit_key,$entry_id);
		mf_do_query($query,$params,$dbh);
	}

	if($is_incomplete_entry && !empty($form_resume_key)){
		$form_resume_url = $mf_settings['base_url']."view.php?id={$form_id}&mf_resume={$form_resume_key}";
	}

	if(!empty($mf_properties['form_entry_edit_enable']) && !empty($form_edit_key) && empty($form_encryption_enable)){
		$form_edit_url = $mf_settings['base_url']."view.php?id={$form_id}&mf_edit={$form_edit_key}";
	}

	if(empty($user_perms['edit_entries']) && empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$form_edit_url = '';
	}

	$is_discount_applicable = false;

	//if the discount element for the current entry_id having any value, we can be certain that the discount code has been validated and applicable
	if(!empty($payment_enable_discount)){
		$query = "select element_{$payment_discount_element_id} coupon_element from ".MF_TABLE_PREFIX."form_{$form_id} where `id` = ? and `status` = 1";
		$params = array($entry_id);
			
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
			
		if(!empty($row['coupon_element'])){
			$is_discount_applicable = true;
		}
	}

	//if payment enabled, get the details
	if(!empty($payment_enable_merchant)){
		$query = "SELECT 
						`payment_id`,
						 date_format(payment_date,'%e %b %Y - %r') payment_date, 
						`payment_status`, 
						`payment_fullname`, 
						`payment_amount`, 
						`payment_currency`, 
						`payment_test_mode`,
						`status`, 
						`billing_street`, 
						`billing_city`, 
						`billing_state`, 
						`billing_zipcode`, 
						`billing_country`, 
						`same_shipping_address`, 
						`shipping_street`, 
						`shipping_city`, 
						`shipping_state`, 
						`shipping_zipcode`, 
						`shipping_country`
					FROM
						".MF_TABLE_PREFIX."form_payments
				   WHERE
				   		form_id = ? and record_id = ? and `status` = 1
				ORDER BY
						payment_date DESC
				   LIMIT 1";
		$params = array($form_id,$entry_id);

		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		if(!empty($row)){
			$payment_id 		= $row['payment_id'];
			$payment_date 		= $row['payment_date'];
			$payment_status 	= $row['payment_status'];
			$payment_fullname 	= $row['payment_fullname'];
			$payment_amount 	= (double) $row['payment_amount'];
			$payment_currency 	= strtoupper($row['payment_currency']);
			$payment_test_mode 	= (int) $row['payment_test_mode'];
			$billing_street 	= htmlspecialchars(trim($row['billing_street']));
			$billing_city 		= htmlspecialchars(trim($row['billing_city']));
			$billing_state 		= htmlspecialchars(trim($row['billing_state']));
			$billing_zipcode 	= htmlspecialchars(trim($row['billing_zipcode']));
			$billing_country 	= htmlspecialchars(trim($row['billing_country']));
			
			$same_shipping_address = (int) $row['same_shipping_address'];

			if(!empty($same_shipping_address)){
				$shipping_street 	= $billing_street;
				$shipping_city		= $billing_city;
				$shipping_state		= $billing_state;
				$shipping_zipcode	= $billing_zipcode;
				$shipping_country	= $billing_country;
			}else{
				$shipping_street 	= htmlspecialchars(trim($row['shipping_street']));
				$shipping_city 		= htmlspecialchars(trim($row['shipping_city']));
				$shipping_state 	= htmlspecialchars(trim($row['shipping_state']));
				$shipping_zipcode 	= htmlspecialchars(trim($row['shipping_zipcode']));
				$shipping_country 	= htmlspecialchars(trim($row['shipping_country']));
			}
		}

		if(!empty($billing_street) || !empty($billing_city) || !empty($billing_state) || !empty($billing_zipcode) || !empty($billing_country)){
			$billing_address  = "{$billing_street}<br />{$billing_city}, {$billing_state} {$billing_zipcode}<br />{$billing_country}";
		}
		
		if(!empty($shipping_street) || !empty($shipping_city) || !empty($shipping_state) || !empty($shipping_zipcode) || !empty($shipping_country)){
			$shipping_address = "{$shipping_street}<br />{$shipping_city}, {$shipping_state} {$shipping_zipcode}<br />{$shipping_country}";
		}

		if(!empty($row)){
			$payment_has_record = true;

			if(empty($payment_id)){
				//if the payment has record but has no payment id, then the record was being inserted manually (the payment status was being set manually by user)
				//in this case, we consider this record empty
				$payment_has_record = false;
			}
		}else{
			//if the entry doesn't have any record within ap_form_payments table
			//we need to calculate the total amount
			$payment_has_record = false;
			$payment_status = "unpaid";
			
			if($payment_price_type == 'variable'){
				$payment_amount = (double) mf_get_payment_total($dbh,$form_id,$entry_id,0,'live');
			}else if($payment_price_type == 'fixed'){
				$payment_amount = $payment_price_amount;
			}

			//calculate discount if applicable
			if($is_discount_applicable){
				$payment_calculated_discount = 0;

				if($payment_discount_type == 'percent_off'){
					//the discount is percentage
					$payment_calculated_discount = ($payment_discount_amount / 100) * $payment_amount;
					$payment_calculated_discount = round($payment_calculated_discount,2); //round to 2 digits decimal
				}else{
					//the discount is fixed amount
					$payment_calculated_discount = round($payment_discount_amount,2); //round to 2 digits decimal
				}

				$payment_amount -= $payment_calculated_discount;
			}

			//calculate tax if enabled
			if(!empty($payment_enable_tax) && !empty($payment_tax_rate)){
				$payment_tax_amount = ($payment_tax_rate / 100) * $payment_amount;
				$payment_tax_amount = round($payment_tax_amount,2); //round to 2 digits decimal
				$payment_amount += $payment_tax_amount;
			}

			$payment_currency = $form_payment_currency;
		}

		//ensure two decimals are being used
		$payment_amount = number_format($payment_amount,2);

		//build payment resume URL if the status is unpaid
		if($payment_status == 'unpaid'){
			if($payment_merchant_type == 'paypal_standard'){
				$payment_resume_url = mf_get_merchant_redirect_url($dbh,$form_id,$entry_id);
			}else if(in_array($payment_merchant_type, array('stripe','paypal_rest','authorizenet','braintree'))){
				$payment_resume_token = base64_encode($entry_id.'-'.md5($date_created_raw));	
				$payment_resume_url   = "payment.php?id={$form_id}&pay_token={$payment_resume_token}";
			}
		}

		switch ($payment_currency) {
			case 'USD' : $currency_symbol = '&#36;';break;
			case 'EUR' : $currency_symbol = '&#8364;';break;
			case 'GBP' : $currency_symbol = '&#163;';break;
			case 'AUD' : $currency_symbol = '&#36;';break;
			case 'CAD' : $currency_symbol = '&#36;';break;
			case 'JPY' : $currency_symbol = '&#165;';break;
			case 'THB' : $currency_symbol = '&#3647;';break;
			case 'HUF' : $currency_symbol = '&#70;&#116;';break;
			case 'CHF' : $currency_symbol = 'CHF';break;
			case 'CZK' : $currency_symbol = '&#75;&#269;';break;
			case 'SEK' : $currency_symbol = 'kr';break;
			case 'DKK' : $currency_symbol = 'kr';break;
			case 'PHP' : $currency_symbol = '&#36;';break;
			case 'IDR' : $currency_symbol = 'Rp';break;
			case 'INR' : $currency_symbol = 'Rs';break;
			case 'MYR' : $currency_symbol = 'RM';break;
			case 'PLN' : $currency_symbol = '&#122;&#322;';break;
			case 'BRL' : $currency_symbol = 'R&#36;';break;
			case 'HKD' : $currency_symbol = '&#36;';break;
			case 'MXN' : $currency_symbol = 'Mex&#36;';break;
			case 'TWD' : $currency_symbol = 'NT&#36;';break;
			case 'TRY' : $currency_symbol = 'TL';break;
			case 'NZD' : $currency_symbol = '&#36;';break;
			case 'SGD' : $currency_symbol = '&#36;';break;
			default: $currency_symbol = ''; break;
		}
	}

		
	//get entry details for particular entry_id
	$param['checkbox_image'] = 'images/icons/59_blue_16.png';
	$param['show_image_preview'] = true;
	
	if(!empty($_SESSION['mf_encryption_private_key'][$form_id])){
		$param['encryption_private_key'] = $_SESSION['mf_encryption_private_key'][$form_id];
	}
	$param['hide_encrypted_data'] = 'substring';
	
	$entry_details = mf_get_entry_details($dbh,$form_id,$entry_id,$param);

	//get the list of the custom email logic template, if any
	$query = "select 
					rule_id	 
				from 
					".MF_TABLE_PREFIX."email_logic 
			   where 
			   		form_id=? and 
			   		template_name='custom' 
			order by 
					rule_id asc";
	$params = array($form_id);

	$sth = mf_do_query($query,$params,$dbh);

	$custom_logic_email_template_array = array();
	while($row = mf_do_fetch_result($sth)){
		$custom_logic_email_template_array[] = $row['rule_id'];
	}

	//if logic is enable, get hidden elements
	//we'll need it to hide section break and media
	if($logic_field_enable){
		$entry_values = mf_get_entry_values($dbh,$form_id,$entry_id,false);
		foreach ($entry_values as $element_name => $value) {
			$input_data[$element_name] = $value['default_value'];
		}

		$hidden_elements = array();
		$hidden_elements_options = array();

		$hidden_elements_options['use_main_table'] = true;
		$hidden_elements_options['entry_id'] = $entry_id;

		for($x=1;$x<=$form_page_total;$x++){
			$current_page_hidden_elements = array();
			$current_page_hidden_elements = mf_get_hidden_elements($dbh,$form_id,$x,$input_data,$hidden_elements_options);
			
			$hidden_elements += $current_page_hidden_elements; //use '+' so that the index won't get lost
		}
	}

	$header_data =<<<EOT
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css{$mf_version_tag}" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="css/entry_print.css{$mf_version_tag}" media="print">
EOT;

	//if approval enabled, we need to display the approval related data
	if(!empty($form_approval_enable)){
		//get approval settings
		$query 	= "select 
					 workflow_type,
					 parallel_workflow   
			     from 
			     	 ".MF_TABLE_PREFIX."approval_settings 
			    where 
			    	 form_id = ?";
		$params = array($form_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		if(!empty($row)){
			$approval_workflow_type		= $row['workflow_type'];
			$approval_parallel_workflow	= $row['parallel_workflow'];
		}

		//get current record final approval status and queue
		$query = "SELECT approval_status,approval_queue_user_id FROM ".MF_TABLE_PREFIX."form_{$form_id} WHERE `id` = ?";
		$params = array($entry_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		$entry_final_approval_status = $row['approval_status']; 
		$approval_queue_user_id_array = explode("|",$row['approval_queue_user_id']); 

		//get approvers and approval signature info
		$query = "SELECT 
						A.rule_all_any,
						A.user_id,
						A.user_position,
						B.user_fullname,
						B.user_email,
						B.last_login_date,
						C.date_created date_signed,
						C.ip_address,
						C.approval_state,
						C.approval_note 
					FROM 
					  	(`".MF_TABLE_PREFIX."approvers` A
			   LEFT JOIN
			   			`".MF_TABLE_PREFIX."users` B
			   		  ON
			   		  	A.user_id = B.user_id)
			   LEFT JOIN
			   			`".MF_TABLE_PREFIX."form_{$form_id}_approvals` C
			   		  ON
			   		  	A.user_id = C.user_id AND C.record_id = ?
				   WHERE
					    form_id = ?
			    ORDER BY
					    user_position ASC";
		$params = array($entry_id,$form_id);
		
		$sth = mf_do_query($query,$params,$dbh);

		$approvers_list_array = array();
		$i=0;
		while($row = mf_do_fetch_result($sth)){
			//if this entry already has final approval status, skip users with empty approval state
			if(($entry_final_approval_status == 'approved' || $entry_final_approval_status == 'denied') && empty($row['approval_state'])){
				continue;
			}
			
			$approvers_list_array[$i]['rule_all_any']  	 = $row['rule_all_any'];
			$approvers_list_array[$i]['user_id'] 	   	 = $row['user_id'];
			$approvers_list_array[$i]['user_position'] 	 = $i + 1;
			$approvers_list_array[$i]['user_fullname'] 	 = htmlspecialchars($row['user_fullname'] ?? '');
			$approvers_list_array[$i]['user_email'] 	 = $row['user_email'];
			$approvers_list_array[$i]['last_login_date'] = $row['last_login_date'];
			$approvers_list_array[$i]['last_login_date'] = $row['last_login_date'];
			$approvers_list_array[$i]['date_signed'] = $row['date_signed'];
			$approvers_list_array[$i]['ip_address'] 	 = $row['ip_address'];
			$approvers_list_array[$i]['approval_state']  = $row['approval_state'];
			$approvers_list_array[$i]['approval_note'] 	 = htmlspecialchars($row['approval_note'] ?? '');
			$i++;
		}

		//check if the approver has conditions or not
		$query = "SELECT count(*) total_condition FROM ".MF_TABLE_PREFIX."approvers_conditions WHERE form_id = ?";
		$params = array($form_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		if(!empty($row['total_condition'])){
			$approval_has_condition = true;
		}else{
			$approval_has_condition = false;
		}

		
	}

	//get recent emails array (10 emails max, display 2 by default)
	//get unique emails from:
	//1. recent email session (from email_entry.php)
	//2. current logged-in user email
	//3. form notification email
	//4. auto reply email
	//5. logic emails
	$recent_emails = array();

	if(!empty($_SESSION['mf_email_entry_recent_email'])){
		$recent_emails[] = $_SESSION['mf_email_entry_recent_email'];
	}

	$template_data = mf_get_template_variables($dbh,$form_id,$entry_id);
		
	$template_variables = $template_data['variables'];
	$template_values    = $template_data['values'];

	$query = "SELECT 
					user_email
				FROM 
					".MF_TABLE_PREFIX."users 
			   WHERE 
			   		user_id=? and `status`=1";
	$params = array($_SESSION['mf_user_id']);
			
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$recent_emails[] = $row['user_email'];

	$query = "SELECT 
					form_email,
					esr_email_address
				FROM 
					".MF_TABLE_PREFIX."forms 
			   WHERE 
			   		form_id=?";
	$params = array($form_id);
			
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	if(!empty($row['form_email'])){
		$form_email = $row['form_email'];
		
		$form_email = str_replace('&nbsp;','',str_replace($template_variables,$template_values,$form_email));
    	$form_email = html_entity_decode($form_email,ENT_QUOTES);

		$form_email_array = explode(",",$form_email);
		array_walk($form_email_array, 'mf_trim_value');
		$recent_emails = array_merge($recent_emails,$form_email_array);
	}

	if(!empty($row['esr_email_address'])){
		$esr_email_address = "{element_".$row['esr_email_address']."}";
		
		$esr_email_address = str_replace('&nbsp;','',str_replace($template_variables,$template_values,$esr_email_address));
    	$esr_email_address = html_entity_decode($esr_email_address,ENT_QUOTES);

		$recent_emails[] = $esr_email_address;
	}
	
	$query = "SELECT 
					target_email
				FROM 
					".MF_TABLE_PREFIX."email_logic 
			   WHERE 
			   		form_id=?";
	$params = array($form_id);
			
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	if(!empty($row['target_email'])){
		$logic_emails = $row['target_email'];
		
		$logic_emails = str_replace('&nbsp;','',str_replace($template_variables,$template_values,$logic_emails));
    	$logic_emails = html_entity_decode($logic_emails,ENT_QUOTES);

		$logic_emails_array = explode(",",$logic_emails);
		array_walk($logic_emails_array, 'mf_trim_value');
		$recent_emails = array_merge($recent_emails,$logic_emails_array);
	}

	//make sure recent emails are unique and then re-key the index
	$recent_emails = array_unique($recent_emails);
	$recent_emails = array_values($recent_emails);
	//--- end getting recent emails

//print_r($recent_emails);
	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post view_entry">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<?php if($is_incomplete_entry){ ?>
								<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <?php echo "<a id=\"ve_a_entries\" class=\"breadcrumb\" href='manage_entries.php?id={$form_id}'>Entries</a>"; ?> <span id="ve_a_next" class="icon-arrow-right2 breadcrumb_arrow"></span> <?php echo "<a id=\"ve_a_entries\" class=\"breadcrumb\" href='manage_incomplete_entries.php?id={$form_id}'>Incomplete</a>"; ?> <span id="ve_a_next" class="icon-arrow-right2 breadcrumb_arrow"></span> #<?php echo $entry_id; ?></h2>
								<p>Displaying incomplete entry #<?php echo $entry_id; ?></p>
							<?php }else{ ?>
								<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <?php echo "<a id=\"ve_a_entries\" class=\"breadcrumb\" href='manage_entries.php?id={$form_id}'>Entries</a>"; ?> <span id="ve_a_next" class="icon-arrow-right2 breadcrumb_arrow"></span> #<?php echo $entry_id; ?></h2>
								<p>Displaying entry #<?php echo $entry_id; ?></p>
							<?php } ?>

						</div>

						<?php if(empty($_SESSION['mf_encryption_private_key'][$form_id]) && !empty($form_encryption_enable) && !$is_incomplete_entry){ ?>
						<div style="float: right;margin-right: 5px">
								<a href="#" id="button_decrypt_entry_data" class="bb_button bb_small bb_green">
									<span class="icon-key" style="margin-right: 5px"></span>View Encrypted Data
								</a>
						</div>
						<?php } ?>	

						<?php if(!empty($_SESSION['mf_encryption_private_key'][$form_id]) && !empty($form_encryption_enable) && !$is_incomplete_entry){ ?>
						<div style="float: right;margin-right: 5px;margin-top:20px">
								<span style="font-style: italic;font-size: 95%">Private key temporarily cached.</span> <a href="view_entry.php?form_id=<?php echo $form_id; ?>&entry_id=<?php echo $entry_id; ?>&clear_privatekey=1" style="font-weight: bold" class="blue_dotted">-clear cache-</a>
						</div>
						<?php } ?>	
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>

				<div class="content_body">

					<div id="ve_details" data-csrftoken="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>" data-formid="<?php echo $form_id; ?>" data-entryid="<?php echo $entry_id; ?>" data-incomplete="<?php if($is_incomplete_entry){ echo '1';}else{ echo '0';} ?>">
						
						<?php if(!empty($form_approval_enable)){ ?>
						<div id="ve_approval_info">
							<h6><?php if($entry_final_approval_status == 'pending'){ echo 'Approval Progress'; }else{ echo 'Approval History'; } ?></h6>
							<div id="ve_approval_signatures">
								<ul id="approval_history">
									<?php
										//the approval signature history is being displayed differently based on the approval types and setting
										//1) If workflow type is parallel, display all the approvers, without arrows
										//1) If workflow type is parallel and approval has condition, display only the approvers filtered by the conditions, without arrows
										//2) If workflow type is serial and approval has condition, display only the approvers already signed, with arrows
										//3) If workflow type is serial and approval does not have any condition, display all the approvers, with arrows 
										
										if($approval_workflow_type == 'parallel'){
											//1) If workflow type is parallel
											foreach ($approvers_list_array as $value) {
												
												$signature_icon_class = '';
												$ahb_approval_class = '';
												
												if($value['approval_state'] == 'approved'){
													$signature_icon_class = 'icon-checkmark4 approved-icon';
													$ahb_approval_class   = 'ahb_approved';
													$approval_date_status = 'Approved';
												}else if($value['approval_state'] == 'denied'){
													$signature_icon_class = 'icon-cross2 denied-icon';
													$ahb_approval_class   = 'ahb_denied';
													$approval_date_status = 'Denied';
												}else{
													
													//if this approval has any logic condition, only the selected users should be displayed
													if($approval_has_condition){
														//if this user is not in queue, skip and continue
														if(!in_array($value['user_id'], $approval_queue_user_id_array)){
															continue;
														}
													}

													$signature_icon_class = 'icon-alarm pending-icon';
													$ahb_approval_class   = 'ahb_pending';
													$approval_date_status = 'Notified';
												}

												$approval_note_markup = '<em class="ahb_pending_approval">----</em>';
												if(!empty($value['approval_note'])){
													$approval_note_markup = "<p>".
																"<strong style=\"font-size: 150%\">&ldquo;</strong>".
																"{$value['approval_note']}".	
															"</p>";
												}

												if(empty($value['date_signed'])){
													$value['date_signed'] = $date_created_raw;
												}
												
												$approval_date_markup = '';
												$approval_date = mf_relative_date($value['date_signed']);
												if(strpos($approval_date,'ago') !== false){
													$approval_date_markup = "<h6>{$approval_date_status} <strong title=\"{$value['date_signed']}\">{$approval_date}</strong></h6>";
												}else{
													$approval_date_markup = "<h6>{$approval_date_status} on <strong title=\"{$value['date_signed']}\">{$approval_date}</strong></h6>";
												}
												

												$approval_history_markup = 
													"<li class=\"approval_history_box\">".
														"<div class=\"ahb_leftbox {$ahb_approval_class}\">".
															"<span class=\"{$signature_icon_class}\"></span>".
														"</div>".
														"<div class=\"ahb_rightbox\">".
															"<strong class=\"ahb_approver_no\">{$value['user_position']}</strong>".
															"<h3>{$value['user_fullname']}</h3>".
															$approval_note_markup.
															$approval_date_markup.
														"</div>".	
													"</li>".
													"<li class=\"approval_history_spacer\">&nbsp;</li>";
												echo $approval_history_markup;
											}
										}else if($approval_workflow_type == 'serial'){
											if($approval_has_condition === true){
												//2) If workflow type is serial and approval has condition, display only the approvers already signed, with arrows
												$total_approvers_count 	  = count($approvers_list_array);
												$current_approver_counter = 0;

												$is_pending_history_displayed = false;
												foreach ($approvers_list_array as $value) {
													
													if($is_pending_history_displayed){
														break;
													}

													$signature_icon_class = '';
													$ahb_approval_class = '';
													
													//override user position with simple counter when logic exist, as it's no longer valid
													$value['user_position'] = $current_approver_counter + 1; 
													
													if($value['approval_state'] == 'approved'){
														$signature_icon_class = 'icon-checkmark4 approved-icon';
														$ahb_approval_class   = 'ahb_approved';
														$approval_date_status = 'Approved';
													}else if($value['approval_state'] == 'denied'){
														$signature_icon_class = 'icon-cross2 denied-icon';
														$ahb_approval_class   = 'ahb_denied';
														$approval_date_status = 'Denied';
													}else{
														//if this user is not in queue, skip and continue
														if(!in_array($value['user_id'], $approval_queue_user_id_array)){
															continue;
														}

														$signature_icon_class = 'icon-alarm pending-icon';
														$ahb_approval_class   = 'ahb_pending';
														$approval_date_status = 'Notified';

														$is_pending_history_displayed = true;

														//if there is final approval status already, then don't display any pending status
														if($entry_final_approval_status == 'approved' || $entry_final_approval_status == 'denied'){
															break;
														}
													}

													$approval_note_markup = '<em class="ahb_pending_approval">----</em>';
													
													if(!empty($value['approval_note'])){
														$approval_note_markup = "<p>".
																	"<strong style=\"font-size: 150%\">&ldquo;</strong>".
																	"{$value['approval_note']}".	
																"</p>";
													}
													
													$approval_date_markup = '';
													
													if(!empty($value['date_signed'])){
														$approval_date = mf_relative_date($value['date_signed']);
														if(strpos($approval_date,'ago') !== false){
															$approval_date_markup = "<h6>{$approval_date_status} <strong title=\"{$value['date_signed']}\">{$approval_date}</strong></h6>";
														}else{
															$approval_date_markup = "<h6>{$approval_date_status} on <strong title=\"{$value['date_signed']}\">{$approval_date}</strong></h6>";
														}
														
														if($value['approval_state'] == 'approved'){
															$last_date_approved = $value['date_signed'];
														}else{
															$last_date_approved = '';
														}
													}else{
														//if this is the second or more of the approvers
														if(!empty($last_date_approved)){
															$notified_date = mf_relative_date($last_date_approved);

															if(strpos($notified_date,'ago') !== false){
																$approval_date_markup = "<h6>{$approval_date_status} <strong title=\"{$last_date_signed}\">{$notified_date}</strong></h6>";
															}else{
																$approval_date_markup = "<h6>{$approval_date_status} on <strong title=\"{$last_date_signed}\">{$notified_date}</strong></h6>";
															}
															$last_date_approved = '';

															$approval_note_markup = '<em class="ahb_pending_approval">--pending approval--</em>';
														}else{
															//otherwise if this is the first approver and no respond yet
															if($current_approver_counter == 0 && empty($last_date_approved)){
																$notified_date = mf_relative_date($date_created_raw);

																if(strpos($notified_date,'ago') !== false){
																	$approval_date_markup = "<h6>{$approval_date_status} <strong title=\"{$date_created_raw}\">{$notified_date}</strong></h6>";
																}else{
																	$approval_date_markup = "<h6>{$approval_date_status} on <strong title=\"{$date_created_raw}\">{$notified_date}</strong></h6>";
																}
																
																$approval_note_markup = '<em class="ahb_pending_approval">--pending approval--</em>';
															}
														}
													}
													
													$current_approver_counter++;
													if($current_approver_counter > 1){
														echo '<li class="approval_history_spacer"><span class="icon-arrow-down11 spacer-icon"></span></li>';
													}

													$approval_history_markup = 
														"<li class=\"approval_history_box\">".
															"<div class=\"ahb_leftbox {$ahb_approval_class}\">".
																"<span class=\"{$signature_icon_class}\"></span>".
															"</div>".
															"<div class=\"ahb_rightbox\">".
																"<strong class=\"ahb_approver_no\">{$value['user_position']}</strong>".
																"<h3>{$value['user_fullname']}</h3>".
																$approval_note_markup.
																$approval_date_markup.
															"</div>".	
														"</li>";
													echo $approval_history_markup;
													
												}
											}elseif($approval_has_condition === false){
												//3) If workflow type is serial and approval does not have any condition, display all the approvers, with arrows 
												$total_approvers_count 	  = count($approvers_list_array);
												$current_approver_counter = 0;

												foreach ($approvers_list_array as $value) {
												
													$signature_icon_class = '';
													$ahb_approval_class = '';
													
													if($value['approval_state'] == 'approved'){
														$signature_icon_class = 'icon-checkmark4 approved-icon';
														$ahb_approval_class   = 'ahb_approved';
														$approval_date_status = 'Approved';
													}else if($value['approval_state'] == 'denied'){
														$signature_icon_class = 'icon-cross2 denied-icon';
														$ahb_approval_class   = 'ahb_denied';
														$approval_date_status = 'Denied';
													}else{
														$signature_icon_class = 'icon-alarm pending-icon';
														$ahb_approval_class   = 'ahb_pending';
														$approval_date_status = 'Notified';
													}

													$approval_note_markup = '<em class="ahb_pending_approval">----</em>';
													
													if(!empty($value['approval_note'])){
														$approval_note_markup = "<p>".
																	"<strong style=\"font-size: 150%\">&ldquo;</strong>".
																	"{$value['approval_note']}".	
																"</p>";
													}
													
													$approval_date_markup = '';
													
													if(!empty($value['date_signed'])){
														$approval_date = mf_relative_date($value['date_signed']);
														if(strpos($approval_date,'ago') !== false){
															$approval_date_markup = "<h6>{$approval_date_status} <strong title=\"{$value['date_signed']}\">{$approval_date}</strong></h6>";
														}else{
															$approval_date_markup = "<h6>{$approval_date_status} on <strong title=\"{$value['date_signed']}\">{$approval_date}</strong></h6>";
														}
														
														if($value['approval_state'] == 'approved'){
															$last_date_approved = $value['date_signed'];
														}else{
															$last_date_approved = '';
														}
													}else{
														//if this is the second or more of the approvers
														if(!empty($last_date_approved)){
															$notified_date = mf_relative_date($last_date_approved);

															if(strpos($notified_date,'ago') !== false){
																$approval_date_markup = "<h6>{$approval_date_status} <strong title=\"{$last_date_signed}\">{$notified_date}</strong></h6>";
															}else{
																$approval_date_markup = "<h6>{$approval_date_status} on <strong title=\"{$last_date_signed}\">{$notified_date}</strong></h6>";
															}
															$last_date_approved = '';

															$approval_note_markup = '<em class="ahb_pending_approval">--pending approval--</em>';
														}else{
															//otherwise if this is the first approver and no respond yet
															if($current_approver_counter == 0 && empty($last_date_approved)){
																$notified_date = mf_relative_date($date_created_raw);

																if(strpos($notified_date,'ago') !== false){
																	$approval_date_markup = "<h6>{$approval_date_status} <strong title=\"{$date_created_raw}\">{$notified_date}</strong></h6>";
																}else{
																	$approval_date_markup = "<h6>{$approval_date_status} on <strong title=\"{$date_created_raw}\">{$notified_date}</strong></h6>";
																}
																
																$approval_note_markup = '<em class="ahb_pending_approval">--pending approval--</em>';
															}
														}
													}
													

													$approval_history_markup = 
														"<li class=\"approval_history_box\">".
															"<div class=\"ahb_leftbox {$ahb_approval_class}\">".
																"<span class=\"{$signature_icon_class}\"></span>".
															"</div>".
															"<div class=\"ahb_rightbox\">".
																"<strong class=\"ahb_approver_no\">{$value['user_position']}</strong>".
																"<h3>{$value['user_fullname']}</h3>".
																$approval_note_markup.
																$approval_date_markup.
															"</div>".	
														"</li>";
													echo $approval_history_markup;

													$current_approver_counter++;
													if($current_approver_counter < $total_approvers_count){
														echo '<li class="approval_history_spacer"><span class="icon-arrow-down11 spacer-icon"></span></li>';
													}
												}
											}
										}
									?>								
								<ul>
							</div>
							
							<div id="ve_approval_final_status"> 
								<?php
									if($entry_final_approval_status == 'approved'){
										echo 'Final Approval Status: <strong class="final_approved">APPROVED <span class="icon-checkmark4 approved-icon"></span></strong>';
									}else if($entry_final_approval_status == 'denied'){
										echo 'Final Approval Status: <strong class="final_denied">DENIED</strong>';
									}else{
										echo 'Final Approval Status: <strong class="final_inprogress">-Pending Approval-</strong>';
									}
								?>
							</div>

							<?php
								if(!empty($entry_final_approval_status)){ 
									if($entry_final_approval_status == 'pending'){ 
										if(in_array($_SESSION['mf_user_id'], $approval_queue_user_id_array)){
							?>			
											<div id="ve_approval_action">
												<label class="description" for="ve-approval-add-note">Add Note (optional)</label>
												<textarea class="element textarea medium" name="ve-approval-add-note" id="ve-approval-add-note" style="width: 250px; height: 100px"></textarea>
												<div style="padding: 15px 0 15px 0">
													<a href="#" id="button_approval_approve" class="bb_button bb_small bb_green">
														<span class="icon-checkmark-circle" style="margin-right: 5px"></span>Approve
													</a> 
													<a style="margin-left: 5px" href="#" id="button_approval_deny" class="bb_button bb_small bb_red">
														<span class="icon-cancel-circle" style="margin-right: 5px"></span>Deny
													</a>
												</div>
											</div>
							<?php 		} 
									} 			
								} 
							?>
						</div>
						<?php } ?>

						<table id="ve_detail_table" width="100%" border="0" cellspacing="0" cellpadding="0">
						  <tbody>

							<?php 
									$toggle = false;
									
									foreach ($entry_details as $data){ 
										if($data['label'] == 'mf_page_break' && $data['value'] == 'mf_page_break'){
											continue;
										}

										//if this is section break/media and hidden due to logic, don't display it
										if(in_array($data['element_type'], array('section','media')) && !empty($hidden_elements) && !empty($hidden_elements[$data['element_id']]) ){
											continue;
										}

										if($toggle){
											$toggle = false;
											$row_style = 'class="alt"';
										}else{
											$toggle = true;
											$row_style = '';
										}

										$row_markup = '';
										$element_id = $data['element_id'] ?? 0;

										if($data['element_type'] == 'section' || $data['element_type'] == 'textarea') {
											if(!empty($data['label']) && !empty($data['value']) && ($data['value'] != '&nbsp;')){
												$section_separator = '<br/>';
											}else{
												$section_separator = '';
											}

											$section_break_content = '<span class="mf_section_title"><strong>'.nl2br($data['label']).'</strong></span>'.$section_separator.'<span class="mf_section_content">'.nl2br($data['value']).'</span>';

											$row_markup .= "<tr {$row_style}>\n";
											$row_markup .= "<td width=\"100%\" colspan=\"2\">{$section_break_content}</td>\n";
											$row_markup .= "</tr>\n";
										}else if($data['element_type'] == 'media'){
											if(!empty($data['label']) && !empty($data['value']) && ($data['value'] != '&nbsp;')){
												$section_separator = '<br/>';
											}else{
												$section_separator = '';
											}

											$section_break_content = '<span class="mf_section_title"><strong>'.nl2br($data['label']).'</strong></span>'.$section_separator.'<span class="mf_section_content">'.nl2br($data['value']).'</span>';

											$row_markup .= "<tr {$row_style}>\n";
											$row_markup .= "<td width=\"100%\" colspan=\"2\">{$section_break_content}</td>\n";
											$row_markup .= "</tr>\n";
										}else if($data['element_type'] == 'signature') {
											//there are 3 possibilities of signature type:
											//1. the old version, using json format, enclosed with [{ ... }]
											//2. data url format, starting with data:image/png
											//3. simple plain text, need to be rendered to image first

											if(substr($data['value'], 0,14) == 'data:image/png'){
												$signature_type = 'image';
											}else if(substr($data['value'], 0,2) == '[{'){
												$signature_type = 'json';
											}else{
												$signature_type = 'text';
											}

											if($signature_type == 'image'){
												if($data['element_size'] == 'small'){
													$signature_height = 100;
												}else if($data['element_size'] == 'medium'){
													$signature_height = 150;
												}else if($data['element_size'] == 'large'){
													$signature_height = 300;
												}
											}else if($signature_type == 'json'){
												//the height from the older json signature is smaller
												if($data['element_size'] == 'small'){
													$signature_height = 70;
												}else if($data['element_size'] == 'medium'){
													$signature_height = 130;
												}else if($data['element_size'] == 'large'){
													$signature_height = 260;
												}
											}else{
												//the plain text signature only has single height, for all sizes of signatures
												$signature_height = 75;
											}
											
											$element_id = $data['element_id'];
											$signature_hash = md5($data['value']);

											//encode the long query string for more readibility
											$q_string = base64_encode("form_id={$form_id}&id={$entry_id}&el=element_{$element_id}&hash={$signature_hash}");

											$signature_markup = "<img src=\"signature_img.php?q={$q_string}\" height=\"{$signature_height}\" title=\"Signature Image\" />";
								
											$row_markup .= "<tr>\n";
											$row_markup .= "<td width=\"40%\" style=\"vertical-align: top\"><strong>{$data['label']}</strong></td>\n";
											$row_markup .= "<td width=\"60%\">{$signature_markup}</td>\n";
											$row_markup .= "</tr>\n";
										}else if($data['element_type'] == 'rating') {
											$row_markup .= "<tr {$row_style}>\n";
											$row_markup .= "<td width=\"40%\"><strong>{$data['label']}</strong></td>\n";
											$row_markup .= "<td width=\"60%\" style=\"font-size: 120%\">".mf_numeric_to_rating($data['value'],$data['rating_max'],$data['rating_style'])."</td>\n";
											$row_markup .= "</tr>\n";
										}else{
											$row_markup .= "<tr {$row_style}>\n";
											$row_markup .= "<td width=\"40%\"><strong>{$data['label']}</strong></td>\n";
											$row_markup .= "<td width=\"60%\">".nl2br($data['value'])."</td>\n";
											$row_markup .= "</tr>\n";
										}

										echo $row_markup;
									} 
							?>  	
						  
						  </tbody>
						</table>
						
						<?php if(!empty($payment_enable_merchant)){ ?>
						<table width="100%" cellspacing="0" cellpadding="0" border="0" id="ve_payment_info">
							<tbody>		
								<tr>
							  	    <td class="payment_details_header">
							  	    	<span class="icon-info"></span>Payment Details</td>
							  		<td>&nbsp; </td>
							  	</tr> 
									
								<tr class="alt">
							  	    <td width="40%" class="payment_label"><strong>Amount</strong></td>
							  		<td width="60%"><?php echo $currency_symbol.$payment_amount.' '.$payment_currency; ?></td>
							  	</tr>  	
							  	<tr>
							  	    <td class="payment_label"><strong>Status</strong></td>
							  		<td class="payment_status_row">
							  			<span id="payment_status_static">	
								  			<span class="payment_status <?php echo $payment_status; ?>"><?php echo strtoupper($payment_status); ?></span> 
											<?php if(!empty($payment_test_mode)){ ?>
												<em style="margin-left: 5px">(TEST mode)</em>
											<?php } ?>
											<a href="#" class="blue_dotted status_changer" id="payment_status_change_link">change status</a>
										</span>
										<span id="payment_status_form" style="display: none">
											<select name="payment_status_dropdown" id="payment_status_dropdown" class="element select small"> 
												<option <?php if($payment_status == 'paid'){ echo 'selected="selected"'; } ?> value="paid">Paid</option>
												<option <?php if($payment_status == 'unpaid'){ echo 'selected="selected"'; } ?> value="unpaid">Unpaid</option>
												<option <?php if($payment_status == 'pending'){ echo 'selected="selected"'; } ?> value="pending">Pending</option>
												<option <?php if($payment_status == 'declined'){ echo 'selected="selected"'; } ?> value="declined">Declined</option>
												<option <?php if($payment_status == 'refunded'){ echo 'selected="selected"'; } ?> value="refunded">Refunded</option>
												<option <?php if($payment_status == 'cancelled'){ echo 'selected="selected"'; } ?> value="cancelled">Cancelled</option>	
											</select>
											<span id="payment_status_save_cancel"><a href="#" class="blue_dotted" id="payment_status_save_link" style="margin-left: 10px">save</a> or <a href="#" class="blue_dotted" id="payment_status_cancel_link">cancel</a></span>
											<span id="payment_status_loader" style="display: none"><em>saving...</em> <img align="absmiddle" src='images/loader_small_grey.gif' /></span>
										</span>
									</td>
							  	</tr>

							  	<?php if($payment_has_record){ ?>
									<tr class="alt">
								  	    <td class="payment_label"><strong>Payment ID</strong></td>
								  		<td><?php echo $payment_id; ?></td>
								  	</tr>
								  	<tr>
								  	    <td class="payment_label"><strong>Payment Date</strong></td>
								  		<td><?php echo $payment_date; ?></td>
								  	</tr>
								  	<tr class="alt">
								  	    <td>&nbsp;</td>
								  		<td>&nbsp;</td>
								  	</tr>
								  	<tr>
								  	    <td class="payment_label"><strong>Full Name</strong></td>
								  		<td><?php echo htmlspecialchars($payment_fullname,ENT_QUOTES); ?></td>
								  	</tr>
								  	
								  	<?php if(!empty($payment_ask_billing) && !empty($billing_address)){ ?>
								  	<tr class="alt">
								  	    <td class="payment_label"><strong>Billing Address</strong></td>
								  		<td><?php echo $billing_address; ?></td>
								  	</tr>
								  	<?php } ?>
								  	
								  	<?php if(!empty($payment_ask_shipping) && !empty($shipping_address)){ ?>
								  	<tr>
								  	    <td class="payment_label"><strong>Shipping Address</strong></td>
								  		<td><?php echo $shipping_address; ?></td>
								  	</tr>
								  	<?php } ?>
							  	
							  	<?php } ?>

							</tbody>
						</table>
						<?php } ?>

						<table width="100%" cellspacing="0" cellpadding="0" border="0" id="ve_table_info">
							<tbody>		
								<tr>
							  	    <td class="entry_info_header">
							  	    	<span class="icon-info"></span>Entry Info</td>
							  		<td>&nbsp; </td>
							  	</tr> 
									
								<tr class="alt">
							  	    <td width="40%"><strong>Date Created</strong></td>
							  		<td width="60%"><?php echo $date_created; ?></td>
							  	</tr>  	
							  	<tr>
							  	    <td><strong>Date Updated</strong></td>
							  		<td><?php echo $date_updated; ?></td>
							  	</tr>  	
								<tr class="alt">
							  	    <td><strong>IP Address</strong></td>
							  		<td><?php echo $ip_address; ?></td>
							  	</tr>
							</tbody>
						</table>

						<?php if($is_incomplete_entry){ ?>
							<table width="100%" cellspacing="0" cellpadding="0" border="0" id="ve_table_info">
								<tbody>		
									<tr>
								  	    <td class="entry_info_header">
								  	    	<span class="icon-info"></span> Resume URL</td>
								  		<td>&nbsp; </td>
								  	</tr> 
									<tr class="alt">
								  	    <td colspan="2">
								  	    	<input readonly="readonly" onclick="javascript: this.select()" id="form_resume_url" name="form_resume_url" class="text" style="width: 475px;font-size: 12px" value="<?php echo htmlspecialchars($form_resume_url); ?>" type="text">
								  	    	<a class="blue_dotted trigger-edit-resume-link" href="javascript:void(0)" id="form_resume_url_link" data-clipboard-action="copy" data-clipboard-target="#form_resume_url" style="font-weight:bold; margin-left: 10px">Copy Link</a>
								  	   	</td>
								  	</tr>  	
								</tbody>
							</table>
						<?php } ?>

						<?php if(!empty($form_edit_url) && !$is_incomplete_entry){ ?>
							<table width="100%" cellspacing="0" cellpadding="0" border="0" id="ve_table_info">
								<tbody>		
									<tr>
								  	    <td class="entry_info_header">
								  	    	<span class="icon-info"></span> Edit URL</td>
								  		<td>&nbsp; </td>
								  	</tr> 
									<tr class="alt">
								  	    <td colspan="2">
								  	    	<input readonly="readonly" onclick="javascript: this.select()" id="form_edit_url" name="form_edit_url" class="text" style="width: 475px;font-size: 12px" value="<?php echo htmlspecialchars($form_edit_url); ?>" type="text">
								  	    	<a class="blue_dotted trigger-edit-resume-link" href="javascript:void(0)" id="form_edit_url_link" data-clipboard-action="copy" data-clipboard-target="#form_edit_url" style="font-weight:bold; margin-left: 10px">Copy Link</a>
								  	    </td>
								  	</tr>  	
								</tbody>
							</table>
						<?php } ?>

						<?php if(!empty($payment_resume_url)){ ?>
							<table width="100%" cellspacing="0" cellpadding="0" border="0" id="ve_table_info">
								<tbody>		
									<tr>
								  	    <td class="entry_info_header">
								  	    	<span class="icon-info"></span> Payment URL</td>
								  		<td>&nbsp; </td>
								  	</tr> 
									<tr class="alt">
								  	    <td colspan="2"><a class="ve_resume_link" href="<?php echo $payment_resume_url; ?>">Open Payment Page</a></td>
								  	</tr>  	
								</tbody>
							</table>
						<?php } ?>

					</div>
					<div id="ve_actions">
						<div id="ve_entry_navigation">
							<a href="<?php echo "view_entry.php?form_id={$form_id}&entry_id={$entry_id}&nav=prev"; ?>" title="Previous Entry" style="margin-left: 1px"><span class="icon-arrow-left"></span></a>
							<a href="<?php echo "view_entry.php?form_id={$form_id}&entry_id={$entry_id}&nav=next"; ?>" title="Next Entry" style="margin-left: 5px"><span class="icon-arrow-right"></span></a>
						</div>
						<div id="ve_entry_actions" class="gradient_blue">
							<ul>
								
								<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_perms['edit_entries'])){ ?>
								<li style="border-bottom: 1px dashed #8EACCF"><a id="ve_action_edit" title="Edit Entry" href="<?php echo "edit_entry.php?form_id={$form_id}&entry_id={$entry_id}"; ?>"><span class="icon-pencil"></span>Edit</a></li>
								<?php } ?>

								<li style="border-bottom: 1px dashed #8EACCF"><a id="ve_action_email" title="Email Entry" href="#"><span class="icon-envelope-opened"></span>Email</a></li>
								<li style="border-bottom: 1px dashed #8EACCF"><a id="ve_action_print" title="Print Entry" href="javascript:window.print()"><span class="icon-print"></span>Print</a></li>
								<li style="border-bottom: 1px dashed #8EACCF"><a id="ve_action_pdf" title="Export to PDF" href="<?php echo "view_entry_pdf.php?form_id={$form_id}&entry_id={$entry_id}"; ?>"><span class="icon-file-download"></span>PDF</a></li>
								
								<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_perms['edit_entries'])){ ?>
									
								<?php if(!empty($form_resume_enable) && !$is_incomplete_entry){ ?>
								<li style="border-bottom: 1px dashed #8EACCF"><a id="ve_action_status" title="Mark as Incomplete Entry" href="#"><span class="icon-unlocked"></span>Status</a></li>
								<?php } ?>

								<li><a id="ve_action_delete" title="Delete Entry" href="#"><span class="icon-remove"></span>Delete</a></li>
								<?php } ?>
								
							</ul>
						</div>
					</div>
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

<div id="dialog-confirm-entry-delete" title="Are you sure you want to delete this entry?" class="buttons" style="display: none">
	<span class="icon-bubble-notification"></span>
	<p id="dialog-confirm-entry-delete-msg">
		This action cannot be undone.<br/>
		<strong id="dialog-confirm-entry-delete-info">Data and files associated with this entry will be deleted.</strong><br/><br/>
	</p>				
</div>

<div id="dialog-confirm-entry-status" title="Change entry status to incomplete?" class="buttons" style="display: none">
	<span class="icon-bubble-notification"></span>
	<p id="dialog-confirm-entry-status-msg">
		This entry will be <strong>marked as incomplete</strong>.<br/>
		Your client will be able to resume/edit the entry again.<br/><br/>
	</p>				
</div>

<div id="dialog-decrypt-entry" title="Enter your Private Key:" class="buttons" style="display: none"> 
	<form id="dialog-decrypt-entry-form" class="dialog-form" style="padding-left: 10px;padding-bottom: 10px">	
		<ul id="dialog-decrypt-entry-li-basic">
			<li>
				<div>
					<input type="text" value="" class="text" name="dialog-decrypt-entry-input" id="dialog-decrypt-entry-input" />
				</div> 
				<div class="infomessage" style="padding-top: 5px;padding-bottom: 0px">Correct Private Key is needed to view encrypted data.</div>
			</li>
		</ul>
	</form>
</div>

<div id="dialog-email-entry" title="Email entry #<?php echo $entry_id; ?> to:" class="buttons" style="display: none"> 
	<form id="dialog-email-entry-form" class="dialog-form" style="padding-left: 10px;padding-bottom: 10px">	
		<ul id="dialog-email-entry-li-basic">
			<li>
				<div>
					<input type="text" value="" class="text" name="dialog-email-entry-input" id="dialog-email-entry-input" />
				</div>
				<div class="infomessage" style="padding-top: 5px;padding-bottom: 0px">Use commas to separate email addresses.</div>
				<div class="infomessage" id="infomessage_recent_emails" style="padding-top: 15px;padding-bottom: 0px">Recent Emails</div>
				<div id="email-entry-suggestion">
					<?php
						$total_recent_emails = count($recent_emails);

						for($i=0;$i<=1;$i++){
							if(!empty($recent_emails[$i])){
								echo "<a class=\"email-entry-suggestion-link\" href=\"#\">".htmlspecialchars($recent_emails[$i])."</a>";
							}
						}

						if($total_recent_emails > 2){
							echo '<a id="toggle-recent-emails" class="email-entry-suggestion-link email-entry-suggestion-toggle" style="font-weight: 700" href="#">&gt;</a>';
						}
					?>
				</div>
				<?php if($total_recent_emails > 2){ ?>
				<div id="email-entry-suggestion-full" style="display: none;">
				<?php
					for($i=2;$i<$total_recent_emails;$i++){
						if(!empty($recent_emails[$i])){
							echo "<a class=\"email-entry-suggestion-link\" href=\"#\">".htmlspecialchars($recent_emails[$i])."</a>";
						}
					}
				?>
				</div> 
				<?php } ?>
			</li>
		</ul>
		<div id="ve_box_email_more" style="padding-bottom: 10px;display: none">
			<label class="description" for="dialog-email-entry-template">Email Template</label>
									
			<select name="dialog-email-entry-template" id="dialog-email-entry-template" class="element select" style="font-size: 90%"> 
				<option value="notification">Notification Email</option>
				<option value="confirmation">Confirmation Email</option>
				<?php
					if(!empty($custom_logic_email_template_array)){
						foreach($custom_logic_email_template_array as $rule_id){
							echo "<option value=\"custom-{$rule_id}\">Custom - Logic Rule #{$rule_id}</option>";
						}
					}
				?>																	
			</select>

			<label class="description" for="dialog-email-entry-note">Add Note (optional)</label>
			<textarea class="element textarea medium" name="dialog-email-entry-note" id="dialog-email-entry-note" style="width: 350px; height: 75px"></textarea>
		</div>
		<div class="email_entry_more_switcher">
				<a id="more_option_email_entry" href="#">more options</a>
		</div>
	</form>
</div>

<div id="dialog-entry-sent" title="Success!" class="buttons" style="display: none">
	<span class="icon-checkmark-circle"></span> 
	<p id="dialog-entry-sent-msg">
		The entry has been sent.
	</p>
</div>

<div id="dialog-entry-status-success" title="Success!" class="buttons" style="display: none">
	<span class="icon-checkmark-circle"></span> 
	<p>
		The entry has been marked as incomplete.<br/>
		You can send the following <strong>Resume URL</strong> to your client:
		<div id="div_dialog_encryption_success" style="padding: 15px;margin-top:10px">
			<input onclick="javascript: this.select()" type="text" value="" class="text" name="form-resume-link" id="form-resume-link" />
		</div> 
	</p>	
</div>
 
<?php
	
	$footer_data =<<<EOT
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.core.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.widget.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.tabs.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.mouse.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.sortable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.draggable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.position.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.dialog.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/clipboard.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/sweetalert2.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/view_entry.js{$mf_version_tag}"></script>
EOT;

	require('includes/footer.php'); 
?>