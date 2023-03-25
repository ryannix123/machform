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
	
	require('includes/language.php');
	require('includes/view-functions.php');
	require('includes/users-functions.php');

	
	$dbh = mf_connect_db();
	
	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);
	
	$form_id 	 = isset($_REQUEST['id']) ? (int) trim($_REQUEST['id']) : false;
	$unlock_hash = isset($_REQUEST['unlock']) ? trim($_REQUEST['unlock']) : false;
	
	$mf_properties = mf_get_form_properties($dbh,$form_id,array('form_active'));
	
	$is_new_form = false;
	
	//check the form_id
	//if blank or zero, create a new form first, otherwise load the form
	if(empty($form_id)){
		$is_new_form = true;
		//insert into ap_forms table and set the status to draft
		//set the status within 'form_active' field
		//form_active: 0 - Inactive / Disabled temporarily
		//form_active: 1 - Active
		//form_active: 2 - Draft
		//form_active: 9 - Deleted

		//check user privileges, is this user has privilege to create new form?
		if(empty($_SESSION['mf_user_privileges']['priv_new_forms'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to create new forms.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}


		//generate random form_id number, based on existing value
		$query = "select max(form_id) max_form_id from ".MF_TABLE_PREFIX."forms";
		$params = array();
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		if(empty($row['max_form_id'])){
			$last_form_id = 10000;
		}else{
			$last_form_id = $row['max_form_id'];
		}
		
		$form_id = $last_form_id + rand(100,1000);

		//insert into ap_permissions table, so that this user can add fields
		$query = "insert into ".MF_TABLE_PREFIX."permissions(form_id,user_id,edit_form,edit_entries,view_entries) values(?,?,1,1,1)";
		$params = array($form_id,$_SESSION['mf_user_id']);
		mf_do_query($query,$params,$dbh);
		
		//the default captcha is reCAPTCHA V2
		$default_form_captcha_type = 'n';
		
		$query = "INSERT INTO `".MF_TABLE_PREFIX."forms` (
							form_id,
							form_name,
							form_description,
							form_redirect,
							form_redirect_enable,
							form_active,
							form_success_message,
							form_password,
							form_frame_height,
							form_unique_ip,
							form_captcha,
							form_captcha_type,
							form_review,
							form_label_alignment,
							form_resume_enable,
							form_limit_enable,
							form_limit,
							form_language,
							form_schedule_enable,
							form_schedule_start_hour,
							form_schedule_end_hour,
							form_lastpage_title,
							form_submit_primary_text,
							form_submit_secondary_text,
							form_submit_primary_img,
							form_submit_secondary_img,
							form_submit_use_image,
							form_page_total,
							form_pagination_type,
							form_review_primary_text,
							form_review_secondary_text,
							form_review_primary_img,
							form_review_secondary_img,
							form_review_use_image,
							form_review_title,
							form_review_description,
							form_custom_script_enable,
							form_custom_script_url 
							)
					VALUES (?,
							'Untitled Form',
							'This is your form description. Click here to edit.',
							'',
							0,
							2,
							'Success! Your submission has been saved!',
							'',
							0,
							0,
							0,
							'{$default_form_captcha_type}',
							0,
							'top_label',
							0,
							0,
							0,
							'english',
							0,
							'',
							'',
							'Untitled Page',
							'Submit',
							'Previous',
							'',
							'',
							0,
							1,
							'steps',
							'Submit',
							'Previous',
							'',
							'',
							0,
							'Review Your Entry',
							'Please review your entry below. Click Submit button to finish.',
							0,
							''
						   );";
		mf_do_query($query,array($form_id),$dbh);
	}else{
		
		//check inactive form, inactive form settings should not displayed
		if(empty($mf_properties) || $mf_properties['form_active'] == null){
			$_SESSION['MF_DENIED'] = "This is not valid URL.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}else{
			$form_active = (int) $mf_properties['form_active'];
		
			if($form_active !== 0 && $form_active !== 1){
				$_SESSION['MF_DENIED'] = "This is not valid URL.";

				header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
				exit;
			}
		}
	
		//check permission, is the user allowed to access this page?
		if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
			$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

			//this page need edit_form permission
			if(empty($user_perms['edit_form'])){
				$_SESSION['MF_DENIED'] = "You don't have permission to edit this form.";

				header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
				exit;
			}
		}

		$is_form_locked = false;

		//get lock status for this form
		$query = "select lock_date from ".MF_TABLE_PREFIX."form_locks where form_id = ? and user_id <> ?";
		$params = array($form_id,$_SESSION['mf_user_id']);
	
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);		

		if(!empty($row['lock_date'])){
			$lock_date = strtotime($row['lock_date']);
			$current_date = date(time());
			
			$seconds_diff = $current_date - $lock_date;
			$lock_expiry_time = 60 * 60; //1 hour expiry
			
			//if there is a lock and the lock hasn't expired yet
			if($seconds_diff < $lock_expiry_time){
				$is_form_locked = true;
			}
		}

		//if the form is locked and no unlock key, redirect to warning page
		if($is_form_locked === true && empty($unlock_hash)){			
			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/form_locked.php?id=".$form_id);
			exit;
		}

		//if this is an existing form, delete the previous unsaved form fields
		$query = "DELETE FROM `".MF_TABLE_PREFIX."form_elements` where form_id = ? AND element_status='2'";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);
		
		//the ap_element_options table has "live" column, which has 3 possible values:
		// 0 - the option is being deleted
		// 1 - the option is active
		// 2 - the option is currently being drafted, not being saved yet and will be deleted by edit_form.php if the form is being edited the next time
		$query = "DELETE FROM `".MF_TABLE_PREFIX."element_options` where form_id = ? AND live='2'";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		//lock this form, to prevent other user editing the same form at the same time
		$query = "delete from ".MF_TABLE_PREFIX."form_locks where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		
		$new_lock_date = date("Y-m-d H:i:s");
		$query = "insert into ".MF_TABLE_PREFIX."form_locks(form_id,user_id,lock_date) values(?,?,?)";
		$params = array($form_id,$_SESSION['mf_user_id'],$new_lock_date);
		mf_do_query($query,$params,$dbh);

	}
	//get the HTML markup of the form
	$markup = mf_display_raw_form($dbh,$form_id);
	
	//get the properties for each form field
	//get form data
	$query 	= "select 
					 form_name,
					 form_name_hide,
					 form_active,
					 form_description,
					 form_redirect,
					 form_redirect_enable,
					 form_success_message,
					 form_password,
					 form_unique_ip,
					 form_unique_ip_maxcount,
					 form_unique_ip_period,
					 form_captcha,
					 form_captcha_type,
					 form_review,
					 form_resume_enable,
					 form_resume_subject,
					 form_resume_content,
					 form_resume_from_name,
					 form_resume_from_email_address,
					 form_limit_enable,
					 form_limit,
					 form_language,
					 form_frame_height,
					 form_label_alignment,
					 form_lastpage_title,
					 form_schedule_enable,
					 form_schedule_start_date,
					 form_schedule_end_date,
					 form_schedule_start_hour,
					 form_schedule_end_hour,
					 form_submit_primary_text,
					 form_submit_secondary_text,
					 form_submit_primary_img,
					 form_submit_secondary_img,
					 form_submit_use_image,
					 form_page_total,
					 form_pagination_type,
					 form_review_primary_text,
					 form_review_secondary_text,
					 form_review_primary_img,
					 form_review_secondary_img,
					 form_review_use_image,
					 form_review_title,
					 form_review_description,
					 form_custom_script_enable,
					 form_custom_script_url,
					 form_approval_enable,
					 form_encryption_enable,
					 form_encryption_public_key,
					 form_entry_edit_enable,
					 form_entry_edit_resend_notifications,
					 form_entry_edit_rerun_logics,
					 form_entry_edit_auto_disable,
					 form_entry_edit_auto_disable_period,
					 form_entry_edit_auto_disable_unit,
					 form_entry_edit_hide_editlink,
					 form_keyword_blocking_enable,
					 form_keyword_blocking_list,
					 form_approval_email_subject,
					 form_approval_email_content    
			     from 
			     	 ".MF_TABLE_PREFIX."forms 
			    where 
			    	 form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	$form = new stdClass();
	if(!empty($row)){
		$form->id 				= $form_id;
		$form->name 			= $row['form_name'];
		$form->name_hide 		= (int) $row['form_name_hide'];
		$form->active 			= (int) $row['form_active'];
		$form->description 		= $row['form_description'];
		$form->redirect 		= $row['form_redirect'];
		$form->redirect_enable 	= (int) $row['form_redirect_enable'];
		$form->approval_enable 	= (int) $row['form_approval_enable'];
		$form->success_message  = $row['form_success_message'];
		$form->password 		= $row['form_password'];
		$form->frame_height 	= $row['form_frame_height'];
		$form->unique_ip 		= (int) $row['form_unique_ip'];
		$form->unique_ip_maxcount = (int) $row['form_unique_ip_maxcount'];
		$form->unique_ip_period   = $row['form_unique_ip_period'];
		$form->captcha 			= (int) $row['form_captcha'];
		$form->captcha_type 	= $row['form_captcha_type'];
		$form->review 			= (int) $row['form_review'];
		$form->encryption_enable  	  = (int) $row['form_encryption_enable'];
		$form->encryption_public_key  = $row['form_encryption_public_key']; //this key is base64_encoded
		$form->entry_edit_enable 				= (int) $row['form_entry_edit_enable'];
		$form->entry_edit_resend_notifications 	= (int) $row['form_entry_edit_resend_notifications'];
		$form->entry_edit_rerun_logics 			= (int) $row['form_entry_edit_rerun_logics'];
		$form->entry_edit_auto_disable 			= (int) $row['form_entry_edit_auto_disable'];
		$form->entry_edit_auto_disable_period 	= (int) $row['form_entry_edit_auto_disable_period'];
		$form->entry_edit_auto_disable_unit 	= $row['form_entry_edit_auto_disable_unit'];
		$form->entry_edit_hide_editlink 		= (int) $row['form_entry_edit_hide_editlink'];
		$form->keyword_blocking_enable 			= (int) $row['form_keyword_blocking_enable'];
		$form->keyword_blocking_list 			= $row['form_keyword_blocking_list'];

		$form->review 			= (int) $row['form_review'];

		if(empty($row['form_language'])){
			$form->language		= 'english';
		}else{
			$form->language		= $row['form_language'];
		}
		mf_set_language($form->language);
		
		$form->resume_enable 	= (int) $row['form_resume_enable'];
		
		$form->resume_subject 	= $row['form_resume_subject'];
		if(empty($form->resume_subject)){
			$form->resume_subject = $mf_lang['resume_email_subject'];
		}

		$form->resume_content 	= $row['form_resume_content'];
		if(empty($form->resume_content)){
			$form->resume_content = $mf_lang['resume_email_content'];
		}

		$form->resume_from_name 			= $row['form_resume_from_name'];
		if(empty($form->resume_from_name)){
			$form->resume_from_name = html_entity_decode($mf_settings['default_from_name'],ENT_QUOTES);
		}

		$form->resume_from_email_address 	= $row['form_resume_from_email_address'];
		if(empty($form->resume_from_email_address)){
			$form->resume_from_email_address = $mf_settings['default_from_email'];
		}

		$form->approval_email_subject 	= $row['form_approval_email_subject'];
		if(empty($form->approval_email_subject)){
			$form->approval_email_subject = "Approval Required - {form_name} [#{entry_no}]";
		}

		$form->approval_email_content 	= $row['form_approval_email_content'];
		if(empty($form->approval_email_content)){
			$form->approval_email_content = "This entry needs your approval.<br/><br/>Please approve or deny by using the link below:<br/><strong>{view_entry_link}</strong><br/><br/><hr style=\"width: 60%;margin-top: 20px;margin-bottom: 20px\"><br/>{entry_data}";
		}

		$form->limit_enable 	= (int) $row['form_limit_enable'];
		$form->limit 			= (int) $row['form_limit'];
		$form->label_alignment	= $row['form_label_alignment'];
		$form->schedule_enable 	= (int) $row['form_schedule_enable'];
		
		
		$form->schedule_start_date  = $row['form_schedule_start_date'];
		if(!empty($row['form_schedule_start_hour'])){
			$form->schedule_start_hour  = date('h:i:a',strtotime($row['form_schedule_start_hour']));
		}else{
			$form->schedule_start_hour  = '';
		}
		$form->schedule_end_date  	= $row['form_schedule_end_date'];
		if(!empty($row['form_schedule_end_hour'])){
			$form->schedule_end_hour  	= date('h:i:a',strtotime($row['form_schedule_end_hour']));
		}else{
			$form->schedule_end_hour	= '';
		}
		$form_lastpage_title		= $row['form_lastpage_title'];
		$form_submit_primary_text 	= $row['form_submit_primary_text'];
		$form_submit_secondary_text = $row['form_submit_secondary_text'];
		$form_submit_primary_img 	= $row['form_submit_primary_img'];
		$form_submit_secondary_img  = $row['form_submit_secondary_img'];
		$form_submit_use_image  	= (int) $row['form_submit_use_image'];
		$form->page_total			= (int) $row['form_page_total'];
		$form->pagination_type		= $row['form_pagination_type'];
		
		$form->review_primary_text 	 = $row['form_review_primary_text'];
		$form->review_secondary_text = $row['form_review_secondary_text'];
		$form->review_primary_img 	 = $row['form_review_primary_img'];
		$form->review_secondary_img  = $row['form_review_secondary_img'];
		$form->review_use_image  	 = (int) $row['form_review_use_image'];
		$form->review_title			 = $row['form_review_title'];
		$form->review_description	 = $row['form_review_description'];
		$form->custom_script_enable  = (int) $row['form_custom_script_enable'];
		$form->custom_script_url  	 = $row['form_custom_script_url'];
	} 
	
	//get element options first and store it into array
	$query = "select 
					element_id,
					option_id,
					`position`,
					`option`,
					option_is_default,
					option_is_hidden  
			    from 
			    	".MF_TABLE_PREFIX."element_options 
			   where 
			   		form_id = ? and live=1 
			order by 
					element_id asc,`position` asc";
	$params = array($form_id);
	$sth 	= mf_do_query($query,$params,$dbh);
	
	while($row = mf_do_fetch_result($sth)){
		$element_id = $row['element_id'];
		$option_id  = $row['option_id'];
		$options_lookup[$element_id][$option_id]['position'] 		  = $row['position'];
		$options_lookup[$element_id][$option_id]['option'] 			  = $row['option'];
		$options_lookup[$element_id][$option_id]['option_is_default'] = $row['option_is_default'];
		$options_lookup[$element_id][$option_id]['option_is_hidden']  = $row['option_is_hidden'];
	}
	
	//get the last option id for each options and store it into array
	//we need it when the user adding a new option, so that we could assign the last option id + 1
	$query = "select 
					element_id,
					max(option_id) as last_option_id 
			    from 
			    	".MF_TABLE_PREFIX."element_options 
			   where 
			   		form_id = ? 
			group by 
					element_id";
	$params = array($form_id);
	$sth 	= mf_do_query($query,$params,$dbh);
	
	while($row = mf_do_fetch_result($sth)){
		$element_id = $row['element_id'];
		$last_option_id_lookup[$element_id] = $row['last_option_id'];
	}

	
	//get elements data
	$element = array();
	$query = "select 
					element_id,
					element_title,
					element_guidelines,
					element_size,
					element_is_required,
					element_is_unique,
					element_is_readonly,
					element_is_private,
					element_is_encrypted,
					element_type,
					element_position,
					element_default_value,
					element_enable_placeholder,
					element_constraint,
					element_css_class,
					element_range_min,
					element_range_max,
					element_range_limit_by,
					element_choice_columns,
					element_choice_has_other,
					element_choice_other_label,
					element_choice_limit_rule,
					element_choice_limit_qty,
					element_choice_limit_range_min,
					element_choice_limit_range_max,
					element_choice_max_entry,
					element_time_showsecond, 
					element_time_24hour,
					element_address_us_only,
					element_date_enable_range,
					element_date_range_min,
					element_date_range_max,
					element_date_enable_selection_limit,
					element_date_selection_max,
					element_date_disable_past_future,
					element_date_past_future,
					element_date_disable_dayofweek,
					element_date_disabled_dayofweek_list,
					element_date_disable_specific,
					element_date_disabled_list,
					element_file_type_list,
					element_file_as_attachment,
					element_file_auto_upload,
					element_file_enable_multi_upload,
					element_file_max_selection,
					element_file_enable_size_limit,
					element_file_size_max,
					element_submit_use_image,
					element_submit_primary_text,
					element_submit_secondary_text,
					element_submit_primary_img,
					element_submit_secondary_img,
					element_page_title,
					element_matrix_allow_multiselect,
					element_matrix_parent_id,
					element_section_display_in_email,
					element_section_enable_scroll,
					element_number_enable_quantity,
					element_number_quantity_link,
					element_text_default_type,
					element_text_default_length,
					element_text_default_random_type,
					element_text_default_prefix,
					element_text_default_case,
					element_email_enable_confirmation,
					element_email_confirm_field_label,
					element_media_type,
					ifnull(element_media_image_src,'') as element_media_image_src,
					ifnull(element_media_image_width,'') as element_media_image_width,
					ifnull(element_media_image_height,'') as element_media_image_height,
					ifnull(element_media_image_alignment,'') as element_media_image_alignment,
					ifnull(element_media_image_alt,'') as element_media_image_alt,
					ifnull(element_media_image_href,'') as element_media_image_href,
					element_media_display_in_email,
					ifnull(element_media_video_src,'') as element_media_video_src,
					ifnull(element_media_video_size,'') as element_media_video_size,
					element_media_video_muted,
					ifnull(element_media_video_caption_file,'') element_media_video_caption_file,
					ifnull(element_media_pdf_src,'') element_media_pdf_src,
					element_rating_style,
					element_rating_max,
					element_rating_default,
					element_rating_enable_label,
					ifnull(element_rating_label_high,'') element_rating_label_high,
					ifnull(element_rating_label_low,'') element_rating_label_low,
					ifnull(element_address_subfields_labels,'') element_address_subfields_labels,
					ifnull(element_address_subfields_visibility,'') element_address_subfields_visibility,
					ifnull(element_address_default_state,'') element_address_default_state 
				from 
					".MF_TABLE_PREFIX."form_elements 
			   where 
			   		form_id = ? and element_status='1'
			order by 
					element_position asc";
	$params = array($form_id);
	$sth 	= mf_do_query($query,$params,$dbh);
	
	$j=0;
	while($row = mf_do_fetch_result($sth)){
		$element_id = $row['element_id'];
		
		//lookup element options first
		$option_id_array = array();
		$element_options = array();
		
		if(!empty($options_lookup[$element_id])){
			
			$i=1;
			foreach ($options_lookup[$element_id] as $option_id=>$data){
				$element_options[$option_id] = new stdClass();
				$element_options[$option_id]->position 	 = $i;
				$element_options[$option_id]->option 	 = $data['option'];
				$element_options[$option_id]->is_default = $data['option_is_default'];
				$element_options[$option_id]->is_hidden  = $data['option_is_hidden'];
				$element_options[$option_id]->is_db_live = 1;
				
				$option_id_array[$element_id][$i] = $option_id;
				
				$i++;
			}
		}
		
	
		//populate elements
		$element[$j] = new stdClass();
		$element[$j]->title 		= $row['element_title'];
		$element[$j]->guidelines 	= $row['element_guidelines'];
		$element[$j]->size 			= $row['element_size'];
		$element[$j]->is_required 	= $row['element_is_required'];
		$element[$j]->is_encrypted 	= $row['element_is_encrypted'];
		$element[$j]->is_unique 	= $row['element_is_unique'];
		$element[$j]->is_readonly 	= $row['element_is_readonly'];
		$element[$j]->is_private 	= $row['element_is_private'];
		$element[$j]->type 			= $row['element_type'];
		$element[$j]->position 		= $row['element_position'];
		$element[$j]->id 			= $row['element_id'];
		$element[$j]->is_db_live 	= 1;

		$element[$j]->default_value 	 = $row['element_default_value'];
		$element[$j]->enable_placeholder = (int) $row['element_enable_placeholder'];
		$element[$j]->constraint 		 = $row['element_constraint'];
		$element[$j]->css_class 		 = $row['element_css_class'];
		$element[$j]->range_min 		 = (int) $row['element_range_min'];
		$element[$j]->range_max 		 = (int) $row['element_range_max'];
		$element[$j]->range_limit_by	 = $row['element_range_limit_by'];
		$element[$j]->choice_columns	 = (int) $row['element_choice_columns'];
		$element[$j]->choice_has_other	 = (int) $row['element_choice_has_other'];
		$element[$j]->choice_other_label = $row['element_choice_other_label'];
		$element[$j]->choice_limit_rule  = $row['element_choice_limit_rule'];
		$element[$j]->choice_limit_qty	 = (int) $row['element_choice_limit_qty'];
		$element[$j]->choice_limit_range_min = (int) $row['element_choice_limit_range_min'];
		$element[$j]->choice_limit_range_max = (int) $row['element_choice_limit_range_max'];
		$element[$j]->choice_max_entry 		 = (int) $row['element_choice_max_entry'];
		$element[$j]->time_showsecond	 = (int) $row['element_time_showsecond'];
		$element[$j]->time_24hour	 	 = (int) $row['element_time_24hour'];
		$element[$j]->address_us_only	 = (int) $row['element_address_us_only'];
		$element[$j]->date_enable_range	 = (int) $row['element_date_enable_range'];
		$element[$j]->date_range_min	 = $row['element_date_range_min'];
		$element[$j]->date_range_max	 = $row['element_date_range_max'];
		$element[$j]->date_enable_selection_limit	= (int) $row['element_date_enable_selection_limit'];
		$element[$j]->date_selection_max	 		= (int) $row['element_date_selection_max'];
		$element[$j]->date_disable_past_future	 	= (int) $row['element_date_disable_past_future'];
		$element[$j]->date_past_future	 			= $row['element_date_past_future'];
		$element[$j]->date_disable_dayofweek	 	= (int) $row['element_date_disable_dayofweek'];
		$element[$j]->date_disabled_dayofweek_list	= $row['element_date_disabled_dayofweek_list'];
		$element[$j]->date_disable_specific	 		= (int) $row['element_date_disable_specific'];
		$element[$j]->date_disabled_list	 		= $row['element_date_disabled_list'];					
		$element[$j]->file_type_list	 			= $row['element_file_type_list'];
		$element[$j]->file_as_attachment	 		= (int) $row['element_file_as_attachment'];	
		$element[$j]->file_auto_upload	 			= (int) $row['element_file_auto_upload'];
		$element[$j]->file_enable_multi_upload	 	= (int) $row['element_file_enable_multi_upload'];
		$element[$j]->file_max_selection	 		= (int) $row['element_file_max_selection'];
		$element[$j]->file_enable_size_limit	 	= (int) $row['element_file_enable_size_limit'];
		$element[$j]->file_size_max	 				= (int) $row['element_file_size_max'];
		$element[$j]->submit_use_image	 			= (int) $row['element_submit_use_image'];
		$element[$j]->submit_primary_text	 		= $row['element_submit_primary_text'];
		$element[$j]->submit_secondary_text	 		= $row['element_submit_secondary_text'];
		$element[$j]->submit_primary_img	 		= $row['element_submit_primary_img'];
		$element[$j]->submit_secondary_img	 		= $row['element_submit_secondary_img'];
		$element[$j]->page_title	 				= $row['element_page_title'];
		$element[$j]->matrix_allow_multiselect	 	= (int) $row['element_matrix_allow_multiselect'];
		$element[$j]->matrix_parent_id	 			= (int) $row['element_matrix_parent_id'];
		$element[$j]->section_display_in_email	 	= (int) $row['element_section_display_in_email'];
		$element[$j]->section_enable_scroll	 		= (int) $row['element_section_enable_scroll'];
		$element[$j]->number_enable_quantity	 	= (int) $row['element_number_enable_quantity'];
		$element[$j]->number_quantity_link	 		= $row['element_number_quantity_link'];
		$element[$j]->text_default_type	 			= $row['element_text_default_type'];
		$element[$j]->text_default_length	 		= (int) $row['element_text_default_length'];
		$element[$j]->text_default_random_type	 	= $row['element_text_default_random_type'];
		$element[$j]->text_default_prefix	 		= $row['element_text_default_prefix'];
		$element[$j]->text_default_case	 			= $row['element_text_default_case'];				 
		$element[$j]->email_enable_confirmation	 	= (int) $row['element_email_enable_confirmation'];
		$element[$j]->email_confirm_field_label	 	= $row['element_email_confirm_field_label'];
		$element[$j]->media_type	 				= $row['element_media_type'];
		$element[$j]->media_image_src	 			= trim($row['element_media_image_src']);
		$element[$j]->media_image_width	 			= trim($row['element_media_image_width']);
		$element[$j]->media_image_height	 		= trim($row['element_media_image_height']);
		$element[$j]->media_image_alignment	 		= trim($row['element_media_image_alignment']);
		$element[$j]->media_image_alt	 			= trim($row['element_media_image_alt']);
		$element[$j]->media_image_href	 			= trim($row['element_media_image_href']);
		$element[$j]->media_display_in_email		= (int) $row['element_media_display_in_email'];
		$element[$j]->media_video_src	 			= trim($row['element_media_video_src']);
		$element[$j]->media_video_size	 			= trim($row['element_media_video_size']);
		$element[$j]->media_video_muted	 			= (int) $row['element_media_video_muted'];
		$element[$j]->media_video_caption_file	 	= trim($row['element_media_video_caption_file']);
		$element[$j]->media_pdf_src	 				= trim($row['element_media_pdf_src']);
		$element[$j]->rating_style	 				= trim($row['element_rating_style']);
		$element[$j]->rating_max	 				= (int) $row['element_rating_max'];
		$element[$j]->rating_default	 			= (int) $row['element_rating_default'];
		$element[$j]->rating_enable_label	 		= (int) $row['element_rating_enable_label'];
		$element[$j]->rating_label_high	 			= trim($row['element_rating_label_high']);
		$element[$j]->rating_label_low	 			= trim($row['element_rating_label_low']);
		$element[$j]->address_subfields_labels	 	= trim($row['element_address_subfields_labels']);
		$element[$j]->address_subfields_visibility	= trim($row['element_address_subfields_visibility']);
		$element[$j]->address_default_state			= trim($row['element_address_default_state']);
	
		if(!empty($element_options)){
			$element[$j]->options 	= $element_options;
			$element[$j]->last_option_id = $last_option_id_lookup[$element_id];
		}else{
			$element[$j]->options 	= '';
		}
		
		//if the element is a matrix field and not the parent, store the data into a lookup array for later use when rendering the markup
		if($row['element_type'] == 'matrix' && !empty($row['element_matrix_parent_id'])){
				
				$parent_id 	  = $row['element_matrix_parent_id'];
				if(!empty($matrix_elements[$parent_id])){
					$row_position = count($matrix_elements[$parent_id]) + 2;
				}else{
					$row_position = 2;
				}
				$element_id   = $row['element_id'];
				
				$matrix_elements[$parent_id][$element_id] = new stdClass();
				$matrix_elements[$parent_id][$element_id]->is_db_live = 1;
				$matrix_elements[$parent_id][$element_id]->position   = $row_position;
				$matrix_elements[$parent_id][$element_id]->row_title  = $row['element_title'];
				
				$column_data = array();
				$col_position = 1;
				foreach ($element_options as $option_id=>$value){
					$column_data[$option_id] = new stdClass();
					$column_data[$option_id]->is_db_live = 1;
					$column_data[$option_id]->position 	 = $col_position;
					$column_data[$option_id]->column_title 	= $value->option;
					$col_position++;
				}
				
				$matrix_elements[$parent_id][$element_id]->column_data = $column_data;
				
				//remove it from the main element array
				$element[$j] = array();
				unset($element[$j]);
				$j--;
		}
		
		$j++;
	}
	
	//if this is multipage form, add the lastpage submit property into the element list
	if($form->page_total > 1){
		$element[$j] = new stdClass();
		$element[$j]->id 		 = 'lastpage';
		$element[$j]->type 		 = 'page_break';
		$element[$j]->page_title = $form_lastpage_title;
		$element[$j]->submit_primary_text	 		= $form_submit_primary_text;
		$element[$j]->submit_secondary_text	 		= $form_submit_secondary_text;
		$element[$j]->submit_primary_img	 		= $form_submit_primary_img;
		$element[$j]->submit_secondary_img	 		= $form_submit_secondary_img;
		$element[$j]->submit_use_image	 			= $form_submit_use_image;
	}

		
	$jquery_data_code = '';
	
	//build the json code for form fields
	$all_element = array('elements' => $element);
	foreach ($element as $data){
		//if this is matrix element, attach the children data into options property and merge with current (matrix parent) options
		if($data->type == 'matrix'){
			$matrix_elements[$data->id][$data->id] = new stdClass();
			$matrix_elements[$data->id][$data->id]->is_db_live = 1;
			$matrix_elements[$data->id][$data->id]->position   = 1;
			$matrix_elements[$data->id][$data->id]->row_title  = $data->title;
				
			$column_data = array();
			$col_position = 1;
			foreach ($data->options as $option_id=>$value){
				$column_data[$option_id] = new stdClass();
				$column_data[$option_id]->is_db_live = 1;
				$column_data[$option_id]->position 	 = $col_position;
				$column_data[$option_id]->column_title 	= $value->option;
				$col_position++;
			}
				
			$matrix_elements[$data->id][$data->id]->column_data = $column_data;

			$temp_array = array();
			$temp_array = $matrix_elements[$data->id];
			
			asort($temp_array);
			
			$matrix_elements[$data->id] = array();
			$matrix_elements[$data->id] = $temp_array;
			
			$data->options = array();
			$data->options = $matrix_elements[$data->id];
			
		}
		$field_settings = json_encode($data);
		$jquery_data_code .= "\$('#li_{$data->id}').data('field_properties',{$field_settings});\n";
	}

	
	//build the json code for form settings
	$json_form = json_encode($form);
	$jquery_data_code .= "\$('#form_header').data('form_properties',{$json_form});\n";
	$jquery_data_code .= "\$('#form_header').data('is_new_form', ".(int) $is_new_form.");\n";
	
	//session info for image uploader
	$session_id = session_id();
	$jquery_data_code .= "\$('#form_header').data('session_id','{$session_id}');\n";

	$header_data =<<<EOT
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="css/edit_form.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="js/datepick/smoothness.datepick.css{$mf_version_tag}" rel="stylesheet" />
EOT;
	
	$current_nav_tab = 'manage_forms';
	
	require('includes/header.php'); 
