<?

	// $name="admin";
	// $pass="promzona2012";
	// $url_site=$_SERVER['HTTP_HOST'];
	// if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER']!=$name || $_SERVER['PHP_AUTH_PW']!=$pass)
	// {
		// header("WWW-Authenticate: Basic realm=\"".$url_site."\"");
		// header("HTTP/1.0 401 Unauthorized");
		// exit("<b><a href=http://".$url_site.">".$url_site."</a> : Access Denied</b>");
	// }

// header('Location: /index.html');
/*
Title: РАСЧУДЕСНОЕ МНОГОЯЗЫКОВОЕ API-ШАБЛОНИЗАТОР С ДВОЙНОЙ БУФЕРИЗАЦИЕЙ ВЫВОДА, МОДУЛЬ-САЙД ИНКЛЮДАМИ И ВСПОМОГАТЕЛЬНЫМИ ЮЗЕР-ФУНКЦИЯМИ
Author: Derevyanko Mikhail <m-derevyanko@ya.ru>
Last UpDate: 21.05.2010
*/
error_reporting(E_ALL);
ini_set("display_errors", "On");
session_start();
include(str_replace("\\", "/", dirname(__FILE__)).'/../cfg.php');
include_once(_FILES_ABS_."/mysql.php");
include_once(_FILES_ABS_."/mail.php");
include_once(_FILES_ABS_."/appends.php");
include_once(_PUBLIC_ABS_."/objects.php");
include_once(_PUBLIC_ABS_."/strings.php");
//include_once(_PUBLIC_ABS_."/form.php");

class api extends appends{

public $modname;
public $pageid;

public $header;
public $template;
public $footer;

public $objects;
public $strings;
public $mail;
public $body;
public $arguments;
public $lang;
public $languages;

public $scripts;
public $styles;

public $logged;

	  function __construct(){
		  parent::__construct();
		   //$this->footer = '/pages.html';
		  //$this->header = '/pages.html';
		  $this->template = '/pages.html';

		  $this->body = null;
		  $this->arguments = array();
		  $this->languages = array(
			  "ru"=>"Рус.",
			  // "en"=>"English",
			  "kz"=>"Каз."
		  );
		  $this->lang = 'ru';

		  $this->objects = new objects( $this->lang );
		  $this->strings = new Strings( $this->lang );
		  $this->mail = new mime_mail();

		   $this->scripts = array();
			$this->styles = array();
			
			
		# ПОЛУЧАЕМ ЮЗЕРА ЕСЛИ ОН ВОШЕЛ
		//die('----'.@$_SESSION['test']);
		if(!empty($_SESSION['u']['role']) && !empty($_SESSION['u']['email'])){		
			$this->logged = true;
		}else{$this->logged = false;}
		
			
		
	  }

	  function arg($name, $value){
		  $this->arguments[$name] = $value;
	  }

	  function args($arr){
		  if($arr) $this->arguments = array_merge($this->arguments, $arr);
	  }

	  function flush($buffer){
		  $this->content = explode("#CONTENT#", $buffer);
		  $this->content = $this->content[0].$this->body.$this->content[1];
		  #INIT HEAD
		  $this->content = str_replace('<head>', "<head>\n".$this->initHead(), $this->content);
		  $this->content = str_replace('</body>', $this->initFoot()."</body>", $this->content);

		  $this->run();
		  $temp = array();
		  foreach($this->arguments as $name => $value){
			  $temp['<!--#'.$name.'#-->'] = $value;
		  }
		  $this->content = strtr($this->content, $temp);

		  #INIT ALL TYPES OF INSIDE OBJECTS
		  $this->content = $this->convertSmartObjects($this->content);
		  $this->content = $this->convertSimpleObjects($this->content);
		  $this->content = $this->convertSimpleTransObjects($this->content);
		  return $this->content;
	  }

	  function convertSmartObjects($buffer){
		  if(!!$this->auth()) return preg_replace_callback("/<!--\s*smart:(.*)\s*-->/sU", array($this, 'activateSmartObject'), $buffer);
		  return preg_replace("/<!--\s*smart:(.*)\s*-->/sU", '', $buffer);
	  }

	  function activateSmartObject($ok){
		  $id = uniqid();
		  $out = array( '<div id="div-'.$id.'"></div><div style="clear:both"></div>' );
		  $out[]= $this->areaJS("fe.add( $('#div-".$id."'), ".str_replace("\n", "", $ok[1])." );");
		  return join("\n", $out);
	  }

	  function convertSimpleObjects($buffer){
		  return preg_replace_callback("/<!--\s*object:(.*)\s*-->/sU", array($this, 'activateSimpleObject'), $buffer);
	  }

	  function activateSimpleObject($ok){
		  if(@!preg_match("/^\[(\d+)\]\[([^\]]+)\]$/", $ok[1], $p) || !($o = $this->objects->getFullObject($p[1], false)) || empty($o[$p[2]])) return '';
		  return $o[$p[2]];
	  }

	  function convertSimpleTransObjects($buffer){
		  return preg_replace_callback("/<!--\s*o:(.*)\s*-->/sU", array($this, 'activateSimpleTransObject'), $buffer);
	  }

	  function activateSimpleTransObject($ok){
		  if(@!preg_match("/^(\d+)$/", $ok[1], $p) || !($o = $this->objects->getFullObject($p[1], false)) || empty($o[18])) return '';
		  return $o[18];
	  }

	  function header($args=array()){
		  $this->args($args);
		  ob_start(array($this, 'flush'));
		  include(_MODS_ABS_.$this->header);
		  include(_MODS_ABS_.'/'.$this->modname.$this->template);
		  include(_MODS_ABS_.$this->footer);

		  ob_start();
		  return true;
	  }

	  function footer(){
		  $this->body = ob_get_contents();
		  ob_end_clean();
	  }


	  function showError($id,$type='print'){
		  // ПЕРЕЧЕНЬ ОБЩИХ ОШИБОК
		  $errs = array(
			1 => 'Вы указали неверный URL адрес. Такой страницы не существует',
			2 => '',
			3 => '',
			4 => '',
			5 => '',
		  );

		  // print
		  if($type=='print'){
			echo $errs[$id];
		  //
		  }else if($id=='return'){

		  }else{

		  }
	  }

