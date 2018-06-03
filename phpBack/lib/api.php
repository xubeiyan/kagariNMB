<?php
/**
* api列表
*/
class API {
	/**
	* 获取饼干, 需要主动调用？
	* `ip`(客户端的ip地址，必须)
	*/
	public static function getCookie($input) {
		$return['request'] = 'getCookie';
		$return['response']['timestamp'] = self::timestamp();
		
		global $conf, $con;
		// ip地址是否合法
		if (filter_var($input['ip'], FILTER_VALIDATE_IP)) {
			$ip = $input['ip'];
		} else {
			$return['response']['error'] = 'invaild IP address...';
			echo json_encode($return, JSON_UNESCAPED_UNICODE);
			exit();
		}
		
		$table = $conf['databaseName'] . '.' . $conf['databaseTableName']['user'];
		$sql = 'SELECT * FROM ' . $table . ' WHERE ip_address="' . $ip . '"';
		// 查询访问ip是否在数据库中
		$result = mysqli_query($con, $sql);
		if (!empty($row = mysqli_fetch_assoc($result))) {
			$return['response']['ip'] = $row['ip_address'];
			$return['response']['username'] = $row['user_name'];
			echo json_encode($return, JSON_UNESCAPED_UNICODE);
			exit();
		} else {
			// 未在数据库中
			$maxsql = 'SELECT max(user_id) FROM ' . $table;
			$result = mysqli_query($con, $maxsql);
			// 计算user_id
			if (empty($row = mysqli_fetch_assoc($result))) {
				$id = 1;
			} else {
				$id = $row['max(user_id)'] + 1;
			}
			$username = self::randomString($id);
			$sql = 'INSERT INTO ' . $table . '(ip_address, user_name, last_post_id) VALUES ("' . $ip . '", "' . $username . '", 0)';
			if (mysqli_query($con, $sql)) {
				$return['response']['ip'] = $ip;
				$return['response']['username'] = $username;
				echo json_encode($return, JSON_UNESCAPED_UNICODE);
				exit();
			} else {
				die(mysqli_error($con));
			}
		}
	}
	
	/**
	* 获取板块列表
	*/
	public static function getAreaLists() {
		global $conf, $con;
		$return['request'] = 'getAreaLists';
		$return['response']['timestamp'] = self::timestamp();
		$return['response']['areas'] = Array();
		
		$areaTable = $conf['databaseName'] . '.' . $conf['databaseTableName']['area'];
		$sql = 'SELECT area_id, area_name, parent_area FROM ' . $areaTable;
		$result = mysqli_query($con, $sql);
		// 返回所有的area
		for ($row = mysqli_fetch_assoc($result); !empty($row); $row = mysqli_fetch_assoc($result)) {
			$area['area_id'] = $row['area_id'];
			$area['area_name'] = $row['area_name'];
			$area['parent_area'] = $row['parent_area'] != 0 ? $row['parent_area'] : "";
			array_push($return['response']['areas'], $area);
		}
		echo json_encode($return, JSON_UNESCAPED_UNICODE);
		exit();
	}
	
