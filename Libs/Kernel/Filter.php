<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 *
 * 拦截器接口
 *  
 * 为应用实现接口请求拦截提供统一处理接口
 * 
 * <br>实现和使用示例：</br>
```
 * 	class My_Filter implements PhalApi_Filter {
 * 
 * 		public function check() {
 * 			//TODO
 * 		}
 * 	}
 *
 * //$ vim ./Public/init.php
 * //注册签名验证服务 
 * Service()->filter = 'Common_SignFilter';
 */

interface Kernel_Filter {

    public function check();
}
