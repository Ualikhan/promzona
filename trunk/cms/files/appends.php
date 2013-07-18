<?
class appends{
var $db;
var $dbname;
	
	function __construct(){
		$this->dbname = 'promzona';
		$this->db = new mysql('localhost', $this->dbname, 'promzona', 'pOOu1XcOhI2h');
	}
	
	function vd($text){		
		echo '<pre>';
		print_r($text);
		echo '</pre>';
	}
	
	function auth(){
		return @$this->checkAuth( $_SESSION['cms_root_auth']['u'], $_SESSION['cms_root_auth']['p']);
	}
	
	function checkAuth( $l, $p ){
		if( !file_exists(_CACHE_ABS_.'/auth.php') || !(include(_CACHE_ABS_.'/auth.php')) ){ 
			$this->changeAuth('root', '123');
			return false;
		}
		if( $l!=$login || md5($p)!=$pass) return false;
		return true;
	}
	
	function changeAuth($l, $p){
		$cache = array('<?');
		$cache[]='$login="'.str_replace('"', '', $l).'";';
		$cache[]='$pass="'.md5($p).'";';
		$cache[]='?>';
		if( file_put_contents( _CACHE_ABS_.'/auth.php', join("\n", $cache)) && chmod(_CACHE_ABS_.'/auth.php', 0666)) return 'ok';
		return 'error';
	}
	
	function areaJS( $text_or_array ){
		if(is_array($text_or_array)) $text_or_array = join("\n", $text_or_array);
		return '<script type="text/javascript">'.$text_or_array.'</script>'."\n";
	}
	
	function initJS(){
		if(!$this->js) return null;
		$out = array();
		foreach($this->js as $file){
			$out[]='<script type="text/javascript" src="'.$file.'"></script>';
		}
		return join("\n", $out)."\n";
	}
	
	function initCSS(){
		if(!$this->css) return null;
		$out = array();
		foreach($this->css as $file){
			$out[]='@import url("'.$file.'");';
		}
		return join("\n", $out)."\n";
	}
	
	function newError( $str ){
		$this->errors[]=$str;
		return true;
	}
	
	function callErrors(){
		if( !$this->errors ) return '';
		return '<div class="error"><div>'.join('</div><div>', $this->errors).'</div></div><br>';
	}
	
	function lower($text){
		$UP_CASE=array('A'=>'a', 'B'=>'b', 'C'=>'c', 'D'=>'d', 'E'=>'e', 'F'=>'f', 'G'=>'g', 'H'=>'h', 'I'=>'i', 'J'=>'j', 'K'=>'k', 'L'=>'l', 'M'=>'m', 'N'=>'n', 'O'=>'o', 'P'=>'p', 'Q'=>'q', 'R'=>'r', 'S'=>'s', 'T'=>'t', 'U'=>'u', 'V'=>'v', 'W'=>'w', 'X'=>'x', 'Y'=>'y', 'Z'=>'z', 'А'=>'а', 'Б'=>'б', 'В'=>'в', 'Г'=>'г', 'Д'=>'д', 'Е'=>'е', 'Ё'=>'ё', 'Ж'=>'ж', 'З'=>'з', 'И'=>'и', 'Й'=>'й', 'К'=>'к', 'Л'=>'л', 'М'=>'м', 'Н'=>'н', 'О'=>'о', 'П'=>'п', 'Р'=>'р', 'С'=>'с', 'Т'=>'т', 'У'=>'у', 'Ф'=>'ф', 'Х'=>'х', 'Ц'=>'ц', 'Ч'=>'ч', 'Ш'=>'ш', 'Щ'=>'щ', 'Ъ'=>'ъ', 'Ы'=>'ы', 'Ь'=>'ь', 'Э'=>'э', 'Ю'=>'ю', 'Я'=>'я');
		return strtr($text,  $UP_CASE);
	}

	function upper($text){
		$LOW_CASE=array("a"=>"A", "b"=>"B", "c"=>"C", "d"=>"D", "e"=>"E", "f"=>"F", "g"=>"G", "h"=>"H", "i"=>"I", "j"=>"J", "k"=>"K", "l"=>"L", "m"=>"M", "n"=>"N", "o"=>"O", "p"=>"P", "q"=>"Q", "r"=>"R", "s"=>"S", "t"=>"T", "u"=>"U", "v"=>"V", "w"=>"W", "x"=>"X", "y"=>"Y", "z"=>"Z", "а"=>"А", "б"=>"Б", "в"=>"В", "г"=>"Г", "д"=>"Д", "е"=>"Е", "ё"=>"Ё", "ж"=>"Ж", "з"=>"З", "и"=>"И", "й"=>"Й", "к"=>"К", "л"=>"Л", "м"=>"М", "н"=>"Н", "о"=>"О", "п"=>"П", "р"=>"Р", "с"=>"С", "т"=>"Т", "у"=>"У", "ф"=>"Ф", "х"=>"Х", "ц"=>"Ц", "ч"=>"Ч", "ш"=>"Ш", "щ"=>"Щ", "ъ"=>"Ъ", "ы"=>"Ы", "ь"=>"Ь", "э"=>"Э", "ю"=>"Ю", "я"=>"Я");
		return strtr($text,  $LOW_CASE);
	}
	
