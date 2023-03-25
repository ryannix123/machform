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
	require('includes/common-validator.php');
	require('includes/post-functions.php');
	require('includes/filter-functions.php');
	require('includes/entry-functions.php');
	require('includes/view-functions.php');
	require('includes/users-functions.php');
	require('lib/libsodium/autoload.php');
	
	$form_id  = (int) trim($_GET['form_id']);
	$entry_id = (int) trim($_GET['entry_id']);
	$nav 	  = isset($_GET['nav']) ? trim($_GET['nav']) : '';

	$dbh = mf_connect_db();

	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);

	$mf_properties = mf_get_form_properties($dbh,$form_id,array('form_active'));
	
	
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

		//this page need edit_entries permission
		if(empty($user_perms['edit_entries'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to edit this entry.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}
	
	//get form name
	$query 	= "select 
					 form_name,
					 form_encryption_enable 
			     from 
			     	 ".MF_TABLE_PREFIX."forms 
			    where 
			    	 form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	$row['form_name'] = mf_trim_max_length($row['form_name'],65);

	if(!empty($row)){
		$form_name = htmlspecialchars($row['form_name']);
		$form_encryption_enable = (int) $row['form_encryption_enable'];
	}else{
		die("Error. Unknown form ID.");
	}

	//get entry status information 
	$query = "select 
					`status` 
				from 
					`".MF_TABLE_PREFIX."form_{$form_id}` 
			where id=?";
	$params = array($entry_id);

	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$entry_status = $row['status'];

	if(empty($entry_status)){
		$_SESSION['MF_DENIED'] = "Entry #{$entry_id} does not exist.";

		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
		exit;
	}

	$is_incomplete_entry = false;
	if($entry_status == 2){
		$is_incomplete_entry = true;
	}

	//if there is "nav" parameter, we need to determine the correct entry id and override the existing entry_id
	if(!empty($nav)){
		$entries_options = array();
		$entries_options['is_incomplete_entry'] = $is_incomplete_entry;

		$all_entry_id_array = mf_get_filtered_entries_ids($dbh,$form_id,$entries_options);
		$entry_key = array_keys($all_entry_id_array,$entry_id);
		$entry_key = $entry_key[0];

		if($nav == 'prev'){
			$entry_key--;
		}else{
			$entry_key++;
		}

		$entry_id = $all_entry_id_array[$entry_key];

		//if there is no entry_id, fetch the first/last member of the array
		if(empty($entry_id)){
			if($nav == 'prev'){
				$entry_id = array_pop($all_entry_id_array);
			}else{
				$entry_id = $all_entry_id_array[0];
			}
		}
	}

	if(mf_is_form_submitted()){ //if form submitted
		
		//validate CSRF token
		mf_verify_csrf_token($_POST['csrf_token']);

		$input_array   = mf_sanitize($_POST);
		$submit_result = mf_process_form($dbh,$input_array);
		
		if($submit_result['status'] === true){
			$_SESSION['MF_SUCCESS'] = 'Entry #'.$input_array['edit_id'].' has been updated.';

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/view_entry.php?form_id={$input_array['form_id']}&entry_id={$input_array['edit_id']}");
			exit;
		}else if($submit_result['status'] === false){ //there are errors, display the form again with the errors
			$old_values 	= $submit_result['old_values'];
			$custom_error 	= @$submit_result['custom_error'];
			$error_elements = $submit_result['error_elements'];
				
			$form_params = array();
			$form_params['populated_values'] = $old_values;
			$form_params['error_elements']   = $error_elements;
			$form_params['custom_error'] 	 = $custom_error;
			$form_params['edit_id']			 = $input_array['edit_id'];
			$form_params['integration_method'] = 'php';
			$form_params['page_number'] = 0; //display all pages (if any) as a single page

			$form_markup = mf_display_form($dbh,$input_array['form_id'],$form_params);
		}

	}else{ //otherwise, display the form with the values
		//set session value to override password protected form
		$_SESSION['user_authenticated'] = $form_id;
		
		//set session value to bypass unique checking
		$_SESSION['edit_entry']['form_id']  = $form_id;
		$_SESSION['edit_entry']['entry_id'] = $entry_id;

		$entry_params = array();

		//decrypt any encrypted fields
		if(!empty($_SESSION['mf_encryption_private_key'][$form_id]) && !empty($form_encryption_enable)){
			$entry_params['encryption_private_key'] = $_SESSION['mf_encryption_private_key'][$form_id];
		}

		$form_values = mf_get_entry_values($dbh,$form_id,$entry_id,false,$entry_params);
		
		$form_params = array();
		$form_params['populated_values'] = $form_values;
		$form_params['edit_id']			 = $entry_id;
		$form_params['integration_method'] = 'php';
		$form_params['page_number'] = 0; //display all pages (if any) as a single page
		$form_params['csrf_token']  = $_SESSION['mf_csrf_token'];

		$form_markup = mf_display_form($dbh,$form_id,$form_params);
	}
	

	$header_data =<<<EOT
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css{$mf_version_tag}" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="css/entry_print.css{$mf_version_tag}" media="print">
EOT;

	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post edit_entry">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<?php if($is_incomplete_entry){ ?>
								<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <?php echo "<a id=\"ve_a_entries\" class=\"breadcrumb\" href='manage_entries.php?id={$form_id}'>Entries</a>"; ?> <span id="ve_a_next" class="icon-arrow-right2 breadcrumb_arrow"></span> <?php echo "<a id=\"ve_a_entries\" class=\"breadcrumb\" href='manage_incomplete_entries.php?id={$form_id}'>Incomplete</a>"; ?> <span id="ve_a_next" class="icon-arrow-right2 breadcrumb_arrow"></span> #<?php echo $entry_id; ?></h2>
								<p>Editing incomplete entry #<?php echo $entry_id; ?></p>
							<?php }else{ ?>
								<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <?php echo "<a id=\"ve_a_entries\" class=\"breadcrumb\" href='manage_entries.php?id={$form_id}'>Entries</a>"; ?> <span id="ve_a_next" class="icon-arrow-right2 breadcrumb_arrow"></span> #<?php echo $entry_id; ?></h2>
								<p>Editing entry #<?php echo $entry_id; ?></p>
							<?php } ?>
						</div>	
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>

				<div class="content_body">
					<div id="ve_details" data-csrftoken="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>" data-formid="<?php echo $form_id; ?>" data-entryid="<?php echo $entry_id; ?>" data-incomplete="<?php if($is_incomplete_entry){ echo '1';}else{ echo '0';} ?>">
						<?php echo $form_markup; ?>
					</div>
					<div id="ve_actions">
						<div id="ve_entry_navigation">
							<a href="<?php echo "edit_entry.php?form_id={$form_id}&entry_id={$entry_id}&nav=prev"; ?>" title="Previous Entry" style="margin-left: 1px"><span class="icon-arrow-left"></span></a>
							<a href="<?php echo "edit_entry.php?form_id={$form_id}&entry_id={$entry_id}&nav=next"; ?>" title="Next Entry" style="margin-left: 5px"><span class="icon-arrow-right"></span></a>
						</div>
						<div id="ve_entry_actions" class="gradient_blue">
							<ul>
								<li style="border-bottom: 1px dashed #8EACCF"><a id="ve_action_view" title="View Entry" href="<?php echo "view_entry.php?form_id={$form_id}&entry_id={$entry_id}"; ?>"><span class="icon-search"></span>View</a></li>
								<li><a id="ve_action_delete" title="Delete Entry" href="#"><span class="icon-remove"></span>Delete</a></li>
							</ul>
						</div>
					</div>
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

<div id="dialog-confirm-entry-delete" title="Are you sure you want to delete this entry?" class="buttons" style="display: none">
	<span class="icon-bubble-notification"></span> 
	<p id="dialog-confirm-entry-delete-msg">
		This action cannot be undone.<br/>
		<strong id="dialog-confirm-entry-delete-info">Data and files associated with this entry will be deleted.</strong><br/><br/>
		
	</p>				
</div>

<div id="dialog-email-entry" title="Email entry #<?php echo $entry_id; ?> to:" class="buttons" style="display: none"> 
	<form id="dialog-email-entry-form" class="dialog-form" style="padding-left: 10px;padding-bottom: 10px">	
		<ul>
			<li>
				<div>
					<input type="text" value="" class="text" name="dialog-email-entry-input" id="dialog-email-entry-input" />
				</div> 
				<div class="infomessage" style="padding-top: 5px;padding-bottom: 0px">Use commas to separate email addresses.</div>
			</li>
		</ul>
	</form>
</div>

<div id="dialog-entry-sent" title="Success!" class="buttons" style="display: none">
	<img src="images/icons/62_green_48.png" title="Success" /> 
	<p id="dialog-entry-sent-msg">
			The entry has been sent.
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
<script type="text/javascript" src="js/view_entry.js{$mf_version_tag}"></script>
EOT;

	$disable_jquery_loading = true;
	
	require('includes/footer.php'); 
?>