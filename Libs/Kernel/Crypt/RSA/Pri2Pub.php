<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * Crypt_RSA_Pri2Pub RSA原始加密
 * 
 * RSA - 私钥加密，公钥解密
 *
 */

class App_Crypt_RSA_Pri2Pub implements App_Crypt {

    public function encrypt($data, $prikey) {
        $rs = '';

        if (@openssl_private_encrypt($data, $rs, $prikey) === FALSE) {
            return NULL;
        }

        return $rs;
    }

    public function decrypt($data, $pubkey) {
        $rs = '';

        if (@openssl_public_decrypt($data, $rs, $pubkey) === FALSE) {
            return NULL;
        }

        return $rs;
    }
}
