var mf_card = null;
var mf_payment_request = null;
var mf_payment_request_button = null;

//IE8 or below doesn't support trim, below is the workaround
if(typeof String.prototype.trim !== 'function') {
  String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g, ''); 
  }
}

//submit payment data to stripe and charge it
function mf_submit_payment(){

	//initialize variables
	var mf_name = '';

	mf_name = $("#cc_first_name").val().trim() + ' ' + $("#cc_last_name").val().trim();
	mf_name = $.trim(mf_name);
	
	//billing address
	var mf_address_line1 = '';
	var mf_address_city = '';
	var mf_address_state = '';
	var mf_address_zip = '';
	var mf_address_country = '';
	var card_data = '';
	
	//collect billing address
	if($("#li_billing_address").length > 0){
		mf_address_line1 = $("#billing_street").val().trim();
		mf_address_city = $("#billing_city").val().trim();
		mf_address_state = $("#billing_state").val().trim();
		mf_address_zip = $("#billing_zipcode").val().trim();
		mf_address_country = $("#billing_country").val().trim();

		card_data = {
					   	billing_details: {
					   		name: mf_name,
					   		address: {
								line1: mf_address_line1,
								city: mf_address_city,
								state: mf_address_state,
								postal_code: mf_address_zip,
								country: mf_address_country
							}
						}
					};
	}else{
		card_data = {
					   	billing_details: {
					   		name: mf_name
						}
					};
	}

	//create payment method and send the id to server
	mf_stripe.createPaymentMethod('card',mf_card,card_data).then(function(response) {
		    if(response.error) {
		    	//enable submit button again
				$("#btn_submit_payment").prop("disabled",false);
				$("#btn_submit_payment").val($("#btn_submit_payment").data('originallabel'));
				$("#mf_payment_loader_img").hide();

				//display the error on credit card field
				$("#error_message").show();
				$("#li_credit_card").addClass("error");
					
				$("#credit_card_error_message").html(response.error.message).show();
				
				if($("html").hasClass("embed")){
					$.postMessage({mf_iframe_height: $('body').outerHeight(true)}, '*', parent );
				}

				alert('There was a problem with your submission. Please check highlighted fields.');
		    }else{
				var stripe_payment_method_id = response.paymentMethod.id;

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
						url: $("#main_body").data("machformpath") + "payment_submit_stripe.php",
						data: {
								payment_method_id: stripe_payment_method_id,
								form_id: $("#form_id").val(),
								mfsid: $("#mfsid").val(),
								payment_properties: payment_data,
								record_id: $("#main_body").data("recordid")
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
									mf_handle_server_response(response_data);   
							  }
				});
		    }
	});
	
	
}

//handle server response from payment_submit_stripe.php
function mf_handle_server_response(response_data){
	if(response_data.status == 'ok'){
		$("#form_payment_redirect").submit();
	}else if(response_data.status == 'error'){
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
	}else if(response_data.status == 'requires_action'){
		mf_handle_action(response_data);
	}  
}

