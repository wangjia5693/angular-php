<?php
/**
 *
 * Author: chenj <chenj@nalashop.com>
 * Assertor: @zhaitt
 * Create: 2015/12/16 13:03
 */
if (!defined('__ROOT__'))
    die();

if (!defined('__ROOTLOG__'))
    define('__ROOTLOG__', __ROOT__ . '/');

if (!defined('__WHOAMI__'))
    define('__WHOAMI__', `whoami`);

/**
 * @param $errno
 * @param $errstr
 * @param $errfile
 * @param $errline
 * @return bool
 * @throws ErrorException
 */
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if ($errfile == __FILE__)
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    return false;
});

class Pg
{
    /**
     * -- abbr --
     * resource res
     * result   rs
     * row      r
     * column   col     c
     * return   rtn     rt
     *
     * 从 0 开始 索引 下标
     * 从 1 开始 序号
     */

    /**
     * @var null|resource(pgsql link)
     */
    private $cn = null;

    /**
     * 连接串
     * @var string
     */
    private $cs = '';

    /**
     * 表字段序列名关联
     * @var array
     */
    private $_seqs = array();

    /**
     * pgsql constructor.
     * @param $dbuser
     * @param $dbpass
     * @param $dbname
     * @param string $dbchar
     * @param int $dbport
     * @param string $dbhost
     * @param int $pconn 是否长连。0表示短连，1表示复用长连，2或其它值表示新建长连
     */
    public function __construct($dbuser, $dbpass, $dbname, $dbchar = 'utf8', $dbport = 5432, $dbhost = 'localhost', $pconn = 0)
    {
        if (version_compare(PHP_VERSION, '5.4.4', '<'))
            die('php version >= 5.4.4');
//        var_dump(ini_get('pgsql.allow_persistent'));
//        echo '<br>pgsql.allow_persistent: ' . ini_get('pgsql.allow_persistent');
//        echo '<br>pgsql.max_persistent: ' . ini_get('pgsql.max_persistent');
//        echo '<br>pgsql.max_links: ' . ini_get('pgsql.max_links');
//        echo '<br><br>';

        if ($pconn && ini_get('pgsql.allow_persistent'))
            die('pgsql.allow_persistent is off');

        $this->cs = "host={$dbhost} port={$dbport} dbname={$dbname} user={$dbuser} password={$dbpass}";

        switch ($pconn) {
            case 0:
                $this->cn = pg_connect($this->cs);
                break;
            case 1:
                $this->cn = pg_pconnect($this->cs);
                break;
            default:
                $this->cn = pg_pconnect($this->cs, PGSQL_CONNECT_FORCE_NEW);
                break;
        }

        if (!$this->cn)
            die('could not connect');

        $this->status('connect is bad');
        $this->busy('connect is busy');

        $sqltpl = '
                  SELECT
                    PG_GET_SERIAL_SEQUENCE(TABLE_NAME, COLUMN_NAME) AS s, TABLE_NAME AS t,COLUMN_NAME AS c
                  FROM
                    INFORMATION_SCHEMA.COLUMNS
                  WHERE
                    TABLE_SCHEMA=$1 AND TABLE_CATALOG=$2 AND COLUMN_DEFAULT LIKE $3';

        $rs = $this->query_pars($sqltpl, array('public', $this->dbname(), 'nextval(%'));
        if (!$rs)
            die('init sequence map failed');

        while ($r = pg_fetch_assoc($rs))
            $this->_seqs[strtolower($r['t'])][$r['c']] = ltrim(strstr($r['s'], '.'), '.');
    }

    public function __destruct()
    {
        $this->cn = null;
    }

    ######### 基本 #########

    /**
     * 重新连接
     * @return bool
     */
    public function reset()
    {
        $this->cn = pg_connection_reset($this->cn);
        return $this->cn ? true : false;
    }

    private $_ver = array();

    /**
     * 版本等关联数据
     * @param string|null $vi Pg::VER_*
     * @return array
     */
    public function version($vi = null)
    {
        if (empty($_ver)) {
            $val = pg_version();
            $key = array_map(function ($v) {
                return strtoupper(ltrim(preg_replace("'[A-Z]+'", "_$0", $v), '_'));
            }, array_keys($val));

            $this->_ver = array_combine($key, $val);
        }

        if (!is_null($vi) && isset($this->_ver[$vi]))
            return $this->_ver[$vi];

        return $this->_ver;
    }

