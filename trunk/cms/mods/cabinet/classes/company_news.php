<?class company_news extends api{

public $title,$tpl, $news, $object;

	function __construct(){
		parent::__construct();
		$this->class_id = 8;
		$this->title = 'Новости компании';
		$this->news = $this->getNews();
		$this->getTemplate();
		$this->deleteNews();
		$this->editNewsObject();
	}

	#Вставляем шаблон взависимости роли пользователя
	function getTemplate(){
		switch ($_SESSION['u']['role']){
			// case 'user':
			// 	$this->tpl = '/news_empty.html';
			// break;
			case 'company':
				$this->tpl = '/news_empty.html';
			break;
			case 'business':
				if (!empty($_GET['subaction']) && is_numeric($id = $_GET['subaction'])){
					$this->object = $this->getNewsObject();
					$this->tpl = '/news_edit.html';
				}elseif (empty($this->news)){
					$this->tpl = '/news_empty_business.html';
				}else{ 
					$this->tpl = '/news.html';
				}
			break;
			default:
				$this->tpl = '/empty.html';
			break;
		}
	}

	#Получаем список новостей
	function getNews(){
		return $this->objects->getFullObjectsListByCLass($_SESSION['u']['id'],$this->class_id);
	}

	#Получаем объект новости
	function getNewsObject(){
		if (!empty($_GET['subaction']) && is_numeric($id = $_GET['subaction']) && ($obj = $this->objects->getFullObject($id)) && ($obj['class_id']==$this->class_id) && ($obj['head']==$_SESSION['u']['id'])){
			return $obj;
		}else{
			header ("location: /404/");
		}
	}

	#Удаляем новости
	function deleteNews(){
		if (empty($_POST['n'])) return false;
		if (!is_array($news = $_POST['n'])) return false;
		$out = array();
		foreach ($news as $k=>$n){
			if ($this->db->update("objects", array("active" => "0"), "WHERE `id` = '$k' "))
				$out[$k] ='Удалено';
			else $out[$k] = 'Нет';
		}
		header("location: ".$_SERVER['REQUEST_URI']);
	}

	#Редактируем новосоть
	function editNewsObject(){
		if (empty($_POST['f']) || !is_array($f = $_POST['f'])) return false;
		$object = array(
			"active"=>1,
			"head"=>$_SESSION['u']['id'],
			"id"=>$this->object['id'],
			"name"=>$this->filterInput($f['Название']),
			"class_id"=>$this->class_id,
			"sort"=>time()
		);
		$photo = !empty($_POST['photo']) ? $_POST['photo'] : $this->object['Изображение'];
		$fields = array(
			19=>$this->object['Дата'],
			20=>$this->filterInput($f['Название']),
			21=>$this->filterInput($f['Анонс']),
			22=>$this->filterInput($f['Текст']),
			217 => $this->filterInput($photo),
			251 => '0',
		);
		if ($this->objects->editObjectAndFields($object,$fields)){
			if (!empty($photo) && file_exists('cms/uploads_temp/'.$photo)) {
				rename('cms/uploads_temp/'.$photo, 'cms/uploads/'.$photo);
			}
			// return true;
			header("location: /cabinet/company_news/");
		}else{
			return false;
		}
	}

	#Статус новости
	function getStatus($n){
		$statuses = array(
			'<i class="icon-warning-sign icon-yellow"></i>',
			'<i class="icon-ok icon-grey"></i>',
			'<i class="icon-remove icon-grey"></i>',
		);
		return $statuses[$n];
	}

}?>