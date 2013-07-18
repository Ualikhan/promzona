function oClasses(div){
	var that = this;
	this.div = div;
	this.list = [];
	this.constructor = new oClassConstructor(this.div);
	
	this.load = function(doAfter){
		this.list = [];
		this.div.html( UI.loadingIMG + ' загрузка списка шаблонов..' );
		$.getJSON(ajaxFile, {run:_RUN_, go:'loadClasses'}, function(list){
			for(var i in list){
				that.list.push( (new oClass(i, list[i])) );
			}
			if(doAfter) doAfter();
		});
	}
	
	this.viewList = function(){
		return this.load( 
			function(){
				that.div.html('<div><a href="#createClass" onclick="return UI.command(\'#createClass\')">+Создать шаблон</a></div><br>');
				if( !that.list.length ) return !that.div.append('Шаблонов нет.');
				for(var i in that.list){
					that.list[i].div.appendTo( that.div );
				}
			}
		);
	}
	
	this.edit = function(id){
		var action = function(){
			var c = that.getClassByID(id);
			if(!c) return that.div.text('Шаблона несуществует.');
			that.div.html( UI.loadingIMG + ' загрузка полей шаблона..' );
			c.loadFields(function(){
				var data = c.data;
					data.fields = c.fields;
				that.constructor.init().setData( data ).build('Редактирование шаблона &laquo;'+data.name+'&raquo;');
			});
		}
		if( !this.list.length ) return this.load( action );
		action();
	}
	
	this.save = function(){
		if(this.constructor.data.name=='') return !alert('Заполните название шаблона!');
		if(!this.constructor.data.fields.length) return !alert('Настройте поля шаблона!');
		if(!confirm('Сохранить шаблон '+this.constructor.data.name+'?')) return false;
		var send = {run:_RUN_, go:'saveClass'};
		var fields = this.constructor.data.fields;
		for(var i in this.constructor.data){
			if(i=='fields') continue;
			var p = this.constructor.data[i];
			send[i] = p;
		}
		for(var i in fields){
			var f = fields[i];
			for(var key in f){
				var p = f[key];
				send['fields['+i+']['+key+']'] = p;
			}
		}
		$.blockUI({message:'Сохранение шаблона..'});
		$.post(ajaxFile, send, function(a){
			if(a==0) return !alert('Ошибка сохранения!');
			$.unblockUI();
			UI.command('#config');
		});
	}
	
	this.create = function(){
		this.constructor.init().build('Создание шаблона');
	}
	
	this.remove = function(id){
		var c = that.getClassByID(id);
		if(!confirm('Удалить шаблон '+c.data.name+'?')) return false;
		$.get(ajaxFile, {run:_RUN_, go:'deleteClass', id:id}, function(a){
			if(a==0) return !alert('Ошибка удаления!');
			that.viewList();
		});
	}
	
	this.getClassByID = function(id){
		for(var i in this.list){
			if(this.list[i].data.id!=id) continue;
			return this.list[i];
		}
		return null;
	}
}