    /**
     * 当前连接、连接或结果集资源状态
     * @param resource|string $result_or_msg 连接或结果集资源|如果 bad 直接 die 消息
     * @param string $desc 状态描述
     * @return int|bool
     */
    public function status($result_or_msg = null, &$desc = null)
    {
        $desc = 'NULL';
        $msg = null;
        if (is_resource($result_or_msg)) {
            switch (get_resource_type($result_or_msg)) {
                case 'pgsql result':
                    $rtn = pg_result_status($result_or_msg);
                    if (func_num_args() == 2 && is_int($rtn))
                        $desc = PgV::ResultStatusDesc($rtn);
                    return $rtn;
                case 'pgsql link':
                    $rtn = pg_connection_status($result_or_msg);
                    break;
                default:
                    return false;
            }
        } else {
            $rtn = pg_connection_status($this->cn);
            $msg = $result_or_msg;
        }

        if (func_num_args() == 2) {
            if ($rtn === PGSQL_CONNECTION_OK)
                $desc = 'PGSQL_CONNECTION_OK';
            elseif ($rtn === PGSQL_CONNECTION_BAD)
                $desc = 'PGSQL_CONNECTION_BAD';
        }

        if ($rtn !== PGSQL_CONNECTION_OK && empty($msg))
            die($msg);

        return $rtn;
    }

    /**
     * 当前连接是否忙碌
     * @param string $msg 如果忙直接 die 消息
     * @return bool
     */
    public function busy($msg = null)
    {
        $rtn = pg_connection_busy($this->cn);
        if ($rtn && empty($msg))
            die($msg);

        return $rtn;
    }

    /**
     * Ping 连接
     * @param string $msg 如果 ping 不通直接 die 消息
     * @return bool
     */
    public function ping($msg = null)
    {
        $rtn = pg_ping($this->cn);
        if ($rtn && empty($msg))
            die($msg);

        return $rtn;
    }

    /**
     * 主机名
     * @return string
     */
    public function host()
    {
        return pg_host($this->cn);
    }

    public function port()
    {
        return pg_port($this->cn);
    }

    /**
     * 数据库名
     * @return string
     */
    public function dbname()
    {
        return pg_dbname($this->cn);
    }

    /**
     * 连接选项
     * @return string
     */
    public function options()
    {
        return pg_options($this->cn);
    }

    /**
     * PID
     * @return int
     */
    public function pid()
    {
        return pg_get_pid($this->cn);
    }

    /**
     * @todo
     */
    public function notify()
    {
        #pg_get_notify();
    }

    /**
     * 返回 tty 号
     * @return string
     */
    public function tty()
    {
        return pg_tty($this->cn);
    }

    /**
     * 读取服务端参数
     * @param string $parname 参数名
     * @return string
     */
    public function parameter($parname)
    {
        return pg_parameter_status($this->cn, $parname);
    }

    /**
     * 转化
     * @param string $data
     * @param int $type 0：字符串（默认），1：二进制字符串，2：标示如表名字段名等字符串
     * @return string
     */
    public function excape($data, $type = 0)
    {
        if (!is_string($data))
            return $data;

        switch ($type) {
            case 1:
                return pg_escape_bytea($this->cn, $data);
            case 2:
                return pg_escape_identifier($this->cn, $data);
            default:
                return pg_escape_literal($this->cn, $data);
        }
    }

    /**
     * 取消 bytea 类型中的字符串转义
     * @param string $data 转义后的 bytea 串
     * @return string
     */
    public function unescape($data)
    {
        return pg_unescape_bytea($data);
    }

    /**
     * 启用追踪
     * @param string $log 记录日志文件路径
     * @return bool
     */
    public function trace($log)
    {
        return pg_trace($log, 'w', $this->cn);
    }

    /**
     * 关闭追踪
     * @return bool
     */
    public function untrace()
    {
        return pg_untrace($this);
    }

    /**
     * 事务状态 0：空闲，1：事务中，2：事务中（语句传输中），3：事务中（发生错误），-1：未知情况，-2：异常情况
     * @return bool|int
     */
    public function trans()
    {
        $stt = pg_transaction_status($this->cn);
        switch ($stt) {
            case PGSQL_TRANSACTION_IDLE:
                return 0;
            case PGSQL_TRANSACTION_INTRANS:
                return 1;
            case PGSQL_TRANSACTION_ACTIVE:
                return 2;
            case PGSQL_TRANSACTION_INERROR:
                return 3;
            case PGSQL_TRANSACTION_UNKNOWN;
                return -1;
        }
    }

    /**
     * 添加或删除 Savepoint
     * @param string $pointname 存储点，非empty串表示删除
     * @return bool|string
     */
    public function point($pointname = null)
    {
        try {
            $stt = $this->trans();
            if ($stt < 1)
                return false;
            if (empty($pointname)) {
                $pointname = str_replace(' . ', '_', uniqid('sq_', true));
                $rs = $this->query("SAVEPOINT {$pointname};");
                if ($rs)
                    return false;
                return $pointname;
            } else {
                $rs = $this->query("RELEASE SAVEPOINT {$pointname};");
                if ($rs)
                    return true;
                return false;
            }
        } catch (Exception $ex) {
            $this->loger($ex);
            return false;
        }
    }

