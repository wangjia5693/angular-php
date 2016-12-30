<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 数组MQ
 * 
 * - 用于单元测试，或者临时一次性执行
 * - 队列存放于内存的数组中
 * 
 */

class Task_MQ_Array implements Task_MQ {

    protected $list = array();

    public function add($service, $params = array()) {
        if (!isset($this->list[$service])) {
            $this->list[$service] = array();
        }

        $this->list[$service][] = $params;

        return TRUE;
    }

    public function pop($service, $num = 1) {
        if (empty($this->list[$service])) {
            return array();
        }

        $rs = array_splice($this->list[$service], 0, $num);

        return $rs;
    }
}
