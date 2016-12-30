<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 日记抽象类
 *
 * - 对系统的各种情况进行纪录，具体存储媒介由实现类定义
 * - 日志分类型，不分优先级，多种类型可按并组合
 *  位运算可支持多类型日志记录
 */

abstract class Kernel_Logger {

	/**
	 * @var int $logLevel 日志级别
	 */
    protected $logLevel = 0;

    /**
     * @var int LOG_LEVEL_DEBUG 调试级别
     */
    const LOG_LEVEL_DEBUG = 1;
    
    /**
     * @var int LOG_LEVEL_INFO 产品级别
     */
    const LOG_LEVEL_INFO = 2;
    
    /**
     * @var int LOG_LEVEL_ERROR 错误级别
     */
    const LOG_LEVEL_ERROR = 4;

    public function __construct($level) {
        $this->logLevel = $level;
    }

    /**
     * 日志纪录
     *
     * 可根据不同需要，将日记写入不同的媒介
     *
     * @param string $type 日记类型，如：info/debug/error, etc
     * @param string $msg 日记关键描述
     * @param string/array $data 场景上下文信息
     * @return NULL
     */
    abstract public function log($type, $msg, $data);

    /**
     * 应用产品级日志
     * @param string $msg 日志关键描述
     * @param string/array $data 场景上下文信息
     * @return NULL
     */
    public function info($msg, $data = NULL) {
        if (!$this->isAllowToLog(self::LOG_LEVEL_INFO)) {
            return;
        }

        $this->log('info', $msg, $data);
    }

    /**
     * 开发调试级日志
     * @param string $msg 日志关键描述
     * @param string/array $data 场景上下文信息
     * @return NULL
     */
    public function debug($msg, $data = NULL) {
        if (!$this->isAllowToLog(self::LOG_LEVEL_DEBUG)) {
            return;
        }

        $this->log('debug', $msg, $data);
    }

    /**
     * 系统错误级日志
     * @param string $msg 日志关键描述
     * @param string/array $data 场景上下文信息
     * @return NULL
     */
    public function error($msg, $data = NULL) {
        if (!$this->isAllowToLog(self::LOG_LEVEL_ERROR)) {
            return;
        }

        $this->log('error', $msg, $data);
    }

    /**
     * 是否允许写入日志，或运算
     * @param int $logLevel
     * @return boolean
     */
    protected function isAllowToLog($logLevel) {
        return (($this->logLevel & $logLevel) != 0) ? TRUE : FALSE;
    }
}
