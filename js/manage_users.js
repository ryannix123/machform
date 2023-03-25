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
	
	
	//'apply filter' button being clicked
	$("#me_filter_pane_submit").click(function(){
		
		$(this).val("Applying...");
		$(this).prop("disabled",true);
		$("#cancel_filter_pane_span").hide();
		$(this).after("<span id='field_select_loader'><img src='images/loader_small_grey.gif' style='vertical-align: middle;margin-left: 5px'/></span>");
		
		//get filter properties
		var filter_prop_array = new Array();
		$("#filter_pane li.filter_settings").each(function(index){
			filter_prop_array[index] = $(this).data("filter_properties");
		});

		//send to backend using ajax call
		$.ajax({
			   	type: "POST",
			   	async: true,
			   	url: "save_filter_users.php",
			   	data: {
					   filter_prop: filter_prop_array,
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
					   window.location.replace('manage_users.php');
				   }	  
				}
		});
	});

	//attach event to the sort dropdown
	$('#me_sort_by').bind('change', function() {
		var sort_element = $(this).val();
		
		window.location.replace('manage_users.php?sortby=' + sort_element);
	});

	//delegate change event into filter field name dropdown
	$('#filter_pane').delegate('select.condition_fieldname', 'change', function(e) {
			var new_element_name = $(this).val();
			var new_element_type = $("#filter_pane").data(new_element_name);

			$(this).parent().find('.condition_text,.condition_number,.condition_admin,.condition_status,input.text').hide();

			//display the appropriate condition type dropdown, depends on the field type
			//and make sure to update the condition property value when the field type has been changed
			if(new_element_type == 'number'){
				$(this).parent().find('.condition_number,input.text').show();
				$(this).parent().data('filter_properties').condition = $(this).parent().find('.condition_number').val();
			}else if(new_element_type == 'admin'){
				$(this).parent().find('.condition_admin').show();
				$(this).parent().data('filter_properties').condition = $(this).parent().find('.condition_admin').val();
			}else if(new_element_type == 'status'){
				$(this).parent().find('.condition_status').show();
				$(this).parent().data('filter_properties').condition = $(this).parent().find('.condition_status').val();
			}else{
				$(this).parent().find('.condition_text,input.text').show();
				$(this).parent().data('filter_properties').condition = $(this).parent().find('.condition_text').val();
			}

			$(this).parent().data('filter_properties').element_name = new_element_name;
    });

    //delegate change event to the condition type dropdown
    $('#filter_pane').delegate('select.condition_text,select.condition_number,select.condition_admin,select.condition_status', 'change', function(e) {
		$(this).parent().data('filter_properties').condition = $(this).val();
    });

    //delegate event to the filter keyword text
    $('#filter_pane').delegate('input.text', 'keyup mouseout change', function(e) {
		$(this).parent().data('filter_properties').keyword = $(this).val();	
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
		width: 400,
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

		return false;
	});

	//attach click event to 'clear filter' link
	$("#me_clear_filter").click(function(){
		if($(this).text() == 'Clear Filter'){
			
			$(this).text('Clearing...');
			$("#filter_info").append("<img src='images/loader_small_grey.gif' style='position: absolute;right: -20px;top:18px'/>");
			
			var csrf_token  = $("#entries_options").data("csrftoken");

			//send to backend using ajax call
			$.ajax({
				   	type: "POST",
				   	async: true,
				   	url: "clear_filter_users.php",
				   	data: {csrf_token: csrf_token},
				   	cache: false,
				   	global: false,
				   	dataType: "json",
				   	error: function(xhr,text_status,e){
						   //error, display the generic error message		  
				   },
				   	success: function(response_data){
						   
					   if(response_data.status == 'ok'){
						   window.location.replace('manage_users.php');
					   }	  
					}
			});
		}
	});

	

	//attach event to the "select all entries on this page" checkbox
	$("#col_select").click(function(){

		var current_page_entries_total = $("#entries_table tbody > tr").length;
		var all_entries_total = $("#me_entries_total").text();

		var selection_scope = ''
		if($("#filter_info").length > 0){
			selection_scope = 'filtered results';	
		}else{
			selection_scope = 'system'	
		}

		var entries_action_row = '<tr class="entries_select_all"><td colspan="' + $("#entries_table th").length + '">All '+ current_page_entries_total + ' users on this page are selected. <a href="#" id="me_select_all">Select all <strong>'+ all_entries_total +'</strong> users in this '+ selection_scope +'</td></tr>';

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

	//dialog box to confirm users deletion
	$("#dialog-confirm-user-delete").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 550,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		open: function(){
			$("#btn-confirm-user-delete-ok").blur();
		},
		buttons: [{
				text: 'Yes. Delete selected users',
				id: 'btn-confirm-user-delete-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					
					//disable the delete button while processing
					$("#btn-confirm-user-delete-ok").prop("disabled",true);
						
					//display loader image
					$("#btn-confirm-user-delete-cancel").hide();
					$("#btn-confirm-user-delete-ok").text('Deleting...');
					$("#btn-confirm-user-delete-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
					
					var csrf_token  = $("#entries_options").data("csrftoken");
					var selected_checkboxes = $("#entries_table input[type='checkbox']:not('#col_select'):checked").serializeArray();
					
					//do the ajax call to delete the entries
					$.ajax({
						   type: "POST",
						   async: true,
						   url: "change_user_status.php",
						   data: {
								  	action: 'delete',
								  	csrf_token: csrf_token,
								  	delete_all: $("#col_select").data('full_selection'),
								  	selected_users: selected_checkboxes
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
								   window.location.replace('manage_users.php?id');
							   }	  
									   
						   }
					});
					
				}
			},
			{
				text: 'Cancel',
				id: 'btn-confirm-user-delete-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});

	//dialog box to confirm users suspension
	$("#dialog-confirm-user-suspend").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 550,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		open: function(){
			$("#btn-confirm-user-suspend-ok").blur();
		},
		buttons: [{
				text: 'Yes. Suspend selected entries',
				id: 'btn-confirm-user-suspend-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					
					//disable the delete button while processing
					$("#btn-confirm-user-suspend-ok").prop("disabled",true);
						
					//display loader image
					$("#btn-confirm-user-suspend-cancel").hide();
					$("#btn-confirm-user-suspend-ok").text('Suspending...');
					$("#btn-confirm-user-suspend-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
					
					var selected_checkboxes = $("#entries_table input[type='checkbox']:not('#col_select'):checked").serializeArray();
					var csrf_token  = $("#entries_options").data("csrftoken");

					//do the ajax call to delete the entries
					$.ajax({
						   type: "POST",
						   async: true,
						   url: "change_user_status.php",
						   data: {
								  	action: 'suspend',
								  	csrf_token: csrf_token,
								  	delete_all: $("#col_select").data('full_selection'),
								  	selected_users: selected_checkboxes
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
								   window.location.replace('manage_users.php');
							   }	  
									   
						   }
					});
					
				}
			},
			{
				text: 'Cancel',
				id: 'btn-confirm-user-suspend-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});
	
	//open the dialog when the delete user link clicked
	$("#user_delete").click(function(){
		
		var selected_entries_total = $("#entries_table input[type='checkbox']:not('#col_select'):checked").length;

		if(selected_entries_total > 0){
			//customize the message for the entry deletion dialog box
			if($("#col_select").data('full_selection') == 1){ //if all entries selected
				
				if($("#filter_info").length > 0){
					$("#ui-dialog-title-dialog-confirm-user-delete").html('Are you sure you want to delete selected users?');
					$("#dialog-confirm-user-delete-info").html("The selected users will be deleted.");	
					$("#btn-confirm-user-delete-ok").text("Yes. Delete selected users");
				}else{
					$("#ui-dialog-title-dialog-confirm-user-delete").html('Are you sure you want to delete all users?');
					$("#dialog-confirm-user-delete-info").html("<span style='font-size: 125%'>ALL users</span> (except Main Administrator) will be deleted.");	
					$("#btn-confirm-user-delete-ok").text("Yes. Delete all users");
				}
			}else{
				//if one entry selected
				if(selected_entries_total == 1){
					var temp = $("#entries_table input[type='checkbox']:not('#col_select'):checked").attr("id").split("_");

					$("#ui-dialog-title-dialog-confirm-user-delete").html('Are you sure you want to delete the user?');
					$("#dialog-confirm-user-delete-info").html("The user with <span style='font-size: 125%'>ID #" + temp[1] + "</span> will be deleted.");	
					$("#btn-confirm-user-delete-ok").text("Yes. Delete user #" + temp[1]);
				}else{ //if few entries being selected
					$("#ui-dialog-title-dialog-confirm-user-delete").html('Are you sure you want to delete selected users?');
					$("#dialog-confirm-user-delete-info").html("The selected <span style='font-size: 125%'>" + selected_entries_total + " users</span> will be deleted.");	
					$("#btn-confirm-user-delete-ok").text("Yes. Delete selected users");
				}
			}

			$("#dialog-confirm-user-delete").dialog('open');
		}else{
			$("#ui-dialog-title-dialog-warning").html('No user selected');
			$("#dialog-warning-msg").html("You haven't selected any user.<br />Please select at least one user to be deleted.");
			$("#dialog-warning").dialog('open');
		}
		
		return false;
	});

	//open the suspend confirmation dialog when the suspend link being clicked
	$("#user_suspend").click(function(){
		var selected_entries_total = $("#entries_table input[type='checkbox']:not('#col_select'):checked").length;

		if(selected_entries_total > 0){
			//customize the message for the entry deletion dialog box
			if($("#col_select").data('full_selection') == 1){ //if all entries selected
				
				if($("#filter_info").length > 0){
					$("#ui-dialog-title-dialog-confirm-user-suspend").html('Are you sure you want to suspend selected users?');
					$("#dialog-confirm-user-suspend-info").html("The selected users will be suspended.");	
					$("#btn-confirm-user-suspend-ok").text("Yes. Suspend selected users");
				}else{
					$("#ui-dialog-title-dialog-confirm-user-suspend").html('Are you sure you want to suspend all users?');
					$("#dialog-confirm-user-suspend-info").html("<span style='font-size: 125%'>ALL users</span> (except Main Administrator) will be suspended.");	
					$("#btn-confirm-user-suspend-ok").text("Yes. Suspend all users");
				}
			}else{
				//if one entry selected
				if(selected_entries_total == 1){
					var temp = $("#entries_table input[type='checkbox']:not('#col_select'):checked").attr("id").split("_");

					$("#ui-dialog-title-dialog-confirm-user-suspend").html('Are you sure you want to suspend the user?');
					$("#dialog-confirm-user-suspend-info").html("The user with <span style='font-size: 125%'>ID #" + temp[1] + "</span> will be suspended.");	
					$("#btn-confirm-user-suspend-ok").text("Yes. Suspend user #" + temp[1]);
				}else{ //if few entries being selected
					$("#ui-dialog-title-dialog-confirm-user-suspend").html('Are you sure you want to suspend selected users?');
					$("#dialog-confirm-user-suspend-info").html("The selected <span style='font-size: 125%'>" + selected_entries_total + " users</span> will be suspended.");	
					$("#btn-confirm-user-suspend-ok").text("Yes. Suspend selected users");
				}
			}

			$("#dialog-confirm-user-suspend").dialog('open');
		}else{
			$("#ui-dialog-title-dialog-warning").html('No user selected');
			$("#dialog-warning-msg").html("You haven't selected any user.<br />Please select at least one user to be suspended.");
			$("#dialog-warning").dialog('open');
		}
		
		return false;
	});
	

	$("#entries_table tbody td:not('.me_action')").click(function(){
		var temp = $(this).parent().attr("id").split("_");
		var selected_user_id = temp[1];

		window.location.href = 'view_user.php?id=' + selected_user_id;
	});

});