	function sklon($num, $arr){
		if($num==1) $out = $arr[0];
		else if($num>=2 && $num<=4) $out = $arr[1];
		else if(($num>=5 && $num <=19) or $num==0) $out = $arr[2];
		else{
			$num1 = substr($num,-1,1);
			$num2 = substr($num,-2,1);
			if($num2==1) $out = $arr[2];
			else if($num1==1) $out = $arr[0];
			else if($num1>=2 && $num1<=4) $out = $arr[1];
			else if(($num1>=5 && $num1 <=9) or $num1==0) $out = $arr[2];
		}
		return $num." ".$out;
	}
	
	function pages($total_count, $on_one_page, $view_page_links_per_side=5, $page_attributes=array(), $url = false, $lang = 'ru'){
		$vars = array(
			'ru' => array(
				'в начало' => 'в начало',
				'предыдущая' => 'предыдущая',
				'следующая' => 'следующая',
				'в конец' => 'в конец',
			),
			'en' => array(
				'в начало' => 'to begin',
				'предыдущая' => 'back',
				'следующая' => 'next',
				'в конец' => 'to end',
			),
			'kz' => array(
				'в начало' => 'басына',
				'предыдущая' => 'ілгері',
				'следующая' => 'алға',
				'в конец' => 'соңына',
			),
			
		);
		$out_html = '';
		if(!$total_count || !$on_one_page || $total_count == 0) array('html'=>'', 'start'=>'0');
		if(!isset($_REQUEST['pg']) || !is_numeric($current_page = $_REQUEST['pg']) || $current_page>ceil($total_count/$on_one_page)) $current_page = 1;
		$attrs = array();
		foreach($page_attributes as $k=>$v){
			if ($k != 'pg') { $attrs[]=$k.'='.$v; }
		}
		$attrs = join("&", $attrs);
		
		$index = $view_page_links_per_side;
		$count_pages = ceil( $total_count/$on_one_page );
		$start_from  = ( $current_page * $on_one_page ) - $on_one_page;
		
		if($count_pages < 2) return array('html'=>'', 'start'=>'0');
		
		$start = 1; $end = ($index*2+1<=$count_pages?$index*2+1:$count_pages);
		if($current_page>$index+1){
			if($current_page+$index<=$count_pages){
				$start = $current_page-$index;
				$end = $current_page+$index;
			}else{
				$start = $current_page - ($index*2-($count_pages - $current_page))<=0?1:$current_page - ($index*2-($count_pages - $current_page));
				$end = $count_pages;
			}
		}
		
		$html = array();
		if($current_page>1){ 
			if($start>1){ 
				if(!$url){ 
					$html[]='<a class="afirst" href="?pg=1'.($attrs?'&'.$attrs:'').'">'.$vars[$lang]['в начало'].'</a>';
				}else $html[]='<a class="afirst" href="'.str_replace("#pg#", 1, $url).'">'.$vars[$lang]['в начало'].'</a>';
			}
			if(!$url){ 
				$html[]='<a class="afirst" href="?pg='.($current_page-1).($attrs?'&'.$attrs:'').'">'.$vars[$lang]['предыдущая'].'</a>';
			}
			else $html[]='<a class="afirst" href="'.str_replace("#pg#", ($current_page-1), $url).'">'.$vars[$lang]['предыдущая'].'</a>';
		}
		
		for($i = intval($start); $i<=intval($end); $i++){
			if($i==$current_page) $html[]='<a class="active" href="javascript:void(0)">'.$i.'</a>';
			else{ 
				if(!$url){
					$html[]='<a href="?pg='.($i).($attrs?'&'.$attrs:'').'">'.$i.'</a>';
				}else $html[]='<a href="'.str_replace("#pg#", $i, $url).'">'.$i.'</a>';
			}
		}
		
		if($current_page!=$count_pages){ 
			if(!$url){ 
				$html[]='<a class="alast" href="?pg='.($current_page+1).($attrs?'&'.$attrs:'').'">'.$vars[$lang]['следующая'].'</a>';
			}
			else $html[]='<a class="alast" href="'.str_replace("#pg#", ($current_page+1), $url).'">'.$vars[$lang]['следующая'].'</a>';
			if($end!=$count_pages){ 
				if(!$url){
					$html[]='<a class="alast" href="?pg='.$count_pages.($attrs?'&'.$attrs:'').'">'.$vars[$lang]['в конец'].'</a>';
				}else $html[]='<a class="alast" href="'.str_replace("#pg#", ($count_pages-1), $url).'">'.$vars[$lang]['в конец'].'</a>';
			}
		}
		$from = (($current_page-1)*$on_one_page+1);
		$to = ($current_page-1)*$on_one_page+$on_one_page;
		// $html[]='<div>показано '.$from.'&ndash;'.($to>$total_count?$total_count:$to).' из '.$total_count.'</div>';
		
		$out_html = '<div class="navi">'.join("\n", $html).'</div>';

		return array('html'=>$out_html, 'start'=>$start_from);
	}
		
