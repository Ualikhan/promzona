<?
if (empty($_SESSION['u'])) header("location: /login/");
if ($_SESSION['u']['role'] != 'moderator') header("location: /cabinet/");
include_once 'cms/public/api.php';

# ОСНОВНОЙ КЛАСС
include_once _MODS_ABS_.'/moderator/moderator_class.php';
$cabinet = new moderator();

# Если послан Ajax запрос
// if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	// exit($cabinet->ajax());
// }


$cabinet->header = '/moderator/m_header.html';
$cabinet->template = '/moderator/moderator.html';
$cabinet->footer = '/general/footer.html';

# ЛОВИМ GET-ы
// $action = $_GET['action'];


# ПОДКЛЮЧАЕМ JS
$cabinet->scripts = array(
	'<script src="'._WWW_.'/js/cabinet.js"></script>',
	'<script src="'._WWW_.'/js/moderation.js"></script>'
);

# ПОДКЛЮЧАЕМ СТИЛИ
$cabinet->styles = array(
	'<link rel="stylesheet" href="'._WWW_.'/css/cabinet.css" type="text/css" >',
	'<link rel="stylesheet" href="'._WWW_.'/css/moderation.css" type="text/css" >'
);



# ЗАГОЛОВОК СТРАНИЦЫ
$cabinet->header(array('page-title'=>$cabinet->title));

#Content
$cabinet->getPage();

$cabinet->footer();
?>