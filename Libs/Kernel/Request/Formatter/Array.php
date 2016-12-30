<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 格式化数组
 *
 */


class Kernel_Request_Formatter_Array extends Kernel_Request_Formatter_Base implements Kernel_Request_Formatter {

    /**
     * 对数组格式化/数组转换
     * @param string $value 变量值
     * @param array $rule array('name' => '', 'type' => 'array', 'default' => '', 'format' => 'json/explode', 'separator' => '', 'min' => '', 'max' => '')
     * @return array
     */
    public function parse($value, $rule) {
        $rs = $value;

        if (!is_array($rs)) {
            $ruleFormat = !empty($rule['format']) ? strtolower($rule['format']) : '';
            if ($ruleFormat == 'explode') {
                $rs = explode(isset($rule['separator']) ? $rule['separator'] : ',', $rs);
            } else if ($ruleFormat == 'json') {
                $rs = json_decode($rs, TRUE);
            } else {
                $rs = array($rs);
            }
        }

        $this->filterByRange(count($rs), $rule);

        return $rs;
    }
}
