//СОБЫТИЯ
$(function(){
	// $.datepicker.setDefaults( $.datepicker.regional[ "" ] );
	$.datepicker.setDefaults({
		firstDay: 1,
		dateFormat: 'yy-mm-dd',
		dayNames: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
		dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
		monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
		duration: 'fast',
		changeYear: true,
		yearRange: "1900:2100"
	});

	$(".thumbs a").click(function(){
		var largePath = $(this).attr("href");
		var biglargePath = $(this).attr("orig");

		$("#largeImg").attr({ src: largePath });
		$("a.largeImg").attr({ href: biglargePath });
		return false;
	});

	// Геотаргетинг
	// $.getJSON('http://smart-ip.net/geoip-json/?callback=?', function(a){
		 // /* a.city = 'Astana'; */
		// $('.info_block_left select option').each(function(){
			// var city = a.city.toLowerCase();
			// var city_site = _CITY_.substr(1).toLowerCase();
			// if (city_site == '') city_site = 'almaty';
			// if (($(this).val().toLowerCase() == city) && (city != 'almaty') && (city != city_site))
				// /* // location.href = '/'+city+'/'+_LANG_+'/catalog/'; */
				// location.href = '/'+city+'/'+_LANG_+'/';
		// });
	// });

	// $("input#autocomplete").autocomplete({
		// serviceUrl: '/search.php', // Страница для обработки запросов автозаполнения

		// minChars: 2, // Минимальная длина запроса для срабатывания автозаполнения

		// delimiter: /(,|;)\s*/, // Разделитель для нескольких запросов, символ или регулярное выражение

		// maxHeight: 400, // Максимальная высота списка подсказок, в пикселях

		// width: 300, // Ширина списка

		// zIndex: 9999, // z-index списка

		// deferRequestBy: 0, // Задержка запроса (мсек), на случай, если мы не хотим слать миллион запросов, пока пользователь печатает. Я обычно ставлю 300.

		// params: { city: _CITY_}, // Дополнительные параметры

		// onSelect: function(data, value){ }, // Callback функция, срабатывающая на выбор одного из предложенных вариантов,

		// lookup: ['January', 'February', 'March'] // Список вариантов для локального автозаполнения
	// });

	$('.date-picker').keypress(function(){
		return false;
	}).datepicker(
		//$.datepicker.regional[ _LANG_ ]
	);

	$('a.fancy').fancybox();

	$('a.photo').fancybox({
		"speedIn"			: 600,
		"speedOut"			: 200,
		"overlayShow"		: true,
		"centerOnScroll"	: true,
		"titlePosition"		: "over"
	});

	// $('a.fancy').live('click', function(){
		// $.fancybox({
			// 'autoDimensions'  : false,
			// 'width'           : 'auto',
			// 'height'          : 'auto',
			// 'href'            : $(this).attr('href')
		// });
		// return false;
	// });

	// FEEDBACK
	$("#feedback").fancybox({
		'scrolling'  : 'no',
		'titleShow'  : false
	});

	//SUBSCRIBE
	$('#subscribe-add').click(function(){
		var vars = {
			ru : {
				errors : {
					emptyMail : 'Введите E-mail',
					wrongMail : 'E-mail некорректен'
				},
				loading : 'Загрузка',
				enter : 'Подписаться'
			},
			en : {
				errors : {
					emptyMail : 'Enter E-mail',
					wrongMail : 'E-mail is incorrect'
				},
				loading : 'Loading',
				enter : 'Subscribe'
			},
			kz : {
				errors : {
					emptyMail : 'Дұрыс E-Mail енгiзiңiз',
					wrongMail : 'Дұрыс E-Mail енгiзiңiз'
				},
				loading : 'Loading',
				enter : 'Subscribe'
			}
		};
		var that = this;
		var el = $('#subscribe-mail');
		var val = el.val();
		var msg = [];
		if( val=='' || val==el.attr('title') ) msg.push(vars[_LANG_].errors.emptyMail);
		else if( !val.match(/^[\d\w\.-]+@([\d\w-]+)((\.[\w\d-]+)+)?\.\w{2,6}$/) ) msg.push(vars[_LANG_].errors.wrongMail);

		if(msg.length>0){
			alert(msg.join("\n"));
			return false;
		}
		//$(this).text(vars[_LANG_].loading).attr('disabled', 'disabled');
		$.get(ajaxFile, {go:'subscribe_add', mail:val}, function(html){
			//$(that).text(vars[_LANG_].enter).removeAttr('disabled');
			$('#subscribe-mail').val('Введите E-mail');
			alert(html);
		});
	});

	//AUTH
	$('#auth-button').click(function(){
		var vars = {
			ru : {
				errors : {
					emptyMail : 'Введите e-mail',
					wrongMail : 'E-mail некорректен',
					emptyPass : 'Введите пароль'
				},
				loading : 'загрузка..',
				enter : 'войти'
			},
			en : {
				errors : {
					emptyMail : 'Enter e-mail',
					wrongMail : 'E-mail is incorrect',
					emptyPass : 'Enter password'
				},
				loading : 'loading..',
				enter : 'enter'
			},
			kz : {
				errors : {
					emptyMail : 'Дұрыс E-Mail енгiзiңiз',
					wrongMail : 'Дұрыс E-Mail енгiзiңiз',
					emptyPass : 'Парольді енгiзiңiз'
				},
				loading : 'loading..',
				enter : 'enter'
			}
		};
		var that = this;
		var msg = []
		var mail = $('#input-login').val();

		if(mail=='E-mail' || mail=='') msg.push(vars[_LANG_].errors.emptyMail);
		else if(!mail.match(/^[\d\w\.-]+@([\d\w-]+)((\.[\w\d-]+)+)?\.\w{2,6}$/)) msg.push(vars[_LANG_].errors.wrongMail);

		var p = $('#input-pass').val();
		if(p=='Пароль' || p=='') msg.push(vars[_LANG_].errors.emptyPass);

		if(msg.length>0){
			alert(msg.join("\n"));
			return;
		}
		$(this).val('загрузка..').attr('disabled', 'disabled');
		$.getJSON('/ajax.php', {go:'auth', mail:mail, pass:p}, function(a){
			$('#auth-button').val('Войти').removeAttr('disabled');
			if(a.st!='ok'){
				alert(a.text);
				return false;
			}
			auth(a);
		});
		return false;
	});
	//AUTH
	 $('#user_login_link, #auth_link_page').click(function(){
		var vars = {
			ru : {
				errors : {
					emptyMail : 'Введите правильный E-mail',
					emptyPass : 'Введите пароль'
				},
				server_error : 'Невозможно связаться с сервером!'
			},
			en : {
				errors : {
					emptyMail : 'Enter e-mail',
					emptyPass : 'Enter password'
				},
				server_error : 'Unable to connect to the server!'
			},
			kz : {
				errors : {
					emptyMail : 'Дұрыс E-Mail енгiзiңiз',
					emptyPass : 'Парольді енгiзiңiз'
				},
				server_error : 'Unable to connect to the server!'
			}
		};
	  $("#login_win").dialog({
		modal: true,
		resizable:false,
		zIndex: 7999,
		width:350,
		buttons: {
		 "OK": function()
		 {
		  var err_msg = "";
		  var focused = 0;
		  // alert('das');

		  if (EmailCheck($("#login_email").val()) == false)  { err_msg = err_msg+vars[_LANG_].errors.emptyMail+"<br/>"; $("#login_email").focus(); focused=1; }
		  if ($("#login_pass").val() == "") { err_msg = err_msg+vars[_LANG_].errors.emptyPass; if (focused == 0) { $("#login_pass").focus(); } }

		  if (err_msg == ""){
		   $.ajax({
			url: "/login.php",
			data: "email="+$("#login_email").val()+"&passwd="+$("#login_pass").val()+"&x=secure&lang="+_LANG_,
			type: "POST",
			dataType : "html",
			cache: false,

			beforeSend: function()   { $("#login_msg").html("").hide(); },
			success:  function(data)  {  $("#login_msg").html(data).slideDown(); },
			error: function()    { $("#login_msg").html("<span class=\"error\">"+vars[_LANG_].server_error+"</span>").slideDown(); }
		   });
		  } else {
			$("#login_msg").html("<span class=\"error\">"+err_msg+"</span>").slideDown();
		  }
		 }
		}
	  });
	  return false;
	 });

	 $("form[name='order-form']").submit(function(e){
	     //отменяем стандартное действие при отправке формы
	    e.preventDefault();
	    //берем из формы метод передачи данных
	    var m_method=$(this).attr('method');
	    //Переводим данные формы в строку для отправки
	    var m_data=$(this).serialize();
	    //посылаем ajax запрос
	    $.ajax({
		    type: m_method,
		    url:'/ajax.php',
		    data: 'go=order&'+m_data,
		    success: function(result){
			    alert(result);
		    }
	    });
	    // очищаем формы
	    $('input:text').val('');
	    $('input:password').val('');
	    $('input.email').val('');
	    $('textarea').val('');
	    $('input:checkbox').removeAttr('checked');
	    return false;
	 });
});

