<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * JSON响应类
 *
 */

class Kernel_Response_JsonP extends Kernel_Response {

    protected $callback = '';

    /**
     * @param string $callback JS回调函数名
     */
    public function __construct($callback) {
        $this->callback = $this->clearXss($callback);

        $this->addHeaders('Content-Type', 'text/javascript; charset=utf-8');
    }

    /**
     * 对回调函数进行跨站清除处理
     *
     * - 可使用白名单或者黑名单方式处理，由接口开发再实现
     */
    protected function clearXss($callback) {
        return $callback;
    }

    protected function formatResult($result) {
        echo $this->callback . '(' . json_encode($result) . ')';
    }
}
