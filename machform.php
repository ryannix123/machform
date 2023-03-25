<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	error_reporting(E_ALL ^ E_NOTICE);

	$include_path = dirname(__FILE__).'/';

	require($include_path.'config.php');
	require($include_path.'includes/language.php');
	require($include_path.'includes/db-core.php');
	require($include_path.'includes/common-validator.php');
	require($include_path.'includes/view-functions.php');
	require($include_path.'includes/post-functions.php');
	require($include_path.'includes/entry-functions.php');
	require($include_path.'includes/filter-functions.php');
	require($include_path.'includes/helper-functions.php');
	require($include_path.'includes/theme-functions.php');
	require($include_path.'hooks/custom_hooks.php');
	require($include_path.'lib/swift-mailer/swift_required.php');
	require($include_path.'lib/HttpClient.class.php');		
	require($include_path.'lib/recaptchalib2.php');
	require($include_path.'lib/php-captcha/php-captcha.inc.php');
	require($include_path.'lib/text-captcha.php');
	require($include_path.'lib/stripe/init.php');
	require($include_path.'lib/dompdf/autoload.inc.php');
	require($include_path.'lib/libsodium/autoload.php');
	require($include_path.'lib/google-api-client/vendor/autoload.php');
	
		
	function display_machform($config){
		
		$form_id       = $config['form_id'];
		$show_border   = $config['show_border'];
		$machform_path = $config['base_path'];
		$machform_data_path = '';
		
		if($show_border === true){
			$integration_method = '';
		}else{
			$integration_method = 'php';
		}

		//start session if there isn't any
		if(session_id() == ""){
			@session_start();
		}
		
		$dbh = mf_connect_db();
		$mf_settings = mf_get_settings($dbh);

		$http_host = parse_url($mf_settings['base_url'], PHP_URL_HOST);

		if(mf_is_form_submitted()){ //if form submitted
			$input_array   = mf_sanitize($_POST);

			$input_array['machform_data_path'] = $machform_data_path;
			$input_array['machform_base_path'] = $machform_path;
			$submit_result = mf_process_form($dbh,$input_array);
			
			if(!isset($input_array['password'])){ //if normal form submitted
				
				if($submit_result['status'] === true){
					if(!empty($submit_result['form_resume_url'])){ //the user saving a form, display success page with the resume URL
						$_SESSION['mf_form_resume_url'][$input_array['form_id']] = $submit_result['form_resume_url'];
						
						if(strpos($_SERVER['REQUEST_URI'],'?') === false){
							echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}?done=1'</script>";
						}else{
							echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}&done=1'</script>";
						}
						exit;
					}else if($submit_result['logic_page_enable'] === true){ //the page has skip logic enable and a custom destination page has been set
						$target_page_id = $submit_result['target_page_id'];

						if(is_numeric($target_page_id)){
							if(strpos($_SERVER['REQUEST_URI'],'?') === false){
								echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}?mf_page={$target_page_id}'</script>";
							}else{
								echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}&mf_page={$target_page_id}'</script>";
							}
							exit;
						}else if($target_page_id == 'payment'){
							//redirect to payment page, based on selected merchant
							$form_properties = mf_get_form_properties($dbh,$input_array['form_id'],array('payment_merchant_type'));

							//if this is form edit entry page and the entry already paid, bypass payment page and display success
							if(!empty($submit_result['bypass_payment_page'])){
								header("Location: http{$ssl_suffix}://".$http_host.$_SERVER['PHP_SELF']."?id={$input_array['form_id']}&done=1");
								exit;
							}

							if(in_array($form_properties['payment_merchant_type'], array('stripe','authorizenet','paypal_rest','braintree'))){
								//allow access to payment page
								$_SESSION['mf_form_payment_access'][$input_array['form_id']] = true;
								$_SESSION['mf_payment_record_id'][$input_array['form_id']] = $submit_result['entry_id'];

								if(strpos($_SERVER['REQUEST_URI'],'?') === false){
									echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}?show_payment=1'</script>";
								}else{
									echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}&show_payment=1'</script>";
								}
								exit;
							}else if($form_properties['payment_merchant_type'] == 'paypal_standard'){
								echo "<script type=\"text/javascript\">top.location = '{$submit_result['form_redirect']}'</script>";
								exit;
							}
						}else if($target_page_id == 'review'){
							if(!empty($submit_result['origin_page_number'])){
								$page_num_params = '&mf_page_from='.$submit_result['origin_page_number'];
							}

							$_SESSION['review_id'] = $submit_result['review_id'];
							if(strpos($_SERVER['REQUEST_URI'],'?') === false){
								echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}?show_review=1{$page_num_params}'</script>";
							}else{
								echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}&show_review=1{$page_num_params}'</script>";
							}	
							exit;
						}else if($target_page_id == 'success'){
							//redirect to success page
							if(!empty($submit_result['logic_success_enable']) && (($logic_redirect_url = mf_get_logic_success_redirect_url($dbh,$input_array['form_id'],$submit_result['entry_id'])) != '') ){
								echo "<script type=\"text/javascript\">top.location.replace('{$logic_redirect_url}')</script>";
								exit;
							}else if(empty($submit_result['form_redirect'])){		
								if(strpos($_SERVER['REQUEST_URI'],'?') === false){
									echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}?done=1'</script>";
								}else{
									echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}&done=1'</script>";
								}
								exit;
							}else{
								echo "<script type=\"text/javascript\">top.location = '{$submit_result['form_redirect']}'</script>";
								exit;
							}
						}
					}else if(!empty($submit_result['review_id'])){ //redirect to review page
						
						if(!empty($submit_result['origin_page_number'])){
							$page_num_params = '&mf_page_from='.$submit_result['origin_page_number'];
						}
						
						$_SESSION['review_id'] = $submit_result['review_id'];
						
						if(strpos($_SERVER['REQUEST_URI'],'?') === false){
							echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}?show_review=1{$page_num_params}'</script>";
						}else{
							echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}&show_review=1{$page_num_params}'</script>";
						}
						exit;
					}else{
						$ssl_suffix = mf_get_ssl_suffix();

						if(!empty($submit_result['next_page_number'])){ //redirect to the next page number
							$_SESSION['mf_form_access'][$input_array['form_id']][$submit_result['next_page_number']] = true;
															
							echo "<script type=\"text/javascript\">top.location = 'http{$ssl_suffix}://".$http_host.$_SERVER['PHP_SELF']."?id={$input_array['form_id']}&mf_page={$submit_result['next_page_number']}'</script>";
							exit;
						}else{ //otherwise display success message or redirect to the custom redirect URL or payment page
							if(mf_is_payment_has_value($dbh,$input_array['form_id'],$submit_result['entry_id']) && empty($submit_result['bypass_payment_page'])){
								//redirect to credit card payment page, if the merchant is being enabled and the amount is not zero

								//allow access to payment page
								$_SESSION['mf_form_payment_access'][$input_array['form_id']] = true;
								$_SESSION['mf_payment_record_id'][$input_array['form_id']] = $submit_result['entry_id'];
								
								if(strpos($_SERVER['REQUEST_URI'],'?') === false){
									echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}?show_payment=1'</script>";
								}else{
									echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}&show_payment=1'</script>";
								}
								exit;
							}else{
								if(empty($submit_result['form_redirect'])){
									if(strpos($_SERVER['REQUEST_URI'],'?') === false){
										echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}?done=1'</script>";
									}else{
										echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}&done=1'</script>";
									}
									exit;
								}else{
									echo "<script type=\"text/javascript\">top.location = '{$submit_result['form_redirect']}'</script>";
									exit;
								}
							}

						}
					}
				}else if($submit_result['status'] === false){ //there are errors, display the form again with the errors
					$old_values 	= $submit_result['old_values'];
					$custom_error 	= @$submit_result['custom_error'];
					$error_elements = $submit_result['error_elements'];
					
					$form_params = array();
					$form_params['page_number'] = $input_array['page_number'];
					$form_params['populated_values'] = $old_values;
					$form_params['error_elements'] = $error_elements;
					$form_params['custom_error'] = $custom_error;
					$form_params['integration_method'] = $integration_method;
					$form_params['machform_path'] = $machform_path;
					$form_params['machform_data_path'] = $machform_data_path;

					$markup = mf_display_form($dbh,$input_array['form_id'],$form_params);
				}
			}else{ //if password form submitted
				
				if($submit_result['status'] === true){ //on success, display the form
					$form_params = array();
					$form_params['integration_method'] = $integration_method;
					$form_params['machform_path'] = $machform_path;
					$form_params['machform_data_path'] = $machform_data_path;
					
					$markup = mf_display_form($dbh,$input_array['form_id'],$form_params);
				}else{
					$custom_error = $submit_result['custom_error']; //error, display the pasword form again
					
					$form_params = array();
					$form_params['custom_error'] = $custom_error;
	 				$form_params['integration_method'] = $integration_method;
	 				$form_params['machform_path'] = $machform_path;
	 				$form_params['machform_data_path'] = $machform_data_path;

	 				$markup = mf_display_form($dbh,$input_array['form_id'],$form_params);
				}
			}
		}else if(!empty($_POST['review_submit']) || !empty($_POST['review_submit_x'])){ //if form review being submitted	
			//commit data from review table to actual table
			//however, we need to check if this form has payment enabled or not

			//if the form doesn't have any payment enabled, continue with commit and redirect to success page
			$form_properties = mf_get_form_properties($dbh,$form_id,array('payment_enable_merchant','payment_delay_notifications','payment_merchant_type','form_entry_edit_enable'));
			$ssl_suffix = mf_get_ssl_suffix();
			$record_id = $_SESSION['review_id'];

			$edit_key = trim($_POST['edit_key']); //record edit_key hash, coming from form page when 'edit completed entry' enabled

			$commit_options = array();

			//don't run integrations when submitting form edit entry page
			if(!empty($edit_key) && !empty($form_properties['form_entry_edit_enable'])){
				$commit_options['run_integrations'] = false;
			}

			if($form_properties['payment_enable_merchant'] != 1){
				
				$commit_options['machform_path'] = $machform_path;
				$commit_options['machform_data_path'] = $machform_data_path;

				$commit_result = mf_commit_form_review($dbh,$form_id,$record_id,$commit_options);
				
				unset($_SESSION['review_id']);
				
				if(empty($commit_result['form_redirect'])){
					
					if(strpos($_SERVER['REQUEST_URI'],'?') === false){
						echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}?done=1'</script>";
					}else{
						echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}&done=1'</script>";
					}
					exit;
				}else{
					echo "<script type=\"text/javascript\">top.location = '{$commit_result['form_redirect']}'</script>";
					exit;
				}
			}else{
				//if the form has payment enabled, continue commit and redirect to payment page
				
				//delay notifications only available on some merchants 
				if(!empty($form_properties['payment_delay_notifications']) && in_array($form_properties['payment_merchant_type'], array('stripe','paypal_standard','authorizenet','paypal_rest','braintree'))){	
					$commit_options['send_notification'] = false;
				}

				$commit_result = mf_commit_form_review($dbh,$form_id,$record_id,$commit_options);

				unset($_SESSION['review_id']);
				
				//if this is form edit entry page and the entry already paid, bypass payment page and display success
				if(!empty($commit_result['bypass_payment_page'])){
					header("Location: http{$ssl_suffix}://".$http_host.$_SERVER['PHP_SELF']."?id={$input_array['form_id']}&done=1");
					exit;
				}
								
				if(in_array($form_properties['payment_merchant_type'], array('stripe','authorizenet','paypal_rest','braintree'))){
					//allow access to payment page
					$_SESSION['mf_form_payment_access'][$form_id] = true;
					$_SESSION['mf_payment_record_id'][$form_id] = $commit_result['record_insert_id'];

					if(strpos($_SERVER['REQUEST_URI'],'?') === false){
						echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}?show_payment=1'</script>";
					}else{
						echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}&show_payment=1'</script>";
					}
					exit;
				}else if($form_properties['payment_merchant_type'] == 'paypal_standard'){
					
					if(empty($commit_result['form_redirect'])){
						if(strpos($_SERVER['REQUEST_URI'],'?') === false){
							echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}?done=1'</script>";
						}else{
							echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}&done=1'</script>";
						}
						exit;
					}else{
						echo "<script type=\"text/javascript\">top.location = '{$commit_result['form_redirect']}'</script>";
						exit;
					}

				}else if($form_properties['payment_merchant_type'] == 'check'){
					//redirect to either success page or custom redirect URL
					if(empty($commit_result['form_redirect'])){
						if(strpos($_SERVER['REQUEST_URI'],'?') === false){
							echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}?done=1'</script>";
						}else{
							echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}&done=1'</script>";
						}
						exit;
					}else{
						echo "<script type=\"text/javascript\">top.location = '{$commit_result['form_redirect']}'</script>";
						exit;
					}
				}
			}
			
		}else if(!empty($_POST['review_back']) || !empty($_POST['review_back_x'])){ //go back to form from review page	
			$origin_page_num = (int) $_POST['mf_page_from'];
			$ssl_suffix = mf_get_ssl_suffix();
			
			echo "<script type=\"text/javascript\">top.location = 'http{$ssl_suffix}://".$http_host.$_SERVER['PHP_SELF']."?id={$form_id}&mf_page={$origin_page_num}'</script>";
			exit;
		}else if(!empty($_POST['form_id_redirect'])){ //form payment being submitted
			$paid_form_id = (int) trim($_POST['form_id_redirect']);
			
			if($_SESSION['mf_payment_completed'][$paid_form_id] === true){
				//when payment succeeded, $paid_form_id should contain the form id number
				$form_properties = mf_get_form_properties($dbh,$paid_form_id,array('form_redirect_enable','form_redirect','form_review','form_page_total','payment_delay_notifications'));
				
				//process any delayed notifications
				mf_process_delayed_notifications($dbh,$paid_form_id,$_SESSION['mf_payment_record_id'][$paid_form_id]);
				
				//redirect to the default success page or the custom redirect URL being set on form properties
				if(!empty($form_properties['form_redirect_enable']) && !empty($form_properties['form_redirect'])){

					//parse redirect URL for any template variables first
					$form_properties['form_redirect'] = mf_parse_template_variables($dbh,$paid_form_id,$_SESSION['mf_payment_record_id'][$paid_form_id],$form_properties['form_redirect']);
					
					echo "<script type=\"text/javascript\">top.location = '{$form_properties['form_redirect']}'</script>";
					exit;
				}else{
					if(strpos($_SERVER['REQUEST_URI'],'?') === false){
						echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}?done=1'</script>";
					}else{
						echo "<script type=\"text/javascript\">top.location = '{$_SERVER['REQUEST_URI']}&done=1'</script>";
					}
					exit;
				}
			}else{
				$markup = 'You are not authorized to access this page.';
			}
		}else if(!empty($_GET['show_review'])){ //show review page
				if(empty($_SESSION['review_id'])){
					die("Your session has been expired. Please start again.");
				}else{
					$record_id = $_SESSION['review_id'];
				}
				
				$from_page_num = (int) $_GET['mf_page_from'];
				if(empty($from_page_num)){
					$form_page_num = 1;
				}
				
				$form_params = array();
				$form_params['integration_method'] = $integration_method;
				$form_params['machform_path'] = $machform_path;
				$form_params['machform_data_path'] = $machform_data_path;

				$markup = mf_display_form_review($dbh,$form_id,$record_id,$from_page_num,$form_params);
		}else if(!empty($_GET['show_payment'])){ //show payment page
			$record_id = $_SESSION['mf_payment_record_id'][$form_id];

			$form_params = array();
			$form_params['integration_method'] = $integration_method;
			$form_params['machform_path'] = $machform_path;
			$form_params['machform_data_path'] = $machform_data_path;

			//if payment token exist, the user is resuming payment from previously unpaid entry
			if(!empty($_GET['pay_token'])){
				$form_params['pay_token'] = $_GET['pay_token'];
			}

			$markup    = mf_display_form_payment($dbh,$form_id,$record_id,$form_params);
		}else{
			$form_id 		= $form_id;
			$page_number	= (int) trim($_GET['mf_page']);
			
			$page_number 	= mf_verify_page_access($form_id,$page_number);
			
			$resume_key		= trim($_GET['mf_resume']);
			if(!empty($resume_key)){
				$_SESSION['mf_form_resume_key'][$form_id] = $resume_key;
			}

			$edit_key		= trim($_GET['mf_edit']);
			if(!empty($edit_key)){
				$_SESSION['mf_form_edit_key'][$form_id] = $edit_key;

				$_SESSION['mf_entry_hash'][$form_id] = array();
				unset($_SESSION['mf_entry_hash'][$form_id]);
			}
			
			if(!empty($_GET['done']) && (!empty($_SESSION['mf_form_completed'][$form_id]) || !empty($_SESSION['mf_form_resume_url'][$form_id]))){
				
				$form_params = array();
				$form_params['integration_method'] = $integration_method;
				$form_params['machform_path'] 	   = $machform_path;
				
				$markup = mf_display_success($dbh,$form_id,$form_params);
			}else{
				$form_params = array();
				$form_params['page_number'] = $page_number;
				$form_params['integration_method'] = $integration_method;
				$form_params['machform_path'] = $machform_path;
				$form_params['machform_data_path'] = $machform_data_path;
				
				$markup = mf_display_form($dbh,$form_id,$form_params);
			}
		}		


		echo $markup;

		
	}

?>