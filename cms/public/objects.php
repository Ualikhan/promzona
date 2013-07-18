<?
/*
Title: OBJECTS PUBLIC API v 1.5.3
Author: Derevyanko Mikhail <m-derevyanko@ya.ru>
Date: 17.08.2009
*/
class objects extends appends{
	
	var $last;
	var $lang;
	function __construct(&$lang){
		parent::__construct();
		$this->last = null;
		$this->lang = &$lang;
	}
	
	
	# ПРОВЕРКА ПРИ АВТОРИЗАЦИИ 
	function getFullObjectsListByClassLogin($class_id, $sql="AND o.active=1 ORDER BY o.sort"){
		if(!($list = $this->getObjectsListByClassLogin($class_id, $sql))) return array();
		$out = array();
		if (!empty($list['id']) && !empty($list['class_id'])) return $list+$this->getObjectFields($list['id'], $list['class_id']);
		foreach($list as $o){
			$out[]=$o+$this->getObjectFields($o['id'], $o['class_id']);
		}
		return $out;
	}
	function getObjectsListByClassLogin($class_id, $sql="AND o.active='1' ORDER BY o.sort"){
		$sql = "WHERE ".(!!$class_id ? "c.lang='".$this->lang."' AND ":'')." o.class_id='".$class_id."'".(@$sql?" ".$sql:'');
		if(!!$class_id) $sql = "LEFT JOIN class_".$class_id." as c ON o.id=c.object_id ".$sql;
		if(!($list = $this->db->select("objects as o", $sql, "o.*")) ) return array();
		return $list;
	}
	
	
	
	
	
	#ВЫБОРКА ГРУППЫ ОБЪЕКТОВ ИЗ РОДИТЕЛЯ (0 - корень), вторым параметров ограничивается лимит выборки (SQL)
	function getObjectsList($head, $limit=''){
		if (is_numeric($head)) $w = 'head'; else $w = 'translit';
		if(!($list = $this->db->select("objects as o1", "LEFT JOIN objects as o2 ON o2.head=o1.id WHERE o1.active='1' AND o1.".$w."='".$head."' GROUP BY o1.id ORDER BY sort,id".(($limit != '')?' LIMIT '.$limit:''), "o1.*, COUNT(o2.id) as inside"))) return array();
		return $list;
	}
	#ВЫБОРКА ГРУППЫ ОБЪЕКТОВ ИЗ РОДИТЕЛЯ С ПОЛЯМИ ЗНАЧЕНИЙ ОБЪЕКТОВ
	function getFullObjectsList($head, $limit=false){
		if(!is_numeric($head) || !($list = $this->getObjectsList($head, $limit))) return array();
		$out = array();
		if(!empty($list['id']) && !empty($list['class_id'])) return $list+$this->getObjectFields($list['id'], $list['class_id']);
		foreach($list as $o){
			$out[]=$o+$this->getObjectFields($o['id'], $o['class_id']);
		}
		return $out;
	}
	
		
	
	
	
