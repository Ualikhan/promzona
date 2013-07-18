<?class Registration extends api{
	
	function __construct(){
	      parent::__construct();
	}
	
	# DB ЗАПРОСЫ
	function getUsers(){
		
	}
	
	/*
	function showForm($posts){
		$out = '<h1>Регистрация пользователя</h1>';
		$out .= '<form action="" method="post">';
		$out .= 'Email: <input type="text" value="" name="email"><br>';
		$out .= 'Password: <input type="password" value="" name="password"><br>';
		$out .= 'Confirm password: <input type="password" value="" name="password_confirm"><br>';
		$out .= 'Full name: <input type="text" value="" name="fullname"><br>';
		$out .= 'Region: <input type="text" value="" name="region"><br>';
		$out .= 'Telephones: <input type="text" value="" name="telephones"><br>';
		$out .= 'Проверочный код: <input type="text" value="" name="captcha"><br>';
		$out .= '<input type="checkbox" value="1" name="agree">Я согласен и принимаю условия <a '.$this->getLink(205).'>Пользовательского соглашения</a><br>';
		$out .= '<button type="submit">Зарегистрироваться</button><br>';
		$out .= '</form>';
		
		return $out;
	}
	*/
	
	function getData($id){
		$a=$this->objects->getFullObject($id);
		return $a;
	}
	
}?>