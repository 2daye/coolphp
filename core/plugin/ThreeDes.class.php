<?php
/**
 * 3DES加密类 - OpenSsl
 * 作者：2daye
 */
namespace core\plugin;

class ThreeDes
{
    //密钥24个字符
    private $key;
    //向量8个字符
    private $iv;

    /**
     * 构造方法
     * 设置传入加密用的密钥和向量
     * ThreeDes constructor.
     * @param $key
     * @param $iv
     */
    public final function __construct($key, $iv)
    {
        //把密钥进行MD5加密，然后截取24位，作为3DES加密的密钥
        $this->key = substr(md5($key), 0, 24);
        //把向量进行MD5加密，然后截取8位，作为3DES加密使用的向量
        $this->iv = substr(md5($iv), 0, 8);
    }

    /**
     * 加密
     * @param $data //要加密数据
     * @return string
     */
    public function encryption($data)
    {
        return base64_encode(openssl_encrypt($data, 'des-ede3-cbc', $this->key, OPENSSL_RAW_DATA, $this->iv));
    }

    /**
     * 解密
     * @param $data //要解密的数据
     * @return string
     */
    public function decryption($data)
    {
        return openssl_decrypt(base64_decode($data), 'des-ede3-cbc', $this->key, OPENSSL_RAW_DATA, $this->iv);
    }
}