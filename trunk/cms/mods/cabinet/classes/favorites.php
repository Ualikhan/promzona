<?class favorites extends api{

public $title,$tpl;
public $ads, $class_id, $sort, $adsList, $pages, $adsCount, $companies;


	function __construct(){
		parent::__construct();
		$this->title = 'Избранные объявления';
		$this->getTemplate();
		include_once (_MODS_ABS_.'/cabinet/classes/ads.php');
		$this->ads = new ads();
		$this->class_id = $this->ads->class_id;
		$this->sort = $this->ads->sort;
		$this->condition = $this->objects->getFieldP3(108,$this->class_id);
		$this->companies = $this->getCompanies();
		$this->adsCount = $this->getAdsCount();
		$this->pages = $this->getPages();
		$this->adsList = $this->getAdsList($this->pages['start'],$this->pages['on_page']);
		$this->orderCall();
		$this->deleteFavorites();
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
				$this->tpl = '/favorites.html';
			break;
		}
	}

	#Получаем страницы для пагинации
	function getPages(){
		$sql = $this->ads->getFilterSql();
		$count = $this->adsCount;
		if(!isset($_REQUEST['pg']) || !is_numeric($current_page = $_REQUEST['pg']) || $current_page>ceil($count/$this->on_page)) $current_page = 1;
		return $this->objects->pagination($count, $current_page, $this->ads->on_page);
	}

	#Получаем таблицу объявлений объявляения 
	// function getAdsList($start,$limit){
	// 	$sql = $this->ads->getFilterSql();
	// 	if (!$list = $this->objects->getFullObjectsListByCLass(-1,$this->class_id,"AND o.active = '1' AND c.field_161 = '".$this->ads->user['id']."' AND c.field_102 = '2' $sql LIMIT $start, $limit")) return false;
	// 	return $list;
	// }
	#Получаем объекты объявлений
	function getAdsList($start,$limit){
		$fields = $this->db->select("fields", "WHERE `class_id` = ".$this->class_id." ORDER BY sort");
		$user_id = $_SESSION['u']['id'];
		$sql = $this->ads->getFilterSql();
		$ads = $this->db->select("favorites as f", "LEFT JOIN objects as o ON o.id = f.ads_id LEFT JOIN class_".$this->class_id." as c ON c.object_id = o.id WHERE f.user_id = '$user_id' AND o.active = '1' AND c.field_102 = '1' $sql LIMIT $start, $limit");
		$out = array();
		foreach ($ads as $k=>$ad){
			$out[$k] = array();
			foreach ($ad as $key=>$i){
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

	#ССылка сортировки
	function sortHref($str){
		return $this->ads->sortHref($str);
	}

	#Общее колличество объявлений 
	function getAdsCount(){
		$user_id = $_SESSION['u']['id'];
		return $this->db->count("favorites", "WHERE `user_id`='$user_id'");
	}

	#колличество объявлений по типу
	function getAdsCountByType($i){
		if (!is_numeric($i)) return '0';
		$user_id = $_SESSION['u']['id'];
		return $this->db->count("favorites as f", "LEFT JOIN objects as o ON o.id = f.ads_id LEFT JOIN class_".$this->class_id." as c ON c.object_id = o.id WHERE f.user_id = '$user_id' AND o.active = '1' AND c.field_102 = '1' AND c.field_100 = '$i'");
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

	#Телефон объявления
	function companyTel($id){
		if (empty($this->companies[$id]['Телефон'])) return false;
		$telephone = explode("\n",$this->companies[$id]['Телефон']);
		return $telephone[0];
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

	#Удаляем объявления из избранного
	function deleteFavorites(){
		if (empty($_POST['ad']) || !is_array($ads = $_POST['ad'])) return false;
		$ads_ids = array();
		foreach ($ads as $k=>$ad){
			if (!is_numeric($k)) continue;
			$ads_ids[] = $k;
		}
		$where = "WHERE `user_id` = '".$_SESSION['u']['id']."' AND `ads_id` IN ( ".join(", ", $ads_ids)." )";
		if ($this->db->delete("favorites", $where)) {
			foreach ($ads_ids as $id){
				unset($_SESSION['u']['favorites'][$id]);
			}
			header("location:".$_SERVER['REQUEST_URI']);
		}
	}


}?>