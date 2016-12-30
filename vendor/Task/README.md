##计划任务

用于后台计划任务的调度

    1、提供了Redis/文件/数据库三种MQ队列
    2、提供了本地和远程两种调度方式
    3、以接口的形式实现计划任务
    4、提供统一的crontab调度

在 ./Config/app.php 配置文件中追加以下配置：

    /**
     * 计划任务配置
     */
    'Task' => array(
        //MQ队列设置，可根据使用需要配置
        'mq' => array(
            'file' => array(
                'path' => APP_ROOT . '/Runtime',
                'prefix' => 'task',
            ),
            'redis' => array(
                'host' => '127.0.0.1',
                'port' => 6379,
                'prefix' => 'task',
                'auth' => '',
            ),
            'mc' => array(
                'host' => '127.0.0.1',
                'port' => 11211,
            ),
        ),

        //Runner设置，如果使用远程调度方式，请加此配置
        'runner' => array(
            'remote' => array(
                'host' => 'http://library.phalapi.net/demo/',
                'timeoutMS' => 3000,
            ),
        ),
    ),

(3)数据库表

当需要使用数据库MQ列队时，还需要将以下数据库的配置追加到 ./Config/dbs.php 中：

    'tables' => array(

    //请将以下配置拷贝到 ./Config/dbs.php 文件对应的位置中，未配置的表将使用默认路由

        //10张表，可根据需要，自行调整表前缀、主键名和路由
        'task_mq' => array(
            'prefix' => 'phalapi_',
            'key' => 'id',
            'map' => array(
                array('db' => 'db_demo'),
                array('start' => 0, 'end' => 9, 'db' => 'db_demo'),
            ),
        ),
    )

同时，需要将 ./Data/phalapi_task_mq.sql 文件的SQL建表语句导入到你的数据库。你也可以将利用脚本来生成。
3.6.3 入门使用
(1)加入MQ队列

首先，我们需要在入口文件进行对Task的初始化：

$mq = new Task_MQ_Redis();  //可以选择你需要的MQ
Service()->taskLite = new Task_Lite($mq);

然后，这样即可添加一个新的计划任务到MQ：

Service()->taskLite->add('Task.DoSth', array('id' => 1));

(2)计划任务的启动

在启动计划任务前，我们需要编写简单的脚本，一如这样：

#!/usr/bin/env php
<?php
require_once '/path/to/Public/init.php';

Service()->loader->addDirs('Demo');

if ($argc < 2) {
    echo "Usage: $argv[0] <service> \n\n";
    exit(1);
}

$service = trim($argv[1]);

$mq = new Task_MQ_Redis();
$runner = new Task_Runner_Local($mq);
$rs = $runnter->go($service);

echo "\nDone:\n", json_encode($rs), "\n\n";

然后使用nohup或者crontab启动即可。
(3)统一的crontab计划任务

上面第二点的启动是通用、自定义、也是自由的启动方式。

这里再提供一种具体的、统一的启动方式，即使用crontab的方式。

首先，创建以下表(或见./Library/Task/Data/phalapi_task_progress.sql文件)：

CREATE TABLE `phalapi_task_progress` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `title` varchar(200) DEFAULT '' COMMENT '任务标题',
      `trigger_class` varchar(50) DEFAULT '' COMMENT '触发器类名',
      `fire_params` varchar(255) DEFAULT '' COMMENT '需要传递的参数，格式自定',
      `interval_time` int(11) DEFAULT '0' COMMENT '执行间隔，单位：秒',
      `enable` tinyint(1) DEFAULT '1' COMMENT '是否启动，1启动，0禁止',
      `result` varchar(255) DEFAULT '' COMMENT '运行的结果，以json格式保存',
      `state` tinyint(1) DEFAULT '0' COMMENT '进程状态，0空闲，1运行中，-1异常退出',
      `last_fire_time` int(11) DEFAULT '0' COMMENT '上一次运行时间',
      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

接着，添加crontab计划任务：

$ crontab -e

*/1 * * * * /usr/bin/php /path/to/PhalApi/Library/Task/crontab.php >> /tmp/phalapi_task_crontab.log 2>&1

然后，实现你的计划任务服务接口：

<?php
class Api_Task_Demo extends PhalApi_Api {

      public function doSth() {
            // ...
      }
}

最后，配置计划任务：

INSERT INTO `phalapi_task_progress`(title, trigger_class, fire_params, interval_time)  VALUES('你的任务名字', 'Task_Progress_Trigger_Common', 'Task_Demo.DoSth&Task_MQ_File&Task_Runner_Local', '300');

