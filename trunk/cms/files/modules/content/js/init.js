//ИНИЦИАЛИЗАЦИЯ СТРАНИЦЫ
$(document).ready(function(){
	(function ( that ){
		that.div = $('#active-screen');
		
		that.classes = new oClasses( that.div );
		
		that.objects = new oObjects( that.div );
		
		that.leftMenu = new oLeftMenu( $('#left-menu') );
		
		that.runCommand = function( com ){
			var params = com.split("/");
			if( params.length>1 ) com = params[0];
			switch( com ){
				case '#list':
					this.leftMenu.activateByCommand( com );
					this.objects.setHead(params[1] || 0).viewList();
					break;
				case '#config':
					this.leftMenu.activateByCommand( com );
					this.classes.viewList();
					break;
				case '#createClass':
					this.leftMenu.activateByCommand( '#config' );
					this.classes.create();
					break;
				case '#editClass':
					this.leftMenu.activateByCommand( '#config' );
					this.classes.edit(params[1]);
					break;
				case '#deleteClass':
					this.classes.remove(params[1]);
					break;
				case '#createObject':
					this.leftMenu.activateByCommand( '#list' );
					this.objects.create();
					break;
				case '#editObject':
					this.leftMenu.activateByCommand( '#list' );
					this.objects.edit(params[1]);
					break;
				case '#deleteObject':
					this.objects.remove(params[1]);
					break;
				case '#deleteObjects':
					this.objects.removeSelected();
					break;
				case '#moveto':
					this.leftMenu.activateByCommand( '#list' );
					this.objects.moveSelected();
					break;
				case '#sortObject':
					this.objects.sort(params[1], params[2]);
					break;
				default:
					alert( 'Unknown command '+com );
					return false;
					
			}
			return true;
		}
		
		that.viewObjects = function(){
			that.div.text('Список объектов.');
		}
	})(UI);
	UI.command( UI.lastCommand || UI.leftMenu.items[0].a.attr('href') );
});

function oLeftMenu(div){
	var that = this;//private link
	this.div = div;
	this.items = [];
	
	this.init = function(){
		$.each( this.div.find('a'), function(k, v){
			var link = $( v ).click(function(){
				return UI.command( $(this).attr('href') );
			});
			that.items.push( ( new oMenuItem( link ) ) );
		});
	}
	
	this.deactivateAll = function(){
		for(var i in this.items){
			this.items[i].deactivate();
		}
	}
	
	this.activateByCommand = function( com ){
		for(var i in this.items){
			var t = this.items[i];
			if( t.a.attr('href')==com ){
				t.activate();
				continue;
			}
			t.deactivate();
		}
	}
	
	this.init();
}

function oMenuItem( a ){
	this.a = a;
	
	this.activate = function(){
		return !!this.a.addClass('active');
	}
	
	this.deactivate = function(){
		return !!this.a.removeClass('active');
	}
}