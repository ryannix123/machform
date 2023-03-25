$(function(){
    
	//we're using tippy for the tooltip
	tippy('[data-tippy-content]',{
		trigger: 'click',
		placement: 'bottom',
		boundary: 'window',
		arrow: true
	});

	//'save integration' button being clicked
	$("#button_save_integration").click(function(){
		
		//display loader while saving
		if($("#button_save_integration").text() == 'Saving...'){
			return false;
		}

		$("#button_save_integration").text('Saving...');
		$("#button_save_integration").after("<div class='small_loader_box' style='float: left;margin-top: -8px'><img src='images/loader_small_grey.gif' /></div>");
		$("#button_remove_integration").hide();

		var form_id = $(".integration_gcal").data("formid");
		var csrf_token  = $(".integration_gcal").data("csrftoken");
		
		//send to backend using ajax call
		$.ajax({
			   	type: "POST",
			   	async: true,
			   	url: "save_integration_gcal.php",
			   	data: {
			   		   form_id: form_id,
			   		   csrf_token: csrf_token,
			   		   integration_properties: $("#gcal_settings").data('integration_properties')
					  },
			   	cache: false,
			   	global: false,
			   	dataType: "json",
			   	error: function(xhr,text_status,e){
					   //error, display the generic error message		  
			   },
			   	success: function(response_data){
					   
				   if(response_data.status == 'ok'){
					   window.location.replace('integration_gcal.php?id=' + response_data.form_id);
				   }	  
				}
		});

		return false;
	});

	//dialog box to confirm integration deletion
	$("#dialog-confirm-integration-delete").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 550,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		open: function(){
			$("#btn-confirm-integration-delete-ok").blur();
		},
		buttons: [{
				text: 'Yes. Remove Integration',
				id: 'btn-confirm-integration-delete-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					
					//disable the delete button while processing
					$("#btn-confirm-integration-delete-ok").prop("disabled",true);
						
					//display loader image
					$("#btn-confirm-integration-delete-cancel").hide();
					$("#btn-confirm-integration-delete-ok").text('Removing...');
					$("#btn-confirm-integration-delete-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
					
					var form_id = $(".integration_gcal").data("formid");
					var csrf_token  = $(".integration_gcal").data("csrftoken");
					
					//do the ajax call to remove the integration
					$.ajax({
						   type: "POST",
						   async: true,
						   url: "delete_integration_gcal.php",
						   data: {
								  	form_id: form_id,
								  	csrf_token: csrf_token
								  },
						   cache: false,
						   global: false,
						   dataType: "json",
						   error: function(xhr,text_status,e){
								   //error, display the generic error message		  
						   },
						   success: function(response_data){
									   
							   if(response_data.status == 'ok'){
								   //redirect to entries page again
								   window.location.replace('integration_settings.php?id=' + response_data.form_id);
							   }	  
									   
						   }
					});
					
				}
			},
			{
				text: 'Cancel',
				id: 'btn-confirm-integration-delete-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});

	//open the dialog when the 'remove integration' link clicked
	$("#button_remove_integration").click(function(){
		$("#dialog-confirm-integration-delete").dialog('open');
		return false;
	});

	//attach event to 'event title' textbox
	$('#gcal_event_title').bind('keyup mouseout change', function() {
		$("#gcal_settings").data('integration_properties').gcal_event_title = $(this).val();
	});

	//attach event to 'event desc' textbox
	$('#gcal_event_desc').bind('keyup mouseout change', function() {
		$("#gcal_settings").data('integration_properties').gcal_event_desc = $(this).val();
	});

	//attach event to 'event location' textbox
	$('#gcal_event_location').bind('keyup mouseout change', function() {
		$("#gcal_settings").data('integration_properties').gcal_event_location = $(this).val();
	});

	/** Event Start Date **/
	//Initialize datepicker for Event Start Date
	$('#linked_picker_gcal_start_date').datepick({ 
	    onSelect: update_gcal_start_date_linked,
	    showTrigger: '#gcal_start_date_pick_img'
	});

	//Update datepicker from three input controls for Event Start Date (mm/dd/yyyy)
	$('#gcal_start_date_mm,#gcal_start_date_dd,#gcal_start_date_yyyy').bind('blur mouseout', function() {
	    var min_dd = parseInt($('#gcal_start_date_dd').val(), 10);
	    var min_mm = parseInt($('#gcal_start_date_mm').val(), 10) - 1;
	    var min_yyyy = parseInt($('#gcal_start_date_yyyy').val(), 10);
		
	    if(!isNaN(min_dd) && !isNaN(min_mm) && !isNaN(min_yyyy) && (min_dd != 0) && (min_mm != -1)){
			
			$('#linked_picker_gcal_start_date').datepick('setDate', new Date( 
		        min_yyyy, 
		        min_mm, 
		        min_dd
		    )); 
		    
		    //update the properties
		    var new_gcal_start_date = $('#gcal_start_date_yyyy').val() + '-' + $('#gcal_start_date_mm').val() + '-' + $('#gcal_start_date_dd').val();
		    $("#gcal_settings").data('integration_properties').gcal_start_date = new_gcal_start_date;
	    }
	}); 

	//attach event to 'event start date' dropdown
	$('#start_date_dropdown').bind('change', function() {
		var start_date_val = $(this).val();

		if(start_date_val == 'specific_date'){
			$("#gcal_settings").data('integration_properties').gcal_start_date_type 	= 'datetime'; 
			$("#gcal_settings").data('integration_properties').gcal_start_date_element  = '';
			$("#start_date_specific_date_span").show();
		}else{
			$("#gcal_settings").data('integration_properties').gcal_start_date_type = 'element';
			$("#gcal_settings").data('integration_properties').gcal_start_date  	= '';
			$("#gcal_settings").data('integration_properties').gcal_start_date_element  = start_date_val;
			$("#start_date_specific_date_span").hide();
		}
		
	});

	//attach event to 'event start time' dropdown
	$('#start_time_dropdown').bind('change', function() {
		var start_time_val = $(this).val();

		if(start_time_val == 'specific_time'){
			$("#gcal_settings").data('integration_properties').gcal_start_time_type 	= 'datetime'; 
			$("#gcal_settings").data('integration_properties').gcal_start_time_element  = '';
			
			$("#gcal_settings").data('integration_properties').gcal_start_time  		= $("#start_time_specific_dropdown").val(); 
			$("#start_date_specific_time_span").show();
		}else{
			$("#gcal_settings").data('integration_properties').gcal_start_time_type = 'element';
			$("#gcal_settings").data('integration_properties').gcal_start_time  	= '';
			$("#gcal_settings").data('integration_properties').gcal_start_time_element  = start_time_val;
			$("#start_date_specific_time_span").hide();
		}
		
	});

	//attach event to 'event start time - specific time' dropdown
	$('#start_time_specific_dropdown').bind('change', function() {
		$("#gcal_settings").data('integration_properties').gcal_start_time  = $(this).val();
	});

	//attach event to 'add time' link on start date
	$("#add_start_date_time_link").click(function(){
		$("#start_date_time_span").show();
		$("#add_start_date_time_link").hide();

		return false;
	});

	/** Event End Date **/
	//Initialize datepicker for Event End Date
	$('#linked_picker_gcal_end_date').datepick({ 
	    onSelect: update_gcal_end_date_linked,
	    showTrigger: '#gcal_end_date_pick_img'
	});

	//Update datepicker from three input controls for Event End Date (mm/dd/yyyy)
	$('#gcal_end_date_mm,#gcal_end_date_dd,#gcal_end_date_yyyy').bind('blur mouseout', function() {
	    var min_dd = parseInt($('#gcal_end_date_dd').val(), 10);
	    var min_mm = parseInt($('#gcal_end_date_mm').val(), 10) - 1;
	    var min_yyyy = parseInt($('#gcal_end_date_yyyy').val(), 10);
		
	    if(!isNaN(min_dd) && !isNaN(min_mm) && !isNaN(min_yyyy) && (min_dd != 0) && (min_mm != -1)){
			
			$('#linked_picker_gcal_end_date').datepick('setDate', new Date( 
		        min_yyyy, 
		        min_mm, 
		        min_dd
		    )); 
		    
		    //update the properties
		    var new_gcal_end_date = $('#gcal_end_date_yyyy').val() + '-' + $('#gcal_end_date_mm').val() + '-' + $('#gcal_end_date_dd').val();
		    $("#gcal_settings").data('integration_properties').gcal_end_date = new_gcal_end_date;
	    }
	}); 

	//attach event to 'event end date' dropdown
	$('#end_date_dropdown').bind('change', function() {
		var end_date_val = $(this).val();

		if(end_date_val == 'specific_date'){
			$("#gcal_settings").data('integration_properties').gcal_end_date_type 	 = 'datetime'; 
			$("#gcal_settings").data('integration_properties').gcal_end_date_element = '';
			$("#end_date_specific_date_span").show();
		}else{
			$("#gcal_settings").data('integration_properties').gcal_end_date_type 	  = 'element';
			$("#gcal_settings").data('integration_properties').gcal_end_date  		  = '';
			$("#gcal_settings").data('integration_properties').gcal_end_date_element  = end_date_val;
			$("#end_date_specific_date_span").hide();
		}
		
	});

	//attach event to 'event end time' dropdown
	$('#end_time_dropdown').bind('change', function() {
		var end_time_val = $(this).val();

		if(end_time_val == 'specific_time'){
			$("#gcal_settings").data('integration_properties').gcal_end_time_type 	  = 'datetime'; 
			$("#gcal_settings").data('integration_properties').gcal_end_time_element  = '';
			
			$("#gcal_settings").data('integration_properties').gcal_end_time  		  = $("#end_time_specific_dropdown").val(); 
			$("#end_date_specific_time_span").show();
		}else{
			$("#gcal_settings").data('integration_properties').gcal_end_time_type 	  = 'element';
			$("#gcal_settings").data('integration_properties').gcal_end_time  		  = '';
			$("#gcal_settings").data('integration_properties').gcal_end_time_element  = end_time_val;
			$("#end_date_specific_time_span").hide();
		}
		
	});

	//attach event to 'event end time - specific time' dropdown
	$('#end_time_specific_dropdown').bind('change', function() {
		$("#gcal_settings").data('integration_properties').gcal_end_time  = $(this).val();
	});

	//attach event to 'add time' link on end date
	$("#add_end_date_time_link").click(function(){
		$("#end_date_time_span").show();
		$("#add_end_date_time_link").hide();

		return false;
	});

	//attach event to 'all day event' checkbox
	$('#gcal_event_allday').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#gcal_settings").data('integration_properties').gcal_event_allday = 1;
			$("#event_duration_div").hide();
			$("#start_date_time_span,#add_start_date_time_link").hide();
		}else{
			$("#gcal_settings").data('integration_properties').gcal_event_allday = 0;
			$("#event_duration_div").show();

			//show the 'add time' or the time inputs
			if($("#gcal_settings").data('integration_properties') == 'element' ||
				($("#gcal_settings").data('integration_properties').gcal_start_time_type == 'datetime' && 
				 $("#gcal_settings").data('integration_properties').gcal_start_time != '00:00:00' && 
				 ($("#gcal_settings").data('integration_properties').gcal_start_time != null))	
			){
				$("#start_date_time_span").show();
			}else{
				$("#add_start_date_time_link").show();
			}
		}
	});

	//attach event to 'event duration' dropdown
	$('#gcal_event_duration_dropdown').bind('change', function() {
		var selected_duration = $(this).val();
		$("#gcal_event_end_date_div,#gcal_event_duration_custom_period_span").hide();

		if(selected_duration == 'period'){
			$("#gcal_event_duration_custom_period_span").show();

			$("#gcal_settings").data('integration_properties').gcal_duration_type  			= 'period';
			$("#gcal_settings").data('integration_properties').gcal_duration_period_length  = $("#gcal_duration_period_length").val();
			$("#gcal_settings").data('integration_properties').gcal_duration_period_unit  	= $("#gcal_duration_period_unit").val();;	
		}else if(selected_duration == 'datetime'){
			$("#gcal_event_end_date_div").show();

			$("#gcal_settings").data('integration_properties').gcal_duration_type  			= 'datetime';
		}else{
			$("#gcal_settings").data('integration_properties').gcal_duration_type  			= 'period';
			$("#gcal_settings").data('integration_properties').gcal_duration_period_length  = selected_duration;
			$("#gcal_settings").data('integration_properties').gcal_duration_period_unit  	= 'minute';	
		}
		
	});

	//attach event to 'event duration - custom period value' textbox
	$('#gcal_duration_period_length').bind('keyup mouseout change', function() {
		$("#gcal_settings").data('integration_properties').gcal_duration_period_length = $(this).val();
	});

	//attach event to 'event duration - custom period unit' dropdown
	$('#gcal_duration_period_unit').bind('keyup mouseout change', function() {
		$("#gcal_settings").data('integration_properties').gcal_duration_period_unit = $(this).val();
	});

	//attach event to 'attendee name' dropdown
	$('#gcal_attendee_email_dropdown').bind('change', function() {
		$("#gcal_settings").data('integration_properties').gcal_attendee_email = $(this).val();
	});

	//attach event to 'calendar name' dropdown
	$('#gcal_calendar_name').bind('change', function() {
		$("#gcal_settings").data('integration_properties').gcal_calendar_id = $(this).val();

		$("#connected_calendar_name").text($("#gcal_calendar_name option:selected").text());
	});

	//attach event to the 'more options' link
	$("#gcal_show_option_switcher").click(function(){

		var current_text = $(this).text();
		if(current_text == 'more options'){
			$("#integration_more_options").slideDown();
			$(this).text('hide options');
		}else{
			$("#integration_more_options").hide();
			$(this).text('more options');
		}
		return false;
	});

	//attach event to 'delay adding events until payment completed' checkbox
	$('#gcal_delay_notification_until_paid').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#gcal_settings").data('integration_properties').gcal_delay_notification_until_paid = 1;
		}else{
			$("#gcal_settings").data('integration_properties').gcal_delay_notification_until_paid = 0;
		}
	});

	//attach event to 'delay adding events until entry approved' checkbox
	$('#gcal_delay_notification_until_approved').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#gcal_settings").data('integration_properties').gcal_delay_notification_until_approved = 1;
		}else{
			$("#gcal_settings").data('integration_properties').gcal_delay_notification_until_approved = 0;
		}
	});

	//dialog box for merge tags
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

});

