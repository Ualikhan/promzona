function RowInsert(newid){
	var table = $('table.internal');
	var idwithtext = table.find('tr:last').attr('id');
	var lastsort = table.find('tr:last td input.row_sort"').val();
	if (lastsort == ''){
		table.find('tr:last td input.row_sort"').val(0);
		sort = 0
	} else sort = lastsort;
	var newsort = parseInt(sort) + 10;
	var newid = parseInt(idwithtext.substr(-1)) + 1;
	$("<tr>").attr({id:"row"+newid})
		.append('<td><input type="text" name="MESSAGE[]" value=""></td>')
		.append('<td><input type="text" size="5" class="row_sort" name="SORT[]" value="'+newsort+'"></td>')
		.append('<td><input type="radio" name="row_def"></td>')
		.append('<td><div title="Удалить ответ" onclick="RowDelete(\'row'+newid+'\')" class="btn_delete" style="background: url('+_FILES_+'/modules/content/i/delete.gif) no-repeat 0 0;"></div></td>').appendTo(table);
}

function RowDelete(id){
	if ($('table.internal tr').length > 2)
		$('table.internal tr#'+id).remove();
	else alert('Невозможно удалить последний ответ. Необходимо иметь хотя бы один ответ');
}

$.datepicker.setDefaults({
	firstDay: 1,
	dateFormat: 'yy-mm-dd',
	dayNames: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
	dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
	monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
	duration: 'fast',
	changeYear: true
	
});

$.blockUI.defaults.css.backgroundColor = '#000';
$.blockUI.defaults.css.border = '0px';
$.blockUI.defaults.css.padding = '10px';
$.blockUI.defaults.css.font = 'bold 14px Arial';
$.blockUI.defaults.css.color = 'white';
$.blockUI.defaults.css.opacity = .5;

var UI = new oUI();
$(function(){
	UI.checkCommand();
});

function oUI(){
	var that = this;//private link
	this.lastCommand = null; 
	this.runCommand = null;
	this.loadingIMG = '<img src="'+_FILES_+'/i/loading.gif" style="margin-bottom:-10px;">';
	this.movingIMG = '<img src="'+_FILES_+'/i/drag.png" width="20">';
	
	this.command = function( com, dont ){
		if(!dont){
			this.lastCommand = com;
			window.location.hash = this.lastCommand.replace('#', '');
		}
		if($.browser.msie) this.addAncor();
		
		if( this.runCommand ) return this.runCommand( com );
		return false;
	}
	
	this.checkCommand = function(){
		var command = window.location.hash;
		if( command && command != this.lastCommand ) return this.command( command ); 
	}
	
	this.addAncor = function(){
		$('<a>').attr({href:this.lastCommand, id:this.lastCommand}).css({display:'none'}).appendTo( document.body );
	}
	
	this.getIframeDocument = function(iframeNode){
		if (iframeNode.contentDocument) return iframeNode.contentDocument;
		else if (iframeNode.contentWindow) return iframeNode.contentWindow.document;
		else return iframeNode.document;
	}
	
	this.getFE = function(name){
		var dot = -1;
		if((dot = name.lastIndexOf('.'))==-1) return '';
		return name.substr(dot);
	}
	
	this.drawPages = function(total_elements, on_one_page, current_page, view_pageslinks_per_side, func){
		if(total_elements==0 || on_one_page==0) return '';
		var index = view_pageslinks_per_side;
		var count_pages = Math.ceil(total_elements/on_one_page);
		if(count_pages==1) return '';
		var start = 1, end = (index*2<=count_pages?index*2:count_pages);
		if(current_page>index+1){
			if(current_page+index<=count_pages){
				start = current_page-index;
				end = current_page+index;
			}else{
				start = current_page - (end-(count_pages - current_page)) || 1;
				end = count_pages;
			}
		}
		
		function makeLink(text, page){
			return $('<a>').attr({href:'#page-'+page}).html(text).click(function(){ return func(page); });
		}
		
		var html = $('<div>').addClass('page-links');
		if(current_page>1){ 
			if(start>1) html.append( makeLink('в начало', 1) );
			html.append(makeLink('назад', current_page-1));
		}
		for(var i = start; i<=end; i++){
			if(i==current_page) html.append('<span>'+i+'</span>');
			else html.append( makeLink(i, i) );
		}
		if(current_page!=count_pages){ 
			html.append( makeLink('вперёд', current_page+1) );
			if(end!=count_pages) html.append( makeLink('в конец', count_pages) );
		}
		return html.append( $('<div>').css({marginTop:'10px', color:'#CCC'}).html('показано '+((current_page-1)*on_one_page)+'&ndash;'+((current_page-1)*on_one_page+on_one_page)+' из '+total_elements) );
	}
}

setInterval(function(){
	UI.checkCommand();
}, 1000);