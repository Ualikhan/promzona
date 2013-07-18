<?include_once 'cms/public/api.php'; 

$api->header = '/general/header.html';
$api->template = '/../general/pages.html';
$api->footer = '/general/footer.html';

# ИНИЦИАЛИЗИРУЕМ КЛАСС
include_once _MODS_ABS_.'/map/map_class.php';
$map = new map();

$api->header(array('page-title'=>$map->title));

$map->getPage();

$api->footer();?>