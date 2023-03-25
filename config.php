<?php
/*
MachForm Configuration File
*/

/** MySQL settings **/

define('MF_DB_NAME', 'database_name_here'); //The name of your database. Note that this database must exist before running installer.php
define('MF_DB_USER', 'username_here'); //Your database username
define('MF_DB_PASSWORD', 'password_here'); //Your database users password
define('MF_DB_HOST', 'localhost'); //The hostname for your database





/** YOU CAN LEAVE THE SETTINGS BELOW THIS LINE UNCHANGED **/

/** Optional Settings **/
/** All settings below this line are optionals, you can leave them as they are now **/
define('MF_TABLE_PREFIX', 'ap_'); //The prefix for all machform tables

//by default (false), deleting field from the form won't actually remove all the data within the table, so that we can manually recover it
//by setting this value to 'true' the data will be removed completely, unrecoverable
define('MF_CONF_TRUE_DELETE',false);

//by default (true), duplicate form entries will be discarded to prevent spam
//by setting this value to 'false', server-side validation for duplicate submissions will be disabled
define('MF_DISCARD_DUPLICATE_ENTRY',true);

//by default (true), any SQL error messages will be displayed to help troublesheeting issue
//if you prefer to hide SQL error messages, change this value to 'false'
define('MF_SQL_DEBUG_MODE',true);

/** LDAP options **/
define('MF_OPENLDAP_LOGIN_ATTRIBUTE','uid'); //if your LDAP is using different auth attribute than uid (ex: cn), change this setting
define('MF_LDAP_MAIL_ATTRIBUTE','mail'); //The attribute that contain user's email address
define('MF_DISABLE_LDAPTLS_REQCERT',false); //if set to 'true', certificate will be ignored during connection
define('MF_LDAP_OPT_REFERRALS',0); //most likely you don't need to change this one. if you aren't sure, leave it as it is

?>