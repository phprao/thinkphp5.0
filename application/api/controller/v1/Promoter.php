<?php
/**
 * 推广员的
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/17
 * Time: 14:33
 */

namespace app\api\controller\v1;

use app\api\block\AgentLevelBlock;
use app\api\block\AgreementBlock;
use app\api\block\MoneyBlock;
use app\api\block\RegisterPlayerBlock;
use app\api\model\AgentAccountInfoModel;
use app\api\model\AgentInfoModel;
use app\api\model\PlayerStatisticalModel;
use app\api\model\PlayerInfoModel;
use app\api\model\PlayerModel;
use app\api\model\PromotersInfoModel;
use app\api\model\AgentIncomeConfigModel;
use app\api\model\SysCoinChangeLog;
use app\api\model\ChannelGiftModel;
use app\api\model\ChannelCoinsLogModel;
use app\api\model\ChangeMoneyInfoModel;
use app\api\redis\PlayerRedis;
use think\Db;
use think\Loader;
use app\api\block\CUrlValidationBlock;
use app\api\service\WechatService;
use think\Request;
use app\common\components\Helper;

use app\api\controller\Controller;

use think\Log;

class Promoter extends Controller
{

    protected $service;

    protected $request;

    // 微信回调链接
    // protected  $redirectUri = "http://jinshi.dcgames.cn/dc_u3dapi_admin/public/api/v1/promoter/index?promoter_id=";
    // 跳转链接到推广页面
    // protected  $jumpctUri = "http://jinshi.dcgames.cn/jinshifenxiang?promoter_id=";
    //生成二维码和推广的链接
    // protected  $generateLink = "http://jinshi.dcgames.cn/dc_u3dapi_admin/public/api/v1/promoter/index?promoter_id=";

    // 渠道推广
    // protected $channelUrl = '/api/v1/promoter/channel_promoter?channel_param=';
    // protected $channelHtmlUrl = 'http://jinshi.dcgames.cn/jinshifenxiangqudao?channel_param=';

    protected function _initialize()
    {
        parent::_initialize();
        $this->service = new WechatService();
        // H5推广
        //微信回调
        $this->redirectUri = request()->domain() . request()->root() . "/api/v1/promoter/index?promoter_id=";
        //二维码生成
        $this->generateLink = request()->domain() . request()->root() . "/api/v1/promoter/index?promoter_id=";
        // 渠道推广星级
        $this->channelPromoterUrl = request()->domain() . request()->root() . '/api/v1/promoter/channel_promoter?channel_param=';
        // 渠道推广玩家
        $this->channelPlayerUrl = request()->domain() . request()->root() . '/api/v1/promoter/channel_player?channel_param=';
        // 新手礼包
        $this->channelGiftUrl = request()->domain() . request()->root() . '/api/v1/promoter/channel_gift?channel_param=';

        //渠道推广跳转链接
        $this->channelHtmlUrl = config('channelHtmlUrl');
        //跳转到H5页面
        $this->jumpctUri = config('jumpctUri');
        // 新手礼包页面
        $this->channelGiftHtmlUrl = config('channelGiftHtmlUrl');


    }

    public function index()
    {
        $code = input('code');
        $promoterId = input('promoter_id');

        if (empty($code)) {
            $redirectUri = $this->redirectUri . $promoterId;
            $url = $this->service->getRedirectUrl($redirectUri);
            header("Location:" . $url);
        } else {
            header("Location:" . $this->jumpctUri . $promoterId . '&code=' . $code);
        }
    }

    public function register()
    {

        $promoterId = input('promoter_id');
        $code = input('code');

        $accessToken = $this->service->getAccessToken($code);

        if (!$accessToken) {
            return $this->sendError(10002, '获取access_token失败');
        }

        $jsData = json_decode($accessToken, true);
        if (isset($jsData['errcode'])) {
            return $this->sendError(10002, '获取access_token失败');
        }

        $access_token = $jsData['access_token'];
        $refresh_token = $jsData['refresh_token'];
        $openid = $jsData['openid'];

        $userRes = $this->service->getUserInfo($access_token, $openid);

        if (!$userRes) {
            return $this->sendError(10002, '获取userinfo失败');
        }

        $js_userinfo = json_decode($userRes, true);
        if (isset($js_userinfo['errcode'])) {
            return $this->sendError(10002, '获取userinfo失败');
        }

        if (!$promoterId) {
            return $this->sendError(10002, '请输入推广ID');
        }
        //todo 判断用户的id 是否真实存在。
        $player = PlayerModel::model()->where('player_id', $promoterId)->find();
        if (!$player) {
            return $this->sendError(10002, '推广员id错误');
        }

        $is_register = PlayerModel::model()->where('player_unionid', $js_userinfo['unionid'])->find();
        if ($is_register) {
            return $this->sendError(10001, '你已经是玩家了,已获得奖励,赶紧来游戏吧!');
        }

        $agentInfo = AgentInfoModel::model()->where('agent_player_id', $promoterId)->find();
        if (empty($agentInfo)) {
            return $this->sendError(10002, '该推广员不存在');
        }

        $return = RegisterPlayerBlock::block()
            ->setParentAgentId($agentInfo->agent_id)
            ->wechat('', $js_userinfo['unionid'], $js_userinfo['nickname'], $js_userinfo['headimgurl'], $js_userinfo['sex'], ['openid' => $js_userinfo['openid'], 'p_parentid' => $agentInfo->agent_parentid]);

        if (!$return) {
            return $this->sendError(10002, '注册推广关系失败');
        }

        MoneyBlock::block()->registerPlayerAwardMoney($return['player_id'], PlayerModel::$registerAward);

        return $this->sendSuccess();
    }