	  function v($in_text, $lang = '', $city = ''){
		  if ($lang == '') $lang = $this->lang;

		  $this->vars = array(
			  'ru' => array(
				  'Главная' => 'Главная',
				  'Новости' => 'Новости',
				  'все новости' => 'все новости',
				  'Карта сайта' => 'Карта сайта',
				  'Вернуться в каталог' => 'Вернуться в каталог',
				  'Назад' => 'Назад',
				  'Вернуться на уровень выше' => 'Вернуться на уровень выше',
				  'В перёд' => 'В перёд',
				  'Войти' => 'Войти',
				  'Регистрация' => 'Регистрация',
				  'Выйти' => 'Выйти',
				  'Авторизация пользователя' => 'Авторизация пользователя',
				  'Пароль' => 'Пароль',
				  'Забыли пароль?' => 'Забыли пароль?',
				  'тг.' => 'тг.',
				  'Товаров' => 'Товаров',
				  'На сумму' => 'На сумму',
				  'Подробнее' => 'Подробнее',
				  'создание сайта' => 'создание сайта',
				  'Не верный логин или пароль!' => 'Не верный логин или пароль!',
				  'Проголосовали' => 'Проголосовали',
				  'Голосовать' => 'Голосовать',
				  'Посмотреть все результаты' => 'Посмотреть все результаты',
				  'Назад к голосованию' => 'Назад к голосованию',
				  'Цена' => 'Цена',
				  'тг' => 'тг',
				  'в корзину' => 'в корзину',
				  'Найти' => 'Найти',
				  'Отмена' => 'Отмена',
				  'Товары не найдены' => 'Товары не найдены',
				  'Товар успешно добавлен в корзину.' => 'Товар успешно добавлен в <a href="'.$city.'/'.$lang.'/rycle/">корзину</a>.',
			  ),
			  'en' => array(
				  'Главная' => 'Main',
				  'Новости' => 'News',
				  'все новости' => 'all news',
				  'Карта сайта' => 'Site map',
				  'Вернуться в каталог' => 'Back to catalog',
				  'Назад' => 'Back',
				  'Вернуться на уровень выше' => 'Back to previous level',
				  'В перёд' => 'Next',
				  'Войти' => 'Signin',
				  'Регистрация' => 'Register',
				  'Выйти' => 'Logout',
				  'Авторизация пользователя' => 'Authorization',
				  'Пароль' => 'Password',
				  'Забыли пароль?' => 'Forgot?',
				  'тг.' => 'tg.',
				  'Товаров' => 'Goods',
				  'На сумму' => 'Summ',
				  'Подробнее' => 'More',
				  'создание сайта' => 'designed',
				  'Не верный логин или пароль!' => 'Wrong login or password!',
				  'Проголосовали' => 'Voted',
				  'Голосовать' => 'Vote',
				  'Посмотреть все результаты' => 'View all results',
				  'Назад к голосованию' => 'Back to voting',
				  'Цена' => 'Price',
				  'тг' => 'tg',
				  'в корзину' => 'to cart',
				  'Найти' => 'Search',
				  'Отмена' => 'Reset',
				  'Товары не найдены' => 'Products not found',
				  'Товар успешно добавлен в корзину.' => 'Goods at the <a href="'.$city.'/'.$lang.'/rycle/">basket</a>.',
			  ),
			  'kz' => array(
				  'Главная' => 'Басқы бет',
				  'Новости' => 'Жаңалықтар',
				  'все новости' => 'барлық жаңалықтар',
				  'Карта сайта' => 'Сайт картасы',
				  'Вернуться в каталог' => 'Каталогқа оралу',
				  'Назад' => 'Ілгері',
				  'Вернуться на уровень выше' => 'Ілгері',
				  'В перёд' => 'Алға',
				  'Войти' => 'Кіру',
				  'Регистрация' => 'Тіркелу',
				  'Выйти' => 'Шығу',
				  'Авторизация пользователя' => 'Сайтқа кіру',
				  'Пароль' => 'Пароль',
				  'Забыли пароль?' => 'Пароліңізді ұмытып қалдыңыз ба?',
				  'тг.' => 'тг.',
				  'Товаров' => 'Тауарлар',
				  'На сумму' => 'Бағасы',
				  'создание сайта' => 'жасаған',
				  'Не верный логин или пароль!' => 'Логин немесе пароліңіз қате!',
				  'Проголосовали' => 'Дауыс бергендер',
				  'Голосовать' => 'Дауыс беру',
				  'Посмотреть все результаты' => 'Жауаптарды көру',
				  'Назад к голосованию' => 'Дауыс беруге қайта оралу',
				  'Цена' => 'Бағасы',
				  'тг' => 'тг',
				  'в корзину' => 'қоржынға салу',
				  'Найти' => 'Iздеу',
				  'Отмена' => 'Тазарту',
				  'Товары не найдены' => 'Тауарлар табылған жоқ',
				  'Товар успешно добавлен в корзину.' => 'Тауарлар <a href="'.$city.'/'.$lang.'/rycle/">қоржында</a>.',
			  ),
		  );

		  if ($lang != '') $lang = $lang; else $lang = $this->lang;

		  return $this->vars[$lang][$in_text];
	  }

	  #ЭТА НИФИГОВАЯ ФУНКЦИЯ ДЕЛАЕТ ИНКЛЮДЫ СТИЛЕЙ И ЖОВОСКРИПТА В САМЫЙ ВЕРХ
	  function initHead(){
	   $files = array(
		'<!------- Meta block ------->',
			'<meta name="viewport" content="width=device-width, initial-scale=1.0">',
			'<meta http-equiv="content-type" content="text/html; charset=utf-8" />',
			'<title><!--#page-title#--> &mdash; <!--object:[5][18]--></title>',
			'<meta name="author" content="go-web.kz">',
			'<meta name="copyright" content="go-web.kz">',
			'<meta name="keywords" content="<!--object:[6][18]-->">',
			'<meta name="description" content="<!--object:[7][18]-->">',
			'<meta name="Publisher-Email" content="info@go-web.kz">',
			'<meta name="Publisher-URL" content="http://go-web.kz/">',
			'<meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />',

		'<!------- html5shiv ------->',
			'<!--[if lt IE 9]><link href="/css/ie.css" rel="stylesheet" /><![endif]-->',
			'<!--[if lt IE 9]><script src="/js/libs/html5shiv.min.js"></script><![endif]-->',

		'<!------- jQuery UI ------->',
	  // '<link href="/js/libs/css/smoothness/jquery-ui-1.9.0.custom.min.css" rel="stylesheet" />',

	'<!------- Bootstrap ------->',
			'<link href="/css/bootstrap.min.css" rel="stylesheet" />',

		'<!------- Icons ------->',
			'<link rel="icon" href="/favicon.ico" type="image/x-icon" />',
			'<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />',

		'<!------- CSS ------->',
			'<link href="/css/common.css" rel="stylesheet" />'
	   );

	   $out = array();
	   $out[]=$this->areaJS('var _LANG_ = "'.$this->lang.'", ajaxFile = "'._AJAX_FILE_.'";');
	   $out[]=$this->areaJS('var _NOWORD_ = "<!--o:40-->", _TOOLONG_ = "<!--o:41-->", _SEARCHWORD_ = "<!--o:39-->";');
	   foreach($files as $file){
		$out[]=$file;
	   }
	   $out[]=$this->areaJS('var _ROOT_ = "'._ROOT_.'", _UPLOADS_ = "'._UPLOADS_.'", _WWW_ = "'._WWW_.'", _FILES_ = "'._FILES_.'", ajaxFeFile = "'._WWW_.'/frontEnd/ajax.php";');

   if (is_array($this->styles)){
	$out[] = join("\n",$this->styles);
   }

	   return join("\n", $out);
	  }


