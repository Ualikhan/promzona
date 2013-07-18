<?class Login extends api{
	public $error;
	
	function __construct(){
	      parent::__construct();
	}
	
	# DB ЗАПРОСЫ
	function getLoginForm(){
		return '<div id="page-login" class="tab-pane active">
						<form  method="post" action="/login">
                            <div class="alert alert-error normalize hide">
                                <button type="button" class="close" data-close="alert">×</button>
                                <strong>Пользователь с такими данными не зарегистрирован.</strong><br>
                                Проверьте раскладку клавиатуры, не нажата ли клавиша «Caps Lock» и попробуйте ввести логин и пароль еще раз.
                            </div>
							<div class="top">
								<h1 class="ta-c">Вход с паролем</h1>
									'.$this->error.'														
							</div>
							<div class="bottom">
								<div class="input-group">
									<label for="inpMail">Электронная почта:</label><input name="email" type="text" id="inpMail" class="required" />
								</div>
								<div class="input-group">
									<label for="inpPass">Пароль:</label><input name="password" type="password" id="inpPass" class="required" />
								</div>
								<div class="submit-group">
									<button class="btn btn-grey btn-grand">Войти</button>
									<a class="dashed" href="#page-reset-password" data-toggle="tab">Забыли пароль?</a>
								</div>
							</div>
						</form>
					</div>';
	}
	
	function getRestoreForm(){
		return '<div id="page-reset-password" class="tab-pane hide">
						<form action="/login" method="post">
                            <div class="alert alert-error normalize hide">
                                <button type="button" class="close" data-close="alert">×</button>
                                <strong>Пользователь с такими данными не зарегистрирован.</strong><br>
                                Проверьте раскладку клавиатуры, не нажата ли клавиша «Caps Lock» и попробуйте ввести логин и пароль еще раз.
                            </div>
                            <div class="top">
								<h1 class="ta-c">Восстановление пароля:</h1>
							</div>
							<div class="bottom">
								<div class="description">Если вы забыли свой пароль, введите электронную почту, указанную при регистрации, и нажмите кнопку «<b>Восстановить</b>». После этого вам будет выслано письмо с вашим новым паролем.</div>
								<div class="input-group">
									<label for="inpRecoverMail">Электронная почта:</label><input type="email"  name="email" id="inpRecoverMail" class="required" />
									<input type="hidden"  name="restore" id=""/>
								</div>
								<div class="submit-group">
									<button class="btn btn-grey btn-biger submit-btn">Восстановить</button>
									<a class="dashed" href="#page-login" data-toggle="tab">Вернуться к форме входа</a>
								</div>
							</div>
						</form>
					</div>';
	}
	
	function getRegInfoBlock(){
		return '<div class="register-promo-b">
					<table>

						<colgroup>
							<col width="485" />
							<col width="455" />
						</colgroup>

						<tbody>

							<tr>
								<td>
									<h2>Регистрация компании:</h2>
									<div>Зарегистрировавшись как компания на нашей площадке вам будут доступны функции:</div>
									<ul>
										<li>Размещение информации о компании с логотипом в каталоге компаний;</li>
										<li>Публикация объявлений о продаже, покупке и аренде оборудования и спецтехники вашей компании в соответствующих разделах;</li>
										<li>Получение заказов на опубликованные объявления вашей компании через систему заявок Promzona.kz;</li>
										<li>Публикация новостей вашей компании в разделе новости компаний;</li>
										<li>Публикация баннеров вашей компании на страницах площадки.</li>
									</ul>
								</td>
								<td>
									<h2>Регистрация пользователя:</h2>
									<div>Зарегистрировавшись как пользователь на нашей площадке вам будут доступны следующие функции:</div>
									<ul>
										<li>Заказ оборудования и спецтехники у поставщиков через систему заявок Promzona.kz;</li>
										<li>Публикация объявлений по поиску и аренде спецтехники и оборудования;</li>
										<li>Возможность добавить информацию о компании и начать продавать, а также получить доступ ко всему функционалу, доступному компаниям на Promzona.kz;</li>
									</ul>
								</td>
							</tr>

							<tr>
								<td>
									<a href="'._WWW_.'/registration/company/" class="btn btn-grey">Зарегистрировать компанию</a>
								</td>
								<td>
									<a href="'._WWW_.'/registration/user/" class="btn btn-grey">Зарегистрировать пользователя</a>
								</td>
							</tr>

						</tbody>

					</table>
				</div>';
	}
	
	function goLogin(){
		# проверка на пустоту
		if(!isset($_POST['email']) || empty($_POST['email'])){
			return 'emptyLogin';
		}
		if(!isset($_POST['password']) || empty($_POST['password'])){
			return 'emptyPass';
		}
		
		# проверка email
		$email = $_POST['email'];		
		$pass = $_POST['password'];	
		if(!$this->checkEmail($email)){
			return 'wrongFormatEmail';
		}
		
		# not exists
		if(!$this->existsEmail($email,$pass)){
			return 'wrongCredentials';
		}else{
			return 'ok';
		}
		
		
		return 'ok';
	}

	#Восстановление пароля
	function restore(){
		# проверка на пустоту
		if(!isset($_POST['email']) || empty($_POST['email'])){
			return 'emptyLogin';
		}
		# проверка email
		$email = $_POST['email'];
		if(!$this->checkEmail($email)){
			return 'wrongFormatEmail';
		}
		# not exists
		if(!$this->userExists($email)){
			return 'wrongCredentials';
		}
		return 'ok';
	}
	
	
	
	function getData($id){
		$a=$this->objects->getFullObject($id);
		return $a;
	}
	
	function checkEmail($email){
		return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email);
	}
	
	function existsEmail($email,$pass){
		$email = trim($email);
		// if ($user = $this->objects->getFullObjectsListByClassLogin(14, "AND o.active='1' 
		// AND (
		// (o.field_123='".$this->db->prepare($email)."' AND c.field_125='".sha1($pass)."') 
		// OR (c.field_41='".sha1($pass)."' AND o.field_101='".$this->db->prepare($email)."') 
		// )
		// ")){
			// return false;
		// }

		if($company = $this->objects->db->select("`objects` as o LEFT JOIN `class_33` as c ON o.id=c.object_id", "WHERE c.field_123='".$this->db->prepare($email)."' AND c.field_125='".sha1($pass)."' LIMIT 1") ){
			if ($company['active']==0) return false;
			# ВХОДИМ КАК КОМПАНИЯ
			$_SESSION['u']['id'] = $company['object_id'];
			$_SESSION['u']['role'] = $company['field_157'];
			$_SESSION['u']['email'] = $company['field_123'];
			$_SESSION['u']['telephone'] = $company['field_143'];
			$_SESSION['u']['fullname'] = $company['field_127'];
			$_SESSION['u']['region'] = $company['field_139'];
			$_SESSION['u']['address'] = $company['field_141'];
			$_SESSION['u']['credits'] = $company['field_171'];
			$_SESSION['u']['favorites'] = $this->getFavoritesAds($company['object_id']);
			return true;
		}elseif($user = $this->objects->db->select("`objects` as o LEFT JOIN `class_14` as c ON o.id=c.object_id", "WHERE c.field_101='".$this->db->prepare($email)."' AND c.field_41='".sha1($pass)."' LIMIT 1") ){
		 	if ($user['active']==0) return false;
			# ВХОДИМ КАК ЮЗЕР
			$_SESSION['u']['id'] = $user['object_id'];
			$_SESSION['u']['email'] = $user['field_101'];
			$_SESSION['u']['telephone'] = $user['field_44'];
			$_SESSION['u']['fullname'] = $user['field_40'];
			$_SESSION['u']['region'] = $user['field_43'];
			$_SESSION['u']['credits'] = $user['field_155'];
			$_SESSION['u']['role'] = $user['field_54'];
			$_SESSION['u']['favorites'] = $this->getFavoritesAds($user['object_id']);
			return true;	
		}else{
			return false;
		}
		
		// if(!$user = $this->objects->db->select("class_14", "WHERE `field_101`='".$this->db->prepare($email)."' AND `field_41`='".sha1($pass)."' LIMIT 1") ){
		// if(!$user = $this->objects->db->select("`objects` as o LEFT JOIN `class_14` as c ON o.id=c.object_id", "WHERE c.field_101='".$this->db->prepare($email)."' AND c.field_41='".sha1($pass)."' LIMIT 1") ){
			
			// if(!$company = $this->objects->db->select("class_33", "WHERE `field_123`='".$this->db->prepare($email)."' AND `field_125`='".sha1($pass)."' LIMIT 1") ){
			// if(!$company = $this->objects->db->select("`objects` as o LEFT JOIN `class_33` as c ON o.id=c.object_id", "WHERE c.field_123='".$this->db->prepare($email)."' AND c.field_125='".sha1($pass)."' LIMIT 1") ){
			// 	return false;
			// }else{
			// 	if ($company['active']==0) return false;
			// 	# ВХОДИМ КАК КОМПАНИЯ
			// 	$_SESSION['u']['id'] = $company['object_id'];
			// 	$_SESSION['u']['role'] = $company['field_157'];
			// 	$_SESSION['u']['email'] = $company['field_123'];
			// 	$_SESSION['u']['telephone'] = $company['field_143'];
			// 	$_SESSION['u']['fullname'] = $company['field_127'];
			// 	$_SESSION['u']['region'] = $company['field_139'];
			// 	$_SESSION['u']['address'] = $company['field_141'];
			// 	$_SESSION['u']['credits'] = $company['field_171'];
			// 	$_SESSION['u']['favorites'] = $this->getFavoritesAds($company['object_id']);
			// 	return true;
			// }
		
			
		//  }else{
		//  	if ($user['active']==0) return false;
		// 	# ВХОДИМ КАК ЮЗЕР
		// 	$_SESSION['u']['id'] = $user['object_id'];
		// 	$_SESSION['u']['email'] = $user['field_101'];
		// 	$_SESSION['u']['telephone'] = $user['field_44'];
		// 	$_SESSION['u']['fullname'] = $user['field_40'];
		// 	$_SESSION['u']['region'] = $user['field_43'];
		// 	$_SESSION['u']['credits'] = $user['field_155'];
		// 	$_SESSION['u']['role'] = $user['field_54'];
		// 	$_SESSION['u']['favorites'] = $this->getFavoritesAds($user['object_id']);
		// 	return true;	
		// }
	}

	#Проверяем пользователя на существование 
	function userExists($mail){
		$email = trim($mail);
		if ( ($u = $this->db->select("class_14", "WHERE `field_101`='".$this->db->prepare($email)."' LIMIT 1")) || ($u = $this->db->select("class_33", "WHERE `field_123`='".$this->db->prepare($email)."' LIMIT 1")) ){
		// if ( ($u = $this->db->select("class_14", "WHERE `field_101`='".$this->db->prepare($email)."' LIMIT 1")) ){
			$this->sendRestoreMail($mail, $u);
			return true;
		}else{
			return false;
		}
	}

	#Отправка на email 
	function sendRestoreMail($mail, $u_fields){
		// $u = array_merge($u, $this->objects->getObject($u['id'], $u['class_id']));
		if (!$u = $this->objects->getFullObject($u_fields['object_id'])) return false;
		$hash = md5(time().$u['id'].rand(999, 99999999));
		$hash_field_id = ($u['class_id'] == 14) ? 45 : 147;
		$this->db->update("class_".$u['class_id'], array("field_".$hash_field_id=>$hash), "WHERE `object_id`='".$u['id']."' LIMIT 1");
		
		$theme = 'Password recovery!';
		
		$html = array('<div><b>Здравствуйте!</b></div><br>');
		$html[]='<div>На сайте <a href="http://'.$_SERVER['HTTP_HOST'].'" target="_blank">http://'.$_SERVER['HTTP_HOST'].'</a> была подана заявка на восстановление пароля к аккаунту, который зарегистирован на этот e-mail.</div>';
		$html[]='<div>Если вы действительно подавали заявку на восстановление пароля, то перейдите по ссылке: <a href="http://'.$_SERVER['HTTP_HOST'].'/registration/changepass/'.$hash.'/" target="_blank">http://'.$_SERVER['HTTP_HOST'].'/registration/changepass/'.$hash.'/</a>.</div>';
		$html[]='<div>Иначе просто проигнорируйте это письмо.</div>';
		$html[]='<br><br>';
		$html[]='<div>---<br>С уважением, администрация.</div>';

		$body = join("", $html);
		
		$this->mail->from = 'info@'.str_replace('www.','', $_SERVER['HTTP_HOST']);
		$this->mail->headers = 'X-Mailer: PHP/' . phpversion();
		$this->mail->subject = $theme;
		$this->mail->body = $body;
		
		if ($this->mail->send($u['Email'])) return true; else return false;
		
	}

	#ПОлучаем избранные объявления
	function getFavoritesAds($id){
		if (!$ads = $this->db->select("favorites", "WHERE `user_id`='$id'")) return array();
		$out = array();
		foreach($ads as $k=>$ad){
			$out[$ad['ads_id']] = $ad['ads_id'];
		}
		return $out;
	}


	
}?>
