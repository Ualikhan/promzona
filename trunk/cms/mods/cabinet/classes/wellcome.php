<?class wellcome extends api{

public $title,$object;

	function __construct(){
		parent::__construct();
		$this->title = 'Личный кабинет';
		// $this->object = $this->objects->getFullObject($_SESSION['u']['id']);
	}

	function getObject($subject){
		if ($this->object = $subject) return true;
		else return false;
	}


	#ПОльзователь
	function userName(){
		// if (!empty($_SESSION['u']['fullname']))
		// 	return ($_SESSION['u']['fullname']);
		if ($this->object['Роль'] == 'user'){
			return $this->object['Фио'];
		}else{
			return $this->object['Имя контактного лица'];
		}
	}

	#Статус компании
	function companyStatus(){
		if ($this->object['Роль']=='user') return 'Отсутсвует';
		// if ($this->object['active']!=1) return 'На модерации';
		if ($this->object['Статус'] == 0 ) return 'На модерации';
		elseif ($this->object['Статус'] == 2) return 'Отклонена';
		else return 'Активна';
	}

	#Текст статус компании
	function companyStatusText(){
		return 'В течении 1-2 дней информация будет проверена модератором. О результатах модерации вы получите уведомление по электронной почте.';
	}

	#Дата регистрации
	function regDate(){
		return date('d.m.Y H:i', $this->object['sort']);
	}

	#Заказы в работе
	function ordersInWork(){
		return '0';
	}

	#Колличество объявлений
	function adsCount($class_id = 31){
		$where = "LEFT JOIN `class_$class_id` as c ON o.id=c.object_id WHERE c.lang='ru' AND o.active = '1' AND c.field_161 = '".$this->object['id']."'";
		return $this->db->count("`objects` as o", $where);
	}

	#Колличество новостей компании
	function companyNewsCount($class_id = 8){
		if ($this->object['Роль']=='business') return $this->objects->getObjectsCount($this->object['id'],$class_id);
		else return 0;
	}

	#Завершенные заказы
	function ordersDone(){
		return '0';
	}

}?>