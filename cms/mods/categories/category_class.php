<?class Categories extends api{

public $root_id, $class_id, $ad_class_id, $title;
	
	function __construct(){
		parent::__construct();
		$this->root_id = 228;
		$this->class_id = 30;
		$this->ad_class_id = 31;
	}
	
	#Получаем основные разделы
	function getRoots(){
		if (!$list = $this->objects->getFullObjectsListByClass($this->root_id,$this->class_id, "AND o.active = '1' AND c.field_159 = '0'")) return false;
		return $list;
	}

	#Колличество объявлений по типу
	function adsCountByType($id, $type, $razdel_id = 258){
		if (!is_numeric($type)) return false;
		return $this->objects->getObjectsCount($id, $this->ad_class_id, "AND o.active='1' AND c.field_102 = '1' AND c.field_100 = '$type' AND c.field_167 = '$razdel_id'");
	}

	#Получаем тип по моду
	function getTypeByMod($mod){
		switch($mod){
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

	#Колличество объявлений по типу в подкаталогах
	function adsCountInSubCats($cats, $type = '0'){
		if (!is_array($cats)) return 0;
		$heads = array();
		$sum = 0;
		foreach ($cats as $c){
			$heads[] = $c['id'];
			if ($subcats = $this->objects->getFullObjectsListByClass($c['id'],$this->class_id)){
				$sum += $this->adsCountInSubCats($subcats,$type);
			}
		}
		if ($counts = $this->db->select("objects as o", "LEFT JOIN `class_".$this->ad_class_id."` as c ON o.id=c.object_id WHERE o.head IN (".join(", ",$heads).") AND c.field_102 = '1' AND c.field_100 = '$type'", "COUNT(*) as counter")){
			foreach ($counts as $i){
				$sum += (int)$i['counter'];
			}
		}
		return $sum;
	}

	#Получаем объекты каталогов без полей
	function getSubCatsCount($id,$type){
		return $this->db->count("`objects` as o", "LEFT JOIN `class_".$this->ad_class_id."` as c ON o.id=c.object_id WHERE c.lang='ru' AND o.head='$id' AND o.active = '1' AND c.field_100 = '$type' AND c.field_102 = '1'");
	}

	#Получаем иконку aka классы Снипетов
	function getIconByName($name){
            $name = trim($name);
            $name = trim($name,'&nbsp;');
		$sprites = array(
			'Автобусы'=>'bus',
			'Грузовой автотранспорт'=>'truck',
			'Дорожная техника'=>'paver',
			'Карьерная и горнодобывающая техника'=>'quarry-truck',
			'Коммунальные машины'=>'garbage-truck',
			'Конвейеры, транспортеры'=>'conveyor',
			'Краны и грузоподъемные машины'=>'crane',
			'Лесозаготовительная техника'=>'forestry',
			'Лифты, подъемники, эскалаторы'=>'escalator',
			'Машины для земляных работ'=>'excavator',
			'Сельскохозяйственная техника'=>'tractor',
			'Складская техника'=>'hoist',
			'Техника для работы с бетоном'=>'truck-mixer',
			'Оборудование для бытового обслуживания'=>'brush',
			'Оборудование для казино, баров, дискотек'=>'chip',
			'Деревообрабатывающее оборудование'=>'saw',
			'Медицинское оборудование'=>'medkit',
			'Оборудование для легкой промышленности'=>'sewing-machine',
			'Оборудование для переработки отходов'=>'recycle',
			'Пищевое оборудование'=>'spoon-fork',
			'С/х оборудование и лесотехника'=>'carrot',
			'Полиграфическое оборудование'=>'printer',
			'Оборудование для производства бумаги и картона'=>'blank-list',
			'Оборудование для производства строительных материалов'=>'bricks',
			'Оборудование для АЗС и автосервиса'=>'fuel-dispenser',
			'Складское оборудование'=>'microscope',
			'Холодильное и климатическое оборудование'=>'warehouse',
			'Строительное оборудование'=>'fridge',
			'Оборудование для производства напитков и табака'=>'pipes',
			'Торговое оборудование'=>'wine',
			'Оборудование для химической промышленности'=>'teller',
			'Нефтегазовое и торфяное оборудование'=>'bulb',
			'Электрооборудование'=>'oil-derrick',
			'Оргтехника и оборудование для офиса'=>'oil-electricity',
			'Прочее оборудование'=>'phone',
			'Землеройные машины и техника'=>'excavation',
			'Деревообрабатывающее оборудование'=>'woodworking',
			'Лабороторное, аналитическое и КИП оборудование'=>'lab-equip',
			'Оборудование сферы услуг  и развлечения'=>'entertainment',
			'Медицинское и фармацевтическое оборудование'=>'med-equip',
			'Оборудование для производства алкогольных и безалкогольных напитков'=>'alcohol-equip',
			'Оборудование для очистки воды и переработки отходов'=>'waterclean-equip',
			'Упаковочное оборудование'=>'packing-equip',
			'Оборудование для производства бытовой химии и средств гигиены'=>'houshold-equip',
			'Сельхозоборудование и лесотехника'=>'agricultural-equip',
			'Нефтегазовое и нефтехимическое оборудование'=>'oil-equip',
			'Оборудование для производства бумаги, картона, изделии из бумаги'=>'paper-equip',
			'Телекоммуникационное и радиооборудование'=>'radio-equip',
			'Оборудование для производства и обработки металлов'=>'metal-equip',
			'Другое промышленное оборудование'=>'other-equip',
			'Полимерное оборудование'=>'polymeric-equip',
		);
		if (isset($sprites[$name])) return $sprites[$name];
	}

	#Для ajax запроса поиска
	function ajax(){
		$out = array();
		if ( !empty($_GET['section']) && ($id=$_GET['section']) && ($list = $this->objects->getFullObjectsListByClass($id,$this->class_id)) ){
			foreach ($list as $o){
				$out[$o['id']]['name'] = $o['Название'];
				$out[$o['id']]['inner'] = array();
				// if ($sublist = $this->objects->getFullObjectsListByClass($o['id'],$this->class_id)){
					// foreach ($sublist as $i){
						// $out[$o['id']]['inner'][$i['id']] = $i['Название'];
					// }
				// }
			}
			exit(json_encode($out));
		}else{
			if (!$roots = $this->objects->getFullObjectsListByClass($this->root_id,$this->class_id)) return false;
			foreach ($roots as $r){
				if (!$list = $this->objects->getFullObjectsListByCLass($r['id'],$this->class_id)) continue;
				foreach ($list as $o){
					$out[$o['id']]['name'] = $o['Название'];
					$out[$o['id']]['inner'] = array();
					// if ($sublist = $this->objects->getFullObjectsListByClass($o['id'],$this->class_id)){
					// 	foreach ($sublist as $i){
					// 		$out[$o['id']]['inner'][$i['id']] = $i['Название'];
					// 	}
					// }
				}
			}
			exit(json_encode($out));
		}
	}

	
}?>
