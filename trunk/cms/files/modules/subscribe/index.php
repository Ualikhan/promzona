<?
class subscribe extends appends{
private $path;

public $title;
public $info;
protected $js;
protected $css;
public $ico;

public $base;
protected $mail;

	function __construct(){
		parent::__construct();
		#PRIVATE
		$this->path = _MODULES_.'/'.basename(dirname(__FILE__));
		#CONFIG
		$this->title = 'Рассылка';
		$this->info = 'Формирование и отправка сообщений по электронной почте.';
		$this->js = array(
			_FILES_."/appends/ckeditor/ckeditor.js",
			_FILES_."/appends/ckeditor/adapters/jquery.js",
			$this->path."/js/init.js"
		);
		$this->css = array(
			$this->path."/css/page.css"
		);
		$this->ico = null;
		
		#SEND BASE / БАЗЫ ПОДПИСЧИКОВ
		$this->base = array(
			11=>'Подписчики с сайта'
		);
		
		#CLASS-mail-sender
		$this->mail = new mime_mail();
	}
	
	function ajax(){
		global $api;

	}
	
	function initHTML(){
		if( !count($this->base) ) return '<font color="red">Нужно создать подключить к модулю хотябы одну базу пользователей.</font>';
		
		#SEND MAILS
		if(!empty($_POST) && !empty($_POST['mail']) && is_array($mail = $_POST['mail'])){
			$this->mail->from = 'info@'.str_replace('www.','', $_SERVER['HTTP_HOST']);
			$this->mail->headers = 'X-Mailer: PHP/' . phpversion();
			$this->mail->subject = $mail['theme'];
			$this->mail->body = $mail['text'];
			
			if(!empty($_FILES['files']) && is_array($files = $_FILES['files'])){
				foreach($files['name'] as $k=>$name){
					if( empty($name) || empty($files['tmp_name'][$k]) ) continue;
					$this->mail->add_attachment(file_get_contents($files['tmp_name'][$k]), $name, $files['type'][$k]);
				}
			}
			
			$count = 0;
			foreach($this->db->select("objects", "WHERE `head`='".$mail['base']."'", "name") as $to){
				$this->mail->send( $to );
				$count++;
			}
			echo '<div id="info-message" style="margin-bottom:20px;"><font color="green">Сообщение разослано на '.$this->sklon($count, array('адрес', 'адреса', 'адресов')).' электронной почты.</font></div>';
		}
		
		$out = array('<div><form class="subscribe-form" enctype="multipart/form-data" method="post" onsubmit="return checkForm(this)">');
			$out[]='<table class="form-table">';
				$out[]='<tr>';
					$out[]='<td class="left-part">';
						$out[]='<h2>Тема</h2>';
						$out[]='<div>';
							$out[]='<input type="text" id="theme" name="mail[theme]">';
						$out[]='</div><br>';
						$out[]='<h2>Текст сообщения</h2>';
						$out[]='<div>';
							$out[]='<textarea id="editor-block" name="mail[text]" style="height:200px;"></textarea>';
						$out[]='</div><br>';
						$out[]='<div><button class="button approve">отправить</button><!--<button class="button cancel">очистить</button>--></div>';
					$out[]='</td>';
					$out[]='<td class="right-part">';
						$out[]='<h2>База рассылки</h2>';
						$out[]='<div class="basename">';
						if(count($this->base)>1){
							$out[]='<select name="mail[base]">';
								foreach($this->base as $id=>$name){
									$out[]='<option value="'.$id.'">'.$name.' ['.$this->db->count("objects", "WHERE `head`='".$id."'").']</option>';
								}
							$out[]='</select>';
						}else{
							$key = key($this->base);
							$out[]='<input type="hidden" name="mail[base]" value="'.$key.'"> '.$this->base[$key].' ['.$this->db->count("objects", "WHERE `head`='".$key."'").']';
						}
						$out[]='</div><br>';
						$out[]='<h2>Файлы</h2>';
						$out[]='<ul id="files-list"></ul>';
						$out[]='<a href="#добавить файл" id="add-file-btn">+добавить файл</a>';
					$out[]='</td>';
				$out[]='</tr>';
			$out[]='</table>';
		$out[]='</form></div>';
		return join("\n", $out);
	}
#USER FUNCTIONS
}
?>