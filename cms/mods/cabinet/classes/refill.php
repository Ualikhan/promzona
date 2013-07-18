<?class refill extends api{

public $title,$tpl;
public $summ, $price;

	function __construct(){
		parent::__construct();
		$this->title = 'Пополнение баланса';
		$this->price = 1;
		$this->getTemplate();
	}

	#Вставляем шаблон взависимости роли пользователя
	function getTemplate(){
		if (isset($_POST['summ']) && is_numeric($summ = str_replace(" ", "", $_POST['summ'])) ){
			$this->tpl = '/bill.html';
			$this->summ = intval($summ) * intval($this->price);
			if (isset($_POST['name']) && ($name = $_POST['name']) && (($name == 'bank'))){
					$this->tpl = '/bill_forming.html';
			}elseif ( isset($_POST['name']) && ($name = $_POST['name']) && (($name == 'qiwi')) ){
				$this->tpl = '/bill_forming_qiwi.html';
			}
			return false;
		}
		switch ($_SESSION['u']['role']){
			// case 'user':
			// 	$this->tpl = '/company_info_create.html';
			// break;
			// case 'company':
			// 	$this->tpl = '/company_info.html';
			// break;
			case 'business':
				$this->tpl = '/refill.html';
			break;
			default:
				$this->tpl = '/empty.html';
			break;
		}
	}

}?>