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

	require('includes/entry-functions.php');
	require('includes/users-functions.php');

	ob_clean(); //clean the output buffer

	$form_id = (int) trim($_REQUEST['form_id']);
	$csrf_token = trim($_REQUEST['csrf_token']);

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);

	if(empty($form_id)){
		die("Invalid form ID.");
	}

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);
	$mf_properties = mf_get_form_properties($dbh,$form_id,array('form_active'));
	
	
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

		//this page need at least view_entries permission
		if(empty($user_perms['view_entries']) && empty($user_perms['edit_entries']) && empty($user_perms['edit_form'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to access this page.";
				
			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}

	$export_content = '';

	$query = "SELECT form_name FROM ".MF_TABLE_PREFIX."forms where form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	if(!empty($row)){
		$form_meta_obj = new StdClass();
		$form_meta_obj->form_id   		 = $form_id;
		$form_meta_obj->form_name 		 = $row['form_name'];
		$form_meta_obj->machform_version = $mf_settings['machform_version'];
		$form_meta_obj->export_date 	 = date("Y-m-d H:i:s");

		$form_name = $row['form_name'];
		$clean_form_name = preg_replace("/[^A-Za-z0-9_-]/","",$form_name);

		if(empty($clean_form_name)){
			$clean_form_name = 'UntitledForm';
		}
	}else{
		die("Error. Invalid Form ID.");
	}
	
	$form_meta_json  = json_encode($form_meta_obj);
	if(!empty($form_meta_json)){
		$export_content .= $form_meta_json."\n";
	}
	
	//export ap_form_elements
	$ap_form_elements_json = mf_export_table_rows($dbh,'form_elements',$form_id);
	if(!empty($ap_form_elements_json)){
		$export_content .= $ap_form_elements_json."\n";
	}
	
	//export ap_element_options
	$ap_element_options_json = mf_export_table_rows($dbh,'element_options',$form_id);
	if(!empty($ap_element_options_json)){
		$export_content .= $ap_element_options_json."\n";
	}
	
	//export ap_element_prices
	$ap_element_prices_json = mf_export_table_rows($dbh,'element_prices',$form_id);
	if(!empty($ap_element_prices_json)){
		$export_content .= $ap_element_prices_json."\n";	
	}
	
	//export ap_email_logic
	$ap_email_logic_json = mf_export_table_rows($dbh,'email_logic',$form_id);
	if(!empty($ap_email_logic_json)){
		$export_content .= $ap_email_logic_json."\n";	
	}

	//export ap_email_logic_conditions
	$ap_email_logic_conditions_json = mf_export_table_rows($dbh,'email_logic_conditions',$form_id);
	if(!empty($ap_email_logic_conditions_json)){
		$export_content .= $ap_email_logic_conditions_json."\n";
	}
	
	//export ap_field_logic_conditions
	$ap_field_logic_conditions_json = mf_export_table_rows($dbh,'field_logic_conditions',$form_id);
	if(!empty($ap_field_logic_conditions_json)){
		$export_content .= $ap_field_logic_conditions_json."\n";
	}
	
	//export ap_field_logic_elements
	$ap_field_logic_elements_json = mf_export_table_rows($dbh,'field_logic_elements',$form_id);
	if(!empty($ap_field_logic_elements_json)){
		$export_content .= $ap_field_logic_elements_json."\n";
	}
	
	//export ap_grid_columns
	$ap_grid_columns_json = mf_export_table_rows($dbh,'grid_columns',$form_id);
	if(!empty($ap_grid_columns_json)){
		$export_content .= $ap_grid_columns_json."\n";
	}
	
	//export ap_page_logic
	$ap_page_logic_json = mf_export_table_rows($dbh,'page_logic',$form_id);
	if(!empty($ap_page_logic_json)){
		$export_content .= $ap_page_logic_json."\n";
	}

	//export ap_page_logic_conditions
	$ap_page_logic_conditions_json = mf_export_table_rows($dbh,'page_logic_conditions',$form_id);
	if(!empty($ap_page_logic_conditions_json)){
		$export_content .= $ap_page_logic_conditions_json."\n";
	}

	//export ap_report_elements
	$ap_report_elements_json = mf_export_table_rows($dbh,'report_elements',$form_id);
	if(!empty($ap_report_elements_json)){
		$export_content .= $ap_report_elements_json."\n";
	}

	//export ap_report_filters
	$ap_report_filters_json = mf_export_table_rows($dbh,'report_filters',$form_id);
	if(!empty($ap_report_filters_json)){
		$export_content .= $ap_report_filters_json."\n";
	}

	//export ap_reports
	$ap_reports_json = mf_export_table_rows($dbh,'reports',$form_id);
	if(!empty($ap_reports_json)){
		$export_content .= $ap_reports_json."\n";
	}

	//export ap_webhook_logic_conditions
	$ap_webhook_logic_conditions_json = mf_export_table_rows($dbh,'webhook_logic_conditions',$form_id);
	if(!empty($ap_webhook_logic_conditions_json)){
		$export_content .= $ap_webhook_logic_conditions_json."\n";
	}

	//export ap_webhook_options
	$ap_webhook_options_json = mf_export_table_rows($dbh,'webhook_options',$form_id);
	if(!empty($ap_webhook_options_json)){
		$export_content .= $ap_webhook_options_json."\n";
	}

	//export ap_webhook_parameters
	$ap_webhook_parameters_json = mf_export_table_rows($dbh,'webhook_parameters',$form_id);
	if(!empty($ap_webhook_parameters_json)){
		$export_content .= $ap_webhook_parameters_json."\n";
	}

	//export ap_success_logic_conditions
	$ap_success_logic_conditions_json = mf_export_table_rows($dbh,'success_logic_conditions',$form_id);
	if(!empty($ap_success_logic_conditions_json)){
		$export_content .= $ap_success_logic_conditions_json."\n";
	}

	//export ap_success_logic_options
	$ap_success_logic_options_json = mf_export_table_rows($dbh,'success_logic_options',$form_id);
	if(!empty($ap_success_logic_options_json)){
		$export_content .= $ap_success_logic_options_json."\n";
	}

	//export ap_approval_settings
	$ap_approval_settings_json = mf_export_table_rows($dbh,'approval_settings',$form_id);
	if(!empty($ap_approval_settings_json)){
		$export_content .= $ap_approval_settings_json."\n";
	}

	//export ap_approvers
	$ap_approvers_json = mf_export_table_rows($dbh,'approvers',$form_id);
	if(!empty($ap_approvers_json)){
		$export_content .= $ap_approvers_json."\n";
	}

	//export ap_approvers_conditions
	$ap_approvers_conditions_json = mf_export_table_rows($dbh,'approvers_conditions',$form_id);
	if(!empty($ap_approvers_conditions_json)){
		$export_content .= $ap_approvers_conditions_json."\n";
	}

	//export ap_forms
	//we're exporting ap_forms on the last position for a purpose
	//so that when the form is being imported back, it would guarantee completed form
	$ap_forms_json 	 = mf_export_table_rows($dbh,'forms',$form_id);
	if(!empty($ap_forms_json)){
		$export_content .= $ap_forms_json."\n";
	}

	$export_content = trim($export_content);

	//if zlib compression enabled, prepare the header for gzip
	$zlib_compression = ini_get('zlib.output_compression');
	if(!empty($zlib_compression)){
		header('Content-Encoding: gzip');
	}

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public", false);
	header("Content-Description: File Transfer");
	header("Content-Type: text/plain");
	header("Content-Disposition: attachment; filename=\"{$clean_form_name}-{$form_id}.json\"");
	        
	$output_stream = fopen('php://output', 'w');
	fwrite($output_stream, $export_content);
	fclose($output_stream);
	

	/*********************************************************************************/
	/** Functions **/

	//export table rows into JSON data
	//each record into one line
	function mf_export_table_rows($dbh,$table_name,$form_id){
		
		//get the data
		$complete_table_name = MF_TABLE_PREFIX.$table_name;
		
		$table_meta_obj = new StdClass();
		$table_meta_obj->table_name = $table_name;
		$table_meta_json = json_encode($table_meta_obj);

		//some tables need to be exported using correct orders
		//particularly logic tables
		$order_clause = '';
		switch ($table_name) {
			case 'field_logic_conditions':
				$order_clause = 'ORDER BY alc_id ASC';
				break;
			case 'page_logic_conditions':
				$order_clause = 'ORDER BY apc_id ASC';
				break;
			case 'email_logic_conditions':
				$order_clause = 'ORDER BY aec_id ASC';
				break;
			case 'webhook_options':
				$order_clause = 'ORDER BY awo_id ASC';
				break;
			case 'success_logic_conditions':
				$order_clause = 'ORDER BY slc_id ASC';
				break;
			case 'success_logic_options':
				$order_clause = 'ORDER BY slo_id ASC';
				break;
			case 'approvers_conditions':
				$order_clause = 'ORDER BY aac_id ASC';
				break;
		}

		$query  = "SELECT * FROM `{$complete_table_name}` WHERE form_id = ? {$order_clause}";
		$params = array($form_id);

		$sth = mf_do_query($query,$params,$dbh);
		
		$table_data_json = '';
		$unused_columns = array('aeo_id','aep_id','aec_id','alc_id','agc_id','apc_id','arf_id','wlc_id','awo_id','awp_id','slo_id','slc_id','aac_id',
								'form_encryption_enable','form_encryption_public_key');

		while($row = mf_do_fetch_result($sth)){
			foreach ($row as $column_name => $column_data) {
				if(in_array($column_name, $unused_columns)){
					continue;
				}

				$row_data[$column_name] = $column_data;
			}
			$table_data_json .= json_encode($row_data)."\n";
		}

		$table_data_json = trim($table_data_json);

		if(!empty($table_data_json)){
			$table_data_json = $table_meta_json."\n".$table_data_json;
		}

		return $table_data_json;		
	}

?>