<?php

/**
 * MYSQLI 操作类
 * Author: chenj <chenj@nalashop.com>
 * Assertor: @zhaitt
 * Create: 2015-01-28 17:18
 */
class Kernel_DB_Sqli
{

    /**
     * 连接
     * @var mysqli|null
     */
    private $cn = null;

    /**
     * 缓存依赖外部初化
     * @var cache|null
     */
    private $cc = null;

    /**
     * 连接是否初始化，@todo 改成实时的
     * @var bool
     */
    private $inited = false;

    /**
     * 库名
     * @var string
     */
    private $dbname = '';

    /**
     * 异常由get_exception调取
     * @var array
     */
    private $exception = array();

    /**
     * 初始化
     * @param string $dbuser 账号
     * @param string $dbpass 密码
     * @param string $dbname 库名
     * @param string $dbchar 编码
     * @param string $dbport 端口
     * @param string $dbhost 地址
     */
    public function __construct($sqliconf)
    {
        if(empty($sqliconf)){
            $this->log('$sqliconf is empty!');
        }

        ##### 实现 try catch.  MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_STRICT 待测试
        if (mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT)) {
            try {
                $this->dbname = $sqliconf['dbname'];

                if (isset($sqliconf['dbuser']) && isset($sqliconf['dbpass']) && isset($sqliconf['dbname'])) {
                    $this->cn = new mysqli($sqliconf['dbhost'], $sqliconf['dbuser'], $sqliconf['dbpass'],$sqliconf['dbname'], $sqliconf['dbport']);
                    $this->cn->set_charset($sqliconf['dbchar']);
                    if (Service()->memc) {
                        $this->cc = Service()->memc;
//                        if ($this->cc->is_inited()) {
//                            if ($this->cache_fields()) {
//                                $this->inited = true;
//                                return;
//                            } else
//                                $this->log('cache fields failure');
//                        } else
//                            $this->log('cache init failure');
                    } else
                        $this->log('no cache');

                    $this->cn->close();
                }
            } catch (mysqli_sql_exception $e) {
                $this->log($e);
            }
        } else
            $this->log('cannot set mysqli report');
    }

    /**
     * 销毁
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * 关闭连接
     */
    public function close()
    {
        try {
            if ($this->inited) {
                $this->inited = false;
                $this->cn->close();
            }
        } catch (mysqli_sql_exception $e) {
            $this->log($e);
        }
    }

    public function ping()
    {
        if(!$this->cn->ping()){
            return false;
        }else
            return true;
    }
    /**
     * 取出 mysqli_sql_exception
     * @param string $funcname 函数名，为空则返回调用方法所在文件的所有异常
     * @return array|bool(false)
     */
    public function get_exception($funcname = null)
    {
        $file = &debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[0]['file'];
        if (!isset($file))
            return false;

        $ex = &$this->exception[$file];
        if (isset($ex)) {
            if (!empty($funcname)) {
                if (isset($ex[$funcname]))
                    return $ex[$funcname];
            } else
                return $ex;
        }

        return false;
    }

    /**
     * 判断 new sqli() 是否已成功初始化
     * @return bool
     */
    public function is_inited()
    {
        return $this->inited;
    }

    /**
     * 连接状态
     * @return bool
     */
    public function conn_stats()
    {
        return $this->cn->get_connection_stats();
    }

    /**
     * 缓存的set,get 存取cache.1个参数时 get, 2个参数时 set
     * @return array|bool
     */
    public function cache()
    {
        switch (func_num_args()) {
            case 1:
                return $this->cc->get('sqli_' . func_get_arg(0));
                break;
            case 2:
                return $this->cc->set('sqli_' . func_get_arg(0), func_get_arg(1));
                break;
            case 0:
                $this->log('no arg');
                break;
            default:
                $this->log('unknow args:' . func_num_args());
                break;
        }

        return false;
    }

    /**
     * 获取字段类型
     * @param string $table 表名
     * @param string $field 字段名
     * @return string|bool(false)
     */
    public function cache_field_type($table, $field)
    {
        if ($this->table($table))
            if (!$this->cache($table))
                return false;

        if ($this->cache($table . '.aid') == $field)
            return 'AID';
        elseif ($this->cache($table . '.key') == $field)
            return 'PRI';
        elseif (in_array($this->cache($table . '.idx'), $field))
            return 'IDX';
        elseif (in_array($this->cache($table . '.all'), $field))
            return '';
        else
            return false;
    }

    /**
     * 判断表是否存在
     * @param string $table
     * @return array|bool
     */
    public function cache_table_exist($table)
    {
        $table = $this->table($table);
        if (!$table)
            return false;

        $rtn = $this->cache($table);

        if (!$rtn)
            $this->log('table not found');

        return $rtn;
    }

    /**
     * 无参时初始化字段缓存，传表名时获取表字段
     * @return array(字段)|bool
     */
    public function cache_fields()
    {
        if (func_num_args()) {
            $tbname = '';
            $fdtype = '';
            switch (func_num_args()) {
                case 1:
                    $tbname = func_get_arg(0);
                    break;
                case 2:
                    list($tbname, $fdtype) = func_get_args();
                    break;
                default:
                    $this->log('unknow args:' . func_num_args());
                    return false;
            }

            if (empty($fdtype))
                $fdtype = '';
            else
                $fdtype = '.' . $fdtype;

            $tbname = $this->table($tbname);
            if ($tbname)
                return $this->cache($tbname . $fdtype);
        } else {
            $cachefile = __ROOT__ . 'data/field/' . $this->dbname . '.cache';

            $cachedir = dirname($cachefile);

            if (!is_dir($cachedir))
                if (!mkdir($cachedir, 0777, true)) {
                    $this->log('mkdir faild');
                    return false;
                }
            $fields = array();

            if (is_readable($cachefile) && time() - filemtime($cachefile) < 24 * 60 * 60) {
                if (!$this->cc->get($this->dbname . '.fieldcached'))
                    $fields = json_decode(file_get_contents($cachefile), true);
                else
                    return false;
            } else {
                $sql = "SELECT
							`table_name`,`column_name`,`column_key`,`extra`
						FROM
							`information_schema`.`columns`
						WHERE
							`table_schema` = '{$this->dbname}';";
                try {
                    $res = $this->cn->query($sql, MYSQLI_USE_RESULT);
                    if (is_object($res)) {
                        $rows = $res->fetch_all(MYSQLI_NUM);
                        if ($rows) {
                            foreach ($rows as $row) {
                                $fields[$row[0]] = true;
                                $fields[$row[0] . '.all'][] = $row[1];

                                if ($row[2] == 'PRI') {
                                    if ($row[3] == 'auto_increment')
                                        $fields[$row[0] . '.aid'] = true;

                                    $fields[$row[0] . '.key'] = $row[1];
                                    $fields[$row[0] . '.idx'][] = $row[1];
                                } elseif ($row[2] != '')
                                    $fields[$row[0] . '.idx'][] = $row[1];
                            }

                            file_put_contents($cachefile, json_encode($fields));
                        } else {
                            $this->log('empty result.sql:' . $sql);
                            return false;
                        }
                    } else {
                        $this->log('error query.sql:' . $sql);
                        return false;
                    }
                } catch (mysqli_sql_exception $e) {
                    $this->log($e);
                }
            }

            if ($fields) {
                foreach ($fields as $k => $v)
                    $this->cache($this->dbname . '.' . $k, $v);

                unset($fields);
                $this->cache($this->dbname . '.fieldcached', 1);
                return true;
            } else
                $this->cache($this->dbname . '.fieldcached', 0);
        }

        return false;
    }

    public function get_smart()
    {

    }

    /**
     * 取一格
     * @param string $sql
     * @return mixed|bool(false)
     */
    public function get_one_sql($sql)
    {
        try {
            if (!$this->allow_select($sql))
                return false;
            $res = $this->cn->query($sql, MYSQLI_USE_RESULT);
            if (is_object($res)) {
                $row = $res->fetch_row();
                if (!empty($row) && is_array($row))
                    return current($row);
            }
        } catch (mysqli_sql_exception $e) {
            $this->log($e);
        }
        return false;
    }

    /**
     * 取一格
     * @param string $field 一个字段
     * @param string $table 表名
     * @param string $cond 条件
     * @return mixed|bool(false)
     */
    public function get_one($field, $table, $cond)
    {
        if (!$this->where_decode($table, $cond))
            return false;

        $sql = "SELECT {$field} FROM `{$table}`{$cond} LIMIT 1";

        return $this->get_one_sql($sql);
    }

    /**
     * 取一列
     * @param string $field 字段
     * @param string $table 表名
     * @param string $cond 条件
     * @return mixed|bool(false)
     */
    public function get_column($field, $table, $cond, $by = null, $asc = false)
    {
        if (!$this->where_decode($table, $cond))
            return false;

        if (preg_match("'^[a-z_]+$'si", $field)) {
            $fields = $this->cache_fields($table);
            if (empty($fields) || !in_array($field, $fields)) {
                $this->log('$table no cache or $field wrong');
                return false;
            }

            $field = "`{$field}`";
        }

        $sql = "SELECT {$field} FROM `{$table}`{$cond}";

        if (!$this->allow_select($sql))
            return false;

        try {
            $res = $this->cn->query($sql, MYSQLI_USE_RESULT);
            if (is_object($res)) {
                $all = $res->fetch_all(MYSQLI_NUM);
                if (!empty($all))
                    return array_map('current', $all);
                else
                    $this->log('');#@todo
            } else
                $this->log('');#@todo
        } catch (mysqli_sql_exception $e) {
            $this->log($e);
        }


        return false;
    }

    /**
     * 取一列
     * @param string $sql SQL
     * @param int|string $col 列下标或字段名
     * @return array|bool(false)
     */
    public function get_column_sql($sql, $col = 0)
    {
        if (!$this->allow_select($sql))
            return false;

        try {
            $res = $this->cn->query($sql, MYSQLI_USE_RESULT);
            if (is_object($res)) {
                $arr = $res->fetch_all(is_int($col) ? MYSQLI_NUM : MYSQLI_ASSOC);
                return array_map(function ($ar) use ($col) {
                    return isset($ar[$col]) ? $ar[$col] : '';
                }, $arr);
            }
        } catch (mysqli_sql_exception $e) {
            $this->log($e);
        }
        return false;
    }

    /**
     * 取一行
     * @param string $sql
     * @param bool $assoc
     * @return array|bool(false)
     */
    public function get_row_sql($sql, $assoc = true)
    {

        if (!$this->allow_select($sql))
            return false;

        try {
            $res = $this->cn->query($sql, MYSQLI_USE_RESULT);
            if (is_object($res))
                return $assoc ? $res->fetch_assoc() : $res->fetch_row();
        } catch (mysqli_sql_exception $e) {
            $this->log($e);
        }

        return false;
    }

    /**
     * 取一行
     * @param array $fields 字段数组
     * @param string $table 一个表
     * @param string $cond 条件
     * @param bool $assoc 是否
     * @return array|bool
     */
    public function get_row($fields, $table, $cond, $assoc = true)
    {
        return $this->get_row_sql($this->row_sql($fields, $table, $cond), $assoc);
    }

    /**
     * 获取所有行（fetch_all）
     * @param string $sql
     * @param bool $liketable 数据类似表格矩阵（true 时 $assoc 设置无效）
     * @param bool $assoc 数字键名
     * @return array|bool(false)
     */
    public function get_all_sql($sql, $liketable = false, $assoc = true)
    {
        try {
            if (!$this->allow_select($sql))
                return false;

            if ($liketable) {
                $res = $this->cn->query($sql, MYSQLI_STORE_RESULT);
                if (is_object($res) && $res->num_rows > 0) {
                    $rtn = array('head' => false);
                    $rtn['head'] = array_keys($res->fetch_assoc());
                    $res->data_seek(0);
                    $rtn['body'] = $res->fetch_all(MYSQLI_NUM);
                    $res->free();
                    return $rtn;
                }
            } else {
                $res = $this->cn->query($sql, MYSQLI_USE_RESULT);
                if (is_object($res))
                    return $res->fetch_all($assoc ? MYSQLI_ASSOC : MYSQLI_NUM);
            }
        } catch (mysqli_sql_exception $e) {
            $this->log($e);
        }
        return false;
    }

    /**
     * 取一行
     * @param array|string $fields 字段数组
     * @param string $table 一个表
     * @param string $cond 条件
     * @param bool $liketable 数据类似表格矩阵（true 时 $assoc 设置无效）
     * @param bool $assoc 是否
     * @return array|bool
     */
    public function get_all($fields, $table, $cond, $liketable = false, $assoc = true)
    {
        return $this->get_all_sql($this->row_sql($fields, $table, $cond), $liketable, $assoc);
    }

    /**
     * @param $fields
     * @param $tmix
     * @param $cond
     */
    public function get_union($fields, $tmix, $cond)
    {

    }

    /**
     * array('trade'=>array(array('',''),))
     *
     * 单多表读取
     * @param array|string $fields 字段或表达式
     * @param array|string $tmix 表数组 或 table_encode 串（相同表结构）
     * @param string $cond WHERE 条件
     * @return array|bool(false)
     */
    public function get_mix($fields, $tmix, $cond)
    {
        try {
            if (!$this->where_decode($tmix, $cond))
                return false;

            if (is_array($fields))
                $fields = $this->field_decode($fields, $tmix[0]);

            if (empty($fields)) {
                $this->log('empty($fields)');
                return false;
            }

            $sql = '';

            /* if (!empty($cond))
              $cond = ' WHERE ' . $cond; */

            $tmix = $this->table_decode($tmix);

            $types = array_unique(array_map('is_int', array_keys($tmix)));
            if (count($types) == 2)
                return false;

            $noas = current($types);

            if ($noas) {
                foreach ($tmix as $table)
                    $sql .= "SELECT {$fields} FROM {$table}{$cond};";
            } else {
                $table = '';
                foreach ($tmix as $as => $t)
                    $table .= ",`{$t}` AS `{$as}`";

                $table = substr($table, 1);

                $sql .= "SELECT {$fields} FROM {$table}{$cond};";
            }

            if (!$this->allow_select($sql))
                return false;

            if ($noas && count($tmix) > 1 && $this->cn->multi_query($sql)) {
                $arr = array();
                do {
                    $res = $this->cn->store_result();
                    if (is_object($res) && $res->num_rows > 0) { 
                        $arr = array_merge_recursive($arr, $res->fetch_all());
                        $res->free();
                    }
                } while ($this->cn->more_results() && $this->cn->next_result());

                return $arr;
            } else {
                $res = $this->cn->query($sql, MYSQLI_USE_RESULT);
                if ($res)
                    return $res->fetch_all(MYSQLI_ASSOC);
            }
        } catch (mysqli_sql_exception $e) {
            $this->log($e);
        }
        return false;
    }


    /**
     * 执行多条sql
     * @param string $sql
     * @param bool $assoc
     * @return array|bool(false)
     */
    public function get_multi_sql($sql, $assoc = true)
    {
        try {
            if ($this->cn->multi_query($sql)) {
                $arr = array();
                do {
                    $res = $this->cn->store_result();
                    if (is_object($res) && $res->num_rows >= 0) {
                        $rtn = $res->fetch_all($assoc ? MYSQLI_ASSOC : MYSQLI_NUM);                     
                        $arr[] = $rtn;
                        $res->free();
                    }
                } while ($this->cn->more_results() && $this->cn->next_result());

                return $arr;
            } 
        } catch (mysqli_sql_exception $e) {
            $this->log($e);
        }
        return false;
    }

    /**
     * COUNT 表行数
     * @param array|string $tmix 表数组 或 table_encode 串
     * @param string $cond WHERE 条件
     * @return int|number
     */
    public function get_count($tmix, $cond = null)
    {
        $arr = $this->get_mix('COUNT(1)', $tmix, $cond);
        $count = count($arr);
        if ($arr && $count == 1)
            return array_sum(array_map('current', $arr));
        elseif ($count > 1) {
            return $count;
        }
        return -1;
    }

    /**
     * SUM字段和
     * @param string $field 求和单个字段
     * @param array|string $tmix 表数组 或 table_encode 串
     * @param string $cond WHERE 条件
     * @return int|number
     */
    public function get_sum($field, $tmix, $cond = null)
    {
        $arr = $this->get_mix("SUM(`{$field}`)", $tmix, $cond);
        if ($arr)
            return array_sum(array_map('current', $arr));
        return -1;
    }

    /**
     * 同表结构的表数据转移
     * @param string $tprx 两表共同前缀（为空则 from 与 to 表需要写全名）
     * @param string $from 来原表后缀
     * @param string $to 目标表后缀
     * @param string $cond WHERE 条件
     * @return bool
     */
    public function move($tprx, $from, $to, $cond, $commit = false)
    {
        try {
            $from = $tprx . $from;
            $to = $tprx . $to;

            if (!$this->where_decode($from, $cond))
                return false;

            if ($this->begin()) {
                # 表是否有自增ID
                if ($this->cache_fields($from, 'aid')) {
                    $this->log("{$from} have aid");
                    return false;
                }

                # 表字段是否相同
                if ($this->cache_fields($from, 'all') != $this->cache_fields($to, 'all')) {
                    $this->log("{$from} != {$to}");
                    return false;
                }

                $sql = "INSERT INTO `{$to}` SELECT * FROM `{$from}`  {$cond};"; #@todo chenxy
                $sql .= "DELETE FROM `{$from}`  {$cond};"; #@todo chenxy

                if ($this->cn->multi_query($sql)) {
                    if (isset($c))
                        unset($c);
                    do {
                        if (isset($c)) {
                            if ($c != $this->cn->affected_rows) {
                                $this->back();
                                return false;
                            }
                        } else
                            $c = $this->cn->affected_rows;
                    } while ($this->cn->more_results() && $this->cn->next_result());

                    if ($commit)
                        return $this->cn->commit();

                    return true;
                }
            } else
                $this->log(__LINE__ . '.no trans');
        } catch (mysqli_sql_exception $e) {
            $this->log($e);
        }
        return false;
    }

    /**
     * 插入行
     * @param string $table 插入表
     * @param array $keyval 字段=>值的数组
     * @param bool|int $commit 是否commit, false,0不判断影响行数不提交;true不判断影响行数提交;正数表式影响行数匹配提交;负数是影响行数匹配不提交
     * @return bool
     */
    public function ins_row($table, array $keyval, $commit = true)
    {
        if (!$this->cache_table_exist($table)) {
            $this->log("table:{$table} not found");
            return false;
        }

        $keys = array_keys($keyval);

        $odku = $this->make_update($keys, true);
        if (is_bool($odku))
            return false;

        $fields = $this->cache_fields($table, 'all');
        $count = count($keyval);

        if (!empty($fields) && count($fields) >= $count && count(array_diff($keys, $fields)) == 0) {
            $sql = sprintf('INSERT INTO `%s`(`%s`)VALUES(?%s)%s', $table, implode('`,`', $keys), str_repeat(',?', $count - 1), $odku);
            return $this->execute_trans($sql, array_values($keyval), $commit);
        } else
            $this->log('error:fields');

        return false;
    }

    /**
     * （多）表（多)行插入
     * @param array|string $tmix 表数组 或 table_encode 串
     * @param array $fields 字段数组
     * @param array $values 值数组，支持多行1维和多行二维（不支持非数字键名）;
     * @param bool $commit 是否提交
     * @return bool
     */
    public function ins_mul($tmix, $fields, $values, $commit = true)
    {
        $tbs = $this->table_decode($tmix);
        if (!empty($tbs)) {
            if (!empty($fields) && !empty($values)) {
                $fnum = count($fields);
                $vnum = count($values);

                $odku = $this->make_update($fields, true);
                if (is_bool($odku))
                    return false;

                if (count($values, 1) != count_more($values))
                    $values = call_user_func_array('array_merge', $values);
                else
                    $vnum /= $fnum;

                if (count($values) != $fnum * $vnum) {
                    $this->log('body\head!=0');
                    return false;
                }
                $valtpl = str_repeat('(?' . str_repeat(',?', $fnum - 1) . '),', $vnum);

                try {
                    if ($this->begin()) {
                        foreach ($tbs as $tbname) {
                            $sql = sprintf('INSERT INTO `%s`(`%s`)VALUES%s%s', $tbname, implode('`,`', $fields), rtrim($valtpl, ','), $odku);
                            $rt = $this->execute($sql, $values);
                            if ($odku == '' && $rt[0] < $vnum) {
                                $this->back();
                                $this->log('insrow!=srcrow');
                                return false;
                            }
                        }
                        if ($commit)
                            return $this->cn->commit();
                        return true;
                    } else
                        $this->log('no trans');
                } catch (mysqli_sql_exception $e) {
                    $this->back();
                    $this->log($e);
                }
            } else
                $this->log('$fields||$values empty');
        } else
            $this->log('$tables error:' . print_r($tmix, true));
        return false;
    }

    /**
     * 删除（多）行
     * @param string $table 表名
     * @param string $cond WHERE 条件
     * @param bool|int $commit 是否commit, false,0不判断影响行数不提交;true不判断影响行数提交;正数表式影响行数匹配提交;负数是影响行数匹配不提交
     * @return bool
     */
    public function del($table, $cond, $commit = 1)
    {
        try {
            if (!$this->where_decode($table, $cond))
                return false;

            if ($this->begin()) {
                if ($this->cn->real_query(sprintf('DELETE FROM `%s` %s', $table, $cond))) {
                    if (is_bool($commit) || $commit == 0) {
                        if ($this->cn->affected_rows > 100) {
                            $this->back();
                            $this->log('del all rows?');
                        } else {
                            if ($commit)
                                return $this->commit();
                            return true;
                        }
                    } else {
                        if ($this->cn->affected_rows == abs($commit)) {
                            if ($commit > 0)
                                return $this->commit();
                            return true;
                        } else {
                            $this->back();
                            $this->log('affected_rows!=' . $commit);
                        }
                    }
                } else
                    $this->log('failed to delete');
            } else
                $this->log('no trans');
        } catch (mysqli_sql_exception $e) {
            $this->back();
            $this->log($e);
        }
        return false;
    }

    /**
     * (多)表记录删除
     * @param array|string $tmix 表数组 或 table_encode 串
     * @param string $cond WHERE 条件
     * @param bool|int $commit 是否commit, false,0不判断影响行数不提交;true不判断影响行数提交;正数表式影响行数匹配提交;负数是影响行数匹配不提交
     * @return bool
     */
    public function del_mul($tmix, $cond, $commit = true)
    {
        $tarr = $this->table_decode($tmix);

        if (empty($tarr))
            return false;

        $sql = vsprintf(str_repeat('DELETE FROM `%s` ' . $cond . ';', count($tarr)), $tarr);

        try {
            if ($this->begin()) {
                if ($this->cn->multi_query($sql)) {
                    if (is_int($commit) && $commit != 0) {
                        $c = abs($commit);
                        do {
                            if (isset($c)) {
                                if ($c != $this->cn->affected_rows) {
                                    $this->back();
                                    $this->log('affected_rows!=' . $commit);
                                    return false;
                                }
                            } else
                                $c = $this->cn->affected_rows;
                        } while ($this->cn->more_results() && $this->cn->next_result());
                    }

                    if ((is_bool($commit) && $commit) || $commit > 0)
                        return $this->commit();

                    return true;
                }
            } else
                $this->log('no trans');
        } catch (mysqli_sql_exception $e) {
            $this->back();
            $this->log($e);
        }

        return false;
    }

    /**
     * 更新
     * @param string $table 表名
     * @param array $keyval 字段=>值的数组
     * @param string $cond 条件
     * @param bool|int $commit 是否commit, false,0不判断影响行数不提交;true不判断影响行数提交;正数表式影响行数匹配提交;负数是影响行数匹配不提交
     * @return array|bool
     */
    public function upd($table, array $keyval, $cond, $commit = true)
    {
        if (!$this->where_decode($table, $cond))
            return false;

        if (!$this->cache_table_exist($table)) {
            $this->log("table:{$table} not found");
            return false;
        }

        $aid = $this->cache_fields($table, 'aid');

        if ($aid && isset($keyval[$aid])) {
            $this->log('update aid?');
            return false;
        }

        $count = count($keyval);
        $keys = array_keys($keyval);

        $set = $this->make_update($keys, false);
        if (is_bool($set))
            return false;

        $fields = $this->cache_fields($table, 'all');

        if (!empty($fields) && count($fields) >= $count && count(array_diff($keys, $fields)) == 0) {
            $sql = sprintf('UPDATE `%s` SET %s %s', $table, $set, $cond);

            return $this->execute_trans($sql, $keyval, $commit);
        } else
            $this->log('error:fields');

        return false;
    }

    /**
     * 字段值反转
     * @param string $table 表名
     * @param string $cond 条件
     * @param string $field 需要反转的字段
     * @param bool|int $commit 是否commit, false,0不判断影响行数不提交;true不判断影响行数提交;正数表式影响行数匹配提交;负数是影响行数匹配不提交
     * @param array $boolmap 反转映射
     * @return bool
     */
    public function upd_flip($table, $cond, $field, $commit = true, $boolmap = array(true => 1, false => 0))
    {
        if (!$this->where_decode($table, $cond))
            return false;

        try {
            if ($this->begin()) {
                $sql = "UPDATE `{$table}` SET `{$field}` = (IF(`{$field}` = {$boolmap[true]}, {$boolmap[false]}, {$boolmap[true]})) {$cond}";
                if ($this->cn->real_query($sql)) {
                    if (is_int($commit) && $commit != 0) {
                        $num = $this->cn->affected_rows;
                        if ($num != abs($commit)) {
                            $this->back();
                            $this->log('affected_rows!=' . $commit);
                            return false;
                        }
                    }

                    if ((is_bool($commit) && $commit) || $commit > 0)
                        $this->commit();

                    return true;
                } else
                    $this->log('failed to update');
            }
        } catch (mysqli_sql_exception $e) {
            $this->back();
            $this->log($e);
        }

        return false;
    }

    /* 	'TABLE_CATALOG'
      'TABLE_SCHEMA'
      'TABLE_NAME'
      'COLUMN_NAME'
      'ORDINAL_POSITION'
      'COLUMN_DEFAULT'
      'IS_NULLABLE'
      'DATA_TYPE'
      'CHARACTER_MAXIMUM_LENGTH'
      'CHARACTER_OCTET_LENGTH'
      'NUMERIC_PRECISION'
      'NUMERIC_SCALE'
      'CHARACTER_SET_NAME'
      'COLLATION_NAME'
      'COLUMN_TYPE'
      'COLUMN_KEY'
      'EXTRA'
      'PRIVILEGES'
      'COLUMN_COMMENT' */

    /**
     * 获取表字段注释
     * @param int $type 0:仅取字段注释,1:仅取表注释,2:两者
     * @param array|string $tmix 表数组 或 table_encode 串
     * @param string $colname 字段名
     * @return array|string|bool(false)
     */
    public function get_comment($type, $tmix, $colname = null)
    {
        $tarr = $this->table_decode($tmix);
        if (empty($tarr))
            return false;

        $sqlarr = array(
            'tm' => "SELECT `table_name` AS n,`table_comment` AS c FROM `information_schema`.`tables` WHERE `table_schema` = '{$this->dbname}' AND `table_name`",
            'ca' => "SELECT `column_name` AS n,`column_comment` AS c FROM `information_schema`.`columns` WHERE `table_schema` = '{$this->dbname}' AND `table_name`",
            'co' => "SELECT `column_name` AS n,`column_comment` AS c FROM `information_schema`.`columns` WHERE `table_schema` = '{$this->dbname}' AND `table_name` = '%s' AND `column_name` = '%s'"
        );

        $tcnt = count($tarr);

        if (is_int($type)) {
            switch ($type) {
                case 0:
                    if ($tcnt > 1)
                        return false;
                    if (empty($colname))
                        $sqlarr = array('ca' => $sqlarr['ca'] . "='{$tarr[0]}'");
                    else
                        $sqlarr = array('co' => sprintf($sqlarr['co'], $tarr[0], $colname));
                    break;
                case 1:
                    if ($tcnt == 1)
                        $sqlarr = array('tm' => "{$sqlarr['tm']}='{$tarr[0]}'");
                    else
                        $sqlarr = array('tm' => $sqlarr['tm'] . sprintf(" IN('%s')", implode("','", $tarr)));
                    break;
                case 2:
                    if ($tcnt == 1 && !empty($colname)) {
                        $sqlarr['tm'] .= "='{$tarr[0]}'";
                        $sqlarr['co'] = sprintf($sqlarr['co'], $tarr[0], $colname);
                        unset($sqlarr['ca']);
                    } elseif ($tcnt == 1 && empty($colname)) {
                        $ts = "='{$tarr[0]}'";
                        $sqlarr['tm'] .= $ts;
                        $sqlarr['ca'] .= $ts;
                        unset($sqlarr['co']);
                    } else
                        return false;
                    break;
            }
        }

        $rtn = array();
        try {
            foreach ($sqlarr as $key => &$sql) {
                $res = $this->cn->query($sql, MYSQLI_USE_RESULT);
                if ($res) {
                    $rows = $res->fetch_all(MYSQLI_ASSOC);
                    if (!empty($rows)) {
                        if (count($rows) == 1) {
                            if (count($sqlarr) == 1)
                                return $rows[0]['c'];
                            else
                                $rtn[$key{0} == 't' ? 'table' : 'column'] = array($rows[0]['n'] => $rows[0]['c']);
                        } else {
                            array_unshift($rows, null);
                            $rows = call_user_func_array('array_map', $rows);
                            if (!empty($rows) && count($rows) == 2) {
                                $sql = call_user_func_array('array_combine', $rows);
                                if (count($sqlarr) == 1)
                                    return $sql;
                                else
                                    $rtn[$key{0} == 't' ? 'table' : 'column'] = $sql;
                            }
                        }
                    }
                }
            }
        } catch (mysqli_sql_exception $e) {
            $this->log($e);
        }

        unset($sqlarr);

        if (!empty($rtn))
            return $rtn;

        return false;
    }

    /**
     * 判断表存不存在
     * @param string $table 表名
     * @return bool
     */
    public function table_exist($table)
    {
        if (is_array($table))
            return false;

        $table = str_replace(array('`', ' '), '', $table);
        if (substr_count($table, '.') == 1) {
            list($dbname, $table) = explode('.', $table);

            $sql = "SELECT `table_name` FROM `information_schema`.`tables` WHERE `table_schema` = '{$dbname}' AND `table_name` = '{$table}'";
            try {
                $res = $this->cn->query($sql, MYSQLI_USE_RESULT);
                if (is_object($res)) {
                    $row = $res->fetch_row();
                    return !empty($row);
                }
            } catch (mysqli_sql_exception $e) {
                $this->log($e);
            }
        } else
            $this->log('need full tablename');

        return false;
    }

    /**
     * 预处理绑定参数并执行
     * @param string $presql 预处理
     * @param array $arg 预处理参数
     * @return array|bool(false)
     */
    public function execute($presql, array $arg)
    {
        if (empty($presql)) {
            $this->log('empty($presql)');
            return false;
        }

        if (empty($arg)) {
            $this->log('empty($arg)');
            return false;
        }

        $arg = array_values($arg);
        array_unshift($arg, str_repeat('s', count($arg)));
        $stm = $this->cn->prepare($presql);
        if (call_user_func_array(array($stm, 'bind_param'), $this->refval($arg))) {
            $stm->execute();
            $rows = $stm->affected_rows;
            $aid = 0;
            if ($rows == 1 && strtolower(substr($presql, 0, 6)) == 'insert' && isset($stm->insert_id))
                $aid = $stm->insert_id;
            $stm->close();
            return array($rows, $aid);
        } else
            $this->log('error bind_param');

        return false;
    }

    /**
     * 事务中预处理绑定参数并执行
     * @param string $presql 预处理
     * @param array $arg 预处理参数
     * @param bool $commit 是否commit
     * @return array|bool(false)
     */
    public function execute_trans($presql, array $arg, $commit)
    {
        try {
            if ($this->begin()) {
                $rt = $this->execute($presql, $arg);
                if ($rt && $this->rows_commit($commit, $rt[0]))
                    return $rt;
                return $rt;
            } else
                $this->log('no trans');
        } catch (mysqli_sql_exception $e) {
            $this->back();
            $this->log($e);
        }
        return false;
    }

    /**
     * 匹行提交
     * @param bool|int $commit 是否commit, false,0不判断影响行数不提交;true不判断影响行数提交;正数表式影响行数匹配提交;负数是影响行数匹配不提交
     * @param int $rows 影响行数
     * @return bool
     */
    public function rows_commit($commit, $rows)
    {
        try {
            if (is_bool($commit) || $commit == 0)
                return $commit ? $this->cn->commit() : true;

            if (is_int($commit)) {
                if (abs($commit) == $rows) {
                    if ($commit > 0)
                        return $this->cn->commit();
                    return true;
                } else
                    $this->log('affected_rows!=' . $commit);
            }
        } catch (mysqli_sql_exception $e) {
            $this->back();
            $this->log($e);
        }
        return false;
    }

    /**
     * 判断是否开启了事务
     * @return bool|int
     */
    public function intrans()
    {
        try {
            if ($res = $this->cn->query('SELECT @@autocommit', MYSQLI_USE_RESULT)) {
                $row = $res->fetch_row();
                if (is_array($row) && count($row) == 1)
                    return current($row) == 0;
            }
        } catch (mysqli_sql_exception $e) {
            $this->log($e);
        }

        return 0;
    }

    /**
     * 开启事务
     * @return bool
     */
    public function begin()
    {
        try {
            if (!$this->cn->autocommit(false))
                return false;

            $res = $this->cn->query('SELECT @@autocommit', MYSQLI_USE_RESULT);
            if (is_object($res)) {
                $row = $res->fetch_row();
                if (is_array($row) && count($row) == 1)
                    return current($row) == 0;
            }
        } catch (mysqli_sql_exception $e) {
            $this->log($e);
        }
        return false;
    }

    /**
     * 回滚事务
     * @return bool
     */
    public function back()
    {
        try {
            return $this->cn->rollback();
        } catch (mysqli_sql_exception $e) {
            $this->log($e);
        }
        return false;
    }

    /**
     * 提交事务
     * @return bool
     */
    public function commit()
    {
        // echo(1332);exit;
        try {
            return $this->cn->commit();
        } catch (mysqli_sql_exception $e) {
            $this->back();
            $this->log($e);
        }
        return false;
    }

    /**
     * 打日志
     * 单参数：对象与字符串
     * 多参数：格式要求与 sprintf 相同
     */
    public function log()
    {
        $log = null;

        $parc = func_num_args();

        switch ($parc) {
            case 0:
                break;
            case 1:
                $log = func_get_arg(0);
                break;
            default:
                $fmt = func_get_arg(0);
                $parv = func_get_args();
                if (is_string($fmt) && !empty($fmt) && substr_count($fmt, '%') >= $parc - 1)
                    $log = call_user_func_array('sprintf', $parv);

                if ($log == null || is_bool($log))
                    $log = implode(',', $parv);

                if (!$log)
                    $log = var_export($parv, true);
                break;
        }

        if ($log != null && !is_string($log)) {
            if (is_object($log) && get_class($log) == 'mysqli_sql_exception') {
                $trcs = $log->getTrace();
                if (!empty($trcs)) {
                    foreach ($trcs as $trc) {
                        if ($trc['file'] != __FILE__ && isset($trc['class']) && $trc['class'] == __CLASS__) {
                            $ex = &$this->exception[$trc['file']];
                            if (!isset($ex))
                                $ex = array();

                            $fn = &$ex[$trc['function']];
                            if (!isset($fn))
                                $fn = array();

                            $fn[$trc['line']] = array('line' => $log->getLine(), 'errno' => $log->getCode(), 'error' => $log->getMessage());
                        }
                    }
                }

                $log = $log->getLine() . ',' . $log->getCode() . ':' . $log->getMessage();
            } else
                $log = str_replace(array("\n", ' '), '', print_r($log, true));
        }

        throw new Kernel_Exception_InternalServerError(
            T('Sqli is wrong  msg:{msg}', array('msg' => $log))
        );
    }

    /**
     * 字段解析
     * @example CASE `status` WHEN 0 THEN '启用' WHEN 1 THEN '关闭' END => status?0:启用,1:关闭
     * @example FROM_UNIXTIME(`create`, '%Y-%m-%d %H:%i:%S')           => create:datetime
     * @example FROM_UNIXTIME(`create`, '%Y-%m-%d')                    => create:date
     * @example FROM_UNIXTIME(`create`, '%H:%i:%S')                    => create:time
     * @example FROM_UNIXTIME(`create`, '%H:%i:%S')                    => create:%YmdHis 自定义以第一个字符为%号作标记
     * @example COUNT(*)                                               => *:count
     * @example SUM(`num`)                                             => num:sum
     *
     * @param array $fields 字段数组,key:别名,value:字段或简写表达式
     * @param string $table 表名，empty时表式不验证字段有效性
     * @return string|bool(false)
     */
    public function field_decode(array $fields, $table = null)
    {
        $ss = '';

        if (empty($fields)) {
            $this->log('empty($fields)');
            return false;
        }

        if (!empty($table))
            $farr = $this->cache_fields($table, 'all');

        if (isset($farr) && empty($farr)) {
            $this->log("no {$table} fields cache");
            return false;
        }

        foreach ($fields as $nk => $ve) {
            if (preg_match("'^[a-z_]+$'si", $ve)) {
                $ss .= "`{$ve}`";
            } elseif (preg_match("'^([a-z_]+)\?(.*)$'si", $ve, $mm)) {
                if (isset($farr) && !in_array($mm[1], $farr)) {
                    $this->log("field {$mm[1]} not found");
                    return false;
                }
                $cnt = preg_match_all("'([^\:]+)\:([^\,]+)(\,|)'", $mm[2], $ms);
                if ($cnt > 1 && $cnt == count($ms[1])) {
                    $ss .= "CASE `{$mm[1]}` ";
                    $sp = count(array_unique(array_map('is_numeric', $ms[1]))) == 1 ? '' : "'";
                    for ($i = 0; $i < $cnt; $i++)
                        $ss .= "WHEN {$sp}{$ms[1][$i]}{$sp} THEN '{$ms[2][$i]}' ";

                    $ss .= 'END';
                } else {
                    $this->log('unknow format:' . $ve);
                    return false;
                }
            } elseif (substr_count($ve, ':') == 1) {
                list($kk, $fn) = explode(':', $ve, 2);
                $fn = strtoupper($fn);
                switch ($fn) {
                    case 'DATETIME':
                    case 'DATE':
                    case 'TIME':
                        if (isset($farr) && !in_array($kk, $farr)) {
                            $this->log('field not found:' . $kk);
                            return false;
                        }
                        $ss .= "IF(`{$kk}`=0, '', FROM_UNIXTIME(`{$kk}`, '{$this->time_format[$fn]}'))";
                        break;
                    case 'COUNT':
                        if ($kk == '1' || $kk == '*')
                            $ss .= "{$fn}($kk)";
                        elseif (!isset($farr) || in_array($kk, $farr))
                            $ss .= "{$fn}(`$kk`)";
                        else {
                            $this->log('field not found:' . $kk);
                            return false;
                        }
                        break;
                    case 'SUM':
                    case 'AVG':
                    case 'MAX':
                    case 'MIN':
                        if (isset($farr) && !in_array($kk, $farr)) {
                            $this->log('field not found:' . $kk);
                            return false;
                        }

                        $ss .= "{$fn}(`{$kk}`)";
                        break;
                    default:
                        if ($fn{0} == '%' && strlen($fn) > 1) {
                            $fmt = str_replace($this->php_format, $this->sql_format, substr($fn, 1), $c);
                            if ($c > 0) {
                                $ss .= "IF(`{$kk}`=0, '', FROM_UNIXTIME(`{$kk}`, '{$fmt}'))";
                                break;
                            }
                        }
                        $this->log('not support:' . $fn);
                        return false;
                }
            } else {
                $this->log('not support:' . $ve);
                return false;
            }

            if (!is_int($nk))
                $ss .= " AS {$nk}, ";
            else
                $ss .= ', ';
        }

        return rtrim($ss, ' ,');
    }

    /**
     * 表编码串 返回格式：表前缀:表后缀1 别名1,表后缀2 别名2,表后缀n 别名n
     * @param array $tarr 表数组
     * @param bool $checktable 检查表
     * @return string
     */
    public function table_encode(array $tarr, $checktable = false)
    {
        if (is_array($tarr) && !empty($tarr)) {

            if ($checktable) {
                foreach ($tarr as $table)
                    if ($this->cache_table_exist($table)) {
                        $this->log("{$table} not exist");
                        return false;
                    }
            }

            $prx = '';
            $pos = 0;
            $idx = 0;
            $brk = false;

            $has = false;

            while (true) {
                $c = '';
                foreach ($tarr as $as => &$suf) {
                    if (!$has && !is_int($as))
                        $has = true;

                    if (isset($suf{$pos})) {
                        if ($c == '')
                            $c = $suf{$pos};
                        elseif ($c != $suf{$pos}) {
                            if ($idx == 0)
                                break 2;
                            else
                                $brk = true;
                        }
                    } else
                        $brk = true;

                    if ($idx > 0)
                        $suf = substr($suf, 1);
                }

                if ($brk)
                    break;

                $prx .= $c;

                if ($idx == 0)
                    $pos = 1;

                $idx++;
            }

            if ($has) {
                $aarr = array_map(function ($v) {
                    return is_int($v) ? '' : ' ' . $v;
                }, array_keys($tarr));

                $tarr = array_map(null, array_values($tarr), $aarr);
                $tarr = array_map('implode', $tarr);
            }

            if (empty($prx))
                return implode(',', $tarr);
            else
                return $prx . ':' . implode(',', $tarr);
        }

        return '';
    }

    /**
     * 返回表数组 table_encode 的反函数
     * @param array|string $tmix 表数组 或 table_encode 串
     * @param bool $existcheck 判断表是否存在
     * @return array|bool
     */
    public function table_decode($tmix, $existcheck = false)
    {
        if (is_array($tmix)) {
            if ($existcheck) {
                foreach ($tmix as $table) {
                    if (!$this->cache_table_exist($table)) {
                        $this->log("{$table} not exist");
                        return false;
                    }
                }
            }
            return $tmix;
        }

        $prx = '';
        $sufs = '';
        if (substr_count($tmix, ':'))
            list($prx, $sufs) = explode(':', $tmix, 2);

        if (strpos($tmix, ','))
            $sufs = explode(',', $sufs);
        else
            $sufs = array($tmix);

        $tarr = array();

        foreach ($sufs as $suf) {
            $as = '';
            if (strpos($suf, ' ') !== false)
                list($suf, $as) = explode(' ', $suf, 2);

            if (!$existcheck || $this->cache_table_exist($prx . $suf))
                if (empty($as))
                    $tarr[] = $prx . $suf;
                else
                    $tarr[$as] = $prx . $suf;
            else {
                $this->log("{$tmix}:{$prx}{$suf} not exist");
                return false;
            }
        }

        return $tarr;
    }

    /**
     * 查询性能判断
     * @param string $sql select 的 SQL
     * @return bool
     */
    public function allow_select($sql)
    {
        return true;
        try {
            if (substr(ltrim(strtolower($sql)), 0, 6) != 'select') {
                $this->log('not select query');
                return false;
            }

            if (preg_match("'`information_schema`\.`[^`]+`'si", $sql))
                return true;

            $res = $this->cn->query('EXPLAIN ' . $sql, MYSQLI_USE_RESULT);
            if ($res) {
                $rows = $res->fetch_all(MYSQLI_ASSOC);
                if ($rows) {
                    foreach ($rows as $row) {
                        if (strtolower($row['select_type']) != 'union result' && !empty($row['type']) && strtolower($row['type']) == 'all') {
                            $this->log("sql:{$sql}\n" . implode("\n", array_map(function ($v) {
                                    return implode("\t", $v);
                                }, $rows)));
                            return false;
                        }
                    }
                }
            }
            return true;
        } catch (mysqli_sql_exception $e) {
            $this->log($e);
        }
        return false;
    }

    /**
     * WERER 表达式解码
     * @remark 若字段是主键可省略字段
     * =, <, >, <>
     * @example WHERE `id`=10;                   => id:10
     * @example WHERE `id`<10;                   => id<10
     * @example WHERE `id`>10;                   => id>10
     * @example WHERE `id`<>10;                  => id!10
     * IN, NOT IN
     * @example WHERE `id` NOT IN(10,20,30,40)   => id!10,20,30,40
     * @example WHERE `status` IN('prt','pick')  => status:prt,pick
     * @example WHERE `id` IN(10,20,30,40);      => id:10,20,30,40
     * BETWEEN AND
     * @example WHERE `id` BETWEEN 1 AND 100;    => id:1-100
     * @example WHERE `id` NOT BETWEEN 1 AND 100;=> id!1-100
     * 字符串
     * @example WHERE `id`='1-100';              => id:#1-100
     * @example WHERE `id`='10,20,30,40';        => id:#10,20,30,40
     * @example WHERE `id`<>'10,20,30,40';       => id!#10,20,30,40
     *
     * @param string $table 表名
     * @param string $cond
     * @param bool $padwhere 是否添加 WHERE
     * @param bool $checkindex 检查是否有索引
     * @return string|bool(false)
     */
    public function where_decode($table, &$cond, $padwhere = true, $checkindex = true)
    {
        if (empty($cond)) {
            $this->log('empty($cond)');
            return false;
        }

        if (strpos($cond, '`') !== false || strpos($cond, "'")) {
            $cond = ($padwhere ? ' WHERE ' : '') . $cond;
            return true;
        }

        $sep = '';
        if ($cond{0} == '#') { ## 无字段名表达式值为字符串 status:#prt status 是主键又是等于 #prt
            $sep = ':';
            $value = $cond;
        } elseif (in_array($cond{0}, $this->wopr_arr)) { ## 首字符为在符号组内
            $sep = $cond{0};
            $value = substr($cond, 1);
        } else {
            $pa = preg_split("'(:|!|<|>)'", $cond, 2);
            if (count($pa) == 2) { #
                list($field, $value) = $pa;
                $sep = substr($cond, strlen($field), 1);
            } else {
                $sep = ':';
                if (preg_match("'^([1-9]\d*|0)(\.\d*|)$'", $pa[0]))
                    $value = $pa[0];
                else {
                    $this->log('unknow format $cond');
                    return false;
                }
            }
        }

        if (!isset($field)) {
            $key = $this->cache_fields($table, 'key');
            if (empty($key)) {
                $this->log('nokey:' . $table);
                return false;
            }
            $field = $key;
        }

        if ($checkindex) {
            $idx = $this->cache_fields($table, 'idx');
            if (!in_array($field, $idx)) {
                $this->log('noidx:' . $field . '@' . $table);
                return false;
            }
        }

        $where = '';

        switch ($sep) {
            case ':':
            case '!':
                if ($value{0} == '#') {
                    $sep = $sep == ':' ? '=' : '<>';
                    $value = substr($value, 1);
                    $where = "`{$field}`{$sep}'{$value}'";
                } else {
                    if (substr_count($value, ',')) {
                        $sep = $sep == '!' ? ' NOT' : '';
                        if (is_numeric(str_replace(',', '', $value)))
                            $where = "`{$field}`{$sep} IN({$value})";
                        else {
                            $value = str_replace(',', "','", $value);
                            $where = "`{$field}`{$sep} IN('{$value}')";
                        }
                    } elseif (substr_count($value, '-') == 1) {
                        $sep = $sep == '!' ? ' NOT' : '';
                        list($min, $max) = explode('-', $value, 2);
                        $where = "`{$field}`{$sep} BETWEEN {$min} AND {$max}";
                    } else {
                        $sep = $sep == ':' ? '=' : '<>';
                        if (is_numeric($value))
                            $where = "`{$field}`{$sep}{$value}";
                        else
                            $where = "`{$field}`{$sep}'{$value}'";
                    }
                }
                break;
            case '<':
            case '>':
                if (is_numeric($value))
                    $where = "`{$field}`{$sep}{$value}";
                break;
            default:
                $this->log('unknow sep:' . $sep, 'error');
                return false;
        }

        if (empty($where)) {
            $this->log('error decode', 'error');
            return false;
        }

        $cond = ($padwhere ? ' WHERE ' : '') . $where;

        return true;
    }

    /**
     * @param $table
     * @return bool|string
     */
    public function table($table)
    {
        if (empty($table))
            return false;

        if (is_array($table)) {
            return array_map(array(get_called_class(), __FUNCTION__), $table);
        } else {
            $table = str_replace(array('`', ' '), '', $table);

            switch (substr_count($table, '.')) {
                case 0:
                    $table = $this->dbname . '.' . $table;
                    break;
                case 1:
                    list($sch, $table) = explode('.', $table, 2);
                    if ($sch != $this->dbname)
                        $this->log('in other db');
                    break;
                default:
                    $this->log('unknow table:' . $table);
                    return false;
            }
            return $table;
        }
    }

    ######################## private private private ########################

    /**
     * where_decode 符号组
     * @var array
     */
    private $wopr_arr = array(':', '!', '<', '>');

    /**
     * ON DUPLICATE KEY UPDATE 运算符
     * 单向用法
     *
     * 覆盖
     * '=field' -> `field` = VALUES(`field`);
     * 加
     * '+field' -> `field` = `field` + VALUES(`field`);
     * 乘
     * '*field' -> `field` = `field` * VALUES(`field`);
     *
     * 双向用法
     *
     * 后连
     * '.field' -> `field` = CONCAT(`field`, VALUES(`field`))
     * 前连
     * 'field.' -> `field` = CONCAT(VALUES(`field`), `field`)
     *
     * 被减
     * '-field' -> `field` = `field` - VALUES(`field`)
     * 减
     * 'field-' -> `field` = VALUES(`field`) - `field`
     *
     * 被除
     * '/field' -> `field` = `field` / VALUES(`field`)
     * 除
     * 'field/' -> `field` = VALUES(`field`) / `field`
     *
     * 被取余
     * '%field' -> `field` = `field` % VALUES(`field`)
     * 取余
     * 'field%' -> `field` = VALUES(`field`) % `field`
     *
     * 被整除
     * '\field' -> `field` = `field` DIV VALUES(`field`)
     * 整除
     * 'field\' -> `field` = VALUES(`field`) DIV `field`
     *
     * @var array
     */
    private $uopr_arr = array('=', '+', '*', '.', '-', '/', '%', '\\');

    /**
     * FROM_UNIXTIME 格式串
     * @var array
     */
    private $time_format = array('DATETIME' => '%Y-%m-%d %H:%i:%S', 'DATE' => '%Y-%m-%d', 'TIME' => '%T');

    # php 与 mysql 可对应部分
    /**
     * PHP date 格式符
     * @var array
     */
    private $php_format = array('u', 's', 'i', 'H', 'h', 'G', 'g', 'A', 'l', 'D', 'd', 'j', 'S', 'w', 'z', 'W', 'F', 'M', 'm', 'n', 'Y', 'y', '%');

    /**
     * FROM_UNIXTIME 格式符
     * @var array
     */
    private $sql_format = array('f', 's', 'i', 'H', 'h', 'k', 'l', 'p', 'W', 'a', 'd', 'e', 'D', 'w', 'j', 'u', 'M', 'b', 'm', 'c', 'Y', 'y', '%%');

    /**
     *
     * @param array $arr
     * @return array
     */
    private function refval($arr)
    {
        if (strnatcmp(phpversion(), '5.3') >= 0) {
            $refs = array();
            foreach ($arr as $key => $value)
                $refs[$key] = &$arr[$key];
            return $refs;
        }
        return $arr;
    }

    /**
     * 组装 SELECT 单多行的SQL
     * @param $fields
     * @param $table
     * @param $cond
     * @return bool|string
     */
    private function row_sql($fields, $table, $cond)
    {
        if (!$this->where_decode($table, $cond))
            return false;

        if (is_string($fields) && substr_count($fields, ','))
            $fields = explode(',', $fields);

        if (is_array($fields))
            $fields = $this->field_decode($fields, $table);

        if (empty($fields))
            return false;

        $sql = "SELECT {$fields} FROM `{$table}`{$cond}";

        $fn = &debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        if (isset($fn))
            switch ($fn) {
                case 'get_all':
                    break;
                case 'get_row':
                    $sql .= ' LIMIT 1';
                    break;
            }

        return $sql;
    }

    /**
     * 组装 UPDATE 或 ON DUPLICATE KEY UPDATE
     * @param array $keys
     * @param bool $odku
     * @return bool(false)|string
     */
    private function make_update(array &$keys, $odku)
    {
        $ret = '';
        $aft = false;

        foreach ($keys as &$key) {
            $key = trim($key);
            $opr = $key{0};
            if (!in_array($opr, $this->uopr_arr)) {
                $opr = substr($key, -1);
                if (!in_array($opr, $this->uopr_arr) && $odku)
                    continue;

                if ($odku) {
                    $key = strlen($key, 0, -1);
                    $aft = true;
                } else
                    $opr = '=';
            } else
                $key = substr($key, 1);

            if (!preg_match("'^[\w_]+$'", $key))
                return false;

            $flg = $odku ? "VALUES(`{$key}`)" : '?';

            switch ($opr) {
                case '.':
                    if ($aft)
                        $ret .= ",`{$key}`=CONCAT({$flg},`{$key}`)";
                    else
                        $ret .= ",`{$key}`=CONCAT(`{$key}`,{$flg})";
                    break;
                case '=':
                    $ret .= ",`{$key}`={$flg}";
                    break;
                case '+':
                case '*':
                    $ret .= ",`{$key}`=`{$key}`{$opr}{$flg}";
                    break;
                case '-':
                case '/':
                case '%':
                    if ($aft)
                        $ret .= ",`{$key}`={$flg}{$opr}`{$key}`";
                    else
                        $ret .= ",`{$key}`=`{$key}`{$opr}{$flg}";
                    break;
                case '\\':
                    if ($aft)
                        $ret .= ",`{$key}`={$flg} DIV `{$key}`";
                    else
                        $ret .= ",`{$key}`=`{$key}` DIV {$flg}";
                    break;
            }
        }

        if (!empty($ret)) {
            $ret = substr($ret, 1);
            if ($odku)
                $ret = ' ON DUPLICATE KEY UPDATE ' . $ret;
        } elseif (!$odku)
            return false;

        return $ret;
    }

    /**
     * @param $sql
     * @param bool $commit
     * @return bool|mysqli_result
     * 直接执行SQL；
     * 创建表，不支持事务， 解决办法：使用try{}catch{}的方式将上面的动作“包”起来，一旦出现了异常，就catch捕获，然后在catch中删除表，删除数据等操作，“模拟回滚”的效果。
     */
    public function query_sql($sql, $commit = true)
    {
        if (!$sql) {
            return false;
        }
        try {
            if ($this->begin()) {
                $res = $this->cn->query($sql);

                if ((is_bool($commit) && $commit) || $commit > 0)
                    $this->commit();

                return $res;
            } else {

                return false;
            }

        } catch (mysqli_sql_exception $e) {
            $this->log($e);
            return false;
        }

    }

}
