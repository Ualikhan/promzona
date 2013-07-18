<?include_once 'cms/public/api.php'; 
$api->header = '/general/header.html';
$api->template = '/../general/pages.html';
$api->footer = '/general/footer.html';

# 

# ИНИЦИАЛИЗИРУЕМ КЛАСС
include_once _MODS_ABS_.'/catalog/catalog_class.php';
$catalog = new catalog();

$api->header(array('page-title'=>$catalog->title));

$catalog->getPage();

?>

<?$api->footer();?>