	   #ЭТА НИФИГОВАЯ ФУНКЦИЯ ДЕЛАЕТ ИНКЛЮДЫ СТИЛЕЙ И ЖОВОСКРИПТА В САМЫЙ НИЗ анологично InitHead
   # scripts - дополнительные js и css файлы от модуля
   function initFoot(){

  $files = array(
	'<!------- jQuery ------->',
	'<!--<script src="ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>-->',
	'<script src="/js/libs/jquery-1.8.3.min.js"></script>',
	'<script src="/js/libs/json2.min.js"></script>',

	'<!------- Bootstrap ------->',
	'<script src="/js/libs/bootstrap.min.js"></script>',

	'<!------- Utilities ------->',
	'<script src="/js/libs/jquery.easing.1.3.min.js"></script>',
	'<script src="/js/libs/jquery.mousewheel.min.js"></script>',
	'<script src="/js/libs/jquery.fitvids.js"></script>',

	'<!------- Plugins ------->',
	'<script src="/js/libs/jquery.fineuploader-3.0.min.js"></script>',
	'<script src="/js/libs/jquery.tinyscrollbar.min.js"></script>',
	'<script src="/js/libs/jquery.bxSlider.min.js"></script>',

	'<!------- JS ------->',
	'<script src="/js/common.js"></script>',
	'<script src="/js/flash_detect.js"></script>'

  );
  $out = array();
  foreach($files as $file){
		$out[]=$file;
	   }
  if (is_array($this->scripts)){
   $out[] = join("\n",$this->scripts);
  }
  //if(!$this->auth()) return join("\n", $out);
	   //$files = array(
		//'<script type="text/javascript" src="'._FILES_.'/appends/ckfinder/ckfinder.js"></script>',
		//'<script type="text/javascript" src="'._WWW_.'/frontEnd/js.js"></script>',
		//'<link href="'._WWW_.'/frontEnd/css.css" rel="stylesheet" type="text/css" />'
	   //);
  //foreach($files as $file){
   //$out[]=$file;
  //}


  return join("\n", $out);
   }