function oClassConstructor(div){
	var that = this;
	this.div = div;
	this.data = null;
	this.inputs = {};
	this.types = [
		{
			name : 'Данные',
			types : [
				{name:'строка текста (text)', value:'text'},
				{name:'блок текста (textarea)', value:'textarea'},
				{name:'html-содержимое', value:'html'},
				{name:'число (text)', value:'number'},
				{name:'дата', value:'date'}
			]
		},
		{
			name : 'Условия',
			types : [
				{name:'выпадающий список (select)', value:'select'},
				{name:'ислючение (radio)', value:'radio'},
				{name:'галочка (checkox)', value:'checkbox'}
			]
		},
		{
			name : 'Прочее',
			types : [
				{name:'загрузка файлов', value:'file'}
			]
		}
	];
	
	this.init = function(){
		this.data = null;
		var typeSelectHTML = $("<select>").change(function(){
			that.buildParams();
		});
		
		for(var i in this.types){
			var group = this.types[i];
			var groupHTML = $("<optgroup>").attr({label:group.name});
			for(var y in group.types){
				var type = group.types[y];
				groupHTML.append( $("<option>").attr({value:type.value}).text( type.name ) );
			}
			typeSelectHTML.append( groupHTML );
		}
		
		this.inputs = {
			name : $('<input type="text">').css({width:300}).keypress(function(e){ 
				$(this).removeClass('wrong-input');
				if(e.which!=8 && e.which!=0 && this.value.length>=250){ 
					$(this).addClass('wrong-input');
					return false; 
				}
			}),
			info : $('<textarea>').css({width:300, height:100}).keypress(function(e){ 
				$(this).removeClass('wrong-input');
				if(e.which!=8 && e.which!=0 && this.value.length>=250){ 
					$(this).addClass('wrong-input');
					return false; 
				}
			}),
			newField : {
				index : null,
				sort : $('<input type="text">').css({width:80}).keypress(function(e){ 
					$(this).removeClass('wrong-input');
					if(e.which!=8 && e.which!=0 && !String.fromCharCode(e.which).match(/^[0-9]$/)){ 
						$(this).addClass('wrong-input');
						return false;
					}
				}),
				name : $('<input type="text">').css({width:300}).keypress(function(e){ 
					$(this).removeClass('wrong-input');
					if(e.which!=8 && e.which!=0 && this.value.length>=250){ 
						$(this).addClass('wrong-input');
						return false; 
					}
				}),
				type : typeSelectHTML.css({width:300}),
				p1 : $('<input type="text">').css({width:40}),
				p2 : $('<input type="text">').css({width:40}),
				p3 : $('<textarea>').css({width:300, height:100}),
				listDiv : $('<div>'),
				paramsDiv : $('<div>').addClass('params-div')
			}
		};
		return this;
	}
	
	this.build = function(title){
		if( !!this.data ){
			this.inputs.name.val( this.data.name );
			this.inputs.info.val( this.data.info );
		}
		
		$("<div>").addClass('class-card')
		.append( $("<div>").addClass('input-row').html( $("<div>").addClass("title").text('Название') ).append( this.inputs.name ) )
		.append( $("<div>").addClass('input-row').html( $("<div>").addClass("title").text('Кратое описание') ).append( this.inputs.info ) )
		.appendTo( this.div.html( $('<h2>').html( title ) ).append( '<br>' ) );
		
		this.div.append( $("<div>").addClass('clear') ).append( $('<h2>').text( 'Настройка полей' ) ).append( '<br>' ).append( this.inputs.newField.listDiv );
		
		$("<div>").addClass('newfield-card').css({position:'relative'})
		.append( $("<div>").addClass('input-row').html( $("<div>").addClass("title").text('Сортировка') ).append( this.inputs.newField.sort ) )
		.append( 
			$("<div>").css({position:'absolute', right:'15px', top:'20px'})
				.append( $('<button>').text('сохранить поле').click(function(){
					that.saveField();
				}) )
				.append( $('<button>').text('отмена').click(function(){
					that.resetNewFieldsForm();
					that.buildParams();
				}) ) 
		)
		.append( $("<div>").addClass('input-row').html( $("<div>").addClass("title").text('Наименование') ).append( this.inputs.newField.name ) )
		.append( $("<div>").addClass('input-row').html( $("<div>").addClass("title").text('Тип поля') ).append( this.inputs.newField.type ) )
		.append( this.inputs.newField.paramsDiv )
		.appendTo( this.div );
		this.div.append( $("<div>").addClass('clear') ).append( '<br>' )
		.append( $('<button>').addClass('button').addClass('approve').text('сохранить').click(function(){
			that.save();
		}) )
		.append( $('<button>').addClass('button').addClass('cancel').text('отмена').click(function(){
			UI.command('#config');
		}) );
		this.buildParams();
		this.resetNewFieldsForm();
		this.buildFields();
	}
	
	this.save = function(){
		if( !this.data ) this.data = {};
		if( !this.data.fields ) this.data.fields = [];
		this.data.name = this.inputs.name.val();
		this.data.info = this.inputs.info.val();
		UI.classes.save();
	}
	
	this.saveField = function(){
		if( !this.data ) this.data = {};
		if( !this.data.fields ) this.data.fields = [];
		if( this.inputs.newField.sort.val()=='' || this.inputs.newField.name.val()=='' ) return !alert('Заполните параметры поля!');
		if( this.inputs.newField.index ){
			var f = this.data.fields[this.inputs.newField.index];
				f.sort = parseInt(this.inputs.newField.sort.val());
				f.name = this.inputs.newField.name.val();
				f.type = this.inputs.newField.type.val();
				f.p1 = (this.inputs.newField.p1.val() || '');
				f.p2 = (this.inputs.newField.p2.val() || '');
				f.p3 = (this.inputs.newField.p3.val() || '');
		}else{
			var newField = {
				sort : parseInt(this.inputs.newField.sort.val()),
				name : this.inputs.newField.name.val(),
				type : this.inputs.newField.type.val(),
				p1 : (this.inputs.newField.p1.val() || ''),
				p2 : (this.inputs.newField.p2.val() || ''),
				p3 : (this.inputs.newField.p3.val() || '')
			};
			var added = false;
			for(var i in this.data.fields){
				var f = this.data.fields[i];
				if(newField.sort<f.sort){
					this.data.fields.splice(i, 0, newField);
					added = true;
					break;
				}
			}
			if(!added) this.data.fields.push(newField);
		}
		this.resetNewFieldsForm();
		this.buildParams();
		this.buildFields();
	}
	
	this.buildParams = function(){
		this.inputs.newField.paramsDiv.empty().hide();
		switch( this.inputs.newField.type.val() ){
			case 'date': return;
			case 'checkbox': return;
			case 'file': return;
			case 'textarea':
				this.inputs.newField.paramsDiv
				.append( $("<div>").append('Длинна поля ').append( this.inputs.newField.p1 ).append(' пикселей.') )
				.append( $("<div>").append('Ширина поля ').append( this.inputs.newField.p2 ).append(' пикселей.') );
				break;
			case 'html': return;
			case 'radio':
				this.inputs.newField.paramsDiv
				.append( $("<div>").append('Введите значения через Enter ') )
				.append( this.inputs.newField.p3 );
				break;
			case 'select':
				this.inputs.newField.paramsDiv
				.append( $("<div>").append('Введите значения через Enter ') )
				.append( this.inputs.newField.p3 );
				break;
			default: this.inputs.newField.paramsDiv.append( $("<div>").append('Длинна поля ').append( this.inputs.newField.p1 ).append(' пикселей.') );
		}
		this.inputs.newField.paramsDiv.animate({height:'show'}, "fast");
	}
	
	this.buildFields = function(highLight){
		this.inputs.newField.listDiv.empty();
		if( !this.data || !this.data.fields || !this.data.fields.length ) return false;
		var table = $('<table>').addClass('fields-list')
		.append(
			$("<tr>")
			.append( $("<th>").text( 'ID' ) )
			.append( $("<th>").addClass('sort-td').text( 'Сортирова' ) )
			.append( $("<th>").addClass('type-td').text( 'Тип поля' ) )
			.append( $("<th>").addClass('name-td').text( 'Название' ) )
		);
		for(var i in this.data.fields){
			var f = this.data.fields[i];
			(function(i){
				$("<tr>").addClass(highLight&&highLight==i?'editing':'')
				.append( $("<td>").addClass('id-td').text( (f.id||'new') ) )
				.append( $("<td>").text( f.sort) )
				.append( $("<td>").text( f.type ) )
				.append( $("<td>").text( f.name ) )
				.append( 
					$("<td>").addClass('none')
					.append( $("<a>").attr({href:'#editField'}).text('редактировать').click(function(){ that.editField(i); return false; }) )
					.append(' или ')
					.append( $("<a>").attr({href:'#editField'}).text('удалить').click(function(){ that.removeField(i); return false; }) )
				)
				.appendTo( table );
			})(i)
		}
		this.inputs.newField.listDiv.html( table );
	}
	
	this.editField = function(id){
		if( !this.data || !this.data.fields || !this.data.fields[id]) return false;
		var f = this.data.fields[id];
		this.inputs.newField.index = id;
		this.inputs.newField.sort.val( f.sort );
		this.inputs.newField.name.val( f.name );
		this.inputs.newField.type.val( f.type );
		this.inputs.newField.p1.val( f.p1 );
		this.inputs.newField.p2.val( f.p2 );
		this.inputs.newField.p3.val( f.p3 );
		this.buildFields(id);
		this.buildParams();
	}
	
	this.removeField = function(id){
		if( !this.data || !this.data.fields || !this.data.fields[id] || !confirm('Удалить поле '+this.data.fields[id].name+'?')) return false;
		this.data.fields.splice(id, 1);
		this.buildFields();
	}
	
	this.setData = function(data){
		this.data = data;
		return this;
	}
	
	this.resetNewFieldsForm = function(){
		this.inputs.newField.index = null;
		this.inputs.newField.sort.val('');
		this.inputs.newField.name.val('');
		this.inputs.newField.type.val('text');
		this.inputs.newField.p1.val(300);
		this.inputs.newField.p2.val(100);
		this.inputs.newField.p3.val('');
		this.buildFields();
	}
	
	this.reset = function(){
		this.data = null;
		this.init().buld();
	}
}