	/**
	* 获取板块串
	* `area_id`(板块id，必须)  
	* `area_page`(板块页数，默认是1)
	*/
	public static function getAreaPosts($post) {
		$return['request'] = 'getAreaPosts';
		$return['response']['timestamp'] = self::timestamp();
		
		$area_id = is_numeric($post['area_id']) ? $post['area_id'] : 1;
		$area_page = isset($post['area_page']) && is_numeric($post['area_page']) && $post['area_page'] > 0 ? $post['area_page'] : 1;
		
		global $conf, $con;
		$userTable = $conf['databaseName'] . '.' . $conf['databaseTableName']['user'];
		
		// 查询所在area是否存在
		$areaTable = $conf['databaseName'] . '.' . $conf['databaseTableName']['area'];
		$sql = 'SELECT area_id, area_name, posts_num FROM ' . $areaTable . ' WHERE area_id=' . $area_id;
		$result = mysqli_query($con, $sql);
		// 检查结果是否非空
		if (!empty($row = mysqli_fetch_assoc($result))) {
			$return['response']['area_id'] = intval($row['area_id']);
			$return['response']['area_name'] = $row['area_name'];
			$postsNum = $row['posts_num'];
		} else {
			$return['response']['error'] = 'Not such area with area_id=' . $area_id;
			echo json_encode($return, JSON_UNESCAPED_UNICODE);
			exit();
		}
		
		// $postsPerPage为每页post数量，$lastReplyPosts为最多显示多少条post回复
		$postTable = $conf['databaseName'] . '.' . $conf['databaseTableName']['post'];
		$postsPerPage = $conf['postsPerPage'];
		$lastReplyPosts = $conf['lastReplyPosts'];
		
		$return['response']['area_page'] = intval($area_page);
		$return['response']['posts_per_page'] = intval($postsPerPage);
		$return['response']['posts_num'] = intval($postsNum);
		$return['response']['last_reply_posts'] = intval($lastReplyPosts);
		$return['response']['posts'] = Array();
		
		// 查询所在post表
		$sql = 'SELECT * FROM ' . $postTable . ' WHERE area_id=' . $area_id .' AND reply_post_id=0 ORDER BY update_time DESC LIMIT ' . $postsPerPage . ' OFFSET ' . ($area_page - 1) * $postsPerPage;
		
		$result = mysqli_query($con, $sql);
		
		$forloop = 0;
		// 返回当前页面的postPerPage数量的主串
		for ($row = mysqli_fetch_assoc($result); !empty($row); $row = mysqli_fetch_assoc($result), $forloop += 1) {
			//print_r($row);
			$sql = 'SELECT user_name FROM ' . $userTable . ' WHERE user_id=' . $row['user_id'];
			$userResult = mysqli_query($con, $sql);
			$userRow = mysqli_fetch_assoc($userResult);
			
			$sql = 'SELECT COUNT(post_id) FROM ' . $postTable . ' WHERE reply_post_id=' . $row['post_id'];
			$replyNumResult = mysqli_query($con, $sql); 
			$replyNum = mysqli_fetch_assoc($replyNumResult);
			//print_r($replyNum);
			
			$postArray['post_id'] = intval($row['post_id']);
			$postArray['post_title'] = $row['post_title'];
			$postArray['post_content'] = $row['post_content'];
			$postArray['post_images'] = $row['post_images'];
			$postArray['user_id'] = intval($row['user_id']);
			$postArray['user_name'] = $userRow['user_name'];
			$postArray['author_name'] = $row['author_name'];
			$postArray['author_email'] = $row['author_email'];
			$postArray['create_time'] = $row['create_time'];
			$postArray['update_time'] = $row['update_time'];
			$postArray['reply_num'] = intval($replyNum['COUNT(post_id)']);
			$postArray['reply_recent_post'] = Array();
			
			// 再次查询reply_post_id=指定值的结果，但结果是降序的，已搞定
			$sql = 'SELECT * FROM ' . $postTable . ' WHERE area_id=' . $area_id . ' AND reply_post_id=' . $row['post_id'] . ' ORDER BY update_time DESC LIMIT ' . $lastReplyPosts;
			$replyResult = mysqli_query($con, $sql);
			//echo $sql;
			// 如果非空则将其写入reply_recent_post
			for ($replyRow = mysqli_fetch_assoc($replyResult); !empty($replyRow); $replyRow = mysqli_fetch_assoc($replyResult)) {
				//print_r($replyRow);
				$sql = 'SELECT user_name FROM ' . $userTable . ' WHERE user_id=' . $replyRow['user_id'];
				$userResult = mysqli_query($con, $sql);
				$userRow = mysqli_fetch_assoc($userResult);
				
				$replyPostArray['post_id'] = intval($replyRow['post_id']);
				$replyPostArray['post_title'] = $replyRow['post_title'];
				$replyPostArray['post_content'] = $replyRow['post_content'];
				$replyPostArray['post_images'] = $replyRow['post_images'];
				$replyPostArray['user_id'] = intval($replyRow['user_id']);
				$replyPostArray['user_name'] = $userRow['user_name'];
				$replyPostArray['author_name'] = $replyRow['author_name'];
				$replyPostArray['author_email'] = $replyRow['author_email'];
				$replyPostArray['create_time'] = $replyRow['create_time'];
				$replyPostArray['update_time'] = $replyRow['update_time'];
				array_push($postArray['reply_recent_post'], $replyPostArray);
				//$postArray['reply_num'] += 1;
			}
			// 倒序reply_recent_post
			$postArray['reply_recent_post'] = array_reverse($postArray['reply_recent_post']);
			array_push($return['response']['posts'], $postArray);
		}
		// 为空则返回
		if (empty($row) && $forloop == 0) {
			$return['response']['info'] = 'No posts in area with area_id=' . $area_id;
			echo json_encode($return, JSON_UNESCAPED_UNICODE);
			exit();
		}
		echo json_encode($return, JSON_UNESCAPED_UNICODE);
		exit();
	}
	