//handle 3ds action
function mf_handle_action(response) {
  	if($("#main_body").data("recurring") == '1'){
  		mf_stripe.handleCardPayment(response.payment_intent_client_secret).then(
		  	function(result) {
			    if (result.error) {
			      	//Show error in payment form
			      	//display the error on credit card field
					$("#error_message").show();
					$("#li_credit_card").addClass("error");
						
					$("#credit_card_error_message").html('Unable to authorize the payment. Please try it again.').show();

					//enable submit button again
					$("#btn_submit_payment").prop("disabled",false);
					$("#btn_submit_payment").val($("#btn_submit_payment").data('originallabel'));
					$("#mf_payment_loader_img").hide();

					if($("html").hasClass("embed")){
						$.postMessage({mf_iframe_height: $('body').outerHeight(true)}, '*', parent );
					}

					alert('Unable to authorize the payment. Please try it again.');
			    }else{
			      // The card action has been handled
			      // The PaymentIntent can be confirmed again on the server
			      
			      var formData = new FormData();
			      formData.append('payment_intent_id',result.paymentIntent.id);
			      formData.append('form_id',$("#form_id").val());
			      formData.append('record_id',$("#main_body").data("recordid"));
			      formData.append('mfsid',$("#mfsid").val());
			     
			      fetch($("#main_body").data("machformpath") + "payment_submit_stripe.php", {
			        method: 'POST',
			        body: formData 
			      }).then(function(confirmResult) {
			        return confirmResult.json();
			      }).then(mf_handle_server_response);
			    }
			}
		);
  	}else{
  		mf_stripe.handleCardAction(response.payment_intent_client_secret).then(
		  	function(result) {
			    if (result.error) {
			      	//Show error in payment form
			      	//display the error on credit card field
					$("#error_message").show();
					$("#li_credit_card").addClass("error");
						
					$("#credit_card_error_message").html('Unable to authorize the payment. Please try it again.').show();

					//enable submit button again
					$("#btn_submit_payment").prop("disabled",false);
					$("#btn_submit_payment").val($("#btn_submit_payment").data('originallabel'));
					$("#mf_payment_loader_img").hide();

					if($("html").hasClass("embed")){
						$.postMessage({mf_iframe_height: $('body').outerHeight(true)}, '*', parent );
					}

					alert('Unable to authorize the payment. Please try it again.');
			    }else{
			      // The card action has been handled
			      // The PaymentIntent can be confirmed again on the server
			      
			      var formData = new FormData();
			      formData.append('payment_intent_id',result.paymentIntent.id);
			      formData.append('form_id',$("#form_id").val());
			      formData.append('record_id',$("#main_body").data("recordid"));
			      formData.append('mfsid',$("#mfsid").val());
			      
			      fetch($("#main_body").data("machformpath") + "payment_submit_stripe.php", {
			        method: 'POST',
			        body: formData 
			      }).then(function(confirmResult) {
			        return confirmResult.json();
			      }).then(mf_handle_server_response);
			    }
			}
		);
  	}
  	
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
	
	//initialize Card element
	mf_card = mf_stripe_elements.create('card', {
				  hidePostalCode: false,
				  style: {
				    base: {
				      iconColor: '#666EE8',
				      color: '#666666',
				      lineHeight: '30px',
				      fontWeight: 300,
				      fontFamily: 'Helvetica Neue',
				      fontSize: '15px',

				      '::placeholder': {
				        color: '#808080',
				      },
				    },
				  }
				});

	//mount the card UI component
	mf_card.mount('#stripe-card-element');

	//handle credit card validation errors
	mf_card.addEventListener('change', function(event) {
		if (event.error) {
			$("#credit_card_error_message").html(event.error.message).show();
	  	}else{
	   		$("#credit_card_error_message").html('').hide();
	  	}
	});

	//initialize the payment request button if enabled
	if($("#stripe-payment-request-button").length > 0){
		var mf_shipping_options = [];
		if(mf_stripe_ask_shipping == true){
			mf_shipping_options = [{
									id: 'shipping',
									label: '---',
									detail: '',
									amount: 0,
								  }];
		}

		mf_payment_request = mf_stripe.paymentRequest({
								  country: mf_stripe_account_country,
								  currency: mf_stripe_payment_currency,
								  requestPayerName: true,
								  requestPayerEmail: false,
								  requestShipping: mf_stripe_ask_shipping,
								  shippingOptions: mf_shipping_options,
								  total: {
								    label: mf_stripe_total_payment_label,
								    amount: mf_stripe_total_payment_amount,
								  },
							 });
		mf_payment_request_button = mf_stripe_elements.create('paymentRequestButton', {
									  	paymentRequest: mf_payment_request,
									});
		
		//check the availability of the Payment Request API first and then mount it
		mf_payment_request.canMakePayment().then(function(result) {
			if (result){
				mf_payment_request_button.mount('#stripe-payment-request-button');
			}else{
				$('#stripe-payment-request-button,#li_payment_detail_title').hide();
			}
		});

		mf_payment_request.on('paymentmethod', function(ev) {
			//disable submit button
			$("#btn_submit_payment").val("Processing. Please wait...");
			$("#btn_submit_payment").prop("disabled",true);
			$("#mf_payment_loader_img").show();

			var mf_ship_same_as_billing = 0; //apple pay or google pay only provide shipping address, so this value always 0
	       
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

			//collect shipping address
			if(mf_stripe_ask_shipping == true){
				mf_ship_address_line1 = ev.shippingAddress.addressLine.join("\n");
				mf_ship_address_city = ev.shippingAddress.city;
				mf_ship_address_state = ev.shippingAddress.region;
				mf_ship_address_zip = ev.shippingAddress.postalCode;
				mf_ship_address_country = ev.shippingAddress.country;
			}

			var payment_data = {
        						first_name: ev.payerName.split(' ').slice(0, -1).join(' '),
        						last_name: ev.payerName.split(' ').slice(-1).join(' '),

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
						url: $("#main_body").data("machformpath") + "payment_submit_stripe.php",
						data: {
								payment_method_id: ev.paymentMethod.id,
								form_id: $("#form_id").val(),
								payment_properties: payment_data,
								mfsid: $("#mfsid").val(),
								record_id: $("#main_body").data("recordid")
							  },
							  cache: false,
							  global: false,
							  dataType: "json",
							  error: function(xhr,text_status,e){
							  		ev.complete('fail');
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

									alert('There was a problem with your submission. Please check your payment details. \nError message: ' + xhr.responseText);
							  },
							  success: function(response_data){							   
									ev.complete('success');
									mf_handle_server_response(response_data);   
							  }
				});
			
		});
	}

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
				//send request to stripe
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
