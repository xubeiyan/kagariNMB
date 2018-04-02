<?php
/**
* 所有访问的入口
*/

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
	if ($_GET['q'] == 'admin') {
		// require('kagari/admin.php');
		$html = Template::index('admin.html');
		$html = Template::replace($html);
		
		echo $html;
		exit();
	}
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
		$html = Template::index('post.html');
		$html = Template::replace($html);
		echo $html;
		exit();
	// 图片访问
	} else if (substr($_GET['q'], 0, 2) == 'i-') {
		$filename = substr($_GET['q'],2);
		require('kagari/imageThumb.php');
		ImageThumb::request($filename);
	// 发送串
	} else if (substr($_GET['q'], 0, 2) == 's-') {
		$html = Template::index('send.html');
		$html = Template::replace($html);
		//print_r($_POST);
		echo $html;
		exit();
	// 无法处理的请求
	} else {
		$html = 'unknown handler for ' . $_GET['q'] . '...';
		echo $html;
		exit();
	}
}
?>