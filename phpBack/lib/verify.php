<?php
/**
* kagariNMB预检查类
*/

class Verify {
	/**
	* 检查User-Agent
	*/
	public static function userAgentVerify($userAgent) {
		if ($userAgent != '') {
			if ($_SERVER['HTTP_USER_AGENT'] != $userAgent) {
				return false;
			}	
		}
		return true;
	}
	
	/**
	* 检查FrontIP地址
	*/
	public static function frontIPVerify($ip) {
		if (!empty($ip)) {
			if (!in_array($_SERVER['REMOTE_ADDR'], $ip)) {
				return false;
			}
		}
		return true;
	}
}
?>