    /**
     * 启用事务
     * @return bool
     */
    public function begin()
    {
        try {
            $stt = $this->trans();
            if ($stt < 0)
                return false;
            elseif ($stt > 0)
                return true;

            $this->query('BEGIN;');
            return $this->trans() == 1;
        } catch (Exception $ex) {
            $this->loger($ex);
            return false;
        }
    }

    /**
     * 事务提交
     * @return bool
     */
    public function commit()
    {
        try {
            if ($this->trans() != 1)
                return false;

            if ($this->query('COMMIT;'))
                return true;
        } catch (Exception $ex) {
            $this->loger($ex);
            return false;
        }
    }

    /**
     * 事务回滚或回滚到存储点
     * @param string $pointname 存储点
     * @return bool
     */
    public function back($pointname = null)
    {
        try {
            if ($this->trans() < 1)
                return false;

            $sql = 'ROLLBACK';

            if (!empty($pointname))
                $sql .= ' TO SAVEPOINT ' . $pointname;

            $sql .= ';';

            if ($this->query($sql))
                return true;
        } catch (Exception $ex) {
            $this->loger($ex);
            return false;
        }
    }

    ######### 增删改查 #########
    /**
     * 按表名返回序列
     * @param string $tabname 表名
     * @param array $seqarr
     * @return bool
     * @throws Exception
     */

    public function get_seqs($tabname, &$seqarr = null)
    {
        if (empty($tabname))
            throw new Exception('empty($tabname) == true');

        if (!isset($this->_seqs[$tabname]))
            return false;

        if (func_num_args() == 2)
            $seqarr = $this->_seqs[$tabname];

        return true;
    }

    /**
     * 生成填充串
     * @param $count
     * @return string
     */
    public function make_subs($count)
    {
        return '$' . implode(', $', range(1, $count));
    }

    /**
     *
     * @param $result
     * @param $offset
     * @return bool
     */
    public function seek($result, $offset)
    {
        return pg_result_seek($result, $offset);
    }

    /**
     * 结果行数或列数
     * @param resource(pgsql result) 结果集
     * @param bool $isrow 返回行，否则返回列数
     * @return int
     */
    public function count($result, $isrow = true)
    {
        if ($isrow)
            return pg_num_rows($result);
        else
            return pg_num_fields($result);
    }

    /**
     * 执行 INSERT，UPDATE 和 DELETE 的影响行数
     * @param resource(pgsql result) $result
     * @return int
     */
    public function aff_rows($result)
    {
        return pg_affected_rows($result);
    }

    /**
     * 将一个表拷贝到数组中
     * @param string $tabname 表名
     * @param string $sep 分隔符
     * @param string $nullas NULL 替代
     * @return array
     */
    public function copy_to($tabname, $sep = "\t", $nullas = "\\N")
    {
        return pg_copy_to($this->cn, $tabname, $sep, $nullas);
    }

    /**
     * 根据数组将记录插入表中
     * @param string $tabname 表名
     * @param array $rows 数据行
     * @param string $sep 分隔符
     * @param string $nullas NULL 替代
     * @return bool
     */
    public function copy_from($tabname, array $rows, $sep = "\t", $nullas = "\\N")
    {
        return pg_copy_from($this->cn, $tabname, $rows, $sep, $nullas);
    }

    /**
     * @todo
     * 表数据复制
     * @param string $tabfrom 源表
     * @param string $tabto 目标表
     * @param string $sep 分隔符
     * @param string $nullas NULL 替代
     */
    public function copy($tabfrom, $tabto, $sep = "\t", $nullas = "\\N")
    {
        
    }

    /**
     * @todo
     * @param $tabfrom
     * @param $tabto
     * @param string $sep
     * @param string $nullas
     */
    public function move($tabfrom, $tabto, $sep = "\t", $nullas = "\\N")
    {
        
    }

    /**
     * 查询
     * @param string $sql
     * @return resource(pgsql result)
     */
    public function query($sql)
    {
        return pg_query($this->cn, $sql);
    }

    /**
     * 查询
     * @param string $sql
     * @return PgRs
     */
    public function pgrs_query($sql)
    {
        return new PgRs($this->cn, $sql);
    }

    /**
     * 查询
     * @param string $sqltpl
     * @param array $pars
     * @return resource(pgsql result)
     */
    public function query_pars($sqltpl, array $pars)
    {
        return pg_query_params($this->cn, $sqltpl, $pars);
    }

