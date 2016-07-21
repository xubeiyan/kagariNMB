<?php
class Template {
	// 匿名版的某些固定值
	private static $template = Array (
		'nimingbanTitle' => 'kagari匿名版',
		'areaLists' => 'wwww',
		'welcomeInformation' => '欢迎！'
	);
	
	// 匿名版替换函数
	public static function replace($html) {
		foreach (self::$template as $key => $value) {
			if (is_string($value)) {
				$html = str_replace('%' . $key . '%', $value, $html);
			} else if(is_array($value)) {
				
			}
		}
		return $html;
	}
}
?>