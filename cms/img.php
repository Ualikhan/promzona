<?
/*
Title: Site IMAGE resizer;
Date: 9 Feb 2010;
Author: Michael Derevyanko;
*/
$types = array(
	"auto",  
	"square",
	"max"
);

if(!empty($_REQUEST['url'])){
	$url = $_SERVER['DOCUMENT_ROOT'].$_REQUEST['url'];
	if(@!!$p = getimagesize($url)){
		$type = !empty($_REQUEST['type']) && in_array($_REQUEST['type'], $types) ? $_REQUEST['type'] : 'auto';
		$w = $width = $p[0]; $h = $height = $p[1];
		$x = $y = 0;
		
		switch($type){
			case 'square':
				if(isset($_REQUEST['w']) && is_numeric($w = $_REQUEST['w'])) $h = $w;
				if(isset($_REQUEST['h']) && is_numeric($h = $_REQUEST['h'])) $w = $h;
								
				if($width!=$height){
					if($width>$height){
						$x = round(($width-$height)/2);
					}else{
						$y = 0;//round(($height-$width)/2);
					}
				}
				$width = $height = min($width,$height);
				break;
			case 'max':
				if(isset($_REQUEST['w']) && is_numeric($_REQUEST['w']) && $w>$_REQUEST['w']){
					$w = $_REQUEST['w'];
					$h /= $width/$w;
				}
				if(isset($_REQUEST['h']) && is_numeric($_REQUEST['h']) && $h>$_REQUEST['h']){
					$h = $_REQUEST['h'];
					$w = $width;
					$w /= $height/$h;
				}
				break;
			default:
				if(isset($_REQUEST['w']) && is_numeric($w = $_REQUEST['w'])) $h /= $width/$w;
				if(isset($_REQUEST['h']) && is_numeric($h = $_REQUEST['h'])) $w /= $height/$h;
			break;
		}
		
		header("Content-type: ".$p['mime']);
		$thumb = imagecreatetruecolor($w, $h);
		//imagecopyresized
		switch($p['mime']){
			case "image/gif":
				$source = imagecreatefromgif($url);
				imagecopyresampled($thumb, $source, 0, 0, $x, $y, $w, $h, $width, $height);
				imagegif($thumb);
				break;
			case "image/png":
				$source = imagecreatefrompng($url);
				imagealphablending($thumb, false);
				imagesavealpha($thumb,true);
				$transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
				imagefilledrectangle($thumb, 0, 0, $w, $h, $transparent);
				imagecopyresampled($thumb, $source, 0, 0, $x, $y, $w, $h, $width, $height);
				imagepng($thumb);
				break;
			default:
				$source = imagecreatefromjpeg($url);
				imagecopyresampled($thumb, $source, 0, 0, $x, $y, $w, $h, $width, $height );
				imagejpeg($thumb, NULL, 80);
		}
		imagedestroy($thumb);
	}
}
?>