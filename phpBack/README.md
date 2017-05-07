### 数据库设计
#### MySQL数据库：(库名kagari_Nimingban)
##### 数据表名：(表名前缀kagari_)
###### user:(用户信息)
* user_id(primary key), 
* ip_address(IP地址), 
* user_name(随机生成字符串还是按规律增长的字符串), 
* block_time(被阻止时间，秒数？分钟数？亦或是被永久),
* last_post_id(最后发串id？这个功能疑似没啥用啊)
* last_post_time(最后发串时间，配合最小区域最小发串时间使用)

###### area:(分区)
* area_id(primary key), 
* area_name(区名), 
* area_sort(排序，在获取板块列表的时候的顺序),
* block_status(被阻止的状态，禁回复，禁发帖，转向第一个子分区), 
* parent_area(此分区的父分区), 
* min_post(最小发串间隔),
* posts_num(该区下主串数)

###### post:(串)
* post_id(primary key), 
* area_id(所属区id), 
* user_id(发布用户id), 
* reply_post_id(跟串id，就是是在哪个串下面的ID，没有则是主串), 
* author_name(作者名), 
* author_email(作者邮箱名), 
* post_title(串标题), 
* post_content(串内容), 
* post_images(图片，应该是可以支持多张图片的),
* create_time(此串发布时间),
* update_time(此串更新时间，普通回复更新，SAGE则不更新)
* reply_posts_num(主串下此值为回复条数，回复串为0)

### API设计列表
>会同时接受JSON和multipart/form-data(因为会上传图片)

#### 用户级别:
* 获取饼干    
`/api/getCookie`    
提交内容：
```javascript
{
	"ip": "::1"
}
```     
返回内容：(举例)    
(正确ip地址)     
```javascript
{
	"request": "getCookie",
	"response": {
		"timestamp": "2016-06-06 10:15:34",
		"ip": "::1",
		"username": "1abCDEF"
	}
}
```
(非法ip地址)
```javascript
{
	"request": "getCookie",
	"response": {
		"timestamp": "2016-06-06 10:15:34",
		"error": "这个IP地址不符合规定呢"
	}
}
```
* 获取板块列表  
`/api/getAreaLists`   
提交内容：(暂无)  
返回内容：(举例)    
```javascript
{
	"request": "getAreaList", 
	"response": {
		"timestamp": "2016-05-24 13:53:05",
		"areas": [
		{
			"area_id": 1,
			"area_name": "综合",
			"parent_area": ""
		},
		{
			"area_id": 2,
			"area_name": "综合版1",
			"parent_area": 1
		}]
	}
}
```

* 获取板块串   
`/api/getAreaPosts`  
提交内容：    
`area_id`    
`area_page`    
返回内容：(举例)(返回结果)  
```javascript
{
	"request": "getAreaPosts",
	"response": {
		"timestamp": "2016-05-27 16:26:24",
		"area_id": 2,
		"area_name": "综合版1",
		"area_page": 1,
		"posts_per_page": 50,
		"posts_num": 2,
		"last_reply_posts": 10002,
		"posts": [{
			"post_id": 10000,
			"post_title": "无标题",
			"post_content": "aaabbbccc",
			"post_images": "1.png",
			"user_id": 1,
			"user_name": "1wuQKIZ",
			"author_name": "无名氏",
			"author_email": "",
			"create_time": "2016-05-27 16:37:45",
			"update_time": "2016-05-27 16:38:56",
			"reply_num": 2,
			"reply_recent_posts": [{
				"post_id": 10001,
				"user_id": 1,
				"user_name": "1wuQKIZ",
				"author_name": "无名氏",
				"author_email": "",
				"post_title": "无标题",
				"post_content": "dddeeefff",
				"post_images": "2.png,3.jpg",
				"create_time": "2016-05-27 16:38:45",
				"update_time": "2016-05-27 16:39:56",
			},
			{
				"post_id": 10002,
				"user_id": 2,
				"user_name": "1mjIUYJ",
				"author_name": "无名氏",
				"author_email": "",
				"post_title": "无标题",
				"post_content": "ggghhhiii",
				"post_images": "",
				"create_time": "2016-05-27 16:40:45",
				"update_time": "2016-05-27 16:41:56",
			}]
		},
		{
			"post_id": 10003,
			"post_title": "无标题",
			"post_content": "aaabbbccc",
			"post_images": "1.png",
			"user_id": 1,
			"user_name": "1mjIUYJ",
			"author_name": "无名氏",
			"author_email": "",
			"create_time": "2016-05-27 16:37:45",
			"update_time": "2016-05-27 16:38:56",
			"reply_num": 0,
			"reply_recent_posts": []
		}]
	}
}
```  
(板块下没有串)   
```javascript
{
	"request": "getAreaPosts",
	"response": {
		"timestamp": "2017-02-28",
		"area_id": 1,
		"area_name": "综合",
		"area_page": 2,
		"posts_per_page": 10,
		"posts_num": 0,
		"last_reply_posts": 8,
		"posts": [],
		"info": "area_id=1的区里没有串"
	}
}
```    
(返回未找到板块)
```javascript
{
	"request": "getAreaPosts",
	"response": {
		"timestamp": "2017-02-28",
		"error": "找不到area_id=3的区"
	}
}
```
* 获取串内容   
`/api/getPost`   
提交内容：  
`post_id`   
`post_page`    
返回内容：(举例)(返回结果)     
```javascript
{
	"request": "getPost",
	"response": {
		"timestamp": "2016-05-27 17:06:43",
		"area_id": 2,
		"area_name": "综合版1",
		"post_id": 10000,
		"post_page": 1,
		"posts_per_page": 50,
		"post_title": "无标题",
		"post_content": "aaabbbccc",
		"post_images": "1.png",
		"user_id": 1,
		"user_name": "1wuQKIZ",
		"author_name": "无名氏",
		"author_email": "",
		"create_time": "2016-05-27 16:37:45",
		"update_time": "2016-05-27 16:38:56",
		"reply_posts_num": 2,
		"reply_recent_posts": [{
			"post_id": 10001,
			"user_id": 1,
			"user_name": "1wuQKIZ",
			"author_name": "无名氏",
			"author_email": "",
			"post_title": "无标题",
			"post_content": "dddeeefff",
			"post_images": "2.png,3.jpg",
			"create_time": "2016-05-27 16:38:45",
			"update_time": "2016-05-27 16:39:56",
		},
		{
			"post_id": 10002,
			"user_id": 2,
			"user_name": "1mjIUYJ",
			"author_name": "无名氏",
			"author_email": "",
			"post_title": "无标题",
			"post_content": "ggghhhiii",
			"post_images": "",
			"create_time": "2016-05-27 16:40:45",
			"update_time": "2016-05-27 16:41:56",
		}]
	}
}
```
(返回未找到帖子)
```javascript
{
	"request": "getPost",
	"response": {
		"timestamp": "2016-06-28 11:05:12",
		"error": "未找到相应帖子"
	}
}
```

