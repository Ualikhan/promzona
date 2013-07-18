<?class search extends api{

public $title, $count, $ads, $search_word, $cats, $class_id_cat;

	function __construct(){
		parent::__construct();
		$this->class_id = 31;
		$this->class_id_cat = 30;
		$this->search_word = !empty($_GET['what']) ? $this->filterInput($_GET['what']) : '';
		$this->ads = $this->getAds();
		$this->count = $this->getAdsCount();
		$this->title = $this->getTitle();
		$this->is_got = $this->objects->getFieldP3(117,$this->class_id);
		$this->money = $this->objects->getFieldP3(116,$this->class_id);
		$this->condition = $this->objects->getFieldP3(108,$this->class_id);
		$this->companies = $this->getCompanies();
		$this->regions = $this->objects->getFullObjectsListByClass(257,32);
		$this->marks = $this->objects->getFullObjectsListByClass(255,2);
		$this->countries = $this->objects->getFullObjectsListByClass(256,7);
		$this->cats = $this->getFoundCats();
		$this->orderCall();
	}

	#Заказ звонка с отправкой на email
	function orderCall(){
		if (!isset($_POST['order_call']) || ($_POST['order_call']!='1')) return false;
		if (!isset($_POST['phone']) || !is_array($_POST['phone'])) return false;
		$phone = '';
		foreach ($_POST['phone'] as $p){
			if (empty($p) || !is_numeric($p)) return false;
			$phone .= $p.' ';
		}
		if (!isset($_POST['time']) || !is_array($t = $_POST['time'])) return false;
		if (!isset($_POST['order-call-time-id1'])) return false;
		if (!isset($_POST['email']) || (!$email = $_POST['email'])) return false;
		if ($_POST['order-call-time-id1'] == 'anytime'){
			$time = 'В любое время';
		}else{
			$time = $t[0]['h'].':'.$t[0]['m'].' - '.$t[1]['h'].':'.$t[1]['m'];
		}
		$this->mail->to = $email;
		$this->mail->from = 'order@'.$_SERVER['HTTP_HOST'];
		$this->mail->subject = 'Заказ звонка с сайта '.$_SERVER['HTTP_HOST'];
		$this->mail->body = '
			<div><b>Номер</b></div>
			<div>'.$phone.'</div>
			<div><b>Время</b></div>
			<div>'.$time.'</div>
		';
		$this->mail->send($this->mail->to); 
		header("location: ".$_SERVER['REQUEST_URI']);
	}

	#Вывод страницы
	function getPage(){
		include_once(_MODS_ABS_.'/search/search.html');
	}

	#ПОлучаем заголовок поиска
	function getTitle(){
		// if (empty($this->search_word)) return 'объявлений не найдено';
		// if (empty($this->ads)) return 'По запросу "'.$this->search_word.'" объявлений не найдено';
		if (empty($this->ads)) return 'Объявлений не найдено';
	 // return 'По запросу "'.$this->search_word.'" найдено '.$this->count.' объявлений';
	 return 'Найдено '.$this->count.' объявлений';
	}

	#Формируем sql запрос в зависимости от query_string
	function getSearchSql(){
		$where = "WHERE o.active = '1' AND c.field_102 = '1' AND o.class_id = '".$this->class_id."' ";
		$where .= !empty($_GET['section']) ? "AND c.field_167 = '".$this->filterInput($_GET['section'])."' " : '';
		$where .= isset($_GET['type']) && is_numeric($_GET['type']) ? "AND c.field_100 = '".$this->filterInput($_GET['type'])."' " : '';
		$where .= !empty($_GET['region']) ? "AND c.field_118 = '".$this->filterInput($_GET['region'])."' " : '';
		$where .= !empty($_GET['proiz']) ? "AND c.field_111 = '".$this->filterInput($_GET['proiz'])."' " : '';
		$where .= !empty($_GET['with_photo']) && ($_GET['with_photo']=='1') ? "AND c.field_163 <> '' " : '';
		$where .= !empty($_GET['y1']) && is_numeric($_GET['y1']) ? "AND c.field_112 >= '".$this->filterInput($_GET['y1'])."' " : '';
		$where .= !empty($_GET['y2']) && is_numeric($_GET['y2']) ? "AND c.field_112 <= '".$this->filterInput($_GET['y2'])."' " : '';
		$where .= !empty($_GET['condition']) && is_numeric($_GET['condition']) ? "AND c.field_108 = '".$this->filterInput($_GET['condition'])."' " : '';
		$where .= !empty($_GET['country']) ? "AND c.field_111 = '".$this->filterInput($_GET['country'])."' " : '';
		$where .= !empty($_GET['category']) ? "AND (c.field_169 = '".$this->filterInput($_GET['category'])."' OR o.head = '".$this->filterInput($_GET['category'])."') " : '';
		// $where .= !empty($_GET['category']) ? "AND c.field_169 = '".$this->filterInput($_GET['category'])."'" : '';
		$where .= !empty($_GET['price1']) ? "AND c.field_115 >= '".$this->filterInput($_GET['price1'])."' " : '';
		$where .= !empty($_GET['price2']) ? "AND c.field_115 <= '".$this->filterInput($_GET['price2'])."'" : '';
		$where .= !empty($_GET['mark']) ? "AND c.field_109 <= '".$this->filterInput($_GET['mark'])."'" : '';
		if (!empty($this->search_word)) {$where .= "AND c.field_107 LIKE '%".$this->search_word."%' ";}
		$where .= $this->getSortSql();
		return "LEFT JOIN class_".$this->class_id." as c ON o.id=c.object_id $where";
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
		return "ORDER BY c.field_211 DESC, c.field_104 DESC, $s";
	}

	#Получаем объявления
	function getAds(){
		// if (empty($this->search_word)) return false;
		$sql = $this->getSearchSql();
		// $fields = $this->objects->getClassFields($this->class_id);
		$ads = $this->db->select("objects as o", $sql);
		return $ads;
	}

	#Получаем колличество объявлений
	function getAdsCount(){
		if (!is_array($this->ads)) return '0';
		return count($this->ads);
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

	#Получаем каталоги найденных объявлений
	function getFoundCats(){
		if (empty($this->ads)) return false;
		$fields = $this->db->select("fields", "WHERE `class_id` = ".$this->class_id_cat." ORDER BY sort");
		$ids = array();
		foreach ($this->ads as $k=>$ad){
			if (!in_array($ad['head'],$ids))
				$ids[] = $ad['head'];
		}
		$where = "WHERE o.id IN (".join(", ",$ids).") AND o.active = '1' ORDER BY o.sort";
		if (!$cats = $this->db->select("objects as o", "LEFT JOIN class_".$this->class_id_cat." as c ON o.id = c.object_id $where")) return false;;
		$out = array();
		foreach ($cats as $k=>$cat){
			$out[$k] = array();
			foreach ($cat as $key=>$i){
				$object_field_name = explode("_",$key);
				if (isset($object_field_name[1]) && is_numeric($field_id = $object_field_name[1])) {
					foreach ($fields as $f){
						if ($f['id']==$field_id) {
							$out[$k][$f['name']] = $i;
							break;
						}
					}
				}else{
					$out[$k][$key] = $i;
				}
			}
		}
		return $out;
	}

	#Получаем колличество объявлений в каталоге
	function getAdsCountIncat($id){
		if (empty($this->ads)) return '0';
		$count = 0;
		foreach ($this->ads as $ad){
			if ($ad['head']==$id) ++$count;
		}
		return $count;
	}

	#Получаем имя каталога по id
	function getCatNameById($id){
		if (empty($this->cats)) return false;
		foreach ($this->cats as $cat){
			if ($cat['object_id'] == $id) return $cat['Название'];
		}
	}

	#Активна ли сортировка по данному полю
	function isSortActive($field){
		if (!empty($_GET['sort']) && ($field == $_GET['sort'])) return true;
		else return false;
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


}?>