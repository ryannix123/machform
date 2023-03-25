/** Functions **/
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
	$("#liapproverrule_" + li_id).data('rule_condition').keyword = selected_date;
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
	/* 2. Approval Settings														   				   				   */
	/***************************************************************************************************************/
	
	
	//attach event to 'approval workflow type' dropdown
	$('#as_select_workflow').bind('change', function() {
		var workflow_type = $(this).val();
		
		$("#single-step-approval-info,#multi-step-approval-info,#single-step-option-label,#single-step-option-div").hide();
		
		if(workflow_type == 'serial'){
			$("#multi-step-approval-info").show();
			$("#as_main_list").data('approval_properties').workflow_type = 'serial';
		}else if(workflow_type == 'parallel'){
			$("#single-step-approval-info,#single-step-option-label,#single-step-option-div").show();
			$("#as_main_list").data('approval_properties').workflow_type = 'parallel';
		}
			
	});
	
	//attach event to 'Single-Step Approval Rule' dropdown
	$("input[name=parallel_workflow]").bind('change',function(){
		$("#as_main_list").data('approval_properties').parallel_workflow = $(this).val();
	});

	/***************************************************************************************************************/	
	/* 3. Approvers													   				   				   */
	/***************************************************************************************************************/
	
	//attach event to 'add user to approvers' dropdown
	$('#add_user_to_approver').bind('change', function() {
		
		if($(this).val() == ''){
			return true;
		}
		
		var user_id = parseInt($(this).val());
		var user_fullname 	= $(this).find('option:selected').text();
		var user_email	  	= $("#add_user_to_approver_lookup option[value=" + user_id + "]").data("email");
		var user_lastlogin	= $("#add_user_to_approver_lookup option[value=" + user_id + "]").data("lastlogin");

		//build the markup
		var li_markup = '';
		var condition_fieldname_markup = '';
		var new_approver_no = 0;

		//get new approver no
		$("#approvers_list .approver_no").each(function(index){
			var i = parseInt($(this).text());
			if(i > new_approver_no){
				new_approver_no = i;
			}
		});
		new_approver_no++;

		li_markup	+=	'<li id="liapproverrule_' + user_id + '">' +
							'<div class="approver_no">' + new_approver_no + '</div>' + 
							'<div class="approver_info">' +
								'<h3>' + user_fullname + '</h3>' +
								'<h6>' + user_email + '</h6>' +
								'<em>Last logged in: ' + user_lastlogin + '</em>' +
							'</div>' +
							'<div class="approver_action">' +
								'<div class="approver_action_delete"><a title="Remove Approver" class="delete_liapproverrule" id="deleteliapproverrule_' + user_id + '" href="#"><span class="icon-cancel-circle"></span> </a></div>' +
								'<div class="approver_action_settings"><a title="Approver Rules" class="approver_rules_toggle" id="approverrulestoggle_' + user_id + '" href="#"><span class="icon-settings"></span> </a></div>' +
							'</div>' +
							'<div class="approver_rules" style="display: none">' +
								'<ul class="as_approver_rules_conditions">' + 									
									'<li class="as_approver_logic_info">' + 
										'This user will receive the approval on any conditions.<br/>' + 
										'You can <a id="addapproverlogic_' + user_id + '" class="as_add_approver_logic" href="#">Add Approver Logic</a> to include this user only when specific conditions are met. ' + 
									'</li>' + 
								'</ul>' + 
							'</div>' + 
						'</li>';

		//append the rule markup
		$("#approvers_list").prepend(li_markup);

		$("#liapproverrule_" + user_id).hide();
		$("#liapproverrule_" + user_id).slideDown();

		//attach dom data
		$("#liapproverrule_" + user_id).data('rule_properties',{"user_id": user_id,"user_position": new_approver_no ,"rule_show_hide":"show","rule_all_any":"all"});

		//remove the option from the dropdown
		$(this).find('option:selected').remove();
		
		if($("#add_user_to_approver option").length == 1){
			$("#add_user_to_approver option").text('No More Users Available');
		}

	});
	
	//attach event to 'Add Approver Logic' link
	$('#approvers_list').delegate('a.as_add_approver_logic', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var user_id = temp[1];
		
		//hide the info box
		$(this).parent().hide();

		//add the 'Send the approval...'
		var rule_title_markup = '';
		rule_title_markup = '<h6>' +
									'<span class="icon-arrow-right2"></span>' + ' ' +
									'Send the approval to this user if ' + 
									'<select style="margin-left: 5px;margin-right: 5px" name="approverruleallany_' + user_id + '" id="fieldruleallany_' + user_id + '" class="element select rule_all_any">' + 
										'<option value="all" selected="selected">all</option>' + 
										'<option value="any" >any</option>' + 
									'</select> of the following conditions match: ' + 
							'</h6>';

		$('#liapproverrule_' + user_id + ' .approver_rules').prepend(rule_title_markup); 
		
		//add the condition fields
		var condition_fieldname_markup = '';
		var li_markup = '';

		condition_fieldname_markup = $("#as_fields_lookup").clone(false).attr("id","conditionfield_" + user_id + "_1").attr("name","conditionfield_" + user_id + "_1").show().outerHTML();

		li_markup	+=	'<li id="liapproverrule_' + user_id + '_1" >' + 
							 condition_fieldname_markup + ' ' + 
							'<select name="conditiontext_' + user_id + '_1" id="conditiontext_' + user_id + '_1" class="element select condition_text" style="width: 120px;display:none">' + 
														'<option  value="is">Is</option>' + 
														'<option  value="is_not">Is Not</option>' + 
														'<option  value="begins_with">Begins with</option>' + 
														'<option  value="ends_with">Ends with</option>' + 
														'<option  value="contains">Contains</option>' + 
														'<option  value="not_contain">Does not contain</option>' + 
							'</select>' + ' ' +
							'<select name="conditionnumber_' + user_id + '_1" id="conditionnumber_' + user_id + '_1" class="element select condition_number" style="width: 120px;display:none">' + 
														'<option  value="is" selected="selected">Is</option>' + 
														'<option  value="less_than">Less than</option>' + 
														'<option  value="greater_than">Greater than</option>' + 
							'</select>' + ' ' +
							'<select id="conditionrating_'+ user_id +'_1" name="conditionrating_' + user_id + '_1" style="width: 120px;display: none" class="element select condition_rating">' + 
												'<option value="is" selected="selected">Is</option>' + 
												'<option value="is_not">Is Not</option>' + 
												'<option value="less_than">Less than</option>' + 
												'<option value="greater_than">Greater than</option>' + 
								'</select>' + ' ' +
							'<select name="conditiondate_' + user_id + '_1" id="conditiondate_' + user_id + '_1" class="element select condition_date" style="width: 120px;display:none">' + 
														'<option  value="is" selected="selected">Is</option>' + 
														'<option  value="is_before">Is Before</option>' + 
														'<option  value="is_after">Is After</option>' + 
							'</select>' + ' ' +
							'<select name="conditioncheckbox_' + user_id + '_1" id="conditioncheckbox_' + user_id + '_1" class="element select condition_checkbox" style="width: 120px;display: none">' + 
														'<option  value="is_one">Is Checked</option>' + 
														'<option  value="is_zero">Is Empty</option>' + 
							'</select>' + ' ' +
							'<select id="conditionselect_' + user_id + '_1" name="conditionselect_' + user_id + '_1" autocomplete="off" class="element select condition_select" style="display:none">' + 
								'<option> value=""></option>' + ' ' +
							'</select> ' + ' ' + 
							'<select id="conditionratingvalues_'+ user_id +'_1" name="conditionratingvalues_' + user_id + '_1" style="display: none" class="element select condition_ratingvalues">' + 
								'<option value=""></option>' +  
							'</select>' + ' ' + "\n" +
							'<span name="conditiontime_' + user_id + '_1" id="conditiontime_' + user_id + '_1" class="condition_time" style="display:none">' + 
														'<input name="conditiontimehour_' + user_id + '_1" id="conditiontimehour_' + user_id + '_1" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="" placeholder="HH"> : ' + 
														'<input name="conditiontimeminute_' + user_id + '_1" id="conditiontimeminute_' + user_id + '_1" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="" placeholder="MM">  ' + 
														'<span class="conditiontime_second" style=""> : <input name="conditiontimesecond_' + user_id + '_1" id="conditiontimesecond_' + user_id + '_1" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="" placeholder="SS"> </span>' + 
														'<select class="element select conditiontime_ampm conditiontime_input" name="conditiontimeampm_' + user_id + '_1" id="conditiontimeampm_' + user_id + '_1" style="">' + 
															'<option selected="selected" value="AM">AM</option>' + 
															'<option  value="PM">PM</option>' + 
														'</select>' + 
							'</span>' + 
							'<input type="text" class="element text condition_keyword" value="" id="conditionkeyword_' + user_id + '_1" name="conditionkeyword_' + user_id + '_1" style="display:none">' + 
							'<a href="#" id="deletecondition_' + user_id + '_1" name="deletecondition_' + user_id + '_1" class="a_delete_condition"><span class="icon-minus-circle2"></span></a>' + 
						'</li>	' + 
						'<li class="as_add_condition">' + 
							'<a href="#" id="addcondition_' + user_id + '" class="a_add_condition"><span class="icon-plus-circle"></span></a>' + 
						'</li>'; 

		//append the rule markup
		$('#liapproverrule_' + user_id + ' .as_approver_rules_conditions').prepend(li_markup);

		
		//diplay the condition operator, depends on the first field on the field list
		var first_field_element_name = $("#conditionfield_" + user_id + "_1").eq(0).val();
		var first_field_element_type = $("#as_fields_lookup").data(first_field_element_name);
		var default_condition = 'is';
		var default_keyword = '';

		//populate options for condition_select
		$("#conditionselect_" + user_id + "_1").html($("#" + first_field_element_name + "_lookup").html());

		//populate options for condition_ratingvalues
		$("#conditionratingvalues_" + user_id + "_1").html($("#" + first_field_element_name + "_lookup").html());

		if(first_field_element_type == 'money' || first_field_element_type == 'number'){
			$("#conditionnumber_" + user_id + "_1").show();
			$("#conditionkeyword_" + user_id + "_1").show();
		}else if(first_field_element_type == 'date' || first_field_element_type == 'europe_date'){
			$("#conditiondate_" + user_id + "_1").show();
			$("#conditionkeyword_" + user_id + "_1").show();

			$("#lifieldrule_" + user_id + "_1").addClass("condition_date");
		}else if(first_field_element_type == 'time' || first_field_element_type == 'time_showsecond' || first_field_element_type == 'time_24hour' || first_field_element_type == 'time_showsecond24hour'){
			$("#conditiondate_" + user_id + "_1").show();
			$("#conditiontime_" + user_id + "_1").show();
			
			if(first_field_element_type == 'time'){
				$("#conditiontimeampm_" + user_id + "_1").show();
			}else if(first_field_element_type == 'time_showsecond'){
				$("#conditiontimeampm_" + user_id + "_1").show();
				$("#conditiontimesecond_" + user_id + "_1").parent().show();
			}else if(first_field_element_type == 'time_showsecond24hour'){
				$("#conditiontimesecond_" + user_id + "_1").parent().show();
			}

		}else if(first_field_element_type == 'checkbox'){
			$("#conditioncheckbox_" + user_id + "_1").show();
			default_condition = 'is_one'
		}else if(first_field_element_type == 'select' || first_field_element_type == 'radio'){
			$("#conditiontext_" + user_id + "_1").show();
			$("#conditionselect_" + user_id + "_1").show();

			default_keyword =  $("#conditionselect_" + user_id + "_1").eq(0).val();
		}else if(first_field_element_type == 'rating'){
			$("#conditionrating_" + user_id + "_1").show();
			$("#conditionratingvalues_" + user_id + "_1").show();

			default_keyword =  $("#conditionratingvalues_" + user_id + "_1").eq(0).val();
		}else{
			$("#conditiontext_" + user_id + "_1").show();
			$("#conditionkeyword_" + user_id + "_1").show();
		}

		//build the datepicker
		var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_'+ user_id +'_1" id="datepicker_'+ user_id +'_1">' + "\n" +
							 	 ' <span style="display:none"><img id="datepickimg_'+ user_id +'_1" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

		$('#conditionkeyword_' + user_id + '_1').after(new_datepicker_tag);

		$('#datepicker_' + user_id + '_1').datepick({ 
		   		onSelect: select_date,
		   		showTrigger: '#datepickimg_' + user_id + '_1'
		});

		//attach dom data
		$("#liapproverrule_" + user_id + "_1").data('rule_condition',{"target_user_id": user_id,"element_name": first_field_element_name, "condition": default_condition,"keyword": default_keyword});

		return false;
	});

	//attach event to 'delete approver ' icon
	$('#approvers_list').delegate('a.delete_liapproverrule', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var user_id = temp[1];

		//restore 'add user to approvers' dropdown values
		$("#add_user_to_approver").html($("#add_user_to_approver_lookup").html());
		
		$("#liapproverrule_" + user_id).fadeOut(400,function(){
			$(this).remove();

			$("#approvers_list > li").each(function(){
				var temp_name = $(this).attr('id').split('_');
				var cur_user_id = temp_name[1];
				
				$("#add_user_to_approver option[value="+ cur_user_id +"]").remove();			
			});
		});
		
		return false;
	});

	//attach event to 'approver rules' icon
	$('#approvers_list').delegate('a.approver_rules_toggle', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var user_id = temp[1];
		
		$("#liapproverrule_" + user_id + " > .approver_rules").toggle();
		
		return false;
	});

	//delegate change event to the all/any dropdown
    $('#as_box_approvers').delegate('select.rule_all_any', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		$("#liapproverrule_" + temp[1]).data('rule_properties').rule_all_any = $(this).val();
    });

    //delegate change event into condition field name dropdown
	$('#as_box_approvers').delegate('select.condition_fieldname', 'change', function(e) {
			
			var new_element_name = $(this).val();
			var new_element_type = $("#as_fields_lookup").data(new_element_name);

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
    $('#as_box_approvers').delegate('select.condition_number,select.condition_date,select.condition_checkbox,select.condition_rating', 'change', function(e) {
		$(this).parent().data('rule_condition').condition = $(this).val();
    });

    //delegate change event to the condition type dropdown (for other fields beside the above)
    $('#as_box_approvers').delegate('select.condition_text', 'change', function(e) {
    	var element_name = $(this).parent().data('rule_condition').element_name;
    	var element_type = $("#as_fields_lookup").data(element_name);

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
    $('#as_box_approvers').delegate('select.condition_select', 'change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();
    });

    //delegate change event to the condition select dropdown (only applicable for rating)
    $('#as_box_approvers').delegate('select.condition_ratingvalues', 'change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();
    });
	
	//delegate event to the condition keyword text
    $('#as_box_approvers').delegate('input.condition_keyword', 'keyup mouseout change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();	
    });

    //delegate event to the time condition inputs
    $('#as_box_approvers').delegate('input.conditiontime_input,select.conditiontime_input', 'keyup mouseout change', function(e) {
		
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

		$("#liapproverrule_" + temp[1] + "_" + temp[2]).data('rule_condition').keyword = hour_value.toString() + ':' + minute_value.toString() + ':' + second_value.toString() + ':' + ampm_value;
    });
	
	
	//attach click event to 'add rule condition' (+) icon
	$('#as_box_approvers').delegate('a.a_add_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var user_id = temp[1];

		var new_id = $("#liapproverrule_" + user_id + " ul > li:not('.as_add_condition')").length + 1;
		var old_id = new_id - 1;

		//duplicate the last rule condition
		var last_rule_element = $("#liapproverrule_" + user_id + " ul > li:not('.as_add_condition'):not('.as_approver_logic_info')").last();
		last_rule_element.clone(false).data('rule_condition',$.extend('{}',last_rule_element.data('rule_condition'))).find("*[id],*[name]").each(function() {
			var temp = $(this).attr("id").split("_"); 
			
			//rename the original id with the new id
			$(this).attr("id", temp[0] + "_" + temp[1] + "_" + new_id);
			$(this).attr("name", temp[0] + "_" + temp[1] + "_" + new_id);
			
		}).end().attr("id","liapproverrule_" + user_id + "_" + new_id).insertBefore("#liapproverrule_" + user_id + " li.as_add_condition").hide().fadeIn();

		//copy the value of the dropdowns
		$("#conditionfield_" + user_id + "_" + new_id).val($("#conditionfield_" + user_id + "_" + old_id).val());
		$("#conditiontext_" + user_id + "_" + new_id).val($("#conditiontext_" + user_id + "_" + old_id).val());
		$("#conditionnumber_" + user_id + "_" + new_id).val($("#conditionnumber_" + user_id + "_" + old_id).val());
		$("#conditiondate_" + user_id + "_" + new_id).val($("#conditiondate_" + user_id + "_" + old_id).val());
		$("#conditioncheckbox_" + user_id + "_" + new_id).val($("#conditioncheckbox_" + user_id + "_" + old_id).val());
		
		//reset the condition keyword  
		$("#conditionkeyword_" + user_id + "_" + new_id).val('');
		$("#liapproverrule_" + user_id + "_" + new_id).data('rule_condition').keyword = '';

		//remove the datepicker and rebuild it, with the events as well
		$('#datepicker_' + user_id + '_' + new_id).next().next().remove();
		$('#datepicker_' + user_id + '_' + new_id).next().remove();
		$('#datepicker_' + user_id + '_' + new_id).remove();

		var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_' + user_id + '_' + new_id +'" id="datepicker_' + user_id + '_' + new_id +'"> ' +
								 '<span style="display:none"> <img id="datepickimg_'+ user_id + '_' + new_id +'" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

		$('#conditionkeyword_' + user_id + '_' + new_id).after(new_datepicker_tag);

		$('#datepicker_' + user_id + '_' + new_id).datepick({ 
	    		onSelect: select_date,
	    		showTrigger: '#datepickimg_' + user_id + '_' + new_id
		});

		return false;
	});

	//delegate click event to the 'delete rule condition' (-) icon
    $('#as_box_approvers').delegate('a.a_delete_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var user_id = temp[1];

		
		$(this).parent().fadeOut(function(){
			$(this).remove();
			if($("#liapproverrule_" + user_id + " ul > li:not('.as_add_condition'):not('.as_approver_logic_info')").length < 1){
				$("#liapproverrule_" + user_id + " ul > li.as_add_condition").hide();
				$("#liapproverrule_" + user_id + " .approver_rules > h6").hide();
				$("#liapproverrule_" + user_id + " ul > li.as_approver_logic_info").show();

				//if no conditions exist any longer, reset the rule_all_any to 'all'
				$("#liapproverrule_" + user_id).data('rule_properties').rule_all_any = 'all';
			}
		});
		

		return false;
    });

	/***************************************************************************************************************/	
	/* 4. Initialize rule date pickers																			   */
	/***************************************************************************************************************/
	$("#approvers_list .rule_datepicker").each(function(index){
		var temp = $(this).attr('id').split('_');
		var user_id = temp[1] + '_' + temp[2];

		$('#datepicker_' + user_id).datepick({ 
	    		onSelect: select_date,
	    		showTrigger: '#datepickimg_' + user_id
		});
	});

	/***************************************************************************************************************/	
	/* 5. Attach event to 'Save Settings' button																   */
	/***************************************************************************************************************/
	$("#button_save_approval_settings").click(function(){
		
		if($("#button_save_approval_settings").text() != 'Saving...'){
				
				//display loader while saving
				$("#button_save_approval_settings").prop("disabled",true);
				$("#button_save_approval_settings").text('Saving...');
				$("#button_save_approval_settings").after("<div class='small_loader_box' style='float: right'><img src='images/loader_small_grey.gif' /></div>");
				
				//get approval properties data
				var approver_rule_properties_elements = $("#approvers_list > li");
				var approver_rule_properties_data 	   = new Array();

				if(approver_rule_properties_elements.length >= 1){
					approver_rule_properties_elements.each(function(index){
						approver_rule_properties_data[index] = $(this).data('rule_properties');
					});
				}

				var approver_rule_condition_elements = $("#approvers_list ul.as_approver_rules_conditions > li:not('.as_add_condition'):not('.as_approver_logic_info')");
				var approver_rule_condition_data 	= new Array();
				var csrf_token  = $(".approval_settings").data("csrftoken");

				if(approver_rule_condition_elements.length >= 1){
					approver_rule_condition_elements.each(function(index){
						approver_rule_condition_data[index] = $(this).data('rule_condition');
					});
				}

				
				//do the ajax call to save the settings
				$.ajax({
					   type: "POST",
					   async: true,
					   url: "save_approval_settings.php",
					   data: {
							  	form_id: $(".approval_settings").data('formid'),
							  	workflow_type: $("#as_select_workflow").val(),
							  	parallel_workflow: $("input[name=parallel_workflow]:checked").val(),
							  	approver_rule_properties: approver_rule_properties_data,
							  	approver_rule_conditions: approver_rule_condition_data,
							  	csrf_token: csrf_token
							  },
					   cache: false,
					   global: false,
					   dataType: "json",
					   error: function(xhr,text_status,e){
							   //error, display the generic error message		  
							   alert('Error! Unable to save approval workflow. Please try again.');
					   },
					   success: function(response_data){
							   
						   if(response_data.status == 'ok'){
							   window.location.replace('approval_settings.php?id=' + response_data.form_id);
						   }else{
							   alert('Error! Unable to save logic settings. Please try again.');
						   }
							   
					   }
				});
		}
		
		
		return false;
	});
	
});