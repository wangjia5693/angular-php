<?php
/**
 *Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 默认接口服务类
 *
 */

class Api_Default extends Kernel_App {

	public function getRules() {
        return array(
            'index' => array(
                'username' 	=> array('name' => 'username','require' =>true ),
                'password' 	=> array('name' => 'password', 'require' => true),
            ),
        );
	}
	
	/**
	 * 登录
	 */
	public function index() {

		$domain = new Domain_User();
		$res = $domain->login($this->username,$this->password);
		return $res;
	}






}
