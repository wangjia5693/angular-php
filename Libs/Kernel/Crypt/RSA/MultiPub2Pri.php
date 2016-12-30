<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * Crypt_RSA_MultiPub2Pri 超长RSA加密
 * 
 * RSA - 公钥加密，私钥解密 - 超长字符串的应对方案
 *
 */

class App_Crypt_RSA_MultiPub2Pri extends App_Crypt_RSA_MultiBase {

    protected $pub2pri;

    public function __construct() {
        $this->pub2pri = new PhalApi_Crypt_RSA_Pub2Pri();

        parent::__construct();
    }

    protected function doEncrypt($toCryptPie, $pubkey) {
        return $this->pub2pri->encrypt($toCryptPie, $pubkey);
    }

    protected function doDecrypt($encryptPie, $prikey) {
        return $this->pub2pri->decrypt($encryptPie, $prikey);
    }
}
