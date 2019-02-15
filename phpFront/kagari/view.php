<?php 
/**
* View
*/
require 'config/config.php';

class View {
	/**
	* 未找到对应的模板
	*/
	public static function notExist($page) {
		return 'file ' . $page . '.html not exist...';
	}
	
	/**
	* 渲染函数
	*/
	public static function render($template, $data) {
		foreach ($data as $key => $value) {
			$template = str_replace('%' . $key . '%', $value, $template);
		}
		return $template;
	}
	
	// 板块列表处理函数
	public static function areaLists($areaListsArray) {
		// 没有板块
		if (count($areaListsArray['areas']) == 0) {
			return '<b>No area...</b>';
		}
		
		$return = '';
		foreach ($areaListsArray['areas'] as $value) {
			if ($value['parent_area'] == '') {
				$return .= '<div class="button menu-first"><b>' . $value['area_name'] . '</b></div>';
			} else {
				$return .= '<div class="button menu-second"><a href="?a=' . $value['area_id'] . '">' . $value['area_name'] . '</a></div>';
			}
		}
		return $return;
	}
	
	// 板块串处理函数
	public static function areaPosts($areaArray) {
		global $config;
		// 没有这个板块
		if (isset($areaArray['error']) ) {
			return 'no such area';
		}
		// 板块没有串
		if ($areaArray['posts'] == Array()) {
			return 'no posts';
		}
		$return = '';
		$areaPostsArray = $areaArray['posts'];

		foreach ($areaPostsArray as $areaPost) {
			$titlePart = '<div class="post-title-info"><span class="post-title">' 
			. $areaPost['post_title'] . '</span><span class="author-name">' 
			. $areaPost['author_name'] . '</span><span class="post-id">No.' 
			. $areaPost['post_id'] . '</span><span class="create-time">' . $areaPost['create_time'] .'</span><span class="user-name">ID:' . $areaPost['user_name'] . '</span><input class="reply-button" onclick="location.href=\'?p=' . $areaPost['post_id'] .'\'" type="button" value="回应" /></div>';
			$postImage = $areaPost['post_images'] == '' ? '' : '<span class="post-images"><a target="_blank" href="' . $config['uri']['imgURI'] . $areaPost['post_images'] . '"><img class="thumb" src="?i=' . $areaPost['post_images'] . '"></a></span>';
			$contentPart = '<div class="post-content">' . $postImage . '<span class="post-content">' . $areaPost['post_content'] . '</span></div>';
			$replyPart = '';
			
			if ($areaPost['reply_num'] > $config['display']['lastReplyPosts']) {
				$contentPart .= '<p class="tip">一共有' . $areaPost['reply_num'] . '条回复，当前只显示最新' . $config['display']['lastReplyPosts'] . '条回复，选择“回应”查看所有回复。</p>';
			}
			foreach ($areaPost['reply_recent_post'] as $replyPost) {
				$replyTitlePart = '<div class="post-title-info reply"><span class="post-title">' 
				. $replyPost['post_title'] . '</span><span class="author-name">' 
				. $replyPost['author_name'] . '</span><span class="post-id">No.' 
				. $replyPost['post_id'] . '</span><span class="create-time">' 
				. $replyPost['create_time'] . '</span><span class="user-name">ID:' 
				. $replyPost['user_name'] . '</span></div>';
				$replyPostImage = $replyPost['post_images'] == '' ? '' : '<span class="post-images"><a href="' . $config['folder']['imgURI'] . $replyPost['post_images'] . '"><img class="thumb" src="?i=' . $replyPost['post_images'] . '"></a></span>';
				$replyContentPart = '<div class="post-content reply">' . $replyPostImage . '<span class="post-content">' . $replyPost['post_content'] . '</span></div>';
				$replyPart .= $replyTitlePart . $replyContentPart;
			}
			
			$endPart = '<hr>';
			
			// print_r($areaArray);
			// exit();
			
			
			$return .= $titlePart . $contentPart . $replyPart . $endPart;
		}
		// 页码部分
		// 第一页不显示<
		if ($areaArray['area_page'] == 1) {
			$prev = '<span class="unavailable">&lt;-</span>';
		} else {
			$prevPage = $areaArray['area_page'] - 1;
			$prev = '<span class="available"><a href="?a=' . $areaArray['area_id'] .'&page' . $prevPage . '" title="上一页">&lt;-</a></span>';
		}
		// 最后一页不显示>
		if (floor($areaArray['posts_num'] / $areaArray['posts_per_page']) + 1 == $areaArray['area_page']) {
			$next = '<span class="unavailable">-&gt;</span>';
		} else {
			$nextPage = $areaArray['area_page'] + 1;
			$next = '<span class="available"><a href="?a=' . $areaArray['area_id'] .'&page=' . $nextPage . '" title="下一页">-&gt;</a></span>';
		}
		$current = '<span class="current" title="当前页面">第' . $areaArray['area_page'] . '页</span>';
		$pageNumberPart = '<div class="page-number">' . $prev . ' ' . $current . ' ' . $next . '</div>';
		$return .= $pageNumberPart;
		return $return;
	}
	