    /**
     * 查询
     * @param string $spftpl SQL 以 format 格式的模板
     * @param array $pars 类似 query_param 的参数
     * @param array|int $escape_type 0：字符串（默认），1：二进制字符串，2：标示如表名字段名等字符串。传 int 表示整个$params是同一类型。传 array 则需要与 $params 一一对应
     * @return bool(false)|resource(pgsql result)
     */
    public function query_fmt($spftpl, array $pars, $escape_type)
    {
        try {
            $arr = array();

            if (is_array($escape_type)) {
                if (count($pars) != count($escape_type))
                    throw new Exception('count($params) != count($escape_type)');

                foreach ($pars as $i => $param) {
                    if (!isset($escape_type[$i]))
                        throw new Exception("isset(\$escape_type[{$i}])==false");

                    if (!is_int($escape_type[$i]))
                        throw new Exception("\$escape_type[\$i]=={$escape_type[$i]}");

                    $arr[] = $this->excape($param, $escape_type[$i]);
                }
            } elseif (is_int($escape_type)) {
                foreach ($pars as $param)
                    $arr[] = $this->excape($param, $escape_type);
            } else
                throw new Exception('escape_type type error');

            return $this->query(vsprintf($spftpl, $arr));
        } catch (Exception $ex) {
            $this->loger($ex);
            return false;
        }
    }

    /**
     * @todo 加 query 过滤，不是所有的语法都支持预处理
     * 预处理
     * @param string $stmtname
     * @param string $sqltpl
     * @return resource(pgsql result)
     */
    public function prep($stmtname, $sqltpl)
    {
        return pg_prepare($this->cn, $stmtname, $sqltpl);
    }

    /**
     * 执行
     * @param string $stmtname
     * @param array $params
     * @return resource(pgsql result)
     */
    public function exec($stmtname, array $params)
    {
        return pg_execute($this->cn, $stmtname, $params);
    }

    /**
     * 预处理后执行
     * @param string $sqltpl
     * @param string $stmtname
     * @param array $params
     * @return array|resource(pgsql result)|bool(false)
     */
    public function prep_exec($sqltpl, $stmtname, array $params)
    {
        $rtn = false;

        $rs = $this->prep($stmtname, $sqltpl);
        if ($rs) {
            if (is_array(current($params))) {
                $rtn = array();
                foreach ($params as $param) {
                    $rs = $this->exec($stmtname, $param);
                    if ($rs)
                        $rtn[] = $rs;
                }
            } else
                $rtn = $this->exec($stmtname, $params);
        }

        return $rtn;
    }

    /**
     * 插入行，如果有自增 id 则返回数组
     * @param string $tabname 表名。全大写表名：启用事务；全小写表名：不使用事务
     * @param array $row
     * @param bool $commit
     * @return bool|array
     */
    public function ins_row($tabname, array $row, $commit = true)
    {
        try {
            if (empty($row))
                throw new Exception('empty($row) == true');

            if (is_array(current($row)))
                throw new Exception();

            $fields = array_keys($row);
            foreach ($fields as &$field)
                $field = $this->excape($field, 2);

            $subs = $this->make_subs(count($row));

            $sql = sprintf('INSERT INTO %s(%s)VALUES(%s)', $this->excape($tabname, 2), implode(',', $fields), $subs);

            $seq = $this->get_seqs($tabname, $seqarr);
            if ($seq)
                $sql .= 'RETURNING "' . implode('","', array_keys($seqarr)) . '"'; //$sql .= sprintf('RETURNING CURRVAL(%s)', $seqname);

            $sql .= ';';

            $rs = $this->query_pars($sql, array_values($row));
            if (!$rs || $this->status($rs) != 2)
                return false;

            if (!$this->aff_rows($rs))
                throw new Exception('no affected row');

            if ($commit)
                $this->commit();

            $rtn = true;

            if ($seq)
                $rtn = pg_fetch_assoc($rs);

            $this->free($rs);
            return $rtn;
        } catch (Exception $ex) {
            $this->back();
            $this->loger($ex);
        }
        return false;
    }

    /**
     * INSERT 后的分配的 oid
     * @param resource(pgsql result) $result 结果集
     * @return string
     */
    public function iid($result)
    {
        return pg_last_oid($result);
    }

    /**
     * 取一行
     * @param string $sql
     * @param int $ri 行索引，从0开始，默认0
     * @return array|bool
     */
    public function get_row($sql, $ri = 0)
    {
        $rtn = false;
        try {
            $rs = $this->query($sql);
            if ($rs)
                $rtn = pg_fetch_row($rs, $ri);
            $this->free($rs);
        } catch (Exception $ex) {
            $this->loger($ex);
        }
        return $rtn;
    }

    /**
     * 结果以双重下标的数组形式返回
     * @param string $sql
     * @param int $ri 行方索引从0开始
     * @return array|bool
     */
    public function get_arr($sql, $ri)
    {
        $rtn = false;
        try {
            $rs = $this->query($sql);
            if ($rs)
                $rtn = pg_fetch_array($rs, $ri, PGSQL_BOTH);
            $this->free($rs);
        } catch (Exception $ex) {
            $this->loger($ex);
        }
        return $rtn;
    }

