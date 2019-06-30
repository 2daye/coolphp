<?php
/**
 * 模型父类
 * 1.单例模式设计
 * 2.实现数据库基本操作方法
 * 作者：2daye
 * 时间：2019年 3月28日
 */

namespace core\plugin;

use core\tool\Tool;

class Model
{
    //$db 存放数据库连接句柄
    private $db = false;
    //SQL 拼接参数
    private $table = null;
    private $field = '*';
    private $data = null;
    private $order = '';
    private $group = '';
    private $having = '';
    private $limit = '';
    private $where = [];
    private $join = [];
    private $is_join = false;
    private $parameter = null;
    //$instance 用于存放实例化的对象
    private static $instance;

    /**
     * 静态单例模式
     * @return Model
     */
    public static function get_instance()
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
     * 私有构造函数，实现单例
     * @throws \Exception
     */
    private function __construct()
    {
        if (false === $this->db) {
            //载入配置数据库配置文件
            $dbconfig = \core\plugin\Config::get_all('db');
            //连接数据库
            try {
                $this->db = new \PDO('mysql:dbname=' . $dbconfig['DB_NAME'] . ';port=' . $dbconfig['DB_PORT_NUMBER'] . ';host=' . $dbconfig['DB_HOST'] . ';', $dbconfig['DB_USER'], $dbconfig['DB_PASSWORD']);
                $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                /**
                 * 注意当使用PDO访问MySQL数据库真正的预备义语句并不是默认使用的！
                 * 为了解决这个问题，必须禁用仿真准备好的语句。使用PDO创建连接的，如下
                 * 设置PDO setAttribute(\PDO::ATTR_EMULATE_PREPARES, false)
                 * 防止数据库字段属性是int，查出来变成string的， 1和'1'
                 */
                $this->db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
                //申明数据库编码 UTF8
                $this->db->exec('set names utf8');
            } catch (\PDOException $e) {
                throw new \Exception('数据库连接失败：' . $e->getMessage());
            }
        }
    }

    //单例防止克隆
    private function __clone()
    {
    }

    /**
     * 数据库插入方法
     * @param array|null $array //要插入的字段和值
     * @return int
     * @throws \Exception
     */
    public function insert(array $array = null)
    {
        //判断是否传入表名
        if ($this->table === null) {
            throw new \Exception('请传入要插入数据的表');
        }
        //判断要插入的数据是否组装完成
        if (null === $array) {
            if (null === $this->data) {
                throw new \Exception('请传入要插入的数据');
            }
        } else {
            $this->data($array);
        }
        //获取字段和值
        $field = $this->data['field'];
        $value = $this->data['value'];
        //编译sql
        $_sql = "insert into " . $this->table . "(" . $field . ") values (" . $value . ")";
        //执行返回结果
        return $this->cud($_sql, $this->parameter);
    }

    /**
     * 数据库删除方法
     * @param bool $where //判断条件是否存在
     * @return int
     * @throws \Exception
     */
    public function delete($where = false)
    {
        //判断是否传入表名
        if ($this->table === null) {
            throw new \Exception('请传入要插入数据的表');
        }
        if (0 === count($where) && $where === true) {
            $w = '';
        } else {
            $w = $this->where;
        }
        //编译sql
        $_sql = "delete from " . $this->table . $w;
        //执行返回结果
        return $this->cud($_sql, $this->parameter);
    }

    /**
     * 数据库更新操作
     * @param array|null $array //要更新的字段和参数
     * @return int
     * @throws \Exception
     */
    public function update(array $array = null)
    {
        //判断是否传入表名
        if ($this->table === null) {
            throw new \Exception('请传入要插入数据的表');
        }
        //判断要插入的数据是否组装完成
        if (null === $array) {
            if (null === $this->data) {
                throw new \Exception('请传入要更新的数据');
            }
        } else {
            $this->data($array);
        }
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
        $_sql = "update " . $this->table . " set " . $u_val . $this->where;
        //执行返回结果
        return $this->cud($_sql, $this->parameter);
    }

    /**
     * 数据库查询操作
     * @param bool $result_type //查询结果 true 返回结果集，false 返回结果条数
     * @return array|int
     * @throws \Exception
     */
    public function select($result_type = true)
    {
        //判断是否传入表名
        if ($this->table === null) {
            throw new \Exception('请传入要插入数据的表');
        }
        //编译sql
        $_sql = "select " . $this->field . " from " . $this->table . implode(' ', $this->join) . implode(' ', $this->where) . $this->order . $this->limit;
        //Tool::p($this->parameter);
        //Tool::p($_sql, true);
        //执行返回结果
        return $result_type ? $this->get_all_result($_sql, $this->parameter) : $this->get_all_number($_sql, $this->parameter);
    }

    /**
     * 数据库(增(Create)，改(Update)，删(Delete))操作
     * @param string $_sql //传入sql语句
     * @param array $parameter //绑定参数数组
     * @return int //数据库影响条数
     */
    public function cud($_sql, array $parameter)
    {
        //传入sql执行
        $results = $this->db->prepare($_sql);
        //传入查询参数
        $results->execute($parameter);
        //返回数据库受影响行数，int型
        $ImpactNumber = $results->rowCount();
        //返回结果
        return $ImpactNumber;
    }

