<?php
// 安装完成后会删除install.php，就没有啦
if (!file_exists('install.php')) {
	echo 'Nimingban has already installed...please access index page...';
	exit();
}

require '../conf/conf.php';
require '../lib/database.php'; // 数据库连接，变量为$con
$test_area_id = 2;

if (isset($_GET['create_db'])) {
	$sql = 'CREATE DATABASE ' . $conf['databaseName'] . ' COLLATE utf8_general_ci';
	if(!mysqli_query($con, $sql)) {
		die(mysqli_connect_error());
	} else {
		echo "create database " . $conf['databaseName'] . " successfully!<br />";
	}
}


if (isset($_GET['create_tbl'])) {
	mysqli_select_db($con, $conf['databaseName']);
	// user表
	$usersql = 'CREATE TABLE ' . $conf['databaseTableName']['user'] . ' (
		user_id int NOT NULL AUTO_INCREMENT,
		ip_address varchar(140), 
		user_name varchar(20), 
		block_time int,
		last_post_id int,
		last_post_time datetime DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY(user_id)
	) COLLATE utf8_general_ci';	
	// area表
	$areasql = 'CREATE TABLE ' . $conf['databaseTableName']['area'] . ' (
		area_id int NOT NULL AUTO_INCREMENT,
		area_name varchar(20),
		area_sort int,
		posts_num int DEFAULT 0,
		block_status varchar(60),
		parent_area int,
		min_post int,
		PRIMARY KEY(area_id)
	) COLLATE utf8_general_ci';
	// post表
	$postsql = 'CREATE TABLE ' . $conf['databaseTableName']['post'] . ' (
		post_id int NOT NULL AUTO_INCREMENT,
		area_id int,
		user_id int,
		reply_post_id int,
		reply_posts_num int DEFAULT 0,
		author_name varchar(20),
		author_email varchar(20),
		post_title text(128),
		post_content text,
		post_images varchar(60),
		create_time datetime DEFAULT CURRENT_TIMESTAMP,
		update_time datetime DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY(post_id)
	) COLLATE utf8_general_ci';
	
	$adminsql = 'CREATE TABLE ' . $conf['datebaseTableName']['post'] . ' (
		admin_id int NOT NULL AUTO_INCREMENT,
		username varchar(20),
		password varchar(20),
		secretKey varchar(10),
		expireTime datetime DEFAULT CURRENT_TIMESTAMP
	) COLLATE utf8_general_ci';
	
	$postidSql = 'ALTER TABLE ' . $conf['databaseTableName']['post'] . 'AUTO_INCREMENT=10000';

	if(!mysqli_query($con, $usersql)) {
		die(mysqli_connect_error());
	} else {
		echo "create table " . $conf['databaseTableName']['user'] . " successfully!<br />";
	}

	if(!mysqli_query($con, $areasql)) {
		die(mysqli_connect_error());
	} else {
		echo "create table " . $conf['databaseTableName']['area'] . " successfully!<br />";
	}

	if(!mysqli_query($con, $postsql)) {
		die(mysqli_connect_error());
	} else {
		mysqli_query($con, $postidSql);
		echo "create table " . $conf['databaseTableName']['post'] . " successfully! and the start id of post has been changed to 10000!<br />";
	}
	
	if(!mysqli_query($con, $postsql)) {
		die(mysqli_connect_error());
	} else {
		mysqli_query($con, $postidSql);
		echo "create table " . $conf['databaseTableName']['admin'] . " successfully!<br />";
	}
}

if (isset($_GET['test_area'])) {
	$test_area_sql = 'INSERT INTO ' . $conf['databaseName'] . '.' . $conf['databaseTableName']['area'] . 
	' (area_name, area_sort, block_status, parent_area, min_post) VALUES (' .
	'"综合", 1, 0, 0, 0)';
	if(!mysqli_query($con, $test_area_sql)) {
		die(mysqli_connect_error());
	} else {
		echo "insert " . $test_area_sql ." successfully!<br />";
	}
	
	$test_area_sql2 = 'INSERT INTO ' . $conf['databaseName'] . '.' . $conf['databaseTableName']['area'] . 
	' (area_name, area_sort, block_status, parent_area, min_post) VALUES (' .
	'"综合版", 5, 0, 1, 0)';
	if(!mysqli_query($con, $test_area_sql2)) {
		die(mysqli_connect_error());
	} else {
		echo "insert " . $test_area_sql ." successfully!<br />";
	}
	
	$sql = 'SELECT area_id FROM ' . $conf['databaseName'] . '.' . $conf['databaseTableName']['area'] . ' WHERE area_name="综合版"';
	$row = mysqli_fetch_assoc(mysqli_query($con, $sql));
	$test_area_id = $row['area_id'];
}

if (isset($_GET['admin_user'])) {
	$admin_sql = 'INSERT INTO ' . $conf['databaseName'] . '.' . $conf['databaseTableName']['admin'] . 
	' (username, password) VALUES ("kagari", "kana")';
	if(!mysqli_query($con, $admin_sql)) {
		die(mysqli_connect_error());
	} else {
		echo "insert " . $admin_sql ." successfully!<br />";
	} 
}

// 暂时没啥用
if (isset($_GET['test_post'])) {
	$test_post_sql = 'INSERT INTO ' . $conf['databaseName'] . '.' . $conf['databaseTableName']['post'] . ' (area_id, user_id, reply_post_id, author_name, author_email, post_title, post_content, post_images, create_time, update_time) VALUES (' . $test_area_id . ')';
}

if (isset($_GET['drop_all'])) {
	$sql = 'DROP DATABASE ' . $conf['databaseName'];
	if (!mysqli_query($con, $sql)) {
		die('drop failed...');
	} else {
		echo 'drop database successfully!<br />';
	}
}
if ($_GET == Array()) {
	echo 'Full install ->';
	echo '<a href="index.php?create_db&create_tbl&test_area">Yes</a><br />';
	echo 'Drop all->';
	echo '<a href="index.php?drop_all">Yes</a>';
}
?>