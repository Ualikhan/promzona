<?

include_once 'cms/public/api.php';

$api->header = '/general/header.html';
$api->template = '/registration.html';
$api->footer = '/general/footer.html';

# ЛОВИМ GET-ы
$action = @$_GET['action'];


# ПОДКЛЮЧАЕМ JS
$api->scripts = array(
	'<script src="'._WWW_.'/js/register.js"></script>'
);

# ПОДКЛЮЧАЕМ СТИЛИ
// $api->styles = array(
// 	'<link rel="stylesheet" href="'._WWW_.'/css/register.css" type="text/css" >'
// );

# ОСНОВНОЙ КЛАСС
include_once _MODS_ABS_.'/registration/registration_class.php';
$reg = new Registration();

# ДОПОЛНИТЕЛЬНЫЕ КЛАССЫ

$e404 = '
    <div class="page-404">
        <div class="header ptsans">
            Ошибка 404
        </div>
        <div class="line">Неправильно набран адрес, или такой страницы на сайте больше не существует.</div>
        <div class="line">Вы можете перейти на <a class="bd-beigt" href="/">главную страницу</a>.</div>
    </div>
';

# ЗАГОЛОВОК СТРАНИЦЫ
$api->header(array('page-title'=>'Регистрация пользователя'));


# ПУСТЫЕ ПЕРЕМЕННЫЕ ОШИБОК
	$error_email = '';
	$error_password = '';
	$error_password_confirm = '';
	$error_fullname = '';
	$error_region = '';
	$error_telephones = '';
	$error_agree = '';
	$error_logo = '';
	$error_captcha = '';

	$error_email_info = '';
	$error_password_info = '';
	$error_password_confirm_info = '';
	$error_fullname_info = '';
	$error_region_info = '';
	$error_telephones_info = '';
	$error_agree_info = '';
	$error_logo_info = '';
	$error_captcha_info = '';

	$error_activitytype = '';
	$error_activitytype_info = '';
	$error_desc	 = '';
	$error_desc_info = '';
	$error_contactname = '';
	$error_contactname_info = '';
	$error_position = '';
	$error_position_info = '';
	$error_address = '';
	$error_address_info = '';
	$error_site = '';
	$error_site_info = '';



#------------- РЕГИСТРИРУЕМ ПОЛЬЗОВАТЕЛЯ ----------------#

