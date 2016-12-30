<?php

/**
 *
 * Author: moore <majw@lizi.comm>
 * Assertor: @chenj
 * Create: 2015/5/13 16:58
 */
class Kernel_DB_Mongo
{
    private static $cn = null;
    private $mogdbs = null;
    public $mogcoll = null;
    private $auto_id = 'auto_id';
    private $auto_coll = null;
    public $collections = array();
    public $dbname = '';
    public $collection = '';

//    private $symbol_map = array(
//        '+' => '$inc',# 加法
//        '*' => '$mul',# 乘法
//        '@' => '$exists', #键是否存在
//        '?' => 'perl', #正则
//    );

    private $where_map = array(
        '>' => '$gt',
        '>=' => '$gte',
        '<' => '$lt',
        '<=' => '$lte',
        '!=' => '$ne',
        'in' => '$in',
        'not in' => '$nin',
    );

    /**
     * mgo constructor.
     * @param array $config
     */
    public function __construct($config = array('user' => 'admin', 'pass' => '123456', 'host' => 'localhost', 'port' => '27017', 'dbname' => ''))
    {

        if (empty($config['dbname'])) {
            throw new Kernel_Exception_InternalServerError(
                T('ReconnectNo:dbname is empty:{dbname}', array('dbname' => $config['dbname']))
            );
        }

        $this->collection = isset($config['collection']) ? $config['collection'] : '';
        $server = "{$config['user']}:{$config['pass']}@{$config['host']}:{$config['port']}/{$config['dbname']}";
        $options = array("connectTimeoutMS" => 30000);
        try {

            self::$cn = new MongoClient('mongodb://' . $server, $options);
            if (isset($config['dbname']) && $config['dbname'] != '') {
                $this->dbname = $config['dbname'];
                $this->use_db();
                if ($this->collection != '') {
                    $this->use_coll($this->collection);
                }
            }

        } catch (MongoConnectionException $e) {
            for ($i = 0; $i < 5; $i++) {
                try {
                    self::$cn = new MongoClient('mongodb://' . $server, $options);
                    if (isset($config['dbname']) && $config['dbname'] != '') {
                        $this->dbname = $config['dbname'];
                        $this->use_db();
                        if ($this->collection != '') {
                            $this->use_coll($this->collection);
                        }
                        return true;
                    }
                } catch (MongoConnectionException $e) {
                    continue;
                }
                $i = 5;
            }
            throw new Kernel_Exception_InternalServerError(
                T('connectMongo is wrong  msg:{msg}', array('msg' => $e->getMessage()))
            );
        }
        return true;
    }

    /**
     * mgo销毁程序
     */
    public function __destruct()
    {
        self::$cn = $this->mogdbs = $this->mogcoll = $this->dbname = $this->collection = $this->collections = null;
    }

    /**
     * 返回所有的db
     * @return bool|array
     */
    public function get_db_list()
    {
        $arr = self::$cn->listDBs();
        if (!is_array($arr) || !isset($arr['databases']) || !isset($arr['ok']) || $arr['ok'] != 1)
            return false;

        foreach ($arr['databases'] as &$db) {
            $db['size'] = $db['sizeOnDisk'];
            unset($db['sizeOnDisk']);
        }

        return $arr['databases'];
    }

    /**
     * 返回mongo库表信息
     * @param string $dbname string db name
     * @return $this|null
     */
    public function use_db($dbname = '')
    {

        try {
            if (empty($dbname) && $this->dbname == '')
                return false;
            if ($dbname != '')
                $this->dbname = $dbname;
            $this->mogdbs = self::$cn->selectDB($this->dbname);
            $this->show_collections();
            return $this;
        } catch (Exception $e) {
            throw new Kernel_Exception_InternalServerError(
                T('use_db is wrong  msg:{msg}', array('msg' => $e->getMessage()))
            );
        }
    }

    /**
     * 选择表或新建表
     * @param string $collection collectionName
     * @param bool $if_none_create 是否创建新collection
     * @return $this|bool|null
     */
    public function use_coll($collection = '', $if_none_create = false, $is_set_id = false)
    {
        try {
            if (empty($collection) && empty($this->collection))
                return false;
            if ($collection != '')
                $this->collection = $collection;
            if (!in_array($this->collection, $this->collections) && !$if_none_create)
                return false;
            if ($this->mogdbs == NULL)
                $this->use_db($this->dbname);
            $this->mogcoll = $this->mogdbs->selectCollection($this->collection);
            if ($is_set_id) {
                $this->auto_coll = $this->mogdbs->selectCollection($this->auto_id);
            }
            return $this;
        } catch (Exception $e) {
            throw new Kernel_Exception_InternalServerError(
                T('use_coll is wrong  msg:{msg}', array('msg' => $e->getMessage()))
            );
        }
    }

