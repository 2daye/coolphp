<?php
/*
 * 模型父类
 * 单例数据库连接
 * 数据库基本操作
 * */
namespace core\plugin;

class Model
{
    private $db = false;

    //定义$instance用于存放实例化的对象
    private static $instance;

    //静态单例模式
    public static function get_instance()
    {
        //通过使用 instanceof操作符 和 self关键字 ，可以检测到类是否已经被实例化，如果 $instance 没有保存，类本身的实例。
        if (!(self::$instance instanceof self)) {
            //就把本身的实例赋给 $instance
            self::$instance = new self();
        }
        return self::$instance;
    }

    //私有构造函数，实现单例
    private function __construct()
    {
        if ($this->db === false) {
            //载入配置数据库配置文件
            $dbconfig = \core\plugin\Config::get_all('db');
            //连接数据库
            try {
                $this->db = new \PDO('mysql:dbname=' . $dbconfig['DB_NAME'] . ';port=' . $dbconfig['DB_PORT_NUMBER'] . ';host=' . $dbconfig['DB_HOST'] . ';', $dbconfig['DB_USER'], $dbconfig['DB_PASSWORD']);
                $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                /*
                 * 注意当使用PDO访问MySQL数据库真正的预备义语句并不是默认使用的！
                 * 为了解决这个问题，必须禁用仿真准备好的语句。使用PDO创建连接的，如下
                 * */
                $this->db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
                //申明数据库编码 UTF8
                $this->db->exec('set names utf8');
            } catch (\PDOException $e) {
                echo 'Database connection failed : ' . $e->getMessage();
                exit;
            }
        }
    }

    //单例防止克隆
    private function __clone(){}

    /*
     * 数据库（增，改，删）操作
     * string $_sql 传入sql语句
     * array $parameter 绑定参数数组
     * return int
     * */
    public function cud($_sql, array $parameter)
    {
        //传入sql执行
        $results = $this->db->prepare($_sql);
        //传入查询参数
        $results->execute($parameter);
        //返回数据库受影响行数，int型
        $ImpactNumber = $results->rowCount();
        return $ImpactNumber;
    }

