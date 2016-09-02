<?php
/**
* 大概是控制器吧
*/
class Controller {
	// cookie设置与读取
	public static function cookies($toReplace) {
		global $config;
		if (!isset($_COOKIE['username'])) {
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
			//print_r($json);
			setcookie('username', $string['response']['username'], time() + 10 * 60); // 十分钟过期？
		} else {
			setcookie('username', $_COOKIE['username'], time() + 10 * 60);
		}
		
		//print_r($_COOKIE);
		if (isset($_COOKIE['username'])) {
			$toReplace = str_replace('%cookie%', $_COOKIE['username'], $toReplace);
		} else {
			$toReplace = str_replace('%cookie%', '未获取到饼干' , $toReplace);
		}
		//print_r($_COOKIE);
		//print_r($_REQUEST);
		return $toReplace;
	}
	// 数据库值替换
	public static function dbDataReplace($list, $toReplace) {
		global $config;
		foreach ($list as $key => $value) {
			//print_r($key . '|' . $value);
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
						if ($arrval['parent_area'] == "") {
							$string .= $arrval['area_name'] . '<br />';
						} else {
							$string .= '|' . $arrval['area_name'] . '<br />';
						}
					}
					$toReplace = str_replace('%' . $key . '%', $string , $toReplace);
				}
				
			} else if ($key == 'areaPosts') {
				$data = Array(
					'area_id' => '',
					'area_page' => ''
				);
				$opts = Array(
					'http' => Array(
						'method' => 'POST',
						'user_agent' => $config['userAgent'],
						'header' => "Content-type: application/json\r\n",
						'content' => json_encode($data, JSON_UNESCAPED_UNICODE)
					)
				);
			}
		}
		return $toReplace;
	}
	
	// 模板替换
	public static function templateReplace($template, $toReplace) {
		foreach ($template as $key => $value) {
			if (is_string($value)) {
				$toReplace = str_replace('%' . $key . '%', $value, $toReplace);
			} else if(is_array($value)) {
				
			}
		}
		return $toReplace;
	}
}
?>