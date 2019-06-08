<?php
/**
* Model
*/
require 'config/config.php';

class Model {
	// 匿名版的某些固定值
	private static $constant = Array (
		'areaListText' => '版块列表',
		'functionText' => '功能',
		'sendPost' => '发新串',
		'replyPost' => '回复串',
	);
	
	/**
	* 计算需要替换的数据
	*/
	public static function data($template) {
		$templateArray = Array();
		$offset = 0; // 从头开始
		$in = FALSE; // 最初是不在一个%xxx%之中的
		$templateString = ''; // 最初的templateString为空
		while ($currentPos = strpos($template, '%', $offset)) {
			// 如果此时不在一个%xxx%之中
			if ($in == FALSE) {
				$startPos = $currentPos;
				$in = TRUE;
				$offset = $currentPos + 1;
			} else {
				$endPos = $currentPos;
				$offset = $currentPos + 1;
				$in = FALSE;
				$templateString = substr($template, $startPos + 1, $endPos - $startPos - 1);
				// 如果templateArray之中没有则将其添加进去
				if (!in_array($templateString, $templateArray)) {
					$templateArray[$templateString] = '';
				}
			}
		}
		
		// 处理templateArray
		
		// 替换固定值
		$templateArray = self::replaceConstant($templateArray);
		
		// 替换计算值
		$templateArray = self::replaceCalculate($templateArray);
		
		// 替换数据
		$templateArray = self::replaceData($templateArray);
		
		return $templateArray;
	}
	
	// 选择模板文件内容
	public static function templateContent($filename) {
		global $config;
		$fullPath = $config['folder']['templateDir'] . '/' . $filename;
		if (file_exists($fullPath)) {
			return file_get_contents($fullPath);
		} else {
			return 'not exist';
		}
	}
	
	// 固定值替换
	private static function replaceConstant($templateArray) {
		
		foreach ($templateArray as $key => $value) {

			if (array_key_exists($key, self::$constant)) {
				
				$templateArray[$key] = self::$constant[$key];
			}
		}
		
		return $templateArray;
	}
	
	
	// 匿名版计算后数值替换
	private static function replaceCalculate($templateArray) {
		global $config;
		
		foreach ($templateArray as $key => $value) {
			// 跳过已计算的
			if ($value != '') {
				continue;
			}
			
			if ($key == 'nimingbanTitle') {
				$templateArray[$key] = $config['general']['NMBname'];
			} else if ($key == 'welcomeInformation') {
				$wcInfoFile = $config['folder']['templateDir'] . '/templates/welcome.html'; 
				$templateArray[$key] = file_get_contents($wcInfoFile);
			} else if ($key == 'datetime') {
				date_default_timezone_set($config['locale']['timeZone']);
				$templateArray[$key] = date('Y年m月d日 H:i');
			} else if ($key == 'cookie') {
				$username = isset($_COOKIE['username']) ? $_COOKIE['username'] : '';
				$templateArray[$key] = self::cookies($username);
			} else if ($key == 'cssFile') {
				$templateArray[$key] = $config['folder']['templateDir'] . $config['general']['cssFile'];
			} else if ($key == 'angularjs') {
				$templateArray[$key] = $config['folder']['templateDir'] . $config['general']['jsFile'];
			} else if ($key == 'favicon') {
				$templateArray[$key] = $config['folder']['templateDir'] . $config['general']['favicon'];
			}
		}
		
		return $templateArray;
	}
	