	function json($a=false){
		if(is_null($a)) return 'null';
		if($a === false) return 'false';
		if($a === true) return 'true';
		if(is_scalar($a)){
			if(is_float($a)){
				return floatval(str_replace(",", ".", strval($a)));
			}elseif(is_numeric($a) && !strstr($a,'+') && !strstr($a,'-')){
				return $a;
			}else{
				$jsonReplaces = array("\\"=>'\\\\', "/"=>'\\/', "\n"=>'\\n', "\t"=>'\\t', "\r"=>'\\r', "\b"=>'\\b', "\f"=>'\\f', '"'=>'\"');
				return '"'.strtr($a, $jsonReplaces).'"';
			}
		}else if( !is_array($a) ) return false;
		$isList = true;
		$checkIndex = 0;
		foreach($a as $k=>$v){
			if( !is_numeric($k) || $k!=$checkIndex++ ){
				$isList = false;
				break;
			}
		}
		$result = array();
		if($isList){
			foreach ($a as $v) $result[] = $this->json($v);
			return '[' . join(',', $result) . ']';
		}else{
			foreach ($a as $k => $v) $result[] = $this->json($k).':'.$this->json($v);
			return '{' . join(',', $result) . '}';
		}
	}
	
	function getFileExtension($fileName){
		return substr($fileName, strrpos($fileName, '.' )+1);
	}
	
	function transliterate($string)
    {
      $cyr=array(
         "Щ",  "Ш", "Ч", "Ц","Ю", "Я", "Ж", "А","Б","В","Г","Д","Е","Ё","З","И","Й","К","Л","М","Н","О","П","Р","С","Т","У","Ф","Х", "Ь","Ы","Ъ","Э","Є","Ї",
         "щ",  "ш", "ч", "ц","ю", "я", "ж", "а","б","в","г","д","е","ё","з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х", "ь","ы","ъ","э","є","ї"
      );
      $lat=array(
         "Shh","Sh","Ch","C","Ju","Ja","Zh","A","B","V","G","D","Je","Jo","Z","I","J","K","L","M","N","O","P","R","S","T","U","F","Kh","'","Y","`","E","Je","Ji",
         "shh","sh","ch","c","ju","ja","zh","a","b","v","g","d","je","jo","z","i","j","k","l","m","n","o","p","r","s","t","u","f","kh","'","y","`","e","je","ji"
      );
      for($i=0; $i<count($cyr); $i++)
      {
         $c_cyr = $cyr[$i];
         $c_lat = $lat[$i];
         $string = str_replace($c_cyr, $c_lat, $string);
      }
      $string = preg_replace("/([qwrtpsdfghklzxcvbnmQWRTPSDFGHKLZXCVBNM]+)[jJ]e/", "\${1}e", $string);
      $string = preg_replace("/([qwrtpsdfghklzxcvbnmQWRTPSDFGHKLZXCVBNM]+)[jJ]/", "\${1}'", $string);
      $string = preg_replace("/([eyuioaEYUIOA]+)[Kk]h/", "\${1}h", $string);
      $string = preg_replace("/^kh/", "h", $string);
      $string = preg_replace("/^Kh/", "H", $string);
      return $string;
   }

   function urlTranslit($string)
   {
      $string = preg_replace("/[_\s\.,?!\[\](){}]+/", "_", $string);
      $string = preg_replace("/-{2,}/", "__", $string);
	  $string = preg_replace("/-/", "_", $string);
      $string = preg_replace("/_-+_/", "__", $string);
      $string = preg_replace("/[_\-]+$/", "", $string);
      $string = $this->transliterate($string);
      $string = StrToLower($string);
      $string = preg_replace("/j{2,}/", "j", $string);
      $string = preg_replace("/[^0-9a-z_\-]+/", "", $string);
	  $string = (is_numeric($string)?$string.'_':$string);
      return $string;
   }
}
?>
