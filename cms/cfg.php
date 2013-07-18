<?
date_default_timezone_set('Asia/Almaty');

#SET ROOT PATH#######
$path = str_replace("\\", "/", dirname(__FILE__));
define('_ROOT_', str_replace($_SERVER['DOCUMENT_ROOT'], "", $path));
define('base_url', str_replace('cms', "", _ROOT_));

#WWW
define('_HTTP_HOST_', "http://".$_SERVER['HTTP_HOST']);

#AUTO PATHS
define('_MODS_', _ROOT_."/mods");
define('_FILES_', _ROOT_."/files");
define('_MODULES_', _FILES_."/modules");
define('_CACHE_', _FILES_."/cache");
define('_UPLOADS_', _ROOT_."/uploads");
define('_PUBLIC_', _ROOT_."/public");
define('_WWW_', _ROOT_."/..");
define('_HTML_', _PUBLIC_."/html");

#AUTO ABS PATHS
define('_MODS_ABS_', $_SERVER['DOCUMENT_ROOT']._MODS_);
define('_ROOT_ABS_', $_SERVER['DOCUMENT_ROOT']._ROOT_);
define('_FILES_ABS_', $_SERVER['DOCUMENT_ROOT']._FILES_);
define('_MODULES_ABS_', $_SERVER['DOCUMENT_ROOT']._MODULES_);
define('_CACHE_ABS_', $_SERVER['DOCUMENT_ROOT']._CACHE_);
define('_UPLOADS_ABS_', $_SERVER['DOCUMENT_ROOT']._UPLOADS_);
define('_PUBLIC_ABS_', $_SERVER['DOCUMENT_ROOT']._PUBLIC_);
define('_WWW_ABS_', $_SERVER['DOCUMENT_ROOT']._WWW_);
define('_HTML_ABS_', $_SERVER['DOCUMENT_ROOT']._HTML_);

#MODULES
define('_IMG_', _ROOT_.'/img.php');
define('_IMGR_', _ROOT_.'/image.php');
define('_AJAX_FILE_', _WWW_.'/ajax.php');
?>