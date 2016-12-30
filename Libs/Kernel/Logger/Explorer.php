<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 直接输出日记到控制台的日记类
 * 
 * - 测试环境下使用
 *
 */

class Kernel_Logger_Explorer extends Kernel_Logger {

	public function log($type, $msg, $data) {
        $msgArr = array();
        $msgArr[] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $msgArr[] = strtoupper($type);
        $msgArr[] = str_replace(PHP_EOL, '\n', $msg);
        if ($data !== NULL) {
            $msgArr[] = is_array($data) ? json_encode($data) : $data;
        }

        $content = implode('|', $msgArr) . PHP_EOL;

        echo "\n", $content, "\n";
	}
}
