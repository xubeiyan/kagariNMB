<?php
/**
* 设置
*/
$conf = Array(
	// 数据库部分
	'databaseHost' => 		'localhost',
	'databaseUsername' => 	'root',
	'databasePassword' => 	'',
	'databaseName' =>		'kagari_Nimingban',
	'databasePort' =>		'3306',
	'databaseTableName' => 	Array(
		'user' => 'user',
		'area' => 'area',
		'post' => 'post',
		'admin' => 'admin'
	),
	// API列表
	'apiLists' => Array(
		'api/getCookie',
		'api/getAreaLists',
		'api/getAreaPosts',
		'api/getPost',
		'api/sendPost',
		'api/adminLogin',
		'api/addArea',
		'api/deleteArea',
		'api/delatePost',
		'api/getUserLists',
	),
	// 匿名版设置
	'customUserAgent' => 'KagariNMBFront', 	// 留空则不限制特定的UserAgent
	'frontIPAddress' => Array (				// 留空则不限制特定的IP
		'127.0.0.1', '::1'
	),
	'scriptFilename' => 'index.php',
	'installerPath' => 'install/install.php',
	'allowedRequest' => 'GET|POST',
	'responseType' => 'json',
	'postsPerPage' => 20, // 每页多少串
	'lastReplyPosts' => 8, // 最多显示多少条post的回复
	'default_author_name' => '无名氏',
	'default_post_title' => '无标题',
	'sageString' => 'SAGE', // 不顶起串回复标题填写值
	'uploadPath' => 'upload' // 上传文件地址
);

?>