	/**
	* 获取串内容
	* `post_id`
	* `post_page` (默认为1)
	*/
	public static function getPost($post) {
		$return['request'] = 'getPost';
		$return['response']['timestamp'] = self::timestamp();
		
		if (!isset($post['post_id']) && !is_numeric($post['post_id']) && $post['post_id'] < 10000) {
			$return['response']['error'] = 'No such posts found';
			echo json_encode($return, JSON_UNESCAPED_UNICODE);
			exit();
		} else {
			$post_id = $post['post_id'];
		}
		
		$post_page = isset($post['post_page']) && is_numeric($post['post_page']) && $post['post_page'] > 0 ? $post['post_page'] : 1;
		
		global $con, $conf;
		
		$userTable = $conf['databaseName'] . '.' . $conf['databaseTableName']['user'];
		$postTable = $conf['databaseName'] . '.' . $conf['databaseTableName']['post'];
		$areaTable = $conf['databaseName'] . '.' . $conf['databaseTableName']['area'];
		$postsPerPage = $conf['postsPerPage'];
		// 查询所在post表
		$sql = 'SELECT * FROM ' . $postTable . ' WHERE post_id=' . $post_id .' AND reply_post_id=0 LIMIT 1';
		//echo $sql;
		$result = mysqli_query($con, $sql);
		
		// 主贴处理
		if (empty($mainPostRow = mysqli_fetch_assoc($result))) {
			$return['response']['error'] = 'No such posts found with post_id ' . $post_id;
			echo json_encode($return, JSON_UNESCAPED_UNICODE);
			exit();
		}
		
		// 从里面获取用户名
		$sql = 'SELECT user_name FROM ' . $userTable . ' WHERE user_id=' . $mainPostRow['user_id'];
		$userResult = mysqli_query($con, $sql);
		$userResultRow = mysqli_fetch_assoc($userResult);
		
		$return['response']['area_id'] = intval($mainPostRow['area_id']);
		
		// 从中获取板块名称
		$sql = 'SELECT area_name FROM ' . $areaTable . ' WHERE area_id=' . $mainPostRow['area_id'];
		$areaResult = mysqli_query($con, $sql);
		$areaResultRow = mysqli_fetch_assoc($areaResult);
		$return['response']['area_name'] = $areaResultRow['area_name'];
		
		$return['response']['post_id'] = intval($post_id);
		$return['response']['post_page'] = intval($post_page);
		$return['response']['posts_per_page'] = intval($postsPerPage);
		$return['response']['post_title'] = $mainPostRow['post_title'];
		$return['response']['post_content'] = $mainPostRow['post_content'];
		$return['response']['post_images'] = $mainPostRow['post_images'];
		$return['response']['user_id'] = intval($mainPostRow['user_id']);
		$return['response']['user_name'] = $userResultRow['user_name'];
		$return['response']['author_name'] = $mainPostRow['author_name'];
		$return['response']['author_email'] = $mainPostRow['author_email'];
		$return['response']['create_time'] = $mainPostRow['create_time'];
		$return['response']['update_time'] = $mainPostRow['update_time'];
		$return['response']['reply_posts_num'] = intval($mainPostRow['reply_posts_num']);
		$return['response']['reply_recent_posts'] = Array();
		
		// 回帖处理
		$sql = 'SELECT * FROM ' . $postTable . ' WHERE reply_post_id=' . $post_id . ' ORDER BY update_time ASC LIMIT ' . $postsPerPage . ' OFFSET ' . ($post_page - 1) * $postsPerPage;
		$replyResult = mysqli_query($con, $sql);
		//echo $sql;
		
		for ($replyRow = mysqli_fetch_assoc($replyResult); !empty($replyRow); $replyRow = mysqli_fetch_assoc($replyResult)) {
			$sql = 'SELECT user_name FROM ' . $userTable . ' WHERE user_id=' . $replyRow['user_id'];
			$userResult = mysqli_query($con, $sql);
			$userResultRow = mysqli_fetch_assoc($userResult);
			
			$replyArray['post_id'] = intval($replyRow['post_id']);
			$replyArray['user_id'] = intval($replyRow['user_id']);
			$replyArray['user_name'] = $userResultRow['user_name'];
			$replyArray['author_name'] = $replyRow['author_name'];
			$replyArray['author_email'] = $replyRow['author_email'];
			$replyArray['post_title'] = $replyRow['post_title'];
			$replyArray['post_content'] = $replyRow['post_content'];
			$replyArray['post_images'] = $replyRow['post_images'];
			$replyArray['create_time'] = $replyRow['create_time'];
			$replyArray['update_time'] = $replyRow['update_time'];
			array_push($return['response']['reply_recent_posts'], $replyArray);
			//$return['response']['reply_num'] += 1;
		}
		
		echo json_encode($return, JSON_UNESCAPED_UNICODE);
		exit();
	}
	
