<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 格式化枚举类型
 *

 */

class Kernel_Request_Formatter_Enum extends Kernel_Request_Formatter_Base implements Kernel_Request_Formatter {

    /**
     * 检测枚举类型
     * @param string $value 变量值
     * @param array $rule array('name' => '', 'type' => 'enum', 'default' => '', 'range' => array(...))
     * @return 当不符合时返回$rule
     */
    public function parse($value, $rule) {
        $this->formatEnumRule($rule);

        $this->formatEnumValue($value, $rule);

        return $value;
    }

    /**
     * 检测枚举规则的合法性
     * @param array $rule array('name' => '', 'type' => 'enum', 'default' => '', 'range' => array(...))
     * @throws Kernel_Exception_InternalServerError
     */
    protected function formatEnumRule($rule) {
        if (!isset($rule['range'])) {
            throw new Kernel_Exception_InternalServerError(
                T("miss {name}'s enum range", array('name' => $rule['name'])));
        }

        if (empty($rule['range']) || !is_array($rule['range'])) {
            throw new Kernel_Exception_InternalServerError(
                T("{name}'s enum range can not be empty", array('name' => $rule['name'])));
        }
    }
}
