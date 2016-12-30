<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 创建控制器类 工厂方法
 *
 * 将创建与使用分离，简化客户调用，负责控制器复杂的创建过程
 *      //根据请求(?service=XXX.XXX)生成对应的接口服务，并进行初始化
 *      $api = App_Factory::generateService();
 */

class Kernel_Factory {

	/**
     * 创建服务器
     * 根据客户端提供的接口服务名称和需要调用的方法进行创建工作，如果创建失败，则抛出相应的自定义异常
     *
     * 创建过程主要如下：
     * - 1、 是否缺少控制器名称和需要调用的方法
     * - 2、 控制器文件是否存在，并且控制器是否存在
     * - 3、 方法是否可调用
     * - 4、 控制器是否初始化成功
     */
	static function generateService($isInitialize = TRUE) {
		$service = Service()->request->get('service', 'Default.Index');
		
		$serviceArr = explode('.', $service);

		if (count($serviceArr) < 2) {
            throw new Kernel_Exception_BadRequest(
                T('service ({service}) illegal', array('service' => $service))
            );
        }

		list ($apiClassName, $action) = $serviceArr;
	    $apiClassName = 'Api_' . ucfirst($apiClassName);
        // $action = lcfirst($action);

        if (!class_exists($apiClassName)) {
            throw new Kernel_Exception_BadRequest(
                T('no such service as {service}', array('service' => $service))
            );
        }
        		
    	$api = new $apiClassName();

        if (!is_subclass_of($api, 'Kernel_App')) {
            throw new Kernel_Exception_InternalServerError(
                T('{class} should be subclass of App_Api', array('class' => $apiClassName))
            );
        }
    			
    	if (!method_exists($api, $action) || !is_callable(array($api, $action))) {
            throw new Kernel_Exception_BadRequest(
                T('no such service as {service}', array('service' => $service))
            );
    	}

        /**
         * 实现身份验证、按参数规则解析生成接口参数等操作
         */
        if ($isInitialize) {
            $api->init();
        }
		
		return $api;
	}
	
}