	/**
	* 发表新串
	* `user_name`(用户名，必需)
	* `area_id`(区id，必须)
	* `user_ip`(用户ip，必须)
	* `reply_post_id`(回复串id)
	* `author_name`   
	* `author_email`   
	* `post_title`   
	* `post_content`(串内容，必需)    
	* `post_image`
	*/
	public static function sendPost($post) {
		// 返回目标
		$return['request'] = 'sendPost';
		$return['response']['timestamp'] = self::timestamp();
		global $conf, $con;
		$user_table = $conf['databaseName'] . '.' . $conf['databaseTableName']['user'];
		$area_table = $conf['databaseName'] . '.' . $conf['databaseTableName']['area'];
		$post_table = $conf['databaseName'] . '.' . $conf['databaseTableName']['post'];
		
		$user_name = $post['user_name'];
		
		$ip = $post['user_ip'];
		$sql = 'SELECT user_id, last_post_time, user_status, block_end_time FROM ' . $user_table . ' WHERE ip_address="' . $ip . '" AND user_name="' . $user_name . '"';
		$result = mysqli_query($con, $sql);
		// 未找到则返回错误
		if (empty($row = mysqli_fetch_assoc($result))) {
			$return['response']['error'] = 'Not exists such user';
			echo json_encode($return, JSON_UNESCAPED_UNICODE);
			exit();
		}
		
		$last_post_time = $row['last_post_time'];
		// 根据状态查询
		$user_status = $row['user_status'];
		if ($user_status == 'forbid') {
			$return['response']['error'] = 'This user is forbid forever';
			echo json_encode($return, JSON_UNESCAPED_UNICODE);
			exit();
		} else if ($user_status == 'block') {
			$block_end_time = strtotime($row['block_end_time']); // 封禁时间 类似2017-03-14 12:53:52
			if ($block_end_time > time()) {
				$return['response']['error'] = 'This user is blocked';
				$return['response']['block_end_time'] = $block_end_time;
				echo json_encode($return, JSON_UNESCAPED_UNICODE);
				exit();
			} else {
				$updateSql = 'UPDATE ' . $user_table . ' SET `user_status` = "normal" WHERE `user_id` = ' . $row['user_id'];
				if (!mysqli_query($con, $updateSql)) {
					die(mysqli_error($con));
				}
			}
		}
		
		// 将找到的用户id赋给$user_id
		$user_id = $row['user_id'];
		// 检查区id
		$area_id = is_numeric($post['area_id']) ? $post['area_id'] : 0;
		$sql = 'SELECT area_id, posts_num, min_post FROM ' . $area_table . ' WHERE area_id=' . $area_id;
		$result = mysqli_query($con, $sql);
		if (!$row = mysqli_fetch_assoc($result)) {
			$return['response']['error'] = 'Not exist such area';
			echo json_encode($return, JSON_UNESCAPED_UNICODE);
			exit();
		} else {
			$postsNum = $row['posts_num'];
			$minPostSeconds = $row['min_post'];
		}
		// 检查最小发串时间
		$current_unix_timestamp = time();
		$last_post_timestamp = strtotime($last_post_time);
		// print_r(date('Y-m-d H:i:s', $current_unix_timestamp));
		// print_r(date('Y-m-d H:i:s', $last_post_timestamp));
		// print $minPostSeconds;
		// exit();
		if ($current_unix_timestamp - $last_post_timestamp < $minPostSeconds) {
			$return['response']['error'] = 'Post time interval too short';
			$return['response']['last_post_time'] = $last_post_time;
			//$return['response']['next_post_time'] = 
			echo json_encode($return, JSON_UNESCAPED_UNICODE);
			exit();
		}
		// 检查reply_post_id，只检查不为空的情况
		$reply_post_id = isset($post['reply_post_id']) && is_numeric($post['reply_post_id']) ? $post['reply_post_id'] : 0;
		if ($reply_post_id != 0) {
			$sql = 'SELECT reply_post_id, reply_posts_num FROM ' . $post_table . ' WHERE post_id=' . $reply_post_id;
			$result = mysqli_query($con, $sql);
			// 先检查回帖是否存在
			if (empty($row = mysqli_fetch_assoc($result))) {
				$return['response']['error'] = 'Post not exists';
				echo json_encode($return, JSON_UNESCAPED_UNICODE);
				exit();
			// 再检查回复的帖子是否为主串
			} else if ($row['reply_post_id'] != 0) {
				$return['response']['error'] = 'Post is reply post';
				echo json_encode($return, JSON_UNESCAPED_UNICODE);
				exit();
			// 是主串
			} else {
				$replyPostsNum = $row['reply_posts_num'];
			}
		}
		
		// 补全其他字段
		$author_name = !isset($post['author_name']) ? $conf['default_author_name'] : $post['author_name'];
		$author_email = !isset($post['author_email']) ? '' : htmlspecialchars($post['author_email']);
		$post_title = !isset($post['post_title']) ? $conf['default_post_title'] : htmlspecialchars($post['post_title']);
		// 如果串内容为空则返回错误
		if (!isset($post['post_content']) && $post['post_content'] == '') {
			$return['response']['error'] = 'content can not be empty';
			echo json_encode($return, JSON_UNESCAPED_UNICODE);
			exit();
		}
		$post_content = htmlspecialchars($post['post_content']);
		
		// 处理提交的base64图片编码
		//print '123';
		if (isset($post['post_image'])) {
			require_once('imagebase64.php');
			$image = new ImageBase64($post['post_image']);
			$fileInfo = $image->info();
			$data = $image->data();
			if ($fileInfo['type'] == 'image/jpeg') {
				$ext = 'jpg';
			} else if ($fileInfo['type'] == 'image/png') {
				$ext = 'png';
			} else if ($fileInfo['type'] == 'image/gif') {
				$ext = 'gif';
			} else {
				$ext = 'html';
			}
			$post_image_filename = self::randomTimeMd5() . '.' . $ext;
			$image_file = base64_decode($data);
			$result = file_put_contents($conf['uploadPath'] . '//' . $post_image_filename, $image_file);
			if ($result == FALSE) {
				$post_image_filename = '';
			}
		} else {
			$post_image_filename = '';
		}
		
		
		// 发送请求
		$sql = 'INSERT INTO ' . $post_table . 
		'(area_id, user_id, reply_post_id, author_name, author_email, post_title, post_content, post_images, create_time, update_time) VALUES (' . 
		$area_id . ',' . $user_id . ',' . $reply_post_id . ',"' . $author_name . '","' . $author_email . '","' . $post_title . '","' . $post_content . '","' . $post_image_filename . '", CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)';
		
		// 如果reply_post_id不为0（为回复串），更新主串update_time，并增加主串回帖数记录
 		if ($reply_post_id != 0) {
			// 新增：post_title为SAGE则不更新时间（所谓的串被SAGE了）
			if ($post_title != $conf['sageString']) {
				$notSageSql = ', update_time=CURRENT_TIMESTAMP';
			} else {
				$notSageSql = '';
			}
 			// 增加主串回帖数目记录
			$replyPostsNum += 1;
			$updatesql = 'UPDATE ' . $post_table . ' SET reply_posts_num=' . $replyPostsNum . $notSageSql . ' WHERE post_id=' . $reply_post_id;
 			if (!mysqli_query($con, $updatesql)) {
 				die(mysqli_error($con));
 			}
		// 如果reply_post_id为0（即为主串），增加area表内主串数记录
 		} else {
			// 改写area表主串数posts_num
			$postsNum += 1;
			$updatesql = 'UPDATE ' . $area_table . ' SET posts_num=' . $postsNum . ' WHERE area_id=' . $area_id;
			//print_r($updatesql);
			if (!mysqli_query($con, $updatesql)) {
				die(mysqli_error($con));
			}
		}
		
		// 改写last_post_id以及last_post_time
		$last_insert_id = mysqli_insert_id($con);
		$updatesql = 'UPDATE ' . $user_table . ' SET last_post_time=CURRENT_TIMESTAMP, last_post_id=' . $last_insert_id . ' WHERE user_id=' . $user_id;
		if (!mysqli_query($con, $updatesql)) {
			die(mysqli_error($con));
		}
			
		//echo $sql;
		if (mysqli_query($con, $sql)) {
			$return['response']['status'] = "OK";
			echo json_encode($return, JSON_UNESCAPED_UNICODE);
			exit();
		} else {
			die(mysqli_error($con));
		}
	}
	
