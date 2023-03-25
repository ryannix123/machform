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
	/* 2. Widget Data														   				   				   	   */
	/***************************************************************************************************************/
	
	//populate the datasource label
	populate_datasource_title();

	//attach event to the Data Option dropdown
	$('#ew_chart_enable_filter').bind('change', function() {
		var selected_value = $(this).val();
		
		if(selected_value == 1){
			$("#widget_filter_pane").slideDown();
		}else{
			$("#widget_filter_pane").slideUp();
		}
	});

	//delegate change event into filter field name dropdown
	$('#widget_filter_pane').delegate('select.condition_fieldname', 'change', function(e) {
			var new_element_name = $(this).val();
			var new_element_type = $("#widget_filter_pane").data(new_element_name);

			$(this).parent().find('.condition_text,.condition_time,.condition_number,.condition_date,.condition_file,.condition_checkbox,.condition_approval_status,.condition_time,.filter_keyword,.condition_select,.condition_rating,.condition_ratingvalues').hide();
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
			}else if(new_element_type == 'approval_status'){
				$(this).parent().find('.condition_approval_status').show();
				$(this).parent().data('filter_properties').condition = $(this).parent().find('.condition_approval_status').val();
			}else{
				$(this).parent().find('.condition_text,input.text').show();
				$(this).parent().data('filter_properties').condition = $(this).parent().find('.condition_text').val();
			}

			$(this).parent().data('filter_properties').element_name = new_element_name;
    });

	 //delegate change event to the condition type dropdown (for number, date, checkbox, rating)
    $('#widget_filter_pane').delegate('select.condition_number,select.condition_date,select.condition_file,select.condition_checkbox,select.condition_approval_status,select.condition_rating', 'change', function(e) {
		$(this).parent().data('filter_properties').condition = $(this).val();
    });

      //delegate change event to the condition type dropdown (for other fields beside the above)
    $('#widget_filter_pane').delegate('select.condition_text', 'change', function(e) {
    	var element_name = $(this).parent().data('filter_properties').element_name;
    	var element_type = $("#widget_filter_pane").data(element_name);

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
    $('#widget_filter_pane').delegate('select.condition_select', 'change', function(e) {
		$(this).parent().data('filter_properties').keyword = $(this).val();
    });

    //delegate change event to the condition select dropdown (only applicable for rating)
    $('#widget_filter_pane').delegate('select.condition_ratingvalues', 'change', function(e) {
		$(this).parent().data('filter_properties').keyword = $(this).val();
    });

    //delegate event to the filter keyword text
    $('#widget_filter_pane').delegate('input.filter_keyword', 'keyup mouseout change', function(e) {
		$(this).parent().data('filter_properties').keyword = $(this).val();	
    });

    //delegate event to the time condition inputs
    $('#widget_filter_pane').delegate('input.conditiontime_input,select.conditiontime_input', 'keyup mouseout change', function(e) {
		
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
    $('#widget_filter_pane').delegate('a.filter_delete_a', 'click', function(e) {
		
		if($("#widget_filter_pane li:not('.filter_add')").length <= 1){
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

    //attach click event to 'add filter condition' icon
	$("#filter_add_a").click(function(){
		var new_id = $("#widget_filter_pane li:not('.filter_add')").length + 1;
		var old_id = new_id - 1;
		
		//duplicate the last filter condition
		var last_filter_element = $("#widget_filter_pane ul > li:not('.filter_add')").last();
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

	/***************************************************************************************************************/	
	/* 2. Widget Options															   				   			   */
	/***************************************************************************************************************/

	//initialize chart Background Color minicolors
	$("#ew_chart_background").miniColors();

	//initialize chart Bar Color minicolors
	$("#ew_chart_bar_color").miniColors();

	//attach event to 'Show Title' checkbox
	$('#ew_show_title').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ew_show_title_div").slideDown();
		}else{
			$("#ew_chart_title").val('');
			$("#ew_show_title_div").slideUp();
		}
	});

	//attach event to 'Show Labels' checkbox
	$('#ew_show_labels').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ew_show_labels_div").slideDown();
		}else{
			$("#ew_show_labels_div").slideUp();
		}
	});

	//attach event to the 'Show Labels - Position' dropdown
	$('#ew_chart_labels_position').bind('change', function() {
		var selected_value = $(this).val();
		var chart_type = $("#ew_main_list").data("charttype");

		//labels alignment only available on pie, donut when the position is outsideEnd
		if(chart_type == 'pie' || chart_type == 'donut'){
			if(selected_value == 'outsideEnd'){
				$("#ew_chart_labels_align_span").show();
			}else{
				$("#ew_chart_labels_align_span").hide();
			}
		}
	});

	//attach event to 'Show Legend' checkbox
	$('#ew_show_legend').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ew_show_legend_div").slideDown();
		}else{
			$("#ew_show_legend_div").slideUp();
		}
	});

	//attach event to 'Show Tooltip' checkbox
	$('#ew_show_tooltip').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ew_show_tooltip_div").slideDown();
		}else{
			$("#ew_show_tooltip_div").slideUp();
		}
	});

	//attach event to the 'Date Range' dropdown
	$('#ew_chart_date_range').bind('change', function() {
		var selected_value = $(this).val();
		
		//hide all options first
		$("#ew_show_date_range_period_div,#ew_show_date_range_custom_div").hide();
		if(selected_value == 'period'){
			$("#ew_show_date_range_period_div").show();
		}else if(selected_value == 'custom'){
			$("#ew_show_date_range_custom_div").show();
		}
	});

	/***************************************************************************************************************/	
	/* 3. Widget Size															   				   				   */
	/***************************************************************************************************************/

	//attach event to the Widget Height dropdown
	$('#ew_chart_height').bind('change', function() {
		var selected_value = $(this).val();
		
		if(selected_value == 'custom'){
			$("#custom_widget_height_div").slideDown();
		}else{
			$("#custom_widget_height_div").slideUp();
		}
	});

	/***************************************************************************************************************/	
	/* 4. Initialize Datepickers												   				   				   */
	/***************************************************************************************************************/

	//initialize datepicker
	var total_filter = $("#widget_filter_pane li").length - 1;
	for(i=1;i<=total_filter;i++){
		$('#datepicker_' + i).datepick({ 
	    		onSelect: select_date,
	    		showTrigger: '#datepickimg_' + i
		});
	}

	//initialize date range start
	$('#datepicker_chart_date_range_start').datepick({ 
	    onSelect: select_date_range_start,
	    showTrigger: '#datepickimg_chart_date_range_start'
	});

	//initialize date range end
	$('#datepicker_chart_date_range_end').datepick({ 
	    onSelect: select_date_range_end,
	    showTrigger: '#datepickimg_chart_date_range_end'
	});

	/***************************************************************************************************************/	
	/* 5. Initialize Dialog Box													   				   				   */
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


	/***************************************************************************************************************/	
	/* 6. Attach event to 'Save Settings' button												   				   */
	/***************************************************************************************************************/

	$("#button_save_widget").click(function(){
		
		if($("#button_save_widget").text() != 'Saving...'){
				
				//display loader while saving
				$("#button_save_widget").prop("disabled",true);
				$("#button_save_widget").text('Saving...');
				$("#button_save_widget").after("<div class='small_loader_box' style='float: right'><img src='images/loader_small_grey.gif' /></div>");
				
				var form_id  = $("#ew_main_list").data("formid");
				var chart_id = $("#ew_main_list").data("chartid");

				//get filter properties
				var filter_prop_array = new Array();
				$("#widget_filter_pane li.filter_settings").each(function(index){
					filter_prop_array[index] = $(this).data("filter_properties");
				});

				var chart_labels_visible_value = 0;
				if($("#ew_show_labels").prop("checked")){
					chart_labels_visible_value = 1;
				}

				var chart_rating_bars_visible_value = 0;
				if($("#ew_show_rating_bars").prop("checked")){
					chart_rating_bars_visible_value = 1;
				}

				var chart_rating_total_visible_value = 0;
				if($("#ew_show_rating_total_entries").prop("checked")){
					chart_rating_total_visible_value = 1;
				}

				var chart_legend_visible_value = 0;
				if($("#ew_show_legend").prop("checked")){
					chart_legend_visible_value = 1;
				}

				var chart_tooltip_visible_value = 0;
				if($("#ew_show_tooltip").prop("checked")){
					chart_tooltip_visible_value = 1;
				}

				var chart_gridlines_visible_value = 0;
				if($("#ew_show_gridlines").prop("checked")){
					chart_gridlines_visible_value = 1;
				}

				var chart_is_stacked_value = 0;
				if($("#ew_chart_is_stacked").prop("checked")){
					chart_is_stacked_value = 1;
				}

				var chart_is_vertical_value = 0;
				if($("#ew_chart_is_vertical").prop("checked")){
					chart_is_vertical_value = 1;
				}

				var selected_grid_columns = $("#li_grid_columns input.checkbox:checked").serializeArray();
				var csrf_token = $("#content").data("csrftoken");

				//send to backend using ajax call
				$.ajax({
					   	type: "POST",
					   	async: true,
					   	url: "save_widget_settings.php",
					   	data: {form_id: form_id,
							   chart_id: chart_id,
							   csrf_token: csrf_token,
							   chart_enable_filter: $("#ew_chart_enable_filter").val(),
							   filter_prop: filter_prop_array,
							   filter_type: $("#filter_all_any").val(),
							   chart_theme: $("#ew_chart_theme").val(),
							   chart_line_style: $("#ew_chart_line_style").val(),
							   chart_background: $("#ew_chart_background").val(),
							   chart_bar_color: $("#ew_chart_bar_color").val(),
							   chart_title: $("#ew_chart_title").val(),
							   chart_title_position: $("#ew_chart_title_position").val(),
							   chart_title_align: $("#ew_chart_title_align").val(),
							   chart_labels_template: $("#ew_chart_labels_template").val(),
							   chart_labels_visible: chart_labels_visible_value,
							   chart_labels_position: $("#ew_chart_labels_position").val(),
							   chart_labels_align: $("#ew_chart_labels_align").val(),
							   chart_legend_visible: chart_legend_visible_value,
							   chart_legend_position: $("#ew_chart_legend_position").val(),
							   chart_tooltip_template: $("#ew_chart_tooltip_template").val(),
							   chart_tooltip_visible: chart_tooltip_visible_value,
							   chart_gridlines_visible: chart_gridlines_visible_value,
							   chart_rating_bars_visible: chart_rating_bars_visible_value,
							   chart_rating_total_visible: chart_rating_total_visible_value,
							   chart_rating_size: $("input[name='ew_rating_size']:checked").val(),
							   chart_is_stacked: chart_is_stacked_value,
							   chart_is_vertical: chart_is_vertical_value,
							   chart_date_range: $("#ew_chart_date_range").val(),
							   chart_date_period_value: $("#ew_chart_date_period_value").val(),
							   chart_date_period_unit: $("#ew_chart_date_period_unit").val(),
							   chart_date_axis_baseunit_period: $("#ew_chart_date_axis_baseunit_period").val(),
							   chart_date_axis_baseunit_custom: $("#ew_chart_date_axis_baseunit_custom").val(),
							   chart_date_range_start: $("#ew_chart_date_range_start").val(),
							   chart_date_range_end: $("#ew_chart_date_range_end").val(),
							   grid_columns: selected_grid_columns,
							   chart_grid_page_size: $("#ew_grid_page_size").val(),
							   chart_grid_max_length: $("#ew_grid_max_length").val(),
							   chart_height: $("#ew_chart_height").val(),
							   chart_height_custom: $("#ew_chart_height_custom").val(),
							   chart_grid_sort_by: $("#ew_chart_grid_sort_by").val()
							  },
					   	cache: false,
					   	global: false,
					   	dataType: "json",
					   	error: function(xhr,text_status,e){
							   //error, display the generic error message		  
					   },
					   	success: function(response_data){
							   
						   if(response_data.status == 'ok'){
							   window.location.replace('manage_report.php?id=' + response_data.form_id);
						   }	  
						}
				});
		}
		
		
		return false;
	});

	
});

