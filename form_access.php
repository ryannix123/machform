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
	
	$form_id = (int) trim($_REQUEST['id']);
	
	if(empty($form_id)){
		die("Invalid Request");
	}

	
	$dbh = mf_connect_db();
	
	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);
	
	//get form properties
	$query 	= "select 
					form_name,
					form_created_by,
					(select user_fullname from ".MF_TABLE_PREFIX."users where user_id = A.form_created_by) form_created_by_fullname,
					date_format(form_created_date,'%b %e, %Y') form_created_date
			     from 
			     	 ".MF_TABLE_PREFIX."forms A
			    where 
			    	 form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row)){
		$row['form_name'] = mf_trim_max_length($row['form_name'],45);

		$form_name 				  = htmlspecialchars($row['form_name']);
		$form_created_by_fullname = htmlspecialchars($row['form_created_by_fullname']);
		$form_created_date  	  = $row['form_created_date']; 
		$form_created_by  	  	  = $row['form_created_by']; 
	}
	
	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_form permission
		if(empty($user_perms['edit_form'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to access this page.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}
	
	//get current user full name
	$query = "SELECT 
					user_fullname
				FROM 
					".MF_TABLE_PREFIX."users 
			   WHERE 
			   		user_id=? and `status`=1";
	$params = array($_SESSION['mf_user_id']);
			
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$user_fullname 	= $row['user_fullname'];

	//get form permission/access data
	
	//the current user always have full access
	$permissions_data[$i]['user_id'] 	   = $row['user_id'];
	$permissions_data[$i]['user_fullname'] = htmlspecialchars($row['user_fullname']);
	$permissions_data[$i]['edit_form'] 	   = $row['edit_form'];
	$permissions_data[$i]['edit_report']   = $row['edit_report'];
	$permissions_data[$i]['edit_entries']  = $row['edit_entries'];
	$permissions_data[$i]['view_entries']  = $row['view_entries'];

	$query = "SELECT 
					A.user_id,
					A.user_fullname,
					B.edit_form,
					B.edit_report,
					B.edit_entries,
					B.view_entries 
				FROM 
					`".MF_TABLE_PREFIX."users` A 
		   LEFT JOIN 
		   			`".MF_TABLE_PREFIX."permissions` B on (A.user_id=B.user_id and B.form_id=?) 
			   WHERE 
			   		A.`status` = 1 and A.priv_administer=0 
			ORDER BY 
					A.user_fullname ASC";	
	$params = array($form_id);
			
	$sth = mf_do_query($query,$params,$dbh);
	$permissions_data = array();
	$i=0;
	while($row = mf_do_fetch_result($sth)){ 
		$permissions_data[$i]['user_id'] 	   = $row['user_id'];
		$permissions_data[$i]['user_fullname'] = htmlspecialchars($row['user_fullname']);
		$permissions_data[$i]['edit_form'] 	   = $row['edit_form'];
		$permissions_data[$i]['edit_report']   = $row['edit_report'];
		$permissions_data[$i]['edit_entries']  = $row['edit_entries'];
		$permissions_data[$i]['view_entries']  = $row['view_entries'];

		$i++;
	}

	//handle form submission if there is any
	if(!empty($_POST['submit_form'])){
	
		//remove existing permissions from ap_permissions table
		$query = "DELETE FROM `".MF_TABLE_PREFIX."permissions` WHERE form_id = ?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);
		
		//insert records for each user to ap_permissions table
		foreach ($permissions_data as $value) {
			
			$user_id 		  = $value['user_id'];
			$perm_editform 	  = (int) $_POST['perm_editform_'.$user_id];
			$perm_editreport  = (int) $_POST['perm_editreport_'.$user_id];
			$perm_editentries = (int) $_POST['perm_editentries_'.$user_id];
			$perm_viewentries = (int) $_POST['perm_viewentries_'.$user_id];
			
			//if edit entries allowed, then view entries allowed as well
			if(!empty($perm_editentries)){
				$perm_viewentries = 1;
			}
			
			if(!empty($perm_editform) || !empty($perm_editreport) || !empty($perm_editentries) || !empty($perm_viewentries)){
				$params = array(
								$form_id, 
								$user_id, 
								$perm_editform,
								$perm_editreport, 
								$perm_editentries, 
								$perm_viewentries);	

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

		$_SESSION['MF_SUCCESS'] = 'Form access has been saved.';

		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/form_info.php?id={$form_id}");
		exit;
	}

	if($i >= 15){
		$perm_style =<<<EOT
<style>
	.me_center_div { padding-left: 10px; }
</style>
EOT;
	}

	$header_data =<<<EOT
{$perm_style}
EOT;

	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post form_info">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <?php echo "<a class=\"breadcrumb\" href='form_info.php?id={$form_id}'>Form Info</a>"; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Manage Access</h2>
							<p>Add or remove access to form</p>
						</div>	
						<div style="float: right;margin-right: 5px">
								<a href="#" id="button_save_notification" class="bb_button bb_small bb_green">
									<span class="icon-disk" style="margin-right: 5px"></span>Save Settings
								</a>
						</div>
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<div class="content_body">
					<table width="982px" cellspacing="0" cellpadding="0" border="0" id="vu_perm_header">
								<tbody>		
									<tr>
								  	    <td>
								  	    	<div class="vu_title">
								  	    		Users Having Access to This Form 
								  	    	</div>
								  	    </td>
								  		<td class="vu_permission_header" width="70px">Edit Form</td>
								  		<td class="vu_permission_header" width="70px">Edit Report</td>
								  		<td class="vu_permission_header" width="70px">Edit Entries</td>
								  		<td class="vu_permission_header" width="70px">View Entries</td>
								  	</tr> 
								</tbody>
					</table>
					
					<form id="fa_form" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
						<div id="vu_details" style="overflow-x: hidden;overflow-y: auto;max-height: 400px;width:982px;padding-top: 0px">
							<div >
								<table width="100%" cellspacing="0" cellpadding="0" border="0" id="vu_perm_body" style="margin-top: 0px">
									<tbody>		
										<?php
											$i = 2;
											$checkmark_tag = '<div class="me_center_div"><input id="perm_editreport_20333" name="perm_editreport_20333" class="checkbox cb_editreport" value="1" type="checkbox"></div>';

											foreach ($permissions_data as $value) {
												$class_tag = '';
												if($i % 2 == 0){
													$class_tag = 'class="alt"';
												}
											
										?>
												<tr <?php echo $class_tag; ?>>
											  	    <td>
											  	    	<div class="fi_perm_title"><span class="icon-user" style="margin-right: 5px"></span> 
											  	    		<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer'])){ ?>
											  	    		<a class="blue_dotted" href="view_user.php?id=<?php echo $value['user_id'] ?>"><?php echo $value['user_fullname']; ?></a>
											  	    		<?php 
											  	    			}else{ 
											  	    				echo $value['user_fullname'];
											  	    		 	} 
											  	    		?>
											  	    	</div>
											  	    </td>
											  	    <td width="70px"><div class="me_center_div"><input id="perm_editform_<?php echo $value['user_id'] ?>" name="perm_editform_<?php echo $value['user_id'] ?>" class="checkbox cb_editform" value="1" type="checkbox" <?php if(!empty($value['edit_form'])){ echo 'checked="checked"';} ?> ></div></td>
											  	    <td width="70px"><div class="me_center_div"><input id="perm_editreport_<?php echo $value['user_id'] ?>" name="perm_editreport_<?php echo $value['user_id'] ?>" class="checkbox cb_editreport" value="1" type="checkbox" <?php if(!empty($value['edit_report'])){ echo 'checked="checked"';} ?> ></div></td>
											  	    <td width="70px"><div class="me_center_div"><input id="perm_editentries_<?php echo $value['user_id'] ?>" name="perm_editentries_<?php echo $value['user_id'] ?>" class="checkbox cb_editentries" value="1" type="checkbox" <?php if(!empty($value['edit_entries'])){ echo 'checked="checked"';} ?> ></div></td>
											  	    <td width="70px"><div class="me_center_div"><input id="perm_viewentries_<?php echo $value['user_id'] ?>" name="perm_viewentries_<?php echo $value['user_id'] ?>" class="checkbox cb_viewentries" value="1" type="checkbox" <?php if(!empty($value['view_entries'])){ echo 'checked="checked"';} ?> ></div></td>
											  	</tr>

									  	<?php 
									  			$i++;
									  		} 
									  	?>
									  	
									</tbody>
								</table>
							</div>
						</div>
						<input type="hidden" name="id" value="<?php echo $form_id; ?>">
						<input type="hidden" name="submit_form" value="1">
					</form>
					
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

 
<?php

	$footer_data =<<<EOT
<script type="text/javascript" src="js/form_access.js{$mf_version_tag}"></script>
EOT;

	require('includes/footer.php'); 
?>