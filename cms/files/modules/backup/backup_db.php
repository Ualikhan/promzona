<?
include_once(_FILES_ABS_.'/mysql.php');
include_once(_FILES_ABS_.'/appends.php');
// $comp_method = 2 = bz2
// $comp_method = 1 = gz
# Степень сжатия comp_level
$comp_level = 7;
// Путь и URL к файлам бекапа
define('PATH', '/backup/');
define('URL',  '/backup/');
// Максимальное время выполнения скрипта в секундах
// 0 - без ограничений
define('TIME_LIMIT', 600);
// Ограничение размера данных доставаемых за одно обращения к БД (в мегабайтах)
// Нужно для ограничения количества памяти пожираемой сервером при дампе очень объемных таблиц
define('LIMIT', 1);
// mysql сервер
define('DBHOST', 'localhost:3306');
// Базы данных, если сервер не разрешает просматривать список баз данных,
// и ничего не показывается после авторизации. Перечислите названия через запятую
define('DBNAMES', '');
// Кодировка соединения с MySQL
// auto - автоматический выбор (устанавливается кодировка таблицы), cp1251 - windows-1251, и т.п.
define('CHARSET', 'auto');
// Кодировка соединения с MySQL при восстановлении
// На случай переноса со старых версий MySQL (до 4.1), у которых не указана кодировка таблиц в дампе
// При добавлении 'forced->', к примеру 'forced->cp1251', кодировка таблиц при восстановлении будет принудительно заменена на cp1251
// Можно также указывать сравнение нужное к примеру 'cp1251_ukrainian_ci' или 'forced->cp1251_ukrainian_ci'
define('RESTORE_CHARSET', 'utf8');
// Включить сохранение настроек и последних действий
// Для отключения установить значение 0
define('SC', 1);
// Типы таблиц у которых сохраняется только структура, разделенные запятой
define('ONLY_CREATE', 'MRG_MyISAM,MERGE,HEAP,MEMORY');
// Глобальная статистика
// Для отключения установить значение 0
define('GS', 1);

function fn_open($name, $mode, $comp_method = ''){
	if ($comp_method == 2) {
		$filename = "{$name}.sql.bz2";
		return bzopen($_SERVER['DOCUMENT_ROOT'].PATH . $filename, "{$mode}b{$comp_level}");
	}
	elseif ($comp_method == 1) {
		$filename = "{$name}.sql.gz";
		return gzopen($_SERVER['DOCUMENT_ROOT'].PATH . $filename, "{$mode}b{$comp_level}");
	}
	else{
		$filename = "{$name}.sql";
		return fopen($_SERVER['DOCUMENT_ROOT'].PATH . $filename, "{$mode}b");
	}
}

function fn_write($fp, $str, $comp_method = ''){
	if ($comp_method == 2) {
		bzwrite($fp, $str);
	}
	elseif ($comp_method == 1) {
		gzwrite($fp, $str);
	}
	else{
		fwrite($fp, $str);
	}
}

function fn_close($fp, $comp_method = ''){
	if ($comp_method == 2) {
		bzclose($fp);
	}
	elseif ($comp_method == 1) {
		gzclose($fp);
	}
	else{
		fclose($fp);
	}
	@chmod(PATH . $filename, 0666);
}

$appends = new appends();
$is_safe_mode = ini_get('safe_mode') == '1' ? 1 : 0;
if (!$is_safe_mode && function_exists('set_time_limit')) set_time_limit(600);

if (!file_exists(PATH) && !$is_safe_mode) {
    mkdir(PATH, 0777) || trigger_error("Не удалось создать каталог для бекапа", E_USER_ERROR);
}

$only_create = explode(',', ONLY_CREATE);

// header("Expires: Tue, 1 Jul 2003 05:00:00 GMT");
// header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
// header("Cache-Control: no-store, no-cache, must-revalidate");
// header("Pragma: no-cache");

ob_implicit_flush();
error_reporting(E_ALL);

$dbname = $appends->dbname;
$dbhost = $appends->dbhost;
$dblogin = $appends->dblogin;
$dbpass = $appends->dbpass;

mysql_connect($dbhost, $dblogin, $dbpass);
mysql_select_db($dbname) or trigger_error ("Не удается выбрать базу данных.<BR>" . mysql_error(), E_USER_ERROR);
$tables = array();
$result = mysql_query("SHOW TABLES");
$all = 0;
while($row = mysql_fetch_array($result)) {
	$tables[] = $row[0];
}

