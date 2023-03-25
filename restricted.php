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

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);
	
	require('includes/header.php'); 
	
	$deny_message = "You don't have permission to access this page.";
	if(!empty($_SESSION['MF_DENIED'])){
		$deny_message = $_SESSION['MF_DENIED'];
		$_SESSION['MF_DENIED'] = '';
	}
?>


		<div id="content" class="full">
			<div class="post access_denied">
				<div class="content_header">
					&nbsp;
				</div>
				<div class="content_body">
					<div id="access_denied_body">
						<span class="icon-bubble-notification" style="font-size: 60px"></span>
						<h2>Access Denied.</h2>
						<h3><?php echo $deny_message; ?></h3>
					</div>	
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

 
<?php

	require('includes/footer.php'); 
?>