	/**
	* 管理员登录
	* `username` 用户名
	* `password` 密码
	*/
	public static function adminLogin($post) {
		$return['request'] = 'adminLogin';
		$return['response']['timestamp'] = self::timestamp();
		
		global $conf, $con;
		
		$sql = 'SELECT password FROM ' . $conf['databaseTableName']['admin'] . ' WHERE username = "' .
			$post['username'] . '" LIMIT 1';
		$result = mysqli_query($con, $sql);

		if (mysqli_num_rows($result) == 0) {
			$return['response']['error'] = 'username or password wrong';
			echo json_encode($resturn, JSON_UNESCAPED_UNICODE);
			exit();	
		}
		
		$row = mysqli_fetch_assoc($result);
		
		if ($row['password'] != $post['password']) {
			$return['response']['error'] = 'username or password wrong';
			echo json_encode($resturn, JSON_UNESCAPED_UNICODE);
			exit();
		}
		
		// 使用时间戳，考虑到对老版本php的支持
		$return['response']['secretKey'] = substr(md5(time()), 0, 10);
		$date_in_30min = time() + 30 * 60;
		$return['response']['expireTime'] = date('Y-m-d H:i;s', $date_in_30min);
		$updateSql = 'UPDATE ' . $conf['databaseTableName']['admin'] . ' SET secretKey = "' . 
			$return['response']['secretKey'] . '", expireTime = "' . $return['response']['expireTime'] . '"';
			
		if (!mysqli_query($con, $updateSql)) {
			die(mysqli_error($con));
		}
		
		$return['response']['status'] = 'OK';
		echo json_encode($return, JSON_UNESCAPED_UNICODE);
		exit(); 
		
	}
	
