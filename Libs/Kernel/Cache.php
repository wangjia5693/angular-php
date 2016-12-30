<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 缓存接口

 */

interface Kernel_Cache {

	/**
	 * 设置缓存
	 * 
	 * @param string $key 缓存key
	 * @param mixed $value 缓存的内容
	 * @param int $expire 缓存有效时间，单位秒，非时间戳
	 */
    public function set($key, $value, $expire = 600);

    /**
     * 读取缓存
     * 
     * @param string $key 缓存key
     * @return mixed 失败情况下返回NULL
     */
    public function get($key);

    /**
     * 删除缓存
     * 
     * @param string $key
     */
    public function delete($key);
}
