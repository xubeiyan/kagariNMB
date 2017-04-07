<?php
/**
* 大概是控制器吧
*/
class Controller {
	/**
	* cookie读取
	* 参数：用户cookie中的username
	* 返回：新的cookie中的username
	* 		或者之前cookie的username
	*		或者dismatch（现有cookie和数据库中不匹配）
	*/
	public static function cookies($username) {
		global $config;
		
		$data = Array(
			'ip' => $_SERVER['REMOTE_ADDR']
		);
		$opts = Array(
			'http' => Array(
				'method' => 'POST',
				'user_agent' => $config['userAgent'],
				'header' => "Content-type: application/json\r\n",
				'content' => json_encode($data, JSON_UNESCAPED_UNICODE)
			)
		);
		
		$context = stream_context_create($opts);
		$json = file_get_contents($config['backURI'] . 'api/getCookie', false, $context);
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
		// 板块列表
		if ($api == 'api/getAreaLists') {
			$opts = Array(
				'http' => Array(
					'method' => 'GET',
					'user_agent' => $config['userAgent']
				)
			);
			$context = stream_context_create($opts);
			$jsonResponse = file_get_contents($config['backURI'] . $api, false, $context);
			$arrayResponse = json_decode($jsonResponse, TRUE);
			
			if ($arrayResponse['response']['areas'] == Array()) {
				return 'no areas';
			} else {
				return $arrayResponse['response']['areas'];
			}
		// 板块下串列表	
		} else if ($api == 'api/getAreaPosts') {
			$opts = Array(
				'http' => Array(
					'method' => 'POST',
					'user_agent' => $config['userAgent'],
					'header' => "Content-type: application/json\r\n",
					'content' => json_encode($req, JSON_UNESCAPED_UNICODE)
				)
			);
			$context = stream_context_create($opts);
			$jsonResponse = file_get_contents($config['backURI'] . $api, false, $context);
			$arrayResponse = json_decode($jsonResponse, TRUE);
			
			return $arrayResponse['response'];
		// 串	
		} else if ($api == 'api/getPost') {
			$opts = Array(
				'http' => Array(
					'method' => 'POST',
					'user_agent' => $config['userAgent'],
					'header' => "Content-type: application/json\r\n",
					'content' => json_encode($req, JSON_UNESCAPED_UNICODE)
				)
			);
			$context = stream_context_create($opts);
			$jsonResponse = file_get_contents($config['backURI'] . $api, false, $context);
			$arrayResponse = json_decode($jsonResponse, TRUE);
			
			return $arrayResponse['response'];
		// 新串
		} else if ($api == 'api/sendPost') {
			$opts = Array(
				'http' => Array(
					'method' => 'POST',
					'user_agent' => $config['userAgent'],
					'header' => "Content-type: application/json\r\n",
					'content' => json_encode($req, JSON_UNESCAPED_UNICODE)
				)
			);
			$context = stream_context_create($opts);
			$jsonResponse = file_get_contents($config['backURI'] . $api, false, $context);
			//print_r($opts['http']['content']);
			$arrayResponse = json_decode($jsonResponse, TRUE);
			return $arrayResponse['response'];
		// 用户列表
		} else if ($api == 'api/getUserLists') {
			$opts = Array(
				'http' => Array(
					'method' => 'POST',
					'user_agent' => $config['userAgent'],
					'header' => "Content-type: application/json\r\n",
					'content' => json_encode($req, JSON_UNESCAPED_UNICODE)
				)
			);
			$context = stream_context_create($opts);
			$jsonResponse = file_get_contents($config['backURI'] . $api, false, $context);
			//print_r($opts['http']['content']);
			$arrayResponse = json_decode($jsonResponse, TRUE);
			return $arrayResponse['response'];
		}
		
	}
	
	// 数据库值替换
	public static function dbDataReplace($list, $toReplace) {
		global $config;
		foreach ($list as $key => $value) {
			//print_r($key . '|' . $value);
			// 板块列表
			if ($key == 'areaLists') {
				$opts = Array(
					'http' => Array(
						'method' => 'GET',
						'user_agent' => $config['userAgent']
					)
				);
				$context = stream_context_create($opts);
				$json = file_get_contents($config['backURI'] . $value, false, $context);
				$array = json_decode($json, TRUE);
				//print_r($array);
				if (isset($array['response']['areas'])) {
					//print_r($array);
					$string = '';
					foreach ($array['response']['areas'] as $arrkey => $arrval) {
						// 是否为母板
						if ($arrval['parent_area'] == '') {
							$string .= $arrval['area_id'] . ' <b>' . $arrval['area_name'] . ' ' . $arrval['parent_area'] . '</b><br />';
						} else {
							$string .= '-' . $arrval['area_id'] . ' ' . $arrval['area_name'] . ' ' . $arrval['parent_area'] . '<br />';
						}
					}
					$toReplace = str_replace('%' . $key . '%', $string , $toReplace);
				}
			// 帖子列表
			} else if ($key == 'areaPosts') {
				$data = Array(
					'area_id' => 2
				);
				$opts = Array(
					'http' => Array(
						'method' => 'POST',
						'user_agent' => $config['userAgent'],
						'header' => "Content-type: application/json\r\n",
						'content' => json_encode($data, JSON_UNESCAPED_UNICODE)
					)
				);
				$context = stream_context_create($opts);
				$json = file_get_contents($config['backURI'] . $value, false, $context);
				$array = json_decode($json, TRUE);
				$posts = $array['response']['posts'];
				$out = '';
				
				foreach ($posts as $arrval) {
					$out .= '标题：' . $arrval['post_title'] . '<br />' .
							'发送者：' . $arrval['user_name'] . '<br />' .
							'发串时间：' . $arrval['create_time'] . '<br />' .  
							$arrval['post_content'] . '<br />';
							
					if ($arrval['reply_recent_post'] != Array()) {
						foreach ($arrval['reply_recent_post'] as $replyArray) {
							$out .= '>>标题：' . $replyArray['post_title'] . '<br />' .
									'>>发送者：' . $replyArray['user_name'] . '<br />' .
									'>>发串时间：' . $replyArray['create_time'] . '<br />' .  
									'>>' . $replyArray['post_content'] . '<br />';
						}
						
					}
					$out .= '<hr>';
				}
				$toReplace = str_replace('%' . $key . '%', $out, $toReplace);
				if ($posts != array()) {
					
				}
				
			}
		}
		return $toReplace;
	}
}
?>