<?class moderator extends api{

	public $ac, $title, $tpl, $subject;
	public $ads_count, $coms_count, $news_count, $bills_count, $counts;

	function __construct(){
		parent::__construct();
		$this->counts = $this->getCounts();
		$this->activateCls();
	}

	#Подключаем нужный класс
	function activateCls(){
		if (!empty($_REQUEST['action']) && ($action = $_REQUEST['action']) && ($file = _MODS_ABS_.'/moderator/classes/'.$action.'.php') && file_exists($file)){
			include_once($file);
			$this->ac = new $action();
			// $this->tpl = '/'.$action.'.html';
			$this->tpl = $this->ac->tpl;
		}else if (!isset($_REQUEST['action']) && ($file = _MODS_ABS_.'/moderator/classes/wellcome.php') && file_exists($file)){
			include_once($file);
			$this->ac = new wellcome();
			$this->tpl = '/wellcome.html';
		}else{
			header("location: /404/");
		}
		$this->title = $this->ac->title;
		$this->subject = $this->objects->getFullObject($_SESSION['u']['id']);
	}

	#Ajax функции
	function ajax(){

	}

	#Получаем содержимое страницы
	function getPage(){
		include_once(_MODS_ABS_.'/moderator/html'.$this->tpl);
	}

	#Получаем меню
	function getMenuArr(){
		return array(
			0 => array(
				'Название'=>'Кабинет модератора',
				'Ссылка'=>'',
			),
			1 => array(
				'Название'=>'Объявления',
				'Ссылка'=>'ads',
				'count'=>$this->ads_count
			),
			2 => array(
				'Название'=>'Компании',
				'Ссылка'=>'coms',
				'count'=>$this->coms_count
			),
			3 => array(
				'Название'=>'Новости компаний',
				'Ссылка'=>'news',
				'count'=>$this->news_count
			),
			4 => array(
				'Название'=>'Счета',
				'Ссылка'=>'bills',
				'count'=>$this->bills_count
			),
			5 => array(
				'Название'=>'Лизинг',
				'Ссылка'=>'leasing',
			),
			6 => array(
				'Название'=>'Транспортировка',
				'Ссылка'=>'logistics',
			),
		);
	}

	#Получаем колличества всех объектов на модерации и присваиваем свойствам
	function getCounts(){
		$table = "`objects` as o";
		$this->ads_count = $this->db->count($table, "LEFT JOIN `class_31` as c ON o.id = c.object_id WHERE o.active = '1' AND c.lang = 'ru' AND c.field_102 = '0'");
		$this->coms_count = $this->objects->getObjectsCount(303, 33, "AND o.active = '1' AND c.field_259 = '0'");
		$this->news_count = $this->objects->getObjectsCount(-1, 8, "AND o.active = '1'  AND c.field_251 = '0' AND o.head<>'843'");
		$this->bills_count = $this->db->count($table, "LEFT JOIN `class_41` as c ON o.id = c.object_id WHERE o.active = '1' AND c.lang = 'ru' AND c.field_197 = '0'");
		return array(
			'ads'=>array(
				'count'=>$this->ads_count,
				'name'=>'объявлений',
			),
			'coms'=>array(
				'count'=>$this->coms_count,
				'name'=>'компаний',
			),
			'news'=>array(
				'count'=>$this->news_count,
				'name'=>'новостей',
			),
			'bills'=>array(
				'count'=>$this->bills_count,
				'name'=>'счетов',
			),
		);
	}

}?>