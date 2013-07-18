function oObjects(div){
	var that = this;
	this.div = div;
	this.parents = [];
	this.parentsDiv = $("<div>").addClass('parents-div');
	this.listDiv = $("<div>");
	this.list = [];
	this.head = 0;
	this.cutted = null;
	this.cuttedObject = null;
	
	this.constructor = new oObjectConstructor(this.div);
	
	this.setHead = function(num){
		if(typeof num == 'undefined') return;
		this.head = num;
		this.pages = {page:1, onepage:50};
		return this;
	}
	
	this.buildParents = function(){
		this.parentsDiv.html( $("<a>").attr({href:'#list/0'}).text('Корень').click(function(){ return UI.command( $(this).attr('href') ); }) );
		for(var i in this.parents){
			var p = this.parents[i];
			(function(p){
				if( p.id==that.head ) var parent = $('<span>').html( p.name );
				else var parent = $("<a>").attr({href:'#list/'+p.id}).text(p.name).click(function(){ return UI.command( $(this).attr('href') ); });
				that.parentsDiv.append(' &rarr; ')
				.append( parent );
			})(p);
		}
	}
	
	this.load = function(doAfter){
		this.list = [];
		this.div.html( UI.loadingIMG+' загрузка списка объектов..' );
		$.getJSON(ajaxFile, {run:_RUN_, go:'loadObjects', head:this.head, page:this.pages.page, onepage:this.pages.onepage}, function(list){
			that.parents = list.parents;
			for(var i in list.objects){
				var o = list.objects[i];
				that.list.push( (new oObject(i, o)) );
			}
			that.buildParents();
			if(doAfter) doAfter(list);
		});
	}
	
	this.loadOne = function(id, doAfter){
		this.list = [];
		this.div.html( UI.loadingIMG+' загрузка объекта..' );
		$.getJSON(ajaxFile, {run:_RUN_, go:'loadObject', id:id}, function(one){
			that.list.push( (new oObject(0, one)) );
			that.list[0].values = one.values;
			if(doAfter) doAfter();
		});
	}
	
	this.viewList = function(){
		return this.load( 
			function(list){
				that.div.html('<div><a href="#createObject" onclick="return UI.command(\'#createObject\')">+Создать объект</a></div>');
				if(that.head>0) that.div.append(that.parentsDiv);
				else that.div.append('<br>');
				that.div.append(that.listDiv);
				that.drawList();
				that.div.append( UI.drawPages(list.total_count, that.pages.onepage, that.pages.page, 3, that.changePage) );
			}
		);
	}
	
	this.changePage = function(pg){
		that.pages.page = pg;
		that.viewList();
		return false;
	}
	
	this.drawList = function(){
		
		this.listDiv.empty();
		if(this.cutted && this.cuttedObject){
			this.listDiv.append( $('<div>').html('Вырезан объект &laquo;'+that.cuttedObject.data.name+'&raquo;, его можно ').append( $("<a>").attr({href:'#pasteRoot'}).text('вставить в корень').click(function(){ return that.paste(0); }) ).append(', любой из других объектов или ').append( $("<a>").attr({href:'#cancelCut'}).text('отменить вырезание').click(function(){ return that.cancelCut(); }) ).append('.') ).append('<br>');
		}
		if( !this.list.length ) return !this.listDiv.append('Объектов нет.');
		var table = $('<table>').addClass('object-row')
			.append(  
				$("<tr>")
				.append( $("<th>").text('!') )
				.append( $("<th>").html('&darr;') )
				.append( $("<th>").text('ID') )
				.append( $("<th>").text('Название') )
				.append( $("<th>").text('Шаблон') )
			)
		for(var i in this.list){
			var tr = this.list[i].buildHTML().tr;
			if(this.cutted == this.list[i].data.id) tr.addClass('cutted');
			tr.appendTo( table );
		}
		table.appendTo( $("<div>").addClass('object-item').appendTo( this.listDiv ) );
		return false;
	}
	
	this.cut = function(id){
		var o = this.getObjectByID(id);
		if(!o) return false;
		this.cutted = id;
		this.cuttedObject = o;
		this.drawList();
		return false;
	}
	
	this.paste = function(id){
		if(!this.cutted) return false;
		$.post(ajaxFile, {run:_RUN_, go:'moveObject', id:this.cutted, to:id}, function(a){
			if(a==0){ 
				alert('Ошибка перемещения!');
				that.drawList();
				return false;
			}
			that.viewList();
		});
		this.cutted = null;
		this.cuttedObject = null;
		return false;
	}
	
	this.cancelCut = function(){
		this.cutted = null;
		this.cuttedObject = null;
		this.drawList();
		return false;
	}
	
	this.edit = function(id){
		var action = function(){
			var c = that.list[0];
			if(!c) return that.div.text('Шаблона несуществует.');
			var data = c.data;
			UI.classes.load(function(){
				that.constructor.init().setData( data ).build('Редактирование объекта &laquo;'+data.name+'&raquo;');
			});
		}
		return this.loadOne( id, action );
	}
	
	this.save = function(){
		var send = {run:_RUN_, go:'saveObject', head:this.head};
		var fields = this.constructor.data.fields;
		for(var i in this.constructor.data){
			if(i=='fields') continue;
			var p = this.constructor.data[i];
			send[i] = p;
		}
		for(var i in fields){
			var f = fields[i];
			send['fields['+f.id+']'] = f.value;
		}
		$.post(ajaxFile, send, function(a){
			if(!a) return !alert('Ошибка сохранения!');
			$.unblockUI();
			UI.command('#list/'+that.head);
		});
	}
	
	this.create = function(){
		UI.classes.load(function(){
			that.constructor.init().build('Создание объекта');
		});
	}
	
	this.remove = function(id){
		var o = this.getObjectByID(id);
		if(!o || !confirm('Удалить объект '+o.data.name+'?')) return false;
		$.get(ajaxFile, {run:_RUN_, go:'deleteObject', id:id}, function(a){
			if(a==0) return !alert('Ошибка удаления!');
			that.viewList();
		});
	}
	
	this.sort = function(id, to){
		var o = this.getObjectByID(id);
		$.get(ajaxFile, {run:_RUN_, go:'sortObject', id:id, to:to}, function(a){
			if(a==0) return !alert('Ошибка сортировки!');
			that.viewList();
		});
	}
	
	this.getObjectByID = function(id){
		for(var i in this.list){
			if(this.list[i].data.id!=id) continue;
			return this.list[i];
		}
		return null;
	}
}

