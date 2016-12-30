<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 组合模式下的多级缓存
 *
 * - 可以自定义添加多重缓存，注意优先添加高效缓存
 * - 最终将委托给各级缓存进行数据的读写，其中读取为短路读取
 *
 */

class Kernel_Cache_Multi implements Kernel_Cache {
    
    protected $caches = array();

    public function addCache(Kernel_Cache $cache) {
		$this->caches[] = $cache;
    }

    public function set($key, $value, $expire = 600) {
        foreach ($this->caches as $cache) {
			$cache->set($key, $value, $expire);
		}
    }

    public function get($key) {
        foreach ($this->caches as $cache) {
			$value = $cache->get($key);
			if ($value !== NULL) {
				return $value;
			}
		}

		return NULL;
    }

    public function delete($key) {
		foreach ($this->caches as $cache) {
			$cache->delete($key);
		}
    }
}
