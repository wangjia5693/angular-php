<?php
/**
 *  Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 *  格式化浮点类型
 *
 */

class Kernel_Request_Formatter_Float extends Kernel_Request_Formatter_Base implements Kernel_Request_Formatter {

    /**
     * 对浮点型进行格式化
     *
     * @param mixed $value 变量值
     * @param array $rule array('min' => '最小值', 'max' => '最大值')
     * @return float/string 格式化后的变量
     *
     */
    public function parse($value, $rule) {
        return floatval($this->filterByRange(floatval($value), $rule));
    }
}
