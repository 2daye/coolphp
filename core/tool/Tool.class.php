<?php
/**
 * CoolPHP框架主函数库
 * 存放开发中自定义的方法
 * 作者：2daye
 */
namespace core\tool;

//继承官方内置函数库
class Tool extends Functions
{
    /**
     * 加密get参数
     * @param $url //要加密的get url
     * @param $key //加密混淆key
     * @return string
     */
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

    /**
     * 解密get参数
     * @param $string //要解密的字符串
     * @param $key //加密时使用的混淆key
     * @return array
     */
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

    /**
     * 压缩html文件
     * @param $html //要压缩的文件
     * @param int $compress_way //选择压缩的方式
     * @return mixed|string
     */
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

    /**
     * 对象转换数组
     * @param $object
     * @return array
     */
    public static function object_conversion_array($object)
    {
        if (is_object($object)) {
            $object = (array)$object;
        }
        if (is_array($object)) {
            foreach ($object as $key => $value) {
                $object[$key] = self::object_conversion_array($value);
            }
        }
        return $object;
    }

    /**
     * 请求搬瓦工服务器api
     * @param $id
     * @param $key
     * @return array
     */
    public static function request_bwh_api($id, $key, $api)
    {
        //搬瓦工veId
        $veId = $id;
        //搬瓦工apiKey
        $apiKey = $key;
        //搬瓦工api
        switch ($api) {
            case 'info':
                $bwhApi = 'https://api.64clouds.com/v1/getServiceInfo';
                break;
            case 'restart':
                $bwhApi = 'https://api.64clouds.com/v1/restart';
                break;
            default:
                $bwhApi = 'https://api.64clouds.com/v1/getServiceInfo';
        }
        //拼接请求地址
        $request = $bwhApi . '?veid=' . $veId . '&api_key=' . $apiKey;
        //请求接口获取结果信息对象
        $result = json_decode(file_get_contents($request));
        //转换得到结果数组
        $resultArray = self::object_conversion_array($result);
        //返回信息数组
        return $resultArray;
    }

    /**
     * 字节转换GB
     * @param $byte
     * @return int
     */
    public static function byte_conversion_gb($byte)
    {
        /**
         * 使用pow函数计算出1024的3次方
         * 在用字节除1024的3次方得到GB
         */
        $gb = $byte / pow(1024, 3);
        return (int)$gb;
    }
}