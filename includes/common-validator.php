<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	
	//validation for required field
	function mf_validate_required($value){
		global $mf_lang;

		$value = $value[0]; 
		if(empty($value) && (($value != 0) || ($value != '0'))){ //0  and '0' should not considered as empty
			return $mf_lang['val_required'];
		}else{
			return true;
		}
	}	
	
	//validation for unique checking on db table
	function mf_validate_unique($value){
		global $mf_lang;

		$input_value  = $value[0]; 
	
		$exploded = explode('#',$value[1]);
		$form_id  = (int) $exploded[0];
		$element_name = $exploded[1];
		
		$dbh = $value[2]['dbh'];
		
		if(!empty($_SESSION['edit_entry']) && ($_SESSION['edit_entry']['form_id'] == $form_id)){
			//if admin is editing through edit_entry.php, bypass the unique checking if the new entry is the same as previous
			$query = "select count($element_name) total from ".MF_TABLE_PREFIX."form_{$form_id} where {$element_name}=? and `id` != ? and {$element_name} is not null and `status` = 1";
			$params = array($input_value,$_SESSION['edit_entry']['entry_id']);
			
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);
		}else{
			$query = "select count($element_name) total from ".MF_TABLE_PREFIX."form_{$form_id} where {$element_name}=? and {$element_name} is not null and resume_key is null and `status`= 1";
			$params = array($input_value);
			
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);
		}
		
		if(!empty($row['total'])){ 
			return $mf_lang['val_unique'];
		}else{
			return true;
		}
	}	
	
	//validation for coupon code checking on db table
	function mf_validate_coupon($value){
		global $mf_lang;

		$input_coupon_code  = strtolower(trim($value[0])); 
		$form_id  			= (int) $value[1];

		//we don't need to validate empty coupon code
		if(empty($input_coupon_code)){
			return true;
		}
		
		$dbh = $value[2]['dbh'];
		
		//get the coupon code setting first
		$query = "select payment_discount_code,payment_discount_element_id,payment_discount_max_usage,payment_discount_expiry_date from ".MF_TABLE_PREFIX."forms where form_id = ?";
		$params = array($form_id);
			
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		$discount_code = strtolower($row['payment_discount_code']);
		$discount_element_id = (int) $row['payment_discount_element_id'];
		$discount_max_usage = (int) $row['payment_discount_max_usage'];
		$discount_expiry_date = $row['payment_discount_expiry_date'];


		//validate the coupon code
		//make sure entered coupon code is valid
		$discount_code_array = explode(',', $discount_code);
		array_walk($discount_code_array, 'mf_trim_value');

		if(!in_array($input_coupon_code, $discount_code_array)){
			return $mf_lang['coupon_not_exist'];
		}
		
		//make sure entered coupon code is within the max usage
		if(!empty($discount_max_usage)){
			$query = "select count(*) coupon_usage from ".MF_TABLE_PREFIX."form_{$form_id} where element_{$discount_element_id} = ? and `status` = 1";

			$params = array($input_coupon_code);
			
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);
			$current_coupon_usage = (int) $row['coupon_usage'];
			
			if($current_coupon_usage >= $discount_max_usage){
				return $mf_lang['coupon_max_usage'];
			}
		}

		//make sure entered coupon code is not expired yet
		if(!empty($discount_expiry_date) && $discount_expiry_date != '0000-00-00'){
			$current_date = strtotime(date("Y-m-d"));
			$expiry_date  = strtotime($discount_expiry_date);

			if($current_date >= $expiry_date){
				return $mf_lang['coupon_expired'];
			}
		}		
		
		return true;
	}
		
	//validation for integer
	function mf_validate_integer($value){
		global $mf_lang;

		$error_message = $mf_lang['val_integer'];
		
		$value = $value[0];
		if(is_int($value)){
			return true; //it's integer
		}else if(is_float($value)){
			return $error_message; //it's float
		}else if(is_numeric($value)){
			$result = strpos($value,'.');
			if($result !== false){
				return $error_message; //it's float
			}else{
				return true; //it's integer
			}
		}else{
			return $error_message; //it's not even a number!
		}
	}
	
	//validation for float aka double
	function mf_validate_float($value){
		global $mf_lang;

		$error_message = $mf_lang['val_float'];
		
		$value = $value[0];
		if(is_int($value)){
			return $error_message; //it's integer
		}else if(is_float($value)){
			return true; //it's float
		}else if(is_numeric($value)){
			$result = strpos($value,'.');
			if($result !== false){
				return true; //it's float
			}else{
				return $error_message; //it's integer
			}
		}else{
			return $error_message; //it's not even a number!
		}
	}
	
	//validation for numeric
	function mf_validate_numeric($value){
		global $mf_lang;

		$error_message = $mf_lang['val_numeric'];
				
		$value = $value[0];
		if(is_numeric($value)){
			return true;
		}else{
			return $error_message;
		}
		
	}
	
	//validation for phone (###) ### ####
	function mf_validate_phone($value){
		global $mf_lang;

		$error_message = $mf_lang['val_phone'];
		
		if(!empty($value[0])){
			$regex  = '/^[1-9][0-9]{9}$/';
			$result = preg_match($regex, $value[0]);
			
			if(empty($result)){
				return $error_message;
			}else{
				return true;
			}
		}else{
			return true;
		}
		
	}
	
	//validation for simple phone, international phone
	function mf_validate_simple_phone($value){
		global $mf_lang;

		$error_message = $mf_lang['val_phone'];
		
		if($value[0][0] == '+'){
			$test_value = substr($value[0],1);
		}else{
			$test_value = $value[0];
		}
		
		if(is_numeric($test_value) && (strlen($test_value) > 3)){
			return true;
		}else{
			return $error_message;
		}
	}
	
	//validation for minimum length
	function mf_validate_min_length($value){
		global $mf_lang;

		$target_value = $value[0];
		$exploded 	  = explode('#',$value[1]);
		
		$range_limit_by = $exploded[0];
		$range_min		= (int) $exploded[1];
		
		if($range_limit_by == 'c' || $range_limit_by == 'd'){
			if(function_exists('mb_strlen')){
				$target_length = mb_strlen($target_value);
			}else{
				$target_length = strlen($target_value);
			}
		}elseif ($range_limit_by == 'w'){
			$target_length = count(preg_split("/[\s\.]+/", $target_value, NULL, PREG_SPLIT_NO_EMPTY));
		}
		
		if($target_length < $range_min){
			return 'error_no_display';
		}else{
			return true;
		}
	}
	
	//validation for number minimum value
	function mf_validate_min_value($value){
		global $mf_lang;

		$target_value = (float) $value[0];
		$range_min	  = (float) $value[1];
		
		if($target_value < $range_min){
			return 'error_no_display';
		}else{
			return true;
		}
	}
	
	//validation for maximum length
	function mf_validate_max_length($value){
		global $mf_lang;

		$target_value = $value[0];
		$exploded 	  = explode('#',$value[1]);
		
		$range_limit_by = $exploded[0];
		$range_max		= (int) $exploded[1];
		
		if($range_limit_by == 'c' || $range_limit_by == 'd'){
			if(function_exists('mb_strlen')){
				$target_length = mb_strlen($target_value);
			}else{
				$target_length = strlen($target_value);
			}
		}elseif ($range_limit_by == 'w'){
			$target_length = count(preg_split("/[\s\.]+/", $target_value, NULL, PREG_SPLIT_NO_EMPTY));
		}
		
		if($target_length > $range_max){
			return 'error_no_display';
		}else{
			return true;
		}
	}
	
	//validation for number minimum value
	function mf_validate_max_value($value){
		global $mf_lang;

		$target_value = (float) $value[0];
		$range_max	  = (float) $value[1];
		
		if($target_value > $range_max){
			return 'error_no_display';
		}else{
			return true;
		}
	}
	
	//validation for range length
	function mf_validate_range_length($value){
		global $mf_lang;

		$target_value = $value[0];
		$exploded 	  = explode('#',$value[1]);
		
		$range_limit_by = $exploded[0];
		$range_min		= (int) $exploded[1];
		$range_max		= (int) $exploded[2];
		
		if($range_limit_by == 'c' || $range_limit_by == 'd'){
			if(function_exists('mb_strlen')){
				$target_length = mb_strlen($target_value);
			}else{
				$target_length = strlen($target_value);
			}
		}elseif ($range_limit_by == 'w'){
			$target_length = count(preg_split("/[\s\.]+/", $target_value, NULL, PREG_SPLIT_NO_EMPTY));
		}
		
		if(!($range_min <= $target_length && $target_length <= $range_max)){
			return 'error_no_display';
		}else{
			return true;
		}
	}
	
	//validation for number range value
	function mf_validate_range_value($value){
		global $mf_lang;
		
		$target_value = (float) $value[0];
		$exploded 	  = explode('#',$value[1]);
		
		$range_min		= (float) $exploded[0];
		$range_max		= (float) $exploded[1];
		
		if(!($range_min <= $target_value && $target_value <= $range_max)){
			return 'error_no_display';
		}else{
			return true;
		}
	}
	
	//validation to check email address format
	function mf_validate_email($value) {
		global $mf_lang;

		$error_message = $mf_lang['val_email'];
		
		$value[0] = trim($value[0]);

		if(!empty($value[0])){
			$regex  = '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD';
			$result = preg_match($regex, $value[0]);
			
			if(empty($result)){
				return sprintf($error_message,'%s',$value[0]);
			}else{
				return true;
			}
		}else{
			return true;
		}
	}
	
	//validation to check URL format
	function mf_validate_website($value) {
		global $mf_lang;

		$error_message = $mf_lang['val_website'];
		$value[0] = trim($value[0],'/').'/';
		
		if(!empty($value[0]) && $value[0] != '/'){
			$regex  = '/^https?:\/\/([a-z0-9]([-a-z0-9]*[a-z0-9])?\.)+([A-z0-9]{2,})(\/)(.*)$/i';
			$result = preg_match($regex, $value[0]);
			
			if(empty($result)){
				return sprintf($error_message,'%s',$value[0]);
			}else{
				return true;
			}
		}else{
			return true;
		}
	}
	
	//validation to allow only a-z 0-9 and underscores 
	function mf_validate_username($value){
		global $mf_lang;

		$error_message = $mf_lang['val_username'];
		
		if(!preg_match("/^[a-z0-9][\w]*$/i",$value[0])){
			return sprintf($error_message,'%s',$value[0]);
		}else{
			return true;
		}
	}
	
	
	
	//validation to check two variable equality. usefull for checking password 
	function mf_validate_equal($value){
		global $mf_lang;

		$error_message = $mf_lang['val_equal'];
		
		if($value[0] != $value[2][$value[1]]){
			return $error_message;
		}else{
			return true;
		}
	}
	
	//validate date format
	//currently only support this format: yyyy-mm-dd
	function mf_validate_date($value) {
		global $mf_lang;

		$error_message 	   = $mf_lang['val_date'];
		$validation_result = false;

		if(!empty($value[0])){
			$exploded = explode('-', $value[0]);
			$validation_result = checkdate($exploded[1], $exploded[2], $exploded[0]);
		}
		
		
		if($validation_result === true){
			return true;
		}else{
			return sprintf($error_message,'%s',$value[1]);
		}
	}
	
	//validate date range
	function mf_validate_date_range($value){
		global $mf_lang;

		$error_message = $mf_lang['val_date_range'];
		
		$target_value = strtotime($value[0]);
		$exploded 	  = explode('#',$value[1]);
		
		if(!empty($exploded[0])){
			if($exploded[0] == '0000-00-00'){
				//0000-00-00 consider minimum date as today date
				$range_min = strtotime(date('Y-m-d'));
				$range_min_formatted = date('M j, Y',$range_min);
			}else if($exploded[0] == '9999-99-99'){
				//9999-99-99 is special code for 'disable past/future date'
				$range_min = '';
			}else{ 
				if(strpos($exploded[0], '-00-00') !== false){
					//if this is relative date range. format: XXXX-00-YY, where XXXX is days and YY is 00 or 01 (negative flag)
					$exploded[0] = (int) $exploded[0];
					$range_min   = strtotime(date('Y-m-d',strtotime("-{$exploded[0]} day")));
				}else if(strpos($exploded[0], '-00-01') !== false){
					//if this is relative date range. format: XXXX-00-YY, where XXXX is days and YY is 00 or 01 (negative flag)
					$exploded[0] = (int) $exploded[0];
					$range_min   = strtotime(date('Y-m-d',strtotime("+{$exploded[0]} day")));
				}else{
					$range_min = strtotime($exploded[0]);
				}

				$range_min_formatted = date('M j, Y',$range_min);
			}
		}

		if(!empty($exploded[1])){
			if($exploded[1] == '0000-00-00'){
				//0000-00-00 consider maximum date as today date
				$range_max = strtotime(date('Y-m-d'));
				$range_max_formatted = date('M j, Y',$range_max);
			}else if($exploded[0] == '9999-99-99'){
				//9999-99-99 is special code for 'disable past/future date'
				$range_max = '';
			}else{
				if(strpos($exploded[1], '-00-00') !== false){
					//if this is relative date range. format: XXXX-00-YY, where XXXX is days and YY is 00 or 01 (negative flag)
					$exploded[1] = (int) $exploded[1];
					$range_max   = strtotime(date('Y-m-d',strtotime("+{$exploded[1]} day")));
				}else if(strpos($exploded[1], '-00-01') !== false){
					//if this is relative date range. format: XXXX-00-YY, where XXXX is days and YY is 00 or 01 (negative flag)
					$exploded[1] = (int) $exploded[1];
					$range_max   = strtotime(date('Y-m-d',strtotime("-{$exploded[1]} day")));
				}else{
					$range_max = strtotime($exploded[1]);
				}

				$range_max_formatted = date('M j, Y',$range_max);
			}
		}
		
		
		if(!empty($range_min) && !empty($range_max)){
			if(!($range_min <= $target_value && $target_value <= $range_max)){
				$error_message = $mf_lang['val_date_range'];
				return sprintf($error_message,$range_min_formatted,$range_max_formatted);
			}
		}else if(!empty($range_min)){
			if($target_value < $range_min){
				$error_message = $mf_lang['val_date_min'];
				return sprintf($error_message,$range_min_formatted);
			}
		}else if (!empty($range_max)){
			if($target_value > $range_max){
				$error_message = $mf_lang['val_date_max'];
				return sprintf($error_message,$range_max_formatted);
			}
		}
		
		return true;
	}
	
	function mf_validate_disabled_dates($value){
		global $mf_lang;

		$error_message = $mf_lang['val_date_na'];
		
		$target_value   = $value[0];
		$disabled_dates = $value[1];

		$target_value = date('Y-n-j',strtotime(trim($target_value)));

		if(in_array($target_value,$disabled_dates)){
			return $error_message;
		}else{
			return true;
		}
	}

	//check if a day matched particular day(s) of week or not
	function mf_validate_disabled_dayofweek($value){
		global $mf_lang;

		$error_message = $mf_lang['val_date_na'];
		
		$target_value   	= $value[0];
		$disabled_dayofweek = explode(',',$value[1]);

		$target_value = date('w', strtotime($target_value));

		if(in_array($target_value,$disabled_dayofweek)){
			return $error_message;
		}else{
			return true;
		}
	}
	
	//check if a date is a weekend date or not
	function mf_validate_date_weekend($value){
		global $mf_lang;

		$error_message = $mf_lang['val_date_na'];
		
		$target_value   = $value[0];
		
		if(date('N', strtotime($target_value)) >= 6){
			return $error_message;
		}else{
			return true;
		}
	}
	
	//validation to check valid time format 
	function mf_validate_time($value){
		global $mf_lang;

		$error_message = $mf_lang['val_time'];
		
		$timestamp = strtotime($value[0]);
		
		if($timestamp == -1 || $timestamp === false){
			return $error_message;
		}else{
			return true;
		}
	}
	
	
	//validation for required file
	function mf_validate_required_file($value){
		global $mf_lang;

		$error_message = $mf_lang['val_required_file'];
		$element_file = $value[0];
		
		if($_FILES[$element_file]['size'] > 0){
			return true;
		}else{
			return $error_message;
		}
	}
	
	//validation for file upload filetype
	function mf_validate_filetype($value){
		global $mf_lang;

		$file_rules = $value[2];
		
		$error_message = $mf_lang['val_filetype'];
		$value = $value[0];
		
		$ext = pathinfo(strtolower($_FILES[$value]['name']), PATHINFO_EXTENSION);
		
		if(!empty($file_rules['file_type_list'])){
			
			$file_type_array = explode(',',$file_rules['file_type_list']);
			$file_type_array = array_map('strtolower', $file_type_array);
			
			if($file_rules['file_block_or_allow'] == 'b'){
				if(in_array($ext,$file_type_array)){
					return $error_message;
				}	
			}else if($file_rules['file_block_or_allow'] == 'a'){
				if(!in_array($ext,$file_type_array)){
					return $error_message;
				}
			}
		}
		
		
		return true;
	}

	//validate checkbox choice limit
	function mf_validate_choice_limit($value){
		global $mf_lang;

		$target_value = $value[0];
		$exploded 	  = explode('#',$value[1]);
		
		$choice_limit_rule 		= $exploded[0];
		$choice_limit_qty  		= (int) $exploded[1];
		$choice_limit_range_min	= (int) $exploded[2];
		$choice_limit_range_max	= (int) $exploded[3];
		
		$selected_choice = strlen($target_value);

		if($choice_limit_rule == 'atleast'){
			$error_message = $mf_lang['val_choice_atleast'];

			if($selected_choice < $choice_limit_qty){
				return sprintf($error_message, $choice_limit_qty);
			}
		}else if($choice_limit_rule == 'atmost'){
			$error_message = $mf_lang['val_choice_atmost'];

			if($selected_choice > $choice_limit_qty){
				return sprintf($error_message, $choice_limit_qty);
			}
		}else if($choice_limit_rule == 'exactly'){
			$error_message = $mf_lang['val_choice_exactly'];

			if($selected_choice != $choice_limit_qty){
				return sprintf($error_message, $choice_limit_qty);
			}
		}else if($choice_limit_rule == 'between'){
			$error_message = $mf_lang['val_choice_between'];

			if($selected_choice < $choice_limit_range_min || $selected_choice > $choice_limit_range_max){
				return sprintf($error_message, $choice_limit_range_min, $choice_limit_range_max);
			}
		}

		return true;
	}
	
	/*********************************************************
	* This is main validation function
	* This function will call sub function, called validate_xx
	* Each sub function is specific for one rule
	*
	* Syntax: $rules[field_name][validation_type] = value
	* validation_type: required,integer,float,min,max,range,email,username,equal,date
	* Example rules:
	*
	* $rules['author_id']['required'] = true; //author_id is required
	* $rules['author_id']['integer']  = true; //author_id must be an integer
	* $rules['author_id']['range']    = '2-10'; //author_id length must be between 2 - 10 characters
	*
	**********************************************************/
	function mf_validate_rules($input,$rules){
		global $mf_lang;

		//traverse for each input, check for rules to be applied
		foreach ($input as $key=>$value){
			$current_rules = @$rules[$key];
			$error_message = array();
			
			if(!empty($current_rules)){
				//an input can be validated by many rules, check that here
				foreach ($current_rules as $key2=>$value2){
					$argument_array = array($value,$value2,$input);
					$result = call_user_func('mf_validate_'.$key2,$argument_array);
					
					if($result !== true){ //if we got error message, break the loop
						$error_message = $result;
						break;
					}
				}
			}
			if(count($error_message) > 0){
				$total_error_message[$key] = $error_message;
			}
		}
		
		if(@is_array($total_error_message)){
			return $total_error_message;
		}else{
			return true;
		}
	}
	
	//similar as function above, but this is specific for validating form inputs, with only one error message per input
	function mf_validate_element($input,$rules){
		global $mf_lang;
		
		//traverse for each input, check for rules to be applied
		foreach ($input as $key=>$value){
			$current_rules = @$rules[$key];
			$error_message = '';
			
			if(!empty($current_rules)){
				//an input can be validated by many rules, check that here
				foreach ($current_rules as $key2=>$value2){
					$argument_array = array($value,$value2,$input);
					$result = call_user_func('mf_validate_'.$key2,$argument_array);
					
					if($result !== true){ //if we got error message, break the loop
						$error_message = $result;
						break;
					}
				}
			}
			if(!empty($error_message)){
				$last_error_message = $error_message;
				break;
			}
		}
		
		if(!empty($last_error_message)){
			return $last_error_message;
		}else{
			return true;
		}
	}
?>