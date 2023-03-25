$(function(){
    
	/***************************************************************************************************************/	
	/* 1. Load Tooltips															   				   				   */
	/***************************************************************************************************************/
	
	//we're using tippy for the tooltip
	tippy('[data-tippy-content]',{
		trigger: 'click',
		placement: 'bottom',
		boundary: 'window',
		arrow: true
	});
	
	/***************************************************************************************************************/	
	/* 2. 'Send Notification Emails to My Inbox' pane 									 		  				   */
	/***************************************************************************************************************/
	
	//Attach event to "more options" link on 'Send Notification Emails to My Inbox'
	$("#more_option_myinbox").click(function(){
		if($(this).text() == 'more options'){
			//expand more options
			$("#ns_box_myinbox .ns_box_more").slideDown();
			$(this).text('hide options');
		}else{
			$("#ns_box_myinbox .ns_box_more").slideUp();
			$(this).text('more options');
		}

		return false;
	});

	//attach event to 'send notification to my inbox' checkbox
	$("#esl_enable").click(function(){
		if($(this).prop("checked") == true){
			$("#ns_box_myinbox .ns_box_email").slideDown();
			$("#ns_box_myinbox .ns_box_more_switcher").slideDown();
		}else{
			$("#ns_box_myinbox .ns_box_email").slideUp();
			$("#ns_box_myinbox .ns_box_more").slideUp();
			$("#ns_box_myinbox .ns_box_more_switcher").slideUp();
			$("#more_option_myinbox").text('more options');
		}
	});

	//attach event to From Name dropdown
	$('#esl_from_name').bind('change', function() {
		if($(this).val() == 'custom'){
			$("#esl_from_name_custom_span").show();
		}else{
			$("#esl_from_name_custom_span").hide();
		}
	});

	//attach event to Reply-To Email dropdown
	$('#esl_replyto_email_address').bind('change', function() {
		if($(this).val() == 'custom'){
			$("#esl_replyto_email_address_custom_span").show();
		}else{
			$("#esl_replyto_email_address_custom_span").hide();
		}
	});

	//attach event to 'attach completed form data as PDF' checkbox
	$("#esl_pdf_enable").click(function(){
		if($(this).prop("checked") == true){
			$("#div_esl_pdf_content").slideDown();
		}else{
			$("#div_esl_pdf_content").hide();
		}
	});

	/***************************************************************************************************************/	
	/* 3. 'Send Confirmation Email to User' pane 										 		  				   */
	/***************************************************************************************************************/
	
	//Attach event to "more options" link on 'Send Notification Emails to My Inbox'
	$("#more_option_confirmation_email").click(function(){
		if($(this).text() == 'more options'){
			//expand more options
			$("#ns_box_user_email .ns_box_more").slideDown();
			$(this).text('hide options');
		}else{
			$("#ns_box_user_email .ns_box_more").slideUp();
			$(this).text('more options');
		}
 
		return false;
	});

	//attach event to 'send notification to my inbox' checkbox
	$("#esr_enable").click(function(){
		if($(this).prop("checked") == true){
			$("#ns_box_user_email .ns_box_email").slideDown();
			$("#ns_box_user_email .ns_box_more_switcher").slideDown();
		}else{
			$("#ns_box_user_email .ns_box_email").slideUp();
			$("#ns_box_user_email .ns_box_more").slideUp();
			$("#ns_box_user_email .ns_box_more_switcher").slideUp();
			$("#more_option_confirmation_email").text('more options');
		}
	});

	//attach event to From Name dropdown
	$('#esr_from_name').bind('change', function() {
		if($(this).val() == 'custom'){
			$("#esr_from_name_custom_span").show();
		}else{
			$("#esr_from_name_custom_span").hide();
		}
	});

	//attach event to Reply-To Email dropdown
	$('#esr_replyto_email_address').bind('change', function() {
		if($(this).val() == 'custom'){
			$("#esr_replyto_email_address_custom_span").show();
		}else{
			$("#esr_replyto_email_address_custom_span").hide();
		}
	});

	//attach event to 'attach completed form data as PDF' checkbox
	$("#esr_pdf_enable").click(function(){
		if($(this).prop("checked") == true){
			$("#div_esr_pdf_content").slideDown();
		}else{
			$("#div_esr_pdf_content").hide();
		}
	});

	/***************************************************************************************************************/	
	/* 4. 'Send Form Data to Another Website' pane 										 		  				   */
	/***************************************************************************************************************/

	//delegate click event to the 'delete param' (-) icon
	$('#ns_webhook_parameters').delegate('a.a_delete_webhook_param', 'click', function(e) {
		
		if($("#ns_webhook_parameters > li.ns_url_params").length <= 1){
			$("#ui-dialog-title-dialog-warning").html('Unable to delete!');
			$("#dialog-warning-msg").html("You can't delete all parameter. <br />You must have at least one parameter.");
			$("#dialog-warning").dialog('open');
		}else{
			$(this).parent().parent().fadeOut(function(){
				$(this).remove();
			});
		}

		return false;
    });

    //attach click event to the 'add param' (+) icon
    $("#ns_add_webhook_param").click(function(){
    	var temp = $("#ns_webhook_parameters > li.ns_url_params:last .ns_param_name > input").attr("id").split("_");
    	var new_param_number = parseInt(temp[1]) + 1;

    	var new_param_markup = '<li class="ns_url_params">' + 
									'<div class="ns_param_name">' +
										'<input id="webhookname_' + new_param_number + '" name="webhookname_' + new_param_number + '" class="element text" style="width: 100%" value="" type="text">' +
									'</div>' +
									'<div class="ns_param_spacer">' + 
										'&#8674;' + 
									'</div>' + 
									'<div class="ns_param_value">' + 
										'<input id="webhookvalue_' + new_param_number + '" name="webhookvalue_' + new_param_number + '" class="element text" style="width: 100%" value="" type="text">' + 
									'</div>' + 
									'<div class="ns_param_control">' + 
										'<a class="a_delete_webhook_param" name="deletewebhookparam_' + new_param_number + '" id="deletewebhookparam_' + new_param_number + '" href="#"><span class="icon-minus-circle2"></span></a>' +
									'</div>' + 
								'</li>';

    	$(".ns_url_add_param").before(new_param_markup);
    	$("#ns_webhook_parameters > li.ns_url_params:last").hide().slideDown();

    	return false;
    })

	//attach event to 'send form data to another website' checkbox
	$("#webhook_enable").click(function(){
		if($(this).prop("checked") == true){
			$("#ns_box_url_notification .ns_box_content").slideDown();
		}else{
			$("#ns_box_url_notification .ns_box_content").slideUp();
		}
	});

	//attach event to 'Send Key-Value Pairs' radio button
	$("#webhook_data_format_key_value").click(function(){
		$("#ns_webhook_raw_div").hide();
		$("#ns_webhook_parameters_label,#ns_webhook_parameters").show();
	});

	//attach event to 'Send Raw Data' radio button
	$("#webhook_data_format_raw").click(function(){
		$("#ns_webhook_parameters_label,#ns_webhook_parameters").hide();
		$("#ns_webhook_raw_div").show();
	});

	//attach event to 'use http authentication' checkbox
	$("#webhook_enable_http_auth").click(function(){
		if($(this).prop("checked") == true){
			$("#ns_http_auth_div").slideDown();
		}else{
			$("#ns_http_auth_div").slideUp();
		}
	});

	//attach event to 'use custom http headers ' checkbox
	$("#webhook_enable_custom_http_headers").click(function(){
		if($(this).prop("checked") == true){
			$("#ns_http_header_div").slideDown();
		}else{
			$("#ns_http_header_div").slideUp();
		}
	});
	
	/***************************************************************************************************************/	
	/* 5. Attach event to 'Save Settings' button																   */
	/***************************************************************************************************************/
	$("#button_save_notification").click(function(){
		
		if($("#button_save_notification").text() != 'Saving...'){
				
				//display loader while saving
				$("#button_save_notification").prop("disabled",true);
				$("#button_save_notification").text('Saving...');
				$("#button_save_notification").after("<div class='small_loader_box' style='float: right'><img src='images/loader_small_grey.gif' /></div>");
				
				//update webhook_param_names value
				var webhook_param_names = new Array();
				$("#ns_webhook_parameters > li.ns_url_params .ns_param_name > input").each(function(index){
					webhook_param_names[index] = $(this).attr("id");
				});

				$("#webhook_param_names").val(webhook_param_names.join(','));
				$("#ns_form").submit();
		}
		
		
		return false;
	});

	/***************************************************************************************************************/	
	/* 6. Dialog Box for template variable																		   */
	/***************************************************************************************************************/
	
	$("#dialog-template-variable").dialog({
		modal: false,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['right',150],
		draggable: true,
		resizable: false,
		buttons: [{
				text: 'Close',
				id: 'btn-change-theme-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});

	$("a.tempvar_link").click(function(){
		$("#dialog-template-variable").dialog('open');
		return false;
	});

	$("#tempvar_help_trigger a").click(function(){
		if($(this).text() == 'more info'){
			$(this).text('hide info');
			$("#tempvar_help_content").slideDown();
			$("#tempvar_value").effect("pulsate", { times:3 }, 1500);
		}else{
			$(this).text('more info');
			$("#tempvar_help_content").slideUp();
		}
		return false;
	});

	//attach event to template variable dropdown
	$('#dialog-template-variable-input').bind('change', function() {
		$("#tempvar_value").text('{' + $(this).val() + '}');
	});

	/***************************************************************************************************************/	
	/* 7. Error Message Dialog Box												   				   				   */
	/***************************************************************************************************************/

	//Generic warning dialog to be used everywhere
	$("#dialog-warning").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 600,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		open: function(){
			$(this).next().find('button').blur()
		},
		buttons: [{
			text: 'OK',
			'class': 'bb_button bb_small bb_green',
			click: function() {
				$(this).dialog('close');
			}
		}]
	});

	
});