	// 匿名版数据库替换函数
	private static function replaceData($templateArray) {
		foreach ($templateArray as $key => $value) {
			// 跳过已计算的
			if ($value != '') {
				continue;
			}
			// 版块列表
			if ($key == 'areaLists') {
				$data = self::apis('api/getAreaLists', Array());
				$templateArray[$key] = View::areaLists($data);
			// 版块下所有串
			} else if ($key == 'areaPosts') {
				global $config;
				// 判断分区值是否为数字，不是给予值0
				$areaId = is_numeric($_GET['a']) ? $_GET['a'] : 0; 
				// 判断页数是否为设置且是数字，不是给予值1
				$areaPage = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
				
				$req = Array(
					'area_id' => $areaId,
					'area_page' => $areaPage
				);
				$data = self::apis('api/getAreaPosts', $req);
				$string = View::areaPosts($data);
				
				if ($string == 'no such area') {
					$data['area_name'] = '未知板块';
					$string = file_get_contents($config['folder']['templateDir'] . 'templates/no_area.html');
				}
				
				if ($string == 'no posts') {
					$string = file_get_contents($config['folder']['templateDir'] . 'templates/no_posts.html');
				}
				
				$templateArray[$key] = $string;
				$templateArray['areaId'] = $areaId;
				$templateArray['areaName'] = $data['area_name'];
				$templateArray['areaPage'] = $areaPage;
				$templateArray['newPost'] = View::sendPost($areaId);
				
			// 浏览某个串
			} else if ($key == 'post') {
				global $config;
				$postId = is_numeric($_GET['p']) ? $_GET['p'] : 0;
				$postPage = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1; 
				$req = Array(
					'post_id' => $postId,
					'post_page' => $postPage
				);
				
				$data = self::apis('api/getPost', $req);
				$string = View::post($data);
				if ($string == 'no such post') {
					$data['area_id'] = -1;
					$data['area_name'] = '未知板块';
					$string = file_get_contents($config['folder']['templateDir'] . 'templates/no_such_post.html');
				}
				$sendPost = View::sendReply($postId, $data['area_id']);
				
				$templateArray[$key] = $string;
				$templateArray['function'] = $sendPost;
				$templateArray['areaId'] = $data['area_id'];
				$templateArray['postId'] = $postId;
				$templateArray['areaName'] = $data['area_name'];
				$templateArray['postPage'] = $postPage;
				
			// 发送串
			} else if ($key == 'sendInfo') {
				// s参数不为数字
				if (!is_numeric($_GET['s'])) {
					die('area id not a num...<a href="javascript:history.go(-1)">click here</a> to return');
				}
				
				//print_r($_POST);
				if (!isset($_POST['content']) || $_POST['content'] == '') {
					die('content should not be empty...<a href="javascript:history.go(-1)">click here</a> to return');
				}

				$areaId = $_GET['s'];
				
				$cookie = $templateArray['cookie'];
				$req = Array(
					'user_name' => $cookie,
					'area_id' => $areaId,
					'user_ip' => $_SERVER['REMOTE_ADDR'],
					'reply_post_id' => 0,
					'post_content' => $_POST['content']
				);
				if (isset($_POST['title']) && $_POST['title'] != '') {
					$req['post_title'] = $_POST['title'];
				}
				if (isset($_POST['name']) && $_POST['name'] != '') {
					$req['author_name'] = $_POST['name'];
				}
				if (isset($_POST['email']) && $_POST['email'] != '') {
					$req['author_email'] = $_POST['email'];
				}
				//print_r($_FILES);
				if (isset($_FILES['uploadFile']) && $_FILES['uploadFile']['error'] != UPLOAD_ERR_NO_FILE) {
					$uploadDir = '.';
					if ($_FILES['uploadFile']['error'] == UPLOAD_ERR_OK &&
						($_FILES['uploadFile']['type'] == 'image/jpeg' || $_FILES['uploadFile']['type'] == 'image/png' || $_FILES['uploadFile']['type'] == 'image/gif')) {
						if (!move_uploaded_file($_FILES['uploadFile']['tmp_name'], $_FILES['uploadFile']['name'])) {
							die('seem not to move successfully...');
						}
					} else {
						die('error code is ' . $_FILES['uploadFile']['error'] . '. or type is not jpg/png/gif');
					}
					$dataImage = file_get_contents($_FILES['uploadFile']['name']);
					unlink($_FILES['uploadFile']['name']);
					$req['post_image'] = 'data:' . $_FILES['uploadFile']['type'] . ';base64,' . base64_encode($dataImage);
				}

				$data = self::apis('api/sendPost', $req);
				// 根据是否具有error字段判断发串成功与否
				if (isset($data['error'])) {
					if ($data['error'] == 'Last post time interval too short') {
						date_default_timezone_set("Asia/Shanghai");
						$sendInfo = '发串时间间隔过短，上次发帖时间为：' . $data['last_post_time'] . '下次可发串时间为：' . date('Y-m-d H:i:s');
						$replyTitle = '发串过快';
					} else if ($data['error'] == 'This user is forbid forever') {
						$sendInfo = '此用户已被封禁';
						$replyTitle = '很遗憾';
					} else if ($data['error'] == 'This user is blocked') {
						$sendInfo = '此用户在' . date('Y-m-d H:i:s', $data['block_end_time']) . '之前不允许发帖';
						$replyTitle = '反思中';
					// 为以后预留？
					} else {
						$sendInfo = '未知错误~';
						$replyTitle = '不知道出了什么问题……';
					}
				} else {
					$sendInfo = '发新串成功';
					$replyTitle = '恭喜';
				}
				$toURI = '?a=' . $areaId;
				$templateArray['sendInfo'] = $sendInfo;
				$templateArray['replyTitle'] = $replyTitle;
				
				header("refresh:5;url=$toURI");
			// 回复串
			} else if ($key == 'replyInfo') {
				// s参数不为数字
				if (!is_numeric($_GET['r'])) {
					die('reply post id not a num...<a href="javascript:history.go(-1)">click here</a> to return');
				}
				
				//print_r($_POST);
				if (!isset($_POST['content']) || $_POST['content'] == '') {
					die('content should not be empty...<a href="javascript:history.go(-1)">click here</a> to return');
				}
				
				$postId = $_GET['r'];
				$areaId = isset($_GET['area']) && is_numeric($_GET['area']) ? $_GET['area'] : 0;
				
				$cookie = $templateArray['cookie'];
				$req = Array(
					'user_name' => $cookie,
					'area_id' => $areaId,
					'user_ip' => $_SERVER['REMOTE_ADDR'],
					'reply_post_id' => $postId,
					'post_content' => $_POST['content']
				);
				if (isset($_POST['title']) && $_POST['title'] != '') {
					$req['post_title'] = $_POST['title'];
				}
				if (isset($_POST['name']) && $_POST['name'] != '') {
					$req['author_name'] = $_POST['name'];
				}
				if (isset($_POST['email']) && $_POST['email'] != '') {
					$req['author_email'] = $_POST['email'];
				}
				//print_r($_FILES);
				if (isset($_FILES['uploadFile']) && $_FILES['uploadFile']['error'] != UPLOAD_ERR_NO_FILE) {
					$uploadDir = '.';
					if ($_FILES['uploadFile']['error'] == UPLOAD_ERR_OK &&
						($_FILES['uploadFile']['type'] == 'image/jpeg' || $_FILES['uploadFile']['type'] == 'image/png' || $_FILES['uploadFile']['type'] == 'image/gif')) {
						if (!move_uploaded_file($_FILES['uploadFile']['tmp_name'], $_FILES['uploadFile']['name'])) {
							die('seem not to move successfully...');
						}
					} else {
						die('error code is ' . $_FILES['uploadFile']['error'] . '. or type is not jpg/png/gif');
					}
					$dataImage = file_get_contents($_FILES['uploadFile']['name']);
					unlink($_FILES['uploadFile']['name']);
					$req['post_image'] = 'data:' . $_FILES['uploadFile']['type'] . ';base64,' . base64_encode($dataImage);
				}

				//print_r($req);
				$data = self::apis('api/sendPost', $req);
				//print_r($data);
				// exit();
				// 根据是否具有error字段判断发串成功与否
				if (isset($data['error'])) {
					if ($data['error'] == 'Last post time interval too short') {
						date_default_timezone_set("Asia/Shanghai");
						$sendInfo = '发串时间间隔过短，上次发帖时间为：' . $data['last_post_time'] . '下次可发串时间为：' . $data['next_post_time'];
						$replyTitle = '发串过快';
					} else if ($data['error'] == 'This user is forbid forever') {
						$sendInfo = '此用户已被封禁';
						$replyTitle = '很遗憾';
					} else if ($data['error'] == 'This user is blocked') {
						$sendInfo = '此用户在' . date('Y-m-d H:i:s', $data['block_end_time']) . '之前不允许发帖';
						$replyTitle = '反思中';
					// 为以后预留？
					} else {
						$sendInfo = '未知错误~' . $data['error'];
						$replyTitle = '不知道出了什么问题……';
					}
				} else {
					$sendInfo = '回复串成功';
					$replyTitle = '恭喜';
				}
				
				$toURI = '?p=' . $postId;
				$templateArray['replyInfo'] = $sendInfo;
				$templateArray['replyTitle'] = $replyTitle;
				
				header("refresh:5;url=$toURI");
			// 管理员登录
			} else if ($key == 'adminLogin') {
				$adminLogin = View::adminLogin();
				$templateArray['adminLogin'] = $adminLogin;
			// 用户列表
			} else if ($key == 'userLists') {
				$req = Array();
				$req['user_per_page'] = 50;
				$data = Controller::apis(self::$dbData['userLists'], $req);
				print_r($data);
				exit();
				$userInfo = '';
				foreach ($data['users'] as $user) {
					$userInfo .= 'id: ' . $user['user_id'] .' IP地址: ' . $user['ip_address'] . ' 用户名: ' . $user['user_name'];
				}
				$html = str_replace('%userLists%', $userInfo, $html);
			// 
		} 
		}
		return $templateArray;
	}
	
