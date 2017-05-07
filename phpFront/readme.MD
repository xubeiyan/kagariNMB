# kagari匿名版 前端部分

>传说中的前端部分，说是前端，但和只有html+css+javascript的前端还是不一样的，大概是为了方便移动端开发(iOS, Android, WP)而将服务端拆成了两个部分。
>使用php编写，暂时用不到数据库，所以是不用担心数据库的。
>图片缓存基本已完成，唯一不完美的是GIF图片只能生成一帧啊！又不想用imagemagick... 
>刚把换行这个问题解决了，并没有操作数据库替换上传的\n为<br />，而是通过设置CSS里的white-space为pre完成的~   


##### 目录结构

* `config` 配置文件 
* `css` CSS主题文件
* `html` html页面
* `js` 某些js库
* `kagari` 主要内容
* `thumbs` 图片缓存
* `.htaccess` URI Rewrite使用
* `favicon.png` 某图标
* `index.php` 入口文件
* `readme.MD` 就是本文件

##### 路由    

* `a-1-page-1`    
访问区，调用getAreaPosts这个API，page参数可省略默认为1
* `p-100000-page-1`
访问串。调用getPost这个API，page参数可省略默认为1
* `s-345-a-2`
在后面跟一个非0的数字表示回复这个串(思考了一下还是加上了分区)     
* `s-0-a-2`
在后面跟0表示发新串(思考了一下还是加上了分区)    
* `i-shinku.png`    
生成略缩图(gif用GD库只能生成1帧啊啊啊啊啊)    

##### 设计思路

>在获取板块列表和获取某板块的串的时候，Controller应该只控制获取的过程，而不是控制格式的处理，这个事情应交给Template来做(done)

>图片缓存工作方式：首先检查thumbs目录下有没有缓存图存在，没有则向后端请求文件，并写入前端thumbs目录，并显示出来。有则直接显示出来(done)

>GD好难用……考虑用ImageMagick好了……

##### 关于饼干(cookie)的发放

>A岛现在并不是如果没有饼干访问则重新生成一个饼干，而是通过在某些帖子里回复获得饼干，暂时没有想好是如何搞定这一步的……研究中……