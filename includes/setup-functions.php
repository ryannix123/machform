<?php
/********************************************************************************
 machform
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	
	function create_ap_forms_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."forms` (
										  `form_id` int(11) NOT NULL AUTO_INCREMENT,
										  `form_name` text,
										  `form_description` text,
										  `form_name_hide` tinyint(1) NOT NULL DEFAULT '0',
										  `form_tags` varchar(255) DEFAULT NULL,
										  `form_email` text,
										  `form_redirect` text,
										  `form_redirect_enable` int(1) NOT NULL DEFAULT '0',
										  `form_success_message` text,
										  `form_disabled_message` text,
										  `form_password` varchar(100) DEFAULT NULL,
										  `form_unique_ip` int(1) NOT NULL DEFAULT '0',
										  `form_unique_ip_maxcount` int(11) NOT NULL DEFAULT '5',
										  `form_unique_ip_period` char(1) NOT NULL DEFAULT 'd' COMMENT 'h,d,w,m,y,l (hour/day/week/month/year/lifetime)',
										  `form_frame_height` int(11) DEFAULT NULL,
										  `form_has_css` int(11) NOT NULL DEFAULT '0',
										  `form_captcha` int(11) NOT NULL DEFAULT '0',
										  `form_captcha_type` char(1) NOT NULL DEFAULT 'n',
										  `form_active` int(11) NOT NULL DEFAULT '1',
										  `form_theme_id` int(11) NOT NULL DEFAULT '0',
										  `form_review` int(11) NOT NULL DEFAULT '0',
										  `form_resume_enable` int(1) NOT NULL DEFAULT '0',
										  `form_resume_subject` text,
										  `form_resume_content` mediumtext,
										  `form_resume_from_name` text,
										  `form_resume_from_email_address` varchar(255) DEFAULT '',
										  `form_custom_script_enable` int(1) NOT NULL DEFAULT '0',
										  `form_custom_script_url` text,
										  `form_limit_enable` int(1) NOT NULL DEFAULT '0',
										  `form_limit` int(11) NOT NULL DEFAULT '0',
										  `form_label_alignment` varchar(11) NOT NULL DEFAULT 'top_label',
										  `form_language` varchar(50) DEFAULT NULL,
										  `form_page_total` int(11) NOT NULL DEFAULT '1',
										  `form_lastpage_title` varchar(255) DEFAULT NULL,
										  `form_submit_primary_text` varchar(255) NOT NULL DEFAULT 'Submit',
										  `form_submit_secondary_text` varchar(255) NOT NULL DEFAULT 'Previous',
										  `form_submit_primary_img` varchar(255) DEFAULT NULL,
										  `form_submit_secondary_img` varchar(255) DEFAULT NULL,
										  `form_submit_use_image` int(1) NOT NULL DEFAULT '0',
										  `form_review_primary_text` varchar(255) NOT NULL DEFAULT 'Submit',
										  `form_review_secondary_text` varchar(255) NOT NULL DEFAULT 'Previous',
										  `form_review_primary_img` varchar(255) DEFAULT NULL,
										  `form_review_secondary_img` varchar(255) DEFAULT NULL,
										  `form_review_use_image` int(11) NOT NULL DEFAULT '0',
										  `form_review_title` text,
										  `form_review_description` text,
										  `form_pagination_type` varchar(11) NOT NULL DEFAULT 'steps',
										  `form_schedule_enable` int(1) NOT NULL DEFAULT '0',
										  `form_schedule_start_date` date DEFAULT NULL,
										  `form_schedule_end_date` date DEFAULT NULL,
										  `form_schedule_start_hour` time DEFAULT NULL,
										  `form_schedule_end_hour` time DEFAULT NULL,
										  `form_approval_enable` tinyint(1) NOT NULL DEFAULT '0',
										  `form_approval_email_subject` text,
										  `form_approval_email_content` mediumtext,
										  `form_created_date` date DEFAULT NULL,
										  `form_created_by` int(11) DEFAULT NULL,
										  `esl_enable` tinyint(1) NOT NULL DEFAULT '0',
										  `esl_from_name` text,
										  `esl_from_email_address` varchar(255) DEFAULT NULL,
										  `esl_bcc_email_address` text,
										  `esl_replyto_email_address` varchar(255) DEFAULT NULL,
										  `esl_subject` text,
										  `esl_content` mediumtext,
										  `esl_plain_text` int(11) NOT NULL DEFAULT '0',
										  `esl_pdf_enable` tinyint(1) NOT NULL DEFAULT '0',
										  `esl_pdf_content` mediumtext,
										  `esr_enable` tinyint(1) NOT NULL DEFAULT '0',
										  `esr_email_address` text,
										  `esr_from_name` text,
										  `esr_from_email_address` varchar(255) DEFAULT NULL,
										  `esr_bcc_email_address` text,
										  `esr_replyto_email_address` varchar(255) DEFAULT NULL,
										  `esr_subject` text,
										  `esr_content` mediumtext,
										  `esr_plain_text` int(11) NOT NULL DEFAULT '0',
										  `esr_pdf_enable` int(1) NOT NULL DEFAULT '0',
										  `esr_pdf_content` mediumtext,
										  `payment_enable_merchant` int(1) NOT NULL DEFAULT '0',
										  `payment_merchant_type` varchar(25) NOT NULL DEFAULT 'paypal_standard',
										  `payment_paypal_email` varchar(255) DEFAULT NULL,
										  `payment_paypal_language` varchar(5) NOT NULL DEFAULT 'US',
										  `payment_currency` varchar(5) NOT NULL DEFAULT 'USD',
										  `payment_show_total` int(1) NOT NULL DEFAULT '0',
										  `payment_total_location` varchar(11) NOT NULL DEFAULT 'top',
										  `payment_enable_recurring` int(1) NOT NULL DEFAULT '0',
										  `payment_recurring_cycle` int(11) NOT NULL DEFAULT '1',
										  `payment_recurring_unit` varchar(5) NOT NULL DEFAULT 'month',
										  `payment_enable_trial` int(1) NOT NULL DEFAULT '0',
										  `payment_trial_period` int(11) NOT NULL DEFAULT '1',
										  `payment_trial_unit` varchar(5) NOT NULL DEFAULT 'month',
										  `payment_trial_amount` decimal(62,2) NOT NULL DEFAULT '0.00',
										  `payment_enable_setupfee` int(1) NOT NULL DEFAULT '0',
										  `payment_setupfee_amount` decimal(62,2) NOT NULL DEFAULT '0.00',
										  `payment_price_type` varchar(11) NOT NULL DEFAULT 'fixed',
										  `payment_price_amount` decimal(62,2) NOT NULL DEFAULT '0.00',
										  `payment_price_name` varchar(255) DEFAULT NULL,
										  `payment_stripe_live_secret_key` varchar(255) DEFAULT NULL,
										  `payment_stripe_live_public_key` varchar(255) DEFAULT NULL,
										  `payment_stripe_test_secret_key` varchar(255) DEFAULT NULL,
										  `payment_stripe_test_public_key` varchar(255) DEFAULT NULL,
										  `payment_stripe_enable_test_mode` int(1) NOT NULL DEFAULT '0',
										  `payment_stripe_enable_receipt` int(1) NOT NULL DEFAULT '0',
										  `payment_stripe_receipt_element_id` int(11) DEFAULT NULL,
										  `payment_stripe_enable_payment_request_button` int(1) NOT NULL DEFAULT '0',
										  `payment_stripe_account_country` varchar(2) DEFAULT NULL,
										  `payment_paypal_rest_live_clientid` varchar(100) DEFAULT NULL,
										  `payment_paypal_rest_live_secret_key` varchar(100) DEFAULT NULL,
										  `payment_paypal_rest_test_clientid` varchar(100) DEFAULT NULL,
										  `payment_paypal_rest_test_secret_key` varchar(100) DEFAULT NULL,
										  `payment_paypal_rest_enable_test_mode` int(1) NOT NULL DEFAULT '0',
										  `payment_authorizenet_live_apiloginid` varchar(50) DEFAULT NULL,
										  `payment_authorizenet_live_transkey` varchar(50) DEFAULT NULL,
										  `payment_authorizenet_test_apiloginid` varchar(50) DEFAULT NULL,
										  `payment_authorizenet_test_transkey` varchar(50) DEFAULT NULL,
										  `payment_authorizenet_enable_test_mode` int(1) NOT NULL DEFAULT '0',
										  `payment_authorizenet_save_cc_data` int(1) NOT NULL DEFAULT '0',
										  `payment_authorizenet_enable_email` int(1) NOT NULL DEFAULT '0',
										  `payment_authorizenet_email_element_id` int(11) DEFAULT NULL,
										  `payment_braintree_live_merchant_id` varchar(50) DEFAULT NULL,
										  `payment_braintree_live_public_key` varchar(50) DEFAULT NULL,
										  `payment_braintree_live_private_key` varchar(50) DEFAULT NULL,
										  `payment_braintree_live_encryption_key` text,
										  `payment_braintree_test_merchant_id` varchar(50) DEFAULT NULL,
										  `payment_braintree_test_public_key` varchar(50) DEFAULT NULL,
										  `payment_braintree_test_private_key` varchar(50) DEFAULT NULL,
										  `payment_braintree_test_encryption_key` text,
										  `payment_braintree_enable_test_mode` int(1) NOT NULL DEFAULT '0',
										  `payment_paypal_enable_test_mode` int(1) NOT NULL DEFAULT '0',
										  `payment_enable_invoice` int(1) NOT NULL DEFAULT '0',
										  `payment_invoice_email` varchar(255) DEFAULT NULL,
										  `payment_delay_notifications` int(1) NOT NULL DEFAULT '1',
										  `payment_ask_billing` int(1) NOT NULL DEFAULT '0',
										  `payment_ask_shipping` int(1) NOT NULL DEFAULT '0',
										  `payment_enable_tax` int(1) NOT NULL DEFAULT '0',
										  `payment_tax_rate` decimal(62,2) NOT NULL DEFAULT '0.00',
										  `payment_enable_discount` int(1) NOT NULL DEFAULT '0',
										  `payment_discount_type` varchar(12) NOT NULL DEFAULT 'percent_off',
										  `payment_discount_amount` decimal(62,2) NOT NULL DEFAULT '0.00',
										  `payment_discount_code` text,
										  `payment_discount_element_id` int(11) DEFAULT NULL,
										  `payment_discount_max_usage` int(11) NOT NULL DEFAULT '0',
										  `payment_discount_expiry_date` date DEFAULT NULL,
										  `logic_field_enable` tinyint(1) NOT NULL DEFAULT '0',
										  `logic_page_enable` tinyint(1) NOT NULL DEFAULT '0',
										  `logic_email_enable` tinyint(1) NOT NULL DEFAULT '0',
										  `logic_webhook_enable` int(1) NOT NULL DEFAULT '0',
										  `logic_success_enable` int(1) NOT NULL DEFAULT '0',
										  `webhook_enable` tinyint(1) NOT NULL DEFAULT '0',
										  `webhook_url` text,
										  `webhook_method` varchar(4) NOT NULL DEFAULT 'post',
										  `form_encryption_enable` tinyint(1) NOT NULL DEFAULT '0',
										  `form_encryption_public_key` text,
										  `form_entry_edit_enable` tinyint(1) NOT NULL DEFAULT '0',
										  `form_entry_edit_resend_notifications` tinyint(1) NOT NULL DEFAULT '0',
										  `form_entry_edit_rerun_logics` tinyint(1) NOT NULL DEFAULT '0',
										  `form_entry_edit_auto_disable` tinyint(1) NOT NULL DEFAULT '0',
										  `form_entry_edit_auto_disable_period` int(11) NOT NULL DEFAULT '1',
										  `form_entry_edit_auto_disable_unit` char(1) NOT NULL DEFAULT 'r' COMMENT 'r,h,d',
										  `form_entry_edit_hide_editlink` tinyint(1) NOT NULL DEFAULT '0',
										  `form_keyword_blocking_enable` tinyint(1) NOT NULL DEFAULT '0',
										  `form_keyword_blocking_list` text,
										  PRIMARY KEY (`form_id`),
										  KEY `form_tags` (`form_tags`)
										) DEFAULT CHARSET=utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}
	
	function create_ap_column_preferences_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."column_preferences` (
																		  `acp_id` int(11) NOT NULL AUTO_INCREMENT,
																		  `form_id` int(11) DEFAULT NULL,
																		  `user_id` int(11) NOT NULL DEFAULT '1',
																		  `incomplete_entries` int(1) NOT NULL DEFAULT '0',
																		  `element_name` varchar(255) NOT NULL DEFAULT '',
																		  `position` int(11) NOT NULL DEFAULT '0',
																		  PRIMARY KEY (`acp_id`),
																		  KEY `acp_position` (`form_id`,`position`)
																		) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_element_options_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."element_options` (
														  `aeo_id` int(11) NOT NULL AUTO_INCREMENT,
														  `form_id` int(11) NOT NULL DEFAULT '0',
														  `element_id` int(11) NOT NULL DEFAULT '0',
														  `option_id` int(11) NOT NULL DEFAULT '0',
														  `position` int(11) NOT NULL DEFAULT '0',
														  `option` text,
														  `option_is_default` int(11) NOT NULL DEFAULT '0',
														  `option_is_hidden` int(11) NOT NULL DEFAULT '0',
														  `live` int(11) NOT NULL DEFAULT '1',
														  PRIMARY KEY (`aeo_id`),
														  KEY `form_id` (`form_id`),
														  KEY `element_id` (`element_id`)
														) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_element_prices_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."element_prices` (
														  `aep_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
														  `form_id` int(11) NOT NULL,
														  `element_id` int(11) NOT NULL,
														  `option_id` int(11) NOT NULL DEFAULT '0',
														  `price` decimal(62,2) NOT NULL DEFAULT '0.00',
														  PRIMARY KEY (`aep_id`),
														  KEY `form_id` (`form_id`),
														  KEY `element_id` (`element_id`)
														) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_form_elements_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_elements` (
														`form_id` int(11) NOT NULL DEFAULT '0',
														`element_id` int(11) NOT NULL DEFAULT '0',
														`element_title` text,
														`element_guidelines` text,
														`element_size` varchar(6) NOT NULL DEFAULT 'medium',
														`element_is_required` int(1) NOT NULL DEFAULT '0',
														`element_is_unique` int(1) NOT NULL DEFAULT '0',
														`element_is_readonly` int(1) NOT NULL DEFAULT '0',
														`element_is_private` int(1) NOT NULL DEFAULT '0' COMMENT '0=public,1=admin-only,2=hidden',
														`element_is_encrypted` int(1) NOT NULL DEFAULT '0',
														`element_type` varchar(50) DEFAULT NULL,
														`element_position` int(11) NOT NULL DEFAULT '0',
														`element_default_value` text,
														`element_enable_placeholder` int(1) NOT NULL DEFAULT '0',
														`element_constraint` varchar(255) DEFAULT NULL,
														`element_total_child` int(11) NOT NULL DEFAULT '0',
														`element_css_class` varchar(255) NOT NULL DEFAULT '',
														`element_range_min` bigint(11) unsigned NOT NULL DEFAULT '0',
														`element_range_max` bigint(11) unsigned NOT NULL DEFAULT '0',
														`element_range_limit_by` char(1) NOT NULL,
														`element_status` int(1) NOT NULL DEFAULT '2',
														`element_choice_columns` int(1) NOT NULL DEFAULT '1',
														`element_choice_has_other` int(1) NOT NULL DEFAULT '0',
														`element_choice_other_label` text,
														`element_choice_limit_rule` varchar(12) NOT NULL DEFAULT 'atleast',
														`element_choice_limit_qty` int(11) NOT NULL DEFAULT '1',
														`element_choice_limit_range_min` int(11) NOT NULL DEFAULT '1',
														`element_choice_limit_range_max` int(11) NOT NULL DEFAULT '1',
														`element_choice_max_entry` int(11) NOT NULL DEFAULT '0',
														`element_time_showsecond` int(11) NOT NULL DEFAULT '0',
														`element_time_24hour` int(11) NOT NULL DEFAULT '0',
														`element_address_us_only` int(11) NOT NULL DEFAULT '0',
														`element_date_enable_range` int(1) NOT NULL DEFAULT '0',
														`element_date_range_min` date DEFAULT NULL,
														`element_date_range_max` date DEFAULT NULL,
														`element_date_enable_selection_limit` int(1) NOT NULL DEFAULT '0',
														`element_date_selection_max` int(11) NOT NULL DEFAULT '1',
														`element_date_past_future` char(1) NOT NULL DEFAULT 'p',
														`element_date_disable_past_future` int(1) NOT NULL DEFAULT '0',
														`element_date_disable_dayofweek` int(1) NOT NULL DEFAULT '0',
														`element_date_disabled_dayofweek_list` varchar(15) DEFAULT NULL,
														`element_date_disable_specific` int(1) NOT NULL DEFAULT '0',
														`element_date_disabled_list` text CHARACTER SET utf8 COLLATE utf8_bin,
														`element_file_type_list` text,
														`element_file_as_attachment` int(1) NOT NULL DEFAULT '0',
														`element_file_auto_upload` int(1) NOT NULL DEFAULT '0',
														`element_file_enable_multi_upload` int(1) NOT NULL DEFAULT '0',
														`element_file_max_selection` int(11) NOT NULL DEFAULT '5',
														`element_file_enable_size_limit` int(1) NOT NULL DEFAULT '0',
														`element_file_size_max` int(11) DEFAULT NULL,
														`element_matrix_allow_multiselect` int(1) NOT NULL DEFAULT '0',
														`element_matrix_parent_id` int(11) NOT NULL DEFAULT '0',
														`element_number_enable_quantity` int(1) NOT NULL DEFAULT '0',
														`element_number_quantity_link` varchar(15) DEFAULT NULL,
														`element_section_display_in_email` int(1) NOT NULL DEFAULT '1',
														`element_section_enable_scroll` int(1) NOT NULL DEFAULT '0',
														`element_submit_use_image` int(1) NOT NULL DEFAULT '0',
														`element_submit_primary_text` varchar(255) NOT NULL DEFAULT 'Continue',
														`element_submit_secondary_text` varchar(255) NOT NULL DEFAULT 'Previous',
														`element_submit_primary_img` varchar(255) DEFAULT NULL,
														`element_submit_secondary_img` varchar(255) DEFAULT NULL,
														`element_page_title` varchar(255) DEFAULT NULL,
														`element_page_number` int(11) NOT NULL DEFAULT '1',
														`element_text_default_type` varchar(6) NOT NULL DEFAULT 'static',
														`element_text_default_length` int(11) NOT NULL DEFAULT '10',
														`element_text_default_random_type` varchar(8) NOT NULL DEFAULT 'letter',
														`element_text_default_prefix` varchar(50) DEFAULT NULL,
														`element_text_default_case` char(1) NOT NULL DEFAULT 'u',
														`element_email_enable_confirmation` int(1) NOT NULL DEFAULT '0',
														`element_email_confirm_field_label` varchar(255) DEFAULT NULL,
														`element_media_type` varchar(8) NOT NULL DEFAULT 'image',
														`element_media_image_src` text,
														`element_media_image_width` int(11) DEFAULT NULL,
														`element_media_image_height` int(11) DEFAULT NULL,
														`element_media_image_alignment` char(1) NOT NULL DEFAULT 'l',
														`element_media_image_alt` text,
														`element_media_image_href` text,
														`element_media_display_in_email` int(1) NOT NULL DEFAULT '0',
														`element_media_video_src` text,
														`element_media_video_size` varchar(6) NOT NULL DEFAULT 'large',
														`element_media_video_muted` int(1) NOT NULL DEFAULT '0',
														`element_media_video_caption_file` text,
														`element_media_pdf_src` text,
														`element_rating_style` varchar(10) NOT NULL DEFAULT 'star',
														`element_rating_max` int(11) NOT NULL DEFAULT '5',
														`element_rating_default` int(11) NOT NULL DEFAULT '0',
														`element_rating_enable_label` int(1) NOT NULL DEFAULT '0',
														`element_rating_label_high` varchar(50) NOT NULL DEFAULT 'Best',
														`element_rating_label_low` varchar(50) NOT NULL DEFAULT 'Worst',
														`element_address_subfields_visibility` text,
														`element_address_subfields_labels` text,
														`element_address_default_state` varchar(30) DEFAULT NULL,
														PRIMARY KEY (`form_id`,`element_id`)
													) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_form_filters_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_filters` (
													  `aff_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
													  `form_id` int(11) NOT NULL,
													  `user_id` int(11) NOT NULL DEFAULT '1',
													  `incomplete_entries` int(1) NOT NULL DEFAULT '0',
													  `element_name` varchar(50) NOT NULL DEFAULT '',
													  `filter_condition` varchar(15) NOT NULL DEFAULT 'is',
													  `filter_keyword` varchar(255) NOT NULL DEFAULT '',
													  PRIMARY KEY (`aff_id`),
													  KEY `form_id` (`form_id`)
													) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_form_themes_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_themes` (
												  `theme_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
												  `user_id` int(11) NOT NULL DEFAULT '1',
												  `status` int(1) DEFAULT '1',
												  `theme_has_css` int(1) NOT NULL DEFAULT '0',
												  `theme_name` varchar(255) DEFAULT '',
												  `theme_built_in` int(1) NOT NULL DEFAULT '0',
												  `theme_is_private` int(11) NOT NULL DEFAULT '1',
												  `logo_type` varchar(11) NOT NULL DEFAULT 'default' COMMENT 'default,custom,disabled',
												  `logo_custom_image` text,
												  `logo_custom_height` int(11) NOT NULL DEFAULT '40',
												  `logo_default_image` varchar(50) DEFAULT '',
												  `logo_default_repeat` int(1) NOT NULL DEFAULT '0',
												  `wallpaper_bg_type` varchar(11) NOT NULL DEFAULT 'color' COMMENT 'color,pattern,custom',
												  `wallpaper_bg_color` varchar(11) DEFAULT '',
												  `wallpaper_bg_pattern` varchar(50) DEFAULT '',
												  `wallpaper_bg_custom` text,
												  `header_bg_type` varchar(11) NOT NULL DEFAULT 'color' COMMENT 'color,pattern,custom',
												  `header_bg_color` varchar(11) DEFAULT '',
												  `header_bg_pattern` varchar(50) DEFAULT '',
												  `header_bg_custom` text,
												  `form_bg_type` varchar(11) NOT NULL DEFAULT 'color' COMMENT 'color,pattern,custom',
												  `form_bg_color` varchar(11) DEFAULT '',
												  `form_bg_pattern` varchar(50) DEFAULT '',
												  `form_bg_custom` text,
												  `highlight_bg_type` varchar(11) NOT NULL DEFAULT 'color' COMMENT 'color,pattern,custom',
												  `highlight_bg_color` varchar(11) DEFAULT '',
												  `highlight_bg_pattern` varchar(50) DEFAULT '',
												  `highlight_bg_custom` text,
												  `guidelines_bg_type` varchar(11) NOT NULL DEFAULT 'color' COMMENT 'color,pattern,custom',
												  `guidelines_bg_color` varchar(11) DEFAULT '',
												  `guidelines_bg_pattern` varchar(50) DEFAULT '',
												  `guidelines_bg_custom` text,
												  `field_bg_type` varchar(11) NOT NULL DEFAULT 'color' COMMENT 'color,pattern,custom',
												  `field_bg_color` varchar(11) DEFAULT '',
												  `field_bg_pattern` varchar(50) DEFAULT '',
												  `field_bg_custom` text,
												  `form_title_font_type` varchar(50) NOT NULL DEFAULT 'Lucida Grande',
												  `form_title_font_weight` int(11) NOT NULL DEFAULT '400',
												  `form_title_font_style` varchar(25) NOT NULL DEFAULT 'normal',
												  `form_title_font_size` varchar(11) DEFAULT '',
												  `form_title_font_color` varchar(11) DEFAULT '',
												  `form_desc_font_type` varchar(50) NOT NULL DEFAULT 'Lucida Grande',
												  `form_desc_font_weight` int(11) NOT NULL DEFAULT '400',
												  `form_desc_font_style` varchar(25) NOT NULL DEFAULT 'normal',
												  `form_desc_font_size` varchar(11) DEFAULT '',
												  `form_desc_font_color` varchar(11) DEFAULT '',
												  `field_title_font_type` varchar(50) NOT NULL DEFAULT 'Lucida Grande',
												  `field_title_font_weight` int(11) NOT NULL DEFAULT '400',
												  `field_title_font_style` varchar(25) NOT NULL DEFAULT 'normal',
												  `field_title_font_size` varchar(11) DEFAULT '',
												  `field_title_font_color` varchar(11) DEFAULT '',
												  `guidelines_font_type` varchar(50) NOT NULL DEFAULT 'Lucida Grande',
												  `guidelines_font_weight` int(11) NOT NULL DEFAULT '400',
												  `guidelines_font_style` varchar(25) NOT NULL DEFAULT 'normal',
												  `guidelines_font_size` varchar(11) DEFAULT '',
												  `guidelines_font_color` varchar(11) DEFAULT '',
												  `section_title_font_type` varchar(50) NOT NULL DEFAULT 'Lucida Grande',
												  `section_title_font_weight` int(11) NOT NULL DEFAULT '400',
												  `section_title_font_style` varchar(25) NOT NULL DEFAULT 'normal',
												  `section_title_font_size` varchar(11) DEFAULT '',
												  `section_title_font_color` varchar(11) DEFAULT '',
												  `section_desc_font_type` varchar(50) NOT NULL DEFAULT 'Lucida Grande',
												  `section_desc_font_weight` int(11) NOT NULL DEFAULT '400',
												  `section_desc_font_style` varchar(25) NOT NULL DEFAULT 'normal',
												  `section_desc_font_size` varchar(11) DEFAULT '',
												  `section_desc_font_color` varchar(11) DEFAULT '',
												  `field_text_font_type` varchar(50) NOT NULL DEFAULT 'Lucida Grande',
												  `field_text_font_weight` int(11) NOT NULL DEFAULT '400',
												  `field_text_font_style` varchar(25) NOT NULL DEFAULT 'normal',
												  `field_text_font_size` varchar(11) DEFAULT '',
												  `field_text_font_color` varchar(11) DEFAULT '',
												  `border_form_width` int(11) NOT NULL DEFAULT '1',
												  `border_form_style` varchar(11) NOT NULL DEFAULT 'solid',
												  `border_form_color` varchar(11) DEFAULT '',
												  `border_guidelines_width` int(11) NOT NULL DEFAULT '1',
												  `border_guidelines_style` varchar(11) NOT NULL DEFAULT 'solid',
												  `border_guidelines_color` varchar(11) DEFAULT '',
												  `border_section_width` int(11) NOT NULL DEFAULT '1',
												  `border_section_style` varchar(11) NOT NULL DEFAULT 'solid',
												  `border_section_color` varchar(11) DEFAULT '',
												  `form_shadow_style` varchar(25) NOT NULL DEFAULT 'WarpShadow',
												  `form_shadow_size` varchar(11) NOT NULL DEFAULT 'large',
												  `form_shadow_brightness` varchar(11) NOT NULL DEFAULT 'normal',
												  `form_button_type` varchar(11) NOT NULL DEFAULT 'text',
												  `form_button_text` varchar(100) NOT NULL DEFAULT 'Submit',
												  `form_button_image` text,
												  `advanced_css` text,
												  PRIMARY KEY (`theme_id`),
												  KEY `theme_name` (`theme_name`)
												) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function populate_ap_form_themes_table($dbh){
		
		$query = "INSERT INTO `".MF_TABLE_PREFIX."form_themes` (`theme_id`, `user_id`, `status`, `theme_has_css`, `theme_name`, `theme_built_in`, `theme_is_private`, `logo_type`, `logo_custom_image`, `logo_custom_height`, `logo_default_image`, `logo_default_repeat`, `wallpaper_bg_type`, `wallpaper_bg_color`, `wallpaper_bg_pattern`, `wallpaper_bg_custom`, `header_bg_type`, `header_bg_color`, `header_bg_pattern`, `header_bg_custom`, `form_bg_type`, `form_bg_color`, `form_bg_pattern`, `form_bg_custom`, `highlight_bg_type`, `highlight_bg_color`, `highlight_bg_pattern`, `highlight_bg_custom`, `guidelines_bg_type`, `guidelines_bg_color`, `guidelines_bg_pattern`, `guidelines_bg_custom`, `field_bg_type`, `field_bg_color`, `field_bg_pattern`, `field_bg_custom`, `form_title_font_type`, `form_title_font_weight`, `form_title_font_style`, `form_title_font_size`, `form_title_font_color`, `form_desc_font_type`, `form_desc_font_weight`, `form_desc_font_style`, `form_desc_font_size`, `form_desc_font_color`, `field_title_font_type`, `field_title_font_weight`, `field_title_font_style`, `field_title_font_size`, `field_title_font_color`, `guidelines_font_type`, `guidelines_font_weight`, `guidelines_font_style`, `guidelines_font_size`, `guidelines_font_color`, `section_title_font_type`, `section_title_font_weight`, `section_title_font_style`, `section_title_font_size`, `section_title_font_color`, `section_desc_font_type`, `section_desc_font_weight`, `section_desc_font_style`, `section_desc_font_size`, `section_desc_font_color`, `field_text_font_type`, `field_text_font_weight`, `field_text_font_style`, `field_text_font_size`, `field_text_font_color`, `border_form_width`, `border_form_style`, `border_form_color`, `border_guidelines_width`, `border_guidelines_style`, `border_guidelines_color`, `border_section_width`, `border_section_style`, `border_section_color`, `form_shadow_style`, `form_shadow_size`, `form_shadow_brightness`, `form_button_type`, `form_button_text`, `form_button_image`, `advanced_css`)
VALUES
	(1,1,1,0,'Green Senegal',1,1,'default','http://',40,'machform.png',0,'color','#cfdfc5','','','color','#bad4a9','','','color','#ffffff','','','color','#ecf1ea','','','color','#ecf1ea','','','color','#cfdfc5','','','Philosopher',700,'normal','','#80af1b','Philosopher',400,'normal','100%','#80af1b','Philosopher',700,'normal','110%','#80af1b','Ubuntu',400,'normal','','#666666','Philosopher',700,'normal','110%','#80af1b','Philosopher',400,'normal','95%','#80af1b','Ubuntu',400,'normal','','#666666',0,'solid','#bad4a9',1,'dashed','#bad4a9',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(2,1,1,0,'Blue Bigbird',1,1,'default','http://',40,'machform.png',0,'color','#336699','','','color','#6699cc','','','color','#ffffff','','','color','#ccdced','','','color','#6699cc','','','color','#FBFBFB','','','Open Sans',600,'normal','','','Open Sans',400,'normal','','','Open Sans',700,'normal','100%','','Ubuntu',400,'normal','80%','#ffffff','Open Sans',600,'normal','','','Open Sans',400,'normal','95%','','Open Sans',400,'normal','','',0,'solid','#336699',1,'dotted','#6699cc',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(3,1,1,0,'Blue Pionus',1,1,'default','http://',40,'machform.png',0,'color','#556270','','','color','#6b7b8c','','','color','#ffffff','','','color','#99aec4','','','color','#6b7b8c','','','color','#FBFBFB','','','Istok Web',400,'normal','170%','','Maven Pro',400,'normal','100%','','Istok Web',700,'normal','100%','','Maven Pro',400,'normal','95%','#ffffff','Istok Web',400,'normal','110%','','Maven Pro',400,'normal','95%','','Maven Pro',400,'normal','','',0,'solid','#556270',1,'solid','#6b7b8c',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(4,1,1,0,'Brown Conure',1,1,'default','http://',40,'machform.png',0,'pattern','#948c75','pattern_036.gif','','color','#b3a783','','','color','#ffffff','','','color','#e0d0a2','','','color','#948c75','','','color','#f0f0d8','pattern_036.gif','','Molengo',400,'normal','170%','','Molengo',400,'normal','110%','','Molengo',400,'normal','110%','','Nobile',400,'normal','','#ececec','Molengo',400,'normal','130%','','Molengo',400,'normal','100%','','Molengo',400,'normal','110%','',0,'solid','#948c75',1,'solid','#948c75',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(5,1,1,0,'Yellow Lovebird',1,1,'default','http://',40,'machform.png',0,'color','#f0d878','','','color','#edb817','','','color','#ffffff','','','color','#f5d678','','','color','#f7c52e','','','color','#FBFBFB','','','Amaranth',700,'normal','170%','','Amaranth',400,'normal','100%','','Amaranth',700,'normal','100%','','Amaranth',400,'normal','','#444444','Amaranth',400,'normal','110%','','Amaranth',400,'normal','95%','','Amaranth',400,'normal','100%','',0,'solid','#f0d878',1,'solid','#f7c52e',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(6,1,1,0,'Pink Starling',1,1,'default','http://',40,'machform.png',0,'color','#ff6699','','','color','#d93280','','','color','#ffffff','','','color','#ffd0d4','','','color','#f9fad2','','','color','#FBFBFB','','','Josefin Sans',600,'normal','160%','','Josefin Sans',400,'normal','110%','','Josefin Sans',700,'normal','110%','','Josefin Sans',600,'normal','100%','','Josefin Sans',700,'normal','','','Josefin Sans',400,'normal','110%','','Josefin Sans',400,'normal','130%','',0,'solid','#ff6699',1,'dashed','#f56990',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(8,1,1,0,'Red Rabbit',1,1,'default','http://',40,'machform.png',0,'color','#a40802','','','color','#800e0e','','','color','#ffffff','','','color','#ffa4a0','','','color','#800e0e','','','color','#FBFBFB','','','Lobster',400,'normal','','#000000','Ubuntu',400,'normal','100%','#000000','Lobster',400,'normal','110%','#222222','Ubuntu',400,'normal','85%','#ffffff','Lobster',400,'normal','130%','#000000','Ubuntu',400,'normal','95%','#000000','Ubuntu',400,'normal','','#333333',0,'solid','#a40702',1,'solid','#800e0e',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(9,1,1,0,'Orange Robin',1,1,'default','http://',40,'machform.png',0,'color','#f38430','','','color','#fa6800','','','color','#ffffff','','','color','#a7dbd8','','','color','#e0e4cc','','','color','#FBFBFB','','','Lucida Grande',400,'normal','','#000000','Nobile',400,'normal','','#000000','Nobile',700,'normal','','#000000','Lucida Grande',400,'normal','','#444444','Nobile',700,'normal','100%','#000000','Nobile',400,'normal','','#000000','Nobile',400,'normal','95%','#333333',0,'solid','#f38430',1,'solid','#e0e4cc',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(10,1,1,0,'Orange Sunbird',1,1,'default','http://',40,'machform.png',0,'color','#d95c43','','','color','#c02942','','','color','#ffffff','','','color','#d95c43','','','color','#53777a','','','color','#FBFBFB','','','Lucida Grande',400,'normal','','#000000','Lucida Grande',400,'normal','','#000000','Lucida Grande',700,'normal','','#222222','Lucida Grande',400,'normal','','#ffffff','Lucida Grande',400,'normal','','#000000','Lucida Grande',400,'normal','','#000000','Lucida Grande',400,'normal','','#333333',0,'solid','#d95c43',1,'solid','#53777a',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(11,1,1,0,'Green Ringneck',1,1,'default','http://',40,'machform.png',0,'color','#0b486b','','','color','#3b8686','','','color','#ffffff','','','color','#cff09e','','','color','#79bd9a','','','color','#a8dba8','','','Delius Swash Caps',400,'normal','','#000000','Delius Swash Caps',400,'normal','100%','#000000','Delius Swash Caps',400,'normal','100%','#222222','Delius',400,'normal','85%','#ffffff','Delius Swash Caps',400,'normal','','#000000','Delius Swash Caps',400,'normal','95%','#000000','Delius',400,'normal','','#515151',0,'solid','#0b486b',1,'solid','#79bd9a',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(12,1,1,0,'Brown Finch',1,1,'default','http://',40,'machform.png',0,'color','#774f38','','','color','#e08e79','','','color','#ffffff','','','color','#ece5ce','','','color','#c5e0dc','','','color','#f9fad2','','','Arvo',700,'normal','','#000000','Arvo',400,'normal','','#000000','Arvo',700,'normal','','#222222','Arvo',400,'normal','','#444444','Arvo',400,'normal','','#000000','Arvo',400,'normal','85%','#000000','Arvo',400,'normal','','#333333',0,'solid','#774f38',1,'dashed','#e08e79',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(14,1,1,0,'Brown Macaw',1,1,'default','http://',40,'machform.png',0,'color','#413e4a','','','color','#73626e','','','pattern','#ffffff','pattern_022.gif','','color','#f0b49e','','','color','#b38184','','','color','#FBFBFB','','','Cabin',500,'normal','160%','#000000','Cabin',400,'normal','100%','#000000','Cabin',700,'normal','110%','#222222','Lucida Grande',400,'normal','','#ececec','Cabin',600,'normal','','#000000','Cabin',600,'normal','95%','#000000','Cabin',400,'normal','110%','#333333',0,'solid','#413e4a',1,'dotted','#ff9900',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(15,1,1,0,'Pink Thrush',1,1,'default','http://',40,'machform.png',0,'color','#ff9f9d','','','color','#ff3d7e','','','color','#ffffff','','','color','#7fc7af','','','color','#3fb8b0','','','color','#FBFBFB','','','Crafty Girls',400,'normal','','#000000','Crafty Girls',400,'normal','100%','#000000','Crafty Girls',400,'normal','100%','#222222','Nobile',400,'normal','80%','#ffffff','Crafty Girls',400,'normal','','#000000','Crafty Girls',400,'normal','95%','#000000','Molengo',400,'normal','110%','#333333',0,'solid','#ff9f9d',1,'solid','#3fb8b0',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(16,1,1,0,'Yellow Bulbul',1,1,'default','http://',40,'machform.png',0,'color','#f8f4d7','','','color','#f4b26c','','','color','#f4dec2','','','color','#f2b4a7','','','color','#e98976','','','color','#FBFBFB','','','Special Elite',400,'normal','','#000000','Special Elite',400,'normal','','#000000','Special Elite',400,'normal','95%','#222222','Cousine',400,'normal','80%','#ececec','Special Elite',400,'normal','','#000000','Special Elite',400,'normal','','#000000','Cousine',400,'normal','','#333333',0,'solid','#f8f4d7',1,'solid','#f4b26c',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(17,1,1,0,'Blue Canary',1,1,'default','http://',40,'machform.png',0,'color','#81a8b8','','','color','#a4bcc2','','','color','#ffffff','','','color','#e8f3f8','','','color','#dbe6ec','','','color','#FBFBFB','','','PT Sans',400,'normal','','#000000','PT Sans',400,'normal','100%','#000000','PT Sans',700,'normal','100%','#222222','PT Sans',400,'normal','','#666666','PT Sans',700,'normal','','#000000','PT Sans',400,'normal','100%','#000000','PT Sans',400,'normal','110%','#333333',0,'solid','#81a8b8',1,'dashed','#a4bcc2',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(18,1,1,0,'Red Mockingbird',1,1,'default','http://',40,'machform.png',0,'color','#6b0103','','','color','#a30005','','','color','#c21b01','','','color','#f03d02','','','color','#1c0113','','','color','#FBFBFB','','','Oswald',400,'normal','','#ffffff','Open Sans',400,'normal','','#ffffff','Oswald',400,'normal','95%','#ffffff','Open Sans',400,'normal','','#ececec','Oswald',400,'normal','','#ececec','Lucida Grande',400,'normal','','#ffffff','Open Sans',400,'normal','','#333333',0,'solid','#6b0103',1,'solid','#1c0113',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(13,1,1,0,'Green Sparrow',1,1,'default','http://',40,'machform.png',0,'color','#d1f2a5','','','color','#f56990','','','color','#ffffff','','','color','#ffc48c','','','color','#ffa080','','','color','#FBFBFB','','','Open Sans',400,'normal','','#000000','Open Sans',400,'normal','','#000000','Open Sans',700,'normal','','#222222','Ubuntu',400,'normal','85%','#f4fce8','Open Sans',600,'normal','','#000000','Open Sans',400,'normal','95%','#000000','Open Sans',400,'normal','','#333333',10,'solid','#f0fab4',1,'solid','#ffa080',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(21,1,1,0,'Purple Vanga',1,1,'default','http://',40,'machform.png',0,'color','#7b85e2','','','color','#7aa6d6','','','color','#d1e7f9','','','color','#7aa6d6','','','color','#fbfcd0','','','color','#FBFBFB','','','Droid Sans',400,'normal','160%','#444444','Droid Sans',400,'normal','95%','#444444','Open Sans',700,'normal','95%','#444444','Droid Sans',400,'normal','85%','#444444','Droid Sans',400,'normal','110%','#444444','Droid Sans',400,'normal','95%','#000000','Droid Sans',400,'normal','100%','#333333',0,'solid','#CCCCCC',1,'solid','#fbfcd0',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(22,1,1,0,'Purple Dove',1,1,'default','http://',40,'machform.png',0,'color','#c0addb','','','color','#a662de','','','pattern','#ffffff','pattern_044.gif','','color','#a662de','','','color','#a662de','','','color','#c0addb','','','Pacifico',400,'normal','180%','#000000','Open Sans',400,'normal','95%','#000000','Pacifico',400,'normal','95%','#222222','Open Sans',400,'normal','80%','#ececec','Pacifico',400,'normal','110%','#000000','Open Sans',400,'normal','95%','#000000','Open Sans',400,'normal','100%','#333333',0,'solid','#a662de',1,'dashed','#CCCCCC',1,'dashed','#a662de','StandShadow','large','dark','text','Submit','http://',''),
	(20,1,1,0,'Pink Flamingo',1,1,'default','http://',40,'machform.png',0,'color','#f87d7b','','','color','#5ea0a3','','','color','#ffffff','','','color','#fab97f','','','color','#dcd1b4','','','color','#FBFBFB','','','Lucida Grande',400,'normal','160%','#b05573','Lucida Grande',400,'normal','95%','#b05573','Lucida Grande',700,'normal','95%','#b05573','Lucida Grande',400,'normal','80%','#444444','Lucida Grande',400,'normal','110%','#b05573','Lucida Grande',400,'normal','85%','#b05573','Lucida Grande',400,'normal','100%','#333333',0,'solid','#f87d7b',1,'dotted','#fab97f',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
	(19,1,1,0,'Yellow Kiwi',1,1,'default','http://',40,'machform.png',0,'color','#ffe281','','','color','#ffbb7f','','','color','#eee9e5','','','color','#fad4b2','','','color','#ff9c97','','','color','#FBFBFB','','','Lucida Grande',400,'normal','160%','#000000','Lucida Grande',400,'normal','95%','#000000','Lucida Grande',700,'normal','95%','#222222','Lucida Grande',400,'normal','80%','#ffffff','Lucida Grande',400,'normal','110%','#000000','Lucida Grande',400,'normal','85%','#000000','Lucida Grande',400,'normal','100%','#333333',0,'solid','#ffe281',1,'solid','#CCCCCC',1,'dotted','#cdcdcd','WarpShadow','large','normal','text','Submit','http://','');";
			
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_fonts_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."fonts` (
											  `font_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
											  `font_origin` varchar(11) NOT NULL DEFAULT 'google',
											  `font_family` varchar(100) DEFAULT NULL,
											  `font_variants` text,
											  `font_variants_numeric` text,
											  PRIMARY KEY (`font_id`),
											  KEY `font_family` (`font_family`)
											) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function populate_ap_fonts_table($dbh){

		//truncate ap_fonts table
		$query = "TRUNCATE ".MF_TABLE_PREFIX."fonts";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		$query = "INSERT INTO `".MF_TABLE_PREFIX."fonts` (`font_id`, `font_origin`, `font_family`, `font_variants`, `font_variants_numeric`)
														VALUES
														(1,'google','Roboto','100,100italic,300,300italic,regular,italic,500,500italic,700,700italic,900,900italic','100,100-italic,300,300-italic,400,400-italic,500,500-italic,700,700-italic,900,900italic'),
														(2,'google','Open Sans','300,regular,500,600,700,800,300italic,italic,500italic,600italic,700italic,800italic','300,400,500,600,700,800,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic'),
														(3,'google','Noto Sans JP','100,300,regular,500,700,900','100,300,400,500,700,900'),
														(4,'google','Lato','100,100italic,300,300italic,regular,italic,700,700italic,900,900italic','100,100-italic,300,300-italic,400,400-italic,700,700-italic,900,900italic'),
														(5,'google','Montserrat','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(6,'google','Roboto Condensed','300,300italic,regular,italic,700,700italic','300,300-italic,400,400-italic,700,700-italic'),
														(7,'google','Source Sans Pro','200,200italic,300,300italic,regular,italic,600,600italic,700,700italic,900,900italic','200,200-italic,300,300-italic,400,400-italic,600,600-italic,700,700-italic,900,900italic'),
														(8,'google','Poppins','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic,900,900italic'),
														(9,'google','Oswald','200,300,regular,500,600,700','200,300,400,500,600,700'),
														(10,'google','Roboto Mono','100,200,300,regular,500,600,700,100italic,200italic,300italic,italic,500italic,600italic,700italic','100,200,300,400,500,600,700,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic'),
														(11,'google','Noto Sans','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(12,'google','Raleway','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(13,'google','Ubuntu','300,300italic,regular,italic,500,500italic,700,700italic','300,300-italic,400,400-italic,500,500-italic,700,700-italic'),
														(14,'google','Roboto Slab','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(15,'google','Merriweather','300,300italic,regular,italic,700,700italic,900,900italic','300,300-italic,400,400-italic,700,700-italic,900,900italic'),
														(16,'google','PT Sans','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(17,'google','Playfair Display','regular,500,600,700,800,900,italic,500italic,600italic,700italic,800italic,900italic','400,500,600,700,800,900,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(18,'google','Nunito','200,300,regular,500,600,700,800,900,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','200,300,400,500,600,700,800,900,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(19,'google','Inter','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(20,'google','Rubik','300,regular,500,600,700,800,900,300italic,italic,500italic,600italic,700italic,800italic,900italic','300,400,500,600,700,800,900,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(21,'google','Noto Sans KR','100,300,regular,500,700,900','100,300,400,500,700,900'),
														(22,'google','Open Sans Condensed','300,300italic,700','300,300-italic,700'),
														(23,'google','Lora','regular,500,600,700,italic,500italic,600italic,700italic','400,500,600,700,400-italic,500-italic,600-italic,700-italic'),
														(24,'google','Mukta','200,300,regular,500,600,700,800','200,300,400,500,600,700,800'),
														(25,'google','Work Sans','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(26,'google','Nunito Sans','200,200italic,300,300italic,regular,italic,600,600italic,700,700italic,800,800italic,900,900italic','200,200-italic,300,300-italic,400,400-italic,600,600-italic,700,700-italic,800,800-italic,900,900italic'),
														(27,'google','Quicksand','300,regular,500,600,700','300,400,500,600,700'),
														(28,'google','Nanum Gothic','regular,700,800','400,700,800'),
														(29,'google','Fira Sans','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic,900,900italic'),
														(30,'google','PT Serif','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(31,'google','Noto Sans TC','100,300,regular,500,700,900','100,300,400,500,700,900'),
														(32,'google','Titillium Web','200,200italic,300,300italic,regular,italic,600,600italic,700,700italic,900','200,200-italic,300,300-italic,400,400-italic,600,600-italic,700,700-italic,900'),
														(33,'google','Hind Siliguri','300,regular,500,600,700','300,400,500,600,700'),
														(34,'google','Barlow','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic,900,900italic'),
														(35,'google','Noto Serif','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(36,'google','Karla','200,300,regular,500,600,700,800,200italic,300italic,italic,500italic,600italic,700italic,800italic','200,300,400,500,600,700,800,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic'),
														(37,'google','Inconsolata','200,300,regular,500,600,700,800,900','200,300,400,500,600,700,800,900'),
														(38,'google','Heebo','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(39,'google','Libre Franklin','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(40,'google','PT Sans Narrow','regular,700','400,700'),
														(41,'google','Oxygen','300,regular,700','300,400,700'),
														(42,'google','Libre Baskerville','regular,italic,700','400,400-italic,700'),
														(43,'google','Source Code Pro','200,300,regular,500,600,700,800,900,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','200,300,400,500,600,700,800,900,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(44,'google','Arimo','regular,500,600,700,italic,500italic,600italic,700italic','400,500,600,700,400-italic,500-italic,600-italic,700-italic'),
														(45,'google','Bebas Neue','regular','400'),
														(46,'google','Dosis','200,300,regular,500,600,700,800','200,300,400,500,600,700,800'),
														(47,'google','Josefin Sans','100,200,300,regular,500,600,700,100italic,200italic,300italic,italic,500italic,600italic,700italic','100,200,300,400,500,600,700,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic'),
														(48,'google','IBM Plex Sans','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(49,'google','Anton','regular','400'),
														(50,'google','Mulish','200,300,regular,500,600,700,800,900,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','200,300,400,500,600,700,800,900,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(51,'google','Cabin','regular,500,600,700,italic,500italic,600italic,700italic','400,500,600,700,400-italic,500-italic,600-italic,700-italic'),
														(52,'google','Bitter','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(53,'google','Lobster','regular','400'),
														(54,'google','Barlow Condensed','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic,900,900italic'),
														(55,'google','EB Garamond','regular,500,600,700,800,italic,500italic,600italic,700italic,800italic','400,500,600,700,800,400-italic,500-italic,600-italic,700-italic,800-italic'),
														(56,'google','Cairo','200,300,regular,500,600,700,800,900','200,300,400,500,600,700,800,900'),
														(57,'google','Dancing Script','regular,500,600,700','400,500,600,700'),
														(58,'google','DM Sans','regular,italic,500,500italic,700,700italic','400,400-italic,500,500-italic,700,700-italic'),
														(59,'google','Source Serif Pro','200,200italic,300,300italic,regular,italic,600,600italic,700,700italic,900,900italic','200,200-italic,300,300-italic,400,400-italic,600,600-italic,700,700-italic,900,900italic'),
														(60,'google','Noto Sans SC','100,300,regular,500,700,900','100,300,400,500,700,900'),
														(61,'google','Yanone Kaffeesatz','200,300,regular,500,600,700','200,300,400,500,600,700'),
														(62,'google','Hind','300,regular,500,600,700','300,400,500,600,700'),
														(63,'google','Prompt','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic,900,900italic'),
														(64,'google','Varela Round','regular','400'),
														(65,'google','Fjalla One','regular','400'),
														(66,'google','Abel','regular','400'),
														(67,'google','Kanit','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic,900,900italic'),
														(68,'google','Comfortaa','300,regular,500,600,700','300,400,500,600,700'),
														(69,'google','Teko','300,regular,500,600,700','300,400,500,600,700'),
														(70,'google','Exo 2','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(71,'google','Manrope','200,300,regular,500,600,700,800','200,300,400,500,600,700,800'),
														(72,'google','Pacifico','regular','400'),
														(73,'google','Maven Pro','regular,500,600,700,800,900','400,500,600,700,800,900'),
														(74,'google','Arvo','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(75,'google','Noto Sans HK','100,300,regular,500,700,900','100,300,400,500,700,900'),
														(76,'google','Shadows Into Light','regular','400'),
														(77,'google','Barlow Semi Condensed','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic,900,900italic'),
														(78,'google','Merriweather Sans','300,regular,500,600,700,800,300italic,italic,500italic,600italic,700italic,800italic','300,400,500,600,700,800,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic'),
														(79,'google','Saira Condensed','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(80,'google','Indie Flower','regular','400'),
														(81,'google','Architects Daughter','regular','400'),
														(82,'google','Asap','regular,500,600,700,italic,500italic,600italic,700italic','400,500,600,700,400-italic,500-italic,600-italic,700-italic'),
														(83,'google','Hind Madurai','300,regular,500,600,700','300,400,500,600,700'),
														(84,'google','Overpass','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(85,'google','Abril Fatface','regular','400'),
														(86,'google','Signika Negative','300,regular,500,600,700','300,400,500,600,700'),
														(87,'google','IBM Plex Serif','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(88,'google','Cormorant Garamond','300,300italic,regular,italic,500,500italic,600,600italic,700,700italic','300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(89,'google','Slabo 27px','regular','400'),
														(90,'google','Padauk','regular,700','400,700'),
														(91,'google','Assistant','200,300,regular,500,600,700,800','200,300,400,500,600,700,800'),
														(92,'google','Noto Serif JP','200,300,regular,500,600,700,900','200,300,400,500,600,700,900'),
														(93,'google','Questrial','regular','400'),
														(94,'google','Balsamiq Sans','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(95,'google','Fira Sans Condensed','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic,900,900italic'),
														(96,'google','Catamaran','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(97,'google','Caveat','regular,500,600,700','400,500,600,700'),
														(98,'google','Permanent Marker','regular','400'),
														(99,'google','Exo','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(100,'google','Ubuntu Mono','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(101,'google','Rajdhani','300,regular,500,600,700','300,400,500,600,700'),
														(102,'google','Archivo Narrow','regular,500,600,700,italic,500italic,600italic,700italic','400,500,600,700,400-italic,500-italic,600-italic,700-italic'),
														(103,'google','Patrick Hand','regular','400'),
														(104,'google','Tajawal','200,300,regular,500,700,800,900','200,300,400,500,700,800,900'),
														(105,'google','Secular One','regular','400'),
														(106,'google','Domine','regular,500,600,700','400,500,600,700'),
														(107,'google','Play','regular,700','400,700'),
														(108,'google','Zilla Slab','300,300italic,regular,italic,500,500italic,600,600italic,700,700italic','300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(109,'google','Acme','regular','400'),
														(110,'google','Public Sans','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(111,'google','Satisfy','regular','400'),
														(112,'google','Staatliches','regular','400'),
														(113,'google','Signika','300,regular,500,600,700','300,400,500,600,700'),
														(114,'google','M PLUS Rounded 1c','100,300,regular,500,700,800,900','100,300,400,500,700,800,900'),
														(115,'google','ABeeZee','regular,italic','400,400-italic'),
														(116,'google','Spartan','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(117,'google','Bree Serif','regular','400'),
														(118,'google','Archivo','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(119,'google','Alfa Slab One','regular','400'),
														(120,'google','Amatic SC','regular,700','400,700'),
														(121,'google','Jost','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(122,'google','Nanum Myeongjo','regular,700,800','400,700,800'),
														(123,'google','Crete Round','regular,italic','400,400-italic'),
														(124,'google','Noto Sans Display','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(125,'google','Cookie','regular','400'),
														(126,'google','Vollkorn','regular,500,600,700,800,900,italic,500italic,600italic,700italic,800italic,900italic','400,500,600,700,800,900,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(127,'google','Righteous','regular','400'),
														(128,'google','Space Mono','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(129,'google','Amiri','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(130,'google','Yantramanav','100,300,regular,500,700,900','100,300,400,500,700,900'),
														(131,'google','Red Hat Display','300,regular,500,600,700,800,900,300italic,italic,500italic,600italic,700italic,800italic,900italic','300,400,500,600,700,800,900,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(132,'google','Fredoka One','regular','400'),
														(133,'google','Space Grotesk','300,regular,500,600,700','300,400,500,600,700'),
														(134,'google','Sarabun','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic'),
														(135,'google','Gothic A1','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(136,'google','Martel','200,300,regular,600,700,800,900','200,300,400,600,700,800,900'),
														(137,'google','Patua One','regular','400'),
														(138,'google','Chakra Petch','300,300italic,regular,italic,500,500italic,600,600italic,700,700italic','300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(139,'google','Antic Slab','regular','400'),
														(140,'google','M PLUS 1p','100,300,regular,500,700,800,900','100,300,400,500,700,800,900'),
														(141,'google','Cinzel','regular,500,600,700,800,900','400,500,600,700,800,900'),
														(142,'google','Courgette','regular','400'),
														(143,'google','Alegreya Sans','100,100italic,300,300italic,regular,italic,500,500italic,700,700italic,800,800italic,900,900italic','100,100-italic,300,300-italic,400,400-italic,500,500-italic,700,700-italic,800,800-italic,900,900italic'),
														(144,'google','IBM Plex Mono','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(145,'google','Alegreya','regular,500,600,700,800,900,italic,500italic,600italic,700italic,800italic,900italic','400,500,600,700,800,900,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(146,'google','Ubuntu Condensed','regular','400'),
														(147,'google','Almarai','300,regular,700,800','300,400,700,800'),
														(148,'google','Great Vibes','regular','400'),
														(149,'google','Didact Gothic','regular','400'),
														(150,'google','Lobster Two','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(151,'google','PT Sans Caption','regular,700','400,700'),
														(152,'google','Archivo Black','regular','400'),
														(153,'google','Tinos','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(154,'google','Prata','regular','400'),
														(155,'google','Russo One','regular','400'),
														(156,'google','Kaushan Script','regular','400'),
														(157,'google','DM Serif Display','regular,italic','400,400-italic'),
														(158,'google','Frank Ruhl Libre','300,regular,500,700,900','300,400,500,700,900'),
														(159,'google','Spectral','200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic','200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic'),
														(160,'google','Gelasio','regular,italic,500,500italic,600,600italic,700,700italic','400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(161,'google','Noticia Text','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(162,'google','Crimson Pro','200,300,regular,500,600,700,800,900,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','200,300,400,500,600,700,800,900,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(163,'google','Francois One','regular','400'),
														(164,'google','Kalam','300,regular,700','300,400,700'),
														(165,'google','Cardo','regular,italic,700','400,400-italic,700'),
														(166,'google','Asap Condensed','regular,italic,500,500italic,600,600italic,700,700italic','400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(167,'google','Parisienne','regular','400'),
														(168,'google','Baloo 2','regular,500,600,700,800','400,500,600,700,800'),
														(169,'google','Old Standard TT','regular,italic,700','400,400-italic,700'),
														(170,'google','Encode Sans','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(171,'google','Luckiest Guy','regular','400'),
														(172,'google','Noto Kufi Arabic','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(173,'google','Bangers','regular','400'),
														(174,'google','Changa','200,300,regular,500,600,700,800','200,300,400,500,600,700,800'),
														(175,'google','Montserrat Alternates','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic,900,900italic'),
														(176,'google','Concert One','regular','400'),
														(177,'google','Sacramento','regular','400'),
														(178,'google','Pathway Gothic One','regular','400'),
														(179,'google','Quattrocento Sans','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(180,'google','Monda','regular,700','400,700'),
														(181,'google','Gloria Hallelujah','regular','400'),
														(182,'google','Orbitron','regular,500,600,700,800,900','400,500,600,700,800,900'),
														(183,'google','Rokkitt','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(184,'google','Chivo','300,300italic,regular,italic,700,700italic,900,900italic','300,300-italic,400,400-italic,700,700-italic,900,900italic'),
														(185,'google','Volkhov','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(186,'google','Advent Pro','100,200,300,regular,500,600,700','100,200,300,400,500,600,700'),
														(187,'google','Cuprum','regular,500,600,700,italic,500italic,600italic,700italic','400,500,600,700,400-italic,500-italic,600-italic,700-italic'),
														(188,'google','Eczar','regular,500,600,700,800','400,500,600,700,800'),
														(189,'google','Faustina','300,regular,500,600,700,800,300italic,italic,500italic,600italic,700italic,800italic','300,400,500,600,700,800,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic'),
														(190,'google','Special Elite','regular','400'),
														(191,'google','Cormorant','300,300italic,regular,italic,500,500italic,600,600italic,700,700italic','300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(192,'google','Saira','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(193,'google','Passion One','regular,700,900','400,700,900'),
														(194,'google','Paytone One','regular','400'),
														(195,'google','Josefin Slab','100,200,300,regular,500,600,700,100italic,200italic,300italic,italic,500italic,600italic,700italic','100,200,300,400,500,600,700,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic'),
														(196,'google','Sawarabi Mincho','regular','400'),
														(197,'google','Khand','300,regular,500,600,700','300,400,500,600,700'),
														(198,'google','Cantarell','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(199,'google','Playfair Display SC','regular,italic,700,700italic,900,900italic','400,400-italic,700,700-italic,900,900italic'),
														(200,'google','Noto Serif TC','200,300,regular,500,600,700,900','200,300,400,500,600,700,900'),
														(201,'google','Recursive','300,regular,500,600,700,800,900','300,400,500,600,700,800,900'),
														(202,'google','Itim','regular','400'),
														(203,'google','PT Mono','regular','400'),
														(204,'google','Press Start 2P','regular','400'),
														(205,'google','Hammersmith One','regular','400'),
														(206,'google','Unna','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(207,'google','News Cycle','regular,700','400,700'),
														(208,'google','Quattrocento','regular,700','400,700'),
														(209,'google','Ropa Sans','regular,italic','400,400-italic'),
														(210,'google','Squada One','regular','400'),
														(211,'google','Vidaloka','regular','400'),
														(212,'google','Fira Sans Extra Condensed','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic,900,900italic'),
														(213,'google','El Messiri','regular,500,600,700','400,500,600,700'),
														(214,'google','Philosopher','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(215,'google','Alata','regular','400'),
														(216,'google','Handlee','regular','400'),
														(217,'google','Poiret One','regular','400'),
														(218,'google','Be Vietnam Pro','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic,900,900italic'),
														(219,'google','Titan One','regular','400'),
														(220,'google','Sanchez','regular,italic','400,400-italic'),
														(221,'google','Neuton','200,300,regular,italic,700,800','200,300,400,400-italic,700,800'),
														(222,'google','Ruda','regular,500,600,700,800,900','400,500,600,700,800,900'),
														(223,'google','Marcellus','regular','400'),
														(224,'google','Ultra','regular','400'),
														(225,'google','Playball','regular','400'),
														(226,'google','DM Serif Text','regular,italic','400,400-italic'),
														(227,'google','Yellowtail','regular','400'),
														(228,'google','Sigmar One','regular','400'),
														(229,'google','Mitr','200,300,regular,500,600,700','200,300,400,500,600,700'),
														(230,'google','Viga','regular','400'),
														(231,'google','Tangerine','regular,700','400,700'),
														(232,'google','Mali','200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic','200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(233,'google','Neucha','regular','400'),
														(234,'google','Outfit','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(235,'google','Arapey','regular,italic','400,400-italic'),
														(236,'google','Yeseva One','regular','400'),
														(237,'google','Alice','regular','400'),
														(238,'google','Istok Web','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(239,'google','Sawarabi Gothic','regular','400'),
														(240,'google','Arima Madurai','100,200,300,regular,500,700,800,900','100,200,300,400,500,700,800,900'),
														(241,'google','Taviraj','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic,900,900italic'),
														(242,'google','Noto Serif KR','200,300,regular,500,600,700,900','200,300,400,500,600,700,900'),
														(243,'google','Gudea','regular,italic,700','400,400-italic,700'),
														(244,'google','Actor','regular','400'),
														(245,'google','Sora','100,200,300,regular,500,600,700,800','100,200,300,400,500,600,700,800'),
														(246,'google','Gochi Hand','regular','400'),
														(247,'google','Lusitana','regular,700','400,700'),
														(248,'google','Monoton','regular','400'),
														(249,'google','Aleo','300,300italic,regular,italic,700,700italic','300,300-italic,400,400-italic,700,700-italic'),
														(250,'google','Bungee','regular','400'),
														(251,'google','Lexend Deca','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(252,'google','Allura','regular','400'),
														(253,'google','Adamina','regular','400'),
														(254,'google','Nanum Pen Script','regular','400'),
														(255,'google','Amaranth','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(256,'google','Jura','300,regular,500,600,700','300,400,500,600,700'),
														(257,'google','Varela','regular','400'),
														(258,'google','Homemade Apple','regular','400'),
														(259,'google','Nanum Gothic Coding','regular,700','400,700'),
														(260,'google','Ramabhadra','regular','400'),
														(261,'google','Merienda','regular,700','400,700'),
														(262,'google','Cabin Condensed','regular,500,600,700','400,500,600,700'),
														(263,'google','Karma','300,regular,500,600,700','300,400,500,600,700'),
														(264,'google','Khula','300,regular,600,700,800','300,400,600,700,800'),
														(265,'google','Hind Vadodara','300,regular,500,600,700','300,400,500,600,700'),
														(266,'google','Economica','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(267,'google','Saira Semi Condensed','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(268,'google','Noto Serif SC','200,300,regular,500,600,700,900','200,300,400,500,600,700,900'),
														(269,'google','Pangolin','regular','400'),
														(270,'google','Pragati Narrow','regular,700','400,700'),
														(271,'google','Alef','regular,700','400,700'),
														(272,'google','Palanquin','100,200,300,regular,500,600,700','100,200,300,400,500,600,700'),
														(273,'google','Gentium Basic','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(274,'google','BenchNine','300,regular,700','300,400,700'),
														(275,'google','Bai Jamjuree','200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic','200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(276,'google','Fugaz One','regular','400'),
														(277,'google','Creepster','regular','400'),
														(278,'google','Unica One','regular','400'),
														(279,'google','Carter One','regular','400'),
														(280,'google','Oleo Script','regular,700','400,700'),
														(281,'google','Armata','regular','400'),
														(282,'google','Sen','regular,700,800','400,700,800'),
														(283,'google','Rock Salt','regular','400'),
														(284,'google','Cousine','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(285,'google','Share Tech Mono','regular','400'),
														(286,'google','Audiowide','regular','400'),
														(287,'google','Kosugi Maru','regular','400'),
														(288,'google','Lilita One','regular','400'),
														(289,'google','Abhaya Libre','regular,500,600,700,800','400,500,600,700,800'),
														(290,'google','Julius Sans One','regular','400'),
														(291,'google','Bad Script','regular','400'),
														(292,'google','Nothing You Could Do','regular','400'),
														(293,'google','Alex Brush','regular','400'),
														(294,'google','IM Fell English SC','regular','400'),
														(295,'google','Electrolize','regular','400'),
														(296,'google','Pontano Sans','regular','400'),
														(297,'google','Aclonica','regular','400'),
														(298,'google','Marck Script','regular','400'),
														(299,'google','Tenor Sans','regular','400'),
														(300,'google','Cantata One','regular','400'),
														(301,'google','Sriracha','regular','400'),
														(302,'google','Sarala','regular,700','400,700'),
														(303,'google','Courier Prime','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(304,'google','Red Hat Text','300,regular,500,600,700,300italic,italic,500italic,600italic,700italic','300,400,500,600,700,300-italic,400-italic,500-italic,600-italic,700-italic'),
														(305,'google','Gentium Book Basic','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(306,'google','Allerta','regular','400'),
														(307,'google','Arsenal','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(308,'google','Damion','regular','400'),
														(309,'google','Syncopate','regular,700','400,700'),
														(310,'google','Bubblegum Sans','regular','400'),
														(311,'google','Rufina','regular,700','400,700'),
														(312,'google','Niramit','200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic','200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(313,'google','Commissioner','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(314,'google','Martel Sans','200,300,regular,600,700,800,900','200,300,400,600,700,800,900'),
														(315,'google','Lalezar','regular','400'),
														(316,'google','Gruppo','regular','400'),
														(317,'google','Mandali','regular','400'),
														(318,'google','Mukta Malar','200,300,regular,500,600,700,800','200,300,400,500,600,700,800'),
														(319,'google','Sorts Mill Goudy','regular,italic','400,400-italic'),
														(320,'google','Italianno','regular','400'),
														(321,'google','Shadows Into Light Two','regular','400'),
														(322,'google','Quantico','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(323,'google','Black Ops One','regular','400'),
														(324,'google','Krub','200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic','200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(325,'google','IBM Plex Sans Condensed','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(326,'google','Fredericka the Great','regular','400'),
														(327,'google','Reenie Beanie','regular','400'),
														(328,'google','Overlock','regular,italic,700,700italic,900,900italic','400,400-italic,700,700-italic,900,900italic'),
														(329,'google','Changa One','regular,italic','400,400-italic'),
														(330,'google','Candal','regular','400'),
														(331,'google','Sintony','regular,700','400,700'),
														(332,'google','Basic','regular','400'),
														(333,'google','Fira Mono','regular,500,700','400,500,700'),
														(334,'google','Coda','regular,800','400,800'),
														(335,'google','Rasa','300,regular,500,600,700,300italic,italic,500italic,600italic,700italic','300,400,500,600,700,300-italic,400-italic,500-italic,600-italic,700-italic'),
														(336,'google','Libre Caslon Text','regular,italic,700','400,400-italic,700'),
														(337,'google','Forum','regular','400'),
														(338,'google','Mate SC','regular','400'),
														(339,'google','Chewy','regular','400'),
														(340,'google','PT Serif Caption','regular,italic','400,400-italic'),
														(341,'google','Rubik Mono One','regular','400'),
														(342,'google','Mada','200,300,regular,500,600,700,900','200,300,400,500,600,700,900'),
														(343,'google','Mr Dafoe','regular','400'),
														(344,'google','Baskervville','regular,italic','400,400-italic'),
														(345,'google','Literata','200,300,regular,500,600,700,800,900,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','200,300,400,500,600,700,800,900,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(346,'google','Glegoo','regular,700','400,700'),
														(347,'google','Six Caps','regular','400'),
														(348,'google','Darker Grotesque','300,regular,500,600,700,800,900','300,400,500,600,700,800,900'),
														(349,'google','Caveat Brush','regular','400'),
														(350,'google','Shrikhand','regular','400'),
														(351,'google','Sansita','regular,italic,700,700italic,800,800italic,900,900italic','400,400-italic,700,700-italic,800,800-italic,900,900italic'),
														(352,'google','Spinnaker','regular','400'),
														(353,'google','Castoro','regular,italic','400,400-italic'),
														(354,'google','Antic','regular','400'),
														(355,'google','Black Han Sans','regular','400'),
														(356,'google','Rancho','regular','400'),
														(357,'google','Lexend','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(358,'google','Average','regular','400'),
														(359,'google','Hind Guntur','300,regular,500,600,700','300,400,500,600,700'),
														(360,'google','Encode Sans Condensed','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(361,'google','Holtwood One SC','regular','400'),
														(362,'google','Jaldi','regular,700','400,700'),
														(363,'google','Pinyon Script','regular','400'),
														(364,'google','Markazi Text','regular,500,600,700','400,500,600,700'),
														(365,'google','Yrsa','300,regular,500,600,700,300italic,italic,500italic,600italic,700italic','300,400,500,600,700,300-italic,400-italic,500-italic,600-italic,700-italic'),
														(366,'google','Kreon','300,regular,500,600,700','300,400,500,600,700'),
														(367,'google','Stint Ultra Condensed','regular','400'),
														(368,'google','Lateef','regular','400'),
														(369,'google','Annie Use Your Telescope','regular','400'),
														(370,'google','Blinker','100,200,300,regular,600,700,800,900','100,200,300,400,600,700,800,900'),
														(371,'google','Alegreya Sans SC','100,100italic,300,300italic,regular,italic,500,500italic,700,700italic,800,800italic,900,900italic','100,100-italic,300,300-italic,400,400-italic,500,500-italic,700,700-italic,800,800-italic,900,900italic'),
														(372,'google','Noto Naskh Arabic','regular,500,600,700','400,500,600,700'),
														(373,'google','Voltaire','regular','400'),
														(374,'google','Belgrano','regular','400'),
														(375,'google','Lemonada','300,regular,500,600,700','300,400,500,600,700'),
														(376,'google','Kameron','regular,700','400,700'),
														(377,'google','Telex','regular','400'),
														(378,'google','Enriqueta','regular,500,600,700','400,500,600,700'),
														(379,'google','Anonymous Pro','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(380,'google','Suez One','regular','400'),
														(381,'google','Aldrich','regular','400'),
														(382,'google','Noto Sans Tamil','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(383,'google','Days One','regular','400'),
														(384,'google','Mate','regular,italic','400,400-italic'),
														(385,'google','Michroma','regular','400'),
														(386,'google','Pridi','200,300,regular,500,600,700','200,300,400,500,600,700'),
														(387,'google','Bodoni Moda','regular,500,600,700,800,900,italic,500italic,600italic,700italic,800italic,900italic','400,500,600,700,800,900,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(388,'google','Leckerli One','regular','400'),
														(389,'google','Boogaloo','regular','400'),
														(390,'google','Judson','regular,italic,700','400,400-italic,700'),
														(391,'google','Knewave','regular','400'),
														(392,'google','Cabin Sketch','regular,700','400,700'),
														(393,'google','Palanquin Dark','regular,500,600,700','400,500,600,700'),
														(394,'google','Reem Kufi','regular,500,600,700','400,500,600,700'),
														(395,'google','Allerta Stencil','regular','400'),
														(396,'google','Georama','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(397,'google','Bowlby One SC','regular','400'),
														(398,'google','Just Another Hand','regular','400'),
														(399,'google','Mrs Saint Delafield','regular','400'),
														(400,'google','Norican','regular','400'),
														(401,'google','Covered By Your Grace','regular','400'),
														(402,'google','Racing Sans One','regular','400'),
														(403,'google','Scada','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(404,'google','Niconne','regular','400'),
														(405,'google','Rozha One','regular','400'),
														(406,'google','VT323','regular','400'),
														(407,'google','Bevan','regular,italic','400,400-italic'),
														(408,'google','Londrina Solid','100,300,regular,900','100,300,400,900'),
														(409,'google','Cinzel Decorative','regular,700,900','400,700,900'),
														(410,'google','Graduate','regular','400'),
														(411,'google','Berkshire Swash','regular','400'),
														(412,'google','Laila','300,regular,500,600,700','300,400,500,600,700'),
														(413,'google','Amethysta','regular','400'),
														(414,'google','Charm','regular,700','400,700'),
														(415,'google','Coming Soon','regular','400'),
														(416,'google','Saira Extra Condensed','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(417,'google','Jua','regular','400'),
														(418,'google','Rambla','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(419,'google','Arizonia','regular','400'),
														(420,'google','Alike Angular','regular','400'),
														(421,'google','Herr Von Muellerhoff','regular','400'),
														(422,'google','Kristi','regular','400'),
														(423,'google','Gilda Display','regular','400'),
														(424,'google','Miriam Libre','regular,700','400,700'),
														(425,'google','Copse','regular','400'),
														(426,'google','Epilogue','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(427,'google','Rye','regular','400'),
														(428,'google','Trocchi','regular','400'),
														(429,'google','Caudex','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(430,'google','Alatsi','regular','400'),
														(431,'google','Amita','regular,700','400,700'),
														(432,'google','Share','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(433,'google','Rochester','regular','400'),
														(434,'google','Athiti','200,300,regular,500,600,700','200,300,400,500,600,700'),
														(435,'google','Noto Sans Thai','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(436,'google','Alike','regular','400'),
														(437,'google','Lustria','regular','400'),
														(438,'google','Skranji','regular,700','400,700'),
														(439,'google','Suranna','regular','400'),
														(440,'google','Capriola','regular','400'),
														(441,'google','Mukta Vaani','200,300,regular,500,600,700,800','200,300,400,500,600,700,800'),
														(442,'google','Alegreya SC','regular,italic,500,500italic,700,700italic,800,800italic,900,900italic','400,400-italic,500,500-italic,700,700-italic,800,800-italic,900,900italic'),
														(443,'google','Magra','regular,700','400,700'),
														(444,'google','Oranienbaum','regular','400'),
														(445,'google','K2D','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic'),
														(446,'google','GFS Didot','regular','400'),
														(447,'google','Nanum Brush Script','regular','400'),
														(448,'google','Comic Neue','300,300italic,regular,italic,700,700italic','300,300-italic,400,400-italic,700,700-italic'),
														(449,'google','Carme','regular','400'),
														(450,'google','Nobile','regular,italic,500,500italic,700,700italic','400,400-italic,500,500-italic,700,700-italic'),
														(451,'google','Yesteryear','regular','400'),
														(452,'google','Delius','regular','400'),
														(453,'google','Fauna One','regular','400'),
														(454,'google','Noto Sans Devanagari','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(455,'google','Trirong','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic,900,900italic'),
														(456,'google','Fira Code','300,regular,500,600,700','300,400,500,600,700'),
														(457,'google','Biryani','200,300,regular,600,700,800,900','200,300,400,600,700,800,900'),
														(458,'google','Mallanna','regular','400'),
														(459,'google','Marcellus SC','regular','400'),
														(460,'google','Arbutus Slab','regular','400'),
														(461,'google','Coustard','regular,900','400,900'),
														(462,'google','Merienda One','regular','400'),
														(463,'google','Cedarville Cursive','regular','400'),
														(464,'google','Krona One','regular','400'),
														(465,'google','Seaweed Script','regular','400'),
														(466,'google','Bowlby One','regular','400'),
														(467,'google','Average Sans','regular','400'),
														(468,'google','NTR','regular','400'),
														(469,'google','Nixie One','regular','400'),
														(470,'google','Overpass Mono','300,regular,500,600,700','300,400,500,600,700'),
														(471,'google','Bungee Inline','regular','400'),
														(472,'google','Belleza','regular','400'),
														(473,'google','Contrail One','regular','400'),
														(474,'google','JetBrains Mono','100,200,300,regular,500,600,700,800,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic','100,200,300,400,500,600,700,800,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic'),
														(475,'google','Big Shoulders Display','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(476,'google','Allan','regular,700','400,700'),
														(477,'google','Petit Formal Script','regular','400'),
														(478,'google','Podkova','regular,500,600,700,800','400,500,600,700,800'),
														(479,'google','Corben','regular,700','400,700'),
														(480,'google','Averia Serif Libre','300,300italic,regular,italic,700,700italic','300,300-italic,400,400-italic,700,700-italic'),
														(481,'google','Sunflower','300,500,700','300,500,700'),
														(482,'google','Jockey One','regular','400'),
														(483,'google','Molengo','regular','400'),
														(484,'google','Kosugi','regular','400'),
														(485,'google','Do Hyeon','regular','400'),
														(486,'google','Pattaya','regular','400'),
														(487,'google','Kumbh Sans','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(488,'google','Antonio','100,200,300,regular,500,600,700','100,200,300,400,500,600,700'),
														(489,'google','Aladin','regular','400'),
														(490,'google','Comforter Brush','regular','400'),
														(491,'google','Amiko','regular,600,700','400,600,700'),
														(492,'google','Rosario','300,regular,500,600,700,300italic,italic,500italic,600italic,700italic','300,400,500,600,700,300-italic,400-italic,500-italic,600-italic,700-italic'),
														(493,'google','Wallpoet','regular','400'),
														(494,'google','La Belle Aurore','regular','400'),
														(495,'google','Rammetto One','regular','400'),
														(496,'google','Halant','300,regular,500,600,700','300,400,500,600,700'),
														(497,'google','Trykker','regular','400'),
														(498,'google','Sue Ellen Francisco','regular','400'),
														(499,'google','Cormorant Infant','300,300italic,regular,italic,500,500italic,600,600italic,700,700italic','300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(500,'google','Chonburi','regular','400'),
														(501,'google','Mr De Haviland','regular','400'),
														(502,'google','Ovo','regular','400'),
														(503,'google','Cambay','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(504,'google','Grand Hotel','regular','400'),
														(505,'google','Esteban','regular','400'),
														(506,'google','Calligraffitti','regular','400'),
														(507,'google','Slabo 13px','regular','400'),
														(508,'google','Carrois Gothic','regular','400'),
														(509,'google','Hanuman','100,300,regular,700,900','100,300,400,700,900'),
														(510,'google','Maitree','200,300,regular,500,600,700','200,300,400,500,600,700'),
														(511,'google','BioRhyme','200,300,regular,700,800','200,300,400,700,800'),
														(512,'google','Kite One','regular','400'),
														(513,'google','Spectral SC','200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic','200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic'),
														(514,'google','Cutive Mono','regular','400'),
														(515,'google','Grandstander','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(516,'google','Sniglet','regular,800','400,800'),
														(517,'google','IM Fell DW Pica','regular,italic','400,400-italic'),
														(518,'google','Bellefair','regular','400'),
														(519,'google','Qwigley','regular','400'),
														(520,'google','Encode Sans Semi Condensed','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(521,'google','Manjari','100,regular,700','100,400,700'),
														(522,'google','Oxygen Mono','regular','400'),
														(523,'google','Schoolbell','regular','400'),
														(524,'google','Stardos Stencil','regular,700','400,700'),
														(525,'google','Noto Sans Malayalam','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(526,'google','Libre Barcode 39','regular','400'),
														(527,'google','Kelly Slab','regular','400'),
														(528,'google','Monsieur La Doulaise','regular','400'),
														(529,'google','Marvel','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(530,'google','Gugi','regular','400'),
														(531,'google','Marmelad','regular','400'),
														(532,'google','STIX Two Text','regular,500,600,700,italic,500italic,600italic,700italic','400,500,600,700,400-italic,500-italic,600-italic,700-italic'),
														(533,'google','Poller One','regular','400'),
														(534,'google','Metrophobic','regular','400'),
														(535,'google','Averia Libre','300,300italic,regular,italic,700,700italic','300,300-italic,400,400-italic,700,700-italic'),
														(536,'google','Poly','regular,italic','400,400-italic'),
														(537,'google','Duru Sans','regular','400'),
														(538,'google','Shippori Mincho','regular,500,600,700,800','400,500,600,700,800'),
														(539,'google','Thasadith','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(540,'google','Sofia','regular','400'),
														(541,'google','Atkinson Hyperlegible','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(542,'google','Euphoria Script','regular','400'),
														(543,'google','Nova Mono','regular','400'),
														(544,'google','Harmattan','regular,700','400,700'),
														(545,'google','Dawning of a New Day','regular','400'),
														(546,'google','Convergence','regular','400'),
														(547,'google','Baloo Tamma 2','regular,500,600,700,800','400,500,600,700,800'),
														(548,'google','David Libre','regular,500,700','400,500,700'),
														(549,'google','Brawler','regular','400'),
														(550,'google','Tenali Ramakrishna','regular','400'),
														(551,'google','Radley','regular,italic','400,400-italic'),
														(552,'google','Lemon','regular','400'),
														(553,'google','Gabriela','regular','400'),
														(554,'google','Hepta Slab','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(555,'google','Montez','regular','400'),
														(556,'google','Hi Melody','regular','400'),
														(557,'google','Coda Caption','800','800'),
														(558,'google','Zeyada','regular','400'),
														(559,'google','Bentham','regular','400'),
														(560,'google','Syne','regular,500,600,700,800','400,500,600,700,800'),
														(561,'google','Noto Sans Arabic','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(562,'google','IM Fell English','regular,italic','400,400-italic'),
														(563,'google','Chelsea Market','regular','400'),
														(564,'google','Calistoga','regular','400'),
														(565,'google','Pompiere','regular','400'),
														(566,'google','Bungee Shade','regular','400'),
														(567,'google','Cormorant SC','300,regular,500,600,700','300,400,500,600,700'),
														(568,'google','Goudy Bookletter 1911','regular','400'),
														(569,'google','IM Fell Double Pica','regular,italic','400,400-italic'),
														(570,'google','Kurale','regular','400'),
														(571,'google','Tillana','regular,500,600,700,800','400,500,600,700,800'),
														(572,'google','Inknut Antiqua','300,regular,500,600,700,800,900','300,400,500,600,700,800,900'),
														(573,'google','Proza Libre','regular,italic,500,500italic,600,600italic,700,700italic,800,800italic','400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic'),
														(574,'google','Oxanium','200,300,regular,500,600,700,800','200,300,400,500,600,700,800'),
														(575,'google','Fanwood Text','regular,italic','400,400-italic'),
														(576,'google','Cutive','regular','400'),
														(577,'google','McLaren','regular','400'),
														(578,'google','Quando','regular','400'),
														(579,'google','Grenze Gotisch','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(580,'google','Give You Glory','regular','400'),
														(581,'google','Dokdo','regular','400'),
														(582,'google','Farro','300,regular,500,700','300,400,500,700'),
														(583,'google','Sarpanch','regular,500,600,700,800,900','400,500,600,700,800,900'),
														(584,'google','Noto Sans Telugu','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(585,'google','Megrim','regular','400'),
														(586,'google','Azeret Mono','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(587,'google','UnifrakturMaguntia','regular','400'),
														(588,'google','Limelight','regular','400'),
														(589,'google','ZCOOL QingKe HuangYou','regular','400'),
														(590,'google','Noto Sans Kannada','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(591,'google','Finger Paint','regular','400'),
														(592,'google','Short Stack','regular','400'),
														(593,'google','Happy Monkey','regular','400'),
														(594,'google','Turret Road','200,300,regular,500,700,800','200,300,400,500,700,800'),
														(595,'google','Irish Grover','regular','400'),
														(596,'google','Battambang','100,300,regular,700,900','100,300,400,700,900'),
														(597,'google','Original Surfer','regular','400'),
														(598,'google','Noto Sans Hebrew','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(599,'google','Mansalva','regular','400'),
														(600,'google','Love Ya Like A Sister','regular','400'),
														(601,'google','Expletus Sans','regular,500,600,700,italic,500italic,600italic,700italic','400,500,600,700,400-italic,500-italic,600-italic,700-italic'),
														(602,'google','Emilys Candy','regular','400'),
														(603,'google','Gravitas One','regular','400'),
														(604,'google','Ceviche One','regular','400'),
														(605,'google','Buenard','regular,700','400,700'),
														(606,'google','Livvic','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,900,900italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,900,900italic'),
														(607,'google','Kadwa','regular,700','400,700'),
														(608,'google','Waiting for the Sunrise','regular','400'),
														(609,'google','Gurajada','regular','400'),
														(610,'google','Henny Penny','regular','400'),
														(611,'google','Mirza','regular,500,600,700','400,500,600,700'),
														(612,'google','Andika','regular','400'),
														(613,'google','Oleo Script Swash Caps','regular,700','400,700'),
														(614,'google','Urbanist','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(615,'google','Vast Shadow','regular','400'),
														(616,'google','Atma','300,regular,500,600,700','300,400,500,600,700'),
														(617,'google','Baloo Da 2','regular,500,600,700,800','400,500,600,700,800'),
														(618,'google','Anaheim','regular','400'),
														(619,'google','Oregano','regular,italic','400,400-italic'),
														(620,'google','Freckle Face','regular','400'),
														(621,'google','Della Respira','regular','400'),
														(622,'google','Doppio One','regular','400'),
														(623,'google','Ranchers','regular','400'),
														(624,'google','Homenaje','regular','400'),
														(625,'google','B612 Mono','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(626,'google','Over the Rainbow','regular','400'),
														(627,'google','Major Mono Display','regular','400'),
														(628,'google','Inder','regular','400'),
														(629,'google','Mountains of Christmas','regular,700','400,700'),
														(630,'google','Big Shoulders Text','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(631,'google','Rouge Script','regular','400'),
														(632,'google','Sedgwick Ave','regular','400'),
														(633,'google','Baloo Thambi 2','regular,500,600,700,800','400,500,600,700,800'),
														(634,'google','Share Tech','regular','400'),
														(635,'google','Rakkas','regular','400'),
														(636,'google','Vesper Libre','regular,500,700,900','400,500,700,900'),
														(637,'google','Raleway Dots','regular','400'),
														(638,'google','Noto Sans Oriya','100,regular,700,900','100,400,700,900'),
														(639,'google','Montserrat Subrayada','regular,700','400,700'),
														(640,'google','Federo','regular','400'),
														(641,'google','Cormorant Upright','300,regular,500,600,700','300,400,500,600,700'),
														(642,'google','Galada','regular','400'),
														(643,'google','Vollkorn SC','regular,600,700,900','400,600,700,900'),
														(644,'google','Meddon','regular','400'),
														(645,'google','Orienta','regular','400'),
														(646,'google','Geo','regular,italic','400,400-italic'),
														(647,'google','Katibeh','regular','400'),
														(648,'google','DM Mono','300,300italic,regular,italic,500,500italic','300,300-italic,400,400-italic,500,500-italic'),
														(649,'google','Noto Sans Gujarati','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(650,'google','Walter Turncoat','regular','400'),
														(651,'google','Noto Sans Gurmukhi','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(652,'google','Clicker Script','regular','400'),
														(653,'google','Metamorphous','regular','400'),
														(654,'google','Wendy One','regular','400'),
														(655,'google','Cambo','regular','400'),
														(656,'google','Fondamento','regular,italic','400,400-italic'),
														(657,'google','Fjord One','regular','400'),
														(658,'google','Ledger','regular','400'),
														(659,'google','Faster One','regular','400'),
														(660,'google','ZCOOL XiaoWei','regular','400'),
														(661,'google','Kiwi Maru','300,regular,500','300,400,500'),
														(662,'google','Reggae One','regular','400'),
														(663,'google','Caladea','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(664,'google','Mochiy Pop One','regular','400'),
														(665,'google','Antic Didone','regular','400'),
														(666,'google','Goblin One','regular','400'),
														(667,'google','B612','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(668,'google','RocknRoll One','regular','400'),
														(669,'google','Timmana','regular','400'),
														(670,'google','Fraunces','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(671,'google','Coiny','regular','400'),
														(672,'google','Patrick Hand SC','regular','400'),
														(673,'google','Overlock SC','regular','400'),
														(674,'google','Nova Round','regular','400'),
														(675,'google','Baumans','regular','400'),
														(676,'google','Bellota Text','300,300italic,regular,italic,700,700italic','300,300-italic,400,400-italic,700,700-italic'),
														(677,'google','Lekton','regular,italic,700','400,400-italic,700'),
														(678,'google','Meera Inimai','regular','400'),
														(679,'google','Aref Ruqaa','regular,700','400,700'),
														(680,'google','Ruslan Display','regular','400'),
														(681,'google','Unkempt','regular,700','400,700'),
														(682,'google','Balthazar','regular','400'),
														(683,'google','Notable','regular','400'),
														(684,'google','Noto Sans Bengali','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(685,'google','Road Rage','regular','400'),
														(686,'google','Mouse Memoirs','regular','400'),
														(687,'google','Aguafina Script','regular','400'),
														(688,'google','Shojumaru','regular','400'),
														(689,'google','Chau Philomene One','regular,italic','400,400-italic'),
														(690,'google','Eater','regular','400'),
														(691,'google','Tienne','regular,700,900','400,700,900'),
														(692,'google','Arya','regular,700','400,700'),
														(693,'google','Italiana','regular','400'),
														(694,'google','Montaga','regular','400'),
														(695,'google','Odibee Sans','regular','400'),
														(696,'google','Frijole','regular','400'),
														(697,'google','Sail','regular','400'),
														(698,'google','Numans','regular','400'),
														(699,'google','Goldman','regular,700','400,700'),
														(700,'google','Lexend Zetta','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(701,'google','Ma Shan Zheng','regular','400'),
														(702,'google','Mukta Mahee','200,300,regular,500,600,700,800','200,300,400,500,600,700,800'),
														(703,'google','Amarante','regular','400'),
														(704,'google','Praise','regular','400'),
														(705,'google','Yatra One','regular','400'),
														(706,'google','Port Lligat Sans','regular','400'),
														(707,'google','Baloo Chettan 2','regular,500,600,700,800','400,500,600,700,800'),
														(708,'google','Prosto One','regular','400'),
														(709,'google','Crafty Girls','regular','400'),
														(710,'google','Codystar','300,regular','300,400'),
														(711,'google','Noto Sans Mandaic','regular','400'),
														(712,'google','Encode Sans Expanded','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(713,'google','Headland One','regular','400'),
														(714,'google','Fahkwang','200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic','200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(715,'google','Bubbler One','regular','400'),
														(716,'google','Allison','regular','400'),
														(717,'google','Bilbo Swash Caps','regular','400'),
														(718,'google','Mako','regular','400'),
														(719,'google','Yusei Magic','regular','400'),
														(720,'google','Elsie','regular,900','400,900'),
														(721,'google','Loved by the King','regular','400'),
														(722,'google','Englebert','regular','400'),
														(723,'google','Port Lligat Slab','regular','400'),
														(724,'google','Strait','regular','400'),
														(725,'google','Delius Swash Caps','regular','400'),
														(726,'google','Salsa','regular','400'),
														(727,'google','Artifika','regular','400'),
														(728,'google','Stalemate','regular','400'),
														(729,'google','Ranga','regular,700','400,700'),
														(730,'google','Pavanam','regular','400'),
														(731,'google','Averia Sans Libre','300,300italic,regular,italic,700,700italic','300,300-italic,400,400-italic,700,700-italic'),
														(732,'google','Style Script','regular','400'),
														(733,'google','Sansita Swashed','300,regular,500,600,700,800,900','300,400,500,600,700,800,900'),
														(734,'google','Varta','300,regular,500,600,700','300,400,500,600,700'),
														(735,'google','Nova Square','regular','400'),
														(736,'google','Akronim','regular','400'),
														(737,'google','MuseoModerno','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(738,'google','Dela Gothic One','regular','400'),
														(739,'google','Dynalight','regular','400'),
														(740,'google','Flamenco','300,regular','300,400'),
														(741,'google','Wire One','regular','400'),
														(742,'google','The Nautigal','regular,700','400,700'),
														(743,'google','The Girl Next Door','regular','400'),
														(744,'google','Spicy Rice','regular','400'),
														(745,'google','Life Savers','regular,700,800','400,700,800'),
														(746,'google','Carrois Gothic SC','regular','400'),
														(747,'google','Bilbo','regular','400'),
														(748,'google','Newsreader','200,300,regular,500,600,700,800,200italic,300italic,italic,500italic,600italic,700italic,800italic','200,300,400,500,600,700,800,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic'),
														(749,'google','Kodchasan','200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic','200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(750,'google','Almendra','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(751,'google','Peralta','regular','400'),
														(752,'google','Shalimar','regular','400'),
														(753,'google','Iceland','regular','400'),
														(754,'google','Baloo Bhai 2','regular,500,600,700,800','400,500,600,700,800'),
														(755,'google','Imprima','regular','400'),
														(756,'google','Baloo Paaji 2','regular,500,600,700,800','400,500,600,700,800'),
														(757,'google','Asul','regular,700','400,700'),
														(758,'google','Cantora One','regular','400'),
														(759,'google','Encode Sans Semi Expanded','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(760,'google','Cherry Swash','regular,700','400,700'),
														(761,'google','Fresca','regular','400'),
														(762,'google','League Script','regular','400'),
														(763,'google','Scope One','regular','400'),
														(764,'google','Solway','300,regular,500,700,800','300,400,500,700,800'),
														(765,'google','Just Me Again Down Here','regular','400'),
														(766,'google','Slackey','regular','400'),
														(767,'google','Baloo Tammudu 2','regular,500,600,700,800','400,500,600,700,800'),
														(768,'google','Voces','regular','400'),
														(769,'google','KoHo','200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic','200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic'),
														(770,'google','Mina','regular,700','400,700'),
														(771,'google','Luxurious Roman','regular','400'),
														(772,'google','Nosifer','regular','400'),
														(773,'google','Delius Unicase','regular,700','400,700'),
														(774,'google','Gafata','regular','400'),
														(775,'google','Cherry Cream Soda','regular','400'),
														(776,'google','Libre Barcode 39 Text','regular','400'),
														(777,'google','Vibur','regular','400'),
														(778,'google','Germania One','regular','400'),
														(779,'google','Gaegu','300,regular,700','300,400,700'),
														(780,'google','Neonderthaw','regular','400'),
														(781,'google','Denk One','regular','400'),
														(782,'google','Noto Sans Sinhala','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(783,'google','Cormorant Unicase','300,regular,500,600,700','300,400,500,600,700'),
														(784,'google','Pirata One','regular','400'),
														(785,'google','Tauri','regular','400'),
														(786,'google','Mochiy Pop P One','regular','400'),
														(787,'google','Song Myung','regular','400'),
														(788,'google','Angkor','regular','400'),
														(789,'google','Baloo Bhaina 2','regular,500,600,700,800','400,500,600,700,800'),
														(790,'google','UnifrakturCook','700','700'),
														(791,'google','Prociono','regular','400'),
														(792,'google','Paprika','regular','400'),
														(793,'google','Trade Winds','regular','400'),
														(794,'google','Modak','regular','400'),
														(795,'google','Manuale','300,regular,500,600,700,800,300italic,italic,500italic,600italic,700italic,800italic','300,400,500,600,700,800,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic'),
														(796,'google','Puritan','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(797,'google','Bellota','300,300italic,regular,italic,700,700italic','300,300-italic,400,400-italic,700,700-italic'),
														(798,'google','Charmonman','regular,700','400,700'),
														(799,'google','Shippori Mincho B1','regular,500,600,700,800','400,500,600,700,800'),
														(800,'google','Sonsie One','regular','400'),
														(801,'google','Lily Script One','regular','400'),
														(802,'google','Kranky','regular','400'),
														(803,'google','Chango','regular','400'),
														(804,'google','Mystery Quest','regular','400'),
														(805,'google','Medula One','regular','400'),
														(806,'google','Tomorrow','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic,900,900italic'),
														(807,'google','Engagement','regular','400'),
														(808,'google','Lexend Exa','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(809,'google','Fenix','regular','400'),
														(810,'google','Libre Barcode 39 Extended Text','regular','400'),
														(811,'google','Macondo Swash Caps','regular','400'),
														(812,'google','Spline Sans','300,regular,500,600,700','300,400,500,600,700'),
														(813,'google','MedievalSharp','regular','400'),
														(814,'google','East Sea Dokdo','regular','400'),
														(815,'google','Saira Stencil One','regular','400'),
														(816,'google','Yeon Sung','regular','400'),
														(817,'google','Orelega One','regular','400'),
														(818,'google','Chicle','regular','400'),
														(819,'google','Sarina','regular','400'),
														(820,'google','Habibi','regular','400'),
														(821,'google','Asar','regular','400'),
														(822,'google','Gamja Flower','regular','400'),
														(823,'google','Stoke','300,regular','300,400'),
														(824,'google','Ribeye','regular','400'),
														(825,'google','DotGothic16','regular','400'),
														(826,'google','Fontdiner Swanky','regular','400'),
														(827,'google','Dekko','regular','400'),
														(828,'google','Ramaraja','regular','400'),
														(829,'google','IM Fell French Canon','regular,italic','400,400-italic'),
														(830,'google','Libre Caslon Display','regular','400'),
														(831,'google','Milonga','regular','400'),
														(832,'google','ZCOOL KuaiLe','regular','400'),
														(833,'google','Kulim Park','200,200italic,300,300italic,regular,italic,600,600italic,700,700italic','200,200-italic,300,300-italic,400,400-italic,600,600-italic,700,700-italic'),
														(834,'google','Julee','regular','400'),
														(835,'google','Stylish','regular','400'),
														(836,'google','Besley','regular,500,600,700,800,900,italic,500italic,600italic,700italic,800italic,900italic','400,500,600,700,800,900,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(837,'google','Island Moments','regular','400'),
														(838,'google','Donegal One','regular','400'),
														(839,'google','Stint Ultra Expanded','regular','400'),
														(840,'google','Lovers Quarrel','regular','400'),
														(841,'google','Rosarivo','regular,italic','400,400-italic'),
														(842,'google','Londrina Outline','regular','400'),
														(843,'google','Iceberg','regular','400'),
														(844,'google','Piazzolla','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(845,'google','Ibarra Real Nova','regular,500,600,700,italic,500italic,600italic,700italic','400,500,600,700,400-italic,500-italic,600-italic,700-italic'),
														(846,'google','Glory','100,200,300,regular,500,600,700,800,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic','100,200,300,400,500,600,700,800,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic'),
														(847,'google','Shanti','regular','400'),
														(848,'google','Zilla Slab Highlight','regular,700','400,700'),
														(849,'google','Uncial Antiqua','regular','400'),
														(850,'google','Lakki Reddy','regular','400'),
														(851,'google','Cute Font','regular','400'),
														(852,'google','Rationale','regular','400'),
														(853,'google','Croissant One','regular','400'),
														(854,'google','Poor Story','regular','400'),
														(855,'google','Autour One','regular','400'),
														(856,'google','Farsan','regular','400'),
														(857,'google','Libre Barcode 128','regular','400'),
														(858,'google','Rubik Beastly','regular','400'),
														(859,'google','Sulphur Point','300,regular,700','300,400,700'),
														(860,'google','Simonetta','regular,italic,900,900italic','400,400-italic,900,900italic'),
														(861,'google','Petrona','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(862,'google','Inika','regular,700','400,700'),
														(863,'google','Gotu','regular','400'),
														(864,'google','Nova Flat','regular','400'),
														(865,'google','Condiment','regular','400'),
														(866,'google','Quintessential','regular','400'),
														(867,'google','Jomhuria','regular','400'),
														(868,'google','Text Me One','regular','400'),
														(869,'google','IM Fell DW Pica SC','regular','400'),
														(870,'google','Sumana','regular,700','400,700'),
														(871,'google','Nokora','100,300,regular,700,900','100,300,400,700,900'),
														(872,'google','Akaya Telivigala','regular','400'),
														(873,'google','Noto Nastaliq Urdu','regular,700','400,700'),
														(874,'google','Buda','300','300'),
														(875,'google','Kotta One','regular','400'),
														(876,'google','Averia Gruesa Libre','regular','400'),
														(877,'google','Crushed','regular','400'),
														(878,'google','Big Shoulders Stencil Display','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(879,'google','Ruluko','regular','400'),
														(880,'google','IM Fell French Canon SC','regular','400'),
														(881,'google','New Rocker','regular','400'),
														(882,'google','Cagliostro','regular','400'),
														(883,'google','Khmer','regular','400'),
														(884,'google','Andika New Basic','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(885,'google','Zen Maru Gothic','300,regular,500,700,900','300,400,500,700,900'),
														(886,'google','Underdog','regular','400'),
														(887,'google','Tourney','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(888,'google','Ephesis','regular','400'),
														(889,'google','Rowdies','300,regular,700','300,400,700'),
														(890,'google','Bigelow Rules','regular','400'),
														(891,'google','Chathura','100,300,regular,700,800','100,300,400,700,800'),
														(892,'google','Sancreek','regular','400'),
														(893,'google','Swanky and Moo Moo','regular','400'),
														(894,'google','Kufam','regular,500,600,700,800,900,italic,500italic,600italic,700italic,800italic,900italic','400,500,600,700,800,900,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(895,'google','Vampiro One','regular','400'),
														(896,'google','IM Fell Great Primer','regular,italic','400,400-italic'),
														(897,'google','Gayathri','100,regular,700','100,400,700'),
														(898,'google','Noto Serif Bengali','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(899,'google','Elsie Swash Caps','regular,900','400,900'),
														(900,'google','Piedra','regular','400'),
														(901,'google','Potta One','regular','400'),
														(902,'google','Kumar One','regular','400'),
														(903,'google','Hachi Maru Pop','regular','400'),
														(904,'google','Tulpen One','regular','400'),
														(905,'google','Gorditas','regular,700','400,700'),
														(906,'google','Sree Krushnadevaraya','regular','400'),
														(907,'google','Offside','regular','400'),
														(908,'google','MonteCarlo','regular','400'),
														(909,'google','Margarine','regular','400'),
														(910,'google','Maiden Orange','regular','400'),
														(911,'google','Londrina Shadow','regular','400'),
														(912,'google','Sura','regular,700','400,700'),
														(913,'google','Dongle','300,regular,700','300,400,700'),
														(914,'google','Content','regular,700','400,700'),
														(915,'google','Kantumruy','300,regular,700','300,400,700'),
														(916,'google','Eagle Lake','regular','400'),
														(917,'google','Moul','regular','400'),
														(918,'google','Griffy','regular','400'),
														(919,'google','Inria Serif','300,300italic,regular,italic,700,700italic','300,300-italic,400,400-italic,700,700-italic'),
														(920,'google','Kavivanar','regular','400'),
														(921,'google','Rhodium Libre','regular','400'),
														(922,'google','Sirin Stencil','regular','400'),
														(923,'google','Redressed','regular','400'),
														(924,'google','Ruthie','regular','400'),
														(925,'google','Scheherazade New','regular,700','400,700'),
														(926,'google','Bayon','regular','400'),
														(927,'google','Rum Raisin','regular','400'),
														(928,'google','Ravi Prakash','regular','400'),
														(929,'google','Akaya Kanadaka','regular','400'),
														(930,'google','Dorsa','regular','400'),
														(931,'google','Red Rose','300,regular,500,600,700','300,400,500,600,700'),
														(932,'google','Miniver','regular','400'),
														(933,'google','Sahitya','regular,700','400,700'),
														(934,'google','Noto Sans Mono','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(935,'google','Mrs Sheppards','regular','400'),
														(936,'google','Moon Dance','regular','400'),
														(937,'google','Meie Script','regular','400'),
														(938,'google','Koulen','regular','400'),
														(939,'google','Barrio','regular','400'),
														(940,'google','Wellfleet','regular','400'),
														(941,'google','Shippori Antique','regular','400'),
														(942,'google','Inria Sans','300,300italic,regular,italic,700,700italic','300,300-italic,400,400-italic,700,700-italic'),
														(943,'google','Chilanka','regular','400'),
														(944,'google','Unlock','regular','400'),
														(945,'google','Almendra SC','regular','400'),
														(946,'google','Kaisei Tokumin','regular,500,700,800','400,500,700,800'),
														(947,'google','Readex Pro','200,300,regular,500,600,700','200,300,400,500,600,700'),
														(948,'google','Princess Sofia','regular','400'),
														(949,'google','Monofett','regular','400'),
														(950,'google','Marko One','regular','400'),
														(951,'google','Junge','regular','400'),
														(952,'google','Kavoon','regular','400'),
														(953,'google','Modern Antiqua','regular','400'),
														(954,'google','Train One','regular','400'),
														(955,'google','Libre Barcode 39 Extended','regular','400'),
														(956,'google','Arbutus','regular','400'),
														(957,'google','Spirax','regular','400'),
														(958,'google','Stick','regular','400'),
														(959,'google','Viaoda Libre','regular','400'),
														(960,'google','Metal Mania','regular','400'),
														(961,'google','Zen Old Mincho','regular,700,900','400,700,900'),
														(962,'google','Mogra','regular','400'),
														(963,'google','Gupter','regular,500,700','400,500,700'),
														(964,'google','Risque','regular','400'),
														(965,'google','Felipa','regular','400'),
														(966,'google','Molle','italic','400-italic'),
														(967,'google','Caesar Dressing','regular','400'),
														(968,'google','Jomolhari','regular','400'),
														(969,'google','Beth Ellen','regular','400'),
														(970,'google','Montagu Slab','100,200,300,regular,500,600,700','100,200,300,400,500,600,700'),
														(971,'google','Asset','regular','400'),
														(972,'google','Revalia','regular','400'),
														(973,'google','Single Day','regular','400'),
														(974,'google','WindSong','regular,500','400,500'),
														(975,'google','Ribeye Marrow','regular','400'),
														(976,'google','Brygada 1918','regular,500,600,700,italic,500italic,600italic,700italic','400,500,600,700,400-italic,500-italic,600-italic,700-italic'),
														(977,'google','Lexend Mega','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(978,'google','IBM Plex Sans Thai','100,200,300,regular,500,600,700','100,200,300,400,500,600,700'),
														(979,'google','Zen Dots','regular','400'),
														(980,'google','Jolly Lodger','regular','400'),
														(981,'google','Diplomata','regular','400'),
														(982,'google','Linden Hill','regular,italic','400,400-italic'),
														(983,'google','Galdeano','regular','400'),
														(984,'google','Bigshot One','regular','400'),
														(985,'google','Romanesco','regular','400'),
														(986,'google','Girassol','regular','400'),
														(987,'google','Smythe','regular','400'),
														(988,'google','Diplomata SC','regular','400'),
														(989,'google','Joti One','regular','400'),
														(990,'google','Noto Sans Georgian','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(991,'google','Zen Antique','regular','400'),
														(992,'google','Lancelot','regular','400'),
														(993,'google','Fascinate Inline','regular','400'),
														(994,'google','Zen Kaku Gothic New','300,regular,500,700,900','300,400,500,700,900'),
														(995,'google','Hanalei Fill','regular','400'),
														(996,'google','Dangrek','regular','400'),
														(997,'google','Source Sans 3','200,300,regular,500,600,700,800,900,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','200,300,400,500,600,700,800,900,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(998,'google','Lexend Giga','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(999,'google','Devonshire','regular','400'),
														(1000,'google','Siemreap','regular','400'),
														(1001,'google','Noto Serif Display','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(1002,'google','Glass Antiqua','regular','400'),
														(1003,'google','Emblema One','regular','400'),
														(1004,'google','Srisakdi','regular,700','400,700'),
														(1005,'google','Bahiana','regular','400'),
														(1006,'google','Galindo','regular','400'),
														(1007,'google','Peddana','regular','400'),
														(1008,'google','Grenze','100,100italic,200,200italic,300,300italic,regular,italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic','100,100-italic,200,200-italic,300,300-italic,400,400-italic,500,500-italic,600,600-italic,700,700-italic,800,800-italic,900,900italic'),
														(1009,'google','Gowun Batang','regular,700','400,700'),
														(1010,'google','Truculenta','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1011,'google','Bona Nova','regular,italic,700','400,400-italic,700'),
														(1012,'google','Gwendolyn','regular,700','400,700'),
														(1013,'google','Kumar One Outline','regular','400'),
														(1014,'google','Ewert','regular','400'),
														(1015,'google','Almendra Display','regular','400'),
														(1016,'google','Oldenburg','regular','400'),
														(1017,'google','GFS Neohellenic','regular,italic,700,700italic','400,400-italic,700,700-italic'),
														(1018,'google','Gemunu Libre','200,300,regular,500,600,700,800','200,300,400,500,600,700,800'),
														(1019,'google','Plaster','regular','400'),
														(1020,'google','Londrina Sketch','regular','400'),
														(1021,'google','Jim Nightshade','regular','400'),
														(1022,'google','Odor Mean Chey','regular','400'),
														(1023,'google','Snippet','regular','400'),
														(1024,'google','Barriecito','regular','400'),
														(1025,'google','Jacques Francois Shadow','regular','400'),
														(1026,'google','Kirang Haerang','regular','400'),
														(1027,'google','Atomic Age','regular','400'),
														(1028,'google','Zen Kaku Gothic Antique','300,regular,500,700,900','300,400,500,700,900'),
														(1029,'google','Dr Sugiyama','regular','400'),
														(1030,'google','Noto Sans Myanmar','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1031,'google','Flavors','regular','400'),
														(1032,'google','Libre Barcode 128 Text','regular','400'),
														(1033,'google','Karantina','300,regular,700','300,400,700'),
														(1034,'google','Festive','regular','400'),
														(1035,'google','Zhi Mang Xing','regular','400'),
														(1036,'google','Keania One','regular','400'),
														(1037,'google','IM Fell Great Primer SC','regular','400'),
														(1038,'google','Zen Loop','regular,italic','400,400-italic'),
														(1039,'google','Jacques Francois','regular','400'),
														(1040,'google','Purple Purse','regular','400'),
														(1041,'google','Licorice','regular','400'),
														(1042,'google','Klee One','regular,600','400,600'),
														(1043,'google','Chela One','regular','400'),
														(1044,'google','Noto Sans Armenian','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1045,'google','Big Shoulders Stencil Text','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1046,'google','Otomanopee One','regular','400'),
														(1047,'google','Bahianita','regular','400'),
														(1048,'google','IBM Plex Sans Arabic','100,200,300,regular,500,600,700','100,200,300,400,500,600,700'),
														(1049,'google','Chenla','regular','400'),
														(1050,'google','Nova Slim','regular','400'),
														(1051,'google','Rampart One','regular','400'),
														(1052,'google','Metal','regular','400'),
														(1053,'google','Kaisei Opti','regular,500,700','400,500,700'),
														(1054,'google','Miss Fajardose','regular','400'),
														(1055,'google','Macondo','regular','400'),
														(1056,'google','Preahvihear','regular','400'),
														(1057,'google','Fuzzy Bubbles','regular,700','400,700'),
														(1058,'google','Combo','regular','400'),
														(1059,'google','Bungee Outline','regular','400'),
														(1060,'google','Yomogi','regular','400'),
														(1061,'google','Corinthia','regular,700','400,700'),
														(1062,'google','Freehand','regular','400'),
														(1063,'google','Vibes','regular','400'),
														(1064,'google','Kaisei Decol','regular,500,700','400,500,700'),
														(1065,'google','Kdam Thmor','regular','400'),
														(1066,'google','Erica One','regular','400'),
														(1067,'google','Sunshiney','regular','400'),
														(1068,'google','Noto Sans Symbols','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1069,'google','Texturina','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(1070,'google','Mr Bedfort','regular','400'),
														(1071,'google','Butterfly Kids','regular','400'),
														(1072,'google','Black And White Picture','regular','400'),
														(1073,'google','Fascinate','regular','400'),
														(1074,'google','Oooh Baby','regular','400'),
														(1075,'google','Passero One','regular','400'),
														(1076,'google','Trochut','regular,italic,700','400,400-italic,700'),
														(1077,'google','Qwitcher Grypen','regular,700','400,700'),
														(1078,'google','Hanalei','regular','400'),
														(1079,'google','Sedgwick Ave Display','regular','400'),
														(1080,'google','Bokor','regular','400'),
														(1081,'google','Dhurjati','regular','400'),
														(1082,'google','Hahmlet','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1083,'google','Bakbak One','regular','400'),
														(1084,'google','Nerko One','regular','400'),
														(1085,'google','Liu Jian Mao Cao','regular','400'),
														(1086,'google','IBM Plex Sans KR','100,200,300,regular,500,600,700','100,200,300,400,500,600,700'),
														(1087,'google','Andada Pro','regular,500,600,700,800,italic,500italic,600italic,700italic,800italic','400,500,600,700,800,400-italic,500-italic,600-italic,700-italic,800-italic'),
														(1088,'google','Bungee Hairline','regular','400'),
														(1089,'google','Federant','regular','400'),
														(1090,'google','Fruktur','regular','400'),
														(1091,'google','Suwannaphum','100,300,regular,700,900','100,300,400,700,900'),
														(1092,'google','BioRhyme Expanded','200,300,regular,700,800','200,300,400,700,800'),
														(1093,'google','Bonbon','regular','400'),
														(1094,'google','IM Fell Double Pica SC','regular','400'),
														(1095,'google','Snowburst One','regular','400'),
														(1096,'google','Benne','regular','400'),
														(1097,'google','Hina Mincho','regular','400'),
														(1098,'google','Astloch','regular,700','400,700'),
														(1099,'google','Seymour One','regular','400'),
														(1100,'google','Lacquer','regular','400'),
														(1101,'google','Kaisei HarunoUmi','regular,500,700','400,500,700'),
														(1102,'google','Geostar Fill','regular','400'),
														(1103,'google','Yaldevi','200,300,regular,500,600,700','200,300,400,500,600,700'),
														(1104,'google','Gidugu','regular','400'),
														(1105,'google','Aubrey','regular','400'),
														(1106,'google','Lexend Tera','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1107,'google','Fuggles','regular','400'),
														(1108,'google','Syne Mono','regular','400'),
														(1109,'google','Supermercado One','regular','400'),
														(1110,'google','Stalinist One','regular','400'),
														(1111,'google','Smokum','regular','400'),
														(1112,'google','Long Cang','regular','400'),
														(1113,'google','Syne Tactile','regular','400'),
														(1114,'google','Twinkle Star','regular','400'),
														(1115,'google','Ruge Boogie','regular','400'),
														(1116,'google','Taprom','regular','400'),
														(1117,'google','Noto Sans Tamil Supplement','regular','400'),
														(1118,'google','Miltonian Tattoo','regular','400'),
														(1119,'google','Butcherman','regular','400'),
														(1120,'google','Gowun Dodum','regular','400'),
														(1121,'google','Suravaram','regular','400'),
														(1122,'google','Carattere','regular','400'),
														(1123,'google','Moulpali','regular','400'),
														(1124,'google','Alumni Sans','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(1125,'google','Nova Cut','regular','400'),
														(1126,'google','Sofadi One','regular','400'),
														(1127,'google','Vujahday Script','regular','400'),
														(1128,'google','Sevillana','regular','400'),
														(1129,'google','Miltonian','regular','400'),
														(1130,'google','New Tegomin','regular','400'),
														(1131,'google','Nova Oval','regular','400'),
														(1132,'google','Zen Kurenaido','regular','400'),
														(1133,'google','Noto Serif Tamil','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(1134,'google','Encode Sans SC','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1135,'google','Nova Script','regular','400'),
														(1136,'google','Noto Sans Gothic','regular','400'),
														(1137,'google','Murecho','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1138,'google','Noto Serif Malayalam','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1139,'google','Gluten','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1140,'google','Comforter','regular','400'),
														(1141,'google','Baloo Bhaijaan 2','regular,500,600,700,800','400,500,600,700,800'),
														(1142,'google','Zen Antique Soft','regular','400'),
														(1143,'google','Birthstone','regular','400'),
														(1144,'google','Mea Culpa','regular','400'),
														(1145,'google','Source Serif 4','200,300,regular,500,600,700,800,900,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','200,300,400,500,600,700,800,900,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(1146,'google','M PLUS 1','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1147,'google','Waterfall','regular','400'),
														(1148,'google','Stick No Bills','200,300,regular,500,600,700,800','200,300,400,500,600,700,800'),
														(1149,'google','Big Shoulders Inline Text','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1150,'google','Love Light','regular','400'),
														(1151,'google','Libre Barcode EAN13 Text','regular','400'),
														(1152,'google','Imbue','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1153,'google','Fasthand','regular','400'),
														(1154,'google','Mohave','300,regular,500,600,700,300italic,italic,500italic,600italic,700italic','300,400,500,600,700,300-italic,400-italic,500-italic,600-italic,700-italic'),
														(1155,'google','Luxurious Script','regular','400'),
														(1156,'google','Lexend Peta','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1157,'google','Langar','regular','400'),
														(1158,'google','Ballet','regular','400'),
														(1159,'google','Bonheur Royale','regular','400'),
														(1160,'google','Ole','regular','400'),
														(1161,'google','Estonia','regular','400'),
														(1162,'google','Genos','100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic','100,200,300,400,500,600,700,800,900,100-italic,200-italic,300-italic,400-italic,500-italic,600-italic,700-italic,800-italic,900italic'),
														(1163,'google','Moo Lah Lah','regular','400'),
														(1164,'google','Qahiri','regular','400'),
														(1165,'google','Birthstone Bounce','regular,500','400,500'),
														(1166,'google','Kenia','regular','400'),
														(1167,'google','Imperial Script','regular','400'),
														(1168,'google','Yuji Syuku','regular','400'),
														(1169,'google','Kolker Brush','regular','400'),
														(1170,'google','Xanh Mono','regular,italic','400,400-italic'),
														(1171,'google','Smooch','regular','400'),
														(1172,'google','M PLUS 2','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1173,'google','Geostar','regular','400'),
														(1174,'google','Hurricane','regular','400'),
														(1175,'google','Trispace','100,200,300,regular,500,600,700,800','100,200,300,400,500,600,700,800'),
														(1176,'google','Inspiration','regular','400'),
														(1177,'google','Petemoss','regular','400'),
														(1178,'google','M PLUS 1 Code','100,200,300,regular,500,600,700','100,200,300,400,500,600,700'),
														(1179,'google','Meow Script','regular','400'),
														(1180,'google','Zen Tokyo Zoo','regular','400'),
														(1181,'google','Warnes','regular','400'),
														(1182,'google','Noto Serif Thai','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1183,'google','Are You Serious','regular','400'),
														(1184,'google','Shippori Antique B1','regular','400'),
														(1185,'google','Big Shoulders Inline Display','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1186,'google','Grechen Fuemen','regular','400'),
														(1187,'google','Noto Serif Sinhala','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1188,'google','Flow Circular','regular','400'),
														(1189,'google','Shizuru','regular','400'),
														(1190,'google','Cherish','regular','400'),
														(1191,'google','Noto Sans Symbols 2','regular','400'),
														(1192,'google','Flow Rounded','regular','400'),
														(1193,'google','Kings','regular','400'),
														(1194,'google','Noto Serif Georgian','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1195,'google','Fleur De Leah','regular','400'),
														(1196,'google','Noto Sans Coptic','regular','400'),
														(1197,'google','Flow Block','regular','400'),
														(1198,'google','Yuji Boku','regular','400'),
														(1199,'google','Grey Qo','regular','400'),
														(1200,'google','Noto Sans Lao','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1201,'google','Puppies Play','regular','400'),
														(1202,'google','Red Hat Mono','300,regular,500,600,700,300italic,italic,500italic,600italic,700italic','300,400,500,600,700,300-italic,400-italic,500-italic,600-italic,700-italic'),
														(1203,'google','IBM Plex Sans Hebrew','100,200,300,regular,500,600,700','100,200,300,400,500,600,700'),
														(1204,'google','Gideon Roman','regular','400'),
														(1205,'google','Yuji Mai','regular','400'),
														(1206,'google','Koh Santepheap','100,300,regular,700,900','100,300,400,700,900'),
														(1207,'google','Oi','regular','400'),
														(1208,'google','Passions Conflict','regular','400'),
														(1209,'google','Sassy Frass','regular','400'),
														(1210,'google','IBM Plex Sans Thai Looped','100,200,300,regular,500,600,700','100,200,300,400,500,600,700'),
														(1211,'google','Noto Sans Tai Viet','regular','400'),
														(1212,'google','Noto Serif Armenian','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1213,'google','Palette Mosaic','regular','400'),
														(1214,'google','Rock 3D','regular','400'),
														(1215,'google','Uchen','regular','400'),
														(1216,'google','Noto Serif Devanagari','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1217,'google','M PLUS Code Latin','100,200,300,regular,500,600,700','100,200,300,400,500,600,700'),
														(1218,'google','Explora','regular','400'),
														(1219,'google','Noto Sans Deseret','regular','400'),
														(1220,'google','IBM Plex Sans Devanagari','100,200,300,regular,500,600,700','100,200,300,400,500,600,700'),
														(1221,'google','Caramel','regular','400'),
														(1222,'google','Noto Sans Khmer','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1223,'google','Yuji Hentaigana Akari','regular','400'),
														(1224,'google','Yuji Hentaigana Akebono','regular','400'),
														(1225,'google','Noto Serif Ethiopic','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1226,'google','Redacted Script','300,regular,700','300,400,700'),
														(1227,'google','Noto Serif Khmer','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1228,'google','Noto Sans Javanese','regular,700','400,700'),
														(1229,'google','Noto Sans Multani','regular','400'),
														(1230,'google','Noto Sans Thai Looped','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1231,'google','Noto Sans N Ko','regular','400'),
														(1232,'google','Noto Sans Brahmi','regular','400'),
														(1233,'google','Noto Sans Imperial Aramaic','regular','400'),
														(1234,'google','Noto Sans Tagalog','regular','400'),
														(1235,'google','Noto Sans Tai Tham','regular,500,600,700','400,500,600,700'),
														(1236,'google','Noto Sans Cherokee','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1237,'google','Noto Sans Caucasian Albanian','regular','400'),
														(1238,'google','Noto Serif Lao','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1239,'google','Redacted','regular','400'),
														(1240,'google','Noto Sans Old Turkic','regular','400'),
														(1241,'google','Noto Sans Ol Chiki','regular,500,600,700','400,500,600,700'),
														(1242,'google','Noto Serif Kannada','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1243,'google','Noto Serif Hebrew','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1244,'google','Noto Rashi Hebrew','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1245,'google','Noto Sans Thaana','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1246,'google','Noto Serif Gujarati','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1247,'google','Noto Serif Telugu','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1248,'google','Noto Serif Myanmar','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1249,'google','Noto Sans Cham','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1250,'google','Noto Sans Canadian Aboriginal','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1251,'google','Noto Sans Saurashtra','regular','400'),
														(1252,'google','Noto Sans Meetei Mayek','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1253,'google','Noto Sans Tai Le','regular','400'),
														(1254,'google','Noto Sans Anatolian Hieroglyphs','regular','400'),
														(1255,'google','Noto Serif Tibetan','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1256,'google','Noto Music','regular','400'),
														(1257,'google','Noto Serif Gurmukhi','100,200,300,regular,500,600,700,800,900','100,200,300,400,500,600,700,800,900'),
														(1258,'google','Noto Sans Balinese','regular,500,600,700','400,500,600,700'),
														(1259,'google','Noto Sans Osmanya','regular','400'),
														(1260,'google','Noto Sans Sora Sompeng','regular,500,600,700','400,500,600,700'),
														(1261,'google','Noto Sans Indic Siyaq Numbers','regular','400'),
														(1262,'google','Noto Serif Yezidi','regular,500,600,700','400,500,600,700'),
														(1263,'google','Noto Sans Old Italic','regular','400'),
														(1264,'google','Noto Serif Nyiakeng Puachue Hmong','regular,500,600,700','400,500,600,700'),
														(1265,'google','Noto Sans Mongolian','regular','400'),
														(1266,'google','Noto Sans Adlam','regular,500,600,700','400,500,600,700'),
														(1267,'google','Noto Sans Carian','regular','400'),
														(1268,'google','Noto Sans Adlam Unjoined','regular,500,600,700','400,500,600,700'),
														(1269,'google','Noto Sans Tifinagh','regular','400'),
														(1270,'google','Noto Sans Sundanese','regular,500,600,700','400,500,600,700'),
														(1271,'google','Noto Sans Egyptian Hieroglyphs','regular','400'),
														(1272,'google','Noto Sans Medefaidrin','regular,500,600,700','400,500,600,700'),
														(1273,'google','Noto Sans Kayah Li','regular,500,600,700','400,500,600,700'),
														(1274,'google','Noto Sans Cypriot','regular','400'),
														(1275,'google','Noto Sans Bamum','regular,500,600,700','400,500,600,700'),
														(1276,'google','Noto Sans Grantha','regular','400'),
														(1277,'google','Noto Sans Inscriptional Pahlavi','regular','400'),
														(1278,'google','Noto Sans Hanifi Rohingya','regular,500,600,700','400,500,600,700'),
														(1279,'google','Noto Sans Linear B','regular','400'),
														(1280,'google','Noto Sans Lisu','regular,500,600,700','400,500,600,700'),
														(1281,'google','Noto Sans Yi','regular','400'),
														(1282,'google','Noto Sans Old Hungarian','regular','400'),
														(1283,'google','Noto Sans Psalter Pahlavi','regular','400'),
														(1284,'google','Noto Sans Samaritan','regular','400'),
														(1285,'google','Noto Sans Inscriptional Parthian','regular','400'),
														(1286,'google','Noto Sans Avestan','regular','400'),
														(1287,'google','Noto Sans Syriac','100,regular,900','100,400,900'),
														(1288,'google','Noto Sans Hanunoo','regular','400'),
														(1289,'google','Noto Sans Old South Arabian','regular','400'),
														(1290,'google','Noto Sans Kaithi','regular','400'),
														(1291,'google','Noto Sans Old North Arabian','regular','400'),
														(1292,'google','Noto Serif Balinese','regular','400'),
														(1293,'google','Noto Sans Tagbanwa','regular','400'),
														(1294,'google','Noto Sans Old Persian','regular','400'),
														(1295,'google','Noto Sans Mayan Numerals','regular','400'),
														(1296,'google','Noto Sans Batak','regular','400'),
														(1297,'google','Noto Sans Mahajani','regular','400'),
														(1298,'google','Noto Sans Newa','regular','400'),
														(1299,'google','Noto Serif Grantha','regular','400'),
														(1300,'google','Noto Serif Dogra','regular','400'),
														(1301,'google','Noto Sans Cuneiform','regular','400'),
														(1302,'google','Noto Serif Ahom','regular','400'),
														(1303,'google','Noto Sans Math','regular','400'),
														(1304,'google','Noto Serif Tangut','regular','400'),
														(1305,'google','Noto Sans Phoenician','regular','400'),
														(1306,'google','Noto Sans Lycian','regular','400'),
														(1307,'google','Noto Sans Buginese','regular','400'),
														(1308,'google','Noto Sans New Tai Lue','regular','400'),
														(1309,'google','Noto Sans Limbu','regular','400'),
														(1310,'google','Noto Sans Siddham','regular','400'),
														(1311,'google','Noto Sans Tirhuta','regular','400'),
														(1312,'google','Noto Sans Palmyrene','regular','400'),
														(1313,'google','Noto Sans Zanabazar Square','regular','400'),
														(1314,'google','Noto Sans Elbasan','regular','400'),
														(1315,'google','Noto Sans Runic','regular','400'),
														(1316,'google','Noto Sans Chakma','regular','400'),
														(1317,'google','Noto Sans Bassa Vah','regular','400'),
														(1318,'google','Noto Sans Masaram Gondi','regular','400'),
														(1319,'google','Noto Traditional Nushu','regular','400'),
														(1320,'google','Noto Sans Marchen','regular','400'),
														(1321,'google','Noto Sans Glagolitic','regular','400'),
														(1322,'google','Noto Sans Syloti Nagri','regular','400'),
														(1323,'google','Noto Sans Rejang','regular','400'),
														(1324,'google','Noto Sans Kharoshthi','regular','400'),
														(1325,'google','Noto Sans Duployan','regular','400'),
														(1326,'google','Noto Sans Linear A','regular','400'),
														(1327,'google','Noto Sans Soyombo','regular','400'),
														(1328,'google','Noto Sans Warang Citi','regular','400'),
														(1329,'google','Noto Sans Mro','regular','400'),
														(1330,'google','Noto Sans Meroitic','regular','400'),
														(1331,'google','Noto Sans Old Permic','regular','400'),
														(1332,'google','Noto Sans Lydian','regular','400'),
														(1333,'google','Noto Sans Vai','regular','400'),
														(1334,'google','Noto Sans Miao','regular','400'),
														(1335,'google','Noto Sans Pau Cin Hau','regular','400'),
														(1336,'google','Noto Sans Gunjala Gondi','regular','400'),
														(1337,'google','Noto Sans Old Sogdian','regular','400'),
														(1338,'google','Noto Sans Sogdian','regular','400'),
														(1339,'google','Noto Sans Hatran','regular','400'),
														(1340,'google','Noto Sans Wancho','regular','400'),
														(1341,'google','Noto Sans Nushu','regular','400'),
														(1342,'google','Noto Sans Buhid','regular','400'),
														(1343,'google','Noto Sans Bhaiksuki','regular','400'),
														(1344,'google','Noto Sans Lepcha','regular','400'),
														(1345,'google','Noto Sans Sharada','regular','400'),
														(1346,'google','Noto Sans Ugaritic','regular','400'),
														(1347,'google','Noto Sans Pahawh Hmong','regular','400'),
														(1348,'google','Noto Sans Khojki','regular','400'),
														(1349,'google','Noto Sans Shavian','regular','400'),
														(1350,'google','Noto Sans Khudawadi','regular','400'),
														(1351,'google','Noto Sans Manichaean','regular','400'),
														(1352,'google','Noto Sans Nabataean','regular','400'),
														(1353,'google','Noto Sans Modi','regular','400'),
														(1354,'google','Noto Sans Phags Pa','regular','400'),
														(1355,'google','Noto Sans Osage','regular','400'),
														(1356,'google','Noto Sans Elymaic','regular','400'),
														(1357,'google','Noto Sans Takri','regular','400'),
														(1358,'google','Noto Sans Ogham','regular','400');";
		
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_settings_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."settings` (
												  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
												  `smtp_enable` tinyint(1) NOT NULL DEFAULT '0',
												  `smtp_host` varchar(255) NOT NULL DEFAULT 'localhost',
												  `smtp_port` int(11) NOT NULL DEFAULT '25',
												  `smtp_auth` tinyint(1) NOT NULL DEFAULT '0',
												  `smtp_username` varchar(255) DEFAULT NULL,
												  `smtp_password` varchar(255) DEFAULT NULL,
												  `smtp_secure` tinyint(1) NOT NULL DEFAULT '0',
												  `upload_dir` varchar(255) NOT NULL DEFAULT './data',
												  `data_dir` varchar(255) NOT NULL DEFAULT './data',
												  `default_from_name` varchar(255) NOT NULL DEFAULT 'MachForm',
												  `default_from_email` varchar(255) DEFAULT NULL,
												  `default_form_theme_id` int(11) NOT NULL DEFAULT '0',
												  `base_url` varchar(255) DEFAULT NULL,
												  `form_manager_max_rows` int(11) DEFAULT '10',
												  `admin_image_url` varchar(255) DEFAULT NULL,
												  `disable_machform_link` int(1) DEFAULT '0',
												  `disable_pdf_link` int(1) DEFAULT '0',
												  `customer_id` varchar(100) DEFAULT NULL,
												  `customer_name` varchar(100) DEFAULT NULL,
												  `license_key` varchar(50) DEFAULT NULL,
												  `machform_version` varchar(10) NOT NULL DEFAULT '3.0',
												  `admin_theme` varchar(11) DEFAULT NULL,
												  `enforce_tsv` int(1) NOT NULL DEFAULT '0',
												  `enable_ip_restriction` int(1) NOT NULL DEFAULT '0',
												  `ip_whitelist` text,
												  `enable_account_locking` int(1) NOT NULL DEFAULT '0',
												  `account_lock_period` int(11) NOT NULL DEFAULT '30',
												  `account_lock_max_attempts` int(11) NOT NULL DEFAULT '6',
												  `recaptcha_site_key` varchar(255) DEFAULT NULL,
												  `recaptcha_secret_key` varchar(255) DEFAULT NULL,
												  `googleapi_clientid` varchar(255) DEFAULT NULL,
  												  `googleapi_clientsecret` varchar(255) DEFAULT NULL,
												  `ldap_enable` tinyint(1) NOT NULL DEFAULT '0',
												  `ldap_type` varchar(11) NOT NULL DEFAULT 'ad' COMMENT 'ad,openldap',
												  `ldap_host` varchar(255) DEFAULT NULL,
												  `ldap_port` int(11) NOT NULL DEFAULT '389',
												  `ldap_encryption` varchar(11) NOT NULL DEFAULT 'none' COMMENT 'none,ssl,tls',
												  `ldap_basedn` varchar(255) DEFAULT NULL,
												  `ldap_account_suffix` varchar(100) DEFAULT NULL,
												  `ldap_required_group` varchar(255) DEFAULT NULL,
												  `ldap_exclusive` tinyint(1) NOT NULL DEFAULT '0',
												  `timezone` varchar(100) DEFAULT NULL,
												  `enable_data_retention` tinyint(1) NOT NULL DEFAULT '0',
  												  `data_retention_period` int(11) NOT NULL DEFAULT '38' COMMENT 'months',
  												  `captcha_public_key` text,
												  `captcha_private_key` text,
												  PRIMARY KEY (`id`)
												) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function populate_ap_settings_table($dbh,$options){
		$default_from_email 	= $options['default_from_email'];
		$machform_base_url  	= $options['machform_base_url'];
		$new_machform_version 	= $options['new_machform_version'];
		$license_key 			= $options['license_key'];
		$upload_dir 			= $options['upload_dir']; 

		$query = "INSERT INTO `".MF_TABLE_PREFIX."settings` (`id`, 
																`smtp_enable`, 
																`smtp_host`, 
																`smtp_port`, 
																`smtp_auth`, 
																`smtp_username`, 
																`smtp_password`, 
																`smtp_secure`, 
																`upload_dir`, 
																`data_dir`, 
																`default_from_name`, 
																`default_from_email`, 
																`base_url`, 
																`form_manager_max_rows`, 
																`admin_image_url`, 
																`disable_machform_link`,
																`license_key`,
																`machform_version`)
														VALUES
																(1,
																 0,
																'localhost',
																25,
																0,
																'',
																'',
																0,
																'{$upload_dir}',
																'./data',
																'MachForm',
																'{$default_from_email}',
																'{$machform_base_url}',
																10,
																'',
																0,
																'{$license_key}',
																'{$new_machform_version}');";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_users_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."users` (
												   	  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
													  `user_email` varchar(255) NOT NULL DEFAULT '',
													  `user_password` varchar(255) NOT NULL DEFAULT '',
													  `user_fullname` varchar(255) NOT NULL DEFAULT '',
													  `user_admin_theme` varchar(11) DEFAULT NULL,
													  `reset_hash` varchar(100) DEFAULT NULL,
  													  `reset_date` datetime DEFAULT NULL,
													  `priv_administer` tinyint(1) NOT NULL DEFAULT '0',
													  `priv_new_forms` tinyint(1) NOT NULL DEFAULT '0',
													  `priv_new_themes` tinyint(1) NOT NULL DEFAULT '0',
													  `last_login_date` datetime DEFAULT NULL,
													  `last_ip_address` varchar(15) DEFAULT '',
													  `login_attempt_date` datetime DEFAULT NULL,
													  `login_attempt_count` int(11) NOT NULL DEFAULT '0',
													  `cookie_hash` varchar(255) DEFAULT '',
													  `tsv_enable` int(1) NOT NULL DEFAULT '0',
													  `tsv_secret` varchar(16) DEFAULT NULL,
													  `tsv_code_log` varchar(100) DEFAULT NULL,
													  `folders_pinned` tinyint(1) NOT NULL DEFAULT '0',
													  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 - deleted; 1 - active; 2 - suspended',
													  PRIMARY KEY (`user_id`)
												) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function populate_ap_users_table($dbh,$options){

		$admin_username 	   = $options['admin_username'];
		$default_password_hash = $options['default_password_hash'];

		$query = "INSERT INTO `".MF_TABLE_PREFIX."users` (`user_id`, 
																`user_email`, 
																`user_password`, 
																`user_fullname`, 
																`priv_administer`, 
																`priv_new_forms`, 
																`priv_new_themes`, 
																`status`)
														VALUES
																(1,
																'{$admin_username}',
																'{$default_password_hash}',
																'Administrator',
																1,
																1,
																1,
																1);";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function populate_ap_folders_table($dbh){

		$query = "INSERT INTO 
								`".MF_TABLE_PREFIX."folders`( 
											`user_id`, 
											`folder_id`, 
											`folder_position`, 
											`folder_name`, 
											`folder_selected`, 
											`rule_all_any`) 
						  VALUES (1, 1, 1, 'All Forms', 1, 'all');";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_permissions_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."permissions` (
												  `form_id` bigint(11) unsigned NOT NULL,
												  `user_id` int(11) unsigned NOT NULL,
												  `edit_form` tinyint(1) NOT NULL DEFAULT '0',
												  `edit_report` tinyint(1) NOT NULL DEFAULT '0',
												  `edit_entries` tinyint(1) NOT NULL DEFAULT '0',
												  `view_entries` tinyint(1) NOT NULL DEFAULT '0',
												  PRIMARY KEY (`form_id`,`user_id`)
												) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_entries_preferences_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."entries_preferences` (
												      `form_id` int(11) NOT NULL,
													  `user_id` int(11) NOT NULL,
													  `entries_sort_by` varchar(100) NOT NULL DEFAULT 'id-desc',
													  `entries_enable_filter` int(1) NOT NULL DEFAULT '0',
													  `entries_filter_type` varchar(5) NOT NULL DEFAULT 'all' COMMENT 'all or any',
													  `entries_incomplete_sort_by` varchar(100) NOT NULL DEFAULT 'id-desc',
													  `entries_incomplete_enable_filter` int(1) NOT NULL DEFAULT '0',
													  `entries_incomplete_filter_type` varchar(5) NOT NULL DEFAULT 'all' COMMENT 'all or any'
												) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}
	
	function create_ap_form_locks_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_locks` (
												  `form_id` int(11) NOT NULL,
												  `user_id` int(11) NOT NULL,
												  `lock_date` datetime NOT NULL
												) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_field_logic_elements_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."field_logic_elements` (
																		  `form_id` int(11) NOT NULL,
																		  `element_id` int(11) NOT NULL,
																		  `rule_show_hide` varchar(4) NOT NULL DEFAULT 'show',
																		  `rule_all_any` varchar(3) NOT NULL DEFAULT 'all',
																		  PRIMARY KEY (`form_id`,`element_id`)
																		) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_field_logic_conditions_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."field_logic_conditions` (
																		  `alc_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
																		  `form_id` int(11) NOT NULL,
																		  `target_element_id` int(11) NOT NULL,
																		  `element_name` varchar(50) NOT NULL DEFAULT '',
																		  `rule_condition` varchar(15) NOT NULL DEFAULT 'is',
																		  `rule_keyword` varchar(255) DEFAULT NULL,
																		  PRIMARY KEY (`alc_id`),
																		  KEY `form_id` (`form_id`,`target_element_id`)
																		) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_form_payments_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_payments` (
																  `afp_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
																  `form_id` int(11) unsigned NOT NULL,
																  `record_id` int(11) unsigned NOT NULL,
																  `payment_id` varchar(255) DEFAULT NULL,
																  `date_created` datetime DEFAULT NULL,
																  `payment_date` datetime DEFAULT NULL,
																  `payment_status` varchar(255) DEFAULT NULL,
																  `payment_fullname` varchar(255) DEFAULT NULL,
																  `payment_amount` decimal(62,2) NOT NULL DEFAULT '0.00',
																  `payment_currency` varchar(3) NOT NULL DEFAULT 'usd',
																  `payment_test_mode` int(1) NOT NULL DEFAULT '0',
																  `payment_merchant_type` varchar(25) DEFAULT NULL,
																  `status` int(1) NOT NULL DEFAULT '1',
																  `billing_street` varchar(255) DEFAULT NULL,
																  `billing_city` varchar(255) DEFAULT NULL,
																  `billing_state` varchar(255) DEFAULT NULL,
																  `billing_zipcode` varchar(255) DEFAULT NULL,
																  `billing_country` varchar(255) DEFAULT NULL,
																  `same_shipping_address` int(1) NOT NULL DEFAULT '1',
																  `shipping_street` varchar(255) DEFAULT NULL,
																  `shipping_city` varchar(255) DEFAULT NULL,
																  `shipping_state` varchar(255) DEFAULT NULL,
																  `shipping_zipcode` varchar(255) DEFAULT NULL,
																  `shipping_country` varchar(255) DEFAULT NULL,
																   PRIMARY KEY (`afp_id`),
																   KEY `status` (`status`),
																   KEY `form_id` (`form_id`),
																   KEY `record_id` (`record_id`)
																  ) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_page_logic_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."page_logic` (
																`form_id` int(11) NOT NULL,
															  	`page_id` varchar(15) NOT NULL DEFAULT '',
															  	`rule_all_any` varchar(3) NOT NULL DEFAULT 'all',
															  	 PRIMARY KEY (`form_id`,`page_id`)
															   ) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_page_logic_conditions_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."page_logic_conditions` (
																		   `apc_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
																		   `form_id` int(11) NOT NULL,
																		   `target_page_id` varchar(15) NOT NULL DEFAULT '',
																		   `element_name` varchar(50) NOT NULL DEFAULT '',
																		   `rule_condition` varchar(15) NOT NULL DEFAULT 'is',
																		   `rule_keyword` varchar(255) DEFAULT NULL,
																		    PRIMARY KEY (`apc_id`),
																		    KEY `form_id` (`form_id`,`target_page_id`)
															   			  ) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_form_sorts_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_sorts` (
												  `user_id` int(11) NOT NULL,
												  `sort_by` varchar(25) DEFAULT '',
												  PRIMARY KEY (`user_id`)
												) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_email_logic_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."email_logic` (
												    		      `form_id` int(11) NOT NULL,
																  `rule_id` int(11) NOT NULL,
																  `rule_all_any` varchar(3) NOT NULL DEFAULT 'all',
																  `target_email` text NOT NULL,
																  `template_name` varchar(15) NOT NULL DEFAULT 'notification' COMMENT 'notification - confirmation - custom',
																  `custom_from_name` text,
																  `custom_from_email` varchar(255) NOT NULL DEFAULT '',
																  `custom_replyto_email` varchar(255) NOT NULL DEFAULT '',
																  `custom_subject` text,
																  `custom_bcc` text,
																  `custom_content` text,
																  `custom_plain_text` int(1) NOT NULL DEFAULT '0',
																  `custom_pdf_enable` int(1) NOT NULL DEFAULT '0',
																  `custom_pdf_content` text,
																  `delay_notification_until_paid` int(1) NOT NULL DEFAULT '0',
																  PRIMARY KEY (`form_id`,`rule_id`)
												) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_email_logic_conditions_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."email_logic_conditions` (
												    		  `aec_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `target_rule_id` int(11) NOT NULL,
															  `element_name` varchar(50) NOT NULL DEFAULT '',
															  `rule_condition` varchar(15) NOT NULL DEFAULT 'is',
															  `rule_keyword` varchar(255) DEFAULT NULL,
															  PRIMARY KEY (`aec_id`),
															  KEY `form_id` (`form_id`,`target_rule_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_webhook_options_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."webhook_options` (
												    		  `awo_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `rule_id` int(11) NOT NULL DEFAULT '0',
															  `rule_all_any` varchar(3) NOT NULL DEFAULT 'all',
															  `webhook_url` text,
															  `webhook_method` varchar(4) NOT NULL DEFAULT 'post',
															  `webhook_format` varchar(10) NOT NULL DEFAULT 'key-value',
															  `webhook_raw_data` mediumtext,
															  `enable_http_auth` int(1) DEFAULT '0',
															  `http_username` varchar(255) DEFAULT NULL,
															  `http_password` varchar(255) DEFAULT NULL,
															  `enable_custom_http_headers` int(1) NOT NULL DEFAULT '0',
															  `custom_http_headers` text,
															  `delay_notification_until_paid` int(1) NOT NULL DEFAULT '0',
															  PRIMARY KEY (`awo_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_webhook_parameters_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."webhook_parameters` (
												    		  `awp_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `rule_id` int(11) NOT NULL DEFAULT '0',
															  `param_name` text,
															  `param_value` text,
															  PRIMARY KEY (`awp_id`),
															  KEY `form_id` (`form_id`,`rule_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_webhook_logic_conditions_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."webhook_logic_conditions` (
												    		  `wlc_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `target_rule_id` int(11) NOT NULL,
															  `element_name` varchar(50) NOT NULL DEFAULT '',
															  `rule_condition` varchar(15) NOT NULL DEFAULT 'is',
															  `rule_keyword` varchar(255) DEFAULT NULL,
															  PRIMARY KEY (`wlc_id`),
															  KEY `form_id` (`form_id`,`target_rule_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_reports_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."reports` (
												    		  `form_id` int(11) NOT NULL,
															  `report_access_key` varchar(100) DEFAULT NULL,
															  PRIMARY KEY (`form_id`),
															  KEY `report_access_key` (`report_access_key`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_report_elements_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."report_elements` (
												    		  `access_key` varchar(100) DEFAULT NULL,
															  `form_id` int(11) NOT NULL,
															  `chart_id` int(11) NOT NULL,
															  `chart_position` int(11) NOT NULL DEFAULT '0',
															  `chart_status` int(1) NOT NULL DEFAULT '1',
															  `chart_datasource` varchar(25) NOT NULL DEFAULT '',
															  `chart_type` varchar(25) NOT NULL DEFAULT '',
															  `chart_enable_filter` int(1) NOT NULL DEFAULT '0',
															  `chart_filter_type` varchar(5) NOT NULL DEFAULT 'all',
															  `chart_title` text,
															  `chart_title_position` varchar(10) NOT NULL DEFAULT 'top',
															  `chart_title_align` varchar(10) NOT NULL DEFAULT 'center',
															  `chart_width` int(11) NOT NULL DEFAULT '0',
															  `chart_height` int(11) NOT NULL DEFAULT '400',
															  `chart_background` varchar(8) DEFAULT NULL,
															  `chart_theme` varchar(25) NOT NULL DEFAULT 'blueopal',
															  `chart_legend_visible` int(1) NOT NULL DEFAULT '1',
															  `chart_legend_position` varchar(10) NOT NULL DEFAULT 'right',
															  `chart_labels_visible` int(1) NOT NULL DEFAULT '1',
															  `chart_labels_position` varchar(10) NOT NULL DEFAULT 'outsideEnd',
															  `chart_labels_template` varchar(255) NOT NULL DEFAULT '#= category #',
															  `chart_labels_align` varchar(10) NOT NULL DEFAULT 'circle',
															  `chart_tooltip_visible` int(1) NOT NULL DEFAULT '1',
															  `chart_tooltip_template` varchar(255) NOT NULL DEFAULT '#= category # - #= dataItem.entry # - #= kendo.format(''{0:P}'', percentage)#',
															  `chart_gridlines_visible` int(1) NOT NULL DEFAULT '1',
															  `chart_bar_color` varchar(8) DEFAULT NULL,
															  `chart_is_stacked` int(1) NOT NULL DEFAULT '0',
															  `chart_is_vertical` int(1) NOT NULL DEFAULT '0',
															  `chart_line_style` varchar(6) NOT NULL DEFAULT 'smooth',
															  `chart_axis_is_date` int(1) NOT NULL DEFAULT '0',
															  `chart_date_range` varchar(6) NOT NULL DEFAULT 'all' COMMENT 'all,period,custom',
															  `chart_date_period_value` int(11) NOT NULL DEFAULT '1',
															  `chart_date_period_unit` varchar(5) NOT NULL DEFAULT 'day',
															  `chart_date_axis_baseunit` varchar(5) DEFAULT NULL,
															  `chart_date_range_start` date DEFAULT NULL,
															  `chart_date_range_end` date DEFAULT NULL,
															  `chart_grid_page_size` int(11) NOT NULL DEFAULT '10',
															  `chart_grid_max_length` int(11) NOT NULL DEFAULT '100',
															  `chart_grid_sort_by` varchar(100) NOT NULL DEFAULT 'id-desc',
															  `chart_rating_size` varchar(6) NOT NULL DEFAULT 'medium',
															  `chart_rating_bars_visible` int(1) NOT NULL DEFAULT '1',
															  `chart_rating_total_visible` int(1) NOT NULL DEFAULT '1',
															  PRIMARY KEY (`form_id`,`chart_id`),
															  KEY `access_key` (`access_key`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_report_filters_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."report_filters` (
												    		  `arf_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `chart_id` int(11) NOT NULL,
															  `element_name` varchar(50) NOT NULL DEFAULT '',
															  `filter_condition` varchar(15) NOT NULL DEFAULT 'is',
															  `filter_keyword` varchar(255) DEFAULT NULL,
															  PRIMARY KEY (`arf_id`),
															  KEY `form_id` (`form_id`,`chart_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_grid_columns_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."grid_columns` (
												    		  `agc_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `chart_id` int(11) NOT NULL,
															  `element_name` varchar(255) NOT NULL DEFAULT '',
															  `position` int(11) NOT NULL,
															  PRIMARY KEY (`agc_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_success_logic_conditions_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."success_logic_conditions` (
												    		  `slc_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `target_rule_id` int(11) NOT NULL,
															  `element_name` varchar(50) NOT NULL DEFAULT '',
															  `rule_condition` varchar(15) NOT NULL DEFAULT 'is',
															  `rule_keyword` varchar(255) DEFAULT NULL,
															  PRIMARY KEY (`slc_id`),
															  KEY `form_id` (`form_id`,`target_rule_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_success_logic_options_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."success_logic_options` (
												    		  `slo_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `rule_id` int(11) NOT NULL DEFAULT '0',
															  `rule_all_any` varchar(3) NOT NULL DEFAULT 'all',
															  `success_type` varchar(11) NOT NULL DEFAULT 'message' COMMENT 'message;redirect',
															  `success_message` text,
															  `redirect_url` text,
															  PRIMARY KEY (`slo_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_form_themes_files_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_themes_files` (
												    		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `file_name` text NOT NULL,
															  `file_content` longblob,
															  PRIMARY KEY (`id`),
															  KEY `file_name` (`file_name`(255))
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_approval_settings_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."approval_settings` (
												    		  `form_id` int(11) NOT NULL,
															  `workflow_type` varchar(11) NOT NULL DEFAULT 'parallel' COMMENT 'parallel,serial',
															  `parallel_workflow` varchar(11) NOT NULL DEFAULT 'any' COMMENT 'any,all',
															  `revision_no` int(11) NOT NULL,
															  `revision_date` datetime default NULL,
															  `last_revised_by` int(11) NOT NULL,
															  PRIMARY KEY (`form_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_approvers_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."approvers` (
												    		  `form_id` int(11) NOT NULL,
															  `user_id` int(11) NOT NULL,
															  `rule_all_any` varchar(3) NOT NULL DEFAULT 'all' COMMENT 'all,any',
															  `user_position` int(11) NOT NULL,
															  PRIMARY KEY (`form_id`,`user_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_approvers_conditions_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."approvers_conditions` (
												    		  `aac_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `target_user_id` int(11) NOT NULL,
															  `element_name` varchar(50) NOT NULL DEFAULT '',
															  `rule_condition` varchar(15) NOT NULL DEFAULT 'is',
															  `rule_keyword` varchar(255) DEFAULT NULL,
															  PRIMARY KEY (`aac_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}


	function create_ap_form_images_files_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_images_files` (
													    		  `form_id` int(11) NOT NULL,
																  `file_name` text NOT NULL,
																  `file_content` longblob,
																  KEY `form_id` (`form_id`,`file_name`(100))
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_form_pdf_files_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_pdf_files` (
													    		  `form_id` int(11) NOT NULL,
																  `file_name` text NOT NULL,
																  `file_content` longblob,
																  KEY `form_id` (`form_id`,`file_name`(100))
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_integrations_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."integrations` (
													    		  `ai_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
																  `form_id` int(11) NOT NULL,
																  `gsheet_integration_status` tinyint(1) NOT NULL DEFAULT '0',
																  `gsheet_spreadsheet_id` varchar(255) DEFAULT NULL,
																  `gsheet_spreadsheet_url` text,
																  `gsheet_elements` text,
																  `gsheet_create_new_sheet` tinyint(1) NOT NULL DEFAULT '0',
																  `gsheet_refresh_token` varchar(255) DEFAULT NULL,
																  `gsheet_access_token` varchar(255) DEFAULT NULL,
																  `gsheet_token_create_date` datetime DEFAULT NULL,
																  `gsheet_linked_user_id` int(11) DEFAULT NULL,
																  `gsheet_delay_notification_until_paid` tinyint(1) NOT NULL DEFAULT '1',
																  `gsheet_delay_notification_until_approved` tinyint(1) NOT NULL DEFAULT '1',
																  `gcal_integration_status` tinyint(1) NOT NULL DEFAULT '0',
																  `gcal_refresh_token` varchar(255) DEFAULT NULL,
																  `gcal_access_token` varchar(255) DEFAULT NULL,
																  `gcal_token_create_date` datetime DEFAULT NULL,
																  `gcal_linked_user_id` int(11) DEFAULT NULL,
																  `gcal_calendar_id` varchar(255) DEFAULT NULL,
																  `gcal_event_title` text,
																  `gcal_event_desc` text,
																  `gcal_event_location` text,
																  `gcal_event_allday` tinyint(1) NOT NULL DEFAULT '1',
																  `gcal_start_datetime` datetime DEFAULT NULL,
																  `gcal_start_date_element` int(11) DEFAULT NULL,
																  `gcal_start_time_element` int(11) DEFAULT NULL,
																  `gcal_start_date_type` varchar(11) NOT NULL DEFAULT 'datetime' COMMENT 'datetime,element',
																  `gcal_start_time_type` varchar(11) NOT NULL DEFAULT 'datetime' COMMENT 'datetime,element',
																  `gcal_end_datetime` datetime DEFAULT NULL,
																  `gcal_end_date_element` int(11) DEFAULT NULL,
																  `gcal_end_time_element` int(11) DEFAULT NULL,
																  `gcal_end_time_type` varchar(11) NOT NULL DEFAULT 'datetime' COMMENT 'datetime,element',
																  `gcal_end_date_type` varchar(11) NOT NULL DEFAULT 'datetime' COMMENT 'datetime,element',
																  `gcal_duration_type` varchar(11) NOT NULL DEFAULT 'period' COMMENT 'period,datetime',
																  `gcal_duration_period_length` int(11) NOT NULL DEFAULT '30',
																  `gcal_duration_period_unit` varchar(11) NOT NULL DEFAULT 'minute' COMMENT 'minute,hour,day',
																  `gcal_attendee_email` int(11) DEFAULT NULL,
																  `gcal_delay_notification_until_paid` tinyint(1) NOT NULL DEFAULT '1',
																  `gcal_delay_notification_until_approved` tinyint(1) NOT NULL DEFAULT '1',
																  PRIMARY KEY (`ai_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_folders_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."folders` (
													    	  `user_id` int(11) NOT NULL,
															  `folder_id` int(11) NOT NULL,
															  `folder_position` int(11) NOT NULL,
															  `folder_name` varchar(255) NOT NULL DEFAULT '',
															  `folder_selected` tinyint(1) NOT NULL DEFAULT '0',
															  `rule_all_any` varchar(3) NOT NULL DEFAULT 'all',
															  PRIMARY KEY (`user_id`,`folder_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_folders_conditions_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."folders_conditions` (
													    	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `user_id` int(11) NOT NULL,
															  `folder_id` int(11) NOT NULL,
															  `element_name` varchar(50) NOT NULL DEFAULT '',
															  `rule_condition` varchar(15) NOT NULL DEFAULT 'is',
															  `rule_keyword` varchar(255) DEFAULT NULL,
															  PRIMARY KEY (`id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_form_stats_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_stats` (
													    	  `form_id` int(11) unsigned NOT NULL,
															  `total_entries` int(11) unsigned DEFAULT NULL,
															  `today_entries` int(11) unsigned DEFAULT NULL,
															  `last_entry_date` datetime DEFAULT NULL,
															  PRIMARY KEY (`form_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	function create_ap_sessions_table($dbh){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."sessions` (
													    	  	`id` varchar(255) NOT NULL,
  																`data` mediumtext NOT NULL,
  																`timestamp` int(255) NOT NULL,
  																PRIMARY KEY (`id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			return $e->getMessage().'<br/><br/>';
		}

		return '';
	}

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_2_x_to_3_0($dbh,$options=array()){

		$post_install_error = '';
		$license_key 		  = $options['license_key'];
		$new_machform_version = $options['new_machform_version'];
		$admin_username		  = $options['admin_username'];
		
		//2. Alter table ap_forms
		//add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."forms`
												  ADD COLUMN `form_tags` varchar(255) DEFAULT NULL,
												  ADD COLUMN `form_redirect_enable` int(1) NOT NULL DEFAULT '0',
												  ADD COLUMN `form_captcha_type` char(1) NOT NULL DEFAULT 'r',
												  ADD COLUMN `form_theme_id` int(11) NOT NULL DEFAULT '0',
												  ADD COLUMN `form_resume_enable` int(1) NOT NULL DEFAULT '0',
												  ADD COLUMN `form_limit_enable` int(1) NOT NULL DEFAULT '0',
												  ADD COLUMN `form_limit` int(11) NOT NULL DEFAULT '0',
												  ADD COLUMN `form_label_alignment` varchar(11) NOT NULL DEFAULT 'top_label',
												  ADD COLUMN `form_language` varchar(50) DEFAULT NULL,
												  ADD COLUMN `form_page_total` int(11) NOT NULL DEFAULT '1',
												  ADD COLUMN `form_lastpage_title` varchar(255) DEFAULT NULL,
												  ADD COLUMN `form_submit_primary_text` varchar(255) NOT NULL DEFAULT 'Submit',
												  ADD COLUMN `form_submit_secondary_text` varchar(255) NOT NULL DEFAULT 'Previous',
												  ADD COLUMN `form_submit_primary_img` varchar(255) DEFAULT NULL,
												  ADD COLUMN `form_submit_secondary_img` varchar(255) DEFAULT NULL,
												  ADD COLUMN `form_submit_use_image` int(1) NOT NULL DEFAULT '0',
												  ADD COLUMN `form_review_primary_text` varchar(255) NOT NULL DEFAULT 'Submit',
												  ADD COLUMN `form_review_secondary_text` varchar(255) NOT NULL DEFAULT 'Previous',
												  ADD COLUMN `form_review_primary_img` varchar(255) DEFAULT NULL,
												  ADD COLUMN `form_review_secondary_img` varchar(255) DEFAULT NULL,
												  ADD COLUMN `form_review_use_image` int(11) NOT NULL DEFAULT '0',
												  ADD COLUMN `form_review_title` text,
												  ADD COLUMN `form_review_description` text,
												  ADD COLUMN `form_pagination_type` varchar(11) NOT NULL DEFAULT 'steps',
												  ADD COLUMN `form_schedule_enable` int(1) NOT NULL DEFAULT '0',
												  ADD COLUMN `form_schedule_start_date` date DEFAULT NULL,
												  ADD COLUMN `form_schedule_end_date` date DEFAULT NULL,
												  ADD COLUMN `form_schedule_start_hour` time DEFAULT NULL,
												  ADD COLUMN `form_schedule_end_hour` time DEFAULT NULL,
												  ADD COLUMN `esl_enable` tinyint(1) NOT NULL DEFAULT '0',
												  ADD COLUMN `esr_enable` tinyint(1) NOT NULL DEFAULT '0',
												  ADD COLUMN `payment_enable_merchant` int(1) NOT NULL DEFAULT '-1',
												  ADD COLUMN `payment_merchant_type` varchar(25) NOT NULL DEFAULT 'paypal_standard',
												  ADD COLUMN `payment_paypal_email` varchar(255) DEFAULT NULL,
												  ADD COLUMN `payment_paypal_language` varchar(5) NOT NULL DEFAULT 'US',
												  ADD COLUMN `payment_currency` varchar(5) NOT NULL DEFAULT 'USD',
												  ADD COLUMN `payment_show_total` int(1) NOT NULL DEFAULT '0',
												  ADD COLUMN `payment_total_location` varchar(11) NOT NULL DEFAULT 'top',
												  ADD COLUMN `payment_enable_recurring` int(1) NOT NULL DEFAULT '0',
												  ADD COLUMN `payment_recurring_cycle` int(11) NOT NULL DEFAULT '1',
												  ADD COLUMN `payment_recurring_unit` varchar(5) NOT NULL DEFAULT 'month',
												  ADD COLUMN `payment_price_type` varchar(11) NOT NULL DEFAULT 'fixed',
												  ADD COLUMN `payment_price_amount` decimal(62,2) NOT NULL DEFAULT '0.00',
												  ADD COLUMN `payment_price_name` varchar(255) DEFAULT NULL,
												  ADD COLUMN `entries_sort_by` varchar(100) NOT NULL DEFAULT 'id-desc',
												  ADD COLUMN `entries_enable_filter` int(1) NOT NULL DEFAULT '0',
												  ADD COLUMN `entries_filter_type` varchar(5) NOT NULL DEFAULT 'all' COMMENT 'all or any';";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Alter table ap_form_elements
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_elements` 
														  ADD COLUMN `element_css_class` varchar(255) NOT NULL DEFAULT '',
														  ADD COLUMN `element_range_min` bigint(11) unsigned NOT NULL DEFAULT '0',
														  ADD COLUMN `element_range_max` bigint(11) unsigned NOT NULL DEFAULT '0',
														  ADD COLUMN `element_range_limit_by` char(1) NOT NULL,
														  ADD COLUMN `element_status` int(1) NOT NULL DEFAULT '2',
														  ADD COLUMN `element_choice_columns` int(1) NOT NULL DEFAULT '1',
														  ADD COLUMN `element_choice_has_other` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_choice_other_label` text,
														  ADD COLUMN `element_time_showsecond` int(11) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_time_24hour` int(11) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_address_hideline2` int(11) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_address_us_only` int(11) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_date_enable_range` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_date_range_min` date DEFAULT NULL,
														  ADD COLUMN `element_date_range_max` date DEFAULT NULL,
														  ADD COLUMN `element_date_enable_selection_limit` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_date_selection_max` int(11) NOT NULL DEFAULT '1',
														  ADD COLUMN `element_date_past_future` char(1) NOT NULL DEFAULT 'p',
														  ADD COLUMN `element_date_disable_past_future` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_date_disable_weekend` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_date_disable_specific` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_date_disabled_list` text CHARACTER SET utf8 COLLATE utf8_bin,
														  ADD COLUMN `element_file_enable_type_limit` int(1) NOT NULL DEFAULT '1',
														  ADD COLUMN `element_file_block_or_allow` char(1) NOT NULL DEFAULT 'b',
														  ADD COLUMN `element_file_type_list` varchar(255) DEFAULT NULL,
														  ADD COLUMN `element_file_as_attachment` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_file_enable_advance` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_file_auto_upload` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_file_enable_multi_upload` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_file_max_selection` int(11) NOT NULL DEFAULT '5',
														  ADD COLUMN `element_file_enable_size_limit` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_file_size_max` int(11) DEFAULT NULL,
														  ADD COLUMN `element_matrix_allow_multiselect` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_matrix_parent_id` int(11) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_submit_use_image` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_submit_primary_text` varchar(255) NOT NULL DEFAULT 'Continue',
														  ADD COLUMN `element_submit_secondary_text` varchar(255) NOT NULL DEFAULT 'Previous',
														  ADD COLUMN `element_submit_primary_img` varchar(255) DEFAULT NULL,
														  ADD COLUMN `element_submit_secondary_img` varchar(255) DEFAULT NULL,
														  ADD COLUMN `element_page_title` varchar(255) DEFAULT NULL,
														  ADD COLUMN `element_page_number` int(11) NOT NULL DEFAULT '1';";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//change the field size of element_constraint
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_elements` CHANGE COLUMN `element_constraint` `element_constraint` varchar(255) DEFAULT NULL;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//update the element_status value to 1
		$query = "UPDATE `".MF_TABLE_PREFIX."form_elements` SET `element_status`=1";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//4. Create table ap_element_prices
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."element_prices` (
													  `aep_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
													  `form_id` int(11) NOT NULL,
													  `element_id` int(11) NOT NULL,
													  `option_id` int(11) NOT NULL DEFAULT '0',
													  `price` decimal(62,2) NOT NULL DEFAULT '0.00',
													  PRIMARY KEY (`aep_id`),
													  KEY `form_id` (`form_id`),
													  KEY `element_id` (`element_id`)
													) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//5. Create table ap_fonts
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."fonts` (
												  `font_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
												  `font_origin` varchar(11) NOT NULL DEFAULT 'google',
												  `font_family` varchar(100) DEFAULT NULL,
												  `font_variants` text,
												  `font_variants_numeric` text,
												  PRIMARY KEY (`font_id`),
												  KEY `font_family` (`font_family`)
												) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		

		//6. Create table ap_form_filters
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_filters` (
														  `aff_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
														  `form_id` int(11) NOT NULL,
														  `element_name` varchar(50) NOT NULL DEFAULT '',
														  `filter_condition` varchar(15) NOT NULL DEFAULT 'is',
														  `filter_keyword` varchar(255) NOT NULL DEFAULT '',
														  PRIMARY KEY (`aff_id`),
														  KEY `form_id` (`form_id`)
														) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//7. Create table ap_form_themes
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_themes` (
													  `theme_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
													  `status` int(1) DEFAULT '1',
													  `theme_has_css` int(1) NOT NULL DEFAULT '0',
													  `theme_name` varchar(255) DEFAULT '',
													  `theme_built_in` int(1) NOT NULL DEFAULT '0',
													  `logo_type` varchar(11) NOT NULL DEFAULT 'default' COMMENT 'default,custom,disabled',
													  `logo_custom_image` text,
													  `logo_custom_height` int(11) NOT NULL DEFAULT '40',
													  `logo_default_image` varchar(50) DEFAULT '',
													  `logo_default_repeat` int(1) NOT NULL DEFAULT '0',
													  `wallpaper_bg_type` varchar(11) NOT NULL DEFAULT 'color' COMMENT 'color,pattern,custom',
													  `wallpaper_bg_color` varchar(11) DEFAULT '',
													  `wallpaper_bg_pattern` varchar(50) DEFAULT '',
													  `wallpaper_bg_custom` text,
													  `header_bg_type` varchar(11) NOT NULL DEFAULT 'color' COMMENT 'color,pattern,custom',
													  `header_bg_color` varchar(11) DEFAULT '',
													  `header_bg_pattern` varchar(50) DEFAULT '',
													  `header_bg_custom` text,
													  `form_bg_type` varchar(11) NOT NULL DEFAULT 'color' COMMENT 'color,pattern,custom',
													  `form_bg_color` varchar(11) DEFAULT '',
													  `form_bg_pattern` varchar(50) DEFAULT '',
													  `form_bg_custom` text,
													  `highlight_bg_type` varchar(11) NOT NULL DEFAULT 'color' COMMENT 'color,pattern,custom',
													  `highlight_bg_color` varchar(11) DEFAULT '',
													  `highlight_bg_pattern` varchar(50) DEFAULT '',
													  `highlight_bg_custom` text,
													  `guidelines_bg_type` varchar(11) NOT NULL DEFAULT 'color' COMMENT 'color,pattern,custom',
													  `guidelines_bg_color` varchar(11) DEFAULT '',
													  `guidelines_bg_pattern` varchar(50) DEFAULT '',
													  `guidelines_bg_custom` text,
													  `field_bg_type` varchar(11) NOT NULL DEFAULT 'color' COMMENT 'color,pattern,custom',
													  `field_bg_color` varchar(11) DEFAULT '',
													  `field_bg_pattern` varchar(50) DEFAULT '',
													  `field_bg_custom` text,
													  `form_title_font_type` varchar(50) NOT NULL DEFAULT 'Lucida Grande',
													  `form_title_font_weight` int(11) NOT NULL DEFAULT '400',
													  `form_title_font_style` varchar(25) NOT NULL DEFAULT 'normal',
													  `form_title_font_size` varchar(11) DEFAULT '',
													  `form_title_font_color` varchar(11) DEFAULT '',
													  `form_desc_font_type` varchar(50) NOT NULL DEFAULT 'Lucida Grande',
													  `form_desc_font_weight` int(11) NOT NULL DEFAULT '400',
													  `form_desc_font_style` varchar(25) NOT NULL DEFAULT 'normal',
													  `form_desc_font_size` varchar(11) DEFAULT '',
													  `form_desc_font_color` varchar(11) DEFAULT '',
													  `field_title_font_type` varchar(50) NOT NULL DEFAULT 'Lucida Grande',
													  `field_title_font_weight` int(11) NOT NULL DEFAULT '400',
													  `field_title_font_style` varchar(25) NOT NULL DEFAULT 'normal',
													  `field_title_font_size` varchar(11) DEFAULT '',
													  `field_title_font_color` varchar(11) DEFAULT '',
													  `guidelines_font_type` varchar(50) NOT NULL DEFAULT 'Lucida Grande',
													  `guidelines_font_weight` int(11) NOT NULL DEFAULT '400',
													  `guidelines_font_style` varchar(25) NOT NULL DEFAULT 'normal',
													  `guidelines_font_size` varchar(11) DEFAULT '',
													  `guidelines_font_color` varchar(11) DEFAULT '',
													  `section_title_font_type` varchar(50) NOT NULL DEFAULT 'Lucida Grande',
													  `section_title_font_weight` int(11) NOT NULL DEFAULT '400',
													  `section_title_font_style` varchar(25) NOT NULL DEFAULT 'normal',
													  `section_title_font_size` varchar(11) DEFAULT '',
													  `section_title_font_color` varchar(11) DEFAULT '',
													  `section_desc_font_type` varchar(50) NOT NULL DEFAULT 'Lucida Grande',
													  `section_desc_font_weight` int(11) NOT NULL DEFAULT '400',
													  `section_desc_font_style` varchar(25) NOT NULL DEFAULT 'normal',
													  `section_desc_font_size` varchar(11) DEFAULT '',
													  `section_desc_font_color` varchar(11) DEFAULT '',
													  `field_text_font_type` varchar(50) NOT NULL DEFAULT 'Lucida Grande',
													  `field_text_font_weight` int(11) NOT NULL DEFAULT '400',
													  `field_text_font_style` varchar(25) NOT NULL DEFAULT 'normal',
													  `field_text_font_size` varchar(11) DEFAULT '',
													  `field_text_font_color` varchar(11) DEFAULT '',
													  `border_form_width` int(11) NOT NULL DEFAULT '1',
													  `border_form_style` varchar(11) NOT NULL DEFAULT 'solid',
													  `border_form_color` varchar(11) DEFAULT '',
													  `border_guidelines_width` int(11) NOT NULL DEFAULT '1',
													  `border_guidelines_style` varchar(11) NOT NULL DEFAULT 'solid',
													  `border_guidelines_color` varchar(11) DEFAULT '',
													  `border_section_width` int(11) NOT NULL DEFAULT '1',
													  `border_section_style` varchar(11) NOT NULL DEFAULT 'solid',
													  `border_section_color` varchar(11) DEFAULT '',
													  `form_shadow_style` varchar(25) NOT NULL DEFAULT 'WarpShadow',
													  `form_shadow_size` varchar(11) NOT NULL DEFAULT 'large',
													  `form_shadow_brightness` varchar(11) NOT NULL DEFAULT 'normal',
													  `form_button_type` varchar(11) NOT NULL DEFAULT 'text',
													  `form_button_text` varchar(100) NOT NULL DEFAULT 'Submit',
													  `form_button_image` text,
													  `advanced_css` text,
													  PRIMARY KEY (`theme_id`),
													  KEY `theme_name` (`theme_name`)
													) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//insert into ap_form_themes table
		$query = "INSERT INTO `".MF_TABLE_PREFIX."form_themes` (`theme_id`, `status`, `theme_has_css`, `theme_name`, `theme_built_in`, `logo_type`, `logo_custom_image`, `logo_custom_height`, `logo_default_image`, `logo_default_repeat`, `wallpaper_bg_type`, `wallpaper_bg_color`, `wallpaper_bg_pattern`, `wallpaper_bg_custom`, `header_bg_type`, `header_bg_color`, `header_bg_pattern`, `header_bg_custom`, `form_bg_type`, `form_bg_color`, `form_bg_pattern`, `form_bg_custom`, `highlight_bg_type`, `highlight_bg_color`, `highlight_bg_pattern`, `highlight_bg_custom`, `guidelines_bg_type`, `guidelines_bg_color`, `guidelines_bg_pattern`, `guidelines_bg_custom`, `field_bg_type`, `field_bg_color`, `field_bg_pattern`, `field_bg_custom`, `form_title_font_type`, `form_title_font_weight`, `form_title_font_style`, `form_title_font_size`, `form_title_font_color`, `form_desc_font_type`, `form_desc_font_weight`, `form_desc_font_style`, `form_desc_font_size`, `form_desc_font_color`, `field_title_font_type`, `field_title_font_weight`, `field_title_font_style`, `field_title_font_size`, `field_title_font_color`, `guidelines_font_type`, `guidelines_font_weight`, `guidelines_font_style`, `guidelines_font_size`, `guidelines_font_color`, `section_title_font_type`, `section_title_font_weight`, `section_title_font_style`, `section_title_font_size`, `section_title_font_color`, `section_desc_font_type`, `section_desc_font_weight`, `section_desc_font_style`, `section_desc_font_size`, `section_desc_font_color`, `field_text_font_type`, `field_text_font_weight`, `field_text_font_style`, `field_text_font_size`, `field_text_font_color`, `border_form_width`, `border_form_style`, `border_form_color`, `border_guidelines_width`, `border_guidelines_style`, `border_guidelines_color`, `border_section_width`, `border_section_style`, `border_section_color`, `form_shadow_style`, `form_shadow_size`, `form_shadow_brightness`, `form_button_type`, `form_button_text`, `form_button_image`, `advanced_css`)
	VALUES
		(1,1,0,'Green Senegal',1,'default','http://',40,'machform.png',0,'color','#cfdfc5','','','color','#bad4a9','','','color','#ffffff','','','color','#ecf1ea','','','color','#ecf1ea','','','color','#cfdfc5','','','Philosopher',700,'normal','','#80af1b','Philosopher',400,'normal','100%','#80af1b','Philosopher',700,'normal','110%','#80af1b','Ubuntu',400,'normal','','#666666','Philosopher',700,'normal','110%','#80af1b','Philosopher',400,'normal','95%','#80af1b','Ubuntu',400,'normal','','#666666',1,'solid','#bad4a9',1,'dashed','#bad4a9',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(2,1,0,'Blue Bigbird',1,'default','http://',40,'machform.png',0,'color','#336699','','','color','#6699cc','','','color','#ffffff','','','color','#ccdced','','','color','#6699cc','','','color','#ffffff','','','Open Sans',600,'normal','','','Open Sans',400,'normal','','','Open Sans',700,'normal','100%','','Ubuntu',400,'normal','80%','#ffffff','Open Sans',600,'normal','','','Open Sans',400,'normal','95%','','Open Sans',400,'normal','','',1,'solid','#336699',1,'dotted','#6699cc',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(3,1,0,'Blue Pionus',1,'default','http://',40,'machform.png',0,'color','#556270','','','color','#6b7b8c','','','color','#ffffff','','','color','#99aec4','','','color','#6b7b8c','','','color','#ffffff','','','Istok Web',400,'normal','170%','','Maven Pro',400,'normal','100%','','Istok Web',700,'normal','100%','','Maven Pro',400,'normal','95%','#ffffff','Istok Web',400,'normal','110%','','Maven Pro',400,'normal','95%','','Maven Pro',400,'normal','','',1,'solid','#556270',1,'solid','#6b7b8c',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(4,1,0,'Brown Conure',1,'default','http://',40,'machform.png',0,'pattern','#948c75','pattern_036.gif','','color','#b3a783','','','color','#ffffff','','','color','#e0d0a2','','','color','#948c75','','','color','#f0f0d8','pattern_036.gif','','Molengo',400,'normal','170%','','Molengo',400,'normal','110%','','Molengo',400,'normal','110%','','Nobile',400,'normal','','#ececec','Molengo',400,'normal','130%','','Molengo',400,'normal','100%','','Molengo',400,'normal','110%','',1,'solid','#948c75',1,'solid','#948c75',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(5,1,0,'Yellow Lovebird',1,'default','http://',40,'machform.png',0,'color','#f0d878','','','color','#edb817','','','color','#ffffff','','','color','#f5d678','','','color','#f7c52e','','','color','#ffffff','','','Amaranth',700,'normal','170%','','Amaranth',400,'normal','100%','','Amaranth',700,'normal','100%','','Amaranth',400,'normal','','#444444','Amaranth',400,'normal','110%','','Amaranth',400,'normal','95%','','Amaranth',400,'normal','100%','',1,'solid','#f0d878',1,'solid','#f7c52e',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(6,1,0,'Pink Starling',1,'default','http://',40,'machform.png',0,'color','#ff6699','','','color','#d93280','','','color','#ffffff','','','color','#ffd0d4','','','color','#f9fad2','','','color','#ffffff','','','Josefin Sans',600,'normal','160%','','Josefin Sans',400,'normal','110%','','Josefin Sans',700,'normal','110%','','Josefin Sans',600,'normal','100%','','Josefin Sans',700,'normal','','','Josefin Sans',400,'normal','110%','','Josefin Sans',400,'normal','130%','',1,'solid','#ff6699',1,'dashed','#f56990',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(8,1,0,'Red Rabbit',1,'default','http://',40,'machform.png',0,'color','#a40802','','','color','#800e0e','','','color','#ffffff','','','color','#ffa4a0','','','color','#800e0e','','','color','#ffffff','','','Lobster',400,'normal','','#000000','Ubuntu',400,'normal','100%','#000000','Lobster',400,'normal','110%','#222222','Ubuntu',400,'normal','85%','#ffffff','Lobster',400,'normal','130%','#000000','Ubuntu',400,'normal','95%','#000000','Ubuntu',400,'normal','','#333333',1,'solid','#a40702',1,'solid','#800e0e',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(9,1,0,'Orange Robin',1,'default','http://',40,'machform.png',0,'color','#f38430','','','color','#fa6800','','','color','#ffffff','','','color','#a7dbd8','','','color','#e0e4cc','','','color','#ffffff','','','Lucida Grande',400,'normal','','#000000','Nobile',400,'normal','','#000000','Nobile',700,'normal','','#000000','Lucida Grande',400,'normal','','#444444','Nobile',700,'normal','100%','#000000','Nobile',400,'normal','','#000000','Nobile',400,'normal','95%','#333333',1,'solid','#f38430',1,'solid','#e0e4cc',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(10,1,0,'Orange Sunbird',1,'default','http://',40,'machform.png',0,'color','#d95c43','','','color','#c02942','','','color','#ffffff','','','color','#d95c43','','','color','#53777a','','','color','#ffffff','','','Lucida Grande',400,'normal','','#000000','Lucida Grande',400,'normal','','#000000','Lucida Grande',700,'normal','','#222222','Lucida Grande',400,'normal','','#ffffff','Lucida Grande',400,'normal','','#000000','Lucida Grande',400,'normal','','#000000','Lucida Grande',400,'normal','','#333333',1,'solid','#d95c43',1,'solid','#53777a',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(11,1,0,'Green Ringneck',1,'default','http://',40,'machform.png',0,'color','#0b486b','','','color','#3b8686','','','color','#ffffff','','','color','#cff09e','','','color','#79bd9a','','','color','#a8dba8','','','Delius Swash Caps',400,'normal','','#000000','Delius Swash Caps',400,'normal','100%','#000000','Delius Swash Caps',400,'normal','100%','#222222','Delius',400,'normal','85%','#ffffff','Delius Swash Caps',400,'normal','','#000000','Delius Swash Caps',400,'normal','95%','#000000','Delius',400,'normal','','#515151',1,'solid','#0b486b',1,'solid','#79bd9a',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(12,1,0,'Brown Finch',1,'default','http://',40,'machform.png',0,'color','#774f38','','','color','#e08e79','','','color','#ffffff','','','color','#ece5ce','','','color','#c5e0dc','','','color','#f9fad2','','','Arvo',700,'normal','','#000000','Arvo',400,'normal','','#000000','Arvo',700,'normal','','#222222','Arvo',400,'normal','','#444444','Arvo',400,'normal','','#000000','Arvo',400,'normal','85%','#000000','Arvo',400,'normal','','#333333',1,'solid','#774f38',1,'dashed','#e08e79',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(14,1,0,'Brown Macaw',1,'default','http://',40,'machform.png',0,'color','#413e4a','','','color','#73626e','','','pattern','#ffffff','pattern_022.gif','','color','#f0b49e','','','color','#b38184','','','color','#ffffff','','','Cabin',500,'normal','160%','#000000','Cabin',400,'normal','100%','#000000','Cabin',700,'normal','110%','#222222','Lucida Grande',400,'normal','','#ececec','Cabin',600,'normal','','#000000','Cabin',600,'normal','95%','#000000','Cabin',400,'normal','110%','#333333',1,'solid','#413e4a',1,'dotted','#ff9900',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(15,1,0,'Pink Thrush',1,'default','http://',40,'machform.png',0,'color','#ff9f9d','','','color','#ff3d7e','','','color','#ffffff','','','color','#7fc7af','','','color','#3fb8b0','','','color','#ffffff','','','Crafty Girls',400,'normal','','#000000','Crafty Girls',400,'normal','100%','#000000','Crafty Girls',400,'normal','100%','#222222','Nobile',400,'normal','80%','#ffffff','Crafty Girls',400,'normal','','#000000','Crafty Girls',400,'normal','95%','#000000','Molengo',400,'normal','110%','#333333',1,'solid','#ff9f9d',1,'solid','#3fb8b0',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(16,1,0,'Yellow Bulbul',1,'default','http://',40,'machform.png',0,'color','#f8f4d7','','','color','#f4b26c','','','color','#f4dec2','','','color','#f2b4a7','','','color','#e98976','','','color','#ffffff','','','Special Elite',400,'normal','','#000000','Special Elite',400,'normal','','#000000','Special Elite',400,'normal','95%','#222222','Cousine',400,'normal','80%','#ececec','Special Elite',400,'normal','','#000000','Special Elite',400,'normal','','#000000','Cousine',400,'normal','','#333333',1,'solid','#f8f4d7',1,'solid','#f4b26c',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(17,1,0,'Blue Canary',1,'default','http://',40,'machform.png',0,'color','#81a8b8','','','color','#a4bcc2','','','color','#ffffff','','','color','#e8f3f8','','','color','#dbe6ec','','','color','#ffffff','','','PT Sans',400,'normal','','#000000','PT Sans',400,'normal','100%','#000000','PT Sans',700,'normal','100%','#222222','PT Sans',400,'normal','','#666666','PT Sans',700,'normal','','#000000','PT Sans',400,'normal','100%','#000000','PT Sans',400,'normal','110%','#333333',1,'solid','#81a8b8',1,'dashed','#a4bcc2',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(18,1,0,'Red Mockingbird',1,'default','http://',40,'machform.png',0,'color','#6b0103','','','color','#a30005','','','color','#c21b01','','','color','#f03d02','','','color','#1c0113','','','color','#ffffff','','','Oswald',400,'normal','','#ffffff','Open Sans',400,'normal','','#ffffff','Oswald',400,'normal','95%','#ffffff','Open Sans',400,'normal','','#ececec','Oswald',400,'normal','','#ececec','Lucida Grande',400,'normal','','#ffffff','Open Sans',400,'normal','','#333333',1,'solid','#6b0103',1,'solid','#1c0113',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(13,1,0,'Green Sparrow',1,'default','http://',40,'machform.png',0,'color','#d1f2a5','','','color','#f56990','','','color','#ffffff','','','color','#ffc48c','','','color','#ffa080','','','color','#ffffff','','','Open Sans',400,'normal','','#000000','Open Sans',400,'normal','','#000000','Open Sans',700,'normal','','#222222','Ubuntu',400,'normal','85%','#f4fce8','Open Sans',600,'normal','','#000000','Open Sans',400,'normal','95%','#000000','Open Sans',400,'normal','','#333333',10,'solid','#f0fab4',1,'solid','#ffa080',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(21,1,0,'Purple Vanga',1,'default','http://',40,'machform.png',0,'color','#7b85e2','','','color','#7aa6d6','','','color','#d1e7f9','','','color','#7aa6d6','','','color','#fbfcd0','','','color','#ffffff','','','Droid Sans',400,'normal','160%','#444444','Droid Sans',400,'normal','95%','#444444','Open Sans',700,'normal','95%','#444444','Droid Sans',400,'normal','85%','#444444','Droid Sans',400,'normal','110%','#444444','Droid Sans',400,'normal','95%','#000000','Droid Sans',400,'normal','100%','#333333',0,'solid','#CCCCCC',1,'solid','#fbfcd0',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(22,1,0,'Purple Dove',1,'default','http://',40,'machform.png',0,'color','#c0addb','','','color','#a662de','','','pattern','#ffffff','pattern_044.gif','','color','#a662de','','','color','#a662de','','','color','#c0addb','','','Pacifico',400,'normal','180%','#000000','Open Sans',400,'normal','95%','#000000','Pacifico',400,'normal','95%','#222222','Open Sans',400,'normal','80%','#ececec','Pacifico',400,'normal','110%','#000000','Open Sans',400,'normal','95%','#000000','Open Sans',400,'normal','100%','#333333',0,'solid','#a662de',1,'dashed','#CCCCCC',1,'dashed','#a662de','StandShadow','large','dark','text','Submit','http://',''),
		(20,1,0,'Pink Flamingo',1,'default','http://',40,'machform.png',0,'color','#f87d7b','','','color','#5ea0a3','','','color','#ffffff','','','color','#fab97f','','','color','#dcd1b4','','','color','#ffffff','','','Lucida Grande',400,'normal','160%','#b05573','Lucida Grande',400,'normal','95%','#b05573','Lucida Grande',700,'normal','95%','#b05573','Lucida Grande',400,'normal','80%','#444444','Lucida Grande',400,'normal','110%','#b05573','Lucida Grande',400,'normal','85%','#b05573','Lucida Grande',400,'normal','100%','#333333',0,'solid','#f87d7b',1,'dotted','#fab97f',1,'dotted','#CCCCCC','WarpShadow','large','normal','text','Submit','http://',''),
		(19,1,0,'Yellow Kiwi',1,'default','http://',40,'machform.png',0,'color','#ffe281','','','color','#ffbb7f','','','color','#eee9e5','','','color','#fad4b2','','','color','#ff9c97','','','color','#ffffff','','','Lucida Grande',400,'normal','160%','#000000','Lucida Grande',400,'normal','95%','#000000','Lucida Grande',700,'normal','95%','#222222','Lucida Grande',400,'normal','80%','#ffffff','Lucida Grande',400,'normal','110%','#000000','Lucida Grande',400,'normal','85%','#000000','Lucida Grande',400,'normal','100%','#333333',1,'solid','#ffe281',1,'solid','#CCCCCC',1,'dotted','#cdcdcd','WarpShadow','large','normal','text','Submit','http://','');";
		
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//8. Create table ap_settings
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."settings` (
													  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
													  `smtp_enable` tinyint(1) NOT NULL DEFAULT '0',
													  `smtp_host` varchar(255) NOT NULL DEFAULT 'localhost',
													  `smtp_port` int(11) NOT NULL DEFAULT '25',
													  `smtp_auth` tinyint(1) NOT NULL DEFAULT '0',
													  `smtp_username` varchar(255) DEFAULT NULL,
													  `smtp_password` varchar(255) DEFAULT NULL,
													  `smtp_secure` tinyint(1) NOT NULL DEFAULT '0',
													  `upload_dir` varchar(255) NOT NULL DEFAULT './data',
													  `data_dir` varchar(255) NOT NULL DEFAULT './data',
													  `default_from_name` varchar(255) NOT NULL DEFAULT 'MachForm',
													  `default_from_email` varchar(255) DEFAULT NULL,
													  `base_url` varchar(255) DEFAULT NULL,
													  `form_manager_max_rows` int(11) DEFAULT '10',
													  `form_manager_sort_by` varchar(25) DEFAULT NULL,
													  `admin_login` varchar(255) NOT NULL DEFAULT '',
													  `admin_password` varchar(255) NOT NULL DEFAULT '',
													  `cookie_hash` varchar(25) DEFAULT NULL,
													  `admin_image_url` varchar(255) DEFAULT NULL,
													  `disable_machform_link` int(1) DEFAULT '0',
													  `customer_id` varchar(100) DEFAULT NULL,
													  `customer_name` varchar(100) DEFAULT NULL,
													  `license_key` varchar(50) DEFAULT NULL,
													  `machform_version` varchar(10) NOT NULL DEFAULT '3.3',
													  PRIMARY KEY (`id`)
													) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//9. Insert into ap_settings table
		$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
		$default_from_email = "no-reply@{$domain}";

		if(!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != 'off')){
			$ssl_suffix = 's';
		}else if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'){
            $ssl_suffix = 's';
        }else if (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && $_SERVER['HTTP_FRONT_END_HTTPS'] == 'on'){
            $ssl_suffix = 's';
        }else{
			$ssl_suffix = '';
		}
		$machform_base_url = 'http'.$ssl_suffix.'://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/';

		$hasher = new PasswordHash(8, FALSE);
		$default_password_hash = $hasher->HashPassword('machform');

		$query = "INSERT INTO `".MF_TABLE_PREFIX."settings` (`id`, 
																	`smtp_enable`, 
																	`smtp_host`, 
																	`smtp_port`, 
																	`smtp_auth`, 
																	`smtp_username`, 
																	`smtp_password`, 
																	`smtp_secure`, 
																	`upload_dir`, 
																	`data_dir`, 
																	`default_from_name`, 
																	`default_from_email`, 
																	`base_url`, 
																	`form_manager_max_rows`, 
																	`form_manager_sort_by`, 
																	`admin_login`, 
																	`admin_password`, 
																	`cookie_hash`, 
																	`admin_image_url`, 
																	`disable_machform_link`,
																	`license_key`,
																	`machform_version`)
															VALUES
																	(1,
																	 0,
																	'localhost',
																	25,
																	0,
																	'',
																	'',
																	0,
																	'./data',
																	'./data',
																	'MachForm',
																	'{$default_from_email}',
																	'{$machform_base_url}',
																	10,
																	'date_created',
																	'{$admin_username}',
																	'{$default_password_hash}',
																	'',
																	'',
																	0,
																	'{$license_key}',
																	'{$new_machform_version}');";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//10. Loop through each form table and alter table structure
		$query = "select `form_id`,`form_email`,`esr_email_address`,`form_redirect` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
				
		$email_enable_status_array = array();
		$redirect_enable_status_array = array();
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];
			$form_id_array[] = $form_id;

			if(!empty($row['form_email'])){
				$email_enable_status_array[$form_id]['esl_enable'] = 1;
			}else{
				$email_enable_status_array[$form_id]['esl_enable'] = 0;
			}

			if(!empty($row['esr_email_address'])){
				$email_enable_status_array[$form_id]['esr_enable'] = 1;
			}else{
				$email_enable_status_array[$form_id]['esr_enable'] = 0;
			}

			if(!empty($row['form_redirect'])){
				$redirect_enable_status_array[$form_id] = 1;
			}else{
				$redirect_enable_status_array[$form_id] = 0;
			}
		}
				
		foreach ($form_id_array as $form_id) {
			//add new columns
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}`
													  ADD COLUMN `status` int(4) unsigned NOT NULL DEFAULT '1',
													  ADD COLUMN `resume_key` varchar(10) default NULL;";
			$params = array();
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				$post_install_error .= $e->getMessage().'<br/><br/>';
			}

			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}_review`
													  ADD COLUMN `status` int(4) unsigned NOT NULL DEFAULT '1',
													  ADD COLUMN `resume_key` varchar(10) default NULL;";
			$params = array();
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				//do nothing, ignore if table review not exist
			}

			//update the esl_enable/esr_enable/redirect_enable
			$query = "UPDATE `".MF_TABLE_PREFIX."forms` SET esl_enable = ?,esr_enable = ?,form_redirect_enable = ? WHERE form_id = ?";
					
			$params = array($email_enable_status_array[$form_id]['esl_enable'],$email_enable_status_array[$form_id]['esr_enable'],$redirect_enable_status_array[$form_id],$form_id);
					
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				$post_install_error .= $e->getMessage().'<br/><br/>';
			}
		}

		//11. Loop through each CSS file and create a theme for each form
				
		//first delete htaccess file if exist
		if(file_exists('./data/.htaccess')){
			@unlink('./data/.htaccess');
		}		
				
		//Create "themes" folder
		if(is_writable("./data")){
			$old_mask = umask(0);
			mkdir("./data/themes",0755);
			umask($old_mask);
		}

		foreach ($form_id_array as $form_id) {
			$advanced_css = '';

			if(file_exists("./data/form_{$form_id}/css/view.css")){
				rename("./data/form_{$form_id}/css/view.css", "./data/form_{$form_id}/css/view.old.css");

				//copy default view.css to css folder
				$old_mask = umask(0);
				if(copy("./view.css","./data/form_{$form_id}/css/view.css")){
					$form_has_css = 1;
				}else{
					$form_has_css = 0;
				}
				umask($old_mask);

				//update form_has_css value
				$query = "UPDATE ".MF_TABLE_PREFIX."forms set form_has_css=? where form_id=?";
				$params = array($form_has_css,$form_id);
				$sth = $dbh->prepare($query);
				try{
					$sth->execute($params);
				}catch(PDOException $e) {
					$post_install_error .= $e->getMessage().'<br/><br/>';
				}


				$advanced_css = file_get_contents("./data/form_{$form_id}/css/view.old.css"); //store the old view.css content as advanced code
			
				//insert into ap_form_themes  table
				$query = "INSERT INTO 
								`".MF_TABLE_PREFIX."form_themes` 
										( 
										`status`, 
										`theme_has_css`, 
										`theme_name`, 
										`theme_built_in`, 
										`logo_type`, 
										`logo_custom_image`, 
										`logo_custom_height`, 
										`logo_default_image`, 
										`logo_default_repeat`, 
										`wallpaper_bg_type`, 
										`wallpaper_bg_color`, 
										`wallpaper_bg_pattern`, 
										`wallpaper_bg_custom`, 
										`header_bg_type`, 
										`header_bg_color`, 
										`header_bg_pattern`, 
										`header_bg_custom`, 
										`form_bg_type`, 
										`form_bg_color`, 
										`form_bg_pattern`, 
										`form_bg_custom`, 
										`highlight_bg_type`, 
										`highlight_bg_color`, 
										`highlight_bg_pattern`, 
										`highlight_bg_custom`, 
										`guidelines_bg_type`, 
										`guidelines_bg_color`, 
										`guidelines_bg_pattern`, 
										`guidelines_bg_custom`, 
										`field_bg_type`, 
										`field_bg_color`, 
										`field_bg_pattern`, 
										`field_bg_custom`, 
										`form_title_font_type`, 
										`form_title_font_weight`, 
										`form_title_font_style`, 
										`form_title_font_size`, 
										`form_title_font_color`, 
										`form_desc_font_type`, 
										`form_desc_font_weight`, 
										`form_desc_font_style`, 
										`form_desc_font_size`, 
										`form_desc_font_color`, 
										`field_title_font_type`, 
										`field_title_font_weight`, 
										`field_title_font_style`, 
										`field_title_font_size`, 
										`field_title_font_color`, 
										`guidelines_font_type`, 
										`guidelines_font_weight`, 
										`guidelines_font_style`, 
										`guidelines_font_size`, 
										`guidelines_font_color`, 
										`section_title_font_type`, 
										`section_title_font_weight`, 
										`section_title_font_style`, 
										`section_title_font_size`, 
										`section_title_font_color`, 
										`section_desc_font_type`, 
										`section_desc_font_weight`, 
										`section_desc_font_style`, 
										`section_desc_font_size`, 
										`section_desc_font_color`, 
										`field_text_font_type`, 
										`field_text_font_weight`, 
										`field_text_font_style`, 
										`field_text_font_size`, 
										`field_text_font_color`, 
										`border_form_width`, 
										`border_form_style`, 
										`border_form_color`, 
										`border_guidelines_width`, 
										`border_guidelines_style`, 
										`border_guidelines_color`, 
										`border_section_width`, 
										`border_section_style`, 
										`border_section_color`, 
										`form_shadow_style`, 
										`form_shadow_size`, 
										`form_shadow_brightness`, 
										`form_button_type`, 
										`form_button_text`, 
										`form_button_image`, 
										`advanced_css`)
								VALUES
									(1,0,?,0,'default','http://',40,'machform.png',0,'color','#ececec','','','color','#DEDEDE','','',
									'color','#ffffff','','','color','#FFF7C0','','','color','#F5F5F5','','','color','#ffffff','','','Lucida Grande',
									400,'normal','160%','#000000','Lucida Grande',400,'normal','95%','#000000','Lucida Grande',700,'normal','95%','#222222',
									'Lucida Grande',400,'normal','80%','#444444','Lucida Grande',400,'normal','110%','#000000','Lucida Grande',400,'normal',
									'85%','#000000','Lucida Grande',400,'normal','100%','#333333',1,'solid','#CCCCCC',1,'solid','#CCCCCC',1,'dotted','#CCCCCC',
									'WarpShadow','large','normal','text','Submit','http://',?);"; 
				$theme_name = 'Form #'.$form_id.' Theme';

				$params = array($theme_name,$advanced_css);
				$sth = $dbh->prepare($query);
				try{
					$sth->execute($params);
				}catch(PDOException $e) {
					$post_install_error .= $e->getMessage().'<br/><br/>';
				}
				
				$theme_id = (int) $dbh->lastInsertId();

				//create/update the CSS file for the theme
				$css_theme_filename = "./data/themes/theme_{$theme_id}.css";
				$css_theme_content  = mf_theme_get_css_content($dbh,$theme_id);
				
				$fpc_result = @file_put_contents($css_theme_filename,$css_theme_content);
				
				if(!empty($fpc_result)){ //if we're able to write into the css file, set the 'theme_has_css' to 1
					$params = array(1,$theme_id);
					$query = "UPDATE ".MF_TABLE_PREFIX."form_themes SET theme_has_css = ? WHERE theme_id = ?";
					
					$sth = $dbh->prepare($query);
					try{
						$sth->execute($params);
					}catch(PDOException $e) {
						$post_install_error .= $e->getMessage().'<br/><br/>';
					}
				}

				//update ap_forms table to use the new theme
				$query = "UPDATE ".MF_TABLE_PREFIX."forms set form_theme_id=? where form_id=?";

				$params = array($theme_id,$form_id);
				$sth = $dbh->prepare($query);
				try{
					$sth->execute($params);
				}catch(PDOException $e) {
					$post_install_error .= $e->getMessage().'<br/><br/>';
				}
				
			} //end file_exists
		} //end foreach form_id_array



		return $post_install_error;

	}

	/***************************************************************************
	 Changelog 3.0 to 3.2 												   
	 
	 - ap_settings: added 'admin_theme' column
	 - ap_form_elements: added 'element_section_display_in_email' default(0),'element_section_enable_scroll' default(0) column
	 - added .section_scroll_small,.section_scroll_medium,.section_scroll_large definition to all view.css for each form
	 - added .mf_review_section_break definition to all view.css
	 - added .mf_canvas_pad,.mf_sig_wrapper,.mf_sigpad_clear to all view.css
	 - added built-in class, .column_2 to .column_6,.new_row,.hidden,.guidelines_bottom

	 there wasn't any database change within 3.1
	 thus there is only this 3.0 to 3.2 update path
	***************************************************************************/
	
	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time

	function do_delta_update_3_0_to_3_2($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//alter table ap_settings, add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."settings` ADD COLUMN `admin_theme` varchar(11) DEFAULT NULL;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//alter table ap_form_elements, add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_elements` 
															ADD COLUMN `element_section_display_in_email` int(1) NOT NULL DEFAULT '0',
  													  		ADD COLUMN `element_section_enable_scroll` int(1) NOT NULL DEFAULT '0';";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//loop through each view.css file and add new classes
		$query  = "select `form_id` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
	
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];
			$form_id_array[] = $form_id;
		}

		$new_css_code = <<<EOT
