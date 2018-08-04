<?php
/**
 * +----------------------------------------------------------
 * date: 2018-03-13 16:16:15
 * +----------------------------------------------------------
 * author: Raoxiaoya
 * +----------------------------------------------------------
 * describe: 用户相关
 * +----------------------------------------------------------
 */

namespace app\api\controller\v1;

use app\api\controller\Controller;
use app\api\model\AgentAccountInfoModel;
use app\api\redis\MobileCodeRedis;
use app\api\model\PlayerModel;
use app\api\model\PlayerInfoModel;
use app\api\block\LoginBlock;

class User extends Controller
{

    /**
     * 账号设置
     * @return
     */
    public function setting()
    {
        $alipay = input('alipay');
        $alipay_name = input('alipay_name');
        $alipay_mobile = input('alipay_mobile');
        $captcha = input('captcha');
        $code = input('code');
        $payment_password = input('payment_password');
        $action = input('action');

        $token = input('thoen');
        $agentInfo = $this->isLogin($this->token);

        if (!$agentInfo) {
            return $this->sendError('100001', '用户token失效，请重新登陆');
        }

        if (empty($alipay)) {
            return $this->sendError('100002', '支付宝账号不能为空。');
        }

        if (empty($alipay_name)) {
            return $this->sendError('100003', '支付宝账号真实姓名不能为空。');
        }
        //更新信息的时候跳过这些验证。
        if ($action != 'update') {
            if (empty($alipay_mobile)) {
                return $this->sendError('100004', '手机号码不能为空。');
            }

            if (!preg_match("/^[1][356789][0-9]{9}$/", $alipay_mobile)) {
                return $this->sendError('100005', '请正确的输入手机号码');
            }

            if (!preg_match("/^[0-9]{6}$/", $payment_password)) {
                return $this->sendError('100005', '请输入6位数字的支付密码');
            }

            if (empty($code)) {
                return $this->sendError('100007', '短信验证码不能为空。');
            }

            $key = 'bind' . $alipay_mobile;
            $codeInfo = MobileCodeRedis::get($key);

            if ($code != $codeInfo['code']) {
                return $this->sendError('100009', '手机验证码错误。');
            }
            MobileCodeRedis::delete($key);

            if (empty($payment_password)) {
                return $this->sendError('100007', '支付密码不能为空!。');
            }

            $data = array(
                'agent_account_alipay' => $alipay,
                'agent_account_username' => $alipay_name,
                'agent_account_mobile' => $alipay_mobile,
                'agent_account_payment_password' => md5($payment_password)
            );

        } else {

            $data = array(
                'agent_account_alipay' => $alipay,
                'agent_account_username' => $alipay_name,
            );
        }


        $status = AgentAccountInfoModel::model()->updateAgentAccountInfo($agentInfo['agent_info']['agent_id'], $data);
        if ($status) {
            return $this->sendSuccess();
        }
        return $this->sendError(100010, '未更改任何信息。');
    }
    public function getSetting(){
        $agentInfo = $this->isLogin($this->token);
        //判断是否配置过
        $agentdata = AgentAccountInfoModel::model()->getAccountInfoByAgentId($agentInfo['agent_info']['agent_id']);
        if ($agentdata['agent_account_alipay'] || $agentdata['agent_account_username'] || $agentdata['agent_account_mobile']) {
            $data_list = array(
                'agent_account_alipay' =>$agentdata['agent_account_alipay'],
                'agent_account_username' =>$agentdata['agent_account_username'],
                'agent_account_mobile' =>$agentdata['agent_account_mobile'],
            );
            $data = array(
                'status' => 1,
                'data' => $data_list,
            );

        }else{
            $data = array(
                'status' => 2,
                'agent_id' => $agentInfo['agent_info']['agent_id'],
            );
        }

        return $this->sendSuccess($data);
    }

    /**
     * 更改手机号码
     */
    public function changeMobile()
    {

        $alipay_mobile = input('alipay_mobile');
        $code = input('code');

        $dataInfo = $this->isLogin($this->token);

        if (empty($dataInfo)) {
            return $this->sendError('10000', '用户token失效，请重新登陆');
        }

        if (empty($alipay_mobile)) {
            return $this->sendError('10000', '手机号码不能为空');
        }

        $key = 'bind' . $alipay_mobile;
        $codeInfo = MobileCodeRedis::get($key);
        if ($code != $codeInfo['code']) {
            return $this->sendError('100009', '手机验证码错误。');
        }
        MobileCodeRedis::delete($key);

        $data = array('agent_account_mobile' => $alipay_mobile);
        $status = AgentAccountInfoModel::model()->updateAgentAccountInfo($dataInfo['agent_info']['agent_id'], $data);

        if ($status) {
            return $this->sendSuccess();
        }

        return $this->sendError(10003, '修改手机号码失败');

    }

    /**
     * 修改支付密码
     */
    public function changePassword()
    {

        $old_password = input('old_password');
        $new_password = input('new_password');
        $confirm_password = input('confirm_password');

        $agentInfo = $this->isLogin($this->token);

        if (!$agentInfo) {
            return $this->sendError(10000, '用户token失效，请重新登陆');
        }

        if (!preg_match("/^[0-9]{6}$/", $old_password)) {
            return $this->sendError('100002', '请输入6位数字的支付密码');
        }

        $agentAccountInfo = AgentAccountInfoModel::model()->getAccountInfoByAgentId($agentInfo['agent_info']['agent_id']);
        if ($agentAccountInfo['agent_account_payment_password'] != md5($old_password)) {
            return $this->sendError(10000, '原支付密码错误。');
        }

        if (!preg_match("/^[0-9]{6}$/", $new_password)) {
            return $this->sendError('100003', '请输入6位数字的新支付密码');
        }
        if (!preg_match("/^[0-9]{6}$/", $confirm_password)) {
            return $this->sendError('100004', '请输入6位数字的确认的支付密码');
        }

        if ($new_password != $confirm_password) {
            return $this->sendError(10000, '新密码与确认密码不一致。');
        }

        $data = array(
            'agent_account_payment_password' => md5($new_password)
        );
        $status = AgentAccountInfoModel::model()->updateAgentAccountInfo($agentInfo['agent_info']['agent_id'], $data);

        if ($status) {
            return $this->sendSuccess();
        }

        //todo 此种情况是 修改的密码与原来的密码是一样的。
        return $this->sendSuccess();
    }

    /**
     * 获取用户的基本信息
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getinfo()
    {
        $agentInfo = $this->isLogin($this->token);

        if (!$agentInfo) {
            return $this->sendError('10000', '用户token失效，请重新登陆');
        }

        $player = PlayerModel::model()->getPlayerinfo($agentInfo['agent_info']['agent_player_id']);
        $playerinfo = PlayerInfoModel::model()->getPlayerinfoOne($agentInfo['agent_info']['agent_player_id']);

        $data = array(
            'agent_info' => array(
                'agent_id'         => $agentInfo['agent_info']['agent_player_id'],
                'agent_name'       => urldecode($player['player_nickname']),
                'agent_head_image' => $playerinfo['player_header_image'],
                'login_from'       => isset($agentInfo['login_from']) ? $agentInfo['login_from'] : LoginBlock::LOGIN_FROM_APP
            ),
        );
        return $this->sendSuccess($data);
    }


}