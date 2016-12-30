<?php
/**
 *
 *  Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 简单的MD5拦截器
 *
 * - 签名的方案如下：
 *
 * + 1、排除签名参数（默认是sign）
 * + 2、将剩下的全部参数，按参数名字进行字典排序
 * + 3、将排序好的参数，全部用字符串拼接起来
 * + 4、进行md5运算
 *
 * 注意：无任何参数时，不作验签

 */

class Kernel_Filter_SimpleMD5 implements Kernel_Filter {

    protected $signName;

    public function __construct($signName = 'sign') {
        $this->signName = $signName;
    }

    public function check() {
        $allParams = Service()->request->getAll();
        if (empty($allParams)) {
            return;
        }

        $sign = isset($allParams[$this->signName]) ? $allParams[$this->signName] : '';
        unset($allParams[$this->signName]);

        $expectSign = $this->encryptAppKey($allParams);

        if ($expectSign != $sign) {
            Service()->logger->debug('Wrong Sign', array('needSign' => $expectSign));
            throw new Kernel_Exception_BadRequest(T('wrong sign'), 6);
        }
    }

    protected function encryptAppKey($params) {
        ksort($params);

        $paramsStrExceptSign = '';
        foreach ($params as $val) {
            $paramsStrExceptSign .= $val;
        }

        return md5($paramsStrExceptSign);
    }
}
