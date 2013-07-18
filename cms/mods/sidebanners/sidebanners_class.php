<?class Sidebanners extends api{
	
	function __construct(){
	      parent::__construct();
	}
	
	# DB ЗАПРОСЫ
	
	function sidebanners($num=0,$id=array()){
		# $num - колчиество, 0 - показывать все
		# $id - список ID баннеров, которые нужно показывать
		#
		$out = '<div class="banner-w240"><a href="#"><img src="/img/dumm/banner-1.png" alt="" /></a></div>
        <div class="banner-w240"><a href="#"><img src="/img/dumm/banner-2.png" alt="" /></a></div>
        <div class="banner-w240"><a href="#"><img src="/img/dumm/banner-3.png" alt="" /></a></div>';
		
		
		return $out;
	}

	function _getData($id){
		$a=$this->objects->getFullObject($id);
		return $a;
	}
	
}?>