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
	require('includes/entry-functions.php');
	require('includes/users-functions.php');
	require('lib/google-api-client/vendor/autoload.php');
	
	$form_id = (int) trim($_REQUEST['id']);
	
	$dbh = mf_connect_db();

	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);
	$mf_properties 	= mf_get_form_properties($dbh,$form_id);
	
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

	//get form name
	$query 	= "select 
					 form_name
			     from 
			     	 ".MF_TABLE_PREFIX."forms 
			    where 
			    	 form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row)){
		$row['form_name'] = mf_trim_max_length($row['form_name'],50);	
		$form_name = htmlspecialchars($row['form_name']);
	}

	//get integration settings

	$integration_properties = new stdClass();
	$jquery_data_code = '';

	$query = "SELECT 
					  `gcal_integration_status`,
					  `gcal_calendar_id`,
					  IFNULL(`gcal_event_title`,'') gcal_event_title,
					  IFNULL(`gcal_event_desc`,'') gcal_event_desc,
					  IFNULL(`gcal_event_location`,'') gcal_event_location,
					  `gcal_event_allday`,
					  IFNULL(`gcal_start_datetime`,'') gcal_start_datetime,
					  `gcal_start_date_element`,
					  `gcal_start_time_element`,
					  `gcal_start_date_type`,
					  `gcal_start_time_type`,
					  IFNULL(`gcal_end_datetime`,'') gcal_end_datetime,
					  `gcal_end_date_element`,
					  `gcal_end_time_element`,
					  `gcal_end_date_type`,
					  `gcal_end_time_type`,
					  `gcal_duration_type`,
					  `gcal_duration_period_length`,
					  `gcal_duration_period_unit`,
					  IFNULL(`gcal_attendee_email`,'') gcal_attendee_email,
					  `gcal_refresh_token`,
					  `gcal_access_token`,
					  `gcal_delay_notification_until_paid`,
					  `gcal_delay_notification_until_approved`   
				FROM 
					".MF_TABLE_PREFIX."integrations WHERE form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$integration_properties->form_id 					= $form_id;
	$integration_properties->gcal_integration_status 	= (int) $row['gcal_integration_status'];
	$integration_properties->gcal_calendar_id 			= $row['gcal_calendar_id'];
	$integration_properties->gcal_event_title 			= $row['gcal_event_title'];
	$integration_properties->gcal_event_desc 			= $row['gcal_event_desc'];
	$integration_properties->gcal_event_location 		= $row['gcal_event_location'];
	$integration_properties->gcal_event_allday 			= (int) $row['gcal_event_allday'];
	$integration_properties->gcal_start_datetime 		= $row['gcal_start_datetime'];
	$integration_properties->gcal_start_date_element 	= (int) $row['gcal_start_date_element'];
	$integration_properties->gcal_start_time_element 	= (int) $row['gcal_start_time_element'];
	$integration_properties->gcal_start_date_type 		= $row['gcal_start_date_type'];
	$integration_properties->gcal_start_time_type 		= $row['gcal_start_time_type'];
	$integration_properties->gcal_end_datetime 			= $row['gcal_end_datetime'];
	$integration_properties->gcal_end_date_element 		= (int) $row['gcal_end_date_element'];
	$integration_properties->gcal_end_time_element 		= (int) $row['gcal_end_time_element'];
	$integration_properties->gcal_end_date_type 		= $row['gcal_end_date_type'];
	$integration_properties->gcal_end_time_type 		= $row['gcal_end_time_type'];
	$integration_properties->gcal_duration_type 		= $row['gcal_duration_type'];
	$integration_properties->gcal_duration_period_length = (int) $row['gcal_duration_period_length'];
	$integration_properties->gcal_duration_period_unit 	= $row['gcal_duration_period_unit'];
	$integration_properties->gcal_attendee_email 		= (int) $row['gcal_attendee_email'];
	$integration_properties->gcal_delay_notification_until_paid 	= (int) $row['gcal_delay_notification_until_paid'];
	$integration_properties->gcal_delay_notification_until_approved = (int) $row['gcal_delay_notification_until_approved'];

	//tokens shouldn't be stored into properties object
	$gcal_refresh_token	= $row['gcal_refresh_token'];
	$gcal_access_token  = $row['gcal_access_token'];

	$gcal_start_date_dd = '';
	$gcal_start_date_mm = '';
	$gcal_start_date_yyyy = '';
	$gcal_end_date_dd = '';
	$gcal_end_date_mm = '';
	$gcal_end_date_yyyy = '';

	$integration_properties->gcal_start_time = '';
	if(!empty($integration_properties->gcal_start_datetime)){
		list($integration_properties->gcal_start_date,$integration_properties->gcal_start_time) = explode(' ', $integration_properties->gcal_start_datetime);
		list($gcal_start_date_yyyy,$gcal_start_date_mm,$gcal_start_date_dd) = explode('-', $integration_properties->gcal_start_date);
	}

	$integration_properties->gcal_end_time = '';
	if(!empty($integration_properties->gcal_end_datetime)){
		list($integration_properties->gcal_end_date,$integration_properties->gcal_end_time) = explode(' ', $integration_properties->gcal_end_datetime);
		list($gcal_end_date_yyyy,$gcal_end_date_mm,$gcal_end_date_dd) = explode('-', $integration_properties->gcal_end_date);
	}

	//get all available date/choice/dropdown fields within the form
	//being used for start/end date
	$query = "select 
					element_id,
					element_title 
				from 
					`".MF_TABLE_PREFIX."form_elements` 
			   where 
			   		form_id=? and element_is_private=0 and element_status=1
			   		and element_type in('date','europe_date','radio','select')
			order by 
					element_position asc";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);

	$i=1;
	$date_fields = array();
	while($row = mf_do_fetch_result($sth)){
		$date_fields[$i]['label'] = $row['element_title'];
		$date_fields[$i]['value'] = $row['element_id'];
		$i++;
	}	

	//get all available time fields within the form
	$query = "select 
					element_id,
					element_title 
				from 
					`".MF_TABLE_PREFIX."form_elements` 
			   where 
			   		form_id=? and element_is_private=0 and element_status=1
			   		and element_type = 'time' 
			order by 
					element_title asc";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);

	$i=1;
	$time_fields = array();
	while($row = mf_do_fetch_result($sth)){
		$time_fields[$i]['label'] = $row['element_title'];
		$time_fields[$i]['value'] = $row['element_id'];
		$i++;
	}

	//get all available email fields within the form
	$query = "select 
					element_id,
					element_title 
				from 
					`".MF_TABLE_PREFIX."form_elements` 
			   where 
			   		form_id=? and element_is_private=0 and element_status=1
			   		and element_type = 'email' 
			order by 
					element_title asc";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);

	$i=1;
	$email_fields = array();
	while($row = mf_do_fetch_result($sth)){
		$email_fields[$i]['label'] = $row['element_title'];
		$email_fields[$i]['value'] = $row['element_id'];
		$i++;
	}	

	//get all available name and text fields within the form
	$query = "select 
					element_id,
					element_title 
				from 
					`".MF_TABLE_PREFIX."form_elements` 
			   where 
			   		form_id=? and element_is_private=0 and element_status=1
			   		and element_type in('text','name','simple_name','name_wmiddle','simple_name_wmiddle')  
			order by 
					element_title asc";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);

	$i=1;
	$name_text_fields = array();
	while($row = mf_do_fetch_result($sth)){
		$name_text_fields[$i]['label'] = $row['element_title'];
		$name_text_fields[$i]['value'] = $row['element_id'];
		$i++;
	}

	//get all available columns label
	$query  = "select 
					 element_id,
					 element_title,
					 element_type,
					 ifnull(element_address_subfields_labels,'') element_address_subfields_labels   
			     from
			     	 `".MF_TABLE_PREFIX."form_elements` 
			    where 
			    	 form_id=? and 
			    	 element_type != 'section' and 
			    	 element_type != 'media' and 
			    	 element_type != 'page_break' and 
			    	 element_status=1
			 order by 
			 		 element_position asc";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	
	
	$simple_field_columns_label = array();
	$complex_field_columns_label = array();
	while($row = mf_do_fetch_result($sth)){
		$element_title = $row['element_title'];
		$element_id    = $row['element_id'];
		$element_type  = $row['element_type']; 

		//limit the title length to 40 characters max
		if(strlen($element_title) > 40){
			$element_title = substr($element_title,0,40).'...';
		}

		$element_title = htmlspecialchars($element_title,ENT_QUOTES);
		$simple_field_columns_label['element_'.$element_id] = $element_title;

		//for some field type, we need to provide more detailed template variables
		//the special field types are Name and Address
		if('simple_name' == $element_type){
			$complex_field_columns_label['element_'.$element_id.'_1'] = $element_title." (First)";
			$complex_field_columns_label['element_'.$element_id.'_2'] = $element_title." (Last)";
		}else if('simple_name_wmiddle' == $element_type){
			$complex_field_columns_label['element_'.$element_id.'_1'] = $element_title." (First)";
			$complex_field_columns_label['element_'.$element_id.'_2'] = $element_title." (Middle)";
			$complex_field_columns_label['element_'.$element_id.'_3'] = $element_title." (Last)";			
		}else if('name' == $element_type){
			$complex_field_columns_label['element_'.$element_id.'_1'] = $element_title." (Title)";
			$complex_field_columns_label['element_'.$element_id.'_2'] = $element_title." (First)";
			$complex_field_columns_label['element_'.$element_id.'_3'] = $element_title." (Last)";
			$complex_field_columns_label['element_'.$element_id.'_4'] = $element_title." (Suffix)";
		}else if('name_wmiddle' == $element_type){
			$complex_field_columns_label['element_'.$element_id.'_1'] = $element_title." (Title)";
			$complex_field_columns_label['element_'.$element_id.'_2'] = $element_title." (First)";
			$complex_field_columns_label['element_'.$element_id.'_3'] = $element_title." (Middle)";
			$complex_field_columns_label['element_'.$element_id.'_4'] = $element_title." (Last)";
			$complex_field_columns_label['element_'.$element_id.'_5'] = $element_title." (Suffix)";
		}else if('address' == $element_type){
			$complex_field_columns_label['element_'.$element_id.'_1'] = $element_title." (Street)";
			$complex_field_columns_label['element_'.$element_id.'_2'] = $element_title." (Address Line 2)";
			$complex_field_columns_label['element_'.$element_id.'_3'] = $element_title." (City)";
			$complex_field_columns_label['element_'.$element_id.'_4'] = $element_title." (State)";
			$complex_field_columns_label['element_'.$element_id.'_5'] = $element_title." (Postal/Zip Code)";
			$complex_field_columns_label['element_'.$element_id.'_6'] = $element_title." (Country)";
			
			//if there is custom label for address subfields, use it instead
			if(!empty($row['element_address_subfields_labels'])){
				$subfields_labels_obj = json_decode($row['element_address_subfields_labels']);
				
				if(!empty($subfields_labels_obj->street)){
					$complex_field_columns_label['element_'.$element_id.'_1'] = $element_title." ({$subfields_labels_obj->street})";
				}
				if(!empty($subfields_labels_obj->street2)){
					$complex_field_columns_label['element_'.$element_id.'_2'] = $element_title." ({$subfields_labels_obj->street2})";
				}
				if(!empty($subfields_labels_obj->city)){
					$complex_field_columns_label['element_'.$element_id.'_3'] = $element_title." ({$subfields_labels_obj->city})";
				}
				if(!empty($subfields_labels_obj->state)){
					$complex_field_columns_label['element_'.$element_id.'_4'] = $element_title." ({$subfields_labels_obj->state})";
				}
				if(!empty($subfields_labels_obj->postal)){
					$complex_field_columns_label['element_'.$element_id.'_5'] = $element_title." ({$subfields_labels_obj->postal})";
				}
				if(!empty($subfields_labels_obj->country)){
					$complex_field_columns_label['element_'.$element_id.'_6'] = $element_title." ({$subfields_labels_obj->country})";
				}
			}	
		}else if('date' == $element_type || 'europe_date' == $element_type){
			$complex_field_columns_label['element_'.$element_id.'_dd'] = $element_title." (DD)";
			$complex_field_columns_label['element_'.$element_id.'_mm'] = $element_title." (MM)";
			$complex_field_columns_label['element_'.$element_id.'_yyyy'] = $element_title." (YYYY)";
		}
	}

	//get calendar list
	$calendar_list_array 	 = array();
	$connected_calendar_name = 'Primary Calendar';

	$calendar_list_array[0]['label'] = 'Primary Calendar';
	$calendar_list_array[0]['value'] = 'primary';

	//if there is calendar list within the session, use it
	if(!empty($_SESSION['cached_calendar_list'])){
		$calendar_list_array = $_SESSION['cached_calendar_list'];
	}

	if(!empty($gcal_refresh_token) && !empty($mf_settings['googleapi_clientid']) && !empty($mf_settings['googleapi_clientsecret']) && empty($_SESSION['cached_calendar_list'])){
		$refresh_token 	   = $gcal_refresh_token;
		$access_token 	   = $gcal_access_token;

		$client_id 	   = trim($mf_settings['googleapi_clientid']);
		$client_secret = trim($mf_settings['googleapi_clientsecret']);

		$response_token = array();
		$response_token['access_token']  = $access_token;
		$response_token['token_type'] 	 = 'Bearer';
		$response_token['expires_in'] 	 = 3600;
		$response_token['refresh_token'] = $refresh_token;
		$response_token['scope'] 		 = 'https://www.googleapis.com/auth/calendar';
		$response_token['created'] 		 = 0;

		try{
			$google_client = new Google_Client();

			$google_client->setAccessToken($response_token);
			$google_client->setAccessType("offline");
			$google_client->setIncludeGrantedScopes(true);  
			$google_client->addScope(Google_Service_Calendar::CALENDAR);
			$google_client->setClientId($client_id);
			$google_client->setClientSecret($client_secret);


			if($google_client->isAccessTokenExpired()) {
			    $new_access_token = $google_client->fetchAccessTokenWithRefreshToken($google_client->getRefreshToken());
			    $access_token = $new_access_token['access_token'];
			}

			$google_service = new Google_Service_Calendar($google_client);


			$calendar_list = $google_service->calendarList->listCalendarList();

			$i=1;
			while(true) {
				foreach ($calendar_list->getItems() as $calendarListEntry) {
					$is_primary = '';
					$is_primary = $calendarListEntry->getPrimary();
					
					//skip primary calendar
					if(empty($is_primary)){
						$calendar_list_array[$i]['label'] = $calendarListEntry->getSummary();
						$calendar_list_array[$i]['value'] = $calendarListEntry->getId();

						if($integration_properties->gcal_calendar_id == $calendar_list_array[$i]['value']){
							$connected_calendar_name = $calendar_list_array[$i]['label'];
						}
					}
					$i++;
				}
			  
				$pageToken = $calendar_list->getNextPageToken();
			  
				if ($pageToken) {
					$optParams = array('pageToken' => $pageToken);
			    	$calendar_list = $google_service->calendarList->listCalendarList($optParams);
			  	}else{
			    	break;
				}
			}

			//store the calendar list in session
			$_SESSION['cached_calendar_list'] = $calendar_list_array;
		}catch(Exception $e){
			//do nothing on errors, just silent
		}
	}		

	$json_integration_properties = json_encode($integration_properties);
	$jquery_data_code .= "\$('#gcal_settings').data('integration_properties',{$json_integration_properties});\n";

	$header_data =<<<EOT
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="js/datepick5/smoothness.datepick.css{$mf_version_tag}" rel="stylesheet" />
EOT;
	
	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post integration_gcal" data-csrftoken="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>" data-formid="<?php echo $form_id; ?>">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <a class="breadcrumb" href="integration_settings.php?id=<?php echo $form_id; ?>">Integrations</a> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Google Calendar</h2>
							<p>Google Calendar integration settings</p>
						</div>	
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>

				<div class="content_body" style="text-align: center">

					<?php if(!empty($gcal_refresh_token)){ ?>
					<div id="gcal_settings" class="gradient_blue">
						<h6><span class="icon-calendar"></span> Google Calendar Settings</h6> 
						<p style="font-size: 95%;position: absolute;right: 15px;top: 17px;">Connected Calendar &#8674; <strong id="connected_calendar_name"><?php echo htmlspecialchars($connected_calendar_name,ENT_QUOTES); ?></strong></p>
						<label class="description" for="gcal_event_title">Event Title</label>
						<textarea style="width: 400px; height: 45px" class="element textarea medium" name="gcal_event_title" id="gcal_event_title"><?php echo htmlspecialchars($integration_properties->gcal_event_title,ENT_QUOTES); ?></textarea>
						<p style="font-size: 90%;margin-top: 5px">You can insert <a href="#" class="tempvar_link blue_dotted">merge tags</a> to customize Event Title with your form data.</p>
						
						<label class="description inline" for="start_date_dropdown">Event Start Date </label>
						<span class="icon-question helpicon clearfix" data-tippy-content="If your form has <strong>Date</strong>, <strong>Multiple Choice</strong>, or <strong> Drop Down</strong> field type, it will be available here and you can choose it to allow users of your form to select the date. Or you can set your own fixed date."></span>

						<select style="width: 200px" name="start_date_dropdown" id="start_date_dropdown" class="element select medium"> 
							<?php
								foreach ($date_fields as $data){
									$data['label'] = htmlspecialchars($data['label'],ENT_QUOTES);
									
									if($integration_properties->gcal_start_date_element == $data['value'] && $integration_properties->gcal_start_date_type == 'element'){
										$selected = 'selected="selected"';
									}else{
										$selected = '';
									}

									echo "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>";
								}
							?>	
							<option value="specific_date" <?php if($integration_properties->gcal_start_date_type == 'datetime'){ echo 'selected="selected"'; } ?> >&#8674; Set Specific Date</option>		
						</select>
						<span id="start_date_specific_date_span" style="<?php if($integration_properties->gcal_start_date_type == 'datetime'){ echo 'display: inline'; }else{ echo 'display: none'; } ?>">
							<span>&#8674;</span>
							
							<span>

								
								<input type="text" placeholder="MM" value="<?php echo $gcal_start_date_mm; ?>" maxlength="2" size="2" style="width: 2em;" class="text" name="gcal_start_date_mm" id="gcal_start_date_mm">
								
								<input type="text" placeholder="DD" value="<?php echo $gcal_start_date_dd; ?>" maxlength="2" size="2" style="width: 2em;" class="text" name="gcal_start_date_dd" id="gcal_start_date_dd">
								
								<input type="text" placeholder="YYYY" value="<?php echo $gcal_start_date_yyyy; ?>" maxlength="4" size="4" style="width: 3em;" class="text" name="gcal_start_date_yyyy" id="gcal_start_date_yyyy">
								
								<span id="gcal_start_date_cal">
										<input type="hidden" value="" maxlength="4" size="4" style="width: 3em;" class="text" name="linked_picker_gcal_start_date" id="linked_picker_gcal_start_date">
										<span style="display: none">
											<img id="gcal_start_date_pick_img" alt="Pick date." src="images/calendar.png" width="16" class="datepicker" style="margin-top: 3px; cursor: pointer" />
										</span>
								</span>
							</span>
						</span>
						<?php
							//check if we need to display start time or not
							if($integration_properties->gcal_start_time_type == 'element' ||
							   ($integration_properties->gcal_start_time_type == 'datetime' && $integration_properties->gcal_start_time != '00:00:00' && !empty($integration_properties->gcal_start_time))	
							){
								$gcal_start_date_time_display = 'display: inline';
								$show_add_start_date_time = false;
							}else{
								$gcal_start_date_time_display = 'display: none';
								$show_add_start_date_time = true;
							}

							//if the event is all day, override the above
							if(!empty($integration_properties->gcal_event_allday)){
								$gcal_start_date_time_display = 'display: none';
								$show_add_start_date_time = false;
							}

						?>
						<span id="start_date_time_span" style="<?php echo $gcal_start_date_time_display; ?>">
							<span style="font-weight: bold;margin: 0 20px;">at</span>
							<select style="width: 200px" name="start_time_dropdown" id="start_time_dropdown" class="element select medium"> 
								<?php
									foreach ($time_fields as $data){
										$data['label'] = htmlspecialchars($data['label'],ENT_QUOTES);
										
										if($integration_properties->gcal_start_time_element == $data['value'] && $integration_properties->gcal_start_time_type == 'element'){
											$selected = 'selected="selected"';
										}else{
											$selected = '';
										}

										echo "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>";
									}
								?>	
								<option value="specific_time" <?php if($integration_properties->gcal_start_time_type == 'datetime'){ echo 'selected="selected"'; } ?> >&#8674; Set Specific Time</option>		
							</select>

							<span id="start_date_specific_time_span" style="<?php if($integration_properties->gcal_start_time_type == 'datetime'){ echo 'display: inline'; }else{ echo 'display: none'; } ?>">
								<span>&#8674;</span>
								<select style="width: 100px;" name="start_time_specific_dropdown" id="start_time_specific_dropdown" class="element select medium"> 
									<option <?php if($integration_properties->gcal_start_time == '00:00:00'){echo 'selected="selected"';} ?> value="00:00:00" >12:00am</option>
									<option <?php if($integration_properties->gcal_start_time == '00:30:00'){echo 'selected="selected"';} ?> value="00:30:00" >12:30am</option>
									<option <?php if($integration_properties->gcal_start_time == '01:00:00'){echo 'selected="selected"';} ?> value="01:00:00" >1:00am</option>
									<option <?php if($integration_properties->gcal_start_time == '01:30:00'){echo 'selected="selected"';} ?> value="01:30:00" >1:30am</option>
									<option <?php if($integration_properties->gcal_start_time == '02:00:00'){echo 'selected="selected"';} ?> value="02:00:00" >2:00am</option>
									<option <?php if($integration_properties->gcal_start_time == '02:30:00'){echo 'selected="selected"';} ?> value="02:30:00" >2:30am</option>
									<option <?php if($integration_properties->gcal_start_time == '03:00:00'){echo 'selected="selected"';} ?> value="03:00:00" >3:00am</option>
									<option <?php if($integration_properties->gcal_start_time == '03:30:00'){echo 'selected="selected"';} ?> value="03:30:00" >3:30am</option>
									<option <?php if($integration_properties->gcal_start_time == '04:00:00'){echo 'selected="selected"';} ?> value="04:00:00" >4:00am</option>
									<option <?php if($integration_properties->gcal_start_time == '04:30:00'){echo 'selected="selected"';} ?> value="04:30:00" >4:30am</option>
									<option <?php if($integration_properties->gcal_start_time == '05:00:00'){echo 'selected="selected"';} ?> value="05:00:00" >5:00am</option>
									<option <?php if($integration_properties->gcal_start_time == '05:30:00'){echo 'selected="selected"';} ?> value="05:30:00" >5:30am</option>
									<option <?php if($integration_properties->gcal_start_time == '06:00:00'){echo 'selected="selected"';} ?> value="06:00:00" >6:00am</option>
									<option <?php if($integration_properties->gcal_start_time == '06:30:00'){echo 'selected="selected"';} ?> value="06:30:00" >6:30am</option>
									<option <?php if($integration_properties->gcal_start_time == '07:00:00'){echo 'selected="selected"';} ?> value="07:00:00" >7:00am</option>
									<option <?php if($integration_properties->gcal_start_time == '07:30:00'){echo 'selected="selected"';} ?> value="07:30:00" >7:30am</option>
									<option <?php if($integration_properties->gcal_start_time == '08:00:00'){echo 'selected="selected"';} ?> value="08:00:00" >8:00am</option>
									<option <?php if($integration_properties->gcal_start_time == '08:30:00'){echo 'selected="selected"';} ?> value="08:30:00" >8:30am</option>
									<option <?php if($integration_properties->gcal_start_time == '09:00:00'){echo 'selected="selected"';} ?> value="09:00:00" >9:00am</option>
									<option <?php if($integration_properties->gcal_start_time == '09:30:00'){echo 'selected="selected"';} ?> value="09:30:00" >9:30am</option>
									<option <?php if($integration_properties->gcal_start_time == '10:00:00'){echo 'selected="selected"';} ?> value="10:00:00" >10:00am</option>
									<option <?php if($integration_properties->gcal_start_time == '10:30:00'){echo 'selected="selected"';} ?> value="10:30:00" >10:30am</option>
									<option <?php if($integration_properties->gcal_start_time == '11:00:00'){echo 'selected="selected"';} ?> value="11:00:00" >11:00am</option>
									<option <?php if($integration_properties->gcal_start_time == '11:30:00'){echo 'selected="selected"';} ?> value="11:30:00" >11:30am</option>
									<option <?php if($integration_properties->gcal_start_time == '12:00:00'){echo 'selected="selected"';} ?> value="12:00:00" >12:00pm</option>
									<option <?php if($integration_properties->gcal_start_time == '12:30:00'){echo 'selected="selected"';} ?> value="12:30:00" >12:30pm</option>
									<option <?php if($integration_properties->gcal_start_time == '13:00:00'){echo 'selected="selected"';} ?> value="13:00:00" >1:00pm</option>
									<option <?php if($integration_properties->gcal_start_time == '13:30:00'){echo 'selected="selected"';} ?> value="13:30:00" >1:30pm</option>
									<option <?php if($integration_properties->gcal_start_time == '14:00:00'){echo 'selected="selected"';} ?> value="14:00:00" >2:00pm</option>
									<option <?php if($integration_properties->gcal_start_time == '14:30:00'){echo 'selected="selected"';} ?> value="14:30:00" >2:30pm</option>
									<option <?php if($integration_properties->gcal_start_time == '15:00:00'){echo 'selected="selected"';} ?> value="15:00:00" >3:00pm</option>
									<option <?php if($integration_properties->gcal_start_time == '15:30:00'){echo 'selected="selected"';} ?> value="15:30:00" >3:30pm</option>
									<option <?php if($integration_properties->gcal_start_time == '16:00:00'){echo 'selected="selected"';} ?> value="16:00:00" >4:00pm</option>
									<option <?php if($integration_properties->gcal_start_time == '16:30:00'){echo 'selected="selected"';} ?> value="16:30:00" >4:30pm</option>
									<option <?php if($integration_properties->gcal_start_time == '17:00:00'){echo 'selected="selected"';} ?> value="17:00:00" >5:00pm</option>
									<option <?php if($integration_properties->gcal_start_time == '17:30:00'){echo 'selected="selected"';} ?> value="17:30:00" >5:30pm</option>
									<option <?php if($integration_properties->gcal_start_time == '18:00:00'){echo 'selected="selected"';} ?> value="18:00:00" >6:00pm</option>
									<option <?php if($integration_properties->gcal_start_time == '18:30:00'){echo 'selected="selected"';} ?> value="18:30:00" >6:30pm</option>
									<option <?php if($integration_properties->gcal_start_time == '19:00:00'){echo 'selected="selected"';} ?> value="19:00:00" >7:00pm</option>
									<option <?php if($integration_properties->gcal_start_time == '19:30:00'){echo 'selected="selected"';} ?> value="19:30:00" >7:30pm</option>
									<option <?php if($integration_properties->gcal_start_time == '20:00:00'){echo 'selected="selected"';} ?> value="20:00:00" >8:00pm</option>
									<option <?php if($integration_properties->gcal_start_time == '20:30:00'){echo 'selected="selected"';} ?> value="20:30:00" >8:30pm</option>
									<option <?php if($integration_properties->gcal_start_time == '21:00:00'){echo 'selected="selected"';} ?> value="21:00:00" >9:00pm</option>
									<option <?php if($integration_properties->gcal_start_time == '21:30:00'){echo 'selected="selected"';} ?> value="21:30:00" >9:30pm</option>
									<option <?php if($integration_properties->gcal_start_time == '22:00:00'){echo 'selected="selected"';} ?> value="22:00:00" >10:00pm</option>
									<option <?php if($integration_properties->gcal_start_time == '22:30:00'){echo 'selected="selected"';} ?> value="22:30:00" >10:30pm</option>
									<option <?php if($integration_properties->gcal_start_time == '23:00:00'){echo 'selected="selected"';} ?> value="23:00:00" >11:00pm</option>
									<option <?php if($integration_properties->gcal_start_time == '23:30:00'){echo 'selected="selected"';} ?> value="23:30:00" >11:30pm</option>
								</select>
							</span>
						</span>

						<?php 
							if($show_add_start_date_time){
								$add_time_display_attr = 'display: inline;';
							}else{
								$add_time_display_attr = 'display: none;';
							}
						?>
						<a href="#" class="blue_dotted" id="add_start_date_time_link" style="font-weight: bold;margin-left: 20px;<?php echo $add_time_display_attr; ?>"><span class="icon-plus-circle"></span> Add Time</a> 
						
						<div>
							<input id="gcal_event_allday" <?php if(!empty($integration_properties->gcal_event_allday)){ echo 'checked="checked"'; } ?> class="checkbox" value="" type="checkbox" style="margin-left: 0px;margin-top: 15px">
							<label class="choice" for="gcal_event_allday">All day event</label>
						</div>

						<div id="event_duration_div" style="<?php if(empty($integration_properties->gcal_event_allday)){ echo 'display: block'; }else{ echo 'display: none'; } ?>">

							<label class="description" for="gcal_event_duration_dropdown">Event Duration</label>

							<select style="width: 200px" name="gcal_event_duration_dropdown" id="gcal_event_duration_dropdown" class="element select medium"> 
								<option <?php if($integration_properties->gcal_duration_type == 'period' && $integration_properties->gcal_duration_period_unit == 'minute' && $integration_properties->gcal_duration_period_length == 15){ echo 'selected="selected"'; } ?> value="15" >15 minutes</option>
								<option <?php if($integration_properties->gcal_duration_type == 'period' && $integration_properties->gcal_duration_period_unit == 'minute' && $integration_properties->gcal_duration_period_length == 30){ echo 'selected="selected"'; } ?> value="30" >30 minutes</option>
								<option <?php if($integration_properties->gcal_duration_type == 'period' && $integration_properties->gcal_duration_period_unit == 'minute' && $integration_properties->gcal_duration_period_length == 45){ echo 'selected="selected"'; } ?> value="45" >45 minutes</option>
								<option <?php if($integration_properties->gcal_duration_type == 'period' && $integration_properties->gcal_duration_period_unit == 'minute' && $integration_properties->gcal_duration_period_length == 60){ echo 'selected="selected"'; } ?> value="60" >60 minutes</option>
								
								<option <?php if($integration_properties->gcal_duration_type == 'period' && ($integration_properties->gcal_duration_period_unit != 'minute' || ($integration_properties->gcal_duration_period_unit == 'minute' && !in_array($integration_properties->gcal_duration_period_length,array(15,30,45,60))))){ echo 'selected="selected"'; } ?> value="period" >&#8674; Set Custom Duration</option>
								<option <?php if($integration_properties->gcal_duration_type == 'datetime'){ echo 'selected="selected"';} ?> value="datetime" >&#8674; Set Date/Time</option>	
							</select>

							<span id="gcal_event_duration_custom_period_span" style="<?php if($integration_properties->gcal_duration_type == 'period' && ($integration_properties->gcal_duration_period_unit != 'minute' || ($integration_properties->gcal_duration_period_unit == 'minute' && !in_array($integration_properties->gcal_duration_period_length,array(15,30,45,60))))){ echo 'display: inline'; }else{ echo 'display: none'; } ?>">
								<span>&#8674;</span> 
								<input type="text" value="<?php echo $integration_properties->gcal_duration_period_length; ?>" size="3" style="width: 3em;" class="text" name="gcal_duration_period_length" id="gcal_duration_period_length">
								<select style="width: 80px;margin-left: 5px" name="gcal_duration_period_unit" id="gcal_duration_period_unit" class="element select medium"> 
									<option <?php if($integration_properties->gcal_duration_period_unit == 'minute'){echo 'selected="selected"';} ?> value="minute" >minutes</option>
									<option <?php if($integration_properties->gcal_duration_period_unit == 'hour'){echo 'selected="selected"';} ?> value="hour" >hours</option>
									<option <?php if($integration_properties->gcal_duration_period_unit == 'day'){echo 'selected="selected"';} ?> value="day" >days</option>
								</select>
							</span>

							<div id="gcal_event_end_date_div" style="<?php if($integration_properties->gcal_duration_type == 'datetime'){ echo 'display: block'; }else{echo 'display: none'; } ?>">
								<label class="description inline" for="end_date_dropdown">Event End Date </label>
								<span class="icon-question helpicon clearfix" data-tippy-content="If your form has <strong>Date</strong>, <strong>Multiple Choice</strong>, or <strong> Drop Down</strong> field type, it will be available here and you can choose it to allow users of your form to select the date. Or you can set your own fixed date."></span>

								<select style="width: 200px" name="end_date_dropdown" id="end_date_dropdown" class="element select medium"> 
									<?php
										foreach ($date_fields as $data){
											$data['label'] = htmlspecialchars($data['label'],ENT_QUOTES);
											
											if($integration_properties->gcal_end_date_element == $data['value'] && $integration_properties->gcal_end_date_type == 'element'){
												$selected = 'selected="selected"';
											}else{
												$selected = '';
											}

											echo "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>";
										}
									?>	
									<option value="specific_date" <?php if($integration_properties->gcal_end_date_type == 'datetime'){ echo 'selected="selected"'; } ?> >&#8674; Set Specific Date</option>		
								</select>
								<span id="end_date_specific_date_span" style="<?php if($integration_properties->gcal_end_date_type == 'datetime'){ echo 'display: inline'; }else{ echo 'display: none'; } ?>">
									<span>&#8674;</span>
									
									<span>

										
										<input type="text" placeholder="MM" value="<?php echo $gcal_end_date_mm; ?>" maxlength="2" size="2" style="width: 2em;" class="text" name="gcal_end_date_mm" id="gcal_end_date_mm">
										
										<input type="text" placeholder="DD" value="<?php echo $gcal_end_date_dd; ?>" maxlength="2" size="2" style="width: 2em;" class="text" name="gcal_end_date_dd" id="gcal_end_date_dd">
										
										<input type="text" placeholder="YYYY" value="<?php echo $gcal_end_date_yyyy; ?>" maxlength="4" size="4" style="width: 3em;" class="text" name="gcal_end_date_yyyy" id="gcal_end_date_yyyy">
										
										<span id="gcal_end_date_cal">
												<input type="hidden" value="" maxlength="4" size="4" style="width: 3em;" class="text" name="linked_picker_gcal_end_date" id="linked_picker_gcal_end_date">
												<span style="display: none">
													<img id="gcal_end_date_pick_img" alt="Pick date." src="images/calendar.png" width="16" class="datepicker" style="margin-top: 3px; cursor: pointer" />
												</span>
										</span>
									</span>
								</span>
								<?php
									//check if we need to display end time or not
									if($integration_properties->gcal_end_time_type == 'element' ||
									   ($integration_properties->gcal_end_time_type == 'datetime' && $integration_properties->gcal_end_time != '00:00:00' && !empty($integration_properties->gcal_end_time))	
									){
										$gcal_end_date_time_display = 'display: inline';
										$show_add_end_date_time = false;
									}else{
										$gcal_end_date_time_display = 'display: none';
										$show_add_end_date_time = true;
									}

								?>
								<span id="end_date_time_span" style="<?php echo $gcal_end_date_time_display; ?>">
									<span style="font-weight: bold;margin: 0 20px;">at</span>
									<select style="width: 200px" name="end_time_dropdown" id="end_time_dropdown" class="element select medium"> 
										<?php
											foreach ($time_fields as $data){
												$data['label'] = htmlspecialchars($data['label'],ENT_QUOTES);
												
												if($integration_properties->gcal_end_time_element == $data['value'] && $integration_properties->gcal_end_time_type == 'element'){
													$selected = 'selected="selected"';
												}else{
													$selected = '';
												}

												echo "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>";
											}
										?>	
										<option value="specific_time" <?php if($integration_properties->gcal_end_time_type == 'datetime'){ echo 'selected="selected"'; } ?> >&#8674; Set Specific Time</option>		
									</select>

									<span id="end_date_specific_time_span" style="<?php if($integration_properties->gcal_end_time_type == 'datetime'){ echo 'display: inline'; }else{ echo 'display: none'; } ?>">
										<span>&#8674;</span>
										<select style="width: 100px;" name="end_time_specific_dropdown" id="end_time_specific_dropdown" class="element select medium"> 
											<option <?php if($integration_properties->gcal_end_time == '00:00:00'){echo 'selected="selected"';} ?> value="00:00:00" >12:00am</option>
											<option <?php if($integration_properties->gcal_end_time == '00:30:00'){echo 'selected="selected"';} ?> value="00:30:00" >12:30am</option>
											<option <?php if($integration_properties->gcal_end_time == '01:00:00'){echo 'selected="selected"';} ?> value="01:00:00" >1:00am</option>
											<option <?php if($integration_properties->gcal_end_time == '01:30:00'){echo 'selected="selected"';} ?> value="01:30:00" >1:30am</option>
											<option <?php if($integration_properties->gcal_end_time == '02:00:00'){echo 'selected="selected"';} ?> value="02:00:00" >2:00am</option>
											<option <?php if($integration_properties->gcal_end_time == '02:30:00'){echo 'selected="selected"';} ?> value="02:30:00" >2:30am</option>
											<option <?php if($integration_properties->gcal_end_time == '03:00:00'){echo 'selected="selected"';} ?> value="03:00:00" >3:00am</option>
											<option <?php if($integration_properties->gcal_end_time == '03:30:00'){echo 'selected="selected"';} ?> value="03:30:00" >3:30am</option>
											<option <?php if($integration_properties->gcal_end_time == '04:00:00'){echo 'selected="selected"';} ?> value="04:00:00" >4:00am</option>
											<option <?php if($integration_properties->gcal_end_time == '04:30:00'){echo 'selected="selected"';} ?> value="04:30:00" >4:30am</option>
											<option <?php if($integration_properties->gcal_end_time == '05:00:00'){echo 'selected="selected"';} ?> value="05:00:00" >5:00am</option>
											<option <?php if($integration_properties->gcal_end_time == '05:30:00'){echo 'selected="selected"';} ?> value="05:30:00" >5:30am</option>
											<option <?php if($integration_properties->gcal_end_time == '06:00:00'){echo 'selected="selected"';} ?> value="06:00:00" >6:00am</option>
											<option <?php if($integration_properties->gcal_end_time == '06:30:00'){echo 'selected="selected"';} ?> value="06:30:00" >6:30am</option>
											<option <?php if($integration_properties->gcal_end_time == '07:00:00'){echo 'selected="selected"';} ?> value="07:00:00" >7:00am</option>
											<option <?php if($integration_properties->gcal_end_time == '07:30:00'){echo 'selected="selected"';} ?> value="07:30:00" >7:30am</option>
											<option <?php if($integration_properties->gcal_end_time == '08:00:00'){echo 'selected="selected"';} ?> value="08:00:00" >8:00am</option>
											<option <?php if($integration_properties->gcal_end_time == '08:30:00'){echo 'selected="selected"';} ?> value="08:30:00" >8:30am</option>
											<option <?php if($integration_properties->gcal_end_time == '09:00:00'){echo 'selected="selected"';} ?> value="09:00:00" >9:00am</option>
											<option <?php if($integration_properties->gcal_end_time == '09:30:00'){echo 'selected="selected"';} ?> value="09:30:00" >9:30am</option>
											<option <?php if($integration_properties->gcal_end_time == '10:00:00'){echo 'selected="selected"';} ?> value="10:00:00" >10:00am</option>
											<option <?php if($integration_properties->gcal_end_time == '10:30:00'){echo 'selected="selected"';} ?> value="10:30:00" >10:30am</option>
											<option <?php if($integration_properties->gcal_end_time == '11:00:00'){echo 'selected="selected"';} ?> value="11:00:00" >11:00am</option>
											<option <?php if($integration_properties->gcal_end_time == '11:30:00'){echo 'selected="selected"';} ?> value="11:30:00" >11:30am</option>
											<option <?php if($integration_properties->gcal_end_time == '12:00:00'){echo 'selected="selected"';} ?> value="12:00:00" >12:00pm</option>
											<option <?php if($integration_properties->gcal_end_time == '12:30:00'){echo 'selected="selected"';} ?> value="12:30:00" >12:30pm</option>
											<option <?php if($integration_properties->gcal_end_time == '13:00:00'){echo 'selected="selected"';} ?> value="13:00:00" >1:00pm</option>
											<option <?php if($integration_properties->gcal_end_time == '13:30:00'){echo 'selected="selected"';} ?> value="13:30:00" >1:30pm</option>
											<option <?php if($integration_properties->gcal_end_time == '14:00:00'){echo 'selected="selected"';} ?> value="14:00:00" >2:00pm</option>
											<option <?php if($integration_properties->gcal_end_time == '14:30:00'){echo 'selected="selected"';} ?> value="14:30:00" >2:30pm</option>
											<option <?php if($integration_properties->gcal_end_time == '15:00:00'){echo 'selected="selected"';} ?> value="15:00:00" >3:00pm</option>
											<option <?php if($integration_properties->gcal_end_time == '15:30:00'){echo 'selected="selected"';} ?> value="15:30:00" >3:30pm</option>
											<option <?php if($integration_properties->gcal_end_time == '16:00:00'){echo 'selected="selected"';} ?> value="16:00:00" >4:00pm</option>
											<option <?php if($integration_properties->gcal_end_time == '16:30:00'){echo 'selected="selected"';} ?> value="16:30:00" >4:30pm</option>
											<option <?php if($integration_properties->gcal_end_time == '17:00:00'){echo 'selected="selected"';} ?> value="17:00:00" >5:00pm</option>
											<option <?php if($integration_properties->gcal_end_time == '17:30:00'){echo 'selected="selected"';} ?> value="17:30:00" >5:30pm</option>
											<option <?php if($integration_properties->gcal_end_time == '18:00:00'){echo 'selected="selected"';} ?> value="18:00:00" >6:00pm</option>
											<option <?php if($integration_properties->gcal_end_time == '18:30:00'){echo 'selected="selected"';} ?> value="18:30:00" >6:30pm</option>
											<option <?php if($integration_properties->gcal_end_time == '19:00:00'){echo 'selected="selected"';} ?> value="19:00:00" >7:00pm</option>
											<option <?php if($integration_properties->gcal_end_time == '19:30:00'){echo 'selected="selected"';} ?> value="19:30:00" >7:30pm</option>
											<option <?php if($integration_properties->gcal_end_time == '20:00:00'){echo 'selected="selected"';} ?> value="20:00:00" >8:00pm</option>
											<option <?php if($integration_properties->gcal_end_time == '20:30:00'){echo 'selected="selected"';} ?> value="20:30:00" >8:30pm</option>
											<option <?php if($integration_properties->gcal_end_time == '21:00:00'){echo 'selected="selected"';} ?> value="21:00:00" >9:00pm</option>
											<option <?php if($integration_properties->gcal_end_time == '21:30:00'){echo 'selected="selected"';} ?> value="21:30:00" >9:30pm</option>
											<option <?php if($integration_properties->gcal_end_time == '22:00:00'){echo 'selected="selected"';} ?> value="22:00:00" >10:00pm</option>
											<option <?php if($integration_properties->gcal_end_time == '22:30:00'){echo 'selected="selected"';} ?> value="22:30:00" >10:30pm</option>
											<option <?php if($integration_properties->gcal_end_time == '23:00:00'){echo 'selected="selected"';} ?> value="23:00:00" >11:00pm</option>
											<option <?php if($integration_properties->gcal_end_time == '23:30:00'){echo 'selected="selected"';} ?> value="23:30:00" >11:30pm</option>
										</select>
									</span>
								</span>

								<?php 
									if($show_add_end_date_time){
								?>
									<a href="#" class="blue_dotted" id="add_end_date_time_link" style="font-weight: bold;margin-left: 20px"><span class="icon-plus-circle"></span> Add Time</a> 
								<?php } ?>
							</div>
						</div>

						<div id="integration_more_options" style="display: none">
							<label class="description" for="gcal_calendar_name">Calendar Name</label>
										
							<select style="width: 200px" name="gcal_calendar_name" id="gcal_calendar_name" class="element select medium"> 
								<?php
									foreach ($calendar_list_array as $data){
										$data['label'] = htmlspecialchars($data['label'],ENT_QUOTES);
										
										if($integration_properties->gcal_calendar_id == $data['value']){
											$selected = 'selected="selected"';
										}else{
											$selected = '';
										}

										echo "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>";
									}
								?>	
							</select>

							<div class="clearfix"></div>

							<label class="description inline" for="gcal_attendee_email_dropdown">Attendee Email</label>
							<span class="icon-question helpicon clearfix" data-tippy-content="This is optional. If your form has 'Email' field type, it will be available here and you can choose it as the Attendee Email."></span>

							<select style="width: 200px" name="gcal_attendee_email_dropdown" id="gcal_attendee_email_dropdown" class="element select medium"> 
								<option value=""></option>
								<?php
									foreach ($email_fields as $data){
										$data['label'] = htmlspecialchars($data['label'],ENT_QUOTES);
										
										if($integration_properties->gcal_attendee_email == $data['value']){
											$selected = 'selected="selected"';
										}else{
											$selected = '';
										}

										echo "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>";
									}
								?>		
							</select>

							<label class="description" for="gcal_event_desc">Event Description</label>
							<textarea style="width: 400px; height: 45px" class="element textarea medium" name="gcal_event_desc" id="gcal_event_desc"><?php echo htmlspecialchars($integration_properties->gcal_event_desc,ENT_QUOTES); ?></textarea>

							<label class="description" for="gcal_event_location">Event Location</label>
							<textarea style="width: 400px; height: 45px" class="element textarea medium" name="gcal_event_location" id="gcal_event_location"><?php echo htmlspecialchars($integration_properties->gcal_event_location,ENT_QUOTES); ?></textarea>
						
							<?php if($mf_properties['payment_enable_merchant'] == 1 && $mf_properties['payment_merchant_type'] != 'check'){ ?>
							<div style="margin: 20px 0 10px 0">
								<input type="checkbox" value="1" class="checkbox" id="gcal_delay_notification_until_paid" name="gcal_delay_notification_until_paid" <?php if(!empty($integration_properties->gcal_delay_notification_until_paid)){ echo 'checked="checked"';} ?>>
								<label for="gcal_delay_notification_until_paid" class="choice">Delay adding events to Google Calendar until payment completed</label>
								<span class="icon-question helpicon" data-tippy-content="If enabled, form will only add events to Google Calendar once payment has been successfully completed. Entries with incomplete payment won't add any events to Google Calendar."></span>
							</div>
							<?php } ?>

							<?php if($mf_properties['form_approval_enable'] == 1){ ?>
							<div style="margin: 10px 0 10px 0">
								<input type="checkbox" value="1" class="checkbox" id="gcal_delay_notification_until_approved" name="gcal_delay_notification_until_approved" <?php if(!empty($integration_properties->gcal_delay_notification_until_approved)){ echo 'checked="checked"';} ?>>
								<label for="gcal_delay_notification_until_approved" class="choice">Delay adding events to Google Calendar until approval status is <strong>APPROVED</strong></label>
								<span class="icon-question helpicon" data-tippy-content="If enabled, form will only add events to Google Calendar once Approval Status is marked as APPROVED. Entries with DENIED status won't add any events to Google Calendar."></span>
							</div>
							<?php } ?>
						</div>	
						
						<div style="margin: 20px 0;text-align: left"><span class="icon-settings" style="margin-right: 5px;display: inline-block;line-height: 22px;vertical-align: top;"></span><a href="#" id="gcal_show_option_switcher" class="blue_dotted" style="font-weight: bold">more options</a></div>

						<div style="margin-top: 30px;width: 100%"></div>

						<a href="#" id="button_save_integration" class="bb_button bb_small bb_green" style="float: left">
							<span class="icon-disk" style="margin-right: 5px"></span>Save Settings
						</a>
						<a href="#" id="button_remove_integration" class="bb_button bb_small bb_grey" style="float: right">
							<span class="icon-remove" style="margin-right: 5px"></span>Remove Integration
						</a>

					</div>
					<?php } ?>

					<div id="dialog-template-variable" title="Merge Tags Lookup" class="buttons" style="display: none"> 
						<form id="dialog-template-variable-form" class="dialog-form" style="padding-left: 10px;padding-bottom: 10px">				
							<ul>
								<li>
									<div>
										
										<div style="margin: 0px 0 10px 0">
											Merge Tag &#8674; <span id="tempvar_value">{form_name}</span>
										</div>

										<select class="select full" id="dialog-template-variable-input" style="margin-bottom: 10px" name="dialog-template-variable-input">
											<optgroup label="Form Fields">
											<?php 
												foreach ($simple_field_columns_label as $element_name => $element_label) {
													echo "<option value=\"{$element_name}\">{$element_label}</option>\n";
												}
											?>
											</optgroup>
											<?php
												if(!empty($complex_field_columns_label)){
													echo "<optgroup label=\"Complex Form Fields (Detailed)\">";
													foreach ($complex_field_columns_label as $element_name => $element_label) {
														echo "<option value=\"{$element_name}\">{$element_label}</option>\n";
													}
													echo "</optgroup>";
												}
											?>
											<optgroup label="Entry Information">
												<option value="entry_no">Entry No.</option>
												<option value="date_created">Date Created</option>
												<option value="ip_address">IP Address</option>
												<option value="form_id">Form ID</option>
												<option value="form_name" selected="selected">Form Name</option>
												<option value="entry_data">Complete Entry</option>

												<?php if(!empty($mf_properties['form_entry_edit_enable'])){ ?>
												<option value="edit_link">Edit Link</option>
												<option value="edit_url">Edit Link (URL only)</option>
												<?php } ?>
											</optgroup>	
											
											<?php if(!empty($payment_enable_merchant)){ ?>
												<optgroup label="Payment Information">
													<option value="total_amount">Total Amount</option>
													<option value="payment_status">Payment Status</option>
													<option value="payment_id">Payment ID</option>
													<option value="payment_date">Payment Date</option>
													<option value="payment_fullname">Full Name</option>
													<option value="billing_address">Billing Address</option>
													<option value="shipping_address">Shipping Address</option>
												</optgroup>
											<?php } ?>

											<?php if(!empty($form_approval_enable)){ ?>
												<optgroup label="Approval Workflow">
													<option value="approval_note">Approval Note</option>
													<option value="approval_note_all">Approval Note (From all approvers)</option>
													<option value="approval_status">Approval Status</option>
												</optgroup>
											<?php } ?>
											
										</select>
										
										<div>
											<div id="tempvar_help_content" style="display: none">
												<h5>What is a merge tag?</h5>
												<p>A merge tag is a special identifier that is automatically replaced with data typed in by a user.</p>

												<h5>How can I use it?</h5>
												<p>Simply copy the tag name (including curly braces) into the field that support it.</p>

											</div>
											<div id="tempvar_help_trigger" style="overflow: auto"><a href="">more info</a></div>
										</div>
									</div> 
								</li>
							</ul>
						</form>
					</div>
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

		<div id="dialog-confirm-integration-delete" title="Are you sure you want to remove the integration?" class="buttons" style="display: none">
			<span class="icon-bubble-notification"></span>
			<p id="dialog-confirm-integration-delete-msg">
				This will unlink Google Calendar from your form.<br/>
				<strong id="dialog-confirm-integration-delete-info">Your calendar will remain intact but won't receive any new events.</strong><br/><br/>
			</p>				
		</div>
 
<?php
	$footer_data =<<<EOT
<script type="text/javascript">
	$(function(){
		{$jquery_data_code}		
    });
</script>
<script type="text/javascript" src="js/popper.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/tippy.index.all.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.core.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.widget.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.tabs.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.mouse.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.sortable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.draggable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.position.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.dialog.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.effects.core.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.effects.pulsate.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery.tools.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/datepick5/jquery.plugin.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/datepick5/jquery.datepick.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/datepick5/jquery.datepick.ext.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/integration_gcal.js{$mf_version_tag}"></script>
<style>
.tippy-tooltip{
	font-size: 98%;
}
</style>
EOT;

	require('includes/footer.php'); 
?>