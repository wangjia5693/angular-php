<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * JSON响应类
 *
 */

class Kernel_Response_Json extends Kernel_Response {

    public function __construct() {
    	$this->addHeaders('Content-Type', 'application/json;charset=utf-8');
    }
    
    protected function formatResult($result) {
        return json_encode($result);
    }
    
}
