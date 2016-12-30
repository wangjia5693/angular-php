<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * Redis缓存
 *
 * - 使用序列化对需要存储的值进行转换，以提高速度
 * - 提供更多redis的操作，以供扩展类库使用
 * 缓存不像是数据库当你需要去查看缓存的时候,如果所有的数据都堆积在redis的一个库,你会非常痛苦
    但是redis支持多库所以需要一套规范来划分,
0~10库 作为正常业务库,也就是推送队列,临时数据,每一个库都只存储一种业务的数据,比如微信推送就存在5库,而邮件推送的数据就存在6库,
 * 发送验证码的临时数据存储在3库,一次类推,如果觉得10个库还不够用可以根据业务增加
10库以上作为cache库用来存储每张表的结果集数据,或者是其余的数据
 */

class Kernel_Cache_Redis implements Kernel_Cache {

    protected $redis;

    protected $auth;

    protected $prefix;

    /**
     * @param string $config['type']    Redis连接方式 unix,http
     * @param string $config['socket']  unix方式连接时，需要配置
     * @param string $config['host']    Redis域名
     * @param int    $config['port']    Redis端口,默认为6379
     * @param string $config['prefix']  Redis key prefix
     * @param string $config['auth']    Redis 身份验证
     * @param int    $config['db']      Redis库,默认0
     * @param int    $config['timeout'] 连接超时时间,单位秒,默认300
     */
    public function __construct($config) {
        $this->redis = new Redis();

        // 连接
        if (isset($config['type']) && $config['type'] == 'unix') {
            if (!isset($config['socket'])) {
                throw new Kernel_Exception_InternalServerError(T('redis config key [socket] not found'));
            }
            $this->redis->connect($config['socket']);
        } else {
            $port = isset($config['port']) ? intval($config['port']) : 6379;
            $timeout = isset($config['timeout']) ? intval($config['timeout']) : 300;
            $this->redis->connect($config['host'], $port, $timeout);
        }

        // 验证
        $this->auth = isset($config['auth']) ? $config['auth'] : '';
        if ($this->auth != '') {
            $this->redis->auth($this->auth);
        }

        // 选择
        $dbIndex = isset($config['db']) ? intval($config['db']) : 0;
        $this->redis->select($dbIndex);

        $this->prefix = isset($config['prefix']) ? $config['prefix'] : 'app:';
    }

    /**
     * 将value 的值赋值给key,生存时间为expire秒
     */
    public function set($key, $value, $expire = 600) {
        $this->redis->setex($this->formatKey($key), $expire, $this->formatValue($value));
    }

    public function get($key) {
        $value = $this->redis->get($this->formatKey($key));
        return $value !== FALSE ? $this->unformatValue($value) : NULL;
    }

    public function delete($key) {
         $this->redis->delete($this->formatKey($key));
    }

    /**
     * 检测是否存在key,若不存在则赋值value
     */
    public function setnx($key, $value) {
        return $this->redis->setnx($this->formatKey($key), $this->formatValue($value));
    }

    public function lPush($key, $value) {
        return $this->redis->lPush($this->formatKey($key), $this->formatValue($value));
    }

    public function rPush($key, $value) {
        return $this->redis->rPush($this->formatKey($key), $this->formatValue($value));
    }

    public function lPop($key) {
        $value = $this->redis->lPop($this->formatKey($key));
        return $value !== FALSE ? $this->unformatValue($value) : NULL;
    }

    public function rPop($key) {
        $value = $this->redis->rPop($this->formatKey($key));
        return $value !== FALSE ? $this->unformatValue($value) : NULL;
    }

    protected function formatKey($key) {
        return $this->prefix . $key;
    }

    protected function formatValue($value) {
        return @serialize($value);
    }

    protected function unformatValue($value) {
        return @unserialize($value);
    }
}
