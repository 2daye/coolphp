<?php
/*PHP Socket服务端类*/

//设置客户端断开连接时是否中断脚本的执行
ignore_user_abort(true);
//设置脚本最大执行时间 0秒
set_time_limit(0);

class Socket
{
    //连接主要的socket
    private $connect;
    //socket连接列表数组
    private $sockets = [];

    //构造函数初始化socket服务器
    public function __construct($ip_address, $port)
    {
        /*
         * 创建一个TCP 流socket服务器
         * socket_create()函数创建一个socket，0参数代表，SQL_TCP处理协议
         */
        $this->connect = socket_create(AF_INET, SOCK_STREAM, 0);
        //设置IP和端口重用,在重启服务器后能重新使用此端口;
        socket_set_option($this->connect, SOL_SOCKET, SO_REUSEADDR, 1);
        /*
         * 绑定socket到ip护着端口
         * socket_bind()函数用于将ip地址绑定到socket_create()所创建的资源中
         */
        if (!socket_bind($this->connect, $ip_address, $port)) {
            //不能绑定端口
            return false;
        }
        //开始监听客户端数据
        socket_listen($this->connect);
        //判断socket是否创建成功
        if (!$this->connect) {
            return false;
        }

        //把主连接socket资源存入，列表数组
        $this->sockets[0] = ['resource' => $this->connect];

        while (true) {
            /*
             * 作用：获取read数组中活动的socket，并且把不活跃的从read数组中删除,具体的看文档。
             * 这是一个同步方法，必须得到响应之后才会继续下一步,常用在同步非阻塞IO
             * 说明:
             * 1 新连接到来时,被监听的端口是活跃的,如果是新数据到来或者客户端关闭链接时,活跃的是对应的客户端socket而不是服务器上被监听的端口
             * 2 如果客户端发来数据没有被读走,则socket_select将会始终显示客户端是活跃状态并将其保存在readfds数组中
             * 3 如果客户端先关闭了,则必须手动关闭服务器上相对应的客户端socket,否则socket_select也始终显示该客户端活跃(这个道
             * 理跟"有新连接到来然后没有用socket_access把它读出来,导致监听的端口一直活跃"是一样的)
             * */
            $write = array();
            $except = null;
            //array_column()返回输入数组中某个单一列的值
            $sockets = array_column($this->sockets, 'resource');
            $read_num = socket_select($sockets, $write, $except, null);
            if ($read_num === false) {
                return false;
            }
            //循环获取socket连接资源
            foreach ($sockets as $socket) {
                //如果可读的是服务器socket,则处理连接
                if ($socket == $this->connect) {
                    //socket_accept接受客户端的连接，得到新的socket资源
                    $client = socket_accept($socket);
                    //socket_getpeername获取连接ip和端口号
                    socket_getpeername($client, $ip, $ports);
                    //建立socket连接信息
                    $socket_info = [
                        'resource' => $client,
                        'name' => '',
                        'handshake' => false,
                        'ip' => $ip,
                        'port' => $ports,
                    ];
                    //用socket连接的id，作为socket连接列表数组的索引
                    $this->sockets[(int)$client] = $socket_info;
                } else {
                    /*
                    * 如果是PHP命令行模式使用socket_read()函数接受客户端信息
                    * 如果是网页客户端或者PHP正常模式则使用socket_recv()函数接受客户端信息
                    */
                    $bytes = @socket_recv($socket, $buffer, 2048, 0);
                    if ($bytes == 8) {
                        socket_close($socket);
                    } else {
                        if (!$this->sockets[(int)$socket]['handshake']) {
                            if ($this->shake_hands($socket, $buffer)) {
                                $this->sockets[(int)$socket]['handshake'] = true;
                            }
                        } else {
                            //已经握手，直接接受数据，并处理
                            $buffer = $this->parsing_data_frame($buffer);
                            $msg = $this->assembly_data_frame(json_encode($buffer));
                            //广播公开信号
                            foreach ($this->sockets as $socketsss) {
                                if ($socketsss['resource'] == $this->connect) {
                                    //continue跳出本轮循环，进行下一轮
                                    continue;
                                }
                                socket_write($socketsss['resource'], $msg, strlen($msg));
                            }
                            /* socket_write($socket, $msg, strlen($msg));*/
                        }
                    }
                }
            }
        }
    }

    //握手
    private function shake_hands($socket, $buffer)
    {
        //定义key
        $key = null;
        //正则取出客户端的key
        preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $buffer, $match);
        $key = $match[1];
        //定义握手专用固定的密钥
        $mask = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
        //遵循固定协议加密Accept key 写入客户端握手信息
        $acceptKey = base64_encode(sha1($key . $mask, true));
        //握手标准格式
        $upgrade = "HTTP/1.1 101 Switching Protocols\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "Sec-WebSocket-Accept: " . $acceptKey . "\r\n" .
            "\r\n";
        //将握手信息写入返回客户端
        socket_write($socket, $upgrade, strlen($upgrade));
        return true;
    }

    //解析数据帧
    private function parsing_data_frame($buffer)
    {
        $decoded = '';

        $len = ord($buffer[1]) & 127;
        if ($len === 126) {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        } else if ($len === 127) {
            $masks = substr($buffer, 10, 4);
            $data = substr($buffer, 14);
        } else {
            $masks = substr($buffer, 2, 4);
            $data = substr($buffer, 6);
        }
        for ($index = 0; $index < strlen($data); $index++) {
            $decoded .= $data[$index] ^ $masks[$index % 4];
        }

        return $decoded;
    }

    /*
     * 组装帧数据
     * 作用将普通信息，组装成 WebSocket可以使用的数据帧
     * */
    private function assembly_data_frame($value)
    {
        $frame = [];
        $frame[0] = '81';
        $len = strlen($value);

        if ($len < 126) {
            $frame[1] = $len < 16 ? '0' . dechex($len) : dechex($len);
        } else if ($len < 65025) {
            $s = dechex($len);
            $frame[1] = '7e' . str_repeat('0', 4 - strlen($s)) . $s;
        } else {
            $s = dechex($len);
            $frame[1] = '7f' . str_repeat('0', 16 - strlen($s)) . $s;
        }

        $data = '';
        $l = strlen($value);

        for ($i = 0; $i < $l; $i++) {
            $data .= dechex(ord($value{$i}));
        }

        $frame[2] = $data;
        $data = implode('', $frame);
        return pack("H*", $data);
    }

}

$s = new Socket('www.yoop.com.cn', '8080');