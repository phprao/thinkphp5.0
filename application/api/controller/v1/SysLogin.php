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

use app\api\block\LoginBlock;
use app\api\controller\Controller;
use app\api\model\AgentInfoModel;
use app\admin\model\UsersModel;

/**
 * Class Login
 * @package app\api\controller\v1
 * @author ChangHai Zhan
 */
class SysLogin extends Controller {

    protected function _initialize()
    {
        parent::_initialize();
        $this->wxJumpIndex = config('wxJumpIndex');
        $this->wxJumpAgree = config('wxJumpAgree');
    }
    /**
     * 登陆
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function index()
    {
        $player_id = input('player_id','');
        $user_name = input('user_name','');
        $user_pass = input('user_pass','');
// $player_id = '1076925';
// $user_name = 'sysadmin';
// $user_pass = '123456';
        if(empty($player_id)){
            return $this->sendError('10000','玩家id不能为空');
        }
        if(empty($user_name)){
            return $this->sendError('10000','账号不能为空');
        }
        if(empty($user_pass)){
            return $this->sendError('10000','密码不能为空');
        }

        if($user_name != 'sysadmin'){
            return $this->sendError('10000','请使用系统账号登陆');
        }

        $model = UsersModel::model()->getUsersByUsername($user_name);
        if (!$model) {
            return $this->sendError(2002, '管理员不存在');
        }
        if (!config('login_not_password') && !LoginBlock::passwordVerify($user_pass, $model->user_pass)) {
            return $this->sendError(2003, '用户名或密码错误');
        }
        if ($model->user_status != UsersModel::USER_STATUS_YES) {
            return $this->sendError(2004, '管理员被禁用请联系相关人员');
        }

        $modelAgentInfo = AgentInfoModel::model()->getLoginAgentByUid($model->id);
        if($modelAgentInfo){
            return $this->sendError(402, '无权访问');
        }

        $condtion = array(
            'agent_player_id' => $player_id
        );
        $agentInfo = AgentInfoModel::model()->getInfo($condtion);

        if(empty($agentInfo)){
            return $this->sendError('10000','玩家不存在');
        }

        if($agentInfo['agent_login_status'] == 0){
            return $this->sendError('10000','还不是星级推广员');
        }

        $data = LoginBlock::LoginByUid($player_id,['login_from'=>LoginBlock::LOGIN_FROM_WX]);

        if($agentInfo['agent_is_agree']){
            $data['redirect_url'] = $this->wxJumpIndex . $data['token'];
        }else{
            $data['redirect_url'] = $this->wxJumpAgree . $data['token'];
        }

        return $this->sendSuccess($data);
    }

    
}
