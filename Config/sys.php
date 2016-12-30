<?php 
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/12/01.
 * 生产环境
 */

return array(
	/**
	 * 默认环境配置
	 */
	'debug' => true,

	/**
	 * MemCache缓存服务器参考配置
	 */
	 'mc_debug' => array(
        'host' => '192.168.101.87',
        'port' => 11211,
	 ),
	/**
	 * MemCache缓存服务器参考配置
	 */
	'mc' => array(
		'host' => '127.0.0.1',
		'port' => 11211,
	),

    /**
     * 加密
     */
    'crypt' => array(
        'mcrypt_iv' => '12345678',      //8位
    ),
);