	/**
	* 增加板块(需要权限)
	* `area_name` 板块名
	* `parent_area` 为某板块的子版块，0为无
	*/
	public static function addArea($post) {
		$return['request'] = 'addArea';
		$return['response']['timestamp'] = self::timestamp();
		
		// area_name未设置或为空
		if (!isset($post['area_name']) && $post['area_name'] == '') {
			$return['response']['error'] = 'area name must not be empty';
			echo json_encode($resturn, JSON_UNESCAPED_UNICODE);
			exit();
		}
		
		$area_name = $post['area_name'];
		$parent_area = isset($post['parent_area']) && is_numeric($post['parent_area']) && $post['parent_area'] > 0 ? $post['parent_area'] : 0;
		
		global $con, $conf;
		$areaTable = $conf['databaseName'] . '.' . $conf['databaseTableName']['area'];
		// 检查area_name及parent_id是否和现有的一致
		$sql = 'SELECT area_name FROM ' . $areaTable . ' WHERE area_name=' . $area_name . ' AND parent_area=' . $parent_area;
		$result = mysqli_query($con, $sql);
		if (!empty($result)) {
			$return['response']['error'] = 'area ' . $area_name . ' has existed in parent area=' . $parent_area;
			echo json_encode($resturn, JSON_UNESCAPED_UNICODE);
			exit();
		}
		
		// 不存在则可以插入新的分区
		$sql = 'INSERT INTO ' . $areaTable . ' (area_name, area_sort, block_status, parent_area, min_post) VALUES ("' . $area_name . '" ,0 ,0 ,' . $parent_area .',0)';
		if(mysqli_query($con, $sql)) {
			$return['response']['status'] = 'OK';
			echo json_encode($return, JSON_UNESCAPED_UNICODE);
			exit(); 
		} else {
			//print $sql;
			die(mysqli_error($con));
		}
		
	}
	
