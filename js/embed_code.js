$(function(){
    
	//attach event to Form Code Type dropdown
	$('#ec_code_type').bind('change', function() {
		var code_type  = $(this).val();
		var code_label = '';

		switch(code_type){
    		case 'javascript' 		 : code_label = 'Javascript Code';break;
    		case 'javascript_jquery' : code_label = 'Javascript jQuery Code';break;
    		case 'iframe' 		: code_label = 'Iframe Code';break;
    		case 'php_file' 	: code_label = 'PHP Form File';break;
    		case 'php_code' 	: code_label = 'PHP Embed Code';break;
    		case 'clickable_link' : code_label = 'Clickable Link';break;
    		case 'popup_link' 	: code_label = 'Popup Link';break;
    		case 'plain_link' 	: code_label = 'Plain Link';break;
    	}

    	//change the code label
    	$("#ec_main_code_meta > h5").text(code_label);
	
		//show the correct embed code
		$("#ec_main_code_content > div").hide();
		$("#ec_information > span:not(.helpicon)").hide();

		if(code_type == 'javascript'){
			$("#ec_code_javascript").show();
			$("#ec_info_javascript").show();
		}else if(code_type == 'javascript_jquery'){
			$("#ec_code_javascript_jquery").show();
			$("#ec_info_javascript_jquery").show();
		}else if(code_type == 'iframe'){
			$("#ec_code_iframe").show();
			$("#ec_info_iframe").show();
		}else if(code_type == 'php_file'){
			$("#ec_code_php_file").show();
			$("#ec_info_php_file").show();
		}else if(code_type == 'php_code'){
			$("#ec_code_php_code").show();
			$("#ec_info_php_code").show();
		}else if(code_type == 'clickable_link'){
			$("#ec_code_clickable_link").show();
			$("#ec_info_clickable_link").show();
		}else if(code_type == 'popup_link'){
			$("#ec_code_popup_link").show();
			$("#ec_info_popup_link").show();
		}else if(code_type == 'plain_link'){
			$("#ec_code_plain_link").show();
			$("#ec_info_plain_link").show();
		}
	
	});

	//copy code to clipboard event handler
	var clipboard = new ClipboardJS('.trigger-copy-code');
    clipboard.on('success', function(e) {
        //display notifications on success
		Swal.fire({
		  toast: true,
		  position: 'center',
		  type: 'success',
		  title: 'Code copied.',
		  showConfirmButton: false,
		  timer: 2000
		});
    });
});