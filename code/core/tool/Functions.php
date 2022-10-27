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
     * @param mixed $var 要展现的数组或者变量
     * @param bool $debug 是否断点调试
     * @param string $label 标签注释输输出的数据
     * @return void
     * @author 2daye
     */
    public static function p(mixed $var, bool $debug = false, string $label = ''): void
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
     * @param mixed $value 要调试的数据
     * @param bool $dump 是否启用var_dump调试
     * @param bool $exit 是否在调试后设置断点
     * @return void
     * @author 2daye
     */
    public static function debug(mixed $value, bool $dump = false, bool $exit = true): void
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
     * @param string $url 要跳转的url
     * @param string $str 跳转时的提示
     * @return void
     * @author 2daye
     */
    public static function jump(string $url, string $str = ''): void
    {
        //判断，如果只有url那么就转跳，如果有URL和str那么就是弹框加转跳
        if (isset($url) && $str === '') {
            echo "<script>window.location='$url';</script>";
        } elseif (isset($url) && isset($str)) {
            echo "<script>alert('$str');window.location='$url';</script>";
        }
        exit;
    }

    /**
     * 框架返回上一页
     * @param string $str 返回提示内容
     * @param int $prior 返回前几页
     * @return void
     * @author 2daye
     */
    public static function goPrior(string $str = '', int $prior = -1): void
    {
        if ($str === '') {
            echo "<script>history.go(" . $prior . ");</script>";
        } else {
            echo "<script>alert('$str');history.go(" . $prior . ");</script>";
        }
        exit;
    }

    /**
     * 框架获取get数据
     * @param string|null $str 要获取的变量名
     * @param string $filter 过滤类型 只支持int类型
     * @param bool $default 默认值 当获取不到值时,所返回的默认值
     * @return array|bool|float|int|string
     * @author 2daye
     */
    public static function get(string $str = '', string $filter = '', bool $default = false): float|int|bool|array|string
    {
        // 判断 有没有传入要获取的get参数，如果没有传入，就直接返回全部的$_GET数据
        if ($str !== '') {
            // 判断要获取的get参数存在不
            $get = $_GET[$str] ?? false;
            // 判断返回什么值
            if ($get !== false) {
                switch ($filter) {
                    case 'int':
                        // is_numeric()函数判断参数是不是数字或者字符串的数字
                        if (!is_numeric($get)) {
                            return $default;
                        }
                        break;
                    default:
                        // htmlspecialchars()函数当碰到HTML标签<>的时候直接当字符串输出，提高安全
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
     * @param string $str
     * @param string $filter
     * @param bool $default
     * @return array|bool|float|int|string
     */
    public static function post(string $str = '', string $filter = '',bool $default = false): float|int|bool|array|string
    {
        // 判断 有没有传入要获取的post参数，如果没有传入，就直接返回全部的$_POST数据
        if ($str !== '') {
            // 判断要获取的post参数存在不
            $post = $_POST[$str] ?? false;
            // 判断返回什么值
            if ($post !== false) {
                switch ($filter) {
                    case 'int':
                        // is_numeric()函数判断参数是不是数字或者字符串的数字
                        if (!is_numeric($post)) {
                            return $default;
                        }
                        break;
                    case 'array':
                        // is_array()函数判断参数是不是数组
                        if (!is_array($post)) {
                            return $default;
                        }
                        break;
                    default:
                        if (!is_array($post)) {
                            // htmlspecialchars()函数当碰到HTML标签<>的时候直接当字符串输出，提高安全
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
     * @param string $perform 执行哪一种操作 set/get/delete/clear
     * @param string $session_name session名字
     * @param mixed $value session的值
     * @return bool|mixed|string
     * @throws \Exception
     * @author 2daye
     */
    public static function session(string $perform,string $session_name = '', mixed $value = ''): mixed
    {
        // 判断执行那种session操作
        switch ($perform) {
            case 'set':
                if ($session_name !== '' && $value !== '') {
                    $_SESSION[$session_name] = $value;
                    return true;
                } else {
                    if (DEBUG) {
                        throw new \Exception('请传入set方法，需要的name和value');
                    } else {
                        return false;
                    }
                }
            case 'get':
                if ($session_name !== '') {
                    return $_SESSION[$session_name] ?? '';
                } else {
                    if (DEBUG) {
                        throw new \Exception('请传入get方法，需要的name');
                    } else {
                        return false;
                    }
                }
            case 'delete':
                if ($session_name !== '') {
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
            case 'clear':
                if (isset($_SESSION)) {
                    session_unset();
                }
                return true;
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
     * @param array $array 传入要输出json的数组
     * @return bool
     * @author 2daye
     */
    public static function json(array $array): bool
    {
        header('Content-Type:application/json; charset=utf-8');

        echo json_encode($array);

        return true;
    }

    /**
     * 浏览器输出404
     * @return void
     */
    public static function show404(): void
    {
        header('HTTP/1.1 404 Not Found');

        header("status: 404 Not Found");

        exit;
    }

    /**
     * 获取文件后缀名
     * @param $file
     * @return string
     * @author 2daye
     */
    public static function getFileSuffix($file): string
    {
        $result = explode('.', $file);

        return end($result);
    }
}
