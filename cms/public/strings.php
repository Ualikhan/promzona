<?
/*
Title: Класс работы со строками
Author: ShadoW <shadow_root@mail.ru>
Date: 8.06.2010
*/
class Strings 
{
	public $lang;

	function __construct(&$lang){
		$this->lang = &$lang;
	}
	/*
	# Как юзать
	$api->strings->date(дата, из, в)

	Из – [sql, date]
	В [sql] ->
		day – день недели
		date – d.m.Y
		textdate – d месяц Y
		datetime – d.m.Y в h:i
		textdatetime - d месяц Y в h:i
		textdateday – d месяц Y, день недели
	*/
	function date($str, $from='sql', $to='textdate')
	{	
		# Локации
		$lang_mass = Array(
							'ru'=>Array(
										'at'=>'в',
										'mounth'=>Array(
													'01'=>'января',
													'02'=>'февраля',
													'03'=>'марта',
													'04'=>'апреля',
													'05'=>'мая',
													'06'=>'июня',
													'07'=>'июля',
													'08'=>'августа',
													'09'=>'сентября',
													'10'=>'октября',
													'11'=>'ноября',
													'12'=>'декабря'),
										'days'=>Array(
													'воскресенье',
													'понедельник',
													'вторник',
													'среда',
													'четверг',
													'пятница',
													'суббота')			
							),
							'en'=>Array(
										'at'=>'at',
										'mounth'=>Array(
													'01'=>'january',
													'02'=>'february',
													'03'=>'march',
													'04'=>'april',
													'05'=>'may',
													'06'=>'june',
													'07'=>'july',
													'08'=>'agust',
													'09'=>'september',
													'10'=>'october',
													'11'=>'november',
													'12'=>'december'),
										'days'=>Array(
													'sunday',
													'monday',
													'thusday',
													'Wednesday',
													'thursday',
													'friday',
													'saturday')
							),
							
							'kz'=>Array(
										'at'=>'',
										'mounth'=>Array(
													'01'=>'қаңтар',
													'02'=>'ақпан',
													'03'=>'наурыз',
													'04'=>'сәуір',
													'05'=>'мамыр',
													'06'=>'маусым',
													'07'=>'шілде',
													'08'=>'қазан',
													'09'=>'қыркүйек',
													'10'=>'қазан',
													'11'=>'қараша',
													'12'=>'желтоқсан'),
										'days'=>Array(
													'жексенбі',
													'дүйсенбі',
													'сейсенбі',
													'жағдай',
													'бейсенбі',
													'жұма',
													'сенбі')
							)
					);
						
		
		# Если из SQL формата
		if ($from == 'sql')
		{
			$date_time 	= explode(' ', $str);
			$date 		= explode('-', $date_time[0]);
			$time 		= @explode(':', $date_time[1]);
			$stamp		= @mktime(0, 0, 0, $date[1], $date[2], $date[0], 0);
			
			# в день недели
			if ($to == 'day') {
				return $lang_mass[$this->lang]['days'][date('w', $stamp)];
			}
			
			
			# в Обычный тип даты
			if ($to == 'date') {
				return $date[2].'.'.$date[1].'.'.$date[0];
			}
			
			# в Текстовая дата
			if ($to == 'textdate')	{
				if (substr($date[2], 0, 1) == 0) { $date[2] = substr($date[2], 1); }
				return $date[2].' '.$lang_mass[$this->lang]['mounth'][$date[1]].' '.$date[0];
			}
			
			# в Дата и время
			if ($to == 'datetime') {
				return $date[2].'.'.$date[1].'.'.$date[0].' '.$lang_mass[$this->lang]['at'].' '.$time[0].':'.$time[1];
			}
			
			# в Текстовые дата и время
			if ($to == 'textdatetime') {
				if (substr($date[2], 0, 1) == 0) { $date[2] = substr($date[2], 1); } 
				if (substr($time[0], 0, 1) == 0) { $time[0] = substr($time[0], 1); } 
				return $date[2].' '.$lang_mass[$this->lang]['mounth'][$date[1]].' '.$date[0].' '.$lang_mass[$this->lang]['at'].' '.$time[0].':'.$time[1];
			}
			
			# в Текстовые дата и день недели
			if ($to == 'textdateday') {
				if (substr($date[2], 0, 1) == 0) { $date[2] = substr($date[2], 1); } 
				return $date[2].' '.$lang_mass[$this->lang]['mounth'][$date[1]].' '.$date[0].', '.$lang_mass[$this->lang]['days'][date('w', $stamp)];
			}
		}
		
		# --------------------------------------------
		# Из обычного формата
		
		if ($from == 'date')
		{
			$date_time 	= explode('.', $str);

			# в SQL
			if ($type_to == 'sql')
			{
				return $date_time[2].'-'.$date_time[1].'-'.$date_time[0];
			}
		}
		
		return false;
	}

}

