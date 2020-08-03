# 简介
由于最近在深入学习swoole，在学习之余觉得自己需要做一些东西来巩固知识。这个项目是基于这个认知上创建的，所以不要把它使用在你的生产环境上。  

这是一个swoole框架，现在已经完成的功能有

 - Bean自动装载  
 - RequestMapping路由映射  
 - HttpServer封装  
 - 参数命令平滑重启  
 - 进程热更新  
 - 携程EloquentORM  
 - PDO连接池  
 - Redis连接池  
 - DB注解（支持事务） 
 - Redis数据预热（支持携程插入数据）
 - Redis注解（支持所有数据类型以及lua脚本）
 - 分布式锁注解（基于redis）
 
# 声明  
这个框架只是一个学习项目，目的是巩固swoole知识点，学习如何搭建一个框架。      
实现的功能可能是不够完善的，只是对一些常用的功能进行一定的封装，我完成的目标是这个项目能保持swoole的特性运行起来。  
如果你需要进一步完善，请fork下去进行进一步的修改，或者去选用更加成熟的框架。  

# 系统要求

 - PHP >= 7.2
 - Swoole PHP 扩展 >= 4.4
 - Redis  PHP 拓展

# 安装

获取代码  
` git clone https://github.com/869413421/ym.git`

安装组件  
`composer install`

启动   
`php boot start`
