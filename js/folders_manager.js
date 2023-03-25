$(function(){
    
	var folder_list = document.getElementById('mf_folder_list');
	
	//build the folder sorting
	var sortable = Sortable.create(folder_list, {
						handle: '.folder_move_handler',
						animation: 150,
						ghostClass: 'folder_move_highlight',
						onEnd: function(){
							var folder_pos = this.toArray();

							axios.post('save_folders_position.php', {
								folder_positions: folder_pos
							})
							.then(function (response) {
								if(response.data.status == 'ok'){
									//display notifications on success
									Swal.fire({
									  toast: true,
									  position: 'bottom-end',
									  type: 'success',
									  title: 'Folders position saved',
									  showConfirmButton: false,
									  timer: 2000
									});
								}else{
									alert('Error: ' + response.data);
								}
							})
							.catch(function (error) {
								alert(error);
							});
						}
				   });

	//'delete folder' event handler
	$(".delete_folder_link").click(function(){
		var folder_id = $(this).data('id');
		
		Swal.fire({
			title: 'Confirm folder deletion',
			text: "The smart folder will be deleted",
			footer: "Any forms inside the folder will remain intact.",
			type: 'warning',
			showCancelButton: true,
		  	confirmButtonText: 'Yes, delete folder.'
		}).then((result) => {
			if (result.value) {
		    	//delete the folder
		    	axios.post('delete_folder.php', {
					folder_id: folder_id
				})
				.then(function (response) {
					if(response.data.status == 'ok'){
						$("#li_" + folder_id).remove();
						
						//display notifications on success
						Swal.fire({
						  toast: true,
						  position: 'bottom-end',
						  type: 'success',
						  title: 'The folder has been deleted',
						  showConfirmButton: false,
						  timer: 2000
						});
					}else{
						alert('Error: ' + response.data);
					}
				})
				.catch(function (error) {
					alert(error);
				});
		  	}
		})

		return false;
	});
});