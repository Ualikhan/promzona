<?class archive extends api{

public $title,$tpl;
public $ads, $class_id, $sort;

	function __construct(){
		parent::__construct();
		$this->tpl = '/archive.html';
		$this->title = 'Архив';
		include_once (_MODS_ABS_.'/cabinet/classes/ads.php');
		$this->ads = new ads();
		$this->class_id = $this->ads->class_id;
		$this->sort = $this->ads->sort;
	}

	function getAdsCount(){
		return $this->objects->getObjectsCount(-1,$this->class_id, "AND o.active = '1' AND c.field_161 = '".$this->ads->user['id']."' AND c.field_102 = 2");
	}

	function getAdsCountByType($i){
		if (!is_numeric($i)) return '0';
		return $this->objects->getObjectsCount(-1,$this->class_id, "AND o.active = '1' AND c.field_161 = '".$this->ads->user['id']."' AND c.field_102 = 2 AND c.field_100 = '$i'");
	}

	#Получаем страницы для пагинации
	function getPages(){
		$sql = $this->ads->getFilterSql();
		$count = $this->objects->getObjectsCount(-1,$this->class_id, "AND o.active = '1' AND c.field_161 = '".$this->ads->user['id']."' AND c.field_102 = '2' $sql");
		if(!isset($_REQUEST['pg']) || !is_numeric($current_page = $_REQUEST['pg']) || $current_page>ceil($count/$this->on_page)) $current_page = 1;
		return $this->objects->pagination($count, $current_page, $this->ads->on_page);
	}

	#Получаем таблицу объявлений объявляения 
	function getAdsList($start,$limit){
		$sql = $this->ads->getFilterSql();
		if (!$list = $this->objects->getFullObjectsListByCLass(-1,$this->class_id,"AND o.active = '1' AND c.field_161 = '".$this->ads->user['id']."' AND c.field_102 = '2' $sql LIMIT $start, $limit")) return false;
		return $list;
	}

	#ССылка сортировки
	function sortHref($str){
		return $this->ads->sortHref($str);
	}




}?>