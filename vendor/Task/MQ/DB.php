<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 数据库MQ
 * 
 * - 队列存放于数据库表中，并支持分表
 * 
 */

class Task_MQ_DB implements Task_MQ {

    public function add($service, $params = array()) {
        $model = new Model_Task_TaskMq();
        return $model->add($service, $params);
    }

    public function pop($service, $num = 1) {
        $model = new Model_Task_TaskMq();
        return $model->pop($service, $num);
    }
}
