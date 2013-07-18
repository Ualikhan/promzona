<?class invoices extends api{

public $title,$tpl;
public $class_id,$object,$bills,$filter, $sort;

	function __construct(){
		parent::__construct();
		$this->title = 'Счета';
		$this->class_id = 41;
		$this->createBill();
		$this->deleteInvoice();
		$this->filter = isset($_GET['sort-filter']) && is_numeric($_GET['sort-filter']) ? $_GET['sort-filter'] : 'all';
		$this->sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
		$this->getTemplate();
	}

	#Вставляем шаблон взависимости роли пользователя
	function getTemplate(){
		if ((isset($_GET['n']) && is_numeric($n = $_GET['n']))){
			$this->tpl = '/bil_page.html';
			$this->object = $this->objects->getFullObject($n);
			if ($this->object['head'] != $_SESSION['u']['id']) header("location: /cabinet/invoices/");
			return false;
		}
		switch ($_SESSION['u']['role']){
			// case 'user':
			// 	$this->tpl = '/company_info_create.html';
			// break;
			case 'company':
				$sql = $this->filtAndSort();
				if (($this->bills = $this->objects->getFullObjectsListByClass($_SESSION['u']['id'],$this->class_id, "AND o.active = 1 ".$sql))||(isset($this->filter) && ($this->filter != 'all'))){
					$this->tpl = '/bills.html';
				}else{
					$this->tpl = '/bills_empty.html';
				}
			break;
			case 'business':
				$sql = $this->filtAndSort();
				$this->bills = $this->objects->getFullObjectsListByClass($_SESSION['u']['id'],$this->class_id, "AND o.active = 1 ".$sql);
				$this->tpl = '/bills.html';
			break;
			default:
				$this->tpl = '/empty.html';
			break;
		}
	}

	#Создаем счет
	function createBill(){
		if (!isset($_POST['name']) || !is_numeric($name = $_POST['name'])) return false;
		if (empty($_POST['fio']) || (!$fio = $this->filterInput($_POST['fio']))) return false;
		$obj_name = !empty($_POST['obj_name']) ? $_POST['obj_name'] : '';
		$summ = !empty($_POST['summ']) ? $_POST['summ'] : 0;
		$address = !empty($_POST['address']) ? $this->filterInput($_POST['summ']) : '';
		$rnn = !empty($_POST['rnn']) ? $this->filterInput($_POST['rnn']) : '';
		$bill_type = !empty($_POST['bill_type']) ? $_POST['bill_type'] : 0;
		$srok = !empty($_POST['bill_srok']) ? $this->getSrok($_POST['bill_srok']) : 0;
		if( $this->db->insert('invoices',array('user_id'=>$_SESSION['u']['id'])) ){ 
			$bill_number = mysql_insert_id($this->db->link);
		}else{
			return false;
		}
		$object = array(
			"active"=>1,
			"head"=>$_SESSION['u']['id'],
			"name"=>$this->filterInput($obj_name),
			"class_id"=>$this->class_id,
			"sort"=>time()
		);
		$fields = array(
			193 => $this->filterInput($obj_name),
			195 => str_pad($bill_number, 8, '0', STR_PAD_LEFT),
			197 => 0,
			199 => (int)str_replace(" ", "", $summ),
			201 => $name,
			203 => $address,
			205 => $rnn,
			207 => '',
			209 => $fio,
			255 => $bill_type,
			257 => $srok,
		);
		if ( ($head = $this->objects->createObjectAndFields($object,$fields)) && ($this->db->update("invoices", array('object_id'=>$head), "WHERE `id`='$bill_number'")) ){
			// $this->db->update("invoices", array('object_id'=>$head), "WHERE `id`='$bill_number'");
			header("location: /cabinet/invoices/".$head);
		}
	}

	#Срок объявления
	function getSrok($num){
		switch ($num){
			case 3:
				return 1;
			break;
			case 6:
				return 2;
			break;
			case 9:
				return 3;
			break;
			case 12:
				return 4;
			break;
			default:
				return 0;
			break;
		}
	}

	#Фильтруем и сортируем счета (формируем sql запрос)
	function filtAndSort(){
		$sql = "";
		if (is_numeric($this->filter)){
			$sql .= " AND c.field_197 = '".$this->filter."'";
		}
		switch ($this->sort){
			case 'name':
				$sql .= " ORDER BY c.field_195 ASC";
			break;
			case 'name_desc':
				$sql .= " ORDER BY c.field_195 DESC";
			break;
			case 'summ':
				$sql .= " ORDER BY c.field_199 ASC";
			break;
			case 'summ_desc':
				$sql .= " ORDER BY c.field_199 DESC";
			break;
			case 'date1':
				$sql .= " ORDER BY o.sort ASC";
			break;
			case 'date1_desc':
				$sql .= " ORDER BY o.sort DESC";
			break;
			case 'date2':
				$sql .= " ORDER BY c.field_207 ASC";
			break;
			case 'date2_desc':
				$sql .= " ORDER BY c.field_207 DESC";
			break;
			default:
				$sql .= " ORDER BY c.field_195 ASC";
			break;
		}
		return $sql;
	}

	#ССылка сортировки
	function sortHref($str){
		$link = isset($_GET['sort-filter']) ? "?sort-filter=".$this->filter."&sort=" : "?sort=";
		if (isset($_GET['sort']) && ($sort = $_GET['sort']) && ($sort == $str)){
			$link .= $str.'_desc';
		}else{
			$link .= $str;
		}
		return $link;
	}

	#Удаление счета 
	function deleteInvoice(){
		if (!isset($_POST['deleteBill']) || ($_POST['deleteBill'] != '1')) return false;
		if (empty($_GET['n']) || !is_numeric($id = (int)$_GET['n'])) return false;
		if ($this->objects->editObject(array('id'=>$id, 'active'=>'0'))){
			header("location: /cabinet/invoices/");
		}
	}

###############################################################################
	// function generateTCPDF( $data ) {
	// 	require_once(_MODS_ABS_.'/cabinet/classes/invoice_tcpdf.php');

	// 	# Создаем PDF-документ.
	// 	$pdf = new InvoicePdf( $data, 'P', 'pt', 'LETTER' );

	// 	# Создаем счет.
	// 	$pdf->CreateInvoice();

	// 	# Выводим PDF-документ.
	// 	$pdf->Output( '', 'D' );
	// }

	// function convertDomPDF($html=''){
	// 	require_once(_FILES_ABS_."/dompdf/dompdf_config.inc.php");
	// 	$html =
	// 	'<html><body>'.
	// 	'<p>Put your html here, or generate it with your favourite '.
	// 	'templating system.</p>'.
	// 	'</body></html>';
	// 	$dompdf = new DOMPDF();
	// 	$dompdf->load_html($html);
	// 	$dompdf->render();
	// 	$dompdf->stream("");
	// }
###############################################################################

}?>