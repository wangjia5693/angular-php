<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 文件MQ
 *
 * - 队列存放于本地文件 中，不支持分布式MQ
 *
 */

class Task_MQ_File extends Task_MQ_KeyValue {

    public function __construct(Kernel_Cache_File $fileCache = NULL) {
        if ($fileCache === NULL) {
            $config = Service()->config->get('app.Task.mq.file');
            if (!isset($config['path'])) {
                $config['path'] = APP_ROOT . '/Runtime';
            }
            if (!isset($config['prefix'])) {
                $config['prefix'] = 'phalapi_task';
            }

            $fileCache = new Kernel_Cache_File($config);
        }

        parent::__construct($fileCache);
    }
}
