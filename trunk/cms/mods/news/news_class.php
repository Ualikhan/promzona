<?class news extends api{
	public $title, $news, $class_id, $companies, $pages, $on_page, $object, $root_id;

	function __construct(){
		parent::__construct();
		$this->root_id = 843;
		$this->class_id = 8;
		$this->title = 'Новости проекта';
		$this->on_page = isset($_GET['on_page']) && is_numeric($_GET['on_page']) ? $_GET['on_page'] : 10;
		$this->pages = $this->getPaginationPages();
		$this->news = $this->getNews();
		// $this->companies = $this->getCompanies();
	}

	#Вывод страницы
	function getPage(){
		if (!empty($_GET['action']) && is_numeric($id = $_GET['action']) && ($this->object = $this->objects->getFullObject($id)) && ($this->object['class_id']==$this->class_id)){
			include_once(_MODS_ABS_.'/company_news/news_one.html');
		}else{
			include_once(_MODS_ABS_.'/news/news.html');
		}
	}

	#Получаем список новостей
	function getNews(){
		return $this->objects->getFullObjectsListByCLass($this->root_id, $this->class_id, "AND o.active = '1' ORDER BY c.field_19 DESC LIMIT ".$this->pages['start'].", ".$this->pages['on_page']);
	}

	#Получаем список компаний
	function getCompanies(){
		if (!$comps = $this->objects->getFullObjectsListByClass(303,33)) return false;
		$out = array();
		foreach ($comps as $k=>$c){
			$out[$c['id']] = $c;
		}
		return $out;
	}

	#Получаем страницы для пагинации
	function getPaginationPages(){
		$count = $this->objects->getObjectsCount($this->root_id,$this->class_id);
		if(!isset($_REQUEST['pg']) || !is_numeric($current_page = $_REQUEST['pg']) || $current_page>ceil($count/$this->on_page)) $current_page = 1;
		return $this->objects->pagination($count, $current_page, $this->on_page);
	}

	#Изменяем колличество просмотров объявления
	function updateViews(){
		if (empty($this->object)) return false;
		$views = $this->object['Просмотры'];
		$new_views = (int)$views + 1;
		if ($this->db->update('class_'.$this->class_id, array('field_219'=>$new_views), "WHERE `object_id`='".$this->object['id']."'")) return $new_views;
	}

}?>