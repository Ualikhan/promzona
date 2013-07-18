<?
/*
Title: MYSQL-DB-INTERFACE v 2.3.1 (расчудесный интерфейс по работе с базой данных mysql)
Author: Derevyanko Mikhail <m-derevyanko@ya.ru>
Date: 12.03.2008
*/
class mysql{
	var $HOST;
	var $BASE;
	var $BASE_USER;
	var $BASE_USER_PASS;
	var $link;
	var $debug;
	var $count;
 
	function __construct($HOST='localhost', $BASE='order', $BASE_USER='root', $BASE_USER_PASS=''){
		$this->HOST = $HOST;
		$this->BASE = $BASE;
		$this->BASE_USER = $BASE_USER;
		$this->BASE_USER_PASS = $BASE_USER_PASS;
		$this->link = false;
		$this->debug = false;
		$this->count = 0;
	}
	
	function __destruct(){
		$this->mysql_close();
	}

	function prepare($str){
		if(!is_numeric($str)){
			$str = strtr($str, array_flip(get_html_translation_table()));
			$str = htmlspecialchars($str);
			if(!get_magic_quotes_gpc()) $str = addslashes($str);
		}
		return $str;
	}
	
	function mysql_connect(){
		if( is_resource($this->link) && mysql_ping($this->link) ) return $this->link;
		if( !$this->link = mysql_connect($this->HOST, $this->BASE_USER, $this->BASE_USER_PASS) ) return false;
		mysql_select_db($this->BASE);
		mysql_query("SET NAMES 'utf8'", $this->link);
		
		# Проверка логирования
		if (!file_exists(_CACHE_ABS_.'/logged')) $this->createLogTable();
		
		return $this->link;
	}
	
	function mysql_close(){
		if( is_resource($this->link) ) mysql_close($this->link);
		$this->link = false;
		return true;
	}
	
	function mysql_query($sql){
		if( $this->mysql_connect() ){
			$this->count++;
			if( $this->debug ) echo $sql."<br>\n";
			if(!$view = mysql_query($sql, $this->link)) echo mysql_error($this->link);
			return $view;
		}else return false;
	}

    function superQuery($query){
        $link=$this->mysql_query($query);
        if ($res=mysql_fetch_array($link))
        return $res;
    }
    	
	function select($table, $where, $what='*'){
		$r = $this->mysql_query("SELECT ".$what." FROM ".$table." ".$where);
		$out = array();
		if(mysql_num_rows($r)){
			
			if(preg_match("/limit\s+1$/i", $where)){ 
				if(mysql_num_rows($r) == 1 && mysql_num_fields($r) == 1) return mysql_result($r, 0);
				return mysql_fetch_array($r, MYSQL_ASSOC);
			}
			if(!strstr($what, '*') && mysql_num_fields($r) == 1){
				$i=0;
				while($i<mysql_num_rows($r)){
					$out[]=mysql_result($r, $i++);
				}
			}else{
				while($o = mysql_fetch_array($r, MYSQL_ASSOC)){
					$out[]=$o;
				}
			}
			mysql_free_result($r);//странно, но пишут что функция освобождения памяти сама жрёт память. Сука засада.
		}
		return $out;
	}
	
	function count($table, $where="", $what='*'){
		return mysql_result($this->mysql_query("SELECT COUNT(".$what.") FROM ".$table." ".$where), 0);
	}
    

