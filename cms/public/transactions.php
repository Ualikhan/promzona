<?class transactions extends appends{

	function __construct(){
		parent::__construct();
	}

	#Создаем транзакцию
	function saveData($from, $to, $summ, $type = 'system', $invoice_id = 0){
		$params = array(
			'from'=>$from,
			'to'=>$to,
			'sum'=>$summ,
			'datetime'=>time(),
			'invoice_id'=>$invoice_id,
			'type'=>$type
		);
		if( $this->db->insert('transactions', $params) ){ 
			// return mysql_insert_id($this->db->link);
			return true;
		}
		return false;
	}

	#Получаем список транзакций // можно указывать доп параметры
	function getData($sql = ""){
		return $this->db->select('transactions', $sql);
	}

}?>