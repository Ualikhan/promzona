<?class co extends api{
	public $title, $class_id, $obj, $branches, $obj_id, $ads, $news, $cats;
	public $is_got, $money, $condition;

	function __construct(){
		parent::__construct();
		$this->class_id = 33;
		$this->obj = $this->getComObject($_GET['action']);
		$this->obj_id = isset($this->obj['object_id']) ? $this->obj['object_id'] : $this->obj['id'];
		$this->title = $this->getTitle();
		$this->branches = $this->getBranches();
		$this->contacts = $this->getContacts();
		$this->ads = $this->getAds();
		$this->news = $this->getNews();
		$this->cats = $this->getCatsByAds();
		$this->getDopProperties();
		$this->orderCall();
	}

	#Вывод страницы
	function getPage(){
		include_once(_MODS_ABS_.'/co/co.html');
	}

	#Получаем объект компании
	function getComObject(){
		if (empty($_GET['action'])) header("location : /404/");
		if ( ($obj = $this->getComObjectByUrl($_GET['action'])) && ($obj['class_id']==$this->class_id) ) return $obj;
		if (is_numeric($id = $_GET['action']) && ($obj = $this->objects->getFullObject($id)) && ($obj['class_id']==$this->class_id)) return $obj;
		header("location: /404/");
	}

	#Получаем компанию по URL
	function getComObjectByUrl($url){
		$fields = $this->db->select("fields", "WHERE `class_id` = ".$this->class_id." ORDER BY sort");
		$where = "LEFT JOIN `class_".$this->class_id."` as c ON o.id=c.object_id WHERE c.lang = 'ru' AND o.active = '1' AND c.field_165 = '".$this->db->prepare($url)."' LIMIT 1 ";
		if (!$com =  $this->db->select("`objects` as o", $where)) return false;
		$out = array();
		foreach ($com[0] as $key=>$i){
			$object_field_name = explode("_",$key);
			if (isset($object_field_name[1]) && is_numeric($field_id = $object_field_name[1])) {
				foreach ($fields as $f){
					if ($f['id']==$field_id) {
						$out[$f['name']] = $i;
						break;
					}
				}
			}else{
				$out[$key] = $i;
			}
		}
		return $out;
	}

	#Получаем заголовок
	function getTitle(){
		if (empty($this->obj)) return false;
		return $this->obj['Название компании'];
	}

	#Получаем филиалы
	function getBranches($class_id = 39){
		return $this->objects->getFullObjectsListByClass($this->obj_id, $class_id);
	}

	#Получаем доп контакты
	function getContacts($class_id = 37){
		return $this->objects->getFullObjectsListByClass($this->obj_id, $class_id);
	}

	#Получаем объекты объявлений
	function getAds($class_id = 31){
		$fields = $this->db->select("fields", "WHERE `class_id` = ".$class_id." ORDER BY sort");
		$where = "LEFT JOIN `class_$class_id` as c ON o.id = c.object_id WHERE c.lang = 'ru' AND o.active='1' AND c.field_161 = '".$this->obj_id."' AND c.field_102 = '1'";
		if (!$ads = $this->db->select("`objects` as o", $where)) return false;
		$out = array();
		foreach ($ads as $k=>$cat){
			$out[$k] = array();
			foreach ($cat as $key=>$i){
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

	#Получаем новости компании
	function getNews($class_id = 8){
		if ($this->obj['Роль'] == 'company') return false;
		return $this->objects->getFullObjectsListByCLass($this->obj_id, $class_id, "AND o.active = '1' AND c.field_251 = '1'");
	}

	#Получаем категории товаров
	function getCatsByAds($class_id = 30){
		if (empty($this->ads)) return false;
		$fields = $this->db->select("fields", "WHERE `class_id` = ".$class_id." ORDER BY sort");
		$ids = array(); $heads = array();
		foreach ($this->ads as $k=>$ad){
			if (!in_array($ad['head'],$ids))
				$ids[] = $ad['head'];
		}
		$where = "LEFT JOIN `class_$class_id` as c ON o.id = c.object_id WHERE c.lang = 'ru' AND o.active = '1' AND o.id IN (".join(", ",$ids).")";
		if (!$cats = $this->db->select("`objects` as o", $where)) return false;
		foreach ($cats as $k=>$cat){
			$out[$k] = array();
			foreach ($cat as $key=>$i){
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

	#Получаем имя каталога по id
	function getCatNameById($id){
		if (empty($this->cats)) return false;
		foreach ($this->cats as $cat){
			if ($cat['object_id'] == $id) return $cat['Название'];
		}
	}

	#Цвет объявления
	function getItemColor($n){
		switch ($n){
			case '1':
				return 'item-green';
			break;
			case '2':
				return 'item-yellow';
			break;
			case '3':
				return 'item-blue';
			break;
			default:
				return '';
			break;
		}
	}

	#Получаем доп свойства класса
	function getDopProperties($class_id = 31){
		$this->is_got = $this->objects->getFieldP3(117,$class_id);
		$this->money = $this->objects->getFieldP3(116,$class_id);
		$this->condition = $this->objects->getFieldP3(108,$class_id);
	}

	#Заказ звонка с отправкой на email
	function orderCall(){
		if (!isset($_POST['order_call']) || ($_POST['order_call']!='1')) return false;
		if (!isset($_POST['phone']) || !is_array($_POST['phone'])) return false;
		$ad_name = !empty($_POST['ad_name']) ? $_POST['ad_name'] : '';
		$phone = '';
		foreach ($_POST['phone'] as $p){
			if (empty($p) || !is_numeric($p)) return false;
			$phone .= $p.' ';
		}
		if (!isset($_POST['time']) || !is_array($t = $_POST['time'])) return false;
		if (!isset($_POST['order-call-time-id1'])) return false;
		if (!isset($_POST['email']) || (!$email = $_POST['email'])) return false;
		if ($_POST['order-call-time-id1'] == 'anytime'){
			$time = 'в любое время';
		}else{
			$time = 'между '.$t[0]['h'].':'.$t[0]['m'].' - '.$t[1]['h'].':'.$t[1]['m'].' часов';
		}
		$this->mail->to = $email;
		$this->mail->from = 'order@'.$_SERVER['HTTP_HOST'];
		$this->mail->subject = 'Заказ звонка с сайта '.$_SERVER['HTTP_HOST'];
		$this->mail->body = '
		<p>Увашаемый клиент!</p>
		<p>Благодарим Вас, за то что воспользовались услугами портала Promzona.kz<br>
		На Ваш адрес поступила заявка на обратный звонок, если желаете связаться покупателем просим вас  перезвонить следующему номеру:  '.$phone.' '.$time.' <br>
		Наименование товара: '.$ad_name.'<br>
		Удачной Вам сделки,<br>
		С уважением,<br>
		Support@promzona.kz</p>
		';
		$this->mail->send($this->mail->to); 
		header("location: ".$_SERVER['REQUEST_URI']);
	}


}?>