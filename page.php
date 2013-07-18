<?
include('cms/public/api.php');
if(!isset($_REQUEST['id']) || ($id=$_REQUEST['id']) && (!($obj = $api->objects->getFullObject($id)) || (($obj['class_id'] != 1) && ($obj['class_id'] != 3)))){
	// exit( header('location: /404.php') );
}

$vars = array(
	"ru"=>array(
		"attachedPages"=>'Прикреплённые страницы',
		"attachedFiles"=>'Прикреплённые файлы',
		"attachedPhotos"=>'Прикреплённые фото'
	),
	"en"=>array(
		"attachedPages"=>'Attached pages',
		"attachedFiles"=>'Attached files',
		"attachedPhotos"=>'Attached pictures'
	),
	"kz"=>array()
);

$api->header(array('page-title'=>(strip_tags(html_entity_decode($obj['Название'])))));

# ВЛОЖЕННОСТЬ
$mothers = array();
function getMothers($id)
{
	global $api;
	global $mothers;
	
	if (is_numeric($id) && ($id != 0) && ($o = $api->objects->getObject($id, false)) && (($o['class_id'] == 1) || ($o['class_id'] == 3)))
	{
		$mothers[] = $o['id'];
		getMothers($o['head']);
	}
}
getMothers($obj['head']);
$mothers = array_reverse($mothers);

# ХЛЕБНЫЕ КРОШКИ
if (sizeof($mothers) > 0)
{
	$out = array();
	foreach($mothers as $obj_id)
	{
		if (is_numeric($obj_id) && ($path_obj = $api->objects->getFullObject($obj_id, false))) $out[] = '<a href="/'.$api->lang.'/pages/'.$path_obj['id'].'.html">'.$path_obj['Название'].'</a>';	
	}	
	//echo '<div style="margin:10px 0; font-weight:bold;">'.join(' / ', $out).'</div>';
}

echo '<div id="page-text">'.$obj['Текст'].'</div>';
?>
<!--smart:{
	id:<?=$obj['id']?>,
	title:'&laquo;<?=$obj['Название']?>&raquo;',
	actions:['edit', 'add'],
	p:{
		add:[3,4,5],
		edit:{
			fields:{
				<?=(@$obj['class_id']==1?1:6)?>:'#page-title',
				<?=(@$obj['class_id']==1?2:7)?>:'#page-text'
			}
		}
	},
	info : {
		add : 'прикрепить&nbsp;данные'
	}
}-->
<style type="text/css">
/*-------File-icons-------*/
.file_load {
    display: -moz-inline-box; display: inline-block; *zoom: 1; *display: inline;
	word-spacing: normal;
	vertical-align: top;
	width:190px;
	margin:0 30px 10px 0;
}
.file_load img{
	vertical-align:middle;
	margin-right:5px;
	border:0px;
}
.line_separate{
	border-top:1px solid #cccccc;
	height:1px;
	clear:both;
	padding:0 0 15px;
	width:100%;
}
</style>
<?

echo '<div>';

# ВЛОЖЕННЫЕ СТРАНИЦЫ
if($api->auth())
{
	if($pages = $api->objects->getFullObjectsListByClass($obj['id'], 3))
	{
		//echo '<br><h2>'.$vars[$api->lang]['attachedPages'].'</h2>';
		$out = array();
		foreach($pages as $page)
		{
			$out[] = '
			<li id="li-'.$page['id'].'">
				<a '.$api->getLink($page['id']).' target="_blank">'.$page['Название'].'</a>
				<div>
				<!--smart:{
					id:'.$page['id'].',
					actions:["edit", "remove"],
					p:{
						remove : "#li-'.$page['id'].'"
					}
				}-->
				</div>			
			</li>';
		}
		echo '<ul style="list-style-type:decimal;">'.join("\n", $out).'</ul>';
	}
}