    /**
     * 获取一个值
     * @param $where array|string
     * @param $field string get field
     * @param bool $is_get_id
     * @return bool
     */
    public function get_one($where, $field, $is_get_id = false)
    {
//        if (empty($where))
//            return false;
        if (!is_array($where))
            $where = $this->exec_where($where);

        if (!is_string($field))
            return false;
        try {
            if (!$this->mogcoll)
                return false;
            $res = $this->mogcoll->findOne($where, array($field));
            if ($res) {
                if (!$is_get_id) {
                    unset($res['_id']);
                } else {
                    $res['_id'] = $res['_id'] . '';
                }
            }
            return isset($res[$field]) ? $res[$field] : false;
        } catch (Exception $e) {
            throw new Kernel_Exception_InternalServerError(
                T('get_one is wrong  msg:{msg}', array('msg' => $e->getMessage()))
            );
        }
    }

    /**
     * 获取一行
     * @param $where array|string
     * @param bool $is_get_id
     * @return bool
     */
    public function get_row($where, $is_get_id = false)
    {
//        if (empty($where))
//            return false;

        if (!is_array($where))
            $where = $this->exec_where($where);

        try {
            if (!$this->mogcoll)
                return false;
            $res = $this->mogcoll->findOne($where);
            if ($res) {
                if (!$is_get_id) {
                    unset($res['_id']);
                } else {
                    $res['_id'] = $res['_id'] . '';
                }
            }
            return $res;
        } catch (Exception $e) {
            throw new Kernel_Exception_InternalServerError(
                T('get_row is wrong  msg:{msg}', array('msg' => $e->getMessage()))
            );
        }
    }

    /**
     * 获取所有行
     * @param $where
     * @param array $field
     * @param array $sort exp asc:array('field'=>1),desc:array('field'=>-1)
     * @param array $limit
     * @param bool $is_get_id
     * @return array|bool
     */
    public function get_all($where, $field = array(), $sort = array(), $limit = array(0, 0), $is_get_id = false)
    {
//        if (empty($where))
//            return false;
        if (!is_array($where))
            $where = $this->exec_where($where);

        $ff = array();
        if (!empty($field)) {
            foreach ($field as $f) {
                $ff[$f] = 1;
            }
        }
        try {
            if (!$this->mogcoll)
                return false;
            $res = $this->mogcoll->find($where, $ff)->sort($sort)->limit(isset($limit[1]) ? $limit[1] : 0)->skip(isset($limit[0]) ? $limit[0] : 0);
            $res = iterator_to_array($res);
            if ($res && !empty($res)) {
                foreach ($res as &$one) {
                    if (!$is_get_id) {
                        unset($one['_id']);
                    } else {
                        $one['_id'] = $one['_id'] . '';
                    }
                }
            }
            return array_values($res);
        } catch (Exception $e) {
            throw new Kernel_Exception_InternalServerError(
                T('get_all is wrong  msg:{msg}', array('msg' => $e->getMessage()))
            );
        }
    }

    /**
     * 插入一行 （如果主键存在则更新 默认直接插入不更新）
     * @param $data array 需要插入的数据
     * @param $where string|array 更新条件
     * @param $int_field array 整型字段
     * @param bool $update 是否执行自动判断，存在条件折更新
     * @return bool
     */
    public function insert($data, $where = '', $int_field = array(), $update = false)
    {
        try {
            if ($this->auto_coll) {
                $data['id'] = $this->get_new_id();
                if ($data['id'] === false) {
                    return false;
                }
            }
            if (empty($data))
                return false;
            if (!$update) { # 直接插入
                $res = $this->mogcoll->insert($data);
                if ($res['ok']) {
                    return isset($data['id']) ? $data['id'] : true;
                }
                return $res['err'];
            }

            if (empty($where))
                return false;

            if (!is_array($where))
                $where = $this->exec_where($where);
            if (!$this->mogcoll)
                return false;
            $res = $this->mogcoll->findAndModify($where, array(), array('_id'),
                array(
                    'new' => true,
                    'update' => $this->exec_data($data, $int_field),
                    'upsert' => true
                ));

            return $res ? (isset($data['id']) ? $data['id'] : true) : false;

        } catch (Exception $e) {
            throw new Kernel_Exception_InternalServerError(
                T('insert is wrong  msg:{msg}', array('msg' => $e->getMessage()))
            );
        }

    }


