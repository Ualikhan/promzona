//ИНИЦИАЛИЗАЦИЯ СТРАНИЦЫ
$(function(){
	/*$('#editor-block').ckeditor({
		uiColor:'#e6e6e6',
		toolbar: [
			['Source','-','Undo','Redo'],
			['Cut','Copy','Paste','PasteText','PasteFromWord', '-', 'Table','HorizontalRule','SpecialChar','Link','Unlink','Anchor','Subscript','Superscript'],
			['NumberedList','BulletedList', '-','Maximize'],
			'/',
			['Format','FontSize'],
			['Bold','Italic','Underline','Strike'],
			['TextColor','BGColor','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','Outdent','Indent', '-', 'RemoveFormat','ShowBlocks'],
			
		],
		removePlugins : 'resize'
	});
	*/
	
	
	$('#add-file-btn').click(function(){
		var div = $('#files-list')
		.append(
			$("<li>").append(
				$("<div>").addClass('nowrap-hidden-line').addClass('filename')
			).append(
				$('<input type="file">').attr({name:'files[]'}).change(function(){
					viewFileInfo( $(this) );
				})
			).append(
				$("<div>").append(
					$('<a href="#удалить файл">удалить</a>').click(function(){
						return removeFileForm( $(this).parent().parent() );
					})
				)
			)
		).show();
		return false;
	});
	
	setTimeout(function(){ $('#info-message').slideUp('normal'); }, 5000);
});

function removeFileForm(li){
	li.remove();
	var div = $('#files-list');
	if( div.is(':empty') ) div.hide();
	return false;
}

function viewFileInfo( input ){
	var filename = input.val();
	if(filename.indexOf('\\')!=-1){
		filename = filename.substr( filename.lastIndexOf('\\')+1 );
	}
	input.parent().find('.filename').text( filename ).append('<img src="'+_FILES_+'/i/nowrap-bg.png">').slideDown('fast');
}

function checkForm(f){
	var msg = [];
	
	if(f['mail[theme]'].value=='') msg.push('Введите тему рассылки!');
	
	if(msg.length>0){
		alert( msg.join("\n") );
		return false;
	}else if(!confirm('Отправить сообщение?')) return false;
	
	return true;
}