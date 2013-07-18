<?class bills extends api{

	public $title, $tpl;
	public $class_id_com, $class_id, $no_show_head, $on_page, $news, $pages, $desc;

	function __construct(){
		parent::__construct();
		$this->class_id = 41;
		$this->class_id_com = 33;
		$this->title = 'Список счетов на модерацию';
		$this->billJSON();
		$this->approve();
		$this->reject();
		$this->remove();
		$this->on_page = isset($_GET['on_page']) && is_numeric($_GET['on_page']) ? $_GET['on_page'] : 10;
		$this->pages = $this->getPaginationPages($this->getCount());
		$this->bills = $this->getBills();
		$this->getTemplate();
	}

	#Получаем шаблон
	function getTemplate(){
		if (!empty($this->bills)){
			$this->tpl = '/bills.html';
		}else{
			$this->desc = 'В данный момент счетов на оплату нет.';
			$this->tpl = '/empty.html';
		}
	}

	#Получаем компании 
	function getBills(){
		return $this->objects->getFullObjectsListByClass(-1,$this->class_id,"AND o.active = '1' AND c.field_197 = '0' ORDER BY o.sort LIMIT ".$this->pages['start'].", ".$this->pages['on_page']);
	}

	#Получаем колличество объявлений 
	function getCount(){
		return $this->db->count("`objects` as o", "LEFT JOIN `class_".$this->class_id."` as c ON o.id = c.object_id WHERE o.active = '1' AND c.lang = 'ru' AND c.field_197 = '0'");
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
		$fields = array('197'=>'1', 207=>date('d.m.Y H:i'));
		include_once(_PUBLIC_ABS_.'/transactions.php');
		foreach ($objects as $k=>$o){
			$this->objects->editObjectFields($k,$fields);
			if (!$bill = $this->objects->getFullObject($k)) continue;
			if ($bill['Тип']==1){
				$this->addBalance($bill);
				$this->sendMail($bill);
			}elseif ($bill['Тип']==0){
				$this->toBusiness($bill);
				$this->sendMail($bill);
			}
		}
		return false;
		header("location:".$_SERVER['REQUEST_URI']);
	}

	#Добавить баланс записать транзакцию
	function addBalance($bill){
		if (empty($bill)) return false;
		if (!$subject = $this->objects->getFullObject($bill['head'])) return false;
		if ($subject['Роль']!='business') return false;
		$fields = array(171 => ($bill['Сумма']+$subject['Кредиты']));
		if (!$this->saveTransaction(0,$subject['id'],$bill['Сумма'],$bill['id'],'Пополнение баланса')) return false;
		if (!$this->objects->editObjectFields($subject['id'], $fields)) return false;
		return true;
	}

	#Перевести на пакет бизнес записать в билинг и транзакцию
	function toBusiness($bill){
		if (empty($bill)) return false;
		if (!$subject = $this->objects->getFullObject($bill['head'])) return false;
		if (!$this->saveTransaction($subject['id'],0,$bill['Сумма'],$bill['id'],$bill['Номер'].' '.$bill['Название'])) return false;
		$ballance = $this->getBallanceBySrok($bill['Срок']);
		if (!$this->saveTransaction(0,$subject['id'],$ballance, $bill['id'],'Баланс за пакет бизнес. Номер счета:'.$bill['Номер'].' - '.$bill['Название'])) return false;
		$fields = array(157 => 'business', 171 => $ballance);
		if (!$this->objects->editObjectFields($subject['id'], $fields)) return false;
		$billing_fields = array(
			'user_id' => $subject['id'],
			'start_date' => date("Y-m-d"),
			'end_date' => $this->getEndDate($bill['Срок']),
			'service' => 'business',
			'active' => 1
		);
		if ($this->db->select("billing", "WHERE `user_id` = '".$subject['id']."'")){
			if (!$this->db->update("billing", $billing_fields, "WHERE `user_id` = '".$subject['id']."'")) return false;
		}else{
			if (!$this->db->insert("billing", $billing_fields)) return false;
		}
		return true;
	}

	#Отклонить
	function reject(){
		if (empty($_POST['reject']) || ($_POST['reject']!=1) || !isset($_POST['o']) || !is_array($_POST['o'])) return false;
		$objects = $_POST['o'];
		$fields = array('197'=>'2');
		foreach ($objects as $k=>$o){
			$this->objects->editObjectFields($k,$fields);
		}
		header("location:".$_SERVER['REQUEST_URI']);
	}

	#Удалить
	function remove(){
		if (empty($_POST['remove']) || ($_POST['remove']!=1) || !isset($_POST['o']) || !is_array($_POST['o'])) return false;
		$objects = $_POST['o'];
		foreach ($objects as $k=>$o){
			$this->objects->editObject(array('id' => $k, 'active' => 0));
		}
		header("location:".$_SERVER['REQUEST_URI']);
	}

	#Сохраняем транзакцию
	function saveTransaction($from, $to, $summ, $invoice_id, $type = ''){
		if (empty($summ)) return false;
		$trans = new transactions();
		if (!$trans->saveData($from, $to, $summ, $type, $invoice_id)) return false;
		return true;
	}

	#Получаем дату окончания
	function getEndDate($num, $sql = true){
		switch ($num){
			case 1:
				$count = 3;
			break;
			case 2:
				$count = 6;
			break;
			case 3:
				$count = 9;
			break;
			case 4:
				$count = 12;
			break;
			default:
				return false;
			break;
		}
		if ($sql) {
			return date("Y-m-d",time() + $count*30*24*60*60);
		}else{
			return date("d.m.Y",time() + $count*30*24*60*60);
		}
	}

	#Получить колличество кредитов по сроку пакета бизнес
	function getBallanceBySrok($srok){
		switch ($srok){
			case 1:
				return 100;
			break;
			case 2:
				return 300;
			break;
			case 3:
				return 600;
			break;
			case 4:
				return 900;
			break;
		}
	}

	#Подробная инфа о счете
	function billJSON(){
		if (empty($_REQUEST['bill']) || !is_numeric($id = $_REQUEST['bill']) || (!$bill = $this->objects->getFullObject($id))) return false;
		$json = array(
			'name'=>$bill['Название'],
			'number'=>$bill['Номер'],
			'payer'=>$bill['Плательщик'],
			'adress'=>$bill['Адрес'],
			'date'=>$this->strings->date(date('Y-m-d',$bill['sort'])),
			'time'=>date('d.m.Y H:i',$bill['sort']),
			'bin'=>$bill['РНН'],
			'sum'=>$bill['Сумма']
		);
		exit(json_encode($json));
	}


	#Текст для отправки письма
	function sendMail($bill){
		if (empty($bill)) return false;
		if (!$obj = $this->objects->getFullObject($bill['head'])) return false;
		$html = '<p>
			Спасибо за пополнение cчета в Promzona.kz.<br>
			Мы рады подтвердить ваш платеж.<br>
			<br>
			'.($bill['Тип'] == 0 ? 
				'Вы подписаны на платный пакет до '.$this->getEndDate($bill['Срок'], false).' г. ':
				'Мы пополнили баланс вашего счета указанную сумму '.$bill['Сумма'].' тенге.<br>'
			).'<br><br>
			Информация о вашей покупке:<p/>
			<ul>
				<li>Логин promzona: '.$obj['Email'].'</li>
				<li>Наименование продукта: '.($bill['Тип'] == 1?'пополнение счета':'подписка на платный пакет').'</li>
				<li>Сумма к оплате: '.$bill['Сумма'].' KZT</li>
				<li>Дата платежа: '.date('d.m.Y').'</li>
				<li>Номер: '.$bill['Номер'].'</li>
				<li>Статус заказа: Одобрено</li>
			</ul>
			<p>
			Если у вас возникнут проблемы, обратитесь в раздел "Помощь" нашего веб-сайта.<br>
			До скорого!<br>
			Команда <b>'.$_SERVER['HTTP_HOST'].'</b><br>
			Skype: pzsupport <br>
			Тел:  +7 727 0000 000<br>
			Моб. Тел:  +7 777 363 2280, +7 7018948161<br>
		</p>
		';
		$this->mail->to = $obj['Email'];
		$this->mail->from = 'support@'.$_SERVER['HTTP_HOST'];
		$this->mail->subject = 'Уведомление о пополнеии счета или подписки по платному пакету на сайте '.$_SERVER['HTTP_HOST'].'';
		$this->mail->body = $html;
		$this->mail->send($this->mail->to);
	}



}?>