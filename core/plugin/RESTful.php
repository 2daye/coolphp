<?php

namespace core\plugin;

class RESTful
{
    /**
     * 定义RESTful风格的响应格式
     * @var array
     * @author 2daye
     */
    public array $result = [
        'code' => Code::OK,
        'message' => '未知消息',
        'data' => []
    ];

    /**
     * 处理 RestFul 风格的响应
     * @param int $code
     * @param mixed $message
     * @param array $data
     * @return $this
     * @author 2daye
     */
    public function response(int $code, mixed $message = '未知消息', array $data = [])
    {
        $this->result['code'] = $code;
        $this->result['message'] = $message;
        $this->result['data'] = $data;

        /**
         * 根据请求的参数个数不同，进行不同的处理方式
         *
         * 1.传入code
         * 自动匹配message，去除data
         *
         * 2.传入code/message
         * message = string，去除data
         * message = array，message转为data，message通过code自动匹配
         *
         * 3.code/message/data
         * 不走智能处理，正常返回
         */
        switch (count(func_get_args())) {
            case 1:
                unset($this->result['data']);

                // 判断code是否存在message，存在就使用对应的message
                if (isset(Code::MESSAGE[$this->result['code']])) {
                    $this->result['message'] = Code::MESSAGE[$this->result['code']];
                }
                break;
            case 2:
                // 如果message是字符串，就正常当message处理，删除data
                if (is_string($this->result['message'])) {
                    unset($this->result['data']);
                }

                // 如果message是数组，就把message处理成data，message根据code自动匹配
                if (is_array($this->result['message'])) {
                    $this->result['data'] = $this->result['message'];

                    // 判断code是否存在message，存在就使用对应的message
                    if (isset(Code::MESSAGE[$this->result['code']])) {
                        $this->result['message'] = Code::MESSAGE[$this->result['code']];
                    }
                }
                break;
        }

        return $this;
    }

    /**
     * 返回数据结果
     * @return array
     * @author 2daye
     */
    public function array(): array
    {
        return $this->result;
    }

    /**
     * 返回json结果
     * @return bool
     * @author 2daye
     */
    public function json(): bool
    {
        header('Content-Type:application/json; charset=utf-8');

        echo json_encode($this->result);

        return true;
    }
}