    /**
     * 结果以对象形式返回
     * @param $sql
     * @param $ri
     * @return object|bool(false)
     */
    public function get_obj($sql, $ri)
    {
        $rtn = false;
        try {
            $rs = $this->query($sql);
            if ($rs)
                $rtn = pg_fetch_object($rs, $ri, PGSQL_ASSOC);
            $this->free($rs);
        } catch (Exception $ex) {
            $this->loger($ex);
        }
        return $rtn;
    }

    /**
     * 取全部，避免外部循环用此方法
     * @param string $sql
     * @return array|bool(false)
     */
    public function get_all($sql)
    {
        $rtn = false;
        try {
            $rs = $this->query($sql);
            if ($rs)
                $rtn = pg_fetch_all($rs);
            $this->free($rs);
        } catch (Exception $ex) {
            $this->loger($ex);
        }
        return $rtn;
    }

    /**
     * 取Pg数据类型一格
     * @param string $sql
     * @param int $ri 行索引
     * @param int|string $ci 列索引或字段名
     * @return bool(false)|mixed
     */
    public function get_pgone($sql, $ri = 0, $ci = 0)
    {
        $rtn = false;
        try {
            $rs = $this->query($sql);
            if ($rs)
                throw new Exception();

            $rtn = pg_fetch_result($rs, $ri, $ci);
            $this->free($rs);
        } catch (Exception $ex) {
            $this->loger($ex);
        }
        return $rtn;
    }

    /**
     * 清空结果集
     * @param resource(pgsql result) $result 结果集
     * @return bool
     */
    public function free($result)
    {
        return pg_free_result($result);
    }

    ######### 大对象lo #########
    //pg_lo_close — 关闭一个大型对象
    //pg_lo_create — 新建一个大型对象
    //pg_lo_export — 将大型对象导出到文件
    //pg_lo_import — 将文件导入为大型对象
    //pg_lo_open — 打开一个大型对象
    //pg_lo_read_all — 读入整个大型对象并直接发送给浏览器
    //pg_lo_read — 从大型对象中读入数据
    //pg_lo_seek — 移动大型对象中的指针
    //pg_lo_tell — 返回大型对象的当前指针位置
    //pg_lo_truncate — Truncates a large object
    //pg_lo_unlink — 删除一个大型对象
    //pg_lo_write — 向大型对象写入数据
    ######### 异步 #########
    //pg_cancel_query
    //pg_send_query()
    //pg_connection_busy()
    //pg_connect_poll()
    //pg_socket()
    //pg_consume_input()
    //pg_flush()
    //pg_result_error()
    //pg_result_error_field()

    /**
     * 异步查询
     * @param $sql
     * @return bool
     */
    public function async_query($sql)
    {
        if ($this->busy())
            return false;

        return pg_send_query($this->cn, $sql);
    }

    /**
     * 取消异步查询
     * @return bool
     */
    public function async_cancel()
    {
        return pg_cancel_query($this->cn);
    }

    /**
     *
     * @param resource(pgsql result) $result
     * @return bool|int
     */
    public function async_result(&$result)
    {
        $result = pg_get_result($this->cn);
        echo pg_result_error_field($result, PGSQL_DIAG_SQLSTATE);
        if ($result)
            return $this->count($result);

        return false;
    }

    ## 维护

    public function ren_table($old, $new)
    {
        // $this->exec(sprintf('ALTER TABLE IF EXISTS "%s" RENAME TO "%s"', $old, $new), false);
        // $this->exec(sprintf('ALTER SEQUENCE IF EXISTS "%s_id_seq"  RENAME TO "%s_id_seq"', $old, $new), true);
    }

    /**
     * 修正表序列
     * @param int $code 状态码
     * @return bool
     */
    public function correct_seq(&$code = null)
    {
        try {
            $code = 0;

            $sqltpl = "
                  SELECT
                    PG_GET_SERIAL_SEQUENCE(TABLE_NAME, COLUMN_NAME) AS s, CONCAT(TABLE_NAME,'_',COLUMN_NAME,'_seq') as c,ORDINAL_POSITION AS p
                  FROM
                    INFORMATION_SCHEMA.COLUMNS
                  WHERE
                    TABLE_SCHEMA=$1 AND TABLE_CATALOG=$2 AND COLUMN_DEFAULT LIKE $3";

            $rs = $this->query_pars($sqltpl, array('public', $this->dbname(), 'nextval(%'));
            if (!$rs) {
                $code = 401;
                return false;
            }

            $alter = 'ALTER SEQUENCE IF EXISTS % s RENAME TO % s;';

            $rtn = array();

            while ($row = pg_fetch_assoc($rs)) {
                if (empty($row['s']))
                    continue;

                list($pub, $seq) = explode(' . ', $row['s'], 2);

                if ($row['c'] == $seq && $pub == 'public')
                    continue;

                $rtn[$seq . '->' . $row['c']] = $this->query_fmt($alter, array($seq, $row['c']), 2) ? 'Y' : 'N';
            }

            $this->free($rs);
            $code = 200;
            return true;
        } catch (Exception $ex) {
            $code = 300;
            $this->loger($ex);
            return false;
        }
    }

