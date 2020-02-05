<?php
/**
 * 模型父类
 * 1.单例模式设计
 * 2.实现数据库基本操作
 * 作者：2daye
 */
namespace core\plugin;

use core\tool\Tool;

class Model
{
    //存放数据库连接句柄
    private $db = false;

    //sql拼接参数
    public $table = '';
    public $field = '*';
    public $data = [];
    public $order = '';
    public $group = '';
    public $having = '';
    public $limit = '';
    public $where = [];
    public $join = [];
    public $isJoin = false;
    public $fetchSql = false;

    //sql绑定查询的参数
    public $parameter = [];

    //缓存参数
    public $cache = [];

    //存放实例化的对象
    private static $instance;

    /**
     * 静态单例模式
     * @return Model
     */
    public static function getInstance()
    {
        /**
         * 通过使用 instanceof操作符 和 self关键字 ，
         * 可以检测到类是否已经被实例化，
         * 如果 $instance 没有保存，类本身的实例。
         */
        if (!(self::$instance instanceof self)) {
            //就把本身的实例赋给 $instance
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 私有构造函数，防止继承，实现单例
     * Model constructor.
     * @throws \Exception
     */
    private function __construct()
    {

    }

    //单例防止克隆
    private function __clone()
    {
    }

    /**
     * 数据库插入方法
     * @param array|null $array
     * @return array|int
     * @throws \Exception
     */
    public function insert(array $array = [])
    {
        //sql执行前初始化
        $this->sqlInit('insert', $array);
        //获取字段和值
        $field = $this->data['field'];
        $value = $this->data['value'];
        //编译sql
        $sql = "INSERT INTO " . $this->table . "(" . $field . ") VALUES (" . $value . ")";
        //执行sql返回结果
        return $this->performSql($sql, 0);
    }

    /**
     * 数据库删除方法
     * @param bool $where
     * @return array|int
     * @throws \Exception
     */
    public function delete($where = false)
    {
        //sql执行前初始化
        $this->sqlInit('delete');
        /**
         * 当删除数据的时候
         * 如果where条件为空
         * 那必须在delete方法里面传入true才能执行整个数据表的删除
         */
        if (!count($this->where) && $where) {
            $w = '';
        } elseif (count($this->where) > 0) {
            $w = implode(' ', $this->where);
        } else {
            throw new \Exception('删除指令终止，没有where条件，异常危险的删除操作，请检查确认');
        }
        //编译sql
        $sql = "DELETE FROM " . $this->table . $w;
        //执行sql返回结果
        return $this->performSql($sql, 0);
    }

    /**
     * 数据库更新操作
     * @param array|null $array
     * @return array|int
     * @throws \Exception
     */
    public function update(array $array = [])
    {
        //sql执行前初始化
        $this->sqlInit('update', $array);
        //获取字段和值
        $field = explode(',', $this->data['field']);
        $value = explode(',', $this->data['value']);
        //定义update字符串
        $u = '';
        foreach ($field as $key => $values) {
            $u .= $values . ' = ' . $value[$key] . ',';
        }
        //去除update参数的尾部的，号
        $u_val = trim($u, ',');
        //编译sql
        $sql = "UPDATE " . $this->table . " SET " . $u_val . implode(' ', $this->where);
        //执行sql返回结果
        return $this->performSql($sql, 0);
    }

    /**
     * 数据库查询操作
     * @param bool $result_type //查询结果 true 返回结果集，false 返回结果条数
     * @return array|int
     * @throws \Exception
     */
    public function select($resultType = true)
    {
        //sql执行前初始化
        $this->sqlInit('select');
        //编译sql
        $sql = "SELECT " . $this->field . " FROM " . $this->table . implode(' ', $this->join) . implode(' ', $this->where) . $this->order . $this->limit;
        //判断是否使用缓存
        if (count($this->cache) > 0) {
            $cache = new Cache();
            $key = $this->cache['key'] !== false ? $this->cache['key'] : md5($sql . implode(',', $this->parameter));
            $data = $cache->get($key);
            if ($data !== false) {
                //重置缓存参数
                $this->cache = [];
                //重置全部条件参数
                $this->resetCondition();
                //返回缓存数据
                return $data;
            } else {
                //执行sql返回结果
                $result = $this->performSql($sql, ($resultType ? 1 : 0));
                $cache->set($key, $result, $this->cache['ttl']);
                $this->cache = [];
                return $result;
            }
        } else {
            //执行sql返回结果
            return $this->performSql($sql, ($resultType ? 1 : 0));
        }
    }

    /**
     * sql初始化
     * @param $operation //哪种sql
     * @param array $array //执行数据
     * @return bool
     * @throws \Exception
     */
    public function sqlInit($operation, $array = [])
    {
        //判断是否传入表名
        if ('' == $this->table) {
            throw new \Exception('error：必须传入要操作的表');
        }
        //执行每种不同的sql的，不同操作
        switch ($operation) {
            case 'insert':
            case 'update':
                //如果update的数组有值，优先使用
                if (count($array)) {
                    $this->data($array);
                } else {
                    if (0 === count($this->data)) {
                        throw new \Exception('error：没有传入可执行的数据，请检查');
                    }
                }
                break;
            case 'delete':
            case 'select':
                //删除查询暂无前置特别处理
                break;
        }
        return true;
    }

    /**
     * 执行sql返回结果
     * @param $sql //sql语句
     * @param int $resultType //执行后返回的类型
     * @param bool $data //默认返回值
     * @return array|bool|int
     * @throws \Exception
     */
    public function performSql($sql, $resultType = 0, $data = false)
    {
        //判断是否连接数据库
        if (false === $this->db) {
            //获取数据库配置
            $dbConfig = \core\plugin\Config::get_all('db');
            //开始尝试连接数据库
            try {
                $this->db = new \PDO('mysql:dbname=' . $dbConfig['DB_NAME'] . ';port=' . $dbConfig['DB_PORT_NUMBER'] . ';host=' . $dbConfig['DB_HOST'] . ';', $dbConfig['DB_USER'], $dbConfig['DB_PASSWORD']);
                $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                /**
                 * 注意当使用PDO访问MySQL数据库真正的预备义语句并不是默认使用的！
                 * 为了解决这个问题，必须禁用仿真准备好的语句。使用PDO创建连接的，如下
                 * 设置PDO setAttribute(\PDO::ATTR_EMULATE_PREPARES, false)
                 * 防止数据库字段属性是int，查出来变成string的，1/'1'
                 */
                $this->db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
                //申明数据库编码UTF-8
                $this->db->exec('set names utf8');
            } catch (\PDOException $e) {
                //连接失败输出错误
                throw new \Exception('数据库连接失败：' . $e->getMessage());
            }
        }

        //是否直接返回sql
        if ($this->fetchSql) {
            return ['parameter' => $this->parameter, 'sql' => $sql];
        }
        //传入sql执行
        $result = $this->db->prepare($sql);
        //传入查询参数
        $result->execute($this->parameter);
        //执行sql
        switch ($resultType) {
            case 0:
                //返回数据库受影响行数，以int返回
                $data = $result->rowCount();
                break;
            case 1:
                //返回结果集，使用PDO::FETCH_ASSOC，以数组返回
                $data = array();
                while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                    $data[] = $row;
                }
                break;
        }
        //重置全部条件参数
        $this->resetCondition();
        //返回结果
        return $data;
    }

    /**
     * 清空数据表，自增id重置
     * @return int
     */
    public function truncate()
    {
        //sql执行前初始化
        $this->sqlInit('truncate');
        //编译sql
        $sql = "truncate " . $this->table;
        //执行sql返回结果
        return $this->performSql($sql);
    }

    /**
     * 返回最后插入行的ID或序列值(当前线程)
     * @return string
     */
    public function lastId()
    {
        return $this->db->lastInsertId();
    }

    /**
     * 锁定表 write
     * @return int
     */
    public function lockTable()
    {
        //sql执行前初始化
        $this->sqlInit('lockTable');
        //编译sql
        $sql = "lock tables " . $this->table . " write";
        //执行sql返回结果
        return $this->db->exec($sql);
    }

    /**
     * 解锁表
     * @return int
     */
    public function unlock()
    {
        return $this->db->exec('unlock tables');
    }

    /**
     * 开启事务处理
     * @return int
     */
    public function openTransaction()
    {
        return $this->db->exec('begin');
    }

    /**
     * 提交事务
     * @return int
     */
    public function commit()
    {
        return $this->db->exec('commit');
    }

    /**
     * 事务回滚
     * @return int
     */
    public function rollBack()
    {
        return $this->db->exec('ROLLBACK');
    }

    /**
     * 传入表名
     * @param $value
     */
    public function table($value)
    {
        $this->table = $value;
        return $this;
    }

    /**
     * 传入要查询的字段
     * @param $value
     */
    public function field($field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * 数据处理
     * @param array $array //字段和值数组
     * @return $this
     */
    public function data(array $array)
    {
        //定义值的数组
        $val = array();
        //定义字段数组
        $field = array();
        //循环处理字段数组和值数组
        foreach ($array as $key => $value) {
            //去除冒号得到字段
            $field[] = $key;
            //拼接SQL绑定参数占位符数组
            $val[] = $this->setParameter($key, $value);
        }
        //implode()函数把数组按(,号)分成字符串
        $vals = implode(",", $val);
        $fields = implode(",", $field);
        //处理后的字段数组和值数组存入data数据
        $this->data = ['field' => $fields, 'value' => $vals];
        //返回本身
        return $this;
    }

    /**
     * 处理where条件
     * @param $where //字符串：'id'，数组：['id' => 1, 'name' => '2daye', 'state' => [1, 2, 3]]
     * @param null $expression //字符串：'='/'<>'/'like'/'null'/
     * @param null $condition //字符串：'2daye'，数组：['2019', '2020']
     * @param null $link //字符串：/'OR'/'AND'
     * @return Model
     */
    public function where($where, $expression = null, $condition = null, $link = null)
    {
        //执行处理where条件，使用func_get_args()函数，获取函数参数列表的数组
        return $this->handlingWhere($where, $expression, $condition, $link, func_get_args(), 'AND');
    }

    /**
     * 拼接OR where条件
     * @param $where //字符串：'id'，数组：['id' => 1, 'name' => '2daye', 'state' => [1, 2, 3]]
     * @param null $expression //字符串：'='/'<>'/'like'/'null'/
     * @param null $condition //字符串：'2daye'，数组：['2019', '2020']
     * @param null $link //字符串：/'OR'/'AND'
     * @return Model
     */
    public function where_or($where, $expression = null, $condition = null, $link = null)
    {
        //执行处理where条件，使用func_get_args()函数，获取函数参数列表的数组
        return $this->handlingWhere($where, $expression, $condition, $link, func_get_args(), 'OR');
    }

    /**
     * 处理where条件逻辑
     * @param $where //条件可以是字符串，数组 'id' ['id'=>1,'name'=>'2daye']
     * @param $expression //可以是表达式，可以是值
     * @param $condition //值，字符串或数组
     * @param $link //链接字符串，OR或者AND
     * @param $parameters //参数个数
     * @param $head //条件头部连接
     * @param $andOr //条件连接
     * @return $this
     * @throws \Exception
     */
    public function handlingWhere($where, $expression, $condition, $link, $whereParameter, $andOr)
    {
        //根据where数组个数判断条件连接头是AND/OR还是WHERE
        $head = count($this->where) > 0 ? $andOr . ' (' : ' WHERE (';
        /**
         * 判断传入方法参数的个数
         * 1个参数就执行，(where条件数组处理)(如果不是数组直接执行字符串where条件操作)
         * 2个参数就执行，(字段 = 条件)
         * 3个参数就执行，(字段 表达式 条件)
         */
        switch (count($whereParameter)) {
            case 1:
                //判断是否是数组
                if (is_array($where)) {
                    //是数组进行解析
                    $i = false;
                    foreach ($where as $key => $value) {
                        //判断条件是否是数组
                        if (is_array($value)) {
                            //是数组就处理成IN(1,2,3)来进行查询
                            $p = '';
                            foreach ($value as $k => $v) {
                                $p .= $this->setParameter($key, $v) . ',';
                            }
                            $this->where[] = ($i ? $andOr . ' ' : $head) . $key . ' IN (' . trim($p, ',') . ')';
                        } else {
                            //不是数组直接处理为 字段 = 条件
                            $this->where[] = ($i ? $andOr . ' ' : $head) . $key . ' = ' . $this->setParameter($key, $value);
                        }
                        $i = true;
                    }
                } else {
                    //不是数组直接输where条件
                    $this->where[] = $head . $where;
                }
                break;
            case 2:
                /**
                 * 判断参数是否是特殊值
                 * 1.null 处理字段 is null
                 * 2.not null 处理字段 is not null
                 * 3.不是特殊值，则处理为 字段 = 值
                 */
                switch ($expression) {
                    /*case null:*/
                    case 'null':
                        $this->where[] = $head . $where . ' IS NULL';
                        break;
                    case 'not null':
                        $this->where[] = $head . $where . ' IS NOT NULL';
                        break;
                    default:
                        $this->where[] = $head . $where . ' = ' . $this->setParameter($where, $expression);
                }
                break;
            case 3:
            case 4:
            case 5:
                switch ($expression) {
                    case '=':
                    case '<>':
                    case '>':
                    case '<':
                    case '>=':
                    case '<=':
                        $this->where[] = $head . $where . ' ' . $expression . ' ' . $this->setParameter($where, $condition);
                        break;
                    case 'like':
                        if (is_array($condition)) {
                            //多个like搜索条件如果有传拼接方法就使用传入的否则默认AND
                            $k = isset($link) ? $link : 'AND';
                            //循环拼接搜索条件，第四个参数$link，
                            $search = '';
                            foreach ($condition as $key => $value) {
                                $parameter = $this->setParameter($where, $value);
                                $search .= ' ' . $k . ' ' . $where . ' LIKE "' . $parameter . '"';
                            }
                            $search = trim($search, ' ' . $k . ' ');
                            $this->where[] = $head . $search;
                        } else {
                            $parameter = $this->setParameter($expression, $condition);
                            $this->where[] = $head . ' ' . $where . ' LIKE "' . $parameter . '"';
                        }
                        break;
                    case 'between':
                    case 'not between':
                        if (!is_array($condition)) {
                            throw new \Exception('between条件异常，请检查第三个参数，标准参数格式，数组[2019,2020]');
                        }
                        $parameter = $this->setParameter($where, $condition[0]);
                        $parameters = $this->setParameter($where, $condition[1]);
                        $between = $expression == 'between' ? ' BETWEEN ' : ' NOT BETWEEN ';
                        $this->where[] = $head . $where . $between . $parameter . ' AND ' . $parameters;
                        break;
                    case 'in':
                        if (!is_array($condition)) {
                            throw new \Exception('in条件异常，请检查第三个参数，标准参数格式，数组[1, 2, 3]');
                        }
                        $inCondition = '';
                        foreach ($condition as $key => $value) {
                            $inCondition .= $this->setParameter($where, $condition[$key]) . ',';
                        }
                        $inCondition = trim($inCondition, ',');
                        $this->where[] = $head . $where . ' IN (' . $inCondition . ')';
                        break;
                    default:
                        $this->where[] = $head . $where . ' = ' . $this->setParameter($where, $expression);
                }
                break;
        }
        /**
         * where条件组括号收尾处理
         * 先获取where条件数组最后一位
         * 在获取where最后一位的key，索引
         * 拼接括号
         */
        $element = end($this->where);
        $key = key($this->where);
        $this->where[$key] = $element . ')';
        return $this;
    }

    /**
     * ORDER BY 查询数据字段排序
     * @param $field //排序字段
     * @param string $way //排序方式，默认asc
     * @return $this
     */
    public function order($field, $way = 'asc')
    {
        //判断是否是数组
        if (is_array($field)) {
            $f = '';
            foreach ($field as $value) {
                $f .= $value . ',';
            }
            $o = ' ORDER BY ' . trim($f, ',') . ' ' . $way . ' ';
        } else {
            $o = ' ORDER BY ' . $field . ' ' . $way . ' ';
        }
        $this->order = $o;
        return $this;
    }

    /**
     * GROUP BY 一个或多个列的结果进行分组
     * @param $field //分组字段
     * @return $this
     */
    public function group($field)
    {
        $this->group = ' GROUP BY ' . $field;
        return $this;
    }

    /**
     * having方法
     * 配合group方法完成从分组的结果中筛选数据
     * @param string $condition //筛选条件
     * @return $this
     */
    public function having($condition)
    {
        $this->having = ' HAVING ' . $condition;
        return $this;
    }

    /**
     * limit指定查询数量
     * @param $start //开始位置
     * @param null $length //查询长度
     * @return $this
     */
    public function limit($start, $length = null)
    {
        //使用func_get_args()函数，获取方法传入的函数参数列表的数组
        switch (count(func_get_args())) {
            case 1:
                $this->limit = ' limit ' . $start;
                break;
            case 2:
                $this->limit = ' limit ' . $start . ',' . $length;
                break;
        }
        return $this;
    }

    /**
     * 处理SQL join操作
     * 表中有至少一个匹配，则返回行
     * @param $table
     * @param $on
     * @param string $type
     */
    public function join($table, $on, $type = 'INNER')
    {
        $type = ' ' . $type . ' JOIN ';
        if (is_array($table)) {
            $this->join[] = $type . $table[0] . ' as ' . $table[1] . ' ON ' . $on;
        } else {
            $this->join[] = $type . $table . ' ON ' . $on;
        }
        $this->isJoin = true;
        return $this;
    }

    /**
     * 处理SQL left join操作
     * 即使右表中没有匹配，也从左表返回所有的行
     * @param $table
     * @param $on
     */
    public function leftJoin($table, $on)
    {
        return $this->join($table, $on, 'LEFT');
    }

    /**
     * 处理SQL right join操作
     * 即使左表中没有匹配，也从右表返回所有的行
     * @param $table
     * @param $on
     */
    public function rightJoin($table, $on)
    {
        return $this->join($table, $on, 'RIGHT');
    }

    /**
     * 处理SQL full join操作
     * 只要其中一个表中存在匹配，就返回行
     * @param $table
     * @param $on
     */
    public function fullJoin($table, $on)
    {
        return $this->join($table, $on, 'FULL');
    }

    /**
     * SQL调试
     * @param bool $is
     * @return $this
     */
    public function fetchSql($is = false)
    {
        $this->fetchSql = $is;
        return $this;
    }

    /**
     * 重置SQL参数
     */
    public function resetCondition()
    {
        $this->table = '';
        $this->field = '*';
        $this->data = [];
        $this->order = '';
        $this->group = '';
        $this->having = '';
        $this->limit = '';
        $this->where = [];
        $this->join = [];
        $this->isJoin = false;
        $this->fetchSql = false;
        $this->parameter = [];
    }

    /**
     * 设置绑定参数
     * @param $field //字段
     * @param $value //值
     * @return string //返回绑定参数的key
     */
    public function setParameter($field, $value)
    {
        //如果当前是连表查询，就会存在user.id这种字段，这种字段作为key会报错，所以我们就取原始的字段
        if ($this->isJoin) {
            $field = explode('.', $field);
            $field = end($field);
        }
        //获取绑定参数数组当前的个数
        $currentNumber = count($this->parameter);
        //如果，有多个参数 或 key已经存在绑定参数数组，就进行去重复处理
        if ($currentNumber > 0 || array_key_exists(':' . $field, $this->parameter)) {
            //把绑定参数数组的长度传入$i，这样可以避免算法循环多次判定都出现重复
            $i = $currentNumber;
            while (true) {
                $i++;
                if (!array_key_exists(':' . $field . $i, $this->parameter)) {
                    $field = ':' . $field . $i;
                    break;
                }
            }
        } else {
            $field = ':' . $field;
        }
        $this->parameter[$field] = $value;
        return $field;
    }

    /**
     * 查询缓存
     * @param $key
     * @param null $ttl
     * @return $this
     */
    public function cache($key, $ttl = null)
    {
        switch (count(func_get_args())) {
            case 1:
                if ($key === true) {
                    $this->cache = ['key' => false, 'ttl' => $ttl];
                } else {
                    $this->cache = ['key' => false, 'ttl' => $key];
                }
                break;
            case 2:
                $this->cache = ['key' => $key, 'ttl' => $ttl];
                break;
        }
        return $this;
    }
}
