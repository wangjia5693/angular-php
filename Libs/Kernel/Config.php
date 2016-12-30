<?php 
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 *  配置接口
 *
 * 获取系统所需要的参数配置
 * 
 * <br>使用示例：<br>
```
 * //假设有这样的app.php配置：
 * return array(
 *  'version' => '1.1.1',
 * 
 *  'email' => array(
 *      'address' => 'chanzonghuang@gmail.com',
 *   );
 * );
 *
 * //我们就可以分别这样根据需要获取配置：
 * //app.php里面的全部配置
 * Service()->config->get('app');
 * 
 * //app.php里面的单个配置
 * Service()->config->get('app.version');  //返回：1.1.1
 * 
 * //app.php里面的多级配置
 * Service()->config->get('app.version.address');  //返回：chanzonghuang@gmail.com
 */

interface Kernel_Config {

	/**
     * 获取配置
     * 
     * @param $key string 配置键值
     * @param mixed $default 缺省值
     * @return mixed 需要获取的配置值，不存在时统一返回$default
     */
	public function get($key, $default = NULL);
}