	// 串处理函数
	public static function post($postArray) {
		$return = '';
		//print_r($postArray);
		global $config;
		
		if (isset($postArray['error'])) {
			return 'no such post';
		}
		
		
		
		$titlePart = '<div class="post-title-info"><span class="post-title">' 
		. $postArray['post_title'] . '</span><span class="author-name">' 
		. $postArray['author_name'] . '</span><span class="post-id">No.' 
		. $postArray['post_id'] . '</span><span class="create-time">' . $postArray['create_time'] .'</span><span class="user-name">ID:' . $postArray['user_name'] . '</span></div>';
		$postImage = $postArray['post_images'] == '' ? '' : '<span class="post-images"><a target="_blank" href="' . $config['uri']['imgURI'] . $postArray['post_images'] . '"><img class="thumb" src="' . $config['uri']['imgURI'] . $postArray['post_images'] . '"></a></span>';
		$contentPart = '<div class="post-content">' . $postImage . '<span class="post-content">' . $postArray['post_content'] . '</span></div>';
		$replyPart = '';
		foreach ($postArray['reply_recent_posts'] as $replyPost) {
			
			// 增加回复串的'>>No.'字符处理
			preg_match_all('/\&gt;\&gt;No\.\d+/', $replyPost['post_content'], $matches);
			// print_r($matches[0]);
			foreach ($matches[0] as $match) {
				$replace = '<b>' . $match . '</b>';
				$replyPost['post_content'] = str_replace($match, $replace, $replyPost['post_content']);
			}
			
			$replyTitlePart = '<div class="post-title-info reply"><span class="post-title">' 
			. $replyPost['post_title'] . '</span><span class="author-name">' 
			. $replyPost['author_name'] . '</span><span class="post-id">No.' 
			. $replyPost['post_id'] . '</span><span class="create-time">' 
			. $replyPost['create_time'] . '</span><span class="user-name">ID:' 
			. $replyPost['user_name'] . '</span></div>';
			$replyPostImage = $replyPost['post_images'] == '' ? '' : '<span class="post-images"><a target="_blank" href="'. $config['uri']['imgURI'] . $replyPost['post_images'] . '"><img class="thumb" src="' . $config['uri']['imgURI'] . $replyPost['post_images'] . '"></a></span>';
			$replyContentPart = '<div class="post-content reply">' . $replyPostImage . '<span class="post-content">' . $replyPost['post_content'] . '</span></div>';
			$replyPart .= $replyTitlePart . $replyContentPart;
		}
		$return = $titlePart . $contentPart . $replyPart;
		// 页码部分
		// 第一页不显示<
		if ($postArray['post_page'] == 1) {
			$prev = '<span class="unavailable">&lt;-</span>';
		} else {
			$prevPage = $postArray['post_page'] - 1;
			$prev = '<span class="available"><a href="?p=' . $postArray['post_id'] . '&page=' . $prevPage . '" title="上一页">&lt;-</a></span>';
		}
		// 最后一页不显示>
		if ($postArray['post_page'] == 1) {
			$next = '<span class="unavailable">-&gt;</span>';
		} else {
			$nextPage = $postArray['post_page'] + 1;
			$netx = '<span class="available"><a href="?p=' . $postArray['post_id'] . '&page=' . $nextPage . '" title="下一页">-&gt;</a></span>';
		}
		$current = '<span class="current" title="当前页面">第' . $postArray['post_page'] . '页</span>';
		$pageNumberPart = '<div class="page-number">' . $prev . ' ' . $current . ' ' . $next . '</div>';
		$return .= $pageNumberPart;
		return $return;       
	}
	
	// 发新串部分
	public static function sendPost($areaId) {
		$action = '?s=' . $areaId;
		
		$return = '<form action="' . $action . '" method="post" enctype="multipart/form-data">' .
					'<span>标题</span><input type="text" name="title" placeholder="无标题"/>' .
					'<span>名称</span><input type="text" name="name" placeholder="无名氏"/>'.
					'<span>邮箱</span><input type="text" name="email" placeholder=""/>'.
					'<span>附件</span><input type="file" name="uploadFile"/>' .
					'<span style="float:left">正文</span>' .
					'<textarea name="content" require="require"></textarea>' .
					'<input type="submit" value="发送" />' .
					'</form>';
		return $return;
	}
	
	// 回复串部分
	public static function sendReply($postId, $areaId) {
		$action = '?r=' . $postId . '&area=' . $areaId;
		$return = '<form action="' . $action . '" method="post" enctype="multipart/form-data">' .
					'<span>回复No.' . $postId . '</span><br />' .
					'<span>标题</span><input type="text" name="title" placeholder="无标题"/>' .
					'<span>名称</span><input type="text" name="name" placeholder="无名氏"/>'.
					'<span>邮箱</span><input type="text" name="email" placeholder=""/>'.
					'<span>附件</span><input type="file" name="uploadFile"/>' .
					'<span style="float:left">正文</span>' .
					'<textarea name="content" require="require"></textarea>' .
					'<input type="submit" value="发送" />' .
					'</form>';
		return $return;
	}
	
	// 登录框体
	public static function adminLogin() {
		$action = '?login';
		$return = '<div id="admin-login-button" class="button menu-first">权限狗认证处</div>' .
					'<div id="admin-login-list"><form action="' . $action . '" method="post" enctype="multipart/form-data">' .
					'<span>用户名</span><input type="text" name="username" placeholder="Username">' .
					'<span>密码</sapn><input type="password" name="password" placeholder="Password"><br />' .
					'<input type="submit" value="登录" />' .
					'</form></div>';
		return $return;
	}
}
?>