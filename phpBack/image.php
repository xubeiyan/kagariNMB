<?php
require('conf/conf.php');
if (!(isset($_GET['img']) && file_exists($conf['uploadPath'] . '//' . $_GET['img']))) {
	$_GET['img'] = 'mea.jpg';
}
$image = $_GET['img'];

if (!file_exists($conf['uploadPath'] . '//' . $image)) {
	echo $conf['uploadPath'] . '//' . $image;
	die('seem not to exist "mea.jpg" in upload folder...');
}
$ext = explode('.', $image)[1];
if ($ext == 'jpg') {
	header('Content-Type:image/jpg');	
} else if ($ext == 'png') {
	header('Content-Type:image/png');
} else if ($ext == 'gif') {
	header('Content-Type:image/gif');
}
echo file_get_contents($conf['uploadPath'] . '//' . $image);
exit();
?>