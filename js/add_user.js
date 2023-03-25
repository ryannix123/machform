/*** Functions ***/

//highlight one item of the permission list based on the form_id
function highlight_permission_list(form_id){

	//remove any highlight
	$("#li_" + form_id).removeClass('highlight_red highlight_green highlight_yellow');

	//decide which highlight need to be applied for this item
	if($("#perm_editform_"+ form_id).prop("checked") == true){
		$("#li_" + form_id).addClass("highlight_red");
	}else if($("#perm_editentries_"+ form_id).prop("checked") == true){
		$("#li_" + form_id).addClass("highlight_yellow");
	}else if($("#perm_viewentries_"+ form_id).prop("checked") == true || $("#perm_editreport_"+ form_id).prop("checked") == true){
		$("#li_" + form_id).addClass("highlight_green");
	}

}

//highlight the whole permission list, based on selected checkboxes on each list
function highlight_refresh_all(){
	$("#au_li_permissions > li").each(function(){
		var temp = $(this).attr("id").split("_");
		var form_id = temp[1];

		//remove any highlight
		$("#li_" + form_id).removeClass('highlight_red highlight_green highlight_yellow');

		//decide which highlight need to be applied for this item
		if($("#perm_editform_"+ form_id).prop("checked") == true){
			$("#li_" + form_id).addClass("highlight_red");
		}else if($("#perm_editentries_"+ form_id).prop("checked") == true){
			$("#li_" + form_id).addClass("highlight_yellow");
		}else if($("#perm_viewentries_"+ form_id).prop("checked") == true || $("#perm_editreport_"+ form_id).prop("checked") == true){
			$("#li_" + form_id).addClass("highlight_green");
		}
	});
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
	/* 2. Attach event to Permissions Checkboxes								   				   				   */
	/***************************************************************************************************************/

	//attach event to 'edit form','view entries' and 'edit report' checkbox
	$("#au_li_permissions .cb_editform,#au_li_permissions .cb_viewentries,#au_li_permissions .cb_editreport").bind('change', function() {
		var temp = $(this).attr("id").split('_');
		highlight_permission_list(temp[2]);
	});

	//attach event to 'edit entries' checkbox
	$("#au_li_permissions .cb_editentries").bind('change', function() {
		var temp = $(this).attr("id").split('_');
		var form_id = temp[2];

		if($(this).prop("checked") === true){
			$("#perm_viewentries_" + form_id).prop("checked",true);
			$("#perm_viewentries_" + form_id).prop("disabled",true);
		}else{
			$("#perm_viewentries_" + form_id).prop("disabled",false);
		}

		highlight_permission_list(form_id);
	});

	/***************************************************************************************************************/	
	/* 3. Attach event to 'Add User' button																	   	   */
	/***************************************************************************************************************/
	
	$("#button_add_user").click(function(){
		
		if($("#button_add_user").text() != 'Adding User...'){
				
				//display loader while saving
				$("#button_add_user").prop("disabled",true);
				$("#button_add_user").text('Adding User...');
				$("#add_user_form").submit();
		}
		
		return false;
	});

	/***************************************************************************************************************/	
	/* 4. Attach event to 'Save Changes' button on edit user page											   	   */
	/***************************************************************************************************************/
	
	$("#button_edit_user").click(function(){
		
		if($("#button_edit_user").text() != 'Saving...'){
				
				//display loader while saving
				$("#button_edit_user").prop("disabled",true);
				$("#button_edit_user").text('Saving...');
				$("#add_user_form").submit();
		}
		
		return false;
	});

	/***************************************************************************************************************/	
	/* 5. Attach event to 'Bulk Action' drop down															   	   */
	/***************************************************************************************************************/
	$('#au_bulk_action').bind('change', function() {
		var bulk_action = $(this).val();

		if(bulk_action == 'select_editform'){
			$("#au_li_permissions .cb_editform").prop("checked",true);
			$("#au_li_permissions > li").removeClass('highlight_red highlight_green highlight_yellow').addClass('highlight_red');
		}else if(bulk_action == 'select_editentries'){
			$("#au_li_permissions .cb_editentries").prop("checked",true);
			$("#au_li_permissions .cb_viewentries").prop("disabled",false).prop("checked",true).prop("disabled",true);

			highlight_refresh_all();
		}else if(bulk_action == 'select_viewentries'){
			$("#au_li_permissions .cb_viewentries").prop("checked",true);

			highlight_refresh_all();
		}else if(bulk_action == 'select_editreport'){
			$("#au_li_permissions .cb_editreport").prop("checked",true);

			highlight_refresh_all();
		}else if(bulk_action == 'unselect_editform'){
			$("#au_li_permissions .cb_editform").prop("checked",false);

			highlight_refresh_all();
		}else if(bulk_action == 'unselect_editentries'){
			$("#au_li_permissions .cb_editentries").prop("checked",false);
			$("#au_li_permissions .cb_viewentries").prop("disabled",false);

			highlight_refresh_all();
		}else if(bulk_action == 'unselect_viewentries'){
			$("#au_li_permissions .cb_viewentries").not(':disabled').prop("checked",false);

			highlight_refresh_all();
		}else if(bulk_action == 'unselect_editreport'){
			$("#au_li_permissions .cb_editreport").prop("checked",false);

			highlight_refresh_all();
		}
	});

	/***************************************************************************************************************/	
	/* 6. Attach event to Administer Checkbox								   				   				   */
	/***************************************************************************************************************/

	$("#au_priv_administer").bind('change', function() {
		if($(this).prop("checked") == true){
			$("#au_priv_new_forms,#au_priv_new_themes").prop("checked",true);
			$("#au_priv_new_forms,#au_priv_new_themes").prop("disabled",true);
			$(".user_permissions_list").fadeOut();
			
		}else{
			$("#au_priv_new_forms,#au_priv_new_themes").prop("disabled",false);
			$(".user_permissions_list").fadeIn();
			
		}	
	});
	
	
});