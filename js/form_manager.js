//code for overloading the :contains selector to be case insensitive
jQuery.expr[':'].Contains = function(a, i, m) {
  return jQuery(a).text().toUpperCase()
      .indexOf(m[3].toUpperCase()) >= 0;
};
jQuery.expr[':'].contains = function(a, i, m) {
  return jQuery(a).text().toUpperCase()
      .indexOf(m[3].toUpperCase()) >= 0;
};

var selected_form_id = null; 
var tippy_form_id = null; //the selected form_id when the tippy tooltip shown

$(function(){
    
	/***************************************************************************************************************/	
	/* 1. Attach events to Form Title															   				   */
	/***************************************************************************************************************/
	
	//expand the form list when being clicked
	$(".middle_form_bar > h3").click(function(){
		
		var selected_form_li_id = $(this).parent().parent().attr('id');
	
		//show or hide all the options
		$("#" + selected_form_li_id + " .bottom_form_bar").slideToggle('fast');
		
		//once all options has been shown/hide, toggle the parent class
		$("#" + selected_form_li_id + " .bottom_form_bar").promise().done(function() {
			$("#" + selected_form_li_id).toggleClass('form_selected');
		});

	});
	
	
	/***************************************************************************************************************/	
	/* 2. Attach events to 'Disable' link														   				   */
	/***************************************************************************************************************/
	

	//Dialog box to disable a form
	$("#dialog-disabled-message").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 490,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		open: function(){
			//populate the current message
			var current_message = $("#liform_" + selected_form_id).data("form_disabled_message");
			
			if(current_message == "" || current_message == null){
				current_message = 'This form is currently inactive.';
			}
			$("#dialog-disabled-message-input").val(current_message);
		},
		buttons: [{
				text: 'Yes. Disable this form',
				id: 'dialog-disabled-message-btn-save',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					
					if($("#dialog-disabled-message-input").val() == ""){
						alert('Please enter a message!');
					}else{
						
						//disable the save changes button while processing
						$("#dialog-disabled-message-btn-save").prop("disabled",true);
						
						//display loader image
						$("#dialog-disabled-message-btn-cancel").hide();
						$("#dialog-disabled-message-btn-save").text('Processing...');
						$("#dialog-disabled-message-btn-save").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
						
						//do the ajax call to disable the form						
						$.ajax({
							   type: "POST",
							   async: true,
							   url: "toggle_form.php",
							   data: {
									  form_id: selected_form_id,
									  action: 'disable',
									  disabled_message: $("#dialog-disabled-message-input").val()
									 },
							   cache: false,
							   global: true,
							   dataType: "json",
							   error: function(xhr,text_status,e){
								  alert('Error! Unable to process');
							   },
							   success: function(response_data){
								   
								   if(response_data.status == 'ok'){
									   
								   		//restore the buttons and close the dialog box
								   	   	$("#dialog-disabled-message-btn-save").prop("disabled",false);
								       	$("#dialog-disabled-message-btn-cancel").show();
									   	$("#dialog-disabled-message-btn-save").text('Yes. Disable this form');
									  	$("#dialog-disabled-message-btn-save").next().remove();

									   	$("#dialog-disabled-message").dialog('close');
								   	   
								   	   	//update the dom data
								   	   	$("#liform_" + selected_form_id).data("form_disabled_message",$("#dialog-disabled-message-input").val());

									   	if(response_data.action == 'disable'){
										   $("#liform_" + response_data.form_id).addClass('form_inactive');
										   $("#action_toggle_content_" + response_data.form_id + " .mf_link_disable a").html('<span class="icon-play-circle"></span> Enable');
										   $("#form_action_" + response_data.form_id)[0]._tippy.setContent($("#action_toggle_content_" + response_data.form_id).html());
									   	}
									   
								   }
								   
							   }
						}); //end of ajax call
						
					}
				}
			},
			{
				text: 'Cancel',
				id: 'dialog-disabled-message-btn-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});
	
	
	/***************************************************************************************************************/	
	/* 3. Attach events to pagination buttons													   				   */
	/***************************************************************************************************************/
	
	$("#mf_pagination > li").click(function(){
		var display_list = $(this).data('liform_list');
		
		$("#mf_form_list > li").hide();
		$(display_list).show();
		
		$("#mf_pagination > li.current_page").removeClass('current_page');
		$(this).addClass('current_page');
	});
	
	
	/***************************************************************************************************************/	
	/* 4. Attach events to search input															   				   */
	/***************************************************************************************************************/
	
	//expand the search box
	$("#filter_form_input").bind('focusin click',function(){
		
		if($("#filter_form_input").val() == 'find form...'){
			$("#filter_form_input").val('');
	
			if(screen.width >= 480){
				$("#mf_search_box,#filter_form_input").animate({'width': '+=165px'},{duration:200,queue:false});
			}
			
			$("#mf_search_box,#filter_form_input").promise().done(function() {
				$("#mf_search_title,#mf_search_tag").slideDown('medium');
				
				$("#mf_search_title,#mf_search_tag").promise().done(function(){
					$("#mf_search_box").addClass('search_focused');
					$("#mf_search_box,#filter_form_input").removeAttr('style');
				});
			});	
		}
		
		//shrink all opened forms
		$('.bottom_form_bar').hide();
		$(".form_selected").removeClass('form_selected');
		
	});
	
	//attach event to 'form title / form tags' tabs
	$("#mf_search_title").click(function(){
		$(this).addClass('mf_pane_selected');
		$("#mf_search_title a").html('&#8674; form title');
		
		$("#mf_search_tag a").html('form tags');
		$("#mf_search_tag").removeClass('mf_pane_selected');
		$("#filter_form_input").val('');
		
		//restore back the filter to the original condition
		reset_form_filter();
		
		$("#filter_form_input").focus();
		
		return false;
	});
	
	$("#mf_search_tag").click(function(){
		$(this).addClass('mf_pane_selected');
		$("#mf_search_tag a").html('&#8674; form tags')
		
		$("#mf_search_title a").html('form title');
		$("#mf_search_title").removeClass('mf_pane_selected');
		$("#filter_form_input").val('');
		
		//restore back the filter to the original condition
		reset_form_filter();
		
		$("#filter_form_input").focus();
		
		return false;
	});
	
	
	//filter the form when user type the search term
	$("#filter_form_input").keyup(function(){
		var search_term = $(this).val();
		var max_search_result = 10;
		
		
		if(search_term != ''){
			//first hide all form
			$("#mf_form_list > li").removeClass('result_set').hide();
			
			//hide pagination
			$("#mf_pagination").hide();
			
			if($("#mf_search_title").hasClass('mf_pane_selected')){ //search on form title
				var result_h3 = $("#mf_form_list h3:contains('"+ search_term + "')");
				
				result_h3.parent().parent().show().addClass('result_set');
				result_h3.unhighlight();
				result_h3.highlight(search_term);
				
				$("#filtered_result_box span.highlight").text(search_term);
				$("#filtered_result_box").fadeIn();
				
				$("#filtered_result_total").text('Found ' + result_h3.length + ' forms');
				
				if(result_h3.length == 0){
					$("#filtered_result_none").fadeIn();
				}else{
					$("#filtered_result_none").hide();
				}
				
				//if the result set exceed the limit, hide the rest and display "show more" button
				if(result_h3.length > max_search_result){
					$("#result_set_show_more").show();
					
					$(".result_set:gt("+ (max_search_result - 1) + ")").hide();
				}else{
					$("#result_set_show_more").hide();
				}
			}else{ //search on form tags
				var result_li = $("ul.form_tag_list li:contains('"+ search_term + "')");
				
				result_li.parent().parent().parent().parent().parent().show().addClass('result_set');
				result_li.unhighlight();
				result_li.highlight(search_term);
				
				$("#filtered_result_box span.highlight").text(search_term);
				$("#filtered_result_box").fadeIn();
				
				$("#filtered_result_total").text('Found ' + result_li.length + ' forms');
				
				if(result_li.length == 0){
					$("#filtered_result_none").fadeIn();
				}else{
					$("#filtered_result_none").hide();
				}
				
				//if the result set exceed the limit, hide the rest and display "show more" button
				if(result_li.length > max_search_result){
					$("#result_set_show_more").show();
					
					$(".result_set:gt("+ (max_search_result - 1) + ")").hide();
				}else{
					$("#result_set_show_more").hide();
				}
			}
			
		}else{
			//if the filter keyword is empty, restore back to the original condition
			reset_form_filter();
			
		}
		
	});
	
	$("#mf_filter_reset").click(function(){
		reset_form_filter();

		$("#mf_search_box").removeClass('search_focused');
		$("#mf_search_title,#mf_search_tag").hide();
		
		$("#filter_form_input").val('find form...');
		
		return false;
	});
	
	//attach event handler to "show more result" on filter result
	$("#result_set_show_more > a").click(function(){
		var show_more_increment = 20; //the number of more results being displayed each time the button being clicked
		
		var last_result_index = $(".result_set:visible").last().index('.result_set');
		var next_start_index = last_result_index + 1;
		var next_end_index   = next_start_index + show_more_increment;
		
		$(".result_set").slice(next_start_index,next_end_index).fadeIn();
		
		if(next_end_index >= $(".result_set").length){
			$("#result_set_show_more").hide();
		}
		
		return false;
	});
	
	/***************************************************************************************************************/	
	/* 5. Dialog box to enter a tag name														   				   */
	/***************************************************************************************************************/
	
	//Dialog box to assign tag names to form
	$("#dialog-enter-tagname").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center',150],
		draggable: false,
		resizable: false,
		buttons: [{
				text: 'Save Changes',
				id: 'dialog-enter-tagname-btn-save-changes',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					var form_id  = parseInt($("#dialog-enter-tagname").data('form_id'));
					
					if($("#dialog-enter-tagname-input").val() == ""){
						alert('Please enter a tag name!');
					}else{
						
						$(this).dialog('close');
						
						//display progress bar
						$("#liform_" + form_id + " ul.form_tag_list").append("<li class=\"processing\"><img src='images/loader_small_grey.gif' /></li>");
						
						//do the ajax call to save the tags
						$.ajax({
							   type: "POST",
							   async: true,
							   url: "save_tags.php",
							   data: {
										action: 'add',
										form_id: form_id,
									  	tags: $("#dialog-enter-tagname-input").val()
									  },
							   cache: false,
							   global: false,
							   dataType: "json",
							   error: function(xhr,text_status,e){
									$("#liform_" + form_id + " ul.form_tag_list li.processing").remove();
									alert('Error! Unable to add tag names. Please try again.');	  
							   },
							   success: function(response_data){
									   
								   if(response_data.status == 'ok'){
									   $("#liform_" + response_data.form_id + " li.form_tag_list_icon").siblings().remove()
									   $("#liform_" + response_data.form_id + " ul.form_tag_list").append(response_data.tags_markup);
								   }else{
									   $("#liform_" + response_data.form_id + " ul.form_tag_list li.processing").remove();
									   alert('Error! Unable to add tag names. Please try again.');
								   }
									   
							   }
						});
						
					}
				}
			},
			{
				text: 'Cancel',
				id: 'dialog-enter-tagname-btn-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});
	
	//if the user submit the form by hitting the enter key, make sure to call the button_save_theme handler
	$("#dialog-enter-tagname-form").submit(function(){
		$("#dialog-enter-tagname-btn-save-changes").click();
		return false;
	});
	
	//attach event to add form tag
	$("ul.form_tag_list a.addtag").click(function(){
		var temp = $(this).attr('id').split('_');
		
		$("#dialog-enter-tagname").data('form_id',temp[1]);
		$("#dialog-enter-tagname-input").val('');
		$("#dialog-enter-tagname").dialog('open');
		
		return false;
	});


	//attach event to 'import form' button
	$("#button_import_form").click(function(){
		$("#dialog-import-form").dialog('open');

		return false;
	});
	
	//delegate onclick event to delete tag link
	$('#mf_form_list').delegate('a.removetag', 'click', function(e) {
		
		var selected_list = $(this).parent().parent().closest('li').attr('id');
		
		var temp = selected_list.split('_');
		var form_id = parseInt(temp[1]);
		
		var selected_tagname = $(this).parent().text();
		var parent_list = $(this).parent();
		
		//do the ajax call to delete the tag
		if($(this).find('img').attr("src") != "images/loader_green_16.png"){
			$(this).find('img').attr("src","images/loader_green_16.png");
			
			//do the ajax call to save the tags
			$.ajax({
				   type: "POST",
				   async: true,
				   url: "save_tags.php",
				   data: {
							action: 'delete',
							form_id: form_id,
						  	tags: selected_tagname
						  },
				   cache: false,
				   global: false,
				   dataType: "json",
				   error: function(xhr,text_status,e){
					    parent_list.find('img').attr("src","images/icons/53.png");
						alert('Error! Unable to delete tag name. Please try again.');	  
				   },
				   success: function(response_data){
						   
					   if(response_data.status == 'ok'){
						   parent_list.fadeOut(function(){$(this).remove()});
					   }else{
						   parent_list.find('img').attr("src","images/icons/53.png");
						   alert('Error! Unable to delete tag name. Please try again.');
					   }
						   
				   }
			});
		}
		
		
		return false;
    });
	
	//initialize the tagname input box with the existing tags
	$("#dialog-enter-tagname-input").autocomplete({
	         source: $("#dialog-enter-tagname-input").data('available_tags')
	});
	
	
	/***************************************************************************************************************/	
	/* 6. Highlight particular form if the variable exist														   */
	/***************************************************************************************************************/
	
	//this is being used to highlight a newly created form, as a result of a duplicate action
	if(selected_form_id_highlight > 0){
		$("#liform_" + selected_form_id_highlight + " div.middle_form_bar").hide().fadeIn();
	}
	
	/***************************************************************************************************************/	
	/* 7. Attach events to 'Delete' link														   				   */
	/***************************************************************************************************************/
	
	//dialog box to confirm deletion
	$("#dialog-confirm-form-delete").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 550,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		open: function(){
			$("#btn-form-delete-ok").blur();
		},
		buttons: [{
				text: 'Yes. Delete this form',
				id: 'btn-form-delete-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					
					var form_id  = parseInt($("#dialog-confirm-form-delete").data('form_id'));
					var csrf_token = $("#content").data("csrftoken");
					
					$("#dropui_theme_options div.dropui-content").attr("style","");
					
					//disable the delete button while processing
					$("#btn-form-delete-ok").prop("disabled",true);
						
					//display loader image
					$("#btn-form-delete-cancel").hide();
					$("#btn-form-delete-ok").text('Deleting...');
					$("#btn-form-delete-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
					
					//do the ajax call to delete the form

					$.ajax({
						   type: "POST",
						   async: true,
						   url: "delete_form.php",
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
								   //redirect to form manager
								   window.location.replace('manage_forms.php');
							   }	  
									   
						   }
					});
					
					
				}
			},
			{
				text: 'Cancel',
				id: 'btn-form-delete-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});
	

	/***************************************************************************************************************/	
	/* 8. Attach events to 'Theme' link														   				   */
	/***************************************************************************************************************/
	
	//dialog box to change a theme 
	$("#dialog-change-theme").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		buttons: [{
				text: 'Save Changes',
				id: 'btn-change-theme-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					
					var form_id  = parseInt($("#dialog-change-theme").data('form_id'));
					var csrf_token = $("#content").data("csrftoken");
					
					//disable the delete button while processing
					$("#btn-change-theme-ok").prop("disabled",true);
						
					//display loader image
					$("#btn-change-theme-cancel").hide();
					$("#btn-change-theme-ok").text('Applying Theme...');
					$("#btn-change-theme-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
					
					//do the ajax call to delete the form
					
					$.ajax({
						   type: "POST",
						   async: true,
						   url: "change_theme.php",
						   data: {
								  	form_id: form_id,
								  	csrf_token: csrf_token,
								  	theme_id: $("#dialog-change-theme-input").val()
								  },
						   cache: false,
						   global: false,
						   dataType: "json",
						   error: function(xhr,text_status,e){
								   //error, display the generic error message	
								  $("#btn-change-theme-cancel").show();
								  $("#btn-change-theme-ok").text('Save Changes');
							      $("#btn-change-theme-ok").next().remove();
							      $("#btn-change-theme-ok").prop("disabled",false);
							      
							      alert('Error! Unable to apply the theme. Please try again.');
						   },
						   success: function(response_data){
							   
							   $("#btn-change-theme-cancel").show();
							   $("#btn-change-theme-ok").text('Save Changes');
							   $("#btn-change-theme-ok").next().remove();
							   $("#btn-change-theme-ok").prop("disabled",false);
							  
							   if(response_data.status == 'ok'){
								   $("#liform_" + form_id).data('theme_id',$("#dialog-change-theme-input").val());
								   $("#dialog-change-theme").dialog('close');
							   }else{
								   alert('Error! Unable to apply the theme. Please try again.');
							   }
									   
						   }
					});
					
				}
			},
			{
				text: 'Cancel',
				id: 'btn-change-theme-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});
	
	//open the dialog when the change theme link clicked
	$(".mf_link_theme").click(function(){
		
		var parent_li = $(this).parent().parent().parent();
		var temp = parent_li.attr('id').split('_');
		var form_id = parseInt(temp[1]);
		
		$("#dialog-change-theme").data('form_id',form_id);
		
		//set the value of the theme dropdown to the current active theme for this form
		$("#dialog-change-theme-input").val(parent_li.data('theme_id'));
		$("#dialog-change-theme").dialog('open');
		
		return false;
	});
	
	//if the user select "create new theme" on the theme selection dropdown
	$('#dialog-change-theme-input').bind('change', function() {
		if($(this).val() == "new"){
			//redirect to theme editor
			window.location.replace('edit_theme.php');
		}
	});

	/***************************************************************************************************************/	
	/* 9. Attach events to dropui buttons														   				   */
	/***************************************************************************************************************/

	$(".manage_forms a.dropui-tab").click(function(){
		if($(this).attr("id") == 'dropui-sort-form'){
			$("#dropui-filter-form").parent().removeClass("hovered");
			$("#dropui-filter-form").next().hide();
		}else if($(this).attr("id") == 'dropui-filter-form'){
			$("#dropui-sort-form").parent().removeClass("hovered");
			$("#dropui-sort-form").next().hide();
		}

		if($(this).parent().hasClass("hovered")){
			$(this).parent().removeClass('hovered');
			$(this).next().hide(); //hide the properties container
		}else{
			$(this).parent().addClass('hovered');
			$(this).next().show(); //display the properties container
		}	
	});

	/***************************************************************************************************************/	
	/* 11. Form Filters																			   				   */
	/***************************************************************************************************************/
	
	//Dialog box to save the filter
	$("#dialog-name-filter").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center',150],
		draggable: false,
		resizable: false,
		buttons: [{
				text: 'Save Changes',
				id: 'dialog-name-filter-btn-save-changes',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					if($("#dialog-name-filter-input").val() == ""){
						alert('Please enter a name for your filter!');
					}else{
						var filter_name = $("#dialog-name-filter-input").val();
						var filter_keyword = $("#filter_form_input").val();
						var filter_by 	= '';

						if($("#mf_search_box > .mf_pane_selected").attr("id") == 'mf_search_title'){
							filter_by = 'title';
						}else{
							filter_by = 'tags';
						}
					
						//disable the save changes button while processing
						$("#dialog-name-filter-btn-save-changes").prop("disabled",true);
						
						//display loader image
						$("#dialog-name-filter-btn-cancel").hide();
						$("#dialog-name-filter-btn-save-changes").text('Saving...');
						$("#dialog-name-filter-btn-save-changes").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
						
						//do the ajax call to save the filter
						$.ajax({
							   type: "POST",
							   async: true,
							   url: "save_dashboard_filter.php",
							   data: {
									  	filter_by: filter_by,
									  	filter_name: filter_name,
									  	filter_keyword: filter_keyword
									  },
							   cache: false,
							   global: false,
							   dataType: "json",
							   error: function(xhr,text_status,e){
									   //error, display the generic error message		  
							   },
							   success: function(response_data){
									   
								   if(response_data.status == 'ok'){
									   //refresh the page
									   window.location.replace('manage_forms.php');
								   }	  
									   
							   }
						});
					}
				}
			},
			{
				text: 'Cancel',
				id: 'dialog-name-filter-btn-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});

	//attach event to the 'save filter' link
	$("#save_filter").click(function(){
		$("#dialog-name-filter").dialog('open');
	});

	

	
	//attach event to the 'filter list toggle' link
	$("#mf_filters_toggle_button").click(function(){

		$('.content_body_sidebar').toggleClass('filter_list_expand_sidebar');
		$('.content_body_main').toggleClass('filter_list_expand_main');

		return false;
	});

	/***************************************************************************************************************/	
	/* 12. Form Import																			   				   */
	/***************************************************************************************************************/

	//Dialog box to import form template
	$("#dialog-import-form").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center',150],
		draggable: false,
		resizable: false,
		buttons: [{
				text: 'Done',
				id: 'dialog-import-form-btn-done',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					$(this).dialog('close');
				}
			},
			{
				text: 'Cancel',
				id: 'dialog-import-form-btn-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});

	//initialize file uploader for export/import tool
	$('#mf_form_import_file').uploadifive({
		'uploadScript'     	: 'import_form_uploader.php',
		'buttonText'        : 'Select File',
		'removeCompleted' 	: true,
		'formData'         	 : {
								 'session_id': $(".manage_forms").data("session_id")
			                  },
		'auto'        : true,
	   	'multi'       : false,
	   	'onUploadError' : function(file, errorCode, errorMsg, errorString) {
        						alert('The file ' + file.name + ' could not be uploaded: ' + errorString);
    					   },
    	'onUploadComplete' : function(file, response) {
    		var is_valid_response = false;
			try{
				var response_json = jQuery.parseJSON(response);
				is_valid_response = true;
			}catch(e){
				is_valid_response = false;
				alert(response);
			}
			
			if(is_valid_response == true && response_json.status == "ok"){
				var uploaded_form_file = response_json.file_name;
				var csrf_token = $("#content").data("csrftoken");

				//do ajax call to parse the file
				$.ajax({
					   type: "POST",
					   async: true,
					   url: "import_form_parser.php",
					   data: {file_name: uploaded_form_file,csrf_token: csrf_token},
					   cache: false,
					   global: false,
					   dataType: "json",
					   error: function(xhr,text_status,e){
							   //error, display the generic error message
							   alert("Error while importing file. Error Message:\n" + xhr.responseText);		  
					   },
					   success: function(response_data){
							   
						   if(response_data.status == 'ok'){
							   $("#dialog-form-import-success").data("form_id",response_data.new_form_id);
							   $("#dialog-form-import-success").data("form_name",response_data.new_form_name);

							   $("#form-imported-link").text(response_data.new_form_name);
							   $("#form-imported-link").attr("href","view.php?id=" + response_data.new_form_id);

							   //display success dialog
							   $("#dialog-import-form").dialog('close');
							   $("#dialog-form-import-success").dialog('open');
						   }else{
						   	   //display error dialog
						   	   if(response_data.status == 'error'){
						   	   		$("#dialog-warning-msg").html(response_data.message);
						   	   }

							   $("#dialog-warning").dialog('open');
						   }	  
							   
					   }
				});
	       	}else{
		       	alert('Error uploading file. Please try again.');
			}  
    	} 

	});
	
	//dialog box for import success
	$("#dialog-form-import-success").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 450,
		resizable: false,
		draggable: false,
		position: ['center','center'],
		open: function(){
			$("#btn-form-success-done").blur();
		},
		buttons: [{
				text: 'Done',
				id: 'btn-form-success-done',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					var current_form_id = $("#dialog-form-import-success").data("form_id");
					window.location.replace('manage_forms.php?id=' + current_form_id);
				}},
				{
				text: 'Edit Form',
				id: 'btn-form-success-edit',
				'class': 'btn_secondary_action',
				click: function() {
					var current_form_id = $("#dialog-form-import-success").data("form_id");
					window.location.replace('edit_form.php?id=' + current_form_id);
				}
			}]

	});

	//dialog for import failed
	$("#dialog-warning").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 450,
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


	tippy('.form_actions_toggle', {
		content: function(element){
			return document.getElementById('action_toggle_content_' + element.dataset.formid).innerHTML;
		},
		placement: 'bottom',
		onShown: function(instance){
			tippy_form_id = instance.reference.dataset.formid;

			//bind event handlers to all links within the tooltip
			bind_delete_form_event();
			bind_disable_form_event();
			bind_duplicate_form_event();
			bind_export_form_event();
		},
		trigger: 'click',
		interactive: true,
		ignoreAttributes: true,
		arrow: true
	});

	tippy('#mf_sort_pane_button', {
		content: function(element){
			return document.getElementById('mf_sort_pane_content').innerHTML;
		},
		placement: 'bottom',
		onShown: function(instance){
			
		},
		trigger: 'click',
		interactive: true,
		ignoreAttributes: true,
		arrow: true
	});

	tippy('#mf_filters_toggle2_button', {
		content: function(element){
			return document.getElementById('mf_filters_toggle2_content').innerHTML;
		},
		placement: 'bottom',
		onShown: function(instance){
			
		},
		trigger: 'click',
		interactive: true,
		ignoreAttributes: true,
		arrow: true
	});
});

/** Functions **/

//clear the form filter
function reset_form_filter(){
	$("#mf_form_list > li").hide();
	$("#mf_pagination").show();
	
	if($("#mf_pagination > li.current_page").length > 0){
		$($("#mf_pagination > li.current_page").data('liform_list')).show();
	}else{
		$("#mf_form_list > li").show();
	}

	$("#mf_form_list h3").unhighlight();
	$("ul.form_tag_list li").unhighlight();
	
	$("#filtered_result_box").fadeOut();
	$("#filtered_result_none").hide();
	
	$("#result_set_show_more").hide();
}

//attach event to the 'Delete' link on the tooltip
function bind_delete_form_event(){
		
		//open the dialog when the delete link clicked
		$(".mf_link_delete a").click(function(){
			var form_id = tippy_form_id;
			var parent_li = $("#liform_" + form_id);

			//hide the tooltip
			var tippy_instance = $('.form_actions_toggle.tippy-active')[0]._tippy;
			tippy_instance.hide();

			$("#confirm_form_delete_name").text(parent_li.find('h3').text());
			$("#dialog-confirm-form-delete").data('form_id',form_id);
			$("#dialog-confirm-form-delete").dialog('open');

			//we need to unbind the event, otherwise it will add up each time the tooltip displayed
			$(".mf_link_delete a").unbind("click");
			
			return false;
		});
}

//attach event to the 'Delete' link on the tooltip
function bind_disable_form_event(){
	//enable or disable the form
	$(".mf_link_disable a").click(function(){
			var current_form_id = tippy_form_id;
			var current_action = '';

			//hide the tooltip
			var tippy_instance = $('.form_actions_toggle.tippy-active')[0]._tippy;
			
			
			current_action = $(this).text().trim().toLowerCase()

			if(current_action == 'disable'){
				tippy_instance.hide();

				selected_form_id = current_form_id;
				$("#dialog-disabled-message").dialog('open');
			}else if(current_action == 'enable'){
				//change the 'Delete' text
				$(this).text('Processing...');
				
				//do the ajax call to enable or disable the form
				$.ajax({
					   type: "POST",
					   async: true,
					   url: "toggle_form.php",
					   data: {
							  form_id: current_form_id,
							  action: current_action
							 },
					   cache: false,
					   global: true,
					   dataType: "json",
					   error: function(xhr,text_status,e){
						  alert('Error! Unable to process');
					   },
					   success: function(response_data){
						   tippy_instance.hide();

						   if(response_data.status == 'ok'){
							   if(response_data.action == 'disable'){
								   $("#liform_" + response_data.form_id).addClass('form_inactive');
								   $("#action_toggle_content_" + response_data.form_id + " .mf_link_disable a").html('<span class="icon-play-circle"></span> Enable');
								   
							   }else{
								   $("#liform_" + response_data.form_id).removeClass('form_inactive');
								   $("#action_toggle_content_" + response_data.form_id + " .mf_link_disable a").html('<span class="icon-pause-circle"></span> Disable');
								  
							   }
							   $("#form_action_" + response_data.form_id)[0]._tippy.setContent($("#action_toggle_content_" + response_data.form_id).html());
						   }
						   
					   }
				}); //end of ajax call
			}

			//we need to unbind the event, otherwise it will add up each time the tooltip displayed
			$(".mf_link_disable a").unbind("click");

			return false;
	});
}

//attach event to the 'Duplicate' link on the tooltip
function bind_duplicate_form_event(){
	$(".mf_link_duplicate a").click(function(){
		var current_form_id = tippy_form_id;
		var tippy_instance = $('.form_actions_toggle.tippy-active')[0]._tippy;
		var self_link = $(this);
		var csrf_token = $("#content").data("csrftoken");

		if($(this).text() == 'Duplicating...'){
			//we need to unbind the event, otherwise it will add up each time the tooltip displayed
			$(".mf_link_disable a").unbind("click");

			return false; //prevent the user from clicking multiple times
		}
		
		//change the 'Duplicate' text
		$(this).text('Duplicating...');
			
			
		//do the ajax call to duplicate the form
		$.ajax({
			   type: "POST",
			   async: true,
			   url: "duplicate_form.php",
			   data: {
					  form_id: current_form_id,
					  csrf_token: csrf_token
					 },
			   cache: false,
			   global: true,
			   dataType: "json",
			   error: function(xhr,text_status,e){
			   	  self_link.html('<span class="icon-copy1"></span> Duplicate');
				  alert('Error! Unable to duplicate. Error: ' + xhr.responseText);
			   },
			   success: function(response_data){
					   
				   if(response_data.status == 'ok'){
					   window.location.replace('manage_forms.php?id=' + response_data.form_id + '&hl=true');
				   }else{
					   //unknown error, response json improperly formatted
					   self_link.html('<span class="icon-copy1"></span> Duplicate');
					   alert('Error! Unable to duplicate. Error: ' + response_data);
				   }
					   
			   }
			}); //end of ajax call
		
		//we need to unbind the event, otherwise it will add up each time the tooltip displayed
		$(".mf_link_disable a").unbind("click");

		return false;
	});
}

//attach event to 'export form' link on the tooltip
function bind_export_form_event(){
	//attach event to 'export form' button
	$(".mf_link_export a").click(function(){
		var selected_form_id = tippy_form_id;
		var tippy_instance = $('.form_actions_toggle.tippy-active')[0]._tippy;
		var csrf_token = $("#content").data("csrftoken");

		//we need to unbind the event, otherwise it will add up each time the tooltip displayed
		$(".mf_link_disable a").unbind("click");
		tippy_instance.hide();

		window.location.href = 'export_form.php?form_id=' + selected_form_id + '&csrf_token=' + csrf_token;

		return false;
	});
}

//pin/unpin smart folders sidebar
function toggle_pin_folders(){
	var pin_folder_status = 0;

	if($("#pin_folders").hasClass('pinned')){
		$("#pin_folders").removeClass('pinned');
		pin_folder_status = 0;
	}else{
		$("#pin_folders").addClass('pinned');
		pin_folder_status = 1;
	}

	//do the ajax call to disable the form						
	$.ajax({
		   type: "POST",
		   async: true,
		   url: "toggle_folder.php",
		   data: {
				  pin_folder: pin_folder_status
				 },
		   cache: false,
		   global: true,
		   dataType: "json",
		   error: function(xhr,text_status,e){
			  alert('Error! Unable to process');
		   },
		   success: function(response_data){
			  //do nothing on success
		   }
	}); //end of ajax call
}