<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 快速接口执行 - 辅助类
 * 
 * - 使用示例：
```
 * public function testWhatever() {
 *		//Step 1. 构建请求URL
 *		$url = 'service=Default.Index&username=dogstar';
 *		
 *		//Step 2. 执行请求	
 *		$rs = App_Helper_TestRunner::go($url);
 *		
 *		//Step 3. 验证
 *		$this->assertNotEmpty($rs);
 *		$this->assertArrayHasKey('code', $rs);
 *		$this->assertArrayHasKey('msg', $rs);
 * }
 */

class Kernel_Helper_TestRunner {

    /**
     * @param string $url 请求的链接
     * @param array $param 额外POST的数据
     * @return array 接口的返回结果
     */
    public static function go($url, $params = array()) {
        parse_str($url, $urlParams);
        $params = array_merge($urlParams, $params);

        if (!isset($params['service'])) {
            throw new Exception('miss service in url');
        }
        Service()->request = new App_Request($params);

        $apiObj = Kernel_Factory::generateService(true);
        list($api, $action) = explode('.', $urlParams['service']);

        $rs = $apiObj->$action();

        /**
        $this->assertNotEmpty($rs);
        $this->assertArrayHasKey('code', $rs);
        $this->assertArrayHasKey('msg', $rs);
         */

        return $rs;
    }
}

