<?php
/**
* 设置文件
*/
$config = Array(
	// 通用设置
	'general' => Array (
		'NMBname' 		=> 'Kagari匿名版',
		'cssFile'		=> 'css/main.css', 		// css文件位置
		'menuFile'		=> 'js/menu.js', 		// 菜单js文件位置
		'adminFile'		=> 'js/admin.js',		// 
		'loginFile'		=> 'js/login.js',
		'replyFile'		=> 'js/reply.js',
		'favicon'		=> 'favicon.png',		// favicon
		'rewriteURI'	=> False 				// 重写URI
	),
	// URI地址
	'uri' => Array(
		'backURI' => 'http://localhost/kagariNmb/phpBack/', // 后台地址
		'imgURI' => 'http://localhost/kagariNmb/phpBack/upload/', // 图片地址
	),
	// 各种文件位置
	'folder' => Array(
		'templateDir' 	=> 'html/', 		// 模板文件位置
		'thumbDir' 		=> 'thumbs/', 		// 略缩图位置
	),
	// 表现设置
	'display' => Array(
		'thumbSize' => 250, // 略缩图大小
		'lastReplyPosts' => 8, // 最多显示多少条最新回复	
	),
	// 后端交互设置
	'back' => Array (
		'responseType' => 'json',
		'userAgent' => 'KagariNMBFront', // 客户端指定User-Agent，留空则为不限制
	),
	// 区域化设置
	'locale' => Array (
		'timeZone' => 'Asia/Shanghai' // 时区	
	),
);

?>