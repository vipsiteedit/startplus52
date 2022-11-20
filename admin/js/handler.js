var seEvents;
seEvents = {
    url: '/admin.php',
    content_url: '/',
    pagename: '',
    se_end: '/',
    lang: 'ru',
    actions: {},
    modals: [],
    block: false,
    state: false,
    showModalEdit: function () {
    },
    showConfirm: function (message_id, formdata, successCallback) {

    },
    showModalImage: function (successCallback) {

    },
    _setEditor: function (element) {
        //console.log("run: _setEditor");
        var that = this;
        element.tinymce({
            height: ((rows = parseInt($(this).attr('rows'))) > 0) ? rows * 15 : 300,
            mode: "exact",
            document_base_url: "/",
            content_css: "/system/main/editor/tiny.css",
            safari_warning: false,
            remove_script_host: false,
            convert_urls: false,
            theme: "modern",
            forced_root_block: false,
            menubar: false,
            browser_spellcheck: true,
            language: that.lang,
            convert_fonts_to_spans: true,
            toolbar: "undo redo pastetext | bold italic underline | forecolor backcolor | alignleft aligncenter alignright alignjustify | " +
            " bullist numlist outdent indent | image link unlink media | table blockquote | removeformat fullscreen code",
            plugins: "link image paste code fullscreen media table textcolor",
            link_list: "/?getpagelist",
            image_advtab: true,
            external_plugins: {"filemanager": "/admin/filemanager/plugin.js"},
            external_filemanager_path: that.url
        });
    },
    showModal: function (html, get, formdata, successCallback, startCallback, prepareCallback) {
        //console.log("run: showModal");
        var that = this;
        if (this.modal.html().length > 0) {
            this.modals.push(this.modal);
            this.modal.after('<div/>');
            this.modal = this.modal.hide().next();
            this._initModal(this.modal);
            this.modal.attr('id', 'modal' + (Math.random() * 0xFFFFFF << 0).toString(16));
        }
        var modal_id = this.modal.attr('id');
        window.modal_id = modal_id;
        this.modal.html(html);
        if (typeof startCallback !== 'undefined') {
            startCallback(this.modal);
        }
        this.modal.find('[data-texteditor]').tinymce({
            height: ((rows = parseInt($(this).attr('rows'))) > 0) ? rows * 15 : 300,
            mode: "exact",
            document_base_url: "/",
            content_css: "/system/main/editor/tiny.css",
            safari_warning: false,
            remove_script_host: false,
            convert_urls: false,
            theme: "modern",
            forced_root_block: false,
            menubar: false,
            browser_spellcheck: true,
            language: that.lang,
            convert_fonts_to_spans: true,
            toolbar: "undo redo pastetext | bold italic underline | forecolor backcolor | alignleft aligncenter alignright alignjustify | " +
            " bullist numlist outdent indent | image link unlink media | table blockquote | removeformat fullscreen code",
            plugins: "link image paste code fullscreen media table textcolor",
            link_list: "/?getpagelist",
            image_advtab: true,
            external_plugins: {"filemanager": "/admin/filemanager/plugin.js"},
            external_filemanager_path: that.url
        });
        this.modal.on('hidden.bs.modal', function (e) {
            that.modal.find('[data-texteditor]').tinymce().remove();
            that.modal.html('');
            if (that.modals.length > 0) {
                that.modal.remove();
                that.modal = that.modals.pop();
                that.modal.show();
            }
            that.block = false;
        });
        /*this.modal.find('#modalcancel').click(function(e) {
         e.preventDefault();
         that.modal.modal('hide');
         });*/
        this.modal.find('[data-action=save]').click(function (e) {
            if (($("[name='namepage']").val() == '') && ($("[name='title']").val() != '')) {
                var new_adress = translite($("[name='title']").val());
                $("[name='namepage']").val(new_adress);
            }
            $(this).button('loading');
            if (typeof formdata !== 'undefined') {
                $.each(that.modal.find('form').first().serializeArray(), function () {
                    formdata[this.name] = this.value;
                });
            }
            if (typeof prepareCallback !== 'undefined') {
                formdata = prepareCallback(formdata, that.modal);
            }
            //console.log(formdata);
            if ((typeof get !== 'undefined') || (typeof formdata !== 'undefined')) {
                $.ajax({
                    url: that.url + '?' + get,
                    data: formdata,
                    type: 'POST',
                    //dataType: 'json',
                    complete: function (j, t) {
                        //console.log(t);
                    },
                    success: function (data) {
                        //console.log(data);
                        if (typeof successCallback !== 'undefined') {
                            successCallback(data);
                        }
                        that.modal.modal('hide');
                    }
                });
            }
        });
        this.modal.modal('show');
    },
    goEvent: function (event, subject, id, trigger) {
        //console.log("run: goEvent");
        var that = this;
        if (typeof this.actions[subject] !== 'undefined') {
            action = this.actions[subject][event];

            if (typeof action !== 'undefined') {
                //console.log(trigger);
                action(id, trigger);
            }
            else {
                console.log('Undefined event: ' + event);
            }
        } else {
            console.log('Undefined subject: ' + subject);
        }
    },
    init: function (options) {
        console.log('initialization started');
        var that = this;
        this.unique = 'test';
        if (typeof options.lang !== 'undefined') {
            this.lang = options.lang;
        }
        if (typeof options.console !== 'undefined') {
            this.console = this._setConsole($(options.console));
        }
        if (typeof options.loader !== 'undefined') {
            this.loader = $(options.loader);
        }
        if (typeof options.url !== 'undefined') {
            this.url = options.url;
        }
        if (typeof options.content_url !== 'undefined') {
            this.content_url = options.content_url;
        }
        if (typeof options.pagename !== 'undefined') {
            this.pagename = options.pagename;
        }
        if (typeof options.se_dir !== 'undefined') {
            this.se_dir = options.se_dir;
        }
        if (typeof options.se_end !== 'undefined') {
            this.se_end = options.se_end;
        }
        this.loader.click(function (e) {
            e.preventDefault();
            return false;
        });
        this.viewer = $(options.viewer);
        this._setEvents();
        this._setHovers();
        this.modal = $(options.modal);
        this._initModal(this.modal);
        $(document).find('body').tooltip({selector: '[data-toggle="tooltip"]'});
        setTimeout(function () {
            seEvents.editorState("enabled");
        }, 100);
    },
    editorState: function (state, loader) {
        //console.log("run: editorState");
        //console.log("state:", state);
        //console.log("loader:", loader);
        //console.log("this.state:", this.state);
        if (typeof loader == 'undefined') {
            loader = true;
        }
        if (state == 'enabled' && this.state == false) {
            if (loader) {
                this.loader.fadeOut();
            }
            $(document).find('[disabled]').removeAttr('disabled').attr('data-enabled', 'enabled');
            this.state = true;
        } else if (this.state == true) {
            if (loader) {
                this.loader.fadeIn();
            }
            $(document).find('[data-enabled]').removeAttr('data-enabled').attr('disabled', 'disabled');
            this.state = false;
        }
    },
    reloadFrame: function (data, url) {
        //console.log("run: reloadFrame", seEvents.content_url);
        //console.log("data:", data);
        //console.log("url:", url);
        if (typeof url == 'undefined') {
            url = false;
        }

        var that = this;

        this.editorState('disabled');

        var pagename = this.getPageName(data) || 'home';

        $.get(that.url + '?jqueryform=pagename&name=' + pagename);

        seEvents.editorState('disabled');
        var iframe = document.createElement('iframe');
        var wrapper = document.getElementById('editor_frame_window');
        var date = Date.now();
        iframe.src = data + "?" + date;
        iframe.width = "100%";
        iframe.height = "100%";
        $(iframe).attr("name", "content");
        $(iframe).attr("frameborder", 0);
        wrapper.innerHTML = '';
        wrapper.appendChild(iframe);
        iframe.contentWindow.location.href = data + "?" + date;

        this.viewer = $('#editor_frame_window').find('iframe');

        $("#editor_frame_window iframe").unbind("load");
        $("#editor_frame_window iframe").load(function () {
            seEvents.editorState('disabled');
            $.get("admin.php?getpageslist&" + Date.now(), function (data) {
                $('[data-event-change=page_select]').html(data);
                var select = $('[data-event-change=page_select]');
                $(select[0]).val(pagename);
                seEvents._setEvents("frame");
                setTimeout(function () {
                    seEvents.editorState("enabled");
                    seEvents._setHovers();
                }, 100);
            });
        });

        //this.viewer[0].src = data;

        //if (url == false && data != null && typeof data != 'undefined') {
        //	this.editorState('disabled');
        //	this.viewer[0].contentWindow.document.open();
        //	this.viewer[0].contentWindow.document.write(data);
        //	this._setEvents('frame');
        //	this._setHovers();
        //	setTimeout(function () {
        //		seEvents.editorState("enabled");
        //	}, 100);
        //} else if (url == true) {
        //	if (data == null || typeof data == 'undefined') {
        //		data = this.content_url;
        //	}
        //	var that = this;
        //	//var url = '';
        //	this.editorState('disabled');
        //	/*
        //	 if (data == 'home') {
        //	 url = this.se_dir + '/';
        //	 } else */
        //
        //	var pagename = '', pn = data.split('/');
        //	if (this.se_dir == '')
        //		pagename = pn[1];
        //	else
        //		pagename = pn[2];
        //
        //	if (pagename == '') pagename = 'home';
        //	$.get(data, function (html) {
        //		//alert(html);
        //		$.get(that.url + '?jqueryform=pagename&name=' + pagename);
        //		var select = $('[data-event-change="page_select"] option');
        //		select.removeAttr('selected');
        //		for (var i = 0; i < select.length; i++) {
        //			if ($(select[i]).val() == pagename) {
        //				$(select[i]).attr("selected", "selected");
        //				break;
        //			}
        //		}
        //		console.log(that.viewer[0]);
        //		that.viewer[0].contentWindow.document.open();
        //		that.viewer[0].contentWindow.document.write(html);
        //		that._setEvents("frame");
        //		setTimeout(function () {
        //			seEvents.editorState("enabled");
        //			seEvents._setHovers();
        //		}, 100);
        //	})
        //}
    },
    _initModal: function (modal) {
        //console.log("run: _initModal");
        modal.addClass('modal').addClass('fade').modal({show: false});
    },
    _setHovers: function () {
        //console.log("run: _setHovers");
        var body = this.viewer.contents().find('body');
        this.viewer.contents().find('[data-toolbar]').each(function () {
            //var type = $(this).attr('data-toolbar');
            var obj = $(this).parent();
            var type = $(this).attr('data-toolbar');
            var pos;
            if (type == 'top') {
                pos = {my: 'center bottom', at: 'center top'};
            } else if (type == 'bottom') {
                pos = {my: 'center top', at: 'center bottom'};
            } else if (type == 'left') {
                pos = {my: 'right top', at: 'left+10 top'}
            } else if (type == 'right') {
                pos = {my: 'left top', at: 'right-10 top'}
            } else {
                pos = {my: 'center center', at: 'center center'}
            }
            pos.of = obj;
            pos.collision = 'fit';
            pos.within = body;
            $(this)
                .css({
                    'position': 'absolute',
                    'display': 'block',
                    'z-index': '2500'
                })
                .position(pos)
                .unbind('mouseenter mouseleave')
                .hover(function () {
                        var parent = $(this).parent();
                        parent.attr('data-border', parent.css('border'));
                        parent.css({'border': '1px dashed #f00', 'min-height': '30px'});
                    },
                    function () {
                        var parent = $(this).parent();
                        parent.css('border', parent.attr('data-border'));
                    });
        });
    },
    _importScripts: function () {

    },
    containerPanel: function (name) {
        var subject = 'section', color = '#f0ad4e', id = name;
        var title = 'Добавить раздел страницы';

        if (name.indexOf('global', 0) >= 0) {
            id = id.replace('global', '');
            color = '#ff0000';
            title = 'Добавить глобальный раздел';
        } else {
            id = id.replace('content', '');
        }
        var tools = '<div class="tools-add" ' +
            'style="display:none;padding-top:1px;padding-left:9px;margin-bottom:2px;height:28px;width:60px;background-color:' + color + ';border-radius:0 0 5px 0;z-index:9999;">';
        tools = tools + '<img class="btn btn-xs glyphicon" data-event="frame_add" data-subject="section" data-id="' + id + '" data-target="frame" style="padding:0"';
        tools = tools + ' src="/admin/assets/icons/16x16/add_content.png?1" title="' + title + '">';
        tools = tools + '<img class="btn btn-xs glyphicon" data-event="frame_help" data-subject="help" data-id="add_section" style="padding:0 10 0 0"';
        tools = tools + ' src="/admin/assets/icons/16x16/se_help.png?1" title="Помощь"></div>';
        return tools;
    },
    sectionPanel: function ($this) {
        var tools = '';
        var $contlist = $this.find('.content');
        //console.log("$contlist:", $contlist);
        for (var j = 0; j < $contlist.length; j++) {
            id = $($contlist[j]).attr('data-id');
            tools = '<img class="btn btn-xs glyphicon" src="/admin/assets/icons/16x16/ed_content.png?1" ' +
                'data-event="frame_edit" ' +
                'data-subject="section" ' +
                'data-target="frame" data-id="' + id + '" title="Изменить раздел"' +
                'style="padding:0">';
            if ($($contlist[j]).attr('data-records') == 'true') {
                tools = tools + '<img class="btn btn-xs glyphicon" src="/admin/assets/icons/16x16/add_content.png" ' +
                    'title="Добавить запись"' +
                    'data-event="frame_add" data-subject="record" ' +
                    'data-target="frame" data-id="' + id + '" style="padding:0 12 0 0">';
            }
            $($contlist[j]).prepend('<div class="tools" style="display:none;">' + tools + '</div>');
        }
        $contlist.hover(function (e) {
            e.preventDefault();
            var id = $(this).attr('data-id');
            var target = $(this).attr('data-target');
            $(this).css({
                'outline': 'solid 1px green',
                'min-height': '30px'
            });


            $(this).find('.tools').css({
                'display': 'block',
                'padding-top': '1px',
                'padding-left': '9px',
                'margin-bottom': '2px',
                'height': '30px',
                'position': 'absolute',
                'width': '60px',
                'background-color': 'green',
                'border-radius': '0 0 5px 0',
                'zIndex': '9999'
            });
        }, function () {
            $(this).css('outline', 'none');
            $(this).find('.tools').css('display', 'none');
        });

    },
    tools: function (scope) {
        //console.log("run: tools");
        var that = this;
        var $containerlist = $('[data-wrap]', scope);
        var $varlist = $('[data-editvar]', scope);
        var tools = '', id = 0, name = '';
        var $this = null;

        for (var i = 0; i < $containerlist.length; i++) {
            $this = $($containerlist[i]);
            name = $($this).attr('data-wrap');
            tools = that.containerPanel(name);
            $this.prepend(tools);
            that.sectionPanel($this);
        }
        // отображаем блоки редактирования
        $containerlist.hover(function (e) {
            var name = $(this).attr('data-wrap');
            e.preventDefault();
            $(this).css({
                'outline': 'dotted 1px #f0ad4e',
                'min-height': '30px'
            });
            $(this).find('.tools-add').css('display', 'block');
        }, function () {
            $(this).css('outline', 'none');
            $(this).find('.tools-add').css('display', 'none');
        });

        for (var i = 0; i < $varlist.length; i++) {
            name = $($varlist[i]).attr('data-editvar');
            tools = '<img class="btn btn-xs glyphicon" src="/admin/assets/icons/16x16/ed_content.png" ' +
                'title="Изменить переменную"' +
                'data-event="edit_var" data-subject="vars" ' +
                'data-target="frame" data-id="' + name + '">';
            $($varlist[i]).append(tools);
        }
    },
    _setEvents: function (scope) {
        //console.log("run: _setEvents");
        //console.log("scope:", scope);
        var that = this;
        var setlinks = true;

        if (scope == 'frame') {
            scope = this.viewer.contents();
        } else if (scope == 'document') {
            scope = $(document);
            setlinks = false;
        } else if (typeof scope == 'undefined') {
            scope = $(document).add(this.viewer.contents());
        } else {
            scope = $(scope);
        }
        that.tools(scope);


        scope.off('change', '[data-event-change]');
        scope.on('change', '[data-event-change]', function (e) {
            e.preventDefault();
            var name = $(this).attr('data-event-change');
            var subject = $(this).attr('data-subject');
            var target = $(this).attr('data-target');

            if (typeof subject === 'undefined' && subject === false) {
                subject = null;
            }
            var id = $(this).val();
            if (typeof id === 'undefined' && id === false) {
                id = null;
            }

            that.goEvent(name, subject, id, $(this), target);
        });
        scope.off('dblclick', '[data-event-dbl]');
        scope.on('dblclick', '[data-event-dbl]', function (e) {
            e.preventDefault();
            var name = $(this).attr('data-event-dbl');
            var subject = $(this).attr('data-subject');
            var target = $(this).attr('data-target');
            if (typeof subject === 'undefined' && subject === false) {
                subject = null;
            }
            var id = $(this).attr('data-id');
            if (typeof id === 'undefined' && id === false) {
                id = null;
            }

            that.goEvent(name, subject, id, $(this), target);
        });
        scope.off('click', '[data-event]');
        scope.on('click', '[data-event]', function (e) {
            e.preventDefault();
            var name = $(this).attr('data-event');
            var subject = $(this).attr('data-subject');
            var target = $(this).attr('data-target');
            if (typeof subject === 'undefined' && subject === false) {
                subject = null;
            }
            var id = $(this).attr('data-id');
            if (typeof id === 'undefined' && id === false) {
                id = null;
            }
            //alert(name+ ' '+id+' '+subject);

            that.goEvent(name, subject, id, $(this), target);
        });

        if (setlinks) {
            $('a', this.viewer.contents()).attr('target', '_blank');
            $('a:regex(href,^' + that.se_dir.split('/').join('\\/') + '((.+?)\/)*)', this.viewer.contents()).click(function (e) {
                e.preventDefault();
                var href = $(this).attr('href');
                //console.log("href:", href);
                var pagename = seEvents.getPageName(href);
                //console.log("pagename", pagename);
                $.get("admin.php?getpages", function (data) {
                    var pages = JSON.parse(data);
                    if (pages.indexOf(pagename) != -1) {
                        that.content_url = href;
                        window.history.pushState({link: href}, document.title, "admin.php?" + Date.now() + "&page=" + href);
                        that.reloadFrame(href, true);
                    } else {
                        //window.open(href,'_blank');
                    }
                });
            });
        }
    },
    _setOrderFunctions: function () {
        var content = this.viewer.contents();
        var that = this;
        /*content.find('.modules_collection .modules').draggable({
         helper:'clone',
         cursorAt: { left: 5 }
         });
         content.find('.groupWrapper,.ggroupWrapper').droppable({
         hoverClass: 'wrappHover',
         drop: function(event, ui) {
         //console.dir(ui.draggable[0].id);
         var $indrop = $(this);
         var objlt = ui.draggable[0];
         var title = $(objlt).find('.editorbtn').attr("title");
         var cnt_id = this.id; cnt_id = cnt_id.split('sort').join('');
         var str = objlt.id;

         if (str.indexOf("mdl_")!=-1){
         str = str.split('mdl_').join('');
         $.ajax({
         url: that.content_url+"?jqueryform=partedit",
         type: 'POST',
         data: { contentid: cnt_id, contenttype: ""+str+"", parttitle: ""+title+""},
         success: function(data){
         $indrop.append(data);
         activationEnentSectIcon();
         activationEventEdPart();
         startEvents();
         }
         });
         }
         }
         });

         content.find('div.groupWrapper').sortable({
         connectWith: "div.groupWrapper",
         handle: "div.group-header",
         items: "div.groupItem",
         stop: function(event, ui){
         processStop(this);
         }
         }).disableSelection();

         content.find('.recordsWrapper').sortable({
         connectWith: ".recordsWrapper",
         handle: ".itemRecordHeader",
         items: ".object",
         stop: function(event, ui){
         processStopRec(this.id);
         }
         }).disableSelection();

         content.find('.groupItem').addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
         .find( ".group-header" )
         .addClass( "ui-widget-header ui-corner-all" )
         .prepend( "<span class='ui-icon ui-icon-minusthick'></span>")
         .end()
         .find( ".group-content" );*/
    },
    _setConsole: function (element) {
        //console.log("run: _setConsole");
        var that = this;
        $(document).find('head').append(
            $('<link/>').attr('rel', 'stylesheet').attr('type', 'text/css').attr('href', '/admin/assets/skin/console.css')
        );
        element.html('');
        var cons = $('<div/>').attr('id', 'console' + that._unique).addClass('editor_console').html('test');
        element.append(
            cons
        );
        return cons;
    },
    imagerender: function (image, trigger) {
        var response_img = trigger.data('response-img'),
            timeNow = Date.now();

        var form = trigger.data('form-id');
        var modal = $(trigger.data('modal-id'));

        $(form).addClass('hidden');

        $(response_img).attr('src', image + "?" + timeNow);
        $(response_img).html('<img class="img-responsive center-block" src="/' + image + "?" + timeNow + '">');
        $(modal).find("img.img-responsive").load(function () {
            var w = this.naturalWidth, h = this.naturalHeight;
            $(modal).find('.img-responsive-width').val(w);
            $(modal).find("[name=img-width]").attr("data-value", w);
            $(modal).find('.img-responsive-width').data('width', w);
            $(modal).find('.img-responsive-height').val(h);
            $(modal).find("[name=img-height]").attr("data-value", h);
            $(modal).find('.img-responsive-height').data('height', h);

            $(this).Jcrop({
                boxWidth: 400
            });

            //$("[type=range]").val(krange);
            //$("#priorityval").html(krange);

            $(modal).off('cropmove cropend', this);
            $(modal).on('cropmove cropend', this, function (e, s, c) {
                $(form).removeClass('hidden');
                $(form + 'input[name=x1]').val(parseInt(c.x));
                $(form + 'input[name=y1]').val(parseInt(c.y));
                $(form + 'input[name=x2]').val(parseInt(c.w + c.x));
                $(form + 'input[name=y2]').val(parseInt(c.h + c.y));
                $(form + 'input[name=w]').val(parseInt(c.w));
                $(form + 'input[name=h]').val(parseInt(c.h));
            });
        });
    },
    getPageName: function(url) {
        url = url || seEvents.content_url;
        if (url == '/') {
            return 'home';
        }
        var regex = /\/(.*)(\.html$)/;
        var match = url.match(regex);
        if (match != null && match[2] == ".html") {
            return match[1];
        }
        var pn = url.split('/');
        return (seEvents.se_dir == '') ? pn[1] : pn[2];
    }

};
