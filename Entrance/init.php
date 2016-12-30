<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 统一初始化
 */
 
/** --- 根目录定义，自动加载 --- **/

date_default_timezone_set('Asia/Shanghai');

defined('APP_ROOT') || define('APP_ROOT', dirname(__FILE__) . '/..');
/** 提前需加载：自动加载类，常用函数，服务类，app类 */
require_once APP_ROOT . '/Libs/application.php';

/** 实例化自动加载类 ，按需加载 CoreKenel，vendor 扩展库*/
$loader = new Kernel_Loader(APP_ROOT, 'vendor');
require  '../vendor/autoload.php';

/** --- 自动加载 --- **/
Service()->loader = $loader;

/** --- 国际化，默认英文，初始化中文 --- **/
SL('zh_cn');

/** --- 配置（可选择文件配置或者Yacconf,现使用文件形式） --- **/
Service()->config = new Kernel_Config_File(APP_ROOT . '/Config');

/** --- 调试模式可根据前端，$_GET['_dbg_']判定 --- **/
Service()->debug =  Service()->config->get('sys.debug');//!empty($_GET['_dbg_']) ? true :

/** --- 日志记录 （可配置记录类型，日志记录在Runtime内）--- **/
//Service()->logger = new Kernel_Logger_File(APP_ROOT . '/Runtime', Kernel_Logger::LOG_LEVEL_DEBUG | Kernel_Logger::LOG_LEVEL_INFO | Kernel_Logger::LOG_LEVEL_ERROR);

/** --- 数据操作入口 --- **/
require_once APP_ROOT . '/Libs/eloquent.php';

/** --- 设定错误和异常处理 --- **/
register_shutdown_function('Kernel_ErrorsHandle::fatalError');
set_error_handler('Kernel_ErrorsHandle::appError');
set_exception_handler('Kernel_ErrorsHandle::appException');


use Pheanstalk\Pheanstalk;

$pheanstalk = new Pheanstalk('192.168.40.131');
$job = $pheanstalk
    ->watch('testtube')
    ->ignore('default')
    ->reserve();

echo $job->getData();

$pheanstalk->delete($job);

exit;





/** --- Service()初始化的时候会自动初始两个服务组件：request,response;按照需求可在此处重新配置response --- **/
//支持JsonP的返回
//if (!empty($_GET['callback'])) {
//Service()->response = new App_Response_JsonP($_GET['callback']);
//}

/** --- angular项目post数据，不能正常接收；采用下面强制方法；但只能接收post数据--- **/
//Service()->request = new Kernel_Request(json_decode(file_get_contents('php://input'),true));

/** --- Memcache/Memcached缓存操作  --- **/
Service()->memc = function () {
    return new Kernel_Cache_Memcache(Service()->config->get('sys.mc'));
};
/** --- SQLi数据操作 (需测试：Warning:  mysqli::query():Couldn't fetch mysqli) --- **/
//Service()->sqli = function () {
//    $sqlicig = Service()->debug ? Service()->config->get('dbs.db_debug') : Service()->config->get('dbs.db');
//    return  new Kernel_DB_Sqli($sqlicig);
//};

/** --- Mongo数据操作  --- **/
Service()->mongo = function () {
    $mgocig = Service()->debug ? Service()->config->get('dbs.mongo_debug') : Service()->config->get('dbs.mongo');
    return  new Kernel_DB_Mongo($mgocig);
};

/*** 可选组件 签名验证服务，可自由扩展；app类中init方法执行验证；
Service()->filter = 'Signature';
*/
