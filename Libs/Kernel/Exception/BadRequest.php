<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * App_Exception_BadRequest 客户端非法请求
 *
 * 客户端非法请求

 */

class Kernel_Exception_BadRequest extends Kernel_Exception{

    public function __construct($message, $code = 0) {
        parent::__construct(
            T('Bad Request: {message}', array('message' => $message)), 400 + $code
        );
    }
}
