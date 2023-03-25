//IE8 or below doesn't support trim, below is the workaround
if(typeof String.prototype.trim !== 'function') {
  String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g, ''); 
  }
}

//submit payment data to PayPal and charge it
function mf_submit_payment(){

	var mf_number = '';
	var mf_cvc = '';
	var mf_exp_month = '';
	var mf_exp_year = '';

	mf_number = $("#cc_number").val().trim();

	if($("#cc_cvv").val().trim().length > 0){
		mf_cvc = $("#cc_cvv").val().trim();
	}

	mf_exp_month = $('#cc_expiry_month').val();
	mf_exp_year  = $('#cc_expiry_year').val();


    var mf_ship_same_as_billing = 1;
    if($("#mf_same_shipping_address").prop("checked") == true){
    	mf_ship_same_as_billing = 1;
    }else{
    	mf_ship_same_as_billing = 0;
    }

	//billing address
	var mf_address_line1 = '';
	var mf_address_city = '';
	var mf_address_state = '';
	var mf_address_zip = '';
	var mf_address_country = '';

	//shipping address
	var mf_ship_address_line1 = '';
	var mf_ship_address_city = '';
	var mf_ship_address_state = '';
	var mf_ship_address_zip = '';
	var mf_ship_address_country = '';

	//collect billing address
	if($("#li_billing_address").length > 0){
		mf_address_line1 = $("#billing_street").val().trim();
		mf_address_city = $("#billing_city").val().trim();
		mf_address_state = $("#billing_state").val().trim();
		mf_address_zip = $("#billing_zipcode").val().trim();
		mf_address_country = $("#billing_country").val().trim();
	}

	//collect shipping address
	if($("#li_shipping_address").length > 0){
		mf_ship_address_line1 = $("#shipping_street").val().trim();
		mf_ship_address_city = $("#shipping_city").val().trim();
		mf_ship_address_state = $("#shipping_state").val().trim();
		mf_ship_address_zip = $("#shipping_zipcode").val().trim();
		mf_ship_address_country = $("#shipping_country").val().trim();
	}


    //collect all payment data
    var payment_data = {
    					first_name: $("#cc_first_name").val().trim(), 
    					last_name: $("#cc_last_name").val().trim(),

    					card_number: mf_number,
    					card_cvc: mf_cvc,
    					card_exp_month: mf_exp_month,
    					card_exp_year: mf_exp_year,
    					
    					billing_street: mf_address_line1,
						billing_city: mf_address_city,
						billing_state: mf_address_state,
						billing_zipcode: mf_address_zip,
						billing_country: mf_address_country,

						same_shipping_address: mf_ship_same_as_billing,

						shipping_street: mf_ship_address_line1,
						shipping_city: mf_ship_address_city,
						shipping_state: mf_ship_address_state,
						shipping_zipcode: mf_ship_address_zip,
						shipping_country: mf_ship_address_country
    				};
    
    //do the ajax call to charge the card and send the payment data
	$.ajax({
			type: "POST",
			async: true,
			url: $("#main_body").data("machformpath") + "payment_submit_paypal_rest.php",
			data: {
					form_id: $("#form_id").val(),
					mfsid: $("#mfsid").val(),
					record_id: $("#main_body").data("recordid"),
					payment_properties: payment_data
				  },
				  cache: false,
				  global: false,
				  dataType: "json",
				  error: function(xhr,text_status,e){
						//display the error on credit card field
						$("#error_message").show();
						$("#li_credit_card").addClass("error");
							
						$("#credit_card_error_message").html("Unknown Error. Please contact tech support.").show();

						//enable submit button again
						$("#btn_submit_payment").prop("disabled",false);
						$("#btn_submit_payment").val($("#btn_submit_payment").data('originallabel'));
						$("#mf_payment_loader_img").hide();

						if($("html").hasClass("embed")){
							$.postMessage({mf_iframe_height: $('body').outerHeight(true)}, '*', parent );
						}

						alert('There was a problem with your submission. Please check highlighted fields. \nError message: ' + xhr.responseText);
				  },
				  success: function(response_data){							   
						if(response_data.status == 'ok'){
							$("#form_payment_redirect").submit();
						}else{
							//display the error on credit card field
							$("#error_message").show();
							$("#li_credit_card").addClass("error");
								
							$("#credit_card_error_message").html(response_data.message).show();

							//enable submit button again
							$("#btn_submit_payment").prop("disabled",false);
							$("#btn_submit_payment").val($("#btn_submit_payment").data('originallabel'));
							$("#mf_payment_loader_img").hide();

							if($("html").hasClass("embed")){
								$.postMessage({mf_iframe_height: $('body').outerHeight(true)}, '*', parent );
							}

							alert('There was a problem with your submission. Please check highlighted fields.');
						}   
				  }
	});
	
}


