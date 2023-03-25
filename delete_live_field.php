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

	defined('MF_STORE_FILES_AS_BLOB') or define('MF_STORE_FILES_AS_BLOB',false);
	
	$form_id 	= (int) trim($_POST['form_id']);
	$element_id = (int) trim($_POST['element_id']);
	$csrf_token = trim($_POST['csrf_token']);

	//validate CSRF token
	mf_verify_csrf_token($csrf_token);

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);
	$mf_properties = mf_get_form_properties($dbh,$form_id,array('form_active'));
	
	
	//check inactive form, inactive form settings should not displayed
	if(empty($mf_properties) || $mf_properties['form_active'] == null){
		die('This is not valid form URL.');
	}else{
		$form_active = (int) $mf_properties['form_active'];
	
		if($form_active !== 0 && $form_active !== 1){
			die('This is not valid form URL.');
		}
	}

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_form permission
		if(empty($user_perms['edit_form'])){
			die("You don't have permission to edit this form.");
		}
	}
	
	//get type of this element
	$query 	= "select 
					 element_type,
					 element_choice_has_other 
				 from 
				 	 `".MF_TABLE_PREFIX."form_elements` 
				where 
					 form_id = ? and 
					 element_id = ?";
	$params = array($form_id,$element_id);
		
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
		
	$element_type 			  = $row['element_type'];
	$element_choice_has_other = $row['element_choice_has_other'];

	//if this is matrix field, first get the element_id of the whole rows into an array
	$matrix_rows_ids = array();
	if('matrix' == $element_type){
		$query = "select element_id from `".MF_TABLE_PREFIX."form_elements` where form_id = ? and element_matrix_parent_id = ? and element_type='matrix'";
		$params = array($form_id,$element_id);
			
		$sth = mf_do_query($query,$params,$dbh);
		while($row = mf_do_fetch_result($sth)){
			$matrix_rows_ids[]  = $row['element_id'];	
		}
		$matrix_rows_ids[] = $element_id; //add the first row id
	}
	
	
	//check if the table for this form is having entries or not
	$table_is_empty = false;

	$query = "select count(*) total_row from `".MF_TABLE_PREFIX."form_{$form_id}` where `status`=1";
	$params = array();
		
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	if(empty($row['total_row'])){
		$table_is_empty = true;
	}

	//delete permanently if true_delete is turned on or the table is still empty (having no entries)	
	if(MF_CONF_TRUE_DELETE == true || $table_is_empty === true){
		
		$params = array();
		
		//delete actual field on respective table data
		if('address' == $element_type){
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` DROP COLUMN `element_{$element_id}_1`,DROP COLUMN `element_{$element_id}_2`, DROP COLUMN `element_{$element_id}_3`, DROP COLUMN `element_{$element_id}_4`, DROP COLUMN `element_{$element_id}_5`, DROP COLUMN `element_{$element_id}_6`;";
			mf_do_query($query,$params,$dbh);
			
		}elseif ('simple_name' == $element_type){
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` DROP COLUMN `element_{$element_id}_1`,DROP COLUMN `element_{$element_id}_2`;";
			mf_do_query($query,$params,$dbh);
			
		}elseif ('simple_name_wmiddle' == $element_type){
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` DROP COLUMN `element_{$element_id}_1`,DROP COLUMN `element_{$element_id}_2`,DROP COLUMN `element_{$element_id}_3`;";
			mf_do_query($query,$params,$dbh);
			
		}elseif ('name' == $element_type){
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` DROP COLUMN `element_{$element_id}_1`,DROP COLUMN `element_{$element_id}_2`, DROP COLUMN `element_{$element_id}_3`, DROP COLUMN `element_{$element_id}_4`;";
			mf_do_query($query,$params,$dbh);
			
		}elseif ('name_wmiddle' == $element_type){
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` DROP COLUMN `element_{$element_id}_1`,DROP COLUMN `element_{$element_id}_2`, DROP COLUMN `element_{$element_id}_3`, DROP COLUMN `element_{$element_id}_4`, DROP COLUMN `element_{$element_id}_5`;";
			mf_do_query($query,$params,$dbh);
			
		}elseif ('checkbox' == $element_type){
			
			//get option_id list
			$query = "select option_id from ".MF_TABLE_PREFIX."element_options where form_id = ? and element_id = ? and live=1";
			$params = array($form_id,$element_id);
			$sth = mf_do_query($query,$params,$dbh);
			
			$option_id_array = array();
			while($row = mf_do_fetch_result($sth)){
				$option_id_array[] = $row['option_id'];
			}
			
			//delete each option
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ";
			foreach ($option_id_array as $option_id){
				$query .= " DROP COLUMN `element_{$element_id}_{$option_id}`,";
			}
			
			$query = rtrim($query,',');
			$params = array();
			mf_do_query($query,$params,$dbh);

			//delete 'other' field
			if(!empty($element_choice_has_other)){
				$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` DROP COLUMN `element_{$element_id}_other`";
				$params = array();
				mf_do_query($query,$params,$dbh);
			}
			
		}elseif('radio' == $element_type){
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` DROP COLUMN `element_{$element_id}`;";
			mf_do_query($query,$params,$dbh);

			//delete 'other' field
			if(!empty($element_choice_has_other)){
				$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` DROP COLUMN `element_{$element_id}_other`";
				$params = array();
				mf_do_query($query,$params,$dbh);
			}

		}elseif('matrix' == $element_type){
			//check if this is checkbox matrix or radio button matrix
			$query = "select element_matrix_allow_multiselect from `".MF_TABLE_PREFIX."form_elements` where form_id = ? and element_id = ?";
			$params = array($form_id,$element_id);
			
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);
			
			if(!empty($row['element_matrix_allow_multiselect'])){
				$matrix_allow_multiselect = true;
			}else{
				$matrix_allow_multiselect = false;
			}
			
			if($matrix_allow_multiselect){ //if this is checkbox matrix
				foreach ($matrix_rows_ids as $m_element_id){
						//get option_id list
						$query = "select option_id from ".MF_TABLE_PREFIX."element_options where form_id = ? and element_id = ? and live=1";
						$params = array($form_id,$m_element_id);
						$sth = mf_do_query($query,$params,$dbh);
						
						$option_id_array = array();
						while($row = mf_do_fetch_result($sth)){
							$option_id_array[] = $row['option_id'];
						}
						
						//delete each option from the form table
						$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` ";
						foreach ($option_id_array as $option_id){
							$query .= " DROP COLUMN `element_{$m_element_id}_{$option_id}`,";
						}
						
						$query = rtrim($query,',');
						$params = array();
						mf_do_query($query,$params,$dbh);
						
						//delete on table ap_form_elements
						$query = "delete from `".MF_TABLE_PREFIX."form_elements` where form_id = ? and element_id = ?";
						$params = array($form_id,$m_element_id);
						mf_do_query($query,$params,$dbh);
						
						//delete on table ap_element_options
						$query = "delete from `".MF_TABLE_PREFIX."element_options` where form_id = ? and element_id = ?";
						$params = array($form_id,$m_element_id);
						mf_do_query($query,$params,$dbh);
				}	
			}else{ //if this is radio button matrix
				
				foreach ($matrix_rows_ids as $m_element_id){
						//delete each option from the form table
						$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` DROP COLUMN `element_{$m_element_id}`";
						mf_do_query($query,array(),$dbh);
						
						//delete on table ap_form_elements
						$query = "delete from `".MF_TABLE_PREFIX."form_elements` where form_id = ? and element_id = ?";
						$params = array($form_id,$m_element_id);
						mf_do_query($query,$params,$dbh);
						
						//delete on table ap_element_options
						$query = "delete from `".MF_TABLE_PREFIX."element_options` where form_id = ? and element_id = ?";
						$params = array($form_id,$m_element_id);
						mf_do_query($query,$params,$dbh);
				}	
			}
			
		}elseif ('section' == $element_type || 'media' == $element_type){
			//do nothing for section break or media field
		}elseif ('file' == $element_type){
			//delete the files first
			if(MF_STORE_FILES_AS_BLOB !== true){
				$query = "select element_{$element_id} from `".MF_TABLE_PREFIX."form_{$form_id}`";
				$sth = mf_do_query($query,$params,$dbh);
				while($row = mf_do_fetch_result($sth)){
					$filename = $row['element_'.$element_id];
					@unlink($mf_settings['upload_dir']."/form_{$form_id}/files/".$filename);
				}
			}else{
				$query = "DELETE FROM ".MF_TABLE_PREFIX."form_{$form_id}_files WHERE file_name LIKE 'element\_{$element_id}\_%';";
				$params = array();
				mf_do_query($query,$params,$dbh);
			}
			
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` DROP COLUMN `element_{$element_id}`;";
			mf_do_query($query,$params,$dbh);
			
		}else{
			$query = "ALTER TABLE `".MF_TABLE_PREFIX."form_{$form_id}` DROP COLUMN `element_{$element_id}`;";
			mf_do_query($query,$params,$dbh);
			
		}
		
		
		//delete on table ap_element_options
		$query = "delete from `".MF_TABLE_PREFIX."element_options` where form_id = ? and element_id = ?";
		$params = array($form_id,$element_id);
		mf_do_query($query,$params,$dbh);
		
		//delete on table ap_form_elements
		$query = "delete from `".MF_TABLE_PREFIX."form_elements` where form_id = ? and element_id = ?";
		$params = array($form_id,$element_id);
		mf_do_query($query,$params,$dbh);
		
	}else{
		//set the status on ap_form_elements table to 0
		$query = "update `".MF_TABLE_PREFIX."form_elements` set element_status = 0 where form_id = ? and element_id = ?";
		$params = array($form_id,$element_id);
		mf_do_query($query,$params,$dbh);
		
		//set the status on ap_element_options table to 0
		$query = "update `".MF_TABLE_PREFIX."element_options` set `live` = 0 where form_id = ? and element_id = ?";
		$params = array($form_id,$element_id);
		mf_do_query($query,$params,$dbh);
		
		//if this is matrix, we need to disable all child rows as well
		if('matrix' == $element_type){
			//check if this is checkbox matrix or radio button matrix
			$query = "select element_matrix_allow_multiselect from `".MF_TABLE_PREFIX."form_elements` where form_id = ? and element_id = ?";
			$params = array($form_id,$element_id);
			
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);
			
			if(!empty($row['element_matrix_allow_multiselect'])){
				$matrix_allow_multiselect = true;
			}else{
				$matrix_allow_multiselect = false;
			}
			
			//get all rows id
			$matrix_rows_ids = array();
			
			$query = "select element_id from `".MF_TABLE_PREFIX."form_elements` where form_id = ? and element_matrix_parent_id = ? and element_type='matrix'";
			$params = array($form_id,$element_id);
			
			$sth = mf_do_query($query,$params,$dbh);
			while($row = mf_do_fetch_result($sth)){
				$matrix_rows_ids[]  = $row['element_id'];	
			}
			$matrix_rows_ids[] = $element_id; //add the first row id
			
			if($matrix_allow_multiselect){ //if this is checkbox matrix
				foreach ($matrix_rows_ids as $m_element_id){
						//get option_id list
						$query = "select option_id from ".MF_TABLE_PREFIX."element_options where form_id = ? and element_id = ? and live=1";
						$params = array($form_id,$m_element_id);
						$sth = mf_do_query($query,$params,$dbh);
						
						$option_id_array = array();
						while($row = mf_do_fetch_result($sth)){
							$option_id_array[] = $row['option_id'];
						}
						
						//delete on table ap_form_elements
						$query = "update `".MF_TABLE_PREFIX."form_elements` set element_status=0 where form_id = ? and element_id = ?";
						$params = array($form_id,$m_element_id);
						mf_do_query($query,$params,$dbh);
						
						//delete on table ap_element_options
						$query = "update `".MF_TABLE_PREFIX."element_options` set live=0 where form_id = ? and element_id = ?";
						$params = array($form_id,$m_element_id);
						mf_do_query($query,$params,$dbh);
				}	
			}else{ //if this is radio button matrix
				
				foreach ($matrix_rows_ids as $m_element_id){
						
						//delete on table ap_form_elements
						$query = "update `".MF_TABLE_PREFIX."form_elements` set element_status=0 where form_id = ? and element_id = ?";
						$params = array($form_id,$m_element_id);
						mf_do_query($query,$params,$dbh);
						
						//delete on table ap_element_options
						$query = "update `".MF_TABLE_PREFIX."element_options` set live=0 where form_id = ? and element_id = ?";
						$params = array($form_id,$m_element_id);
						mf_do_query($query,$params,$dbh);
				}	
			}
		}
	}	
	
	//delete the records on ap_column_preferences, regardless of the MF_CONF_TRUE_DELETE value
	//if the field is matrix field, we need to delete the childs as well
	if('matrix' == $element_type){
		foreach ($matrix_rows_ids as $m_element_id) {
			$query = "delete from ".MF_TABLE_PREFIX."column_preferences where form_id = ? and (element_name = ? or element_name like ?)";
			$params = array($form_id, 'element_'.$m_element_id,'element\_'.$m_element_id.'\_%');
			mf_do_query($query,$params,$dbh);
		}
	}else{
		$query = "delete from ".MF_TABLE_PREFIX."column_preferences where form_id = ? and (element_name = ? or element_name like ?)";
		$params = array($form_id, 'element_'.$element_id,'element\_'.$element_id.'\_%');
		mf_do_query($query,$params,$dbh);
	}

	//delete the records on ap_element_prices, regardless of the MF_CONF_TRUE_DELETE
	$query = "delete from ".MF_TABLE_PREFIX."element_prices where form_id = ? and element_id = ?";
	$params = array($form_id,$element_id);
	mf_do_query($query,$params,$dbh);

	//if there is no price fields available, make sure to set the value of payment_enable_merchant on ap_forms table to 0
	//only do this when the payment price is being set to variable

	$query = "select count(*) total_price_fields from ".MF_TABLE_PREFIX."element_prices where form_id=?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	if(empty($row['total_price_fields'])){
		$query = "update ".MF_TABLE_PREFIX."forms set payment_enable_merchant='0' where form_id=? and payment_price_type = 'variable'";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);
	}

	//start processing field logic tables ---------
	//delete the records on ap_field_logic_elements, regardless of the MF_CONF_TRUE_DELETE value
	$query = "delete from ".MF_TABLE_PREFIX."field_logic_elements where form_id = ? and element_id = ?";
	$params = array($form_id,$element_id);
	mf_do_query($query,$params,$dbh);

	//delete the records on ap_field_logic_conditions, regardless of the MF_CONF_TRUE_DELETE value
	$query = "delete from ".MF_TABLE_PREFIX."field_logic_conditions where form_id = ? and target_element_id = ?";
	$params = array($form_id,$element_id);
	mf_do_query($query,$params,$dbh);

	$query = "delete from ".MF_TABLE_PREFIX."field_logic_conditions where form_id = ? and (element_name = 'element\_{$element_id}' or element_name like 'element\_{$element_id}\_%')";
	$params = array($form_id);
	mf_do_query($query,$params,$dbh);

	//if the field is matrix field, we need to delete the childs as well
	if('matrix' == $element_type){
		foreach ($matrix_rows_ids as $m_element_id) {
			$query = "delete from ".MF_TABLE_PREFIX."field_logic_conditions where form_id = ? and (element_name = ? or element_name like ?)";
			$params = array($form_id, 'element\_'.$m_element_id,'element\_'.$m_element_id.'\_%');
			mf_do_query($query,$params,$dbh);
		}
	}

	//check to make sure each record on ap_field_logic_elements has an associated value within ap_field_logic_conditions
	//if there is no associated value, delete the record
	$query = "SELECT 
					C.element_id 
				FROM (
						SELECT 
							  A.element_id,
							  (select count(B.target_element_id) from ".MF_TABLE_PREFIX."field_logic_conditions B where B.form_id=A.form_id and B.target_element_id=A.element_id) total_conditions 
						  FROM 
							  ".MF_TABLE_PREFIX."field_logic_elements A 
						 WHERE 
							  A.form_id = ?
					) C
			   WHERE 
			   		C.total_conditions < 1";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);

	$no_conditions_element_id = array();
	while($row = mf_do_fetch_result($sth)){
		$no_conditions_element_id[] = $row['element_id'];
	}

	if(!empty($no_conditions_element_id)){
		$no_conditions_element_id_joined = implode("','", $no_conditions_element_id);

		$query = "delete from `".MF_TABLE_PREFIX."field_logic_elements` where form_id = ? and `element_id` in('{$no_conditions_element_id_joined}')";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);
	}

	//if there is no logic enables fields anymore on ap_field_logic_elements, we need to set the value of logic_field_enable on ap_forms table to 0
	$query = "select count(*) total_logic_fields from ".MF_TABLE_PREFIX."field_logic_elements where form_id=?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	if(empty($row['total_logic_fields'])){
		$query = "update ".MF_TABLE_PREFIX."forms set logic_field_enable='0' where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);
	}
	//end processing field logic tables ---------

	//start processing page logic tables ---------
	//delete the records on ap_page_logic_conditions, regardless of the MF_CONF_TRUE_DELETE value
	$query = "delete from ".MF_TABLE_PREFIX."page_logic_conditions where form_id = ? and (element_name = 'element\_{$element_id}' or element_name like 'element\_{$element_id}\_%')";
	$params = array($form_id);
	mf_do_query($query,$params,$dbh);

	//if the field is matrix field, we need to delete the childs as well
	if('matrix' == $element_type){
		foreach ($matrix_rows_ids as $m_element_id) {
			$query = "delete from ".MF_TABLE_PREFIX."page_logic_conditions where form_id = ? and (element_name = ? or element_name like ?)";
			$params = array($form_id, 'element\_'.$m_element_id,'element\_'.$m_element_id.'\_%');
			mf_do_query($query,$params,$dbh);
		}
	}

	//check to make sure each record on ap_page_logic has an associated value within ap_page_logic_conditions
	//if there is no associated value, delete the record
	$query = "SELECT 
					C.page_id 
				FROM (
						SELECT 
							  A.page_id,
							  (select count(B.target_page_id) from ".MF_TABLE_PREFIX."page_logic_conditions B where B.form_id=A.form_id and B.target_page_id=A.page_id) total_conditions 
						  FROM 
							  ".MF_TABLE_PREFIX."page_logic A 
						 WHERE 
							  A.form_id = ?
					) C
			   WHERE 
			   		C.total_conditions < 1";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);

	$no_conditions_page_id = array();
	while($row = mf_do_fetch_result($sth)){
		$no_conditions_page_id[] = $row['page_id'];
	}

	if(!empty($no_conditions_page_id)){
		$no_conditions_page_id_joined = implode("','", $no_conditions_page_id);

		$query = "delete from `".MF_TABLE_PREFIX."page_logic` where form_id = ? and `page_id` in('{$no_conditions_page_id_joined}')";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);
	}

	//if there is no logic-enabled-page anymore on ap_page_logic, we need to set the value of logic_page_enable on ap_forms table to 0
	$query = "select count(*) total_logic_pages from ".MF_TABLE_PREFIX."page_logic where form_id=?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	if(empty($row['total_logic_pages'])){
		$query = "update ".MF_TABLE_PREFIX."forms set logic_page_enable='0' where form_id=?";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);
	}
	//end processing page logic tables ---------

	//delete records on ap_entries_preferences
	$query = "delete from ".MF_TABLE_PREFIX."entries_preferences where form_id=?";
	$params = array($form_id);
	mf_do_query($query,$params,$dbh);

	//delete records on ap_form_filters
	$query = "delete from ".MF_TABLE_PREFIX."form_filters where form_id=?";
	$params = array($form_id);
	mf_do_query($query,$params,$dbh);

	//update record on ap_forms table, make sure to disable discount if this field is coupon code field
	$query = "UPDATE 
					".MF_TABLE_PREFIX."forms 
				 SET 
				 	payment_enable_discount=0,
				 	payment_discount_element_id=NULL 
			   WHERE 
			   		form_id = ? and 
			   		payment_enable_discount = 1 and 
			   		payment_discount_element_id = ?";
	$params = array($form_id,$element_id);
	mf_do_query($query,$params,$dbh);
		
	$response_data = new stdClass();
	
	$response_data->status    	= "ok";
	$response_data->element_id 	= $element_id;
	
	$response_json = json_encode($response_data);
	
	echo $response_json;
?>