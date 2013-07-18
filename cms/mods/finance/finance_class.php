<?class finance extends api{

	public $title, $type;
	public $regions, $class_id, $root_id, $obj, $companies, $pages, $region, $on_page, $count, $types, $doType;

	function __construct(){
		parent::__construct();
		$this->class_id = 43;
		$this->root_id = 955;
		$this->getDopProperties();
		$this->regions = $this->objects->getFullObjectsListByClass(257,32);
		$this->types = $this->objects->getFieldP3(245, $this->class_id);
	}

	function getPage(){
		if (isset($_GET['order']) && file_exists(_MODS_ABS_.'/finance/'.$_GET['order'].'_order.html') && !empty($_GET['id'])){
			if ( is_numeric($id = $_GET['id']) && ($obj = $this->objects->getFullObject($id)) && ($obj['class_id']==$this->class_id) && ($obj['Тип']==$this->type) ){
				$this->sendOrder($obj);
				include_once(_MODS_ABS_.'/finance/'.$_GET['order'].'_order.html');
			}elseif ( is_array($id = $_GET['id']) && ($coms = $this->getCompaniesByIds($id)) ){
				$this->sendOrder($coms);
				include_once(_MODS_ABS_.'/finance/'.$_GET['order'].'_order.html');
			}else{
				include_once(_MODS_ABS_.'/finance/finance_cat.html');
			}
		}elseif (!empty($_GET['action']) && is_numeric($_GET['action']) && !empty($this->obj)){
			include_once(_MODS_ABS_.'/finance/finance_company.html');
		}else{
			include_once(_MODS_ABS_.'/finance/finance_cat.html');
		}
	}

	#Получаем свойства фильтрации
	function getDopProperties(){
		if (!empty($_GET['region'])) $this->region = $this->filterInput($_GET['region']);
		if (!empty($_GET['doType'])) $this->doType = $this->filterInput($_GET['doType']);
		$this->on_page = isset($_GET['on_page']) && is_numeric($_GET['on_page']) ? $_GET['on_page'] : 10;
	}

	#Получаем объект компании
	function getObj(){
		if (!empty($_GET['action']) && is_numeric($id = $_GET['action']) && ($obj = $this->objects->getFullObject($id)) && ($obj['class_id']==$this->class_id) /*&& ($obj['Тип деятельности']==$this->type) */){
			return $obj;
		}else{
			return false;
		}
	}

	#Получаем списко компаний по типу
	function getComs(){
		if (!empty($this->obj)) return false;
		$region = !empty($this->region) ? "AND c.field_227 LIKE '%".$this->region."%'" : "";
		$doType = !empty($this->doType) ? "AND c.field_245 = '".$this->doType."'" : "";
		return $this->objects->getFullObjectsListByClass($this->root_id, $this->class_id, "AND o.active = '1' AND c.field_229 = '".$this->type."' $region $doType ORDER BY o.sort LIMIT ".$this->pages['start'].", ".$this->pages['on_page']);
	}

	#Получаем колличество компаний по типу
	function getComsCount(){
		if (!empty($this->obj)) return false;
		$region = !empty($this->region) ? "AND c.field_227 LIKE '%".$this->region."%'" : "";
		$doType = !empty($this->doType) ? "AND c.field_245 = '".$this->doType."'" : "";
		$where = "LEFT JOIN `class_".$this->class_id."` as c ON o.id = c.object_id WHERE o.active = '1' AND c.field_229 = '".$this->type."' $region $doType";
		return $this->db->count("`objects` as o", $where);
	}

	#Получаем страницы для пагинации
	function getPaginationPages($count){
		if(!isset($_REQUEST['pg']) || !is_numeric($current_page = $_REQUEST['pg']) || ($current_page>ceil($count/$this->on_page))) $current_page = 1;
		return $this->objects->pagination($count, $current_page, $this->on_page);
	}

	#Ссылка пагинации
	function pgHref($i,$type = ''){
		$link = isset($_GET['region']) ? $_SERVER['REDIRECT_URL']."?region=".$this->region."&on_page=".$this->on_page."&pg=" : $_SERVER['REDIRECT_URL']."?pg=";
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

	#Получаем доп файлы
	function getFiles($id, $class_id = 5){
		return $this->objects->getFullObjectsListByCLass($id,$class_id);
	}

	#Получаем расширение файла
	function fileExt($filename){
		return @end(explode(".",$filename));
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

	#Получаем компании по массиву ID
	function getCompaniesByIds($ids = array()){
		if (empty($ids)) return false;
		$arr = array();
		foreach ($ids as $k=>$i){
			if (!is_numeric($k)) continue;
			$arr[] = $k;
		}
		$fields = $this->db->select("fields", "WHERE `class_id` = ".$this->class_id." ORDER BY sort");
		$where = "LEFT JOIN `class_".$this->class_id."` as c ON o.id = c.object_id WHERE o.active = '1' AND c.lang = 'ru' AND o.id IN (".join(", ", $arr).")";
		$coms = $this->db->select("`objects` as o", $where);
		$out = array();
		foreach ($coms as $k=>$ad){
			$out[$k] = array();
			foreach ($ad as $key=>$i){
				$object_field_name = explode("_",$key);
				if (isset($object_field_name[1]) && is_numeric($field_id = $object_field_name[1])) {
					foreach ($fields as $f){
						if ($f['id']==$field_id) {
							$out[$k][$f['name']] = $i;
							break;
						}
					}
				}else{
					$out[$k][$key] = $i;
				}
			}
		}
		return $out;
	}

	#Отправляем заявку
	function sendOrder($coms){
		if (empty($_POST['f']) || !is_array($fields = $_POST['f'])) return false;
		if (!$emails = $this->getEmails($coms)) return false;
		$string = $this->type == 1 ?'транспортировку':'финансирование';
		$html = array();
		$html[] = '<p>
			Благодарим Вас, за то что воспользовались услугами портала Promzona.kz.<br>
			На Ваш адрес поступила заявка на '.$string.', если желаете связаться с  клиентом, просим вас  связаться по следующим контактным даным:
		</p>';
		foreach($fields as $k=>$f ){
			$html[] = '<div><b>'.$k.':</b>'.$f.'</div>';
		}
		$html[] = '<p>
		Удачной Вам сделки,<br>
		С уважением,<br>
		Служба поддержки Promzona.kz<br>
		http://promzona.kz<br>
		Support@promzona.kz<br>
		</p>';
		$this->mail->from = 'order@'.$_SERVER['HTTP_HOST'];
		$this->mail->subject = 'Заявка с сайта '.$_SERVER['HTTP_HOST'];
		$this->mail->body = join("\n",$html);
		foreach ($emails as $e){
			$this->mail->to = $e;
			$this->mail->send($this->mail->to); 
		}
		header("location: /".$_GET['mod']."/");
	}

	#Получаем email для отправки
	function getEmails($coms){
		$out = array();
		if (!empty($coms['Электронная почта'])){
			$out[0] = $coms['Электронная почта'];
			return $out;
		}else{
			foreach ($coms as $c){
				$out[] = $c['Электронная почта'];
			}
		return $out;
		}
	}

}?>