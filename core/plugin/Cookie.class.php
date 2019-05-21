<?php
/*
 * CooKie操作类
 * */

namespace core\plugin;

/* 加密采用了md5，base64，PHP逻辑异或运算 ^
 * 《逻辑异或》可使用于加密算法某一环节或更多环节，使算法更复杂，不易被破解，安全性更高。
 * 1.PHP里的逻辑异或和C语言是相同，
 * 2.int型是二进制的异或运算,
 * 3.但是PHP的位运算更高级，
 * 4.如果左右参数都是字符串，
 * 5.则位运算符将操作字符的ASCII值
 *
 * 异或运算也可以实现无中间变量,两int数字的兑换,或者字符串的兑换
 *
 * 1.首先，我们要知道什么是异或，异或，简单来说就是，相同的两个数，异或的结果是0，不同的两个数，异或的结果是 1
 * 2.0 和 0，异或的结果是0，1 和 1，异或的结果是0，0 和 1，异或的结果是 1，于是我们发现，在0和1的世界里，A和 B异或
 * 结果是C，B和C异或的结果一定是A，A和C异或的结果一定是B
 *
 * 原因就在于：两个变量进行异或时，会将字符串转换成二进制再进行异或，异或完，又从二进制转换成了字符串。
 * $a = 3; //0011
 * $b = 4; //0100
 * $c = $a ^ $b; //A和B异或，结果是C
 * $b = $b ^ $c; //B和C异或的结果一定是A，将A赋值给B
 * $a = $b ^ $c; //A（原A，现B）和C异或的结果一定是B，将B赋值给A
 * echo $a, $b;  //已经实现$a和$b的值对调
 *
 * $a = 3;
 * $b = 4;
 * $a = $a ^ $b;
 * $b = $b ^ $a;
 * $a = $a ^ $b;
 * echo $a, $b;  //这样不定义变了$c也可以$a和$b的值对调
 */
class Cookie
{
    //定义常量，混淆加密的字符串
    const SECRET_KEY = '南边来了他大大伯子家的大搭拉尾巴耳朵狗';

    /**
     * 设置cookie
     * name     必需。规定 cookie 的名称
     * value    必需。规定 cookie 的值
     * expire   可选。规定 cookie 的有效期
     * path     可选。规定 cookie 的服务器路径
     * domain   可选。规定 cookie 的域名
     * secure   可选。规定是否通过安全的 HTTPS 连接来传输 cookie
     */
    public static function set($name, $value, $expire = 3600, $encryption = true, $path = '/', $domain = '', $secure = 0)
    {
        if ($name != '' && $value != '') {
            //加密Cookie的值
            $value = $encryption ? self::encryption($value) : $value;
            //设置Cookie过期时间
            $expires = intval(time() + $expire);
            //设置Cookie
            return setcookie($name, $value, $expires, $path, $domain, $secure);
        } else {
            return false;
        }
    }

    /*
     * 删除cookie
     */
    public static function delete($name, $path = '/', $domain = '', $secure = 0)
    {
        if ($name != '' && !empty($_COOKIE[$name])) {
            return setcookie($name, '', intval(time() - 3600), $path, $domain, $secure);
        } else {
            return true;
        }
    }

    /*
     * 获取cookie
     * name cookie名称
     */
    public static function get($name, $decryption = true)
    {
        if ($name != '') {
            if (!empty($_COOKIE[$name])) {
                //调用decrypt解密Cookie
                return $decryption ? self::decrypt($_COOKIE[$name]) : $_COOKIE[$name];
            }
        }
        return '';
    }
    /*
     * 字符串加密
     */
    private static function encryption($string)
    {
        //对字符串，先进行一次base64加密
        $string = base64_encode($string);
        //定义最终输出的加密数据
        $code = '';
        //加密密钥MD5加密，在使用substr()函数从第8位开始截取后面的18位字符串
        $key = substr(md5(self::SECRET_KEY), 8, 18);
        //获取base64加密后的字符串长度
        $strLen = strlen($string);
        //获取截取后的加密密钥长度
        $keyLen = strlen($key);
        //循环再次进行加密，使用逻辑异或加密
        for ($i = 0; $i < $strLen; $i++) {
            //根据字符串的第几位取摸，得出要和那一位的密钥进行，逻辑异或运算加密
            $k = $i % $keyLen;
            //异或运算 循环拼接得到全部加密后的字符串
            $code .= $string[$i] ^ $key[$k];
        }
        return $code;
    }

    /*
     * 字符串解密
     */
    private static function decrypt($string)
    {
        //定义最终解密的数据
        $code = '';
        //加密密钥MD5加密，在使用substr()函数从第8位开始截取后面的18位字符串
        $key = substr(md5(self::SECRET_KEY), 8, 18);
        //获取密文的长度
        $strLen = strlen($string);
        //获取截取后的加密密钥长度
        $keyLen = strlen($key);
        //循环再次进行解密，使用逻辑异或解密
        for ($i = 0; $i < $strLen; $i++) {
            //根据字符串的第几位取摸，得出要和那一位的密钥进行，逻辑异或运算
            $k = $i % $keyLen;
            //逻辑异或运算循环拼接得到全部解密后的字符串
            $code .= $string[$i] ^ $key[$k];
        }
        //对字符串，在进行一次base64解密得到最终结果
        return base64_decode($code);
    }
}