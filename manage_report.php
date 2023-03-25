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

	//set this value to 'false' to turn off all charts
	//useful if you have large amount of charts and need to load the report page faster
	$display_widgets = true;
	
	$form_id = (int) trim($_REQUEST['id']);
	
	$dbh = mf_connect_db();

	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);
	$mf_properties 	= mf_get_form_properties($dbh,$form_id,array('form_active'));
	
	$http_host = parse_url($mf_settings['base_url'], PHP_URL_HOST);

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

		//this page need edit_report permission
		if(empty($user_perms['edit_report'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to edit this report.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}
	
	
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
		$row['form_name'] = mf_trim_max_length($row['form_name'],55);
		$form_name = htmlspecialchars($row['form_name']);		
	}

	//get the list of widgets, put them into  array
	$query = "SELECT 
					access_key,
					chart_id,
					chart_title,
					chart_height,
					chart_type 
				FROM 
					".MF_TABLE_PREFIX."report_elements
				WHERE 
					chart_status = 1 and 
					access_key <> '' and 
					form_id = ?
			ORDER BY 
					chart_position,chart_id desc";
	
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);
	
	$report_widgets_array = array();
	$i=0;
	while($row = mf_do_fetch_result($sth)){
		$report_widgets_array[$i]['chart_id'] 	 = $row['chart_id'];
		$report_widgets_array[$i]['access_key']  = $row['access_key'];

		$chart_type = $row['chart_type'];
		$chart_title = $row['chart_title'];

		$report_widgets_array[$i]['chart_type']  = $row['chart_type'];

		$report_widgets_array[$i]['chart_height'] = (int) $row['chart_height'];
		if($chart_type == 'grid' && !empty($chart_title)){
			$report_widgets_array[$i]['chart_height'] += 30; //if the grid is having title, add 30px to the height
		}


		if(empty($report_widgets_array[$i]['chart_height']) || $report_widgets_array[$i]['chart_height'] > 600){
			//the maximum chart height is 600px for chart and 630px for grid (30px is for grid title)
			if($chart_type == 'grid'){
				$report_widgets_array[$i]['chart_height'] = 630; 
			}else{
				$report_widgets_array[$i]['chart_height'] = 600;	
			}
		}

		$report_widgets_array[$i]['chart_title'] = htmlspecialchars($row['chart_title']);
		if(empty($report_widgets_array[$i]['chart_title'])){
			$report_widgets_array[$i]['chart_title'] = '-Untitled Widget-';
		}

		$i++;
	}

	//get report access key
	$query 	= "select 
					 report_access_key 
			     from 
			     	 ".MF_TABLE_PREFIX."reports 
			    where 
			    	 form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row)){
		$report_access_key  = $row['report_access_key'];
		$report_shared_link = "<a href=\"{$mf_settings['base_url']}report.php?key={$report_access_key}\" target=\"blank\">{$mf_settings['base_url']}report.php?key={$report_access_key}</a>";
	}

			$header_data =<<<EOT
