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
	/* 2. Merchant Settings														   				   				   */
	/***************************************************************************************************************/
	
	//attach event to 'enable merchant' checkbox
	$('#ps_enable_merchant').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').enable_merchant = 1;
			$("#ps_main_list li:gt(0)").fadeIn();
		}else{
			$("#ps_main_list").data('payment_properties').enable_merchant = 0;
			$("#ps_main_list li:gt(0)").slideUp();
		}
	});
	
	//attach event to 'select merchant' dropdown
	$('#ps_select_merchant').bind('change', function() {
		var merchant_type = $(this).val();
		$("#ps_main_list").data('payment_properties').merchant_type = merchant_type;

		$(".merchant_options,.stripe_option,.paypal_option,.authorizenet_option,.braintree_option,.paypal_rest_option").hide();
		$("#ps_currency_paypal_div,#ps_currency_stripe_div,#ps_currency_authorizenet_div,#ps_currency_braintree_div,#ps_currency_paypal_rest_div,#ps_currency_check_div").hide();
		
		//reset the currency to USD
		$("#ps_main_list").data('payment_properties').currency = 'USD';
		$('#ps_currency_paypal,#ps_currency_stripe,#ps_currency_authorizenet,#ps_currency_braintree,#ps_currency_paypal_rest,#ps_currency_check').val('USD');
		$(".ps_td_currency,.symbol").html('&#36;');

		//hide recurring cycle unit and reset to monthly cycle
		$("#ps_recurring_cycle_unit,#ps_recurring_cycle_unit_month_year").val('month').hide();
		$("#ps_main_list").data('payment_properties').recurring_unit = 'month';

		//show 'price name' by default
		$("#ps_price_name_container").show();
			
		if(merchant_type == 'paypal_standard'){
			$("#ps_paypal_options").show();
			$(".paypal_option").css("display","block").show();

			$("#ps_recurring_cycle_unit").show();

			$("#ps_currency_paypal_div").show();
		}else if(merchant_type == 'stripe'){
			$("#ps_stripe_options,#ps_stripe_info").show();
			$(".stripe_option").css("display","block").show();

			//stripe only support monthly and yearly recurring
			$("#ps_recurring_cycle_unit_month_year").show();

			$("#ps_currency_stripe_div").show();

			//hide price name if 'stripe email receipt' is enabled
			if($("#ps_main_list").data('payment_properties').stripe_enable_receipt == 1){
				$("#ps_price_name_container").hide();
			}
		}else if(merchant_type == 'paypal_rest'){
			$("#ps_paypal_rest_options").show();
			$(".paypal_rest_option").css("display","block").show();

			$("#ps_recurring_cycle_unit").show();

			$("#ps_currency_paypal_rest_div").show();
		}else if(merchant_type == 'authorizenet'){
			$("#ps_authorizenet_options").show();
			$(".authorizenet_option").css("display","block").show();

			$("#ps_recurring_cycle_unit").show();

			$("#ps_currency_authorizenet_div").show();
		}else if(merchant_type == 'braintree'){
			$("#ps_braintree_options").show();
			$(".braintree_option").css("display","block").show();

			$("#ps_currency_braintree_div").show();
		}else if(merchant_type == 'check'){
			$("#ps_check_options").show();

			$(".check_option").css("display","block").show();
			$("#ps_currency_check_div").show();
		}

		//only display 'trial period' when 'enable recurring payments' being checked
		//recurring payment is not available for Braintree and Check
		$("#ps_trial_div_container").hide();
		if($("#ps_main_list").data('payment_properties').enable_recurring == 1 && merchant_type != 'check' && merchant_type != 'braintree'){
			$("#ps_trial_div_container").show();
		}

		//only display 'charge one-time fee' when 'enable recurring payments' being checked
		//this option only available for Stripe and PayPal Pro
		$("#ps_setupfee_div_container").hide();
		if($("#ps_main_list").data('payment_properties').enable_recurring == 1 &&  (merchant_type == 'paypal_rest' || merchant_type == 'stripe') ){
			$("#ps_setupfee_div_container").show();
		}

	});
	
	//attach event to 'paypal email address' textbox
	$('#ps_paypal_email').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').paypal_email = $(this).val();
	});
	
	//attach event to 'paypal language' dropdown
	$('#ps_paypal_language').bind('change', function() {
		$("#ps_main_list").data('payment_properties').paypal_language = $(this).val();
	});

	//attach event to Stripe 'Enable Test Mode' checkbox
	$('#ps_stripe_enable_test_mode').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').stripe_enable_test_mode = 1;
			$("#ps_stripe_live_keys").slideUp();
			$("#ps_stripe_test_keys_div").slideDown();
		}else{
			$("#ps_main_list").data('payment_properties').stripe_enable_test_mode = 0;
			$("#ps_stripe_live_keys").slideDown();
			$("#ps_stripe_test_keys_div").slideUp();
		}
	});

	//attach event to Authorize.net 'Enable Test Mode' checkbox
	$('#ps_authorizenet_enable_test_mode').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').authorizenet_enable_test_mode = 1;
			$("#ps_authorizenet_live_keys").slideUp();
			$("#ps_authorizenet_test_keys_div").slideDown();
		}else{
			$("#ps_main_list").data('payment_properties').authorizenet_enable_test_mode = 0;
			$("#ps_authorizenet_live_keys").slideDown();
			$("#ps_authorizenet_test_keys_div").slideUp();
		}
	});

	//attach event to Braintree 'Enable Test Mode' checkbox
	$('#ps_braintree_enable_test_mode').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').braintree_enable_test_mode = 1;
			$("#ps_braintree_live_keys").slideUp();
			$("#ps_braintree_test_keys_div").slideDown();
		}else{
			$("#ps_main_list").data('payment_properties').braintree_enable_test_mode = 0;
			$("#ps_braintree_live_keys").slideDown();
			$("#ps_braintree_test_keys_div").slideUp();
		}
	});

	//attach event to PayPal Pro (REST API) 'Enable Test Mode' checkbox
	$('#ps_paypal_rest_enable_test_mode').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').paypal_rest_enable_test_mode = 1;
			$("#ps_paypal_rest_live_keys").slideUp();
			$("#ps_paypal_rest_test_keys_div").slideDown();
		}else{
			$("#ps_main_list").data('payment_properties').paypal_rest_enable_test_mode = 0;
			$("#ps_paypal_rest_live_keys").slideDown();
			$("#ps_paypal_rest_test_keys_div").slideUp();
		}
	});

	//attach event to PayPal 'Enable Test Mode' checkbox
	$('#ps_paypal_enable_test_mode').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').paypal_enable_test_mode = 1;
		}else{
			$("#ps_main_list").data('payment_properties').paypal_enable_test_mode = 0;
		}
	});
	
	//attach event to Stripe 'live secret key' textbox
	$('#ps_stripe_live_secret_key').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').stripe_live_secret_key = $(this).val();
	});

	//attach event to Stripe 'live public key' textbox
	$('#ps_stripe_live_public_key').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').stripe_live_public_key = $(this).val();
	});

	//attach event to Stripe 'test secret key' textbox
	$('#ps_stripe_test_secret_key').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').stripe_test_secret_key = $(this).val();
	});

	//attach event to Stripe 'test public key' textbox
	$('#ps_stripe_test_public_key').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').stripe_test_public_key = $(this).val();
	});

	//attach event to Authorize.net 'live API Login ID' textbox
	$('#ps_authorizenet_live_apiloginid').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').authorizenet_live_apiloginid = $(this).val();
	});

	//attach event to Authorize.net 'live Transaction Key' textbox
	$('#ps_authorizenet_live_transkey').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').authorizenet_live_transkey = $(this).val();
	});

	//attach event to Authorize.net 'test API Login ID' textbox
	$('#ps_authorizenet_test_apiloginid').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').authorizenet_test_apiloginid = $(this).val();
	});

	//attach event to Authorize.net 'test Transaction Key' textbox
	$('#ps_authorizenet_test_transkey').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').authorizenet_test_transkey = $(this).val();
	});

	//attach event to PayPal Pro 'live Client ID' textbox
	$('#ps_paypal_rest_live_clientid').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').paypal_rest_live_clientid = $(this).val();
	});

	//attach event to PayPal Pro 'live Secret Key' textbox
	$('#ps_paypal_rest_live_secret_key').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').paypal_rest_live_secret_key = $(this).val();
	});

	//attach event to PayPal Pro 'test secret key' textbox
	$('#ps_paypal_rest_test_clientid').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').paypal_rest_test_clientid = $(this).val();
	});

	//attach event to PayPal Pro 'test public key' textbox
	$('#ps_paypal_rest_test_secret_key').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').paypal_rest_test_secret_key = $(this).val();
	});

	//attach event to Braintree 'live Merchant ID' textbox
	$('#ps_braintree_live_merchant_id').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').braintree_live_merchant_id = $(this).val();
	});

	//attach event to Braintree 'live Public Key' textbox
	$('#ps_braintree_live_public_key').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').braintree_live_public_key = $(this).val();
	});

	//attach event to Braintree 'live Private Key' textbox
	$('#ps_braintree_live_private_key').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').braintree_live_private_key = $(this).val();
	});

	//attach event to Braintree 'live Encryption Key' textbox
	$('#ps_braintree_live_encryption_key').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').braintree_live_encryption_key = $(this).val();
	});

	//attach event to Braintree 'test Merchant ID' textbox
	$('#ps_braintree_test_merchant_id').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').braintree_test_merchant_id = $(this).val();
	});

	//attach event to Braintree 'test Public Key' textbox
	$('#ps_braintree_test_public_key').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').braintree_test_public_key = $(this).val();
	});

	//attach event to Braintree 'test Private Key' textbox
	$('#ps_braintree_test_private_key').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').braintree_test_private_key = $(this).val();
	});

	//attach event to Braintree 'test Encryption Key' textbox
	$('#ps_braintree_test_encryption_key').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').braintree_test_encryption_key = $(this).val();
	});
	
	/***************************************************************************************************************/	
	/* 3. Payment Options														   				   				   */
	/***************************************************************************************************************/
	
	//attach event to 'currency' dropdown
	$('#ps_currency_paypal,#ps_currency_stripe,#ps_currency_authorizenet,#ps_currency_braintree,#ps_currency_paypal_rest,#ps_currency_check').bind('change', function() {
		$("#ps_main_list").data('payment_properties').currency = $(this).val();
		
		//change the currency symbol on the field assignment section
		var temp = $(this).find('option:selected').text().split(' - ');
		$(".ps_td_currency,.symbol").html(temp[0]);
	});
	
	//attach event to 'show total amount' checkbox
	$('#ps_show_total_amount').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').show_total = 1;
			$("#ps_show_total_location_div").slideDown();
		}else{
			$("#ps_main_list").data('payment_properties').show_total = 0;
			$("#ps_show_total_location_div").slideUp();
		}
	});

	//attach event to 'Enable Fast Checkout (Apple Pay)' checkbox
	$('#ps_enable_payment_request_button').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').stripe_enable_payment_request_button = 1;
			$("#ps_enable_fast_checkout_div").show();
			
		}else{
			$("#ps_main_list").data('payment_properties').stripe_enable_payment_request_button = 0;
			$("#ps_enable_fast_checkout_div").hide();
		}
	});

	//attach event to the 'Stripe Account Country' dropdown
	$("#ps_stripe_account_country").bind('change', function() {
		$("#ps_main_list").data('payment_properties').stripe_account_country = $(this).val();
	});

	//attach event to 'add sales tax' checkbox
	$('#ps_enable_tax').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').enable_tax = 1;
			$("#ps_tax_rate_div").slideDown();
		}else{
			$("#ps_main_list").data('payment_properties').enable_tax = 0;
			$("#ps_tax_rate_div").slideUp();
		}
	});

	//attach event to 'tax rate' textbox
	$('#ps_tax_rate').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').tax_rate = $(this).val();
	});
	
	//attach event to 'total location' dropdown
	$('#ps_show_total_location').bind('change', function() {
		$("#ps_main_list").data('payment_properties').total_location = $(this).val();
	});
	
	//attach event to 'enable recurring' checkbox
	$('#ps_enable_recurring').bind('change', function() {
		var merchant_type = $("#ps_main_list").data('payment_properties').merchant_type;

		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').enable_recurring = 1;
			$("#ps_recurring_div,#ps_trial_div_container").slideDown();

			//charge one-time setup fee only available for stripe and paypal rest
			if(merchant_type == 'stripe' || merchant_type == 'paypal_rest'){
				$("#ps_setupfee_div_container").slideDown();
			}
		}else{
			$("#ps_main_list").data('payment_properties').enable_recurring = 0;
			$("#ps_recurring_div,#ps_trial_div_container").slideUp();

			//charge one-time setup fee only available for stripe and paypal rest
			if(merchant_type == 'stripe' || merchant_type == 'paypal_rest'){
				$("#ps_setupfee_div_container").slideUp();
			}
		}
	});
	
	//attach event to 'recurring cycle' dropdown
	$('#ps_recurring_cycle').bind('change', function() {
		$("#ps_main_list").data('payment_properties').recurring_cycle = parseInt($(this).val());
	});
	
	//attach event to 'recurring cycle unit' dropdown
	$('#ps_recurring_cycle_unit,#ps_recurring_cycle_unit_month_year').bind('change', function() {
		$("#ps_main_list").data('payment_properties').recurring_unit = $(this).val();
	});

	//attach event to 'enable trial' checkbox
	$('#ps_enable_trial').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').enable_trial = 1;
			$("#ps_trial_div").slideDown();
		}else{
			$("#ps_main_list").data('payment_properties').enable_trial = 0;
			$("#ps_trial_div").slideUp();
		}
	});

	//attach event to 'trial periods' dropdown
	$('#ps_trial_period').bind('change', function() {
		$("#ps_main_list").data('payment_properties').trial_period = parseInt($(this).val());
	});

	//attach event to 'trial periods unit' dropdown
	$('#ps_trial_unit').bind('change', function() {
		$("#ps_main_list").data('payment_properties').trial_unit = $(this).val();
	});
	
	//attach event to 'trial price' textbox
	$('#ps_trial_amount').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').trial_amount = $(this).val();
	});

	//attach event to 'charge a one-time setup fee' checkbox
	$('#ps_enable_setupfee').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').enable_setupfee = 1;
			$("#ps_setupfee_div").slideDown();
		}else{
			$("#ps_main_list").data('payment_properties').enable_setupfee = 0;
			$("#ps_setupfee_div").slideUp();
		}
	});

	//attach event to 'setup fee' textbox
	$('#ps_setupfee_amount').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').setupfee_amount = $(this).val();
	});

	//attach event to 'Send Invoice to User' checkbox
	$('#ps_enable_invoice').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').enable_invoice = 1;
			$("#ps_enable_invoice_div").slideDown();
		}else{
			$("#ps_main_list").data('payment_properties').enable_invoice = 0;
			$("#ps_enable_invoice_div").slideUp();
		}
	});

	//attach event to 'User Email Address' dropdown
	$('#ps_invoice_email').bind('change', function() {
		$("#ps_main_list").data('payment_properties').invoice_email = $(this).val();
	});

	//attach event to 'Delay Notifications' checkbox
	$('#ps_delay_notifications').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').delay_notifications = 1;
		}else{
			$("#ps_main_list").data('payment_properties').delay_notifications = 0;
		}
	});

	//attach event to 'Ask for Billing Address' checkbox
	$('#ps_ask_billing').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').ask_billing = 1;
		}else{
			$("#ps_main_list").data('payment_properties').ask_billing = 0;
		}
	});

	//attach event to 'Ask for Shipping Address' checkbox
	$('#ps_ask_shipping').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').ask_shipping = 1;
		}else{
			$("#ps_main_list").data('payment_properties').ask_shipping = 0;
		}
	});

	//attach event to 'Save Cards to Authorize.net' checkbox
	$('#ps_save_cc_data').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').authorizenet_save_cc_data = 1;
		}else{
			$("#ps_main_list").data('payment_properties').authorizenet_save_cc_data = 0;
		}
	});

	//attach event to 'Enable Discount' checkbox
	$('#ps_enable_discount').bind('change', function() {
		if($(this).prop("checked") == true){
			//check if the form already has coupon field or not
			if($("#ps_discount_element_id").children().eq(0).val().length == 0){
				alert("Your form currently doesn't have any coupon code field. \nPlease add a Single Line Text field into your form to use this feature.");
				$(this).prop("checked",false);
			}else{
				$("#ps_main_list").data('payment_properties').enable_discount = 1;
				$("#ps_discount_div").slideDown();
			}
		}else{
			$("#ps_main_list").data('payment_properties').enable_discount = 0;
			$("#ps_discount_div").slideUp();
		}
	});

	//attach event to 'Enable Stripe Email Receipts' checkbox
	$('#ps_stripe_enable_receipt').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').stripe_enable_receipt = 1;
			$("#ps_stripe_enable_receipt_div").slideDown();
			$("#ps_price_name_container").hide();
		}else{
			$("#ps_main_list").data('payment_properties').stripe_enable_receipt = 0;
			$("#ps_stripe_enable_receipt_div").slideUp();
			$("#ps_price_name_container").show();
		}
	});

	//attach event to 'Send Customer Email to Authorize.net' checkbox
	$('#ps_authorizenet_enable_email').bind('change', function() {
		if($(this).prop("checked") == true){
			$("#ps_main_list").data('payment_properties').authorizenet_enable_email = 1;
			$("#ps_authorizenet_enable_email_div").slideDown();
		}else{
			$("#ps_main_list").data('payment_properties').authorizenet_enable_email = 0;
			$("#ps_authorizenet_enable_email_div").slideUp();
		}
	});


	//Initialize datepicker for discount expiry date
	$('#linked_picker_discount_expiry').datepick({ 
	    onSelect: update_discount_expiry_linked,
	    showTrigger: '#discount_expiry_pick_img'});

	//Update datepicker from three input controls for discount expiry date (mm/dd/yyyy)
	$('#discount_expiry_mm,#discount_expiry_dd,#discount_expiry_yyyy').bind('blur mouseout', function() {
	    var min_dd = parseInt($('#discount_expiry_dd').val(), 10);
	    var min_mm = parseInt($('#discount_expiry_mm').val(), 10) - 1;
	    var min_yyyy = parseInt($('#discount_expiry_yyyy').val(), 10);
		
	    if(!isNaN(min_dd) && !isNaN(min_mm) && !isNaN(min_yyyy) && (min_dd != 0) && (min_mm != -1)){
			
			$('#linked_picker_discount_expiry').datepick('setDate', new Date( 
		        min_yyyy, 
		        min_mm, 
		        min_dd
		    )); 
		    
		    //update the properties
		    var new_discount_expiry_date = $('#discount_expiry_yyyy').val() + '-' + $('#discount_expiry_mm').val() + '-' + $('#discount_expiry_dd').val();
		    $("#ps_main_list").data('payment_properties').discount_expiry_date = new_discount_expiry_date;
	    }
	}); 

	//attach event to 'amount off/percent off' dropdown
	$('#ps_discount_type').bind('change', function() {
		var discount_type = $(this).val();

		if(discount_type == 'percent_off'){
			$("#discount_type_currency_sign").hide();
			$("#discount_type_percentage_sign").show();
		}else if(discount_type == 'amount_off'){
			$("#discount_type_currency_sign").show();
			$("#discount_type_percentage_sign").hide();
		}

		$("#ps_main_list").data('payment_properties').discount_type = discount_type;
	});

	//attach event to discount amount/percentage textbox
	$('#ps_discount_amount').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').discount_amount = $(this).val();
	});

	//attach event to discount code textbox
	$('#ps_discount_code').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').discount_code = $(this).val();
	});

	//attach event to 'select coupon code field' dropdown
	$('#ps_discount_element_id').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').discount_element_id = parseInt($(this).val());
	});

	//attach event to 'select email receipt field' dropdown
	$('#ps_stripe_receipt_element_id').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').stripe_receipt_element_id = parseInt($(this).val());
	});

	//attach event to 'select email address field' dropdown for Authorize.net
	$('#ps_authorizenet_email_element_id').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').authorizenet_email_element_id = parseInt($(this).val());
	});
	
	//attach event to discount Max Redemption
	$('#ps_discount_max_usage').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').discount_max_usage = parseInt($(this).val());
	});

	/***************************************************************************************************************/	
	/* 4. Define Prices														   				   				   	   */
	/***************************************************************************************************************/
	
	//attach event to 'fixed/variable amount' dropdown
	$('#ps_pricing_type').bind('change', function() {
		if($(this).val() == 'fixed'){
			$("#ps_box_price_variable_amount_div").hide();
			$("#ps_box_price_fixed_amount_div").fadeIn();
			
			$("#ps_main_list").data('payment_properties').price_type = 'fixed';
		}else{
			$("#ps_box_price_fixed_amount_div").hide();
			$("#ps_box_price_variable_amount_div").fadeIn();
			
			$("#ps_main_list").data('payment_properties').price_type = 'variable';
		}
	});
	
	//attach event to 'price amount' textbox
	$('#ps_price_amount').bind('keyup mouseout change', function() {
		var price_amount = parseFloat($(this).val());
		$("#ps_main_list").data('payment_properties').price_amount = price_amount;
		
	});
	
	//attach event to 'price name' or 'charge description' textbox
	$('#ps_price_name,#ps_stripe_charge_desc').bind('keyup mouseout change', function() {
		$("#ps_main_list").data('payment_properties').price_name = $(this).val();
	});
	
	//attach event to 'add field to set prices' dropdown
	$('#ps_select_field_prices').bind('change', function() {
		
		if($(this).val() == ''){
			return true;
		}
		
		var element_id = parseInt($(this).val());
		var choices_array = '';
		
		if($("#ps_box_define_prices").data('field_options') != null){
			choices_array = $("#ps_box_define_prices").data('field_options')[element_id];
		}
		
		var li_markup = '';
		
		var temp = $("#ps_currency option:selected").text().split(' - ');
		var currency_symbol = temp[0];
		var field_price_properties = new Array();
		
		//build the row markup for the price table
		if($.isArray(choices_array)){ //if this is choices field
			
			var tr_markup = '';
			$.each(choices_array, function(index, data) {
				 
				tr_markup += '<tr>' +
								'<td class="ps_td_field_label">' + data.option + '</td>' +
								'<td class="ps_td_field_price">' +
									'<span class="ps_td_currency">' + currency_symbol + '</span><input type="text" id="price_' + element_id + '_' + data.option_id + '" value="0.00" class="element text large">' +
								'</td>' +
							  '</tr>';
				
				field_price_properties[parseInt(data.option_id)] = {element_id : element_id, option_id: parseInt(data.option_id), price : 0, element_type: 'multi'};
			});
			
			li_markup = '<li id="liprice_' + element_id + '" style="display: none"><table cellspacing="0" width="100%"><thead><tr><td colspan="2"><strong>';
			li_markup += $(this).find('option:selected').text();
			li_markup += '</strong><a class="delete_liprice" id="deleteliprice_'+ element_id +'" href="#"><span class="icon-cancel-circle"></span></a></td></tr></thead><tbody>';
			li_markup += tr_markup;
			li_markup += '</tbody></table></li>';
			
		}else{ //if this is price field
			li_markup = '<li id="liprice_' + element_id +'">' +
							'<table cellspacing="0" width="100%">' +
							'<thead>' +
								'<tr>' +
									'<td>' +
										'<strong>' + $(this).find('option:selected').text() + '</strong>' +
										'<a class="delete_liprice" id="deleteliprice_'+ element_id +'" href="#"><span class="icon-cancel-circle"></span></a>' +
									'</td>' +
								 '</tr>' +
							'</thead>' +
							'<tbody>' +
								'<tr>' +
									'<td class="ps_td_field_label">Amount will be entered by the client.</td>' +
								'</tr>' +
							'</tbody>' +
							'</table>' +
						 '</li>';
			
			field_price_properties.push({element_id : element_id, option_id: 0, price : 0, element_type: 'price'})
		}
		
		
		$("#ps_field_assignment").prepend(li_markup);
		$("#liprice_" + element_id).fadeIn();
		
		//attach the dom data
		$("#liprice_" + element_id).data('field_price_properties',field_price_properties);
		
		$(this).find('option:selected').remove();
		
		if($("#ps_select_field_prices option").length == 1){
			$("#ps_select_field_prices option").text('No More Fields Available');
		}
	});
	
	//attach event to field prices textbox
	$('#ps_field_assignment').delegate('input.text', 'keyup mouseout change', function(e) {

		var temp = $(this).attr('id').split('_');
		var element_id = parseInt(temp[1]);
		var option_id  = parseInt(temp[2]);
		
		$("#liprice_" + element_id).data('field_price_properties')[option_id].price = parseFloat($(this).val());
	});
	
	//attach event to 'delete field price' image
	//attach event to field prices textbox
	$('#ps_field_assignment').delegate('a.delete_liprice', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var element_id = temp[1];
		
		$("#liprice_" + element_id).fadeOut("fast",function(){
			$(this).remove();
		});
		
		$("#ps_select_field_prices").append('<option value="'+ element_id +'">'+ $(this).prev().text() +'</option>');
		$('#ps_select_field_prices option[value=""]').text('');
		
		return false;
	});
	
	/***************************************************************************************************************/	
	/* 5. Attach event to 'Save Settings' button																   */
	/***************************************************************************************************************/
	$("#button_save_payment").click(function(){
		
		if($("#button_save_payment").text() != 'Saving...'){
				
				//display loader while saving
				$("#button_save_payment").prop("disabled",true);
				$("#button_save_payment").text('Saving...');
				$("#button_save_payment").after("<div class='small_loader_box' style='float: right'><img src='images/loader_small_grey.gif' /></div>");
				
				var liprice_elements = $("#ps_field_assignment li");
				if(liprice_elements.length >= 1){
					var liprice_prop_array = new Array();
					liprice_elements.each(function(index){
						liprice_prop_array[index] = $(this).data('field_price_properties');
					});
				}
				
				var csrf_token = $("#content").data("csrftoken");

				//do the ajax call to save the theme
				$.ajax({
					   type: "POST",
					   async: true,
					   url: "save_payment_settings.php",
					   data: {
							  	payment_properties: $("#ps_main_list").data('payment_properties'),
							  	csrf_token: csrf_token,
							  	field_prices: liprice_prop_array
							  },
					   cache: false,
					   global: false,
					   dataType: "json",
					   error: function(xhr,text_status,e){
							   //error, display the generic error message		  
							   alert('Error! Unable to save payment settings. Please try again.');
					   },
					   success: function(response_data){
							   
						   if(response_data.status == 'ok'){
							   window.location.replace('manage_forms.php?id=' + response_data.form_id + '&hl=1');
						   }else{
							   alert('Error! Unable to save payment settings. Please try again.');
						   }
							   
					   }
				});
		}
		
		
		return false;
	});
	
	/***************************************************************************************************************/	
	/* 6. Initializator codes																   					   */
	/***************************************************************************************************************/
	$("#discount_expiry_mm").blur();
	
});

/** Functions **/

//Discount Expiry
//this function being used to update three inputs (mm/dd/yyyy) to match the selection from the datepicker
function update_discount_expiry_linked(dates) { 
    $('#discount_expiry_mm').val(dates.length ? dates[0].getMonth() + 1 : ''); 
    $('#discount_expiry_dd').val(dates.length ? dates[0].getDate() : ''); 
    $('#discount_expiry_yyyy').val(dates.length ? dates[0].getFullYear() : ''); 
    
    //update the properties
    var new_discount_expiry_date = $('#discount_expiry_yyyy').val() + '-' + $('#discount_expiry_mm').val() + '-' + $('#discount_expiry_dd').val();
    $("#ps_main_list").data('payment_properties').discount_expiry_date = new_discount_expiry_date;
}