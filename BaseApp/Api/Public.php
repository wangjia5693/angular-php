<?php
/**
 *Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 默认接口服务类
 *
 */

class Api_Public extends Kernel_App {

    public function getRules() {
        return array(
            'allNodes' => array(
            ),
        );
    }

    /**
     * 登录
     */
    public function allNodes() {

        $domain = new Domain_Public();
        $res = $domain->allNodes();
        return $res;
    }
}
