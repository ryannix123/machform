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
	$mf_properties 	= mf_get_form_properties($dbh,$form_id,array('form_active'));
	
	
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

	$show_integration_success = false;
	if(!empty($_GET['success'])){
		$show_integration_success = true;
	}
	
	//get integration settings
	$query = "SELECT 
					gsheet_spreadsheet_url,
					gsheet_integration_status,
					gsheet_elements,
					gsheet_delay_notification_until_paid,
					gsheet_delay_notification_until_approved  
				FROM 
					".MF_TABLE_PREFIX."integrations WHERE form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$spreadsheet_url = $row['gsheet_spreadsheet_url'];
	$integration_status = $row['gsheet_integration_status'];
	$current_gsheet_columns = explode(',', $row['gsheet_elements']);
	$gsheet_delay_notification_until_paid = $row['gsheet_delay_notification_until_paid'];
	$gsheet_delay_notification_until_approved = $row['gsheet_delay_notification_until_approved'];

	//get all available columns label
	$columns_meta  = mf_get_simple_columns_meta($dbh,$form_id);
	$columns_label = $columns_meta['name_lookup'];
	$columns_type  = $columns_meta['type_lookup'];

	$form_properties = mf_get_form_properties($dbh,$form_id,array('payment_enable_merchant','form_resume_enable','form_approval_enable','payment_merchant_type'));
	
	//if payment enabled, add ap_form_payments columns into $columns_label
	if($form_properties['payment_enable_merchant'] == 1 && $form_properties['payment_merchant_type'] != 'check'){
		$columns_label['payment_amount'] = 'Payment Amount';
		$columns_label['payment_status'] = 'Payment Status';
		$columns_label['payment_id']	 = 'Payment ID';

		$columns_type['payment_amount'] = 'money';
		$columns_type['payment_status'] = 'text';
		$columns_type['payment_id'] 	= 'text';
	}

	if($form_properties['form_approval_enable'] == 1){
		$columns_label['approval_status'] = 'Approval Status';
		$columns_type['approval_status']  = 'approval_status';
	}

	$header_data =<<<EOT
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css{$mf_version_tag}" rel="stylesheet" />
EOT;
	
	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post integrations_settings" data-csrftoken="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>" data-formid="<?php echo $form_id; ?>">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <a class="breadcrumb" href="integration_settings.php?id=<?php echo $form_id; ?>">Integrations</a> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Google Sheets</h2>
							<p>Google Sheets integration settings</p>
						</div>	
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>

				<div class="content_body" style="text-align: center">

					<?php if($show_integration_success === false && !empty($spreadsheet_url)){ ?>
					<div id="gsheets_field_selection" class="gradient_blue">
						<h6 style="border: none;margin-bottom: 0px;padding-bottom: 0px;line-height: 1"><span class="icon-file-spreadsheet2"></span> Spreadsheet URL:</h6>
						<a href="<?php echo $spreadsheet_url; ?>" target="_blank" class="blue_dotted" style="font-weight: bold;font-size: 14px"><?php echo $spreadsheet_url; ?></a>
					
						<h6 style="margin-top: 20px">Select fields to save to Google Sheets: <span class="icon-question helpicon" data-tippy-content="If you leave this empty, all fields will be saved to Google Sheets"></span></h6> 
						<ul id="gsheets_field_selection_list">
							<?php 
								foreach($columns_label as $element_name=>$element_label){
									//don't display signature field
									if($columns_type[$element_name] == 'signature'){
										continue;
									}

									//limit the field title length to 40 characters max
									if(strlen($element_label) > 40){
										$element_label = substr($element_label,0,40).'...';
									}

									$element_label = htmlspecialchars($element_label,ENT_QUOTES);

									if(!empty($current_gsheet_columns)){
										if(in_array($element_name,$current_gsheet_columns)){
											$checked_tag = 'checked="checked"';
										}else{
											$checked_tag = '';
										}
									}
							?>
								<li>
									<input type="checkbox" value="1" <?php echo $checked_tag; ?> class="element checkbox" name="<?php echo $element_name; ?>" id="<?php echo $element_name; ?>">
									<label for="<?php echo $element_name; ?>" title="<?php echo $element_label; ?>" class="choice"><?php echo $element_label; ?></label>
								</li>
							<?php } ?>
						</ul>
						<div id="gsheets_field_selection_apply" style="text-align: left;overflow: auto">
								<?php if($form_properties['payment_enable_merchant'] == 1 && $form_properties['payment_merchant_type'] != 'check'){ ?>
								<div style="margin: 10px 0 10px 0">
									<input type="checkbox" value="1" class="checkbox" id="gsheet_delay_notification_until_paid" name="gsheet_delay_notification_until_paid" <?php if(!empty($gsheet_delay_notification_until_paid)){ echo 'checked="checked"';} ?>>
									<label for="gsheet_delay_notification_until_paid" class="choice">Delay saving to Google Sheets until payment completed</label>
									<span class="icon-question helpicon" data-tippy-content="If enabled, form will only save entries to Google Sheets once payment has been successfully completed. Entries with incomplete payment won't be saved to Google Sheets."></span>								
								</div>
								<?php } ?>

								<?php if($form_properties['form_approval_enable'] == 1){ ?>
								<div style="margin: 10px 0 10px 0">
									<input type="checkbox" value="1" class="checkbox" id="gsheet_delay_notification_until_approved" name="gsheet_delay_notification_until_approved" <?php if(!empty($gsheet_delay_notification_until_approved)){ echo 'checked="checked"';} ?>>
									<label for="gsheet_delay_notification_until_approved" class="choice">Delay saving to Google Sheets until approval status is <strong>APPROVED</strong></label>
									<span class="icon-question helpicon" data-tippy-content="If enabled, form will only save entries to Google Sheets once Approval Status is marked as APPROVED. Entries with DENIED status won't be saved to Google Sheets."></span>
								</div>
								<?php } ?>
								<div style="margin-top: 20px;width: 100%"></div>

								<a href="#" id="button_save_integration" class="bb_button bb_small bb_green" style="float: left">
									<span class="icon-disk" style="margin-right: 5px"></span>Save Settings
								</a>
								<a href="#" id="button_remove_integration" class="bb_button bb_small bb_grey" style="float: right">
									<span class="icon-remove" style="margin-right: 5px"></span>Remove Integration
								</a>
						</div>
					</div>
					<?php } ?>
					
					<?php if($show_integration_success === true && $integration_status == 1 && !empty($spreadsheet_url)){ ?>
					<div id="integration_connect_gsheet_body">
						<span class="icon-checkmark-circle success_icon"></span>
						<h3 class="success_message">Integration Successful!</h3>
						<p>Your form is now connected to your Google Spreadsheet below:</p> 
						<p><a target="_blank" style="font-weight: bold" href="<?php echo $spreadsheet_url; ?>" class="blue_dotted"><?php echo $spreadsheet_url; ?></a></p>
						
						<a href="integration_settings.php?id=<?php echo $form_id; ?>" class="bb_button bb_grey" style="margin-top: 55px"><span class="icon-checkmark"></span> Done</a>
					</div>
					<?php } ?>

				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

		<div id="dialog-confirm-integration-delete" title="Are you sure you want to remove the integration?" class="buttons" style="display: none">
			<span class="icon-bubble-notification"></span>
			<p id="dialog-confirm-integration-delete-msg">
				This will unlink Google Sheets from your form.<br/>
				<strong id="dialog-confirm-integration-delete-info">Your spreadsheet will remain intact but won't receive any new entries.</strong><br/><br/>
			</p>				
		</div>
 
<?php
	$footer_data =<<<EOT
<script type="text/javascript" src="js/popper.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/tippy.index.all.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.core.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.widget.js{$mf_version_tag}{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.tabs.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.mouse.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.sortable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.draggable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.position.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.dialog.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/integration_gsheets.js{$mf_version_tag}"></script>
<style>
.tippy-tooltip{
	font-size: 98%;
}
</style>
EOT;

	require('includes/footer.php'); 
?>