function oObject(index, data){
	var that = this;
	var parent = UI.objects;
	this.index = index;
	this.data = data;
	this.values = [];
	
	this.buildHTML = function(){
		this.menuDiv = $("<div>").addClass('menu').css({zIndex:(1000-this.index)}).mouseenter(function(){ that.showMenu() }).mouseleave(function(){ that.hideMenu() });
		this.menuItemsDiv = null;
		
		this.tr = $("<tr>")
			.append( $("<td>").addClass('menu-td').html( this.menuDiv ) )
			.append( $("<td>").addClass('children-td').html( $("<a>").attr({href:'#list/'+this.data.id}).text( this.data.inside ).click(function(){ UI.command('#list/'+that.data.id) }) ) )
			.append( $("<td>").addClass('id-td').html(this.data.id) )
			.append( $("<td>").html( 
				$("<div>").addClass('nowrap-hidden-line').css({width:'200'})
					.html(this.data.name)
					.append( $("<img>").attr({src:_FILES_+'/i/nowrap-bg.png'}) )) 
			)
			.append( $("<td>").addClass('class-name').html( (this.data.class_id>0?$("<a>").attr({href:'#editClass/'+this.data.class_id}).text(this.data.class_name).click(function(){ return UI.command( $(this).attr('href') ); }):'раздел' ) ) )
			.append( 
				$("<td>").addClass('sort').html( 
					$("<a>").attr({href:'#sortObject/'+this.data.id+'/up', title:'поднять выше'}).html("&uarr;").click(function(){ UI.command( $(this).attr('href'), true ); return false; }) 
				)
				.append(" ")
				.append( 
					$("<a>").attr({href:'#sortObject/'+this.data.id+'/down', title:'опустить ниже'}).html("&darr;").click(function(){ UI.command( $(this).attr('href'), true ); return false; }) 
				) 
			);
		return this;
	}
	
	this.showMenu = function(){
		this.menuItemsDiv = $("<div>").addClass("menu-items").css({display:'none'});
		$("<a>").attr({href:'#editObject/'+this.data.id}).click(function(){ return UI.command($(this).attr('href')) }).text('редактировать').appendTo( this.menuItemsDiv );
		if(parent.cutted!=this.data.id) $("<a>").attr({href:'#cutObject/'+this.data.id}).click(function(){ return parent.cut( that.data.id ) }).text('вырезать').appendTo( this.menuItemsDiv );
		if(!!(parent.cutted) && parent.cutted!=this.data.id && parent.cutted!=this.data.head) $("<a>").attr({href:'#pasteObject/'+this.data.id}).click(function(){ return parent.paste( that.data.id ) }).text('вставить').appendTo( this.menuItemsDiv );
		$("<a>").attr({href:'#deleteObject/'+this.data.id}).click(function(){ UI.command($(this).attr('href'), true); return false; }).text('удалить').appendTo( this.menuItemsDiv );
		this.menuItemsDiv.appendTo( this.menuDiv ).show("fast");
	}
	
	this.hideMenu = function(){
		this.menuItemsDiv.remove();
		this.menuItemsDiv = null;
		return this;
	}
}

