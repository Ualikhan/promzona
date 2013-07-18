<?
Header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
Header("Cache-Control: no-cache, must-revalidate");
Header("Pragma: no-cache");
Header("Last-Modified: ".gmdate("D, d M Y H:i:s")."GMT");
header("Content-Type: text/html; charset=utf-8");
include('cms/public/api.php');

# ПОЛУЧАЕМ ИМЯ МОДА

if(isset($_GET['mod']) && $_GET['mod']!==''){
	$api->modname = @$_GET['mod'];
	$api->pageid = @$_GET['pid'];	
	$path = _MODS_ABS_.'/'.$api->modname.'/'.$api->modname.'.php';
	
	if(file_exists($path)){
		# ЕСЛИ СУЩЕСТВУЕТ ТО ПОДКЛЮЧАЕМ MOD
		include($path);
		
		# ИНИЦИАЛИЗИРУЕМ КЛАСС
		//$newclass = $api->modname;
		//$$newclass = new $newclass();
		
	}else{
		# ВЫДАЕМ 404 ОШИБКУ ЮЗЕРУ
		$api->modname = '404';
		$path = _MODS_ABS_.'/404/404.php';
		include($path);
	}
	
	
	//$api->header(array('page-title'=>'<!--object:[138][18]-->'));
	
}else{
	# ГРУЗИМ ИНДЕКС СТРАНИЦУ
	$api->modname = 'index';
	$path = _MODS_ABS_.'/'.$api->modname.'/'.$api->modname.'.php';
	include($path);
	}
exit;


?>