<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	
	//remove all whitespaces and slashes from input
	function mf_sanitize($input){
		if(!empty($input)){
	
			if(is_array($input)){
				$input = array_map('mf_trim_deep', $input);
			}else{
				$input = trim($input);
			}
		}
		
		return $input;
	}
	
	function mf_trim_deep($value){
		
		if(is_array($value)){
			$value = array_map('mf_trim_deep', $value);
		}else{
			$value = trim($value ?? '');
		}

	    return $value;
	}

	//helper function for mf_check_max_input_vars()
	function mf_implode_array_chuncks($input_array){
		return implode('&', $input_array);
	}

	//check the value of max_input_vars
	//if being set between 1-10000, get the POST data and chunk it into smaller arrays
	function mf_init_max_input_vars(){
		$max_input_vars = (int) ini_get('max_input_vars');
		
		//if max_input_vars is 0 or empty, then most likely the PHP version is less than PHP 5.3.9
		if ($max_input_vars <= 0) {
        	return true;
    	}

    	//if max_input_vars already being set to a large value, no need to parse it further
    	if($max_input_vars >= 10000){
    		return true;
    	}

    	//if the number of input is less than max_input_vars, no need to parse it further
    	if (count($_POST, COUNT_RECURSIVE) < $max_input_vars) {
        	return true;
    	}
    	
    	//read raw post data using php://input wrapper, since this one is not affected by max_input_vars
    	$input_string = file_get_contents("php://input");
    	if($input_string === false or $input_string === '') {
        	return true;
    	}

		$exploded_array = explode('&', $input_string);
		$chunked_array  = array_chunk($exploded_array, $max_input_vars);
    	$imploded_array = array_map('mf_implode_array_chuncks', $chunked_array);

    	foreach ($imploded_array as $chunk_data) {
	        $parsed_vars = array();
	        parse_str($chunk_data, $parsed_vars);
	        
	        //merge parsed variables into POST
	        mf_merge_parsed_vars_to_post($parsed_vars);
	    }
	}

	//merge parsed variables into POST. support nested array up to 5 level
	//this function could be better using recursive, but recursive is harder to read
	function mf_merge_parsed_vars_to_post($parsed_vars){
		foreach ($parsed_vars as $key_1 => $value_1) {
			if(is_array($value_1)){
				foreach ($value_1 as $key_2 => $value_2) {
					if(is_array($value_2)){
						foreach ($value_2 as $key_3 => $value_3) {
							if(is_array($value_3)){
								foreach ($value_3 as $key_4 => $value_4) {
									if(is_array($value_4)){
										foreach ($value_4 as $key_5 => $value_5) {
											if(is_array($value_5)){
												//placeholder
												//add another loop here to add more level
											}else{
												$_POST[$key_1][$key_2][$key_3][$key_4][$key_5] = $value_5;
											}
										}										
									}else{
										$_POST[$key_1][$key_2][$key_3][$key_4] = $value_4;
									}
								}
							}else{
								$_POST[$key_1][$key_2][$key_3] = $value_3;
							}
						}
					}else{
						$_POST[$key_1][$key_2] = $value_2;
					}
				}
			}else{
				$_POST[$key_1] = $value_1;
			}
		}
	}

?>