    /**
     * 插入多行 存在则更新
     * @param $data array(array(key=>value),array(key=>value)) || array(array(key -=>value),array(key +=>value))
     * @param array $where where条件 array|string 在$index 为true的时候，则where 只是一个主键
     * @param $int_field array 整型字段
     * @param bool $update 是否判断数据存在则更新
     * @return bool|int
     */
    public function insert_mul($data, $where = '', $int_field = array(), $update = false, $index = false)
    {
        try {

            if (empty($data))
                return false;
            if ($this->auto_coll) {
                $num = count($data);
                $id = $this->get_new_id($num);
                if ($id === false) {
                    return false;
                }
                $id -= $num;
                foreach ($data as &$d) {
                    foreach ($int_field as $f) {
                        $d[$f] = intval($d[$f]);
                    }
                    $id++;
                    $d['id'] = $id;
                }
            }
            if (!$update) { # 直接插入
                if (!$this->mogcoll)
                    return false;
                $res = $this->mogcoll->batchInsert($data, array('continueOnError' => true));
                if ($res['ok']) {
                    return isset($num) ? $num : true;
                }
                return $res['err'];
            }
            $index_field = $where;

            if (!$index) {# 判断是否根据主键进行更新数据

                if (empty($where))
                    return false;

                if (!is_array($where))
                    $where = $this->exec_where($where);
            }
            $retuen = 0;
            foreach ($data as $one) {
                if ($index) {
                    $where = array($index_field => $one[$index_field]);
                }
                // var_dump($one); echo "<br/>";var_dump($where);$this->exec_data($one, $int_field);exit;
                $res = $this->mogcoll->findAndModify($where, array(), array('_id'),
                    array(
                        'new' => true,
                        'update' => $this->exec_data($one, $int_field),
                        'upsert' => true
                    ));
                $res && $retuen++;
            }
            return $retuen;

        } catch (Exception $e) {
            throw new Kernel_Exception_InternalServerError(
                T('insert_mul is wrong  msg:{msg}', array('msg' => $e->getMessage()))
            );
        }
    }

    /** 获取表自增id
     * @param int $num 插入条数
     * @return bool
     */
    private function get_new_id($num = 1)
    {
        $res = $this->auto_coll->findAndModify(array('name' => $this->collection), array(), array(),
            array(
                'new' => true,
                'update' => array('$inc' => array('id' => $num)),
                'upsert' => true
            ));

        return isset($res['id']) ? $res['id'] : false;
    }

    /**
     * 统计数量
     * @param $where string|array
     * @return bool
     */
    public function count($where)
    {
        try {
//            if (empty($where))
//                return false;

            if (!is_array($where))
                $where = $this->exec_where($where);
            if (!$this->mogcoll)
                return false;
            return $this->mogcoll->find($where)->count();
        } catch (Exception $e) {
            throw new Kernel_Exception_InternalServerError(
                T('count is wrong  msg:{msg}', array('msg' => $e->getMessage()))
            );
        }

    }

    /**
     * 更新一行
     * @param $where string |array
     * @param $update array array(array(key=>value),array(key=>value)) || array(array(key -=>value),array(key +=>value))
     * @param $int_field array 整型字段
     * @return bool
     */
    public function update($where, $update, $int_field = array(), $options = array())
    {
        try {
            if (empty($where))
                return false;
            if (empty($update))
                return false;
            if (!is_array($where))
                $where = $this->exec_where($where);
            if (!$this->mogcoll)
                return false;
            $res = $this->mogcoll->update($where, $this->exec_data($update, $int_field), $options);
            if ((isset($res['updatedExisting']) && !empty($res['updatedExisting'])) || (isset($options['upsert']) && $options['upsert'] == true)) {
                return isset($res['nModified']) ? ($res['nModified'] > 0 ? $res['nModified'] : 1) : 1;
            }
            return false;
        } catch (Exception $e) {
            throw new Kernel_Exception_InternalServerError(
                T('update is wrong  msg:{msg}', array('msg' => $e->getMessage()))
            );
        }
    }


