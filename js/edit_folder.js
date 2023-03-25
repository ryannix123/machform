$(function(){

    //attach event to the 'save folder' button
	$("#button_save_folder").click(function(){

		//make sure folder name is not empty
		var folder_name = $("#af_folder_name").val().trim();

		if(folder_name == ''){
			Swal.fire({
				title: 'Folder Name empty',
				text: "Please enter a folder name",
				type: 'error'
			});

			return false;
		}
		

		//save the folder
		axios.post('save_folder.php', {
			folder_id: $("#folder_id").val(),
			folder_name: folder_name,
			rule_all_any: $("#rule_all_any").val(),
			folder_rules: mf_data.folder_rules
		})
		.then(function (response) {
			if(response.data.status == 'ok'){
				//success
				//redirect to folders page
				window.location.replace('manage_folders.php');
			}else{
				Swal.fire({
					title: 'Error. Unable to save.',
					text: response.data.message,
					type: 'error'
				});
			}
		})
		.catch(function (error) {
			alert(error);
		});

		return false;
	});
	
	//attach event to the main condition dropdown
	$('#folder_condition_pane').on('change','select.condition_fieldname', function(e) {
		var selected_condition = $(this).val();
		var current_id = $(this).attr("id").split("_")[1];

		//save the selected condition data
		mf_data.folder_rules[current_id].element_name = selected_condition;
												
		//display the appropriate condition pane and load the data (rule_condition & rule_keyword) as well
		$(this).parent().parent().find(".condition_text_container,.condition_number_container,.condition_createdby_container,.condition_status_container,.condition_date_container").hide();
		
		if(selected_condition == 'created_date' || selected_condition == 'last_entry_date'){
			$(this).parent().parent().find(".condition_date_container").show();
			$("#filterdatekeywordpicker_" + current_id + ",#filterdatekeyword_" + current_id + ",#filterdateperiod_" + current_id).hide();

			mf_data.folder_rules[current_id].rule_condition = $("#conditiondate_" + current_id).val();

			switch($("#conditiondate_" + current_id).val()){
				case 'within_last' : 
						mf_data.folder_rules[current_id].rule_keyword = $("#filterdatekeyword_" + current_id).val() + '-' + $("#filterdateperiod_" + current_id).val(); 
						$("#filterdatekeyword_" + current_id + ",#filterdateperiod_" + current_id).show();
						break;
				case 'exactly' :
				case 'before' :
				case 'after' : 
						mf_data.folder_rules[current_id].rule_keyword = $("#filterdatekeywordpicker_" + current_id).val(); 
						$("#filterdatekeywordpicker_" + current_id).show();
						break;
				default: mf_data.folder_rules[current_id].rule_keyword = '';	
			}	
		}else if(selected_condition == 'created_by'){
			$(this).parent().parent().find(".condition_createdby_container").show();

			mf_data.folder_rules[current_id].rule_condition = 'is';
			mf_data.folder_rules[current_id].rule_keyword	= $("#conditioncreatedby_" + current_id).val();
		}else if(selected_condition == 'status'){
			$(this).parent().parent().find(".condition_status_container").show();

			mf_data.folder_rules[current_id].rule_condition = $("#conditionstatus_" + current_id).val();
			mf_data.folder_rules[current_id].rule_keyword	= '';
		}else if(selected_condition == 'total_entries' || selected_condition == 'today_entries'){
			$(this).parent().parent().find(".condition_number_container").show();

			mf_data.folder_rules[current_id].rule_condition = $("#conditionnumber_" + current_id).val();
			mf_data.folder_rules[current_id].rule_keyword	= $("#filternumberkeyword_" + current_id).val();
		}else{
			$(this).parent().parent().find(".condition_text_container").show();

			mf_data.folder_rules[current_id].rule_condition = $("#conditiontext_" + current_id).val();
			mf_data.folder_rules[current_id].rule_keyword	= $("#filtertextkeyword_" + current_id).val();
		}
	});

	//attach event to the 'text' condition dropdown
	$('#folder_condition_pane').on('change','select.condition_text', function(e) {
		var selected_condition = $(this).val();
		var current_id = $(this).attr("id").split("_")[1];

		//save the selected condition data
		mf_data.folder_rules[current_id].rule_condition = selected_condition;
	});

	//attach event to the 'text' keyword
    $('#folder_condition_pane').on('keyup mouseout change','input.filter_text_keyword', function(e) {
    	var current_id = $(this).attr("id").split("_")[1];
		mf_data.folder_rules[current_id].rule_keyword = $(this).val();
    });

	//attach event to the 'number' condition dropdown
	$('#folder_condition_pane').on('change','select.condition_number', function(e) {
		var selected_condition = $(this).val();
		var current_id = $(this).attr("id").split("_")[1];

		//save the selected condition data
		mf_data.folder_rules[current_id].rule_condition = selected_condition;
	});

	//attach event to the 'number' keyword
    $('#folder_condition_pane').on('keyup mouseout change','input.filter_number_keyword', function(e) {
    	var current_id = $(this).attr("id").split("_")[1];
		mf_data.folder_rules[current_id].rule_keyword = $(this).val();
    });

    //attach event to the 'created by' condition dropdown
	$('#folder_condition_pane').on('change','select.condition_createdby', function(e) {
		var selected_condition = $(this).val();
		var current_id = $(this).attr("id").split("_")[1];

		//save the selected condition data
		mf_data.folder_rules[current_id].rule_keyword = selected_condition;
	});

	//attach event to the 'form status' condition dropdown
	$('#folder_condition_pane').on('change','select.condition_status', function(e) {
		var selected_condition = $(this).val();
		var current_id = $(this).attr("id").split("_")[1];

		//save the selected condition data
		mf_data.folder_rules[current_id].rule_condition = selected_condition;
	});

	//attach event to the 'date' condition dropdown
	$('#folder_condition_pane').on('change','select.condition_date', function(e) {
		var selected_condition = $(this).val();
		var current_id = $(this).attr("id").split("_")[1];

		//save the selected condition data
		mf_data.folder_rules[current_id].rule_condition = selected_condition;

		//display the appropriate date keyword inputs
		$("#filterdatekeywordpicker_" + current_id + ",#filterdatekeyword_" + current_id + ",#filterdateperiod_" + current_id).hide();
		
		if(selected_condition == 'within_last'){
			$("#filterdatekeyword_" + current_id + ",#filterdateperiod_" + current_id).show();

			mf_data.folder_rules[current_id].rule_keyword = $("#filterdatekeyword_" + current_id).val() + '-' + $("#filterdateperiod_" + current_id).val();
		}else if(selected_condition == 'exactly' || selected_condition == 'before' || selected_condition == 'after'){
			$("#filterdatekeywordpicker_" + current_id).show();

			mf_data.folder_rules[current_id].rule_keyword = $("#filterdatekeywordpicker_" + current_id).val();
		}else{
			mf_data.folder_rules[current_id].rule_keyword = '';
		}

	});

	//attach event to the 'within last -- d/m/y' condition dropdown
	$('#folder_condition_pane').on('change','select.condition_date_period', function(e) {
		var selected_condition = $(this).val();
		var current_id = $(this).attr("id").split("_")[1];

		mf_data.folder_rules[current_id].rule_keyword = $("#filterdatekeyword_" + current_id).val() + '-' + selected_condition;
	});

	//attach event to the 'within last -- d/m/y' condition text
    $('#folder_condition_pane').on('keyup mouseout change','input.filter_date_keyword', function(e) {
    	var current_id = $(this).attr("id").split("_")[1];
		
		mf_data.folder_rules[current_id].rule_keyword = $(this).val() + '-' + $("#filterdateperiod_" + current_id).val();
    });

	//initiliaze date picker
	flatpickr(".filter_date_keyword_picker", { 
		allowInput: true,
		dateFormat: 'n/j/Y',
		onChange: function(selectedDates, dateStr, instance) {
			var current_id = instance.element.id.split("_")[1];

        	mf_data.folder_rules[current_id].rule_keyword = dateStr;
    	}
	});

	//'delete rules' event handler
	$('#folder_condition_pane').on('click','a.filter_delete_a', function(e) {
		var current_id = $(this).attr("id").split("_")[1];
		
		//check make sure this is not the last condition
		if($("#folder_condition_pane li.filter_settings").length <= 1){
			Swal.fire({
				title: 'Unable to remove',
				text: "You cannot remove all conditions. At least one condition required.",
				type: 'error'
			});

			return false;
		}

		//remove the dom
		$("#li_" + current_id).remove();

		//remove the data
		delete mf_data.folder_rules[current_id];

		return false;
	});

	//attach event to the 'add condition' icon
	$("#filter_add_a").click(function(){
		var new_id = $("#folder_condition_pane li:not('.filter_add')").length + 1;
		var old_id = new_id - 1;
		
		//duplicate the last filter condition
		var last_filter_element = $("#folder_condition_pane ul > li:not('.filter_add')").last();
		last_filter_element.clone(false).find("*[id],*[name]").each(function() {
			var temp = $(this).attr("id").split("_"); 
			var old_id = new_id - 1;

			//rename the original id with the new id
			$(this).attr("id", temp[0] + "_" + new_id);
			$(this).attr("name", temp[0] + "_" + new_id);
			
		}).end().attr("id","li_" + new_id).insertBefore("#li_filter_add").hide().fadeIn();

		//initialize the new datepicker
		flatpickr("#filterdatekeywordpicker_" + new_id, { 
			allowInput: true,
			dateFormat: 'n/j/Y',
			onChange: function(selectedDates, dateStr, instance) {
        		mf_data.folder_rules[new_id].rule_keyword = dateStr;
    		}
		});

		//copy the data
		mf_data.folder_rules[new_id] = Object.assign({},mf_data.folder_rules[old_id]);
		
		//copy the values on the interface
		$("#filterfield_" + new_id).val(mf_data.folder_rules[new_id].element_name);
		
		var selected_condition = mf_data.folder_rules[new_id].element_name;
		var current_id = new_id;

		if(selected_condition == 'created_date' || selected_condition == 'last_entry_date'){
			$("#conditiondate_" + current_id).val(mf_data.folder_rules[current_id].rule_condition);

			if(mf_data.folder_rules[current_id].rule_condition == 'within_last'){
				$("#filterdatekeyword_" + current_id).val(mf_data.folder_rules[current_id].rule_keyword.split('-')[0]);
				$("#filterdateperiod_" + current_id).val(mf_data.folder_rules[current_id].rule_keyword.split('-')[1]);
			}else if(mf_data.folder_rules[current_id].rule_condition == 'exactly' || mf_data.folder_rules[current_id].rule_condition == 'before' || mf_data.folder_rules[current_id].rule_condition == 'after'){
				$("#filterdatekeywordpicker_" + current_id).val(mf_data.folder_rules[current_id].rule_keyword);
			}	
		}else if(selected_condition == 'created_by'){
			$("#conditioncreatedby_" + current_id).val(mf_data.folder_rules[current_id].rule_keyword);
		}else if(selected_condition == 'status'){
			$("#conditionstatus_" + current_id).val(mf_data.folder_rules[current_id].rule_condition);
		}else if(selected_condition == 'total_entries' || selected_condition == 'today_entries'){
			$("#conditionnumber_" + current_id).val(mf_data.folder_rules[current_id].rule_condition);
			$("#filternumberkeyword_" + current_id).val(mf_data.folder_rules[current_id].rule_keyword);
		}else{
			$("#conditiontext_" + current_id).val(mf_data.folder_rules[current_id].rule_condition);
			$("#filtertextkeyword_" + current_id).val(mf_data.folder_rules[current_id].rule_keyword);
		}

		return false;
	});
});