seEvents.actions.record = {
	frame_add: function(id, trigger) {
		if (typeof seEvents=='undefined') {
			return false;
		}
		if (seEvents.block == true  && $(trigger).data('target')!='section'){
		    return false;
		}
		seEvents.block = true;
		var formdata = {name: 'addrecord', value: ""+id+""};
		$.ajax({
			url: seEvents.url+"?on_ajax_execute",
			type: 'POST',
			data: formdata,
			beforeSend: function() {
				console.log('ajax send: addrecord');
			},
			success: function(data) {
				//console.log('ajax success: addrecord');
				seEvents.showModal(data, 'jqueryform=recordedit', formdata, function(result) {
					//console.log("result:");
					//console.log(result);
					var target = trigger.data('target');
					seEvents._setHovers();
					if (target == 'frame') {
						seEvents.reloadFrame(seEvents.content_url, true);
					} else {
						trigger.trigger('change');
					}
				});
			}
		});
	},
	frame_edit: function(id, trigger) {
		if (typeof seEvents=='undefined') {
			return false;
		}
		if (seEvents.block == true  && $(trigger).data('target')!='section'){
		    return false;
		}
		seEvents.block = true;
		var formdata = {name: 'editrecord', value: ""+id+""};
		$.ajax({
			url: seEvents.url+"?on_ajax_execute",
			type: 'POST',
			data: formdata,
			beforeSend: function() {
				console.log('ajax send: editrecord');
				console.log(seEvents.content_url+"?on_ajax_execute");
			},
			success: function(data) {
				console.log('ajax success: editrecord');
				seEvents.showModal(data, 'jqueryform=recordedit', formdata, function(result) {
					var target = trigger.data('target');
					seEvents._setHovers();
					if (target == 'frame') {
						seEvents.reloadFrame(seEvents.content_url, true);
					} else {
						trigger.trigger('change');
					}
				});
			}
		});
	},
	frame_remove: function(id, trigger) {
		if (typeof seEvents=='undefined') {
			return false;
		}
		var formdata = {value: ""+id+""};
		var getdata = {
			confirmation: 'delete',
			subject: 'record',
			subject_id: id,
			status: 'danger'
		};
		$.ajax({
			url: seEvents.url,
			type: 'GET',
			data: getdata,
			beforeSend: function() {
				//console.log('request confirmation');
			},
			success: function(data) {
				seEvents.showModal(data, 'on_ajax_execute&jqueryform=recordremove', formdata, function(result) {
					var target = trigger.data('target');
					seEvents._setHovers();
					if (target == 'frame') {
						seEvents.reloadFrame(seEvents.content_url, true);
					} else {
						trigger.trigger('change');
					}
				});
			},
			complete: function(j,t) {
				//console.log(t);
			}
		});
	}
};