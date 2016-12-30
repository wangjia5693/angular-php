<?php

return array(

    //请将以下配置拷贝到 ./Config/app.php 文件中

    /**
     * 计划任务配置
     */
    'Task' => array(
        //队列设置
        'mq' => array(
            'redis' => array(
                'host' => '127.0.0.1',
            	'port' => 6379,
                'prefix' => 'task',
                'auth' => '',
            ),
        ),

        //Runner设置，如果使用远程调度方式，请加此配置
        'runner' => array(
            'remote' => array(
                'host' => 'localhost/demo/',
                'timeoutMS' => 3000,
            ),
        ),
    ),
);