    /**
     * 打日志
     * 单参数：对象与字符串
     * 多参数：格式要求与 sprintf 相同
     */
    private function loger()
    {
        $log = null;

        $parc = func_num_args();

        switch ($parc) {
            case 0:
                $log = 'unknown';
                break;
            case 1:
                $log = func_get_arg(0);
                break;
            default:
                $fmt = func_get_arg(0);
                $parv = func_get_args();
                if (is_string($fmt) && !empty($fmt) && substr_count($fmt, ' % ') >= $parc - 1)
                    $log = call_user_func_array('sprintf', $parv);

                if ($log == null || is_bool($log))
                    $log = implode(',', $parv);

                if (!$log)
                    $log = var_export($parv, true);
                break;
        }

        if ($log != null && !is_string($log)) {
            $classname = 'Exception';
            if (is_object($log) && $log instanceof $classname)
                $log = sprintf('line:%d,code:%d,file:%s,msg:%s', $log->getLine(), $log->getCode(), $log->getFile(), html_entity_decode($log->getMessage()));
            else
                $log = print_r($log, true);
        }

        if (function_exists('loger'))
            loger($log, strtolower(__CLASS__));
        else
            error_log(PHP_EOL . date('Y-m-d+H:i:s') . $log . PHP_EOL, 3, __ROOTLOG__ . strtolower(__CLASS__) . '_' . urlencode(__WHOAMI__) . '.log');
    }

}

class PgRs
{

    /**
     * 最后执行结果
     * @var resource(pgsql result)
     */
    private $rs = null;

    /**
     * 状态
     * @var int|null
     */
    private $ss = null;

    public function __construct($res_or_obj, $sql = null)
    {
        if (is_resource($res_or_obj)) {
            if (get_resource_type($res_or_obj) != 'pgsql result')
                throw new Exception('$res_or_obj not an resource(pgsql result)');

            $this->rs = $res_or_obj;
        } elseif (is_object($res_or_obj)) {
            if (get_class($res_or_obj) != 'Pg')
                throw new Exception('$res_or_obj not an object(Pg)');

            if (is_null($sql))
                throw new Exception('$sql is null');

            if (!is_callable(array($res_or_obj, 'query')))
                throw new Exception('Pg->query not callable');

            $this->rs = call_user_func(array($res_or_obj, 'query'), $sql);
        } else
            throw new Exception('$res_or_obj type is invalid');

        $this->ss = pg_result_status($this->rs);
    }

    public function __destruct()
    {
        $this->free();
    }

    public function __debugInfo()
    {
        return array($this->rs);
    }

    private function rs()
    {
        if (is_resource($this->rs) && get_resource_type($this->rs) == 'pgsql result')
            return $this->rs;

        throw new Exception('$this->rs is invalid');
    }

    /**
     * 释放
     * @return bool
     */
    public function free()
    {
        if (is_null($this->rs))
            return false;

        pg_free_result($this->rs);
        $this->rs = null;
    }

    /**
     * 结果集状态
     * @param string $desc 状态描述
     * @param int|null $sv 状态值
     * @return bool|mixed|null
     * @throws Exception
     */
    public function status(&$desc, $sv = null)
    {
        $desc = PgV::ResultStatusDesc($sv);

        if (is_null($sv))
            return $this->ss;

        if (is_int($sv))
            return $this->ss == $sv;

        throw new Exception('$sv type error');
    }

    /**
     * 返回行或列数
     * @param bool $isrow 返回行，否则返回列数
     * @return int
     */
    public function count($isrow = true)
    {
        if ($isrow)
            return pg_num_rows($this->rs());
        else
            return pg_num_fields($this->rs());
    }

    /**
     * 返回所有行或指定列
     * @param int|null $ci 列索引
     * @return array|bool
     * @throws Exception
     */
    public function all($ci = null)
    {
        if (is_null($ci))
            return pg_fetch_all($this->rs());

        if (is_int($ci))
            return pg_fetch_all_columns($this->rs(), $ci);

        throw new Exception('$ci type error');
    }

    /**
     * 返回数字索引的一行
     * @param int|null $ri 行索引
     * @return array
     * @throws Exception
     */
    public function row($ri = null)
    {
        if (!is_null($ri) && !is_int($ri))
            throw new Exception('$ri type error');

        return pg_fetch_row($this->rs(), $ri);
    }

    /**
     * 返回字段名索引的一行
     * @param int|null $ri 行索引
     * @return array
     * @throws Exception
     */
    public function ass($ri = null)
    {
        if (!is_null($ri) && !is_int($ri))
            throw new Exception('$ri type error');

        return pg_fetch_assoc($this->rs(), $ri);
    }

