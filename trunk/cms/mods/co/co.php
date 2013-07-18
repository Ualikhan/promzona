<?include_once 'cms/public/api.php'; 

$api->header = '/general/header.html';
$api->template = '/../general/promzona.html';
$api->footer = '/general/footer.html';

# ИНИЦИАЛИЗИРУЕМ КЛАСС
include_once _MODS_ABS_.'/co/co_class.php';
$coms = new co();

# ПОДКЛЮЧАЕМ JS
$api->scripts = array(
	'<script src="'._WWW_.'/js/cabinet.js"></script>'
);

# ПОДКЛЮЧАЕМ СТИЛИ
$api->styles = array(
	'<link rel="stylesheet" href="'._WWW_.'/css/cabinet.css" type="text/css" >'
);

$api->header(array('page-title'=>$coms->title));

$coms->getPage();

$api->footer();?>