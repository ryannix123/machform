<?php
/******************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
******************************************************************************/

global $mf_hook_emails;

/*** START SAMPLE - Sample email hooks

This hooks variable can be used to dynamically set the destination email address of a form
based on dropdown selection

For example, you have a form with id number = 3 and dropdown with element_id = 5 and have three options:
- First option
- Second option
- Third option
your hook variable should be the following:

START EMAIL HOOK ---------------------

$mf_hook_emails[3]['element_id'] = 5;

$mf_hook_emails[3]['First option']   = 'email_1@example.com';
$mf_hook_emails[3]['Second option']	 = 'email_2@example.com';
$mf_hook_emails[3]['Third option'] 	 = 'email_3@example.com,email_4@example.com';	

END EMAIL HOOK ---------------------

END SAMPLE ****/


?>