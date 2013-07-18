<?
include_once('tools.php');
class form
{
	
	public  $ansTable;
	public  $api;
	public  $formId;
	public  $arForm;
	public  $strFormNote;
	public  $__error_msg;
	
	function __construct($formId)
	{
		global $api;
		global $errors;
		
		$this->api = $api;
		$this->ansTable = 'form_answer';
		$this->formId = $formId;
		$this->arForm = $this->api->objects->getFullObject($this->formId);
		$this->strFormNote = 'test note message';
		$this->__error_msg = (isset($errors)?$errors:'Не подключена валидация формы');
	}
	
	function getArAnswers($FIELD_SID = '')
	{
		if (!empty($FIELD_SID) && is_numeric($FIELD_SID))
		{
			$list = $this->api->db->select($this->ansTable, "WHERE `FIELD_ID` = '".$FIELD_SID."' ORDER BY `C_SORT`");
			return $list;
		}
		else
		return 0;
	}
	
	function getFormId(){
		return $this->formId;
	}
	
	function ShowInput($FIELD_SID, $caption_css_class = '')
	{
		$res = "";
		
		$arAnswers = $this->getArAnswers($FIELD_SID);
		reset($arAnswers);
		$value = getCheckedValue($FIELD_SID, $arAnswers);
		
		$object = $this->api->objects->getFullObject($FIELD_SID);
		$tmp = explode(" ", $object['Тип поля']);
		$type = '';
		for ($i = 0; $i < count($tmp); $i++)
			$type .= $tmp[$i].(($i < (count($tmp) - 1))?'_':'');
			
		if ($object['active']){
			// if ($object['Обязательное']) $this->__error_msg .= 'Не заполнено объязательное поле '.$object['Название'];
			
			switch ($type)
			{
				case 'radio':
					$res .= InputType("radio", "form_radio_".$FIELD_SID, $arAnswers, $value, false, "", '', $caption_css_class);
					break;
				case 'checkbox':
					$res .= InputType("checkbox", "form_checkbox_".$FIELD_SID, $arAnswers, $value, false, "", '', $caption_css_class);
					break;
				case 'dropdown':
					$res .= SelectBoxFromArray("form_dropdown_".$FIELD_SID, $arAnswers, $value);
					break;
				case 'multiselect':
					$res .= SelectBoxFromArray("form_multiselect_".$FIELD_SID, $arAnswers, $value, true, 5);
					break;
				case 'text':
					list($key, $arAnswer) = each($arAnswers);
					$res .= '<input type="text" size="'.$arAnswer['FIELD_WIDTH'].'" '.(!empty($object['Maxlength'])?'MaxLength="'.$object['Maxlength'].'"':'').' name="form_text_'.$object['id'].'" value="'.(isset($_REQUEST['form_'.$type.'_'.$object['id']])?$_REQUEST['form_'.$type.'_'.$object['id']]:htmlspecialchars($object['Описание'])).'" />';
					break;
				case 'only_numeric':
					list($key, $arAnswer) = each($arAnswers);
					$res .= '<input type="text" size="'.$arAnswer['FIELD_WIDTH'].'" '.(!empty($object['Maxlength'])?'MaxLength="'.$object['Maxlength'].'"':'').' name="form_'.$type.'_'.$object['id'].'" value="'.(isset($_REQUEST['form_'.$type.'_'.$object['id']])?$_REQUEST['form_'.$type.'_'.$object['id']]:htmlspecialchars($object['Описание'])).'" />';
					break;
				case 'only_alpha':
					list($key, $arAnswer) = each($arAnswers);
					$res .= '<input type="text" size="'.$arAnswer['FIELD_WIDTH'].'" '.(!empty($object['Maxlength'])?'MaxLength="'.$object['Maxlength'].'"':'').' name="form_'.$type.'_'.$object['id'].'" value="'.(isset($_REQUEST['form_'.$type.'_'.$object['id']])?$_REQUEST['form_'.$type.'_'.$object['id']]:htmlspecialchars($object['Описание'])).'" />';
					break;
				case 'hidden':
					list($key, $arAnswer) = each($arAnswers);
					$res .= '<input type="hidden" size="'.$arAnswer['FIELD_WIDTH'].'" name="form_hidden_'.$object['id'].'" value="'.(isset($_REQUEST['form_'.$type.'_'.$object['id']])?$_REQUEST['form_'.$type.'_'.$object['id']]:htmlspecialchars($object['Описание'])).'" />';
					break;
				case 'password':
					list($key, $arAnswer) = each($arAnswers);
					$res .= '<input type="password" size="'.$arAnswer['FIELD_WIDTH'].'" name="form_password_'.$object['id'].'" value="'.(isset($_REQUEST['form_'.$type.'_'.$object['id']])?$_REQUEST['form_'.$type.'_'.$object['id']]:htmlspecialchars($object['Описание'])).'" />';
					break;
				case 'email':
					list($key, $arAnswer) = each($arAnswers);
					$res .= '<input type="text" size="'.$arAnswer['FIELD_WIDTH'].'" '.(!empty($object['Maxlength'])?'MaxLength="'.$object['Maxlength'].'"':'').' name="form_email_'.$object['id'].'" value="'.(isset($_REQUEST['form_'.$type.'_'.$object['id']])?$_REQUEST['form_'.$type.'_'.$object['id']]:htmlspecialchars($object['Описание'])).'" />';
					break;
				case 'url':
					list($key, $arAnswer) = each($arAnswers);
					$res .= '<input type="text" size="'.$arAnswer['FIELD_WIDTH'].'" '.(!empty($object['Maxlength'])?'MaxLength="'.$object['Maxlength'].'"':'').' name="form_url_'.$object['id'].'" value="'.(isset($_REQUEST['form_'.$type.'_'.$object['id']])?$_REQUEST['form_'.$type.'_'.$object['id']]:htmlspecialchars($object['Описание'])).'" />';
					break;
				case 'textarea':
					list($key, $arAnswer) = each($arAnswers);
					$res .= '<textarea name="form_textarea_'.$object['id'].'" '.(!empty($object['Maxlength'])?'MaxLength="'.$object['Maxlength'].'"':'').' cols="'.$arAnswer['FIELD_WIDTH'].'" rows="'.$arAnswer['FIELD_HEIGHT'].'">'.(isset($_REQUEST['form_'.$type.'_'.$object['id']])?$_REQUEST['form_'.$type.'_'.$object['id']]:htmlspecialchars($object['Описание'])).'</textarea>';
					break;
				case 'date':
					list($key, $arAnswer) = each($arAnswers);
					$res .= '<input type="text" size="'.$arAnswer['FIELD_WIDTH'].'" '.(!empty($object['Maxlength'])?'MaxLength="'.$object['Maxlength'].'"':'').' name="form_date_'.$object['id'].'" class="date-picker" value="'.(isset($_REQUEST['form_'.$type.'_'.$object['id']])?$_REQUEST['form_'.$type.'_'.$object['id']]:htmlspecialchars($object['Описание'])).'" />';
					break;
				case "image":				
					list($key, $arAnswer) = each($arAnswers);
					$res .= '<input type="file" size="'.$arAnswer['FIELD_WIDTH'].'" name="form_image_'.$object['id'].'" value="'.(isset($_REQUEST['form_'.$type.'_'.$object['id']])?$_REQUEST['form_'.$type.'_'.$object['id']]:htmlspecialchars($object['Описание'])).'" />';
					break;
				case "file":				
					list($key, $arAnswer) = each($arAnswers);
					$res .= '<input type="file" size="'.$arAnswer['FIELD_WIDTH'].'" name="form_file_'.$object['id'].'" value="'.(isset($_REQUEST['form_'.$type.'_'.$object['id']])?$_REQUEST['form_'.$type.'_'.$object['id']]:htmlspecialchars($object['Описание'])).'" />';
					break;
			}
		}

		return $res;
	}
	
