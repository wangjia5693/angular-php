<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 格式化接口
 *
 */

interface Kernel_Request_Formatter {

    public function parse($value, $rule);
}
