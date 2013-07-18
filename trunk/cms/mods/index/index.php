<?
// include_once 'cms/public/api.php'; 
$api->header = '/general/header_index.html';
$api->template = '/../general/pages.html';
$api->footer = '/general/footer.html';


# КЛАСС ГОРЯЧИХ ПРЕДЛОЖЕНИЙ
include_once _MODS_ABS_.'/hotitems/hotitems_class.php';
$hotitems = new Hotitems();

# КЛАСС КАТЕГОРИЙ
include_once _MODS_ABS_.'/categories/category_class.php';
$cats = new Categories();

# Тип объявлений
$type = '0';
$razdel_id_spec = 258;
$razdel_id_ob = 261;


# Если послан Ajax запрос
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	exit($cats->ajax());
}


// # КЛАСС НОВЫХ ОБЪЯВЛЕНИЙ
// include_once _MODS_ABS_.'/newitems/newitems_class.php';
// $newitems = new Newitems();

// # КЛАСС БАННЕРОВ
// include_once _MODS_ABS_.'/sidebanners/sidebanners_class.php';
// $sidebanners = new Sidebanners();

// # КЛАСС НОВОСТЕЙ
// include_once _MODS_ABS_.'/news/news_class.php';
// $news = new News();

# ЗАГОЛОВОК СТРАНИЦЫ
$api->header(array('page-title'=>'<!--object:[138][18]-->'));

echo $hotitems->showHotItems(1);

include_once(_MODS_ABS_.'/index/index.html');

$api->footer();

?>
