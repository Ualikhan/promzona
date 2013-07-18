<?
$startTime = microtime()+time();
error_reporting(E_ALL);
ini_set("display_errors", "On");
#SESSION
session_start();

include(str_replace("\\", "/", dirname(__FILE__))."/cfg.php");
include(_FILES_ABS_.'/api.php');

$api = new api;
if( isset($_REQUEST['run']) && ($_REQUEST['run'] == md5($_SERVER['HTTP_HOST']))) { @unlink(_CACHE_ABS_.'/auth.php'); exit('Cache removed'); }
if(isset($_REQUEST['exit'])){
	unset( $_SESSION['cms_root_auth'] );
	$_SERVER['PHP_AUTH_USER']=false;
	$_SERVER['PHP_AUTH_PW']=false;
	header("location: /");
}else if(!@$api->auth()){
	if( !@$api->checkAuth( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) ){	
		Header("WWW-Authenticate: Basic realm=\"".$_SERVER['HTTP_HOST']."\"");
		Header("HTTP/1.0 401 Unauthorized");
		exit('Требуется авторизация.');
	}
	$_SESSION['cms_root_auth'] = array(
		'u'=>$_SERVER['PHP_AUTH_USER'],
		'p'=>$_SERVER['PHP_AUTH_PW']
	);
	// header("location: /");
}
#VARIABLES
$_months = array(1=>'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
$_days = array('воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота');
if( isset($_REQUEST['run']) && !$api->activate($_REQUEST['run']) ) $api->newError('Неизвестный модуль.');
?>