	  #USE IT FOR ACTIONS BY DEFAULT;
	  function run(){
		  $this->arg('auth-block', $this->authBlock());
		  $this->arg('menu', $this->getMenu(4));
		  $this->arg('left-menu', $this->leftMenu(171,false));
		  $this->arg('menu-footer1', $this->getMenuFooter(200, false,false,"w-220"));
		  $this->arg('menu-footer2', $this->getMenuFooter(201, false,false,"w-220"));
		  $this->arg('menu-footer3', $this->getMenuFooter(202, false,false,"w-160"));
		  $this->arg('menu-footer4', $this->getMenuFooter(203, false,false,"w-120"));
		  $this->arg('menu-footer5', $this->getMenuFooter(204, false,false,"w-160"));

		  $this->arg('news-list', $this->newsList(9, 8));
//		  $this->arg('banners-list-1', $this->bannersList(2, 6, 7));
		  $this->arg('banners-list-1', $this->getTopBanner());
		  $this->arg('banners-list-2', $this->bannersList(22, 6, 7));
		  $this->arg('voting-block', $this->votingBlock(12));
		  $this->arg('langs', $this->getLangs());
		  $this->arg('search', $this->search());
	  }
################
#USER FUNCTIONS#
################

function getMothers($id){
	  if (is_numeric($id) && ($id != 0) && ($o = $this->objects->getObject($id, false)) && (($o['class_id'] == 1) || ($o['class_id'] == 3) || ($o['class_id'] == 19) || ($o['class_id'] == 20) || ($o['class_id'] == 21) || ($o['class_id'] == 2))){
		  if ($o['class_id'] != 20){
			  $this->mothers[] = $o['id'];
		  }
		  return $this->getMothers($o['head']);
	  }
}

function bread($separator = '&nbsp;<span>/</span>&nbsp;'){
	  if (@$_GET['id'])
		  $id = $_GET['id'];
	  elseif (@$_GET['cat'])
		  $id = $_GET['cat'];
	  else $id = @$_GET['bread'];
	  $out = array();
	  if ((!empty($id)) && (is_numeric($id))){
		  if(($obj = $this->objects->getFullObject($id)) || (($obj['class_id'] == 1) && ($obj['class_id'] == 3))){
			  # ВЛОЖЕННОСТЬ
			  $this->mothers = array();

			  $this->getMothers($obj['head']);
			  $this->mothers = array_reverse($this->mothers);
			  $out[] = '<a href="/'.$this->lang.'/"><!--o:131--></a>';

			  if (($obj['class_id'] == 8)){
				  $out[] = '<a href="/'.$this->lang.'/news/"><!--o:132--></a>';
			  }

			  if (($obj['class_id'] == 24)){
				  $out[] = '<a href="/'.$this->lang.'/articles/"><!--o:124--></a>';
			  }

			  if (($obj['class_id'] == 19) || ($obj['class_id'] == 15)){
				  $out[] = '<a href="/'.$this->lang.'/catalog/"><!--o:126--></a>';
			  }
			  if($id != 131){ #ЕСЛИ НЕ ГЛАВНАЯ
				  # ХЛЕБНЫЕ КРОШКИ
				  if (sizeof($this->mothers) > 0){
					  foreach($this->mothers as $obj_id){
						  if (is_numeric($obj_id) && ($path_obj = $this->objects->getFullObject($obj_id, false))) $out[] = '<a '.$this->getLink($path_obj['id']).'>'.(($path_obj['Название'])?$path_obj['Название']:$path_obj['name']).'</a>';
					  }
				  }

				  $out[] = ((@$obj['Название'])?$obj['Название']:$obj['Значение']);
			  }
		  }
	  }
	  return join($separator, $out);
}

function array_random_k($input_mass, $flag_asKey_or_asValue = "asvalue"){
	  $mass = array();
	  $return_array = array();
	  $k = 0;
	  $count = count($input_mass);
	  while($k != $count){
		  $rand_key = mt_rand(0, $count - 1);
		  if (!in_array($rand_key, $mass)){
			  $mass[] = $rand_key;
			  $k++;
		  }
	  }
	  if (strtolower($flag_asKey_or_asValue) == 'askey'){
		  return $mass;
	  } elseif (strtolower($flag_asKey_or_asValue) == 'asvalue'){
		  foreach ($mass as $v){
				  $return_array[] = $input_mass[$v];
		  }
		  return $return_array;
	  }
}

#Первая буква заглавная
function firstUpper($text){
	  $first = mb_substr(trim($text),0,1, 'UTF-8');//первая буква
	  $last = mb_substr(trim($text),1);//все кроме первой буквы
	  $first = mb_strtoupper($first, 'UTF-8');
	  $last = mb_strtolower($last, 'UTF-8');

	  return $first.$last;
}

/* example: $api->substrword($o['Анонс'], 14); */
/* this code writes in api.php file */
function substrword($str = '', $c = 50){
	  if ($mass = explode(" ", $str)){
		  if (count($mass) > $c){
			  $str = '';
			  for($i = 0; $i < $c; $i++){
				  $str .= $mass[$i]." ";
			  }
			  $str = $str."...";
		  }
	  }
	  return strip_tags($str, '<p><br><br />');
}

function substrstr($str = '', $c = 50, $addstr = '...'){
	  if (mb_strlen($str, "UTF-8") > $c){
		  return mb_substr($str, 0, $c, "UTF-8").$addstr;
	  }
	  return $str;
}



#ФОРМА ПОИСКА
function search(){
	   $out=array('<form method="post" action="/search/" onsubmit="">
			<div class="input-append">
			  <input id="" class="input-grand" name="what" type="text" placeholder="Поиск по объявлениям..."><button class="btn btn-grand" type="submit">Найти</button>
			</div>
		  </form>');

	  return join("\n",$out);
}



# ЯЗЫКИ
function getLangs(){
	  $out = array();
	  $i=0;
	  foreach($this->languages as $kk=>$vv){
		  if ($this->lang == $kk){
			  # ВЫБРАН
			  $out[] = '<a href="#" class="active">'.$vv.'</a>'.((++$i == 1)?' / ':'');
		  } else {
			  $out[] = '<a href="/'.$kk.'/">'.$vv.'</a>'.((++$i == 1)?' / ':'');
		  }
	  }
	  return join("", $out);
}

	  # PASSWORD GENERATION
	  function genPass(){
		  $out = array();
		  $symbols = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
		  for($i=1; $i<=8; $i++){
			  $out[]=$symbols[rand(0, count($symbols)-1)];
		  }
		  return join("", $out);
	  }

	  # AUTH USER BLOCK
	  function authBlock(){
		  $display = array( 'block', 'none' );
		  if(!empty($_SESSION['auth']['u']) && is_array($u = $_SESSION['auth']['u'])){
			  $display = array( 'none', 'block' );
			  $auth_html = array(
				  '<div><!--o:33-->, <strong>'.$u['name'].'</strong>!</div><br>',
				  '<div><a href="/'.$this->lang.'/edit/"><!--o:34--></a></div>',
				  '<div><a href="#выход" onclick="return exit()"><!--o:35--></a></div>'
			  );
		  }
		  $html = array(
			  '<h2><!--o:27--></h2>&nbsp;&nbsp;<a href="/'.$this->lang.'/register/"><!--o:28--></a>',
			  '<div id="auth-form" style="display:'.$display[0].'">',
				  '<div class="wihte_text top12"><!--o:29--></div>',
				  '<input id="input-login" type="text" value="" class="input_text" />',
				  '<div class="wihte_text"><!--o:30--></div>',
				  '<div><input id="input-pass" type="password" value="" class="input_text" /></div>',
				  '<div><input id="auth-button" type="button" value="<!--o:31-->" class="but" />&nbsp;&nbsp;<a href="/forgot.php?lang='.$this->lang.'"><!--o:32--></a></div>',
			  '</div>',
			  '<div id="auth-block" style="display:'.$display[1].'">',
			  join("\n", $auth_html),
			  '</div>'
		  );
		  return join("\n", $html);
	  }

###ZOTTIG (c)
	  #НОВОЕ МЕНЮ
	  function selected($id,$last){
	 $o=$this->objects->getFullObject($id);
	 if (($_GET['id']==$id) || ($last==$id) || ($_GET['id']==$o['id']) || ($_GET['cat']==$o['id']))
		  return true;
	  else
		  if($o['class_id']==2){
		  if (!empty($_GET['mod']) && ($_GET['mod'] == 'ads') && (!empty($_GET['action'])) && is_numeric($ad_id = $_GET['action']) && ($obj = $this->objects->getFullObject($ad_id)) && $this->isAdsMenuType($o['id'],$obj['Тип'])) return true;
		  elseif ( !empty($_GET['type']) && ($o['Ссылка'] == '/') && ($_GET['type']=='buy')) return true;
		  elseif($_SERVER['REQUEST_URI']=='/'.$this->lang.$o['Ссылка'] || $_SERVER['SCRIPT_NAME']==$o['Ссылка'] ||  (strstr($_SERVER['REQUEST_URI'],$o['Ссылка']) AND $o['Ссылка']!='/') || ((empty($_GET)) AND ($o['Ссылка']=='/')))
			  return true;
		  else
			  return false;
		}
	  }

	  function t($id){
		  return $id.'-'.$this->objects->urlTranslitFormID($id);
	  }



	  function getLink($id){
		  $o=$this->objects->getFullObject($id);
		  $parent = $this->objects->getObject($o['head']);
		  if(($o['class_id']==2) || ($o['class_id']==25)){
			  if (!empty($o['Ссылка'])){
				  if(strstr($o['Ссылка'], '.php')){
					  return 'href="'.$o['Ссылка'].'?lang=" '.($o['В модальном окне'] == 1?'class="d_b fancy"':'').' '.(@$o['В новом окне']?' target="_blank"':'');
				  }elseif(strstr($o['Ссылка'], 'http://')) return 'href="'.$o['Ссылка'].'"'.(@$o['В новом окне']?' target="_blank"':'');
				  else return 'href="'.$o['Ссылка'].'"'.(@$o['В новом окне']?' target="_blank"':'');
			  } else return 'href="" onclick="return false;" style="cursor: default;"';
		  }elseif($o['class_id']==5){
			  return 'href="'._UPLOADS_.'/'.$o['Ссылка'].'"';
		  }elseif($o['class_id']==8){
			  return 'href="/news/'.$this->t($o['id']).'/"';
		  }elseif($o['class_id']==24){
			  return 'href="/'.$this->t($o['id']).'/"';
		  }elseif($o['class_id']==19){
			  return 'href="/catalog/'.$this->t($o['id']).'/"';
		  }elseif($o['class_id']==15){
			  return 'href="/catalog/'.$this->t($parent['id']).'/'.$this->t($o['id']).'/"';
		  }elseif($o['class_id']==30){
			return 'href="/'.$_GET['mod'].'/'.$id.'/"';
		  }else{
			  return 'href="/page/'.$this->t($o['id']).'.html"';

		  }
	  }

	  function isHasCatalog($id){
		  if(!!$list = $this->objects->getFullObjectsListByClass($id, 19)){
			  return true;
		  }
		  return false;
	  }

   # МЕНЮ
   function getMenu($id, $hasSubMenu = true, $withIMG = false){
	  $out = array();
	  if(!!$list = $this->objects->getFullObjectsList($id)){
	  $last=$this->objects->last;
	  $out[]='<ul>';
	  foreach($list as $o){
		  if($this->selected($o['id'],$last))
		  $out[]='<li class="active">'.($withIMG?'<img alt="" src="'._UPLOADS_.'/'.$o['Изображение'].'">':'').'<a '.$this->getLink($o['id']).' '.(!empty($_REQUEST['bread'])?'onclick="return false;"':'').'>'.$o['Название'].'</a>'.($hasSubMenu?$this->getSubMenu($o['id']):'').'</li>';
		  else
		  $out[]='<li>'.($withIMG?'<img alt="" src="'._UPLOADS_.'/'.$o['Изображение'].'">':'').'<a '.$this->getLink($o['id']).'>'.$o['Название'].'</a>'.($hasSubMenu?$this->getSubMenu($o['id']):'').'</li>';
	  }
	  $out[]='</ul>';
	  }
	  // $smart = '<!--smart:{
	  // id : '.$id.',
	  // title : "меню",
	  // actions : ["list"],
	  // p : {
		 //  list : 1
	  // },
			//   css:{
			// 	  position: "absolute"
			//   }
	  // }-->';
	  return join("\n", $out);
  }

   # МЕНЮ
   function leftMenu($id, $hasSubMenu = true, $withIMG = false){
	  $out = array();
	  if(!!$list = $this->objects->getFullObjectsList($id)){
	  $last=$this->objects->last;
	  foreach($list as $o){
		  if($this->selected($o['id'],$last))
		  $out[]='<li class="active">'.($withIMG?'<img alt="" src="'._UPLOADS_.'/'.$o['Изображение'].'">':'').'<a '.$this->getLink($o['id']).' '.(!empty($_REQUEST['bread'])?'onclick="return false;"':'').'>'.$o['Название'].'</a>'.($hasSubMenu?$this->getSubMenu($o['id']):'').'</li>';
		  else
		  $out[]='<li>'.($withIMG?'<img alt="" src="'._UPLOADS_.'/'.$o['Изображение'].'">':'').'<a '.$this->getLink($o['id']).'>'.$o['Название'].'</a>'.($hasSubMenu?$this->getSubMenu($o['id']):'').'</li>';
	  }
	  }
	  return join("\n", $out);
  }


	# МЕНЮ ФУТЕРА
   function getMenuFooter($id, $hasSubMenu = true, $withIMG = false, $classname){
	  $out = array();
	  if(!!$list = $this->objects->getFullObjectsList($id)){
	  $last=$this->objects->last;
	  $out[]='<ul class="'.$classname.'">';
	  foreach($list as $o){
		  if($this->selected($o['id'],$last))
		  $out[]='<li class="active">'.($withIMG?'<img alt="" src="'._UPLOADS_.'/'.$o['Изображение'].'">':'').'<a '.$this->getLink($o['id']).' '.(!empty($_REQUEST['bread'])?'onclick="return false;"':'').'>'.$o['Название'].'</a>'.($hasSubMenu?$this->getSubMenu($o['id']):'').'</li>';
		  else
		  $out[]='<li>'.($withIMG?'<img alt="" src="'._UPLOADS_.'/'.$o['Изображение'].'">':'').'<a class="as-text bd-grey-outside" '.$this->getLink($o['id']).'>'.$o['Название'].'</a>'.($hasSubMenu?$this->getSubMenu($o['id']):'').'</li>';
	  }
	  $out[]='</ul>';
	  }
	  $smart = '<!--smart:{
	  id : '.$id.',
	  title : "меню",
	  actions : ["list"],
	  p : {
		  list : 1
	  },
			  css:{
				  position: "absolute"
			  }
	  }-->';
	  return join("\n", $out);
  }

