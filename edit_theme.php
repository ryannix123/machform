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

	require('includes/theme-functions.php');

	$dbh 		 = mf_connect_db();
	
	$mf_settings 	= mf_get_settings($dbh);
	$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);
	
	$theme_id 	= isset($_REQUEST['theme_id']) ? (int) trim($_REQUEST['theme_id']) : 0;
	$ssl_suffix = mf_get_ssl_suffix();

	//check user privileges, is this user has privilege to create new theme (or edit)?
	if(empty($_SESSION['mf_user_privileges']['priv_new_themes'])){
		$_SESSION['MF_DENIED'] = "You don't have permission to create/edit themes.";
					
		header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
		exit;
	}
	
	$theme_properties = new stdClass();
	$is_builtin_theme = false;
	
	if(empty($theme_id)){
		//this is a new theme, populate with default values
		$theme_properties->theme_id = 0;
		$theme_properties->theme_name  = '';
		$theme_properties->logo_type = 'default'; //possible values: default, custom, disabled
		$theme_properties->logo_custom_image  = 'http://';
		$theme_properties->logo_custom_height = 40;
		$theme_properties->logo_default_image = 'machform.png';
		$theme_properties->wallpaper_bg_type 	= 'pattern';
		$theme_properties->wallpaper_bg_color 	= '#ececec';
		$theme_properties->wallpaper_bg_pattern = 'grey-mild.png';
		$theme_properties->wallpaper_bg_custom 	= '';
		$theme_properties->header_bg_type 		= 'color';
		$theme_properties->header_bg_color 		= '#525252';
		$theme_properties->header_bg_pattern 	= '';
		$theme_properties->header_bg_custom 	= '';
		$theme_properties->form_bg_type 		= 'color';
		$theme_properties->form_bg_color 		= '#ffffff';
		$theme_properties->form_bg_pattern 		= '';
		$theme_properties->form_bg_custom 		= '';
		$theme_properties->highlight_bg_type 	= 'color';
		$theme_properties->highlight_bg_color 	= '#FFF7C0';
		$theme_properties->highlight_bg_pattern = '';
		$theme_properties->highlight_bg_custom 	= '';
		$theme_properties->guidelines_bg_type 	= 'color';
		$theme_properties->guidelines_bg_color 	= '#F5F5F5';
		$theme_properties->guidelines_bg_pattern = '';
		$theme_properties->guidelines_bg_custom  = '';
		$theme_properties->field_bg_type 		 = 'color';
		$theme_properties->field_bg_color 		 = '#FBFBFB';
		$theme_properties->field_bg_pattern 	 = '';
		$theme_properties->field_bg_custom  	 = '';
		$theme_properties->form_title_font_type    = 'Lucida Grande';
		$theme_properties->form_title_font_weight  = 400;
		$theme_properties->form_title_font_style   = 'normal';
		$theme_properties->form_title_font_size    = '160%';
		$theme_properties->form_title_font_color    = '#444444';
		$theme_properties->form_desc_font_type    = 'Lucida Grande';
		$theme_properties->form_desc_font_weight  = 400;
		$theme_properties->form_desc_font_style   = 'normal';
		$theme_properties->form_desc_font_size    = '95%';
		$theme_properties->form_desc_font_color    = '#444444';
		$theme_properties->field_title_font_type    = 'Lucida Grande';
		$theme_properties->field_title_font_weight  = 700;
		$theme_properties->field_title_font_style   = 'normal';
		$theme_properties->field_title_font_size    = '95%';
		$theme_properties->field_title_font_color    = '#444444';
		$theme_properties->guidelines_font_type    = 'Lucida Grande';
		$theme_properties->guidelines_font_weight  = 400;
		$theme_properties->guidelines_font_style   = 'normal';
		$theme_properties->guidelines_font_size    = '80%';
		$theme_properties->guidelines_font_color    = '#444444';
		$theme_properties->section_title_font_type    = 'Lucida Grande';
		$theme_properties->section_title_font_weight  = 400;
		$theme_properties->section_title_font_style   = 'normal';
		$theme_properties->section_title_font_size    = '110%';
		$theme_properties->section_title_font_color   = '#444444';
		$theme_properties->section_desc_font_type    = 'Lucida Grande';
		$theme_properties->section_desc_font_weight  = 400;
		$theme_properties->section_desc_font_style   = 'normal';
		$theme_properties->section_desc_font_size    = '85%';
		$theme_properties->section_desc_font_color   = '#444444';
		$theme_properties->field_text_font_type    = 'Lucida Grande';
		$theme_properties->field_text_font_weight  = 400;
		$theme_properties->field_text_font_style   = 'normal';
		$theme_properties->field_text_font_size    = '100%';
		$theme_properties->field_text_font_color   = '#666666';
		$theme_properties->border_form_width   = 1;
		$theme_properties->border_form_style   = 'solid';
		$theme_properties->border_form_color   = '#CCCCCC';
		$theme_properties->border_guidelines_width   = 1;
		$theme_properties->border_guidelines_style   = 'solid';
		$theme_properties->border_guidelines_color   = '#CCCCCC';
		$theme_properties->border_section_width   = 1;
		$theme_properties->border_section_style   = 'dotted';
		$theme_properties->border_section_color   = '#CCCCCC';
		$theme_properties->form_shadow_style	  = 'disabled';
		$theme_properties->form_shadow_size	  	  = 'large';
		$theme_properties->form_shadow_brightness = 'normal';
		$theme_properties->form_button_type	  	  = 'text';
		$theme_properties->form_button_text	  	  = 'Submit';
		$theme_properties->form_button_image	  = 'http://';
		$theme_properties->advanced_css	  		  = '';

	}else{
		//this is editing existing theme, load the values from the database
		$query = "SELECT
						theme_name,
						`status`,
						logo_type,
						ifnull(logo_custom_image,'') logo_custom_image,
						logo_custom_height,
						logo_default_image,
						wallpaper_bg_type,
						wallpaper_bg_color,
						wallpaper_bg_pattern,
						wallpaper_bg_custom,
						header_bg_type,
						header_bg_color,
						header_bg_pattern,
						header_bg_custom,
						form_bg_type,
						form_bg_color,
						form_bg_pattern,
						form_bg_custom,
						highlight_bg_type,
						highlight_bg_color,
						highlight_bg_pattern,
						highlight_bg_custom,
						guidelines_bg_type,
						guidelines_bg_color,
						guidelines_bg_pattern,
						guidelines_bg_custom,
						field_bg_type,
						field_bg_color,
						field_bg_pattern,
						field_bg_custom,
						form_title_font_type,
						form_title_font_weight,
						form_title_font_style,
						form_title_font_size,
						form_title_font_color,
						form_desc_font_type,
						form_desc_font_weight,
						form_desc_font_style,
						form_desc_font_size,
						form_desc_font_color,
						field_title_font_type,
						field_title_font_weight,
						field_title_font_style,
						field_title_font_size,
						field_title_font_color,
						guidelines_font_type,
						guidelines_font_weight,
						guidelines_font_style,
						guidelines_font_size,
						guidelines_font_color,
						section_title_font_type,
						section_title_font_weight,
						section_title_font_style,
						section_title_font_size,
						section_title_font_color,
						section_desc_font_type,
						section_desc_font_weight,
						section_desc_font_style,
						section_desc_font_size,
						section_desc_font_color,
						field_text_font_type,
						field_text_font_weight,
						field_text_font_style,
						field_text_font_size,
						field_text_font_color,
						border_form_width,
						border_form_style,
						border_form_color,
						border_guidelines_width,
						border_guidelines_style,
						border_guidelines_color,
						border_section_width,
						border_section_style,
						border_section_color,
						form_shadow_style,
						form_shadow_size,
						form_shadow_brightness,
						form_button_type,
						form_button_text,
						form_button_image,
						advanced_css,
						theme_built_in,
						theme_is_private,
						user_id
					FROM
						`".MF_TABLE_PREFIX."form_themes`
				   WHERE
				   		theme_id=? and `status`=1";
		$params = array($theme_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		$theme_properties->theme_id 		   = $theme_id;
		$theme_properties->theme_name  		   = $row['theme_name'];
		$theme_properties->status  		   	   = (int) $row['status'];
		$theme_properties->theme_is_private    = (int) $row['theme_is_private'];
		$theme_properties->logo_type 		   = $row['logo_type']; 
		$theme_properties->logo_custom_image   = $row['logo_custom_image'];
		$theme_properties->logo_custom_height  = (int) $row['logo_custom_height'];
		$theme_properties->logo_default_image  = $row['logo_default_image'];
		$theme_properties->wallpaper_bg_type 	= $row['wallpaper_bg_type'];
		$theme_properties->wallpaper_bg_color 	= $row['wallpaper_bg_color'];
		$theme_properties->wallpaper_bg_pattern = $row['wallpaper_bg_pattern'];
		$theme_properties->wallpaper_bg_custom 	= $row['wallpaper_bg_custom'];
		$theme_properties->header_bg_type 		= $row['header_bg_type'];
		$theme_properties->header_bg_color 		= $row['header_bg_color'];
		$theme_properties->header_bg_pattern 	= $row['header_bg_pattern'];;
		$theme_properties->header_bg_custom 	= $row['header_bg_custom'];
		$theme_properties->form_bg_type 		= $row['form_bg_type'];
		$theme_properties->form_bg_color 		= $row['form_bg_color'];
		$theme_properties->form_bg_pattern 		= $row['form_bg_pattern'];;
		$theme_properties->form_bg_custom 		= $row['form_bg_custom'];
		$theme_properties->highlight_bg_type 	= $row['highlight_bg_type'];
		$theme_properties->highlight_bg_color 	= $row['highlight_bg_color'];
		$theme_properties->highlight_bg_pattern = $row['highlight_bg_pattern'];
		$theme_properties->highlight_bg_custom 	= $row['highlight_bg_custom'];
		$theme_properties->guidelines_bg_type 	= $row['guidelines_bg_type'];
		$theme_properties->guidelines_bg_color 	= $row['guidelines_bg_color'];
		$theme_properties->guidelines_bg_pattern = $row['guidelines_bg_pattern'];
		$theme_properties->guidelines_bg_custom  = $row['guidelines_bg_custom'];
		$theme_properties->field_bg_type 		 = $row['field_bg_type'];
		$theme_properties->field_bg_color 		 = $row['field_bg_color'];
		$theme_properties->field_bg_pattern 	 = $row['field_bg_pattern'];
		$theme_properties->field_bg_custom  	 = $row['field_bg_custom'];
		$theme_properties->form_title_font_type    = $row['form_title_font_type'];
		$theme_properties->form_title_font_weight  = (int) $row['form_title_font_weight'];
		$theme_properties->form_title_font_style   = $row['form_title_font_style'];
		$theme_properties->form_title_font_size    = $row['form_title_font_size'];
		$theme_properties->form_title_font_color   = $row['form_title_font_color'];
		$theme_properties->form_desc_font_type    = $row['form_desc_font_type'];
		$theme_properties->form_desc_font_weight  = (int) $row['form_desc_font_weight'];
		$theme_properties->form_desc_font_style   = $row['form_desc_font_style'];
		$theme_properties->form_desc_font_size    = $row['form_desc_font_size'];
		$theme_properties->form_desc_font_color   = $row['form_desc_font_color'];
		$theme_properties->field_title_font_type    = $row['field_title_font_type'];
		$theme_properties->field_title_font_weight  = (int) $row['field_title_font_weight'];
		$theme_properties->field_title_font_style   = $row['field_title_font_style'];
		$theme_properties->field_title_font_size    = $row['field_title_font_size'];
		$theme_properties->field_title_font_color   = $row['field_title_font_color'];
		$theme_properties->guidelines_font_type    = $row['guidelines_font_type'];
		$theme_properties->guidelines_font_weight  = (int) $row['guidelines_font_weight'];
		$theme_properties->guidelines_font_style   = $row['guidelines_font_style'];
		$theme_properties->guidelines_font_size    = $row['guidelines_font_size'];
		$theme_properties->guidelines_font_color   = $row['guidelines_font_color'];
		$theme_properties->section_title_font_type    = $row['section_title_font_type'];
		$theme_properties->section_title_font_weight  = (int) $row['section_title_font_weight'];
		$theme_properties->section_title_font_style   = $row['section_title_font_style'];
		$theme_properties->section_title_font_size    = $row['section_title_font_size'];
		$theme_properties->section_title_font_color   = $row['section_title_font_color'];
		$theme_properties->section_desc_font_type    = $row['section_desc_font_type'];
		$theme_properties->section_desc_font_weight  = (int) $row['section_desc_font_weight'];
		$theme_properties->section_desc_font_style   = $row['section_desc_font_style'];
		$theme_properties->section_desc_font_size    = $row['section_desc_font_size'];
		$theme_properties->section_desc_font_color   = $row['section_desc_font_color'];
		$theme_properties->field_text_font_type    = $row['field_text_font_type'];
		$theme_properties->field_text_font_weight  = (int) $row['field_text_font_weight'];
		$theme_properties->field_text_font_style   = $row['field_text_font_style'];
		$theme_properties->field_text_font_size    = $row['field_text_font_size'];
		$theme_properties->field_text_font_color   = $row['field_text_font_color'];
		$theme_properties->border_form_width   = (int) $row['border_form_width'];
		$theme_properties->border_form_style   = $row['border_form_style'];
		$theme_properties->border_form_color   = $row['border_form_color'];
		$theme_properties->border_guidelines_width   = (int) $row['border_guidelines_width'];
		$theme_properties->border_guidelines_style   = $row['border_guidelines_style'];
		$theme_properties->border_guidelines_color   = $row['border_guidelines_color'];
		$theme_properties->border_section_width   = (int) $row['border_section_width'];
		$theme_properties->border_section_style   = $row['border_section_style'];
		$theme_properties->border_section_color   = $row['border_section_color'];
		$theme_properties->form_shadow_style	  = $row['form_shadow_style'];
		$theme_properties->form_shadow_size	  	  = $row['form_shadow_size'];
		$theme_properties->form_shadow_brightness = $row['form_shadow_brightness'];
		$theme_properties->form_button_type	  	  = $row['form_button_type'];
		$theme_properties->form_button_text	  	  = $row['form_button_text'];
		$theme_properties->form_button_image	  = $row['form_button_image'];
		$theme_properties->advanced_css	  		  = $row['advanced_css'];
		
		//don't allow anyone (including admin) to edit built-in themes
		if(!empty($row['theme_built_in'])){
			$is_builtin_theme = true;

			$_SESSION['MF_DENIED'] = "You don't have permission to edit built-in themes.";
				
			header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}

		//check is this user allowed to edit this theme or not
		if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
			if($row['user_id'] != $_SESSION['mf_user_id']){
				$_SESSION['MF_DENIED'] = "You don't have permission to edit this theme.";
				
				header("Location: ".mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
				exit;
			}
		}
	
	}
	
	/** Start building form styles **/
	$font_family_array = array();
		
	/** Form Logo **/
	$form_logo_style = array();
	$form_logo_height = 40;
	
	if($theme_properties->logo_type == 'disabled'){ //logo disabled
		$form_logo_style[] = "background-image: url('images/form_resources/nologo.png')";
	}else if($theme_properties->logo_type == 'default'){//default logo
		$form_logo_style[] = "background-image: url('images/form_resources/{$theme_properties->logo_default_image}')";
		$form_logo_style[] = "background-repeat: no-repeat";
	}else if($theme_properties->logo_type == 'custom'){//custom logo
		$form_logo_style[] = "background-image: url('{$theme_properties->logo_custom_image}')";
		$form_logo_height  = $theme_properties->logo_custom_height;
	}
	
	$form_logo_style[] = "height: {$form_logo_height}px";
	
	//build the form logo style
	$form_logo_style_tag = implode(';',$form_logo_style);
	$form_logo_style_tag = ' style="'.$form_logo_style_tag.'" ';
	
	/** Wallpaper **/
	$form_wallpaper_style = array();
	
	if($theme_properties->wallpaper_bg_type == 'color'){
		if($theme_properties->wallpaper_bg_color == 'transparent'){
			$form_wallpaper_style[] = "background-image: url('images/icons/transparent.png')";
		}else{
			$form_wallpaper_style[] = "background-color: {$theme_properties->wallpaper_bg_color}";
		}
	}else if($theme_properties->wallpaper_bg_type == 'pattern'){
		$form_wallpaper_style[] = "background-image: url('images/form_resources/{$theme_properties->wallpaper_bg_pattern}')";
		$form_wallpaper_style[] = "background-repeat: repeat";
	}else if($theme_properties->wallpaper_bg_type == 'custom'){
		$form_wallpaper_style[] = "background-image: url('{$theme_properties->wallpaper_bg_custom}')";
		$form_wallpaper_style[] = "background-repeat: repeat";
	}
	
	$form_wallpaper_style_tag = implode(';',$form_wallpaper_style);
	$form_wallpaper_style_tag = ' style="'.$form_wallpaper_style_tag.'" ';
	
	/** Form Header **/
	$form_header_style = array();
	
	if($theme_properties->header_bg_type == 'color'){
		$form_header_style[] = "background-color: {$theme_properties->header_bg_color}";
	}else if($theme_properties->header_bg_type == 'pattern'){
		$form_header_style[] = "background-image: url('images/form_resources/{$theme_properties->header_bg_pattern}')";
		$form_header_style[] = "background-repeat: repeat";
	}else if($theme_properties->header_bg_type == 'custom'){
		$form_header_style[] = "background-image: url('{$theme_properties->header_bg_custom}')";
		$form_header_style[] = "background-repeat: repeat";
	}
	
	$form_header_style_tag = implode(';',$form_header_style);
	$form_header_style_tag = ' style="'.$form_header_style_tag.'" ';
	
	/** Form Background **/
	$form_container_style = array();
	
	if($theme_properties->form_bg_type == 'color'){
		$form_container_style[] = "background-color: {$theme_properties->form_bg_color}";
	}else if($theme_properties->form_bg_type == 'pattern'){
		$form_container_style[] = "background-image: url('images/form_resources/{$theme_properties->form_bg_pattern}')";
		$form_container_style[] = "background-repeat: repeat";
	}else if($theme_properties->form_bg_type == 'custom'){
		$form_container_style[] = "background-image: url('{$theme_properties->form_bg_custom}')";
		$form_container_style[] = "background-repeat: repeat";
	}
	
	/** Form Border **/
	if($theme_properties->border_form_width == 0){
		$form_container_style[] = "border-width: 0px";
		$form_container_style[] = "box-shadow: none";
	}else if($theme_properties->border_form_width == 1){
		if(strtolower($theme_properties->border_form_color) == '#cccccc'){
			$form_container_style[] = "border-width: 0px";
		}else{
			$form_container_style[] = "border-width: 1px";
		}

		$form_container_style[] = "box-shadow: 0 0 3px rgba(0, 0, 0, 0.4)";
	}else{
		$form_container_style[] = "border-width: {$theme_properties->border_form_width}px";
		$form_container_style[] = "box-shadow: 0 0 3px rgba(0, 0, 0, 0.4)";
	}

	if(!empty($theme_properties->border_form_style)){
		$form_container_style[] = "border-style: {$theme_properties->border_form_style}";
	}
	
	if(!empty($theme_properties->border_form_color)){
		$form_container_style[] = "border-color: {$theme_properties->border_form_color}";
	}
	
	$form_container_style_tag = implode(';',$form_container_style);
	$form_container_style_tag = ' style="'.$form_container_style_tag.'" ';
	
	/** Field Highlight **/
	$field_highlight_style = array();
	
	if($theme_properties->highlight_bg_type == 'color'){
		$field_highlight_style[] = "background-color: {$theme_properties->highlight_bg_color}";
	}else if($theme_properties->highlight_bg_type == 'pattern'){
		$field_highlight_style[] = "background-image: url('images/form_resources/{$theme_properties->highlight_bg_pattern}')";
		$field_highlight_style[] = "background-repeat: repeat";
	}else if($theme_properties->highlight_bg_type == 'custom'){
		$field_highlight_style[] = "background-image: url('{$theme_properties->highlight_bg_custom}')";
		$field_highlight_style[] = "background-repeat: repeat";
	}
	
	$field_highlight_style_tag = implode(';',$field_highlight_style);
	$field_highlight_style_tag = ' style="'.$field_highlight_style_tag.'" ';
	
	/** Field Guidelines **/
	$field_guidelines_style = array();
	
	if($theme_properties->guidelines_bg_type == 'color'){
		$field_guidelines_style[] = "background-color: {$theme_properties->guidelines_bg_color}";
	}else if($theme_properties->guidelines_bg_type == 'pattern'){
		$field_guidelines_style[] = "background-image: url('images/form_resources/{$theme_properties->guidelines_bg_pattern}')";
		$field_guidelines_style[] = "background-repeat: repeat";
	}else if($theme_properties->guidelines_bg_type == 'custom'){
		$field_guidelines_style[] = "background-image: url('{$theme_properties->guidelines_bg_custom}')";
		$field_guidelines_style[] = "background-repeat: repeat";
	}
	
	//guidelines border
	$field_guidelines_style[] = "border-width: {$theme_properties->border_guidelines_width}px";
	
	if(!empty($theme_properties->border_guidelines_style)){
		$field_guidelines_style[] = "border-style: {$theme_properties->border_guidelines_style}";
	}
	
	if(!empty($theme_properties->border_guidelines_color)){
		$field_guidelines_style[] = "border-color: {$theme_properties->border_guidelines_color}";
	}
	
	$field_guidelines_style_tag = implode(';',$field_guidelines_style);
	$field_guidelines_style_tag = ' style="'.$field_guidelines_style_tag.'" ';

	//guidelines font
	$field_guidelines_text_style = array();
	
	if(!empty($theme_properties->guidelines_font_type)){
		$field_guidelines_text_style[] = "font-family: '{$theme_properties->guidelines_font_type}','Lucida Grande',Tahoma,Arial,sans-serif";
		$font_family_array[] = $theme_properties->guidelines_font_type;
	}
	
	if(!empty($theme_properties->guidelines_font_weight)){
		$field_guidelines_text_style[] = "font-weight: {$theme_properties->guidelines_font_weight}";
	}
	
	if(!empty($theme_properties->guidelines_font_style)){
		$field_guidelines_text_style[] = "font-style: {$theme_properties->guidelines_font_style}";
	}
	
	if(!empty($theme_properties->guidelines_font_size)){
		$field_guidelines_text_style[] = "font-size: {$theme_properties->guidelines_font_size}";
	}
	
	if(!empty($theme_properties->guidelines_font_color)){
		$field_guidelines_text_style[] = "color: {$theme_properties->guidelines_font_color}";
	}
	
	$field_guidelines_text_style_tag = implode(';',$field_guidelines_text_style);
	$field_guidelines_text_style_tag = ' style="'.$field_guidelines_text_style_tag.'" ';
	
	
	/** Field Box **/
	$field_box_style = array();
	
	if($theme_properties->field_bg_type == 'color'){
		$field_box_style[] = "background-color: {$theme_properties->field_bg_color}";
	}else if($theme_properties->field_bg_type == 'pattern'){
		$field_box_style[] = "background-image: url('images/form_resources/{$theme_properties->field_bg_pattern}')";
		$field_box_style[] = "background-repeat: repeat";
	}else if($theme_properties->field_bg_type == 'custom'){
		$field_box_style[] = "background-image: url('{$theme_properties->field_bg_custom}')";
		$field_box_style[] = "background-repeat: repeat";
	}
	
	//field text values
	if(!empty($theme_properties->field_text_font_type)){
		$field_box_style[] = "font-family: '{$theme_properties->field_text_font_type}','Lucida Grande',Tahoma,Arial,sans-serif";
		$font_family_array[] = $theme_properties->field_text_font_type;
	}
	
	if(!empty($theme_properties->field_text_font_weight)){
		$field_box_style[] = "font-weight: {$theme_properties->field_text_font_weight}";
	}
	
	if(!empty($theme_properties->field_text_font_style)){
		$field_box_style[] = "font-style: {$theme_properties->field_text_font_style}";
	}
	
	if(!empty($theme_properties->field_text_font_size)){
		$field_box_style[] = "font-size: {$theme_properties->field_text_font_size}";
	}
	
	if(!empty($theme_properties->field_text_font_color)){
		$field_box_style[] = "color: {$theme_properties->field_text_font_color}";
	}
	
	$field_box_style_tag = implode(';',$field_box_style);
	$field_box_style_tag = ' style="'.$field_box_style_tag.'" ';
	
	/** Form Title **/
	$form_title_style = array();
	
	if(!empty($theme_properties->form_title_font_type)){
		$form_title_style[] = "font-family: '{$theme_properties->form_title_font_type}','Lucida Grande',Tahoma,Arial,sans-serif";
		$font_family_array[] = $theme_properties->form_title_font_type;
	}
	
	if(!empty($theme_properties->form_title_font_weight)){
		$form_title_style[] = "font-weight: {$theme_properties->form_title_font_weight}";
	}
	
	if(!empty($theme_properties->form_title_font_style)){
		$form_title_style[] = "font-style: {$theme_properties->form_title_font_style}";
	}
	
	if(!empty($theme_properties->form_title_font_size)){
		$form_title_style[] = "font-size: {$theme_properties->form_title_font_size}";
	}
	
	if(!empty($theme_properties->form_title_font_color)){
		$form_title_style[] = "color: {$theme_properties->form_title_font_color}";
	}
	
	$form_title_style_tag = implode(';',$form_title_style);
	$form_title_style_tag = ' style="'.$form_title_style_tag.'" ';
	
	/** Form Description **/
	$form_desc_style = array();
	
	if(!empty($theme_properties->form_desc_font_type)){
		$form_desc_style[] = "font-family: '{$theme_properties->form_desc_font_type}','Lucida Grande',Tahoma,Arial,sans-serif";
		$font_family_array[] = $theme_properties->form_desc_font_type;
	}
	
	if(!empty($theme_properties->form_desc_font_weight)){
		$form_desc_style[] = "font-weight: {$theme_properties->form_desc_font_weight}";
	}
	
	if(!empty($theme_properties->form_desc_font_style)){
		$form_desc_style[] = "font-style: {$theme_properties->form_desc_font_style}";
	}
	
	if(!empty($theme_properties->form_desc_font_size)){
		$form_desc_style[] = "font-size: {$theme_properties->form_desc_font_size}";
	}
	
	if(!empty($theme_properties->form_desc_font_color)){
		$form_desc_style[] = "color: {$theme_properties->form_desc_font_color}";
	}
	
	$form_desc_style_tag = implode(';',$form_desc_style);
	$form_desc_style_tag = ' style="'.$form_desc_style_tag.'" ';
	
	/** Field Title **/
	$field_title_style = array();
	$field_sub_title_style = array();
	
	if(!empty($theme_properties->field_title_font_type)){
		$field_title_style[] = "font-family: '{$theme_properties->field_title_font_type}','Lucida Grande',Tahoma,Arial,sans-serif";
		$field_sub_title_style[] = "font-family: '{$theme_properties->field_title_font_type}','Lucida Grande',Tahoma,Arial,sans-serif";
		$font_family_array[] = $theme_properties->field_title_font_type;
	}
	
	if(!empty($theme_properties->field_title_font_weight)){
		$field_title_style[] = "font-weight: {$theme_properties->field_title_font_weight}";
	}
	
	if(!empty($theme_properties->field_title_font_style)){
		$field_title_style[] = "font-style: {$theme_properties->field_title_font_style}";
	}
	
	if(!empty($theme_properties->field_title_font_size)){
		$field_title_style[] = "font-size: {$theme_properties->field_title_font_size}";
	}
	
	if(!empty($theme_properties->field_title_font_color)){
		$field_title_style[] = "color: {$theme_properties->field_title_font_color}";
		$field_sub_title_style[] = "color: {$theme_properties->field_title_font_color}";
	}
	
	$field_title_style_tag = implode(';',$field_title_style);
	$field_title_style_tag = ' style="'.$field_title_style_tag.'" ';
	
	$field_sub_title_style_tag = implode(';',$field_sub_title_style);
	$field_sub_title_style_tag = ' style="'.$field_sub_title_style_tag.'" ';
	
	/** Section Title **/
	$section_title_style = array();
	
	if(!empty($theme_properties->section_title_font_type)){
		$section_title_style[] = "font-family: '{$theme_properties->section_title_font_type}','Lucida Grande',Tahoma,Arial,sans-serif";
		$font_family_array[] = $theme_properties->section_title_font_type;
	}
	
	if(!empty($theme_properties->section_title_font_weight)){
		$section_title_style[] = "font-weight: {$theme_properties->section_title_font_weight}";
	}
	
	if(!empty($theme_properties->section_title_font_style)){
		$section_title_style[] = "font-style: {$theme_properties->section_title_font_style}";
	}
	
	if(!empty($theme_properties->section_title_font_size)){
		$section_title_style[] = "font-size: {$theme_properties->section_title_font_size}";
	}
	
	if(!empty($theme_properties->section_title_font_color)){
		$section_title_style[] = "color: {$theme_properties->section_title_font_color}";
	}
	
	$section_title_style_tag = implode(';',$section_title_style);
	$section_title_style_tag = ' style="'.$section_title_style_tag.'" ';
	
	/** Section Description **/
	$section_desc_style = array();
	
	if(!empty($theme_properties->section_desc_font_type)){
		$section_desc_style[] = "font-family: '{$theme_properties->section_desc_font_type}','Lucida Grande',Tahoma,Arial,sans-serif";
		$font_family_array[] = $theme_properties->section_desc_font_type;
	}
	
	if(!empty($theme_properties->section_desc_font_weight)){
		$section_desc_style[] = "font-weight: {$theme_properties->section_desc_font_weight}";
	}
	
	if(!empty($theme_properties->section_desc_font_style)){
		$section_desc_style[] = "font-style: {$theme_properties->section_desc_font_style}";
	}
	
	if(!empty($theme_properties->section_desc_font_size)){
		$section_desc_style[] = "font-size: {$theme_properties->section_desc_font_size}";
	}
	
	if(!empty($theme_properties->section_desc_font_color)){
		$section_desc_style[] = "color: {$theme_properties->section_desc_font_color}";
	}
	
	$section_desc_style_tag = implode(';',$section_desc_style);
	$section_desc_style_tag = ' style="'.$section_desc_style_tag.'" ';
	
	/** Section Block **/
	$section_block_style = array();
	
	$section_block_style[] = "border-top-width: {$theme_properties->border_section_width}px";
	
	if(!empty($theme_properties->border_section_style)){
		$section_block_style[] = "border-top-style: {$theme_properties->border_section_style}";
	}
	
	if(!empty($theme_properties->border_section_color)){
		$section_block_style[] = "border-top-color: {$theme_properties->border_section_color}";
	}
	
	$section_block_style_tag = implode(';',$section_block_style);
	$section_block_style_tag = ' style="'.$section_block_style_tag.'" ';
	
	/** Form Shadow **/
	$form_container_class = '';
	
	if(!empty($theme_properties->form_shadow_style) && ($theme_properties->form_shadow_style != 'disabled')){
		preg_match_all("/[A-Z]/",$theme_properties->form_shadow_style,$prefix_matches);
		//this regex simply get the capital characters of the shadow style name
		//example: RightPerspectiveShadow result to RPS and then being sliced to RP
		$form_shadow_prefix_code = substr(implode("",$prefix_matches[0]),0,-1);
		
		$form_shadow_size_class  = $form_shadow_prefix_code.ucfirst($theme_properties->form_shadow_size);
		$form_shadow_brightness_class = $form_shadow_prefix_code.ucfirst($theme_properties->form_shadow_brightness);
		
		$form_container_class = $theme_properties->form_shadow_style.' '.$form_shadow_size_class.' '.$form_shadow_brightness_class;
	}
	
	/** Build the font CSS tag **/
	$font_css_markup = '';
	
	if(!empty($font_family_array)){
		$font_family_joined = implode("','",$font_family_array);
		
		$query = "SELECT font_family,font_variants FROM ".MF_TABLE_PREFIX."fonts WHERE font_family IN('{$font_family_joined}')";
		$params = array();
	
		$sth = mf_do_query($query,$params,$dbh);
		$font_css_array = array();
		while($row = mf_do_fetch_result($sth)){
			$font_css_array[] = urlencode($row['font_family']).":".$row['font_variants'];
		}

		if(!empty($font_css_array)){
			$font_css_markup = implode('|',$font_css_array);
			$font_css_markup = "<link href='http{$ssl_suffix}://fonts.googleapis.com/css?family={$font_css_markup}' rel='stylesheet' type='text/css'>\n";
		}
	}
	
	if($theme_properties->form_button_type == 'text'){
		$form_button_text_style_tag = '';
		$form_button_image_style_tag = 'style="display: none"';
	}else{
		$form_button_text_style_tag = 'style="display: none"';
		$form_button_image_style_tag = '';
	}
	/** End building form styles **/

	if(empty($theme_id)){
		$page_title = 'Theme Editor';
		$page_desc = 'Create a new theme for your forms.';
	}else{
		$page_title = 'Theme Editor <span class="icon-arrow-right2 breadcrumb_arrow"></span> '.htmlspecialchars($theme_properties->theme_name);
		
		if(empty($theme_properties->theme_is_private)){
			$page_title .= ' <span style="font-size: 70%;color: #BD3D20">(public)</span>';
		}

		$page_desc = 'You are currently editing <span style="color: #529214; font-weight: bold;">'.htmlspecialchars($theme_properties->theme_name).'</span> theme.';
	}
	
	//get the list of existing custom themes
	if(!empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$query = "SELECT count(*) as total_row FROM ".MF_TABLE_PREFIX."form_themes WHERE theme_built_in=0 and status=1";
		$params = array();
	}else{
		$query = "SELECT count(*) as total_row FROM ".MF_TABLE_PREFIX."form_themes WHERE theme_built_in=0 and status=1 and user_id=?";
		$params = array($_SESSION['mf_user_id']);
	}
	
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	$total_custom_themes = $row['total_row'];
	
	$custom_themes_list_markup = '';
	$builtin_themes_list_markup = '';
	
	if(!empty($total_custom_themes)){

		if(!empty($_SESSION['mf_user_privileges']['priv_administer'])){
			$query = "SELECT theme_id,theme_name,theme_is_private FROM ".MF_TABLE_PREFIX."form_themes WHERE theme_built_in=0 and status=1 ORDER BY theme_name ASC";
			$params = array();
		}else{
			$query = "SELECT theme_id,theme_name,theme_is_private FROM ".MF_TABLE_PREFIX."form_themes WHERE theme_built_in=0 and status=1 and user_id=? ORDER BY theme_name ASC";
			$params = array($_SESSION['mf_user_id']);
		}
		
		$sth = mf_do_query($query,$params,$dbh);
		
		while($row = mf_do_fetch_result($sth)){
			if(empty($row['theme_is_private'])){
				$row['theme_name'] .= ' *';
			}
			$custom_themes_list_markup .= '<li><a href="edit_theme.php?theme_id='.$row['theme_id'].'">'.htmlspecialchars($row['theme_name']).'</a></li>'."\n";
		}
			
		if($total_custom_themes > 5){ //if the theme exceed 5 rows, display scrollbar
			$custom_themes_list_markup = '<li><div class="dropui-sub-content"><ul class="dropui-sub-content-ul">'.$custom_themes_list_markup.'</ul></div></li>';
		}
		
		$custom_themes_list_markup = '<li class="sub_separator">Your Themes</li>'."\n".$custom_themes_list_markup;
		
	}
	
	//get the list of existing built-in themes
	$query = "SELECT count(*) as total_row FROM ".MF_TABLE_PREFIX."form_themes WHERE theme_built_in=1 and status=1";
	
	$params = array();
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	$total_builtin_themes = $row['total_row'];
	
	if(!empty($total_builtin_themes)){
		$query = "SELECT theme_id,theme_name FROM ".MF_TABLE_PREFIX."form_themes WHERE theme_built_in=1 and status=1 ORDER BY theme_name ASC";
		
		$params = array();
		$sth = mf_do_query($query,$params,$dbh);
		
		while($row = mf_do_fetch_result($sth)){
			$builtin_themes_list_markup .= '<li id="li_builtin_'.$row['theme_id'].'"><a href="#">'.htmlspecialchars($row['theme_name']).'</a></li>'."\n";
		}
			
		
		$builtin_themes_list_markup = '<li><div class="dropui-sub-content"><ul class="dropui-sub-content-ul" id="ul_builtin_themes">'.$builtin_themes_list_markup.'</ul></div></li>';
		$builtin_themes_list_markup = '<li class="sub_separator">Built-in Themes</li>'."\n".$builtin_themes_list_markup;
		
	}
	
	
	
	//build the json code for theme properties
	$session_id = session_id();
	$json_theme = json_encode($theme_properties);
	
	$jquery_data_code  = '';
	$jquery_data_code .= "\$('#et_theme_preview').data('theme_properties',{$json_theme});\n";
	$jquery_data_code .= "\$('#et_theme_preview').data('session_id','{$session_id}');\n";
	$jquery_data_code .= "\$('#header').data('last_font_id',0);\n";
	$jquery_data_code .= "\$('#header').data('font_styles', new Array());\n";
	
	//build the json code for the built-in theme properties
	$query = "SELECT
						theme_name,
						theme_id,
						theme_is_private,
						`status`,
						logo_type,
						ifnull(logo_custom_image,'') logo_custom_image,
						logo_custom_height,
						logo_default_image,
						wallpaper_bg_type,
						wallpaper_bg_color,
						wallpaper_bg_pattern,
						wallpaper_bg_custom,
						header_bg_type,
						header_bg_color,
						header_bg_pattern,
						header_bg_custom,
						form_bg_type,
						form_bg_color,
						form_bg_pattern,
						form_bg_custom,
						highlight_bg_type,
						highlight_bg_color,
						highlight_bg_pattern,
						highlight_bg_custom,
						guidelines_bg_type,
						guidelines_bg_color,
						guidelines_bg_pattern,
						guidelines_bg_custom,
						field_bg_type,
						field_bg_color,
						field_bg_pattern,
						field_bg_custom,
						form_title_font_type,
						form_title_font_weight,
						form_title_font_style,
						form_title_font_size,
						form_title_font_color,
						form_desc_font_type,
						form_desc_font_weight,
						form_desc_font_style,
						form_desc_font_size,
						form_desc_font_color,
						field_title_font_type,
						field_title_font_weight,
						field_title_font_style,
						field_title_font_size,
						field_title_font_color,
						guidelines_font_type,
						guidelines_font_weight,
						guidelines_font_style,
						guidelines_font_size,
						guidelines_font_color,
						section_title_font_type,
						section_title_font_weight,
						section_title_font_style,
						section_title_font_size,
						section_title_font_color,
						section_desc_font_type,
						section_desc_font_weight,
						section_desc_font_style,
						section_desc_font_size,
						section_desc_font_color,
						field_text_font_type,
						field_text_font_weight,
						field_text_font_style,
						field_text_font_size,
						field_text_font_color,
						border_form_width,
						border_form_style,
						border_form_color,
						border_guidelines_width,
						border_guidelines_style,
						border_guidelines_color,
						border_section_width,
						border_section_style,
						border_section_color,
						form_shadow_style,
						form_shadow_size,
						form_shadow_brightness,
						form_button_type,
						form_button_text,
						form_button_image,
						advanced_css,
						theme_built_in
					FROM
						`".MF_TABLE_PREFIX."form_themes`
				   WHERE
				   		theme_built_in=1 and `status`=1
				ORDER BY 
						theme_name ASC";
	$params = array();
		
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		
		$theme_builtin_properties = new stdClass();
		
		$theme_builtin_properties->theme_id 		   = (int) $row['theme_id'];
		$theme_builtin_properties->theme_is_private    = (int) $row['theme_is_private'];
		$theme_builtin_properties->theme_name  		   = $row['theme_name'];
		$theme_builtin_properties->status  		   	   = (int) $row['status'];
		$theme_builtin_properties->logo_type 		   = $row['logo_type']; 
		$theme_builtin_properties->logo_custom_image   = $row['logo_custom_image'];
		$theme_builtin_properties->logo_custom_height  = (int) $row['logo_custom_height'];
		$theme_builtin_properties->logo_default_image  = $row['logo_default_image'];
		$theme_builtin_properties->wallpaper_bg_type 	= $row['wallpaper_bg_type'];
		$theme_builtin_properties->wallpaper_bg_color 	= $row['wallpaper_bg_color'];
		$theme_builtin_properties->wallpaper_bg_pattern = $row['wallpaper_bg_pattern'];
		$theme_builtin_properties->wallpaper_bg_custom 	= $row['wallpaper_bg_custom'];
		$theme_builtin_properties->header_bg_type 		= $row['header_bg_type'];
		$theme_builtin_properties->header_bg_color 		= $row['header_bg_color'];
		$theme_builtin_properties->header_bg_pattern 	= $row['header_bg_pattern'];;
		$theme_builtin_properties->header_bg_custom 	= $row['header_bg_custom'];
		$theme_builtin_properties->form_bg_type 		= $row['form_bg_type'];
		$theme_builtin_properties->form_bg_color 		= $row['form_bg_color'];
		$theme_builtin_properties->form_bg_pattern 		= $row['form_bg_pattern'];;
		$theme_builtin_properties->form_bg_custom 		= $row['form_bg_custom'];
		$theme_builtin_properties->highlight_bg_type 	= $row['highlight_bg_type'];
		$theme_builtin_properties->highlight_bg_color 	= $row['highlight_bg_color'];
		$theme_builtin_properties->highlight_bg_pattern = $row['highlight_bg_pattern'];
		$theme_builtin_properties->highlight_bg_custom 	= $row['highlight_bg_custom'];
		$theme_builtin_properties->guidelines_bg_type 	= $row['guidelines_bg_type'];
		$theme_builtin_properties->guidelines_bg_color 	= $row['guidelines_bg_color'];
		$theme_builtin_properties->guidelines_bg_pattern = $row['guidelines_bg_pattern'];
		$theme_builtin_properties->guidelines_bg_custom  = $row['guidelines_bg_custom'];
		$theme_builtin_properties->field_bg_type 		 = $row['field_bg_type'];
		$theme_builtin_properties->field_bg_color 		 = $row['field_bg_color'];
		$theme_builtin_properties->field_bg_pattern 	 = $row['field_bg_pattern'];
		$theme_builtin_properties->field_bg_custom  	 = $row['field_bg_custom'];
		$theme_builtin_properties->form_title_font_type    = $row['form_title_font_type'];
		$theme_builtin_properties->form_title_font_weight  = (int) $row['form_title_font_weight'];
		$theme_builtin_properties->form_title_font_style   = $row['form_title_font_style'];
		$theme_builtin_properties->form_title_font_size    = $row['form_title_font_size'];
		$theme_builtin_properties->form_title_font_color   = $row['form_title_font_color'];
		$theme_builtin_properties->form_desc_font_type    = $row['form_desc_font_type'];
		$theme_builtin_properties->form_desc_font_weight  = (int) $row['form_desc_font_weight'];
		$theme_builtin_properties->form_desc_font_style   = $row['form_desc_font_style'];
		$theme_builtin_properties->form_desc_font_size    = $row['form_desc_font_size'];
		$theme_builtin_properties->form_desc_font_color   = $row['form_desc_font_color'];
		$theme_builtin_properties->field_title_font_type    = $row['field_title_font_type'];
		$theme_builtin_properties->field_title_font_weight  = (int) $row['field_title_font_weight'];
		$theme_builtin_properties->field_title_font_style   = $row['field_title_font_style'];
		$theme_builtin_properties->field_title_font_size    = $row['field_title_font_size'];
		$theme_builtin_properties->field_title_font_color   = $row['field_title_font_color'];
		$theme_builtin_properties->guidelines_font_type    = $row['guidelines_font_type'];
		$theme_builtin_properties->guidelines_font_weight  = (int) $row['guidelines_font_weight'];
		$theme_builtin_properties->guidelines_font_style   = $row['guidelines_font_style'];
		$theme_builtin_properties->guidelines_font_size    = $row['guidelines_font_size'];
		$theme_builtin_properties->guidelines_font_color   = $row['guidelines_font_color'];
		$theme_builtin_properties->section_title_font_type    = $row['section_title_font_type'];
		$theme_builtin_properties->section_title_font_weight  = (int) $row['section_title_font_weight'];
		$theme_builtin_properties->section_title_font_style   = $row['section_title_font_style'];
		$theme_builtin_properties->section_title_font_size    = $row['section_title_font_size'];
		$theme_builtin_properties->section_title_font_color   = $row['section_title_font_color'];
		$theme_builtin_properties->section_desc_font_type    = $row['section_desc_font_type'];
		$theme_builtin_properties->section_desc_font_weight  = (int) $row['section_desc_font_weight'];
		$theme_builtin_properties->section_desc_font_style   = $row['section_desc_font_style'];
		$theme_builtin_properties->section_desc_font_size    = $row['section_desc_font_size'];
		$theme_builtin_properties->section_desc_font_color   = $row['section_desc_font_color'];
		$theme_builtin_properties->field_text_font_type    = $row['field_text_font_type'];
		$theme_builtin_properties->field_text_font_weight  = (int) $row['field_text_font_weight'];
		$theme_builtin_properties->field_text_font_style   = $row['field_text_font_style'];
		$theme_builtin_properties->field_text_font_size    = $row['field_text_font_size'];
		$theme_builtin_properties->field_text_font_color   = $row['field_text_font_color'];
		$theme_builtin_properties->border_form_width   = (int) $row['border_form_width'];
		$theme_builtin_properties->border_form_style   = $row['border_form_style'];
		$theme_builtin_properties->border_form_color   = $row['border_form_color'];
		$theme_builtin_properties->border_guidelines_width   = (int) $row['border_guidelines_width'];
		$theme_builtin_properties->border_guidelines_style   = $row['border_guidelines_style'];
		$theme_builtin_properties->border_guidelines_color   = $row['border_guidelines_color'];
		$theme_builtin_properties->border_section_width   = (int) $row['border_section_width'];
		$theme_builtin_properties->border_section_style   = $row['border_section_style'];
		$theme_builtin_properties->border_section_color   = $row['border_section_color'];
		$theme_builtin_properties->form_shadow_style	  = $row['form_shadow_style'];
		$theme_builtin_properties->form_shadow_size	  	  = $row['form_shadow_size'];
		$theme_builtin_properties->form_shadow_brightness = $row['form_shadow_brightness'];
		$theme_builtin_properties->form_button_type	  	  = $row['form_button_type'];
		$theme_builtin_properties->form_button_text	  	  = $row['form_button_text'];
		$theme_builtin_properties->form_button_image	  = $row['form_button_image'];
		$theme_builtin_properties->advanced_css	  		  = $row['advanced_css'];
		
		$json_theme = json_encode($theme_builtin_properties);
		$jquery_data_code .= "\$('#li_builtin_{$row['theme_id']}').data('theme_builtin_properties',{$json_theme});\n";
		
		$font_link_markup = mf_theme_get_fonts_link($dbh,$theme_builtin_properties->theme_id);
		
		$json_font_link_markup = json_encode($font_link_markup);
		$jquery_data_code .= "\$('#li_builtin_{$row['theme_id']}').data('font_link',{$json_font_link_markup});\n";
		$jquery_data_code .= "\$('#li_builtin_{$row['theme_id']}').data('font_link_loaded',0);\n";
	}
	
	$header_data =<<<EOT