	/**
	* 是否需要重写URI
	*/
	private static function uri() {
		global $config;
		if ($config['general']['rewriteURI']) {
			$uri = $config['uri']['backURI'];
		} else {
			$uri = $config['uri']['backURI'] . 'index.php?q=';
		}
		return $uri;
	}
	/**
	* cookie读取
	* 参数：用户cookie中的username
	* 返回：新的cookie中的username
	* 		或者之前cookie的username
	*		或者dismatch（现有cookie和数据库中不匹配）
	*/
	public static function cookies($username) {
		global $config;
		$userAgent = $config['back']['userAgent'] == '' ? '' : $config['back']['userAgent'];
		
		$data = Array(
			'ip' => $_SERVER['REMOTE_ADDR']
		);
		$opts = Array(
			'http' => Array(
				'method' => 'POST',
				'header' => "User-Agent: " . $userAgent . "\r\nContent-type: application/json\r\n",
				'content' => json_encode($data, JSON_UNESCAPED_UNICODE)
			)
		);
		
		$context = stream_context_create($opts);
		$json = file_get_contents(self::uri() . 'api/getCookie', false, $context);
		$string = json_decode($json, TRUE);
		
		if ($username == '') {
			//print_r($json);
			return $string['response']['username'];
		} else if ($username == $string['response']['username']){
			return $username;
		} else {
			return 'dismatch';
		}
	}
	
