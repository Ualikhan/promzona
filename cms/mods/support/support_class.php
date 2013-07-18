<?class support extends api{
	public $title;

	function __construct(){
		parent::__construct();
		$this->title = 'Служба поддержки Promzona.kz';
	}

	#Вывод страницы
	function getPage(){
		include_once(_MODS_ABS_.'/support/support.html');
	}

	#Отправляем сообщение
	function sendMsg(){
		if (empty($_POST['f']) || !is_array($f=$_POST['f'])) return false;
		include_once(_FILES_ABS_.'/mail.php');
		$smail = new mime_mail();
		if(($obj=$api->objects->getFullObject(16)) && (trim($obj['Значение'])!='')){
			$smail->to=trim($obj['Значение']);
		}else{
			$smail->to='asd_2000@mail.ru';
		}
		$smail->from = 'admin@'.$_SERVER['HTTP_HOST'];
		$smail->subject = 'Сообщение с формы обратной связи на сайте '.$_SERVER['HTTP_HOST'].'';
		$html = array();
		$html[]='<div><b>Имя</b></div>';
		$html[]='<div>'.$f['name'].'</div>';
		$html[]='<br>';
		$html[]='<div><b>email</b></div>';
		$html[]='<div>'.$f['email'].'</div>';
		$html[]='<br>';
		$html[]='<div><b>Сообщение</b></div>';
		$html[]='<div>'.$f['msg'].'</div>';
		$html[]='<br>';
		$smail->body = join("", $html);
		$smail->send($smail->to);
		header("location: ".$_SERVER['REQUEST_URI']);
	}


}?>