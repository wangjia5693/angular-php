<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/5
 * Time: 17:03
 */

class Domain_Public {

    public function allNodes(){
        $res = array();
        $model = new Model_Public();
        $res = $model->allNodes();
        return $res;
    }


}