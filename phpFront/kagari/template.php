<?php

require 'config/config.php';

class Template {
	// 匿名版的某些固定值
	private static $template = Array (
		'nimingbanTitle' => 'kagari匿名版',
		'welcomeInformation' => '<h3>Kagari匿名版欢迎你！</h3>',
		'areaListText' => '版块列表',
		'functionText' => '功能',
		'sendPost' => '发新串',
		'replyPost' => '回复串',
		'adminListPanel' => '管理列表'
	);
	
	// 匿名版的某些计算后的到的值
	private static $calculate = Array (
		'datetime' => 'Y年m月d日 H:i',
		'funcLists' => '权限狗认证处',
		'replyTitle' => Array('发送新串', '回复串'),
		'sendInfo' => Array('回复成功', '没有饼干', '回复失败'),
		'adminLists' => Array('用户管理')
	);
	
	// 匿名版里需要从数据库读取的值
	private static $dbData = Array (
		'cookie' => 'api/getCookie',
		'areaLists' => 'api/getAreaLists',
		'areaPosts' => 'api/getAreaPosts',
		'post' => 'api/getPost',
		'sendPost' => 'api/sendPost',
		'userLists' => 'api/getUserLists'
	);
	
	// 选择模板文件
	public static function index($filename) {
		global $config;
		$fullPath = $config['folder']['templateDir'] . '/' . $filename;
		if (file_exists($fullPath)) {
			return file_get_contents($fullPath);
		} else {
			return 'file:<b>' . $filename . '</b> not exist...';
		}
	}
	
	// 匿名版替换函数$html变量为需要替换的html
	public static function replace($html) {
		require('controller.php');
		// Cookie设置函数
		$html = self::replaceCookies($html);
		// 数据库数据替换
		$html = self::replaceData($html);
		//$html = Controller::dbDataReplace(self::$dbData, $html);
		// 计算后值替换
		$html = self::replaceCalculate($html);
		// 固定参数替换
		$html = self::replaceTemplate($html);
		
		return $html;
	}
	
	// 匿名版cookie替换
	private static function replaceCookies($html) {
		require_once('controller.php');
		// 如果已经设置了cookie名字
		if (isset($_COOKIE['username'])) {
			$cookie = Controller::cookies($_COOKIE['username']);
		} else {
			$cookie = Controller::cookies('');
		}
		
		$html = str_replace('%cookie%', $cookie, $html);
		return $html;
	}
	
	// 匿名版固定参数替换
	private static function replaceTemplate($html) {
		foreach (self::$template as $key => $value) {
			$html = str_replace('%' . $key . '%', $value, $html);
		}
		return $html;
	}
	
	// 匿名版计算后数值替换
	private static function replaceCalculate($html) {
		global $config;
		foreach (self::$calculate as $key => $value) {
			if ($key == 'datetime') {
				$date = date($value);
				$html = str_replace('%' . $key . '%', $date, $html);
			} else if ($key == 'admin') {
				$string = '<div class="button">' . $value . '</div>';
				$html = str_replace('%' . $key . '%', $string, $html);
			} else if ($key == 'adminLists') {
				$string = '<div class="button menu-first"><a href="?admin">' . $value[0] . '</a></div>';
				$html = str_replace('%' . $key . '%', $string, $html);
			}
			// } else if ($key == 'replyTitle') {
				// var_dump($key);
				// if (isset($_GET['q']) && $_GET['q'] == 's-0') {
					// $string = $value[0];
				// } else {
					// $string = $value[1];
				// }
				// $html = str_replace('%' . $key . '%', $string, $html);
			// }
		}
		return $html;
	}
	
