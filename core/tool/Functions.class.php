<?php
/**
 * CoolPHP框架官方内置函数库
 * 作者：2daye
 */
namespace core\tool;

class Functions
{
    /**
     * 框架更加漂亮的输出方法，变量/数组/参数
     * @param $var //要展现的数组或者变量
     * @param bool $debug //是否断点调试
     * @param string $label //标签注释输输出的数据
     */
    public static function p($var, $debug = false, $label = '')
    {
        /**
         * 获取方法传入的参数
         * 使用func_get_args()函数
         */
        $parameter = func_get_args();
        /**
         * 判断是否打标签
         */
        if (2 === count($parameter) && !is_bool($debug)) {
            $label = $debug . '：';
        } elseif (3 === count($parameter)) {
            $label = $label . '：';
        }
        /**
         * ob_start
         * 此函数将打开输出缓冲。
         * 当输出缓冲激活后，脚本将不会输出内容（除http标头外），
         * 相反需要输出的内容被存储在内部缓冲区中。
         */
        ob_start();
        if (is_null($var)) {
            //null
            var_dump(null);
        } elseif (is_bool($var)) {
            //bool
            var_dump($var);
        } else {
            print_r($var);
        }
        /**
         * ob_get_clean
         * 得到当前缓冲区的内容并删除当前输出缓冲区
         */
        $p = ob_get_clean();
        echo "<pre style='position:relative;z-index:1000;padding:10px;border-radius:5px;background:#2a2a2a;border:1px solid #aaa;font-size:17px;line-height:22px;opacity:0.9;color: #f8fbfd;font-weight:bold;font-family: \"STHeiti\",sans-serif;'>" . $label . $p . "</pre>";
        //判断是否断点
        if ($debug) {
            exit;
        }
    }

    /**
     * 框架调试函数
     * @param $value //要调试的数据
     * @param bool $dump //是否启用var_dump调试
     * @param bool $exit //是否在调试后设置断点
     */
    public static function debug($value, $dump = false, $exit = true)
    {
        //判断调试的时候用什么函数
        if ($dump) {
            $func = 'var_dump';
        } else {
            if (is_array($value) || is_object($value)) {
                $func = 'print_r';
            } else {
                $func = 'printf';
            }
        }
        //输出html
        echo '<pre>调试输出:<hr/>';
        $func($value);
        echo '</pre>';
        //是否断点
        if ($exit) {
            exit;
        }
    }

    /**
     * 框架跳转方法
     * @param $url //要跳转的url
     * @param null $str //跳转时的提示
     */
    public static function jump($url, $str = null)
    {
        //判断，如果只有url那么就转跳，如果有URL和str那么就是弹框加转跳
        if (isset($url) && $str === null) {
            echo "<script>window.location='$url';</script>";
        } elseif (isset($url) && isset($str)) {
            echo "<script>alert('$str');window.location='$url';</script>";
        }
        exit;
    }

    /**
     * 框架返回上一页
     * @param null $str //返回提示内容
     * @param int $prior //返回前几页
     */
    public static function go_prior($str = null, $prior = -1)
    {
        if ($str === null) {
            echo "<script>history.go(" . $prior . ");</script>";
        } else {
            echo "<script>alert('$str');history.go(" . $prior . ");</script>";
        }
        exit;
    }

    /**
     * 框架获取get数据
     * @param null $str //要获取的变量名
     * @param string $filter //过滤类型 只支持int类型
     * @param bool $default //默认值 当获取不到值时,所返回的默认值
     * @return bool|string
     */
    public static function get($str = null, $filter = '', $default = false)
    {
        //判断 有没有传入要获取的get参数，如果没有传入，就直接返回全部的$_GET数据
        if ($str !== null) {
            //判断要获取的get参数存在不
            $get = isset($_GET[$str]) ? $_GET[$str] : false;
            //判断返回什么值
            if ($get !== false) {
                switch ($filter) {
                    case 'int':
                        //is_numeric()函数判断参数是不是数字或者字符串的数字
                        if (!is_numeric($get)) {
                            return $default;
                        }
                        break;
                    default:
                        //htmlspecialchars()函数当碰到HTML标签<>的时候直接当字符串输出，提高安全
                        $get = htmlspecialchars($get);
                }
                return $get;
            } else {
                return $default;
            }
        } else {
            return $_GET;
        }
    }