?>

 		<div id="editor_loading">
 			Loading... Please wait...
 		</div>
		
		<div id="content" data-csrftoken="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>">
		<div class="post form_editor">
		<span id="selected_field_image" class="icon-arrow-right2 arrow-field-prop" ></span> 
		
<?php 
	echo $markup;
	
?>

		<div id="bottom_bar" style="display: none">
				
				<div id="bottom_bar_content" class="buttons_bar">
						 
						<a id="bottom_bar_save_form" href="#" class="bb_button bb_green"  alt="Save Form" title="Save Form">
					        <span class="icon-disk"></span>Save Form
					    </a>
					    
					    <a id="bottom_bar_add_field" class="bb_button bb_grey" href="#" alt="Add a New Field" title="Add a New Field">
					       <span class="icon-plus-circle"></span>Add Field
					    </a>
					   
					    <div id="bottom_bar_field_action">
						  	<span class="icon-arrow-right2 arrow-field-prop" ></span> 
						    <a id="bottom_bar_duplicate_field" href="#" class="bb_button bb_grey" alt="Duplicate Selected Field" title="Duplicate Selected Field">
						       <span class="icon-copy"></span>Duplicate
						    </a>
						    
						    <a id="bottom_bar_delete_field" href="#" class="bb_button bb_red" alt="Delete Selected Field" title="Delete Selected Field">
						        <span class="icon-remove"></span>Delete
						    </a>
					   </div> 
				</div>
				<div id="bottom_bar_loader">
					<span>
						<img src="images/loader.gif" width="32" height="32"/>
						<span id="bottom_bar_msg">Please wait... Synching...</span>
					</span>
				</div>
				
		</div>	
		<div id="bottom_bar_limit"></div>
