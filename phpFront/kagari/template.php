<?php


class Template {
	// 匿名版的某些固定值
	private static $template = Array (
		'nimingbanTitle' => 'kagari匿名版',
		'welcomeInformation' => '欢迎！'
	);
	
	// 匿名版里需要从数据库读取的值
	private static $dbData = Array (
		'cookie' => 'api/getCookie',
		'areaLists' => 'api/getAreaLists',
		'areaPosts' => 'api/getAreaPosts',
		'post' => 'api/getPost',
		'sendPost' => 'api/sendPost'
	);
	
	// 匿名版替换函数
	public static function replace($html) {
		require('controller.php');
		// Cookie设置函数
		$html = Controller::cookies($html);
		// 数据库数据替换
		$html = Controller::dbDataReplace(self::$dbData, $html);
		
		// 固定参数替换
		$html = Controller::templateReplace(self::$template, $html);
		
		return $html;
	}
}
?>