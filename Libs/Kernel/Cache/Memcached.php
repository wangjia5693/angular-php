<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * MC缓存
 *
 * - 使用序列化对需要存储的值进行转换，以提高速度
 *
 */

class Kernel_Cache_Memcached extends Kernel_Cache_Memcache {

    /**
     * 注意参数的微妙区别
     */
    public function set($key, $value, $expire = 600) {
        $this->memcache->set($this->formatKey($key), @serialize($value), $expire);
    }

    /**
     * 返回更高版本的MC实例
	 * @return Memcached
     */
    protected function createMemcache() {
        return new Memcached();
    }
}
