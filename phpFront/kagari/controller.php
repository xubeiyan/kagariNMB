<?php
/**
* 大概是控制器吧
*/
class Controller {
	// 数据库值替换
	public static function dbDataReplace($list, $toReplace) {
		global $config;
		
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