<?
Header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
Header("Cache-Control: no-cache, must-revalidate");
Header("Pragma: no-cache");
Header("Last-Modified: ".gmdate("D, d M Y H:i:s")."GMT");
header("Content-Type: text/html; charset=utf-8");
include('../cms/public/api.php');

if ( !$api->auth() ) exit('0');//!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || !$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' || 

if(isset($_REQUEST['translit']) && is_numeric($_REQUEST['translit']) && ($_REQUEST['translit'] == 1) && (!empty($_REQUEST['str']))){
	if( !!($str = $api->strings->urlTranslit($_REQUEST['str'])) ){
		exit($str);
	}
	exit('ошибка транслита..');
}else if(isset($_REQUEST['getObjectInfo']) && is_numeric($id = $_REQUEST['getObjectInfo'])){
	if( !$obj = $api->objects->getObject($id) ) exit('{}');
	$obj['fields'] = $api->db->select('fields', "WHERE `class_id`='".$obj['class_id']."' ORDER BY sort", "id, type, name, p1, p2, p3, p4");
	
	$fieldsValues = $api->objects->getObjectFields($id, $obj['class_id']);
	foreach($obj['fields'] as &$f) $f['value'] = $fieldsValues[$f['id']];
	
	exit( json_encode($obj) );
}else if(isset($_REQUEST['editObject']) && isset($_REQUEST['obj']) && is_array($obj = $_REQUEST['obj'])){
	if(!isset($obj['fields']) || !is_array($fields = $obj['fields'])) $fields = array();
	unset( $obj['fields'] );
	unset( $obj['name'] );
	if(!!$obj['class_id']){
		foreach($api->db->select("fields", "WHERE `class_id`='".$obj['class_id']."'") as $f){
			if($f['name']=='Название' && !empty($fields[$f['id']])){
				$obj['name']=$fields[$f['id']];
				// $obj['translit'] = $api->strings->urlTranslit($fields[$f['id']]);
				break;
			}
		}
	}
	if(!$obj['class_id'] && !!$api->objects->editObject($obj)) {
		preg_match('/href="?([^">]+)"/', $api->getLink($obj['id']), $match);
		exit($match[1]);
	}
	else if( @!!$api->objects->editObjectAndFields( $obj, $fields ) ) {
		preg_match('/href="?([^">]+)"/', $api->getLink($obj['id']), $match);
		exit($match[1]);
	}
	exit('ошибка сохранения..');
}else if(isset($_REQUEST['createObject']) && is_numeric($head = $_REQUEST['createObject']) && isset($_REQUEST['obj']) && is_array($obj = $_REQUEST['obj'])){
	if(!isset($obj['fields']) || !is_array($fields = $obj['fields'])) $fields = array();
	unset( $obj['fields'] );
	if(!!$obj['class_id']){
		foreach($api->db->select("fields", "WHERE `class_id`='".$obj['class_id']."'") as $f){
			if($f['name']=='Название' && !empty($fields[$f['id']])){
				$obj['name']=$fields[$f['id']];
				// $obj['translit'] = $api->strings->urlTranslit($fields[$f['id']]);
			}
		}
	}
	$obj['head'] = $head;
	$obj['sort'] = time();
	if(!$obj['class_id'] && !!$api->objects->createObject($obj)) exit('ok');
	else if( @!!$api->objects->createObjectAndFields( $obj, $fields ) ) exit('ok');
	exit('error');
}else if(isset($_REQUEST['addObjectToList']) && is_numeric($head = $_REQUEST['addObjectToList']) && isset($_REQUEST['class_id']) && is_numeric($class_id = $_REQUEST['class_id'])){
	$obj = array(
		'active'=>1,
		'head' => $head,
		'class_id' => $class_id,
		'name' => 'Новая страница',
		'sort' => time()
	);
	
	$fields = array();
	foreach($api->objects->getClassFields($class_id) as $f){
		if( in_array($f['type'], array('text', 'textarea', 'html')) ) $fields[$f['id']]='Текст.';
	}
	
	if(!$obj['class_id'] && !!$api->objects->createObject($obj)) exit('ok');
	else if( @!!$api->objects->createObjectAndFields( $obj, $fields ) ) exit('ok');
	exit('error');
}else if(isset($_REQUEST['removeObject']) && is_numeric($id = $_REQUEST['removeObject'])){
	if( $api->objects->deleteObject($id) ) exit('ok');
	exit('error');
}else if(isset($_REQUEST['moveObject']) && is_numeric($id = $_REQUEST['moveObject']) && isset($_REQUEST['to']) && is_numeric($to = $_REQUEST['to'])){
	if(!!($object = $api->objects->getObject($id)) && !!($to_object = $api->objects->getObject($to))){
		$api->db->update("objects", array("head"=>$to), "WHERE `id`='".$id."' LIMIT 1");
		exit('ok');
	}
	exit('error');
}else if(isset($_REQUEST['loadClasses']) && is_array($ids = $_REQUEST['ids'])){
	$out = array();
	foreach($ids as $id){
		if(!is_numeric($id) || !$id) continue;
		if(!($class = $api->db->select('classes', "WHERE `id`='".$id."' LIMIT 1")) || !($fields = $api->objects->getClassFields($id))) continue;
		$out[]=array_merge($class, array('fields'=>$fields));
	}
	exit( $api->json($out) );
}else if(isset($_REQUEST['loadObjects']) && is_array($ids = $_REQUEST['ids'])){
	$out = array();
	foreach($ids as $id){
		if(!is_numeric($id) || !$id) continue;
		$out[]=$id;
	}
	exit( $api->json( $api->db->select('objects', "WHERE `id` IN (".join(",", $out).") ORDER BY id") ) );

}else if(isset($_REQUEST['getObjectsList']) && is_numeric( $head = $_REQUEST['getObjectsList']) && isset( $_REQUEST['class_id'] ) && is_numeric( $class_id = $_REQUEST['class_id'] ) ){// AND o1.class_id='".$class_id."'
	exit( $api->json( $api->db->select("objects as o1", "LEFT JOIN objects as o2 ON o2.head=o1.id WHERE o1.active='1' AND o1.head='".$head."' AND (o1.class_id='0' OR o1.class_id='1' OR o1.class_id='2') GROUP BY o1.id ORDER BY sort,id", "o1.*, COUNT(o2.id) as inside") ) );

	
}
else if(isset($_REQUEST['loadObjectsList']) && is_numeric( $head = $_REQUEST['loadObjectsList'])){
	exit( json_encode( $api->objects->getFullObjectsList($head) ) );
}else if( isset($_REQUEST['sortObject']) && is_numeric($id = $_REQUEST['sortObject']) ){
	if(isset($_REQUEST['to']) && $api->objects->sortObject($id, $_REQUEST['to'])) exit('ok');
	return exit('error');
	
}else if(isset($_REQUEST['uploadFiles'])){
	if(empty($_FILES)) exit("[]");
	$out = array();
	foreach($_FILES['file']['tmp_name'] as $k => $v){
		if(!$v) continue;
		$type = '';
		if(preg_match("/\.([^\.]+)$/", $_FILES['file']['name'][$k], $ok)) $type = $api->lower($ok[1]);
		$new_name = "file_".time()."_".rand(0, 1000000000).($type ? ".".$type : "");
		move_uploaded_file( $v, _UPLOADS_ABS_."/".$new_name);
		$out['file-'.$k]=$new_name;
	}
	exit( $api->json( $out ) );
}else exit('WrongREQUEST;');
?>