    /**
     * 删除
     * @param $where array|string
     * @param bool $del_one 是否删除一行，默认删除一行
     * @return bool
     */
    public function delete($where, $del_one = true)
    {
        try {
            if (empty($where))
                return false;

            if (!is_array($where))
                $where = $this->exec_where($where);
            if (!$this->mogcoll)
                return false;
            $res = $this->mogcoll->remove($where, array("justOne" => $del_one));
            if ($res && !$res['err']) {
                return true;
            }
        } catch (Exception $e) {
            throw new Kernel_Exception_InternalServerError(
                T('delete is wrong  msg:{msg}', array('msg' => $e->getMessage()))
            );
        }
        return false;
    }

    /**
     * 统计值  求和，求平均值，最大值，最小值
     * @param $where
     * @param $field 字段名 'field'|'field=>asname' | 数组 array('field'=>'sum','field=>asname'=>'sum')
     * @param string $type array('sum', 'avg', 'min', 'max') default 'sum'
     * @return array|bool
     */
    public function aggregate($where, $field, $type = 'sum')
    {
        if (!in_array($type, array('sum', 'avg', 'min', 'max'))) {
            return false;
        }

        if (empty($where))
            return false;

        if (empty($field))
            return false;
        $g = array('_id' => 1);
        if (is_array($field)) {
            foreach ($field as $f => $t) {
                if (!in_array($t, array('sum', 'avg', 'min', 'max'))) {
                    $t = $type;
                }
                $ex = explode('=>', $f);
                $g[isset($ex[1]) ? trim($ex[1]) : $ex[0]] = array('$' . $t => '$' . trim($ex[0]));
            }
        } else {
            $field = trim($field);
            $ex = explode('=>', $field);
            $g[isset($ex[1]) ? trim($ex[1]) : $ex[0]] = array('$' . $type => '$' . trim($ex[0]));
        }
        try {
            if (!is_array($where))
                $where = $this->exec_where($where);
            $where = isset($where[0]) ? $where[0] : $where;
            $res = $this->mogcoll->aggregate(
                array('$match' => $where),
                array('$group' => $g)
            );
            if ($res) {
                return $res['result'][0];
            }
        } catch (Exception $e) {
            throw new Kernel_Exception_InternalServerError(
                T('aggregate is wrong  msg:{msg}', array('msg' => $e->getMessage()))
            );
        }
        return false;
    }

    /**
     * 聚合处理获取所有数据
     * @param $where
     * $prototype  要获取的字段
     * @param $field 聚合字段名 'field'|'field=>asname' | 数组 array('field'=>'sum','field=>asname'=>'sum')
     * @param string $type array('sum', 'avg', 'min', 'max') default 'sum'
     * @return array|bool
     */
    public function aggregate_all($where, $prototype, $field, $type = 'sum', $order = array(), $limitinfo = array(), $count = false)
    {
        $pipeline = array();
        if (!in_array($type, array('sum', 'avg', 'min', 'max'))) {
            return false;
        }
        if (empty($field))
            return false;
        if (is_array($field)) {
            foreach ($field as $f => $t) {
                if (!in_array($t, array('sum', 'avg', 'min', 'max'))) {
                    $t = $type;
                }
                $ex = explode('=>', $f);
                $g[isset($ex[1]) ? trim($ex[1]) : $ex[0]] = array('$' . $t => '$' . trim($ex[0]));
            }
        } else {
            $field = trim($field);
            $ex = explode('=>', $field);
            $g[isset($ex[1]) ? trim($ex[1]) : $ex[0]] = array('$' . $type => '$' . trim($ex[0]));
        }
        try {
            if (!is_array($where))
                $where = $this->exec_where($where);
            $where = isset($where[0]) ? $where[0] : $where;
            if (!empty($where))
                $pipeline = array_merge($pipeline, array(array('$match' => $where)));

            if (!empty($order))
                $pipeline = array_merge($pipeline, array(array('$sort' => $order)));
            if (!empty($g)) {
                $g = array_merge($prototype, $g);
                $pipeline = array_merge($pipeline, array(array('$group' => $g)));
            }

            if (!empty($limitinfo)) {
                $limit = isset($limitinfo[1]) ? $limitinfo[1] : 0;
                $skip = isset($limitinfo[0]) ? $limitinfo[0] : 0;
            }
            if (!empty($skip))
                $pipeline = array_merge($pipeline, array(array('$skip' => $skip)));
            if (!empty($limit))
                $pipeline = array_merge($pipeline, array(array('$limit' => $limit)));
            $res = $this->mogcoll->aggregate($pipeline);
            if ($res) {
                $result['num'] = count($res['result']);
                $result['result'] = $res['result'];
                if ($count == true)
                    return $result['num'];
                return $result;
            }
        } catch (Exception $e) {
            throw new Kernel_Exception_InternalServerError(
                T('aggregate_all is wrong  msg:{msg}', array('msg' => $e->getMessage()))
            );
        }
        return false;
    }