    public function promotePlayer()
    {
        $promoterId = input('promoter_id');
        $data = [];
        if ($promoterId) {
            $player = playermodel::model()->where('player_id', $promoterId)->find();

            $playerInfo = playerinfomodel::model()->where('player_id', $promoterId)->find();
            $player_nickname = urldecode($player['player_nickname']);
            $player_header_image = $playerInfo['player_header_image'];
            //多少天前
            $number_daty = $this->formatTime($player['player_resigter_time']);
            $statistics_award_money = PlayerStatisticalModel::model()->where('statistical_player_id', $promoterId)->find();
            $reward = $statistics_award_money['statistical_award_money'] / 100 ? $statistics_award_money['statistical_award_money'] / 100 : 0;
            $data = array(
                'player_nickname' => $player_nickname,
                'player_header_image' => $player_header_image,
                'number_daty' => $number_daty,
                'reward' => $reward,
                'presented_gold' => PlayerModel::$registerAward,
            );
        }
        return $this->sendSuccess($data);
    }

    /**
     * 二维码
     */
    public function erweima()
    {
        $agnetInfo = $this->isLogin($this->token);
        $playerId = $agnetInfo['user_info']['id'];

        $PlayerModel = new PlayerModel();
        $PlayerInfoModel = new PlayerInfoModel();
        $playerinfo = $PlayerModel->getPlayerinfo($playerId);
        $playerinfoimg = $PlayerInfoModel->getPlayerInfoById($playerId);
        $player_nickname = urldecode($playerinfo['player_nickname']);
        $player_img = $playerinfoimg['player_header_image'];

        $url = $this->generateLink($playerId);
        $img = $this->generateQrcode($playerId, $url);
        $localhost = request()->domain();
        $img_show = $localhost . request()->root() . '/' . $img;

        $data = array(
            'img' => $img_show,
            'player_nickname' => $player_nickname,
            'player_img' => $player_img,
        );
        return $this->sendSuccess($data);
    }

    public function doAgree()
    {
        $agnetInfo = $this->isLogin($this->token);
        $playerId = $agnetInfo['user_info']['id'];
        if ($playerId) {
            $re = AgentInfoModel::model()->updateInfo(['agent_player_id' => $playerId], ['agent_is_agree' => 1]);
            if ($re !== false) {
                // 刷新到redis用户信息-todo
                return $this->sendSuccess();
            } else {
                return $this->sendError(2003, '服务器繁忙-请稍后再试');
            }
        } else {
            return $this->sendError(2003, '服务器繁忙-请稍后再试');
        }
    }

    /**
     *
     * 推广协议接口
     */
    public function agreement()
    {
        $agnetInfo = $this->virtualLogin();
        $playerId = $agnetInfo['user_info']['id'];

        if ($playerId) {
            $content = AgreementBlock::content();
            $contentlucky = AgreementBlock::contentlucky();
            $data1 = array(
                'title' => '《金石游戏中心》推广员合作协议',
                'content' => $content,
                'at_the_end' => '河源盛皓网络科技有限公司',
            );

            $data2 = array(
                'title' => '《幸运王国》推广员合作协议',
                'content' => $contentlucky,
                'tutorial_img' => '广州谊邦成长软件科技有限公司',
            );
            $data = array(
                'data1' => $data1,
                'data2' => $data2,
            );
            return $this->sendSuccess($data);
        } else {
            return $this->sendError(2003, '服务器繁忙-请稍后再试');
        }
    }

