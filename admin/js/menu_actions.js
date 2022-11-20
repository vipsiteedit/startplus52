seEvents.actions.menu = {
	
	editor_menu: function(id) {
		if (typeof seEvents=='undefined') {
			return false;
		}
		var formdata = {name: 'editpage_menu', value: ""+id+"" };
		var that = this;

		$.ajax({
			url: seEvents.content_url+"?on_ajax_execute",
			type: 'POST',
			data: formdata,
			success: function(data) {
				seEvents.showModal(data, 'on_ajax_execute&sortablepagemenu', formdata, function(result) {
					result = result.split('>>!!<<');
					seEvents.viewer.contents().find('[data-menu=pagemenu]').replaceWith(result[0]);
					seEvents.viewer.contents().find('[data-menu=mainmenu]').replaceWith(result[1]);
					seEvents._setHovers();
				}, seEvents.actions.menu._initEditor,
				function (formdata, modal) {
					formdata.mainmenu = $('.simple-sortable', modal).sortable('toArray',{attribute: 'data-name'});
					formdata.pagemenu = $('.nested-sortable', modal).nestedSortable('toHierarchy',{attribute: 'data-name'});
					//console.log(formdata.mainmenu,formdata.pagemenu);
					return formdata;					
				});
			}
		});
		
		
	},
	_initEditor: function(modal) {
		
		function findGM(event) {
			//event.preventDefault();
			if (((event.type == "keydown") && (event.keyCode == 13)) || (event.type == "click")) {
				sString = $(this).parent().find("input[type='text']").val();
				if (sString == '') {
					$(this).parents(".menuListDMM").find("[data-name]").removeClass('hidden');
					return false;
				};
				$(this).parents('[data-type="pageMenuList"]').find("[data-name]").removeClass('hidden').each(function(i){
					var obj = $(this);
					if(obj.text().toLowerCase().search(sString.toLowerCase())!= -1) {
						var fitems = $(this).find('[data-name]');
						for (i = 0; i < fitems.length; i++){
							if ($(fitems[i]).text().toLowerCase().search(sString.toLowerCase())!= -1)
								$(fitems[i]).addClass('hidden');
						}
					};
				});
				/*$(this).parents(".menuListDMM").find("[data-name]").each(function(i){
					if ($(this).text().toLowerCase().search(sString.toLowerCase())!=-1) {
						$(this).parent().animate(
							{
								scrollTop : this.offsetTop - parseInt($(this).css("marginTop"))
							},
							500);
						return false;
					};
				});*/
			}
		}
		modal.on('keydown', '[data-type="searchDMM"] > input[type="text"]', findGM);
		modal.on('click', '[data-type="searchDMM"] > input[type="button"]', findGM);
		
		$('.nested-sortable', modal).nestedSortable({
			forcePlaceholderSize: true,
			helper:	'clone',
			items: 'li',
			opacity: .6,
			placeholder: 'placeholder',
			tabSize: 25,
			isTree: true,
			startCollapsed: false,
			listType: 'ul',
			beforeStop: function (e,ui) {
				var name = $(ui.item).attr('data-name');
				//console.log($(e.target).find('[data-name='+name+']:not(.ui-sortable-helper)'));
				if ($(e.target).find('[data-name='+name+']:not(.ui-sortable-helper)').length>1) {
					$(ui.item).remove();
				} else {
					$(ui.item).find('[data-removeitem]').removeClass('hidden');
				}
			}
		});
		
		$('.nested-sortable', modal).sortable({
			forcePlaceholderSize: true,
			helper:	'clone',
			items: 'li',
			opacity: .6,
			placeholder: 'placeholder',
			beforeStop: function (e,ui) {
				var name = $(ui.item).attr('data-name');
				if ($(e.target).find('[data-name='+name+']:not(.ui-sortable-helper)').length>1) {
					$(ui.item).remove();
				} else {
					$(ui.item).find('[data-removeitem]').removeClass('hidden');
				}
			}
		});
		
		$('.simple-sortable', modal).sortable({
			forcePlaceholderSize: true,
			helper:	'clone',
			items: 'li',
			opacity: .6,
			placeholder: 'placeholder',
			beforeStop: function (e,ui) {
				var name = $(ui.item).attr('data-name');
				if ($(e.target).find('[data-name='+name+']:not(.ui-sortable-helper)').length>1) {
					$(ui.item).remove();
				} else {
					$(ui.item).find('[data-removeitem]').removeClass('hidden');
				}
			}
		});
		$('.draggable li', modal).draggable({
			connectToSortable: ".nested-sortable, .simple-sortable",
			forcePlaceholderSize: true,
			helper:	'clone',
			opacity: .6,
			placeholder: 'placeholder'
		});
		modal.on('click','[data-removeitem]', function(e){
			e.preventDefault();
			var item = $(this).parents('li').first();
			if (item.has('ul')) {
				item.after(item.find('ul').children('li').detach());
			}
			item.remove();
		})
		/*function createSortableMenu(index) {
			var m = 'div.menuWrapper';
			if (index == 0) m = 'div.mainMenuWrapper'; 
				$(m).sortable({
					option:"placeholder",
					connectWith: m+", .groupMenu"
				}).disableSelection();
		}
		
		// Модель меню
		function menu (data) {
			// объект, в котором находится меню
			var parent = $(data.parent);
			// добавление пункта меню
			// object - передается объект, подобный объекту из хранилища points
			this.add = function(object){
				this.points[object.id]=object;
				parent.append(this.getPoint(object));
			};
			// удаление пункта меню
			this.remove = function(object){
				delete this.points[object.id];
				//parent.empty();
				for (var n in this.points) {
					if (this.points[n].child != "undefined"){
						for (var t in this.points[n].child){
							if (this.points[n].child[t] == object.id) this.points[n].child.splice(t,1);
						}
					}
				}
				
				this.create();
			};
			
			this.create = function (){
				parent.empty();
				for (var name in this.points){
					//console.log(this.points[name].parent);
					if(!this.points[name].parent){
					//console.dir($(this.getPoint(this.points[name])));
						parent.append($(this.getPoint(this.points[name])))
					};
				}
			}
			
			// шаблон пункта меню
			var pointTemplate = data.pointTemplate;
			// преобразование шаблона пункта меню в строку html
			this.getPoint = function (object){
				// Делаем слепок с шаблона
				//console.dir(this.points);
				var temp = pointTemplate;
				var points = this.points;
				//var child = "";//pointTemplate;
				// Перебераем свойства объекта и заменяем совпадения с шаблоном
				for (var name in object) {
					// Если в шаблоне присутствует указание на дочерний пункт , то начинаем искать его в объекте и подставлять его код
					if (name=="child") {
						var arr = points[object.id].child;
						var child = "";
						for (var i in arr){
							//console.log(arr[i]);
							child = child + this.getPoint(points[arr[i]]);
						};
						temp = temp.split("[child]").join(child);
						//console.log(child);
					} else {
						temp = temp.split("["+name+"]").join(object[name]);
					}
				};
				
				// Костыли для замещения ненайденных свойств
				temp = temp.split("[child]").join("");
				
				// Возвращаем преобразованную html строку с подставленными из хранилища данными
				return temp;
			};
			
			// все пункты этого меню
			// хранилище данных
			this.points = {
				// home: { id:"", title:"", link:"", parent:"", child:"", select:"" },
			}
			
			this.setPoints = function(){
				// Заполнение хранилища
				//points = this.points;
				var points = {};
				parent.find("label[data-id]").each(function(){
					var ob = getMenuData(this);
					//console.log(ob);
					points[ob.id] = ob;
				});
				this.points = points;
			}
		};
		
		
		// Функция разбирает переданный пункт меню на составляющие и на выходе отдает объект с данными о пункте меню
		function getMenuData(point){
			var id = $(point).attr("data-id");
			var title = $(point).find("i").text();
			var link = $(point).find("em").text();
			function getChild(){
				var a = [];
				$(point).find("+ div label[data-id]").each(function(i){
					if ( getParent(this) == id){
						a[i] = $(this).attr("data-id");
					}
				});
				if (a.length > 0) {return a} else {return false};
			};
			var child = getChild();
			
			function getParent(ob){
				return $($(ob).parents("div.groupMenu")[1]).find("label[data-id]").attr("data-id");
			}
			var parent = getParent(point);
			
			var obj = {};
			if (id) { obj.id = id };
			if (title) { obj.title = title };
			if (link) { obj.link = link };
			if (child) { obj.child = child };
			if (parent) { obj.parent = parent };
			
			return obj;
		}

		// Создание меню и заполнение содержимого
		var menuList = [];
		function createMenu() {
			var parent = "";
			
			//Главное меню
			parent = $("#tabs-1 .edit_menu_pages", modal);
			var mainMenu = new menu({
				pointTemplate: 
					"<div class='groupMenu' id='group_m_[id]'>"
						+ "<label data-id='[id]' class='groupMenuItem'>"
							+ "<input type='checkbox'><div><i> [title] </i><br><em> [link] </em></div>"
						+ "</label>"
						+"</div>",
				parent : parent
			});
			// Заполнение хранилища
			mainMenu.setPoints();
			
			//  Список страниц
			parent = $(".pageMenuList .edit_menu_pages", modal);
			mainMenu.pageList = new menu({
				pointTemplate:"<div class='groupMenu'><label data-id='[id]' class='groupMenuItem'><input type='checkbox'><div><i> [title] </i><br><em> [link] </em></div></label>  </div>",
				parent:parent
			});
			// Заполнение хранилища
			mainMenu.pageList.setPoints();
			
			// Универсльное меню
			parent = $("#tabs-2 .edit_menu_pages", modal);
			var universalMenu = new menu({
				pointTemplate:"<div class='groupMenu' id='group_[id]'><label data-id='[id]' class='groupMenuItem'><input type='checkbox'><div><i> [title] </i><br><em> [link] </em></div></label> <div id='sort_[id]' class='menuWrapper submenu ui-sortable'> [child] </div> </div>",
				parent:parent
			});
			// Заполнение хранилища
			universalMenu.setPoints();
			
			//  Список страниц
			parent = $(".pageMenuList .edit_menu_pages", modal);
			universalMenu.pageList = new menu({
				pointTemplate:"<div class='groupMenu'><label data-id='[id]' class='groupMenuItem'><input type='checkbox'><div><i> [title] </i><br><em> [link] </em></div></label>  </div>",
				parent:parent
			});
			// Заполнение хранилища
			universalMenu.pageList.setPoints();
			
			// Все меню собраны в единый массив для монепуляции через индексы
			menuList = [mainMenu, universalMenu];
			
			// Удаляем совпадения из списка страниц
			// и перестраиваем меню
			for (var i=0; i < menuList.length; i++){
				var obj = menuList[i].pageList.points;
				for (var name in obj){
					if ( menuList[i].points[name] != undefined) { delete obj[name]; }
				}
			}
			
			//console.dir(menuList);
		};
		createMenu();
		
		var tabIndex = 0;
		var activeMenu = $('<div/>');
		var add = $("[data-action='button_add']", modal);
		var remove = $("[data-action='button_remove']", modal);
		var up = $("[data-action='button_up']", modal);
		var down = $("[data-action='button_down']", modal);
		var into = $("[data-action='button_putting']", modal);
		var out = $("[data-action='button_getting']", modal);
		
		modal.on('shown.bs.tab', 'a[data-toggle="tab"]', function(e) {
			var tab = $(e.target);

			menuList[tabIndex].setPoints();

			ui_index = parseInt(tab.attr('href').split('#tabs-').join(''))-1;
			var tabcontent = $(tab.attr('href'), modal);
			menuList[ui_index].create();
			menuList[ui_index].pageList.create();
			tabIndex = ui_index;

			for (var i = 0; i < 2; i++){
				createSortableMenu(i);
			}
			
			activeMenu = tabcontent.find('.sortbase');
			
			$([add, remove, up, down, into, out]).addClass('noActive');
			
			if (activeMenu.find('.submenu').length > 0) {
				into.removeClass('hidden');
				out.removeClass('hidden');
			} else {
				into.addClass('hidden');
				out.addClass('hidden');
			};
		});
		// Запускаем функции управления пунктами меню, такими как перемещение пунктов вверх/вниз/вглубь

		// Добавить возможность смещать все меню даже если верхний из них уже на самом верху
		up.on("click", function(e) {
			e.preventDefault();
			var selected = activeMenu.find(".groupMenuItem[selected]");
			// Если мы уперлиысь в потолок уровня то нужно перейти на уровень выше
			function setTarget(s,i){
				i = (typeof i == "undefined") ? 0 : i;
				var t = $(s[i]).prev(':not(.groupMenuItem[selected])')[0];
				if (typeof t == "undefined"){
					t = $(s[i]).parents('.groupMenu')[0];
				}
				return t;
			}
				
			var target = setTarget(selected);
			$(target).before(selected);
			
			// Схлопываем выбранные пункты в случае если выбран самый верхний пункт
			if ((typeof target == "undefined") && (selected.length > 0)){
				$(selected[0]).after(selected.not(selected[0]));
			};
				
		});
			
		// Событие перемещения выбранной группы вниз
		down.on("click", function(e) {
			e.preventDefault();
			console.log('down click');
			var selected = activeMenu.find(".groupMenuItem[selected]");
			console.log(selected);
			// Если мы уперлиысь в потолок уровня то нужно перейти на уровень выше
			function setTarget(s,i){
				i = (typeof i == "undefined") ? 0 : i;		
				var t = $(s[i]).next(':not([selected])')[0];
				if (typeof t == "undefined"){
					t = $(s[s.length-1]).next()[0];
				}
				if (typeof t == "undefined"){
					t = $(s[i]).parents('.groupMenu')[0];
				}
				return t;
			}
			var target = setTarget(selected);
			$(target).after(selected);
			
		});
		*/
		// Событие перемещения выбранной группы вглубь пункта меню
		/*into.on("click",function(e) {
			e.preventDefault();
			
			console.log('into click');
			/*function getSelect(){
			var list = [];
			$(".pageMenuList .groupMenuItem[selected] > label", modal).each(function(i){
				list[i] = getMenuData(this);
			});
			return list;
			};
			
			var select = getSelect();
			var target = menuList[tabIndex]
			var source = menuList[tabIndex].pageList;
			for ( var i=0; i < select.length ; i++){
				target.add(select[i]);
				source.remove(select[i]);
			};
			createSortableMenu(tabIndex);*/
			
		/*	var selected = activeMenu.find(".groupMenuItem[selected]");
			var target = $(selected[0]).prev().find(".submenu")[0];
			$(target).append(selected);
			// Схлопываем выбранные пункты в случае если первым выбран пункт, который не может быть перемещен
			if ((typeof target == "undefined") && (selected.length > 0)){
				$(selected[0]).after(selected.not(selected[0]));
			};
			
		});
			// Событие выведения выбранной группы пунктов меню на внешний уровень
		out.on("click",function(e) {
			e.preventDefault();
			
			/*menuList[tabIndex].setPoints();
			function getSelect(){
				var list = [];
				$("#tabs-menu .siteMenuList:not(:hidden) .groupMenuItem[selected] label[data-id]", modal).each(function(i){
					list[i] = getMenuData(this);
				});
				return list;
			};
			
			var select = getSelect();
			var target = menuList[tabIndex].pageList;
			var source = menuList[tabIndex];
			
			for ( var i=0; i < select.length ; i++){
				target.add(select[i]);
				source.remove(select[i]);
			};
			$('div.menuWrapper', modal).sortable({
				option:"placeholder",
				connectWith: "div.menuWrapper, .groupMenu"
			}).disableSelection();

			$('div.mainMenuWrapper', modal).sortable({
				option:"placeholder",
				connectWith: "div.mainMenuWrapper, .groupMenu"
			}).disableSelection();*/
			
		/*	var selected = activeMenu.find(".groupMenuItem[selected]");
			var target = $(selected[0]).parents(".groupMenu")[0];
			$(target).after(selected);
			// Схлопываем выбранные пункты в случае если выбран пункт в корне и пункты в нем
			if ((typeof target == "undefined") && (selected.length > 0)){
				$(selected[0]).after(selected.not(selected[0]));
			};
		});
		
		modal.on('click','.groupMenuItem, [data-action]', function() {
			if (activeMenu.find('.groupMenuItem[selected]').length > 0) {
				$([remove, up, down, into, out]).removeClass('noActive');
			} else {
				$([remove, up, down, into, out]).addClass('noActive');
			};
			if ($('.pageMenuList .groupMenuItem[selected]', modal).length > 0) {
				add.removeClass('noActive');
			} else {
				add.addClass('noActive');
			}
		});*/
	}
}