// Проверка E-MAIL
function EmailCheck(value)
{
  var re = /^\w+([\.-]?\w+)*@(((([a-z0-9]{2,})|([a-z0-9][-][a-z0-9]+))[\.][a-z0-9])|([a-z0-9]+[-]?))+[a-z0-9]+\.([a-z]{2}|(com|net|org|edu|int|mil|gov|arpa|biz|aero|name|coop|info|pro|museum))$/i;

  if(re.test(value))
  {
    return true;
  } else {
    return false;
  }
}

function nameCheck(val){
 var re = /^[a-zA-Zа-яА-ЯЁё][a-zA-Zа-яА-ЯЁё\s-]*$/; 
 if(re.test(val)) return true; else return false; 
}

$(function(){
	$('.needClear')
		 .focus(function(){
			  if(this.value==this.title) this.value='';
		 })
		 .blur(function(){
			  if(this.value=='') this.value=this.title;
		 });
});

function auth(u){
	var vars = {
		ru : {
			change : 'Изменение анкеты',
			exit : 'Выход'
		},
		en : {
			change : 'Change data',
			exit : 'Exit'
		}
	};

	var auth_html = [
		'<div>Здравствуйте, <strong>'+u['name']+'</strong>!</div><br>',
		'<div><a href="/'+_LANG_+'/cab/">'+vars[_LANG_].change+'</a></div>',
		'<div><a href="#выход" onclick="return exit()">'+vars[_LANG_].exit+'</a></div>'
	];
	var html = auth_html.join("\n");

	$('#auth-block').html(html).slideDown('fast');
	$('#auth-form').slideUp('fast');
	location.reload();
}

