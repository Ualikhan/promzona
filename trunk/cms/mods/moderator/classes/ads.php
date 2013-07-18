<?class ads extends api{

	public $title, $tpl;
	public $ads, $class_id, $class_id_com, $class_id_cat, $conditions, $money, $on_page, $pages, $desc;

	function __construct(){
		parent::__construct();
		$this->class_id = 31;
		$this->class_id_com = 33;
		$this->class_id_cat = 30;
		$this->approve();
		$this->reject();
		$this->remove();
		$this->title = 'Список объявлений на модерацию';
		$this->on_page = isset($_GET['on_page']) && is_numeric($_GET['on_page']) ? $_GET['on_page'] : 10;
		$this->pages = $this->getPaginationPages($this->getCount());
		$this->ads = $this->getAds();
		$this->conditions = $this->objects->getFieldP3(108, $this->class_id);
		$this->money = $this->objects->getFieldP3(116, $this->class_id);
		$this->is_got = $this->objects->getFieldP3(117, $this->class_id);
		$this->getTemplate();
	}

	#Получаем шаблон
	function getTemplate(){
		if (!empty($this->ads)){
			$this->tpl = '/ads.html';
		}else{
			$this->desc = 'В данный момент объявлений ожидающих рассмотрения модератором нет.';
			$this->tpl = '/empty.html';
		}
	}

	#Получаем объявления на модерации
	function getAds(){
		$fields = $this->db->select("fields", "WHERE `class_id` IN (".$this->class_id.", ".$this->class_id_com.", ".$this->class_id_cat.") ORDER BY sort");
		$where = "LEFT JOIN `class_".$this->class_id."` as ad ON o.id=ad.object_id LEFT JOIN `class_".$this->class_id_cat."` as cat ON o.head=cat.object_id LEFT JOIN `class_".$this->class_id_com."` as com ON ad.field_161=com.object_id WHERE o.active = '1' AND ad.field_102 = '0' AND ad.lang = 'ru' ORDER BY o.sort LIMIT ".$this->pages['start'].", ".$this->pages['on_page'];
		$what = "o.*, ad.*, cat.field_98 as category_name, com.field_143, com.field_127, com.field_139";
		$ads = $this->db->select("`objects` as o", $where, $what);
		$out = array();
		foreach ($ads as $k=>$ad){
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

	#Получаем колличество объявлений 
	function getCount(){
		return $this->db->count("`objects` as o", "LEFT JOIN `class_".$this->class_id."` as c ON o.id = c.object_id WHERE o.active = '1' AND c.lang = 'ru' AND c.field_102 = '0'");
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

	#Одобрить
	function approve(){
		if (empty($_POST['approve']) || ($_POST['approve']!=1) || empty($_POST['o']) || !is_array($_POST['o'])) return false;
		$objects = $_POST['o'];
		$fields = array('102'=>'1');
		foreach ($objects as $k=>$o){
			$this->objects->editObjectFields($k,$fields);
		}
		header("location:".$_SERVER['REQUEST_URI']);
	}

	#Отклонить
	function reject(){
		if (empty($_POST['reject']) || ($_POST['reject']!=1) || !isset($_POST['o']) || !is_array($_POST['o'])) return false;
		$objects = $_POST['o'];
		$desc = !empty($_POST['desc']) ? $_POST['desc'] : '';
		$fields = array('102'=>'3');
		foreach ($objects as $k=>$o){
			$this->objects->editObjectFields($k,$fields);
			// print_r($this->objects->getFullObject($k));
			if ($obj = $this->objects->getFullObject($k)){
				if (!$subject = $this->objects->getFullObject($obj['user_id'])) continue;
				$this->mail->from = 'support@'.$_SERVER['HTTP_HOST'];
				$this->mail->subject = 'Результат модерации на сайте '.$_SERVER['HTTP_HOST'];
				$this->mail->to = $subject['Email'];
				$this->mail->body = '
					<p>Увашаемый клиент!</p>
					<p>Ваше объявление - '.$obj['Название'].' отклонено модератором '.(!empty($desc)?'по причине: <br> '.$desc:'').'.</p>
					<p>С уважением,<br>
					Support@promzona.kz</p>
				';
				$this->mail->send($this->mail->to);
			}
		}
		header("location:".$_SERVER['REQUEST_URI']);
	}

	#Удалить
	function remove(){
		if (empty($_POST['remove']) || ($_POST['remove']!=1) || !isset($_POST['o']) || !is_array($_POST['o'])) return false;
		$objects = $_POST['o'];
		$desc = !empty($_POST['desc']) ? $_POST['desc'] : '';
		foreach ($objects as $k=>$o){
			$this->objects->editObject(array('id' => $k, 'active' => 0));
			if ($obj = $this->objects->getFullObject($k)){
				if (!$subject = $this->objects->getFullObject($obj['user_id'])) continue;
				$this->mail->from = 'support@'.$_SERVER['HTTP_HOST'];
				$this->mail->subject = 'Результат модерации на сайте '.$_SERVER['HTTP_HOST'];
				$this->mail->to = $subject['Email'];
				$this->mail->body = '
					<p>Увашаемый клиент!</p>
					<p>Ваше объявление - '.$obj['Название'].' удалено модератором '.(!empty($desc)?'по причине: <br> '.$desc:'').'.</p>
					<p>С уважением,<br>
					Support@promzona.kz</p>
				';
				$this->mail->send($this->mail->to);
			}
		}
		header("location:".$_SERVER['REQUEST_URI']);
	}

}?>