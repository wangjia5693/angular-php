<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * Crypt_RSA_MultiPri2Pub 超长RSA加密
 * 
 * RSA - 私钥加密，公钥解密 - 超长字符串的应对方案
 *
 */

class App_Crypt_RSA_MultiPri2Pub extends App_Crypt_RSA_MultiBase {

    protected $pri2pub;

    public function __construct() {
        $this->pri2pub = new PhalApi_Crypt_RSA_Pri2Pub();

        parent::__construct();
    }

    protected function doEncrypt($toCryptPie, $prikey) {
        return $this->pri2pub->encrypt($toCryptPie, $prikey);
    }

    protected function doDecrypt($encryptPie, $prikey) {
        return $this->pri2pub->decrypt($encryptPie, $prikey);
    }
}
