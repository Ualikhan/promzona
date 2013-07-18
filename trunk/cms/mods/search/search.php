<?include_once 'cms/public/api.php'; 
$api->header = '/general/header.html';
$api->template = '/../general/pages.html';
$api->footer = '/general/footer.html';

# 

# ИНИЦИАЛИЗИРУЕМ КЛАСС
include_once _MODS_ABS_.'/search/search_class.php';
$search = new search();

$api->header(array('page-title'=>$search->title));

$search->getPage();

?>

<?$api->footer();?>