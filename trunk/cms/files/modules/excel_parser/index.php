<?
include(_FILES_ABS_.'/modules/excel_parser/excel_reader2.php');
include($_SERVER['DOCUMENT_ROOT'].'/cms/public/objects.php');

class excel_parser extends appends{
private $path;

public $title;
public $info;
protected $js;
protected $css;
public $ico;
public $base;
public $objects;


	function __construct(){
		parent::__construct();
		#PRIVATE
		$this->path = _MODULES_.'/'.basename(dirname(__FILE__));
		#CONFIG
		$this->title = 'Загрузка товаров из excel';
		$this->info = 'Парсинг файла excel и загрузка полученных данных в каталог';
		$this->js = array(
			$this->path."/js/init.js"
		);
		$this->css = array(
			$this->path."/css/page.css"
		);
		$this->lang = 'ru';
		$this->objects = new objects( $this->lang );
		$this->ico = null;
	}
	
	function ajax(){
		global $api;

	}
	function vd($o){
		echo '<pre>';
		var_dump($o);
		echo '</pre>';
	}
	
	function initHTML(){
        $goods_class_id = 15;
        $catalog_class_id = 17;
		$root = 15;

		if(!empty($_FILES['upload_file'])) {

            if(end(explode('.',$_FILES['upload_file']['name']))!="xls") $error = "Файл может быть только в формате Excel 97-2003 (.xls)";
            if(!$_FILES['upload_file']['size']) $error = "Ошибка закачки файла";

            $xls_data=new Spreadsheet_Excel_Reader($_FILES['upload_file']['tmp_name']);
            $xls_row_count=$xls_data->rowcount();

            if(!$xls_data || $xls_row_count<2) $error = "Ошибка чтения файла или пустой excel-файл";

            if (empty($error))
            {
                $categories = $this->db->select("objects", "WHERE `class_id`='".(int)$catalog_class_id."'");
                $goods_data = $this->db->select("objects", "WHERE `class_id`='".(int)$goods_class_id."'");

                $cats = array();

                foreach($goods_data as $aItem)
                {
                    $goods[$aItem['id']] = $aItem['name'];
                }

                foreach($categories as $iKey=>$aItem)
                {
                    $cats[$aItem['id']] = $aItem['name'];
                }

                $count = 0;
				$updcount= 0;
                $category_new = $goods_new = $goods_upd = array();

                for($i=2;$i<=$xls_row_count;$i++){
                    $category = $category_id = array();

                    $category[1] = trim($xls_data->val($i,1));
                    $category[2] = trim($xls_data->val($i,2));

                    $goods_name = trim($xls_data->val($i,3));
                    $goods_articul = trim($xls_data->val($i,4));
                    $goods_producer = trim($xls_data->val($i,5));
                    $goods_model = trim($xls_data->val($i,6));
                    $goods_ostatok = trim($xls_data->val($i,7));
					$goods_price1 = trim($xls_data->val($i,8));
					$goods_price2 = trim($xls_data->val($i,9));
					
                    if (!empty($category[1]) && !empty($category[2]) && !empty($goods_name) && !empty($goods_articul) && !empty($goods_producer) && !empty($goods_price1) && !empty($goods_price2)){
					
                        foreach($category as $iKey=>$sItem){
							
                            if($obj_id = array_search($sItem,$cats)){
                                $category_id[$iKey] = $obj_id;
                            }elseif($obj_id = array_search($sItem,$category_new)){
                                $category_id[$iKey] = $obj_id;
                            }else{
                                if($iKey == 1) $head = $root;
                                else $head = $category_id[$iKey-1];
								$str = '';
								if( !!($str = $this->objects->urlTranslit($sItem) )){
									if (($obj_exist = $this->objects->getObject($str)) && ($q  = $this->db->mysql_query("SHOW TABLE STATUS FROM `".$this->dbname."` LIKE 'objects'")) && ($id = mysql_result($q, 0, 'Auto_increment')))
										$str = $str.'_'.$id;
								}
								$object['translit'] = $str;
                                $aData = array(
                                    'head' => $head,
                                    'class_id' => $catalog_class_id,
                                    'sort' => time(),
                                    'active' => 1,
                                    'name' => $sItem,
									'translit' => $str
                                );

                                $this->db->insert("objects", $aData);
                                $object_id = mysql_insert_id($this->db->link);

                                $aData = array(
                                    'object_id' => $object_id,
                                    'lang' => 'ru',
                                    'field_53' => $sItem,
                                    'field_55' => '',
                                    'field_56' => '',
                                );

                                $this->db->insert("class_".$catalog_class_id, $aData);
                                $category_id[$iKey] = $object_id;
                                $category_new[$object_id] = $sItem;
                            }
							// print_r($cats);
                        }
						
						//проверка на существование товара: если есть в данной категории товар с идентичным названием и ценой. Цена другая - создаем новый
						// echo '$category_id[2] = '.$category_id[2].'<br />';
						// echo '$this->db->prepare($goods_name) = '.$this->db->prepare($goods_name).'<br />';
						if($sobj=$this->objects->getFullObjectsListByClass($category_id[2],$goods_class_id,"AND c.field_47='".$this->db->prepare($goods_name)."' AND c.field_48=".$goods_price1." AND c.field_60=".$goods_price2." LIMIT 1")){

							$aData = array(
								'id' => $sobj['id'],
								'head' => $sobj['head'],
								'class_id' => $goods_class_id,
								'sort' => $sobj['sort'],
								'active' => 1,
								'name' => $goods_name
							);

							$this->objects->editObject($aData);
							$aData = array(
								'object_id' => $sobj['id'],
								'lang' => 'ru',
								'field_57' => trim($goods_ostatok),
								'field_46' => trim($sobj['Новинка']),
								'field_58' => trim($sobj['Специальное предложение']),
								'field_47' => trim($goods_name),
								'field_59' => trim($sobj['Артикул']),
								'field_61' => trim($goods_producer),
								'field_62' => trim($goods_model),
								'field_48' => trim($goods_price1),
								'field_60' => trim($goods_price2),
								'field_49' => trim($sobj['Изображение']),
								'field_50' => trim($sobj['Анонс']),
								'field_51' => trim($sobj['Описание'])
							);
							$this->objects->editObjectFields($sobj['id'],$aData);
							$updcount++; //прошел запрос обновления, не прошел - все-равно считаем как обновленный товар
							$goods_upd[] = $goods_name;
							
						}else{
							$str = '';
							if( !!($str = $this->objects->urlTranslit($goods_name) )){
								if (($obj_exist = $this->objects->getObject($str)) && ($q  = $this->db->mysql_query("SHOW TABLE STATUS FROM `".$this->dbname."` LIKE 'objects'")) && ($id = mysql_result($q, 0, 'Auto_increment')))
									$str = $str.'_'.$id;
							}
						
							$aData = array(
								'head' => $category_id[2],
								'class_id' => $goods_class_id,
								'sort' => time(),
								'active' => 1,
								'name' => $goods_name,
								'translit' => $str
							);

							$this->db->insert("objects", $aData);
							$object_id = mysql_insert_id($this->db->link);

							$aData = array(
								'object_id' => $object_id,
								'lang' => 'ru',
								'field_57' => trim($goods_ostatok),
								'field_46' => '',
								'field_58' => '',
								'field_47' => trim($goods_name),
								'field_59' => trim($goods_articul),
								'field_61' => trim($goods_producer),
								'field_62' => trim($goods_model),
								'field_48' => trim($goods_price1),
								'field_60' => trim($goods_price2),
								'field_49' => '',
								'field_50' => '',
								'field_51' => ''
							);

							$this->db->insert("class_".$goods_class_id, $aData);
							$count++;
							$goods_new[] = $goods_name;
						}
                    }
                }
				echo '<div id="info-message" style="margin-bottom:20px;"><font color="green">Добавлено в каталог '.$this->sklon($count, array('строка', 'строки', 'строк')).' из excel-файла.</font></div>';
				echo '<div id="info-message" style="margin-bottom:20px;"><font color="green">Обновлено в каталоге '.$this->sklon($updcount, array('строка', 'строки', 'строк')).' из excel-файла.</font></div>';
            }else{
                echo '<div id="info-message" style="margin-bottom:20px;"><font color="red">'.$error.'</font></div>';
            }
		}
		
		$out = array('<div><form class="subscribe-form" enctype="multipart/form-data" method="post">');
			$out[]='<table class="form-table">';
				$out[]='<tr>';
					$out[]='<td class="left-part">';
						$out[]='<h2>Excel-файл</h2>';
						$out[]='<div>';
							$out[]='<input type="file" id="upload_file" name="upload_file">';
						$out[]='</div><br>';
                        $out[]='<div><input type=submit class="button approve" value="отправить"></div>';
					$out[]='</td>';
				$out[]='</tr>';
			$out[]='</table>';
		$out[]='</form></div>';
		return join("\n", $out);
	}
#USER FUNCTIONS
}
?>