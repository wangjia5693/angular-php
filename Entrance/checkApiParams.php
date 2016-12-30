<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 工具 - 查看接口参数规则
 */

require_once dirname(__FILE__) . '/init.php';

//装载你的接口
Service()->loader->addDirs('Demo');

$apiDesc = new Kernel_Helper_ApiDesc();
$apiDesc->render();

