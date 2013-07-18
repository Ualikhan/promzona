/*
Title: Front-end site moderation script v 1.2.1 BETA
Author: Derevyanko Mikhail <m-derevyanko@ya.ru>
Date: 27.02.2010

Код не вычесаный, первая рабочая бетка - колбасня
К доработке:
- унифицировать нахер все методы сохранения объектов, разработать систему единого сохранения
- унифицировать нахер все методы вычленения значений полей, разработать систему единого вычленения
- унифицировать нахер все методы редактирования объектов и добавления, это должен быть один метод как-то
- ПОДУМАТЬ над смарт-объектом, копчик подсказывает что сейчас там нехватает параметров
- вычесать архитектуру хранения информации, массивы редактируемого и добавляемого объекта должны быть слиты воедино

+ ЧТО ДЕЛАТЬ С АЖАКС ПРИЛОЖЕНИЯМИ??

+сделано
-к доработке

ЗДЕСЬ МОЖНО ГРАБИТЬ КОРОВАНЫ! ТРАМПАМПАМ!
*/

function feUI(){
	var that = this;
	this.list = [];
	
	this.add = function( div, cfg ){
		var obj = new feObject( div.addClass('fe-smart-menu').css({zIndex:(2000-this.list.length)}), cfg );
		obj.display(div);
		this.list.push( obj );
	}
	
	this.getIframeDocument = function(iframeNode){
		if (iframeNode.contentDocument) return iframeNode.contentDocument;
		else if (iframeNode.contentWindow) return iframeNode.contentWindow.document;
		else return iframeNode.document;
	}
}