	  function getSubMenu($id){
	  $out = array();
	  if(!!$list = $this->objects->getFullObjectsList($id)){
	  $last=$this->objects->last;
			  $i = 0;
			  $mass = array();
	  foreach($list as $o){
				  if (($o['class_id'] == 1) || ($o['class_id'] == 2)){
					  if($this->selected($o['id'],$last))
						  $mass[]='<li class="active"><a '.$this->getLink($o['id']).' onclick="return false;">'.$o['Название'].'</a></li>';
					  else
						  $mass[]='<li><a '.$this->getLink($o['id']).'>'.$o['Название'].'</a></li>';
				  }
	  }
			  if (count($mass) > 0)
				  $out[] = '<ul>'.join("\n", $mass).'</ul>';
	  }
		  return join("\n", $out);
	  }
###ZOTTIG (c)

	# ФУНКЦИЯ ГРАММОТНОЙ ОБРЕЗКИ СТРОК
	function maxsite_str_word($text, $counttext = 10, $sep = ' ') {
		$words = split($sep, $text);
		if ( count($words) > $counttext )
				$text = join($sep, array_slice($words, 0, $counttext));
		return $text;
	}

	  # НОВОСТНАЯ ЛЕНТА, ВЫВОД НА ГЛАВНОЙ ДВУХ НОВОСТЕЙ
	  function newsList($id, $cid)
	  {
		  if(!$list = $this->objects->getFullObjectsListByClass($id, $cid, "AND o.active=1 ORDER BY c.field_19 DESC LIMIT 2")) return '<!--o:41-->';
		  $html = array('<h3>'.$this->v('Новости').' <a href="/'.$this->lang.'/news/" class="in">'.$this->v('все новости').'</a></h3><div class="list_nwes">');
		  foreach($list as $o)
		  {
					  $txt = strip_tags($o['Анонс']);

			  $html[]='
				  <div class="news_box">
					  <span>'.$this->strings->date($o['Дата'], 'sql', 'textdateday').'</span>
					  <div>'.$this->maxsite_str_word($txt, 20, ' ').'</div>
					  <a '.$this->getLink($o['id']).'>подробнее...</a>
				  </div>
			  ';
		  }
		  $html[] = '</div>';
		  $smart = '
		  <!--smart:{
			  id : '.$id.',
			  title : "новостей",
			  actions : ["add"],
			  p : {
				  add : ['.$cid.']
			  }
		  }-->';
		  return join("\n", $html).$smart;
	  }
          
