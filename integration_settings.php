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

	//get integrations status		
	$query = "SELECT 
					gsheet_integration_status,
					gsheet_refresh_token,
					gcal_integration_status,
					gcal_refresh_token  
				FROM 
					".MF_TABLE_PREFIX."integrations WHERE form_id=?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	$gsheet_integration_status 	= $row['gsheet_integration_status'] ?? false;
	$gsheet_refresh_token 		= $row['gsheet_refresh_token'] ?? '';
	$gcal_integration_status 	= $row['gcal_integration_status'] ?? false;
	$gcal_refresh_token 		= $row['gcal_refresh_token'] ?? '';
	
	$current_nav_tab = 'manage_forms';
	$jquery_data_code = '';

	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post integrations_settings" data-csrftoken="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>" data-formid="<?php echo $form_id; ?>">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Integrations</h2>
							<p>Connect your forms with third party services</p>
						</div>	
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>

				<div class="content_body">
					<ul id="is_integrations_list">
						<li id="integration_google_sheet">
							<div class="integration_panel gradient_blue">
								<h5>Google Sheets</h5>
								<span class="integration_desc">Save form entries to Google Sheets</span>
								<h1><span class="icon-file-spreadsheet2"></span></h1>
								<div class="integration_panel_options">
									
									<?php if(empty($gsheet_refresh_token)){ ?>
									<div class="integration_enable">
										<a href="integration_connect_gsheets.php?id=<?php echo $form_id; ?>" class="bb_button bb_blue"><span class="icon-link4"></span> Connect</a>
									</div>
									<?php } ?>

									<?php if(!empty($gsheet_refresh_token)){ ?>
									<div id="gsheets_toggle_wrapper" class="togglebtnWrapper integration_toggle">
									  	<input type="checkbox" id="toggle_integration_gsheets" name="toggle_integration_gsheets" <?php if(!empty($gsheet_integration_status)){ echo 'checked="checked"'; } ?>>
									  	<label for="toggle_integration_gsheets" class="togglebtn"><span class="togglebtn__handler"></span></label>
									</div>
									<div class="integration_config">
										<a href="integration_gsheets.php?id=<?php echo $form_id; ?>"><span class="icon-cog2"></span></a>
									</div>
									<?php } ?>

								</div>
							</div>
						</li>
						<li id="integration_google_calendar">
							<div class="integration_panel gradient_blue">
								<h5>Google Calendar</h5>
								<span class="integration_desc">Add events to Google Calendar</span>
								<h1><span class="icon-calendar"></span></h1>
								<div class="integration_panel_options">
									
									<?php if(empty($gcal_refresh_token)){ ?>
									<div class="integration_enable">
										<a href="integration_connect_gcal.php?id=<?php echo $form_id; ?>" class="bb_button bb_blue"><span class="icon-link4"></span> Connect</a>
									</div>
									<?php } ?>

									<?php if(!empty($gcal_refresh_token)){ ?>
									<div id="gcal_toggle_wrapper" class="togglebtnWrapper integration_toggle">
									  	<input type="checkbox" id="toggle_integration_gcal" name="toggle_integration_gcal" <?php if(!empty($gcal_integration_status)){ echo 'checked="checked"'; } ?>>
									  	<label for="toggle_integration_gcal" class="togglebtn"><span class="togglebtn__handler"></span></label>
									</div>
									<div class="integration_config">
										<a href="integration_gcal.php?id=<?php echo $form_id; ?>"><span class="icon-cog2"></span></a>
									</div>
									<?php } ?>

								</div>
							</div>
						</li>
					</ul>
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

 
<?php
	$footer_data =<<<EOT
<script type="text/javascript">
	$(function(){
		{$jquery_data_code}		
    });
</script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.core.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/integration_settings.js{$mf_version_tag}"></script>
EOT;

	require('includes/footer.php'); 
?>