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
	
	$form_id = (int) trim($_REQUEST['id']);
	
	$dbh = mf_connect_db();

	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);
	$mf_properties 	= mf_get_form_properties($dbh,$form_id,array('form_active'));
	
	
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

		//this page need edit_form permission
		if(empty($user_perms['edit_form'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to edit this form.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}
	
	//load the payment settings property from ap_forms table
	$payment_properties = new stdClass();
	$jquery_data_code = '';

	$query 	= "select 
					 form_name,
					 payment_enable_merchant,
					 payment_merchant_type,
					 ifnull(payment_paypal_email,'') payment_paypal_email,
					 payment_paypal_language,
					 payment_currency,
					 payment_show_total,
					 payment_total_location,
					 payment_enable_recurring,
					 payment_recurring_cycle,
					 payment_recurring_unit,
					 payment_enable_trial,
					 payment_trial_period,
					 payment_trial_unit,
					 payment_trial_amount,
					 payment_enable_setupfee,
					 payment_setupfee_amount,
					 payment_price_type,
					 payment_price_amount,
					 payment_price_name,
					 ifnull(payment_stripe_live_secret_key,'') as payment_stripe_live_secret_key,
					 ifnull(payment_stripe_live_public_key,'') as payment_stripe_live_public_key,
					 ifnull(payment_stripe_test_secret_key,'') as payment_stripe_test_secret_key,
					 ifnull(payment_stripe_test_public_key,'') as payment_stripe_test_public_key,
					 payment_stripe_enable_test_mode,
					 payment_stripe_enable_receipt,
					 payment_stripe_receipt_element_id,
					 payment_stripe_enable_payment_request_button,
					 ifnull(payment_stripe_account_country,'') as payment_stripe_account_country,
					 ifnull(payment_authorizenet_live_apiloginid,'') as payment_authorizenet_live_apiloginid,
					 ifnull(payment_authorizenet_live_transkey,'') as payment_authorizenet_live_transkey,
					 ifnull(payment_authorizenet_test_apiloginid,'') as payment_authorizenet_test_apiloginid,
					 ifnull(payment_authorizenet_test_transkey,'') as payment_authorizenet_test_transkey,
					 payment_authorizenet_enable_test_mode,
					 payment_authorizenet_save_cc_data,
					 payment_authorizenet_enable_email,
					 payment_authorizenet_email_element_id,
					 ifnull(payment_braintree_live_merchant_id,'') as payment_braintree_live_merchant_id,
					 ifnull(payment_braintree_live_public_key,'') as payment_braintree_live_public_key,
					 ifnull(payment_braintree_live_private_key,'') as payment_braintree_live_private_key,
					 ifnull(payment_braintree_live_encryption_key,'') as payment_braintree_live_encryption_key,
					 ifnull(payment_braintree_test_merchant_id,'') as payment_braintree_test_merchant_id,
					 ifnull(payment_braintree_test_public_key,'') as payment_braintree_test_public_key,
					 ifnull(payment_braintree_test_private_key,'') as payment_braintree_test_private_key,
					 ifnull(payment_braintree_test_encryption_key,'') as payment_braintree_test_encryption_key,
					 payment_braintree_enable_test_mode,
					 ifnull(payment_paypal_rest_live_clientid,'') as payment_paypal_rest_live_clientid,
					 ifnull(payment_paypal_rest_live_secret_key,'') as payment_paypal_rest_live_secret_key,
					 ifnull(payment_paypal_rest_test_clientid,'') as payment_paypal_rest_test_clientid,
					 ifnull(payment_paypal_rest_test_secret_key,'') as payment_paypal_rest_test_secret_key,
					 payment_paypal_rest_enable_test_mode,
					 payment_paypal_enable_test_mode,
					 payment_enable_invoice,
					 payment_invoice_email,
					 payment_delay_notifications,
					 payment_ask_billing,
					 payment_ask_shipping,
					 payment_enable_tax,
					 payment_tax_rate,
					 payment_enable_discount,
					 payment_discount_type,
					 payment_discount_code,
					 payment_discount_amount,
					 payment_discount_element_id,
					 payment_discount_max_usage,
					 payment_discount_expiry_date  
			     from 
			     	 ".MF_TABLE_PREFIX."forms 
			    where 
			    	 form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row)){
		$row['form_name'] = mf_trim_max_length($row['form_name'],50);	
		$form_name = htmlspecialchars($row['form_name']);
		
		$payment_properties->form_id = $form_id;
		$payment_properties->enable_merchant = (int) $row['payment_enable_merchant'];
		$payment_properties->merchant_type 	 = $row['payment_merchant_type'];
		$payment_properties->paypal_email 	 = $row['payment_paypal_email'];
		$payment_properties->paypal_language = $row['payment_paypal_language'];
		$payment_properties->paypal_enable_test_mode  = (int) $row['payment_paypal_enable_test_mode'];
		
		$payment_properties->currency 		  = $row['payment_currency'];
		$payment_properties->show_total 	  = (int) $row['payment_show_total'];
		$payment_properties->total_location   = $row['payment_total_location'];
		$payment_properties->enable_recurring = (int) $row['payment_enable_recurring'];
		$payment_properties->recurring_cycle  = (int) $row['payment_recurring_cycle'];
		$payment_properties->recurring_unit   = $row['payment_recurring_unit'];

		$payment_properties->enable_trial = (int) $row['payment_enable_trial'];
		$payment_properties->trial_period = (int) $row['payment_trial_period'];
		$payment_properties->trial_unit   = $row['payment_trial_unit'];
		$payment_properties->trial_amount = $row['payment_trial_amount'];

		$payment_properties->enable_setupfee = (int) $row['payment_enable_setupfee'];
		$payment_properties->setupfee_amount = $row['payment_setupfee_amount'];

		$payment_properties->enable_tax = (int) $row['payment_enable_tax'];
		$payment_properties->tax_rate 	= $row['payment_tax_rate'];

		$payment_properties->enable_discount = (int) $row['payment_enable_discount'];
		$payment_properties->discount_type 	 = $row['payment_discount_type'];
		$payment_properties->discount_code 	 = $row['payment_discount_code'];
		$payment_properties->discount_amount = (float) $row['payment_discount_amount'];
		$payment_properties->discount_element_id = (int) $row['payment_discount_element_id'];
		$payment_properties->discount_max_usage = (int) $row['payment_discount_max_usage'];
		$payment_properties->discount_expiry_date 	= $row['payment_discount_expiry_date'];

		$payment_properties->stripe_live_secret_key   = trim($row['payment_stripe_live_secret_key']);
		$payment_properties->stripe_live_public_key   = trim($row['payment_stripe_live_public_key']);
		$payment_properties->stripe_test_secret_key   = trim($row['payment_stripe_test_secret_key']);
		$payment_properties->stripe_test_public_key   = trim($row['payment_stripe_test_public_key']);
		$payment_properties->stripe_enable_test_mode  = (int) $row['payment_stripe_enable_test_mode'];
		$payment_properties->stripe_enable_receipt  	= (int) $row['payment_stripe_enable_receipt'];
		$payment_properties->stripe_receipt_element_id  = (int) $row['payment_stripe_receipt_element_id'];
		$payment_properties->stripe_enable_payment_request_button  = (int) $row['payment_stripe_enable_payment_request_button'];
		$payment_properties->stripe_account_country   = trim($row['payment_stripe_account_country']);
		
		if(empty($payment_properties->stripe_account_country)){
			$payment_properties->stripe_account_country = 'US';
		}

		$payment_properties->authorizenet_live_apiloginid   = trim($row['payment_authorizenet_live_apiloginid']);
		$payment_properties->authorizenet_live_transkey   	= trim($row['payment_authorizenet_live_transkey']);
		$payment_properties->authorizenet_test_apiloginid   = trim($row['payment_authorizenet_test_apiloginid']);
		$payment_properties->authorizenet_test_transkey   	= trim($row['payment_authorizenet_test_transkey']);
		$payment_properties->authorizenet_enable_test_mode  = (int) $row['payment_authorizenet_enable_test_mode'];
		$payment_properties->authorizenet_save_cc_data  	= (int) $row['payment_authorizenet_save_cc_data'];
		$payment_properties->authorizenet_enable_email  	= (int) $row['payment_authorizenet_enable_email'];
		$payment_properties->authorizenet_email_element_id  = (int) $row['payment_authorizenet_email_element_id'];

		$payment_properties->braintree_live_merchant_id    = trim($row['payment_braintree_live_merchant_id']);
		$payment_properties->braintree_live_public_key     = trim($row['payment_braintree_live_public_key']);
		$payment_properties->braintree_live_private_key    = trim($row['payment_braintree_live_private_key']);
		$payment_properties->braintree_live_encryption_key = trim($row['payment_braintree_live_encryption_key']);
		$payment_properties->braintree_test_merchant_id    = trim($row['payment_braintree_test_merchant_id']);
		$payment_properties->braintree_test_public_key     = trim($row['payment_braintree_test_public_key']);
		$payment_properties->braintree_test_private_key    = trim($row['payment_braintree_test_private_key']);
		$payment_properties->braintree_test_encryption_key = trim($row['payment_braintree_test_encryption_key']);
		$payment_properties->braintree_enable_test_mode    = (int) $row['payment_braintree_enable_test_mode'];
		
		$payment_properties->paypal_rest_live_clientid  	= trim($row['payment_paypal_rest_live_clientid']);
		$payment_properties->paypal_rest_live_secret_key  	= trim($row['payment_paypal_rest_live_secret_key']);
		$payment_properties->paypal_rest_test_clientid  	= trim($row['payment_paypal_rest_test_clientid']);
		$payment_properties->paypal_rest_test_secret_key  	= trim($row['payment_paypal_rest_test_secret_key']);
		$payment_properties->paypal_rest_enable_test_mode  	= (int) $row['payment_paypal_rest_enable_test_mode'];

		$payment_properties->enable_invoice  	  = (int) $row['payment_enable_invoice'];
		$payment_properties->delay_notifications  = (int) $row['payment_delay_notifications'];
		$payment_properties->ask_billing  		  = (int) $row['payment_ask_billing'];
		$payment_properties->ask_shipping  		  = (int) $row['payment_ask_shipping'];
		$payment_properties->invoice_email 		  = $row['payment_invoice_email'];
		
		$payment_properties->price_type   = $row['payment_price_type'];
		$payment_properties->price_amount = (float) $row['payment_price_amount'];
		$payment_properties->price_name   = $row['payment_price_name'];
		
		if(empty($payment_properties->price_name)){
			$payment_properties->price_name = $form_name.' Fee';
		}
	}
	
	//get the currency symbol first
	switch($payment_properties->currency){
		case 'USD' : $currency_symbol = '&#36;';break;
		case 'EUR' : $currency_symbol = '&#8364;';break;
		case 'GBP' : $currency_symbol = '&#163;';break;
		case 'AUD' : $currency_symbol = 'A&#36;';break;
		case 'CAD' : $currency_symbol = 'C&#36;';break;
		case 'JPY' : $currency_symbol = '&#165;';break;
		case 'THB' : $currency_symbol = '&#3647;';break;
		case 'HUF' : $currency_symbol = '&#70;&#116;';break;
		case 'CHF' : $currency_symbol = 'CHF';break;
		case 'CZK' : $currency_symbol = '&#75;&#269;';break;
		case 'SEK' : $currency_symbol = 'kr';break;
		case 'DKK' : $currency_symbol = 'kr';break;
		case 'NOK' : $currency_symbol = 'kr';break;
		case 'PHP' : $currency_symbol = '&#36;';break;
		case 'IDR' : $currency_symbol = 'Rp';break;
		case 'INR' : $currency_symbol = 'Rs';break;
		case 'MYR' : $currency_symbol = 'RM';break;
		case 'ZAR' : $currency_symbol = 'R';break;
		case 'PLN' : $currency_symbol = '&#122;&#322;';break;
		case 'BRL' : $currency_symbol = 'R&#36;';break;
		case 'HKD' : $currency_symbol = 'HK&#36;';break;
		case 'MXN' : $currency_symbol = 'Mex&#36;';break;
		case 'TWD' : $currency_symbol = 'NT&#36;';break;
		case 'TRY' : $currency_symbol = 'TL';break;
	}
	
	//when certain fields (checkboxes, radio buttons, dropdown) has an option being deleted
	//the related ap_element_prices records are not being updated
	//thus we need to do a cleanup here, before loading the prices
	$query = "select element_id,option_id from ".MF_TABLE_PREFIX."element_options where form_id = ? and live = 0";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$deleted_element_options = array();
	$i=0;
	while($row = mf_do_fetch_result($sth)){
		$deleted_element_options[$i]['element_id'] = $row['element_id'];
		$deleted_element_options[$i]['option_id']  = $row['option_id'];
		$i++;
	}

	foreach ($deleted_element_options as $value) {
		$query = "delete from ".MF_TABLE_PREFIX."element_prices where form_id=? and element_id=? and option_id=?";
	
		$params = array($form_id,$value['element_id'],$value['option_id']);
		mf_do_query($query,$params,$dbh);
	}

	//when certain fields (checkboxes, radio buttons, dropdown) has new option being added
	//the related ap_element_prices records are not being updated
	//thus we need to add them here, before loading the prices
	$query = "SELECT 
					C.element_id,
					C.option_id,
					C.price 
			    FROM (
						SELECT 
							  A.element_id,
							  A.option_id,
							  B.price 
						  FROM 
							  ".MF_TABLE_PREFIX."element_options A left join ".MF_TABLE_PREFIX."element_prices B 
							ON 
							  (A.form_id = B.form_id and A.element_id = B.element_id and A.option_id = B.option_id) 
						 WHERE 
						   	  A.form_id = ? and A.`live` = 1
					 ) C 
			   WHERE 
			   		C.price IS NULL 
			ORDER BY 
					element_id,option_id ASC";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$new_element_options = array();

	$i=0;
	while($row = mf_do_fetch_result($sth)){
		$new_element_options[$i]['element_id'] = $row['element_id'];
		$new_element_options[$i]['option_id']  = $row['option_id'];
		$i++;
	}

	//get existing price-enabled elements from ap_element_prices
	$existing_price_elements_id = array();
	$query = "select element_id from ".MF_TABLE_PREFIX."element_prices where form_id = ? group by element_id order by element_id asc";
	$params = array($form_id);

	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		$existing_price_elements_id[] = $row['element_id'];
	}

	//if existing_price_elements_id is empty, we don't need to insert into the table, because this means the form is new
	//and this is the first time the user loading this page
	if(!empty($new_element_options) && !empty($existing_price_elements_id)){
		//insert into ap_element_prices and set the price to 0
		foreach ($new_element_options as $value) {

			//if this item doesn't have any friends within the same element_id, we don't need to insert, because this mean the field is new
			if(!in_array($value['element_id'], $existing_price_elements_id)){
				continue;
			}

			$query = "INSERT INTO ".MF_TABLE_PREFIX."element_prices(form_id,element_id,option_id,`price`) VALUES(?,?,?,?)";
			
			$params = array($form_id,$value['element_id'],$value['option_id'],0);
			mf_do_query($query,$params,$dbh);
		}
	}

	//get price-ready fields for this form and put them into array
	//price-ready fields are the following types: price, checkboxes, multiple choice, dropdown
	$query = "select 
					element_title,
					element_id,
					element_type 
				from 
					".MF_TABLE_PREFIX."form_elements 
			   where 
			   		form_id=? and 
			   		element_status=1 and 
			   		element_is_private=0 and 
			   		element_type in('radio','money','select','checkbox') 
		    order by 
		    		element_title asc";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$price_field_array = array();
	$price_field_options_array = array();
	$price_field_options_lookup = array();
	
	while($row = mf_do_fetch_result($sth)){
		$element_id = $row['element_id'];
		$price_field_array[$element_id]['element_title'] = htmlspecialchars(strip_tags($row['element_title']));
		$price_field_array[$element_id]['element_type'] = $row['element_type'];

		if(strlen($price_field_array[$element_id]['element_title']) > 45){
			$price_field_array[$element_id]['element_title'] = substr($price_field_array[$element_id]['element_title'], 0, 45).'...';
		}
		
		if($row['element_type'] != 'money'){
			//get the choices for the field
			$sub_query = "select 
								option_id,
								`option` 
							from 
								".MF_TABLE_PREFIX."element_options 
						   where 
						   		form_id=? and 
						   		live=1 and 
						   		element_id=? 
						order by 
								`position` asc";
			$sub_params = array($form_id,$element_id);
			$sub_sth = mf_do_query($sub_query,$sub_params,$dbh);
			$i=0;
			while($sub_row = mf_do_fetch_result($sub_sth)){
				$price_field_options_array[$element_id][$i]['option_id'] = $sub_row['option_id'];
				$price_field_options_array[$element_id][$i]['option'] = htmlspecialchars(strip_tags($sub_row['option']));
				$price_field_options_lookup[$element_id][$sub_row['option_id']] = htmlspecialchars(strip_tags($sub_row['option']));
				$i++;
			}
			
		}
	}
	
	if(!empty($price_field_options_array)){
		$json_price_field_options = json_encode($price_field_options_array);
		$jquery_data_code .= "\$('#ps_box_define_prices').data('field_options',{$json_price_field_options});\n";
	}
	
	//load existing data from ap_element_prices table
	$query = "select 
					A.element_id,
					A.option_id,
					A.`price`,
					B.`position` 
				from 
					".MF_TABLE_PREFIX."element_prices A left join ".MF_TABLE_PREFIX."element_options B 
				  on 
				  	(A.form_id=B.form_id and A.element_id=B.element_id and A.option_id=B.option_id) 
   			   where 
   			   		A.form_id = ? 
   			order by 
   					A.element_id,B.position asc";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$current_price_settings = array();
	
	while($row = mf_do_fetch_result($sth)){
		$element_id = (int) $row['element_id'];
		$option_id = (int) $row['option_id'];
		$current_price_settings[$element_id][$option_id]  = $row['price'];
	}
	
	
	//prepare the dom data for the prices fields
	foreach ($current_price_settings as $element_id=>$values){
		if($price_field_array[$element_id]['element_type'] == 'money'){ //if this is 'price' field
			$price_values = new stdClass();
			
			$price_values->element_id = $element_id;
			$price_values->option_id  = 0;
			$price_values->price      = 0;
			$price_values->element_type = 'price';
			
			$json_price_values = json_encode($price_values);
			$jquery_data_code .= "\$('#liprice_{$element_id}').data('field_price_properties',{$json_price_values});\n";
		}else{
			
			
			$price_values_array = array();
			foreach ($values as $option_id=>$price){
				$price_values = new stdClass();
				
				$price_values->element_id = $element_id;
				$price_values->option_id  = $option_id;
				$price_values->price      = $price;
				$price_values->element_type = 'multi';
				
				$price_values_array[$option_id] = $price_values;
			}
			
			$json_price_values = json_encode($price_values_array);
			$jquery_data_code .= "\$('#liprice_{$element_id}').data('field_price_properties',{$json_price_values});\n";
		}	
	}

	//get email fields for this form
	$query = "select 
					element_id,
					element_title 
				from 
					`".MF_TABLE_PREFIX."form_elements` 
			   where 
			   		form_id=? and element_type='email' and element_is_private=0 and element_status=1
			order by 
					element_title asc";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);

	$i=1;
	$email_fields = array();
	while($row = mf_do_fetch_result($sth)){
		$email_fields[$i]['label'] = $row['element_title'];
		$email_fields[$i]['value'] = $row['element_id'];
		$i++;
	}

	//initialize the stripe email receipt field with the first 'email' field on the form, if the field is currently not being set
	if(empty($payment_properties->stripe_receipt_element_id) && !empty($email_fields)){
		$payment_properties->stripe_receipt_element_id = $email_fields[1]['value'];
	}

	//get single line text fields for this form
	$query = "select 
					element_id,
					element_title 
				from 
					`".MF_TABLE_PREFIX."form_elements` 
			   where 
			   		form_id=? and element_type='text' and element_is_private=0 and element_status=1
			order by 
					element_title asc";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);

	$i=1;
	$coupon_code_fields = array();
	while($row = mf_do_fetch_result($sth)){
		
		$element_title = htmlspecialchars(strip_tags($row['element_title']));
		
		if(empty($element_title)){
			$element_title = '-untitled field-';
		}

		if(strlen($element_title) > 70){
			$element_title = substr($element_title, 0, 70).'...';
		}	

		$coupon_code_fields[$i]['label'] = $element_title;
		$coupon_code_fields[$i]['value'] = $row['element_id'];
		$i++;
	}

	//initialize the discount field with the first 'single line text' field on the form, if the field is currently not being set
	if(empty($payment_properties->discount_element_id) && !empty($coupon_code_fields)){
		$payment_properties->discount_element_id = $coupon_code_fields[1]['value'];
	}

	$json_payment_properties = json_encode($payment_properties);
	$jquery_data_code .= "\$('#ps_main_list').data('payment_properties',{$json_payment_properties});\n";

	$header_data =<<<EOT
