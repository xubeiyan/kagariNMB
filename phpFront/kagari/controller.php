<?php
/**
* Controller部分，作出请求部分
*/
require 'model.php';
require 'view.php';

class Controller {
	public static function index($pageName) {
		$content = Model::TemplateContent($pageName);
		// 未找到对应的模板
		if ($content == 'not exist') {
			$pageContent = View::notExist($pageName);
			return $pageContent;
		}
		// 根据内容获取数据
		$data = Model::data($content);
		// 将数据渲染在页面上
		$pageContent = View::render($content, $data);
		return $pageContent;
	}
}
?>