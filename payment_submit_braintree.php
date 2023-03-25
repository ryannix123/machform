<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	require('config.php');
	require('lib/db-session-handler.php');

	require('includes/init-form.php');
	
	require('includes/db-core.php');
	require('includes/helper-functions.php');
	require('includes/filter-functions.php');
	require('includes/post-functions.php');
	require('lib/braintree/Braintree.php');

	
	$form_id 			= (int) trim($_POST['form_id']);
	$payment_record_id 	= (int) trim($_POST['record_id']);
	$payment_data 		= mf_sanitize($_POST['payment_properties']);

	$customer_ip	    = $_SERVER['REMOTE_ADDR'];

	$payment_success  = false;
	$payment_message = '';
	
	if(empty($form_id) || empty($payment_record_id)){
		$response_data = new stdClass();
		$response_data->status    	= "error";
		$response_data->message 	= "Error. Your session has been expired. Please start the form again.";
		
		$response_json = json_encode($response_data);
		echo $response_json;
		
		exit;
	}

	$dbh = mf_connect_db();
	
	//get form properties data
	$query 	= "select 
					form_review,
					form_page_total,
					payment_enable_merchant,
					payment_merchant_type,
					payment_currency,
					payment_price_type,
					payment_price_name,
					payment_price_amount,
					payment_ask_billing,
					payment_ask_shipping,
					payment_braintree_live_merchant_id,
					payment_braintree_live_public_key,
					payment_braintree_live_private_key,
					payment_braintree_live_encryption_key,
					payment_braintree_test_merchant_id,
				 	payment_braintree_test_public_key,
					payment_braintree_test_private_key,
					payment_braintree_test_encryption_key,
					payment_braintree_enable_test_mode,
					payment_enable_recurring,
					payment_recurring_cycle,
					payment_recurring_unit,
					payment_enable_trial,
					payment_trial_period,
					payment_trial_unit,
					payment_trial_amount,
					payment_delay_notifications,
					payment_enable_tax,
					payment_tax_rate,
					payment_enable_discount,
					payment_discount_type,
					payment_discount_amount,
					payment_discount_element_id 
				from 
				    ".MF_TABLE_PREFIX."forms 
			   where 
				    form_id=? and form_active=1";
	$params = array($form_id);
		
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$form_review  	 		= (int) $row['form_review'];
	$form_page_total 		= (int) $row['form_page_total'];
		
	$payment_enable_merchant 	 = (int) $row['payment_enable_merchant'];
	
	$payment_enable_tax 		 = (int) $row['payment_enable_tax'];
	$payment_tax_rate 			 = (float) $row['payment_tax_rate'];

	$payment_currency 	   		 = strtoupper($row['payment_currency']);
	$payment_price_type 	     = $row['payment_price_type'];
	$payment_price_amount    	 = $row['payment_price_amount'];
	$payment_ask_billing 	 	 = (int) $row['payment_ask_billing'];
	$payment_ask_shipping 	 	 = (int) $row['payment_ask_shipping'];
	$payment_merchant_type		 = $row['payment_merchant_type'];
	
	$payment_braintree_live_merchant_id    = trim($row['payment_braintree_live_merchant_id']);
	$payment_braintree_live_public_key     = trim($row['payment_braintree_live_public_key']);
	$payment_braintree_live_private_key    = trim($row['payment_braintree_live_private_key']);
	$payment_braintree_live_encryption_key = trim($row['payment_braintree_live_encryption_key']);
	$payment_braintree_test_merchant_id    = trim($row['payment_braintree_test_merchant_id']);
	$payment_braintree_test_public_key     = trim($row['payment_braintree_test_public_key']);
	$payment_braintree_test_private_key    = trim($row['payment_braintree_test_private_key']);
	$payment_braintree_test_encryption_key = trim($row['payment_braintree_test_encryption_key']);
	$payment_braintree_enable_test_mode    = (int) $row['payment_braintree_enable_test_mode'];
	
	$payment_price_type   = $row['payment_price_type'];
	$payment_price_amount = (float) $row['payment_price_amount'];
	$payment_price_name   = $row['payment_price_name'];

	$payment_enable_recurring = 0; //Braintree API currently doesn't support creating subscription plan through API
	
	$payment_enable_discount = (int) $row['payment_enable_discount'];
	$payment_discount_type 	 = $row['payment_discount_type'];
	$payment_discount_amount = (float) $row['payment_discount_amount'];
	$payment_discount_element_id = (int) $row['payment_discount_element_id'];

	$payment_delay_notifications = (int) $row['payment_delay_notifications'];

	if(!empty($payment_data['same_shipping_address'])){
		$payment_data['shipping_street']	= $payment_data['billing_street'];
		$payment_data['shipping_city']		= $payment_data['billing_city'];
		$payment_data['shipping_state']		= $payment_data['billing_state'];
		$payment_data['shipping_zipcode'] 	= $payment_data['billing_zipcode'];
		$payment_data['shipping_country'] 	= $payment_data['billing_country'];
	}
	
	$is_discount_applicable = false;

	//if the discount element for the current entry_id having any value, we can be certain that the discount code has been validated and applicable
	if(!empty($payment_enable_discount)){
		$query = "select element_{$payment_discount_element_id} coupon_element from ".MF_TABLE_PREFIX."form_{$form_id} where `id` = ? and `status` = 1";
		$params = array($payment_record_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		if(!empty($row['coupon_element'])){
			$is_discount_applicable = true;
		}
	}

	if(!empty($payment_enable_merchant) && $payment_merchant_type == 'braintree'){
		
		if(!empty($payment_braintree_enable_test_mode)){
			//$payment_braintree_test_merchant_id accepts 'merchant-id;merchant-account-id'
			$exploded = array();
			$exploded = explode(';', $payment_braintree_test_merchant_id);

			$api_merchant_id		 = trim($exploded[0]);
			$api_merchant_account_id = isset($exploded[1]) ? trim($exploded[1]) : false;

			$api_public_key 	= $payment_braintree_test_public_key;
			$api_private_key 	= $payment_braintree_test_private_key;
			$api_environment = 'sandbox';
		}else{
			//$payment_braintree_live_merchant_id accepts 'merchant-id;merchant-account-id'
			$exploded = array();
			$exploded = explode(';', $payment_braintree_live_merchant_id);

			$api_merchant_id		 = trim($exploded[0]);
			$api_merchant_account_id = trim($exploded[1]);

			$api_public_key 	= $payment_braintree_live_public_key;
			$api_private_key 	= $payment_braintree_live_private_key;
			$api_environment = 'production';
		}

		//calculate payment amount
		if($payment_price_type == 'fixed'){ 
				
			$charge_amount = $payment_price_amount * 100; //charge in cents
		}else if($payment_price_type == 'variable'){ 
				
			$charge_amount = (double) mf_get_payment_total($dbh,$form_id,$payment_record_id,0,'live');
			$charge_amount = $charge_amount * 100;
		}

		//calculate discount if applicable
		if($is_discount_applicable){
			$payment_calculated_discount = 0;

			if($payment_discount_type == 'percent_off'){
				//the discount is percentage
				$payment_calculated_discount = ($payment_discount_amount / 100) * $charge_amount;
				$payment_calculated_discount = round($payment_calculated_discount,2); //round to 2 digits decimal
			}else{
				//the discount is fixed amount
				$payment_calculated_discount = round($payment_discount_amount * 100,2); //round to 2 digits decimal
			}

			$charge_amount -= $payment_calculated_discount;
		}

		//calculate tax if enabled
		if(!empty($payment_enable_tax) && !empty($payment_tax_rate)){
			$payment_tax_amount = ($payment_tax_rate / 100) * $charge_amount;
			$payment_tax_amount = round($payment_tax_amount); //we need to round it without decimal, since stripe only accept charges in cents, without any decimal 
			$charge_amount += $payment_tax_amount;
		}

		$customer_name = trim($payment_data['first_name'].' '.$payment_data['last_name']);
		
		$billing_country_code  = mf_get_country_code($payment_data['billing_country']);
		if(empty($billing_country_code)){
			$billing_country_code = null;
		}

		$shipping_country_code = mf_get_country_code($payment_data['shipping_country']);
		if(empty($shipping_country_code)){
			$shipping_country_code = null;
		}

		if(!empty($payment_enable_recurring)){ //this is recurring payments
			
			//Place the recurring code here once Braintree API support creation of subscription plan

		}else{ //this is non recurring payment
			
			$order_id = $form_id.'-'.$payment_record_id;

			//charge the customer
			$charge_amount_dollar =	sprintf("%.2f",round($charge_amount / 100, 2)); //round to exactly 2 digit decimal

			$gateway = new Braintree\Gateway([
						    'environment' => $api_environment,
						    'merchantId' => $api_merchant_id,
						    'publicKey' => $api_public_key,
						    'privateKey' => $api_private_key
						]);

			if(!empty($api_merchant_account_id)){
				$api_result = Braintree_Transaction::sale(array(
														  'amount' => $charge_amount_dollar,
														  'orderId' => $order_id,
														  'merchantAccountId' => $api_merchant_account_id,
														  'creditCard' => array(
														    'number' => $payment_data['card_number'],
														    'expirationMonth' => $payment_data['card_exp_month'],
														    'expirationYear' => $payment_data['card_exp_year'],
														    'cardholderName' => $customer_name,
														    'cvv' => $payment_data['card_cvc']
														  ),
														  'customer' => array(
														    'firstName' => $payment_data['first_name'],
														    'lastName' => $payment_data['last_name']
														  ),
														  'billing' => array(
														    'firstName' => $payment_data['first_name'],
														    'lastName' => $payment_data['last_name'],
														    'streetAddress' => $payment_data['billing_street'],
														    'locality' => $payment_data['billing_city'],
														    'region' => $payment_data['billing_state'],
														    'postalCode' => $payment_data['billing_zipcode'],
														    'countryCodeAlpha2' => $billing_country_code
														  ),
														  'shipping' => array(
														    'firstName' => $payment_data['first_name'],
														    'lastName' => $payment_data['last_name'],
														    'streetAddress' => $payment_data['shipping_street'],
														    'locality' => $payment_data['shipping_city'],
														    'region' => $payment_data['shipping_state'],
														    'postalCode' => $payment_data['shipping_zipcode'],
														    'countryCodeAlpha2' => $shipping_country_code
														  ),
														  'options' => array(
														    'submitForSettlement' => true,
														    'storeInVaultOnSuccess' => true,
														    'addBillingAddressToPaymentMethod' => true,
														    'storeShippingAddressInVault' => true
														  )
														));
			}else{
				$api_result = $gateway->transaction()->sale(array(
														  'amount' => $charge_amount_dollar,
														  'orderId' => $order_id,
														  'creditCard' => array(
														    'number' => $payment_data['card_number'],
														    'expirationMonth' => $payment_data['card_exp_month'],
														    'expirationYear' => $payment_data['card_exp_year'],
														    'cardholderName' => $customer_name,
														    'cvv' => $payment_data['card_cvc']
														  ),
														  'customer' => array(
														    'firstName' => $payment_data['first_name'],
														    'lastName' => $payment_data['last_name']
														  ),
														  'billing' => array(
														    'firstName' => $payment_data['first_name'],
														    'lastName' => $payment_data['last_name'],
														    'streetAddress' => $payment_data['billing_street'],
														    'locality' => $payment_data['billing_city'],
														    'region' => $payment_data['billing_state'],
														    'postalCode' => $payment_data['billing_zipcode'],
														    'countryCodeAlpha2' => $billing_country_code
														  ),
														  'shipping' => array(
														    'firstName' => $payment_data['first_name'],
														    'lastName' => $payment_data['last_name'],
														    'streetAddress' => $payment_data['shipping_street'],
														    'locality' => $payment_data['shipping_city'],
														    'region' => $payment_data['shipping_state'],
														    'postalCode' => $payment_data['shipping_zipcode'],
														    'countryCodeAlpha2' => $shipping_country_code
														  ),
														  'options' => array(
														    'submitForSettlement' => true,
														    'storeInVaultOnSuccess' => true,
														    'addBillingAddressToPaymentMethod' => true,
														    'storeShippingAddressInVault' => true
														  )
														));
			}

			if($api_result->success){
				$payment_success = true;
				
				$payment_data['payment_id'] 		= $api_result->transaction->id;
				$payment_data['payment_date'] 		= date("Y-m-d H:i:s");
				$payment_data['payment_amount'] 	= $api_result->transaction->amount;
				$payment_data['payment_currency'] 	= $api_result->transaction->currencyIsoCode;
				$payment_data['payment_test_mode'] 	= $payment_braintree_enable_test_mode;
			}else{
				$payment_message = $api_result->message;
			}				
			

		} //end of non recurring payment

	}else{
		$payment_message = "Error. Braintree API is not enabled for this form.";
	}


	if($payment_success === true){
		$payment_status = "ok";
		$_SESSION['mf_payment_completed'][$form_id] = true;

		//revoke access to form payment page
		unset($_SESSION['mf_form_payment_access'][$form_id]);

		//make sure to delete empty record from ap_form_payments table related with current entry_id
		//empty record is possible when the user manually changed the payment status previously
		$query = "DELETE FROM `".MF_TABLE_PREFIX."form_payments` WHERE form_id = ? AND record_id = ? and payment_id IS NULL";
		$params = array($form_id,$payment_record_id);
		mf_do_query($query,$params,$dbh);

		//insert into ap_form_payments table
		$payment_data['payment_fullname'] = trim($payment_data['first_name'].' '.$payment_data['last_name']);
		$payment_data['form_id'] 		  = $form_id;
		$payment_data['record_id'] 		  = $payment_record_id;
		$payment_data['date_created']	  = $payment_data['payment_date'];
		$payment_data['status']			  = 1;
		$payment_data['payment_status']   = 'paid'; 

		$query = "INSERT INTO `".MF_TABLE_PREFIX."form_payments`(
								`form_id`, 
								`record_id`, 
								`payment_id`, 
								`date_created`, 
								`payment_date`, 
								`payment_status`, 
								`payment_fullname`, 
								`payment_amount`, 
								`payment_currency`, 
								`payment_test_mode`,
								`payment_merchant_type`, 
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
								`shipping_country`) 
						VALUES (
								:form_id, 
								:record_id, 
								:payment_id, 
								:date_created, 
								:payment_date, 
								:payment_status, 
								:payment_fullname, 
								:payment_amount, 
								:payment_currency, 
								:payment_test_mode,
								:payment_merchant_type, 
								:status, 
								:billing_street, 
								:billing_city, 
								:billing_state, 
								:billing_zipcode, 
								:billing_country, 
								:same_shipping_address, 
								:shipping_street, 
								:shipping_city, 
								:shipping_state, 
								:shipping_zipcode, 
								:shipping_country)";		
		
		$params = array();
		$params[':form_id'] 		  	= $payment_data['form_id'];
		$params[':record_id'] 			= $payment_data['record_id'];
		$params[':payment_id'] 			= $payment_data['payment_id'];
		$params[':date_created'] 		= $payment_data['date_created'];
		$params[':payment_date'] 		= $payment_data['payment_date'];
		$params[':payment_status'] 		= $payment_data['payment_status'];
		$params[':payment_fullname']  	= $payment_data['payment_fullname'];
		$params[':payment_amount'] 	  	= $payment_data['payment_amount'];
		$params[':payment_currency']  	= $payment_data['payment_currency'];
		$params[':payment_test_mode'] 	= $payment_data['payment_test_mode'];
		$params[':payment_merchant_type'] = 'braintree';
		$params[':status'] 			  	= $payment_data['status'];
		$params[':billing_street'] 		= $payment_data['billing_street'];
		$params[':billing_city']		= $payment_data['billing_city'];
		$params[':billing_state'] 		= $payment_data['billing_state'];
		$params[':billing_zipcode'] 	= $payment_data['billing_zipcode'];
		$params[':billing_country'] 	= $payment_data['billing_country'];
		$params[':same_shipping_address'] = $payment_data['same_shipping_address'];
		$params[':shipping_street'] 	= $payment_data['shipping_street'];
		$params[':shipping_city'] 		= $payment_data['shipping_city'];
		$params[':shipping_state'] 		= $payment_data['shipping_state'];
		$params[':shipping_zipcode'] 	= $payment_data['shipping_zipcode'];
		$params[':shipping_country'] 	= $payment_data['shipping_country'];

		mf_do_query($query,$params,$dbh);
	}else{
		$payment_status = "error";
	}
	
	$response_data = new stdClass();
	$response_data->status    	= $payment_status;
	$response_data->form_id 	= $form_id;
	$response_data->message 	= $payment_message;
	
	$response_json = json_encode($response_data);
	echo $response_json;


?>