    /**
     * 框架获取post数据
     * @param null $str //要获取的变量名
     * @param string $filter //过滤类型 只支持int类型
     * @param bool $default //默认值 当获取不到值时,所返回的默认值
     * @return bool|string
     */
    public static function post($str = null, $filter = '', $default = false)
    {
        //判断 有没有传入要获取的post参数，如果没有传入，就直接返回全部的$_POST数据
        if ($str !== null) {
            //判断要获取的post参数存在不
            $post = isset($_POST[$str]) ? $_POST[$str] : false;
            //判断返回什么值
            if ($post !== false) {
                switch ($filter) {
                    case 'int':
                        //is_numeric()函数判断参数是不是数字或者字符串的数字
                        if (!is_numeric($post)) {
                            return $default;
                        }
                        break;
                    case 'array':
                        //is_array()函数判断参数是不是数组
                        if (!is_array($post)) {
                            return $default;
                        }
                        break;
                    default:
                        if (!is_array($post)) {
                            //htmlspecialchars()函数当碰到HTML标签<>的时候直接当字符串输出，提高安全
                            $post = htmlspecialchars($post);
                        }
                }
                return $post;
            } else {
                return $default;
            }
        } else {
            return $_POST;
        }
    }

    /**
     * 框架操作session方法
     * @param $perform //执行哪一种操作 set/get/delete/clear
     * @param null $session_name //session名字
     * @param null $value //session的值
     * @return bool
     * @throws \Exception
     */
    public static function session($perform, $session_name = null, $value = null)
    {
        //判断执行那种session操作
        switch ($perform) {
            case 'set':
                if ($session_name !== null && $value !== null) {
                    $_SESSION[$session_name] = $value;
                    return true;
                } else {
                    if (DEBUG) {
                        throw new \Exception('请传入set方法，需要的name和value');
                    } else {
                        return false;
                    }
                }
                break;
            case 'get':
                if ($session_name !== null) {
                    return isset($_SESSION[$session_name]) ? $_SESSION[$session_name] : '';
                } else {
                    if (DEBUG) {
                        throw new \Exception('请传入get方法，需要的name');
                    } else {
                        return false;
                    }
                }
                break;
            case 'delete':
                if ($session_name !== null) {
                    if (isset($session_name)) {
                        unset($_SESSION[$session_name]);
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    if (DEBUG) {
                        throw new \Exception('请传入delete方法，需要的name');
                    } else {
                        return false;
                    }
                }
                break;
            case 'clear':
                if (isset($_SESSION)) {
                    session_unset();
                }
                return true;
                break;
            default:
                if (DEBUG) {
                    throw new \Exception('请传入需要执行那种session()操作，set，get，delete，clear');
                } else {
                    return false;
                }
        }
    }

    /**
     * 输出json
     * @param array $array //传入要输出json的数组
     */
    public static function json(array $array)
    {
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode($array);
        exit;
    }

    /**
     * 浏览器输出404
     */
    public static function show404()
    {
        header('HTTP/1.1 404 Not Found');
        header("status: 404 Not Found");
        exit;
    }

    /**
     * 返回RESTful风格
     * @param $statusCode
     * @param string $message
     * @param array $data
     */
    public static function return_rest_ful($statusCode, $message = '', $data = [])
    {
        //建立RESTful风格的返回格式
        $result = ['status' => $statusCode, 'message' => $message, 'data' => $data];
        //默认状态码对应的消息
        $httpStatus = [
            200 => '成功',
            400 => '请求参数错误',
            401 => '请登录验证身份',
            403 => '拒绝请求',
            404 => '资源不存在',
            500 => '处理失败'
        ];
        /**
         * 根据请求的参数个数不同，进行不同的处理方式
         * 只传入一个状态码，$message就获取默认的，$data处理为空
         * 只传入一个状态码和一个数据，$message就获取默认的，$message赋值给$data
         * 三个参数都传，就正常进行返回
         */
        switch (count(func_get_args())) {
            case 1:
                unset($result['data']);
                $result['message'] = isset($httpStatus[$statusCode]) ? $httpStatus[$statusCode] : '未知消息';
                break;
            case 2:
                if (!is_array($message)) {
                    unset($result['data']);
                } else {
                    $result['data'] = $message;
                    $result['message'] = isset($httpStatus[$statusCode]) ? $httpStatus[$statusCode] : '未知消息';
                }
                break;
        }
        //返回结果
        self::json($result);
    }

    /**
     * 获取文件后缀名
     * @param $file //文件的名字
     * @return mixed
     */
    public static function get_file_suffix($file)
    {
        return end(explode('.', $file));
    }
}
