<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-03-19 10:51:14
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 登陆模块
 +---------------------------------------------------------- 
 */

namespace app\admin\controller\v1;

use app\admin\block\LoginBlock;
use app\admin\controller\Controller;
use app\admin\model\UsersModel;
use \think\Session;

/**
 * @controllerName 登陆模块
 */

class Login extends Controller
{
    /**
     * @actionName 渠道后台登录
     */
    public function index()
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');

        if (!$username) {
            return $this->sendError(2000, '用户名不可空白');
        }
        if (!config('login_not_password') && !$password) {
            return $this->sendError(2001, '密码不可空白');
        }

        $model = UsersModel::model()->getUsersByUsername($username);
        if (!$model) {
            return $this->sendError(2002, '管理员不存在');
        }
        if (!config('login_not_password') && !LoginBlock::passwordVerify($password, $model->user_pass)) {
            return $this->sendError(2003, '用户名或密码错误');
        }
        if ($model->user_status != UsersModel::USER_STATUS_YES) {
            return $this->sendError(2004, '管理员被禁用请联系相关人员');
        }
        $return = LoginBlock::LoginByUid($model->id);
        if (!$return) {
            return $this->sendError(2005, '登陆失败');
        }
        
        if(isset($return['agentInfo']) && !empty($return['agentInfo'])){
            // 渠道后台
            $return['url'] = config('channel_url') . '?token=' . $return['token'];
            Session::set('login_info', json_encode($return));
        }else{
            return $this->sendError(2003, '用户名或密码错误');
        }
        return $this->sendSuccess($return);
    }

    /**
     * 总后台登录
     * @return [type] [description]
     */
    public function system()
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');

        if (!$username) {
            return $this->sendError(2000, '用户名 不可空白');
        }
        if (!config('login_not_password') && !$password) {
            return $this->sendError(2001, '密码 不可空白');
        }

        $model = UsersModel::model()->getUsersByUsername($username);
        if (!$model) {
            return $this->sendError(2002, '管理员 不存在');
        }
        if (!config('login_not_password') && !LoginBlock::passwordVerify($password, $model->user_pass)) {
            return $this->sendError(2003, '用户名或密码 错误');
        }
        if ($model->user_status != UsersModel::USER_STATUS_YES) {
            return $this->sendError(2004, '管理员 被禁用 请联系相关人员');
        }
        $return = LoginBlock::LoginByUid($model->id);
        if (!$return) {
            return $this->sendError(2005, '登陆失败');
        }
        
        if(isset($return['agentInfo']) && !empty($return['agentInfo'])){
            return $this->sendError(2003, '用户名或密码错误');
        }else{
            // 总后台
            $return['url'] = config('admin_url') . '?token=' . $return['token'];
            Session::set('login_info', json_encode($return));
        }
        return $this->sendSuccess($return);
    }

    /**
     * @actionName 渠道后台登录
     */
    public function partner()
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        // $username = 'partner2';
        // $password = '123456';
        if (!$username) {
            return $this->sendError(2000, '用户名不可空白');
        }
        if (!config('login_not_password') && !$password) {
            return $this->sendError(2001, '密码不可空白');
        }

        $model = UsersModel::model()->getUsersByUsername($username);
        if (!$model) {
            return $this->sendError(2002, '管理员不存在');
        }
        if (!config('login_not_password') && !LoginBlock::passwordVerify($password, $model->user_pass)) {
            return $this->sendError(2003, '用户名或密码错误');
        }
        if ($model->user_status != UsersModel::USER_STATUS_YES) {
            return $this->sendError(2004, '管理员被禁用请联系相关人员');
        }
        $return = LoginBlock::LoginByPartnerId($model->id);
        if (!$return) {
            return $this->sendError(2005, '登陆失败');
        }
        
        if(isset($return['partnerInfo']) && !empty($return['partnerInfo'])){
            $return['url'] = config('partner_url') . '?token=' . $return['token'];
            Session::set('login_info', json_encode($return));
        }else{
            return $this->sendError(2003, '该渠道不存在或已禁用');
        }
        return $this->sendSuccess($return);
    }
}