<?php if($is_new_form){ ?>		
		<div id="no_fields_notice">
			<span class="icon-arrow-right" style="margin-bottom: 20px;color: #529214;font-size: 50px;display: block"></span>
			<h3>Your form has no fields yet!</h3>
			<p><span style="color: #529214; font-weight: bold;">Click the buttons</span> on the right sidebar or <span style="color: #529214; font-weight: bold;">Drag it here</span> to add new field.</p>
		</div>			
<?php } ?>        
        </div>   	
			 
		</div><!-- /#content -->
		
		<div id="sidebar">
			<div id="builder_tabs">
								<ul id="builder_tabs_btn" style="display: none">
									<li id="btn_add_field"><a href="#tab_add_field">Add a Field</a></li>
									<li id="btn_field_properties"><a href="#tab_field_properties">Field Properties</a></li>
									<li id="btn_form_properties"><a href="#tab_form_properties">Form Properties</a></li>
								</ul>
								<div id="tab_add_field">
									<div id="social" class="box">
										<ul>   		
											<li id="btn_single_line_text" class="box">
												<a id="a_single_line_text" href="#" title="Single Line Text">
													<span class="icon-font-size icon-font"></span><span class="blabel">Single Line Text</span>
												</a>
											</li>     
											  
											<li id="btn_number" class="box">
												<a id="a_number" href="#" title="Number">
													<span class="icon-seven-segment8 icon-font"></span><span class="blabel">Number</span>
												</a>
											</li>     
								          	
								          	<li id="btn_paragraph_text" class="box">
												<a id="a_paragraph_text" href="#" title="Paragraph Text">
													<span class="icon-paragraph-left icon-font"></span><span class="blabel">Paragraph Text</span>
												</a>
											</li>     
											<li id="btn_checkboxes" class="box">
												<a id="a_checkboxes" href="#" title="Checkboxes">
													<span class="icon-checkbox icon-font"></span><span class="blabel">Checkboxes</span>
												</a>
											</li>   	
											
											<li id="btn_multiple_choice" class="box">
												<a id="a_multiple_choice" href="#" title="Multiple Choice">
													<span class="icon-list icon-font"></span><span class="blabel">Multiple Choice</span>
												</a>
											</li>     
											  
											<li id="btn_drop_down" class="box">
												<a id="a_drop_down" href="#" title="Drop Down">
													<span class="icon-menu icon-font"></span><span class="blabel">Drop Down</span>
												</a>
											</li>     
								          	
								          	<li id="btn_name" class="box">
												<a id="a_name" href="#" title="Name">
													<span class="icon-user2 icon-font"></span><span class="blabel">Name</span>
												</a>
											</li>     
											<li id="btn_date" class="box">
												<a id="a_date" href="#" title="Date">
													<span class="icon-calendar icon-font"></span><span class="blabel">Date</span>
												</a>
											</li>   	
											
											<li id="btn_time" class="box">
												<a id="a_time" href="#" title="Time">
													<span class="icon-alarm icon-font"></span><span class="blabel">Time</span>
												</a>
											</li>     
											  
											<li id="btn_phone" class="box">
												<a id="a_phone" href="#" title="Phone">
													<span class="icon-phone icon-font"></span><span class="blabel">Phone</span>
												</a>
											</li>     
								          	
								          	<li id="btn_address" class="box">
												<a id="a_address" href="#" title="Address">
													<span class="icon-home icon-font"></span><span class="blabel">Address</span>
												</a>
											</li>     
											<li id="btn_rating" class="box">
												<a id="a_rating" href="#" title="Rating">
													<span class="icon-star-full2 icon-font"></span><span class="blabel">Rating</span>
												</a>
											</li>   	
											
											<li id="btn_price" class="box">
												<a id="a_price" href="#" title="Price">
													<span class="icon-coins icon-font"></span><span class="blabel">Price</span>
												</a>
											</li>     
											  
											<li id="btn_email" class="box">
												<a  id="a_email" href="#" title="Email">
													<span class="icon-envelope-opened icon-font"></span><span class="blabel">Email</span>
												</a>
											</li>     
								          	
								          	<li id="btn_matrix" class="box">
												<a id="a_matrix" href="#" title="Matrix Choice">
													<span class="icon-grid icon-font"></span><span class="blabel">Matrix Choice</span>
												</a>
											</li>     
											<li id="btn_file_upload" class="box">
												<a id="a_file_upload" href="#" title="File Upload">
													<span class="icon-file-upload icon-font"></span><span class="blabel">File Upload</span>
												</a>
											</li>  
											<li id="btn_section_break" class="box">
												<a id="a_section_break" href="#" title="Section Break">
													<span class="icon-marker icon-font"></span><span class="blabel">Section Break</span>
												</a>
											</li>     
											<li id="btn_page_break" class="box">
												<a id="a_page_break" href="#" title="Page Break">
													<span class="icon-file-plus icon-font"></span><span class="blabel">Page Break</span>
												</a>
											</li>   	
											<li id="btn_signature" class="box">
												<a id="a_signature" href="#" title="Signature">
													<span class="icon-quill icon-font"></span><span class="blabel">Signature</span>
												</a>
											</li>
											<li id="btn_media" class="box">
												<a id="a_media" href="#" title="Image / Video / HTML">
													<span class="icon-camera icon-font"></span><span class="blabel">Media</span>
												</a>
											</li>
											        			
					</ul>
										
					<div class="clear"></div>
									
			</div><!-- /#social -->
		</div>
				
		<div id="tab_field_properties" style="display: none">
			<div id="field_properties_pane" class="box"> <!-- Start field properties pane -->
				<form style="display: block;" id="element_properties" action="" onsubmit="return false;">
					
					<div id="element_inactive_msg">
						<span class="icon-arrow-left" style="margin-top: 80px;color: #529214;font-size: 50px;display: block"></span>
						<h3>Please select a field</h3>
						<p id="eim_p">Click on a field on the left to change its properties.</p>
					</div>
					
					<div id="element_properties_form">
						<div class="num" id="element_position">12</div>
						<ul id="all_properties">
						<li id="prop_element_label">
								<label class="desc" for="element_label">Field Label</label><span class="icon-question helpicon" data-tippy-content="<strong>Field Label</strong> is one or two words placed directly above the field."></span>
								<textarea id="element_label" name="element_label" class="textarea" /></textarea>
						</li>
						
						<li class="leftCol" id="prop_element_type">
							<label class="desc" for="element_type">
							Field Type 
							</label><span class="icon-question helpicon" data-tippy-content="<strong>Field Type</strong> detemines what kind of data can be collected by your field. After you save the form, the field type cannot be changed."></span>
							<select class="select full" id="element_type" name="element_type" autocomplete="off" tabindex="12">
							<option value="text">Single Line Text</option>
							<option value="textarea">Paragraph Text</option>
							<option value="radio">Multiple Choice</option>
							<option value="checkbox">Checkboxes</option>
							<option value="select">Drop Down</option>
							<option value="number">Number</option>
							<option value="simple_name">Name</option>
							<option value="date">Date</option>
							<option value="time">Time</option>
							<option value="phone">Phone</option>
							<option value="money">Price</option>
							<option value="rating">Rating</option>
							<option value="email">Email</option>
							<option value="address">Address</option>
							<option value="file">File Upload</option>
							<option value="section">Section Break</option>
							<option value="matrix">Matrix Choice</option>
							<option value="media">Media</option>
							</select>
						</li>
						
						<li class="rightCol" id="prop_element_size">
							<label class="desc" for="element_size">
							Field Size 
							</label>
							<span class="icon-question helpicon" data-tippy-content="This property sets the visual appearance of the field in your form. It does not limit nor increase the amount of data that can be collected by the field."></span>
							<select class="select full" id="element_size" autocomplete="off" tabindex="13">
							<option value="small">Small</option>
							<option value="medium">Medium</option>
							<option value="large">Large</option>
							</select>
						</li>
						
						<li class="rightCol" id="prop_choice_columns">
							<label class="desc" for="element_choice_columns">
							Choice Columns 
							</label>
							<span class="icon-question helpicon" data-tippy-content="Set the number of columns being used to display the choices. Inline columns means the choices are sitting next to each other."></span>
							<select class="select full" id="element_choice_columns" autocomplete="off">
							<option value="1">One Column</option>
							<option value="2">Two Columns</option>
							<option value="3">Three Columns</option>
							<option value="9">Inline</option>
							</select>
						</li>
						
						<li class="rightCol" id="prop_date_format">
							<label class="desc" for="field_size">
							Date Format 
							</label>
							<span class="icon-question helpicon" data-tippy-content="You can choose between American and European Date Formats"></span>
							<select class="select full" id="date_type" autocomplete="off">
							<option id="element_date" value="date">MM / DD / YYYY</option>
							<option id="element_europe_date" value="europe_date">DD / MM / YYYY</option>
							</select>
						</li>
						
						<li class="rightCol" id="prop_name_format">
							<label class="desc" for="name_format">
							Name Format 
							</label>
							<span class="icon-question helpicon" data-tippy-content="Two formats available. A normal name field, or an extended name field with title and suffix."></span>
							<select class="select full" id="name_format" autocomplete="off">
							<option id="element_simple_name" value="simple_name" selected="selected">Normal</option>
							<option id="element_name" value="name" selected="selected">Normal + Title</option>
							<option id="element_simple_name_wmiddle" value="simple_name_wmiddle" selected="selected">Full</option>
							<option id="element_name_wmiddle" value="name_wmiddle">Full + Title</option>
							</select>
						</li>
						
						<li class="rightCol" id="prop_phone_format">
							<label class="desc" for="field_size">
							Phone Format 
							</label>
							<span class="icon-question helpicon" data-tippy-content="You can choose between American and International Phone Formats"></span>
							<select class="select full" id="phone_format" name="phone_format" autocomplete="off">
							<option id="element_phone" value="phone">### - ### - ####</option>
							<option id="element_simple_phone" value="simple_phone">International</option>
							</select>
						</li>
						
						<li class="rightCol" id="prop_currency_format">
							<label class="desc" for="field_size">
							Currency Format
							</label>
							<select class="select full" id="money_format" name="money_format" autocomplete="off">
							<option id="element_money_usd" value="dollar">&#36; - Dollars</option>
							<option id="element_money_euro" value="euro">&#8364; - Euros</option>
							<option id="element_money_pound" value="pound">&#163; - Pounds Sterling</option>
							<option id="element_money_yen" value="yen">&#165; - Yen</option>
							<option id="element_money_baht" value="baht">&#3647; - Baht</option>
							<option id="element_money_forint" value="forint">&#70;&#116; - Forint</option>
							<option id="element_money_franc" value="franc">CHF - Francs</option>
							<option id="element_money_koruna" value="koruna">&#75;&#269; - Koruna</option>
							<option id="element_money_krona" value="krona">kr - Krona</option>
							<option id="element_money_leu" value="leu">L - Leu</option>
							<option id="element_money_pesos" value="pesos">&#36; - Pesos</option>
							<option id="element_money_rand" value="rand">R - Rand</option>
							<option id="element_money_reais" value="reais">R&#36; - Reais</option>
							<option id="element_money_ringgit" value="ringgit">RM - Ringgit</option>
							<option id="element_money_rupees" value="rupees">Rs - Rupees</option>
							<option id="element_money_riyals" value="riyals">&#65020; - Riyals</option>
							<option id="element_money_zloty" value="zloty">&#122;&#322; - Złoty</option>
							</select>
						</li>
						
						<li class="clear" id="prop_choices">
							<fieldset class="choices">
							<legend>
							Choices 
							<span class="icon-question helpicon" style="color: #fff;font-size: 100%" data-tippy-content="Use the plus and minus buttons to add and delete choices. Click on the choice to make it the default selection."></span>
							</legend>
							<ul id="element_choices">
							<li>
								<input type="radio" title="Select this choice as the default." class="choices_default" name="choices_default" />
								<input type="text" value="First option" autocomplete="off" class="text" id="choice_1" /> 
								<a href="#" class="add_choice" title="Add Choice" id="choiceadd_1"><span class="icon-plus-circle"></span></a>
								<a href="#" class="del_choice" title="Remove Choice" id="choicedel_1"><span class="icon-cancel-circle"></span></a>  
							</li>	
							</ul>
							
							<div id="element_choices_action">
								<a href="#" id="bulk_import_choices"><span class="icon-list"></span> Bulk Insert</a>
								<a href="#" id="toggle_hidden_choices" style="display: none">Hidden (<var id="hidden_choices_counter">0</var>)</a>
							</div> 

							<ul id="element_choices_hidden" style="display: none">
							<li>
								<input type="radio" title="Select this choice as the default." class="choices_default_hidden" name="choices_default_hidden" />
								<input type="text" value="" autocomplete="off" class="text" id="choicehidden_1" /> 
								<a href="#" class="unhide_choice" title="Unhide Choice" id="choiceunhide_1"><span class="icon-plus-circle"></span></a>
								<a href="#" class="trash_choice" title="Remove Choice" id="choicetrash_1"><span class="icon-cancel-circle"></span></a>  
							</li>	
							</ul>

							</fieldset>
							
						</li>
						
						<li class="clear" id="prop_choices_other">
							<fieldset class="choices">
							<legend>
							Choices Options 
							</legend>
							
							<span>	
									<input id="prop_choices_other_checkbox" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_choices_other_checkbox">Allow Client to Add Other Choice</label>
									<span class="icon-question helpicon" data-tippy-content="Enable this option if you would like to allow your client to write his own answer if none of the other choices are applicable. A text field will be added to the last choice. Enter the label below this checkbox."></span>
									<div style="margin-bottom: 5px;margin-top: 3px;padding-left: 20px">
										<img src="images/icons/tag_green.png" style="vertical-align: middle"> <input id="prop_other_choices_label" style="width: 220px" class="text" value="" size="25" type="text">
									</div>
									<span id="prop_choices_randomize_span" style="display: none">
									<input id="prop_choices_randomize" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_choices_randomize">Randomize Choices</label>
									<span class="icon-question helpicon" data-tippy-content="Enable this option if you would like the choices to be shuffled around each time the form being displayed."></span>
									</span>
							</span>
							
							</fieldset>
							
						</li>
						
						<li class="clear" id="prop_matrix_row">
							<fieldset class="choices">
							<legend>
							Rows 
							<span style="font-size: 100%;color: #fff" class="icon-question helpicon" data-tippy-content="Enter rows labels here. Use the plus and minus buttons to add and delete matrix row."></span>
							</legend>
							<ul id="element_matrix_row">
							<li>
								<input type="text" value="First Question" autocomplete="off" class="text" id="matrixrow_1" /> 
								<img title="Add" alt="Add" src="images/icons/add.png" style="vertical-align: middle" > 
								<img title="Delete" alt="Delete" src="images/icons/delete.png" style="vertical-align: middle" > 
							</li>	
							</ul>
							
							<div style="text-align: center;padding-top: 5px;padding-bottom: 10px">
								<a href="#" id="bulk_import_matrix_row"><span class="icon-list"></span> Bulk Insert Rows</a>
							</div> 
							
							</fieldset>
							
						</li>
						<li class="clear" id="prop_matrix_column">
							<fieldset class="choices">
							<legend>
							Columns 
							<span style="font-size: 100%;color: #fff" class="icon-question helpicon" data-tippy-content="Enter column labels here. Use the plus and minus buttons to add and delete matrix column."></span>
							</legend>
							<ul id="element_matrix_column">
							<li>
								<input type="text" value="First Question" autocomplete="off" class="text" id="matrixcolumn_1" /> 
								<img title="Add" alt="Add" src="images/icons/add.png" style="vertical-align: middle" > 
								<img title="Delete" alt="Delete" src="images/icons/delete.png" style="vertical-align: middle" > 
							</li>	
							</ul>
							
							<div style="text-align: center;padding-top: 5px;padding-bottom: 10px">
								<a href="#" id="bulk_import_matrix_column"><span class="icon-list"></span> Bulk Insert Columns</a>
							</div> 
							
							</fieldset>
							
						</li>
						<li id="prop_breaker"></li> 
						<li class="leftCol" id="prop_options">
							<fieldset class="fieldset">
							<legend>Rules</legend>
							<span style="display: block">
								<input id="element_required" class="checkbox" value="" type="checkbox">
								<label class="choice" for="element_required">Required</label>
								<span class="icon-question helpicon" data-tippy-content="Checking this rule will make sure that a user fills out a particular field. A message will be displayed to the user if they have not filled out the field."></span>
							</span>
							<span id="element_unique_span" style="display: block">
								<input id="element_unique" class="checkbox" value="" type="checkbox"> 
								<label class="choice" for="element_unique">No Duplicates</label>
								<span class="icon-question helpicon" data-tippy-content="Checking this rule will verify that the data entered into this field is unique and has not been submitted previously."></span>
							</span>
							<span id="element_readonly_span" style="display: block">
								<input id="element_readonly" class="checkbox" value="" type="checkbox"> 
								<label class="choice" for="element_readonly">Read Only</label>
								<span class="icon-question helpicon" data-tippy-content="If enabled, users won't be able to change the value of this field."></span>
							</span>
							</fieldset>
						</li>

						<li class="leftCol" id="prop_media_type">
							<fieldset class="fieldset">
							<legend>Media Type</legend>
							
							<input id="element_media_image" name="element_media_type" class="radio" value="" checked="checked" type="radio">
							<label class="choice" for="element_media_image">Image</label>
							
							<span style="display: block;margin: 1px 0">
								<input id="element_media_video" name="element_media_type" class="radio" value="" type="radio">
								<label class="choice" for="element_media_video">Video</label> 
							</span>

							<span style="display: block;margin: 1px 0">
								<input id="element_media_pdf" name="element_media_type" class="radio" value="" type="radio">
								<label class="choice" for="element_media_pdf">PDF</label> 
							</span>
							</fieldset>
						</li>
						
						<li class="rightCol" id="prop_access_control">
							<fieldset class="fieldset">
							<legend>Field Visibility</legend>
							
							<input id="element_public" name="element_visibility" class="radio" value="" checked="checked" type="radio">
							<label class="choice" for="element_public">Visible</label>
							<span class="icon-question helpicon" data-tippy-content="This is the default option. The field will be accessible by anyone when the form is made public."></span>

							<span style="display: block;margin: 1px 0">
								<input id="element_hidden" name="element_visibility" class="radio" value="" type="radio">
								<label class="choice" for="element_hidden">Hidden</label> 
								<span class="icon-question helpicon" data-tippy-content="Field will not be shown to users when the form is made public. Useful to collect information that is not entered by the users (e.g. populated from URL parameters)"></span>
							</span>
							
							<span id="admin_only_span">
								<input id="element_private" name="element_visibility" class="radio" value="" type="radio">
								<label class="choice" for="element_private">Admin Only</label> 
								<span class="icon-question helpicon" data-tippy-content="Similar as hidden, but the field can't be used to collect any data from the public form. The field will only visible inside MachForm entry manager. Useful to add additional information to a submitted entry."></span>
							</span>
							</fieldset>
						</li>
						
						<li class="clear" id="prop_time_options">
							<fieldset class="choices">
							<legend>
							Time Options 
							</legend>
							
							<span>	
									<input id="prop_time_showsecond" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_time_showsecond">Show Seconds Field</label>
									<span class="icon-question helpicon" data-tippy-content="Checking this will enable Seconds field on your time field."></span>
									<br/>
									<input id="prop_time_24hour" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_time_24hour">Use 24 Hour Format</label>
									<span class="icon-question helpicon" data-tippy-content="This will enable 24-hour notation in the form hh:mm (for example 14:23) or hh:mm:ss (for example, 14:23:45)"></span>
							</span>
							
							</fieldset>
							
						</li>
						
						<li class="clear" id="prop_data_encryption">
							<fieldset class="choices">
							<legend>
							Data Encryption 
							</legend>
							
							<span>	
									<input id="prop_encrypt_data" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_encrypt_data">Encrypt Field Data</label>
									<span id="tooltip_is_encrypted" class="icon-question helpicon" data-tippy-content="To be able using this feature, you'll need to enable <strong>Data Encryption</strong> option on your <strong>Form Properties</strong> and generate the encryption keys."></span>
							</span>
							
							</fieldset>
							
						</li>

						<li class="clear" id="prop_rating_style">
							<fieldset class="choices">
							<legend>
							Rating Style 
							</legend>
							<div>
								<input id="element_rating_style_star" name="element_rating_style" class="radio" value="star" type="radio">
								<label class="choice" for="element_rating_style_star"><span class="icon-star-full2 icon-font" title="Star"></span></label> 
							</div>
							<div>
								<input id="element_rating_style_circle" name="element_rating_style" class="radio" value="circle" type="radio">
								<label class="choice" for="element_rating_style_circle"><span class="icon-star icon-font" title="Circle Star"></span></label> 
							</div>
							<div>
								<input id="element_rating_style_love" name="element_rating_style" class="radio" value="love" type="radio">
								<label class="choice" for="element_rating_style_love"><span class="icon-heart5 icon-font" title="Love"></span></label> 
							</div>
							<div>
								<input id="element_rating_style_thumb" name="element_rating_style" class="radio" value="thumb" type="radio">
								<label class="choice" for="element_rating_style_thumb"><span class="icon-thumbs-up3 icon-font" title="Thumb Up"></span></label> 
							</div>
							</fieldset>
						</li>

						<li class="clear" id="prop_rating_options">
							<fieldset>
							<legend>
							Rating Options 
							</legend>
								<div id="prop_rating_options_max">
									<label class="desc" for="element_rating_max">
										Max 
									</label>
									<span class="icon-question helpicon" data-tippy-content="Set the maximum rating that can be given."></span>
									<select class="select" id="element_rating_max" name="element_rating_max" autocomplete="off">
										<option value="1">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
										<option value="6">6</option>
										<option value="7">7</option>
										<option value="8">8</option>
										<option value="9">9</option>
										<option value="10">10</option>
									</select>
								</div>
								<div id="prop_rating_options_default">
									<label class="desc" for="element_rating_default">
										Default 
									</label>
									<span class="icon-question helpicon" data-tippy-content="The default, pre-selected rating."></span>
									<select class="select" id="element_rating_default" name="element_rating_max" autocomplete="off">
										<option value="0">-</option>
										<option value="1">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
										<option value="6">6</option>
										<option value="7">7</option>
										<option value="8">8</option>
										<option value="9">9</option>
										<option value="10">10</option>
									</select>
									<select style="display: none;" id="element_rating_default_lookup" name="element_rating_default_lookup">
										<option value="0">-</option>
										<option value="1">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
										<option value="6">6</option>
										<option value="7">7</option>
										<option value="8">8</option>
										<option value="9">9</option>
										<option value="10">10</option>
									</select>
								</div>
								<div id="prop_rating_enable_label">
									<input id="element_rating_enable_label" class="checkbox" value="" type="checkbox">
									<label class="choice" for="element_rating_enable_label">Show Rating Labels</label>
								</div>
								<div id="prop_rating_labels" style="display: none;">
									<label class="desc" for="element_rating_label_high">Highest Rating Label</label>
									<input id="element_rating_label_high" class="text" name="element_rating_label_high" value="" style="width: 65%;" type="text">
									<label class="desc" for="element_rating_label_low">Lowest Rating Label</label>
									<input id="element_rating_label_low" class="text" name="element_rating_label_low" value="" style="width: 65%;" type="text">
								</div>
							</fieldset>
						</li>

						<li class="clear" id="prop_text_options">
							<fieldset class="choices">
							<legend>
							Text Option 
							</legend>
							
							<span>	
									<input id="prop_text_as_password" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_text_as_password">Display as Password Field</label>
									<span class="icon-question helpicon" data-tippy-content="Checking this will display the field as a password field and masked all the characters (shown as asterisks or circles)."></span>
							</span>
							<div class="clear"></div>
							<span>	
									<input id="prop_text_as_website" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_text_as_website">Display as Web Site Field</label>
									<span class="icon-question helpicon" data-tippy-content="Checking this will display the field as a website field and require the user to enter a valid website URL."></span>
							</span>
							
							</fieldset>
							
						</li>

						<li class="clear" id="prop_email_options">
							<fieldset class="choices">
							<legend>
							Email Field Option 
							</legend>
							
							<div style="padding-bottom: 5px">
									<input id="prop_enable_email_confirmation" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_enable_email_confirmation">Enable Email Confirmation Field</label>
									<span class="icon-question helpicon" data-tippy-content="A second email address field will be displayed, and users are required to input the same email address twice, ensuring that it is entered correctly."></span>
							</div>
							<div id="prop_enable_email_confirmation_div" style="display: none; margin-left: 25px;margin-bottom: 10px">
										<label class="desc" for="prop_confirmation_field_label">Confirmation Field Label</label>
										<input id="prop_confirmation_field_label" name="prop_confirmation_field_label" style="width: 90%" class="text" value=""  type="text">
							</div>
							
							</fieldset>
							
						</li>

						<li class="clear" id="prop_choice_limit">
							<fieldset class="choices" style="padding-bottom: 10px">
							<legend style="background-color: #dc6666; border-color: #dc6666">
							Choices Limit 
							</legend>
							
							<span style="padding-left: 5px">	
								  <label class="choice">Client must select</label> 
								  <select class="select" id="prop_choice_limit_rule" name="prop_choice_limit_rule" autocomplete="off" style="margin: 0 3px">
										<option value="atleast">at least</option>
										<option value="atmost">at most</option>
										<option value="exactly">exactly</option>
										<option value="between">between</option>
								  </select>  
								  
								  <span id="prop_choice_limit_qty_span" style="display: none">
									  <input id="prop_choice_limit_qty" style="width: 20px" class="text" value="" maxlength="255" type="text"> 
									  <label>choice</label>
								  </span>
								  <span id="prop_choice_limit_range_span" style="display: none">
									  <input id="prop_choice_limit_range_min" style="width: 20px" class="text" value="" maxlength="255" type="text"> and 
									  <input id="prop_choice_limit_range_max" style="width: 20px" class="text" value="" maxlength="255" type="text"> 
								  </span>
							</span>
							
							</fieldset>
							
						</li>
						
						<li class="clear" id="prop_matrix_options">
							<fieldset class="choices">
							<legend>
							Matrix Option 
							</legend>
							
							<span>	
									<input id="prop_matrix_allow_multiselect" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_matrix_allow_multiselect">Allow Multiple Answers Per Row</label>
									<span class="icon-question helpicon" data-tippy-content="Checking this option will allow your client to select multiple answers for each row. This option can only being set once, when you initially added the matrix field. Once you have saved the form, this option can't be changed."></span>
							</span>
							
							</fieldset>
							
						</li>
						
						<li class="clear" id="prop_address_subfields">
							<fieldset class="choices">
							<legend>
							Subfields Visibility &amp; Labels
							</legend>
							
									<div id="address_subfield_street_btn" class="address_sub_div" style="margin-top: 10px">
										<span class="address_sub_buttons">
											<span class="icon-eye2" style="font-size: 20px;vertical-align: bottom;margin-right: 5px"></span> Street Address
										</span>
										<input type="text" value="" placeholder="<?php echo htmlentities($mf_lang['address_street'],ENT_QUOTES); ?>" autocomplete="off"  id="address_subfield_street" /> 
									</div>

									<div id="address_subfield_street2_btn" class="address_sub_div">
										<span class="address_sub_buttons">
											<span class="icon-eye2" style="font-size: 20px;vertical-align: bottom;margin-right: 5px"></span> Address Line 2
										</span>
										<input type="text" value="" placeholder="<?php echo htmlentities($mf_lang['address_street2'],ENT_QUOTES); ?>" autocomplete="off"  id="address_subfield_street2" /> 
									</div>

									<div id="address_subfield_city_btn" class="address_sub_div">
										<span class="address_sub_buttons">
											<span class="icon-eye2" style="font-size: 20px;vertical-align: bottom;margin-right: 5px"></span> City
										</span>
										<input type="text" value="" placeholder="<?php echo htmlentities($mf_lang['address_city'],ENT_QUOTES); ?>" autocomplete="off"  id="address_subfield_city" /> 
									</div>

									<div id="address_subfield_state_btn" class="address_sub_div">
										<span class="address_sub_buttons">
											<span class="icon-eye2" style="font-size: 20px;vertical-align: bottom;margin-right: 5px"></span> State
										</span>
										<input type="text" value="" placeholder="<?php echo htmlentities($mf_lang['address_state'],ENT_QUOTES); ?>" autocomplete="off"  id="address_subfield_state" /> 
									</div>

									<div id="address_subfield_postal_btn" class="address_sub_div">
										<span class="address_sub_buttons">
											<span class="icon-eye2" style="font-size: 20px;vertical-align: bottom;margin-right: 5px"></span> Postal / Zip
										</span>
										<input type="text" value="" placeholder="<?php echo htmlentities($mf_lang['address_zip'],ENT_QUOTES); ?>" autocomplete="off"  id="address_subfield_postal" /> 
									</div>
									
									<div id="address_subfield_country_btn" class="address_sub_div" style="margin-bottom: 10px">
										<span class="address_sub_buttons">
											<span class="icon-eye2" style="font-size: 20px;vertical-align: bottom;margin-right: 5px"></span> Country
										</span>
										<input type="text" value="" placeholder="<?php echo htmlentities($mf_lang['address_country'],ENT_QUOTES); ?>" autocomplete="off"  id="address_subfield_country" /> 
									</div>
							
							</fieldset>
							
						</li>

						<li class="clear" id="prop_address_options">
							<fieldset class="choices">
							<legend>
							Address Options 
							</legend>
							
							<span>	
									<input id="prop_address_us_only" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_address_us_only">Restrict to U.S. State Selection</label>
									<span class="icon-question helpicon" data-tippy-content="Checking this will limit the country selection to United States only and the state field will be populated with U.S. state list"></span>
							</span>
							<div id="default_state_div" style="display: none; margin: 5px 0 5px 23px">
								<label class="desc" for="element_address_default_state">
								Default State
								</label>
								<select class="select" id="element_address_default_state" name="element_address_default_state">
									<option value=""></option>
									<?php
										$state_list = mf_get_state_list();
										foreach($state_list as $data){
											echo "<option value=\"{$data['value']}\">{$data['label']}</option>\n";
										}
									?>
								</select>
									</div>
							
							</fieldset>
							
						</li>
						
						<li class="clear" id="prop_date_options">
							<fieldset class="choices">
							<legend>
							Date Options 
							</legend>
							
							<span>	
									<input id="prop_date_range" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_date_range">Enable Minimum and/or Maximum Dates</label>
									<span class="icon-question helpicon" data-tippy-content="You can set minimum and/or maximum dates within which a date may be chosen."></span>
									<div id="prop_date_range_details" style="display: none;">

										<div style="margin-left: 23px;margin-top: 10px">
											Set range using 
											<select class="select" id="prop_date_range_format_switcher" name="prop_date_range_format_switcher" autocomplete="off">
												<option value="fixed">fixed</option>
												<option value="relative">relative</option>
											</select> dates:
										</div>
										
										<div id="prop_date_range_fixed">
											<div id="form_date_range_minimum">
												<label class="desc">Minimum Date:</label>  
												
												<span>
												<input type="text" value="" maxlength="2" size="2" style="width: 2em;" class="text" name="date_range_min_mm" id="date_range_min_mm">
												<label for="date_range_min_mm">MM</label>
												</span>
												
												<span>
												<input type="text" value="" maxlength="2" size="2" style="width: 2em;" class="text" name="date_range_min_dd" id="date_range_min_dd">
												<label for="date_range_min_dd">DD</label>
												</span>
												
												<span>
												 <input type="text" value="" maxlength="4" size="4" style="width: 3em;" class="text" name="date_range_min_yyyy" id="date_range_min_yyyy">
												 <label for="date_range_min_yyyy">YYYY</label>
												</span>
												
												<span style="height: 30px;padding-right: 10px;">
												<input type="hidden" value="" maxlength="4" size="4" style="width: 3em;" class="text" name="linked_picker_range_min" id="linked_picker_range_min">
												<div style="display: none"><img id="date_range_min_pick_img" alt="Pick date." src="images/icons/calendar.png" class="trigger" style="margin-top: 3px; cursor: pointer" /></div>
												</span>	
											</div>
												
											<div id="form_date_range_maximum">
												<label class="desc">Maximum Date:</label> 
												
												<span>
												<input type="text" value="" maxlength="2" size="2" style="width: 2em;" class="text" name="date_range_max_mm" id="date_range_max_mm">
												<label for="date_range_max_mm">MM</label>
												</span>
												
												<span>
												<input type="text" value="" maxlength="2" size="2" style="width: 2em;" class="text" name="date_range_max_dd" id="date_range_max_dd">
												<label for="date_range_max_dd">DD</label>
												</span>
												
												<span>
												 <input type="text" value="" maxlength="4" size="4" style="width: 3em;" class="text" name="date_range_max_yyyy" id="date_range_max_yyyy">
												<label for="date_range_max_yyyy">YYYY</label>
												</span>
												
												<span>
												<input type="hidden" value="" maxlength="4" size="4" style="width: 3em;" class="text" name="linked_picker_range_max" id="linked_picker_range_max">
												<div style="display: none"><img id="date_range_max_pick_img" alt="Pick date." src="images/icons/calendar.png" class="trigger" style="margin-top: 3px; cursor: pointer" /></div>
												</span>
			 								</div>
		 								</div>

		 								<div id="prop_date_range_relative" style="display: none">
		 									<div id="form_date_range_minimum_relative">
												<label for="date_range_min_relative" class="desc">Minimum Date:</label>
												<input type="text" value="" size="3" style="width: 3em;" class="text" name="date_range_min_relative" id="date_range_min_relative"> days ago
											</div>
												
											<div id="form_date_range_maximum_relative">
												<label for="date_range_max_relative" class="desc">Maximum Date:</label>
												<input type="text" value="" size="3" style="width: 3em;" class="text" name="date_range_max_relative" id="date_range_max_relative"> days ahead
											</div>
		 								</div>
											
										<div style="clear: both"></div>
										
									</div>
									
									<div style="clear: both"></div>
									
									<input id="prop_date_selection_limit" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_date_selection_limit">Enable Date Selection Limit</label>
									<span class="icon-question helpicon" data-tippy-content="This is useful for reservation or booking form, so that you could allocate each day for a maximum number of customers. For example, setting the value to 5 will ensure that the same date can't be booked/selected by more than 5 customers."></span>
									<div id="form_date_selection_limit" style="display: none">
											Only allow each date to be selected
											<input id="date_selection_max" style="width: 20px" class="text" value="" maxlength="255" type="text"> times
									</div>
									<div style="clear: both"></div>
									
									<input id="prop_date_past_future_selection" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_date_past_future_selection">Disable</label>
										<select class="select medium" id="prop_date_past_future" name="prop_date_past_future" autocomplete="off" disabled="disabled">
											<option id="element_date_past" value="p">All Past Dates</option>
											<option id="element_date_future" value="f">All Future Dates</option>
										</select>
									
									<span class="icon-question helpicon" data-tippy-content="Checking this option will disable either past or future dates selection."></span>
									<div style="clear: both"></div>
									
									<input id="prop_date_disable_dayofweek" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_date_disable_dayofweek">Disable Days of Week</label>
									<span class="icon-question helpicon" data-tippy-content="Block or disable any particular day(s) of week in the calendar."></span>
									<div id="div_disable_dayofweek_list" style="display: none">
										
											<span>
												<input id="dayofweek_0"  name="dayofweek_0" class="element checkbox dayofweek" type="checkbox" value="1"  />
												<label class="choice" for="dayofweek_0">Sun</label>
											</span>
											<span>
												<input id="dayofweek_1"  name="dayofweek_1" class="element checkbox dayofweek" type="checkbox" value="1"  />
												<label class="choice" for="dayofweek_1">Mon</label>
											</span>
											<span>
												<input id="dayofweek_2"  name="dayofweek_2" class="element checkbox dayofweek" type="checkbox" value="1"  />
												<label class="choice" for="dayofweek_2">Tue</label>
											</span>
											<span>
												<input id="dayofweek_3"  name="dayofweek_3" class="element checkbox dayofweek" type="checkbox" value="1"  />
												<label class="choice" for="dayofweek_3">Wed</label>
											</span>
											<span>
												<input id="dayofweek_4"  name="dayofweek_4" class="element checkbox dayofweek" type="checkbox" value="1"  />
												<label class="choice" for="dayofweek_4">Thu</label>
											</span>
											<span>
												<input id="dayofweek_5"  name="dayofweek_5" class="element checkbox dayofweek" type="checkbox" value="1"  />
												<label class="choice" for="dayofweek_5">Fri</label>
											</span>
											<span>
												<input id="dayofweek_6"  name="dayofweek_6" class="element checkbox dayofweek" type="checkbox" value="1"  />
												<label class="choice" for="dayofweek_6">Sat</label>
											</span>
										
									</div>
									<div style="clear: both"></div>
									
									<input id="prop_date_disable_specific" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_date_disable_specific">Disable Specific Dates</label>
									<span class="icon-question helpicon" data-tippy-content="Block or disable any specific date in the calendar. Use the datepicker to disable multiple dates."></span>
									<div id="form_date_disable_specific" style="display: none">
											<textarea class="textarea" rows="10" cols="100" style="width: 175px;height: 45px" id="date_disabled_list"></textarea>
											<div style="display: none"><img id="date_disable_specific_pick_img" alt="Pick date." src="images/icons/calendar.png" class="trigger" style="vertical-align: top; cursor: pointer" /></div>
									</div>
							</span>
							
							</fieldset>
							
						</li>

						<li class="clear" id="prop_image_options">
							<fieldset class="choices" style="padding-bottom: 15px;">
							<legend>
							Image Options 
							</legend>
							
							<div class="left" style="padding-bottom: 5px">
								<input id="element_image_upload_file" name="element_image_type" class="radio" value="" type="radio">
								<label class="choice" for="element_image_upload_file">Upload Image</label>
							</div>
								
							<div class="left" style="padding-left: 15px;padding-bottom: 5px">
								<input id="element_image_set_url" name="element_image_type" class="radio" value="" type="radio">
								<label class="choice" for="element_image_set_url">Image URL</label>
							</div>

							<div style="clear: both"></div>

							<div id="div_image_src" style="display: block;margin-bottom: 10px">
								<input id="element_image_src" class="text large" name="element_image_src" value="" type="text">
							</div>

							<div id="div_image_upload_file" style="display: block;margin-top: 5px;margin-bottom: 10px; margin-left: 5px">
								<input id="element_image_file_uploader" name="element_image_file_uploader" class="element file" type="file" />
							</div>
							
							<div style="padding-left: 4px">
								<label class="desc" style="margin-bottom: 5px;display: block">Image Size (px)</label>
								<span>
								<input type="text" value="" class="text medium" name="element_image_width" id="element_image_width" style="width: 40px">
								<label for="element_image_width" class="desc" style="font-size: 95%;color: #222222">Width</label>
								</span>
								<div style="margin-left: 5px;margin-right: 5px;float: left">
									<div class="image-lock-ratio"></div>
									<span style="margin-left: 5px;width: 14px">
										<a id="element_image_ratio_lock" href="#"><span class="icon-lock5 icon-font"></span></a>
									</span>
									<div class="image-lock-ratio"></div>
								</div>
								<span>
								<input type="text" value="" class="text medium" name="element_image_height" id="element_image_height" style="width: 40px">
								<label for="element_image_height" class="desc" style="font-size: 95%;color: #222222">Height</label>
								</span>
							</div>

							<div style="clear: both;"></div>

							<label class="desc" style="margin-top: 10px;margin-left: 4px;display: block">Image Alignment</label>
							<span>
								<input id="element_image_alignment_left"  name="element_image_alignment" class="element radio" type="radio" value="left"  />
								<label class="choice" for="element_image_alignment_left">Left</label>
							</span>
							<span>
								<input id="element_image_alignment_center"  name="element_image_alignment" class="element radio" type="radio" value="center"  />
								<label class="choice" for="element_image_alignment_center">Center</label>
							</span>
							<span>
								<input id="element_image_alignment_right"  name="element_image_alignment" class="element radio" type="radio" value="right"  />
								<label class="choice" for="element_image_alignment_right">Right</label>
							</span>

							<div style="clear: both; border-bottom: 1px dotted green;width: 90%;margin-left: 10px;margin-top: 15px;"></div>

							<label class="desc" for="element_media_image_alt" style="margin-left: 4px;margin-top: 10px">
							Alternative Text
							</label><span class="icon-question helpicon" data-tippy-content="Provide a clear text alternative of the image for screen reader users. The text should let the user know what an image's content and purpose are."></span>
							
							
							<input id="element_media_image_alt" class="text large" name="element_media_image_alt" value="" type="text" style="margin-left: 4px;width: 255px">

							<label class="desc" for="element_media_image_href" style="margin-left: 4px;margin-top: 10px">
							Image Link
							</label><span class="icon-question helpicon" data-tippy-content="Add a link to this image. (e.g. https://www.example.com)"></span>
							
							<input id="element_media_image_href" class="text large" name="element_media_image_href" value="" type="text" style="margin-left: 4px;width: 255px;margin-bottom: 10px">

							<input id="element_media_display_in_email" class="checkbox" value="" type="checkbox">
							<label class="choice" for="element_media_display_in_email">Display Image in Email</label>
							<span class="icon-question helpicon" data-tippy-content="Enable this option if you need to display the image within the notification email, review page and entries page."></span>
							</fieldset>
							
						</li>

						<li class="clear" id="prop_video_options">
							<fieldset class="choices" style="padding-bottom: 15px;">
							<legend>
							Video Options 
							</legend>
							
							<label class="desc" for="element_media_video_src" style="margin-left: 4px;margin-top: 5px">
							YouTube URL / MP4 URL
							</label><span class="icon-question helpicon" data-tippy-content="Enter YouTube URL or direct link to *.mp4 file"></span>

							
							<input id="element_media_video_src" class="text large" name="element_media_video_src" value="" type="text" style="margin-left: 4px;width: 255px;">


							<label class="desc" style="margin-top: 10px;margin-left: 4px;display: block">Video Player Size</label>
							<span>
								<input id="element_media_video_size_small"  name="element_media_video_size" class="element radio" type="radio" value="left"  />
								<label class="choice" for="element_media_video_size_small">Small</label>
							</span>
							<span>
								<input id="element_media_video_size_medium"  name="element_media_video_size" class="element radio" type="radio" value="center"  />
								<label class="choice" for="element_media_video_size_medium">Medium</label>
							</span>
							<span>
								<input id="element_media_video_size_large"  name="element_media_video_size" class="element radio" type="radio" value="right"  />
								<label class="choice" for="element_media_video_size_large">Large</label>
							</span>

							<div style="clear: both; border-bottom: 1px dotted green;width: 90%;margin-left: 10px;margin-top: 15px;margin-bottom: 15px"></div>

							<input id="element_media_video_muted" class="checkbox" value="" type="checkbox">
							<label class="choice" for="element_media_video_muted">Mute Video</label>

							<div style="clear: both;"></div>

							<input id="element_media_video_enable_caption" class="checkbox" value="" type="checkbox">
							<label class="choice" for="element_media_video_enable_caption">Enable Caption</label>
							
							<div id="div_video_caption_file" style="margin-left: 19px;width: 250px;display: none">
								<label class="desc" for="element_media_video_caption_file" style="margin-left: 4px;margin-top: 5px">
								Caption File URL (*.vtt)
								</label>
								<span class="icon-question helpicon" data-tippy-content="Enter the full URL to *.vtt file (WebVTT)"></span>
								
								<input id="element_media_video_caption_file" class="text large" name="element_media_video_caption_file" value="" type="text" style="margin-left: 4px;width: 235px;margin-bottom: 0px">
							</div>

							</fieldset>
							
						</li>

						<li class="clear" id="prop_pdf_options">
							<fieldset class="choices" style="padding-bottom: 15px;">
							<legend>
							PDF Options 
							</legend>
							
							<div class="left" style="padding-bottom: 5px">
								<input id="element_pdf_upload_file" name="element_pdf_type" class="radio" value="" type="radio">
								<label class="choice" for="element_pdf_upload_file">Upload PDF</label>
							</div>
								
							<div class="left" style="padding-left: 15px;padding-bottom: 5px">
								<input id="element_pdf_set_url" name="element_pdf_type" class="radio" value="" type="radio">
								<label class="choice" for="element_pdf_set_url">PDF File URL</label>
							</div>

							<div style="clear: both"></div>

							<div id="div_pdf_src" style="display: block;margin-bottom: 10px">
								<input id="element_pdf_src" class="text large" name="element_pdf_src" value="" type="text">
							</div>

							<div id="div_pdf_upload_file" style="display: block;margin-top: 5px;margin-bottom: 10px; margin-left: 5px">
								<input id="element_pdf_file_uploader" name="element_pdf_file_uploader" class="element file" type="file" />
							</div>
							
							<div style="clear: both;"></div>

							<label class="desc" style="margin-top: 10px;margin-left: 4px;display: block">Viewer Size</label>
							<span>
								<input id="element_pdf_size_small"  name="element_pdf_size" class="element radio" type="radio" value="small"  />
								<label class="choice" for="element_pdf_size_small">Small</label>
							</span>
							<span>
								<input id="element_pdf_size_medium"  name="element_pdf_size" class="element radio" type="radio" value="medium"  />
								<label class="choice" for="element_pdf_size_medium">Medium</label>
							</span>
							<span>
								<input id="element_pdf_size_large"  name="element_pdf_size" class="element radio" type="radio" value="large"  />
								<label class="choice" for="element_pdf_size_large">Large</label>
							</span>

							<div style="clear: both"></div>

							</fieldset>
							
						</li>

						<li class="clear" id="prop_choice_max_entry">
							<fieldset class="choices" style="padding-bottom: 10px">
							<legend>
							Choice Limit 
							</legend>
							
							<span>	
									<input id="prop_choice_max_entry_enable" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_choice_max_entry_enable">Enable Choice Limit</label>
									<span class="icon-question helpicon" data-tippy-content="Limit the number of entries per choice option. Once a choice option has reached the maximum limit, it won't be available again for future selection. This is useful for reservation or booking form."></span>
									<div id="form_choice_max_entry" style="display: none;padding-bottom: 0px">
											Only allow each choice to be selected
											<input id="choice_max_entry" style="width: 20px" class="text" value="" maxlength="255" type="text"> times
									</div>
									<div style="clear: both"></div>
							</span>
							
							</fieldset>
							
						</li>

						<li class="clear" id="prop_section_options">
							<fieldset class="choices">
							<legend>
							Section Break Options 
							</legend>
							
							<span>	
									<input id="prop_section_email_display" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_section_email_display">Display Section Break in Email</label>
									<span class="icon-question helpicon" data-tippy-content="Enable this option if you need to display the content of the section break within the notification email, review page and entries page."></span>
									<div style="clear: both"></div>
									
									<input id="prop_section_enable_scroll" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_section_enable_scroll">Enable Scrollbar</label>
									<span class="icon-question helpicon" data-tippy-content="The section break will be set to a fixed height and a vertical scrollbar will be displayed as needed. This is useful to display large amount of text, such as terms and conditions, or contract agreement."></span>
									<div id="div_section_size" style="display: none">
											Section Break Size:
											<select class="select" id="prop_section_size" autocomplete="off" tabindex="13" style="width: 100px">
												<option value="small">Small</option>
												<option value="medium">Medium</option>
												<option value="large">Large</option>
											</select>
									</div>
									<div style="clear: both"></div>
							</span>
							
							</fieldset>
							
						</li>
						
						<li class="clear" id="prop_file_options">
							<fieldset class="choices">
							<legend>
							File Types 
							</legend>
							
							<span>
									<label class="choice" for="file_type_list" style="margin-left: 10px">Accepted File Types</label>
									<span class="icon-question helpicon" data-tippy-content="Write the name extensions of the allowed file types into the textbox, separate them with commas (i.e: jpg, pdf, doc). <br/><br/>Leaving the field blank will block all file types."></span>
									<div id="form_file_limit_type">
											<textarea class="textarea" rows="10" cols="100" style="width: 250px; height: 3.7em;" id="file_type_list"></textarea>
									</div>

									<div style="clear: both"></div>									
									
									
							</span>
							
							</fieldset>
							
						</li>
						
						<li class="clear" id="prop_file_advance_options">
							<fieldset class="choices">
							<legend>
							Upload Options 
							</legend>
							
							<span>
									<input id="prop_file_as_attachment" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_file_as_attachment">Send File as Email Attachment</label>
									<span class="icon-question helpicon" data-tippy-content="By default, all file uploads will be sent to your email as a download link. Checking this option will send the file as email attachment instead. <br/><br/>WARNING: Don't enable this option if you expect to receive large files from your clients. If the files attached are larger than the allowed memory limit on your server, the email won't be sent."></span>

									<div style="clear: both"></div>

									<input id="prop_file_auto_upload" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_file_auto_upload">Automatically Upload Files</label>
									<span class="icon-question helpicon" data-tippy-content="By default, the upload button or the form submit button need to be clicked to start uploading the file. By checking this option, the file will be automatically being uploaded as soon as the file being selected."></span>
									<div style="clear: both"></div>	
									
									<input id="prop_file_multi_upload" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_file_multi_upload">Allow Multiple File Upload</label>
									<span class="icon-question helpicon" data-tippy-content="Checking this option will allow multiple files to be uploaded. You can also limit the maximum number of files to be uploaded."></span>
									<div id="form_file_max_selection">
											Limit selection to a maximum 
											<input id="file_max_selection" style="width: 20px" class="text" value="" maxlength="255" type="text"> files
									</div>
									<div style="clear: both"></div>
									
									<input id="prop_file_limit_size" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_file_limit_size">Limit File Size</label>
									<span class="icon-question helpicon" data-tippy-content="You can set the maximum size of a file allowed to be uploaded here."></span>
									<div id="form_file_limit_size">
											Limit each file to a maximum 
											<input id="file_size_max" style="width: 20px" class="text" value="" maxlength="255" type="text"> MB
									</div>
									

							</span>
							
							</fieldset>
							
						</li>
						
						<li class="clear" id="prop_range">
							<fieldset class="range">
							<legend>
								Range
							</legend>
							
							<div style="padding-left: 2px">
								<span>
								<label for="element_range_min" class="desc">Min</label>
								<input type="text" value="" class="text" name="element_range_min" id="element_range_min">
								</span>
								<span>
								<label for="element_range_max" class="desc">Max</label>
								<input type="text" value="" class="text" name="element_range_max" id="element_range_max">
								</span>
								<span>
								<label for="element_range_limit_by" class="desc">Limit By</label>
								<span style="float: none" class="icon-question helpicon" data-tippy-content="You can limit the amount of characters typed to be between certain characters or words, or between certain values in the case of number field. Leave the value blank or 0 if you don't want to set any limit."></span>
								<select class="select" name="element_range_limit_by" id="element_range_limit_by">
									<option value="c">Characters</option>
									<option value="w">Words</option>
								</select>
								<select class="select" name="element_range_number_limit_by" id="element_range_number_limit_by">
									<option value="v">Value</option>
									<option value="d">Digits</option>
								</select>
								</span>
								
							</div>
							</fieldset>
						</li>

						<li class="clear" id="prop_default_value_text">
							<fieldset class="choices">
							<legend>
							Default Value
							</legend>
							
							<div class="left" style="padding-bottom: 5px">
								<input id="element_default_value_text_static" name="element_default_value_text_type" class="radio" value="" checked="checked" type="radio">
								<label class="choice" for="element_default_value_text_static">Text</label>
								<span class="icon-question helpicon" data-tippy-content="The field will be prepopulated with the text you enter."></span>
							</div>
								
							<div class="left" style="padding-left: 15px;padding-bottom: 5px">
								<input id="element_default_value_text_random" name="element_default_value_text_type" class="radio" value="" type="radio">
								<label class="choice" for="element_default_value_text_random">Random</label>
								<span class="icon-question helpicon" data-tippy-content="The field will be prepopulated with random letters, numbers or both."></span>
							</div>

							<div id="default_value_text_static_div">
								<input id="element_default_value_text" class="text large" name="element_default_value_text" value="" type="text">
							</div>

							<div id="default_value_text_random_div">
								<span>
									<strong>Length</strong> &#8674; <input id="element_text_default_length" style="width: 20px" class="text" value="" maxlength="255" type="text"> characters of 
									<select class="select" id="element_text_default_random_type" name="element_text_default_random_type" autocomplete="off">
											<option value="letter">letters</option>
											<option value="number">numbers</option>
											<option value="alphanum">alphanumeric</option>
											<option value="all">all</option>
									</select>
								</span>
								<span>
									<strong>Prefix</strong> &#8674; <input id="element_text_default_prefix" style="width: 60px" class="text" value="" maxlength="255" type="text">
								</span>
								<span>
									<strong>Letters Case</strong> &#8674; 
									<select class="select" id="element_text_default_case" name="element_text_default_case" autocomplete="off">
											<option value="u">uppercase</option>
											<option value="l">lowercase</option>
											<option value="b">both</option>
									</select>
								</span>
							</div>
							
							<span id="prop_default_value_text_autohide_span">	
									<input id="element_enable_placeholder_text" class="checkbox" value="" type="checkbox">
									<label class="choice" for="element_enable_placeholder_text">Auto Hide Default Value</label>
									<span class="icon-question helpicon" data-tippy-content="As soon as the user types in the field, the default value will be automatically removed. Useful to provide short hint that describe the expected value of the field.<br/><br/>Tech terms: The default value will be used for the 'placeholder' attribute"></span>
							</span>

							</fieldset>
							
						</li>

						<li class="clear" id="prop_number_advance_options">
							<fieldset class="choices">
							<legend>
							Advanced Option 
							</legend>
							
							<span>
									<input id="prop_number_enable_quantity" class="checkbox" value="" type="checkbox">
									<label class="choice" for="prop_number_enable_quantity">Enable as Quantity field</label>
									<span class="icon-question helpicon" data-tippy-content="Enable this option if your form has payment enabled and need to use quantity field to calculate the total price. Select the target field for the calculation from the dropdown list. Target field type must be one of the following: Multiple Choice, Drop Down, Checkboxes, Price."></span>
									<div id="prop_number_quantity_link_div" style="display: none">
											Calculate with this field: <br />
											<select class="select large" id="prop_number_quantity_link" name="prop_number_quantity_link" style="width: 95%" autocomplete="off">
												<option value=""> -- No Supported Fields Available --</option>
											</select>
									</div>
							</span>
							
							</fieldset>
							
						</li>
						
						<li class="clear" id="prop_default_value" style="margin-top: 10px">
							<label class="desc" for="element_default">
							Default Value
							</label><span class="icon-question helpicon" data-tippy-content="By setting this value, the field will be prepopulated with the text you enter."></span>
							
							<input id="element_default_value" class="text large" name="element_default_value" value="" type="text">
						</li>

						<li class="clear" id="prop_default_phone">
							<label class="desc" for="element_default_phone">
							Default Value
							</label><span class="icon-question helpicon" data-tippy-content="By setting this value, the field will be prepopulated with the text you enter."></span>
							<div></div>
							<input id="element_default_phone1" class="text" size="3" maxlength="3" name="element_default_phone1" value="" type="text"> - 
 							<input id="element_default_phone2" class="text" size="3" maxlength="3" name="element_default_phone2" value="" type="text"> - 
							<input id="element_default_phone3" class="text" size="4" maxlength="4" name="element_default_phone3" value="" type="text">
						</li>

						<li class="clear" id="prop_default_date">
							<label class="desc" for="element_default_date">
							Default Date
							</label><span class="icon-question helpicon" data-tippy-content="Use the format ##/##/#### or any English date words, such as 'today', 'tomorrow', 'last friday', '+1 week', 'last day of next month', '3 days ago', 'monday next week'"></span>
							<div></div>
							<input id="element_default_date" class="text large" name="element_default_date" value="" type="text">
						</li>

						<li class="clear" id="prop_default_time">
							<label class="desc" for="element_default_time">
							Default Time
							</label><span class="icon-question helpicon" data-tippy-content="Example values: 'now', '+5 minute', '+2 hour', '-10 minute', '+2 hour 30 minute'"></span>
							<div></div>
							<input id="element_default_time" class="text large" name="element_default_time" value="" type="text">
						</li>
						
						<li class="clear" id="prop_default_value_textarea" style="margin-top: 10px">
							<label class="desc" for="element_default_textarea">
							Default Value
							<span class="icon-question helpicon" data-tippy-content="By setting this value, the field will be prepopulated with the text you enter."></span>
							</label>
							
							<textarea class="textarea" rows="10" cols="50" id="element_default_value_textarea" name="element_default_value_textarea"></textarea>
						</li>
						
						<li class="clear" id="prop_placeholder" style="margin-bottom: 10px">
							<span>
								<input id="element_enable_placeholder" class="checkbox" value="" type="checkbox"> 
								<label class="choice" for="element_enable_placeholder">Auto Hide Default Value</label>
								<span class="icon-question helpicon" data-tippy-content="As soon as the user types in the field, the default value will be automatically removed. Useful to provide short hint that describe the expected value of the field.<br/><br/>Tech terms: The default value will be used for the 'placeholder' attribute"></span>
							</span>
						</li>

						<li class="clear" id="prop_default_country">
							<label class="desc" for="element_countries">
							Default Country
							</label><span class="icon-question helpicon" data-tippy-content="By setting this value, the country field will be prepopulated with the selection you make."></span>
							<select class="select" id="element_countries" name="element_countries">
							<option value=""></option>
							
							<optgroup label="North America">
							<option value="Antigua and Barbuda">Antigua and Barbuda</option>
							<option value="Bahamas">Bahamas</option>
							<option value="Barbados">Barbados</option> 
							<option value="Belize">Belize</option> 
							<option value="Canada">Canada</option> 
							<option value="Costa Rica">Costa Rica</option> 
							<option value="Cuba">Cuba</option> 
							<option value="Dominica">Dominica</option> 
							<option value="Dominican Republic">Dominican Republic</option>
							<option value="El Salvador">El Salvador</option>
							<option value="Grenada">Grenada</option> 
							<option value="Guatemala">Guatemala</option> 
							<option value="Haiti">Haiti</option> 
							<option value="Honduras">Honduras</option> 
							<option value="Jamaica">Jamaica</option> 
							<option value="Mexico">Mexico</option> 
							<option value="Nicaragua">Nicaragua</option> 
							<option value="Panama">Panama</option> 
							<option value="Puerto Rico">Puerto Rico</option> 
							<option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option> 
							<option value="Saint Lucia">Saint Lucia</option>
							<option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option> 
							<option value="Trinidad and Tobago">Trinidad and Tobago</option>
							<option value="United States">United States</option>
							</optgroup>
							
							<optgroup label="South America">
							<option value="Argentina">Argentina</option>
							<option value="Bolivia">Bolivia</option> 
							<option value="Brazil">Brazil</option> 
							<option value="Chile">Chile</option> 
							<option value="Columbia">Columbia</option>
							<option value="Ecuador">Ecuador</option> 
							<option value="Guyana">Guyana</option> 
							<option value="Paraguay">Paraguay</option> 
							<option value="Peru">Peru</option> 
							<option value="Suriname">Suriname</option> 
							<option value="Uruguay">Uruguay</option> 
							<option value="Venezuela">Venezuela</option>
							</optgroup>
							
							<optgroup label="Europe">
							<option value="Albania">Albania</option>
							<option value="Andorra">Andorra</option>
							<option value="Armenia">Armenia</option>
							<option value="Austria">Austria</option>
							<option value="Azerbaijan">Azerbaijan</option>
							<option value="Belarus">Belarus</option>
							<option value="Belgium">Belgium</option>
							<option value="Bermuda">Bermuda</option>  
							<option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
							<option value="Bulgaria">Bulgaria</option> 
							<option value="Croatia">Croatia</option> 
							<option value="Cyprus">Cyprus</option> 
							<option value="Czech Republic">Czech Republic</option>
							<option value="Denmark">Denmark</option> 
							<option value="Estonia">Estonia</option> 
							<option value="Finland">Finland</option> 
							<option value="France">France</option> 
							<option value="Georgia">Georgia</option>
							<option value="Germany">Germany</option>
							<option value="Gibraltar">Gibraltar</option>
							<option value="Greece">Greece</option>
							<option value="Guernsey">Guernsey</option>
							<option value="Hungary">Hungary</option> 
							<option value="Iceland">Iceland</option> 
							<option value="Ireland">Ireland</option> 
							<option value="Italy">Italy</option>
							<option value="Jersey">Jersey</option>
							<option value="Kosovo">Kosovo</option> 
							<option value="Latvia">Latvia</option> 
							<option value="Liechtenstein">Liechtenstein</option>
							<option value="Lithuania">Lithuania</option> 
							<option value="Luxembourg">Luxembourg</option> 
							<option value="Macedonia">Macedonia</option> 
							<option value="Malta">Malta</option> 
							<option value="Moldova">Moldova</option> 
							<option value="Monaco">Monaco</option> 
							<option value="Montenegro">Montenegro</option> 
							<option value="Netherlands">Netherlands</option> 
							<option value="Norway">Norway</option> 
							<option value="Poland">Poland</option> 
							<option value="Portugal">Portugal</option>
							<option value="Romania">Romania</option> 
							<option value="San Marino">San Marino</option>
							<option value="Serbia">Serbia</option>
							<option value="Slovakia">Slovakia</option>
							<option value="Slovenia">Slovenia</option> 
							<option value="Spain">Spain</option> 
							<option value="Sweden">Sweden</option> 
							<option value="Switzerland">Switzerland</option> 
							<option value="Ukraine">Ukraine</option> 
							<option value="United Kingdom">United Kingdom</option>
							<option value="Vatican City">Vatican City</option>
							</optgroup>
							
							<optgroup label="Asia">
							<option value="Afghanistan">Afghanistan</option>
							<option value="Bahrain">Bahrain</option>
							<option value="Bangladesh">Bangladesh</option>
							<option value="Bhutan">Bhutan</option>
							<option value="Brunei Darussalam">Brunei Darussalam</option>
							<option value="Myanmar">Myanmar</option>
							<option value="Cambodia">Cambodia</option>
							<option value="China">China</option>
							<option value="East Timor">East Timor</option>
							<option value="Hong Kong">Hong Kong</option> 
							<option value="India">India</option>
							<option value="Indonesia">Indonesia</option>
							<option value="Iran">Iran</option>
							<option value="Iraq">Iraq</option>
							<option value="Israel">Israel</option>
							<option value="Japan">Japan</option>
							<option value="Jordan">Jordan</option>
							<option value="Kazakhstan">Kazakhstan</option>
							<option value="North Korea">North Korea</option>
							<option value="South Korea">South Korea</option>
							<option value="Kuwait">Kuwait</option> 
							<option value="Kyrgyzstan">Kyrgyzstan</option> 
							<option value="Laos">Laos</option> 
							<option value="Lebanon">Lebanon</option> 
							<option value="Malaysia">Malaysia</option> 
							<option value="Maldives">Maldives</option> 
							<option value="Mongolia">Mongolia</option> 
							<option value="Nepal">Nepal</option> 
							<option value="Oman">Oman</option> 
							<option value="Pakistan">Pakistan</option> 
							<option value="Palestine">Palestine</option>
							<option value="Philippines">Philippines</option> 
							<option value="Qatar">Qatar</option> 
							<option value="Russia">Russia</option> 
							<option value="Saudi Arabia">Saudi Arabia</option> 
							<option value="Singapore">Singapore</option> 
							<option value="Sri Lanka">Sri Lanka</option>
							<option value="Syria">Syria</option>
							<option value="Taiwan">Taiwan</option> 
							<option value="Tajikistan">Tajikistan</option> 
							<option value="Thailand">Thailand</option> 
							<option value="Turkey">Turkey</option> 
							<option value="Turkmenistan">Turkmenistan</option> 
							<option value="United Arab Emirates">United Arab Emirates</option>
							<option value="Uzbekistan">Uzbekistan</option> 
							<option value="Vietnam">Vietnam</option> 
							<option value="Yemen">Yemen</option>
							</optgroup>
							
							<optgroup label="Oceania">
							<option value="Australia">Australia</option>
							<option value="Fiji">Fiji</option> 
							<option value="Kiribati">Kiribati</option>
							<option value="Marshall Islands">Marshall Islands</option> 
							<option value="Micronesia">Micronesia</option> 
							<option value="Nauru">Nauru</option> 
							<option value="New Zealand">New Zealand</option>
							<option value="Palau">Palau</option>
							<option value="Papua New Guinea">Papua New Guinea</option>
							<option value="Samoa">Samoa</option> 
							<option value="Solomon Islands">Solomon Islands</option>
							<option value="Tonga">Tonga</option> 
							<option value="Tuvalu">Tuvalu</option>  
							<option value="Vanuatu">Vanuatu</option>
							</optgroup>
							
							<optgroup label="Africa">
							<option value="Algeria">Algeria</option> 
							<option value="Angola">Angola</option> 
							<option value="Benin">Benin</option> 
							<option value="Botswana">Botswana</option> 
							<option value="Burkina Faso">Burkina Faso</option> 
							<option value="Burundi">Burundi</option> 
							<option value="Cameroon">Cameroon</option> 
							<option value="Cape Verde">Cape Verde</option>
							<option value="Central African Republic">Central African Republic</option>
							<option value="Chad">Chad</option>  
							<option value="Comoros">Comoros</option>  
							<option value="Congo">Congo</option>
							<option value="Djibouti">Djibouti</option> 
							<option value="Egypt">Egypt</option> 
							<option value="Equatorial Guinea">Equatorial Guinea</option> 
							<option value="Eritrea">Eritrea</option> 
							<option value="Ethiopia">Ethiopia</option> 
							<option value="Gabon">Gabon</option> 
							<option value="Gambia">Gambia</option> 
							<option value="Ghana">Ghana</option> 
							<option value="Guinea">Guinea</option> 
							<option value="Guinea-Bissau">Guinea-Bissau</option>
							<option value="Côte d'Ivoire">Côte d'Ivoire</option> 
							<option value="Kenya">Kenya</option> 
							<option value="Lesotho">Lesotho</option> 
							<option value="Liberia">Liberia</option> 
							<option value="Libya">Libya</option> 
							<option value="Madagascar">Madagascar</option> 
							<option value="Malawi">Malawi</option> 
							<option value="Mali">Mali</option>
							<option value="Mauritania">Mauritania</option> 
							<option value="Mauritius">Mauritius</option> 
							<option value="Morocco">Morocco</option> 
							<option value="Mozambique">Mozambique</option> 
							<option value="Namibia">Namibia</option>
							<option value="Niger">Niger</option>
							<option value="Nigeria">Nigeria</option> 
							<option value="Rwanda">Rwanda</option> 
							<option value="Sao Tome and Principe">Sao Tome and Principe</option>
							<option value="Senegal">Senegal</option> 
							<option value="Seychelles">Seychelles</option> 
							<option value="Sierra Leone">Sierra Leone</option>
							<option value="Somalia">Somalia</option> 
							<option value="South Africa">South Africa</option>
							<option value="Sudan">Sudan</option> 
							<option value="Swaziland">Swaziland</option> 
							<option value="United Republic of Tanzania">Tanzania</option>
							<option value="Togo">Togo</option> 
							<option value="Tunisia">Tunisia</option> 
							<option value="Uganda">Uganda</option> 
							<option value="Zambia">Zambia</option> 
							<option value="Zimbabwe">Zimbabwe</option>
							</optgroup>
							</select>
						</li>
						
						
						<li class="clear" id="prop_instructions">
							<label class="desc" for="element_instructions">
							Guidelines for User 
							</label>
							<span class="icon-question helpicon" data-tippy-content="This text will be displayed to your users while they're filling out particular field."></span>

							<textarea class="textarea" rows="10" cols="50" id="element_instructions"></textarea>
						</li>
						
						<li class="clear" id="prop_custom_css">
							<label class="desc" for="element_custom_css">
							Custom CSS Class
							</label>
							<span class="icon-question helpicon" data-tippy-content="This is an advanced option. You can add custom CSS classnames to the parent element of the field. This is useful if you would like to customize the styling for each of your field using your own CSS code. These custom class names will not appear live in the form builder, only on the live form."></span>
							
							<input id="element_custom_css" class="text large" name="element_custom_css" value="" maxlength="255" type="text">
						</li>

						<li class="clear" id="prop_section508_note">
							<div><span style="clear: both;font-size:150%;display: block" class="icon-info"></span> This field makes this form <a href="https://www.section508.gov" target="_blank">Section 508</a> non-compliant</div>
						</li>
						
						<li class="clear" id="prop_page_break_button" style="margin-top: 50px;margin-bottom: 50px">
								<fieldset style="padding-top: 15px">
								<legend>Page Submit Buttons</legend>
								
								<div class="left" style="padding-bottom: 5px">
								<input id="prop_submit_use_text" name="submit_use_image" class="radio" value="0" type="radio">
								<label class="choice" for="prop_submit_use_text">Use Text Button</label>
								<span class="icon-question helpicon" data-tippy-content="This is the default and recommended option. All buttons will use simple text. You can change the text being used on each page submit/back button."></span>
								</div>
								
								<div class="left" style="padding-left: 5px;padding-bottom: 5px">
								<input id="prop_submit_use_image" name="submit_use_image" class="radio" value="1" type="radio">
								<label class="choice" for="prop_submit_use_image">Use Image Button</label>
								<span class="icon-question helpicon" data-tippy-content="Select this option if you prefer to use your own submit/back image buttons. Make sure to enter the full URL address to your images."></span>
								</div>
								
								<div id="div_submit_use_text" class="left" style="padding-left: 8px;padding-bottom: 10px;width: 92%">
								<label class="desc" for="submit_primary_text">Submit Button</label>
								<input id="submit_primary_text" class="text large" name="submit_primary_text" value="" type="text">
								<label id="lbl_submit_secondary_text" class="desc" for="submit_secondary_text" style="margin-top: 10px">Back Button</label>
								<input id="submit_secondary_text" class="text large" name="submit_secondary_text" value="" type="text">
								</div>
								
								<div id="div_submit_use_image" class="left" style="padding-left: 8px;padding-bottom: 10px;width: 92%; display: none">
								<label class="desc" for="submit_primary_img">Submit Button. Image URL:</label>
								<input id="submit_primary_img" class="text large" name="submit_primary_img" value="http://" type="text">
								<label id="lbl_submit_secondary_img" class="desc" for="submit_secondary_img" style="margin-top: 10px">Back Button. Image URL:</label>
								<input id="submit_secondary_img" class="text large" name="submit_secondary_img" value="http://" type="text">
								</div>
								</fieldset>
						</li>
						
						</ul>
					</div>
					
				</form>
			</div> <!-- end field properties pane -->
		</div>
		
		<div id="tab_form_properties" style="display: none">
			<div id="form_properties_pane" class="box">
				<div id="form_properties_holder">
						<!--  start form properties pane -->
						<form id="form_properties" action="" onsubmit="return false;">
							<ul id="all_form_properties">
							<li class="form_prop">
								<label class="desc" for="form_title">Form Title 
								</label>
								<span class="icon-question helpicon" data-tippy-content="The title of your form displayed to the user when they see your form."></span>
								<input id="form_title" name="form_title" class="text large" value="" tabindex="1"  type="text">
							</li>
							<li class="form_prop">
								<label class="desc" for="form_description">Description 
								</label>
								<span class="icon-question helpicon" data-tippy-content="This will appear directly below the form name. Useful for displaying a short description or any instructions, notes, guidelines."></span>
								<textarea class="textarea small" rows="10" cols="50" id="form_description" tabindex="2"></textarea>
							</li>
							<li class="form_prop" style="margin-bottom: 20px">
								<span>
									<input id="form_name_hide" class="checkbox" value="" type="checkbox"> 
									<label class="choice" for="form_name_hide">Hide Title and Description from Public View</label>
								</span>
							</li>
							
							<li id="form_prop_confirmation" class="form_prop">
								<fieldset>
								<legend>Submission Confirmation</legend>
								
								<div class="left" style="padding-bottom: 5px">
								<input id="form_success_message_option" name="confirmation" class="radio" value="" checked="checked" type="radio">
								<label class="choice" for="form_success_message_option">Show Text</label>
								<span class="icon-question helpicon" data-tippy-content="This message will be displayed after your users have successfully submitted an entry. <br/><br/>You can enter any HTML codes, Javascript codes or Merge Tags as well."></span>
								</div>
								
								<div class="left" style="padding-left: 15px;padding-bottom: 5px">
								<input id="form_redirect_option" name="confirmation" class="radio" value="" type="radio">
								<label class="choice" for="form_redirect_option">Redirect to Web Site</label>
								<span class="icon-question helpicon" data-tippy-content="After your users have successfully submitted an entry, you can redirect them to another 
								website/URL of your choice.<br/><br/>You can insert Merge Tags into the URL to pass form data."></span>
								</div>
								
								<textarea class="textarea" rows="10" cols="50" id="form_success_message" tabindex="9"></textarea>
								
								<input id="form_redirect_url" class="text hide" name="form_redirect_url" value="http://" type="text">
								</fieldset>
							</li>
							
							<li id="form_prop_toggle" class="form_prop">
							<div style="text-align: right">
								<a href=""  id="form_prop_toggle_a">show more options</a> 	
							</div> 
							</li>
							
							<li id="form_prop_language" class="leftCol advanced_prop form_prop">
								<label class="desc">
								Language 
								</label><span class="icon-question helpicon" data-tippy-content="You can choose the language being used to display your form messages."></span>
								<div>
								<select autocomplete="off" id="form_language" class="select large">
								<option value="bulgarian">Bulgarian</option>
								<option value="chinese">Chinese (Traditional)</option>
								<option value="chinese_simplified">Chinese (Simplified)</option>
								<option value="czech">Czech</option>
								<option value="danish">Danish</option>
								<option value="dutch">Dutch</option>
								<option value="english">English</option>
								<option value="estonian">Estonian</option>
								<option value="finnish">Finnish</option>
								<option value="french">French</option>
								<option value="german">German</option>
								<option value="greek">Greek</option>
								<option value="hungarian">Hungarian</option>
								<option value="indonesia">Indonesia</option>
								<option value="italian">Italian</option>
								<option value="japanese">Japanese</option>
								<option value="korean">Korean</option>
								<option value="norwegian">Norwegian</option>
								<option value="polish">Polish</option>
								<option value="portuguese">Portuguese</option>
								<option value="romanian">Romanian</option>
								<option value="russian">Russian</option>
								<option value="slovak">Slovak</option>
								<option value="spanish">Spanish</option>
								<option value="swedish">Swedish</option>
								<option value="turkish">Turkish</option>
								</select>
								</div>
							</li>
							
							<li id="form_prop_label_alignment" class="rightCol advanced_prop form_prop">
								<label for="form_label_alignment" class="desc">Label Alignment 
								</label><span class="icon-question helpicon" data-tippy-content="Set the field label placement"></span>
								<div>
								<select autocomplete="off" id="form_label_alignment" class="select large">
								<option value="top_label">Top Aligned</option>
								<option value="left_label">Left Aligned</option>
								<option value="right_label">Right Aligned</option>
								</select>
								</div>
							</li>
							
							
								
						
							
							<li id="form_prop_processing" class="clear advanced_prop form_prop">
								<fieldset>
								<legend>End-Users Options</legend>
									<span>
										<input id="form_resume" class="checkbox" value="" type="checkbox"> 
										<label class="choice" for="form_resume">Allow Users to Save and Resume Later</label>
										<span class="icon-question helpicon" data-tippy-content="Checking this will display additional link at the bottom of your form which would allow your clients to save their progress and resume later. This option only available if your form has at least two pages (has one or more Page Break field)."></span>
									</span><br>
									<span>
										<input id="form_allow_edit_completed_entry" class="checkbox" value="" type="checkbox"> 
										<label class="choice" for="form_allow_edit_completed_entry">Allow Users to Edit Completed Submission</label>
										<span class="icon-question helpicon" data-tippy-content="If enabled, form users will be able to edit their submission by clicking a special edit link sent through email (no login required). You can control the edit link expiry or the maximum number of revisions."></span>
									</span><br>
									<div id="div_form_allow_edit_completed_entry" style="display: none">
										<span>
											<input id="form_resend_notifications" class="checkbox" value="" type="checkbox"> 
											<label class="choice" for="form_resend_notifications">Resend Notifications</label>
											<span class="icon-question helpicon" data-tippy-content="Resend email notifications each time the client updated their submission."></span>
										</span><br>
										<span>
											<input id="form_reprocess_logics" class="checkbox" value="" type="checkbox"> 
											<label class="choice" for="form_reprocess_logics">Resend Logic Notifications</label>
											<span class="icon-question helpicon" data-tippy-content="Resend any notifications coming from logic rules (logic for notification emails and sending form data to another website) each time the client updated their submission."></span>
										</span><br>
										
										<div style="display: block">
											<input id="form_disable_editing_enable" class="checkbox" value="" type="checkbox"> 
											<label class="choice" for="form_disable_editing_enable">Disable Editing After: </label>
											<input id="form_disable_editing_value" style="width: 25px" class="text" value="" maxlength="255" type="text"> 
											<select autocomplete="off" id="form_disable_editing_period" class="select">
												<option value="r">revision</option>
												<option value="h">hour</option>
												<option value="d">day</option>
											</select>
										</div>
										<span>
											<input id="form_hide_editlink" class="checkbox" value="" type="checkbox"> 
											<label class="choice" for="form_hide_editlink">Hide Edit Link</label>
											<span class="icon-question helpicon" data-tippy-content="If enabled, form users won't receive any edit link within the email or success page. You'll need to manually send the edit link."></span>
										</span><br>
									</div>
									<span>	
										<input id="form_review" class="checkbox" value="" type="checkbox">
										<label class="choice" for="form_review">Show Review Page Before Submitting</label>
										<span class="icon-question helpicon" data-tippy-content="If enabled, your clients will be prompted to a preview page that lets them double check their entries before submitting the form."></span>
									</span><br>
								</fieldset>
							</li>
							
							<li class="clear advanced_prop form_prop" id="form_prop_resume" style="display: none;zoom: 1">
								<fieldset style="padding-top: 15px;padding-bottom: 20px">
								<legend>Resume Email Options</legend>
								
								<label class="desc" for="form_review_title">
								Resume Email Subject
								</label>
								
								<input id="form_resume_subject" class="text large" name="form_resume_subject" value="" maxlength="255" type="text">
								
								<label class="desc" for="form_resume_content">
								Resume Email Content 
								</label><span class="icon-question helpicon" data-tippy-content="You can insert merge tags and HTML codes into Resume Email Subject and Resume Email Content."></span>
								
								<textarea class="textarea" rows="10" cols="50" id="form_resume_content" style="height: 5em"></textarea>
								
								<div style="border-bottom: 1px dashed green; height: 15px;margin: 5px 10px 10px 0"></div>

								<label class="desc" for="form_resume_from_name">
								From Name
								</label>
								<input id="form_resume_from_name" class="text large" name="form_resume_from_name" value="" maxlength="255" type="text">

								<label class="desc" for="form_resume_from_email_address">
								From Email
								</label>
								<input id="form_resume_from_email_address" class="text large" name="form_resume_from_email_address" value="" maxlength="255" type="text">

								</fieldset>
							</li>

							<li class="clear advanced_prop form_prop" id="form_prop_review" style="display: none;zoom: 1">
								<fieldset style="padding-top: 15px">
								<legend>Review Page Options</legend>
								
								<label class="desc" for="form_review_title">
								Review Page Title
								</label><span class="icon-question helpicon" data-tippy-content="Enter the title to be displayed on the review page."></span>
								
								<input id="form_review_title" class="text large" name="form_review_title" value="" maxlength="255" type="text">
								
								<label class="desc" for="form_review_description">
								Review Page Description 
								</label><span class="icon-question helpicon" data-tippy-content="Enter some brief description to be displayed on the review page."></span>
								
								<textarea class="textarea" rows="10" cols="50" id="form_review_description" style="height: 2.5em"></textarea>
								<div style="border-bottom: 1px dashed green; height: 15px;margin-right: 10px"></div>
								<div class="left" style="padding-bottom: 5px;margin-top: 12px">
								<input id="form_review_use_text" name="form_review_option" class="radio" value="0" type="radio">
								<label class="choice" for="form_review_use_text">Use Text Button</label>
								<span class="icon-question helpicon" data-tippy-content="This is the default and recommended option. All buttons on review page will use simple text."></span>
								</div>
								
								<div class="left" style="padding-left: 5px;padding-bottom: 5px;margin-top: 12px">
								<input id="form_review_use_image" name="form_review_option" class="radio" value="1" type="radio">
								<label class="choice" for="form_review_use_image">Use Image Button</label>
								<span class="icon-question helpicon" data-tippy-content="Select this option if you prefer to use your own submit/back image buttons. Make sure to enter the full URL address to your images."></span>
								</div>
								
								<div id="div_review_use_text" class="left" style="padding-left: 8px;padding-bottom: 10px;width: 92%">
								<label class="desc" for="review_primary_text">Submit Button</label>
								<input id="review_primary_text" class="text large" name="review_primary_text" value="" type="text">
								<label id="lbl_review_secondary_text" class="desc" for="review_secondary_text" style="margin-top: 3px">Back Button</label>
								<input id="review_secondary_text" class="text large" name="review_secondary_text" value="" type="text">
								</div>
								
								<div id="div_review_use_image" class="left" style="padding-left: 8px;padding-bottom: 10px;width: 92%;display: none">
								<label class="desc" for="review_primary_img">Submit Button. Image URL:</label>
								<input id="review_primary_img" class="text large" name="review_primary_img" value="http://" type="text">
								<label id="lbl_review_secondary_img" class="desc" for="review_secondary_img" style="margin-top: 3px">Back Button. Image URL:</label>
								<input id="review_secondary_img" class="text large" name="review_secondary_img" value="http://" type="text">
								</div>
								</fieldset>
							</li>
							 
							<li id="form_prop_protection" class="advanced_prop form_prop">
								<fieldset>
								<legend>Security</legend>
									<span>	
										<input id="form_password_option" class="checkbox" value=""  type="checkbox">
										<label class="choice" for="form_password_option">Enable Password Protection</label>
										<span class="icon-question helpicon" data-tippy-content="If enabled, all users accessing the public form will then be required to type in the password to access the form. Your form is password protected."></span>
										<div id="form_password" style="display: none;padding-bottom: 10px;">
											<img src="images/icons/key.png" alt="Password : " style="vertical-align: middle">
											<input id="form_password_data" autocomplete="off" style="width: 50%" class="text" value="" size="25"  type="text">
										</div>
									</span>
									
									<span style="clear: both;display: block">
										<input id="form_captcha" class="checkbox" value="" type="checkbox">
										<label class="choice" for="form_captcha">Enable Spam Protection (CAPTCHA)</label>
										<span class="icon-question helpicon" data-tippy-content="If enabled, an image with random words will be generated and users will be required to enter the correct words in order to submit the form. This is useful to prevent abuse from bots or automated programs usually written to generate spam."></span>
										<div id="form_captcha_type_option" style="display: block;padding-bottom: 10px;">
											
											<label class="choice" for="form_captcha_type">Type: </label>
											<select class="select" id="form_captcha_type" name="form_captcha_type" autocomplete="off">
												<option value="n">reCAPTCHA V2</option>
												<option value="i">Simple Image</option>
												<option value="t">Simple Text</option>
											</select>
											<span class="icon-question helpicon" data-tippy-content="You can select the difficulty level of the spam protection.
											 <br/>
											 <br/>
											 reCAPTCHA V2 : Display an image with distorted words. Improved version of reCAPTCHA by Google. If you use this, you'll need to fill your keys into the Settings page.
											 <br/>
											 <br/> 
											Simple Image : Display an image with a clear and sharp words. Most people will find this easy to read.
											 <br/>
											 <br/>
											Simple Text : Display a text (not an image) which contain simple question to solve."></span>
										</div>
										<div id="form_prop_captcha508" style="display: none"><span style="vertical-align: middle;font-size:120%;" class="icon-info"></span> This type of CAPTCHA makes this form <a href="https://www.section508.gov" target="_blank">Section 508</a> non-compliant</div>
									</span>

									<span style="clear: both;display: block">
										<input id="form_keyword_blocking_enable" class="checkbox" value="" type="checkbox">
										<label class="choice" for="form_keyword_blocking_enable">Enable Keyword Blocking</label>
										<span class="icon-question helpicon" data-tippy-content="Block form submission if it contains any of the keywords specified here. Enter one keyword per line, or separated by commas.
											 <br/>
											 <br/>
											 <b>USE WITH CAUTION</b> as you might inadvertently block valid form submission if you're using a broad / generic keyword."></span>
										<div id="form_keyword_blocking_div" style="display: none;padding-bottom: 10px;">
											<textarea class="textarea" rows="10" cols="50" id="form_keyword_blocking_list" tabindex="9" placeholder="Enter keywords, one per line or separated by commas."></textarea>
										</div>
									</span>

									<span style="clear: both;display: block">
										<input id="form_unique_ip" class="checkbox" value="" type="checkbox">
										<label class="choice" for="form_unique_ip">Limit Entry Per IP Address</label>
										<span class="icon-question helpicon" data-tippy-content="Use this to prevent each client from filling out your form more than the allowed limit within a certain period. This is done by comparing client's IP Address."></span>
										<div id="form_unique_ip_div" style="display: block">
											Maximum 
											<input id="form_unique_ip_maxcount" style="width: 25px" class="text" value="" maxlength="255" type="text"> entries per
											<select autocomplete="off" id="form_unique_ip_period" class="select">
												<option value="h">hour</option>
												<option value="d">day</option>
												<option value="w">week</option>
												<option value="m">month</option>
												<option value="y">year</option>
												<option value="l">lifetime</option>
											</select>
										</div>
									</span>
									<span style="clear: both;display: block">	
										<input id="form_limit_option" class="checkbox" value="" type="checkbox">
										<label class="choice" for="form_limit_option">Limit Submission</label>
										<span class="icon-question helpicon" data-tippy-content="The form will be turned off after reaching the number of entries defined here."></span>
										<div id="form_limit_div" style="display: none">
											<img src="images/icons/flag_red.png" alt="Maximum accepted entries : " style="vertical-align: middle"> Maximum accepted entries:
											<input id="form_limit" style="width: 20%" class="text" value="" maxlength="255" type="text">
										</div>
									</span>
									<span style="clear: both;display: block">
										 <input id="form_encryption_enable" class="checkbox" value="" type="checkbox">
										 <label class="choice" for="form_encryption_enable">Enable Data Encryption</label>
								  		 <span class="icon-question helpicon" data-tippy-content="If enabled, you'll be able to encrypt the data of <strong>Single Line Text</strong> and <strong>Paragraph Text</strong> fields on your form to add an extra layer of security.<br/><br/> Encrypted data can only be read with the <strong>Private Key</strong> known only to you, so if the data were to be compromised it could not be read. <br/><br/>Use this feature if you're collecting sensitive data."></span>
								  	</span>
								</fieldset>
							</li>
							
							
							<li id="form_prop_scheduling" class="clear advanced_prop form_prop">
								
								<fieldset>
								 <legend>Automatic Scheduling</legend> 
								 <div style="padding-bottom: 10px">
								 <input id="form_schedule_enable" class="checkbox" value="" style="float: left"  type="checkbox">
								 <label class="choice" style="float: left;margin-left: 5px;margin-right:3px;line-height: 1.7" for="form_schedule_enable">Enable Automatic Scheduling</label>
								 <span class="icon-question helpicon" data-tippy-content="If you would like to schedule your form to become active at certain period of time only, enable this option."></span>
								 </div>
								<div id="form_prop_scheduling_start" style="display: none;clear: both">
								
									<label class="desc" style="display: block">Only Accept Submission From Date: </label> 
									
									<span>
									<input type="text" value="" maxlength="2" size="2" style="width: 1.5em;" class="text" name="scheduling_start_mm" id="scheduling_start_mm">
									<label for="scheduling_start_mm">MM</label>
									</span>
									
									<span>
									<input type="text" value="" maxlength="2" size="2" style="width: 1.5em;" class="text" name="scheduling_start_dd" id="scheduling_start_dd">
									<label for="scheduling_start_dd">DD</label>
									</span>
									
									<span>
									 <input type="text" value="" maxlength="4" size="4" style="width: 3em;" class="text" name="scheduling_start_yyyy" id="scheduling_start_yyyy">
									<label for="scheduling_start_yyyy">YYYY</label>
									</span>
									
									<span id="scheduling_cal_start">
											<input type="hidden" value="" maxlength="4" size="4" style="width: 3em;" class="text" name="linked_picker_scheduling_start" id="linked_picker_scheduling_start">
											<div style="display: none"><img id="scheduling_start_pick_img" alt="Pick date." src="images/icons/calendar.png" class="trigger" style="margin-top: 3px; cursor: pointer" /></div>
									</span>
									<span>
									<select name="scheduling_start_hour" id="scheduling_start_hour" class="select"> 
									<option value="01">1</option>
									<option value="02">2</option>
									<option value="03">3</option>
									<option value="04">4</option>
									<option value="05">5</option>
									<option value="06">6</option>
									<option value="07">7</option>
									<option value="08">8</option>
									<option value="09">9</option>
									<option value="10">10</option>
									<option value="11">11</option>
									<option value="12">12</option>
									</select>
									<label for="scheduling_start_hour">HH</label>
									</span>
									<span>
									<select name="scheduling_start_minute" id="scheduling_start_minute" class="select"> 
									<option value="00">00</option>
									<option value="15">15</option>
									<option value="30">30</option>
									<option value="45">45</option>
									</select>
									<label for="scheduling_start_minute">MM</label>
									</span>
									<span>
									<select name="scheduling_start_ampm" id="scheduling_start_ampm" class="select"> 
									<option value="am">AM</option>
									<option value="pm">PM</option>
									</select>
									<label for="scheduling_start_ampm">AM/PM</label>
									</span>

								</div>
									
								<div id="form_prop_scheduling_end" style="display: none">
								
									<label class="desc" style="display: block">Until Date:</label>
									<span>
									<input type="text" value="" maxlength="2" size="2" style="width: 1.5em;" class="text" name="scheduling_end_mm" id="scheduling_end_mm">
									<label for="scheduling_end_mm">MM</label>
									</span>
									
									<span>
									<input type="text" value="" maxlength="2" size="2" style="width: 1.5em;" class="text" name="scheduling_end_dd" id="scheduling_end_dd">
									<label for="scheduling_end_dd">DD</label>
									</span>
									
									<span>
									 <input type="text" value="" maxlength="4" size="4" style="width: 3em;" class="text" name="scheduling_end_yyyy" id="scheduling_end_yyyy">
									<label for="scheduling_end_yyyy">YYYY</label>
									</span>
									
									<span id="scheduling_cal_end">
											<input type="hidden" value="" maxlength="4" size="4" style="width: 3em;" class="text" name="linked_picker_scheduling_end" id="linked_picker_scheduling_end">
											<div style="display: none"><img id="scheduling_end_pick_img" alt="Pick date." src="images/icons/calendar.png" class="trigger" style="margin-top: 3px; cursor: pointer" /></div>
									</span>
									<span>
									<select name="scheduling_end_hour" id="scheduling_end_hour" class="select"> 
									<option value="01">1</option>
									<option value="02">2</option>
									<option value="03">3</option>
									<option value="04">4</option>
									<option value="05">5</option>
									<option value="06">6</option>
									<option value="07">7</option>
									<option value="08">8</option>
									<option value="09">9</option>
									<option value="10">10</option>
									<option value="11">11</option>
									<option value="12">12</option>
									</select>
									<label for="scheduling_end_hour">HH</label>
									</span>
									<span>
									<select name="scheduling_end_minute" id="scheduling_end_minute" class="select"> 
									<option value="00">00</option>
									<option value="15">15</option>
									<option value="30">30</option>
									<option value="45">45</option>
									</select>
									<label for="scheduling_end_minute">MM</label>
									</span>
									<span>
									<select name="scheduling_end_ampm" id="scheduling_end_ampm" class="select"> 
									<option value="am">AM</option>
									<option value="pm">PM</option>
									</select>
									<label for="scheduling_end_ampm">AM/PM</label>
									</span>

								</div>
								
								</fieldset>
							</li>

							<li id="form_prop_advanced_option" class="clear advanced_prop form_prop">
								
								<fieldset>
								  <legend>Advanced Option</legend> 
								  
								  <div style="padding-bottom: 10px">
										 <input id="form_custom_script_enable" class="checkbox" value="1" style="float: left"  type="checkbox">
										 <label class="choice" style="float: left;margin-left: 5px;margin-right:3px;line-height: 1.7" for="form_custom_script_enable">Load Custom Javascript File</label>
								  		 <span class="icon-question helpicon" data-tippy-content="You can register your own custom javascript file to run inline with the form. Your script will be loaded each time the form is being displayed."></span>
								  </div>
								  <div id="form_custom_script_div" style="display: none; margin-left: 25px;margin-bottom: 10px">
										<label class="desc" for="form_custom_script_url">Script URL:</label>
										<input id="form_custom_script_url" name="form_custom_script_url" style="width: 90%" class="text" value=""  type="text">
								  </div>

								  <div style="padding-bottom: 10px">
										 <input id="form_approval_enable" class="checkbox" value="1" style="float: left"  type="checkbox">
										 <label class="choice" style="float: left;margin-left: 5px;margin-right:3px;" for="form_approval_enable">Enable Approval Workflow</label>
								  		 <span class="icon-question helpicon" data-tippy-content="If enabled, you'll be able to setup admin users to approve or deny submissions. <br/><br/>Once enabled, go to Form Manager and click the <strong>Approval</strong> link to configure the approvers and workflow."></span>
								  </div>
					
								</fieldset>
							</li>
							
							<li class="clear advanced_prop form_prop" id="form_prop_approval_email" style="display: none;zoom: 1">
								<fieldset style="padding-top: 15px">
								<legend>Approval Workflow Email</legend>
								
								<label class="desc" for="form_approval_email_subject">
								Approval Email Subject
								</label>
								
								<input id="form_approval_email_subject" class="text large" name="form_approval_email_subject" value="" type="text">
								
								<label class="desc" for="form_approval_email_content">
								Approval Email Content 
								</label><span class="icon-question helpicon" data-tippy-content="This field accepts HTML codes and merge tags."></span>
								
								<textarea class="textarea" rows="10" cols="50" id="form_approval_email_content" style="height: 5em"></textarea>
								</fieldset>
							</li>

							<li id="form_prop_breaker" class="clear advanced_prop form_prop"></li>
							
							<li id="prop_pagination_style" class="clear">
								<fieldset class="choices">
								<legend>
									Pagination Header Style 
									<span style="font-size: 100%;color: #fff" class="icon-question helpicon" data-tippy-content="When a form has multiple pages, the pagination header will be displayed on top of your form to let your clients know their progress. This is useful to help your clients understand how much of the form has been completed and how much left to be filled out."></span>
								</legend>
								<ul>
									<li>
										<input type="radio" id="pagination_style_steps" name="pagination_style" class="choices_default" title="Complete Steps">
										<label for="pagination_style_steps" class="choice">Complete Steps</label>
										<span class="icon-question helpicon" data-tippy-content="A complete series of all page titles will be displayed, along with the page numbers. The respective page title will be highlighted as the client continue to the next pages. Use this style if your form only has small number of pages."></span>
									</li>
									<li>
										<input type="radio" id="pagination_style_percentage" name="pagination_style" class="choices_default" title="Progress Bar">
										<label for="pagination_style_percentage" class="choice">Progress Bar</label>
										<span class="icon-question helpicon" data-tippy-content="A progress bar with a percentage number and the current active page title will be displayed. Use this style if your form has many pages or you need to put longer page title for each page."></span>
									</li>
									<li>
										<input type="radio" id="pagination_style_disabled" name="pagination_style" class="choices_default" title="Disable Pagination Header">
										<label for="pagination_style_disabled" class="choice">Disable</label>
										<span class="icon-question helpicon" data-tippy-content="Select this option if you prefer to disable the pagination header completely."></span>
									</li>
								</ul>	
							</fieldset>
							</li>
							
							<li id="prop_pagination_titles" class="clear">
								<fieldset class="choices">
								<legend>
									Page Titles
									<span style="font-size: 100%;color: #fff" class="icon-question helpicon" data-tippy-content="Each page on your form will have its own title which you can specify here. This is useful to organize the form into meaningful content groups. Ensure that the titles of your form pages match your clients' expectations and succintly explain what each page is for."></span>
								</legend>
								<ul id="pagination_title_list">
									<li>
										<label for="pagetitleinput_1">1.</label> 
										<input type="text" value="" autocomplete="off" class="text" id="pagetitle_1" /> 
									</li>	
								</ul>	
							</fieldset>
								
							</li>
							
							</ul>
						</form>
						<!--  end form properties pane -->
				</div>
			</div>
		</div>
	</div>			
