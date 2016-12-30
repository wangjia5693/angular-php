<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * Yaconf扩展配置类
 *
 * - 通过Yaconf扩展快速获取配置
 *
 * 使用示例：
```
 * <code>
 * $config = new PhalApi_Config_Yaconf();
 *
 * var_dump($config->get('foo')); //相当于var_dump(Yaconf::get("foo"));
 *
 * var_dump($config->has('foo')); //相当于var_dump(Yaconf::has("foo"));
 * </code>
 */

class Kernel_Config_Yaconf implements Kernel_Config {

    public function get($key, $default = NULL) {
        return Yaconf::get($key, $default);
    }

    public function has($key) {
        return Yaconf::has($key);
    }
}