	#ВЫБОРКА КОЛИЧЕСТВА ОБЪЕКТОВ ИЗ РОДИТЕЛЯ С ЗАДАННЫМ КЛАССОМ
	function getObjectsCount($head, $class_id, $sql="AND o.active='1'"){
		if (!is_numeric($head)) {
			$obj = $this->getObject($head);
			$head = $obj['id'];
		};
		$sql = "WHERE ".(!!$class_id ? "c.lang='".$this->lang."' AND ":'').($head != -1 ? "o.head='".$head."' AND " : '')."o.class_id='".$class_id."'".(@$sql?" ".$sql:'');
		if(!!$class_id) $sql = "LEFT JOIN class_".$class_id." as c ON o.id=c.object_id ".$sql;
		if(!is_numeric($head) || !is_numeric($class_id) || !($count = $this->db->count("objects as o", $sql)) ) return 0;
		return $count;
	}
	#ВЫБОРКА ГРУППЫ ОБЪЕКТОВ ИЗ РОДИТЕЛЯ С ЗАДАННЫМ КЛАССОМ, возможна сортировка по полям класса
	function getObjectsListByClass($head, $class_id, $sql="AND o.active='1' ORDER BY o.sort"){
		if (!is_numeric($head)) {
			$obj = $this->getObject($head);
			$head = $obj['id'];
		};
		$sql = "WHERE ".(!!$class_id ? "c.lang='".$this->lang."' AND ":'').($head != -1 ? "o.head='".$head."' AND " : '')."o.class_id='".$class_id."'".(@$sql?" ".$sql:'');
		if(!!$class_id) $sql = "LEFT JOIN class_".$class_id." as c ON o.id=c.object_id ".$sql;
		if(!($list = $this->db->select("objects as o", $sql, "o.*")) ) return array();
		return $list;
	}
	#ВЫБОРКА ГРУППЫ ОБЪЕКТОВ ИЗ РОДИТЕЛЯ С ЗАДАННЫМ КЛАССОМ СО ЗНАЧЕНИЯМИ ПОЛЕЙ ОБЪЕКТОВ
	function getFullObjectsListByClass($head, $class_id, $sql="AND o.active=1 ORDER BY o.sort"){
		if(!($list = $this->getObjectsListByClass($head, $class_id, $sql))) return array();
		$out = array();
		if (!empty($list['id']) && !empty($list['class_id'])) return $list+$this->getObjectFields($list['id'], $list['class_id']);
		foreach($list as $o){
			$out[]=$o+$this->getObjectFields($o['id'], $o['class_id']);
		}
		return $out;
	}
	#ВЫБОРКА ОДНОГО ОБЪЕКТА С ЗАДАННЫМ ID, вторым параметром указывается запомнится ли ID данного объекта в переменной $this->last или нет.
	function getObject( $id, $remember=true ){
		if (is_numeric($id)) $w = 'id'; else $w = 'translit';
		if(!($obj = $this->db->select("objects as o1", "LEFT JOIN objects as o2 ON o2.head=o1.id WHERE o1.".$w."='".$id."' GROUP BY o1.id LIMIT 1", "o1.*, COUNT(o2.id) as inside"))) return false;
		if(!!$remember) $this->last = $obj['id'];
		return $obj;
	}
	#ВЫБОРКА ОДНОГО ОБЪЕКТА С ЗАДАННЫМ ID ВКЛЮЧАЯ ЗНАЧЕНИЯ ПОЛЕЙ
	function getFullObject( $id, $remember=true ){
		if(!($obj = $this->getObject( $id, $remember ))) return false;
		return $obj+$this->getObjectFields($obj['id'], $obj['class_id']);
	}
	#ВЫБОРКА ЗНАЧЕНИЙ ПОЛЕЙ ОБЪЕКТА ПО ID ОБЪЕКТА И ID КЛАССА
	function getObjectFields($object_id, $class_id){
		if(!$class_id) return array();
		$fields = array();
		$field_values = $this->db->select('class_'.$class_id, "WHERE `object_id`='".$object_id."' AND `lang`='".$this->lang."' LIMIT 1");
		foreach($this->db->select('fields', "WHERE `class_id`='".$class_id."' ORDER BY sort") as $f){
			if($f['type']=='html'){
				$value = isset($field_values['field_'.$f['id']]) ? htmlspecialchars_decode($field_values['field_'.$f['id']]) : '';
			}else $value = isset($field_values['field_'.$f['id']]) ? $field_values['field_'.$f['id']] : '';
			$fields[$f['name']] = $value;
			$fields[$f['id']] = $value;
		}
		return $fields;
	}
	#ВЫБОРКА ПОЛЕЙ КЛАССА, С СООТВЕСТВУЮЩИМИ ПОЛЯМИ, КОТОРЫЕ ЗАКРЕПЛЕНЫ ЗА ПОЛЯМИ (В ТАБЛИЦЕ ПОЛЕЙ)
	function getClassFields($class_id){
		return $this->db->select('fields', "WHERE `class_id`='".$class_id."' ORDER BY sort");
	}
	#СОЗДАНИЕ ОБЪЕКТА И ПОЛЕЙ ОДНИМ МЕТОДОМ
	function createObjectAndFields($object, $fields){
		if( empty($object['name']) || !is_numeric($object['class_id']) ) return false;
		return $this->createObjectFields( $this->createObject($object), $fields );
	}
	#СОЗДАНИЕ ОБЪЕКТА
	function createObject($object){
		if(empty($object['sort'])) $object['sort'] = time();
		if(!isset($object['active'])) $object['active'] = 1;
		
		if( $this->db->insert('objects', $object) ){ 
			return mysql_insert_id($this->db->link);
		}
		return false;
	}
	#СОЗДАНИЕ ПОЛЕЙ ОБЪЕКТА
	function createObjectFields($object_id, $fields){
		if(!$object = $this->db->select("objects", "WHERE `id`='".$object_id."' LIMIT 1")) return false;
		
		$field_ids = array();
		foreach($this->getClassFields($object['class_id']) as $f){
			$field_ids[$f['name']]=$f['id'];
		}		
		
		$out = array();
		foreach($fields as $id=>$value){
			if(!is_numeric($id)){
				if(!empty($field_ids[$id])) $id = $field_ids[$id];
				else continue;
			}
			$out['field_'.$id]=$value;
		}
		$out['lang'] = $this->lang;
		$out['object_id'] = $object['id'];
		if( $this->db->insert("class_".$object['class_id'], $out) ) return $object_id;
		return false;
	}
	#РЕДАКТИРОВАНИЕ ОБЪЕКТА И ПОЛЕЙ ОДНОВРЕМЕННО
	function editObjectAndFields($object, $fields){
		if( !isset($object['id']) || !is_numeric($object['class_id'])) return false;
		return ( $this->editObject($object) && $this->editObjectFields($object['id'], $fields) );
	}
	#РЕДАКТИРОВАНИЕ ОБЪЕКТА
	function editObject($object){
		if( empty($object['id']) ) return false;
		return $this->db->update("objects", $object, "WHERE `id`='".$object['id']."'");
	}
	#РЕДАКТИРОВАНИЕ ПОЛЕЙ ОБЪЕКТА
	function editObjectFields($object_id, $fields){
		if(!$object = $this->db->select("objects", "WHERE `id`='".$object_id."' LIMIT 1")) return false;
		
		$field_ids = array();
		foreach($this->getClassFields($object['class_id']) as $f){
			$field_ids[$f['name']]=$f['id'];
		}	
		
		$out = array();
		foreach($fields as $id=>$value){
			if(!is_numeric($id)){
				if(!empty($field_ids[$id])) $id = $field_ids[$id];
				else continue;
			}
			$out['field_'.$id]=$value;
		}
		$out['object_id'] = $object['id'];
		$out['lang'] = $this->lang;
		#ПРОВЕРЯЕМ ЕСТЬ ЗАПИСЬ В ПОЛЯХ У КЛАССА ОБЪЕКТА, ЕСЛИ НЕТ ЗНАЧИТ ПРИ РЕДАТИРОВАНИИ КЛАСС БЫЛ ИЗМЕНЕН
		if( $langs = $this->db->select('class_'.$object['class_id'], "WHERE `object_id`='".$object['id']."'", 'lang') ){
			#КЛАСС НЕ МЕНЯЛИ, НУЖНО ПРОВЕРИТЬ ЕСТЬ ЛИ ИНТЕРЕСНЫЙ НАМ ЯЗЫК ИЛИ НУЖНО ЕГО СОЗДАТЬ
			#ЯЗЫК ЕСТЬ, СОХРАНЯЕМ ПОЛЯ
			if( !!in_array($this->lang, $langs) )	return $this->db->update('class_'.$object['class_id'], $out, "WHERE `object_id`='".$object['id']."' AND `lang`='".$this->lang."'");
			else return $this->db->insert('class_'.$object['class_id'], $out);
		}else{
			#КЛАСС МЕНЯЛИ, СУЧКИ!
			#ЧИСТИМ ВСЕ КЛАССЫ ОТ ВОЗМОЖНОГО ПРИСУТСТВИЯ ОБЪЕКТА
			foreach($this->db->select("classes", "ORDER BY id") as $c){
				$this->db->delete('class_'.$c['id'], "WHERE `object_id`='".$object['id']."'");
			}
			
			#ДОБАВЛЯЕМ НОВУЮ ЗАПИСЬ С ПОЛЯМИ В ТАБЛИЦУ КЛАССА
			return $this->db->insert('class_'.$object['class_id'], $out);
		}
	}
	#СОРТИРОВКА ОБЪЕКТА
	function sortObject($id, $to='up'){
	
		if(!is_numeric($id) || !in_array($to, array("up", "down"))) return false;
		if($to=='up'){
			if(!($obj = $this->db->select('objects', "WHERE `id`='".$id."' LIMIT 1")) || !($upper = $this->db->select('objects', "WHERE `head`='".$obj['head']."' AND `sort`<'".$obj['sort']."' ORDER BY sort DESC LIMIT 1"))) return false;
			$this->db->update('objects', array_merge($obj, array("sort"=>$upper['sort'])), "WHERE `id`='".$obj['id']."'");
			$this->db->update('objects', array_merge($upper, array("sort"=>$obj['sort'])), "WHERE `id`='".$upper['id']."'");
			return true;
		}
		if(!($obj = $this->db->select('objects', "WHERE `id`='".$id."' LIMIT 1")) || !($downer = $this->db->select('objects', "WHERE `head`='".$obj['head']."' AND `sort`>'".$obj['sort']."' ORDER BY sort LIMIT 1"))) return false;
		$this->db->update('objects', array_merge($obj, array("sort"=>$downer['sort'])), "WHERE `id`='".$obj['id']."'");
		$this->db->update('objects', array_merge($downer, array("sort"=>$obj['sort'])), "WHERE `id`='".$downer['id']."'");
		return true;
	}
	#ОПРЕДЕЛЕНИЕ HTML-КОДА ПОЛЯ ПО ТИПУ
	function getFieldInput($k, $f){
		$class='';
		if(isset($f['class'])){
			$class=$f['class'];
		}
		switch( $f['type'] ){
			case 'text': return '<input type="text" maxlength="250" name="fields['.$k.']" value="'.(@$f['value']?$f['value']:'').'" '.(@$f['p1']?'style="width:'.$f['p1'].'px;"':'').'>';
			case 'number': return '<input type="text" class="digital '.$class.'" name="fields['.$k.']" value="'.(@$f['value']?$f['value']:'').'" style="'.(@$f['p1']?'width:'.$f['p1'].'px;':'').'" />';
		case 'date': return '<input type="text" name="fields['.$k.']" value="'.(@$f['value']?$f['value']:'').'" class="datepicker '.$class.'" />';
		case 'checkbox': return '<input type="checkbox"'.($class!='' ? ' class="'.$class.'"' : '').' name="fields['.$k.']" value="" />';
		case 'textarea': return '<textarea name="fields['.$k.']"'.($class!='' ? ' class="'.$class.'"' : '').' style="'.(@$f['p1']?'width:'.$f['p1'].'px;':'').(@$f['p2']?'height:'.$f['p2'].'px;':'').'">'.(@$f['value']?$f['value']:'').'</textarea>';
		case 'html': return 'none';
		case 'select':
			$html='';
			if(@$f['p3'] && (trim($f['p3'])!='')){
				$values=explode("\n",$f['p3']);
				$html.='<select'.($class!='' ? ' class="'.$class.'"' : '').' name="fields['.$k.']">';
				foreach($values as $v){
					$html.='<option value="'.$v.'">'.$v.'</option>';
				}
				$html.='</select>';
			}
			return $html;
		case 'password': return '<input type="password"'.($class!='' ? ' class="'.$class.'"' : '').' name="fields['.$k.']" value="'.(@$f['value']?$f['value']:'').'" style="'.(@$f['p1']?'width:'.$f['p1'].'px;':'').'">';
		case 'radio': return '<input type="radio"'.($class!='' ? ' class="'.$class.'"' : '').' name="fields['.$k.']" value="" />';
		case 'file': return '<input type="file"'.($class!='' ? ' class="'.$class.'"' : '').' name="fields['.$k.']" value="" />';
		default: return '<input type="text"'.($class!='' ? ' class="'.$class.'"' : '').' name="fields['.$k.']" value="'.(@$f['value']?$f['value']:'').'" style="'.(@$f['p1']?'width:'.$f['p1'].'px;':'').'">';
		}
	}
	#РЕКУРСИВНОЕ УДАЛЕНИЕ ОБЪЕКТА И ВСЕХ ЕГО ПОТОМКОВ (С УДАЛЕНИЕМ ЗНАЧЕНИЙ ПОЛЕЙ ВСЕХ ЯЗЫКОВЫХ ВЕРСИЙ)
	function deleteObject($id){
		if(!!($object = $this->db->select("objects", "WHERE `id`='".$id."' LIMIT 1"))){ 
			$this->db->delete("class_".$object['class_id'], "WHERE `object_id`='".$object['id']."'");
			$this->db->delete("objects", "WHERE `id`='".$id."' LIMIT 1");
			if(!!$childs = $this->db->select("objects", "WHERE `head`='".$id."'")){
				foreach($childs as $ch){
					$this->deleteObject($ch['id']);
				}
			}
			return true;
		}
		return false;
	}
	function urlTranslitFormID($id){
		if ($o = $this->getObject($id))
			return $this->urlTranslit($o['name']);
		else return '';
	}

	#Новая постраничная навигация 
	function pagination($total_count, $curent_page, $on_page, $count_links = 5){
		$count_pages = ceil($total_count / $on_page);
		$prev = $curent_page > 1 ? true : false;
		$next = $curent_page != $count_pages ? true : false;
		$start = ($curent_page * $on_page) - $on_page;
		return array(
			'start' => $start,
			'count_pages' => $count_pages,
			'next' => $next,
			'prev' => $prev,
			'curent_page' => $curent_page,
			'on_page' => $on_page
		);
	}

	#Названия полей селекта
	function getFieldP3($field_id,$class_id, $sql = ''){
		return explode("\n",$this->db->select("fields","WHERE `id` = '$field_id' AND `class_id` = '$class_id' LIMIT 1", "p3"));
	}


}
?>