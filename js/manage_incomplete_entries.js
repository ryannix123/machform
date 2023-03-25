function hide_selection_pane(){
	$("#field_selection").fadeOut('fast');
	$("#entries_actions").fadeIn();
}

function show_selection_pane(){
	$("#filter_pane").hide();
	$("#entries_actions").fadeOut();
	$("#field_selection").fadeIn();	
}

function show_filter_pane(){
	$("#field_selection").hide();
	$("#entries_actions").fadeOut();
	$("#filter_pane").fadeIn();
}

function hide_filter_pane(){
	$("#filter_pane").fadeOut();
	$("#entries_actions").fadeIn();
}

function select_date(dates){
	var month = dates[0].getMonth() + 1;
	var day   = dates[0].getDate();
	var year  = dates[0].getFullYear();
	
	var temp = $(this).attr("id").split("_");
	var li_id = temp[1];

	var selected_date = month + '/' + day + '/' + year;

	$("#filterkeyword_" + li_id).val(selected_date);
	$("#li_" + li_id).data('filter_properties').keyword = selected_date;
}

$(function(){
    
    //toggle field selection pane when the 'select fields' link being clicked
	$("#entry_select_field").click(function(){
		
		if($("#field_selection").is(':visible')){
			hide_selection_pane();
		}else{
			show_selection_pane();
		}

		return false;
	});

	//display field selection pane when the 'cancel' link being clicked
	$("#field_selection_cancel").click(function(){
		
		hide_selection_pane();
		return false;
	});

	//display the filter pane when 'edit filter being clicked'
	$("#me_edit_filter").click(function(){
		show_filter_pane();
		return false;
	});

	//hide the filter pane when the cancel link being clicked
	$("#filter_pane_cancel").click(function(){
		hide_filter_pane();
		return false;
	});

	//toggle field selection pane when the 'filter entries' link being clicked
	$("#entry_filter").click(function(){
		
		if($("#filter_pane").is(':visible')){
			hide_filter_pane();
		}else{
			show_filter_pane();
		}

		return false;
	});
	
	//'apply selected fields' button being clicked
	$("#me_field_select_submit").click(function(){
		$(this).val("Applying...");
		$(this).prop("disabled",true);
		$("#cancel_field_select_span").hide();
		$(this).after("<span id='field_select_loader'><img src='images/loader_small_grey.gif' style='vertical-align: middle;margin-left: 5px'/></span>");
	
		var form_id = $("#entries_options").data("formid");
		var selected_columns = $("#field_selection input.checkbox:checked").serializeArray();
		//send to backend using ajax call
		$.ajax({
			   	type: "POST",
			   	async: true,
			   	url: "save_column_preference.php",
			   	data: {form_id: form_id,
					  col_pref: selected_columns,
					  incomplete_entries: $("#entries_table").data("incomplete")
					  },
			   	cache: false,
			   	global: false,
			   	dataType: "json",
			   	error: function(xhr,text_status,e){
					   //error, display the generic error message		  
			   },
			   	success: function(response_data){
					   
				   if(response_data.status == 'ok'){
					   window.location.replace('manage_incomplete_entries.php?id=' + response_data.form_id);
				   }	  
				}
		});
	});

	//'apply filter' button being clicked
	$("#me_filter_pane_submit").click(function(){
		
		$(this).val("Applying...");
		$(this).prop("disabled",true);
		$("#cancel_filter_pane_span").hide();
		$(this).after("<span id='field_select_loader'><img src='images/loader_small_grey.gif' style='vertical-align: middle;margin-left: 5px'/></span>");
		
		var form_id = $("#entries_options").data("formid");
		
		//get filter properties
		var filter_prop_array = new Array();
		$("#filter_pane li.filter_settings").each(function(index){
			filter_prop_array[index] = $(this).data("filter_properties");
		});

		//send to backend using ajax call
		$.ajax({
			   	type: "POST",
			   	async: true,
			   	url: "save_filter.php",
			   	data: {form_id: form_id,
					   filter_prop: filter_prop_array,
					   incomplete_entries: $("#entries_table").data("incomplete"),
					   filter_type: $("#filter_all_any").val()
					  },
			   	cache: false,
			   	global: false,
			   	dataType: "json",
			   	error: function(xhr,text_status,e){
					   //error, display the generic error message		  
			   },
			   	success: function(response_data){
					   
				   if(response_data.status == 'ok'){
					   window.location.replace('manage_incomplete_entries.php?id=' + response_data.form_id);
				   }	  
				}
		});
	});

	//attach event to the sort dropdown
	$('#me_sort_by').bind('change', function() {
		var form_id = $("#entries_options").data("formid");
		var sort_element = $(this).val();
		
		window.location.replace('manage_incomplete_entries.php?id=' + form_id + '&sortby=' + sort_element);
	});

	//delegate change event into filter field name dropdown
	$('#filter_pane').delegate('select.condition_fieldname', 'change', function(e) {
			var new_element_name = $(this).val();
			var new_element_type = $("#filter_pane").data(new_element_name);

			$(this).parent().find('.condition_text,.condition_time,.condition_number,.condition_date,.condition_file,.condition_checkbox,.condition_time,.filter_keyword,.condition_select,.condition_rating,.condition_ratingvalues').hide();
			$(this).parent().removeClass('filter_date');

			//reset keyword
			$(this).parent().data('filter_properties').keyword = '';
			$(this).parent().find('.filter_keyword').val('');
			
			//display the appropriate condition type dropdown, depends on the field type
			//and make sure to update the condition property value when the field type has been changed
			if(new_element_type == 'money' || new_element_type == 'number'){
				$(this).parent().find('.condition_number,input.text').show();
				$(this).parent().data('filter_properties').condition = $(this).parent().find('.condition_number').val();
			}else if(new_element_type == 'date' || new_element_type == 'europe_date'){
				$(this).parent().addClass('filter_date');
				$(this).parent().find('.condition_date,input.text').show();
				$(this).parent().data('filter_properties').condition = $(this).parent().find('.condition_date').val();
			}else if(new_element_type == 'time' || new_element_type == 'time_showsecond' || new_element_type == 'time_24hour' || new_element_type == 'time_showsecond24hour'){
				$(this).parent().find('.condition_date,.condition_time').show();
				
				$(this).parent().find('.condition_time .conditiontime_second,.condition_time .conditiontime_ampm').hide();
				
				if(new_element_type == 'time'){
					$(this).parent().find('.condition_time .conditiontime_ampm').show();
				}else if(new_element_type == 'time_showsecond'){
					$(this).parent().find('.condition_time .conditiontime_ampm,.condition_time .conditiontime_second').show();
				}else if(new_element_type == 'time_showsecond24hour'){
					$(this).parent().find('.condition_time .conditiontime_second').show();
				}

				$(this).parent().data('filter_properties').condition = $(this).parent().find('.condition_date').val();
			}else if(new_element_type == 'radio' || new_element_type == 'select'){
				//reset condition type
				$(this).parent().find('.condition_text').show().val('is');
				$(this).parent().data('filter_properties').condition = 'is';

				//reset condition keyword with dropdown values and display it
				$(this).parent().find('.condition_select').html($("#" + new_element_name + "_lookup").html()).show();
				$(this).parent().data('filter_properties').keyword = $(this).parent().find('.condition_select').eq(0).val();
			}else if(new_element_type == 'rating'){
				//reset condition type
				$(this).parent().find('.condition_rating').show().val('is');
				$(this).parent().data('filter_properties').condition = 'is';

				//reset condition keyword with dropdown values and display it
				$(this).parent().find('.condition_ratingvalues').html($("#" + new_element_name + "_lookup").html()).show();
				$(this).parent().data('filter_properties').keyword = $(this).parent().find('.condition_ratingvalues').eq(0).val();
			}else if(new_element_type == 'file'){
				$(this).parent().find('.condition_file,input.text').show();
				$(this).parent().data('filter_properties').condition = $(this).parent().find('.condition_file').val();
			}else if(new_element_type == 'checkbox'){
				$(this).parent().find('.condition_checkbox').show();
				$(this).parent().data('filter_properties').condition = $(this).parent().find('.condition_checkbox').val();
			}else{
				$(this).parent().find('.condition_text,input.text').show();
				$(this).parent().data('filter_properties').condition = $(this).parent().find('.condition_text').val();
			}

			$(this).parent().data('filter_properties').element_name = new_element_name;
    });

   	//delegate change event to the condition type dropdown (for number, date, checkbox, rating)
    $('#filter_pane').delegate('select.condition_number,select.condition_date,select.condition_file,select.condition_checkbox,select.condition_approval_status,select.condition_rating', 'change', function(e) {
		$(this).parent().data('filter_properties').condition = $(this).val();
    });

     //delegate change event to the condition type dropdown (for other fields beside the above)
    $('#filter_pane').delegate('select.condition_text', 'change', function(e) {
    	var element_name = $(this).parent().data('filter_properties').element_name;
    	var element_type = $("#filter_pane").data(element_name);

    	var condition_type = $(this).val();
    	
    	//if the field type is radio/dropdown, check for the selected condition type
    	//if condition type = 'is'/'is_not' , display the dropdown
    	if(element_type == 'radio' || element_type == 'select'){
    		$(this).parent().find('.filter_keyword,.condition_select').hide();

    		if(condition_type == 'is' || condition_type == 'is_not'){
    			$(this).parent().find('.condition_select').show();
    			$(this).parent().data('filter_properties').keyword = $(this).parent().find('.condition_select').eq(0).val();
    		}else{
    			$(this).parent().find('.filter_keyword').show();
    			$(this).parent().data('filter_properties').keyword = $(this).parent().find('.filter_keyword').val();
    		}
    	}

		$(this).parent().data('filter_properties').condition = condition_type;
    });

    //delegate change event to the condition select dropdown (only applicable for radio and select)
    $('#filter_pane').delegate('select.condition_select', 'change', function(e) {
		$(this).parent().data('filter_properties').keyword = $(this).val();
    });

    //delegate change event to the condition select dropdown (only applicable for rating)
    $('#filter_pane').delegate('select.condition_ratingvalues', 'change', function(e) {
		$(this).parent().data('filter_properties').keyword = $(this).val();
    });
    
    //delegate event to the filter keyword text
    $('#filter_pane').delegate('input.filter_keyword', 'keyup mouseout change', function(e) {
		$(this).parent().data('filter_properties').keyword = $(this).val();	
    });

     //delegate event to the time condition inputs
    $('#filter_pane').delegate('input.conditiontime_input,select.conditiontime_input', 'keyup mouseout change', function(e) {
		
		var temp = $(this).attr("id").split("_");

		var hour_value 	 = parseInt($("#conditiontimehour_" + temp[1]).val(),10);
		var minute_value = parseInt($("#conditiontimeminute_" + temp[1]).val(),10);
		var second_value = parseInt($("#conditiontimesecond_" + temp[1]).val(),10);
		
		var ampm_value 	 = $("#conditiontimeampm_" + temp[1]).val();

		if(isNaN(hour_value)){
			hour_value = '00';
		}

		if(isNaN(minute_value)){
			minute_value = '00';
		}
		
		if(isNaN(second_value)){
			second_value = '00';
		}

		$("#li_" + temp[1]).data('filter_properties').keyword = hour_value.toString() + ':' + minute_value.toString() + ':' + second_value.toString() + ':' + ampm_value;
    });

    //delegate click event to the delete filter condition icon
    $('#filter_pane').delegate('a.filter_delete_a', 'click', function(e) {
		
		if($("#filter_pane li:not('.filter_add')").length <= 1){
			$("#ui-dialog-title-dialog-warning").html('Unable to delete!');
			$("#dialog-warning-msg").html("You can't delete all filter condition! <br />You must have at least 1 filter condition.");
			$("#dialog-warning").dialog('open');
		}else{
			$(this).parent().fadeOut(function(){
				$(this).remove();
			});
		}

		return false;
    });

    
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

	//export entries dialog box
	$("#dialog-export-entries").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 500,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		open: function(){
			$(this).next().find('button').blur()
		},
		buttons: [{
			text: 'Close',
			'class': 'bb_button bb_small bb_green',
			click: function() {
				$(this).dialog('close');
			}
		}]
	});

	//attach click event to 'add filter condition' icon
	$("#filter_add_a").click(function(){
		var new_id = $("#filter_pane li:not('.filter_add')").length + 1;
		var old_id = new_id - 1;
		
		//duplicate the last filter condition
		var last_filter_element = $("#filter_pane ul > li:not('.filter_add')").last();
		last_filter_element.clone(false).data('filter_properties',$.extend('{}',last_filter_element.data('filter_properties'))).find("*[id],*[name]").each(function() {
			var temp = $(this).attr("id").split("_"); 
			var old_id = new_id - 1;

			//rename the original id with the new id
			$(this).attr("id", temp[0] + "_" + new_id);
			$(this).attr("name", temp[0] + "_" + new_id);
			
		}).end().attr("id","li_" + new_id).insertBefore("#li_filter_add").hide().fadeIn();

		//copy the value of the dropdowns
		$("#filterfield_" + new_id).val($("#filterfield_" + old_id).val());
		$("#conditiontext_" + new_id).val($("#conditiontext_" + old_id).val());
		$("#conditionnumber_" + new_id).val($("#conditionnumber_" + old_id).val());
		$("#conditiondate_" + new_id).val($("#conditiondate_" + old_id).val());
		$("#conditionfile_" + new_id).val($("#conditionfile_" + old_id).val());
		$("#conditioncheckbox_" + new_id).val($("#conditioncheckbox_" + old_id).val());
		
		//reset the filter keyword  
		$("#filterkeyword_" + new_id).val('');
		$("#li_" + new_id).data('filter_properties').keyword = '';

		//remove the datepicker and rebuild it, with the events as well
		$('#datepicker_' + new_id).next().next().remove();
		$('#datepicker_' + new_id).next().remove();
		$('#datepicker_' + new_id).remove();

		var new_datepicker_tag = '<input type="hidden" value="" name="datepicker_'+ new_id +'" id="datepicker_'+ new_id +'">' +
								 '<span style="display:none"><img id="datepickimg_'+ new_id +'" alt="Pick date." src="images/icons/calendar.png" class="trigger filter_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

		$('#filterkeyword_' + new_id).after(new_datepicker_tag);

		$('#datepicker_' + new_id).datepick({ 
	    		onSelect: select_date,
	    		showTrigger: '#datepickimg_' + new_id
		});

		return false;
	});

	//attach click event to 'clear filter' link
	$("#me_clear_filter").click(function(){
		if($(this).text() == 'Clear Filter'){
			
			$(this).text('Clearing...');
			$("#filter_info").append("<img src='images/loader_small_grey.gif' style='position: absolute;right: -20px;top:18px'/>");
			
			var form_id = $("#entries_options").data("formid");
			var csrf_token  = $("#entries_options").data("csrftoken");

			//send to backend using ajax call
			$.ajax({
				   	type: "POST",
				   	async: true,
				   	url: "clear_filter.php",
				   	data: {
				   			form_id: form_id,
				   			csrf_token: csrf_token,
				   			incomplete_entries: $("#entries_table").data("incomplete")
				   		  },
				   	cache: false,
				   	global: false,
				   	dataType: "json",
				   	error: function(xhr,text_status,e){
						   //error, display the generic error message		  
				   },
				   	success: function(response_data){
						   
					   if(response_data.status == 'ok'){
						   window.location.replace('manage_incomplete_entries.php?id=' + response_data.form_id);
					   }	  
					}
			});
		}
	});

	//initialize datepicker
	var total_filter = $("#filter_pane li").length - 1;
	for(i=1;i<=total_filter;i++){
		$('#datepicker_' + i).datepick({ 
	    		onSelect: select_date,
	    		showTrigger: '#datepickimg_' + i
		});
	}	

	//attach event to the "select all entries on this page" checkbox
	$("#col_select").click(function(){

		var current_page_entries_total = $("#entries_table tbody > tr").length;
		var all_entries_total = $("#me_entries_total").text();

		var selection_scope = ''
		if($("#filter_info").length > 0){
			selection_scope = 'filtered results';	
		}else{
			selection_scope = 'form'	
		}

		var entries_action_row = '<tr class="entries_select_all"><td colspan="' + $("#entries_table th").length + '">All '+ current_page_entries_total + ' entries on this page are selected. <a href="#" id="me_select_all">Select all <strong>'+ all_entries_total +'</strong> entries in this '+ selection_scope +'</td></tr>';

		if($(this).prop("checked") == true){
			//select all checkbox
			$("#entries_table input[type='checkbox']:not('#col_select')").prop("checked",true);
			$("#entries_table tr").addClass('me_row_selected');
			$("#entries_table tbody").prepend(entries_action_row);
		}else{
			//deselect all checkbox
			$("#entries_table input[type='checkbox']:not('#col_select')").prop("checked",false);
			$("#entries_table tr.me_row_selected").removeClass('me_row_selected');
			$("#entries_table .entries_select_all").remove();
			$("#col_select").data('full_selection',0);
		}
	});

	//attach event to the "select all entries on this form/filtered results" link
	$("#entries_table").delegate("#me_select_all","click",function(){

		var all_entries_total = $("#me_entries_total").text();

		var selection_scope = ''
		if($("#filter_info").length > 0){
			selection_scope = 'filtered results';	
		}else{
			selection_scope = 'form'	
		}

		var info_all_selected = 'All ' + all_entries_total + ' entries in this ' + selection_scope + ' are selected. <a id="me_clear_selection" href="#">Clear selection</a>'
		$(".entries_select_all td").html(info_all_selected);

		$("#col_select").data('full_selection',1);

		return false;
	});

	//attach event to the "clear selection" link
	$("#entries_table").delegate("#me_clear_selection","click",function(){

		$("#entries_table input[type='checkbox']").prop("checked",false);
		$("#entries_table .entries_select_all").remove();
		$("#entries_table tr.me_row_selected").removeClass('me_row_selected');

		$("#col_select").data('full_selection',0);

		return false;
	});

	//attach event to the checkbox of each row
	$("#entries_table input[type='checkbox']:not('#col_select')").click(function(){
		if($(this).prop("checked") == true){
			$(this).parent().parent().addClass("me_row_selected");	
		}else{
			$(this).parent().parent().removeClass("me_row_selected");
			$("#entries_table .entries_select_all").remove();
			$("#col_select").prop("checked",false);
			$("#col_select").data('full_selection',0);
		}
	});

	//initialize the selection status. initially none of the entries are being selected
	$("#col_select").data('full_selection',0);

	//dialog box to confirm entries deletion
	$("#dialog-confirm-entry-delete").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 550,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		open: function(){
			$("#btn-confirm-entry-delete-ok").blur();
		},
		buttons: [{
				text: 'Yes. Delete selected entries',
				id: 'btn-confirm-entry-delete-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					
					//disable the delete button while processing
					$("#btn-confirm-entry-delete-ok").prop("disabled",true);
						
					//display loader image
					$("#btn-confirm-entry-delete-cancel").hide();
					$("#btn-confirm-entry-delete-ok").text('Deleting...');
					$("#btn-confirm-entry-delete-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
					
					var form_id = $("#entries_options").data("formid");
					var selected_checkboxes = $("#entries_table input[type='checkbox']:not('#col_select'):checked").serializeArray();
					var csrf_token  = $("#entries_options").data("csrftoken");

					//do the ajax call to delete the entries
					$.ajax({
						   type: "POST",
						   async: true,
						   url: "delete_entries.php",
						   data: {
								  	form_id: form_id,
								  	csrf_token: csrf_token,
								  	delete_all: $("#col_select").data('full_selection'),
								  	incomplete_entries: $("#entries_table").data("incomplete"),
								  	selected_entries: selected_checkboxes
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
								   window.location.replace('manage_incomplete_entries.php?id=' + response_data.form_id);
							   }	  
									   
						   }
					});
					
				}
			},
			{
				text: 'Cancel',
				id: 'btn-confirm-entry-delete-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});
	
	//open the dialog when the delete entry link clicked
	$("#entry_delete").click(function(){
		
		var selected_entries_total = $("#entries_table input[type='checkbox']:not('#col_select'):checked").length;

		if(selected_entries_total > 0){
			//customize the message for the entry deletion dialog box
			if($("#col_select").data('full_selection') == 1){ //if all entries selected
				
				if($("#filter_info").length > 0){
					$("#ui-dialog-title-dialog-confirm-entry-delete").html('Are you sure you want to delete selected entries?');
					$("#dialog-confirm-entry-delete-info").html("Data and files associated with your selected <span style='font-size: 125%'>"+ $("#me_entries_total").text() + " entries</span> will be deleted.");	
					$("#btn-confirm-entry-delete-ok").text("Yes. Delete selected entries");
				}else{
					$("#ui-dialog-title-dialog-confirm-entry-delete").html('Are you sure you want to delete all entries?');
					$("#dialog-confirm-entry-delete-info").html("Data and files associated with <span style='font-size: 125%'>ALL entries</span> will be deleted.");	
					$("#btn-confirm-entry-delete-ok").text("Yes. Delete all entries");
				}
			}else{
				//if one entry selected
				if(selected_entries_total == 1){
					var temp = $("#entries_table input[type='checkbox']:not('#col_select'):checked").attr("id").split("_");

					$("#ui-dialog-title-dialog-confirm-entry-delete").html('Are you sure you want to delete the entry?');
					$("#dialog-confirm-entry-delete-info").html("Data and files associated with <span style='font-size: 125%'>entry #" + temp[1] + "</span> will be deleted.");	
					$("#btn-confirm-entry-delete-ok").text("Yes. Delete entry #" + temp[1]);
				}else{ //if few entries being selected
					$("#ui-dialog-title-dialog-confirm-entry-delete").html('Are you sure you want to delete selected entries?');
					$("#dialog-confirm-entry-delete-info").html("Data and files associated with your selected <span style='font-size: 125%'>" + selected_entries_total + " entries</span> will be deleted.");	
					$("#btn-confirm-entry-delete-ok").text("Yes. Delete selected entries");
				}
			}

			$("#dialog-confirm-entry-delete").dialog('open');
		}else{
			$("#ui-dialog-title-dialog-warning").html('No entry selected');
			$("#dialog-warning-msg").html("You haven't selected any entry.<br />Please select at least one entry to be deleted.");
			$("#dialog-warning").dialog('open');
		}
		
		return false;
	});

	//open the export dialog when the export link being clicked
	$("#entry_export").click(function(){
		$("#dialog-export-entries").dialog('open');
		return false;
	});
	
	//send the exported file to the user when the export file type link beinhg clicked
	$("#dialog-export-entries a").click(function(e){
		var form_id 	   	    = $("#entries_options").data("formid");
		var csrf_token  		= $("#entries_options").data("csrftoken");
		var full_selection      = $("#col_select").data('full_selection');
		var selected_checkboxes = $("#entries_table input[type='checkbox']:not('#col_select'):checked");
		var clicked_link		= $(this).attr("id");
		var export_type			= 'xls';
		var selected_fields		= '';

		if(clicked_link == 'export_as_excel'){
			export_type = 'xls';	
		}if(clicked_link == 'export_as_csv'){
			export_type = 'csv';
		}if(clicked_link == 'export_as_txt'){
			export_type = 'txt';
		}

		//check for export all/selection
		if($("#export_selected").prop("checked") === true){
			selected_fields = 'selected_fields=1';
		}else{
			selected_fields = 'selected_fields=0';
		}

		e.preventDefault();  //stop the browser from following the redirect
		
		if(full_selection == 1){ //all entries being selected
			window.location.href = 'export_entries.php?type='+ export_type +'&form_id=' + form_id + '&csrf_token=' + csrf_token + '&incomplete_entries='+ $("#entries_table").data("incomplete") + '&' + selected_fields;
		}else{
			if(selected_checkboxes.length > 0){ //some entries being selected
				var selected_entries = selected_checkboxes.serializeArray();
				var selected_entries_id = new Array();

				$.each(selected_entries, function(index, value) {
				    selected_entries_id[index] = (value.name.split('_'))[1];
				});

				window.location.href = 'export_entries.php?type='+ export_type +'&form_id=' + form_id + '&csrf_token=' + csrf_token + '&entries_id=' + selected_entries_id.join(',') + '&incomplete_entries='+ $("#entries_table").data("incomplete") + '&' + selected_fields;
			}else{ //none of the entries being selected, consider this as full selection as well
				window.location.href = 'export_entries.php?type='+ export_type +'&form_id=' + form_id + '&csrf_token=' + csrf_token + '&incomplete_entries=' + $("#entries_table").data("incomplete") + '&' + selected_fields;
		
			}
		}
				
		return false;
	});

	$("#entries_table tbody td:not('.me_action')").click(function(){
		var form_id 	   	    = $("#entries_options").data("formid");

		var temp = $(this).parent().attr("id").split("_");
		var selected_entry_id = temp[1];

		window.location.href = 'view_entry.php?form_id=' + form_id + '&entry_id=' + selected_entry_id;
	});

});