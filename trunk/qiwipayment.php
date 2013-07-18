<?php
if( ($_SERVER['REMOTE_ADDR']!='92.46.53.228') && ($_SERVER['REMOTE_ADDR']!='212.154.215.82') && ($_SERVER['REMOTE_ADDR']!='195.189.30.150') && ($_SERVER['REMOTE_ADDR']!='79.142.55.231')){
	header("HTTP/1.1 403 Access denied");
	exit();
}

	require_once('cms/public/api.php');
	require_once('cms/public/transactions.php');

	$class_id = 33;

	$pay_type = !empty($_GET['pay_type']) ? $_GET['pay_type'] : 1;

	#Проверка запроса на ошибки
	function checkRequest(){
		global $api, $class_id;
		if (!isset($_GET['account']) || !is_numeric($id = $_GET['account'])) return 4;
		if ( (!$object = $api->objects->getFullObject($id)) || ($object['class_id'] != $class_id) ) return 5;
		if ($object['Роль'] != 'business') return 7;
		if (($object['active'] != 1) || ($object['Статус'] != 1)) return 79;
		if (!isset($_GET['sum']) || ($_GET['sum'] < 200)) return 241;
		return 0;
	}

	$result = checkRequest();


// header("Expires: Thu, 19 Feb 1998 13:24:18 GMT");
// // header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
// // header("Cache-Control: no-cache, must-revalidate");
// // header("Cache-Control: post-check=0,pre-check=0");
// // header("Cache-Control: max-age=0");
// // header("Pragma: no-cache");

if ($_GET['command'] == 'check'){
header("Content-Type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8"?>';
exit ('<response>
		<osmp_txn_id>'.$_GET['txn_id'].'</osmp_txn_id>
		<result>'.$result.'</result>
		<comment></comment>
	</response>');
}elseif ($_GET['command'] == 'pay'){

	$trans = new transactions();
	$id = (int)$_GET['account'];
	$txn_id = (int)$_GET['txn_id'];
	$sum = $_GET['sum'];

	if ($object = $api->objects->getFullObject($id)){
		if (!$trans->saveData(0, $id, $sum, 'qiwi')) $result = 8;
		if ($result == 0){
			$fields = array(171 => ($sum+$object['Кредиты']));
			if (!$api->objects->editObjectFields($id, $fields)) $result = 90;
		}
	}
header("Content-Type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8"?>';
exit('<response>
		<osmp_txn_id>'.$txn_id.'</osmp_txn_id>
		<prv_txn>2016</prv_txn>
		<sum>'.$sum.'</sum>
		<result>'.$result.'</result>
		<comment></comment>
	</response>');
}
?>