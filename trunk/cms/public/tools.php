<?
function getCheckedValue($FIELD_SID, $arAnswers, $arrVALUES=false)
{
	$value = array();
	while (list($key, $arAnswer) = each($arAnswers))
	{
		if (strpos(strtolower($arAnswer["FIELD_PARAM"]), "selected")!==false || strpos(strtolower($arAnswer["FIELD_PARAM"]), "checked")!==false)
			$value[] = $arAnswer["ID"];
	}
	return $value;
}

function InputType($strType, $strName, $arAnswers, $strCmp, $strPrintValue=false, $strPrint="", $field1="", $caption_css_class="")
{
	$strReturnInput = '';
	foreach ($arAnswers as $arAnswer)
	{
		$ans_id = "form_".$strType."_".$arAnswer['ID'];
		$bCheck = false;
		$bCheck = in_array($arAnswer['ID'], $strCmp);
		$bLabel = false;
		if ($strType == 'radio')
			$bLabel = true;
		$strReturnInput .= ($bLabel? '<label>': '').'<input type="'.$strType.'" '.$field1.' name="'.$strName.(($strType == 'checkbox')?'[]':'').'" id="'.$ans_id.'" value="'.$arAnswer['MESSAGE'].'"'.
			($bCheck? ' checked':'').'>'.($strPrintValue? $arAnswer['MESSAGE']:$strPrint).($bLabel? '</label>': '');
		$strReturnInput .= "<label for=\"".$ans_id."\">";
		$strReturnInput .= "<span class=\"".$caption_css_class."\">&nbsp;".$arAnswer["MESSAGE"]."</span></label>";
	}
	return $strReturnInput;
}

function SelectBoxFromArray(
	$strBoxName, //FIELD_SID
	$arAnswers, //массив ответов
	$strSelectedVal=array(), //заранее выбранные пол€
	$isMultiple=false,
	$size=1,
	$strDetText = "", //первое поле. Ќапример: ¬ыбрать отель
	$field1="class='typeselect'", //“ут пишутса классы, id и т.д.
	$go=false, // перейти сразу после выбора
	$form="form1"
	)
{
	if($go)
	{
		$strReturnBox = "<script type=\"text/javascript\">\n".
			"function ".$strBoxName."LinkUp()\n".
			"{var number = document.".$form.".".$strBoxName.".selectedIndex;\n".
			"if(document.".$form.".".$strBoxName.".options[number].value!=\"0\"){ \n".
			"document.".$form.".".$strBoxName."_SELECTED.value=\"yes\";\n".
			"document.".$form.".submit();\n".
			"}}\n".
			"</script>\n";
		$strReturnBox .= '<input type="hidden" name="'.$strBoxName.'_SELECTED" id="'.$strBoxName.'_SELECTED" value="">';
		$strReturnBox .= '<select '.$field1.' name="'.$strBoxName.'" id="'.$strBoxName.'" size="'.$size.'" '.($isMultiple?'multiple':'').' onchange="'.$strBoxName.'LinkUp()" class="typeselect">';
	}
	else
	{
		$strReturnBox = '<select '.$field1.' name="'.$strBoxName.'" id="'.$strBoxName.'" size="'.$size.'" '.($isMultiple?'multiple':'').'>';
	}

	if($strDetText <> '')
		$strReturnBox .= '<option value="">'.$strDetText.'</option>';

	foreach ($arAnswers as $arAnswer)
	{
		$strReturnBox .= '<option';
		
		if(in_array($arAnswer['ID'], $strSelectedVal))
			$strReturnBox .= ' selected';
		$strReturnBox .= ' value="'.htmlspecialchars($arAnswer['MESSAGE']).'">'.htmlspecialchars($arAnswer['MESSAGE']).'</option>';
	}
	return $strReturnBox.'</select>';
}

