<?php

/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 */
class Kernel_Filter_Signature implements Kernel_Filter
{
  public function check()
    {
        $signature = Service()->request->get('signature');
        $timestamp = Service()->request->get('timestamp');
        $nonce = Service()->request->get('nonce');

        $token = 'Your Token Here ...';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if ($tmpStr != $signature) {
            throw new Kernel_Exception_BadRequest('wrong sign', 1);
        }
    }

}