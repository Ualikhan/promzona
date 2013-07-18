<?
header('Content-Type: text/html; charset=utf-8');

if($_SERVER['REQUEST_URI']=='/cms/files/modules/excel_parser/index.php'){
	exit;
}

include('excel_reader2.php');
include($_SERVER['DOCUMENT_ROOT'].'/cms/public/objects.php');

class excel_parser extends appends{
private $path;

public $title;
public $info;
protected $js;
protected $css;
public $ico;
public $base;
public $lang;
public $objects;

	function vd($o){
		echo '<pre>';
		echo var_dump($o);
		echo '</pre>';
	}
	
	function getField($id){
		return $this->db->select('fields', "WHERE `id`='".$id."' LIMIT 1");
	}

	function __construct(){
		parent::__construct();
		#PRIVATE
		$this->path = _MODULES_.'/'.basename(dirname(__FILE__));
		#CONFIG
		$this->title = 'Загрузка товаров из excel';
		$this->info = 'Парсинг файла excel и загрузка полученных данных в каталог';
		$this->js = array(
			//_FILES_."/appends/ckeditor/adapters/jquery.js",
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
	
	function getOrCreateCat($arr,$head=15){ //15 - id главного каталога
		$catPar=array();
		$catalog_class_id = 21;
		$cat_field_name=73;
		if(!is_array($arr)){
			if($cat=$this->objects->getFullObjectsListByClass($head,$catalog_class_id,"AND c.field_".$cat_field_name."='".$arr."' LIMIT 1")){
				return $cat['id'];
			}else{
				$newobject=array("class_id"=>$catalog_class_id,"head"=>$head,"active"=>1,"name"=>$arr);
				$newfields=array($cat_field_name=>$arr);
				if($cat=$this->objects->createObject($newobject)){
					$this->objects->createObjectFields($cat,$newfields);
					return $cat;
				}
			}
		}else{
			$cntCats=sizeof($arr)-1;
			foreach($arr as $k=>$a){
				if(trim($a)=='') continue;
				if($k==0){
					$newobject=array("class_id"=>$catalog_class_id,"head"=>$head,"active"=>1,"name"=>$a);
					$newfields=array($cat_field_name=>$a);
					if($cat=$this->objects->getFullObjectsListByClass($head,$catalog_class_id,"AND c.field_".$cat_field_name."='".$a."' LIMIT 1")){
						$catPar[$k]=$cat['id'];
					}else{
						if($newcat=$this->objects->createObject($newobject)){
							$cat['id']=$newcat;
							$catPar[$k]=$cat['id'];
							$this->objects->createObjectFields($newcat,$newfields);
						}
					}
				}else{
					$newobject=array("class_id"=>$catalog_class_id,"head"=>$catPar[$k-1],"active"=>1,"name"=>$a);
					$newfields=array($cat_field_name=>$a);
					if($cat=$this->objects->getFullObjectsListByClass($catPar[$k-1],$catalog_class_id,"AND c.field_".$cat_field_name."='".$a."' LIMIT 1")){
						$catPar[$k]=$cat['id'];
					}else{
						if($newcat=$this->objects->createObject($newobject)){
							$cat['id']=$newcat;
							$catPar[$k]=$cat['id'];
							$this->objects->createObjectFields($newcat,$newfields);
						}
					}
				}
				if($cntCats==$k){ //если уже конечный элемент - останавливаем и возвращаем id категории
					return $cat['id'];
				}
			}
		}
		return false;
	}
	
	function initHTML(){
        error_reporting(0);
        
        $new_tovar = array();
		
        ### поля & config
        $field_name = 'field_47'; // поле товара назание
        $id_1c = '72'; //цифровое id поля для уникального кода товара, хранящийся у клиента в 1С или еще где-нибудь..
        $uniqField=$this->getField($id_1c); //узнаем параметры поля "Уникальный id"
		$id_1c= 'field_'.$id_1c; //тектовое поле "Уникальный id"
		$catname_field='field_73'; //поле "Название" у каталога
		$separ=':'; //разделитель категорий в поле "Категории"
		
        $goods_class_id = 15; //класс товаров
        $catalog_class_id = 21; //класс категорий
		### поля & config
		
		//if( !count($this->base) ) return '<font color="red">Нужно создать подключить к модулю хотябы одну базу пользователей.</font>';
		
        $aval_fields = $this->db->select('fields', "WHERE `class_id`='".$goods_class_id."' ORDER BY sort");
        $display_fields=array();
        
  
        $aval_fields = array_reverse($aval_fields);
		$display_fields['category']='<option value="category">Категория(-и) товара(для нового товара)</option>';
		//$display_fields['object_id']='<option value="object_id">ID товара на сайте</option>';
        foreach ($aval_fields as $field)
        {
            $display_fields['field_'.$field['id']] = '<option value="field_'.$field['id'].'">'.$field['name'].'</option>';
        }
		$tmp_display_fields=$display_fields;
        $out_cur_nab = array();
        $symbol_col = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        if ($cur_nab = $this->db->select("objects", "WHERE `head`='10000' LIMIT 1")){
            $cur_nab = explode(",",$cur_nab['name']);
            
			foreach($tmp_display_fields as $tempf=>$tempval){
				if(in_array($tempf,$cur_nab)){
					unset($tmp_display_fields[$tempf]);
				}
			}
			
          foreach ($cur_nab as $key=>$val)
            {
				
                 $out_cur_nab[] = '<div class="column"><span class="num">'.$symbol_col[$key].'</span><br />
									 <select class="stolbs" style="width: 90px; height: 23px;" name="stolbs[]">';
                                       foreach($display_fields as $field_val=>$field_name_cur){
                                            if ($field_val==$val){
                                                $out_cur_nab[]=$display_fields[$val];
                                                unset($display_fields[$val]);
                                                $out_cur_nab[]=join("\n", $tmp_display_fields);
                                            }
                                       }
                 $out_cur_nab[] ='</select> 
                                    </div>';
            }
            
           
        }
        // post as порядок поля => имя поля 
     if (isset($_POST['stolbs'])){
        // запоминание набора
        if (isset($_POST['nabor']) && $_POST['nabor']=='remember')
        {
            if ($cur_nab = $this->db->select("objects", "WHERE `head`='10000'"))
            {
                $nabor =join(",",$_POST['stolbs']);
                
                $aData = array(
                    'head' => 10000,
                    'class_id' => 10000,
                    'sort' => time(),
                    'active' => 1,
                    'name' => $nabor
                 );
                $this->db->update("objects", $aData,"WHERE `head`='10000'");
            }
            else
            {
                $nabor =join(",",$_POST['stolbs']);
                
                $aData = array(
                    'head' => 10000,
                    'class_id' => 10000,
                    'sort' => time(),
                    'active' => 1,
                    'name' => $nabor
                 );
                $this->db->insert("objects", $aData);
            }
            
        }
               
        // Выбран файл
               
		if(!empty($_FILES['upload_file'])) {

            /* Чтение из тхт файла
            if(end(explode('.',$_FILES['upload_file']['name']))!="txt") $error = "Файл может быть только в формате txt";
            if(!$_FILES['upload_file']['size']) $error = "Ошибка закачки файла";

            if (!$file = file($_FILES['filename']['tmp_name'])) $error = 'Ошибка чтения';
            */

           if(end(explode('.',$_FILES['upload_file']['name']))!="xls") $error = "Файл может быть только в формате Excel 97-2003 (.xls)";
            if(!$_FILES['upload_file']['size']) $error = "Ошибка закачки файла";

			/*iconv_set_encoding("input_encoding", "UTF-8");
			iconv_set_encoding("internal_encoding", "UTF-8");
			iconv_set_encoding("output_encoding", "UTF-8");*/
			
			
			/*$newfile=_FILES_ABS_.'/modules/excel_parser/exceldata.xls';
			
			//move_uploaded_file($_FILES['upload_file']['tmp_name'],$newfile);
			//mb_internal_encoding('utf-8');
			header('Content-Type: application/x-excel; charset="utf-8"');
			header("Content-Transfer-Encoding: binary ");
			$fcontents=file_get_contents($_FILES['upload_file']['tmp_name']);
			file_put_contents($newfile,$fcontents);
					
			chmod($newfile,0777);
			
						
			exit;*/
			
            // Читаем файл 
            $xls_data=new Spreadsheet_Excel_Reader($_FILES['upload_file']['tmp_name']);
            $xls_row_count=$xls_data->rowcount();

            if(!$xls_data || $xls_row_count<2) $error = "Ошибка чтения файла или пустой excel-файл";

            if (empty($error)){
                
                $categories = $this->db->select("objects", "WHERE `class_id`='".(int)$catalog_class_id."'");
                
				$cats = array();


                foreach($categories as $iKey=>$aItem) // Все категории
                {
                    $cats[$aItem['id']] = $aItem['name'];
                    $cats_id[]=$aItem['id'];
                }
                $count_new = 0; // счётчик новых товаров
                $count_upd = 0; // счётчик обновлённых товаров
				$delcount=0; //удаленных
				$nondel=0; //неудалены

				$cols=array();
				
				$ruarr=array("а","б","в","г","д","е","ё","ж","з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х","ц","ч","ш","щ","ъ","ы","ь","э","ю","я");
				

				$numArr=array();
				
				foreach($_POST['stolbs'] as $ks=>$stolb){
					if(strstr($stolb,'field_')){
						$nf=explode("_",$stolb);
						if(is_numeric($nf[1])){
							$field=$this->getField($nf[1]);
							$_POST['stolbs'][$ks]=$field['name'];
							if($field['type']=='number'){
								$numArr[]=$field['name'];
							}
						}
					}
				}
				if(!($uniqCol=array_search($uniqField['name'],$_POST['stolbs']))) {
					return '<p style="color:red;">В excel-файле должно быть поле с уникальным кодом товара.</p>';
				}
				
				if((sizeof($_POST['stolbs'])>1 && (@$_POST['deletethis']=='allgoods')) || (($_POST['deletethis']=='allgoods') && sizeof($_POST['stolbs'])==1 && $_POST['stolbs'][0]!=$id_1c)) return '<div style="color: red;">Для удаления разрешается использовать только одну колонку!<br /> И только со значением "'. $uniqField['name'].'"</div><br /><a href="">обновить страницу</a>';

					
				if(($_POST['deletethis']=='allgoods') && sizeof($_POST['stolbs'])==1 && $_POST['stolbs'][0]==$id_1c){
				
					$nd='';
					if(sizeof($cols)>0){
						foreach($cols as $num_col=>$gid){

							if($id=$this->db->select("class_$goods_class_id", "WHERE ".$id_1c."=".$gid[$id_1c]."","object_id")){
								$this->db->delete("class_$goods_class_id", "WHERE `object_id`='".$id[0]."'");
								$this->db->delete("objects", "WHERE `id`='".$id[0]."' LIMIT 1");
								$delcount++; //удаленных
							}else{
								$nd.=$gid[$id_1c].'<br />';
								$nondel++; //неудалены
							}
						}
						
						echo '<div style="color: green;">Удалено товаров: '.$delcount.'</div>';
						
						if($nondel>0){
							echo '<br /><div style="color:red;">Не было в базе следующих товаров('.$nondel.' шт):<br />
								'.$nd.'
								</div>';
						}
					}else echo '<div style="color:red;">Не было товаров в файле!</div>';
					
					
					return '<br /><a href="">обновить страницу</a>';
				}
				
				if(!($catCol=array_search('category',$_POST['stolbs']))){
					return '<p style="color:red;">В excel-файле должно быть поле с указанием категории товара. Вложенность категорий разделяется "'.$separ.'".</p>';
				}
				

				for($i=2;$i<=$xls_row_count;$i++){ //товары должны начинаться со второй строки
					$cols[$i]=array();
					for($nc=1;$nc<=sizeof($_POST['stolbs']);$nc++){
						/*if(!($uniq=$xls_data->val($i,$uniqCol)) || (trim($uniq)=='') || !($catc=$xls_data->val($i,$catCol)) || (trim($catc)=='')){
							//сразу пропускаем товар, если у него нет уникального кода или нет указания категории, в которую его записывать
							continue;
						}*/
						$enc1=trim(mb_convert_encoding($xls_data->val($i,$nc),'utf-8','auto'));
						$enc2=trim(iconv('windows-1251','utf-8',$xls_data->val($i,$nc)));
						if(trim(str_replace($ruarr,'',mb_strtolower($xls_data->val($i,$nc),'utf-8')))==trim(mb_strtolower($enc1,'utf-8'))){
							if(in_array($_POST['stolbs'][$nc-1],$numArr)){
								$val=(int) ceil(str_replace(array(" ",","),"",$enc2));
							}else{$val=$enc2;}
						}else{
							if(in_array($_POST['stolbs'][$nc-1],$numArr)){
								$val=(int) ceil(str_replace(array(" ",","),"",$enc1));
							}else{$val=$enc1;}
						}
						if($nc==$catCol+1){
							if(strstr($val,$separ)){
								$cats=explode($separ,$val);
								$val=$cats;
							}
						}
						
						$cols[$i][$_POST['stolbs'][$nc-1]]=$val;
					}
					if(empty($cols[$i][$uniqField['name']]) || empty($cols[$i]['category'])){unset($cols[$i]);}
				}
				
				
				
				foreach($cols as $k=>$c){
					
					if($gid=$this->objects->getFullObjectsListByClass(-1,$goods_class_id,"AND c.".$id_1c."='".$c[$uniqField['name']]."'")){
						$newobject=array("id"=>$gid['id'],"class_id"=>$goods_class_id,"active"=>1);
						unset($c[$uniqField['name']],$c['category']);
						$newfields=$c;
						$this->objects->editObjectAndFields($newobject,$newfields);
						//$updCols[]=$c;
						$count_upd++;
					}else{
						if($catId=$this->getOrCreateCat($c['category'])){ /*Здесь по ходу создаем категории которых нет в базе по цепочке.. И возвращаем id непосредственно родительской категории*/
							$newobject=array("class_id"=>$goods_class_id,"head"=>$catId,"active"=>1,"name"=>(@$c['Название'] ? $c['Название'] : 'Товар'));
							unset($c['category']);
							$newfields=$c;
							
							if($co=$this->objects->createObject($newobject)){
								$this->objects->createObjectFields($co,$newfields);
								$count_new++;
							}
						}
						
					}
					
				}


                    
                
                    echo '<div id="info-message" style="margin-bottom:20px;"><font color="green">Обновлено товаров: '.$count_upd.' ед. из excel-файла.</font></div>';
                    echo '<div id="info-message" style="margin-bottom:20px;"><font color="green">Добавлено товаров: '.$count_new.' ед. из excel-файла.</font></div>';
                    
                    if (!empty($new_tovar))
                    {
                                       
                        echo '<div style="text-align:center"><h3>список добавленных товаров</h3>';
                        echo '<table border="1" cellpadding="3" cellspacing="0">';
                        $title=0;
                        foreach ($new_tovar as $num_new_good => $new_good)
                        {
                            if ($title==0)
                            {
                                echo '<tr>';
                                foreach ($new_good as $pole=>$val) 
                                {
                                    echo '<td>'.$pole.'</td>';
                                }
                                echo '</tr>';
                            }
                            
							echo '<tr>';
							foreach ($new_good as $pole=>$val) 
							{
								echo '<td>'.$val.'</td>';
							}
							echo '</tr>';
                            
                            $title++;
                            
                        }
                        echo '</table></div>';
                }

            }
            else{
                    echo '<div id="info-message" style="margin-bottom:20px;"><font color="red">'.$error.'</font></div>';
            }
		}
  
  } 
		//<option value="object_id">ID товара на сайте</option>
		$out = '<div>
		<form class="subscribe-form" id="exel_form" enctype="multipart/form-data" method="post">
			<table class="form-table">
				<tr>
					<td class="left-part">
						<h2>Excel-файл</h2>
						<div>
							<input type="file" id="upload_file" name="upload_file">
							<div id="nastr">
								<p>
									Столбцы в excel файле
									<img id="add_col" style="cursor:pointer;" src="/images/edit_add.png" height="42" />
									<img id="del_col" style="cursor:pointer;" src="/images/edit_remove.png" height="42" />
									<img id="ask" style="cursor:pointer; margin-left:20px;" src="/images/system-help.png" height="42" />
									<a href="/cms/files/modules/excel_parser/sample.xls" style="margin-left:10px;">скачать образец</a>
									<a href="/ru/cat/" target="_blank" style="margin-left:10px;">посмотреть каталог &rarr;</a>
									<br />
									<input type="hidden" id="uniqf" value="'.$uniqField['name'].'" />
									<input style="vertical-align:middle;" type="checkbox" name="nabor" value="remember" id="remember" />Запомнить текущий набор полей<br /><br />
									<input type="checkbox" name="deletethis" value="allgoods" />Удалить товары из базы?
								</p>
								'.((isset($out_cur_nab) && !empty($out_cur_nab))?join("\n", $out_cur_nab):'<div class="column"><span class="num">A</span><br />
									 <select class="stolbs" style="width: 90px; height: 23px;" name="stolbs[]">
								   <option></option>
								   <option value="category">Категория(-и) товара(для нового товара)</option> 
								   '.join("\n", $display_fields).'
									 </select> 
							</div> ').'
                                        
                                    
							</div>
							<div style="display:none;" id="instruction">Инструкция</div>
						</div>
						<p style="clear:both;"></p>
						<div>
							<input type=button class="button approve" id="go_load_exel" value="отправить">
						</div>
					</td>
				</tr>
			</table>
		</form>
	<script type="text/javascript">
		//----- excel file loader
$(function(){
    $("#add_col").click(function(){
        var max_cols = $(".stolbs").last().find("option").size()-1; // максимальное число столбцов
        
        var num_col ="";
        var last_symbol_col = $(".column .num").last().text();
        
        var new_col = $(".column").last().clone();
        
        var symbol_col = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
        $.each(symbol_col,function(i,e){
            //console.log(e + last_symbol_col);
            if (e==last_symbol_col) num_col=i;
            
        });
         
        new_col.find(".num").text(symbol_col[num_col+1]);
		new_col.find(".stolbs option:first").remove();
		var firstDel=new_col.find(".stolbs option:first").val();
		
		$(".column").not(new_col).find(".stolbs option[value=\""+firstDel+"\"]").remove();
		
        if (max_cols!=0) $("#nastr").append(new_col)
        else alert("Вы достигли маскимального количества столбцов");
    });
    
    $("#del_col").click(function(){
		var lastCol=$(".column").last();
        if (lastCol.index()!=1){
			var firstAdd= lastCol.find(".stolbs option:selected");
			firstAdd.removeAttr("selected");
			lastCol.remove();
			$(".column").find(".stolbs option:selected").after(firstAdd);
			$(".column").find(".stolbs option:first").attr("selected","selected");
		}
    });
    $(".stolbs").change(function(){
		if($(".stolbs").not(this).find("option[value=\""+$(this).val()+"\"]:selected").length>0){
			alert("Вы уже выбрали это поле в другой колонке!");
			$(this).find(":first").attr("selected","selected");
		}
    })
    $("#go_load_exel").click(function(){
        var error_load="";
        
        $.each($("#nastr .column"),function(){
            
            if ($(this).find(".stolbs option:selected").val()=="")
            {
                error_load = "Не выбранны поля для загрузки";
                $(this).find("select").click();
                
            }
        })
        
        // Ошибок нет - отправляем
		if (error_load == "")
		{
			$("#exel_form").submit();

		} else {
		  alert(error_load);
		}
    });
    
    $("#ask").click(function(){
    alert("Краткая инструкция: \n 1. Для загрузки товаров через excel нужно задать поля, которые предусмотренны в excel файле. Нажимая на плюс или минус, добавляем или удаляем поля. \n 2. При обновлении и добавлении товара нужно обязательно указать следующие поля: Название, '.$uniqField['name'].', Категория(-и) товара(для нового товара) с разделителем \"'.$separ.'\". \n 3. Для удаления разрешается использовать только одну колонку! И только со значением \"'.$uniqField['name'].'\" \n 4. Список товаров в excel-файле должен начинаться со 2-й строки!");  
   });
   
});
//---end of excel file loader
	</script>
	</div>
<style>
.column {
    text-align:center;
    float:left;
}
#nastr img {
    vertical-align:middle;
}
</style>';


		return $out;
	}
#USER FUNCTIONS
}
?>