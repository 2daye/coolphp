<?php
/*
 * 3DES加密类
 * 模式 CBC
 * 填充 pkcs5padding
 * 输出 base64
 * 字符集 utf8
 * */
namespace core\plugin;

class ThreeDes
{
    //加密密钥
    private $key;
    //加密使用的向量
    private $iv;

    //构造方法
    public final function __construct($key, $iv)
    {
        //把密钥进行MD5加密，然后截取24位，作为3DES加密的密钥
        $this->key = substr(md5($key), 0, 24);
        //把向量进行MD5加密，然后截取8位，作为3DES加密使用的向量
        $this->iv = substr(md5($iv), 0, 8);
    }

    /*
     * 加密
     * string $crypt 需要加密的字符串
     * string $key 加密使用的密钥
     * string $vi 加密使用的向量
     * return string 3DES加密后的字符串
     * */
    public final function encrypt($input)
    {
        $size = 8;
        $input = self::pkcs5_pad($input, $size);
        $encryption_descriptor = mcrypt_module_open(MCRYPT_3DES, '', 'cbc', '');
        mcrypt_generic_init($encryption_descriptor, $this->key, $this->iv);
        $data = mcrypt_generic($encryption_descriptor, $input);
        mcrypt_generic_deinit($encryption_descriptor);
        mcrypt_module_close($encryption_descriptor);
        return base64_encode($data);
    }

    /*
     * 解密
     * string $crypt 需要加密的字符串
     * string $key 加密使用的密钥
     * string $vi 加密使用的向量
     * return string 3DES解密后的字符串
     * */
    public final function decrypt($crypt)
    {
        $crypt = base64_decode($crypt);
        $encryption_descriptor = mcrypt_module_open(MCRYPT_3DES, '', 'cbc', '');
        mcrypt_generic_init($encryption_descriptor, $this->key, $this->iv);
        $decrypted_data = mdecrypt_generic($encryption_descriptor, $crypt);
        mcrypt_generic_deinit($encryption_descriptor);
        mcrypt_module_close($encryption_descriptor);
        $decrypted_data = self::pkcs5_unpad($decrypted_data);
        return rtrim($decrypted_data);
    }

    private final function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    private final function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }
}