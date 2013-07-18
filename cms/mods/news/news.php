<?include_once 'cms/public/api.php'; 

$api->header = '/general/header.html';
$api->template = '/../general/pages.html';
$api->footer = '/general/footer.html';

# ИНИЦИАЛИЗИРУЕМ КЛАСС
include_once _MODS_ABS_.'/news/news_class.php';
$news = new news();

$api->header(array('page-title'=>$news->title));

$news->getPage();
$news->updateViews();

$api->footer();?>