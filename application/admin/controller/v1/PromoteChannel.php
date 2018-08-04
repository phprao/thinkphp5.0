<?php
/**
 * +----------------------------------------------------------
 * date: 2018-02-02 17:56:19
 * +----------------------------------------------------------
 * author: Raoxiaoya
 * +----------------------------------------------------------
 * describe: 推广渠道
 * +----------------------------------------------------------
 */

namespace app\admin\controller\v1;

use app\admin\controller\Controller;
use app\admin\model\AgentInfoModel;
use app\common\components\Helper;
use app\admin\model\UsersModel;
use app\admin\block\LoginBlock;
use app\admin\model\AgentAccountInfoModel;
use app\admin\model\AgentSuperStatisticsModel;
use app\admin\model\AgentSuperIncomeConfigModel;
use app\admin\model\StatisticsTotalModel;
use app\admin\model\AuthUserModel;
use app\admin\model\UsersActionLogModel;
use app\admin\model\ConfigModel;
use app\admin\model\ChannelInfoModel;
use app\admin\model\ChannelCoinsLogModel;
use app\admin\model\AgentGameModel;
use app\admin\model\PartnerModel;
use app\admin\model\PartnerAccountModel;
use think\Db;

/**
 * @controllerName 推广渠道
 */
class promoteChannel extends Controller
{
    protected $size = 10;
    protected $error_code = 0;
    protected $error_msg = '';
    protected $max_search_num = 50;
    protected $max_search_nitice = '请再精确一下关键词';