.section_scroll_small{
	height: 5em;
	overflow-y: scroll;
}
.section_scroll_medium{
	height: 10em;
	overflow-y: scroll;
}
.section_scroll_large{
	height: 20em;
	overflow-y: scroll;
}
#machform_review_table td.mf_review_section_break{
	padding: 10px 5px;
}
/** Built-in Class **/
#main_body form li{
	clear: both;
}
#main_body form li.column_2{
  width: 47%;
  float: left;
  clear: none !important;
}
#main_body form li.column_3{
	width: 31%;
	float: left;
	clear: none !important;
}
#main_body form li.column_4{
	width: 22%;
  	float: left;
	clear: none !important;
}
#main_body form li.column_5{
	width: 17%;
	float: left;
	clear: none !important;
}
#main_body form li.column_6{
	width: 14%;
	float: left;
	clear: none !important;
}
#main_body form li.new_row{
	clear: left !important;
}
#main_body form li.hidden{
	display: none;
}
#main_body form li.guidelines_bottom .guidelines
{
	background: none !important;
	border: none !important;
	font-size:105%;
	line-height:100%;
	margin: 0 !important;
    padding: 0 0 5px;
	visibility:visible;
	width:100%;
	position: static;

}
EOT;
		foreach ($form_id_array as $form_id) {
			$target_css_file = $mf_settings['data_dir']."/form_{$form_id}/css/view.css";
			if(file_exists($target_css_file) && is_writable($target_css_file)){
				file_put_contents($target_css_file, $new_css_code, FILE_APPEND);
			}
		}

		return $post_install_error;
	}

	/***************************************************************************
	 Changelog 3.2 to 3.3 												   
	 
	 - new table ap_users
	 - new table ap_permissions
	 - new table ap_entries_preferences
	 - new table ap_form_locks
	 - new table ap_form_sorts

	 - ap_form_filters: added 'user_id' column
	 - ap_column_preferences: added 'user_id' column
	 - ap_form_themes: added 'user_id','theme_is_private' columns 

	 - delete columns from ap_forms: entries_sort_by, entries_enable_filter, entries_filter_type.... move the records into ap_entries_preferences table and set user_id to 1
	 - delete column from ap_settings: form_manager_sort_by and move the record into ap_form_sorts table

	 - copy admin login data to ap_users data and then delete the columns (admin_login,admin_password,cookie_hash), set admin user_id = 1

	 - create index.html file to each "files" folder and to the "data" folder
	***************************************************************************/
	
	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time

	function do_delta_update_3_2_to_3_3($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Create table ap_users
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."users` (
												  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
												  `user_email` varchar(255) NOT NULL DEFAULT '',
												  `user_password` varchar(255) NOT NULL DEFAULT '',
												  `user_fullname` varchar(255) NOT NULL DEFAULT '',
												  `priv_administer` tinyint(1) NOT NULL DEFAULT '0',
												  `priv_new_forms` tinyint(1) NOT NULL DEFAULT '0',
												  `priv_new_themes` tinyint(1) NOT NULL DEFAULT '0',
												  `last_login_date` datetime DEFAULT NULL,
												  `last_ip_address` varchar(15) DEFAULT '',
												  `cookie_hash` varchar(255) DEFAULT '',
												  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 - deleted; 1 - active; 2 - suspended',
												  PRIMARY KEY (`user_id`)
												) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Create table ap_permissions
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."permissions` (
												  `form_id` bigint(11) unsigned NOT NULL,
												  `user_id` int(11) unsigned NOT NULL,
												  `edit_form` tinyint(1) NOT NULL DEFAULT '0',
												  `edit_entries` tinyint(1) NOT NULL DEFAULT '0',
												  `view_entries` tinyint(1) NOT NULL DEFAULT '0',
												  PRIMARY KEY (`form_id`,`user_id`)
												) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Create table ap_entries_preferences
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."entries_preferences` (
												  `form_id` int(11) NOT NULL,
												  `user_id` int(11) NOT NULL,
												  `entries_sort_by` varchar(100) NOT NULL DEFAULT 'id-desc',
												  `entries_enable_filter` int(1) NOT NULL DEFAULT '0',
												  `entries_filter_type` varchar(5) NOT NULL DEFAULT 'all' COMMENT 'all or any'
												) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//4. Create table ap_form_locks
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_locks` (
												  `form_id` int(11) NOT NULL,
												  `user_id` int(11) NOT NULL,
												  `lock_date` datetime NOT NULL
												) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//5. Create table ap_form_sorts
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_sorts` (
												  `user_id` int(11) NOT NULL,
												  `sort_by` varchar(25) DEFAULT '',
												  PRIMARY KEY (`user_id`)
												) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}
		
		//6. Alter table ap_form_filters. Add 'user_id' column
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_filters` ADD COLUMN `user_id` int(11) NOT NULL DEFAULT '1';";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//7. Alter table ap_column_preferences. Add 'user_id' column
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."column_preferences` ADD COLUMN `user_id` int(11) NOT NULL DEFAULT '1';";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//8. Alter table ap_form_themes. Add 'user_id' and 'theme_is_private' column
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_themes`
													  ADD COLUMN `user_id` int(11) NOT NULL DEFAULT '1',
													  ADD COLUMN `theme_is_private` int(11) NOT NULL DEFAULT '1';";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//9. Alter table ap_forms. Delete columns: entries_sort_by, entries_enable_filter, entries_filter_type 
		//   Move the records into ap_entries_preferences table first and set user_id to 1
		$query = "INSERT INTO ".MF_TABLE_PREFIX."entries_preferences(
													form_id,
													user_id,
													entries_sort_by,
													entries_enable_filter,
													entries_filter_type) 
											  SELECT 
											 		form_id,
											 		'1' as user_id,
											 		entries_sort_by,
											 		entries_enable_filter,
											 		entries_filter_type 
											 	FROM 
											 		".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		$query = "ALTER TABLE ".MF_TABLE_PREFIX."forms DROP COLUMN `entries_sort_by`,DROP COLUMN `entries_enable_filter`,DROP COLUMN `entries_filter_type`;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//10. Alter table ap_settings. Delete column 'form_manager_sort_by'
		//    Move the record value into ap_form_sorts table
		$query = "INSERT INTO ".MF_TABLE_PREFIX."form_sorts(user_id,sort_by) SELECT '1' as user_id,form_manager_sort_by FROM ".MF_TABLE_PREFIX."settings";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		$query = "ALTER TABLE ".MF_TABLE_PREFIX."settings DROP COLUMN `form_manager_sort_by`;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//11. Copy admin login data to ap_users and then delete columns: admin_login,admin_password,cookie_hash
		//    Set user_id = 1
		$query = "INSERT INTO 
							 ".MF_TABLE_PREFIX."users(
		   							  user_id,
		   							  user_email,
		   							  user_password,
		   							  user_fullname,
		   							  priv_administer,
		   							  priv_new_forms,
		   							  priv_new_themes,
		   							  cookie_hash,
		   							  `status`)
								SELECT 
									  '1' as user_id,
									  admin_login,
									  admin_password,
									  'Administrator' as user_fullname, 
									  '1' as priv_administer, 
									  '1' as priv_new_forms,
									  '1' as priv_new_themes,
									  cookie_hash,
									  '1' as `status` 
								  FROM 
									  ".MF_TABLE_PREFIX."settings";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		$query = "ALTER TABLE ".MF_TABLE_PREFIX."settings DROP COLUMN `admin_login`,DROP COLUMN `admin_password`,DROP COLUMN `cookie_hash`;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//12. Loop through each "files" folder and to "data" folder. Create an empty "index.html" file.
		$query = "select `form_id` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
				
		$form_id_array = array();
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];

			if(is_writable($mf_settings['upload_dir']."/form_{$form_id}/files")){
				@file_put_contents($mf_settings['upload_dir']."/form_{$form_id}/files/index.html",' ');
			}
		}

		if(is_writable("./data")){
			@file_put_contents("./data/index.html",' ');
		}

		return $post_install_error;
	}

	/***************************************************************************
	 Changelog 3.3 to 3.4 												   
	 
	 - new table ap_field_logic_elements
	 - new table ap_field_logic_conditions
	 - new table ap_form_payments
	 - new table ap_page_logic
	 - new table ap_page_logic_conditions

	 - ap_forms: added columns 'logic_field_enable', logic_page_enable, payment_enable_trial, payment_trial_period, payment_trial_unit, payment_trial_amount, payment_stripe_live_secret_key, 
	 							payment_stripe_live_public_key, payment_stripe_test_secret_key, payment_stripe_test_public_key, payment_stripe_enable_test_mode, payment_enable_invoice, 
	 							payment_delay_notifications, payment_ask_billing, payment_ask_shipping, payment_invoice_email, payment_paypal_enable_test_mode 
	 - update ap_forms records, set the value of 'payment_delay_notifications' to 0 for all records. so that all existing paypal payments will still working as it is now.

	 - add these CSS code to all view.css files on all forms:

		a) #main_body select.select { background-image: none; }
		b) #main_body form li.guidelines_bottom .guidelines { clear: both; }
		c) everything under 'Payment Page'
	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	
	function do_delta_update_3_3_to_3_4($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Create table ap_field_logic_elements
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."field_logic_elements` (
																		  `form_id` int(11) NOT NULL,
																		  `element_id` int(11) NOT NULL,
																		  `rule_show_hide` varchar(4) NOT NULL DEFAULT 'show',
																		  `rule_all_any` varchar(3) NOT NULL DEFAULT 'all',
																		  PRIMARY KEY (`form_id`,`element_id`)
																		) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Create table ap_field_logic_conditions
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."field_logic_conditions` (
																		  `alc_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
																		  `form_id` int(11) NOT NULL,
																		  `target_element_id` int(11) NOT NULL,
																		  `element_name` varchar(50) NOT NULL DEFAULT '',
																		  `rule_condition` varchar(15) NOT NULL DEFAULT 'is',
																		  `rule_keyword` varchar(255) DEFAULT NULL,
																		  PRIMARY KEY (`alc_id`),
																		  KEY `form_id` (`form_id`,`target_element_id`)
																		) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Create table ap_form_payments
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_payments` (
																  `afp_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
																  `form_id` int(11) unsigned NOT NULL,
																  `record_id` int(11) unsigned NOT NULL,
																  `payment_id` varchar(255) DEFAULT NULL,
																  `date_created` datetime DEFAULT NULL,
																  `payment_date` datetime DEFAULT NULL,
																  `payment_status` varchar(255) DEFAULT NULL,
																  `payment_fullname` varchar(255) DEFAULT NULL,
																  `payment_amount` decimal(62,2) NOT NULL DEFAULT '0.00',
																  `payment_currency` varchar(3) NOT NULL DEFAULT 'usd',
																  `payment_test_mode` int(1) NOT NULL DEFAULT '0',
																  `payment_merchant_type` varchar(25) DEFAULT NULL,
																  `status` int(1) NOT NULL DEFAULT '1',
																  `billing_street` varchar(255) DEFAULT NULL,
																  `billing_city` varchar(255) DEFAULT NULL,
																  `billing_state` varchar(255) DEFAULT NULL,
																  `billing_zipcode` varchar(255) DEFAULT NULL,
																  `billing_country` varchar(255) DEFAULT NULL,
																  `same_shipping_address` int(1) NOT NULL DEFAULT '1',
																  `shipping_street` varchar(255) DEFAULT NULL,
																  `shipping_city` varchar(255) DEFAULT NULL,
																  `shipping_state` varchar(255) DEFAULT NULL,
																  `shipping_zipcode` varchar(255) DEFAULT NULL,
																  `shipping_country` varchar(255) DEFAULT NULL,
																   PRIMARY KEY (`afp_id`)
																  ) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//4. Create table ap_page_logic
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."page_logic` (
																`form_id` int(11) NOT NULL,
															  	`page_id` varchar(15) NOT NULL DEFAULT '',
															  	`rule_all_any` varchar(3) NOT NULL DEFAULT 'all',
															  	 PRIMARY KEY (`form_id`,`page_id`)
															   ) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//5. Create table ap_page_logic_conditions
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."page_logic_conditions` (
																		   `apc_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
																		   `form_id` int(11) NOT NULL,
																		   `target_page_id` varchar(15) NOT NULL DEFAULT '',
																		   `element_name` varchar(50) NOT NULL DEFAULT '',
																		   `rule_condition` varchar(15) NOT NULL DEFAULT 'is',
																		   `rule_keyword` varchar(255) DEFAULT NULL,
																		    PRIMARY KEY (`apc_id`),
																		    KEY `form_id` (`form_id`,`target_page_id`)
															   			  ) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//6. Alter ap_forms table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."forms` 
														  ADD COLUMN `logic_field_enable` tinyint(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `logic_page_enable` tinyint(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `payment_enable_trial` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `payment_trial_period` int(11) NOT NULL DEFAULT '1',
														  ADD COLUMN `payment_trial_unit` varchar(5) NOT NULL DEFAULT 'month',
														  ADD COLUMN `payment_trial_amount` decimal(62,2) NOT NULL DEFAULT '0.00',
														  ADD COLUMN `payment_stripe_live_secret_key` varchar(50) DEFAULT NULL,
											  			  ADD COLUMN `payment_stripe_live_public_key` varchar(50) DEFAULT NULL,
											  			  ADD COLUMN `payment_stripe_test_secret_key` varchar(50) DEFAULT NULL,
											  			  ADD COLUMN `payment_stripe_test_public_key` varchar(50) DEFAULT NULL,
											  			  ADD COLUMN `payment_stripe_enable_test_mode` int(1) NOT NULL DEFAULT '0',
											  			  ADD COLUMN `payment_paypal_enable_test_mode` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `payment_enable_invoice` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `payment_invoice_email` varchar(255) DEFAULT NULL,
														  ADD COLUMN `payment_delay_notifications` int(1) NOT NULL DEFAULT '1',
														  ADD COLUMN `payment_ask_billing` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `payment_ask_shipping` int(1) NOT NULL DEFAULT '0';";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//7. Update ap_forms records, set the value of 'payment_delay_notifications' to 0 for all records. 
		//so that all existing paypal payments will still working as it is now.
		$query = "UPDATE `".MF_TABLE_PREFIX."forms` SET `payment_delay_notifications`=0";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//8. Loop through each form CSS file and add new CSS code
		$query  = "select `form_id` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
	
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];
			$form_id_array[] = $form_id;
		}

		$new_css_code = <<<EOT

#main_body select.select { background-image: none; }
#main_body form li.guidelines_bottom .guidelines { clear: both; }
#main_body ul.payment_summary{
	overflow: hidden;
}
#main_body form li.payment_summary_list{
	border-right: 1px dashed #ccc;
	padding-right: 10px;
	width: 70%;
	float: right;
	clear: none;
	text-align: right;
}
#main_body form li.payment_summary_amount{
	width: auto;
	float: right;
	clear: none;
}
#main_body form ul.payment_list_items li{
	width: 98%;
	font-size: 95%;
	padding-top: 0px;
	padding-bottom: 5px;
}
#main_body form ul.payment_list_items li span{
	margin: 0px;
	float: right;
	display: block;
	font-weight: bold;
	padding: 0px;
	padding-left: 10px;
	color: inherit;
}
#main_body form li.payment_summary_term{
	text-align: right;
	font-size: 90%;
	padding: 15px 0;
}
#main_body form li#li_accepted_cards{
	margin-bottom: 10px;
}
#li_accepted_cards img{
	height: 27px;
}
#main_body form ul.payment_detail_form{
	margin-top: 20px
}
#main_body form li.credit_card div span{
	padding-bottom: 8px;
}
#main_body form li.credit_card div span#li_cc_span_3{
	width: 75%;
}
#main_body form li.credit_card div span#li_cc_span_4{
	width: 21%;
}
#cc_secure_icon{
	float: left;
	margin-top:5px;
}
#cc_expiry_month{
	width: 23%;
}
#cc_expiry_year{
	width: 11%;
}
#li_billing_address span.state_list,
#li_shipping_address span.state_list{
	padding-bottom: 12px !important;
}
#li_shipping_address div.shipping_address_detail{
	content: "";
    display: table;
  	clear: both;
}
#li_credit_card{
	padding-bottom: 5px !important;
	margin-bottom: 20px !important;
}
EOT;
		foreach ($form_id_array as $form_id) {
			$target_css_file = $mf_settings['data_dir']."/form_{$form_id}/css/view.css";
			if(file_exists($target_css_file) && is_writable($target_css_file)){
				file_put_contents($target_css_file, $new_css_code, FILE_APPEND);
			}
		}

		return $post_install_error;
	}

	/***************************************************************************
	 Changelog 3.4 to 3.5 												   
	 
	 - new table ap_email_logic
	 - new table ap_email_logic_conditions
	 
	 - ap_forms: added columns  form_disabled_message, payment_enable_tax, payment_tax_rate, logic_email_enable
	 - ap_form_elements: added columns  element_number_enable_quantity, element_number_quantity_link

	 - update ap_forms records, set the value of 'form_disabled_message' to be coming from the language setting for each form.

	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	
	function do_delta_update_3_4_to_3_5($dbh,$options=array()){

		global $mf_lang;

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Create table ap_email_logic
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."email_logic` (
												    		  `form_id` int(11) NOT NULL,
															  `rule_id` int(11) NOT NULL,
															  `rule_all_any` varchar(3) NOT NULL DEFAULT 'all',
															  `target_email` text NOT NULL,
															  `template_name` varchar(15) NOT NULL DEFAULT 'notification' COMMENT 'notification - confirmation - custom',
															  `custom_from_name` text,
															  `custom_from_email` varchar(255) NOT NULL DEFAULT '',
															  `custom_subject` text,
															  `custom_content` text,
															  `custom_plain_text` int(1) NOT NULL DEFAULT '0',
															  PRIMARY KEY (`form_id`,`rule_id`)
												) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Create table ap_email_logic_conditions
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."email_logic_conditions` (
												    		  `aec_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `target_rule_id` int(11) NOT NULL,
															  `element_name` varchar(50) NOT NULL DEFAULT '',
															  `rule_condition` varchar(15) NOT NULL DEFAULT 'is',
															  `rule_keyword` varchar(255) DEFAULT NULL,
															  PRIMARY KEY (`aec_id`),
															  KEY `form_id` (`form_id`,`target_rule_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		
		//3. Alter ap_forms table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."forms` 
														  ADD COLUMN `form_disabled_message` text,
														  ADD COLUMN `payment_enable_tax` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `payment_tax_rate` decimal(62,2) NOT NULL DEFAULT '0.00',
														  ADD COLUMN `logic_email_enable` tinyint(1) NOT NULL DEFAULT '0';";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//4. Alter ap_form_elements table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_elements` 
														  ADD COLUMN `element_number_enable_quantity` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_number_quantity_link` varchar(15) DEFAULT NULL;";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//5. Update ap_forms records, set the value of 'form_disabled_message' to be coming from the language setting for each form 
		$form_language_settings = array();

		$query  = "select `form_id`,`form_language` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
	
		$i=0;
		while($row = mf_do_fetch_result($sth)){
			$form_language_settings[$i]['form_id'] = $row['form_id'];

			if(!empty($row['form_language'])){
				$form_language_settings[$i]['form_language'] = $row['form_language'];
			}else{
				$form_language_settings[$i]['form_language'] = 'english';
			}
			$i++;
		}

		if(!empty($form_language_settings)){
			foreach ($form_language_settings as $value) {
				
				mf_set_language($value['form_language']);

				$query = "UPDATE `".MF_TABLE_PREFIX."forms` SET `form_disabled_message`=? where form_id=?";
				
				$params = array($mf_lang['form_inactive'],$value['form_id']);
				$sth = $dbh->prepare($query);
				try{
					$sth->execute($params);
				}catch(PDOException $e) {
					$post_install_error .= $e->getMessage().'<br/><br/>';
				}
			}
		}
		

		

		return $post_install_error;
	}

	/***************************************************************************
	 Changelog 3.5 to 4.0.beta 												   
	 
	- new table ap_webhook_options
	- new table ap_webhook_parameters
	- new table ap_reports
	- new table ap_report_elements
	- new table ap_report_filters
	- new table ap_grid_columns

	- ap_forms: added column 'payment_enable_discount','payment_discount_type','payment_discount_amount','payment_discount_code', 'payment_discount_element_id','payment_discount_max_usage','payment_discount_expiry_date','webhook_enable'
	- ap_forms: added 'payment_authorizenet_live_apiloginid','payment_authorizenet_live_transkey','payment_authorizenet_test_apiloginid','payment_authorizenet_test_transkey','payment_authorizenet_enable_test_mode','payment_authorizenet_save_cc_data'
	- ap forms: added payment_paypal_rest_live_clientid, payment_paypal_rest_live_secret_key,payment_paypal_rest_test_clientid,payment_paypal_rest_test_secret_key,payment_paypal_rest_enable_test_mode column
	- ap_forms: added colums:
					* payment_braintree_live_merchant_id
					* payment_braintree_live_public_key
					* payment_braintree_live_private_key
					* payment_braintree_live_encryption_key

					* payment_braintree_test_merchant_id
					* payment_braintree_test_public_key
					* payment_braintree_test_private_key
					* payment_braintree_test_encryption_key

					* payment_braintree_enable_test_mode

	- ap_entries_preferences: added column 'entries_incomplete_sort_by','entries_incomplete_enable_filter','entries_incomplete_filter_type'
	- ap_form_filters: added column 'incomplete_entries'
	- ap_column_preferences: added column 'incomplete_entries'

	- add a block of new CSS code to all view.css files on all forms
	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	
	function do_delta_update_3_5_to_4_0_beta($dbh,$options=array()){

		global $mf_lang;

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Create table ap_webhook_options
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."webhook_options` (
												    		  `awo_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `rule_id` int(11) NOT NULL DEFAULT '0',
															  `webhook_url` text,
															  `webhook_method` varchar(4) NOT NULL DEFAULT 'post',
															  `webhook_format` varchar(10) NOT NULL DEFAULT 'key-value',
															  `webhook_raw_data` mediumtext,
															  `enable_http_auth` int(1) DEFAULT '0',
															  `http_username` varchar(255) DEFAULT NULL,
															  `http_password` varchar(255) DEFAULT NULL,
															  `enable_custom_http_headers` int(1) NOT NULL DEFAULT '0',
															  `custom_http_headers` text,
															  PRIMARY KEY (`awo_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Create table ap_webhook_parameters
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."webhook_parameters` (
												    		  `awp_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `rule_id` int(11) NOT NULL DEFAULT '0',
															  `param_name` text,
															  `param_value` text,
															  PRIMARY KEY (`awp_id`),
															  KEY `form_id` (`form_id`,`rule_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Create table ap_reports
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."reports` (
												    		  `form_id` int(11) NOT NULL,
															  `report_access_key` varchar(100) DEFAULT NULL,
															  PRIMARY KEY (`form_id`),
															  KEY `report_access_key` (`report_access_key`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//4. Create table ap_report_elements
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."report_elements` (
												    		  `access_key` varchar(100) DEFAULT NULL,
															  `form_id` int(11) NOT NULL,
															  `chart_id` int(11) NOT NULL,
															  `chart_position` int(11) NOT NULL DEFAULT '0',
															  `chart_status` int(1) NOT NULL DEFAULT '1',
															  `chart_datasource` varchar(25) NOT NULL DEFAULT '',
															  `chart_type` varchar(25) NOT NULL DEFAULT '',
															  `chart_enable_filter` int(1) NOT NULL DEFAULT '0',
															  `chart_filter_type` varchar(5) NOT NULL DEFAULT 'all',
															  `chart_title` text,
															  `chart_title_position` varchar(10) NOT NULL DEFAULT 'top',
															  `chart_title_align` varchar(10) NOT NULL DEFAULT 'center',
															  `chart_width` int(11) NOT NULL DEFAULT '0',
															  `chart_height` int(11) NOT NULL DEFAULT '400',
															  `chart_background` varchar(8) DEFAULT NULL,
															  `chart_theme` varchar(25) NOT NULL DEFAULT 'blueopal',
															  `chart_legend_visible` int(1) NOT NULL DEFAULT '1',
															  `chart_legend_position` varchar(10) NOT NULL DEFAULT 'right',
															  `chart_labels_visible` int(1) NOT NULL DEFAULT '1',
															  `chart_labels_position` varchar(10) NOT NULL DEFAULT 'outsideEnd',
															  `chart_labels_template` varchar(255) NOT NULL DEFAULT '#= category #',
															  `chart_labels_align` varchar(10) NOT NULL DEFAULT 'circle',
															  `chart_tooltip_visible` int(1) NOT NULL DEFAULT '1',
															  `chart_tooltip_template` varchar(255) NOT NULL DEFAULT '#= category # - #= dataItem.entry # - #= kendo.format(''{0:P}'', percentage)#',
															  `chart_gridlines_visible` int(1) NOT NULL DEFAULT '1',
															  `chart_bar_color` varchar(8) DEFAULT NULL,
															  `chart_is_stacked` int(1) NOT NULL DEFAULT '0',
															  `chart_is_vertical` int(1) NOT NULL DEFAULT '0',
															  `chart_line_style` varchar(6) NOT NULL DEFAULT 'smooth',
															  `chart_axis_is_date` int(1) NOT NULL DEFAULT '0',
															  `chart_date_range` varchar(6) NOT NULL DEFAULT 'all' COMMENT 'all,period,custom',
															  `chart_date_period_value` int(11) NOT NULL DEFAULT '1',
															  `chart_date_period_unit` varchar(5) NOT NULL DEFAULT 'day',
															  `chart_date_axis_baseunit` varchar(5) DEFAULT NULL,
															  `chart_date_range_start` date DEFAULT NULL,
															  `chart_date_range_end` date DEFAULT NULL,
															  `chart_grid_page_size` int(11) NOT NULL DEFAULT '10',
															  `chart_grid_max_length` int(11) NOT NULL DEFAULT '100',
															  PRIMARY KEY (`form_id`,`chart_id`),
															  KEY `access_key` (`access_key`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//5. Create table ap_report_filters
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."report_filters` (
												    		  `arf_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `chart_id` int(11) NOT NULL,
															  `element_name` varchar(50) NOT NULL DEFAULT '',
															  `filter_condition` varchar(15) NOT NULL DEFAULT 'is',
															  `filter_keyword` varchar(255) DEFAULT NULL,
															  PRIMARY KEY (`arf_id`),
															  KEY `form_id` (`form_id`,`chart_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//6. Create table ap_grid_columns
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."grid_columns` (
												    		  `agc_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `chart_id` int(11) NOT NULL,
															  `element_name` varchar(255) NOT NULL DEFAULT '',
															  `position` int(11) NOT NULL,
															  PRIMARY KEY (`agc_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}
		
		//7. Alter ap_forms table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."forms` 
														  ADD COLUMN `payment_enable_discount` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `payment_discount_type` varchar(12) NOT NULL DEFAULT 'percent_off',
														  ADD COLUMN `payment_discount_amount` decimal(62,2) NOT NULL DEFAULT '0.00',
														  ADD COLUMN `payment_discount_code` text,
														  ADD COLUMN `payment_discount_element_id` int(11) DEFAULT NULL,
														  ADD COLUMN `payment_discount_max_usage` int(11) NOT NULL DEFAULT '0',
														  ADD COLUMN `payment_discount_expiry_date` date DEFAULT NULL,
														  ADD COLUMN `webhook_enable` tinyint(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `webhook_url` text,
														  ADD COLUMN `webhook_method` varchar(4) NOT NULL DEFAULT 'post',
														  ADD COLUMN `payment_paypal_rest_live_clientid` varchar(100) DEFAULT NULL,
														  ADD COLUMN `payment_paypal_rest_live_secret_key` varchar(100) DEFAULT NULL,
														  ADD COLUMN `payment_paypal_rest_test_clientid` varchar(100) DEFAULT NULL,
														  ADD COLUMN `payment_paypal_rest_test_secret_key` varchar(100) DEFAULT NULL,
														  ADD COLUMN `payment_paypal_rest_enable_test_mode` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `payment_authorizenet_live_apiloginid` varchar(50) DEFAULT NULL,
														  ADD COLUMN `payment_authorizenet_live_transkey` varchar(50) DEFAULT NULL,
														  ADD COLUMN `payment_authorizenet_test_apiloginid` varchar(50) DEFAULT NULL,
														  ADD COLUMN `payment_authorizenet_test_transkey` varchar(50) DEFAULT NULL,
														  ADD COLUMN `payment_authorizenet_enable_test_mode` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `payment_authorizenet_save_cc_data` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `payment_braintree_live_merchant_id` varchar(50) DEFAULT NULL,
														  ADD COLUMN `payment_braintree_live_public_key` varchar(50) DEFAULT NULL,
														  ADD COLUMN `payment_braintree_live_private_key` varchar(50) DEFAULT NULL,
														  ADD COLUMN `payment_braintree_live_encryption_key` text,
														  ADD COLUMN `payment_braintree_test_merchant_id` varchar(50) DEFAULT NULL,
														  ADD COLUMN `payment_braintree_test_public_key` varchar(50) DEFAULT NULL,
														  ADD COLUMN `payment_braintree_test_private_key` varchar(50) DEFAULT NULL,
														  ADD COLUMN `payment_braintree_test_encryption_key` text,
														  ADD COLUMN `payment_braintree_enable_test_mode` int(1) NOT NULL DEFAULT '0';";
																							  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//8. Alter ap_entries_preferences table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."entries_preferences` 
														  ADD COLUMN `entries_incomplete_sort_by` varchar(100) NOT NULL DEFAULT 'id-desc',
														  ADD COLUMN `entries_incomplete_enable_filter` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `entries_incomplete_filter_type` varchar(5) NOT NULL DEFAULT 'all' COMMENT 'all or any';";
														  																				  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}
		
		//9. Alter ap_form_filters table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_filters` 
														  ADD COLUMN `incomplete_entries` int(1) NOT NULL DEFAULT '0';";
														  																				  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//10. Alter ap_column_preferences table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."column_preferences` 
														  ADD COLUMN `incomplete_entries` int(1) NOT NULL DEFAULT '0';";
														  																				  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//11. Loop through each form CSS file and add new CSS code
		$query  = "select `form_id` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
	
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];
			$form_id_array[] = $form_id;
		}

		$new_css_code = <<<EOT
#main_body form li.total_payment span.total_extra{
	clear: both; 
	margin-top: 10px; 
	text-align: right
}
#main_body form li.total_payment span.total_extra h5{
	font-weight: 700;
}
/** File Upload - HTML5 **/
.uploadifive-button {
	background-color: #505050;
	background-image: linear-gradient(bottom, #505050 0%, #707070 100%);
	background-image: -o-linear-gradient(bottom, #505050 0%, #707070 100%);
	background-image: -moz-linear-gradient(bottom, #505050 0%, #707070 100%);
	background-image: -webkit-linear-gradient(bottom, #505050 0%, #707070 100%);
	background-image: -ms-linear-gradient(bottom, #505050 0%, #707070 100%);
	background-image: -webkit-gradient(
		linear,
		left bottom,
		left top,
		color-stop(0, #505050),
		color-stop(1, #707070)
	);
	background-position: center top;
	background-repeat: no-repeat;
	-webkit-border-radius: 30px;
	-moz-border-radius: 30px;
	border-radius: 30px;
	border: 2px solid #808080;
	color: #FFF !important;
	font: bold 12px Arial, Helvetica, sans-serif;
	text-align: center;
	text-shadow: 0 -1px 0 rgba(0,0,0,0.25);
	text-transform: uppercase;
	width: 100%;
	margin: 0 !important;
	padding: 0 !important;
}
.uploadifive-button:hover {
	background-color: #606060;
	background-image: linear-gradient(top, #606060 0%, #808080 100%);
	background-image: -o-linear-gradient(top, #606060 0%, #808080 100%);
	background-image: -moz-linear-gradient(top, #606060 0%, #808080 100%);
	background-image: -webkit-linear-gradient(top, #606060 0%, #808080 100%);
	background-image: -ms-linear-gradient(top, #606060 0%, #808080 100%);
	background-image: -webkit-gradient(
		linear,
		left bottom,
		left top,
		color-stop(0, #606060),
		color-stop(1, #808080)
	);
	background-position: center bottom;
}
.uploadifive-queue-item {
	background-color: #F5F5F5 !important;
	border: 2px solid #E5E5E5 !important;
	font: 11px Verdana, Geneva, sans-serif;
	margin-top: 5px !important;
	padding: 10px !important;
	width: 350px;
}
.uploadifive-queue-item.error {
	background-color: #FDE5DD !important;
	border: 2px solid #FBCBBC !important;
}
.uploadifive-queue-item .close {
	background: url('../../../images/icons/delete.png') 0 0 no-repeat;
	display: block;
	float: right;
	height: 16px;
	text-indent: -9999px;
	width: 16px;
}
.uploadifive-queue-item .progress {
	border: 1px solid #D0D0D0;
	height: 3px;
	margin: 8px 0 0 0 !important;
	width: 100%;
	padding: 0 !important;
	clear: both;
}
.uploadifive-queue-item .progress-bar {
	background-color: #0099FF;
	height: 3px;
	width: 0;
	padding: 0 !important;
}
.uploadifive-queue-item div{
	overflow: auto;
	padding-bottom: 0 !important;
}
.uploadifive-queue-item span{
	width: auto !important;
}
.uploadifive-queue-item span.fileinfo{
	margin-left: 5px !important;
}
EOT;
		foreach ($form_id_array as $form_id) {
			$target_css_file = $mf_settings['data_dir']."/form_{$form_id}/css/view.css";
			if(file_exists($target_css_file) && is_writable($target_css_file)){
				file_put_contents($target_css_file, $new_css_code, FILE_APPEND);
			}
		}
		


		return $post_install_error;
	}

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_4_0_beta_to_4_0($dbh,$options=array()){
		//there is no changes between 4.0.beta to 4.0
		//only the version number need to be changed

		return '';
	}

	/***************************************************************************
	 Changelog 4.0 to 4.1 												   
	 
	 - ap_forms: added column 'esl_replyto_email_address','esr_replyto_email_address'
	 - ap_email_logic: added column 'custom_replyto_email'
	 
	1) Loop through each form
	..copy value from 'esl_from_email_address' to 'esl_replyto_email_address'
	..check into 'esl_from_email_address' field 
	....if the field doesn't contain email address, replace it with the Default From Email

	..copy value from 'esr_from_email_address' to 'esr_replyto_email_address'
	..check into 'esr_from_email_address' field 
	....if the field doesn't contain email address, replace it with the Default From Email

	2) Loop through each record within ap_email_logic table
    ..copy value from 'custom_from_email' to 'custom_replyto_email'
    ..check into 'custom_from_email' field
    ....if the field doesn't contain email address, replace it with the Default From Email
	 
	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	
	function do_delta_update_4_0_to_4_1($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Alter ap_forms table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."forms` 
														  ADD COLUMN `esl_replyto_email_address` varchar(255) DEFAULT NULL,
														  ADD COLUMN `esr_replyto_email_address` varchar(255) DEFAULT NULL;";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Alter ap_email_logic table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."email_logic` ADD COLUMN `custom_replyto_email` varchar(255) NOT NULL DEFAULT '';";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Update ap_forms table 
		//   Copy value from 'esl_from_email_address' to 'esl_replyto_email_address'
		//   Copy value from 'esr_from_email_address' to 'esr_replyto_email_address'
		$query = "UPDATE ".MF_TABLE_PREFIX."forms set esl_replyto_email_address=esl_from_email_address,esr_replyto_email_address=esr_from_email_address";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//4. Update ap_email_logic table
		//   Copy value from 'custom_from_email' to 'custom_replyto_email'
		$query = "update ".MF_TABLE_PREFIX."email_logic set custom_replyto_email=custom_from_email";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//5. Loop through each record within ap_forms table 
		$query  = "select `form_id`,`esl_from_email_address`,`esr_from_email_address` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
		
		$form_email_data = array();
		$i=0;
		while($row = mf_do_fetch_result($sth)){
			$form_email_data[$i]['form_id'] = $row['form_id'];
			$form_email_data[$i]['esl_from_email_address'] = $row['esl_from_email_address'];
			$form_email_data[$i]['esr_from_email_address'] = $row['esr_from_email_address'];
			$i++;
		}

		if(!empty($form_email_data)){
			$default_from_email = $mf_settings['default_from_email'];

			foreach ($form_email_data as $value) {
				$form_id = $value['form_id'];

				$query = '';
				if((strpos($value['esl_from_email_address'], '@') === false) && (strpos($value['esr_from_email_address'], '@') === false)){
					$query = "UPDATE ".MF_TABLE_PREFIX."forms set esl_from_email_address = ?,esr_from_email_address = ? where form_id = ?";
					$params = array($default_from_email,$default_from_email,$form_id);
				}else if(strpos($value['esl_from_email_address'], '@') === false){
					$query = "UPDATE ".MF_TABLE_PREFIX."forms set esl_from_email_address = ? where form_id = ?";
					$params = array($default_from_email,$form_id);
				}else if(strpos($value['esr_from_email_address'], '@') === false){
					$query = "UPDATE ".MF_TABLE_PREFIX."forms set esr_from_email_address = ? where form_id = ?";
					$params = array($default_from_email,$form_id);
				}

				if(!empty($query)){
					$sth = $dbh->prepare($query);
					try{
						$sth->execute($params);
					}catch(PDOException $e) {
						$post_install_error .= $e->getMessage().'<br/><br/>';
					}
				}

			}
		}

		//6. Loop through each record within ap_email_logic table
		//check into custom_from_email_address field
		//if the field doesn't contain email address, replace it with the Default From Email
		$query  = "select `form_id`,`rule_id`,`custom_from_email` from ".MF_TABLE_PREFIX."email_logic";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
		
		$form_email_logic_data = array();
		$i=0;
		while($row = mf_do_fetch_result($sth)){
			$form_email_logic_data[$i]['form_id'] = $row['form_id'];
			$form_email_logic_data[$i]['rule_id'] = $row['rule_id'];
			$form_email_logic_data[$i]['custom_from_email'] = $row['custom_from_email'];
			$i++;
		}

		if(!empty($form_email_logic_data)){
			$default_from_email = $mf_settings['default_from_email'];

			foreach ($form_email_logic_data as $value) {
				$form_id = $value['form_id'];
				$rule_id = $value['rule_id'];

				if(strpos($value['custom_from_email'], '@') === false){
					$query = "UPDATE ".MF_TABLE_PREFIX."email_logic set custom_from_email = ? where form_id = ? and rule_id = ?";
					$params = array($default_from_email,$form_id,$rule_id);

					$sth = $dbh->prepare($query);
					try{
						$sth->execute($params);
					}catch(PDOException $e) {
						$post_install_error .= $e->getMessage().'<br/><br/>';
					}
				}

			}
		}
		

		return $post_install_error;
	}

	/***************************************************************************
	 Changelog 4.1 to 4.2 												   
	 - create new table: ap_webhook_logic_conditions
	 
	 - ap_settings: added column 'enforce_tsv','enable_ip_restriction','ip_whitelist','enable_account_locking',
	 				'account_lock_period','account_lock_max_attempts','default_form_theme_id'
	 - ap_users: added column 'tsv_enable','tsv_secret','tsv_code_log','login_attempt_date','login_attempt_count'
	 - ap_forms: added column 'logic_webhook_enable','form_custom_script_enable','form_custom_script_url'
	 - ap_webhook_options: added column 'rule_all_any'
	 
	 - loop through each form: update 'session_id' length on all ap_form_x_review tables. Set the length to 128 instead of 100.
	 
	 - run these queries:
			ALTER TABLE `ap_field_logic_conditions` ADD INDEX (`form_id`,`target_element_id`);
			ALTER TABLE `ap_email_logic_conditions` ADD INDEX (`form_id`,`target_rule_id`);
			ALTER TABLE `ap_page_logic_conditions` ADD INDEX (`form_id`,`target_page_id`);
			update ap_form_themes set field_bg_color='#FBFBFB' where field_bg_color = '#ffffff' and theme_id <= 22;
			update ap_form_themes set border_form_width=0 where border_form_width=1 and theme_id <= 22;
	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	
	function do_delta_update_4_1_to_4_2($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Create table ap_webhook_logic_conditions
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."webhook_logic_conditions` (
												    		  `wlc_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `target_rule_id` int(11) NOT NULL,
															  `element_name` varchar(50) NOT NULL DEFAULT '',
															  `rule_condition` varchar(15) NOT NULL DEFAULT 'is',
															  `rule_keyword` varchar(255) DEFAULT NULL,
															  PRIMARY KEY (`wlc_id`),
															  KEY `form_id` (`form_id`,`target_rule_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Alter ap_settings table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."settings` 
														  ADD COLUMN `enforce_tsv` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `enable_ip_restriction` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `ip_whitelist` text,
														  ADD COLUMN `enable_account_locking` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `account_lock_period` int(11) NOT NULL DEFAULT '30',
														  ADD COLUMN `account_lock_max_attempts` int(11) NOT NULL DEFAULT '6',
														  ADD COLUMN `default_form_theme_id` int(11) NOT NULL DEFAULT '0';";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Alter ap_users table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."users` 
														  ADD COLUMN `tsv_enable` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `tsv_secret` varchar(16) DEFAULT NULL,
														  ADD COLUMN `tsv_code_log` varchar(100) DEFAULT NULL,
														  ADD COLUMN `login_attempt_date` datetime DEFAULT NULL,
														  ADD COLUMN `login_attempt_count` int(11) NOT NULL DEFAULT '0';";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//4. Alter ap_forms table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."forms` 
														  ADD COLUMN `logic_webhook_enable` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `form_custom_script_enable` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `form_custom_script_url` text;";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//5. Alter ap_webhook_options table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."webhook_options` 
														  ADD COLUMN `rule_all_any` varchar(3) NOT NULL DEFAULT 'all';";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//6. Update 'session_id' length on all ap_form_x_review tables. Set the length to 128 instead of 100. 
		$query = "select `form_id` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
				
		$form_id_array = array();
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];
			$form_id_array[] = $form_id;
		}
				
		foreach ($form_id_array as $form_id) {
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}_review` CHANGE `session_id` `session_id` VARCHAR(128) NULL;";
			
			$params = array();
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				//do nothing, ignore if table review not exist
			}
		}

		//7. Run several UPDATE queries
		$misc_update_query_array = array();

		$misc_update_query_array[] = "ALTER TABLE `".MF_TABLE_PREFIX."field_logic_conditions` ADD INDEX (`form_id`,`target_element_id`)";
		$misc_update_query_array[] = "ALTER TABLE `".MF_TABLE_PREFIX."email_logic_conditions` ADD INDEX (`form_id`,`target_rule_id`)";
		$misc_update_query_array[] = "ALTER TABLE `".MF_TABLE_PREFIX."page_logic_conditions` ADD INDEX (`form_id`,`target_page_id`)";
		$misc_update_query_array[] = "UPDATE ".MF_TABLE_PREFIX."form_themes SET field_bg_color='#FBFBFB' WHERE field_bg_color = '#ffffff' and theme_id <= 22";
		$misc_update_query_array[] = "UPDATE ".MF_TABLE_PREFIX."form_themes SET border_form_width=0 WHERE border_form_width=1 and theme_id <= 22";
		
		foreach ($misc_update_query_array as $query) {
			$params = array();
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				$post_install_error .= $e->getMessage().'<br/><br/>';
			}
		}

		return $post_install_error;
	}

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_4_2_to_4_2_3($dbh,$options=array()){
		//there is no database changes between 4.2 to 4.2.3
		//only the version number need to be changed

		return '';
	}

	/***************************************************************************
	 Changelog 4.2.3 to 4.3 												   
	 - ap_settings: added column 'recaptcha_site_key','recaptcha_secret_key','ldap_enable','ldap_type','ldap_host','ldap_port','ldap_encryption','ldap_basedn','ldap_account_suffix','ldap_required_group','ldap_exclusive'
	  
	 - run these queries:
			ALTER TABLE `ap_form_payments` ADD INDEX (`form_id`,`record_id`);
	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	
	function do_delta_update_4_2_3_to_4_3($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Alter ap_settings table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."settings` 
														  ADD COLUMN `recaptcha_site_key` varchar(255) DEFAULT NULL,
														  ADD COLUMN `recaptcha_secret_key` varchar(255) DEFAULT NULL,
														  ADD COLUMN `ldap_enable` tinyint(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `ldap_type` varchar(11) NOT NULL DEFAULT 'ad' COMMENT 'ad,openldap',
														  ADD COLUMN `ldap_host` varchar(255) DEFAULT NULL,
														  ADD COLUMN `ldap_port` int(11) NOT NULL DEFAULT '389',
														  ADD COLUMN `ldap_encryption` varchar(11) NOT NULL DEFAULT 'none' COMMENT 'none,ssl,tls',
														  ADD COLUMN `ldap_basedn` varchar(255) DEFAULT NULL,
														  ADD COLUMN `ldap_account_suffix` varchar(100) DEFAULT NULL,
														  ADD COLUMN `ldap_required_group` varchar(255) DEFAULT NULL,
														  ADD COLUMN `ldap_exclusive` tinyint(1) NOT NULL DEFAULT '0';";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Run several UPDATE queries
		$misc_update_query_array = array();

		$misc_update_query_array[] = "ALTER TABLE `".MF_TABLE_PREFIX."form_payments` ADD INDEX (`form_id`,`record_id`)";
		
		foreach ($misc_update_query_array as $query) {
			$params = array();
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				$post_install_error .= $e->getMessage().'<br/><br/>';
			}
		}

		return $post_install_error;
	}

	/***************************************************************************
	 Changelog 4.3 to 4.4 												   
	 - ap_forms: added columns 'esl_pdf_enable','esl_pdf_content','esr_pdf_enable','esr_pdf_content','form_name_hide','form_unique_ip_maxcount','form_unique_ip_period'
	 - ap_email_logic: added columns 'custom_pdf_enable', 'custom_pdf_content','delay_notification_until_paid'
	 - ap_webhook_options: added columns 'delay_notification_until_paid'
	 - ap_form_elements: added columns 'element_is_readonly','element_choice_limit_rule','element_choice_limit_qty',
									  'element_choice_limit_range_min','element_choice_limit_range_max','element_enable_placeholder',
									  'element_choice_max_entry'
	 - ap_report_elements: added columns 'chart_grid_sort_by'

	 run this query to all ap_form_XX tables:
	 --------
	 ALTER TABLE `ap_form_XXXX` ADD INDEX (`ip_address`);
	 ALTER TABLE `ap_form_XXXX` ADD INDEX (`date_created`);

	 run this single query:
	 ----------------
	 1) update ap_forms set payment_enable_merchant = 0 where payment_enable_merchant = -1
	 2) ALTER TABLE `ap_forms` CHANGE `payment_enable_merchant` `payment_enable_merchant` INT(1)  NOT NULL  DEFAULT '0';

	 tasks:
	 ------
	 loop through each "form_unique_ip" records, if the value is 1, set 'form_unique_ip_period' to 'l' (lifetime) and set 'form_unique_ip_maxcount' to 1

	 Add the following CSS code to all view.css files:
	--------
	#main_body form li div span.label
	{
		clear:both;
		color:#444;
		display:block;
		font-size:85%;
		line-height:15px;
		margin:0;
		padding-top:3px;
	}

	#main_body form li div span.label var
	{
		font-style: normal;
		font-weight: bold;
	}
	#main_body span.description
	{
		border:none;
		color:#444;
		display:block;
		font-size:95%;
		font-weight:700;
		line-height:150%;
		padding:0 0 1px;
		float: none;
	}

	#main_body form.left_label span.description{
		float: left;
		margin: 0 15px 0 0;
		width: 29%;
	}

	#main_body form.right_label span.description{
		float: left;
		margin: 0 15px 0 0;
		width: 29%;
		text-align: right;
	}
	.no_guidelines form.left_label span.description,
	.no_guidelines form.right_label span.description{
		width: 30% !important;
	}
	#main_body form li fieldset{
		margin: 0;
		padding:0;
		border: none;
	}
	--------
	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	
	function do_delta_update_4_3_to_4_4($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Alter ap_forms table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."forms` 
														  ADD COLUMN `esl_pdf_enable` tinyint(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `esl_pdf_content` mediumtext,
														  ADD COLUMN `esr_pdf_enable` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `esr_pdf_content` mediumtext,
														  ADD COLUMN `form_name_hide` tinyint(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `form_unique_ip_maxcount` int(11) NOT NULL DEFAULT '5',
														  ADD COLUMN `form_unique_ip_period` char(1) NOT NULL DEFAULT 'd' COMMENT 'h,d,w,m,y,l (hour/day/week/month/year/lifetime)';";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Alter ap_email_logic table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."email_logic` 
														  ADD COLUMN `custom_pdf_enable` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `custom_pdf_content` text,
														  ADD COLUMN `delay_notification_until_paid` int(1) NOT NULL DEFAULT '0';";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Alter ap_webhook_options table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."webhook_options` 
														  ADD COLUMN `delay_notification_until_paid` int(1) NOT NULL DEFAULT '0';";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//4. Alter ap_form_elements table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_elements` 
														  ADD COLUMN `element_is_readonly` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_choice_limit_rule` varchar(12) NOT NULL DEFAULT 'atleast',
														  ADD COLUMN `element_choice_limit_qty` int(11) NOT NULL DEFAULT '1',
														  ADD COLUMN `element_choice_limit_range_min` int(11) NOT NULL DEFAULT '1',
														  ADD COLUMN `element_choice_limit_range_max` int(11) NOT NULL DEFAULT '1',
														  ADD COLUMN `element_enable_placeholder` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_choice_max_entry` int(11) NOT NULL DEFAULT '0';";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//5. Alter ap_report_elements table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."report_elements` 
														  ADD COLUMN `chart_grid_sort_by` varchar(100) NOT NULL DEFAULT 'id-desc';";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//6. Add indexes (ip_address, date_created) on all ap_form_x tables
		$query = "select `form_id` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
				
		$form_id_array = array();
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];
			$form_id_array[] = $form_id;
		}
				
		foreach ($form_id_array as $form_id) {
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD INDEX (`ip_address`);";
			
			$params = array();
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				//do nothing, ignore if table review not exist
			}
		}

		foreach ($form_id_array as $form_id) {
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD INDEX (`date_created`);";
			
			$params = array();
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				//do nothing, ignore if table review not exist
			}
		}

		//7. Run several UPDATE queries
		$misc_update_query_array = array();

		$misc_update_query_array[] = "UPDATE ".MF_TABLE_PREFIX."forms set payment_enable_merchant = 0 WHERE payment_enable_merchant = -1";
		$misc_update_query_array[] = "UPDATE ".MF_TABLE_PREFIX."forms set form_unique_ip_period = 'l',form_unique_ip_maxcount = 1 WHERE form_unique_ip = 1";
		$misc_update_query_array[] = "ALTER TABLE `".MF_TABLE_PREFIX."forms` CHANGE `payment_enable_merchant` `payment_enable_merchant` INT(1)  NOT NULL  DEFAULT '0'";
		
		foreach ($misc_update_query_array as $query) {
			$params = array();
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				$post_install_error .= $e->getMessage().'<br/><br/>';
			}
		}
		
		//8. Loop through each view.css file and add new code
		$new_css_code = <<<EOT
#main_body form li div span.label
{
	clear:both;
	color:#444;
	display:block;
	font-size:85%;
	line-height:15px;
	margin:0;
	padding-top:3px;
}

#main_body form li div span.label var
{
	font-style: normal;
	font-weight: bold;
}
#main_body span.description
{
	border:none;
	color:#444;
	display:block;
	font-size:95%;
	font-weight:700;
	line-height:150%;
	padding:0 0 1px;
	float: none;
}

#main_body form.left_label span.description{
	float: left;
	margin: 0 15px 0 0;
	width: 29%;
}

#main_body form.right_label span.description{
	float: left;
	margin: 0 15px 0 0;
	width: 29%;
	text-align: right;
}
.no_guidelines form.left_label span.description,
.no_guidelines form.right_label span.description{
	width: 30% !important;
}
#main_body form li fieldset{
	margin: 0;
	padding:0;
	border: none;
}
EOT;
		foreach ($form_id_array as $form_id) {
			$target_css_file = $mf_settings['data_dir']."/form_{$form_id}/css/view.css";
			if(file_exists($target_css_file) && is_writable($target_css_file)){
				file_put_contents($target_css_file, $new_css_code, FILE_APPEND);
			}
		}

		return $post_install_error;
	}

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_4_4_to_4_5($dbh,$options=array()){
		//there is no database structure changes between 4.4 to 4.5
		//only the version number need to be changed

		return '';
	}

	/***************************************************************************
	 Changelog 4.5 to 4.6

	 new tables:
		- ap_success_logic_conditions
		- ap_success_logic_options
	
	 modified tables:
		- ap_forms: added columns 'payment_enable_setupfee','payment_setupfee_amount','logic_success_enable','esl_bcc_email_address',
								  'esr_bcc_email_address','form_resume_subject','form_resume_content','form_resume_from_name',
								  'form_resume_from_email_address'
		- ap_email_logic: added columns 'custom_bcc'
	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	
	function do_delta_update_4_5_to_4_6($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Create table ap_success_logic_conditions
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."success_logic_conditions` (
												    		  `slc_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `target_rule_id` int(11) NOT NULL,
															  `element_name` varchar(50) NOT NULL DEFAULT '',
															  `rule_condition` varchar(15) NOT NULL DEFAULT 'is',
															  `rule_keyword` varchar(255) DEFAULT NULL,
															  PRIMARY KEY (`slc_id`),
															  KEY `form_id` (`form_id`,`target_rule_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Create table ap_success_logic_options
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."success_logic_options` (
												    		  `slo_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `rule_id` int(11) NOT NULL DEFAULT '0',
															  `rule_all_any` varchar(3) NOT NULL DEFAULT 'all',
															  `success_type` varchar(11) NOT NULL DEFAULT 'message' COMMENT 'message;redirect',
															  `success_message` text,
															  `redirect_url` text,
															  PRIMARY KEY (`slo_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		
		//3. Alter ap_forms table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."forms` 
														  ADD COLUMN `payment_enable_setupfee` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `payment_setupfee_amount` decimal(62,2) NOT NULL DEFAULT '0.00',
														  ADD COLUMN `logic_success_enable` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `esl_bcc_email_address` text,
														  ADD COLUMN `esr_bcc_email_address` text,
														  ADD COLUMN `form_resume_subject` text,
														  ADD COLUMN `form_resume_content` mediumtext,
														  ADD COLUMN `form_resume_from_name` text,
														  ADD COLUMN `form_resume_from_email_address` varchar(255) DEFAULT '';";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//4. Alter ap_email_logic table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."email_logic` ADD COLUMN `custom_bcc` text";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}


		return $post_install_error;
	}

	/***************************************************************************
	 Changelog 4.6 to 4.7

	 modified tables:
		- ap_forms: added columns 'payment_stripe_enable_receipt','payment_stripe_receipt_element_id'
		- ap_permissions: added columns 'edit_report'
		- ap_form_elements: added columns 'element_text_default_type','element_text_default_length','element_text_default_random_type',
								  'element_text_default_prefix','element_text_default_case'

	 run this single query:
	 ---------------- 
	 update ap_permissions set edit_report = edit_form;
	
	 add the following CSS code to all view.css files:
	 ---------
	 #main_body form.left_label li div.mf_sig_wrapper, #main_body form.right_label li div.mf_sig_wrapper
	 {
		float: left;
		width: 309px;
	 }
	 #main_body form.left_label li .mf_sigpad_clear, #main_body form.right_label li .mf_sigpad_clear
	 {
		float: left;
	 }
	 ---------
	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	
	function do_delta_update_4_6_to_4_7($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Alter ap_forms table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."forms` 
														  ADD COLUMN `payment_stripe_enable_receipt` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `payment_stripe_receipt_element_id` int(11) DEFAULT NULL;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Alter ap_permissions table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."permissions` ADD COLUMN `edit_report` tinyint(1) NOT NULL DEFAULT '0'";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Alter ap_form_elements table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_elements` 
														  ADD COLUMN `element_text_default_type` varchar(6) NOT NULL DEFAULT 'static',
														  ADD COLUMN `element_text_default_length` int(11) NOT NULL DEFAULT '10',
														  ADD COLUMN `element_text_default_random_type` varchar(8) NOT NULL DEFAULT 'letter',
														  ADD COLUMN `element_text_default_prefix` varchar(50) DEFAULT NULL,
														  ADD COLUMN `element_text_default_case` char(1) NOT NULL DEFAULT 'u';";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//4. run single query to update ap_permissions
		$query = "UPDATE ".MF_TABLE_PREFIX."permissions SET edit_report = edit_form;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//5. Loop through the view.css file of each form and add new code
		$query = "select `form_id` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
				
		$form_id_array = array();
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];
			$form_id_array[] = $form_id;
		}

		$new_css_code = <<<EOT
#main_body form.left_label li div.mf_sig_wrapper, #main_body form.right_label li div.mf_sig_wrapper
{
	float: left;
	width: 309px;
}
#main_body form.left_label li .mf_sigpad_clear, #main_body form.right_label li .mf_sigpad_clear
{
	float: left;
}
EOT;
		foreach ($form_id_array as $form_id) {
			$target_css_file = $mf_settings['data_dir']."/form_{$form_id}/css/view.css";
			if(file_exists($target_css_file) && is_writable($target_css_file)){
				file_put_contents($target_css_file, $new_css_code, FILE_APPEND);
			}
		}

		return $post_install_error;
	}

	/***************************************************************************
	 Changelog 4.7 to 4.8

	 modified tables:
	 -----------
	 - ap_settings: added column 'timezone'
	 - ap_users: added column 'reset_hash','reset_date'

	 new tables:
	 -----------
	 - ap_form_themes_files

	 add the following CSS code to all view.css files:
	 ---------
	 #main_body form li.error span.description
	 {
		color:#DF0000 !important;
	 }
	 ---------
	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	
	function do_delta_update_4_7_to_4_8($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Alter ap_settings table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."settings` ADD COLUMN `timezone` varchar(100) DEFAULT NULL;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Alter ap_users table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."users` 
														  ADD COLUMN `reset_hash` varchar(100) DEFAULT NULL,
														  ADD COLUMN `reset_date` datetime DEFAULT NULL;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Create table ap_form_themes_files
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_themes_files` (
													  		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `file_name` text NOT NULL,
															  `file_content` longblob,
															  PRIMARY KEY (`id`),
															  KEY `file_name` (`file_name`(255))
													) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//4. Loop through the view.css file of each form and add new code
		$query = "select `form_id` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
				
		$form_id_array = array();
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];
			$form_id_array[] = $form_id;
		}

		$new_css_code = <<<EOT
#main_body form li.error span.description
{
	color:#DF0000 !important;
}
EOT;
		foreach ($form_id_array as $form_id) {
			$target_css_file = $mf_settings['data_dir']."/form_{$form_id}/css/view.css";
			if(file_exists($target_css_file) && is_writable($target_css_file)){
				file_put_contents($target_css_file, $new_css_code, FILE_APPEND);
			}
		}

		return $post_install_error;
	}

	/***************************************************************************
	 Changelog 4.8 to 5

	 modified tables:
	 -----------
	 - ap_forms: added column 'form_approval_enable','form_created_date','form_created_by'

	 new tables:
	 -----------
	 - ap_approval_settings
	 - ap_approvers
	 - ap_approvers_conditions
	 - ap_dashboard_filters

	 do the following:
	 ---------
	 check into ap_permissions table, make sure to fill the 'form_created_date' & 'form_created_by' 
	 on ap_forms with values from the ap_permissions table
	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	
	function do_delta_update_4_8_to_5($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Alter ap_forms table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."forms` ADD COLUMN `form_approval_enable` tinyint(1) NOT NULL DEFAULT '0',
														 ADD COLUMN `form_created_date` date DEFAULT NULL,
														 ADD COLUMN `form_created_by` int(11) DEFAULT NULL;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Create table ap_approval_settings
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."approval_settings` (
												    		  `form_id` int(11) NOT NULL,
															  `workflow_type` varchar(11) NOT NULL DEFAULT 'parallel' COMMENT 'parallel,serial',
															  `parallel_workflow` varchar(11) NOT NULL DEFAULT 'any' COMMENT 'any,all',
															  `revision_no` int(11) NOT NULL,
															  `revision_date` datetime default NULL,
															  `last_revised_by` int(11) NOT NULL,
															  PRIMARY KEY (`form_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Create table ap_approvers
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."approvers` (
												    		  `form_id` int(11) NOT NULL,
															  `user_id` int(11) NOT NULL,
															  `rule_all_any` varchar(3) NOT NULL DEFAULT 'all' COMMENT 'all,any',
															  `user_position` int(11) NOT NULL,
															  PRIMARY KEY (`form_id`,`user_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//4. Create table ap_approvers_conditions
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."approvers_conditions` (
												    		  `aac_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `target_user_id` int(11) NOT NULL,
															  `element_name` varchar(50) NOT NULL DEFAULT '',
															  `rule_condition` varchar(15) NOT NULL DEFAULT 'is',
															  `rule_keyword` varchar(255) DEFAULT NULL,
															  PRIMARY KEY (`aac_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//5. Create table ap_dashboard_filters
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."dashboard_filters` (
												    		  `adf_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `user_id` int(11) NOT NULL,
															  `filter_id` int(11) NOT NULL,
															  `filter_name` varchar(255) NOT NULL DEFAULT '',
															  `filter_active` tinyint(1) NOT NULL DEFAULT '0',
															  `filter_by` varchar(5) NOT NULL DEFAULT 'title',
															  `filter_keyword` varchar(255) DEFAULT NULL,
															  PRIMARY KEY (`adf_id`)
															) DEFAULT CHARACTER SET utf8;";
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//6. check into ap_permissions table, make sure to fill the 'form_created_by' 
	 	//   on ap_forms with values from the ap_permissions table (this is just best-effort attempt)
		$query = "select form_id,min(user_id) user_id from ".MF_TABLE_PREFIX."permissions where edit_form=1 group by form_id";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
				
		$form_perms_array = array();
		$i=0;
		while($row = mf_do_fetch_result($sth)){
			$form_perms_array[$i]['form_id'] = $row['form_id'];
			$form_perms_array[$i]['user_id'] = $row['user_id'];
			$i++;
		}

		//update ap_forms
		foreach ($form_perms_array as $value) {
			$query = "update ".MF_TABLE_PREFIX."forms set form_created_by=? where form_id=?";
			$params = array($value['user_id'],$value['form_id']);

			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				$post_install_error .= $e->getMessage().'<br/><br/>';
			}
		}

		return $post_install_error;
	}

	/***************************************************************************
	 Changelog 5 to 6
	
	 modified tables:
	 -----------------
	 - ap_form_elements: added column 'element_email_enable_confirmation','element_email_confirm_field_label',
									 'element_date_disable_dayofweek','element_date_disabled_dayofweek_list',
									 'element_is_encrypted'
	 - ap_forms: added column 'form_encryption_enable','form_encryption_public_key'

	 do the following:
	 -----------------
	 - check all 'element_date_disable_weekend' value, if enabled make sure to enable 'element_date_disable_dayofweek' 
	 and set the value of 'element_date_disable_dayofweek_list' with weekends days	
	 - remove 'element_date_disable_weekend' column from ap_form_elements table
	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_5_to_6($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Alter ap_forms table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."forms` ADD COLUMN `form_encryption_enable` tinyint(1) NOT NULL DEFAULT '0',
														 ADD COLUMN `form_encryption_public_key` text;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Alter ap_form_elements table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_elements` 
														  ADD COLUMN `element_email_enable_confirmation` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_email_confirm_field_label` varchar(255) DEFAULT NULL,
														  ADD COLUMN `element_date_disable_dayofweek` int(1) NOT NULL DEFAULT '0',
														  ADD COLUMN `element_date_disabled_dayofweek_list` varchar(15) DEFAULT NULL,
														  ADD COLUMN `element_is_encrypted` int(1) NOT NULL DEFAULT '0';";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. check all 'element_date_disable_weekend' value, if enabled make sure to enable 'element_date_disable_dayofweek' 
	 	//   and set the value of 'element_date_disable_dayofweek_list' with weekends days	
	 	//   -> element_date_disable_dayofweek=1
		//   -> element_date_disabled_dayofweek_list=0,6
		$query = "UPDATE 
						`".MF_TABLE_PREFIX."form_elements` 
					 SET 
					 	element_date_disable_dayofweek=1,
					 	element_date_disabled_dayofweek_list='0,6' 
				   WHERE 
				   		element_type in('date','europe_date') AND 
				   		element_date_disable_weekend=1;";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//4. remove 'element_date_disable_weekend' column from ap_form_elements table
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_elements` DROP COLUMN `element_date_disable_weekend`;";
														  
		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		return $post_install_error;
	}

	/***************************************************************************
	 Changelog 6 to 6.1
	
	 modified tables:
	 -----------------
	 - ap_element_options: added column 'option_is_hidden'
	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_6_to_6_1($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Alter element_options table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."element_options` ADD COLUMN `option_is_hidden` int(11) NOT NULL DEFAULT '0';";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		return $post_install_error;
	}

	/***************************************************************************
	 Changelog 6.1 to 7
	
	 modified tables:
	 -----------------
	 - ap_form_elements: added columns 'element_media_type','element_media_image_src','element_media_image_width','element_media_image_height',
	 								   'element_media_image_alignment','element_media_image_alt','element_media_image_href',
	 								   'element_media_display_in_email','element_media_video_src','element_media_video_size',
	 								   'element_media_video_muted','element_media_video_caption_file'

	 - ap_forms: added columns 'payment_authorizenet_enable_email','payment_authorizenet_email_element_id'

	 new tables:
	 ------------
	 - ap_form_images_files

	 others:
	 -------
	 replace all view.css files and keep backup of the old view.css file as view.old.css
	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_6_1_to_7($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Alter ap_form_elements table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_elements` 
														    ADD COLUMN `element_media_type` varchar(8) NOT NULL DEFAULT 'image',
															ADD COLUMN `element_media_image_src` text,
															ADD COLUMN `element_media_image_width` int(11) DEFAULT NULL,
															ADD COLUMN `element_media_image_height` int(11) DEFAULT NULL,
															ADD COLUMN `element_media_image_alignment` char(1) NOT NULL DEFAULT 'l',
															ADD COLUMN `element_media_image_alt` text,
															ADD COLUMN `element_media_image_href` text,
															ADD COLUMN `element_media_display_in_email` int(1) NOT NULL DEFAULT '0',
															ADD COLUMN `element_media_video_src` text,
															ADD COLUMN `element_media_video_size` varchar(6) NOT NULL DEFAULT 'large',
															ADD COLUMN `element_media_video_muted` int(1) NOT NULL DEFAULT '0',
															ADD COLUMN `element_media_video_caption_file` text;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Alter ap_forms table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."forms` 
														ADD COLUMN `payment_authorizenet_enable_email` int(1) NOT NULL DEFAULT '0',
  														ADD COLUMN `payment_authorizenet_email_element_id` int(11) DEFAULT NULL;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Create ap_form_images_files table
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_images_files` (
													    		  `form_id` int(11) NOT NULL,
																  `file_name` text NOT NULL,
																  `file_content` longblob,
																  KEY `form_id` (`form_id`,`file_name`(100))
															) DEFAULT CHARACTER SET utf8;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//4. Replace all view.css file with the new one and keep backup of the old file
		$query  = "select `form_id` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
	
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];
			$form_id_array[] = $form_id;
		}

		foreach ($form_id_array as $form_id) {
			$target_css_file 		= $mf_settings['data_dir']."/form_{$form_id}/css/view.css";
			$target_css_file_backup = $mf_settings['data_dir']."/form_{$form_id}/css/view.old.css";

			if(file_exists($target_css_file)){
				rename($target_css_file, $target_css_file_backup);

				$old_mask = umask(0);
				copy("./view.css",$mf_settings['data_dir']."/form_{$form_id}/css/view.css");
				umask($old_mask);
			}
		}

		return $post_install_error;
	}

	/***************************************************************************
	 Changelog 7 to 8
	
	 modified tables:
	 -----------------
	 - ap_forms: added column 'payment_stripe_enable_payment_request_button','payment_stripe_account_country'

	 - run this query on upgrade.php (bugfix with recaptcha2 being deprecated):  
			UPDATE ap_forms SET form_captcha_type = 'n' WHERE form_captcha_type = 'r';

	 - add the following CSS code to all view.css files:
	 ----
	 #main_body form li.hide_cents .sub_currency{
	 	display: none;
	 }
	 #main_body form ul.payment_detail_form li span.description{
	 	margin-bottom: 5px;
	 }
	 #stripe-card-element {
	 	border: 4px solid #EFEFEF;
	 	border-radius: 8px;
	 	box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.2) inset;
	 	outline: medium none;
	 	background: none repeat scroll 0 0 #FBFBFB;
	 	padding: 0 0 0 5px !important;
	 	color:#666666;
	 	font-size:100%;
	 	margin-bottom:15px !important;
	 	width: 75%;
	 }
	 #main_body form li.media{
	 	width: 97% !important;
	 }
	 ----

	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_7_to_8($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Alter ap_forms table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."forms` 
														ADD COLUMN `payment_stripe_enable_payment_request_button` int(1) NOT NULL DEFAULT '0',
  														ADD COLUMN `payment_stripe_account_country` varchar(2) DEFAULT NULL;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Run query to update ap_forms captcha
		$query = "UPDATE ".MF_TABLE_PREFIX."forms SET form_captcha_type = 'n' WHERE form_captcha_type = 'r'";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Loop through the view.css file of each form and add new code
		$query = "select `form_id` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
				
		$form_id_array = array();
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];
			$form_id_array[] = $form_id;
		}

		$new_css_code = <<<EOT
#main_body form li.hide_cents .sub_currency{
	display: none;
}
#main_body form ul.payment_detail_form li span.description{
	margin-bottom: 5px;
}
#stripe-card-element {
	border: 4px solid #EFEFEF;
	border-radius: 8px;
	box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.2) inset;
	outline: medium none;
	background: none repeat scroll 0 0 #FBFBFB;
	padding: 0 0 0 5px !important;
	color:#666666;
	font-size:100%;
	margin-bottom:15px !important;
	width: 75%;
}
#main_body form li.media{
	width: 97% !important;
}
EOT;
		foreach ($form_id_array as $form_id) {
			$target_css_file = $mf_settings['data_dir']."/form_{$form_id}/css/view.css";
			if(file_exists($target_css_file) && is_writable($target_css_file)){
				file_put_contents($target_css_file, $new_css_code, FILE_APPEND);
			}
		}

		return $post_install_error;
	}

	/***************************************************************************
	 Changelog 8 to 9
	
	 modified tables:
	 -----------------
	 - ap_settings: added column 'enable_data_retention','data_retention_period'
	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_8_to_9($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Alter ap_settings table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."settings` 
														ADD COLUMN `enable_data_retention` tinyint(1) NOT NULL DEFAULT '0',
  														ADD COLUMN `data_retention_period` int(11) NOT NULL DEFAULT '38' COMMENT 'months';";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		return $post_install_error;
	}

	/***************************************************************************
	 Changelog 9 to 10

	 new table:
	 -----------------
	 - ap_integrations
	
	 modified tables:
	 -----------------
	 - ap_settings: added column 'googleapi_clientid','googleapi_clientsecret'

	 Minimum PHP version changed to PHP 5.4
	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_9_to_10($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Create ap_integrations table
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."integrations` (
												    		  `ai_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `form_id` int(11) NOT NULL,
															  `gsheet_integration_status` tinyint(1) NOT NULL DEFAULT '0',
															  `gsheet_spreadsheet_id` varchar(255) DEFAULT NULL,
															  `gsheet_spreadsheet_url` text,
															  `gsheet_elements` text,
															  `gsheet_create_new_sheet` tinyint(1) NOT NULL DEFAULT '0',
															  `gsheet_refresh_token` varchar(255) DEFAULT NULL,
															  `gsheet_access_token` varchar(255) DEFAULT NULL,
															  `gsheet_token_create_date` datetime DEFAULT NULL,
															  `gsheet_linked_user_id` int(11) DEFAULT NULL,
															  `gsheet_delay_notification_until_paid` tinyint(1) NOT NULL DEFAULT '1',
															  `gsheet_delay_notification_until_approved` tinyint(1) NOT NULL DEFAULT '1',
															  PRIMARY KEY (`ai_id`)
															) DEFAULT CHARACTER SET utf8;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Alter ap_settings table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."settings` 
														ADD COLUMN `googleapi_clientid` varchar(255) DEFAULT NULL,
  														ADD COLUMN `googleapi_clientsecret` varchar(255) DEFAULT NULL;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		return $post_install_error;
	}

	/***************************************************************************
	 Changelog 10 to 11

	 modified tables:
	 -----------------
	 - ap_integrations : all 'gcal_xxxx' columns

	 Minimum PHP version changed to PHP 5.5
	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_10_to_11($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Alter ap_integrations table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."integrations` 
														ADD COLUMN `gcal_integration_status` tinyint(1) NOT NULL DEFAULT '0',
														ADD COLUMN `gcal_refresh_token` varchar(255) DEFAULT NULL,
														ADD COLUMN `gcal_access_token` varchar(255) DEFAULT NULL,
														ADD COLUMN `gcal_token_create_date` datetime DEFAULT NULL,
														ADD COLUMN `gcal_linked_user_id` int(11) DEFAULT NULL,
														ADD COLUMN `gcal_calendar_id` varchar(255) DEFAULT NULL,
														ADD COLUMN `gcal_event_title` text,
														ADD COLUMN `gcal_event_desc` text,
														ADD COLUMN `gcal_event_location` text,
														ADD COLUMN `gcal_event_allday` tinyint(1) NOT NULL DEFAULT '1',
														ADD COLUMN `gcal_start_datetime` datetime DEFAULT NULL,
														ADD COLUMN `gcal_start_date_element` int(11) DEFAULT NULL,
														ADD COLUMN `gcal_start_time_element` int(11) DEFAULT NULL,
														ADD COLUMN `gcal_start_date_type` varchar(11) NOT NULL DEFAULT 'datetime' COMMENT 'datetime,element',
														ADD COLUMN `gcal_start_time_type` varchar(11) NOT NULL DEFAULT 'datetime' COMMENT 'datetime,element',
														ADD COLUMN `gcal_end_datetime` datetime DEFAULT NULL,
														ADD COLUMN `gcal_end_date_element` int(11) DEFAULT NULL,
														ADD COLUMN `gcal_end_time_element` int(11) DEFAULT NULL,
														ADD COLUMN `gcal_end_time_type` varchar(11) NOT NULL DEFAULT 'datetime' COMMENT 'datetime,element',
														ADD COLUMN `gcal_end_date_type` varchar(11) NOT NULL DEFAULT 'datetime' COMMENT 'datetime,element',
														ADD COLUMN `gcal_duration_type` varchar(11) NOT NULL DEFAULT 'period' COMMENT 'period,datetime',
														ADD COLUMN `gcal_duration_period_length` int(11) NOT NULL DEFAULT '30',
														ADD COLUMN `gcal_duration_period_unit` varchar(11) NOT NULL DEFAULT 'minute' COMMENT 'minute,hour,day',
														ADD COLUMN `gcal_attendee_email` int(11) DEFAULT NULL,
														ADD COLUMN `gcal_delay_notification_until_paid` tinyint(1) NOT NULL DEFAULT '1',
														ADD COLUMN `gcal_delay_notification_until_approved` tinyint(1) NOT NULL DEFAULT '1';";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Alter ap_integrations table. Change column 'gsheet_integration_status' default value
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."integrations` CHANGE `gsheet_integration_status` `gsheet_integration_status` TINYINT(1)  NOT NULL  DEFAULT '0';";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		return $post_install_error;
	}

	/***************************************************************************
	 Changelog 10 to 11

	 modified tables:
	 -----------------
	 - ap_form_elements: added column 'element_media_pdf_src'

	 new tables:
	 -----------
	 - ap_form_pdf_files

	 Run the following SQL:
	 ----------------------
	 - update ap_form_elements set element_guidelines = 'I understand this is a legal representation of my signature.' where element_type = 'signature';
	 
	 Run the following table creation SQL to all existing form id:
	 --------
	 CREATE TABLE `ap_form_XXXX_log` (
	  `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `record_id` int(11) NOT NULL DEFAULT '0',
	  `log_time` datetime NOT NULL,
	  `log_user` text NOT NULL,
	  `log_origin` text NOT NULL,
	  `log_message` text NOT NULL,
	  PRIMARY KEY (`log_id`),
	  KEY `record_id` (`record_id`)
	 ) DEFAULT CHARSET=utf8;

	 Add the following CSS code to all view.css files:
	 ----
		#main_body form li div.media_pdf_container{
			margin: 0;
			padding: 0 0 10px 0;
		}
		#main_body form li div.media_pdf_small{
			height: 300px;
		}
		#main_body form li div.media_pdf_medium{
			height: 600px;
		}
		#main_body form li div.media_pdf_large{
			height: 900px;
		}
		.mf_signature_wrapper {
			border-radius: 5px;
			border: 1px solid #ccc;
			padding-bottom: 0px !important;
		}
		.mf_signature_clear{
			float: right;
			margin-right: 5px;
			margin-top: 5px;
			display: block;
		}
		.mf_signature_switch a{
			text-decoration: none;
			border-bottom: 1px dotted #3B699F;
			font-family: Arial, Verdana, Helvetica;
		}
		.mf_signature_switch a:visited{
			color: #3661A1;
		}
		.mf_signature_switch a.active{
			text-decoration: none;
			border-bottom: none;
			background-color: #6d6d6d;
			border-radius: 3px;
			padding: 5px;
			color: #fff;
		}
		.mf_signature_draw{
			margin-top: 5px !important;
		}
		#main_body form li.signature{
			width: 97% !important;
		}
		#main_body form li.signature div span {
			width:auto;
		}
	 
	***************************************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_11_to_12($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Alter ap_form_elements table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_elements` ADD COLUMN `element_media_pdf_src` text;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Create ap_form_pdf_files table
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_pdf_files` (
													    		  `form_id` int(11) NOT NULL,
																  `file_name` text NOT NULL,
																  `file_content` longblob,
																  KEY `form_id` (`form_id`,`file_name`(100))
															) DEFAULT CHARACTER SET utf8;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Run SQL
		$query = "UPDATE `".MF_TABLE_PREFIX."form_elements` SET element_guidelines = 'I understand this is a legal representation of my signature.' WHERE element_type = 'signature';";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//5. Create ap_form_xx_log table for all existing forms
		$query  = "select `form_id` from ".MF_TABLE_PREFIX."forms where form_active in(0,1)";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
	
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];
			$form_id_array[] = $form_id;
		}

		foreach ($form_id_array as $form_id) {
			$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_{$form_id}_log` (
												  `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
												  `record_id` int(11) NOT NULL DEFAULT '0',
												  `log_time` datetime NOT NULL,
												  `log_user` text NOT NULL,
												  `log_origin` text NOT NULL,
												  `log_message` text NOT NULL,
												  PRIMARY KEY (`log_id`),
												  KEY `record_id` (`record_id`)
												) DEFAULT CHARSET=utf8;";

			$params = array();
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				$post_install_error .= $e->getMessage().'<br/><br/>';
			}
		}

		//5. Update view.css files with the new one
		foreach ($form_id_array as $form_id) {
			$target_css_file 		= $mf_settings['data_dir']."/form_{$form_id}/css/view.css";

			if(file_exists($target_css_file)){
				@unlink($target_css_file);

				$old_mask = umask(0);
				copy("./view.css",$mf_settings['data_dir']."/form_{$form_id}/css/view.css");
				umask($old_mask);
			}
		}

		return $post_install_error;
	}

	/* Changelog 12 to 13 *************************************
	
	- New tables:
	---------------------
	- ap_folders, ap_folders_conditions, ap_form_stats

	- Modified tables:
	---------------------
	- ap_users: added new column 'user_admin_theme'
	- ap_settings: added new column 'disable_pdf_link'

	- Run the following SQL:
	------------------
	update ap_settings set admin_theme = '' where admin_theme in('green','red');
	update ap_settings set admin_theme = 'light' where admin_theme in('gray','brown');

	- custom tasks:
	-------
	- Convert the records on ap_dashboard_filters to ap_folders and then remove the ap_dashboard_filters table
	- Create default folder (folder_id=1,all folders,selected) for each existing user

	**********************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_12_to_13($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Create ap_folders table
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."folders` (
													    	  `user_id` int(11) NOT NULL,
															  `folder_id` int(11) NOT NULL,
															  `folder_position` int(11) NOT NULL,
															  `folder_name` varchar(255) NOT NULL DEFAULT '',
															  `folder_selected` tinyint(1) NOT NULL DEFAULT '0',
															  `rule_all_any` varchar(3) NOT NULL DEFAULT 'all',
															  PRIMARY KEY (`user_id`,`folder_id`)
															) DEFAULT CHARACTER SET utf8;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Create ap_folders_conditions table
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."folders_conditions` (
													    	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
															  `user_id` int(11) NOT NULL,
															  `folder_id` int(11) NOT NULL,
															  `element_name` varchar(50) NOT NULL DEFAULT '',
															  `rule_condition` varchar(15) NOT NULL DEFAULT 'is',
															  `rule_keyword` varchar(255) DEFAULT NULL,
															  PRIMARY KEY (`id`)
															) DEFAULT CHARACTER SET utf8;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Create ap_form_stats table
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_stats` (
													    	  `form_id` int(11) unsigned NOT NULL,
															  `total_entries` int(11) unsigned DEFAULT NULL,
															  `today_entries` int(11) unsigned DEFAULT NULL,
															  `last_entry_date` datetime DEFAULT NULL,
															  PRIMARY KEY (`form_id`)
															) DEFAULT CHARACTER SET utf8;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//4. Alter ap_users table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."users` ADD COLUMN `user_admin_theme` varchar(11) DEFAULT NULL;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//5. Alter ap_settings table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."settings` ADD COLUMN `disable_pdf_link` int(1) DEFAULT '0';";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//6. Run Custom SQL
		$query = "UPDATE `".MF_TABLE_PREFIX."settings` SET admin_theme = '' WHERE admin_theme IN('green','red');";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		$query = "UPDATE `".MF_TABLE_PREFIX."settings` SET admin_theme = 'light' WHERE admin_theme IN('gray','brown');";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//6. Custom Tasks
		//6.a. Create default folder (folder_id=1,all folders,selected) for each existing user
		//get all users first
		$query = "select `user_id` from ".MF_TABLE_PREFIX."users";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
				
		$user_id_array = array();
		while($row = mf_do_fetch_result($sth)){
			$user_id_array[] = $row['user_id'];
		}

		foreach ($user_id_array as $user_id) {
			$query = "INSERT INTO 
								`".MF_TABLE_PREFIX."folders`( 
											`user_id`, 
											`folder_id`, 
											`folder_position`, 
											`folder_name`, 
											`folder_selected`, 
											`rule_all_any`) 
						  VALUES (?, 1, 1, 'All Forms', 1, 'all');";

			$params = array($user_id);
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				$post_install_error .= $e->getMessage().'<br/><br/>';
			}
		}

		//6.b. Convert the records on ap_dashboard_filters to ap_folders
		$query = "SELECT 
						user_id,
						filter_name,
						(filter_id+1) as filter_id,
						if(filter_by = 'tags','tag',filter_by) filter_by,
						filter_keyword 
					FROM 
						`".MF_TABLE_PREFIX."dashboard_filters`";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
				
		$filters_array = array();
		$i=0;
		while($row = mf_do_fetch_result($sth)){
			$filters_array[$i]['user_id'] 		= $row['user_id'];
			$filters_array[$i]['filter_name'] 	= $row['filter_name'];
			$filters_array[$i]['filter_id'] 	= $row['filter_id'];
			$filters_array[$i]['filter_by'] 	= $row['filter_by'];
			$filters_array[$i]['filter_keyword'] = $row['filter_keyword'];
			$i++;
		}

		//insert into ap_folders and ap_folders_conditions
		if(!empty($filters_array)){
			foreach ($filters_array as $value) {
				$query = "INSERT INTO 
								`".MF_TABLE_PREFIX."folders`( 
											`user_id`, 
											`folder_id`, 
											`folder_position`, 
											`folder_name`, 
											`folder_selected`, 
											`rule_all_any`) 
						  VALUES (?, ?, ?, ?, 0, 'all');";

				$params = array($value['user_id'],$value['filter_id'],$value['filter_id'],$value['filter_name']);
				$sth = $dbh->prepare($query);
				try{
					$sth->execute($params);
				}catch(PDOException $e) {
					$post_install_error .= $e->getMessage().'<br/><br/>';
				}

				$query = "INSERT INTO 
								`".MF_TABLE_PREFIX."folders_conditions`( 
											`user_id`, 
											`folder_id`, 
											`element_name`, 
											`rule_condition`, 
											`rule_keyword`) 
						  VALUES (?, ?, ?, 'contains', ?);";

				$params = array($value['user_id'],$value['filter_id'],$value['filter_by'],$value['filter_keyword']);
				$sth = $dbh->prepare($query);
				try{
					$sth->execute($params);
				}catch(PDOException $e) {
					$post_install_error .= $e->getMessage().'<br/><br/>';
				}

			}
		}
		
		return $post_install_error;
	}

	/* Changelog 13 to 13.1 *************************************
	
	- New table:
	---------------------
	- ap_sessions

	- Modified tables:
	---------------------
	- ap_users: added new column 'folders_pinned'

	- Run the following SQL:
	------------------
	ALTER TABLE `ap_form_payments` DROP INDEX `form_id`;
	ALTER TABLE `ap_form_payments` ADD INDEX (`record_id`);
	ALTER TABLE `ap_form_payments` ADD INDEX (`form_id`);
	ALTER TABLE `ap_form_payments` ADD INDEX (`status`);

	- custom tasks:
	-------
	Go through all forms table (including review tables) and run the following query:
	ALTER TABLE `ap_form_xxx` CHANGE `resume_key` `resume_key` VARCHAR(64) DEFAULT NULL;

	**********************************************************/

	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_13_to_13_1($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//get all forms
		$query  = "select `form_id` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
	
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];
			$form_id_array[] = $form_id;
		}

		//1. Create ap_sessions table
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."sessions` (
													    	  	`id` varchar(255) NOT NULL,
  																`data` mediumtext NOT NULL,
  																`timestamp` int(255) NOT NULL,
  																PRIMARY KEY (`id`)
															) DEFAULT CHARACTER SET utf8;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Alter ap_users table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."users` ADD COLUMN `folders_pinned` tinyint(1) NOT NULL DEFAULT '0';";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Run Custom SQL
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_payments` DROP INDEX `form_id`;
				  ALTER TABLE `".MF_TABLE_PREFIX."form_payments` ADD INDEX (`record_id`);
				  ALTER TABLE `".MF_TABLE_PREFIX."form_payments` ADD INDEX (`form_id`);
				  ALTER TABLE `".MF_TABLE_PREFIX."form_payments` ADD INDEX (`status`);";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		
		//4. Go through all forms table (including review tables) and run the following query:
		//ALTER TABLE `ap_form_xxx` CHANGE `resume_key` `resume_key` VARCHAR(64) DEFAULT NULL;
		foreach ($form_id_array as $form_id) {
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` CHANGE `resume_key` `resume_key` VARCHAR(64) DEFAULT NULL;";

			$params = array();
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				//discard error
			}

			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}_review` CHANGE `resume_key` `resume_key` VARCHAR(64) DEFAULT NULL;";

			$params = array();
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				//discard error
			}
		}
		
		return $post_install_error;
	}


	/** Changelog 13.1 to 14
	- Go through all forms table (including review tables) and run the following query:
	ALTER TABLE `ap_form_xxx` ADD COLUMN `edit_key` VARCHAR(64) DEFAULT NULL;
	ALTER TABLE `ap_form_xxx` ADD UNIQUE INDEX (`edit_key`);


	- ap_forms: added new column 'form_entry_edit_enable','form_entry_edit_resend_notifications','form_entry_edit_rerun_logics'
							 'form_entry_edit_auto_disable','form_entry_edit_auto_disable_period','form_entry_edit_auto_disable_unit','form_entry_edit_hide_editlink'

	*/
	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_13_1_to_14($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//get all forms
		$query  = "select `form_id` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
	
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];
			$form_id_array[] = $form_id;
		}

		//1. Alter ap_forms table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."forms` 
													ADD COLUMN `form_entry_edit_enable` tinyint(1) NOT NULL DEFAULT '0',
													ADD COLUMN `form_entry_edit_resend_notifications` tinyint(1) NOT NULL DEFAULT '0',
													ADD COLUMN `form_entry_edit_rerun_logics` tinyint(1) NOT NULL DEFAULT '0',
													ADD COLUMN `form_entry_edit_auto_disable` tinyint(1) NOT NULL DEFAULT '0',
													ADD COLUMN `form_entry_edit_auto_disable_period` int(11) NOT NULL DEFAULT '1',
													ADD COLUMN `form_entry_edit_auto_disable_unit` char(1) NOT NULL DEFAULT 'r' COMMENT 'r,h,d',
													ADD COLUMN `form_entry_edit_hide_editlink` tinyint(1) NOT NULL DEFAULT '0';";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		
		//2. Go through all forms table (including review tables) and run queries
		foreach ($form_id_array as $form_id) {
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD COLUMN `edit_key` VARCHAR(64) DEFAULT NULL;";

			$params = array();
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				//discard error
			}

			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD UNIQUE INDEX (`edit_key`);";

			$params = array();
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				//discard error
			}

			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}_review` ADD COLUMN `edit_key` VARCHAR(64) DEFAULT NULL;";

			$params = array();
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				//discard error
			}

			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}_review` ADD UNIQUE INDEX (`edit_key`);";

			$params = array();
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				//discard error
			}
		}
		
		return $post_install_error;
	}

	/** Changelog v14 to v15 ****
	- ap_settings: add new column 'captcha_public_key','captcha_private_key' (CLOUD ALREADY HAVE THESE COLUMNS!)

	- Run the following queries:
	----
	ALTER TABLE `ap_forms` 
	 CHANGE `payment_stripe_live_secret_key` `payment_stripe_live_secret_key` VARCHAR(255)  DEFAULT NULL,
	 CHANGE `payment_stripe_live_public_key` `payment_stripe_live_public_key` VARCHAR(255)  DEFAULT NULL,
	 CHANGE `payment_stripe_test_secret_key` `payment_stripe_test_secret_key` VARCHAR(255)  DEFAULT NULL,
	 CHANGE `payment_stripe_test_public_key` `payment_stripe_test_public_key` VARCHAR(255)  DEFAULT NULL;
	----
	
	- Go through all view.css file and add this code:
	----
	#main_body input.button_text:not(.btn_secondary):hover{
		box-shadow: 0px 0px 1px 1px #666666;
	}
	#edit_info{
		width: 400px;
		margin: 0 auto;
		margin-bottom: 20px;
		padding: 10px;
		background-color: #333;
		box-shadow: 0 0 3px rgba(0, 0, 0, 0.4);
		font-family: Arial, Verdana, Helvetica;
		color: #fff;
		text-shadow: 0 1px 1px rgba(0, 0, 0, 0.25), 0 0 1px rgba(0, 0, 0, 0.3);
		border-radius: 2px;
		font-size: 14px;
	}
	#edit_info img{
		vertical-align: bottom;
		margin-right: 5px;
	}
	#edit_info a{
		text-decoration: none;
		text-transform: uppercase;
		color: #fff;
		margin-left: 25px;
	}
	#edit_info a:hover{
		text-decoration: underline;
	}
	#edit_info a:visited{
		color: #fff;
	}
	----*/


	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_14_to_15($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//1. Alter ap_settings table. Add new columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."settings` ADD COLUMN `captcha_public_key` text, ADD COLUMN `captcha_private_key` text;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Alter ap_forms table, change columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."forms` CHANGE `payment_stripe_live_secret_key` `payment_stripe_live_secret_key` VARCHAR(255)  DEFAULT NULL,
														 CHANGE `payment_stripe_live_public_key` `payment_stripe_live_public_key` VARCHAR(255)  DEFAULT NULL,
														 CHANGE `payment_stripe_test_secret_key` `payment_stripe_test_secret_key` VARCHAR(255)  DEFAULT NULL,
														 CHANGE `payment_stripe_test_public_key` `payment_stripe_test_public_key` VARCHAR(255)  DEFAULT NULL;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		
		$query  = "select `form_id` from ".MF_TABLE_PREFIX."forms where form_active in(0,1)";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
	
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];
			$form_id_array[] = $form_id;
		}

		//3. Update view.css files on all forms with the new one
		foreach ($form_id_array as $form_id) {
			$target_css_file 		= $mf_settings['data_dir']."/form_{$form_id}/css/view.css";

			if(file_exists($target_css_file)){
				@unlink($target_css_file);

				$old_mask = umask(0);
				copy("./view.css",$mf_settings['data_dir']."/form_{$form_id}/css/view.css");
				umask($old_mask);
			}
		}

		return $post_install_error;
	}

	/** Changelog 15 to 16
	- ALTER TABLE `ap_form_elements` CHANGE `element_file_type_list` `element_file_type_list` TEXT;
	- check into ap_form_elements table, if the value of "element_file_block_or_allow" is "b", then update the element_file_type_list with the new default accepted file types
  		(jpg, jpeg, png, gif, bmp, heic, pdf, docx, doc, xlsx, xls, ppt, pptx, txt, csv, zip, mp3, wma, mpg, mpeg, mp4, avi)

	- ALTER TABLE ap_form_elements DROP COLUMN `element_file_enable_advance`, DROP COLUMN `element_file_enable_type_limit`, DROP COLUMN `element_file_block_or_allow`;
	
	- run this query to all ap_form_XX tables:
	 ALTER TABLE `ap_form_XXXX` ADD INDEX (`status`);
	*/
	
	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_15_to_16($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//get all forms
		$query  = "select `form_id` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
	
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];
			$form_id_array[] = $form_id;
		}

		//1. Alter ap_form_elements table. Change column
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_elements` CHANGE `element_file_type_list` `element_file_type_list` TEXT;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		
		//3. Check into ap_form_elements table, if the value of "element_file_block_or_allow" is "b", then update the element_file_type_list with the new default accepted file types
  		//   (jpg, jpeg, png, gif, bmp, heic, pdf, docx, doc, xlsx, xls, ppt, pptx, txt, csv, zip, mp3, wma, mpg, mpeg, mp4, avi)
		$query = "UPDATE `".MF_TABLE_PREFIX."form_elements` SET `element_file_type_list`='jpg, jpeg, png, gif, bmp, heic, pdf, docx, doc, xlsx, xls, ppt, pptx, txt, csv, zip, mp3, wma, mpg, mpeg, mp4, avi' WHERE `element_file_block_or_allow`='b';";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Alter ap_form_elements table. Drop columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_elements` DROP COLUMN `element_file_enable_advance`, DROP COLUMN `element_file_enable_type_limit`, DROP COLUMN `element_file_block_or_allow`;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//4. Add index (status) on all ap_form_xxx tables
		foreach ($form_id_array as $form_id) {
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ADD INDEX (`status`);";
			
			$params = array();
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				//do nothing, ignore if table review not exist
			}
		}

		return $post_install_error;
	}

	/** Changelog 16 to 17
	- ap_forms: added new column 'form_keyword_blocking_enable','form_keyword_blocking_list',
								 'form_approval_email_subject','form_approval_email_content'
	- ap_form_elements: added new column 'element_rating_style','element_rating_max','element_rating_default'
									     'element_rating_enable_label','element_rating_label_high','element_rating_label_low'
	- ap_report_elements: added new column 'chart_rating_size','chart_rating_bars_visible', 'chart_rating_total_visible'

	- Add the following CSS code to all forms view.css files:
		---
		#main_body .rating .rating_label{
			margin: 5px 0 0 0;
			color: #444;
			font-size: 85%;
			line-height: 15px;
		}
		#main_body .rating .rating_label_low{
			float: left;
		}
		#main_body .rating .rating_label_high{
			float: right;
		}
		---
	*/
	
	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_16_to_17($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//get all forms
		$query  = "select `form_id` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
	
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];
			$form_id_array[] = $form_id;
		}

		//1. Alter ap_forms table. Add columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."forms` 
													 ADD COLUMN `form_keyword_blocking_enable` tinyint(1) NOT NULL DEFAULT '0',
													 ADD COLUMN `form_keyword_blocking_list` text,
													 ADD COLUMN `form_approval_email_subject` text,
													 ADD COLUMN `form_approval_email_content` mediumtext;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Alter ap_form_elements table. Add columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_elements` 
													 ADD COLUMN `element_rating_style` varchar(10) NOT NULL DEFAULT 'star',
													 ADD COLUMN `element_rating_max` int(11) NOT NULL DEFAULT '5',
													 ADD COLUMN `element_rating_default` int(11) NOT NULL DEFAULT '0',
													 ADD COLUMN `element_rating_enable_label` int(1) NOT NULL DEFAULT '0',
													 ADD COLUMN `element_rating_label_high` varchar(50) NOT NULL DEFAULT 'Best',
													 ADD COLUMN `element_rating_label_low` varchar(50) NOT NULL DEFAULT 'Worst';";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Alter ap_report_elements table. Add columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."report_elements` 
													 ADD COLUMN `chart_rating_size` varchar(6) NOT NULL DEFAULT 'medium',
													 ADD COLUMN `chart_rating_bars_visible` int(1) NOT NULL DEFAULT '1',
													 ADD COLUMN `chart_rating_total_visible` int(1) NOT NULL DEFAULT '1';";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//4. Loop through the view.css file of each form and add new code
		$new_css_code = <<<EOT
