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
	require('lib/authnetxml/AuthnetXML.class.php');
	
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
					payment_authorizenet_live_apiloginid,
					payment_authorizenet_live_transkey,
					payment_authorizenet_test_apiloginid,
					payment_authorizenet_test_transkey,
					payment_authorizenet_enable_test_mode,
					payment_authorizenet_save_cc_data,
					payment_authorizenet_enable_email,
					payment_authorizenet_email_element_id,
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

	$payment_currency 	   		 = strtolower($row['payment_currency']);
	$payment_price_type 	     = $row['payment_price_type'];
	$payment_price_amount    	 = $row['payment_price_amount'];
	$payment_ask_billing 	 	 = (int) $row['payment_ask_billing'];
	$payment_ask_shipping 	 	 = (int) $row['payment_ask_shipping'];
	$payment_merchant_type		 = $row['payment_merchant_type'];
	
	$payment_authorizenet_live_apiloginid   = trim($row['payment_authorizenet_live_apiloginid']);
	$payment_authorizenet_live_transkey   	= trim($row['payment_authorizenet_live_transkey']);
	$payment_authorizenet_test_apiloginid   = trim($row['payment_authorizenet_test_apiloginid']);
	$payment_authorizenet_test_transkey   	= trim($row['payment_authorizenet_test_transkey']);
	$payment_authorizenet_enable_test_mode  = (int) $row['payment_authorizenet_enable_test_mode'];
	$payment_authorizenet_save_cc_data  	= (int) $row['payment_authorizenet_save_cc_data'];
	$payment_authorizenet_enable_email  	= (int) $row['payment_authorizenet_enable_email'];
	$payment_authorizenet_email_element_id  = (int) $row['payment_authorizenet_email_element_id'];
	
	$payment_price_type   = $row['payment_price_type'];
	$payment_price_amount = (float) $row['payment_price_amount'];
	$payment_price_name   = $row['payment_price_name'];

	$payment_enable_recurring = (int) $row['payment_enable_recurring'];
	$payment_recurring_cycle  = (int) $row['payment_recurring_cycle'];
	$payment_recurring_unit   = $row['payment_recurring_unit'].'s'; //authorize.net need 's' suffix for the recurring unit

	//authorize.net only support 'days' and 'months' for the recurring unit, so we need to convert other unit
	if($payment_recurring_unit == 'weeks'){
		$payment_recurring_unit  = 'days';
		$payment_recurring_cycle *= 7; 
	}else if($payment_recurring_unit == 'years'){
		$payment_recurring_unit = 'months';
		$payment_recurring_cycle *= 12;
	}

	$payment_enable_trial = (int) $row['payment_enable_trial'];
	$payment_trial_period = (int) $row['payment_trial_period'];
	$payment_trial_unit   = $row['payment_trial_unit'];
	$payment_trial_amount = (float) $row['payment_trial_amount'];

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

	if(!empty($payment_enable_merchant) && $payment_merchant_type == 'authorizenet'){
		
		if(!empty($payment_authorizenet_enable_test_mode)){
			$api_login_id    = $payment_authorizenet_test_apiloginid;
			$transaction_key = $payment_authorizenet_test_transkey;
			$server_mode	 = AuthnetXML::USE_DEVELOPMENT_SERVER;
		}else{
			$api_login_id    = $payment_authorizenet_live_apiloginid;
			$transaction_key = $payment_authorizenet_live_transkey;
			$server_mode	 = AuthnetXML::USE_PRODUCTION_SERVER;
		}

		//if 'send customer email to authorize.net' being enabled, get the customer email address
		$customer_email = '';
		if(!empty($payment_authorizenet_enable_email) && !empty($payment_authorizenet_email_element_id)){
			$query = "select `element_{$payment_authorizenet_email_element_id}` customer_email from ".MF_TABLE_PREFIX."form_{$form_id} where `id` = ? and `status` = 1";
			$params = array($payment_record_id);
			
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);

			if(!empty($row['customer_email'])){
				$customer_email = $row['customer_email'];
			}
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

			
		if(!empty($payment_enable_recurring)){ //this is recurring payments
			
			$plan_start_date = date('Y-m-d');  //by default, set the billing start date to today
			$create_subscription = true;

			$credit_card_data = array();
			$credit_card_data['cardNumber'] = $payment_data['card_number'];
			$credit_card_data['expirationDate'] = $payment_data['card_exp_month'].$payment_data['card_exp_year'];
			
			if(!empty($payment_data['card_cvc'])){
				$credit_card_data['cardCode'] = $payment_data['card_cvc'];
			}

			//if trial period enabled, we need to recalculate the plan start date and charge the trial amount (if any)
			if(!empty($payment_enable_trial)){

				//recalculate the subscription start date
				$trial_period_days = 0;
				
				if($payment_trial_unit == 'day'){
					$trial_period_days = $payment_trial_period;
				}else if($payment_trial_unit == 'week'){
					$trial_period_days = $payment_trial_period * 7;
				}else if($payment_trial_unit == 'month'){
					$trial_period_days = $payment_trial_period * 30;
				}else if($payment_trial_unit == 'year'){
					$trial_period_days = $payment_trial_period * 365;
				}
				
				$plan_start_date = date('Y-m-d',strtotime($plan_start_date) + (24*3600*$trial_period_days));

				//start charge for the trial amount---
				if(!empty($payment_trial_amount)){
					$trial_charge_desc = "Trial Period Payment for (Form #{$form_id} - Entry #{$payment_record_id})";

					//charge the customer
					$authorizenet = new AuthnetXML($api_login_id, $transaction_key, $server_mode);
				    $authorizenet->createTransactionRequest(array(
				        'transactionRequest' => array(
				            'transactionType' => 'authCaptureTransaction',
				            'amount' => $payment_trial_amount,
				            'payment' => array(
				                'creditCard' => $credit_card_data,
				            ),
				            'order' => array(
				                'invoiceNumber' => $form_id.'-'.$payment_record_id,
				                'description' => $trial_charge_desc,
				            ),
				            'customer' => array(
				                'id' => $form_id.'-'.$payment_record_id,
				                'email' => $customer_email,
				            ),
				            'billTo' => array(
				               'firstName' => $payment_data['first_name'],
				               'lastName' => $payment_data['last_name'],
				               'address' => $payment_data['billing_street'],
				               'city' => $payment_data['billing_city'],
				               'state' => $payment_data['billing_state'],
				               'zip' => $payment_data['billing_zipcode'],
				               'country' => $payment_data['billing_country'],
				            ),
				            'shipTo' => array(
				               'firstName' => $payment_data['first_name'],
				               'lastName' => $payment_data['last_name'],
				               'address' => $payment_data['shipping_street'],
				               'city' => $payment_data['shipping_city'],
				               'state' => $payment_data['shipping_state'],
				               'zip' => $payment_data['shipping_zipcode'],
				               'country' => $payment_data['shipping_country'],
				            ),
				            'customerIP' => $customer_ip,
				            'transactionSettings' => array(
				                'setting' => array(
				                    0 => array(
				                        'settingName' => 'emailCustomer',
				                        'settingValue' => 'false'
				                    )
				                )
				            ),
				            
				        ),
				    ));
					
					if($authorizenet->isError()){
						$create_subscription = false;

						if(!empty($authorizenet->transactionResponse->errors->error->errorText)){
							$payment_message = ''.$authorizenet->transactionResponse->errors->error->errorText;
						}else if(!empty($authorizenet->messages->message->text)){
							$payment_message = ''.$authorizenet->messages->message->text;
						}
					}
				}
				//end charge of trial amount --
			}

			//create the subscription and charge the customer
			if($create_subscription === true){
				$plan_desc = "Plan for (Form #{$form_id} - Entry #{$payment_record_id})";
				$plan_desc = substr($plan_desc, 0, 50); //max plan desc is 50 characters

				$charge_amount_dollar = $charge_amount / 100;

				$authorizenet = new AuthnetXML($api_login_id, $transaction_key, $server_mode);
				$authorizenet->ARBCreateSubscriptionRequest(array(
			        'subscription' => array(
			            'name' => $plan_desc,
			            'paymentSchedule' => array(
			                'interval' => array(
			                    'length' => $payment_recurring_cycle,
			                    'unit' => $payment_recurring_unit
			                ),
			                'startDate' => $plan_start_date,
			                'totalOccurrences' => '9999'
			            ),
			            'amount' => $charge_amount_dollar,
			            'payment' => array(
			                'creditCard' => $credit_card_data,
			            ),
			            'billTo' => array(
			               'firstName' => $payment_data['first_name'],
			               'lastName' => $payment_data['last_name'],
			               'address' => $payment_data['billing_street'],
			               'city' => $payment_data['billing_city'],
			               'state' => $payment_data['billing_state'],
			               'zip' => $payment_data['billing_zipcode'],
			               'country' => $payment_data['billing_country'],
			            ),
			            'shipTo' => array(
			               'firstName' => $payment_data['first_name'],
			               'lastName' => $payment_data['last_name'],
			               'address' => $payment_data['shipping_street'],
			               'city' => $payment_data['shipping_city'],
			               'state' => $payment_data['shipping_state'],
			               'zip' => $payment_data['shipping_zipcode'],
			               'country' => $payment_data['shipping_country'],
			            )
			        )
			    ));

				if($authorizenet->isSuccessful()){
					$payment_success = true;

					$payment_data['payment_id'] 		= 'Subscription #'.$authorizenet->subscriptionId;
					$payment_data['payment_date'] 		= date("Y-m-d H:i:s");
					$payment_data['payment_amount'] 	= $charge_amount_dollar;
					$payment_data['payment_currency'] 	= $payment_currency;
					$payment_data['payment_test_mode'] 	= $payment_authorizenet_enable_test_mode;
				}else{
					if(!empty($authorizenet->transactionResponse->errors->error->errorText)){
						$payment_message = ''.$authorizenet->transactionResponse->errors->error->errorText;
					}else if(!empty($authorizenet->messages->message->text)){
						$payment_message = ''.$authorizenet->messages->message->text;
					}
				}
			}

		}else{ //this is non recurring payment
			
			$charge_desc = "Payment for (Form #{$form_id} - Entry #{$payment_record_id})";
			if(!empty($customer_name)){
				$charge_desc .= " - {$customer_name}";
			}

			//charge the customer
			$charge_amount_dollar = $charge_amount / 100;

			$credit_card_data = array();
			$credit_card_data['cardNumber'] = $payment_data['card_number'];
			$credit_card_data['expirationDate'] = $payment_data['card_exp_month'].$payment_data['card_exp_year'];
			if(!empty($payment_data['card_cvc'])){
				$credit_card_data['cardCode'] = $payment_data['card_cvc'];
			}

			$authorizenet = new AuthnetXML($api_login_id, $transaction_key, $server_mode);
		    $authorizenet->createTransactionRequest(array(
		        'transactionRequest' => array(
		            'transactionType' => 'authCaptureTransaction',
		            'amount' => $charge_amount_dollar,
		            'payment' => array(
		                'creditCard' => $credit_card_data,
		            ),
		            'order' => array(
		                'invoiceNumber' => $form_id.'-'.$payment_record_id,
		                'description' => $charge_desc,
		            ),
		            'customer' => array(
		                'id' => $form_id.'-'.$payment_record_id,
		                'email' => $customer_email,
		            ),
		            'billTo' => array(
		               'firstName' => $payment_data['first_name'],
		               'lastName' => $payment_data['last_name'],
		               'address' => $payment_data['billing_street'],
		               'city' => $payment_data['billing_city'],
		               'state' => $payment_data['billing_state'],
		               'zip' => $payment_data['billing_zipcode'],
		               'country' => $payment_data['billing_country'],
		            ),
		            'shipTo' => array(
		               'firstName' => $payment_data['first_name'],
		               'lastName' => $payment_data['last_name'],
		               'address' => $payment_data['shipping_street'],
		               'city' => $payment_data['shipping_city'],
		               'state' => $payment_data['shipping_state'],
		               'zip' => $payment_data['shipping_zipcode'],
		               'country' => $payment_data['shipping_country'],
		            ),
		            'customerIP' => $customer_ip,
		            'transactionSettings' => array(
		                'setting' => array(
		                    0 => array(
		                        'settingName' => 'emailCustomer',
		                        'settingValue' => 'false'
		                    )
		                )
		            ),
		            
		        ),
		    ));
			
			if($authorizenet->isSuccessful() && empty($authorizenet->transactionResponse->errors->error->errorText)){
				$payment_success = true;

				$payment_data['payment_id'] 		= $authorizenet->transactionResponse->transId;
				$payment_data['payment_date'] 		= date("Y-m-d H:i:s");
				$payment_data['payment_amount'] 	= $charge_amount_dollar;
				$payment_data['payment_currency'] 	= $payment_currency;
				$payment_data['payment_test_mode'] 	= $payment_authorizenet_enable_test_mode;
			}else{
				if(!empty($authorizenet->transactionResponse->errors->error->errorText)){
					$payment_message = ''.$authorizenet->transactionResponse->errors->error->errorText;
				}else if(!empty($authorizenet->messages->message->text)){
					$payment_message = ''.$authorizenet->messages->message->text;
				}
				
			}

		}

		//if this option is being enabled, create customer and save it into Authorize.net (using CIM API)
		if($payment_success === true && !empty($payment_authorizenet_save_cc_data)){
			//customer info
			$customer_desc = "Customer for (Form #{$form_id} - Entry #{$payment_record_id})";
			$customer_name = trim($payment_data['first_name'].' '.$payment_data['last_name']);
			if(!empty($customer_name)){
				$customer_desc = $customer_name;
			}
			
			$customer_desc = substr($customer_desc, 0, 255);

			$credit_card_data = array();
			$credit_card_data['cardNumber'] = $payment_data['card_number'];
			$credit_card_data['expirationDate'] = $payment_data['card_exp_year'].'-'.$payment_data['card_exp_month'];
			if(!empty($payment_data['card_cvc'])){
				$credit_card_data['cardCode'] = $payment_data['card_cvc'];
			}

			$authorizenet = new AuthnetXML($api_login_id, $transaction_key, $server_mode);
			$authorizenet->createCustomerProfileRequest(array(
		        'profile' => array(
		          'merchantCustomerId' => $form_id.'-'.$payment_record_id,
		          'description' => $customer_desc,
		          'paymentProfiles' => array(
		            'billTo' => array(
		               'firstName' => $payment_data['first_name'],
		               'lastName' => $payment_data['last_name'],
		               'address' => $payment_data['billing_street'],
		               'city' => $payment_data['billing_city'],
		               'state' => $payment_data['billing_state'],
		               'zip' => $payment_data['billing_zipcode'],
		               'country' => $payment_data['billing_country']
		            ),
		            'payment' => array(
		              'creditCard' => $credit_card_data
		            ),
		          ),
		            'shipToList' => array(
		               'firstName' => $payment_data['first_name'],
		               'lastName' => $payment_data['last_name'],
		               'address' => $payment_data['shipping_street'],
		               'city' => $payment_data['shipping_city'],
		               'state' => $payment_data['shipping_state'],
		               'zip' => $payment_data['shipping_zipcode'],
		               'country' => $payment_data['shipping_country']
		            ),
		        ),
		      'validationMode' => 'none'
		  	));
		
			if($authorizenet->isError()){
				echo $authorizenet;
			}

		}
	}else{
		$payment_message = "Error. Authorize.net is not enabled for this form.";
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
		$params[':payment_merchant_type'] = 'authorizenet';
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
