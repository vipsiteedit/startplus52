seEvents.actions.vars = {
	edit_var: function(id) {
		if (typeof seEvents=='undefined') {
			return false;
		}
		var formdata = {name: 'editvar', value: ""+id+""};
		$.ajax({
			url: seEvents.content_url+"?on_ajax_execute",
			type: 'POST',
			data: formdata,
			success: function(data) {
				//console.log('ajax success: editsection');
				seEvents.showModal(data, 'jqueryform=sitevars', formdata, function(result) {
					var container = seEvents.viewer.contents().find('[data-editvar='+id+']');
					var btns = container.children().first().detach();
					container.html(result);
					container.prepend(btns);
					seEvents._setHovers();
				});
			}
		});
	},
	edit_logo: function() {
		if (typeof seEvents=='undefined') {
			return false;
		}
		var formdata = {name: 'editvar', value: "site_sitelogotype"};
		$.ajax({
			url: seEvents.content_url+"?on_ajax_execute",
			type: 'POST',
			data: formdata,
			success: function(data) {
				//console.log('ajax success: editsection');
				seEvents.showModal(data, 'jqueryform=sitelogo', formdata, function(result) {
					var container = seEvents.viewer.contents().find('#LogotypeBlock');
					var btns = container.children().first().detach();
					container.html('<a href="/"><img id="siteLogotype" src="'+result+'" border="0"></a>');
					container.prepend(btns);
					seEvents._setHovers();
				});
			}
		});
	}
}