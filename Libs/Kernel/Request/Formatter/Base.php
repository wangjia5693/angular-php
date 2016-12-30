<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 公共基类
 *
 * - 提供基本的公共功能，便于子类重用
 *
 */


class Kernel_Request_Formatter_Base {

    /**
     * 根据范围进行控制
     */
    protected function filterByRange($value, $rule) {
        $this->filterRangeMinLessThanOrEqualsMax($rule);

        $this->filterRangeCheckMin($value, $rule);

        $this->filterRangeCheckMax($value, $rule);

        return $value;
    }

    protected function filterRangeMinLessThanOrEqualsMax($rule) {
        if (isset($rule['min']) && isset($rule['max']) && $rule['min'] > $rule['max']) {
            throw new Kernel_Exception_InternalServerError(
                T('min should <= max, but now {name} min = {min} and max = {max}', 
                    array('name' => $rule['name'], 'min' => $rule['min'], 'max' => $rule['max']))
            );
        }
    }

    protected function filterRangeCheckMin($value, $rule) {
        if (isset($rule['min']) && $value < $rule['min']) {
            throw new Kernel_Exception_BadRequest(
                T('{name} should >= {min}, but now {name} = {value}', 
                    array('name' => $rule['name'], 'min' => $rule['min'], 'value' => $value))
            );
        }
    }

    protected function filterRangeCheckMax($value, $rule) {
        if (isset($rule['max']) && $value > $rule['max']) {
            throw new Kernel_Exception_BadRequest(
                T('{name} should <= {max}, but now {name} = {value}', 
                array('name' => $rule['name'], 'max' => $rule['max'], 'value' => $value))
            );
        }
    }

    /**
     * 格式化枚举类型
     * @param string $value 变量值
     * @param array $rule array('name' => '', 'type' => 'enum', 'default' => '', 'range' => array(...))
     * @throws Kernel_Exception_BadRequest
     */
    protected function formatEnumValue($value, $rule) {
        if (!in_array($value, $rule['range'])) {
            throw new Kernel_Exception_BadRequest(
                T('{name} should be in {range}, but now {name} = {value}', 
                    array('name' => $rule['name'], 'range' => implode('/', $rule['range']), 'value' => $value))
            );
        }
    }
}
