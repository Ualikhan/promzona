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
	/*$_SERVER['PHP_AUTH_USER']=false;
	$_SERVER['PHP_AUTH_PW']=false;*/
	header("location: /");
}else if(!@$api->auth()){
	if(!@$_POST || !@$api->checkAuth( $_POST['auth_admin_name'], $_POST['auth_admin_pw']) ){
		/*Header("WWW-Authenticate: Basic realm=\"".$_SERVER['HTTP_HOST']."\"");
		Header("HTTP/1.0 401 Unauthorized");*/
		header('Content-Type: text/html; charset=utf-8');
		?>
		<table width="100%" cellpadding="0" cellspacing="0" border="0" height="100%">
			<tr>
				<td valign="middle" align="center">
					<h2>Требуется авторизация.</h2>
					<?if(@$_POST){echo '<span style="color:red">Неверный логин или пароль</span>';}?>
					<form action="" method="post">
						<table cellpadding="5" cellspacing="0" border="1" width="250">
							<tr>
								<td width="100">Логин</td>
								<td><input width="130" type="text" name="auth_admin_name" value="<?=@$_POST['auth_admin_name']?>" /></td>
							</tr>
							<tr>
								<td>Пароль</td>
								<td><input width="130" type="password" name="auth_admin_pw" value="" /></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td><input type="submit" value="Аторизоваться" /></td>
							</tr>
						</table>
					</form>
				</td>
			</tr>
		</table>
		<?
		exit;
	}
	$_SESSION['cms_root_auth'] = array(
		'u'=>$_POST['auth_admin_name'],
		'p'=>$_POST['auth_admin_pw']
	);
	header("location: /");
}
#VARIABLES
$_months = array(1=>'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
$_days = array('воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота');
if( isset($_REQUEST['run']) && !$api->activate($_REQUEST['run']) ) $api->newError('Неизвестный модуль.');
?>