    /**
     * 推广教程
     *
     *
     */
    public function promoteTutorial()
    {
        $agnetInfo = $this->isLogin($this->token);
        $playerId = $agnetInfo['user_info']['id'];
        if ($playerId) {
            $localhost = request()->domain();

            $data1 = array(
                'title' => '如何推广玩家',
                'img1' => $localhost . request()->root() . '/promotion/pictures1/img1.png',
                'img2' => $localhost . request()->root() . '/promotion/pictures1/img2.png',
                'content1' => '1.在后台通过微信分享或发送二维码，分享给朋友/朋友圈。',
                'content2' => '2.新玩家扫描二维码之后，自动生成游戏ID领取3万金币成为您推广的玩家。',
                'content' => ' 3.玩家参与游戏累积消耗服务费之后，您可获得推广奖励，推广的总奖励会根据推广的人数递增而递增。 <br/>4.名下玩家成为星级推广员之后，他再推广玩家，您也可获得推广奖励。<br/>5.您推荐的星级推广员，名下的星级推广员再推广玩家，您还可获得推广奖励。<br/>6.推广阶梯分成占比表：
            ',
                'form' => $this->the_rules(),

            );
            $data2 = array(
                'title' => '如何推广玩家',
                'img1' => $localhost . request()->root() . '/promotion/pictures2/img1.png',
                'img2' => $localhost . request()->root() . '/promotion/pictures2/img2.png',
                'content1' => '1.在后台通过微信分享或发送二维码，分享给朋友/朋友圈。',
                'content2' => '2.新玩家扫描二维码之后，自动生成游戏ID领取3万金币成为您推广的玩家。',
                'content' => '3.玩家参与游戏累积消耗服务费之后，您可获得推广奖励，推广的总奖励会根据推广的人数递增而递增。<br/>4.名下玩家成为星级推广员之后，他再推广玩家，您也可获得推广奖励。<br/>5.您推荐的星级推广员，名下的星级推广员再推广玩家，您还可获得推广奖励。<br/>6.推广阶梯分成占比表：',
                'form' => $this->the_rules(),

            );
            $data = array(
                'data1' => $data1,
                'data2' => $data2,
            );

            return $this->sendSuccess($data);
        } else {
            return $this->sendError(2003, '服务器繁忙-请稍后再试');
        }
    }

    /**
     *
     *
     * 推广链接
     */
    public function promoteUrl()
    {
        $agnetInfo = $this->isLogin($this->token);
        $playerId = $agnetInfo['user_info']['id'];
        $data = array(
            'title' => '推广链接！',
            'url' => $this->generateLink($playerId),
        );
        return $this->sendSuccess($data);
    }

    /**
     * @return
     * 脚本接口用
     */
    public function urlErwrima()
    {
        $sign = input('sign');
        $player_id = input('player_id');
        $time = input('time');
        $key = 'dcyouxi';

        if (!CUrlValidationBlock::validation($sign, $player_id, $time, $key)) {
            return $this->sendError('10000', '验证错误，请检查签名');
        }

        $value = $this->generateLink($player_id);
        $file_path = $this->generateQrcode($player_id, $value);

        $localhost = request()->domain();
        $img_show = $localhost . request()->root() . '/' . $file_path;

        $data = array(
            'url' => $value,
            'image' => $img_show,
        );
        return $this->sendSuccess($data);
    }

    /**
     * @return
     */
    public function theLink()
    {
        $sign = input('sign');
        $player_id = input('player_id');
        $time = input('time');
        $key = 'dcyouxi';
        if (!CUrlValidationBlock::validation($sign, $player_id, $time, $key)) {
            return $this->sendError('10000', '验证错误，请检查签名');
        }
        $localhost = request()->domain();
        $data = $localhost . request()->root() . "/public/api/v1/promoter/index/?promoter_id=" . $player_id;
        return $data;


    }

    /**
     * 生成的链接
     * @param $palyer
     */
    public function generateLink($player_id)
    {
        $data = $this->generateLink . $player_id;
        return $data;
    }

    /**
     * @param $player_id
     * @param null $value
     * @return string
     */
    public function generateQrcode($player_id, $value = null)
    {
        Loader::import('Phpqrcode.phpqrcode');
        $file_path = 'erweima/' . $player_id . '.png';
        \QRcode::png($value, $file_path);

        return $file_path;

    }

    /**
     * 规则1
     */
    public function the_rules()
    {

        $number = array(
            'people1' => '>=10人',
            'people2' => '>=30人',
            'people3' => '>=60人',
            'people4' => '>=100人',
            'people5' => '>=150人',
            'people6' => '>=200人',
            // 'people7' => '>=300人',
        );


        $ratio = array(
            'ratio1' => '35%',
            'ratio2' => '36%',
            'ratio3' => '37%',
            'ratio4' => '38%',
            'ratio5' => '39%',
            'ratio6' => '40%',
            // 'ratio7' => '45%',
        );
        $make_ratio = array(
            'make_ratio1' => '12%',
            'make_ratio2' => '12%',
            'make_ratio3' => '12%',
            'make_ratio4' => '12%',
            'make_ratio5' => '12%',
            'make_ratio6' => '12%',
            // 'make_ratio7' => '15%',
        );

        $earnings = array(
            'earnings1' => '8%',
            'earnings2' => '8%',
            'earnings3' => '8%',
            'earnings4' => '8%',
            'earnings5' => '8%',
            'earnings6' => '8%',
            // 'earnings7' => '10%',
        );

        $data = array(
            'number' => $number,
            'ratio' => $ratio,
            'make_ratio' => $make_ratio,
            'earnings' => $earnings,
        );


        return $data;
    }

    /**
     * @param $time
     * @return string
     */
    function formatTime($time)
    {
        $time = (int)substr($time, 0, 10);
        $int = time() - $time;
        if ($int <= 86439) {
            $str = sprintf('1', $int);
        } elseif ($int < 2592000) {
            $str = sprintf('%d', floor($int / 86400));
        } else {
            $str = sprintf('%d', floor($int / 86400));
        }
        return $str;
    }

