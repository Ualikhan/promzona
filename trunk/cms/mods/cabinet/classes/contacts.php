<?class contacts extends api{

public $title,$tpl, $msg;
public $phones, $object, $class_id, $class_id_company, $class_id_contact, $class_id_filial;

	function __construct(){
		parent::__construct();
		$this->title = 'Контактная информация';
		$this->class_id = 14;
		$this->class_id_company = 33;
		$this->class_id_contact = 37;
		$this->class_id_filial = 39;
		$this->changeContact();
		$this->changeCompanyContacts();
		$this->changeContactsBusiness();
		$this->object = $this->objects->getFullObject($_SESSION['u']['id']);
		$this->phones = explode("\n",$this->object['Телефон']);
		$this->getTemplate();
	}

	#Вставляем шаблон взависимости роли пользователя
	function getTemplate(){
		switch ($_SESSION['u']['role']){
			case 'user':
				$this->tpl = '/contacts_user.html';
			break;
			case 'company':
				$this->tpl = '/contacts_company.html';
			break;
			case 'business':
				$this->tpl = '/contacts_business.html';
			break;
			default:
				$this->tpl = '/empty.html';
			break;
		}
	}

	#Изменяем контакты
	function changeContact(){
		if (empty($_POST)) return false;
		if ($_SESSION['u']['role'] != 'user') return false;
		if (empty($_POST['f']) || !is_array($f = $_POST['f'])) return false;
		$object = array(
			"id"=>$_SESSION['u']['id'],
			"class_id"=>$this->class_id,
			"name"=>$this->filterInput($f['Имя контактного лица']),
		);
		$fields = array(
			"40"=>$this->filterInput($f['Имя контактного лица']),
			"43"=>$this->filterInput($f['Регион']),
			"44"=>$this->filterInput(join("\n",$f['phone'])),
		);
		if ($this->objects->editObjectAndFields($object,$fields)){
			$_SESSION['u']['fullname'] = $this->filterInput($f['Имя контактного лица']);
			$_SESSION['u']['region'] = $this->filterInput($f['Регион']);
			$_SESSION['u']['telephone'] = $this->filterInput(join("\n",$f['phone']));
			$this->msg = 'Изменения сохранены';
			return true;
		}else{
			$this->msg = 'Ошибка! Изменения не были сохранены.';
			return false;
		}
	}

	#Изменяем контакты компании
	function changeCompanyContacts(){
		if (empty($_POST)) return false;
		if ($_SESSION['u']['role']!='company') return false;
		if (empty($_POST['f']) || !is_array($f = $_POST['f'])) return false;
		$object = array(
			"id"=>$_SESSION['u']['id'],
			"class_id"=>$this->class_id_company,
			"name"=>$this->filterInput($f['Имя контактного лица']),
		);
		$fields = array(
			"135"=>$this->filterInput($f['Имя контактного лица']),
			"137"=>$this->filterInput($f['Должность']),
			"139"=>$this->filterInput($f['Регион']),
			"141"=>$this->filterInput($f['Адрес']),
			"145"=>$this->filterInput($f['Сайт компании']),
			"165"=>$this->filterInput($f['url']),
			"143"=>$this->filterInput(join("\n",$f['phone'])),
		);
		if ($this->objects->editObjectAndFields($object,$fields)){
			$_SESSION['u']['region'] = $this->filterInput($f['Регион']);
			$_SESSION['u']['address'] = $this->filterInput($f['Адрес']);
			$_SESSION['u']['telephone'] = $this->filterInput(join("\n",$f['phone']));
			// header("location: ".$_SERVER['REQUEST_URI']);
			$this->msg = 'Изменения сохранены';
			return true;
		}else{
			$this->msg = 'Ошибка! Изменения не были сохранены.';
			return false;
		}
	}

	#Изменяем контакты бизнес пакета
	function changeContactsBusiness(){
		if (empty($_POST)) return false;
		if ($_SESSION['u']['role']!='business') return false;
		if (empty($_POST['f']) || !is_array($f = $_POST['f'])) return false;
		$object = array(
			"id"=>$_SESSION['u']['id'],
			"class_id"=>$this->class_id_company,
			"name"=>$this->filterInput($f['Имя контактного лица']),
		);
		$fields = array(
			"135"=>$this->filterInput($f['Имя контактного лица']),
			"137"=>$this->filterInput($f['Должность']),
			"139"=>$this->filterInput($f['Регион']),
			"141"=>$this->filterInput($f['Адрес']),
			"145"=>$this->filterInput($f['Сайт компании']),
			"165"=>$this->filterInput($f['url']),
			"143"=>$this->filterInput(join("\n",$f['phone'])),
		);
		if ($this->objects->editObjectAndFields($object,$fields)){
			$_SESSION['u']['region'] = $this->filterInput($f['Регион']);
			$_SESSION['u']['address'] = $this->filterInput($f['Адрес']);
			$_SESSION['u']['telephone'] = $this->filterInput(join("\n",$f['phone']));
			if (!empty($_POST['contacts']) && is_array($contacts = $_POST['contacts'])){
				$c_ids = $this->getObjectIdsByClass($_SESSION['u']['id'],$this->class_id_contact);
				$not_delete_c = array();
				foreach ($contacts as $key=>$c){
					if (empty($c['id']) || !is_numeric($c['id'])){
						$this->addContact($c);
					}else{
						$this->editContact($c);
						$not_delete_c[] = $c['id'];
					}
				}
				// print_r($c_ids);exit;
				foreach ($c_ids as $idc){
					if (!in_array($idc,$not_delete_c)) $this->objects->deleteObject($idc);
				}
			}
			if (!empty($_POST['branches']) && is_array($branches = $_POST['branches'])){
				$b_ids = $this->getObjectIdsByClass($_SESSION['u']['id'],$this->class_id_filial);
				$not_delete_b = array();
				foreach ($branches as $key=>$c){
					if (empty($c['id']) || !is_numeric($c['id'])){
						$this->addBranche($c);
					}else{
						$this->editBranche($c);
						$not_delete_b[] = $c['id'];
					}
				}
				foreach ($b_ids as $idb){
					if (!in_array($idb,$not_delete_b)) $this->objects->deleteObject($idb);
				}
			}
			$this->msg = 'Изменения сохранены';
			return true;
		}else{
			$this->msg = 'Ошибка! Изменения не были сохранены.';
			return false;
		}
	}

	#Получаем список контактных лиц
	function getContacts($id){
		return $this->objects->getFullObjectsListByClass($id,$this->class_id_contact);
	}

	#Получаем список филиалов
	function getFilials($id){
		return $this->objects->getFullObjectsListByClass($id,$this->class_id_filial);
	}

	#добавляем контактное лицо
	function addContact($f){
		$object = array(
			"active"=>1,
			"head"=>$_SESSION['u']['id'],
			"name"=>$this->filterInput($f['name']),
			"class_id"=>$this->class_id_contact,
			"sort"=>time()
		);
		$fields = array(
			173=>$this->filterInput($f['name']),
			175=>$this->filterInput($f['position']),
			177=>$this->filterInput(join("\n",$f['phone'])),
			179=>$this->filterInput($f['email']),
		);
		return $this->objects->createObjectAndFields($object,$fields);
	}

	#Изменяем контактное лицо
	function editContact($f){
		$object = array(
			"id"=>$f['id'],
			"name"=>$this->filterInput($f['name']),
			"class_id"=>$this->class_id_contact,
		);
		$fields = array(
			173=>$this->filterInput($f['name']),
			175=>$this->filterInput($f['position']),
			177=>$this->filterInput(join("\n",$f['phone'])),
			179=>$this->filterInput($f['email']),
		);
		return $this->objects->editObjectAndFields($object,$fields);
	}

	#Добавляем филиал
	function addBranche($f){
		$object = array(
			"active"=>1,
			"head"=>$_SESSION['u']['id'],
			"name"=>$this->filterInput($f['name']),
			"class_id"=>$this->class_id_filial,
			"sort"=>time()
		);
		$fields = array(
			181=>$this->filterInput($f['name']),
			183=>$this->filterInput($f['address']),
			185=>$this->filterInput(join("\n",$f['phone'])),
			187=>$this->filterInput($f['contactName']),
			189=>$this->filterInput($f['contactPosition']),
		);
		return $this->objects->createObjectAndFields($object,$fields);
	}

	#Изменяем Филиал
	function editBranche($f){
		$object = array(
			"id"=>$f['id'],
			"name"=>$this->filterInput($f['name']),
			"class_id"=>$this->class_id_filial,
		);
		$fields = array(
			181=>$this->filterInput($f['name']),
			183=>$this->filterInput($f['address']),
			185=>$this->filterInput(join("\n",$f['phone'])),
			187=>$this->filterInput($f['contactName']),
			189=>$this->filterInput($f['contactPosition']),
		);
		return $this->objects->editObjectAndFields($object,$fields);
	}

	#Получаем список id 
	function getObjectIdsByClass($id,$class_id){
		if (!$list = $this->objects->getObjectsListByClass($id,$class_id)) return array();
		$out = array();
		foreach ($list as $o){
			$out[] = $o['id'];
		}
		return $out;
	}

}?>