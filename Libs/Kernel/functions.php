<?php
/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 快速的函数和方法
 */

/**
 * 获取DI
 *
 */
function Service() {
    return Kernel_Service::one();
}

/**
 * 前导<pre>的易读输出
 * @param mixed $mix
 * @return void
 */
function pre($mix)
{
    $args = func_get_args();
    if (isset($args[1])) {
        foreach ($args as $arg)
            echo '<pre>' . print_r($arg, true);
    } else
        echo '<pre>' . print_r($mix, true);
}

/**
 * 设定语言，SL为setLanguage的简写
 * @param string $language 翻译包的目录名
 */
function SL($language) {
    Kernel_Translator::setLanguage($language);
}

/**
 * 快速翻译
 * @param string $msg 待翻译的内容
 * @param array $params 动态参数
 */
function T($msg, $params = array()) {
    return Kernel_Translator::get($msg, $params);
}

/**
 * json_decode 扩展
 * @param string $str json串
 * @param bool $urldecode 是否 urldecode
 * @return mixed|null
 */
function json_decode_ex($str, $urldecode = true)
{
    if (is_null($str))
        return null;

    # 编码转换
    $get_encode = mb_detect_encoding($str, array('UTF-8', 'ASCII', 'GB2312', 'GBK', 'CP936'), true);
    if ($get_encode != 'UTF-8')
        $str = mb_convert_encoding($str, 'UTF-8', $get_encode);

    # 第一次解，正常解
    list($mix, $err) = json_decode_error($str, true);
    if ($err == JSON_ERROR_NONE)
        return $mix;

    # 第二次解，异常解
    ## 替换双引号为空串，正常语法时不应该的，但这里就是异常情况
    $str = str_replace(array('\\&quot;', '&quot;'), array('', '"'), $str);

    ### 将 JSON 的 key->value 部分的 value 全回调 urlencode，注意正则的冒号
    $str = preg_replace_callback('/:"([^"]*?)"/u', function ($mac) {
        return ':"' . urlencode($mac[1]) . '"';
    }, $str);

    # 解
    list($mix, $err) = json_decode_error($str, true);
    if ($err != JSON_ERROR_NONE)
        return $mix;

    if ($urldecode && is_array($mix)) {
        array_walk_recursive($mix, function (&$v) {
            $v = urldecode($v);
        });
    }

    return $mix;
}

/**
 * json_decode 或解码失败是否打日志
 * @param string $str json串
 * @param bool $loger 是否打日志
 * @return array
 */
function json_decode_error($str, $loger = false)
{
    $mix = json_decode($str, true);
    $err = json_last_error();
    if ($loger && $err != JSON_ERROR_NONE)
        loger($str, __FUNCTION__ . $err);

    return array($mix, $err);
}


/**
 * 错误消息
 * @param $msg
 */
function error($msg)
{
    exit(json_encode(array('error' => $msg)));
}



function loger($message, $logname, $trace = false)
{

    $logdir = (isset($dir) ? $dir : APP_ROOT . '/data/log/') . date('Ymd/');

    if (!is_dir($logdir) && !mkdir($logdir, 0777, true))
        error('cannot make log dir');

    $logpath = $logdir . $logname . '.log';

    ## 判断 debug_backtrace 是否存在 @zhaitt
    if (($trace || class_exists($logname)) && function_exists('debug_backtrace')) {
        $trcs = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        if (!empty($trcs)) {
            array_walk($trcs, function (&$val) {
                $exp = '';

                if (isset($val['function'])) {
                    if (isset($val['args'])) {
                        $vstr = '';

                        foreach ($val['args'] as $v)
                            $vstr .= ',' . var_export($v, true);

                        $val['args'] = substr($vstr, 1);
                    }

                    $c = &$val['class'];
                    $t = &$val['type'];

                    $exp = sprintf('%s%s%s(...)', isset($c) ? $c : '', isset($t) ? $t : '', $val['function']);
                }

                $f = &$val['file'];
                # 忽略 windows
                if (stripos($f, APP_ROOT) === 0)
                    $f = substr($f, strlen(APP_ROOT));

                $val = $f . '(' . $val['line'] . '):' . $exp;
            });

            $message .= ' trace: ' . print_r(implode(' < ', $trcs), true);
        }
    }

    $message = str_replace(array("\t", "\r", "\n"), array('', '', ' '), trim($message));

    $uak = &$_SESSION['USER_AUTH_KEY'];
    $message = date('Y-m-d+H:i:s ') . (isset($uak) ? intval($uak) : '0') . ' ' . $message . PHP_EOL;

    error_log($message, 3, $logpath);
}

