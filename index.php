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
	
	use adLDAP\adLDAP,adLDAP\adLDAPException;

	require('lib/password-hash.php');
	require('lib/adLDAP/adLDAP.php');

	$ssl_suffix = mf_get_ssl_suffix();

	$dbh = mf_connect_db();
	
	//immediately redirect to installer page if the config values are correct but no ap_forms table found
	$query  = "select count(*) from ".MF_TABLE_PREFIX."settings";
	$params = array();

	$sth = $dbh->prepare($query);
	try{
		$sth->execute($params);
	}catch(PDOException $e) {
		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/installer.php");
		exit;
	}
	
	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);
	
	$allow_login = false;
	$username = '';
	$ldap_error_message = '';
	
	//check for ip address restriction, if enabled, compare the ip address
	if(!empty($mf_settings['enable_ip_restriction'])){
		$allow_login = mf_is_whitelisted_ip_address($dbh,$_SERVER['REMOTE_ADDR']);

		if($allow_login === false){
			$_SESSION['MF_LOGIN_ERROR'] = '<br/>- Forbidden -<br/><br/>Your IP address ('.$_SERVER['REMOTE_ADDR'].') <br/>is not allowed to access this page.<br/><br/>';
		}
	}else{
		$allow_login = true;
	}


	//process login submission
	if($allow_login){
		//check if the user has "remember me" cookie or not
		if(!empty($_COOKIE['mf_remember']) && empty($_SESSION['mf_logged_in'])){
			$query  = "SELECT 
							`user_id`,
							`user_admin_theme`,
							`priv_administer`,
							`priv_new_forms`,
							`priv_new_themes` 
						FROM 
							`".MF_TABLE_PREFIX."users` 
						WHERE 
							`cookie_hash`=? and `status`=1";
			$params = array($_COOKIE['mf_remember']);
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);

			$user_id 			  = $row['user_id'];
			$user_admin_theme 	  = $row['user_admin_theme'];
			$priv_administer	  = (int) $row['priv_administer'];
			$priv_new_forms		  = (int) $row['priv_new_forms'];
			$priv_new_themes	  = (int) $row['priv_new_themes'];

			if(!empty($user_id)){
				$_SESSION['mf_logged_in'] 		 = true;
				$_SESSION['mf_user_id']   		 = $user_id;
				$_SESSION['mf_user_admin_theme'] = $user_admin_theme;
				$_SESSION['mf_user_privileges']['priv_administer'] = $priv_administer;
				$_SESSION['mf_user_privileges']['priv_new_forms']  = $priv_new_forms;
				$_SESSION['mf_user_privileges']['priv_new_themes'] = $priv_new_themes;

				//generate CSRF token
				$_SESSION['mf_csrf_token'] = bin2hex(random_bytes(24));
			}
		}
		
		//redirect to form manager if already logged-in
		if(!empty($_SESSION['mf_logged_in']) && $_SESSION['mf_logged_in'] == true){
			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/manage_forms.php");
			exit;
		}
		
		if(!empty($_POST['submit'])){
			//run cron jobs each time user logged in
			mf_run_cron_jobs($dbh,true);

			//validate CSRF token
			$csrf_token_is_valid = false;
			if(!empty($_SESSION['mf_csrf_token'])){
				$received_csrf_token = $_POST['csrf_token'] ?? '';

				if(hash_equals($_SESSION['mf_csrf_token'], $received_csrf_token)) {
  					$csrf_token_is_valid = true;
				} 
			}
			

			$username 	 = strtolower(trim($_POST['admin_username']));
			$password 	 = trim($_POST['admin_password']);
			$remember_me = isset($_POST['admin_remember']) ? (int) $_POST['admin_remember'] : '';

			$hasher = new PasswordHash(8, FALSE);

			if(empty($username) || empty($password)){
				$_SESSION['MF_LOGIN_ERROR'] = 'Incorrect email or password!';
			}else if($csrf_token_is_valid === false){
				$_SESSION['MF_LOGIN_ERROR'] = 'Invalid CSRF Token. Please re-submit.';
			}else{
				$auth_result 	= false;
				$run_local_auth = true; //the default is to run local authentication against MachForm user database

				//if LDAP authentication enabled
				if(!empty($mf_settings['ldap_enable'])){
					
					//don't run local authentication if LDAP Exclusive is being set to 1
					if(!empty($mf_settings['ldap_exclusive'])){
						$run_local_auth = false;
					}
					
					$ldap_login_verified 	  = false;
					$domain_controllers_array = explode(',', $mf_settings['ldap_host']);
					
					$ldap_use_tls = false;
					$ldap_use_ssl = false;
					if($mf_settings['ldap_encryption'] == 'ssl'){
						$ldap_use_ssl = true;
					}else if($mf_settings['ldap_encryption'] == 'tls'){
						$ldap_use_tls = true;
					}
					
					if(defined('MF_DISABLE_LDAPTLS_REQCERT') && MF_DISABLE_LDAPTLS_REQCERT === true){
						//this would allow LDAPS connection without any valid certificate
						putenv('LDAPTLS_REQCERT=never');
					}

					//the default is not to follow referrals, unles defined otherwise within the config.php file
					defined('MF_LDAP_OPT_REFERRALS') or define('MF_LDAP_OPT_REFERRALS',0);

					//the default attribute that contain the email address
					defined('MF_LDAP_MAIL_ATTRIBUTE') or define('MF_LDAP_MAIL_ATTRIBUTE','mail');

					
					if($mf_settings['ldap_type'] == 'ad'){ //if using Active Directory
						try {
						    if(empty($mf_settings['ldap_basedn'])){
						    	$mf_settings['ldap_basedn'] = null; //if baseDN is empty, set to null, so that adLDAP will find the defaultNamingContext
						    }

						    $ldap_admin_username = null;
						    $ldap_admin_password = null;

						    //if the ldap host contain admin username and password
						    //user:password@ldap-host
						    //provide the credentials when connecting to the LDAP
						    if(strpos($domain_controllers_array[0], '@') !== false){
						    	$ldap_uri 			 = 'ldaps://'.$domain_controllers_array[0];
						    	$ldap_admin_username = parse_url($ldap_uri, PHP_URL_USER);
						    	$ldap_admin_password = parse_url($ldap_uri, PHP_URL_PASS);
						    
						    	//remove credentials from domain_controllers_array
						    	$i=0;
						    	foreach($domain_controllers_array as $value){
						    		$value = parse_url('ldaps://'.$value, PHP_URL_HOST);
						    		$domain_controllers_array[$i] = $value;
						    		$i++;
						    	}
						    } 

						    $adldap = new adLDAP(array(
						    						"account_suffix" => $mf_settings['ldap_account_suffix'],
						    						"base_dn" => $mf_settings['ldap_basedn'],
						    						"domain_controllers" => $domain_controllers_array,
						    						"admin_username" => $ldap_admin_username,
						    						"admin_password" => $ldap_admin_password,
						    						"use_tls" => $ldap_use_tls,
						    						"use_ssl" => $ldap_use_ssl,
						    						"ad_port" => (int) $mf_settings['ldap_port'],
						    						"follow_referrals" => MF_LDAP_OPT_REFERRALS
						    		  			));

						    $adldap_auth_result = false;
							$adldap_auth_result = $adldap->authenticate($username, $password);
							
							if(empty($mf_settings['ldap_basedn'])){
								$adldap->setBaseDn($adldap->findBaseDn());
							}

							if($adldap_auth_result === true){
								//get user full name
								$ldap_user_info = $adldap->user()->info($username,array("givenname","sn",MF_LDAP_MAIL_ATTRIBUTE));
								$user_fullname  = $ldap_user_info[0]['givenname'][0].' '.$ldap_user_info[0]['sn'][0];
								$user_email		= $ldap_user_info[0][MF_LDAP_MAIL_ATTRIBUTE][0];
								
								if(!empty($user_email)){
									//check for required groups, if any
									if(!empty($mf_settings['ldap_required_group'])){
										$ldap_required_group_array = explode(",", $mf_settings['ldap_required_group']);
										array_walk($ldap_required_group_array, 'mf_trim_value');

										$user_in_group = false;
										foreach ($ldap_required_group_array as $group_name) {
										 	$group_check_result = $adldap->user()->inGroup($username,$group_name);
											if($group_check_result === true){
												$user_in_group = true;
												break;
											}
										} 
										
										if($user_in_group){
											$ldap_login_verified = true;
										}else{
											$ldap_login_verified = false;
											$ldap_error_message  = "You're not in an authorized group! (LDAP)";
										}
									}else{
										$ldap_login_verified = true;
									}
								}else{
									//if the user don't have email, don't allow login
									$ldap_login_verified = false;
									$ldap_error_message  = "Incorrect login credentials! (LDAP)";
								}
							}else{
								$ldap_login_verified = false;
								$ldap_error_message  = "Incorrect login credentials! (LDAP)";
							}
						} catch (adLDAPException $e) {
						    $ldap_error_message = $e->getMessage();
						}						
					}else if($mf_settings['ldap_type'] == 'openldap'){ //if using OpenLDAP
						try {
						    //get one of the provided domain controller
							$selected_domain_controller = $domain_controllers_array[array_rand($domain_controllers_array)];

							if($ldap_use_ssl){
					        	$opldap_conn = ldap_connect("ldaps://".$selected_domain_controller, (int) $mf_settings['ldap_port']);
					        }else{
					        	$opldap_conn = ldap_connect("ldap://".$selected_domain_controller, (int) $mf_settings['ldap_port']);
					        }
					               
							//set LDAP version
					        ldap_set_option($opldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
					        ldap_set_option($opldap_conn, LDAP_OPT_REFERRALS, MF_LDAP_OPT_REFERRALS);
					        
					        if($ldap_use_tls) {
					            ldap_start_tls($opldap_conn);
					        }

					        //authenticate, bind as the user
					        if(defined('MF_OPENLDAP_LOGIN_ATTRIBUTE')){
					        	$openldap_login_attribute = MF_OPENLDAP_LOGIN_ATTRIBUTE;
					        }else{
					        	$openldap_login_attribute = "uid";
					        }

					        $openldap_auth_result = false;      
					        $openldap_auth_result = @ldap_bind($opldap_conn, $openldap_login_attribute.'='.$username.','.$mf_settings['ldap_basedn'], $password);
					        
							if($openldap_auth_result === true){
								//check for required groups, if any
								if(!empty($mf_settings['ldap_required_group'])){
									$ldap_required_group_array = explode(",", $mf_settings['ldap_required_group']);
									
									array_walk($ldap_required_group_array, 'mf_trim_value');
									array_walk($ldap_required_group_array, 'mf_strtolower_value');

									$user_in_group = false;
									$user_cn = $openldap_login_attribute.'='.$username.','.$mf_settings['ldap_basedn'];
									
									$opldap_result = array(); 
									$opldap_data = array();
									$user_current_groups = array();

									$opldap_result = ldap_search($opldap_conn, $mf_settings['ldap_basedn'], "( |(&(objectClass=groupOfUniqueNames)(uniqueMember={$user_cn}))(&(objectClass=groupOfNames)(member={$user_cn})) )",array('cn'));
									$opldap_data   = ldap_get_entries($opldap_conn, $opldap_result);
									
									foreach ($opldap_data as $value) {
										if(!empty($value['cn'][0])){
											$user_current_groups[] = strtolower(trim($value['cn'][0]));
										}
									}
									
									if(!empty($user_current_groups)){
										if(count(array_intersect($user_current_groups, $ldap_required_group_array)) > 0){
											$user_in_group = true;
										}
									}
									
									if($user_in_group){
										$ldap_login_verified = true;
									}else{
										$ldap_login_verified = false;
										$ldap_error_message  = "You're not in an authorized group! (LDAP)";
									}
								}else{
									$ldap_login_verified = true;
								}

								//get user full name
								$opldap_result = ldap_search($opldap_conn, $mf_settings['ldap_basedn'], "({$openldap_login_attribute}={$username})", array($openldap_login_attribute, 'sn', 'givenname', MF_LDAP_MAIL_ATTRIBUTE));
								$opldap_data  = ldap_get_entries($opldap_conn, $opldap_result);
								
								$user_fullname  = $opldap_data[0]['givenname'][0].' '.$opldap_data[0]['sn'][0];
								$user_email		= $opldap_data[0]['mail'][0];
								$username       = $user_email; //machform uses email as the main identifier

							}else{
								$ldap_login_verified = false;
								$ldap_error_message  = "Incorrect login credentials! (LDAP)";
							}
						} catch (Exception $e) {
						    $ldap_error_message = $e->getMessage();
						}
					}

					if($ldap_login_verified){

						//once verified through LDAP, no need to run local auth
						$auth_result = true;
						$run_local_auth = false;

						//if user authenticated within LDAP, check if the local user account already exist or not
						//if not exist and not suspended, create the account
						//if user suspended, throw error message
						$query  = "SELECT 
									`user_password`,
									`user_id`,
									`user_admin_theme`,
									`priv_administer`,
									`priv_new_forms`,
									`priv_new_themes`,
									`tsv_enable`,
									`tsv_secret`,
									`login_attempt_date`,
									`login_attempt_count`,
									`status`    
								FROM 
									`".MF_TABLE_PREFIX."users` 
							   WHERE 
							   		`user_email`=? and `status` IN(1,2)";
						$params = array($user_email);
						$sth = mf_do_query($query,$params,$dbh);
						$row = mf_do_fetch_result($sth);

						if(!empty($row)){
							//load existing user data
							$stored_password_hash = $row['user_password'];
							$user_id 			  = $row['user_id'];
							$user_admin_theme 	  = $row['user_admin_theme'];
							$priv_administer	  = (int) $row['priv_administer'];
							$priv_new_forms		  = (int) $row['priv_new_forms'];
							$priv_new_themes	  = (int) $row['priv_new_themes'];

							$tsv_enable	  		  = (int) $row['tsv_enable'];
							$tsv_secret 		  = $row['tsv_secret'];

							$login_attempt_date   = $row['login_attempt_date'];
							$login_attempt_count  = $row['login_attempt_count'];

							$user_status 		  = (int) $row['status'];

							if($user_status == 1){
								//update user fullname from LDAP into local users table
								$query = "UPDATE ".MF_TABLE_PREFIX."users 
											 SET user_fullname = ? 
										   WHERE `user_email`=? and `status`=1";
								$params = array($user_fullname,$user_email);
								mf_do_query($query,$params,$dbh);
							}else if($user_status == 2){
								//if user is suspended locally, display error message
								$ldap_error_message = 'Your account has been suspended.';
								$auth_result = false;
								$run_local_auth = false;
							}
							
						}else{
							//create local account using info from LDAP
							$priv_administer  = 0;
							$priv_new_forms   = 1;
							$priv_new_themes  = 1;
							
							$query = "INSERT INTO 
												`".MF_TABLE_PREFIX."users`( 
															`user_email`, 
															`user_password`, 
															`user_fullname`, 
															`priv_administer`, 
															`priv_new_forms`, 
															`priv_new_themes`, 
															`status`) 
										  VALUES (?, ?, ?, ?, ?, ?, ?);";
							$params = array(
											$user_email,
											'',
											$user_fullname,
											$priv_administer,
											$priv_new_forms,
											$priv_new_themes,
											1);
							mf_do_query($query,$params,$dbh);
							$user_id = (int) $dbh->lastInsertId();

							//insert into ap_folders table
							//create a default folder for the user
							$query = "INSERT INTO 
												`".MF_TABLE_PREFIX."folders`( 
															`user_id`, 
															`folder_id`, 
															`folder_position`, 
															`folder_name`, 
															`folder_selected`, 
															`rule_all_any`) 
										  VALUES (?, 1, 1, 'All Forms', 1, 'all');";
							$params = array($user_id);
							mf_do_query($query,$params,$dbh);
						}
					}
				}

				if($run_local_auth){
					//start local authentication---------------------
					//get the password hash from the database
					$query  = "SELECT 
									`user_password`,
									`user_id`,
									`user_admin_theme`,
									`priv_administer`,
									`priv_new_forms`,
									`priv_new_themes`,
									`tsv_enable`,
									`tsv_secret`,
									`login_attempt_date`,
									`login_attempt_count`   
								FROM 
									`".MF_TABLE_PREFIX."users` 
							   WHERE 
							   		`user_email`=? and `status`=1";
					$params = array($username);
					$sth = mf_do_query($query,$params,$dbh);
					$row = mf_do_fetch_result($sth);

					$stored_password_hash = $row['user_password'];
					$user_id 			  = $row['user_id'];
					$user_admin_theme 	  = $row['user_admin_theme'];
					$priv_administer	  = (int) $row['priv_administer'];
					$priv_new_forms		  = (int) $row['priv_new_forms'];
					$priv_new_themes	  = (int) $row['priv_new_themes'];

					$tsv_enable	  		  = (int) $row['tsv_enable'];
					$tsv_secret 		  = $row['tsv_secret'];

					$login_attempt_date   = $row['login_attempt_date'];
					$login_attempt_count  = $row['login_attempt_count'];

					//check the password
					$auth_result  = $hasher->CheckPassword($password, $stored_password_hash);
					//end local authentication---------------------
				}

				if($auth_result){
					$login_is_valid = true;
				}else{
					$login_is_valid = false;
					
					if(!empty($ldap_error_message)){
						$_SESSION['MF_LOGIN_ERROR'] = $ldap_error_message;
					}else{
						$_SESSION['MF_LOGIN_ERROR'] = 'Incorrect email or password!';
					}

					//if account locking enabled, increase the login attempt counter
					if(!empty($mf_settings['enable_account_locking']) && !empty($user_id)){
						$query = "UPDATE ".MF_TABLE_PREFIX."users 
									  SET 
									  	 login_attempt_date=?,
									  	 login_attempt_count=(login_attempt_count + 1) 
								    WHERE 
								    	 user_id = ?";
						$new_login_attempt_date = date("Y-m-d H:i:s");
						$params = array($new_login_attempt_date,$user_id);
						mf_do_query($query,$params,$dbh);
					}
				}

				//check for account locking status
				if(!empty($mf_settings['enable_account_locking']) && !empty($user_id)){
					$account_lock_period	   = (int) $mf_settings['account_lock_period'];
					$account_lock_max_attempts = (int) $mf_settings['account_lock_max_attempts'];

					$account_blocked_message   = "Sorry, this account is temporarily blocked. Please try again after {$account_lock_period} minutes.";

					//check the lock period
					$login_attempt_date 	   = strtotime($login_attempt_date);
					$account_lock_expiry_date  = $login_attempt_date + (60 * $account_lock_period); 
					$current_datetime 		   = strtotime(date("Y-m-d H:i:s"));
					
					//if lock period still valid, check max attempts
					if($current_datetime < $account_lock_expiry_date){
					
						//if max attempts already exceed the limit, block the user
						if($login_attempt_count >= $account_lock_max_attempts){
							$login_is_valid = false;
							$_SESSION['MF_LOGIN_ERROR'] = $account_blocked_message;
						}
					}else{
						
						//else if lock period already expired
						$query = "UPDATE ".MF_TABLE_PREFIX."users 
									  SET 
									  	 login_attempt_date = ?,
									  	 login_attempt_count = ? 
								    WHERE 
								    	 user_id = ?";
						
						//if password is correct, reset to zero
						//else if password is incorrect, set counter to 1
						if($login_is_valid){
							$login_attempt_date  = '';
							$login_attempt_count = 0;
						}else{
							$login_attempt_date  = date("Y-m-d H:i:s");
							$login_attempt_count = 1;
						}
						
						$params = array($login_attempt_date,$login_attempt_count,$user_id);
						mf_do_query($query,$params,$dbh);
					}
				}

				//if login is validated and password is correct
				if($login_is_valid){

					//check for 2-Step Verification, is it enabled or not
					$show_tsv_page = false;

					//if TSV is enforced globally
					if(!empty($mf_settings['enforce_tsv'])){
						$show_tsv_page = true;

						if(empty($tsv_secret)){
							//display TSV setup page
							$tsv_page_target = 'setup';
						}else{
							//display TSV verify page
							$tsv_page_target = 'verify';
						}
					}else{
						if(!empty($tsv_enable)){
							$show_tsv_page = true;

							if(empty($tsv_secret)){
								//display TSV setup page
								$tsv_page_target = 'setup';
							}else{
								//display TSV verify page
								$tsv_page_target = 'verify';
							}
						}
					}

					if($show_tsv_page === true){
						if($tsv_page_target == 'setup'){
							//display TSV setup page
							$_SESSION['mf_tsv_setup'] = $user_id;
							$_SESSION['mf_tsv_setup_remember_me'] = $remember_me;

							header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/login_tsv_setup.php");
							exit;
						}else if($tsv_page_target == 'verify'){
							$_SESSION['mf_tsv_verify'] = $user_id;
							$_SESSION['mf_tsv_verify_remember_me'] = $remember_me;

							header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/login_verify.php");
							exit;
						}
					}else{
						//reset login counter
						$query = "UPDATE ".MF_TABLE_PREFIX."users 
										  SET 
										  	 login_attempt_date = NULL,
										  	 login_attempt_count = 0 
									    WHERE 
									    	 user_id = ?";
						$params = array($user_id);
						mf_do_query($query,$params,$dbh);

						//regenerate session id for protection against session fixation
						session_regenerate_id();

						//regenerate CSRF token
						$_SESSION['mf_csrf_token'] = bin2hex(random_bytes(24));

						//set the session variables for the user=========
						$_SESSION['mf_logged_in'] = true;
						$_SESSION['mf_user_id']   = $user_id;
						$_SESSION['mf_user_admin_theme'] = $user_admin_theme;
						$_SESSION['mf_user_privileges']['priv_administer'] = $priv_administer;
						$_SESSION['mf_user_privileges']['priv_new_forms']  = $priv_new_forms;
						$_SESSION['mf_user_privileges']['priv_new_themes'] = $priv_new_themes;
						//===============================================

						//update last_login_date and last_ip_address
						$last_login_date = date("Y-m-d H:i:s");
						$last_ip_address = $_SERVER['REMOTE_ADDR'];

						$query  = "UPDATE ".MF_TABLE_PREFIX."users set last_login_date=?,last_ip_address=? WHERE `user_id`=?";
						$params = array($last_login_date,$last_ip_address,$user_id);
						mf_do_query($query,$params,$dbh);


						//if the user select the "remember me option"
						//set the cookie and make it active for the next 30 days
						if(!empty($remember_me)){
							$cookie_hash = $hasher->HashPassword(mt_rand()); //generate random hash and save it into ap_users table

							$query = "update ".MF_TABLE_PREFIX."users set cookie_hash=? where `user_id`=?";
				   			$params = array($cookie_hash,$user_id);
				   			mf_do_query($query,$params,$dbh);

				   			//send the cookie
				   			setcookie('mf_remember',$cookie_hash, time()+3600*24*30, "/");
						}

						if(!empty($_SESSION['prev_referer'])){
							$next_page = $_SESSION['prev_referer'];
							
							unset($_SESSION['prev_referer']);
							header("Location: ".$next_page);
							
							exit;
						}else{
							header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/manage_forms.php");
							exit;
						}
					}
				}

			}

		}else{
			//generate anti CSRF token if not exist
			if(empty($_SESSION['mf_csrf_token'])){
				$_SESSION['mf_csrf_token'] = bin2hex(random_bytes(24));
			}
		}
		
		if(!empty($_GET['from'])){
			$_SESSION['prev_referer'] = base64_decode($_GET['from']);
		}
	} //end allow_login
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>MachForm Admin Panel</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="index, nofollow" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="css/main.css<?php echo $mf_version_tag; ?>" media="screen" />
<link rel="stylesheet" type="text/css" href="css/main.mobile.css<?php echo $mf_version_tag; ?>" media="screen" />
<link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">    
    