</div><!-- /#sidebar -->

<div id="dialog-message" title="Error. Unable to complete the task." class="buttons" style="display: none">
	<span class="icon-bubble-notification"></span> 
	<p>
		There was a problem connecting with your website server.<br/>
		Please try again within few minutes.<br/><br/>
		If the problem persist, please contact us and we'll get back to you immediately!
	</p>
</div>
<div id="dialog-warning" title="Error Title" class="buttons" style="display: none">
	<span class="icon-bubble-notification"></span>
	<p id="dialog-warning-msg">
		Error
	</p>
</div>
<div id="dialog-confirm-field-delete" title="Are you sure you want to delete this field?" class="buttons" style="display: none">
	<span class="icon-bubble-notification"></span> 
	<p>
		This action cannot be undone.<br/>
		<strong>All data collected by the field will be deleted as well.</strong><br/><br/>
	</p>
	
</div>
<div id="dialog-confirm-encryption-disable" title="Are you sure you want to turn off data encryption?" class="buttons" style="display: none">
	<span class="icon-bubble-notification"></span> 
	<p>
		Please be careful. This action cannot be undone.<br/>
		<strong>All existing data will remain encrypted, and you won't be able to view them anymore.</strong><br/><br/>
	</p>
	
</div>
<div id="dialog-confirm-choice-delete" title="Are you sure you want to delete this choice?" class="buttons" style="display: none">
	<span class="icon-bubble-notification"></span> 
	<p>
		This action cannot be undone.<br/>
		<strong>All collected data associated with this choice will be deleted as well.</strong><br/><br/>
	</p>
	
