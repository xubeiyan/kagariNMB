#kagari匿名版 前端部分

>传说中的前端部分，说是前端，但和只有html+css+javascript的前端还是不一样的，大概是为了方便移动端开发(iOS, Android, WP)而将服务端拆成了两个部分。
>使用php编写，暂时用不到数据库，所以是不用担心数据库的。


#####目录结构

* `config` 配置文件 
* `css` CSS主题文件
* `html` html页面
* `kagari` 主要内容
* `.htaccess` URI Rewrite使用
* `index.php` 入口文件

#####路由    

* `a/1/page/1`    
访问区，调用getAreaPosts这个API，page参数可省略默认为1
* `p/100000/page/1`
访问串。调用getPost这个API，page参数可省略默认为1

#####设计思路

>在获取板块列表和获取某板块的串的时候，Controller应该只控制获取的过程，而不是控制格式的处理，这个事情应交给Template来做