<?
include_once 'cms/public/api.php'; 
$api->header = '/general/header.html';
$api->template = '/page.html';
$api->footer = '/general/footer.html';

# ПОДКЛЮЧАЕМ БОКОВЫЕ БАННЕРЫ
include_once _MODS_ABS_.'/sidebanners_innerpage/sidebanners_innerpage_class.php';
$sidebanners = new Sidebanners_innerpage();


# НАЗВАНИЕ СТРАНИЦЫ
$pagetitle = 'Главная';
$ptitle = ($api->pageid!=='' && !empty($api->pageid)? '<!--object:['.$api->pageid.'][1]-->' : $pagetitle);
$api->header(array('page-title' => $ptitle ));


echo ' <section id="content-left-b">
		<section class="text-page-wrapper">';

# ВЫВОДИМ НАЗВАНИЕ СТРАНИЦЫ
echo '<h1><!--#page-title#--></h1>';

# ВЫВОДИМ СОДЕРЖАНИЕ СТРАНИЦЫ
echo '<!--object:['.$api->pageid.'][2]-->';			
echo '</section></section>';


# КОНТЕНТ СПРАВА
echo '<aside id="content-right-b">';

# ВЫВОДИМ БАНЕРКИ СПРАВА
echo $sidebanners->sidebanners();

// echo '<h2>Пример выноски с заголовком:</h2>
				// <p class="ml-10">Начало координат, следовательно, отражает возрастающий математический анализ, таким образом сбылась мечта идиота — утверждение полностью доказано.</p>';
echo '</aside>';




$api->footer();?>