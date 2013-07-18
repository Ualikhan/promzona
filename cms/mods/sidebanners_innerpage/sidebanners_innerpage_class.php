<?class Sidebanners_innerpage extends api{
	
	function __construct(){
	      parent::__construct();
	}
	
	# DB ЗАПРОСЫ
	
	function sidebanners($num=0,$id=array()){
		# $num - колчиество, 0 - показывать все
		# $id - список ID баннеров, которые нужно показывать
		#
		$out = '<div class="banner-w240"><a href="#"><img src="/img/promzona.gif" alt="" /></a></div>';
		$out .= '<div class="banner-w240"><a href="#"><img src="/img/dumm/banner-2.png" alt="" /></a></div>';
		
		
		return $out;
	}

	function _getData($id){
		$a=$this->objects->getFullObject($id);
		return $a;
	}
	
}?>