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
	require('lib/stripe/init.php');
	
	$form_id 			= (int) trim($_POST['form_id']);
	$payment_record_id 	= (int) trim($_POST['record_id']);

	$payment_method_id 	= isset($_POST['payment_method_id']) ? trim($_POST['payment_method_id']) : false; 
	$payment_intent_id 	= isset($_POST['payment_intent_id']) ? trim($_POST['payment_intent_id']) : false; //this parameter received from 3D Secure page (bank)
	
	$payment_data 		= mf_sanitize($_POST['payment_properties'] ?? array());

	$payment_success  = false;
	$requires_action  = false;
	$payment_message = '';

	if(!empty($payment_intent_id)){
		//if there is payment_intent_id value, this is a confirmation process
		$is_confirming_paymentintent = true;
	}else{
		$is_confirming_paymentintent = false;
	}
	
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
					payment_stripe_live_secret_key,
					payment_stripe_test_secret_key,
					payment_stripe_enable_test_mode,
					payment_stripe_enable_receipt,
					payment_stripe_receipt_element_id,
					payment_enable_recurring,
					payment_recurring_cycle,
					payment_recurring_unit,
					payment_enable_trial,
					payment_trial_period,
					payment_trial_unit,
					payment_trial_amount,
					payment_enable_setupfee,
					payment_setupfee_amount,
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
	
	if(!empty($row)){	
		$form_review  	 = (int) $row['form_review'];
		$form_page_total = (int) $row['form_page_total'];
			
		$payment_enable_merchant 	 = (int) $row['payment_enable_merchant'];

		$payment_enable_tax 		 = (int) $row['payment_enable_tax'];
		$payment_tax_rate 			 = (float) $row['payment_tax_rate'];

		$payment_currency 	   		 = strtolower($row['payment_currency']);
		$payment_price_type 	     = $row['payment_price_type'];
		$payment_price_amount    	 = $row['payment_price_amount'];
		$payment_ask_billing 	 	 = (int) $row['payment_ask_billing'];
		$payment_ask_shipping 	 	 = (int) $row['payment_ask_shipping'];
		$payment_merchant_type		 = $row['payment_merchant_type'];
		
		$payment_stripe_enable_test_mode 	= (int) $row['payment_stripe_enable_test_mode'];
		$payment_stripe_live_secret_key	 	= trim($row['payment_stripe_live_secret_key']);
		$payment_stripe_test_secret_key	 	= trim($row['payment_stripe_test_secret_key']);
		$payment_stripe_enable_receipt  	= (int) $row['payment_stripe_enable_receipt'];
		$payment_stripe_receipt_element_id  = (int) $row['payment_stripe_receipt_element_id'];
		
		$payment_price_type   = $row['payment_price_type'];
		$payment_price_amount = (float) $row['payment_price_amount'];
		$payment_price_name   = $row['payment_price_name'];

		$payment_enable_recurring = (int) $row['payment_enable_recurring'];
		$payment_recurring_cycle  = (int) $row['payment_recurring_cycle'];
		$payment_recurring_unit   = $row['payment_recurring_unit'];

		$payment_enable_trial = (int) $row['payment_enable_trial'];
		$payment_trial_period = (int) $row['payment_trial_period'];
		$payment_trial_unit   = $row['payment_trial_unit'];
		$payment_trial_amount = (float) $row['payment_trial_amount'];

		$payment_enable_setupfee = (int) $row['payment_enable_setupfee'];
		$payment_setupfee_amount = (float) $row['payment_setupfee_amount'];

		$payment_enable_discount 	 = (int) $row['payment_enable_discount'];
		$payment_discount_type 	 	 = $row['payment_discount_type'];
		$payment_discount_amount 	 = (float) $row['payment_discount_amount'];
		$payment_discount_element_id = (int) $row['payment_discount_element_id'];

		$payment_delay_notifications = (int) $row['payment_delay_notifications'];
	}

	if(!empty($payment_enable_merchant) && $payment_merchant_type == 'stripe'){		
		if(!empty($payment_stripe_enable_test_mode)){
			$stripe_secret_key = $payment_stripe_test_secret_key;
		}else{
			$stripe_secret_key = $payment_stripe_live_secret_key;
		}

		//calculate payment amount
		if($payment_price_type == 'fixed'){ 
				
			$charge_amount = $payment_price_amount * 100; //charge in cents
		}else if($payment_price_type == 'variable'){ 
				
			$charge_amount = (double) mf_get_payment_total($dbh,$form_id,$payment_record_id,0,'live');
			$charge_amount = $charge_amount * 100;
		}

		//if the discount element for the current entry_id having any value, we can be certain that the discount code has been validated and applicable
		$is_discount_applicable = false;
		if(!empty($payment_enable_discount)){
			$query = "select element_{$payment_discount_element_id} coupon_element from ".MF_TABLE_PREFIX."form_{$form_id} where `id` = ? and `status` = 1";
			$params = array($payment_record_id);
			
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);
			
			if(!empty($row['coupon_element'])){
				$is_discount_applicable = true;
			}
		}

		//calculate discount if applicable
		if($is_discount_applicable){
			$payment_calculated_discount = 0;

			if($payment_discount_type == 'percent_off'){
				//the discount is percentage
				$payment_calculated_discount = ($payment_discount_amount / 100) * $charge_amount;
				$payment_calculated_discount = round($payment_calculated_discount); //we need to round it without decimal, since stripe only accept charges in cents, without any decimal 
			}else{
				//the discount is fixed amount
				//multiple it with 100 to charge in cents
				$payment_calculated_discount = round($payment_discount_amount * 100); //we need to round it without decimal, since stripe only accept charges in cents, without any decimal 
			}

			$charge_amount -= $payment_calculated_discount;
		}

		//calculate tax if enabled
		if(!empty($payment_enable_tax) && !empty($payment_tax_rate)){
			$payment_tax_amount = ($payment_tax_rate / 100) * $charge_amount;
			$payment_tax_amount = round($payment_tax_amount); //we need to round it without decimal, since stripe only accept charges in cents, without any decimal 
			$charge_amount += $payment_tax_amount;
		}

		//if stripe email receipt being enabled, get the customer email address
		$customer_email = null;
		if(!empty($payment_stripe_enable_receipt) && !empty($payment_stripe_receipt_element_id)){
			$query = "select `element_{$payment_stripe_receipt_element_id}` customer_email from ".MF_TABLE_PREFIX."form_{$form_id} where `id` = ? and `status` = 1";
			$params = array($payment_record_id);
			
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);

			if(!empty($row['customer_email'])){
				$customer_email = $row['customer_email'];
			}
		}

		//set private key
		\Stripe\Stripe::setApiKey($stripe_secret_key);

		//create Customer object
		//this operation shouldn't be executed when confirming payment intent
		if(!$is_confirming_paymentintent){
			$customer_desc = "Customer for (Form #{$form_id} - Entry #{$payment_record_id})";
			$customer_name = trim($payment_data['first_name'].' '.$payment_data['last_name']);
			if(!empty($customer_name)){
				$customer_desc .= " - {$customer_name}";
			}
			
			
			try {
				if(!empty($payment_enable_recurring) && !empty($payment_enable_setupfee) && !empty($payment_setupfee_amount)){
					//for recurring payments, when setup fee is enabled
					//we need to pass the balance argument
					$payment_setupfee_amount = $payment_setupfee_amount * 100; //charge in cents

					$customer_obj = \Stripe\Customer::create(array(
										"balance" => $payment_setupfee_amount,
								  		"description" => $customer_desc,
								  		"email" => $customer_email,
								  		"name" => $customer_name)
									);
				}else{
					$customer_obj = \Stripe\Customer::create(array(
								  		"description" => $customer_desc,
								  		"email" => $customer_email,
								  		"name" => $customer_name)
									);
				}

				//attach payment method to the customer
				$payment_method = \Stripe\PaymentMethod::retrieve($payment_method_id);
				$payment_method->attach(['customer' => $customer_obj->id]);
			}catch(\Stripe\Error\Card $e) {
			 	//Since it's a decline, \Stripe\Error\Card will be caught
			  	$payment_message = $e->getMessage();
			}catch (\Stripe\Error\RateLimit $e) {
			  	//Too many requests made to the API too quickly
				$payment_message = $e->getMessage();
			}catch(\Stripe\Error\InvalidRequest $e) {
			  	//Invalid parameters were supplied to Stripe's API
				$payment_message = $e->getMessage();
			}catch(\Stripe\Error\Authentication $e) {
			  	//Authentication with Stripe's API failed
			  	//(maybe you changed API keys recently)
				$payment_message = $e->getMessage();
			}catch (\Stripe\Error\ApiConnection $e) {
			  	//Network communication with Stripe failed
				$payment_message = $e->getMessage();
			}catch (\Stripe\Error\Base $e) {
			  	//Display a very generic error to the user
			  	$payment_message = $e->getMessage();
			}catch (Exception $e) {
			  	//Something else happened, completely unrelated to Stripe
				$payment_message = $e->getMessage();
			}
		}

		if(empty($payment_message)){ //if no error with the card, continue creating charges
			if(!empty($payment_enable_recurring)){ //this is recurring payments
				
				$trial_period_days = 0;
				if(!empty($payment_enable_trial)){
					if($payment_trial_unit == 'day'){
						$trial_period_days = $payment_trial_period;
					}else if($payment_trial_unit == 'week'){
						$trial_period_days = $payment_trial_period * 7;
					}else if($payment_trial_unit == 'month'){
						$trial_period_days = $payment_trial_period * 30;
					}else if($payment_trial_unit == 'year'){
						$trial_period_days = $payment_trial_period * 365;
					}
				}

				//prepare default plan description
				$plan_desc = "Plan for (Form #{$form_id} - Entry #{$payment_record_id})";
				if(!empty($customer_name)){
					$plan_desc .= " - {$customer_name}";
				}

				//if stripe email receipt enabled, use the price name as the plan description
				//since this will be displayed within the email receipt
				if(!empty($payment_price_name) && !empty($payment_stripe_enable_receipt)){
					$plan_desc = $payment_price_name;
				}


				//if paid trial enabled, create an invoice item
				if(!empty($payment_enable_trial) && !empty($payment_trial_amount)){
					
					$trial_charge_amount = $payment_trial_amount * 100; //charge in cents
					
					//prepare default trial description
					$trial_charge_desc = "Trial Period Payment for (Form #{$form_id} - Entry #{$payment_record_id})";
					if(!empty($customer_name)){
						$trial_charge_desc .= " - {$customer_name}";
					}

					//if stripe email receipt enabled, use the price name as the plan description
					//since this will be displayed within the email receipt
					if(!empty($payment_price_name) && !empty($payment_stripe_enable_receipt)){
						$trial_charge_desc = $payment_price_name.' (Trial Period)';
					}

					
					\Stripe\InvoiceItem::create(array( 
													"customer" => $customer_obj, 
													"amount" => $trial_charge_amount, 
													"currency" => $payment_currency, 
													"description" => $trial_charge_desc) 
												);
				}

				//create subscription plan
				try{
					if(!$is_confirming_paymentintent){
						$plan = \Stripe\Plan::create(array(
												  "amount" => $charge_amount,
												  "interval" => $payment_recurring_unit,
												  "interval_count" => $payment_recurring_cycle,
												  "product" => array(
															    "name" => $plan_desc
															  ),
												  "currency" => $payment_currency)
												);

						//subscribe the customer to the plan
						$charge_result = \Stripe\Subscription::create([
																    'customer' => $customer_obj->id,
																    'default_payment_method' => $payment_method_id,
																    'trial_period_days' => $trial_period_days,
																    'items' => [['plan' => $plan->id]],
																    'enable_incomplete_payments' => true,
																	'trial_period_days' => $trial_period_days,
																	'expand' => ['latest_invoice.payment_intent']
																]);
					}else{
						//if this is simply confirming payment intent

						//specifically for recurring payment, stripe only allow automatic confirmation
						//thus there's no $intent->confirm(); required
						//we just get the status of the intent here
						$intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
				      	
				      	$charge_result = $intent;
					}

					if($charge_result->status == 'incomplete' && $charge_result->latest_invoice->payment_intent->status == 'requires_action') {
					    //tell the client to handle the action
					    $requires_action = true;
						$payment_intent_client_secret = $charge_result->latest_invoice->payment_intent->client_secret;
					} 
				}catch(\Stripe\Error\Card $e) {
				 	//Since it's a decline, \Stripe\Error\Card will be caught
				  	$payment_message = $e->getMessage();
				}catch (\Stripe\Error\RateLimit $e) {
				  	//Too many requests made to the API too quickly
					$payment_message = $e->getMessage();
				}catch(\Stripe\Error\InvalidRequest $e) {
				  	//Invalid parameters were supplied to Stripe's API
					$payment_message = $e->getMessage();
				}catch(\Stripe\Error\Authentication $e) {
				  	//Authentication with Stripe's API failed
				  	//(maybe you changed API keys recently)
					$payment_message = $e->getMessage();
				}catch (\Stripe\Error\ApiConnection $e) {
				  	//Network communication with Stripe failed
					$payment_message = $e->getMessage();
				}catch (\Stripe\Error\Base $e) {
				  	//Display a very generic error to the user
				  	$payment_message = $e->getMessage();
				}catch (Exception $e) {
				  	//Something else happened, completely unrelated to Stripe
					$payment_message = $e->getMessage();
				}

				if(!empty($charge_result->status) && in_array($charge_result->status, array('active','trialing','succeeded'))){
					$payment_success = true;

					$payment_data['payment_id'] 		= $charge_result->id;
					$payment_data['payment_date'] 		= date("Y-m-d H:i:s");
					$payment_data['payment_currency'] 	= $payment_currency;
					$payment_data['payment_amount'] 	= $charge_result->latest_invoice->amount_paid / 100;
					

					if(!empty($payment_stripe_enable_test_mode)){
						$payment_data['payment_test_mode'] = 1;
					}else{
						$payment_data['payment_test_mode'] = 0;
					}
				}
			}else{ //this is non recurring payment
				
				//prepare default charge description
				$charge_desc = "Payment for (Form #{$form_id} - Entry #{$payment_record_id})";
				if(!empty($customer_name)){
					$charge_desc .= " - {$customer_name}";
				}

				//if stripe email receipt enabled, use the price name as the charge description
				//since this will be displayed within the email receipt
				if(!empty($payment_price_name) && !empty($payment_stripe_enable_receipt)){
					$charge_desc = $payment_price_name;
				}

				//charge the customer
				try {
					if(!$is_confirming_paymentintent){
						$charge_result = \Stripe\PaymentIntent::create([
																'payment_method' => $payment_method_id,
															    'amount' => $charge_amount,
															    'currency' => $payment_currency,
															    'confirmation_method' => 'manual',
															    'confirm' => true,
															    'customer' => $customer_obj->id,
																'description' => $charge_desc,
																'receipt_email' => $customer_email
															]);
					}else{
						//if this is simply confirming payment intent
						$intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
				      	$intent->confirm();

				      	$charge_result = $intent;
					}

					if($charge_result->status == 'requires_action' && $charge_result->next_action->type == 'use_stripe_sdk') {
					    //tell the client to handle the action
					    $requires_action = true;
						$payment_intent_client_secret = $charge_result->client_secret;
					} 
				}catch(\Stripe\Error\Card $e) {
				 	//Since it's a decline, \Stripe\Error\Card will be caught
				  	$payment_message = $e->getMessage();
				}catch (\Stripe\Error\RateLimit $e) {
				  	//Too many requests made to the API too quickly
					$payment_message = $e->getMessage();
				}catch(\Stripe\Error\InvalidRequest $e) {
				  	//Invalid parameters were supplied to Stripe's API
					$payment_message = $e->getMessage();
				}catch(\Stripe\Error\Authentication $e) {
				  	//Authentication with Stripe's API failed
				  	//(maybe you changed API keys recently)
					$payment_message = $e->getMessage();
				}catch (\Stripe\Error\ApiConnection $e) {
				  	//Network communication with Stripe failed
					$payment_message = $e->getMessage();
				}catch (\Stripe\Error\Base $e) {
				  	//Display a very generic error to the user
				  	$payment_message = $e->getMessage();
				}catch (Exception $e) {
				  	//Something else happened, completely unrelated to Stripe
					$payment_message = $e->getMessage();
				}

				if($charge_result->status == 'succeeded'){
					$payment_success = true;

					$payment_data['payment_id'] 	= $charge_result->id;
					$payment_data['payment_date'] 	= date("Y-m-d H:i:s");
					$payment_data['payment_amount'] = $charge_result->amount / 100;
					$payment_data['payment_currency'] = $charge_result->currency;

					if($charge_result->livemode === true){
						$payment_data['payment_test_mode'] = 0;
					}else{
						$payment_data['payment_test_mode'] = 1;
					}
					
				}
			}
		}

	}else{
		$payment_message = "Error. Stripe is not enabled for this form.";
	}

	$response_data = new stdClass();

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

		if(!$is_confirming_paymentintent){
			//insert into ap_form_payments table
			$payment_data['payment_fullname'] = trim($payment_data['first_name'].' '.$payment_data['last_name']);
			$payment_data['form_id'] 		  = $form_id;
			$payment_data['record_id'] 		  = $payment_record_id;
			$payment_data['date_created']	  = date("Y-m-d H:i:s");
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
			$params[':payment_merchant_type'] = 'stripe';
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
			//this is payment intent confirmation, we only need to update the existing record within the ap_form_payments table
			$query = "UPDATE `".MF_TABLE_PREFIX."form_payments` 
						 SET 
						 	payment_id=?,
						 	payment_date=?,
						 	payment_amount=?,
						 	payment_currency=?,
						 	payment_status='paid'   
					  WHERE 
					  		form_id = ? AND record_id = ? AND payment_id='-'";
			$params = array($charge_result->id,
							date("Y-m-d H:i:s"),
							$charge_result->amount / 100,
							$charge_result->currency,
							$form_id,
							$payment_record_id);
			mf_do_query($query,$params,$dbh);
		}
	}else{
		if($requires_action === true){
			//client need to confirm their payment using SCA 
			$payment_status = "requires_action";
			$response_data->payment_intent_client_secret = $payment_intent_client_secret;

			//save payment data into ap_form_payments and set the payment status to 'unpaid' 

			//make sure to delete empty record from ap_form_payments table related with current entry_id
			//empty record is possible when the user manually changed the payment status previously
			$query = "DELETE FROM `".MF_TABLE_PREFIX."form_payments` WHERE form_id = ? AND record_id = ? and payment_id IS NULL";
			$params = array($form_id,$payment_record_id);
			mf_do_query($query,$params,$dbh);

			//insert into ap_form_payments table
			$payment_data['payment_fullname'] = trim($payment_data['first_name'].' '.$payment_data['last_name']);
			$payment_data['form_id'] 		  = $form_id;
			$payment_data['record_id'] 		  = $payment_record_id;
			$payment_data['date_created']	  = date("Y-m-d H:i:s");
			$payment_data['status']			  = 1;
			$payment_data['payment_status']   = 'unpaid'; 

			$payment_data['payment_id'] 	  = '-'; //put any value, not NULL, so that it won't get deleted by the deletion query above
			$payment_data['payment_date'] 	  = date("Y-m-d H:i:s");
			$payment_data['payment_amount']   = $charge_amount / 100;
			$payment_data['payment_currency'] = $payment_currency;

			if(!empty($payment_stripe_enable_test_mode)){
				$payment_data['payment_test_mode'] = 1;
			}else{
				$payment_data['payment_test_mode'] = 0;
			} 

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
			$params[':payment_merchant_type'] = 'stripe';
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
	}
	
	$response_data->status    	= $payment_status;
	$response_data->form_id 	= $form_id;
	$response_data->message 	= $payment_message;
	
	$response_json = json_encode($response_data);
	echo $response_json;
?>