    // --------------------------------------------------- 渠道推广 ---------------------------------------------

    public function channelPromoter()
    {
        echo "<script type='text/javascript'>alert('暂未开放')</script>";
        exit;

        $channel_param = $this->request->get('channel_param');

        $code = input('code');

        if (empty($code)) {
            $redirectUri = $this->channelPromoterUrl . $channel_param;
            $url = $this->service->getRedirectUrl($redirectUri);
            header("Location:" . $url);
        } else {
            $url = $this->channelHtmlUrl . $channel_param . '&code=' . $code;
            header("Location:" . $url);
        }
    }

    /**
     * 点击成为星级推广员
     * @return [type] [description]
     */
    public function becomePromoter()
    {
        return $this->sendError(10000, '暂未开放');

        $channel_param = $this->request->get('channel_param');
        $channel_id = Helper::think_decrypt($channel_param, 'dc_channel');
        if (!$channel_id) {
            return $this->sendError(10000, '渠道参数错误');
        }
        $channel_id = (int)$channel_id;
        if (!AgentInfoModel::model()->getInfo(['agent_id' => $channel_id, 'agent_level' => 1])) {
            return $this->sendError(10000, '该渠道不存在');
        }

        $code = input('code');

        $accessToken = $this->service->getAccessToken($code);

        if (!$accessToken) {
            return $this->sendError(10001, '获取access_token失败,请重新扫码');
        }

        $jsData = json_decode($accessToken, true);
        if (isset($jsData['errcode'])) {
            return $this->sendError(10001, '获取access_token失败,请重新扫码');
        }

        $access_token = $jsData['access_token'];
        $refresh_token = $jsData['refresh_token'];
        $openid = $jsData['openid'];

        $userRes = $this->service->getUserInfo($access_token, $openid);

        if (!$userRes) {
            return $this->sendError(10001, '获取userinfo失败，可能您没有关注金石游戏公众号,请重新扫码');
        }

        $js_userinfo = json_decode($userRes, true);
        if (isset($js_userinfo['errcode'])) {
            return $this->sendError(10001, '获取userinfo失败,请重新扫码');
        }

        // $js_userinfo = [
        //     'unionid'=>'raoxiaoya_test',
        //     'nickname'=>'raoxiaoya_test',
        //     'headimgurl'=>'http://thirdwx.qlogo.cn/mmopen/vi_32/NibTq3q4XBcicUgCUicUhRg5Z96PahvwZr5J7Bf2z6WI92G5Pd3OU3KGFV825wvIY2QD7icLS40oYVA8QFaaNUDrtw/132',
        //     'sex'=>'1',
        //     'openid'=>'raoxiaoya_test'
        // ];

        $is_new = 1; // 新玩家注册
        $playerInfo = PlayerModel::model()->where('player_unionid', $js_userinfo['unionid'])->find();
        if ($playerInfo) {
            $agentInfo = AgentInfoModel::model()->where('agent_player_id', $playerInfo['player_id'])->find();
            if (empty($agentInfo)) {
                // 删除该Playerinfo，重新注册成星级推广员
                PlayerModel::model()->where('player_unionid', $js_userinfo['unionid'])->delete();
            } else {
                if ($agentInfo['agent_parentid'] != $agentInfo['agent_top_agentid']) {
                    return $this->sendError(10002, '您已被其他人推广了~');
                }
                if ($agentInfo['agent_top_agentid'] != $channel_id && $agentInfo['agent_top_agentid'] != 1) {
                    // 属于该渠道或者大川特代
                    return $this->sendError(10002, '您不属于该渠道');
                }
                if ($agentInfo['agent_login_status'] == 1) {
                    // 已经是星级推广员
                    return $this->sendError(10002, '您已经是星级推广员了~');
                } else {
                    // 是否推广过玩家
                    if ($agentInfo['agent_promote_count'] > 0) {
                        return $this->sendError(10002, '您已经成为推广员了，努力进阶成星级推广员吧~');
                    }
                }
                $agent_id = $agentInfo['agent_id'];
                $is_new = 0; // 成为星级推广员
            }
        }

        if ($is_new) {
            $return = RegisterPlayerBlock::block()
                ->setParentAgentId($channel_id)
                ->wechat(
                    '',
                    $js_userinfo['unionid'],
                    $js_userinfo['nickname'],
                    $js_userinfo['headimgurl'],
                    $js_userinfo['sex'],
                    [
                        'openid' => $js_userinfo['openid'],
                        'p_parentid' => 0
                    ]
                );

            if (!$return) {
                return $this->sendError(10003, '注册推广关系失败,请重新扫码');
            } else {
                MoneyBlock::block()->registerPlayerAwardMoney($return['player_id'], PlayerModel::$registerAward, ['type'=>'star']);
            }

            $agent_id = $return['agent_id'];
        }

        if (AgentInfoModel::model()->save(['agent_parentid' => $channel_id, 'agent_top_agentid' => $channel_id, 'agent_login_status' => 1, 'agent_orignal_type'=>2], ['agent_id' => $agent_id]) === FALSE) {
            // 销毁该账号
            PlayerModel::model()->save(['player_unionid'=>'------','player_status'=>0], ['player_id'=>$return['player_id']]);
            return $this->sendError(10003, '更新信息失败,请重新扫码');
        }

        // 单独的收益分成比例--加载全局配置
        $config = AgentIncomeConfigModel::model()->getAll(['income_agent_id' => 0]);
        $config_init = [];
        foreach ($config as $key => $val) {
            if ($key == 0) {
                $config[0]['income_promote_count'] = 1;
            }
            $config[$key]['income_agent_id'] = $agent_id;

            $config_init[$key]['income_agent_id'] = $config[$key]['income_agent_id'];
            $config_init[$key]['income_promote_count'] = $config[$key]['income_promote_count'];
            $config_init[$key]['income_count_level'] = $config[$key]['income_count_level'];
            $config_init[$key]['income_agent'] = $config[$key]['income_agent'];
            $config_init[$key]['income_level_one'] = $config[$key]['income_level_one'];
            $config_init[$key]['income_level_two'] = $config[$key]['income_level_two'];
            $config_init[$key]['income_level_three'] = $config[$key]['income_level_three'];
            $config_init[$key]['income_remark'] = $config[$key]['income_remark'];
        }

        if (!AgentIncomeConfigModel::model()->setIncomeConfig($config_init)) {
            // 销毁该账号
            PlayerModel::model()->save(['player_unionid'=>'------','player_status'=>0], ['player_id'=>$return['player_id']]);
            return $this->sendError(10003, '更新收益配置失败,请重新扫码');
        }
        if ($is_new) {
            return $this->sendSuccess(['is_new' => 1, 'msg' => PlayerModel::$registerAward ? PlayerModel::$registerAward : 20000]);
        } else {
            return $this->sendSuccess(['is_new' => 0, 'msg' => '打开APP进入游戏立即去赚钱']);
        }

    }

