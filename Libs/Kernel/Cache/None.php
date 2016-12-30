<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 空缓存 - NULL-Object空对象模式
 *
 */

class Kernel_Cache_None implements Kernel_Cache {

	public function set($key, $value, $expire = 600) {
	}

    public function get($key) {
		return NULL;
	}

    public function delete($key) {
	}
}