</div>
<div id="dialog-form-saved" title="Success! Your form has been saved" class="buttons" style="display: none">
	<span class="icon-checkmark-circle"></span> 
	<p>
		<strong>Do you want to continue editing this form?</strong><br/><br/>
	</p>	
</div>
<div id="dialog-insert-choices" title="Bulk insert choices" class="buttons" style="display: none"> 
	<form class="dialog-form">				
		<ul>
			<li>
				<label for="bulk_insert_choices" class="description">You can insert a list of choices here. Separate the choices with new line. </label>
				<div>
				<textarea cols="90" rows="8" class="element textarea medium" name="bulk_insert_choices" id="bulk_insert_choices"></textarea> 
				</div> 
			</li>
		</ul>
	</form>
</div>
<div id="dialog-insert-matrix-rows" title="Bulk insert rows" class="buttons" style="display: none"> 
	<form class="dialog-form">				
		<ul>
			<li>
				<label for="bulk_insert_rows" class="description">You can insert a list of rows here. Separate the rows with new line. </label>
				<div>
				<textarea cols="90" rows="8" class="element textarea medium" name="bulk_insert_rows" id="bulk_insert_rows"></textarea> 
				</div> 
			</li>
		</ul>
	</form>
</div>
<div id="dialog-insert-matrix-columns" title="Bulk insert columns" class="buttons" style="display: none"> 
	<form class="dialog-form">				
		<ul>
			<li>
				<label for="bulk_insert_columns" class="description">You can insert a list of columns here. Separate the labels with new line. </label>
				<div>
				<textarea cols="90" rows="8" class="element textarea medium" name="bulk_insert_columns" id="bulk_insert_columns"></textarea> 
				</div> 
			</li>
		</ul>
	</form>
