<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:41
 * @author ChangHai Zhan
 */

namespace app\admin\block;

use app\admin\model\AgentInfoModel;
use app\admin\model\UsersModel;
use app\admin\redis\LoginRedis;
use app\admin\model\PartnerModel;

/**
 * 逻辑块
 * Class DemoBlock
 * @author ChangHai Zhan
 */
class LoginBlock
{
    /**
     * 验证密码
     * @param $password
     * @param $pwd
     * @param string $salt
     * @return bool
     */
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
     * @param $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function LoginByUid($uid)
    {
        $model = UsersModel::model()->getLoginById($uid, 'login');
        if (!$model) {
            return [];
        }
        $token = md5($uid . time() . mt_rand(0, 999999));
        $data = [
            'userInfo' => $model->getData(),
            'token' => $token,
        ];
        $modelAgentInfo = AgentInfoModel::model()->getLoginAgentByUid($uid);
        if ($modelAgentInfo) {
            $data['agentInfo'] = $modelAgentInfo->getData();
        }
        // if (LoginRedis::set($token, $data)) {
            UsersModel::model()->updateLoginById($model->id, request()->ip(), date('Y-m-d H:i:s', time()));
            return $data;
        // }
        // return [];
    }

    public static function LoginByPartnerId($id)
    {
        $model = UsersModel::model()->getLoginById($id, 'login');
        if (!$model) {
            return [];
        }
        $token = md5($id . time() . mt_rand(0, 999999));
        $data = [
            'userInfo' => $model->getData(),
            'token' => $token,
        ];
        $modelPatnerInfo = PartnerModel::model()->getOne(['login_user_id'=>$id, 'status'=>1]);
        if ($modelPatnerInfo) {
            $data['partnerInfo'] = $modelPatnerInfo->getData();
        }
        
        UsersModel::model()->updateLoginById($model->id, request()->ip(), date('Y-m-d H:i:s', time()));
        return $data;
        
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