    public function channelPlayer()
    {
        $channel_param = $this->request->get('channel_param');

        $code = input('code');

        if (empty($code)) {
            $redirectUri = $this->channelPlayerUrl . $channel_param;
            $url = $this->service->getRedirectUrl($redirectUri);
            header("Location:" . $url);
        } else {
            $url = $this->channelHtmlUrl . $channel_param . '&code=' . $code;
            header("Location:" . $url);
        }
    }

    /**
     * 点击成为玩家
     * @return [type] [description]
     */
    public function becomePlayer()
    {
        set_time_limit(0);
        
        $channel_param = $this->request->get('channel_param');
        $channel_id = Helper::think_decrypt($channel_param, 'dc_channel');
        if (!$channel_id) {
            return $this->sendError(10000, '渠道参数错误');
        }
        $channel_id = (int)$channel_id;
        $channelInfo = AgentInfoModel::model()->getInfo(['agent_id' => $channel_id, 'agent_level' => 1]);
        if (!$channelInfo) {
            return $this->sendError(10000, '该渠道不存在');
        }

        $code = input('code');

        $accessToken = $this->service->getAccessToken($code);

        if (!$accessToken) {
            return $this->sendError(10001, '获取access_token失败,请重新扫码');
        }

        $jsData = json_decode($accessToken, true);
        if (isset($jsData['errcode'])) {
            return $this->sendError(10001, '获取access_token失败,请重新扫码');
        }

        $access_token = $jsData['access_token'];
        $refresh_token = $jsData['refresh_token'];
        $openid = $jsData['openid'];

        $userRes = $this->service->getUserInfo($access_token, $openid);

        if (!$userRes) {
            return $this->sendError(10001, '获取userinfo失败，可能您没有关注金石游戏公众号,请重新扫码');
        }

        $js_userinfo = json_decode($userRes, true);
        if (isset($js_userinfo['errcode'])) {
            return $this->sendError(10001, '获取userinfo失败,请重新扫码');
        }
        
        // $channel_id = 2;
        // $channelInfo = AgentInfoModel::model()->getInfo(['agent_id' => $channel_id, 'agent_level' => 1]);
        // if (!$channelInfo) {
        //     return $this->sendError(10000, '该渠道不存在');
        // }
        // $js_userinfo = [
        //     'unionid'=>'test_unionid_8888',
        //     'nickname'=>'raoxiaoya_test_8888',
        //     'headimgurl'=>'http://thirdwx.qlogo.cn/mmopen/vi_32/NibTq3q4XBcicUgCUicUhRg5Z96PahvwZr5J7Bf2z6WI92G5Pd3OU3KGFV825wvIY2QD7icLS40oYVA8QFaaNUDrtw/132',
        //     'sex'=>'1',
        //     'openid'=>'raoxiaoya_test_8888'
        // ];

        $playerInfo = PlayerModel::model()->where('player_unionid', $js_userinfo['unionid'])->find();
        if ($playerInfo) {
            if($playerInfo['player_status'] == 0){
                return $this->sendError(10002, '您的账号已被禁用'); 
            }
            $agentInfo = AgentInfoModel::model()->getInfo(['agent_player_id'=>$playerInfo['player_id']]);
            if(!$agentInfo){
                return $this->sendError(10002, '您的账号缺少代理信息');
            }
            if($agentInfo['agent_top_agentid'] == $channel_id){
                return $this->sendError(10002, '您已在该渠道名下');
            }
            if($agentInfo['agent_top_agentid'] != 1){
                return $this->sendError(10002, '您已在其他渠道名下');
            }
            if($agentInfo['agent_parentid'] != 1){
                return $this->sendError(10002, '您已在其他推广员名下');
            }
            if($playerInfo['player_resigter_time'] <= strtotime('-1 week')){
                return $this->sendError(10002, '因超过了一周期限，您的账号无法转移到该渠道名下');
            }
            Db::startTrans();
            $re = $this->doTransfer($playerInfo['player_id'], $channel_id, $agentInfo, 1);

            if($re){
                Db::commit();
                return $this->sendSuccess(
                    ['is_new' => 0, 'msg' => '恭喜您成为【'.$channelInfo['agent_name'].'】名下玩家，打开APP进入游戏立即去赚钱']
                );
            }else{
                Db::rollback();
                return $this->sendError(10001, '操作失败！');
            }
        }else{
            $return = RegisterPlayerBlock::block()
                ->setParentAgentId($channel_id)
                ->wechat(
                    '',
                    $js_userinfo['unionid'],
                    $js_userinfo['nickname'],
                    $js_userinfo['headimgurl'],
                    $js_userinfo['sex'],
                    [
                        'openid' => $js_userinfo['openid'],
                        'p_parentid' => 0
                    ]
                );

            if (!$return) {
                return $this->sendError(10003, '注册推广关系失败,请重新扫码');
            } else {
                MoneyBlock::block()->registerPlayerAwardMoney($return['player_id'], PlayerModel::$registerAward, ['type'=>'player']);
            }

            $res = AgentInfoModel::model()->save(
                [
                    'agent_parentid'     => $channel_id, 
                    'agent_top_agentid'  => $channel_id, 
                    'agent_login_status' => 0,
                    'agent_orignal_type' => 1
                ], 
                [
                    'agent_id' => $return['agent_id']
                ]
            );

            if ($res === FALSE) {
                // 销毁该账号
                PlayerModel::model()->save(['player_unionid'=>PlayerModel::$cancel_remark, 'player_status'=>0], ['player_id'=>$return['player_id']]);
                return $this->sendError(10003, '更新玩家信息失败,请重新扫码');
            }
            
            return $this->sendSuccess(['is_new' => 1, 'msg' => PlayerModel::$registerAward ? PlayerModel::$registerAward : 30000]);
        }

    }