</div> 
<div id="dialog-enable-data-encryption" title="Create Encryption Keys" class="buttons" style="display: none">
	<span class="icon-key"></span> 
	<p>
		Your data will be encrypted using secure Asymmetrical Cryptography system that uses <strong>Public Key</strong> and <strong>Private Key</strong> to encrypt and decrypt data.
		<br/><br/>
	</p>
	
</div> 
<div id="dialog-enable-data-encryption-success" title="Success! Save your Private Key" class="buttons" style="display: none">
	<span class="icon-checkmark-circle"></span> 
	<p>
		Your Public Key has been saved. Your <strong>Private Key</strong> is displayed below.<br/>

		<div id="div_dialog_encryption_success" style="padding: 15px;margin-top:10px">
			<input onclick="javascript: this.select()" type="text" value="" class="text" name="dialog-enable-encryption-private-key" id="dialog-enable-encryption-private-key" />
		</div> 

		<strong style="text-decorration: underline;font-size: 110%">Please copy and save it in a secure place!</strong><br/>
		
		<span style="font-size: 90%;display: block;margin-top:30px;margin-bottom: 20px">You'll need this private key to read/decrypt your data in the future.<br/>
		It cannot be recovered if you lose it.</span>
	</p>
	
</div> 
<?php
	$footer_data =<<<EOT
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.core.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.widget.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.tabs.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.mouse.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.sortable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.draggable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.position.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.dialog.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/builder.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/datepick/jquery.datepick.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/uploadifive/jquery.uploadifive.js{$mf_version_tag}"></script>
<link rel="stylesheet" href="js/videojs/video-js.css{$mf_version_tag}">
<script type="text/javascript" src="js/videojs/video.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/videojs/Youtube.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/popper.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/tippy.index.all.min.js{$mf_version_tag}"></script>
<script type="text/javascript">
	$(function(){
		{$jquery_data_code}		
    });
</script>
<style>
.tippy-tooltip{
	font-size: 98%;
}
</style>
EOT;
	require('includes/footer.php'); 
?>