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

	require('includes/entry-functions.php');
	require('includes/post-functions.php');
	require('includes/users-functions.php');
	
	$form_id  		= (int) trim($_POST['form_id']);
	$entry_id 		= (int) trim($_POST['entry_id']);
	$payment_status = strtolower(trim($_POST['payment_status']));
	$csrf_token 	= trim($_POST['csrf_token']);

	if(empty($form_id) || empty($entry_id) || empty($payment_status)){
		die("Invalid parameters.");
	}

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);
		
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_entries permission
		if(empty($user_perms['edit_entries'])){
			die("Access Denied. You don't have permission to edit this entry.");
		}
	}

	//update or insert to ap_form_payments table
	$query = "select count(afp_id) record_exist from ".MF_TABLE_PREFIX."form_payments where form_id = ? and record_id = ? and `status` = 1";
	$params = array($form_id,$entry_id);
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row['record_exist'])){
		//do update to ap_form_payments table
		$query = "update ".MF_TABLE_PREFIX."form_payments set payment_status = ? where form_id = ? and record_id = ? and `status` = 1";
		$params = array($payment_status,$form_id,$entry_id);
		mf_do_query($query,$params,$dbh);
	}else{
		//do insert to ap_form_payments table
		//calculate the payment amount and currencies
		$form_properties = mf_get_form_properties($dbh,
												  $form_id,
												  array('payment_merchant_type',
												  		'payment_price_type',
												  		'payment_price_amount',
												  		'payment_currency',
												  		'payment_enable_tax',
												  		'payment_tax_rate',
												  		'payment_enable_discount',
					 									'payment_discount_type',
					 									'payment_discount_amount',
					 									'payment_discount_element_id'));
		
		$payment_price_amount = (double) $form_properties['payment_price_amount'];
		$payment_merchant_type = $form_properties['payment_merchant_type'];
		$payment_price_type = $form_properties['payment_price_type'];
		$payment_currency = strtolower($form_properties['payment_currency']);

		$payment_enable_tax = (int) $form_properties['payment_enable_tax'];
		$payment_tax_rate 	= (float) $form_properties['payment_tax_rate'];

		$payment_enable_discount = (int) $form_properties['payment_enable_discount'];
		$payment_discount_type 	 = $form_properties['payment_discount_type'];
		$payment_discount_amount = (float) $form_properties['payment_discount_amount'];
		$payment_discount_element_id = (int) $form_properties['payment_discount_element_id'];
		
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

		$query = "insert into ".MF_TABLE_PREFIX."form_payments(
																form_id,
																record_id,
																payment_status,
																payment_merchant_type,
																payment_amount,
																payment_currency) 
														values(?,?,?,?,?,?)";
		$params = array($form_id,$entry_id,$payment_status,$payment_merchant_type,$payment_amount,$payment_currency);
		mf_do_query($query,$params,$dbh);
	}
	
   	$response_data = new stdClass();
	$response_data->status    	   = "ok";
	$response_data->payment_status = $payment_status;

	$response_json = json_encode($response_data);
		
	echo $response_json;

?>