<link type="text/css" href="js/datepick/smoothness.datepick.css{$mf_version_tag}" rel="stylesheet" />
EOT;
	
	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full" data-csrftoken="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>">
			<div class="post payment_settings">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Payment Settings</h2>
							<p>Configure payment options for your form</p>
						</div>	
						<div style="float: right;margin-right: 5px">
								<a href="#" id="button_save_payment" class="bb_button bb_small bb_green">
									<span class="icon-disk" style="margin-right: 5px"></span>Save Settings
								</a>
						</div>
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>
				<div class="content_body">
					
					<ul id="ps_main_list">
						<li>
							<div id="ps_box_merchant_settings" class="ps_box_main gradient_blue">
								<div class="ps_box_meta">
									<h1>1.</h1>
									<h6>Merchant Settings</h6>
								</div>
								<div class="ps_box_content">
									<span>	
										<input id="ps_enable_merchant" class="checkbox" value="" type="checkbox" style="margin-left: 0px" <?php if(!empty($payment_properties->enable_merchant)){ echo 'checked="checked"'; } ?>>
										<label class="choice" for="ps_enable_merchant">Enable Merchant</label> 
										<span class="icon-question helpicon" data-tippy-content="Disabling this option will turn off the payment functionality of your form."></span>
									</span>
									<div class="clearfix"></div>
									<label class="description inline" for="ps_select_merchant" style="margin-top: 10px">
										Select a Merchant 
									</label>
									<span class="icon-question helpicon" data-tippy-content="A merchant will process transactions on your form and authorize the payments."></span>
									<select class="select" id="ps_select_merchant" style="width: 60%" autocomplete="off">
										<option <?php if($payment_properties->merchant_type == 'paypal_standard'){ echo 'selected="selected"'; } ?> value="paypal_standard">PayPal Standard</option>
										<option <?php if($payment_properties->merchant_type == 'stripe'){ echo 'selected="selected"'; } ?> value="stripe">Stripe</option>
										<option <?php if($payment_properties->merchant_type == 'authorizenet'){ echo 'selected="selected"'; } ?> value="authorizenet">Authorize.net</option>
										<option <?php if($payment_properties->merchant_type == 'braintree'){ echo 'selected="selected"'; } ?> value="braintree">Braintree</option>
										<option <?php if($payment_properties->merchant_type == 'check'){ echo 'selected="selected"'; } ?> value="check">Check / Cash</option>
										
										<?php if($payment_properties->merchant_type == 'paypal_rest'){ ?>
											<option <?php if($payment_properties->merchant_type == 'paypal_rest'){ echo 'selected="selected"'; } ?> value="paypal_rest">PayPal Pro (DEPRECATED)</option>
										<?php } ?>
									</select>
									<div id="ps_paypal_options" class="merchant_options" <?php if($payment_properties->merchant_type != 'paypal_standard'){ echo 'style="display: none"'; } ?>>
										<label class="description inline" for="ps_paypal_email">PayPal Email Address <span class="required">*</span> </label>
										<span class="icon-question helpicon" data-tippy-content="This is the email address associated with your PayPal account."></span>
										<input id="ps_paypal_email" name="ps_paypal_email" style="width: 94%" class="element text large" value="<?php echo htmlspecialchars($payment_properties->paypal_email); ?>" type="text">
					
										<label class="description inline" for="ps_paypal_language">Language </label>
										<span class="icon-question helpicon" data-tippy-content="Select the language to be displayed on PayPal pages."></span>
										<select id="ps_paypal_language" name="ps_paypal_language" class="select large" style="width: 95%">
											<option value="US" <?php if($payment_properties->paypal_language == 'US'){ echo 'selected="selected"'; } ?>>English (American)</option>
											<option value="GB" <?php if($payment_properties->paypal_language == 'GB'){ echo 'selected="selected"'; } ?>>English (Great Britain)</option>
											<option value="AU" <?php if($payment_properties->paypal_language == 'AU'){ echo 'selected="selected"'; } ?>>English (Australian)</option>
											<option value="BG" <?php if($payment_properties->paypal_language == 'BG'){ echo 'selected="selected"'; } ?>>Bulgarian</option>
											<option value="CN" <?php if($payment_properties->paypal_language == 'CN'){ echo 'selected="selected"'; } ?>>Chinese</option>
											<option value="DK" <?php if($payment_properties->paypal_language == 'DK'){ echo 'selected="selected"'; } ?>>Danish</option>
											<option value="NL" <?php if($payment_properties->paypal_language == 'NL'){ echo 'selected="selected"'; } ?>>Dutch</option>
											<option value="EE" <?php if($payment_properties->paypal_language == 'EE'){ echo 'selected="selected"'; } ?>>Estonian</option>
											<option value="FI" <?php if($payment_properties->paypal_language == 'FI'){ echo 'selected="selected"'; } ?>>Finnish</option>
											<option value="FR" <?php if($payment_properties->paypal_language == 'FR'){ echo 'selected="selected"'; } ?>>French</option>
											<option value="fr_CA" <?php if($payment_properties->paypal_language == 'fr_CA'){ echo 'selected="selected"'; } ?>>French (Canada)</option>
											<option value="DE" <?php if($payment_properties->paypal_language == 'DE'){ echo 'selected="selected"'; } ?>>German</option>
											<option value="GR" <?php if($payment_properties->paypal_language == 'GR'){ echo 'selected="selected"'; } ?>>Greek</option>
											<option value="HU" <?php if($payment_properties->paypal_language == 'HU'){ echo 'selected="selected"'; } ?>>Hungarian</option>
											<option value="IT" <?php if($payment_properties->paypal_language == 'IT'){ echo 'selected="selected"'; } ?>>Italian</option>
											<option value="JP" <?php if($payment_properties->paypal_language == 'JP'){ echo 'selected="selected"'; } ?>>Japanese</option>
											<option value="NO" <?php if($payment_properties->paypal_language == 'NO'){ echo 'selected="selected"'; } ?>>Norwegian</option>
											<option value="PL" <?php if($payment_properties->paypal_language == 'PL'){ echo 'selected="selected"'; } ?>>Polish</option>
											<option value="PT" <?php if($payment_properties->paypal_language == 'PT'){ echo 'selected="selected"'; } ?>>Portuguese</option>
											<option value="RO" <?php if($payment_properties->paypal_language == 'RO'){ echo 'selected="selected"'; } ?>>Romanian</option>
											<option value="ES" <?php if($payment_properties->paypal_language == 'ES'){ echo 'selected="selected"'; } ?>>Spanish</option>
											<option value="SE" <?php if($payment_properties->paypal_language == 'SE'){ echo 'selected="selected"'; } ?>>Swedish</option>
											<option value="CH" <?php if($payment_properties->paypal_language == 'CH'){ echo 'selected="selected"'; } ?>>Swiss-German</option>
										</select>

										<input id="ps_paypal_enable_test_mode" <?php if(!empty($payment_properties->paypal_enable_test_mode)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px;margin-top: 15px">
										<label class="choice" for="ps_paypal_enable_test_mode">Enable Test Mode</label>		
										<span class="icon-question helpicon" data-tippy-content="If enabled, all transactions will go through PayPal test server (Sandbox). You can use this to test payments without using actual money. You'll need to login to https://developer.paypal.com and create your sandbox account there."></span>
									</div>
									<div id="ps_authorizenet_options" class="merchant_options" <?php if($payment_properties->merchant_type != 'authorizenet'){ echo 'style="display: none"'; } ?>>
										<div id="ps_authorizenet_live_keys" <?php if(!empty($payment_properties->authorizenet_enable_test_mode)){ echo 'style="display: none;"'; } ?>>
											<label class="description inline" for="ps_authorizenet_live_apiloginid">API Login ID <span class="required">*</span> </label>
											<span class="icon-question helpicon" data-tippy-content="These are keys provided by Authorize.net. Login to your Authorize.net account and go to 'Account' -> 'Settings' -> 'API Login ID and Transaction Key'. Copy all your keys here."></span>
											<input id="ps_authorizenet_live_apiloginid" name="ps_authorizenet_live_apiloginid" class="element text large" value="<?php echo htmlspecialchars($payment_properties->authorizenet_live_apiloginid); ?>" type="text">

											<label class="description" for="ps_authorizenet_live_transkey">Transaction Key <span class="required">*</span></label>
											<input id="ps_authorizenet_live_transkey" name="ps_authorizenet_live_transkey" class="element text large" value="<?php echo htmlspecialchars($payment_properties->authorizenet_live_transkey); ?>" type="text">
										</div>
										
										<div id="ps_authorizenet_test_keys">
											<input id="ps_authorizenet_enable_test_mode" <?php if(!empty($payment_properties->authorizenet_enable_test_mode)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px;">
											<label class="choice" for="ps_authorizenet_enable_test_mode">Enable Test Mode</label>
											<span class="icon-question helpicon" data-tippy-content="You need to signup for Authorize.net Sandbox account to use this. If enabled, all credit card transactions won't go through the actual credit card network. You can use some test cards numbers (provided by Authorize.net) to simulate a successful transaction."></span>
											<div id="ps_authorizenet_test_keys_div" <?php if(empty($payment_properties->authorizenet_enable_test_mode)){ echo 'style="display: none;"'; } ?>>
													<label class="description inline" for="ps_authorizenet_test_apiloginid" style="margin-top: 10px">Sandbox API Login ID </label>
													<span class="icon-question helpicon" data-tippy-content="These are keys provided by Authorize.net. Login to your Authorize.net Sandbox account and go to 'Account' -> 'Settings' -> 'API Login ID and Transaction Key'. Copy all your keys here."></span>
													<input id="ps_authorizenet_test_apiloginid" name="ps_authorizenet_test_apiloginid" class="element text large" value="<?php echo htmlspecialchars($payment_properties->authorizenet_test_apiloginid); ?>" type="text"></span>

													<label class="description" for="ps_authorizenet_test_transkey" style="margin-top: 15px">Sandbox Transaction Key</label>
													<input id="ps_authorizenet_test_transkey" name="ps_authorizenet_test_transkey" class="element text large" value="<?php echo htmlspecialchars($payment_properties->authorizenet_test_transkey); ?>" type="text"></span>
											</div>
										</div>													
									</div>
									<div id="ps_braintree_options" class="merchant_options" <?php if($payment_properties->merchant_type != 'braintree'){ echo 'style="display: none"'; } ?>>
										<div id="ps_braintree_live_keys" <?php if(!empty($payment_properties->braintree_enable_test_mode)){ echo 'style="display: none;"'; } ?>>
											<label class="description inline" for="ps_braintree_live_merchant_id">Merchant ID <span class="required">*</span> </label>
											<span class="icon-question helpicon" data-tippy-content="These are API keys provided by Braintree. You can see all your keys in your Braintree control panel. Login to your Braintree account and go to 'Your Username' -> 'My User' -> 'API Keys'. Copy all your live keys here. <br/><br/>To use a specific Merchant Account ID, use a semicolon, ex:<br/> merchant-id;merchant-account-id"></span>
											<input id="ps_braintree_live_merchant_id" name="ps_braintree_live_merchant_id" class="element text large" value="<?php echo htmlspecialchars($payment_properties->braintree_live_merchant_id); ?>" type="text">

											<label class="description" for="ps_braintree_live_public_key">Public Key <span class="required">*</span></label>
											<input id="ps_braintree_live_public_key" name="ps_braintree_live_public_key" class="element text large" value="<?php echo htmlspecialchars($payment_properties->braintree_live_public_key); ?>" type="text">

											<label class="description" for="ps_braintree_live_private_key">Private Key <span class="required">*</span></label>
											<input id="ps_braintree_live_private_key" name="ps_braintree_live_private_key" class="element text large" value="<?php echo htmlspecialchars($payment_properties->braintree_live_private_key); ?>" type="text">

											<label class="description" for="ps_braintree_live_encryption_key">Client-Side Encryption Key <span class="required">*</span></label>
											<textarea class="element textarea small" style="width: 90%" name="ps_braintree_live_encryption_key" id="ps_braintree_live_encryption_key"><?php echo htmlspecialchars($payment_properties->braintree_live_encryption_key); ?></textarea>
										</div>
										
										<div id="ps_braintree_test_keys">
											<input id="ps_braintree_enable_test_mode" <?php if(!empty($payment_properties->braintree_enable_test_mode)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px;">
											<label class="choice" for="ps_braintree_enable_test_mode">Enable Test Mode</label>
											<span class="icon-question helpicon" data-tippy-content="You need to signup for Braintree Sandbox account to use this. If enabled, all credit card transactions won't go through the actual credit card network. You can use some test cards numbers (provided by Braintree) to simulate a successful transaction."></span>
											<div id="ps_braintree_test_keys_div" <?php if(empty($payment_properties->braintree_enable_test_mode)){ echo 'style="display: none;"'; } ?>>
													<label class="description inline" for="ps_braintree_test_merchant_id">Sandbox Merchant ID </label>
													<span class="icon-question helpicon" data-tippy-content="These are API keys provided by Braintree. You can see all your keys in your Braintree control panel. Login to your Braintree account and go to 'Your Username' -> 'My User' -> 'API Keys'. Copy all your sandbox keys here.<br/><br/>To use a specific Merchant Account ID, use a semicolon, ex:<br/> merchant-id;merchant-account-id"></span>
													<input id="ps_braintree_test_merchant_id" name="ps_braintree_test_merchant_id" class="element text large" value="<?php echo htmlspecialchars($payment_properties->braintree_test_merchant_id); ?>" type="text">

													<label class="description" for="ps_braintree_test_public_key">Sandbox Public Key</label>
													<input id="ps_braintree_test_public_key" name="ps_braintree_test_public_key" class="element text large" value="<?php echo htmlspecialchars($payment_properties->braintree_test_public_key); ?>" type="text">

													<label class="description" for="ps_braintree_test_private_key">Sandbox Private Key</label>
													<input id="ps_braintree_test_private_key" name="ps_braintree_test_private_key" class="element text large" value="<?php echo htmlspecialchars($payment_properties->braintree_test_private_key); ?>" type="text">

													<label class="description" for="ps_braintree_test_encryption_key">Sandbox Encryption Key</label>
													<textarea class="element textarea small" style="width: 90%" name="ps_braintree_test_encryption_key" id="ps_braintree_test_encryption_key"><?php echo htmlspecialchars($payment_properties->braintree_test_encryption_key); ?></textarea>
											</div>
										</div>													
									</div>
									<div id="ps_paypal_rest_options" class="merchant_options" <?php if($payment_properties->merchant_type != 'paypal_rest'){ echo 'style="display: none"'; } ?>>
										<div id="ps_paypal_rest_live_keys" <?php if(!empty($payment_properties->paypal_rest_enable_test_mode)){ echo 'style="display: none;"'; } ?>>
											<label class="description inline" for="ps_paypal_rest_live_clientid">Client ID <span class="required">*</span> </label>
											<span class="icon-question helpicon" data-tippy-content="These are keys provided by PayPal. Login to https://developer.paypal.com and go to Applications -> My apps -> Create App. Follow the instruction and create an app with the name 'machform'. You'll get all your keys there."></span>
											<input id="ps_paypal_rest_live_clientid" name="ps_paypal_rest_live_clientid" class="element text large" value="<?php echo htmlspecialchars($payment_properties->paypal_rest_live_clientid); ?>" type="text">

											<label class="description" for="ps_paypal_rest_live_secret_key">Secret Key <span class="required">*</span></label>
											<input id="ps_paypal_rest_live_secret_key" name="ps_paypal_rest_live_secret_key" class="element text large" value="<?php echo htmlspecialchars($payment_properties->paypal_rest_live_secret_key); ?>" type="text">
										</div>
										
										<div id="ps_paypal_rest_test_keys">
											<input id="ps_paypal_rest_enable_test_mode" <?php if(!empty($payment_properties->paypal_rest_enable_test_mode)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px;">
											<label class="choice" for="ps_paypal_rest_enable_test_mode">Enable Test Mode</label>
											<span class="icon-question helpicon" data-tippy-content="If enabled, all credit card transactions won't go through the actual credit card network. You can use some test cards numbers (provided by PayPal) to simulate a successful transaction."></span>
											<div id="ps_paypal_rest_test_keys_div" <?php if(empty($payment_properties->paypal_rest_enable_test_mode)){ echo 'style="display: none;"'; } ?>>
													<label class="description inline" for="ps_paypal_rest_test_clientid" style="margin-top: 10px">Test Client ID </label>
													<span class="icon-question helpicon" data-tippy-content="These are keys provided by PayPal. Login to https://developer.paypal.com and go to Applications -> My apps -> Create App. Follow the instruction and create an app with the name 'machform'. You'll get all your keys there."></span>
													<input id="ps_paypal_rest_test_clientid" name="ps_paypal_rest_test_clientid" class="element text large" value="<?php echo htmlspecialchars($payment_properties->paypal_rest_test_clientid); ?>" type="text"></span>

													<label class="description" for="ps_paypal_rest_test_secret_key" style="margin-top: 15px">Test Secret Key</label>
													<input id="ps_paypal_rest_test_secret_key" name="ps_paypal_rest_test_secret_key" class="element text large" value="<?php echo htmlspecialchars($payment_properties->paypal_rest_test_secret_key); ?>" type="text"></span>
											</div>
										</div>													
									</div>
									<div id="ps_stripe_options" class="merchant_options" <?php if($payment_properties->merchant_type != 'stripe'){ echo 'style="display: none"'; } ?>>
										<div id="ps_stripe_live_keys" <?php if(!empty($payment_properties->stripe_enable_test_mode)){ echo 'style="display: none;"'; } ?>>
											<label class="description inline" for="ps_stripe_live_public_key">Live Publishable Key <span class="required">*</span></label>
											<span class="icon-question helpicon" data-tippy-content="These are keys provided by Stripe. You can see all your keys in your Stripe dashboard. Login to your Stripe account and go to 'Your Account' -> 'Account Settings' -> 'API Keys'. Copy all your Live keys here."></span>
											<input id="ps_stripe_live_public_key" name="ps_stripe_live_public_key" class="element text large" value="<?php echo htmlspecialchars($payment_properties->stripe_live_public_key); ?>" type="text">

											<label class="description" for="ps_stripe_live_secret_key">Live Secret Key <span class="required">*</span> </label>
											<input id="ps_stripe_live_secret_key" name="ps_stripe_live_secret_key" class="element text large" value="<?php echo htmlspecialchars($payment_properties->stripe_live_secret_key); ?>" type="text">		
										</div>
										
										<div id="ps_stripe_test_keys">
											<input id="ps_stripe_enable_test_mode" <?php if(!empty($payment_properties->stripe_enable_test_mode)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px;">
											<label class="choice inline" for="ps_stripe_enable_test_mode">Enable Test Mode</label>
											<span class="icon-question helpicon" data-tippy-content="If enabled, all credit card transactions won't go through the actual credit card network. You can use some test cards numbers (provided by Stripe) to simulate a successful transaction."></span>
											<div id="ps_stripe_test_keys_div" <?php if(empty($payment_properties->stripe_enable_test_mode)){ echo 'style="display: none;"'; } ?>>
													<label class="description inline" for="ps_stripe_test_public_key" style="margin-top: 15px">Test Publishable Key</label>
													<span class="icon-question helpicon" data-tippy-content="These are keys provided by Stripe. You can see all your keys in your Stripe dashboard. Login to your Stripe account and go to 'Your Account' -> 'Account Settings' -> 'API Keys'. Copy all your Test keys here."></span>
													<input id="ps_stripe_test_public_key" name="ps_stripe_test_public_key" class="element text large" value="<?php echo htmlspecialchars($payment_properties->stripe_test_public_key); ?>" type="text"></span>

													<label class="description inline" for="ps_stripe_test_secret_key" style="margin-top: 10px">Test Secret Key </label>
													<input id="ps_stripe_test_secret_key" name="ps_stripe_test_secret_key" class="element text large" value="<?php echo htmlspecialchars($payment_properties->stripe_test_secret_key); ?>" type="text"></span>				
											</div>
										</div>													
									</div>
									<div id="ps_stripe_info" class="merchant_options" <?php if($payment_properties->merchant_type != 'stripe'){ echo 'style="display: none"'; } ?>>
										Stripe is an easy way to accept credit card payments. <br/><a class="blue_dotted" href="https://stripe.com/" target="_blank">Learn More</a>
									</div>

									<div id="ps_check_options" class="merchant_options" <?php if($payment_properties->merchant_type != 'check'){ echo 'style="display: none"'; } ?>>
										This allows you to create payment forms (with option to have total calculations) without having actual payment processor integration.		
									</div>

								</div>
							</div>
						</li>
						<li class="ps_arrow" <?php if($payment_properties->enable_merchant === 0){ echo 'style="display: none;"'; } ?>><span class="icon-arrow-down11 spacer-icon"></span></li>
						<li <?php if($payment_properties->enable_merchant === 0){ echo 'style="display: none;"'; } ?>>
							<div id="ps_box_payment_options" class="ps_box_main gradient_red">
								<div class="ps_box_meta">
									<h1>2.</h1>
									<h6>Payment Options</h6>
								</div>
								<div class="ps_box_content">
									<div id="ps_currency_paypal_div" <?php if($payment_properties->merchant_type != 'paypal_standard'){ echo 'style="display: none"'; } ?>>
										<label class="description inline" for="ps_currency_paypal" style="margin-top: 2px">
										Currency
										</label>
										<span class="icon-question helpicon" data-tippy-content="Select the currency you would like to use to accept the payment from your clients."></span>
										<select class="select large" id="ps_currency_paypal" name="ps_currency_paypal" autocomplete="off">
											<option value="USD" <?php if($payment_properties->currency == 'USD'){ echo 'selected="selected"'; } ?>>&#36; - U.S. Dollar</option>
											<option value="EUR" <?php if($payment_properties->currency == 'EUR'){ echo 'selected="selected"'; } ?>>&#8364; - Euro</option>
											<option value="GBP" <?php if($payment_properties->currency == 'GBP'){ echo 'selected="selected"'; } ?>>&#163; - Pound Sterling</option>
											<option value="AUD" <?php if($payment_properties->currency == 'AUD'){ echo 'selected="selected"'; } ?>>A&#36; - Australian Dollar</option>
											<option value="CAD" <?php if($payment_properties->currency == 'CAD'){ echo 'selected="selected"'; } ?>>C&#36; - Canadian Dollar</option>
											
											<option value="JPY" <?php if($payment_properties->currency == 'JPY'){ echo 'selected="selected"'; } ?>>&#165; - Japanese Yen</option>
											<option value="THB" <?php if($payment_properties->currency == 'THB'){ echo 'selected="selected"'; } ?>>&#3647; - Thai Baht</option>
											<option value="HUF" <?php if($payment_properties->currency == 'HUF'){ echo 'selected="selected"'; } ?>>&#70;&#116; - Hungarian Forint</option>
											<option value="CHF" <?php if($payment_properties->currency == 'CHF'){ echo 'selected="selected"'; } ?>>CHF - Swiss Francs</option>
											<option value="SGD" <?php if($payment_properties->currency == 'SGD'){ echo 'selected="selected"'; } ?>>&#36; - Singapore Dollar</option>
											<option value="CZK" <?php if($payment_properties->currency == 'CZK'){ echo 'selected="selected"'; } ?>>&#75;&#269; - Czech Koruna</option>
											<option value="SEK" <?php if($payment_properties->currency == 'SEK'){ echo 'selected="selected"'; } ?>>kr - Swedish Krona</option>
											<option value="DKK" <?php if($payment_properties->currency == 'DKK'){ echo 'selected="selected"'; } ?>>kr - Danish Krone</option>
											<option value="NOK" <?php if($payment_properties->currency == 'NOK'){ echo 'selected="selected"'; } ?>>kr - Norwegian Krone</option>
											<option value="PHP" <?php if($payment_properties->currency == 'PHP'){ echo 'selected="selected"'; } ?>>&#36; - Philippine Peso</option>
											<option value="MYR" <?php if($payment_properties->currency == 'MYR'){ echo 'selected="selected"'; } ?>>RM - Malaysian Ringgit</option>
											<option value="NZD" <?php if($payment_properties->currency == 'NZD'){ echo 'selected="selected"'; } ?>>NZ&#36; - New Zealand Dollar</option>
											<option value="PLN" <?php if($payment_properties->currency == 'PLN'){ echo 'selected="selected"'; } ?>>&#122;&#322; - Polish Złoty</option>
											<option value="BRL" <?php if($payment_properties->currency == 'BRL'){ echo 'selected="selected"'; } ?>>R&#36; - Brazilian Real</option>
											<option value="HKD" <?php if($payment_properties->currency == 'HKD'){ echo 'selected="selected"'; } ?>>HK&#36; - Hong Kong Dollar</option>
											<option value="MXN" <?php if($payment_properties->currency == 'MXN'){ echo 'selected="selected"'; } ?>>Mex&#36; - Mexican Peso</option>
											<option value="TWD" <?php if($payment_properties->currency == 'TWD'){ echo 'selected="selected"'; } ?>>NT&#36; - Taiwan New Dollar</option>
											<option value="TRY" <?php if($payment_properties->currency == 'TRY'){ echo 'selected="selected"'; } ?>>TL - Turkish Lira</option>
										</select>
									</div>

									<div id="ps_currency_stripe_div" <?php if($payment_properties->merchant_type != 'stripe'){ echo 'style="display: none"'; } ?>>
										<label class="description inline" for="ps_currency_stripe" style="margin-top: 2px">
										Currency
										</label>
										<span class="icon-question helpicon" data-tippy-content="Select the currency you would like to use to accept the payment from your clients."></span>
										<select class="select large" id="ps_currency_stripe" name="ps_currency_stripe" autocomplete="off">
											<option value="USD" <?php if($payment_properties->currency == 'USD'){ echo 'selected="selected"'; } ?>>&#36; - U.S. Dollar</option>
											<option value="EUR" <?php if($payment_properties->currency == 'EUR'){ echo 'selected="selected"'; } ?>>&#8364; - Euro</option>
											<option value="GBP" <?php if($payment_properties->currency == 'GBP'){ echo 'selected="selected"'; } ?>>&#163; - Pound Sterling</option>
											<option value="AUD" <?php if($payment_properties->currency == 'AUD'){ echo 'selected="selected"'; } ?>>A&#36; - Australian Dollar</option>
											<option value="CAD" <?php if($payment_properties->currency == 'CAD'){ echo 'selected="selected"'; } ?>>C&#36; - Canadian Dollar</option>
											
											<option value="JPY" <?php if($payment_properties->currency == 'JPY'){ echo 'selected="selected"'; } ?>>&#165; - Japanese Yen</option>
											<option value="THB" <?php if($payment_properties->currency == 'THB'){ echo 'selected="selected"'; } ?>>&#3647; - Thai Baht</option>
											<option value="HUF" <?php if($payment_properties->currency == 'HUF'){ echo 'selected="selected"'; } ?>>&#70;&#116; - Hungarian Forint</option>
											<option value="CHF" <?php if($payment_properties->currency == 'CHF'){ echo 'selected="selected"'; } ?>>CHF - Swiss Francs</option>
											<option value="SGD" <?php if($payment_properties->currency == 'SGD'){ echo 'selected="selected"'; } ?>>&#36; - Singapore Dollar</option>
											<option value="CZK" <?php if($payment_properties->currency == 'CZK'){ echo 'selected="selected"'; } ?>>&#75;&#269; - Czech Koruna</option>
											<option value="SEK" <?php if($payment_properties->currency == 'SEK'){ echo 'selected="selected"'; } ?>>kr - Swedish Krona</option>
											<option value="DKK" <?php if($payment_properties->currency == 'DKK'){ echo 'selected="selected"'; } ?>>kr - Danish Krone</option>
											<option value="NOK" <?php if($payment_properties->currency == 'NOK'){ echo 'selected="selected"'; } ?>>kr - Norwegian Krone</option>
											<option value="RON" <?php if($payment_properties->currency == 'RON'){ echo 'selected="selected"'; } ?>>L - Romanian Leu</option>
											<option value="PHP" <?php if($payment_properties->currency == 'PHP'){ echo 'selected="selected"'; } ?>>&#36; - Philippine Peso</option>
											<option value="ZAR" <?php if($payment_properties->currency == 'ZAR'){ echo 'selected="selected"'; } ?>>R - South African Rand</option>
											<option value="IDR" <?php if($payment_properties->currency == 'IDR'){ echo 'selected="selected"'; } ?>>Rp - Indonesian Rupiah</option>
											<option value="MYR" <?php if($payment_properties->currency == 'MYR'){ echo 'selected="selected"'; } ?>>RM - Malaysian Ringgit</option>
											<option value="NZD" <?php if($payment_properties->currency == 'NZD'){ echo 'selected="selected"'; } ?>>NZ&#36; - New Zealand Dollar</option>
											<option value="PLN" <?php if($payment_properties->currency == 'PLN'){ echo 'selected="selected"'; } ?>>&#122;&#322; - Polish Złoty</option>
											<option value="BRL" <?php if($payment_properties->currency == 'BRL'){ echo 'selected="selected"'; } ?>>R&#36; - Brazilian Real</option>
											<option value="HKD" <?php if($payment_properties->currency == 'HKD'){ echo 'selected="selected"'; } ?>>HK&#36; - Hong Kong Dollar</option>
											<option value="MXN" <?php if($payment_properties->currency == 'MXN'){ echo 'selected="selected"'; } ?>>Mex&#36; - Mexican Peso</option>
											<option value="TWD" <?php if($payment_properties->currency == 'TWD'){ echo 'selected="selected"'; } ?>>NT&#36; - Taiwan New Dollar</option>
											<option value="TRY" <?php if($payment_properties->currency == 'TRY'){ echo 'selected="selected"'; } ?>>TL - Turkish Lira</option>
										</select>
									</div>

									<div id="ps_currency_authorizenet_div" <?php if($payment_properties->merchant_type != 'authorizenet'){ echo 'style="display: none"'; } ?>>
										<label class="description inline" for="ps_currency_authorizenet" style="margin-top: 2px">
										Currency
										</label>
										<span class="icon-question helpicon" data-tippy-content="Select the currency you would like to use to accept the payment from your clients."></span>
										<select class="select large" id="ps_currency_authorizenet" name="ps_currency_authorizenet" autocomplete="off">
											<option value="USD" <?php if($payment_properties->currency == 'USD'){ echo 'selected="selected"'; } ?>>&#36; - U.S. Dollar</option>
											<option value="GBP" <?php if($payment_properties->currency == 'GBP'){ echo 'selected="selected"'; } ?>>&#163; - Pound Sterling</option>
											<option value="EUR" <?php if($payment_properties->currency == 'EUR'){ echo 'selected="selected"'; } ?>>&#8364; - Euros</optbesok ion>
											<option value="CAD" <?php if($payment_properties->currency == 'CAD'){ echo 'selected="selected"'; } ?>>C&#36; - Canadian Dollar</option>
											<option value="AUD" <?php if($payment_properties->currency == 'AUD'){ echo 'selected="selected"'; } ?>>A&#36; - Australian Dollar</option>
											<option value="NZD" <?php if($payment_properties->currency == 'NZD'){ echo 'selected="selected"'; } ?>>NZ&#36; - New Zealand Dollar</option>
										</select>
									</div>

									<div id="ps_currency_braintree_div" <?php if($payment_properties->merchant_type != 'braintree'){ echo 'style="display: none"'; } ?>>
										<label class="description inline" for="ps_currency_braintree" style="margin-top: 2px">
										Currency
										</label>
										<span class="icon-question helpicon" data-tippy-content="Select the currency you would like to use to accept the payment from your clients."></span>
										<select class="select large" id="ps_currency_braintree" name="ps_currency_braintree" autocomplete="off">
											<option value="USD" <?php if($payment_properties->currency == 'USD'){ echo 'selected="selected"'; } ?>>&#36; - U.S. Dollar</option>
											<option value="GBP" <?php if($payment_properties->currency == 'GBP'){ echo 'selected="selected"'; } ?>>&#163; - Pound Sterling</option>
											<option value="EUR" <?php if($payment_properties->currency == 'EUR'){ echo 'selected="selected"'; } ?>>&#8364; - Euros</optbesok ion>
											<option value="CAD" <?php if($payment_properties->currency == 'CAD'){ echo 'selected="selected"'; } ?>>C&#36; - Canadian Dollar</option>
											<option value="AUD" <?php if($payment_properties->currency == 'AUD'){ echo 'selected="selected"'; } ?>>A&#36; - Australian Dollar</option>
											<option value="USD" <?php if($payment_properties->currency == 'SGD'){ echo 'selected="selected"'; } ?>>S&#36; - Singapore Dollar</option>
										</select>
									</div>

									<div id="ps_currency_paypal_rest_div" <?php if($payment_properties->merchant_type != 'paypal_rest'){ echo 'style="display: none"'; } ?>>
										<label class="description inline" for="ps_currency_paypal_rest" style="margin-top: 2px">
										Currency
										</label>
										<span class="icon-question helpicon" data-tippy-content="Select the currency you would like to use to accept the payment from your clients."></span>
										<select class="select large" id="ps_currency_paypal_rest" name="ps_currency_paypal_rest" autocomplete="off">
											<option value="USD" <?php if($payment_properties->currency == 'USD'){ echo 'selected="selected"'; } ?>>&#36; - U.S. Dollar</option>
											<option value="EUR" <?php if($payment_properties->currency == 'EUR'){ echo 'selected="selected"'; } ?>>&#8364; - Euro</option>
											<option value="GBP" <?php if($payment_properties->currency == 'GBP'){ echo 'selected="selected"'; } ?>>&#163; - Pound Sterling</option>
											<option value="AUD" <?php if($payment_properties->currency == 'AUD'){ echo 'selected="selected"'; } ?>>A&#36; - Australian Dollar</option>
											<option value="CAD" <?php if($payment_properties->currency == 'CAD'){ echo 'selected="selected"'; } ?>>C&#36; - Canadian Dollar</option>
											
											<option value="JPY" <?php if($payment_properties->currency == 'JPY'){ echo 'selected="selected"'; } ?>>&#165; - Japanese Yen</option>
											<option value="THB" <?php if($payment_properties->currency == 'THB'){ echo 'selected="selected"'; } ?>>&#3647; - Thai Baht</option>
											<option value="HUF" <?php if($payment_properties->currency == 'HUF'){ echo 'selected="selected"'; } ?>>&#70;&#116; - Hungarian Forint</option>
											<option value="CHF" <?php if($payment_properties->currency == 'CHF'){ echo 'selected="selected"'; } ?>>CHF - Swiss Francs</option>
											<option value="SGD" <?php if($payment_properties->currency == 'SGD'){ echo 'selected="selected"'; } ?>>&#36; - Singapore Dollar</option>
											<option value="CZK" <?php if($payment_properties->currency == 'CZK'){ echo 'selected="selected"'; } ?>>&#75;&#269; - Czech Koruna</option>
											<option value="SEK" <?php if($payment_properties->currency == 'SEK'){ echo 'selected="selected"'; } ?>>kr - Swedish Krona</option>
											<option value="DKK" <?php if($payment_properties->currency == 'DKK'){ echo 'selected="selected"'; } ?>>kr - Danish Krone</option>
											<option value="NOK" <?php if($payment_properties->currency == 'NOK'){ echo 'selected="selected"'; } ?>>kr - Norwegian Krone</option>
											<option value="PHP" <?php if($payment_properties->currency == 'PHP'){ echo 'selected="selected"'; } ?>>&#36; - Philippine Peso</option>
											<option value="MYR" <?php if($payment_properties->currency == 'MYR'){ echo 'selected="selected"'; } ?>>RM - Malaysian Ringgit</option>
											<option value="NZD" <?php if($payment_properties->currency == 'NZD'){ echo 'selected="selected"'; } ?>>NZ&#36; - New Zealand Dollar</option>
											<option value="PLN" <?php if($payment_properties->currency == 'PLN'){ echo 'selected="selected"'; } ?>>&#122;&#322; - Polish Złoty</option>
											<option value="BRL" <?php if($payment_properties->currency == 'BRL'){ echo 'selected="selected"'; } ?>>R&#36; - Brazilian Real</option>
											<option value="HKD" <?php if($payment_properties->currency == 'HKD'){ echo 'selected="selected"'; } ?>>HK&#36; - Hong Kong Dollar</option>
											<option value="MXN" <?php if($payment_properties->currency == 'MXN'){ echo 'selected="selected"'; } ?>>Mex&#36; - Mexican Peso</option>
											<option value="TWD" <?php if($payment_properties->currency == 'TWD'){ echo 'selected="selected"'; } ?>>NT&#36; - Taiwan New Dollar</option>
											<option value="TRY" <?php if($payment_properties->currency == 'TRY'){ echo 'selected="selected"'; } ?>>TL - Turkish Lira</option>
										</select>
									</div>

									<div id="ps_currency_check_div" <?php if($payment_properties->merchant_type != 'check'){ echo 'style="display: none"'; } ?>>
										<label class="description inline" for="ps_currency_check" style="margin-top: 2px">
										Currency
										</label>
										<span class="icon-question helpicon" data-tippy-content="Select the currency you would like to use to accept the payment from your clients."></span>
										<select class="select large" id="ps_currency_check" name="ps_currency_check" autocomplete="off">
											<option value="USD" <?php if($payment_properties->currency == 'USD'){ echo 'selected="selected"'; } ?>>&#36; - U.S. Dollar</option>
											<option value="EUR" <?php if($payment_properties->currency == 'EUR'){ echo 'selected="selected"'; } ?>>&#8364; - Euro</option>
											<option value="GBP" <?php if($payment_properties->currency == 'GBP'){ echo 'selected="selected"'; } ?>>&#163; - Pound Sterling</option>
											<option value="AUD" <?php if($payment_properties->currency == 'AUD'){ echo 'selected="selected"'; } ?>>A&#36; - Australian Dollar</option>
											<option value="CAD" <?php if($payment_properties->currency == 'CAD'){ echo 'selected="selected"'; } ?>>C&#36; - Canadian Dollar</option>
											
											<option value="JPY" <?php if($payment_properties->currency == 'JPY'){ echo 'selected="selected"'; } ?>>&#165; - Japanese Yen</option>
											<option value="THB" <?php if($payment_properties->currency == 'THB'){ echo 'selected="selected"'; } ?>>&#3647; - Thai Baht</option>
											<option value="HUF" <?php if($payment_properties->currency == 'HUF'){ echo 'selected="selected"'; } ?>>&#70;&#116; - Hungarian Forint</option>
											<option value="CHF" <?php if($payment_properties->currency == 'CHF'){ echo 'selected="selected"'; } ?>>CHF - Swiss Francs</option>
											<option value="SGD" <?php if($payment_properties->currency == 'SGD'){ echo 'selected="selected"'; } ?>>&#36; - Singapore Dollar</option>
											<option value="CZK" <?php if($payment_properties->currency == 'CZK'){ echo 'selected="selected"'; } ?>>&#75;&#269; - Czech Koruna</option>
											<option value="SEK" <?php if($payment_properties->currency == 'SEK'){ echo 'selected="selected"'; } ?>>kr - Swedish Krona</option>
											<option value="DKK" <?php if($payment_properties->currency == 'DKK'){ echo 'selected="selected"'; } ?>>kr - Danish Krone</option>
											<option value="NOK" <?php if($payment_properties->currency == 'NOK'){ echo 'selected="selected"'; } ?>>kr - Norwegian Krone</option>
											<option value="PHP" <?php if($payment_properties->currency == 'PHP'){ echo 'selected="selected"'; } ?>>&#36; - Philippine Peso</option>
											<option value="ZAR" <?php if($payment_properties->currency == 'ZAR'){ echo 'selected="selected"'; } ?>>R - South African Rand</option>
											<option value="IDR" <?php if($payment_properties->currency == 'IDR'){ echo 'selected="selected"'; } ?>>Rp - Indonesian Rupiah</option>
											<option value="INR" <?php if($payment_properties->currency == 'INR'){ echo 'selected="selected"'; } ?>>Rs - Indian Rupee</option>
											<option value="MYR" <?php if($payment_properties->currency == 'MYR'){ echo 'selected="selected"'; } ?>>RM - Malaysian Ringgit</option>
											<option value="NZD" <?php if($payment_properties->currency == 'NZD'){ echo 'selected="selected"'; } ?>>NZ&#36; - New Zealand Dollar</option>
											<option value="PLN" <?php if($payment_properties->currency == 'PLN'){ echo 'selected="selected"'; } ?>>&#122;&#322; - Polish Złoty</option>
											<option value="BRL" <?php if($payment_properties->currency == 'BRL'){ echo 'selected="selected"'; } ?>>R&#36; - Brazilian Real</option>
											<option value="HKD" <?php if($payment_properties->currency == 'HKD'){ echo 'selected="selected"'; } ?>>HK&#36; - Hong Kong Dollar</option>
											<option value="MXN" <?php if($payment_properties->currency == 'MXN'){ echo 'selected="selected"'; } ?>>Mex&#36; - Mexican Peso</option>
											<option value="TWD" <?php if($payment_properties->currency == 'TWD'){ echo 'selected="selected"'; } ?>>NT&#36; - Taiwan New Dollar</option>
											<option value="TRY" <?php if($payment_properties->currency == 'TRY'){ echo 'selected="selected"'; } ?>>TL - Turkish Lira</option>
										</select>
									</div>
									
									<div id="ps_optional_settings">
										<label class="description" style="margin-bottom: 15px">
										Optional Settings
										</label>

										<span id="ps_enable_fast_checkout" class="ps_options_span stripe_option" <?php if(in_array($payment_properties->merchant_type,array('stripe'))){ echo 'style="display: block"'; } ?>>
											<input id="ps_enable_payment_request_button" <?php if(!empty($payment_properties->stripe_enable_payment_request_button)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px">
											<label class="choice" for="ps_enable_payment_request_button">Enable Apple Pay / Google Pay</label>
											<span class="icon-question helpicon" data-tippy-content="Using this option, your customers are shown either an <strong>Apple Pay</strong> button or <strong>Google Pay</strong> button or Payment Request button, depending on what is supported by their device and browser combination. <br/><br/>If neither is available, the button is not displayed."></span>
											<div id="ps_enable_fast_checkout_div" <?php if(empty($payment_properties->stripe_enable_payment_request_button)){ echo 'style="display: none"'; } ?>>
												<label class="description inline" style="margin-top: 5px" for="ps_stripe_account_country">Stripe Account Country </label>
												<span class="icon-question helpicon" data-tippy-content="The country of your Stripe account is registered in."></span>

												<select class="select large" id="ps_stripe_account_country" name="ps_stripe_account_country" autocomplete="off">
													<option value="US" <?php if($payment_properties->stripe_account_country == 'US'){ echo 'selected="selected"'; } ?>>United States</option>
													<option value="GB" <?php if($payment_properties->stripe_account_country == 'GB'){ echo 'selected="selected"'; } ?>>United Kingdom</option>
													<option value="AU" <?php if($payment_properties->stripe_account_country == 'AU'){ echo 'selected="selected"'; } ?>>Australia</option>
													<option value="AT" <?php if($payment_properties->stripe_account_country == 'AT'){ echo 'selected="selected"'; } ?>>Austria</option>
													<option value="BE" <?php if($payment_properties->stripe_account_country == 'BE'){ echo 'selected="selected"'; } ?>>Belgium</option>
													<option value="BR" <?php if($payment_properties->stripe_account_country == 'BR'){ echo 'selected="selected"'; } ?>>Brazil</option>
													<option value="CA" <?php if($payment_properties->stripe_account_country == 'CA'){ echo 'selected="selected"'; } ?>>Canada</option>
													<option value="DK" <?php if($payment_properties->stripe_account_country == 'DK'){ echo 'selected="selected"'; } ?>>Denmark</option>
													<option value="FI" <?php if($payment_properties->stripe_account_country == 'FI'){ echo 'selected="selected"'; } ?>>Finland</option>
													<option value="FR" <?php if($payment_properties->stripe_account_country == 'FR'){ echo 'selected="selected"'; } ?>>France</option>
													<option value="DE" <?php if($payment_properties->stripe_account_country == 'DE'){ echo 'selected="selected"'; } ?>>Germany</option>
													<option value="HK" <?php if($payment_properties->stripe_account_country == 'HK'){ echo 'selected="selected"'; } ?>>Hong Kong</option>
													<option value="IE" <?php if($payment_properties->stripe_account_country == 'IE'){ echo 'selected="selected"'; } ?>>Ireland</option>
													<option value="JP" <?php if($payment_properties->stripe_account_country == 'JP'){ echo 'selected="selected"'; } ?>>Japan</option>
													<option value="LU" <?php if($payment_properties->stripe_account_country == 'LU'){ echo 'selected="selected"'; } ?>>Luxembourg</option>
													<option value="MX" <?php if($payment_properties->stripe_account_country == 'MX'){ echo 'selected="selected"'; } ?>>Mexico</option>
													<option value="NL" <?php if($payment_properties->stripe_account_country == 'NL'){ echo 'selected="selected"'; } ?>>Netherlands</option>
													<option value="NZ" <?php if($payment_properties->stripe_account_country == 'NZ'){ echo 'selected="selected"'; } ?>>New Zealand</option>
													<option value="NO" <?php if($payment_properties->stripe_account_country == 'NO'){ echo 'selected="selected"'; } ?>>Norway</option>
													<option value="SG" <?php if($payment_properties->stripe_account_country == 'SG'){ echo 'selected="selected"'; } ?>>Singapore</option>
													<option value="ES" <?php if($payment_properties->stripe_account_country == 'ES'){ echo 'selected="selected"'; } ?>>Spain</option>
													<option value="SE" <?php if($payment_properties->stripe_account_country == 'SE'){ echo 'selected="selected"'; } ?>>Sweden</option>
													<option value="CH" <?php if($payment_properties->stripe_account_country == 'CH'){ echo 'selected="selected"'; } ?>>Switzerland</option>
													<option value="IT" <?php if($payment_properties->stripe_account_country == 'IT'){ echo 'selected="selected"'; } ?>>Italy</option>
													<option value="PT" <?php if($payment_properties->stripe_account_country == 'PT'){ echo 'selected="selected"'; } ?>>Portugal</option>
												</select>
												
												<span id="ps_enable_fast_checkout_info">Supporting Apple Pay requires <a class="blue_dotted" href="https://docs.machform.com/help/verifying-your-domain-with-apple-pay" target="_blank">some additional steps</a>.</span>
											</div>
										</span>
									
										<input id="ps_show_total_amount" <?php if(!empty($payment_properties->show_total)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px">
										<label class="choice" for="ps_show_total_amount">Show Total Amount</label>
										<span class="icon-question helpicon" data-tippy-content="Shows the total amount of the payment to the client as they are filling out the form. You can also select the location of the total amount placement within the form."></span>
										<div id="ps_show_total_location_div" <?php if(empty($payment_properties->show_total)){ echo 'style="display: none"'; } ?>>
												Display at 
												<select class="select medium" id="ps_show_total_location" name="ps_show_total_location" autocomplete="off">
													<option <?php if($payment_properties->total_location == 'top'){ echo 'selected="selected"'; } ?> id="ps_location_top" value="top">top</option>
													<option <?php if($payment_properties->total_location == 'bottom'){ echo 'selected="selected"'; } ?> id="ps_location_bottom" value="bottom">bottom</option>
													<option <?php if($payment_properties->total_location == 'top-bottom'){ echo 'selected="selected"'; } ?> id="ps_location_top_bottom" value="top-bottom">top and bottom</option>
												</select>
										</div>
										
										<div style="clear: both;margin-top: 10px"></div>

										<div class="paypal_option stripe_option authorizenet_option paypal_rest_option braintree_option check_option" <?php if(!in_array($payment_properties->merchant_type,array('stripe','paypal_standard','authorizenet','paypal_rest','braintree','check'))){ echo 'style="display: none"'; } ?>>
											<input id="ps_enable_tax" <?php if(!empty($payment_properties->enable_tax)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px">
											<label class="choice" for="ps_enable_tax">Add Sales Tax</label>
											<span class="icon-question helpicon" data-tippy-content="Upon checkout, sales tax will automatically be added to the order total. You can define the tax rate here."></span>
											<div id="ps_tax_rate_div" <?php if(empty($payment_properties->enable_tax)){ echo 'style="display: none"'; } ?>>
													Tax Rate:  
													<input id="ps_tax_rate" name="ps_tax_rate" class="element text" style="width: 40px"  value="<?php echo htmlspecialchars($payment_properties->tax_rate); ?>" type="text"> %
											</div>
										</div>

										<div style="clear: both;margin-top: 10px"></div>
										
										<div class="paypal_option paypal_rest_option stripe_option authorizenet_option" <?php if(!in_array($payment_properties->merchant_type,array('stripe','paypal_standard','authorizenet','paypal_rest'))){ echo 'style="display: none"'; } ?>>
											<input id="ps_enable_recurring" <?php if(!empty($payment_properties->enable_recurring)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px;">
											<label class="choice" for="ps_enable_recurring">Enable Recurring Payments</label>
											<span class="icon-question helpicon" data-tippy-content="If enabled, your clients will be charged automatically for every period of time."></span>
											<div id="ps_recurring_div" <?php if(empty($payment_properties->enable_recurring)){ echo 'style="display: none"'; } ?>>
												<label class="description" style="margin-top: 5px">Charge Payment Every:</label>
												<select id="ps_recurring_cycle" class="select">
													<?php 
														for($i=1;$i<=10;$i++){
															if($i == $payment_properties->recurring_cycle){
																echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
															}else{
																echo '<option value="'.$i.'">'.$i.'</option>';	
															}
														}
													?>
												</select>
												<select id="ps_recurring_cycle_unit" class="select" <?php if(!in_array($payment_properties->merchant_type,array('paypal_standard','authorizenet','paypal_rest'))){ echo 'style="display: none"'; }; ?>>
													<option value="day" <?php if($payment_properties->recurring_unit == 'day'){ echo 'selected="selected"'; } ?>>Day(s)</option>
													<option value="week" <?php if($payment_properties->recurring_unit == 'week'){ echo 'selected="selected"'; } ?>>Week(s)</option>
													<option value="month" <?php if($payment_properties->recurring_unit == 'month'){ echo 'selected="selected"'; } ?>>Month(s)</option>
													<option value="year" <?php if($payment_properties->recurring_unit == 'year'){ echo 'selected="selected"'; } ?>>Year(s)</option>
												</select>
												<select id="ps_recurring_cycle_unit_month_year" class="select" <?php if($payment_properties->merchant_type != 'stripe'){ echo 'style="display: none"'; }; ?>>
													<option value="week" <?php if($payment_properties->recurring_unit == 'week'){ echo 'selected="selected"'; } ?>>Week(s)</option>
													<option value="month" <?php if($payment_properties->recurring_unit == 'month'){ echo 'selected="selected"'; } ?>>Month(s)</option>
													<option value="year" <?php if($payment_properties->recurring_unit == 'year'){ echo 'selected="selected"'; } ?>>Year(s)</option>
												</select>
											</div>
										</div>
										
										<div style="clear: both;margin-top: 10px"></div>
										<div class="paypal_option paypal_rest_option stripe_option authorizenet_option" id="ps_trial_div_container" <?php if(empty($payment_properties->enable_recurring) || !in_array($payment_properties->merchant_type, array('paypal_standard','paypal_rest','stripe','authorizenet'))){ echo 'style="display: none"'; } ?>>
											<input id="ps_enable_trial" <?php if(!empty($payment_properties->enable_trial)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px;">
											<label class="choice" for="ps_enable_trial">Enable Trial Periods</label>
											<span class="icon-question helpicon" data-tippy-content="You can enable trial periods to let your clients to try your subscription service before their regular subscription begin. You can set the price and duration of trial periods independently of the regular subscription price and billing cycle. Enter '0' to offer free trial."></span>
											<div id="ps_trial_div" <?php if(empty($payment_properties->enable_trial)){ echo 'style="display: none"'; } ?>>
												<label class="description" style="margin-top: 5px">Trial Period:</label>
												<select id="ps_trial_period" class="select">
													<?php 
														for($i=1;$i<=10;$i++){
															if($i == $payment_properties->trial_period){
																echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
															}else{
																echo '<option value="'.$i.'">'.$i.'</option>';	
															}
														}
													?>
												</select>
												<select id="ps_trial_unit" class="select">
													<option value="day" <?php if($payment_properties->trial_unit == 'day'){ echo 'selected="selected"'; } ?>>Day(s)</option>
													<option value="week" <?php if($payment_properties->trial_unit == 'week'){ echo 'selected="selected"'; } ?>>Week(s)</option>
													<option value="month" <?php if($payment_properties->trial_unit == 'month'){ echo 'selected="selected"'; } ?>>Month(s)</option>
													<option value="year" <?php if($payment_properties->trial_unit == 'year'){ echo 'selected="selected"'; } ?>>Year(s)</option>
												</select>
												<label class="description" style="margin-top: 5px">Trial Price:</label>
												<span class="symbol"><?php echo $currency_symbol; ?></span><span><input id="ps_trial_amount" name="ps_trial_amount" class="element text medium" value="<?php echo htmlspecialchars($payment_properties->trial_amount); ?>" type="text"></span>
											</div>
										</div>

										<div style="clear: both;margin-top: 10px"></div>
										<div class="stripe_option paypal_rest_option" id="ps_setupfee_div_container" <?php if(empty($payment_properties->enable_recurring) || !in_array($payment_properties->merchant_type, array('stripe','paypal_rest'))){ echo 'style="display: none"'; } ?>>
											<input id="ps_enable_setupfee" <?php if(!empty($payment_properties->enable_setupfee)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px;">
											<label class="choice" for="ps_enable_setupfee">Charge a One-time Setup Fee</label>
											<span class="icon-question helpicon" data-tippy-content="In addition of the recurring payments, your clients will be charged for a one-time setup fee as well."></span>
											<div id="ps_setupfee_div" <?php if(empty($payment_properties->enable_setupfee)){ echo 'style="display: none"'; } ?>>
												<label class="description" style="margin-top: 5px">Setup Fee:</label>
												<span class="symbol">$</span><span><input id="ps_setupfee_amount" name="ps_setupfee_amount" class="element text medium" value="<?php echo htmlspecialchars($payment_properties->setupfee_amount); ?>" type="text"></span>
											</div>
										</div>

										<div style="clear: both;margin-top: 10px"></div>
										<div class="paypal_option stripe_option authorizenet_option paypal_rest_option braintree_option" id="ps_discount_div_container" <?php if(!in_array($payment_properties->merchant_type,array('stripe','paypal_standard','authorizenet','paypal_rest','braintree'))){ echo 'style="display: none"'; } ?>>
											<input id="ps_enable_discount" <?php if(!empty($payment_properties->enable_discount)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px;">
											<label class="choice" for="ps_enable_discount">Enable Discount</label>
											<span class="icon-question helpicon" data-tippy-content="Allows your client to enter coupon code and receive discount."></span>
											<div id="ps_discount_div" <?php if(empty($payment_properties->enable_discount)){ echo 'style="display: none"'; } ?>>
												<ul>
													<li>
														<select class="select medium" id="ps_discount_type" name="ps_discount_type" autocomplete="off">
															<option <?php if($payment_properties->discount_type == 'percent_off'){ echo 'selected="selected"'; } ?> value="percent_off">Percent Off</option>
															<option <?php if($payment_properties->discount_type == 'amount_off'){ echo 'selected="selected"'; } ?> value="amount_off">Amount Off</option>
														</select> &#8674; <span class="symbol" id="discount_type_currency_sign" style="display: <?php if($payment_properties->discount_type == 'percent_off'){ echo 'none'; }else{ echo 'inline'; }  ?>"><?php echo $currency_symbol; ?></span> 
														<input id="ps_discount_amount" name="ps_discount_amount" class="element text" style="width: 40px"  value="<?php echo htmlspecialchars($payment_properties->discount_amount); ?>" type="text"> <span style="display: <?php if($payment_properties->discount_type == 'percent_off'){ echo 'inline'; }else{ echo 'none'; }  ?>" id="discount_type_percentage_sign">&#37;</span>
													</li>
													<li>
														<label class="description inline" for="ps_discount_code" style="margin-top: 0px">Coupon Code: </label>
														<span class="icon-question helpicon" data-tippy-content="Coupon codes are case insensitive. Use commas to separate multiple coupon codes."></span>
														<input id="ps_discount_code" name="ps_discount_code" class="element text large" value="<?php echo htmlspecialchars($payment_properties->discount_code ?? '',ENT_QUOTES); ?>" type="text">
													</li>
													<li>
														<label class="description inline" for="ps_discount_code" style="margin-top: 0px">Select Coupon Code Field: </label>
														<span class="icon-question helpicon" data-tippy-content="Select the field on your form to be used as the coupon code field. The field type must be 'Single Line Text'. If your form doesn't have it, you need to add it first."></span>
														<select class="select" id="ps_discount_element_id" name="ps_discount_element_id" style="width: 90%" autocomplete="off">
															<?php 
																if(!empty($coupon_code_fields)){ 
																	foreach ($coupon_code_fields as $data){
																		if($payment_properties->discount_element_id == $data['value']){
																			$selected = 'selected="selected"';
																		}else{
																			$selected = '';
																		}

																		echo "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>";
																	}
																}else{
																	echo '<option selected="selected" value="">-- No Text Field Available --</option>';
															 	} 
															?>
														</select>

													</li>
													<li>
														Max Redemptions:  
														<input id="ps_discount_max_usage" name="ps_discount_max_usage" class="element text" style="width: 40px"  value="<?php echo htmlspecialchars($payment_properties->discount_max_usage); ?>" type="text">
														 <span class="icon-question helpicon" data-tippy-content="The coupon can only be used this many times in total. Enter '0' for unlimited usage."></span>
													</li>
													<li id="ps_li_discount_expiry">
														<span>
															Expires On:
														</span>
														<?php
															$discount_expiry_dd = '';
															$discount_expiry_mm = '';
															$discount_expiry_yyyy = '';
															if(!empty($payment_properties->discount_expiry_date) && $payment_properties->discount_expiry_date != '0000-00-00'){
																list($discount_expiry_yyyy, $discount_expiry_mm, $discount_expiry_dd) = explode('-', $payment_properties->discount_expiry_date);
															}
														?> 
														<span>
														<input type="text" value="<?php echo $discount_expiry_mm; ?>" maxlength="2" size="2" style="width: 2em;" class="text" name="discount_expiry_mm" id="discount_expiry_mm">
														<label for="discount_expiry_mm">MM</label>
														</span>
														
														<span>
														<input type="text" value="<?php echo $discount_expiry_dd; ?>" maxlength="2" size="2" style="width: 2em;" class="text" name="discount_expiry_dd" id="discount_expiry_dd">
														<label for="discount_expiry_dd">DD</label>
														</span>
														
														<span>
														 <input type="text" value="<?php echo $discount_expiry_yyyy; ?>" maxlength="4" size="4" style="width: 3em;" class="text" name="discount_expiry_yyyy" id="discount_expiry_yyyy">
														<label for="discount_expiry_yyyy">YYYY</label>
														</span>
														
														<span id="discount_expiry_cal">
																<input type="hidden" value="" maxlength="4" size="4" style="width: 3em;" class="text" name="linked_picker_discount_expiry" id="linked_picker_discount_expiry">
																<div style="display: none"><img id="discount_expiry_pick_img" alt="Pick date." src="images/icons/calendar.png" class="trigger" style="margin-top: 3px; cursor: pointer" /></div>
														</span>
													</li>
												</ul>
											</div>
										</div>

										<div style="clear: both;margin-top: 10px"></div>
										<div class="stripe_option" id="ps_stripe_receipt_div_container" <?php if(!in_array($payment_properties->merchant_type,array('stripe'))){ echo 'style="display: none"'; } ?>>
											<input id="ps_stripe_enable_receipt" <?php if(!empty($payment_properties->stripe_enable_receipt)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px;">
											<label class="choice" for="ps_stripe_enable_receipt">Enable Stripe Email Receipts</label>
											<span class="icon-question helpicon" data-tippy-content="If enabled, your clients will receive email receipt directly from Stripe upon successful payments.<br/><br/>NOTE: Stripe doesn't send email receipts in Test Mode."></span>
											<div id="ps_stripe_enable_receipt_div" <?php if(empty($payment_properties->stripe_enable_receipt)){ echo 'style="display: none"'; } ?>>
												<ul>
													<li>
														<label class="description inline" style="margin-top: 5px" for="ps_stripe_receipt_element_id">Select Email Receipt Field: </label>
														<span class="icon-question helpicon" data-tippy-content="Stripe receipt will be sent to the email address entered into this field. If your form doesn't have it, you need to add an email field first."></span>

														<select class="select" id="ps_stripe_receipt_element_id" name="ps_stripe_receipt_element_id" style="width: 90%" autocomplete="off">
															<?php 
																if(!empty($email_fields)){ 
																	foreach ($email_fields as $data){
																		if($payment_properties->stripe_receipt_element_id == $data['value']){
																			$selected = 'selected="selected"';
																		}else{
																			$selected = '';
																		}

																		echo "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>";
																	}
																}else{
																	echo '<option selected="selected" value="">-- No Email Field Available --</option>';
															 	} 
															?>
														</select>
													</li>
													<li>
														<label class="description inline" for="ps_stripe_charge_desc" style="margin-top: 0px">Charge Description:<span class="required">*</span> </label>
														<span class="icon-question helpicon" data-tippy-content="Enter a descriptive name for the charge being included within the email receipt."></span>
														<input id="ps_stripe_charge_desc" name="ps_stripe_charge_desc" class="element text large" value="<?php echo htmlspecialchars($payment_properties->price_name,ENT_QUOTES); ?>" type="text">
													</li>
												</ul>
											</div>
										</div>

										<div class="authorizenet_option" id="ps_authorizenet_email_div_container" <?php if(!in_array($payment_properties->merchant_type,array('authorizenet'))){ echo 'style="display: none"'; } ?>>
											<input id="ps_authorizenet_enable_email" <?php if(!empty($payment_properties->authorizenet_enable_email)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px;">
											<label class="choice" for="ps_authorizenet_enable_email">Send Customer Email to Authorize.net</label>
											<span class="icon-question helpicon" data-tippy-content="This option is required only when you're using European payment processor with your Authorize.net account. <br/><br/>If you aren't sure, you can leave this option unchecked."></span>
											<div id="ps_authorizenet_enable_email_div" <?php if(empty($payment_properties->authorizenet_enable_email)){ echo 'style="display: none"'; } ?>>
												<ul>
													<li>
														<label class="description" style="margin-top: 5px" for="ps_authorizenet_email_element_id">Select Email Address Field:  </label>
														
														<select class="select" id="ps_authorizenet_email_element_id" name="ps_authorizenet_email_element_id" style="width: 90%" autocomplete="off">
															<?php 
																if(!empty($email_fields)){ 
																	foreach ($email_fields as $data){
																		if($payment_properties->authorizenet_email_element_id == $data['value']){
																			$selected = 'selected="selected"';
																		}else{
																			$selected = '';
																		}

																		echo "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>";
																	}
																}else{
																	echo '<option selected="selected" value="">-- No Email Field Available --</option>';
															 	} 
															?>
														</select>
													</li>
												</ul>
											</div>
										</div>
										
										<span class="ps_options_span paypal_option stripe_option authorizenet_option paypal_rest_option braintree_option" <?php if(in_array($payment_properties->merchant_type,array('paypal_standard','stripe','authorizenet','paypal_rest','braintree'))){ echo 'style="display: block"'; } ?>>
											<input id="ps_delay_notifications" <?php if(!empty($payment_properties->delay_notifications)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px">
											<label class="choice inline" for="ps_delay_notifications">Delay Notifications Until Paid</label>
											<span class="icon-question helpicon" data-tippy-content="By default, notification emails are being sent when payment is made successfully. If you disable this option (not recommended), notification emails are being sent immediately upon form submission, regardless of payment status."></span>
										</span>

										<span id="ask_billing_span" class="ps_options_span stripe_option authorizenet_option paypal_rest_option braintree_option" 
											<?php 
												if(in_array($payment_properties->merchant_type,array('authorizenet','paypal_rest','braintree','stripe'))){ 
													echo 'style="display: block"'; 
												}
											?>
										>
											<input id="ps_ask_billing" <?php if(!empty($payment_properties->ask_billing)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px">
											<label class="choice inline" for="ps_ask_billing">Ask for Billing Address</label>
											<span class="icon-question helpicon" data-tippy-content="If enabled, the payment page will prompt your clients to enter their billing address."></span>
										</span>

										<span id="ask_shipping_span" class="ps_options_span stripe_option authorizenet_option paypal_rest_option braintree_option" 
											<?php 
												if(in_array($payment_properties->merchant_type,array('authorizenet','paypal_rest','braintree','stripe'))){ 
													echo 'style="display: block"'; 
												}
											?>
										>
											<input id="ps_ask_shipping" <?php if(!empty($payment_properties->ask_shipping)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px">
											<label class="choice inline" for="ps_ask_shipping">Ask for Shipping Address</label>
											<span class="icon-question helpicon" data-tippy-content="If enabled, the payment page will prompt your clients to enter their shipping address."></span>
										</span>

										<span id="save_cc_data_span" class="ps_options_span authorizenet_option" <?php if(in_array($payment_properties->merchant_type,array('authorizenet'))){ echo 'style="display: block"'; } ?>>
											<input id="ps_save_cc_data" <?php if(!empty($payment_properties->authorizenet_save_cc_data)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px">
											<label class="choice inline" for="ps_save_cc_data">Save Cards to Authorize.net</label>
											<span class="icon-question helpicon" data-tippy-content="If enabled, all your customers data (including credit card numbers) will be stored into your account on Authorized.net server. You need to enable CIM on your Authorize.net account to use this feature."></span>
										</span>
										

									</div>
								</div>
							</div>
						</li>
						<li class="ps_arrow" <?php if($payment_properties->enable_merchant === 0){ echo 'style="display: none;"'; } ?>><span class="icon-arrow-down11 spacer-icon"></span></li>
						<li <?php if($payment_properties->enable_merchant === 0){ echo 'style="display: none;"'; } ?>>
							<div id="ps_box_define_prices" class="ps_box_main gradient_green">
								<div class="ps_box_meta">
									<h1>3.</h1>
									<h6>Define Prices</h6>
								</div>
								<div class="ps_box_content">
									<div id="ps_box_price_selector">
										<select id="ps_pricing_type" class="select">
												<option value="fixed" <?php if($payment_properties->price_type == 'fixed'){ echo 'selected="selected"'; } ?>>Fixed Amount</option>
												<option value="variable" <?php if($payment_properties->price_type == 'variable'){ echo 'selected="selected"'; } ?>>Variable Amount</option>
										</select>
										<span class="icon-arrow-right2 define-prices-icon"></span>
									</div>
									<div id="ps_box_price_fields">
										<div id="ps_box_price_fixed_amount_div" <?php if($payment_properties->price_type == 'variable'){ echo 'style="display: none;"'; } ?>>
											
											<label class="description inline" for="ps_price_amount" style="margin-top: 0px">Price Amount <span class="required">*</span></label>
											<span class="icon-question helpicon clearfix" data-tippy-content="Enter the amount to be charged to your client."></span>
											<span class="symbol"><?php echo $currency_symbol; ?></span><span><input id="ps_price_amount" name="ps_price_amount" class="element text medium" value="<?php echo $payment_properties->price_amount; ?>" type="text"></span>
											
											<div class="clearfix"></div>

											<span id="ps_price_name_container" <?php if($payment_properties->merchant_type == 'stripe' && !empty($payment_properties->stripe_enable_receipt)){ echo 'style="display: none;"'; } ?>>
												<label class="description inline" for="ps_price_name" style="margin-top: 15px">Price Name <span class="required">*</span></label>
												<span class="icon-question helpicon clearfix" data-tippy-content="Enter a descriptive name for the price. This will be displayed into payment pages and the receipt email being sent to your client."></span>
												<input id="ps_price_name" name="ps_price_name" class="element text large" value="<?php echo htmlspecialchars($payment_properties->price_name,ENT_QUOTES); ?>" type="text">
											</span>

											<p><span class="icon-info helpicon" style="vertical-align: top;margin-right: 5px"></span> Fixed Amount - Your clients will be charged a fixed amount per form submission.</p>
										</div>
										<div id="ps_box_price_variable_amount_div" <?php if($payment_properties->price_type == 'fixed'){ echo 'style="display: none;"'; } ?>>
											
											<?php if(!empty($price_field_array)){ ?>
											
											<label class="description inline" for="ps_select_field_prices" style="margin-top: 2px">
											Add a Field To Set Prices
											</label>
											<span class="icon-question helpicon" data-tippy-content="Add one or more field from this list to set the prices. A field can have one or more prices, depends on the type. When your client select any of the field you've set here, he will be charged the amount being assigned for the selected field. Supported fields: Checkboxes, Drop Down, Multiple Choice, Price"></span>
											<select class="select large" id="ps_select_field_prices" name="ps_select_field_prices" autocomplete="off">
												<option value=""></option>
												<?php 
													foreach ($price_field_array as $element_id=>$data){
														
														if($data['element_type'] == 'radio'){
															$element_type = 'Multiple Choice';
														}else if($data['element_type'] == 'money'){
															$element_type = 'Price';
														}else if($data['element_type'] == 'select'){
															$element_type = 'Drop Down';
														}else if($data['element_type'] == 'checkbox'){
															$element_type = 'Checkboxes';
														}
														
														$price_field_array[$element_id]['complete_title'] = $data['element_title'].' ('.$element_type.')';
														$price_field_array[$element_id]['element_type']   = $data['element_type'];
														
														if(empty($current_price_settings[$element_id])){
															echo "<option value=\"{$element_id}\">{$data['element_title']} ({$element_type})</option>";
														}
													}
												?>
											</select>
											<ul id="ps_field_assignment">
												<?php 
													
													if(!empty($current_price_settings)){
														
														
														foreach ($current_price_settings as $element_id=>$data){
															$liprice_markup = '';
															
															if($price_field_array[$element_id]['element_type'] == 'money'){ //if this is price field
																$liprice_markup = '<li id="liprice_'.$element_id.'">'.
																	'<table width="100%" cellspacing="0">'.
																		'<thead>'.
																			'<tr><td>'.
																				'<strong>'.$price_field_array[$element_id]['complete_title'].'</strong>'.
																				'<a href="#" id="deleteliprice_'.$element_id.'" class="delete_liprice"><span class="icon-cancel-circle"></span></a>'.
																			'</td></tr>'.
																		'</thead>'.
																		'<tbody>'.
																			'<tr><td class="ps_td_field_label">Amount will be entered by the client.</td></tr>'.
																		'</tbody>'.
																	'</table>'.
																	'</li>';
															}else{
																
																$liprice_markup = '<li style="" id="liprice_'.$element_id.'">'.
																	'<table width="100%" cellspacing="0">'.
																		'<thead>'.
																			'<tr><td colspan="2">'.
																				'<strong>'.$price_field_array[$element_id]['complete_title'].'</strong>'.
																				'<a href="#" id="deleteliprice_'.$element_id.'" class="delete_liprice"><span class="icon-cancel-circle"></span></a>'.
																			'</td></tr>'.
																		'</thead>'.
																		'<tbody>';
																		
																foreach ($data as $option_id=>$price){
																	$liprice_markup .=	'<tr>'.
																				'<td class="ps_td_field_label">'.$price_field_options_lookup[$element_id][$option_id].'</td>'.
																				'<td class="ps_td_field_price">'.
																					'<span class="ps_td_currency">'.$currency_symbol.'</span>'.
																					'<input type="text" class="element text large" value="'.$price.'" id="price_'.$element_id.'_'.$option_id.'">'.
																				'</td>'.
																			'</tr>';
																}
																			
																$liprice_markup .= '</tbody></table></li>';
															}
															
															echo $liprice_markup;
														}
													}
												?>
											</ul>
											
											<?php } else { ?>
												<div id="ps_no_price_fields">
													<h6>No Available Fields Found</h6>
													<p>To set variable amount prices, you need to add one or more of the following field types into your form: <span style="font-weight: 700">Checkboxes, Drop Down, Multiple Choice, Price.</span></p>
												</div>
											<?php } ?>
											
											<p><span class="icon-info helpicon" style="vertical-align: top;margin-right: 5px"></span> Variable Amount - Your clients will be charged a certain amount depends on their selection.</p>
										</div>
									</div>
								</div>
							</div>
						</li>
								
					</ul>
					
					
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

 
<?php
	$footer_data =<<<EOT
<script type="text/javascript">
	$(function(){
		{$jquery_data_code}		
    });
</script>
<script src="js/popper.min.js{$mf_version_tag}"></script>
<script src="js/tippy.index.all.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/datepick/jquery.datepick.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/payment_settings.js{$mf_version_tag}"></script>
<style>
.tippy-tooltip{
	font-size: 98%;
}
</style>
EOT;

	require('includes/footer.php'); 
?>