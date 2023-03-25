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
	
	$form_id = (int) trim($_GET['id']);
	
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

	//get total entries and form meta info
	$query = "select count(*) total_entry from `".MF_TABLE_PREFIX."form_{$form_id}` where `status`=1";
	$params = array();
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	$total_active_entry = $row['total_entry'];

	$query = "select count(*) total_entry from `".MF_TABLE_PREFIX."form_{$form_id}` where `status`=2";
	$params = array();
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	$total_incomplete_entry = $row['total_entry'];

	$query = "select count(*) total_entry from `".MF_TABLE_PREFIX."form_{$form_id}` where `status`=0";
	$params = array();
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	$total_deleted_entry = $row['total_entry'];

	$query = "select date_created from `".MF_TABLE_PREFIX."form_{$form_id}` where `status`=1 order by `id` desc limit 1";
	$params = array();
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row)){
		$last_entry_date = mf_relative_date($row['date_created']);
	}else{
		$last_entry_date = '';
	}

	//get form permission/access data
	$query = "SELECT 
					A.user_id,
					A.edit_form,
					A.edit_report,
					A.edit_entries,
					A.view_entries,
					B.user_fullname
				FROM 
					".MF_TABLE_PREFIX."permissions A LEFT JOIN ".MF_TABLE_PREFIX."users B ON A.user_id=B.user_id 
			   WHERE 
			   		A.form_id=? AND B.priv_administer=0 AND B.status = 1
		    ORDER BY 
		    		B.user_fullname ASC";	
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

	//if the user currently logged in is having admin privilege
	//get all admin users and merge them into the permission
	if(!empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$query = "select user_id,user_fullname from ".MF_TABLE_PREFIX."users where priv_administer=1 and status=1 order by user_fullname asc";
		
		$params = array();
		$sth = mf_do_query($query,$params,$dbh);
		while($row = mf_do_fetch_result($sth)){ 
			$permissions_data[$i]['user_id'] 	   = $row['user_id'];
			$permissions_data[$i]['user_fullname'] = htmlspecialchars($row['user_fullname']).'*';
			$permissions_data[$i]['edit_form'] 	   = 1;
			$permissions_data[$i]['edit_report']   = 1;
			$permissions_data[$i]['edit_entries']  = 1;
			$permissions_data[$i]['view_entries']  = 1;

			$i++;
		}
	}

	$perm_style = '';
	if($i >= 15){
		$perm_style =<<<EOT
<style>
	.me_center_div { padding-left: 10px; }
</style>
EOT;
	}

	//get daily total entries
	$data_series_array = array();			
	$params = array();
	
	$query = "SELECT 
					date_format(date(date_created),'%Y/%c/%e') entry_date,
					count(*) total_entry 
				FROM 
					`".MF_TABLE_PREFIX."form_{$form_id}` 
			   WHERE 
			   		`status`=1 AND year(date_created)=year(now()) AND month(date_created)=month(now())
			GROUP BY 
			   		date(date_created) 
			ORDER BY 
					date(date_created) ASC";
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		$data_object = new stdClass();
		$data_object->date 	= 'new Date('.$row['entry_date'].')';
		$data_object->value = $row['total_entry'];

		$data_series_array[] = '{date: new Date("'.$row['entry_date'].'"), value: '.$row['total_entry'].'}';
	}

	$data_series_joined = implode(',', $data_series_array);
	$data_series_json = '['.$data_series_joined.']';


	$disable_jquery_loading = true;

	$header_data =<<<EOT
<link href="js/kendoui/styles/kendo.common.min.css{$mf_version_tag}" rel="stylesheet">
<link href="js/kendoui/styles/kendo.blueopal.min.css{$mf_version_tag}" rel="stylesheet">
    
<link href="js/kendoui/styles/kendo.dataviz.min.css{$mf_version_tag}" rel="stylesheet">
<link href="js/kendoui/styles/kendo.dataviz.blueopal.min.css{$mf_version_tag}" rel="stylesheet">
    
