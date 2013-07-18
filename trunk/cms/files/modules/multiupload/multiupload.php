<?

ini_set('max_execution_time',720);
header('Content-Type: text/html; charset=utf-8');
include('../../../public/api.php');

if(!$api->auth()){
	exit('<h1>Access Denied!</h1>');
	}
	
function ru2Lat($str){
	$ruarr=array("а","б","в","г","д","е","ё","ж","з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х","ц","ч","ш","щ","ъ","ы","ь","э","ю","я"," ", "\\","/",":","*","?",'"',"<",">","|","'","«","»");
	$enarr=array("a","b","v","g","d","e","jo","j","z","i","j","k","l","m","n","o","p","r","s","t","u","f","h","ts","ch","sh","sch","","y","","e","ju","ja","_", "","","","","",'',"","","","","","");
	return str_replace($ruarr, $enarr, $str);
}



# UPLOAD
$status = '404 Not Found';
if(!empty($_FILES)) {
    // Файл передан через обычный массив $_FILES
    //echo "HEAD--".$_GET['names'].'---'.$_GET['head'];
    $file = $_FILES['mypic'];
	//die('sdf');
    // Внимание! Имя файла для Blob-данных может различаться в разных браузерах
    //if ( ($file['type'] == 'image/png' || $file['type'] == 'image/jpeg' || $file['type'] == 'image/bmp' || $file['type'] == 'image/gif')) {
	
				//die(@$_GET['browser'].'--');
				if(@$_GET['browser'] == 'ie'){
					
					/*
					print_r($_FILES['mypic']);
					die();
					
					 [name] => Array ( [0] => mix_a_48.jpg [1] => mix_a_40.jpg [2] => mix_a_47.jpg ) 
					 [type] => Array ( [0] => image/jpeg [1] => image/jpeg [2] => image/jpeg ) 
					 [tmp_name] => Array ( [0] => Z:\tmp\phpB6CD.tmp [1] => Z:\tmp\phpB6CE.tmp [2] => Z:\tmp\phpB6DF.tmp ) 
					 [error] => Array ( [0] => 0 [1] => 0 [2] => 0 ) 
					 [size] => Array ( [0] => 60495 [1] => 49727 [2] => 71297 )
					*/
					
					for($n=0;$n<(count($file['name']));$n++){
						$fname = $file['name'][$n];
						$ftype = $file['type'][$n];
						$ftmpname = $file['tmp_name'][$n];
						$ferror = $file['error'][$n];
						$fsize = $file['size'][$n];
						if(!$ftmpname) continue;
							$type = '';
							if(preg_match("/\.([^\.]+)$/", $fname, $ok)) $type = $api->lower($ok[1]);
							$new_name = "file_".time()."_".rand(0, 1000000000).($type ? ".".$type : "");
							if(move_uploaded_file($ftmpname, _UPLOADS_ABS_."/".$new_name)){	
								
								if(isset($_GET['names'])){
									$rusname_e = explode('.',$fname);	
									$rusname = array_slice($rusname_e, 0, sizeof($rusname_e)-1); 	
									$rusname = join(' ',$rusname);	
								}else{
									$rusname = '';
								}
													
								$object = array(
								 "active"=>1,
								 "head"=>$_GET['head'],
								 "name"=>$new_name,
								 "class_id"=>4,
								 "sort"=>time()
								);
								$fields=array(8=>$rusname,9=>$new_name);
								
								//задержка 
								usleep(200000);
								$api->objects->createObjectAndFields($object,$fields);
								//echo 'Contents of $_FILES:<br/><pre>'.print_r($_FILES, true).'</pre>';
								//echo $n.'-yes <br>';
							}
						
					}
					//die('end!');
					// if no errors					
					if(@$_GET['back']!==''){
									$e = explode('#',@$_GET['back']);
									header("location: ".$e[0]."#".$e[1]);
									}else{
										die('Просто вернитесь назад...');
									}
					
					
					
				}else{
			
					$out = array();				
					//foreach($_FILES as $f){
					//	if(!$f['tmp_name']) continue;
						$f = $_FILES['mypic'];
						$type = '';
						if(preg_match("/\.([^\.]+)$/", $f['name'], $ok)) $type = $api->lower($ok[1]);
						$new_name = "file_".time()."_".rand(0, 1000000000).($type ? ".".$type : "");
						if(move_uploaded_file($f['tmp_name'], _UPLOADS_ABS_."/".$new_name)){
							//echo @$_GET['names'].'---';
							if(isset($_GET['names'])){
									$rusname_e = explode('.',$f['name']);	
									$rusname = array_slice($rusname_e, 0, sizeof($rusname_e)-1); 	
									$rusname = join(' ',$rusname);
								}else{
									$rusname = '';
								}
							
							$object = array(
							 "active"=>1,
							 "head"=>$_GET['head'],
							 "name"=>$new_name,
							 "class_id"=>4,
							 "sort"=>time()
							);
							$fields=array(8=>$rusname,9=>$new_name);
							
							$api->objects->createObjectAndFields($object,$fields);
							//echo 'Contents of $_FILES:<br/><pre>'.print_r($_FILES, true).'</pre>';
						}
						//$out['file-'.$k]=$new_name;				
					//}
					
				}
        //move_uploaded_file($file['tmp_name'], './canvas-' . uniqid() . '.png');
    //}
}else{
	// Имитируем ошибку каждый четвертый раз
    header("HTTP/1.0 {$status}");
    header("HTTP/1.1 {$status}");
    header("Status: {$status}");
    die();
}


?>
