<?
#Что нужно :
##Прописать ссылки в getaddHtml
##вывести поля наличия 
## Получить статусы объявлений
# Фото объявлений

class ads extends api{

public $title,$tpl;
public $colors, $class_id, $is_got, $money, $types, $root_id, $cat_class_id, $checked, $cat, $subcat, $on_page, $cats, $subcats, $sort, $vars;

	function __construct(){
		parent::__construct();
		$this->tpl = '/ads.html';
		$this->title = 'Список объявлений';

		$this->class_id = 31;
		$this->root_id = 228;
		$this->cat_class_id = 30;

		$this->user = $this->objects->getFullObject($_SESSION['u']['id']);

		#Цвета объявлений
		$this->colors = array(
			'0'=>'',
			'1'=>'tr-green',
			'2'=>'tr-yellow',
			'3'=>'tr-blue',
		);
		$this->is_got = $this->objects->getFieldP3(117,$this->class_id);
		$this->money = $this->objects->getFieldP3(116,$this->class_id);
		$this->types = $this->objects->getFieldP3(100,$this->class_id);

		#Переменные фильтра
		$this->checked = isset($_GET['sort-filter']) && is_numeric($_GET['sort-filter']) ? $_GET['sort-filter'] : 'all';
		$this->cat = isset($_GET['cat']) && is_numeric($_GET['cat']) ? $_GET['cat'] : 'all';
		$this->subcat = isset($_GET['subcat']) && is_numeric($_GET['subcat']) ? $_GET['subcat'] : 'all';
		$this->on_page = isset($_GET['on_page']) && is_numeric($_GET['on_page']) ? $_GET['on_page'] : 25;
		$this->sort = isset($_GET['sort']) ? $_GET['sort'] : '123';

		#Получаем разделы
		$this->cats = $this->getAdsCat($this->root_id);
		$this->subcats = is_numeric($this->cat) ? $this->getAdsCat($this->cat) : $this->getAdsSubcats($this->cats);
		$this->editAdsList();
	}


	#Получаем таблицу объявлений объявляения 
	function getAdsList($start,$limit){
		$sql = $this->getFilterSql();
		if (!$list = $this->objects->getFullObjectsListByCLass(-1,$this->class_id,"AND o.active = '1' AND c.field_161 = '".$this->user['id']."' AND c.field_102 <> '2' $sql LIMIT $start, $limit")) return false;
		return $list;
	}

	#Получаем статус объявления с датами
	function getAdStatus($ad){
		$out = array();
		switch ($ad['Статус']){
			case 2: 
				return false;
			break;
			case 1:
				$out[] = array(
					'content'=>'Объявление одобренно модератором.',
					'date'=>$this->strings->date($ad['Дата добавления'],'sql','date'),
					'icon'=>'icon-ok icon-grey',
				);
			break;
			case 3:
				$out[] = array(
					'content'=>'Объявление отклонено модератором.',
					'date'=>$this->strings->date($ad['Дата добавления'],'sql','date'),
					'icon'=>'icon-remove icon-grey',
				);
			break;
			default :
				$out[] = array(
					'content'=>'Объявление ожидает проверку модератором.',
					'date'=>$this->strings->date($ad['Дата добавления'],'sql','date'),
					'icon'=>'icon-warning-sign icon-yellow',
				);
			break;
		}
		if (!empty($ad['Дата поднятия']) && ($ad['Дата поднятия']!='0000-00-00')) $out[] = array(
			'content'=>'',
			'date'=>$this->strings->date($ad['Дата поднятия'],'sql','date'),
			'icon'=>'icon-chevron-up icon-grey'
		);
		return $out;
	}

	#Получаем страницы для пагинации
	function getPages(){
		$sql = $this->getFilterSql();
		$count = $this->objects->getObjectsCount(-1,$this->class_id, "AND o.active = '1' AND c.field_161 = '".$this->user['id']."' AND c.field_102 <> '2' $sql");
		if(!isset($_REQUEST['pg']) || !is_numeric($current_page = $_REQUEST['pg']) || $current_page>ceil($count/$this->on_page)) $current_page = 1;
		return $this->objects->pagination($count, $current_page, $this->on_page);
	}

	#ПОлучаем колличество элементов по типу
	function getAdsCountByType($i){
		if (!is_numeric($i)) return '0';
		return $this->objects->getObjectsCount(-1,$this->class_id, "AND o.active = '1' AND c.field_161 = '".$this->user['id']."' AND c.field_102 <> '2' AND c.field_100 = '$i'");
	}