    /**
     * 获取最近一个错误
     * @return mixed
     */
    public function get_last_error()
    {
        return $this->mogdbs->lastError();
    }

    /**
     * 获取集合列表
     * @return bool
     */
    private function show_collections()
    {
        if (!$this->mogdbs)
            return false;
        $this->mogdbs->setReadPreference(MongoClient::RP_NEAREST);
        $this->collections = $this->mogdbs->getCollectionNames();
//        $this->mogdbs->getCollectionInfo();
        return true;
    }

    /**
     * 获取索引
     * @return bool
     */
    public function get_index()
    {
        try {
            return $this->mogcoll->getIndexInfo();
        } catch (Exception $e) {
            throw new Kernel_Exception_InternalServerError(
                T('get_index is wrong  msg:{msg}', array('msg' => $e->getMessage()))
            );
        }
    }

    /**
     * 创建索引
     * @param $field string 索引字段
     * @param bool $unique 创建唯一索引
     * @param int $drop_dups 唯一索引时，如果有重复数据是否删除
     * @return bool
     */
    public function create_index($field, $unique = false, $drop_dups = 1)
    {
        try {
            $options = array();
            if ($unique) {
                $options = array('unique' => intval($unique), 'dropDups' => $drop_dups);
            }
            if (!$this->mogcoll)
                return false;
            return $this->mogcoll->createIndex(array($field => 1), $options);
        } catch (Exception $e) {
            throw new Kernel_Exception_InternalServerError(
                T('create_index is wrong  msg:{msg}', array('msg' => $e->getMessage()))
            );
        }
    }

    /**
     * 处理更新数据
     * @param $data
     * @param $int_field array 整型字段
     * @return array
     */
    private function exec_data($data, $int_field = array())
    {
        if (empty($data))
            return false;
        $return = array();
        $set = $seti = array();
        $inc = array();
        $mul = array();
        foreach ($data as $key => $val) {
            $a = explode(' ', $key);
            if (!isset($a[1])) {
                if (in_array($key, $int_field))
                    $val = intval($val);
                $set[$key] = $val;
            } else {
                switch (trim($a[1])) {
                    case '=':
                        $seti = $val;
                        break;
                    case '-':
                        if (is_array($val)) {
                            foreach ($val as $k => $v) {
                                $inc[$k] = -($v + 0);
                            }

                        } else {
                            $val = $val + 0;
                            $val = -$val;
                            $inc[$a[0]] = $val;
                        }
                        break;
                    case '+':
                        if (is_array($val)) {
                            foreach ($val as $k => $v) {
                                $inc[$k] = $v + 0;
                            }
                        } else {
                            $val = $val + 0;
                            $inc[$a[0]] = $val;
                        }
                        break;
                    case '/':
                        $val = $val + 0;
                        $val = 1 / $val;
                    case '*':
                        $val = $val + 0;
                        $mul[$a[0]] = $val;
                        break;
                    case '#':
                        $push = $val;
                        break;
                    case '!':
                        $unset = $val;
                        break;
                }
            }
        }
        $set = array_merge($set, $seti);
        if (!empty($set))
            $return['$set'] = $set;
        if (!empty($inc))
            $return['$inc'] = $inc;
        if (!empty($mul))
            $return['$set'] = $mul;
        if (!empty($push))
            $return['$push'] = $push;
        if (!empty($unset))
            $return['$unset'] = $unset;
        return $return;
    }

    /**
     * 处理where条件
     * @param $where
     * @return array|bool
     */
    public function exec_where($where)
    {
        // 替换\t\r和空格
        $where = preg_replace("/\r\n|\t| +/", ' ', $where);
        //echo $sql . "\r\n";
        $rxarr = array(
            "/(\b[a-zA-Z]+\b between +[0-9]+ +and +[0-9]+)/i",
            "/'(([^']*?)(\\\')?)*?'/",
            "/\([^\(\)]*?\)/"
        );

        $matchs = array();
        $where = preg_replace_callback($rxarr, function ($matches) use (&$matchs) {
            $matchs[base64_encode($matches[0])] = $matches[0];
            return base64_encode($matches[0]);
        }, $where);
        //多层（）处理
        while (true) {
            if (preg_match("/\([^\(\)]*?\)/", $where)) {
                $where = preg_replace_callback($rxarr, function ($matches) use (&$matchs) {
                    $matchs[base64_encode($matches[0])] = $matches[0];
                    return base64_encode($matches[0]);
                }, $where);
            } else {
                break;
            }
        }

        return $this->exec_where_helper($where, $matchs);
    }

