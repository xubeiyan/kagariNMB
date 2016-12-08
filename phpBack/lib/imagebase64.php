<?php
class ImageBase64 {
	private $fileInfo;
	private $data;
	
	/**
	* 从base64代码初始化
	*/
	public function __construct($base64Code) {
		$fileArray = explode(',', $base64Code);
		if (count($fileArray) < 2) {
			$this ->fileInfo = 'not an image...';
			return;
		}
		$this ->fileInfo = $fileArray[0];
		$this ->data = $fileArray[1];
	}
	
	/**
	* 获取内容
	*/
	public function data() {
		return $this ->data;
	}
	
	/**
	* 获取文件信息
	*/
	public function info() {
		$info = Array();
		$info['type'] = explode(':', explode(';', $this ->fileInfo)[0])[1];
		return $info;
	}
}
?>