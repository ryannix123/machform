(function($){
  $.fn.outerHTML = function() {
    var el = this[0];
    return !el ? null : el.outerHTML || $('<div />').append(el).html();
  }
})(jQuery);

function select_date(dates){

	var month = dates[0].getMonth() + 1;
	var day   = dates[0].getDate();
	var year  = dates[0].getFullYear();
	
	var temp = $(this).attr("id").split("_");
	var li_id = temp[1] + '_' + temp[2];

	var selected_date = month + '/' + day + '/' + year;

	$("#conditionkeyword_" + li_id).val(selected_date);
	$("#lifieldrule_" + li_id).data('rule_condition').keyword = selected_date;
}

function select_date_page(dates){

	var month = dates[0].getMonth() + 1;
	var day   = dates[0].getDate();
	var year  = dates[0].getFullYear();
	
	var temp = $(this).attr("id").split("_");
	var li_id = temp[1] + '_' + temp[2];

	var selected_date = month + '/' + day + '/' + year;

	$("#conditionkeyword_" + li_id).val(selected_date);
	$("#lipagerule_" + li_id).data('rule_condition').keyword = selected_date;
}

function select_date_email(dates){

	var month = dates[0].getMonth() + 1;
	var day   = dates[0].getDate();
	var year  = dates[0].getFullYear();
	
	var temp = $(this).attr("id").split("_");
	var li_id = temp[1] + '_' + temp[2];

	var selected_date = month + '/' + day + '/' + year;

	$("#conditionkeyword_" + li_id).val(selected_date);
	$("#liemailrule_" + li_id).data('rule_condition').keyword = selected_date;
}

function select_date_webhook(dates){

	var month = dates[0].getMonth() + 1;
	var day   = dates[0].getDate();
	var year  = dates[0].getFullYear();
	
	var temp = $(this).attr("id").split("_");
	var li_id = temp[1] + '_' + temp[2];

	var selected_date = month + '/' + day + '/' + year;

	$("#conditionkeyword_" + li_id).val(selected_date);
	$("#liwebhookrule_" + li_id).data('rule_condition').keyword = selected_date;
}

