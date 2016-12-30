<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/12/03.
 * 数据操作入口 引入laravel eloquent组件
 * 要求： PHP > = 5.64;  开启：pdo,mysql-pdo
 *
 */

use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as DB;


$capsule = new DB;

$capsule->addConnection(Service()->config->get('dbs.orm'));

$capsule->setEventDispatcher(new Dispatcher(new Container));

// 模型缓存
//$capsule->setCacheManager(...);


// 注册全局静态类
$capsule->setAsGlobal();


// 启动 Eloquent ORM...
$capsule->bootEloquent();


/**
 * 是否开启sql测试日志
 */
//$capsule::listen(
//    function ($query, $bindings = null, $time = null, $connectionName = null) {
//
//        if ( $query instanceof \Illuminate\Database\Events\QueryExecuted ) {
//            $bindings = $query->bindings;
//            $time = $query->time;
//            $connection = $query->connection;
//
//            $query = $query->sql;
//        }
//
//       loger($query,'test.txt');
//    }
//);