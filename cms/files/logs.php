<?
	# ЭКСОПРТ ЛОГОВ
	# Coded by ShadoW (C) 2011 
	# v.2.0	
	header('Content-Type: text/html; charset=utf-8');
	
	# Подключаем API
	require_once('../public/api.php');
	
	
	# Проверяем статус логирования
	if (!file_exists(_CACHE_ABS_.'/logged')) exit('#-#-# Логирование выключено');

	# Ключ доступа
	if ((!$logs_key = file_get_contents(_CACHE_ABS_.'/logged')) || empty($logs_key)) exit('#-#-# Ключ доступа не установлен');

	# Проверям ключ доступа и действие	
	if (!empty($_GET['key'])  && ($_GET['key'] == $logs_key) && !empty($_GET['act']))
	{
		# Подключаемся к базе
		$api->db->mysql_connect();
		
		switch ($_GET['act'])
		{
			# ПОЛУЧИТЬ ЛОГИ
			case 'get':
			
				$link = mysql_query("
					SELECT 
						`action`, 
						`gmadate_ts`, 
						`object_type`, 
						`url` 
					FROM 
						`clogs` 
					ORDER BY 
						`gmadate_ts` ASC
				");
				
				# ЛОГИ ЕСТЬ
				if (mysql_num_rows($link) > 0)
				{						
					$out = array();
					while($o = mysql_fetch_assoc($link)) $out[] = $o;
					
					echo serialize($out);
					
					# ЧИСТИМ БАЗУ ДАННЫХ
					$api->db->mysql_query("TRUNCATE TABLE `clogs`");
				}
				else{
					$api->db->createLogTable();
					exit('#-#-# Изменений не зафиксированно');
				}
			break;
			
			# ОЧИСТИТЬ БАЗУ ЛОГОВ
			case 'trunc':
				$api->db->mysql_query("TRUNCATE TABLE `clogs`");
			break;
			
			# NO
			default:
				echo '#-#-# Неверное действие';				
			break;
		}
		
	}
	else echo '#-#-# Не верный ключ или действие';
?>