          function getTopBanner(){
              $smart_global = '';
              $html = '<div id="screenglider" style="border-bottom:1px solid #CCCCCC;background:#FFFFFF;" class="banner">
		<div style="position: relative; width: 100%; height: 60px; display: block;">
			<embed type="application/x-shockwave-flash" quality="best" wmode="opaque" play="true" loop="true" menu="true" allowscriptaccess="always" flashvars="" src="http://www.trucksale.ru/upload/docs/banners/scaniax60.swf" height="100%" width="100%">
		</div>
		<div style="position: relative; width: 100%; height: 60px; overflow: hidden; display: none;">
			<embed type="application/x-shockwave-flash" quality="best" wmode="opaque" play="true" loop="true" menu="true" allowscriptaccess="always" flashvars="" src="http://www.trucksale.ru/upload/docs/banners/scaniax300.swf" height="300px" width="100%">
		</div>
                </div>
                <div id="screengliderNoFlash" style="border-bottom:1px solid #CCCCCC;background:#FFFFFF;display:none; text-align: center;">
                        <a href="http://www.scania.ru/dealer-locator/dealers-scania/" target="_blank">
                                <img src="/img/scaniatmp.png" height="60">
                        </a>
                </div>';
              $out = array();
              $out[]=''.$html.'';
              return join("\n", $out).$smart_global;
          }

	  # СПИСОК БАННЕРОВ
	  function bannersList($id, $cid1, $cid2)
	  {
		  $smart_global = '';

		  if(!$list = $this->objects->getFullObjectsList($id)) return $smart_global;
		  $out = array();
		  foreach($list as $o)
		  {
			  if($o['class_id'] == 6)
			  {
				  if($this->lower($this->getFileExtension($o['Баннер'])) == 'swf')
				  {
					  $html = '
					  <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0"'.($o['width']?' width="'.$o['width'].'"':'').($o['height']?' height="'.$o['height'].'"':'').'>
						  <param name="movie" value="'._UPLOADS_.'/'.$o['Баннер'].'">
						  <param name="quality" value="high">
						  <param name="wmode" value="transparent">
						  <embed src="'._UPLOADS_.'/'.$o['Баннер'].'" quality="high" pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash"'.($o['width']?' width="'.$o['width'].'"':'').($o['height']?' height="'.$o['height'].'"':'').'>
					  </object>';
				  } else $html = '<a id="banner-'.$o['id'].'" href="'.($o['Ссылка']?$o['Ссылка']:'javascript:void(0)').'"'.($o['В новом окне']?' target="_blank"':'').'><img id="banner-'.$o['id'].'" src="'._UPLOADS_.'/'.$o['Баннер'].'" border="0"'.($o['width']?' width="'.$o['width'].'"':'').($o['height']?' height="'.$o['height'].'"':'').'></a>';
			  } else $html = htmlspecialchars_decode($o['Значение']);
			  $smart = '';
			  $out[]=''.$html.'';
		  }
		  return join("\n", $out).$smart_global;
	  }

	  #ФУНКЦИЯ ПОДАЧИ ГОЛОСА, АЙДИ ОПРОСА, АЙДИ ОТВЕТА
	  function voteOne($voting_id, $answer_id){
		  $class_id = 10;
		  if(!!$field_id = $this->db->select("fields", "WHERE `name`='Голоса' AND class_id='".$class_id."' LIMIT 1", "id")){
			  $this->db->mysql_query("UPDATE class_".$class_id." SET field_".$field_id."=field_".$field_id."+1 WHERE `object_id`='".$answer_id."'");
		  }
		  setcookie("votingStamp", time(), time()+3600*2);
	  }
	  #ФУНКЦИЯ КОТОРАЯ ВОЗВРАЩАЕТ ТАБЛИЦУ С РЕЗУЛЬТАТАМИ, ВХОДЯЩИЕ ДАННЫЕ - ID КОНКРЕТНОГО ОПРОСА
	  function getVotingResults($id){
		  if(!!$obj = $this->objects->getObject($id)){
			  $total_count = 0;
			  if(!!$list = $this->objects->getFullObjectsList($id)){
				  foreach($list as $a){
					  //if(!isset($a['Голоса']) || !is_numeric($a['Голоса'])) continue;
					  $total_count+=$a['Голоса'];
				  }
				  $colors = array(
					  "red",
					  "green",
					  "blue",
					  "yellow",
					  "purple",
					  "orange",
					  "black",
					  "magenta",
					  "gray"
				  );
				  $html = array('<br>');
				  foreach($list as $k=>$a){
					  $percentage = round($a['Голоса']/$total_count*100, 2);
					  $html[]='<div>'.$a['Ответ'].' '.$percentage.'%</div>';
					  $html[]='<div style="margin-bottom:10px;"><div style="width:'.($percentage*2).'px; background:'.$colors[$k].'; height:5px;"></div></div>';
					  //$html[]='<br>';
				  }
				  $html[]= '<div><b><!--o:43--> '.$this->sklon($total_count, array('человека', 'человек', 'человек')).'.</b> </div><br>';

			  }
		  }
		  return join("\n", $html);
	  }