    protected function doTransfer($playerId, $channelId, $agent, $level){
        if($level == 1){
            $agentData = ['agent_parentid'=>$channelId, 'agent_top_agentid'=>$channelId];
            $promoterData = ['promoters_agent_parentid'=>$channelId, 'promoters_agent_top_agentid'=>$channelId];
        }else{
            $agentData = ['agent_top_agentid'=>$channelId];
            $promoterData = ['promoters_agent_top_agentid'=>$channelId];
        }
        if($agent['agent_p_parentid'] == $agent['agent_top_agentid']){
            $agentData['agent_p_parentid'] = $channelId;
        }
        $re1 = AgentInfoModel::model()->save($agentData, ['agent_player_id'=>$playerId]);
        $re2 = PromotersInfoModel::model()->save($promoterData, ['promoters_player_id'=>$playerId]);
        
        if($re1 === false || $re2 === false){
            return false;
        }else{
            $sub = AgentInfoModel::model()->where(['agent_parentid'=>$agent['agent_id']])->select();
            if(empty($sub)){
                return true;
            }
            foreach($sub as $val){
                $re4 = $this->doTransfer($val['agent_player_id'], $channelId, $val, 2);
                if(!$re4){
                    return false;
                }
            }
        }

        return true;
    }

    public function channelGift(){
        $channel_param = $this->request->get('channel_param');

        $code = input('code');

        if (empty($code)) {
            $redirectUri = $this->channelGiftUrl . $channel_param;
            $url = $this->service->getRedirectUrl($redirectUri);
            header("Location:" . $url);
        } else {
            $url = $this->channelGiftHtmlUrl . $channel_param . '&code=' . $code;
            header("Location:" . $url);
        }
    }

