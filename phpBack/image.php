<?php
/*
* 显示图片
* 
*/
require('conf/conf.php'); // 配置文件
require('lib/error.php'); // 错误输出

$default_image_file = 'mea.jpg';

if (isset($_GET['img']) && $_GET['img'] != '') {
	$image = $_GET['img'];	
} else {
	$image = $default_image_file;
}

$fullPath = $conf['uploadPath'] . '//' . $image;

if (!file_exists($fullPath)) {
	$paras = Array($image);
	die(Err::errMsg('defaultImageNotFound', $paras));
}
$fileType = exif_imagetype($fullPath);
if ($fileType == IMAGETYPE_JPEG) {
	header('Content-Type:image/jpg');	
} else if ($fileType == IMAGETYPE_PNG) {
	header('Content-Type:image/png');
} else if ($fileType == IMAGETYPE_GIF) {
	header('Content-Type:image/gif');
} else {
	$paras = Array();
	die(Err::errMsg('imageTypeNotSupport', $paras));
}
echo file_get_contents($fullPath);
exit();
?>