    /**
     * 返回按参数要求设定结果索引的一行
     * @param int|null $ri 行索引
     * @param int $rt 结果类型
     * @return array
     * @throws Exception
     */
    public function arr($ri = null, $rt = PGSQL_BOTH)
    {
        if (!is_null($ri) && !is_int($ri))
            throw new Exception('$ri type error');

        if (!is_int($rt))
            throw new Exception('$rt type error');

        return pg_fetch_array($this->rs(), $ri, $rt);
    }

    /**
     * 返回对象的一行
     * @param int|null $ri 行索引
     * @return object
     * @throws Exception
     */
    public function obj($ri = null)
    {
        if (!is_null($ri) && !is_int($ri))
            throw new Exception('$ri type error');

        return pg_fetch_object($this->rs(), $ri);
    }

    /**
     * 取Pg数据类型一格
     * @param int $ri 行索引
     * @param int|string $ci 列索引或字段名
     * @return string
     */
    public function pgone($ri, $ci)
    {
        return pg_fetch_result($this->rs(), $ri, $ci);
    }

    /**
     * 字段是否为 NULL
     * @param int $ri 行索引
     * @param int|string $ci 列索引或字段名
     * @return int
     */
    public function is_null($ri, $ci)
    {
        return pg_field_is_null($this->rs(), $ri, $ci);
    }

    /**
     * 字段名
     * @param int $ci 列索引
     * @return string
     */
    public function colname($ci)
    {
        return pg_field_name($this->rs(), $ci);
    }

    /**
     * 列索引
     * @param string $cn 列名
     * @return int
     */
    public function colidx($cn)
    {
        return pg_field_num($this->rs(), $cn);
    }

    /**
     * 值打印长度
     * @param int $ri 行索引
     * @param string $cn 列名
     * @return int
     */
    public function colplen($ri, $cn)
    {
        return pg_field_prtlen($this->rs(), $ri, $cn);
    }

    /**
     * 字段存储空间
     * @param int $ci 列索引
     * @return int
     */
    public function colsize($ci)
    {
        return pg_field_size($this->rs(), $ci);
    }

    /**
     * 返回列所在表的表名或 oid
     * @param int $ci 列索引
     * @param bool $oid_only 是否返回oid
     * @return mixed
     */
    public function coltbl($ci, $oid_only = false)
    {
        return pg_field_table($this->rs(), $ci, $oid_only);
    }

    /**
     * 返回字段类型或类型的 oid
     * @param int $ci 列索引
     * @param bool $oid_only
     * @return int|string
     */
    public function coltype($ci, $oid_only = false)
    {
        if ($oid_only)
            return pg_field_type_oid($this->rs, $ci);

        return pg_field_type($this->rs(), $ci);
    }

}

 class Sqler
{

    /**
     * @var null|Pg
     */
    protected $cn = null;
    protected $_sql = '';
    protected $_sqlarr = array();

    /**
     * Sqler constructor.
     * @param Pg $cn PgSQL连接
     */
    public function __construct(Pg $cn)
    {
        $this->cn = $cn;
    }

    protected function _sqlget($key, $def, &$val = '')
    {
        if (empty($key))
            throw new Exception();

        if (isset($this->_sqlarr[$key]))
            $val = $this->_sqlarr[$key];
        elseif (!is_null($def))
            $val = $def;
        else
            return false;

        return true;
    }

    protected function _sqlset($key, $val)
    {
        if (empty($key))
            throw new Exception();

        if (empty($val))
            return;

        $this->_sql = '';

        $this->_sqlarr[$key] = $val;
    }

    protected function table($table)
    {
        $this->_sqlset('table', $this->cn->excape($table, 2));
    }

    protected function _field($parc, $pars)
    {
        $this->_sql = '';

        if (!$parc)
            throw new Exception();

        if ($parc == 1 && is_array(current($pars)))
            $pars = current($pars);

        $fldstr = '';

        foreach ($pars as $as => $fld) {
            if (!is_string($fld))
                throw new Exception();

            if (preg_match("'^[a-z0-9_]+$'i", $fld))
                $fldstr .= $this->cn->excape($fld, 2);
            else
                $fldstr .= $fld;

            if (!is_int($as))
                $fldstr .= ' AS ' . $as;
            $fldstr .= ',';
        }

        $this->_sqlset('field', rtrim($fldstr, ','));
    }

    /**
     * 重置 SQL
     */
    public function sql_reset()
    {
        $this->_sql = '';
        $this->_sqlarr = array();
    }

    /**
     * 运行并返回 PgRs
     * @return PgRs
     */
    public function run()
    {
        return new PgRs($this->cn, $this->_sql);
    }

}

class PgIns extends Sqler
{

    public function field()
    {
        parent::_field(func_num_args(), func_get_args());
        return $this;
    }

    public function value()
    {
        func_get_args();

        return $this;
    }

    public function update()
    {
        
    }

}

class PgDel extends Sqler
{

