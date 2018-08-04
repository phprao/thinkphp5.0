<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:41
 * @author ChangHai Zhan
 */
namespace app\api\block;

use app\api\model\AgentInfoModel;
use app\api\redis\LoginRedis;

/**
 * 逻辑块
 * Class DemoBlock
 * @author ChangHai Zhan
 */
class LoginBlock
{
    const LOGIN_FROM_WX  = 1; // 从微信登陆H5后台
    const LOGIN_FROM_APP = 2; // 从客户端登陆H5后台
    /**
     * 验证密码
     * @param $password
     * @param $pwd
     * @param string $salt
     * @return bool
     */
    // public static function passwordVerify($password, $pwd, $salt = '')
    // {
    //    return self::passwordEncrypt($password, $salt) === $pwd;
    // }

    /**
     * 加密
     * @param $password
     * @param string $salt
     * @return string
     */
    // public static function passwordEncrypt($password, $salt = '')
    // {
    //     if (empty($salt)) {
    //         $salt = '';
    //     }
    //     return md5($salt . $password);
    // }

    public static function passwordVerify($password, $pwd, $salt = '')
    {
        if (!$password || !$pwd) {
            return false;
        }
        return self::passwordEncrypt($password, $salt) === $pwd;
    }

    /**
     * 加密
     * @param $password
     * @param string $salt
     * @return string
     */
    public static function passwordEncrypt($password, $salt = '')
    {
        if (empty($salt)) {
            $salt = 'ZFE3EXWBECr1IdTcpD';
        }
        return "###" . md5(md5($salt . $password));
    }

    /**
     * 获取登陆系统
     * @param $playerId
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function LoginByUid($playerId, $param = [])
    {
        $token = md5($playerId.time());
        $data = [
            'user_info' => [
                'id' => $playerId,
            ],
        ];
        $model = AgentInfoModel::model()->getLoginByPlayerId($playerId);
        if (!$model) {
            return false;
        }
        $data['agent_info'] = $model->getData();
        $data['token']      = $token;
        $data['login_from'] = isset($param['login_from']) ? $param['login_from'] : self::LOGIN_FROM_APP;
        if (LoginRedis::set($token, $data)) {
            return $data;
        }
        return false;
    }

    /**
     * 退出登陆
     * @param $token
     * @return bool
     */
    public static function logoutByToken($token)
    {
        return LoginRedis::delete($token);
    }

    /**
     * 是否登陆
     * @param $token
     * @return bool
     */
    public static function isLogin($token)
    {
        return LoginRedis::get($token);
    }
}