    public function receiveGift(){
        $channel_param = $this->request->get('channel_param');
        $gift_id = Helper::think_decrypt($channel_param, 'dc_channel_gift');
        if (!$gift_id) {
            return $this->sendError(10000, '新手礼包参数错误');
        }
        $gift_id = (int)$gift_id;
        $giftInfo = ChannelGiftModel::model()->getOne(['gift_id' => $gift_id, 'gift_status' => ['neq', 3]]);
        if (!$giftInfo) {
            return $this->sendError(10000, '该新手礼包不存在或已失效');
        }

        $code = input('code');

        $accessToken = $this->service->getAccessToken($code);

        if (!$accessToken) {
            return $this->sendError(10001, '获取access_token失败,请重新扫码');
        }

        $jsData = json_decode($accessToken, true);
        if (isset($jsData['errcode'])) {
            return $this->sendError(10001, '获取access_token失败,请重新扫码');
        }

        $access_token = $jsData['access_token'];
        $refresh_token = $jsData['refresh_token'];
        $openid = $jsData['openid'];

        $userRes = $this->service->getUserInfo($access_token, $openid);

        if (!$userRes) {
            return $this->sendError(10001, '获取userinfo失败，可能您没有关注金石游戏公众号,请重新扫码');
        }

        $js_userinfo = json_decode($userRes, true);
        if (isset($js_userinfo['errcode'])) {
            return $this->sendError(10001, '获取userinfo失败,请重新扫码');
        }
        
        // $gift_id = 4;
        // $giftInfo = ChannelGiftModel::model()->getOne(['gift_id' => $gift_id, 'gift_status' => ['neq', 3]]);
        // if (!$giftInfo) {
        //     return $this->sendError(10000, '该新手礼包不存在或已失效');
        // }
        // $js_userinfo = [
        //     'unionid'=>'test_unionid_8888',
        //     'nickname'=>'raoxiaoya_test_8888',
        //     'headimgurl'=>'http://thirdwx.qlogo.cn/mmopen/vi_32/NibTq3q4XBcicUgCUicUhRg5Z96PahvwZr5J7Bf2z6WI92G5Pd3OU3KGFV825wvIY2QD7icLS40oYVA8QFaaNUDrtw/132',
        //     'sex'=>'1',
        //     'openid'=>'raoxiaoya_test_8888'
        // ];
        
        $is_new = false;
        $playerInfo = PlayerModel::model()->where('player_unionid', $js_userinfo['unionid'])->find();
        if ($playerInfo) {
            if($playerInfo['player_status'] == 0){
                return $this->sendError(10002, '您的账号已被禁用'); 
            }
            $agentInfo = AgentInfoModel::model()->getInfo(['agent_player_id'=>$playerInfo['player_id']]);
            if(!$agentInfo){
                return $this->sendError(10002, '您的账号缺少代理信息');
            }
            // 该渠道下才能领
            if($agentInfo['agent_top_agentid'] != $giftInfo['gift_channel_id']){
                return $this->sendError(10002, '您不是该渠道名下，不能领取该礼包');
            }

            $player_id = $playerInfo['player_id'];
        }else{
            $return = RegisterPlayerBlock::block()
                ->setParentAgentId($giftInfo['gift_channel_id'])
                ->wechat(
                    '',
                    $js_userinfo['unionid'],
                    $js_userinfo['nickname'],
                    $js_userinfo['headimgurl'],
                    $js_userinfo['sex'],
                    [
                        'openid' => $js_userinfo['openid'],
                        'p_parentid' => 0
                    ]
                );

            if (!$return) {
                return $this->sendError(10003, '注册推广关系失败,请重新扫码');
            } else {
                MoneyBlock::block()->registerPlayerAwardMoney($return['player_id'], PlayerModel::$registerAward, ['type'=>'player']);
            }

            $res = AgentInfoModel::model()->save(
                [
                    'agent_parentid'     => $giftInfo['gift_channel_id'], 
                    'agent_top_agentid'  => $giftInfo['gift_channel_id'], 
                    'agent_login_status' => 0,
                    'agent_orignal_type' => 1
                ], 
                [
                    'agent_id' => $return['agent_id']
                ]
            );

            if ($res === FALSE) {
                // 销毁该账号
                PlayerModel::model()->save(['player_unionid'=>PlayerModel::$cancel_remark, 'player_status'=>0], ['player_id'=>$return['player_id']]);
                return $this->sendError(10003, '更新玩家信息失败,请重新扫码');
            }
            $is_new = true;
            $player_id = $return['player_id'];
        }

        // 是否领完
        Db::startTrans();
        $giftInfoLock = ChannelGiftModel::model()->getGiftInfoByIdLock($gift_id);
        if ($giftInfoLock['gift_status'] == 2 || $giftInfoLock['gift_count_receive'] >= $giftInfoLock['gift_count']) {
            return $this->sendError(10000, '该新手礼包已领完，请关注渠道的其他礼包~');
        } else {
            $gift_done = false;
            $gift_r1 = ChannelGiftModel::model()->where(['gift_id'=>$gift_id])->setInc('gift_count_receive');
            if(($giftInfoLock['gift_count_receive'] + 1) == $giftInfoLock['gift_count']){
                $gift_done = true;
                $gift_r2 = ChannelGiftModel::model()->save(['gift_status'=>2], ['gift_id'=>$gift_id]);
            }else{
                $gift_r2 = true;
            }
            if ($gift_r1 && $gift_r2) {
                Db::commit();
            } else {
                Db::rollback();
                Log::error('[ receiveGift ] : 礼包信息失败 | gift_id = ' . $gift_id);
                return $this->sendError(10002, '系统繁忙，请稍后重试');
            }
        }

        // 一天只能领一次，可设置，只针对多人礼包
        Db::startTrans();
        if($giftInfo['gift_count'] > 1){
            $mode_id_type = 1;// 多人礼包
            $limit_day = 7;
            $cur_day = strtotime(date('Y-m-d')) - 86400 * ($limit_day - 1);
            $where = [
                'player_id'    =>$player_id, 
                'mode'         =>2,
                'mode_id_type' =>$mode_id_type
            ];
            $logCount = SysCoinChangeLog::model()->getLogCountByPlayerIdLock($where, $cur_day);
        }else{
            $logCount = 0;
            $mode_id_type = 2;// 单人礼包
        }
        
        if($logCount == 0){
            // 领取礼包
            $data = [
                'player_id'      => $player_id,
                'action_user_id' => 0,
                'action_user'    => '',
                'before_coin'    => 0,
                'modified_coin'  => $giftInfo['gift_single_value'],
                'after_coin'     => 0,
                'type'           => 2,
                'channel_id'     => $giftInfo['gift_channel_id'],
                'remark'         => '',
                'mode'           => 2,
                'mode_id'        => $gift_id,
                'mode_id_type'   => $mode_id_type,
                'add_time'       => time(),
                'add_date'       => date('Y-m-d H:i:s'),
            ];
            $log_id = SysCoinChangeLog::model()->insertOne($data);
            if(!$log_id){
                // 恢复礼包状态
                Db::rollback();
                ChannelGiftModel::model()->where(['gift_id'=>$gift_id])->setDec('gift_count_receive');
                if($gift_done){
                    ChannelGiftModel::model()->save(['gift_status'=>1], ['gift_id'=>$gift_id]);
                }
                Log::error('[ receiveGift ] : 领取记录写入失败 | gift_id = ' . $gift_id);
                return $this->sendError(10003, '系统繁忙，请稍后重试');
            }else{
                Db::commit();
            }
        }else{
            // 恢复礼包状态
            Db::rollback();
            ChannelGiftModel::model()->where(['gift_id'=>$gift_id])->setDec('gift_count_receive');
            if($gift_done){
                ChannelGiftModel::model()->save(['gift_status'=>1], ['gift_id'=>$gift_id]);
            }
            return $this->sendError(10003, '您在最近'.$limit_day.'天里已经领过礼包了，请继续关注渠道活动');
        }

        // 金币入账
        $exist = PlayerRedis::exists($player_id);
        $detail = PlayerInfoModel::model()->getPlayerinfoOne($player_id);

        if($exist){
            $before_coin = PlayerRedis::hget($player_id, 'player_coins');
            $result  = PlayerRedis::hincrby($player_id, 'player_coins', $giftInfo['gift_single_value']);
            $after_coin = $before_coin + $giftInfo['gift_single_value'];
        }else{
            $before_coin = $detail['player_coins'];
            $result = PlayerInfoModel::model()->where(['player_id'=>$player_id])->setInc('player_coins', $giftInfo['gift_single_value']);
            $after_coin = $before_coin + $giftInfo['gift_single_value'];
        }

        if($result !== false){
            SysCoinChangeLog::model()->save(['before_coin'=>$before_coin, 'after_coin'=>$after_coin], ['id'=>$log_id]);
            // 添加日志
            ChangeMoneyInfoModel::model()->addLogByPlayerId([
                'change_money_player_id'   => $player_id,
                'change_money_begin_value' => $before_coin,
                'change_money_money_value' => $giftInfo['gift_single_value'],
                'change_money_after_value' => $after_coin,
                'change_money_type'        => 8,// 新手礼包
                'change_money_money_type'  => ChangeMoneyInfoModel::CHANG_MONEY_MONEY_TYPE_GOLD
            ]);
            return $this->sendSuccess(['is_new' => $is_new, 'msg' => '恭喜您领取'.$giftInfo['gift_single_value'].'金币，请进入游戏查看吧~']);
        }else{
            if($exist){
                PlayerRedis::hdecrby($player_id, 'player_coins', $giftInfo['gift_single_value']);
            }else{
                PlayerInfoModel::model()->where(['player_id'=>$player_id])->setDec('player_coins', $giftInfo['gift_single_value']);
            }
            // 恢复礼包状态
            ChannelGiftModel::model()->where(['gift_id'=>$gift_id])->setDec('gift_count_receive');
            if($gift_done){
                ChannelGiftModel::model()->save(['gift_status'=>1], ['gift_id'=>$gift_id]);
            }
            // 删除领取记录
            SysCoinChangeLog::model()->where(['id'=>$log_id])->delete();
            Log::error('[ receiveGift ] : 金币入账失败 | gift_id = ' . $gift_id);
            return $this->sendError(10003, '系统繁忙，请稍后重试');
        }
        
    }

}