function oObjectConstructor(div){
	var that = this;
	this.div = div;
	this.data = null;
	this.fieldsDiv = null;
	this.inputs = {};
	this.files = [];
	
	this.init = function(){
		this.data = null;
		this.files = [];
		this.fieldsDiv = $('<div>');
		var typeSelectHTML = $("<select>").change(function(){
			that.buildFields();
		}).append( $("<option>").attr({value:0}).text( 'Без шаблона' ) );
		for(var i in UI.classes.list){
			var c = UI.classes.list[i];
			typeSelectHTML.append( $("<option>").attr({value:c.data.id}).text( c.data.name ) );
		}
		
		this.inputs = {
			active : $('<input type="checkbox">').attr({checked:'checked'}),
			name : $('<input type="text">').css({width:300}).keypress(function(e){ 
				$(this).removeClass('wrong-input');
				if(e.which!=8 && e.which!=0 && this.value.length>=250){ 
					$(this).addClass('wrong-input');
					return false; 
				}
			}),
			class_id : typeSelectHTML,
			fields : []
		};
		return this;
	}
	
	this.setData = function(data){
		this.data = data;
		return this;
	}
	
	this.build = function(title){
		CKEDITOR.instances = {};
		if( !!this.data ){
			if(this.data.active!=1) this.inputs.active.removeAttr('checked');
			this.inputs.name.val( this.data.name );
			this.inputs.class_id.val( this.data.class_id );
		}
		
		$("<div>").addClass('class-card')
		.append( $("<div>").addClass('input-row').html( this.inputs.active ).append(' Активен') )
		.append( $("<div>").addClass('input-row').html( $("<div>").addClass("title").text('Название') ).append( this.inputs.name ) )
		.append( $("<div>").addClass('input-row').html( $("<div>").addClass("title").text('Шаблон') ).append( this.inputs.class_id ) )
		.appendTo( this.div.html( $('<h2>').html( title ) ).append( '<br>' ) );
		
		this.div.append( $("<div>").addClass('clear') )
		.append( this.fieldsDiv )
		.append( $('<button>').addClass('button').addClass('approve').text('сохранить').click(function(){
			that.preSave();
		}) )
		.append( $('<button>').addClass('button').addClass('cancel').text('отмена').click(function(){
			UI.command('#list/'+UI.objects.head);
		}) );
		this.buildFields();
	}
	
	this.save = function(files){
		$.blockUI({message:'Сохранение объекта..'});
		if( !this.data ) this.data = {};
		this.data.fields = [];
		this.data.active = this.inputs.active.is(':checked')?1:0;
		this.data.name = this.inputs.name.val();
		this.data.class_id = this.inputs.class_id.val();
		if( this.data.class_id>0 && this.inputs.fields.length>0){
			for(var i in this.inputs.fields ){
				var f = this.inputs.fields[i];
				var value = this.getInputValue(i);
				this.data.fields.push({
					id : f.data.id,
					value : value
				});
			}
		}
		UI.objects.save();
	}
	
	this.preSave = function(){
		if(this.inputs.name.val()=='') return !alert('Заполните название объекта!');
		//if(!confirm('Сохранить объект '+this.inputs.name.val()+'?')) return false;
		var files = [];
		for(var i in this.inputs.fields){
			var f = this.inputs.fields[i];
			if(f.data.type!='file') continue;
			if(f.input.val()!='') files.push(f);
		}
		if(files.length==0) return this.save();
		//мне наверное стыдно немного за то что ниже, ну и похуй, - грубовато
		$('<div>').attr({id:'upload-files-conteiner'}).css({display:'none'})
		.html('<iframe id="form-transport-id" name="form-transport" src="about:blank"></iframe>')
		.append('<form method="POST" action="'+ajaxFile+'?go=uploadFiles" target="form-transport" id="upload-form" enctype="multipart/form-data"></form>')
		.appendTo(this.div);

		var form = $('#upload-form');
		for(var i in files){
			files[i].input.clone().appendTo(form);
		}
		$('#form-transport-id').load(function(){
			that.files = eval("("+UI.getIframeDocument(this).body.innerHTML+")");
			that.save();
		});
		$.blockUI({message:'Загрузка файлов...'});
		form.submit();
	}
	
	this.getInputValue = function(i){
		if(this.inputs.fields.length>0){
			if(typeof this.inputs.fields[i] != 'undefined'){
				var f = this.inputs.fields[i];
				switch(f.data.type){
					case 'checkbox': return (f.input.is(':checked') ? 1 : 0);
					case 'radio': return f.input.find("input:radio[checked]").val();
					case 'html': return $('#field-'+f.data.id).val();
					case 'file': 
						if(this.files && this.files['file-'+f.data.id]){ 
							return this.files['file-'+f.data.id];
						}
						if(this.data && this.data.values){ 
							return this.getFieldValueByID(f.data.id);
						}
						return '';
					default: return f.input.val()
				}
			}
		}
		return null;
	}
	
	this.buildFieldInput = function(f){
		var input = null;
		var value = '';
		if(this.data && this.data.values){
			value = this.getFieldValueByID(f.id) || '';
		}
		switch( f.type ){
			
			case 'date': 
				input = $('<input type="text">').val(value).datepicker();
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
		return {input:input, data:f, value:value};
	}
	
	this.getFieldValueByID = function(id){
		if(typeof this.data.values['field_'+id] != 'undefined') return this.data.values['field_'+id];
		return '';
	}
	
	this.buildFields = function(){
		var class_id = this.inputs.class_id.val();
		if( class_id == 0 ){
			return that.fieldsDiv.empty();
		}
		that.fieldsDiv.html(UI.loadingIMG+' загрузка шаблона..');
		var c = UI.classes.getClassByID(class_id);
		c.loadFields(function(){
			that.inputs.fields = [];
			that.fieldsDiv.empty();
			for(var i in c.fields){
				var f = c.fields[i];
				var obj = that.buildFieldInput(f);
				that.inputs.fields.push(obj);
				
				if(f.type=='file'){
					$("<div>").addClass('input-row').html( $("<div>").addClass("title").text(f.name) ).append(obj.value?'<a href="/uploads/'+obj.value+'" target="_blank">скачать'+UI.getFE(obj.value)+'</a>, &mdash; или загрузить другой ':'').append( obj.input ).appendTo( that.fieldsDiv );
				}else $("<div>").addClass('input-row').html( $("<div>").addClass("title").text(f.name) ).append( obj.input ).appendTo( that.fieldsDiv );
				
			}
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
					['Cut','Copy','Paste','PasteText','PasteFromWord', '-', 'Image','Flash','Table','HorizontalRule','SpecialChar','Link','Unlink','Anchor','-','Subscript','Superscript'],
					['NumberedList','BulletedList'],
					'/',
					['Format','FontSize'],
					['Bold','Italic','Underline','Strike'],
					['TextColor','BGColor','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','Outdent','Indent', '-', 'RemoveFormat','ShowBlocks'],
					
				]
			});
		});
	}
}