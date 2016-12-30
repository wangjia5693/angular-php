<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * Crypt_RSA_Pub2Pri 原始RSA加密
 * 
 * RSA - 公钥加密，私钥解密
 *
 */

class App_Crypt_RSA_Pub2Pri implements App_Crypt {

    public function encrypt($data, $pubkey) {
        $rs = '';

        if (@openssl_public_encrypt($data, $rs, $pubkey) === FALSE) {
            return NULL;
        }

        return $rs;
    }
    
    public function decrypt($data, $prikey) {
        $rs = '';

        if (@openssl_private_decrypt($data, $rs, $prikey) === FALSE) {
            return NULL;
        }

        return $rs;
    }
}
