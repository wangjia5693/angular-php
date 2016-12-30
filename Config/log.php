<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/12/27.
 * 利用monolog对日志进行处理；不仅仅错误异常，也可用户操作行为；处理方式多级：记录，邮件，debug
 */
return array(
    'version' => 1,

    'formatters' => array(//数据格式
        'spaced' => array(
            'format' => "%datetime% %channel%.%level_name%  %message%\n",
            'include_stacktraces' => true
        ),
        'dashed' => array(
            'format' => "%datetime%-%channel%.%level_name% - %message%\n"
        ),
    ),
    'handlers' => array(//处理方式
        'console' => array(
            'class' => 'Monolog\Handler\StreamHandler',
            'level' => 'DEBUG',
            'formatter' => 'spaced',
            'stream' => 'php://stdout'
        ),

        'info_file_handler' => array(
            'class' => 'Monolog\Handler\StreamHandler',
            'level' => 'INFO',
            'formatter' => 'dashed',
            'stream' => './demo_info.log'
        ),

        'error_file_handler' => array(
            'class' => 'Monolog\Handler\StreamHandler',
            'level' => 'ERROR',
            'stream' => './demo_error.log',
            'formatter' => 'spaced'
        )
    ),
    'processors' => array(//数据处理器
        'tag_processor' => array(
            'class' => 'Monolog\Processor\TagProcessor'
        )
    ),
    'loggers' => array(//必须
        'my_logger' => array(
            'handlers' => array('console', 'info_file_handler')
        )
    )
);