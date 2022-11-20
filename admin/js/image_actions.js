seEvents.actions.image = {
	
	image_select: function(id) {
		if (typeof seEvents=='undefined') {
			return false;
		}
		var formdata = {
			filemanager: 'getframe',
			type: 1,
			descending: false,
			lang: seEvents.lang
		}
		if (typeof id!=='undefined' && id.length>0) {
			formdata.field_id = id;
		}
		$.ajax({
			url: seEvents.content_url,
			type: 'GET',
			data: formdata,
			success: function(data) {
				seEvents.showModal(data);
			}
		});
	}
}