class num2tenge {
        public $def = array (
                'form' => array('1' => 0, '2' => 1, '1f' => 0, '2f' => 1, '3' => 1, '4' => 1),
                'rank' => array(
                        0 => array('тенге', 'тенге', 'тенге', 'f' => ''),
                        1 => array('тысяча', 'тысячи', 'тысяч', 'f' => 'f'),
                        2 => array('миллион', 'миллиона', 'миллионов', 'f' => ''),
                        3 => array('миллиард', 'миллиарда', 'миллиардов', 'f' => ''),
                        'k' => array('тиын', 'тиын', 'тиын', 'f' => 'f')
                ),
        
                'words' => array(
                        '0' => array( '', 'десять', '', ''),
                        '1' => array( 'один', 'одиннадцать', '', 'сто'),
                        '2' => array( 'два', 'двенадцать', 'двадцать', 'двести'),
                        '1f' => array( 'одна', '', '', ''),
                        '2f' => array( 'две', '', '', ''),
                        '3' => array( 'три', 'тринадцать', 'тридцать', 'триста'),
                        '4' => array( 'четыре', 'четырнадцать', 'сорок', 'четыреста'),
                        '5' => array( 'пять', 'пятнадцать', 'пятьдесят', 'пятьсот'),
                        '6' => array( 'шесть', 'шестнадцать', 'шестьдесят', 'шестьсот'),
                        '7' => array( 'семь', 'семнадцать', 'семьдесят', 'семьсот'),
                        '8' => array( 'восемь', 'восемнадцать', 'восемьдесят', 'восемьсот'),
                        '9' => array( 'девять', 'девятнадцать', 'девяносто', 'девятьсот')
                )
        );
                
        public static function doit($str) {
                $num2rub = new num2tenge();
                
                $str = number_format($str, 2, '.', ',');
                $rubkop = explode('.', $str);
                $rub = $rubkop[0];
                $kop = (isset($rubkop[1])) ? $rubkop[1] : '00';
                $rub = (strlen($rub) == 1) ? '0' . $rub : $rub;
                $rub = explode(',', $rub);
                $rub = array_reverse($rub);
                
                $word = array();
                $word[] = $num2rub->dvig($kop, 'k', false);
                foreach($rub as $key => $value) {
                        if (intval($value) > 0 || $key == 0) //подсказал skrabus
                                $word[] = $num2rub->dvig($value, $key);
                }
                
                $word = array_reverse($word);
                $word = trim(implode(' ', $word));
                $first = mb_substr($word,0,1,'UTF-8');//первая буква
                $last = mb_substr($word, 1, strlen($word), 'UTF-8');//все кроме первой буквы
                $first = mb_strtoupper($first,'UTF-8');
                $last = mb_strtolower($last,'UTF-8');
                return $first.$last;
                // return ucfirst(trim(implode(' ', $word)));
        }
        
        public function dvig($str, $key, $do_word = true) {
                $def =& $this->def;
                $words = $def['words'];
                $form = $def['form'];

                if (!isset($def['rank'][$key])) return '!razriad';
                $rank = $def['rank'][$key];
                $sotni = '';
                $word = '';
                $num_word = '';
                
                $str = (strlen($str) == 1) ? '0' . $str : $str;
                $dig = str_split($str);
                $dig = array_reverse($dig);
                
                if (1 == $dig[1]) {
                        $num_word = ($do_word) ? $words[$dig[0]][1] : $dig[1] . $dig[0];
                        $word = $rank[2];
                }
                else {
                        //$rank[3] - famale
                        if ($dig[0] != 1 && $dig[0] != 2) $rank['f'] = '';
                        $num_word = ($do_word) 
                                ? $words[$dig[1]][2] . ' ' . $words[$dig[0] . $rank['f']][0]
                                : $dig[1] . $dig[0];
                        $key = (isset($form[$dig[0]])) ? $form[$dig[0]] : false;
                        $word = ($key !== false) ? $rank[$key] : $rank[2];
                }
                
                $sotni = (isset($dig[2])) ? (($do_word) ? $words[$dig[2]][3] : $dig[2]) : '';
                if ($sotni && $do_word) $sotni .= ' ';
                
                return $sotni . $num_word . ' ' . $word;
        } //function dvig
        
} //class num2tenge() # echo num2tenge::doit($number); 

?>