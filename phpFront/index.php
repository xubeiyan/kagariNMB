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
	if (count(explode('/', $_GET['q'])) < 2) {
		$html = 'lack of parameters...';
		echo $html;
		exit();
	}
	// 区访问，截取前两个字符
	if (substr($_GET['q'], 0, 2) == 'a/') {
		$html = file_get_contents('html/area.html');
		exit();
	// 串访问
	} else if (substr($_GET['q'], 0, 2) == 'p/') {
		echo 'post';
		exit();
	}
}
?>