# ВЛОЖЕННЫЕ ФАЙЛЫ
if($files = $api->objects->getFullObjectsListByClass($obj['id'], 5))
{
	//echo '<br><h2>'.$vars[$lang]['attachedFiles'].'</h2>';
	$out = array('<div class="line_separate"></div>');
	foreach($files as $k=>$file)
	{
		$ico = _WWW_.'/ext/none.png';
		$ext = $api->lower($api->getFileExtension($file['Ссылка']));
		$dp = $ext;
		if ($ext == 'docx') $ext = 'doc';
		if ($ext == 'xlsx') $ext = 'xls';
		if (file_exists(_WWW_ABS_.'/ext/'.$ext.'.png')) $ico = _WWW_.'/ext/'.$ext.'.png';
		$out[] = '
		<div class="file_load" id="file-'.$file['id'].'">
			<table><tr><td valign="top" align="left" style="padding-right: 5px; padding-top: 7px;">'.($k + 1).'.</td><td valign="top" align="left">
			<a href="'._UPLOADS_.'/'.$file['Ссылка'].'" target="_blank"><img src="'.$ico.'" border="0" align="left" /></a>
			</td><td valign="middle" align="left"><a href="'._UPLOADS_.'/'.$file['Ссылка'].'" target="_blank">'.$file['Название'].'.'.$dp.'</a><!--smart:{
				id:'.$file['id'].',
				actions:["edit", "remove"],
				p:{
					remove : "#file-'.$file['id'].'"
				},
				css : {marginLeft:32}
			}--></td></tr></table>
		</div>';
	}
	$out[]='';
	echo join("\n", $out);
}

$lang = $api->lang;
$api->lang='ru';
# ФОТОГАЛЛЕРЕЯ
$onepage = 9;
$pages = $api->pages($api->objects->getObjectsCount($obj['id'], 4), $onepage, 5, array(), "/".$api->lang."/".$api->t($obj['id'])."/pg/#pg#.html#photos-list", $api->lang);
if($photos = $api->objects->getFullObjectsListByClass($obj['id'], 4, "AND o.active='1' ORDER BY o.sort LIMIT ".$pages['start'].", $onepage"))
{
	//echo '<br><h2>'.$vars[$lang]['attachedPhotos'].'</h2>';
	$n=0;
	$out = array();
	foreach($photos as $photo){
		$n++;
		if ($n == 1) { $out[] = '<tr valign="top">'; }
		$out[] = '
		<td id="photo-'.$photo['id'].'" align="center">
			<a class="photo" href="'._UPLOADS_.'/'.$photo['Ссылка'].'" rel="photo_group_'.$obj['id'].'" title="'.$photo['Название'].'"><img style="padding:3px;border:1px solid #e7e7e7; background-color:#fff;" src="'._IMGR_.'?w=136&h=136&image='._UPLOADS_.'/'.$photo['Ссылка'].'"></a>
			<div>
			<!--smart:{
				id:'.$photo['id'].',
				actions:["edit", "remove"],
				p:{
					remove : "#photo-'.$photo['id'].'"
				}
			}-->
			</div>
		</td>';
		if ($n == 3) { $out[] = '</tr>'; $n = 0; }
	}
	if ($out[sizeof($out)-1] != '</tr>') $out[] = '</tr>'; 
	echo '<table id="photos-list" width="100%" cellpadding="7" cellspacing="0">'.join("\n", $out).'</table>';
}

$api->lang = $lang;

echo '
	<div style="margin:10px 0;">'.$pages['html'].'</div>
</div>';

# НАЗАД
if (sizeof($mothers) > 0) echo '<div class="back_link">&larr; <a href="/'.$api->lang.'/'.$api->t($mothers[sizeof($mothers)-1]).'.html">'.$api->v('Вернуться на уровень выше').'</a></div>';

# ВЛОЖЕНОСТЬ
if (sizeof($mothers) > 0) $api->objects->last = $mothers[0];

$api->footer();
?>