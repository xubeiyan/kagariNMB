<?php
/**
* 大概是控制器吧
*/
class Controller {
	// 数据库值替换
	public static function dbDataReplace($list, $toReplace) {
		global $config;
		foreach ($list as $key => $value) {
			//print_r($key . '|' . $value);
			if ($key == 'areaLists') {
				$opts = Array(
					'http' => Array(
						'method' => 'GET',
						'user_agent' => 'KagariNMBFront'
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
						$string .= $arrval['area_name'] . '<br />';
					}
					$toReplace = str_replace('%' . $key . '%', $string , $toReplace);
				}
				
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