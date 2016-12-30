<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 框架版本号
 */
defined('APP_VERSION') || define('APP_VERSION', '0.0.1');
 
/**
 * 项目根目录
 */
defined('CORE_ROOT') || define('CORE_ROOT', dirname(__FILE__));

require_once CORE_ROOT . DIRECTORY_SEPARATOR . 'Kernel' . DIRECTORY_SEPARATOR . 'Loader.php';

/**
 * 应用类
 * - 实现远程服务的响应、调用等操作
 * $api = new App();
 * $rs = $api->response();
 * $rs->output();
 *  array(
 *      'ret'   => 200,	 //服务器响应状态
 *      'data'  => array(),//正常并成功响应后，返回给客户端的数据
 *      'msg'   => '',	//错误提示信息
 *  );
 */

class App {

    /**
     * 响应操作
     * 通过工厂方法创建合适的控制器，然后调用指定的方法，最后返回格式化的数据。
     */
    public function response() {
        $rs = Service()->response;
        /*** request->get()第二个参数为默认值*/
        $service = Service()->request->get('service', 'Default.Index');

        try {
            /***
             * 工厂方法创建合适的控制器;
             * 默认generateService()内参数为true;需要身份验证等操作
             */
            $api = Kernel_Factory::generateService();
            list($apiClassName, $action) = explode('.', $service);
            $data = call_user_func(array($api, $action));

            $rs->setData($data);
        } catch (Kernel_Exception $ex) {

            $rs->setRet($ex->getCode());
            $rs->setMsg($ex->getMessage());
        } catch (Exception $ex) {

            Service()->logger->error($service, strval($ex));
            throw $ex;
        }

        return $rs;
    }
    
}
