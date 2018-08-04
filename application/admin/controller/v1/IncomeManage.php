<?php
/**
 * +----------------------------------------------------------
 * date: 2018-01-31 15:28:35
 * +----------------------------------------------------------
 * author: Raoxiaoya
 * +----------------------------------------------------------
 * describe: 代理收益
 * +----------------------------------------------------------
 */

namespace app\admin\controller\v1;

use app\admin\controller\Controller;
use app\admin\model\AgentInfoModel;
use app\admin\model\AgentSuperStatisticsModel;
use app\admin\model\AgentSuperStatisticsExtModel;
use app\admin\model\AgentsPromotersStatisticsModel;
use app\admin\model\AgentsStatisticsDayModel;
use app\admin\model\AgentsStatisticsPlayerModel;
use app\admin\model\PlayerStatisticalModel;
use app\admin\model\PlayerpromoteawardlogModel;
use app\admin\model\AgentsStatisticsHourModel;
use app\admin\model\AgentSuperIncomeConfigModel;
use app\admin\model\PromotersInfoModel;
use app\admin\model\MoneyRateInfoModel;
use app\admin\model\PlayerModel;
use app\admin\model\UsersModel;
use app\admin\model\ConfigModel;
use app\admin\model\StatisticsTotalModel;
use app\common\components\Helper;
use think\Db;

/**
 * @controllerName 代理收益
 */
class IncomeManage extends Controller
{
    protected $error_code = 0;
    protected $error_msg = '';
    protected $size = 10;
    protected $max_search_num = 50;
    protected $max_search_nitice = '请再精确一下关键词';

    // 统计时间段
    const TYPE_HOUR = 1;// 小时
    const TYPE_DAY = 2;// 天
    const TYPE_ALL = 3;// 所有的
    const TYPE_DAY_LEN = 14;// 按天查看时间段长度，默认14天（包括今天）

    const  ROLE_TYPE_ALL = 0; //公司
    const  ROLE_TYPE_CHANNEL = 1; //渠道
    const  ROLE_TYPE_PROMOTE = 2; //推广员
    // 统计类型
    const MODE_ALL = 0; //所有的
    const MODE_CHARGE_SUM = 1; //1-充值金额（分）
    const MODE_NEW_REGISTER = 2; //2-注册用户数（个）
    const MODE_COIN_COST = 3; //3-金币消耗数（个）
    const MODE_LOGIN_IN = 4; //4-活跃玩家数（个）
    const MODE_SEND_COIN = 5; //5-赠送金币数（个）
    const MODE_GAME_PLAYER = 6; //6-游戏玩家数（个）
    const MODE_PRODUCE_SUM = 7; //7-产出金币数（充值+赠送）
    const MODE_LAST_SUM = 8; //8-玩家剩余金币数（按天统计）
    const MODE_PROMOTE_SUM = 9; //9-所有推广员奖励（天，分）
    const MODE_THESTAR_SUM = 10; //10-所有推广员（不含星级）旗下玩家消耗金币数总计
    const MODE_EARN_SUM = 100; //100-金币消耗产生的总收益


    /**
     * 初始化操作
     * @access protected
     */
    protected function _initialize()
    {
        parent::_initialize();
        $info = $this->isLogin($this->token);
        $this->login_channel_id = 0;
        if (isset($info['agentInfo']) && !empty($info['agentInfo'])) {
            $this->login_channel_id = $info['agentInfo']['agent_id'];
        }
    }

