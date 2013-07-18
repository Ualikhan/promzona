<?class companies extends api{
	public $title;
	public $class_id_com, $class_id_cat, $class_id_prod, $regions, $companies, $category, $region, $on_page, $pages;

	function __construct(){
		parent::__construct();
		$this->class_id_prod = 31;
		$this->class_id_com = 33;
		$this->class_id_cat = 30;
		$this->getDopProperties();
		$this->pages = $this->getPaginationPages();
		$this->category = $this->getOneCat();
		$this->title = $this->getTitle();
		$this->companies = $this->getCompanies();
		$this->regions = $this->objects->getFullObjectsListByClass(257,32);
	}

	#Вывод страницы
	function getPage(){
		include_once(_MODS_ABS_.'/companies/companies.html');
	}

	#Получаем заголовок страницы
	function getTitle(){
		if (!empty($this->category)) return $this->category['Название'];
		return 'Каталог компаний';
	}

	#Получаем свойства фильтрации
	function getDopProperties(){
		if (!empty($_GET['region'])) $this->region = $this->filterInput($_GET['region']);
		$this->on_page = isset($_GET['on_page']) && is_numeric($_GET['on_page']) ? $_GET['on_page'] : 10;
	}

	#Получаем одну категорию
	function getOneCat(){
		if ( !empty($_GET['action']) && is_numeric($id = $_GET['action']) && ($obj = $this->objects->getFullObject($id)) && ($obj['class_id'] == $this->class_id_cat) ) return $obj;
		return false;
	}

	#Получаем категории 
	function getCats($id){
		return $this->objects->getFullObjectsListByCLass($id,$this->class_id_cat);
	}

	#Получаем компании
	function getCompanies($id = 303){
		if (!empty($this->category)){
			$region = !empty($this->region) ? "AND com.field_139 LIKE '%".$this->region."%'" : "";
			$fields = $this->db->select("fields", "WHERE `class_id` = ".$this->class_id_com." ORDER BY sort");
			$region = !empty($this->region) ? "AND com.field_139 LIKE '%".$this->region."%'" : "";
			$where = "RIGHT JOIN `class_".$this->class_id_com."` as com ON o.id = com.object_id RIGHT JOIN `class_".$this->class_id_prod."` as ads ON o.id = ads.field_161 WHERE ads.field_169 = '".$this->category['id']."' AND o.active = '1' AND com.field_259 = '1' $region GROUP BY ads.field_161 ORDER BY o.sort LIMIT ".$this->pages['start'].", ".$this->pages['on_page'];
			$what = "o.*, com.*, ads.field_161, ads.field_169";
			if (!$coms =  $this->db->select("`objects` as o", $where, $what)) return array();
			$out = array();
			foreach ($coms as $k=>$cat){
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
		}else{
			$region = !empty($this->region) ? "AND c.field_139 LIKE '%".$this->region."%'" : "";
			return $this->objects->getFullObjectsListByCLass($id,$this->class_id_com, "AND o.active = '1' AND c.field_259 = '1' $region ORDER BY o.sort LIMIT ".$this->pages['start'].", ".$this->pages['on_page']);
		}
	}

	#ПОлучаем колличество компаний 
	function getComsCount($id = 303){
		if (!empty($this->category)){
			$region = !empty($this->region) ? "AND com.field_139 LIKE '%".$this->region."%'" : "";
			$where = "RIGHT JOIN `class_".$this->class_id_com."` as com ON o.id = com.object_id RIGHT JOIN `class_".$this->class_id_prod."` as ads ON o.id = ads.field_161 WHERE ads.field_169 = '".$this->category['id']."' AND o.active = '1' AND com.field_259 = '1' $region GROUP BY ads.field_161";
			$arr = $this->db->select("`objects` as o", $where, "COUNT(*) as count");
			if (isset($arr[0]['count'])) return $arr[0]['count'];
			else return '0';
		}else{
			$region = !empty($this->region) ? "AND c.field_139 LIKE '%".$this->region."%'" : "";
			return $this->objects->getObjectsCount($id, $this->class_id_com, "AND o.active = '1' AND c.field_259 = '1' $region");
		}
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

	#Получаем колличество компаний по категории
	function getComsCountByCat($cat_id){
		$where = "RIGHT JOIN `class_".$this->class_id_com."` as com ON o.id = com.object_id RIGHT JOIN `class_".$this->class_id_prod."` as ads ON o.id = ads.field_161 WHERE ads.field_169 = '$cat_id' AND o.active = '1' GROUP BY ads.field_161";
		$arr =  $this->db->select("`objects` as o",$where, "COUNT(*) as count");
		if (isset($arr[0]['count'])) return $arr[0]['count'];
		else return '0';
	}

	#Получаем страницы для пагинации
	function getPaginationPages(){
		$count = $this->getComsCount();
		if(!isset($_REQUEST['pg']) || !is_numeric($current_page = $_REQUEST['pg']) || ($current_page>ceil($count/$this->on_page))) $current_page = 1;
		return $this->objects->pagination($count, $current_page, $this->on_page);
	}

	#Ссылка пагинации
	function pgHref($i,$type = ''){
		$link = isset($_GET['region']) ? $_SERVER['REDIRECT_URL']."?region=".$this->region."&on_page=".$this->on_page."&pg=" : $_SERVER['REDIRECT_URL']."?pg=";
		switch ($type){
			case 'next' :
				return 'href="'.$link.intval($i+1).'"';
			break;
			case 'prev' :
				return 'href="'.$link.intval($i-1).'"';
			break;
			default:
				return 'href="'.$link.intval($i).'"';
			break;
		}
	}



}?>