    function getFieldName($id){
        $link=$this->mysql_query("SELECT * FROM `fields` WHERE `id`='".$id."'");
        if ($res=mysql_fetch_array($link))
            return $res['name'];
    }
 
                               
	function insert($table, $params){

	    // ЛОГИРОВАНИЕ
        if (
			!empty($_SESSION['cms_root_auth'])
			&& preg_match('/^class_([0-9]{1,5})$/', $table, $class_id_match)
			)
		{	
			# ID класса объекта
			$class_id = $class_id_match[1];
		
			# Тип объекта
			$object_type = mysql_result(mysql_query("SELECT `name` FROM `classes` WHERE `id`='$class_id'"), 0, 'name');
			
			# URL в зависимости от класса объекта
			switch($class_id)
			{
                case 1:  $url = '/'.$params['lang'].'/pages/'.$params['object_id'].'.html'; 		break;
                case 3:  $url = '/'.$params['lang'].'/pages/'.$params['object_id'].'.html'; 		break;
                case 4:  $url = '/'.$params['lang'].'/pages/'.mysql_result(mysql_query("SELECT `head` FROM `objects` WHERE `id`='".$params['object_id']."' LIMIT 1"), 0, 'head').'.html'; 		break;
                case 5:  $url = '/'.$params['lang'].'/pages/'.mysql_result(mysql_query("SELECT `head` FROM `objects` WHERE `id`='".$params['object_id']."' LIMIT 1"), 0, 'head').'.html'; 		break;
				case 8:  $url = '/'.$params['lang'].'/news/'.$params['object_id'].'/'; 				break;
                case 15: $url = '/cat.php?id='.$params['object_id'].'&lang='.$params['lang']; 		break;
                default: $url = ''; 																break;
            }
			
			# Если лога сегодня нет к этому объекту
			if (mysql_num_rows(mysql_query("SELECT `id` FROM `clogs` WHERE `object_id`='".$params['object_id']."' AND `day`='".date('Y-m-d')."' LIMIT 1")) == 0)
			{
				mysql_query("
					INSERT INTO 
						`clogs` 
					(`object_id`, `day`, `action`, `gmadate_ts`, `object_type`, `url`)
					VALUES
					('".$params['object_id']."', NOW(), '1', '".time()."', '$object_type', '$url')
				");
			}
        }
        // ЛОГИРОВАНИЕ END

		$str1 = array();
		$str2 = array();
		foreach($params as $name => $value){
			$str1[]= "`".$name."`";
			$str2[]= "'".$this->prepare($value)."'";
		}
    
		return $this->mysql_query("INSERT INTO ".$table." (".join(", ", $str1).") VALUES (".join(", ", $str2).")");
	}
	
	function update($table, $update, $where){
	
        // ЛОГИРОВАНИЕ
        if (
			!empty($_SESSION['cms_root_auth'])
			&& preg_match('/^class_([0-9]{1,5})$/', $table, $class_id_match)
			&& preg_match("/`object_id`='(\d+)'/Uis", $where, $object_id_match)
			&& preg_match("/`lang`='(.*)'/Uis", $where, $lang_match)
			)
		{	
			# ID класса объекта
			$class_id = $class_id_match[1];
		
			# Атрибуты объекта
			$atributes = array(		
				'object_id' 	=> $object_id_match[1],
				'lang'			=> $lang_match[1],
				'object_type'	=> @mysql_result(mysql_query("SELECT `name` FROM `classes` WHERE `id`='$class_id'"), 0, 'name')
			);

			# URL в зависимости от класса объекта
			switch($class_id)
			{
                case 1:  $atributes['url'] = '/'.$atributes['lang'].'/pages/'.$atributes['object_id'].'.html'; 		break;
                case 3:  $atributes['url'] = '/'.$atributes['lang'].'/pages/'.$atributes['object_id'].'.html'; 		break;
                case 4:  $url = '/'.$params['lang'].'/pages/'.mysql_result(mysql_query("SELECT `head` FROM `objects` WHERE `id`='".$params['object_id']."' LIMIT 1"), 0, 'head').'.html'; 		break;
                case 5:  $url = '/'.$params['lang'].'/pages/'.mysql_result(mysql_query("SELECT `head` FROM `objects` WHERE `id`='".$params['object_id']."' LIMIT 1"), 0, 'head').'.html'; 		break;
				case 8:  $atributes['url'] = '/'.$atributes['lang'].'/news/'.$atributes['object_id'].'/'; 			break;
                case 15: $atributes['url'] = '/cat.php?id='.$atributes['object_id'].'&lang='.$atributes['lang']; 	break;
                default: $atributes['url'] = ''; 																	break;
            }
			
			# Если лога сегодня нет к этому объекту
			if (@mysql_num_rows(mysql_query("SELECT `id` FROM `clogs` WHERE `object_id`='".$atributes['object_id']."' AND `day`='".date('Y-m-d')."' LIMIT 1")) == 0)
			{
				mysql_query("
					INSERT INTO 
						`clogs` 
					(`object_id`, `day`, `action`, `gmadate_ts`, `object_type`, `url`)
					VALUES
					('".$atributes['object_id']."', NOW(), '2', '".time()."', '".$atributes['object_type']."', '".$atributes['url']."')
				");
			}
        }
        // ЛОГИРОВАНИЕ END
		
		$str = array();
		foreach($update as $name => $value){
			$str[]= "`".$name."`='".$this->prepare($value)."'";
		}
		return $this->mysql_query("UPDATE ".$table." SET ".join(", ", $str)." ".$where);
	}
	
	function delete($table, $where){
	
        // ЛОГИРОВАНИЕ
        if (
			!empty($_SESSION['cms_root_auth'])
			&& preg_match('/^class_([0-9]{1,5})$/', $table, $class_id_match)
			&& preg_match("/`object_id`='(\d+)'/Uis", $where, $object_id_match)
			)
		{
			# ID класса объекта
			$class_id = $class_id_match[1];
		
			# Атрибуты объекта
			$atributes = array(		
				'object_id' 	=> $object_id_match[1],
				'object_type'	=> mysql_result(mysql_query("SELECT `name` FROM `classes` WHERE `id`='$class_id'"), 0, 'name')
			);
			
			# Владелец
			$head = mysql_result(mysql_query("SELECT `head` FROM `objects` WHERE `id`='".$atributes['object_id']."' LIMIT 1"), 0, 'head');
			if ($head != 0)
			{
				switch($class_id)
				{
					case 3:  $url = '/ru/pages/'.$head.'.html'; 		break;
					case 4:  $url = '/ru/pages/'.$head.'.html'; 		break;
					case 5:  $url = '/ru/pages/'.$head.'.html'; 		break;
					case 15: $url = '/cat.php?cat='.$head.'&lang=ru'; break;
				}
			}
			else $url = '';
			
			# Если лога сегодня нет к этому объекту
			if (mysql_num_rows(mysql_query("SELECT `id` FROM `clogs` WHERE `object_id`='".$atributes['object_id']."' AND `day`='".date('Y-m-d')."' LIMIT 1")) == 0)
			{
				mysql_query("
					INSERT INTO 
						`clogs` 
					(`object_id`, `day`, `action`, `gmadate_ts`, `object_type`, `url`)
					VALUES
					('".$atributes['object_id']."', NOW(), '3', '".time()."', '".$atributes['object_type']."', '$url')
				");
			}
        }
        // ЛОГИРОВАНИЕ END
		
		return $this->mysql_query("DELETE FROM ".$table." ".$where);
	}

	# СОЗДАНИЕ ТАБЛИЦЫ ЛОГОВ
    function createLogTable(){
	
		@mysql_query("DROP TABLE `logs`");
		mysql_query("
			CREATE TABLE IF NOT EXISTS `clogs` (
			`id` int(11) NOT NULL auto_increment,
			`object_id` int(10) NOT NULL,
			`day` date NOT NULL,
			`action` tinyint(1) NOT NULL,
			`gmadate_ts` int(15) NOT NULL,
			`object_type` varchar(100) NOT NULL,
			`url` varchar(200) NOT NULL,
			PRIMARY KEY  (`id`),
			KEY `object_id` (`object_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		");
		
		file_put_contents(_CACHE_ABS_.'/logged', md5('go-web'));
    }	

}#CLASS
?>