    protected function initRequestParam()
    {
        $time = time();
        $timestamp_day = strtotime(date('Y-m-d', $time));
        $timestamp_month = strtotime(date('Y-m', $time));
        $input = array(
            'start'    => $this->request->get('start'),
            'end'      => $this->request->get('end'),
            'keyword'  => (string)$this->request->get('keyword'),// 玩家ID/昵称
            'channel'  => (int)$this->request->get('channel'),
            'page'     => (int)$this->request->get('page'),
            'size'     => (int)$this->request->get('size'),
            'turnpage' => (int)$this->request->get('turnpage'),
            'time_type'=> (int)$this->request->get('time_type')
            // 0-整月，1-第一周，2-第二周，3-上半月，4-第三周，5-第四周，6-下半月
        );
        /*
    	$input = array(
			'start'     =>'2018-6-2',//2017-2-11
			'end'       =>'2018-6-5',//2018-2-12
			'keyword'   =>'',// 玩家ID  1078114
			'page'      =>1,
			'channel'	=>'',
			'size'		=>'10',
            'turnpage'  =>0,
            'time_type' =>0
    	);
        */
        // 时间
        $filter = array();

        $start = strtotime($input['start']);
        $end = strtotime($input['end']);
        if ($start && $end) {
            if ($start > $end) {
                $this->returnCode(10001, '请选择合适的时间区间');
                return false;
            }
            if (request()->action() == 'specialAgentIncome') {
                $start = strtotime(date('Y-m', $start));
                $end = strtotime(date('Y-m', $end));
                if($end != $start && $input['time_type'] > 0){
                    $this->returnCode(10001, '请选择整月');
                    return false;
                }
                $end_t = strtotime('+1 month', $end);
                if ($start > $timestamp_month) {
                    $this->returnCode(10001, '请选择合适的时间区间');
                    return false;
                }
                $filter['start'] = $start;
                if ($end > $end_t) {
                    $filter['end'] = $end_t;
                } elseif ($end < $timestamp_month) {
                    $filter['end'] = strtotime('+1 month', $end);
                } else{
                    $filter['end'] = strtotime('+1 month', $end);
                }
            } elseif (request()->action() == 'myChannelIncome') {
                $start = strtotime(date('Y-m', $start));
                $end = strtotime(date('Y-m', $end));
                if($end != $start && $input['time_type'] > 0){
                    $this->returnCode(10001, '请选择整月');
                    return false;
                }
                $end_t = strtotime('+1 month', $end);
                if ($start > $timestamp_month) {
                    $this->returnCode(10001, '请选择合适的时间区间');
                    return false;
                }
                $filter['start'] = $start;
                if ($end > $end_t) {
                    $filter['end'] = $end_t;
                } elseif ($end < $timestamp_month) {
                    $filter['end'] = strtotime('+1 month', $end);
                } else{
                    $filter['end'] = strtotime('+1 month', $end);
                }
            } elseif (request()->action() == 'starAgentIncome') {
                $start = strtotime(date('Y-m-d', $start));
                $end = strtotime(date('Y-m-d', $end));
                if ($start > $timestamp_day) {
                    $this->returnCode(10001, '请选择合适的时间区间，只能查看今天之前的记录');
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
            } elseif (request()->action() == 'starAgentIncomeDetail') {
                $start = strtotime(date('Y-m-d', $start));
                $end = strtotime(date('Y-m-d', $end));
                if ($start > $timestamp_day) {
                    $this->returnCode(10001, '请选择合适的时间区间，只能查看今天之前的记录');
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
            } elseif (request()->action() == 'normalAgentIncome') {
                // 包含今日
                $start = strtotime(date('Y-m-d', $start));
                $end = strtotime(date('Y-m-d', $end));
                if ($start >= $timestamp_day) {
                    $this->returnCode(10001, '请选择合适的时间区间，只能查看今天之前的记录');
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
        } else {
            if (!$start && !$end) {
                if (request()->action() == 'specialAgentIncome') {
                    $filter['start'] = $timestamp_month;
                    $filter['end'] = strtotime('+1 month', $filter['start']);

                } elseif (request()->action() == 'specialAgentIncomeMonth') {
                    $filter['start'] = $timestamp_month;
                    $filter['end'] = strtotime('+1 month', $filter['start']);
                } elseif (request()->action() == 'myChannelIncome') {
                    $filter['start'] = $timestamp_month;
                    $filter['end'] = strtotime('+1 month', $filter['start']);

                } elseif (request()->action() == 'starAgentIncome') {
                    $filter['end'] = $timestamp_day + 86400;
                    $filter['start'] = strtotime('-1 day', $filter['end']);
                } elseif (request()->action() == 'normalAgentIncome') {
                    $filter['start'] = $timestamp_day;
                    $filter['end'] = strtotime('+1 day', $filter['start']);
                }
            } else {
                $this->returnCode(10001, '请选择合适的时间区间');
                return false;
            }
        }

        if ($input['keyword'] !== '') {
            $filter['keyword'] = $input['keyword'];
        }

        if ($input['channel']) {
            $filter['channel'] = $input['channel'];
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

        $filter['time_type'] = $input['time_type'];

        if ($input['turnpage']) {
            $filter['turnpage'] = 1;
        } else {
            $filter['turnpage'] = 0;
        }

        if ($this->error_code) {
            return false;
        } else {
            $filter = (object)$filter;
            // 搜所筛选
            if (isset($filter->keyword) && $filter->keyword) {
                if (request()->action() == 'starAgentIncome') {
                    $ret_arr = $this->checkStarAgentExists($filter);
                    $msg = '没有符合条件的星级推广员信息';
                } elseif (request()->action() == 'specialAgentIncome') {
                    $ret_arr = $this->checkSuperAgentExists($filter);
                    $msg = '没有符合条件的渠道信息';
                } elseif (request()->action() == 'specialAgentIncomeMonth') {
                    $ret_arr = $this->checkSuperAgentExists($filter);
                    $msg = '没有符合条件的渠道信息';
                }

                if (!is_bool($ret_arr) && empty($ret_arr)) {
                    $this->returnCode(10002, $msg);
                    return false;
                } else {
                    if (!$ret_arr) {
                        return false;
                    }
                    $filter->keyword = $ret_arr;
                }
            }
            
            return $filter;
        }
    }

    /**
     * @actionName 特代（渠道）收益
     * @return [type] [description]
     */
    public function specialAgentIncome()
    {
        $condition = $this->initRequestParam();
        if ($condition === false) {
            return $this->sendError($this->error_code, $this->error_msg);
        }
        
        $filterDate = $this->addTime($condition);
        
        $condition = $this->initTime($condition);

        if (!$condition->turnpage) {
            // 收益统计
            $superAgentIncome = $this->getSuperAgentIncome($condition);
        } else {
            $filterDate = null;
            $superAgentIncome = null;
        }

        // 渠道月收益列表
        $list = AgentSuperStatisticsModel::model()->getSuperAgentStatisticsList($condition);

        foreach ($list->items() as $item) {
            // 渠道账号-后台登陆账号
            $item->super_account = $this->getSuperAccount($item->statistics_agent_id);
            // 渠道名称
            $superInfo = $this->getSuperInfo($item->statistics_agent_id);
            $item->super_name = $superInfo['agent_name'];
            // 渠道分成占比区间
            $item->super_rate = AgentSuperStatisticsModel::model()->getSuperAgentIncomeShareRate($condition, $item->statistics_agent_id);
            // 渠道名下星级推广员分成收益
            $condition->channel = $item->statistics_agent_id;
            $income_star = AgentsPromotersStatisticsModel::model()->getStarAgentIncome($condition);
            unset($condition->channel);
            // 直接推广玩家服务费收益(元)
            $item->direct_player_income = Helper::cutDataByLen($item->direct_income);
            // (星级)推广玩家服务费收益(元)
            $item->non_direct_player_income = Helper::cutDataByLen($item->other_income);
            // 渠道应得直接推广玩家服务费收益(元)
            $item->direct_player_income_super = Helper::cutDataByLen($item->super_direct_income);
            // 渠道应得(星级)推广玩家服务费收益(元)
            $item->non_direct_player_income_super = Helper::cutDataByLen($item->super_other_income);
            // 渠道应得直接推广玩家服务费收益(元) --- 百分比 70%
            $item->direct_player_income_rate = ($item->direct_rate_high == $item->direct_rate_low) ? ($item->direct_rate_low / 100 . '%') : ($item->direct_rate_low / 100 . '%-' . $item->direct_rate_high / 100 . '%');
            // 渠道应得(星级)推广玩家服务费收益(元) --- 百分比 10%-15% 可能是区间
            $item->non_direct_player_income_rate = ($item->other_rate_high == $item->other_rate_low) ? ($item->other_rate_low / 100 . '%') : ($item->other_rate_low / 100 . '%-' . $item->other_rate_high / 100 . '%');

            // 渠道月度奖金
            $item->super_ext_income = 0;
            $item->super_ext_rate   = '0%';
            if($condition->time_type == 0){
                $condition->super_id = $item->statistics_agent_id;
                $ext = $this->getSuperIncomeExt($condition);
                if($ext){
                    $item->super_ext_income = $ext['statistics_money_ext'];
                    $item->super_ext_rate   = ($ext['share_high'] == $ext['share_low']) ? ($ext['share_high'] / 100 . '%') : ($ext['share_low'] / 100 . '%-' . $ext['share_high'] / 100 . '%');
                }
            }

            $item->star_income = Helper::cutDataByLen($income_star['star_income'] / 100);
            // 渠道收益
            $item->super_income = $item->super_income + $item->super_ext_income;
            $item->super_income = Helper::cutDataByLen($item->super_income / 100);
            $item->super_ext_income = Helper::cutDataByLen($item->super_ext_income / 100);
            // 总消耗收益
            $item->total_income = Helper::cutDataByLen($item->other_income + $item->direct_income);
            // 公司最终应得收益
            $item->final_income = Helper::cutDataByLen($item->total_income - $item->super_income - $item->star_income);
            // 公司综合分成占比
            $item->company_rate = (string)round($item->final_income / $item->total_income, 4) * 100 . '%';

            // $item->top_income_super = $item->non_direct_player_income_super + $item->direct_player_income_super;

            unset($item->direct_rate_high);
            unset($item->direct_rate_low);
            unset($item->other_rate_high);
            unset($item->other_rate_low);
            unset($item->statistics_money_rate_value);
            unset($item->total_income);
            unset($item->direct_income);
            unset($item->other_income);
            unset($item->super_direct_income);
            unset($item->super_other_income);
            unset($item->company_rate);
            unset($item->super_rate);
        }

        return $this->sendSuccess(['list' => $list, 'income' => $superAgentIncome, 'date' => $filterDate]);
    }

    protected function initTime($condition){
        if(!isset($condition->time_type)){
            return $condition;
        }
        // 0-整月，1-第一周，2-第二周，3-上半月，4-第三周，5-第四周，6-下半月
        if($condition->time_type == 1){
            $condition->end = strtotime('+7 day', $condition->start);
        }elseif($condition->time_type == 2){
            $condition->start = strtotime('+7 day', $condition->start);
            $condition->end = strtotime('+7 day', $condition->start);
        }elseif($condition->time_type == 3){
            $condition->end = strtotime('+14 day', $condition->start);
        }elseif($condition->time_type == 4){
            $condition->start = strtotime('+14 day', $condition->start);
            $condition->end = strtotime('+7 day', $condition->start);
        }elseif($condition->time_type == 5){
            // 第四周到月底
            $condition->start = strtotime('+21 day', $condition->start);
        }elseif($condition->time_type == 6){
            $condition->start = strtotime('+15 day', $condition->start);
        }

        return $condition;
    }

    protected function getSuperIncomeExt($condition){
        if(isset($condition->super_id)){
            return AgentSuperStatisticsExtModel::model()->getSuperAgentExtOne($condition);
        }
        if(isset($condition->agent_id) && !isset($condition->is_all)){
            return AgentSuperStatisticsExtModel::model()->getSuperAgentExtOneMonth($condition);
        }
        
        $data = AgentSuperStatisticsExtModel::model()->getSuperAgentExt($condition);
        return $data;
        
    }

    /**
     * @actionName 特代（渠道）收益当月
     * @return [type] [description]
     */
    public function specialAgentIncomeMonth()
    {
        $condition = $this->initRequestParam();
        if ($condition === false) {
            return $this->sendError($this->error_code, $this->error_msg);
        }
        $condition->start = strtotime(date('Y-m', time()));
        $condition->end = strtotime('+1 month', $condition->start);
        $condition->method = 1; // 当月

        if (!$condition->turnpage) {
            // 收益统计
            $superAgentIncome = $this->getSuperAgentIncomeMonth($condition);
            $filterDate = $this->addTime($condition);
        } else {
            $filterDate = null;
            $superAgentIncome = null;
        }

        $sysConfig = ConfigModel::model()->getConfigArray(['config_name' => 'channel_income_rate_from_direct', 'config_status' => 1]);
        if ($sysConfig) {
            $config_rate = $sysConfig[0]['config_config']['rate'];
        } else {
            $config_rate = 0;
        }

        // 渠道月收益列表
        $list = AgentSuperStatisticsModel::model()->getSuperAgentStatisticsList($condition);
        foreach ($list->items() as $item) {
            // 渠道账号-后台登陆账号
            $item->super_account = $this->getSuperAccount($item->statistics_agent_id);
            // 渠道名称
            $superInfo = $this->getSuperInfo($item->statistics_agent_id);
            $item->super_name = $superInfo['agent_name'];
            // 渠道分成占比区间
            $item->super_rate = '--'; // 废弃
            // 渠道名下星级推广员分成收益
            $condition->channel = $item->statistics_agent_id;
            $income_star = AgentsPromotersStatisticsModel::model()->getStarAgentIncome($condition);
            unset($condition->channel);
            $item->star_income = Helper::cutDataByLen($income_star['star_income'] / 100);
            // 渠道收益
            $item->super_income = Helper::cutDataByLen($item->super_income / 100);
            // 总消耗收益
            $item->total_income = Helper::cutDataByLen($item->other_income + $item->direct_income);

            // 公司综合分成占比
            $item->company_rate = '--';

            // 直接推广玩家服务费收益(元)
            $item->direct_player_income = Helper::cutDataByLen($item->direct_income);
            // (星级)推广玩家服务费收益(元)
            $item->non_direct_player_income = Helper::cutDataByLen($item->other_income);

            // 渠道应得直接推广玩家服务费收益(元) --- 百分比 70%
            $item->direct_player_income_rate = $config_rate / 100 . '%';
            // 渠道应得(星级)推广玩家服务费收益(元) --- 百分比 
            $config = AgentSuperIncomeConfigModel::model()->getSuperIncomeConfig($item->statistics_agent_id, 'desc');
            $config = AgentSuperIncomeConfigModel::model()->configObjToArray($config);
            $super_rate = AgentSuperIncomeConfigModel::model()->getSuperIncomeRate($config, $item->other_cost / $item->statistics_money_rate_value);
            $item->non_direct_player_income_rate = ($super_rate / 100) . '%';

            // 渠道应得(星级)推广玩家服务费收益(元)
            $item->super_other_income = $item->other_income * $super_rate / 10000;
            $item->non_direct_player_income_super = Helper::cutDataByLen($item->super_other_income);

            // 渠道应得直接推广玩家服务费收益(元)
            $item->super_direct_income = $item->direct_income * $config_rate / 10000;
            $item->direct_player_income_super = Helper::cutDataByLen($item->super_direct_income);

            // 公司最终应得收益
            $item->final_income = Helper::cutDataByLen($item->total_income - $item->super_direct_income - $item->super_other_income - $item->star_income);

            $item->top_income_super = $item->non_direct_player_income_super + $item->direct_player_income_super;
        }

        return $this->sendSuccess(['list' => $list, 'income' => $superAgentIncome, 'date' => $filterDate]);
    }

    /**
     * @actionName 特代（渠道）收益明细
     * @return [type] [description]
     */
    public function specialAgentIncomeDetail()
    {

    }

    /**
     * @actionName 星级推广员收益
     * @return [type] [description]
     */
    public function starAgentIncome()
    {
        $condition = $this->initRequestParam();
        if ($condition === false) {
            return $this->sendError($this->error_code, $this->error_msg);
        }
        // 如果是渠道后台
        if ($this->login_channel_id > 0) {
            $condition->channel = $this->login_channel_id;
        }
        // 列表
        $list = AgentsPromotersStatisticsModel::model()->getStarAgentIncomeList($condition);
        $channel_ids = array();
        $player_ids = array();
        foreach ($list->items() as $item) {
            array_push($channel_ids, $item->statistics_super_agents_id);
            array_push($player_ids, $item->statistics_agents_player_id);
        }
        $channel_ids = array_unique($channel_ids);
        $player_ids = array_unique($player_ids);
        $channelInfo = AgentInfoModel::model()->getByAgentId($channel_ids, ['agent_id,agent_name']);
        $channelInfo_init = array();
        $playerInfo_init = array();
        foreach ($channelInfo as $val) {
            $channelInfo_init[$val['agent_id']] = $val;
        }
        $playerInfo = PlayerModel::model()->getPlayerinfoById($player_ids, ['player_id,player_nickname']);
        foreach ($playerInfo as $val) {
            $playerInfo_init[$val['player_id']] = $val;
        }

        foreach ($list->items() as $item) {
            // 星级推广员昵称(ID)
            if (isset($playerInfo_init[$item->statistics_agents_player_id])) {
                $item->player_nickname = urldecode($playerInfo_init[$item->statistics_agents_player_id]['player_nickname']);
            } else {
                $item->player_nickname = '';
            }
            // $item->statistics_agents_player_id = (string)$item->statistics_agents_player_id;
            // 所属渠道
            if (isset($channelInfo_init[$item->statistics_super_agents_id])) {
                $item->channel_name = $channelInfo_init[$item->statistics_super_agents_id]['agent_name'];
            } else {
                $item->channel_name = '--';
            }
            // 星级推广总收益
            $item->total = Helper::cutDataByLen($item->my_income / 100);
            // 总金币消耗
            $item->all_data = Helper::fomatBigData($item->all_data / 100);
            // 总消耗收益
            $item->all_income = Helper::cutDataByLen($item->all_income / 100);
            // 星级分成比例
            $item->starShareRate = AgentsPromotersStatisticsModel::model()->getStarAgentIncomeShareRate($condition, $item->statistics_agents_id, 1);
            // 星级收益(直属)
            $starShare = AgentsPromotersStatisticsModel::model()->getStarAgentSubIncome($condition, $item->statistics_agents_id, 1);
            $item->my_income = Helper::cutDataByLen($starShare / 100);
            // 一级推广比例
            $item->starSubOneShareRate = AgentsPromotersStatisticsModel::model()->getStarAgentIncomeShareRate($condition, $item->statistics_agents_id, 2);
            // 一级推广收益
            $starSubOneShare = AgentsPromotersStatisticsModel::model()->getStarAgentSubIncome($condition, $item->statistics_agents_id, 2);
            $item->starSubOneShare = Helper::cutDataByLen($starSubOneShare / 100);
            // 二级推广比例
            $item->starSubTwoShareRate = AgentsPromotersStatisticsModel::model()->getStarAgentIncomeShareRate($condition, $item->statistics_agents_id, 3);
            // 二级推广收益
            $starSubTwoShare = AgentsPromotersStatisticsModel::model()->getStarAgentSubIncome($condition, $item->statistics_agents_id, 3);
            $item->starSubTwoShare = Helper::cutDataByLen($starSubTwoShare / 100);
        }
        if (!$condition->turnpage) {
            // 收益统计
            $starAgentIncome = $this->getStarAgentIncome($condition);
            // 时间
            $filterDate = $this->addTime($condition);
        } else {
            $starAgentIncome = null;
            $filterDate = null;
        }

        return $this->sendSuccess(['list' => $list, 'income' => $starAgentIncome, 'date' => $filterDate]);
    }

    /**
     * @actionName 星级推广员收益明细
     * @return [type] [description]
     */
    public function starAgentIncomeDetail()
    {
        $condition = $this->initRequestParam();
        if ($condition === false) {
            return $this->sendError($this->error_code, $this->error_msg);
        }
        // start, end, agent_id
        $agent_id = (int)$this->request->get('agent_id');
        if (!$agent_id) {
            return $this->sendError(10000, 'agent_id参数错误');
        } else {
            $condition->agent_id = $agent_id;
        }
        $list = AgentsStatisticsPlayerModel::model()->getPlayerCostDetail($condition);
        if (empty($list)) {
            return $this->sendSuccess();
        }
        // 玩家昵称
        $player_ids = [];
        $playerInfo_init = [];
        foreach ($list->items() as $item) {
            array_push($player_ids, $item->change_money_player_id);
        }
        $playerInfo = PlayerModel::model()->getPlayerinfoById($player_ids, ['player_id,player_nickname']);
        foreach ($playerInfo as $val) {
            $playerInfo_init[$val['player_id']] = $val;
        }

        // 所属星级推广员昵称
        $thisAgent = AgentInfoModel::model()->getInfo(['agent_id' => $agent_id]);
        if (!$thisAgent) {
            return $this->sendError(10000, '星级推广员不存在' . $agent_id);
        }
        $thisAgentInfos = PlayerModel::model()->getPlayerinfoById($thisAgent['agent_player_id'], ['player_id,player_nickname']);
        if (!$thisAgentInfos) {
            return $this->sendError(10000, '星级推广员信息缺失' . $agent_id);
        }
        $thisAgentInfos['player_nickname'] = urldecode($thisAgentInfos['player_nickname']);

        foreach ($list->items() as $item) {
            //玩家昵称
            if (isset($playerInfo_init[$item->change_money_player_id])) {
                $item->player_nickname = urldecode($playerInfo_init[$item->change_money_player_id]['player_nickname']);
            } else {
                $item->player_nickname = '--';
            }
            //游戏名称
            //服务费消耗
            $item->change_money_tax = $item->change_money_tax / 100;
            //收益类型
            //星级推广员分成占比
            //星级推广员分成收益（金币）
            if ($item->change_money_parent_agents_id == $agent_id && $item->change_money_my_tax > 0) {
                $item->type = '直接推广';
                $item->change_money_get_tax = $item->change_money_my_tax / 100;
                $item->change_money_get_rate = $item->change_money_share_rate . '%';
            } elseif ($item->change_money_one_agents_id == $agent_id && $item->change_money_one_tax > 0) {
                $item->type = '一层推广';
                $item->change_money_get_tax = $item->change_money_one_tax / 100;
                $item->change_money_get_rate = $item->change_money_one_rate . '%';
            } elseif ($item->change_money_two_agents_id == $agent_id && $item->change_money_two_tax > 0) {
                $item->type = '二层推广';
                $item->change_money_get_tax = $item->change_money_two_tax / 100;
                $item->change_money_get_rate = $item->change_money_two_rate . '%';
            }
            // 所属星级推广员昵称
            $agentInfo = AgentInfoModel::model()->getInfo(['agent_id' => $item->change_money_parent_agents_id]);
            if (!$agentInfo) {
                return $this->sendError(10000, '星级推广员不存在' . $item->change_money_parent_agents_id);
            }
            $agentInfos = PlayerModel::model()->getPlayerinfoById($agentInfo['agent_player_id'], ['player_id,player_nickname']);
            if (!$agentInfos) {
                $item->agent_player_nickname = '--';
                $item->agent_player_id = '--';
            } else {
                $item->agent_player_nickname = urldecode($agentInfos['player_nickname']);
                $item->agent_player_id = $agentInfos['player_id'];
            }

            unset($item->change_money_my_tax);
            unset($item->change_money_share_rate);
            unset($item->change_money_one_tax);
            unset($item->change_money_one_rate);
            unset($item->change_money_two_tax);
            unset($item->change_money_two_rate);
        }

        return $this->sendSuccess(['list' => $list, 'agent' => $thisAgentInfos]);
    }


    /**
     * @actionName 代理收益/推广员列表
     */
    public function normalAgentIncome()
    {
        $this->superAgentId = '';
        $loginData = $this->isLogin($this->request->get('token'));
        if (isset($loginData['agentInfo'])) {
            $this->superAgentId = $loginData['agentInfo']['agent_id'];
        }
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('pageSize', 10);
        $keyword = $this->request->get('keyword');
        if ($keyword) {
            $condition['keyword'] = $keyword;
        }


        if ($this->request->get('start_time') || $this->request->get('end_time')) {
            $start_time = strtotime($this->request->get('start_time'));
            $end_time = strtotime($this->request->get('end_time')) + 24 * 3600;
        } else {
            $start_time = strtotime(date('Y-m-d'));
            $end_time = strtotime(date('Y-m-d')) + 24 * 3600;
        }
        //金币比例
        $moeyconfig = MoneyRateInfoModel::model()->find();

        //总代理
        // if (!$this->superAgentId) {
        //总消耗
        $consumption['statistics_role_type'] = self::ROLE_TYPE_PROMOTE;
        $consumption['statistics_mode'] = self::MODE_THESTAR_SUM;
        $consumption['statistics_type'] = self::TYPE_DAY;
        $consumption['statistics_role_value'] = self::MODE_ALL;
        $consumption['statistics_timestamp'][0] = array('EGT', $start_time);
        $consumption['statistics_timestamp'][1] = array('LT', $end_time);
        $total_consumption_gold = StatisticsTotalModel::model()->getPromotersSumInfo($consumption);

        //总收益
        $total_consumption_revenue = $total_consumption_gold / $moeyconfig['money_rate_value'];

        //推广领取奖励
        $always['statistics_role_type'] = self::ROLE_TYPE_PROMOTE;
        $always['statistics_mode'] = self::MODE_PROMOTE_SUM;
        $always['statistics_type'] = self::TYPE_DAY;
        $always['statistics_timestamp'][0] = array('EGT', $start_time);
        $always['statistics_timestamp'][1] = array('LT', $end_time);
        $always_get = StatisticsTotalModel::model()->getPromotersSumInfo($always);

        $data = array(
            'consumption_gold' => $total_consumption_gold ? $total_consumption_gold : 0,
            'total_consumption_revenue' => $total_consumption_revenue ? $total_consumption_revenue : 0,
            'always_get' => $always_get / 100 ? $always_get / 100 : 0,
        );


        //别表
        $condition['agent_login_status'] = 0;
        // $condition['agent_promote_count']  0;
        $condition['agent_promote_count'] = array('GT', 0);
        $parentid_info = AgentInfoModel::model()->getTpromoteEarnings($condition, $page, $pageSize);
        //分页
        $count = AgentInfoModel::model()->getTpromoteEarningsCount($condition);
        $number = [];
        $thevalue = [];
        foreach ($parentid_info as $value) {

            $playerid = $value['agent_player_id'];
            $contrastplayerid = $value['agent_player_id'];
            $agent_top_agentid = $value['agent_top_agentid'];
            $thevalue['playereid'] = $value['agent_player_id'];

            //推广员昵名
            $thevalue['player_nickname'] = urldecode($value['player_nickname']) . '(' . $value['agent_player_id'] . ')';

            //所属星级推广员
            $promoters = PromotersInfoModel::model()->getParentIdByPlayerId($playerid);
            // $promoters_nickname[$playerid] = urldecode($promoters['player_nickname']);
            // $thevalue['promoters_nickname'] = '--';
            // if (isset($promoters_nickname[$contrastplayerid])) {
            //     $thevalue['promoters_nickname'] = $promoters_nickname[$contrastplayerid]?$promoters_nickname[$contrastplayerid]:"--";
            // }
            //判断用户是否是星级推广员
            $agent_info = AgentInfoModel::model()->getInfo(array('agent_player_id' => $promoters['promoters_parent_id']));
            $p_nickname = urldecode($promoters['player_nickname']);
            if ($agent_info['agent_login_status'] == 1) {
                $thevalue['promoters_nickname'] = $p_nickname;
            } else {
                $thevalue['promoters_nickname'] = '--';
            }
            $thevalue['promoters_parent_id'] = $promoters['promoters_parent_id'];
            //所属渠道
            $channelid['agent_id'] = $agent_top_agentid;
            $thevalue_channel = AgentInfoModel::model()->getAgentId($channelid);
            $channel[$playerid] = $thevalue_channel['agent_name'];
            $thevalue['channel'] = '';
            if (isset($channel[$contrastplayerid])) {
                $thevalue['channel'] = $channel[$contrastplayerid];
            }

            //统计推广人数
            $getCount['promoters_parent_id'] = $playerid;
            $getCount['promoters_time'][0] = array('EGT', $start_time);
            $getCount['promoters_time'][1] = array('LT', $end_time);
            $number[$playerid] = PromotersInfoModel::model()->getCount($getCount);
            if (isset($number[$contrastplayerid])) {
                $thevalue['number'] = $number[$contrastplayerid];
            }
            // $thevalue['number'] = $value['agent_promote_count'];


            $parent_agents_id = AgentInfoModel::model()->getAgentId(array('agent_player_id' => $playerid));
            $p_agent_id = $parent_agents_id['agent_id'];
            //消耗金币
            $earnings['statistics_parent_agents_id'] = $p_agent_id;
            $earnings['statistics_time'][0] = array('EGT', $start_time);
            $earnings['statistics_time'][1] = array('LT', $end_time);
            $player_earnings = AgentsStatisticsHourModel::model()->getSum($earnings);
            $consumption[$playerid] = $player_earnings;
            $earnings_service[$playerid] = $player_earnings / $moeyconfig['money_rate_value'];
            $thevalue['consumption'] = 0;
            if (isset($consumption[$contrastplayerid])) {
                $thevalue['consumption'] = $consumption[$contrastplayerid];
            }
            //推广玩家累计服务费金币收益
            $thevalue['serviceearnings'] = 0;
            if (isset($earnings_service[$contrastplayerid])) {
                $thevalue['serviceearnings'] = $earnings_service[$contrastplayerid];
            }

            //推广领取奖励
            // $always['statistics_role_type'] = self::ROLE_TYPE_PROMOTE;
            // $always['statistics_role_value'] = $playerid;
            // $always['statistics_mode'] = self::MODE_PROMOTE_SUM;
            // $always['statistics_type'] = self::TYPE_DAY;
            // $always['statistics_timestamp'][0] = array('EGT', $start_time);
            // $always['statistics_timestamp'][1] = array('LT', $end_time);
            // $always_get = StatisticsTotalModel::model()->getPromotersSumInfo($always);
            // $always_info[$playerid] = $always_get['statistics_sum'];
            // $thevalue['reward'] = 0;
            // if (isset($always_info[$contrastplayerid])) {
            //     $thevalue['reward'] = $always_info[$contrastplayerid];
            // }
            //  推广一次性奖励
            // $always_get = PlayerStatisticalModel::model()->getOne(array('statistical_player_id' => $playerid));
            // $thevalue['reward'] = $always_get['statistical_award_money'] / 100 ? $always_get['statistical_award_money'] / 100 : 0;

            $award_log['log_time'][0] = array('EGT', $start_time);
            $award_log['log_time'][1] = array('LT', $end_time);
            $award_log['log_promoter_id'] = $playerid;
            $award_data = PlayerpromoteawardlogModel::model()->getSumAward($award_log);
            $thevalue['reward'] = $award_data / 100 ? $award_data / 100 : 0;


            $datalist[] = $thevalue;
        }


        $data = array(
            'total' => $count,
            'per_page' => $pageSize,
            'page' => $page,
            'last_page' => ceil($count / $pageSize),
            'start_time' => date('Y-m-d H:i:s', $start_time),
            'end_time' => date('Y-m-d H:i:s', $end_time - 24 * 3600),
            'data' => $data,
            'list' => $datalist,
        );
        return $this->sendSuccess($data);

    }

    /**
     * @actionName 代理收益/推广员列表明细
     */
    public function normalAgentIncomeDetail()
    {

    }

    /**
     * @actionName 渠道后台--我的收益
     */
    public function myChannelIncome()
    {
        $condition = $this->initRequestParam();
        if ($condition === false) {
            return $this->sendError($this->error_code, $this->error_msg);
        }

        $condition->agent_id = $this->login_channel_id;
        
        $filterDate = $this->addTime($condition);
        
        $condition = $this->initTime($condition);

        if (!$condition->turnpage) {
            // 收益统计
            $superAgentIncome = $this->getSuperAgentIncomeById($condition);
        } else {
            $filterDate = null;
            $superAgentIncome = null;
        }

        // 渠道月收益列表
        $list = AgentSuperStatisticsModel::model()->getSuperAgentIncomeById($condition);
        foreach ($list->items() as $item) {
            // 当月消耗收益总计(元)
            $item->total_income = Helper::cutDataByLen($item->statistics_money_data / $item->statistics_money_rate_value);
            // 直接分成占比
            $item->direct_rate = $item->statistics_super_share_direct / 100 . '%';
            // 间接分成占比
            $item->other_rate = ($item->statistics_super_share_high == $item->statistics_super_share_low) ? ($item->statistics_super_share_high / 100 . '%') : ($item->statistics_super_share_low / 100 . '%-' . $item->statistics_super_share_high / 100 . '%');
            // 直接推广玩家服务费收益(元)
            $direct_income = $item->statistics_money_data_direct / $item->statistics_money_rate_value;
            $item->direct_income = Helper::cutDataByLen($direct_income);
            // (星级)推广玩家服务费收益(元)
            $other_income = $item->statistics_money_data / $item->statistics_money_rate_value;
            $item->other_income = Helper::cutDataByLen($other_income);
            // 我的应得直接推广玩家服务费收益(元)
            $item->direct_income_super = Helper::cutDataByLen($item->direct_income_super);
            // 我的应得(星级)推广玩家服务费收益(元)
            $item->other_income_super = Helper::cutDataByLen($item->other_income_super);
            // 月度奖金
            $item->super_ext_income = 0;
            $item->super_ext_rate   = '0%';
            if($condition->time_type == 0){
                $where = [
                    'statistics_month' =>$item->statistics_month,
                    'agent_id'         =>$condition->agent_id,
                ];
                $where = (object)$where;
                $ext = $this->getSuperIncomeExt($where);
                if($ext){
                    $item->super_ext_income = $ext['statistics_money_ext'];
                    $item->super_ext_rate   = ($ext['statistics_super_share_ext']/100).'%';
                }
            }
            // 我的应得收益(元)+月度奖金
            $item->statistics_money = $item->statistics_money + $item->super_ext_income;
            $item->statistics_money = Helper::cutDataByLen($item->statistics_money/100);
            $item->super_ext_income = Helper::cutDataByLen($item->super_ext_income/100);

            $item->date = date('Y-m', $item->statistics_month);

            unset($item->statistics_add_time);
            unset($item->statistics_up_time);
            unset($item->statistics_money_status);
            unset($item->statistics_money_rate_unit_type);
            unset($item->statistics_money_rate_unit);
            unset($item->statistics_money_rate_value);
            unset($item->statistics_super_config);
            unset($item->statistics_time);
            unset($item->statistics_money_type);
            unset($item->statistics_agent_id);
            unset($item->statistics_super_share_high);
            unset($item->statistics_super_share_low);
            unset($item->total_income);
            unset($item->statistics_money_data);
            unset($item->statistics_money_data_direct);
            unset($item->statistics_super_share_direct);
        }

        return $this->sendSuccess(['list' => $list, 'income' => $superAgentIncome, 'date' => $filterDate]);

    }

    /**
     * @actionName 渠道后台--我的收益当月
     */
    public function myChannelIncomeMonth()
    {
        $condition = array();
        $condition = (object)$condition;
        $condition->start = strtotime(date('Y-m', time()));
        $condition->end = strtotime('+1 month', $condition->start);
        $condition->page = 1;
        $condition->size = $this->size;

        if (!$this->login_channel_id) {
            return $this->sendError(10000, '登陆信息错误');
        } else {
            $condition->agent_id = $this->login_channel_id;
        }

        $sysConfig = ConfigModel::model()->getConfigArray(['config_name' => 'channel_income_rate_from_direct', 'config_status' => 1]);
        if ($sysConfig) {
            $config_rate = $sysConfig[0]['config_config']['rate'];
        } else {
            $config_rate = 0;
        }

        // 渠道月收益列表
        $list = AgentSuperStatisticsModel::model()->getSuperAgentIncomeById($condition);
        foreach ($list->items() as $item) {
            // 当月消耗收益总计(元)
            $item->total_income = Helper::cutDataByLen($item->statistics_money_data / $item->statistics_money_rate_value);
            // 直接分成占比
            $item->direct_rate = $config_rate / 100 . '%';
            // 间接分成占比
            $config = AgentSuperIncomeConfigModel::model()->getSuperIncomeConfig($item->statistics_agent_id, 'desc');
            $config = AgentSuperIncomeConfigModel::model()->configObjToArray($config);
            $super_rate = AgentSuperIncomeConfigModel::model()->getSuperIncomeRate($config, $item->statistics_money_data / $item->statistics_money_rate_value);
            $item->other_rate = $super_rate / 100 . '%';
            // 直接推广玩家服务费收益(元)
            $direct_income = $item->statistics_money_data_direct / $item->statistics_money_rate_value;
            $item->direct_income = Helper::cutDataByLen($direct_income);
            // (星级)推广玩家服务费收益(元)
            $other_income = $item->statistics_money_data / $item->statistics_money_rate_value;
            $item->other_income = Helper::cutDataByLen($other_income);
            // 我的应得直接推广玩家服务费收益(元)
            $item->direct_income_super = Helper::cutDataByLen($direct_income * $config_rate / 10000);
            // 我的应得(星级)推广玩家服务费收益(元)
            $item->other_income_super = Helper::cutDataByLen($other_income * $super_rate / 10000);
            // 我的应得收益(元)
            $item->statistics_money = $item->other_income_super + $item->direct_income_super;
            unset($item->statistics_add_time);
            unset($item->statistics_up_time);
            unset($item->statistics_money_status);
            unset($item->statistics_money_rate_unit_type);
            unset($item->statistics_money_rate_unit);
            unset($item->statistics_money_rate_value);
            unset($item->statistics_super_config);
            unset($item->statistics_time);
            unset($item->statistics_money_type);
            unset($item->statistics_agent_id);
        }

        $filterDate = $this->addTime($condition);
        $superAgentIncome = [
            'total_cost' => $list[0]['statistics_money_data_direct'] + $list[0]['statistics_money_data'],
            'total_income' => $list[0]['direct_income'] + $list[0]['other_income'],
            'super_income' => $list[0]['direct_income_super'] + $list[0]['other_income_super']
        ];

        return $this->sendSuccess(['list' => $list, 'income' => $superAgentIncome, 'date' => $filterDate]);

    }

    /**
     * @actionName 渠道后台--渠道收益明细
     */
    public function myChannelIncomeDetail()
    {
        $input = array(
            'statistics_id' => (int)$this->request->get('statistics_id'),
            'page' => (int)$this->request->get('page'),
            'size' => (int)$this->request->get('size'),
            'type' => (int)$this->request->get('type')  // 1-直接推广玩家, 2-(星级)推广玩家
        );
        $condition = [];
        if (!$input['statistics_id']) {
            return $this->sendError(10000, '参数错误');
        } else {
            $condition['statistics_id'] = $input['statistics_id'];
        }
        if ($input['page']) {
            $condition['page'] = $input['page'];
        } else {
            $condition['page'] = 1;
        }

        if ($input['size']) {
            $condition['size'] = $input['size'];
        } else {
            $condition['size'] = $this->size;
        }
        $condition = (object)$condition;
        $info = AgentSuperStatisticsModel::model()->getInfo(['statistics_id' => $condition->statistics_id]);
        if (!$info) {
            return $this->sendError(10000, '参数错误');
        }
        $condition->start = $info['statistics_time'];
        $condition->end = strtotime('+1 month', $condition->start);
        $condition->channel = $info['statistics_agent_id'];
        $list = AgentsPromotersStatisticsModel::model()->getStarAgentIncomeListExt($condition);
        foreach ($list->items() as $item) {
            // 星级推广员昵称
            $playerInfo = PlayerModel::model()->getInfo(['player_id' => $item->statistics_agents_player_id]);
            if ($playerInfo) {
                $item->statistics_agents_player_nickname = urldecode($playerInfo['player_nickname']);
            } else {
                $item->statistics_agents_player_nickname = '';
            }
            // 我的分成占比
            $item->channel_rate = ($info['statistics_super_share'] / 100) . '%';
            // 我的分成收益
            $item->channel_income = Helper::cutDataByLen($item->all_income * $info['statistics_super_share'] / (10000 * 100));
            // 总金币消耗(金币)
            $item->all_data = Helper::fomatBigData($item->all_data / 100);
            // 总消耗收益(元)
            $item->all_income = Helper::cutDataByLen($item->all_income / 100);
            // 星级推广员分成收益（元）
            $item->my_income = Helper::cutDataByLen($item->my_income / 100);

        }

        return $this->sendSuccess(['list' => $list, 'date' => $this->addTime($condition)]);
    }

    /**
     * @param null $condition
     * @return array
     */
    protected function addTime($condition = null)
    {
        $ret_time = array(
            'start_date' => '',
            'end_date' => ''
        );
        if (request()->action() == 'specialAgentIncome') {
            if (isset($condition->start)) {
                $ret_time['start_date'] = date('Y-m-d', $condition->start);
            }
            if (isset($condition->end)) {
                $ret_time['end_date'] = date('Y-m-d', strtotime('-1 day', $condition->end));
            }
        } elseif (request()->action() == 'specialAgentIncomeMonth') {
            if (isset($condition->start)) {
                $ret_time['start_date'] = date('Y-m-d', $condition->start);
            }
            if (isset($condition->end)) {
                $ret_time['end_date'] = date('Y-m-d', strtotime('-1 day', $condition->end));
            }
        } elseif (request()->action() == 'myChannelIncomeMonth') {
            if (isset($condition->start)) {
                $ret_time['start_date'] = date('Y-m-d', $condition->start);
            }
            if (isset($condition->end)) {
                $ret_time['end_date'] = date('Y-m-d', strtotime('-1 day', $condition->end));
            }
        } elseif (request()->action() == 'myChannelIncome') {
            if (isset($condition->start)) {
                $ret_time['start_date'] = date('Y-m-d', $condition->start);
            }
            if (isset($condition->end)) {
                $ret_time['end_date'] = date('Y-m-d', $condition->end - 86400);
            }
        } elseif (request()->action() == 'myChannelIncomeDetail') {
            if (isset($condition->start)) {
                $ret_time['start_date'] = date('Y-m-d', $condition->start);
            }
            if (isset($condition->end)) {
                $ret_time['end_date'] = date('Y-m-d', $condition->end - 86400);
            }
        } elseif (request()->action() == 'starAgentIncome') {
            if (isset($condition->start)) {
                $ret_time['start_date'] = date('Y-m-d', $condition->start);
            }
            if (isset($condition->end)) {
                $ret_time['end_date'] = date('Y-m-d', $condition->end - 86400);
            }
        } elseif (request()->action() == 'normalAgentIncome') {
            if (isset($condition->start)) {
                $ret_time['start_date'] = date('Y-m-d', $condition->start);
            }
            if (isset($condition->end)) {
                $ret_time['end_date'] = date('Y-m-d', $condition->end - 86400);
            }
        }

        return $ret_time;
    }

    /**
     * 星级推广员收益数据总计
     * @return [type] [description]
     */
    protected function getStarAgentIncome($condition)
    {
        $income_arr = AgentsPromotersStatisticsModel::model()->getStarAgentIncome($condition);
        $income_arr['total_cost'] = (string)(int)($income_arr['total_cost'] / 100);// 个
        $income_arr['total_income'] = Helper::cutDataByLen($income_arr['total_income'] / 100); // 元
        $income_arr['star_income'] = Helper::cutDataByLen($income_arr['star_income'] / 100); // 元
        // 此处公司收益包括渠道的
        $income_arr['company_income'] = Helper::cutDataByLen($income_arr['total_income'] - $income_arr['star_income']);
        if ($income_arr['total_income'] > 0) {
            $income_arr['company_rate'] = round($income_arr['company_income'] * 100 / $income_arr['total_income'], 2) . '%';
            $income_arr['star_rate'] = round($income_arr['star_income'] * 100 / $income_arr['total_income'], 2) . '%';
        } else {
            $income_arr['company_rate'] = 0;
            $income_arr['star_rate'] = 0;
        }
        if ($this->login_channel_id > 0) {
            return [
                'total_cost' => $income_arr['total_cost'],
                'total_income' => $income_arr['total_income']
            ];
        } else {
            return $income_arr;
        }

    }

    protected function getSuperAgentIncome($condition)
    {
        $income_arr = AgentSuperStatisticsModel::model()->getSuperAgentIncome($condition);
        $income_arr['total_cost'] = Helper::fomatBigData($income_arr['other_cost'] + $income_arr['direct_cost']);
        $income_arr['total_income'] = Helper::cutDataByLen($income_arr['other_income'] + $income_arr['direct_income']);
        $income_star = AgentsPromotersStatisticsModel::model()->getStarAgentIncome($condition);
        $income_arr['star_income'] = Helper::cutDataByLen($income_star['star_income'] / 100);
        
        $super_income_ext = 0;
        if($condition->time_type == 0){
            $ext = $this->getSuperIncomeExt($condition);
            if($ext){
                $super_income_ext = $ext['statistics_money_ext'];
            }
        }

        $income_arr['super_income'] += $super_income_ext;
        $income_arr['super_income']   = Helper::cutDataByLen($income_arr['super_income'] / 100);
        $income_arr['company_income'] = Helper::cutDataByLen($income_arr['total_income'] - $income_arr['super_income'] - $income_arr['star_income']);

        // $income_arr['super_income_ext'] = $super_income_ext;

        unset($income_arr['other_cost']);
        unset($income_arr['direct_cost']);
        unset($income_arr['direct_income']);
        unset($income_arr['other_income']);

        return $income_arr;
    }

    protected function getSuperAgentIncomeMonth($condition)
    {
        $income_arr = AgentSuperStatisticsModel::model()->getSuperAgentIncome($condition);
        $income_arr['total_cost'] = Helper::fomatBigData($income_arr['other_cost'] + $income_arr['direct_cost']);
        $income_arr['total_income'] = Helper::cutDataByLen($income_arr['other_income'] + $income_arr['direct_income']);

        // $income_arr['super_income'] = Helper::cutDataByLen($income_arr['super_income'] / 100);
        $income_arr['super_income'] = $this->getCurrentMonthSuperIncome($condition);

        $income_star = AgentsPromotersStatisticsModel::model()->getStarAgentIncome($condition);
        $income_arr['star_income'] = Helper::cutDataByLen($income_star['star_income'] / 100);

        $income_arr['company_income'] = Helper::cutDataByLen($income_arr['total_income'] - $income_arr['super_income'] - $income_arr['star_income']);
        return $income_arr;
    }

    protected function getCurrentMonthSuperIncome($condition)
    {
        $list = AgentSuperStatisticsModel::model()->getSuperAgentStatisticsListAll($condition);
        if (empty($list)) {
            return 0;
        }
        $total = 0;
        $sysConfig = ConfigModel::model()->getConfigArray(['config_name' => 'channel_income_rate_from_direct', 'config_status' => 1]);
        if ($sysConfig) {
            $config_rate = $sysConfig[0]['config_config']['rate'];
        } else {
            $config_rate = 0;
        }

        foreach ($list as $val) {
            $config = AgentSuperIncomeConfigModel::model()->getSuperIncomeConfig($val['statistics_agent_id'], 'desc');
            $config = AgentSuperIncomeConfigModel::model()->configObjToArray($config);
            $other_rate = AgentSuperIncomeConfigModel::model()->getSuperIncomeRate($config, $val['statistics_money_data'] / $val['statistics_money_rate_value']);

            $d_income = $config_rate * $val['statistics_money_data_direct'] / 10000 / $val['statistics_money_rate_value'];// 元
            $o_income = $other_rate * $val['statistics_money_data'] / 10000 / $val['statistics_money_rate_value'];

            $total += Helper::cutDataByLen($d_income) + Helper::cutDataByLen($o_income);
        }

        return $total;

    }

    protected function getSuperAgentIncomeById($condition)
    {
        $income_arr = AgentSuperStatisticsModel::model()->getSuperAgentIncome($condition);
        $income_arr['total_cost'] = Helper::fomatBigData($income_arr['other_cost'] + $income_arr['direct_cost']);
        $income_arr['total_income'] = $income_arr['other_income'] + $income_arr['direct_income'];

        $super_income_ext = 0;
        if($condition->time_type == 0){
            $condition->is_all = 1;
            $ext = $this->getSuperIncomeExt($condition);
            if($ext){
                $super_income_ext = $ext['statistics_money_ext'];
            }
        }

        $income_arr['super_income'] += $super_income_ext;
        $income_arr['total_income'] = Helper::cutDataByLen($income_arr['total_income']);
        $income_arr['super_income'] = Helper::cutDataByLen($income_arr['super_income'] / 100);

        unset($income_arr['other_cost']);
        unset($income_arr['direct_cost']);
        unset($income_arr['other_income']);
        unset($income_arr['direct_income']);

        return $income_arr;
    }

    /**
     * 检索星级推广员信息
     * @param  string $condition
     * @return [type]          [description]
     */
    protected function checkStarAgentExists($condition)
    {
        $size = $condition->size;
        unset($condition->size);
        $playerInfo = PlayerModel::model()->getPlayerinfoByLike($condition);
        if (count($playerInfo) > $this->max_search_num) {
            $this->returnCode(10001, $this->max_search_nitice);
            return false;
        }
        $ids = array();
        if (count($playerInfo) > 0) {
            foreach ($playerInfo as $item) {
                $starInfo = AgentInfoModel::model()->getByAgentPlayerId($item['player_id'], ['agent_id'], ['agent_login_status' => 1]);
                if ($starInfo) {
                    array_push($ids, $item['player_id']);
                }
            }
        }
        $condition->size = $size;
        return array_unique($ids);
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

    protected function cutDataByLen($data, $len = 2)
    {
        $pows = pow(10, $len);
        $temp = $data * $pows;
        $temp = (int)$temp;
        $data = $temp / $pows;
        $data = $data ? (string)$data : '0.00';
        return sprintf("%.2f", $data);
    }

    protected function getSuperAccount($agent_id)
    {
        $agentInfo = $this->getSuperInfo($agent_id);
        if (!$agentInfo) {
            return '';
        }
        $userInfo = UsersModel::model()->getLoginById($agentInfo['agent_user_id']);
        if (!$userInfo) {
            return '';
        }

        return $userInfo['user_login'];
    }

    protected function getSuperInfo($agent_id)
    {
        return AgentInfoModel::model()->getAgentById($agent_id);
    }

    protected function returnCode($code, $error)
    {
        $this->error_code = $code;
        $this->error_msg = $error;
    }
}