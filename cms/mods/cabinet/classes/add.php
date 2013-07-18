<?class add extends api{

public $title,$tpl,$msg;

	function __construct(){
		parent::__construct();
		$this->title = 'Размещение объявления';
		$this->getTemplate();
		$this->msg = '';
		if ($_SESSION['u']['role']=='user')
			$this->addAdForUser();
		else
			$this->addAd();
	}

	#Вставляем шаблон взависимости роли пользователя
	function getTemplate(){
		switch ($_SESSION['u']['role']){
			case 'user':
				$this->tpl = '/add_user.html';
			break;
			case 'company':
				$this->tpl = '/add.html';
			break;
			case 'business':
				$this->tpl = '/add.html';
			break;
			default:
				$this->tpl = '/empty.html';
			break;
		}
	}

	#Виды объявлений
	function adsType(){
		$out = array();
		if ($selected = $this->db->select("fields","WHERE `id`=100 LIMIT 1","p3")){
			$list = explode("\n",$selected);
			foreach ($list as $k=>$o){
				$out[] = '
					<label class="radio mr-20"><input name="adType" type="radio" value="'.$k.'">'.$o.'</label>
				';
			}
		}
		return join("\n",$out);
	}

	#Раздел размешения объявления
	function rootCatsList($id = 228,$class = 30){
		$out = array();
		if ($list = $this->objects->getFullObjectsListByClass($id,$class)){
			foreach ($list as $o){
				if ($o['Бизнес пакет']==0){ 
					$out[] = '
						<div class="mb-5"><label class="radio"><input name="adCategory" data-root-id="'.$o['id'].'" type="radio" value="'.$o['id'].'">'.$o['Название'].'</label></div>
				';
				}else{ 
					if ($_SESSION['u']['role']=='user') $out[] = '';
					else
					$out[] = '
						<div class="mb-5"><label class="radio disabled-el"><input name="adCategory" disabled type="radio">'.$o['Название'].'</label> <span class="ml-10">(доступно только обладателям пакета <a class="bd-beige bold psevdo" href="#">«<span>Бизнес</span>»</a>)</span></div>
					';
				}
			}
		}
		return join("\n",$out);
	}

	#Список категорий (//со вложенностями) без вложенности
	function getCatsList($id){
		if ($list = $this->objects->getFullObjectsListByClass($id,30)){
			$out = array();
			foreach ($list as $o){
				$status = $this->objects->getObjectsListByClass($o['id'],30) ? false : true;
				$out[$o['id']] = array(
					"name"=>$o['Название'],
					// "object"=>$this->getCatsList($o['id'])
					"object"=> $status
				);
			}
			return $out;
		}else {
			return false;
		}
	}

	#Возвращаем json список
	function getJsonCatsList($id){
		if (is_numeric($id))
			return json_encode($this->getCatsList($id));
	}

	#Поиск категории
	function searchCat($word = '',$root_id = 228){
		$out = array();
		$fields = $this->objects->getClassFields(30);
		foreach ($fields as $f){
			if ($selected = $this->db->select("class_30","WHERE `field_".$f['id']."` like '%$word%'")){
				foreach ($selected as $s){
					if (!in_array($s,$out) && (!$this->objects->getObjectsListByClass($s['object_id'],30)))
						$out[] = $s;
				}
			}
		}
		return $this->getJsonSearchCat($out,$root_id);
		// return $out;
	}

	#Возвращаем json объект после поиска
	function getJsonSearchCat($list,$root_id){
		// if (!is_array($list)) return false;
		$out = array();
		foreach ($list as $o){
			$parents = array_reverse($this->getParents($o['object_id'],$root_id));
			$arr = array();
			if ($parents[0]['head']==$root_id){
				foreach ($parents as $p){
					if (!empty($p['Название']))
						$arr[] = $p['Название'];
				}
				$out[$o['object_id']] = array(
					'name' => join("&;",$arr),
					'object' => true
				); 
			}
		}
		return json_encode($out);
		// return $out;
	}

	#Получаем состояния товара
	function conditionOptions($field){
		if (!is_array($field)) return false;
		$out = array();
		$ops = explode("\n",$field['p3']);
		foreach ($ops as $k=>$o){
			$out[] = '<option value="'.$k.'">'.$o.'</option>';
		}
		return join("\n",$out);
	}

	#Получаем состояния товара
	function srokOptions($field){
		if (!is_array($field)) return false;
		$out = array();
		$ops = explode("\n",$field['p3']);
		foreach ($ops as $k=>$o){
			$out[] = '<option value="'.$k.'">'.$o.'</option>';
		}
		return join("\n",$out);
	}

	#Получаем список марок товара
	function markOptions($field){
		if (!is_array($field)) return false;
		$id = $field['p4'];
		if (!$list = $this->objects->getFullObjectsList($id)) return false;
		$out = array();
		foreach ($list as $o){
			$out[] = '<option value="'.$o['Название'].'">'.$o['Название'].'</option>';
		}
		return join("\n",$out);
	}

	#Список годов
	function yearsList($count = 100){
		$begin = (int)date('Y') ;
		$end = (int)date('Y') - $count;
		$out = array();
		for ($i = $begin; $i>($end+1); $i--){
			$out[] = '<option value="'.$i.'">'.$i.'</option>';
		}
		return join("\n",$out);
	}

	#Добавить объявление
	function addAd(){
		$class_id = 31;
		if (empty($_POST)) {return false;}
		if (!isset($_POST['adType']) || !is_numeric($adType = $_POST['adType'])) {$this->msg = 'Отсутсвует тип'; return false;}
		if (!isset($_POST['adCategory']) || !is_numeric($adCategory = $_POST['adCategory'])) {$this->msg = 'Не выбран раздел'; return false;}
		if (empty($_POST['productCategory']) || !is_numeric($productCategory = $_POST['productCategory'])) {$this->msg = 'не выбрана категория'; return false;}
		if (empty($_POST['f']) || !is_array($f = $_POST['f'])) {$this->msg = 'заполнена не вся инфа'; return false;};
		$img = !empty($_POST['mainPhoto']) ? $this->filterInput($_POST['mainPhoto']) : '';
		$arenda = !empty($f['Срок аренды']) ? $this->filterInput($f['Срок аренды']) : '';
		$parents = array_reverse($this->getParents($productCategory,$adCategory));
		$object = array(
			"active"=>1,
			"head"=>$productCategory,
			"name"=>$this->filterInput($f['Название']),
			"class_id"=>$class_id,
			"sort"=>time()
		);
		$price = isset($f['Цена']) ? $this->filterInput($f['Цена']) : '';
		$valuta = isset($f['Валюта']) ? (int)$f['Валюта'] : 0;
		$fields = array(
			161 => $_SESSION['u']['id'],
			102 => 0, // Статус. Ставим на модерации
			103 => date("Y-m-d"), // Дата добавления 
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
			116 => $valuta,
			117 => (int)$f['Наличие товара'],
			118 => $this->filterInput($f['Местоположение товара']),
			119 => $this->filterInput($f['Поставщик оборудования']),
			120 => $this->filterInput($f['Сайт поставщика']),
			163 => $img,
			167 => (int)$adCategory,
			169 => (int)$parents[0]['id'],
			213 => $arenda,
			221 => (!empty($f['Видео'])?$this->convertYouTubeVideo($this->filterInput($f['Видео'])):''),
		);
		if ($head = $this->objects->createObjectAndFields($object,$fields)){
			if (!empty($img) && file_exists('cms/uploads_temp/'.$img))
				rename('cms/uploads_temp/'.$img, 'cms/uploads/'.$img);
			$this->msg = 'Добавлено';
			#Создаем доп фото
			if (!empty($_POST['photo']) && is_array($photos = $_POST['photo'])){
				$i = 1;
				foreach ($photos as $p){
					if ($p == $img) continue;
					$obj_photo = array(
						"active"=>1,
						"head"=>$head,
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
			$ad = $this->objects->getFullObject($head);
			$user = $this->objects->getFullObject($_SESSION['u']['id']);
			// print_r($this->sendNoticeToBusiness($user, $ad, $ad['Тип'], $ad['head']));
			$this->sendNoticeToBusiness($user, $ad, $ad['Тип'], $ad['head']);
			return true;
		}else return false;
	}

	#Добавление объявления для пользователей
	function addAdForUser(){
		$class_id = 31;
		if (empty($_POST)) {return false;}
		if (!isset($_POST['adType']) || !is_numeric($adType = $_POST['adType'])) {$this->msg = 'Отсутсвует тип'; return false;}
		if (!isset($_POST['adCategory']) || !is_numeric($adCategory = $_POST['adCategory'])) {$this->msg = 'Не выбран раздел'; return false;}
		if (empty($_POST['productCategory']) || !is_numeric($productCategory = $_POST['productCategory'])) {$this->msg = 'не выбрана категория'; return false;}
		if (empty($_POST['f']) || !is_array($f = $_POST['f'])) {$this->msg = 'заполнена не вся инфа'; return false;};
		$img = !empty($_POST['mainPhoto']) ? $this->filterInput($_POST['mainPhoto']) : '';
		$parents = array_reverse($this->getParents($productCategory,$adCategory));
		$arenda = !empty($f['Срок аренды']) ? $this->filterInput($f['Срок аренды']) : '';
		$price = isset($f['Цена']) ? $this->filterInput($f['Цена']) : '';
		$object = array(
			"active"=>1,
			"head"=>$productCategory,
			"name"=>$this->filterInput($f['Название']),
			"class_id"=>$class_id,
			"sort"=>time()
		);
		$fields = array(
			161 => $_SESSION['u']['id'],
			102 => 0, // Статус. Ставим на модерации
			103 => date("Y-m-d"), // Дата добавления 
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
			118 => $this->filterInput($f['Местоположение товара']),
			163 => $img,
			167 => (int)$adCategory,
			169 => (int)$parents[0]['id'],
			213 => $arenda,
		);
		if ($head = $this->objects->createObjectAndFields($object,$fields)){
			if (!empty($img) && file_exists('cms/uploads_temp/'.$img))
				rename('cms/uploads_temp/'.$img, 'cms/uploads/'.$img);
			$this->msg = 'Добавлено';
			#Создаем доп фото
			if (!empty($_POST['photo']) && is_array($photos = $_POST['photo'])){
				$i = 1;
				foreach ($photos as $kp=>$p){
					if ($p == $img) continue;
					$obj_photo = array(
						"active"=>1,
						"head"=>$head,
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
				if ($kp == 5) break;
			}
			$ad = $this->objects->getFullObject($head);
			$user = $this->objects->getFullObject($_SESSION['u']['id']);
			$this->sendNoticeToBusiness($user, $ad, $ad['Тип'], $ad['head']);
			return true;
		}else return false;
	}


	#Преобразуем ссылку видео в iframe
	function convertYouTubeVideo($url){
		if (empty($url)) return false;
		$parsed_url = parse_url($url);
		parse_str($parsed_url['query'], $parsed_query);
		return '<iframe src="http://www.youtube.com/embed/' . $parsed_query['v'] . '" type="text/html" width="400" height="300" frameborder="0"></iframe>';
	}


	#Уведомляем компании с "Бизнес" пакетом о подаче объявления о покупке или желании взять в аренду от пользователя
	function sendNoticeToBusiness($user, $ad, $type, $head, $com_class_id = 33, $ad_class_id = 31){
		if ($type == 1) $sendType = 0;
		elseif ($type == 3) $sendType = 2;
		elseif ($type == 0) $sendType = 1;
		elseif ($type == 2) $sendType = 3;
		else return false;
		#Получаем компании с бизнес пакетом которые размещали объявления в данной категории и с нужным нам типом
		$table = "`objects` as o RIGHT JOIN `class_$com_class_id` as com ON o.id = com.object_id RIGHT JOIN `class_$ad_class_id` as ad ON o.id = ad.field_161 RIGHT JOIN `objects` as o2 ON ad.object_id = o2.id";
		$where = "WHERE o.active = '1' AND o2.head = '$head' AND com.field_157 = 'business' AND com.field_259 = '1' AND ad.field_100 = '$sendType' AND ad.field_102 = '1'";
		$what = "com.field_123 as email";
		if (!$emails = $this->db->select($table, $where, $what)) return false;
		#Формируем письмо
		$this->mail->from = 'info@'.$_SERVER['HTTP_HOST'];
		$this->mail->subject = 'Новое объявление. Автоматическое уведомление с сайта '.$_SERVER['HTTP_HOST'].'';
		if (isset($user['Фио'])){
		$html = '
			<h3>Новое объявление на '.$_SERVER['HTTP_HOST'].'</h3>
			<div>
				Пользователь <b>'.$user['Фио'].'</b> разместил объявление 
				<a href="http://'.$_SERVER['HTTP_HOST'].'/ads/'.$ad['id'].'/" target="_blanc">http://'.$_SERVER['HTTP_HOST'].'/ads/'.$ad['id'].'/</a>
			</div>
		';
		}else{
		$html = '
			<h3>Новое объявление на '.$_SERVER['HTTP_HOST'].'</h3>
			<div>
				Компания <b>'.$user['Название компании'].'</b> разместила объявление 
				<a href="http://'.$_SERVER['HTTP_HOST'].'/ads/'.$ad['id'].'/" target="_blanc">http://'.$_SERVER['HTTP_HOST'].'/ads/'.$ad['id'].'/</a>
			</div>
		';
		}
		$this->mail->body = $html;
		// return $emails;
		#Отправляем письма
		foreach ($emails as $email){
			$this->mail->send($email);
		}
		return true;
	}


}?>