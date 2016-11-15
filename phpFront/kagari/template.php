<?php
class Template {
	// 匿名版的某些固定值
	private static $template = Array (
		'nimingbanTitle' => 'kagari匿名版',
		'welcomeInformation' => '<h3>Kagari匿名版欢迎你！</h3>',
		'areaListText' => '版块列表',
		'functionText' => '功能'
	);
	
	// 匿名版的某些计算后的到的值
	private static $calculate = Array (
		'date' => 'Y年m月d日',
		'time' => 'H:i',
		'admin' => '权限狗认证处'
	);
	
	// 匿名版里需要从数据库读取的值
	private static $dbData = Array (
		'cookie' => 'api/getCookie',
		'areaLists' => 'api/getAreaLists',
		'areaPosts' => 'api/getAreaPosts',
		'post' => 'api/getPost',
		'sendPost' => 'api/sendPost'
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
			}
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
				
				//
				if ($templateString == 'areaLists') {
					$data = Controller::apis(self::$dbData[$templateString], Array());
					$string = self::areaLists($data);
					$html = str_replace('%' . $templateString . '%', $string, $html);
				} else if ($templateString == 'areaPosts') {
					$queryArray = explode('-', $_GET['q']);
					$areaId = $queryArray[1];
					if (count($queryArray) == 4) {
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
					
					$html = str_replace('%' . $templateString . '%', $string, $html);
					$html = str_replace('%areaId%', $areaId, $html);
					$html = str_replace('%areaName%', $data['area_name'], $html);
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
		if ($areaArray['posts'] == Array()) {
			return '<b>No Posts...</b>';
		}
		$return = '';
		$areaPostsArray = $areaArray['posts'];
		//print_r($areaPostsArray);
		foreach ($areaPostsArray as $areaPost) {
			$titlePart = '<div class="post-title-info"><span class="post-title">' 
			. $areaPost['post_title'] . '</span><span class="author-name">' 
			. $areaPost['author_name'] . '</span><span class="post-id">No.' 
			. $areaPost['post_id'] . '</span><span class="create-time">' . $areaPost['create_time'] .'</span><span class="user-name">ID:' . $areaPost['user_name'] . '</span><input class="replay-button" onclick="location.href=\'p-' . $areaPost['post_id'] .'\'" type="button" value="回应" /></div>';
			$postImage = $areaPost['post_images'] == '' ? '' : '<span class="post-images"><a href=""><img class="thumb" src="images-' . $areaPost['post_images'] . '"></a></span>';
			$contentPart = '<div class="post-content">' . $postImage . '<span class="post-content">' . $areaPost['post_content'] . '</span></div>';
			$replyPart = '';
			foreach ($areaPost['reply_recent_post'] as $replyPost) {
				$replyTitlePart = '<div class="reply post-title-info"><span class="post-title">' 
				. $replyPost['post_title'] . '</span><span class="author-name">' 
				. $replyPost['author_name'] . '</span><span class="post_id">No.' 
				. $replyPost['post_id'] . '</span><span class="create-time">' 
				. $replyPost['create_time'] . '</span><span class="user-name">ID:' 
				. $replyPost['user_name'] . '</span></div>';
				$replyContentPart = '<div class="reply post-content"><span class="post-content">' . $replyPost['post_content'] . '</span></div>';
				$replyPart .= $replyTitlePart . $replyContentPart;
			}
			
			$endPart = '<hr>';
			
			$return .= $titlePart . $contentPart . $replyPart . $endPart;
		}
		return $return;
	}
}
?>