	// 匿名版数据库替换函数
	private static function replaceData($html) {
		require_once('controller.php');
		$offset = 0;
		$in = FALSE;
		while ($pos = strpos($html, '%', $offset)) {
			if ($in == FALSE) {
				$startPos = $pos;
				$in = TRUE;
				$offset = $pos + 1;
			} else {
				$endPos = $pos;
				$offset = $pos + 1;
				$in = FALSE;
				$templateString = substr($html, $startPos + 1, $endPos - $startPos - 1);
				//print_r($templateString . ' ');
				// 版块列表
				if ($templateString == 'areaLists') {
					$data = Controller::apis(self::$dbData[$templateString], Array());
					$string = self::areaLists($data);
					$html = str_replace('%' . $templateString . '%', $string, $html);
				// 版块下所有串
				} else if ($templateString == 'areaPosts') {
					// 判断分区值是否为数字，不是给予值0
					$areaId = is_numeric($_GET['a']) ? $_GET['a'] : 0; 
					// 判断页数是否为设置且是数字，不是给予值1
					$areaPage = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
					
					$req = Array(
						'area_id' => $areaId,
						'area_page' => $areaPage
					);
					$data = Controller::apis(self::$dbData[$templateString], $req);
					$string = self::areaPosts($data);
					
					if ($string == '<b>No such area</b>') {
						$data['area_name'] = '未知板块';
					}
					$html = str_replace('%' . $templateString . '%', $string, $html);
					$html = str_replace('%areaId%', $areaId, $html);
					$html = str_replace('%areaName%', $data['area_name'], $html);
					$html = str_replace('%areaPage%', $areaPage, $html);
					$newPost = self::sendPost($areaId);
					$html = str_replace('%newPost%', $newPost, $html);
				// 浏览某个串
				} else if ($templateString == 'post') {
					$postId = is_numeric($_GET['p']) ? $_GET['p'] : 0;
					$postPage = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1; 
					$req = Array(
						'post_id' => $postId,
						'post_page' => $postPage
					);
					
					$data = Controller::apis(self::$dbData[$templateString], $req);
					$string = self::post($data);
					$sendPost = self::sendReply($postId, $data['area_id']);
					
					$html = str_replace('%' . $templateString . '%', $string, $html);
					//$html = str_replace('%')
					$html = str_replace('%function%', $sendPost, $html);
					$html = str_replace('%areaId%', $data['area_id'], $html);
					$html = str_replace('%postId%', $postId, $html);
					$html = str_replace('%areaName%', $data['area_name'], $html);
					$html = str_replace('%postPage%', $postPage, $html);
				// 发送串
				} else if ($templateString == 'sendInfo') {
					// s参数不为数字
					if (!is_numeric($_GET['s'])) {
						die('area id not a num...<a href="javascript:history.go(-1)">click here</a> to return');
					}
					
					//print_r($_POST);
					if (!isset($_POST['content']) || $_POST['content'] == '') {
						die('content should not be empty...<a href="javascript:history.go(-1)">click here</a> to return');
					}

					$areaId = $_GET['s'];
					$cookie = Controller::cookies('');
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

					//print_r($req);
					$data = Controller::apis(self::$dbData['sendPost'], $req);
					//print_r($data);
					// exit();
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
							$replyTitle = '唔唔唔';
						// 为以后预留？
						} else {
							$sendInfo = '未知错误~';
							$replyTitle = '不知道出了什么问题……';
						}
					} else {
						$sendInfo = '发新串成功';
						$replyTitle = self::$calculate['replyTitle'][0];
					}
					$toURI = '?a=' . $areaId;
					$html = str_replace('%sendInfo%', $sendInfo, $html);
					$html = str_replace('%replyTitle%', $replyTitle, $html);
					header("refresh:5;url=$toURI");
				// 回复串
				} else if ($templateString == 'replyInfo') {
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
					
					$cookie = Controller::cookies('');
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
					$data = Controller::apis(self::$dbData['sendPost'], $req);
					//print_r($data);
					// exit();
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
							$replyTitle = '唔唔唔';
						// 为以后预留？
						} else {
							$sendInfo = '未知错误~';
							$replyTitle = '不知道出了什么问题……';
						}
					} else {
						$sendInfo = '回复串成功';
						$replyTitle = self::$calculate['replyTitle'][0];
					}
					
