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

	require('includes/users-functions.php');
	
	$user_id = (int) trim($_GET['id']);
	$nav 	 = trim($_GET['nav'] ?? '');

	if(empty($user_id)){
		die("Invalid Request");
	}

	//check user privileges, is this user has privilege to administer MachForm?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$_SESSION['MF_DENIED'] = "You don't have permission to administer Users.";

		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
		exit;
	}

	$dbh = mf_connect_db();
	
	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);
	
	//if there is "nav" parameter, we need to determine the correct entry id and override the existing user_id
	if(!empty($nav)){
		$exclude_admin = false;

		$all_user_id_array = mf_get_filtered_users_ids($dbh,$_SESSION['filter_users'],$exclude_admin);
		$user_key = array_keys($all_user_id_array,$user_id);
		$user_key = $user_key[0];

		if($nav == 'prev'){
			$user_key--;
		}else{
			$user_key++;
		}

		$user_id = $all_user_id_array[$user_key];

		//if there is no user_id, fetch the first/last member of the array
		if(empty($user_id)){
			if($nav == 'prev'){
				$user_id = array_pop($all_user_id_array);
			}else{
				$user_id = $all_user_id_array[0];
			}
		}
	}

	//get user information
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
	$user_profile = $row;
	
	if (empty($user_profile)) {
		$_SESSION['MF_DENIED'] = "You don't have permission to access this page.";

		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
		exit;
	}

	//if this user is admin, all privileges should be available
	if(!empty($user_profile['priv_administer'])){
		$user_profile['priv_new_forms'] = 1;
		$user_profile['priv_new_themes'] = 1;
	}

	$is_user_suspended = false;
	if($user_profile['status'] == 2){
		$is_user_suspended = true;
	}	
	
	$privileges = array();
	if(!empty($user_profile['priv_new_forms'])){
		$privileges[] = 'Able to <strong>create new forms</strong>';
	}
	if(!empty($user_profile['priv_new_themes'])){
		$privileges[] = 'Able to <strong>create new themes</strong>';
	}

	$user_is_admin = false;

	if(!empty($user_profile['priv_administer'])){
		if($user_id == 1){
			$privileges[] = 'Able to <strong>administer MachForm</strong> (Main Administrator)';
		}else{
			$privileges[] = 'Able to <strong>administer MachForm</strong>';
		}
		$user_is_admin = true;
	}

	//get form permissions data
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
	$permissions_data = array();
	$i=0;
	while($row = mf_do_fetch_result($sth)){ 
		if(!empty($row['form_name'])){		
			$permissions_data[$i]['form_name'] = $row['form_name'];
		}else{
			$permissions_data[$i]['form_name'] = '-Untitled Form- (#'.$row['form_id'].')';
		}

		$permissions_data[$i]['form_id'] 	  = $row['form_id'];
		$permissions_data[$i]['edit_form'] 	  = $row['edit_form'];
		$permissions_data[$i]['edit_report']  = $row['edit_report'];
		$permissions_data[$i]['edit_entries'] = $row['edit_entries'];
		$permissions_data[$i]['view_entries'] = $row['view_entries'];

		$i++;
	}
	
	$perm_style = '';
	if($i >= 15){
		$perm_style =<<<EOT
<style>
	.me_center_div { padding-left: 10px; }
</style>
EOT;
	}

	$header_data =<<<EOT
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css{$mf_version_tag}" rel="stylesheet" />
{$perm_style}
EOT;

	$current_nav_tab = 'users';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post view_user">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_users.php'>Users Manager</a>"; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> #<?php echo $user_id; ?></h2>
							<p>Displaying user #<?php echo $user_id; ?></p>
						</div>	
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>

				<div class="content_body">
					<div id="vu_details" style="padding-top: 0px" data-csrftoken="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>" data-userid="<?php echo $user_id; ?>">
						<div id="vu_profile">
							<h2 class="vu_userfullname"><?php echo htmlspecialchars($user_profile['user_fullname']); ?></h2>
							<h5 class="vu_email"><?php echo htmlspecialchars($user_profile['user_email']); ?></h5>
							<?php
								if(!empty($user_profile['last_login_date']) && !empty($user_profile['last_ip_address'])){
									echo '<div id="vu_log">Last login <strong>'.mf_short_relative_date($user_profile['last_login_date']).'</strong> from <strong>'.$user_profile['last_ip_address'].'</strong></div>';
								}
								
								if($is_user_suspended){
									echo '<div id="vu_suspended">This user is currently being <span>SUSPENDED</span></div>';
								}
							?>
						</div>
						<table width="100%" cellspacing="0" cellpadding="0" border="0" id="vu_privileges">
							<tbody>		
								<tr>
							  	    <td>
							  	    	<div class="vu_title">
							  	    		Privileges
							  	    	</div>
							  	    </td>
							  	</tr> 
								<?php
									if(!empty($privileges)){
										$i = 2;
										foreach ($privileges as $priv_title) {
											$class_tag = '';
											if($i % 2 == 0){
												$class_tag = 'class="alt"';
											}
											echo '<tr '.$class_tag.'><td><span class="vu_checkbox"><span class="icon-checkmark vu_checkbox_icon"></span>'.$priv_title.'</span></td></tr>';
											$i++;
										}
									}else{
								?>
									<tr class="alt">
								  	    <td><span class="vu_nopriv"><span class="icon-notification vu_nopriv_icon"></span> This user has <strong>no privileges</strong> to create new forms, themes or administer MachForm.</span></td>
								  	</tr>
							  	<?php } ?>

							</tbody>
						</table>
						<?php if($user_is_admin === true){ ?>
						<table width="100%" cellspacing="0" cellpadding="0" border="0" id="vu_perm_header">
								<tbody>		
									<tr>
								  	    <td>
								  	    	<div class="vu_title">
								  	    		Permissions
								  	    	</div>
								  	    </td>
								  	</tr>
								  	<tr class="alt">
								  	    <td>
								  	    	<span class="vu_checkbox">This user has <strong>full permission</strong> to all forms and entries.</span>
								  	    </td>
								  	</tr> 
								</tbody>
						</table>
						<?php 
							} else { 
								 if(!empty($permissions_data)){
						?>
						
						<table width="100%" cellspacing="0" cellpadding="0" border="0" id="vu_perm_header">
								<tbody>		
									<tr>
								  	    <td>
								  	    	<div class="vu_title">
								  	    		Permissions
								  	    	</div>
								  	    </td>
								  		<td class="vu_permission_header" width="70px">Edit Form</td>
								  		<td class="vu_permission_header" width="70px">Edit Report</td>
								  		<td class="vu_permission_header" width="70px">Edit Entries</td>
								  		<td class="vu_permission_header" width="70px">View Entries</td>
								  	</tr> 
								</tbody>
						</table>

						<div id="vu_permission_container">
							<table width="100%" cellspacing="0" cellpadding="0" border="0" id="vu_perm_body" style="margin-top: 0px">
								<tbody>		
									<?php
										$i = 2;
										$checkmark_tag = '<div class="me_center_div"><span class="icon-checkmark vu_perm_checkbox"></span></div>';

										foreach ($permissions_data as $value) {
											$class_tag = '';
											if($i % 2 == 0){
												$class_tag = 'class="alt"';
											}
										
									?>
											<tr <?php echo $class_tag; ?>>
										  	    <td><div class="vu_perm_title"><span class="icon-document" style="margin-right: 5px"></span> <a class="blue_dotted" href="form_info.php?id=<?php echo $value['form_id'] ?>"><?php echo htmlspecialchars($value['form_name']); ?></a></div></td>
										  	    <td width="70px"><?php if(!empty($value['edit_form'])){ echo $checkmark_tag; }else{ echo '&nbsp;'; }; ?></td>
										  	    <td width="70px"><?php if(!empty($value['edit_report'])){ echo $checkmark_tag; }else{ echo '&nbsp;'; }; ?></td>
										  	    <td width="70px"><?php if(!empty($value['edit_entries'])){ echo $checkmark_tag; }else{ echo '&nbsp;'; }; ?></td>
										  	    <td width="70px"><?php if(!empty($value['view_entries'])){ echo $checkmark_tag; }else{ echo '&nbsp;'; }; ?></td>
										  	</tr>

								  	<?php 
								  			$i++;
								  		} 
								  	?>
								  	
								</tbody>
							</table>
						</div>
						
						<?php 
								}else{
						?>
								<table width="100%" cellspacing="0" cellpadding="0" border="0" id="vu_perm_header">
										<tbody>		
											<tr>
										  	    <td>
										  	    	<div class="vu_title">
										  	    		Permissions
										  	    	</div>
										  	    </td>
										  	</tr>
										  	<tr class="alt">
										  	    <td>
										  	    	<span class="vu_nopriv"><span class="icon-notification vu_nopriv_icon"></span> This user has <strong>no permission</strong> to any forms or entries.</span>
										  	    </td>
										  	</tr> 
										</tbody>
								</table>	

						<?php	
								}
							} 
						?>

						<?php if(!empty($user_profile['tsv_enable'])){ ?>
						<table width="100%" cellspacing="0" cellpadding="0" border="0" id="vu_security">
							<tbody>		
								<tr>
							  	    <td>
							  	    	<div class="vu_title">
							  	    		Security
							  	    	</div>
							  	    </td>
							  	</tr>
							  	<tr class="alt">
								  	<td><span class="vu_checkbox">2-Step Verification <strong>enabled</strong></span></td>
								</tr> 
							</tbody>
						</table>
						<?php } ?>
					</div>
					<div id="ve_actions">
						<div id="ve_entry_navigation">
							<a href="<?php echo "view_user.php?id={$user_id}&nav=prev"; ?>" title="Previous User"><span class="icon-arrow-left"></span></a>
							<a href="<?php echo "view_user.php?id={$user_id}&nav=next"; ?>" title="Next User" style="margin-left: 5px"><span class="icon-arrow-right"></span></a>
						</div>
						
						<?php if($user_id == 1 && $_SESSION['mf_user_id'] != 1){ ?>
						
						<?php }else{ ?>
						<div id="ve_entry_actions" class="gradient_blue">
							<ul>
								<li style="border-bottom: 1px dashed #8EACCF"><a id="vu_action_edit" title="Edit User" href="<?php echo "edit_user.php?id={$user_id}"; ?>"><span class="icon-pencil"></span>Edit</a></li>
								
								<?php if(empty($mf_settings['ldap_enable']) || (!empty($mf_settings['ldap_enable']) && empty($mf_settings['ldap_exclusive']))){ ?>
									<li style="border-bottom: 1px dashed #8EACCF"><a id="vu_action_password" title="Change Password" href="#"><span class="icon-key"></span>Password</a></li>
								<?php } ?>
								<?php if($user_id != 1){ ?>
									<?php
										if($is_user_suspended){
											echo '<li style="border-bottom: 1px dashed #8EACCF"><a id="vu_action_suspend" class="unsuspend" title="Un-Suspend User" href="#"><span class="icon-unlocked"></span>Unblock</a></li>';
										}else{
											echo '<li style="border-bottom: 1px dashed #8EACCF"><a id="vu_action_suspend" title="Suspend User" href="#"><span class="icon-user-block"></span>Suspend</a></li>';
										}
									?>
									<li><a id="vu_action_delete" title="Delete User" href="#"><span class="icon-remove"></span>Delete</a></li>
								<?php } ?>

							</ul>
						</div>
						<?php } ?>
					</div>
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

