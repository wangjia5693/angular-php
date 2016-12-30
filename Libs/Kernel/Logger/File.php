<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 文件日记纪录类
 *
 * - 将日记写入文件，文件目录可以自定义
 *
 * <br>接口实现示例：<br>
```
 *      class App_Logger_Mock extends App_Logger {
 *          public function log($type, $msg, $data) {
 *              //nothing to do here ...
 *          }
 *      }
 *
 *      //保存全部类型的日记
 *      $logger = new App_Logger_Mock(
 *          App_Logger::LOG_LEVEL_DEBUG | App_Logger::LOG_LEVEL_INFO | App_Logger::LOG_LEVEL_ERROR);
 *
 *      //开发调试使用，且带更多信息
 *      $logger->debug('this is bebug test', array('name' => 'mock', 'ver' => '1.0.0'));
 *
 *      //业务场景使用
 *      $logger->info('this is info test', 'and more detail here ...');
 *
 *      //一些不该发生的事情
 *      $logger->error('this is error test');
 */

class Kernel_Logger_File extends Kernel_Logger {

    /** 外部传参 **/
    protected $logFolder;
    protected $dateFormat;

    /** 内部状态 **/
    protected $fileDate;
    protected $logFile;

    public function __construct($logFolder, $level, $dateFormat = 'Y-m-d H:i:s') {
        $this->logFolder = $logFolder;
        $this->dateFormat = $dateFormat;

        parent::__construct($level);
        
        $this->init();
    }

    protected function init() {
        // 跨天时新建日记文件
        $curFileDate = date('Ymd', time());
        if ($this->fileDate == $curFileDate) {
            return;
        }
        $this->fileDate = $curFileDate;

        // 每月一个目录
        $folder = $this->logFolder
            . DIRECTORY_SEPARATOR . 'log'
            . DIRECTORY_SEPARATOR . substr($this->fileDate, 0, -2);
        if (!file_exists($folder)) {
            mkdir($folder . '/', 0777, TRUE);
        }

        // 每天一个文件
        $this->logFile = $folder
            . DIRECTORY_SEPARATOR . $this->fileDate . '.log';
        if (!file_exists($this->logFile)) {
            touch($this->logFile);
            chmod($this->logFile, 0777);
        }
    }

    public function log($type, $msg, $data) {
        $this->init();

        $msgArr = array();
        $msgArr[] = date($this->dateFormat, time());
        $msgArr[] = strtoupper($type);
        $msgArr[] = str_replace(PHP_EOL, '\n', $msg);
        if ($data !== NULL) {
            $msgArr[] = is_array($data) ? json_encode($data) : $data;
        }

        $content = implode('|', $msgArr) . PHP_EOL;

        file_put_contents($this->logFile, $content, FILE_APPEND);
    }
}
