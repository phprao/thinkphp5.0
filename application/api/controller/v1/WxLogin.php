<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-04-14 16:40:52
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 微信登陆
 +---------------------------------------------------------- 
 */

namespace app\api\controller\v1;

use app\api\controller\Controller;
use app\api\service\WechatService;
use app\api\model\PlayerModel;
use app\api\model\AgentInfoModel;
use app\api\model\PlayerInfoModel;
use app\api\model\WxBonusLogModel;
use app\api\block\LoginBlock;
use think\Request;
use think\Loader;
use think\Db;
use think\Log;


class WxLogin extends Controller
{
	protected $service;

    protected $request;

    protected function _initialize()
    {
        parent::_initialize();
        $this->service = new WechatService();
        // 微信授权回调
        $this->wxRedirectUri = request()->domain() . request()->root() . "/api/v1/wx_login/index";
        $this->wxRedirectBind = request()->domain() . request()->root() . "/api/v1/wx_login/bind";
        // 微信登陆跳转
        $this->wxJumpIndex = config('wxJumpIndex');
        $this->wxJumpAgree = config('wxJumpAgree');
    }

	public function index()
    {
        $code = input('code');

        if (empty($code)) {
            $url = $this->service->getRedirectUrl($this->wxRedirectUri);
            header("Location:" . $url);
        } else {
        	// 获取用户基本信息
        	$code = input('code');
	        $accessToken = $this->service->getAccessToken($code);
	        if (!$accessToken) {
	        	echo "<script type='text/javascript'>alert('请在微信打开此链接')</script>";
				exit;
	        }
			
	        $jsData = json_decode($accessToken, true);
	        if (isset($jsData['errcode'])) {
	            echo "<script type='text/javascript'>alert('".$jsData['errmsg']."')</script>";
				exit;
	        }
	        $access_token = $jsData['access_token'];
	        $refresh_token = $jsData['refresh_token'];
	        $openid = $jsData['openid'];
	        $userRes = $this->service->getUserInfo($access_token, $openid);
	        if (!$userRes) {
	            echo "<script type='text/javascript'>alert('获取用户基本信息失败')</script>";
				exit;
	        }
	        $js_userinfo = json_decode($userRes, true);
	        if (isset($js_userinfo['errcode'])) {
	            echo "<script type='text/javascript'>alert('获取用户基本信息失败')</script>";
				exit;
	        }
	        // 是否已是玩家
	        $player = PlayerModel::model()->where('player_unionid', $js_userinfo['unionid'])->find();
	        if (!$player) {
	            echo "<script type='text/javascript'>alert('您还不是幸运王国玩家，请先下载app')</script>";
				exit;
	        }
	        if(!$player['player_status']){
	        	echo "<script type='text/javascript'>alert('对不起，您已被禁止登录')</script>";
				exit;
	        }
        	// 校验是否是星级推广员
        	$agentInfo = AgentInfoModel::model()->where(['agent_player_id'=>$player['player_id'], 'agent_login_status'=>1])->find();
	        if (empty($agentInfo)) {
	            echo "<script type='text/javascript'>alert('对不起，您还不是星级推广员，无权登录')</script>";
				exit;
	        }
        	// 是否已绑定公众号
        	if($player['player_openid_gzh'] != $js_userinfo['openid']){
        		$re = PlayerModel::model()->save(['player_openid_gzh'=>$js_userinfo['openid']], ['player_id'=>$player['player_id']]);
        		if(!$re){
        			echo "<script type='text/javascript'>alert('公众号绑定失败')</script>";
					exit;
        		}
        	}
        	// 生成token
        	$data = LoginBlock::LoginByUid($player['player_id'], ['login_from'=>LoginBlock::LOGIN_FROM_WX]);
	        if(empty($data)){
	            echo "<script type='text/javascript'>alert('系统登录失败')</script>";
				exit;
	        }
	        // 跳转地址
	        if($agentInfo['agent_is_agree']){
	        	header("Location:" . $this->wxJumpIndex . $data['token']);
	        }else{
	        	header("Location:" . $this->wxJumpAgree . $data['token']);
	        }
            
        }
    }
    public function bind()
    {
        $code = input('code');

        if (empty($code)) {
            $url = $this->service->getRedirectUrl($this->wxRedirectBind);
            header("Location:" . $url);
        } else {
        	// 获取用户基本信息
        	$code = input('code');
	        $accessToken = $this->service->getAccessToken($code);
	        if (!$accessToken) {
	        	echo "<script type='text/javascript'>alert('请在微信打开此链接')</script>";
				exit;
	        }
	        $jsData = json_decode($accessToken, true);
	        if (isset($jsData['errcode'])) {
	            echo "<script type='text/javascript'>alert('".$jsData['errmsg']."')</script>";
				exit;
	        }
	        $access_token = $jsData['access_token'];
	        $refresh_token = $jsData['refresh_token'];
	        $openid = $jsData['openid'];
	        $userRes = $this->service->getUserInfo($access_token, $openid);
	        if (!$userRes) {
	            echo "<script type='text/javascript'>alert('获取用户基本信息失败')</script>";
				exit;
	        }
	        $js_userinfo = json_decode($userRes, true);
	        if (isset($js_userinfo['errcode'])) {
	            echo "<script type='text/javascript'>alert('获取用户基本信息失败')</script>";
				exit;
	        }
	        // 是否已是玩家
	        $player = PlayerModel::model()->where('player_unionid', $js_userinfo['unionid'])->find();
	        if (!$player) {
	            echo "<script type='text/javascript'>alert('您还不是幸运王国玩家，请先下载app')</script>";
				exit;
	        }
	        if(!$player['player_status']){
	        	echo "<script type='text/javascript'>alert('对不起，您已被禁止登录')</script>";
				exit;
	        }
        	// 是否已绑定公众号
        	if($player['player_openid_gzh'] != $js_userinfo['openid']){
        		$re = PlayerModel::model()->save(['player_openid_gzh'=>$js_userinfo['openid']], ['player_id'=>$player['player_id']]);
        		if(!$re){
        			echo "<script type='text/javascript'>alert('公众号绑定失败')</script>";
					exit;
        		}
        	}
        	$w = ['player_id'=>$player['player_id'], 'openid_gzh'=>'', 'status'=>0];
        	$num = WxBonusLogModel::model()->where($w)->count();
        	if($num){
				$re  = WxBonusLogModel::model()->save(['openid_gzh'=>$js_userinfo['openid']], $w);
	            if($re === false){
	            	echo "<script type='text/javascript'>alert('公众号绑定失败')</script>";
					exit;
	            }
        	}

        	echo "<script type='text/javascript'>alert('公众号绑定成功')</script>";
			exit;
        }
    }

}