注意，在配置时，需要指明MQ和Runner的类型。
3.6.4 更多说明
(1)依赖的资源服务
名称 	类 	没有时是否自动创建
curl 	Kernel_CUrl 	是
request 	Kernel_Request 	强制每次初始化，用于本地调度时
response 	Kernel_Response_Json 	强制每次初始化，用于本地调度时
(2)MQ
Redis MQ队列

Redis MQ队列需要的配置为：

    'Task' => array(
        //MQ队列设置，可根据使用需要配置
        'mq' => array(
            'redis' => array(
                'host' => '127.0.0.1',
                'port' => 6379,
                'prefix' => 'phalapi_task',
                'auth' => '',
            ),
        ),
    ),

其中：
选项 	是否必须 	默认值 	说明
host 	否 	127.0.0.1 	redis的HOST
port 	否 	6379 	redis的端口
prefix 	否 	phalapi_task 	key的前缀
auth 	否 		redis的验证，不为空时执行验证

可以这样创建Redis MQ队列：

//方法一：使用app.Task.mq.redis配置
$mq = new Task_MQ_Redis();

//方法二：外部依赖注入
$redisCache = new Kernel_Cache_Redis(array('host' => '127.0.0.1'));
$mq = new Task_MQ_Redis($redisCache);

文件MQ队列（通常不能共享，队列大小不限制，有效期一年）

文件MQ需要的配置为：

    'Task' => array(
        //MQ队列设置，可根据使用需要配置
        'mq' => array(
            'file' => array(
                'path' => API_ROOT . '/Runtime',
                'prefix' => 'phalapi_task',
            ),
        ),
    ),

其中：
选项 	是否必须 	默认值 	说明
path 	否 	API_ROOT/Runtime 	缓存的文件目录
prefix 	否 	phalapi_task 	key的前缀

可以这样创建文件MQ队列：

//方法一：使用app.Task.mq.file配置
$mq = new Task_MQ_File();

//方法二：外部依赖注入
$fileCache = new Kernel_Cache_File(array('path' => '/tmp/cache'));
$mq = new Task_MQ_File($fileCache);

Memcached/Memcache MQ队列（通常队列大小不能超过1M，有效期29天）

MC MQ需要的配置为：

    'Task' => array(
        //MQ队列设置，可根据使用需要配置
        'mq' => array(
            'mc' => array(
                'host' => '127.0.0.1',
                'port' => 11211,
            ),
        ),
    ),

其中：
选项 	是否必须 	默认值 	说明
host 	否 	127.0.0.1 	MC的host
port 	否 	11211 	MC端口

可以这样创建文件MQ队列：

//方法一：使用app.Task.mq.mc配置
$mq = new Task_MQ_Memcached();

//方法二：外部依赖注入
$mc = new Kernel_Cache_Memcached(array('host' => '127.0.0.1', 'port' => 11211));
$mq = new Task_MQ_File($mc);

数据库MQ队列

数据库MQ队列需要的配置为：

    'tables' => array(

        //请将以下配置拷贝到 ./Config/dbs.php 文件对应的位置中，未配置的表将使用默认路由

        //10张表，可根据需要，自行调整表前缀、主键名和路由
        'task_mq' => array(
            'prefix' => 'phalapi_',
            'key' => 'id',
            'map' => array(
                array('db' => 'db_demo'),
                array('start' => 0, 'end' => 9, 'db' => 'db_demo'),
            ),
        ),
    )

可以这样创建数据库MQ队列：

$mq = new Task_MQ_DB();

数组MQ队列

数组MQ队列是将MQ存放在PHP的数组里面，用于单元测试或者是一次性、临时性的计划任务调度。

可以这样创建数据库MQ队列：

$mq = new Task_MQ_Array();

(3)本地和远程调度
本地调度的创建

$runner = new Task_Runner_Local($mq, 10);  //10表示每批次弹出10个进行处理

需要注意的是，每次执行一个计划任务，都会重新初始化必要的DI资源服务。

且此调度方式不能用于接口请求时的同步调用。
远程调度的创建

首先需要以下配置：

    /**
     * 计划任务配置
     */
    'Task' => array(
        //Runner设置，如果使用远程调度方式，请加此配置
        'runner' => array(
            'remote' => array(
                'host' => 'http://library.phalapi.net/demo/',
                'timeoutMS' => 3000,
            ),
        ),
    ),

其中：
选项 	是否必须 	默认值 	说明
host 	是 		接口域名链接
timeoutMS 	否 	3000 	接口超时时间，单位毫秒

可以这样创建：

//使用默认的连接器 - HTTP + POST的方式
$runner = new Task_Runner_Remote($mq, 10); //10表示每批次弹出10个进行处理

//或者，指定连接器
$connector = new Task_Runner_Remote_Connector_Impl();
$runner = new Task_Runner_Remote($mq, 10, $connector); //10表示每批次弹出10个进行处理
