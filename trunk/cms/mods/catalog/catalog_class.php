<?class catalog extends api{

public $title, $object, $root_id, $ads_type, $cat_class_id, $prod_class_id, $subcats, $is_got, $money, $condition, $companies, $regions, $marks, $countries, $on_page, $heads, $pages, $sort, $filter_sql;

	function __construct(){
		if (!isset($_GET['type'])) header("location: /404/");
		parent::__construct();
		$this->root_id = 228;
		$this->cat_class_id = 30;
		$this->prod_class_id = 31;
		$this->on_page = $this->getAdsLimit();
		$this->ads_type = $this->getAdsType();
		$this->object = $this->getCatObject();
		$this->heads = $this->getAdsHeads($this->object['id']);
		$this->subcats = $this->objects->getFullObjectsListByClass($this->object['id'],$this->cat_class_id);
		$this->title = $this->getTitle();
		$this->is_got = $this->objects->getFieldP3(117,$this->prod_class_id);
		$this->money = $this->objects->getFieldP3(116,$this->prod_class_id);
		$this->condition = $this->objects->getFieldP3(108,$this->prod_class_id);
		$this->sroks = $this->objects->getFieldP3(213,$this->prod_class_id);
		$this->companies = $this->getCompanies();
		$this->regions = $this->objects->getFullObjectsListByClass(257,32);
		$this->marks = $this->objects->getFullObjectsListByClass(255,2);
		$this->countries = $this->objects->getFullObjectsListByClass(256,7);
		$this->getFilterSql();
		$this->getSortSql();
		$this->pages = $this->getPaginationPages();
		// $this->types = $this->objects->getFieldP3(100,$this->class_id);
		$this->orderCall();
	}

	#Вывод страницы
	function getPage(){
		include_once(_MODS_ABS_.'/catalog/catalog.html');
	}

	#Заказ звонка с отправкой на email
	function orderCall(){
		if (!isset($_POST['order_call']) || ($_POST['order_call']!='1')) return false;
		if (!isset($_POST['phone']) || !is_array($_POST['phone'])) return false;
		$phone = '';
		$ad_name = !empty($_POST['ad_name']) ? $_POST['ad_name'] : '';
		foreach ($_POST['phone'] as $p){
			if (empty($p) || !is_numeric($p)) return false;
			$phone .= $p.' ';
		}
		if (!isset($_POST['time']) || !is_array($t = $_POST['time'])) return false;
		if (!isset($_POST['order-call-time-id1'])) return false;
		if (!isset($_POST['email']) || (!$email = $_POST['email'])) return false;
		if ($_POST['order-call-time-id1'] == 'anytime'){
			$time = 'в любое время';
		}else{
			$time = 'между '.$t[0]['h'].':'.$t[0]['m'].' - '.$t[1]['h'].':'.$t[1]['m'].' часов';
		}
		$this->mail->to = $email;
		$this->mail->from = 'order@'.$_SERVER['HTTP_HOST'];
		$this->mail->subject = 'Заказ звонка с сайта '.$_SERVER['HTTP_HOST'];
		$this->mail->body = '
		<p>Увашаемый клиент!</p>
		<p>Благодарим Вас, за то что воспользовались услугами портала Promzona.kz<br>
		На Ваш адрес поступила заявка на обратный звонок, если желаете связаться покупателем просим вас  перезвонить следующему номеру:  '.$phone.' '.$time.' <br>
		Наименование товара: '.$ad_name.'<br>
		Удачной Вам сделки,<br>
		С уважением,<br>
		Support@promzona.kz</p>
		';
		$this->mail->send($this->mail->to); 
		header("location: ".$_SERVER['REQUEST_URI']);
	}

	#Получаем объект каталога
	function getCatObject(){
		if (isset($_GET['action']) && is_numeric($cat = $_GET['action']) && ($obj = $this->objects->getFullObject($cat)) && ($obj['class_id']==$this->cat_class_id)) {
			return $obj;
		}else{
			header("location: /404/");
		}
	}

	#Получаем тип объявлений 
	function getAdsType(){
		if (!isset($_GET['type'])) return '0';
		switch ($_GET['type']){
			case 'buy':
				return '0';
			break;
			case 'sell':
				return '1';
			break;
			case 'forrent':
				return '2';
			break;
			case 'rent':
				return '3';
			break;
			// default:
			// 	return '0';
			// break;
		}
	}

	#Получаем все компании
	function getCompanies($id = 303, $class_id = 33){
		if (!$list = $this->objects->getFullObjectsListByClass($id,$class_id)) return false;
		$out = array();
		foreach ($list as $o){
			$out[$o['id']] = $o;
		}
		return $out;
	}

	#Получаем заголовок страницы
	function getTitle(){
		if (!empty($this->object['Название'])) return $this->object['Название'];
		elseif (isset($_GET['mod']) && ($_GET['mod']=='buy')) return 'Купить';
	}

	#Получаем колличество объявлений в каталоге и подкаталогах
	function getAdsCountInCat($id){
		$count = 0;
		if (!isset($_GET['mod'])) return 0;
		// $type = $this->getAdsType($_GET['mod']);
		if (!$list = $this->objects->getFullObjectsList($id)) return 0;
		foreach ($list as $o){
			if ($o['class_id']==$this->cat_class_id){
				$count += $this->getAdsCountInCat($o['id']);
			}elseif (($o['class_id']==$this->prod_class_id)&&($o['Статус']==1)&&($o['Статус']==1)&&($o['Тип']==$this->ads_type)){
				$count += 1;
			}
		}
		return $count;
	}

	#Получаем Типы объявлений
	function getAdsTypeByMod($type){
		switch ($type){
			case 'buy':
				return '0';
			break;
			case 'sell':
				return '1';
			break;
			case 'forrent':
				return '2';
			break;
			case 'rent':
				return '3';
			break;
			default:
				return '0';
			break;
		}
	}

	#Получаем объявления
	function getAds(){
		$heads = $this->heads;
		$pages = $this->pages;
		$head_sql = !empty($heads) ? "AND o.head IN (".$this->object['id'].",".join(",",$heads).")" : "AND o.head = '".$this->object['id']."'";
		$where = "WHERE c.lang='".$this->lang."' $head_sql AND o.class_id = '".$this->prod_class_id."' AND o.active = '1' AND c.field_102 = '1' AND c.field_100 = '".$this->ads_type."' ".$this->filter_sql." ".$this->sort." LIMIT ".$pages['start'].", ".$pages['on_page'];
		$sql = "LEFT JOIN class_".$this->prod_class_id." as c ON o.id=c.object_id $where";
		return $this->db->select("objects as o", $sql, "o.*,c.*");
	}

	#Получаем heads объявлений
	function getAdsHeads($id){
		$out = array();
		if (!$list = $this->db->select("objects","WHERE `head`='$id' AND `active`='1' AND `class_id`='".$this->cat_class_id."'","id")) return false;
		$out = $list;
		foreach ($list as $i){
			if (is_array($arr = $this->getAdsHeads($i)))
				$out = array_merge($out,$arr);
		}
		return $out;
	}

	#Цвет объявления
	function getItemColor($n){
		switch ($n){
			case '1':
				return 'item-green';
			break;
			case '2':
				return 'item-yellow';
			break;
			case '3':
				return 'item-blue';
			break;
			default:
				return '';
			break;
		}
	}

	#Телефон объявления
	function companyTel($id){
		if (empty($this->companies[$id]['Телефон'])) return false;
		$telephone = explode("\n",$this->companies[$id]['Телефон']);
		return $telephone[0];
	}

	#Получаем страницы для пагинации
	function getPaginationPages(){
		$heads = $this->heads;
		$head_sql = !empty($heads) ? "AND o.head IN (".$this->object['id'].",".join(",",$heads).")" : "AND o.head = '".$this->object['id']."'";
		$where = "WHERE c.lang='".$this->lang."' $head_sql AND o.class_id = '".$this->prod_class_id."' AND o.active = '1' AND c.field_102 = '1' AND c.field_100 = '".$this->ads_type."' ".$this->filter_sql." ".$this->sort;
		$sql = "LEFT JOIN class_".$this->prod_class_id." as c ON o.id=c.object_id $where";
		$count = $this->db->count("objects as o", $sql);
		if(!isset($_REQUEST['pg']) || !is_numeric($current_page = $_REQUEST['pg']) || $current_page>ceil($count/$this->on_page)) $current_page = 1;
		return $this->objects->pagination($count, $current_page, $this->on_page);
	}

	#Ссылка пагинации
	function pgHref($i,$type = ''){
		if (!isset($_GET['action'])||(!is_numeric($id = $_GET['action']))) return false;
		$link = '/'.$_GET['type'].'/catalog/'.$id.'/pg-';
		switch ($type){
			case 'next' :
				return 'href="'.$link.intval($i+1).'/"';
			break;
			case 'prev' :
				return 'href="'.$link.intval($i-1).'/"';
			break;
			default:
				return 'href="'.$link.intval($i).'/"';
			break;
		}
	}

	#Получаем строку sql для фильтрации
	function getFilterSql(){
		$this->filter_sql = '';
		if (!empty($_GET['region']) && ($r=$_GET['region'])) $this->filter_sql .= "AND c.field_118 = '$r' ";
		if (!empty($_GET['mark']) && ($m=$_GET['mark'])) $this->filter_sql .= "AND c.field_109 = '$m' ";
		if (!empty($_GET['with_photo']) && ($_GET['with_photo']=='on')) $this->filter_sql .= "AND c.field_163 <> '' ";
		if (!empty($_GET['y1']) && is_numeric($_GET['y1']) && ($y1 = (int)$_GET['y1']) ) $this->filter_sql .= "AND c.field_112 >= '$y1' ";
		if (!empty($_GET['y1']) && is_numeric($_GET['y2']) && ($y2 = (int)$_GET['y2']) ) $this->filter_sql .= "AND c.field_112 <= '$y2' ";
		if (isset($_GET['condition']) && is_numeric($c = $_GET['condition']) ) $this->filter_sql .= "AND c.field_108 = '$c' ";
		if (isset($_GET['country']) && ($country = $_GET['country']) ) $this->filter_sql .= "AND c.field_111 like '%$country%' ";
	}

	#sql сортировки
	function getSortSql(){
		if (!empty($_GET['sort'])){
			switch ($_GET['sort']){
				case 'date':
					$s = "c.field_103 ASC";
				break;
				case 'date_desc':
					$s = "c.field_103 DESC";
				break;
				case 'price':
					$s = "c.field_115 ASC";
				break;
				case 'price_desc':
					$s = "c.field_115 DESC";
				break;
				case 'year':
					$s = "c.field_112 ASC";
				break;
				case 'year_desc':
					$s = "c.field_112 DESC";
				break;
				default:
					$s = "o.sort";
				break;
			}
		}else{
			$s = "o.sort";
		}
		$this->sort = "ORDER BY c.field_211 DESC, c.field_104 DESC, $s";
	}

	#Активна ли сортировка по данному полю
	function isSortActive($field){
		if (!empty($_GET['sort']) && ($field == $_GET['sort'])) return true;
		else return false;
	}
        
        #Признак сортировки (дата/цены/год)
        function getSortBy(){
            return (!empty($_GET['sort'])) ? $_GET['sort'] : '';
        }

	#Ссылка сортировки
	function sortHref($word){
		$href = isset($_GET['region']) || isset($_GET['on_page']) ? '&' : '?';
		$href .= "sort=$word";
		if (isset($_GET['region']) || isset($_GET['on_page'])){
			if (!empty($_GET['sort'])){
				$link = explode('&sort',$_SERVER['REQUEST_URI']);
				return $link[0].$href;
			}else{
				return $_SERVER['REQUEST_URI'].$href;
			}
		}else{
			return $href;
		}
	}

	#Колличество объявлений на страницу
	function getAdsLimit(){
		if (!isset($_GET['on_page']) || !is_numeric($_GET['on_page'])) return '10';
		return $_GET['on_page'];
	}

	#Хлебные крошки
	function catBC($id){
		$parents = array_reverse($this->getParents($id, 228));
		switch ($_GET['type']){
			case 'buy' :
				$str = 'Продажа';
			break;
			case 'sell' :
				$str = 'Покупка';
			break;
			case 'rent' :
				$str = 'Аренда';
			break;
			case 'forrent' :
				$str = 'Аренда';
			break;
			default:
				$str = 'Покупка';
			break;
		}
		$out = array();
		foreach ($parents as $k=>$o){
			if ($k==0){
				if ($o['Название'] == 'Оборудование') $razd = 'оборудования';
				if ($o['Название'] == 'Спецтехника') $razd = 'спецтехники';
				$out[] = '<a class="bd-beige bold" href="/'.$_GET['type'].'/">'.$str.' '.$razd.'</a>&nbsp;<i class="icon-chevron-right icon-grey"></i>';
			}elseif($k==(count($parents)-1)){
				$out[] = '<a class="bd-beige bold" href="#">'.$o['Название'].'</a>&nbsp;<i class="icon-chevron-down icon-grey"></i>';
			}else{
				$out[] = '<a class="bd-beige bold" href="/'.$_GET['type'].'/catalog/'.$o['id'].'/">'.$o['Название'].'</a>&nbsp;<i class="icon-chevron-right icon-grey"></i>';
			}
		}
		return join("\n",$out);
	}


}?>