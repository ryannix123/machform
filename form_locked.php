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
		die("Form ID required.");
	}

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);
	
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

	$query = "select form_name from ".MF_TABLE_PREFIX."forms where form_id=?";
	$params = array($form_id);

	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	if(!empty($row)){		
		if(!empty($row['form_name'])){		
			$form_name = htmlspecialchars($row['form_name']);
		}else{
			$form_name = 'Untitled Form (#'.$form_id.')';
		}	
	}

	//get lock information
	$query = "select 
					A.user_id,
					A.lock_date,
					B.user_fullname 
				from 
					".MF_TABLE_PREFIX."form_locks A left join ".MF_TABLE_PREFIX."users B on A.user_id=B.user_id 
				where 
					A.form_id=?";
	$params = array($form_id);

	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$lock_fullname = $row['user_fullname'];
	$lock_date     = mf_short_relative_date($row['lock_date']); 

	require('includes/header.php');
	
?>


		<div id="content" class="full">
			<div class="post form_locked">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Form Locked for Editing</h2>
							<p>Another user is currently <strong>editing</strong> this form</p>
						</div>	
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>
				<div class="content_body">
					<div id="form_locked_body">
						<img src="images/icons/106_red_48.png" />
						<h3 style="color: #BD3D20;margin-top: 20px;margin-bottom:5px">This form was locked by <?php echo htmlspecialchars($lock_fullname).' '.$lock_date; ?>.</h3>
						<p>If you are certain nobody else is currently editing this form, you may unlock it to continue:</p>
						<p><a href="edit_form.php?id=<?php echo $form_id ?>&unlock=<?php echo time(); ?>" id="unlock_form" style="margin: 30px auto">Unlock Form</a></p>
						<p>Important: Clicking unlock will <strong>discard any unsaved changes</strong> being made by <?php echo htmlspecialchars($lock_fullname); ?>.</p>
					</div>	
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

 
<?php

	require('includes/footer.php'); 
?>