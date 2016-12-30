<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 键-值对的 MQ
 *
 * - 队列存放于Key-Value的缓存中
 *
 */

class Task_MQ_KeyValue implements Task_MQ {

    /**
     * @var Kernel_Cache_Memcached/Kernel_Cache_Memcache/Kernel_Cache_File $kvCache 缓存实例
     */
    protected $kvCache;

    public function __construct(Kernel_Cache $kvCache) {
        $this->kvCache = $kvCache;
    }

    public function add($service, $params = array()) {
        $list = $this->kvCache->get($service);
        if (empty($list)) {
            $list = array();
        }

        $list[] = $params;

        $this->kvCache->set($service, $list, $this->getExpireTime());

        $list = $this->kvCache->get($service);

        return true;
    }

    public function pop($service, $num = 1) {
        $rs = array();
        if ($num <= 0) {
            return $rs;
        }

        $list = $this->kvCache->get($service);
        if (empty($list)) {
            $list = array();
        }

        $rs = array_splice($list, 0, $num);

        $this->kvCache->set($service, $list, $this->getExpireTime());

        return $rs;
    }

    /**
     * 最大缓存时间，一年
     */
    protected function getExpireTime() {
        return 31536000;
    }
}