<div id="dialog-confirm-user-delete" title="Are you sure you want to delete this user?" class="buttons" style="display: none">
	<span class="icon-bubble-notification"></span>
	<p id="dialog-confirm-user-delete-msg">
		This action cannot be undone.<br/>
		<strong id="dialog-confirm-user-delete-info">This user will be deleted and blocked.</strong><br/><br/>
		
	</p>				
</div>

<div id="dialog-change-password" title="Change User Password" class="buttons" style="display: none"> 
	<form id="dialog-change-password-form" class="dialog-form" style="margin-bottom: 10px">				
			<ul>
				<li>
					<label for="dialog-change-password-input1" class="description">Enter New Password</label>
					<input type="password" id="dialog-change-password-input1" name="dialog-change-password-input1" class="text large" value="">
					<label for="dialog-change-password-input2" style="margin-top: 15px" class="description">Confirm New Password</label>
					<input type="password" id="dialog-change-password-input2" name="dialog-change-password-input2" class="text large" value="">	
					<span style="display: block;margin-top: 10px">
						<input type="checkbox"  value="1" class="checkbox" id="dialog-change-password-send-login" name="dialog-change-password-send-login" style="margin-left: 0px">
						<label for="dialog-change-password-send-login" class="choice change-password">Send login information to user</label>
					</span>			
				</li>
			</ul>
	</form>
</div>

<div id="dialog-password-changed" title="Success!" class="buttons" style="display: none">
	<img src="images/icons/62_green_48.png" title="Success" /> 
	<p id="dialog-password-changed-msg">
			The new password has been saved.
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
<script type="text/javascript" src="js/view_user.js{$mf_version_tag}"></script>
EOT;

	require('includes/footer.php'); 
?>