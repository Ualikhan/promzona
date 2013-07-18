<?
class backup extends appends{
private $path;

public $title;
public $info;
protected $js;
protected $css;
public $ico;

public $base;
protected $mail;

	function __construct(){
		parent::__construct();
		#PRIVATE
		$this->path = _MODULES_.'/'.basename(dirname(__FILE__));
		#CONFIG
		$this->title = 'Backup сайта';
		$this->info = 'Создание полной копии сайта в одном zip архиве.';
		$this->js = array(
			$this->path."/js/init.js"
		);
		$this->css = array(
			$this->path."/css/page.css"
		);
		
	}
	
	function ajax(){
		global $api;
		
		if( @!$go=$_REQUEST['go'] ) return '';
		
		switch( $go ){
			case 'loadBackups': return json_encode( $this->getBackups() );
			case 'createBackup': return $this->createBackup();
			case 'deleteBackup': return $this->deleteBackup($_REQUEST['oname']);
		}
	}
	
	function initHTML(){
		return '<div id="backupsArea" class="object-item"></div>';
	}
	
	#USER FUNCTIONS
	function getBackups(){
		$names = array();
		$sizes = array();
		$dates = array();
		for($d = @opendir($_SERVER['DOCUMENT_ROOT'].'/backup/'); $file = @readdir($d);)
			if($file!='.' && $file!='..'){
				$file_path = $_SERVER['DOCUMENT_ROOT'] . '/backup/' . $file;
				$names[] = $file;
				$sizes[] = number_format( ceil( filesize( $file_path ) / 1024), 0, '', ' ' );
				$dates[] = date("d.m.Y H:i:s", filemtime( $file_path ));
			}
		
		return array('name' => array_reverse($names), 'date' => array_reverse($dates), 'size' => array_reverse($sizes));
	}
	
	function createBackup(){
		$res = '';
		include_once _MODULES_ABS_.'/'.basename(dirname(__FILE__)).'/backup_db.php';
		
		if (!isset($name) || empty($name)) return 'Ошибка при создании бэкапа базы';

		$name_backup = $_SERVER['DOCUMENT_ROOT'] . '/backup/' . 'backup_' . date("Y-m-d_H-i-s").'.zip';
		$files_dir = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
		$files_to_arch = array();

		require_once rtrim(str_replace('\\', '/', dirname(__FILE__)), '/') . '/pclzip.lib.php';

		for($d = @opendir($files_dir); $file = @readdir($d);)
			if($file!='.' && $file!='..')
				$files_to_arch[]= $file;

		chdir($files_dir);
		
		$backup = new PclZip($name_backup);
		$v_list = $backup->create(join(',', $files_to_arch));

		if($v_list == 0)
		   $res = "Error : ".$backup->errorInfo(true);
		else
		{
			$backup->delete(PCLZIP_OPT_BY_EREG, '/backup_(.*?).zip/');
			$backup->delete(PCLZIP_OPT_BY_EREG, '/db_b_(.*?).sql/');
			if ($backup->add(	$_SERVER['DOCUMENT_ROOT'] . '/backup/' . $name . '.sql',
								PCLZIP_OPT_REMOVE_ALL_PATH,
								PCLZIP_OPT_ADD_PATH, 'db_backup')){
				@unlink($_SERVER['DOCUMENT_ROOT'] . '/backup/' . $name . '.sql');
				$res = 'success';
			} else $res = "Error : ".$backup->errorInfo(true);
		}
			
		return $res;
	}
	
	function deleteBackup($oname){
		return (unlink($_SERVER['DOCUMENT_ROOT'] . '/backup/' . $oname)? 'deleted' : 'error');
	}
}
?>