<?php
/*
 * CoolPHP框架官方内置函数库
 * */
namespace core\tool;

class Functions
{
    /**
     * 更漂亮的数组或变量的展现方式
     * @param $var //要展现的数组或者变量
     * @param bool $debug //是否断点调试
     */
    public static function p($var, $debug = false)
    {
        //is_bool()检测变量是否是布尔型
        if (is_null($var)) {
            var_dump(null);
        } else if (is_bool($var)) {
            var_dump($var);
        } else {
            echo "<pre style='position:relative;z-index:1000;padding:10px;border-radius:5px;background:#2a2a2a;border:1px solid #aaa;font-size:17px;line-height:22px;opacity:0.9;color: #f8fbfd;font-weight:bold;font-family: \"STHeiti\",sans-serif;'>" . print_r($var, true) . "</pre>";
        }
        //判断是否断点
        if ($debug) {
            exit;
        }
    }

    //获取网站域名
    public static function getUrl()
    {
        if ($_SERVER['HTTP_HOST'] == '127.0.0.1') {
            return 'http://' . $_SERVER['HTTP_HOST'] . '/cool';
        } else {
            return 'http://' . $_SERVER['HTTP_HOST'];
        }
    }

    //显示404
    public static function show404()
    {
        header('HTTP/1.1 404 Not Found');
        header("status: 404 Not Found");
        exit;
    }

    //输出json
    public static function json($array)
    {
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode($array);
    }

    //获取文件的后缀名
    public static function getFileSuffix($file)
    {
        return end(explode('.', $file));
    }

    //跳转方法
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

    //返回上一页
    public static function goPrior($str = null, $prior = -1)
    {
        if ($str === null) {
            echo "<script>history.go(" . $prior . ");</script>";
        } else {
            echo "<script>alert('$str');history.go(" . $prior . ");</script>";
        }
        exit;
    }

    //调试函数
    public static function debug($value, $dump = false, $exit = true)
    {
        /*
         * 调试函数
         * $value 要调试的数据
         * $dump 是否启用var_dump调试
         * $exit 是否在调试后设置断点
         */
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

    //获取get数据
    public static function get($str = null, $filter = '', $default = false)
    {
        /*
         * 获取get数据
         * $str 要获取的变量名
         * $filter 过滤类型 只支持int类型
         * $default 默认值 当获取不到值时,所返回的默认值
         */
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

    //获取post数据
    public static function post($str = null, $filter = '', $default = false)
    {
        /*
         * 获取post数据
         * $str 要获取的变量名
         * $filter 过滤类型 只支持int类型
         * $default 默认值 当获取不到值时,所返回的默认值
         */
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

    //session操作
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
                    if (isset($sessionName)) {
                        unset($_SESSION[$sessionName]);
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

    //压缩Html
    public static function compressHtml($html, $compress_way = 1)
    {
        $html_string = file_get_contents(ROOT_PATH . '/core/app/' . $html);
        $string = '';
        switch ($compress_way) {
            case 1:
                $string = preg_replace("~>\s+<~", "><", preg_replace("~>\s+\r\n~", ">", $html_string));
                break;
            case 2:
                $string = ltrim(
                    rtrim(
                        preg_replace(
                            array("/> *([^ ]*) *</", "//", "'/\*[^*]*\*/'", "/\r\n/", "/\n/", "/\t/", '/>[ ]+</'),
                            array(">\\1<", '', '', '', '', '', '><'),
                            $html_string
                        )
                    )
                );
                break;
            case 3:
                //清除换行符
                $string = str_replace("\r\n", '', $html_string);
                //清除换行符
                $string = str_replace("\n", '', $string);
                //清除制表符
                $string = str_replace("\t", '', $string);
                $pattern = array(
                    //去掉注释标记
                    "/> *([^ ]*) *</",
                    "/[\s]+/",
                    "/<!--[^!]*-->/",
                    "/\" /",
                    "/ \"/",
                    "'/\*[^*]*\*/'"
                );
                $replace = array(
                    ">\\1<",
                    " ",
                    "",
                    "\"",
                    "\"",
                    ""
                );
                $string = preg_replace($pattern, $replace, $string);
                break;
            default:
                if (DEBUG) {
                    self::debug('请传入正确的压缩方式，int 1 只压缩html，int 2 全部压缩包html页面中的js jq，int 3 强效压缩');
                } else {
                    self::show404();
                }
        }
        return $string;
    }

    //加密get参数
    public static function encryptionGet($url, $key)
    {
        $encrypt_key = md5(mt_rand(0, 100));
        $ctr = 0;
        $tmps = "";
        for ($i = 0; $i < strlen($url); $i++) {
            if ($ctr == strlen($encrypt_key))
                $ctr = 0;
            $tmps .= substr($encrypt_key, $ctr, 1) . (substr($url, $i, 1) ^ substr($encrypt_key, $ctr, 1));
            $ctr++;
        }
        $encrypt_key = md5($key);
        $ctr = 0;
        $tmp = "";
        for ($i = 0; $i < strlen($tmps); $i++) {
            if ($ctr == strlen($encrypt_key))
                $ctr = 0;
            $tmp .= substr($tmps, $i, 1) ^ substr($encrypt_key, $ctr, 1);
            $ctr++;
        }
        return rawurlencode(base64_encode($tmp));
    }

    //解密get参数
    public static function decryptionGet($string, $key)
    {
        $txts = base64_decode(rawurldecode($string));
        $encrypt_key = md5($key);
        $ctr = 0;
        $txt = "";
        for ($s = 0; $s < strlen($txts); $s++) {
            if ($ctr == strlen($encrypt_key))
                $ctr = 0;
            $txt .= substr($txts, $s, 1) ^ substr($encrypt_key, $ctr, 1);
            $ctr++;
        }
        $strs = "";
        for ($i = 0; $i < strlen($txt); $i++) {
            $md5 = substr($txt, $i, 1);
            $i++;
            $strs .= (substr($txt, $i, 1) ^ $md5);
        }
        $url_array = explode('&', $strs);
        $vars = [];
        if (is_array($url_array)) {
            foreach ($url_array as $var) {
                $var_array = explode("=", $var);
                $vars[$var_array[0]] = $var_array[1];
            }
        }
        return $vars;
    }

    //当前是否Ajax请求
    public static function isAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false;
    }

    //当前是否Get请求
    public static function isGet()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET') ? true : false;
    }

    //当前是否Post请求
    public static function isPost()
    {
        return (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') ? true : false;
    }

}