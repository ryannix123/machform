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
	require('lib/dompdf/autoload.inc.php');
	require('lib/libsodium/autoload.php');

	$form_id  = (int) trim($_GET['form_id']);
	$entry_id = (int) trim($_GET['entry_id']);
	
	if(empty($form_id) || empty($entry_id)){
		die("Invalid Request");
	}

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

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
	
	$form_name = '';
	if(!empty($row)){
		$form_name = strip_tags($row['form_name']);
		$form_name = mf_trim_max_length($form_name,90);
		$form_name .= ' '; //add extra space
	}

	$template_data_options = array();
	
	$template_data_options['as_plain_text']		   = false;
    $template_data_options['target_is_admin'] 	   = true;
    $template_data_options['machform_path'] 	   = $mf_settings['base_url'];
    $template_data_options['show_image_preview']   = true;
    $template_data_options['use_list_layout']	   = true;
    $template_data_options['hide_encrypted_data']  = 'asterisk';

    if(!empty($_SESSION['mf_encryption_private_key'][$form_id])){
		$template_data_options['encryption_private_key'] = $_SESSION['mf_encryption_private_key'][$form_id];
	}

    $template_data = mf_get_template_variables($dbh,$form_id,$entry_id,$template_data_options);
		
	$template_variables = $template_data['variables'];
	$template_values    = $template_data['values'];

	$pdf_content = '<html><body><h4>'.$form_name.'#'.$entry_id.'</h4>{entry_data}</body></html>';
	
	//parse pdf template
	$pdf_content = str_replace($template_variables,$template_values,$pdf_content);

	//generate PDF file
	use Dompdf\Dompdf;
	use Dompdf\Options;

	$dompdf_options = new Options();
	$dompdf_options->set('isRemoteEnabled', TRUE);
	$dompdf_options->set('defaultFont','helvetica');
	
	$dompdf = new Dompdf($dompdf_options);
	$dompdf->loadHtml($pdf_content);

	//paper size: letter, legal, ledger, tabloid, executive, folio, a0, a1, a2, a3, a4,a5, a6, etc
	//orientation: portrait, landscape
	$dompdf->setPaper('letter','portrait');
	
	$dompdf->render();
	$dompdf->stream("Entry-{$entry_id}-Form{$form_id}.pdf");

?>