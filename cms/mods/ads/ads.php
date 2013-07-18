<?include_once 'cms/public/api.php'; 

$api->header = '/general/header.html';
$api->template = '/ads_pages.html';
$api->footer = '/general/footer.html';

# ИНИЦИАЛИЗИРУЕМ КЛАСС
include_once _MODS_ABS_.'/ads/ads_class.php';
$ads = new ads();

$api->header(array('page-title'=>$ads->title));

$ads->getPage();

$api->footer();?>