	function ShowInputCaption($FIELD_SID, $css_style = "")
	{
		$ret = "";
		if (!($object = $this->api->objects->getFullObject($FIELD_SID)) && empty($object['Название'])) $ret = "";
		else
		{
			$ret = "<b>".$object['Название']."</b>".$this->ShowRequired($object["Обязательное"]);
		}
		
		if (strlen($css_style) > 0) $ret = "<span class=\"".$css_style."\">".$ret."</span>";

		// if (is_array($this->__form_validate_errors) && array_key_exists($FIELD_SID, $this->__form_validate_errors))
			// $ret = '<span class="form-error-fld" title="'.htmlspecialchars($this->__form_validate_errors[$FIELD_SID]).'"></span>'."\r\n".$ret;
		
		return $ret;
	}
	
	function ShowRequired($flag)
	{
		if ($flag=="1") return "<font color='red'><span class='form-required'>*</span></font>";
	}
	
	function ShowFormTitle($css_style = "")
	{
		$ret = trim(htmlspecialchars($this->arForm["Название"]));
		
		if (strlen($css_style) > 0) $ret = "<div class=\"".$css_style."\">".$ret."</div>";
		
		return $ret;
	}
	
	function ShowFormDescription($css_style = "")
	{
		$ret = trim($this->arForm["Описание формы"]);
		
		if (strlen($css_style) > 0) $ret = "<div class=\"".$css_style."\">".$ret."</div>";
		
		return $ret;
	}
	
