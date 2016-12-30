<?php
/**
 *Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 默认接口服务类
 *
 */

class Api_User extends Kernel_App {

    public function getRules() {
        return array(
            'usrlist' => array(
                'pageIndex' 	=> array('name' => 'pageIndex','type' =>'int','default'=>1,'dec'=>'页码' ),
                'pageSize' 	=> array('name' => 'pageSize', 'type' => 'int','default'=>15,'dec'=>'分页值'),
                'sort' 	=> array('name' => 'sort', 'type' => 'array','dec'=>'排序'),
                'filter' 	=> array('name' => 'filter', 'type' => 'array','dec'=>'筛选'),
            ),
            'adduser'=>array(
                'username' =>array('name'=>'username','type'=>'string','dec'=>'用户名'),
                'email' =>array('name'=>'username','type'=>'string','dec'=>'邮箱'),
                'mobile' =>array('name'=>'username','type'=>'int','dec'=>'手机'),
            )
        );
    }

    /**
     * 用户列表
     */
    public function usrlist() {
        $domain = new Domain_User();

        $sort = $filter = array();
        if(isset($this->sort))
            $sort = array_map('json_decode_ex',$this->sort);
        if(isset($this->filter))
            $filter = array_map('json_decode_ex',$this->filter);

        $res = $domain->usrlist($this->pageIndex,$this->pageSize,$sort,$filter);
        return $res;
    }

    /**
     * 新增用户(调取前端数据依次枚举方式太过复杂；中间domain层，目前来看存在意义不大)
    */
    public function adduser(){
        $domain = new Domain_User();
        $res = $domain->adduser($this->username,$this->email,$this->mobile);
        return $res;
    }



}
