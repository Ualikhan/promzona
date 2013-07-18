<?class ads extends api{

public $title,$obj, $class_id, $money, $subject;

	function __construct(){
		parent::__construct();
		$this->class_id = 31;
		$this->obj = $this->getFullAd();
		$this->subject = $this->getSubject();
		$this->title = $this->obj['Название'];
		$this->money = $this->objects->getFieldP3(116,$this->class_id);
		$this->types = $this->objects->getFieldP3(100,$this->class_id);
		$this->is_got = $this->objects->getFieldP3(117,$this->class_id);
		$this->condition = $this->objects->getFieldP3(108,$this->class_id);
		$this->sroks = $this->objects->getFieldP3(213,$this->class_id);
		$this->updateViews();
		$this->toFavorite();
		$this->orderCall();
		$this->sendLink();
		if (empty($this->subject)) header('location: /404');
	}

	#Вывод страницы
	function getPage(){
		include_once(_MODS_ABS_.'/ads/ads.html');
	}

	#Получаем объект объявления с полями
	function getFullAd(){
		if (!empty($_GET['action']) && is_numeric($id = $_GET['action']) && ($obj = $this->objects->getFullObject($id)) && ($obj['class_id']==$this->class_id)){
			return $obj;
		}else{
			header("location: /404/");
		}
	}

	#Получаем пользователя или компанию которая добавила объявление
	function getSubject(){
		return $this->objects->getFullObject($this->obj['user_id']);
	}

	#Хлебные крошки
	function adsBC($id){
		$parents = array_reverse($this->getParents($id, 228));
		$out = array();
		$type = $this->getTypeUrl($this->obj['Тип']);
		switch ($type){
			case 'buy' :
				$str = 'Продажа';
			break;
			case 'sell' :
				$str = 'Покупка';
			break;
			case 'rent' :
				$str = 'Аренда';
			break;
			case 'forrent' :
				$str = 'Аренда';
			break;
			default:
				$str = 'Продажа';
			break;
		}
		foreach ($parents as $k=>$o){
			if ($k==0){
				if ($o['Название'] == 'Оборудование') $razd = 'оборудования';
				if ($o['Название'] == 'Спецтехника') $razd = 'спецтехники';
				$out[] = '<a class="bd-beige bold" href="/'.$type.'/">'.$str.' '.$razd.'</a>&nbsp;<i class="icon-chevron-right icon-grey"></i>';
			}elseif($k==(count($parents)-1)){
				$out[] = '<a class="bd-beige bold" href="#">'.$o['Название'].'</a>&nbsp;<i class="icon-chevron-down icon-grey"></i>';
			}else{
				$out[] = '<a class="bd-beige bold" href="/'.$type.'/catalog/'.$o['id'].'/">'.$o['Название'].'</a>&nbsp;<i class="icon-chevron-right icon-grey"></i>';
			}
		}
		return join("\n",$out);
	}

	#Получаем url типа объявления
	function getTypeUrl($n){
		switch ($n){
			case '0':
				return 'buy';
			break;
			case '1':
				return 'sell';
			break;
			case '2':
				return 'rent';
			break;
			case '3':
				return 'forrent';
			break;
		}
	}

	#Получаем доп изображения 
	function getDopPhotos(){
		return $this->objects->getFullObjectsListByClass($this->obj['id'],4);
	}

	#Получаем телефон
	function showPhones(){
		$phones = explode("\n",$this->subject['Телефон']);
		return join(", ",$phones);
	}

	#Заказ звонка с отправкой на email
	function orderCall(){
		if (!isset($_POST['order_call']) || ($_POST['order_call']!='1')) return false;
		if (!isset($_POST['phone']) || !is_array($_POST['phone'])) return false;
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
		$this->mail->from = 'support@'.$_SERVER['HTTP_HOST'];
		$this->mail->subject = 'Заказ звонка с сайта '.$_SERVER['HTTP_HOST'];
		$this->mail->body = '
		<p>Увашаемый клиент!</p>
		<p>Благодарим Вас, за то что воспользовались услугами портала Promzona.kz<br>
		На Ваш адрес поступила заявка на обратный звонок, если желаете связаться покупателем просим вас  перезвонить следующему номеру:  '.$phone.' '.$time.' <br>
		Наименование товара: '.$this->obj['Название'].'<br>
		Удачной Вам сделки,<br>
		С уважением,<br>
		Support@promzona.kz</p>
		';
		$this->mail->send($this->mail->to); 
		header("location: ".$_SERVER['REQUEST_URI']);
	}

	#Изменяем колличество просмотров объявления
	function updateViews(){
		$views = $this->obj['Колличество просмотров'];
		$new_views = (int)$views + 1;
		if ($this->db->update('class_'.$this->obj['class_id'], array('field_215'=>$new_views), "WHERE `object_id`='".$this->obj['id']."'")) return $new_views;
	}

	#Добавляем объявление в избранное
	function toFavorite(){
		if (!isset($_POST['toFavorite']) || ($_POST['toFavorite']!='1')) return false;
		if (!isset($_SESSION['u']['id'])) return false;
		if ($this->db->insert( "favorites", array('user_id'=>$_SESSION['u']['id'],'ads_id'=>$this->obj['id']) )){
			$_SESSION['u']['favorites'][$this->obj['id']] = $this->obj['id'];
			header('location:'.$_SERVER['REQUEST_URI']);
		}
	}

	#ПРоверяем находится ли объявление в избранном
	function is_inFavorites($id){
		if (empty($_SESSION['u'])) return false;
		if (isset($_SESSION['u']['favorites'][$id])) return true;
		else return false;
	}

	#Отправить ссылку
	function sendLink(){
		if (empty($_POST['send_link_email']) || (!$email = $_POST['send_link_email'])) return false;
		if(!preg_match("/[0-9a-z_]+@[0-9a-z_^\.]+\.[a-z]{2,3}/i", $email)){
			return false;
		}
		$this->mail->to = $email;
		$this->mail->from = 'order@'.$_SERVER['HTTP_HOST'];
		$this->mail->subject = 'Ссылка на объявление';
		$this->mail->body = '
			<div>Посмотрите объявление на сайте '.$_SERVER['HTTP_HOST'].'</div>
			<div><b>Ссылка:</b></div>
			<div><a href="http://'.$_SERVER['HTTP_HOST'].'/'.$_SERVER['REQUEST_URI'].'">http://'.$_SERVER['HTTP_HOST'].'/'.$_SERVER['REQUEST_URI'].'</a></div>
		';
		$this->mail->send($this->mail->to); 
		header("location: ".$_SERVER['REQUEST_URI']);
	}


}?>