<?class settings extends api{

public $title,$tpl;
public $object, $msg;

	function __construct(){
		parent::__construct();
		$this->title = 'Настройка аккаунта';
		$this->getTemplate();
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
				$this->tpl = '/settings.html';
			break;
		}
	}

	#Меняем настройки аккаунта
	function editAcount(){
		if (empty($_POST)) return false;
		if (empty($_POST['f']) || !is_array($f=$_POST['f'])) return false;
		if ($f['pass']!=$f['pass2']) return false;
		if (sha1($f['oldpass']) != $this->object['Пароль']){
			$this->msg = 'Текущий пароль введен не верно!';
			 return false;
		}
		$pass = strip_tags(addslashes(trim($f['pass'])));
		$class_id = $this->object['class_id'];
		if ($class_id == 33){
			if ($this->db->update('class_'.$class_id, array("field_125"=>sha1($pass)), "WHERE `object_id`='".$this->object['id']."' AND `lang`='".$this->lang."'")){
				$this->msg = 'Изменения сохранены';
			}else {
				$this->msg = 'Ошибка! Неудалось сохранить изменения';
				return false;
			}
		}elseif ($class_id == 14){
			if ($this->db->update('class_'.$class_id, array("field_41"=>sha1($pass)), "WHERE `object_id`='".$this->object['id']."' AND `lang`='".$this->lang."'")){
				$this->msg = 'Изменения сохранены';
			}else {
				$this->msg = 'Ошибка! Неудалось сохранить изменения';
				return false;
			}
		}else{
			$this->msg = 'Ошибка! Неудалось сохранить изменения';
			return false;
		}
		$this->mail->from = 'info@'.$_SERVER['HTTP_HOST'];
		$this->mail->subject = 'Изменение пароля. Автоматическое уведомление с сайта '.$_SERVER['HTTP_HOST'].'';
		$this->mail->body = '<p>Ваш пароль успешно изменен. Новый пароль: <b>'.$pass.'</b></p>';
		$this->mail->send($this->object['Email']);
		return true;
	}

	#Удаляем аккаунт
	function deleteAcount(){
		if (empty($_POST)) return false;
		if (!isset($_POST['delete_acount']) || ($_POST['delete_acount']!=1)) return false;
		if (!isset($_POST['delete_word']) || ($_POST['delete_word']!="Удалить аккаунт")) return false;
		if ($this->objects->deleteObject($this->object['id'])){
			$this->db->delete("`objects` as o, `class_31` as c","WHERE o.id = c.object_id && c.field_161 = '".$this->object['id']."'");
			unset($_SESSION['u']);
			header("location: /");
		}
	}

}?>