function feObject( div, cfg ){
	var that = this;
	this.cfg = cfg;
	if(typeof this.cfg.p == 'undefined') this.cfg.p = {};
	this.div = div;
	
	if(this.cfg.css) this.div.css(this.cfg.css);
	
	this.menuItemsDiv = null;
	this.menu = this.cfg.actions;
	this.screen = null;
	
	this.editing = {};
	this.adding = {};
	/*
		name : {
			input : $(element)
		},
		fields : [
			{
				id : 1,
				p1 : 1,
				p2 : p2,
				p3 : p3,
				type : type,
				input : $(element)
			},
			... etc
		]
	*/
	
	this.info = {};
	this.info.actions = {
		add : {
			title : 'добавить',
			css : {backgroundPosition:'0px -48px'}
		},
		edit : {
			title : 'редактировать',
			css : {backgroundPosition:'0px 0px'}
		},
		remove : {
			title : 'удалить',
			css : {backgroundPosition:'0px -288px'}
		},
		move : {
			title : 'переместить',
			css : {backgroundPosition:'0px -96px'}
		},
		save : {
			title : 'сохранить',
			css : {backgroundPosition:'0px -32px'}
		},
		resetEdit : {
			title : 'отмена',
			css : {backgroundPosition:'0px -208px'}
		},
		list : {
			title : 'редактировать&nbsp;список',
			css : {backgroundPosition:'0px -400px'}
		}
	};
	
	this.action = {
		add : function(){
			that.addObjectForm();
		},
		
		edit : function(){
			that.editObject();
		},
		
		remove : function(){
			if(!confirm('Удалить объект?')) return false;
			that.removeObject();
		},
		
		move : function(){
			that.moveForm();
		}, 
		
		save : function(){
			that.saveObjectFromDesign();
		}, 
		
		resetEdit : function(){
			that.resetEdit();
		},
		
		list : function(){
			that.viewObjectsList();
		}
	}
	
	this.buildActionTag = function( name ){
		if(typeof this.info.actions[name] == 'undefined') return '<div>Unknown '+name+'</div>';
		
		var a = $('<a href="#">'+this.info.actions[name].title+'</a>').click(function(){
			that.action[name]();
			that.hideMenu();
			return false;
		}).append( $("<div>").addClass('fe-ico').css(this.info.actions[name].css) );
		
		return a;
	}
	
	this.display = function(){
		if( this.cfg.info ){
			for(var i in this.cfg.info){
				if(this.info.actions[i]) this.info.actions[i].title = this.cfg.info[i];
			}
		}
		that.div.empty();
		
		that.div.live("mouseenter mouseleave", function(e){
			id = that.div.attr('id');
			div = $('div#'+id);
			if (e.type=='mouseenter'){
				that.hideMenu();
				that.menuItemsDiv = $('<div>').addClass('fe-smart-menu-items');
				for(var i in that.menu){
					that.menuItemsDiv.append(
						that.buildActionTag( that.menu[i] )
					);
				}
				
				that.menuItemsDiv.appendTo(div).slideDown('fast');
			}
			if (e.type=='mouseleave'){
				$('div#'+id+' div').remove();
			}
		});
		
		this.viewText();
	}
	
	this.viewText = function(text){
		if(typeof text != 'undefined'){ 
			this.div.html(text);
			return;
		}
		this.div.html( '▼ правка'+(this.cfg.title?' '+this.cfg.title:'') );
		return;
	}
	
//РАБОТА С МЕНЮ	
	this.setMenu = function(list){
		if(typeof list != 'undefined' && list.length>0) this.menu = list;
		else this.menu = this.cfg.actions;
		return this;
	}
	
	this.showMenu = function(list){
		this.hideMenu();
		this.menuItemsDiv = $('<div>').addClass('fe-smart-menu-items');
		for(var i in this.menu){
			this.menuItemsDiv.append( 
				this.buildActionTag( this.menu[i] ) 
			);
		}
		this.menuItemsDiv.appendTo(this.div).slideDown('fast');
	}
	
	this.hideMenu = function(){
		if(this.menuItemsDiv){ 
			this.menuItemsDiv.remove();
			this.menuItemsDiv = null;
		}
	}
//РАБОТА С МЕНЮ.end
	
//РАБОТА С МОДАЛЬНЫМ ОКНОМ	
	this.createScreen = function(action){
		this.removeScreen();
		this.screen = {};
		this.screen.div = $("<div>").addClass('fe-modal-conteiner').css({height:(document.documentElement.scrollHeight || document.documentElement.offsetHeight)}).appendTo(document.body);
		this.screen.background = $("<div>").addClass('fe-background').css({opacity:0, height:'100%'}).click(function(){
			that.removeScreen(true);
		}).appendTo( this.screen.div );
		this.screen.face = $("<div>").addClass('fe-face').css({top:document.documentElement.scrollTop+50}).appendTo( this.screen.div ).css({cursor : 'move'}).draggable({opacity:0.7});
		//animation
		this.screen.background.show().animate({opacity:0.5}, function(){
			//do stuff
			action();
			that.screen.face.show().css({marginLeft:'-'+(that.screen.face.width()/2)+'px'});
			setTimeout(function(){ that.screen.face.css({marginLeft:'-'+(that.screen.face.width()/2)+'px'}) }, 1000);
			//alert(document.documentElement.scrollTop+' : '+document.documentElement.scrollHeight);
		});
	}
	
	this.removeScreen = function(flag){
		if(this.screen){
			if(flag){
				this.screen.face.empty().slideUp('fast', function(){
					that.screen.background.fadeOut('fast', function(){
						that.screen.div.remove();
						that.screen = null;
					});
				});
			}else this.screen.div.remove();
		}
	}
//РАБОТА СО СПИСКОМ
	this.viewObjectsList = function(){
		that.createScreen(function(){
			var screen = $('<div>').css({padding:'30px 70px'}).html( $('<h1>').html('Редактирование списка') )
			.append( $('<a href="#">обновить сайт</a>').click(function(){location.reload();}) );
			that.cfg.listDiv = $("<div>").appendTo( screen );
			that.loadObjectsList(that.cfg.listDiv, that.cfg.id);
			
			that.screen.face.html( screen.append( $('<a href="#" title="добавить страницу"><img src="/frontEnd/add.png" border="0" style="margin-bottom:-5px;"></a>').click(function(){
				that.addObjectFromList(that.cfg.id, that.cfg.p.list);
			}) ) );
		});
	}
	
	this.loadObjectsList = function(div, id){
		div.empty();
		var out = {getObjectsList:id, class_id : this.cfg.p.list};
		
		$.getJSON(ajaxFeFile, out, function(list){
			var ul = $("<ul>").addClass('ul-none').attr({id:'ul-sort-'+id});
			for(var i in list){
				(function(o){
					var a = $('<span>').text(o.name);
					var li = $("<li>").html( a ).append(" &mdash;");
					if(i!=0){
						li.append(" ")
						.append( $('<a href="#" title="выше"><img src="/frontEnd/up.png" border="0" style="margin-bottom:-5px;"></a>').click(function(){
							return that.sortObject(o.id, 'up');
						}) );
					}
					if(list.length-1>i){
						li.append(" ")
						.append( $('<a href="#" title="ниже"><img src="/frontEnd/down.png" border="0" style="margin-bottom:-5px;"></a>').click(function(){
							return that.sortObject(o.id, 'down');
						}) );
					}	
					li.append(" ")
					.append( $('<a href="#" title="добавить страницу"><img src="/frontEnd/add.png" border="0" style="margin-bottom:-5px;"></a>').click(function(){
						return that.addObjectFromList(o.id, that.cfg.p.list);
					}) );
					li.append(" ")
					.append( $('<a href="#" title="удалить страницу"><img src="/frontEnd/remove.png" border="0" style="margin-bottom:-5px;"></a>').click(function(){
						return that.removeObjectFromList(o.id);
					}) );
					if(o.inside>0 && o.id!=5 && o.id != 24){
						var hidden = $('<div>').appendTo(li);
						that.loadObjectsList(hidden, o.id);
					}
					li.appendTo(ul);
				})(list[i]);
			}
			$("<div>").html(ul).appendTo( div );
		});
	}
	
	this.sortObject = function(id, to){
		var out = {
			sortObject : id,
			to : to
		};
		$.get(ajaxFeFile, out, function(status){
			if(status == 'ok')	that.loadObjectsList(that.cfg.listDiv, that.cfg.id);
			else alert('Ошибка сортировки.');
		});
		return false;
	}
	
	this.addObjectFromList = function(head, class_id){
		//if(!confirm('Добавить объект?')) return false;
		var out = {
			addObjectToList : head,
			class_id : class_id,
			lang : _LANG_
		};
		$.get(ajaxFeFile, out, function(status){
			if(status == 'ok')	that.loadObjectsList(that.cfg.listDiv, that.cfg.id);
			else alert('Ошибка добавления.');
		});
		return false;
	}
	
	this.removeObjectFromList = function(id){
		if(!confirm('Удалить объект?')) return false;
		$.get(ajaxFeFile, {removeObject:id}, function(status){
			that.loadObjectsList(that.cfg.listDiv, that.cfg.id);
			
		});
		return false;
	}
	
//ПЕРЕМЕЩЕНИЕ ОБЪЕКТА
	this.moveForm = function(){
		if(typeof this.cfg.p.move == 'undefined' || this.cfg.p.move.length==0) return alert('Недостаточно параметров.');
		this.viewText('загрузка службы...');
		var out = {loadObjects:1};
		for(var i in this.cfg.p.move){
			var id = this.cfg.p.move[i];
			out['ids['+i+']']=id;
		}
		
		$.getJSON(ajaxFeFile, out, function(list){
			that.viewText();
			if(list.length==0) return alert('Объектов нет в системе!');
			that.createScreen(function(){
				var screen = $('<div>').css({padding:'30px 70px'}).html( $('<h1>').html('Переместить в') );
				
				for(var i in list){
					var o = list[i];
					$("<div>").html('<input type="radio" name="check_move_option" value="'+o.id+'"'+(that.cfg.head && that.cfg.head==o.id?' checked':'')+'> '+o.name).appendTo( screen );
				}
				
				screen.append('<br>');
				
				$('<button>').addClass('button').addClass('approve').text('переместить').click(function(){
					/*if(!!confirm('Переместить элемент?'))*/ that.move( that.screen.face.find(":checked").val() );
				}).appendTo( screen );
				
				$('<button>').addClass('button').addClass('cancel').text('отмена').click(function(){
					that.removeScreen(true);
				}).appendTo( screen );
								
				that.screen.face.html( screen );
			});
		});
	}
	
	this.move = function(head){	
		this.removeScreen(true);
		this.viewText('сохранение данных...');
		var out = {
			moveObject : this.cfg.id,
			to : head
		};
		$.post(ajaxFeFile, out, function(text){
			if(text=='ok'){ 
				location.reload();
			}else{
				that.viewText( text );
				setTimeout(function(){ that.viewText(); } , 4000);
			}
		});
	}
	
//ДОБАВЛЕНИЕ ОБЪЕКТА
	this.addObjectForm = function(){
		this.adding = {};
		this.loadApprovedClasses();
	}
	
	this.loadApprovedClasses = function(){
		var out = {};
		for(var i in this.cfg.p.add){
			out['ids['+i+']']=this.cfg.p.add[i];
		}
		out['loadClasses']=Math.round(0, 1000000);
		this.viewText('загрузка службы...');
		this.adding.classes = {};
		$.getJSON(ajaxFeFile, out, function(list){
			that.viewText();
			that.adding.classes = list;
			that.viewAddForm();
		});
		
	}
	
	this.buildClassFields = function(y){
		var screen = this.adding.div.empty();
		for(var x in this.adding.classes){
			if(this.adding.classes[x].id==y){
				this.adding.fields = [];
				for(var i in this.adding.classes[x].fields){
					var f = this.adding.classes[x].fields[i];
					var input = this.buildFieldInput(f);
					f.input = input;
					
					this.adding.fields.push(f);
					
					if(f.type=='file'){
						$("<div>").addClass('input-row').html( $("<div>").addClass("title").text(f.name) ).append(f.value?'<a href="'+_UPLOADS_+'/'+f.value+'" target="_blank">скачать</a>, &mdash; или загрузить другой ':'').append( input ).appendTo( screen );
					}else $("<div>").addClass('input-row').html( $("<div>").addClass("title").text(f.name) ).append( input ).appendTo( screen );
					
				}	
				CKEDITOR.instances = {};
				$('.cke').ckeditor({
						//skin:'office2003',
						uiColor:'#e6e6e6',
						filebrowserBrowseUrl : _FILES_ + '/appends/ckfinder/ckfinder.html',
						filebrowserImageBrowseUrl : _FILES_ + '/appends/ckfinder/ckfinder.html?Type=Images',
						filebrowserFlashBrowseUrl : _FILES_ + '/appends/ckfinder/ckfinder.html?Type=Flash',
						filebrowserUploadUrl : _FILES_ + '/appends/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
						filebrowserImageUploadUrl : _FILES_ + '/appends/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
						filebrowserFlashUploadUrl : _FILES_ + '/appends/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash',
						toolbar: [
							['Source','-','Undo','Redo'],
							['Cut','Copy','Paste','PasteText','PasteFromWord', '-', 'Image','Flash','Table','HorizontalRule','SpecialChar','Link','Unlink','Anchor','Subscript','Superscript'],
							['NumberedList','BulletedList','-','Maximize'],
							'/',
							['Format','FontSize'],
							['Bold','Italic','Underline','Strike'],
							['TextColor','BGColor','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','Outdent','Indent', '-', 'RemoveFormat','ShowBlocks'],
							
						],
						removePlugins : 'resize',
						width : '650px'
					});
				break;
			}
		}
	}
	
	this.viewAddForm = function(){
		//if(this.adding.classes.length==0) return alert('Ошибка формы. Обратитесь к администратору.');
		CKEDITOR.instances = {};
		this.createScreen(function(){
			var screen = $('<div>').css({padding:'30px 70px'}).html( $('<h1>').html('Добавление') ).append('<br>');
			if(that.adding.classes.length>1){
				var typeSelectHTML = $("<select>").change(function(){
					that.adding.class_id = $(this).val();
					that.buildClassFields( $(this).val() );
				});
				if( that.cfg.p.add[0]==0 ) typeSelectHTML.html( $("<option>").attr({value:0}).text( 'Без шаблона' ) );
				for(var i in that.adding.classes){
					(function(c){
						typeSelectHTML.append( $("<option>").attr({value:c.id}).text( c.name ) );
					})(that.adding.classes[i]);
				}
					
				//set object name
				var name = {type:'text', p1:'300px'};
				var input = that.buildFieldInput( name );
				name.input = input;
				that.adding.name = name;
				
				//$("<div>").addClass('input-row').html( $("<div>").addClass("title").text('Название') ).append( input ).appendTo( screen );
				
				$("<div>").append( typeSelectHTML).appendTo( screen );
				
				screen.append("<br>");
				
				that.adding.div = $('<div>').appendTo(screen);
				
				$('<button>').addClass('button').addClass('approve').text('добавить').click(function(){
					/*if(!!confirm('Добавить элемент?')) */that.createObjectFromModal();
				}).appendTo( screen );
				
				$('<button>').addClass('button').addClass('cancel').text('отмена').click(function(){
					that.removeScreen(true);
				}).appendTo( screen );
								
				that.screen.face.html( screen );
				
				
				
				that.adding.class_id = typeSelectHTML.val();
				that.buildClassFields( typeSelectHTML.val() );
			}else{
				
				//set object name
				var name = {type:'text', p1:'300px'};
				var input = that.buildFieldInput( name );
				name.input = input;
				that.adding.name = name;
				
				//$("<div>").addClass('input-row').html( $("<div>").addClass("title").text('Название') ).append( input ).appendTo( screen );
				that.adding.fields = [];
				if( that.adding.classes.length>0 ){
					
				var cls = that.adding.classes[0];
				that.adding.class_id = cls.id;that.adding.class_id = cls.id;
					//set fields
					for(var i in cls.fields){
						var f = cls.fields[i];
						var input = that.buildFieldInput(f);
						f.input = input;
						
						that.adding.fields.push(f);
						
						if(f.type=='file'){
							$("<div>").addClass('input-row').html( $("<div>").addClass("title").text(f.name) ).append(f.value?'<a href="'+_UPLOADS_+'/'+f.value+'" target="_blank">скачать</a>, &mdash; или загрузить другой ':'').append( input ).appendTo( screen );
						}else $("<div>").addClass('input-row').html( $("<div>").addClass("title").text(f.name) ).append( input ).appendTo( screen );
						
					}			
				}else{
					that.adding.class_id = 0;
				}
				
				$('<button>').addClass('button').addClass('approve').text('добавить').click(function(){
					/*if(!!confirm('Добавить элемент?'))*/ that.createObjectFromModal();
				}).appendTo( screen );
				
				$('<button>').addClass('button').addClass('cancel').text('отмена').click(function(){
					that.removeScreen(true);
				}).appendTo( screen );
				
				that.screen.face.html( screen );
				$('.cke').ckeditor({
					//skin:'office2003',
					uiColor:'#e6e6e6',
					filebrowserBrowseUrl : _FILES_ + '/appends/ckfinder/ckfinder.html',
					filebrowserImageBrowseUrl : _FILES_ + '/appends/ckfinder/ckfinder.html?Type=Images',
					filebrowserFlashBrowseUrl : _FILES_ + '/appends/ckfinder/ckfinder.html?Type=Flash',
					filebrowserUploadUrl : _FILES_ + '/appends/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
					filebrowserImageUploadUrl : _FILES_ + '/appends/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
					filebrowserFlashUploadUrl : _FILES_ + '/appends/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash',
					toolbar: [
						['Source','-','Undo','Redo'],
						['Cut','Copy','Paste','PasteText','PasteFromWord', '-', 'Image','Flash','Table','HorizontalRule','SpecialChar','Link','Unlink','Anchor','Subscript','Superscript'],
						['NumberedList','BulletedList','-','Maximize'],
						'/',
						['Format','FontSize'],
						['Bold','Italic','Underline','Strike'],
						['TextColor','BGColor','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','Outdent','Indent', '-', 'RemoveFormat','ShowBlocks'],
						
					],
					removePlugins : 'resize',
						width : '650px'
				});
			}
		});
	}
	
	this.createObjectFromModal = function(){
		var files = [];
		if(this.adding.fields.length>0){
			for(var i in this.adding.fields){
				var f = this.adding.fields[i];
				if(f.type!='file' || f.input.val()=='') continue;
				files.push(f);
			}
		}
		if(files.length==0) return this.saveCreatedObjectFromModal();
		$('<div>').attr({id:'upload-files-conteiner'}).css({display:'none'})
		.html('<iframe id="form-transport-id" name="form-transport" src="about:blank"></iframe>')
		.append('<form method="POST" action="'+ajaxFeFile+'?uploadFiles" target="form-transport" id="upload-form" enctype="multipart/form-data"></form>')
		.appendTo(this.screen.face);

		var form = $('#upload-form');
		for(var i in files){
			files[i].input.appendTo(form);
		}
		$('#form-transport-id').load(function(){
			that.adding.files = eval("("+fe.getIframeDocument(this).body.innerHTML+")");
			that.saveCreatedObjectFromModal();
		});
		form.submit();
		
		return false
	}
	
	this.saveCreatedObjectFromModal = function(){
		var out = {};
		/*if(this.adding.name){
			this.saveFieldFromModal( this.adding.name ); 
			out['obj[name]'] = this.adding.name.value;
		}else */
		out['obj[name]'] = (new Date()).toString();//this.cfg.obj.name;
		if(this.adding.fields.length>0){
			for(var i in this.adding.fields){
				var f = this.adding.fields[i];
				if(f.type=='file'){
					if(this.adding.files && this.adding.files['file-'+f.id]){ 
						f.value = this.adding.files['file-'+f.id];
					}else f.value = '';
				}else this.saveFieldFromModal( this.adding.fields[i] );
				out['obj[fields]['+this.adding.fields[i].id+']'] = this.adding.fields[i].value;
			}
		}
		out['obj[class_id]'] = this.adding.class_id;
		
		out['createObject'] = this.cfg.id;
		out['lang'] = _LANG_;
		this.removeScreen(true);
		this.viewText('сохранение данных...');
		$.post(ajaxFeFile, out, function(text){
			if(text=='ok'){ 
				location.reload();
			}else{
				that.viewText( text );
				setTimeout(function(){ that.viewText(); } , 4000);
			}
		});
		
		this.setMenu();
		return false;
	}

//УДАЛЕНИЕ ОБЪЕКТА
	this.removeObject = function(){
		this.viewText('удаление...');
		$.get(ajaxFeFile, {removeObject:this.cfg.id}, function(text){
			if(that.cfg.p.remove) $(that.cfg.p.remove).text('Удалено успешно.');
			that.div.remove();
		});
	}

//СОХРАНЕНИЕ ПОЛЕЙ И ОБЪЕКТОВ	
	//СОХРАНЕНИЕ ПОЛЕЙ И ОБЪЕКТОВ	
	this.saveObjectFromModal = function(){
		var files = [];
		if(this.editing.fields.length>0){
			for(var i in this.editing.fields){
				var f = this.editing.fields[i];
				if(f.type!='file' || f.input.val()=='') continue;
				files.push(f);
			}
		}
		if(files.length==0) return this.doSaveObjectFromModal();
		$('<div>').attr({id:'upload-files-conteiner'}).css({display:'none'})
		.html('<iframe id="form-transport-id" name="form-transport" src="about:blank"></iframe>')
		.append('<form method="POST" action="'+ajaxFeFile+'?uploadFiles" target="form-transport" id="upload-form" enctype="multipart/form-data"></form>')
		.appendTo(this.screen.face);

		var form = $('#upload-form');
		for(var i in files){
			files[i].input.appendTo(form);
		}
		$('#form-transport-id').load(function(){
			that.editing.files = eval("("+fe.getIframeDocument(this).body.innerHTML+")");
			that.doSaveObjectFromModal();
		});
		form.submit();
		
		return false
	}	
	
	this.doSaveObjectFromModal = function(){
		var out = {};
		/*if(this.editing.name){
			this.saveFieldFromModal( this.editing.name ); 
			out['obj[name]'] = this.editing.name.value;
		}else out['obj[name]'] = this.cfg.obj.name;*/
		out['obj[name]'] = (new Date()).toString();
		if(this.editing.fields.length>0){
			for(var i in this.editing.fields){
				var f = this.editing.fields[i];
				if(f.type=='file'){
					if(this.editing.files && this.editing.files['file-'+f.id]){ 
						f.value = this.editing.files['file-'+f.id];
					}
				}else this.saveFieldFromModal( this.editing.fields[i] );
				out['obj[fields]['+this.editing.fields[i].id+']'] = this.editing.fields[i].value;
			}
		}
		out['obj[id]'] = this.cfg.id;
		out['obj[class_id]'] = this.cfg.obj.class_id;
		
		out['editObject'] = this.cfg.id;
		out['lang'] = _LANG_;
		this.removeScreen(true);
		this.viewText('сохранение данных...');
		
		$.post(ajaxFeFile, out, function(url){
			that.viewText( 'Сохранено успешно' );
			setTimeout(function(){ that.viewText(); } , 4000);
		});
		
		this.setMenu();
		return false;
	}

	
	this.saveFieldFromModal = function(f){
		switch(f.type){
			case 'checkbox': 
				f.value = (f.input.is(':checked') ? 1 : 0);
				break
			case 'radio': 
				f.value = f.input.find("input:radio[checked]").val();
				break;
			case 'html': 
				f.value = $('#field-'+f.id).val();
				break;
			default: f.value = f.input.val();
		}
	}
	
	this.saveObjectFromDesign = function(){
		var out = {};
		if(this.editing.name){
			this.saveFieldFromDesign( this.editing.name ); 
			out['obj[name]'] = this.editing.name.value;
		}else out['obj[name]'] = this.cfg.obj.name;
		if(this.editing.fields.length>0){
			for(var i in this.editing.fields){
				this.saveFieldFromDesign( this.editing.fields[i] );
				out['obj[fields]['+this.editing.fields[i].id+']'] = this.editing.fields[i].value;
			}
		}
		out['obj[id]'] = this.cfg.id;
		out['obj[class_id]'] = this.cfg.obj.class_id;
		
		out['editObject'] = this.cfg.id;
		out['lang'] = _LANG_;
		
		this.viewText('сохранение данных...');
		$.post(ajaxFeFile, out, function(url){
			that.viewText( 'Сохранено успешно' );
			setTimeout(function(){ that.viewText(); } , 4000);
		});
		
		this.setMenu();
		return false;
	}
	
	this.saveFieldFromDesign = function(f){
		switch(f.type){
			case 'html':
				var editor = f.input.ckeditorGet().destroy();
				f.value = f.div.html();
				break;
			default:
				f.value = f.input.val();
				f.input.remove();
				f.div.show().html(f.value);
		}
	}
//СОХРАНЕНИЕ ПОЛЕЙ И ОБЪЕКТОВ.end
	
//РЕДАКТИРОВАНИЕ ОБЪЕКТА
	this.resetEdit = function(){
		if(this.editing.name) this.resetField( this.editing.name );
		if(this.editing.fields.length>0){
			for(var i in this.editing.fields){
				this.resetField( this.editing.fields[i] );
			}
		}
		
		this.viewText();
		this.setMenu();
	}
	
	this.resetField = function(f){
		switch(f.type){
			case 'html':
				var editor = f.input.ckeditorGet().destroy(true);
				break;
			default:
				f.input.remove();
				f.div.show();
		}
	}
	
	this.editObject = function(){
		//обнуляем объект редактирования
		this.editing = {};
		this.viewText('загрузка службы...');
		
		$.getJSON(ajaxFeFile, {getObjectInfo:this.cfg.id, lang:_LANG_}, function(obj){
			that.cfg.obj = obj;
			if(obj.length==0) return alert('Объект не найден');
			
			// !!!редактирование в дизайне, если в параметрах smart-объекта заполнен параметр edit
			if(that.cfg.p.edit){
				if(that.cfg.p.edit['name']){ 
					var name = {div : $(that.cfg.p.edit['name']), type:'text', p1:'300px'};
					name.input = that.fieldChangeToInput( name );
					that.editing.name = name;
				}
				that.editing.fields = [];
				if(that.cfg.p.edit.fields){
					for(var i in obj.fields){
						var f = obj.fields[i];
						if(typeof that.cfg.p.edit.fields[f.id] != 'undefined'){ 
							f.div = $(that.cfg.p.edit.fields[f.id]);
							f.input = that.fieldChangeToInput(f);
							that.editing.fields.push(f);
						}
					}
				}
				that.setMenu(['save', 'resetEdit']);
				
				that.viewText('▼ сохранить или отменить');
			// !!!модальное окно
			}else{
				
				CKEDITOR.instances = {};
				that.createScreen(function(){
					var screen = $('<div>').css({padding:'30px 70px'}).html( $('<h1>').html('Редактирование') ).append('<br>');
					//set object name
					var name = {value: obj.name, type:'text', p1:'300px'};
					var input = that.buildFieldInput( name );
					name.input = input;
					that.editing.name = name;
					
					//$("<div>").addClass('input-row').html( $("<div>").addClass("title").text('Название') ).append( input ).appendTo( screen );
					
					//set fields
					that.editing.fields = [];
					for(var i in obj.fields){
						var f = obj.fields[i];
						var input = that.buildFieldInput(f);
						f.input = input;
						
						that.editing.fields.push(f);
						
						if(f.type=='file'){
							$("<div>").addClass('input-row').html( $("<div>").addClass("title").text(f.name) ).append(f.value?'<a href="'+_UPLOADS_+'/'+f.value+'" target="_blank">скачать</a>, &mdash; или загрузить другой ':'').append( input ).appendTo( screen );
						}else $("<div>").addClass('input-row').html( $("<div>").addClass("title").text(f.name) ).append( input ).appendTo( screen );
						
					}			
					
					$('<button>').addClass('button').addClass('approve').text('сохранить').click(function(){
						/*if(!!confirm('Сохранить изменения?'))*/ that.saveObjectFromModal();
					}).appendTo( screen );
					
					$('<button>').addClass('button').addClass('cancel').text('отмена').click(function(){
						that.removeScreen(true);
					}).appendTo( screen );
					
					that.screen.face.html( screen );
					
					$('.cke').ckeditor({
						//skin:'office2003',
						uiColor:'#e6e6e6',
						filebrowserBrowseUrl : _FILES_ + '/appends/ckfinder/ckfinder.html',
						filebrowserImageBrowseUrl : _FILES_ + '/appends/ckfinder/ckfinder.html?Type=Images',
						filebrowserFlashBrowseUrl : _FILES_ + '/appends/ckfinder/ckfinder.html?Type=Flash',
						filebrowserUploadUrl : _FILES_ + '/appends/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
						filebrowserImageUploadUrl : _FILES_ + '/appends/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
						filebrowserFlashUploadUrl : _FILES_ + '/appends/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash',
						toolbar: [
							['Source','-','Undo','Redo'],
							['Cut','Copy','Paste','PasteText','PasteFromWord', '-', 'Image','Flash','Table','HorizontalRule','SpecialChar','Link','Unlink','Anchor','Subscript','Superscript'],
							['NumberedList','BulletedList','-','Maximize'],
							'/',
							['Format','FontSize'],
							['Bold','Italic','Underline','Strike'],
							['TextColor','BGColor','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','Outdent','Indent', '-', 'RemoveFormat','ShowBlocks'],
							
						],
						removePlugins : 'resize',
						width : '650px'
					});
				});
				
			that.viewText();
			}
		});
		return false;
	}
	
	this.buildFieldInput = function(f){
		var input = null;
		var value = f.value?f.value:'';
		
		switch( f.type ){
			
			case 'date': 
				input = $('<input type="text">').val(value).datepicker({hightlight:{}});
				break;
			case 'checkbox': 
				input = $('<input type="checkbox">').attr({checked:(value==1?'checked':0)});
				break;
			case 'file': 
				input = $('<input type="file" name="file['+f.id+']">');
				break;
			case 'textarea':
				input = $('<textarea>').css({width:(f.p1||300), height:(f.p2||100)}).val(value);
				break;
			case 'html':
				input = $('<textarea>').attr({id:'field-'+f.id}).addClass('cke').css({width:(f.p1||300), height:(f.p2||100)}).val(value.replace(new RegExp("&lt;","gi"), '<').replace(new RegExp("&gt;","gi"), '>').replace(new RegExp("&amp;","gi"), '&').replace(new RegExp("&quot;","gi"), '"'));
				break;
			case 'radio':
				if(value == '') value=0;
				input = $("<div>");
				var vars = f.p3.split("\n");
				for(var i in vars){
					var row = vars[i];
					if(!row || row=='') continue;
					(function(){
						input.append(
							$("<div>").append(	$('<input type="radio" name="field_'+f.id+'" value="'+i+'"'+((value==i?' checked':''))+'>') ).append(' '+row)
						);
					})(i);
				}
				break;
			case 'select':
				if(value == '') value=0;
				input = $('<select>');
				var vars = f.p3.split("\n");
				for(var i in vars){
					var row = vars[i];
					if(!row || row=='') continue;
					(function(){
						$('<option>').attr({value:i}).text(row).appendTo( input );
					})(i);
				}
				if(value) input.val(value);
				break;
			case 'linkTo':
				input = $('<select>');
				$.getJSON(ajaxFeFile, {loadObjectsList:f.p4, lang: _LANG_}, function(list){
					var vars = list;
					for(var i in vars){
						var row = vars[i]['Название'];
						if(!row || row=='') continue;
						(function(){
							if (value == row)
								$('<option>').attr({value:row}).text(row).appendTo( input ).attr('selected', 'selected');
							else 
								$('<option>').attr({value:row}).text(row).appendTo( input );
						})(i);
					}
				});
				break;
			case 'text': 
				input = $('<input type="text">').css({width:(f.p1||300)}).val(value).keypress(function(e){ 
					$(this).removeClass('wrong-input');
					if(e.which!=8 && e.which!=0 && this.value.length>=250){ 
						$(this).addClass('wrong-input');
						return false; 
					}
				});
				break;
			case 'number': 
				input = $('<input type="text">').css({width:(f.p1||300)}).val(value).keypress(function(e){ 
					$(this).removeClass('wrong-input');
					if(e.which!=8 && e.which!=0 && !String.fromCharCode(e.which).match(/^[0-9]$/)){ 
						$(this).addClass('wrong-input');
						return false;
					}
				});
		}
		return input;
	}
	
	//CHANGE FIELD IN DESIGN
	this.fieldChangeToInput = function(field){
		switch(field.type){
			case 'text':
				var input = $('<input type="text">').attr({id:field.div.attr('id')+'-input'}).css({width:field.p1}).val( field.div.text() );
				field.div.hide().after( input );
				return input;
				break;
			case 'textarea':
				var input = $('<textarea>').attr({id:field.div.attr('id')+'-input'}).css({width:field.p1, height:field.p2}).val( field.div.text() );
				field.div.hide().after( input );
				return input;
				break;
			case 'html':
				var input = field.div.ckeditor({
					uiColor:'#e6e6e6',
					filebrowserBrowseUrl : _FILES_ + '/appends/ckfinder/ckfinder.html',
					filebrowserImageBrowseUrl : _FILES_ + '/appends/ckfinder/ckfinder.html?Type=Images',
					filebrowserFlashBrowseUrl : _FILES_ + '/appends/ckfinder/ckfinder.html?Type=Flash',
					filebrowserUploadUrl : _FILES_ + '/appends/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
					filebrowserImageUploadUrl : _FILES_ + '/appends/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
					filebrowserFlashUploadUrl : _FILES_ + '/appends/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash',
					toolbar: [
						['Source','-','Undo','Redo'],
						['Cut','Copy','Paste','PasteText','PasteFromWord', '-', 'Image','Flash','Table','HorizontalRule','SpecialChar','Link','Unlink','Anchor','-','Subscript','Superscript'],
						['NumberedList','BulletedList'],
						'/',
						['Format','FontSize'],
						['Bold','Italic','Underline','Strike'],
						['TextColor','BGColor','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','Outdent','Indent', '-', 'RemoveFormat','ShowBlocks','-','Maximize'],
						
					],
					removePlugins : 'resize',
					/*sharedSpaces : {
						top : 'editor-space',
						bottom : 'bottom-editor-space'
					},*/
					height:field.div.height()<200?200:field.div.height()
				});
				return input;
				break;
			default:
				return;
		}
	}
//РЕДАКТИРОВАНИЕ ОБЪЕКТА.end
}

var fe = new feUI();
$(document).keyup(function(e) {
	if (e.keyCode == 27)
		$('.fe-modal-conteiner').remove();
});