	  function votingBlock($parent_id){
		  #БЕРЕТСЯ ПЕРВЫЙ АКТИВНЫЙ ОБЪЕКТ ОПРОСА ИЗ КАТАЛОГА ГОЛОСОВАНИЯ И ОТОБРАЖАЕТСЯ
		  $o = $this->objects->getFullObject( $this->db->select("objects", "WHERE `head`='".$parent_id."' AND `active`='1' ORDER BY sort DESC LIMIT 1", "id") );
		  $html = array();
		  $html[]= '<div>'.$o['Значение'].'</div>';
		  $html[]= '<div id="voting-backup" style="display:none"></div>';

		  $html[]= '<div id="voting-screen">';
		  #ЕСЛИ ЕСТЬ КУКИ ЗНАЧИТ ПОЛЬЗОВАТЕЛЬ УЖЕ ГОЛОСОВАЛ
		  if(!!@$_COOKIE['votingStamp']){
			  $html[]= $this->getVotingResults($o['id']);
		  }else{
			  $html[]= '<br>';
			  foreach($this->objects->getFullObjectsList($o['id']) as $k=>$a){
				  $html[]= '<div class="vote"><input name="votingAnswer" type="radio" value="'.$a['id'].'"'.(!$k?' checked':'').'> '.$a['Ответ'].'</div>';
			  }
			  $html[]= '<div><button class="vote_but" onclick="return voteIt(this, '.$o['id'].', $(\'#voting-screen\').find(\':checked\').val())"><!--o:44--></button></div>';
			  $html[]= '<div style="margin-top:10px;"><a href="#" onclick="return voteIt(this, '.$o['id'].', false)"><!--o:45--></a></div>';
		  }
		  $html[]= '</div>';
		  $html[]= '<div id="back-to-vote" style="display:none;">&larr; <a href="#" onclick="return showVoting()"><!--o:46--></a></div>';
		  return join("\n", $html);
	  }

	  function script_datepicker(){
		  #СТИЛИ НАДО ТАК СТАВИТЬ
		  #.ui-state-custom {
		  #	border-bottom: red solid 2px !important;
		  #	background: #F26100 !important;
		  #}
		  $out = array();
		  $object_id 	= 62;			# ID объекта в котором лежат новости
		  $class_id	= 8;

		  $out[] = '<script type="text/javascript">

						  $("#datepickerid").datepicker({';
							  if ($this->lang == 'ru'){
								  $out[]= 'firstDay: 1,
								  dayNames: ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"],
								  dayNamesMin: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
								  monthNames: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],';
							  }
							  $out[] = 'duration: "fast",
							  changeYear: true,
							  hightlight : { // подсвечиваем
								  format:"yy-mm-dd",';
								  if ($news = $this->objects->getFullObjectsListByClass($object_id, $class_id, "AND o.active='1'")){
									  $out[] = 'values:[';
									  foreach($news as $n)
									  {
										  $out[] = '"'.$n['Дата'].'",';
									  }
									  $out[] = '],';
								  }
								  if ($news = $this->objects->getFullObjectsListByClass($object_id, $class_id, "AND o.active='1'")){
									  $out[] = 'titles:[';
									  foreach($news as $n)
									  {
										  $out[] = '"'.$n['Название'].'",';
									  }
									  $out[] = '],';
								  }
								  $out[] = '
							  },onSelect: function(dateText) {
									  self.location.href = "/'.$this->lang.'/events.php?date="+dateText;
								  }
						  });
					  </script>';
		  return join('', $out);
	  }
	  
	# ФУНКЦИЯ ПОЛУЧЕНИЯ ТЕКУЩЕГО АДРЕСА СТРАНИЦЫ
	function curPageURL() {
		 $pageURL = 'http';
		 //if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		 $pageURL .= "://";
		 if ($_SERVER["SERVER_PORT"] != "80") {
		  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		 } else {
		  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		 }
		 return $pageURL;
	}
	  
