<?class support extends api{

public $title,$tpl, $msg;
public $email, $phone, $icq, $skype;

	function __construct(){
		parent::__construct();
		$this->title = 'Служба поддержки';
		$this->getTemplate();
		$this->sendMsg();
	}

	#Вставляем шаблон взависимости роли пользователя
	function getTemplate(){
		switch ($_SESSION['u']['role']){
			// case 'user':
			// 	$this->tpl = '/company_info_create.html';
			// break;
			// case 'company':
			// 	$this->tpl = '/company_info.html';
			// break;
			// case 'business':
			// 	$this->tpl = '/company_info.html';
			// break;
			default:
				$this->tpl = '/support.html';
			break;
		}
	}

	#Отправляем сообщение
	function sendMsg(){
		if (empty($_POST['f']) || !is_array($f=$_POST['f'])) return false;
		include_once(_FILES_ABS_.'/mail.php');
		$smail = new mime_mail();
		if(($obj=$this->objects->getFullObject(16)) && (trim($obj['Значение'])!='')){
			$smail->to=trim($obj['Значение']);
		}else{
			$smail->to='timur@go-web.kz';
		}
		$smail->from = 'support@'.$_SERVER['HTTP_HOST'];
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
		// $this->msg = $smail->send($smail->to);
		if ($smail->send($smail->to)) $this->msg = 'Сообщение успешно отправлено';
		else $this->msg = 'Сообщение не было доставлено. Убедитесь что всё введено верно.';
	}

}?>