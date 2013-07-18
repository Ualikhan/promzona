<?include_once 'cms/public/api.php'; 

$api->header = '/general/header.html';
$api->template = '/../general/promzona.html';
$api->footer = '/general/footer.html';


# ИНИЦИАЛИЗИРУЕМ КЛАСС
include_once _MODS_ABS_.'/finance/finance_class.php';
$coms = new finance();
$coms->title = 'Каталог транспортных компаний';
$coms->type = 1;
$coms->obj = $coms->getObj();
$coms->count = $coms->getComsCount();
$coms->pages = $coms->getPaginationPages($coms->count);
$coms->companies = $coms->getComs();

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