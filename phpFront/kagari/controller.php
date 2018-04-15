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
		$userAgent = $config['back']['userAgent'] == '' ? '' : $config['back']['userAgent'];
		
		$data = Array(
			'ip' => $_SERVER['REMOTE_ADDR']
		);
		$opts = Array(
			'http' => Array(
				'method' => 'POST',
				'user_agent' => $userAgent,
				'header' => "Content-type: application/json\r\n",
				'content' => json_encode($data, JSON_UNESCAPED_UNICODE)
			)
		);
		
		$context = stream_context_create($opts);
		$json = file_get_contents($config['uri']['backURI'] . 'api/getCookie', false, $context);
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
					'user_agent' => $userAgent,
				)
			);
			$context = stream_context_create($opts);
			$jsonResponse = file_get_contents($config['uri']['backURI'] . $api, false, $context);
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
					'user_agent' => $userAgent,
					'header' => "Content-type: application/json\r\n",
					'content' => json_encode($req, JSON_UNESCAPED_UNICODE)
				)
			);
			$context = stream_context_create($opts);
			$jsonResponse = file_get_contents($config['uri']['backURI'] . $api, false, $context);
			$arrayResponse = json_decode($jsonResponse, TRUE);
			
			return $arrayResponse['response'];
		// 串	
		} else if ($api == 'api/getPost') {
			$opts = Array(
				'http' => Array(
					'method' => 'POST',
					'user_agent' => $userAgent,
					'header' => "Content-type: application/json\r\n",
					'content' => json_encode($req, JSON_UNESCAPED_UNICODE)
				)
			);
			$context = stream_context_create($opts);
			$jsonResponse = file_get_contents($config['uri']['backURI'] . $api, false, $context);
			$arrayResponse = json_decode($jsonResponse, TRUE);
			
			return $arrayResponse['response'];
		// 新串
		} else if ($api == 'api/sendPost') {
			$opts = Array(
				'http' => Array(
					'method' => 'POST',
					'user_agent' => $userAgent,
					'header' => "Content-type: application/json\r\n",
					'content' => json_encode($req, JSON_UNESCAPED_UNICODE)
				)
			);
			$context = stream_context_create($opts);
			$jsonResponse = file_get_contents($config['uri']['backURI'] . $api, false, $context);
			//print_r($opts['http']['content']);
			$arrayResponse = json_decode($jsonResponse, TRUE);
			return $arrayResponse['response'];
		// 用户列表
		} else if ($api == 'api/getUserLists') {
			$opts = Array(
				'http' => Array(
					'method' => 'POST',
					'user_agent' => $userAgent,
					'header' => "Content-type: application/json\r\n",
					'content' => json_encode($req, JSON_UNESCAPED_UNICODE)
				)
			);
			$context = stream_context_create($opts);
			$jsonResponse = file_get_contents($config['uri']['backURI'] . $api, false, $context);
			//print_r($opts['http']['content']);
			$arrayResponse = json_decode($jsonResponse, TRUE);
			return $arrayResponse['response'];
		}
		
	}
}
?>