<link type="text/css" href="css/dropui.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css{$mf_version_tag}" rel="stylesheet" />
EOT;

	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full" data-csrftoken="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>">
			<div class="post manage_report">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Report</h2>
							<p>Edit, share and publish your form report</p>
						</div>

						<?php if(!empty($report_widgets_array)){ ?>
						<div style="float: right">
							<div id="mf_share_report">
								<a id="mf_share_report_button" class="button_primary" href="javascript:;" title="Report Actions">
									<span class="icon-cog"></span>
								</a>
							</div>
							<template id="mf_share_report_content">
								<ul id="mf_share_report_list">
									<li id="li_share_report" class="share_report" style="display: <?php if(empty($report_shared_link)){ echo 'block'; }else{ echo 'none'; } ?>"><a id="share_report_link" href="#">Share This Report</a></li>
									<li id="li_unshare_report" class="unshare_report" style="display: <?php if(!empty($report_shared_link)){ echo 'block'; }else{ echo 'none'; } ?>"><a id="unshare_report_link" href="#">Unshare Report</a></li>
									<li class="sort_widgets"><a id="sort_widget_link" href="#">Sort Widgets</a></li>
								</ul>
							</template>
						</div>	
						<?php } ?>

						<div style="float: right;margin-right: 5px">
								<a href="add_widget.php?id=<?php echo $form_id; ?>" class="bb_button bb_small bb_green">
									<span class="icon-chart" style="margin-right: 5px"></span>Add New Widget
								</a>
						</div>
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>
				
				<div class="content_body">
					<div id="mr_report_shared" style="display: <?php if(!empty($report_shared_link)){ echo 'block'; }else{ echo 'none'; } ?>">
						Report shared at &#8674; <span id="mr_report_shared_span"><?php echo $report_shared_link; ?><span>
					</div>
					<?php
						if(!empty($report_widgets_array)){
							$ssl_suffix = mf_get_ssl_suffix();

							//display the sortable widget list
							echo '<ul id="widget_list_sortable" data-formid="'.$form_id.'" style="clear: both;display: none">';
							foreach ($report_widgets_array as $value) {
								echo "<li id=\"widget_{$value['chart_id']}\" class=\"gradient_blue\">[#{$value['chart_id']}] {$value['chart_title']}</li>\n";
							}
							echo '</ul>';
					?>

							<div id="report_sort_pane_apply" style="display: none">
									<input type="button" id="mr_report_sort_pane_submit" value="Save Changes" class="button_text"> <span id="cancel_report_sort_pane_span">or <a href="#" id="report_sort_pane_cancel">Cancel</a></span>
							</div>

					<?php
							//display the widgets
							echo "<ul id=\"mr_report_list\">\n";
							foreach ($report_widgets_array as $value) {
								$widget_url = 'http'.$ssl_suffix.'://'.$http_host.rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/widget.php?key='.$value['access_key'];
					?>

								<li id="li_<?php echo $value['chart_id']; ?>">
									<?php if($display_widgets == true){ ?>
									<iframe 
											id="iframe_widget_<?php echo $value['chart_id']; ?>" 
											height="<?php echo $value['chart_height']; ?>" 
											allowTransparency="true" 
											frameborder="0" 
											scrolling="no" 
											style="width:100%;border:none" 
											src="<?php echo $widget_url; ?>"
											title="<?php echo $value['chart_title']; ?>">
										 <a href="<?php echo $widget_url; ?>" title="<?php echo $value['chart_title']; ?>"><?php echo $value['chart_title']; ?></a>
									</iframe>
									
									<?php if($value['chart_type'] == 'rating'){ ?>
									<script>
										var iframe_<?php echo $value['chart_id']; ?> = document.getElementById("iframe_widget_<?php echo $value['chart_id']; ?>");
    
									    iframe_<?php echo $value['chart_id']; ?>.onload = function(){
									    	iframe_<?php echo $value['chart_id']; ?>.style.height = (iframe_<?php echo $value['chart_id']; ?>.contentWindow.document.body.scrollHeight + 50) + 'px';
    									}
									</script>
									<?php } ?>
									
									<?php 
										}else{
											echo  "<h3>#{$value['chart_id']} {$value['chart_title']}</h3>";
										}
									?>
									
									<div class="report_toolbar">
										<div>
											<span class="chart_idnum">#<?php echo $value['chart_id']; ?></span>
											<a id="widgetedit_<?php echo $value['chart_id']; ?>" href="<?php echo "edit_widget.php?key={$value['access_key']}"; ?>"><span class="icon-pencil" style="margin-right: 5px;font-size: 120%"></span>Edit</a>
											<a id="widgetcode_<?php echo $value['chart_id']; ?>" href="<?php echo "widget_code.php?key={$value['access_key']}"; ?>" style="margin-left: 10px"><span class="icon-paste" style="margin-right: 5px;font-size: 120%"></span>Widget Code</a>
											
											<a id="widgetdelete_<?php echo $value['chart_id']; ?>" href="#" class="delete_icon"><span class="icon-remove" style="margin-right: 5px;font-size: 120%"></span>Delete</a>
											<a id="widgetduplicate_<?php echo $value['chart_id']; ?>" href="#" class="duplicate_icon"><span class="icon-files" style="margin-right: 5px;font-size: 120%"></span>Duplicate</a>
											<a href="<?php echo "widget.php?key={$value['access_key']}"; ?>" target="_blank" class="open_icon"><span class="icon-popout" style="margin-right: 5px;font-size: 120%"></span>Open</a>
										</div>
									</div>
								</li>

					<?php
							}
							echo "</ul>";

						}else{
					?>
					
						<div id="report_manager_empty">
							<img src="images/icons/arrow_up.png" />
							<h2>Report Empty.</h2>
							<h3>Add widgets to your report by clicking the button above.</h3>
						</div>

					<?php } ?>

					<!-- start dialog boxes -->
					<div id="dialog-delete-widget" title="Are you sure you want to delete this widget?" class="buttons" style="display: none">
						<span class="icon-bubble-notification"></span> 
						<p>
							This action cannot be undone.<br/>
							<strong>Only this widget will be deleted. Any related data will remain intact.</strong><br/><br/>
							
						</p>	
					</div>
					<div id="dialog-share-report" title="Are you sure you want to share this report?" class="buttons" style="display: none">
						<span class="icon-bubble-notification"></span>  
						<p>
							<strong>This report will be made public.<br />Anyone who has the link can access. No sign-in required.</strong><br/><br/>
							You can unshare it later at anytime you want.<br /><br />
						</p>	
					</div>
					<div id="dialog-unshare-report" title="Are you sure you want to unshare this report?" class="buttons" style="display: none">
						<span class="icon-bubble-notification"></span> 
						<p>
							<strong>This report will be made private.<br />Nobody else will be able to view it anymore.</strong><br/><br/>
							You can share it again later at anytime you want.<br /><br />
						</p>	
					</div>
					<!-- end dialog boxes -->

				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

 
<?php
	$footer_data =<<<EOT
<script src="js/popper.min.js{$mf_version_tag}"></script>
<script src="js/tippy.index.all.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.core.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.widget.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.tabs.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.mouse.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.sortable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.draggable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.position.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.dialog.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/manage_report.js{$mf_version_tag}"></script>
EOT;

	require('includes/footer.php'); 
?>