if(isset($_POST['user'])){
	//die('Registration');
	# ПЕРЕМЕННЫЕ ОШИБОК

	// $error_email_info = '<div class="middle-tip-b error">Введите корректный адрес электронной почты.</div>';
	// $error_password_info = '<div class="middle-tip-b error">Длина должна быть от 5 до 30 символов.</div>';
	// $error_password_confirm_info = '<div class="middle-tip-b error" id="passMatchError">Пароли не совпадают.</div>';
	// $error_fullname_info = '<div class="middle-tip-b error">Введите корректное имя.</div>';
	// $error_region_info = '<div class="middle-tip-b error">Введите корректное название региона.</div>';
	// $error_telephones_info = '<div class="middle-tip-b error">Введите корректный номер телефона.</div>';
	// $error_agree_info = '<div class="middle-tip-b error" style="color:#F66;margin-bottom:10px">Чтобы продолжить, необходимо согласиться с условиями Пользовательского соглашения.</div>';
	// $error_logo = '<div class="middle-tip-b error">Ошибка! Выбранное изображение не соотвествует поддерживаемым форматам.</div>';
	// $error_captcha_info = '<div class="middle-tip-b error" style="margin-left:0px">Ошибка! Укажите корректный текст с картинки.</div>';

	$head_object_id = 239;
	$class_id = 14;

	if( !preg_match("/^[\d\w\.-]+@([\d\w-]+)((\.[\w\d-]+)+)?\.\w{2,6}$/", (@$_POST['email'])) ){
		$error_email = 'error';
		$error_email_info = '<div class="middle-tip-b error">Введите корректный адрес электронной почты.</div>';

	}elseif (!!($u = $api->db->select("class_33", "WHERE `field_123`='".@$_POST['email']."' LIMIT 1")) || !!($u = $api->db->select("class_14", "WHERE `field_101`='".@$_POST['email']."' LIMIT 1")) ){
				$error_email = 'error';
				$error_email_info = '<div class="middle-tip-b error">Компания или пользователь с указанным адресом электронной почты уже зарегистрирована в системе.</div>';
	}else if(@$_POST['agree']!=='1'){
		$error_agree = 'error';
		$error_agree_info = '<div class="middle-tip-b error" style="color:#F66;margin-bottom:10px">Чтобы продолжить, необходимо согласиться с условиями Пользовательского соглашения.</div>';

	}else if(@$_POST['fullname']==''){
		$error_fullname = 'error';
		$error_fullname_info = '<div class="middle-tip-b error">Введите Ваше полное имя.</div>';

	}else if(!preg_match('/^[A-Za-zа-яА-Я \-ё]+$/ui',@$_POST['fullname'])){
		$error_fullname = 'error';
		$error_fullname_info = '<div class="middle-tip-b error">Введите корректное имя.</div>';

	}else if(@$_POST['password']==''){
		$error_password = 'error';
		$error_password_info = '<div class="middle-tip-b error">Укажите пароль.</div>';

	}else if(strlen(@$_POST['password'])<5 || strlen(@$_POST['password'])>30){
		$error_password = 'error';
		$error_password_info = '<div class="middle-tip-b error">Длина пароля должна быть от 5 до 30 символов. </div>';

	}else if(!preg_match('/^[A-Za-z0-9_\-\$\.]+$/',@$_POST['password'])){
		$error_password = 'error';
		$error_password_info = '<div class="middle-tip-b error">В пароле можно использовать цифры, латинские буквы, символы «-», «.», «_», «$».</div>';

	}else if(!!($u = $api->db->select("objects", "WHERE `head`='".$head_object_id."' AND `name`='".@$_POST['email']."' LIMIT 1")) ){
		//echo '<p>Пользователь с таким e-mail адресом уже есть в системе.</p>';
		$error_email = 'error';
		$error_email_info = '<div class="middle-tip-b error">Указанный адрес электронной почты уже зарегистрирован в системе.</div>';

	}else if(@$_POST['password_confirm']!=@$_POST['password']){
		//echo '<p>Пароли не совпадают.</p>';
		$error_password_confirm = 'error';
		$error_password_confirm_info = '<div class="middle-tip-b error" id="passMatchError">Пароли не совпадают.</div>';

	// }else if(@$_POST['captcha'] = @$_SESSION['captcha_keystring']){
	}else if(@$_POST['captcha'] != @$_SESSION['captcha_keystring']){
		$error_captcha = 'error';
		$error_captcha_info = '<div class="captcha-error">Неверный код</div>';

	}else if(@$_POST['region']==''){
		$error_region = 'error';
		$error_region_info = '<div class="middle-tip-b error">Введите корректное название региона.</div>';

	}else if(empty($_POST['f']['phone'][0])){
		$error_telephones= 'error';
		$error_telephones_info = '<div class="middle-tip-b error">Укажите номер телефона.</div>';

	// }else if(!preg_match('/^[0-9 \-\+]+$/',@$_POST['telephones'])){
	// 	$error_telephones= 'error';
	// 	$error_telephones_info = '<div class="middle-tip-b error">Введите корректный номер телефона.</div>';

	}else{
		#ОБЪЕКТ
		$object = array(
			"active"=>0,
			"head"=>$head_object_id,
			"name"=>$_POST['fullname'],
			"class_id"=>$class_id,
			"sort"=>time()
		);

		#ПОЛЯ
		$fields = array(
			54=>'user',
			101=>$_POST['email'],//email
			40=>$_POST['fullname'],//фио,
			41=>sha1($_POST['password']),//пароль,
			//42=>$_POST['birthday'],//день рождения,
			43=>$_POST['region'],//горд проживания
			44=>join ("\n", $_POST['f']['phone'])//телефон
		);

		#ЛОЖИМ В БАЗУ, ВЫДАЕМ СООБЩЕНИЕ
		$lang = $api->lang;
		$api->lang = 'ru';
		if( !!$object_id = $api->objects->createObjectAndFields($object, $fields) ){

			//$base_id = 14;
			$pass_field_id = 41;
			$hash_field_id = 45;
			$hash = md5(time().$object_id.rand(999, 99999999));
			$api->db->update("class_14", array("field_".$hash_field_id=>$hash), "WHERE `object_id`='".$object_id."' LIMIT 1");
			$link = 'http://'.$_SERVER['HTTP_HOST'].'/registration/confirm/'.$hash.'/';

			$mess = '<p>Добро пожаловать на '.$_SERVER['HTTP_HOST'].'.</p>

				<p>Прежде чем вы сможете использовать ваш новый аккаунт вы должны активировать его - это гарантирует, что адрес электронной почты, который вы использовали действителен и принадлежит вам. Чтобы активировать свою учетную запись, нажмите на ссылку ниже или скопируйте и вставьте все это в адресную строку вашего браузера:</p>

				<p><a href="'.$link.'" target="_blank">'.$link.'</a></p>

				<p>Ваш пароль: '.$_POST['password'].'</p>

				<p>Спасибо!</p>

				<p>С уважением, '.$_SERVER['HTTP_HOST'].'.</p>';

			$api->mail->from = 'info@'.str_replace('www.','', $_SERVER['HTTP_HOST']);
			$api->mail->headers = 'X-Mailer: PHP/' . phpversion();
			$api->mail->subject = 'Пожалуйста, подтвердите свой адрес электронной почты';
			$api->mail->body = $mess;

			$api->mail->send(trim(@$_POST['email']));
			unset($_POST);
			echo '<p class="ok">Спасибо! Вам отправлено письмо для подтверждения регистрации. Пожалуйста, следуйте инструкциям в этом письме.</p>';
			// echo '<p class="ok">'.$trans[$api->lang]['Вы успешно зарегистрированы! Теперь вы можете авторизоваться в системе.'].'</p>';//<script type="text/javascript"> $(function(){ $("#reg-block").hide(); }); </script>
			exit( $api->footer() );
		}else{
			echo '<p>Ошибка базы данных.</p>';
		}
	}




#------------- РЕГИСТРИРУЕМ КОМПАНИЮ ----------------#
}else if(isset($_POST['company'])){
	# Ообязательные поля:
	# email
	# password
	# name
	# activity
	# desc
	# contactname
	# position
	# region
	# address
	# tel
	# site
	# code
	# agree




	$head_object_id = 303;
	$class_id = 33;



	$error = 0;

	# соглашение
	if(@$_POST['agree']!=='1'){
		$error_agree = 'error';
		$error_agree_info = '<div class="middle-tip-b error" style="color:#F66;margin-bottom:10px">Чтобы продолжить, необходимо согласиться с условиями Пользовательского соглашения.</div>';
		$error++;
	}
	# капча

	if( $_POST['captcha'] == '' || empty($_POST['captcha']) ){
		$error_captcha = 'error';
		$error_captcha_info = '<div class="captcha-error">Укажите, пожалуйста, код с картинки.</div>';
		$error++;
	}else{
		
		if(@$_POST['captcha']!=@$_SESSION['captcha_keystring']){
		$error_captcha = 'error';
		$error_captcha_info = '<div class="captcha-error">Код с картинки введён неверно.</div>';
		}
		
	}


	# имейл
	if($_POST['email'] == '' || empty($_POST['email'])){
		//$error .= '<p>Укажите, пожалуйста, email.</p>';
		$error_email = 'error';
		$error_email_info = '<div class="middle-tip-b error">Введите адрес электронной почты.</div>';
		$error++;
	}else{
		if( !preg_match("/^[\d\w\.-]+@([\d\w-]+)((\.[\w\d-]+)+)?\.\w{2,6}$/", (@$_POST['email'])) ){
			$error_email = 'error';
			$error_email_info = '<div class="middle-tip-b error">Введите корректный адрес электронной почты.</div>';
			$error++;
		}else{
			if(!!($u = $api->db->select("class_33", "WHERE `field_123`='".@$_POST['email']."' LIMIT 1")) || !!($u = $api->db->select("class_14", "WHERE `field_101`='".@$_POST['email']."' LIMIT 1")) ){
				$error_email = 'error';
				$error_email_info = '<div class="middle-tip-b error">Компания или пользователь с указанным адресом электронной почты уже зарегистрирована в системе.</div>';
				$error++;
			}
		}
	}
	# имя компании
	if($_POST['fullname'] == '' || empty($_POST['fullname'])){
		$error_fullname = 'error';
		$error_fullname_info = '<div class="middle-tip-b error">Введите полное наименование компании.</div>';
		$error++;
	}

	# тип деят-ти
	if(@$_POST['activitytype'] == '' || empty($_POST['activitytype']) || @$_POST['activitytype']=='0'){
		$error_activitytype = 'error';
		$error_activitytype_info = '<div class="middle-tip-b error">Вы не указали тип деятельности компании.</div>';
		$error++;
	}
	//echo $_POST['activitytype'].'-';


	# logo

	if(@$_FILES['logo'] == '' || empty($_FILES['logo']) || @$_FILES['logo']=='0'){
		$logo = '';
	}else{
		$logo = '';
		$error_logo = 'error';
		$error_logo_info = '<div class="middle-tip-b error">Ошибка! Выбранное изображение не соотвествует поддерживаемым форматам.</div>';
		$error++;
		}


	# описание
	if(@$_POST['desc'] == '' || empty($_POST['desc'])){
		$error_desc = 'error';
		$error_desc_info = '<div class="middle-tip-b error">Вы не указали описание компании.</div>';
		$error++;
	}else if(strlen($_POST['desc'])>800){
		$error_desc = 'error';
		$error_desc_info = '<div class="middle-tip-b error">Максимальное количество символов текста 800. У вас - '.strlen($_POST['desc']).'</div>';
		$error++;
		}

	# контактное лицо
	if(@$_POST['contactname'] == '' || empty($_POST['contactname'])){
		$error_contactname = 'error';
		$error_contactname_info = '<div class="middle-tip-b error">Вы не указали контактное лицо компании.</div>';
		$error++;
	}elseif(!preg_match('/^[A-Za-zа-яА-Я \-ё]+$/ui',@$_POST['contactname'])){
		$error_contactname = 'error';
		$error_contactname_info = '<div class="middle-tip-b error">Введите корректное контактное лицо компании.</div>';
		$error++;
	}
	# позиция
	if(@$_POST['position'] == '' || empty($_POST['position'])){
		$error_position = 'error';
		$error_position_info = '<div class="middle-tip-b error">Вы не указали позицию контактного лица компании.</div>';
		$error++;
	}elseif(!preg_match('/^[A-Za-zа-яА-Я \-ё]+$/ui',@$_POST['position'])){
		$error_position = 'error';
		$error_position_info = '<div class="middle-tip-b error">Введите корректное позицию контактного лица компании..</div>';
		$error++;
	}
	# регион
	if(@$_POST['region'] == '' || empty($_POST['region'])){
		$error_region = 'error';
		$error_region_info = '<div class="middle-tip-b error">Вы не указали регион.</div>';
		$error++;
	}elseif(!preg_match('/^[A-Za-zа-яА-Я \-ё\,]+$/ui',@$_POST['region'])){
		$error_region = 'error';
		$error_region_info = '<div class="middle-tip-b error">Введите корректный регион.</div>';
		$error++;
	}
	# адрес
	if(@$_POST['address'] == '' || empty($_POST['address'])){
		$error_address = 'error';
		$error_address_info = '<div class="middle-tip-b error">Вы не указали адрес.</div>';
		$error++;
	}
	# telephones
	if(empty($_POST['f']['phone']) || !is_array($_POST['f']['phone'])){
		$error_telephones = 'error';
		$error_telephones_info = '<div class="middle-tip-b error">Вы не указали телефон(ы).</div>';
		$error++;
		$phones_arr = array();
	}else{
		$phones = $_POST['f']['phone'];
		$phones_arr = array();
		foreach ($phones as $p){
			$phones_arr[] = $p;
		}
	}



	# пароль
	if($_POST['password']=='' || empty($_POST['password'])){
		$error_password = 'error';
		$error_password_info = '<div class="middle-tip-b error">Укажите, пожалуйста, свой пароль.</div>';
		$error++;
	}else{
		if(strlen(@$_POST['password'])<5 || strlen(@$_POST['password'])>30){
			$error_password = 'error';
			$error_password_info = '<div class="middle-tip-b error">Длина пароля должна быть от 5 до 30 символов. </div>';
			$error++;
			}

		if($_POST['password_confirm'] == '' || empty($_POST['password_confirm'])){
			$error_password_confirm = 'error';
			$error_password_confirm_info = '<div class="middle-tip-b error">Подтвердите, пожалуйста, свой пароль.</div>';
			$error++;
		}else{
			if($_POST['password_confirm']!=$_POST['password']){
				$error_password_confirm = 'error';
				$error_password_confirm_info = '<div class="middle-tip-b error" id="passMatchError">Пароли не совпадают.</div>';
				$error++;
			}
			}
		}

	if(@$_POST['captcha'] != @$_SESSION['captcha_keystring']){
		$error_captcha = 'error';
		$error_captcha_info = '<div class="captcha-error">Неверный код</div>';
		$error++;
	}


	if($error < 1){
		#ОБЪЕКТ
		$object = array(
			"active"=>0,
			"head"=>$head_object_id,
			"name"=>$_POST['fullname'],
			"class_id"=>$class_id,
			"sort"=>time()
		);

		#ПОЛЯ
		$fields = array(
			157=>'company',
			123=>$_POST['email'],//email
			125=>sha1($_POST['password']),//пароль,
			127=>$_POST['fullname'],
			129=>$_POST['activitytype'],
			131=>$_POST['desc'],
			133=>$logo,
			135=>$_POST['contactname'],
			137=>$_POST['position'],
			139=>$_POST['region'],
			141=>$_POST['address'],
			143=>join("\n",$phones_arr),
			145=>$_POST['site'],
			259 => 0
		);

		//print_r($fields);
		//die("fuckinshit");

		#ЛОЖИМ В БАЗУ, ВЫДАЕМ СООБЩЕНИЕ
		$lang = $api->lang;
		$api->lang = 'ru';
		if( !!$object_id = $api->objects->createObjectAndFields($object, $fields) ){

			$base_id = 33;
			$pass_field_id = 125;
			$hash_field_id = 147;
			$hash = md5(time().$object_id.rand(999, 99999999));
			$api->db->update("class_33", array("field_".$hash_field_id=>$hash), "WHERE `object_id`='".$object_id."' LIMIT 1");
			$link = 'http://'.$_SERVER['HTTP_HOST'].'/registration/confirm/'.$hash.'/';

			$mess = '<p>Добро пожаловать на '.$_SERVER['HTTP_HOST'].'.</p>

				<p>Прежде чем вы сможете использовать ваш новый аккаунт вы должны активировать его - это гарантирует, что адрес электронной почты, который вы использовали действителен и принадлежит вам. Чтобы активировать свою учетную запись, нажмите на ссылку ниже или скопируйте и вставьте все это в адресную строку вашего браузера:</p>

				<p><a href="'.$link.'" target="_blank">'.$link.'</a></p>

				<p>Ваш пароль: '.$_POST['password'].'</p>

				<p>Спасибо!</p>

				<p>С уважением, '.$_SERVER['HTTP_HOST'].'.</p>';

			$api->mail->from = 'info@'.str_replace('www.','', $_SERVER['HTTP_HOST']);
			$api->mail->headers = 'X-Mailer: PHP/' . phpversion();
			$api->mail->subject = 'Пожалуйста, подтвердите свой адрес электронной почты';
			$api->mail->body = $mess;

			$api->mail->send(trim(@$_POST['email']));
			unset($_POST);
			// echo '<pre>',print_r($_POST),'</pre>';
			echo '<p class="ok">Спасибо! Вам отправлено письмо для подтверждения регистрации. Для подтверждения регистрации вашей учётной записи, пожалуйста, следуйте инструкциям в этом письме.</p>';
			// echo '<p class="ok">Вы успешно зарегистрированы! Теперь вы можете авторизоваться в системе.</p>';//<script type="text/javascript"> $(function(){ $("#reg-block").hide(); }); </script>
			exit( $api->footer() );
		}else{
			echo '<p>Ошибка.</p>';
		}
	}

}