<!--[if IE 7]>
	<link rel="stylesheet" type="text/css" href="css/ie7.css" media="screen" />
<![endif]-->
	
<!--[if IE 8]>
	<link rel="stylesheet" type="text/css" href="css/ie8.css" media="screen" />
<![endif]-->

<!--[if IE 9]>
	<link rel="stylesheet" type="text/css" href="css/ie9.css" media="screen" />
<![endif]-->
   
<link href="css/theme.css<?php echo $mf_version_tag; ?>" rel="stylesheet" type="text/css" />
<?php
	if(!empty($mf_settings['admin_theme'])){
		echo '<link href="css/themes/theme_'.$mf_settings['admin_theme'].'.css'.$mf_version_tag.'" rel="stylesheet" type="text/css" />';
	}
?>
<link href="css/bb_buttons.css<?php echo $mf_version_tag; ?>" rel="stylesheet" type="text/css" />
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css<?php echo $mf_version_tag; ?>" rel="stylesheet" />
<link type="text/css" href="css/edit_form.css<?php echo $mf_version_tag; ?>" rel="stylesheet" />
<link type="text/css" href="js/datepick/smoothness.datepick.css<?php echo $mf_version_tag; ?>" rel="stylesheet" />
<link href="css/override.css<?php echo $mf_version_tag; ?>" rel="stylesheet" type="text/css" />
</head>

