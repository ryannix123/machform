$(function(){
    
	//attach event to Form Code Type dropdown
	$('#ec_code_type').bind('change', function() {
		var code_type  = $(this).val();
		var code_label = '';

		switch(code_type){
    		case 'iframe' 		: code_label = 'Iframe Code';break;
    		case 'simple_link' 	: code_label = 'Simple Link';break;
    		case 'popup_link' 	: code_label = 'Popup Link';break;
    	}

    	//change the code label
    	$("#ec_main_code_meta > h5").text(code_label);
	
		//show the correct embed code
		$("#ec_main_code_content > div:not('.view_widget_div')").hide();
		$("#ec_information > span:not(.helpicon)").hide();

		if(code_type == 'iframe'){
			$("#ec_code_iframe").show();
			$("#ec_info_iframe").show();
		}else if(code_type == 'simple_link'){
			$("#ec_code_simple_link").show();
			$("#ec_info_simple_link").show();
		}else if(code_type == 'popup_link'){
			$("#ec_code_popup_link").show();
			$("#ec_info_popup_link").show();
		}
	
	});

	
});