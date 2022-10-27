<?php
// 控制器命名空间
namespace core\app\api\controller;

// 父类控制器
use core\tool\Tool;
use core\plugin\Code;
use core\plugin\RESTful;
use core\plugin\Controller;
use core\app\common\model\User as UserModel;

class User extends Controller
{
    /**
     * 注册
     * @return bool
     * @author 2daye
     */
    public function register(): bool
    {
        $data = [];
        $data['username'] = Tool::post('username');
        $data['phone'] = Tool::post('phone');
        $data['password'] = md5(Tool::post('password'));
        $data['created_at'] = time();

        $result = (new UserModel())->register($data);

        if ($result['code'] === Code::OK) {
            return (new RESTful())->response(Code::OK)->json();
        }

        return (new RESTful())->response(Code::INTERNAL_SERVER_ERROR, $result['message'])->json();
    }
}
