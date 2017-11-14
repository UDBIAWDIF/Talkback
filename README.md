使用方法
该系统运行在Linux下。不支持windows.使用workerman作为开发框架。在此感谢workerman提供如此优秀的框架

依赖扩展，请自行安装。
pcntl、posix、libevent库

在Application目录下的config配置好数据库及服务器IP地址。

启动
以debug（调试）方式启动

php start.php start

以daemon（守护进程）方式启动

php start.php start -d

停止
php start.php stop

重启
php start.php restart

接口文档参考协议部份