	/**
	* 删除板块(需要权限
	* `area_id` 
	*/
	public static function deleteArea($post) {
		$return['request'] = 'deleteArea';
		$return['response']['timestamp'] = self::timestamp();
		
		$area_id = is_numeric($post['area_id']) && $post['area_id'] > 0 ? $post['area_id'] : 0;
		
		global $con, $conf;
		$areaTable = $conf['databaseName'] . '.' . $conf['databaseTableName']['area'];
		$postTable = $conf['databaseName'] . '.' . $conf['databaseTableName']['post'];

		// 查询指定的版
		$sql = 'SELECT area_id FROM ' . $areaTable . ' WHERE area_id=' . $area_id;
		$result = mysqli_query($con, $sql);
		// 检查是否存在该区域
		if(empty($row = mysqli_fetch_assoc($result))) {
			$return['response']['error'] = 'area with id= ' . $area_id . 'not exists';
			echo json_encode($resturn, JSON_UNESCAPED_UNICODE);
			exit();
		}
		// 删除所在区的串
		$sql = 'DELETE FROM ' . $postTable . ' WHERE area_id=' . $area_id;
		if(!mysqli_query($con, $sql)) {
			die(mysqli_error($con));
		}
		// 删除所在区
		$sql = 'DELETE FROM ' . $areaTable . ' WHERE area_id=' . $area_id;
		if (!mysqli_query($con, $sql)) {
			die(mysqli_error($con));
		}
		
		$return['response']['status'] = 'OK';
		echo json_encode($return, JSON_UNESCAPED_UNICODE);
		exit();
	}
	
