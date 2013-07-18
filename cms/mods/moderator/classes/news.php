<?class news extends api{

	public $title, $tpl;
	public $class_id_com, $class_id, $no_show_head, $on_page, $news, $pages, $desc;

	function __construct(){
		parent::__construct();
		$this->class_id = 8;
		$this->class_id_com = 33;
		$this->no_show_head = 843;
		$this->title = 'Список новостей на модерацию';
		if (isset($_POST['reject'])) {
			$this->reject();
		} elseif (isset($_POST['remove'])) {
			$this->remove();
		} elseif (!empty($_POST['o'])) {
			$this->approve();
		}
		$this->on_page = isset($_GET['on_page']) && is_numeric($_GET['on_page']) ? $_GET['on_page'] : 10;
		$this->pages = $this->getPaginationPages($this->getCount());
		$this->news = $this->getNews();
		$this->getTemplate();
	}

	#Получаем шаблон
	function getTemplate(){
		if (!empty($this->news)){
			$this->tpl = '/news.html';
		}else{
			$this->desc = 'В данный момент новостей ожидающих рассмотрения модератором нет.';
			$this->tpl = '/empty.html';
		}
	}

	#Получаем компании 
	function getNews(){
		$fields = $this->db->select("fields", "WHERE `class_id` IN (".$this->class_id.", ".$this->class_id_com.") ORDER BY sort");
		$where = "LEFT JOIN `class_".$this->class_id."` as n ON o.id = n.object_id LEFT JOIN `class_".$this->class_id_com."` as com ON o.head = com.object_id WHERE o.active = '1' AND n.field_251 = '0' AND o.head <> '".$this->no_show_head."' AND n.lang = 'ru' ORDER BY o.sort LIMIT ".$this->pages['start'].", ".$this->pages['on_page'];
		$what = "o.*, n.*, n.object_id as n_object_id, com.*";
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
		return $this->objects->getObjectsCount(-1, $this->class_id, "AND o.active = '1' AND c.field_251 = '0' AND o.head<>'".$this->no_show_head."'");
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
		// if (empty($_POST['approve']) || ($_POST['approve']!=1) || empty($_POST['o']) || !is_array($_POST['o'])) return false;
		if (empty($_POST['o']) || !is_array($_POST['o'])) return false;
		$objects = $_POST['o'];
		$fields = array('251'=>'1');
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
		$fields = array('251'=>'2');
		foreach ($objects as $k=>$o){
			$this->objects->editObjectFields($k,$fields);
			if ($obj = $this->objects->getFullObject($k)){
				if (!$subject = $this->objects->getFullObject($obj['head'])) continue;
				$this->mail->from = 'support@'.$_SERVER['HTTP_HOST'];
				$this->mail->subject = 'Результат модерации на сайте '.$_SERVER['HTTP_HOST'];
				$this->mail->to = $subject['Email'];
				$this->mail->body = '
					<p>Увашаемый клиент!</p>
					<p>Ваше новость - '.$obj['Название'].' отклонена модератором '.(!empty($desc)?'по причине: <br> '.$desc:'').'.</p>
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
			if (!$subject = $this->objects->getFullObject($obj['head'])) continue;
				$this->mail->from = 'support@'.$_SERVER['HTTP_HOST'];
				$this->mail->subject = 'Результат модерации на сайте '.$_SERVER['HTTP_HOST'];
				$this->mail->to = $subject['Email'];
				$this->mail->body = '
					<p>Увашаемый клиент!</p>
					<p>Ваше новость - '.$obj['Название'].' отклонена модератором '.(!empty($desc)?'по причине: <br> '.$desc:'').'.</p>
					<p>С уважением,<br>
					Support@promzona.kz</p>
				';
				$this->mail->send($this->mail->to);
		}
		header("location:".$_SERVER['REQUEST_URI']);
	}

}?>