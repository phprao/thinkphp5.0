<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:44
 * @author ChangHai Zhan
 */

namespace app\admin\controller\v1;

use app\admin\block\LoginBlock;
use app\admin\controller\Controller;
use app\admin\model\UsersModel;
use app\common\components\Helper;
use \think\Session;

/**
 * @controllerName 管理员模块
 */
class Users extends Controller
{
    /**
     * @actionName 管理员信息
     */
    public function index()
    {
        $userInfo = $this->isLogin($this->token);
        if(!isset($userInfo['agentInfo'])){
            $userInfo['agentInfo']['agent_name'] = '总公司';
        }
        return $this->sendSuccess($userInfo);
    }


    /**
     * 修改密码
     */
    public function changePwd()
    {
        $infotoken = $this->isLogin($this->token);
        $old_password = $this->request->post('old_password');
        $new_password = $this->request->post('new_password');
        $confirm_password = $this->request->post('confirm_password');

        $userinfo = UsersModel::model()->getFind(array('id' => $infotoken['userInfo']['id']));
        if (!LoginBlock::passwordVerify($old_password, $userinfo['user_pass'])) {
            return $this->sendError(2003, '原始密码错误');
        }

        if (!$new_password) {
            return $this->sendError(2003, '请输入新密码！');
        }
        if (!$confirm_password) {
            return $this->sendError(2003, '请输入确认密码！');
        }
        if ($new_password != $confirm_password) {
            return $this->sendError(2003, '新密码与确认密码不一致！');
        }
        if(!Helper::checkString($new_password,1)){
            return $this->sendError(2003, '密码必须为数字、英文，6到16位！');
        }
        $pwd = LoginBlock::passwordEncrypt($new_password);
        $condition['user_pass'] = $pwd;
        $re = UsersModel::model()->updateUser($infotoken['userInfo']['id'], $condition);
        if ($re) {
            return $this->sendSuccess($re);
        } else {
            return $this->sendError(2003, '修改失败！');
        }

    }

    /**
     * 退出
     */
    public function userExit()
    {
        $infotoken = $this->isLogin($this->token);
        Session::delete('login_info');
        return $this->sendSuccess();

    }
}