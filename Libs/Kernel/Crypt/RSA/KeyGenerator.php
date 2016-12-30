<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * Crypt_RSA_KeyGenerator 生成器
 * 
 * RSA私钥或公钥的生成器
 *
 */

class App_Crypt_RSA_KeyGenerator {

    protected $privkey;

    protected $pubkey;

    public function __construct() {
        $res = openssl_pkey_new();
        openssl_pkey_export($res, $privkey);
        $this->privkey = $privkey;

        $pubkey = openssl_pkey_get_details($res);
        $this->pubkey = $pubkey['key'];
    }

    public function getPriKey() {
        return $this->privkey;
    }

    public function getPubKey() {
        return $this->pubkey;
    }
}
