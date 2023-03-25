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
	require('includes/post-functions.php');
	require('includes/filter-functions.php');
	require('lib/password-hash.php');

	$dbh = mf_connect_db();
	
	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);

	//check user privileges, is this user has privilege to administer MachForm?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$_SESSION['MF_DENIED'] = "You don't have permission to administer MachForm.";

		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
		exit;
	}

	//don't allow adding users when LDAP enabled exclusively
	if(!empty($mf_settings['ldap_enable']) && !empty($mf_settings['ldap_exclusive'])){
		$_SESSION['MF_DENIED'] = "You can't add users from MachForm when LDAP enabled exclusively.";

		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
		exit;
	}

	//check current license usage, if this is Standard or Professional
	if($mf_settings['license_key'][0] == 'S' || $mf_settings['license_key'][0] == 'P'){
		$query = "select count(user_id) user_total from ".MF_TABLE_PREFIX."users where `status` > 0";
		
		$params = array();
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		$current_total_user = $row['user_total'];

		if($mf_settings['license_key'][0] == 'S'){
			$max_user = 5;
		}else if($mf_settings['license_key'][0] == 'P'){
			$max_user = 20;
		}

		if($current_total_user >= $max_user){
			$_SESSION['MF_DENIED'] = "You have 0 users left. Please upgrade your license to add more.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}

	//get the list of the form, put them into array
	$query = "SELECT 
					form_name,
					form_id
				FROM
					".MF_TABLE_PREFIX."forms
				WHERE
					form_active=0 or form_active=1
			 ORDER BY 
					form_name ASC";
	
	$params = array();
	$sth = mf_do_query($query,$params,$dbh);
	
	$form_list_array = array();
	$i=0;
	while($row = mf_do_fetch_result($sth)){
		$form_list_array[$i]['form_id']   	  = $row['form_id'];

		if(!empty($row['form_name'])){		
			$form_list_array[$i]['form_name'] = $row['form_name'];
		}else{
			$form_list_array[$i]['form_name'] = '-Untitled Form- (#'.$row['form_id'].')';
		}
		$i++;
	}
	
	if(mf_is_form_submitted()){ //if form submitted

		//validate CSRF token
		mf_verify_csrf_token($_POST['csrf_token']);
		
		//get all required inputs
		$user_input['user_bulk_data'] 		= $_POST['au_user_bulk_data'];

		$user_input['priv_new_forms'] 	= (int) ($_POST['au_priv_new_forms'] ?? 0);
		$user_input['priv_new_themes'] 	= (int) ($_POST['au_priv_new_themes'] ?? 0);
		$user_input['priv_administer'] 	= (int) ($_POST['au_priv_administer'] ?? 0);

		//if the user has administer privileges, make sure to get all other privileges as well
		if(!empty($user_input['priv_administer'])){
			$user_input['priv_new_forms'] = 1;
			$user_input['priv_new_themes'] = 1;
		}

		foreach ($form_list_array as $value) {
			$form_id = $value['form_id'];

			$user_input['perm_editform_'.$form_id] 	  = (int) ($_POST['perm_editform_'.$form_id] ?? 0);
			$user_input['perm_editreport_'.$form_id]  = (int) ($_POST['perm_editreport_'.$form_id] ?? 0);
			$user_input['perm_editentries_'.$form_id] = (int) ($_POST['perm_editentries_'.$form_id] ?? 0);
			$user_input['perm_viewentries_'.$form_id] = (int) ($_POST['perm_viewentries_'.$form_id] ?? 0);
		}
		//clean the inputs
		$user_input = mf_sanitize($user_input);

		//validate inputs
		$error_messages = array();

		//validate email
		if(empty($user_input['user_bulk_data'])){
			$error_messages['user_bulk_data'] = 'This field is required. Please enter users data.';
		}else{
			//parse data into array for easier processing
			$temp_data = explode("\n", $user_input['user_bulk_data']);
			array_walk($temp_data, 'mf_trim_value');

			$i=0;
			foreach ($temp_data as $row_data) {
				if(empty($row_data)){
					continue;
				}

				$columns = array();
				$columns = explode(',', $row_data);

				array_walk($columns, 'mf_trim_value');
				
				$users_data[$i] = $columns;
				$i++;
				
			}

			//check for data format, all required columns must exist
			$is_data_incomplete = false;
			foreach ($users_data as $value) {
				if(empty($value[0]) || empty($value[1]) || empty($value[2])){
					$is_data_incomplete = true;
					$error_messages['user_bulk_data'] = 'Users data incorrectly formatted or missing some information. Please check again.';
					break;
				}
			}

			//check for email format, all emails must be correct
			if($is_data_incomplete === false){
				$email_regex  = '/^[A-z0-9][\+\w.\'-]*@[A-z0-9][\w\-\.]*\.[A-z0-9]{2,}$/';
				$invalid_emails = array();

				foreach ($users_data as $value) {
					$email_address = $value[1];
					$regex_result = preg_match($email_regex, $email_address);
					
					if(empty($regex_result)){
						//the email being entered is incorrectly formatted
						$invalid_emails[] = $email_address;
					}
				}

				if(!empty($invalid_emails)){
					$error_messages['user_bulk_data'] = 'Invalid email address(es): '.implode(', ', $invalid_emails).'. <br/>Please enter valid email address.';
				}else{
					//check each email, make sure they aren't registered already
					
					$invalid_registered_emails = array();
					foreach ($users_data as $value) {
						$email_address = $value[1];
						
						$query = "select count(user_email) total_user from `".MF_TABLE_PREFIX."users` where user_email = ? and `status` > 0";
				
						$params = array($email_address);
						$sth = mf_do_query($query,$params,$dbh);
						$row = mf_do_fetch_result($sth);

						if(!empty($row['total_user'])){
							$invalid_registered_emails[] = $email_address;
						}
					}

					if(!empty($invalid_registered_emails)){
						$error_messages['user_bulk_data'] = 'These email addresses already being used: '.implode(', ', $invalid_registered_emails);
					}else{
						//check for total user usage, make sure not to exceed the limit
						if($mf_settings['license_key'][0] == 'S' || $mf_settings['license_key'][0] == 'P'){
							$query = "select count(user_id) user_total from ".MF_TABLE_PREFIX."users where `status` > 0";
							
							$params = array();
							$sth = mf_do_query($query,$params,$dbh);
							$row = mf_do_fetch_result($sth);

							$current_total_user = $row['user_total'];

							if($mf_settings['license_key'][0] == 'S'){
								$max_user = 5;
							}else if($mf_settings['license_key'][0] == 'P'){
								$max_user = 20;
							}
							$total_user_to_add = count($users_data);
							if($current_total_user + $total_user_to_add  > $max_user){
								$error_messages['user_bulk_data'] = "You've exceeded the maximum number of users. Please upgrade your license to add more users.";
							}
						}
					}

				}
				
			}

		}


		if(!empty($error_messages)){
			$_SESSION['MF_ERROR'] = 'Please correct the marked field(s) below.';
		}else{
			//everything is validated, continue creating user

			$hasher = new PasswordHash(8, FALSE);

			foreach ($users_data as $user_info) {
				$user_name  = $user_info[0];
				$user_email = strtolower($user_info[1]);
				$user_password = $user_info[2];

				//insert into ap_users table
				$password_hash = $hasher->HashPassword($user_password);

				$query = "INSERT INTO 
									`".MF_TABLE_PREFIX."users`( 
												`user_email`, 
												`user_password`, 
												`user_fullname`, 
												`priv_administer`, 
												`priv_new_forms`, 
												`priv_new_themes`, 
												`status`) 
							  VALUES (?, ?, ?, ?, ?, ?, ?);";
				$params = array(
								$user_email,
								$password_hash,
								$user_name,
								(int) $user_input['priv_administer'],
								(int) $user_input['priv_new_forms'],
								(int) $user_input['priv_new_themes'],
								1);
				
				mf_do_query($query,$params,$dbh);
				$user_id = (int) $dbh->lastInsertId();

				//insert into ap_folders table
				//create a default folder for the user
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
				mf_do_query($query,$params,$dbh);

				//insert into ap_permissions table
				foreach ($form_list_array as $value) {
					$form_id = $value['form_id'];

					if(!empty($user_input['perm_editentries_'.$form_id])){
						$user_input['perm_viewentries_'.$form_id] = 1;
					}
					
					//if all permission are empty, don't do insert
					if(empty($user_input['perm_editform_'.$form_id]) && empty($user_input['perm_editreport_'.$form_id]) && empty($user_input['perm_editentries_'.$form_id]) && empty($user_input['perm_viewentries_'.$form_id])){
						continue;
					}

					$params = array(
									$form_id, 
									$user_id, 
									$user_input['perm_editform_'.$form_id], 
									$user_input['perm_editreport_'.$form_id], 
									$user_input['perm_editentries_'.$form_id], 
									$user_input['perm_viewentries_'.$form_id]);

					$query = "INSERT INTO 
										`".MF_TABLE_PREFIX."permissions` (
																`form_id`, 
																`user_id`, 
																`edit_form`, 
																`edit_report`, 
																`edit_entries`, 
																`view_entries`) 
									VALUES (?, ?, ?, ?, ?, ?);";
					mf_do_query($query,$params,$dbh);
				}
			}
			

			//redirect to manage_users page and display success message
			$_SESSION['MF_SUCCESS'] = 'New users has been added.';

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/manage_users.php");
			exit;
		}
	}else{
		$user_input['user_bulk_data'] = "John Doe, john@example.com, password1\nJane Doe, jane@example.com, password2\nRobert Doyle, doyle@example.com, password3\n";
	}
	

	$current_nav_tab = 'users';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post add_user">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><a class="breadcrumb" href='manage_users.php'>Users Manager</a> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Bulk Add Users</h2>
							<p>Create multiple users and set permissions</p>
						</div>
						<div style="float: right;margin-right: 0px;padding-top: 26px;">
								<a href="add_user.php" id="add_user_bulk_link" class="">
									<span class="icon-tab"></span> Switch to <strong>Normal</strong> Mode
								</a>
						</div>	
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>
				
				<?php mf_show_message(); ?>

				<div class="content_body">
					<form id="add_user_form" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
					<ul id="au_main_list">
						<li>
							<div id="au_box_user_profile" class="au_box_main gradient_blue" style="width: 80%">
								<div class="au_box_meta">
									<h1>1.</h1>
									<h6>Define Profiles</h6>
								</div>
								<div class="au_box_content" style="padding-bottom: 15px;width: 545px">
									<label class="description <?php if(!empty($error_messages['user_bulk_data'])){ echo 'label_red'; } ?>" for="au_user_bulk_data">Enter multiple users data. One user per line. <span class="required">*</span> </label>
									<textarea id="au_user_bulk_data" name="au_user_bulk_data" class="element textarea medium" rows="8" cols="90"><?php echo htmlspecialchars($user_input['user_bulk_data']); ?></textarea>
									<?php
										if(!empty($error_messages['user_bulk_data'])){
											echo '<span class="au_error_span">'.$error_messages['user_bulk_data'].'</span>';
										}
									?>
									<span id="au_bulk_info">
										Line format: <strong>Name, Email, Password</strong><br/>
										Example: <strong>John Doe, john@doe.com, secretpassword</strong>
									</span>
								</div>
							</div>
						</li>
						<li class="ps_arrow"><span class="icon-arrow-down11 spacer-icon"></span></li>
						<li>
							<div id="au_box_privileges" class="au_box_main gradient_red">
								<div class="au_box_meta">
									<h1>2.</h1>
									<h6>Set Privileges</h6>
								</div>
								<div class="au_box_content" style="padding-top: 10px;min-height: 90px;">
										<input id="au_priv_new_forms" name="au_priv_new_forms" class="checkbox" <?php if(!empty($user_input['priv_new_forms'])){ echo 'checked="checked"'; } ?> value="1" type="checkbox" style="margin-left: 0px" <?php if(!empty($user_input['priv_administer'])){ echo 'disabled="disabled"'; } ?>>
										<label class="choice" for="au_priv_new_forms">Allow users to create new forms</label>
											
										<div style="clear: both;margin-top: 10px"></div>
										
										<input id="au_priv_new_themes" name="au_priv_new_themes" class="checkbox" <?php if(!empty($user_input['priv_new_themes'])){ echo 'checked="checked"'; } ?> value="1" type="checkbox" style="margin-left: 0px;" <?php if(!empty($user_input['priv_administer'])){ echo 'disabled="disabled"'; } ?>>
										<label class="choice" for="au_priv_new_themes">Allow users to create new themes</label>
										
										<div style="clear: both;margin-top: 10px"></div>
										
										<input id="au_priv_administer" name="au_priv_administer" class="checkbox" <?php if(!empty($user_input['priv_administer'])){ echo 'checked="checked"'; } ?> value="1" type="checkbox" style="margin-left: 0px;">
										<label class="choice" for="au_priv_administer">Allow users to administer MachForm</label>
								</div>
							</div>
						</li>
						<li class="ps_arrow"><span class="icon-arrow-down11 spacer-icon"></span></li>
						<?php
							if(!empty($form_list_array)){
						?>
						<li class="user_permissions_list" <?php if(!empty($user_input['priv_administer'])){ echo 'style="display: none"'; } ?>>
							<div id="au_box_permissions" class="au_box_main gradient_green">
								<div class="au_box_meta">
									<h1>3.</h1>
									<h6>Set Permissions</h6>
								</div>
								<div class="au_box_content">
									<ul id="au_li_permissions">
										
										<?php
											foreach ($form_list_array as $value) {
												$form_id = $value['form_id'];

												if(!empty($user_input['perm_editform_'.$form_id])){
													$class_attr = 'class="highlight_red"';
												}else if(!empty($user_input['perm_editentries_'.$form_id])){
													$class_attr = 'class="highlight_yellow"';
												}else if(!empty($user_input['perm_viewentries_'.$form_id]) || !empty($user_input['perm_editreport_'.$form_id])){
													$class_attr = 'class="highlight_green"';
												}else{
													$class_attr = '';
												}
										?>
												<li id="li_<?php echo $form_id; ?>" <?php echo $class_attr; ?>>
													<div class="au_perm_title"><?php echo $value['form_name']; ?></div>
													<div class="au_perm_controls">
														<span class="au_perm_guide">allow users to</span> <span class="au_perm_arrow">&#8674;</span>
														<input id="perm_editform_<?php echo $form_id; ?>" name="perm_editform_<?php echo $form_id; ?>" <?php if(!empty($user_input['perm_editform_'.$form_id])){ echo 'checked="checked"'; } ?> class="checkbox cb_editform" value="1" type="checkbox" style="margin-left: 5px">
														<label class="choice" for="perm_editform_<?php echo $form_id; ?>">Edit Form</label>

														<input id="perm_editreport_<?php echo $form_id; ?>" name="perm_editreport_<?php echo $form_id; ?>" <?php if(!empty($user_input['perm_editreport_'.$form_id])){ echo 'checked="checked"'; } ?> class="checkbox cb_editreport" value="1" type="checkbox">
														<label class="choice" for="perm_editreport_<?php echo $form_id; ?>">Edit Report</label>

														<input id="perm_editentries_<?php echo $form_id; ?>" name="perm_editentries_<?php echo $form_id; ?>" <?php if(!empty($user_input['perm_editentries_'.$form_id])){ echo 'checked="checked"'; } ?> class="checkbox cb_editentries" value="1" type="checkbox">
														<label class="choice" for="perm_editentries_<?php echo $form_id; ?>">Edit Entries</label>

														<input id="perm_viewentries_<?php echo $form_id; ?>" name="perm_viewentries_<?php echo $form_id; ?>" <?php if(!empty($user_input['perm_viewentries_'.$form_id]) || !empty($user_input['perm_editentries_'.$form_id])){ echo 'checked="checked"'; } ?> class="checkbox cb_viewentries" <?php if(!empty($user_input['perm_editentries_'.$form_id])){ echo 'disabled="disabled"'; } ?> value="1" type="checkbox">
														<label class="choice" for="perm_viewentries_<?php echo $form_id; ?>">View Entries</label>
													</div>
												</li>
										<?php
											}
										?>
									</ul>
									<div id="au_bulk_select">
										<select class="element select" id="au_bulk_action" name="au_bulk_action">
											<option value="" selected="selected">Bulk Action</option> 
											<optgroup label="Select All:">
												<option value="select_editform">Edit Form</option>
												<option value="select_editreport">Edit Report</option>
												<option value="select_editentries">Edit Entries</option>
												<option value="select_viewentries">View Entries</option>
											</optgroup>
											<optgroup label="Unselect All:">
												<option value="unselect_editform">Edit Form</option>
												<option value="unselect_editreport">Edit Report</option>
												<option value="unselect_editentries">Edit Entries</option>
												<option value="unselect_viewentries">View Entries</option>
											</optgroup>
										</select>
									</div>
								</div>
							</div>
						</li>
						<li class="ps_arrow user_permissions_list" <?php if(!empty($user_input['priv_administer'])){ echo 'style="display: none"'; } ?>><span class="icon-arrow-down11 spacer-icon"></span></li>
						<?php } ?>
						<li>
							<div>
								<a href="#" id="button_add_user" class="bb_button bb_small bb_green">
									<span class="icon-disk" style="margin-right: 5px"></span>Add Users
								</a>
							</div>
						</li>	
					</ul>
					<input type="hidden" name="submit_form" value="1" />
					<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>">
					</form>
					
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

 
<?php
	$footer_data =<<<EOT
<script src="js/popper.min.js{$mf_version_tag}"></script>
<script src="js/tippy.index.all.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/add_user.js{$mf_version_tag}"></script>
EOT;

	require('includes/footer.php'); 
?>