    /**
     * 获得数据库查询的全部结果
     * @param string $_sql //传入sql语句
     * @param array $parameter //绑定参数数组
     * @param int $type //返回查询结果的类型 0 => 结果数组 1 => 结果数量
     * @return array|int
     * @throws \Exception
     */
    public function get_all_result($_sql, array $parameter, $type = 0)
    {
        //传入sql执行
        $results = $this->db->prepare($_sql);
        //传入查询参数
        $results->execute($parameter);
        //判断返回类型
        switch ($type) {
            //使用PDO::FETCH_ASSOC，返回结果集，以数组形式返回
            case 0:
                $data = array();
                while ($row = $results->fetch(\PDO::FETCH_ASSOC)) {
                    $data[] = $row;
                }
                break;
            //获取条数，返回int型
            case 1:
                $data = $results->rowCount();
                break;
            default:
                throw new \Exception('请传入正确的返回类型 $type 0 => 结果数组 1 => 结果数量');
        }
        //返回结果
        return $data;
    }

    /**
     * 清空数据表，自增id重置
     * @param string $table //要清空的表
     * @return int
     */
    public function truncate($table)
    {
        $_sql = "truncate " . $table;
        //执行返回结果
        return $this->cud($_sql, array());
    }

    /**
     * 获取数据库当前线程最后插入数据的自增id
     * @return string
     */
    public function last_id()
    {
        return $this->db->lastInsertId();
    }

    /**
     * 锁定表
     * @param $table //要锁的表
     * @return int
     */
    public function lock_table($table)
    {
        $_sql = "lock tables " . $table . " write";
        $result = $this->db->exec($_sql);
        return $result;
    }

    /**
     * 解锁表
     * @return int
     */
    public function unlock()
    {
        $_sql = "unlock tables";
        $result = $this->db->exec($_sql);
        return $result;
    }

    /**
     * 开启事务处理
     * @return int
     */
    public function open_transaction()
    {
        $_sql = "begin";
        $result = $this->db->exec($_sql);
        return $result;
    }

    /**
     * 成功提交事务
     * @return int
     */
    public function commit()
    {
        $_sql = "commit";
        $result = $this->db->exec($_sql);
        return $result;
    }