	#Получаем колличество всех объявлений
	function getAdsCount(){
		return $this->objects->getObjectsCount(-1,$this->class_id, "AND o.active = '1' AND c.field_161 = '".$this->user['id']."' AND c.field_102 <> '2'");
	}

	#Получаем разделы
	function getAdsCat($id){
		if (!$list = $this->objects->getFullObjectsListByClass($id,$this->cat_class_id)) return false;
		$out = array();
		foreach ($list as $o){
			$out[$o['id']] = $o;
		}
		return $out;
	}

	#Получаем подразделы в нескольких разделах
	function getAdsSubcats($cats){
		if (!is_array($cats)) return false;
		$out = array();
		foreach ($cats as $c){
			if (!$list = $this->objects->getFullObjectsListByClass($c['id'],$this->cat_class_id)) continue;
			foreach ($list as $o){
				$out[$o['id']] = $o;
			}
		}
		return $out;
	}

	#Cсылка пагинации
	function pgHref($i,$type = ''){
		switch ($type){
			case 'next' :
				return 'href="/cabinet/ads/pg/'.intval($i+1).'/?sort-filter='.$this->checked.'&cat='.$this->cat.'&subcat='.$this->subcat.'&on_page='.$this->on_page.'"';
			break;
			case 'prev' :
				return 'href="/cabinet/ads/pg/'.intval($i-1).'/?sort-filter='.$this->checked.'&cat='.$this->cat.'&subcat='.$this->subcat.'&on_page='.$this->on_page.'"';
			break;
			default:
				return 'href="/cabinet/ads/pg/'.intval($i).'/?sort-filter='.$this->checked.'&cat='.$this->cat.'&subcat='.$this->subcat.'&on_page='.$this->on_page.'"';
			break;
		}
	}

	#sql запрос для фильтра
	function getFilterSql(){
		$sort =$this->sortAds($this->sort);
		$out = '';
		if (is_numeric($this->checked) ) $out .= "AND c.field_100 = '".$this->checked."' ";
		if (is_numeric($this->cat) ) $out .= "AND c.field_167 = '".$this->cat."' ";
		if (is_numeric($this->subcat) ) $out .= "AND c.field_169 = '".$this->subcat."' ";
		if (isset($this->sort)) $out .= "ORDER BY $sort ";
		return $out;
	}

	#Сортировка объявлений
	function sortAds($what){
		switch ($what){
			case 'type':
				return "c.field_100 ASC";
			break;
			case 'type_desc':
				return "c.field_100 DESC";
			break;
			case 'img':
				return "c.field_163 ASC";
			break;
			case 'img_desc':
				return "c.field_163 DESC";
			break;
			case 'name':
				return "c.field_107 ASC";
			break;
			case 'name_desc':
				return "c.field_107 DESC";
			break;
			case 'razdel':
				return "c.field_167 ASC";
			break;
			case 'razdel_desc':
				return "c.field_167 DESC";
			break;
			case 'price':
				return "c.field_115 ASC";
			break;
			case 'price_desc':
				return "c.field_115 DESC";
			break;
			case 'date':
				return "c.field_103 ASC";
			break;
			case 'date_desc':
				return "c.field_103 DESC";
			break;
			default: return "o.sort";
		}
	}

	#Действия при модальном окне
	function editAdsList(){
		if (empty($_POST['ad']) || !is_array($ads = $_POST['ad'])) return false;
		if (isset($_POST['archivate']) && ($_POST['archivate'] == 1)){
			if ($this->archivate($ads)) header("location: ".$_SERVER['REQUEST_URI']);
		}elseif(isset($_POST['delete']) && ($_POST['delete'] == 1)){
			if ($this->deleteAd($ads)) header("location: ".$_SERVER['REQUEST_URI']);
		}elseif(isset($_POST['restore']) && ($_POST['restore'] == 1)){
			if ($this->restoreAd($ads)) header("location: ".$_SERVER['REQUEST_URI']);
		}elseif(isset($_POST['highlight']) && ($_POST['highlight'] == 1) && isset($_POST['HighlightColor']) && is_numeric($color = $_POST['HighlightColor'])){
			if ($this->changeColor($ads,$color)) header("location: ".$_SERVER['REQUEST_URI']);
		}elseif(isset($_POST['rise']) && ($_POST['rise'] == 1)){
			if ($this->riseAd($ads)) header("location: ".$_SERVER['REQUEST_URI']);
		}elseif(isset($_POST['hot']) && ($_POST['hot'] == 1)){
			if ($this->hotAd($ads)) header("location: ".$_SERVER['REQUEST_URI']);
		}else return false;
	}

