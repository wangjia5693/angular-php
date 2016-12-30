<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 对称加密接口
 */

interface Kernel_Crypt {

	/**
	 * 对称加密
	 * 
	 * @param mixed $data 等加密的数据
	 * @param string $key 加密的key
	 * @return mixed 加密后的数据
	 */
    public function encrypt($data, $key);
    
    /**
     * 对称解密
     * 
     * @see Kernel_Crypt::encrypt()
     * @param mixed $data 对称加密后的内容
     * @param string $key 加密的key
     * @return mixed 解密后的数据
     */
    public function decrypt($data, $key);
}