* 发表新串   
`/api/sendPost`     
提交内容：   
`user_name`(用户名，必需)   
`area_id`(分区id，必需)     
`user_ip`(用户ip，必需)   
`reply_post_id`(回复还是新串，新串为0，为空则为0)        
`author_name`   
`author_email`   
`post_title`   
`post_content`(串内容，必需)    
`post_image`(按data:image/gif;base64,AABBCC==这样的形式使用base64编码上传)    
返回内容：(正常回帖)    
```javascript
{
	"request": "sendPost",
	"response": {
		"timestamp": "2016-06-06 10:50:45",
		"status": "OK"
	}
}
```
(不存在的帖子)     
```javascript
{
	"request": "sendPost",
	"response": {
		"timestamp": "2016-06-29 13:17:09",
		"error": "回复串不存在"
	}
}
```
(所在的帖子为回复帖子)     
```javascript
{
	"request": "sendPost",
	"response": {
		"timestamp": "2016-06-29 13:21:35",
		"error": "回复的串不是主串"
	}
}
```
(发串过快)
```javascript
{
	"request": "sendPost",
	"response": {
		"timestamp": "2017-04-10 23:35:26",
		"error": "发串间隔太短",
		"last_post_time": "2017-04-10 23:39:10"
	}
}
```
* 增加新板块    
`api/addArea`
提交内容：      	
`area_name` 板块名     
`parent_area` 为某板块的子版块，0为无    
返回内容：(增加成功)    
```javascript
{
	"request": "addArea",
	"response": {
		"timestamp": "2016-08-21 20:24:33",
		"status": "OK"
	}
}
```
(板块名为空)
```javascript
{
	"request": "addArea",
	"response": {
		"timestamp": "2016-08-21 20:24:33",
		"error": "板块名不能为空"
	}
}
```
(板块名和现有的一样)
```javascript
{
	"request": "addArea",
	"response": {
		"timestamp": "2016-08-21 20:24:33",
		"error": "板块名abc已存在于母板1之下"
	}
}
```
	
* 删除某个区    
`api/deleteArea`
提交内容：    
`area_id`(要删除的区的id)    
返回内容：(删除成功)    
```javascript
{
	"request": "deleteArea",
	"response": {
		"timestamp": "2016-07-18 18:12:29",
		"status": "OK"
	}
	
}
```
(不存在的区)    
```javascript
{
	"request": "deleteArea",
	"response": {
		"timestamp": "2016-07-18 18:12:25",
		"error": "删除的区不存在"
	}
}
```

* 删除某个串    
`/api/deletePost`    
提交内容：    
`post_id`(删除的串的id)     
返回内容：(删除成功)     
```javascript
{
	"request": "deletePost",
	"response": {
		"timestamp": "2016-07-07 11:53:19",
		"status": "OK"
	}
	
}
```
(不存在的帖子)    
```javascript
{
	"request": "deletePost",
	"response": {
		"timestamp": "2016-07-07 11:53:25",
		"error": "删除的串不存在"
	}
}
```
	
* 获取用户列表    
`/api/getUserLists`
提交内容：   
`user_per_page`(每页多少信息，超过50自动设为50)      
`pages`(可选，页数)     
返回内容:     
```javascript
{
	"request": "getUserLists",
	"response": {
		"timestamp": "2017-03-01",
		"user_per_page": 10,
		"pages": 1,
		"users": [
			{
				"user_id": "1",
				"ip_address": "::1",
				"user_name": "1cfBDZD",
				"block_time": "0",
				"last_post_id": "0",
				"last_post_time": "2017-02-20 18:03:47"
			},
			{
				"user_id": "2",
				"ip_address": "127.0.0.1",
				"user_name": "1cfZHQG",
				"block_time": "0",
				"last_post_id": "0",
				"last_post_time": "2017-01-12 22:12:51"
			}
		]
	}
}
```
