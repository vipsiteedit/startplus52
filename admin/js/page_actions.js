seEvents.actions.page = {
	
	editor_addpage: function() {
		if (typeof seEvents=='undefined') {
			return false;
		}
		var formdata = { name: 'addpage' };
		$.ajax({
			url: seEvents.content_url+"?on_ajax_execute",
			type: 'POST',
			data: formdata,
			success: function(data) {
				console.log('ajax success: addpage');
				seEvents.showModal(data, 'addpage', formdata, function(result) {
					seEvents.reloadFrame(result);	
					history.go(0);
				});
				
			}
		});
	},
	editor_editpage: function() {
		if (typeof seEvents=='undefined') {
			return false;
		}
		var formdata = { name: 'editpage' };
		$.ajax({
			url: seEvents.content_url+"?on_ajax_execute",
			type: 'POST',
			data: formdata,
			success: function(data) {
				seEvents.showModal(data, 'editpage', formdata, function(result) {
					seEvents.reloadFrame(result);
					history.go(0);
				});
			}
		});
		
		
	},
	editor_editcontacts: function() {
		if (typeof seEvents=='undefined') {
			return false;
		}
		var formdata = { name: 'editpage_contacts' };
		$.ajax({
			url: seEvents.content_url+"?on_ajax_execute",
			type: 'POST',
			data: formdata,
			success: function(data) {
				console.log('ajax success: editpage_contacts');
				seEvents.showModal(data, 'jqueryform=sitecontacts', formdata);
			}
		});
	},
	editor_save: function() {
		if (typeof seEvents=='undefined') {
			return false;
		}
		var formdata = { name: "pagesave"};
		var getdata = {
			confirmation: 'save',
			subject: 'page',
			status: 'success'
		}
		$.ajax({
			url: seEvents.content_url,
			type: 'GET',
			data: getdata,
			success: function(data) {
				seEvents.showModal(data, 'on_ajax_execute', formdata, function(result) {
					if (result.substr(0,5)!=='error') {
						seEvents.reloadFrame(result);
					} else {
						alert(result.substr(5));
					}
				});
			}
		});
	},
	editor_cancel: function() {
		if (typeof seEvents=='undefined') {
			return false;
		}
		var formdata = { name: "pagecancel"};
		var getdata = {
			confirmation: 'cancel',
			subject: 'page',
			status: 'danger'
		}
		$.ajax({
			url: seEvents.content_url,
			type: 'GET',
			data: getdata,
			success: function(data) {
				seEvents.showModal(data, 'on_ajax_execute', formdata, function(result) {
					if (result.substr(0,5)!=='error') {
						seEvents.reloadFrame(result);
					} else {
						alert(result.substr(5));
					}
				});
			}
		});
	},
	editor_switch: function(id, trigger) {
		if (typeof seEvents=='undefined') {
			return false;
		}
		$.ajax({
			url: seEvents.url,
			type: 'GET',
			data: { editor_on_off: true },
			success: function(data) {
				var img = (data=='1') ? trigger.attr('data-on') : trigger.attr('data-off');
				console.log(img);
				trigger.find('img').attr('src',img);
				seEvents.reloadFrame(null, true);
			}
		});
	},
	page_select: function(id) {
		if (typeof seEvents=='undefined') {
			return false;
		}
		seEvents.content_url = id;
		seEvents.reloadFrame(id, true);
	}
	
}