					$toURI = '?p=' . $postId;
					$html = str_replace('%replyInfo%', $sendInfo, $html);
					$html = str_replace('%replyTitle%', $replyTitle, $html);
					header("refresh:5;url=$toURI");
				// 管理员登录
				} else if ($templateString == 'adminLogin') {
				
				// 用户列表
				} else if ($templateString == 'userLists') {
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
		}
		return $html;
	}
	
	// 板块列表处理函数
	private static function areaLists($areaListsArray) {
		if ($areaListsArray == 'no areas') {
			return '<b>No Areas...</b>';
		}
		
		$return = '';
		foreach ($areaListsArray as $value) {
			if ($value['parent_area'] == '') {
				$return .= '<div class="button menu-first"><b>' . $value['area_name'] . '</b></div>';
			} else {
				$return .= '<div class="button menu-second"><a href="?a=' . $value['area_id'] . '">' . $value['area_name'] . '</a></div>';
			}
		}
		return $return;
	}
	
	// 板块串处理函数
	private static function areaPosts($areaArray) {
		global $config;
		// 没有这个板块
		if (isset($areaArray['error']) ) {
			return '<b>No such area</b>';
		}
		// 板块没有串
		if ($areaArray['posts'] == Array()) {
			return '<b>No Posts...</b>';
		}
		$return = '';
		$areaPostsArray = $areaArray['posts'];

		foreach ($areaPostsArray as $areaPost) {
			$titlePart = '<div class="post-title-info"><span class="post-title">' 
			. $areaPost['post_title'] . '</span><span class="author-name">' 
			. $areaPost['author_name'] . '</span><span class="post-id">No.' 
			. $areaPost['post_id'] . '</span><span class="create-time">' . $areaPost['create_time'] .'</span><span class="user-name">ID:' . $areaPost['user_name'] . '</span><input class="reply-button" onclick="location.href=\'?p=' . $areaPost['post_id'] .'\'" type="button" value="回应" /></div>';
			$postImage = $areaPost['post_images'] == '' ? '' : '<span class="post-images"><a target="_blank" href="' . $config['uri']['imgURI'] . $areaPost['post_images'] . '"><img class="thumb" src="?i=' . $areaPost['post_images'] . '"></a></span>';
			$contentPart = '<div class="post-content">' . $postImage . '<span class="post-content">' . $areaPost['post_content'] . '</span></div>';
			$replyPart = '';
			//require_once('../config/config.php');
			if ($areaPost['reply_num'] > $config['display']['lastReplyPosts']) {
				$contentPart .= '<p class="tip">一共有' . $areaPost['reply_num'] . '条回复，当前只显示最新' . $config['display']['lastReplyPosts'] . '条回复，选择“回应”查看所有回复。</p>';
			}
			foreach ($areaPost['reply_recent_post'] as $replyPost) {
				$replyTitlePart = '<div class="post-title-info reply"><span class="post-title">' 
				. $replyPost['post_title'] . '</span><span class="author-name">' 
				. $replyPost['author_name'] . '</span><span class="post-id">No.' 
				. $replyPost['post_id'] . '</span><span class="create-time">' 
				. $replyPost['create_time'] . '</span><span class="user-name">ID:' 
				. $replyPost['user_name'] . '</span></div>';
				$replyPostImage = $replyPost['post_images'] == '' ? '' : '<span class="post-images"><a href="' . $config['folder']['imgURI'] . $replyPost['post_images'] . '"><img class="thumb" src="?i=' . $replyPost['post_images'] . '"></a></span>';
				$replyContentPart = '<div class="post-content reply">' . $replyPostImage . '<span class="post-content">' . $replyPost['post_content'] . '</span></div>';
				$replyPart .= $replyTitlePart . $replyContentPart;
			}
			
			$endPart = '<hr>';
			
			// print_r($areaArray);
			// exit();
			
			
			$return .= $titlePart . $contentPart . $replyPart . $endPart;
		}
		// 页码部分
		// 第一页不显示<
		if ($areaArray['area_page'] == 1) {
			$prev = '<span class="unavailable">&lt;-</span>';
		} else {
			$prevPage = $areaArray['area_page'] - 1;
			$prev = '<span class="available"><a href="?a=' . $areaArray['area_id'] .'&page' . $prevPage . '" title="上一页">&lt;-</a></span>';
		}
		// 最后一页不显示>
		if (floor($areaArray['posts_num'] / $areaArray['posts_per_page']) + 1 == $areaArray['area_page']) {
			$next = '<span class="unavailable">-&gt;</span>';
		} else {
			$nextPage = $areaArray['area_page'] + 1;
			$next = '<span class="available"><a href="?a=' . $areaArray['area_id'] .'&page=' . $nextPage . '" title="下一页">-&gt;</a></span>';
		}
		$current = '<span class="current" title="当前">' . $areaArray['area_page'] . '</span>';
		$pageNumberPart = '<div class="page-number">' . $prev . ' ' . $current . ' ' . $next . '</div>';
		$return .= $pageNumberPart;
		return $return;
	}
	
	// 串处理函数
	private static function post($postArray) {
		$return = '';
		//print_r($postArray);
		global $config;
		
		$titlePart = '<div class="post-title-info"><span class="post-title">' 
		. $postArray['post_title'] . '</span><span class="author-name">' 
		. $postArray['author_name'] . '</span><span class="post-id">No.' 
		. $postArray['post_id'] . '</span><span class="create-time">' . $postArray['create_time'] .'</span><span class="user-name">ID:' . $postArray['user_name'] . '</span></div>';
		$postImage = $postArray['post_images'] == '' ? '' : '<span class="post-images"><a target="_blank" href="' . $config['uri']['imgURI'] . $postArray['post_images'] . '"><img class="thumb" src="' . $config['uri']['imgURI'] . $postArray['post_images'] . '"></a></span>';
		$contentPart = '<div class="post-content">' . $postImage . '<span class="post-content">' . $postArray['post_content'] . '</span></div>';
		$replyPart = '';
		foreach ($postArray['reply_recent_posts'] as $replyPost) {
			$replyTitlePart = '<div class="post-title-info reply"><span class="post-title">' 
			. $replyPost['post_title'] . '</span><span class="author-name">' 
			. $replyPost['author_name'] . '</span><span class="post-id">No.' 
			. $replyPost['post_id'] . '</span><span class="create-time">' 
			. $replyPost['create_time'] . '</span><span class="user-name">ID:' 
			. $replyPost['user_name'] . '</span></div>';
			$replyPostImage = $replyPost['post_images'] == '' ? '' : '<span class="post-images"><a target="_blank" href="'. $config['uri']['imgURI'] . $replyPost['post_images'] . '"><img class="thumb" src="' . $config['uri']['imgURI'] . $replyPost['post_images'] . '"></a></span>';
			$replyContentPart = '<div class="post-content reply">' . $replyPostImage . '<span class="post-content">' . $replyPost['post_content'] . '</span></div>';
			$replyPart .= $replyTitlePart . $replyContentPart;
		}
		$return = $titlePart . $contentPart . $replyPart;
		// 页码部分
		// 第一页不显示<
		if ($postArray['post_page'] == 1) {
			$prev = '<span class="unavailable">&lt;-</span>';
		} else {
			$prevPage = $postArray['post_page'] - 1;
			$prev = '<span class="available"><a href="?p=' . $postArray['post_id'] . '&page=' . $prevPage . '" title="上一页">&lt;-</a></span>';
		}
		// 最后一页不显示>
		if ($postArray['post_page'] == 1) {
			$next = '<span class="unavailable">-&gt;</span>';
		} else {
			$nextPage = $postArray['post_page'] + 1;
			$netx = '<span class="available"><a href="?p=' . $postArray['post_id'] . '&page=' . $nextPage . '" title="下一页">-&gt;</a></span>';
		}
		$current = '<span class="current" title="当前">' . $postArray['post_page'] . '</span>';
		$pageNumberPart = '<div class="page-number">' . $prev . ' ' . $current . ' ' . $next . '</div>';
		$return .= $pageNumberPart;
		return $return;       
	}
	
	// 发新串
	private static function sendPost($areaId) {
		$action = '?s=' . $areaId;
		
		$return = '<form action="' . $action . '" method="post" enctype="multipart/form-data">' .
					'<span>标题</span><input type="text" name="title" placeholder="无标题"/>' .
					'<span>名称</span><input type="text" name="name" placeholder="无名氏"/>'.
					'<span>邮箱</span><input type="text" name="email" placeholder=""/>'.
					'<span>附件</span><input type="file" name="uploadFile"/>' .
					'<span style="float:left">正文</span>' .
					'<textarea name="content" require="require"></textarea>' .
					'<input type="submit" value="发送" />' .
					'</form>';
		return $return;
	}
	
	// 回复串
	private static function sendReply($postId, $areaId) {
		$action = '?r=' . $postId . '&area=' . $areaId;
		$return = '<form action="' . $action . '" method="post" enctype="multipart/form-data">' .
					'<span>回复No.' . $postId . '</span><br />' .
					'<span>标题</span><input type="text" name="title" placeholder="无标题"/>' .
					'<span>名称</span><input type="text" name="name" placeholder="无名氏"/>'.
					'<span>邮箱</span><input type="text" name="email" placeholder=""/>'.
					'<span>附件</span><input type="file" name="uploadFile"/>' .
					'<span style="float:left">正文</span>' .
					'<textarea name="content" require="require"></textarea>' .
					'<input type="submit" value="发送" />' .
					'</form>';
		return $return;
	}
}
?>