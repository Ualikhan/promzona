<?
// header('Location: /index.html');
include('run.php');?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<script type="text/javascript">
		var _ROOT_ = '<?=_ROOT_?>';
		var _FILES_ = '<?=_FILES_?>';
		var _UPLOADS_ = '<?=_UPLOADS_?>';
		var _RUN_ = '<?=$api->activeName?>';
		var ajaxFile = _ROOT_+"/ajax.php";
		function in_array(needle, haystack, strict) {
			var found = false, key, strict = !!strict;
			for (key in haystack) {
				if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
					found = true;
					break;
				}
			}
			return found;
		}
	</script>
	<script type="text/javascript" src="<?=_WWW_?>/js/libs/jquery-1.8.1.min.js"></script>
	<script type="text/javascript" src="<?=_WWW_?>/cms/js/jquery.tablednd.js"></script>
	<script type="text/javascript" src="/cms/js/ui/ui.js"></script>
	<script type="text/javascript" src="<?=_WWW_?>/cms/js/blockUI.js"></script>
	<script type="text/javascript" src="<?=_WWW_?>/cms/js/jstree/jquery.hotkeys.js"></script>
	<script type="text/javascript" src="<?=_WWW_?>/cms/js/jstree/jquery.jstree.js"></script>
	<script type="text/javascript" src="<?=_FILES_?>/js/init.js"></script>
	<?=$api->initJS()?>
	<style type="text/css" media="all">
	@import url("<?=_FILES_?>/css/styles.css");
	@import url("<?=_WWW_?>/cms/js/ui/ui.css");
	<?=$api->initCSS()?>
	#modules-list{
		text-align:right;
		margin-bottom:10px;
	}
	
	.ui-widget{font: normal 13px Arial !important;}
	.ui-dialog .ui-dialog-content{padding: 10px !important;}
	#dialogUL{padding: 7px 5px;}
	
	#modules-list a:link, #modules-list a:visited{
		padding:7px 10px;
	}

	#modules-list a:hover{
		
	}
	#modules-list span, .round-box{
		position:relative;
		padding:7px 20px;
		color:white;
		background:url(files/i/lc.png) left top no-repeat;
	}

	#modules-list span .rc, .round-box .rc{
		position:absolute;
		width:15px;
		height:30px;
		top:0px;
		right:0px;
		background:url(files/i/rc.png) left top no-repeat;
	}
	
	#body-content{
		margin-top:30px;
	}
	
	#auth-block{
		float:right;
		margin-top:10px;
		margin-right:10px;
		position:relative;
		z-index:1100;
	}
	
	#auth-block a:link, #auth-block a:visited{
		display:block;
		float:left;
		margin-left:20px;
		font:normal 13px Arial;
	}
	
	#auth-block a:hover{
		color:red;
	}
	
	#auth-block #pass-change-form{
		position:absolute; 
		top:20px; 
		right:0px;
		background:white;
		padding:20px 40px;
		border:1px dashed #999;
	}
	
	#auth-block #pass-change-form div{
		margin-bottom:5px;
	}
	
	#auth-block #pass-change-form div.h{
		color:#999;
		font:normal 11px Arial;
	}
	</style>
	<title><?='MyCMS &mdash; '.$api->active->title?></title>
</head>
<body>
	
	
		<div id="auth-block">
			<a href="#сменить логин и пароль" onclick="return toggleChangePassForm(this)">изменить пароль</a> 
			<a href="?exit" onclick="if(!confirm('Вы действительно хотите выйти?')) return false;">выход</a>
			<div id="pass-change-form" style="display:none;">
				<div class="h">логин</div>
				<div><input type="text" id="new-login" value="<?=$_SESSION['cms_root_auth']['u']?>"></div>
				<div class="h">пароль</div>
				<div><input type="password" id="new-pass"></div>
				<div><button id="new-btn" class="small-button" onclick="return changeAuth()">сменить</button></div>
			</div>
		</div>
		<!--<div class="date-block" style="float:right;">
		<div class="text-part">Сегодня <?=$_days[date('w')]?> &mdash;</div>
		<div class="day"><?=date('d')?></div>
		<div class="month-and-year">
			<div class="month"><?=$_months[date('n')]?></div>
			<div class="year"><?=date('Y')?></div>
		</div>
		<div class="clear"></div></div>-->
	<h1><?=$api->active->title?></h1>
	<div class="hint"><?=$api->active->info?></div><br />
	<div id="modules-list"><?=$api->initModulesList()?></div>
	<div id="body-content">
		<?=$api->initHTML()?>
		<div id="oldSort"></div>
		<div id="newSort"></div>
	</div>
	<div id="selectedCatalogID" style="display: none;"></div>
<script type="text/javascript">
function toggleChangePassForm(a){
	var a = $(a);
	var form = $('#pass-change-form');
	if(form.is(':hidden')){
		a.text('не изменять пароль');
		form.slideDown('fast');
	}else{
		a.text('изменить пароль');
		form.slideUp('fast');
	}
	return false;
}

function changeAuth(){
	var btn = $('#new-btn').text('загрузка..').attr({disabled:'disabled'});
	$.post(ajaxFile, {'changeAuth[l]':$('#new-login').val(), 'changeAuth[p]':$('#new-pass').val()}, function(status){
		btn.text('сменить').removeAttr('disabled');
		if(status=='ok'){
			location.reload();
		}else alert('Ошибка изменения. Проверьте права на директорию /cms/files/cache/');
	});
	return false;
}
</script>
</body>
</html>
