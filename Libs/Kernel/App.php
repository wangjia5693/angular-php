<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 接口服务基类
 *
 * - 实现身份验证、按参数规则解析生成接口参数等操作
 * - 提供给开发人员自宝义的接口服务具体类继承
 *
 * <br>通常地，可以这样继承：<br>
 *
```
 *  class Api_Demo extends App_Api {
 *      
 *      public function getRules() {
 *          return array(
 *              // ...
 *          );
 *      }
 *
 *      public function doSth() {
 *          $rs = array();
 *
 *          // ...
 *
 *          return $rs;
 *      }
 *  }
 */

class Kernel_App {

    /**
     * 设置规则解析后的接口参数
     * @param string $name 接口参数名字
     * @param mixed $value 接口参数解析后的值
     */
    public function __set($name, $value) {
        $this->$name = $value;
    }

    /**
     * 获取规则解析后的接口参数
     * @param string $name 接口参数名字
     * @throws Kernel_Exception_InternalServerError 获取未设置的接口参数时，返回500
     * @return mixed
     */
    public function __get($name) {
        if(!isset($this->$name) || empty($name)) {
            throw new Kernel_Exception_InternalServerError(
                T('App_Api::${name} undefined', array('name' => $name))
            );
        }

        return $this->$name;
    }

    /**
     * 初始化
     *
     * 主要完成的初始化工作有：
     * - 1、[必须]按参数规则解析生成接口参数
     * - 2、[可选]过滤器调用，如：签名验证
     * - 3、[可选]用户身份验证
     *
     */
    public function init() {
        $this->createMemberValue();

        $this->filterCheck();

        $this->userCheck();
    }

    /**
     * 按参数规则解析生成接口参数
     *
     * 根据配置的参数规则，解析过滤，并将接口参数存放于类成员变量
     * 
     * @uses Kernel_App::getApiRules()
     */
    protected function createMemberValue() {
        foreach ($this->getApiRules() as $key => $rule) {
            $this->$key = Service()->request->getByRule($rule);
        }
    }

    /**
     * 取接口参数规则
     *
     * 主要包括有：
     * - 1、[固定]系统级的service参数
     * - 2、应用级统一接口参数规则，在app.apiCommonRules中配置
     * - 3、接口级通常参数规则，在子类的*中配置
     * - 4、接口级当前操作参数规则
     *
     * <b>当规则有冲突时，以后面为准。另外，被请求的函数名和配置的下标都转成小写再进行匹配。</b>
     *
     * @uses Kernel_App::getRules()
     * @return array
     */
    public function getApiRules() {
        $rules = array();

        $allRules = $this->getRules();
        if (!is_array($allRules)) {
            $allRules = array();
        }

        $allRules = array_change_key_case($allRules, CASE_LOWER);

        $service = Service()->request->get('service', 'Default.Index');
        list($apiClassName, $action) = explode('.', $service);
        $action = strtolower($action); 

        if (isset($allRules[$action]) && is_array($allRules[$action])) {
            $rules = $allRules[$action];
        }
        if (isset($allRules['*'])) {
            $rules = array_merge($allRules['*'], $rules);
        }

        $apiCommonRules = Service()->config->get('app.apiCommonRules', array());
        if (!empty($apiCommonRules) && is_array($apiCommonRules)) {
            $rules = array_merge($apiCommonRules, $rules);
        }

        return $rules;
    }

    /**
     * 获取参数设置的规则
     *
     * 可由开发人员根据需要重载
     * 
     * @return array
     */
    public function getRules() {
        return array();
    }

    /**
     * 过滤器调用
     *
     */
    protected function filterCheck() {
        $filter = Service()->get('filter', 'Kernel_Filter_None');

        if (isset($filter)) {
            if (!($filter instanceof Kernel_Filter)) {
                throw new Kernel_Exception_InternalServerError(
                    T('Service()->filter should be instanceof PhalApi_Filter'));
            }

            $filter->check();
        }
    }

    /**
     * 用户身份验证
     *
     * 根据需要重载，此通用操作一般可以使用委托或者放置在应用接口基类
     * 
     * @throws Kernel_Exception_BadRequest 当验证失败时，请抛出此异常，以返回400
     */
    protected function userCheck() {

    }

}
