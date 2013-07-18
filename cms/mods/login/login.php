<?



include_once 'cms/public/api.php'; 

$api->header = '/general/header.html';
$api->template = '/login.html';
$api->footer = '/general/footer.html';

# ЛОВИМ GET-ы
$action = @$_GET['action'];


# ПОДКЛЮЧАЕМ JS
$api->scripts = array(
	'<script src="'._WWW_.'/js/login.js"></script>'
);

# ПОДКЛЮЧАЕМ СТИЛИ
//$api->styles = array(
//	'<link rel="stylesheet" href="'._WWW_.'/css/register.css" type="text/css" >'
//);

# ОСНОВНОЙ КЛАСС
include_once _MODS_ABS_.'/login/login_class.php';
$login = new Login();

# ДОПОЛНИТЕЛЬНЫЕ КЛАССЫ



# ВЫХОД ИЗ КАБИНЕТА
if(isset($action) && $action=='out'){
	unset($_SESSION['u']);
	$curpage = $api->curPageURL();
	if(strpos($curpage, 'login')){
		header('location: '._HTTP_HOST_.'/');
	}else{
		header('location: "'.$curpage.'"');
	}
	
	exit;
}


# Если зареген, то перенаправляем в кабинет
if($api->logged){
	//print_r($_SESSION['u']);
	header("location: "._HTTP_HOST_."/cabinet/ ");	
	exit;
}


# if AJAX
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	
	# ВХОД
	if(isset($_POST['password'])){
		$error = '';
		if($result = $login->goLogin()){
			# ошибка
			switch($result){
				case 'ok':				
					$arr = array('success' => 1,);
					echo json_encode($arr);
					exit;				
				break;
				case 'emptyLogin':
					$arr = array('error' => 1, 'msg'=>'emptyLogin');
					echo json_encode($arr);
					exit;		
				break;
				case 'emptyPass':
					$arr = array('error' => 1, 'msg'=>'emptyPass');
					echo json_encode($arr);
					exit;					
				break;
				case 'wrongCredentials':
					$arr = array('error' => 1, 'msg'=>'wrongCredentials');
					echo json_encode($arr);
					exit;		
				break;	
				case 'wrongFormatEmail':
					$arr = array('error' => 1, 'wrongFormatEmail');
					echo json_encode($arr);
					exit;	
				break;			
			}
		}	
	# ВОССТАНОВЛНИЕ	
	}else if(isset($_POST['email'])){
		$error = '';
		if($result = $login->restore()){
			# ошибка
			switch($result){
				case 'ok':				
					$arr = array('success' => 1);
					echo json_encode($arr);
					exit;				
				break;
				case 'emptyLogin':
					$arr = array('error' => 1);
					echo json_encode($arr);
					exit;		
				break;
				case 'emptyPass':
					$arr = array('error' => 2);
					echo json_encode($arr);
					exit;					
				break;
				case 'wrongCredentials':
					$arr = array('error' => 3);
					echo json_encode($arr);
					exit;		
				break;	
				case 'wrongFormatEmail':
					$arr = array('error' => 4);
					echo json_encode($arr);
					exit;	
				break;			
			}
		}	
	}
	$arr = array('error' => 5);
	echo json_encode($arr);
	exit;	
	exit('Ошибка');
}

# ЗАГОЛОВОК СТРАНИЦЫ
$api->header(array('page-title'=>'Авторизация'));






# ВХОД
if(isset($_POST['password'])){
	$error = '';
	if($result = $login->goLogin()){
		# ошибка		
		switch($result){
			case 'ok':				
				header("location: "._WWW_."/cabinet/ ");
			break;
			case 'emptyLogin':
			exit ('
									<button type="button" class="close" data-dismiss="alert">×</button>
									<strong>Укажите адрес электронной почты</strong><br />									
								');		
			break;
			case 'emptyPass':
			exit ('
									<button type="button" class="close" data-dismiss="alert">×</button>
									<strong>Укажите пароль</strong><br />									
								');					
			break;
			case 'wrongCredentials':
			exit ('
									<button type="button" class="close" data-dismiss="alert">×</button>
									<strong>Пользователь с такими данными не зарегистрирован.</strong><br />
									Проверьте раскладку клавиатуры, не нажата ли клавиша «Caps Lock» и попробуйте ввести логин и пароль еще раз.
								');		
			break;			
		}
	}	
	
# ВОССТАНОВЛНИЕ	
}else if(isset($_POST['restore'])){
	
}


echo '<div id="page-auth" class="tab-content">';
echo $login->getLoginForm();
echo $login->getRestoreForm();
echo '</div>';

echo $login->getRegInfoBlock();


$api->footer();
?>