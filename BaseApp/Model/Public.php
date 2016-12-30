<?php
use Illuminate\Database\Capsule\Manager as DB;
class Model_Public  extends Kernel_Model{

    public function allNodes(){

        $node_info = DB::table ('node_2_pmp')->where('status','Y')->select('id','title','url','level','pid')->get()->toArray();
        if (!$node_info)
            return array('code'=>300,'msg'=>T('nodes  is not exists'));
        $result = array();

        foreach ($node_info as $item) {
            if ($item->pid == "0"){
                $result[$item->id] = json_decode( json_encode( $item),true);
            } else {
                $result[$item->pid]['child'][] = json_decode( json_encode( $item),true);
            }
        }
        return array('code'=>200,'node_info'=>$result);
    }
}