	function ShowFormErrors(){
		ob_start();
		echo $this->__error_msg;
		$ret = ob_get_contents();
		ob_end_clean();
		
		return $ret;
	}
	
	function ShowFormNote($css_style = "")
	{
		$strNote = $this->strFormNote;
		
		if (!isset($strNote) || strlen($strNote) <= 0)
			return;		

		$strNote = str_replace("<br>", "\n", $strNote);
		$strNote = str_replace("<br />", "\n", $strNote);

		$strNote = htmlspecialchars($strNote);

		$strNote = str_replace("\n", "<br />", $strNote);
		$strNote = str_replace("&amp;", "&", $strNote);

		$ret = $strNote;
		$ret = '<p><font class="'.$css_style.'">'.$ret.'</font></p>';
		
		return $ret;
		
	}
	
	function ShowDateFormat($type, $css_style = "")
	{
		$format = $this->api->strings->date(date("Y-m-d"), "sql", $type);

		if (strlen($css_style) > 0) return '<span class="'.$css_style.'">'.$format.'</span>';
		else return $format;
	}
	
	function ShowFormTagBegin($method = 'post', $css_style = "")
	{
		return '<form name="form_'.$this->formId.'" method="'.$method.'" enctype="multipart/form-data" '.(!empty($css_style)?'class="'.$css_style.'"':'').'>';
	}
	
	function ShowFormTagEnd()
	{
		return '</form>';
	}
	
	function ShowSubmitButton($caption = "", $css_style = "")
	{		
		$button_value = strlen(trim($caption)) > 0 ? trim($caption) : (strlen(trim($this->arForm["Текст кнопки отправки"]))<=0 ? '' : $this->arForm["Текст кнопки отправки"]);
	
		return "<input type=\"submit\" name=\"web_form_submit\" value=\"".$button_value."\"".(!empty($css_style) ? " class=\"".$css_style."\"" : "")." />";
	}
	
	function ShowResetButton($caption = "", $css_style = "")
	{
		$button_value = strlen(trim($caption)) > 0 ? trim($caption) : (strlen(trim($this->arForm["Текст кнопки отправки"]))<=0 ? '' : $this->arForm["Текст кнопки отмены"]);
	
		return "<input type=\"reset\" name=\"web_form_reset\" value=\"".$button_value."\"".(!empty($css_style) ? " class=\"".$css_style."\"" : "")." />";	
	}
	
	function isUseCaptcha()
	{
		return $this->arForm["Использовать CAPTCHA"] == 1;
	}
	
	function ShowCaptchaImage()
	{
		
		if ($this->isUseCaptcha())
			return "<img src=\""._FILES_."/appends/kcaptcha/index.php\" class=\"captcha_img\" width=\"120\" height=\"60\" /><br /><a href=\"javascript:void(0)\" id=\"update_captcha\" onclick=\"return $('.captcha_img').attr('src', '"._FILES_."/appends/kcaptcha/index.php?rand=' + ((Math.random()*10)+1|0));\">".trim($this->arForm["Текст ссылки обновить для капчи"])."</a>";
		else return "";
	}
	
	function ShowCaptchaField()
	{
		if ($this->isUseCaptcha())
			return "<input type=\"text\" name=\"captcha_word\" size=\"30\" maxlength=\"6\" value=\"\" class=\"inputtext\" />";
		else return "";
	}
	
	function ShowCaptcha()
	{
		return ($this->ShowCaptchaImage()?$this->ShowCaptchaImage()."<br />":"").$this->ShowCaptchaField();
	}
	
}
?>