<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 本地调度器 Task_Runner_Local
 * 
 * - 本地内部调度
 * - 不能在Api请求时进行此调度
 * 
 */

class Task_Runner_Local extends Task_Runner {

    protected function youGo($service, $params) {
        $params['service'] = $service;

        Service()->request = new Kernel_Request($params);
        Service()->response = new Kernel_Response_Json();

        $phalapi = new App();
        $rs = $phalapi->response();
        $apiRs = $rs->getResult();

        if ($apiRs['ret'] != 200) {
            Service()->logger->debug('task local go fail',
                array('servcie' => $service, 'params' => $params, 'rs' => $apiRs));

            return FALSE;
        }

        return TRUE;
    }

}