function oClass(index, data){
	var that = this;
	this.index = index;
	this.data = data;
	this.fields = [];
	
	this.menuDiv = $("<div>").addClass('menu').css({zIndex:(1000-this.index)}).mouseenter(function(){ that.showMenu() }).mouseleave(function(){ that.hideMenu() });
	this.menuItemsDiv = null;
	
	var table = $('<table>').addClass('class-row')
	.append(
		$("<tr>")
		.append( $("<td>").addClass('menu-td').append( this.menuDiv ) )
		.append( $("<td>").append('<div class="name">'+this.data.name+' ['+this.data.id+']</div>').append( this.data.info?' <div class="info">'+this.data.info+'</div>':'' ) )
	);
	
	this.div = $("<div>").addClass('class-item').append( table );
	
	this.loadFields = function(doAfter){
		$.getJSON(ajaxFile, {run:_RUN_, go:'loadClassFields', id:this.data.id}, function(fields){
			if(!fields.length) return false;
			that.fields = fields;
			if(doAfter) doAfter();
		});
	}
	
	this.showMenu = function(){
		this.menuItemsDiv = $("<div>").addClass("menu-items").css({display:'none'});
		$("<a>").attr({href:'#editClass/'+this.data.id}).click(function(){ return UI.command($(this).attr('href')) }).text('редактировать').appendTo( this.menuItemsDiv );
		$("<a>").attr({href:'#deleteClass/'+this.data.id}).click(function(){ UI.command($(this).attr('href'), true); return false; }).text('удалить').appendTo( this.menuItemsDiv );
		this.menuItemsDiv.appendTo( this.menuDiv ).show("fast");
	}
	
	this.hideMenu = function(){
		this.menuItemsDiv.remove();
		this.menuItemsDiv = null;
	}
}
