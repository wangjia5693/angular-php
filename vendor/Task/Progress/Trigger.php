<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 触发器接口
 * 
 */

interface Task_Progress_Trigger {

	/**
	 * 进程的具体操作
	 * @param string $params 对应数据库表task_progress.fire_params字段
	 */
    public function fire($params);
}