function exit(){
	$.get('/ajax.php', {go:'exit'}, function(a){
		$('#auth-block').slideUp('fast');
		$('#auth-form').slideDown('fast');
		location.reload();
	});
	return false;
}

function checkSearchForm(f){
	var msg = [];
		if(f['what'].value==_SEARCHWORD_ || f['what'].value=='') msg.push(_NOWORD_);
		else if(f['what'].value.length<3) msg.push(_TOOLONG_);

	if(msg.length>0){
		alert(msg.join("\n"));
		return false;
	}
	return true;
}

function voteIt(btn, voting_id, answer_id){
	var btn = $(btn).attr({disabled:'disabled'});
	$.get(ajaxFile, {vote:voting_id, a_id:answer_id}, function(html){
		$('#voting-backup').html( $('#voting-screen').html() );
		$('#voting-screen').html( html );
		btn.removeAttr('disabled');
		if(answer_id==false) $('#back-to-vote').show();
	});
	return false;
}

function showVoting(){
	$('#voting-screen').html( $('#voting-backup').html() );
	$('#back-to-vote').hide();
	return false;
}

function buy(id, title){
	var vars = {
		ru : {
			goods : 'Товаров',
			add : 'Добавление в корзину',
			count : 'Количество',
			summ : 'На сумму',
			tg: ' тг.'
		},
		en : {
			goods : 'Goods',
			add : 'Add to cart',
			count : 'Count',
			summ : 'Summ',
			tg: ' tg.'
		},
		kz : {
			goods : 'Тауарлар',
			add : 'Қоржынға салу',
			count : 'Саны',
			summ : 'Бағасы',
			tg: ' тг.'
		}
	};
	$("<div>").attr({
		title:vars[_LANG_]['add']
	})
	.html('<br><h2>'+title+'</h2><br><div>'+vars[_LANG_]['count']+': <input class="digits-only" type="text" value="1" style="width:40px;"></div><br>')
	.dialog({
		modal: true,
        resizable:false,
        zIndex: 7999,
		width:300,
		buttons : {
			'OK' : function(){
				$('.ui-dialog-buttonpane').remove();
				var count = $(this).find('input').val();
				var div = $(this).html('<br><div align="center">Loading...</div><br>');
				$.get(ajaxFile, {go:'buy', id:id, count:count, lang:_LANG_}, function(text){
					// $('.end_buy').removeClass('disabled');
					// window.location.reload();
					 div.html('<br><div align="center">'+text+'</div><br>');
					// $.getJSON(ajaxFile, {go:'getCount'}, function(text){
						// $('.count_p').html(vars[_LANG_]['goods'] + ': ' + text);
						// $('.title_car').html('<br />');
					// });
					// $.getJSON(ajaxFile, {go:'getSumm'}, function(text){
						// var a=' '+text.toString();
						// var b='';
						// while(a.length) {
							// if(b) b=' '+b;
							// b=a.substr(a.length-3)+b
							// a=a.substr(0,a.length-3);
						// }
						// $('.summ_p').html(vars[_LANG_]['summ'] + ': ' + b + vars[_LANG_]['tg']);
					// });
				});
			}
		}
	});
	$('input.digits-only').keypress(function(e){
		$(this).removeClass('error');
		if(e.which!=8 && e.which!=0 && !String.fromCharCode(e.which).match(/^[0-9]$/)){
			$(this).addClass('error');
			return false;
		}
	});
	return false;
}

