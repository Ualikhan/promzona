<?class company_info extends api{

public $title,$tpl;
public $object, $types, $class_id, $root_id, $finComs, $logComs, $msg;

	function __construct(){
		parent::__construct();
		$this->title = 'Информация о компании';
		$this->getTemplate();
		$this->class_id = 33;
		$this->root_id = 303;
		$this->types = $this->objects->getFullObjectsListByClass(240,7);
		$this->object = $this->objects->getFullObject($_SESSION['u']['id']);
		$this->finComs = $this->getFinLogComs(0); // компании финансирования и лизинга
		$this->logComs = $this->getFinLogComs(1); // компании логистики (транспартировки)
		$this->start();
	}

	#Вставляем шаблон взависимости роли пользователя
	function getTemplate(){
		switch ($_SESSION['u']['role']){
			case 'user':
				$this->tpl = '/company_info_create.html';
			break;
			case 'company':
				$this->tpl = '/company_info.html';
			break;
			case 'business':
				$this->tpl = '/company_info_business.html';
			break;
			default:
				$this->tpl = '/empty.html';
			break;
		}
	}

	#Получаем пользователя
	function getUser($subject){
		return $subject;
	}

	#Добаляем компанию
	function addCompany(){
		if (empty($_POST)) return false;
		if (empty($_POST['f']) || !is_array($f = $_POST['f'])) {return false;};
		$logo = !empty($_POST['comapnyLogo']) ? $this->filterInput($_POST['comapnyLogo']) : '';
		$object = array(
			"active"=>1,
			"head"=>$this->root_id,
			"name"=>$this->filterInput($f['Название компании']),
			"class_id"=>$this->class_id,
			"sort"=>time()
		);
		$fields = array(
			157 => 'company',//Роль
			123 => $this->object['Email'], //email пользователя
			125 => $this->object['Пароль'], //Пароль пользователя
			127 => $this->filterInput($f['Название компании']), //Название компании
			129 => $this->filterInput($f['Тип деятельности']),
			131 => $this->filterInput($f['Описание']), 
			133 => $logo, //Логотип
			135 => $this->filterInput($f['Имя контактного лица']),
			137 => $this->filterInput($f['Должность']),
			139 => $this->filterInput($f['Регион']),
			141 => $this->filterInput($f['Адрес']),
			143 => join("\n", $f['phone']),
			145 => $this->filterInput($f['Сайт компании']),
		);
		if ($head = $this->objects->createObjectAndFields($object,$fields)){
			if ($logo && file_exists('cms/uploads_temp/'.$logo)) rename('cms/uploads_temp/'.$logo, 'cms/uploads/'.$logo);
			$this->changeUserToCompany($head,$fields);
			header("location: ".$_SERVER['REQUEST_URI']);
			// return true;
		}else return false;
	}

	#Меняем информацию о компании
	function editCompany(){
		if (empty($_POST)) return false;
		if (empty($_POST['f']) || !is_array($f = $_POST['f'])) {return false;};
		$logo = !empty($_POST['comapnyLogo']) ? $this->filterInput($_POST['comapnyLogo']) : '';
		$object = array(
			"id"=>$this->object['id'],
			"active"=>1,
			"head"=>$this->object['head'],
			"name"=>$this->filterInput($f['Название компании']),
			"class_id"=>$this->object['class_id'],
			"sort"=>time()
		);
		$fields = array(
			157 => 'company',//Роль
			123 => $this->object['Email'], //email пользователя
			125 => $this->object['Пароль'], //Пароль пользователя
			127 => $this->filterInput($f['Название компании']), //Название компании
			129 => $this->filterInput($f['Тип деятельности']),
			131 => $this->filterInput($f['Описание']), 
			133 => $logo, //Логотип
			135 => $this->object['Имя контактного лица'],
			137 => $this->object['Должность'],
			139 => $this->object['Регион'],
			141 => $this->object['Адрес'],
			143 => $this->object['Телефон'],
			145 => $this->object['Сайт компании'],
		);
		if ($this->objects->editObjectAndFields($object,$fields)){
			if (($logo) && file_exists('cms/uploads_temp/'.$logo)) rename('cms/uploads_temp/'.$logo, 'cms/uploads/'.$logo);
			$this->changeUserToCompany($this->object['id'],$fields);
			$this->msg = 'Изменения сохранены';
			// header("location: ".$_SERVER['REQUEST_URI']);
			// return true;
		}else {
			$this->msg = 'Ошибка! Изменения не были сохранены';
			return false;
		}
	}

	#Меняем информацию о компании для бизнес пакета
	function editCompanyBusiness(){
		if (empty($_POST)) return false;
		if (empty($_POST['f']) || !is_array($f = $_POST['f'])) {return false;};
		$logo = !empty($_POST['comapnyLogo']) ? $this->filterInput($_POST['comapnyLogo']) : '';
		$companyPrice = !empty($_POST['companyPrice'][0]) ? $this->filterInput($_POST['companyPrice'][0]) : '';
		$object = array(
			"id"=>$this->object['id'],
			"active"=>1,
			"head"=>$this->object['head'],
			"name"=>$this->filterInput($f['Название компании']),
			"class_id"=>$this->object['class_id'],
			"sort"=>time()
		);
		$fields = array(
			157 => 'business',//Роль
			123 => $this->object['Email'], //email пользователя
			125 => $this->object['Пароль'], //Пароль пользователя
			127 => $this->filterInput($f['Название компании']), //Название компании
			129 => $this->filterInput($f['Тип деятельности']),
			131 => $this->filterInput($f['Описание']), 
			133 => $logo, //Логотип
			135 => $this->object['Имя контактного лица'],
			137 => $this->object['Должность'],
			139 => $this->object['Регион'],
			141 => $this->object['Адрес'],
			143 => $this->object['Телефон'],
			145 => $this->object['Сайт компании'],
			247 => $this->filterInput($f['Финансирование и лизинг']),
			249 => $this->filterInput($f['Транспортировка']),
			253 => $this->filterInput($companyPrice),

		);
		if ($this->objects->editObjectAndFields($object,$fields)){
			if (($logo) && file_exists('cms/uploads_temp/'.$logo)) rename('cms/uploads_temp/'.$logo, 'cms/uploads/'.$logo);
			if (($companyPrice) && file_exists('cms/uploads_temp/'.$companyPrice)) rename('cms/uploads_temp/'.$logo, 'cms/uploads/'.$companyPrice);
			$this->changeUserToCompany($this->object['id'],$fields);
			$this->msg = 'Изменения сохранены';
			// header("location: ".$_SERVER['REQUEST_URI']);
			// return true;
		}else {
			$this->msg = 'Ошибка! Изменения не были сохранены';
			return false;
		}
	}

	#Меняем сессию пользователя на компанию
	function changeUserToCompany($id, $fields){
		unset($_SESSION['u']);
		$_SESSION['u'] = array();
		$_SESSION['u']['id'] = $id;
		$_SESSION['u']['role'] = $fields[157];
		$_SESSION['u']['email'] = $fields[123];
		$_SESSION['u']['telephone'] = $fields[143];
		$_SESSION['u']['fullname'] = $fields[127];
		$_SESSION['u']['region'] = $fields[139];
		$_SESSION['u']['address'] = $fields[141];
	}

	function start(){
		if ($this->tpl == '/company_info_create.html') return $this->addCompany();
		elseif ($this->tpl == '/company_info.html') return $this->editCompany();
		elseif ($this->tpl == '/company_info_business.html') return $this->editCompanyBusiness();
	}

	#Получаем статус компании
	function companyStatus(){
		if ($this->object['active'] == 1) return '<i class="icon-ok"></i> Активна';
		else return '<i class="icon-warning-sign icon-yellow"></i> На модерации';
	}

	#Получаем компании финанстирования или логистики
	function getFinLogComs($type, $id=955, $class_id=43){
		return $this->objects->getFulLObjectsListByClass($id, $class_id, "AND o.active = '1' AND c.field_229 = '$type' ORDER BY c.field_225 ASC");
	}

}?>