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

	$csrf_token = trim($_POST['csrf_token']);
	
	//validate CSRF token
	mf_verify_csrf_token($csrf_token);

	//check for max_input_vars
	mf_init_max_input_vars();
	
	if(empty($_POST['payment_properties'])){
		die("Error! You can't open this file directly");
	}
	
	$payment_properties = mf_sanitize($_POST['payment_properties']);
	$field_prices = mf_sanitize($_POST['field_prices']);
	
	$form_id = (int) $payment_properties['form_id'];
	unset($payment_properties['form_id']);

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_form permission
		if(empty($user_perms['edit_form'])){
			die("Access Denied. You don't have permission to edit this form.");
		}
	}
	
	//save payment properties into ap_forms table
	foreach ($payment_properties as $key=>$value){
		$form_input['payment_'.$key] = $value;
	}
	
	
	//make sure only positive numbers entered into discount amount
	if(!empty($form_input['payment_discount_amount'])){
		$form_input['payment_discount_amount'] = (float) abs($form_input['payment_discount_amount']);

		//make sure discount percentage is not larger than 100%
		if($form_input['payment_discount_type'] == 'percent_off' && $form_input['payment_discount_amount'] > 100){
			$form_input['payment_discount_amount'] = 100;
		}
	}

	//make sure discount max redemption is a positive number
	if(!empty($form_input['payment_discount_max_usage'])){
		$form_input['payment_discount_max_usage'] = (int) abs($form_input['payment_discount_max_usage']);
	}

	mf_ap_forms_update($form_id,$form_input,$dbh);
	
	//save field prices into ap_element_prices table
	$query = "delete from ".MF_TABLE_PREFIX."element_prices where form_id=?";
	$params = array($form_id);
	mf_do_query($query,$params,$dbh);
	
	if(!empty($field_prices)){
		foreach ($field_prices as $element_data){
			if(isset($element_data['element_type']) && $element_data['element_type'] == 'price'){ //if this is price field
				$query = "insert into ".MF_TABLE_PREFIX."element_prices(form_id,element_id,option_id,`price`) values(?,?,?,?)";
				$params = array($form_id,$element_data['element_id'],$element_data['option_id'],$element_data['price']);
				mf_do_query($query,$params,$dbh);
			}else{
				foreach($element_data as $values){
					if(is_array($values)){
						$element_id = (int) $values['element_id'];
						
						if(!empty($element_id)){
							$query = "insert into ".MF_TABLE_PREFIX."element_prices(form_id,element_id,option_id,`price`) values(?,?,?,?)";
							$params = array($form_id,$values['element_id'],$values['option_id'],$values['price']);
							mf_do_query($query,$params,$dbh);
						}
					}	
				}
			}	
		}
	}
   
	$_SESSION['MF_SUCCESS'] = 'Payment settings has been saved.';
	
   	echo '{ "status" : "ok", "form_id" : "'.$form_id.'" }';
   
?>