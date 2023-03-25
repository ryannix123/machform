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
	require('includes/entry-functions.php');
	require('includes/post-functions.php');
	require('includes/users-functions.php');

	
	$form_id  = (int) trim($_GET['form_id']);
	$entry_id = (int) trim($_GET['entry_id']);
	$nav 	  = trim($_GET['nav'] ?? '');

	if(empty($form_id) || empty($entry_id)){
		die("Invalid Request");
	}

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

		//this page need edit_entries or view_entries permission
		if(empty($user_perms['edit_entries']) && empty($user_perms['view_entries'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to access this page.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}

	if(!empty($_GET['clear_privatekey'])){
		unset($_SESSION['mf_encryption_private_key'][$form_id]);
	}

	//determine the 'incomplete' status of current entry
	$query = "select 
					`status` 
				from 
					`".MF_TABLE_PREFIX."form_{$form_id}` 
			where id=?";
	$params = array($entry_id);

	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$entry_status = $row['status'];
	
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
		$row['form_name'] = mf_trim_max_length($row['form_name'],65);
		
		$form_name = htmlspecialchars($row['form_name']);
	}else{
		die("Error. Unknown form ID.");
	}

	//get log data
	$query 	= "SELECT 
					record_id, 
					date_format(log_time,'%e %b %Y - %r') log_time,
					log_user,
					log_origin,
					log_message
				FROM 
					`".MF_TABLE_PREFIX."form_{$form_id}_log`
			   WHERE 
			   		record_id=? 
			ORDER BY 
					log_id ASC";
	$params = array($entry_id);
	$sth = mf_do_query($query,$params,$dbh);
	
	$i=0;
	while($row = mf_do_fetch_result($sth)){
		$log_data[$i]['log_time'] 	 = htmlspecialchars($row['log_time']);
		$log_data[$i]['log_user'] 	 = htmlspecialchars($row['log_user']);
		$log_data[$i]['log_origin']  = htmlspecialchars($row['log_origin']);
		$log_data[$i]['log_message'] = htmlspecialchars($row['log_message']);
		$i++;
	}

	$header_data =<<<EOT
<link rel="stylesheet" type="text/css" href="css/entry_print.css{$mf_version_tag}" media="print">
<style>
#entries_table tbody tr:hover td{
	cursor: default;
}
</style>
EOT;

	
	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post view_entry view_entry_log">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<?php if($is_incomplete_entry){ ?>
								<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <?php echo "<a id=\"ve_a_entries\" class=\"breadcrumb\" href='manage_entries.php?id={$form_id}'>Entries</a>"; ?> <span id="ve_a_next" class="icon-arrow-right2 breadcrumb_arrow"></span> <?php echo "<a id=\"ve_a_entries\" class=\"breadcrumb\" href='manage_incomplete_entries.php?id={$form_id}'>Incomplete</a>"; ?> <span id="ve_a_next" class="icon-arrow-right2 breadcrumb_arrow"></span> #<?php echo $entry_id; ?></h2>
								<p>Displaying incomplete entry #<?php echo $entry_id; ?></p>
							<?php }else{ ?>
								<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <?php echo "<a id=\"ve_a_entries\" class=\"breadcrumb\" href='manage_entries.php?id={$form_id}'>Entries</a>"; ?> <span id="ve_a_next" class="icon-arrow-right2 breadcrumb_arrow"></span> <?php echo "<a id=\"ve_a_entries\" class=\"breadcrumb\" href='view_entry.php?form_id={$form_id}&entry_id={$entry_id}'>#{$entry_id}</a>"; ?> <span id="ve_a_next" class="icon-arrow-right2 breadcrumb_arrow"></span> Audit Trail</h2>
								<p>Displaying entry #<?php echo $entry_id; ?> activity log</p>
							<?php } ?>

						</div>
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<div class="content_body">

					<div id="ve_details">
						
						<table data-incomplete="0" id="entries_table" width="100%" cellspacing="0" cellpadding="0" border="0">
							<thead>
								<tr>
									<th class="me_number" scope="col">#</th>
									<th scope="col"><div title="Date Created">Time</div></th>
									<th scope="col"><div title="Text 1">Activity</div></th>
									<th scope="col"><div title="Text 2">User</div></th>
									<th scope="col"><div title="Text 2">Origin</div></th>
								</tr>
							</thead>
							<tbody>
								<?php if(empty($log_data)){ ?>
								<tr>
									<td colspan="5"><div style="text-align: center">There's no activity logged for this entry.</div></td>
								</tr>
								<?php }else{ 
									$toggle = false;
									$i=0;
									foreach($log_data as $row_data){
										$i++;

										if($toggle){
											$toggle = false;
											$row_style = 'class="alt"';
										}else{
											$toggle = true;
											$row_style = '';
										}
								?>

								<tr <?php echo $row_style ?>>
									<td class="me_number"><?php echo $i; ?></td>
									<td><div><?php echo $row_data['log_time']; ?></div></td>
									<td><div><?php echo $row_data['log_message']; ?></div></td>
									<td><div><?php echo $row_data['log_user']; ?></div></td>
									<td><div><?php echo $row_data['log_origin']; ?></div></td>
								</tr>
								<?php }} ?>								
							</tbody>
							</table>

					</div>
					<div id="ve_actions">
						<div id="ve_entry_navigation">
							<a href="<?php echo "view_entry_log.php?form_id={$form_id}&entry_id={$entry_id}&nav=prev"; ?>" title="Previous Entry" style="margin-left: 1px"><span class="icon-arrow-left"></span></a>
							<a href="<?php echo "view_entry_log.php?form_id={$form_id}&entry_id={$entry_id}&nav=next"; ?>" title="Next Entry" style="margin-left: 5px"><span class="icon-arrow-right"></span></a>
						</div>
						<div id="ve_entry_actions" class="gradient_blue">
							<ul>
								<li><a id="ve_action_print" title="Print Entry" href="javascript:window.print()"><span class="icon-print"></span>Print</a></li>
								
							</ul>
						</div>
					</div>
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->
 
<?php

	require('includes/footer.php'); 
?>