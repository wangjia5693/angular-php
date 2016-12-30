<?php

class Domain_User {

    public function login($username,$password){
        $res = array();
        $model = new Model_User();
        $res = $model->check($username,$password);
        return $res;
    }

    //    用户列表
     public function usrlist($index,$size,$sort,$filter){
         $res = array();
         $model = new Model_User();
         $res = $model->usrlist($index,$size,$sort,$filter);
         return $res;
     }

    //    新增用户
    public function adduser($username,$email,$mobile){
        $res = array();
        $model = new Model_User();
        $res = $model->adduser($username,$email,$mobile);
        return $res;
    }

}
