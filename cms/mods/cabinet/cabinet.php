<?
if (empty($_SESSION['u'])) header("location: /login/");
if (($_SESSION['u']['role'] == 'moderator') && ($_SERVER['PHP_SELF']!='/print.php')) header("location: /moderator/");
include_once 'cms/public/api.php';

# ОСНОВНОЙ КЛАСС
include_once _MODS_ABS_.'/cabinet/cabinet_class.php';
$cabinet = new cabinet();

# Если послан Ajax запрос
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	exit($cabinet->ajax());
}


$cabinet->header = '/cabinet/html/header.html';
$cabinet->template = '/cabinet/cabinet.html';
$cabinet->footer = '/general/footer.html';

# ЛОВИМ GET-ы
// $action = $_GET['action'];


# ПОДКЛЮЧАЕМ JS
$cabinet->scripts = array(
	'<script src="'._WWW_.'/js/cabinet.js"></script>'
);

# ПОДКЛЮЧАЕМ СТИЛИ
$cabinet->styles = array(
	'<link rel="stylesheet" href="'._WWW_.'/css/cabinet.css" type="text/css" >'
);



# ЗАГОЛОВОК СТРАНИЦЫ
$cabinet->header(array('page-title'=>$cabinet->title));
#Content
$cabinet->getPage();

$cabinet->footer();
?>