/** Functions **/

//Event Start Date
//this function being used to update three inputs (mm/dd/yyyy) to match the selection from the datepicker
function update_gcal_start_date_linked(dates) { 
    $('#gcal_start_date_mm').val(dates.length ? dates[0].getMonth() + 1 : ''); 
    $('#gcal_start_date_dd').val(dates.length ? dates[0].getDate() : ''); 
    $('#gcal_start_date_yyyy').val(dates.length ? dates[0].getFullYear() : ''); 
    
    //update the properties
    var new_gcal_start_date = $('#gcal_start_date_yyyy').val() + '-' + $('#gcal_start_date_mm').val() + '-' + $('#gcal_start_date_dd').val();
    $("#gcal_settings").data('integration_properties').gcal_start_date = new_gcal_start_date;
}

//Event End Date
//this function being used to update three inputs (mm/dd/yyyy) to match the selection from the datepicker
function update_gcal_end_date_linked(dates) { 
    $('#gcal_end_date_mm').val(dates.length ? dates[0].getMonth() + 1 : ''); 
    $('#gcal_end_date_dd').val(dates.length ? dates[0].getDate() : ''); 
    $('#gcal_end_date_yyyy').val(dates.length ? dates[0].getFullYear() : ''); 
    
    //update the properties
    var new_gcal_end_date = $('#gcal_end_date_yyyy').val() + '-' + $('#gcal_end_date_mm').val() + '-' + $('#gcal_end_date_dd').val();
    $("#gcal_settings").data('integration_properties').gcal_end_date = new_gcal_end_date;
}