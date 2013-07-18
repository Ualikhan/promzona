<?
include('cms/public/api.php');
$api->header(array('page-title'=>'Ошибка 404'));
echo '<div class="text_block">';
echo '<p>Запрашиваемая Вами страница не найдена.<br />
Возможно Вы найдете её на нашей карте сайта:</p>';
	include('map_code.php');
echo '</div>';
$api->footer();
?>