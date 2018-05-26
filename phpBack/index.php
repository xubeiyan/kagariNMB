<?php
/**
* kagariNMB入口文件index.php
*/
require 'lib/verify.php';	// 检查类
require 'conf/conf.php'; 	// 引入$conf变量
require 'lib/error.php';	// 错误信息

// 验证UserAgent
if (!Verify::userAgentVerify($conf['customUserAgent'])) {
	$paras = Array($_SERVER['HTTP_USER_AGENT']);
	die(Error::errMsg('notSpecificUserAgent', $paras));
}
// 验证客户端地址
if (!Verify::frontIPVerify($conf['frontIPAddress'])) {
	$paras = Array($_SERVER['REMOTE_ADDR']);
	die(Error::errMsg('notAllowedFrontIP', $paras));
}

// 获取执行文件名
$scriptArray = explode("/", $_SERVER['SCRIPT_FILENAME']);
$scriptFilename = array_pop($scriptArray);


// 使用json还是html作为返回格式
if (!isset($conf['responseType']) || $conf['responseType'] == 'json') {
	header('content-type:application/json;charset=utf-8');
} else if ($conf['responseType'] == 'html') {
	header('content-type:text/html;charset=utf-8');
}

// 请求方法非指定的一律拒绝 默认为GET|POST
$allowedRequest = explode('|', $conf['allowedRequest']);
if (!in_array($_SERVER['REQUEST_METHOD'], $allowedRequest)) {
	$paras = Array($_SERVER['REQUEST_METHOD']);
	die(Error::errMsg('notAllowedRequestMethod', $paras));
}

// 判断是否执行过安装
// if (file_exists($conf['installerPath'])) {
	// $paras = [$conf['installerPath']];
	// die(Error::errMsg('notInstalled', $paras));
// }

require 'lib/database.php';	// 访问数据库

// 检查请求的文件是否是index.php，但是由于rewrite模块的存在这个疑似没啥用
if ($scriptFilename != $conf['scriptFilename']) {
	$paras = Array($conf['scriptFilename'], $scriptFilename);
	die(Error::errMsg('requestInvalidURI', $paras));
}

// 检查提交API是否为空，是则返回欢迎页面
if ($_SERVER['QUERY_STRING'] == '') {
	echo file_get_contents('welcome.html');
	exit();
}
// 检查提交的API是否在指定的API列表内
// print($_SERVER['QUERY_STRING']);
$queryString = explode("=", $_SERVER['QUERY_STRING'])[1];
if (!in_array($queryString, $conf['apiLists'])) {
	$paras = Array($queryString);
	die(Error::errMsg('notAllowedAPI', $paras));
}

// 检查使用时区
if(isset($conf['timeZone'])) {
	date_default_timezone_set($conf['timeZone']);
} else {
	date_default_timezone_set("Asia/Shanghai");
}

// 使用api.php
require 'lib/api.php';
// 获取提交内容
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	//echo 'Request API: ' . $queryString . '<br />';
	header('connection:close'); // close不要keep-alive
	switch ($queryString) {
		case 'api/getAreaLists':
			API::getAreaLists();
			break;
		default:
			die('>w<');
	}
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$inputJSON = file_get_contents("php://input");
	$input = json_decode($inputJSON, true); // true返回array
	if ($input == NULL) {
		die(Error::errMsg('badJSON', []));
	}
	header('connection:close');
	switch ($queryString) {
		case 'api/getCookie':
			if (isset($input['ip'])) {
				API::getCookie($input);
			}
			break;
		case 'api/getAreaPosts':
			if (isset($input['area_id'])) {
				API::getAreaPosts($input);
			}
			break;
		case 'api/getPost':
			if (isset($input['post_id'])) {
				API::getPost($input);
			}
			break;
		case 'api/sendPost':
			if (isset($input['area_id']) && isset($input['user_name']) && isset($input['user_ip']) && isset($input['post_content'])) {
				API::sendPost($input);				
			}
			break;
		case 'api/addArea':
			if (isset($input['area_name']) && isset($input['secret_key'])) {
				API::addArea($input);
			}
			break;
		case 'api/deleteArea':
			if (isset($input['area_id']) && isset($input['secret_key'])) {
				API::deleteArea($input);
			}
			break;
		case 'api/deletePost':
			if (isset($input['post_id']) && isset($input['secret_key'])) {
				API::deletePost($input);
			}
 			break;
		case 'api/getUserLists':
			if (isset($input['user_per_page']) && isset($input['secret_key'])) {
				API::getUserLists($input);
			}
			break;
		case 'api/blockUser':
			if (isset($input['user_name']) && isset($input['block_time']) && isset($input['secret_key'])) {
				API::blockUser($input);
			}
		case 'api/adminLogin':
			if (isset($input['username']) && isset($input['password'])) {
				API::adminLogin($input);
			}
			break;
		default:
			die('>w<');
	}
}
?>