//reset all error messages on the form
function mf_clear_errors(){
	$("#error_message").hide();
	$("li.error").removeClass('error');
	$("#credit_card_error_message").html('');
	$("#shipping_error_message").html('');
	$("#billing_error_message").html('');
}

//validate all required fields and format
function mf_validate_fields(){
	var validation_status = true;

	//validate credit card field
	if($.payment.validateCardNumber($("#cc_number").val().trim()) == false){
		$("#error_message").show();
		$("#li_credit_card").addClass("error");
			
		$("#credit_card_error_message").html("Your credit card number is incorrect. Please enter correct number.").show();

		validation_status = false;
	}

	//validate billing address, if exist
	if($("#li_billing_address").length > 0){
		if($("#billing_street").val().trim().length == 0 || $("#billing_city").val().trim().length == 0 || $("#billing_state").val().trim().length == 0 || $("#billing_zipcode").val().trim().length == 0 || $("#billing_country").val().trim().length == 0){
			$("#error_message").show();
			$("#li_billing_address").addClass("error");
			
			$("#billing_error_message").html("The field is required. Please enter a complete billing address.").show();

			validation_status = false;
		}
	}

	//validate shipping address, if exist
	if($("#li_shipping_address").length > 0){
		if($("#mf_same_shipping_address").prop("checked") == false){
			if($("#shipping_street").val().trim().length == 0 || $("#shipping_city").val().trim().length == 0 || $("#shipping_state").val().trim().length == 0 || $("#shipping_zipcode").val().trim().length == 0 || $("#shipping_country").val().trim().length == 0){
				$("#error_message").show();
				$("#li_shipping_address").addClass("error");
				
				$("#shipping_error_message").html("The field is required. Please enter a complete shipping address.").show();

				validation_status = false;
			}
		}
	}

	return validation_status;
}

$(function(){
	
	//attach event handler to shipping address checkbox
	$('#mf_same_shipping_address').on('change', function() {
		if($(this).prop("checked") == true){
			$(".shipping_address_detail").hide();
		}else{
			$(".shipping_address_detail").show();
		}

		if($("html").hasClass("embed")){
			$.postMessage({mf_iframe_height: $('body').outerHeight(true)}, '*', parent );
		}
		
	});

	//handle form submissions
	$('form.appnitro').submit(function() {
			var fields_validated = false;

			//disable submit button
			$("#btn_submit_payment").val("Processing. Please wait...");
			$("#btn_submit_payment").prop("disabled",true);
			$("#mf_payment_loader_img").show();

			mf_clear_errors();
			fields_validated = mf_validate_fields();

			if(fields_validated === true){
				//send request to PayPal
				mf_submit_payment();	
			}else{
				//enable submit button again
				$("#btn_submit_payment").prop("disabled",false);
				$("#btn_submit_payment").val($("#btn_submit_payment").data('originallabel'));
				$("#mf_payment_loader_img").hide();

				if($("html").hasClass("embed")){
					$.postMessage({mf_iframe_height: $('body').outerHeight(true)}, '*', parent );
				}
				
				alert('There was a problem with your submission. Please check highlighted fields.');
			}

			//always return false, to override submit event
			return false;
	});

});
