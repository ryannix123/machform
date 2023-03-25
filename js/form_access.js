$(function(){
    
	/***************************************************************************************************************/	
	/* 1. Attach event to 'Save Settings' button																   */
	/***************************************************************************************************************/
	$("#button_save_notification").click(function(){
		
		if($("#button_save_notification").text() != 'Saving...'){
				
				//display loader while saving
				$("#button_save_notification").prop("disabled",true);
				$("#button_save_notification").text('Saving...');
				$("#button_save_notification").after("<img style=\"margin-left: 10px\" src='images/loader_small_grey.gif' />");
				
				$("#fa_form").submit();
		}
		
		
		return false;
	});

	
});