    /**
     * 事务回滚
     * @return int
     */
    public function roll_back()
    {
        $_sql = "ROLLBACK";
        $result = $this->db->exec($_sql);
        return $result;
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
            $field[] = trim($key, ':');
            //拼接SQL绑定参数占位符数组
            $val[] = $key;
            //传入绑定参数数组
            $this->parameter[$key] = $value;
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
     * [':UserId' => ['UserId' => 1],':PassWord'=>['PassWord' => 123456]]
     * @param $where
     * @param null $expression
     * @param null $condition
     * @param string $link
     * @return $this
     */
    public function where($where, $expression = null, $condition = null, $link = null)
    {
        $type = 'AND';
        /**
         * 获取方法传入的参数
         * 使用func_get_args()函数
         */
        $parameters = func_get_args();
        //判断where数组是否是第一个where条件，第一个where条件不需要加AND
        $AND = count($this->where) != 0 ? ' ' . $type . ' (' : ' WHERE (';
        return $this->handling_where($where, $expression, $condition, $link, $parameters, $AND, $type = 'AND');
    }

    public function where_or($where, $expression = null, $condition = null, $link = null)
    {
        $type = 'OR';
        /**
         * 获取方法传入的参数
         * 使用func_get_args()函数
         */
        $parameters = func_get_args();
        //判断where数组是否是第一个where条件，第一个where条件不需要加AND
        $AND = count($this->where) != 0 ? ' ' . $type . ' (' : ' WHERE (';
        return $this->handling_where($where, $expression, $condition, $link, $parameters, $AND, $type);
    }

    public function handling_where($where, $expression, $condition, $link, $parameters, $AND, $type)
    {
        /**
         * 判断传入方法参数的个数
         * 1个参数就执行，(where条件数组处理)(如果不是数组直接执行字符串where条件操作)
         * 2个参数就执行，(字段 = 条件)
         * 3个参数就执行，(字段 表达式 条件)
         */
        switch (count($parameters)) {
            case 1:
                //判断是否是数组
                if (is_array($where)) {
                    //是数组进行解析
                    $i = 0;
                    foreach ($where as $key => $value) {
                        //判断条件是否是数组
                        if (is_array($value)) {
                            //是数组处理为 字段 in(1,2,3)
                            $p = '';
                            foreach ($value as $k => $v) {
                                $parameter = $this->is_join ? $this->get_parameter($key) : $key;
                                $p .= $parameter . $k . ',';
                                $this->parameter[$parameter . $k] = $v;
                            }
                            $this->where[] = $AND . trim($key, ':') . ' IN (' . trim($p, ',') . ')';
                        } else {
                            //不是数组直接处理为 字段 = 条件
                            if ($i > 0) {
                                $AND = ' ' . $type . ' ';
                            }
                            $parameter = $this->is_join ? $this->get_parameter($key) : $key;
                            $this->where[] = $AND . trim($key, ':') . ' = ' . $parameter;
                            $this->parameter[$parameter] = $value;
                            $i++;
                        }
                    }
                } else {
                    //不是数组直接输where条件
                    $this->where[] = $AND . $where;
                }
                $this->where[] = ')';
                break;
            case 2:
                /**
                 * 判断参数是否是特殊值
                 * 1.null和'null' 处理字段 is null
                 * 2.'not null' 处理字段 is not null
                 * 3.不是特殊值，则处理为 字段 = 值
                 */
                switch ($expression) {
                    case null:
                    case 'null':
                        $this->where[] = $AND . trim($where, ':') . ' IS NULL ';
                        break;
                    case 'not null':
                        $this->where[] = $AND . trim($where, ':') . ' IS NOT NULL ';
                        break;
                    default:
                        $this->where[] = $AND . trim($where, ':') . ' = ' . $where;
                        $this->parameter[$where] = $expression;
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
                        $parameter = $this->is_join ? $this->get_parameter($where) : $where;
                        $this->where[] = $AND . trim($where, ':') . ' ' . $expression . ' ' . $parameter;
                        $this->parameter[$parameter] = $condition;
                        break;
                    case 'like':
                        if (is_array($condition)) {
                            $parameter = $this->is_join ? $this->get_parameter($where) : $where;
                            $cca = '';
                            foreach ($condition as $key => $value) {
                                $cca .= $link . ' ' . trim($where, ':') . ' LIKE "' . $parameter . $key . '"';
                                $this->parameter[$parameter . $key] = $value;
                            }
                            $cca = trim($cca, $link);
                            $this->where[] = $AND . '(' . $cca . ')';
                        } else {
                            $parameter = $this->is_join ? $this->get_parameter($where) : $where;
                            $this->where[] = $AND . trim($where, ':') . ' LIKE "' . $parameter . '"';
                            $this->parameter[$parameter] = $condition;
                        }
                        break;
                    case 'between':
                        if (!is_array($condition)) {
                            throw new \Exception('between条件异常，请检查第三个参数，标准参数格式 例：数组[1,2]');
                        }
                        $parameter = $this->is_join ? $this->get_parameter($where) : $where;
                        $this->where[] = $AND . trim($where, ':') . ' BETWEEN ' . $parameter . '1 AND ' . $parameter . '2';
                        $this->parameter[$parameter . '1'] = $condition[0];
                        $this->parameter[$parameter . '2'] = $condition[1];
                        break;
                    case 'in':
                        if (!is_array($condition)) {
                            throw new \Exception('in条件异常，请检查第三个参数，标准参数格式 例：数组[1,2,3]');
                        }
                        $parameter = $this->is_join ? $this->get_parameter($where) : $where;
                        $in_condition = '';
                        foreach ($condition as $key => $value) {
                            $in_condition .= $parameter . $key . ',';
                            $this->parameter[$parameter . $key] = $value;
                        }
                        $in_condition = trim($in_condition, ',');
                        $this->where[] = $AND . trim($where, ':') . ' IN (' . $in_condition . ')';
                        break;
                    case 'nat':
                        $parameter = $this->is_join ? $this->get_parameter($condition) : $condition;
                        $this->where[] = $AND . trim($where, ':') . ' = ' . $parameter;
                        break;
                }
                break;
        }
        return $this;
    }

    /**
     * 如果使用了join链表查询的时候，参数会出现:user.id
     * 绑定参数遇到.的时候回报错，所以这里进行处理
     * @param $parameter
     * @return string
     */
    public function get_parameter($parameter)
    {
        $arr = explode('.', $parameter);
        $parameter = end($arr);
        return ':' . $parameter;
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
        /**
         * 获取方法传入的参数
         * 使用func_get_args()函数
         */
        $parameters = func_get_args();
        switch (count($parameters)) {
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
    public function join($table, $on, $type = ' INNER JOIN ')
    {
        if (is_array($table)) {
            $this->join[] = $type . $table[0] . ' as ' . $table[1] . ' ON ' . $on;
        } else {
            $this->join[] = $type . $table . ' ON ' . $on;
        }
        $this->is_join = true;
        return $this;
    }

    /**
     * 处理SQL left join操作
     * 即使右表中没有匹配，也从左表返回所有的行
     * @param $table
     * @param $on
     */
    public function left_join($table, $on)
    {
        $type = ' LEFT JOIN ';
        return $this->join($table, $on, $type);
    }

    /**
     * 处理SQL right join操作
     * 即使左表中没有匹配，也从右表返回所有的行
     * @param $table
     * @param $on
     */
    public function right_join($table, $on)
    {
        $type = ' RIGHT JOIN ';
        return $this->join($table, $on, $type);
    }

    /**
     * 处理SQL full join操作
     * 只要其中一个表中存在匹配，就返回行
     * @param $table
     * @param $on
     */
    public function full_join($table, $on)
    {
        $type = ' FULL JOIN ';
        return $this->join($table, $on, $type);
    }
}
