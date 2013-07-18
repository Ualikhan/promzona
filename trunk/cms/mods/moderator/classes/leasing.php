<?class leasing extends api{

	public $title, $tpl, $type;
	public $class_id, $root_id, $coms, $on_page, $pages, $desc, $doTypes;

	function __construct(){
		parent::__construct();
		$this->root_id = 955;
		$this->class_id = 43;
		$this->type = 0;
		$this->add();
		$this->edit();
		$this->doTypes = $this->objects->getFieldP3(245, $this->class_id);
		$this->getTemplate();
	}


	#Получаем шаблон
	function getTemplate(){
		if (!empty($_GET['do']) && ($_GET['do']=='edit') && (!empty($_GET['id']) && is_numeric($_GET['id'])) && ($obj = $this->objects->getFullObject($_GET['id'])) && ($obj['class_id']==$this->class_id)){
			$this->obj = $obj;
			$this->title = 'Редактирование лизинговой компании';
			$this->tpl = '/leasing_edit.html';
		}elseif (!empty($_GET['do']) && ($_GET['do']=='add')){
			$this->title = 'Добавление лизинговой компании';
			$this->tpl = '/leasing_add.html';
		}else{
			$this->on_page = isset($_GET['on_page']) && is_numeric($_GET['on_page']) ? $_GET['on_page'] : 10;
			$this->pages = $this->getPaginationPages($this->getCount());
			$this->coms = $this->getComs();
			$this->title = 'Список лизинговых компании';
			$this->tpl = '/leasing.html';
		}
	}

	#Получаем компании 
	function getComs(){
		return $this->objects->getFullObjectsListByCLass($this->root_id, $this->class_id, "AND o.active = '1' AND c.field_229 = '".$this->type."' ORDER BY o.sort LIMIT ".$this->pages['start'].", ".$this->pages['on_page']);
	}

	#Получаем колличество объявлений 
	function getCount(){
		return $this->objects->getObjectsCount($this->root_id, $this->class_id, "AND o.active = '1' AND c.field_229 = '".$this->type."'");
	}

	#Получаем страницы для пагинации
	function getPaginationPages($count){
		if(!isset($_REQUEST['pg']) || !is_numeric($current_page = $_REQUEST['pg']) || ($current_page>ceil($count/$this->on_page))) $current_page = 1;
		return $this->objects->pagination($count, $current_page, $this->on_page);
	}

	#Ссылка пагинации
	function pgHref($i,$type = ''){
		$link = isset($_GET['on_page']) ? $_SERVER['REDIRECT_URL']."?on_page=".$this->on_page."&pg=" : $_SERVER['REDIRECT_URL']."?pg=";
		switch ($type){
			case 'next' :
				return 'href="'.$link.intval($i+1).'"';
			break;
			case 'prev' :
				return 'href="'.$link.intval($i-1).'"';
			break;
			default:
				return 'href="'.$link.intval($i).'"';
			break;
		}
	}

	#Добавляем компанию 
	function add(){
		if (!isset($_POST['add']) || ($_POST['add']!='1')) return false;
		if (empty($_POST['f']) || !is_array($_POST['f']) || (!$f = $_POST['f'])) return false;
		$logo = !empty($f['Логотип']) ? $this->filterInput($f['Логотип']) : '';
		$object = array(
			"active"=>1,
			"head"=>$this->root_id,
			"name"=>$this->filterInput($f['Название']),
			"class_id"=>$this->class_id,
			"sort"=>time()
		);
		$fields = array(
			241 => $logo, //Логотип
			225 => $this->filterInput($f['Название']),
			227 => $this->filterInput($f['Регион']),
			229 => $this->type,
			245 => $this->filterInput($f['Тип деятельности']), 
			231 => $this->filterInput($f['Описание']), 
			233 => $this->filterInput($f['Контактное лицо']), 
			235 => join("\n", $f['phone']), 
			243 => $this->filterInput($f['Адрес']),
			237 => $this->filterInput($f['Электронная почта']),
			239 => $this->filterInput($f['Сайт компании']),
		);
		if ($head = $this->objects->createObjectAndFields($object,$fields)){
			if ($logo && file_exists('cms/uploads_temp/'.$logo)) rename('cms/uploads_temp/'.$logo, 'cms/uploads/'.$logo);
			if (isset($_POST['files']) && is_array($_POST['files']) && ($files = $_POST['files'])){
				$i = 0;
				foreach ($files as $file){
					if (file_exists('cms/uploads_temp/'.$file)){
						rename('cms/uploads_temp/'.$file, 'cms/uploads/'.$file);
						$f_obj = array(
							"active"=>1,
							"head"=>$head,
							"name"=>$this->filterInput($file),
							"class_id"=>5,
							"sort"=>time()+$i
						);
						$f_fields = array(
							10=>$file,
							11=>$file
						);
						$this->objects->createObjectAndFields($f_obj,$f_fields);
						$i++;
					}
				}
			}
			header("location: ".$_SERVER['REQUEST_URI']);
		}else return false;
	}

	#Редактируем компанию
	function edit(){
		if (!isset($_POST['edit']) || ($_POST['edit']!='1') || (empty($_GET['id']) || !is_numeric($_GET['id'])) || (!$obj = $this->objects->getFullObject($_GET['id'])) || ($obj['class_id'] != $this->class_id)) return false;
		$this->obj = $obj;
		if (empty($_POST['f']) || !is_array($_POST['f']) || (!$f = $_POST['f'])) return false;
		$logo = !empty($f['Логотип']) ? $this->filterInput($f['Логотип']) : '';
		$object = array(
			"id"=>$this->obj['id'],
			"active"=>1,
			"head"=>$this->root_id,
			"name"=>$this->filterInput($f['Название']),
			"class_id"=>$this->class_id,
			"sort"=>time()
		);
		$fields = array(
			241 => $logo, //Логотип
			225 => $this->filterInput($f['Название']),
			227 => $this->filterInput($f['Регион']),
			229 => $this->type,
			245 => $this->filterInput($f['Тип деятельности']), 
			231 => $this->filterInput($f['Описание']), 
			233 => $this->filterInput($f['Контактное лицо']), 
			235 => join("\n", $f['phone']), 
			243 => $this->filterInput($f['Адрес']),
			237 => $this->filterInput($f['Электронная почта']),
			239 => $this->filterInput($f['Сайт компании']),
		);
		if ($head = $this->objects->editObjectAndFields($object,$fields)){
			if ($logo && file_exists('cms/uploads_temp/'.$logo)) rename('cms/uploads_temp/'.$logo, 'cms/uploads/'.$logo);
			if (isset($_POST['files']) && is_array($_POST['files']) && ($files = $_POST['files'])){
				$i = 0;
				foreach ($files as $file){
					if (file_exists('cms/uploads_temp/'.$file)){
						rename('cms/uploads_temp/'.$file, 'cms/uploads/'.$file);
						$f_obj = array(
							"active"=>1,
							"head"=>$this->obj['id'],
							"name"=>$this->filterInput($file),
							"class_id"=>5,
							"sort"=>time()+$i
						);
						$f_fields = array(
							10=>$file,
							11=>$file
						);
						$this->objects->createObjectAndFields($f_obj,$f_fields);
						$i++;
					}
				}
				if ($dop_files = $this->objects->getFullObjectsListByClass($this->obj['id'],5)){
					foreach ($dop_files as $kf=>$df){
						if (!in_array($df['Ссылка'],$files)) $this->objects->deleteObject($df['id']);
					}
				}
			}elseif ($dop_files = $this->objects->getFullObjectsListByClass($this->obj['id'],5)){
				foreach ($dop_files as $kf=>$df){
					$this->objects->deleteObject($df['id']);
				}
			}
			header("location: ".$_SERVER['REQUEST_URI']);
		}else {
			return false;
		}
	}

	#Получаем размер файла
	function filesize_get($file){
		// проверяем существует ли файл 
		if(!file_exists($file)) return '0 б'; 
		//определяем размер файла 
		$filesize = filesize($file); 
		// Если размер переданного в функцию файла больше 1кб 
		if($filesize > 1024) { 
			$filesize = ($filesize/1024); 
			// если размер файла больше одного килобайта 
			// пересчитываем в мегабайтах 
			if($filesize > 1024){
				$filesize = ($filesize/1024); 
				// если размер файла больше одного мегабайта 
				// пересчитываем в гигабайтах 
				if($filesize > 1024) { 
					$filesize = ($filesize/1024); 
					$filesize = round($filesize, 1); 
					return $filesize." ГБ";    
				}else{ 
					$filesize = round($filesize, 1); 
					return $filesize." MБ";    
				}
			}else { 
				$filesize = round($filesize, 1); 
				return $filesize." Кб";
			}
		}else{
			$filesize = round($filesize, 1); 
			return $filesize." байт";    
		}
	} 

}?>