<?class business extends api{

public $title,$tpl;
public $month, $summ, $price;

	function __construct(){
		parent::__construct();
		$this->price = 1000;
		$this->title = 'Пакет "Бизнес"';
		$this->getTemplate();
	}

	#Вставляем шаблон взависимости от роли пользователя
	function getTemplate(){
		if (isset($_GET['month']) && is_numeric($month=$_GET['month'])){
			$this->tpl = '/bill.html';
			$this->title = "Платный пакет «Бизнес» на $month ".$this->declinationMonths($month);
			$this->month = $month;
			$this->summ = intval($month) * intval($this->price);
			if (isset($_POST['name']) && ($name = $_POST['name']) && (($name == 'bank'))){
					$this->tpl = '/bill_forming.html';
			}
			return false;
		}
		switch ($_SESSION['u']['role']){
			// case 'user':
			// 	$this->tpl = '/company_info_create.html';
			// break;
			case 'company':
				$this->tpl = '/package_business.html';
			break;
			case 'business':
				$this->tpl = '/package_business.html';
			break;
			default:
				$this->tpl = '/empty.html';
			break;
		}
	}

	#Склоняем месяцы
	function declinationMonths($count){
		if ($count == 1) return 'месяц';
		elseif (($count <= 4)&&($count>1)) return 'месяца';
		elseif (($count>4) ) return 'месяцев';
	}

}?>