<link type="text/css" href="css/pagination_bright.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="css/dropui.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="css/et_view.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="css/shadow.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="css/jquery_minicolors.css{$mf_version_tag}" rel="stylesheet" />
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css{$mf_version_tag}" rel="stylesheet" />
{$font_css_markup}
EOT;

	$current_nav_tab = 'edit_theme';
	require('includes/header.php'); 
	
?>
		<div id="theme_editor_loading">
 			Loading... Please wait...
 		</div>

		<div id="content" class="full">
			<div class="post edit_theme">
				<div id="et_theme_buttons">
					<div id="et_theme_buttons_title">
						<div style="float: left">
							<h2><?php echo $page_title; ?></h2>
							<p><?php echo $page_desc; ?></p>
						</div>
						
						<?php if($is_builtin_theme == false){ ?>
						
						<div id="dropui_theme_options" style="float: right;">
							<div class="dropui dropuiquick dropui-icon dropui-menu dropui-pink dropui-right">
								<a href="javascript:;" class="dropui-tab" style="text-indent: 0; background-image: none;font-size: 110%;width: auto;padding: 6px">
									<span class="icon-settings"></span>
								</a>
							
								<div class="dropui-content">
									<ul>
										<li class="new_theme"><a href="edit_theme.php">Create New Theme</a></li>
										
										<?php if($is_builtin_theme === false){ ?>
										<li class="advanced_theme"><a id="advanced_css_link" href="#">Advanced CSS</a></li>
										<?php } ?>
										
										<?php if(!empty($theme_id)){ ?>
										<li class="duplicate_theme"><a id="duplicate_theme_link" href="#">Duplicate</a></li>
											<?php if($is_builtin_theme === false){ ?>
										<li class="delete_theme"><a id="delete_theme_link" href="#">Delete</a></li>
										<li class="rename_theme"><a id="rename_theme_link" href="#">Rename</a></li>
										
										<?php if(empty($theme_properties->theme_is_private)){ ?>
										<li class="set_private_theme"><a id="set_private_theme_link" href="#">Set as Private Theme</a></li>
										<?php }else{ ?>
										<li class="set_public_theme"><a id="set_public_theme_link" href="#">Share This Theme</a></li>
										<?php } ?>

										<?php }} ?>
										
										<?php echo $custom_themes_list_markup; ?>
										<?php echo $builtin_themes_list_markup; ?>
									</ul>
								</div>
							</div>
						</div>
						
						<div style="float: right;margin-right: 5px">
								<a href="#" id="button_save_theme" class="bb_button bb_small bb_green">
									<span class="icon-disk" style="margin-right: 5px"></span>Save Theme
								</a>
							
						</div>
						
						<?php } ?>
						
						<div style="clear: both; height: 1px"></div>
					</div>
					<div id="et_theme_buttons_tab" style="text-align: center; clear: both">
						 
						<ul id="et_theme_button_ul" class="pages bluegrey group">
							<li id="li_tab_logo" class="previous l-bullet">Logo</li>
							<li id="li_tab_backgrounds" class="page">Backgrounds</li>
							<li id="li_tab_fonts" class="page">Fonts</li>
							<li id="li_tab_borders" class="page">Borders</li>
							<li id="li_tab_shadows" class="page">Shadows</li>
							<li id="li_tab_buttons" class="r-bullet">Buttons</li>
						</ul>
						<br clear="all" />
					</div>
				</div>
				<div id="et_theme_preview" <?php echo $form_wallpaper_style_tag; ?> >
					<div id="main_body">
					<div id="form_container" class="<?php echo $form_container_class; ?>" <?php echo $form_container_style_tag; ?>>
					
						<h1 id="form_header_preview" <?php echo $form_header_style_tag; ?>><a id="form_logo_preview" <?php echo $form_logo_style_tag; ?>>Form Title</a></h1>
						<form id="form_theme_preview" class="appnitro top_label"  method="post" action="#main_body">
						<div class="form_description">
							<h2 id="form_title_preview" <?php echo $form_title_style_tag; ?>>Form Title</h2>
							<p id="form_desc_preview" <?php echo $form_desc_style_tag; ?>>This is form description. Useful for displaying a short description or any instructions.</p>
						</div>				
								
						<ul id="li_fields">
							<li id="li_1" >
								<label class="description" for="element_1" <?php echo $field_title_style_tag; ?>>Field Label </label>
								<div>
									<input id="element_1" name="element_1"  class="element text medium" type="text" value="This is sample field text" title=""  <?php echo $field_box_style_tag; ?> />
								</div> 
							</li>		
							<li id="li_2" class="highlighted" <?php echo $field_highlight_style_tag; ?>>
								<label class="description" <?php echo $field_title_style_tag; ?>>Highlighted Field <span id="required_2" class="required">*</span></label>
								<span>
									<input id="element_2_1" name="element_2_1" type="text" class="element text" maxlength="255" size="8" value="" <?php echo $field_box_style_tag; ?> />
									<label <?php echo $field_sub_title_style_tag; ?>>First</label>
								</span>
								<span>
									<input id="element_2_2" name="element_2_2" type="text" class="element text" maxlength="255" size="14" value="" <?php echo $field_box_style_tag; ?>/>
									<label <?php echo $field_sub_title_style_tag; ?>>Last</label>
								</span>
								<p class="guidelines" id="guide_2" <?php echo $field_guidelines_style_tag; ?>><small <?php echo $field_guidelines_text_style_tag; ?>>This is field guidelines. This will be displayed to your users while they're filling out particular field. </small></p> 
							</li>		
							<li id="li_4" class="section_break" <?php echo $section_block_style_tag; ?>>
								<h3 id="section_title_preview" <?php echo $section_title_style_tag; ?>>Section Title</h3>
								<p id="section_desc_preview" <?php echo $section_desc_style_tag; ?>>This is the description of the section break.</p>
							</li>		
							<li id="li_3" >
								<label class="description" for="element_3" <?php echo $field_title_style_tag; ?>>Field With Values </label>
								<div>
									<textarea id="element_3" name="element_3" class="element textarea small" rows="8" cols="90" <?php echo $field_box_style_tag; ?> >This is sample field text. The quick brown fox jumps over the lazy dog. The quick brown fox jumps over the lazy dog. The quick brown fox jumps over the lazy dog.</textarea>
							 	</div> 
							</li>
							<li id="li_buttons" class="buttons">
							    <input id="submit_form" class="button_text submit_button" type="button" name="submit_form" value="<?php echo $theme_properties->form_button_text; ?>" <?php echo $form_button_text_style_tag; ?>/>
							    <input id="submit_form_image" disabled="disabled" class="button_image submit_button" type="image" name="submit_form_image" alt="Submit" src="<?php echo $theme_properties->form_button_image; ?>" value="Submit" <?php echo $form_button_image_style_tag; ?>/>
							</li>
						</ul>
						</form>	
						
					</div><!--  /end of form_container -->
					
					<!-- start drop buttons -->
						<div id="dropui-form-logo" class="dropui dropui-blue dropui-circle dropui-left et-prop-logo">
							<img src="images/arrows/arrow_left_blue.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">A</a>
							<div class="dropui-content" style="width: 500px">
								<div class="dropui-content-header">
									<img src="images/icons/257.png" class="dropui-header-img" />
									<h6>Form Logo</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									<div style="float: left; width: 150px">
										<ul id="et_ul_form_logo">
											<li><input type="radio" title="Your Logo" name="et_form_logo" id="et_form_logo_custom" /> <label for="et_form_logo_custom">Your Logo</label></li>
											<li class="prop_selected"><input type="radio" title="Default Logo" name="et_form_logo" id="et_form_logo_default" /> <label for="et_form_logo_default">Default Logo</label></li>
											<li><input type="radio" title="None" name="et_form_logo" id="et_form_logo_none" /> <label for="et_form_logo_none">None</label></li>
										</ul>
									</div>
									<div id="et_form_logo_content">
										
										<div id="et_form_logo_custom_tab" style="display: none">
											<div id="et_form_logo_upload">
												<input id="et_form_logo_file" name="et_form_logo_file" class="element file" type="file" />
											</div>
											<div id="et_form_logo_advanced" style="display: none">
												<span style="display: block">
													<label for="et_your_logo_url">Enter Your Image URL:</label>
													<input type="text" value="http://" class="text" name="et_your_logo_url" id="et_your_logo_url" />
													<span style="font-size: 90%; margin-top: 3px;display: block">(maximum width: 640px)</span>
												</span>
												<span style="display: block;padding-top: 10px">
													<label for="et_your_logo_height">Height: </label>
													<input type="text" size="4" value="" class="text" name="et_your_logo_height" id="et_your_logo_height" /> px
												</span>
												<span style="display: block;padding-top: 10px">
													<input type="button" class="button_text" value="Apply" id="et_your_logo_submit" />
												</span>
											</div>
											<div style="margin-top: 10px;padding-right: 5px;text-align: right">
												<a id="et_form_logo_more" href="#">more options</a>
											</div>
										</div>
										
										<div id="et_form_logo_default_tab">
											<label for="et_logo_default_dropdown">Available Logo</label>
											<select class="select" size="15" id="et_logo_default_dropdown" name="et_logo_default_dropdown" style="height: 120px; width: 265px">
												<option value="machform.png">MachForm</option>
												<option value="">--------------</option>
												<option value="logo1.png">Fire</option>
												<option value="logo2.png">Tree</option>
												<option value="logo3.png">Orbit</option>
												<option value="logo4.png">Television</option>
												<option value="logo5.png">Gear</option>
												<option value="logo6.png">Clock</option>
												<option value="logo7.png">Lightning</option>
												<option value="logo8.png">Tag</option>
												<option value="logo9.png">Leaves</option>
												<option value="logo10.png">Heart</option>
												<option value="logo11.png">Mail</option>
												<option value="logo12.png">Pencil</option>
												<option value="logo13.png">Egg</option>
												<option value="logo14.png">Factory</option>
												<option value="logo15.png">Home</option>
												<option value="logo16.png">Palette</option>
												<option value="logo17.png">Chat Bubble</option>
												<option value="logo18.png">Shopping Cart</option>
												<option value="logo19.png">Tableware</option>
												<option value="logo20.png">Music</option>
												<option value="logo21.png">Hammer</option>
												<option value="logo22.png">Sun Flower</option>
												<option value="logo23.png">Lock</option>
												<option value="logo24.png">Headphone</option>
												<option value="logo25.png">Buildings</option>
												<option value="logo26.png">Bug</option>
												<option value="logo27.png">Bird</option>
												<option value="">--------------</option>
												<option value="cal1.png">Calligraphy 1</option>
												<option value="cal2.png">Calligraphy 2</option>
												<option value="cal3.png">Calligraphy 3</option>
												<option value="cal4.png">Calligraphy 4</option>
												<option value="cal5.png">Calligraphy 5</option>
												<option value="cal6.png">Calligraphy 6</option>
												<option value="cal7.png">Calligraphy 7</option>
												<option value="cal8.png">Calligraphy 8</option>
											</select>
										</div>
										
										
										<div id="et_form_logo_none_tab" style="display: none">
											<img src="images/icons/disabled_white.png"/>
											<h3 style="padding-top: 10px">Logo Disabled</h3>
										</div>
										
									</div>
									<div style="clear: both; height: 0px"></div>
								</div> <!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-form-logo -->
						
						<div id="dropui-bg-main" class="dropui dropui-green dropui-circle dropui-left et-prop-bg">
						<img src="images/arrows/arrow_down_green.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">A</a>
							<div class="dropui-content" style="width: 515px">
								<div class="dropui-content-header">
									<img src="images/icons/26.png" class="dropui-header-img" />
									<h6>Wallpaper</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									<div style="float: left; width: 150px">
										<ul id="et_ul_form_wallpaper">
											<li class="prop_selected"><input type="radio" name="et_form_wallpaper" id="et_form_wallpaper_color" /> <label for="et_form_wallpaper_color">Color</label></li>
											<li><input type="radio" name="et_form_wallpaper" id="et_form_wallpaper_pattern" /> <label for="et_form_wallpaper_pattern">Pattern</label></li>
											<li><input type="radio" name="et_form_wallpaper" id="et_form_wallpaper_custom" /> <label for="et_form_wallpaper_custom">Custom Image</label></li>
										</ul>
									</div>
									<div id="et_form_wallpaper_content">
										
										<div id="et_form_wallpaper_color_tab" >
											<div class="color_picker_tab">
												<?php echo get_color_picker_markup(); ?>
											</div>
											<div class="minicolors_tab">
												<div id="et_form_wallpaper_minicolor_box" class="minicolors_preview"></div>
												<div class="minicolors_container" >
													<input type="text" id="et_form_wallpaper_minicolor_input" name="et_form_wallpaper_minicolor_input" class="colors"  size="7" value="" />
												</div>
											</div>
											
										</div>
										
										<div id="et_form_wallpaper_pattern_tab" style="display: none">
											<div class="pattern_tab">
												<?php echo get_pattern_picker_markup(); ?>
											</div>
											<div class="pattern_preview_tab">
												<div id="et_form_wallpaper_pattern_box" class="pattern_preview"></div>
												<div id="et_form_wallpaper_pattern_number" style="text-align: center; width: 79px; padding-top: 5px"></div>
											</div>
										</div>
										
										
										<div id="et_form_wallpaper_custom_tab" style="display: none">
											<div id="et_wallpaper_custom_bg_upload">
												<input id="et_wallpaper_custom_bg_file" name="et_wallpaper_custom_bg_file" class="element file" type="file" />
											</div>
											<div id="et_wallpaper_custom_bg_advanced" style="display: none">
												<span style="display: block">
													<label for="et_wallpaper_custom_bg">Enter Your Image URL:</label>
													<input type="text" value="http://" class="text" name="et_wallpaper_custom_bg" id="et_wallpaper_custom_bg" />
												</span>
												<span style="display: block;padding-top: 10px">
													<input type="button" class="button_text" value="Apply" id="et_wallpaper_custom_bg_submit" />
												</span>
											</div>
											<div style="margin-top: 10px;padding-right: 5px;text-align: right">
												<a id="et_wallpaper_custom_bg_more" href="#">more options</a>
											</div>
										</div>
										
									</div>
									<div style="clear: both; height: 0px"></div>
								</div><!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-bg-main -->
						
						<div id="dropui-bg-header" class="dropui dropui-green dropui-circle dropui-right et-prop-bg">
							<img src="images/arrows/arrow_right_green.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">B</a>
							<div class="dropui-content" style="width: 515px">
								<div class="dropui-content-header">
									<img src="images/icons/26.png" class="dropui-header-img" />
									<h6>Header Background</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									<div style="float: left; width: 150px">
										<ul id="et_ul_form_headerbg">
											<li class="prop_selected"><input type="radio" name="et_form_headerbg" id="et_form_headerbg_color" /> <label for="et_form_headerbg_color">Color</label></li>
											<li><input type="radio" name="et_form_headerbg" id="et_form_headerbg_pattern" /> <label for="et_form_headerbg_pattern">Pattern</label></li>
											<li><input type="radio" name="et_form_headerbg" id="et_form_headerbg_custom" /> <label for="et_form_headerbg_custom">Custom Image</label></li>
										</ul>
									</div>
									<div id="et_form_headerbg_content">
										
										<div id="et_form_headerbg_color_tab" >
											<div class="color_picker_tab">
												<?php echo get_color_picker_markup(); ?>
											</div>
											<div class="minicolors_tab">
												<div id="et_form_headerbg_minicolor_box" class="minicolors_preview"></div>
												<div class="minicolors_container" >
													<input type="text" id="et_form_headerbg_minicolor_input" name="et_form_headerbg_minicolor_input" class="colors"  size="7" value="" />
												</div>
											</div>
											
										</div>
										
										<div id="et_form_headerbg_pattern_tab" style="display: none">
											<div class="pattern_tab">
												<?php echo get_pattern_picker_markup(); ?>
											</div>
											<div class="pattern_preview_tab">
												<div id="et_form_headerbg_pattern_box" class="pattern_preview"></div>
												<div id="et_form_headerbg_pattern_number" style="text-align: center; width: 79px; padding-top: 5px"></div>
											</div>
										</div>
										
										
										<div id="et_form_headerbg_custom_tab" style="display: none">
											<div id="et_headerbg_custom_bg_upload">
												<input id="et_headerbg_custom_bg_file" name="et_headerbg_custom_bg_file" class="element file" type="file" />
											</div>
											<div id="et_headerbg_custom_bg_advanced" style="display: none">
												<span style="display: block">
													<label for="et_headerbg_custom_bg">Enter Your Image URL:</label>
													<input type="text" value="http://" class="text" name="et_headerbg_custom_bg" id="et_headerbg_custom_bg" />
												</span>
												<span style="display: block;padding-top: 10px">
													<input type="button" class="button_text" value="Apply" id="et_headerbg_custom_bg_submit" />
												</span>
											</div>
											<div style="margin-top: 10px;padding-right: 5px;text-align: right">
												<a id="et_headerbg_custom_bg_more" href="#">more options</a>
											</div>

										</div>
										
									</div>
									<div style="clear: both; height: 0px"></div>
								</div><!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-bg-header -->
						
						<div id="dropui-bg-form" class="dropui dropui-green dropui-circle dropui-right et-prop-bg">
						<img src="images/arrows/arrow_right_medium_green.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">C</a>
							<div class="dropui-content" style="width: 515px">
								<div class="dropui-content-header">
									<img src="images/icons/26.png" class="dropui-header-img" />
									<h6>Form Background</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									<div style="float: left; width: 150px">
										<ul id="et_ul_form_formbg">
											<li class="prop_selected"><input type="radio" name="et_form_formbg" id="et_form_formbg_color" /> <label for="et_form_formbg_color">Color</label></li>
											<li><input type="radio" name="et_form_formbg" id="et_form_formbg_pattern" /> <label for="et_form_formbg_pattern">Pattern</label></li>
											<li><input type="radio" name="et_form_formbg" id="et_form_formbg_custom" /> <label for="et_form_formbg_custom">Custom Image</label></li>
										</ul>
									</div>
									<div id="et_form_formbg_content">
										
										<div id="et_form_formbg_color_tab" >
											<div class="color_picker_tab">
												<?php echo get_color_picker_markup(); ?>
											</div>
											<div class="minicolors_tab">
												<div id="et_form_formbg_minicolor_box" class="minicolors_preview"></div>
												<div class="minicolors_container" >
													<input type="text" id="et_form_formbg_minicolor_input" name="et_form_formbg_minicolor_input" class="colors"  size="7" value="" />
												</div>
											</div>
											
										</div>
										
										<div id="et_form_formbg_pattern_tab" style="display: none">
											<div class="pattern_tab">
												<?php echo get_pattern_picker_markup(); ?>
											</div>
											<div class="pattern_preview_tab">
												<div id="et_form_formbg_pattern_box" class="pattern_preview"></div>
												<div id="et_form_formbg_pattern_number" style="text-align: center; width: 79px; padding-top: 5px"></div>
											</div>
										</div>
										
										
										<div id="et_form_formbg_custom_tab" style="display: none">
											<div id="et_form_formbg_custom_upload">
												<input id="et_form_formbg_custom_file" name="et_form_formbg_custom_file" class="element file" type="file" />
											</div>
											<div id="et_form_formbg_custom_advanced" style="display: none">
												<span style="display: block">
													<label for="et_formbg_custom_bg">Enter Your Image URL:</label>
													<input type="text" value="http://" class="text" name="et_formbg_custom_bg" id="et_formbg_custom_bg" />
												</span>
												<span style="display: block;padding-top: 10px">
													<input type="button" class="button_text" value="Apply" id="et_formbg_custom_bg_submit" />
												</span>
											</div>
											<div style="margin-top: 10px;padding-right: 5px;text-align: right">
												<a id="et_form_formbg_custom_more" href="#">more options</a>
											</div>
										</div>
										
									</div>
									<div style="clear: both; height: 0px"></div>
								</div><!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-bg-form -->
						
						<div id="dropui-bg-highlight" class="dropui dropui-green dropui-circle dropui-right et-prop-bg">
							<img src="images/arrows/arrow_diagonal_bottom_right_green.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">D</a>
							<div class="dropui-content" style="width: 515px">
								<div class="dropui-content-header">
									<img src="images/icons/26.png" class="dropui-header-img" />
									<h6>Highlight Color</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									<div style="float: left; width: 150px">
										<ul id="et_ul_form_highlightbg">
											<li class="prop_selected"><input type="radio" name="et_form_highlightbg" id="et_form_highlightbg_color" /> <label for="et_form_highlightbg_color">Color</label></li>
											<li><input type="radio" name="et_form_highlightbg" id="et_form_highlightbg_pattern" /> <label for="et_form_highlightbg_pattern">Pattern</label></li>
											<li><input type="radio" name="et_form_highlightbg" id="et_form_highlightbg_custom" /> <label for="et_form_highlightbg_custom">Custom Image</label></li>
										</ul>
									</div>
									<div id="et_form_highlightbg_content">
										
										<div id="et_form_highlightbg_color_tab" >
											<div class="color_picker_tab">
												<?php echo get_color_picker_markup(); ?>
											</div>
											<div class="minicolors_tab">
												<div id="et_form_highlightbg_minicolor_box" class="minicolors_preview"></div>
												<div class="minicolors_container" >
													<input type="text" id="et_form_highlightbg_minicolor_input" name="et_form_highlightbg_minicolor_input" class="colors"  size="7" value="" />
												</div>
											</div>
											
										</div>
										
										<div id="et_form_highlightbg_pattern_tab" style="display: none">
											<div class="pattern_tab">
												<?php echo get_pattern_picker_markup(); ?>
											</div>
											<div class="pattern_preview_tab">
												<div id="et_form_highlightbg_pattern_box" class="pattern_preview"></div>
												<div id="et_form_highlightbg_pattern_number" style="text-align: center; width: 79px; padding-top: 5px"></div>
											</div>
										</div>
										
										
										<div id="et_form_highlightbg_custom_tab" style="display: none">
											<div id="et_form_highlightbg_custom_upload">
												<input id="et_form_highlightbg_custom_file" name="et_form_highlightbg_custom_file" class="element file" type="file" />
											</div>
											<div id="et_form_highlightbg_custom_advanced" style="display: none">
												<span style="display: block">
													<label for="et_highlightbg_custom_bg">Enter Your Image URL:</label>
													<input type="text" value="http://" class="text" name="et_highlightbg_custom_bg" id="et_highlightbg_custom_bg" />
												</span>
												<span style="display: block;padding-top: 10px">
													<input type="button" class="button_text" value="Apply" id="et_highlightbg_custom_bg_submit" />
												</span>
											</div>
											<div style="margin-top: 10px;padding-right: 5px;text-align: right">
												<a id="et_form_highlightbg_custom_more" href="#">more options</a>
											</div>
										</div>
										
									</div>
									<div style="clear: both; height: 0px"></div>
								</div><!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-bg-highlight -->
						
						<div id="dropui-bg-guidelines" class="dropui dropui-green dropui-circle dropui-right et-prop-bg">
						<img src="images/arrows/arrow_right_green.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">E</a>
							<div class="dropui-content" style="width: 515px">
								<div class="dropui-content-header">
									<img src="images/icons/26.png" class="dropui-header-img" />
									<h6>Guidelines Background</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									<div style="float: left; width: 150px">
										<ul id="et_ul_form_guidelinesbg">
											<li class="prop_selected"><input type="radio" name="et_form_guidelinesbg" id="et_form_guidelinesbg_color" /> <label for="et_form_guidelinesbg_color">Color</label></li>
											<li><input type="radio" name="et_form_guidelinesbg" id="et_form_guidelinesbg_pattern" /> <label for="et_form_guidelinesbg_pattern">Pattern</label></li>
											<li><input type="radio" name="et_form_guidelinesbg" id="et_form_guidelinesbg_custom" /> <label for="et_form_guidelinesbg_custom">Custom Image</label></li>
										</ul>
									</div>
									<div id="et_form_guidelinesbg_content">
										
										<div id="et_form_guidelinesbg_color_tab" >
											<div class="color_picker_tab">
												<?php echo get_color_picker_markup(); ?>
											</div>
											<div class="minicolors_tab">
												<div id="et_form_guidelinesbg_minicolor_box" class="minicolors_preview"></div>
												<div class="minicolors_container" >
													<input type="text" id="et_form_guidelinesbg_minicolor_input" name="et_form_guidelinesbg_minicolor_input" class="colors"  size="7" value="" />
												</div>
											</div>
											
										</div>
										
										<div id="et_form_guidelinesbg_pattern_tab" style="display: none">
											<div class="pattern_tab">
												<?php echo get_pattern_picker_markup(); ?>
											</div>
											<div class="pattern_preview_tab">
												<div id="et_form_guidelinesbg_pattern_box" class="pattern_preview"></div>
												<div id="et_form_guidelinesbg_pattern_number" style="text-align: center; width: 79px; padding-top: 5px"></div>
											</div>
										</div>
										
										
										<div id="et_form_guidelinesbg_custom_tab" style="display: none">
											<div id="et_form_guidelinesbg_custom_upload">
												<input id="et_form_guidelinesbg_custom_file" name="et_form_guidelinesbg_custom_file" class="element file" type="file" />
											</div>
											<div id="et_form_guidelinesbg_custom_advanced" style="display: none">
												<span style="display: block">
													<label for="et_guidelinesbg_custom_bg">Enter Your Image URL:</label>
													<input type="text" value="http://" class="text" name="et_guidelinesbg_custom_bg" id="et_guidelinesbg_custom_bg" />
												</span>
												<span style="display: block;padding-top: 10px">
													<input type="button" class="button_text" value="Apply" id="et_guidelinesbg_custom_bg_submit" />
												</span>
											</div>
											<div style="margin-top: 10px;padding-right: 5px;text-align: right">
												<a id="et_form_guidelinesbg_custom_more" href="#">more options</a>
											</div>
										</div>
										
									</div>
									<div style="clear: both; height: 0px"></div>
								</div><!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-bg-guidelines -->
						
						<div id="dropui-bg-field" class="dropui dropui-green dropui-circle dropui-left et-prop-bg">
						<img src="images/arrows/arrow_left_green.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">F</a>
							<div class="dropui-content" style="width: 515px">
								<div class="dropui-content-header">
									<img src="images/icons/26.png" class="dropui-header-img" />
									<h6>Field Background</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									<div style="float: left; width: 150px">
										<ul id="et_ul_form_fieldbg">
											<li class="prop_selected"><input type="radio" name="et_form_fieldbg" id="et_form_fieldbg_color" /> <label for="et_form_fieldbg_color">Color</label></li>
											<li><input type="radio" name="et_form_fieldbg" id="et_form_fieldbg_pattern" /> <label for="et_form_fieldbg_pattern">Pattern</label></li>
											<li><input type="radio" name="et_form_fieldbg" id="et_form_fieldbg_custom" /> <label for="et_form_fieldbg_custom">Custom Image</label></li>
										</ul>
									</div>
									<div id="et_form_fieldbg_content">
										
										<div id="et_form_fieldbg_color_tab" >
											<div class="color_picker_tab">
												<?php echo get_color_picker_markup(); ?>
											</div>
											<div class="minicolors_tab">
												<div id="et_form_fieldbg_minicolor_box" class="minicolors_preview"></div>
												<div class="minicolors_container" >
													<input type="text" id="et_form_fieldbg_minicolor_input" name="et_form_fieldbg_minicolor_input" class="colors"  size="7" value="" />
												</div>
											</div>
											
										</div>
										
										<div id="et_form_fieldbg_pattern_tab" style="display: none">
											<div class="pattern_tab">
												<?php echo get_pattern_picker_markup(); ?>
											</div>
											<div class="pattern_preview_tab">
												<div id="et_form_fieldbg_pattern_box" class="pattern_preview"></div>
												<div id="et_form_fieldbg_pattern_number" style="text-align: center; width: 79px; padding-top: 5px"></div>
											</div>
										</div>
										
										
										<div id="et_form_fieldbg_custom_tab" style="display: none">
											<div id="et_form_fieldbg_custom_upload">
												<input id="et_form_fieldbg_custom_file" name="et_form_fieldbg_custom_file" class="element file" type="file" />
											</div>
											<div id="et_form_fieldbg_custom_advanced" style="display: none">
												<span style="display: block">
													<label for="et_fieldbg_custom_bg">Enter Your Image URL:</label>
													<input type="text" value="http://" class="text" name="et_fieldbg_custom_bg" id="et_fieldbg_custom_bg" />
												</span>
												<span style="display: block;padding-top: 10px">
													<input type="button" class="button_text" value="Apply" id="et_fieldbg_custom_bg_submit" />
												</span>
											</div>
											<div style="margin-top: 10px;padding-right: 5px;text-align: right">
												<a id="et_form_fieldbg_custom_more" href="#">more options</a>
											</div>
										</div>
										
									</div>
									<div style="clear: both; height: 0px"></div>
								</div><!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-bg-field -->
						
						<div id="dropui-typo-form-title" class="dropui dropui-orange dropui-circle dropui-left et-prop-typo">
							<img src="images/arrows/arrow_left_orange.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">A</a>
							<div class="dropui-content" style="width: 507px">
								<div class="dropui-content-header">
									<img src="images/icons/28.png" class="dropui-header-img" />
									<h6>Form Title</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									
									<div style="padding-bottom: 10px">
											<ul id="et_ul_typo_form_title">
												<li id="et_li_typo_form_title_font" class="tab_left tab_selected">Font</li>
												<li id="et_li_typo_form_title_style">Style</li>
												<li id="et_li_typo_form_title_size">Size</li>
												<li id="et_li_typo_form_title_color" class="tab_right">Color</li>
											</ul>
									</div>
										
									<div id="et_typo_form_title_content">
										
										<div id="et_li_typo_form_title_font_tab">
											<div class="font_picker_tab">
												<?php echo get_font_picker_markup(); ?>
											</div>
											<div class="font_preview_tab">
												<div id="et_form_title_font_preview_box" class="font_preview">AaBb</div>
												<div id="et_form_title_font_preview_name" style="text-align: center; width: 79px; padding-top: 5px">Lucida Grande</div>
											</div>
										</div>
										
										<div id="et_li_typo_form_title_style_tab" style="display: none">
											<ul id="et_ul_typo_form_title_style" class="et_li_style_picker">
												<li class="dummy_li"></li>
											</ul>
										</div>
										
										<div id="et_li_typo_form_title_size_tab" style="display: none">
											<ul id="et_typo_form_title_size_pickerbox" class="et_li_size_picker">
												<li data-fsize="80%">8</li>
												<li data-fsize="85%">9</li>
												<li data-fsize="95%">10</li>
												<li data-fsize="100%">11</li>
												<li data-fsize="110%">12</li>
												<li data-fsize="130%">13</li>
												<li data-fsize="140%">14</li>
												<li data-fsize="150%">15</li>
												<li data-fsize="160%" class="default_fsize">16</li>
												<li data-fsize="170%">17</li>
												<li data-fsize="180%">18</li>
												<li data-fsize="240%">24</li>
												<li data-fsize="280%">28</li>
												<li data-fsize="320%">32</li>
												<li data-fsize="360%">36</li>
												<li data-fsize="400%">40</li>
												<li data-fsize="450%">45</li>
												<li data-fsize="640%">64</li>
											</ul>
										</div>
										
										<div id="et_li_typo_form_title_color_tab" style="display: none">
											<div class="color_picker_tab">
												<?php echo get_color_picker_markup(); ?>
											</div>
											<div class="minicolors_tab">
												<div id="et_typo_form_title_minicolor_box" class="minicolors_preview"></div>
												<div class="minicolors_container" >
													<input type="text" id="et_typo_form_title_minicolor_input" name="et_typo_form_title_minicolor_input" class="colors"  size="7" value="" />
												</div>
											</div>
										</div>
															
									</div>
									<div style="clear: both; height: 0px"></div>
								</div><!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-typo-form-title -->
						
						<div id="dropui-typo-form-desc" class="dropui dropui-orange dropui-circle dropui-right et-prop-typo">
							<img src="images/arrows/arrow_right_orange_long.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">B</a>
							<div class="dropui-content" style="width: 507px">
								<div class="dropui-content-header">
									<img src="images/icons/28.png" class="dropui-header-img" />
									<h6>Form Description</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									
									<div style="padding-bottom: 10px">
											<ul id="et_ul_typo_form_desc">
												<li id="et_li_typo_form_desc_font" class="tab_left tab_selected">Font</li>
												<li id="et_li_typo_form_desc_style">Style</li>
												<li id="et_li_typo_form_desc_size">Size</li>
												<li id="et_li_typo_form_desc_color" class="tab_right">Color</li>
											</ul>
									</div>
										
									<div id="et_typo_form_desc_content">
										
										<div id="et_li_typo_form_desc_font_tab">
											<div class="font_picker_tab">
												<?php echo get_font_picker_markup(); ?>
											</div>
											<div class="font_preview_tab">
												<div id="et_form_desc_font_preview_box" class="font_preview">AaBb</div>
												<div id="et_form_desc_font_preview_name" style="text-align: center; width: 79px; padding-top: 5px">Lucida Grande</div>
											</div>
										</div>
										
										<div id="et_li_typo_form_desc_style_tab" style="display: none">
											<ul id="et_ul_typo_form_desc_style" class="et_li_style_picker">
												<li class="dummy_li"></li>
											</ul>
										</div>
										
										<div id="et_li_typo_form_desc_size_tab" style="display: none">
											<ul id="et_typo_form_desc_size_pickerbox" class="et_li_size_picker">
												<li data-fsize="80%">8</li>
												<li data-fsize="85%">9</li>
												<li data-fsize="95%" class="default_fsize">10</li>
												<li data-fsize="100%">11</li>
												<li data-fsize="110%">12</li>
												<li data-fsize="130%">13</li>
												<li data-fsize="140%">14</li>
												<li data-fsize="150%">15</li>
												<li data-fsize="160%">16</li>
												<li data-fsize="170%">17</li>
												<li data-fsize="180%">18</li>
												<li data-fsize="240%">24</li>
												<li data-fsize="280%">28</li>
												<li data-fsize="320%">32</li>
												<li data-fsize="360%">36</li>
												<li data-fsize="400%">40</li>
												<li data-fsize="450%">45</li>
												<li data-fsize="640%">64</li>
											</ul>
										</div>
										
										<div id="et_li_typo_form_desc_color_tab" style="display: none">
											<div class="color_picker_tab">
												<?php echo get_color_picker_markup(); ?>
											</div>
											<div class="minicolors_tab">
												<div id="et_typo_form_desc_minicolor_box" class="minicolors_preview"></div>
												<div class="minicolors_container" >
													<input type="text" id="et_typo_form_desc_minicolor_input" name="et_typo_form_desc_minicolor_input" class="colors"  size="7" value="" />
												</div>
											</div>
										</div>
												
									</div>
									<div style="clear: both; height: 0px"></div>
								</div><!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-typo-form-desc -->
						
						<div id="dropui-typo-field-title" class="dropui dropui-orange dropui-circle dropui-left et-prop-typo">
							<img src="images/arrows/arrow_left_orange.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">C</a>
							<div class="dropui-content" style="width: 507px">
								<div class="dropui-content-header">
									<img src="images/icons/28.png" class="dropui-header-img" />
									<h6>Field Label</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									
									<div style="padding-bottom: 10px">
											<ul id="et_ul_typo_field_title">
												<li id="et_li_typo_field_title_font" class="tab_left tab_selected">Font</li>
												<li id="et_li_typo_field_title_style">Style</li>
												<li id="et_li_typo_field_title_size">Size</li>
												<li id="et_li_typo_field_title_color" class="tab_right">Color</li>
											</ul>
									</div>
										
									<div id="et_typo_field_title_content">
										
										<div id="et_li_typo_field_title_font_tab">
											<div class="font_picker_tab">
												<?php echo get_font_picker_markup(); ?>
											</div>
											<div class="font_preview_tab">
												<div id="et_field_title_font_preview_box" class="font_preview">AaBb</div>
												<div id="et_field_title_font_preview_name" style="text-align: center; width: 79px; padding-top: 5px">Lucida Grande</div>
											</div>
										</div>
										
										<div id="et_li_typo_field_title_style_tab" style="display: none">
											<ul id="et_ul_typo_field_title_style" class="et_li_style_picker">
												<li class="dummy_li"></li>
											</ul>
										</div>
										
										<div id="et_li_typo_field_title_size_tab" style="display: none">
											<ul id="et_typo_field_title_size_pickerbox" class="et_li_size_picker">
												<li data-fsize="80%">8</li>
												<li data-fsize="85%">9</li>
												<li data-fsize="95%" class="default_fsize">10</li>
												<li data-fsize="100%">11</li>
												<li data-fsize="110%">12</li>
												<li data-fsize="130%">13</li>
												<li data-fsize="140%">14</li>
												<li data-fsize="150%">15</li>
												<li data-fsize="160%">16</li>
												<li data-fsize="170%">17</li>
												<li data-fsize="180%">18</li>
												<li data-fsize="240%">24</li>
												<li data-fsize="280%">28</li>
												<li data-fsize="320%">32</li>
												<li data-fsize="360%">36</li>
												<li data-fsize="400%">40</li>
												<li data-fsize="450%">45</li>
												<li data-fsize="640%">64</li>
											</ul>
										</div>
										
										<div id="et_li_typo_field_title_color_tab" style="display: none">
											<div class="color_picker_tab">
												<?php echo get_color_picker_markup(); ?>
											</div>
											<div class="minicolors_tab">
												<div id="et_typo_field_title_minicolor_box" class="minicolors_preview"></div>
												<div class="minicolors_container" >
													<input type="text" id="et_typo_field_title_minicolor_input" name="et_typo_field_title_minicolor_input" class="colors"  size="7" value="" />
												</div>
											</div>
										</div>
												
									</div>
									<div style="clear: both; height: 0px"></div>
								</div><!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-typo-field-title -->
						
						<div id="dropui-typo-guidelines" class="dropui dropui-orange dropui-circle dropui-right et-prop-typo">
							<img src="images/arrows/arrow_right_orange.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">D</a>
							<div class="dropui-content" style="width: 507px">
								<div class="dropui-content-header">
									<img src="images/icons/28.png" class="dropui-header-img" />
									<h6>Guidelines Text</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									
									<div style="padding-bottom: 10px">
											<ul id="et_ul_typo_guidelines">
												<li id="et_li_typo_guidelines_font" class="tab_left tab_selected">Font</li>
												<li id="et_li_typo_guidelines_style">Style</li>
												<li id="et_li_typo_guidelines_size">Size</li>
												<li id="et_li_typo_guidelines_color" class="tab_right">Color</li>
											</ul>
									</div>
										
									<div id="et_typo_guidelines_content">
										
										<div id="et_li_typo_guidelines_font_tab">
											<div class="font_picker_tab">
												<?php echo get_font_picker_markup(); ?>
											</div>
											<div class="font_preview_tab">
												<div id="et_guidelines_font_preview_box" class="font_preview">AaBb</div>
												<div id="et_guidelines_font_preview_name" style="text-align: center; width: 79px; padding-top: 5px">Lucida Grande</div>
											</div>
										</div>
										
										<div id="et_li_typo_guidelines_style_tab" style="display: none">
											<ul id="et_ul_typo_guidelines_style" class="et_li_style_picker">
												<li class="dummy_li"></li>
											</ul>
										</div>
										
										<div id="et_li_typo_guidelines_size_tab" style="display: none">
											<ul id="et_typo_guidelines_size_pickerbox" class="et_li_size_picker">
												<li data-fsize="80%" class="default_fsize">8</li>
												<li data-fsize="85%">9</li>
												<li data-fsize="95%">10</li>
												<li data-fsize="100%">11</li>
												<li data-fsize="110%">12</li>
												<li data-fsize="130%">13</li>
												<li data-fsize="140%">14</li>
												<li data-fsize="150%">15</li>
												<li data-fsize="160%">16</li>
												<li data-fsize="170%">17</li>
												<li data-fsize="180%">18</li>
												<li data-fsize="240%">24</li>
												<li data-fsize="280%">28</li>
												<li data-fsize="320%">32</li>
												<li data-fsize="360%">36</li>
												<li data-fsize="400%">40</li>
												<li data-fsize="450%">45</li>
												<li data-fsize="640%">64</li>
											</ul>
										</div>
										
										<div id="et_li_typo_guidelines_color_tab" style="display: none">
											<div class="color_picker_tab">
												<?php echo get_color_picker_markup(); ?>
											</div>
											<div class="minicolors_tab">
												<div id="et_typo_guidelines_minicolor_box" class="minicolors_preview"></div>
												<div class="minicolors_container" >
													<input type="text" id="et_typo_guidelines_minicolor_input" name="et_typo_guidelines_minicolor_input" class="colors"  size="7" value="" />
												</div>
											</div>
										</div>
												
									</div>
									<div style="clear: both; height: 0px"></div>
								</div><!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-typo-guidelines -->
						
						<div id="dropui-typo-section-title" class="dropui dropui-orange dropui-circle dropui-left et-prop-typo">
							<img src="images/arrows/arrow_left_orange.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">E</a>
							<div class="dropui-content" style="width: 507px">
								<div class="dropui-content-header">
									<img src="images/icons/28.png" class="dropui-header-img" />
									<h6>Section Break Title</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									
									<div style="padding-bottom: 10px">
											<ul id="et_ul_typo_section_title">
												<li id="et_li_typo_section_title_font" class="tab_left tab_selected">Font</li>
												<li id="et_li_typo_section_title_style">Style</li>
												<li id="et_li_typo_section_title_size">Size</li>
												<li id="et_li_typo_section_title_color" class="tab_right">Color</li>
											</ul>
									</div>
										
									<div id="et_typo_section_title_content">
										
										<div id="et_li_typo_section_title_font_tab">
											<div class="font_picker_tab">
												<?php echo get_font_picker_markup(); ?>
											</div>
											<div class="font_preview_tab">
												<div id="et_section_title_font_preview_box" class="font_preview">AaBb</div>
												<div id="et_section_title_font_preview_name" style="text-align: center; width: 79px; padding-top: 5px">Lucida Grande</div>
											</div>
										</div>
										
										<div id="et_li_typo_section_title_style_tab" style="display: none">
											<ul id="et_ul_typo_section_title_style" class="et_li_style_picker">
												<li class="dummy_li"></li>
											</ul>
										</div>
										
										<div id="et_li_typo_section_title_size_tab" style="display: none">
											<ul id="et_typo_section_title_size_pickerbox" class="et_li_size_picker">
												<li data-fsize="80%">8</li>
												<li data-fsize="85%">9</li>
												<li data-fsize="95%">10</li>
												<li data-fsize="100%">11</li>
												<li data-fsize="110%" class="default_fsize">12</li>
												<li data-fsize="130%">13</li>
												<li data-fsize="140%">14</li>
												<li data-fsize="150%">15</li>
												<li data-fsize="160%">16</li>
												<li data-fsize="170%">17</li>
												<li data-fsize="180%">18</li>
												<li data-fsize="240%">24</li>
												<li data-fsize="280%">28</li>
												<li data-fsize="320%">32</li>
												<li data-fsize="360%">36</li>
												<li data-fsize="400%">40</li>
												<li data-fsize="450%">45</li>
												<li data-fsize="640%">64</li>
											</ul>
										</div>
										
										<div id="et_li_typo_section_title_color_tab" style="display: none">
											<div class="color_picker_tab">
												<?php echo get_color_picker_markup(); ?>
											</div>
											<div class="minicolors_tab">
												<div id="et_typo_section_title_minicolor_box" class="minicolors_preview"></div>
												<div class="minicolors_container" >
													<input type="text" id="et_typo_section_title_minicolor_input" name="et_typo_section_title_minicolor_input" class="colors"  size="7" value="" />
												</div>
											</div>
										</div>
												
									</div>
									<div style="clear: both; height: 0px"></div>
								</div><!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-typo-section-title -->
						
						<div id="dropui-typo-section-desc" class="dropui dropui-orange dropui-circle dropui-left et-prop-typo">
							<img src="images/arrows/arrow_bottom_left_orange.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">F</a>
							<div class="dropui-content" style="width: 507px">
								<div class="dropui-content-header">
									<img src="images/icons/28.png" class="dropui-header-img" />
									<h6>Section Break Description</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									
									<div style="padding-bottom: 10px">
											<ul id="et_ul_typo_section_desc">
												<li id="et_li_typo_section_desc_font" class="tab_left tab_selected">Font</li>
												<li id="et_li_typo_section_desc_style">Style</li>
												<li id="et_li_typo_section_desc_size">Size</li>
												<li id="et_li_typo_section_desc_color" class="tab_right">Color</li>
											</ul>
									</div>
										
									<div id="et_typo_section_desc_content">
										
										<div id="et_li_typo_section_desc_font_tab">
											<div class="font_picker_tab">
												<?php echo get_font_picker_markup(); ?>
											</div>
											<div class="font_preview_tab">
												<div id="et_section_desc_font_preview_box" class="font_preview">AaBb</div>
												<div id="et_section_desc_font_preview_name" style="text-align: center; width: 79px; padding-top: 5px">Lucida Grande</div>
											</div>
										</div>
										
										<div id="et_li_typo_section_desc_style_tab" style="display: none">
											<ul id="et_ul_typo_section_desc_style" class="et_li_style_picker">
												<li class="dummy_li"></li>
											</ul>
										</div>
										
										<div id="et_li_typo_section_desc_size_tab" style="display: none">
											<ul id="et_typo_section_desc_size_pickerbox" class="et_li_size_picker">
												<li data-fsize="80%">8</li>
												<li data-fsize="85%" class="default_fsize">9</li>
												<li data-fsize="95%">10</li>
												<li data-fsize="100%">11</li>
												<li data-fsize="110%">12</li>
												<li data-fsize="130%">13</li>
												<li data-fsize="140%">14</li>
												<li data-fsize="150%">15</li>
												<li data-fsize="160%">16</li>
												<li data-fsize="170%">17</li>
												<li data-fsize="180%">18</li>
												<li data-fsize="240%">24</li>
												<li data-fsize="280%">28</li>
												<li data-fsize="320%">32</li>
												<li data-fsize="360%">36</li>
												<li data-fsize="400%">40</li>
												<li data-fsize="450%">45</li>
												<li data-fsize="640%">64</li>
											</ul>
										</div>
										
										<div id="et_li_typo_section_desc_color_tab" style="display: none">
											<div class="color_picker_tab">
												<?php echo get_color_picker_markup(); ?>
											</div>
											<div class="minicolors_tab">
												<div id="et_typo_section_desc_minicolor_box" class="minicolors_preview"></div>
												<div class="minicolors_container" >
													<input type="text" id="et_typo_section_desc_minicolor_input" name="et_typo_section_desc_minicolor_input" class="colors"  size="7" value="" />
												</div>
											</div>
										</div>
												
									</div>
									<div style="clear: both; height: 0px"></div>
								</div><!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-typo-section-desc -->
						
						<div id="dropui-typo-field-text" class="dropui dropui-orange dropui-circle dropui-right et-prop-typo">
							<img src="images/arrows/arrow_right_orange.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">G</a>
							<div class="dropui-content" style="width: 507px">
								<div class="dropui-content-header">
									<img src="images/icons/28.png" class="dropui-header-img" />
									<h6>Field Values</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									
									<div style="padding-bottom: 10px">
											<ul id="et_ul_typo_field_text">
												<li id="et_li_typo_field_text_font" class="tab_left tab_selected">Font</li>
												<li id="et_li_typo_field_text_style">Style</li>
												<li id="et_li_typo_field_text_size">Size</li>
												<li id="et_li_typo_field_text_color" class="tab_right">Color</li>
											</ul>
									</div>
										
									<div id="et_typo_field_text_content">
										
										<div id="et_li_typo_field_text_font_tab">
											<div class="font_picker_tab">
												<?php echo get_font_picker_markup(); ?>
											</div>
											<div class="font_preview_tab">
												<div id="et_field_text_font_preview_box" class="font_preview">AaBb</div>
												<div id="et_field_text_font_preview_name" style="text-align: center; width: 79px; padding-top: 5px">Lucida Grande</div>
											</div>
										</div>
										
										<div id="et_li_typo_field_text_style_tab" style="display: none">
											<ul id="et_ul_typo_field_text_style" class="et_li_style_picker">
												<li class="dummy_li"></li>
											</ul>
										</div>
										
										<div id="et_li_typo_field_text_size_tab" style="display: none">
											<ul id="et_typo_field_text_size_pickerbox" class="et_li_size_picker">
												<li data-fsize="80%">8</li>
												<li data-fsize="85%">9</li>
												<li data-fsize="95%">10</li>
												<li data-fsize="100%" class="default_fsize">11</li>
												<li data-fsize="110%">12</li>
												<li data-fsize="130%">13</li>
												<li data-fsize="140%">14</li>
												<li data-fsize="150%">15</li>
												<li data-fsize="160%">16</li>
												<li data-fsize="170%">17</li>
												<li data-fsize="180%">18</li>
												<li data-fsize="240%">24</li>
												<li data-fsize="280%">28</li>
												<li data-fsize="320%">32</li>
												<li data-fsize="360%">36</li>
												<li data-fsize="400%">40</li>
												<li data-fsize="450%">45</li>
												<li data-fsize="640%">64</li>
											</ul>
										</div>
										
										<div id="et_li_typo_field_text_color_tab" style="display: none">
											<div class="color_picker_tab">
												<?php echo get_color_picker_markup(); ?>
											</div>
											<div class="minicolors_tab">
												<div id="et_typo_field_text_minicolor_box" class="minicolors_preview"></div>
												<div class="minicolors_container" >
													<input type="text" id="et_typo_field_text_minicolor_input" name="et_typo_field_text_minicolor_input" class="colors"  size="7" value="" />
												</div>
											</div>
										</div>
												
									</div>
									<div style="clear: both; height: 0px"></div>
								</div><!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-typo-field-text -->
						
						<div id="dropui-border-form" class="dropui dropui-teal dropui-circle dropui-right et-prop-border">
							<img src="images/arrows/arrow_right_teal.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">A</a>
							<div class="dropui-content" style="width: 270px">
								<div class="dropui-content-header">
									<img src="images/icons/115.png" class="dropui-header-img" />
									<h6>Form Border</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									
									<div style="padding-bottom: 10px">
											<ul id="et_ul_border_form">
												<li id="et_li_border_form_thickness" class="tab_left tab_selected">Thickness</li>
												<li id="et_li_border_form_style">Style</li>
												<li id="et_li_border_form_color" class="tab_right">Color</li>
											</ul>
									</div>
										
									<div id="et_border_form_content">
										
										<div id="et_li_border_form_thickness_tab">
											<ul id="et_ul_border_form_thickness" class="et_li_style_picker">
												<li>
													<input type="radio" value="0" id="et_border_form_thickness_none" name="et_border_form_thickness_radio"> 
													<label for="et_border_form_thickness_none">None</label>
												</li>
												<li>
													<input type="radio" value="1" id="et_border_form_thickness_thin" name="et_border_form_thickness_radio"> 
													<label for="et_border_form_thickness_thin">Thin</label>
												</li>
												<li>
													<input type="radio" value="3" id="et_border_form_thickness_medium" name="et_border_form_thickness_radio"> 
													<label for="et_border_form_thickness_medium">Medium</label>
												</li>
												<li>
													<input type="radio" value="5" id="et_border_form_thickness_thick" name="et_border_form_thickness_radio"> 
													<label for="et_border_form_thickness_thick">Thick</label>
												</li>
												<li>
													<input type="radio" value="10" id="et_border_form_thickness_extrathick" name="et_border_form_thickness_radio"> 
													<label for="et_border_form_thickness_extrathick">Extra-Thick</label>
												</li>
											</ul>
										</div>
										
										<div id="et_li_border_form_style_tab" style="display: none">
											<ul id="et_ul_border_form_style" class="et_li_style_picker">
												<li>
													<input type="radio" value="solid" id="et_border_form_style_solid" name="et_border_form_style_radio"> 
													<label for="et_border_form_style_solid">Solid</label>
												</li>
												<li>
													<input type="radio" value="dotted" id="et_border_form_style_dotted" name="et_border_form_style_radio"> 
													<label for="et_border_form_style_dotted">Dotted</label>
												</li>
												<li>
													<input type="radio" value="dashed" id="et_border_form_style_dashed" name="et_border_form_style_radio"> 
													<label for="et_border_form_style_dashed">Dashed</label>
												</li>
												<li>
													<input type="radio" value="double" id="et_border_form_style_double" name="et_border_form_style_radio"> 
													<label for="et_border_form_style_double">Double</label>
												</li>
												
											</ul>
										</div>
										
										<div id="et_li_border_form_color_tab" style="display: none">
											<div class="color_picker_tab">
												<?php echo get_color_picker_markup(); ?>
											</div>
											<div class="minicolors_tab">
												<div id="et_border_form_minicolor_box" class="minicolors_preview"></div>
												<div class="minicolors_container" >
													<input type="text" id="et_border_form_minicolor_input" name="et_border_form_minicolor_input" class="colors"  size="7" value="" />
												</div>
											</div>
										</div>
												
									</div>
									<div style="clear: both; height: 0px"></div>
								</div><!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-border-form -->
						
						<div id="dropui-border-guidelines" class="dropui dropui-teal dropui-circle dropui-right et-prop-border">
							<img src="images/arrows/arrow_right_teal.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">B</a>
							<div class="dropui-content" style="width: 270px">
								<div class="dropui-content-header">
									<img src="images/icons/115.png" class="dropui-header-img" />
									<h6>Guidelines Border</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									
									<div style="padding-bottom: 10px">
											<ul id="et_ul_border_guidelines">
												<li id="et_li_border_guidelines_thickness" class="tab_left tab_selected">Thickness</li>
												<li id="et_li_border_guidelines_style">Style</li>
												<li id="et_li_border_guidelines_color" class="tab_right">Color</li>
											</ul>
									</div>
										
									<div id="et_border_guidelines_content">
										
										<div id="et_li_border_guidelines_thickness_tab">
											<ul id="et_ul_border_guidelines_thickness" class="et_li_style_picker">
												<li>
													<input type="radio" value="0" id="et_border_guidelines_thickness_none" name="et_border_guidelines_thickness_radio"> 
													<label for="et_border_guidelines_thickness_none">None</label>
												</li>
												<li>
													<input type="radio" value="1" id="et_border_guidelines_thickness_thin" name="et_border_guidelines_thickness_radio"> 
													<label for="et_border_guidelines_thickness_thin">Thin</label>
												</li>
												<li>
													<input type="radio" value="3" id="et_border_guidelines_thickness_medium" name="et_border_guidelines_thickness_radio"> 
													<label for="et_border_guidelines_thickness_medium">Medium</label>
												</li>
												<li>
													<input type="radio" value="5" id="et_border_guidelines_thickness_thick" name="et_border_guidelines_thickness_radio"> 
													<label for="et_border_guidelines_thickness_thick">Thick</label>
												</li>
												<li>
													<input type="radio" value="10" id="et_border_guidelines_thickness_extrathick" name="et_border_guidelines_thickness_radio"> 
													<label for="et_border_guidelines_thickness_extrathick">Extra-Thick</label>
												</li>
											</ul>
										</div>
										
										<div id="et_li_border_guidelines_style_tab" style="display: none">
											<ul id="et_ul_border_guidelines_style" class="et_li_style_picker">
												<li>
													<input type="radio" value="solid" id="et_border_guidelines_style_solid" name="et_border_guidelines_style_radio"> 
													<label for="et_border_guidelines_style_solid">Solid</label>
												</li>
												<li>
													<input type="radio" value="dotted" id="et_border_guidelines_style_dotted" name="et_border_guidelines_style_radio"> 
													<label for="et_border_guidelines_style_dotted">Dotted</label>
												</li>
												<li>
													<input type="radio" value="dashed" id="et_border_guidelines_style_dashed" name="et_border_guidelines_style_radio"> 
													<label for="et_border_guidelines_style_dashed">Dashed</label>
												</li>
												<li>
													<input type="radio" value="double" id="et_border_guidelines_style_double" name="et_border_guidelines_style_radio"> 
													<label for="et_border_guidelines_style_double">Double</label>
												</li>
												
											</ul>
										</div>
										
										<div id="et_li_border_guidelines_color_tab" style="display: none">
											<div class="color_picker_tab">
												<?php echo get_color_picker_markup(); ?>
											</div>
											<div class="minicolors_tab">
												<div id="et_border_guidelines_minicolor_box" class="minicolors_preview"></div>
												<div class="minicolors_container" >
													<input type="text" id="et_border_guidelines_minicolor_input" name="et_border_guidelines_minicolor_input" class="colors"  size="7" value="" />
												</div>
											</div>
										</div>
												
									</div>
									<div style="clear: both; height: 0px"></div>
								</div><!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-border-guidelines -->
						
						<div id="dropui-border-section" class="dropui dropui-teal dropui-circle dropui-right et-prop-border">
							<img src="images/arrows/arrow_bottom_right_teal.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">C</a>
							<div class="dropui-content" style="width: 270px">
								<div class="dropui-content-header">
									<img src="images/icons/115.png" class="dropui-header-img" />
									<h6>Section Break Border</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									
									<div style="padding-bottom: 10px">
											<ul id="et_ul_border_section">
												<li id="et_li_border_section_thickness" class="tab_left tab_selected">Thickness</li>
												<li id="et_li_border_section_style">Style</li>
												<li id="et_li_border_section_color" class="tab_right">Color</li>
											</ul>
									</div>
										
									<div id="et_border_section_content">
										
										<div id="et_li_border_section_thickness_tab">
											<ul id="et_ul_border_section_thickness" class="et_li_style_picker">
												<li>
													<input type="radio" value="0" id="et_border_section_thickness_none" name="et_border_section_thickness_radio"> 
													<label for="et_border_section_thickness_none">None</label>
												</li>
												<li>
													<input type="radio" value="1" id="et_border_section_thickness_thin" name="et_border_section_thickness_radio"> 
													<label for="et_border_section_thickness_thin">Thin</label>
												</li>
												<li>
													<input type="radio" value="3" id="et_border_section_thickness_medium" name="et_border_section_thickness_radio"> 
													<label for="et_border_section_thickness_medium">Medium</label>
												</li>
												<li>
													<input type="radio" value="5" id="et_border_section_thickness_thick" name="et_border_section_thickness_radio"> 
													<label for="et_border_section_thickness_thick">Thick</label>
												</li>
												<li>
													<input type="radio" value="10" id="et_border_section_thickness_extrathick" name="et_border_section_thickness_radio"> 
													<label for="et_border_section_thickness_extrathick">Extra-Thick</label>
												</li>
											</ul>
										</div>
										
										<div id="et_li_border_section_style_tab" style="display: none">
											<ul id="et_ul_border_section_style" class="et_li_style_picker">
												<li>
													<input type="radio" value="solid" id="et_border_section_style_solid" name="et_border_section_style_radio"> 
													<label for="et_border_section_style_solid">Solid</label>
												</li>
												<li>
													<input type="radio" value="dotted" id="et_border_section_style_dotted" name="et_border_section_style_radio"> 
													<label for="et_border_section_style_dotted">Dotted</label>
												</li>
												<li>
													<input type="radio" value="dashed" id="et_border_section_style_dashed" name="et_border_section_style_radio"> 
													<label for="et_border_section_style_dashed">Dashed</label>
												</li>
												<li>
													<input type="radio" value="double" id="et_border_section_style_double" name="et_border_section_style_radio"> 
													<label for="et_border_section_style_double">Double</label>
												</li>
												
											</ul>
										</div>
										
										<div id="et_li_border_section_color_tab" style="display: none">
											<div class="color_picker_tab">
												<?php echo get_color_picker_markup(); ?>
											</div>
											<div class="minicolors_tab">
												<div id="et_border_section_minicolor_box" class="minicolors_preview"></div>
												<div class="minicolors_container" >
													<input type="text" id="et_border_section_minicolor_input" name="et_border_section_minicolor_input" class="colors"  size="7" value="" />
												</div>
											</div>
										</div>
												
									</div>
									<div style="clear: both; height: 0px"></div>
								</div><!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-border-section -->
						
						<div id="dropui-form-shadow" class="dropui dropui-blue dropui-circle dropui-left et-prop-shadow">
							<img src="images/arrows/arrow_left_curly_blue.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">A</a>
							<div class="dropui-content" style="width: 360px">
								<div class="dropui-content-header">
									<img src="images/icons/117.png" class="dropui-header-img" />
									<h6>Form Shadow</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									
									<div style="padding-bottom: 10px">
											<ul id="et_ul_form_shadow">
												<li id="et_li_form_shadow_style" class="tab_left tab_selected">Style</li>
												<li id="et_li_form_shadow_size">Size</li>
												<li id="et_li_form_shadow_brightness" class="tab_right">Brightness</li>
											</ul>
									</div>
										
									<div id="et_form_shadow_content">
										
										<div id="et_li_form_shadow_style_tab">
											<ul id="et_ul_form_shadow_style" class="et_li_style_picker">
												<li>
													<input type="radio" value="disabled" id="et_form_shadow_style_disabled" name="et_form_shadow_style_radio"> 
													<label for="et_form_shadow_style_disabled">None</label>
												</li>
												<li>
													<input type="radio" value="WarpShadow" id="et_form_shadow_style_warp" name="et_form_shadow_style_radio"> 
													<label for="et_form_shadow_style_warp">Warp</label>
												</li>
												<li>
													<input type="radio" value="LeftWarpShadow" id="et_form_shadow_style_warp_left" name="et_form_shadow_style_radio"> 
													<label for="et_form_shadow_style_warp_left">Warp Left</label>
												</li>
												<li>
													<input type="radio" value="RightWarpShadow" id="et_form_shadow_style_warp_right" name="et_form_shadow_style_radio"> 
													<label for="et_form_shadow_style_warp_right">Warp Right</label>
												</li>
												<li>
													<input type="radio" value="FoldShadow" id="et_form_shadow_style_fold" name="et_form_shadow_style_radio"> 
													<label for="et_form_shadow_style_fold">Fold</label>
												</li>
												<li>
													<input type="radio" value="StandShadow" id="et_form_shadow_style_stand" name="et_form_shadow_style_radio"> 
													<label for="et_form_shadow_style_stand">Stand</label>
												</li>
												
												<li>
													<input type="radio" value="LeftCurlShadow" id="et_form_shadow_style_curl_left" name="et_form_shadow_style_radio"> 
													<label for="et_form_shadow_style_curl_left">Curl Top-Left</label>
												</li>
												<li>
													<input type="radio" value="RightCurlShadow" id="et_form_shadow_style_curl_right" name="et_form_shadow_style_radio"> 
													<label for="et_form_shadow_style_curl_right">Curl Top-Right</label>
												</li>
												<li>
													<input type="radio" value="LeftPerspectiveShadow" id="et_form_shadow_style_perspective_left" name="et_form_shadow_style_radio"> 
													<label for="et_form_shadow_style_perspective_left">Perspective Left</label>
												</li>
												<li>
													<input type="radio" value="RightPerspectiveShadow" id="et_form_shadow_style_perspective_right" name="et_form_shadow_style_radio"> 
													<label for="et_form_shadow_style_perspective_right">Perspective Right</label>
												</li>
												<li>
													<input type="radio" value="HoverShadow" id="et_form_shadow_style_hover" name="et_form_shadow_style_radio"> 
													<label for="et_form_shadow_style_hover">Hover</label>
												</li>
												
												<li>
													<input type="radio" value="BottomShadow" id="et_form_shadow_style_perspective_bottom" name="et_form_shadow_style_radio"> 
													<label for="et_form_shadow_style_perspective_bottom">Perspective Bottom</label>
												</li>
												
											</ul>
										</div>
										
										<div id="et_li_form_shadow_size_tab" style="display: none">
											<ul id="et_ul_form_shadow_size" class="et_li_style_picker">
												<li>
													<input type="radio" value="small" id="et_form_shadow_size_small" name="et_form_shadow_size_radio"> 
													<label for="et_form_shadow_size_small">Small</label>
												</li>
												<li>
													<input type="radio" value="medium" id="et_form_shadow_size_medium" name="et_form_shadow_size_radio"> 
													<label for="et_form_shadow_size_medium">Medium</label>
												</li>
												<li>
													<input type="radio" value="large" id="et_form_shadow_size_large" name="et_form_shadow_size_radio"> 
													<label for="et_form_shadow_size_large">Large</label>
												</li>
												
											</ul>
										</div>
										
										<div id="et_li_form_shadow_brightness_tab" style="display: none">
											<ul id="et_ul_form_shadow_brightness" class="et_li_style_picker">
												<li>
													<input type="radio" value="light" id="et_form_shadow_brightness_light" name="et_form_shadow_brightness_radio"> 
													<label for="et_form_shadow_brightness_light">Light</label>
												</li>
												<li>
													<input type="radio" value="normal" id="et_form_shadow_brightness_normal" name="et_form_shadow_brightness_radio"> 
													<label for="et_form_shadow_brightness_normal">Normal</label>
												</li>
												<li>
													<input type="radio" value="dark" id="et_form_shadow_brightness_dark" name="et_form_shadow_brightness_radio"> 
													<label for="et_form_shadow_brightness_dark">Dark</label>
												</li>
												
											</ul>
										</div>
												
									</div>
									<div style="clear: both; height: 0px"></div>
								</div><!-- end dropui-content-main -->
							</div><!-- end dropui-content -->
						</div><!-- end dropui-form-shadow -->
						
						<div id="dropui-form-button" class="dropui dropui-red dropui-circle dropui-left et-prop-button">
							<img src="images/arrows/arrow_left_red.png" class="dropui-arrow-img" />
							<a href="javascript:;" class="dropui-tab dropui-prop">A</a>
							<div class="dropui-content" style="width: 500px">
								<div class="dropui-content-header">
									<img src="images/icons/62.png" width="16" class="dropui-header-img" />
									<h6>Form Submit Button</h6>
									<a href="#" class="dropui-close"><img src="images/icons/52.png" /></a>
									<div class="dropui-header-clear"></div>
								</div>
								<div class="dropui-content-main">
									<div style="float: left; width: 150px">
										<ul id="et_ul_form_button">
											<li class="prop_selected"><input type="radio" title="Use Text Button" name="et_form_button" id="et_form_button_text" /> <label for="et_form_button_text">Use Text Button</label></li>
											<li><input type="radio" title="Use Image Button" name="et_form_button" id="et_form_button_image" /> <label for="et_form_button_image">Use Image Button</label></li>
										</ul>
									</div>
									<div id="et_form_button_content">
										
										<div id="et_form_button_text_tab">
											<span style="display: block">
												<label for="et_form_button_text_input">Enter Your Button Text:</label>
												<input type="text" value="Submit" class="text" name="et_form_button_text_input" id="et_form_button_text_input" />
											</span>
										</div>
										
										<div id="et_form_button_image_tab" style="display: none">
											<span style="display: block">
												<label for="et_form_button_image_input">Enter Your Image URL:</label>
												<input type="text" value="http://" class="text" name="et_form_button_image_input" id="et_form_button_image_input" />
											</span>
										</div>
										
									</div>
									<div style="clear: both; height: 0px"></div>
								</div> <!-- end dropui-content-main -->
							</div>
						</div>
						
						
					<!-- end drop buttons -->
					
					<!-- start dialog boxes -->
					<div id="dialog-name-theme" title="Name this theme" class="buttons" style="display: none"> 
						<form id="dialog-name-theme-form" class="dialog-form" style="padding-left: 10px;padding-bottom: 10px">				
							<ul>
								<li>
									<div>
									<input type="text" value="" class="text" name="dialog-name-theme-input" id="dialog-name-theme-input" />
									</div> 
								</li>
							</ul>
						</form>
					</div>
					
					<div id="dialog-duplicate-theme" title="Name the new theme" class="buttons" style="display: none"> 
						<form id="dialog-duplicate-theme-form" class="dialog-form" style="padding-left: 10px;padding-bottom: 10px">				
							<ul>
								<li>
									<div>
									<input type="text" value="" class="text" name="dialog-duplicate-theme-input" id="dialog-duplicate-theme-input" />
									</div> 
								</li>
							</ul>
						</form>
					</div>
					
					<div id="dialog-rename-theme" title="Enter a New Name" class="buttons" style="display: none"> 
						<form id="dialog-rename-theme-form" class="dialog-form" style="padding-left: 10px;padding-bottom: 10px">				
							<ul>
								<li>
									<div>
									<input type="text" value="" class="text" name="dialog-rename-theme-input" id="dialog-rename-theme-input" />
									</div> 
								</li>
							</ul>
						</form>
					</div>
					
					<div id="dialog-theme-saved" title="Success!" class="buttons" style="display: none">
						<img src="images/icons/62_green_48.png" title="Success" /> 
						<p id="dialog-theme-saved-msg">
							Your theme has been saved.
						</p>
					</div>
					
					<div id="dialog-advanced-css" title="Advanced CSS Code" class="buttons" style="display: none"> 
						<form class="dialog-form">				
							<ul>
								<li>
									<label for="dialog-advanced-css-input" class="description">Enter CSS code below to customize current form theme</label>
									<div>
									<textarea cols="90" rows="8" class="element textarea large" name="dialog-advanced-css-input" id="dialog-advanced-css-input"></textarea> 
									<div class="infomessage"><img src="images/icons/70_red.png" style="vertical-align: middle"/> The result of the above custom CSS code won't be displayed within the theme editor. Save your theme, assign it to your form and view the form to see the result.</div>
									</div> 
								</li>
							</ul>
						</form>
					</div>
					<div id="dialog-delete-theme" title="Are you sure you want to delete this theme?" class="buttons" style="display: none">
						<span class="icon-bubble-notification"></span>
						<p>
							This action cannot be undone.<br/>
							<strong>Any form using this theme will be reverted to the default theme.</strong><br/><br/>
							
						</p>
						
					</div>
					<div id="dialog-share-theme" title="Are you sure you want to share this theme?" class="buttons" style="display: none">
						<img src="images/icons/warning.png" title="Confirmation" /> 
						<p>
							<br/>
							<strong>Any other users will be able to use this theme on their forms.</strong><br/><br/>
							They won't be able to edit this theme, only using it.<br /><br />
						</p>
						
					</div>
					<div id="dialog-unshare-theme" title="Are you sure you want to set this theme private?" class="buttons" style="display: none">
						<span class="icon-bubble-notification"></span>
						<p>
							This action might affect many forms.<br/>
							<strong>Any form using this theme will be reverted to the default theme.</strong><br/><br/>
							
						</p>
						
					</div>
					<!-- end dialog boxes -->
					
					</div><!-- /end of main_body -->
	
	
				</div> <!-- /end of et_theme_preview -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

 
<?php
	$footer_data =<<< EOT
<script type="text/javascript">
	$(function(){
		{$jquery_data_code}		
    });
</script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.core.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.widget.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.tabs.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.mouse.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.sortable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.draggable.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.position.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.dialog.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery.mini_colors.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/uploadifive/jquery.uploadifive.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/jquery.jqplugin.min.js{$mf_version_tag}"></script>
<script type="text/javascript" src="js/theme_editor.js{$mf_version_tag}"></script>
EOT;

	require('includes/footer.php'); 
?>