#-------------- ВЫВОДИМ ВЕСЬ КОНТЕНТ СТРАНИЦЫ -------------#

# Текст объекта Регистрация
$a = $reg->getData(175);
//echo $a['Текст'];


if($action=='user'){
	# ВЫВОДИМ ФОРМУ РЕГ ПОЛЬЗОВАТЕЛЯ
	//echo $reg->showForm($_POST);
	$outRegForm = '<form action="" method="post" class="form-inline">';
	$outRegForm .= '<h1>Регистрация пользователя</h1>';
	//$outRegForm .= $error;
	$outRegForm .= '<div class="step-b">';
	$outRegForm .= '<h3><i class="step-number">1</i>Электронная почта и пароль для входа на сайт:</h3>

							<div class="input-group clearfix '.$error_email.'">
								<label class="left" for="registerEmail">
									Электронная почта: <sup class="red">*</sup>
									<i class="icon-question-sign icon-yellow helper-popup">
										<div>Электронная почта, которую вы указываете, будет использоваться в качестве логина для входа в личный кабинет.</div>
									</i>
								</label>
								<input class="required w-320" type="email" id="registerEmail" autofocus value="'.@$_POST['email'].'" name="email"/>
								'.$error_email_info.'
							</div>';
	$outRegForm .= '<div class="input-group clearfix '.$error_password.'">
								<div class="right-tip-b">
									Длина пароля от 5 до 30 символов. Можно использовать цифры, латинские буквы, символы «-», «.», «_», «$»
								</div>
								<label class="left" for="passInput">
									Пароль: <sup class="red">*</sup>
									<i class="icon-question-sign icon-yellow helper-popup"></i>
								</label>
								<input class="required w-320" type="password" id="passInput" name="password" value="'.@$_POST['password'].'" maxlength="30">
								'.$error_password_info.'
							</div>';
	$outRegForm .= '<div class="input-group clearfix '.$error_password_confirm.'">
								<label class="left" for="passConfirmInput">
									Повторите пароль: <sup class="red">*</sup>
									<i class="icon-question-sign icon-yellow helper-popup"></i>
								</label>
								<input class="required w-320" type="password" id="passConfirmInput" name="password_confirm" value="'.@$_POST['password_confirm'].'" maxlength="30">
                                <div class="middle-tip-b error hide" id="passMatchError">Пароли не совпадают.</div>
                                <div class="middle-tip-b error hide" id="passRulesError">Можно использовать только цифры, латинские буквы, символы «-», «.», «_», «$»</div>
								'.$error_password_confirm_info.'
							</div>';
	$outRegForm .= '</div>';

	$outRegForm .= '<div class="step-b">';
	$outRegForm .= '<h3><i class="step-number">2</i>Контактная инфомация:</h3>';
	$outRegForm .= '<div class="input-group clearfix '.$error_fullname.'">
								<label class="left" for="registerContactPerson">
									Ваше полное имя: <sup class="red">*</sup>
									<i class="icon-question-sign icon-yellow helper-popup"></i>
								</label>
								<input class="required w-440" type="text" id="registerContactPerson" value="'.@$_POST['fullname'].'" name="fullname"/>
								'.$error_fullname_info.'
							</div>';
	$outRegForm .= '<div class="input-group clearfix '.$error_region.'">
								<label class="left" for="registerRegion">
									Регион: <sup class="red">*</sup>
									<i class="icon-question-sign icon-yellow helper-popup"></i>
								</label>
								<input class="required w-440 typeaheaded" autocomplete="off" data-typeaheaded="region" type="text" id="registerRegion" value="'.@$_POST['region'].'" name="region"/>
								'.$error_region_info .'
								<div class="middle-tip-b">
									Начните вводить название города, затем выберите его из списка.
								</div>
							</div>';
	if (empty($_POST['f']['phone'][1])){
	$outRegForm .= '<div class="phones-input-group">
                                    <div class="input-group clearfix ">
                                        <label class="left" for="registerPhone1">
                                            Телефоны: <sup class="red">*</sup>
                                            <i class="icon-question-sign icon-yellow helper-popup"></i>
                                        </label>
                                        <input class="w-160 required phoneInput" type="text" id="registerPhone1" name="f[phone][0]" value="'.@$_POST['f']['phone'][0].'">
                                        <button class="btn  btn-smaller ml-10 addPhoneInput" type="button"><i class="icon-plus-sign"></i> Добавить телефон </button>
                                        <div class="middle-tip-b"> Рекомендуем писать телефонные номера в формате: +7 777 123-45-67</div>
                                    </div>
                                </div>';
    }else{
	$outRegForm .= '<div class="phones-input-group handled">
                        <div class="input-group clearfix ">
                            <label class="left" for="registerPhone1">
                                Телефоны: <sup class="red">*</sup>
                                <i class="icon-question-sign icon-yellow helper-popup"></i>
                            </label>
                            <input class="w-160 required phoneInput" type="text" id="registerPhone1" name="f[phone][0]" value="'.@$_POST['f']['phone'][0].'">
                            <button class="btn btn-white btn-smaller ml-10 removeFirstPhoneInput" type="button"><i class="icon-remove"></i></button>
                        </div>';
   
    foreach ($_POST['f']['phone'] as $k=>$phone){
    	if ($k == 0) continue;
    	if ($k == (count($_POST['f']['phone']) - 1)){
    		$outRegForm .= '
    			<div class="input-group clearfix ml-220">
    				 <input class="w-160 required phoneInput" type="text" id="registerPhone1" name="f[phone]['.$k.']" value="'.@$_POST['f']['phone'][$k].'">
                     <button class="btn btn-white btn-smaller ml-10 removeFirstPhoneInput" type="button"><i class="icon-remove"></i></button>
                     <button class="btn  btn-smaller ml-10 addPhoneInput" type="button"><i class="icon-plus-sign"></i> Добавить телефон </button>
                     <div class="middle-tip-b"> Рекомендуем писать телефонные номера в формате: +7 777 123-45-67</div>
    			</div>
    		';
    	}else{
    		$outRegForm .= '
    			<div class="input-group clearfix ml-220">
    				 <input class="w-160 required phoneInput" type="text" name="f[phone]['.$k.']" value="'.@$_POST['f']['phone'][$k].'">
                     <button class="btn btn-white btn-smaller ml-10 removeFirstPhoneInput" type="button"><i class="icon-remove"></i></button>
    			</div>
    		';
    	}
    }
    $outRegForm .= '</div>';
    }
	$outRegForm .= '<div class="input-group clearfix '.$error_captcha.'">
								<label class="left" for="registerCaptcha">
									Проверочный код: <sup class="red">*</sup>
									<i class="icon-question-sign icon-yellow helper-popup"></i>
								</label>
								<img src="'._FILES_.'/appends/kcaptcha/" alt="" class="captcha-image" />
								<div class="captcha-input-wrapper">
									<input class="required w-320" type="text" id="registerCaptcha" maxlength="6" name="captcha"/>
									'.$error_captcha_info.'
									<a href="#" onclick="return newCaptcha( $(\'.captcha-image\') )" class="pseudo dashed grey"><i class="icon-refresh icon-grey"></i> <span>Обновить картинку</span></a>
								</div>
							</div>';
	$outRegForm .= '<div class="submit-group '.$error_agree.'">

								<input class="required" type="checkbox" class="required" id="registerAgree" '.(@$_POST['agree']=='1'?'checked':'').' value="1" name="agree"/> <label class="left" for="registerAgree">Я согласен и принимаю условия <a '.$api->getLink(205).' target="blank">Пользовательского соглашения</a></label>
								'.$error_agree_info.'
								<input type="hidden" value="1" name="user">
								<button type="submit" class="btn btn-grey btn-grand">Зарегистрировать пользователя</button>
							</div>';
	$outRegForm .= '</div>';
	$outRegForm .= '</form>';

	# ГРУЗИМ РЕГИОНЫ
	if(!!$a=$api->objects->getFullObjectsList(257)){
		$out = array();
		foreach($a as $b){
			$out[] = "'".$b['Название']."'";
		}
		// $outRegForm .= "<script>var regions = [".join(',',$out)."]; </script>";
	}else{
		// $outRegForm .= "<script>var regions = ['Алматы', 'Астана']; </script>";
	}
	echo $outRegForm;


### ФОРМА КОМПАНИИ
}else if($action=='company'){
		# получаем Типы деят-ти
		if($a=$api->objects->getFullObjectsList(240)){
			$out = '';
			foreach($a as $b){
				$out .= '<option value="'.$b['Значение'].'">'.$b['Значение'].'</option>';
			}
		}else{
			$out = '';
		}
		# ВЫВОДИМ ФОРМУ РЕГ КОМПАНИИ
		//echo $reg->showForm($_POST);
		$outRegForm = '<form action="" method="post" class="form-inline">';
		$outRegForm .= '<h1>Регистрация компании</h1>';
		$outRegForm .= '<div class="step-b">';
		$outRegForm .= '<h3><i class="step-number">1</i>Электронная почта и пароль для входа на сайт:</h3>';
		$outRegForm .= '<div class="input-group clearfix '.$error_email.'">
								<label class="left" for="registerEmail">
									Электронная почта: <sup class="red">*</sup>
									<i class="icon-question-sign icon-yellow helper-popup">
										<div>Электронная почта, которую вы указываете, будет использоваться в качестве логина для входа в личный кабинет.</div>
									</i>
								</label>
								<input class="required w-320" type="email" id="registerEmail" autofocus value="'.@$_POST['email'].'" name="email"/>
								'.$error_email_info.'
							</div>
							';
		$outRegForm .= '<div class="input-group clearfix '.$error_password.'">
								<div class="right-tip-b">
									Длина пароля от 5 до 30 символов. Можно использовать цифры, латинские буквы, символы «-», «.», «_», «$»
								</div>
								<label class="left" for="passInput">
									Пароль: <sup class="red">*</sup>
									<i class="icon-question-sign icon-yellow helper-popup"></i>
								</label>
								<input class="required w-320" type="password" id="passInput" name="password" maxlength="30">
								'.$error_password_info.'
							</div>
							';
		$outRegForm .= '<div class="input-group clearfix '.$error_password_confirm.'">
								<label class="left" for="passConfirmInput">
									Повторите пароль: <sup class="red">*</sup>
									<i class="icon-question-sign icon-yellow helper-popup"></i>
								</label>
								<input class="required w-320" type="password" id="passConfirmInput" name="password_confirm" maxlength="30">
                                <div class="middle-tip-b error hide" id="passMatchError">Пароли не совпадают.</div>
                                <div class="middle-tip-b error hide" id="passRulesError">Можно использовать только цифры, латинские буквы, символы «-», «.», «_», «$»</div>
								'.$error_password_confirm_info.'

							</div>';
		$outRegForm .= '</div>';

		$outRegForm .= '<div class="step-b">';
		$outRegForm .= '<h3><i class="step-number">2</i>Информация о компании:</h3>';
		$outRegForm .= '<div class="input-group clearfix '.$error_fullname.'">
								<label class="left" for="registerCompanyName">
									Название компании: <sup class="red">*</sup>
									<i class="icon-question-sign icon-yellow helper-popup"></i>
								</label>
								<input class="required w-440" type="text" id="registerCompanyName" value="'.@$_POST['fullname'].'" name="fullname"/>
								'.$error_fullname_info.'
							</div>
							';
		$outRegForm .= '<div class="input-group clearfix '.$error_activitytype.'">
								<div id="activitytypeID" style="display:none">'.@$_POST['activitytype'].'</div>
								<label class="left" for="registerActivity">
									Тип деятельности: <sup class="red">*</sup>
									<i class="icon-question-sign icon-yellow helper-popup"></i>
								</label>
								<select class="required w-320" name="activitytype" id="registerActivity">
									<option value="0">Выберите тип деятельности</option>
									'.$out.'
								</select>
								'.$error_activitytype_info.'
							</div>
							';
		$outRegForm .= '<div class="input-group clearfix '.$error_logo.'">
                            <label class="left">
                                Логотип:
                                <i class="icon-question-sign icon-yellow helper-popup"></i>
                            </label>
                            <div class="logoUploader"></div>
                            '.$error_logo_info.'
                        </div>

                        <div class="ml-220 input-group logoUploaderResponse logo-uploader-response '.(empty($_POST['comapnyLogo'])?'hide':'').'">
                        	<img src="/cms/uploads_temp/'.@$_POST['comapnyLogo'].'" alt="" class="mr-10 va-t">
                            <a class="pseudo dashed red fz-12 delLogo" href="javascript:void(0)"><i class="icon-trash icon-red"></i> <span>Удалить фото</span></a>
                            <input type="hidden" name="comapnyLogo" value="'.(!empty($_POST['comapnyLogo'])?$_POST['comapnyLogo']:'').'">
                        </div>';
		$outRegForm .= '<div class="input-group clearfix '.$error_desc.'">

								<label class="left" for="companyDesc">
									Описание компании: <sup class="red">*</sup>
								</label>
								<div class="left-tip-b">
									Подсказка про то, как правильно написать описание компании, что там пишут, для чего это, почему важно написать развёрнуто и грамотно.
								</div>
								<textarea class="required w-440" id="companyDesc" maxlength="800" name="desc">'.@$_POST['desc'].'</textarea>
								'.$error_desc_info.'
								<div class="middle-tip-b">
									Максимум 800 символов. <span id="companyDescCount" class="black hide">Осталось символов: <span>400</span>.</span>
								</div>

							</div>
							';
		$outRegForm .= '</div>';

		$outRegForm .= '<div class="step-b">';
		$outRegForm .= '<h3><i class="step-number">3</i>Контактная инфомация:</h3>';
		$outRegForm .= '<div class="input-group clearfix '.$error_contactname.'">
								<label class="left" for="registerContactPerson">
									Имя контактного лица: <sup class="red">*</sup>
									<i class="icon-question-sign icon-yellow helper-popup"></i>
								</label>
								<input class="required w-440" type="text" id="registerContactPerson" name="contactname" value="'.@$_POST['contactname'].'"/>
								'.$error_contactname_info.'
							</div>
							';
		$outRegForm .= '<div class="input-group clearfix '.$error_position.'">
								<label class="left" for="registerContactPerson">
									Должность: <sup class="red">*</sup>
									<i class="icon-question-sign icon-yellow helper-popup"></i>
								</label>
								<input class="required w-440" type="text" id="registerContactPerson" name="position" value="'.@$_POST['position'].'"/>
								'.$error_position_info.'
							</div>
							';
		$outRegForm .= '<div class="input-group clearfix '.$error_region.'">
								<label class="left" for="registerRegion">
									Регион: <sup class="red">*</sup>
									<i class="icon-question-sign icon-yellow helper-popup"></i>
								</label>
								<input class="required w-440 typeaheaded" autocomplete="off" data-typeaheaded="region" type="text" id="registerRegion" name="region" value="'.@$_POST['region'].'"/>
								'.$error_region_info.'
								<div class="middle-tip-b">
									Начните вводить название города, затем выберите его из списка.
								</div>
							</div>
							';
		$outRegForm .= '<div class="input-group clearfix '.$error_address.'">
								<label class="left" for="registerAddress">
									Адрес: <sup class="red">*</sup>
									<i class="icon-question-sign icon-yellow helper-popup"></i>
								</label>
								<input class="required w-440" type="text" id="registerAddress" name="address" value="'.@$_POST['address'].'"/>
								'.$error_address_info.'
							</div>
							';
	if (empty($_POST['f']['phone'][1])){
	$outRegForm .= '<div class="phones-input-group">
                                    <div class="input-group clearfix ">
                                        <label class="left" for="registerPhone1">
                                            Телефоны: <sup class="red">*</sup>
                                            <i class="icon-question-sign icon-yellow helper-popup"></i>
                                        </label>
                                        <input class="w-160 required phoneInput" type="text" id="registerPhone1" name="f[phone][0]" value="'.@$_POST['f']['phone'][0].'">
                                        <button class="btn  btn-smaller ml-10 addPhoneInput" type="button"><i class="icon-plus-sign"></i> Добавить телефон </button>
                                        <div class="middle-tip-b"> Рекомендуем писать телефонные номера в формате: +7 777 123-45-67</div>
                                    </div>
                                </div>';
    }else{
	$outRegForm .= '<div class="phones-input-group handled">
                        <div class="input-group clearfix ">
                            <label class="left" for="registerPhone1">
                                Телефоны: <sup class="red">*</sup>
                                <i class="icon-question-sign icon-yellow helper-popup"></i>
                            </label>
                            <input class="w-160 required phoneInput" type="text" id="registerPhone1" name="f[phone][0]" value="'.@$_POST['f']['phone'][0].'">
                            <button class="btn btn-white btn-smaller ml-10 removeFirstPhoneInput" type="button"><i class="icon-remove"></i></button>
                        </div>';
   
    foreach ($_POST['f']['phone'] as $k=>$phone){
    	if ($k == 0) continue;
    	if ($k == (count($_POST['f']['phone']) - 1)){
    		$outRegForm .= '
    			<div class="input-group clearfix ml-220">
    				 <input class="w-160 required phoneInput" type="text" id="registerPhone1" name="f[phone]['.$k.']" value="'.@$_POST['f']['phone'][$k].'">
                     <button class="btn btn-white btn-smaller ml-10 removeFirstPhoneInput" type="button"><i class="icon-remove"></i></button>
                     <button class="btn  btn-smaller ml-10 addPhoneInput" type="button"><i class="icon-plus-sign"></i> Добавить телефон </button>
                     <div class="middle-tip-b"> Рекомендуем писать телефонные номера в формате: +7 777 123-45-67</div>
    			</div>
    		';
    	}else{
    		$outRegForm .= '
    			<div class="input-group clearfix ml-220">
    				 <input class="w-160 required phoneInput" type="text" name="f[phone]['.$k.']" value="'.@$_POST['f']['phone'][$k].'">
                     <button class="btn btn-white btn-smaller ml-10 removeFirstPhoneInput" type="button"><i class="icon-remove"></i></button>
    			</div>
    		';
    	}
    }
    $outRegForm .= '</div>';
    }

		$outRegForm .= '<div class="input-group clearfix '.$error_site.'">
								<label class="left" for="registerSite">
									Сайт компании:
									<i class="icon-question-sign icon-yellow helper-popup"></i>
								</label>
								<input class="w-440" type="text" id="registerSite" name="site" value="'.@$_POST['site'].'"/>
								'.$error_site_info.'
							</div>
							';

		$outRegForm .= '<div class="input-group mb-40 clearfix '.$error_captcha.'">
								<label class="left" for="registerCaptcha">
									Проверочный код: <sup class="red">*</sup>
									<i class="icon-question-sign icon-yellow helper-popup"></i>
								</label>
								<img src="'._FILES_.'/appends/kcaptcha/" alt="" class="captcha-image" />
								<div class="captcha-input-wrapper">
									<input class="required w-160" type="text" id="registerCaptcha" name="captcha"/>
									'.$error_captcha_info.'
									<a href="#" onclick="return newCaptcha( $(\'.captcha-image\') )" class="pseudo dashed grey"><i class="icon-refresh icon-grey"></i> <span>Обновить картинку</span></a>
								</div>
							</div>';
		$outRegForm .= '<div class="submit-group '.$error_agree.'">
								<input class="required" type="checkbox" class="required" id="registerAgree" '.(@$_POST['agree']=='1'?'checked':'').' value="1" name="agree"/> <label class="left" for="registerAgree">Я согласен и принимаю условия <a '.$api->getLink(205).' target="blank">Пользовательского соглашения</a></label>
								'.$error_agree_info.'
								<button type="submit" class="btn btn-grey btn-grand">Зарегистрировать компанию</button>
								<input type="hidden" value="1" name="company">
								<div class="fz-12 grey">Компания пройдёт обязательную проверку модератором.</div>
							</div>
							';
		$outRegForm.= '</div>';
		$outRegForm .= '</form>';
		echo $outRegForm;

		# ГРУЗИМ РЕГИОНЫ
		if(!!$a=$api->objects->getFullObjectsList(257)){
			$out = array();
			foreach($a as $b){
				$out[] = "'".$b['Название']."'";
			}
			// $outRegForm .= "<script>var regions = [".join(',',$out)."]; </script>";
		}else{
			// $outRegForm .= "<script>var regions = ['Алматы', 'Астана']; </script>";
		}

}elseif (($action == 'confirm') && (!empty($_GET['h'])) && ($hash = $api->db->prepare($_GET['h']))){

	#Если пользователь
	if (!!$line = $api->db->select("`class_14`", "WHERE `field_45` = '$hash' LIMIT 1")){
		$api->db->update("class_14", array("field_45"=>''), "WHERE `id`='".$line['id']."'");
		$api->db->update("objects", array("active"=>'1'), "WHERE `id`='".$line['object_id']."'");
		echo '
			<p class="ok">Регистрация прошла успешно! Теперь вы можете <a href="/login/">авторизоваться</a> в системе.</p>
		';
	}
	#Если компания 
	elseif (!!$line = $api->db->select("`class_33`", "WHERE `field_147` = '$hash' LIMIT 1")){
		$api->db->update("class_33", array("field_147"=>''), "WHERE `id`='".$line['id']."'");
		$api->db->update("objects", array("active"=>'1'), "WHERE `id`='".$line['object_id']."'");
		echo '
			<p class="ok">Регистрация прошла успешно! Теперь вы можете <a href="/login/">авторизоваться</a> в системе.</p>
		';
	}
	else echo $e404;

}elseif (($action == 'changepass') && (!empty($_GET['h'])) && ($hash = $api->db->prepare($_GET['h']))){

	if(!!$line = $api->db->select("class_14", "WHERE `field_45`='".$api->db->prepare($_GET['h'])."' LIMIT 1") or !!$line = $api->db->select("class_33", "WHERE `field_147`='".$api->db->prepare($_GET['h'])."' LIMIT 1") ){
		$object = $api->objects->getFullObject($line['object_id']);
		$pass = $api->genPass();
		$hash_field_id = ($object['class_id'] == 14) ? 45 : 147;
		$pass_field_id = ($object['class_id'] == 14) ? 41 : 125;
		$api->db->update("class_".$object['class_id'], array("field_".$hash_field_id=>'', "field_".$pass_field_id=>sha1($pass)), "WHERE `object_id`='".$object['id']."'");
		echo '<p class="ok">Пароль успешно изменен. Новый пароль:</p>'.$pass;

		$api->mail->from = 'info@'.str_replace('www.','', $_SERVER['HTTP_HOST']);
		$api->mail->headers = 'X-Mailer: PHP/' . phpversion();
		$api->mail->subject = 'Новый пароль на сайте'.str_replace('www.','', $_SERVER['HTTP_HOST']);
		$api->mail->body = 'Пароль успешно изменен. Новый пароль:<br /><strong>'.$pass.'</strong>';

		$api->mail->send($object['Email']);
	}else{
		echo $e404;
	}

}else{
	echo $e404;
}

#--------------           THE END            -------------#


?>
<?$api->footer();?>
