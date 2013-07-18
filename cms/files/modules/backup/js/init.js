$(function(){
	loadBackups();
});

function loadBackups(){
	ba = $('#backupsArea');
	ba.html( UI.loadingIMG + ' загрузка списка бэкапов' );
	$.getJSON(ajaxFile, {run: _RUN_, go: 'loadBackups'}, function(list){
		ba.html($('<a>').attr('href', '#createBackup').text('Создать бэкап').addClass('createBackup').click( function(){ createBackup() } ));
		if (list.name.length == 0){ ba.append('Бэкапов нет.'); return false; }
		row = [];
		row.push( $('<tr>')
		.append( $('<th>').text('№') )
		.append( $('<th>').text('Название') )
		.append( $('<th>').text('Размер') )
		.append( $('<th>').text('Дата') )
		.append( $('<th>').html('&nbsp;') ) );
		
		for(var i in list.name){
			var oname = list.name[i];
			var osize = list.size[i];
			var odate = list.date[i];
			row.push( $('<tr>').append( $('<td>').text(parseInt(i) + 1) )
			.append( $('<td>').html( $('<a>').attr('href', '/backup/' + oname).addClass('class-name').text( oname ) ) )
			.append( $('<td>').text( osize + ' КБ' ) )
			.append( $('<td>').text( odate ).css({"color": "#B80", "font-weight": "bold"}) )
			.append( $('<td>')
			// .append( $('<a>').attr('href', '/backup/' + oname).addClass('class-name').text('скачать') ).append('<br>')
			.append( $('<a>').attr('href', '#deleteBackup').addClass('class-name').addClass('deleteBackup').text('удалить').click( function(){ deleteBackup( $(this).parent().prev().prev().prev().text() ) } ) ) ) );
		}
		
		$('<table>').attr({'id' : 'object-row'}).addClass('object-row').append( row ).appendTo( ba );
	});
}

function createBackup(){
	ba = $('#backupsArea');
	ba.html( UI.loadingIMG + ' создание бэкапа' );
	$.get(ajaxFile, {run: _RUN_, go: 'createBackup'}, function(res){
		if (res === 'success')
			loadBackups();
		else {
			ba.empty();
			ba.append('Ошибка( ' + res + ' ) при создании бэкапа. ').append( $('<a>').attr('href', '#reload').text('Обновите').click( function(){ location.reload(); return false; } ) ).append(' страницу и попробуйте снова сохдать бэкап.');
		}
	});
	
	return false;
}

function deleteBackup(oname){
	if (!confirm('Вы действительно хотите удалить ' + oname + '?')) return false;
	
	ba = $('#backupsArea');
	ba.html( UI.loadingIMG + ' удаление бэкапа' );
	$.get(ajaxFile, {run: _RUN_, go: 'deleteBackup', oname: oname}, function(res){
		if (res === 'deleted')
			loadBackups();
		else {
			ba.empty();
			ba.append('Ошибка при удалении бэкапа. ').append( $('<a>').attr('href', '#reload').text('Обновите').click( function(){ location.reload(); return false; } ) ).append(' страницу и попробуйте снова удалить.');
		}
	});
	
	return false;
}