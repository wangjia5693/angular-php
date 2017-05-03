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
        'dbname'      => '',               //数据库名字
        'dbuser'      => '',                  //数据库用户名
        'dbpass'  => '',	                    //数据库密码
        'dbport'      => '3306',                  //数据库端口
        'dbchar'   => 'utf8',                  //数据库字符集
    ),
    'db' => array(                         //服务器标记
        'dbhost'      => '',             //数据库域名
        'dbname'      => '',               //数据库名字
        'dbuser'      => '',                  //数据库用户名
        'dbpass'  => '',	                    //数据库密码
        'dbport'      => '3306',                  //数据库端口
        'dbchar'   => 'utf8',                  //数据库字符集
    ),

    'mongo_debug' => array(
        'user' => '',
        'host' => '',
       'pass' => "",
        'port' => '27',
        'dbname' => 'pmp'
    ),

    'mongo' => array(
        'user' => '',
        'host' => '',
        'pass' => "",
        'port' => '27017',
        'dbname' => 'pmp'
    ),
    'orm' => array(
        'driver'    => 'mysql',
        'host'      => '127.0.0.1',
        'database'  => '',
        'username'  => '',
        'password'  => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix'    => '',
    ),

);
