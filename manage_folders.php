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

	$dbh 		 = mf_connect_db();
	
	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);
	
	//get all folders for this user
	$query = "SELECT 
					folder_id,
					folder_name,
					folder_position 
				FROM 
					".MF_TABLE_PREFIX."folders 
			   WHERE 
					user_id=? 
			order by 
					folder_position asc";	
	$params = array($_SESSION['mf_user_id']);
	$sth = mf_do_query($query,$params,$dbh);

	$folder_list_array = array();
	$i=0;
	while($row = mf_do_fetch_result($sth)){
		$folder_list_array[$i]['folder_id']   = $row['folder_id']; 
		$folder_list_array[$i]['folder_name'] = htmlspecialchars($row['folder_name']);
		$i++;
	}

		
	
	$current_nav_tab = 'manage_forms';
	
	require('includes/header.php'); 
	
?>

		<div id="content" class="full">
			<div class="post manage_forms">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><a class="breadcrumb" href='manage_forms.php'>Form Manager</a> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Folders</h2>
							<p>Create, edit and manage your Smart Folders</p>
						</div>
						<div style="float: right;margin-right: 0px">		
								<a href="edit_folder.php" title="Add New Folder" id="button_create_folder" class="button_primary">
									<span class="icon-folder-plus" style="margin-right: 5px"></span>New Folder
								</a>
						</div>
						<div style="clear: both; height: 1px"></div>
					</div>
				</div>
				
				<?php mf_show_message(); ?>
				
				<div class="content_body">
					<ul id="mf_folder_list">
						<?php  
							foreach ($folder_list_array as $folder_data) {
						?>
						<li id="li_<?php echo $folder_data['folder_id']; ?>" data-id="<?php echo $folder_data['folder_id']; ?>">
							<?php if($folder_data['folder_id'] == 1){ ?>
							
							<div class="middle_folder_bar">
								<h3><span class="folder_move_handler icon-move"></span> <?php echo $folder_data['folder_name']; ?></h3>
							</div>
							
							<?php }else{ ?>
							
							<div class="middle_folder_bar">
								<h3><span class="folder_move_handler icon-move"></span> <a href="edit_folder.php?id=<?php echo $folder_data['folder_id']; ?>"><?php echo $folder_data['folder_name']; ?></a></h3>
								<h3 class="delete_folder_h3"><a class="delete_folder_link" data-id="<?php echo $folder_data['folder_id']; ?>" id="deletefolder_<?php echo $folder_data['folder_id']; ?>" href="#"><span class="icon-trash2"></span></a></h3>
							</div>
							
							<?php } ?>
						</li>
						<?php } ?>
					</ul>
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->


 
<?php

	$footer_data =<<<EOT
<script type="text/javascript" src="js/sortable.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/axios.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/sweetalert2.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/folders_manager.js{$mf_version_tag}"></script>
EOT;

	require('includes/footer.php');
?>