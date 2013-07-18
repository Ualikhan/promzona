<?class coms extends api{

	public $title, $tpl;
	public $class_id, $root_id, $coms, $on_page, $pages, $desc;

	function __construct(){
		parent::__construct();
		$this->root_id = 303;
		$this->class_id = 33;
		$this->approve();
		$this->reject();
		$this->remove();
		$this->title = 'Список компаний на модерацию';
		$this->on_page = isset($_GET['on_page']) && is_numeric($_GET['on_page']) ? $_GET['on_page'] : 10;
		$this->pages = $this->getPaginationPages($this->getCount());
		$this->coms = $this->getComs();
		$this->getTemplate();
	}


	#Получаем шаблон
	function getTemplate(){
		if (!empty($this->coms)){
			$this->tpl = '/coms.html';
		}else{
			$this->desc = 'В данный момент компаний ожидающих рассмотрения модератором нет.';
			$this->tpl = '/empty.html';
		}
	}

	#Получаем компании 
	function getComs(){
		return $this->objects->getFullObjectsListByCLass($this->root_id, $this->class_id, "AND o.active = '1' AND c.field_259 = '0' ORDER BY o.sort LIMIT ".$this->pages['start'].", ".$this->pages['on_page']);
	}

	#Получаем колличество объявлений 
	function getCount(){
		return $this->objects->getObjectsCount(303, 33, "AND o.active = '1' AND c.field_259 = '0'");
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
		// $object = array('active'=>'1');
		foreach ($objects as $k=>$o){
			if (!$this->objects->editObjectFields($k,array(259=>1))) return 'asdsad';
		}
		header("location:".$_SERVER['REQUEST_URI']);
	}

	#Отклонить
	function reject(){
		if (empty($_POST['reject']) || ($_POST['reject']!=1) || !isset($_POST['o']) || !is_array($_POST['o'])) return false;
		$objects = $_POST['o'];
		$desc = !empty($_POST['desc']) ? $_POST['desc'] : '';
		foreach ($objects as $k=>$o){
			if (!$this->objects->editObjectFields($k,array(259=>2))) return 'asdsad';
			if ($obj = $this->objects->getFullObject($k)){
				$this->mail->from = 'support@'.$_SERVER['HTTP_HOST'];
				$this->mail->subject = 'Результат модерации на сайте '.$_SERVER['HTTP_HOST'];
				$this->mail->to = $obj['Email'];
				$this->mail->body = '
					<p>
					Уважаемый пользователь!<br>
					Благодарим Вас, за то что воспользовались услугами портала Promzona.kz.<br>
					Реситрация Вашей компании  ОТКЛОНЕНА.<br>
					Причиной отказа в регистрации может быть одно или совокупность следующих фактов:
					</p>
					<ul>
						<li>Вы не подтвердили Ваш электронный адрес.</li>
						<li>Профиль пользователя не соответсвует действительности, либо зафексирована множественная регистрация аккаунтов;</li>
						<li>Не заполнены или неправильно заполнены необходимые пункты для регистрации;</li>
						<li>Контактные данные не соответсвуют  действительности;</li>
						<li>Нарушение авторских прав  и/или интелектуальных прав собственности.</li>
					<p>Если Вы не получили  письмо уведомление или введенные данные являются верными, просим связаться со службой поддержки support@promzona.kz<br>
					Если Вы являетесь истинным собственником  интелектуальных или авторских прав, просим Вас связаться со службой поддержки support@promzona.kz<br>
					Skype: pzsupport <br>
					Тел:  +7 727 0000 000<br>
					Моб. Тел:  +7 777 363 2280, +7 7018948161<br>
					</p>
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
			$this->objects->deleteObject($k);
			if ($obj = $this->objects->getFullObject($k)){
				$this->mail->from = 'support@'.$_SERVER['HTTP_HOST'];
				$this->mail->subject = 'Результат модерации на сайте '.$_SERVER['HTTP_HOST'];
				$this->mail->to = $obj['Email'];
				$this->mail->body = '
					<p>
					Уважаемый пользователь!<br>
					Благодарим Вас, за то что воспользовались услугами портала Promzona.kz.<br>
					Реситрация Вашей компании  УДАЛЕНА.<br>
					Причиной отказа в регистрации может быть одно или совокупность следующих фактов:
					</p>
					<ul>
						<li>Вы не подтвердили Ваш электронный адрес.</li>
						<li>Профиль пользователя не соответсвует действительности, либо зафексирована множественная регистрация аккаунтов;</li>
						<li>Не заполнены или неправильно заполнены необходимые пункты для регистрации;</li>
						<li>Контактные данные не соответсвуют  действительности;</li>
						<li>Нарушение авторских прав  и/или интелектуальных прав собственности.</li>
					<p>Если Вы не получили  письмо уведомление или введенные данные являются верными, просим связаться со службой поддержки support@promzona.kz<br>
					Если Вы являетесь истинным собственником  интелектуальных или авторских прав, просим Вас связаться со службой поддержки support@promzona.kz<br>
					Skype: pzsupport <br>
					Тел:  +7 727 0000 000<br>
					Моб. Тел:  +7 777 363 2280, +7 7018948161<br>
					</p>
				';
				$this->mail->send($this->mail->to);
			}
		}
		header("location:".$_SERVER['REQUEST_URI']);
	}

}?>