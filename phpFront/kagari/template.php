<?php
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
		'date' => 'Y年m月d日',
		'time' => 'H:i',
		'admin' => '权限狗认证处',
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
		if (file_exists('html/' . $filename)) {
			return file_get_contents('html/' . $filename);
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
		foreach (self::$calculate as $key => $value) {
			if ($key == 'date') {
				$date = date($value);
				$html = str_replace('%' . $key . '%', $date, $html);
			} else if ($key == 'time') {
				$time = date($value);
				$html = str_replace('%' . $key . '%', $time, $html);
			} else if ($key == 'admin') {
				$string = '<div class="button">' . $value . '</div>';
				$html = str_replace('%' . $key . '%', $string, $html);
			} else if ($key == 'adminLists') {
				$string = '<div class="button menu-first">' . $value[0] . '</div>';
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
					$queryArray = explode('-', $_GET['q']);
					$areaId = $queryArray[1];
					if (count($queryArray) == 4 && is_numeric($queryArray[3])) {
						$areaPage = $queryArray[3];
					} else {
						$areaPage = 1;
					}
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
					$newPost = self::sendPost(0, $areaId);
					$html = str_replace('%newPost%', $newPost, $html);
				// 某个串
				} else if ($templateString == 'post') {
					$queryArray = explode('-', $_GET['q']);
					$postId = $queryArray[1];
					if (count($queryArray) == 4) {
						$postPage = $queryArray[3];
					} else {
						$postPage = 1;
					}
					$req = Array(
						'post_id' => $postId,
						'post_page' => $postPage
					);
					
					$data = Controller::apis(self::$dbData[$templateString], $req);
					$string = self::post($data);
					$sendPost = self::sendPost($postId, $data['area_id']);
					
					$html = str_replace('%' . $templateString . '%', $string, $html);
					//$html = str_replace('%')
					$html = str_replace('%function%', $sendPost, $html);
					$html = str_replace('%areaId%', $data['area_id'], $html);
					$html = str_replace('%postId%', $postId, $html);
					$html = str_replace('%areaName%', $data['area_name'], $html);
					$html = str_replace('%postPage%', $postPage, $html);
				// 发送串
				} else if ($templateString == 'sendInfo') {
					// 无论是回复串还是新串$queryArray长度都是4
					$queryArray = explode('-', $_GET['q']);
					
					if (count($queryArray) != 4) {
						die('the length of queryArray is not 4...');
					}
					//print_r($_POST);
					if (!isset($_POST['content']) || $_POST['content'] == '') {
						die('content should not be empty');
					}
					$id = $queryArray[1];
					$areaId = $queryArray[3];
					$cookie = Controller::cookies('');
					$req = Array(
						'user_name' => $cookie,
						'area_id' => $areaId,
						'user_ip' => $_SERVER['REMOTE_ADDR'],
						'reply_post_id' => $id,
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
						// 为以后预留？
						} else {
							$sendInfo = '未知错误~';
							$replyTitle = '不知道出了什么问题……';
						}
					} else {
						if (substr($_GET['q'], 0, 3) == 's-0') {
							$sendInfo = '发新串成功';
							$replyTitle = self::$calculate['replyTitle'][0];
							$toURI = 'a-' . $areaId;
						} else {
							$sendInfo = '回复串成功';
							$replyTitle = self::$calculate['replyTitle'][1];
							$toURI = 'p-' . $id;
						}					
					}
					$html = str_replace('%sendInfo%', $sendInfo, $html);
					$html = str_replace('%replyTitle%', $replyTitle, $html);
					header("refresh:5;url=$toURI");
				} else if ($templateString == 'userLists') {
					$req = Array();
					$req['user_per_page'] = 50;
					$data = Controller::apis(self::$dbData['userLists'], $req);
					$userInfo = '';
					foreach ($data['users'] as $user) {
						$userInfo .= 'id: ' . $user['user_id'] .' IP地址: ' . $user['ip_address'] . ' 用户名: ' . $user['user_name'];
					}
					$html = str_replace('%userLists%', $userInfo, $html);
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
				$return .= '<div class="button menu-second"><a href="a-' . $value['area_id'] . '">' . $value['area_name'] . '</a></div>';
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
			. $areaPost['post_id'] . '</span><span class="create-time">' . $areaPost['create_time'] .'</span><span class="user-name">ID:' . $areaPost['user_name'] . '</span><input class="replay-button" onclick="location.href=\'p-' . $areaPost['post_id'] .'\'" type="button" value="回应" /></div>';
			$postImage = $areaPost['post_images'] == '' ? '' : '<span class="post-images"><a href="' . $config['imgURI'] . $areaPost['post_images'] . '"><img class="thumb" src="i-' . $areaPost['post_images'] . '"></a></span>';
			$contentPart = '<div class="post-content">' . $postImage . '<span class="post-content">' . $areaPost['post_content'] . '</span></div>';
			$replyPart = '';
			//require_once('../config/config.php');
			if ($areaPost['reply_num'] > $config['lastReplyPosts']) {
				$contentPart .= '<p class="tip">一共有' . $areaPost['reply_num'] . '条回复，当前只显示最新' . $config['lastReplyPosts'] . '条回复，选择“回应”查看所有回复。</p>';
			}
			foreach ($areaPost['reply_recent_post'] as $replyPost) {
				$replyTitlePart = '<div class="reply post-title-info"><span class="post-title">' 
				. $replyPost['post_title'] . '</span><span class="author-name">' 
				. $replyPost['author_name'] . '</span><span class="post_id">No.' 
				. $replyPost['post_id'] . '</span><span class="create-time">' 
				. $replyPost['create_time'] . '</span><span class="user-name">ID:' 
				. $replyPost['user_name'] . '</span></div>';
				$replyPostImage = $replyPost['post_images'] == '' ? '' : '<span class="post-images"><a href="' . $config['imgURI'] . $replyPost['post_images'] . '"><img class="thumb" src="i-' . $replyPost['post_images'] . '"></a></span>';
				$replyContentPart = '<div class="reply post-content">' . $replyPostImage . '<span class="post-content">' . $replyPost['post_content'] . '</span></div>';
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
			$prev = '<span class="unavailable">&lt;</span>';
		} else {
			$prevPage = $areaArray['area_page'] - 1;
			$prev = '<span class="available"><a href="a-' . $areaArray['area_id'] .'-p-' . $prevPage . '" title="上一页">&lt;</a></span>';
		}
		// 最后一页不显示>
		if (floor($areaArray['posts_num'] / $areaArray['posts_per_page']) + 1 == $areaArray['area_page']) {
			$next = '<span class="unavailable">&gt;</span>';
		} else {
			$nextPage = $areaArray['area_page'] + 1;
			$next = '<span class="available"><a href="a-' . $areaArray['area_id'] .'-p-' . $nextPage . '" title="下一页">&gt;</a></span>';
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
		$postImage = $postArray['post_images'] == '' ? '' : '<span class="post-images"><a href="' . $config['imgURI'] . $postArray['post_images'] . '"><img class="thumb" src="' . $config['imgURI'] . $postArray['post_images'] . '"></a></span>';
		$contentPart = '<div class="post-content">' . $postImage . '<span class="post-content">' . $postArray['post_content'] . '</span></div>';
		$replyPart = '';
		foreach ($postArray['reply_recent_posts'] as $replyPost) {
			$replyTitlePart = '<div class="reply post-title-info"><span class="post-title">' 
			. $replyPost['post_title'] . '</span><span class="author-name">' 
			. $replyPost['author_name'] . '</span><span class="post_id">No.' 
			. $replyPost['post_id'] . '</span><span class="create-time">' 
			. $replyPost['create_time'] . '</span><span class="user-name">ID:' 
			. $replyPost['user_name'] . '</span></div>';
			$replyPostImage = $replyPost['post_images'] == '' ? '' : '<span class="post-images"><a href="'. $config['imgURI'] . $replyPost['post_images'] . '"><img class="thumb" src="' . $config['imgURI'] . $replyPost['post_images'] . '"></a></span>';
			$replyContentPart = '<div class="reply post-content">' . $replyPostImage . '<span class="post-content">' . $replyPost['post_content'] . '</span></div>';
			$replyPart .= $replyTitlePart . $replyContentPart;
		}
		$return = $titlePart . $contentPart . $replyPart;
		// 页码部分
		// 第一页不显示<
		if ($postArray['post_page'] == 1) {
			$prev = '<span class="unavailable">&lt;</span>';
		} else {
			$prevPage = $postArray['post_page'] - 1;
			$prev = '<span class="available"><a href="p-' . $postArray['post_id'] . '-page-' . $prevPage . '" title="上一页">&lt;</a></span>';
		}
		// 最后一页不显示>
		if ($postArray['post_page'] == 1) {
			$next = '<span class="unavailable">&gt;</span>';
		} else {
			$nextPage = $postArray['post_page'] + 1;
			$netx = '<span class="available"><a href="p-' . $postArray['post_id'] . '-page-' . $nextPage . '" title="下一页">&gt;</a></span>';
		}
		$current = '<span class="current" title="当前">' . $postArray['post_page'] . '</span>';
		$pageNumberPart = '<div class="page-number">' . $prev . ' ' . $current . ' ' . $next . '</div>';
		$return .= $pageNumberPart;
		return $return;       
	}
	
	// 发新串
	private static function sendPost($id, $areaId) {
		$action = 's-' . $id . '-a-' . $areaId;
		
		$return = '<form action="' . $action . '" method="post" enctype="multipart/form-data">' .
					'<span>标题</span><input type="text" name="title" placeholder="无标题"/><br />' .
					'<span>名称</span><input type="text" name="name" placeholder="无名氏"/><br />'.
					'<span>邮箱</span><input type="text" name="email" placeholder=""/><br />'.
					'<span>附件</span><input type="file" name="uploadFile"/><br />' .
					'<span style="float:left">正文</span>' .
					'<textarea style="margin-top:2px" name="content" require="require"></textarea><br />' .
					'<input type="submit" value="发送" />' .
					'</form>';
		return $return;
	}
}
?>