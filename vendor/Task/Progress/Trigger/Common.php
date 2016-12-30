<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 通用 触发器接口
 * 
 */

class Task_Progress_Trigger_Common implements Task_Progress_Trigger {

    public function fire($params) {
        $paramsArr = explode('&', $params);

        $service = !empty($paramsArr[0]) ? trim($paramsArr[0]) : '';
        $mqClass = !empty($paramsArr[1]) ? trim($paramsArr[1]) : 'Task_MQ_Redis';
        $runnerClass = !empty($paramsArr[2]) ? trim($paramsArr[2]) : 'Task_Runner_Local';

        $mq = new $mqClass();
        $runner = new $runnerClass($mq);

        return $runner->go($service);
    }
}
