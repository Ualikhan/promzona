<?class news_add extends api{

public $title,$tpl, $msg, $subject;

	function __construct(){
		parent::__construct();
		$this->title = 'Добавление новости';
		$this->class_id = 8;
		$this->getTemplate();
		$this->add();
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
				$this->tpl = '/news_add.html';
			break;
			default:
				$this->tpl = '/empty.html';
			break;
		}
	}

	#Добавление новости
	function add(){
		if ($_SESSION['u']['role'] != 'business') return false;
		if (empty($_POST)) {return false;}
		if (empty($_POST['f']) || !is_array($f = $_POST['f'])) {
			$this->msg = 'заполнена не вся инфа'; 
			return false;
		}
		$photo = !empty($_POST['photo']) ? $_POST['photo'] : '';
		$object = array(
			"active"=>1,
			"head"=>$_SESSION['u']['id'],
			"name"=>$this->filterInput($f['Название']),
			"class_id"=>$this->class_id,
			"sort"=>time()
		);
		$fields = array(
			19 => date("Y-m-d",time()),
			20 => $this->filterInput($f['Название']),
			21 => $this->filterInput($f['Анонс']),
			22 => $this->filterInput($f['Текст']),
			217 => $this->filterInput($photo),
			251 => '0',
		);
		if ($head = $this->objects->createObjectAndFields($object,$fields)){
			if (!empty($photo) && file_exists('cms/uploads_temp/'.$photo)) {
				rename('cms/uploads_temp/'.$photo, 'cms/uploads/'.$photo);
			}
			$this->msg = 'Добавлено';
			return true;
		}else return false;
	}




}?>