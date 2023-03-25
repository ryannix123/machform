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
	require('includes/entry-functions.php');
	require('includes/report-functions.php');

	$access_key = trim($_GET['key']);

	if(empty($access_key)){
		die("This widget is not available (missing access key).");
	}

	$dbh = mf_connect_db();

	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);
	
	//check the validity of the access key and get the chart property
	$query = "SELECT 
					chart_id,
					chart_type,
					chart_theme,
					chart_title,
					chart_title_align
			    FROM
			    	".MF_TABLE_PREFIX."report_elements
			   WHERE
			   		access_key = ? and chart_status = 1";
	$params = array($access_key);
		
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	if(!empty($row['chart_id'])){
		$chart_type  = $row['chart_type'];
		$chart_title = htmlspecialchars($row['chart_title'],ENT_QUOTES);
		$chart_title_align = $row['chart_title_align'];
		$chart_theme = strtolower($row['chart_theme']);

		if(empty($chart_title)){
			$chart_title = 'Widget';
		}
	}else{
		die("This widget is no longer available (invalid access key).");
	}

	$body_class = '';

	if($chart_type == 'grid'){
		$widget_markup = mf_display_grid($dbh,$access_key);
	}else if($chart_type == 'rating'){
		if($chart_theme == 'rating-dark'){
			$body_class = 'class="rating-dark"';
		}else{
			$body_class = 'class="rating-light"';
		}

		$widget_markup = mf_display_rating_scorecard($dbh,$access_key);
	}else{
		$widget_markup = mf_display_chart($dbh,$access_key);
	}


?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $chart_title; ?></title>
    <meta charset="utf-8">
    
    <?php if($chart_type == 'rating'){ ?>
    	<link href="css/rating_widget.css?<?php echo $mf_version_tag; ?>" rel="stylesheet">
    	<link href="css/icon-fonts.css?<?php echo $mf_version_tag; ?>" rel="stylesheet">
    <?php } ?>

    <?php if($chart_type != 'rating'){ ?>
	    <style>
			html{
				 font:75% Arial, Helvetica, sans-serif;
			}
	    </style>
	   	
	   	<link href="js/kendoui/styles/kendo.common.min.css<?php echo $mf_version_tag; ?>" rel="stylesheet">
	   	<link href="js/kendoui/styles/kendo.default.min.css<?php echo $mf_version_tag; ?>" rel="stylesheet">
	   	<link href="js/kendoui/styles/kendo.default.mobile.min.css<?php echo $mf_version_tag; ?>" rel="stylesheet">
	    <link href="js/kendoui/styles/kendo.<?php echo $chart_theme; ?>.min.css<?php echo $mf_version_tag; ?>" rel="stylesheet">
	    
	    <link href="js/kendoui/styles/kendo.dataviz.min.css<?php echo $mf_version_tag; ?>" rel="stylesheet">
	  	<link href="js/kendoui/styles/kendo.dataviz.<?php echo $chart_theme; ?>.min.css<?php echo $mf_version_tag; ?>" rel="stylesheet">
	    
	    <script src="js/kendoui/js/jquery.min.js<?php echo $mf_version_tag; ?>"></script>
	    <script src="js/kendoui/js/kendo.custom.min.js<?php echo $mf_version_tag; ?>"></script>
	<?php } ?>
    
    <?php 
    	if($chart_type == 'grid'){ 
    		if(in_array($chart_theme, array('black','highcontrast','metroblack','moonlight'))){
    			$entry_link_color = '#ffffff';
    			$entry_link_border_bottom = '#ffffff';
    		}else{
    			$entry_link_color = '#3661A1';
    			$entry_link_border_bottom = '#000000';
    		}
    ?>
    <style>
		html {
		    font: 75% 'Lucida Sans Unicode','Lucida Grande',Arial,helvetica,sans-serif;
		}
		.me_center_div {
		    text-align: center !important;
		    width: 100%;
		}
		.me_right_div {
		    text-align: right !important;
		    width: 100%;
		}
		.me_file_div{
			background-image: url('images/icons/185.png');
			background-repeat: no-repeat;
			background-position: 0 2px;
			padding-left: 15px;
		}
		.entry_link{
			border-bottom: 1px dotted <?php echo $entry_link_border_bottom; ?>;
		    color: <?php echo $entry_link_color; ?> !important;
		    text-decoration: none !important;
		}
		.mf_grid_title{
			line-height: 30px;
			margin: 0px;
			font-size: 18px;
			font-family: Helvetica, Arial, sans-serif; 
			font-weight: 400;
			text-align: <?php echo $chart_title_align; ?>;
		}
	</style>
	<?php } ?>

</head>
<body style="margin: 0px" <?php echo $body_class; ?>>
    <?php echo $widget_markup; ?>
    
    <?php if($chart_type == 'rating'){  ?>
    <script type="text/javascript">
    	var send_widget_height = function(event){
    		let message = { 
    						height: (document.body.scrollHeight + 50),
    						mf_widget: true, 
    						widget_key: '<?php echo $access_key; ?>'
    					   };	
			window.top.postMessage(message, "*");
    	}
		
		window.addEventListener('resize', send_widget_height, false);
		window.addEventListener('load', send_widget_height, false);
    </script>
	<?php } ?>

</body>
</html>