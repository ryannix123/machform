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
	

	$dbh = mf_connect_db();

	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);

	//check user privileges, is this user has privilege to administer MachForm?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$_SESSION['MF_DENIED'] = "You don't have permission to administer MachForm.";

		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
		exit;
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
	
	$ldap_enabled_exclusively = false;
	if(!empty($mf_settings['ldap_enable']) && !empty($mf_settings['ldap_exclusive'])){
		$ldap_enabled_exclusively = true;
	}

	if(mf_is_form_submitted()){ //if form submitted
		
		//validate CSRF token
		mf_verify_csrf_token($_POST['csrf_token']);

		//get all required inputs
		$user_input['user_name'] 		= $_POST['au_user_name'];
		$user_input['user_email'] 		= strtolower($_POST['au_user_email']);
		$user_input['user_id'] 			= (int) $_POST['user_id'];
		$user_input['tsv_enable'] 		= (int) ($_POST['au_tsv_enable'] ?? 0);

		$user_input['priv_new_forms'] 	= (int) ($_POST['au_priv_new_forms'] ?? 0);
		$user_input['priv_new_themes'] 	= (int) ($_POST['au_priv_new_themes'] ?? 0);
		$user_input['priv_administer'] 	= (int) ($_POST['au_priv_administer'] ?? 0);

		if(empty($user_input['user_id'])){
			die('User ID required.');
		}

		//only administrator can modify himself
		if($user_input['user_id'] == 1 && $_SESSION['mf_user_id'] != 1){
			die("Access Denied. You don't have permission to edit Main Administrator.");
		}

		//make sure that Main Administrator privileges can't be modified
		if($user_input['user_id'] == 1){
			$user_input['priv_administer'] = 1;
		}

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

		//don't validate user name and email when LDAP enabled exclusively
		if(!$ldap_enabled_exclusively){
			//validate name
			if(empty($user_input['user_name'])){
				$error_messages['user_name'] = 'This field is required. Please enter a name.';
			}

			//validate email
			if(empty($user_input['user_email'])){
				$error_messages['user_email'] = 'This field is required. Please enter an email.';
			}else{
				//check for valid email address
				$email_regex  = '/^[A-z0-9][\+\w.\'-]*@[A-z0-9][\w\-\.]*\.[A-z0-9]{2,}$/';
				$regex_result = preg_match($email_regex, $user_input['user_email']);
					
				if(empty($regex_result)){
					//the email being entered is incorrectly formatted
					$error_messages['user_email'] = 'Please enter a valid email address.';
				}else{
					//check for duplicate
					$query = "select count(user_email) total_user from `".MF_TABLE_PREFIX."users` where user_email = ? and user_id <> ? and `status` > 0";
					
					$params = array($user_input['user_email'],$user_input['user_id']);
					$sth = mf_do_query($query,$params,$dbh);
					$row = mf_do_fetch_result($sth);

					if(!empty($row['total_user'])){
						$error_messages['user_email'] = 'This email address already being used.';
					}
				}
			}
		}


		if(!empty($error_messages)){
			$_SESSION['MF_ERROR'] = 'Please correct the marked field(s) below.';
		}else{
			//everything is validated, continue updating user

			//update ap_users table
			if(!$ldap_enabled_exclusively){
				$query = "UPDATE 
								`".MF_TABLE_PREFIX."users` 
							SET	
								`user_email`=?,  
								`user_fullname`=?, 
								`priv_administer`=?, 
								`priv_new_forms`=?, 
								`priv_new_themes`=?,
								`tsv_enable`=? 
						 WHERE  `user_id` = ?";
				$params = array(
								$user_input['user_email'],
								$user_input['user_name'],
								$user_input['priv_administer'],
								$user_input['priv_new_forms'],
								$user_input['priv_new_themes'],
								$user_input['tsv_enable'],
								$user_input['user_id']);
			}else{
				$query = "UPDATE 
								`".MF_TABLE_PREFIX."users` 
							SET	
								`priv_administer`=?, 
								`priv_new_forms`=?, 
								`priv_new_themes`=?,
								`tsv_enable`=? 
						 WHERE  `user_id` = ?";
				$params = array(
								$user_input['priv_administer'],
								$user_input['priv_new_forms'],
								$user_input['priv_new_themes'],
								$user_input['tsv_enable'],
								$user_input['user_id']);
			}
			
			mf_do_query($query,$params,$dbh);

			//when TSV being disabled, reset secret key and previous tsv code log
			if(empty($user_input['tsv_enable'])){
				$query = "UPDATE ".MF_TABLE_PREFIX."users set tsv_enable=0,tsv_secret='',tsv_code_log='' where user_id=?";
				$params = array($user_input['user_id']);
				mf_do_query($query,$params,$dbh);
			}

			//delete existing permissions
			$query = "DELETE from ".MF_TABLE_PREFIX."permissions WHERE user_id = ?";
			$params = array($user_input['user_id']);
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
								$user_input['user_id'], 
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

			
			//redirect to manage_users page and display success message
			$_SESSION['MF_SUCCESS'] = 'User #'.$user_input['user_id'].' has been updated.';

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/view_user.php?id=".$user_input['user_id']);
			exit;
		}
	}else{
		//populate user data
		$user_id = (int) trim($_GET['id']);
		
		if(empty($user_id)){
			die("Invalid Request");
		}

		//only administrator can modify himself
		if($user_id == 1 && $_SESSION['mf_user_id'] != 1){
			$_SESSION['MF_DENIED'] = "You don't have permission to edit Main Administrator.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;

		}

		$user_input['user_id'] = $user_id;

		//get user profile data
		$query = "SELECT 
						user_email,
						user_fullname,
						priv_administer,
						priv_new_forms,
						priv_new_themes,
						last_login_date,
						last_ip_address,
						tsv_enable,
						`status` 
				    FROM 
						".MF_TABLE_PREFIX."users 
				   WHERE 
				   		user_id=? and `status` > 0";
		$params = array($user_id);
				
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		$user_input['user_name']  		= $row['user_fullname'];
		$user_input['user_email'] 	    = $row['user_email'];
		$user_input['priv_new_forms'] 	= $row['priv_new_forms'];
		$user_input['priv_new_themes'] 	= $row['priv_new_themes'];
		$user_input['priv_administer'] 	= $row['priv_administer'];
		$user_input['tsv_enable'] 		= (int) $row['tsv_enable'];
 
		//if this user is admin, all privileges should be available
		if(!empty($user_input['priv_administer'])){
			$user_input['priv_new_forms'] = 1;
			$user_input['priv_new_themes'] = 1;
		}

		//get permission list for this user
		$query = "SELECT 
					A.form_id,
					A.edit_form,
					A.edit_report,
					A.edit_entries,
					A.view_entries,
					B.form_name
			    FROM
			   		".MF_TABLE_PREFIX."permissions A LEFT JOIN ".MF_TABLE_PREFIX."forms B on A.form_id=B.form_id
			   WHERE 
			   		A.user_id = ? and (B.form_active=0 or B.form_active=1)
			ORDER BY 
					B.form_name ASC";
		$params = array($user_id);
				
		$sth = mf_do_query($query,$params,$dbh);
		
		while($row = mf_do_fetch_result($sth)){ 
			$form_id = (int) $row['form_id'];
		
			$user_input['perm_editform_'.$form_id] 	  = $row['edit_form'];
			$user_input['perm_editreport_'.$form_id]  = $row['edit_report'];
			$user_input['perm_editentries_'.$form_id] = $row['edit_entries'];
			$user_input['perm_viewentries_'.$form_id] = $row['view_entries'];
		}

	}
	
	$current_nav_tab = 'users';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post add_user">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><a class="breadcrumb" href='manage_users.php'>Users Manager</a> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <a class="breadcrumb" href='view_user.php?id=<?php echo $user_input['user_id']; ?>'>#<?php echo $user_input['user_id']; ?></a> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Edit</h2>
							<p>Editing user #<?php echo $user_input['user_id']; ?></p>
						</div>
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>
				
				<?php mf_show_message(); ?>

				<div class="content_body">
					<form id="add_user_form" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
					<ul id="au_main_list">
						<li>
							<div id="au_box_user_profile" class="au_box_main gradient_blue">
								<div class="au_box_meta">
									<h1>1.</h1>
									<h6>Edit Profile</h6>
								</div>
								<div class="au_box_content" style="padding-bottom: 15px">
									<label class="inline description <?php if(!empty($error_messages['user_name'])){ echo 'label_red'; } ?>" for="au_user_name">Name <span class="required">*</span> </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="Full name of the new user."></span>
									<input <?php if($ldap_enabled_exclusively){ echo 'readonly="readonly"'; } ?> id="au_user_name" name="au_user_name" class="element text large" value="<?php echo htmlspecialchars($user_input['user_name']); ?>" type="text">
									<?php
										if(!empty($error_messages['user_name'])){
											echo '<span class="au_error_span">'.$error_messages['user_name'].'</span>';
										}
									?>
									
									<label class="inline description <?php if(!empty($error_messages['user_email'])){ echo 'label_red'; } ?>" for="au_user_email">Email Address <span class="required">*</span> </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="The email address must be unique. No two users can have the same email address."></span>
									<input <?php if($ldap_enabled_exclusively){ echo 'readonly="readonly"'; } ?> id="au_user_email" name="au_user_email" class="element text large" value="<?php echo htmlspecialchars($user_input['user_email']); ?>" type="text">
									<?php
										if(!empty($error_messages['user_email'])){
											echo '<span class="au_error_span">'.$error_messages['user_email'].'</span>';
										}
									?>

									<div style="clear: both;margin-top: 10px"></div>
										
									<input id="au_tsv_enable" name="au_tsv_enable" class="checkbox" <?php if(!empty($user_input['tsv_enable'])){ echo 'checked="checked"'; } ?> value="1" type="checkbox" style="margin-left: 0px;">
									<label class="choice inline" for="au_tsv_enable">Enable 2-Step Verification </label>
									<span class="icon-question helpicon clearfix" data-tippy-content="2-Step Verification is an optional but highly recommended security feature that adds an extra layer of protection to MachForm account.<br/><br/>Once enabled, MachForm will require a six-digit security code in addition to the standard password whenever a user sign-in."></span>

								</div>
							</div>
						</li>
						<li class="ps_arrow"><span class="icon-arrow-down11 spacer-icon"></span></li>
						<li>
							<div id="au_box_privileges" class="au_box_main gradient_red">
								<div class="au_box_meta">
									<h1>2.</h1>
									<h6>Edit Privileges</h6>
								</div>
								<div class="au_box_content" style="padding-top: 10px;min-height: 90px;">
										<input id="au_priv_new_forms" name="au_priv_new_forms" class="checkbox" <?php if(!empty($user_input['priv_new_forms'])){ echo 'checked="checked"'; } ?> value="1" type="checkbox" style="margin-left: 0px" <?php if(!empty($user_input['priv_administer'])){ echo 'disabled="disabled"'; } ?>>
										<label class="choice" for="au_priv_new_forms">Allow user to create new forms</label>
											
										<div style="clear: both;margin-top: 10px"></div>
										
										<input id="au_priv_new_themes" name="au_priv_new_themes" class="checkbox" <?php if(!empty($user_input['priv_new_themes'])){ echo 'checked="checked"'; } ?> value="1" type="checkbox" style="margin-left: 0px;" <?php if(!empty($user_input['priv_administer'])){ echo 'disabled="disabled"'; } ?>>
										<label class="choice" for="au_priv_new_themes">Allow user to create new themes</label>
										
										<div style="clear: both;margin-top: 10px"></div>
										
										<input id="au_priv_administer" name="au_priv_administer" class="checkbox" <?php if(!empty($user_input['priv_administer'])){ echo 'checked="checked"'; } ?> value="1" type="checkbox" style="margin-left: 0px;" <?php if($user_input['user_id'] == 1){ echo 'disabled="disabled"'; } ?>>
										<label class="choice" for="au_priv_administer">Allow user to administer MachForm</label>
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
									<h6>Edit Permissions</h6>
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
														<span class="au_perm_guide">allow user to</span> <span class="au_perm_arrow">&#8674;</span>
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
											<option value="">Bulk Action</option> 
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
								<a href="#" id="button_edit_user" class="bb_button bb_small bb_green">
									<span class="icon-disk" style="margin-right: 5px"></span>Save Changes
								</a>
							</div>
						</li>	
					</ul>
					<input type="hidden" name="submit_form" value="1" />
					<input type="hidden" name="user_id" value="<?php echo (int) $user_input['user_id']; ?>" />
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
<style>
.tippy-tooltip{
	font-size: 98%;
}
</style>
EOT;

	require('includes/footer.php'); 
?>