$tabs = count($tables);
// Определение размеров таблиц
$result = mysql_query("SHOW TABLE STATUS");
$tabinfo = array();
$tab_charset = array();
$tab_type = array();
$tabinfo[0] = 0;
$size = 0;
$info = '';

// Версия MySQL вида 40101
preg_match("/^(\d+)\.(\d+)\.(\d+)/", mysql_get_server_info(), $m);
$mysql_version = sprintf("%d%02d%02d", $m[1], $m[2], $m[3]);

while($item = mysql_fetch_assoc($result)){
	if(in_array($item['Name'], $tables)) {
		$item['Rows'] = empty($item['Rows']) ? 0 : $item['Rows'];
		$tabinfo[0] += $item['Rows'];
		$tabinfo[$item['Name']] = $item['Rows'];
		$size += $item['Data_length'];
		$tabsize[$item['Name']] = 1 + round(1 * 1048576 / ($item['Avg_row_length'] + 1));
		if($item['Rows']) $info .= "|" . $item['Rows'];
		if (!empty($item['Collation']) && preg_match("/^([a-z0-9]+)_/i", $item['Collation'], $m)) {
			$tab_charset[$item['Name']] = $m[1];
		}
		$tab_type[$item['Name']] = isset($item['Engine']) ? $item['Engine'] : $item['Type'];
	}
}
$show = 10 + $tabinfo[0] / 50;
$info = $tabinfo[0] . $info;
$name = 'db_b_' . $dbname . '_' . date("Y-m-d_H-i-s");
$fp = fn_open($name, "w");
fn_write($fp, "#SKD101|{$dbname}|{$tabs}|" . date("Y.m.d H:i:s") ."|{$info}\n\n");
$t=0;
$result = mysql_query("SET SQL_QUOTE_SHOW_CREATE = 1");
// Кодировка соединения по умолчанию
if ($mysql_version > 40101 && CHARSET != 'auto') {
	mysql_query("SET NAMES '" . CHARSET . "'") or trigger_error ("Неудается изменить кодировку соединения.<BR>" . mysql_error(), E_USER_ERROR);
	$last_charset = CHARSET;
}
else{
	$last_charset = '';
}
foreach ($tables as $table){
	// Выставляем кодировку соединения соответствующую кодировке таблицы
	if ($mysql_version > 40101 && $tab_charset[$table] != $last_charset) {
		if (CHARSET == 'auto') {
			mysql_query("SET NAMES '" . $tab_charset[$table] . "'") or trigger_error ("Неудается изменить кодировку соединения.<BR>" . mysql_error(), E_USER_ERROR);
			$last_charset = $tab_charset[$table];
		}
	}
	// Создание таблицы
	$result = mysql_query("SHOW CREATE TABLE `{$table}`");
	$tab = mysql_fetch_array($result);
	$tab = preg_replace('/(default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP|DEFAULT CHARSET=\w+|COLLATE=\w+|character set \w+|collate \w+)/i', '/*!40101 \\1 */', $tab);
	fn_write($fp, "DROP TABLE IF EXISTS `{$table}`;\n{$tab[1]};\n\n");
	// Проверяем нужно ли дампить данные
	if (in_array($tab_type[$table], $only_create)) {
		continue;
	}
	// Опредеделяем типы столбцов
	$NumericColumn = array();
	$result = mysql_query("SHOW COLUMNS FROM `{$table}`");
	$field = 0;
	while($col = mysql_fetch_row($result)) {
		$NumericColumn[$field++] = preg_match("/^(\w*int|year)/", $col[1]) ? 1 : 0;
	}
	$fields = $field;
	$from = 0;
	$limit = $tabsize[$table];
	$limit2 = round($limit / 3);
	if ($tabinfo[$table] > 0) {
		$i = 0;
		fn_write($fp, "INSERT INTO `{$table}` VALUES");
		while(($result = mysql_query("SELECT * FROM `{$table}` LIMIT {$from}, {$limit}")) && ($total = mysql_num_rows($result))){
				while($row = mysql_fetch_row($result)) {
					$i++;
					$t++;

					for($k = 0; $k < $fields; $k++){
						if ($NumericColumn[$k])
							$row[$k] = isset($row[$k]) ? $row[$k] : "NULL";
						else
							$row[$k] = isset($row[$k]) ? "'" . mysql_escape_string($row[$k]) . "'" : "NULL";
					}

					fn_write($fp, ($i == 1 ? "" : ",") . "\n(" . implode(", ", $row) . ")");
				}
				mysql_free_result($result);
				if ($total < $limit) {
					break;
				}
				$from += $limit;
		}

		fn_write($fp, ";\n\n");
	}
}
fn_close($fp);
mysql_close();
?>