#main_body .rating .rating_label{
	margin: 5px 0 0 0;
	color: #444;
	font-size: 85%;
	line-height: 15px;
}
#main_body .rating .rating_label_low{
	float: left;
}
#main_body .rating .rating_label_high{
	float: right;
}
EOT;
		foreach ($form_id_array as $form_id) {
			$target_css_file = $mf_settings['data_dir']."/form_{$form_id}/css/view.css";
			if(file_exists($target_css_file) && is_writable($target_css_file)){
				file_put_contents($target_css_file, $new_css_code, FILE_APPEND);
			}
		}
		

		return $post_install_error;
	}

	/** Changelog 17 to 18
	- ap_form_elements: added new column 'element_address_subfields_visibility','element_address_subfields_labels','element_address_default_state'
	- ap_form_elements: remove column 'element_address_hideline2'

	Add the following CSS code to all form view.css file:
	--------------------
	#main_body form li.address span label{
		margin-bottom: 3px;
		padding-top: 0px;
	}
	#main_body form li.address span.description{
		margin-bottom: 3px;
	}
	---
	and many CSS changes related with address field, replace the whole content of view.css instead
	*/
	
	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_17_to_18($dbh,$options=array()){

		$post_install_error = '';

		$mf_settings = mf_get_settings($dbh);

		//get all forms
		$query  = "select `form_id` from ".MF_TABLE_PREFIX."forms";
		$params = array();
		$sth 	= mf_do_query($query,$params,$dbh);
	
		while($row = mf_do_fetch_result($sth)){
			$form_id = $row['form_id'];
			$form_id_array[] = $form_id;
		}

		//1. Alter ap_form_elements table. Add columns
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_elements` 
													 ADD COLUMN `element_address_subfields_visibility` text,
													 ADD COLUMN `element_address_subfields_labels` text,
													 ADD COLUMN `element_address_default_state` varchar(30) DEFAULT NULL;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//2. Alter ap_form_elements table. Remove column
		$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_elements` DROP COLUMN `element_address_hideline2`;";

		$params = array();
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$post_install_error .= $e->getMessage().'<br/><br/>';
		}

		//3. Loop through the view.css file of each form and replace the content from the main view.css file
		$new_css_code = file_get_contents("./view.css");

		foreach ($form_id_array as $form_id) {
			$target_css_file = $mf_settings['data_dir']."/form_{$form_id}/css/view.css";
			if(file_exists($target_css_file) && is_writable($target_css_file)){
				file_put_contents($target_css_file, $new_css_code);
			}
		}
		

		return $post_install_error;
	}

	/* Changelog: Security fix with upload.php file only, no database changes required **/
	//this function or any other do_delta_update_xxx functions should never call the create_xxx_tables functions
	//because those create_xxx_tables are intended for new installations and the structure might be changed over time
	function do_delta_update_18_to_18_1($dbh,$options=array()){

		$post_install_error = '';

		//do nothing
		return $post_install_error;
	}

?>