    /*
     * 获得数据库查询的全部结果
     * string $_sql 传入sql语句
     * array $parameter 绑定参数数组
     * return array
     * */
    public function get_all_result($_sql, array $parameter)
    {
        //传入sql执行
        $results = $this->db->prepare($_sql);
        //传入查询参数
        $results->execute($parameter);
        //使用PDO::FETCH_ASSOC，返回结果集，以数组形式返回
        $data = array();
        while ($row = $results->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }

    /*
     * 获得数据库查询的全部结果的个数
     * string $_sql 传入sql语句
     * array $parameter 绑定参数数组
     * return int
     * */
    public function get_all_number($_sql, array $parameter)
    {
        $results = $this->db->prepare($_sql);
        $results->execute($parameter);
        //获取条数，返回int型
        $total = $results->rowCount();
        return $total;
    }

    /*
     * 数据库插入方法
     * string $table 表名
     * array $arr 要插入的字段和值
     * return int
     * */
    public function insert($table, array $arr)
    {
        //定义值的数组
        $val = array();
        //定义字段数组
        $field = array();
        foreach ($arr as $key => $value) {
            $field[] = trim($key, ':');
            $val[] = $key;
        }
        //implode()函数把数组按,号分成字符串
        $vals = implode(",", $val);
        $fields = implode(",", $field);
        //编译sql
        $_sql = "insert into " . $table . "(" . $fields . ") values (" . $vals . ")";
        //执行返回结果
        return $this->cud($_sql, $arr);
    }

    /*
     * 数据库删除方法
     * string $table 表名
     * string/array $where where条件数组或字符串
     * array $parameter 绑定参数数组
     * boolean $and 使用and还是or拼接条件
     * return int
     * */
    public function delete($table, $where = null, array $parameter = array(), $and = true)
    {
        if ($where !== null) {
            //调用where条件处理函数和绑定参数
            $w_p = $this->where($where, $parameter, $and);
        } else {
            if (DEBUG) {
                throw new \Exception('现在是DEBUG模式，不允许，使用delete()方法时，不传入where条件');
            } else {
                $w_p = array('wheres' => '', 'parameter' => array());
            }
        }
        //编译where条件
        $w = $where === null ? '' : ' WHERE ' . $w_p['wheres'];
        //编译sql
        $_sql = "delete from " . $table . $w;
        //执行返回结果
        return $this->cud($_sql, $w_p['parameter']);
    }

    /*
     * 数据库更新操作
     * string $table 表名
     * array $arr 要更新的字段和值
     * array $parameter 绑定参数数组
     * string/array $where where条件数组或字符串
     * boolean $and 使用and还是or拼接条件
     * return int
     * */
    public function update($table, array $arr, $where = null, array $parameter = array(), $and = true)
    {
        if ($where !== null) {
            //调用where条件处理函数和绑定参数
            $w_p = $this->where($where, $parameter, $and);
        } else {
            $w_p = array('wheres' => '', 'parameter' => array());
        }
        //编译where条件
        $w = $where === null ? '' : ' WHERE ' . $w_p['wheres'];
        //定义update字符串
        $u = '';
        //循环解析update参数数组，编译出update的字符串
        foreach ($arr as $key => $value) {
            $u .= trim($key, ':') . " = " . $key . ",";
        }
        //去除update参数的尾部的，号
        $u_val = trim($u, ',');
        //编译sql
        $_sql = "update " . $table . " set " . $u_val . $w;
        //合并where条件的绑定参数数组和update要修改的参数的，绑定参数数组
        $p = array_merge($arr, $w_p['parameter']);
        //执行返回结果
        return $this->cud($_sql, $p);
    }

    /*
     * 数据库查询操作
     * string $table 表名
     * string $field 要查询的字段，默认(*)全部字段
     * string/array $where where条件数组或字符串
     * boolean $get_all_number 返回结果还是结果个数
     * array $order_by 是否有order_by排序
     * $order_by例：
     * ['UserId' => 'DESC']
     * array $limit limit条件
     * $limit例：
     * [1,2] [5]
     * array $parameter 绑定参数数组
     * boolean $and 使用and还是or拼接条件
     * return int/array
     * */
    public function select($table, $field = '*', $where = null, $get_all_number = false, $order_by = null, $limit = null, array $parameter = array(), $and = true)
    {
        if ($where !== null) {
            //调用where条件处理函数和绑定参数
            $w_p = $this->where($where, $parameter, $and);
        } else {
            $w_p = array('wheres' => '', 'parameter' => array());
        }
        //编译where条件
        $w = $where === null ? '' : ' WHERE ' . $w_p['wheres'];
        //判断是否有order_by条件
        if ($order_by !== null) {
            foreach ($order_by as $key => $value) {
                switch ($value) {
                    case 'DESC':
                        $order_by = ' ORDER BY ' . $key . ' DESC ';
                        break;
                    case 'ASC':
                        $order_by = ' ORDER BY ' . $key . ' ASC ';
                        break;
                }
            }
        } else {
            $order_by = ' ';
        }
        //判断是否有limit条件
        if ($limit !== null) {
            switch (count($limit)) {
                case 1:
                    $limit = ' limit ' . reset($limit);
                    break;
                case 2:
                    $l = '';
                    foreach ($limit as $key => $value) {
                        $l .= $value . ',';
                    }
                    $limit = trim(' limit ' . $l, ',');
                    break;
            }
        } else {
            $limit = '';
        }
        //编译sql
        $_sql = "select " . $field . " from " . $table . $w . $order_by . $limit;
        //执行返回结果
        return $get_all_number ? $this->get_all_number($_sql, $w_p['parameter']) : $this->get_all_result($_sql, $w_p['parameter']);
    }

    /*
     * sql where生成处理
     * string/array $where where条件数组或字符串
     * $where例：
     * [':UserId' => ['UserId' => 1],':PassWord'=>['PassWord' => 123456]]
     * [
     *    ':UserId' => ['UserId' => ['UserId1' => 1,'UserId2' => 2]],
     *    ':PassWord'=>['PassWord' => 123456]
     * ]
     * array $parameter 绑定参数数组
     * $parameter例：
     * [':Id' => 1]
     * boolean $and 使用and还是or拼接条件
     * return array
     * */
    private function where($where, array $parameter = array(), $and = true)
    {
        //定义where条件
        $wheres = '';
        //判断如果where条件是数组，则进行数组编译，否则直接使用where条件
        if (is_array($where)) {
            //判断有几个where条件，一个where条件不需要信息拼接
            if (count($where) == 1) {
                //循环解析where条件数组
                foreach ($where as $key => $value) {
                    foreach ($value as $ke => $va) {
                        //判断条件参数，是不是数组，如果是数组说明，一个条件有多个参数，如：in(1,2,3)
                        if (is_array($va)) {
                            //循环参数数组，拼接in()参数字符串1,2,3
                            $in = '';
                            foreach ($va as $k => $v) {
                                $in .= ',' . $k;
                                //传入绑定参数查询的数组
                                $parameter[$k] = $v;
                            }
                            //去除尾部的，号
                            $in_val = trim($in, ',');
                            //编译where条件字符串
                            $wheres = $ke . ' in(' . $in_val . ')';
                        } else {
                            //编译where条件字符串
                            $wheres = $ke . ' = ' . $key;
                            //传入绑定参数查询的数组
                            $parameter[$key] = $va;
                        }
                    }
                }
            } else {
                //定义要编译的where条件字符串
                $w = '';
                //循环解析where条件数组
                foreach ($where as $key => $value) {
                    foreach ($value as $ke => $va) {
                        //判断使用and还是or关键字拼接条件
                        $a = $and ? ' and ' : ' or ';
                        //判断条件参数，是不是数组，如果是数组说明，一个条件有多个参数，如：in(1,2,3)
                        if (is_array($va)) {
                            //循环参数数组，拼接in()参数字符串1,2,3
                            $in = '';
                            foreach ($va as $k => $v) {
                                $in .= ',' . $k;
                                //传入绑定参数查询的数组
                                $parameter[$k] = $v;
                            }
                            //去除尾部的，号
                            $in_val = trim($in, ',');
                            //编译where条件字符串
                            $w .= $ke . ' in(' . $in_val . ')' . $a;
                        } else {
                            //编译where条件字符串
                            $w .= $ke . ' = ' . $key . $a;
                            //传入绑定参数查询的数组
                            $parameter[$key] = $va;
                        }
                    }
                }
                //去除where条件字符串，最后的or或者and
                $wheres = $and ? substr($w, 0, strlen($w) - 5) : substr($w, 0, strlen($w) - 4);
            }
        } else {
            $wheres = $where;
        }
        return array('wheres' => $wheres, 'parameter' => $parameter);
    }

    //获取数据库当前线程最后插入的数据id
    public function last_id()
    {
        return $this->db->lastInsertId();
    }

    //清空数据表，自增id也重置
    public function truncate($table)
    {
        $_sql = "truncate " . $table;
        //执行返回结果
        return $this->cud($_sql, array());
    }

    //表写锁
    public function lock_write($value)
    {
        $_sql = "lock tables " . $value . " write";
        $result = $this->db->exec($_sql);
        return $result;
    }

    //解表锁
    public function unlock()
    {
        $_sql = "unlock tables";
        $result = $this->db->exec($_sql);
        return $result;
    }

    //声明行锁开始
    public function row_lock()
    {
        $_sql = "begin";
        $result = $this->db->exec($_sql);
        return $result;
    }

    //解除行锁
    public function unlock_row_lock()
    {
        $_sql = "commit";
        $result = $this->db->exec($_sql);
        return $result;
    }

}