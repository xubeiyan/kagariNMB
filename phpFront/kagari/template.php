<?php
class Template {
	// 匿名版的某些固定值
	private static $template = Array (
		'nimingbanTitle' => 'kagari匿名版',
		'welcomeInformation' => '<h3>Kagari匿名版欢迎你！</h3>'
	);
	
	// 匿名版的某些计算后的到的值
	private static $calculate = Array (
		'date' => 'Y年m月d日',
		'time' => 'H:i'
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
		$html = Controller::dbDataReplace(self::$dbData, $html);
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
			}
		}
		return $html;
	}
}
?>