	#Переносим объявление в архив
	function archivate($ads){
		if (!is_array($ads)) return false;
		$out = array();
		foreach ($ads as $k => $id){
			if ($this->db->update("class_".$this->class_id, array("field_102" => "2"), "WHERE `object_id` = $k AND `lang`='".$this->lang."'"))
				$out[$k] ='Добавлено';
			else $out[$k] = 'Не добалено';
		}
		return $out;
	}

	#Удаляем объявление
	function deleteAd($ads){
		if (!is_array($ads)) return false;
		$out = array();
		foreach ($ads as $k => $id){
			if ($this->db->update("objects", array("active" => "0"), "WHERE `id` = '$k' "))
				$out[$k] ='Удалено';
			else $out[$k] = 'Нет';
		}
		return $out;
	}

	#Востановить из архива
	function restoreAd($ads){
		if (!is_array($ads)) return false;
		$out = array();
		foreach ($ads as $k => $id){
			if ($this->db->update("class_".$this->class_id, array("field_102" => "0"), "WHERE `object_id` = $k AND `lang`='".$this->lang."'"))
				$out[$k] ='Востановлена';
			else $out[$k] = 'Не востановлена';
		}
		return $out;
	}

	#ССылка сортировки
	function sortHref($str){
		$link = !empty($_GET['sort-filter']) ? $_SERVER['REQUEST_URI']."&sort=" : "?sort=";
		if (isset($_GET['sort']) && ($sort = $_GET['sort']) && ($sort == $str)){
			$link .= $str.'_desc';
		}else{
			$link .= $str;
		}
		return $link;
	}

	#Изменеям цвет объявлений
	function changeColor($arr,$color){
		if (!is_array($arr) || !is_numeric($color)) return false;
		if ($_SESSION['u']['credits'] < 200) return false;
		foreach ($arr as $k=>$i){
			if (!$this->objects->editObjectFields($k,array(106=>$color,104=>date("Y-m-d")))) return false;
			$count = intval($_SESSION['u']['credits']) - 200;
			$this->saveTransaction(200, "Изменение цвета объявления № $k");
			$this->changeCredits($count);
		}
		return true;
	}

	#Поднять объявление
	function riseAd($arr){
		if (!is_array($arr)) return false;
		if ($_SESSION['u']['credits'] < 100) return false;
		foreach ($arr as $k=>$i){
			if (!$this->objects->editObjectFields($k,array(104=>date("Y-m-d")))) return false;
			$count = intval($_SESSION['u']['credits']) - 100;
			$this->changeCredits($count);
			$this->saveTransaction(100, "Поднятие объявления № $k");
		}
		return true;
	}

	#Сделать горячим объявлением
	function hotAd($arr){
		if (!is_array($arr)) return false;
		if ($_SESSION['u']['credits'] < 300) return false;
		foreach ($arr as $k=>$i){
			if (!$this->objects->editObjectFields($k,array(211=>'1',104=>date("Y-m-d")))) return false;
			$count = intval($_SESSION['u']['credits']) - 300;
			$this->changeCredits($count);
			$this->saveTransaction(300, "Сделать горячим объявление № $k");
		}
		return true;
	}

	#Изменяем колличество кредитов
	function changeCredits($count){
		if (!is_numeric($count)) return false;
		if ($_SESSION['u']['role'] == 'user'){
			if ($this->objects->editObjectFields($_SESSION['u']['id'],array(155=>$count))){
				$_SESSION['u']['credits'] = $count;
			}
		}else{
			if ($this->objects->editObjectFields($_SESSION['u']['id'],array(171=>$count))){
				$_SESSION['u']['credits'] = $count;
			}
		}
	}

	#Сохраняем транзакцию
	function saveTransaction($summ, $type = ''){
		if (empty($summ)) return false;
		include_once(_PUBLIC_ABS_.'/transactions.php');
		$trans = new transactions();
		$from = $_SESSION['u']['id'];
		$to = 0;
		$date = time();
		$invoice_id = 0;
		if (!$trans->saveData($from, $to, $summ, $type, $invoice_id)) return false;
		return true;
	}




}?>