    /**
     * 初始化操作
     * @access protected
     */
    protected function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->isLogin($this->token);
        if(!$this->userInfo){
            return $this->sendError(10000, 'token校验失败');
        }
    }

    protected function initRequestParam()
    {
        $input = array(
            'keyword' => (string)$this->request->get('keyword'),
            'page' => (int)$this->request->get('page'),
            'size' => (int)$this->request->get('size'),
            'start' => (string)$this->request->get('start'),// 加入时间
            'end' => (string)$this->request->get('end')
        );
        /*
        $input = array(
            'start'     =>'2017-10-1',//2017-10-1
            'end'       =>'2018-12-2',//2018-2-2
            'keyword'   =>'',// 玩家ID  601709
            'page'      =>1,
            'size'      =>'10'
        );
        */
        $filter = array();

        $start = strtotime($input['start']);
        $end = strtotime($input['end']);
        $time = time();
        $timestamp_day = strtotime(date('Y-m-d', $time));

        if ($start && $end) {
            if ($start > $end) {
                $this->returnCode(10001, '请选择合适的时间区间');
                return false;
            }

            $start = strtotime(date('Y-m-d', $start));
            $end = strtotime(date('Y-m-d', $end));
            // 包含今日
            if ($start > $timestamp_day) {
                $this->returnCode(10001, '请选择合适的时间区间');
                return false;
            }
            $filter['start'] = $start;
            if ($end > $timestamp_day) {
                $filter['end'] = $timestamp_day + 86400;
            } elseif ($end == $timestamp_day) {
                $filter['end'] = $end + 86400;
            } elseif ($end < $timestamp_day) {
                $filter['end'] = $end + 86400;
            }
        }
        // else {
        //     if (!$start && !$end) {
        //         $filter['start'] = $timestamp_day;
        //         $filter['end'] = strtotime('+1 day', $filter['start']);
        //     } else {
        //         $this->returnCode(10001, '请选择合适的时间区间');
        //         return false;
        //     }
        // }

        if ($input['keyword'] !== '') {
            $filter['keyword'] = $input['keyword'];
        }

        if ($input['page']) {
            $filter['page'] = $input['page'];
        } else {
            $filter['page'] = 1;
        }

        if ($input['size']) {
            $filter['size'] = $input['size'];
        } else {
            $filter['size'] = $this->size;
        }


        if ($this->error_code) {
            return false;
        } else {
            $filter = (object)$filter;
            // 搜所筛选
            if (isset($filter->keyword) && $filter->keyword) {
                if (request()->action() == 'partnerDetailList') {
                    $ret_arr = $this->checkPartnerExists($filter);
                    if (!is_bool($ret_arr) && empty($ret_arr)) {
                        $this->returnCode(10002, '没有符合条件的渠道信息');
                        return false;
                    } else {
                        if (!$ret_arr) {
                            return false;
                        }
                        $filter->keyword = $ret_arr;
                    }
                }
            }

            return $filter;
        }

    }

    /**
     * @actionName 渠道列表
     * @param  string $condition
     * @return [type]          [description]
     */
    public function getAllPartner()
    {
        $list = PartnerModel::model()->getAllPartnerInfo();
        return $this->sendSuccess(['list' => $list]);
    }

    /**
     * @actionName 添加或修改渠道信息
     */
    public function addAndUpdateChannel()
    {
        $input = [
            'partner_id'   => (int)$this->request->post('partner_id'),
            'partner_name' => (string)$this->request->post('partner_name'),
            'user_login'   => (string)$this->request->post('partner_login'),
            'user_pass'    => (string)$this->request->post('partner_pass'),
            'remark'       => (string)$this->request->post('partner_remark'),
            'game_id'      => (string)$this->request->post('game_id'),
            'share_rate'   => $this->request->post('share_rate'),
        ];

        /*
        $input = [
            'partner_id'   => '',
            'partner_name' => 'partner_kk',
            'user_login'   => 'partnerkk',
            'user_pass'    => '12345678',
            'remark'       => 'partner_3',
            'game_id'      => [10001401],
            'share_rate'   => '0.28'
        ];
        */
        
        if ($input['game_id']) {
            $game_list = is_array($input['game_id']) ? $input['game_id'] : explode(',', $input['game_id']);
        } else {
            $game_list = [];
        }
        
        if (!$input['partner_id']) {
            $input['partner_id'] = 0;
        }

        /* a-z,A-Z,0-9,~,!,@,#,$,%,^,&,*,(,),_,+
         * 登录账号或者密码
         */
        if (!Helper::checkString($input['partner_name'], 2) && strlen($input['partner_name']) > 16 * 3) {
            return $this->sendError(10000, '渠道名称参数错误');
        }
        if (!Helper::checkString($input['user_login'], 1)) {
            return $this->sendError(10000, '渠道后台登陆账号必须为数字、英文，长度为6到16位数');
        }
        if (!is_numeric($input['share_rate']) || $input['share_rate'] >= 1) {
            return $this->sendError(10000, '分成比例需小于1');
        }
        if (!$input['partner_id']) {
            // 新增时，密码必填
            if (!Helper::checkString($input['user_pass'], 1)) {
                return $this->sendError(10000, '渠道后台登陆密码必须为数字、英文，长度为6到16位数');
            } else {
                $input['user_pass'] = LoginBlock::passwordEncrypt($input['user_pass']);
            }
        } else {
            // 修改时，不填表示不修改
            if ($input['user_pass'] != '') {
                if (!Helper::checkString($input['user_pass'], 1)) {
                    return $this->sendError(10000, '渠道后台登陆密码必须为数字、英文，长度为6到16位数');
                } else {
                    $input['user_pass'] = LoginBlock::passwordEncrypt($input['user_pass']);
                }
            } else {
                unset($input['user_pass']);
            }
        }

        Db::startTrans();

        if($input['partner_id']){
            $agent = $this->updatePromoteChannel($input);
            $re1 = $agent;
            $re2 = $re3 = $re4 = true;
        }else{
            $re1 = $this->addPromoteChannel($input);
            if($re1){
                $data = [
                    'agent_parent_id' => $re1,
                    'agent_name'      => '推广渠道_'.$input['partner_name'].'_'.$re1,
                    'user_login'      => $input['user_login'].'pro',
                    'user_pass'       => $input['user_pass'],
                    'user_type'       => 2,
                    'agent_remark'    => '推广渠道',
                    'agent_partner_id'=> $re1
                ];
                $re2 = $this->addChannelAction($data);
                if($re2){
                    $re3 = $this->addChannelConfig($re2);
                    $re4 = PartnerModel::model()->save(['channel_id'=>$re2], ['partner_id'=>$re1]);
                }else{
                    $re3 = $re4 = false;
                }
            }else{
                $re2 = $re3 = $re4 = false;
            }

            $agent = $re2;
        }

        // 操作日志
        $action_type = $input['partner_id'] > 0 ? UsersActionLogModel::ACTION_MODIFY : UsersActionLogModel::ACTION_ADD;
        $action_name = $input['partner_id'] > 0 ? '修改推广渠道信息' : '新增推广渠道';
        $action_after = '';
        $re6 = UsersActionLogModel::model()->addActionLog($this->userInfo['userInfo']['id'], $action_type, $action_name, '', $action_after);

        //添加游戏
        if ($game_list) {
            $temp = true;
            foreach ($game_list as $key => $value) {
                $info = AgentGameModel::model()->getInfo(['agent_agent_id'=>$agent,'agent_game_game_id'=>$value]);
                if(!$info){
                    $gamecondint['agent_agent_id']     = $agent;
                    $gamecondint['agent_game_game_id'] = $value;
                    $gamecondint['agent_game_order']   = 1;
                    $gamecondint['agent_host']         = 5;
                    $gamecondint['agent_game_time']    = time();
                    $temp = AgentGameModel::model()->insertID($gamecondint);
                }
            }
            if(!$temp){
                $re5 = false;
            }else{
                $re5 = AgentGameModel::model()->where(['agent_agent_id'=>$agent,'agent_game_game_id'=>['not in',$game_list]])->delete();
            }
        } else {
            $re5 = AgentGameModel::model()->where(['agent_agent_id'=>$agent])->delete();
        }

        if ($re1 && $re2 && $re3 && $re4 !== false && $re5 !== false && $re6) {
            Db::commit();
            return $this->sendSuccess();
        } else {
            Db::rollback();
            return $this->sendError(10001, $this->error_msg ? $this->error_msg : '写入失败！');
        }
    }


    /**
     * @actionName 编辑渠道信息-详情
     */
    public function getSimplePartnerInfo()
    {
        $partner_id = (int)$this->request->get('partner_id');
        if (!$partner_id) {
            return $this->sendError(10000, '渠道id错误');
        }

        $pinfo = PartnerModel::model()->getOne(['partner_id'=>$partner_id]);
        if (!$pinfo) {
            return $this->sendError(10000, '该渠道不存在！');
        }

        $ainfo = AgentInfoModel::model()->getInfo(['agent_id' => $pinfo['channel_id'], 'agent_level' => 1, 'agent_parentid' => 0]);
        if (!$ainfo) {
            return $this->sendError(10000, '该渠道不存在！');
        }
        $user_id = $pinfo['login_user_id'];
        $uinfo = UsersModel::model()->getUserById($user_id);
        if (!$uinfo) {
            return $this->sendError(10000, '该渠道对应的账号不存在！');
        }
        $list = [
            'partner_name'   => $pinfo['partner_name'],
            'share_rate'     => $pinfo['share_rate'],
            'partner_login'  => $uinfo['user_login'],
            'partner_remark' => $pinfo['remark']
        ];

        //获取已有的游戏
        $field = "agent_game_id,agent_agent_id,agent_game_game_id";
        $data_game = AgentGameModel::model()->getList(array('agent_agent_id' => $pinfo['channel_id']), $field);
        $topicid = '';
        foreach ($data_game as $key => $datum) {
            $topicid .= $datum['agent_game_game_id'] . ',';
        }
        return $this->sendSuccess(['list' => $list, 'game_info' => $topicid]);
    }

    /**
     * @actionName 渠道列表-详细
     * @return [type] [description]
     */
    public function partnerDetailList()
    {
        $condition = $this->initRequestParam();
        if ($condition === false) {
            return $this->sendError($this->error_code, $this->error_msg);
        }

        $list = PartnerModel::model()->getAllPartnerInfo($condition);
        foreach ($list->items() as $item) {
            // 账号
            $uinfo = UsersModel::model()->getUserById($item->login_user_id);
            if ($uinfo) {
                $item->partner_login = $uinfo['user_login'];
            } else {
                $item->partner_login = '';
            }
            // 名下玩家总数
            
            $item->player_num = AgentInfoModel::model()->where(['agent_top_agentid'=>$item->channel_id])->count();
            // 名下玩家金币总数
            $param = [
                'statistics_role_type' => 1,
                'statistics_role_value' => $item->channel_id,
                'statistics_mode' => 8,// 8-剩余金币数
                'statistics_type' => 3
            ];
            $last_coin = StatisticsTotalModel::model()->getOne($param);
            if ($last_coin) {
                $item->last_coin = Helper::fomatBigData($last_coin['statistics_sum']);
            } else {
                $item->last_coin = 0;
            }
            
        }
        $filterDate = $this->addTime($condition);
        return $this->sendSuccess(['list' => $list, 'date' => $filterDate]);
    }

    protected function updatePromoteChannel($input){
        $info = PartnerModel::model()->getOne(['partner_id'=>$input['partner_id']]);
        if (!$info) {
            return false;
        }
        $input['id'] = $info['login_user_id'];
        $re1 = UsersModel::model()->updateUserInfo($input);
        $re2 = PartnerModel::model()->save(
            ['partner_name'=>$input['partner_name'],'share_rate'=>$input['share_rate'],'remark'=>$input['remark']], 
            ['partner_id'=>$input['partner_id']]
        );
        if ($re1 !== FALSE && $re2 !== FALSE) {
            return $info['channel_id'];
        } else {
            return false;
        }
    }

    protected function addPromoteChannel($data){
        // agent_name是否已存在
        $ainfo = PartnerModel::model()->getOne(['partner_name'=>$data['partner_name']]);
        if ($ainfo) {
            $this->returnCode(10001, '该渠道名称已存在！');
            return false;
        }
        // user_login是否已存在
        $uinfo = UsersModel::model()->getUserByLogin($data['user_login']);
        if ($uinfo) {
            $this->returnCode(10001, '该账号已存在！');
            return false;
        }

        // 新增后台账户
        $data['user_type'] = 3;
        $re1 = UsersModel::model()->addUser($data);
        $data['login_user_id'] = $re1;
        // 新增渠道信息
        $re2 = PartnerModel::model()->insertOne([
            'partner_name'  =>$data['partner_name'],
            'login_user_id' =>$data['login_user_id'],
            'share_rate'    =>$data['share_rate'],
            'remark'        =>$data['remark'],
            'create_time'   =>time(),
            'create_date'   =>date('Y-m-d')
        ]);
        // 新增资金账户
        $data['partner_id'] = $re2;
        $re3 = PartnerAccountModel::model()->addAgentAccount($data);
        // 接口权限
        $re4 = AuthUserModel::model()->addRoleByUserId($re1, [7]);
        if ($re1 && $re2 && $re3 && $re4) {
            return $re2;
        } else {
            $this->returnCode(10001, '操作失败！');
            return false;
        }

        return false;
    }

    protected function addChannelConfig($agent){
        // 配置项处理
        $confArr_init = [];
        $re = true;
        $confArrs = [
            [0,0],
            [150000,0],
            [300000,0],
            [500000,0],
            [800000,0],
            [1000000,0]
        ];

        foreach ($confArrs as $key => $val) {
            if ($val[0] !== '' && $val[1] !== '') {
                $temp = ['super_agent_id' => $agent, 'super_condition' => $val[0], 'super_share' => $val[1] * 100];
                array_push($confArr_init, $temp);
            }
        }
        if (!empty($confArr_init)) {
            AgentSuperIncomeConfigModel::model()->where(['super_agent_id' => $agent])->delete();
            $re = AgentSuperIncomeConfigModel::model()->setSuperIncomeConfig($confArr_init);
        }
        return $re;
    }

    protected function addChannelAction($data)
    {
        // agent_name是否已存在
        $ainfo = AgentInfoModel::model()->getSuperAgentByName($data['agent_name']);
        if ($ainfo) {
            $this->returnCode(10001, '该渠道名称已存在！');
            return false;
        }
        // user_login是否已存在
        $uinfo = UsersModel::model()->getUserByLogin($data['user_login']);
        if ($uinfo) {
            $this->returnCode(10001, '该账号已存在！');
            return false;
        }

        // 新增后台账户
        $data['user_type'] = 2;
        $re1 = UsersModel::model()->addUser($data);
        $data['agent_user_id'] = $re1;
        // 新增渠道信息
        $re2 = AgentInfoModel::model()->addChannel($data);
        // 新增资金账户
        $data['agent_id'] = $re2;
        $re3 = AgentAccountInfoModel::model()->addAgentAccount($data);
        // 接口权限
        $re4 = AuthUserModel::model()->addRoleByUserId($re1, [2]);
        if ($re1 && $re2 && $re3 && $re4) {
            return $re2;
        } else {
            $this->returnCode(10001, '操作失败！');
            return false;
        }

        return false;
    }

    /**
     * 检索渠道信息
     * @param  string $condition
     * @return [type]          [description]
     */
    protected function checkPartnerExists($condition)
    {
        $condition->channel = $condition->keyword;
        $size = $condition->size;
        unset($condition->size);
        $superInfo = PartnerModel::model()->getPartnerInfoByKeyword($condition);
        if (count($superInfo) > $this->max_search_num) {
            $this->returnCode(10001, $this->max_search_nitice);
            return false;
        }
        $ids = array();
        if (count($superInfo) > 0) {
            foreach ($superInfo as $item) {
                array_push($ids, $item['partner_id']);
            }
        }
        $condition->size = $size;
        return array_unique($ids);
    }

    protected function returnCode($code, $error)
    {
        $this->error_code = $code;
        $this->error_msg = $error;
    }

    protected function addTime($condition = null)
    {
        $ret_time = [];
        if (isset($condition->start)) {
            $ret_time['start_date'] = date('Y-m-d', $condition->start);
        }
        if (isset($condition->end)) {
            $ret_time['end_date'] = date('Y-m-d', $condition->end - 86400);
        }
        return $ret_time;
    }


}