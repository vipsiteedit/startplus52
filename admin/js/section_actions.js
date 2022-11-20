seEvents.actions.section = {
	frame_add: function(id, trigger) {
		if (typeof seEvents=='undefined') {
			return false;
		}
		if (seEvents.block == true){
		    return false;
		}
		seEvents.block = true;
		var formdata = {name: 'addsection', value: ""+id+""};
		$.ajax({
			url: seEvents.url+"?on_ajax_execute",
			type: 'POST',
			data: formdata,
			success: function(data) {
				seEvents.showModal(data, 'jqueryform=partedit', formdata, function(result) {
					/*var contstr = (id>=100) ? 'global' : 'content';
					var container = seEvents.viewer.contents().find('[data-wrap='+contstr+id+']');
					var btns = container.children().first().detach();
					container.html(result);
					container.prepend(btns);*/
					seEvents._setHovers();
					trigger.trigger('change');

/*
					var tempDiv = $('<div>').html(result);
				    var raw = $('<div>').html(result);
				    $(tempDiv).find("script").remove();
					// console.log(result);
					container.append(result);
					seEvents._setHovers();*/
				});
			}
		});
	},
	frame_edit: function(id, trigger) {
		if (typeof seEvents=='undefined') {
			return false;
		}
// Добавлено
		if (seEvents.block == true  && $(trigger).data('target')!='page'){
			return false;
		}
		seEvents.block = true;

		var pagename = seEvents.getPageName() || "home";

		var formdata = {name: 'editsection', page: pagename, value: ""+id+""};
		//alert(seEvents.content_url);
		$.ajax({
			url: seEvents.url+"?on_ajax_execute",
			type: 'POST',
			data: formdata,
			success: function(data) {
				seEvents.showModal(data, 'jqueryform=partedit', formdata, function(result) {
					var target = trigger.data('target');
					seEvents._setHovers();
					if (target == 'frame'){
						//alert(seEvents.content_url);
						seEvents.reloadFrame(seEvents.content_url, true);
					} else {
						trigger.trigger('change');
					}
					/*var d = jQuery.parseJSON(result);
					console.log(data);
					var container = seEvents.viewer.contents().find('[data-content='+id+']');
					container.remove();

					var btns = container.children().first().detach();
					container.html(result);
					container.prepend(btns);*/
					/*



					var btns = container.children().first().detach();
					var tempDiv = $('<div>').html(result);
				    var raw = $('<div>').html(result);
				    $(tempDiv).find("script").remove();
					container.html(tempDiv.html());
					container.prepend(btns);
					seEvents._setHovers();*/
				}, function(modal){
						$('.simple-sortable', modal).sortable({
							forcePlaceholderSize: true,
							helper:	'clone',
							items: 'tr',
							opacity: .6,
							placeholder: 'placeholder',
							create: function() {
								var _this = $(this);
								_this.attr('data-order',_this.sortable('serialize',{ attribute: 'data-id' }));
							},
							stop: function() {
								var _this = $(this);
								_this.attr('data-order',_this.sortable('serialize',{ attribute: 'data-id' }));
							}
						});


				}, function(formdata, modal) {
					parse_str($('[data-order]', modal).attr('data-order'),formdata);
					return formdata;
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
			subject: 'section',
			subject_id: id,
			status: 'danger'
		}
		//console.log(getdata);

		$.ajax({
			url: seEvents.url,
			type: 'GET',
			data: getdata,
			beforeSend: function() {

			},
			success: function(data) {
				seEvents.showModal(data, 'on_ajax_execute&jqueryform=partremove', formdata, function(result) {
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

			}
		});
	},
	frame_position: function(id, trigger) {
		if (typeof seEvents=='undefined') {
			return false;
		}
		var formdata = {name: 'repossection', value: ""+id+""};
		$.ajax({
			url: seEvents.url+"?on_ajax_execute",
			type: 'POST',
			data: formdata,
			success: function(data) {
				seEvents.showModal(data, 'jqueryform=partposition', formdata, function(result) {
					var scope = (id>=100) ? 'global' : 'content';
					var container = seEvents.viewer.contents().find('[data-wrap='+scope+id+']');
					var btns = container.children().first().detach();
					var tempDiv = $('<div>').html(result);
				    var raw = $('<div>').html(result);
				    $(tempDiv).find("script").remove();
					container.html(tempDiv.html());
					container.prepend(btns);
					seEvents._setHovers();
				}, function(modal){
					$('.simple-sortable', modal).sortable({
						forcePlaceholderSize: true,
						helper:	'clone',
						items: '.grid-item',
						opacity: .6,
						placeholder: 'placeholder'
					});
				}, function(formdata, modal) {
					formdata.sectionorder = $('.simple-sortable', modal).sortable('toArray',{attribute: 'data-id'});
					return formdata;
				});
			}
		});
	},
	type_select: function(val){
		var formdata = {name: 'partparam', value: ""+val+""};
		$.ajax({
			url: seEvents.url + "?on_ajax_execute",
			type: 'POST',
			data: formdata,
			success: function (data) {
				//alert(data);
				$('.parametrs').html(data);
			}
		});
	}
};