/**
 * 生成 guid
 * @param bool $brace 是否带花括号
 * @return string
 */
function guid($brace = true)
{
    if (function_exists('com_create_guid')) {
        $uuid = com_create_guid();
        if (!$brace)
            $uuid = trim($uuid, '{}');
    } else {
        $hash = strtoupper(md5(uniqid(mt_rand(), true)));
        $uuid = substr($hash, 0, 8) . '-' . substr($hash, 8, 4) . '-' . substr($hash, 12, 4) . '-' . substr($hash, 16, 4) . '-' . substr($hash, 20, 12);
        if ($brace)
            $uuid = '{' . $uuid . '}';
    }
    return $uuid;
}

/** #@ jijg
 * @param array $array 需要取出数组列的多维数组（或结果集）
 * @param string $column_key 需要返回值的列，它可以是索引数组的列索引，或者是关联数组的列的键。 也可以是 NULL ，此时将返回整个数组（配合index_key参数来重置数组键的时候，非常管用）
 * @param string $index_key 作为返回数组的索引/键的列，它可以是该列的整数索引，或者字符串键值。
 * @return array
 */
function get_array_column(array $array, $column_key, $index_key = null)
{
    if (function_exists('array_column')) { //array_column PHP 5.5
        return array_column($array, $column_key, $index_key);
    }
    $result = [];
    if (!is_array($array)) {
        return $result;
    }
    foreach ($array as $arr) {
        if (!is_array($arr))
            continue;

        if (is_null($column_key)) {
            $value = $arr;
        } else {
            $value = isset($arr[$column_key]) ? $arr[$column_key] : '';
        }

        if (!is_null($index_key)) {
            $key = $arr[$index_key];
            $result[$key] = $value;
        } else {
            $result[] = $value;
        }
    }

    return $result;
}

function get_ip()
{
    if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] != 'unknown') {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != 'unknown') {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * 当 limit>0 时字符串将分割成 limit 限制的个数；limit<1 时等于explode
 * @param string $sep 分割串
 * @param string $str 源字符串
 * @param int $limit 限制个数
 * @param mixed $padval 补全元素,默认空串
 * @return array
 */
function safe_split($sep, $str, $limit = 0, $padval = '')
{
    if (!isset($str) || !isset($sep) || $str == '' || $sep == '') {
        if ($limit > 0)
            return array_fill(0, $limit, $padval);

        return array();
    }

    if ($limit > 0) {
        $arr = explode($sep, $str, $limit);
        for ($i = count($arr); $i < $limit; $i++)
            $arr[$i] = $padval;

        return $arr;
    } else
        return explode($sep, $str);
}

/**
 * 返回数组维数（层级）
 * @author echo <chenj@nalashop.com>
 * @param array $arr
 * @return int
 */
function array_depth($arr)
{
    if (is_array($arr)) {
        #递归将所有值置NULL，目的1、消除虚构层如array("array(\n  ()")，2、print_r 输出轻松点，
        array_walk_recursive($arr, function (&$val) use (&$arr) {
            $val = NULL;
        });

        $ma = array();
        #从行首匹配[空白]至第一个左括号，要使用多行开关'm'
        preg_match_all("'^\(|^\s+\('m", print_r($arr, true), $ma);
        return (max(array_map('strlen', current($ma))) - 1) / 8 + 1;
    } else {
        return 0;
    }
}

/**
 * 将二维数组转为一维数组或逗号分隔的字符串
 * @author echo <chenj@nalashop.com>
 * @param array $arr 二维数组，传大于二维数组返回 false
 * @param bool $rtnstr 返回数组还是字符串
 * @return mixed
 */
function array_flat($arr, $rtnstr = true)
{
    if (array_depth($arr) == 2) {
        $ret = call_user_func_array('array_merge', array_map('array_values', $arr));
        if ($rtnstr)
            return implode(',', $ret);

        return $ret;
    }

    return false;
}