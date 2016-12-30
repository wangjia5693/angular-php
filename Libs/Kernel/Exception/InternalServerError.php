<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 服务器运行异常错误
 *
 */

class Kernel_Exception_InternalServerError extends Kernel_Exception {

    public function __construct($message, $code = 0) {
        parent::__construct(
            T('Interal Server Error: {message}', array('message' => $message)), 500 + $code
        );
    }
}
