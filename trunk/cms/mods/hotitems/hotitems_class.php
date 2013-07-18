<?class Hotitems extends api{
	
	public $type;

	function __construct(){
	      parent::__construct();
	      $this->type = $this->getAdType();
	}
	
	# DB ЗАПРОСЫ
	
	function showHotItems($num){
		ob_start();
		$title = '<a href="/" class="bd-beige">Горячие предложения:</a>';
		
		#
		# Здесь выводим горячие предложения!
		#
		
		include('hotitems.html');
		$out = ob_get_contents();	    
		ob_end_clean();
		return $out;
	}

	function _getData($id){
		$a=$this->objects->getFullObject($id);
		return $a;
	}

	#Получаем Тип объявления
	function getAdType(){
		if (!isset($_GET['mod']) || (!$type = $_GET['mod'])) return '0';
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
	
}?>