/** Functions **/

//populate the datasource label from the hidden dropdowns
function populate_datasource_title(){
	var chart_datasource = $("#ew_datasource_title").text();
	var datasource_title = '';
	var chart_type = $("#ew_main_list").data("charttype");
	
	datasource_title = $("#ew_select_datasource option[value=" + chart_datasource + "]").text();
	if($("#ew_select_datasource option[value=" + chart_datasource + "]").parent().attr('label') != null){
			datasource_title = datasource_title + ' (' + $("#ew_select_datasource option[value=" + chart_datasource + "]").parent().attr('label') + ')';	
	}

	if(chart_type == 'grid'){
		datasource_title = 'All fields';
	}

	$("#ew_datasource_title").text(datasource_title);
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
function select_date_range_start(dates){
	var month = dates[0].getMonth() + 1;
	var day   = dates[0].getDate();
	var year  = dates[0].getFullYear();
	
	var temp = $(this).attr("id").split("_");
	var li_id = temp[1];

	var selected_date = month + '/' + day + '/' + year;

	$("#ew_chart_date_range_start").val(selected_date);
}
function select_date_range_end(dates){
	var month = dates[0].getMonth() + 1;
	var day   = dates[0].getDate();
	var year  = dates[0].getFullYear();
	
	var temp = $(this).attr("id").split("_");
	var li_id = temp[1];

	var selected_date = month + '/' + day + '/' + year;

	$("#ew_chart_date_range_end").val(selected_date);
}