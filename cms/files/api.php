<?
include_once(_FILES_ABS_."/mysql.php");
include_once(_FILES_ABS_."/mail.php");
include_once(_FILES_ABS_."/appends.php");
class api extends appends{	
	var $modules;
	var $activeName;
	var $sort;
	var $errors;
	
	function __construct( $module='content' ){
		parent::__construct();
		#ЕСЛИ В ЭТОМ МАССИВЕ НЕ ЗАРЕГИСТРИРОВАТЬ МОДУЛЬ, ОН НЕ ПОДКЛЮЧИТСЯ
		$this->sort = array(
			'content'=>array('color'=>'orange'),
			'subscribe'=>array('color'=>'purple'),
			// 'excel_parser'=>array('color'=>'purple'),
			// 'tests'=>array('color'=>'#CC0000')
		);
		
		$this->initModules();
		$this->activate( $module );
		$this->errors = array();
	}
	
	function __get($m){
		switch($m){
			case 'active': return $this->modules[ $this->activeName ];
			default:
				return null;
				break;
		}
	}
	
	function activate( $module ){
		if(!array_key_exists($module, $this->modules)) return false;
		$this->activeName = $module;
		return true;
	}
	
	function initModules(){
		$this->modules = array();
		
		if( file_exists(_CACHE_ABS_.'/modules.php') && include_once(_CACHE_ABS_.'/modules.php') ) return true;
		
		$cache = array('<?', '$updated='.time().';');
		$dir = opendir( _MODULES_ABS_ );
		while( $file = readdir( $dir ) ){
			if( in_array($file, array('.', '..')) ) continue;
			if( is_dir(_MODULES_ABS_.'/'.$file) ){
				$cache[]='include_once(_MODULES_ABS_."/'.$file.'/index.php");';
				$cache[]='$this->modules["'.$file.'"] = new '.$file.';';
			}
		}
		closedir( $dir );
		$cache[]='?>';
		
		file_put_contents( _CACHE_ABS_.'/modules.php', join("\n", $cache));
		$this->initModules();
	}
	
	function initModulesList(){
		$out = array();
		
		foreach($this->sort as $key => $config){
			if(!array_key_exists($key, $this->modules) || !!@$this->modules[$key]->hidden) continue;
			$class = $this->modules[$key];
			if($key==$this->activeName) $out[]='<span'.(!empty($config['color'])?' style="background-color:'.$config['color'].';"':'').'><div class="rc"></div>'.$class->title.'</span>';
			else $out[]='<a href="?run='.$key.'">'.$class->title.'</a>';
		}
		return count($out)>1 ? join(" ", $out) : '';
	}
	
	final function initJS(){
		return $this->active->initJS();
	}
	
	final function initCSS(){
		return $this->active->initCSS();
	}
	
	function ajax(){
		if(isset($_REQUEST['changeAuth']) && is_array($auth = $_REQUEST['changeAuth']) && !empty($auth['l']) && !empty($auth['p'])){
			return $this->changeAuth($auth['l'], $auth['p']);
		}
		return $this->active->ajax();
	}
	
	function initHTML(){
		return $this->callErrors().$this->active->initHTML();
	}
}
?>