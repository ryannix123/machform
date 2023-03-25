<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	//Single Line Text
	function mf_display_text($element){
		global $mf_lang;

		//check for error
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_readonly = '';
		$attr_placeholder = '';
		$attr_aria = array();
		$guidelines = '';
		$el_class = array();
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		} 

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			
			if($element->error_message != 'error_no_display'){
				$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
			}else{
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"range_message_{$element->id}\"";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}

		//check for read-only attribute
		if(!empty($element->is_readonly)){
			$attr_readonly = 'readonly="readonly"';
		}
		
		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}
		
		//check for constraint
		if($element->constraint == 'password'){
			$element_type = 'password';
		}else{
			$element_type = 'text';
		}
		
		//determine default value
		if($element->text_default_type == 'random'){
			$rs_generator = new RandomStringGenerator;

			if($element->text_default_random_type == 'letter'){
				switch ($element->text_default_case) {
					case 'u': $rs_alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; break;
					case 'l': $rs_alphabet = 'abcdefghijklmnopqrstuvwxyz'; break;
					case 'b': $rs_alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; break;
				}
			}else if($element->text_default_random_type == 'number'){
				$rs_alphabet = '0123456789';
			}else if($element->text_default_random_type == 'alphanum'){
				switch ($element->text_default_case) {
					case 'u': $rs_alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'; break;
					case 'l': $rs_alphabet = '0123456789abcdefghijklmnopqrstuvwxyz'; break;
					case 'b': $rs_alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; break;
				}
			}else if($element->text_default_random_type == 'all'){
				switch ($element->text_default_case) {
					case 'u': $rs_alphabet = '"\'`~!@#$%^&*()_-+={}[]|\\:;<>,.?/0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'; break;
					case 'l': $rs_alphabet = '"\'`~!@#$%^&*()_-+={}[]|\\:;<>,.?/0123456789abcdefghijklmnopqrstuvwxyz'; break;
					case 'b': $rs_alphabet = '"\'`~!@#$%^&*()_-+={}[]|\\:;<>,.?/0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; break;
				}
			}

			$rs_generator->setAlphabet($rs_alphabet);

			if(empty($element->text_default_length)){
				$element->text_default_length = 10;
			}

			$element->default_value = htmlspecialchars($element->text_default_prefix.$rs_generator->generate($element->text_default_length));
		}else if($element->text_default_type == 'static'){
			//parse default value for some pre-defined variables
			if(!empty($element->default_value) && !$element->is_design_mode){
				$element->default_value = str_replace(array('{http_referer}','{request_uri}'), array($_SERVER['HTTP_REFERER'],$_SERVER['REQUEST_URI']), $element->default_value);
			}
		} 

		//check for placeholder
		$original_default_value = $element->default_value;
		
		//check for GET parameter to populate default value
		if(isset($_GET['element_'.$element->id]) && $element->text_default_type == 'static'){
			$element->default_value = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id]),ENT_QUOTES);
		}
		
		//check for populated value, if exist, use it instead default_value
		if(isset($element->populated_value['element_'.$element->id]['default_value'])){
			$element->default_value = $element->populated_value['element_'.$element->id]['default_value'];
		}
		
		//check for placeholder
		if( (!empty($element->enable_placeholder) && ($element->default_value !== '') && ($element->default_value == $original_default_value)) ||
			(!empty($element->enable_placeholder) && ($element->default_value === '') && ($original_default_value !== ''))
		 ){
			$attr_placeholder = 'placeholder="'.$original_default_value.'"';
			$element->default_value = '';
		}

		if($element->range_limit_by == 'c'){
			$range_limit_by = $mf_lang['range_type_chars'];
		}else if($element->range_limit_by == 'w'){
			$range_limit_by = $mf_lang['range_type_words'];
		}
		
		if(!empty($element->is_design_mode)){
			$range_limit_by = '<var class="range_limit_by">'.$range_limit_by.'</var>';
		}
		
		$input_handler = '';
		$maxlength = '';
		
		if(!empty($element->range_min) || !empty($element->range_max)){
			$currently_entered_length = 0;
			if(!empty($element->default_value)){
				if($element->range_limit_by == 'c'){
					if(function_exists('mb_strlen')){
						$currently_entered_length = mb_strlen($element->default_value);
					}else{
						$currently_entered_length = strlen($element->default_value);
					}
				}else if($element->range_limit_by == 'w'){
					$currently_entered_length = count(preg_split("/[\s\.]+/", $element->default_value, NULL, PREG_SPLIT_NO_EMPTY));
				}
			}
		}
		
		if(!empty($element->range_min) && !empty($element->range_max)){
			if($element->range_min == $element->range_max){
				$range_min_max_tag = sprintf($mf_lang['range_min_max_same'],"<var id=\"range_max_{$element->id}\">{$element->range_max}</var> {$range_limit_by}");
			}else{
				$range_min_max_tag = sprintf($mf_lang['range_min_max'],"<var id=\"range_min_{$element->id}\">{$element->range_min}</var>","<var id=\"range_max_{$element->id}\">{$element->range_max}</var> {$range_limit_by}");
			}

			$currently_entered_tag = sprintf($mf_lang['range_min_max_entered'],"<var id=\"currently_entered_{$element->id}\">{$currently_entered_length}</var> {$range_limit_by}");

			$range_limit_markup = "<span id=\"range_message_{$element->id}\" class=\"label\">{$range_min_max_tag}&nbsp;&nbsp; <em class=\"currently_entered\">{$currently_entered_tag}</em></span>";
			$input_handler = "onkeyup=\"limit_input({$element->id},'{$element->range_limit_by}',{$element->range_max});\" onchange=\"limit_input({$element->id},'{$element->range_limit_by}',{$element->range_max});\"";
			
			if($element->range_limit_by == 'c'){
				$maxlength = 'maxlength="'.$element->range_max.'"';
			}
		}elseif(!empty($element->range_max)){
			$range_max_tag = sprintf($mf_lang['range_max'],"<var id=\"range_max_{$element->id}\">{$element->range_max}</var> {$range_limit_by}");
			$currently_entered_tag = sprintf($mf_lang['range_max_entered'],"<var id=\"currently_entered_{$element->id}\">{$currently_entered_length}</var> {$range_limit_by}");

			$range_limit_markup = "<span id=\"range_message_{$element->id}\" class=\"label\">{$range_max_tag}&nbsp;&nbsp; <em class=\"currently_entered\">{$currently_entered_tag}</em></span>";
			$input_handler = "onkeyup=\"limit_input({$element->id},'{$element->range_limit_by}',{$element->range_max});\" onchange=\"limit_input({$element->id},'{$element->range_limit_by}',{$element->range_max});\"";
			
			if($element->range_limit_by == 'c'){
				$maxlength = 'maxlength="'.$element->range_max.'"';
			}
		}elseif(!empty($element->range_min)){
			$range_min_tag = sprintf($mf_lang['range_min'],"<var id=\"range_min_{$element->id}\">{$element->range_min}</var> {$range_limit_by}");
			$currently_entered_tag = sprintf($mf_lang['range_min_entered'],"<var id=\"currently_entered_{$element->id}\">{$currently_entered_length}</var> {$range_limit_by}");

			$range_limit_markup = "<span id=\"range_message_{$element->id}\" class=\"label\">{$range_min_tag}&nbsp;&nbsp; <em class=\"currently_entered\">{$currently_entered_tag}</em></span>";
			$input_handler = "onkeyup=\"count_input({$element->id},'{$element->range_limit_by}');\" onchange=\"count_input({$element->id},'{$element->range_limit_by}');\"";
		}else{
			$range_limit_markup = '';
		}
		
		if(!empty($element->is_design_mode)){
			$input_handler = '';
		}
		
		//if there is any error message unrelated with range rules, don't display the range markup
		if(!empty($error_message)){
			$range_limit_markup = '';
		}
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}

		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}
				
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<label class="description" for="element_{$element->id}">{$element->title} {$span_required}</label>
		<div>
			<input id="element_{$element->id}" name="element_{$element->id}" {$attr_placeholder} {$attr_readonly} {$attr_aria} {$maxlength} class="element text {$element->size}" type="{$element_type}" value="{$element->default_value}"  {$input_handler} />
			{$range_limit_markup} 
		</div>{$guidelines} {$error_message}
		</li>
EOT;
		
		return $element_markup;
	}
	
	
	
	//Paragraph Text
	function mf_display_textarea($element){
		global $mf_lang;

		//check for error
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_readonly = '';
		$attr_placeholder = '';
		$attr_aria = array();
		$guidelines = '';
		$el_class = array();
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';

			if($element->error_message != 'error_no_display'){
				$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
			}else{
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"range_message_{$element->id}\"";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}

		//check for read-only attribute
		if(!empty($element->is_readonly)){
			$attr_readonly = 'readonly="readonly"';
		}
		
		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}

		//parse default value for some pre-defined variables
		if(!empty($element->default_value) && !$element->is_design_mode){
			$element->default_value = str_replace(array('{http_referer}','{request_uri}'), array($_SERVER['HTTP_REFERER'],$_SERVER['REQUEST_URI']), $element->default_value);
		}
		
		//check for placeholder
		$original_default_value = $element->default_value;
		
		//check for GET parameter to populate default value
		if(isset($_GET['element_'.$element->id])){
			$element->default_value = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id]),ENT_QUOTES);
		}

		//check for populated value, if exist, use it instead default_value
		if(isset($element->populated_value['element_'.$element->id]['default_value'])){
			$element->default_value = $element->populated_value['element_'.$element->id]['default_value'];
		}

		//check for placeholder
		if( (!empty($element->enable_placeholder) && ($element->default_value !== '') && ($element->default_value == $original_default_value)) ||
			(!empty($element->enable_placeholder) && ($element->default_value === '') && ($original_default_value !== ''))
		 ){
			$attr_placeholder = 'placeholder="'.$original_default_value.'"';
			$element->default_value = '';
		}
		
		if($element->range_limit_by == 'c'){
			$range_limit_by = $mf_lang['range_type_chars'];
		}else if($element->range_limit_by == 'w'){
			$range_limit_by = $mf_lang['range_type_words'];
		}
		
		if(!empty($element->is_design_mode)){
			$range_limit_by = '<var class="range_limit_by">'.$range_limit_by.'</var>';
		}
		
		$input_handler = '';
		
		if(!empty($element->range_min) || !empty($element->range_max)){
			$currently_entered_length = 0;
			if(!empty($element->default_value)){
				if($element->range_limit_by == 'c'){
					if(function_exists('mb_strlen')){
						$currently_entered_length = mb_strlen($element->default_value);
					}else{
						$currently_entered_length = strlen($element->default_value);
					}
				}else if($element->range_limit_by == 'w'){
					$currently_entered_length = count(preg_split("/[\s\.]+/", $element->default_value, NULL, PREG_SPLIT_NO_EMPTY));
				}
			}
		}
		
		if(!empty($element->range_min) && !empty($element->range_max)){
			if($element->range_min == $element->range_max){
				$range_min_max_tag = sprintf($mf_lang['range_min_max_same'],"<var id=\"range_max_{$element->id}\">{$element->range_max}</var> {$range_limit_by}");
			}else{
				$range_min_max_tag = sprintf($mf_lang['range_min_max'],"<var id=\"range_min_{$element->id}\">{$element->range_min}</var>","<var id=\"range_max_{$element->id}\">{$element->range_max}</var> {$range_limit_by}");
			}

			$currently_entered_tag = sprintf($mf_lang['range_min_max_entered'],"<var id=\"currently_entered_{$element->id}\">{$currently_entered_length}</var> {$range_limit_by}");

			$range_limit_markup = "<span id=\"range_message_{$element->id}\" class=\"label\">{$range_min_max_tag}&nbsp;&nbsp; <em class=\"currently_entered\">{$currently_entered_tag}</em></span>";
			$input_handler = "onkeyup=\"limit_input({$element->id},'{$element->range_limit_by}',{$element->range_max});\" onchange=\"limit_input({$element->id},'{$element->range_limit_by}',{$element->range_max});\"";
		}elseif(!empty($element->range_max)){
			$range_max_tag = sprintf($mf_lang['range_max'],"<var id=\"range_max_{$element->id}\">{$element->range_max}</var> {$range_limit_by}");
			$currently_entered_tag = sprintf($mf_lang['range_max_entered'],"<var id=\"currently_entered_{$element->id}\">{$currently_entered_length}</var> {$range_limit_by}");

			$range_limit_markup = "<span id=\"range_message_{$element->id}\" class=\"label\">{$range_max_tag}&nbsp;&nbsp; <em class=\"currently_entered\">{$currently_entered_tag}</em></span>";
			$input_handler = "onkeyup=\"limit_input({$element->id},'{$element->range_limit_by}',{$element->range_max});\" onchange=\"limit_input({$element->id},'{$element->range_limit_by}',{$element->range_max});\"";
		}elseif(!empty($element->range_min)){
			$range_min_tag = sprintf($mf_lang['range_min'],"<var id=\"range_min_{$element->id}\">{$element->range_min}</var> {$range_limit_by}");
			$currently_entered_tag = sprintf($mf_lang['range_min_entered'],"<var id=\"currently_entered_{$element->id}\">{$currently_entered_length}</var> {$range_limit_by}");

			$range_limit_markup = "<span id=\"range_message_{$element->id}\" class=\"label\">{$range_min_tag}&nbsp;&nbsp; <em class=\"currently_entered\">{$currently_entered_tag}</em></span>";
			$input_handler = "onkeyup=\"count_input({$element->id},'{$element->range_limit_by}');\" onchange=\"count_input({$element->id},'{$element->range_limit_by}');\"";
		}else{
			$range_limit_markup = '';
		}
		
		if(!empty($element->is_design_mode)){
			$input_handler = '';
		}
		
		//if there is any error message unrelated with range rules, don't display the range markup
		if(!empty($error_message)){
			$range_limit_markup = '';
		}
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}
		
		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}

$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<label class="description" for="element_{$element->id}">{$element->title} {$span_required}</label>
		<div>
			<textarea id="element_{$element->id}" name="element_{$element->id}" {$attr_placeholder} {$attr_readonly} {$attr_aria} class="element textarea {$element->size}" rows="8" cols="90" {$input_handler}>{$element->default_value}</textarea>
			{$range_limit_markup} 
		</div>{$guidelines} {$error_message}
		</li>
EOT;
		
		return $element_markup;
	}

	//Signature Field
	function mf_display_signature($element){
		global $mf_lang;

		//check for error
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$guidelines = '';
		$el_class = array('signature');
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p class=\"error\">{$element->error_message}</p>";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
		}
		
		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<span class=\"label\">{$element->guidelines}</span>";
		}
		
		//check for populated value, if exist, use it instead default_value
		$signature_default_value = '';
		if(isset($element->populated_value['element_'.$element->id]['default_value']) && !empty($element->populated_value['element_'.$element->id]['default_value'])){
			$signature_default_value = $element->populated_value['element_'.$element->id]['default_value'];
		}
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}
		
		if(!empty($element->is_design_mode)){
			$signature_markup = "<div class=\"signature_pad {$element->size}\"><h6>Signature Pad</h6><span class=\"label signature_guidelines\">{$element->guidelines}</span></div>";
		}else{
			if($element->size == 'small'){
				$canvas_height = 100;
				$line_margin_top = 50;
			}else if($element->size == 'medium'){
				$canvas_height = 150;
				$line_margin_top = 95;
			}else{
				$canvas_height = 300;
				$line_margin_top = 200;
			}

			//load signature value, if exist
			$signature_draw_active  = 'active';
			$signature_draw_display = 'block';
			$signature_type_display = 'none';
			$signature_type_active  = '';
			$signature_text_default_value = '';
			$signature_text_url_param = '';
			$signature_default_value_markup = '';

			if(!empty($signature_default_value)){
				$signature_draw_active = '';
				$signature_type_active = '';
				$signature_draw_display = 'none';
				$signature_type_display = 'none';

				//there are 3 possibilities of signature type:
				//1. the old version, using json format, enclosed with [{ ... }]
				//2. data url format, starting with data:image/png
				//3. simple plain text
				if(substr($signature_default_value, 0,14) == 'data:image/png'){
					$signature_default_value_markup = "signature_pad_{$element->id}.fromDataURL('{$signature_default_value}');";

					$signature_draw_active  = 'active';
					$signature_draw_display = 'block';
				}else if(substr($signature_default_value, 0,2) == '[{'){
					//if this is old json format, leave the signature pad blank and keep the old values in the backend
					$signature_default_value = htmlentities($signature_default_value,ENT_QUOTES);
					
					$signature_draw_active  = 'active';
					$signature_draw_display = 'block';
				}else{
					$signature_type_active  = 'active';
					$signature_type_display = 'block';
					$signature_text_default_value = $signature_default_value;
					$signature_text_url_param = '?signature_text='.$signature_text_default_value;
				}
			}

			$signature_markup = <<<EOT
			<div class="mf_signature_switch" style="text-align: right">
				<a class="sig_switch_draw {$signature_draw_active}" href="javascript: switch_signature_type({$element->id},'draw');">{$mf_lang['signature_draw']}</a> {$mf_lang['signature_or']} 
				<a class="sig_switch_type {$signature_type_active}" href="javascript: switch_signature_type({$element->id},'type');">{$mf_lang['signature_type']}</a>
			</div>
	        <div class="mf_signature_draw" style="display: {$signature_draw_display}">
		        <div class="mf_signature_wrapper {$element->size}" style="height: {$canvas_height}px">
		          <canvas id="mf_canvas_signature_pad_{$element->id}" class="mf_canvas_signature_pad" style="width: 100%; height: 100%"></canvas>
		        </div>
		        {$guidelines}
		        <a class="mf_signature_clear element_{$element->id}_clear" href="javascript:clear_signature({$element->id})">{$mf_lang['signature_clear']}</a>
	        </div>
	        <div class="mf_signature_type" style="display: {$signature_type_display}">
	        	<label class="description" for="element_{$element->id}_text_signature">Full Name</label>
				<input id="element_{$element->id}_text_signature" name="element_{$element->id}_text_signature" data-elementid="{$element->id}" class="element text large text_signature" type="text" value="{$signature_text_default_value}" />
				<div class="mf_signature_wrapper {$element->size}" style="height: 100px;margin-top: 20px">
					<img id="element_{$element->id}_text_signature_img" src="signature_img_renderer.php{$signature_text_url_param}" style="height: 75px;margin-top: 10px;margin-left: 15px"/>	 
				</div> 
				{$guidelines}
	        </div>
	        <input type="hidden" name="element_{$element->id}" id="element_{$element->id}" value="{$signature_default_value}">
	        <script type="text/javascript">	   
				var canvas_{$element->id} = document.getElementById('mf_canvas_signature_pad_{$element->id}');
				var signature_pad_{$element->id} = new SignaturePad(canvas_{$element->id});

				signature_pad_{$element->id}.onEnd = function(){
					$("#element_" + {$element->id}).val(signature_pad_{$element->id}.toDataURL());
				};
				refresh_signature(signature_pad_{$element->id},canvas_{$element->id});
				{$signature_default_value_markup}
			</script>
EOT;
		}

$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<label class="description" for="element_{$element->id}">{$element->title} {$span_required}</label>
		<div id="mf_signature_pad_{$element->id}">
			{$signature_markup} 
		</div> {$error_message}
		</li>
EOT;
		
		return $element_markup;
	}
	
	
	//File Upload
	function mf_display_file($element){
		global $mf_lang;

		//check for error
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$guidelines = '';
		$file_queue = '';
		$uploader_script = '';
		$file_option = '';
		$el_class = array();
		$machform_path = '';
		$size_limit_uploadifive = '';
		$queue_content = '';
		$disabled_tag = '';

		$element->is_design_mode = $element->is_design_mode ?? false;
		$element->is_edit_entry  = $element->is_edit_entry ?? false;
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}

		if(!empty($element->machform_path)){
			$machform_path = $element->machform_path;
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p class=\"error\">{$element->error_message}</p>";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
		}
		
		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}

		//check for populated value 
		if(!empty($element->populated_value['element_'.$element->id]['default_value'])){
			foreach ($element->populated_value['element_'.$element->id]['default_value'] as $data){
				$queue_id = "element_{$element->id}".substr(strtoupper(md5(mt_rand())),0,6);
				
				//trim filename if more than 20 characters
				if(strlen($data['filename']) > 20){
					$display_filename = substr($data['filename'],0,20)."...";
				}else{
					$display_filename = $data['filename'];
				}

				$encoded_filename = base64_encode($data['filename']);
				
				if($element->is_edit_entry){
					$db_live_status = 2;
				}else{
					$db_live_status = 1;
				}

				if(!empty($data['filesize'])){
					$data['filesize'] = '('.$data['filesize'].')';
				}

				$queue_content .= <<<EOT
						<div class="uploadifyQueueItem completed" id="{$queue_id}">
						<div class="cancel">									
							<a href="javascript:remove_attachment('{$encoded_filename}',{$element->form_id},{$element->id},'{$queue_id}',{$db_live_status},{$data['entry_id']});"><img border="0" src="{$machform_path}images/icons/delete.png"></a>
						</div>	
						<span class="fileName">
						  <img align="absmiddle" src="{$machform_path}images/icons/attach.gif" class="file_attached">{$display_filename} {$data['filesize']}
						</span>
						</div>
EOT;
			
			}
			
			
		}
		
		
		
		if(!$element->is_design_mode){
			
			if(!empty($element->populated_value['element_'.$element->id]['file_token'])){
				$file_token = $element->populated_value['element_'.$element->id]['file_token'];
				
				//check for existing listfile
				$listfile_name = $element->machform_data_path.$element->upload_dir."/form_{$element->form_id}/files/listfile_{$file_token}.txt";
				if(file_exists($listfile_name) && MF_STORE_FILES_AS_BLOB !== true){
					$uploaded_files = file($listfile_name, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
					array_shift($uploaded_files);
					array_pop($uploaded_files);
				}else if(MF_STORE_FILES_AS_BLOB === true){
					$query = "SELECT file_content FROM `".MF_TABLE_PREFIX."form_{$element->form_id}_listfiles` where file_token=?";
					$params = array($file_token);
					
					$sth = mf_do_query($query,$params,$element->dbh);
					
					while($row = mf_do_fetch_result($sth)){
						$uploaded_files[] = $row['file_content'];
					}
				}

				if(!empty($uploaded_files)){	
					foreach($uploaded_files as $tmp_filename_path){
						if(MF_STORE_FILES_AS_BLOB !== true){
							$file_size = mf_format_bytes(filesize($tmp_filename_path));
						}
						
						if(!empty($file_size)){
							$file_size = '('.$file_size.')';
						}

						$tmp_filename_only =  basename($tmp_filename_path);
						$filename_value    =  substr($tmp_filename_only,strpos($tmp_filename_only,'-')+1);
						$filename_value    =  str_replace('.tmp', '', $filename_value);			
						$filename_value	   =  str_replace('|','',$filename_value);
						
						$queue_id = "element_{$element->id}".substr(strtoupper(md5(mt_rand())),0,6);
						
						//trim filename if more than 20 characters
						if(strlen($filename_value) > 20){
							$display_filename = substr($filename_value,0,20)."...";
						}else{
							$display_filename = $filename_value;
						}
						
						$encoded_filename = base64_encode($filename_value);
						$queue_content .= <<<EOT
							<div class="uploadifyQueueItem completed" id="{$queue_id}">
							<div class="cancel">									
							<a href="javascript:remove_attachment('{$encoded_filename}',{$element->form_id},{$element->id},'{$queue_id}',0,'{$file_token}');"><img border="0" src="{$machform_path}images/icons/delete.png"></a>
						    </div>		
							<span class="fileName">
							  <img align="absmiddle" src="{$machform_path}images/icons/attach.gif" class="file_attached">{$display_filename} {$file_size}
							</span>
							</div>
EOT;
					}
				}
				
				
				
			}else{
				$file_token = md5(uniqid(rand(), true)); 
			}
			
			//generate parameters for auto upload
			$upload_link_show_tag = '';
			$upload_link_hide_tag = '';

			if(!empty($element->file_auto_upload)){
				$auto_upload = 'true';
			}else{
				$auto_upload = 'false';
				$upload_link_show_tag = "$(\"#element_{$element->id}_upload_link,#element_{$element->id}_upload_link_uploadifive\").show();";
				$upload_link_hide_tag = "$(\"#element_{$element->id}_upload_link,#element_{$element->id}_upload_link_uploadifive\").hide();";
			}
			
			//generate parameters for multi upload
			if(!empty($element->file_enable_multi_upload)){
				$multi_upload = 'true';
				$queue_limit  = $element->file_max_selection;
			}else{
				$multi_upload = 'false';
				$queue_limit  = 1;
			}
			
			//generate parameters for file size limit
			if(!empty($element->file_enable_size_limit)){
				if(!empty($element->file_size_max)){
					$file_size_max_bytes = 1048576 * $element->file_size_max;
					$size_limit 			= "'sizeLimit' : {$file_size_max_bytes},";
					$size_limit_uploadifive = "'fileSizeLimit'  : '{$element->file_size_max}MB',";
				}else{
					$size_limit = "'sizeLimit' : 10485760,"; //default 10MB
					$size_limit_uploadifive = "'fileSizeLimit'  : '0',";
				}
			}
			
			if(!empty($element->file_type_list)){
				$file_type_limit_exts  = str_replace(' ', '', $element->file_type_list);
			}
			
			$msg_queue_limited = sprintf($mf_lang['file_queue_limited'],$queue_limit);
			$msg_upload_max	   = sprintf($mf_lang['file_upload_max'],$element->file_size_max);
			$uploader_script = <<<EOT
<script type="text/javascript">
	$(function(){
		 if(is_support_html5_uploader()){
		 	$('#element_{$element->id}').uploadifive({
		 		'uploadScript'     : '{$machform_path}upload.php',
		 		'buttonText'	   : '{$mf_lang['file_select']}',
		 		'removeCompleted' : false,
				'formData'         : {
									  'form_id': {$element->form_id},
				        			  'element_id': {$element->id},
				        			  'file_token': '{$file_token}'
				                     },
				'auto'             : {$auto_upload},
				'multi'       	   : {$multi_upload},
				'queueSizeLimit' : {$queue_limit},
				{$size_limit_uploadifive}
				'queueID'          : 'element_{$element->id}_queue',
				'onAddQueueItem' : function(file) {
		            var file_type_limit_exts = '{$file_type_limit_exts}';
		            var file_type_limit_exts_array = file_type_limit_exts.split(',');

		            var uploaded_file_ext 	 = file.name.split('.').pop().toLowerCase();
		            
		            var file_exist_in_array = false;
		            $.each(file_type_limit_exts_array,function(index,value){
		            	if(value == uploaded_file_ext){
		            		file_exist_in_array = true;
		            	}
		            });
					if(file_type_limit_exts.trim().length > 0){
			            if(file_exist_in_array == false){
			            	$("#" + file.queueItem.attr('id')).addClass('error');
				            $("#" + file.queueItem.attr('id') + ' span.fileinfo').text(" - {$mf_lang['file_type_limited']}");
			            }
		        	}

		            {$upload_link_show_tag}
		            if($("html").hasClass("embed")){
				    	$.postMessage({mf_iframe_height: $('body').outerHeight(true)}, '*', parent );
				   	}
		        },
				'onUploadComplete' : function(file, response) { 
					{$upload_link_hide_tag}

					var is_valid_response = false;
					try{
						var response_json = JSON.parse(response);
						is_valid_response = true;
					}catch(e){
						is_valid_response = false;
						alert(response);
					}
					var queue_item_id =  file.queueItem.attr('id');
					
					if(is_valid_response == true && response_json.status == "ok"){
						var remove_link = "<a class=\"close\" href=\"javascript:remove_attachment('" + response_json.message + "',{$element->form_id},{$element->id},'" + queue_item_id + "',0,'{$file_token}');\"><img border=\"0\" src=\"{$machform_path}images/icons/delete.png\" /></a>";
						
						$("#" + queue_item_id + " a.close").replaceWith(remove_link);
				        $("#" + queue_item_id + ' span.filename').prepend('<img align="absmiddle" class="file_attached" src="{$machform_path}images/icons/attach.gif">'); 
			        }else{
			        	$("#" + queue_item_id).addClass('error');
			        	$("#" + queue_item_id + " a.close").replaceWith('<img style="float: right" border="0" src="{$machform_path}images/icons/exclamation.png" />');
						$("#" + queue_item_id + " span.fileinfo").text(" - {$mf_lang['file_error_upload']}");
					}

					if($("html").hasClass("embed")){
				    	$.postMessage({mf_iframe_height: $('body').outerHeight(true)}, '*', parent );
				   	} 

					if($("#form_{$element->form_id}").data('form_submitting') === true){
				       	upload_all_files();
					}
				}
			});
			$("#element_{$element->id}_upload_link").remove();
		 }else{
	     	$("#element_{$element->id}_token").remove();
		 }
    });
</script>
<input type="hidden" id="element_{$element->id}_token" name="element_{$element->id}_token" value="{$file_token}" />
<a id="element_{$element->id}_upload_link_uploadifive" style="display: none" href="javascript:$('#element_{$element->id}').uploadifive('upload');">{$mf_lang['file_attach']}</a>
EOT;
			$file_queue = "<div id=\"element_{$element->id}_queue\" class=\"file_queue\">{$queue_content}</div>";
		}
		
		if(!empty($queue_content)){
			$file_queue = "<div id=\"element_{$element->id}_queue\" class=\"file_queue uploadifyQueue\">{$queue_content}</div>";
		}
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}
		
		//disable the file upload field on design mode
		if($element->is_design_mode){
			$disabled_tag = 'disabled="disabled"';
		}

$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<label for="element_{$element->id}" class="description" for="element_{$element->id}">{$element->title} {$span_required}</label>
		<div>
			<input id="element_{$element->id}" name="element_{$element->id}" class="element file" type="file" {$disabled_tag} />
			{$file_queue} 
			{$uploader_script}
		</div>{$file_option} {$guidelines} {$error_message}
		</li>
EOT;
		
		return $element_markup;
	}
	
	//Website
	function mf_display_url($element){
		//check for error
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_readonly = '';
		$attr_aria = array();
		$attr_placeholder = '';
		$guidelines = '';
		$el_class = array();
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}

		//check for read-only attribute
		if(!empty($element->is_readonly)){
			$attr_readonly = 'readonly="readonly"';
		}		
		
		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}
		
		//check for placeholder
		$original_default_value = $element->default_value;

		//check for default value
		if(empty($element->default_value)){
			$element->default_value = 'https://';
		}

		//check for GET parameter to populate default value
		if(isset($_GET['element_'.$element->id])){
			$element->default_value = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id]),ENT_QUOTES);
		}
		
		//check for populated value, if exist, use it instead default_value
		if(!empty($element->populated_value['element_'.$element->id]['default_value'])){
			$element->default_value = $element->populated_value['element_'.$element->id]['default_value'];
		}

		//check for placeholder
		if( (!empty($element->enable_placeholder) && ($element->default_value !== '') && ($element->default_value == $original_default_value)) ||
			(!empty($element->enable_placeholder) && ($element->default_value === '') && ($original_default_value !== ''))
		 ){
			$attr_placeholder = 'placeholder="'.$original_default_value.'"';
			$element->default_value = '';
		}
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}

		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}
			
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<label class="description" for="element_{$element->id}">{$element->title} {$span_required}</label>
		<div>
			<input id="element_{$element->id}" name="element_{$element->id}" {$attr_placeholder} {$attr_readonly} {$attr_aria} class="element text {$element->size}" type="text"  value="{$element->default_value}" /> 
		</div>{$guidelines} {$error_message}
		</li>
EOT;
		
		return $element_markup;
	}
	
	//Email
	function mf_display_email($element){
		//check for error
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_readonly = '';
		$attr_aria = array();
		$attr_placeholder = '';
		$guidelines = '';
		$confirm_email_markup = '';
		$el_class = array();

		$element->is_design_mode = $element->is_design_mode ?? false;
		$element->is_edit_entry  = $element->is_edit_entry ?? false;
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}

		//check for read-only attribute
		if(!empty($element->is_readonly)){
			$attr_readonly = 'readonly="readonly"';
		}
		
		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}
		
		//check for placeholder
		$original_default_value = $element->default_value;

		//check for GET parameter to populate default value
		if(isset($_GET['element_'.$element->id])){
			$element->default_value = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id]),ENT_QUOTES);
		}

		//check for populated value, if exist, use it instead default_value
		if(!empty($element->populated_value['element_'.$element->id]['default_value'])){
			$element->default_value = $element->populated_value['element_'.$element->id]['default_value'];
			
			//if the email field is on a multipage form, and the page containing the field is being loaded
			//we assume the email field already validated, so get the value from the main email field
			if(!isset($element->populated_value['element_'.$element->id.'_confirm']['default_value'])){
				$element->default_value_confirm = $element->default_value;
			}
		}

		//check for populated value (if 'enable confirmation email' enabled), if exist, use it instead default_value
		if(!empty($element->populated_value['element_'.$element->id.'_confirm']['default_value'])){
			$element->default_value_confirm = htmlspecialchars(mf_sanitize($element->populated_value['element_'.$element->id.'_confirm']['default_value']),ENT_QUOTES);
		}

		//check for placeholder
		if( (!empty($element->enable_placeholder) && ($element->default_value !== '') && ($element->default_value == $original_default_value)) ||
			(!empty($element->enable_placeholder) && ($element->default_value === '') && ($original_default_value !== ''))
		 ){
			$attr_placeholder = 'placeholder="'.$original_default_value.'"';
			$element->default_value = '';
		}
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}

		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}

		if(($element->is_design_mode && !empty($element->email_enable_confirmation)) || 
		   (empty($element->is_design_mode) && empty($element->is_edit_entry) && !empty($element->email_enable_confirmation))
		  ){
			$confirm_email_markup = <<<EOT
		<label class="description confirm_email_label" for="element_{$element->id}_confirm">{$element->email_confirm_field_label} {$span_required}</label>
		<div>
			<input id="element_{$element->id}_confirm" name="element_{$element->id}_confirm" class="element text {$element->size} confirm_email_input" type="text" maxlength="255" value="{$element->default_value_confirm}" /> 
		</div>
EOT;
		}
					
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<label class="description" for="element_{$element->id}">{$element->title} {$span_required}</label>
		<div>
			<input id="element_{$element->id}" name="element_{$element->id}" {$attr_placeholder} {$attr_readonly} {$attr_aria} class="element text {$element->size}" type="text" maxlength="255" value="{$element->default_value}" /> 
		</div>{$confirm_email_markup}{$guidelines} {$error_message}
		</li>
		
EOT;
		
		return $element_markup;
	}

	//Rating
	function mf_display_rating($element){
		//check for error
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_readonly = '';
		$attr_aria = array();
		$attr_placeholder = '';
		$guidelines = '';
		$confirm_email_markup = '';
		$el_class = array('rating');

		$element->is_design_mode = $element->is_design_mode ?? false;
		$element->is_edit_entry  = $element->is_edit_entry ?? false;
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}

		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}
		
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}

		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}

		//build rating CSS codes
		switch($element->size){
			case 'small' : $rating_size = 20;break;
			case 'medium' : $rating_size = 30;break;
			case 'large' : $rating_size = 50;break;
		}

		//rating options markup
		$option_markup = '';

		for($i=1;$i<=$element->rating_max;$i++){

			if($element->rating_default == $i){
				$checked = 'checked="checked"';
				
				//default value shouldn't be loaded during edit entry if the field is not admin only
				if($element->is_private == 0 && $element->is_edit_entry){
					$checked = '';
				}
			}else{
				$checked = '';
			}
			
			//check for GET parameter to populate default value
			if(isset($_GET['element_'.$element->id]) && $_GET['element_'.$element->id] == $i){
				$checked = 'checked="checked"';
			}

			//check for populated values
			if(!empty($element->populated_value['element_'.$element->id]['default_value'])){
				$checked = '';

				if($element->populated_value['element_'.$element->id]['default_value'] == $i){
					$checked = 'checked="checked"';
				}
			}

			//only display aria attribute for the first choice field
			if($i > 1){
				$attr_aria = '';
			}

			//disable the rating inputs on edit mode
			$attr_disabled = '';
			if($element->is_design_mode){
				$attr_disabled = 'disabled="disabled"';
				$checked = '';
			}

			$pre_option_markup = '';
			$pre_option_markup .= "<input {$attr_disabled} type=\"radio\" name=\"element_{$element->id}\" value=\"{$i}\" id=\"element_{$element->id}_{$i}\"  {$attr_aria} {$checked} />\n";
			$pre_option_markup .= "<label for=\"element_{$element->id}_{$i}\"><span>{$i}</span></label>\n";

			$option_markup .= $pre_option_markup;
		}
		
		//rating style
		switch($element->rating_style){
			case 'star' : 
					$rating_image_svg = 'm25,3.087l6.8775,14.53059l15.6225,2.20992l-11.37,11.18978l2.775,15.89571l-13.905,-7.61665l-13.90688,7.61665l2.77688,-15.89571l-11.37,-11.18978l15.6225,-2.20992l6.8775,-14.53059z';
					break;
			case 'circle' : 
					$rating_image_svg = 'm25,3c-12.1495,0 -22,9.8505 -22,22s9.8505,22 22,22s22,-9.8505 22,-22s-9.8505,-22 -22,-22zm7.931,33.48767l-7.931,-4.24233l-7.931,4.2405l1.58217,-8.85317l-6.4845,-6.2315l8.91,-1.23017l3.92333,-8.09417l3.92333,8.09417l8.91,1.23017l-6.4845,6.23333l1.58217,8.85317z';
					break;
			case 'love' : 
					$rating_image_svg = 'm25,8.7915c-6.16483,-11.29505 -23.5,-7.9977 -23.5,6.15561c0,9.74569 10.90988,19.71093 23.5,33.05297c12.59208,-13.34204 23.5,-23.30727 23.5,-33.05297c0,-14.2014 -17.38021,-17.36702 -23.5,-6.15561z';
					break;
			case 'thumb' : 
					$rating_image_svg = 'm12.16667,43.75l-9.16667,0l0,-22.5l9.16667,0l0,22.5zm32.29417,-15.7725c-1.57117,-0.21562 -1.05967,-1.37625 0.05683,-1.72875c0.95517,-0.3 2.48233,-0.9375 2.48233,-2.83125c0,-1.26 -0.91667,-2.92875 -4.1635,-2.79375c-2.25133,0.09375 -6.721,-0.37125 -9.12817,-1.65938c1.661,-6.855 1.26133,-16.46438 -3.09467,-16.46438c-2.92233,0 -3.476,3.38813 -4.35417,6.50437c-2.2385,7.95375 -6.072,11.28188 -10.42617,12.90938l0,20.39625c8.03367,1.31437 11.6325,5.19 19.25917,5.19c5.863,0 8.89533,-3.25312 8.89533,-4.99875c0,-0.62813 -0.49867,-1.07438 -1.76,-1.17375c-1.48683,-0.11625 -1.34567,-1.5225 0.05683,-1.78688c2.32467,-0.43875 3.34767,-1.71375 3.34767,-2.89312c0,-0.99188 -0.726,-1.91625 -2.013,-2.21438c-1.5345,-0.35437 -1.21733,-1.41937 0.05683,-1.5225c2.07717,-0.16875 3.09467,-1.4325 3.09467,-2.64375c0,-1.05937 -0.77733,-2.07937 -2.31,-2.28937z';
					break;
		}
        
        //rating label markup
        $rating_label_markup = '';
		if(!empty($element->rating_enable_label) && (!empty($element->rating_label_low) || !empty($element->rating_label_high))){
			$rating_label_markup .= "<p class=\"rating_label rating_label_low\">{$element->rating_label_low}</p>\n";
            $rating_label_markup .= "<p class=\"rating_label rating_label_high\">{$element->rating_label_high}</p>\n";
		}

		$hover_css = <<<EOT
		.mf-rating-{$element->id} input:checked + label:hover,
        .mf-rating-{$element->id} label:hover{
                background-image: var(--hovered-image-{$element->id}); 
                background-repeat: repeat-x;
        }
EOT;

		//dont display hover state when editing the form
		if($element->is_design_mode){
			$hover_css = '';
		}

$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<style type="text/css">
			
			:root{
                --mf-rating-size-{$element->id}: {$rating_size}px;
                --unchecked-image-{$element->id}: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50' viewBox='0 0 50 50'%3e%3cpath fill='%23fff' stroke='%23666' d='{$rating_image_svg}'/%3e%3c/svg%3e");
                --checked-image-{$element->id}: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50' viewBox='0 0 50 50'%3e%3cpath fill='gold' stroke='%23666' stroke-width='2' d='{$rating_image_svg}'/%3e%3c/svg%3e");
                --hovered-image-{$element->id}: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50' viewBox='0 0 50 50'%3e%3cpath fill='%23F5B401' stroke='%23fff' stroke-width='2' d='{$rating_image_svg}'/%3e%3c/svg%3e");
                --max-rating-{$element->id}: {$element->rating_max};
               
            }
                      
            .mf-rating-{$element->id}{
                width: min-content;
                padding: 0.3rem;
            }

            .mf-rating-{$element->id}>div {
                position: relative;
                height: var(--mf-rating-size-{$element->id});
                width: calc(var(--mf-rating-size-{$element->id}) * var(--max-rating-{$element->id}));
                background-image: var(--unchecked-image-{$element->id}); 
                background-size: var(--mf-rating-size-{$element->id}) var(--mf-rating-size-{$element->id});
                background-repeat: repeat-x;
                padding-bottom: 0px !important;
            }

            .mf-rating-{$element->id} label {
                position: absolute;
                height: 100%;
                background-size: var(--mf-rating-size-{$element->id}) var(--mf-rating-size-{$element->id});
            }

            .mf-rating-{$element->id} label:nth-of-type(1) {
                z-index: 10;
                width: calc(100% / var(--max-rating-{$element->id}) * 1);
            }

            .mf-rating-{$element->id} label:nth-of-type(2) {
                z-index: 9;
                width: calc(100% / var(--max-rating-{$element->id}) * 2);
            }

            .mf-rating-{$element->id} label:nth-of-type(3) {
                z-index: 8;
                width: calc(100% / var(--max-rating-{$element->id}) * 3);
            }

            .mf-rating-{$element->id} label:nth-of-type(4) {
                z-index: 7;
                width: calc(100% / var(--max-rating-{$element->id}) * 4);
            }

            .mf-rating-{$element->id} label:nth-of-type(5) {
                z-index: 6;
                width: calc(100% / var(--max-rating-{$element->id}) * 5);
            }
            
            .mf-rating-{$element->id} label:nth-of-type(6) {
                z-index: 5;
                width: calc(100% / var(--max-rating-{$element->id}) * 6);
            }
            
            .mf-rating-{$element->id} label:nth-of-type(7) {
                z-index: 4;
                width: calc(100% / var(--max-rating-{$element->id}) * 7);
            }
            
            .mf-rating-{$element->id} label:nth-of-type(8) {
                z-index: 3;
                width: calc(100% / var(--max-rating-{$element->id}) * 8);
            }
            
             .mf-rating-{$element->id} label:nth-of-type(9) {
                z-index: 2;
                width: calc(100% / var(--max-rating-{$element->id}) * 9);
            }
            
             .mf-rating-{$element->id} label:nth-of-type(10) {
                z-index: 1;
                width: calc(100% / var(--max-rating-{$element->id}) * 10);
            }

            .mf-rating-{$element->id} input:checked + label,
            .mf-rating-{$element->id} input:focus + label{
                background-image: var(--checked-image-{$element->id}); 
                background-repeat: repeat-x;
            }
            {$hover_css}
                        
            .mf-rating-{$element->id} input,
            .mf-rating-{$element->id} label>span{
                border: 0;
                padding: 0;
                margin: 0;
                position: absolute !important;
                height: 1px; 
                width: 1px;
                overflow: hidden;
                clip: rect(1px 1px 1px 1px); 
                clip: rect(1px, 1px, 1px, 1px); 
                clip-path: inset(50%);
                white-space: nowrap; 
            }
		</style>
		<span class="description">{$element->title} {$span_required}</span>
		<div>
			 <fieldset class="mf-rating-{$element->id}">
                <legend style="color: transparent;height: 0px;font-size: 0px;">{$element->title}</legend>
                <div>
                    {$option_markup}
                </div>
                {$rating_label_markup}
    		</fieldset>
		</div>{$guidelines} {$error_message}
		</li>
		
EOT;
		
		return $element_markup;
	}
	
	
	//Phone - Extended
	function mf_display_phone($element){
		//check for error
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_readonly = '';
		$attr_aria = array();
		$guidelines = '';
		$default_value_1 = '';
		$default_value_2 = '';
		$default_value_3 = '';
		$el_class = array('phone');
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}
		
		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}

		//check for read-only attribute
		if(!empty($element->is_readonly)){
			$attr_readonly = 'readonly="readonly"';
		}
		
		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}
		
		//check default value
		if(!empty($element->default_value)){
			//split into (xxx) xxx - xxxx
			$default_value_1 = substr($element->default_value,0,3);
			$default_value_2 = substr($element->default_value,3,3);
			$default_value_3 = substr($element->default_value,6,4);
		}

		//check for GET parameter to populate default value
		if(isset($_GET['element_'.$element->id.'_1'])){
			$default_value_1 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_1']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_2'])){
			$default_value_2 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_2']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_3'])){
			$default_value_3 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_3']),ENT_QUOTES);
		}
		
		//check for populated values, if exist override the default value
		if(!empty($element->populated_value['element_'.$element->id.'_1']['default_value']) || 
		   !empty($element->populated_value['element_'.$element->id.'_2']['default_value']) ||
		   !empty($element->populated_value['element_'.$element->id.'_3']['default_value'])
		){
			$default_value_1 = '';
			$default_value_2 = '';
			$default_value_3 = '';
			$default_value_1 = $element->populated_value['element_'.$element->id.'_1']['default_value'];
			$default_value_2 = $element->populated_value['element_'.$element->id.'_2']['default_value'];
			$default_value_3 = $element->populated_value['element_'.$element->id.'_3']['default_value'];
		}
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}

		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}
		
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<fieldset>
			<legend style="color: transparent;height: 0px;font-size: 0px;">{$element->title}</legend>
			<span class="description">{$element->title} {$span_required}</span>
			<span class="phone_1">
				<input id="element_{$element->id}_1" name="element_{$element->id}_1" {$attr_readonly} {$attr_aria} class="element text" size="3" maxlength="3" value="{$default_value_1}" type="text" /> -
				<label for="element_{$element->id}_1">###</label>
			</span>
			<span class="phone_2">
				<input id="element_{$element->id}_2" name="element_{$element->id}_2" {$attr_readonly} {$attr_aria} class="element text" size="3" maxlength="3" value="{$default_value_2}" type="text" /> -
				<label for="element_{$element->id}_2">###</label>
			</span>
			<span class="phone_3">
		 		<input id="element_{$element->id}_3" name="element_{$element->id}_3" {$attr_readonly} {$attr_aria} class="element text" size="4" maxlength="4" value="{$default_value_3}" type="text" />
				<label for="element_{$element->id}_3">####</label>
			</span>
		</fieldset>
		{$guidelines} {$error_message}
		</li>
EOT;
		

		return $element_markup;
	}
	
	//Phone - Simple
	function mf_display_simple_phone($element){
		//check for error
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_readonly = '';
		$guidelines = '';
		$el_class = array();
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}
		
		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p class=\"error\">{$element->error_message}</p>";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
		}
		
		//check for read-only attribute
		if(!empty($element->is_readonly)){
			$attr_readonly = 'readonly="readonly"';
		}

		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}
		
		//check for GET parameter to populate default value
		if(isset($_GET['element_'.$element->id])){
			$element->default_value = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id]),ENT_QUOTES);
		}

		//check for populated value
		if(!empty($element->populated_value['element_'.$element->id]['default_value'])){
			$element->default_value = $element->populated_value['element_'.$element->id]['default_value'];
		}
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}
		
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<label for="element_{$element->id}" class="description">{$element->title} {$span_required}</label>
		<div>
			<input id="element_{$element->id}" name="element_{$element->id}" {$attr_readonly} class="element text medium" type="text" maxlength="255" value="{$element->default_value}"/> 
		</div>{$guidelines} {$error_message}
		</li>
EOT;
		
		return $element_markup;
	}
	
	
	
	//Date - Normal
	function mf_display_date($element){
		
		global $mf_lang;
		
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_readonly = '';
		$attr_aria = array();
		$guidelines = '';
		$default_value_1 = '';
		$default_value_2 = '';
		$default_value_3 = '';
		$el_class = array('date_field');

		$element->is_design_mode = $element->is_design_mode ?? false;
		$element->is_edit_entry  = $element->is_edit_entry ?? false;
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}
		
		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}

		//check for read-only attribute
		if(!empty($element->is_readonly)){
			$attr_readonly = 'readonly="readonly"';
		}
		
		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}
		
		//check for default value
		$cal_default_value = '';
		if(!empty($element->default_value)){
			//the default value can be mm/dd/yyyy or any valid english date words
			//we need to convert the default value into three parts (mm, dd, yyyy)
			$timestamp = strtotime($element->default_value);

			if(($timestamp !== false) && ($timestamp != -1)){
				$valid_default_date = date('m-d-Y', $timestamp);
				$valid_default_date = explode('-',$valid_default_date);
				
				$default_value_1 = (int) $valid_default_date[0];
				$default_value_2 = (int) $valid_default_date[1];
				$default_value_3 = (int) $valid_default_date[2];
			}else{ //it's not a valid date, display blank
				$default_value_1 = '';
				$default_value_2 = '';
				$default_value_3 = '';
			}
		}
		
		//check for GET parameter to populate default value
		if(isset($_GET['element_'.$element->id.'_1'])){
			$default_value_1 = (int) htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_1']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_2'])){
			$default_value_2 = (int) htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_2']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_3'])){
			$default_value_3 = (int) htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_3']),ENT_QUOTES);
		}

		//if there's value submitted from the form, overwrite the default value
		if(!empty($element->populated_value['element_'.$element->id.'_1']['default_value']) || 
		   !empty($element->populated_value['element_'.$element->id.'_2']['default_value']) ||
		   !empty($element->populated_value['element_'.$element->id.'_3']['default_value'])
		){
			$default_value_1 = '';
			$default_value_2 = '';
			$default_value_3 = '';
			$default_value_1 = (int) $element->populated_value['element_'.$element->id.'_1']['default_value'];
			$default_value_2 = (int) $element->populated_value['element_'.$element->id.'_2']['default_value'];
			$default_value_3 = (int) $element->populated_value['element_'.$element->id.'_3']['default_value'];
		}
		
		if(!empty($default_value_1) && !empty($default_value_2) && !empty($default_value_3)){
			$cal_default_value = "\$('#element_{$element->id}_datepick').datepick('setDate', \$.datepick.newDate({$default_value_3}, {$default_value_1}, {$default_value_2}));";
		}
		
		$machform_path = '';
		if(!empty($element->machform_path)){
			$machform_path = $element->machform_path;
		}


		$cal_min_date = '';
		$cal_max_date = '';
		if(!empty($element->date_enable_range)){
			if(!empty($element->date_range_min)){ //value: yyyy-mm-dd
				$date_range_min = explode('-',$element->date_range_min);
				
				if($date_range_min[1] == '00' && ($date_range_min[2] == '00' || $date_range_min[2] == '01')){
					//this is relative date. format: XXXX-00-YY, where XXXX is number of days and YY is 00 or 01 (negative)
					$date_range_min[0] = (int) $date_range_min[0];

					if($date_range_min[2] == '00'){
						$cal_min_date =  ", minDate: -{$date_range_min[0]}";
					}else if($date_range_min[2] == '01'){ //if the value is negatove
						$cal_min_date =  ", minDate: +{$date_range_min[0]}";
					}
				}else{
					//minDate: YYYY, MM - 1, DD
					$cal_min_date = ", minDate: new Date({$date_range_min[0]}, {$date_range_min[1]} - 1, {$date_range_min[2]})"; 
				}
			}
			
			if(!empty($element->date_range_max)){ //value: yyyy-mm-dd
				$date_range_max = explode('-',$element->date_range_max);
				
				if($date_range_max[1] == '00' && ($date_range_max[2] == '00' || $date_range_max[2] == '01')){
					//this is relative date. format: XXXX-00-YY, where XXXX is number of days and YY is 00 or 01 (negative)
					$date_range_max[0] = (int) $date_range_max[0];
					
					if($date_range_max[2] == '00'){
						$cal_max_date =  ", maxDate: +{$date_range_max[0]}";
					}else if($date_range_max[2] == '01'){ //if the value is negative
						$cal_max_date =  ", maxDate: -{$date_range_max[0]}";
					}
				}else{
					//maxDate: YYYY, MM - 1, DD
					$cal_max_date = ", maxDate: new Date({$date_range_max[0]}, {$date_range_max[1]} - 1, {$date_range_max[2]})";
				}
			}
		}
		if(!empty($element->date_disable_past_future)){
			if($element->date_past_future == 'p'){ //disable past dates
				//set minDate to today's date
				$cal_min_date = ", minDate: new Date(".date('Y').", ".date('m')." - 1, ".date('d').")";
			}else if($element->date_past_future == 'f'){ //disable future dates
				//set maxDate to today's date
				$cal_max_date = ", maxDate: new Date(".date('Y').", ".date('m')." - 1, ".date('d').")";
			}
		}
		
		//disable specific dates or disable days of week
		$cal_disable_specific = '';
		$cal_disable_specific_callback = '';
		if( (!empty($element->date_disable_specific) && !empty($element->date_disabled_list)) || 
			(!empty($element->date_disable_dayofweek) && isset($element->date_disabled_dayofweek_list)) ){
			
			if(!empty($element->date_disable_specific) && !empty($element->date_disabled_list)){
				$date_disabled_list = explode(',',$element->date_disabled_list);
				$disabled_days = '';
				foreach ($date_disabled_list as $a_day){
					$a_day = trim($a_day);
					$a_day_exploded = explode('/',$a_day);
					$disabled_days .= "[".(int) $a_day_exploded[0].", ".(int) $a_day_exploded[1].", {$a_day_exploded[2]}],";
				}
				$disabled_days = rtrim($disabled_days,',');
				$disabled_days = "var disabled_days_{$element->id} = [".$disabled_days."];";
			}else{
				$disabled_days = "var disabled_days_{$element->id} = [];";
			}

			if(!empty($element->date_disable_dayofweek) && isset($element->date_disabled_dayofweek_list)){
				$disabled_dayofweek = "var disabled_dayofweek_{$element->id} = [".$element->date_disabled_dayofweek_list."];";	
			}else{
				$disabled_dayofweek = "var disabled_dayofweek_{$element->id} = [];";
			}

$cal_disable_specific = <<<EOT
{$disabled_days}
{$disabled_dayofweek}
			function disable_days_{$element->id}(date, inMonth) { 
				if (inMonth) { 
			    	if(disabled_dayofweek_{$element->id}.indexOf(date.getDay()) != -1){
			    		return {selectable: false};
			    	}else{
				        for (i = 0; i < disabled_days_{$element->id}.length; i++) { 
				            if (date.getMonth() + 1 == disabled_days_{$element->id}[i][0] && 
				                date.getDate() == disabled_days_{$element->id}[i][1] &&
				                date.getFullYear() == disabled_days_{$element->id}[i][2]
				                ) { 
				                return {dateClass: 'day_disabled', selectable: false}; 
				            } 
				        } 
			        }
			        
			    } 
			    return {}; 
			}	
EOT;
			$cal_disable_specific_callback = ", onDate: disable_days_{$element->id}";
		}
		
		//if this is edit entry, disable any date rules. admin should be able to use any dates
		if(isset($element->is_edit_entry) && $element->is_edit_entry === true){
			$cal_min_date = '';
			$cal_max_date = '';
			$cal_disable_specific_callback = '';
		}

		
$calendar_script = <<<EOT
<script type="text/javascript">
			{$cal_disable_specific}
			$('#element_{$element->id}_datepick').datepick({ 
	    		onSelect: select_date,
	    		showTrigger: '#cal_img_{$element->id}'
	    		{$cal_min_date}
	    		{$cal_max_date}
	    		{$cal_disable_specific_callback}
			});
			{$cal_default_value}
</script>
EOT;

		//don't call the calendar script if this is on edit_form page
		//or when the calendar fiel is read-only
		$cal_img_style = 'display: none';
		if($element->is_design_mode || $element->is_readonly){
			$calendar_script = '';
			$cal_img_style = 'display: block';
		}
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}

		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}
		
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<fieldset>
			<legend style="color: transparent;height: 0px;font-size: 0px;">{$element->title}</legend>
			<span class="description">{$element->title} {$span_required}</span>
			<span class="date_mm">
				<input id="element_{$element->id}_1" name="element_{$element->id}_1" {$attr_readonly} {$attr_aria} class="element text" size="3" maxlength="2" value="{$default_value_1}" type="text" /> /
				<label for="element_{$element->id}_1">{$mf_lang['date_mm']}</label>
			</span>
			<span class="date_dd">
				<input id="element_{$element->id}_2" name="element_{$element->id}_2" {$attr_readonly} {$attr_aria} class="element text" size="3" maxlength="2" value="{$default_value_2}" type="text" /> /
				<label for="element_{$element->id}_2">{$mf_lang['date_dd']}</label>
			</span>
			<span class="date_yyyy">
		 		<input id="element_{$element->id}_3" name="element_{$element->id}_3" {$attr_readonly} {$attr_aria} class="element text" size="5" maxlength="4" value="{$default_value_3}" type="text" />
				<label for="element_{$element->id}_3">{$mf_lang['date_yyyy']}</label>
			</span>
		
			<span id="calendar_{$element->id}">
			    <input type="hidden" value="" name="element_{$element->id}_datepick" id="element_{$element->id}_datepick">
				<div style="{$cal_img_style}"><img id="cal_img_{$element->id}" class="datepicker" src="{$machform_path}images/calendar.png" width="32" alt="Pick a date." /></div>	
			</span>
		</fieldset>
		{$calendar_script}
		{$guidelines} {$error_message}
		</li>
EOT;
	
		return $element_markup;
	}
	
	//Date - Normal
	function mf_display_europe_date($element){
		global $mf_lang;
		
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_readonly = '';
		$guidelines = '';
		$el_class = array('europe_date_field');
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p class=\"error\">{$element->error_message}</p>";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
		}
		
		//check for read-only attribute
		if(!empty($element->is_readonly)){
			$attr_readonly = 'readonly="readonly"';
		}

		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}
		
		//check for GET parameter to populate default value
		if(isset($_GET['element_'.$element->id.'_1'])){
			$default_value_1 = (int) htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_1']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_2'])){
			$default_value_2 = (int) htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_2']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_3'])){
			$default_value_3 = (int) htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_3']),ENT_QUOTES);
		}

		//check for default value
		$cal_default_value = '';
		if(!empty($element->default_value)){
			//the default value can be mm/dd/yyyy or any valid english date words
			//we need to convert the default value into three parts (dd, mm, yyyy)
			$timestamp = strtotime($element->default_value);

			if(($timestamp !== false) && ($timestamp != -1)){
				$valid_default_date = date('d-m-Y', $timestamp);
				$valid_default_date = explode('-',$valid_default_date);
				
				$default_value_1 = (int) $valid_default_date[0];
				$default_value_2 = (int) $valid_default_date[1];
				$default_value_3 = (int) $valid_default_date[2];
			}else{ //it's not a valid date, display blank
				$default_value_1 = '';
				$default_value_2 = '';
				$default_value_3 = '';
			}
		}
		
		//if there's value submitted from the form, overwrite the default value
		if(!empty($element->populated_value['element_'.$element->id.'_1']['default_value']) || 
		   !empty($element->populated_value['element_'.$element->id.'_2']['default_value']) ||
		   !empty($element->populated_value['element_'.$element->id.'_3']['default_value'])
		){
			$default_value_1 = '';
			$default_value_2 = '';
			$default_value_3 = '';
			$default_value_1 = (int) $element->populated_value['element_'.$element->id.'_1']['default_value'];
			$default_value_2 = (int) $element->populated_value['element_'.$element->id.'_2']['default_value'];
			$default_value_3 = (int) $element->populated_value['element_'.$element->id.'_3']['default_value'];
		}
		
		if(!empty($default_value_1) && !empty($default_value_2) && !empty($default_value_3)){
			$cal_default_value = "\$('#element_{$element->id}_datepick').datepick('setDate', \$.datepick.newDate({$default_value_3}, {$default_value_2}, {$default_value_1}));";
		}
		
		$machform_path = '';
		if(!empty($element->machform_path)){
			$machform_path = $element->machform_path;
		}
		
		$cal_min_date = '';
		$cal_max_date = '';
		if(!empty($element->date_enable_range)){
			if(!empty($element->date_range_min)){ //value: yyyy-mm-dd
				$date_range_min = explode('-',$element->date_range_min);
				
				if($date_range_min[1] == '00' && ($date_range_min[2] == '00' || $date_range_min[2] == '01')){
					//this is relative date. format: XXXX-00-YY, where XXXX is number of days and YY is 00 or 01 (negative)
					$date_range_min[0] = (int) $date_range_min[0];

					if($date_range_min[2] == '00'){
						$cal_min_date =  ", minDate: -{$date_range_min[0]}";
					}else if($date_range_min[2] == '01'){ //if the value is negatove
						$cal_min_date =  ", minDate: +{$date_range_min[0]}";
					}
				}else{
					//minDate: YYYY, MM - 1, DD
					$cal_min_date = ", minDate: new Date({$date_range_min[0]}, {$date_range_min[1]} - 1, {$date_range_min[2]})"; 
				}
			}
			
			if(!empty($element->date_range_max)){ //value: yyyy-mm-dd
				$date_range_max = explode('-',$element->date_range_max);
				
				if($date_range_max[1] == '00' && ($date_range_max[2] == '00' || $date_range_max[2] == '01')){
					//this is relative date. format: XXXX-00-YY, where XXXX is number of days and YY is 00 or 01 (negative)
					$date_range_max[0] = (int) $date_range_max[0];
					
					if($date_range_max[2] == '00'){
						$cal_max_date =  ", maxDate: +{$date_range_max[0]}";
					}else if($date_range_max[2] == '01'){ //if the value is negative
						$cal_max_date =  ", maxDate: -{$date_range_max[0]}";
					}
				}else{
					//maxDate: YYYY, MM - 1, DD
					$cal_max_date = ", maxDate: new Date({$date_range_max[0]}, {$date_range_max[1]} - 1, {$date_range_max[2]})";
				}
			}
		}
		if(!empty($element->date_disable_past_future)){
			if($element->date_past_future == 'p'){ //disable past dates
				//set minDate to today's date
				$cal_min_date = ", minDate: new Date(".date('Y').", ".date('m')." - 1, ".date('d').")";
			}else if($element->date_past_future == 'f'){ //disable future dates
				//set maxDate to today's date
				$cal_max_date = ", maxDate: new Date(".date('Y').", ".date('m')." - 1, ".date('d').")";
			}
		}
		
		//disable specific dates or disable days of week
		$cal_disable_specific = '';
		$cal_disable_specific_callback = '';
		if( (!empty($element->date_disable_specific) && !empty($element->date_disabled_list)) || 
			(!empty($element->date_disable_dayofweek) && isset($element->date_disabled_dayofweek_list)) ){
			
			if(!empty($element->date_disable_specific) && !empty($element->date_disabled_list)){
				$date_disabled_list = explode(',',$element->date_disabled_list);
				$disabled_days = '';
				foreach ($date_disabled_list as $a_day){
					$a_day = trim($a_day);
					$a_day_exploded = explode('/',$a_day);
					$disabled_days .= "[".(int) $a_day_exploded[0].", ".(int) $a_day_exploded[1].", {$a_day_exploded[2]}],";
				}
				$disabled_days = rtrim($disabled_days,',');
				$disabled_days = "var disabled_days_{$element->id} = [".$disabled_days."];";
			}else{
				$disabled_days = "var disabled_days_{$element->id} = [];";
			}

			if(!empty($element->date_disable_dayofweek) && isset($element->date_disabled_dayofweek_list)){
				$disabled_dayofweek = "var disabled_dayofweek_{$element->id} = [".$element->date_disabled_dayofweek_list."];";	
			}else{
				$disabled_dayofweek = "var disabled_dayofweek_{$element->id} = [];";
			}

$cal_disable_specific = <<<EOT
{$disabled_days}
{$disabled_dayofweek}
			function disable_days_{$element->id}(date, inMonth) { 
				if (inMonth) { 
			    	if(disabled_dayofweek_{$element->id}.indexOf(date.getDay()) != -1){
			    		return {selectable: false};
			    	}else{
				        for (i = 0; i < disabled_days_{$element->id}.length; i++) { 
				            if (date.getMonth() + 1 == disabled_days_{$element->id}[i][0] && 
				                date.getDate() == disabled_days_{$element->id}[i][1] &&
				                date.getFullYear() == disabled_days_{$element->id}[i][2]
				                ) { 
				                return {dateClass: 'day_disabled', selectable: false}; 
				            } 
				        } 
			        }
			        
			    } 
			    return {}; 
			}	
EOT;
			$cal_disable_specific_callback = ", onDate: disable_days_{$element->id}";
		}
		
		//if this is edit entry, disable any date rules. admin should be able to use any dates
		if($element->is_edit_entry === true){
			$cal_min_date = '';
			$cal_max_date = '';
			$cal_disable_specific_callback = '';
		}

		
$calendar_script = <<<EOT
<script type="text/javascript">
			{$cal_disable_specific}
			$('#element_{$element->id}_datepick').datepick({ 
	    		onSelect: select_europe_date,
	    		showTrigger: '#cal_img_{$element->id}'
	    		{$cal_min_date}
	    		{$cal_max_date}
	    		{$cal_disable_specific_callback}
			});
			{$cal_default_value}
</script>
EOT;

		//don't call the calendar script if this is on edit_form page
		//or when the calendar is read-only
		$cal_img_style = 'display: none';
		if($element->is_design_mode || $element->is_readonly){
			$calendar_script = '';
			$cal_img_style = 'display: block';
		}
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<fieldset>
			<legend style="color: transparent;height: 0px;font-size: 0px;">{$element->title}</legend>
			<span class="description">{$element->title} {$span_required}</span>
			<span class="date_dd">
				<input id="element_{$element->id}_1" name="element_{$element->id}_1" {$attr_readonly} class="element text" size="3" maxlength="2" value="{$default_value_1}" type="text" /> /
				<label for="element_{$element->id}_1">{$mf_lang['date_dd']}</label>
			</span>
			<span class="date_mm">
				<input id="element_{$element->id}_2" name="element_{$element->id}_2" {$attr_readonly} class="element text" size="3" maxlength="2" value="{$default_value_2}" type="text" /> /
				<label for="element_{$element->id}_2">{$mf_lang['date_mm']}</label>
			</span>
			<span class="date_yyyy">
		 		<input id="element_{$element->id}_3" name="element_{$element->id}_3" {$attr_readonly} class="element text" size="5" maxlength="4" value="{$default_value_3}" type="text" />
				<label for="element_{$element->id}_3">{$mf_lang['date_yyyy']}</label>
			</span>
		
			<span id="calendar_{$element->id}">
				<input type="hidden" value="" name="element_{$element->id}_datepick" id="element_{$element->id}_datepick">
				<div style="{$cal_img_style}"><img id="cal_img_{$element->id}" class="datepicker" src="{$machform_path}images/calendar.png" width="32" alt="Pick a date." /></div>	
			</span>
		</fieldset>
		{$calendar_script}
		{$guidelines} {$error_message}
		</li>
EOT;
	
		return $element_markup;
	}
	
	
	//Multiple Choice
	function mf_display_radio($element){
		global $mf_lang;

		//check for error
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$guidelines = '';
		$price_data_tag = '';
		$attr_aria = array();
		$el_class = array('multiple_choice');

		$element->is_design_mode = $element->is_design_mode ?? false;
		$element->is_edit_entry  = $element->is_edit_entry ?? false;

		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}
		
		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->choice_columns)){
			$col_number = (int) $element->choice_columns;
			if($col_number == 2){
				$el_class[] = 'two_columns';
			}else if($col_number == 3){
				$el_class[] = 'three_columns';
			}else if($col_number == 9){
				$el_class[] = 'inline_columns';
			}
		}
		
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
			$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
		}
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}

		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}
		
		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}
		
		$option_markup = '';
		
		//don't shuffle the choice on edit form page
		if(($element->constraint == 'random') && ($element->is_design_mode != true)){
			$temp = $element->options;
			shuffle($temp);
			$element->options = $temp;
		}
		
		$has_price_definition = false;
		$selected_price_value = 0;
		$i=1; 
		foreach ($element->options as $option){
			
			//check for Choice Limit
			//don't display the option if the total entries for the particular option has reached the limit
			if(!$element->is_design_mode && !$element->is_edit_entry && !empty($element->choice_max_entry)){
				$query = "SELECT COUNT(*) total_entry FROM ".MF_TABLE_PREFIX."form_{$element->form_id} where element_{$element->id} = ? and `status` = 1";
				$params = array($option->id);
			
				$sth = mf_do_query($query,$params,$element->dbh);
				$row = mf_do_fetch_result($sth);
				if($row['total_entry'] >= $element->choice_max_entry){
					continue;
				}
			}

			//don't display hidden option, unless this is edit entry page
			if(!empty($option->is_hidden) && !$element->is_edit_entry){
				continue;
			}

			if($option->is_default){
				$checked = 'checked="checked"';
				$selected_price_value = $option->price_definition ?? 0;

				//default value shouldn't be loaded during edit entry if the field is not admin only
				if(!empty($option->is_default) && ($element->is_private == 0) && $element->is_edit_entry){
					$checked = '';
				}
			}else{
				$checked = '';
			}
			
			//check for GET parameter to populate default value
			if(isset($_GET['element_'.$element->id]) && $_GET['element_'.$element->id] == $option->id){
				$checked = 'checked="checked"';
				$selected_price_value = $option->price_definition ?? 0;
			}

			//check for populated values
			if(!empty($element->populated_value['element_'.$element->id]['default_value'])){
				$checked = '';

				if($element->populated_value['element_'.$element->id]['default_value'] == $option->id){
					$checked = 'checked="checked"';
					$selected_price_value = $option->price_definition ?? 0;
				}
			}
			
			if(isset($option->price_definition)){
				$price_definition_data_attr = 'data-pricedef="'.$option->price_definition.'"';
				$has_price_definition = true;
			}else{
				$price_definition_data_attr = '';
			}
			
			//only display aria attribute for the first choice field
			if($i > 1){
				$attr_aria = '';
			}

			$pre_option_markup = '';			
			$pre_option_markup .= "<input id=\"element_{$element->id}_{$option->id}\" {$price_definition_data_attr} name=\"element_{$element->id}\" class=\"element radio\" type=\"radio\" value=\"{$option->id}\" {$attr_aria} {$checked} />\n";
			$pre_option_markup .= "<label class=\"choice\" for=\"element_{$element->id}_{$option->id}\">{$option->option}</label>\n";
			
			$option_markup .= '<span>'.$pre_option_markup."</span>\n";
			$i++;
		}
		
		//if 'other choice' is enabled, add a new choice at the end and add text field
		if(!empty($element->choice_has_other)){
			//check for GET parameter to populate default value
			if(isset($_GET['element_'.$element->id.'_other'])){
				$element->populated_value['element_'.$element->id.'_other']['default_value'] = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_other']),ENT_QUOTES);
			}

			if(!empty($element->populated_value['element_'.$element->id.'_other']['default_value'])){
				$other_value = htmlspecialchars($element->populated_value['element_'.$element->id.'_other']['default_value'],ENT_QUOTES);
				$checked = 'checked="checked"';

				$other_pricevalue = (double) $other_value;
				if(!empty($other_pricevalue)){
					$has_price_definition = true;
					$selected_price_value = $other_pricevalue;
				}
			}else{
				$checked = '';
				$other_value = '';
				$other_pricevalue = '0';	
			}
			
			$pre_option_markup = '';
			$pre_option_markup .= "<input id=\"element_{$element->id}_0\" name=\"element_{$element->id}\" class=\"element radio\" type=\"radio\" value=\"\" data-pricedef=\"{$other_pricevalue}\" {$checked} />\n";
			$pre_option_markup .= "<label class=\"choice other\" for=\"element_{$element->id}_0\">{$element->choice_other_label}</label>\n";
			$pre_option_markup .= "<label for=\"element_{$element->id}_other\" style=\"display: none\">{$element->choice_other_label}</label>\n";
			$pre_option_markup .= "<input type=\"text\" value=\"{$other_value}\" class=\"element text other\" name=\"element_{$element->id}_other\" id=\"element_{$element->id}_other\" onclick=\"\$('#element_{$element->id}_0').prop('checked',true).click();\" />\n";
			
			$option_markup .= '<span>'.$pre_option_markup."</span>\n";
		}

		if($has_price_definition === true){
			$price_data_tag = 'data-pricefield="radio" data-pricevalue="'.$selected_price_value.'"';
		}
		
		if(empty($option_markup)){
			$option_markup = '<span>-- '.$mf_lang['choice_empty'].' --</span>';
		}

$element_markup = <<<EOT
		<li id="li_{$element->id}" {$price_data_tag} {$li_style} {$li_class}>
		<span class="description">{$element->title} {$span_required}</span>
		<div>
			<fieldset>
				<legend style="color: transparent;height: 0px;font-size: 0px;">{$element->title}</legend>
				{$option_markup}
			</fieldset>
		</div>{$guidelines} {$error_message}
		</li>
EOT;
		
		return $element_markup;
	}
	
	//Checkboxes
	function mf_display_checkbox($element){
		//check for error
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$guidelines = '';
		$price_data_tag = '';
		$attr_aria = array();
		$el_class = array('checkboxes');
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}
		
		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->choice_columns)){
			$col_number = (int) $element->choice_columns;
			if($col_number == 2){
				$el_class[] = 'two_columns';
			}else if($col_number == 3){
				$el_class[] = 'three_columns';
			}else if($col_number == 9){
				$el_class[] = 'inline_columns';
			}
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
			$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
		}
		
		//build the class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}

		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}
		
		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}
		
		//check for populated value first, if any exist, unselect all default value
		$is_populated = false;
		foreach ($element->options as $option){
			
			if(!empty($element->populated_value['element_'.$element->id.'_'.$option->id]['default_value'])){
				$is_populated = true;
				break;
			}
		}
	
		$option_markup = '';
		$has_price_definition = false;
		$selected_price_value = 0;
		$i = 1;
		foreach ($element->options as $option){
			//don't display hidden option, unless this is edit entry page
			if(!empty($option->is_hidden) && !$element->is_edit_entry){
				continue;
			}

			if(!$is_populated){
				if($option->is_default && ($element->is_edit_entry !== true)){
					$checked = 'checked="checked"';
					$selected_price_value += (double) $option->price_definition;
				}else if(isset($_GET['element_'.$element->id.'_'.$option->id])){
					$checked = 'checked="checked"';
					$selected_price_value += (double) $option->price_definition;
				}else{
					$checked = '';
				}
			}else{
				
				if(!empty($element->populated_value['element_'.$element->id.'_'.$option->id]['default_value'])){
					$checked = 'checked="checked"';
					$selected_price_value += (double) $option->price_definition;
				}else{
					$checked = '';	
				}
			}
			
			if(isset($option->price_definition)){
				$price_definition_data_attr = 'data-pricedef="'.$option->price_definition.'"';
				$has_price_definition = true;
			}else{
				$price_definition_data_attr = '';
			}
			
			if($i > 1){
				$attr_aria = '';
			}

			$pre_option_markup = '';
			$pre_option_markup .= "<input id=\"element_{$element->id}_{$option->id}\" {$price_definition_data_attr} name=\"element_{$element->id}_{$option->id}\" class=\"element checkbox\" type=\"checkbox\" value=\"1\" {$attr_aria} {$checked} />\n";
			$pre_option_markup .= "<label class=\"choice\" for=\"element_{$element->id}_{$option->id}\">{$option->option}</label>\n";
			
			$option_markup .= '<span>'.$pre_option_markup."</span>\n";

			$i++;
		}
		
		//if 'other checkbox' is enabled, add a new checkbox at the end and add text field
		if(!empty($element->choice_has_other)){
			//check for GET parameter to populate default value
			if(isset($_GET['element_'.$element->id.'_other'])){
				$element->populated_value['element_'.$element->id.'_other']['default_value'] = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_other']),ENT_QUOTES);
			}

			if(!empty($element->populated_value['element_'.$element->id.'_other']['default_value'])){
				$other_value = htmlspecialchars($element->populated_value['element_'.$element->id.'_other']['default_value'],ENT_QUOTES);
				$checked = 'checked="checked"';

				$other_pricevalue = (double) $other_value;
				if(!empty($other_pricevalue)){
					$has_price_definition = true;
					$selected_price_value += $other_pricevalue;
				}
			}else{
				$checked = '';
				$other_value = '';
				$other_pricevalue = '0';	
			}
			
			$pre_option_markup = '';
			$pre_option_markup .= "<input id=\"element_{$element->id}_0\" name=\"element_{$element->id}\" class=\"element checkbox\" onchange=\"clear_cb_other(this,{$element->id});\"  type=\"checkbox\" value=\"\" data-pricedef=\"{$other_pricevalue}\" {$checked} />\n";
			$pre_option_markup .= "<label class=\"choice other\" for=\"element_{$element->id}_0\">{$element->choice_other_label}</label>\n";
			$pre_option_markup .= "<label style=\"display: none\" for=\"element_{$element->id}_other\">{$element->choice_other_label}</label>\n";
			$pre_option_markup .= "<input type=\"text\" value=\"{$other_value}\" class=\"element text other\" name=\"element_{$element->id}_other\" id=\"element_{$element->id}_other\" onclick=\"\$('#element_{$element->id}_0').prop('checked',true);\" />\n";
			
			$option_markup .= '<span>'.$pre_option_markup."</span>\n";
		}
		
		if($has_price_definition === true){
			$selected_price_value = (double) $selected_price_value;
			$price_data_tag = 'data-pricefield="checkbox" data-pricevalue="'.$selected_price_value.'"';
		}
		
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$price_data_tag} {$li_style} {$li_class}>
		<span class="description">{$element->title} {$span_required}</span>
		<div>
			<fieldset>
				<legend style="color: transparent;height: 0px;font-size: 0px;">{$element->title}</legend>
				{$option_markup}
			</fieldset>
		</div>{$guidelines} {$error_message}
		</li>
EOT;
		
		return $element_markup;
	}

	
	//Dropdown
	function mf_display_select($element){
		global $mf_lang;

		//check for error
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$guidelines = '';
		$price_data_tag = '';
		$attr_aria = array();
		$el_class = array('dropdown');

		$element->is_design_mode = $element->is_design_mode ?? false;
		$element->is_edit_entry  = $element->is_edit_entry ?? false;
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}
		
		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}
		
		$option_markup = '';
		$has_price_definition = false;
		$selected_price_value = 0;
		
		$has_default = false;
		foreach ($element->options as $option){
			
			//check for Choice Limit
			//don't display the option if the total entries for the particular option has reached the limit
			if(!$element->is_design_mode && !$element->is_edit_entry && !empty($element->choice_max_entry)){
				$query = "SELECT COUNT(*) total_entry FROM ".MF_TABLE_PREFIX."form_{$element->form_id} where element_{$element->id} = ? and `status` = 1";
				$params = array($option->id);
			
				$sth = mf_do_query($query,$params,$element->dbh);
				$row = mf_do_fetch_result($sth);
				if($row['total_entry'] >= $element->choice_max_entry){
					continue;
				}
			}

			//don't display hidden option, unless this is edit entry page
			if(!empty($option->is_hidden) && !$element->is_edit_entry){
				continue;
			}
			
			if($option->is_default || (isset($_GET['element_'.$element->id]) && $_GET['element_'.$element->id] == $option->id)){
				$selected = 'selected="selected"';
				$has_default = true;
				$selected_price_value = $option->price_definition;

				//default value shouldn't be loaded during edit entry if the field is not admin only
				if(!empty($option->is_default) && ($element->is_private == 0) && $element->is_edit_entry){
					$has_default = false;
					$selected = '';
				}
			}else{
				$selected = '';
			}
			
			if(!empty($element->populated_value['element_'.$element->id]['default_value'])){
				$selected = '';
				if($element->populated_value['element_'.$element->id]['default_value'] == $option->id){
					$selected = 'selected="selected"';
					$selected_price_value = $option->price_definition ?? 0;
				}
			}
			
			if(isset($option->price_definition)){
				$price_definition_data_attr = 'data-pricedef="'.$option->price_definition.'"';
				$has_price_definition = true;
			}else{
				$price_definition_data_attr = '';
			}
			
			$option_markup .= "<option value=\"{$option->id}\" {$price_definition_data_attr} {$selected}>{$option->option}</option>\n";
		}
		
		if(!$has_default && !empty($option_markup)){
			if(!empty($element->populated_value['element_'.$element->id]['default_value'])){
				$option_markup = '<option value=""></option>'."\n".$option_markup;
			}else{
				$option_markup = '<option value="" selected="selected"></option>'."\n".$option_markup;
			}
		}
		
		if(empty($option_markup)){
			$option_markup = '<option value="">--'.$mf_lang['choice_empty'].'--</option>';
		}

		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}

		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}
		
		if($has_price_definition === true){
			$price_data_tag = 'data-pricefield="select" data-pricevalue="'.$selected_price_value.'"';
		}
		
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$price_data_tag} {$li_style} {$li_class}>
		<label class="description" for="element_{$element->id}">{$element->title} {$span_required}</label>
		<div>
		<select class="element select {$element->size}" {$attr_aria} id="element_{$element->id}" name="element_{$element->id}"> 
			{$option_markup}
		</select>
		</div>{$guidelines} {$error_message}
		</li>
EOT;

		return $element_markup;
	}
	
	
	//Name - Simple
	function mf_display_simple_name($element){
		
		global $mf_lang;
		
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_readonly = '';
		$attr_aria = array();
		$guidelines = '';
		$default_value_1 = '';
		$default_value_2 = '';
		
		$el_class = array('simple_name');
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}
		
		//check for read-only attribute
		if(!empty($element->is_readonly)){
			$attr_readonly = 'readonly="readonly"';
		}

		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}
		
		//check for GET parameter to populate default value
		if(isset($_GET['element_'.$element->id.'_1'])){
			$default_value_1 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_1']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_2'])){
			$default_value_2 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_2']),ENT_QUOTES);
		}

		//check for populated values, if exist override the default value
		if(!empty($element->populated_value['element_'.$element->id.'_1']['default_value']) || 
		   !empty($element->populated_value['element_'.$element->id.'_2']['default_value'])
		){
			$default_value_1 = $element->populated_value['element_'.$element->id.'_1']['default_value'];
			$default_value_2 = $element->populated_value['element_'.$element->id.'_2']['default_value'];
		}

		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}

		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}
		
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<fieldset>
		<legend style="color: transparent;height: 0px;font-size: 0px;">{$element->title}</legend>
		<span class="description">{$element->title} {$span_required}</span>
		<span class="simple_name_1">
			<input id="element_{$element->id}_1" name="element_{$element->id}_1" {$attr_readonly} {$attr_aria} type="text" class="element text" maxlength="255" size="8" value="{$default_value_1}" />
			<label for="element_{$element->id}_1">{$mf_lang['name_first']}</label>
		</span>
		<span class="simple_name_2">
			<input id="element_{$element->id}_2" name="element_{$element->id}_2" {$attr_readonly} type="text" class="element text" maxlength="255" size="14" value="{$default_value_2}" />
			<label for="element_{$element->id}_2">{$mf_lang['name_last']}</label>
		</span></fieldset>{$guidelines} {$error_message}
		</li>
EOT;
		
		return $element_markup;
	}
	
	//Name - Simple, with Middle Name
	function mf_display_simple_name_wmiddle($element){
		global $mf_lang;
		
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_readonly = '';
		$attr_aria = array();
		$guidelines = '';
		$default_value_1 = '';
		$default_value_2 = '';
		$default_value_3 = '';
		$el_class = array('simple_name_wmiddle');
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}
		
		//check for read-only attribute
		if(!empty($element->is_readonly)){
			$attr_readonly = 'readonly="readonly"';
		}

		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}
		
		//check for GET parameter to populate default value
		if(isset($_GET['element_'.$element->id.'_1'])){
			$default_value_1 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_1']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_2'])){
			$default_value_2 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_2']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_3'])){
			$default_value_3 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_3']),ENT_QUOTES);
		}
		
		//check for populated values, if exist override the default value
		if(!empty($element->populated_value['element_'.$element->id.'_1']['default_value']) || 
		   !empty($element->populated_value['element_'.$element->id.'_2']['default_value']) ||
		   !empty($element->populated_value['element_'.$element->id.'_3']['default_value'])
		){
			$default_value_1 = $element->populated_value['element_'.$element->id.'_1']['default_value'];
			$default_value_2 = $element->populated_value['element_'.$element->id.'_2']['default_value'];
			$default_value_3 = $element->populated_value['element_'.$element->id.'_3']['default_value'];
		}

		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}

		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}
		
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<fieldset>
		<legend style="color: transparent;height: 0px;font-size: 0px;">{$element->title}</legend>
		<span class="description">{$element->title} {$span_required}</span>
		<span class="simple_name_wmiddle_1">
			<input id="element_{$element->id}_1" name="element_{$element->id}_1" {$attr_readonly} {$attr_aria} type="text" class="element text" maxlength="255" size="8" value="{$default_value_1}" />
			<label for="element_{$element->id}_1">{$mf_lang['name_first']}</label>
		</span>
		<span class="simple_name_wmiddle_2">
			<input id="element_{$element->id}_2" name="element_{$element->id}_2" {$attr_readonly} type="text" class="element text" maxlength="255" size="8" value="{$default_value_2}" />
			<label for="element_{$element->id}_2">{$mf_lang['name_middle']}</label>
		</span>
		<span class="simple_name_wmiddle_3">
			<input id="element_{$element->id}_3" name="element_{$element->id}_3" {$attr_readonly} type="text" class="element text" maxlength="255" size="14" value="{$default_value_3}" />
			<label for="element_{$element->id}_3">{$mf_lang['name_last']}</label>
		</span></fieldset>{$guidelines} {$error_message}
		</li>
EOT;
		
		return $element_markup;
	}

	//Name 
	function mf_display_name($element){
		global $mf_lang;
		
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_readonly = '';
		$attr_aria = array();
		$guidelines = '';
		$default_value_1 = '';
		$default_value_2 = '';
		$default_value_3 = '';
		$default_value_4 = '';
		$el_class = array('fullname');
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}
		
		//check for read-only attribute
		if(!empty($element->is_readonly)){
			$attr_readonly = 'readonly="readonly"';
		}

		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}

		//check for GET parameter to populate default value
		if(isset($_GET['element_'.$element->id.'_1'])){
			$default_value_1 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_1']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_2'])){
			$default_value_2 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_2']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_3'])){
			$default_value_3 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_3']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_4'])){
			$default_value_4 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_4']),ENT_QUOTES);
		}
		
		//check for populated values, if exist override the default value
		if(!empty($element->populated_value['element_'.$element->id.'_1']['default_value']) || 
		   !empty($element->populated_value['element_'.$element->id.'_2']['default_value']) ||
		   !empty($element->populated_value['element_'.$element->id.'_3']['default_value']) ||
		   !empty($element->populated_value['element_'.$element->id.'_4']['default_value'])
		){
			$default_value_1 = $element->populated_value['element_'.$element->id.'_1']['default_value'];
			$default_value_2 = $element->populated_value['element_'.$element->id.'_2']['default_value'];
			$default_value_3 = $element->populated_value['element_'.$element->id.'_3']['default_value'];
			$default_value_4 = $element->populated_value['element_'.$element->id.'_4']['default_value'];
		}
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}

		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}
		
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<fieldset>
		<legend style="color: transparent;height: 0px;font-size: 0px;">{$element->title}</legend>
		<span class="description">{$element->title} {$span_required}</span>
		<span class="fullname_1">
			<input id="element_{$element->id}_1" name="element_{$element->id}_1" {$attr_readonly} type="text" class="element text" maxlength="255" size="2" value="{$default_value_1}" />
			<label for="element_{$element->id}_1">{$mf_lang['name_title']}</label>
		</span>
		<span class="fullname_2">
			<input id="element_{$element->id}_2" name="element_{$element->id}_2" {$attr_readonly} {$attr_aria} type="text" class="element text" maxlength="255" size="8" value="{$default_value_2}" />
			<label for="element_{$element->id}_2">{$mf_lang['name_first']}</label>
		</span>
		<span class="fullname_3">
			<input id="element_{$element->id}_3" name="element_{$element->id}_3" {$attr_readonly} type="text" class="element text" maxlength="255" size="14" value="{$default_value_3}" />
			<label for="element_{$element->id}_3">{$mf_lang['name_last']}</label>
		</span>
		<span class="fullname_4">
			<input id="element_{$element->id}_4" name="element_{$element->id}_4" {$attr_readonly} type="text" class="element text" maxlength="255" size="3" value="{$default_value_4}" />
			<label for="element_{$element->id}_4">{$mf_lang['name_suffix']}</label>
		</span></fieldset>{$guidelines} {$error_message}
		</li>
EOT;
		
		return $element_markup;
	}
	
	//Name, with Middle
	function mf_display_name_wmiddle($element){
		global $mf_lang;
		
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_readonly = '';
		$attr_aria = array();
		$guidelines = '';
		$default_value_1 = '';
		$default_value_2 = '';
		$default_value_3 = '';
		$default_value_4 = '';
		$default_value_5 = '';
		$el_class = array('fullname_wmiddle');
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}

		//check for read-only attribute
		if(!empty($element->is_readonly)){
			$attr_readonly = 'readonly="readonly"';
		}
		
		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}
		
		//check for GET parameter to populate default value
		if(isset($_GET['element_'.$element->id.'_1'])){
			$default_value_1 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_1']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_2'])){
			$default_value_2 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_2']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_3'])){
			$default_value_3 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_3']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_4'])){
			$default_value_4 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_4']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_5'])){
			$default_value_5 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_5']),ENT_QUOTES);
		}
		
		//check for populated values, if exist override the default value
		if(!empty($element->populated_value['element_'.$element->id.'_1']['default_value']) || 
		   !empty($element->populated_value['element_'.$element->id.'_2']['default_value']) ||
		   !empty($element->populated_value['element_'.$element->id.'_3']['default_value']) ||
		   !empty($element->populated_value['element_'.$element->id.'_4']['default_value']) ||
		   !empty($element->populated_value['element_'.$element->id.'_5']['default_value'])
		){
			$default_value_1 = $element->populated_value['element_'.$element->id.'_1']['default_value'];
			$default_value_2 = $element->populated_value['element_'.$element->id.'_2']['default_value'];
			$default_value_3 = $element->populated_value['element_'.$element->id.'_3']['default_value'];
			$default_value_4 = $element->populated_value['element_'.$element->id.'_4']['default_value'];
			$default_value_5 = $element->populated_value['element_'.$element->id.'_5']['default_value'];
		}

		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}

		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}
		
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<fieldset>
		<legend style="color: transparent;height: 0px;font-size: 0px;">{$element->title}</legend>
		<span class="description">{$element->title} {$span_required}</span>
		<span class="namewm_ext">
			<input id="element_{$element->id}_1" name="element_{$element->id}_1" {$attr_readonly} type="text" class="element text large" maxlength="255" value="{$default_value_1}" />
			<label for="element_{$element->id}_1">{$mf_lang['name_title']}</label>
		</span>
		<span class="namewm_first">
			<input id="element_{$element->id}_2" name="element_{$element->id}_2" {$attr_readonly} {$attr_aria} type="text" class="element text large" maxlength="255" value="{$default_value_2}" />
			<label for="element_{$element->id}_2">{$mf_lang['name_first']}</label>
		</span>
		<span class="namewm_middle">
			<input id="element_{$element->id}_3" name="element_{$element->id}_3" {$attr_readonly} type="text" class="element text large" maxlength="255" value="{$default_value_3}" />
			<label for="element_{$element->id}_3">{$mf_lang['name_middle']}</label>
		</span>
		<span class="namewm_last">
			<input id="element_{$element->id}_4" name="element_{$element->id}_4" {$attr_readonly} type="text" class="element text large" maxlength="255" value="{$default_value_4}" />
			<label for="element_{$element->id}_4">{$mf_lang['name_last']}</label>
		</span>
		<span class="namewm_ext">
			<input id="element_{$element->id}_5" name="element_{$element->id}_5" {$attr_readonly} type="text" class="element text large" maxlength="255" value="{$default_value_5}" />
			<label for="element_{$element->id}_5">{$mf_lang['name_suffix']}</label>
		</span></fieldset>{$guidelines} {$error_message}
		</li>
EOT;
		
		return $element_markup;
	}
	
	//Time
	function mf_display_time($element){
		
		global $mf_lang;
		
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_readonly = '';
		$attr_aria = array();
		$guidelines = '';
		$default_value_1 = '';
		$default_value_2 = '';
		$default_value_3 = '';
		$selected_am	 = '';
		$selected_pm     = '';
		$am_pm_markup = '';
		$am_pm_hidden_field_markup = '';
		$am_pm_disabled_attribute = '';

		$el_class = array('time_field');
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}

		//check for read-only attribute
		if(!empty($element->is_readonly)){
			$attr_readonly = 'readonly="readonly"';
		}
		
		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}

		//check for default value
		if(!empty($element->default_value)){
			$timestamp = strtotime($element->default_value);

			if(($timestamp !== false) && ($timestamp != -1)){

				if(!empty($element->time_24hour)){
					$valid_default_time = date('H-i-s-A', $timestamp);
					$valid_default_time = explode('-',$valid_default_time);
				}else{
					$valid_default_time = date('h-i-s-A', $timestamp);
					$valid_default_time = explode('-',$valid_default_time);
				}
				
				$default_value_1 = $valid_default_time[0];
				$default_value_2 = $valid_default_time[1];
				$default_value_3 = $valid_default_time[2];

				if($valid_default_time[3] == 'AM'){
					$selected_am = 'selected';
				}else{
					$selected_pm = 'selected';
				}
				
			}else{ //it's not a valid time, display blank
				$default_value_1 = '';
				$default_value_2 = '';
				$default_value_3 = '';
				$selected_am	 = '';
				$selected_pm     = '';
			}
		}

		//check for GET parameter to populate default value
		if(isset($_GET['element_'.$element->id.'_1'])){
			$default_value_1 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_1']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_2'])){
			$default_value_2 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_2']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_3'])){
			$default_value_3 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_3']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_4'])){
			if($_GET['element_'.$element->id.'_4'] == 'AM'){
				$selected_am = 'selected';
			}else{
				$selected_pm = 'selected';
			}
		}

		//if there's value submitted from the form, override the default value
		if(!empty($element->populated_value['element_'.$element->id.'_1']['default_value']) || 
		   !empty($element->populated_value['element_'.$element->id.'_2']['default_value']) ||
		   !empty($element->populated_value['element_'.$element->id.'_3']['default_value'])
		){
			$default_value_1 = '';
			$default_value_2 = '';
			$default_value_3 = '';
			$default_value_1 = $element->populated_value['element_'.$element->id.'_1']['default_value'];
			$default_value_2 = $element->populated_value['element_'.$element->id.'_2']['default_value'];
			$default_value_3 = $element->populated_value['element_'.$element->id.'_3']['default_value'];
		}
		
		if(!empty($element->populated_value['element_'.$element->id.'_4']['default_value'])){
			if($element->populated_value['element_'.$element->id.'_4']['default_value'] == 'AM'){
				$selected_am = 'selected';
			}else{
				$selected_pm = 'selected';
			}
		}
		
		if(!empty($element->time_showsecond)){
			$seconds_markup =<<<EOT
		<span>
			<input id="element_{$element->id}_3" name="element_{$element->id}_3" {$attr_readonly} class="element text" size="2" type="text" maxlength="2" value="{$default_value_3}" />
			<label for="element_{$element->id}_3">{$mf_lang['time_ss']}</label>
		</span>
EOT;
			$seconds_separator = ':';
		}else{
			$seconds_markup = '';
			$seconds_separator = '';
		}
		
		if(empty($element->time_24hour)){
			
			//since there is no readonly attribute for dropdown, we need to disable it and include the value as hidden field
			if(!empty($element->is_readonly)){
				if($selected_pm == 'selected'){
					$am_pm_hidden_field_value = 'PM';
				}else{
					$am_pm_hidden_field_value = 'AM';
				}

				$am_pm_hidden_field_markup = "<input type=\"hidden\" name=\"element_{$element->id}_4\" value=\"{$am_pm_hidden_field_value}\" />";
				$am_pm_disabled_attribute = 'disabled="disabled"';
			}

			$am_pm_markup =<<<EOT
		<span>
			<select class="element select" style="width:5em" {$am_pm_disabled_attribute} id="element_{$element->id}_4" name="element_{$element->id}_4">
				<option value="AM" {$selected_am}>AM</option>
				<option value="PM" {$selected_pm}>PM</option>
			</select>
			<label for="element_{$element->id}_4">AM/PM</label>
			{$am_pm_hidden_field_markup}
		</span>
EOT;
		}
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}
		
		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}

$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<fieldset>
		<legend style="color: transparent;height: 0px;font-size: 0px;">{$element->title}</legend>
		<span class="description">{$element->title} {$span_required}</span>
		<span>
			<input id="element_{$element->id}_1" name="element_{$element->id}_1" {$attr_readonly} {$attr_aria} class="element text " size="2" type="text" maxlength="2" value="{$default_value_1}" /> : 
			<label for="element_{$element->id}_1">{$mf_lang['time_hh']}</label>
		</span>
		<span>
			<input id="element_{$element->id}_2" name="element_{$element->id}_2" {$attr_readonly} class="element text " size="2" type="text" maxlength="2" value="{$default_value_2}" /> {$seconds_separator} 
			<label for="element_{$element->id}_2">{$mf_lang['time_mm']}</label>
		</span>
		{$seconds_markup}
		{$am_pm_markup}
		</fieldset>
		{$guidelines} {$error_message}
		</li>
EOT;
		
		return $element_markup;
	}
	
	
	//Price
	function mf_display_money($element){
		global $mf_lang;
		
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_readonly = '';
		$attr_aria = array();
		$guidelines = '';
		$price_data_tag = '';
		$default_value_1 = '';
		$default_value_2 = '';
		$el_class = array('price');
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		//if the hidden property is enabled, hide the field
		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
			$el_class[] = 'hidden';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
			}
			
		}
		
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}
		
		//check for read-only attribute
		if(!empty($element->is_readonly)){
			$attr_readonly = 'readonly="readonly"';
		}

		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}

		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}
		
		if($element->constraint != 'yen'){ 
			if($element->constraint == 'pound'){
				$main_cur  = $mf_lang['price_pound_main'];
				$child_cur = $mf_lang['price_pound_sub'];
				$cur_symbol = '&#163;';
			}elseif ($element->constraint == 'euro'){
				$main_cur  = $mf_lang['price_euro_main'];
				$child_cur = $mf_lang['price_euro_sub'];
				$cur_symbol = '&#8364;';
			}elseif ($element->constraint == 'baht'){
				$main_cur  = $mf_lang['price_baht_main'];
				$child_cur = $mf_lang['price_baht_sub'];
				$cur_symbol = '&#3647;';
			}elseif ($element->constraint == 'rupees'){
				$main_cur  = $mf_lang['price_rupees_main'];
				$child_cur = $mf_lang['price_rupees_sub'];
				$cur_symbol = 'Rs';
			}elseif ($element->constraint == 'rand'){
				$main_cur  = $mf_lang['price_rand_main'];
				$child_cur = $mf_lang['price_rand_sub'];
				$cur_symbol = 'R';
			}elseif ($element->constraint == 'reais'){
				$main_cur  = $mf_lang['price_reais_main'];
				$child_cur = $mf_lang['price_reais_sub'];
				$cur_symbol = 'R&#36;';
			}elseif ($element->constraint == 'forint'){
				$main_cur  = $mf_lang['price_forint_main'];
				$child_cur = $mf_lang['price_forint_sub'];
				$cur_symbol = '&#70;&#116;';
			}elseif ($element->constraint == 'franc'){
				$main_cur  = $mf_lang['price_franc_main'];
				$child_cur = $mf_lang['price_franc_sub'];
				$cur_symbol = 'CHF';
			}elseif ($element->constraint == 'koruna'){
				$main_cur  = $mf_lang['price_koruna_main'];
				$child_cur = $mf_lang['price_koruna_sub'];
				$cur_symbol = '&#75;&#269;';
			}elseif ($element->constraint == 'krona'){
				$main_cur  = $mf_lang['price_krona_main'];
				$child_cur = $mf_lang['price_krona_sub'];
				$cur_symbol = 'kr';
			}elseif ($element->constraint == 'leu'){
				$main_cur  = $mf_lang['price_leu_main'];
				$child_cur = $mf_lang['price_leu_sub'];
				$cur_symbol = 'Lei';
			}elseif ($element->constraint == 'pesos'){
				$main_cur  = $mf_lang['price_pesos_main'];
				$child_cur = $mf_lang['price_pesos_sub'];
				$cur_symbol = '&#36;';
			}elseif ($element->constraint == 'ringgit'){
				$main_cur  = $mf_lang['price_ringgit_main'];
				$child_cur = $mf_lang['price_ringgit_sub'];
				$cur_symbol = 'RM';
			}elseif ($element->constraint == 'zloty'){
				$main_cur  = $mf_lang['price_zloty_main'];
				$child_cur = $mf_lang['price_zloty_sub'];
				$cur_symbol = '&#122;&#322;';
			}elseif ($element->constraint == 'riyals'){
				$main_cur  = $mf_lang['price_riyals_main'];
				$child_cur = $mf_lang['price_riyals_sub'];
				$cur_symbol = '&#65020;';
			}else{ //dollar
				$main_cur  = $mf_lang['price_dollar_main'];
				$child_cur = $mf_lang['price_dollar_sub'];
				$cur_symbol = '$';
			}

			//populate default value
			if(isset($element->default_value) && ($element->default_value !== "")){
				$default_value_1_2 = mf_to_float($element->default_value);
				$default_value_1   = floor($default_value_1_2);

				$exp = array();
				$exp = explode('.', $default_value_1_2 - $default_value_1);
				$default_value_2 = $exp[1];	
			}

			//check for GET parameter to populate default value
			if(isset($_GET['element_'.$element->id.'_1'])){
				$default_value_1 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_1']),ENT_QUOTES);
			}
			if(isset($_GET['element_'.$element->id.'_2'])){
				$default_value_2 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_2']),ENT_QUOTES);
			}
			if(isset($_GET['element_'.$element->id])){
				$default_value_1_2 = mf_to_float(htmlspecialchars(mf_sanitize($_GET['element_'.$element->id]),ENT_QUOTES));
				$default_value_1   = floor($default_value_1_2);

				$exp = array();
				$exp = explode('.', $default_value_1_2 - $default_value_1);
				$default_value_2 = $exp[1];	
			}

			//check for populated values, if exist override the default value
			if(!empty($element->populated_value['element_'.$element->id.'_1']['default_value']) || 
			   !empty($element->populated_value['element_'.$element->id.'_2']['default_value'])
			){
				$default_value_1 = '';
				$default_value_2 = '';
				$default_value_1 = $element->populated_value['element_'.$element->id.'_1']['default_value'];
				$default_value_2 = $element->populated_value['element_'.$element->id.'_2']['default_value'];
			}

			if(isset($element->price_definition)){
				$price_value  = $default_value_1.'.'.$default_value_2;
				$price_value  = (double) $price_value;
				
				$price_data_tag = 'data-pricevalue="'.$price_value.'" data-pricefield="money"';
			}		
		
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class} {$price_data_tag}>
		<span class="description">{$element->title} {$span_required}</span>
		<span class="symbol">{$cur_symbol}</span>
		<span class="main_currency">
			<input id="element_{$element->id}_1" name="element_{$element->id}_1" {$attr_readonly} {$attr_aria} class="element text currency" size="10" value="{$default_value_1}" type="text" />		
			<label for="element_{$element->id}_1">{$main_cur}</label>
		</span>
		<span class="sub_currency">
			. <input id="element_{$element->id}_2" name="element_{$element->id}_2" {$attr_readonly} class="element text" size="2" maxlength="2" value="{$default_value_2}" type="text" />
			<label for="element_{$element->id}_2">{$child_cur}</label>
		</span>
		{$guidelines} {$error_message}
		</li>
EOT;

		}else{ //for yen, only display one textfield
			$main_cur  = $mf_lang['price_yen'];
			$cur_symbol = '&#165;';

			$default_value = $element->default_value;

			//check for GET parameter to populate default value
			if(isset($_GET['element_'.$element->id])){
				$default_value = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id]),ENT_QUOTES);
			}
		
			//check for populated values, if exist override the default value
			if(!empty($element->populated_value['element_'.$element->id]['default_value'])){
				$default_value = '';
				$default_value = $element->populated_value['element_'.$element->id]['default_value'];
			}
			
			if(isset($element->price_definition)){
				$price_value  = $default_value;
				$price_value  = (double) $price_value;
				
				$price_data_tag = 'data-pricevalue="'.$price_value.'" data-pricefield="money_simple"';
			}		
			
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class} {$price_data_tag}>
		<span class="description">{$element->title} {$span_required}</span>
		<span class="symbol">{$cur_symbol}</span>
		<span>
			<input id="element_{$element->id}" name="element_{$element->id}" class="element text currency" {$attr_readonly} {$attr_aria} size="10" value="{$default_value}" type="text" />	
			<label for="element_{$element->id}">{$main_cur}</label>
		</span>
		{$guidelines} {$error_message}
		</li>
EOT;
		
		}



		return $element_markup;
	}
	
	//Section Break
	function mf_display_section($element){
		$li_class = '';
		$li_style = '';
		$el_class = array();
		
		$el_class[] = "section_break";
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}
		
		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}

		if(!empty($element->section_enable_scroll)){
			if($element->size == 'large'){
				$el_class[] = 'section_scroll_large';
			}else if($element->size == 'medium'){
				$el_class[] = 'section_scroll_medium';
			}else{
				$el_class[] = 'section_scroll_small';
			}
		}
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}
		
		$element->guidelines = $element->guidelines;			
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
			<h3>{$element->title}</h3>
			<p>{$element->guidelines}</p>
		</li>
EOT;
		
		return $element_markup;
	}
	
	//Page Break
	function mf_display_page_break($element){
		
		$firstpage_class = '';
		
		if($element->page_number == 1){
			$firstpage_class = ' firstpage';
		}
		
		if(isset($element->submit_use_image) && $element->submit_use_image == 1){
			$btn_class = ' hide';
			$image_class = '';
		}else{
			$btn_class = '';
			$image_class = ' hide';
		}
		
		if(empty($element->submit_primary_img)){
			$element->submit_primary_img = 'images/empty.gif';
		}
		
		if(empty($element->submit_secondary_img)){
			$element->submit_secondary_img = 'images/empty.gif';
		}
		
$element_markup = <<<EOT
		<li id="li_{$element->id}" class="page_break{$firstpage_class}" title="Click to edit">
			<div>
				<table class="ap_table_pagination" width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td align="left" style="vertical-align: bottom">
							<input type="submit" disabled="disabled" value="{$element->submit_primary_text}" id="btn_submit_{$element->id}" name="btn_submit_{$element->id}" class="btn_primary btn_submit{$btn_class}">
							<input type="submit" disabled="disabled" value="{$element->submit_secondary_text}" id="btn_prev_{$element->id}" name="btn_prev_{$element->id}" class="btn_secondary btn_submit{$btn_class}">
							<input type="image" disabled="disabled" src="{$element->submit_primary_img}" alt="Continue" value="Continue" id="img_submit_{$element->id}" name="img_submit_{$element->id}" class="img_primary img_submit{$image_class}">
							<input type="image" disabled="disabled" src="{$element->submit_secondary_img}" alt="Previous" value="Previous" id="img_prev_{$element->id}" name="img_prev_{$element->id}" class="img_secondary img_submit{$image_class}">
						</td> 
						<td align="center" style="vertical-align: top" width="75px">
							<span id="pagenum_{$element->id}" name="pagenum_{$element->id}" class="ap_tp_num">{$element->page_number}</span>
							<span id="pagetotal_{$element->id}" name="pagetotal_{$element->id}" class="ap_tp_text">Page {$element->page_number} of {$element->page_total}</span>
						</td>
					</tr>
				</table>
			</div>
		</li>	
EOT;
		
		return $element_markup;
	}
	
	
	
	//Number
	function mf_display_number($element){
		
		global $mf_lang;

		//check for error
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_readonly = '';
		$attr_aria = array();
		$guidelines = '';
		$attr_placeholder = '';
		$quantity_link_data_tag = '';
		$el_class = array();
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
			}else{
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"range_message_{$element->id}\"";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}
		
		//check for read-only attribute
		if(!empty($element->is_readonly)){
			$attr_readonly = 'readonly="readonly"';
		}

		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}

		//check for placeholder
		$original_default_value = $element->default_value;
		
		//check for GET parameter to populate default value
		if(isset($_GET['element_'.$element->id])){
			$element->default_value = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id]),ENT_QUOTES);
		}

		//check for populated value, if exist, use it instead default_value
		if(isset($element->populated_value['element_'.$element->id]['default_value'])){
			$element->default_value = $element->populated_value['element_'.$element->id]['default_value'];
		}

		//check for placeholder
		if( (!empty($element->enable_placeholder) && ($element->default_value !== '') && ($element->default_value == $original_default_value)) ||
			(!empty($element->enable_placeholder) && ($element->default_value === '') && ($original_default_value !== ''))
		 ){
			$attr_placeholder = 'placeholder="'.$original_default_value.'"';
			$element->default_value = '';
		}
		
		$input_handler = '';
		$maxlength = '';
		
		if(!empty($element->range_min) || !empty($element->range_max)){
			$currently_entered_length = 0;
			if(!empty($element->default_value) && $element->range_limit_by == 'd'){
					if(function_exists('mb_strlen')){
						$currently_entered_length = mb_strlen($element->default_value);
					}else{
						$currently_entered_length = strlen($element->default_value);
					}
			}
		}
		
		if($element->range_limit_by == 'd'){
			$range_limit_by = $mf_lang['range_type_digit'];
			
			if(!empty($element->is_design_mode)){
				$range_limit_by = '<var class="range_limit_by">'.$range_limit_by.'</var>';
			}
			
			if(!empty($element->range_min) && !empty($element->range_max)){
				if($element->range_min == $element->range_max){
					$range_min_max_tag = sprintf($mf_lang['range_min_max_same'],"<var id=\"range_max_{$element->id}\">{$element->range_max}</var> {$range_limit_by}");
				}else{
					$range_min_max_tag = sprintf($mf_lang['range_min_max'],"<var id=\"range_min_{$element->id}\">{$element->range_min}</var>","<var id=\"range_max_{$element->id}\">{$element->range_max}</var> {$range_limit_by}");
				}

				$currently_entered_tag = sprintf($mf_lang['range_min_max_entered'],"<var id=\"currently_entered_{$element->id}\">{$currently_entered_length}</var> {$range_limit_by}");

				$range_limit_markup = "<span id=\"range_message_{$element->id}\" class=\"label\">{$range_min_max_tag}&nbsp;&nbsp; <em class=\"currently_entered\">{$currently_entered_tag}</em></span>";
				$input_handler = "onkeyup=\"limit_input({$element->id},'{$element->range_limit_by}',{$element->range_max});\" onchange=\"limit_input({$element->id},'{$element->range_limit_by}',{$element->range_max});\"";
				$maxlength = 'maxlength="'.$element->range_max.'"';
			}elseif(!empty($element->range_max)){
				$range_max_tag = sprintf($mf_lang['range_max'],"<var id=\"range_max_{$element->id}\">{$element->range_max}</var> {$range_limit_by}");
				$currently_entered_tag = sprintf($mf_lang['range_max_entered'],"<var id=\"currently_entered_{$element->id}\">{$currently_entered_length}</var> {$range_limit_by}");

				$range_limit_markup = "<span id=\"range_message_{$element->id}\" class=\"label\">{$range_max_tag}&nbsp;&nbsp; <em class=\"currently_entered\">{$currently_entered_tag}</em></span>";
				$input_handler = "onkeyup=\"limit_input({$element->id},'{$element->range_limit_by}',{$element->range_max});\" onchange=\"limit_input({$element->id},'{$element->range_limit_by}',{$element->range_max});\"";
				$maxlength = 'maxlength="'.$element->range_max.'"';
			}elseif(!empty($element->range_min)){
				$range_min_tag = sprintf($mf_lang['range_min'],"<var id=\"range_min_{$element->id}\">{$element->range_min}</var> {$range_limit_by}");
				$currently_entered_tag = sprintf($mf_lang['range_min_entered'],"<var id=\"currently_entered_{$element->id}\">{$currently_entered_length}</var> {$range_limit_by}");

				$range_limit_markup = "<span id=\"range_message_{$element->id}\" class=\"label\">{$range_min_tag}&nbsp;&nbsp; <em class=\"currently_entered\">{$currently_entered_tag}</em></span>";
				$input_handler = "onkeyup=\"count_input({$element->id},'{$element->range_limit_by}');\" onchange=\"count_input({$element->id},'{$element->range_limit_by}');\"";
			}else{
				$range_limit_markup = '';
			}

			
		}else if($element->range_limit_by == 'v'){
			if(!empty($element->range_min) && !empty($element->range_max)){
				$range_min_max_tag = sprintf($mf_lang['range_number_min_max'],"<var id=\"range_min_{$element->id}\">{$element->range_min}</var>","<var id=\"range_max_{$element->id}\">{$element->range_max}</var>");
				$range_limit_markup = "<span id=\"range_message_{$element->id}\" class=\"label\">{$range_min_max_tag}</span>";
			}elseif(!empty($element->range_max)){
				$range_max_tag = sprintf($mf_lang['range_number_max'],"<var id=\"range_max_{$element->id}\">{$element->range_max}</var>");
				$range_limit_markup = "<span id=\"range_message_{$element->id}\" class=\"label\">{$range_max_tag}</span>";
			}elseif(!empty($element->range_min)){
				$range_min_tag = sprintf($mf_lang['range_number_min'],"<var id=\"range_min_{$element->id}\">{$element->range_min}</var>");
				$range_limit_markup = "<span id=\"range_message_{$element->id}\" class=\"label\">{$range_min_tag}</span>";
			}else{
				$range_limit_markup = '';
			}
		}
		
		if(!empty($element->is_design_mode)){
			$input_handler = '';
		}
		
		//if there is any error message unrelated with range rules, don't display the range markup
		if(!empty($error_message)){
			$range_limit_markup = '';
		}
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}

		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}

		//build the tag for the quantity link if exist
		if(!empty($element->number_quantity_link)){
			$quantity_link_data_tag = 'data-quantity_link="'.$element->number_quantity_link.'"';
		}
		
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<label class="description" for="element_{$element->id}">{$element->title} {$span_required}</label>
		<div>
			<input id="element_{$element->id}" name="element_{$element->id}" class="element text {$element->size}" {$attr_placeholder} {$attr_readonly} {$attr_aria} type="text" {$maxlength} {$quantity_link_data_tag} value="{$element->default_value}" {$input_handler} /> 
			{$range_limit_markup}
		</div>{$guidelines} {$error_message}
		</li>
EOT;
		
		return $element_markup;
	}
	
	//Media Field
	function mf_display_media($element){
		$li_class = '';
		$li_style = '';
		$media_image_markup = '';
		$media_video_markup = '';
		$media_pdf_markup = '';
		$el_class = array();
		
		$el_class[] = "media";
		if($element->media_type == 'video'){
			$el_class[] = "media_video";
		}else if($element->media_type == 'image'){
			$el_class[] = "media_image";
		}else if($element->media_type == 'pdf'){
			$el_class[] = "media_pdf";
		}

		if(!empty($element->is_design_mode)){
			$el_class[] = "media_design_mode";
		}
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}

		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}

		//default markup to display media icon
		$media_icon_markup = '';
		
		if(!empty($element->is_design_mode)){
			if($element->media_type == 'image'){
				$media_icon = 'icon-image2';
			}else if($element->media_type == 'video'){
				$media_icon = 'icon-play2';
			}else if($element->media_type == 'html'){
				$media_icon = 'icon-embed2';
			}else if($element->media_type == 'pdf'){
				$media_icon = 'icon-file-pdf';
			}
			$media_icon_markup = '<p class="media_icon"><span class="'.$media_icon.'"></span></p>';

			if($element->media_type == 'pdf'){
				//pdf is too heavy for preview
				$media_icon_markup .= '<p class="media_pdf_container media_pdf_preview_unavailable"><span style="font-size:150%" class="icon-info"></span> PDF Preview Unavailable in Edit Mode.</p>';
			}
		}
		
		if($element->media_type == 'image' && !empty($element->media_image_src)){
			$media_icon_markup  = '';

			switch ($element->media_image_alignment) {
				case 'l': $alignment_class = 'media_image_left';break;
				case 'c': $alignment_class = 'media_image_center';break;
				case 'r': $alignment_class = 'media_image_right';break;	
			}

			if(empty($element->media_image_alt)){
				if(empty($element->title)){
					$element->media_image_alt = 'Image';
				}else{
					$element->media_image_alt = $element->title;
				}
				
			}

			if(!empty($element->media_image_href) && !$element->is_design_mode){
				$media_image_markup = <<<EOT
					<div class="media_image_container {$alignment_class}"><a href="{$element->media_image_href}" target="_blank"><img src="{$element->media_image_src}" width="{$element->media_image_width}" height="{$element->media_image_height}" alt="{$element->media_image_alt}" title="{$element->media_image_alt}" class="media_image" /></a></div>
EOT;
			}else{
				$media_image_markup = <<<EOT
					<div class="media_image_container {$alignment_class}"><img src="{$element->media_image_src}" width="{$element->media_image_width}" height="{$element->media_image_height}" alt="{$element->media_image_alt}" title="{$element->media_image_alt}" class="media_image" /></div>
EOT;
			}

		}elseif($element->media_type == 'video' && !empty($element->media_video_src)){
			
			$media_icon_markup  = '';
			$mute_attr = '';
			if(!empty($element->media_video_muted)){
				$mute_attr = 'muted';
			}

			//parse video source
			$media_info = array();
			$media_info = parse_url($element->media_video_src);
			$media_info_host = strtolower($media_info['host']);
			
			//find poster URL if any (poster URL is part of the video URL, separated with '|')
			//example: http://www.youtube.com/xyz|http://www.example.com/poster.png
			$exploded = array(); 
			$exploded = explode('|', $element->media_video_src);
			$poster_attr = '';
			if(!empty($exploded[1])){
				$poster_attr = 'poster="'.$exploded[1].'"';
				$element->media_video_src = $exploded[0];
			}

			//is this youtube url or mp4 url?
			if(in_array($media_info_host, array('www.youtube.com','youtube.com','youtu.be'))){
				//this is youtube video
				$tech_order = '"youtube","html5"';
				$video_type = "video/youtube";
			}else{
				$tech_order = '"html5"';
				$video_type = "video/mp4";
			}

			//check caption url
			//to specify language and label for caption, use this file naming format:
			//http://www.example.com/captionname---en-english.vtt
			$caption_tag = '';
			if(!empty($element->media_video_caption_file)){
				$exploded = array();
				$exploded = explode('---', $element->media_video_caption_file);

				if(!empty($exploded[1])){
					$temp = array();
					$temp = explode('-', $exploded[1]);
					$caption_srclang = $temp[0];
					$caption_label	 = str_replace('.vtt', '', $temp[1]);
				}else{
					$caption_srclang = "en";
					$caption_label 	 = "english";
				}

				$caption_tag = "<track kind=\"captions\" src=\"{$element->media_video_caption_file}\" srclang=\"{$caption_srclang}\" label=\"{$caption_label}\"></track>";
			}

			//don't display captions controls on design
			if($element->is_design_mode){
				$caption_tag = '';
			}

			$media_video_markup = <<<EOT
			<div class="media_video_container {$element->media_video_size}">
				<video
				    id="video_{$element->id}"
				    class="video-js vjs-default-skin vjs-fluid"
				    controls
				    {$mute_attr}
				    {$poster_attr}
				    preload="auto"
				    aspectRatio="16:9" 
				    width="640" height="264"
				    data-setup='{ "techOrder": [{$tech_order}], "sources": [{ "type": "{$video_type}", "src": "{$element->media_video_src}"}] }'
				>
			  	{$caption_tag}	
			  	</video>
			</div>
EOT;

		}elseif($element->media_type == 'pdf' && !empty($element->media_pdf_src) && !$element->is_design_mode){
			//don't display PDF preview on edit mode, too heavy and freeze the window during drag and drop
			$media_icon_markup  = '';

			switch ($element->size) {
				case 'small': $size_class 	= 'media_pdf_small';break;
				case 'medium': $size_class 	= 'media_pdf_medium';break;
				case 'large': $size_class 	= 'media_pdf_large';break;	
			}
		
			$media_pdf_markup = <<<EOT
					<div id="li_{$element->id}_pdf" class="media_pdf_container {$size_class}"></div>
					<script>
							PDFObject.embed("{$element->media_pdf_src}", "#li_{$element->id}_pdf");
					</script>
EOT;

		}

		$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
			<h3>{$element->title}</h3>
			<p class="media_guidelines">{$element->guidelines}</p>
			{$media_icon_markup}
			{$media_image_markup}
			{$media_video_markup}
			{$media_pdf_markup}
		</li>
EOT;

		return $element_markup;
	}

	//simple function to return an array of countries
	function mf_get_country_list(){
		$country[0]['label'] = "United States";
		$country[1]['label'] = "United Kingdom";
		$country[2]['label'] = "Canada";
		$country[3]['label'] = "Australia";
		$country[4]['label'] = "Netherlands";
		$country[5]['label'] = "France";
		$country[6]['label'] = "Germany";
		$country[7]['label'] = "-------";
		$country[8]['label'] = "Afghanistan";
		$country[9]['label'] = "Albania";
		$country[10]['label'] = "Algeria";
		$country[11]['label'] = "Andorra";
		$country[12]['label'] = "Antigua and Barbuda";
		$country[13]['label'] = "Argentina";
		$country[14]['label'] = "Armenia";
		$country[15]['label'] = "Austria";
		$country[16]['label'] = "Azerbaijan";
		$country[17]['label'] = "Bahamas";
		$country[18]['label'] = "Bahrain";
		$country[19]['label'] = "Bangladesh";
		$country[20]['label'] = "Barbados";
		$country[21]['label'] = "Belarus";
		$country[22]['label'] = "Belgium";
		$country[23]['label'] = "Belize";
		$country[24]['label'] = "Benin";
		$country[25]['label'] = "Bermuda";
		$country[26]['label'] = "Bhutan";
		$country[27]['label'] = "Bolivia";
		$country[28]['label'] = "Bosnia and Herzegovina";
		$country[29]['label'] = "Botswana";
		$country[30]['label'] = "Brazil";
		$country[31]['label'] = "Brunei";
		$country[32]['label'] = "Bulgaria";
		$country[33]['label'] = "Burkina Faso";
		$country[34]['label'] = "Burundi";
		$country[35]['label'] = "Cambodia";
		$country[36]['label'] = "Cameroon";	
		$country[37]['label'] = "Cape Verde";
		$country[38]['label'] = "Cayman Islands";
		$country[39]['label'] = "Central African Republic";
		$country[40]['label'] = "Chad";
		$country[41]['label'] = "Chile";
		$country[42]['label'] = "China";
		$country[43]['label'] = "Colombia";
		$country[44]['label'] = "Comoros";
		$country[45]['label'] = "Congo";
		$country[46]['label'] = "Costa Rica";
		$country[47]['label'] = "Côte d'Ivoire";
		$country[48]['label'] = "Croatia";
		$country[49]['label'] = "Cuba";
		$country[50]['label'] = "Cyprus";
		$country[51]['label'] = "Czech Republic";
		$country[52]['label'] = "Denmark";
		$country[53]['label'] = "Djibouti";
		$country[54]['label'] = "Dominica";
		$country[55]['label'] = "Dominican Republic";
		$country[56]['label'] = "East Timor";
		$country[57]['label'] = "Ecuador";
		$country[58]['label'] = "Egypt";
		$country[59]['label'] = "El Salvador";
		$country[60]['label'] = "Equatorial Guinea";
		$country[61]['label'] = "Eritrea";
		$country[62]['label'] = "Estonia";
		$country[63]['label'] = "Ethiopia";
		$country[64]['label'] = "Fiji";
		$country[65]['label'] = "Finland";
		$country[66]['label'] = "Gabon";
		$country[67]['label'] = "Gambia";
		$country[68]['label'] = "Georgia";
		$country[69]['label'] = "Ghana";
		$country[70]['label'] = "Gibraltar";
		$country[71]['label'] = "Greece";
		$country[72]['label'] = "Grenada";
		$country[73]['label'] = "Guatemala";
		$country[74]['label'] = "Guernsey";
		$country[75]['label'] = "Guinea";
		$country[76]['label'] = "Guinea-Bissau";
		$country[77]['label'] = "Guyana";
		$country[78]['label'] = "Haiti";
		$country[79]['label'] = "Honduras";
		$country[80]['label'] = "Hong Kong";
		$country[81]['label'] = "Hungary";
		$country[82]['label'] = "Iceland";
		$country[83]['label'] = "India";
		$country[84]['label'] = "Indonesia";
		$country[85]['label'] = "Iran";
		$country[86]['label'] = "Iraq";
		$country[87]['label'] = "Ireland";
		$country[88]['label'] = "Israel";
		$country[89]['label'] = "Italy";
		$country[90]['label'] = "Jamaica";
		$country[91]['label'] = "Japan";
		$country[92]['label'] = "Jersey";
		$country[93]['label'] = "Jordan";
		$country[94]['label'] = "Kazakhstan";
		$country[95]['label'] = "Kenya";
		$country[96]['label'] = "Kiribati";
        $country[97]['label'] = "Kosovo";
		$country[98]['label'] = "Kuwait";
		$country[99]['label'] = "Kyrgyzstan";
		$country[100]['label'] = "Laos";
		$country[101]['label'] = "Latvia";
		$country[102]['label'] = "Lebanon";
		$country[103]['label'] = "Lesotho";
		$country[104]['label'] = "Liberia";
		$country[105]['label'] = "Libya";
		$country[106]['label'] = "Liechtenstein";
		$country[107]['label'] = "Lithuania";
		$country[108]['label'] = "Luxembourg";
		$country[109]['label'] = "Macedonia";
		$country[110]['label'] = "Madagascar";
		$country[111]['label'] = "Malawi";
		$country[112]['label'] = "Malaysia";
		$country[113]['label'] = "Maldives";
		$country[114]['label'] = "Mali";
		$country[115]['label'] = "Malta";
		$country[116]['label'] = "Marshall Islands";
		$country[117]['label'] = "Mauritania";
		$country[118]['label'] = "Mauritius";
		$country[119]['label'] = "Mexico";
		$country[120]['label'] = "Micronesia";
		$country[121]['label'] = "Moldova";
		$country[122]['label'] = "Monaco";
		$country[123]['label'] = "Mongolia";
		$country[124]['label'] = "Montenegro";
		$country[125]['label'] = "Morocco";
		$country[126]['label'] = "Mozambique";
		$country[127]['label'] = "Myanmar";
		$country[128]['label'] = "Namibia";
		$country[129]['label'] = "Nauru";
		$country[130]['label'] = "Nepal";
		$country[131]['label'] = "New Zealand";
		$country[132]['label'] = "Nicaragua";
		$country[133]['label'] = "Niger";
		$country[134]['label'] = "Nigeria";
		$country[135]['label'] = "Norway";
        $country[136]['label'] = "North Korea";
		$country[137]['label'] = "Oman";
		$country[138]['label'] = "Pakistan";
		$country[139]['label'] = "Palau";
		$country[140]['label'] = "Palestine";
		$country[141]['label'] = "Panama";
		$country[142]['label'] = "Papua New Guinea";
		$country[143]['label'] = "Paraguay";
		$country[144]['label'] = "Peru";
		$country[145]['label'] = "Philippines";
		$country[146]['label'] = "Poland";
		$country[147]['label'] = "Portugal";
		$country[148]['label'] = "Puerto Rico";
		$country[149]['label'] = "Qatar";
		$country[150]['label'] = "Romania";
		$country[151]['label'] = "Russia";
		$country[152]['label'] = "Rwanda";
		$country[153]['label'] = "Saint Kitts and Nevis";
		$country[154]['label'] = "Saint Lucia";
		$country[155]['label'] = "Saint Vincent and the Grenadines";
		$country[156]['label'] = "Samoa";
		$country[157]['label'] = "San Marino";
		$country[158]['label'] = "Sao Tome and Principe";
		$country[159]['label'] = "Saudi Arabia";
		$country[160]['label'] = "Senegal";
		$country[161]['label'] = "Serbia and Montenegro";
		$country[162]['label'] = "Seychelles";
		$country[163]['label'] = "Sierra Leone";
		$country[164]['label'] = "Singapore";
		$country[165]['label'] = "Slovakia";
		$country[166]['label'] = "Slovenia";
		$country[167]['label'] = "Solomon Islands";
		$country[168]['label'] = "Somalia";
		$country[169]['label'] = "South Africa";
        $country[170]['label'] = "South Korea";
		$country[171]['label'] = "Spain";
		$country[172]['label'] = "Sri Lanka";
		$country[173]['label'] = "Sudan";
		$country[174]['label'] = "Suriname";
		$country[175]['label'] = "Swaziland";
		$country[176]['label'] = "Sweden";
		$country[177]['label'] = "Switzerland";
		$country[178]['label'] = "Syria";
		$country[179]['label'] = "Taiwan";
		$country[180]['label'] = "Tajikistan";
		$country[181]['label'] = "Tanzania";
		$country[182]['label'] = "Thailand";
		$country[183]['label'] = "Togo";
		$country[184]['label'] = "Tonga";
		$country[185]['label'] = "Trinidad and Tobago";
		$country[186]['label'] = "Tunisia";
		$country[187]['label'] = "Turkey";
		$country[188]['label'] = "Turkmenistan";
		$country[189]['label'] = "Tuvalu";
		$country[190]['label'] = "Uganda";
		$country[191]['label'] = "Ukraine";
		$country[192]['label'] = "United Arab Emirates";
		$country[193]['label'] = "Uruguay";
		$country[194]['label'] = "Uzbekistan";
		$country[195]['label'] = "Vanuatu";
		$country[196]['label'] = "Vatican City";
		$country[197]['label'] = "Venezuela";
		$country[198]['label'] = "Vietnam";
		$country[199]['label'] = "Yemen";
		$country[200]['label'] = "Zambia";
		$country[201]['label'] = "Zimbabwe";

		$country[0]['value'] = "United States";
		$country[1]['value'] = "United Kingdom";
		$country[2]['value'] = "Canada";
		$country[3]['value'] = "Australia";
		$country[4]['value'] = "Netherlands";
		$country[5]['value'] = "France";
		$country[6]['value'] = "Germany";
		$country[7]['value'] = "-------";
		$country[8]['value'] = "Afghanistan";
		$country[9]['value'] = "Albania";
		$country[10]['value'] = "Algeria";
		$country[11]['value'] = "Andorra";
		$country[12]['value'] = "Antigua and Barbuda";
		$country[13]['value'] = "Argentina";
		$country[14]['value'] = "Armenia";
		$country[15]['value'] = "Austria";
		$country[16]['value'] = "Azerbaijan";
		$country[17]['value'] = "Bahamas";
		$country[18]['value'] = "Bahrain";
		$country[19]['value'] = "Bangladesh";
		$country[20]['value'] = "Barbados";
		$country[21]['value'] = "Belarus";
		$country[22]['value'] = "Belgium";
		$country[23]['value'] = "Belize";
		$country[24]['value'] = "Benin";
		$country[25]['value'] = "Bermuda";
		$country[26]['value'] = "Bhutan";
		$country[27]['value'] = "Bolivia";
		$country[28]['value'] = "Bosnia and Herzegovina";
		$country[29]['value'] = "Botswana";
		$country[30]['value'] = "Brazil";
		$country[31]['value'] = "Brunei";
		$country[32]['value'] = "Bulgaria";
		$country[33]['value'] = "Burkina Faso";
		$country[34]['value'] = "Burundi";
		$country[35]['value'] = "Cambodia";
		$country[36]['value'] = "Cameroon";	
		$country[37]['value'] = "Cape Verde";
		$country[38]['value'] = "Cayman Islands";
		$country[39]['value'] = "Central African Republic";
		$country[40]['value'] = "Chad";
		$country[41]['value'] = "Chile";
		$country[42]['value'] = "China";
		$country[43]['value'] = "Colombia";
		$country[44]['value'] = "Comoros";
		$country[45]['value'] = "Congo";
		$country[46]['value'] = "Costa Rica";
		$country[47]['value'] = "Côte d'Ivoire";
		$country[48]['value'] = "Croatia";
		$country[49]['value'] = "Cuba";
		$country[50]['value'] = "Cyprus";
		$country[51]['value'] = "Czech Republic";
		$country[52]['value'] = "Denmark";
		$country[53]['value'] = "Djibouti";
		$country[54]['value'] = "Dominica";
		$country[55]['value'] = "Dominican Republic";
		$country[56]['value'] = "East Timor";
		$country[57]['value'] = "Ecuador";
		$country[58]['value'] = "Egypt";
		$country[59]['value'] = "El Salvador";
		$country[60]['value'] = "Equatorial Guinea";
		$country[61]['value'] = "Eritrea";
		$country[62]['value'] = "Estonia";
		$country[63]['value'] = "Ethiopia";
		$country[64]['value'] = "Fiji";
		$country[65]['value'] = "Finland";
		$country[66]['value'] = "Gabon";
		$country[67]['value'] = "Gambia";
		$country[68]['value'] = "Georgia";
		$country[69]['value'] = "Ghana";
		$country[70]['value'] = "Gibraltar";
		$country[71]['value'] = "Greece";
		$country[72]['value'] = "Grenada";
		$country[73]['value'] = "Guatemala";
		$country[74]['value'] = "Guernsey";
		$country[75]['value'] = "Guinea";
		$country[76]['value'] = "Guinea-Bissau";
		$country[77]['value'] = "Guyana";
		$country[78]['value'] = "Haiti";
		$country[79]['value'] = "Honduras";
		$country[80]['value'] = "Hong Kong";
		$country[81]['value'] = "Hungary";
		$country[82]['value'] = "Iceland";
		$country[83]['value'] = "India";
		$country[84]['value'] = "Indonesia";
		$country[85]['value'] = "Iran";
		$country[86]['value'] = "Iraq";
		$country[87]['value'] = "Ireland";
		$country[88]['value'] = "Israel";
		$country[89]['value'] = "Italy";
		$country[90]['value'] = "Jamaica";
		$country[91]['value'] = "Japan";
		$country[92]['value'] = "Jersey";
		$country[93]['value'] = "Jordan";
		$country[94]['value'] = "Kazakhstan";
		$country[95]['value'] = "Kenya";
		$country[96]['value'] = "Kiribati";
        $country[97]['value'] = "Kosovo";
		$country[98]['value'] = "Kuwait";
		$country[99]['value'] = "Kyrgyzstan";
		$country[100]['value'] = "Laos";
		$country[101]['value'] = "Latvia";
		$country[102]['value'] = "Lebanon";
		$country[103]['value'] = "Lesotho";
		$country[104]['value'] = "Liberia";
		$country[105]['value'] = "Libya";
		$country[106]['value'] = "Liechtenstein";
		$country[107]['value'] = "Lithuania";
		$country[108]['value'] = "Luxembourg";
		$country[109]['value'] = "Macedonia";
		$country[110]['value'] = "Madagascar";
		$country[111]['value'] = "Malawi";
		$country[112]['value'] = "Malaysia";
		$country[113]['value'] = "Maldives";
		$country[114]['value'] = "Mali";
		$country[115]['value'] = "Malta";
		$country[116]['value'] = "Marshall Islands";
		$country[117]['value'] = "Mauritania";
		$country[118]['value'] = "Mauritius";
		$country[119]['value'] = "Mexico";
		$country[120]['value'] = "Micronesia";
		$country[121]['value'] = "Moldova";
		$country[122]['value'] = "Monaco";
		$country[123]['value'] = "Mongolia";
		$country[124]['value'] = "Montenegro";
		$country[125]['value'] = "Morocco";
		$country[126]['value'] = "Mozambique";
		$country[127]['value'] = "Myanmar";
		$country[128]['value'] = "Namibia";
		$country[129]['value'] = "Nauru";
		$country[130]['value'] = "Nepal";
		$country[131]['value'] = "New Zealand";
		$country[132]['value'] = "Nicaragua";
		$country[133]['value'] = "Niger";
		$country[134]['value'] = "Nigeria";
		$country[135]['value'] = "Norway";
        $country[136]['value'] = "North Korea";
		$country[137]['value'] = "Oman";
		$country[138]['value'] = "Pakistan";
		$country[139]['value'] = "Palau";
		$country[140]['value'] = "Palestine";
		$country[141]['value'] = "Panama";
		$country[142]['value'] = "Papua New Guinea";
		$country[143]['value'] = "Paraguay";
		$country[144]['value'] = "Peru";
		$country[145]['value'] = "Philippines";
		$country[146]['value'] = "Poland";
		$country[147]['value'] = "Portugal";
		$country[148]['value'] = "Puerto Rico";
		$country[149]['value'] = "Qatar";
		$country[150]['value'] = "Romania";
		$country[151]['value'] = "Russia";
		$country[152]['value'] = "Rwanda";
		$country[153]['value'] = "Saint Kitts and Nevis";
		$country[154]['value'] = "Saint Lucia";
		$country[155]['value'] = "Saint Vincent and the Grenadines";
		$country[156]['value'] = "Samoa";
		$country[157]['value'] = "San Marino";
		$country[158]['value'] = "Sao Tome and Principe";
		$country[159]['value'] = "Saudi Arabia";
		$country[160]['value'] = "Senegal";
		$country[161]['value'] = "Serbia and Montenegro";
		$country[162]['value'] = "Seychelles";
		$country[163]['value'] = "Sierra Leone";
		$country[164]['value'] = "Singapore";
		$country[165]['value'] = "Slovakia";
		$country[166]['value'] = "Slovenia";
		$country[167]['value'] = "Solomon Islands";
		$country[168]['value'] = "Somalia";
		$country[169]['value'] = "South Africa";
        $country[170]['value'] = "South Korea";
		$country[171]['value'] = "Spain";
		$country[172]['value'] = "Sri Lanka";
		$country[173]['value'] = "Sudan";
		$country[174]['value'] = "Suriname";
		$country[175]['value'] = "Swaziland";
		$country[176]['value'] = "Sweden";
		$country[177]['value'] = "Switzerland";
		$country[178]['value'] = "Syria";
		$country[179]['value'] = "Taiwan";
		$country[180]['value'] = "Tajikistan";
		$country[181]['value'] = "Tanzania";
		$country[182]['value'] = "Thailand";
		$country[183]['value'] = "Togo";
		$country[184]['value'] = "Tonga";
		$country[185]['value'] = "Trinidad and Tobago";
		$country[186]['value'] = "Tunisia";
		$country[187]['value'] = "Turkey";
		$country[188]['value'] = "Turkmenistan";
		$country[189]['value'] = "Tuvalu";
		$country[190]['value'] = "Uganda";
		$country[191]['value'] = "Ukraine";
		$country[192]['value'] = "United Arab Emirates";
		$country[193]['value'] = "Uruguay";
		$country[194]['value'] = "Uzbekistan";
		$country[195]['value'] = "Vanuatu";
		$country[196]['value'] = "Vatican City";
		$country[197]['value'] = "Venezuela";
		$country[198]['value'] = "Vietnam";
		$country[199]['value'] = "Yemen";
		$country[200]['value'] = "Zambia";
		$country[201]['value'] = "Zimbabwe";

		return $country;
	}
	
	//simple function to return an array of US states
	function mf_get_state_list(){
		$state_list[0]['label'] = 'Alabama';
		$state_list[1]['label'] = 'Alaska';
		$state_list[2]['label'] = 'Arizona';
		$state_list[3]['label'] = 'Arkansas';
		$state_list[4]['label'] = 'California';
		$state_list[5]['label'] = 'Colorado';
		$state_list[6]['label'] = 'Connecticut';
		$state_list[7]['label'] = 'Delaware';
		$state_list[8]['label'] = 'District of Columbia';
		$state_list[9]['label'] = 'Florida';
		$state_list[10]['label'] = 'Georgia';
		$state_list[11]['label'] = 'Hawaii';
		$state_list[12]['label'] = 'Idaho';
		$state_list[13]['label'] = 'Illinois';
		$state_list[14]['label'] = 'Indiana';
		$state_list[15]['label'] = 'Iowa';
		$state_list[16]['label'] = 'Kansas';
		$state_list[17]['label'] = 'Kentucky';
		$state_list[18]['label'] = 'Louisiana';
		$state_list[19]['label'] = 'Maine';
		$state_list[20]['label'] = 'Maryland';
		$state_list[21]['label'] = 'Massachusetts';
		$state_list[22]['label'] = 'Michigan';
		$state_list[23]['label'] = 'Minnesota';
		$state_list[24]['label'] = 'Mississippi';
		$state_list[25]['label'] = 'Missouri';
		$state_list[26]['label'] = 'Montana';
		$state_list[27]['label'] = 'Nebraska';
		$state_list[28]['label'] = 'Nevada';
		$state_list[29]['label'] = 'New Hampshire';
		$state_list[30]['label'] = 'New Jersey';
		$state_list[31]['label'] = 'New Mexico';
		$state_list[32]['label'] = 'New York';
		$state_list[33]['label'] = 'North Carolina';
		$state_list[34]['label'] = 'North Dakota';
		$state_list[35]['label'] = 'Ohio';
		$state_list[36]['label'] = 'Oklahoma';
		$state_list[37]['label'] = 'Oregon';
		$state_list[38]['label'] = 'Pennsylvania';
		$state_list[39]['label'] = 'Rhode Island';
		$state_list[40]['label'] = 'South Carolina';
		$state_list[41]['label'] = 'South Dakota';
		$state_list[42]['label'] = 'Tennessee';
		$state_list[43]['label'] = 'Texas';
		$state_list[44]['label'] = 'Utah';
		$state_list[45]['label'] = 'Vermont';
		$state_list[46]['label'] = 'Virginia';
		$state_list[47]['label'] = 'Washington';
		$state_list[48]['label'] = 'West Virginia';
		$state_list[49]['label'] = 'Wisconsin';
		$state_list[50]['label'] = 'Wyoming';

		$state_list[0]['value'] = 'Alabama';
		$state_list[1]['value'] = 'Alaska';
		$state_list[2]['value'] = 'Arizona';
		$state_list[3]['value'] = 'Arkansas';
		$state_list[4]['value'] = 'California';
		$state_list[5]['value'] = 'Colorado';
		$state_list[6]['value'] = 'Connecticut';
		$state_list[7]['value'] = 'Delaware';
		$state_list[8]['value'] = 'District of Columbia';
		$state_list[9]['value'] = 'Florida';
		$state_list[10]['value'] = 'Georgia';
		$state_list[11]['value'] = 'Hawaii';
		$state_list[12]['value'] = 'Idaho';
		$state_list[13]['value'] = 'Illinois';
		$state_list[14]['value'] = 'Indiana';
		$state_list[15]['value'] = 'Iowa';
		$state_list[16]['value'] = 'Kansas';
		$state_list[17]['value'] = 'Kentucky';
		$state_list[18]['value'] = 'Louisiana';
		$state_list[19]['value'] = 'Maine';
		$state_list[20]['value'] = 'Maryland';
		$state_list[21]['value'] = 'Massachusetts';
		$state_list[22]['value'] = 'Michigan';
		$state_list[23]['value'] = 'Minnesota';
		$state_list[24]['value'] = 'Mississippi';
		$state_list[25]['value'] = 'Missouri';
		$state_list[26]['value'] = 'Montana';
		$state_list[27]['value'] = 'Nebraska';
		$state_list[28]['value'] = 'Nevada';
		$state_list[29]['value'] = 'New Hampshire';
		$state_list[30]['value'] = 'New Jersey';
		$state_list[31]['value'] = 'New Mexico';
		$state_list[32]['value'] = 'New York';
		$state_list[33]['value'] = 'North Carolina';
		$state_list[34]['value'] = 'North Dakota';
		$state_list[35]['value'] = 'Ohio';
		$state_list[36]['value'] = 'Oklahoma';
		$state_list[37]['value'] = 'Oregon';
		$state_list[38]['value'] = 'Pennsylvania';
		$state_list[39]['value'] = 'Rhode Island';
		$state_list[40]['value'] = 'South Carolina';
		$state_list[41]['value'] = 'South Dakota';
		$state_list[42]['value'] = 'Tennessee';
		$state_list[43]['value'] = 'Texas';
		$state_list[44]['value'] = 'Utah';
		$state_list[45]['value'] = 'Vermont';
		$state_list[46]['value'] = 'Virginia';
		$state_list[47]['value'] = 'Washington';
		$state_list[48]['value'] = 'West Virginia';
		$state_list[49]['value'] = 'Wisconsin';
		$state_list[50]['value'] = 'Wyoming';

		return $state_list;
	}
	
	//Address
	function mf_display_address($element){
		
		$country = mf_get_country_list();
		$state_list = mf_get_state_list();
		
		global $mf_lang;

		$label_address_state = $mf_lang['address_state'];
		$label_address_zip	 = $mf_lang['address_zip'];
		
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_readonly = '';
		$attr_aria = array();
		$guidelines = '';
		$default_value_1 = '';
		$default_value_2 = '';
		$default_value_3 = '';
		$default_value_4 = '';
		$default_value_5 = '';
		$default_value_6 = '';
		$country_disabled_attr = '';
		$country_hidden_field_markup = '';
		
		$el_class = array();
		
		$el_class[] = 'address';
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}
		
		//check for read-only attribute
		if(!empty($element->is_readonly)){
			$attr_readonly = 'readonly="readonly"';
		}

		//check for guidelines
		if(!empty($element->guidelines)){
			$guidelines = "<p class=\"guidelines\" id=\"guide_{$element->id}\"><small>{$element->guidelines}</small></p>";
		}
		
		if(!empty($element->default_value)){
			$default_value_6 = $element->default_value;

			//if the default value is UK, adjust some of the field labels
			if($element->default_value == 'United Kingdom'){
				$label_address_state  = 'County';
				$label_address_zip	  = 'Postcode';
			}
		}

		//check for GET parameter to populate default value
		if(isset($_GET['element_'.$element->id.'_1'])){
			$default_value_1 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_1']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_2'])){
			$default_value_2 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_2']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_3'])){
			$default_value_3 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_3']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_4'])){
			$default_value_4 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_4']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_5'])){
			$default_value_5 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_5']),ENT_QUOTES);
		}
		if(isset($_GET['element_'.$element->id.'_6'])){
			$default_value_6 = htmlspecialchars(mf_sanitize($_GET['element_'.$element->id.'_6']),ENT_QUOTES);
		}
		
		//check for populated values, if exist override the default value
		if(!empty($element->populated_value['element_'.$element->id.'_1']['default_value']) || 
		   !empty($element->populated_value['element_'.$element->id.'_2']['default_value']) ||
		   !empty($element->populated_value['element_'.$element->id.'_3']['default_value']) ||
		   !empty($element->populated_value['element_'.$element->id.'_4']['default_value']) ||
		   !empty($element->populated_value['element_'.$element->id.'_5']['default_value']) ||
		   !empty($element->populated_value['element_'.$element->id.'_6']['default_value'])
		){
			$default_value_1 = '';
			$default_value_2 = '';
			$default_value_3 = '';
			$default_value_4 = '';
			$default_value_5 = '';
			$default_value_1 = $element->populated_value['element_'.$element->id.'_1']['default_value'];
			$default_value_2 = $element->populated_value['element_'.$element->id.'_2']['default_value'];
			$default_value_3 = $element->populated_value['element_'.$element->id.'_3']['default_value'];
			$default_value_4 = $element->populated_value['element_'.$element->id.'_4']['default_value'];
			$default_value_5 = $element->populated_value['element_'.$element->id.'_5']['default_value'];
			$default_value_6 = $element->populated_value['element_'.$element->id.'_6']['default_value'];
		}
		
		//create country markup, if no default value, provide a blank option
		if(!empty($element->address_us_only)){
			$default_value_6 = 'United States';
			$default_value_4 = $element->address_default_state;
		}
		
		if(empty($default_value_6)){
			$country_markup = '<option value="" selected="selected"></option>'."\n";
		}else{
			$country_markup = '';
		}
		

		foreach ($country as $data){
			if(!empty($data['value']) && $data['value'] == $default_value_6){
				$selected = 'selected="selected"';
				$selected_country = $data['value'];
			}else{
				$selected = '';
			}
			
			$country_markup .= "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>\n";
		}

		//disable country dropdown and replace it with hidden fields when the property is read-only
		if(!empty($element->is_readonly)){
			$country_disabled_attr = 'disabled="disabled"';
			$country_hidden_field_markup = "<input type=\"hidden\" name=\"element_{$element->id}_6\" value=\"{$selected_country}\" />";
		}

		//if this address field is restricted to US only
		if(empty($element->is_design_mode) && !empty($element->address_us_only)){
			$country_markup = '<option selected="selected" value="United States">United States</option>';
		}
		
		//decide which state markup being used
		if(empty($element->address_us_only)){
			//display simple input for the state
			$state_markup = "<input id=\"element_{$element->id}_4\" name=\"element_{$element->id}_4\" class=\"element text large\" {$attr_readonly}  value=\"{$default_value_4}\" type=\"text\" />";
		}else{

			if(!empty($element->is_readonly)){
				$state_disabled_attr   = 'disabled="disabled"';
			}

			//display us state dropdown
			$state_markup = "<select class=\"element select large\" id=\"element_{$element->id}_4\" name=\"element_{$element->id}_4\" {$state_disabled_attr}>";
			$state_markup .= '<option value="" selected="selected">Select a State</option>'."\n";
			
			foreach ($state_list as $data){
				if($data['value'] == $default_value_4){
					$selected = 'selected="selected"';
					$selected_state = $data['value'];
				}else{
					$selected = '';
				}
				
				$state_markup .= "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>\n";
			}
			
			$state_markup .= "</select>";
			
			if(!empty($element->is_readonly)){
				$state_hidden_field_markup = "<input type=\"hidden\" name=\"element_{$element->id}_4\" value=\"{$selected_state}\" />";
				$state_markup .= $state_hidden_field_markup;
			}
		}

		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}

		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}

		//build the complete address field markup based on visibility settings
		
		$fieldset_street_display = '';
		$fieldset_street2_display = '';
		$fieldset_city_display = '';
		$fieldset_state_display = '';
		$fieldset_postal_display = '';
		$fieldset_country_display = '';

		$label_address_street  = $mf_lang['address_street'];
		$label_address_street2 = $mf_lang['address_street2'];
		$label_address_city    = $mf_lang['address_city'];
		$label_address_country = $mf_lang['address_country'];

		if(!empty($element->address_subfields_labels)){
			$subfields_labels_obj = json_decode($element->address_subfields_labels);
			
			if(!empty($subfields_labels_obj->street)){
				$label_address_street = $subfields_labels_obj->street; 
			}
			if(!empty($subfields_labels_obj->street2)){
				$label_address_street2 = $subfields_labels_obj->street2; 
			}
			if(!empty($subfields_labels_obj->city)){
				$label_address_city = $subfields_labels_obj->city; 
			}
			if(!empty($subfields_labels_obj->country)){
				$label_address_country = $subfields_labels_obj->country; 
			}
			if(!empty($subfields_labels_obj->state)){
				$label_address_state = $subfields_labels_obj->state; 
			}
			if(!empty($subfields_labels_obj->postal)){
				$label_address_zip = $subfields_labels_obj->postal; 
			}
		}

		if(!empty($element->address_subfields_visibility) && $element->is_design_mode){
			$subfields_visibility_obj = json_decode($element->address_subfields_visibility);

			//on edit mode, simply hide fieldsets, don't remove it as in the live forms
			if(empty($subfields_visibility_obj->street)){
				$fieldset_street_display = 'style="display: none"'; 
			}
			if(empty($subfields_visibility_obj->street2)){
				$fieldset_street2_display = 'style="display: none"'; 
			}
			if(empty($subfields_visibility_obj->city)){
				$fieldset_city_display = 'style="display: none"'; 
			}
			if(empty($subfields_visibility_obj->state)){
				$fieldset_state_display = 'style="display: none"'; 
			}
			if(empty($subfields_visibility_obj->postal)){
				$fieldset_postal_display = 'style="display: none"'; 
			}
			if(empty($subfields_visibility_obj->country)){
				$fieldset_country_display = 'style="display: none"'; 
			}
		}

		$fieldset_street_markup = <<<EOT
			<span id="li_{$element->id}_span_1" {$fieldset_street_display}>
				<label for="element_{$element->id}_1">{$label_address_street}</label>
				<input id="element_{$element->id}_1" name="element_{$element->id}_1" class="element text large" {$attr_readonly} {$attr_aria} value="{$default_value_1}" type="text" />
			</span>
EOT;

		$fieldset_street2_markup = <<<EOT
			<span id="li_{$element->id}_span_2" {$fieldset_street2_display}>
				<label for="element_{$element->id}_2">{$label_address_street2}</label>
				<input id="element_{$element->id}_2" name="element_{$element->id}_2" class="element text large" {$attr_readonly} value="{$default_value_2}" type="text" />
			</span>
EOT;
		
		$fieldset_city_markup = <<<EOT
			<span id="li_{$element->id}_span_3" class="left state_list" {$fieldset_city_display}>
				<label for="element_{$element->id}_3">{$label_address_city}</label>
				<input id="element_{$element->id}_3" name="element_{$element->id}_3" class="element text large" {$attr_readonly} value="{$default_value_3}" type="text" />
			</span>
EOT;

		$fieldset_state_markup = <<<EOT
			<span id="li_{$element->id}_span_4" class="right state_list" {$fieldset_state_display}>
				<label for="element_{$element->id}_4">{$label_address_state}</label>
				{$state_markup}
			</span>
EOT;

		$fieldset_postal_markup = <<<EOT
			<span id="li_{$element->id}_span_5" class="left" {$fieldset_postal_display}>
				<label for="element_{$element->id}_5">{$label_address_zip}</label>
				<input id="element_{$element->id}_5" name="element_{$element->id}_5" class="element text large" {$attr_readonly} maxlength="15" value="{$default_value_5}" type="text" />
			</span>
EOT;

		$fieldset_country_markup = <<<EOT
			<span id="li_{$element->id}_span_6" class="right" {$fieldset_country_display}>
				<label for="element_{$element->id}_6">{$label_address_country}</label>
				<select class="element select large" id="element_{$element->id}_6" name="element_{$element->id}_6" {$country_disabled_attr}> 
				{$country_markup}	
				</select>
		    </span>
EOT;

		//if visibility settings not available, display all fields by default		
		if(!empty($element->address_subfields_visibility) && !$element->is_design_mode){
			$subfields_visibility_obj = json_decode($element->address_subfields_visibility);

			if(empty($subfields_visibility_obj->street)){
				$fieldset_street_markup = ''; 
			}
			if(empty($subfields_visibility_obj->street2)){
				$fieldset_street2_markup = '';
			}
			if(empty($subfields_visibility_obj->city)){
				$fieldset_city_markup = '';
			}
			if(empty($subfields_visibility_obj->state)){
				$fieldset_state_markup = '';
			}
			if(empty($subfields_visibility_obj->postal)){
				$fieldset_postal_markup = '';
			}
			if(empty($subfields_visibility_obj->country)){
				$fieldset_country_markup = '';
			}
		}

		

$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
		<fieldset>
		<legend style="color: transparent;height: 0px;font-size: 0px;">{$element->title}</legend>
		<span class="description">{$element->title} {$span_required}</span>
		
		<div>
			{$fieldset_street_markup}
			{$fieldset_street2_markup}
			{$fieldset_city_markup}
			{$fieldset_state_markup}
			{$fieldset_postal_markup}
			{$fieldset_country_markup}
		    {$country_hidden_field_markup}
	    </div></fieldset>{$guidelines} {$error_message}
		</li>
EOT;
		
	
		return $element_markup;
	}
	
	
	//Captcha
	function mf_display_captcha($element){
		
		$mf_settings = mf_get_settings($element->dbh);

		if(!empty($element->error_message)){
			$error_code = $element->error_message;
		}else{
			$error_code = '';
		}
					
		//check for error
		$error_class = '';
		$error_message = '';
		$span_required = '';
		$guidelines = '';
		global $mf_lang;		
		
		if(!empty($element->is_error)){
			if($element->error_message == 'el-required'){
				$element->error_message = $mf_lang['captcha_required'];
				$error_code = '';	
			}else if($element->error_message == 'el-text-required'){
				$element->error_message = $mf_lang['val_required'];
				$error_code = '';	
			}elseif ($element->error_message == 'incorrect-captcha-sol'){
				$element->error_message = $mf_lang['captcha_mismatch'];
			}elseif ($element->error_message == 'incorrect-text-captcha-sol'){
				$element->error_message = $mf_lang['captcha_text_mismatch'];
			}else{
				$element->error_message = "{$mf_lang['captcha_error']} ({$element->error_message})";
			}
			
			$error_class = 'class="error"';
			$error_message = "<p class=\"error\">{$element->error_message}</p>";
		}

		
		if(!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != 'off')){
			$use_ssl = true;
		}else if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'){
			$use_ssl = true;
		}else if (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && $_SERVER['HTTP_FRONT_END_HTTPS'] == 'on'){
			$use_ssl = true;
		}else{
			$use_ssl = false;
		}
		
		if($element->captcha_type == 'i' || $element->captcha_type == 't'){
			//if this is internal captcha (either text or image)
			//generate the encrypted code, so that we don't need to use session

			//get captcha encryption key from ap_settings
			//if the values are empty generate the key
			if(empty($mf_settings['captcha_public_key']) || empty($mf_settings['captcha_private_key'])){
				$encryption_keypair = \Sodium\crypto_box_keypair();

			    $captcha_public_key = \Sodium\crypto_box_publickey($encryption_keypair);
			    $captcha_private_key = \Sodium\crypto_box_secretkey($encryption_keypair);

			    $captcha_settings = array();
			    $captcha_settings['captcha_public_key']  = base64_encode($captcha_public_key);
			    $captcha_settings['captcha_private_key'] = base64_encode($captcha_private_key);

			    mf_ap_settings_update($captcha_settings,$element->dbh);
			}else{
				$captcha_public_key  = base64_decode($mf_settings['captcha_public_key']);
			}

		}
		
		if($element->captcha_type == 'i'){ //if this is internal captcha type
		
			$machform_path = '';
			if(!empty($element->machform_path)){
				$machform_path = $element->machform_path;
			}
			
			$timestamp = time(); //use this as paramater for captcha.php, to prevent caching
			
			//generate the characters and encrypt it
			$image_captcha_challenge = '';
	        for($i=0; $i<5; $i++) {
	            $image_captcha_challenge .= chr(rand(65, 90));
	        }

	        $image_captcha_challenge_crypted = \Sodium\crypto_box_seal($image_captcha_challenge,$captcha_public_key);
			$image_captcha_challenge_crypted = base64_encode($image_captcha_challenge_crypted); //since the encrypted data is binary string, we need to additionally encode to base64
			$image_captcha_challenge_crypted_url_encoded = urlencode($image_captcha_challenge_crypted);

			$element->title = $mf_lang['captcha_simple_image_title'];
$captcha_html = <<<EOT
<img id="captcha_image" src="{$machform_path}captcha.php?t={$timestamp}&c={$image_captcha_challenge_crypted_url_encoded}" width="200" height="60" alt="Please refresh your browser to see this image." /><br />
<input id="captcha_response_field" name="captcha_response_field" class="element text small" type="text" />
<input id="captcha_response_challenge" name="captcha_response_challenge" type="hidden" value="{$image_captcha_challenge_crypted}" /><div id="dummy_captcha_internal"></div>
EOT;

			$element_markup = <<<EOT
		<li id="li_captcha" {$error_class}> 
		<label class="description" for="captcha_response_field">{$element->title} {$span_required}</label>
		<div>
			{$captcha_html}	
		</div>	 
		{$guidelines} {$error_message}
		</li>
EOT;
	 		
		}else if($element->captcha_type == 'n' || $element->captcha_type == 'r'){ 
			//if this is reCAPTCHA V2 (No CAPTCHA)
			//also enforce recaptcha v1 'r' (deprecated) to use recaptcha v2

			if(empty($element->recaptcha_site_key) || empty($element->recaptcha_secret_key)){
				$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
				$captcha_html = "<b>Error!</b> You have enabled reCAPTCHA V2 but your API keys are missing. <br /><br />To use reCAPTCHA V2 you must get API keys from <a href=\"https://www.google.com/recaptcha/admin\">https://www.google.com/recaptcha/admin</a><br /><br />After getting the API keys, save them into your Settings page.";
				$error_class = 'class="error"';
			}else{
				$captcha_html = '<div class="g-recaptcha" data-sitekey="'.$element->recaptcha_site_key.'"></div>';
				$recaptcha_post_message = <<<EOT
<script type="text/javascript">
    $(function(){
    	$.postMessage({mf_iframe_height: $('body').outerHeight(true) + 130}, '*', parent );
    });
</script>
EOT;
				//manually add 130px padding for recaptcha, since google is building the captcha after the dom is loaded
				$captcha_html .= "\n".$recaptcha_post_message; 
			}

			$element_markup = <<<EOT
		<li id="li_captcha" {$error_class}> 
		<label class="description" for="captcha_response_field">{$element->title} {$span_required}</label>
		<div>
			{$captcha_html}	
		</div>	 
		{$guidelines} {$error_message}
		</li>
EOT;
		}else if($element->captcha_type == 't'){ //if this is simple text captcha
			
			$element->title = $mf_lang['captcha_simple_text_title'];

			$text_captcha = mf_get_text_captcha();

			$text_captcha_crypted = \Sodium\crypto_box_seal($text_captcha['answer'],$captcha_public_key);
			$text_captcha_crypted = base64_encode($text_captcha_crypted); //since the encrypted data is binary string, we need to additionally encode to base64
			
			$text_captcha_question = htmlspecialchars($text_captcha['question'],ENT_QUOTES);

			$element_markup = <<<EOT
		<li id="li_captcha" {$error_class}>
		<label class="description" for="captcha_response_field">
			{$element->title} <br/>
			{$text_captcha_question}
		</label>
		<div>
			<input id="captcha_response_field" name="captcha_response_field" class="element text small" type="text" />
			<input id="captcha_response_challenge" name="captcha_response_challenge" type="hidden" value="{$text_captcha_crypted}" />
		</div>	 
		{$guidelines} {$error_message}
		</li>
EOT;
		}
		
		
		return $element_markup;
	}
	
	//Matrix Table
	function mf_display_matrix($element){
		
		//check for error
		$li_class = '';
		$li_style = '';
		$error_message = '';
		$span_required = '';
		$attr_aria = array();
		$el_class = array();
		
		$el_class[] = "matrix";
		
		if($element->is_private == 1){
			$el_class[] = 'private';
		}

		if($element->is_private == 2 && !$element->is_design_mode && !$element->is_edit_entry){
			$li_style = 'style="display: none"';
		}

		if(!empty($element->css_class)){
			$el_class[] = trim($element->css_class);
		}
		
		if(!empty($element->is_error)){
			$el_class[] = 'error';
			if($element->error_message != 'error_no_display'){
				$error_message = "<p id=\"error_{$element->id}\" class=\"error\">{$element->error_message}</p>";
				$attr_aria[] = "aria-invalid=\"true\" aria-describedby=\"error_{$element->id}\"";
			}
		}
		
		//check for required
		if(!empty($element->is_required)){
			$span_required = "<span id=\"required_{$element->id}\" aria-hidden=\"true\" class=\"required\">*</span>";
			$attr_aria[] = 'aria-required="true"';
		}
		
		//check matrix field type
		if($element->matrix_allow_multiselect){
			$input_type = 'checkbox';
		}else{
			$input_type = 'radio';
		}
		
		//build attr_aria
		if(!empty($attr_aria)){
			$attr_aria = implode(' ',$attr_aria);
		}else{
			$attr_aria = '';
		}
		
		//calculate the table columns width
		$total_answer = count($element->options) + 1;
		$initial_width = 100 / $total_answer;
		$first_col_width = 2 * $initial_width;
		$first_col_width = round($first_col_width);
		$other_col_width = (100 - $first_col_width) / ($total_answer - 1);
		$other_col_width = round($other_col_width);

		//build th markup and first row markup		
		$th_markup = '';
		$first_row_td = '';
		$attr_aria_matrix = $attr_aria;
		$option_titles = array();
		$i=0;
		foreach($element->options as $option){
			$option_titles[$i] = $option->option;
			
			if($input_type == 'checkbox'){
				$option_id_var = '_'.$option->id;

				if(!empty($element->populated_value['element_'.$element->id.'_'.$option->id]['default_value']) && ($element->populated_value['element_'.$element->id.'_'.$option->id]['default_value'] == $option->id)){
					$checked_markup = 'checked="checked"';
				}else{
					$checked_markup = '';
				}
			}else{
				$option_id_var = '';
				
				if(!empty($element->populated_value['element_'.$element->id]['default_value']) && ($element->populated_value['element_'.$element->id]['default_value'] == $option->id)){
					$checked_markup = 'checked="checked"';
				}else{
					$checked_markup = '';
				}
			}
			
			if($i > 0){
				$attr_aria_matrix = '';
			}

			$th_markup 	  .= "<th id=\"mc_{$element->id}_{$option->id}\" style=\"width: {$other_col_width}%\" scope=\"col\">{$option->option}</th>\n";
			$first_row_td .= "<td><fieldset><legend style=\"display: none\">{$element->title}</legend><label style=\"display: none\" for=\"element_{$element->id}_{$option->id}\">{$option_titles[$i]}</label><input id=\"element_{$element->id}_{$option->id}\" name=\"element_{$element->id}{$option_id_var}\" type=\"{$input_type}\" value=\"{$option->id}\" {$attr_aria_matrix} {$checked_markup} /></fieldset></td>\n";
			$i++;
		}
		
		//build other rows markup
		$tr_markup = '';
		$show_alt = false;
		
		if(!empty($element->matrix_children)){
			foreach ($element->matrix_children as $matrix_item){
			
				$children_option_id = array();
				$children_option_id = explode(',',$matrix_item['children_option_id']);
				
				$td_markup = "<td class=\"first_col\">{$matrix_item['title']}</td>";
				$i=0;
				$attr_aria_matrix = $attr_aria;
				
				foreach ($children_option_id as $option_id){
					
					if($input_type == 'checkbox'){
						$option_id_var = '_'.$option_id;

						if(!empty($element->populated_value['element_'.$matrix_item['id'].'_'.$option_id]['default_value']) && ($element->populated_value['element_'.$matrix_item['id'].'_'.$option_id]['default_value'] == $option_id)){
							$checked_markup = 'checked="checked"';
						}else{
							$checked_markup = '';
						}
					}else{
						$option_id_var = '';
						
						if(!empty($element->populated_value['element_'.$matrix_item['id']]['default_value']) && ($element->populated_value['element_'.$matrix_item['id']]['default_value'] == $option_id)){
							$checked_markup = 'checked="checked"';
						}else{
							$checked_markup = '';
						}
					}
					
					if($i > 0){
						$attr_aria_matrix = '';
					}

					$td_markup .= "<td><fieldset><legend style=\"display: none\">{$matrix_item['title']}</legend><label style=\"display: none\" for=\"element_{$matrix_item['id']}_{$option_id}\">{$option_titles[$i]}</label><input id=\"element_{$matrix_item['id']}_{$option_id}\" name=\"element_{$matrix_item['id']}{$option_id_var}\" type=\"{$input_type}\" {$attr_aria_matrix} value=\"{$option_id}\" {$checked_markup} /></fieldset></td>\n";
					$i++;
				}
				
				if($show_alt){
					$row_style = ' class="alt" ';
					$show_alt = false;
				}else{
					$row_style = '';
					$show_alt = true;
				}

				$tr_markup .= "<tr {$row_style} id=\"mr_{$matrix_item['id']}\">".$td_markup."</tr>";
			}
		}
		
		//build the li class
		if(!empty($el_class)){
			foreach ($el_class as $value){
				$li_class .= $value.' ';
			}
			
			$li_class = 'class="'.rtrim($li_class).'"';
		}
		
$element_markup = <<<EOT
		<li id="li_{$element->id}" {$li_style} {$li_class}>
			<table>
				<caption>
					{$element->guidelines} {$span_required}
				</caption>
			    <thead>
			    	<tr>
			        	<th style="width: {$first_col_width}%" scope="col"><span style="display: none">{$element->guidelines}</span></th>
			            {$th_markup}
			        </tr>
			    </thead>
			    <tbody>
			    	<tr class="alt" id="mr_{$element->id}">
			        	<td class="first_col">{$element->title}</td>
			            {$first_row_td}
			        </tr>
			        {$tr_markup}
			    </tbody>
			</table>
		{$error_message}
		</li>
EOT;
		
		return $element_markup;
	}
	
	
	//Main function to display a form
	//There are few mode when displaying a form
	//1. New blank form (form populated with default values)
	//2. New form with error (displayed when 1 submitted and having error, form populated with user inputs)
	//3. Edit form (form populated with data from db)
	//4. Edit form with error (displayed when #3 submitted and having error)

	function mf_display_form($dbh,$form_id,$form_params=array()){	
		
		$mf_edit_tag = '';
		$mfsid_tag = '';
		
		global $mf_lang;

		$form_id = (int) $form_id;
		
		if(empty($_COOKIE['mf_has_cookie'])){
			$mfsid = session_id();
			$mfsid_tag = '<input type="hidden" id="mfsid" name="mfsid" value="'.$mfsid.'" />';
		}

		//reset sessions if there is 'reset' parameter
		if(!empty($_GET['reset'])){
			//remove form history from session
			$_SESSION['mf_form_loaded'][$form_id] = array();
			unset($_SESSION['mf_form_loaded'][$form_id]);

			$_SESSION['mf_form_completed'][$form_id] = array();
			unset($_SESSION['mf_form_completed'][$form_id]);
			
			//remove form access session
			$_SESSION['mf_form_access'][$form_id] = array();
			unset($_SESSION['mf_form_access'][$form_id]);
			
			//remove pages history
			$_SESSION['mf_pages_history'][$form_id] = array();
			unset($_SESSION['mf_pages_history'][$form_id]);

			//unset the form resume session, if any
			$_SESSION['mf_form_resume_loaded'][$form_id] = false;
			unset($_SESSION['mf_form_resume_loaded'][$form_id]);

			//unset the form edit session, if any
			$_SESSION['mf_form_edit_loaded'][$form_id] = false;
			unset($_SESSION['mf_form_edit_loaded'][$form_id]);

			$_SESSION['mf_form_edit_key'][$form_id] = array();
			unset($_SESSION['mf_form_edit_key'][$form_id]);

			//delete leftover record within ap_form_xxx_review table
			$session_id = session_id();
			$query = "DELETE FROM `".MF_TABLE_PREFIX."form_{$form_id}_review` WHERE session_id=?";
			$params = array($session_id);
	
			$sth = $dbh->prepare($query);
			try{
				$sth->execute($params);
			}catch(PDOException $e) {
				//do nothing on error, in case of non existent review table
				//this is applicable for single page form
			}
		}

		defined('MF_STORE_FILES_AS_BLOB') or define('MF_STORE_FILES_AS_BLOB',false);
		
		//parameters mapping
		if(isset($form_params['page_number'])){
			$page_number = (int) $form_params['page_number'];
		}else{
			$page_number = 1;
		}

		if(isset($form_params['populated_values'])){
			$populated_values = $form_params['populated_values'];
		}else{
			$populated_values = array();
		}

		if(isset($form_params['error_elements'])){
			$error_elements = $form_params['error_elements'];
		}else{
			$error_elements = array();
		}
		
		if(isset($form_params['custom_error'])){
			$custom_error = $form_params['custom_error'];
		}else{
			$custom_error = '';
		}
		
		if(isset($form_params['edit_id'])){
			$edit_id = (int) $form_params['edit_id'];
		}else{
			$edit_id = 0;
		}
		
		if(isset($form_params['integration_method'])){ //valid values are empty string, 'iframe' or 'php'
			$integration_method = $form_params['integration_method'];
		}else{
			$integration_method = '';
		}

		if(!empty($form_params['machform_path'])){
			$machform_path = $form_params['machform_path'];
		}else{
			$machform_path = '';
		}

		if(!empty($form_params['machform_data_path'])){
			$machform_data_path = $form_params['machform_data_path'];
		}else{
			$machform_data_path = '';
		}

		
		$mf_settings 	= mf_get_settings($dbh);
		$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);

		//check for scheduled tasks each time form being displayed
		mf_run_cron_jobs($dbh);
		
		//if there is custom error, don't show other errors
		if(!empty($custom_error)){
			$error_elements = array();
		}
		
		//get form properties data
		$query 	= "SELECT 
						 form_name,
						 form_name_hide,
						 form_description,
						 form_redirect,
						 form_success_message,
						 form_password,
						 form_unique_ip,
						 form_frame_height,
						 form_has_css,
						 form_active,
						 form_disabled_message,
						 form_captcha,
						 form_captcha_type,
						 form_review,
						 form_label_alignment,
						 form_language,
						 form_page_total,
						 form_lastpage_title,
						 form_submit_primary_text,
						 form_submit_secondary_text,
						 form_submit_primary_img,
						 form_submit_secondary_img,
						 form_submit_use_image,
						 form_pagination_type,
						 form_review_primary_text,
						 form_review_secondary_text,
						 form_review_primary_img,
						 form_review_secondary_img,
						 form_review_use_image,
						 form_review_title,
						 form_review_description,
						 form_resume_enable,
						 form_theme_id,
						 payment_show_total,
						 payment_total_location,
						 payment_enable_merchant,
						 payment_currency,
						 payment_price_type,
						 payment_price_amount,
						 form_limit_enable,
						 form_limit,
						 form_schedule_enable,
						 form_schedule_start_date,
						 form_schedule_end_date,
						 form_schedule_start_hour,
						 form_schedule_end_hour,
						 logic_field_enable,
						 logic_page_enable,
						 payment_enable_discount,
						 payment_discount_type,
						 payment_discount_amount,
						 payment_discount_element_id,
						 payment_enable_tax,
					 	 payment_tax_rate,
					 	 form_custom_script_enable,
					 	 form_custom_script_url,
					 	 form_entry_edit_enable,
					 	 form_entry_edit_auto_disable,
					 	 form_entry_edit_auto_disable_period,
					 	 form_entry_edit_auto_disable_unit  
				     FROM 
				     	 ".MF_TABLE_PREFIX."forms 
				    WHERE 
				    	 form_id = ?";
		$params = array($form_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		//check for non-existent or currently drafted forms or inactive forms
		if($row === false){
			die('This is not valid form URL.');
		}else{
			$form_active = (int) $row['form_active'];
		
			if($form_active !== 0 && $form_active !== 1){
				die('This is not valid form URL.');
			}
		}

		$form = new stdClass();
		
		$form->id 				= $form_id;
		$form->name 			= $row['form_name'];
		$form->name_hide 		= (int) $row['form_name_hide'];
		$form->description 		= $row['form_description'];
		$form->redirect 		= $row['form_redirect'];
		$form->success_message  = $row['form_success_message'];
		$form->password 		= $row['form_password'];
		$form->frame_height 	= $row['form_frame_height'];
		$form->unique_ip 		= $row['form_unique_ip'];
		$form->has_css 			= $row['form_has_css'];
		$form->active 			= $row['form_active'];
		$form->disabled_message = $row['form_disabled_message'];
		$form->captcha 			= $row['form_captcha'];
		$form->captcha_type 	= $row['form_captcha_type'];
		$form->review 			= $row['form_review'];
		$form->label_alignment  = $row['form_label_alignment'];
		$form->page_total 		= $row['form_page_total'];
		if($page_number === 0){ //this is edit_entry page
			$form->page_total = 1;
		}

		$form->lastpage_title 	= $row['form_lastpage_title'];
		$form->submit_primary_text 	 = $row['form_submit_primary_text'];
		$form->submit_secondary_text = $row['form_submit_secondary_text'];
		$form->submit_primary_img 	 = $row['form_submit_primary_img'];
		$form->submit_secondary_img  = $row['form_submit_secondary_img'];
		$form->submit_use_image  	 = (int) $row['form_submit_use_image'];
		$form->pagination_type		 = $row['form_pagination_type'];
		$form->review_primary_text 	 = $row['form_review_primary_text'];
		$form->review_secondary_text = $row['form_review_secondary_text'];
		$form->review_primary_img 	 = $row['form_review_primary_img'];
		$form->review_secondary_img  = $row['form_review_secondary_img'];
		$form->review_use_image  	 = (int) $row['form_review_use_image'];
		$form->review_title			 = $row['form_review_title'];
		$form->review_description	 = $row['form_review_description'];
		$form->resume_enable	 	 = $row['form_resume_enable'];
		$form->theme_id	    	 	 = (int) $row['form_theme_id'];
		$form->payment_show_total	 = (int) $row['payment_show_total'];
		$form->payment_total_location = $row['payment_total_location'];
		$form->payment_enable_merchant = (int) $row['payment_enable_merchant'];
		$form->payment_currency 	   = $row['payment_currency'];
		$form->payment_price_type 	   = $row['payment_price_type'];
		$form->payment_price_amount    = $row['payment_price_amount'];
		$form->limit_enable  	= (int) $row['form_limit_enable'];
		$form->limit  			= (int) $row['form_limit'];
		$form->schedule_enable  = (int) $row['form_schedule_enable'];
		$form->schedule_start_date  = $row['form_schedule_start_date'];
		$form->schedule_end_date  	= $row['form_schedule_end_date'];
		$form->schedule_start_hour  = $row['form_schedule_start_hour'];
		$form->schedule_end_hour  	= $row['form_schedule_end_hour'];
		$form->language 			= trim($row['form_language']);
		$form->logic_field_enable  	= (int) $row['logic_field_enable'];
		$form->logic_page_enable  	= (int) $row['logic_page_enable'];

		$form->enable_discount 		= (int) $row['payment_enable_discount'];
		$form->discount_type 	 	= $row['payment_discount_type'];
		$form->discount_amount 		= (float) $row['payment_discount_amount'];
		$form->discount_element_id 	= (int) $row['payment_discount_element_id'];

		$form->enable_tax 		 	= (int) $row['payment_enable_tax'];
		$form->tax_rate 			= (float) $row['payment_tax_rate'];

		$form->custom_script_enable = (int) $row['form_custom_script_enable'];
		$form->custom_script_url 	= $row['form_custom_script_url'];
		$form->entry_edit_enable 	= (int) $row['form_entry_edit_enable'];
		$form->entry_edit_auto_disable 			= (int) $row['form_entry_edit_auto_disable'];
		$form->entry_edit_auto_disable_period 	= (int) $row['form_entry_edit_auto_disable_period'];
		$form->entry_edit_auto_disable_unit 	= $row['form_entry_edit_auto_disable_unit'];

		//if the form has 'entry edit' enabled and edit key exist
		//populate the hidden edit_key input
		$is_edit_key_exist = false;
		if(!empty($form->entry_edit_enable) && !empty($_SESSION['mf_form_edit_key'][$form_id])){
			$mf_edit_tag = '<input type="hidden" id="edit_key" name="edit_key" value="'.htmlspecialchars($_SESSION['mf_form_edit_key'][$form_id]).'" />';
			$is_edit_key_exist = true;
		}

		//if the form has page logic enabled, store the page history
		if(!empty($form->logic_page_enable) && !empty($page_number)){
			//store the page numbers into session for history
			if($page_number == 1){ //if there is no current history, initialize with page 1
				$_SESSION['mf_pages_history'][$form_id] = array();
				$_SESSION['mf_pages_history'][$form_id][] = 1; 
			}else{
				//if the pages history already exist and the current page number already being stored
				//we need to remove it from the array first, along with any subsequent pages
				if(!empty($_SESSION['mf_pages_history'][$form_id]) && in_array($page_number, $_SESSION['mf_pages_history'][$form_id])){
					$current_page_index = array_search($page_number, $_SESSION['mf_pages_history'][$form_id]);
					array_splice($_SESSION['mf_pages_history'][$form_id], $current_page_index);
				}

				$_SESSION['mf_pages_history'][$form_id][] = (int) $page_number;
			}
		}

		if(!empty($form->language)){
			mf_set_language($form->language);
		}

		if(empty($error_elements)){
			$form->is_error 	= 0;
		}else{
			$form->is_error 	= 1;
		}

		if(!empty($edit_id)){
			$form->active = 1;
		}
		
		
		if($form->page_total == 1){
			//if there is form edit key within the URL, load the record into the form
			if(!empty($_GET['mf_edit']) && !empty($form->entry_edit_enable)){

				$edit_key = $_SESSION['mf_form_edit_key'][$form_id];

				$query  = "SELECT `id` FROM `".MF_TABLE_PREFIX."form_{$form_id}` WHERE edit_key=? AND `status`=1";
				$params = array($edit_key);
				
				$sth = mf_do_query($query,$params,$dbh);
				$row = mf_do_fetch_result($sth);
				$edit_key_entry_id = $row['id'];

				if(!empty($edit_key_entry_id)){
					//check if entry editing still allowed or not based on the "Disable Editing After..." preference
					if(!empty($form->entry_edit_auto_disable) && !empty($form->entry_edit_auto_disable_period) && !empty($form->entry_edit_auto_disable_unit)){
						if($form->entry_edit_auto_disable_unit == 'r'){ //expiry based on x revisions
							$query = "SELECT 
											count(*) total_revision from `".MF_TABLE_PREFIX."form_{$form_id}_log` 
									   WHERE 
									   		record_id=? and log_user='Form User'";
							$params = array($edit_key_entry_id);
					
							$sth = mf_do_query($query,$params,$dbh);
							$row = mf_do_fetch_result($sth);

							$total_revision = $row['total_revision'];

							if($total_revision >= $form->entry_edit_auto_disable_period){
								$custom_error = $mf_lang['entry_edit_max_revision'];
							}
						}else if($form->entry_edit_auto_disable_unit == 'h'){ //expiry based on x hours
							$form->entry_edit_auto_disable_period = (int) $form->entry_edit_auto_disable_period;

							$query = "SELECT
											IF(date_created + INTERVAL {$form->entry_edit_auto_disable_period} HOUR < now(),'1',NULL) as is_expired 
										FROM 
											`".MF_TABLE_PREFIX."form_{$form_id}` WHERE `id`=?";
							$params = array($edit_key_entry_id);
					
							$sth = mf_do_query($query,$params,$dbh);
							$row = mf_do_fetch_result($sth);

							if(!empty($row['is_expired'])){
								$custom_error = $mf_lang['entry_edit_link_expired'];
							}
						}else if($form->entry_edit_auto_disable_unit == 'd'){ //expiry based on x days
							$form->entry_edit_auto_disable_period = (int) $form->entry_edit_auto_disable_period;
							
							$query = "SELECT
											IF(date_created + INTERVAL {$form->entry_edit_auto_disable_period} DAY < now(),'1',NULL) as is_expired 
										FROM 
											`".MF_TABLE_PREFIX."form_{$form_id}` WHERE `id`=?";
							$params = array($edit_key_entry_id);
					
							$sth = mf_do_query($query,$params,$dbh);
							$row = mf_do_fetch_result($sth);

							if(!empty($row['is_expired'])){
								$custom_error = $mf_lang['entry_edit_link_expired'];
							}
						}
					}

					if(empty($custom_error)){
						$entry_params = array();
						$populated_values = mf_get_entry_values($dbh,$form_id,$edit_key_entry_id,false,$entry_params);
						
						//set session value to bypass unique checking
						$_SESSION['edit_entry']['form_id']  = $form_id;
						$_SESSION['edit_entry']['entry_id'] = $edit_key_entry_id; 
					}
				}else{
					//invalid edit_key given, display error message
					$custom_error = 'Invalid Link! <br/>Please open the complete URL to edit your entry.';
				}
			}

			//if there is form edit key within the URL and form review enabled, load the record from ap_form_x table to ap_form_x_review table
			if(!empty($_GET['mf_edit']) && !empty($form->entry_edit_enable) && !empty($form->review) && empty($custom_error)){
				$session_id = session_id();
				
				$edit_key = $_SESSION['mf_form_edit_key'][$form_id];
				
				//first delete existing record within review table
				$query = "DELETE from `".MF_TABLE_PREFIX."form_{$form_id}_review` where session_id=? or edit_key=?";
				$params = array($session_id, $edit_key);
				
				mf_do_query($query,$params,$dbh);
				
				//copy data from ap_form_x table to ap_form_x_review table
				$query  = "SELECT * FROM `".MF_TABLE_PREFIX."form_{$form_id}` WHERE edit_key=? AND `status`=1";
				$params = array($edit_key);
				
				$sth = mf_do_query($query,$params,$dbh);
				$row = mf_do_fetch_result($sth);
				
				$columns = array();
				if(!empty($row)){
					foreach($row as $column_name=>$column_data){
						if($column_name != 'id'){
							$columns[] = $column_name;
						}
					}	
				}
				
				if(empty($columns)){
					//invalid resume_key given, display error message
					$custom_error = 'Invalid Link! <br/>Please open the complete URL to edit your entry.';
				}else{	
				
					$columns_joined = implode("`,`",$columns);
					$columns_joined = '`'.$columns_joined.'`';
					
					//copy data from main table
					$query = "INSERT INTO `".MF_TABLE_PREFIX."form_{$form_id}_review`($columns_joined) SELECT {$columns_joined} from `".MF_TABLE_PREFIX."form_{$form_id}` WHERE edit_key=?";
					$params = array($edit_key);
					
					mf_do_query($query,$params,$dbh);
					
					$query = "UPDATE `".MF_TABLE_PREFIX."form_{$form_id}_review` set session_id=? WHERE edit_key=?";
					$params = array($session_id,$edit_key);
					
					mf_do_query($query,$params,$dbh);
					
					for($i=1;$i<=$form->page_total;$i++){
						$_SESSION['mf_form_loaded'][$form_id][$i] = true;
					}
					
					$_SESSION['mf_form_edit_loaded'][$form_id] = true;
					
					//unlike the resume_key, we can't do: unset($_SESSION['mf_form_edit_key'][$form_id]) here
					//because edit_key need to stay off the URL for security reason
				}

				//load files from ap_form_xxx_files into the filesystem
				if(MF_STORE_FILES_AS_BLOB === true){
					$query  = "SELECT element_id FROM ".MF_TABLE_PREFIX."form_elements WHERE form_id = ? and element_status = 1 and element_type = 'file'";
					$params = array($form_id);
							
					$saved_files_id = array();
					$sth = mf_do_query($query,$params,$dbh);
					while($row = mf_do_fetch_result($sth)){
						$saved_files_id[] = $row['element_id'];
					}
					
					if(!empty($saved_files_id)){
						$query  = "SELECT * FROM `".MF_TABLE_PREFIX."form_{$form_id}_review` WHERE session_id=? and edit_key=?";
						$params = array($session_id,$resume_key);
						
						$sth = mf_do_query($query,$params,$dbh);
						$row = mf_do_fetch_result($sth);

						foreach ($saved_files_id as $element_id) {
							$filename_record = $row['element_'.$element_id];
				
							if(empty($filename_record)){
								continue;
							}
							
							//if the file upload field is using advance uploader, $filename would contain multiple file names, separated by pipe character '|'
							$filename_array = array();
							$filename_array = explode('|',$filename_record);
							
							foreach ($filename_array as $filename){
								$filename = $mf_settings['upload_dir']."/form_{$form_id}/files/{$filename}.tmp";
								mf_ap_form_files_load($dbh,$form_id,$filename);
							}
						}
					}
				}

				//repopulate the values to the form from review table
				$query = "SELECT `id` from `".MF_TABLE_PREFIX."form_{$form_id}_review` where session_id=?";
				$params = array($session_id);
				
				$sth = mf_do_query($query,$params,$dbh);
				$row = mf_do_fetch_result($sth);

				$entry_params = array();
				$populated_values = array();
				$populated_values = mf_get_entry_values($dbh,$form_id,$row['id'],true,$entry_params);

			}

			//if this form has review enabled and user are having $_SESSION['review_id'], then populate the form with that values
			if(!empty($form->review) && !empty($_SESSION['review_id']) && empty($populated_values)){
				$entry_params = array();
				$entry_params['machform_data_path'] = $machform_data_path;

				$populated_values = mf_get_entry_values($dbh,$form_id,$_SESSION['review_id'],true,$entry_params);
			}elseif (!empty($form->review) && !empty($_SESSION['review_id']) && !empty($populated_values)){ //if form review enabled and there is some validation error, the uploaded files needs to be displayed
				$entry_params = array();
				$entry_params['machform_data_path'] = $machform_data_path;

				$populated_file_values = mf_get_entry_values($dbh,$form_id,$_SESSION['review_id'],true,$entry_params);
			}

			//populate the default value for uploaded files, when validation error occured
			if(!empty($error_elements) && !empty($_SESSION['mf_form_edit_key'][$form_id])){
				$edit_key = $_SESSION['mf_form_edit_key'][$form_id];

				$query  = "SELECT `id` FROM `".MF_TABLE_PREFIX."form_{$form_id}` WHERE edit_key=? AND `status`=1";
				$params = array($edit_key);
				
				$sth = mf_do_query($query,$params,$dbh);
				$row = mf_do_fetch_result($sth);
				$edit_key_entry_id = $row['id'];

				if(!empty($edit_key_entry_id)){
					$entry_params = array();
					$populated_file_values = mf_get_entry_values($dbh,$form_id,$edit_key_entry_id,false,$entry_params);
				}
			}
		}else{
			//if this is multipage form, always populate the fields
			$session_id = session_id();
			
			//if there is form resume key, load the record from ap_form_x table to ap_form_x_review table
			if(!empty($_SESSION['mf_form_resume_key'][$form_id]) && !empty($form->resume_enable)){
				$resume_key = $_SESSION['mf_form_resume_key'][$form_id];
				
				//first delete existing record within review table
				$query = "DELETE from `".MF_TABLE_PREFIX."form_{$form_id}_review` where session_id=? or resume_key=?";
				$params = array($session_id, $resume_key);
				
				mf_do_query($query,$params,$dbh);
				
				//copy data from ap_form_x table to ap_form_x_review table
				$query  = "SELECT * FROM `".MF_TABLE_PREFIX."form_{$form_id}` WHERE resume_key=? and `status`=2";
				$params = array($resume_key);
				
				$sth = mf_do_query($query,$params,$dbh);
				$row = mf_do_fetch_result($sth);
				
				$columns = array();
				if(!empty($row)){
					foreach($row as $column_name=>$column_data){
						if($column_name != 'id'){
							$columns[] = $column_name;
						}
					}	
				}
				
				if(empty($columns)){
					//invalid resume_key given, display error message
					$custom_error = 'Invalid Link! <br/>Please open the complete URL to resume your saved progress.';
				}else{	
				
					$columns_joined = implode("`,`",$columns);
					$columns_joined = '`'.$columns_joined.'`';
					
					//copy data from main table
					$query = "INSERT INTO `".MF_TABLE_PREFIX."form_{$form_id}_review`($columns_joined) SELECT {$columns_joined} from `".MF_TABLE_PREFIX."form_{$form_id}` WHERE resume_key=?";
					$params = array($resume_key);
					
					mf_do_query($query,$params,$dbh);
					
					$query = "UPDATE `".MF_TABLE_PREFIX."form_{$form_id}_review` set session_id=? WHERE resume_key=?";
					$params = array($session_id,$resume_key);
					
					mf_do_query($query,$params,$dbh);
					
					for($i=1;$i<=$form->page_total;$i++){
						$_SESSION['mf_form_loaded'][$form_id][$i] = true;
					}
					
					$_SESSION['mf_form_resume_loaded'][$form_id] = true;
					unset($_SESSION['mf_form_resume_key'][$form_id]);
				}

				//load files from ap_form_xxx_files into the filesystem
				if(MF_STORE_FILES_AS_BLOB === true){
					$query  = "SELECT element_id FROM ".MF_TABLE_PREFIX."form_elements WHERE form_id = ? and element_status = 1 and element_type = 'file'";
					$params = array($form_id);
							
					$saved_files_id = array();
					$sth = mf_do_query($query,$params,$dbh);
					while($row = mf_do_fetch_result($sth)){
						$saved_files_id[] = $row['element_id'];
					}
					
					if(!empty($saved_files_id)){
						$query  = "SELECT * FROM `".MF_TABLE_PREFIX."form_{$form_id}_review` WHERE session_id=? and resume_key=?";
						$params = array($session_id,$resume_key);
						
						$sth = mf_do_query($query,$params,$dbh);
						$row = mf_do_fetch_result($sth);

						foreach ($saved_files_id as $element_id) {
							$filename_record = $row['element_'.$element_id];
				
							if(empty($filename_record)){
								continue;
							}
							
							//if the file upload field is using advance uploader, $filename would contain multiple file names, separated by pipe character '|'
							$filename_array = array();
							$filename_array = explode('|',$filename_record);
							
							foreach ($filename_array as $filename){
								$filename = $mf_settings['upload_dir']."/form_{$form_id}/files/{$filename}.tmp";
								mf_ap_form_files_load($dbh,$form_id,$filename);
							}
						}
					}
				}
			}

			//if there is form edit key within the URL, load the record from ap_form_x table to ap_form_x_review table
			if(!empty($_GET['mf_edit']) && !empty($form->entry_edit_enable)){
				$edit_key = $_SESSION['mf_form_edit_key'][$form_id];

				$query  = "SELECT `id` FROM `".MF_TABLE_PREFIX."form_{$form_id}` WHERE edit_key=? AND `status`=1";
				$params = array($edit_key);
				
				$sth = mf_do_query($query,$params,$dbh);
				$row = mf_do_fetch_result($sth);
				$edit_key_entry_id = $row['id'];

				if(!empty($edit_key_entry_id)){

					//check if entry editing still allowed or not based on the "Disable Editing After..." preference
					if(!empty($form->entry_edit_auto_disable) && !empty($form->entry_edit_auto_disable_period) && !empty($form->entry_edit_auto_disable_unit)){
						if($form->entry_edit_auto_disable_unit == 'r'){ //expiry based on x revisions
							$query = "SELECT 
											count(*) total_revision from `".MF_TABLE_PREFIX."form_{$form_id}_log` 
									   WHERE 
									   		record_id=? and log_user='Form User'";
							$params = array($edit_key_entry_id);
					
							$sth = mf_do_query($query,$params,$dbh);
							$row = mf_do_fetch_result($sth);

							$total_revision = $row['total_revision'];

							if($total_revision >= $form->entry_edit_auto_disable_period){
								$custom_error = $mf_lang['entry_edit_max_revision'];
							}
							
						}else if($form->entry_edit_auto_disable_unit == 'h'){ //expiry based on x hours
							$form->entry_edit_auto_disable_period = (int) $form->entry_edit_auto_disable_period;

							$query = "SELECT
											IF(date_created + INTERVAL {$form->entry_edit_auto_disable_period} HOUR < now(),'1',NULL) as is_expired 
										FROM 
											`".MF_TABLE_PREFIX."form_{$form_id}` WHERE `id`=?";
							$params = array($edit_key_entry_id);
					
							$sth = mf_do_query($query,$params,$dbh);
							$row = mf_do_fetch_result($sth);

							if(!empty($row['is_expired'])){
								$custom_error = $mf_lang['entry_edit_link_expired'];
							}
						}else if($form->entry_edit_auto_disable_unit == 'd'){ //expiry based on x days
							$form->entry_edit_auto_disable_period = (int) $form->entry_edit_auto_disable_period;
							
							$query = "SELECT
											IF(date_created + INTERVAL {$form->entry_edit_auto_disable_period} DAY < now(),'1',NULL) as is_expired 
										FROM 
											`".MF_TABLE_PREFIX."form_{$form_id}` WHERE `id`=?";
							$params = array($edit_key_entry_id);
					
							$sth = mf_do_query($query,$params,$dbh);
							$row = mf_do_fetch_result($sth);

							if(!empty($row['is_expired'])){
								$custom_error = $mf_lang['entry_edit_link_expired'];
							}
						}
					}
				}else{
					//invalid edit_key given, display error message
					$custom_error = 'Invalid Link! <br/>Please open the complete URL to edit your entry.';
				}

				if(empty($custom_error)){
					//first delete existing record within review table
					$query = "DELETE from `".MF_TABLE_PREFIX."form_{$form_id}_review` where session_id=? or edit_key=?";
					$params = array($session_id, $edit_key);
					
					mf_do_query($query,$params,$dbh);
					
					//copy data from ap_form_x table to ap_form_x_review table
					$query  = "SELECT * FROM `".MF_TABLE_PREFIX."form_{$form_id}` WHERE edit_key=?";
					$params = array($edit_key);
					
					$sth = mf_do_query($query,$params,$dbh);
					$row = mf_do_fetch_result($sth);
					
					$columns = array();
					if(!empty($row)){
						foreach($row as $column_name=>$column_data){
							if($column_name != 'id'){
								$columns[] = $column_name;
							}
						}	
					}
					
					$columns_joined = implode("`,`",$columns);
					$columns_joined = '`'.$columns_joined.'`';
					
					//copy data from main table
					$query = "INSERT INTO `".MF_TABLE_PREFIX."form_{$form_id}_review`($columns_joined) SELECT {$columns_joined} from `".MF_TABLE_PREFIX."form_{$form_id}` WHERE edit_key=?";
					$params = array($edit_key);
					
					mf_do_query($query,$params,$dbh);
					
					$query = "UPDATE `".MF_TABLE_PREFIX."form_{$form_id}_review` set session_id=? WHERE edit_key=?";
					$params = array($session_id,$edit_key);
					
					mf_do_query($query,$params,$dbh);
					
					for($i=1;$i<=$form->page_total;$i++){
						$_SESSION['mf_form_loaded'][$form_id][$i] = true;
					}
					
					$_SESSION['mf_form_edit_loaded'][$form_id] = true;

					//set session value to bypass unique checking
					$_SESSION['edit_entry']['form_id']  = $form_id;
					$_SESSION['edit_entry']['entry_id'] = $edit_key_entry_id;
					
					//unlike the resume_key, we can't do: unset($_SESSION['mf_form_edit_key'][$form_id]) here
					//because edit_key need to stay off the URL for security reason

					//load files from ap_form_xxx_files into the filesystem
					if(MF_STORE_FILES_AS_BLOB === true){
						$query  = "SELECT element_id FROM ".MF_TABLE_PREFIX."form_elements WHERE form_id = ? and element_status = 1 and element_type = 'file'";
						$params = array($form_id);
								
						$saved_files_id = array();
						$sth = mf_do_query($query,$params,$dbh);
						while($row = mf_do_fetch_result($sth)){
							$saved_files_id[] = $row['element_id'];
						}
						
						if(!empty($saved_files_id)){
							$query  = "SELECT * FROM `".MF_TABLE_PREFIX."form_{$form_id}_review` WHERE session_id=? and edit_key=?";
							$params = array($session_id,$resume_key);
							
							$sth = mf_do_query($query,$params,$dbh);
							$row = mf_do_fetch_result($sth);

							foreach ($saved_files_id as $element_id) {
								$filename_record = $row['element_'.$element_id];
					
								if(empty($filename_record)){
									continue;
								}
								
								//if the file upload field is using advance uploader, $filename would contain multiple file names, separated by pipe character '|'
								$filename_array = array();
								$filename_array = explode('|',$filename_record);
								
								foreach ($filename_array as $filename){
									$filename = $mf_settings['upload_dir']."/form_{$form_id}/files/{$filename}.tmp";
									mf_ap_form_files_load($dbh,$form_id,$filename);
								}
							}
						}
					}
				}
			}
			
			$query = "SELECT `id` from `".MF_TABLE_PREFIX."form_{$form_id}_review` where session_id=?";
			$params = array($session_id);
			
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);

			//we need to check mf_form_loaded to make sure default values of fields are being loaded on the first view of the form
			if(empty($populated_values) && !empty($_SESSION['mf_form_loaded'][$form_id][$page_number])){
				$entry_params = array();
				$entry_params['machform_data_path'] = $machform_data_path;

				$populated_values = mf_get_entry_values($dbh,$form_id,$row['id'],true,$entry_params);
			}else{ //if there is some validation error, the uploaded files needs to be displayed
				$entry_params = array();
				$entry_params['machform_data_path'] = $machform_data_path;
				
				if(isset($row['id'])){
					$populated_file_values = mf_get_entry_values($dbh,$form_id,$row['id'],true,$entry_params);
				}
			}
		}
		
		//get price definitions for fields, if the merchant feature is enabled
		if(!empty($form->payment_enable_merchant) && $form->payment_price_type == 'variable'){
			$query = "select 
							element_id,
							option_id,
							`price` 
					   from 
					   		`".MF_TABLE_PREFIX."element_prices` 
					   where 
					   		form_id=? 
				   order by 
				   			element_id,option_id asc";
			$params = array($form_id);
			$sth = mf_do_query($query,$params,$dbh);
			while($row = mf_do_fetch_result($sth)){
				$element_prices_array[$row['element_id']][$row['option_id']] = $row['price'];
			}	
		}
		
		//get elements data
		//get element options first and store it into array
		$query = "SELECT 
						element_id,
						option_id,
						`position`,
						`option`,
						option_is_default,
						option_is_hidden 
				    FROM 
				    	".MF_TABLE_PREFIX."element_options 
				   where 
				   		form_id = ? and live=1 
				order by 
						element_id asc,`position` asc";
		$params = array($form_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		while($row = mf_do_fetch_result($sth)){
			$element_id = $row['element_id'];
			$option_id  = $row['option_id'];
			$options_lookup[$element_id][$option_id]['position'] 		  = $row['position'];
			$options_lookup[$element_id][$option_id]['option'] 			  = $row['option'];
			$options_lookup[$element_id][$option_id]['option_is_default'] = $row['option_is_default'];
			$options_lookup[$element_id][$option_id]['option_is_hidden']  = $row['option_is_hidden'];
			
			if(isset($element_prices_array[$element_id][$option_id])){
				$options_lookup[$element_id][$option_id]['price_definition'] = $element_prices_array[$element_id][$option_id];
			}
		}
	
		$matrix_elements = array();
		
		//get elements data
		$element = array();

		if($page_number === 0){ //if page_number is 0, display all pages (this is being used on edit_entry page)
			$page_number_clause = '';
			$params = array($form_id);
		}else{
			$page_number_clause = 'and element_page_number = ?';
			$params = array($form_id,$page_number);
		}

		$query = "SELECT 
						element_id,
						element_title,
						element_guidelines,
						element_size,
						element_is_required,
						element_is_unique,
						element_is_readonly,
						element_is_private,
						element_type,
						element_position,
						element_default_value,
						element_enable_placeholder,
						element_constraint,
						element_choice_has_other,
						element_choice_other_label,
						element_choice_columns,
						element_choice_max_entry,
						element_time_showsecond, 
						element_time_24hour,
						element_address_us_only,
						element_date_enable_range,
						element_date_range_min,
						element_date_range_max,
						element_date_enable_selection_limit,
						element_date_selection_max,
						element_date_disable_past_future,
						element_date_past_future,
						element_date_disable_dayofweek,
						element_date_disabled_dayofweek_list,
						element_date_disable_specific,
						element_date_disabled_list,
						element_file_type_list,
						element_file_as_attachment,
						element_file_auto_upload,
						element_file_enable_multi_upload,
						element_file_max_selection,
						element_file_enable_size_limit,
						element_file_size_max,
						element_matrix_allow_multiselect,
						element_matrix_parent_id,
						element_range_min,
						element_range_max,
						element_range_limit_by,
						element_css_class,
						element_section_display_in_email,
						element_section_enable_scroll,
						element_number_enable_quantity,
						element_number_quantity_link,
						element_text_default_type,
						element_text_default_length,
						element_text_default_random_type,
						element_text_default_prefix,
						element_text_default_case,
						element_email_enable_confirmation,
						element_email_confirm_field_label,
						element_media_type,
						ifnull(element_media_image_src,'') as element_media_image_src,
						ifnull(element_media_image_width,'') as element_media_image_width,
						ifnull(element_media_image_height,'') as element_media_image_height,
						ifnull(element_media_image_alignment,'') as element_media_image_alignment,
						ifnull(element_media_image_alt,'') as element_media_image_alt,
						ifnull(element_media_image_href,'') as element_media_image_href,
						element_media_display_in_email,
						ifnull(element_media_video_src,'') as element_media_video_src,
						ifnull(element_media_video_size,'') as element_media_video_size,
						element_media_video_muted,
						ifnull(element_media_video_caption_file,'') element_media_video_caption_file,
						ifnull(element_media_pdf_src,'') element_media_pdf_src,
						ifnull(element_rating_style,'') element_rating_style,
						element_rating_max,
						element_rating_default,
						element_rating_enable_label,
						ifnull(element_rating_label_high,'') element_rating_label_high,
						ifnull(element_rating_label_low,'') element_rating_label_low,
						ifnull(element_address_subfields_labels,'') element_address_subfields_labels,
						ifnull(element_address_subfields_visibility,'') element_address_subfields_visibility,  
						ifnull(element_address_default_state,'') element_address_default_state 
					FROM 
						".MF_TABLE_PREFIX."form_elements 
				   WHERE 
				   		form_id = ? and element_status='1' {$page_number_clause} and element_type <> 'page_break'
				ORDER BY 
						element_position asc";
		
		$sth = mf_do_query($query,$params,$dbh);
		
		$j=0;
		$has_calendar = false; //assume the form doesn't have calendar, so it won't load calendar.js
		$has_advance_uploader = false;
		$has_signature_pad = false;
		$has_guidelines = false;
		$has_media_video = false;
		$has_media_pdf = false;
		
		while($row = mf_do_fetch_result($sth)){
			
			$element[$j] = new stdClass();
			
			$element_id = $row['element_id'];
			
			//lookup element options first
			if(!empty($options_lookup[$element_id])){
				$element_options = array();
				$i=0;
				foreach ($options_lookup[$element_id] as $option_id=>$data){
					$element_options[$i] = new stdClass();
					$element_options[$i]->id 		 = $option_id;
					$element_options[$i]->option 	 = $data['option'];
					$element_options[$i]->is_default = $data['option_is_default'];
					$element_options[$i]->is_hidden  = $data['option_is_hidden'];
					$element_options[$i]->is_db_live = 1;
					
					if(isset($data['price_definition'])){
						$element_options[$i]->price_definition = $data['price_definition'];
					}
					
					$i++;
				}
			}
			
		
			//populate elements
			$element[$j]->title 		= nl2br($row['element_title']);
			$element[$j]->guidelines 	= nl2br($row['element_guidelines']);
			
			if(!empty($row['element_guidelines']) && ($row['element_is_private'] == 0) && !in_array($row['element_type'], array('section','media','matrix','signature'))){
				$has_guidelines = true;
			}
			
			$element[$j]->size 			= $row['element_size'];
			$element[$j]->is_required 	= $row['element_is_required'];
			$element[$j]->is_readonly 	= $row['element_is_readonly'];
			$element[$j]->is_unique 	= $row['element_is_unique'];
			$element[$j]->is_private 	= $row['element_is_private'];
			$element[$j]->type 			= $row['element_type'];
			$element[$j]->position 		= $row['element_position'];
			$element[$j]->id 			= $row['element_id'];
			$element[$j]->is_db_live 	= 1;
			$element[$j]->form_id 		= $form_id;
			$element[$j]->choice_has_other   = (int) $row['element_choice_has_other'];
			$element[$j]->choice_other_label = $row['element_choice_other_label'];
			$element[$j]->choice_columns   	 = (int) $row['element_choice_columns'];
			
			$element[$j]->choice_max_entry   = (int) $row['element_choice_max_entry'];
			if(!empty($element[$j]->choice_max_entry) || $element[$j]->type == 'file'){
				$element[$j]->dbh = $dbh;
			}

			$element[$j]->time_showsecond    = (int) $row['element_time_showsecond'];
			$element[$j]->time_24hour    	 = (int) $row['element_time_24hour'];
			$element[$j]->address_us_only	 = (int) $row['element_address_us_only'];
			$element[$j]->date_enable_range	 = (int) $row['element_date_enable_range'];
			$element[$j]->date_range_min	 = $row['element_date_range_min'];
			$element[$j]->date_range_max	 = $row['element_date_range_max'];
			$element[$j]->date_enable_selection_limit	 = (int) $row['element_date_enable_selection_limit'];
			$element[$j]->date_selection_max	 		 = (int) $row['element_date_selection_max'];
			$element[$j]->date_disable_past_future	 	= (int) $row['element_date_disable_past_future'];
			$element[$j]->date_past_future	 			= $row['element_date_past_future'];
			$element[$j]->date_disable_dayofweek	 	= (int) $row['element_date_disable_dayofweek'];
			$element[$j]->date_disabled_dayofweek_list	= $row['element_date_disabled_dayofweek_list'];
			$element[$j]->date_disable_specific	 		= (int) $row['element_date_disable_specific'];
			$element[$j]->date_disabled_list	 		= $row['element_date_disabled_list'];
			$element[$j]->file_type_list	 			= $row['element_file_type_list'];
			$element[$j]->file_as_attachment	 		= (int) $row['element_file_as_attachment'];	
			
			if($row['element_type'] == 'file'){
				$has_advance_uploader = true;
			}
			
			$element[$j]->file_auto_upload	 			= (int) $row['element_file_auto_upload'];
			$element[$j]->file_enable_multi_upload	 	= (int) $row['element_file_enable_multi_upload'];
			$element[$j]->file_max_selection	 		= (int) $row['element_file_max_selection'];
			$element[$j]->file_enable_size_limit	 	= (int) $row['element_file_enable_size_limit'];
			$element[$j]->file_size_max	 				= (int) $row['element_file_size_max'];
			$element[$j]->matrix_allow_multiselect	 	= (int) $row['element_matrix_allow_multiselect'];
			$element[$j]->matrix_parent_id	 			= (int) $row['element_matrix_parent_id'];
			$element[$j]->upload_dir	 				= $mf_settings['upload_dir'];		
			$element[$j]->range_min	 					= $row['element_range_min'];
			$element[$j]->range_max	 					= $row['element_range_max'];
			$element[$j]->range_limit_by	 			= $row['element_range_limit_by'];
			$element[$j]->css_class	 					= $row['element_css_class'];
			$element[$j]->machform_path	 				= $machform_path;
			$element[$j]->machform_data_path	 		= $machform_data_path;
			$element[$j]->section_display_in_email	 	= (int) $row['element_section_display_in_email'];
			$element[$j]->section_enable_scroll	 		= (int) $row['element_section_enable_scroll'];
			$element[$j]->text_default_type	 			= $row['element_text_default_type'];
			$element[$j]->text_default_length	 		= (int) $row['element_text_default_length'];
			$element[$j]->text_default_random_type	 	= $row['element_text_default_random_type'];
			$element[$j]->text_default_prefix	 		= $row['element_text_default_prefix'];
			$element[$j]->text_default_case	 			= $row['element_text_default_case'];
			$element[$j]->email_enable_confirmation	 	= (int) $row['element_email_enable_confirmation'];
			$element[$j]->email_confirm_field_label	 	= $row['element_email_confirm_field_label'];
			$element[$j]->media_type	 				= $row['element_media_type'];
			$element[$j]->media_image_src	 			= trim($row['element_media_image_src']);
			$element[$j]->media_image_width	 			= trim($row['element_media_image_width']);
			$element[$j]->media_image_height	 		= trim($row['element_media_image_height']);
			$element[$j]->media_image_alignment	 		= trim($row['element_media_image_alignment']);
			$element[$j]->media_image_alt	 			= trim($row['element_media_image_alt']);
			$element[$j]->media_image_href	 			= trim($row['element_media_image_href']);
			$element[$j]->media_display_in_email		= (int) $row['element_media_display_in_email'];
			$element[$j]->media_video_src	 			= trim($row['element_media_video_src']);
			$element[$j]->media_video_size	 			= trim($row['element_media_video_size']);
			$element[$j]->media_video_muted	 			= (int) $row['element_media_video_muted'];
			$element[$j]->media_video_caption_file	 	= trim($row['element_media_video_caption_file']);
			$element[$j]->media_pdf_src	 				= trim($row['element_media_pdf_src']);
			$element[$j]->rating_max	 				= (int) $row['element_rating_max'];
			$element[$j]->rating_enable_label	 		= (int) $row['element_rating_enable_label'];
			$element[$j]->rating_default	 			= (int) $row['element_rating_default'];
			$element[$j]->rating_style	 				= trim($row['element_rating_style']);
			$element[$j]->rating_label_high	 			= trim($row['element_rating_label_high']);
			$element[$j]->rating_label_low	 			= trim($row['element_rating_label_low']);
			$element[$j]->address_subfields_labels	 	= trim($row['element_address_subfields_labels']);
			$element[$j]->address_subfields_visibility	= trim($row['element_address_subfields_visibility']);
			$element[$j]->address_default_state			= trim($row['element_address_default_state']);

			if($element[$j]->media_type == 'video' && !empty($element[$j]->media_video_src)){
				$has_media_video = true;
			}

			if($element[$j]->media_type == 'pdf' && !empty($element[$j]->media_pdf_src)){
				$has_media_pdf = true;
			}

			if(!empty($form->payment_enable_merchant) && !empty($row['element_number_enable_quantity']) && !empty($row['element_number_quantity_link'])){
				$element[$j]->number_quantity_link	 	= $row['element_number_quantity_link'];
			}

			//this data came from db or form submit
			//being used to display edit form or redisplay form with errors and previous inputs
			//this should be optimized in the future, only pass necessary data, not the whole array			
			$element[$j]->populated_value = $populated_values;
			
			
			//set prices for price-enabled field
			if($row['element_type'] == 'money' && isset($element_prices_array[$row['element_id']][0])){
				$element[$j]->price_definition = 0;
			}
			
			//if there is file upload type, set form enctype to multipart
			$form_enc_type = '';
			if($row['element_type'] == 'file'){
				$form_enc_type = 'enctype="multipart/form-data"';
				
				//if this is single page form with review enabled or multipage form
				if ((!empty($form->review) && !empty($_SESSION['review_id']) && !empty($populated_file_values)) ||
					($form->page_total > 1 && !empty($populated_file_values)) || 
					(!empty($_SESSION['mf_form_edit_key'][$form_id]) && !empty($populated_file_values)) 
				) {
					//populate the default value for uploaded files, when validation error occured

					//make sure to keep the file token if exist
					if(!empty($populated_values['element_'.$row['element_id']]['file_token'])){
						$populated_file_values['element_'.$row['element_id']]['file_token'] = $populated_values['element_'.$row['element_id']]['file_token'];
					}

					$element[$j]->populated_value = $populated_file_values;
				}
			}

			if(!empty($edit_id) && $_SESSION['mf_logged_in'] === true){
				//if this is edit_entry page
				$element[$j]->is_edit_entry = true;
			}
			
			if(!empty($error_elements[$element[$j]->id])){
				$element[$j]->is_error 	    = 1;
				$element[$j]->error_message = $error_elements[$element[$j]->id];
			}
			
			
			$element[$j]->default_value = htmlspecialchars($row['element_default_value']);
			$element[$j]->enable_placeholder = $row['element_enable_placeholder'];
			
			
			$element[$j]->constraint 	= $row['element_constraint'];
			if(!empty($element_options)){
				$element[$j]->options 	= $element_options;
			}else{
				$element[$j]->options 	= '';
			}
			
			//check for signature type
			if($row['element_type'] == 'signature'){
				$has_signature_pad = true;
			}
			
			//check for calendar type
			if($row['element_type'] == 'date' || $row['element_type'] == 'europe_date'){
				$has_calendar = true;
				
				//if the field has date selection limit, we need to do query to existing entries and disable all date which reached the limit
				if(!empty($row['element_date_enable_selection_limit']) && !empty($row['element_date_selection_max'])){
					$sub_query = "select 
										selected_date 
									from (
											select 
												  date_format(element_{$row['element_id']},'%m/%d/%Y') as selected_date,
												  count(element_{$row['element_id']}) as total_selection 
										      from 
										      	  ".MF_TABLE_PREFIX."form_{$form_id} 
										     where 
										     	  status=1 and element_{$row['element_id']} is not null 
										  group by 
										  		  element_{$row['element_id']}
										 ) as A
								   where 
										 A.total_selection >= ?";
					$params = array($row['element_date_selection_max']);
					$sub_sth = mf_do_query($sub_query,$params,$dbh);
					$current_date_disabled_list = array();
					$current_date_disabled_list_joined = '';
					
					while($sub_row = mf_do_fetch_result($sub_sth)){
						$current_date_disabled_list[] = $sub_row['selected_date'];
					}
					
					$current_date_disabled_list_joined = implode(',',$current_date_disabled_list);
					if(!empty($element[$j]->date_disable_specific)){ //add to existing disable date list
						if(empty($element[$j]->date_disabled_list)){
							$element[$j]->date_disabled_list = $current_date_disabled_list_joined;
						}else{
							$element[$j]->date_disabled_list .= ','.$current_date_disabled_list_joined;
						}
					}else{
						//'disable specific date' is not enabled, we need to override and enable it from here
						$element[$j]->date_disable_specific = 1;
						$element[$j]->date_disabled_list = $current_date_disabled_list_joined;
					}
					
				}
			}
			
			//if the element is a matrix field and not the parent, store the data into a lookup array for later use when rendering the markup
			if($row['element_type'] == 'matrix' && !empty($row['element_matrix_parent_id'])){
				
				$parent_id 	 = $row['element_matrix_parent_id'];
				$el_position = $row['element_position'];
				$matrix_elements[$parent_id][$el_position]['title'] = $element[$j]->title; 
				$matrix_elements[$parent_id][$el_position]['id'] 	= $element[$j]->id; 
				
				$matrix_child_option_id = '';
				foreach($element_options as $value){
					$matrix_child_option_id .= $value->id.',';
				}
				$matrix_child_option_id = rtrim($matrix_child_option_id,',');
				$matrix_elements[$parent_id][$el_position]['children_option_id'] = $matrix_child_option_id; 
				
				//remove it from the main element array
				$element[$j] = array();
				unset($element[$j]);
				$j--;
			}
			
			$j++;
		}
		
		
		//add captcha if enabled
		//on multipage form, captcha should be displayed on the last page only
		$recaptcha2_header = '';
		if(!empty($form->captcha) && (empty($edit_id))){
			if($form->page_total == 1 || ($form->page_total == $page_number)){
				if(!isset($_SESSION['captcha_passed'][$form_id]) || $_SESSION['captcha_passed'][$form_id] !== true){
					$element[$j] = new stdClass();
					$element[$j]->type 			= 'captcha';
					$element[$j]->captcha_type 	= $form->captcha_type;
					$element[$j]->form_id 		= $form_id;
					$element[$j]->is_private	= 0;
					$element[$j]->recaptcha_site_key   = $mf_settings['recaptcha_site_key'];
					$element[$j]->recaptcha_secret_key = $mf_settings['recaptcha_secret_key'];
					$element[$j]->machform_path = $machform_path;
					$element[$j]->dbh 			= $dbh;

					if(!empty($error_elements['element_captcha'])){
						$element[$j]->is_error 	    = 1;
						$element[$j]->error_message = $error_elements['element_captcha'];
					}

					//initialize reCAPTCHA v2 header
					if($form->captcha_type == 'n'){
						
						switch ($form->language) {
							case 'bulgarian': $recaptcha2_language = 'bg'; break;
							case 'chinese': $recaptcha2_language = 'zh-TW'; break;
							case 'chinese_simplified': $recaptcha2_language = 'zh-CN'; break;
							case 'danish': $recaptcha2_language = 'da'; break;
							case 'dutch': $recaptcha2_language = 'nl'; break;
							case 'estonian': $recaptcha2_language = 'en'; break;
							case 'finnish': $recaptcha2_language = 'fi'; break;
							case 'french': $recaptcha2_language = 'fr'; break;
							case 'german': $recaptcha2_language = 'de'; break;
							case 'greek': $recaptcha2_language = 'el'; break;
							case 'hungarian': $recaptcha2_language = 'hu'; break;
							case 'indonesia': $recaptcha2_language = 'id'; break;
							case 'italian': $recaptcha2_language = 'it'; break;
							case 'japanese': $recaptcha2_language = 'ja'; break;
							case 'norwegian': $recaptcha2_language = 'no'; break;
							case 'polish': $recaptcha2_language = 'pl'; break;
							case 'portuguese': $recaptcha2_language = 'pt'; break;
							case 'romanian': $recaptcha2_language = 'ro'; break;
							case 'russian': $recaptcha2_language = 'ru'; break;
							case 'slovak': $recaptcha2_language = 'sk'; break;
							case 'spanish': $recaptcha2_language = 'es'; break;
							case 'swedish': $recaptcha2_language = 'sv'; break;
							default:
								$recaptcha2_language = 'en';
								break;
						}

						$recaptcha2_header 	 = "<script type=\"text/javascript\" src=\"https://www.google.com/recaptcha/api.js?hl={$recaptcha2_language}\"></script>\n";
					}
				}
			}
		}
		
		//generate html markup for each element
		$container_class = '';
		$all_element_markup = '';
		$form->error_message = '';

		foreach ($element as $element_data){
			if(($element_data->is_private == 1) && empty($edit_id)){ //don't show private element on live forms
				continue;
			}
			
			//if this is matrix field, build the children data from $matrix_elements array
			if($element_data->type == 'matrix'){
				$element_data->matrix_children = $matrix_elements[$element_data->id];
			}
			
			$all_element_markup .= call_user_func('mf_display_'.$element_data->type,$element_data);
		}
		
		if(!empty($custom_error)){
			$form->error_message =<<<EOT
			<li role="alert" id="error_message">
					<h3 id="error_message_title">{$custom_error}</h3>
			</li>	
EOT;
		}elseif(!empty($error_elements)){
			$form->error_message =<<<EOT
			<li id="error_message">
					<h3 role="alert" id="error_message_title">{$mf_lang['error_title']}</h3>
					<p role="alert" id="error_message_desc">{$mf_lang['error_desc']}</p>
			</li>	
EOT;
		}
		
		//if this form is using custom theme and not on edit entry page
		$theme_css_link = '';
		$font_css_markup = '';
		$auto_height_js = '';
		$form_container_class = '';

		if(!empty($form->theme_id) && empty($edit_id)){
			//get the field highlight color for the particular theme
			$query = "SELECT 
							highlight_bg_type,
							highlight_bg_color,
							form_shadow_style,
							form_shadow_size,
							form_shadow_brightness,
							form_button_type,
							form_button_text,
							form_button_image,
							theme_has_css  
						FROM 
							".MF_TABLE_PREFIX."form_themes 
					   WHERE 
					   		theme_id = ?";
			$params = array($form->theme_id);
			
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);
			
			$form_shadow_style 		= $row['form_shadow_style'];
			$form_shadow_size 		= $row['form_shadow_size'];
			$form_shadow_brightness = $row['form_shadow_brightness'];
			$theme_has_css = (int) $row['theme_has_css'];
			
			//if the theme has css file, make sure to refer to that file
			//otherwise, generate the css dynamically


			//make sure to put a timestamp for the CSS file for logged in users
			//so that the CSS file generated by the theme editor is always being applied immediately
			if(!empty($_SESSION['mf_logged_in'])){
				$css_timestamp_1 = "?t=".time(); 
				$css_timestamp_2 = "&t=".time();
			}

			if(!empty($theme_has_css)){
				$theme_css_link = '<link rel="stylesheet" type="text/css" href="'.$machform_path.$mf_settings['data_dir'].'/themes/theme_'.$form->theme_id.'.css'.$css_timestamp_1.'" media="all" />';
			}else{
				$theme_css_link = '<link rel="stylesheet" type="text/css" href="'.$machform_path.'css_theme.php?theme_id='.$form->theme_id.$css_timestamp_2.'" media="all" />';
			}
			
			if($row['highlight_bg_type'] == 'color'){
				$field_highlight_color = $row['highlight_bg_color'];
			}else{ 
				//if the field highlight is using pattern instead of color, set the color to empty string
				$field_highlight_color = ''; 
			}
			
			//get the css link for the fonts
			$font_css_markup = mf_theme_get_fonts_link($dbh,$form->theme_id);
			
			//get the form shadow classes
			if(!empty($form_shadow_style) && ($form_shadow_style != 'disabled')){
				preg_match_all("/[A-Z]/",$form_shadow_style,$prefix_matches);
				//this regex simply get the capital characters of the shadow style name
				//example: RightPerspectiveShadow result to RPS and then being sliced to RP
				$form_shadow_prefix_code = substr(implode("",$prefix_matches[0]),0,-1);
				
				$form_shadow_size_class  = $form_shadow_prefix_code.ucfirst($form_shadow_size);
				$form_shadow_brightness_class = $form_shadow_prefix_code.ucfirst($form_shadow_brightness);

				if(empty($integration_method)){ //only display shadow if the form is not being embedded using any method
					$form_container_class = $form_shadow_style.' '.$form_shadow_size_class.' '.$form_shadow_brightness_class;
				}
			}
			
			//get the button text/image setting
			if(empty($form->review)){
				if($row['form_button_type'] == 'text'){
					$submit_button_markup = '<input id="submit_form" class="button_text" type="submit" name="submit_form" value="'.$row['form_button_text'].'" />';
				}else{
					$submit_button_markup = '<input class="submit_img_primary" type="image" alt="Submit" id="submit_form" name="submit_form" src="'.$row['form_button_image'].'" />';
				}			
			}else{
				if($row['form_button_type'] == 'text'){
					$submit_button_markup = '<input id="submit_form" class="button_text" type="submit" name="submit_form" value="'.$mf_lang['continue_button'].'" />';
				}else{
					$submit_button_markup = '<input class="submit_img_primary" type="image" alt="Submit" id="submit_form" name="submit_form" src="'.$row['form_button_image'].'" />';
				}
			}
			
		}else{ //if the form doesn't have any theme being applied
			$field_highlight_color = '#FFF7C0';
			
			if(empty($integration_method)){
				$form_container_class = ''; //default shadow as of v4.2 is no-shadow
			}else{
				$form_container_class = ''; //dont show any shadow when the form being embedded
			}
			
			
			if(empty($form->review)){
				$submit_button_markup = '<input id="submit_form" class="button_text" type="submit" name="submit_form" value="'.$mf_lang['submit_button'].'" />';
			}else{
				$submit_button_markup = '<input id="submit_form" class="button_text" type="submit" name="submit_form" value="'.$mf_lang['continue_button'].'" />';
			}
		}
		
		//display edit_id if there is any, this is being called on edit_entry.php page
		if(!empty($edit_id)){
			$edit_markup = "<input type=\"hidden\" name=\"edit_id\" value=\"{$edit_id}\" />\n";
			$submit_button_markup = '<input id="submit_form" class="bb_button bb_green" type="submit" name="submit_form" value="Save Changes" />';
		}else{
			$edit_markup = '';
		}
		
		//display CSRF token, if token exist
		if(!empty($form_params['csrf_token'])){
			$csrf_markup = "<input type=\"hidden\" name=\"csrf_token\" value=\"{$form_params['csrf_token']}\" />\n";
		}else{
			$csrf_markup = '';
		}

		//check for specific form css, if any, use it instead
		if($form->has_css){
			$css_dir = $mf_settings['data_dir']."/form_{$form_id}/css/";
		}
		
		if(!empty($form->password) && empty($_SESSION['user_authenticated'])){ //if form require password and password hasn't set yet
			$show_password_form = true;
			
		}elseif (!empty($form->password) && !empty($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] != $form_id){ //if user authenticated but not for this form
			$show_password_form = true;
			
		}else{ //user authenticated for this form, or no password required
			$show_password_form = false;
		}

		//if there is edit key, user is editing an entry, no need to display form password any longer
		if (!empty($form->password) && !empty($form->entry_edit_enable) && !empty($_SESSION['mf_form_edit_key'][$form_id])){ 
			$show_password_form = false;
		}
		
		if($show_password_form){
			$submit_button_markup = '<input id="submit_form" class="button_text" type="submit" name="submit_form" value="'.$mf_lang['submit_button'].'" />';
		}

		//default markup for single page form submit button
		$button_markup =<<<EOT
		<li id="li_buttons" class="buttons">
			    <input type="hidden" name="form_id" value="{$form->id}" />
			    {$mf_edit_tag}
			    {$mfsid_tag}
			    {$edit_markup}
			    {$csrf_markup}
			    <input type="hidden" name="submit_form" value="1" />
			    <input type="hidden" name="page_number" value="{$page_number}" />
				{$submit_button_markup}
		</li>
EOT;
		
		//check for form limit rule
		$form_has_maximum_entries = false;
		
		if(!empty($form->limit_enable)){
			if(!empty($form->payment_enable_merchant)){
				//if the form has payment enabled, we only count paid records or unpaid records within the last 30 minutes
				$query = "select 
								count(*) total_row 
							from
								(select 
									  A.`id`,
									  A.`status`,
									  A.date_created,  
									  (select payment_status from ".MF_TABLE_PREFIX."form_payments where record_id=A.`id` and form_id = ? limit 1) payment_status
								  from 
								      ".MF_TABLE_PREFIX."form_{$form_id} A where A.`status` = 1) B
						   where 
							    (B.`payment_status` = 'paid' and B.`status` = 1) OR
							    (B.`payment_status` is null and B.status = 1 and B.date_created > now()-interval 30 minute)";
				$params = array($form_id);
			}else{
				$query = "select count(*) total_row from ".MF_TABLE_PREFIX."form_{$form_id} where `status`=1";
				$params = array();
			}

			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);
			
			$total_entries  = $row['total_row'];

			if($total_entries >= $form->limit){
				$form_has_maximum_entries = true;
			}
		}

		//check for automatic scheduling limit, if enabled
		if(!empty($form->schedule_enable) && empty($edit_id)){
			$schedule_start_time = strtotime($form->schedule_start_date.' '.$form->schedule_start_hour);
			$schedule_end_time = strtotime($form->schedule_end_date.' '.$form->schedule_end_hour);

			$current_time = strtotime(date("Y-m-d H:i:s"));

			if(!empty($schedule_start_time)){
				if($current_time < $schedule_start_time){
					$form->active = 0;
				}
			}

			if(!empty($schedule_end_time)){
				if($current_time > $schedule_end_time){
					$form->active = 0;
				}
			}
		}

		$is_edit_entry = false;
		if(!empty($edit_id) && $_SESSION['mf_logged_in'] === true){
			//if this is edit_entry page
			$is_edit_entry = true;
		}
		
		$ul_class = '';
		$custom_element = '';
		$form_resume_markup = '';

		if( (empty($form->active) || $form_has_maximum_entries) && $is_edit_entry === false && $is_edit_key_exist === false){ //if form is not active, don't show the fields
			$form_desc_div ='';	
			$all_element_markup = '';
			$button_markup = '';
			$ul_class = 'class="password"';

			if($form_has_maximum_entries){
				$inactive_message = $mf_lang['form_limited'];
			}else{
				$inactive_message = $mf_lang['form_inactive'];
			}

			if(!empty($form->disabled_message)){
				$inactive_message = nl2br($form->disabled_message);
			}

			$custom_element =<<<EOT
			<li>
				<h2>{$inactive_message}</h2>
			</li>
EOT;
		}elseif($show_password_form){ //don't show form description if this page is password protected and user not authenticated
			$form_desc_div ='';	
			$all_element_markup = '';	
			$custom_element =<<<EOT
			<li>
				<h2>{$mf_lang['form_pass_title']}</h2>
				<div>
				<input type="password" value="" class="text" name="password" id="password" />
				<label for="password" class="desc">{$mf_lang['form_pass_desc']}</label>
				</div>
			</li>
EOT;
			$ul_class = 'class="password"';
		}else{
			if(!empty($form->name) || !empty($form->description)){
				$form->description = nl2br($form->description);
				$form_desc_div =<<<EOT
		<div class="form_description">
			<h2>{$form->name}</h2>
			<p>{$form->description}</p>
		</div>
EOT;
			}

			if(!empty($form->name_hide)){
				$form_desc_div = '';
			}
		}
		
		if(!$has_guidelines){
			$container_class .= " no_guidelines";
		}
		
		$html_class_tag = '';
		if($integration_method == 'iframe'){
			$html_class_tag = 'class="embed"';
		}
		
		if($has_calendar){
			
			//initialize the calendar, along with the selected form language
			switch ($form->language) {
				case 'dutch' : $datepick_lang = 'js/datepick5/jquery.datepick-nl.js';break;
				case 'french' : $datepick_lang = 'js/datepick5/jquery.datepick-fr.js';break;
				case 'german' : $datepick_lang = 'js/datepick5/jquery.datepick-de.js';break;
				case 'italian' : $datepick_lang = 'js/datepick5/jquery.datepick-it.js';break;
				case 'portuguese' : $datepick_lang = 'js/datepick5/jquery.datepick-pt.js';break;
				case 'spanish' : $datepick_lang = 'js/datepick5/jquery.datepick-es.js';break;
				case 'swedish' : $datepick_lang = 'js/datepick5/jquery.datepick-sv.js';break;
				case 'japanese' : $datepick_lang = 'js/datepick5/jquery.datepick-ja.js';break;
				case 'estonian' : $datepick_lang = 'js/datepick5/jquery.datepick-et.js';break;
				case 'russian' : $datepick_lang = 'js/datepick5/jquery.datepick-ru.js';break;
				case 'hungarian' : $datepick_lang = 'js/datepick5/jquery.datepick-hu.js';break;
				case 'chinese' : $datepick_lang = 'js/datepick5/jquery.datepick-zh-TW.js';break;
				case 'chinese_simplified' : $datepick_lang = 'js/datepick5/jquery.datepick-zh-CN.js';break;
				case 'bulgarian' : $datepick_lang = 'js/datepick5/jquery.datepick.js-bg';break;
				case 'danish' : $datepick_lang = 'js/datepick5/jquery.datepick-da.js';break;
				case 'finnish' : $datepick_lang = 'js/datepick5/jquery.datepick-fi.js';break;
				case 'polish' : $datepick_lang = 'js/datepick5/jquery.datepick-pl.js';break;
				case 'greek' : $datepick_lang = 'js/datepick5/jquery.datepick-el.js';break;
				case 'norwegian' : $datepick_lang = 'js/datepick5/jquery.datepick-no.js';break;
				case 'romanian' : $datepick_lang = 'js/datepick5/jquery.datepick-ro.js';break;
				case 'slovak' : $datepick_lang = 'js/datepick5/jquery.datepick-sk.js';break;
				case 'indonesia' : $datepick_lang = 'js/datepick5/jquery.datepick-id.js';break;
				default : $datepick_lang = ''; //the default english language
			}

			if(!empty($datepick_lang)) {
				$datepick_lang =  '<script type="text/javascript" src="'.$machform_path.$datepick_lang.'"></script>';
			}

			$calendar_init = '<script type="text/javascript" src="'.$machform_path.'js/datepick5/jquery.plugin.min.js"></script>'."\n".
							 '<script type="text/javascript" src="'.$machform_path.'js/datepick5/jquery.datepick.min.js"></script>'."\n".
							 $datepick_lang . "\n".
							 '<script type="text/javascript" src="'.$machform_path.'js/datepick5/jquery.datepick.ext.min.js"></script>'."\n".
							 '<link type="text/css" href="'.$machform_path.'js/datepick5/smoothness.datepick.css" rel="stylesheet" />';
		}else{
			$calendar_init = '';
		}

		if($has_signature_pad){
			$signature_pad_init = '<script type="text/javascript" src="'.$machform_path.'js/signature_pad/signature_pad.umd.js"></script>'."\n";
		}else{
			$signature_pad_init = '';
		}

		if($has_media_video){
			$media_video_init = '<link rel="stylesheet" href="'.$machform_path.'js/videojs/video-js.css">'."\n".
							 	'<script type="text/javascript" src="'.$machform_path.'js/videojs/video.min.js"></script>'."\n".
							 	'<script type="text/javascript" src="'.$machform_path.'js/videojs/Youtube.js"></script>'."\n";
		}else{
			$media_video_init = '';
		}

		if($has_media_pdf){
			$media_pdf_init = '<script type="text/javascript" src="'.$machform_path.'js/pdfobject.js"></script>'."\n";
		}else{
			$media_pdf_init = '';
		}

		//generate conditional logic code, if enabled and not on edit entry page
		if(!empty($form->logic_field_enable) && empty($edit_id)){
			$logic_js = mf_get_logic_javascript($dbh,$form_id,$page_number);
		}else{
			$logic_js = '';
		}

		$page_main_title = $form->name;
		
		//if the form has multiple pages
		//display the pagination header
		$pagination_header = '';
		if($form->page_total > 1 && $show_password_form === false){
			//build pagination header based on the selected type. possible values:
			//steps - display multi steps progress
			//percentage - display progress bar with percentage
			//disabled - disabled
			
			$page_breaks_data = array();
			$page_title_array = array();
			
			//get page titles
			$query = "SELECT 
							element_page_title,
							element_page_number,
							element_submit_use_image,
						    element_submit_primary_text,
							element_submit_secondary_text,
							element_submit_primary_img,
							element_submit_secondary_img 
						FROM 
							".MF_TABLE_PREFIX."form_elements
					   WHERE
							form_id = ? and element_status = 1 and element_type = 'page_break'
					ORDER BY 
					   		element_page_number asc";
			$params = array($form_id);
			
			$sth = mf_do_query($query,$params,$dbh);
			while($row = mf_do_fetch_result($sth)){
				$temp_page_number = $row['element_page_number'];
				$page_breaks_data[$temp_page_number]['use_image'] 		= $row['element_submit_use_image'];
				$page_breaks_data[$temp_page_number]['primary_text'] 	= $row['element_submit_primary_text'];
				$page_breaks_data[$temp_page_number]['secondary_text'] 	= $row['element_submit_secondary_text'];
				$page_breaks_data[$temp_page_number]['primary_img']		= $row['element_submit_primary_img'];
				$page_breaks_data[$temp_page_number]['secondary_img'] 	= $row['element_submit_secondary_img'];
				
				$page_title_array[] = $row['element_page_title'];
			}
			
			//add the last page buttons info into the array for easy lookup
			$page_breaks_data[$form->page_total]['use_image'] 		= $form->submit_use_image;
			$page_breaks_data[$form->page_total]['primary_text'] 	= $form->submit_primary_text;
			$page_breaks_data[$form->page_total]['secondary_text'] 	= $form->submit_secondary_text;
			$page_breaks_data[$form->page_total]['primary_img'] 	= $form->submit_primary_img;
			$page_breaks_data[$form->page_total]['secondary_img'] 	= $form->submit_secondary_img;
			
			$page_total = count($page_title_array) + 1;
				
			if(!empty($form->review)){
				$page_total++;
			}

			if(!empty($form->payment_enable_merchant)){
				$page_total++;
			}

			if($form->pagination_type == 'steps'){
				
				$page_titles_markup = '';
				
				$i=1;
				foreach ($page_title_array as $page_title){
					if($i == $page_number){
						$ap_tp_num_active = ' ap_tp_num_active';
						$ap_tp_text_active = ' ap_tp_text_active';
					}else{
						$ap_tp_num_active = '';
						$ap_tp_text_active = '';
					}
					
					$page_titles_markup .= '<td align="center"><span id="page_num_'.$i.'" class="ap_tp_num'.$ap_tp_num_active.'">'.$i.'</span><span id="page_title_'.$i.'" class="ap_tp_text'.$ap_tp_text_active.'">'.$page_title.'</span></td><td align="center" class="ap_tp_arrow">&gt;</td>'."\n";
					$i++;
				}
				
				//add the last page title into the pagination header markup
				if($i == $page_number){
					$ap_tp_num_active = ' ap_tp_num_active';
					$ap_tp_text_active = ' ap_tp_text_active';
				}else{
					$ap_tp_num_active = '';
					$ap_tp_text_active = '';
				}
				$page_titles_markup .= '<td align="center"><span id="page_num_'.$i.'" class="ap_tp_num'.$ap_tp_num_active.'">'.$i.'</span><span id="page_title_'.$i.'" class="ap_tp_text'.$ap_tp_text_active.'">'.$form->lastpage_title.'</span></td>';
			
				//if form review enabled, we need to add the pagination header
				if(!empty($form->review)){
					$i++;
					$page_titles_markup .= '<td align="center" class="ap_tp_arrow">&gt;</td><td align="center"><span id="page_num_'.$i.'" class="ap_tp_num">'.$i.'</span><span id="page_title_'.$i.'" class="ap_tp_text">'.$form->review_title.'</span></td>';
				}

				//if payment enabled, we need to add the pagination header
				if(!empty($form->payment_enable_merchant)){
					$i++;
					$page_titles_markup .= '<td align="center" class="ap_tp_arrow">&gt;</td><td align="center"><span id="page_num_'.$i.'" class="ap_tp_num">'.$i.'</span><span id="page_title_'.$i.'" class="ap_tp_text">'.$mf_lang['form_payment_header_title'].'</span></td>';
				}
				
				$pagination_header =<<<EOT
			<li id="pagination_header" class="li_pagination">
			 <table class="ap_table_pagination" width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr> 
			  	{$page_titles_markup}
			  </tr>
			</table>
			</li>
EOT;
			}else if($form->pagination_type == 'percentage'){
				
				$percent_value = round(($page_number/$page_total) * 100);
				
				if($percent_value == 100){ //it's not make sense to display 100% when the form is not really submitted yet
					$percent_value = 99;
				}
				
				if(!empty($form->review) && !empty($form->payment_enable_merchant)){
					if(($page_total-2) == $page_number){ //if this is last page of the form
						$current_page_title = $form->lastpage_title;
					}else{
						$current_page_title = $page_title_array[$page_number-1];
					}
				}else if(!empty($form->review) || !empty($form->payment_enable_merchant)){
					if(($page_total-1) == $page_number){ //if this is last page of the form
						$current_page_title = $form->lastpage_title;
					}else{
						$current_page_title = $page_title_array[$page_number-1];
					}
				}else{
					if($page_total == $page_number){ //if this is last page of the form
						$current_page_title = $form->lastpage_title;
					}else{
						$current_page_title = $page_title_array[$page_number-1];
					}
				}

				
				$page_number_title = sprintf($mf_lang['page_title'],$page_number,$page_total);
				$pagination_header =<<<EOT
			<li id="pagination_header" class="li_pagination">
			    <h3 id="page_title_{$page_number}">{$page_number_title} - {$current_page_title}</h3>
				<div class="mf_progress_container">          
			    	<div id="mf_progress_percentage" class="mf_progress_value" style="width: {$percent_value}%"><span>{$percent_value}%</span></div>
				</div>
			</li>
EOT;
			}else{			
				$pagination_header = '';
			}

			if(!empty($current_page_title)){
				$page_main_title = $form->name.' - '.sprintf($mf_lang['page_title'],$page_number,$page_total)." ({$current_page_title})";
			}else{
				$page_main_title = $form->name.' - '.sprintf($mf_lang['page_title'],$page_number,$page_total);
			}
			

			//build the submit buttons markup
			$button_secondary_markup = '';

			if(empty($edit_id)){
				if(empty($page_breaks_data[$page_number]['use_image'])){ //if using text buttons as submit
				
					if($page_number > 1){
						$button_secondary_markup = '<input class="button_text btn_secondary" type="submit" id="submit_secondary" name="submit_secondary" value="'.$page_breaks_data[$page_number]['secondary_text'].'" />';
					}
					
					$button_markup =<<<EOT
			<li id="li_buttons" class="buttons">
				    <input type="hidden" name="form_id" value="{$form->id}" />
				    {$mf_edit_tag}
				    {$mfsid_tag}
				    {$edit_markup}
				    {$csrf_markup}
				    <input type="hidden" name="submit_form" value="1" />
				    <input type="hidden" name="page_number" value="{$page_number}" />
					<input class="button_text btn_primary" type="submit" id="submit_primary" name="submit_primary" value="{$page_breaks_data[$page_number]['primary_text']}" />
					{$button_secondary_markup}
			</li>
EOT;
				}else{ //if using images as submit
					
					if($page_number > 1){
						$button_secondary_markup = '<input class="submit_img_secondary" type="image" alt="Previous" id="submit_secondary" name="submit_secondary" src="'.$page_breaks_data[$page_number]['secondary_img'].'" />';
					}
					
					$button_markup =<<<EOT
			<li id="li_buttons" class="buttons">
				    <input type="hidden" name="form_id" value="{$form->id}" />
				    {$mf_edit_tag}
				    {$mfsid_tag}
				    {$edit_markup}
				    {$csrf_markup}
				    <input type="hidden" name="submit_form" value="1" />
				    <input type="hidden" name="page_number" value="{$page_number}" />
				 	<input class="submit_img_primary" type="image" alt="Continue" id="submit_primary" name="submit_primary" src="{$page_breaks_data[$page_number]['primary_img']}" />
					{$button_secondary_markup}
			</li>
EOT;
					
				}
			}else{ //if there is edit_id, then this is edit_entry page, display a standard button
				$button_markup =<<<EOT
			<li id="li_buttons" class="buttons">
				    <input type="hidden" name="form_id" value="{$form->id}" />
				    {$mf_edit_tag}
				    {$mfsid_tag}
				    {$edit_markup}
				    {$csrf_markup}
				    <input type="hidden" name="submit_form" value="1" />
				    <input type="hidden" name="page_number" value="{$page_number}" />
					<input class="button_text btn_primary" type="submit" id="submit_primary" name="submit_primary" value="Save Changes" />
			</li>
EOT;
			}
			
		}
		
		$advance_uploader_js = '';
		if($has_advance_uploader){

			$mf_path_script = '';
			if(!empty($machform_path)){
				$mf_path_script =<<<EOT
<script type="text/javascript">
var __machform_path = '{$machform_path}';
</script>
EOT;
			}

			$advance_uploader_js =<<<EOT
<script type="text/javascript" src="{$machform_path}js/uploadifive/jquery.uploadifive.js"></script>
{$mf_path_script}
EOT;
		}

		if($integration_method == 'iframe'){
			$auto_height_js =<<<EOT
<script type="text/javascript" src="{$machform_path}js/jquery.ba-postmessage.min.js"></script>
<script type="text/javascript">
    $(function(){
    	$.postMessage({mf_iframe_height: $('body').outerHeight(true)}, '*', parent );
    });
</script>
EOT;
		}
		
		//if the form has resume enabled and this is multi page form (single page form doesn't have resume option)
		if(!empty($form->resume_enable) && $form->page_total > 1 && $show_password_form === false && empty($inactive_message)){
			
			if(!empty($error_elements['element_resume_email'])){
				$li_resume_email_style = '';
				$li_resume_error_message = "<p class=\"error\">{$error_elements['element_resume_email']}</p>";
				$li_resume_class = 'class="error"';
				$li_resume_checked = 'checked="checked"';
				$li_resume_button_status = 1;
			}else{
				$li_resume_email_style = 'style="display: none"';
				$li_resume_error_message = '';
				$li_resume_class = '';
				$li_resume_checked = '';
				$li_resume_button_status = 0;
			}
			
			$populated_values['element_resume_email'] = $populated_values['element_resume_email'] ?? '';
			
			$form_resume_markup = <<<EOT
			<li id="li_resume_checkbox">
			<div>
				<span><input type="checkbox" value="1" class="element checkbox" name="element_resume_checkbox" id="element_resume_checkbox" {$li_resume_checked}>
					<label for="element_resume_checkbox" class="choice">{$mf_lang['resume_checkbox_title']}</label>
				</span>
			</div> 
			</li>
			<li id="li_resume_email" {$li_resume_class} {$li_resume_email_style} data-resumebutton="{$li_resume_button_status}" data-resumelabel="{$mf_lang['resume_submit_button_text']}">
				<label for="element_resume_email" class="description">{$mf_lang['resume_email_input_label']} <span class="required">*</span></label>
				<div>
					<input type="text" value="{$populated_values['element_resume_email']}" class="element text medium" name="element_resume_email" id="element_resume_email"> 
				</div><p id="guide_resume_email" class="guidelines"><small>{$mf_lang['resume_guideline']}</small></p> {$li_resume_error_message}
			</li>
EOT;

		}
		
		//if the form has enabled merchant support and set the total payment to be displayed
		$payment_total_markup_top = '';
		$payment_total_markup_bottom = '';

		if(!empty($form->payment_enable_merchant) && !empty($form->payment_show_total)){
			
			$currency_symbol = '&#36;';
			
			switch($form->payment_currency){
				case 'USD' : $currency_symbol = '&#36;';break;
				case 'EUR' : $currency_symbol = '&#8364;';break;
				case 'GBP' : $currency_symbol = '&#163;';break;
				case 'AUD' : $currency_symbol = 'A&#36;';break;
				case 'CAD' : $currency_symbol = 'C&#36;';break;
				case 'JPY' : $currency_symbol = '&#165;';break;
				case 'THB' : $currency_symbol = '&#3647;';break;
				case 'HUF' : $currency_symbol = '&#70;&#116;';break;
				case 'CHF' : $currency_symbol = 'CHF';break;
				case 'CZK' : $currency_symbol = '&#75;&#269;';break;
				case 'SEK' : $currency_symbol = 'kr';break;
				case 'DKK' : $currency_symbol = 'kr';break;
				case 'RON' : $currency_symbol = 'L';break;
				case 'NOK' : $currency_symbol = 'kr';break;
				case 'PHP' : $currency_symbol = '&#36;';break;
				case 'IDR' : $currency_symbol = 'Rp';break;
				case 'INR' : $currency_symbol = 'Rs';break;
				case 'MYR' : $currency_symbol = 'RM';break;
				case 'ZAR' : $currency_symbol = 'R';break;
				case 'PLN' : $currency_symbol = '&#122;&#322;';break;
				case 'BRL' : $currency_symbol = 'R&#36;';break;
				case 'HKD' : $currency_symbol = 'HK&#36;';break;
				case 'MXN' : $currency_symbol = 'Mex&#36;';break;
				case 'TWD' : $currency_symbol = 'NT&#36;';break;
				case 'TRY' : $currency_symbol = 'TL';break;
				case 'NZD' : $currency_symbol = '&#36;';break;
				case 'SGD' : $currency_symbol = '&#36;';break;
			}
		
			if($form->payment_price_type == 'variable'){	
				//if this is multipage form, we need to get the total selected price from other pages
				if($form->page_total > 1){
					$other_page_total_payment = (double) mf_get_payment_total($dbh,$form_id,$session_id,$page_number);
					$other_page_total_data_tag = 'data-basetotal="'.$other_page_total_payment.'"';
				}else{
					$other_page_total_data_tag = 'data-basetotal="0"';
				}
			}elseif ($form->payment_price_type == 'fixed') {
				$other_page_total_data_tag = 'data-basetotal="'.$form->payment_price_amount.'"';
			}

			//display tax info if enabled
			if(!empty($form->enable_tax) && !empty($form->tax_rate)){
				$tax_markup = "<h5>+{$mf_lang['tax']} {$form->tax_rate}&#37;</h5>";
			}

			//display discount info if applicable
			//the discount info can only being displayed when the form is having review enabled or a multipage form
			$discount_markup = '';
			if(!empty($form->review) || $form->page_total > 1){
				$session_id = session_id();

				$is_discount_applicable = false;

				//if the discount element for the current session having any value, we can be certain that the discount code has been validated and applicable
				if(!empty($form->enable_discount)){
					$query = "select element_{$form->discount_element_id} coupon_element from ".MF_TABLE_PREFIX."form_{$form_id}_review where `session_id` = ?";
					$params = array($session_id);
					
					$sth = mf_do_query($query,$params,$dbh);
					$row = mf_do_fetch_result($sth);
					
					if(!empty($row['coupon_element'])){
						$is_discount_applicable = true;
					}
				}

				if($is_discount_applicable){
					if($form->discount_type == 'percent_off'){
						$discount_markup = "<h5>-{$mf_lang['discount']} {$form->discount_amount}&#37;</h5>";
					}else{
						$discount_markup = "<h5>-{$mf_lang['discount']} {$currency_symbol}{$form->discount_amount}</h5>";
					}
				}
			}

			$payment_extra_markup = '';
			if(!empty($tax_markup) || !empty($discount_markup)){
				$payment_extra_markup =<<<EOT
				<span class="total_extra">
					{$discount_markup}
					{$tax_markup}
				</span>
EOT;
			}
			
			$payment_total_markup = <<<EOT
			<li class="total_payment" {$other_page_total_data_tag}>
				<span class="total_main">
					<h3>{$currency_symbol}<var>0</var></h3>
					<h5>{$mf_lang['payment_total']}</h5>
				</span>
				{$payment_extra_markup}
			</li>
EOT;
			
			if(empty($form->active) || $form_has_maximum_entries || $is_edit_entry || $show_password_form){ 
				//if form is not active or this is edit_entry page or this is a password prompt page, don't show the total payment
				$payment_total_markup = '';
			}
			
			if($form->payment_total_location == 'top'){
				$payment_total_markup_top = $payment_total_markup;
			}else if($form->payment_total_location == 'bottom'){
				$payment_total_markup_bottom = $payment_total_markup;
			}else if($form->payment_total_location == 'top-bottom' || $form->payment_total_location == 'all'){
				$payment_total_markup_top 	 = $payment_total_markup;
				$payment_total_markup_bottom = $payment_total_markup;
			}
		}
		
		if(!empty($inactive_message)){
			$pagination_header = '';
			$button_markup = '';
		}

		if(empty($mf_settings['disable_machform_link'])){
			$powered_by_markup = 'Powered by <a href="https://www.machform.com" target="_blank">MachForm</a>';
		}else{
			$powered_by_markup = '';
		}

		$jquery_url = $machform_path.'js/jquery.min.js';

		//load custom javascript if enabled
		$custom_script_js = '';
		if(!empty($form->custom_script_enable) && !empty($form->custom_script_url)){
			$custom_script_js = '<script type="text/javascript" src="'.$form->custom_script_url.'"></script>';
		}

		//if there is edit key and this is edit entry, display the message and the option to open blank form
		$edit_entry_info_markup = '';
		
		if(!empty($form->entry_edit_enable) && !empty($_SESSION['mf_form_edit_key'][$form_id])){
			$edit_entry_info_markup = "<div id=\"edit_info\"><img src=\"images/icons/info-msg.png\" width=\"14\" title=\"Info\" /> {$mf_lang['entry_edit_user_info_msg']} <a href=\"view.php?id={$form_id}&reset=1\" title=\"{$mf_lang['entry_edit_user_info_btn']}\">{$mf_lang['entry_edit_user_info_btn']}</a></div>";
		}

		//if advanced form code being used, display the form without body container
		if($integration_method == 'php'){
			$container_class .= " integrated";

			if(!empty($edit_id)){
				$view_css_markup = '<link rel="stylesheet" type="text/css" href="css/edit_entry.css'.$mf_version_tag.'" media="all" />';
			}else{
				$view_css_markup = "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$machform_path}{$css_dir}view.css{$mf_version_tag}\" media=\"all\" />";
			}

			
			$jquery_effect_url	=  $machform_path.'js/jquery-ui-1.12/effect.js';
			
			//at this moment, we're using the old jquery library when editing an entry
			//using newer jquery will break lot of things --------
			if($is_edit_entry){
				$jquery_url    = $machform_path.'js/jquery.legacy.min.js';
				$jquery_effect_url	=  $machform_path.'js/jquery-ui/ui/jquery.effects.core.js';
			}
			//--------------------------
			
			$form_markup = <<<EOT
{$view_css_markup}
<link rel="stylesheet" type="text/css" href="{$machform_path}view.mobile.css{$mf_version_tag}" media="all" />
{$theme_css_link}
{$font_css_markup}
<style>
html{
	background: none repeat scroll 0 0 transparent;
	background-color: transparent;
}
</style>
<script type="text/javascript" src="{$jquery_url}"></script>
<script type="text/javascript" src="{$jquery_effect_url}"></script>
<script type="text/javascript" src="{$machform_path}view.js"></script>
{$recaptcha2_header}
{$advance_uploader_js}
{$calendar_init}
{$signature_pad_init}
{$media_video_init}
{$media_pdf_init}
{$logic_js}
{$custom_script_js}
<div id="main_body" class="{$container_class}">
	{$edit_entry_info_markup}
	<div id="form_container">
	
		<h1><a>{$form->name}</a></h1>
		<form id="form_{$form->id}" class="appnitro {$form->label_alignment}" {$form_enc_type} method="post" data-highlightcolor="{$field_highlight_color}" action="#main_body">
			{$form_desc_div}						
			<ul {$ul_class}>
			{$pagination_header}
			{$payment_total_markup_top}
			{$form->error_message}
			{$all_element_markup}
			{$custom_element}
			{$payment_total_markup_bottom}
			{$form_resume_markup}
			{$button_markup}
			</ul>
		</form>	
		<div id="footer">
			{$powered_by_markup}
		</div>
	</div>	
</div>

EOT;
		}else{
			$self_address = htmlentities($_SERVER['PHP_SELF']); //prevent XSS
			$form_markup = <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xml:lang="en" {$html_class_tag} xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$page_main_title}</title>
<link rel="stylesheet" type="text/css" href="{$machform_path}{$css_dir}view.css{$mf_version_tag}" media="all" />
<link rel="stylesheet" type="text/css" href="{$machform_path}view.mobile.css{$mf_version_tag}" media="all" />
{$theme_css_link}
{$font_css_markup}
<script type="text/javascript" src="{$jquery_url}{$mf_version_tag}"></script>
<script type="text/javascript" src="{$machform_path}js/jquery-ui-1.12/effect.js{$mf_version_tag}"></script>
<script type="text/javascript" src="{$machform_path}view.js{$mf_version_tag}"></script>
{$recaptcha2_header}
{$advance_uploader_js}
{$calendar_init}
{$signature_pad_init}
{$media_video_init}
{$media_pdf_init}
{$logic_js}
{$auto_height_js}
{$custom_script_js}
</head>
<body id="main_body" class="{$container_class}">
	{$edit_entry_info_markup}
	<div id="form_container" class="{$form_container_class}">
		<h1><a>{$form->name}</a></h1>
		<form id="form_{$form->id}" class="appnitro {$form->label_alignment}" {$form_enc_type} method="post" data-highlightcolor="{$field_highlight_color}" action="{$self_address}">
			{$form_desc_div}						
			<ul {$ul_class}>
			{$pagination_header}
			{$payment_total_markup_top}
			{$form->error_message}
			{$all_element_markup}
			{$custom_element}
			{$payment_total_markup_bottom}
			{$form_resume_markup}
			{$button_markup}
			</ul>
		</form>	
		<div id="footer">
			{$powered_by_markup}
		</div>
	</div>
	
	</body>
</html>
EOT;
		}

		return $form_markup;
		
	}
	
	
	//display the form within the form builder page
	function mf_display_raw_form($dbh,$form_id){
		
		global $mf_lang;
		
		
		//get form properties data
		$query 	= "select 
						 form_name,
						 form_description,
						 form_label_alignment,
						 form_language,
						 form_page_total,
						 form_lastpage_title,
						 form_submit_primary_text,
						 form_submit_secondary_text,
						 form_submit_primary_img,
						 form_submit_secondary_img,
						 form_submit_use_image,
						 form_pagination_type 
				     from 
				     	 ".MF_TABLE_PREFIX."forms 
				    where 
				    	 form_id = ?";
		$params = array($form_id);
	
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		mf_set_language($row['form_language']);

		$form = new stdClass();
		
		$form->id 				= $form_id;
		$form->name 			= $row['form_name'];
		$form->description 		= $row['form_description'];
		$form->label_alignment 	= $row['form_label_alignment'];
		$form->page_total 		= $row['form_page_total'];
		$form->lastpage_title 	= $row['form_lastpage_title'];
		$form->submit_primary_text 	 = $row['form_submit_primary_text'];
		$form->submit_secondary_text = $row['form_submit_secondary_text'];
		$form->submit_primary_img 	 = $row['form_submit_primary_img'];
		$form->submit_secondary_img  = $row['form_submit_secondary_img'];
		$form->submit_use_image  	 = (int) $row['form_submit_use_image'];
		$form->pagination_type		 = $row['form_pagination_type'];
		
		$matrix_elements = array();
		
		//get elements data
		//get element options first and store it into array
		$query = "select 
						element_id,
						option_id,
						`position`,
						`option`,
						option_is_default,
						option_is_hidden 
				    from 
				    	".MF_TABLE_PREFIX."element_options 
				   where 
				   		form_id = ? and live = 1 
				order by 
						element_id asc,`position` asc";
		$params = array($form_id);
		$sth = mf_do_query($query,$params,$dbh);
		
		while($row = mf_do_fetch_result($sth)){
			$element_id = $row['element_id'];
			$option_id  = $row['option_id'];
			$options_lookup[$element_id][$option_id]['position'] 		  = $row['position'];
			$options_lookup[$element_id][$option_id]['option'] 			  = $row['option'];
			$options_lookup[$element_id][$option_id]['option_is_default'] = $row['option_is_default'];
			$options_lookup[$element_id][$option_id]['option_is_hidden']  = $row['option_is_hidden'];
		}
	
		
		//get elements data
		$element = array();
		$query = "select 
						element_id,
						element_title,
						element_guidelines,
						element_size,
						element_is_required,
						element_is_readonly,
						element_is_unique,
						element_is_private,
						element_type,
						element_position,
						element_default_value,
						element_enable_placeholder,
						element_constraint,
						element_choice_has_other,
						element_choice_other_label,
						element_choice_columns,
						element_time_showsecond, 
						element_time_24hour,
						element_address_us_only,
						element_date_enable_range,
						element_date_range_min,
						element_date_range_max,
						element_date_enable_selection_limit,
						element_date_selection_max,
						element_date_disable_past_future,
						element_date_past_future,
						element_date_disable_dayofweek,
						element_date_disabled_dayofweek_list,
						element_date_disable_specific,
						element_date_disabled_list,
						element_file_type_list,
						element_file_as_attachment,
						element_file_auto_upload,
						element_file_enable_multi_upload,
						element_file_max_selection,
						element_file_enable_size_limit,
						element_file_size_max,
						element_submit_use_image,
						element_submit_primary_text,
						element_submit_secondary_text,
						element_submit_primary_img,
						element_submit_secondary_img,
						element_page_title,
						element_page_number,
						element_matrix_allow_multiselect,
						element_matrix_parent_id,
						element_range_min,
						element_range_max,
						element_range_limit_by,
						element_section_display_in_email,
						element_section_enable_scroll,
						element_text_default_type,
						element_text_default_length,
						element_text_default_random_type,
						element_text_default_prefix,
						element_text_default_case,
						element_email_enable_confirmation,
						element_email_confirm_field_label,
						element_media_type,
						ifnull(element_media_image_src,'') as element_media_image_src,
						ifnull(element_media_image_width,'') as element_media_image_width,
						ifnull(element_media_image_height,'') as element_media_image_height,
						ifnull(element_media_image_alignment,'') as element_media_image_alignment,
						ifnull(element_media_image_alt,'') as element_media_image_alt,
						ifnull(element_media_image_href,'') as element_media_image_href,
						element_media_display_in_email,
						ifnull(element_media_video_src,'') as element_media_video_src,
						ifnull(element_media_video_size,'') as element_media_video_size,
						element_media_video_muted,
						ifnull(element_media_video_caption_file,'') element_media_video_caption_file,
						ifnull(element_media_pdf_src,'') element_media_pdf_src,
						ifnull(element_rating_style,'') element_rating_style,
						element_rating_max,
						element_rating_default,
						element_rating_enable_label,
						ifnull(element_rating_label_high,'') element_rating_label_high,
						ifnull(element_rating_label_low,'') element_rating_label_low,
						ifnull(element_address_subfields_labels,'') element_address_subfields_labels,
						ifnull(element_address_subfields_visibility,'') element_address_subfields_visibility,  
						ifnull(element_address_default_state,'') element_address_default_state 
					from 
						".MF_TABLE_PREFIX."form_elements 
				   where 
				   		form_id = ? and element_status='1'
				order by 
						element_position asc";
		$params = array($form_id);
	
		$sth = mf_do_query($query,$params,$dbh);
		
		$j=0;
		$has_calendar = false;
		$has_guidelines = false;
		$ul_class = '';
		$pagination_header = '';
		$pagination_footer = '';

		$page_title_array = array();
		
		while($row = mf_do_fetch_result($sth)){
			$element_id = $row['element_id'];
			
			//lookup element options first
			$element_options = array();
			if(!empty($options_lookup[$element_id])){
				
				$i=0;
				foreach ($options_lookup[$element_id] as $option_id=>$data){
					$element_options[$i] = new stdClass();
					$element_options[$i]->id 		 = $option_id;
					$element_options[$i]->option 	 = $data['option'];
					$element_options[$i]->is_default = $data['option_is_default'];
					$element_options[$i]->is_hidden  = $data['option_is_hidden'];
					$element_options[$i]->is_db_live = 1;
					
					$i++;
				}
			}
			
			//populate elements
			$element[$j] = new stdClass();
			$element[$j]->title 		= nl2br($row['element_title']);
			$element[$j]->guidelines 	= nl2br($row['element_guidelines']);
			
			if(!empty($row['element_guidelines']) && ($row['element_is_private'] == 0) && !in_array($row['element_type'], array('section','media','matrix','signature'))){
				$has_guidelines = true;
			}

			$element[$j]->size 			= $row['element_size'];
			$element[$j]->default_value = htmlspecialchars($row['element_default_value']);
			$element[$j]->is_required 	= $row['element_is_required'];
			$element[$j]->is_readonly 	= $row['element_is_readonly'];
			$element[$j]->is_unique 	= $row['element_is_unique'];
			$element[$j]->is_private 	= $row['element_is_private'];
			$element[$j]->type 			= $row['element_type'];
			$element[$j]->position 		= $row['element_position'];
			$element[$j]->id 			= $row['element_id'];
			$element[$j]->is_db_live 	 = 1;
			$element[$j]->is_design_mode = true;
			$element[$j]->enable_placeholder = (int) $row['element_enable_placeholder'];
			$element[$j]->choice_has_other   = (int) $row['element_choice_has_other'];
			$element[$j]->choice_other_label = $row['element_choice_other_label'];
			$element[$j]->choice_columns   	 = (int) $row['element_choice_columns'];
			$element[$j]->time_showsecond	 = (int) $row['element_time_showsecond'];
			$element[$j]->time_24hour	 	 = (int) $row['element_time_24hour'];
			$element[$j]->address_us_only	 = (int) $row['element_address_us_only'];
			$element[$j]->date_enable_range	 = (int) $row['element_date_enable_range'];
			$element[$j]->date_range_min	 = $row['element_date_range_min'];
			$element[$j]->date_range_max	 = $row['element_date_range_max'];
			$element[$j]->date_enable_selection_limit	= (int) $row['element_date_enable_selection_limit'];
			$element[$j]->date_selection_max	 		= (int) $row['element_date_selection_max'];
			$element[$j]->date_disable_past_future	 	= (int) $row['element_date_disable_past_future'];
			$element[$j]->date_past_future	 			= $row['element_date_past_future'];
			$element[$j]->date_disable_dayofweek	 	= (int) $row['element_date_disable_dayofweek'];
			$element[$j]->date_disabled_dayofweek_list	= $row['element_date_disabled_dayofweek_list'];
			$element[$j]->date_disable_specific	 		= (int) $row['element_date_disable_specific'];
			$element[$j]->date_disabled_list	 		= $row['element_date_disabled_list'];
			$element[$j]->file_type_list	 			= $row['element_file_type_list'];
			$element[$j]->file_as_attachment	 		= (int) $row['element_file_as_attachment'];	
			$element[$j]->file_auto_upload	 			= (int) $row['element_file_auto_upload'];
			$element[$j]->file_enable_multi_upload	 	= (int) $row['element_file_enable_multi_upload'];
			$element[$j]->file_max_selection	 		= (int) $row['element_file_max_selection'];
			$element[$j]->file_enable_size_limit	 	= (int) $row['element_file_enable_size_limit'];
			$element[$j]->file_size_max	 				= (int) $row['element_file_size_max'];
			$element[$j]->submit_use_image	 			= (int) $row['element_submit_use_image'];
			$element[$j]->submit_primary_text	 		= $row['element_submit_primary_text'];
			$element[$j]->submit_secondary_text	 		= $row['element_submit_secondary_text'];
			$element[$j]->submit_primary_img	 		= $row['element_submit_primary_img'];
			$element[$j]->submit_secondary_img	 		= $row['element_submit_secondary_img'];
			$element[$j]->page_title	 				= $row['element_page_title'];
			$element[$j]->page_number	 				= (int) $row['element_page_number'];
			$element[$j]->page_total	 				= $form->page_total;
			$element[$j]->matrix_allow_multiselect	 	= (int) $row['element_matrix_allow_multiselect'];
			$element[$j]->matrix_parent_id	 			= (int) $row['element_matrix_parent_id'];
			$element[$j]->range_min	 					= $row['element_range_min'];
			$element[$j]->range_max	 					= $row['element_range_max'];
			$element[$j]->range_limit_by	 			= $row['element_range_limit_by'];
			$element[$j]->section_display_in_email	 	= (int) $row['element_section_display_in_email'];
			$element[$j]->section_enable_scroll	 		= (int) $row['element_section_enable_scroll'];
			$element[$j]->text_default_type	 			= $row['element_text_default_type'];
			$element[$j]->text_default_length	 		= (int) $row['element_text_default_length'];
			$element[$j]->text_default_random_type	 	= $row['element_text_default_random_type'];
			$element[$j]->text_default_prefix	 		= $row['element_text_default_prefix'];
			$element[$j]->text_default_case	 			= $row['element_text_default_case'];
			$element[$j]->email_enable_confirmation	 	= (int) $row['element_email_enable_confirmation'];
			$element[$j]->email_confirm_field_label	 	= $row['element_email_confirm_field_label'];
			$element[$j]->media_type	 				= $row['element_media_type'];
			$element[$j]->media_image_src	 			= trim($row['element_media_image_src']);
			$element[$j]->media_image_width	 			= trim($row['element_media_image_width']);
			$element[$j]->media_image_height	 		= trim($row['element_media_image_height']);
			$element[$j]->media_image_alignment	 		= trim($row['element_media_image_alignment']);
			$element[$j]->media_image_alt	 			= trim($row['element_media_image_alt']);
			$element[$j]->media_image_href	 			= trim($row['element_media_image_href']);
			$element[$j]->media_display_in_email		= (int) $row['element_media_display_in_email'];
			$element[$j]->media_video_src	 			= trim($row['element_media_video_src']);
			$element[$j]->media_video_size	 			= trim($row['element_media_video_size']);
			$element[$j]->media_video_muted	 			= (int) $row['element_media_video_muted'];
			$element[$j]->media_video_caption_file	 	= trim($row['element_media_video_caption_file']);
			$element[$j]->media_pdf_src	 				= trim($row['element_media_pdf_src']);
			$element[$j]->rating_max	 				= (int) $row['element_rating_max'];
			$element[$j]->rating_enable_label	 		= (int) $row['element_rating_enable_label'];
			$element[$j]->rating_default	 			= (int) $row['element_rating_default'];
			$element[$j]->rating_style	 				= trim($row['element_rating_style']);
			$element[$j]->rating_label_high	 			= trim($row['element_rating_label_high']);
			$element[$j]->rating_label_low	 			= trim($row['element_rating_label_low']);
			$element[$j]->address_subfields_labels	 	= trim($row['element_address_subfields_labels']);
			$element[$j]->address_subfields_visibility	= trim($row['element_address_subfields_visibility']);
			$element[$j]->address_default_state			= trim($row['element_address_default_state']);

			$element[$j]->constraint 	= $row['element_constraint'];
			if(!empty($element_options)){
				$element[$j]->options 	= $element_options;
			}else{
				$element[$j]->options 	= '';
			}
			
			if($row['element_type'] == 'page_break'){
				$page_title_array[] = $row['element_page_title'];
			}
			
			//if the element is a matrix field and not the parent, store the data into a lookup array for later use when rendering the markup
			if($row['element_type'] == 'matrix' && !empty($row['element_matrix_parent_id'])){
				
				$parent_id 	 = $row['element_matrix_parent_id'];
				$el_position = $row['element_position'];
				$matrix_elements[$parent_id][$el_position]['title'] = $element[$j]->title; 
				$matrix_elements[$parent_id][$el_position]['id'] 	= $element[$j]->id; 
				
				$matrix_child_option_id = '';
				foreach($element_options as $value){
					$matrix_child_option_id .= $value->id.',';
				}
				$matrix_child_option_id = rtrim($matrix_child_option_id,',');
				$matrix_elements[$parent_id][$el_position]['children_option_id'] = $matrix_child_option_id; 
				
				//remove it from the main element array
				$element[$j] = array();
				unset($element[$j]);
				$j--;
			}
			
			$j++;
		}

		
		
		
		//generate html markup for each element
		$all_element_markup = '';
		foreach ($element as $element_data){
			//if this is matrix field, build the children data from $matrix_elements array
			if($element_data->type == 'matrix'){
				$element_data->matrix_children = $matrix_elements[$element_data->id];
			}
			$all_element_markup .= call_user_func('mf_display_'.$element_data->type,$element_data);
		}
		
		if(empty($all_element_markup)){
			$all_element_markup = '<li id="li_dummy">&nbsp;</li>';
		}	
				
				

		if(!empty($form->name) || !empty($form->description)){
			$form->description = nl2br($form->description);
			$form_desc_div =<<<EOT
		<div id="form_header" class="form_description">
			<h2 id="form_header_title">{$form->name}</h2>
			<p id="form_header_desc">{$form->description}</p>
		</div>
EOT;
		}else{
			$form_desc_div =<<<EOT
		<div id="form_header" class="form_description">
			<h2 id="form_header_title"><i>This form has no title</i></h2>
			<p id="form_header_desc"></p>
		</div>
EOT;
		}

		if($has_guidelines){
			$container_class = "integrated";
		}else{
			$container_class = "integrated no_guidelines";
		}
		
		
		//if the form has multiple pages
		//display the pagination header
		if($form->page_total > 1){
			
			
			//build pagination header based on the selected type. possible values:
			//steps - display multi steps progress
			//percentage - display progress bar with percentage
			//disabled - disabled
			
			if($form->pagination_type == 'steps'){
				
				$page_titles_markup = '';
				
				$i=1;
				foreach ($page_title_array as $page_title){
					if($i==1){
						$ap_tp_num_active = ' ap_tp_num_active';
						$ap_tp_text_active = ' ap_tp_text_active';
					}else{
						$ap_tp_num_active = '';
						$ap_tp_text_active = '';
					}
					
					$page_titles_markup .= '<td align="center"><span id="page_num_'.$i.'" class="ap_tp_num'.$ap_tp_num_active.'">'.$i.'</span><span id="page_title_'.$i.'" class="ap_tp_text'.$ap_tp_text_active.'">'.$page_title.'</span></td><td align="center" class="ap_tp_arrow">&gt;</td>'."\n";
					$i++;
				}
				
				//add the last page title into the pagination header markup
				$page_titles_markup .= '<td align="center"><span id="page_num_'.$i.'" class="ap_tp_num">'.$i.'</span><span id="page_title_'.$i.'" class="ap_tp_text">'.$form->lastpage_title.'</span></td>';
				
			
				$pagination_header =<<<EOT
			<li id="pagination_header" class="li_pagination" title="Click to edit">
			 <table class="ap_table_pagination" width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr> 
			  	{$page_titles_markup}
			  </tr>
			</table>
			</li>
EOT;
			}else if($form->pagination_type == 'percentage'){
				$page_total = count($page_title_array) + 1;
				$percent_value = round((1/$page_total) * 100);
				$pagination_header =<<<EOT
			<li id="pagination_header" class="li_pagination" title="Click to edit">
			    <h3 id="page_title_1">Page 1 of {$page_total} - {$page_title_array[0]}</h3>
				<div class="mf_progress_container">          
			    	<div id="mf_progress_percentage" class="mf_progress_value" style="width: {$percent_value}%"><span>{$percent_value}%</span></div>
				</div>
			</li>
EOT;
			}else{
			
				$pagination_header =<<<EOT
			<li id="pagination_header" class="li_pagination" title="Click to edit">
			    <h3 class="no_header">Pagination Header Disabled</h3>
			</li>
EOT;
			}

			if($form->submit_use_image == 1){
				$btn_class = ' hide';
				$image_class = '';
			}else{
				$btn_class = '';
				$image_class = ' hide';
			}  	
			
			if(empty($form->submit_primary_img)){
				$form->submit_primary_img = 'images/empty.gif';
			}
			
			if(empty($form->submit_secondary_img)){
				$form->submit_secondary_img = 'images/empty.gif';
			}

			$pagination_footer =<<<EOT
		<li title="Click to edit" class="page_break synched" id="li_lastpage">
			<div>
				<table width="100%" cellspacing="0" cellpadding="0" border="0" class="ap_table_pagination">
					<tbody><tr>
						<td align="left" style="vertical-align: bottom;">
							<input type="submit" class="btn_primary btn_submit{$btn_class}" name="btn_submit_lastpage" id="btn_submit_lastpage" value="{$form->submit_primary_text}" disabled="disabled">
							<input type="submit" class="btn_secondary btn_submit{$btn_class}" name="btn_prev_lastpage" id="btn_prev_lastpage" value="{$form->submit_secondary_text}" disabled="disabled">
							<input type="image" src="{$form->submit_primary_img}" class="img_primary img_submit{$image_class}" alt="Submit" name="img_submit_lastpage" id="img_submit_lastpage" value="Submit" disabled="disabled">
							<input type="image" src="{$form->submit_secondary_img}" class="img_secondary img_submit{$image_class}" alt="Previous" name="img_prev_lastpage" id="img_prev_lastpage" value="Previous" disabled="disabled">
						</td> 
						<td width="75px" align="center" style="vertical-align: top;">
							<span class="ap_tp_num" name="pagenum_lastpage" id="pagenum_lastpage">{$form->page_total}</span>
							<span class="ap_tp_text" name="pagetotal_lastpage" id="pagetotal_lastpage">Page {$form->page_total} of {$form->page_total}</span>
						</td>
					</tr>
				</tbody></table>
			</div>
		</li>
EOT;
		}

			
		$form_markup =<<<EOT
<div id="main_body" class="{$container_class}">
		
	<div id="form_container">
	
		<h1><a>{$form->name}</a></h1>
		<form id="form_builder_preview" class="appnitro {$form->label_alignment}" method="post" action="#main_body">
			{$form_desc_div}				
			<ul {$ul_class} id="form_builder_sortable" title="Click field to edit. Drag to reorder.">
			{$pagination_header}	
			{$all_element_markup}
			{$pagination_footer}
			</ul>
		</form>	
	</div>
</div>
EOT;
		return $form_markup;
		
	}
	
	function mf_display_success($dbh,$form_id,$form_params=array()){
		global $mf_lang;
		
		if(!empty($form_params['integration_method'])){
			$integration_method = $form_params['integration_method'];
		}else{
			$integration_method = '';
		}

		if(!empty($form_params['machform_path'])){
			$machform_path = $form_params['machform_path'];
		}else{
			$machform_path = '';
		}

		$mf_settings = mf_get_settings($dbh);
		$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);

		$theme_css_link = '';
		$font_css_markup = '';
		$auto_height_js = '';

		//get form properties data
		$query 	= "select 
						  form_success_message,
						  form_has_css,
						  form_name,
						  form_theme_id,
						  form_language,
						  form_custom_script_enable,
					 	  form_custom_script_url,
					 	  logic_success_enable,
					 	  form_entry_edit_enable,
					 	  form_entry_edit_hide_editlink      
				     from 
				     	 ".MF_TABLE_PREFIX."forms 
				    where 
				    	 form_id=?";
		$params = array($form_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
	
		$form = new stdClass();
		
		$form->id 				= $form_id;
		$form->success_message  = $row['form_success_message'];
		$form->logic_success_enable  	= (int) $row['logic_success_enable'];
		$form->entry_edit_enable  	 	= (int) $row['form_entry_edit_enable'];
		$form->entry_edit_hide_editlink = (int) $row['form_entry_edit_hide_editlink'];
		$form->has_css 			= $row['form_has_css'];
		$form->name 			= $row['form_name'];
		$form->theme_id 		= $row['form_theme_id'];
		$form->language 		= trim($row['form_language']);

		$form->custom_script_enable = (int) $row['form_custom_script_enable'];
		$form->custom_script_url 	= $row['form_custom_script_url'];
		
		if(!empty($form->language)){
			mf_set_language($form->language);
		}

		//check for success page logic, if enabled get the correct success message and override the default
		//make sure this not being executed on resume page's success
		if(!empty($form->logic_success_enable) && !empty($_SESSION['mf_success_entry_id']) && empty($_SESSION['mf_form_resume_url'][$form_id])){
			//get all the rules from ap_success_logic_options table that has success_type = message
	    	$query = "SELECT 
							rule_id,
							rule_all_any,
							success_message  
						FROM 
							".MF_TABLE_PREFIX."success_logic_options 
					   WHERE 
							form_id = ? and success_type = 'message' 
					ORDER BY 
							rule_id asc";
			$params = array($form_id);
			$sth = mf_do_query($query,$params,$dbh);
					
			$success_logic_array = array();
			$i = 0;
			while($row = mf_do_fetch_result($sth)){
				$success_logic_array[$i]['rule_id'] 	   		= $row['rule_id'];
				$success_logic_array[$i]['rule_all_any'] 		= $row['rule_all_any'];
				$success_logic_array[$i]['success_message'] 	= $row['success_message'];
				$i++;
			}

			//evaluate the condition for each rule
			//if the condition true, get the success message
			if(!empty($success_logic_array)){

				foreach ($success_logic_array as $value) {
					$target_rule_id = $value['rule_id'];
					$rule_all_any 	= $value['rule_all_any'];
					
					$success_message = $value['success_message'];

					$current_rule_conditions_status = array();

					$query = "SELECT 
									element_name,
									rule_condition,
									rule_keyword 
								FROM 
									".MF_TABLE_PREFIX."success_logic_conditions 
							   WHERE 
							   		form_id = ? AND target_rule_id = ?";
					$params = array($form_id,$target_rule_id);
					
					$sth = mf_do_query($query,$params,$dbh);
					while($row = mf_do_fetch_result($sth)){
						
						$condition_params = array();
						$condition_params['form_id']		= $form_id;
						$condition_params['element_name'] 	= $row['element_name'];
						$condition_params['rule_condition'] = $row['rule_condition'];
						$condition_params['rule_keyword'] 	= $row['rule_keyword'];
						$condition_params['use_main_table'] = true;
						$condition_params['entry_id'] 		= $_SESSION['mf_success_entry_id'];  
						
						$current_rule_conditions_status[] = mf_get_condition_status_from_table($dbh,$condition_params);
					}
					
					if($rule_all_any == 'all'){
						if(in_array(false, $current_rule_conditions_status)){
							$all_conditions_status = false;
						}else{
							$all_conditions_status = true;
						}
					}else if($rule_all_any == 'any'){
						if(in_array(true, $current_rule_conditions_status)){
							$all_conditions_status = true;
						}else{
							$all_conditions_status = false;
						}
					}

					if($all_conditions_status === true){
						$form->success_message = $success_message;
						break;
					}
				} //end foreach $success_logic_array
			} //end !empty success_logic_array

		} //end logic_success_enable

		//parse success messages with template variables
		if(!empty($_SESSION['mf_success_entry_id']) && empty($_SESSION['mf_form_resume_url'][$form_id])){
			
			//if "allow user to edit completed entry" enabled
			//automatically add {edit_link} at the end of the success message or not based on user preference
			if(!empty($form->entry_edit_enable)){
				if(empty($form->entry_edit_hide_editlink)){
					$form->success_message .= "<br/><br/>{edit_link}";
				}
			}

			$entry_id = $_SESSION['mf_success_entry_id'];

			$template_data_options = array();
			$template_data_options['strip_download_link']  = false; 
		    $template_data_options['as_plain_text']		   = false;
		    $template_data_options['target_is_admin'] 	   = true;
			$template_data_options['machform_path'] 	   = $mf_settings['base_url'];
				
			$template_data = mf_get_template_variables($dbh,$form_id,$entry_id,$template_data_options);
				
			$template_variables = $template_data['variables'];
			$template_values    = $template_data['values'];

			//parse the form success message with the template variables
			$form->success_message = str_replace($template_variables,$template_values,$form->success_message);
		}
		
		//check for specific form css, if any, use it instead
		if($form->has_css){
			$css_dir = $mf_settings['data_dir']."/form_{$form_id}/css/";
		}
		
		//if this form is using custom theme
		if(!empty($form->theme_id)){
			//get the field highlight color for the particular theme
			$query = "SELECT 
							highlight_bg_type,
							highlight_bg_color,
							form_shadow_style,
							form_shadow_size,
							form_shadow_brightness,
							form_button_type,
							form_button_text,
							form_button_image,
							theme_has_css  
						FROM 
							".MF_TABLE_PREFIX."form_themes 
					   WHERE 
					   		theme_id = ?";
			$params = array($form->theme_id);
			
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);
			
			$form_shadow_style 		= $row['form_shadow_style'];
			$form_shadow_size 		= $row['form_shadow_size'];
			$form_shadow_brightness = $row['form_shadow_brightness'];
			$theme_has_css = (int) $row['theme_has_css'];
			
			//if the theme has css file, make sure to refer to that file
			//otherwise, generate the css dynamically
			
			if(!empty($theme_has_css)){
				$theme_css_link = '<link rel="stylesheet" type="text/css" href="'.$machform_path.$mf_settings['data_dir'].'/themes/theme_'.$form->theme_id.'.css" media="all" />';
			}else{
				$theme_css_link = '<link rel="stylesheet" type="text/css" href="'.$machform_path.'css_theme.php?theme_id='.$form->theme_id.'" media="all" />';
			}
			
			if($row['highlight_bg_type'] == 'color'){
				$field_highlight_color = $row['highlight_bg_color'];
			}else{ 
				//if the field highlight is using pattern instead of color, set the color to empty string
				$field_highlight_color = ''; 
			}
			
			//get the css link for the fonts
			$font_css_markup = mf_theme_get_fonts_link($dbh,$form->theme_id);
			
			//get the form shadow classes
			if(!empty($form_shadow_style) && ($form_shadow_style != 'disabled')){
				preg_match_all("/[A-Z]/",$form_shadow_style,$prefix_matches);
				//this regex simply get the capital characters of the shadow style name
				//example: RightPerspectiveShadow result to RPS and then being sliced to RP
				$form_shadow_prefix_code = substr(implode("",$prefix_matches[0]),0,-1);
				
				$form_shadow_size_class  = $form_shadow_prefix_code.ucfirst($form_shadow_size);
				$form_shadow_brightness_class = $form_shadow_prefix_code.ucfirst($form_shadow_brightness);

				if(empty($integration_method)){ //only display shadow if the form is not being embedded using any method
					$form_container_class = $form_shadow_style.' '.$form_shadow_size_class.' '.$form_shadow_brightness_class;
				}
			}
			
			
			
		}else{ //if the form doesn't have any theme being applied
			$field_highlight_color = '#FFF7C0';
			
			if(empty($integration_method)){
				$form_container_class = ''; //default shadow as of v4.2 is no-shadow
			}else{
				$form_container_class = ''; //dont show any shadow when the form being embedded
			}
		}
		
		
		if(!empty($_SESSION['mf_form_resume_url'][$form_id])){
			
			$resume_success_title = $mf_lang['resume_success_title'];
			$resume_url_tag		  = "<a href=\"{$_SESSION['mf_form_resume_url'][$form_id]}\">{$_SESSION['mf_form_resume_url'][$form_id]}</a>";
			
			$resume_success_content = sprintf($mf_lang['resume_success_content'],$resume_url_tag);

			$success_markup = <<<EOT
			<h2>{$resume_success_title}</h2>
			<h3>{$resume_success_content}</h3>
EOT;
			unset($_SESSION['mf_form_resume_url'][$form_id]);
		}else{
			$success_markup = "<h2>{$form->success_message}</h2>";		
		}

		if(empty($mf_settings['disable_machform_link'])){
			$powered_by_markup = 'Powered by <a href="https://www.machform.com" target="_blank">MachForm</a>';
		}else{
			$powered_by_markup = '';
		}
		
		//load custom javascript if enabled
		$custom_script_js = '';
		$embed_class = '';

		if(!empty($form->custom_script_enable) && !empty($form->custom_script_url)){
			$custom_script_js = '<script type="text/javascript" src="'.$form->custom_script_url.'"></script>';
		}

		$jquery_url = $machform_path.'js/jquery.min.js';

		if($integration_method == 'php'){
			$form_markup = <<<EOT
<link rel="stylesheet" type="text/css" href="{$machform_path}{$css_dir}view.css{$mf_version_tag}" media="all" />
<link rel="stylesheet" type="text/css" href="{$machform_path}view.mobile.css{$mf_version_tag}" media="all" />
{$theme_css_link}
{$font_css_markup}
{$custom_script_js}
<style>
html{
	background: none repeat scroll 0 0 transparent;
}
</style>

<div id="main_body" class="integrated">
	<div id="form_container">
		<h1><a>Appnitro MachForm</a></h1>
		<div class="form_success">
			{$success_markup}
		</div>
		<div id="footer" class="success">
			{$powered_by_markup}
		</div>		
	</div>
	
</div>
EOT;

		}else{
	
			if($integration_method == 'iframe'){
				$embed_class = 'class="embed"';
				$auto_height_js =<<<EOT
<script type="text/javascript" src="{$jquery_url}"></script>
<script type="text/javascript" src="{$machform_path}js/jquery.ba-postmessage.min.js"></script>
<script type="text/javascript">
    $(function(){
    	$.postMessage({mf_iframe_height: $('body').outerHeight(true)}, '*', parent );
    });
</script>
EOT;
			}else{
				$embed_class = '';
				if(!empty($custom_script_js)){
					$custom_script_js = '<script type="text/javascript" src="'.$jquery_url.'"></script>'."\n".$custom_script_js;	
				}
			}
			
			$form_markup = <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xml:lang="en" {$embed_class} xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$form->name}</title>
<link rel="stylesheet" type="text/css" href="{$machform_path}{$css_dir}view.css{$mf_version_tag}" media="all" />
<link rel="stylesheet" type="text/css" href="{$machform_path}view.mobile.css{$mf_version_tag}" media="all" />
{$theme_css_link}
{$font_css_markup}
{$auto_height_js}
{$custom_script_js}
</head>
<body id="main_body">
	<div id="form_container" class="{$form_container_class}">
	
		<h1><a>Appnitro MachForm</a></h1>
			
		<div class="form_success">
			{$success_markup}
		</div>
		<div id="footer" class="success">
			{$powered_by_markup}
		</div>		
	</div>
</body>
</html>
EOT;
		}

		return $form_markup;
	}
	
	
	
	//display form confirmation page
	function mf_display_form_review($dbh,$form_id,$record_id,$from_page_num,$form_params=array()){
		global $mf_lang;

		if(empty($_COOKIE['mf_has_cookie'])){
			$mfsid = session_id();
			$mfsid_tag = '<input type="hidden" id="mfsid" name="mfsid" value="'.$mfsid.'" />';
		}
		
		if(!empty($form_params['integration_method'])){
			$integration_method = $form_params['integration_method'];
		}else{
			$integration_method = '';
		}

		if(!empty($form_params['machform_path'])){
			$machform_path = $form_params['machform_path'];
		}else{
			$machform_path = '';
		}

		if(!empty($form_params['machform_data_path'])){
			$machform_data_path = $form_params['machform_data_path'];
		}else{
			$machform_data_path = '';
		}

		$mf_settings = mf_get_settings($dbh);
		$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);

		//get form properties data
		$query 	= "select 
						  form_name,
						  form_has_css,
						  form_redirect,
						  form_language,
						  form_review_primary_text,
						  form_review_secondary_text,
						  form_review_primary_img,
						  form_review_secondary_img,
						  form_review_use_image,
						  form_review_title,
						  form_review_description,
						  form_resume_enable,
						  form_page_total,
						  form_lastpage_title,
						  form_pagination_type,
						  form_theme_id,
						  payment_show_total,
						  payment_total_location,
						  payment_enable_merchant,
						  payment_currency,
						  payment_price_type,
						  payment_price_amount,
						  payment_enable_discount,
						  payment_discount_type,
						  payment_discount_amount,
						  payment_discount_element_id,
						  payment_enable_tax,
					 	  payment_tax_rate,
					 	  logic_field_enable,
					 	  form_custom_script_enable,
					 	  form_custom_script_url,
					 	  form_entry_edit_enable   
				     from 
				     	 ".MF_TABLE_PREFIX."forms 
				    where 
				    	 form_id=?";
		$params = array($form_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		$form_language = $row['form_language'];

		if(!empty($form_language)){
			mf_set_language($form_language);
		}
		
		$form_has_css 			= $row['form_has_css'];
		$form_redirect			= $row['form_redirect'];
		$form_review_primary_text 	 = $row['form_review_primary_text'];
		$form_review_secondary_text  = $row['form_review_secondary_text'];
		$form_review_primary_img 	 = $row['form_review_primary_img'];
		$form_review_secondary_img   = $row['form_review_secondary_img'];
		$form_review_use_image  	 = (int) $row['form_review_use_image'];
		$form_review_title			 = $row['form_review_title'];
		$form_review_description	 = $row['form_review_description'];
		$form_page_total 			 = $row['form_page_total'];
		$form_lastpage_title 		 = $row['form_lastpage_title'];
		$form_pagination_type		 = $row['form_pagination_type'];
		$form_name					 = htmlspecialchars($row['form_name'],ENT_QUOTES);
		$form_theme_id				 = $row['form_theme_id'];
		$form_resume_enable  	 	 = (int) $row['form_resume_enable'];
		$logic_field_enable 		 = (int) $row['logic_field_enable'];

		$payment_show_total	 		 = (int) $row['payment_show_total'];
		$payment_total_location 	 = $row['payment_total_location'];
		$payment_enable_merchant 	 = (int) $row['payment_enable_merchant'];
		$payment_currency 	   		 = $row['payment_currency'];
		$payment_price_type 	     = $row['payment_price_type'];
		$payment_price_amount    	 = $row['payment_price_amount'];

		$payment_enable_discount 		= (int) $row['payment_enable_discount'];
		$payment_discount_type 	 		= $row['payment_discount_type'];
		$payment_discount_amount 		= (float) $row['payment_discount_amount'];
		$payment_discount_element_id 	= (int) $row['payment_discount_element_id'];

		$payment_enable_tax 		 	= (int) $row['payment_enable_tax'];
		$payment_tax_rate 				= (float) $row['payment_tax_rate'];

		$form_custom_script_enable 		= (int) $row['form_custom_script_enable'];
		$form_custom_script_url 		= $row['form_custom_script_url'];
		$form_entry_edit_enable 		= (int) $row['form_entry_edit_enable'];

		//if the form has 'entry edit' enabled and edit key exist
		//populate the hidden edit_key input
		if(!empty($form_entry_edit_enable) && !empty($_SESSION['mf_form_edit_key'][$form_id])){
			$mf_edit_tag = '<input type="hidden" id="edit_key" name="edit_key" value="'.htmlspecialchars($_SESSION['mf_form_edit_key'][$form_id]).'" />';
		}
		
		//prepare entry data for previewing
		$param['strip_download_link'] = true;
		$param['review_mode']    	  = true;
		$param['show_attach_image']   = true;
		$param['hide_password_data']  = true;
		$param['machform_data_path']   = $machform_data_path;
		$param['machform_path']   	   = $machform_path;

		
		$entry_details = mf_get_entry_details($dbh,$form_id,$record_id,$param);

		//if logic is enable, get hidden elements
		//we'll need it to hide section break
		if($logic_field_enable){
			$entry_values = mf_get_entry_values($dbh,$form_id,$record_id,true);
			foreach ($entry_values as $element_name => $value) {
				$input_data[$element_name] = $value['default_value'];
			}

			$hidden_elements = array();
			for($i=1;$i<=$form_page_total;$i++){
				$current_page_hidden_elements = array();
				$current_page_hidden_elements = mf_get_hidden_elements($dbh,$form_id,$i,$input_data);
				
				$hidden_elements += $current_page_hidden_elements; //use '+' so that the index won't get lost
			}
		}
		
		$entry_data = '<table id="machform_review_table" width="100%" border="0" cellspacing="0" cellpadding="0">
						<thead style="display: none"><tr><th scope="col" width="40%">Questions</th><th scope="col" width="60%">Responses</th></tr></thead>
						<tbody>'."\n";
		
		$toggle = false;
		foreach ($entry_details as $data){
			//0 should be displayed, empty string don't
			if((empty($data['value']) || $data['value'] == '&nbsp;') && $data['value'] !== 0 && $data['value'] !== '0' && $data['element_type'] !== 'section' && $data['element_type'] !== 'media'){
				continue;
			}	

			//don't display page break within review page
			if($data['label'] == 'mf_page_break' && $data['value'] == 'mf_page_break'){
				continue;
			}

			if($toggle){
				$toggle = false;
				$row_style = 'class="alt"';
			}else{
				$toggle = true;
				$row_style = '';
			}	
			
			if($data['element_type'] == 'section' || $data['element_type'] == 'media') {
				
				//if this section break is hidden due to logic, don't display it
				if((!empty($hidden_elements) && !empty($hidden_elements[$data['element_id']])) || 
				   (!empty($hidden_elements) && !empty($hidden_elements[$data['matrix_main_element_id']]))
				){
					continue;
				}

				if(!empty($data['label']) && !empty($data['value']) && ($data['value'] != '&nbsp;')){
					$section_separator = '<br/>';
				}else{
					$section_separator = '';
				}

				$section_break_content = '<span class="mf_section_title">'.nl2br($data['label']).'</span>'.$section_separator.'<span class="mf_section_content">'.nl2br($data['value']).'</span>';

				$entry_data .= "<tr>\n";
				$entry_data .= "<td class=\"mf_review_section_break\" width=\"100%\" colspan=\"2\">".$section_break_content."</td>\n";
				$entry_data .= "</tr>\n";
			}else if($data['element_type'] == 'signature') {
				$element_id = $data['element_id'];
				$signature_hash = md5($data['value']);

				//encode the long query string for more readibility
				$q_string = base64_encode("form_id={$form_id}&id={$record_id}&el=element_{$element_id}&hash={$signature_hash}&review=1");

				$signature_markup = "<img src=\"signature_img.php?q={$q_string}\" style=\"max-width: 100%\" title=\"Signature Image\" />";				

				$entry_data .= "<tr {$row_style}>\n";
				$entry_data .= "<td class=\"mf_review_label\" width=\"40%\" style=\"vertical-align: top\">{$data['label']}</td>\n";
				$entry_data .= "<td class=\"mf_review_value\" width=\"60%\">{$signature_markup}</td>\n";
				$entry_data .= "</tr>\n";
			}else{
	  			$entry_data .= "<tr {$row_style}>\n";
	  	    	$entry_data .= "<td class=\"mf_review_label\" width=\"40%\">{$data['label']}</td>\n";
	  			$entry_data .= "<td class=\"mf_review_value\" width=\"60%\">".nl2br($data['value'])."</td>\n";
	  			$entry_data .= "</tr>\n";
  			}
 		}   	
		 	
   	    $entry_data .= '</tbody></table>';

		//check for specific form css, if any, use it instead
		if($form_has_css){
			$css_dir = $mf_settings['data_dir']."/form_{$form_id}/css/";
		}
		
		if($integration_method == 'iframe'){
			$embed_class = 'class="embed"';
		}
		
		
		//if the form has multiple pages
		//display the pagination header
		if($form_page_total > 1){
			
			//build pagination header based on the selected type. possible values:
			//steps - display multi steps progress
			//percentage - display progress bar with percentage
			//disabled - disabled
			
			$page_breaks_data = array();
			$page_title_array = array();
			
			//get page titles
			$query = "SELECT 
							element_page_title
						FROM 
							".MF_TABLE_PREFIX."form_elements
					   WHERE
							form_id = ? and element_status = 1 and element_type = 'page_break'
					ORDER BY 
					   		element_page_number asc";
			$params = array($form_id);
			
			$sth = mf_do_query($query,$params,$dbh);
			while($row = mf_do_fetch_result($sth)){
				$page_title_array[] = $row['element_page_title'];
			}
			
			if($form_pagination_type == 'steps'){
				
				$page_titles_markup = '';
				
				$i=1;
				foreach ($page_title_array as $page_title){
					$page_titles_markup .= '<td align="center"><span id="page_num_'.$i.'" class="ap_tp_num">'.$i.'</span><span id="page_title_'.$i.'" class="ap_tp_text">'.$page_title.'</span></td><td align="center" class="ap_tp_arrow">&gt;</td>'."\n";
					$i++;
				}
				
				//add the last page title into the pagination header markup
				$page_titles_markup .= '<td align="center"><span id="page_num_'.$i.'" class="ap_tp_num">'.$i.'</span><span id="page_title_'.$i.'" class="ap_tp_text">'.$form_lastpage_title.'</span></td>';
			
				$i++;
				$page_titles_markup .= '<td align="center" class="ap_tp_arrow">&gt;</td><td align="center"><span id="page_num_'.$i.'" class="ap_tp_num ap_tp_num_active">'.$i.'</span><span id="page_title_'.$i.'" class="ap_tp_text ap_tp_text_active">'.$form_review_title.'</span></td>';
				
				//if payment enabled, we need to add the pagination header
				if(!empty($payment_enable_merchant)){
					$i++;
					$page_titles_markup .= '<td align="center" class="ap_tp_arrow">&gt;</td><td align="center"><span id="page_num_'.$i.'" class="ap_tp_num">'.$i.'</span><span id="page_title_'.$i.'" class="ap_tp_text">'.$mf_lang['form_payment_header_title'].'</span></td>';
				}
				
				$pagination_header =<<<EOT
			<ul>
			<li id="pagination_header" class="li_pagination">
			 <table class="ap_table_pagination" width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr> 
			  	{$page_titles_markup}
			  </tr>
			</table>
			</li>
			</ul>
EOT;
			}else if($form_pagination_type == 'percentage'){
				
				if(!empty($payment_enable_merchant)){
					$page_total = count($page_title_array) + 3;
					$current_page_number = $page_total - 1;
					$percent_value = round(($current_page_number/$page_total) * 100);
				}else{
					$page_total = count($page_title_array) + 2;
					$current_page_number = $page_total;
					$percent_value = 99; //it's not make sense to display 100% when the form is not really submitted yet
				}
				
				$page_number_title = sprintf($mf_lang['page_title'],$current_page_number,$page_total);
				$pagination_header =<<<EOT
			<ul>
				<li id="pagination_header" class="li_pagination" title="Click to edit">
			    <h3 id="page_title_{$page_total}">{$page_number_title}</h3>
				<div class="mf_progress_container">          
			    	<div id="mf_progress_percentage" class="mf_progress_value" style="width: {$percent_value}%"><span>{$percent_value}%</span></div>
				</div>
				</li>
			</ul>
EOT;
			}else{			
				$pagination_header = '';
			}
			
		}




		
		//build the button markup (image or text)
		if(!empty($form_review_use_image)){
			$button_markup =<<<EOT
<input id="review_submit" class="submit_img_primary" type="image" name="review_submit" alt="{$form_review_primary_text}" src="{$form_review_primary_img}" />
<input id="review_back" class="submit_img_secondary" type="image" name="review_back" alt="{$form_review_secondary_text}" src="{$form_review_secondary_img}" />
EOT;
		}else{
			$button_markup =<<<EOT
<input id="review_submit" class="button_text btn_primary" type="submit" name="review_submit" value="{$form_review_primary_text}" />
<input id="review_back" class="button_text btn_secondary" type="submit" name="review_back" value="{$form_review_secondary_text}" />
EOT;
		}

		//if this form is using custom theme
		if(!empty($form_theme_id)){
			//get the field highlight color for the particular theme
			$query = "SELECT 
							highlight_bg_type,
							highlight_bg_color,
							form_shadow_style,
							form_shadow_size,
							form_shadow_brightness,
							form_button_type,
							form_button_text,
							form_button_image,
							theme_has_css  
						FROM 
							".MF_TABLE_PREFIX."form_themes 
					   WHERE 
					   		theme_id = ?";
			$params = array($form_theme_id);
			
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);
			
			$form_shadow_style 		= $row['form_shadow_style'];
			$form_shadow_size 		= $row['form_shadow_size'];
			$form_shadow_brightness = $row['form_shadow_brightness'];
			$theme_has_css = (int) $row['theme_has_css'];
			
			//if the theme has css file, make sure to refer to that file
			//otherwise, generate the css dynamically
			
			if(!empty($theme_has_css)){
				$theme_css_link = '<link rel="stylesheet" type="text/css" href="'.$machform_path.$mf_settings['data_dir'].'/themes/theme_'.$form_theme_id.'.css" media="all" />';
			}else{
				$theme_css_link = '<link rel="stylesheet" type="text/css" href="'.$machform_path.'css_theme.php?theme_id='.$form_theme_id.'" media="all" />';
			}
			
			if($row['highlight_bg_type'] == 'color'){
				$field_highlight_color = $row['highlight_bg_color'];
			}else{ 
				//if the field highlight is using pattern instead of color, set the color to empty string
				$field_highlight_color = ''; 
			}
			
			//get the css link for the fonts
			$font_css_markup = mf_theme_get_fonts_link($dbh,$form_theme_id);
			
			//get the form shadow classes
			if(!empty($form_shadow_style) && ($form_shadow_style != 'disabled')){
				preg_match_all("/[A-Z]/",$form_shadow_style,$prefix_matches);
				//this regex simply get the capital characters of the shadow style name
				//example: RightPerspectiveShadow result to RPS and then being sliced to RP
				$form_shadow_prefix_code = substr(implode("",$prefix_matches[0]),0,-1);
				
				$form_shadow_size_class  = $form_shadow_prefix_code.ucfirst($form_shadow_size);
				$form_shadow_brightness_class = $form_shadow_prefix_code.ucfirst($form_shadow_brightness);

				if(empty($integration_method)){ //only display shadow if the form is not being embedded using any method
					$form_container_class = $form_shadow_style.' '.$form_shadow_size_class.' '.$form_shadow_brightness_class;
				}
			}
			
			
			
		}else{ //if the form doesn't have any theme being applied
			$field_highlight_color = '#FFF7C0';
			
			if(empty($integration_method)){
				$form_container_class = ''; //default shadow as of v4.2 is no-shadow
			}else{
				$form_container_class = ''; //dont show any shadow when the form being embedded
			}
		}

		//if the form has enabled merchant support and set the total payment to be displayed
		if(!empty($payment_enable_merchant) && !empty($payment_show_total)){
			
			$currency_symbol = '&#36;';
			
			switch($payment_currency){
				case 'USD' : $currency_symbol = '&#36;';break;
				case 'EUR' : $currency_symbol = '&#8364;';break;
				case 'GBP' : $currency_symbol = '&#163;';break;
				case 'AUD' : $currency_symbol = 'A&#36;';break;
				case 'CAD' : $currency_symbol = 'C&#36;';break;
				case 'JPY' : $currency_symbol = '&#165;';break;
				case 'THB' : $currency_symbol = '&#3647;';break;
				case 'HUF' : $currency_symbol = '&#70;&#116;';break;
				case 'CHF' : $currency_symbol = 'CHF';break;
				case 'CZK' : $currency_symbol = '&#75;&#269;';break;
				case 'SEK' : $currency_symbol = 'kr';break;
				case 'DKK' : $currency_symbol = 'kr';break;
				case 'RON' : $currency_symbol = 'L';break;
				case 'NOK' : $currency_symbol = 'kr';break;
				case 'PHP' : $currency_symbol = '&#36;';break;
				case 'IDR' : $currency_symbol = 'Rp';break;
				case 'INR' : $currency_symbol = 'Rs';break;
				case 'MYR' : $currency_symbol = 'RM';break;
				case 'ZAR' : $currency_symbol = 'R';break;
				case 'PLN' : $currency_symbol = '&#122;&#322;';break;
				case 'BRL' : $currency_symbol = 'R&#36;';break;
				case 'HKD' : $currency_symbol = 'HK&#36;';break;
				case 'MXN' : $currency_symbol = 'Mex&#36;';break;
				case 'TWD' : $currency_symbol = 'NT&#36;';break;
				case 'TRY' : $currency_symbol = 'TL';break;
				case 'NZD' : $currency_symbol = '&#36;';break;
				case 'SGD' : $currency_symbol = '&#36;';break;
			}
		
			
			$session_id = session_id();

			if($payment_price_type == 'variable'){
				$total_payment = (double) mf_get_payment_total($dbh,$form_id,$session_id,0);
			}elseif ($payment_price_type == 'fixed') {
				$total_payment = $payment_price_amount;
			}

			$total_payment = sprintf("%.2f",$total_payment);

			//display tax info if enabled
			if(!empty($payment_enable_tax) && !empty($payment_tax_rate)){
				$tax_markup = "<h5>+{$mf_lang['tax']} {$payment_tax_rate}&#37;</h5>";
			}

			//display discount info if applicable
			//the discount info can only being displayed when the form is having review enabled or a multipage form
			$is_discount_applicable = false;

			//if the discount element for the current session having any value, we can be certain that the discount code has been validated and applicable
			if(!empty($payment_enable_discount)){
				$query = "select element_{$payment_discount_element_id} coupon_element from ".MF_TABLE_PREFIX."form_{$form_id}_review where `id` = ?";
				$params = array($record_id);
				
				$sth = mf_do_query($query,$params,$dbh);
				$row = mf_do_fetch_result($sth);
				
				if(!empty($row['coupon_element'])){
					$is_discount_applicable = true;
				}
			}

			if($is_discount_applicable){
				if($payment_discount_type == 'percent_off'){
					$discount_markup = "<h5>-{$mf_lang['discount']} {$payment_discount_amount}&#37;</h5>";
				}else{
					$discount_markup = "<h5>-{$mf_lang['discount']} {$currency_symbol}{$payment_discount_amount}</h5>";
				}
			}
			
			$payment_extra_markup = '';
			if(!empty($tax_markup) || !empty($discount_markup)){
				$payment_extra_markup =<<<EOT
				<span class="total_extra">
					{$discount_markup}
					{$tax_markup}
				</span>
EOT;
			}

			$payment_total_markup = <<<EOT
				<ul><li class="total_payment mf_review">
					<span>
						<h3>{$currency_symbol}<var>{$total_payment}</var></h3>
						<h5>{$mf_lang['payment_total']}</h5>
					</span>
					{$payment_extra_markup}
				</li></ul>
EOT;
			
		}
		
		if(empty($mf_settings['disable_machform_link'])){
			$powered_by_markup = 'Powered by <a href="https://www.machform.com" target="_blank">MachForm</a>';
		}else{
			$powered_by_markup = '';
		}

		//load custom javascript if enabled
		$custom_script_js = '';
		if(!empty($form_custom_script_enable) && !empty($form_custom_script_url)){
			$custom_script_js = '<script type="text/javascript" src="'.$form_custom_script_url.'"></script>';
		}

		//check for any 'signature' field, if there is any, we need to include the javascript library to display the signature
		$query = "select 
						count(form_id) total_signature_field 
					from 
						".MF_TABLE_PREFIX."form_elements 
				   where 
				   		element_type = 'signature' and 
				   		element_status=1 and 
				   		form_id=?";
		$params = array($form_id);

		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		if(!empty($row['total_signature_field'])){
			$has_signature_field = true;
		}else{
			$has_signature_field = false;
		}

		$self_address = htmlentities($_SERVER['PHP_SELF']); //prevent XSS
		$jquery_url	  = $machform_path.'js/jquery.min.js';

		if($integration_method == 'php'){

			if($has_signature_field){
				$signature_pad_init = '<script type="text/javascript" src="'.$machform_path.'js/signature_pad/signature_pad.umd.js"></script>'."\n";
			}

			$form_markup = <<<EOT
<script type="text/javascript" src="{$jquery_url}"></script>
<script type="text/javascript" src="{$machform_path}js/jquery-ui-1.12/effect.js"></script>
<script type="text/javascript" src="{$machform_path}view.js"></script>
{$signature_pad_init}
{$custom_script_js}
<link rel="stylesheet" type="text/css" href="{$machform_path}{$css_dir}view.css" media="all" />
{$theme_css_link}
{$font_css_markup}
<style>
html{
	background: none repeat scroll 0 0 transparent;
}
</style>

<div id="main_body" class="integrated">
	<div id="form_container">
		<form id="form_{$form->id}" class="appnitro" method="post" action="{$self_address}">
		    <div class="form_description">
				<h2>{$form_review_title}</h2>
				<p>{$form_review_description}</p>
			</div>
			{$pagination_header}
			{$entry_data}
			<ul>
			{$payment_total_markup}
			<li id="li_buttons" class="buttons">
			    <input type="hidden" name="id" value="{$form_id}" />
			    <input type="hidden" name="mf_page_from" value="{$from_page_num}" />
			    {$mf_edit_tag}
			    {$mfsid_tag}
			    {$button_markup}
			</li>
			</ul>
		</form>		
	</div>
</div>
EOT;
		}else{

			if($integration_method == 'iframe'){	
				$auto_height_js =<<<EOT
<script type="text/javascript" src="{$machform_path}js/jquery.ba-postmessage.min.js"></script>
<script type="text/javascript">
    $(function(){
    	$.postMessage({mf_iframe_height: $('body').outerHeight(true)}, '*', parent );
    });
    window.onload = function(e){ 
    	$.postMessage({mf_iframe_height: $('body').outerHeight(true)}, '*', parent );
	}
</script>
EOT;
			}

			if($has_signature_field){
					$signature_pad_init = '<script type="text/javascript" src="'.$machform_path.'js/signature_pad/signature_pad.umd.js"></script>'."\n";
			}

			$form_markup = <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xml:lang="en" {$embed_class} xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$form_name}</title>
<link rel="stylesheet" type="text/css" href="{$machform_path}{$css_dir}view.css{$mf_version_tag}" media="all" />
<link rel="stylesheet" type="text/css" href="{$machform_path}view.mobile.css{$mf_version_tag}" media="all" />
{$theme_css_link}
{$font_css_markup}
<script type="text/javascript" src="{$jquery_url}"></script>
<script type="text/javascript" src="{$machform_path}js/jquery-ui-1.12/effect.js"></script>
<script type="text/javascript" src="{$machform_path}view.js"></script>
{$auto_height_js}
{$signature_pad_init}
{$media_video_init}
{$media_pdf_init}
{$custom_script_js}
</head>
<body id="main_body">
	
	<img id="top" src="{$machform_path}images/top.png" alt="" />
	<div id="form_container" class="{$form_container_class}">
	
		<h1><a>MachForm</a></h1>
		<form id="form_{$form_id}" class="appnitro" method="post" action="{$self_address}">
		    <div class="form_description">
				<h2>{$form_review_title}</h2>
				<p>{$form_review_description}</p>
			</div>
			{$pagination_header}
			{$payment_total_markup}
			{$entry_data}
			<ul>
			<li id="li_buttons" class="buttons">
			    <input type="hidden" name="id" value="{$form_id}" />
			    <input type="hidden" name="mf_page_from" value="{$from_page_num}" />
			    {$mf_edit_tag}
			    {$mfsid_tag}
			    {$button_markup}
			</li>
			</ul>
		</form>		
			
	</div>
	<img id="bottom" src="{$machform_path}images/bottom.png" alt="" />
	</body>
</html>
EOT;
		}

		return $form_markup;
	}

	//display form payment page
	function mf_display_form_payment($dbh,$form_id,$record_id,$form_params=array()){
		global $mf_lang;

		$mfsid_tag = '';
		if(empty($_COOKIE['mf_has_cookie'])){
			$mfsid = session_id();
			$mfsid_tag = '<input type="hidden" id="mfsid" name="mfsid" value="'.$mfsid.'" />';
		}
		
		if(!empty($form_params['integration_method'])){
			$integration_method = $form_params['integration_method'];
		}else{
			$integration_method = '';
		}

		if(!empty($form_params['machform_path'])){
			$machform_path = $form_params['machform_path'];
		}else{
			$machform_path = '';
		}

		if(!empty($form_params['machform_data_path'])){
			$machform_data_path = $form_params['machform_data_path'];
		}else{
			$machform_data_path = '';
		}

		$embed_class = '';
		$theme_css_link = '';
		$font_css_markup = '';
		$auto_height_js = '';
		$pagination_header = '';
		$payment_term_markup = '';
		$payment_term_markup = '';
		$payment_request_button_markup = '';

		//check for payment_token
		//if exist, the user is resuming the payment from previously unpaid entry
		//we need to set all necessary session if the token is valid
		if(!empty($form_params['pay_token'])){
			$payment_resume_token = trim($form_params['pay_token']);
			$payment_resume_token = base64_decode($payment_resume_token);

			$exploded  = explode('-', $payment_resume_token);
			$record_id = (int) $exploded[0];
			$date_created_md5 = $exploded[1];

			//compare the date created md5 with the existing record
			$query = "SELECT date_created FROM ".MF_TABLE_PREFIX."form_{$form_id} WHERE `id`=?";
			$params = array($record_id);
		
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);

			if(md5($row['date_created']) == $date_created_md5){
				$_SESSION['mf_payment_record_id'][$form_id] = $record_id;
				$_SESSION['mf_form_payment_access'][$form_id]  = true;
				$_SESSION['mf_form_completed'][$form_id] = true;
			}
		}

		//check permission to access this page
		if($_SESSION['mf_form_payment_access'][$form_id] !== true){
			return "Your session has been expired. Please <a href='view.php?id={$form_id}'>click here</a> to start again.";
		}

		$mf_settings = mf_get_settings($dbh);
		$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);

		//get form properties data
		$query 	= "select 
						  form_name,
						  form_has_css,
						  form_redirect,
						  form_language,
						  form_review,
						  form_review_primary_text,
						  form_review_secondary_text,
						  form_review_primary_img,
						  form_review_secondary_img,
						  form_review_use_image,
						  form_review_title,
						  form_review_description,
						  form_resume_enable,
						  form_page_total,
						  form_lastpage_title,
						  form_pagination_type,
						  form_theme_id,
						  payment_show_total,
						  payment_total_location,
						  payment_enable_merchant,
						  payment_merchant_type,
						  payment_currency,
						  payment_price_type,
						  payment_price_name,
						  payment_price_amount,
						  payment_ask_billing,
						  payment_ask_shipping,
						  payment_stripe_live_public_key,
						  payment_stripe_test_public_key,
						  payment_stripe_live_secret_key,
						  payment_stripe_test_secret_key,
						  payment_stripe_enable_test_mode,
						  payment_stripe_enable_payment_request_button,
						  payment_stripe_account_country,
						  payment_braintree_live_encryption_key,
						  payment_braintree_test_encryption_key,
						  payment_braintree_enable_test_mode,
						  payment_enable_recurring,
						  payment_recurring_cycle,
						  payment_recurring_unit,
						  payment_enable_trial,
						  payment_trial_period,
						  payment_trial_unit,
						  payment_trial_amount,
						  payment_enable_setupfee,
						  payment_setupfee_amount,
						  payment_delay_notifications,
						  payment_enable_tax,
					 	  payment_tax_rate,
					 	  payment_enable_discount,
						  payment_discount_type,
						  payment_discount_amount,
						  payment_discount_element_id,
						  form_custom_script_enable,
					 	  form_custom_script_url  
				     from 
				     	 ".MF_TABLE_PREFIX."forms 
				    where 
				    	 form_id=?";
		$params = array($form_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		$form_language = $row['form_language'];

		if(!empty($form_language)){
			mf_set_language($form_language);
		}
		
		$form_payment_title			 = $mf_lang['form_payment_title'];
		$form_payment_description	 = $mf_lang['form_payment_description'];

		$form_has_css 			= $row['form_has_css'];
		$form_redirect			= $row['form_redirect'];
		$form_review  	 		= (int) $row['form_review'];
		$form_review_primary_text 	 = $row['form_review_primary_text'];
		$form_review_secondary_text  = $row['form_review_secondary_text'];
		$form_review_primary_img 	 = $row['form_review_primary_img'];
		$form_review_secondary_img   = $row['form_review_secondary_img'];
		$form_review_use_image  	 = (int) $row['form_review_use_image'];
		$form_review_title			 = $row['form_review_title'];
		$form_review_description	 = $row['form_review_description'];
		$form_page_total 			 = (int) $row['form_page_total'];
		$form_lastpage_title 		 = $row['form_lastpage_title'];
		$form_pagination_type		 = $row['form_pagination_type'];
		$form_name					 = htmlspecialchars($row['form_name'],ENT_QUOTES);
		$form_theme_id				 = $row['form_theme_id'];
		$form_resume_enable  	 	 = (int) $row['form_resume_enable'];

		$form_custom_script_enable 	= (int) $row['form_custom_script_enable'];
		$form_custom_script_url 	= $row['form_custom_script_url'];

		$payment_show_total	 		 = (int) $row['payment_show_total'];
		$payment_total_location 	 = $row['payment_total_location'];
		$payment_enable_merchant 	 = (int) $row['payment_enable_merchant'];
		$payment_enable_tax 		 = (int) $row['payment_enable_tax'];
		$payment_tax_rate 			 = (float) $row['payment_tax_rate'];

		$payment_currency 	   		 = $row['payment_currency'];
		$payment_price_type 	     = $row['payment_price_type'];
		$payment_price_amount    	 = $row['payment_price_amount'];
		$payment_price_name			 = htmlspecialchars($row['payment_price_name'],ENT_QUOTES);
		$payment_ask_billing 	 	 = (int) $row['payment_ask_billing'];
		$payment_ask_shipping 	 	 = (int) $row['payment_ask_shipping'];
		$payment_merchant_type		 = $row['payment_merchant_type'];
		$payment_stripe_enable_test_mode = (int) $row['payment_stripe_enable_test_mode'];
		$payment_stripe_enable_payment_request_button = (int) $row['payment_stripe_enable_payment_request_button'];
		$payment_stripe_live_public_key	 = trim($row['payment_stripe_live_public_key']);
		$payment_stripe_test_public_key	 = trim($row['payment_stripe_test_public_key']);
		$payment_stripe_live_secret_key	 = trim($row['payment_stripe_live_secret_key']);
		$payment_stripe_test_secret_key	 = trim($row['payment_stripe_test_secret_key']);
		if(!empty($row['payment_stripe_account_country'])){
			$payment_stripe_account_country = strtoupper($row['payment_stripe_account_country']);
		}else{
			$payment_stripe_account_country = 'US';
		}

		$payment_braintree_live_encryption_key  = trim($row['payment_braintree_live_encryption_key']);
		$payment_braintree_test_encryption_key  = trim($row['payment_braintree_test_encryption_key']);
		$payment_braintree_enable_test_mode 	= (int) $row['payment_braintree_enable_test_mode'];

		$payment_enable_recurring = (int) $row['payment_enable_recurring'];
		$payment_recurring_cycle  = (int) $row['payment_recurring_cycle'];
		$payment_recurring_unit   = $row['payment_recurring_unit'];

		$payment_enable_setupfee = (int) $row['payment_enable_setupfee'];
		$payment_setupfee_amount = (float) $row['payment_setupfee_amount'];

		//braintree currently doesn't support creating subscription through API
		if(in_array($payment_merchant_type, array('braintree'))){
			$payment_enable_recurring = 0;
		}

		$payment_enable_trial = (int) $row['payment_enable_trial'];
		$payment_trial_period = (int) $row['payment_trial_period'];
		$payment_trial_unit   = $row['payment_trial_unit'];
		$payment_trial_amount = (float) $row['payment_trial_amount'];

		$payment_enable_discount = (int) $row['payment_enable_discount'];
		$payment_discount_type 	 = $row['payment_discount_type'];
		$payment_discount_amount = (float) $row['payment_discount_amount'];
		$payment_discount_element_id = (int) $row['payment_discount_element_id'];

		$payment_delay_notifications = (int) $row['payment_delay_notifications'];

		$is_discount_applicable = false;

		//if the discount element for the current entry_id having any value, we can be certain that the discount code has been validated and applicable
		if(!empty($payment_enable_discount)){
			$query = "select element_{$payment_discount_element_id} coupon_element from ".MF_TABLE_PREFIX."form_{$form_id} where `id` = ? and `status` = 1";
			$params = array($record_id);
			
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);
			
			if(!empty($row['coupon_element'])){
				$is_discount_applicable = true;
			}
		}

		//check for specific form css, if any, use it instead
		if($form_has_css){
			$css_dir = $mf_settings['data_dir']."/form_{$form_id}/css/";
		}
		
		if($integration_method == 'iframe'){
			$embed_class = 'class="embed"';
		}
		
		//get total payment
		$currency_symbol 	  = '&#36;';
		
		switch($payment_currency){
				case 'USD' : $currency_symbol = '&#36;';break;
				case 'EUR' : $currency_symbol = '&#8364;';break;
				case 'GBP' : $currency_symbol = '&#163;';break;
				case 'AUD' : $currency_symbol = 'A&#36;';break;
				case 'CAD' : $currency_symbol = 'C&#36;';break;
				case 'JPY' : $currency_symbol = '&#165;';break;
				case 'THB' : $currency_symbol = '&#3647;';break;
				case 'HUF' : $currency_symbol = '&#70;&#116;';break;
				case 'CHF' : $currency_symbol = 'CHF';break;
				case 'CZK' : $currency_symbol = '&#75;&#269;';break;
				case 'SEK' : $currency_symbol = 'kr';break;
				case 'DKK' : $currency_symbol = 'kr';break;
				case 'RON' : $currency_symbol = 'L';break;
				case 'NOK' : $currency_symbol = 'kr';break;
				case 'PHP' : $currency_symbol = '&#36;';break;
				case 'IDR' : $currency_symbol = 'Rp';break;
				case 'INR' : $currency_symbol = 'Rs';break;
				case 'MYR' : $currency_symbol = 'RM';break;
				case 'ZAR' : $currency_symbol = 'R';break;
				case 'PLN' : $currency_symbol = '&#122;&#322;';break;
				case 'BRL' : $currency_symbol = 'R&#36;';break;
				case 'HKD' : $currency_symbol = 'HK&#36;';break;
				case 'MXN' : $currency_symbol = 'Mex&#36;';break;
				case 'TWD' : $currency_symbol = 'NT&#36;';break;
				case 'TRY' : $currency_symbol = 'TL';break;
				case 'NZD' : $currency_symbol = '&#36;';break;
				case 'SGD' : $currency_symbol = '&#36;';break;
		}

		if($payment_price_type == 'variable'){

			$total_payment_amount = (double) mf_get_payment_total($dbh,$form_id,$record_id,0,'live');
			$payment_items = mf_get_payment_items($dbh,$form_id,$record_id,'live');
			
			
			//build the payment list markup
			$payment_list_items_markup = '';
			if(!empty($payment_items)){
				foreach ($payment_items as $item) {
					if($item['quantity'] > 1){
						$quantity_tag = ' <span style="font-weight: normal;padding-left:5px">x'.$item['quantity'].'</span>';
					}else{
						$quantity_tag = '';
					}
					
					// '0' quantity will not display in item lists
					if($item['quantity'] > 0) {
						if($item['type'] == 'money'){
							$payment_list_items_markup .= "<li>{$item['title']} <span>{$currency_symbol}{$item['amount']}{$quantity_tag}</span></li>"."\n";
						}else if($item['type'] == 'checkbox'){
							$payment_list_items_markup .= "<li>{$item['sub_title']} <span>{$currency_symbol}{$item['amount']}{$quantity_tag}</span></li>"."\n";
						}else if($item['type'] == 'select' || $item['type'] == 'radio'){
							$payment_list_items_markup .= "<li>{$item['title']} <em>({$item['sub_title']})</em> <span>{$currency_symbol}{$item['amount']}{$quantity_tag}</span></li>"."\n";
						}
					}
				}

				//calculate discount if applicable
				if($is_discount_applicable){
					$payment_calculated_discount = 0;

					if($payment_discount_type == 'percent_off'){
						//the discount is percentage
						$payment_calculated_discount = ($payment_discount_amount / 100) * $total_payment_amount;
						$payment_calculated_discount = round($payment_calculated_discount,2); //round to 2 digits decimal
						$discount_percentage_label  = '('.$payment_discount_amount.'%)';
					}else{
						//the discount is fixed amount
						$payment_calculated_discount = sprintf("%.2f",round($payment_discount_amount,2)); //round to 2 digits decimal
						$discount_percentage_label = '';
					}

					$total_payment_amount -= $payment_calculated_discount;

					$payment_list_items_markup .= "<li>{$mf_lang['discount']} {$discount_percentage_label}<span>-{$currency_symbol}{$payment_calculated_discount}</span></li>"."\n";
				}

				//calculate tax if enabled
				if(!empty($payment_enable_tax) && !empty($payment_tax_rate)){
					$payment_tax_amount = ($payment_tax_rate / 100) * $total_payment_amount;
					$payment_tax_amount = round($payment_tax_amount,2); //round to 2 digits decimal

					$total_payment_amount += $payment_tax_amount;
					$payment_list_items_markup .= "<li>{$mf_lang['tax']} ({$payment_tax_rate}%)<span>{$currency_symbol}{$payment_tax_amount}</span></li>"."\n";
				}
			}
		}else if($payment_price_type == 'fixed'){
			$total_payment_amount = $payment_price_amount;

			$payment_list_items_markup = "<li>{$payment_price_name}</li>";

			//calculate discount if applicable
			if($is_discount_applicable){
				$payment_calculated_discount = 0;

				if($payment_discount_type == 'percent_off'){
					//the discount is percentage
					$payment_calculated_discount = ($payment_discount_amount / 100) * $total_payment_amount;
					$payment_calculated_discount = round($payment_calculated_discount,2); //round to 2 digits decimal
					$discount_amount_label = $payment_discount_amount.'%';
				}else{
					//the discount is fixed amount
					$payment_calculated_discount = sprintf("%.2f",round($payment_discount_amount,2)); //round to 2 digits decimal
					$discount_amount_label = $currency_symbol.$payment_calculated_discount;
				}

				$total_payment_amount -= $payment_calculated_discount;
				$discount_label = "-{$mf_lang['discount']} {$discount_amount_label}";

				$payment_list_items_markup .= "<li>{$discount_label}</li>";
			}


			//calculate tax if enabled
			$tax_label = '';
			if(!empty($payment_enable_tax) && !empty($payment_tax_rate)){
				$payment_tax_amount = ($payment_tax_rate / 100) * $total_payment_amount;
				$payment_tax_amount = round($payment_tax_amount,2); //round to 2 digits decimal

				$total_payment_amount += $payment_tax_amount;
				$tax_label = "+{$mf_lang['tax']} {$payment_tax_rate}%";

				$payment_list_items_markup .= "<li>{$tax_label}</li>";
			}

			
		}

		
		//construct terms wording and setup fee (if any) for recurring payments
		if(!empty($payment_enable_recurring)){
			
			//construct terms wording
			$payment_plurals = '';
			if($payment_recurring_cycle > 1){
				$payment_plurals = 's';
				$payment_recurring_cycle_markup = $payment_recurring_cycle.' ';
			}

			if(!empty($payment_enable_trial)){
				//recurring with trial period
				$payment_trial_price = $currency_symbol.$payment_trial_amount;
				if(empty($payment_trial_amount)){
					$payment_trial_price = 'free';
				}

				$payment_trial_plurals = '';
				if($payment_trial_period > 1){
					$payment_trial_plurals = 's';
				}

				$payment_term_markup =<<<EOT
					<li class="payment_summary_term">
						<em>Trial period: {$payment_trial_period} {$payment_trial_unit}{$payment_trial_plurals} ({$payment_trial_price})</em><br>
						<em>Then you will be charged {$currency_symbol}{$total_payment_amount} every {$payment_recurring_cycle_markup}{$payment_recurring_unit}{$payment_plurals}</em>
					</li>
EOT;
				//when trial being enabled, we need to display the trial amount into the TOTAL
				$total_payment_amount = $payment_trial_amount; 
			}else{
				$payment_term_markup = "<li class=\"payment_summary_term\"><em>You will be charged {$currency_symbol}{$total_payment_amount} every {$payment_recurring_cycle_markup}{$payment_recurring_unit}{$payment_plurals}</em></li>";
			}

			//construct setup fee
			//currently only available for stripe and paypal pro
			if(!empty($payment_enable_setupfee) && !empty($payment_setupfee_amount) && in_array($payment_merchant_type, array('stripe','paypal_rest'))){
				$payment_list_items_markup .= "<li>{$mf_lang['setup_fee']} <span>{$currency_symbol}{$payment_setupfee_amount}</span></li>"."\n";
				$total_payment_amount += $payment_setupfee_amount;
			}
		}
		
		//if the form has multiple pages
		//display the pagination header
		if($form_page_total > 1){
			
			//build pagination header based on the selected type. possible values:
			//steps - display multi steps progress
			//percentage - display progress bar with percentage
			//disabled - disabled
			
			$page_breaks_data = array();
			$page_title_array = array();
			
			//get page titles
			$query = "SELECT 
							element_page_title
						FROM 
							".MF_TABLE_PREFIX."form_elements
					   WHERE
							form_id = ? and element_status = 1 and element_type = 'page_break'
					ORDER BY 
					   		element_page_number asc";
			$params = array($form_id);
			
			$sth = mf_do_query($query,$params,$dbh);
			while($row = mf_do_fetch_result($sth)){
				$page_title_array[] = $row['element_page_title'];
			}
			
			if($form_pagination_type == 'steps'){
				
				$page_titles_markup = '';
				
				$i=1;
				foreach ($page_title_array as $page_title){
					$page_titles_markup .= '<td align="center"><span id="page_num_'.$i.'" class="ap_tp_num">'.$i.'</span><span id="page_title_'.$i.'" class="ap_tp_text">'.$page_title.'</span></td><td align="center" class="ap_tp_arrow">&gt;</td>'."\n";
					$i++;
				}
				
				//add the last page title into the pagination header markup
				$page_titles_markup .= '<td align="center"><span id="page_num_'.$i.'" class="ap_tp_num">'.$i.'</span><span id="page_title_'.$i.'" class="ap_tp_text">'.$form_lastpage_title.'</span></td>';
				
				if(!empty($form_review)){
					$i++;
					$page_titles_markup .= '<td align="center" class="ap_tp_arrow">&gt;</td><td align="center"><span id="page_num_'.$i.'" class="ap_tp_num">'.$i.'</span><span id="page_title_'.$i.'" class="ap_tp_text">'.$form_review_title.'</span></td>';
				}

				$i++;
				$page_titles_markup .= '<td align="center" class="ap_tp_arrow">&gt;</td><td align="center"><span id="page_num_'.$i.'" class="ap_tp_num ap_tp_num_active">'.$i.'</span><span id="page_title_'.$i.'" class="ap_tp_text ap_tp_text_active">'.$mf_lang['form_payment_header_title'].'</span></td>';
				
				
				$pagination_header =<<<EOT
			<ul>
			<li id="pagination_header" class="li_pagination">
			 <table class="ap_table_pagination" width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr> 
			  	{$page_titles_markup}
			  </tr>
			</table>
			</li>
			</ul>
EOT;
			}else if($form_pagination_type == 'percentage'){
				
				$page_total = count($page_title_array) + 2;
				if(!empty($form_review)){
					$page_total++;
				}
				
				$percent_value = 99;
				
				$page_number_title = sprintf($mf_lang['page_title'],$page_total,$page_total);
				$pagination_header =<<<EOT
			<ul>
				<li id="pagination_header" class="li_pagination" title="Click to edit">
			    <h3 id="page_title_{$page_total}">{$page_number_title}</h3>
				<div class="mf_progress_container">          
			    	<div id="mf_progress_percentage" class="mf_progress_value" style="width: {$percent_value}%"><span>{$percent_value}%</span></div>
				</div>
				</li>
			</ul>
EOT;
			}else{			
				$pagination_header = '';
			}
			
		}

		
		//build the button markup
		$button_markup =<<<EOT
<input id="btn_submit_payment" class="button_text btn_primary" type="submit" data-originallabel="{$mf_lang['payment_submit_button']}" value="{$mf_lang['payment_submit_button']}" />
EOT;

		//if this form is using custom theme
		if(!empty($form_theme_id)){
			//get the field highlight color for the particular theme
			$query = "SELECT 
							highlight_bg_type,
							highlight_bg_color,
							form_shadow_style,
							form_shadow_size,
							form_shadow_brightness,
							form_button_type,
							form_button_text,
							form_button_image,
							theme_has_css  
						FROM 
							".MF_TABLE_PREFIX."form_themes 
					   WHERE 
					   		theme_id = ?";
			$params = array($form_theme_id);
			
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);
			
			$form_shadow_style 		= $row['form_shadow_style'];
			$form_shadow_size 		= $row['form_shadow_size'];
			$form_shadow_brightness = $row['form_shadow_brightness'];
			$theme_has_css = (int) $row['theme_has_css'];
			
			//if the theme has css file, make sure to refer to that file
			//otherwise, generate the css dynamically
			
			if(!empty($theme_has_css)){
				$theme_css_link = '<link rel="stylesheet" type="text/css" href="'.$machform_path.$mf_settings['data_dir'].'/themes/theme_'.$form_theme_id.'.css" media="all" />';
			}else{
				$theme_css_link = '<link rel="stylesheet" type="text/css" href="'.$machform_path.'css_theme.php?theme_id='.$form_theme_id.'" media="all" />';
			}
			
			if($row['highlight_bg_type'] == 'color'){
				$field_highlight_color = $row['highlight_bg_color'];
			}else{ 
				//if the field highlight is using pattern instead of color, set the color to empty string
				$field_highlight_color = ''; 
			}
			
			//get the css link for the fonts
			$font_css_markup = mf_theme_get_fonts_link($dbh,$form_theme_id);
			
			//get the form shadow classes
			if(!empty($form_shadow_style) && ($form_shadow_style != 'disabled')){
				preg_match_all("/[A-Z]/",$form_shadow_style,$prefix_matches);
				//this regex simply get the capital characters of the shadow style name
				//example: RightPerspectiveShadow result to RPS and then being sliced to RP
				$form_shadow_prefix_code = substr(implode("",$prefix_matches[0]),0,-1);
				
				$form_shadow_size_class  = $form_shadow_prefix_code.ucfirst($form_shadow_size);
				$form_shadow_brightness_class = $form_shadow_prefix_code.ucfirst($form_shadow_brightness);

				if(empty($integration_method)){ //only display shadow if the form is not being embedded using any method
					$form_container_class = $form_shadow_style.' '.$form_shadow_size_class.' '.$form_shadow_brightness_class;
				}
			}
			
			
			
		}else{ //if the form doesn't have any theme being applied
			$field_highlight_color = '#FFF7C0';
			
			if(empty($integration_method)){
				$form_container_class = ''; //default shadow as of v4.2 is no-shadow
			}else{
				$form_container_class = ''; //dont show any shadow when the form being embedded
			}
		}

		if(empty($mf_settings['disable_machform_link'])){
			$powered_by_markup = 'Powered by <a href="https://www.machform.com" target="_blank">MachForm</a>';
		}else{
			$powered_by_markup = '';
		}

		$self_address = htmlentities($_SERVER['PHP_SELF']); //prevent XSS

		$country = mf_get_country_list();
		$country_markup = '<option value="" selected="selected"></option>'."\n";

		foreach ($country as $data){
			//stripe requires 2 digit country code
			if($payment_merchant_type == 'stripe'){
				$data['value'] = mf_get_country_code($data['value']);
			}

			$country_markup .= "<option value=\"{$data['value']}\">{$data['label']}</option>\n";
		}

		$label_address_state = $mf_lang['address_state'];
		$label_address_zip	 = $mf_lang['address_zip'];

		//if 'GBP' selected, display UK-specific wording
		if($payment_currency == 'GBP'){
			$label_address_state  = 'County';
			$label_address_zip	  = 'Postcode';
		}

		$billing_address_markup = '';
		if(!empty($payment_ask_billing)){
			$billing_address_markup =<<<EOT
				<li id="li_billing_address" class="address">
					<fieldset>
						<legend style="color: transparent;height: 0px;font-size: 0px;">Billing Address</legend>
						<span class="description">Billing Address <span class="required">*</span></span>
						<div>
							<span id="li_billing_span_1">
								<label for="billing_street">{$mf_lang['address_street']}</label>
								<input id="billing_street" class="element text large" value="" type="text" />
							</span>
						
							<span id="li_billing_span_2" class="left state_list">
								<label for="billing_city">{$mf_lang['address_city']}</label>
								<input id="billing_city" class="element text large" value="" type="text" />
							</span>
						
							<span id="li_billing_span_3" class="right state_list">
								<label for="billing_state">{$label_address_state}</label>
								<input id="billing_state" class="element text large" value="" type="text" />
							</span>
						
							<span id="li_billing_span_4" class="left">
								<label for="billing_zipcode">{$label_address_zip}</label>
								<input id="billing_zipcode" class="element text large" maxlength="15" value="{$default_value_5}" type="text" />
							</span>
							
							<span id="li_billing_span_5" class="right">
								<label for="billing_country">{$mf_lang['address_country']}</label>
								<select class="element select large" id="billing_country"> 
									{$country_markup}	
								</select>
						    </span>
					    </div><p id="billing_error_message" class="error" style="display: none"></p>
					</fieldset>
				</li>
EOT;
		}

		$shipping_address_markup = '';
		if(!empty($payment_ask_shipping)){

			//if both billing and shipping being enabled, display a checkbox to allow the user to mark the address as the same
			if(!empty($payment_ask_billing)){
				$same_shipping_markup =<<<EOT
					<div>
					    <input type="checkbox" value="1" checked="checked" class="checkbox" id="mf_same_shipping_address">
						<label for="mf_same_shipping_address" class="choice">My shipping address is the same as my billing address</label>
					</div>
EOT;
				$shipping_display = 'display: none';
			}

			$shipping_address_markup =<<<EOT
				<li id="li_shipping_address" class="address">
					<fieldset>
						<legend style="color: transparent;height: 0px;font-size: 0px;">Shipping Address</legend>
						<span class="description shipping_address_detail" style="{$shipping_display}">Shipping Address <span class="required">*</span></span>
						<div class="shipping_address_detail" style="{$shipping_display}">
							<span id="li_shipping_span_1">
								<label for="shipping_street">{$mf_lang['address_street']}</label>
								<input id="shipping_street" class="element text large" value="" type="text" />
							</span>
						
							<span id="li_shipping_span_2" class="left state_list">
								<label for="shipping_city">{$mf_lang['address_city']}</label>
								<input id="shipping_city" class="element text large" value="" type="text" />
							</span>
						
							<span id="li_shipping_span_3" class="right state_list">
								<label for="shipping_state">{$label_address_state}</label>
								<input id="shipping_state" class="element text large" value="" type="text" />
							</span>
						
							<span id="li_shipping_span_4" class="left">
								<label for="shipping_zipcode">{$label_address_zip}</label>
								<input id="shipping_zipcode" class="element text large" maxlength="15" value="{$default_value_5}" type="text" />
							</span>
							
							<span id="li_shipping_span_5" class="right">
								<label for="shipping_country">{$mf_lang['address_country']}</label>
								<select class="element select large" id="shipping_country"> 
									{$country_markup}	
								</select>
						    </span>
						    <p id="shipping_error_message" class="error" style="display: none"></p>
					    </div>
					    {$same_shipping_markup}
					</fieldset>
				</li>
EOT;
		}

		$credit_card_logos = array();
		$credit_card_logos['visa'] 		 = '<img src="'.$machform_path.'images/cards/visa.png" alt="Visa" title="Visa" />';
		$credit_card_logos['mastercard'] = '<img src="'.$machform_path.'images/cards/mastercard.png" alt="MasterCard" title="MasterCard" />';
		$credit_card_logos['amex'] 		 = '<img src="'.$machform_path.'images/cards/amex.png" alt="American Express" title="American Express" />';
		$credit_card_logos['jcb'] 		 = '<img src="'.$machform_path.'images/cards/jcb.png" alt="JCB" title="JCB" />';
		$credit_card_logos['discover']   = '<img src="'.$machform_path.'images/cards/discover.png" alt="Discover" title="Discover" />';
		$credit_card_logos['diners'] 	 = '<img src="'.$machform_path.'images/cards/diners.png" alt="Diners Club" title="Diners Club" />';

		$accepted_card_types = array('visa','mastercard','amex','jcb','discover','diners'); //the default accepted credit card types

		//build credit card input markup
		$current_year = date("Y");
		$year_dropdown_markup = '';
		foreach (range($current_year, $current_year + 15) as $year) {
			$year_dropdown_markup .= "<option value=\"{$year}\">{$year}</option>"."\n";
		}
		
		$credit_card_markup =<<<EOT
				<li id="li_credit_card" class="credit_card">
					<fieldset>
						<legend style="color: transparent;height: 0px;font-size: 0px;">{$mf_lang['payment_card_title']}</legend>
						<span class="description">{$mf_lang['payment_card_title']} <span class="required">*</span></span>
						<div>
							<span id="li_cc_span_1" class="left">
								<label for="cc_first_name">{$mf_lang['name_first']}</label>
								<input id="cc_first_name" class="element text large" value="" type="text" />
							</span>
						
							<span id="li_cc_span_2" class="right">
								<label for="cc_last_name">{$mf_lang['name_last']}</label>
								<input id="cc_last_name" class="element text large" value="" type="text" />
							</span>

							<span id="li_cc_span_3" class="left">
								<label for="cc_number">Card Number</label>
								<input id="cc_number" class="element text large" value="" type="text" />
							</span>
						
							<span id="li_cc_span_4" class="right">
								<label for="cc_cvv">CVV</label>
								<input id="cc_cvv" class="element text large" value="" type="text" />
							</span>

							<span id="li_cc_span_5" style="text-align: right">
								<img id="cc_secure_icon" src="{$machform_path}images/icons/lock.png" alt="Secure" title="Secure" /> 
								<label for="cc_expiry_month" style="display: inline">Expiration: </label>
								<select class="element select" id="cc_expiry_month">
									<option value="01">01 - January</option>
									<option value="02">02 - February</option>
									<option value="03">03 - March</option>
									<option value="04">04 - April</option>
									<option value="05">05 - May</option>
									<option value="06">06 - June</option>
									<option value="07">07 - July</option>
									<option value="08">08 - August</option>
									<option value="09">09 - September</option>
									<option value="10">10 - October</option>
									<option value="11">11 - November</option>
									<option value="12">12 - December</option>
								</select>
								<select class="element select" id="cc_expiry_year">
									{$year_dropdown_markup}
								</select>
							</span>
						</div><p id="credit_card_error_message" class="error" style="display: none"></p>
					</fieldset>
				</li>
EOT;

		if($payment_merchant_type == 'stripe'){
			if(!empty($payment_stripe_enable_test_mode)){
				$stripe_public_key = $payment_stripe_test_public_key;
			}else{
				$stripe_public_key = $payment_stripe_live_public_key;
			}

			$credit_card_markup =<<<EOT
			<li id="li_credit_card" class="credit_card highlighted">
				<legend style="color: transparent;height: 0px;font-size: 0px;">{$mf_lang['payment_card_title']}</legend>
				<span class="description">{$mf_lang['payment_card_title']} <span class="required">*</span></span>
				<div>
							<div id="stripe-card-element"></div>
							<p id="credit_card_error_message" class="error" style="display: none;margin-top: -10px !important;padding-bottom: 10px"></p>
							<span id="li_cc_span_1" class="left">
								<label for="cc_first_name">{$mf_lang['name_first']}</label>
								<input id="cc_first_name" class="element text large" value="" type="text" />
							</span>
						
							<span id="li_cc_span_2" class="right">
								<label for="cc_last_name">{$mf_lang['name_last']}</label>
								<input id="cc_last_name" class="element text large" value="" type="text" />
							</span>

				</div>			
			</li>
EOT;

			$mf_stripe_total_payment_amount = $total_payment_amount * 100; //used for payment request button
			$mf_stripe_payment_currency = strtolower($payment_currency);

			if(!empty($payment_ask_shipping)){
				$mf_stripe_ask_shipping = 'true';
			}else{
				$mf_stripe_ask_shipping = 'false';
			}

			$payment_intent_client_secret = '';
			if(!empty($payment_stripe_enable_payment_request_button)){
				//generate the payment intent secret key
				require('lib/stripe/init.php');

				if(!empty($payment_stripe_enable_test_mode)){
					$stripe_secret_key = $payment_stripe_test_secret_key;
				}else{
					$stripe_secret_key = $payment_stripe_live_secret_key;
				}
				
				//set private key
				\Stripe\Stripe::setApiKey($stripe_secret_key);

				//create payment intent
				$payment_intent = \Stripe\PaymentIntent::create([
				    'amount' => $mf_stripe_total_payment_amount,
				    'currency' => $mf_stripe_payment_currency,
				]);

				$payment_intent_client_secret = $payment_intent->client_secret;

				$payment_request_button_markup =<<<EOT
<li id="li_payment_request_button"><div id="stripe-payment-request-button"></div></li>
<li id="li_payment_detail_title"><h4>Or enter payment details below:</h4></li>
EOT;
			}

			$merchant_js =<<<EOT
<script type="text/javascript" src="https://js.stripe.com/v3/"></script>
<script type="text/javascript">
	var mf_stripe = Stripe('{$stripe_public_key}');
	var mf_stripe_payment_intent_client_secret = '{$payment_intent_client_secret}';
	var mf_stripe_elements = mf_stripe.elements();
	var mf_stripe_total_payment_amount = {$mf_stripe_total_payment_amount};
	var mf_stripe_total_payment_label = '{$mf_lang['payment_total']}';
	var mf_stripe_account_country = '{$payment_stripe_account_country}';
	var mf_stripe_payment_currency = '{$mf_stripe_payment_currency}';
	var mf_stripe_ask_shipping = {$mf_stripe_ask_shipping};
</script>
<script type="text/javascript" src="{$machform_path}js/payment_stripe.js"></script>
EOT;
			
			//don't display credit card logo on stripe, since the card number field will automatically display it already
			$accepted_card_types = array();
			
		}else if($payment_merchant_type == 'authorizenet'){

			$merchant_js =<<<EOT
<script type="text/javascript" src="{$machform_path}js/jquery.payment.js"></script>
<script type="text/javascript" src="{$machform_path}js/payment_authorizenet.js"></script>
EOT;
		}else if($payment_merchant_type == 'paypal_rest'){

			$merchant_js =<<<EOT
<script type="text/javascript" src="{$machform_path}js/jquery.payment.js"></script>
<script type="text/javascript" src="{$machform_path}js/payment_paypal_rest.js"></script>
EOT;
			$accepted_card_types = array('visa','mastercard','amex','discover');
		}else if($payment_merchant_type == 'braintree'){
			if(!empty($payment_braintree_enable_test_mode)){
				$braintree_client_side_encryption_key = $payment_braintree_test_encryption_key;
			}else{
				$braintree_client_side_encryption_key = $payment_braintree_live_encryption_key;
			}

			$merchant_js =<<<EOT
<script type="text/javascript" src="{$machform_path}js/jquery.payment.js"></script>
<script type="text/javascript" src="https://js.braintreegateway.com/v1/braintree.js"></script>
<script>
  var mf_braintree = Braintree.create("{$braintree_client_side_encryption_key}");
</script>
<script type="text/javascript" src="{$machform_path}js/payment_braintree.js"></script>
EOT;
		}

		//build the credit card logo markup
		$credit_card_logo_markup = '';
		foreach ($accepted_card_types as $card_type) {
			$credit_card_logo_markup .= $credit_card_logos[$card_type]."\n";
		}

		$jquery_url = $machform_path.'js/jquery.min.js';

		//load custom javascript if enabled
		$custom_script_js = '';
		if(!empty($form_custom_script_enable) && !empty($form_custom_script_url)){
			$custom_script_js = '<script type="text/javascript" src="'.$form_custom_script_url.'"></script>';
		}

		if($integration_method == 'php'){

			$form_markup = <<<EOT
<link rel="stylesheet" type="text/css" href="{$machform_path}{$css_dir}view.css{$mf_version_tag}" media="all" />
<link rel="stylesheet" type="text/css" href="{$machform_path}view.mobile.css{$mf_version_tag}" media="all" />
{$theme_css_link}
{$font_css_markup}
<script type="text/javascript" src="{$jquery_url}"></script>
<script type="text/javascript" src="{$machform_path}js/jquery-ui-1.12/effect.js{$mf_version_tag}"></script>
<script type="text/javascript" src="{$machform_path}view.js{$mf_version_tag}"></script>
{$merchant_js}
{$custom_script_js}
<style>
html{
	background: none repeat scroll 0 0 transparent;
}
</style>

<div id="main_body" class="integrated no_guidelines" data-machformpath="{$machform_path}" data-recordid="{$record_id}" data-recurring="{$payment_enable_recurring}">
	<div id="form_container">
		<form id="form_{$form_id}" class="appnitro" method="post" action="javascript:" data-highlightcolor="{$field_highlight_color}">
		    <div class="form_description">
				<h2>{$form_payment_title}</h2>
				<p>{$form_payment_description}</p>
			</div>
			{$pagination_header}
			
			<ul class="payment_summary">
				<li class="payment_summary_amount total_payment" data-basetotal="{$total_payment_amount}">
					<span>
						<h3>{$currency_symbol}<var>0</var></h3>
						<h5>{$mf_lang['payment_total']}</h5>
					</span>
				</li>
				<li class="payment_summary_list">
					<ul class="payment_list_items">
						{$payment_list_items_markup}
					</ul>
				</li>
				{$payment_term_markup}
			</ul>
			<ul class="payment_detail_form">
				<li id="error_message" style="display: none">
						<h3 id="error_message_title">{$mf_lang['error_title']}</h3>
						<p id="error_message_desc">{$mf_lang['error_desc']}</p>
				</li>	
				<li id="li_accepted_cards">
					{$credit_card_logo_markup}
				</li>
				{$payment_request_button_markup}
				<li class="section_break"></li>
				{$credit_card_markup}
				<li class="section_break">
				</li>
				{$billing_address_markup}
				{$shipping_address_markup}
				<li id="li_buttons" class="buttons">
					<input type="hidden" id="form_id" value="{$form_id}" />
				    {$button_markup}
				    {$mfsid_tag}
				    <img id="mf_payment_loader_img" alt="Loading" style="display: none" src="{$machform_path}images/loader_small_grey.gif" />
				</li>
			</ul>
		</form>		
		<form id="form_payment_redirect" method="post" action="{$self_address}">
			{$mfsid_tag}
			<input type="hidden" id="form_id_redirect" name="form_id_redirect" value="{$form_id}" />
		</form>		
	</div>
</div>
EOT;
		}else{

			if($integration_method == 'iframe'){
				$auto_height_js =<<<EOT
<script type="text/javascript" src="{$machform_path}js/jquery.ba-postmessage.min.js"></script>
<script type="text/javascript">
    $(function(){
    	$.postMessage({mf_iframe_height: $('body').outerHeight(true)}, '*', parent );
    });
</script>
EOT;
			}

			$form_markup = <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xml:lang="en" {$embed_class} xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$form_name}</title>
<link rel="stylesheet" type="text/css" href="{$machform_path}{$css_dir}view.css{$mf_version_tag}" media="all" />
<link rel="stylesheet" type="text/css" href="{$machform_path}view.mobile.css{$mf_version_tag}" media="all" />
{$theme_css_link}
{$font_css_markup}
<script type="text/javascript" src="{$jquery_url}"></script>
<script type="text/javascript" src="{$machform_path}js/jquery-ui-1.12/effect.js{$mf_version_tag}"></script>
<script type="text/javascript" src="{$machform_path}view.js{$mf_version_tag}"></script>
{$merchant_js}
{$auto_height_js}
{$custom_script_js}
</head>
<body id="main_body" class="no_guidelines" data-machformpath="{$machform_path}" data-recordid="{$record_id}" data-recurring="{$payment_enable_recurring}">
	
	<div id="form_container" class="{$form_container_class}">
	
		<h1><a>MachForm</a></h1>
		<form id="form_{$form_id}" class="appnitro" method="post" action="javascript:" data-highlightcolor="{$field_highlight_color}">
		    <div class="form_description">
				<h2>{$form_payment_title}</h2>
				<p>{$form_payment_description}</p>
			</div>
			{$pagination_header}
			
			<ul class="payment_summary">
				<li class="payment_summary_amount total_payment" data-basetotal="{$total_payment_amount}">
					<span>
						<h3>{$currency_symbol}<var>0</var></h3>
						<h5>{$mf_lang['payment_total']}</h5>
					</span>
				</li>
				<li class="payment_summary_list">
					<ul class="payment_list_items">
						{$payment_list_items_markup}
					</ul>
				</li>
				{$payment_term_markup}
			</ul>
			<ul class="payment_detail_form">
				<li id="error_message" style="display: none">
						<h3 id="error_message_title">{$mf_lang['error_title']}</h3>
						<p id="error_message_desc">{$mf_lang['error_desc']}</p>
				</li>	
				<li id="li_accepted_cards">
					{$credit_card_logo_markup}
				</li>
				{$payment_request_button_markup}
				<li class="section_break"></li>
				{$credit_card_markup}
				<li class="section_break">
				</li>
				{$billing_address_markup}
				{$shipping_address_markup}
				<li id="li_buttons" class="buttons">
					<input type="hidden" id="form_id" value="{$form_id}" />
					{$mfsid_tag}
				    {$button_markup}
				    <img id="mf_payment_loader_img" alt="Loading" style="display: none" src="{$machform_path}images/loader_small_grey.gif" />
				</li>
			</ul>
		</form>		
		<form id="form_payment_redirect" method="post" action="{$self_address}">
			{$mfsid_tag}
			<input type="hidden" id="form_id_redirect" name="form_id_redirect" value="{$form_id}" />
		</form>	
	</div>
	
	</body>
</html>
EOT;
		}

		return $form_markup;
	}
	
?>
