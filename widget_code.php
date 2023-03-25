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

	$access_key = trim($_GET['key']);
	$form_id 	= (int) substr($access_key, 0, strpos($access_key, 'x'));
	
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
		if(empty($user_perms['edit_report'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to edit this form.";

			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}
	
	//get form properties
	$query 	= "select 
					 form_name
			     from 
			     	 ".MF_TABLE_PREFIX."forms 
			    where 
			    	 form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	//trim form name to maximum 50 characters
	$row['form_name'] = mf_trim_max_length($row['form_name'],50);
	$form_name 		  = htmlspecialchars($row['form_name']);

	//get widget properties
	$query 	= "select 
					chart_title,
					chart_height,
					chart_id,
					chart_type
			    from 
			     	 ".MF_TABLE_PREFIX."report_elements 
			    where 
			    	 access_key = ? and chart_status = 1";
	$params = array($access_key);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row)){
		$chart_title 		= htmlspecialchars($row['chart_title']);
		$chart_frame_height = (int) $row['chart_height'];
		$chart_id 			= (int) $row['chart_id'];
		$chart_type			= $row['chart_type'];

		//specific for grid, if there is chart title, add 30px to the height
		if($chart_type == 'grid' && !empty($chart_title)){
			$chart_frame_height += 30;
		}

		if(empty($chart_title)){
			$chart_title = 'View Widget';
		}
	}else{
		die("Error. Invalid key.");
	}

	$http_host = parse_url($mf_settings['base_url'], PHP_URL_HOST);
	$ssl_suffix = mf_get_ssl_suffix();
	
	$widget_embed_url 	= 'http'.$ssl_suffix.'://'.$http_host.rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/widget.php?key='.$access_key;
	
	//construct iframe code
	$iframe_widget_code = '<iframe id="mf_'.$access_key.'" height="'.$chart_frame_height.'" allowTransparency="true" frameborder="0" scrolling="no" style="width:100%;border:none" src="'.$widget_embed_url.'" title="'.$chart_title.'"><a href="'.$widget_embed_url.'" title="'.$chart_title.'">'.$chart_title.'</a></iframe>';	
	
	//if chart is rating field, add the javascript code to auto adjust the iframe height
	if($chart_type == 'rating'){
		
		$widget_js_auto_height =<<<EOT
<script type="text/javascript">window.addEventListener('message', function(e) {if(e.data.mf_widget != undefined && document.querySelector("#mf_" + e.data.widget_key) != undefined){ document.querySelector("#mf_" + e.data.widget_key).style.height = e.data.height + 'px';}},false);</script>
EOT;

		$iframe_widget_code .= "\n".$widget_js_auto_height;
	}

	//construct simple link code
	$simple_link_widget_code = '<a href="'.$widget_embed_url.'" title="'.$chart_title.'">'.$chart_title.'</a>';

	//construct popup link code
	if($chart_frame_height > 750){
		$popup_height = 750;
	}else{
		$popup_height = $chart_frame_height;
	}
	$popup_link_widget_code = '<a href="'.$widget_embed_url.'" onclick="window.open(this.href,  null, \'height='.$popup_height.', width=800, toolbar=0, location=0, status=0, scrollbars=1, resizable=1\'); return false;">'.$chart_title.'</a>';


	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post embed_code">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <a class="breadcrumb" href="manage_report.php?id=<?php echo $form_id; ?>">Report</a> <span class="icon-arrow-right2 breadcrumb_arrow"></span> <?php echo 'Widget #'.$chart_id; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Code </h2>
							<p>Integrate the widget into your website page by using the code provided below</p>
						</div>	
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>
				<div class="content_body">
					<div id="ec_main_code" class="gradient_blue" style="height: auto">
						<div id="ec_main_code_meta">
							<span class="icon-paste" style="font-size: 65px;display:block;margin-top:35px"></span>
							<h5>Iframe Code</h5>
						</div>
						<div id="ec_main_code_content" style="height: auto">
							<div id="ec_code_iframe">
								<label class="choice" for="ec_iframe">Copy and Paste the Code Below into Your Website Page</label>
								<textarea readonly="readonly" onclick="javascript: this.select()" id="ec_iframe" class="element textarea medium ec_code_data"><?php echo $iframe_widget_code; ?></textarea>
							</div>
							<div id="ec_code_simple_link" style="display: none">
								<label class="choice" for="ec_iframe">Copy and Paste the Code Below into Your Website Page</label>
								<textarea readonly="readonly" onclick="javascript: this.select()" id="ec_simple_link" class="element textarea medium ec_code_data"><?php echo $simple_link_widget_code; ?></textarea>
							</div>
							<div id="ec_code_popup_link" style="display: none">
								<label class="choice" for="ec_iframe">Copy and Paste the Code Below into Your Website Page</label>
								<textarea readonly="readonly" onclick="javascript: this.select()" id="ec_popup_link" class="element textarea medium ec_code_data"><?php echo $popup_link_widget_code; ?></textarea>
							</div>
							<div class="view_widget_div">
								<a href="<?php echo $widget_embed_url; ?>" class="blue_dotted" target="_blank">View Widget</a>
							</div>
						</div>
					</div>
					<div id="ec_meta" style="height: 218px">
						<div id="ec_information" class="gradient_green">
							<span class="icon-info helpicon"></span>  
							<span id="ec_info_iframe">This code will insert the widget into your existing web page seamlessly. <?php if($chart_type != 'rating'){ echo 'You might also need to adjust the iframe height value.'; }; ?></span>
							<span id="ec_info_simple_link" style="display:none">This code will display direct link to your widget. Use this code to share your widget with others through emails or web pages.</span>
							<span id="ec_info_popup_link" style="display:none">This code will display your widget into a popup window.</span>
						</div>
						<div id="ec_options" class="gradient_blue">
							<label for="ec_code_type" class="description">Widget Code Type</label>
							<select class="element select medium" id="ec_code_type" name="ec_code_type" style="width: 210px"> 
								<option value="iframe">Iframe Code</option>
								<option value="simple_link">Simple Link</option>
								<option value="popup_link">Popup Link</option>	
							</select>
						</div>
					</div>
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

 
<?php

	$footer_data =<<<EOT
<script type="text/javascript" src="js/widget_code.js{$mf_version_tag}"></script>
EOT;

	require('includes/footer.php'); 
?>