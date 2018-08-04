<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-03-14 12:02:06
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 登陆相关
 +---------------------------------------------------------- 
 */

namespace app\api\controller\v1;

use app\api\block\CUrlValidationBlock;
use app\api\block\LoginBlock;
use app\api\controller\Controller;
use app\api\model\AgentInfoModel;
use think\captcha\Captcha;

/**
 * Class Login
 * @package app\api\controller\v1
 * @author ChangHai Zhan
 */
class Login extends Controller {
    /**
     * 登陆
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function index()
    {
        $player_id = input('player_id','');
        if(empty($player_id)){
            return $this->sendError('10000','玩家id不能为空');
        }

        $agentInfoModel = new AgentInfoModel();
        $condtion = array(
            'agent_player_id' => $player_id
        );
        $agentInfo = $agentInfoModel->getInfo($condtion);

        if(empty($agentInfo)){
            return $this->sendError('10000','登陆失败');
        }

        if($agentInfo['agent_status'] == 0 ){
            return $this->sendError('10000','该代理商已被禁用。');
        }

        if($agentInfo['agent_login_status'] == 0){
            return $this->sendError('10000','没有登陆的权限。');
        }

        $data = LoginBlock::LoginByUid($player_id,['login_from'=>LoginBlock::LOGIN_FROM_APP]);
        return $this->sendSuccess($data);
    }

    public function CheckAuth(){

        $sign = input('sign');
        $player_id = input('player_id');
        $time = input('time');
        $key = 'dcyouxi';

        if(empty($sign)){
            return $this->sendError('10000','缺少sign参数');
        }

        if(empty($player_id)){
            return $this->sendError('10000','缺少player_id参数');
        }

        if(empty($time)){
            return $this->sendError('10000','缺少time参数');
        }

        $string = $player_id.$time.$key;
        $my_sign = md5($string);

        if($sign !== $my_sign){
            return $this->sendError('10000','验证错误，请检查签名');
        }

        $agentInfoModel = new AgentInfoModel();
        $condtion = array(
            'agent_user_id' => $player_id
        );
        $agentInfo = $agentInfoModel->getInfo($condtion);
        if(empty($agentInfo)){
            return $this->sendError('10000','登陆失败');
        }

        if($agentInfo['agent_status'] == 0 ){
            return $this->sendError('10000','该代理商已被禁用。');
        }

        if($agentInfo['agent_login_status'] == 0){
            return $this->sendError('10000','没有登陆的权限。');
        }

        $data = LoginBlock::LoginByUid($player_id,['login_from'=>LoginBlock::LOGIN_FROM_APP]);
        if(!empty($data)){
            return $this->sendSuccess($data['token']);
        }

        return $this->sendError('10001','获取token失败');
    }


    /**
     * @param $uid
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function logout()
    {
        $token = input('token');
        if (!LoginBlock::logoutByToken($token)) {
            return $this->sendError(1000);
        }
        return $this->sendSuccess();
    }

    /**
     * 生成图像验证码
     * @return \think\Response
     */
    public function getCaptcha()
    {
        $captcha = new Captcha();
        return $captcha->entry('',$this->token);
    }

    // public function checks(){
    //     var_dump(captcha_check('nvfhn',$this->token));
    // }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * 外链接登录接口
     */
    public function authorizedLongin()
    {
        $sign = input('sign');
        $player_id = input('player_id');
        $time = input('time');
        $key = 'dcyouxi';

        if(!CUrlValidationBlock::validation($sign, $player_id, $time, $key)){
            return $this->sendError('10000', '验证错误，请检查签名');
        }

        $agentInfoModel = new AgentInfoModel();
        $condtion = array(
            'agent_player_id' => $player_id
        );
        $agentInfo = $agentInfoModel->getInfo($condtion);

        if (empty($agentInfo)) {
            return $this->sendError('10000', '登陆失败');
        }

        if ($agentInfo['agent_status'] == 0) {
            return $this->sendError('10000', '该代理商已被禁用。');
        }

        if ($agentInfo['agent_login_status'] == 0) {
            return $this->sendError('10000', '没有登陆的权限。');
        }

        $data = LoginBlock::LoginByUid($player_id, ['login_from'=>LoginBlock::LOGIN_FROM_APP]);
        return $this->sendSuccess($data);


    }




}
