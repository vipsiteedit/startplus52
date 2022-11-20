function runEditorContacts(){
		$( "#dialog:ui-dialog" ).dialog( "destroy" );
		var dialog_width = 800,
			dialog_height = 520;
					
		$( "#dialog-modal-contact" ).dialog({
			height: dialog_height,
			width: dialog_width,
			modal: true,
			create: function(event, ui) {
			},
			resize: function(event, ui) {
				dialog_width = this.clientWidth;
				dialog_height = this.clientHeight;
			},
			buttons:[
			{
				text: ed_button['save'],
				click: function() {
					var options = { 
						target: '',
						beforeSubmit: showRequestPage, // функция, вызываемая перед передачей 
						success: showResponsePage, // функция, вызываемая при получении ответа
						timeout: 3000 // тайм-аут
					};
					$('#editpageheaderform').ajaxSubmit(options);
					$( this ).dialog( "close" );
				}
			},{
				text: ed_button['cancel'],
				click: function() {
					$( this ).dialog("close");
					$('#dialog-modal-contact').remove();
				}
			}],
			close: function () {
				frameWindow("hide");
			}
		});

		function showRequestPage(formData, jqForm, options) { 
			var queryString = $.param(formData);			
			return true; 
		} 
 
		// вызов после получения ответа 
		function showResponsePage(responseText, statusText)  {
			var dialogmodal = $('#dialog-modal-contact');
			if (dialogmodal != null) dialogmodal.remove();
		} 			
}