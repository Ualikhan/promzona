<?class map extends api{

public $title, $pages;

	function __construct(){
		parent::__construct();
		$this->title = 'Карта сайта';
		$this->pages = $this->getObjects();
	}

	#Вывод страницы
	function getPage(){
		include_once(_MODS_ABS_.'/map/map.html');
	}

	#Получаем объекты разделов сайта
	function getObjects($id=1){
		if (!$list = $this->objects->getFullObjectsList($id)) return false;
		$out = array();
		foreach($list as $o){
			if (($o['class_id']==2)||($o['class_id']==1)){
				$out[] = $o;
			}
			if ($list2 = $this->getObjects($o['id'])){
				$out[] = $list2;
			}
		}
		return $out;
	}

	#Получаем ссылку страницы
	function getPageLink($o){
		if (!is_array($o)) return '#';
		if (empty($o['class_id'])) return '#';
		if ($o['class_id']==2) return 'href="'.$o['Ссылка'].'"';
		if ($o['class_id']==1) return 'href="/page/'.$this->t($o['id']).'.html"';
		return '#';
	}

	#Выводим список Разделов сайта
	function getMapHtml($list){
		$out = array();
		$out[] = '<ul>';
			foreach ($list as $o){
				if (!empty($o['id'])){
					$out[] = '<li><a '.$this->getPageLink($o).'>'.$o['Название'].'</a></li>';
				}else{
					$out[] = $this->getMapHtml($o);
				}
			}
		$out[] = '</ul>';
		return join("\n",$out);
	}

}?>