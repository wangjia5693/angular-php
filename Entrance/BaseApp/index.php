<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 入口文件
 */

/**
 * 注册组件，服务等
 * App_Request        $request    请求
 * App_Response_Json  $response   结果响应
 * App_Cache          $cache      缓存
 * App_Crypt          $crypt      加密
 * App_Config         $config     配置
 * App_Logger         $logger     日记
 * App_Loader         $loader     自动加载
 */

require_once dirname(__FILE__) . '/../init.php';

/** Service()容器单一实例 */
Service()->loader->addDirs('BaseApp');

/**响应接口请求 **/

$api = new App();
$rs = $api->response();
$rs->output();

