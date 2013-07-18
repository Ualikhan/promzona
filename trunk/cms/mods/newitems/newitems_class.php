<?class Newitems extends api{
	
	function __construct(){
	      parent::__construct();
	}
	
	# DB ЗАПРОСЫ
	
	function showNewItems($num){
		ob_start();
		$title = 'Новые объявления:';
		
		#
		# Здесь выводим новые предложения!
		#
		
		include('newitems.html');
		$out = ob_get_contents();	    
		ob_end_clean();
		return $out;
	}

	function _getData($id){
		$a=$this->objects->getFullObject($id);
		return $a;
	}
	
}?>