<script src="js/kendoui/js/jquery.min.js{$mf_version_tag}"></script>
<script src="js/kendoui/js/kendo.custom.min.js{$mf_version_tag}"></script>
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
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Form Info</h2>
							<p>Displaying form information, statistic, and user access</p>
						</div>	
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>
				
				<div class="content_body">
					<div class="chart-wrapper">
		        		<div id="chart_preview"></div>
		    		</div>
		    		<script>
		    			function display_line_chart() {

	
							$("#chart_preview").css("width", 960);

							$("#chart_preview").kendoChart({
							    theme: "blueopal",
							    "title": {
							    	visible: true,
							        position: "top",
							        align: "center",
							        text: "Entries This Month"
							    },
							    chartArea: {"background":"","height":260},
							    legend: {
							        visible: true,
							        position: "top" 
							    },
							    seriesDefaults: {
							        type: "area",
							        categoryField: "date",
							        
							        area: { line: { style: "smooth" } },
							        
							        stack: false,
							        labels: {
							            visible: false,
							            template: "",
							            align: "circle",
							            position: "outsideEnd"
							        }
							    },
							   series: [{ color: "", data: <?php echo $data_series_json; ?> }],
							    tooltip: {
							        visible: true,
							        template: "#= value # entries"
							    },
							    valueAxis: {
							        line: {
							            visible: true
							        },
							        minorGridLines: {
							            visible: false
							        },
							        majorGridLines: {
							            visible: true
							        }
							    },
							    categoryAxis: {
							    	
							        line: {
							            visible: true
							        },
							        majorGridLines: {
							            visible: true
							        },
							        minorGridLines: {
							            visible: false
							        }
							    }
							});
						}

						$(function(){
							//load line chart as the default
							display_line_chart();
						});
		    		</script>

					<div id="vu_details" style="padding-top: 20px;width:942px" data-userid="<?php echo $user_id; ?>">
						
						
						
						<table width="100%" cellspacing="0" cellpadding="0" border="0" id="vu_perm_header">
								<tbody>		
									<tr>
								  	    <td>
								  	    	<div class="vu_title">
								  	    		Users Having Access to This Form  <span style="margin: 0 10px">&#8226;</span> <span class="icon-cog" style="vertical-align: middle"></span> <a class="blue_dotted" href="form_access.php?id=<?php echo $form_id; ?>" />manage access</a>
								  	    	</div>
								  	    </td>
								  		<td class="vu_permission_header" width="70px">Edit Form</td>
								  		<td class="vu_permission_header" width="70px">Edit Report</td>
								  		<td class="vu_permission_header" width="70px">Edit Entries</td>
								  		<td class="vu_permission_header" width="70px">View Entries</td>
								  	</tr> 
								</tbody>
						</table>

						<div id="vu_permission_container">
							<table width="100%" cellspacing="0" cellpadding="0" border="0" id="vu_perm_body" style="margin-top: 0px">
								<tbody>		
									<?php
										$i = 2;
										$checkmark_tag = '<div class="me_center_div"><span class="icon-checkmark" style="color: #3661A1"></span></div>';

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
										  	    <td width="70px"><?php if(!empty($value['edit_form'])){ echo $checkmark_tag; }else{ echo '&nbsp;'; }; ?></td>
										  	    <td width="70px"><?php if(!empty($value['edit_report'])){ echo $checkmark_tag; }else{ echo '&nbsp;'; }; ?></td>
										  	    <td width="70px"><?php if(!empty($value['edit_entries'])){ echo $checkmark_tag; }else{ echo '&nbsp;'; }; ?></td>
										  	    <td width="70px"><?php if(!empty($value['view_entries'])){ echo $checkmark_tag; }else{ echo '&nbsp;'; }; ?></td>
										  	</tr>

								  	<?php 
								  			$i++;
								  		} 
								  	?>
								  	
								</tbody>
							</table>
						</div>
						
						<table width="40%" cellspacing="0" cellpadding="0" border="0" id="vu_privileges">
							<tbody>		
								<tr>
							  	    <td>
							  	    	<div class="vu_title">
							  	    		Form Info
							  	    	</div>
							  	    </td>
							  	</tr> 
									<tr class="">
								  	    <td>Created Date: <strong><?php echo $form_created_date; ?></strong></td>
								  	</tr>
								  	<tr class="alt">
								  	    <td>Created By: <strong><?php echo $form_created_by_fullname; ?></strong></td>
								  	</tr>
								  	<tr class="">
								  	    <td>Total Completed Entries: <strong><?php echo $total_active_entry; ?></strong></td>
								  	</tr>
								  	<tr class="alt">
								  	    <td>Total Incomplete Entries: <strong><?php echo $total_incomplete_entry; ?></strong></td>
								  	</tr>
								  	<tr class="">
								  	    <td>Total Deleted Entries: <strong><?php echo $total_deleted_entry; ?></strong></td>
								  	</tr>
							  		<tr class="alt">
								  	    <td>Last Entry: <strong><?php echo $last_entry_date; ?></strong></td>
								  	</tr>

							</tbody>
						</table>
					</div>
					
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

 
<?php
	require('includes/footer.php'); 
?>