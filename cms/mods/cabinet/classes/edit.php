<?class edit extends api{

public $title,$tpl;
public $msg, $object, $class_id, $ads_cls, $add_cls, $types, $root_id, $class_id_c, $conditions, $marks, $money, $is_got, $parents;

	function __construct(){
		parent::__construct();
		$this->title = 'Редактирование объявления';
		$this->tpl = '/ad_edit.html';
		$this->getTemplate();
		$this->class_id = 31;
		$this->root_id = 228;
		$this->class_id_c = 30;
		$this->msg = '';
		$this->object = $this->getAdObject();
		$this->types = $this->objects->getFieldP3(100,$this->class_id);
		$this->conditions = $this->objects->getFieldP3(108,$this->class_id);
		$this->money = $this->objects->getFieldP3(116,$this->class_id);
		$this->is_got = $this->objects->getFieldP3(117,$this->class_id);
		$this->marks = $this->objects->getFullObjectsList(255);
		$this->parents = array_reverse($this->getParents($this->object['id'],0));
		// $this->getAdClasses();
		if ($_SESSION['u']['role']=='user')
			$this->editAdForUser();
		else
			$this->editAd();
	}

	#Вставляем шаблон взависимости роли пользователя
	function getTemplate(){
		switch ($_SESSION['u']['role']){
			case 'user':
				$this->tpl = '/ad_edit_user.html';
			break;
			case 'company':
				$this->tpl = '/ad_edit.html';
			break;
			case 'business':
				$this->tpl = '/ad_edit.html';
			break;
			default:
				$this->tpl = '/empty.html';
			break;
		}
	}

	#Получаем объект объявления
	function getAdObject(){
		if (empty($_GET['id']) || !is_numeric($id = $_GET['id']) || (!$object = $this->objects->getFullObject($id)) || ($object['class_id']!= $this->class_id) ) header("location: /404");
		return $object;
	}

	#Подключаем дополнительные классы
	function getAdClasses(){
		include_once (_MODS_ABS_.'/cabinet/classes/ads.php');
		$this->ads_cls = new ads();
		include_once (_MODS_ABS_.'/cabinet/classes/add.php');
		$this->add_cls = new add();
	}

	#Статус объявления
	function getStatus($ad){
		if (!is_array($ad)) return false;
		$out = array();
		switch ($ad['Статус']){
			case '3':
				$out['icon'] = 'icon-remove icon-grey';
				$out['color'] = 'orange';
				$out['text'] = 'Отклонено';
				$out['desc'] = 'Ваше объявление было отклонено модератором.';
			break;
			case '2': 
				$out['icon'] = 'icon-warning-sign icon-yellow';
				$out['color'] = 'orange';
				$out['text'] = 'В архиве';
				$out['desc'] = 'Объявление в архиве';
			break;
			case '1':
				$out['icon'] = 'icon-ok icon-grey';
				$out['color'] = 'orange';
				$out['text'] = 'Активно';
				$out['desc'] = 'Активно';
			break;
			default:
				$out['icon'] = 'icon-warning-sign icon-yellow';
				$out['color'] = 'orange';
				$out['text'] = 'На модерации';
				$out['desc'] = 'В течении 1-2 дней информация будет проверена модератором. О результатах модерации вы получите уведомление по электронной почте.';
			break;
		}
		return $out;
	}

	#Список годов
	function yearsList($count = 100){
		$begin = (int)date('Y');
		$end = (int)date('Y') - $count;
		$out = array();
		for ($i = $begin; $i>($end+1); $i--){
			$out[] = $i;
		}
		return $out;
	}

	#Изменить объявление
	function editAd(){
		if($this->object['user_id'] != $_SESSION['u']['id']) header("location: /404");
		if ($this->object['active']==0) header("location: /cabinet/ads");
		if (empty($_POST)) {return false;}
		if (isset($_POST['archivate']) && ($_POST['archivate'] == 1) && ($this->archivate($this->object))) {
			header("location: ".$_SERVER['REQUEST_URI']);
		}elseif (isset($_POST['delete']) && ($_POST['delete'] == 1) && ($this->delete($this->object))) {
			header("location: ".$_SERVER['REQUEST_URI']);
		}elseif (isset($_POST['restore']) && ($_POST['restore'] == 1) && ($this->restore($this->object))) {
			header("location: ".$_SERVER['REQUEST_URI']);
		}elseif( isset($_POST['rise']) && ($this->riseAd($this->object['id']))){
			$this->msg = 'Объявления поднято';
			return true;
		}elseif ( isset($_POST['highlight']) && isset($_POST['HighlightColor']) && ($color = $this->getHighLightColor($_POST['HighlightColor'])) && ($this->changeColor($this->object['id'],$color)) ){
			$this->msg = 'Обявление выделено цветом';
			return true;
		}elseif ( isset($_POST['hot']) && ($_POST['hot']==1) && ($this->hotAd($this->object['id'])) ){
			$this->msg = 'Объявление сделано горячим предложением';
			return true;
		}else{
			if (!isset($_POST['adType']) || !is_numeric($adType = $_POST['adType'])) {$this->msg = 'Отсутсвует тип'; return false;}
			if (!isset($_POST['adCategory']) || !is_numeric($adCategory = $_POST['adCategory'])) {$this->msg = 'Не выбран раздел'; return false;}
			if (empty($_POST['productCategory']) || !is_numeric($productCategory = $_POST['productCategory'])) {$this->msg = 'не выбрана категория'; return false;}
			if (empty($_POST['f']) || !is_array($f = $_POST['f'])) {$this->msg = 'заполнена не вся инфа'; return false;};
			$img = !empty($_POST['mainPhoto']) ? $this->filterInput($_POST['mainPhoto']) : '';
			$price = isset($f['Цена']) ? $this->filterInput($f['Цена']) : '';
			$parents = array_reverse($this->getParents($productCategory,$adCategory));
			if (!empty($f['priceType']) && ($f['priceType']==1)){
				$price = '';
			}
			$object = array(
				"active"=>1,
				"head"=>$productCategory,
				"id"=>$this->object['id'],
				"name"=>$this->filterInput($f['Название']),
				"class_id"=>$this->class_id,
				"sort"=>time()
			);
			$fields = array(
				161 => $_SESSION['u']['id'],
				102 => 0, // Статус. Ставим на модерации
				104 => date("Y-m-d"), // Дата обновления 
				100 => $adType, // Тип объявления
				107 => $this->filterInput($f['Название']), // Название 
				108 => (int)$f['Состояние товара'], // Состояние товара 
				109 => (!empty($_POST['selfmark'])?$this->filterInput($_POST['selfmark']):$this->filterInput($f['Марка'])), // Марка 
				110 => $this->filterInput($f['Модель']), // Модель
				111 => $this->filterInput($f['Страна производитель']), // Страна производитель
				112 => (int)$f['Год'], // Год
				113 => $this->filterInput($f['Описание товара']), // Описание товара
				114 => $this->filterInput($f['Технические характеристики']), // Технические характеристики
				115 => $price,
				116 => (int)$f['Валюта'],
				117 => (int)$f['Наличие товара'],
				118 => $this->filterInput($f['Местоположение товара']),
				119 => $this->filterInput($f['Поставщик оборудования']),
				120 => $this->filterInput($f['Сайт поставщика']),
				163 => $img,
				167 => (int)$adCategory,
				169 => (int)$parents[0]['id'],
			);
			if ($this->objects->editObjectAndFields($object,$fields)){
				if (!file_exists("cms/uploads/".$img))
					rename('cms/uploads_temp/'.$img, 'cms/uploads/'.$img);
				$this->msg = 'Изменено';
				#Создаем доп фото
				if (!empty($_POST['photo']) && is_array($photos = $_POST['photo'])){
					#Удаляем лишние доп фото
					if ($dop_imgs = $this->getDopPhotos($this->object['id'])){
						$di_array = array();
						foreach ($dop_imgs as $di){
							if (!in_array($di['Ссылка'],$photos)) $this->deleteDopPhoto($di['id'],$di['Ссылка']);
						}
					}
					$i = 1;
					foreach ($photos as $p){
						if ($p == $img) continue;
						if (file_exists("cms/uploads/".$p)) continue;
						$obj_photo = array(
							"active"=>1,
							"head"=>$this->object['id'],
							"name"=>$this->filterInput($f['Название']).'_'.$i++,
							"class_id"=>4,
							"sort"=>time()
						);
						$fields_photo = array(
							8 => $this->filterInput($f['Название']).'_'.$i++,
							9 => $p,
						);
						if ($this->objects->createObjectAndFields($obj_photo,$fields_photo)){
							rename('cms/uploads_temp/'.$p, 'cms/uploads/'.$p);
						}
					} 
				}
				header("location: /cabinet/ads");
				return true;
			}else return false;
		}
	}

	#Редактирование объявления для пользователя
	function editAdForUser(){
		if($this->object['user_id'] != $_SESSION['u']['id']) header("location: /404");
		if ($this->object['active']==0) header("location: /cabinet/ads");
		if (empty($_POST)) {return false;}
		if (isset($_POST['archivate']) && ($_POST['archivate'] == 1) && ($this->archivate($this->object))) {
			header("location: ".$_SERVER['REQUEST_URI']);
		}elseif (isset($_POST['delete']) && ($_POST['delete'] == 1) && ($this->delete($this->object))) {
			header("location: ".$_SERVER['REQUEST_URI']);
		}elseif (isset($_POST['restore']) && ($_POST['restore'] == 1) && ($this->restore($this->object))) {
			header("location: ".$_SERVER['REQUEST_URI']);
		}
		if (!isset($_POST['adType']) || !is_numeric($adType = $_POST['adType'])) {$this->msg = 'Отсутсвует тип'; return false;}
		if (!isset($_POST['adCategory']) || !is_numeric($adCategory = $_POST['adCategory'])) {$this->msg = 'Не выбран раздел'; return false;}
		if (empty($_POST['productCategory']) || !is_numeric($productCategory = $_POST['productCategory'])) {$this->msg = 'не выбрана категория'; return false;}
		if (empty($_POST['f']) || !is_array($f = $_POST['f'])) {$this->msg = 'заполнена не вся инфа'; return false;};
		$img = !empty($_POST['mainPhoto']) ? $this->filterInput($_POST['mainPhoto']) : '';
		$price = isset($f['Цена']) ? $this->filterInput($f['Цена']) : '';
		$parents = array_reverse($this->getParents($productCategory,$adCategory));
		if (!empty($f['priceType']) && ($f['priceType']==1)){
			$price = '';
		}
		$object = array(
			"active"=>1,
			"head"=>$productCategory,
			"id"=>$this->object['id'],
			"name"=>$this->filterInput($f['Название']),
			"class_id"=>$this->class_id,
			"sort"=>time()
		);
		$fields = array(
			161 => $_SESSION['u']['id'],
			102 => 0, // Статус. Ставим на модерации
			104 => date("Y-m-d"), // Дата обновления 
			100 => $adType, // Тип объявления
			107 => $this->filterInput($f['Название']), // Название 
			108 => (int)$f['Состояние товара'], // Состояние товара
			109 => (!empty($_POST['selfmark'])?$this->filterInput($_POST['selfmark']):$this->filterInput($f['Марка'])), // Марка 
			110 => $this->filterInput($f['Модель']), // Модель
			111 => $this->filterInput($f['Страна производитель']), // Страна производитель
			112 => (int)$f['Год'], // Год
                        113 => $this->filterInput($f['Описание товара']), // Описание товара
			115 => $price,
			116 => (int)$f['Валюта'],
			//117 => (int)$f['Наличие товара'],
			118 => $this->filterInput($f['Местоположение товара']),
			163 => $img,
			167 => (int)$adCategory,
			169 => (int)$parents[0]['id'],
		);
		if ($this->objects->editObjectAndFields($object,$fields)){
			if (!file_exists("cms/uploads/".$img))
				rename('cms/uploads_temp/'.$img, 'cms/uploads/'.$img);
			$this->msg = 'Изменено';
			#Создаем доп фото
			if (!empty($_POST['photo']) && is_array($photos = $_POST['photo'])){
				#Удаляем лишние доп фото
				if ($dop_imgs = $this->getDopPhotos($this->object['id'])){
					$di_array = array();
					foreach ($dop_imgs as $di){
						if (!in_array($di['Ссылка'],$photos)) $this->deleteDopPhoto($di['id'],$di['Ссылка']);
					}
				}
				$i = 1;
				foreach ($photos as $kp=>$p){
					if ($p == $img) continue;
					if (file_exists("cms/uploads/".$p)) continue;
					$obj_photo = array(
						"active"=>1,
						"head"=>$this->object['id'],
						"name"=>$this->filterInput($f['Название']).'_'.$i++,
						"class_id"=>4,
						"sort"=>time()
					);
					$fields_photo = array(
						8 => $this->filterInput($f['Название']).'_'.$i++,
						9 => $p,
					);
					if ($this->objects->createObjectAndFields($obj_photo,$fields_photo)){
						rename('cms/uploads_temp/'.$p, 'cms/uploads/'.$p);
					}
					if ($kp == 5) break;
				} 
			}
			header("location: /cabinet/ads");
			return true;
		}else return false;
	}

	#Переносим объявление в архив
	function archivate($ad){
		if (!is_array($ad)) return false;
		if ($this->db->update("class_".$this->class_id, array("field_102" => "2"), "WHERE `object_id` = ".$ad['id']." AND `lang`='".$this->lang."'"))
			$out ='Добавлено';
		else $out = 'Не добалено';
		return $out;
	}

	#Удаляем объявление
	function delete($ad){
		if (!is_array($ad)) return false;
		if ($this->db->update("objects", array("active" => "0"), "WHERE `id` = '".$ad['id']."' "))
			$out ='Удалено';
		else $out = 'Не удалено';
		return $out;
	} 

	#Востановление из архива
	function restore($ad){
		if (!is_array($ad)) return false;
		if ($this->db->update("class_".$this->class_id, array("field_102" => "0"), "WHERE `object_id` = ".$ad['id']." AND `lang`='".$this->lang."'"))
			$out ='Востановлено';
		else $out = 'Не востановлено';
		return $out;
	}

	#Получаем категории товара
	function getCategories(){
		$out = array();
		if ($parents = array_reverse($this->getParents($this->object['id'], $this->object['razdel_id']))){
			foreach ($parents as $p){
				if ($p['class_id']==30){
					if ($p['id']== $this->object['head'])
						$out[] = '<span class="obj">'.$p['Название'].'</span>';
					else $out[] = '<span>'.$p['Название'].'</span>';
				}
			}
		}
		return join("",$out);
	}

	#Получаем категории с вложенными списками
	function getCategoriesList($id){
		if (!$list = $this->objects->getFullObjectsListByClass($id,$this->class_id_c)) return array();
		$out = array();
		foreach ($list as $o){
			$out[$o['id']] = $o;
			// if (in_array($o,$this->parents)) $out = $this->getCategoriesList($o['id']) + $out;
			foreach ($this->parents as $p){
				if ($p['id']==$o['id']) $out = $this->getCategoriesList($o['id']) + $out;
			}
		}
		return $out;
	}

	#Получаем детей родителей данного объявления
	function getCategoriesListByParents(){
		if ((!$list = $this->getCategoriesList($this->object['razdel_id'])) || empty($list) || empty($this->parents) || !is_array($this->parents)) return false;
		$out = array();
		foreach ($this->parents as $k=>$p){
			$out[$k] = array();
			foreach ($list as $o){
				if ($o['head']==$p['id']) $out[$k][] = $o;
			}
		}
		return $out;
	}

	#Получаем id из массивов 
	function getObjectsIds($arr){
		if (empty($arr) || !is_array($arr)) return false;
		$out = array();
		foreach ($arr as $o){
			if (!isset($o['id'])) continue;
			$out[] = $o['id'];
		}
		return $out;
	}

	#Получаем до изображения 
	function getDopPhotos($id){
		if (!$list = $this->objects->getFullObjectsListByCLass($id,4)) return false;
		else return $list;
	}


	#Удаление доп фото
	function deleteDopPhoto($id,$filename){
		if (($this->objects->deleteObject($id)) && unlink('cms/uploads/'.$filename)) return true;
		else return false;
	}

	#Изменеям цвет объявлений
	function changeColor($id,$color){
		if (!is_numeric($id) || !is_numeric($color)) return false;
		if ($_SESSION['u']['credits'] < 200) return false;
		if (!$this->objects->editObjectFields($id,array(106=>$color,104=>date("Y-m-d")))) return false;
		$count = intval($_SESSION['u']['credits']) - 200;
		$this->saveTransaction(200, "Изменение цвета объявления № $id");
		$this->changeCredits($count);
		return true;
	}

	#Поднять объявление
	function riseAd($id){
		if ($_SESSION['u']['credits'] < 100) return false;
		if (!$this->objects->editObjectFields($id,array(104=>date("Y-m-d")))) return false;
		$count = intval($_SESSION['u']['credits']) - 100;
		$this->changeCredits($count);
		$this->saveTransaction(100, "Поднятие объявления № $id");
		return true;
	}

	#Сделать горячим объявлением
	function hotAd($id){
		if ($_SESSION['u']['credits'] < 300) return false;
		if (!$this->objects->editObjectFields($id,array(211=>'1',104=>date("Y-m-d")))) return false;
		$count = intval($_SESSION['u']['credits']) - 300;
		$this->changeCredits($count);
		$this->saveTransaction(300, "Сделать горячим объявление № $id");
		return true;
	}

	#Изменяем колличество кредитов
	function changeCredits($count){
		if (!is_numeric($count)) return false;
		if ($_SESSION['u']['role'] == 'user'){
			if ($this->objects->editObjectFields($_SESSION['u']['id'],array(155=>$count))){
				$_SESSION['u']['credits'] = $count;
			}
		}else{
			if ($this->objects->editObjectFields($_SESSION['u']['id'],array(171=>$count))){
				$_SESSION['u']['credits'] = $count;
			}
		}
	}

	#Сохраняем транзакцию
	function saveTransaction($summ, $type = ''){
		if (empty($summ)) return false;
		include_once(_PUBLIC_ABS_.'/transactions.php');
		$trans = new transactions();
		$from = $_SESSION['u']['id'];
		$to = 0;
		$date = time();
		$invoice_id = 0;
		if (!$trans->saveData($from, $to, $summ, $type, $invoice_id)) return false;
		return true;
	}

	#Получить цвет объялвения
	function getHighLightColor($color){
		switch ($color){
			case 'green':
				return 1;
			break;
			case 'yellow':
				return 2;
			break;
			case 'blue':
				return 3;
			break;
			default:
				return 0;
			break;
		}
	}

}?>