function select_date_success(dates){

	var month = dates[0].getMonth() + 1;
	var day   = dates[0].getDate();
	var year  = dates[0].getFullYear();
	
	var temp = $(this).attr("id").split("_");
	var li_id = temp[1] + '_' + temp[2];

	var selected_date = month + '/' + day + '/' + year;

	$("#conditionkeyword_" + li_id).val(selected_date);
	$("#lisuccessrule_" + li_id).data('rule_condition').keyword = selected_date;
}

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
	/* 2. Initialize Dialog Boxes												   				   				   */
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
	/* 3. 'Show / Hide Fields' pane												   				   				   */
	/***************************************************************************************************************/
	
	//attach event to 'enable show/hide field rules' checkbox
	$("#logic_field_enable").click(function(){
		if($(this).prop("checked") == true){
			$("#ls_box_field_rules .ls_box_content").slideDown();
			$(".logic_settings").data('logic_status').logic_field_enable = 1;
		}else{
			$("#ls_box_field_rules .ls_box_content").slideUp();
			$(".logic_settings").data('logic_status').logic_field_enable = 0;
		}
	});

	//attach event to 'add a field to show/hide' dropdown
	$('#ls_select_field_rule').bind('change', function() {
		
		if($(this).val() == ''){
			return true;
		}
		
		var element_id = parseInt($(this).val());

		//build the markup
		var li_markup = '';
		var condition_fieldname_markup = '';

		condition_fieldname_markup = $("#ls_fields_lookup").clone(false).attr("id","conditionfield_" + element_id + "_1").attr("name","conditionfield_" + element_id + "_1").show().outerHTML();

		li_markup 	+= 	'<li id="lifieldrule_'+ element_id +'" style="display: none">' +
							'<table width="100%" cellspacing="0">' +
							  '<thead>' +
							    '<tr>' +
							      '<td>' + 
							         '<strong>' + $(this).find('option:selected').text() + '</strong><a class="delete_lifieldrule" id="deletelifieldrule_'+ element_id +'" href="#"><span class="icon-cross2"></span></a>' + 
							      '</td>' +
							    '</tr>' +
							  '</thead>' +
							  '<tbody>' +
							    '<tr>' +
							      '<td>' +
							      	'<h6>' +
							      		'<select style="margin-left: 5px;margin-right: 5px" name="fieldruleshowhide_'+ element_id +'" id="fieldruleshowhide_' + element_id + '" class="element select rule_show_hide">' + 
											'<option value="show">Show</option>' +
											'<option value="hide">Hide</option>' +
										'</select> this field if ' + 
										'<select style="margin-left: 5px;margin-right: 5px" name="fieldruleallany_' + element_id + '" id="fieldruleallany_' + element_id + '" class="element select rule_all_any">' + 
											'<option value="all">all</option>' +
											'<option value="any">any</option>' +
										'</select>' + 
										' of the following conditions match:' +
									'</h6>' +
							      	'<ul class="ls_field_rules_conditions">' +
							      		'<li id="lifieldrule_' + element_id + '_1"> ' +
							      			 condition_fieldname_markup + ' ' +
											'<select name="conditiontext_'+ element_id +'_1" id="conditiontext_'+ element_id +'_1" class="element select condition_text" style="width: 120px;display: none">' +
												'<option value="is">Is</option>' +
												'<option value="is_not">Is Not</option>' +
												'<option value="begins_with">Begins with</option>' +
												'<option value="ends_with">Ends with</option>' +
												'<option value="contains">Contains</option>' +
												'<option value="not_contain">Does not contain</option>' +
											'</select>' + ' ' +
											'<select id="conditionnumber_'+ element_id +'_1" name="conditionnumber_' + element_id + '_1" style="width: 120px;display: none" class="element select condition_number">' + 
												'<option value="is" selected="selected">Is</option>' + 
												'<option value="less_than">Less than</option>' + 
												'<option value="greater_than">Greater than</option>' + 
											'</select>' + ' ' +
											'<select id="conditionrating_'+ element_id +'_1" name="conditionrating_' + element_id + '_1" style="width: 120px;display: none" class="element select condition_rating">' + 
												'<option value="is" selected="selected">Is</option>' + 
												'<option value="is_not">Is Not</option>' + 
												'<option value="less_than">Less than</option>' + 
												'<option value="greater_than">Greater than</option>' + 
											'</select>' + ' ' +
											'<select id="conditiondate_'+ element_id +'_1" name="conditiondate_' + element_id + '_1" style="width: 120px;display: none" class="element select condition_date">' + 
												'<option value="is" selected="selected">Is</option>' + 
												'<option value="is_before">Is Before</option>' + 
												'<option value="is_after">Is After</option>' + 
											'</select>' + ' ' + 
											'<select id="conditioncheckbox_'+ element_id +'_1" name="conditioncheckbox_' + element_id + '_1" style="width: 120px;display: none" class="element select condition_checkbox">' + 
												'<option value="is_one">Is Checked</option>' + 
												'<option value="is_zero">Is Empty</option>' + 
											'</select>' + ' ' + "\n" +
											'<select id="conditionselect_'+ element_id +'_1" name="conditionselect_' + element_id + '_1" style="display: none" class="element select condition_select">' + 
												'<option value=""></option>' +  
											'</select>' + ' ' + "\n" +
											'<select id="conditionratingvalues_'+ element_id +'_1" name="conditionratingvalues_' + element_id + '_1" style="display: none" class="element select condition_ratingvalues">' + 
												'<option value=""></option>' +  
											'</select>' + ' ' + "\n" +
											'<span name="conditiontime_' + element_id + '_1" id="conditiontime_'+ element_id + '_1" class="condition_time" style="display: none">' + 
												'<input name="conditiontimehour_' + element_id + '_1" id="conditiontimehour_' + element_id + '_1" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="" placeholder="HH"> : ' + 
												'<input name="conditiontimeminute_' + element_id + '_1" id="conditiontimeminute_' + element_id + '_1" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="" placeholder="MM">  ' + 
												'<span class="conditiontime_second" style="display:none"> : <input name="conditiontimesecond_' + element_id + '_1" id="conditiontimesecond_' + element_id + '_1" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="" placeholder="SS"> </span>' + 
												'<select class="element select conditiontime_ampm conditiontime_input" name="conditiontimeampm_' + element_id + '_1" id="conditiontimeampm_' + element_id + '_1" style="display:none">' + 
													'<option selected="selected" value="AM">AM</option>' + 
													'<option value="PM">PM</option>' + 
												'</select>' + 
											'</span>' + 
											'<input type="text" class="element text condition_keyword" value="" id="conditionkeyword_'+ element_id +'_1" name="conditionkeyword_'+ element_id +'_1" style="display: none"> ' + "\n" +
											'<a href="#" id="deletecondition_' + element_id + '_1" name="deletecondition_'+ element_id +'_1" class="a_delete_condition"><span class="icon-minus-circle2"></span></a>' + "\n" +
							      		'</li>' +
							      		'<li class="ls_add_condition">' +
											'<a href="#" id="addcondition_'+ element_id +'" class="a_add_condition"><span class="icon-plus-circle"></span></a>' +
										'</li>' +
							      	'</ul>' +
							      '</td>' +
							    '</tr>' +
							  '</tbody>' +
							'</table>' +
						'</li>';

		//append the rule markup
		$("#ls_field_rules_group").prepend(li_markup);
		$("#lifieldrule_" + element_id).hide();

		//remove the current element from the list of condition
		$("#conditionfield_" + element_id + "_1 option[value=element_"+ element_id +"]").remove();
		$("#conditionfield_" + element_id + "_1 option[value^=element_"+ element_id +"_]").remove(); //remove childs element (for checkbox)

		//diplay the condition operator, depends on the first field on the field list
		var first_field_element_name = $("#conditionfield_" + element_id + "_1").eq(0).val();
		var first_field_element_type = $("#ls_fields_lookup").data(first_field_element_name);
		var default_condition = 'is';
		var default_keyword = '';

		//populate options for condition_select
		$("#conditionselect_" + element_id + "_1").html($("#" + first_field_element_name + "_lookup").html());

		//populate options for condition_ratingvalues
		$("#conditionratingvalues_" + element_id + "_1").html($("#" + first_field_element_name + "_lookup").html());

		if(first_field_element_type == 'money' || first_field_element_type == 'number'){
			$("#conditionnumber_" + element_id + "_1").show();
			$("#conditionkeyword_" + element_id + "_1").show();
		}else if(first_field_element_type == 'date' || first_field_element_type == 'europe_date'){
			$("#conditiondate_" + element_id + "_1").show();
			$("#conditionkeyword_" + element_id + "_1").show();

			$("#lifieldrule_" + element_id + "_1").addClass("condition_date");
		}else if(first_field_element_type == 'time' || first_field_element_type == 'time_showsecond' || first_field_element_type == 'time_24hour' || first_field_element_type == 'time_showsecond24hour'){
			$("#conditiondate_" + element_id + "_1").show();
			$("#conditiontime_" + element_id + "_1").show();
			
			if(first_field_element_type == 'time'){
				$("#conditiontimeampm_" + element_id + "_1").show();
			}else if(first_field_element_type == 'time_showsecond'){
				$("#conditiontimeampm_" + element_id + "_1").show();
				$("#conditiontimesecond_" + element_id + "_1").parent().show();
			}else if(first_field_element_type == 'time_showsecond24hour'){
				$("#conditiontimesecond_" + element_id + "_1").parent().show();
			}

		}else if(first_field_element_type == 'checkbox'){
			$("#conditioncheckbox_" + element_id + "_1").show();
			default_condition = 'is_one'
		}else if(first_field_element_type == 'select' || first_field_element_type == 'radio'){
			$("#conditiontext_" + element_id + "_1").show();
			$("#conditionselect_" + element_id + "_1").show();

			default_keyword =  $("#conditionselect_" + element_id + "_1").eq(0).val();
		}else if(first_field_element_type == 'rating'){
			$("#conditionrating_" + element_id + "_1").show();
			$("#conditionratingvalues_" + element_id + "_1").show();

			default_keyword =  $("#conditionratingvalues_" + element_id + "_1").eq(0).val();
		}else{
			$("#conditiontext_" + element_id + "_1").show();
			$("#conditionkeyword_" + element_id + "_1").show();
		}

		//build the datepicker
		var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_'+ element_id +'_1" id="datepicker_'+ element_id +'_1">' + "\n" +
							 	 ' <span style="display:none"><img id="datepickimg_'+ element_id +'_1" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

		$('#conditionkeyword_' + element_id + '_1').after(new_datepicker_tag);

		$('#datepicker_' + element_id + '_1').datepick({ 
		   		onSelect: select_date,
		   		showTrigger: '#datepickimg_' + element_id + '_1'
		});

		$("#lifieldrule_" + element_id).slideDown();

		//attach dom data
		$("#lifieldrule_" + element_id).data('rule_properties',{"element_id": element_id,"rule_show_hide":"show","rule_all_any":"all"});
		$("#lifieldrule_" + element_id + "_1").data('rule_condition',{"target_element_id": element_id,"element_name": first_field_element_name, "condition": default_condition,"keyword": default_keyword});

		//remove the option from the dropdown
		$(this).find('option:selected').remove();
		
		if($("#ls_select_field_rule option").length == 1){
			$("#ls_select_field_rule option").text('No More Fields Available');
		}

	});
	
	//delegate change event to the show/hide dropdown
    $('#ls_box_field_rules').delegate('select.rule_show_hide', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		$("#lifieldrule_" + temp[1]).data('rule_properties').rule_show_hide = $(this).val();
    });

    //delegate change event to the all/any dropdown
    $('#ls_box_field_rules').delegate('select.rule_all_any', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		$("#lifieldrule_" + temp[1]).data('rule_properties').rule_all_any = $(this).val();
    });
	
	//delegate change event into condition field name dropdown
	$('#ls_box_field_rules').delegate('select.condition_fieldname', 'change', function(e) {
			
			var new_element_name = $(this).val();
			var new_element_type = $("#ls_fields_lookup").data(new_element_name);

			$(this).parent().find('.condition_text,.condition_time,.condition_number,.condition_date,.condition_checkbox,.condition_keyword,.condition_select,.condition_rating,.condition_ratingvalues').hide();
			$(this).parent().removeClass('condition_date');

			//reset keyword
			$(this).parent().data('rule_condition').keyword = '';
			$(this).parent().find('.condition_keyword').val('');

			//display the appropriate condition type dropdown, depends on the field type
			//and make sure to update the condition property value when the field type has been changed
			if(new_element_type == 'money' || new_element_type == 'number'){
				$(this).parent().find('.condition_number,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_number').val();
			}else if(new_element_type == 'date' || new_element_type == 'europe_date'){
				$(this).parent().addClass('condition_date');
				$(this).parent().find('.condition_date,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_date').val();
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

				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_date').val();
			}else if(new_element_type == 'checkbox'){
				$(this).parent().find('.condition_checkbox').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_checkbox').val();
			}else if(new_element_type == 'radio' || new_element_type == 'select'){
				//reset condition type
				$(this).parent().find('.condition_text').show().val('is');
				$(this).parent().data('rule_condition').condition = 'is';

				//reset condition keyword with dropdown values and display it
				$(this).parent().find('.condition_select').html($("#" + new_element_name + "_lookup").html()).show();
				$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_select').eq(0).val();
			}else if(new_element_type == 'rating'){
				//reset condition type
				$(this).parent().find('.condition_rating').show().val('is');
				$(this).parent().data('rule_condition').condition = 'is';

				//reset condition keyword with dropdown values and display it
				$(this).parent().find('.condition_ratingvalues').html($("#" + new_element_name + "_lookup").html()).show();
				$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_ratingvalues').eq(0).val();
			}else{
				$(this).parent().find('.condition_text,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_text').val();
			}

			$(this).parent().data('rule_condition').element_name = new_element_name;

    });
	
	//delegate change event to the condition type dropdown (for number, date, checkbox, rating)
    $('#ls_box_field_rules').delegate('select.condition_number,select.condition_date,select.condition_checkbox,select.condition_rating', 'change', function(e) {
			$(this).parent().data('rule_condition').condition = $(this).val();
    });

    //delegate change event to the condition type dropdown (for other fields beside the above)
    $('#ls_box_field_rules').delegate('select.condition_text', 'change', function(e) {
    	var element_name = $(this).parent().data('rule_condition').element_name;
    	var element_type = $("#ls_fields_lookup").data(element_name);

    	var condition_type = $(this).val();
    	
    	//if the field type is radio/dropdown, check for the selected condition type
    	//if condition type = 'is'/'is_not' , display the dropdown
    	if(element_type == 'radio' || element_type == 'select'){
    		$(this).parent().find('.condition_keyword,.condition_select').hide();

    		if(condition_type == 'is' || condition_type == 'is_not'){
    			$(this).parent().find('.condition_select').show();
    			$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_select').eq(0).val();
    		}else{
    			$(this).parent().find('.condition_keyword').show();
    			$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_keyword').val();
    		}
    	}

		$(this).parent().data('rule_condition').condition = condition_type;
    });

	//delegate change event to the condition select dropdown (only applicable for radio and select)
    $('#ls_box_field_rules').delegate('select.condition_select', 'change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();
    });
	
	//delegate change event to the condition select dropdown (only applicable for rating)
    $('#ls_box_field_rules').delegate('select.condition_ratingvalues', 'change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();
    });

	//delegate event to the condition keyword text
    $('#ls_box_field_rules').delegate('input.condition_keyword', 'keyup mouseout change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();	
    });

    //delegate event to the time condition inputs
    $('#ls_box_field_rules').delegate('input.conditiontime_input,select.conditiontime_input', 'keyup mouseout change', function(e) {
		
		var temp = $(this).attr("id").split("_");

		var hour_value 	 = parseInt($("#conditiontimehour_" + temp[1] + "_" + temp[2]).val(),10);
		var minute_value = parseInt($("#conditiontimeminute_" + temp[1] + "_" + temp[2]).val(),10);
		var second_value = parseInt($("#conditiontimesecond_" + temp[1] + "_" + temp[2]).val(),10);
		
		var ampm_value 	 = $("#conditiontimeampm_" + temp[1] + "_" + temp[2]).val();

		if(isNaN(hour_value)){
			hour_value = '00';
		}

		if(isNaN(minute_value)){
			minute_value = '00';
		}
		
		if(isNaN(second_value)){
			second_value = '00';
		}

		$("#lifieldrule_" + temp[1] + "_" + temp[2]).data('rule_condition').keyword = hour_value.toString() + ':' + minute_value.toString() + ':' + second_value.toString() + ':' + ampm_value;
    });
	
	//attach event to 'delete field rules' icon
	$('#ls_box_field_rules').delegate('a.delete_lifieldrule', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var element_id = temp[1];
		
		//restore field dropdown values
		$("#ls_select_field_rule").html($("#ls_select_field_rule_lookup").html());

		$("#lifieldrule_" + element_id).fadeOut(400,function(){
			$(this).remove();

			$("#ls_field_rules_group > li").each(function(){
				var temp_name = $(this).attr('id').split('_');
				var cur_element_id = temp_name[1];
				
				$("#ls_select_field_rule option[value="+ cur_element_id +"]").remove();			
			});
		});
		
		return false;
	});

	//attach click event to 'add rule condition' (+) icon
	$('#ls_box_field_rules').delegate('a.a_add_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var element_id = temp[1];

		var new_id = $("#lifieldrule_" + element_id + " ul > li:not('.ls_add_condition')").length + 1;
		var old_id = new_id - 1;

		//duplicate the last rule condition
		var last_rule_element = $("#lifieldrule_" + element_id + " ul > li:not('.ls_add_condition')").last();
		last_rule_element.clone(false).data('rule_condition',$.extend('{}',last_rule_element.data('rule_condition'))).find("*[id],*[name]").each(function() {
			var temp = $(this).attr("id").split("_"); 
			
			//rename the original id with the new id
			$(this).attr("id", temp[0] + "_" + temp[1] + "_" + new_id);
			$(this).attr("name", temp[0] + "_" + temp[1] + "_" + new_id);
			
		}).end().attr("id","lifieldrule_" + element_id + "_" + new_id).insertBefore("#lifieldrule_" + element_id + " li.ls_add_condition").hide().fadeIn();

		//copy the value of the dropdowns
		$("#conditionfield_" + element_id + "_" + new_id).val($("#conditionfield_" + element_id + "_" + old_id).val());
		$("#conditiontext_" + element_id + "_" + new_id).val($("#conditiontext_" + element_id + "_" + old_id).val());
		$("#conditionnumber_" + element_id + "_" + new_id).val($("#conditionnumber_" + element_id + "_" + old_id).val());
		$("#conditiondate_" + element_id + "_" + new_id).val($("#conditiondate_" + element_id + "_" + old_id).val());
		$("#conditioncheckbox_" + element_id + "_" + new_id).val($("#conditioncheckbox_" + element_id + "_" + old_id).val());
		
		//reset the condition keyword  
		$("#conditionkeyword_" + element_id + "_" + new_id).val('');
		$("#lifieldrule_" + element_id + "_" + new_id).data('rule_condition').keyword = '';

		//remove the datepicker and rebuild it, with the events as well
		$('#datepicker_' + element_id + '_' + new_id).next().next().remove();
		$('#datepicker_' + element_id + '_' + new_id).next().remove();
		$('#datepicker_' + element_id + '_' + new_id).remove();

		var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_' + element_id + '_' + new_id +'" id="datepicker_' + element_id + '_' + new_id +'"> ' +
								 '<span style="display:none"> <img id="datepickimg_'+ element_id + '_' + new_id +'" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

		$('#conditionkeyword_' + element_id + '_' + new_id).after(new_datepicker_tag);

		$('#datepicker_' + element_id + '_' + new_id).datepick({ 
	    		onSelect: select_date,
	    		showTrigger: '#datepickimg_' + element_id + '_' + new_id
		});

		return false;
	});

	//delegate click event to the 'delete rule condition' (-) icon
    $('#ls_box_field_rules').delegate('a.a_delete_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var element_id = temp[1];

		if($("#lifieldrule_" + element_id + " ul > li:not('.ls_add_condition')").length <= 1){
			$("#ui-dialog-title-dialog-warning").html('Unable to delete!');
			$("#dialog-warning-msg").html("You can't delete all condition. <br />You must have at least one condition.");
			$("#dialog-warning").dialog('open');
		}else{
			$(this).parent().fadeOut(function(){
				$(this).remove();
			});
		}

		return false;
    });


    /***************************************************************************************************************/	
	/* 4. Attach event to 'Save Settings' button																   */
	/***************************************************************************************************************/
	$("#button_save_logics").click(function(){
		
		if($("#button_save_logics").text() != 'Saving...'){
				
				//display loader while saving
				$("#button_save_logics").prop("disabled",true);
				$("#button_save_logics").text('Saving...');
				$("#button_save_logics").after("<div class='small_loader_box' style='float: right'><img src='images/loader_small_grey.gif' /></div>");
				
				//get field logic properties data
				var field_rule_properties_elements = $("#ls_field_rules_group > li");
				var field_rule_properties_data 	   = new Array();

				if(field_rule_properties_elements.length >= 1){
					field_rule_properties_elements.each(function(index){
						field_rule_properties_data[index] = $(this).data('rule_properties');
					});
				}

				var field_rule_condition_elements = $("#ls_field_rules_group ul.ls_field_rules_conditions > li:not('.ls_add_condition')");
				var field_rule_condition_data 	= new Array();

				if(field_rule_condition_elements.length >= 1){
					field_rule_condition_elements.each(function(index){
						field_rule_condition_data[index] = $(this).data('rule_condition');
					});
				}

				//get page logic properties data
				var page_rule_properties_elements = $("#ls_page_rules_group > li");
				var page_rule_properties_data 	   = new Array();

				if(page_rule_properties_elements.length >= 1){
					page_rule_properties_elements.each(function(index){
						page_rule_properties_data[index] = $(this).data('rule_properties');
					});
				}

				var page_rule_condition_elements = $("#ls_page_rules_group ul.ls_page_rules_conditions > li:not('.ls_add_condition')");
				var page_rule_condition_data 	= new Array();

				if(page_rule_condition_elements.length >= 1){
					page_rule_condition_elements.each(function(index){
						page_rule_condition_data[index] = $(this).data('rule_condition');
					});
				}

				//get email logic properties data
				var email_rule_properties_elements = $("#ls_email_rules_group > li");
				var email_rule_properties_data 	   = new Array();

				if(email_rule_properties_elements.length >= 1){
					email_rule_properties_elements.each(function(index){
						email_rule_properties_data[index] = $(this).data('rule_properties');
					});
				}

				var email_rule_condition_elements = $("#ls_email_rules_group ul.ls_email_rules_conditions > li:not('.ls_add_condition')");
				var email_rule_condition_data 	= new Array();

				if(email_rule_condition_elements.length >= 1){
					email_rule_condition_elements.each(function(index){
						email_rule_condition_data[index] = $(this).data('rule_condition');
					});
				}

				//get webhook logic properties data
				var webhook_rule_properties_elements = $("#ls_webhook_rules_group > li");
				var webhook_rule_properties_data 	 = new Array();

				if(webhook_rule_properties_elements.length >= 1){
					webhook_rule_properties_elements.each(function(index){
						webhook_rule_properties_data[index] = $(this).data('rule_properties');
					});
				}

				var webhook_rule_condition_elements = $("#ls_webhook_rules_group ul.ls_webhook_rules_conditions > li:not('.ls_add_condition')");
				var webhook_rule_condition_data 	= new Array();

				if(webhook_rule_condition_elements.length >= 1){
					webhook_rule_condition_elements.each(function(index){
						webhook_rule_condition_data[index] = $(this).data('rule_condition');
					});
				}
				
				var webhook_keyvalue_param_name_data  = $("#ls_webhook_rules_group li.ns_url_params .ns_param_name > input").serializeArray();
				var webhook_keyvalue_param_value_data = $("#ls_webhook_rules_group li.ns_url_params .ns_param_value > input").serializeArray();

				//get success page logic properties data
				var success_rule_properties_elements = $("#ls_success_rules_group > li");
				var success_rule_properties_data 	 = new Array();

				if(success_rule_properties_elements.length >= 1){
					success_rule_properties_elements.each(function(index){
						success_rule_properties_data[index] = $(this).data('rule_properties');
					});
				}

				var success_rule_condition_elements = $("#ls_success_rules_group ul.ls_success_rules_conditions > li:not('.ls_add_condition')");
				var success_rule_condition_data 	= new Array();

				if(success_rule_condition_elements.length >= 1){
					success_rule_condition_elements.each(function(index){
						success_rule_condition_data[index] = $(this).data('rule_condition');
					});
				}
				
				var csrf_token  = $("#content").data("csrftoken");

				//do the ajax call to save the settings
				$.ajax({
					   type: "POST",
					   async: true,
					   url: "save_logic_settings.php",
					   data: {
							  	form_id: $("#form_id").val(),
							  	csrf_token: csrf_token,
							  	logic_status: $(".logic_settings").data('logic_status'),
							  	field_rule_properties: field_rule_properties_data,
							  	field_rule_conditions: field_rule_condition_data,
							  	page_rule_properties: page_rule_properties_data,
							  	page_rule_conditions: page_rule_condition_data,
							  	email_rule_properties: email_rule_properties_data,
							  	email_rule_conditions: email_rule_condition_data,
							  	webhook_rule_properties: webhook_rule_properties_data,
							  	webhook_rule_conditions: webhook_rule_condition_data,
							  	webhook_keyvalue_param_names: webhook_keyvalue_param_name_data,
							  	webhook_keyvalue_param_values: webhook_keyvalue_param_value_data,
							  	success_rule_properties: success_rule_properties_data,
							  	success_rule_conditions: success_rule_condition_data
							  },
					   cache: false,
					   global: false,
					   dataType: "json",
					   error: function(xhr,text_status,e){
							   //error, display the generic error message		  
							   alert('Error! Unable to save logic settings. Please try again.');
					   },
					   success: function(response_data){
							   
						   if(response_data.status == 'ok'){
							   window.location.replace('logic_settings.php?id=' + response_data.form_id);
						   }else{
							   alert('Error! Unable to save logic settings. Please try again.');
						   }
							   
					   }
				});
		}
		
		
		return false;
	});
	
    /***************************************************************************************************************/	
	/* 5. Initialize rule date pickers																			   */
	/***************************************************************************************************************/
	$("#ls_box_field_rules .rule_datepicker").each(function(index){
		var temp = $(this).attr('id').split('_');
		var element_id = temp[1] + '_' + temp[2];

		$('#datepicker_' + element_id).datepick({ 
	    		onSelect: select_date,
	    		showTrigger: '#datepickimg_' + element_id
		});
	});

	$("#ls_box_page_rules .rule_datepicker").each(function(index){
		var temp = $(this).attr('id').split('_');
		var element_id = temp[1] + '_' + temp[2];

		$('#datepicker_' + element_id).datepick({ 
	    		onSelect: select_date_page,
	    		showTrigger: '#datepickimg_' + element_id
		});
	});

	$("#ls_box_email_rules .rule_datepicker").each(function(index){
		var temp = $(this).attr('id').split('_');
		var element_id = temp[1] + '_' + temp[2];

		$('#datepicker_' + element_id).datepick({ 
	    		onSelect: select_date_email,
	    		showTrigger: '#datepickimg_' + element_id
		});
	});

	$("#ls_box_webhook_rules .rule_datepicker").each(function(index){
		var temp = $(this).attr('id').split('_');
		var element_id = temp[1] + '_' + temp[2];

		$('#datepicker_' + element_id).datepick({ 
	    		onSelect: select_date_webhook,
	    		showTrigger: '#datepickimg_' + element_id
		});
	});

	$("#ls_box_success_rules .rule_datepicker").each(function(index){
		var temp = $(this).attr('id').split('_');
		var element_id = temp[1] + '_' + temp[2];

		$('#datepicker_' + element_id).datepick({ 
	    		onSelect: select_date_success,
	    		showTrigger: '#datepickimg_' + element_id
		});
	});

	/***************************************************************************************************************/	
	/* 6. Page Logic (Skip Pages) Pane																			   */
	/***************************************************************************************************************/

	//attach event to 'enable rules to skip pages' checkbox
	$("#logic_page_enable").click(function(){
		if($(this).prop("checked") == true){
			$("#ls_box_page_rules .ls_box_content").slideDown();
			$(".logic_settings").data('logic_status').logic_page_enable = 1;
		}else{
			$("#ls_box_page_rules .ls_box_content").slideUp();
			$(".logic_settings").data('logic_status').logic_page_enable = 0;
		}
	});

	//attach event to 'select destination page' dropdown
	$('#ls_select_page_rule').bind('change', function() {
		
		if($(this).val() == ''){
			return true;
		}
		
		//val() could be page number or these: review, payment, success
		//we added 'page' prefix so that the generated id won't be the same with the show/hide logic fields
		var page_id = 'page' + $(this).val(); 

		//build the markup
		var li_markup = '';
		var condition_fieldname_markup = '';

		condition_fieldname_markup = $("#ls_fields_lookup").clone(false).attr("id","conditionpage_" + page_id + "_1").attr("name","conditionpage_" + page_id + "_1").show().outerHTML();

		li_markup 	+= 	'<li id="lipagerule_'+ page_id +'" style="display: none">' +
							'<table width="100%" cellspacing="0">' +
							  '<thead>' +
							    '<tr>' +
							      '<td>' + 
							         '<strong>' + $(this).find('option:selected').text() + '</strong><a class="delete_lipagerule" id="deletelipagerule_'+ page_id +'" href="#"><span class="icon-cross2"></span></a>' + 
							      '</td>' +
							    '</tr>' +
							  '</thead>' +
							  '<tbody>' +
							    '<tr>' +
							      '<td>' +
							      	'<h6>' +
							      		'Go to this page if ' + 
										'<select style="margin-left: 5px;margin-right: 5px" name="pageruleallany_' + page_id + '" id="pageruleallany_' + page_id + '" class="element select rule_all_any">' + 
											'<option value="all">all</option>' +
											'<option value="any">any</option>' +
										'</select>' + 
										' of the following conditions match:' +
									'</h6>' +
							      	'<ul class="ls_page_rules_conditions">' +
							      		'<li id="lipagerule_' + page_id + '_1"> ' +
							      			 condition_fieldname_markup + ' ' +
											'<select name="conditiontext_'+ page_id +'_1" id="conditiontext_'+ page_id +'_1" class="element select condition_text" style="width: 120px;display: none">' +
												'<option value="is">Is</option>' +
												'<option value="is_not">Is Not</option>' +
												'<option value="begins_with">Begins with</option>' +
												'<option value="ends_with">Ends with</option>' +
												'<option value="contains">Contains</option>' +
												'<option value="not_contain">Does not contain</option>' +
											'</select>' + ' ' +
											'<select id="conditionnumber_'+ page_id +'_1" name="conditionnumber_' + page_id + '_1" style="width: 120px;display: none" class="element select condition_number">' + 
												'<option value="is" selected="selected">Is</option>' + 
												'<option value="less_than">Less than</option>' + 
												'<option value="greater_than">Greater than</option>' + 
											'</select>' + ' ' +
											'<select id="conditionrating_'+ page_id +'_1" name="conditionrating_' + page_id + '_1" style="width: 120px;display: none" class="element select condition_rating">' + 
												'<option value="is" selected="selected">Is</option>' + 
												'<option value="is_not">Is Not</option>' + 
												'<option value="less_than">Less than</option>' + 
												'<option value="greater_than">Greater than</option>' + 
											'</select>' + ' ' +
											'<select id="conditiondate_'+ page_id +'_1" name="conditiondate_' + page_id + '_1" style="width: 120px;display: none" class="element select condition_date">' + 
												'<option value="is" selected="selected">Is</option>' + 
												'<option value="is_before">Is Before</option>' + 
												'<option value="is_after">Is After</option>' + 
											'</select>' + ' ' + 
											'<select id="conditioncheckbox_'+ page_id +'_1" name="conditioncheckbox_' + page_id + '_1" style="width: 120px;display: none" class="element select condition_checkbox">' + 
												'<option value="is_one">Is Checked</option>' + 
												'<option value="is_zero">Is Empty</option>' + 
											'</select>' + ' ' + "\n" +
											'<select id="conditionselect_'+ page_id +'_1" name="conditionselect_' + page_id + '_1" style="display: none" class="element select condition_select">' + 
												'<option value=""></option>' +  
											'</select>' + ' ' + "\n" +
											'<select id="conditionratingvalues_'+ page_id +'_1" name="conditionratingvalues_' + page_id + '_1" style="display: none" class="element select condition_ratingvalues">' + 
												'<option value=""></option>' +  
											'</select>' + ' ' + "\n" +
											'<span name="conditiontime_' + page_id + '_1" id="conditiontime_'+ page_id + '_1" class="condition_time" style="display: none">' + 
												'<input name="conditiontimehour_' + page_id + '_1" id="conditiontimehour_' + page_id + '_1" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="" placeholder="HH"> : ' + 
												'<input name="conditiontimeminute_' + page_id + '_1" id="conditiontimeminute_' + page_id + '_1" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="" placeholder="MM">  ' + 
												'<span class="conditiontime_second" style="display:none"> : <input name="conditiontimesecond_' + page_id + '_1" id="conditiontimesecond_' + page_id + '_1" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="" placeholder="SS"> </span>' + 
												'<select class="element select conditiontime_ampm conditiontime_input" name="conditiontimeampm_' + page_id + '_1" id="conditiontimeampm_' + page_id + '_1" style="display:none">' + 
													'<option selected="selected" value="AM">AM</option>' + 
													'<option value="PM">PM</option>' + 
												'</select>' + 
											'</span>' + 
											'<input type="text" class="element text condition_keyword" value="" id="conditionkeyword_'+ page_id +'_1" name="conditionkeyword_'+ page_id +'_1" style="display: none"> ' + "\n" +
											'<a href="#" id="deletecondition_' + page_id + '_1" name="deletecondition_'+ page_id +'_1" class="a_delete_condition"><span class="icon-minus-circle2"></span></a>' + "\n" +
							      		'</li>' +
							      		'<li class="ls_add_condition">' +
											'<a href="#" id="addcondition_'+ page_id +'" class="a_add_condition"><span class="icon-plus-circle"></span></a>' +
										'</li>' +
							      	'</ul>' +
							      '</td>' +
							    '</tr>' +
							  '</tbody>' +
							'</table>' +
						'</li>';

		//append the rule markup
		$("#ls_page_rules_group").prepend(li_markup);
		$("#lipagerule_" + page_id).hide();

		//remove the current element from the list of condition
		$("#conditionpage_" + page_id + "_1 option[value=element_"+ page_id +"]").remove();
		$("#conditionpage_" + page_id + "_1 option[value^=element_"+ page_id +"_]").remove(); //remove childs element (for checkbox)

		//diplay the condition operator, depends on the first field on the field list
		var first_field_element_name = $("#conditionpage_" + page_id + "_1").eq(0).val();
		var first_field_element_type = $("#ls_fields_lookup").data(first_field_element_name);
		var default_condition = 'is';
		var default_keyword = '';

		//populate options for condition_select
		$("#conditionselect_" + page_id + "_1").html($("#" + first_field_element_name + "_lookup").html());

		//populate options for condition_ratingvalues
		$("#conditionratingvalues_" + page_id + "_1").html($("#" + first_field_element_name + "_lookup").html());

		if(first_field_element_type == 'money' || first_field_element_type == 'number'){
			$("#conditionnumber_" + page_id + "_1").show();
			$("#conditionkeyword_" + page_id + "_1").show();
		}else if(first_field_element_type == 'date' || first_field_element_type == 'europe_date'){
			$("#conditiondate_" + page_id + "_1").show();
			$("#conditionkeyword_" + page_id + "_1").show();

			$("#lipagerule_" + page_id + "_1").addClass("condition_date");
		}else if(first_field_element_type == 'time' || first_field_element_type == 'time_showsecond' || first_field_element_type == 'time_24hour' || first_field_element_type == 'time_showsecond24hour'){
			$("#conditiondate_" + page_id + "_1").show();
			$("#conditiontime_" + page_id + "_1").show();
			
			if(first_field_element_type == 'time'){
				$("#conditiontimeampm_" + page_id + "_1").show();
			}else if(first_field_element_type == 'time_showsecond'){
				$("#conditiontimeampm_" + page_id + "_1").show();
				$("#conditiontimesecond_" + page_id + "_1").parent().show();
			}else if(first_field_element_type == 'time_showsecond24hour'){
				$("#conditiontimesecond_" + page_id + "_1").parent().show();
			}

		}else if(first_field_element_type == 'checkbox'){
			$("#conditioncheckbox_" + page_id + "_1").show();
			default_condition = 'is_one'
		}else if(first_field_element_type == 'select' || first_field_element_type == 'radio'){
			$("#conditiontext_" + page_id + "_1").show();
			$("#conditionselect_" + page_id + "_1").show();

			default_keyword =  $("#conditionselect_" + page_id + "_1").eq(0).val();
		}else if(first_field_element_type == 'rating'){
			$("#conditionrating_" + page_id + "_1").show();
			$("#conditionratingvalues_" + page_id + "_1").show();

			default_keyword =  $("#conditionratingvalues_" + page_id + "_1").eq(0).val();
		}else{
			$("#conditiontext_" + page_id + "_1").show();
			$("#conditionkeyword_" + page_id + "_1").show();
		}

		//build the datepicker
		var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_'+ page_id +'_1" id="datepicker_'+ page_id +'_1">' + "\n" +
							 	 ' <span style="display:none"><img id="datepickimg_'+ page_id +'_1" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

		$('#conditionkeyword_' + page_id + '_1').after(new_datepicker_tag);

		$('#datepicker_' + page_id + '_1').datepick({ 
		   		onSelect: select_date_page,
		   		showTrigger: '#datepickimg_' + page_id + '_1'
		});

		$("#lipagerule_" + page_id).slideDown();

		//attach dom data
		$("#lipagerule_" + page_id).data('rule_properties',{"page_id": page_id, "rule_all_any":"all"});
		$("#lipagerule_" + page_id + "_1").data('rule_condition',{"target_page_id": page_id,"element_name": first_field_element_name, "condition": default_condition,"keyword": default_keyword});

		//remove the option from the dropdown
		$(this).find('option:selected').remove();
		
		if($("#ls_select_page_rule option").length == 1){
			$("#ls_select_page_rule option").text('No More Page Available');
		}

	});

	//delegate change event to the all/any dropdown
    $('#ls_box_page_rules').delegate('select.rule_all_any', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		$("#lipagerule_" + temp[1]).data('rule_properties').rule_all_any = $(this).val();
    });

    //delegate change event into condition field name dropdown
	$('#ls_box_page_rules').delegate('select.condition_fieldname', 'change', function(e) {
			
			var new_element_name = $(this).val();
			var new_element_type = $("#ls_fields_lookup").data(new_element_name);

			$(this).parent().find('.condition_text,.condition_time,.condition_number,.condition_date,.condition_checkbox,.condition_keyword,.condition_select,.condition_rating,.condition_ratingvalues').hide();
			$(this).parent().removeClass('condition_date');

			//reset keyword
			$(this).parent().data('rule_condition').keyword = '';
			$(this).parent().find('.condition_keyword').val('');


			//display the appropriate condition type dropdown, depends on the field type
			//and make sure to update the condition property value when the field type has been changed
			if(new_element_type == 'money' || new_element_type == 'number'){
				$(this).parent().find('.condition_number,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_number').val();
			}else if(new_element_type == 'date' || new_element_type == 'europe_date'){
				$(this).parent().addClass('condition_date');
				$(this).parent().find('.condition_date,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_date').val();
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

				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_date').val();
			}else if(new_element_type == 'checkbox'){
				$(this).parent().find('.condition_checkbox').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_checkbox').val();
			}else if(new_element_type == 'radio' || new_element_type == 'select'){
				//reset condition type
				$(this).parent().find('.condition_text').show().val('is');
				$(this).parent().data('rule_condition').condition = 'is';

				//reset condition keyword with dropdown values and display it
				$(this).parent().find('.condition_select').html($("#" + new_element_name + "_lookup").html()).show();
				$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_select').eq(0).val();
			}else if(new_element_type == 'rating'){
				//reset condition type
				$(this).parent().find('.condition_rating').show().val('is');
				$(this).parent().data('rule_condition').condition = 'is';

				//reset condition keyword with dropdown values and display it
				$(this).parent().find('.condition_ratingvalues').html($("#" + new_element_name + "_lookup").html()).show();
				$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_ratingvalues').eq(0).val();
			}else{
				$(this).parent().find('.condition_text,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_text').val();
			}

			$(this).parent().data('rule_condition').element_name = new_element_name;

    });
	
	//delegate change event to the condition type dropdown (for number, date, checkbox, rating)
    $('#ls_box_page_rules').delegate('select.condition_number,select.condition_date,select.condition_checkbox,select.condition_rating', 'change', function(e) {
		$(this).parent().data('rule_condition').condition = $(this).val();
    });

    //delegate change event to the condition type dropdown (for other fields beside the above)
    $('#ls_box_page_rules').delegate('select.condition_text', 'change', function(e) {
    	var element_name = $(this).parent().data('rule_condition').element_name;
    	var element_type = $("#ls_fields_lookup").data(element_name);

    	var condition_type = $(this).val();
    	
    	//if the field type is radio/dropdown, check for the selected condition type
    	//if condition type = 'is'/'is_not' , display the dropdown
    	if(element_type == 'radio' || element_type == 'select'){
    		$(this).parent().find('.condition_keyword,.condition_select').hide();

    		if(condition_type == 'is' || condition_type == 'is_not'){
    			$(this).parent().find('.condition_select').show();
    			$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_select').eq(0).val();
    		}else{
    			$(this).parent().find('.condition_keyword').show();
    			$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_keyword').val();
    		}
    	}

		$(this).parent().data('rule_condition').condition = condition_type;
    });

	//delegate change event to the condition select dropdown (only applicable for radio and select)
    $('#ls_box_page_rules').delegate('select.condition_select', 'change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();
    });

    //delegate change event to the condition select dropdown (only applicable for rating)
    $('#ls_box_page_rules').delegate('select.condition_ratingvalues', 'change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();
    });

    //delegate event to the condition keyword text
    $('#ls_box_page_rules').delegate('input.condition_keyword', 'keyup mouseout change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();	
    });

    //delegate event to the time condition inputs
    $('#ls_box_page_rules').delegate('input.conditiontime_input,select.conditiontime_input', 'keyup mouseout change', function(e) {
		
		var temp = $(this).attr("id").split("_");

		var hour_value 	 = parseInt($("#conditiontimehour_" + temp[1] + "_" + temp[2]).val(),10);
		var minute_value = parseInt($("#conditiontimeminute_" + temp[1] + "_" + temp[2]).val(),10);
		var second_value = parseInt($("#conditiontimesecond_" + temp[1] + "_" + temp[2]).val(),10);
		var ampm_value 	 = $("#conditiontimeampm_" + temp[1] + "_" + temp[2]).val();

		if(isNaN(hour_value)){
			hour_value = '00';
		}

		if(isNaN(minute_value)){
			minute_value = '00';
		}
		
		if(isNaN(second_value)){
			second_value = '00';
		}

		$("#lipagerule_" + temp[1] + "_" + temp[2]).data('rule_condition').keyword = hour_value.toString() + ':' + minute_value.toString() + ':' + second_value.toString() + ':' + ampm_value;
    });

    //attach event to 'delete page rules' icon
	$('#ls_box_page_rules').delegate('a.delete_lipagerule', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var page_id = temp[1];
		
		//restore field dropdown values
		$("#ls_select_page_rule").html($("#ls_select_page_rule_lookup").html());

		$("#lipagerule_" + page_id).fadeOut(400,function(){
			$(this).remove();

			$("#ls_page_rules_group > li").each(function(){
				var temp_name = $(this).attr('id').split('_');
				var cur_page_id = temp_name[1];

				cur_page_id = cur_page_id.substring(4); //remove the 'page' prefix
				
				$("#ls_select_page_rule option[value="+ cur_page_id +"]").remove();			
			});
		});
		
		return false;
	});

	//attach click event to 'add rule condition' (+) icon
	$('#ls_box_page_rules').delegate('a.a_add_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var page_id = temp[1];

		var new_id = $("#lipagerule_" + page_id + " ul > li:not('.ls_add_condition')").length + 1;
		var old_id = new_id - 1;

		//duplicate the last rule condition
		var last_rule_element = $("#lipagerule_" + page_id + " ul > li:not('.ls_add_condition')").last();
		last_rule_element.clone(false).data('rule_condition',$.extend('{}',last_rule_element.data('rule_condition'))).find("*[id],*[name]").each(function() {
			var temp = $(this).attr("id").split("_"); 
			
			//rename the original id with the new id
			$(this).attr("id", temp[0] + "_" + temp[1] + "_" + new_id);
			$(this).attr("name", temp[0] + "_" + temp[1] + "_" + new_id);
			
		}).end().attr("id","lipagerule_" + page_id + "_" + new_id).insertBefore("#lipagerule_" + page_id + " li.ls_add_condition").hide().fadeIn();

		//copy the value of the dropdowns
		$("#conditionpage_" + page_id + "_" + new_id).val($("#conditionpage_" + page_id + "_" + old_id).val());
		$("#conditiontext_" + page_id + "_" + new_id).val($("#conditiontext_" + page_id + "_" + old_id).val());
		$("#conditionnumber_" + page_id + "_" + new_id).val($("#conditionnumber_" + page_id + "_" + old_id).val());
		$("#conditiondate_" + page_id + "_" + new_id).val($("#conditiondate_" + page_id + "_" + old_id).val());
		$("#conditioncheckbox_" + page_id + "_" + new_id).val($("#conditioncheckbox_" + page_id + "_" + old_id).val());
		
		//reset the condition keyword  
		$("#conditionkeyword_" + page_id + "_" + new_id).val('');
		$("#lipagerule_" + page_id + "_" + new_id).data('rule_condition').keyword = '';

		//remove the datepicker and rebuild it, with the events as well
		$('#datepicker_' + page_id + '_' + new_id).next().next().remove();
		$('#datepicker_' + page_id + '_' + new_id).next().remove();
		$('#datepicker_' + page_id + '_' + new_id).remove();

		var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_' + page_id + '_' + new_id +'" id="datepicker_' + page_id + '_' + new_id +'"> ' +
								 '<span style="display:none"> <img id="datepickimg_'+ page_id + '_' + new_id +'" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

		$('#conditionkeyword_' + page_id + '_' + new_id).after(new_datepicker_tag);

		$('#datepicker_' + page_id + '_' + new_id).datepick({ 
	    		onSelect: select_date_page,
	    		showTrigger: '#datepickimg_' + page_id + '_' + new_id
		});

		return false;
	});

	//delegate click event to the 'delete rule condition' (-) icon
    $('#ls_box_page_rules').delegate('a.a_delete_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var page_id = temp[1];

		if($("#lipagerule_" + page_id + " ul > li:not('.ls_add_condition')").length <= 1){
			$("#ui-dialog-title-dialog-warning").html('Unable to delete!');
			$("#dialog-warning-msg").html("You can't delete all condition. <br />You must have at least one condition.");
			$("#dialog-warning").dialog('open');
		}else{
			$(this).parent().fadeOut(function(){
				$(this).remove();
			});
		}

		return false;
    });

    /***************************************************************************************************************/	
	/* 7. Notification Emails Rules Pane					   												   	   */
	/***************************************************************************************************************/

	//attach event to 'enable rules to skip pages' checkbox
	$("#logic_email_enable").click(function(){
		if($(this).prop("checked") == true){
			$("#ls_box_email_rules .ls_box_content").slideDown();
			$(".logic_settings").data('logic_status').logic_email_enable = 1;
		}else{
			$("#ls_box_email_rules .ls_box_content").slideUp();
			$(".logic_settings").data('logic_status').logic_email_enable = 0;
		}
	});

	//delegate change event to the all/any dropdown
    $('#ls_box_email_rules').delegate('select.rule_all_any', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		$("#liemailrule_" + temp[1]).data('rule_properties').rule_all_any = $(this).val();
    });

    //delegate change event into condition field name dropdown
	$('#ls_box_email_rules').delegate('select.condition_fieldname', 'change', function(e) {
			
			var new_element_name = $(this).val();
			var new_element_type = $("#ls_fields_lookup").data(new_element_name);

			//approval status field doesn't have type
			if(new_element_name == 'approval_status'){
				new_element_type = 'approval_status';
			}

			//'form is submitted' field doesn't have type
			if(new_element_name == 'form_is_submitted'){
				new_element_type = 'form_is_submitted';
			}

			$(this).parent().find('.condition_text,.condition_time,.condition_number,.condition_date,.condition_checkbox,.condition_approval_status,.condition_keyword,.condition_select,.condition_rating,.condition_ratingvalues').hide();
			$(this).parent().removeClass('condition_date');

			//reset keyword
			$(this).parent().data('rule_condition').keyword = '';
			$(this).parent().find('.condition_keyword').val('');

			//display the appropriate condition type dropdown, depends on the field type
			//and make sure to update the condition property value when the field type has been changed
			if(new_element_type == 'money' || new_element_type == 'number'){
				$(this).parent().find('.condition_number,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_number').val();
			}else if(new_element_type == 'date' || new_element_type == 'europe_date'){
				$(this).parent().addClass('condition_date');
				$(this).parent().find('.condition_date,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_date').val();
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

				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_date').val();
			}else if(new_element_type == 'checkbox'){
				$(this).parent().find('.condition_checkbox').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_checkbox').val();
			}else if(new_element_type == 'approval_status'){
				$(this).parent().find('.condition_approval_status').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_approval_status').val();
			}else if(new_element_type == 'radio' || new_element_type == 'select'){
				//reset condition type
				$(this).parent().find('.condition_text').show().val('is');
				$(this).parent().data('rule_condition').condition = 'is';

				//reset condition keyword with dropdown values and display it
				$(this).parent().find('.condition_select').html($("#" + new_element_name + "_lookup").html()).show();
				$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_select').eq(0).val();
			}else if(new_element_type == 'rating'){
				//reset condition type
				$(this).parent().find('.condition_rating').show().val('is');
				$(this).parent().data('rule_condition').condition = 'is';

				//reset condition keyword with dropdown values and display it
				$(this).parent().find('.condition_ratingvalues').html($("#" + new_element_name + "_lookup").html()).show();
				$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_ratingvalues').eq(0).val();
			}else if(new_element_type == 'form_is_submitted'){
				$(this).parent().data('rule_condition').keyword = '';
			}else{
				$(this).parent().find('.condition_text,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_text').val();
			}

			$(this).parent().data('rule_condition').element_name = new_element_name;

    });
	
	//delegate change event to the condition type dropdown (for number, date, checkbox, rating)
    $('#ls_box_email_rules').delegate('select.condition_number,select.condition_date,select.condition_checkbox,select.condition_approval_status,select.condition_rating', 'change', function(e) {
		$(this).parent().data('rule_condition').condition = $(this).val();
    });

    //delegate change event to the condition type dropdown (for other fields beside the above)
    $('#ls_box_email_rules').delegate('select.condition_text', 'change', function(e) {
    	var element_name = $(this).parent().data('rule_condition').element_name;
    	var element_type = $("#ls_fields_lookup").data(element_name);

    	var condition_type = $(this).val();
    	
    	//if the field type is radio/dropdown, check for the selected condition type
    	//if condition type = 'is'/'is_not' , display the dropdown
    	if(element_type == 'radio' || element_type == 'select'){
    		$(this).parent().find('.condition_keyword,.condition_select').hide();

    		if(condition_type == 'is' || condition_type == 'is_not'){
    			$(this).parent().find('.condition_select').show();
    			$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_select').eq(0).val();
    		}else{
    			$(this).parent().find('.condition_keyword').show();
    			$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_keyword').val();
    		}
    	}

		$(this).parent().data('rule_condition').condition = condition_type;
    });

	//delegate change event to the condition select dropdown (only applicable for radio and select)
    $('#ls_box_email_rules').delegate('select.condition_select', 'change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();
    });

    //delegate change event to the condition select dropdown (only applicable for rating)
    $('#ls_box_email_rules').delegate('select.condition_ratingvalues', 'change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();
    });

    //delegate event to the condition keyword text
    $('#ls_box_email_rules').delegate('input.condition_keyword', 'keyup mouseout change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();		
    });

    //delegate event to the time condition inputs
    $('#ls_box_email_rules').delegate('input.conditiontime_input,select.conditiontime_input', 'keyup mouseout change', function(e) {
		
		var temp = $(this).attr("id").split("_");

		var hour_value 	 = parseInt($("#conditiontimehour_" + temp[1] + "_" + temp[2]).val(),10);
		var minute_value = parseInt($("#conditiontimeminute_" + temp[1] + "_" + temp[2]).val(),10);
		var second_value = parseInt($("#conditiontimesecond_" + temp[1] + "_" + temp[2]).val(),10);
		var ampm_value 	 = $("#conditiontimeampm_" + temp[1] + "_" + temp[2]).val();

		if(isNaN(hour_value)){
			hour_value = '00';
		}

		if(isNaN(minute_value)){
			minute_value = '00';
		}
		
		if(isNaN(second_value)){
			second_value = '00';
		}

		$("#liemailrule_" + temp[1] + "_" + temp[2]).data('rule_condition').keyword = hour_value.toString() + ':' + minute_value.toString() + ':' + second_value.toString() + ':' + ampm_value;
    });

    //attach event to 'delete email rules' icon
	$('#ls_box_email_rules').delegate('a.delete_liemailrule', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var rule_id = temp[1];
		
		if($("#ls_email_rules_group > li").length <= 1){
			$("#ui-dialog-title-dialog-warning").html('Unable to delete!');
			$("#dialog-warning-msg").html("You can't delete all rules. <br />You must have at least one rule.");
			$("#dialog-warning").dialog('open');
		}else{
			$("#liemailrule_" + rule_id).fadeOut(400,function(){
				$(this).remove();
			});
		}
		
		return false;
	});

	//attach click event to 'add rule condition' (+) icon
	$('#ls_box_email_rules').delegate('a.a_add_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var rule_id = temp[1];

		var new_id = $("#liemailrule_" + rule_id + " ul > li:not('.ls_add_condition')").length + 1;
		var old_id = new_id - 1;

		//duplicate the last rule condition
		var last_rule_element = $("#liemailrule_" + rule_id + " ul > li:not('.ls_add_condition')").last();
		last_rule_element.clone(false).data('rule_condition',$.extend('{}',last_rule_element.data('rule_condition'))).find("*[id],*[name]").each(function() {
			var temp = $(this).attr("id").split("_"); 
			
			//rename the original id with the new id
			$(this).attr("id", temp[0] + "_" + temp[1] + "_" + new_id);
			$(this).attr("name", temp[0] + "_" + temp[1] + "_" + new_id);
			
		}).end().attr("id","liemailrule_" + rule_id + "_" + new_id).insertBefore("#liemailrule_" + rule_id + " li.ls_add_condition").hide().fadeIn();

		//copy the value of the dropdowns
		$("#conditionemail_" + rule_id + "_" + new_id).val($("#conditionemail_" + rule_id + "_" + old_id).val());
		$("#conditiontext_" + rule_id + "_" + new_id).val($("#conditiontext_" + rule_id + "_" + old_id).val());
		$("#conditionnumber_" + rule_id + "_" + new_id).val($("#conditionnumber_" + rule_id + "_" + old_id).val());
		$("#conditiondate_" + rule_id + "_" + new_id).val($("#conditiondate_" + rule_id + "_" + old_id).val());
		$("#conditioncheckbox_" + rule_id + "_" + new_id).val($("#conditioncheckbox_" + rule_id + "_" + old_id).val());
		$("#conditionapprovalstatus_" + rule_id + "_" + new_id).val($("#conditionapprovalstatus_" + rule_id + "_" + old_id).val());
		
		//reset the condition keyword  
		$("#conditionkeyword_" + rule_id + "_" + new_id).val('');
		$("#liemailrule_" + rule_id + "_" + new_id).data('rule_condition').keyword = '';

		//remove the datepicker and rebuild it, with the events as well
		$('#datepicker_' + rule_id + '_' + new_id).next().next().remove();
		$('#datepicker_' + rule_id + '_' + new_id).next().remove();
		$('#datepicker_' + rule_id + '_' + new_id).remove();

		var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_' + rule_id + '_' + new_id +'" id="datepicker_' + rule_id + '_' + new_id +'"> ' +
								 '<span style="display:none"> <img id="datepickimg_'+ rule_id + '_' + new_id +'" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

		$('#conditionkeyword_' + rule_id + '_' + new_id).after(new_datepicker_tag);

		$('#datepicker_' + rule_id + '_' + new_id).datepick({ 
	    		onSelect: select_date_email,
	    		showTrigger: '#datepickimg_' + rule_id + '_' + new_id
		});

		return false;
	});

	//delegate click event to the 'delete rule condition' (-) icon
    $('#ls_box_email_rules').delegate('a.a_delete_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var rule_id = temp[1];

		if($("#liemailrule_" + rule_id + " ul > li:not('.ls_add_condition')").length <= 1){
			$("#ui-dialog-title-dialog-warning").html('Unable to delete!');
			$("#dialog-warning-msg").html("You can't delete all condition. <br />You must have at least one condition.");
			$("#dialog-warning").dialog('open');
		}else{
			$(this).parent().fadeOut(function(){
				$(this).remove();
			});
		}

		return false;
    });

    //delegate change event to the 'send email to' dropdown
    $('#ls_box_email_rules').delegate('select.target_email_dropdown', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		var target_email = $(this).val();
		
		if(target_email == 'custom'){
			$("#targetemailcustomspan_" + temp[1]).show();
			target_email = $("#targetemailcustom_" + temp[1]).val();
		}else{
			$("#targetemailcustomspan_" + temp[1]).hide();
		}

		$("#liemailrule_" + temp[1]).data('rule_properties').target_email = target_email;
		
    });

    //delegate event to the 'custom target email' text
    $('#ls_box_email_rules').delegate('input.target_email_custom', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liemailrule_" + temp[1]).data('rule_properties').target_email = $(this).val();
    });

    //delegate change event to the 'email template' dropdown
    $('#ls_box_email_rules').delegate('select.template_name', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		
		$("#liemailrule_" + temp[1]).data('rule_properties').template_name = $(this).val();

		if($(this).val() == 'custom'){
			$("#ls_email_rules_delay_notification_div_" + temp[1]).hide();
			$("#ls_email_custom_template_div_" + temp[1]).fadeIn();
		}else{
			$("#ls_email_rules_delay_notification_div_" + temp[1]).show();
			$("#ls_email_custom_template_div_" + temp[1]).fadeOut();
		}
    });

    //delegate change event to the 'custom from name' dropdown
    $('#ls_box_email_rules').delegate('select.custom_from_name_dropdown', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		var from_name = $(this).val();
		
		if(from_name == 'custom'){
			$("#customfromnamespan_" + temp[1]).show();
			from_name = $("#customfromnameuser_" + temp[1]).val();
		}else{
			$("#customfromnamespan_" + temp[1]).hide();
		}

		$("#liemailrule_" + temp[1]).data('rule_properties').custom_from_name = from_name;
		
    });

    //delegate event to the 'custom from name' text
    $('#ls_box_email_rules').delegate('input.custom_from_name_text', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liemailrule_" + temp[1]).data('rule_properties').custom_from_name = $(this).val();
    });

    //delegate change event to the 'custom replyto email' dropdown
    $('#ls_box_email_rules').delegate('select.custom_replyto_email_dropdown', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		var replyto_email = $(this).val();
		
		if(replyto_email == 'custom'){
			$("#customreplytoemailspan_" + temp[1]).show();
			replyto_email = $("#customreplytoemailuser_" + temp[1]).val();
		}else{
			$("#customreplytoemailspan_" + temp[1]).hide();
		}

		$("#liemailrule_" + temp[1]).data('rule_properties').custom_replyto_email = replyto_email;
		
    });

    //delegate event to the 'custom replyto email' text
    $('#ls_box_email_rules').delegate('input.custom_replyto_email_text', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liemailrule_" + temp[1]).data('rule_properties').custom_replyto_email = $(this).val();
    });

    //delegate event to the 'custom from email' text
    $('#ls_box_email_rules').delegate('input.custom_from_email', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liemailrule_" + temp[1]).data('rule_properties').custom_from_email = $(this).val();
    });

     //delegate event to the 'custom email bcc' text
    $('#ls_box_email_rules').delegate('input.custom_email_bcc', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liemailrule_" + temp[1]).data('rule_properties').custom_bcc = $(this).val();
    });

    //delegate event to the 'custom email subject' text
    $('#ls_box_email_rules').delegate('input.custom_email_subject', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liemailrule_" + temp[1]).data('rule_properties').custom_subject = $(this).val();
    });

    //delegate event to the 'custom email content' textarea
    $('#ls_box_email_rules').delegate('textarea.custom_email_content', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liemailrule_" + temp[1]).data('rule_properties').custom_content = $(this).val();
    });

    //delegate event to the 'custom PDF content' textarea
    $('#ls_box_email_rules').delegate('textarea.custom_pdf_content', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liemailrule_" + temp[1]).data('rule_properties').custom_pdf_content = $(this).val();
    });

    //attach event to 'plain text' checkbox
    $('#ls_box_email_rules').delegate('input.custom_plain_text', 'change', function(e) {
    	var temp = $(this).attr("id").split("_");

    	if($(this).prop("checked") == true){
			$("#liemailrule_" + temp[1]).data('rule_properties').custom_plain_text = 1;
		}else{
			$("#liemailrule_" + temp[1]).data('rule_properties').custom_plain_text = 0;
		}
    });

    //attach event to 'attach pdf' checkbox
    $('#ls_box_email_rules').delegate('input.custom_pdf_enable', 'change', function(e) {
    	var temp = $(this).attr("id").split("_");

    	if($(this).prop("checked") == true){
			$("#liemailrule_" + temp[1]).data('rule_properties').custom_pdf_enable = 1;
			$("#divpdfcontent_" + temp[1]).show();
		}else{
			$("#liemailrule_" + temp[1]).data('rule_properties').custom_pdf_enable = 0;
			$("#divpdfcontent_" + temp[1]).hide();
		}
    });

     //attach event to Email 'Delay Notification until Paid' checkbox
    $('#ls_box_email_rules').delegate('input.email_delay_notification', 'change', function(e) {
    	var temp = $(this).attr("id").split("_");

    	if($(this).prop("checked") == true){
    		$("#emaildelaynotification_" + temp[1]).prop("checked",true);
    		$("#customemaildelaynotification_" + temp[1]).prop("checked",true);
			$("#liemailrule_" + temp[1]).data('rule_properties').delay_notification_until_paid = 1;
		}else{
			$("#emaildelaynotification_" + temp[1]).prop("checked",false);
    		$("#customemaildelaynotification_" + temp[1]).prop("checked",false);
			$("#liemailrule_" + temp[1]).data('rule_properties').delay_notification_until_paid = 0;
		}

    });

	//attach event to 'add email rule' link
	$("#ls_add_email_rule").click(function(){
		
		//duplicate the last rule group
		var last_rule_element = $("#ls_email_rules_group > li").last();
		var last_rule_id = last_rule_element.attr("id");

		last_rule_id_clean = parseInt(last_rule_id.replace("liemailrule_email",""));
		var new_rule_id    = last_rule_id_clean + 1;


		last_rule_element.clone(false).find("*[id],*[name],*[for]").each(function() {
			if($(this).attr("id") !== undefined){
				var current_id = $(this).attr("id"); 
				var new_id = current_id.replace("email" + last_rule_id_clean, "email" + new_rule_id);

				//rename the original id with the new id
				$(this).attr("id", new_id);
				$(this).attr("name", new_id);
			}else{
				var current_id = $(this).attr("for"); 
				var new_id = current_id.replace("email" + last_rule_id_clean, "email" + new_rule_id);

				$(this).attr("for", new_id);
			}
		}).end().attr("id","liemailrule_email" + new_rule_id ).appendTo('#ls_email_rules_group').hide().fadeIn();

		//rename the new rule main label
		$("#liemailrule_email" + new_rule_id + " .rule_title").text("Rule #" + new_rule_id);

		//copy the data for main rule properties
		$("#liemailrule_email" + new_rule_id).data('rule_properties',$.extend('{}', last_rule_element.data('rule_properties')));
		$("#liemailrule_email" + new_rule_id).data('rule_properties').rule_id = new_rule_id;

		
		//copy the data from the previous conditions to the new one
		$("#liemailrule_email" + new_rule_id + " .ls_email_rules_conditions > li:not('.ls_add_condition')").each(function(){
			var current_condition_id = $(this).attr("id");
			var old_condition_id = current_condition_id.replace("email" + new_rule_id,"email" + last_rule_id_clean);

			//rule_condition data is an object, we need to use extend so that the object won't get passed by reference, screwing all other elements data
			$(this).data('rule_condition',$.extend('{}',$("#" + old_condition_id).data('rule_condition')));
			$(this).data('rule_condition').target_rule_id = 'email' + new_rule_id;

			//remove the datapicker and rebuild it, with the events as well
			var current_datepicker = $(this).find(".hasDatepick");
			var current_datepicker_id = current_datepicker.attr("id");

			current_datepicker.next().next().remove();
			current_datepicker.next().remove();
			current_datepicker.remove();

			var temp = current_datepicker_id.split("_");
			var condition_id = temp[2];

			var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_email' + new_rule_id + '_' + condition_id +'" id="datepicker_email' + new_rule_id + '_' + condition_id +'"> ' +
									 '<span style="display:none"> <img id="datepickimg_email'+ new_rule_id + '_' + condition_id +'" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

			$('#conditionkeyword_email' + new_rule_id + '_' + condition_id).after(new_datepicker_tag);

			$('#datepicker_email' + new_rule_id + '_' + condition_id).datepick({ 
		    		onSelect: select_date_email,
		    		showTrigger: '#datepickimg_email' + new_rule_id + '_' + condition_id
			});
		});

		//copy all dropdown selection state
		var original_rule_dropdowns = $("#liemailrule_email" + last_rule_id_clean).find("select");
		var new_rule_dropdowns =  $("#liemailrule_email" + new_rule_id).find("select");
		new_rule_dropdowns.each(function(index,item){
			//set new select to value of old select
     		$(item).val(original_rule_dropdowns.eq(index).val());
		});

		return false;

	});

	/***************************************************************************************************************/	
	/* 8. Webhook Rules Pane								   												   	   */
	/***************************************************************************************************************/

	//attach event to 'enable rules to send form data to another site' checkbox
	$("#logic_webhook_enable").click(function(){
		if($(this).prop("checked") == true){
			$("#ls_box_webhook_rules .ls_box_content").slideDown();
			$(".logic_settings").data('logic_status').logic_webhook_enable = 1;
		}else{
			$("#ls_box_webhook_rules .ls_box_content").slideUp();
			$(".logic_settings").data('logic_status').logic_webhook_enable = 0;
		}
	});

	//delegate change event to the all/any dropdown
    $('#ls_box_webhook_rules').delegate('select.rule_all_any', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		$("#liwebhookrule_" + temp[1]).data('rule_properties').rule_all_any = $(this).val();
    });

    //delegate change event into condition field name dropdown
	$('#ls_box_webhook_rules').delegate('select.condition_fieldname', 'change', function(e) {
			
			var new_element_name = $(this).val();
			var new_element_type = $("#ls_fields_lookup").data(new_element_name);

			//approval status field doesn't have type
			if(new_element_name == 'approval_status'){
				new_element_type = 'approval_status';
			}

			//'form is submitted' field doesn't have type
			if(new_element_name == 'form_is_submitted'){
				new_element_type = 'form_is_submitted';
			}

			$(this).parent().find('.condition_text,.condition_time,.condition_number,.condition_date,.condition_checkbox,.condition_approval_status,.condition_keyword,.condition_select,.condition_rating,.condition_ratingvalues').hide();
			$(this).parent().removeClass('condition_date');

			//reset keyword
			$(this).parent().data('rule_condition').keyword = '';
			$(this).parent().find('.condition_keyword').val('');

			//display the appropriate condition type dropdown, depends on the field type
			//and make sure to update the condition property value when the field type has been changed
			if(new_element_type == 'money' || new_element_type == 'number'){
				$(this).parent().find('.condition_number,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_number').val();
			}else if(new_element_type == 'date' || new_element_type == 'europe_date'){
				$(this).parent().addClass('condition_date');
				$(this).parent().find('.condition_date,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_date').val();
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

				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_date').val();
			}else if(new_element_type == 'checkbox'){
				$(this).parent().find('.condition_checkbox').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_checkbox').val();
			}else if(new_element_type == 'approval_status'){
				$(this).parent().find('.condition_approval_status').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_approval_status').val();
			}else if(new_element_type == 'radio' || new_element_type == 'select'){
				//reset condition type
				$(this).parent().find('.condition_text').show().val('is');
				$(this).parent().data('rule_condition').condition = 'is';

				//reset condition keyword with dropdown values and display it
				$(this).parent().find('.condition_select').html($("#" + new_element_name + "_lookup").html()).show();
				$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_select').eq(0).val();
			}else if(new_element_type == 'rating'){
				//reset condition type
				$(this).parent().find('.condition_rating').show().val('is');
				$(this).parent().data('rule_condition').condition = 'is';

				//reset condition keyword with dropdown values and display it
				$(this).parent().find('.condition_ratingvalues').html($("#" + new_element_name + "_lookup").html()).show();
				$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_ratingvalues').eq(0).val();
			}else if(new_element_type == 'form_is_submitted'){
				$(this).parent().data('rule_condition').keyword = '';
			}else{
				$(this).parent().find('.condition_text,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_text').val();
			}

			$(this).parent().data('rule_condition').element_name = new_element_name;

    });
	
	//delegate change event to the condition type dropdown (for number, date, checkbox, rating)
    $('#ls_box_webhook_rules').delegate('select.condition_number,select.condition_date,select.condition_checkbox,select.condition_approval_status,select.condition_rating', 'change', function(e) {
		$(this).parent().data('rule_condition').condition = $(this).val();
    });

    //delegate change event to the condition type dropdown (for other fields beside the above)
    $('#ls_box_webhook_rules').delegate('select.condition_text', 'change', function(e) {
    	var element_name = $(this).parent().data('rule_condition').element_name;
    	var element_type = $("#ls_fields_lookup").data(element_name);

    	var condition_type = $(this).val();
    	
    	//if the field type is radio/dropdown, check for the selected condition type
    	//if condition type = 'is'/'is_not' , display the dropdown
    	if(element_type == 'radio' || element_type == 'select'){
    		$(this).parent().find('.condition_keyword,.condition_select').hide();

    		if(condition_type == 'is' || condition_type == 'is_not'){
    			$(this).parent().find('.condition_select').show();
    			$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_select').eq(0).val();
    		}else{
    			$(this).parent().find('.condition_keyword').show();
    			$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_keyword').val();
    		}
    	}

		$(this).parent().data('rule_condition').condition = condition_type;
    });

	//delegate change event to the condition select dropdown (only applicable for radio and select)
    $('#ls_box_webhook_rules').delegate('select.condition_select', 'change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();
    });

    //delegate change event to the condition select dropdown (only applicable for rating)
    $('#ls_box_webhook_rules').delegate('select.condition_ratingvalues', 'change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();
    });

    //delegate event to the condition keyword text
    $('#ls_box_webhook_rules').delegate('input.condition_keyword', 'keyup mouseout change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();		
    });

    //delegate event to the time condition inputs
    $('#ls_box_webhook_rules').delegate('input.conditiontime_input,select.conditiontime_input', 'keyup mouseout change', function(e) {
		
		var temp = $(this).attr("id").split("_");

		var hour_value 	 = parseInt($("#conditiontimehour_" + temp[1] + "_" + temp[2]).val(),10);
		var minute_value = parseInt($("#conditiontimeminute_" + temp[1] + "_" + temp[2]).val(),10);
		var second_value = parseInt($("#conditiontimesecond_" + temp[1] + "_" + temp[2]).val(),10);
		var ampm_value 	 = $("#conditiontimeampm_" + temp[1] + "_" + temp[2]).val();

		if(isNaN(hour_value)){
			hour_value = '00';
		}

		if(isNaN(minute_value)){
			minute_value = '00';
		}
		
		if(isNaN(second_value)){
			second_value = '00';
		}

		$("#liwebhookrule_" + temp[1] + "_" + temp[2]).data('rule_condition').keyword = hour_value.toString() + ':' + minute_value.toString() + ':' + second_value.toString() + ':' + ampm_value;
    });

    //attach event to 'delete webhook rules' icon
	$('#ls_box_webhook_rules').delegate('a.delete_liwebhookrule', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var rule_id = temp[1];

		if($("#ls_webhook_rules_group > li").length <= 1){
			$("#ui-dialog-title-dialog-warning").html('Unable to delete!');
			$("#dialog-warning-msg").html("You can't delete all rules. <br />You must have at least one rule.");
			$("#dialog-warning").dialog('open');
		}else{
			$("#liwebhookrule_" + rule_id).fadeOut(400,function(){
				$(this).remove();
			});
		}
		
		return false;
	});

	//attach click event to 'add rule condition' (+) icon
	$('#ls_box_webhook_rules').delegate('a.a_add_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var rule_id = temp[1];

		var new_id = $("#liwebhookrule_" + rule_id + " ul.ls_webhook_rules_conditions > li:not('.ls_add_condition')").length + 1;
		var old_id = new_id - 1;

		//duplicate the last rule condition
		var last_rule_element = $("#liwebhookrule_" + rule_id + " ul.ls_webhook_rules_conditions > li:not('.ls_add_condition')").last();
		last_rule_element.clone(false).data('rule_condition',$.extend('{}',last_rule_element.data('rule_condition'))).find("*[id],*[name]").each(function() {
			var temp = $(this).attr("id").split("_"); 
			
			//rename the original id with the new id
			$(this).attr("id", temp[0] + "_" + temp[1] + "_" + new_id);
			$(this).attr("name", temp[0] + "_" + temp[1] + "_" + new_id);
			
		}).end().attr("id","liwebhookrule_" + rule_id + "_" + new_id).insertBefore("#liwebhookrule_" + rule_id + " li.ls_add_condition").hide().fadeIn();

		//copy the value of the dropdowns
		$("#conditionwebhook_" + rule_id + "_" + new_id).val($("#conditionwebhook_" + rule_id + "_" + old_id).val());
		$("#conditiontext_" + rule_id + "_" + new_id).val($("#conditiontext_" + rule_id + "_" + old_id).val());
		$("#conditionnumber_" + rule_id + "_" + new_id).val($("#conditionnumber_" + rule_id + "_" + old_id).val());
		$("#conditiondate_" + rule_id + "_" + new_id).val($("#conditiondate_" + rule_id + "_" + old_id).val());
		$("#conditioncheckbox_" + rule_id + "_" + new_id).val($("#conditioncheckbox_" + rule_id + "_" + old_id).val());
		$("#conditionapprovalstatus_" + rule_id + "_" + new_id).val($("#conditionapprovalstatus_" + rule_id + "_" + old_id).val());
		
		//reset the condition keyword  
		$("#conditionkeyword_" + rule_id + "_" + new_id).val('');
		$("#liwebhookrule_" + rule_id + "_" + new_id).data('rule_condition').keyword = '';

		//remove the datepicker and rebuild it, with the events as well
		$('#datepicker_' + rule_id + '_' + new_id).next().next().remove();
		$('#datepicker_' + rule_id + '_' + new_id).next().remove();
		$('#datepicker_' + rule_id + '_' + new_id).remove();

		var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_' + rule_id + '_' + new_id +'" id="datepicker_' + rule_id + '_' + new_id +'"> ' +
								 '<span style="display:none"> <img id="datepickimg_'+ rule_id + '_' + new_id +'" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

		$('#conditionkeyword_' + rule_id + '_' + new_id).after(new_datepicker_tag);

		$('#datepicker_' + rule_id + '_' + new_id).datepick({ 
	    		onSelect: select_date_webhook,
	    		showTrigger: '#datepickimg_' + rule_id + '_' + new_id
		});

		return false;
	});

	//delegate click event to the 'delete rule condition' (-) icon
    $('#ls_box_webhook_rules').delegate('a.a_delete_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var rule_id = temp[1];

		if($("#liwebhookrule_" + rule_id + " ul.ls_webhook_rules_conditions > li:not('.ls_add_condition')").length <= 1){
			$("#ui-dialog-title-dialog-warning").html('Unable to delete!');
			$("#dialog-warning-msg").html("You can't delete all condition. <br />You must have at least one condition.");
			$("#dialog-warning").dialog('open');
		}else{
			$(this).parent().fadeOut(function(){
				$(this).remove();
			});
		}

		return false;
    });

    //delegate event to the 'webhook URL' text
    $('#ls_box_webhook_rules').delegate('input.webhook_url', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liwebhookrule_" + temp[1]).data('rule_properties').webhook_url = $(this).val();
    });

    //delegate change event to the 'HTTP Method' dropdown
    $('#ls_box_webhook_rules').delegate('select.webhook_method_dropdown', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		
		$("#liwebhookrule_" + temp[1]).data('rule_properties').webhook_method = $(this).val();

    });

    //attach event to 'use http authentication' checkbox
	$('#ls_box_webhook_rules').delegate('input.webhook_enable_http_auth', 'change', function(e) {
		var temp = $(this).attr("id").split("_");

		if($(this).prop("checked") == true){
			$("#liwebhookrule_" + temp[1]).data('rule_properties').webhook_enable_http_auth = 1;
			$("#liwebhookrule_" + temp[1] + " div.webhook_http_auth_div").slideDown();
		}else{
			$("#liwebhookrule_" + temp[1]).data('rule_properties').webhook_enable_http_auth = 0;
			$("#liwebhookrule_" + temp[1] + " div.webhook_http_auth_div").slideUp();
		}
	});

	//attach event to 'delay notification until paid' checkbox for webhook
	$('#ls_box_webhook_rules').delegate('input.webhook_delay_notification', 'change', function(e) {
		var temp = $(this).attr("id").split("_");

		if($(this).prop("checked") == true){
			$("#liwebhookrule_" + temp[1]).data('rule_properties').delay_notification_until_paid = 1;
		}else{
			$("#liwebhookrule_" + temp[1]).data('rule_properties').delay_notification_until_paid = 0;
		}
	});

	//delegate event to the 'HTTP username' text
    $('#ls_box_webhook_rules').delegate('input.webhook_http_username', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liwebhookrule_" + temp[1]).data('rule_properties').webhook_http_username = $(this).val();
    });

    //delegate event to the 'HTTP password' text
    $('#ls_box_webhook_rules').delegate('input.webhook_http_password', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liwebhookrule_" + temp[1]).data('rule_properties').webhook_http_password = $(this).val();
    });

    //attach event to 'use custom http headers' checkbox
	$('#ls_box_webhook_rules').delegate('input.webhook_enable_custom_http_headers', 'change', function(e) {
		var temp = $(this).attr("id").split("_");

		if($(this).prop("checked") == true){
			$("#liwebhookrule_" + temp[1]).data('rule_properties').webhook_enable_custom_http_headers = 1;
			$("#liwebhookrule_" + temp[1] + " div.webhook_http_header_div").slideDown();
		}else{
			$("#liwebhookrule_" + temp[1]).data('rule_properties').webhook_enable_custom_http_headers = 0;
			$("#liwebhookrule_" + temp[1] + " div.webhook_http_header_div").slideUp();
		}
	});

	//delegate event to the 'HTTP headers' text
    $('#ls_box_webhook_rules').delegate('textarea.webhook_custom_http_headers', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liwebhookrule_" + temp[1]).data('rule_properties').webhook_custom_http_headers = $(this).val();
    });

    //delegate event to the 'Send Key-Value' radio button
    $('#ls_box_webhook_rules').delegate('input.webhook_data_format_key_value', 'change', function(e) {
    	var temp = $(this).attr("id").split("_");
    	
    	$("#liwebhookrule_" + temp[1] + " div.webhook_parameters_label_div").hide();
    	$("#liwebhookrule_" + temp[1] + " ul.ul_webhook_parameters").hide();
    	$("#liwebhookrule_" + temp[1] + " div.webhook_raw_div").hide();

    	if($(this).prop("checked") == true){
    		$("#liwebhookrule_" + temp[1]).data('rule_properties').webhook_format = 'key-value';
    		$("#liwebhookrule_" + temp[1] + " div.webhook_parameters_label_div").show();
    		$("#liwebhookrule_" + temp[1] + " ul.ul_webhook_parameters").show();
    	}
    });

    //delegate event to the 'Send Raw Data' radio button
    $('#ls_box_webhook_rules').delegate('input.webhook_data_format_raw', 'change', function(e) {
    	var temp = $(this).attr("id").split("_");
    	
    	$("#liwebhookrule_" + temp[1] + " div.webhook_parameters_label_div").hide();
    	$("#liwebhookrule_" + temp[1] + " ul.ul_webhook_parameters").hide();
    	$("#liwebhookrule_" + temp[1] + " div.webhook_raw_div").hide();

    	if($(this).prop("checked") == true){
    		$("#liwebhookrule_" + temp[1]).data('rule_properties').webhook_format = 'raw';
    		$("#liwebhookrule_" + temp[1] + " div.webhook_raw_div").show();
    	}
    });

    //delegate event to the 'HTTP headers' text
    $('#ls_box_webhook_rules').delegate('textarea.webhook_raw_data', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liwebhookrule_" + temp[1]).data('rule_properties').webhook_raw_data = $(this).val();
    });

    //delegate event to the 'add param' (+) icon on webhook key-value pairs
    $('#ls_box_webhook_rules').delegate('a.add_webhook_param', 'click', function(e) {
    	var temp = $(this).parent().prev().children().filter('.ns_param_name').children().attr("id").split("_");
    	var new_param_number = temp[1] + '_' + (parseInt(temp[2]) + 1);

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
										'<a class="delete_webhook_param" name="deletewebhookparam_' + new_param_number + '" id="deletewebhookparam_' + new_param_number + '" href="#"><img src="images/icons/51_green_16.png"></a>' +
									'</div>' + 
								'</li>';
		$("#liwebhookrule_webhook" + temp[1] + " .ns_url_add_param").before(new_param_markup);
		$("#liwebhookrule_webhook" + temp[1] + " .ul_webhook_parameters > li.ns_url_params:last").hide().slideDown();

		return false;
    });

	//delegate click event to the 'delete webhook param' (-) icon
    $('#ls_box_webhook_rules').delegate('a.delete_webhook_param', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var rule_id = temp[1];

		if($("#liwebhookrule_webhook" + temp[1] + " .ul_webhook_parameters > li.ns_url_params").length <= 1){
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
    
	//attach event to 'add webhook rule' link
	$("#ls_add_webhook_rule").click(function(){
		
		//duplicate the last rule group
		var last_rule_element = $("#ls_webhook_rules_group > li").last();
		var last_rule_id = last_rule_element.attr("id");

		last_rule_id_clean = parseInt(last_rule_id.replace("liwebhookrule_webhook",""));
		var new_rule_id    = last_rule_id_clean + 1;


		last_rule_element.clone(false).find("*[id],*[name],*[for]").each(function() {
			
			if($(this).attr("id") !== undefined){
				var current_id 	= $(this).attr("id"); 
				var new_id 		= current_id.replace("webhook" + last_rule_id_clean, "webhook" + new_rule_id);
				
				new_id = new_id.replace("webhookname_" + last_rule_id_clean + "_","webhookname_" + new_rule_id + "_");
				new_id = new_id.replace("webhookvalue_" + last_rule_id_clean + "_","webhookvalue_" + new_rule_id + "_");
				new_id = new_id.replace("deletewebhookparam_" + last_rule_id_clean + "_","deletewebhookparam_" + new_rule_id + "_");

				//rename the original id with the new id
				$(this).attr("id", new_id);
				
				if($(this).attr("type") == 'radio'){
					//exception for the Data Format radio button, don't replace the 'name' attribute with the 'id'
					var new_name = new_id.replace("keyvalue","");
					new_name = new_name.replace("raw","");
					$(this).attr("name", new_name);
				}else{
					$(this).attr("name", new_id);
				}
			}else{
				var current_id = $(this).attr("for"); 
				var new_id = current_id.replace("webhook" + last_rule_id_clean, "webhook" + new_rule_id);

				$(this).attr("for", new_id);
			}

		}).end().attr("id","liwebhookrule_webhook" + new_rule_id).appendTo('#ls_webhook_rules_group').hide().fadeIn();

		//rename the new rule main label
		$("#liwebhookrule_webhook" + new_rule_id + " .rule_title").text("Rule #" + new_rule_id);

		//copy the data for main rule properties
		$("#liwebhookrule_webhook" + new_rule_id).data('rule_properties',$.extend('{}', last_rule_element.data('rule_properties')));
		$("#liwebhookrule_webhook" + new_rule_id).data('rule_properties').rule_id = new_rule_id;

		
		//copy the data from the previous conditions to the new one
		$("#liwebhookrule_webhook" + new_rule_id + " .ls_webhook_rules_conditions > li:not('.ls_add_condition')").each(function(){
			var current_condition_id = $(this).attr("id");
			var old_condition_id = current_condition_id.replace("webhook" + new_rule_id,"webhook" + last_rule_id_clean);

			//rule_condition data is an object, we need to use extend so that the object won't get passed by reference, screwing all other elements data
			$(this).data('rule_condition',$.extend('{}',$("#" + old_condition_id).data('rule_condition')));
			$(this).data('rule_condition').target_rule_id = 'webhook' + new_rule_id;

			//remove the datapicker and rebuild it, with the events as well
			var current_datepicker = $(this).find(".hasDatepick");
			var current_datepicker_id = current_datepicker.attr("id");

			current_datepicker.next().next().remove();
			current_datepicker.next().remove();
			current_datepicker.remove();

			var temp = current_datepicker_id.split("_");
			var condition_id = temp[2];

			var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_webhook' + new_rule_id + '_' + condition_id +'" id="datepicker_webhook' + new_rule_id + '_' + condition_id +'"> ' +
									 '<span style="display:none"> <img id="datepickimg_webhook'+ new_rule_id + '_' + condition_id +'" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

			$('#conditionkeyword_webhook' + new_rule_id + '_' + condition_id).after(new_datepicker_tag);

			$('#datepicker_webhook' + new_rule_id + '_' + condition_id).datepick({ 
		    		onSelect: select_date_webhook,
		    		showTrigger: '#datepickimg_webhook' + new_rule_id + '_' + condition_id
			});
		});

		//copy all dropdown selection state
		var original_rule_dropdowns = $("#liwebhookrule_webhook" + last_rule_id_clean).find("select");
		var new_rule_dropdowns =  $("#liwebhookrule_webhook" + new_rule_id).find("select");
		new_rule_dropdowns.each(function(index,item){
			//set new select to value of old select
     		$(item).val(original_rule_dropdowns.eq(index).val());
		});

		return false;

	});
	
	/***************************************************************************************************************/	
	/* 9. Dialog Box for template variable																		   */
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

	$("#ls_main_list").delegate('a.tempvar_link', 'click', function(e) {
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
	/* 10. Success Rules Pane								   												   	   */
	/***************************************************************************************************************/

	//attach event to 'enable rules to display success page' checkbox
	$("#logic_success_enable").click(function(){
		if($(this).prop("checked") == true){
			$("#ls_box_success_rules .ls_box_content").slideDown();
			$(".logic_settings").data('logic_status').logic_success_enable = 1;
		}else{
			$("#ls_box_success_rules .ls_box_content").slideUp();
			$(".logic_settings").data('logic_status').logic_success_enable = 0;
		}
	});

	//delegate change event to the all/any dropdown
    $('#ls_box_success_rules').delegate('select.rule_all_any', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		$("#lisuccessrule_" + temp[1]).data('rule_properties').rule_all_any = $(this).val();
    });

    //delegate change event into condition field name dropdown
	$('#ls_box_success_rules').delegate('select.condition_fieldname', 'change', function(e) {
			
			var new_element_name = $(this).val();
			var new_element_type = $("#ls_fields_lookup").data(new_element_name);

			$(this).parent().find('.condition_text,.condition_time,.condition_number,.condition_date,.condition_checkbox,.condition_keyword,.condition_select,.condition_rating,.condition_ratingvalues').hide();
			$(this).parent().removeClass('condition_date');

			//reset keyword
			$(this).parent().data('rule_condition').keyword = '';
			$(this).parent().find('.condition_keyword').val('');

			//display the appropriate condition type dropdown, depends on the field type
			//and make sure to update the condition property value when the field type has been changed
			if(new_element_type == 'money' || new_element_type == 'number'){
				$(this).parent().find('.condition_number,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_number').val();
			}else if(new_element_type == 'date' || new_element_type == 'europe_date'){
				$(this).parent().addClass('condition_date');
				$(this).parent().find('.condition_date,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_date').val();
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

				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_date').val();
			}else if(new_element_type == 'checkbox'){
				$(this).parent().find('.condition_checkbox').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_checkbox').val();
			}else if(new_element_type == 'radio' || new_element_type == 'select'){
				//reset condition type
				$(this).parent().find('.condition_text').show().val('is');
				$(this).parent().data('rule_condition').condition = 'is';

				//reset condition keyword with dropdown values and display it
				$(this).parent().find('.condition_select').html($("#" + new_element_name + "_lookup").html()).show();
				$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_select').eq(0).val();
			}else if(new_element_type == 'rating'){
				//reset condition type
				$(this).parent().find('.condition_rating').show().val('is');
				$(this).parent().data('rule_condition').condition = 'is';

				//reset condition keyword with dropdown values and display it
				$(this).parent().find('.condition_ratingvalues').html($("#" + new_element_name + "_lookup").html()).show();
				$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_ratingvalues').eq(0).val();
			}else{
				$(this).parent().find('.condition_text,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_text').val();
			}

			$(this).parent().data('rule_condition').element_name = new_element_name;

    });

	//delegate change event to the condition type dropdown (for number, date, checkbox, rating)
    $('#ls_box_success_rules').delegate('select.condition_number,select.condition_date,select.condition_checkbox,select.condition_rating', 'change', function(e) {
		$(this).parent().data('rule_condition').condition = $(this).val();
    });

    //delegate change event to the condition type dropdown (for other fields beside the above)
    $('#ls_box_success_rules').delegate('select.condition_text', 'change', function(e) {
    	var element_name = $(this).parent().data('rule_condition').element_name;
    	var element_type = $("#ls_fields_lookup").data(element_name);

    	var condition_type = $(this).val();
    	
    	//if the field type is radio/dropdown, check for the selected condition type
    	//if condition type = 'is'/'is_not' , display the dropdown
    	if(element_type == 'radio' || element_type == 'select'){
    		$(this).parent().find('.condition_keyword,.condition_select').hide();

    		if(condition_type == 'is' || condition_type == 'is_not'){
    			$(this).parent().find('.condition_select').show();
    			$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_select').eq(0).val();
    		}else{
    			$(this).parent().find('.condition_keyword').show();
    			$(this).parent().data('rule_condition').keyword = $(this).parent().find('.condition_keyword').val();
    		}
    	}

		$(this).parent().data('rule_condition').condition = condition_type;
    });

	//delegate change event to the condition select dropdown (only applicable for radio and select)
    $('#ls_box_success_rules').delegate('select.condition_select', 'change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();
    });

    //delegate change event to the condition select dropdown (only applicable for rating)
    $('#ls_box_success_rules').delegate('select.condition_ratingvalues', 'change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();
    });

    //delegate event to the condition keyword text
    $('#ls_box_success_rules').delegate('input.condition_keyword', 'keyup mouseout change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();		
    });

    //delegate event to the time condition inputs
    $('#ls_box_success_rules').delegate('input.conditiontime_input,select.conditiontime_input', 'keyup mouseout change', function(e) {
		
		var temp = $(this).attr("id").split("_");

		var hour_value 	 = parseInt($("#conditiontimehour_" + temp[1] + "_" + temp[2]).val(),10);
		var minute_value = parseInt($("#conditiontimeminute_" + temp[1] + "_" + temp[2]).val(),10);
		var second_value = parseInt($("#conditiontimesecond_" + temp[1] + "_" + temp[2]).val(),10);
		var ampm_value 	 = $("#conditiontimeampm_" + temp[1] + "_" + temp[2]).val();

		if(isNaN(hour_value)){
			hour_value = '00';
		}

		if(isNaN(minute_value)){
			minute_value = '00';
		}
		
		if(isNaN(second_value)){
			second_value = '00';
		}

		$("#lisuccessrule_" + temp[1] + "_" + temp[2]).data('rule_condition').keyword = hour_value.toString() + ':' + minute_value.toString() + ':' + second_value.toString() + ':' + ampm_value;
    });

    //attach event to 'delete success page rules' icon
	$('#ls_box_success_rules').delegate('a.delete_lisuccessrule', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var rule_id = temp[1];

		if($("#ls_success_rules_group > li").length <= 1){
			$("#ui-dialog-title-dialog-warning").html('Unable to delete!');
			$("#dialog-warning-msg").html("You can't delete all rules. <br />You must have at least one rule.");
			$("#dialog-warning").dialog('open');
		}else{
			$("#lisuccessrule_" + rule_id).fadeOut(400,function(){
				$(this).remove();
			});
		}
		
		return false;
	});

	//attach click event to 'add rule condition' (+) icon
	$('#ls_box_success_rules').delegate('a.a_add_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var rule_id = temp[1];

		var new_id = $("#lisuccessrule_" + rule_id + " ul.ls_success_rules_conditions > li:not('.ls_add_condition')").length + 1;
		var old_id = new_id - 1;

		//duplicate the last rule condition
		var last_rule_element = $("#lisuccessrule_" + rule_id + " ul.ls_success_rules_conditions > li:not('.ls_add_condition')").last();
		last_rule_element.clone(false).data('rule_condition',$.extend('{}',last_rule_element.data('rule_condition'))).find("*[id],*[name]").each(function() {
			var temp = $(this).attr("id").split("_"); 
			
			//rename the original id with the new id
			$(this).attr("id", temp[0] + "_" + temp[1] + "_" + new_id);
			$(this).attr("name", temp[0] + "_" + temp[1] + "_" + new_id);
			
		}).end().attr("id","lisuccessrule_" + rule_id + "_" + new_id).insertBefore("#lisuccessrule_" + rule_id + " li.ls_add_condition").hide().fadeIn();

		//copy the value of the dropdowns
		$("#conditionsuccess_" + rule_id + "_" + new_id).val($("#conditionsuccess_" + rule_id + "_" + old_id).val());
		$("#conditiontext_" + rule_id + "_" + new_id).val($("#conditiontext_" + rule_id + "_" + old_id).val());
		$("#conditionnumber_" + rule_id + "_" + new_id).val($("#conditionnumber_" + rule_id + "_" + old_id).val());
		$("#conditiondate_" + rule_id + "_" + new_id).val($("#conditiondate_" + rule_id + "_" + old_id).val());
		$("#conditioncheckbox_" + rule_id + "_" + new_id).val($("#conditioncheckbox_" + rule_id + "_" + old_id).val());
		
		//reset the condition keyword  
		$("#conditionkeyword_" + rule_id + "_" + new_id).val('');
		$("#lisuccessrule_" + rule_id + "_" + new_id).data('rule_condition').keyword = '';

		//remove the datepicker and rebuild it, with the events as well
		$('#datepicker_' + rule_id + '_' + new_id).next().next().remove();
		$('#datepicker_' + rule_id + '_' + new_id).next().remove();
		$('#datepicker_' + rule_id + '_' + new_id).remove();

		var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_' + rule_id + '_' + new_id +'" id="datepicker_' + rule_id + '_' + new_id +'"> ' +
								 '<span style="display:none"> <img id="datepickimg_'+ rule_id + '_' + new_id +'" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

		$('#conditionkeyword_' + rule_id + '_' + new_id).after(new_datepicker_tag);

		$('#datepicker_' + rule_id + '_' + new_id).datepick({ 
	    		onSelect: select_date_success,
	    		showTrigger: '#datepickimg_' + rule_id + '_' + new_id
		});

		return false;
	});

	//delegate click event to the 'delete rule condition' (-) icon
    $('#ls_box_success_rules').delegate('a.a_delete_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var rule_id = temp[1];

		if($("#lisuccessrule_" + rule_id + " ul.ls_success_rules_conditions > li:not('.ls_add_condition')").length <= 1){
			$("#ui-dialog-title-dialog-warning").html('Unable to delete!');
			$("#dialog-warning-msg").html("You can't delete all condition. <br />You must have at least one condition.");
			$("#dialog-warning").dialog('open');
		}else{
			$(this).parent().fadeOut(function(){
				$(this).remove();
			});
		}

		return false;
    });

    //delegate event to the 'redirect URL' text
    $('#ls_box_success_rules').delegate('input.success_redirect', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#lisuccessrule_" + temp[1]).data('rule_properties').redirect_url = $(this).val();
    });

    //delegate event to the 'success message' textarea
    $('#ls_box_success_rules').delegate('textarea.success_message', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#lisuccessrule_" + temp[1]).data('rule_properties').success_message = $(this).val();
    });

    //delegate event to the 'Show Message' radio button
    $('#ls_box_success_rules').delegate('input.success_type_message', 'change', function(e) {
    	var temp = $(this).attr("id").split("_");
    	
    	$("#lisuccessrule_" + temp[1] + " .ls_success_option").hide();
    	
    	if($(this).prop("checked") == true){
    		$("#lisuccessrule_" + temp[1]).data('rule_properties').success_type = 'message';
    		$("#successoptionmessage_" + temp[1]).show();
    	}
    	console.log("show message");
    });

    //delegate event to the 'Redirect to Page' radio button
    $('#ls_box_success_rules').delegate('input.success_type_redirect', 'change', function(e) {
    	var temp = $(this).attr("id").split("_");
    	
    	$("#lisuccessrule_" + temp[1] + " .ls_success_option").hide();
    	
    	if($(this).prop("checked") == true){
    		$("#lisuccessrule_" + temp[1]).data('rule_properties').success_type = 'redirect';
    		$("#successoptionredirect_" + temp[1]).show();
    	}
    	console.log("redirecte");
    });

    //attach event to 'add success page rule' link
	$("#ls_add_success_rule").click(function(){
		
		//duplicate the last rule group
		var last_rule_element = $("#ls_success_rules_group > li").last();
		var last_rule_id = last_rule_element.attr("id");

		last_rule_id_clean = parseInt(last_rule_id.replace("lisuccessrule_success",""));
		var new_rule_id    = last_rule_id_clean + 1;


		last_rule_element.clone(false).find("*[id],*[name],*[for]").each(function() {
			
			if($(this).attr("id") !== undefined){
				var current_id 	= $(this).attr("id");
				var new_id 		= current_id.replace("success" + last_rule_id_clean, "success" + new_rule_id);
				
				//rename the original id with the new id
				$(this).attr("id", new_id);

				if($(this).attr("type") == 'radio'){
					//exception for the 'success type' radio button (show message/redirect), don't replace the 'name' attribute with the 'id'
					var new_name = new_id.replace("message","");
					new_name = new_name.replace("redirect","");
					$(this).attr("name", new_name);
				}else{
					$(this).attr("name", new_id);
				}
			}else{
				var current_id = $(this).attr("for"); 
				var new_id = current_id.replace("success" + last_rule_id_clean, "success" + new_rule_id);

				$(this).attr("for", new_id);
			}

		}).end().attr("id","lisuccessrule_success" + new_rule_id).appendTo('#ls_success_rules_group').hide().fadeIn();

		//rename the new rule main label
		$("#lisuccessrule_success" + new_rule_id + " .rule_title").text("Rule #" + new_rule_id);

		//copy the data for main rule properties
		$("#lisuccessrule_success" + new_rule_id).data('rule_properties',$.extend('{}', last_rule_element.data('rule_properties')));
		$("#lisuccessrule_success" + new_rule_id).data('rule_properties').rule_id = new_rule_id;

		
		//copy the data from the previous conditions to the new one
		$("#lisuccessrule_success" + new_rule_id + " .ls_success_rules_conditions > li:not('.ls_add_condition')").each(function(){
			var current_condition_id = $(this).attr("id");
			var old_condition_id = current_condition_id.replace("success" + new_rule_id,"success" + last_rule_id_clean);

			//rule_condition data is an object, we need to use extend so that the object won't get passed by reference, screwing all other elements data
			$(this).data('rule_condition',$.extend('{}',$("#" + old_condition_id).data('rule_condition')));
			$(this).data('rule_condition').target_rule_id = 'success' + new_rule_id;

			//remove the datapicker and rebuild it, with the events as well
			var current_datepicker = $(this).find(".hasDatepick");
			var current_datepicker_id = current_datepicker.attr("id");

			current_datepicker.next().next().remove();
			current_datepicker.next().remove();
			current_datepicker.remove();

			var temp = current_datepicker_id.split("_");
			var condition_id = temp[2];

			var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_success' + new_rule_id + '_' + condition_id +'" id="datepicker_success' + new_rule_id + '_' + condition_id +'"> ' +
									 '<span style="display:none"> <img id="datepickimg_success'+ new_rule_id + '_' + condition_id +'" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

			$('#conditionkeyword_success' + new_rule_id + '_' + condition_id).after(new_datepicker_tag);

			$('#datepicker_success' + new_rule_id + '_' + condition_id).datepick({ 
		    		onSelect: select_date_success,
		    		showTrigger: '#datepickimg_success' + new_rule_id + '_' + condition_id
			});
		});

		//copy all dropdown selection state
		var original_rule_dropdowns = $("#lisuccessrule_success" + last_rule_id_clean).find("select");
		var new_rule_dropdowns =  $("#lisuccessrule_success" + new_rule_id).find("select");
		new_rule_dropdowns.each(function(index,item){
			//set new select to value of old select
     		$(item).val(original_rule_dropdowns.eq(index).val());
		});

		return false;

	});

});