	/**
 	* 删除串（要求权限
	* `post_id` 要删除的串的id
 	*/
 	public static function deletePost($post) {
 		// 返回目标
 		$return['request'] = 'deletePost';
 		$return['response']['timestamp'] = self::timestamp();
 		
 		$post_id = is_numeric($post['post_id']) && $post['post_id'] > 0 ? $post['post_id'] : 0;
 		
 		global $con, $conf;
 		$postTable = $conf['databaseName'] . '.' . $conf['databaseTableName']['post'];
 		
 		// 查询指定的串
 		$sql = 'SELECT reply_post_id FROM ' . $postTable . ' WHERE post_id=' . $post_id;
 		$result = mysqli_query($con, $sql);
 		
 		// 为空返回
 		if (empty($row = mysqli_fetch_assoc($result))) {
 			$return['response']['error'] = 'to delete post not exists';
 			echo json_encode($return, JSON_UNESCAPED_UNICODE);
 			exit();
 		}
 		
 		// 为0表示这是主串，需要删除回复
 		if ($row['reply_post_id'] == 0) {
 			$sql = 'DELETE FROM ' . $postTable . ' WHERE reply_post_id=' . $post_id;
 			$result = mysqli_query($con, $sql);
 			//print_r($result);
 		}
 		
 		// 删除该记录
 		$sql = 'DELETE FROM ' . $postTable . ' WHERE post_id=' . $post_id;
 		$result = mysqli_query($con, $sql);
 		//print_r($result);
 		$return['response']['status'] = 'OK';
 		echo json_encode($return, JSON_UNESCAPED_UNICODE);
 		exit();
 	}
	
	/**
	* 获取用户信息（要求权限
	* `user_per_page` 每页的用户数（最大为50）
	* `pages` 请求的页数（省略则为1
	*/
	public static function getUserLists($post) {
		if (!is_numeric($post['user_per_page']) || $post['user_per_page'] > 50) {
			$user_per_page = 50;
		} else {
			$user_per_page = $post['user_per_page'];
		}
		
		$pages = isset($post['pages']) && is_numeric($post['pages']) && $post['pages'] > 0 ? $post['pages'] : 1;
		
		global $con, $conf;
		$userTable = $conf['databaseName'] . '.' . $conf['databaseTableName']['user'];
		
		$return['request'] = 'getUserLists';
		$return['response']['timestamp'] = self::timestamp();
		
		$return['response']['user_per_page'] = $user_per_page;
		$return['response']['pages'] = $pages;
		
		$pages -= 1;
		
		$sql = 'SELECT * FROM ' . $userTable . ' LIMIT ' . $user_per_page . ' OFFSET ' . ($pages * $user_per_page);

		$result = mysqli_query($con, $sql);

		$return['response']['users'] = Array();
		
		for ($row = mysqli_fetch_assoc($result); !empty($row); $row = mysqli_fetch_assoc($result)) {
			array_push($return['response']['users'], $row);
		}
		echo json_encode($return, JSON_UNESCAPED_UNICODE);
		exit();
		
	}
	
	/**
	* 获得某个数字对应的用户名(修复原来randomString值总量太小的问题)
	*/
	private static function randomString($num) {
		// 25的7次约为6e9和256的4次持平（IPv4地址总量个数）
		$str = 'N1M2B5a0c9T4m3D6s7x8CiSdI';
		$toNum = base_convert($num, 10, 25);
		$startIndex = [8, 6, 5, 1, 3, 0, 2];
		$userStr = '';
		// 根据数字生成7位字符串
		for ($i = 0; $i < 7; ++$i) {
			// print '-->';
			$digit = base_convert(substr($toNum, $i, 1), 25, 10);
			$index = ($digit + $startIndex[$i]) % 25;
			// print $index;
			$userStr .= $str[$index];
			// print '<--';
		}
		return $userStr;
	}
	
	/**
	* 生成一个当前时间戳
	*/
	private static function timestamp() {
		return date("Y-m-d H:i:s");
	}
	
	/**
	* 生成一个随机时间字符串
	*/
	private static function randomTimeMd5() {
		return md5(microtime());
	}
}
?>