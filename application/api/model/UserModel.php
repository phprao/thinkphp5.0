<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 10:11
 */
namespace app\api\model;

use app\common\model\Model;

class UserModel extends Model {

    protected $table = 'users';

    /**
     * 管理员登陆
     * @param $userName 用户名
     * @param $password 密码
     */
    public function login($userName,$password)
    {
        $condition = array(
            'user_login' => $userName,
            'user_pass' => $password
        );
        $data = $this->where($condition)->find();
        return $data;
    }
}