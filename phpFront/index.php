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
	$html = Template::index('index.html');
	$html = Template::replace($html);
	echo $html;
	exit();
}

if (isset($_GET)) {
	if (count(explode('-', $_GET['q'])) < 2) {
		$html = 'lack of parameters...';
		echo $html;
		exit();
	}
	// 区访问，截取前两个字符
	if (substr($_GET['q'], 0, 2) == 'a-') {
		$html = Template::index('area.html');
		$html = Template::replace($html);
		echo $html;
		exit();
	// 串访问
	} else if (substr($_GET['q'], 0, 2) == 'p-') {
		echo 'post';
		exit();
	// 图片访问
	} else if (substr($_GET['q'], 0, 2) == 'i-') {
		
	}
}
?>