	/**
	* 数据库读取
	* 参数：调用API名称$api
	*		调用API的请求本体$req
	* 返回值：
	*/
	public static function apis($api, $req) {
		global $config;
		$userAgent = $config['back']['userAgent'] == '' ? '' : $config['back']['userAgent'];
		
		// 板块列表
		if ($api == 'api/getAreaLists') {
			$opts = Array(
				'http' => Array(
					'method' => 'GET',
					'header' => 'User-Agent: ' . $userAgent,
				)
			);
			$context = stream_context_create($opts);
			$jsonResponse = file_get_contents(self::uri() . $api, false, $context);
			$arrayResponse = json_decode($jsonResponse, TRUE);
			
			return $arrayResponse['response'];
		// 板块下串列表	| 串 | 新串 | 管理员登录 | 
		// 	获取用户列表
		} else if ($api == 'api/getAreaPosts' || $api == 'api/getPost' || $api == 'api/sendPost' || $api == 'api/adminLogin' ||
			$api == 'api/getUserLists') {
			$opts = Array(
				'http' => Array(
					'method' => 'POST',
					'header' => "User-Agent: " . $userAgent . "\r\nContent-type: application/json\r\n",
					'content' => json_encode($req, JSON_UNESCAPED_UNICODE)
				)
			);
			$context = stream_context_create($opts);
			$jsonResponse = file_get_contents(self::uri() . $api, false, $context);
			$arrayResponse = json_decode($jsonResponse, TRUE);
			// print_r($jsonResponse);
			return $arrayResponse['response'];
		} 
	}
}
?>