<?class cabinet extends api{
	
public $ac,$title,$tpl, $subject, $vars;

	function __construct(){
		parent::__construct();
		$this->activateCls();
	}

	function activateCls(){
		if (!empty($_REQUEST['action']) && ($action = $_REQUEST['action']) && ($file = _MODS_ABS_.'/cabinet/classes/'.$action.'.php') && file_exists($file)){
			include_once($file);
			$this->ac = new $action();
			// $this->tpl = '/'.$action.'.html';
			$this->tpl = $this->ac->tpl;
		}else if (empty($_REQUEST['action']) && ($file = _MODS_ABS_.'/cabinet/classes/wellcome.php') && file_exists($file)){
			include_once($file);
			$this->ac = new wellcome();
			$this->tpl = '/wellcome.html';
		}else{
			header("location: /404/");
		}
		$this->title = $this->ac->title;
		$this->subject = $this->objects->getFullObject($_SESSION['u']['id']);
		$this->checkBusinessPacket();
		$this->vars = $this->getVars();
	}

	#Ajax функции
	function ajax(){
		// print_r($_REQUEST);
		if (!empty($_REQUEST['goal']) && ($goal = $_REQUEST['goal'])){
			switch ($goal){
				case 'list': 
					if (!empty($_REQUEST['id']) && is_numeric($id = $_REQUEST['id']))
						return $this->ac->getJsonCatsList($id);
					if (!empty($_REQUEST['rootID']) && is_numeric($id = $_REQUEST['rootID']))
						return $this->ac->getJsonCatsList($id);
				break;
				case 'search':
					if ( !empty($_REQUEST['search']) && ($word = $_REQUEST['search']) && !empty($_REQUEST['rootID']) && is_numeric($rootID = $_REQUEST['rootID']) ){
						// print_r($this->ac->searchCat($word,$rootID));
						return $this->ac->searchCat($word,$rootID);
					}
				break;
				case 'marks':
					if (!empty($_REQUEST['marks_head']) && ($head = $_REQUEST['marks_head']) && ($list = $this->objects->getFullObjectsListByClass($head,2))){
						$out = array();
						foreach ($list as $o){
							$out[] = '<option value='.$o['Название'].'>'.$o['Название'].'</option>';
						}
						return join("\n",$out);
					}elseif ($list = $this->objects->getFullObjectsListByCLass(251,2)){
						$out = array();
						foreach ($list as $o){
							$out[] = '<option value='.$o['Название'].'>'.$o['Название'].'</option>';
						}
						return join("\n",$out);
					}else{
						return false;
					}
				break;
			}
		}
	}

	#Получаем содержимое страницы
	function getPage(){
		include_once(_MODS_ABS_.'/cabinet/html'.$this->tpl);
	}

	#ЛЕвое меню
	function cabMenu($id = 235){
		$out = array();
		if(!!$list = $this->objects->getFullObjectsListByClass($id,2)){
			$not_show_for_user = array('/business/','/invoices/');
			foreach($list as $o){
				if (in_array($o['Ссылка'],$not_show_for_user) && ($this->subject['Роль']=='user')) continue;
				if($this->isActive($o)){
					if ($o['Ссылка']=='/invoices/')
						$out[]='<li class="active"><a href="/cabinet'.$o['Ссылка'].'">'.$o['Название'].'<span class="credit-count">'.$this->subject['Кредиты'].' кр.</span></a>'.($this->subject['Роль']=='business'?$this->cabSubMenu($o['id']):'').'</li>';
					elseif (($this->subject['Роль']=='user') && ($o['Ссылка']=='/company_info/'))
						$out[] = '<li class="active"><a href="/cabinet'.$o['Ссылка'].'">'.$o['Название'].'</a></li>';
					else
						$out[]='<li class="active"><a href="/cabinet'.$o['Ссылка'].'">'.$o['Название'].'</a>'.$this->cabSubMenu($o['id']).'</li>';
				}else{
					if ($o['Ссылка']=='/invoices/')
						$out[]='<li><a href="/cabinet'.$o['Ссылка'].'">'.$o['Название'].'<span class="credit-count">'.(int)$this->subject['Кредиты'].' кр.</span></a></li>';
					else
						$out[]='<li><a href="/cabinet'.$o['Ссылка'].'">'.$o['Название'].'</a></li>';
				}
			}
		}
		return join("\n", $out);
	}

	#Под меню
	function cabSubMenu($id){
		$out = array();
		if ($list = $this->objects->getFullObjectsListByClass($id,2)){
			$out[] = '<ul class="reset">';
			foreach($list as $o){
				if($this->isActive($o))
					$out[]='<li class="active"><a href="/cabinet'.$o['Ссылка'].'">'.$o['Название'].'</a></li>';
				else
					$out[]='<li><a href="/cabinet'.$o['Ссылка'].'">'.$o['Название'].'</a></li>';
			}
			$out[] = '</ul>';
		}
		return join("\n",$out);
	}

	#Активна ли ссылка
	function isActive($o){
		if (!is_array($o)) return false;
		if (empty($_GET['action']) && ($o['Ссылка'] == '/')) return true;
		if (!empty($_GET['action']) && (str_replace('/','',$o['Ссылка']) == $_GET['action'])) return true;
		if ($list = $this->objects->getFullObjectsListByClass($o['id'],2)){
			foreach ($list as $i){
				if ($this->isActive($i)){
					return true;
				}
			}
		}
		return false;
	}

	#Получаем URL компании на сайте
	function getCompanyUrl($subject){
		$out = !empty($subject['URL']) ? '/co/'.$subject['URL'] : '/co/'.$subject['id'];
		return $out;
	}

	#Получаем подсказки
	function getVars(){
		switch ($this->subject['Роль']){
			case 'user':
				return $this->getVarsByRole(501);
			break;
			case 'company':
				return $this->getVarsByRole(505);
			break;
			default:
				return $this->getVarsByRole(511);
			break;
		}
	}

	#Получаем подсказки по типу роли
	function getVarsByRole($id){
		if (!$list = $this->objects->getFullObjectsList($id)) return false;
		$out = array();
		foreach ($list as $o){
			if (!isset($o['Значение'])) continue;
			$out[$o['id']] = $o['Значение'];
		}
		return $out;
	}

	#Получить дату окончания 
	function getCompanyBilling(){
		return $this->db->select("billing","WHERE `user_id` = '".$this->subject['id']."' LIMIT 1");
	}

	#Получить дату окончания
	function getBusinessEndDate(){
		if (!$selected = $this->db->select("billing","WHERE `user_id` = '".$this->subject['id']."' LIMIT 1")) return false;
		return $selected['end_date'];
	}

	#Проверяем бизнес пакет
	function checkBusinessPacket(){
		if ($this->subject['Роль']!='business') return false;
		if (!$selected = $this->db->select("billing","WHERE `user_id` = '".$this->subject['id']."' AND `active` = '1' AND `service` = 'business' LIMIT 1")) return false;
		if ($selected['end_date'] < date("Y-m-d")){
			$this->objects->editObjectFields($this->subject['id'],array(157 => 'company'));
			$this->db->update("billing",array('active'=>0),"WHERE `user_id` = '".$this->subject['id']."'");
			return false;
		}else{
			return true;
		}
	}
}
?>