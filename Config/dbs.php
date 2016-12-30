<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/12/01.
 * 分库分表的自定义数据库路由配置
 *
 */

return array(
    /**
     * DB数据库服务器
     */

    'db_debug' => array(                         //服务器标记
        'dbhost'      => '127.0.0.1',             //数据库域名
        'dbname'      => 'pmp',               //数据库名字
        'dbuser'      => 'root',                  //数据库用户名
        'dbpass'  => 'wangjia5693',	                    //数据库密码
        'dbport'      => '3306',                  //数据库端口
        'dbchar'   => 'utf8',                  //数据库字符集
    ),
    'db' => array(                         //服务器标记
        'dbhost'      => '192.168.121.227',             //数据库域名
        'dbname'      => 'simba_pmp',               //数据库名字
        'dbuser'      => 'simba_pmp',                  //数据库用户名
        'dbpass'  => 'pmp1234',	                    //数据库密码
        'dbport'      => '3306',                  //数据库端口
        'dbchar'   => 'utf8',                  //数据库字符集
    ),

    'mongo_debug' => array(
        'user' => 'pmp',
        'host' => '192.168.102.78',
       'pass' => "pmp123456",
        'port' => '27',
        'dbname' => 'pmp'
    ),

    'mongo' => array(
        'user' => 'pmp',
        'host' => '127.0.0.1',
        'pass' => "pmp123456",
        'port' => '27017',
        'dbname' => 'pmp'
    ),
    'orm' => array(
        'driver'    => 'mysql',
        'host'      => '127.0.0.1',
        'database'  => 'pmp',
        'username'  => 'root',
        'password'  => 'wangjia5693',
        'charset'   => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix'    => '',
    ),

);