#Функция получения формы
  function getForm($id,$class_id=29){
	  $obj = $this->objects->getFullObject($id);
	  $out = array('<form method="post" name="order-form">');
	  $out[] = $obj['Описание формы'];
	  if ($fields = $this->objects->getFullObjectsListByClass($id,$class_id)){

		  foreach ($fields as $o){
		  $smart = '
			  <!--smart:{
				  id:'.$o['id'].',
				  title:"поля",
				  actions:["edit", "remove"],
				  p:{
					  remove: "#field-'.$o['id'].'"
				  },
				  css:{}
			  }-->
		  ';
		switch ($o['Тип']){
			case 0://Простой текст
			$out[] = '
				<div id="field-'.$o['id'].'">'.$smart.'
				  <label>'.$o['Название'].'</label>
				  <input type="text" class="text" name="fields['.$o['Название'].']" '.($o['Обязательное']==1?'required':'').' pattern="^[А-Яа-яЁёA-Za-z0-9\s]+$" title="Только буквы, цифры и пробел" />
				</div>
			';
			break;
			case 1://Только буквы
			$out[] = '
				<div id="field-'.$o['id'].'">'.$smart.'
				 <label>'.$o['Название'].'</label>
				 <input type="text" class="name" name="fields['.$o['Название'].']" '.($o['Обязательное']==1?'required':'').' pattern="^[А-Яа-яЁёA-Za-z\s]+$" title="Только буквы" />
				</div>
			';
			break;
		   case 2://Число
			$out[] = '
				 <div id="field-'.$o['id'].'">'.$smart.'
				 <label>'.$o['Название'].'</label>
				 <input type="text" class="digits" name="fields['.$o['Название'].']" '.($o['Обязательное']==1?'required':'').' pattern="^[0-9]+$" title="Только цифры" />
				</div>
			';
			break;
		   case 3://Пароль
			$out[] = '
				 <div id="field-'.$o['id'].'">'.$smart.'
				 <label>'.$o['Название'].'</label>
				 <input type="password" class="password" name="fields['.$o['Название'].']" '.($o['Обязательное']==1?'required':'').' />
				</div>
			';
			break;
		   case 4://Email
			$out[] = '
				 <div id="field-'.$o['id'].'">'.$smart.'
				 <label>'.$o['Название'].'</label>
				 <input type="email" class="email" name="fields['.$o['Название'].']" '.($o['Обязательное']==1?'required':'').' />
				</div>
			';
			break;
		   case 5://Дата
			$out[] = '
				 <div id="field-'.$o['id'].'">'.$smart.'
				 <label>'.$o['Название'].'</label>
				 <input type="text" class="date-picker" name="fields['.$o['Название'].']" '.($o['Обязательное']==1?'required':'').' />
				</div>
			';
			break;
		   case 6://Текстовый блок
			$out[] = '
				 <div id="field-'.$o['id'].'">'.$smart.'
				 <label>'.$o['Название'].'</label>
				 <textarea name="fields['.$o['Название'].']" class="textarea" '.($o['Обязательное']==1?'required':'').' ></textarea>
				</div>
			';
			break;
		   case 7://Галочка
			$out[] = '
				 <div id="field-'.$o['id'].'">'.$smart.'
				 <label>'.$o['Название'].'</label>
				 <input type="checkbox" class="checkbox" name="fields['.$o['Название'].']"  />
				</div>
			';
			break;
		   case 8://Список
			$out[] = '
				 <div id="field-'.$o['id'].'">'.$smart.'
				 <label>'.$o['Название'].'</label>
				 <select class="select" name="fields['.$o['Название'].']" />';
			$options = explode("\n",$o['Значения']);
			foreach ($options as $i){
				$out[] = '<option>'.$i.'</option>';
			}
			   $out[] = '
				</select>
				</div>
			';
			break;
			case 9://Переключатели
			$out[] = '
				 <div id="field-'.$o['id'].'">'.$smart.'
				 <label>'.$o['Название'].'</label>';
				 $btns = explode("\n",$o['Значения']);
				 foreach($btns as $b){
				 $out[] = '<label><input type="radio" name="fields['.$o['Название'].']" value="'.$b.'">'.$b.'</label>';
				 }
			$out[] = '</div>';
			break;
		}
		  }
	  }
	  $out[] ='
	  <!--smart:{
		id : '.$obj['id'].',
		title : "формы",
		actions : ["edit","add"],
		p : {
			add : ['.$class_id.']
		},
		info : {
			"add" : "добавить&nbsp;поле",
			"edit" : "редактировать&nbsp;форму"
		},
		css : {
			marginBottom:20,
		}
	}-->';
	  if ($obj['Captcha']==1){
	  $out[] = 'Здесь будет выводиться Капча';
	  }
$out[] = '<input type="submit" value="'.$obj['Текст кнопки отправки'].'" />';
$out[] = '</form>';
return join("\n",$out);
  }

	#------------------------------------------------------------------------------------
	#общие Функции по промзоне
	#------------------------------------------------------------------------------------
	// function activateFunction()

	#Фильтрация инпута // Возможно придется дописать
	function filterInput($string){
//		return strip_tags(addslashes(trim($string)));
		return strip_tags((trim($string)));
	}

	#Получаем список компаний
	function getComps(){
		if (!$comps = $this->objects->getFullObjectsListByClass(303,33)) return false;
		$out = array();
		foreach ($comps as $k=>$c){
			$out[$c['id']] = $c;
		}
		return $out;
	}

	#Получаем родителей объекта
	function getParents($id,$last){
		if ($id == $last) return array();
		if (!$o = $this->objects->getFullObject($id)) return array();
		$out = array();
		$out[] = $o;
		$out = array_merge($out,$this->getParents($o['head'],$last));
		return $out;
	}

	#Получаем url компании
	function getComURL($com){
		if (!empty($com['URL'])) return '/co/'.$com['URL'];
		elseif (!empty($com['object_id'])) return '/co/'.$com['object_id'];
		else return '/co/'.$com['id'];
	}

	#Выводим список стран/регионов для автокомплита
	function getAutoCompleteArray($file){
		if (!file_exists($file) || (!$text = file_get_contents($file))) return false;
		$strings = explode("\n",$text);
		$out = array();
		foreach ($strings as $str){
			$out[] = "'$str'";
		}
		return join(", ",$out);
	}

	#Получаем регионы из базы данных
	function getACRegionsList(){
		if (!$list = $this->db->select("regions", "")) return false;
		$out = array();
		foreach ($list as $c){
			$out[] = "'".$c['index']."'";
		}
		return join(", ",$out);
	}

	#Получаем страны с БД
	function getACCountiesList(){
		if (!$list = $this->db->select("regions","WHERE `parent_id` = 0")) return false;
		$out = array();
		foreach ($list as $c){
			$out[] = "'".$c['index']."'";
		}
		return join(", ",$out);
	}

	#Получаем флаг
	function getFlag($region){
		if (empty($region) || (!$flag_id = $this->db->select("regions","WHERE `index` LIKE '%$region%' LIMIT 1", "grand_id"))) return '';
		return '<img class="ico-flag" src="/img/flags/'.$flag_id.'.png" alt="" />';
	}

	#Получаем флаг
	function getFlagUrl($region){
		if (empty($region) || (!$flag_id = $this->db->select("regions","WHERE `index` LIKE '%$region%' LIMIT 1", "grand_id"))) return '';
		return '/img/flags/'.$flag_id.'.png';
	}

	#Тип по меню
	function isAdsMenuType($id, $type){
		switch ($id){
			case 167: $typeID = 0; break;
			case 168: $typeID = 1; break;
			case 169: $typeID = 2; break;
			case 170: $typeID = 3; break;
		}
		if ($typeID == $type) return true;
		else return false;
	}


	/**
	* Функция возвращает окончание для множественного числа слова на основании числа и массива окончаний
	* @param  $number Integer Число на основе которого нужно сформировать окончание
	* @param  $endingsArray  Array Массив слов или окончаний для чисел (1, 4, 5),
	*         например array('яблоко', 'яблока', 'яблок')
	* @return String
	*/
	function getNumEnding($number, $endingArray)
	{
	    $number = $number % 100;
	    if ($number>=11 && $number<=19) {
	        $ending=$endingArray[2];
	    }
	    else {
	        $i = $number % 10;
	        switch ($i)
	        {
	            case (1): $ending = $endingArray[0]; break;
	            case (2):
	            case (3):
	            case (4): $ending = $endingArray[1]; break;
	            default: $ending=$endingArray[2];
	        }
	    }
	    return $ending;
	}

}

$api = new api();
// if(($zaglushka=$api->objects->getFullObject(162)) && ($zaglushka['active']==1) ) exit ($zaglushka['Значение']);
if(isset($_REQUEST['lang']) && array_key_exists($_REQUEST['lang'], $api->languages)) $api->lang = $_REQUEST['lang'];
if( empty($_SESSION['auth']['u']) || !is_array($AUTH_USER = $_SESSION['auth']['u']) ) $AUTH_USER = array();
?>
