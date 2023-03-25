<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	
	//generate color picker boxes
	function get_color_picker_markup(){
		$color_picker_markup =<<<EOT
												<ul class="et_color_picker">
													<li id="li_transparent" style="background-image: url('images/icons/colorbox_minus.png');background-color: transparent"></li>
													<li style="background-color: #000000"></li>
													<li style="background-color: #444444"></li>
													<li style="background-color: #666666"></li>
													<li style="background-color: #999999"></li>
													<li style="background-color: #CDCDCD"></li>
													<li style="background-color: #ECECEC"></li>
													<li style="background-color: #FFFFFF"></li>
													<li style="background-color: #1693A5"></li>
													<li style="background-color: #FBB829"></li>
													<li style="background-color: #CDD7B6"></li>
													<li style="background-color: #FF0000"></li>
													<li style="background-color: #FF0066"></li>
													<li style="background-color: #556270"></li>
													<li style="background-color: #ADD8C7"></li>
													<li style="background-color: #333333"></li>
													<li style="background-color: #FCFBE3"></li>
													<li style="background-color: #F0F0D8"></li>
													<li style="background-color: #F02311"></li>
													<li style="background-color: #FF9900"></li>
													<li style="background-color: #800F25"></li>
													<li style="background-color: #2A8FBD"></li>
													<li style="background-color: #CCFF00"></li>
													<li style="background-color: #A40802"></li>
													<li style="background-color: #FF5EAA"></li>
													<li style="background-color: #D8D8C0"></li>
													<li style="background-color: #6CDFEA"></li>
													<li style="background-color: #AD234B"></li>
													<li style="background-color: #666666"></li>
													<li style="background-color: #F0F0F0"></li>
													<li style="background-color: #77CCA4"></li>
													<li style="background-color: #FF0033"></li>
													<li style="background-color: #FE4365"></li>
													<li style="background-color: #025D8C"></li>
													<li style="background-color: #7F94B0"></li>
													<li style="background-color: #C7F464"></li>
													<li style="background-color: #D9FFA9"></li>
													<li style="background-color: #FC0F3E"></li>
													<li style="background-color: #D2E4FC"></li>
													<li style="background-color: #948C75"></li>
													<li style="background-color: #FFFF00"></li>
													<li style="background-color: #CCCCCC"></li>
													<li style="background-color: #FF6666"></li>
													<li style="background-color: #FFCC00"></li>
													<li style="background-color: #F4FCE8"></li>
													<li style="background-color: #999999"></li>
													<li style="background-color: #F7FDFA"></li>
													<li style="background-color: #7FAF1B"></li>
													<li style="background-color: #C0ADDB"></li>
													<li style="background-color: #A0D4A4"></li>
													<li style="background-color: #A1BEE6"></li>
													<li style="background-color: #FF6600"></li>
													<li style="background-color: #7FFF24"></li>
													<li style="background-color: #FEF9F0"></li>
													<li style="background-color: #0B8C8F"></li>
													<li style="background-color: #01D2FF"></li>
													<li style="background-color: #CAE8A2"></li>
													<li style="background-color: #FF5500"></li>
													<li style="background-color: #A80000"></li>
													<li style="background-color: #D70044"></li>
													<li style="background-color: #630947"></li>
													<li style="background-color: #515151"></li>
													<li style="background-color: #FF8800"></li>
													<li style="background-color: #AB0743"></li>
													<li style="background-color: #369699"></li>
													<li style="background-color: #520039"></li>
													<li style="background-color: #D7217E"></li>
													<li style="background-color: #D9E68E"></li>
													<li style="background-color: #107FC9"></li>
													<li style="background-color: #4F4E57"></li>
													<li style="background-color: #A8C0A8"></li>
													<li style="background-color: #44AA55"></li>
													<li style="background-color: #C0D8D8"></li>
													<li style="background-color: #FFA4A0"></li>
													<li style="background-color: #E3F6F3"></li>
													<li style="background-color: #F5F3E5"></li>
													<li style="background-color: #F4FFF9"></li>
													<li style="background-color: #919999"></li>
													<li style="background-color: #FF6B6B"></li>
													<li style="background-color: #C9E69A"></li>
													<li style="background-color: #EDF7FF"></li>
													<li style="background-color: #F56991"></li>
													<li style="background-color: #036564"></li>
													<li style="background-color: #E45635"></li>
													<li style="background-color: #D3B2D1"></li>
													<li style="background-color: #8EAFD1"></li>
													<li style="background-color: #FF9500"></li>
													<li style="background-color: #BAE4E5"></li>
													<li style="background-color: #FAF2F8"></li>
													<li style="background-color: #B1D58B"></li>
													<li style="background-color: #F0D878"></li>
													<li style="background-color: #D8F0F0"></li>
													<li style="background-color: #FFFFCC"></li>
													<li style="background-color: #FFD0D4"></li>
													<li style="background-color: #EFFAB4"></li>
													<li style="background-color: #F5AA1A"></li>
													<li style="background-color: #FFCCCC"></li>
													<li style="background-color: #D5D6CB"></li>
													<li style="background-color: #F0F0C0"></li>
													<li style="background-color: #82AEC8"></li>
													<li style="background-color: #69D2E7"></li>
													<li style="background-color: #B3C7EB"></li>
													<li style="background-color: #87D69B"></li>
													<li style="background-color: #ECCD35"></li>
													<li style="background-color: #F9CDAD"></li>
													<li style="background-color: #E0B5CB"></li>
													<li style="background-color: #484848"></li>
													<li style="background-color: #FF8080"></li>
													<li style="background-color: #ADDDEB"></li>
													<li style="background-color: #E9ECD9"></li>
													<li style="background-color: #BBC793"></li>
													<li style="background-color: #7BA5D1"></li>
													<li style="background-color: #C4CDE6"></li>
													<li style="background-color: #BFA76F"></li>
													<li style="background-color: #814444"></li>
													<li style="background-color: #4E6189"></li>
													<li style="background-color: #9AE4E8"></li>
													<li style="background-color: #BFA76F"></li>
													<li style="background-color: #FF4F4F"></li>
													<li style="background-color: #990000"></li>
													<li style="background-color: #006666"></li>
													<li style="background-color: #F74427"></li>
													<li style="background-color: #0E4E5A"></li>
													<li style="background-color: #C20562"></li>
													<li style="background-color: #A662DE"></li>
													<li style="background-color: #ADC7BE"></li>
													<li style="background-color: #F38630"></li>
													<li style="background-color: #FF005E"></li>
													<li style="background-color: #301830"></li>
													<li style="background-color: #FFFB00"></li>
													<li style="background-color: #FF2A00"></li>
													<li style="background-color: #EBEBEB"></li>
													<li style="background-color: #F0EEE1"></li>
													<li style="background-color: #FF7300"></li>
													<li style="background-color: #C0FF33"></li>
													<li style="background-color: #00A0C6"></li>
													<li style="background-color: #FFD700"></li>
													<li style="background-color: #9D007A"></li>
													<li style="background-color: #81971A"></li>
													<li style="background-color: #C7E2C3"></li>
													<li style="background-color: #F8ECC9"></li>
													<li style="background-color: #800149"></li>
													<li style="background-color: #BD8B64"></li>
													<li style="background-color: #8ABFCF"></li>
													<li style="background-color: #F0D8C0"></li>
													<li style="background-color: #D8D8A8"></li>
													<li style="background-color: #FF6699"></li>
													<li style="background-color: #FA5B49"></li>
													<li style="background-color: #9FC2D6"></li>
													<li style="background-color: #549CCC"></li>
													<li style="background-color: #F0D8D8"></li>
													<li style="background-color: #6991AA"></li>
													<li style="background-color: #D4E77D"></li>
													<li style="background-color: #62BECB"></li>
													<li style="background-color: #7D96FF"></li>
													<li style="background-color: #F9FAD2"></li>
													<li style="background-color: #F5FAAC"></li>
													<li style="background-color: #FFAA7D"></li>
													<li style="background-color: #786060"></li>
													<li style="background-color: #A8A878"></li>
													<li style="background-color: #48A09B"></li>
													<li style="background-color: #FFF200"></li>
													<li style="background-color: #FCCD43"></li>
													<li style="background-color: #83AF9B"></li>
													<li style="background-color: #E1F5B0"></li>
													<li style="background-color: #C7E7E6"></li>
													<li style="background-color: #FFBAA9"></li>
												</ul>
EOT;
	
		return $color_picker_markup;
	}
	
	//generate pattern picker boxes
	function get_pattern_picker_markup(){
		$pattern_picker_markup =<<<EOT
												<ul class="et_pattern_picker">
													<li data-pattern="grey-mild.png" style="background-image: url('images/form_resources/grey-mild.png');"></li>
													<li data-pattern="45degreee_fabric.png" style="background-image: url('images/form_resources/45degreee_fabric.png');"></li>
													<li data-pattern="batthern.gif" style="background-image: url('images/form_resources/batthern.gif');"></li>
													<li data-pattern="bgnoise_lg.jpg" style="background-image: url('images/form_resources/bgnoise_lg.jpg');"></li>
													<li data-pattern="black_linen_v2.jpg" style="background-image: url('images/form_resources/black_linen_v2.jpg');"></li>
													<li data-pattern="blu_stripes.png" style="background-image: url('images/form_resources/blu_stripes.png');"></li>
													<li data-pattern="brushed_alu_dark.jpg" style="background-image: url('images/form_resources/brushed_alu_dark.jpg');"></li>
													<li data-pattern="cloth_alike.gif" style="background-image: url('images/form_resources/cloth_alike.gif');"></li>
													<li data-pattern="darkdenim3.png" style="background-image: url('images/form_resources/darkdenim3.png');"></li>
													<li data-pattern="diagonal-noise.png" style="background-image: url('images/form_resources/diagonal-noise.png');"></li>
													<li data-pattern="egg_shell.png" style="background-image: url('images/form_resources/egg_shell.png');"></li>
													<li data-pattern="embossed_paper.png" style="background-image: url('images/form_resources/embossed_paper.png');"></li>
													<li data-pattern="escheresque.png" style="background-image: url('images/form_resources/escheresque.png');"></li>
													<li data-pattern="graphy.png" style="background-image: url('images/form_resources/graphy.png');"></li>
													<li data-pattern="grid.png" style="background-image: url('images/form_resources/grid.png');"></li>
													<li data-pattern="gridme.png" style="background-image: url('images/form_resources/gridme.png');"></li>
													<li data-pattern="knitting250px.png" style="background-image: url('images/form_resources/knitting250px.png');"></li>
													<li data-pattern="lil_fiber.png" style="background-image: url('images/form_resources/lil_fiber.png');"></li>
													<li data-pattern="linedpaper.png" style="background-image: url('images/form_resources/linedpaper.png');"></li>
													<li data-pattern="low_contrast_linen.png" style="background-image: url('images/form_resources/low_contrast_linen.png');"></li>
													<li data-pattern="moulin.png" style="background-image: url('images/form_resources/moulin.png');"></li>
													<li data-pattern="natural_paper.png" style="background-image: url('images/form_resources/natural_paper.png');"></li>
													<li data-pattern="noisy_grid.gif" style="background-image: url('images/form_resources/noisy_grid.gif');"></li>
													<li data-pattern="grey_wash_wall.png" style="background-image: url('images/form_resources/grey_wash_wall.png');"></li>
													<li data-pattern="p4.png" style="background-image: url('images/form_resources/p4.png');"></li>
													<li data-pattern="p5.png" style="background-image: url('images/form_resources/p5.png');"></li>
													<li data-pattern="p6.png" style="background-image: url('images/form_resources/p6.png');"></li>
													<li data-pattern="ps_neutral.png" style="background-image: url('images/form_resources/ps_neutral.png');"></li>
													<li data-pattern="sneaker_mesh_fabric.png" style="background-image: url('images/form_resources/sneaker_mesh_fabric.png');"></li>
													<li data-pattern="subtle_white_feathers.png" style="background-image: url('images/form_resources/subtle_white_feathers.png');"></li>
													<li data-pattern="tex2res3.png" style="background-image: url('images/form_resources/tex2res3.png');"></li>
													<li data-pattern="tex2res4.png" style="background-image: url('images/form_resources/tex2res4.png');"></li>
													<li data-pattern="tex2res5.png" style="background-image: url('images/form_resources/tex2res5.png');"></li>
													<li data-pattern="textured_paper.png" style="background-image: url('images/form_resources/textured_paper.png');"></li>
													<li data-pattern="wavegrid.png" style="background-image: url('images/form_resources/wavegrid.png');"></li>
													<li data-pattern="weave.png" style="background-image: url('images/form_resources/weave.png');"></li>
													<li data-pattern="white_brick_wall.png" style="background-image: url('images/form_resources/white_brick_wall.png');"></li>
													<li data-pattern="pattern_022.gif" style="background-image: url('images/form_resources/pattern_022.gif');"></li>
													<li data-pattern="pattern_036.gif" style="background-image: url('images/form_resources/pattern_036.gif');"></li>
													<li data-pattern="pattern_038.gif" style="background-image: url('images/form_resources/pattern_038.gif');"></li>
													<li data-pattern="pattern_044.gif" style="background-image: url('images/form_resources/pattern_044.gif');"></li>
												</ul>
EOT;
	
		return $pattern_picker_markup;
	}
	
	//generate font picker boxes
	function get_font_picker_markup(){
		$font_picker_markup =<<<EOT
												<ul class="et_font_picker">
													<li>
														<div class="font_picker_preview" style="font-family: 'Lucida Grande',sans-serif">Default</div>
														<div class="font_picker_meta">
															<div class="font_name">Lucida Grande</div>
															<div class="font_info">System Font</div>
														</div>
													</li>
													<li>
														<div id="li_lobster" class="font_picker_preview" style="font-family: Arial,sans-serif">Arial</div>
														<div class="font_picker_meta">
															<div class="font_name">Arial</div>
															<div class="font_info">System Font</div>
														</div>
													</li>
													<li>
														<div class="font_picker_preview" style="font-family: 'Trebuchet MS', Helvetica, sans-serif;">Trebuchet MS</div>
														<div class="font_picker_meta">
															<div class="font_name">Trebuchet MS</div>
															<div class="font_info">System Font</div>
														</div>
													</li>
													<li>
														<div class="font_picker_preview" style="font-family: Verdana, sans-serif;">Verdana</div>
														<div class="font_picker_meta">
															<div class="font_name">Verdana</div>
															<div class="font_info">System Font</div>
														</div>
													</li>
													<li>
														<div class="font_picker_preview" style="font-family: Tahoma, Geneva, sans-serif;">Tahoma</div>
														<div class="font_picker_meta">
															<div class="font_name">Tahoma</div>
															<div class="font_info">System Font</div>
														</div>
													</li>
													<li>
														<div class="font_picker_preview" style="font-family: 'Courier New', Courier, monospace;">Courier New</div>
														<div class="font_picker_meta">
															<div class="font_name">Courier New</div>
															<div class="font_info">System Font</div>
														</div>
													</li>
													<li>
														<div class="font_picker_preview" style="font-family: 'Palatino Linotype', 'Book Antiqua', Palatino, serif;">Palatino Linotype</div>
														<div class="font_picker_meta">
															<div class="font_name">Palatino Linotype</div>
															<div class="font_info">System Font</div>
														</div>
													</li>
													<li>
														<div class="font_picker_preview" style="font-family: 'Times New Roman', serif;">Times New Roman</div>
														<div class="font_picker_meta">
															<div class="font_name">Times New Roman</div>
															<div class="font_info">System Font</div>
														</div>
													</li>
													<li>
														<div class="font_picker_preview" style="font-family: Georgia, serif;">Georgia</div>
														<div class="font_picker_meta">
															<div class="font_name">Georgia</div>
															<div class="font_info">System Font</div>
														</div>
													</li>
													<li>
														<div class="font_picker_preview" style="font-family: 'Comic Sans MS', cursive;">Comic Sans MS</div>
														<div class="font_picker_meta">
															<div class="font_name">Comic Sans MS</div>
															<div class="font_info">System Font</div>
														</div>
													</li>
													<li>
														<div class="font_picker_preview" style="font-family: 'Arial Black', Gadget, sans-serif;">Arial Black</div>
														<div class="font_picker_meta">
															<div class="font_name">Arial Black</div>
															<div class="font_info">System Font</div>
														</div>
													</li>
													<li class="li_show_more">
														<span>Show More Fonts</span> <img src="images/icons/arrow_down.png" style="vertical-align: middle"/>
													</li>
												</ul>
EOT;
	
		return $font_picker_markup;
	}
	
	//generate the CSS markup for the selected form theme
	function mf_theme_get_css_content($dbh,$theme_id){
		
		$css_content = "/** DO NOT MODIFY THIS FILE. All code here are generated by MachForm Theme Editor **/\n\n";
		$theme_properties = new stdClass();
		
		$mf_settings = mf_get_settings($dbh);

		
		$query = "SELECT
						theme_name,
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
						advanced_css
					FROM
						`".MF_TABLE_PREFIX."form_themes`
				   WHERE
				   		theme_id=? and `status`=1";
		$params = array($theme_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		$theme_properties->theme_id 		   = $theme_id;
		$theme_properties->theme_name  		   = $row['theme_name'];
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
		
		/** Form Logo **/
		$form_logo_style  = "#main_body h1 a";
		$form_logo_style .= "\n"."{"."\n";
		
		$form_logo_height = 40;
		
		if($theme_properties->logo_type == 'disabled'){ //logo disabled
			$form_logo_style .= "background-image: none;"."\n";
		}else if($theme_properties->logo_type == 'default'){//default logo
			$form_logo_style .= "background-image: url('{$mf_settings['base_url']}images/form_resources/{$theme_properties->logo_default_image}');"."\n";
			$form_logo_style .= "background-repeat: no-repeat;"."\n";
		}else if($theme_properties->logo_type == 'custom'){//custom logo
			$form_logo_style .= "background-image: url('{$theme_properties->logo_custom_image}');"."\n";
			$form_logo_height  = $theme_properties->logo_custom_height;
		}
		
		$form_logo_style .= "height: {$form_logo_height}px;"."\n";
		$form_logo_style .= "}"."\n\n";
		
		$css_content .= $form_logo_style;
		
		/** Wallpaper **/
		$form_wallpaper_style = "html";
		$form_wallpaper_style .= "\n"."{"."\n";
		
		if($theme_properties->wallpaper_bg_type == 'color'){
			$form_wallpaper_style .= "background-color: {$theme_properties->wallpaper_bg_color};"."\n";
			$form_wallpaper_style .= "background-image: none;"."\n";
		}else if($theme_properties->wallpaper_bg_type == 'pattern'){
			$form_wallpaper_style .= "background-image: url('{$mf_settings['base_url']}images/form_resources/{$theme_properties->wallpaper_bg_pattern}');"."\n";
			$form_wallpaper_style .= "background-repeat: repeat;"."\n";
		}else if($theme_properties->wallpaper_bg_type == 'custom'){
			$form_wallpaper_style .= "background-image: url('{$theme_properties->wallpaper_bg_custom}');"."\n";
			$form_wallpaper_style .= "background-repeat: repeat;"."\n";
		}
		
		$form_wallpaper_style .= "}"."\n\n";
		$css_content .= $form_wallpaper_style;
		
		/** Form Header **/
		$form_header_style = "#main_body h1";
		$form_header_style .= "\n"."{"."\n";
		
		if($theme_properties->header_bg_type == 'color'){
			$form_header_style .= "background-color: {$theme_properties->header_bg_color};"."\n";
			$form_header_style .= "background-image: none;"."\n";
		}else if($theme_properties->header_bg_type == 'pattern'){
			$form_header_style .= "background-image: url('{$mf_settings['base_url']}images/form_resources/{$theme_properties->header_bg_pattern}');"."\n";
			$form_header_style .= "background-repeat: repeat;"."\n";
		}else if($theme_properties->header_bg_type == 'custom'){
			$form_header_style .= "background-image: url('{$theme_properties->header_bg_custom}');"."\n";
			$form_header_style .= "background-repeat: repeat;"."\n";
		}
		
		$form_header_style .= "}"."\n\n";
		$css_content .= $form_header_style;
		
		/** Form Background **/
		$form_container_style = "#form_container";
		$form_container_style .= "\n"."{"."\n";
		
		if($theme_properties->form_bg_type == 'color'){
			$form_container_style .= "background-color: {$theme_properties->form_bg_color};"."\n";
		}else if($theme_properties->form_bg_type == 'pattern'){
			$form_container_style .= "background-image: url('{$mf_settings['base_url']}images/form_resources/{$theme_properties->form_bg_pattern}');"."\n";
			$form_container_style .= "background-repeat: repeat;"."\n";
		}else if($theme_properties->form_bg_type == 'custom'){
			$form_container_style .= "background-image: url('{$theme_properties->form_bg_custom}');"."\n";
			$form_container_style .= "background-repeat: repeat;"."\n";
		}
		
		/** Form Border **/
		if(!empty($theme_properties->border_form_width)){
			if($theme_properties->border_form_width == 1){
				if(strtolower($theme_properties->border_form_color) == '#cccccc'){
					$form_container_style .= "border-width: 0px;"."\n";
				}else{
					$form_container_style .= "border-width: 1px;"."\n";
				}
				
			}else{
				$form_container_style .= "border-width: {$theme_properties->border_form_width}px;"."\n";
			}
		}else{
			$form_container_style .= "border-width: 0px;"."\n";
			$form_container_style .= "box-shadow: none;"."\n";
		}
		
		if(!empty($theme_properties->border_form_style)){
			$form_container_style .= "border-style: {$theme_properties->border_form_style};"."\n";
		}
		
		if(!empty($theme_properties->border_form_color)){
			$form_container_style .= "border-color: {$theme_properties->border_form_color};"."\n";
		}
		
		$form_container_style .= "}"."\n\n";
		$css_content .= $form_container_style;
		
		/** Field Highlight **/
		$field_highlight_style = "#main_body form li.highlighted,#main_body .matrix tbody tr:hover td,#machform_review_table tr.alt";
		$field_highlight_style .= "\n"."{"."\n";
		
		if($theme_properties->highlight_bg_type == 'color'){
			$field_highlight_style .= "background-color: {$theme_properties->highlight_bg_color};"."\n";
		}else if($theme_properties->highlight_bg_type == 'pattern'){
			$field_highlight_style .= "background-image: url('{$mf_settings['base_url']}images/form_resources/{$theme_properties->highlight_bg_pattern}');"."\n";
			$field_highlight_style .= "background-repeat: repeat;"."\n";
		}else if($theme_properties->highlight_bg_type == 'custom'){
			$field_highlight_style .= "background-image: url('{$theme_properties->highlight_bg_custom}');"."\n";
			$field_highlight_style .= "background-repeat: repeat;"."\n";
		}
		
		$field_highlight_style .= "}"."\n\n";
		$css_content .= $field_highlight_style;
		
		/** Field Guidelines **/
		$field_guidelines_style = "#main_body form .guidelines";
		$field_guidelines_style .= "\n"."{"."\n";
		
		if($theme_properties->guidelines_bg_type == 'color'){
			$field_guidelines_style .= "background-color: {$theme_properties->guidelines_bg_color};"."\n";
		}else if($theme_properties->guidelines_bg_type == 'pattern'){
			$field_guidelines_style .= "background-image: url('{$mf_settings['base_url']}images/form_resources/{$theme_properties->guidelines_bg_pattern}');"."\n";
			$field_guidelines_style .= "background-repeat: repeat;"."\n";
		}else if($theme_properties->guidelines_bg_type == 'custom'){
			$field_guidelines_style .= "background-image: url('{$theme_properties->guidelines_bg_custom}');"."\n";
			$field_guidelines_style .= "background-repeat: repeat;"."\n";
		}
		
		//guidelines border
		if(!empty($theme_properties->border_guidelines_width)){
			$field_guidelines_style .= "border-width: {$theme_properties->border_guidelines_width}px;"."\n";
		}else{
			$field_guidelines_style .= "border-width: 0px;"."\n";
		}
		
		if(!empty($theme_properties->border_guidelines_style)){
			$field_guidelines_style .= "border-style: {$theme_properties->border_guidelines_style};"."\n";
		}
		
		if(!empty($theme_properties->border_guidelines_color)){
			$field_guidelines_style .= "border-color: {$theme_properties->border_guidelines_color};"."\n";
		}
		
		$field_guidelines_style .= "}"."\n\n";
		$css_content .= $field_guidelines_style;
		
		//guidelines font
		$field_guidelines_text_style = "#main_body form .guidelines small";
		$field_guidelines_text_style .= "\n"."{"."\n";
		
		if(!empty($theme_properties->guidelines_font_type)){
			$field_guidelines_text_style .= "font-family: '{$theme_properties->guidelines_font_type}','Lucida Grande',Tahoma,Arial,sans-serif;"."\n";
		}
		
		if(!empty($theme_properties->guidelines_font_weight)){
			$field_guidelines_text_style .= "font-weight: {$theme_properties->guidelines_font_weight};"."\n";
		}
		
		if(!empty($theme_properties->guidelines_font_style)){
			$field_guidelines_text_style .= "font-style: {$theme_properties->guidelines_font_style};"."\n";
		}
		
		if(!empty($theme_properties->guidelines_font_size)){
			$field_guidelines_text_style .= "font-size: {$theme_properties->guidelines_font_size};"."\n";
		}
		
		if(!empty($theme_properties->guidelines_font_color)){
			$field_guidelines_text_style .= "color: {$theme_properties->guidelines_font_color};"."\n";
		}
		
		$field_guidelines_text_style .= "}"."\n\n";
		$css_content .= $field_guidelines_text_style;
		
		
		/** Field Box **/
		$field_box_style = "#main_body input.text,#main_body input.file,#main_body textarea.textarea,#main_body select.select,#main_body input.checkbox,#main_body input.radio";
		$field_box_style .= "\n"."{"."\n";
		
		if($theme_properties->field_bg_type == 'color'){
			$field_box_style .= "background-color: {$theme_properties->field_bg_color};"."\n";
		}else if($theme_properties->field_bg_type == 'pattern'){
			$field_box_style .= "background-image: url('{$mf_settings['base_url']}images/form_resources/{$theme_properties->field_bg_pattern}');"."\n";
			$field_box_style .= "background-repeat: repeat;";
		}else if($theme_properties->field_bg_type == 'custom'){
			$field_box_style .= "background-image: url('{$theme_properties->field_bg_custom}');"."\n";
			$field_box_style .= "background-repeat: repeat;"."\n";
		}
		
		//field text values
		$font_family_array = '';
		if(!empty($theme_properties->field_text_font_type)){
			$field_box_style .= "font-family: '{$theme_properties->field_text_font_type}','Lucida Grande',Tahoma,Arial,sans-serif;"."\n";
			$font_family_array .= $theme_properties->field_text_font_type;
		}
		
		if(!empty($theme_properties->field_text_font_weight)){
			$field_box_style .= "font-weight: {$theme_properties->field_text_font_weight};"."\n";
		}
		
		if(!empty($theme_properties->field_text_font_style)){
			$field_box_style .= "font-style: {$theme_properties->field_text_font_style};"."\n";
		}
		
		if(!empty($theme_properties->field_text_font_size)){
			$field_box_style .= "font-size: {$theme_properties->field_text_font_size};"."\n";
		}
		
		if(!empty($theme_properties->field_text_font_color)){
			$field_box_style .= "color: {$theme_properties->field_text_font_color};"."\n";
		}
		
		$field_box_style .= "}"."\n\n";
		$css_content .= $field_box_style;

		/** Review Table, value section (right column) **/
		//this is similar as field box above, except without background
		$review_table_value_style = "#machform_review_table td.mf_review_value";
		$review_table_value_style .= "\n"."{"."\n";

		if(!empty($theme_properties->field_text_font_type)){
			$review_table_value_style .= "font-family: '{$theme_properties->field_text_font_type}','Lucida Grande',Tahoma,Arial,sans-serif;"."\n";
		}
		
		if(!empty($theme_properties->field_text_font_weight)){
			$review_table_value_style .= "font-weight: {$theme_properties->field_text_font_weight};"."\n";
		}
		
		if(!empty($theme_properties->field_text_font_style)){
			$review_table_value_style .= "font-style: {$theme_properties->field_text_font_style};"."\n";
		}
		
		if(!empty($theme_properties->field_text_font_size)){
			$review_table_value_style .= "font-size: {$theme_properties->field_text_font_size};"."\n";
		}
		
		//on review page, special for the value color should be the same as label color
		if(!empty($theme_properties->field_title_font_color)){
			$review_table_value_style .= "color: {$theme_properties->field_title_font_color};"."\n";
		}

		$review_table_value_style .= "}"."\n\n";
		$css_content .= $review_table_value_style;
		
		/** Form Title **/
		$form_title_style = "#main_body .form_description h2,#main_body .form_success h2";
		$form_title_style .= "\n"."{"."\n";
		
		if(!empty($theme_properties->form_title_font_type)){
			$form_title_style .= "font-family: '{$theme_properties->form_title_font_type}','Lucida Grande',Tahoma,Arial,sans-serif;"."\n";
		}
		
		if(!empty($theme_properties->form_title_font_weight)){
			$form_title_style .= "font-weight: {$theme_properties->form_title_font_weight};"."\n";
		}
		
		if(!empty($theme_properties->form_title_font_style)){
			$form_title_style .= "font-style: {$theme_properties->form_title_font_style};"."\n";
		}
		
		if(!empty($theme_properties->form_title_font_size)){
			$form_title_style .= "font-size: {$theme_properties->form_title_font_size};"."\n";
		}
		
		if(!empty($theme_properties->form_title_font_color)){
			$form_title_style .= "color: {$theme_properties->form_title_font_color};"."\n";
		}
		
		$form_title_style .= "}"."\n\n";
		$css_content .= $form_title_style;
		
		/** Form Description **/
		$form_desc_style = "#main_body .form_description p,#main_body form ul.payment_list_items li";
		$form_desc_style .= "\n"."{"."\n";
		
		if(!empty($theme_properties->form_desc_font_type)){
			$form_desc_style .= "font-family: '{$theme_properties->form_desc_font_type}','Lucida Grande',Tahoma,Arial,sans-serif;"."\n";
		}
		
		if(!empty($theme_properties->form_desc_font_weight)){
			$form_desc_style .= "font-weight: {$theme_properties->form_desc_font_weight};"."\n";
		}
		
		if(!empty($theme_properties->form_desc_font_style)){
			$form_desc_style .= "font-style: {$theme_properties->form_desc_font_style};"."\n";
		}
		
		if(!empty($theme_properties->form_desc_font_size)){
			$form_desc_style .= "font-size: {$theme_properties->form_desc_font_size};"."\n";
		}
		
		if(!empty($theme_properties->form_desc_font_color)){
			$form_desc_style .= "color: {$theme_properties->form_desc_font_color};"."\n";
		}
		
		$form_desc_style .= "}"."\n\n";
		$css_content .= $form_desc_style;

		/** Pagination Text **/
		$pagination_desc_style = "#main_body form li span.ap_tp_text";
		$pagination_desc_style .= "\n"."{"."\n";

		if(!empty($theme_properties->form_desc_font_color)){
			$pagination_desc_style .= "color: {$theme_properties->form_desc_font_color};"."\n";
		}

		$pagination_desc_style .= "}"."\n\n";
		$css_content .= $pagination_desc_style;

		
		/** Field Title **/
		$field_title_style 	   = "#main_body form li label.description,#main_body form li span.description,#main_body .matrix caption,#main_body .matrix td.first_col,#main_body form li.total_payment span,#machform_review_table td.mf_review_label";
		$field_sub_title_style = "#main_body form li span label,#main_body label.choice,#main_body .matrix th,#main_body form li span.symbol,.mf_sigpad_clear,#main_body form li div label,#main_body form li div span.label";
		
		$field_title_style .= "\n"."{"."\n";
		$field_sub_title_style .= "\n"."{"."\n";
		
		if(!empty($theme_properties->field_title_font_type)){
			$field_title_style .= "font-family: '{$theme_properties->field_title_font_type}','Lucida Grande',Tahoma,Arial,sans-serif;"."\n";
			$field_sub_title_style .= "font-family: '{$theme_properties->field_title_font_type}','Lucida Grande',Tahoma,Arial,sans-serif;"."\n";
		}
		
		if(!empty($theme_properties->field_title_font_weight)){
			$field_title_style .= "font-weight: {$theme_properties->field_title_font_weight};"."\n";
		}
		
		if(!empty($theme_properties->field_title_font_style)){
			$field_title_style .= "font-style: {$theme_properties->field_title_font_style};"."\n";
		}
		
		if(!empty($theme_properties->field_title_font_size)){
			$field_title_style .= "font-size: {$theme_properties->field_title_font_size};"."\n";
		}
		
		if(!empty($theme_properties->field_title_font_color)){
			$field_title_style .= "color: {$theme_properties->field_title_font_color};"."\n";
			$field_sub_title_style .= "color: {$theme_properties->field_title_font_color};"."\n";
		}
		
		$field_title_style .= "}"."\n\n";
		$css_content .= $field_title_style;
		
		$field_sub_title_style .= "}"."\n\n";
		$css_content .= $field_sub_title_style;
		
		/** Section Title **/
		$section_title_style = "#main_body form .section_break h3,#main_body form .media h3,#machform_review_table td .mf_section_title";
		$section_title_style .= "\n"."{"."\n";
		
		if(!empty($theme_properties->section_title_font_type)){
			$section_title_style .= "font-family: '{$theme_properties->section_title_font_type}','Lucida Grande',Tahoma,Arial,sans-serif;"."\n";
		}
		
		if(!empty($theme_properties->section_title_font_weight)){
			$section_title_style .= "font-weight: {$theme_properties->section_title_font_weight};"."\n";
		}
		
		if(!empty($theme_properties->section_title_font_style)){
			$section_title_style .= "font-style: {$theme_properties->section_title_font_style};"."\n";
		}
		
		if(!empty($theme_properties->section_title_font_size)){
			$section_title_style .= "font-size: {$theme_properties->section_title_font_size};"."\n";
		}
		
		if(!empty($theme_properties->section_title_font_color)){
			$section_title_style .= "color: {$theme_properties->section_title_font_color};"."\n";
		}
		
		$section_title_style .= "}"."\n\n";
		$css_content .= $section_title_style;
		
		/** Section Description **/
		$section_desc_style = "#main_body form .section_break p,#main_body form .media p,#machform_review_table td .mf_section_content";
		$section_desc_style .= "\n"."{"."\n";
		
		if(!empty($theme_properties->section_desc_font_type)){
			$section_desc_style .= "font-family: '{$theme_properties->section_desc_font_type}','Lucida Grande',Tahoma,Arial,sans-serif;"."\n";
		}
		
		if(!empty($theme_properties->section_desc_font_weight)){
			$section_desc_style .= "font-weight: {$theme_properties->section_desc_font_weight};"."\n";
		}
		
		if(!empty($theme_properties->section_desc_font_style)){
			$section_desc_style .= "font-style: {$theme_properties->section_desc_font_style};"."\n";
		}
		
		if(!empty($theme_properties->section_desc_font_size)){
			$section_desc_style .= "font-size: {$theme_properties->section_desc_font_size};"."\n";
		}
		
		if(!empty($theme_properties->section_desc_font_color)){
			$section_desc_style .= "color: {$theme_properties->section_desc_font_color};"."\n";
		}
		
		$section_desc_style .= "}"."\n\n";
		$css_content .= $section_desc_style;
		
		/** Section Block **/
		$section_block_style = "#main_body form li.section_break";
		$section_block_style .= "\n"."{"."\n";
		
		if(!empty($theme_properties->border_section_width)){
			$section_block_style .= "border-top-width: {$theme_properties->border_section_width}px;"."\n";
		}else{
			$section_block_style .= "border-top-width: 0px;"."\n";
		}
		
		if(!empty($theme_properties->border_section_style)){
			$section_block_style .= "border-top-style: {$theme_properties->border_section_style};"."\n";
		}
		
		if(!empty($theme_properties->border_section_color)){
			$section_block_style .= "border-top-color: {$theme_properties->border_section_color};"."\n";
		}
		
		$section_block_style .= "}"."\n\n";
		$css_content .= $section_block_style;
		
		/** Advanced CSS Code **/
		if(!empty($theme_properties->advanced_css)){
			$css_content .= "\n\n".'/** Advanced CSS **/'."\n\n";
			$css_content .= $theme_properties->advanced_css;
		}
		
		return $css_content;
		
	}
	
	//generate the links to the fonts
	function mf_theme_get_fonts_link($dbh,$theme_id){
		
		$font_family_array = array();
		
		$query = "SELECT
						form_title_font_type,
						form_desc_font_type,
						field_title_font_type,
						guidelines_font_type,
						section_title_font_type,
						section_desc_font_type,
						field_text_font_type
					FROM
						`".MF_TABLE_PREFIX."form_themes`
				   WHERE
				   		theme_id=? and `status`=1";
		$params = array($theme_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		$font_family_array[] = $row['form_title_font_type'];
		$font_family_array[] = $row['form_desc_font_type'];
		$font_family_array[] = $row['field_title_font_type'];
		$font_family_array[] = $row['guidelines_font_type'];
		$font_family_array[] = $row['section_title_font_type'];
		$font_family_array[] = $row['section_desc_font_type'];
		$font_family_array[] = $row['field_text_font_type'];
		
		/** Build the font CSS tag **/
		if(!empty($font_family_array)){
			$font_family_joined = implode("','",$font_family_array);
			
			$query = "SELECT font_family,font_variants FROM ".MF_TABLE_PREFIX."fonts WHERE font_family IN('{$font_family_joined}')";
			$params = array();
		
			$sth = mf_do_query($query,$params,$dbh);
			$font_css_array = array();
			while($row = mf_do_fetch_result($sth)){
				$font_css_array[] = urlencode($row['font_family']).":".$row['font_variants'];
			}
			
			$ssl_suffix = mf_get_ssl_suffix();

			$font_css_markup = implode('|',$font_css_array);
			if(!empty($font_css_array)){
				$font_css_markup = "<link href='http{$ssl_suffix}://fonts.googleapis.com/css?family={$font_css_markup}' rel='stylesheet' type='text/css'>\n";
			}else{
				$font_css_markup = '';
			}
		}
		
		return $font_css_markup;
	}
?>