    /**
     * exec_where的辅助方法
     * @param $where
     * @param $matchs
     * @return array|bool
     */
    private function exec_where_helper($where, $matchs)
    {
        $array = array();
        $prev = 0;
        $arr = preg_split('/ +(and) +| +(or) +| +(orderby) +/i', $where, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($arr as $key => $val) {
            $val = $this->format_val_helper($val, $matchs);

            if (preg_match("/(\b[a-zA-Z]+\b) between +([0-9]+) +and +([0-9]+)/i", $val, $m)) {
                $array[trim($m[1])] = array('$gt' => $m[2] + 0, '$lt' => $m[3] + 0);
                continue;
            }
            switch (strtolower($val)) {
                case 'and':
                    $prev = '$and';
                    break;
                case 'orderby':
                    $prev = '$orderby';
                    break;
                case 'or':
                    $prev = '$or';
                    break;
                default:
                    if (preg_match("/ +(and) +| +(or) +/i", $val, $matches)) {
                        $array = array_merge_recursive($array, array($this->exec_where_helper($val, $matchs)));
                        continue;
                    }

                    $n = preg_match_all("/(\>)|(\<)|(\<\=)|(\>\=)|(\=)|(\bin\b)|(\bnot in\b)|(\blike\b)|(\bnot like\b)/i", $val, $matches);
                    if ($n) {
                        $s = explode($matches[0][0], $val, 2);
                        $v = trim($s[1]);
                        $k = trim($s[0]);
                        switch (strtolower($matches[0][0])) {
                            case '=':
                                $re = $this->format_val_helper(trim($v), $matchs);

                                if (!preg_match("/['|\"]([^\'|\"]+)['|\"]/", $re)) {
                                    $array[][$k] = $re + 0;
                                } else {
                                    $array[][$k] = trim($re, "'\"");
                                }

                                break;
                            case '!=':
                            case '>':
                            case '<':
                            case '<=':
                            case '>=':
                                $rv = $this->format_val_helper(trim($v), $matchs);
                                if (!preg_match("/['|\"]([^\'|\"]+)['|\"]/", $rv)) {
                                    $rv = $rv + 0;
                                } else {
                                    $rv = trim($rv, "'\"");
                                }
                                $array[$this->where_map[strtolower($matches[0][0])]][] = array($array[$k] => $rv);
                                break;
                            case 'in':
                            case 'not in':
                                $e = explode(',', $this->format_val_helper($v, $matchs));
                                $e = array_map(function ($val) use ($matchs) {
                                    $val = $this->format_val_helper($val, $matchs);
                                    if (!preg_match("/['|\"]([^\'|\"]+)['|\"]/", $val)) {
                                        $val = $val + 0;
                                    } else {
                                        $val = trim($val, "'\"");
                                    }
                                    return $val;
                                }, $e);
                                $array[$k][$this->where_map[strtolower($matches[0][0])]] = $e;
                                break;
                            case 'like':
                            case 'not like':
                                $v = trim($this->format_val_helper($v, $matchs), "'\"");
                                $start = $v[0];
                                $end = $v[strlen($v) - 1];
                                $v = trim($v, '%');
                                if ($start == '%') {
                                    $m = $v;
                                } else {
                                    $m = '^' . $v;
                                }

                                if ($end == '%') {
                                    $m .= '';
                                } else {
                                    $m .= '$';
                                }
                                $array[$k] = new MongoRegex("/" . $m . "/");
                                break;
                        }
                    } else {
                        $array[] = $this->exec_where_helper($val, $matchs);
                    }
                    break;
            }
        }
        if ($prev === 0) {
            if (isset($array[0]))
                return $array[0];
            return $array;
        }
        if ($prev === '$or') {
            return array('$or' => $array);
        } elseif ($prev === '$and') {
            $res = array();
            foreach ($array as $key => $val) {
                if (is_int($key))
                    $res = array_merge($res, $val);
                else
                    $res[$key] = $val;
            }
            return $res;
        }
        return false;
    }


    private function format_val_helper($key, $array)
    {

        $key = trim($key, "\(\)");
        while (true) {
            if (isset($array[$key])) {
                $key = trim($array[$key], "\(\)");
            } else {
                return $key;
            }
        }
        return false;
    }

}