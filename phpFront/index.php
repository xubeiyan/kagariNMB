<?php
/**
* 所有访问的入口
*/
// 设置文件
require('config/config.php');
// 模板
require('kagari/template.php');

// 未提交任何GET参数则认为访问主页

if (!isset($_GET) || empty($_GET)) {
	$html = file_get_contents("html/index.html");
	$html = Template::replace($html);
	echo $html;
	exit();
}

if (isset($_GET)) {
	print_r($_GET);
}
?>