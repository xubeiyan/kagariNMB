kagari Nimingba(匿名版)
======================

>纯属个人练手作品   

考虑后端用php+mysql，以及python+elasticsearch各做一个版本

前后端数据交互使用json（方便开发？）

>匿名版说是匿名版但其实还是将你的信息绑定到了IP上，于是就有了某个字符串代表用户这样的规定（貌似A岛是这样的）。然后有个认证User-Agent的地方，于是乎那些不能修改User-Agent的浏览器也被踢掉了。回某个串会将其在某个区顶起来，但是也可以加上SAGE让其保持下沉状态

>目前php前端和后端基本上完成了

###使用方式

1. 首先安装好php(>=5.4)和MySQL
2. 把phpFront(前端)和phpBack(后端)两个文件夹，放到合适的目录下
3. 修改phpBack/conf/conf.php中数据库连接地址，访问phpBack/install/index.php，生成初始数据库(Full install)
4. 修改phpFront/config/config.php中后端和图片地址至合适的值
5. 试着访问phpFront/index.php