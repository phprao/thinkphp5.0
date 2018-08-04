<?php
/**
 * +----------------------------------------------------------
 * date: 2018-02-02 17:56:19
 * +----------------------------------------------------------
 * author: Raoxiaoya
 * +----------------------------------------------------------
 * describe: 渠道商管理
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
use think\Db;

/**
 * @controllerName 渠道（特代）管理
 */
class SuperAgent extends Controller
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
            'end'       =>'2018-3-2',//2018-2-2
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
                if (request()->action() == 'channelDetailList') {
                    $ret_arr = $this->checkSuperAgentExists($filter);
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
    public function getSuperAgent()
    {
        $list = AgentInfoModel::model()->getAllSuperAgentInfo();
        return $this->sendSuccess(['list' => $list]);
    }

    /**
     * @actionName 添加或修改渠道信息
     */
    public function addAndUpdateChannel()
    {
        $input = [
            'agent_id' => (int)$this->request->post('channel_id'),
            'agent_name' => (string)$this->request->post('channel_name'),
            'user_login' => (string)$this->request->post('channel_login'),
            'user_pass' => (string)$this->request->post('channel_pass'),
            'agent_remark' => (string)$this->request->post('channel_remark'),
            'game_id' => (string)$this->request->post('game_id'),
        ];
        if ($input['game_id']) {
            $game_list = explode(',', $input['game_id']);
        } else {
            $game_list = [];
        }

        /*
        $input = [
            'agent_id'    => 91742,
            'agent_name'  => '渠道——测试mmm',
            'user_login'  => 'channelmmm',
            'user_pass'   => '123456',
            'agent_remark' => 'mmm'
        ];
        */
        if (!$input['agent_id']) {
            $input['agent_id'] = 0;
        }

        /* a-z,A-Z,0-9,~,!,@,#,$,%,^,&,*,(,),_,+
         * 登录账号或者密码
         */
        if (!Helper::checkString($input['agent_name'], 2) && strlen($input['agent_name']) > 16 * 3) {
            return $this->sendError(10000, '渠道名称参数错误');
        }
        if (!Helper::checkString($input['user_login'], 1)) {
            return $this->sendError(10000, '渠道后台登陆账号必须为数字、英文，长度为6到16位数');
        }

        if (!$input['agent_id']) {
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
        $agent = $input['agent_id'];
        if ($input['agent_id'] > 0) {
            $re1 = $this->updateChannel($input);
            if ($re1 !== false)
                $re1 = true;
            else
                $re1 = false;
        } else {
            $re1 = $this->addChannelAction($input);
            $agent = $re1;
        }

        // 配置项处理
        $confArr_init = [];
        if ($re1) {
            $re2 = true;

            /*
            $confArrs = [
                [0,7],
                [150000,9],
                [300000,10],
                [500000,11],
                [750000,12],
                [1100000,13],
                [1600000,14],
                [2400000,15],
                [3000000,16],
                [3800000,17],
                [4400000,18],
                [5200000,19],
                [6000000,20]
            ];
            $_POST['config'] = $confArrs;
            */

            if (isset($_POST['config'])) {
                $confArr = $_POST['config'];
                foreach ($confArr as $key => $val) {
                    if ($val[0] !== '' && $val[1] !== '' && $val[2] !== '') {
                        $temp = ['super_agent_id'=>$agent,'super_condition'=>$val[0],'super_share'=>$val[1]*100,'super_share_ext'=>$val[2]*100];
                        array_push($confArr_init, $temp);
                    }
                }
                if (!empty($confArr_init)) {
                    AgentSuperIncomeConfigModel::model()->where(['super_agent_id' => $agent])->delete();
                    $re2 = AgentSuperIncomeConfigModel::model()->setSuperIncomeConfig($confArr_init);
                }
            } else {
                AgentSuperIncomeConfigModel::model()->where(['super_agent_id' => $agent])->delete();
            }
        } else {
            $re2 = false;
        }

        // 操作日志
        $action_type = $input['agent_id'] > 0 ? UsersActionLogModel::ACTION_MODIFY : UsersActionLogModel::ACTION_ADD;
        $action_name = $input['agent_id'] > 0 ? '修改渠道信息' : '新增渠道';
        $action_after = json_encode($confArr_init);
        $re3 = UsersActionLogModel::model()->addActionLog($this->userInfo['userInfo']['id'], $action_type, $action_name, '', $action_after);


        //添加游戏
        if ($game_list) {
            foreach ($game_list as $key => $value) {
                $datainfo = AgentGameModel::model()->getOne(['agent_agent_id' => $input['agent_id'], 'agent_game_game_id' => $value]);
                if (!$datainfo) {
                    $gamecondint['agent_game_game_id'] = $value;
                    $gamecondint['agent_agent_id'] = $agent;
                    $gamecondint['agent_game_order'] = 1;
                    $gamecondint['agent_host'] = 5;
                    $gamecondint['agent_game_time'] = time();
                    $re4 = AgentGameModel::model()->insertID($gamecondint);
                }
            }
            $re4 = AgentGameModel::model()->where(['agent_agent_id' => $input['agent_id'], 'agent_game_game_id' => ['not in', $game_list]])->delete();
        } else {
            $re4 = AgentGameModel::model()->where(['agent_agent_id' => $input['agent_id']])->delete();
        }


        if ($re1 && $re2 && $re3 && $re4 !==false) {
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
    public function getSimpleChannelInfo()
    {
        $channel_id = (int)$this->request->get('channel_id');
        if (!$channel_id) {
            return $this->sendError(10000, '渠道id错误');
        }

        $ainfo = AgentInfoModel::model()->getInfo(['agent_id' => $channel_id, 'agent_level' => 1, 'agent_parentid' => 0]);
        if (!$ainfo) {
            return $this->sendError(10000, '该渠道不存在！');
        }
        $user_id = $ainfo['agent_user_id'];
        $uinfo = UsersModel::model()->getUserById($user_id);
        if (!$uinfo) {
            return $this->sendError(10000, '该渠道对应的账号不存在！');
        }
        $list = [
            'channel_name' => $ainfo['agent_name'],
            'channel_login' => $uinfo['user_login'],
            'channel_remark' => $ainfo['agent_remark']
        ];
        $conf = AgentSuperIncomeConfigModel::model()->getSuperIncomeConfig($channel_id);
        $config = [];
        if ($conf) {
            foreach ($conf as $key => $val) {
                if (isset($conf[$key + 1])) {
                    $n = $conf[$key + 1]['super_condition'];
                } else {
                    $n = '++';
                }
                array_push($config, [$val['super_condition'], $n, $val['super_share'] / 100, $val['super_share_ext'] / 100]);
            }
        }

        //获取已有的游戏
        $field = "agent_game_id,agent_agent_id,agent_game_game_id";
        $data_game = AgentGameModel::model()->getList(array('agent_agent_id' => $channel_id), $field);
        $topicid = '';
        foreach ($data_game as $key => $datum) {
//            $gameinfo = GameInfoModel::model()->getOne(array('game_id' => $datum['agent_game_game_id']));
//            $data_game[$key]['game_name'] = $gameinfo['game_name'];
            $topicid .= $datum['agent_game_game_id'] . ',';
        }
//        return $this->sendSuccess(['list' => $list,'config'=>$config]);
        return $this->sendSuccess(['list' => $list, 'config' => $config, 'game_info' => $topicid]);

    }

    /**
     * @actionName 获取系统默认配置
     */
    public function getSystemConfig()
    {
        $conf = AgentSuperIncomeConfigModel::model()->getSuperIncomeConfig(0);
        $config = [];
        if ($conf) {
            foreach ($conf as $key => $val) {
                if (isset($conf[$key + 1])) {
                    $n = $conf[$key + 1]['super_condition'];
                } else {
                    $n = '++';
                }
                array_push($config, [$val['super_condition'], $n, $val['super_share'] / 100, $val['super_share_ext'] / 100]);
            }
        }
        return $this->sendSuccess(['config' => $config]);
    }

    /**
     * @actionName 修改渠道信息>异步保存数据
     */
    public function setAjaxData()
    {
        $input = [
            'agent_id' => (int)$this->request->post('channel_id'),
            'agent_name' => (string)$this->request->post('channel_name'),
            'user_login' => (string)$this->request->post('channel_login'),
            'user_pass' => (string)$this->request->post('channel_pass'),
            'agent_remark' => (string)$this->request->post('channel_remark')
        ];
        /*
        $input = [
            'agent_id'     => 2,
            'agent_name'   => '',
            'user_login'   => 'admin111',
            'user_pass'    => '',
            'agent_remark' => ''
        ];
        */
        if (!$input['agent_id']) {
            return $this->sendError(10000, '渠道id参数错误');
        }

        /* a-z,A-Z,0-9
         * 登录账号或者密码
         */
        if ($input['agent_name'] && !Helper::checkString($input['agent_name'], 2) && strlen($input['agent_name']) > 16 * 3) {
            return $this->sendError(10000, '渠道名称参数错误');
        }
        if ($input['user_login'] && !Helper::checkString($input['user_login'], 1)) {
            return $this->sendError(10000, '渠道后台登陆账号必须为数字、英文，长度为6到16位数');
        }
        if ($input['user_pass'] && !Helper::checkString($input['user_pass'], 1)) {
            return $this->sendError(10000, '渠道后台登陆密码必须为数字、英文，长度为6到16位数');
        }

        // 不为空则写入密码
        if ($input['user_pass'] != '') {
            if (!Helper::checkString($input['user_pass'], 1)) {
                return $this->sendError(10000, '渠道后台登录密码必填且6-16位');
            } else {
                $input['user_pass'] = LoginBlock::passwordEncrypt($input['user_pass']);
            }
        }
        if (!$input['agent_name'] && !$input['user_login'] && !$input['user_pass'] && !$input['agent_remark']) {
            return $this->sendError(10000, '参数错误');
        }
        $input = array_filter($input);
        $re = $this->updateChannel($input);
        if ($re) {
            return $this->sendSuccess();
        } else {
            return $this->sendError($this->error_code, $this->error_msg);
        }
    }

    /**
     * @actionName 渠道列表-详细
     * @return [type] [description]
     */
    public function channelDetailList()
    {
        $condition = $this->initRequestParam();
        if ($condition === false) {
            return $this->sendError($this->error_code, $this->error_msg);
        }
        // 渠道应得直接推广玩家服务费收益分成比例
        $config = ConfigModel::model()->getConfigArray(['config_name' => 'channel_income_rate_from_direct', 'config_status' => 1]);
        if ($config) {
            $config_rate = ($config[0]['config_config']['rate'] / 100) . '%';
        } else {
            $config_rate = '0%';
        }

        $condition->field = ['agent_id', 'agent_name', 'agent_createtime', 'agent_user_id', 'agent_status'];
        $list = AgentInfoModel::model()->getAllSuperAgentInfo($condition);
        foreach ($list->items() as $item) {
            $item->agent_createtime = date('Y-m-d H:i:s', $item->agent_createtime);
            // 账号
            $uinfo = UsersModel::model()->getUserById($item->agent_user_id);
            if ($uinfo) {
                $item->agent_login = $uinfo['user_login'];
            } else {
                $item->agent_login = '';
            }
            // 名下玩家总数
            $condition->channel_id = $item->agent_id;
            $item->player_num = AgentInfoModel::model()->getChannelPlayerCount($condition, 0);
            // 名下玩家金币总数
            $param = [
                'statistics_role_type' => 1,
                'statistics_role_value' => $condition->channel_id,
                'statistics_mode' => 8,// 8-剩余金币数
                'statistics_type' => 3
            ];
            $last_coin = StatisticsTotalModel::model()->getOne($param);
            if ($last_coin) {
                $item->last_coin = Helper::fomatBigData($last_coin['statistics_sum']);
            } else {
                $item->last_coin = 0;
            }
            // 名下星级推广员总数
            $item->star_num = AgentInfoModel::model()->getChannelPlayerCount($condition, 1);
            // 金币消耗分成比例-本月到目前为止的比例
            $timestamp = strtotime(date('Y-m', time()));
            $data = AgentSuperStatisticsModel::model()->getSuperStatisticsData($condition->channel_id, $timestamp);
            if (empty($data)) {
                $money = 0;
            } else {
                $money = $data['statistics_money_data'] / $data['statistics_money_rate_value'];
            }
            $config = AgentSuperIncomeConfigModel::model()->getSuperIncomeConfig($condition->channel_id, 'desc');
            $config = AgentSuperIncomeConfigModel::model()->configObjToArray($config);
            $super_rate = AgentSuperIncomeConfigModel::model()->getSuperIncomeRate($config, $money);
            $item->super_rate = ($super_rate / 100) . '%';
            // 直接推广星级推广员总数
            $item->direct_star_num = AgentInfoModel::model()->getCount(['agent_parentid' => $item->agent_id, 'agent_login_status' => 1]);
            // 直接推广玩家总数
            $item->direct_player_num = AgentInfoModel::model()->getCount(['agent_parentid' => $item->agent_id, 'agent_login_status' => 0]);
            $item->direct_rate = $config_rate;
        }
        $filterDate = $this->addTime($condition);
        return $this->sendSuccess(['list' => $list, 'date' => $filterDate]);
    }

    /**
     * @actionName 渠道活动--新手礼包
     */
    public function channelActivity()
    {
        $condition = $this->initRequestParam();
        if ($condition === false) {
            return $this->sendError($this->error_code, $this->error_msg);
        }
        $condition->field = ['agent_id', 'agent_name', 'agent_createtime', 'agent_user_id', 'agent_status'];
        $list = AgentInfoModel::model()->getAllSuperAgentInfo($condition);
        foreach ($list->items() as $item) {
            $item->agent_createtime = date('Y-m-d H:i:s', $item->agent_createtime);
            // 账号
            $uinfo = UsersModel::model()->getUserById($item->agent_user_id);
            if ($uinfo) {
                $item->agent_login = $uinfo['user_login'];
            } else {
                $item->agent_login = '';
            }
            // 渠道金币数
            $info = ChannelInfoModel::model()->getOne(['channel_id' => $item->agent_id]);
            if ($info) {
                $item->channel_coins = Helper::fomatBigData($info['channel_coins']);
            } else {
                $item->channel_coins = 0;
            }
        }

        $filterDate = $this->addTime($condition);
        return $this->sendSuccess(['list' => $list, 'date' => $filterDate]);
    }

    public function modifyChannelInfo()
    {
        $channel_id = $this->request->get('channel_id');
        $channel_coins = $this->request->get('channel_coins');

        if (!is_numeric($channel_id) || !is_numeric($channel_coins) || $channel_coins == 0) {
            return $this->sendError(10000, '参数错误');
        } else {
            $channel_id = (int)$channel_id;
            $channel_coins = (int)$channel_coins;
        }

        $channel = AgentInfoModel::model()->getInfo(['agent_id' => $channel_id]);
        if (!$channel) {
            return $this->sendError(10000, '该渠道不存在');
        }
        if ($channel['agent_status'] == 0) {
            return $this->sendError(10000, '该渠道已被禁用');
        }

        $info = ChannelInfoModel::model()->getOne(['channel_id' => $channel_id]);

        Db::startTrans();

        if ($info) {
            $r1 = ChannelInfoModel::model()->where(['channel_id' => $channel_id])->setInc('channel_coins', $channel_coins);
            $before_coins = $info['channel_coins'];
            $after_coins = $info['channel_coins'] + $channel_coins;
        } else {
            $r1 = ChannelInfoModel::model()->insertOne([
                'channel_id' => $channel_id,
                'channel_coins' => $channel_coins,
                'add_time' => time()
            ]);
            $before_coins = 0;
            $after_coins = $channel_coins;
        }

        if ($after_coins < 0) {
            Db::rollback();
            return $this->sendError(10000, '操作失败，最终金币数不能为负数');
        }

        $r2 = ChannelCoinsLogModel::model()->insertOne([
            'channel_id' => $channel_id,
            'channel_coins_before' => $before_coins,
            'channel_coins_change' => $channel_coins,
            'channel_coins_after' => $after_coins,
            'channel_coins_change_type' => 2,
            'add_time' => time(),
            'add_date' => date('Y-m-d H:i:s')
        ]);

        if ($r1 && $r2) {
            Db::commit();
            return $this->sendSuccess(['before_coin' => (int)$before_coins, 'after_coin' => $after_coins]);
        } else {
            Db::rollback();
            return $this->sendError(10000, '写入失败');
        }

    }

    // public function test(){
    //     set_time_limit(0);
    //     $model = new AgentInfoModel();
    //     $re = $model->where(['agent_user_id'=>0,'agent_parentid'=>0])->limit(8000)->select();
    //     foreach($re as $r){
    //         $pid = rand(1,$r['agent_id']-1);
    //         $model->where(['agent_id'=>$r['agent_id']])->update(['agent_parentid'=>$pid]);
    //         $pre = $model->where(['agent_id'=>$pid])->find();
    //         if($pre){
    //             $ppid = $pre['agent_parentid'];
    //         }else{
    //             $ppid = 1;
    //         }
    //         $model->where(['agent_id'=>$r['agent_id']])->update(['agent_p_parentid'=>$ppid]);
    //     }
    // }


    protected function updateChannel($data)
    {
        $info = AgentInfoModel::model()->getAgentById($data['agent_id']);
        if (!$info) {
            return false;
        }
        $data['id'] = $info['agent_user_id'];
        $re1 = UsersModel::model()->updateUserInfo($data);
        $re2 = AgentInfoModel::model()->updateAgentInfo($data);
        if ($re1 !== FALSE && $re2 !== FALSE) {
            return true;
        } else {
            return false;
        }
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

        // Db::startTrans();
        // 新增后台账户
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
            // Db::commit();
            return $re2;
        } else {
            // Db::rollback();
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
    protected function checkSuperAgentExists($condition)
    {
        $condition->channel = $condition->keyword;
        $size = $condition->size;
        unset($condition->size);
        $superInfo = AgentInfoModel::model()->getSuperAgentInfoByKeyword($condition);
        if (count($superInfo) > $this->max_search_num) {
            $this->returnCode(10001, $this->max_search_nitice);
            return false;
        }
        $ids = array();
        if (count($superInfo) > 0) {
            foreach ($superInfo as $item) {
                array_push($ids, $item['agent_id']);
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