    public function run()
    {
        parent::run();
    }

}

class PgUpd extends Sqler
{

    /**
     * @param string|array
     * @return $this
     */
    public function field()
    {
        parent::_field(func_num_args(), func_get_args());
        return $this;
    }

    public function value()
    {
        return $this;
    }

}

/**
 * Class PgSel
 */
class PgSel extends Sqler
{

    /**
     * 指定表名
     * @param $table
     * @return $this
     */
    public function table($table)
    {
        parent::table($table);
        return $this;
    }

    /**
     * 字段或表达式
     * @return $this
     * @throws Exception
     */
    public function field()
    {
        parent::_field(func_num_args(), func_get_args());
        return $this;
    }

    /**
     * 条件
     * @param $where
     * @return $this
     * @throws Exception
     */
    public function where($where)
    {
        parent::_sqlset('where', $where);
        return $this;
    }

    /**
     * GROUP BY
     * @param $group
     * @return $this
     * @throws Exception
     */
    public function group($group)
    {
        parent::_sqlset('group', $group);
        return $this;
    }

    /**
     * HAVING
     * @param $exp
     * @return $this
     * @throws Exception
     */
    public function having($exp)
    {
        parent::_sqlset('having', $exp);
        return $this;
    }

    /**
     * ORDER BY
     * @param $order
     * @param bool $desc
     * @return $this
     * @throws Exception
     */
    public function order($order, $desc = true)
    {
        parent::_sqlset('order', $order);
        parent::_sqlset('desc', $desc);
        return $this;
    }

    /**
     * 指定返回行数和偏移量
     * @param $limit
     * @param int $offset
     * @return $this
     * @throws Exception
     */
    public function limit($limit, $offset = 0)
    {
        parent::_sqlset('limit', $limit);
        parent::_sqlset('offset', $offset);
        return $this;
    }

    public function run()
    {
        $res = $this->sql();
        if (empty($res))
            throw new Exception();
        return parent::run();
    }

    public function sql()
    {
        if (empty($this->_sql)) {

            $this->_sqlget('field', '*', $field);

            $sql = 'SELECT ' . $field;

            if ($this->_sqlget('table', null, $table))
                $sql .= ' FROM ' . $table;

            if ($this->_sqlget('where', null, $where))
                $sql .= ' WHERE ' . $where;

            if ($this->_sqlget('group', null, $group))
                $sql .= ' GROUP BY ' . $group;

            if ($this->_sqlget('having', null, $having))
                $sql .= ' HAVING ' . $having;

            if ($this->_sqlget('order', null, $order) && $this->_sqlget('desc', true, $desc))
                $sql .= ' ORDER BY ' . $this->cn->excape($order, 2) . ' ' . ($desc ? 'DESC' : 'ASC');

            if ($this->_sqlget('limit', null, $limit) && $this->_sqlget('offset', 0, $offset))
                $sql .= ' LIMIT ' . $limit . ($offset != 'ALL' ? ' OFFSET ' . $offset : '');

            $this->_sql = $sql . ';';
        }

        return $this->_sql;
    }

}

class PgV
{

    const VER_CLIENT = 'CLIENT';
    const VER_PROTOCOL = 'PROTOCOL';
    const VER_SERVER = 'SERVER';
    const VER_SERVER_ENCODING = 'SERVER_ENCODING';
    const VER_CLIENT_ENCODING = 'CLIENT_ENCODING';
    const VER_IS_SUPERUSER = 'IS_SUPERUSER';
    const VER_SESSION_AUTHORIZATION = 'SESSION_AUTHORIZATION';
    const VER_DATE_STYLE = 'DATE_STYLE';
    const VER_INTERVAL_STYLE = 'INTERVAL_STYLE';
    const VER_TIME_ZONE = 'TIME_ZONE';
    const VER_INTEGER_DATETIMES = 'INTEGER_DATETIMES';
    const VER_STANDARD_CONFORMING_STRINGS = 'STANDARD_CONFORMING_STRINGS';
    const VER_APPLICATION_NAME = 'APPLICATION_NAME';

    private static $_result_status = array(
        0 => 'PGSQL_EMPTY_QUERY',
        1 => 'PGSQL_COMMAND_OK',
        2 => 'PGSQL_TUPLES_OK',
        3 => 'PGSQL_COPY_TO',
        4 => 'PGSQL_COPY_FROM',
        5 => 'PGSQL_BAD_RESPONSE',
        6 => 'PGSQL_NONFATAL_ERROR',
        7 => 'PGSQL_FATAL_ERROR'
    );

    /**
     * 结果集状态描述
     * @param int $sv 状态值
     * @return string|null
     */
    public static function ResultStatusDesc($sv)
    {
        if (!is_int($sv))
            return null;

        $desc = &self::$_result_status[$sv];
        if (isset($desc))
            return $desc;

        return null;
    }

}
