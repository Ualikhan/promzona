<?php
$errors = '';

function isValidEmail($email)
{
	$email = trim($email);
	return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email);
}

function isValidNumeric($text)
{
	$text = trim($text);
	return preg_match("/^[\Q^+-\E0-9\s]+$/", $text);
}

function isValidAlpha($text)
{
	$text = trim($text);
	return preg_match('/[a-zA-Zа-ячфёйА-ЯФЧЁЙ]+$/', $text);
}

function isValidUrl($text)
{
	$text = trim($text);
    $url_pattern = "~(?:(?:ftp|https?)?://|www\.)(?:[a-z0-9\-]+\.)*[a-z]{2,6}(:?/[a-z0-9\-?\[\]=&;#]+)?~i"; 
    return preg_match($url_pattern, $text);
}

if (isset($_REQUEST['web_form_submit']))
{
	if (isset($_REQUEST['formId'])){
		#AJAX
		include_once 'api.php';
		$formId = $_REQUEST['formId'];
		foreach ($_REQUEST['f'] as $k => $fields){
			$_REQUEST[$fields] = $_REQUEST['v'][$k];
		}
	}
	
	if (($formObject = $api->objects->getFullObject($formId)) && ($list = $api->objects->getFullObjectsListByClass($formId, 21)))
	{		
		foreach ($list as $o)
		{
			$tmp = explode(" ", $o['Тип поля']);
			$type = '';
			for ($i = 0; $i < count($tmp); $i++)
				$type .= $tmp[$i].(($i < (count($tmp) - 1))?'_':'');
			
			if ($o['Обязательное'] && empty($_REQUEST['form_'.$type.'_'.$o['id']]))
			{
				$errors .= 'Вы не указали '.$o['Название'].'<br />';
			}
			else
			{
				switch ($o['Тип поля'])
				{
					case 'email':
						if (!isValidEmail($_REQUEST['form_'.$type.'_'.$o['id']]))
							$errors .= 'Не корректно заполнено поле '.$o['Название'].'<br />';
						break;
					case 'only numeric':
						if (!isValidNumeric($_REQUEST['form_'.$type.'_'.$o['id']]))
							$errors .= 'Не корректно заполнено поле '.$o['Название'].'<br />';
						break;
					case 'only alpha':
						if (!isValidAlpha($_REQUEST['form_'.$type.'_'.$o['id']]))
							$errors .= 'Не корректно заполнено поле '.$o['Название'].'<br />';
						break;
					case 'url':
						if (!isValidUrl($_REQUEST['form_'.$type.'_'.$o['id']]))
							$errors .= 'Не корректно заполнено поле '.$o['Название'].'<br />';
						break;
				}
			}
		}
		
		# CAPTCHA
		if ($formObject['Использовать CAPTCHA'] == 1)
		{
			if (empty($_SESSION['captcha_keystring']) || ($_REQUEST['captcha_word'] != $_SESSION['captcha_keystring']))
				$errors .= 'Неверный код с картинки<br />';
		}
		
		if (isset($_REQUEST['f']) && isset($_REQUEST['v'])) exit(str_replace('<br />', "\n", $errors));
		
	}
}
?>
<script type="text/javascript">
$(function(){
	$('input[name="web_form_submit"]').live('click', function(){
		var fields = new Array();
		var fields_vals = new Array();
		// var errors = '';
		
		var i = 0;
		var j = 0;
		
		$('form[name="form_' + <?=$formId?> + '"]').find('input').each(function(){
			fields[i++] = $(this).attr('name');
			fields_vals[j++] = $(this).val();
		});
		$('form[name="form_' + <?=$formId?> + '"]').find('textarea').each(function(){
			fields[i++] = $(this).attr('name');
			fields_vals[j++] = $(this).val();
		});
		$('form[name="form_' + <?=$formId?> + '"]').find('select').each(function(){
			fields[i++] = $(this).attr('name');
			fields_vals[j++] = $(this).val();
		});
		// alert(fields);
		// return false;
		$.post('/cms/public/form_validator.php', {web_form_submit:'1', formId:'<?=$formId?>', f:fields, v:fields_vals}, function(errors){
			if (errors == '')
				$('form[name="form_' + <?=$formId?> + '"]').submit();
			else
				alert(errors);
		});
		// alert(errors);
		return false;
	});
});
</script>



