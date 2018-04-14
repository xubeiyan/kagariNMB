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

// admin
if (isset($_GET['admin'])) {
	// require('kagari/admin.php');
	$html = Template::index('admin.html');
	$html = Template::replace($html);
	echo $html;
// 区访问，截取前两个字符
} else if (isset($_GET['a'])) {
	$area = is_numeric($_GET['a']) ? $_GET['a'] : 0;
	$param = Array (
		'area' => $area,
	);
	$html = Template::index('area_page.html', $param);
	$html = Template::replace($html);
	echo $html;
// 串访问
} else if (isset($_GET['p'])) {
	$post = is_numeric($_GET['p']) ? $_GET['p'] : 0;
	$param = Array (
		'post' => $post,
	);
	$html = Template::index('post_page.html');
	$html = Template::replace($html);
	echo $html;
// 图片访问
} else if (isset($_GET['i'])) {
	$image = is_string($_GET['i']) ? $_GET['i'] : 'r18';
	require('kagari/imageThumb.php');
	ImageThumb::request($image);
// 发送串
} else if (isset($_GET['s'])) {
	$html = Template::index('send_page.html');
	$html = Template::replace($html);
	//print_r($_POST);
	echo $html;
// 回复串
} else if (isset($_GET['r'])) {
	$html = Template::index('reply_page.html');
	$html = Template::replace($html);
	//print_r($_POST);
	echo $html;

// 无法处理的请求
} else {
	$get_str = '';
	foreach ($_GET as $key => $value) {
		$get_str .= $key . '=>' . $value . ' ';
	}
	$html = Template::index('error.html');
	$html = Template::replace($html);
	echo $html;
	exit();
}
?>