<body>

<div id="bg" class="login_page">

<div id="container">
	<div id="header">
	<?php
		if(!empty($mf_settings['admin_image_url'])){
			$machform_logo_main = htmlspecialchars($mf_settings['admin_image_url']);
			$machform_logo_tag = '<img class="title" src="'.$machform_logo_main.'" style="margin-left: 8px;" />';
		}else{
			if(!empty($mf_settings['admin_theme'])){
				$machform_logo_main = 'images/machform_logo_'.$mf_settings['admin_theme'].'.png';
			}else{
				$machform_logo_main = 'images/machform_logo_vibrant.png';
			}

			$machform_logo_tag = '<img class="title" src="'.$machform_logo_main.'" style="margin-left: 8px;width: 180px" alt="MachForm" />';
		}
	?>
		<div id="logo">
			<?php echo $machform_logo_tag; ?>
		</div>	

		
	</div>
	<div id="main">
	
 
		<div id="content">
			<div class="post login_main">

				<div style="padding-top: 10px">
					<div>
						<span id="login_logo" class="icon-shield"></span>
						<h3>Sign In to Admin Panel</h3>
						<p>Sign in below to create or edit your forms</p>
						<div style="clear:both; border-bottom: 1px dotted #CCCCCC;margin-top: 15px"></div>
					</div>
					<?php ?>
					<div style="border-bottom: 1px dotted #CCCCCC;margin-top: 10px">
							<form id="form_login" class="appnitro"  method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
							<ul>

								<?php if(!empty($_SESSION['MF_LOGIN_ERROR'])){ ?>
									<li id="li_login_notification">
										<h5><?php echo $_SESSION['MF_LOGIN_ERROR']; ?></h5>	
									</li>		
								<?php 
									   unset($_SESSION['MF_LOGIN_ERROR']);
									} 
								?>

								<?php if($allow_login){ ?>
								<li id="li_email_address">
									<label class="desc" for="admin_username"><?php if(!empty($mf_settings['ldap_enable'])){ echo "Username"; }else{ echo "Email Address"; } ?></label>
									<div>
										<input id="admin_username" name="admin_username" class="element text large" type="text" maxlength="255" value="<?php echo htmlspecialchars($username); ?>"/> 
									</div>
								</li>		
								<li id="li_password">
									<label class="desc" for="admin_password">Password </label>
									<div>
										<input id="admin_password" name="admin_password" class="element text large" type="password" maxlength="255" value=""/> 
									</div> 
								</li>
								<li id="li_remember_me">
									<span>
										<input type="checkbox" value="1" class="element checkbox" name="admin_remember" id="admin_remember" style="margin-left: 0px">
										<label for="admin_remember" class="choice">Remember me</label>
							
									</span> 
								</li>
					    		<li id="li_submit" class="buttons" style="overflow: auto">
					    			<input type="hidden" name="submit" id="submit" value="1">
					    			<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['mf_csrf_token']); ?>">
							    	<button type="submit" class="bb_button bb_green" id="submit_button" name="submit_button" style="float: left;border-radius: 4px">
								        <span class="icon-keyhole"></span>
								        Sign In
								    </button>
								</li>
								<?php } ?>

							</ul>
							</form>	
					</div>

					<?php if($allow_login && empty($mf_settings['ldap_enable'])){ ?>
					<ul id="login_forgot_container">
							<li>
									<span>
										<input type="checkbox" value="1" class="element checkbox" name="admin_forgot" id="admin_forgot" style="margin-left: 0px">
										<label id="admin_forgot_label" for="admin_forgot" class="choice">I forgot my password</label>
							
									</span> 
							</li>
					</ul>
					<?php } ?>

				</div>
     
        	</div>  		 
		</div>


<div id="dialog-login-page" title="Success!" class="buttons" style="display: none">
	<img src="images/icons/62_green_48.png" title="Success" /> 
	<p id="dialog-login-page-msg">
			Success
	</p>
</div>		
		

<?php
	$footer_data =<<<EOT
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.core.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.widget.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.tabs.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.mouse.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.sortable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.draggable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.position.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.dialog.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.effects.core.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/login_admin.js{$mf_version_tag}"></script>
EOT;
	require('includes/footer.php'); 
?>