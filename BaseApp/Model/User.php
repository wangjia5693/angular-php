<?php
use Illuminate\Database\Capsule\Manager as DB;

class Model_User  extends Kernel_Model{

//    登录验证
    public function check($username,$password){

        $user_info = DB::table ('user_2_pmp')->where('email',$username)->select('username','password')->first();
        if (!$user_info)
            return array('code'=>300,'msg'=>T('user : {usr} is not exists',array('usr'=>$username)));

        #判断用户密码是否正确 登录错误记录次数 等于五次记录
        if (md5(trim($password)) != $user_info->password)
            return array('code'=>301,'msg'=>T('password : {password} is not correct',array('password'=>$password)));

        return array('code'=>200,'username'=>$username,'password'=>$password,'userRole'=>'admin');
    }

//  用户列表
    public function usrlist($index,$size,$sort,$filter){

        /**
         * db使用测试；利用laravel-debugbar查看QueryCollector
         *
                $sql = "select username, email from user_2_pmp";
                $users = DB::select($sql);

                $users = DB::select('select * from user_2_pmp where id = :id', ['id' => 1]);
                        var_dump($users);exit;

                try{
                     DB::insert('insert into user_2_pmp (id, username) values (?, ?)', [1, 'Dayle']);
                }catch(Exception $e){
                    Service()->logger->error(json_encode($e));
                    throw new Exception("insert false.");
                }
        */

        $base = DB::table('user_2_pmp')->where('status','Y');


        if(isset($filter[0])&&!empty($filter[0])){
            foreach($filter[0] as $fk=>$fv){
                if(!empty($fv))
                    $base = $base->where($fk,$fv);
            }
        }
        if(isset($sort[0])&&!empty($sort[0])){
            foreach($sort[0] as $sk=>$sv){
                $base = $base->orderBy($sk,$sv);
            }
        }
        $count = $base->count();
        $usrlist = $base->skip(($index-1)*$size)->take($size)->select('username','email','id','mobile')->get()->toArray();

        return array('code'=>200,'list'=>$usrlist,'total'=>$count);

    }

//    新增用户
    public function adduser($username,$email,$mobile){
        try{

        }catch(Exception $e){

        }
    }

}
