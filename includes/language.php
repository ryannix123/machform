<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	global $mf_lang;
	
	//simple name and extended name
	$mf_lang['name_first']			=	'First';
	$mf_lang['name_middle']			=	'Middle';
	$mf_lang['name_last']			=	'Last';
	$mf_lang['name_title']			=	'Title';
	$mf_lang['name_suffix']			=	'Suffix';
	
	//address
	$mf_lang['address_street']		=	'Street Address';
	$mf_lang['address_street2']		=	'Address Line 2';
	$mf_lang['address_city']		=	'City';
	$mf_lang['address_state']		=	'State / Province / Region';
	$mf_lang['address_zip']			=	'Postal / Zip Code';
	$mf_lang['address_country']		=	'Country';

	//captcha
	$mf_lang['captcha_required']			=	'This field is required. Please enter the letters shown in the image.';
	$mf_lang['captcha_mismatch']			=	'The letters in the image do not match. Try again.';
	$mf_lang['captcha_text_mismatch'] 		=	'Incorrect answer. Please try again.';
	$mf_lang['captcha_error']				=	'Error while processing, please try again.';
	$mf_lang['captcha_simple_image_title']	= 	'Type the letters you see in the image below.';
	$mf_lang['captcha_simple_text_title']	= 	'Spam Protection. Please answer this simple question:';
	
	//date
	$mf_lang['date_dd']				=	'DD';
	$mf_lang['date_mm']				=	'MM';
	$mf_lang['date_yyyy']			=	'YYYY';
	
	//price
	$mf_lang['price_dollar_main']	=	'Dollars';
	$mf_lang['price_dollar_sub']	=	'Cents';
	$mf_lang['price_euro_main']		=	'Euros';
	$mf_lang['price_euro_sub']		=	'Cents';
	$mf_lang['price_pound_main']	=	'Pounds';
	$mf_lang['price_pound_sub']		=	'Pence';
	$mf_lang['price_yen']			=	'Yen';
	$mf_lang['price_baht_main']		=	'Baht';
	$mf_lang['price_baht_sub']		=	'Satang';
	$mf_lang['price_rupees_main']	=	'Rupees';
	$mf_lang['price_rupees_sub']	=	'Paise';
	$mf_lang['price_rand_main']		=	'Rand';
	$mf_lang['price_rand_sub']		=	'Cents';
	$mf_lang['price_forint_main']	=	'Forint';
	$mf_lang['price_forint_sub']	=	'Filler';
	$mf_lang['price_franc_main']	=	'Francs';
	$mf_lang['price_franc_sub']		=	'Rappen';
	$mf_lang['price_koruna_main']	=	'Koruna';
	$mf_lang['price_koruna_sub']	=	'Haléřů';
	$mf_lang['price_krona_main']	=	'Kroner';
	$mf_lang['price_krona_sub']		=	'Øre';
	$mf_lang['price_leu_main']		=	'Lei';
	$mf_lang['price_leu_sub']		=	'Bani';
	$mf_lang['price_pesos_main']	=	'Pesos';
	$mf_lang['price_pesos_sub']		=	'Cents';
	$mf_lang['price_ringgit_main']	=	'Ringgit';
	$mf_lang['price_ringgit_sub']	=	'Sen';
	$mf_lang['price_reais_main']	=	'Reais';
	$mf_lang['price_reais_sub']		=	'Centavos';
	$mf_lang['price_zloty_main']	=	'Złoty';
	$mf_lang['price_zloty_sub']		=	'Grosz';
	$mf_lang['price_riyals_main']	=	'Riyals';
	$mf_lang['price_riyals_sub']	=	'Halalah';
	
	//time
	$mf_lang['time_hh']				=	'HH';
	$mf_lang['time_mm']				=	'MM';
	$mf_lang['time_ss']				=	'SS';
	
	//error message
	$mf_lang['error_title']			=	'There was a problem with your submission.';
	$mf_lang['error_desc']			=	'Errors have been <strong>highlighted</strong> below.';
	
	//form buttons
	$mf_lang['submit_button']		=	'Submit';
	$mf_lang['continue_button']		=	'Continue';
	$mf_lang['back_button']			=	'Previous';
	
	//form status
	$mf_lang['form_inactive']		=	'This form is currently inactive.';
	$mf_lang['form_limited']		=   'Sorry, but this form is no longer accepting any entries.';
	
	//form password
	$mf_lang['form_pass_title']		=	'This form is password protected.';
	$mf_lang['form_pass_desc']		=	'Please enter your password.';
	$mf_lang['form_pass_invalid']	=	'Invalid Password!';
	
	//form review
	$mf_lang['review_title']		=	'Review Your Entry';
	$mf_lang['review_message']		=	'Please review your entry below. Click Submit button to finish.';
	
	//validation message 
	$mf_lang['val_required'] 		=	'This field is required. Please enter a value.';
	$mf_lang['val_required_file'] 	=	'This field is required. Please upload a file.';
	$mf_lang['val_unique'] 			=	'This field requires a unique entry and this value has already been used.';
	$mf_lang['val_integer'] 		=	'This field must be an integer.';
	$mf_lang['val_float'] 			=	'This field must be a float.';
	$mf_lang['val_numeric'] 		=	'This field must be a number.';
	$mf_lang['val_email'] 			=	'This field is not in the correct email format.';
	$mf_lang['val_website'] 		=	'This field is not in the correct website address format.';
	$mf_lang['val_username'] 		=	'This field may only consist of a-z 0-9 and underscores.';
	$mf_lang['val_equal'] 			=	'%s must match.';
	$mf_lang['val_equal_email'] 	=	'Email addresses must match.';
	$mf_lang['val_date'] 			=	'This field is not in the correct date format.';
	$mf_lang['val_date_range'] 		=	'This date field must be between %s and %s.';
	$mf_lang['val_date_min'] 		=	'This date field must be greater than or equal to %s.';
	$mf_lang['val_date_max'] 		=	'This date field must be less than or equal to %s.';
	$mf_lang['val_date_na'] 		=	'This date is not available for selection.';
	$mf_lang['val_time'] 			=	'This field is not in the correct time format.';
	$mf_lang['val_phone'] 			=	'Please enter a valid phone number.';
	$mf_lang['val_filetype']		=	'The filetype you are attempting to upload is not allowed.';

	$mf_lang['val_choice_atleast'] 	= 	'Minimum of %s choices required.'; 
	$mf_lang['val_choice_atmost']  	= 	'Maximum of %s choices allowed.';
	$mf_lang['val_choice_exactly'] 	= 	'Must be exactly %s choices.';
	$mf_lang['val_choice_between'] 	= 	'Must be between %s and %s choices.';
	
	//fields on excel/csv
	$mf_lang['export_num']			=	'No.';
	$mf_lang['date_created']		=	'Date Created';
	$mf_lang['date_updated']		=	'Date Updated';
	$mf_lang['ip_address']			=	'IP Address';

	//form resume
	$mf_lang['resume_email_subject']		= 'Your submission to {form_name} has been saved';
	$mf_lang['resume_email_content'] 		= 'Thank you! Your submission to <b>{form_name}</b> has been saved.<br /><br />You can resume the form at any time by clicking the link below:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>IMPORTANT:</b><br />Your submission is considered incomplete until you resume the form and press the submit button.';							

	$mf_lang['resume_success_title']   		= 'Your progress has been saved.';
	$mf_lang['resume_success_content'] 		= 'Please copy the link below and save it in a safe place:<br/>%s<br/><br/>You can resume the form at any time by going to the above link.';

	$mf_lang['resume_checkbox_title']		= 'Save my progress and resume later';
	$mf_lang['resume_email_input_label']	= 'Enter Your Email Address';
	$mf_lang['resume_submit_button_text']	= 'Save form and resume later';
	$mf_lang['resume_guideline']			= 'A special link to resume the form will be sent to your email address.';

	//range validation
	$mf_lang['range_type_digit']			= 'digits';
	$mf_lang['range_type_chars'] 			= 'characters';
	$mf_lang['range_type_words'] 			= 'words';

	$mf_lang['range_min']  					= 'Minimum of %s required.'; 
	$mf_lang['range_min_entered']   		= 'Currently Entered: %s.';

	$mf_lang['range_max']					= 'Maximum of %s allowed.';
	$mf_lang['range_max_entered']   		= 'Currently Entered: %s.';

	$mf_lang['range_min_max'] 				= 'Must be between %s and %s.';
	$mf_lang['range_min_max_same'] 			= 'Must be %s.';
	$mf_lang['range_min_max_entered'] 		= 'Currently Entered: %s.';

	$mf_lang['range_number_min']	 		= 'Must be a number greater than or equal to %s.';
	$mf_lang['range_number_max']	 		= 'Must be a number less than or equal to %s.';
	$mf_lang['range_number_min_max'] 		= 'Must be a number between %s and %s';

	//file uploads
	$mf_lang['file_queue_limited'] 			= 'This field is limited to maximum %s files.';
	$mf_lang['file_upload_max']	   			= 'Error. Maximum %sMB allowed.';
	$mf_lang['file_type_limited']  			= 'Error. This file type is not allowed.';
	$mf_lang['file_error_upload']  			= 'Error! Unable to upload';
	$mf_lang['file_attach']		  			= 'Attach Files';
	$mf_lang['file_select']		  			= 'Select Files';

	//payment page
	$mf_lang['payment_card_title']			= 'Credit/Debit Card';
	$mf_lang['payment_total'] 				= 'Total';
	$mf_lang['form_payment_header_title'] 	= 'Payment';
	$mf_lang['form_payment_title'] 			= 'Enter Payment Information';
	$mf_lang['form_payment_description'] 	= 'Please review the details below before entering payment information.';
	$mf_lang['payment_submit_button']		= 'Submit Payment';
	$mf_lang['tax']							= 'Tax';
	$mf_lang['setup_fee']					= 'Setup Fee';

	//payment details
	$mf_lang['payment_status']	 = 'Status';
	$mf_lang['payment_id']		 = 'Payment ID';
	$mf_lang['payment_date']	 = 'Payment Date';
	$mf_lang['payment_fullname'] = 'Full Name';
	$mf_lang['payment_shipping'] = 'Shipping Address';
	$mf_lang['payment_billing']	 = 'Billing Address';	

	//multipage
	$mf_lang['page_title']					= 'Page %s of %s';

	//coupon code
	$mf_lang['coupon_not_exist'] = "This coupon code does not exist.";
	$mf_lang['coupon_max_usage'] = "This coupon has reached the maximum redemption limit.";
	$mf_lang['coupon_expired']	 = "This coupon code has been expired.";
	$mf_lang['discount']		 = 'Discount';

	//editing completed entry
	$mf_lang['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
	$mf_lang['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
	$mf_lang['entry_edit_button_text']  = "Edit Response";
	$mf_lang['entry_edit_user_info_msg']  = "You're editing your response.";
	$mf_lang['entry_edit_user_info_btn']  = "Open Blank Form";

	//signature field
	$mf_lang['signature_draw']	 = 'Draw';
	$mf_lang['signature_or'] 	 = 'or';
	$mf_lang['signature_type']	 = 'Type';
	$mf_lang['signature_clear']	 = 'Clear';

	//others
	$mf_lang['choice_empty']	 = 'No more choices left';
	$mf_lang['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

	/** Functions **/

	//this function set the global language variable ($mf_language) to the selected language
	function mf_set_language($target_language){
		global $mf_lang;

		if(empty($target_language)){
			$target_language = 'english';
		}
		$target_language = strtolower($target_language);

		if($target_language == 'english'){
			//simple name and extended name
			$languages['name_first']			= 'First';
			$languages['name_middle']			= 'Middle';
			$languages['name_last']				= 'Last';
			$languages['name_title']			= 'Title';
			$languages['name_suffix']			= 'Suffix';
			
			//address
			$languages['address_street']		= 'Street Address';
			$languages['address_street2']		= 'Address Line 2';
			$languages['address_city']			= 'City';
			$languages['address_state']			= 'State / Province / Region';
			$languages['address_zip']			= 'Postal / Zip Code';
			$languages['address_country']		= 'Country';

			//captcha
			$languages['captcha_required']				= 'This field is required. Please enter the letters shown in the image.';
			$languages['captcha_mismatch']				= 'The letters in the image do not match. Try again.';
			$languages['captcha_text_mismatch'] 		= 'Incorrect answer. Please try again.';
			$languages['captcha_error']					= 'Error while processing, please try again.';
			$languages['captcha_simple_image_title']	= 'Type the letters you see in the image below.';
			$languages['captcha_simple_text_title']		= 'Spam Protection. Please answer this simple question:';
			
			//date
			$languages['date_dd']				= 'DD';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'YYYY';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'HH';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'There was a problem with your submission.';
			$languages['error_desc']			=	'Errors have been <strong>highlighted</strong> below.';
			
			//form buttons
			$languages['submit_button']			=	'Submit';
			$languages['continue_button']		=	'Continue';
			$languages['back_button']			=	'Previous';
			
			//form status
			$languages['form_inactive']			=	'This form is currently inactive.';
			$languages['form_limited']			=   'Sorry, but this form is no longer accepting any entries.';
			
			//form password
			$languages['form_pass_title']		=	'This form is password protected.';
			$languages['form_pass_desc']		=	'Please enter your password.';
			$languages['form_pass_invalid']		=	'Invalid Password!';
			
			//form review
			$languages['review_title']			=	'Review Your Entry';
			$languages['review_message']		=	'Please review your entry below. Click Submit button to finish.';
			
			//validation message 
			$languages['val_required'] 			=	'This field is required. Please enter a value.';
			$languages['val_required_file'] 	=	'This field is required. Please upload a file.';
			$languages['val_unique'] 			=	'This field requires a unique entry and this value has already been used.';
			$languages['val_integer'] 			=	'This field must be an integer.';
			$languages['val_float'] 			=	'This field must be a float.';
			$languages['val_numeric'] 			=	'This field must be a number.';
			$languages['val_email'] 			=	'This field is not in the correct email format.';
			$languages['val_website'] 			=	'This field is not in the correct website address format.';
			$languages['val_username'] 			=	'This field may only consist of a-z 0-9 and underscores.';
			$languages['val_equal'] 			=	'%s must match.';
			$languages['val_equal_email'] 		=	'Email addresses must match.';
			$languages['val_date'] 				=	'This field is not in the correct date format.';
			$languages['val_date_range'] 		=	'This date field must be between %s and %s.';
			$languages['val_date_min'] 			=	'This date field must be greater than or equal to %s.';
			$languages['val_date_max'] 			=	'This date field must be less than or equal to %s.';
			$languages['val_date_na'] 			=	'This date is not available for selection.';
			$languages['val_time'] 				=	'This field is not in the correct time format.';
			$languages['val_phone'] 			=	'Please enter a valid phone number.';
			$languages['val_filetype']			=	'The filetype you are attempting to upload is not allowed.';

			$languages['val_choice_atleast'] 	= 	'Minimum of %s choices required.'; 
			$languages['val_choice_atmost']  	= 	'Maximum of %s choices allowed.';
			$languages['val_choice_exactly'] 	= 	'Must be exactly %s choices.';
			$languages['val_choice_between'] 	= 	'Must be between %s and %s choices.';
			
			//fields on excel/csv
			$languages['export_num']			=	'No.';
			$languages['date_created']			=	'Date Created';
			$languages['date_updated']			=	'Date Updated';
			$languages['ip_address']			=	'IP Address';

			//form resume
			$languages['resume_email_subject']		= 'Your submission to {form_name} has been saved';
			$languages['resume_email_content'] 		= 'Thank you! Your submission to <b>{form_name}</b> has been saved.<br /><br />You can resume the form at any time by clicking the link below:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>IMPORTANT:</b><br />Your submission is considered incomplete until you resume the form and press the submit button.';							

			$languages['resume_success_title']   	= 'Your progress has been saved.';
			$languages['resume_success_content'] 	= 'Please copy the link below and save it in a safe place:<br/>%s<br/><br/>You can resume the form at any time by going to the above link.';

			$languages['resume_checkbox_title']		= 'Save my progress and resume later';
			$languages['resume_email_input_label']	= 'Enter Your Email Address';
			$languages['resume_submit_button_text']	= 'Save form and resume later';
			$languages['resume_guideline']			= 'A special link to resume the form will be sent to your email address.';

			//range validation
			$languages['range_type_digit']			= 'digits';
			$languages['range_type_chars'] 			= 'characters';
			$languages['range_type_words'] 			= 'words';

			$languages['range_min']  				= 'Minimum of %s required.'; 
			$languages['range_min_entered']   		= 'Currently Entered: %s.';

			$languages['range_max']					= 'Maximum of %s allowed.';
			$languages['range_max_entered']   		= 'Currently Entered: %s.';

			$languages['range_min_max'] 			= 'Must be between %s and %s.';
			$languages['range_min_max_same'] 		= 'Must be %s.';
			$languages['range_min_max_entered'] 	= 'Currently Entered: %s.';

			$languages['range_number_min']	 		= 'Must be a number greater than or equal to %s.';
			$languages['range_number_max']	 		= 'Must be a number less than or equal to %s.';
			$languages['range_number_min_max'] 		= 'Must be a number between %s and %s';

			//file uploads
			$languages['file_queue_limited'] 		= 'This field is limited to maximum %s files.';
			$languages['file_upload_max']	   		= 'Error. Maximum %sMB allowed.';
			$languages['file_type_limited']  		= 'Error. This file type is not allowed.';
			$languages['file_error_upload']  		= 'Error! Unable to upload';
			$languages['file_attach']		  		= 'Attach Files';
			$languages['file_select']		  		= 'Select Files';

			//payment page
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Total';
			$languages['form_payment_header_title'] = 'Payment';
			$languages['form_payment_title'] 		= 'Enter Payment Information';
			$languages['form_payment_description'] 	= 'Please review the details below before entering payment information.';
			$languages['payment_submit_button']		= 'Submit Payment';
			$languages['tax']						= 'Tax';
			$languages['setup_fee']					= 'Setup Fee';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'Payment ID';
			$languages['payment_date']	 	= 'Payment Date';
			$languages['payment_fullname'] 	= 'Full Name';
			$languages['payment_shipping'] 	= 'Shipping Address';
			$languages['payment_billing']	= 'Billing Address';	

			//coupon code
			$languages['coupon_not_exist'] = "This coupon code does not exist.";
			$languages['coupon_max_usage'] = "This coupon has reached the maximum redemption limit.";
			$languages['coupon_expired']   = "This coupon code has been expired.";
			$languages['discount']		   = "Discount";

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";

			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= 'Page %s of %s';
		}else if($target_language == 'dutch'){
			//simple name and extended name
			$languages['name_first']			= 'Voornaam';
			$languages['name_middle']			= 'Tussenvoegsel';
			$languages['name_last']				= 'Achternaam';
			$languages['name_title']			= 'Titel';
			$languages['name_suffix']			= 'Achtervoegsel';
			
			//address
			$languages['address_street']		= 'Adres en huisnummer';
			$languages['address_street2']		= 'Adresregel 2';
			$languages['address_city']			= 'Woonplaats';
			$languages['address_state']			= 'Provincie';
			$languages['address_zip']			= 'Postcode';
			$languages['address_country']		= 'Land';

			//captcha
			$languages['captcha_required']				= 'Dit veld is verplicht. Vul de letters in die u ziet in de afbeelding';
			$languages['captcha_mismatch']				= 'De letters komen niet overeen. Probeer het opnieuw.';
			$languages['captcha_text_mismatch'] 		= 'Verkeerde antwoord. Probeer het opnieuw.';
			$languages['captcha_error']					= 'Fout bij het verwerken, probeer het opnieuw.';
			$languages['captcha_simple_image_title']	= 'Typ de letters in die u in onderstaande afbeelding ziet.';
			$languages['captcha_simple_text_title']		= 'Spambescherming. Beantwoord deze eenvoudige vraag:';
			
			//date
			$languages['date_dd']				= 'DD';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'JJJJ';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'UU';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Er is een probleem met uw inzending.';
			$languages['error_desc']			=	'Fouten zijn hieronder <strong>gemarkeerd</strong>.';
			
			//form buttons
			$languages['submit_button']			=	'Bevestig';
			$languages['continue_button']		=	'Doorgaan';
			$languages['back_button']			=	'Vorige';
			
			//form status
			$languages['form_inactive']			=	'Dit formulier is op dit moment niet actief.';
			$languages['form_limited']			=   'Sorry, maar dit formulier kan niet langer inzendingen verwerken.';
			
			//form password
			$languages['form_pass_title']		=	'Dit formulier is beveiligd met een wachtwoord.';
			$languages['form_pass_desc']		=	'Vul uw wachtwoord in.';
			$languages['form_pass_invalid']		=	'Ongeldig wachtwoord!';
			
			//form review
			$languages['review_title']			=	'Herzie uw inzending';
			$languages['review_message']		=	'Gelieve de onderstaande inzending te herzien. Klik op Bevestig om te voltooien.';
			
			//validation message 
			$languages['val_required'] 			=	'Dit veld is verplicht. Geef een waarde in.';
			$languages['val_required_file'] 	=	'Dit veld is verplicht. Upload een bestand.';
			$languages['val_unique'] 			=	'Dit veld vereist een unieke invoer en deze waarde is al gebruikt.';
			$languages['val_integer'] 			=	'Dit veld een geheel getal zijn.';
			$languages['val_float'] 			=	'Dit veld moet een float zijn.';
			$languages['val_numeric'] 			=	'Dit veld moet een getal zijn.';
			$languages['val_email'] 			=	'Dit veld heeft niet het juiste e-mail formaat.';
			$languages['val_website'] 			=	'Dit veld heeft niet het juiste website adresformaat.';
			$languages['val_username'] 			=	'Dit veld mag alleen bestaan uit a-z, 0-9 en underscores.';
			$languages['val_equal'] 			=	'%s moeten overeenkomen.';
			$languages['val_equal_email'] 		=	'E-mailadressen moeten overeenkomen.';
			$languages['val_date'] 				=	'Dit veld heeft niet het juiste datumformaat.';
			$languages['val_date_range'] 		=	'Dit datumveld moet tussen %s en %s zijn.';
			$languages['val_date_min'] 			=	'Dit datumveld moet groter zijn dan of gelijk aan %s zijn.';
			$languages['val_date_max'] 			=	'Dit datumveld moet kleiner zijn dan of gelijk aan %s zijn.';
			$languages['val_date_na'] 			=	'Deze datum kan niet worden geselecteerd.';
			$languages['val_time'] 				=	'Dit veld heeft niet het juiste tijdformaat.';
			$languages['val_phone'] 			=	'Vul een geldig telefoonnummer in.';
			$languages['val_filetype']			=	'Het bestandstype dat u probeert te uploaden is niet toegestaan.';

			$languages['val_choice_atleast'] 	= 	'Minimaal %s nodig zijn.'; 
			$languages['val_choice_atmost']  	= 	'Maximaal %s toegestaan.';
			$languages['val_choice_exactly'] 	= 	'Moet %s.';
			$languages['val_choice_between'] 	= 	'Moet tussen %s en %s hebben.';
			
			//fields on excel/csv
			$languages['export_num']			=	'Nee.';
			$languages['date_created']			=	'Aanmaakdatum';
			$languages['date_updated']			=	'Datum bijgewerkt';
			$languages['ip_address']			=	'IP-adres';

			//form resume
			$languages['resume_email_subject']		= 'Uw inschrijving via het {form_name} formulier is opgeslagen.';
			$languages['resume_email_content'] 		= 'Dank u wel! Uw inschrijving via <b>{form_name}</b> is opgeslagen.<br /><br />U kunt het formulier te allen tijde opnieuw opvragen door te klikken op de onderstaande link:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>BELANGRIJK:</b><br />Uw inschrijving wordt beschouwd als onvolledig totdat u het formulier opnieuw opvraagt en op bevestigen klikt.';							

			$languages['resume_success_title']   	= 'Uw vooruitgang is opgeslagen.';
			$languages['resume_success_content'] 	= 'Kopieer de onderstaande link en sla het op op een veilige plaats:<br/>%s<br/><br/>U kunt het formulier te allen tijde opnieuw opvragen door naar de bovenstaande link te gaan.';

			$languages['resume_checkbox_title']		= 'Bewaar mijn vooruitgang en hervat later';
			$languages['resume_email_input_label']	= 'Vul Uw e-mailadres In';
			$languages['resume_submit_button_text']	= 'Sla formulier op en hervat later';
			$languages['resume_guideline']			= 'Een speciale link om het formulier opnieuw op te vragen zal naar uw e-mailadres worden verzonden';

			//range validation
			$languages['range_type_digit']			= 'cijfers';
			$languages['range_type_chars'] 			= 'tekens';
			$languages['range_type_words'] 			= 'woorden';

			$languages['range_min']  				= 'Minimaal %s nodig zijn.'; 
			$languages['range_min_entered']   		= 'Op dit moment ingevuld: %s.';

			$languages['range_max']					= 'Maximaal %s toegestaan.';
			$languages['range_max_entered']   		= 'Op dit moment ingevuld: %s.';

			$languages['range_min_max'] 			= 'Moet tussen %s en %s hebben.';
			$languages['range_min_max_same'] 		= 'Moet %s.';
			$languages['range_min_max_entered'] 	= 'Op dit moment ingevuld: %s.';

			$languages['range_number_min']	 		= 'Moet een getal groter dan of gelijk aan %s zijn.';
			$languages['range_number_max']	 		= 'Moet een getal kleiner dan of gelijk aan %s zijn.';
			$languages['range_number_min_max'] 		= 'Moet een getal tussen %s en %s zijn.';

			//file uploads
			$languages['file_queue_limited'] 		= 'Dit veld is beperkt tot maximaal %s bestanden.';
			$languages['file_upload_max']	   		= 'Fout. Maximaal %s MB toegestaan.';
			$languages['file_type_limited']  		= 'Fout. Dit bestandstype is niet toegestaan.';
			$languages['file_error_upload']  		= 'Fout! Niet in staat om te uploaden';
			$languages['file_attach']		  		= 'Bevestig bestanden';
			$languages['file_select']		  		= 'Selecteer bestanden';

			//payment total
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Totaal';
			$languages['form_payment_header_title'] = 'Betaling';
			$languages['form_payment_title'] 		= 'Voer betaalgegevens in';
			$languages['form_payment_description'] 	= 'Check a.u.b. de ingegeven informatie voordat u betaalgegevens invoert.';
			$languages['payment_submit_button']		= 'Betaal nu';
			$languages['tax']						= 'Belasting';
			$languages['setup_fee']					= 'Inregelkosten';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'Betalings ID';
			$languages['payment_date']	 	= 'Betaaldatum';
			$languages['payment_fullname'] 	= 'Volledige naam';
			$languages['payment_shipping'] 	= 'Verzendadres';
			$languages['payment_billing']	= 'Factuuradres';

			//coupon code
			$languages['coupon_not_exist'] = "Deze coupon code is niet bekend.";
			$languages['coupon_max_usage'] = "Deze coupon code heeft het maximum aantal ingaven bereikt.";
			$languages['coupon_expired']   = "Deze coupon code is niet langer geldig.";
			$languages['discount']		   = "Korting";		

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Wijzigen is niet meer mogelijk. Het maximum aantal aanpassingen is gebruikt.";
			$languages['entry_edit_link_expired'] = "Wijzigen is niet meer mogelijk. De link die u heeft gebruikt is niet langer geldig.";
			$languages['entry_edit_button_text']  = "Wijzig antwoor";
			$languages['entry_edit_user_info_msg']  = "U gaat nu uw reactie wijzigen.";
			$languages['entry_edit_user_info_btn']  = "Open leeg formulier";
			
			//signature field
			$languages['signature_draw']  = 'Plaats handtekening';
			$languages['signature_or'] 	  = 'of';
			$languages['signature_type']  = 'Typ uw naam';
			$languages['signature_clear'] = 'Opnieuw';

			//others
			$languages['choice_empty']	   = 'Er zijn geen keuzemogelijkheden meer over.';
			$languages['choice_max_entry'] = 'De keuze die u heeft gemaakt is niet meer beschikbaar. Gelieve een nieuwe keus te maken.';

			//multipage
			$languages['page_title']				= 'Pagina %s van %s';
		}else if($target_language == 'french'){
			//simple name and extended name
			$languages['name_first']			= 'Prénom';
			$languages['name_middle']			= 'Autres prénoms';
			$languages['name_last']				= 'Nom de famille';
			$languages['name_title']			= 'Titre';
			$languages['name_suffix']			= 'Suffixe';
			
			//address
			$languages['address_street']		= 'Nom de rue';
			$languages['address_street2']		= 'Complément d\'adresse';
			$languages['address_city']			= 'Ville';
			$languages['address_state']			= 'Département';
			$languages['address_zip']			= 'Code postal';
			$languages['address_country']		= 'Pays';

			//captcha
			$languages['captcha_required']				= 'Ce champ est obligatoire. Veuillez entrer les lettres que vous voyez sur l\'image.';
			$languages['captcha_mismatch']				= 'Ce que vous n\'avez tapé ne correspond pas à l\'image. Veuillez recommencer.';
			$languages['captcha_text_mismatch'] 		= 'Mauvaise réponse. Veuillez réessayer.';
			$languages['captcha_error']					= 'Une erreur est survenue, veuillez réessayer.';
			$languages['captcha_simple_image_title']	= 'Entrez ci-dessous les lettres telles que vous les voyez dans l\'image';
			$languages['captcha_simple_text_title']		= 'Protection contre les spams. Veuillez répondre à cette question simple:';
			
			//date
			$languages['date_dd']				= 'JJ';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'AAAA';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'HH';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Votre application a rencontré un problème.';
			$languages['error_desc']			=	'Les erreurs ont été <strong>surlignées</strong> ci-dessous.';
			
			//form buttons
			$languages['submit_button']			=	'Envoyer';
			$languages['continue_button']		=	'Continuer';
			$languages['back_button']			=	'Précédent';
			
			//form status
			$languages['form_inactive']			=	'Ce formulaire est actuellement inactif.';
			$languages['form_limited']			=   'Désolé, mais ce formulaire n\'est désormais plus accepté.';
			
			//form password
			$languages['form_pass_title']		=	'Ce formulaire est protégé par un mot de passe.';
			$languages['form_pass_desc']		=	'Veuillez entrer votre mot de passe.';
			$languages['form_pass_invalid']		=	'Mot de passe invalide!';
			
			//form review
			$languages['review_title']			=	'Commenter Votre Billet';
			$languages['review_message']		=	'Veuillez relire votre billet. Cliquer sur envoyer pour achever le processus.';
			
			//validation message 
			$languages['val_required'] 			=	'Ce champ est obligatoire. Veuillez entrer une valeur.';
			$languages['val_required_file'] 	=	'Ce champ est obligatoire. Veuillez envoyer un fichier.';
			$languages['val_unique'] 			=	'Ce champ nécessite une unique entrée et cette valeur a déjà été utilisée.';
			$languages['val_integer'] 			=	'Ce champ doit être un intégré.';
			$languages['val_float'] 			=	'Ce champ doit être flottant.';
			$languages['val_numeric'] 			=	'Ce champ doit être un nombre.';
			$languages['val_email'] 			=	'Ce champ n\'est pas dans le bon format e-mail.';
			$languages['val_website'] 			=	'Ce champ ne contient pas d\'adresse email au format correct.';
			$languages['val_username'] 			=	'Ce champ ne peut contenir que des lettres et chiffres, a-z, 0-9, et _.';
			$languages['val_equal'] 			=	'%s doivent correspondre.';
			$languages['val_equal_email'] 		=	'E-mail doivent correspondre.';
			$languages['val_date'] 				=	'Ce champ n\'est pas au bon format de date.';
			$languages['val_date_range'] 		=	'Ce champ doit rester entre %s et %s.';
			$languages['val_date_min'] 			=	'Ce champ doit être supérieur ou égal à %s.';
			$languages['val_date_max'] 			=	'Ce champ doit être inférieur ou égal à %s.';
			$languages['val_date_na'] 			=	'Cette date n\'est pas disponible pour la sélection.';
			$languages['val_time'] 				=	'Ce champ n\'est pas au bon format de temps.';
			$languages['val_phone'] 			=	'Veuillez entrer un numéro de téléphone valide.';
			$languages['val_filetype']			=	'Le type de fichier que vous tentez de télécharger n\'est pas supporté.';

			$languages['val_choice_atleast'] 	= 	'%s minimum nécessaires.';
			$languages['val_choice_atmost']  	= 	'%s maximum autorisés.';
			$languages['val_choice_exactly'] 	= 	'Doit être %s.';
			$languages['val_choice_between'] 	= 	'Le nombre doit être compris entre %s et %s.';
			
			//fields on excel/csv
			$languages['export_num']			=	'Non.';
			$languages['date_created']			=	'Date de création';
			$languages['date_updated']			=	'Date de téléchargement';
			$languages['ip_address']			=	'Adresse IP';

			//form resume
			$languages['resume_email_subject']		= 'Votre application pour le formulaire {form_name} a été sauvegardée.';
			$languages['resume_email_content'] 		= 'Merci! Votre soumission à <b>{form_name}</b> a été acceptée. <br /><br />Vous pouvez fermer le formulaire à n\'importe quel moment en cliquant ci-dessous:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>IMPORTANT:</b><br />Votre application est considérée comme incomplète tant que vous n\'avez pas validé en cliquant sur le bouton d\'envoi.';							

			$languages['resume_success_title']   	= 'Votre progression a été sauvegardée.';
			$languages['resume_success_content'] 	= 'Veuillez copier le lien ci-dessous et le sauvegarder dans un endroit sûr:<br/>%s<br/><br/>Vous pouvez fermer le formulaire à n\'importe quel moment en cliquant ci-dessus.';

			$languages['resume_checkbox_title']		= 'Sauvegarder ma progression et revenir plus tard';
			$languages['resume_email_input_label']	= 'Entrez Votre Adresse Email';
			$languages['resume_submit_button_text']	= 'Sauvegarder le formulaire et revenir plus tard';
			$languages['resume_guideline']			= 'Un lien spécial pour revenir au formulaire vous sera envoyé par email.';

			//range validation
			$languages['range_type_digit']			= 'chiffres';
			$languages['range_type_chars'] 			= 'caractères';
			$languages['range_type_words'] 			= 'mots';

			$languages['range_min']  				= '%s minimum nécessaires.'; 
			$languages['range_min_entered']   		= 'Actuellement entrés: %s.';

			$languages['range_max']					= '%s maximum autorisés.';
			$languages['range_max_entered']   		= 'Actuellement entrés: %s.';

			$languages['range_min_max'] 			= 'Le nombre doit être compris entre %s et %s.';
			$languages['range_min_max_same'] 		= 'Doit être %s.';
			$languages['range_min_max_entered'] 	= 'Actuellement entrés: %s.';

			$languages['range_number_min']	 		= 'Le nombre doit être supérieur ou égal à %s.';
			$languages['range_number_max']	 		= 'Le nombre doit être inférieur ou égal à %s.';
			$languages['range_number_min_max'] 		= 'Le nombre doit être compris entre %s et %s';

			//file uploads
			$languages['file_queue_limited'] 		= 'Ce champ est limité à %s fichiers.';
			$languages['file_upload_max']	   		= 'Erreur. %sMB Maximum autorisés.';
			$languages['file_type_limited']  		= 'Erreur. Ce type de fichier n\'est pas pris en charge.';
			$languages['file_error_upload']  		= 'Erreur! Téléchargement impossible.';
			$languages['file_attach']		  		= 'Joindre les fichiers';
			$languages['file_select']		  		= 'Sélectionner les fichiers';

			//payment total
			$languages['payment_card_title']		= 'Carte de crédit';
			$languages['payment_total'] 			= 'Total';
			$languages['form_payment_header_title'] = 'Paiement';
			$languages['form_payment_title'] 		= 'Entrez vos informations de paiement';
			$languages['form_payment_description'] 	= 'Veuillez verifier les détails ci-dessous avant de saisir vos informations de paiement.';
			$languages['payment_submit_button']		= 'Valider votre paiement';
			$languages['tax']						= 'Taxes';
			$languages['setup_fee']					= 'Frais';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'Payment ID';
			$languages['payment_date']	 	= 'Payment Date';
			$languages['payment_fullname'] 	= 'Full Name';
			$languages['payment_shipping'] 	= 'Adresse de livraison';
			$languages['payment_billing']	= 'Adresse de facturation';

			//coupon code
			$languages['coupon_not_exist'] = "This coupon code does not exist.";
			$languages['coupon_max_usage'] = "This coupon has reached the maximum redemption limit.";
			$languages['coupon_expired']   = "This coupon code has been expired.";
			$languages['discount']		   = "Discount";		

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= 'Page %s sur %s';
		}else if($target_language == 'german'){
			//simple name and extended name
			$languages['name_first']			= 'Vorname';
			$languages['name_middle']			= '2. Vorname';
			$languages['name_last']				= 'Nachname';
			$languages['name_title']			= 'Titel';
			$languages['name_suffix']			= 'Zusatz';
			
			//address
			$languages['address_street']		= 'Straße, Hausnr.';
			$languages['address_street2']		= '2. Adresszeile';
			$languages['address_city']			= 'Stadt';
			$languages['address_state']			= 'Bundesland / Kanton';
			$languages['address_zip']			= 'PLZ';
			$languages['address_country']		= 'Land';

			//captcha
			$languages['captcha_required']				= 'Dieses Feld ist erforderlich. Geben Sie bitte die im Bild gezeigten Buchstaben ein.';
			$languages['captcha_mismatch']				= 'Die Buchstaben im Bild stimmen nicht überein. Versuchen Sie es erneut.';
			$languages['captcha_text_mismatch'] 		= 'Ungültige Antwort. Versuchen Sie es erneut.';
			$languages['captcha_error']					= 'Fehler in der Verarbeitung, versuchen Sie es bitte erneut.';
			$languages['captcha_simple_image_title']	= 'Geben Sie die Buchstaben aus dem Bild unten ein.';
			$languages['captcha_simple_text_title']		= 'Spamschutz. Beantworten Sie bitte diese einfache Frage:';
			
			//date
			$languages['date_dd']				= 'TT';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'JJJJ';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'HH';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Mit Ihren Eingaben gab es ein Problem.';
			$languages['error_desc']			=	'Fehler sind unten <strong>hervorgehoben</strong>.';
			
			//form buttons
			$languages['submit_button']			=	'Absenden';
			$languages['continue_button']		=	'Weiter';
			$languages['back_button']			=	'Zurück';
			
			//form status
			$languages['form_inactive']			=	'Dieses Formular ist im Moment nicht aktiv.';
			$languages['form_limited']			=   'Dieses Formular nimmt leider keine weiteren Eingaben mehr an.';
			
			//form password
			$languages['form_pass_title']		=	'Dieses Formular ist passwortgeschützt.';
			$languages['form_pass_desc']		=	'Geben Sie bitte Ihr Passwort ein.';
			$languages['form_pass_invalid']		=	'Ungültiges Passwort!';
			
			//form review
			$languages['review_title']			=	'Überprüfen Sie Ihre Eingabe.';
			$languages['review_message']		=	'Überprüfen Sie bitte Ihre Eingabe unten. Klicken Sie zum Beenden auf "Absenden".';
			
			//validation message 
			$languages['val_required'] 			=	'Dieses Feld ist erforderlich. Geben Sie bitte einen Wert ein.';
			$languages['val_required_file'] 	=	'Dieses Feld ist erforderlich. Laden Sie bitte eine Datei hoch.';
			$languages['val_unique'] 			=	'Dieses Feld verlangt eine eindeutige Eingabe, und dieser Wert wurde bereits verwendet.';
			$languages['val_integer'] 			=	'Dieses Feld ist zwingend eine Ganzzahl.';
			$languages['val_float'] 			=	'Dieses Feld ist zwingend eine Gleitkommazahl.';
			$languages['val_numeric'] 			=	'Dieses Feld ist zwingend eine Nummer.';
			$languages['val_email'] 			=	'Dieses Feld enthält kein gültiges Email-Format.';
			$languages['val_website'] 			=	'Dieses Feld enthält kein gültiges Adressformat einer Website.';
			$languages['val_username'] 			=	'Dieses Feld darf nur a-z, 0-9 und Unterstriche enthalten.';
			$languages['val_equal'] 			=	'%s müssen übereinstimmen.';
			$languages['val_equal_email'] 		=	'Email müssen übereinstimmen.';
			$languages['val_date'] 				=	'Dieses Feld enthält kein gültiges Datumsformat.';
			$languages['val_date_range'] 		=	'Dieses Datumsfeld muss zwischen %s und %s liegen.';
			$languages['val_date_min'] 			=	'Dieses Datumsfeld muss größer oder gleich %s sein. ';
			$languages['val_date_max'] 			=	'Dieses Datumsfeld muss kleiner oder gleich %s sein.';
			$languages['val_date_na'] 			=	'Dieses Datum können Sie nicht wählen.';
			$languages['val_time'] 				=	'Dieses Feld enthält kein gültiges Zeit-Format.';
			$languages['val_phone'] 			=	'Geben Sie bitte eine gültige Telefonnummer ein.';
			$languages['val_filetype']			=	'Sie versuchen einen nicht unterstützten Dateityp hochzuladen.';

			$languages['val_choice_atleast'] 	= 	'Mindestens %s erforderlich.'; 
			$languages['val_choice_atmost']  	= 	'Höchstens %s erlaubt.';
			$languages['val_choice_exactly'] 	= 	'Muss %s.';
			$languages['val_choice_between'] 	= 	'%s bis %s erlaubt.';
			
			//fields on excel/csv
			$languages['export_num']			=	'Nein.';
			$languages['date_created']			=	'Erstellungsdatum';
			$languages['date_updated']			=	'Änderungsdatum';
			$languages['ip_address']			=	'IP Adresse';

			//form resume
			$languages['resume_email_subject']		= 'Ihre Angaben zum {form_name} Formular sind gesichert worden.';
			$languages['resume_email_content'] 		= 'Vielen Dank! Ihre Angaben zu <b>{form_name}</b> sind gesichert worden.<br /><br />Sie können jederzeit das Formular wieder aufnehmen, indem Sie auf den Link unten klicken:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>WICHTIG:</b><br />Ihre Angaben gelten als unvollständig, bis Sie das Formular wieder aufnehmen und "Absenden" klicken.';							

			$languages['resume_success_title']   	= 'Ihr aktueller Arbeitsstand ist gesichert worden.';
			$languages['resume_success_content'] 	= 'Kopieren Sie bitte den Link unten und bewahren Sie ihn an einem sicheren Ort auf:<br/>%s<br/><br/>Sie können jederzeit im Formular weiterarbeiten, indem Sie obigen Link aufrufen.';

			$languages['resume_checkbox_title']		= 'Meinen aktuellen Arbeitsstand sichern und später weitermachen';
			$languages['resume_email_input_label']	= 'Geben Sie Ihre Email-Adresse ein';
			$languages['resume_submit_button_text']	= 'Formular sichern und später weitermachen';
			$languages['resume_guideline']			= 'Einen speziellen Link zur Wiederaufnahme des Formulars erhalten Sie unter Ihrer Email-Adresse';

			//range validation
			$languages['range_type_digit']			= 'ziffern';
			$languages['range_type_chars'] 			= 'zeichen';
			$languages['range_type_words'] 			= 'wörter';

			$languages['range_min']  				= 'Mindestens %s erforderlich.'; 
			$languages['range_min_entered']   		= 'Soeben eingegeben: %s.';

			$languages['range_max']					= 'Höchstens %s erlaubt.';
			$languages['range_max_entered']   		= 'Soeben eingegeben: %s.';

			$languages['range_min_max'] 			= '%s bis %s erlaubt.';
			$languages['range_min_max_same'] 		= 'Muss %s.';
			$languages['range_min_max_entered'] 	= 'Soeben eingegeben: %s.';

			$languages['range_number_min']	 		= 'Muss eine Zahl größer oder gleich %s sein.';
			$languages['range_number_max']	 		= 'Muss eine Zahl kleiner oder gleich %s sein.';
			$languages['range_number_min_max'] 		= 'Muss eine Zahl zwischen %s und %s sein.';

			//file uploads
			$languages['file_queue_limited'] 		= 'Dieses Feld ist auf höchstens %s Dateien begrenzt.';
			$languages['file_upload_max']	   		= 'Fehler: Höchstens %sMB erlaubt';
			$languages['file_type_limited']  		= 'Fehler: Dieser Dateityp wird nicht unterstützt';
			$languages['file_error_upload']  		= 'Fehler! Hochladen nicht möglich';
			$languages['file_attach']		  		= 'Dateien anfügen';
			$languages['file_select']		  		= 'Durchsuchen';

			//payment total
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Gesamt';
			$languages['form_payment_header_title'] = 'Payment';
			$languages['form_payment_title'] 		= 'Enter Payment Information';
			$languages['form_payment_description'] 	= 'Please review the details below before entering payment information.';
			$languages['payment_submit_button']		= 'Submit Payment';
			$languages['tax']						= 'Tax';
			$languages['setup_fee']					= 'Setup Fee';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'Payment ID';
			$languages['payment_date']	 	= 'Payment Date';
			$languages['payment_fullname'] 	= 'Full Name';
			$languages['payment_shipping'] 	= 'Shipping Address';
			$languages['payment_billing']	= 'Billing Address';

			//coupon code
			$languages['coupon_not_exist'] = "This coupon code does not exist.";
			$languages['coupon_max_usage'] = "This coupon has reached the maximum redemption limit.";
			$languages['coupon_expired']   = "This coupon code has been expired.";
			$languages['discount']		   = "Discount";		

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= 'Seite %s von %s';
		}else if($target_language == 'italian'){
			//simple name and extended name
			$languages['name_first']			= 'Nome';
			$languages['name_middle']			= 'Secondo nome';
			$languages['name_last']				= 'Cognome';
			$languages['name_title']			= 'Titolo';
			$languages['name_suffix']			= 'Suffisso';
			
			//address
			$languages['address_street']		= 'Indirizzo';
			$languages['address_street2']		= 'Indirizzo (continua)';
			$languages['address_city']			= 'Città';
			$languages['address_state']			= 'Stato / Provincia / Regione';
			$languages['address_zip']			= 'CAP';
			$languages['address_country']		= 'Paese';

			//captcha
			$languages['captcha_required']				= 'Questo campo è obbligatorio. Inserisci le lettere che vedi nell\'immagine.';
			$languages['captcha_mismatch']				= 'Le lettere nell\'immagine non corrispondono. Prova di nuovo.';
			$languages['captcha_text_mismatch'] 		= 'Risposta errata. Riprova.';
			$languages['captcha_error']					= 'Errore durante l\'elaborazione. Riprova.';
			$languages['captcha_simple_image_title']	= 'Digita le lettere che vedi nell\'immagine qui sotto.';
			$languages['captcha_simple_text_title']		= 'Protezione contro lo spam. Rispondi a questa semplice domanda:';
			
			//date
			$languages['date_dd']				= 'GG';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'AAAA';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'OO';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Si è verificato un problema durante l\'invio dei dati.';
			$languages['error_desc']			=	'Gli errori sono <strong>evidenziati</strong> qui di seguito.';
			
			//form buttons
			$languages['submit_button']			=	'Invia';
			$languages['continue_button']		=	'Continua';
			$languages['back_button']			=	'Indietro';
			
			//form status
			$languages['form_inactive']			=	'Questo modulo attualmente non è attivo.';
			$languages['form_limited']			=   'Siamo spiacenti, ma questo modulo non accetta più partecipazioni.';
			
			//form password
			$languages['form_pass_title']		=	'Questo modulo è protetto da password.';
			$languages['form_pass_desc']		=	'Inserisci la tua password.';
			$languages['form_pass_invalid']		=	'Password non valida!';
			
			//form review
			$languages['review_title']			=	'Controlla i dati inseriti';
			$languages['review_message']		=	'Rivedi la tua partecipazione qui di seguito. Fai clic su Invia per inoltrarla.';
			
			//validation message 
			$languages['val_required'] 			=	'Questo campo è obbligatorio. Inserisci un valore.';
			$languages['val_required_file'] 	=	'Questo campo è obbligatorio. Carica un file.';
			$languages['val_unique'] 			=	'Questo campo richiede una voce unica e questo valore è già stato utilizzato.';
			$languages['val_integer'] 			=	'Questo campo deve essere un numero intero.';
			$languages['val_float'] 			=	'Questo campo deve essere un numero decimale.';
			$languages['val_numeric'] 			=	'Questo campo deve essere un numero.';
			$languages['val_email'] 			=	'Questo campo non è nel formato corretto di indirizzo e-mail.';
			$languages['val_website'] 			=	'Questo campo non è nel formato corretto di indirizzo Web.';
			$languages['val_username'] 			=	'Questo campo può essere costituito solo da a-z 0-9 e trattini bassi.';
			$languages['val_equal'] 			=	'%s devono corrispondere.';
			$languages['val_equal_email'] 		=	'E-mail devono corrispondere.';
			$languages['val_date'] 				=	'Questo campo non è nel formato di data corretto.';
			$languages['val_date_range'] 		=	'Questo campo della data deve essere compreso tra %s e %s.';
			$languages['val_date_min'] 			=	'Questo campo della data deve essere maggiore o uguale a %s.';
			$languages['val_date_max'] 			=	'Questo campo della data deve essere minore o uguale a %s.';
			$languages['val_date_na'] 			=	'Questa data non è disponibile per la selezione.';
			$languages['val_time'] 				=	'Questo campo non è nel formato corretto dell\'ora.';
			$languages['val_phone'] 			=	'Inserisci un numero di telefono valido.';
			$languages['val_filetype']			=	'Il tipo di file che stai tentando di caricare non è consentito.';

			$languages['val_choice_atleast'] 	= 	'Minimo %s richieste.'; 
			$languages['val_choice_atmost']  	= 	'Massimo %s consentiti.';
			$languages['val_choice_exactly'] 	= 	'Deve essere di %s.';
			$languages['val_choice_between'] 	= 	'Deve essere compreso tra %s e %s.';
			
			//fields on excel/csv
			$languages['export_num']			=	'No.';
			$languages['date_created']			=	'Data di creazione';
			$languages['date_updated']			=	'Data di aggiornamento';
			$languages['ip_address']			=	'Indirizzo IP';

			//form resume
			$languages['resume_email_subject']		= 'La tua partecipazione a {form_name} è stata salvata.';
			$languages['resume_email_content'] 		= 'Grazie! La tua partecipazione a <b>{form_name}</b> è stata salvata.<br /><br />Puoi riprendere il modulo in qualsiasi momento utilizzando il seguente link:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>ATTENZIONE:</b><br />La tua partecipazione è considerata incompleta fino a quando non riprendi il modulo e premi il pulsante Invia.';							

			$languages['resume_success_title']   	= 'Il tuo progresso è stato salvato.';
			$languages['resume_success_content'] 	= 'Copiate i link qui sotto e salvalo in un luogo sicuro:<br/>%s<br/><br/>Puoi riprendere il modulo in qualsiasi momento accedendo al link qui sopra.';

			$languages['resume_checkbox_title']		= 'Salva il mio progresso e riprendi più tardi';
			$languages['resume_email_input_label']	= 'Inserisci il tuo indirizzo e-mail';
			$languages['resume_submit_button_text']	= 'Salva il modulo e riprendi più tardi';
			$languages['resume_guideline']			= 'Al tuo indirizzo e-mail verrà inviato un link speciale per riprendere il modulo';

			//range validation
			$languages['range_type_digit']			= 'cifre';
			$languages['range_type_chars'] 			= 'caratteri';
			$languages['range_type_words'] 			= 'parole';

			$languages['range_min']  				= 'Minimo %s richieste.'; 
			$languages['range_min_entered']   		= 'Attualmente inserite: %s.';

			$languages['range_max']					= 'Massimo %s consentiti.';
			$languages['range_max_entered']   		= 'Attualmente inserite: %s.';

			$languages['range_min_max'] 			= 'Deve essere compreso tra %s e %s.';
			$languages['range_min_max_same'] 		= 'Deve essere di %s.';
			$languages['range_min_max_entered'] 	= 'Attualmente inserite: %s.';

			$languages['range_number_min']	 		= 'Deve essere un numero maggiore o uguale a %s.';
			$languages['range_number_max']	 		= 'Deve essere un numero inferiore o uguale a %s.';
			$languages['range_number_min_max'] 		= 'Deve essere un numero compreso tra %s e %s.';

			//file uploads
			$languages['file_queue_limited'] 		= 'Questo campo è limitato ad un massimo di %s file.';
			$languages['file_upload_max']	   		= 'Errore. Massimo %s MB consentiti.';
			$languages['file_type_limited']  		= 'Errore. Questo tipo di file non è consentito.';
			$languages['file_error_upload']  		= 'Errore! Impossibile caricare';
			$languages['file_attach']		  		= 'Allega file';
			$languages['file_select']		  		= 'Scegli file';

			//payment total
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Totale';
			$languages['form_payment_header_title'] = 'Payment';
			$languages['form_payment_title'] 		= 'Enter Payment Information';
			$languages['form_payment_description'] 	= 'Please review the details below before entering payment information.';
			$languages['payment_submit_button']		= 'Submit Payment';
			$languages['tax']						= 'Tax';
			$languages['setup_fee']					= 'Setup Fee';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'Payment ID';
			$languages['payment_date']	 	= 'Payment Date';
			$languages['payment_fullname'] 	= 'Full Name';
			$languages['payment_shipping'] 	= 'Shipping Address';
			$languages['payment_billing']	= 'Billing Address';

			//coupon code
			$languages['coupon_not_exist'] = "This coupon code does not exist.";
			$languages['coupon_max_usage'] = "This coupon has reached the maximum redemption limit.";
			$languages['coupon_expired']   = "This coupon code has been expired.";
			$languages['discount']		   = "Discount";		

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= 'Pagina %s di %s';
		}else if($target_language == 'portuguese'){
			//simple name and extended name
			$languages['name_first']			= 'Primeiro';
			$languages['name_middle']			= 'Meio';
			$languages['name_last']				= 'Último';
			$languages['name_title']			= 'Título';
			$languages['name_suffix']			= 'Sufixo';
			
			//address
			$languages['address_street']		= 'Endereço';
			$languages['address_street2']		= 'Endereço linha 2';
			$languages['address_city']			= 'Cidade';
			$languages['address_state']			= 'Estado / Província / Região';
			$languages['address_zip']			= 'Caixa postal / Código postal';
			$languages['address_country']		= 'País';

			//captcha
			$languages['captcha_required']				= 'Esse campo é obrigatório. Por favor digite as letras mostradas na imagem';
			$languages['captcha_mismatch']				= 'As letras não batem com as da imagem. Tente novamente.';
			$languages['captcha_text_mismatch'] 		= 'Resposta incorreta. Por favor tente novamente.';
			$languages['captcha_error']					= 'Erro ao processar, por favor tente novamente.';
			$languages['captcha_simple_image_title']	= 'Digite as letras que vê na figura abaixo.';
			$languages['captcha_simple_text_title']		= 'Proteção anti-spam. Por favor responda a esta pergunta simples:';
			
			//date
			$languages['date_dd']				= 'DD';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'AAAA';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'HH';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Houve um problema com seu envio.';
			$languages['error_desc']			=	'Os erros foram <strong>destacados</strong> abaixo.';
			
			//form buttons
			$languages['submit_button']			=	'Enviar';
			$languages['continue_button']		=	'Continuar';
			$languages['back_button']			=	'Anterior';
			
			//form status
			$languages['form_inactive']			=	'Este formulário está inativo atualmente.';
			$languages['form_limited']			=   'Descupe, mas esse formolário não está aceitando nenhuma entrada mais.';
			
			//form password
			$languages['form_pass_title']		=	'Este formulário é protegido por senha.';
			$languages['form_pass_desc']		=	'Por favor digite sua senha.';
			$languages['form_pass_invalid']		=	'Senha inválida!';
			
			//form review
			$languages['review_title']			=	'Reveja o que digitou';
			$languages['review_message']		=	'Reveja o que digitou abaixo. Clique no botão enviar para finalizar.';
			
			//validation message 
			$languages['val_required'] 			=	'Este campo é necessário. Por favor digite um valor.';
			$languages['val_required_file'] 	=	'Este campo é necessári. Por favor envie um arquivo.';
			$languages['val_unique'] 			=	'Esse campo requer apenas uma entrada e esse valor já foi usado.';
			$languages['val_integer'] 			=	'Este campo deve receber um inteiro.';
			$languages['val_float'] 			=	'Este campo deve receber um decimal.';
			$languages['val_numeric'] 			=	'Este campo deve receber um número.';
			$languages['val_email'] 			=	'Este campo não está no formato de email correto.';
			$languages['val_website'] 			=	'Este campo não está no formato de site correto.';
			$languages['val_username'] 			=	'Este campo deve consistir apenas de a-z e 0-9 e sublinhados.';
			$languages['val_equal'] 			=	'%s não batem.';
			$languages['val_equal_email'] 		=	'Email não batem.';
			$languages['val_date'] 				=	'Este campo não está no formato correto.';
			$languages['val_date_range'] 		=	'Este campo de data deve estar entre XXX e %s.';
			$languages['val_date_min'] 			=	'Este campo de data deve ser maior ou igual a %s.';
			$languages['val_date_max'] 			=	'Este campo de data deve ser menor ou igual a %s.';
			$languages['val_date_na'] 			=	'Esta data não está disponível para seleção.';
			$languages['val_time'] 				=	'Este campo não está no formato de data correto.';
			$languages['val_phone'] 			=	'Por favor entre com um número de telefone válido.';
			$languages['val_filetype']			=	'O tipo de arquivo que está tentando enviar não é permitido.';

			$languages['val_choice_atleast'] 	= 	'Mínimo de %s necessárias.'; 
			$languages['val_choice_atmost']  	= 	'Máximo de %s permitidas.';
			$languages['val_choice_exactly'] 	= 	'Deve ter %s.';
			$languages['val_choice_between'] 	= 	'Precisa ter entre %s e %s.';
			
			//fields on excel/csv
			$languages['export_num']			=	'Não.';
			$languages['date_created']			=	'Dados criados';
			$languages['date_updated']			=	'Dados atualizados';
			$languages['ip_address']			=	'Endereço IP';

			//form resume
			$languages['resume_email_subject']		= 'Seu cadastro de formulário de {form_name} foi salvo.';
			$languages['resume_email_content'] 		= 'Obrigado! Seu cadastro de formulário de <b>{form_name}</b> foi salvo.<br /><br />Você pode continuar o formulário a qualquer momento clicando no link abaixo:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>IMPORTANTE:</b><br />Seu cadastro é considerado incompleto até que você continue o formulário e aperte o botão de envio.';							

			$languages['resume_success_title']   	= 'Seu progresso foi salvo.';
			$languages['resume_success_content'] 	= 'Por favor copie o link abaixo e guarde em local seguro:<br/>%s<br/><br/>Você pode continuar o formulário a qualquer momento clicando no link acima.';

			$languages['resume_checkbox_title']		= 'Salvar meu progresso e continuar mais tarde';
			$languages['resume_email_input_label']	= 'Forneça seu endereço de email';
			$languages['resume_submit_button_text']	= 'Salvar formulário e continuar mais tarde';
			$languages['resume_guideline']			= 'Um link especial para continuar o formulário será enviado para seu email.';

			//range validation
			$languages['range_type_digit']			= 'dígitos';
			$languages['range_type_chars'] 			= 'caracteres';
			$languages['range_type_words'] 			= 'palavras';

			$languages['range_min']  				= 'Mínimo de %s necessárias.'; 
			$languages['range_min_entered']   		= 'Quantidade Fornecida: %s.';

			$languages['range_max']					= 'Máximo de %s permitidas.';
			$languages['range_max_entered']   		= 'Quantidade Fornecida: %s.';

			$languages['range_min_max'] 			= 'Precisa ter entre %s e %s.';
			$languages['range_min_max_same'] 		= 'Deve ter %s.';
			$languages['range_min_max_entered'] 	= 'Quantidade Fornecida: %s.';

			$languages['range_number_min']	 		= 'Precisa ser um número maior ou igual a %s.';
			$languages['range_number_max']	 		= 'Precisa ser um número menor ou igual a %s.';
			$languages['range_number_min_max'] 		= 'Precisa ser um número entre %s e %s.';

			//file uploads
			$languages['file_queue_limited'] 		= 'Este campo está limitado a um máximo de %s arquivos.';
			$languages['file_upload_max']	   		= 'Erro. Máximo de %sMB permitidos.';
			$languages['file_type_limited']  		= 'Erro. Este arquivo não é permitido.';
			$languages['file_error_upload']  		= 'Erro! incapaz de enviar';
			$languages['file_attach']		  		= 'Anexar arquivos';
			$languages['file_select']		  		= 'Select Files';

			//payment total
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Total';
			$languages['form_payment_header_title'] = 'Payment';
			$languages['form_payment_title'] 		= 'Enter Payment Information';
			$languages['form_payment_description'] 	= 'Please review the details below before entering payment information.';
			$languages['payment_submit_button']		= 'Submit Payment';
			$languages['tax']						= 'Tax';
			$languages['setup_fee']					= 'Setup Fee';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'Payment ID';
			$languages['payment_date']	 	= 'Payment Date';
			$languages['payment_fullname'] 	= 'Full Name';
			$languages['payment_shipping'] 	= 'Shipping Address';
			$languages['payment_billing']	= 'Billing Address';

			//coupon code
			$languages['coupon_not_exist'] = "This coupon code does not exist.";
			$languages['coupon_max_usage'] = "This coupon has reached the maximum redemption limit.";
			$languages['coupon_expired']   = "This coupon code has been expired.";
			$languages['discount']		   = "Discount";		

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= 'Página %s de %s';
		}else if($target_language == 'spanish'){
			//simple name and extended name
			$languages['name_first']			= 'Nombre';
			$languages['name_middle']			= 'Segundo';
			$languages['name_last']				= 'Apellidos';
			$languages['name_title']			= 'Título';
			$languages['name_suffix']			= 'Sufijo';
			
			//address
			$languages['address_street']		= 'Dirección';
			$languages['address_street2']		= 'Dirección (continuación)';
			$languages['address_city']			= 'Ciudad';
			$languages['address_state']			= 'Estado / Provincia / Región';
			$languages['address_zip']			= 'Código postal';
			$languages['address_country']		= 'País';

			//captcha
			$languages['captcha_required']				= 'Este campo es obligatorio. Por favor ingrese las letras que aparecen en la imagen.';
			$languages['captcha_mismatch']				= 'Las letras en la imagen no coinciden. Intente de nuevo.';
			$languages['captcha_text_mismatch'] 		= 'Respuesta incorrecta. Por favor intente de nuevo.';
			$languages['captcha_error']					= 'Error durante el procesamiento. Por favor intente de nuevo.';
			$languages['captcha_simple_image_title']	= 'Ingrese las letras que ve en la imagen de abajo.';
			$languages['captcha_simple_text_title']		= 'Protección contra correo no deseado. Por favor responda esta sencilla pregunta:';
			
			//date
			$languages['date_dd']				= 'DD';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'AAAA';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'HH';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Hubo un error con su envío.';
			$languages['error_desc']			=	'Abajo aparecen <strong>destacados</strong> los errores.';
			
			//form buttons
			$languages['submit_button']			=	'Enviar';
			$languages['continue_button']		=	'Continuar';
			$languages['back_button']			=	'Anterior';
			
			//form status
			$languages['form_inactive']			=	'Este formulario ahora está inactivo.';
			$languages['form_limited']			=   'Lo siento, pero este formulario ya no acepta más entradas.';
			
			//form password
			$languages['form_pass_title']		=	'Este formulario está protegido por contraseña.';
			$languages['form_pass_desc']		=	'Por favor ingrese su contraseña.';
			$languages['form_pass_invalid']		=	'¡Contraseña inválida!';
			
			//form review
			$languages['review_title']			=	'Revise su entrada';
			$languages['review_message']		=	'Por favor revise su entrada a continuación. Para terminar, haga clic en el botón Enviar.';
			
			//validation message 
			$languages['val_required'] 			=	'Este campo es obligatorio. Por favor ingrese un valor.';
			$languages['val_required_file'] 	=	'Este campo es obligatorio. Por favor suba un archivo.';
			$languages['val_unique'] 			=	'Este campo requiere una entrada única y este valor ya se ha usado.';
			$languages['val_integer'] 			=	'Este campo debe tener un entero.';
			$languages['val_float'] 			=	'Este campo debe tener un flotante.';
			$languages['val_numeric'] 			=	'Este campo debe tener un número.';
			$languages['val_email'] 			=	'Este campo no tiene el formato correcto de correo electrónico.';
			$languages['val_website'] 			=	'Este campo no tiene el formato correcto de dirección de sitio web.';
			$languages['val_username'] 			=	'Este campo solo puede contener a-z 0-9 y guion bajo.';
			$languages['val_equal'] 			=	'%s deben coincidir.';
			$languages['val_equal_email'] 		=	'Electrónico deben coincidir.';
			$languages['val_date'] 				=	'Este campo no tiene el formato de fecha correcto.';
			$languages['val_date_range'] 		=	'Este campo de fecha debe estar entre %s y %s.';
			$languages['val_date_min'] 			=	'Este campo de fecha debe ser superior o igual a %s.';
			$languages['val_date_max'] 			=	'Este campo de fecha debe ser inferior o igual a %s.';
			$languages['val_date_na'] 			=	'Esta fecha no se puede seleccionar.';
			$languages['val_time'] 				=	'Este campo no tiene el formato de hora correcto.';
			$languages['val_phone'] 			=	'Por favor ingrese un número telefónico válido.';
			$languages['val_filetype']			=	'No se permite el tipo de archivo que intenta subir.';

			$languages['val_choice_atleast'] 	= 	'Obligatorio un mínimo de %s.'; 
			$languages['val_choice_atmost']  	= 	'Se permite un máximo de %s.';
			$languages['val_choice_exactly'] 	= 	'Debe ser de %s.';
			$languages['val_choice_between'] 	= 	'Debe contener entre %s y %s.';
			
			//fields on excel/csv
			$languages['export_num']			=	'No.';
			$languages['date_created']			=	'Fecha creada';
			$languages['date_updated']			=	'Fecha actualizada';
			$languages['ip_address']			=	'Dirección IP';

			//form resume
			$languages['resume_email_subject']		= 'Su envío del formulario {form_name} se ha guardado.';
			$languages['resume_email_content'] 		= '¡Gracias! Su envío de {form_name} se ha guardado.<br /><br />Puede reanudar el formulario en cualquier momento haciendo clic en el enlace siguiente:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>IMPORTANTE:</b><br />Se considera que su envío está incompleto hasta que reanude el formulario y presione el botón de envío.';							

			$languages['resume_success_title']   	= 'Se ha guardado su progreso.';
			$languages['resume_success_content'] 	= 'Por favor copie el enlace siguiente y guárdelo en un lugar seguro:<br/>%s<br/><br/>Puede reanudar el formulario en cualquier momento visitando el enlace de arriba.';

			$languages['resume_checkbox_title']		= 'Guardar mi progreso y reanudar luego';
			$languages['resume_email_input_label']	= 'Ingresar su dirección de correo electrónico';
			$languages['resume_submit_button_text']	= 'Guardar formulario y reanudar luego';
			$languages['resume_guideline']			= 'Se enviará a su dirección de correo un enlace especial para reanudar el formulario.';

			//range validation
			$languages['range_type_digit']			= 'dígitos';
			$languages['range_type_chars'] 			= 'caracteres';
			$languages['range_type_words'] 			= 'palabras';

			$languages['range_min']  				= 'Obligatorio un mínimo de %s.'; 
			$languages['range_min_entered']   		= 'Ha ingresado: %s.';

			$languages['range_max']					= 'Se permite un máximo de %s.';
			$languages['range_max_entered']   		= 'Ha ingresado: %s.';

			$languages['range_min_max'] 			= 'Debe contener entre %s y %s.';
			$languages['range_min_max_same'] 		= 'Debe ser de %s.';
			$languages['range_min_max_entered'] 	= 'Ha ingresado: %s.';

			$languages['range_number_min']	 		= 'Debe ser un número superior o igual a %s.';
			$languages['range_number_max']	 		= 'Debe ser un número menor o igual a %s.';
			$languages['range_number_min_max'] 		= 'Debe ser un número entre %s y %s.';

			//file uploads
			$languages['file_queue_limited'] 		= 'Este campo tiene un límite máximo de %s archivos.';
			$languages['file_upload_max']	   		= 'Error. Se permite un máximo de %s MB.';
			$languages['file_type_limited']  		= 'Error. No se permite este tipo de archivo.';
			$languages['file_error_upload']  		= '¡Error! Falló la subida';
			$languages['file_attach']		  		= 'Agregar archivos';
			$languages['file_select']		  		= 'Seleccionar archivo';

			//payment total
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Total';
			$languages['form_payment_header_title'] = 'Payment';
			$languages['form_payment_title'] 		= 'Enter Payment Information';
			$languages['form_payment_description'] 	= 'Please review the details below before entering payment information.';
			$languages['payment_submit_button']		= 'Submit Payment';
			$languages['tax']						= 'Tax';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'Payment ID';
			$languages['payment_date']	 	= 'Payment Date';
			$languages['payment_fullname'] 	= 'Full Name';
			$languages['payment_shipping'] 	= 'Shipping Address';
			$languages['payment_billing']	= 'Billing Address';

			//coupon code
			$languages['coupon_not_exist'] = "This coupon code does not exist.";
			$languages['coupon_max_usage'] = "This coupon has reached the maximum redemption limit.";
			$languages['coupon_expired']   = "This coupon code has been expired.";
			$languages['discount']		   = "Discount";		

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= 'Página %s de %s';
		}else if($target_language == 'swedish'){
			//simple name and extended name
			$languages['name_first']			= 'Förnamn';
			$languages['name_middle']			= 'Mellannamn';
			$languages['name_last']				= 'Efternamn';
			$languages['name_title']			= 'Titel';
			$languages['name_suffix']			= 'Ändelse';
			
			//address
			$languages['address_street']		= 'Gatuadress';
			$languages['address_street2']		= 'Adressrad 2';
			$languages['address_city']			= 'Stad';
			$languages['address_state']			= 'Delstat / Län / Region';
			$languages['address_zip']			= 'Postnummer';
			$languages['address_country']		= 'Land';

			//captcha
			$languages['captcha_required']				= 'Detta fält är obligatoriskt. Vänligen skriv in bokstäverna som visas i bilden.';
			$languages['captcha_mismatch']				= 'Bokstäverna i bilden stämmer inte överrens. Försök igen.';
			$languages['captcha_text_mismatch'] 		= 'Fel svar. Vänligen försök igen.';
			$languages['captcha_error']					= 'Fel vid bearbetning, vänligen försök igen.';
			$languages['captcha_simple_image_title']	= 'Skriv bokstäverna du ser i bilden nedan.';
			$languages['captcha_simple_text_title']		= 'Spamskydd. Vänligen svara på denna enkla fråga:';
			
			//date
			$languages['date_dd']				= 'DD';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'ÅÅÅÅ';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'TT';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Ett problem uppstog med ditt formulär.';
			$languages['error_desc']			=	'Felen har <strong>markerats</strong> nedan.';
			
			//form buttons
			$languages['submit_button']			=	'Skicka';
			$languages['continue_button']		=	'Fortsätt';
			$languages['back_button']			=	'Föregående';
			
			//form status
			$languages['form_inactive']			=	'Detta formulär är för närvarande inaktivt.';
			$languages['form_limited']			=   'Tyvärr så tar detta formulär inte emot några fler inmatningar.';
			
			//form password
			$languages['form_pass_title']		=	'Detta formulär är lösenordsskyddat.';
			$languages['form_pass_desc']		=	'Vänligen skriv in ditt lösenord.';
			$languages['form_pass_invalid']		=	'Fel lösenord!';
			
			//form review
			$languages['review_title']			=	'Kontrollera dina uppgifter';
			$languages['review_message']		=	'Vänligen kontrollera dina uppgifter nedan. Tryck Skicka-knappen för att slutföra.';
			
			//validation message 
			$languages['val_required'] 			=	'Detta fält är obligatoriskt. Vänligen skriv in ett värde.';
			$languages['val_required_file'] 	=	'Detta fält är obligatoriskt. Vänligen ladda upp en fil.';
			$languages['val_unique'] 			=	'Detta fält kräver ett unikt värde och detta värde har redan använts.';
			$languages['val_integer'] 			=	'Detta fält måste vara en siffra.';
			$languages['val_float'] 			=	'Detta fält måste vara ett flyttal.';
			$languages['val_numeric'] 			=	'Detta fält måste vara ett nummer.';
			$languages['val_email'] 			=	'Detta fält har inte ett korrekt e-postformat.';
			$languages['val_website'] 			=	'Detta fält har inte ett korrekt webbadressformat.';
			$languages['val_username'] 			=	'Detta fält får endast bestå av a-z 0-9 och understreck.';
			$languages['val_equal'] 			=	'%s måste stämma överrens.';
			$languages['val_equal_email'] 		=	'E-post måste stämma överrens.';
			$languages['val_date'] 				=	'Detta fält har inte rätt datumformat.';
			$languages['val_date_range'] 		=	'Detta fält måste vara mellan %s och %s.';
			$languages['val_date_min'] 			=	'Detta fält måste vara större än eller lika med %s.';
			$languages['val_date_max'] 			=	'Detta fält måste vara mindre än eller lika med %s.';
			$languages['val_date_na'] 			=	'Detta datum är inte tillgängligt.';
			$languages['val_time'] 				=	'Detta fält har inte rätt tidsformat.';
			$languages['val_phone'] 			=	'Vänligen skriv in ett giltigt telefonnummer.';
			$languages['val_filetype']			=	'Den filtyp du försöker ladda upp är inte tillåten.';

			$languages['val_choice_atleast'] 	= 	'Minst %s krävs.'; 
			$languages['val_choice_atmost']  	= 	'Maximalt %s tillåts.';
			$languages['val_choice_exactly'] 	= 	'Måste vara %s.';
			$languages['val_choice_between'] 	= 	'Måste vara mellan %s och %s.';
			
			//fields on excel/csv
			$languages['export_num']			=	'Nej.';
			$languages['date_created']			=	'Datum skapat';
			$languages['date_updated']			=	'Datum uppdaterat';
			$languages['ip_address']			=	'IP-adress';

			//form resume
			$languages['resume_email_subject']		= 'Ditt formulär till {form_name}-formuläret har sparats.';
			$languages['resume_email_content'] 		= 'Tack! Ditt formulär till <b>{form_name}</b> har sparats.<br /><br />Du kan återuppta ifyllnaden av formuläret när som helst genom att klicka på länken nedan:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>VIKTIGT:</b><br />Ditt formulär anses vara ofullständig tills du återupptar formuläret och trycker på skicka-knappen.';							

			$languages['resume_success_title']   	= 'Din data har sparats.';
			$languages['resume_success_content'] 	= 'Vänligen kopiera nedanstående länk och spara den på ett säkert ställe:<br/>%s<br/><br/>Du kan återuppta ifyllnaden av formuläret när som helst genom att gå till ovanstående länk';

			$languages['resume_checkbox_title']		= 'Spara min data och fortsätt senare';
			$languages['resume_email_input_label']	= 'Skriv in din e-postadress';
			$languages['resume_submit_button_text']	= 'Spara formulär och återuppta senare';
			$languages['resume_guideline']			= 'En speciell länk för att fortsätta fylla i formuläret kommer skickas till din e-postadress';

			//range validation
			$languages['range_type_digit']			= 'siffror';
			$languages['range_type_chars'] 			= 'tecken';
			$languages['range_type_words'] 			= 'ord';

			$languages['range_min']  				= 'Minst %s krävs.'; 
			$languages['range_min_entered']   		= 'Är för närvarande: %s.';

			$languages['range_max']					= 'Maximalt %s tillåts.';
			$languages['range_max_entered']   		= 'Är för närvarande: %s.';

			$languages['range_min_max'] 			= 'Måste vara mellan %s och %s.';
			$languages['range_min_max_same'] 		= 'Måste vara %s.';
			$languages['range_min_max_entered'] 	= 'Är för närvarande: %s.';

			$languages['range_number_min']	 		= 'Måste vara ett nummer större än eller lika med %s.';
			$languages['range_number_max']	 		= 'Måste vara ett nummer mindre än eller lika med %s.';
			$languages['range_number_min_max'] 		= 'Måste vara ett nummer mellan %s and %s.';

			//file uploads
			$languages['file_queue_limited'] 		= 'Detta fält är begränsat till högst %s filer.';
			$languages['file_upload_max']	   		= 'Fel. Maximalt %sMB tillåts.';
			$languages['file_type_limited']  		= 'Fel. Denna filtyp tillåts inte.';
			$languages['file_error_upload']  		= 'Fel! Kunde inte ladda upp';
			$languages['file_attach']		  		= 'Bifoga filer';
			$languages['file_select']		  		= 'Select Files';

			//payment total
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Totalt';
			$languages['form_payment_header_title'] = 'Betalning';
			$languages['form_payment_title'] 		= 'Betalningsinformation';
			$languages['form_payment_description'] 	= 'Kontrollera alla uppgifter innan ni godkänner betalningen.';
			$languages['payment_submit_button']		= 'Skicka betalning';
			$languages['tax']						= 'Moms';
			$languages['setup_fee']					= 'Setup Fee';

			//payment details
			$languages['payment_status']			= 'Status';
			$languages['payment_id']				= 'Betalnings ID';
			$languages['payment_date']				= 'Betalningsdatum';
			$languages['payment_fullname'] 			= 'Namn';
			$languages['payment_shipping'] 			= 'Leverans adress';
			$languages['payment_billing']			= 'Faktura adress';

			//coupon code
			$languages['coupon_not_exist']       	= "Denna kupong finns inte.";
			$languages['coupon_max_usage']   		= "Denna kupong har nått inlösengräns.";
			$languages['coupon_expired']         	= "Denna kupong har nått sin tidsgräns.";
			$languages['discount']		           	= "Rabatt";	

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';
		
			//multipage
			$languages['page_title']				= 'Sidan %s av %s';
		}else if($target_language == 'japanese'){
			//simple name and extended name
			$languages['name_first']			= '名';
			$languages['name_middle']			= 'ミドルネーム';
			$languages['name_last']				= '姓';
			$languages['name_title']			= '敬称';
			$languages['name_suffix']			= '称号';
			
			//address
			$languages['address_street']		= '住所';
			$languages['address_street2']		= '住所２行目';
			$languages['address_city']			= '市';
			$languages['address_state']			= '県（州／省／地域';
			$languages['address_zip']			= '郵便番号';
			$languages['address_country']		= '国';

			//captcha
			$languages['captcha_required']				= 'この欄は必須です。画像に表示されている文字を入力してください。';
			$languages['captcha_mismatch']				= '文字が画像と一致しません。もう一度試してください。';
			$languages['captcha_text_mismatch'] 		= '答えが不正確です。もう一度試してください。';
			$languages['captcha_error']					= '処理中にエラーが発生しました。もう一度試してください。';
			$languages['captcha_simple_image_title']	= '以下の画像に表示されている文字を入力してください。';
			$languages['captcha_simple_text_title']		= 'スパム防止です。この簡単な質問に答えてください。';
			
			//date
			$languages['date_dd']				= '日';
			$languages['date_mm']				= '月';
			$languages['date_yyyy']				= '年';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'時';
			$languages['time_mm']				=	'分';
			$languages['time_ss']				=	'秒';
			
			//error message
			$languages['error_title']			=	'送信に不具合が発生しました。';
			$languages['error_desc']			=	'エラーは以下に<strong>強調</strong>されています。';
			
			//form buttons
			$languages['submit_button']			=	'送信';
			$languages['continue_button']		=	'続ける';
			$languages['back_button']			=	'前へ';
			
			//form status
			$languages['form_inactive']			=	'このフォームは現在休止中です。';
			$languages['form_limited']			=   '申し訳ありませんが、このフォームはエントリーの受付を終了しました。';
			
			//form password
			$languages['form_pass_title']		=	'このフォームはパスワードによって保護されています。';
			$languages['form_pass_desc']		=	'パスワードを入力してください。';
			$languages['form_pass_invalid']		=	'無効なパスワードです！';
			
			//form review
			$languages['review_title']			=	'エントリーを確認する';
			$languages['review_message']		=	'以下のエントリーを確認してください。送信ボタンをクリックすると終了します。';
			
			//validation message 
			$languages['val_required'] 			=	'この欄は必須です。数値を入力してください。';
			$languages['val_required_file'] 	=	'この欄は必須です。ファイルをアップロードしてください。';
			$languages['val_unique'] 			=	'この欄には固有のエントリーが必要です。この数値は既に使用されています。';
			$languages['val_integer'] 			=	'この欄は整数でなければいけません。';
			$languages['val_float'] 			=	'この欄は浮動小数でなければいけません。';
			$languages['val_numeric'] 			=	'この欄は数字でなければいけません。';
			$languages['val_email'] 			=	'この欄には適切なＥメール形式が入力されていません。';
			$languages['val_website'] 			=	'この欄には適切なウェブサイトアドレス形式が入力されていません。';
			$languages['val_username'] 			=	'この欄にはa〜zおよび0〜9、アンダースコアのみを入力することができます。';
			$languages['val_equal'] 			=	'%sが一致しなければいけません。';
			$languages['val_equal_email'] 		=	'Emailが一致しなければいけません。';
			$languages['val_date'] 				=	'この欄には適切な日付の形式が入力されていません。';
			$languages['val_date_range'] 		=	'この日付欄は%sから%sの間でなければいけません。';
			$languages['val_date_min'] 			=	'この日付欄は%s以上でなければいけません。';
			$languages['val_date_max'] 			=	'この日付欄は%s以下でなければいけません。';
			$languages['val_date_na'] 			=	'この日付は選択できません。';
			$languages['val_time'] 				=	'この欄には適切な時間形式が入力されていません。';
			$languages['val_phone'] 			=	'有効な電話番号を入力してください。';
			$languages['val_filetype']			=	'アップロードしようとしているファイル形式には対応していません。';

			$languages['val_choice_atleast'] 	= 	'最小%sの入力が必要です。';
			$languages['val_choice_atmost']  	= 	'最大%sがの入力が可能です。';
			$languages['val_choice_exactly'] 	= 	'%sでなければなりません。';
			$languages['val_choice_between'] 	= 	'%sから%sでなければいけません。';
			
			//fields on excel/csv
			$languages['export_num']			=	'Ｎｏ．';
			$languages['date_created']			=	'作成日時';
			$languages['date_updated']			=	'更新日時';
			$languages['ip_address']			=	'ＩＰアドレス';

			//form resume
			$languages['resume_email_subject']		= '{form_name}フォームへの入力が保存されました。';
			$languages['resume_email_content'] 		= 'ありがとうございます！<b>{form_name}</b>への入力が保存されました。<br /><br />以下のリンクをクリックすることでいつでもフォームの入力を再開することができます：<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>重要：</b><br />フォームの入力を再開して送信ボタンを押すまで、お客様の入力は不完全なものとみなされます。';							

			$languages['resume_success_title']   	= 'お客様の進捗が保存されました。';
			$languages['resume_success_content'] 	= '以下のリンクをコピーして安全な場所に保存してください：<br/>%s<br/><br/>上のリンクを開くことでいつでもフォームの入力を再開することができます';

			$languages['resume_checkbox_title']		= '進捗を保存して後で再開する';
			$languages['resume_email_input_label']	= 'Ｅメールアドレスを入力してください。';
			$languages['resume_submit_button_text']	= 'フォームを保存して後で再開する';
			$languages['resume_guideline']			= 'フォームの入力を再開するための特別なリンクがお客様のＥメールアドレスに送信されます';

			//range validation
			$languages['range_type_digit']			= '桁';
			$languages['range_type_chars'] 			= '文字';
			$languages['range_type_words'] 			= '語';

			$languages['range_min']  				= '最小%sの入力が必要です。'; 
			$languages['range_min_entered']   		= '現在の入力数： %s.';

			$languages['range_max']					= '最大%sがの入力が可能です。';
			$languages['range_max_entered']   		= '現在の入力数： %s.';

			$languages['range_min_max'] 			= '%sから%sでなければいけません。';
			$languages['range_min_max_same'] 		= '%sでなければなりません。';
			$languages['range_min_max_entered'] 	= '現在の入力数： %s.';

			$languages['range_number_min']	 		= '%s以上の数字でなければいけません。';
			$languages['range_number_max']	 		= '%s以下の数字でなければいけません。';
			$languages['range_number_min_max'] 		= '%sから%sの数字でなければいけません。';

			//file uploads
			$languages['file_queue_limited'] 		= 'この欄は最大%sファイルまでに限定されています。';
			$languages['file_upload_max']	   		= 'エラーです。最大%sＭＢまで利用可能です。';
			$languages['file_type_limited']  		= 'エラーです。このファイル形式には対応していません。';
			$languages['file_error_upload']  		= 'エラーです！アップロードできません';
			$languages['file_attach']		  		= '添付ファイル';
			$languages['file_select']		  		= 'Select Files';

			//payment total
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= '計';
			$languages['form_payment_header_title'] = 'Payment';
			$languages['form_payment_title'] 		= 'Enter Payment Information';
			$languages['form_payment_description'] 	= 'Please review the details below before entering payment information.';
			$languages['payment_submit_button']		= 'Submit Payment';
			$languages['tax']						= 'Tax';
			$languages['setup_fee']					= 'Setup Fee';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'Payment ID';
			$languages['payment_date']	 	= 'Payment Date';
			$languages['payment_fullname'] 	= 'Full Name';
			$languages['payment_shipping'] 	= 'Shipping Address';
			$languages['payment_billing']	= 'Billing Address';

			//coupon code
			$languages['coupon_not_exist'] = "This coupon code does not exist.";
			$languages['coupon_max_usage'] = "This coupon has reached the maximum redemption limit.";
			$languages['coupon_expired']   = "This coupon code has been expired.";
			$languages['discount']		   = "Discount";		

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= '%sページ。%s:総ページ数';
		}else if($target_language == 'estonian'){
			//Estonian translation courtesy of EUROPEAN NETWORKS, www.en.ee

			//simple name and extended name
			$languages['name_first']			= 'Nimi';
			$languages['name_middle']			= 'Teine ​​eesnimi';
			$languages['name_last']				= 'Perenimi';
			$languages['name_title']			= 'Nimi pealkirja';
			$languages['name_suffix']			= 'Sufiks';
			
			//address
			$languages['address_street']		= 'Tänav';
			$languages['address_street2']		= 'Aadress 2';
			$languages['address_city']			= 'Linn';
			$languages['address_state']			= 'Maakond';
			$languages['address_zip']			= 'Postiindeks';
			$languages['address_country']		= 'Riik';

			//captcha
			$languages['captcha_required']				= 'Seda captch-teksti on vaja. Palun sisesta tähed pildil.';
			$languages['captcha_mismatch']				= 'Tähed pildil ei sobi. Proovige uuesti.';
			$languages['captcha_text_mismatch'] 		= 'Vale vastus. Palun proovige uuesti.';
			$languages['captcha_error']					= 'Viga töötlemine, palun proovige uuesti.';
			$languages['captcha_simple_image_title']	= 'Sisestage tähed, mida näed alloleval pildil.';
			$languages['captcha_simple_text_title']		= 'Rämpsposti kaitse. Palun vastake see lihtne küsimus:';
			
			//date
			$languages['date_dd']				= 'PP';
			$languages['date_mm']				= 'KK';
			$languages['date_yyyy']				= 'AAAA';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'HH';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Tekkis probleem oma tagasiside vormi.';
			$languages['error_desc']			=	'Vead on <strong>rõhutatud</strong> allpool.';
			
			//form buttons
			$languages['submit_button']			=	'Saada';
			$languages['continue_button']		=	'Järgmine';
			$languages['back_button']			=	'Eelmine';
			
			//form status
			$languages['form_inactive']			=	'Selle vormi on praegu passiivne.';
			$languages['form_limited']			=   'Vabandage, selle vormi ei võta endale mingit kirjet.';
			
			//form password
			$languages['form_pass_title']		=	'Selle vormi on parooliga kaitstud.';
			$languages['form_pass_desc']		=	'Palun sisesta oma parool.';
			$languages['form_pass_invalid']		=	'Vigane parool!';
			
			//form review
			$languages['review_title']			=	'Vaadata oma sissekanne';
			$languages['review_message']		=	'Palun vaadake oma sissekanne. Vajuta nupule, et lõpetada.';
			
			//validation message 
			$languages['val_required'] 			=	'See andmeväli on vaja. Palun sisesta väärtus.';
			$languages['val_required_file'] 	=	'See andmeväli on vaja. Palun faili üles laadida.';
			$languages['val_unique'] 			=	'See andmeväli nõuab portaali ja selle väärtus on juba kasutatud.';
			$languages['val_integer'] 			=	'See andmeväli peab olema täisarv.';
			$languages['val_float'] 			=	'See andmeväli peab olema float-number.';
			$languages['val_numeric'] 			=	'See andmeväli peab olema number.';
			$languages['val_email'] 			=	'See andmeväli on vaja. Palun sisesta email e-posti.';
			$languages['val_website'] 			=	'See andmeväli on vaja. Palun sisesta veebi aadress.';
			$languages['val_username'] 			=	'See andmeväli võib sisaldada ainult az 0-9 ja alakriipsud.';
			$languages['val_equal'] 			=	'%s peab sobima.';
			$languages['val_equal_email'] 		=	'E-posti peab sobima.';
			$languages['val_date'] 				=	'See andmeväli on vaja. Palun sisesta kuupäev.';
			$languages['val_date_range'] 		=	'Seda kuupäeva andmevälja peavad olema %s ja %s.';
			$languages['val_date_min'] 			=	'Seda kuupäeva andmevälja peab olema suurem või võrdne %s.';
			$languages['val_date_max'] 			=	'Seda kuupäeva andmevälja peavad olema väiksem või võrdne %s.';
			$languages['val_date_na'] 			=	'Selle kuupäev ei ole kättesaadav valiku.';
			$languages['val_time'] 				=	'See andmeväli on vaja. Palun sisesta kellaaja vorming.';
			$languages['val_phone'] 			=	'Palun sisesta telefoninumber.';
			$languages['val_filetype']			=	'Failitüübi üritad saata ei tohi.';
			
			$languages['val_choice_atleast'] 	= 	'Vähemalt %s on vaja.'; 
			$languages['val_choice_atmost']  	= 	'Maksimaalne %s lubatud.';
			$languages['val_choice_exactly'] 	= 	'Peab olema %s.';
			$languages['val_choice_between'] 	= 	'Peab olema %s kuni %s.';

			//fields on excel/csv
			$languages['export_num']			=	'No.';
			$languages['date_created']			=	'Loomise kuupäev';
			$languages['date_updated']			=	'Uuendamise kuupäev';
			$languages['ip_address']			=	'IP aadress';

			//form resume
			$languages['resume_email_subject']		= 'Teie vormi {form_name} on salvestatud';
			$languages['resume_email_content'] 		= 'Aitäh! Vormi <b>{form_name}</b> on salvestatud.<br /><br />Võite jätkata kujul igal ajal klikkides alloleval lingil:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>Olulist teavet:</b><br />Teie vorm on puudulik, kuni te uuesti selle vormi ja klikkida Saada lingi.';							

			$languages['resume_success_title']   	= 'Sinu edu on salvestatud.';
			$languages['resume_success_content'] 	= 'Palun kopeerida link allpool ja salvestage:<br/>%s<br/><br/>Võite jätkata kujul igal ajal läheb üle link.';

			$languages['resume_checkbox_title']		= 'Salvestage minu edu ja hiljem jätkata';
			$languages['resume_email_input_label']	= 'Sisesta oma e-posti aadress';
			$languages['resume_submit_button_text']	= 'Salvestage vorm ja hiljem jätkata';
			$languages['resume_guideline']			= 'Link saadetakse teie e-posti aadress.';

			//range validation
			$languages['range_type_digit']			= 'numbrit';
			$languages['range_type_chars'] 			= 'märgid';
			$languages['range_type_words'] 			= 'sõnad';

			$languages['range_min']  				= 'Vähemalt %s on vaja.'; 
			$languages['range_min_entered']   		= 'Sisestatud: %s.';

			$languages['range_max']					= 'Maksimaalne %s lubatud.';
			$languages['range_max_entered']   		= 'Sisestatud: %s.';

			$languages['range_min_max'] 			= 'Peab olema %s kuni %s.';
			$languages['range_min_max_same'] 		= 'Peab olema %s.';
			$languages['range_min_max_entered'] 	= 'Sisestatud: %s.';

			$languages['range_number_min']	 		= 'Peab olema number on suurem või võrdne %s.';
			$languages['range_number_max']	 		= 'Peab olema number on väiksem või võrdne %s.';
			$languages['range_number_min_max'] 		= 'Peab olema number vahemikus %s ja %s';

			//file uploads
			$languages['file_queue_limited'] 		= 'Piiratud %s faili maksimaalse.';
			$languages['file_upload_max']	   		= 'Viga. Suurim %sMB lubatud.';
			$languages['file_type_limited']  		= 'Viga. Selle failitüüp ei ole lubatud.';
			$languages['file_error_upload']  		= 'Viga! Ei saa laadida';
			$languages['file_attach']		  		= 'Lisa faili';
			$languages['file_select']		  		= 'Select Files';

			//payment total
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Kokku';
			$languages['form_payment_header_title'] = 'Payment';
			$languages['form_payment_title'] 		= 'Enter Payment Information';
			$languages['form_payment_description'] 	= 'Please review the details below before entering payment information.';
			$languages['payment_submit_button']		= 'Submit Payment';
			$languages['tax']						= 'Tax';
			$languages['setup_fee']					= 'Setup Fee';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'Payment ID';
			$languages['payment_date']	 	= 'Payment Date';
			$languages['payment_fullname'] 	= 'Full Name';
			$languages['payment_shipping'] 	= 'Shipping Address';
			$languages['payment_billing']	= 'Billing Address';

			//coupon code
			$languages['coupon_not_exist'] = "This coupon code does not exist.";
			$languages['coupon_max_usage'] = "This coupon has reached the maximum redemption limit.";
			$languages['coupon_expired']   = "This coupon code has been expired.";
			$languages['discount']		   = "Discount";		

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= 'Lehekülg %s kuni %s';
		}else if($target_language == 'russian'){
			//Russian translation courtesy of EUROPEAN NETWORKS, www.en.ee

			//simple name and extended name
			$languages['name_first']			= 'Имя';
			$languages['name_middle']			= 'Отчество';
			$languages['name_last']				= 'Фамилия';
			$languages['name_title']			= 'Звание';
			$languages['name_suffix']			= 'Suffix';
			
			//address
			$languages['address_street']		= 'Адрес, улица';
			$languages['address_street2']		= 'Адрес, дополнительно';
			$languages['address_city']			= 'Город';
			$languages['address_state']			= 'Область';
			$languages['address_zip']			= 'Индекс';
			$languages['address_country']		= 'Страна';

			//captcha
			$languages['captcha_required']				= 'Это поле необходимо заполнить. Пожалуйста, введите символы с картинки.';
			$languages['captcha_mismatch']				= 'Не совпадает с символами на картинке. Попробуйте снова.';
			$languages['captcha_text_mismatch'] 		= 'Неверный ответ. Попробуйте снова.';
			$languages['captcha_error']					= 'Ошибка в процессе обработки, пожалуйста, попробуйте снова.';
			$languages['captcha_simple_image_title']	= 'Введите, пожалуйста, символы, которые вы видите на картинке ниже.';
			$languages['captcha_simple_text_title']		= 'Проверка не робот ли вы, пожалуйста, ответьте на вопрос:';
			
			//date
			$languages['date_dd']				= 'ДД';
			$languages['date_mm']				= 'ММ';
			$languages['date_yyyy']				= 'ГГГГ';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'ЧЧ';
			$languages['time_mm']				=	'ММ';
			$languages['time_ss']				=	'СС';
			
			//error message
			$languages['error_title']			=	'Во время отправки были зафиксированы ошибки.';
			$languages['error_desc']			=	'Ошибки обозначены <strong>подсветкой</strong> ниже.';
			
			//form buttons
			$languages['submit_button']			=	'Отправить';
			$languages['continue_button']		=	'Продолжить';
			$languages['back_button']			=	'Вернуться';
			
			//form status
			$languages['form_inactive']			=	'Эта форма в данный момент неактивна.';
			$languages['form_limited']			=   'Извините, но эта форма не может принять новые данные.';
			
			//form password
			$languages['form_pass_title']		=	'Доступ к этой форме осуществляется по паролю.';
			$languages['form_pass_desc']		=	'Введите, пожалуйста, пароль.';
			$languages['form_pass_invalid']		=	'Неверный пароль!';
			
			//form review
			$languages['review_title']			=	'Проверка введенных данных';
			$languages['review_message']		=	'Пожалуйста, проверьте введенные данные ниже. Подтвердите кнопкой Отправить внизу страницы.';
			
			//validation message 
			$languages['val_required'] 			=	'Это поле необходимо заполнить. Введите данные, пожалуйста.';
			$languages['val_required_file'] 	=	'Это поле необходимо заполнить. Закачайте файл, пожалуйста.';
			$languages['val_unique'] 			=	'Это поле требует выбора единственного значение и данное значение уже было использовано.';
			$languages['val_integer'] 			=	'В этом поле должны быть целые числа.';
			$languages['val_float'] 			=	'В этом поле должны быть переменные данные.';
			$languages['val_numeric'] 			=	'В этом поле должны быть числа.';
			$languages['val_email'] 			=	'В этом поле должен быть email.';
			$languages['val_website'] 			=	'В этом поле должен быть адрес вебсайта.';
			$languages['val_username'] 			=	'В этом поле могут быть только символы a-z, числа 0-9 и символ подчеркивания.';
			$languages['val_equal'] 			=	'%s должны совпадать.';
			$languages['val_equal_email'] 		=	'Email должны совпадать.';
			$languages['val_date'] 				=	'В этом поле должна быть указана дата.';
			$languages['val_date_range'] 		=	'В этом поле данные должны быть между %s и %s.';
			$languages['val_date_min'] 			=	'В этом поле данные должны быть больше или равны %s.';
			$languages['val_date_max'] 			=	'В этом поле данные должны быть меньше или равны %s.';
			$languages['val_date_na'] 			=	'Эта дата недоступна для выбора.';
			$languages['val_time'] 				=	'В этом поле должно быть указано время.';
			$languages['val_phone'] 			=	'Пожалуйста, введите корректный номер телефона.';
			$languages['val_filetype']			=	'Файл такого формата не может быть загружен на сервер.';

			$languages['val_choice_atleast'] 	= 	'Минимально требуется: %s.'; 
			$languages['val_choice_atmost']  	= 	'Максимально допустимо: %s.';
			$languages['val_choice_exactly'] 	= 	'Должно быть %s.';
			$languages['val_choice_between'] 	= 	'Должно быть между %s и %s.';
			
			//fields on excel/csv
			$languages['export_num']			=	'Nr.';
			$languages['date_created']			=	'Дата создания';
			$languages['date_updated']			=	'Дата обновления';
			$languages['ip_address']			=	'IP адрес';

			//form resume
			$languages['resume_email_subject']		= 'Ваши данные в сообщении {form_name} были сохранены';
			$languages['resume_email_content'] 		= 'Спасибо! Ваше сообщение <b>{form_name}</b> было сохранено.<br /><br />Вы можете продолжить работу с формой в любой удобный момент, перейдя по следующей ссылке:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>Внимание:</b><br />Ваше сообщение является незавершенным до момента вашего возврата к форме, завершения ввода данных и подтверждения отправки нажатием на кнопку Отправить.';							

			$languages['resume_success_title']   	= 'Ваши данные были успешно сохранены.';
			$languages['resume_success_content'] 	= 'Пожалуйста, скопируйте данную ссылку в надежное место:<br/>%s<br/><br/>Вы можете продолжить работу с формой в любой удобный момент, перейдя по ссылке выше.';

			$languages['resume_checkbox_title']		= 'Сохранить текущие данные и продолжить позднее';
			$languages['resume_email_input_label']	= 'Введите ваш Email адрес';
			$languages['resume_submit_button_text']	= 'Сохранить форму и продолжить позднее';
			$languages['resume_guideline']			= 'На ваш email адрес была выслана ссылка для возврата к сохраненной форме.';

			//range validation
			$languages['range_type_digit']			= 'цифр';
			$languages['range_type_chars'] 			= 'символов';
			$languages['range_type_words'] 			= 'слов';

			$languages['range_min']  				= 'Минимально требуется: %s.'; 
			$languages['range_min_entered']   		= 'Введено: %s.';

			$languages['range_max']					= 'Максимально допустимо: %s.';
			$languages['range_max_entered']   		= 'Введено: %s.';

			$languages['range_min_max'] 			= 'Должно быть между %s и %s.';
			$languages['range_min_max_same'] 		= 'Должно быть %s.';
			$languages['range_min_max_entered'] 	= 'Введено: %s.';

			$languages['range_number_min']	 		= 'Долюно быть число больше или равное %s.';
			$languages['range_number_max']	 		= 'Долюно быть число меньше или равное %s.';
			$languages['range_number_min_max'] 		= 'Должно быть число между %s и %s';

			//file uploads
			$languages['file_queue_limited'] 		= 'В данном поле есть ограничение на максимум %s файлов.';
			$languages['file_upload_max']	   		= 'Ошибка. Не более %sMB допустимо.';
			$languages['file_type_limited']  		= 'Ошибка. Данный тип файла не поддерживается.';
			$languages['file_error_upload']  		= 'Ошибка! Невозможно произвести закачку.';
			$languages['file_attach']		  		= 'Прикрепить файлы';
			$languages['file_select']		  		= 'Select Files';

			//payment total
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Всего';
			$languages['form_payment_header_title'] = 'Payment';
			$languages['form_payment_title'] 		= 'Enter Payment Information';
			$languages['form_payment_description'] 	= 'Please review the details below before entering payment information.';
			$languages['payment_submit_button']		= 'Submit Payment';
			$languages['tax']						= 'Tax';
			$languages['setup_fee']					= 'Setup Fee';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'Payment ID';
			$languages['payment_date']	 	= 'Payment Date';
			$languages['payment_fullname'] 	= 'Full Name';
			$languages['payment_shipping'] 	= 'Shipping Address';
			$languages['payment_billing']	= 'Billing Address';

			//coupon code
			$languages['coupon_not_exist'] = "This coupon code does not exist.";
			$languages['coupon_max_usage'] = "This coupon has reached the maximum redemption limit.";
			$languages['coupon_expired']   = "This coupon code has been expired.";
			$languages['discount']		   = "Discount";		

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= 'Страница %s из %s';
		}else if($target_language == 'hungarian'){
			
			//simple name and extended name
			$languages['name_first']			= 'Keresztnév';
			$languages['name_middle']			= 'Középső név';
			$languages['name_last']				= 'Vezetéknév';
			$languages['name_title']			= 'Cím';
			$languages['name_suffix']			= 'Utótag';
			
			//address
			$languages['address_street']		= 'Cím utca, házszám';
			$languages['address_street2']		= 'Cím sor 2';
			$languages['address_city']			= 'Város';
			$languages['address_state']			= 'Állam / Megye / Régió';
			$languages['address_zip']			= 'Posta / Irányítószám';
			$languages['address_country']		= 'Ország';

			//captcha
			$languages['captcha_required']				= 'A mező kitöltése kötelező. Kérjük írja be a képen látható betűket.';
			$languages['captcha_mismatch']				= 'A betűk és a kép nem egyezik meg. Próbálja újra.';
			$languages['captcha_text_mismatch'] 		= 'Helytelen válasz. Kérjük, próbálja újra.';
			$languages['captcha_error']					= 'Hiba történt a feldolgozáskor, kérjük, próbálja újra.';
			$languages['captcha_simple_image_title']	= 'Az alábbi képen látható betűket írja be.';
			$languages['captcha_simple_text_title']		= 'Kéretlen levél elleni védelem. Kérjük, válaszolja meg ezt az egyszerű kérdést:';
			
			//date
			$languages['date_dd']				= 'DD';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'YYYY';
			
			//price
			$languages['price_dollar_main']		=	'Dollár';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euró';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Fillér';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'HH';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Volt egy probléma a benyújtáskor.';
			$languages['error_desc']			=	'Hibák kerültek a <strong>kiemelt sorban!</strong> Tekintse át a bejegyzést lent.';
			
			//form buttons
			$languages['submit_button']			=	'Küldés';
			$languages['continue_button']		=	'Folytatás';
			$languages['back_button']			=	'Előző';
			
			//form status
			$languages['form_inactive']			=	'Ez az űrlap jelenleg inaktív.';
			$languages['form_limited']			=   'Sajnáljuk, de ez az űrlap már nem fogadja a bejegyzéseket.';
			
			//form password
			$languages['form_pass_title']		=	'Ez az űrlap, jelszóval védett.';
			$languages['form_pass_desc']		=	'Kérjük, adja meg a jelszót.';
			$languages['form_pass_invalid']		=	'Érvénytelen jelszó!';
			
			//form review
			$languages['review_title']			=	'Tekintse át a bejegyzést';
			$languages['review_message']		=	'Kérjük, nézze át az alábbi bejegyzést. Kattintson a Küldés gombra a befejezéshez.';
			
			//validation message 
			$languages['val_required'] 			=	'A mező kitöltése kötelező. Kérem adjon meg egy értéket.';
			$languages['val_required_file'] 	=	'A mező kitöltése kötelező. Kérjük, töltsön fel egy fájlt.';
			$languages['val_unique'] 			=	'Ez a mező szükséges egyedi bejegyzést, és ezt az értéket már felhasználták.';
			$languages['val_integer'] 			=	'Ez a mező egész számnak kell lennie.';
			$languages['val_float'] 			=	'Ez a mező a lebegőpontos kell lennie.';
			$languages['val_numeric'] 			=	'Ez a mező a számnak kell lennie.';
			$languages['val_email'] 			=	'Ez a mező nem a helyes e-mail formátumban van.';
			$languages['val_website'] 			=	'Ez a mező nem a helyes weboldal cím formátumban van.';
			$languages['val_username'] 			=	'Ez a mező csak állhat a-z, 0-9 és aláhúzásjelek.';
			$languages['val_equal'] 			=	'%s egyeznie kell.';
			$languages['val_equal_email'] 		=	'E-mail egyeznie kell.';
			$languages['val_date'] 				=	'Ez a mező nem a helyes dátum formátumban van.';
			$languages['val_date_range'] 		=	'Ez a dátum mező a %s és %s közé kell esnie.';
			$languages['val_date_min'] 			=	'Ez a dátum mező nagyobb vagy egyenlő % s kell lennie.';
			$languages['val_date_max'] 			=	'Ez a dátum mező kisebb, mint % s kell lennie.';
			$languages['val_date_na'] 			=	'Ez a dátum a kiválasztáshoz nem érhető el.';
			$languages['val_time'] 				=	'Ez a mező nincs a helyes idő formátumban.';
			$languages['val_phone'] 			=	'Kérem adjon meg egy érvényes telefonszámot.';
			$languages['val_filetype']			=	'A feltölteni kívánt fájltípus nem engedélyezett.';

			$languages['val_choice_atleast'] 	= 	'Minimum %s szükséges.'; 
			$languages['val_choice_atmost']  	= 	'Maximum %s engedélyezett.';
			$languages['val_choice_exactly'] 	= 	'Kell %s.';
			$languages['val_choice_between'] 	= 	'Jelenleg Megadott: %s.';
			
			//fields on excel/csv
			$languages['export_num']			=	'Száma.';
			$languages['date_created']			=	'Létrehozás dátuma';
			$languages['date_updated']			=	'Frissítésének dátuma';
			$languages['ip_address']			=	'IP Cím';

			//form resume
			$languages['resume_email_subject']		= 'A benyújtás {form_name} mentése megtörtént';
			$languages['resume_email_content'] 		= 'Köszönöm! A benyújtás <b>{form_name}</b> mentése megtörtént.<br /><br />Folytathatja az űrlapot bármikor az alábbi linkre kattintva:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>FONTOS:</b><br />A beadványt hiányos tekintik, amíg az űrlap folytatása, és nyomja meg a Küldés gombot.';							

			$languages['resume_success_title']   	= 'A haladás mentése megtörtént.';
			$languages['resume_success_content'] 	= 'Kérjük, másolja az alábbi linket, és mentse el egy biztonságos helyre:<br/>%s<br/><br/>Akkor folytatásához az űrlap bármikor megy a fenti linken.';

			$languages['resume_checkbox_title']		= 'Menteni a haladást és később folytathatja';
			$languages['resume_email_input_label']	= 'Adja meg email címét';
			$languages['resume_submit_button_text']	= 'Mentse az űrlapot, és később folytassa';
			$languages['resume_guideline']			= 'Egy különleges kapcsolat, hogy folytassa a űrlapot fog küldeni az e-mail címre.';

			//range validation
			$languages['range_type_digit']			= 'számjegy';
			$languages['range_type_chars'] 			= 'karakter';
			$languages['range_type_words'] 			= 'szavak';

			$languages['range_min']  				= 'Minimum %s szükséges.'; 
			$languages['range_min_entered']   		= 'Jelenleg Megadott: %s.';

			$languages['range_max']					= 'Maximum %s engedélyezett.';
			$languages['range_max_entered']   		= 'Jelenleg Megadott: %s.';

			$languages['range_min_max'] 			= 'Között kell lennie %s és %s.';
			$languages['range_min_max_same'] 		= 'Kell %s.';
			$languages['range_min_max_entered'] 	= 'Jelenleg Megadott: %s.';

			$languages['range_number_min']	 		= 'Kell lennie egy szám nagyobb vagy egyenlő-% s.';
			$languages['range_number_max']	 		= 'Kell lennie egy szám kisebb vagy egyenlő, mint % s.';
			$languages['range_number_min_max'] 		= 'Közötti számnak kell lennie %s és %s';

			//file uploads
			$languages['file_queue_limited'] 		= 'TEz a mező korlátozódik maximum %s fájlok.';
			$languages['file_upload_max']	   		= 'Hiba. Maximum %sMB engedélyezett.';
			$languages['file_type_limited']  		= 'Hiba. Ez a fájltípus nem engedélyezett.';
			$languages['file_error_upload']  		= 'Hiba! Nem lehet feltölteni';
			$languages['file_attach']		  		= 'Fájlok csatolása';
			$languages['file_select']		  		= 'Fájl(ok) kiválasztása';


			//payment total
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Összesen';
			$languages['form_payment_header_title'] = 'Fizetés';
			$languages['form_payment_title'] 		= 'Adja meg a fizetési adatokat';
			$languages['form_payment_description'] 	= 'A fizetési adatok megadása előtt olvassa el az alábbi részleteket.';
			$languages['payment_submit_button']		= 'Fizetés beküldése';
			$languages['tax']						= 'Adó';
			$languages['setup_fee']					= 'Telepítési költség';

			//payment details
			$languages['payment_status']			= 'Állapot';
			$languages['payment_id']				= 'Fizetési azonosító';
			$languages['payment_date']	 			= 'Fizetés dátuma';
			$languages['payment_fullname'] 			= 'Teljes név';
			$languages['payment_shipping'] 			= 'Szállítási cím';
			$languages['payment_billing']			= 'Számlázási cím';

			//coupon code
			$languages['coupon_not_exist'] 			= "Nincs ilyen kuponkód.";
			$languages['coupon_max_usage'] 			= "Ezt a kupont már nem lehet többször beváltani.";
			$languages['coupon_expired']   			= "Ez a kuponkód lejárt.";
			$languages['discount']		   			= "Kedvezmény";	

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   			= 'Nincsenek további lehetőségek';
			$languages['choice_max_entry'] 			= 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= 'Oldal %s a %s';		
		}else if($target_language == 'chinese'){
			//Traditional Chinese translation by Ho Bernie

			//simple name and extended name
			$languages['name_first']			= '姓氏';
			$languages['name_middle']			= '中間名';
			$languages['name_last']				= '名字';
			$languages['name_title']			= '稱謂';
			$languages['name_suffix']			= '字尾';
			
			//address
			$languages['address_street']		= '街道地址';
			$languages['address_street2']		= '地址第二行';
			$languages['address_city']			= '城市';
			$languages['address_state']			= '州 / 省 / 區域';
			$languages['address_zip']			= '郵遞區號';
			$languages['address_country']		= '國家';

			//captcha
			$languages['captcha_required']				= '此為必填欄位。請輸入圖片裡的字元。';
			$languages['captcha_mismatch']				= '輸入的字元和圖片不符，請重新輸入。';
			$languages['captcha_text_mismatch'] 		= '答案錯誤，請重新輸入。';
			$languages['captcha_error']					= '處理錯誤，請重新輸入。';
			$languages['captcha_simple_image_title']	= '請輸入圖片所呈現的字元。';
			$languages['captcha_simple_text_title']		= '此為防止機器人的機制，請回答問題。';
			
			//date
			$languages['date_dd']				= '日';
			$languages['date_mm']				= '月';
			$languages['date_yyyy']				= '西元年';
			
			//price
			$languages['price_dollar_main']		=	'元';
			$languages['price_dollar_sub']		=	'分';
			$languages['price_euro_main']		=	'歐元';
			$languages['price_euro_sub']		=	'分';
			$languages['price_pound_main']		=	'英鎊';
			$languages['price_pound_sub']		=	'便士';
			$languages['price_yen']				=	'日圓';
			$languages['price_baht_main']		=	'泰銖';
			$languages['price_baht_sub']		=	'泰銖';
			$languages['price_rupees_main']		=	'盧比';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'蘭特';
			$languages['price_rand_sub']		=	'分';
			$languages['price_forint_main']		=	'匈牙利幣';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'法郎';
			$languages['price_franc_sub']		=	'瑞分';
			$languages['price_koruna_main']		=	'韓幣';
			$languages['price_koruna_sub']		=	'土耳其幣';
			$languages['price_krona_main']		=	'瑞典克朗';
			$languages['price_krona_sub']		=	'義大利幣';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'披索';
			$languages['price_pesos_sub']		=	'分';
			$languages['price_ringgit_main']	=	'馬幣';
			$languages['price_ringgit_sub']		=	'馬幣';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'茲羅提';
			$languages['price_zloty_sub']		=	'格羅茨';
			$languages['price_riyals_main']		=	'里亞爾';
			$languages['price_riyals_sub']		=	'馬來西亞幣';
			
			//time
			$languages['time_hh']				=	'時';
			$languages['time_mm']				=	'分';
			$languages['time_ss']				=	'秒';
			
			//error message
			$languages['error_title']			=	'您的提交內容有問題。';
			$languages['error_desc']			=	'錯誤已被 <strong>標示</strong> 在下方。';
			
			//form buttons
			$languages['submit_button']			=	'提交/送出';
			$languages['continue_button']		=	'繼續';
			$languages['back_button']			=	'上一頁';
			
			//form status
			$languages['form_inactive']			=	'這個表單目前不接受填寫。';
			$languages['form_limited']			=   '抱歉，這個表單不再接受填寫。';
			
			//form password
			$languages['form_pass_title']		=	'這個表單被密碼保護。';
			$languages['form_pass_desc']		=	'請輸入密碼：';
			$languages['form_pass_invalid']		=	'密碼錯誤！';
			
			//form review
			$languages['review_title']			=	'檢視回顧填寫內容';
			$languages['review_message']		=	'請檢視回顧以下的填寫內容並按提交/送出按鈕完成程序';
			
			//validation message 
			$languages['val_required'] 			=	'這是必填欄位，請輸入適當內容';
			$languages['val_required_file'] 	=	'這是必填欄位，請選擇上傳檔案';
			$languages['val_unique'] 			=	'這個欄位的內容必須唯一，此內容已經存在';
			$languages['val_integer'] 			=	'這個欄位的內容必須是整數';
			$languages['val_float'] 			=	'這個欄位的內容必須是浮點數';
			$languages['val_numeric'] 			=	'這個欄位的內容必須是數字';
			$languages['val_email'] 			=	'這個欄位的內容並非正確的電子信箱格式';
			$languages['val_website'] 			=	'這個欄位的內容並非正確的網址格式';
			$languages['val_username'] 			=	'這個欄位的內容僅接受 a-z 和 0-9';
			$languages['val_equal'] 			=	'%s 必須相符';
			$languages['val_equal_email'] 		=	'Email 必須相符';
			$languages['val_date'] 				=	'這個欄位的內容並不符合正確的資料格式';
			$languages['val_date_range'] 		=	'這個日期欄位必須介於 %s 和 %s 之間';
			$languages['val_date_min'] 			=	'這個日期欄位必須大於或等於 %s.';
			$languages['val_date_max'] 			=	'這個日期欄位必須小於或等於 %s.';
			$languages['val_date_na'] 			=	'這個日期並不在允許範圍';
			$languages['val_time'] 				=	'這個欄位的內容並不符合正確的時間格式';
			$languages['val_phone'] 			=	'請輸入合法的電話號碼';
			$languages['val_filetype']			=	'欲上傳的檔案類型不被接受';

			$languages['val_choice_atleast'] 	= 	'最少字元 %s 是必要的';
			$languages['val_choice_atmost']  	= 	'最多可接受 %s 個字元';
			$languages['val_choice_exactly'] 	= 	'必須%s.';
			$languages['val_choice_between'] 	= 	'必須介於 %s 和 %s 之間';
			
			//fields on excel/csv
			$languages['export_num']			=	'否';
			$languages['date_created']			=	'建立日期';
			$languages['date_updated']			=	'更新日期';
			$languages['ip_address']			=	'網路IP位址';

			//form resume
			$languages['resume_email_subject']		= '您填寫的主旨給 {form_name} 已經妥善儲存';
			$languages['resume_email_content'] 		= '謝謝您！ 您填寫的內容給 <b>{form_name}</b> 已經妥善儲存<br /><br />您可點擊以下連結以便日後隨時重新開啟表格：<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>非常重要：</b><br />您填寫的內容直到重新開啟表格並按提交/送出按鈕之後才算完整';							

			$languages['resume_success_title']   	= '您填寫的內容已經妥善儲存';
			$languages['resume_success_content'] 	= '請複製以下網址連結並將其儲存在安全之處：<br/>%s<br/><br/>您可點擊以上連結以便日後隨時重新開啟表格';

			$languages['resume_checkbox_title']		= '儲存目前進度以供日後繼續填寫';
			$languages['resume_email_input_label']	= '請輸入您的電子信箱';
			$languages['resume_submit_button_text']	= '儲存填寫內容以供日後繼續';
			$languages['resume_guideline']			= '關於日後隨時能重新開啟表格的網址連結已傳送至您的電子信箱';

			//range validation
			$languages['range_type_digit']			= '位數';
			$languages['range_type_chars'] 			= '字母';
			$languages['range_type_words'] 			= '字元';

			$languages['range_min']  				= '最少字元 %s 是必要的'; 
			$languages['range_min_entered']   		= '目前已輸入： %s.';

			$languages['range_max']					= '最多可接受 %s 個字元';
			$languages['range_max_entered']   		= '目前已輸入： %s.';

			$languages['range_min_max'] 			= '必須介於 %s 和 %s 之間';
			$languages['range_min_max_same'] 		= '必須%s.';
			$languages['range_min_max_entered'] 	= '目前已輸入： %s.';

			$languages['range_number_min']	 		= '必須填寫大於或等於 %s 的數字';
			$languages['range_number_max']	 		= '必須填寫小於或等於 %s 的數字';
			$languages['range_number_min_max'] 		= '必須填寫數字且介於 %s 和 %s 之間';

			//file uploads
			$languages['file_queue_limited'] 		= '這個欄位僅接受 %s 個檔案';
			$languages['file_upload_max']	   		= '錯誤！檔案超過大小限制： %sMB ';
			$languages['file_type_limited']  		= '錯誤！此種檔案類型不支援！';
			$languages['file_error_upload']  		= '錯誤！無法上傳！';
			$languages['file_attach']		  		= '附加檔案';
			$languages['file_select']		  		= '選取檔案';

			//payment total
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= '總計';
			$languages['form_payment_header_title'] = '付款';
			$languages['form_payment_title'] = '填寫付款資訊';
			$languages['form_payment_description'] = '填寫付款資訊之前，請先預覽下列詳情';
			$languages['payment_submit_button']	 = '確定送出付款';
			$languages['tax']	 = '稅';
			$languages['setup_fee']					= 'Setup Fee';

			//payment details
			$languages['payment_status']	= '狀態';
			$languages['payment_id']	 	= '付款編號';
			$languages['payment_date']	 	= '付款日期';
			$languages['payment_fullname'] 	= '全名';
			$languages['payment_shipping'] 	= '物品運送地址';
			$languages['payment_billing']	= '帳單郵寄地址';

			//coupon code
			$languages['coupon_not_exist'] = "這個優惠代碼不存在";
			$languages['coupon_max_usage'] = "這個優惠代碼已達最大可兌換的次數上限";
			$languages['coupon_expired']   = "這個優惠代碼已經逾期";
			$languages['discount']		   = "折扣";		

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = '沒有更多選項';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= '目前頁數 %s 總頁數 %s';
		}else if($target_language == 'chinese_simplified'){
			//Simplified Chinese translation by Ho Bernie

			//simple name and extended name
			$languages['name_first']			= '姓氏';
			$languages['name_middle']			= 'Middle';
			$languages['name_last']				= '名字';
			$languages['name_title']			= '称谓';
			$languages['name_suffix']			= '字尾';
			
			//address
			$languages['address_street']		= '街道地址';
			$languages['address_street2']		= '地址第二行';
			$languages['address_city']			= '城市';
			$languages['address_state']			= '州 / 省 / 区域';
			$languages['address_zip']			= '邮政编码';
			$languages['address_country']		= '国家';

			//captcha
			$languages['captcha_required']				= '此为必填字段。请输入图片里的字符。';
			$languages['captcha_mismatch']				= '输入的字符和图片不符，请重新输入。';
			$languages['captcha_text_mismatch'] 		= '答案错误，请重新输入。';
			$languages['captcha_error']					= '处理错误，请重新输入。';
			$languages['captcha_simple_image_title']	= '请输入图片所呈现的字符。';
			$languages['captcha_simple_text_title']		= '此为防止机器人的机制，请回答问题。';
			
			//date
			$languages['date_dd']				= '日';
			$languages['date_mm']				= '月';
			$languages['date_yyyy']				= '公元年';
			
			//price
			$languages['price_dollar_main']		=	'元';
			$languages['price_dollar_sub']		=	'分';
			$languages['price_euro_main']		=	'欧元';
			$languages['price_euro_sub']		=	'分';
			$languages['price_pound_main']		=	'英镑';
			$languages['price_pound_sub']		=	'便士';
			$languages['price_yen']				=	'日圆';
			$languages['price_baht_main']		=	'泰铢';
			$languages['price_baht_sub']		=	'泰铢';
			$languages['price_rupees_main']		=	'卢比';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'兰特';
			$languages['price_rand_sub']		=	'分';
			$languages['price_forint_main']		=	'匈牙利币';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'法郎';
			$languages['price_franc_sub']		=	'瑞分';
			$languages['price_koruna_main']		=	'韩币';
			$languages['price_koruna_sub']		=	'土耳其币';
			$languages['price_krona_main']		=	'瑞典克朗';
			$languages['price_krona_sub']		=	'意大利币';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'披索';
			$languages['price_pesos_sub']		=	'分';
			$languages['price_ringgit_main']	=	'马币';
			$languages['price_ringgit_sub']		=	'马币';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'兹罗提';
			$languages['price_zloty_sub']		=	'格罗茨';
			$languages['price_riyals_main']		=	'里亚尔';
			$languages['price_riyals_sub']		=	'马来西亚币';
			
			//time
			$languages['time_hh']				=	'时';
			$languages['time_mm']				=	'分';
			$languages['time_ss']				=	'秒';
			
			//error message
			$languages['error_title']			=	'您的提交内容有问题。';
			$languages['error_desc']			=	'错误已被 <strong>标示</strong> 在下方。';
			
			//form buttons
			$languages['submit_button']			=	'提交/送出';
			$languages['continue_button']		=	'继续';
			$languages['back_button']			=	'上一页';
			
			//form status
			$languages['form_inactive']			=	'这个窗体目前不接受填写。';
			$languages['form_limited']			=   '抱歉，这个窗体不再接受填写。';
			
			//form password
			$languages['form_pass_title']		=	'这个窗体被密码保护。';
			$languages['form_pass_desc']		=	'请输入密码：';
			$languages['form_pass_invalid']		=	'密码错误！';
			
			//form review
			$languages['review_title']			=	'检视回顾填写内容';
			$languages['review_message']		=	'请检视回顾以下的填写内容并按提交/送出按钮完成程序';
			
			//validation message 
			$languages['val_required'] 			=	'这是必填字段，请输入适当内容';
			$languages['val_required_file'] 	=	'这是必填字段，请选择上传档案';
			$languages['val_unique'] 			=	'这个字段的内容必须唯一，此内容已经存在';
			$languages['val_integer'] 			=	'这个字段的内容必须是整数';
			$languages['val_float'] 			=	'这个字段的内容必须是浮点数';
			$languages['val_numeric'] 			=	'这个字段的内容必须是数字';
			$languages['val_email'] 			=	'这个字段的内容并非正确的电子信箱格式';
			$languages['val_website'] 			=	'这个字段的内容并非正确的网址格式';
			$languages['val_username'] 			=	'这个字段的内容仅接受 a-z 和 0-9';
			$languages['val_equal'] 			=	'%s 必须相符';
			$languages['val_equal_email'] 		=	'Email 必须相符';
			$languages['val_date'] 				=	'这个字段的内容并不符合正确的数据格式';
			$languages['val_date_range'] 		=	'这个日期字段必须介于 %s 和 %s 之间';
			$languages['val_date_min'] 			=	'这个日期字段必须大于或等于 %s.';
			$languages['val_date_max'] 			=	'这个日期字段必须小于或等于 %s.';
			$languages['val_date_na'] 			=	'这个日期并不在允许范围';
			$languages['val_time'] 				=	'这个字段的内容并不符合正确的时间格式';
			$languages['val_phone'] 			=	'请输入合法的电话号码';
			$languages['val_filetype']			=	'欲上传的文件类型不被接受';

			$languages['val_choice_atleast'] 	= 	'最少字符 %s 是必要的'; 
			$languages['val_choice_atmost']  	= 	'最多可接受 %s 个字符';
			$languages['val_choice_exactly'] 	= 	'必须%s.';
			$languages['val_choice_between'] 	= 	'必须介于 %s 和 %s 之间';
			
			//fields on excel/csv
			$languages['export_num']			=	'否';
			$languages['date_created']			=	'建立日期';
			$languages['date_updated']			=	'更新日期';
			$languages['ip_address']			=	'网络IP地址';

			//form resume
			$languages['resume_email_subject']		= '您填写的主旨给 {form_name} 已经妥善储存';
			$languages['resume_email_content'] 		= '谢谢您！ 您填写的内容给 <b>{form_name}</b> 已经妥善储存<br /><br />您可点击以下连结以便日后随时重新开启表格：<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>非常重要：</b><br />您填写的内容直到重新开启表格并按提交/送出按钮之后才算完整';							

			$languages['resume_success_title']   	= '您填写的内容已经妥善储存';
			$languages['resume_success_content'] 	= '请复制以下网址连结并将其储存在安全之处：<br/>%s<br/><br/>您可点击以上连结以便日后随时重新开启表格';

			$languages['resume_checkbox_title']		= '储存目前进度以供日后继续填写';
			$languages['resume_email_input_label']	= '请输入您的电子信箱';
			$languages['resume_submit_button_text']	= '储存填写内容以供日后继续';
			$languages['resume_guideline']			= '关于日后随时能重新开启表格的网址链接已传送至您的电子信箱';

			//range validation
			$languages['range_type_digit']			= '位数';
			$languages['range_type_chars'] 			= '字母';
			$languages['range_type_words'] 			= '字符';

			$languages['range_min']  				= '最少字符 %s 是必要的'; 
			$languages['range_min_entered']   		= '目前已输入： %s.';

			$languages['range_max']					= '最多可接受 %s 个字符';
			$languages['range_max_entered']   		= '目前已输入： %s.';

			$languages['range_min_max'] 			= '必须介于 %s 和 %s 之间';
			$languages['range_min_max_same'] 		= '必须%s.';
			$languages['range_min_max_entered'] 	= '目前已输入： %s.';

			$languages['range_number_min']	 		= '必须填写大于或等于 %s 的数字';
			$languages['range_number_max']	 		= '必须填写小于或等于 %s 的数字';
			$languages['range_number_min_max'] 		= '必须填写数字且介于 %s 和 %s 之间';

			//file uploads
			$languages['file_queue_limited'] 		= '这个字段仅接受 %s 个档案';
			$languages['file_upload_max']	   		= '错误！档案超过大小限制： %sMB ';
			$languages['file_type_limited']  		= '错误！此种文件类型不支援！';
			$languages['file_error_upload']  		= '错误！无法上传！';
			$languages['file_attach']		  		= '附加档案';
			$languages['file_select']		  		= 'Select Files';

			//payment total
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= '总计';
			$languages['form_payment_header_title'] = '付款';
			$languages['form_payment_title'] = '填写付款信息';
			$languages['form_payment_description'] = '填写付款信息之前，请先预览下列详情';
			$languages['payment_submit_button']	 = '确定送出付款';
			$languages['tax']	 = '税';
			$languages['setup_fee']					= 'Setup Fee';

			//payment details
			$languages['payment_status']	= '状态';
			$languages['payment_id']	 	= '付款编号';
			$languages['payment_date']	 	= '付款日期';
			$languages['payment_fullname'] 	= '全名';
			$languages['payment_shipping'] 	= '物品运送地址';
			$languages['payment_billing']	= '账单邮件地址';

			//coupon code
			$languages['coupon_not_exist'] = "这个优惠代码不存在";
			$languages['coupon_max_usage'] = "这个优惠代码已达最大可兑换的次数上限";
			$languages['coupon_expired']   = "这个优惠代码已经逾期";
			$languages['discount']		   = "折扣";		

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= '目前页数 %s 总页数 %s';
		}else if($target_language == 'bulgarian'){
			//simple name and extended name
			$languages['name_first']			= 'Име';
			$languages['name_middle']			= 'Бащино име';
			$languages['name_last']				= 'Фамилия';
			$languages['name_title']			= 'Обръщение';
			$languages['name_suffix']			= 'Титла';
			
			//address
			$languages['address_street']		= 'Улица';
			$languages['address_street2']		= 'Допълнителен ред за адрес';
			$languages['address_city']			= 'Град';
			$languages['address_state']			= 'Щат / Провинция / Регион';
			$languages['address_zip']			= 'Пощенски код';
			$languages['address_country']		= 'Държава';

			//captcha
			$languages['captcha_required']				= 'Това поле е задължително. Моля, въведете буквите, показани в изображението.';
			$languages['captcha_mismatch']				= 'Буквите от изображението не съвпадат. Опитайте отново.';
			$languages['captcha_text_mismatch'] 		= 'Неправилен отговор. Моля, опитайте отново.';
			$languages['captcha_error']					= 'Грешка при обработката, моля опитайте отново.';
			$languages['captcha_simple_image_title']	= 'Въведете буквите, които виждате в изображението по-долу.';
			$languages['captcha_simple_text_title']		= 'Спам защита. Моля, отговорете на този прост въпрос:';
			
			//date
			$languages['date_dd']				= 'ДД';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'ГГГГ';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'ЧЧ';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'СС';
			
			//error message
			$languages['error_title']			=	'Възникна проблем при подаването на информацията.';
			$languages['error_desc']			=	'Грешките са <strong>посочени</strong> по-долу.';
			
			//form buttons
			$languages['submit_button']			=	'Подаване';
			$languages['continue_button']		=	'Продължи';
			$languages['back_button']			=	'Предишен';
			
			//form status
			$languages['form_inactive']			=	'Тази форма в момента не е активна.';
			$languages['form_limited']			=   'Съжаляваме, но чрез тази форма вече не се приема информация.';
			
			//form password
			$languages['form_pass_title']		=	'Тази форма е защитена с парола.';
			$languages['form_pass_desc']		=	'Моля, въведете вашата парола.';
			$languages['form_pass_invalid']		=	'Невалидна парола!';
			
			//form review
			$languages['review_title']			=	'Прегледайте въведената информация';
			$languages['review_message']		=	'Моля, прегледайте въведената информация по-долу. Кликнете върху бутона Подаване, за да завършите.';
			
			//validation message 
			$languages['val_required'] 			=	'Това поле е задължително. Моля, въведете стойност.';
			$languages['val_required_file'] 	=	'Това поле е задължително. Моля, качете файл.';
			$languages['val_unique'] 			=	'Това поле изисква уникална въведена стойност и тази стойност вече е била използвана.';
			$languages['val_integer'] 			=	'Това поле трябва да бъде цяло число.';
			$languages['val_float'] 			=	'Това поле трябва да е с плаваща запетая.';
			$languages['val_numeric'] 			=	'Това поле трябва да е число.';
			$languages['val_email'] 			=	'Това поле не е в правилния формат за имейл.';
			$languages['val_website'] 			=	'Това поле не е в правилния формат на адрес на уеб сайт.';
			$languages['val_username'] 			=	'Това поле може да се състои само от a-z 0-9 и долни черти.';
			$languages['val_equal'] 			=	'%s трябва да съвпадат.';
			$languages['val_equal_email'] 		=	'имейл трябва да съвпадат.';
			$languages['val_date'] 				=	'Това поле не е в правилния формат за дата.';
			$languages['val_date_range'] 		=	'Това поле за дата трябва да бъде между %s и %s.';
			$languages['val_date_min'] 			=	'Това поле за дата трябва да бъде по-голямо или равно на %s.';
			$languages['val_date_max'] 			=	'Това поле за дата трябва да бъде по-малко или равно на %s.';
			$languages['val_date_na'] 			=	'Тази дата не е на разположение за селекция.';
			$languages['val_time'] 				=	'Това поле не е в правилния формат за време.';
			$languages['val_phone'] 			=	'Моля, въведете валиден телефонен номер.';
			$languages['val_filetype']			=	'Типът файл, който се опитвате да качите, не е разрешен.';

			$languages['val_choice_atleast'] 	= 	'Изискват се поне %s.'; 
			$languages['val_choice_atmost']  	= 	'Разрешени са максимум %s.';
			$languages['val_choice_exactly'] 	= 	'Трябва да бъде %s.';
			$languages['val_choice_between'] 	= 	'Трябва да бъде между %s и %s.';
			
			//fields on excel/csv
			$languages['export_num']			=	'№';
			$languages['date_created']			=	'Дата на създаване';
			$languages['date_updated']			=	'Дата на актуализация';
			$languages['ip_address']			=	'IP адрес';

			//form resume
			$languages['resume_email_subject']		= 'Подадената от вас информация във форма {form_name} беше запазена';
			$languages['resume_email_content'] 		= 'Благодарим ви! Подадената от вас информация в <b>{form_name}</b> беше запазена.<br /><br />Можете да възобновите формата по всяко време, като кликнете върху линка по-долу:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>ВАЖНО:</b><br />Вашето подаване се счита за непълно, докато не възобновите попълването на формата отново и не натиснете бутона Подаване.';							

			$languages['resume_success_title']   	= 'Въведената до момента информация беше записана.';
			$languages['resume_success_content'] 	= 'Моля, копирайте линка по-долу и го запишете на сигурно място:<br/>%s<br/><br/>Можете да възобновите попълването на формата по всяко време, като кликнете на линка по-горе.';

			$languages['resume_checkbox_title']		= 'Запишете въведената информация и продължете по-късно';
			$languages['resume_email_input_label']	= 'Въведете вашия имейл адрес';
			$languages['resume_submit_button_text']	= 'Запазете формата и продължете по-късно';
			$languages['resume_guideline']			= 'Специален линк, с който да продължите формата, ще ви бъде изпратен на вашия имейл адрес.';

			//range validation
			$languages['range_type_digit']			= 'цифри';
			$languages['range_type_chars'] 			= 'символи';
			$languages['range_type_words'] 			= 'думи';

			$languages['range_min']  				= 'Изискват се поне %s.'; 
			$languages['range_min_entered']   		= 'В момента са въведени: %s.';

			$languages['range_max']					= 'Разрешени са максимум %s.';
			$languages['range_max_entered']   		= 'В момента са въведени: %s.';

			$languages['range_min_max'] 			= 'Трябва да бъде между %s и %s.';
			$languages['range_min_max_same'] 		= 'Трябва да бъде %s.';
			$languages['range_min_max_entered'] 	= 'В момента са въведени: %s.';

			$languages['range_number_min']	 		= 'Трябва да е число, по-голямо или равно на %s.';
			$languages['range_number_max']	 		= 'Трябва да е число, по-малко или равно на %s.';
			$languages['range_number_min_max'] 		= 'Трябва да е число между %s и %s';

			//file uploads
			$languages['file_queue_limited'] 		= 'Това поле е ограничено до максимум %s файла.';
			$languages['file_upload_max']	   		= 'Грешка. Разрешени са максимум %sMB.';
			$languages['file_type_limited']  		= 'Грешка. Този тип файл не е разрешен.';
			$languages['file_error_upload']  		= 'Грешка! Файлът на може да бъде качен';
			$languages['file_attach']		  		= 'Прикачване на файлове';
			$languages['file_select']		  		= 'Select Files';

			//payment total
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Общо';
			$languages['form_payment_header_title'] = 'Payment';
			$languages['form_payment_title'] 		= 'Enter Payment Information';
			$languages['form_payment_description'] 	= 'Please review the details below before entering payment information.';
			$languages['payment_submit_button']		= 'Submit Payment';
			$languages['tax']						= 'Tax';
			$languages['setup_fee']					= 'Setup Fee';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'Payment ID';
			$languages['payment_date']	 	= 'Payment Date';
			$languages['payment_fullname'] 	= 'Full Name';
			$languages['payment_shipping'] 	= 'Shipping Address';
			$languages['payment_billing']	= 'Billing Address';

			//coupon code
			$languages['coupon_not_exist'] = "This coupon code does not exist.";
			$languages['coupon_max_usage'] = "This coupon has reached the maximum redemption limit.";
			$languages['coupon_expired']   = "This coupon code has been expired.";
			$languages['discount']		   = "Discount";	

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';	
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= 'Страница %s of %s';
		}else if($target_language == 'danish'){
			//simple name and extended name
			$languages['name_first']			= 'Fornavn';
			$languages['name_middle']			= 'Mellemnavn';
			$languages['name_last']				= 'Efternavn';
			$languages['name_title']			= 'Titel';
			$languages['name_suffix']			= 'Suffiks';
			
			//address
			$languages['address_street']		= 'Adresselinje 1';
			$languages['address_street2']		= 'Adresselinje 2';
			$languages['address_city']			= 'By';
			$languages['address_state']			= 'Stat / Provins / Region';
			$languages['address_zip']			= 'Postnummer';
			$languages['address_country']		= 'Land';

			//captcha
			$languages['captcha_required']				= 'Dette felt er påkrævet. Indtast bogstaverne vist på billedet.';
			$languages['captcha_mismatch']				= 'Bogstaverne i billedet stemmer ikke overens. Prøv igen.';
			$languages['captcha_text_mismatch'] 		= 'Forkert svar. Prøv igen.';
			$languages['captcha_error']					= 'Fejl under behandling, prøv igen.';
			$languages['captcha_simple_image_title']	= 'Indtast de bogstaver, du ser på billedet nedenfor.';
			$languages['captcha_simple_text_title']		= 'Spambeskyttelse. Besvar dette enkle spørgsmål:';
			
			//date
			$languages['date_dd']				= 'DD';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'ÅÅÅÅ';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'TT';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Der opstod et problem med lagringen af indtastningen i din formular.';
			$languages['error_desc']			=	'Fejlen er <strong>fremhævet</strong> nedenfor.';
			
			//form buttons
			$languages['submit_button']			=	'Send';
			$languages['continue_button']		=	'Fortsæt';
			$languages['back_button']			=	'Forrige';
			
			//form status
			$languages['form_inactive']			=	'Denne formular er i øjeblikket inaktiv.';
			$languages['form_limited']			=   'Beklager, men du kan ikke længere foretage indtastninger i denne formular.';
			
			//form password
			$languages['form_pass_title']		=	'Denne form er beskyttet med adgangskode.';
			$languages['form_pass_desc']		=	'Indtast din adgangskode.';
			$languages['form_pass_invalid']		=	'Ugyldig adgangskode!';
			
			//form review
			$languages['review_title']			=	'Gennemse din indtastning';
			$languages['review_message']		=	'Gennemse din indtastning nedenfor. Klik på Send-knappen for at afslutte.';
			
			//validation message 
			$languages['val_required'] 			=	'Dette felt er påkrævet. Angiv en værdi.';
			$languages['val_required_file'] 	=	'Dette felt er påkrævet. Upload en fil.';
			$languages['val_unique'] 			=	'Dette felt kræver en unik indtastning, og denne værdi er allerede blevet brugt.';
			$languages['val_integer'] 			=	'Dette felt skal indeholde en heltalsværdi.';
			$languages['val_float'] 			=	'Dette felt skal indeholde en hexadecimalværdi.';
			$languages['val_numeric'] 			=	'Dette felt skal indeholde en talværdi.';
			$languages['val_email'] 			=	'This field is not in the correct email format.';
			$languages['val_website'] 			=	'Dette felt indeholder ikke en e-mail i det korrekte e-mail-format.';
			$languages['val_username'] 			=	'Dette felt kan kun bestå af a-z, 0-9 og understregningstegn.';
			$languages['val_equal'] 			=	'%s skal matche.';
			$languages['val_equal_email'] 		=	'E-mail skal matche.';
			$languages['val_date'] 				=	'Dette felt indeholder ikke en dato i det korrekte datoformat.';
			$languages['val_date_range'] 		=	'Dette datofelt skal indeholde en værdi mellem %s og %s.';
			$languages['val_date_min'] 			=	'Dette datofelt skal indeholde en værdi større end eller lig med %s.';
			$languages['val_date_max'] 			=	'Dette datofelt skal indeholde en værdi mindre end eller lig med %s.';
			$languages['val_date_na'] 			=	'Denne dato kan ikke vælges.';
			$languages['val_time'] 				=	'Dette felt er ikke i det korrekte tidsformat.';
			$languages['val_phone'] 			=	'Indtast et gyldigt telefonnummer.';
			$languages['val_filetype']			=	'Den filtype, du forsøger at uploade, er ikke tilladt.';

			$languages['val_choice_atleast'] 	= 	'Minimum %s er påkrævet.';  
			$languages['val_choice_atmost']  	= 	'Højst %s tilladt.';
			$languages['val_choice_exactly'] 	= 	'Skal være %s.';
			$languages['val_choice_between'] 	= 	'Skal være mellem %s og %s.';
			
			//fields on excel/csv
			$languages['export_num']			=	'Nr.';
			$languages['date_created']			=	'Oprettelsesdato';
			$languages['date_updated']			=	'Opdateringsdato';
			$languages['ip_address']			=	'IP-adresse';

			//form resume
			$languages['resume_email_subject']		= 'Din indtastning i {form_name} er blevet gemt';
			$languages['resume_email_content'] 		= 'Tak! Din indtastning i <b>{form_name}</b> er blevet gemt.<br /><br />Du kan til enhver tid genoptage indtastning i formularen ved at klikke på linket nedenfor:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>VIGTIGT:</b><br />Din indtastning betragtes som ufuldstændig, indtil du genoptager indtastning i formularen og trykker på Send-knappen.';							

			$languages['resume_success_title']   	= 'Din formular er nu gemt.';
			$languages['resume_success_content'] 	= 'Kopier venligst nedenstående link og gem det et sikkert sted:<br/>%s<br/><br/>Du kan til enhver tid genoptage indtastning i formularen ved at klikke på ovenstående link.';

			$languages['resume_checkbox_title']		= 'Gem min indtastning og genoptag senere';
			$languages['resume_email_input_label']	= 'Indtast din e-mail-adresse';
			$languages['resume_submit_button_text']	= 'Gem formular og genoptag senere';
			$languages['resume_guideline']			= 'Et særligt link til genoptagelse af indtastning i formularen vil blive sendt til din e-mail-adresse.';

			//range validation
			$languages['range_type_digit']			= 'cifre';
			$languages['range_type_chars'] 			= 'tegn';
			$languages['range_type_words'] 			= 'ord';

			$languages['range_min']  				= 'Minimum %s er påkrævet.'; 
			$languages['range_min_entered']   		= 'Indtastet: %s.';

			$languages['range_max']					= 'Højst %s tilladt.';
			$languages['range_max_entered']   		= 'Indtastet: %s.';

			$languages['range_min_max'] 			= 'Skal være mellem %s og %s.';
			$languages['range_min_max_same'] 		= 'Skal være %s.';
			$languages['range_min_max_entered'] 	= 'Indtastet: %s.';

			$languages['range_number_min']	 		= 'Skal være et tal større end eller lig med %s.';
			$languages['range_number_max']	 		= 'Skal være et tal mindre end eller lig med %s.';
			$languages['range_number_min_max'] 		= 'Skal være et tal mellem %s og %s.';

			//file uploads
			$languages['file_queue_limited'] 		= 'Dette felt er begrænset til højst %s filer.';
			$languages['file_upload_max']	   		= 'Fejl! Højst %s MB tilladt.';
			$languages['file_type_limited']  		= 'Fejl. Denne filtype er ikke tilladt.';
			$languages['file_error_upload']  		= 'Fejl! Kan ikke uploade';
			$languages['file_attach']		  		= 'Vedhæft filer';
			$languages['file_select']		  		= 'Select Files';

			//payment total
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Total';
			$languages['form_payment_header_title'] = 'Payment';
			$languages['form_payment_title'] 		= 'Enter Payment Information';
			$languages['form_payment_description'] 	= 'Please review the details below before entering payment information.';
			$languages['payment_submit_button']		= 'Submit Payment';
			$languages['tax']						= 'Tax';
			$languages['setup_fee']					= 'Setup Fee';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'Payment ID';
			$languages['payment_date']	 	= 'Payment Date';
			$languages['payment_fullname'] 	= 'Full Name';
			$languages['payment_shipping'] 	= 'Shipping Address';
			$languages['payment_billing']	= 'Billing Address';

			//coupon code
			$languages['coupon_not_exist'] = "This coupon code does not exist.";
			$languages['coupon_max_usage'] = "This coupon has reached the maximum redemption limit.";
			$languages['coupon_expired']   = "This coupon code has been expired.";
			$languages['discount']		   = "Discount";	

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= 'Side %s af %s';
		}else if($target_language == 'finnish'){
			//simple name and extended name
			$languages['name_first']			= 'Etunimi';
			$languages['name_middle']			= 'Toinen nimi';
			$languages['name_last']				= 'Sukunimi';
			$languages['name_title']			= 'Sukupuoli';
			$languages['name_suffix']			= 'Arvonimi';
			
			//address
			$languages['address_street']		= 'Katuosoite';
			$languages['address_street2']		= 'Osoite 2. rivi';
			$languages['address_city']			= 'Kaupunki';
			$languages['address_state']			= 'Osavaltio / Alue';
			$languages['address_zip']			= 'Postinumero';
			$languages['address_country']		= 'Maa';

			//captcha
			$languages['captcha_required']				= 'Tämä kenttä on pakollinen. Syötä kuvassa näkyvät kirjaimet.';
			$languages['captcha_mismatch']				= 'Kirjaimet eivät täsmää. Yritä uudelleen.';
			$languages['captcha_text_mismatch'] 		= 'Väärä vastaus. Yritä uudelleen.';
			$languages['captcha_error']					= 'Käsittelyssä virhe, yritä uudelleen.';
			$languages['captcha_simple_image_title']	= 'Syötä kuvassa alla näkyvät kirjaimet.';
			$languages['captcha_simple_text_title']		= 'Roskapostisuojaus. Vastaa tähän helppoon kysymykseen:';
			
			//date
			$languages['date_dd']				= 'PV';
			$languages['date_mm']				= 'KK';
			$languages['date_yyyy']				= 'VVVV';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'TT';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Lähetyksessä oli ongelma.';
			$languages['error_desc']			=	'Virheet ovat <strong>korostettuina</strong> alla.';
			
			//form buttons
			$languages['submit_button']			=	'Lähetä';
			$languages['continue_button']		=	'Jatka';
			$languages['back_button']			=	'Edellinen';
			
			//form status
			$languages['form_inactive']			=	'Tämä lomake ei ole aktiivinen tällä hetkellä.';
			$languages['form_limited']			=   'Valitettavasti tämä lomake ei hyväksy enää uusia merkintöjä.';
			
			//form password
			$languages['form_pass_title']		=	'Tämä lomake on suojattu salasanalla.';
			$languages['form_pass_desc']		=	'Syötä salasanasi.';
			$languages['form_pass_invalid']		=	'Väärä salasana!';
			
			//form review
			$languages['review_title']			=	'Katso merkintäsi';
			$languages['review_message']		=	'Katso merkintäsi alla. Valitse Lähetä-nappi lopettaaksesi.';
			
			//validation message 
			$languages['val_required'] 			=	'Tämä kenttä vaaditaan. Syötä arvo.';
			$languages['val_required_file'] 	=	'Tämä kenttä vaaditaan. Lataa tiedosto.';
			$languages['val_unique'] 			=	'Tämä kenttä vaatii yksilöllisen tunnisteen ja arvo on jo käytetty.';
			$languages['val_integer'] 			=	'Tämän kentän arvon pitää olla kokonaisluku.';
			$languages['val_float'] 			=	'Tämän kentän arvon pitää olla liukuva.';
			$languages['val_numeric'] 			=	'Tämän kentän arvon pitää olla numero.';
			$languages['val_email'] 			=	'Tämä kenttä ei ole oikeassa sähköposti muodossa.';
			$languages['val_website'] 			=	'Tämä kenttä ei ole oikeassa verkkosivun osoite muodossa.';
			$languages['val_username'] 			=	'Tämä kenttä voi sisältää vain arvoja A-Z 0-9 ja alaviiva.';
			$languages['val_equal'] 			=	'%s tulee täsmätä.';
			$languages['val_equal_email'] 		=	'sähköposti tulee täsmätä.';
			$languages['val_date'] 				=	'Tämä kenttä ei ole oikeassa päivämäärä-muodossa.';
			$languages['val_date_range'] 		=	'Päivämääräkentän tulee olla välillä %s ja %s.';
			$languages['val_date_min'] 			=	'Päivämääräkentän tulee olla suurempi tai yhtä suuri kuin %s.';
			$languages['val_date_max'] 			=	'Päivämääräkentän tulee olla pienempi tai yhtä suuri kuin %s.';
			$languages['val_date_na'] 			=	'Tämä päivämäärä ei ole valittavissa.';
			$languages['val_time'] 				=	'Tämä kenttä ei ole oikeassa aikamuodossa.';
			$languages['val_phone'] 			=	'Syötä toiminnassa oleva puhelinnumero.';
			$languages['val_filetype']			=	'Tiedostotyyppi jota yrität ladata ei ole sallittu.';

			$languages['val_choice_atleast'] 	= 	'Vähintään %s vaaditaan.'; 
			$languages['val_choice_atmost']  	= 	'Enintään %s sallittu.';
			$languages['val_choice_exactly'] 	= 	'Täytyy olla %s.';
			$languages['val_choice_between'] 	= 	'On oltava %s-%s.';
			
			//fields on excel/csv
			$languages['export_num']			=	'Nro';
			$languages['date_created']			=	'Luontipäivämäärä';
			$languages['date_updated']			=	'Päivityspäivämäärä';
			$languages['ip_address']			=	'IP-osoite';

			//form resume
			$languages['resume_email_subject']		= '{form_name} lomakkeesi on tallennettu.';
			$languages['resume_email_content'] 		= 'Kiitos! <b>{form_name}</b> lomakkeesi on tallennettu.<br /><br />Voit jatkaa lomakkeella milloin tahansa valitsemalla linkin alla:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>TÄRKEÄÄ:</b><br />Lomake jää keskeneräiseksi, kunnes täydennät lomaketta ja painat lähetä-näppäintä.';							

			$languages['resume_success_title']   	= 'Muutokset on tallennettu.';
			$languages['resume_success_content'] 	= 'Kopioi linkki alta ja tallenna se turvalliseen paikkaan:<br/>%s<br/><br/>Voit jatkaa lomakkeella milloin tahansa yllä olevasta linkistä.';

			$languages['resume_checkbox_title']		= 'Tallenna muutokset ja jatka myöhemmin';
			$languages['resume_email_input_label']	= 'Syötä sähköpostiosoitteesi';
			$languages['resume_submit_button_text']	= 'Tallenna ja jatka myöhemmin';
			$languages['resume_guideline']			= 'Linkki lomakkeen muuttamiseksi lähetetään sähköpostiosoitteeseesi.';

			//range validation
			$languages['range_type_digit']			= 'numerot';
			$languages['range_type_chars'] 			= 'merkkiä';
			$languages['range_type_words'] 			= 'sanat';

			$languages['range_min']  				= 'Vähintään %s vaaditaan.'; 
			$languages['range_min_entered']   		= 'Nyt syötetty: %s.';

			$languages['range_max']					= 'Enintään %s sallittu.';
			$languages['range_max_entered']   		= 'Nyt syötetty: %s.';

			$languages['range_min_max'] 			= 'On oltava %s-%s.';
			$languages['range_min_max_same'] 		= 'Täytyy olla %s.';
			$languages['range_min_max_entered'] 	= 'Nyt syötetty: %s.';

			$languages['range_number_min']	 		= 'Luvun on oltava suurempi tai yhtä suuri kuin %s.';
			$languages['range_number_max']	 		= 'Luvun on oltava pienempi tai yhtä suuri kuin %s.';
			$languages['range_number_min_max'] 		= 'Pitää olla numero väliltä %s-%s';

			//file uploads
			$languages['file_queue_limited'] 		= 'Tämä kenttä on rajoitettu enintään %s tiedostolle.';
			$languages['file_upload_max']	   		= 'Virhe! Enintään %s Mb on sallittu.';
			$languages['file_type_limited']  		= 'Virhe. Tämä tiedostotyyppi ei ole sallittu.';
			$languages['file_error_upload']  		= 'Virhe! Lataus ei onnistu.';
			$languages['file_attach']		  		= 'Liitä tiedostoja';
			$languages['file_select']		  		= 'Select Files';

			//payment total
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Yhteensä';
			$languages['form_payment_header_title'] = 'Payment';
			$languages['form_payment_title'] 		= 'Enter Payment Information';
			$languages['form_payment_description'] 	= 'Please review the details below before entering payment information.';
			$languages['payment_submit_button']		= 'Submit Payment';
			$languages['tax']						= 'Tax';
			$languages['setup_fee']					= 'Setup Fee';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'Payment ID';
			$languages['payment_date']	 	= 'Payment Date';
			$languages['payment_fullname'] 	= 'Full Name';
			$languages['payment_shipping'] 	= 'Shipping Address';
			$languages['payment_billing']	= 'Billing Address';

			//coupon code
			$languages['coupon_not_exist'] = "This coupon code does not exist.";
			$languages['coupon_max_usage'] = "This coupon has reached the maximum redemption limit.";
			$languages['coupon_expired']   = "This coupon code has been expired.";
			$languages['discount']		   = "Discount";	

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';	
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= 'Sivu %s / %s';
		}else if($target_language == 'polish'){
			//simple name and extended name
			$languages['name_first']			= 'Imię';
			$languages['name_middle']			= 'Drugie Imię';
			$languages['name_last']				= 'Nazwisko';
			$languages['name_title']			= 'Tytuł';
			$languages['name_suffix']			= 'Przyrostek';
			
			//address
			$languages['address_street']		= 'Adres Ulica';
			$languages['address_street2']		= 'Adres, pole drugie';
			$languages['address_city']			= 'Miasto';
			$languages['address_state']			= 'Stan / Dzielnica / Okręg';
			$languages['address_zip']			= 'Pocztowy / Kod pocztowy';
			$languages['address_country']		= 'Kraj';

			//captcha
			$languages['captcha_required']				= 'To pole jest wymagane, wpisz litery widoczne na obrazku.';
			$languages['captcha_mismatch']				= 'Litery na obrazie nie pasują. Proszę spróbować jeszcze raz.';
			$languages['captcha_text_mismatch'] 		= 'Nieprawidłowa odpowiedź. Proszę spróbować jeszcze raz.';
			$languages['captcha_error']					= 'Błąd przetwarzania. Proszę spróbować ponownie.';
			$languages['captcha_simple_image_title']	= 'Proszę wpisać litery jakie widzisz na obrazie poniżej.';
			$languages['captcha_simple_text_title']		= 'Ochrona przed spamem. Proszę odpowiedzieć na poniższe proste pytanie:';
			
			//date
			$languages['date_dd']				= 'DD';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'RRRR';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'GG';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Wystąpił problem z Twoim zgłoszeniem.';
			$languages['error_desc']			=	'Błędy zostały <strong>podświetlone</strong> poniżej.';
			
			//form buttons
			$languages['submit_button']			=	'Wyślij';
			$languages['continue_button']		=	'Kontynuuj';
			$languages['back_button']			=	'Poprzedni';
			
			//form status
			$languages['form_inactive']			=	'Ten formularz jest obecnie nieaktywny.';
			$languages['form_limited']			=   'Przepraszamy, ale ten formularz nie akceptuje więcej wpisów.';
			
			//form password
			$languages['form_pass_title']		=	'Ten formularz jest zabezpieczony hasłem.';
			$languages['form_pass_desc']		=	'Proszę wprowadzić hasło.';
			$languages['form_pass_invalid']		=	'Błędne hasło!';
			
			//form review
			$languages['review_title']			=	'Sprawdź wprowadzone dane';
			$languages['review_message']		=	'Proszę, sprawdź dane widoczne poniżej. Aby zakończyć, kliknij Wyślij.';
			
			//validation message 
			$languages['val_required'] 			=	'To pole jest wymagane. Wprowadź treść.';
			$languages['val_required_file'] 	=	'To pole jest wymagane. Proszę przesłać plik.';
			$languages['val_unique'] 			=	'To pole wymaga wpisu niepowtarzalnego, a wartość została poprzednio już użyta.';
			$languages['val_integer'] 			=	'W tym polu musi być liczba całkowita.';
			$languages['val_float'] 			=	'W tym polu musi być liczba zmiennoprzecinkowa.';
			$languages['val_numeric'] 			=	'W tym polu musi być numer.';
			$languages['val_email'] 			=	'Pole nie ma prawidłowego formatu emailowego.';
			$languages['val_website'] 			=	'Pole nie jest w prawidłowym formacie internetowym.';
			$languages['val_username'] 			=	'Pole może składać się tylko z a-z, 0-9 i podkreśleń.';
			$languages['val_equal'] 			=	'%s muszą się zgadzać.';
			$languages['val_equal_email'] 		=	'emailowego muszą się zgadzać.';
			$languages['val_date'] 				=	'Pole nie ma prawidłowego formatu daty.';
			$languages['val_date_range'] 		=	'Pole daty musi być pomiędzy %s a %s.';
			$languages['val_date_min'] 			=	'Pole daty musi być większe lub równe %s.';
			$languages['val_date_max'] 			=	'Pole daty musi być mniejsze lub równe %s.';
			$languages['val_date_na'] 			=	'Brak możliwości wybrania tej daty.';
			$languages['val_time'] 				=	'Pole nie ma prawidłowego formatu czasowego.';
			$languages['val_phone'] 			=	'Proszę wprowadzić ważny numer telefonu.';
			$languages['val_filetype']			=	'Usiłowanie przesłania tego rodzaju pliku jest niedozwolone.';

			$languages['val_choice_atleast'] 	= 	'Wymaga się %s minimum.'; 
			$languages['val_choice_atmost']  	= 	'Dozwolone maksymalnie %s.';
			$languages['val_choice_exactly'] 	= 	'Musi być %s.';
			$languages['val_choice_between'] 	= 	'Ilość musi być od %s do %s';
			
			//fields on excel/csv
			$languages['export_num']			=	'Nie.';
			$languages['date_created']			=	'Data utworzenia';
			$languages['date_updated']			=	'Data uaktualniona';
			$languages['ip_address']			=	'Adres IP';

			//form resume
			$languages['resume_email_subject']		= 'Twoje złożenie do {form_name} zostało zapisane.';
			$languages['resume_email_content'] 		= 'Dziękujemy! Twoje złożenie do <b>{form_name}</b> zostało zapisane.<br /><br />Możesz wznowić formularz kiedykolwiek poprzez naciśnięcie odnośnika poniżej:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>UWAGA:</b><br />Twoje złożenie nie będzie uznane za zakończone do momentu gdy nie naciśniesz przycisk do złożenia.';							

			$languages['resume_success_title']   	= 'Twoje postępy zostały zapisane.';
			$languages['resume_success_content'] 	= 'Proszę skopiować poniższy odnośnik i zapisać go w bezpiecznym miejscu:<br/>%s<br/><br/>Możesz wznowić formularz kiedykolwiek poprzez użycie powyższego odnośnika.';

			$languages['resume_checkbox_title']		= 'Zapisz moje postępy aby wznowić je później';
			$languages['resume_email_input_label']	= 'Wpisz Twój adres emailowy';
			$languages['resume_submit_button_text']	= 'Zapisz formularz aby wznowić później';
			$languages['resume_guideline']			= 'Specjalny odnośnik aby wznowić formularz zostanie przesłany na Twój adres emailowy';

			//range validation
			$languages['range_type_digit']			= 'cyfry';
			$languages['range_type_chars'] 			= 'znaków';
			$languages['range_type_words'] 			= 'słowa';

			$languages['range_min']  				= 'Wymaga się %s minimum.'; 
			$languages['range_min_entered']   		= 'Obecnie Wpisane: %s.';

			$languages['range_max']					= 'Dozwolone max %s.';
			$languages['range_max_entered']   		= 'Aktualnie: %s.';

			$languages['range_min_max'] 			= 'Ilość musi być od %s do %s';
			$languages['range_min_max_same'] 		= 'Musi być %s.';
			$languages['range_min_max_entered'] 	= 'Obecnie Wpisane: %s.';

			$languages['range_number_min']	 		= 'Numer musi być większy lub równy %s.';
			$languages['range_number_max']	 		= 'Numer musi być mniejszy lub równy %s.';
			$languages['range_number_min_max'] 		= 'Numer musi pomiędzy %s a %s';

			//file uploads
			$languages['file_queue_limited'] 		= 'To pole jest ograniczone do maksymalnej ilości %s plików.';
			$languages['file_upload_max']	   		= 'Błąd! Maksymalna ilość dozwolona jest %s MB.';
			$languages['file_type_limited']  		= 'Błąd. Tego rodzaju plik jest niedozwolony.';
			$languages['file_error_upload']  		= 'Błąd! Nie można przesłać danych';
			$languages['file_attach']		  		= 'Załącz Pliki';
			$languages['file_select']		  		= 'Wybierz pliki';

			//payment total
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'W sumie';
			$languages['form_payment_header_title'] = 'Payment';
			$languages['form_payment_title'] 		= 'Enter Payment Information';
			$languages['form_payment_description'] 	= 'Please review the details below before entering payment information.';
			$languages['payment_submit_button']		= 'Submit Payment';
			$languages['tax']						= 'Tax';
			$languages['setup_fee']					= 'Setup Fee';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'Payment ID';
			$languages['payment_date']	 	= 'Payment Date';
			$languages['payment_fullname'] 	= 'Full Name';
			$languages['payment_shipping'] 	= 'Shipping Address';
			$languages['payment_billing']	= 'Billing Address';

			//coupon code
			$languages['coupon_not_exist'] = "This coupon code does not exist.";
			$languages['coupon_max_usage'] = "This coupon has reached the maximum redemption limit.";
			$languages['coupon_expired']   = "This coupon code has been expired.";
			$languages['discount']		   = "Discount";	

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';	
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= 'Strona %s z %s';
		}else if($target_language == 'greek'){
			//simple name and extended name
			$languages['name_first']			= 'Όνομα';
			$languages['name_middle']			= 'μέσο';
			$languages['name_last']				= 'Επίθετο';
			$languages['name_title']			= 'Τίτλος';
			$languages['name_suffix']			= 'Suffix';
			
			//address
			$languages['address_street']	=	'Διεύθυνση';
			$languages['address_street2']	=	'Διεύθυνση 2';
			$languages['address_city']		=	'Πόλη';
			$languages['address_state']		=	'Περιοχή';
			$languages['address_zip']		=	'Τ.Κ.';
			$languages['address_country']	=	'Χώρα';

			//captcha
			$languages['captcha_required']	=	'Αυτό το πεδίο είναι υποχρεωτικό. Παρακαλούμε πληκτρολογήστε τα γράμματα που βλέπετε.';
			$languages['captcha_mismatch']	=	'Τα γράμματα που δηλώσατε δεν ταιριάζουν με της φωτογραφίας. Δοκιμάστε ξανά.';
			$languages['captcha_text_mismatch'] 		= 'Incorrect answer. Please try again.';
			$languages['captcha_error']					= 'Σφάλμα κατα την επεξεργασία, παρακαλούμε δοκιμάστε ξανά.';
			$languages['captcha_simple_image_title'] = 'Πληκτρολογήστε τα γράμματα που βλέπετε στην εικόνα.';
			$languages['captcha_simple_text_title'] = 'Προστασία από Spam. Παρακαλούμε απαντήστε στην εξής ερώτηση:';
			
			//date
			$languages['date_dd']				= 'DD';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'YYYY';
			
			//price
			$languages['price_dollar_main']	=	'Δολλάρια';
			$languages['price_dollar_sub']	=	'Cents';
			$languages['price_euro_main']	=	'Ευρώ';
			$languages['price_euro_sub']	=	'Cents';
			$languages['price_pound_main']	=	'Λίρες';
			$languages['price_pound_sub']	=	'Πένες';
			$languages['price_yen']			=	'Γεν';
			$languages['price_baht_main']		=	'Μπαχτ';
			$languages['price_baht_sub']		=	'Σάτανγκ';
			$languages['price_rupees_main']		=	'Ρούπις';
			$languages['price_rupees_sub']		=	'Πεισες';
			$languages['price_rand_main']		=	'Ραντ';
			$languages['price_rand_sub']		=	'Σεντς';
			$languages['price_forint_main']		=	'Φοριντ';
			$languages['price_forint_sub']		=	'Φίλλερ';
			$languages['price_franc_main']		=	'Φράγκα';
			$languages['price_franc_sub']		=	'Ραππεν';
			$languages['price_koruna_main']		=	'Κορόνα';
			$languages['price_koruna_sub']		=	'Χαλερού';
			$languages['price_krona_main']		=	'Κρονορ';
			$languages['price_krona_sub']		=	'Ορε';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Πεσος';
			$languages['price_pesos_sub']		=	'Σεντς';
			$languages['price_ringgit_main']	=	'Ρινγκιτ';
			$languages['price_ringgit_sub']		=	'Σεν';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Ζλότι';
			$languages['price_zloty_sub']		=	'Γκροσζ';
			$languages['price_riyals_main']		=	'Ριγιαλς';
			$languages['price_riyals_sub']		=	'Χαλαλαμπ';
			
			//time
			$languages['time_hh']			=	'ΩΩ';
			$languages['time_mm']			=	'ΛΛ';
			$languages['time_ss']			=	'ΔΔ';
			
			//error message
			$languages['error_title']		=	'Υπάρχει πρόβλημα με αυτά που δηλώσατε.';
			$languages['error_desc']			=	'Τα λάθη έχουν <strong>έντονο</strong> χρώμμα.';
			
			//form buttons
			$languages['submit_button']		=	'Αποστολή';
			$languages['continue_button']	=	'Συνέχεια';
			$languages['back_button']		=	'Πίσω';
			
			//form status
			$languages['form_inactive']		=	'Η φόρμα αποστολής προσωρινά είναι ανενεργή.';
			$languages['form_limited']		='Η φόρμα αποστολής δεν δέχεται άλλες εγγραφές';
			
			//form password
			$languages['form_pass_title']	=	'Η φόρμα είναι προστατευμένη.';
			$languages['form_pass_desc']	=	'Παρακαλούμε δώστε τον κωδικό σας.';
			$languages['form_pass_invalid']	=	'Λάθος κωδικός!';
			
			//form review
			$languages['review_title']		=	'Επανεξετάστε τί έχετε δηλώσει';
			$languages['review_message']	=	'Παρακαλώ ελέγχτε τα στοιχεία σας. Πατήστε αποστολή για να ολοκληρώσετε.';
			
			//validation message 
			$languages['val_required'] 		=	'Το πεδίο είναι υποχρεωτικό για συμπλήρωση.';
			$languages['val_required_file'] =	'Το ανέβασμα είναι υποχρεωτικό. Παρακαλούμε επιλέξτε ένα αρχείο σας';
			$languages['val_unique'] 		=	'Έχετε ήδη χρησιμοποιήσει μια φορά τη φόρμα μας.';
			$languages['val_integer'] 		=	'Αυτό το πεδίο πρέπει να έχει ακέραιο αριθμό.';
			$languages['val_float'] 		=	'Αυτό το πεδίο πρέπει να εξέχει.';
			$languages['val_numeric'] 		=	'Πρέπει να είναι αριθμός';
			$languages['val_email'] 		=	'Δεν έχετε πληκτρολογήσει σωστά το e-mail σας';
			$languages['val_website'] 		=	'Δεν έχετε πληκτρολογήσει σωστά την ιστοσελίδα σας.';
			$languages['val_username'] 		=	'Μόνο γράμματα και αριθμούς επιτρέπονται και ο χαρακτήρας _.';
			$languages['val_equal'] 		=	'%s πρέπει να είναι το ίδιο.';
			$languages['val_equal_email'] 	=	'E-mail πρέπει να είναι το ίδιο.';
			$languages['val_date'] 			=	'Δεν έχετε πληκτρολογήσει σωστά την ημερομηνία.';
			$languages['val_date_range'] 	=	'Η ημερομηνία πρέπει να είναι μεταξύ %s και %s.';
			$languages['val_date_min'] 		=	'Η ημερομηνία πρέπει να είναι ίση ή μεγαλύτερη με %s.';
			$languages['val_date_max'] 		=	'Η ημερομηνία πρέπει να είναι μικρότερη ή ίση με %s.';
			$languages['val_date_na'] 		=	'Η ημερομηνία δεν είναι διαθέσιμη προς επιλογή.';
			$languages['val_time'] 			=	'Το πεδίο δεν έχει σωστά τη μορφή της ώρας.';
			$languages['val_phone'] 		=	'Παρακαλούμε πληκτρολογήστε σωστά τον αριθμό τηλεφώνου σας.';
			$languages['val_filetype']		=	'Η μορφή του αρχείου που ανεβάζετε δεν είναι αποδεκτή, δοκιμάστε ένα άλλο';

			$languages['val_choice_atleast'] 	= 	'Το ελάχιστο %s είναι υποχρεωτικό.'; 
			$languages['val_choice_atmost']  	= 	'Συνολικά %s επιτρέπονται.';
			$languages['val_choice_exactly'] 	= 	'Πρέπει να είναι %s.';
			$languages['val_choice_between'] 	= 	'Πρέπει να είναι μεταξύ %s και %s.';
			
			//fields on excel/csv
			$languages['export_num']		=	'No.';
			$languages['date_created']		=	'Ημερομηνία Δημιουργίας';
			$languages['date_updated']		=	'Ημερομηνία Ενημέρωσης';
			$languages['ip_address']		=	'Διεύθυνση IP';

			//form resume
			$languages['resume_email_subject']		= 'Η υποβολή σας στο {form_name} έχει αποθηκευτεί';
			$languages['resume_email_content'] 		= 'Σας ευχαριστούμε! Η υποβολή σας σε <b>{form_name}</ b> έχει αποθηκευτεί <br /> <br /> Μπορείτε να συνεχίσετε τη φόρμα, ανά πάσα στιγμή κάνοντας κλικ στον παρακάτω σύνδεσμο:. <br /> <a href="{resume_url}">{resume_url}</a> <br /> <br /> <br /> <br /> <b> ΣΗΜΑΝΤΙΚΟ: </ b> <br /> η υποβολή σας θεωρείται ελλιπής έως ότου συνεχίσετε τη φόρμα και πατήστε το κουμπί υποβολή.';							

			$languages['resume_success_title']   	= 'Η πρόοδός σας έχει αποθηκευτεί.';
			$languages['resume_success_content'] 	= 'Παρακαλώ αντιγράψτε τον παρακάτω σύνδεσμο και αποθηκεύστε το σε ασφαλές μέρος:<br/>%s<br/><br/>Μπορείτε να συνεχίσετε τη φόρμα, ανά πάσα στιγμή πηγαίνοντας στο παραπάνω σύνδεσμο.';

			$languages['resume_checkbox_title']		= 'Αποθηκεύστε την πρόοδο μου και να συνεχίσετε αργότερα';
			$languages['resume_email_input_label']	= 'Εισάγετε το email σας';
			$languages['resume_submit_button_text']	= 'Αποθηκεύστε τη φόρμα για να συνεχίσετε αργότερα';
			$languages['resume_guideline']			= 'Ένας ειδικός σύνδεσμος για να συνεχίσετε το έντυπο θα αποσταλεί στην ηλεκτρονική σας διεύθυνσή.';

			//range validation
			$languages['range_type_digit']			= 'ψηφία';
			$languages['range_type_chars'] 			= 'χαρακτήρες';
			$languages['range_type_words'] 			= 'λέξεις';

			$languages['range_min']  				= 'Το ελάχιστο %s είναι υποχρεωτικό.'; 
			$languages['range_min_entered']   		= 'Έχετε επιλέξει: %s.';

			$languages['range_max']					= 'Συνολικά %s επιτρέπονται.';
			$languages['range_max_entered']   		= 'Έχετε επιλέξει: %s.';

			$languages['range_min_max'] 			= 'Πρέπει να είναι μεταξύ %s και %s.';
			$languages['range_min_max_same'] 		= 'Πρέπει να είναι %s.';
			$languages['range_min_max_entered'] 	= 'Έχετε επιλέξει: %s.';

			$languages['range_number_min']	 		= 'Πρέπει να είναι αριθμός μεγαλύτερος από %s.';
			$languages['range_number_max']	 		= 'Πρέπει να είναι αριθμός μικρότερος από %s.';
			$languages['range_number_min_max'] 		= 'Πρέπει να είναι αριθμός μεταξύ %s και %s';

			//file uploads
			$languages['file_queue_limited'] 			= 'Μπορείτε να ανεβάσετε μέχρι και %s αρχεία.';
			$languages['file_upload_max']	   			= 'Σφάλμα. Μπορείτε να ανεβάσετε έως %sMB .';
			$languages['file_type_limited']  			= 'Σφάλμα. Ο τύπος του αρχείου δεν είναι αποδεκτός.';
			$languages['file_error_upload']  			= 'Σφάλμα! Το ανέβασμα απέτυχε!';
			$languages['file_attach']		  			= 'Επισύναψη Αρχείων';
			$languages['file_select']		  		= 'Select Files';

			//payment page
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Σύνολο';
			$languages['form_payment_header_title'] = 'Payment';
			$languages['form_payment_title'] 		= 'Enter Payment Information';
			$languages['form_payment_description'] 	= 'Please review the details below before entering payment information.';
			$languages['payment_submit_button']		= 'Submit Payment';
			$languages['tax']						= 'Tax';
			$languages['setup_fee']					= 'Setup Fee';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'Payment ID';
			$languages['payment_date']	 	= 'Payment Date';
			$languages['payment_fullname'] 	= 'Full Name';
			$languages['payment_shipping'] 	= 'Shipping Address';
			$languages['payment_billing']	= 'Billing Address';

			//coupon code
			$languages['coupon_not_exist'] = "This coupon code does not exist.";
			$languages['coupon_max_usage'] = "This coupon has reached the maximum redemption limit.";
			$languages['coupon_expired']   = "This coupon code has been expired.";
			$languages['discount']		   = "Discount";	

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';	
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= 'Σελίδα %s από %s';
		}else if($target_language == 'norwegian'){
			//simple name and extended name
			$languages['name_first']			= 'Fornavn';
			$languages['name_middle']			= 'Mellomnavn';
			$languages['name_last']				= 'Etternavn';
			$languages['name_title']			= 'Tittel';
			$languages['name_suffix']			= 'Suffiks';
			
			//address
			$languages['address_street']		= 'Gatenavn';
			$languages['address_street2']		= 'Addresse linje 2';
			$languages['address_city']			= 'By';
			$languages['address_state']			= 'Stat / Kommune / Fylke';
			$languages['address_zip']			= 'Postnummer';
			$languages['address_country']		= 'Land';

			//captcha
			$languages['captcha_required']				= 'Dette feltet er påkrevd. Vennligst oppgi bokstavene som vises i bildet.';
			$languages['captcha_mismatch']				= 'Bokstavene i bildet stemmer ikke overens. Prøv igjen.';
			$languages['captcha_text_mismatch'] 		= 'Feil svar. Vennligst prøv igjen.';
			$languages['captcha_error']					= 'Feil under behandling, vennligst prøv igjen.';
			$languages['captcha_simple_image_title']	= 'Skriv inn bokstavene som du ser i bildet nedenfor.';
			$languages['captcha_simple_text_title']		= 'Spambeskyttelse. Vennligst svar på dette enkle spørsmålet:';
			
			//date
			$languages['date_dd']				= 'DD';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'ÅÅÅÅ';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'HH';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Det oppstod et problem med innsendingen din.';
			$languages['error_desc']			=	'Feil er <strong>markert</strong> nedenfor.';
			
			//form buttons
			$languages['submit_button']			=	'Sende';
			$languages['continue_button']		=	'Fortsette';
			$languages['back_button']			=	'Forrige';
			
			//form status
			$languages['form_inactive']			=	'Dette skjemaet er for øyeblikket inaktivt.';
			$languages['form_limited']			=   'Beklager, men dette skjemaet aksepterer ikke lengre noen oppføringer.';
			
			//form password
			$languages['form_pass_title']		=	'Dette skjemaet er passordbeskyttet.';
			$languages['form_pass_desc']		=	'Vennligst oppgi ditt passord.';
			$languages['form_pass_invalid']		=	'Ugyldig passord!';
			
			//form review
			$languages['review_title']			=	'Gjennomgå din oppføring';
			$languages['review_message']		=	'Vennligst gjennomgå oppføringen din nedenfor. Klikk sendeknappen for å fullføre.';
			
			//validation message 
			$languages['val_required'] 			=	'Dette feltet er obligatorisk. Vennligst oppgi en verdi.';
			$languages['val_required_file'] 	=	'Dette feltet er obligatorisk. Vennligst last opp en fil.';
			$languages['val_unique'] 			=	'Dette feltet krever en spesiell oppføring og denne verdien er allerede brukt.';
			$languages['val_integer'] 			=	'Dette feltet må være et heltall.';
			$languages['val_float'] 			=	'Dette feltet har en minimumslengde.';
			$languages['val_numeric'] 			=	'Dette feltet må være et nummer.';
			$languages['val_email'] 			=	'Dette feltet er ikke i riktig e-postformat.';
			$languages['val_website'] 			=	'Dette feltet er ikke i riktig format for nettsideaddresse.';
			$languages['val_username'] 			=	'Dette feltet kan bare bestå av a-z, 0-9 og understreking.';
			$languages['val_equal'] 			=	'%s må stemme overens.';
			$languages['val_equal_email'] 		=	'E-post må stemme overens.';
			$languages['val_date'] 				=	'Dette feltet er ikke i riktig datoformat.';
			$languages['val_date_range'] 		=	'Dette datofeltet må være mellom %s og %s.';
			$languages['val_date_min'] 			=	'Dette datofeltet må være større enn eller det samme som %s.';
			$languages['val_date_max'] 			=	'Dette datofeltet må være mindre enn eller det samme som %s.';
			$languages['val_date_na'] 			=	'Denne datoen kan ikke velges.';
			$languages['val_time'] 				=	'Dette feltet er ikke i riktig tidsformat.';
			$languages['val_phone'] 			=	'Vennligst oppgi et gyldig telefonnummer.';
			$languages['val_filetype']			=	'Filtypen som du forsøker å laste opp er ikke tillatt.';

			$languages['val_choice_atleast'] 	= 	'Minimum %s er påkrevet.';  
			$languages['val_choice_atmost']  	= 	'Maksimum %s tillatt.';
			$languages['val_choice_exactly'] 	= 	'Må være %s.';
			$languages['val_choice_between'] 	= 	'Må være mellom %s og %s.';
			
			//fields on excel/csv
			$languages['export_num']			=	'No.';
			$languages['date_created']			=	'Dato Opprettet';
			$languages['date_updated']			=	'Dato Oppdatert';
			$languages['ip_address']			=	'IP-addresse';

			//form resume
			$languages['resume_email_subject']		= 'Din innsending til {form_name}-skjema er lagret.';
			$languages['resume_email_content'] 		= 'Takk! Din innsending til <strong>{form_name}</stronh>-skjema er lagret.<br /><br />Du kan gjenoppta skjemaet til enhver tid ved å klikke på linken nedenfor:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>VIKTIG:</b><br />Innsendingen din betraktes som ufullstendig inntil du gjenopptar skjemaet og trykker på sendeknappen.';							

			$languages['resume_success_title']   	= 'Dine oppføringer er lagret.';
			$languages['resume_success_content'] 	= 'Vennligst kopier linken nedenfor og lagre den på en sikker plass:<br/>%s<br/><br/>Du kan gjenoppta skjemaet til enhver tid ved å gå til linken ovenfor.';

			$languages['resume_checkbox_title']		= 'Lagre mine oppføringer og gjenoppta senere';
			$languages['resume_email_input_label']	= 'Oppgi din e-postaddresse';
			$languages['resume_submit_button_text']	= 'Lagre skjema og gjenoppta senere';
			$languages['resume_guideline']			= 'En spesiell link for gjenopptagelse av skjemaet ditt sendes til e-postaddressen din.';

			//range validation
			$languages['range_type_digit']			= 'sifre';
			$languages['range_type_chars'] 			= 'tegn';
			$languages['range_type_words'] 			= 'ord';

			$languages['range_min']  				= 'Minimum %s er påkrevet.'; 
			$languages['range_min_entered']   		= 'Foreløpig Oppgitt: %s.';

			$languages['range_max']					= 'Maksimum %s tillatt.';
			$languages['range_max_entered']   		= 'Foreløpig Oppgitt: %s.';

			$languages['range_min_max'] 			= 'Må være mellom %s og %s.';
			$languages['range_min_max_same'] 		= 'Må være %s.';
			$languages['range_min_max_entered'] 	= 'Foreløpig Oppgitt: %s.';

			$languages['range_number_min']	 		= 'Må være et nummer større enn eller det samme som %s.';
			$languages['range_number_max']	 		= 'Må være et nummer mindre enn eller det samme som %s.';
			$languages['range_number_min_max'] 		= 'Må være et nummer mellom %s og %s.';

			//file uploads
			$languages['file_queue_limited'] 		= 'Dette feltet er begrenset til maksimum %s filer.';
			$languages['file_upload_max']	   		= 'Feil! Maksimum %sMB tillatt.';
			$languages['file_type_limited']  		= 'Feil. Denne filtypen er ikke tillatt.';
			$languages['file_error_upload']  		= 'Feil! Kan ikke laste opp';
			$languages['file_attach']		  		= 'Legge ved filer';
			$languages['file_select']		  		= 'Select Files';

			//payment page
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Totalt';
			$languages['form_payment_header_title'] = 'Payment';
			$languages['form_payment_title'] 		= 'Oppgi betalingsinformasjon';
			$languages['form_payment_description'] 	= 'Vennligst gjennomgå detaljene nedenfor før du oppgir betalingsinformasjon.';
			$languages['payment_submit_button']		= 'Submit Payment';
			$languages['tax']						= 'Tax';
			$languages['setup_fee']					= 'Setup Fee';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'Payment ID';
			$languages['payment_date']	 	= 'Payment Date';
			$languages['payment_fullname'] 	= 'Full Name';
			$languages['payment_shipping'] 	= 'Shipping Address';
			$languages['payment_billing']	= 'Billing Address';

			//coupon code
			$languages['coupon_not_exist'] = "This coupon code does not exist.";
			$languages['coupon_max_usage'] = "This coupon has reached the maximum redemption limit.";
			$languages['coupon_expired']   = "This coupon code has been expired.";
			$languages['discount']		   = "Discount";	

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= 'Side %s av %s';
		}else if($target_language == 'romanian'){
			//simple name and extended name
			$languages['name_first']			= 'Nume';
			$languages['name_middle']			= 'Prenume';
			$languages['name_last']				= 'Prenume';
			$languages['name_title']			= 'Titlu';
			$languages['name_suffix']			= 'Sufix';
			
			//address
			$languages['address_street']		= 'Strada';
			$languages['address_street2']		= 'Adresa Linia 2';
			$languages['address_city']			= 'Localitatea';
			$languages['address_state']			= 'Oraş';
			$languages['address_zip']			= 'Codul poştal';
			$languages['address_country']		= 'Ţara';

			//captcha
			$languages['captcha_required']				= 'Acest câmp este obligatoriu. Te rugăm să introduci literele afişate în imagine.';
			$languages['captcha_mismatch']				= 'Literele introduse nu corespund celor din imagine. Te rugăm să mai încerci odată.';
			$languages['captcha_text_mismatch'] 		= 'Răspuns incorect. Te rugăm să mai incerci odată.';
			$languages['captcha_error']					= 'Eroare la procesare, te rugăm să mai incerci odată.';
			$languages['captcha_simple_image_title']	= 'Introduceţi literele pe care le vedeţi în imaginea de mai jos.';
			$languages['captcha_simple_text_title']		= 'Protecţie SPAM. Te rugăm să răspunzi la următoarea întrebare:';
			
			//date
			$languages['date_dd']				= 'ZZ';
			$languages['date_mm']				= 'LL';
			$languages['date_yyyy']				= 'AAAA';
			
			//price
			$languages['price_dollar_main']		=	'Dolari';
			$languages['price_dollar_sub']		=	'Cenţi';
			$languages['price_euro_main']		=	'Euro';
			$languages['price_euro_sub']		=	'Cenţi';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'HH';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Ooops! Avem o problemă cu procesarea datelor introduse.';
			$languages['error_desc']			=	'Erorile şi explicaţiile sunt <strong>evidenţiate</strong> mai jos.';
			
			//form buttons
			$languages['submit_button']			=	'Expediază';
			$languages['continue_button']		=	'Continuă';
			$languages['back_button']			=	'Înapoi';
			
			//form status
			$languages['form_inactive']			=	'Acest formular este inactiv.';
			$languages['form_limited']			=   'Ne pare rău, dar acest formular nu mai acceptă date noi.';
			
			//form password
			$languages['form_pass_title']		=	'Formularul este protejat prin parolă de acces.';
			$languages['form_pass_desc']		=	'Te rugăm să introduci parola.';
			$languages['form_pass_invalid']		=	'Parolă incorectă!';
			
			//form review
			$languages['review_title']			=	'Verifică datele introduse';
			$languages['review_message']		=	'Te rugăm să revizuieşti datele introduse. Pentru a expedia datele apasă pe butonul Expediază.';
			
			//validation message 
			$languages['val_required'] 			=	'Acest câmp este obligatoriu. Te rugăm să introduci o valoare.';
			$languages['val_required_file'] 	=	'Acest câmp este obligatoriu. Te rugăm să introduci un fişier.';
			$languages['val_unique'] 			=	'Acest câmp poate fi completat doar cu date unice iar această valoarea a mai fost folosită.';
			$languages['val_integer'] 			=	'Acest câmp trebuie sa fie un cifră.';
			$languages['val_float'] 			=	'Acest câmp trebuie sa fie un decimal.';
			$languages['val_numeric'] 			=	'Acest câmp trebuie sa fie un număr.';
			$languages['val_email'] 			=	'Acest câmp nu conţine o adresă email validă.';
			$languages['val_website'] 			=	'Acest câmp nu conţine o adresă web validă.';
			$languages['val_username'] 			=	'Acest câmp poate conţine doar litere de la a-z, numere de la 0-9 şi subliniere.';
			$languages['val_equal'] 			=	'%s trebuie sa coiincidă.';
			$languages['val_equal_email'] 		=	'Email trebuie sa coiincidă.';
			$languages['val_date'] 				=	'Acest câmp nu contine un format valid de dată.';
			$languages['val_date_range'] 		=	'Acest câmp de date trebuie să fie între %s şi %s.';
			$languages['val_date_min'] 			=	'Acest câmp de date trebuie să fie mai mare sau egal cu %s.';
			$languages['val_date_max'] 			=	'Acest câmp de date trebuie să fie mai mic sau egal cu %s.';
			$languages['val_date_na'] 			=	'Această dată nu este disponibilă pentru selectare.';
			$languages['val_time'] 				=	'Acest câmp nu conţine un format valid de oră.';
			$languages['val_phone'] 			=	'Te rugăm să introduci un format valid de număr de telefon.';
			$languages['val_filetype']			=	'Încărcarea acestui tip de fişier nu este permisă.';

			$languages['val_choice_atleast'] 	= 	'Minimum de %s este obligatoriu.'; 
			$languages['val_choice_atmost']  	= 	'Permis un Maxim de %s.';
			$languages['val_choice_exactly'] 	= 	'Trebuie să fie de %s.';
			$languages['val_choice_between'] 	= 	'Trebuie să fie între %s şi %s.';
			
			//fields on excel/csv
			$languages['export_num']			=	'Nu.';
			$languages['date_created']			=	'Data Crearii';
			$languages['date_updated']			=	'Data Actualizării';
			$languages['ip_address']			=	'Adresa IP';

			//form resume
			$languages['resume_email_subject']		= 'Completarea şi datele de la {form_name} au fost salvate';
			$languages['resume_email_content'] 		= 'Mulţumim! Completarea şi datele de la <b>{form_name}</b> au fost salvate.<br /><br />Poţi continua cu formularul oricând accesând legătura de mai jos:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>IMPORTANT:</b><br />Completarea este considerată incompletă până ce nu va fi apăsat butonul Expediază.';							

			$languages['resume_success_title']   	= 'Progresul a fost înregistrat.';
			$languages['resume_success_content'] 	= 'Copiază şi salvează legătura de mai jos:<br/>%s<br/><br/>Poţi relua lucrul la formular oricând accesând legătura.';

			$languages['resume_checkbox_title']		= 'Salvează progresul şi revino mai târziu';
			$languages['resume_email_input_label']	= 'Introduce adresa de E-mail';
			$languages['resume_submit_button_text']	= 'Salvează formularul şi revino mai târziu';
			$languages['resume_guideline']			= 'Pentru continuarea formularului, un link va fi expediat pe adresa de E-mail.';

			//range validation
			$languages['range_type_digit']			= 'numere';
			$languages['range_type_chars'] 			= 'caractere';
			$languages['range_type_words'] 			= 'cuvinte';

			$languages['range_min']  				= 'Minimum de %s este obligatoriu.'; 
			$languages['range_min_entered']   		= 'Introduse în mod curent: %s.';

			$languages['range_max']					= 'Permis un Maxim de %s.';
			$languages['range_max_entered']   		= 'Introduse în mod curent: %s.';

			$languages['range_min_max'] 			= 'Trebuie să fie intre %s şi %s.';
			$languages['range_min_max_same'] 		= 'Trebuie să fie de %s.';
			$languages['range_min_max_entered'] 	= 'Introduse în mod curent: %s.';

			$languages['range_number_min']	 		= 'Trebuie să fie un număr mai mare sau egal cu %s.';
			$languages['range_number_max']	 		= 'Trebuie să fie un număr mai mic sau egal cu %s.';
			$languages['range_number_min_max'] 		= 'Trebuie să fie un număr între %s şi %s';

			//file uploads
			$languages['file_queue_limited'] 		= 'Acest câmp este limitat la un maxim de %s fişiere.';
			$languages['file_upload_max']	   		= 'Eroare. Maximum permis este de %sMB.';
			$languages['file_type_limited']  		= 'Eroare. Acest tip de fişier nu este permis.';
			$languages['file_error_upload']  		= 'Eroare! Fişierul nu poate fi încarcat.';
			$languages['file_attach']		  		= 'Ataşeaza fişiere';
			$languages['file_select']		  		= 'Select Files';

			//payment page
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Total';
			$languages['form_payment_header_title'] = 'Achitare';
			$languages['form_payment_title'] 		= 'Introduce informaţia pentru achitare';
			$languages['form_payment_description'] 	= 'Verifică datele de mai jos înainte de a introduce informaţiile pentru achitare.';
			$languages['payment_submit_button']		= 'Expediază plata';
			$languages['tax']						= 'TVA';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'ID Achitare';
			$languages['payment_date']	 	= 'Data achitării';
			$languages['payment_fullname'] 	= 'Nume Prenume';
			$languages['payment_shipping'] 	= 'Adresa de transport';
			$languages['payment_billing']	= 'Adresa de facturare';

			//coupon code
			$languages['coupon_not_exist'] = "Acest cod promoțional nu există.";
			$languages['coupon_max_usage'] = "Acest cod a atins limita maximă de revedincare.";
			$languages['coupon_expired']   = "Acest cod promoțional a expirat.";
			$languages['discount']		   = "Reducere";	

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']				= 'Pagina %s din %s';
		}else if($target_language == 'slovak'){
			//simple name and extended name
			$languages['name_first']			= 'Meno';
			$languages['name_middle']			= 'Stredné';
			$languages['name_last']				= 'Priezvisko';
			$languages['name_title']			= 'Titul';
			$languages['name_suffix']			= 'Titul za';
			
			//address
			$languages['address_street']		= 'Ulica';
			$languages['address_street2']		= 'Adresa 2';
			$languages['address_city']			= 'Mesto';
			$languages['address_state']			= 'Okres';
			$languages['address_zip']			= 'PSČ';
			$languages['address_country']		= 'Štát';

			//captcha
			$languages['captcha_required']				= 'Toto pole je povinné. Prosím opíšte písmená na obrázku.';
			$languages['captcha_mismatch']				= 'Písmenia nesedia s písmenami na obrázku. Skúste to prosím ešte raz.';
			$languages['captcha_text_mismatch'] 		= 'Nesprávna odpoveď. Skúste prosím ešte raz.';
			$languages['captcha_error']					= 'Chyba pri spracovaní požiadavky, skúste to prosím znovu.';
			$languages['captcha_simple_image_title']	= 'Prosím opíšte písmená na obrázku dole.';
			$languages['captcha_simple_text_title']		= 'Ochrana proti spamu. Prosím odpovedzte na jednoduchú otázku:';
			
			//date
			$languages['date_dd']				= 'DD';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'RRRR';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléøù';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Z³oty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'HH';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Pri odosielaní sa vyskytol problém.';
			$languages['error_desc']			=	'Problémy boli <strong>zvýraznené</strong>.';
			
			//form buttons
			$languages['submit_button']			=	'Odoslať';
			$languages['continue_button']		=	'Pokračovať';
			$languages['back_button']			=	'Naspäť';
			
			//form status
			$languages['form_inactive']			=	'Tento formulár momentálne nie je aktívny.';
			$languages['form_limited']			=   'Prepáčte, tento formulár momentálne už neprijíma vstupné údaje.';
			
			//form password
			$languages['form_pass_title']		=	'Tento formulár je chránený heslom.';
			$languages['form_pass_desc']		=	'Prosím vložte heslo.';
			$languages['form_pass_invalid']		=	'Heslo je nesprávne!';
			
			//form review
			$languages['review_title']			=	'Skontrolujte si vložené údaje.';
			$languages['review_message']		=	'Skontrolujte si vložené údaje a stlačte Odoslať.';
			
			//validation message 
			$languages['val_required'] 			=	'Toto pole je povinné.';
			$languages['val_required_file'] 	=	'Toto pole je povinné. Prosím priložte súbor.';
			$languages['val_unique'] 			=	'Toto pole vyžaduje unikátnu hodnotu a táto hodnota už bola použitá.';
			$languages['val_integer'] 			=	'Toto pole musí by celé číslo.';
			$languages['val_float'] 			=	'Toto pole musí by reálne číslo.';
			$languages['val_numeric'] 			=	'Toto pole musí by číslo.';
			$languages['val_email'] 			=	'Nesprávny formát emailu.';
			$languages['val_website'] 			=	'Nesprávny formát web adresy.';
			$languages['val_username'] 			=	'Toto pole môže obsahovať iba a-z 0-9 a podčiarkovník.';
			$languages['val_equal'] 			=	'%s musí pasovať.';
			$languages['val_equal_email'] 			=	'Emailu musí pasovať.';
			$languages['val_date'] 				=	'Nesprávny formát dátumu.';
			$languages['val_date_range'] 		=	'Toto dátumové pole musí byť medzi %s a %s.';
			$languages['val_date_min'] 			=	'Toto dátumové pole musí by väèšie alebo rovné %s.';
			$languages['val_date_max'] 			=	'Toto dátumové pole musí byť menšie alebo rovné %s.';
			$languages['val_date_na'] 			=	'Tento dátum nie je možné vybrať.';
			$languages['val_time'] 				=	'Toto pole nemá správny formát času.';
			$languages['val_phone'] 			=	'Prosím vložte platné telefónne číslo.';
			$languages['val_filetype']			=	'Tento typ súboru nie je povolený.';

			$languages['val_choice_atleast'] 	= 	'Požaduje sa minimálne %s.'; 
			$languages['val_choice_atmost']  	= 	'Povolených je maximálne %s.';
			$languages['val_choice_exactly'] 	= 	'Musí byť %s.';
			$languages['val_choice_between'] 	= 	'Musí byť medzi %s a %s.';
			
			//fields on excel/csv
			$languages['export_num']			=	'Č.';
			$languages['date_created']			=	'Dátum vytovrenia';
			$languages['date_updated']			=	'Datum úpravy';
			$languages['ip_address']			=	'IP adresa';

			//form resume
			$languages['resume_email_subject']		= 'Váš príspevok k {form_name} bol uložený.';
			$languages['resume_email_content'] 		= 'Ďakujeme! Váš príspevok k <b>{form_name}</b> bol uložený.<br /><br />You can resume the form at any time by clicking the link below:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>IMPORTANT:</b><br />Váš príspevok je považovaný za neplatný, kým znovu nenačítate formulár a nestlačíte tlačítko Odoslať.';							

			$languages['resume_success_title']   	= 'Aktuálny stav bol uložený.';
			$languages['resume_success_content'] 	= 'Prosím skopírujte uvedený odkaz a uložte ho na bezpečné miesto:<br/>%s<br/><br/>Vo vypĺňaní môžete kedykoľvek pokračovať, keď kliknete na odkaz.';

			$languages['resume_checkbox_title']		= 'Uložiť aktuálny stav a pokračovať neskôr';
			$languages['resume_email_input_label']	= 'Vložte Váš Email';
			$languages['resume_submit_button_text']	= 'Uložiť formulár a pokračovať neskôr';
			$languages['resume_guideline']			= 'Na Váš email bude odoslaný špeciálny odkaz, ktorý Vám umožní pokračovať vo vypĺňaní formulára.';

			//range validation
			$languages['range_type_digit']			= 'číslic';
			$languages['range_type_chars'] 			= 'znakov';
			$languages['range_type_words'] 			= 'slov';

			$languages['range_min']  				= 'Požaduje sa minimálne %s.'; 
			$languages['range_min_entered']   		= 'Aktuálne vložených: %s.';

			$languages['range_max']					= 'Povolených je maximálne %s.';
			$languages['range_max_entered']   		= 'Aktuálne vložených: %s.';

			$languages['range_min_max'] 			= 'Musí byť medzi %s a %s.';
			$languages['range_min_max_same'] 		= 'Musí byť %s.';
			$languages['range_min_max_entered'] 	= 'Aktuálne vložených: %s.';

			$languages['range_number_min']	 		= 'Musí to byť číslo väčšie alebo rovné %s.';
			$languages['range_number_max']	 		= 'Musí to byť číslo menšie alebo rovné %s.';
			$languages['range_number_min_max'] 		= 'Musí to byť číslo medzi %s a %s';

			//file uploads
			$languages['file_queue_limited'] 		= 'Toto pole je obmedzené na maximálne %s súborov.';
			$languages['file_upload_max']	   		= 'Chyba. Maximálna veľkosť súboru je %s MB.';
			$languages['file_type_limited']  		= 'Chyba. Tento typ súboru nie je povolený.';
			$languages['file_error_upload']  		= 'Chyba! Upload sa nepodaril.';
			$languages['file_attach']		  		= 'Pripojiť súbory';
			$languages['file_select']		  		= 'Select Files';

			//payment total
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Spolu';
			$languages['form_payment_header_title'] = 'Platba';
			$languages['form_payment_title'] 		= 'Zadajte platobné informácie';
			$languages['form_payment_description'] 	= 'Kým zadáte platobné informácie, skontrolujte prosím informácie dole.';
			$languages['payment_submit_button']		= 'Odoslať platbu';
			$languages['tax']						= 'Tax';
			$languages['setup_fee']					= 'Setup Fee';

			//payment details
			$languages['payment_status']	 = 'Stav';
			$languages['payment_id']		 = 'ID platby';
			$languages['payment_date']	 	 = 'Dátum platby';
			$languages['payment_fullname']   = 'Plné meno';
			$languages['payment_shipping']   = 'Dodacia adresa';
			$languages['payment_billing']	 = 'Fakturačná adresa';

			//coupon code
			$languages['coupon_not_exist'] = "This coupon code does not exist.";
			$languages['coupon_max_usage'] = "This coupon has reached the maximum redemption limit.";
			$languages['coupon_expired']   = "This coupon code has been expired.";
			$languages['discount']		   = "Discount";	

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';	
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']		 = 'Strana %s z %s';
		}else if($target_language == 'indonesia'){
			//simple name and extended name
			$languages['name_first']			= 'Depan';
			$languages['name_middle']			= 'Tengah';
			$languages['name_last']				= 'Belakang';
			$languages['name_title']			= 'Titel';
			$languages['name_suffix']			= '';
			
			//address
			$languages['address_street']		= 'Nama Jalan';
			$languages['address_street2']		= 'Nama Jalan';
			$languages['address_city']			= 'Kota';
			$languages['address_state']			= 'Provinsi';
			$languages['address_zip']			= 'Kode Pos';
			$languages['address_country']		= 'Negara';

			//captcha
			$languages['captcha_required']				= 'Field ini dibutuhkan. Mohon ketik huruf-huruf yang ditampilkan di gambar.';
			$languages['captcha_mismatch']				= 'Huruf yang anda masukkan tidak sama dengan gambar. Coba lagi.';
			$languages['captcha_text_mismatch'] 		= 'Jawaban salah. Mohon coba lagi.';
			$languages['captcha_error']					= 'Ada kesalahan dalam proses verifikasi. Mohon coba lagi.';
			$languages['captcha_simple_image_title']	= 'Masukkan huruf-huruf yang ditampilkan di gambar berikut.';
			$languages['captcha_simple_text_title']		= 'Proteksi Spam. Mohon jawab pertanyaan berikut:';
			
			//date
			$languages['date_dd']				= 'DD';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'YYYY';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'HH';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Terdapat kesalahan dengan data anda.';
			$languages['error_desc']			=	'Kesalahan-kesalahan tersebut telah <strong>ditandai</strong> dibawah ini.';
			
			//form buttons
			$languages['submit_button']			=	'Kirim';
			$languages['continue_button']		=	'Lanjutkan';
			$languages['back_button']			=	'Kembali';
			
			//form status
			$languages['form_inactive']			=	'Form ini tidak aktif.';
			$languages['form_limited']			=   'Maaf, form ini tidak lagi menerima entri data.';
			
			//form password
			$languages['form_pass_title']		=	'Form ini diproteksi dengan password.';
			$languages['form_pass_desc']		=	'Masukkan password anda.';
			$languages['form_pass_invalid']		=	'Password Salah!';
			
			//form review
			$languages['review_title']			=	'Tinjau Ulang Data Anda';
			$languages['review_message']		=	'Mohon tinjau ulang data anda dibawah ini. Klik tombol Kirim untuk melanjutkan.';
			
			//validation message 
			$languages['val_required'] 			=	'Field ini dibutuhkan. Mohon masukkan data.';
			$languages['val_required_file'] 	=	'Field ini dibutuhkan. Mohon pilih file untuk diupload.';
			$languages['val_unique'] 			=	'Field ini membutuhkan data yang berbeda untuk tiap entri. Data yang anda masukkan sudah ada.';
			$languages['val_integer'] 			=	'Field ini harus berupa angka.';
			$languages['val_float'] 			=	'Field ini harus berupa angka desimal.';
			$languages['val_numeric'] 			=	'Field ini harus berupa angka.';
			$languages['val_email'] 			=	'Email yang anda masukkan salah.';
			$languages['val_website'] 			=	'Alamat website yang anda masukkan salah.';
			$languages['val_username'] 			=	'This field may only consist of a-z 0-9 and underscores.';
			$languages['val_equal'] 			=	'%s must match.';
			$languages['val_equal_email'] 		=	'Alamat email harus sama.';
			$languages['val_date'] 				=	'Format tanggal yang anda masukkan salah';
			$languages['val_date_range'] 		=	'Tanggal yang anda masukkan harus diantara %s dan %s.';
			$languages['val_date_min'] 			=	'Tanggal yang anda masukkan harus sama atau setelah %s.';
			$languages['val_date_max'] 			=	'Tanggal yang anda masukkan tidak bisa melebihi %s.';
			$languages['val_date_na'] 			=	'Tanggal yang anda pilih tidak tersedia lagi.';
			$languages['val_time'] 				=	'Format waktu yang anda masukkan salah.';
			$languages['val_phone'] 			=	'Mohon masukkan nomor telepon yang benar.';
			$languages['val_filetype']			=	'Tipe file yang anda pilih tidak diperkenankan.';

			$languages['val_choice_atleast'] 	= 	'Minimum %s pilihan dibutuhkan.'; 
			$languages['val_choice_atmost']  	= 	'Maksimum %s pilihan diperkenankan.';
			$languages['val_choice_exactly'] 	= 	'Harus tepat %s pilihan.';
			$languages['val_choice_between'] 	= 	'Harus diantara %s dan %s pilihan.';
			
			//fields on excel/csv
			$languages['export_num']			=	'Nomor';
			$languages['date_created']			=	'Tanggal Dibuat';
			$languages['date_updated']			=	'Tanggal Update';
			$languages['ip_address']			=	'IP Address';

			//form resume
			$languages['resume_email_subject']		= 'Entri data anda ke {form_name} telah disimpan sebagian.';
			$languages['resume_email_content'] 		= 'Terima kasih! Entri data anda ke <b>{form_name}</b> telah disimpan sebagian.<br /><br />Anda dapat melanjutkan lagi di lain waktu dengan menggunakan link dibawah ini:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>PENTING:</b><br />Data anda baru akan tersimpan sepenuhnya setelah anda melanjutkan entri data dan menekan tombol Kirim.';							

			$languages['resume_success_title']   	= 'Entri anda telah disimpan sebagian.';
			$languages['resume_success_content'] 	= 'Mohon salin link dibawah ini dan simpan:<br/>%s<br/><br/>Anda dapat melanjutkan entri data lagi dengan menggunakan link tersebut.';

			$languages['resume_checkbox_title']		= 'Simpan data dan lanjutkan nanti';
			$languages['resume_email_input_label']	= 'Masukkan Email Anda';
			$languages['resume_submit_button_text']	= 'Simpan data dan lanjutkan nanti';
			$languages['resume_guideline']			= 'Link untuk melanjutkan entri anda akan dikirim ke email.';

			//range validation
			$languages['range_type_digit']			= 'digit';
			$languages['range_type_chars'] 			= 'karakter';
			$languages['range_type_words'] 			= 'kata';

			$languages['range_min']  				= 'Minimum %s dibutuhkan.'; 
			$languages['range_min_entered']   		= 'Data Dimasukkan: %s.';

			$languages['range_max']					= 'Maksimum %s diperkenankan.';
			$languages['range_max_entered']   		= 'Data Dimasukkan: %s.';

			$languages['range_min_max'] 			= 'Harus diantara %s dan %s.';
			$languages['range_min_max_same'] 		= 'Harus %s.';
			$languages['range_min_max_entered'] 	= 'Data Dimasukkan: %s.';

			$languages['range_number_min']	 		= 'Harus berupa angka yang sama atau lebih besar dari %s.';
			$languages['range_number_max']	 		= 'Harus berupa angka yang sama atau lebih kecil dari %s.';
			$languages['range_number_min_max'] 		= 'Harus berupa angka antara %s dan %s';

			//file uploads
			$languages['file_queue_limited'] 		= 'Field ini dibatasi maksimum %s file.';
			$languages['file_upload_max']	   		= 'Error. Maksimum %sMB diperbolehkan.';
			$languages['file_type_limited']  		= 'Error. Tipe file ini tidak diperbolehkan.';
			$languages['file_error_upload']  		= 'Error! Tidak dapat upload file.';
			$languages['file_attach']		  		= 'Attach File';
			$languages['file_select']		  		= 'Pilih File';

			//payment page
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Total';
			$languages['form_payment_header_title'] = 'Pembayaran';
			$languages['form_payment_title'] 		= 'Masukkan Informasi Pembayaran';
			$languages['form_payment_description'] 	= 'Mohon tinjau ulang data dibawah ini sebelum melanjutkan.';
			$languages['payment_submit_button']		= 'Kirim Pembayaran';
			$languages['tax']						= 'Pajak';
			$languages['setup_fee']					= 'Biaya Awal';

			//payment details
			$languages['payment_status']	= 'Status';
			$languages['payment_id']		= 'ID Pembayaran';
			$languages['payment_date']	 	= 'Tanggal Pembayaran';
			$languages['payment_fullname'] 	= 'Nama Lengkap';
			$languages['payment_shipping'] 	= 'Alamat Pengiriman';
			$languages['payment_billing']	= 'Alamat Penagihan';	

			//coupon code
			$languages['coupon_not_exist'] = "Kode kupon ini tidak ada.";
			$languages['coupon_max_usage'] = "Kode kupon ini telah melampaui limit.";
			$languages['coupon_expired']   = "Kode kupon ini telah melampaui masa pemakaian.";
			$languages['discount']		   = "Diskon";

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'No more choices left';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']	   = 'Halaman %s dari %s';
		}else if($target_language == 'turkish'){
			//simple name and extended name
			$languages['name_first']			=	'İlk';
			$languages['name_middle']			=	'İkinci Adı';
			$languages['name_last']				=	'Soyadı';
			$languages['name_title']			=	'Ünvan';
			$languages['name_suffix']			=	'Son Ek Adı';
			
			//address
			$languages['address_street']		=	'Sokak adresi';
			$languages['address_street2']		=	'Adres Satırı 2';
			$languages['address_city']			=	'Şehir';
			$languages['address_state']			=	'Eyalet / İl / Bölge';
			$languages['address_zip']			=	'Posta Kodu';
			$languages['address_country']		=	'Ülke';

			//captcha
			$languages['captcha_required']			=	'Bu alan gereklidir. Lütfen resimde gösterilen harfleri girin.';
			$languages['captcha_mismatch']			=	'Resimdeki harfler uyuşmuyor. Lütfen tekrar deneyin.';
			$languages['captcha_text_mismatch'] 	=	'Yanlış cevap. Lütfen tekrar deneyin.';
			$languages['captcha_error']				=	'İşlem yapılırken hata oluştu, lütfen tekrar deneyin..';
			$languages['captcha_simple_image_title'] = 	'Aşağıdaki resimde gördüğünüz harfleri yazınız.';
			$languages['captcha_simple_text_title']	= 	'Spam Koruması. Lütfen bu basit soruyu cevaplayın:';
			
			//date
			$languages['date_dd']				=	'GG';
			$languages['date_mm']				=	'AA';
			$languages['date_yyyy']				=	'YYYY';
			
			//price
			$languages['price_dollar_main']		=	'Dolar';
			$languages['price_dollar_sub']		=	'Sent';
			$languages['price_euro_main']		=	'Euro';
			$languages['price_euro_sub']		=	'Sent';
			$languages['price_pound_main']		=	'Pound';
			$languages['price_pound_sub']		=	'Peni';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupi';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Sent';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Frank';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Kron';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pezo';
			$languages['price_pesos_sub']		=	'Sent';
			$languages['price_ringgit_main']	=	'Ringiti';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Real';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Zloti';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyal';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'SS';
			$languages['time_mm']				=	'DD';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Gönderiminizle ilgili bir sorun oluştu..';
			$languages['error_desc']			=	'Hatalar aşağıda <strong> vurgulanmıştır </ strong>.';
			
			//form buttons
			$languages['submit_button']			=	'Gönder';
			$languages['continue_button']		=	'Devam et';
			$languages['back_button']			=	'Önceki';
			
			//form status
			$languages['form_inactive']		=	'Bu form şu anda etkin değil.';
			$languages['form_limited']		=   'Maalesef, bu form artık hiçbir girişi kabul etmiyor.';
			
			//form password
			$languages['form_pass_title']		=	'Bu form şifreyle korunmaktadır.';
			$languages['form_pass_desc']		=	'Lütfen şifrenizi giriniz.';
			$languages['form_pass_invalid']		=	'Geçersiz şifre!';
			
			//form review
			$languages['review_title']			=	'Girişinizi İnceleyiniz';
			$languages['review_message']		=	'Lütfen girişinizi aşağıda inceleyin. Bitirmek için Gönder düğmesini tıklayın.';
			
			//validation message 
			$languages['val_required'] 			=	'Bu alan gereklidir. Bir değer girin.';
			$languages['val_required_file'] 	=	'Bu alan gereklidir. Lütfen bir dosya yükleyin.';
			$languages['val_unique'] 			=	'Bu alan benzersiz bir girdi gerektirir ve bu değer zaten kullanılmıştır.';
			$languages['val_integer'] 			=	'Bu alan bir tamsayı olmalıdır.';
			$languages['val_float'] 			=	'Bu alan bir kayan nokta sayı olmalıdır.';
			$languages['val_numeric'] 			=	'Bu alan bir sayı olmalıdır.';
			$languages['val_email'] 			=	'Bu alan doğru e-posta biçiminde değil.';
			$languages['val_website'] 			=	'Bu alan doğru web sitesi adresi biçiminde değil.';
			$languages['val_username'] 			=	'Bu alan yalnızca a-z, 0-9 ve altçizgilerden oluşabilir.';
			$languages['val_equal'] 			=	'%s eşleşmek zorundadır.';
			$languages['val_date'] 				=	'Bu alan doğru tarih biçiminde değil.';
			$languages['val_date_range'] 		=	'Bu tarih alanı %s ve %s arasında olmalıdır.';
			$languages['val_date_min'] 			=	'Bu tarih alanı %s den büyük veya ona eşit olmalıdır.';
			$languages['val_date_max'] 			=	'Bu tarih alanı %s den küçük veya ona eşit olmalıdır.';
			$languages['val_date_na'] 			=	'Bu tarih seçilemez.';
			$languages['val_time'] 				=	'Bu alan doğru zaman formatında değil.';
			$languages['val_phone'] 			=	'Geçerli bir telefon numarası giriniz.';
			$languages['val_filetype']			=	'Yüklemeye çalıştığınız dosya türüne izin verilmiyor.';

			$languages['val_choice_atleast'] 	= 	'Minimum %s seçenek gerekli.'; 
			$languages['val_choice_atmost']  	= 	'İzin verilen maksimum seçenek sayısı %s.';
			$languages['val_choice_exactly'] 	= 	'Tam olarak %s seçenek olmalıdır.';
			$languages['val_choice_between'] 	= 	'%s ve %s seçenekleri arasında olmalıdır.';
			
			//fields on excel/csv
			$languages['export_num']			=	'No.';
			$languages['date_created']			=	'Oluşturma Tarihi';
			$languages['date_updated']			=	'Güncelleme Tarihi';
			$languages['ip_address']			=	'IP Adresi';

			//form resume
			$languages['resume_email_subject']		= '{Form_name} adresine gönderiminiz kaydedildi.';
			$languages['resume_email_content'] 		= 'Teşekkür ederiz! <b> {form_name} </ b> adresine gönderiminiz kaydedildi. <br /> <br /> Aşağıdaki bağlantıyı tıklayarak formu dilediğiniz zaman yeniden başlatabilirsiniz: <br /> <a href = "{resume_url}"> {resume_url} </a> <br /> <br /> <br /> <b> ÖNEMLİ: </ b> <br /> Forma devam etmeden ve gönderene kadar başvurunuz tamamlanmamış sayılır. Gönder düğmesine basın.';							

			$languages['resume_success_title']   		= 'İlerlemeniz kaydedildi.';
			$languages['resume_success_content'] 		= 'Lütfen aşağıdaki bağlantıyı kopyalayıp güvenli bir yere kaydedin: <br/>%s <br/><br/> Formu istediğiniz zaman yukarıdaki bağlantıya giderek kaldığınız yerden devam ettirebilirsiniz..';

			$languages['resume_checkbox_title']		= 'İlerlemenizi kaydedin ve daha sonra devam ediniz.';
			$languages['resume_email_input_label']	= 'E-Posta adresinizi giriniz';
			$languages['resume_submit_button_text']	= 'Formu kaydet ve daha sonra devam et';
			$languages['resume_guideline']			= 'Formu devam ettirmek için özel bir bağlantı e-posta adresinize gönderilecek.';

			//range validation
			$languages['range_type_digit']			= 'basamaklar';
			$languages['range_type_chars'] 			= 'karakterler';
			$languages['range_type_words'] 			= 'kelimeler';

			$languages['range_min']  				= 'Minimum %s gereklidir.'; 
			$languages['range_min_entered']   		= 'Şu Anda Girilen: %s.';

			$languages['range_max']					= 'Maksimum %s e izin verilir.';
			$languages['range_max_entered']   		= 'Şu Anda Girilen: %s.';

			$languages['range_min_max'] 				= '%s ve %s arasında olmalıdır.';
			$languages['range_min_max_same'] 			= '%s olmalıdır.';
			$languages['range_min_max_entered'] 		= 'Şu Anda Girilen: %s.';

			$languages['range_number_min']	 		= '%s den büyük veya ona eşit bir sayı olmalıdır.';
			$languages['range_number_max']	 		= '%s den küçük veya ona eşit bir numara olmalıdır.';
			$languages['range_number_min_max'] 		= '%s ve %s arasında bir sayı olmalı';

			//file uploads
			$languages['file_queue_limited'] 			= 'Bu alan, en fazla %s dosya ile sınırlandırılmıştır.';
			$languages['file_upload_max']	   			= 'Hata! Maksimum %s MB izin verildi.';
			$languages['file_type_limited']  			= 'Hata! Bu dosya türüne izin verilmiyor.';
			$languages['file_error_upload']  			= 'Hata! Yüklenemiyor';
			$languages['file_attach']		  			= 'Dosyaları ekle';
			$languages['file_select']		  			= 'Dosyaları Seç';

			//payment page
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Toplam';
			$languages['form_payment_header_title'] = 'Ödeme';
			$languages['form_payment_title'] 		= 'Ödeme Bilgisini Girin';
			$languages['form_payment_description'] 	= 'Lütfen ödeme bilgilerini girmeden önce aşağıdaki ayrıntıları gözden geçirin.';
			$languages['payment_submit_button']		= 'Ödemeyi Onaylayın';
			$languages['tax']						= 'Vergi';
			$languages['setup_fee']					= 'Kurulum Ücreti';

			//payment details
			$languages['payment_status']	= 'Durum';
			$languages['payment_id']		= 'Ödeme ID';
			$languages['payment_date']	 	= 'Ödeme Tarihi';
			$languages['payment_fullname'] 	= 'Ad Soyad';
			$languages['payment_shipping'] 	= 'Teslimat Adresi';
			$languages['payment_billing']	= 'Fatura Adresi';	

			//multipage
			$languages['page_title']		= '%s den %s sayfa';

			//coupon code
			$languages['coupon_not_exist'] = "Bu kupon kodu mevcut değil.";
			$languages['coupon_max_usage'] = "Bu kupon maksimum geri ödeme limitine ulaştı.";
			$languages['coupon_expired']   = "Bu kupon kodunun tarihi geçti.";
			$languages['discount']		   = 'İndirim';

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty'] 	   = 'Başka seçenek kalmadı';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title'] = '%s den %s sayfa';
		}else if($target_language == 'korean'){
			//simple name and extended name
			$languages['name_first']			= '이름(First)';
			$languages['name_middle']			= '가운데 이름(Middle)';
			$languages['name_last']				= '성(Last)';
			$languages['name_title']			= '호칭(Title)';
			$languages['name_suffix']			= '이름접미사(Suffix)';
			
			//address
			$languages['address_street']		= '도로명주소';
			$languages['address_street2']		= '상세주소';
			$languages['address_city']			= '구/시/군(City)';
			$languages['address_state']			= '특별시/광역시/도(State)';
			$languages['address_zip']			= '우편번호(Postal / Zip Code)';
			$languages['address_country']		= '국가(Country)';

			//captcha
			$languages['captcha_required']				= '이 필드는 필수 항목입니다. 이미지에 표시된 문자를 입력하세요.';
			$languages['captcha_mismatch']				= '이미지의 문자가 일치하지 않습니다. 다시 시도해 주세요.';
			$languages['captcha_text_mismatch'] 		= '정답이 일치하지 않습니다. 다시 시도해 주세요.';
			$languages['captcha_error']					= '처리 중 오류가 발생했습니다. 다시 시도해 주세요.';
			$languages['captcha_simple_image_title']	= '아래 이미지에 표시된 문자를 입력하세요.';
			$languages['captcha_simple_text_title']		= '스팸 방지 중입니다. 질문에 대답해 주세요.';
			
			//date
			$languages['date_dd']				= 'DD';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'YYYY';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Koruna';
			$languages['price_koruna_sub']		=	'Haléřů';
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'HH';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'입력해주신 사항에 문제가 있습니다.';
			$languages['error_desc']			=	'오류는 아래에서 <strong>강조</strong> 표시되어 있습니다.';
			
			//form buttons
			$languages['submit_button']			=	'제출';
			$languages['continue_button']		=	'다음(계속)';
			$languages['back_button']			=	'이전';
			
			//form status
			$languages['form_inactive']			=	'이 양식은 현재 비활성되어 있습니다.';
			$languages['form_limited']			=   '죄송하지만, 이 양식은 더 이상 입력을 받지 않습니다.';
			
			//form password
			$languages['form_pass_title']		=	'이 양식은 비밀 번호로 보호되어 있습니다.';
			$languages['form_pass_desc']		=	'비밀번호를 입력해 주세요.';
			$languages['form_pass_invalid']		=	'비밀번호가 올바르지 않습니다!';
			
			//form review
			$languages['review_title']			=	'입력사항 검토';
			$languages['review_message']		=	"아래 항목을 검토해 주세요. '제출' 버튼을 클릭하면 접수가 완료됩니다.";
			
			//validation message 
			$languages['val_required'] 			=	'이 필드는 필수 항목입니다. 값을 입력하세요.';
			$languages['val_required_file'] 	=	'이 필드는 필수 항목입니다. 파일을 업로드해 주세요.';
			$languages['val_unique'] 			=	'이 필드는 제출되지 않은 고유한 값이 필요하며, 입력해주신 값은 이미 제출되었습니다.';
			$languages['val_integer'] 			=	'이 필드는 정수(integer)여야 합니다.';
			$languages['val_float'] 			=	'이 필드는 부동소수점(float)이어야 합니다.';
			$languages['val_numeric'] 			=	'이 필드는 숫자(number)여야 합니다.';
			$languages['val_email'] 			=	'이 필드는 올바른 이메일 형식이 아닙니다.';
			$languages['val_website'] 			=	'이 필드는 올바른 웹사이트 주소 형식이 아닙니다.';
			$languages['val_username'] 			=	"이 필드는 'a-z', '0-9' 와 '_'(밑줄)로만 구성할 수 있습니다.";
			$languages['val_equal'] 			=	'%s 이(가) 일치해야만 합니다.';
			$languages['val_equal_email'] 		=	'이메일 주소가 일치해야 합니다.';
			$languages['val_date'] 				=	'이 필드는 올바른 날짜 형식이 아닙니다.';
			$languages['val_date_range'] 		=	'이 날짜 필드는 %s 에서 %s 사이여야 합니다.';
			$languages['val_date_min'] 			=	'이 날짜 필드는 %s 보다 크거나 같아야 합니다.';
			$languages['val_date_max'] 			=	'이 날짜 필드는 %s 이하이어야 합니다.';
			$languages['val_date_na'] 			=	'이 날짜는 선택할 수 없습니다.';
			$languages['val_time'] 				=	'이 필드는 올바른 시간 형식이 아닙니다.';
			$languages['val_phone'] 			=	'올바른 전화번호를 입력하십시오.';
			$languages['val_filetype']			=	'업로드 하려는 파일 형식은 허용되지 않습니다.';

			$languages['val_choice_atleast'] 	= 	'최소 %s 가지 선택이 필요합니다.'; 
			$languages['val_choice_atmost']  	= 	'최대 %s 가지 선택이 가능됩니다.';
			$languages['val_choice_exactly'] 	= 	'정확히 %s 가지 선택이어야 합니다.';
			$languages['val_choice_between'] 	= 	'%s 가지에서 %s 가지 사이에서 선택해야 합니다.';
			
			//fields on excel/csv
			$languages['export_num']			=	'No.';
			$languages['date_created']			=	'Date Created';
			$languages['date_updated']			=	'Date Updated';
			$languages['ip_address']			=	'IP Address';

			//form resume
			$languages['resume_email_subject']		= '{form_name}에 대한 제출사항이 저장되었습니다.';
			$languages['resume_email_content'] 		= '감사합니다! <b>{form_name}</b>에 대한 제출사항이 저장되었습니다.<br /><br />아래 링크를 클릭하면 언제든지 양식을 다시 재개할 수 있습니다.:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>중요:</b><br />양식을 다시 시작하고 \'제출\' 버튼을 누를 때까지 양식의 제출은 완료되지 않습니다.';							

			$languages['resume_success_title']   	= '진행사항이 저장되었습니다.';
			$languages['resume_success_content'] 	= '아래 링크를 복사하여 안전한 곳에 저장하세요.:<br/>%s<br/><br/>위의 링크로 이동하여 언제든지 양식을 재개할 수 있습니다.';

			$languages['resume_checkbox_title']		= '내 진행사항을 저장하고, 나중에 다시 시작하기';
			$languages['resume_email_input_label']	= '이메일 주소를 입력하세요.';
			$languages['resume_submit_button_text']	= '양식을 저장하고, 나중에 다시 시작';
			$languages['resume_guideline']			= '양식을 재개하기 위한 특별한 링크가 사용자의 이메일 주소로 전송됩니다.';

			//range validation
			$languages['range_type_digit']			= '숫자(digits)';
			$languages['range_type_chars'] 			= '글자(characters)';
			$languages['range_type_words'] 			= '단어(words)';

			$languages['range_min']  				= '최소 %s 를 입력해야만 합니다.'; 
			$languages['range_min_entered']   		= '현재 입력됨: %s.';

			$languages['range_max']					= '최대 %s 를 입력할 수 있습니다.';
			$languages['range_max_entered']   		= '현재 입력됨: %%s.';

			$languages['range_min_max'] 			= '%s 에서 %s 사이여야 합니다.';
			$languages['range_min_max_same'] 		= '%s 이어야 합니다.';
			$languages['range_min_max_entered'] 	= '현재 입력됨: %s.';

			$languages['range_number_min']	 		= '%s 보다 크거나 같은 숫자여야 합니다.';
			$languages['range_number_max']	 		= '%s 이하의 숫자여야 합니다.';
			$languages['range_number_min_max'] 		= '%s 에서 %s 사이의 숫자여야 합니다.';

			//file uploads
			$languages['file_queue_limited'] 		= '이 필드는 최대 %s 파일로 제한됩니다.';
			$languages['file_upload_max']	   		= '오류. 최대 %sMB 용량이 허용됩니다.';
			$languages['file_type_limited']  		= '오류. 이 파일 형식은 허용되지 않습니다.';
			$languages['file_error_upload']  		= '오류! 파일을 업로드할 수 없습니다.';
			$languages['file_attach']		  		= '파일 첨부';
			$languages['file_select']		  		= '파일 선택';

			//payment page
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= '합계';
			$languages['form_payment_header_title'] = '결제';
			$languages['form_payment_title'] 		= '결제 정보 입력';
			$languages['form_payment_description'] 	= '결제 정보를 입력하기 전에 아래 내용을 검토하세요.';
			$languages['payment_submit_button']		= '결제 정보 제출';
			$languages['tax']						= '세금(Tax)';
			$languages['setup_fee']					= '설치비용(setup fee)';

			//payment details
			$languages['payment_status']	= '상태';
			$languages['payment_id']		= '구매 ID';
			$languages['payment_date']	 	= '구매일';
			$languages['payment_fullname'] 	= '이름';
			$languages['payment_shipping'] 	= '배송 주소';
			$languages['payment_billing']	= '청구지 주소';	

			//coupon code
			$languages['coupon_not_exist'] = "존재하지 않는 쿠폰 코드입니다.";
			$languages['coupon_max_usage'] = "이 쿠폰은 최대 사용 한도에 도달했습니다.";
			$languages['coupon_expired']   = "이 쿠폰 코드는 사용 기간이 만료되었습니다.";
			$languages['discount']		   = "할인(DC)";

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = '선택항목이 남아있지 않습니다.';
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']	   = '페이지 %s 중 %s';
		}else if($target_language == 'czech'){
			//simple name and extended name
			$languages['name_first']			= 'Jméno';
			$languages['name_middle']			= 'Prostřední jméno';			
			$languages['name_last']				= 'Příjmení';	
			$languages['name_title']			= 'Titul';	
			$languages['name_suffix']			= 'Titul za jménem';	
			
			//address
			$languages['address_street']		= 'Ulice a číslo';	
			$languages['address_street2']		= 'Adresa 2. řádek';	
			$languages['address_city']			= 'Město';		
			$languages['address_state']			= 'Kraj';	
			$languages['address_zip']			= 'PSČ';	
			$languages['address_country']		= 'Země';

			//captcha
			$languages['captcha_required']				= 'Toto pole je povinné. Zadejte prosím znaky uvedené na obrázku'; 
			$languages['captcha_mismatch']				= 'Znaky neodpovídají obrázku. Zkuste to prosím znovu';	
			$languages['captcha_text_mismatch'] 		= 'Nesprávná odpověď. Zkuste to prosím znovu';	
			$languages['captcha_error']					= 'Chyba zpracování. Zkuste to prosím znovu'; 
			$languages['captcha_simple_image_title']	= 'Opište znaky, které vidíte na obrázku dole';	
			$languages['captcha_simple_text_title']		= 'Ochrana proti spamu. Prosím, odpovězte na jednoduchou otázku:';	
			
			//date
			$languages['date_dd']				= 'DD';
			$languages['date_mm']				= 'MM';
			$languages['date_yyyy']				= 'YYYY';
			
			//price
			$languages['price_dollar_main']		=	'Dollars';
			$languages['price_dollar_sub']		=	'Cents';
			$languages['price_euro_main']		=	'Euros';
			$languages['price_euro_sub']		=	'Cents';
			$languages['price_pound_main']		=	'Pounds';
			$languages['price_pound_sub']		=	'Pence';
			$languages['price_yen']				=	'Yen';
			$languages['price_baht_main']		=	'Baht';
			$languages['price_baht_sub']		=	'Satang';
			$languages['price_rupees_main']		=	'Rupees';
			$languages['price_rupees_sub']		=	'Paise';
			$languages['price_rand_main']		=	'Rand';
			$languages['price_rand_sub']		=	'Cents';
			$languages['price_forint_main']		=	'Forint';
			$languages['price_forint_sub']		=	'Filler';
			$languages['price_franc_main']		=	'Francs';
			$languages['price_franc_sub']		=	'Rappen';
			$languages['price_koruna_main']		=	'Korun';	
			$languages['price_koruna_sub']		=	'Haléřů';	
			$languages['price_krona_main']		=	'Kroner';
			$languages['price_krona_sub']		=	'Øre';
			$languages['price_leu_main']		=	'Lei';
			$languages['price_leu_sub']			=	'Bani';
			$languages['price_pesos_main']		=	'Pesos';
			$languages['price_pesos_sub']		=	'Cents';
			$languages['price_ringgit_main']	=	'Ringgit';
			$languages['price_ringgit_sub']		=	'Sen';
			$languages['price_reais_main']		=	'Reais';
			$languages['price_reais_sub']		=	'Centavos';
			$languages['price_zloty_main']		=	'Złoty';
			$languages['price_zloty_sub']		=	'Grosz';
			$languages['price_riyals_main']		=	'Riyals';
			$languages['price_riyals_sub']		=	'Halalah';
			
			//time
			$languages['time_hh']				=	'HH';
			$languages['time_mm']				=	'MM';
			$languages['time_ss']				=	'SS';
			
			//error message
			$languages['error_title']			=	'Nastal problém s vložením vašeho příspěvku';
			$languages['error_desc']			=	'Chyby jsou <strong>zvýrazněny</strong> níže.';	
			
			//form buttons
			$languages['submit_button']			=	'Vložit'; 
			$languages['continue_button']		=	'Dále'; 
			$languages['back_button']			=	'Předchozí';	
			
			//form status
			$languages['form_inactive']			=	'Tento formulář je nyní neaktivní';	
			$languages['form_limited']			=   'Litujeme, ale tento formulář už nemůže přijmout žádná data'; 
			
			//form password
			$languages['form_pass_title']		=	'Tento formulář je chráněn heslem';	
			$languages['form_pass_desc']		=	'Prosím, zadejte heslo';	
			$languages['form_pass_invalid']		=	'Nesprávné heslo!';	
			
			//form review
			$languages['review_title']			=	'Zkontrolujte své zadání'; 
			$languages['review_message']		=	'Prosím, zkontrolujte své zadání. Zakončete tlačítkem Vložit'; 
			
			//validation message 
			$languages['val_required'] 			=	'Toto pole je povinné. Prosím, zadejte hodnotu';	
			$languages['val_required_file'] 	=	'Toto pole je povinné. Prosím, nahrajte soubor';	
			$languages['val_unique'] 			=	'Tato hodnota není unikátní, už byla použita'; 
			$languages['val_integer'] 			=	'Požaduje se celé číslo';	
			$languages['val_float'] 			=	'Požaduje se desetinné číslo';		
			$languages['val_numeric'] 			=	'Požaduje se číslo';		
			$languages['val_email'] 			=	'Email není zadán ve správném formátu';	
			$languages['val_website'] 			=	'Webová adresa není zadána ve správném formátu'; 
			$languages['val_username'] 			=	'Povolené jsou jen znaky a-z, 0-9 a podtržítko';	
			$languages['val_equal'] 			=	'%s musí souhlasit'; 
			$languages['val_equal_email'] 		=	'Emailová adresa musí souhlasit'; 
			$languages['val_date'] 				=	'Datum nemá správný formát'; 
			$languages['val_date_range'] 		=	'Datum musí být mezi %s a %s'; 
			$languages['val_date_min'] 			=	'Datum musí být větší nebo rovno %s'; 
			$languages['val_date_max'] 			=	'Datum musí být menší nebo rovno %s';	
			$languages['val_date_na'] 			=	'Toto datum nelze zvolit';	
			$languages['val_time'] 				=	'Čas nemá správný formát';	
			$languages['val_phone'] 			=	'Zadejte prosím platné telefonní číslo';	
			$languages['val_filetype']			=	'Snažíte se nahrát soubor nedovoleného typu';	

			$languages['val_choice_atleast'] 	= 	'Požaduje se nejméně %s možností'; 
			$languages['val_choice_atmost']  	= 	'Požaduje se maximálně %s možností';	
			$languages['val_choice_exactly'] 	= 	'Musí být přesně %s možností';	
			$languages['val_choice_between'] 	= 	'Počet možností musí být mezi %s a %s';	
			
			//fields on excel/csv
			$languages['export_num']			=	'Ne';	
			$languages['date_created']			=	'Datum vytvoření';	
			$languages['date_updated']			=	'Datum nahrání';	
			$languages['ip_address']			=	'IP adresa';	

			//form resume
			$languages['resume_email_subject']		= 'Vaše údaje do formuláře {form_name} byly uloženy';	
			$languages['resume_email_content'] 		= 'Děkujeme. Vaše údaje do formuláře <b>{form_name}</b> byly uloženy.<br /><br />Formulář můžete kdykoliv znovu vyvolat kliknutím na následující odkaz:<br /><a href="{resume_url}">{resume_url}</a><br /><br /><br /><br /><b>DŮLEŽITÉ:</b><br />Váš formulář bude pokládán za neúplně vyplněný, dokud jej neobnovíte a nestisknete tlačítko Uložit.';						
										
			$languages['resume_success_title']   	= 'Váš příspěvek byl uložen';	
			$languages['resume_success_content'] 	= 'Prosím zkopírujte si níže uvedený odkaz a uložte ho na bezpečném místě:<br/>%s<br/><br/>Formulář můžete kdykoliv znovu vyvolat pomocí výše uvedeného odkazu.';
									
			$languages['resume_checkbox_title']		= 'Ulož můj příspěvek, budu pokračovat později '; 
			$languages['resume_email_input_label']	= 'Zadejte svou emailovou adresu'; 
			$languages['resume_submit_button_text']	= 'Ulož můj formulář, budu pokračovat později';	
			$languages['resume_guideline']			= 'Zvláštní odkaz, pomocí kterého můžete vyvolat svůj formulář, bude odeslán na vaši emailovou adresu'; 

			//range validation
			$languages['range_type_digit']			= 'číslic'; 
			$languages['range_type_chars'] 			= 'znaků'; 
			$languages['range_type_words'] 			= 'slov'; 

			$languages['range_min']  				= 'Je požadováno minimum %s.'; 
			$languages['range_min_entered']   		= 'Zadáno je %s.'; 

			$languages['range_max']					= 'Je dovoleno maximálně %s.'; 
			$languages['range_max_entered']   		= 'Je zadáno %s.'; 

			$languages['range_min_max'] 			= 'Musí být mezi %s a %s.'; 
			$languages['range_min_max_same'] 		= 'Musí být %s.'; 
			$languages['range_min_max_entered'] 	= 'Je zadáno %s.'; 

			$languages['range_number_min']	 		= 'Požaduje se číslo větší nebo rovno %s.'; 
			$languages['range_number_max']	 		= 'Požaduje se číslo menší nebo rovno %s.'; 
			$languages['range_number_min_max'] 		= 'Požaduje se číslo mezi %s a %s.'; 

			//file uploads
			$languages['file_queue_limited'] 		= 'Toto pole je limitováno na maximálně %s souborů.';	
			$languages['file_upload_max']	   		= 'Chyba! Je dovoleno maximálně %s MB.';	
			$languages['file_type_limited']  		= 'Chyba! Tento typ souborů není dovolen.'; 
			$languages['file_error_upload']  		= 'Chyba! Nahrání se nepodařilo';	
			$languages['file_attach']		  		= 'Přidat soubory';	
			$languages['file_select']		  		= 'Vybrat soubory';	

			//payment page
			$languages['payment_card_title']		= 'Credit/Debit Card';
			$languages['payment_total'] 			= 'Celkem'; 
			$languages['form_payment_header_title'] = 'Platba';	
			$languages['form_payment_title'] 		= 'Zadejte informace k platbě'; 
			$languages['form_payment_description'] 	= 'Prosím, zkontrolujte níže uvedené údaje, než zadáte informace k platbě'; 
			$languages['payment_submit_button']		= 'Odeslat platbu';	
			$languages['tax']						= 'Daň';	
			$languages['setup_fee']					= 'Poplatek';	

			//payment details
			$languages['payment_status']	= 'Stav'; 
			$languages['payment_id']		= 'Identifikace platby'; 
			$languages['payment_date']	 	= 'Datum platby';	
			$languages['payment_fullname'] 	= 'Celé jméno';	
			$languages['payment_shipping'] 	= 'Doručovací adresa';	
			$languages['payment_billing']	= 'Adresa pro fakturaci';	

			//coupon code
			$languages['coupon_not_exist'] = "Takový kód kupónu neexistuje."; 
			$languages['coupon_max_usage'] = "Kupón už dosáhl maximálního počtu uplatnění."; 
			$languages['coupon_expired']   = "Kód kupónu vypršel."; 
			$languages['discount']		   = "Slave"; 

			//editing completed entry
			$languages['entry_edit_max_revision'] = "Editing unavailable. This entry has reached the maximum number of revisions.";
			$languages['entry_edit_link_expired'] = "Editing unavailable. The link you followed has expired.";
			$languages['entry_edit_button_text']  = "Edit Response";
			$languages['entry_edit_user_info_msg']  = "You're editing your response.";
			$languages['entry_edit_user_info_btn']  = "Open Blank Form";
			
			//signature field
			$languages['signature_draw']  = 'Draw';
			$languages['signature_or'] 	  = 'or';
			$languages['signature_type']  = 'Type';
			$languages['signature_clear'] = 'Clear';

			//others
			$languages['choice_empty']	   = 'Nejsou žádné další volby'; 
			$languages['choice_max_entry'] = 'The choice you selected is no longer available. Please choose another.';

			//multipage
			$languages['page_title']	   = 'Stránka %s z %s'; 
		}

		$mf_lang = $languages;
	}
?>