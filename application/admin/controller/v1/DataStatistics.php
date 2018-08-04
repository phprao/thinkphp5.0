<?php
/**
 *数据统计
 *
 */

namespace app\admin\controller\v1;

use app\admin\block\DataArrange;
use app\admin\controller\Controller;
use app\admin\model\AgentInfoModel;
use app\admin\model\GameInfoModel;
use app\admin\model\GameRoundDayModel;
use app\admin\model\StatisticsTotalModel;
use app\common\components\Helper;


class DataStatistics extends Controller
{


    private $superAgentId;
    private $role_id;
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


    public function _initialize()
    {
        $loginData = $this->isLogin($this->request->get('token'));
        if (isset($loginData['userInfo']['role_id'])) {
            $this->role_id = $loginData['userInfo']['role_id'];
        }

        $this->superAgentId = '';
        $loginData = $this->isLogin($this->request->get('token'));
        if (isset($loginData['agentInfo'])) {
            $this->superAgentId = $loginData['agentInfo']['agent_id'];
        }

    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @actionNane  今日对比数据总览
     */
    public function index()
    {
        $today = strtotime(date('Y-m-d', time())); //凌晨时间
        $endtime = $today + 24 * 3600;//24点

        $yesterday = strtotime(date('Y-m-d', strtotime('-1 day')));
        //昨天全天
        $throughoutyesterday = $yesterday + 24 * 60 * 60;
        /**
         * $this->superAgentId  空是超级用户
         *
         */
        if (!$this->superAgentId) {
            //今日充值
            $topup['statistics_role_type'] = self::ROLE_TYPE_ALL;
            //今日产出总额
            $coinsamount['statistics_role_type'] = self::ROLE_TYPE_ALL;
            //截至昨日剩余金币
            $remaininggold['statistics_role_type'] = self::ROLE_TYPE_ALL;
            //今日消耗总额(金币)
            $topconsumption['statistics_role_type'] = self::ROLE_TYPE_ALL;
            // 今日新增用户
            $newnumber['statistics_role_type'] = self::ROLE_TYPE_ALL;
            //活跃人数
            $getPtion['statistics_role_type'] = self::ROLE_TYPE_ALL;
            //今日游戏人数
            $gamenumber['statistics_role_type'] = self::ROLE_TYPE_ALL;

            //今日充值
            $topup['statistics_mode'] = self::MODE_CHARGE_SUM;
            $topup['statistics_type'] = self::TYPE_DAY;
            $topup['statistics_timestamp'] = $today;
            $parentidsum = StatisticsTotalModel::model()->getOne($topup);

            //今日游戏人数
            $gamenumber['statistics_mode'] = self::MODE_GAME_PLAYER;
            $gamenumber['statistics_type'] = self::TYPE_DAY;
            $gamenumber['statistics_timestamp'] = $today;
            $today_game_number = StatisticsTotalModel::model()->getOne($gamenumber);

        } else {
//            $Agentinfo = AgentInfoModel::model()->getAgentId(array('agent_user_id' => $this->superAgentId));
//            if (!$Agentinfo) {
//                return $this->sendError(2003, '请输入代理ID');
//            }
//            //今日充值
//            $topup['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
//            $topup['statistics_role_value'] = $this->superAgentId;
            $parentidsum['statistics_sum'] = '';
            //今日产出总额
            $coinsamount['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $coinsamount['statistics_role_value'] = $this->superAgentId;
            //截至昨日剩余金币
            $remaininggold['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $remaininggold['statistics_role_value'] = $this->superAgentId;
            //今日消耗总额(金币)
            $topconsumption['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $topconsumption['statistics_role_value'] = $this->superAgentId;
            // 今日新增用户
            $newnumber['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $newnumber['statistics_role_value'] = $this->superAgentId;
            //活跃人数
            $getPtion['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $getPtion['statistics_role_value'] = $this->superAgentId;
//            //今日游戏人数
//            $gamenumber['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
//            $gamenumber['statistics_role_value'] = $this->superAgentId;
            $today_game_number['statistics_sum'] = '';

        }


        //今日产出总额
        $coinsamount['statistics_mode'] = self::MODE_PRODUCE_SUM;
        $coinsamount['statistics_type'] = self::TYPE_DAY;
        $coinsamount['statistics_timestamp'] = $today;
        $today_output_amount = StatisticsTotalModel::model()->getOne($coinsamount);
        //截至昨日剩余金币
        $remaininggold['statistics_mode'] = self::MODE_LAST_SUM;
        $remaininggold['statistics_type'] = self::TYPE_ALL;;
        // $remaininggold['statistics_timestamp'] = $throughoutyesterday;
        $yesterday_remaining_gold = StatisticsTotalModel::model()->getOne($remaininggold);
        //今日消耗总额(金币)
        $topconsumption['statistics_mode'] = self::MODE_COIN_COST;
        $topconsumption['statistics_type'] = self::TYPE_DAY;
        $topconsumption['statistics_timestamp'] = $today;
        $today_top_consumption = StatisticsTotalModel::model()->getOne($topconsumption);
        //今日收益总额(元)
        $today_top_earnings = $today_top_consumption['statistics_sum'] / 10000;
        // 今日新增用户
        $newnumber['statistics_mode'] = self::MODE_NEW_REGISTER;
        $newnumber['statistics_type'] = self::TYPE_DAY;
        $newnumber['statistics_timestamp'] = $today;
        $today_new_number = StatisticsTotalModel::model()->getOne($newnumber);
        //活跃人数
        $getPtion['statistics_mode'] = self::MODE_LOGIN_IN;
        $getPtion['statistics_type'] = self::TYPE_DAY;
        $getPtion['statistics_timestamp'] = $today;
        $today_active_number = StatisticsTotalModel::model()->getOne($getPtion);


        $data = array(
            'today_top_up' => $parentidsum['statistics_sum'] / 100 ? $parentidsum['statistics_sum'] / 100 : 0,
            'today_output_amount' => Helper::fomatBigData($today_output_amount['statistics_sum']) ? Helper::fomatBigData($today_output_amount['statistics_sum']) : 0,
            'yesterday_remaining_gold' => Helper::fomatBigData($yesterday_remaining_gold['statistics_sum']) ? Helper::fomatBigData($yesterday_remaining_gold['statistics_sum']) : 0,
            'today_top_consumption' => Helper::fomatBigData($today_top_consumption['statistics_sum']) ? Helper::fomatBigData($today_top_consumption['statistics_sum']) : 0,
            'today_top_earnings' => $today_top_earnings ? $today_top_earnings : 0,
            'today_new_number' => $today_new_number['statistics_sum'] ? $today_new_number['statistics_sum'] : 0,
            'today_active_number' => $today_active_number['statistics_sum'] ? $today_active_number['statistics_sum'] : 0,
            'today_game_number' => $today_game_number['statistics_sum'] ? $today_game_number['statistics_sum'] : 0,
        );
        return $this->sendSuccess($data);
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @actionName 今昨同时段对比
     */
    public function dataContrast()
    {

        //今日时间
        $today = strtotime(date('Y-m-d', time()));
        // 当天的24
//        $end = $today + 14 * 60 * 60;
        //当前时间
        $end = time();
        //昨日
        $yesterday = strtotime(date('Y-m-d', strtotime('-1 day')));
        //14点
//        $endyesterday = $yesterday + 14 * 60 * 60;
        //获取昨天当前时间
        $endyesterday = time() - 24 * 3600;
        //全天
        $throughoutyesterday = $yesterday + 24 * 60 * 60;

        if (!$this->superAgentId) {
            //充值金额 今日（00点-14点）
            $topup['statistics_role_type'] = self::ROLE_TYPE_ALL;
            //充值金额  昨日（00点-14点）
            $twelvetota['statistics_role_type'] = self::ROLE_TYPE_ALL;
            //充值金额  昨日全天
            $yesterdaytotal['statistics_role_type'] = self::ROLE_TYPE_ALL;
            //赠送金币  今日（00点-14点）
            $todaygold['statistics_role_type'] = self::ROLE_TYPE_ALL;
            //赠送金币  昨日（00点-14点）
            $yesterdaygold['statistics_role_type'] = self::ROLE_TYPE_ALL;
            //赠送金币  昨日全天
            $yesterdaallgold['statistics_role_type'] = self::ROLE_TYPE_ALL;
            //金币消耗  今日（00点-14点）
            $todayconsumption['statistics_role_type'] = self::ROLE_TYPE_ALL;
            //金币消耗  昨日（00点-14点）
            $yesterdayconsumption['statistics_role_type'] = self::ROLE_TYPE_ALL;
            //金币消耗  昨日全天
            $yesterdayallconsumption['statistics_role_type'] = self::ROLE_TYPE_ALL;
            // 新增用户 今日（00点-14点）
            $todaynewnumber['statistics_role_type'] = self::ROLE_TYPE_ALL;
            // 新增用户 昨日（00点-14点）
            $yesterdaynewnumber['statistics_role_type'] = self::ROLE_TYPE_ALL;
            // 新增用户 昨日全天
            $yesterdaytotalnumber['statistics_role_type'] = self::ROLE_TYPE_ALL;


            //充值金额 今日（00点-14点）
            $topup['statistics_mode'] = self::MODE_CHARGE_SUM;
            $topup['statistics_type'] = self::TYPE_HOUR;
            $topup['statistics_timestamp'][0] = array('EGT', $today);
            $topup['statistics_timestamp'][1] = array('LT', $end);
            $top_up_total = StatisticsTotalModel::model()->getPromotersSumInfo($topup);
            //充值金额  昨日（00点-14点）
            $twelvetota['statistics_mode'] = self::MODE_CHARGE_SUM;
            $twelvetota['statistics_type'] = self::TYPE_HOUR;
            $twelvetota['statistics_timestamp'][0] = array('EGT', $yesterday);
            $twelvetota['statistics_timestamp'][1] = array('LT', $endyesterday);
            $yesterday_twelve_total = StatisticsTotalModel::model()->getPromotersSumInfo($twelvetota);

            //充值金额  昨日全天
            $yesterdaytotal['statistics_mode'] = self::MODE_CHARGE_SUM;
            $yesterdaytotal['statistics_type'] = self::TYPE_HOUR;
            $yesterdaytotal['statistics_timestamp'][0] = array('EGT', $yesterday);
            $yesterdaytotal['statistics_timestamp'][1] = array('LT', $throughoutyesterday);
            $yesterday_total = StatisticsTotalModel::model()->getPromotersSumInfo($yesterdaytotal);
            //赠送金币  今日（00点-14点）
            $todaygold['statistics_mode'] = self::MODE_SEND_COIN;
            $todaygold['statistics_type'] = self::TYPE_HOUR;
            $todaygold['statistics_timestamp'][0] = array('EGT', $today);
            $todaygold['statistics_timestamp'][1] = array('LT', $end);
            $today_gold = StatisticsTotalModel::model()->getPromotersSumInfo($todaygold);

            //赠送金币  昨日（00点-14点）
            $yesterdaygold['statistics_mode'] = self::MODE_SEND_COIN;
            $yesterdaygold['statistics_type'] = self::TYPE_HOUR;
            $yesterdaygold['statistics_timestamp'][0] = array('EGT', $yesterday);
            $yesterdaygold['statistics_timestamp'][1] = array('LT', $endyesterday);
            $yesterday_gold = StatisticsTotalModel::model()->getPromotersSumInfo($yesterdaygold);

            //赠送金币  昨日全天
            $yesterdaallgold['statistics_mode'] = self::MODE_SEND_COIN;
            $yesterdaallgold['statistics_type'] = self::TYPE_HOUR;
            $yesterdaallgold['statistics_timestamp'][0] = array('EGT', $yesterday);
            $yesterdaallgold['statistics_timestamp'][1] = array('LT', $throughoutyesterday);
            $yesterda_all_gold = StatisticsTotalModel::model()->getPromotersSumInfo($yesterdaallgold);

        } else {

//            $Agentinfo = AgentInfoModel::model()->getAgentId(array('agent_user_id' => $this->superAgentId));
//            if (!$Agentinfo) {
//                return $this->sendError(2003, '请输入代理ID');
//            }
//            //充值金额 今日（00点-14点）
//            $topup['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
//            $topup['statistics_role_value'] = $this->superAgentId;
//            //充值金额  昨日（00点-14点）
//            $twelvetota['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
//            $twelvetota['statistics_role_value'] = $this->superAgentId;
//            //充值金额  昨日全天
//            $yesterdaytotal['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
//            $yesterdaytotal['statistics_role_value'] = $this->superAgentId;
//
//            //赠送金币  今日（00点-14点）
//            $todaygold['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
//            $todaygold['statistics_role_value'] = $this->superAgentId;
//            //赠送金币  昨日（00点-14点）
//            $yesterdaygold['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
//            $yesterdaygold['statistics_role_value'] = $this->superAgentId;
//            //赠送金币  昨日全天
//            $yesterdaallgold['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
//            $yesterdaallgold['statistics_role_value'] = $this->superAgentId;

            $top_up_total = '';
            $yesterday_twelve_total = '';
            $yesterday_total = '';
            $today_gold = '';
            $yesterday_gold = '';
            $yesterda_all_gold = '';


            //金币消耗 今日（00点-14点）
            $todayconsumption['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $todayconsumption['statistics_role_value'] = $this->superAgentId;
            //金币消耗 昨日（00点-14点）
            $yesterdayconsumption['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $yesterdayconsumption['statistics_role_value'] = $this->superAgentId;
            //金币消耗 昨日全天（00点-14点）
            $yesterdayallconsumption['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $yesterdayallconsumption['statistics_role_value'] = $this->superAgentId;
            //新增用户 昨日全天（00点-14点）
            $todaynewnumber['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $todaynewnumber['statistics_role_value'] = $this->superAgentId;
            //新增用户 昨日全天（00点-14点）
            $yesterdaynewnumber['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $yesterdaynewnumber['statistics_role_value'] = $this->superAgentId;
            //新增用户 昨日全天（00点-14点）
            $yesterdaytotalnumber['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $yesterdaytotalnumber['statistics_role_value'] = $this->superAgentId;

        }


        //金币消耗  今日（00点-14点）
        $todayconsumption['statistics_mode'] = self::MODE_COIN_COST;
        $todayconsumption['statistics_type'] = self::TYPE_HOUR;
        $todayconsumption['statistics_timestamp'][0] = array('EGT', $today);
        $todayconsumption['statistics_timestamp'][1] = array('LT', $end);
        $today_consumption = StatisticsTotalModel::model()->getPromotersSumInfo($todayconsumption);

        //金币消耗  昨日（00点-14点）
        $yesterdayconsumption['statistics_mode'] = self::MODE_COIN_COST;
        $yesterdayconsumption['statistics_type'] = self::TYPE_HOUR;
        $yesterdayconsumption['statistics_timestamp'][0] = array('EGT', $yesterday);
        $yesterdayconsumption['statistics_timestamp'][1] = array('LT', $endyesterday);
        $yesterday_consumption = StatisticsTotalModel::model()->getPromotersSumInfo($yesterdayconsumption);

        //金币消耗  昨日全天
        $yesterdayallconsumption['statistics_mode'] = self::MODE_COIN_COST;
        $yesterdayallconsumption['statistics_type'] = self::TYPE_HOUR;
        $yesterdayallconsumption['statistics_timestamp'][0] = array('EGT', $yesterday);
        $yesterdayallconsumption['statistics_timestamp'][1] = array('LT', $throughoutyesterday);
        $yesterday_all_consumption = StatisticsTotalModel::model()->getPromotersSumInfo($yesterdayallconsumption);
        // 新增用户 今日（00点-14点）
        $todaynewnumber['statistics_mode'] = self::MODE_NEW_REGISTER;
        $todaynewnumber['statistics_type'] = self::TYPE_HOUR;
        $todaynewnumber['statistics_timestamp'][0] = array('EGT', $today);
        $todaynewnumber['statistics_timestamp'][1] = array('LT', $end);
        $today_new_number = StatisticsTotalModel::model()->getPromotersSumInfo($todaynewnumber);
        // 新增用户 昨日（00点-14点）
        $yesterdaynewnumber['statistics_mode'] = self::MODE_NEW_REGISTER;
        $yesterdaynewnumber['statistics_type'] = self::TYPE_HOUR;
        $yesterdaynewnumber['statistics_timestamp'][0] = array('EGT', $yesterday);
        $yesterdaynewnumber['statistics_timestamp'][1] = array('LT', $endyesterday);
        $yesterday_new_number = StatisticsTotalModel::model()->getPromotersSumInfo($yesterdaynewnumber);
        // 新增用户 昨日全天
        $yesterdaytotalnumber['statistics_mode'] = self::MODE_NEW_REGISTER;
        $yesterdaytotalnumber['statistics_type'] = self::TYPE_HOUR;
        $yesterdaytotalnumber['statistics_timestamp'][0] = array('EGT', $yesterday);
        $yesterdaytotalnumber['statistics_timestamp'][1] = array('LT', $throughoutyesterday);
        $yesterday_total_number = StatisticsTotalModel::model()->getPromotersSumInfo($yesterdaytotalnumber);
        //收益
        $today_revenue = $today_consumption / 10000;
        $yesterday_revenue = $yesterday_consumption / 10000;
        $yesterday_all_revenue = $yesterday_all_consumption / 10000;

        $top_up_total = array(
            'today_total' => $top_up_total / 100 ? $top_up_total / 100 : 0,
            'yesterdaytop_total' => $yesterday_twelve_total / 100 ? $yesterday_twelve_total / 100 : 0,
            'yesterdaytop_all_total' => $yesterday_total / 100 ? $yesterday_total / 100 : 0,
        );

        $giving_gold = array(
            'today_gold' => $today_gold ? $today_gold : 0,
            'yesterday_gold' => $yesterday_gold ? $yesterday_gold : 0,
            'yesterda_all_gold' => $yesterda_all_gold ? $yesterda_all_gold : 0,
        );

        $revenue_total = array(
            'today_revenue' => $today_revenue ? $today_revenue : 0,
            'yesterday_revenue' => $yesterday_revenue ? $yesterday_revenue : 0,
            'yesterday_all_revenue' => $yesterday_all_revenue ? $yesterday_all_revenue : 0,
        );

        $gold_consumption = array(
            'today_consumption' => $today_consumption ? $today_consumption : 0,
            'yesterday_consumption' => $yesterday_consumption ? $yesterday_consumption : 0,
            'yesterday_all_consumption' => $yesterday_all_consumption ? $yesterday_all_consumption : 0,
        );

        $today_new_number = array(
            'today_number' => $today_new_number ? $today_new_number : 0,
            'yesterday_number' => $yesterday_new_number ? $yesterday_new_number : 0,
            'yesterday_all_number' => $yesterday_total_number ? $yesterday_total_number : 0,
        );

        $date = array(
            'today' => array(
                'start_time' => date('Y-m-d H:i:s', $today),
                'start_times' => date('H', $today),
                'end_time' => date('Y-m-d H:i:s', $end),
                'end_times' => date('H', $end),
            ),
            'yesterday' => array(
                'start_time' => date('Y-m-d H:i:s', $yesterday),
                'start_times' => date('H', $yesterday),
                'end_time' => date('Y-m-d H:i:s', $endyesterday),
                'end_times' => date('H', $endyesterday),
            )
        );
        $data = array(
            'date' => $date,
            'top_up_total' => $top_up_total,
            'giving_gold' => $giving_gold,
            'revenue_total' => $revenue_total,
            'gold_consumption' => $gold_consumption,
            'today_new_number' => $today_new_number,

        );
        return $this->sendSuccess($data);
    }


    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @actionName 今日注册用户对比
     */
    public function statisticalUser()
    {
        $today = strtotime(date("Y-m-d", strtotime("0 day")));
        $yesterday = strtotime(date("Y-m-d", strtotime("-1 day")));

        for ($i = 0; $i < 24; $i++) {
            //今天
            $t_start_time = $today + ($i * 3600);
            $t_end_time = $today + (($i + 1) * 3600);

            //昨天时间段
            $y_start_time = $yesterday + ($i * 3600);
            $y_end_time = $yesterday + (($i + 1) * 3600);

            //今天时间段
            $t_date[] = date("H:i", $t_start_time);

            if (!$this->superAgentId) {
                $t_condition['statistics_timestamp'] = $t_start_time;
                $t_condition['statistics_role_type'] = self::ROLE_TYPE_ALL;
                $t_condition['statistics_mode'] = self::MODE_NEW_REGISTER;
                $t_condition['statistics_type'] = self::TYPE_HOUR;

                $y_condition['statistics_timestamp'] = $y_start_time;
                $y_condition['statistics_role_type'] = self::ROLE_TYPE_ALL;
                $y_condition['statistics_mode'] = self::MODE_NEW_REGISTER;
                $y_condition['statistics_type'] = self::TYPE_HOUR;

            } else {
                $t_condition['statistics_timestamp'] = $t_start_time;
                $t_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
                $t_condition['statistics_mode'] = self::MODE_NEW_REGISTER;
                $t_condition['statistics_type'] = self::TYPE_HOUR;
                $t_condition['statistics_role_value'] = $this->superAgentId;

                $y_condition['statistics_timestamp'] = $y_start_time;
                $y_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
                $y_condition['statistics_mode'] = self::MODE_NEW_REGISTER;
                $y_condition['statistics_type'] = self::TYPE_HOUR;
                $y_condition['statistics_role_value'] = $this->superAgentId;
            }
            //今天的注册数据
            $t_totalss = StatisticsTotalModel::model()->getFieldOne($t_condition);
            $t_total[] = (int)$t_totalss['statistics_sum'] ? $t_totalss['statistics_sum'] : 0;
            //昨天的注册数据
            $y_totalss = StatisticsTotalModel::model()->getFieldOne($y_condition);
            $y_total[] = (int)$y_totalss['statistics_sum'] ? $y_totalss['statistics_sum'] : 0;

        }
        $userStatistical = $this->userTime();
        //注册用户
        $data = array(
            'show_date' => $userStatistical['date'],
            't_half_total' => $userStatistical['t_half_total'],
            't_up_total' => $userStatistical['t_up_total'],
            'y_half_total' => $userStatistical['y_half_total'],
            'y_up_total' => $userStatistical['y_up_total'],
            'date' => $t_date,
            'today' => $t_total,
            'yesterday' => $y_total,
        );
        return $this->sendSuccess($data);
    }

    /**
     * @return array
     * 今日昨日人数对比
     */
    public function userTime()
    {
        //今日时间
        $today = strtotime(date('Y-m-d', time()));
        // 当天的14
//        $end = $today + 14 * 60 * 60;
        $end = time();
        // 当天的24
        $y_end = $today + 24 * 60 * 60;
        //昨日
        $yesterday = strtotime(date('Y-m-d', strtotime('-1 day')));
        //14点
//        $endyesterday = $yesterday + 14 * 60 * 60;
        $endyesterday = time() - 24 * 3600;
        //全天
        $throughoutyesterday = $yesterday + 24 * 60 * 60;

        //权限判断
        if (!$this->superAgentId) {
            //今天14点
            $t_condition['statistics_role_type'] = self::ROLE_TYPE_ALL;
            $t_condition['statistics_mode'] = self::MODE_NEW_REGISTER;
            $t_condition['statistics_type'] = self::TYPE_HOUR;
            $t_condition['statistics_timestamp'][0] = array('EGT', $today);
            $t_condition['statistics_timestamp'][1] = array('LT', $end);
            // 今天
            $t_up_condition['statistics_role_type'] = self::ROLE_TYPE_ALL;
            $t_up_condition['statistics_mode'] = self::MODE_NEW_REGISTER;
            $t_up_condition['statistics_type'] = self::TYPE_DAY;
            $t_up_condition['statistics_timestamp'] = $today;

            //昨天14点
            $y_condition['statistics_role_type'] = self::ROLE_TYPE_ALL;
            $y_condition['statistics_mode'] = self::MODE_NEW_REGISTER;
            $y_condition['statistics_type'] = self::TYPE_HOUR;
            $y_condition['statistics_timestamp'][0] = array('EGT', $yesterday);
            $y_condition['statistics_timestamp'][1] = array('LT', $endyesterday);

            //昨天全天
            $y_up_condition['statistics_role_type'] = self::ROLE_TYPE_ALL;
            $y_up_condition['statistics_mode'] = self::MODE_NEW_REGISTER;
            $y_up_condition['statistics_type'] = self::TYPE_DAY;
            $y_up_condition['statistics_timestamp'] = $yesterday;

        } else {
            //今天14点
            $t_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $t_condition['statistics_mode'] = self::MODE_NEW_REGISTER;
            $t_condition['statistics_type'] = self::TYPE_HOUR;
            $t_condition['statistics_role_value'] = $this->superAgentId;
            $t_condition['statistics_timestamp'][0] = array('EGT', $today);
            $t_condition['statistics_timestamp'][1] = array('LT', $end);
            // 今天
            $t_up_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $t_up_condition['statistics_mode'] = self::MODE_NEW_REGISTER;
            $t_up_condition['statistics_type'] = self::TYPE_DAY;
            $t_up_condition['statistics_role_value'] = $this->superAgentId;
            $t_up_condition['statistics_timestamp'] = $today;
            //昨天14点
            $y_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $y_condition['statistics_mode'] = self::MODE_NEW_REGISTER;
            $y_condition['statistics_type'] = self::TYPE_HOUR;
            $y_condition['statistics_role_value'] = $this->superAgentId;
            $y_condition['statistics_timestamp'][0] = array('EGT', $yesterday);
            $y_condition['statistics_timestamp'][1] = array('LT', $endyesterday);
            //昨天全天
            $y_up_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $y_up_condition['statistics_mode'] = self::MODE_NEW_REGISTER;
            $y_up_condition['statistics_type'] = self::TYPE_DAY;
            $y_up_condition['statistics_role_value'] = $this->superAgentId;
            $y_up_condition['statistics_timestamp'] = $yesterday;

        }

        $t_half_total = StatisticsTotalModel::model()->getPromotersSumInfo($t_condition);
        $t_up_total = StatisticsTotalModel::model()->getFieldOne($t_up_condition);
        $y_half_total = StatisticsTotalModel::model()->getPromotersSumInfo($y_condition);
        $y_up_total = StatisticsTotalModel::model()->getFieldOne($y_up_condition);

        $date = array(
            'today' => array(
                'start_time' => date('Y-m-d H:i:s', $today),
                'start_times' => date('H', $today),
                'end_time' => date('Y-m-d H:i:s', $end),
                'end_times' => date('H', $end),
            ),
            'yesterday' => array(
                'start_time' => date('Y-m-d H:i:s', $yesterday),
                'start_times' => date('H', $yesterday),
                'end_time' => date('Y-m-d H:i:s', $endyesterday),
                'end_times' => date('H', $endyesterday),
            )
        );

        $data = array(
            'date' => $date,
            't_half_total' => $t_half_total ? $t_half_total : 0,
            't_up_total' => $t_up_total['statistics_sum'] ? $t_up_total['statistics_sum'] : 0,
            'y_half_total' => $y_half_total ? $y_half_total : 0,
            'y_up_total' => $y_up_total['statistics_sum'] ? $y_up_total['statistics_sum'] : 0,
        );
        return $data;
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @actionName  今日昨日充值对比
     * 充值统计
     */
    public function statisticalTopUp()
    {

        $today = strtotime(date("Y-m-d", strtotime("0 day")));
        $yesterday = strtotime(date("Y-m-d", strtotime("-1 day")));

        for ($i = 0; $i < 24; $i++) {
            //今天
            $t_start_time = $today + ($i * 3600);
            $t_end_time = $today + (($i + 1) * 3600);

            //昨天时间段
            $y_start_time = $yesterday + ($i * 3600);
            $y_end_time = $yesterday + (($i + 1) * 3600);

            //今天时间段
            $t_date[] = date("H:i", $t_start_time);

            if (!$this->superAgentId) {
                $t_condition['statistics_timestamp'] = $t_start_time;
                $t_condition['statistics_role_type'] = self::ROLE_TYPE_ALL;
                $t_condition['statistics_mode'] = self::MODE_CHARGE_SUM;
                $t_condition['statistics_type'] = self::TYPE_HOUR;

                $y_condition['statistics_timestamp'] = $y_start_time;
                $y_condition['statistics_role_type'] = self::ROLE_TYPE_ALL;
                $y_condition['statistics_mode'] = self::MODE_CHARGE_SUM;
                $y_condition['statistics_type'] = self::TYPE_HOUR;

            } else {
                $t_condition['statistics_timestamp'] = $t_start_time;
                $t_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
                $t_condition['statistics_mode'] = self::MODE_CHARGE_SUM;
                $t_condition['statistics_type'] = self::TYPE_HOUR;
                $t_condition['statistics_role_value'] = $this->superAgentId;

                $y_condition['statistics_timestamp'] = $y_start_time;
                $y_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
                $y_condition['statistics_mode'] = self::MODE_CHARGE_SUM;
                $y_condition['statistics_type'] = self::TYPE_HOUR;
                $y_condition['statistics_role_value'] = $this->superAgentId;
            }
            //今日充值
            $t_totalss = StatisticsTotalModel::model()->getFieldOne($t_condition);
            $t_total_up[] = (int)$t_totalss['statistics_sum'] / 100 ? $t_totalss['statistics_sum'] / 100 : 0;
            //昨天充值
            $y_totalss = StatisticsTotalModel::model()->getFieldOne($y_condition);
            $y_total_up[] = (int)$y_totalss['statistics_sum'] / 100 ? $y_totalss['statistics_sum'] / 100 : 0;
        }
        $TopUpTime = $this->TopUpTime();
        $data = array(
            'show_date' => $TopUpTime['date'],
            't_half_total' => $TopUpTime['t_half_total'] / 100,
            't_up_total' => $TopUpTime['t_up_total'] / 100,
            'y_half_total' => $TopUpTime['y_half_total'] / 100,
            'y_up_total' => $TopUpTime['y_up_total'] / 100,
            'date' => $t_date,
            'today' => $t_total_up,
            'yesterday' => $y_total_up,
        );

        return $this->sendSuccess($data);

    }

    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     */
    public function TopUpTime()
    {
        //今日时间
        $today = strtotime(date('Y-m-d', time()));
        // 当天的24
//        $end = $today + 14 * 60 * 60;
        $end = time();
        $y_end = $today + 24 * 60 * 60;
        //昨日
        $yesterday = strtotime(date('Y-m-d', strtotime('-1 day')));
        //14点
//        $endyesterday = $yesterday + 14 * 60 * 60;
        $endyesterday = time() - 24 * 3600;

        //全天
        $throughoutyesterday = $yesterday + 24 * 60 * 60;

        //权限判断
        if (!$this->superAgentId) {
            //今天14点
            $t_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $t_condition['statistics_mode'] = self::MODE_CHARGE_SUM;
            $t_condition['statistics_type'] = self::TYPE_HOUR;
            $t_condition['statistics_timestamp'][0] = array('EGT', $today);
            $t_condition['statistics_timestamp'][1] = array('LT', $end);
            // 今天
            $t_up_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $t_up_condition['statistics_mode'] = self::MODE_CHARGE_SUM;
            $t_up_condition['statistics_type'] = self::TYPE_DAY;
            $t_up_condition['statistics_timestamp'] = $today;

            //昨天14点
            $y_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $y_condition['statistics_mode'] = self::MODE_CHARGE_SUM;
            $y_condition['statistics_type'] = self::TYPE_HOUR;
            $y_condition['statistics_timestamp'][0] = array('EGT', $yesterday);
            $y_condition['statistics_timestamp'][1] = array('LT', $endyesterday);

            //昨天全天
            $y_up_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $y_up_condition['statistics_mode'] = self::MODE_CHARGE_SUM;
            $y_up_condition['statistics_type'] = self::TYPE_DAY;
            $y_up_condition['statistics_timestamp'] = $yesterday;

        } else {
            //今天14点
            $t_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $t_condition['statistics_mode'] = self::MODE_CHARGE_SUM;
            $t_condition['statistics_type'] = self::TYPE_HOUR;
            $t_condition['statistics_role_value'] = $this->superAgentId;
            $t_condition['statistics_timestamp'][0] = array('EGT', $today);
            $t_condition['statistics_timestamp'][1] = array('LT', $end);
            // 今天
            $t_up_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $t_up_condition['statistics_mode'] = self::MODE_CHARGE_SUM;
            $t_up_condition['statistics_type'] = self::TYPE_DAY;
            $t_up_condition['statistics_role_value'] = $this->superAgentId;
            $t_up_condition['statistics_timestamp'] = $today;
            //昨天14点
            $y_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $y_condition['statistics_mode'] = self::MODE_CHARGE_SUM;
            $y_condition['statistics_type'] = self::TYPE_HOUR;
            $y_condition['statistics_role_value'] = $this->superAgentId;
            $y_condition['statistics_timestamp'][0] = array('EGT', $yesterday);
            $y_condition['statistics_timestamp'][1] = array('LT', $endyesterday);
            //昨天全天
            $y_up_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $y_up_condition['statistics_mode'] = self::MODE_CHARGE_SUM;
            $y_up_condition['statistics_type'] = self::TYPE_DAY;
            $y_up_condition['statistics_role_value'] = $this->superAgentId;
            $y_up_condition['statistics_timestamp'] = $yesterday;

        }
        $t_half_total = StatisticsTotalModel::model()->getPromotersSumInfo($t_condition);
        $t_up_total = StatisticsTotalModel::model()->getFieldOne($t_up_condition);
        $y_half_total = StatisticsTotalModel::model()->getPromotersSumInfo($y_condition);
        $y_up_total = StatisticsTotalModel::model()->getFieldOne($y_up_condition);


        $date = array(
            'today' => array(
                'start_time' => date('Y-m-d H:i:s', $today),
                'start_times' => date('H', $today),
                'end_time' => date('Y-m-d H:i:s', $end),
                'end_times' => date('H', $end),
            ),
            'yesterday' => array(
                'start_time' => date('Y-m-d H:i:s', $yesterday),
                'start_times' => date('H', $yesterday),
                'end_time' => date('Y-m-d H:i:s', $endyesterday),
                'end_times' => date('H', $endyesterday),
            )
        );
        $data = array(
            'date' => $date,
            't_half_total' => $t_half_total ? $t_half_total : 0,
            't_up_total' => $t_up_total['statistics_sum'] ? $t_up_total['statistics_sum'] : 0,
            'y_half_total' => $y_half_total ? $y_half_total : 0,
            'y_up_total' => $y_up_total['statistics_sum'] ? $y_up_total['statistics_sum'] : 0,
        );
        return $data;

    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 用户消耗
     * @actionName  今日昨日对比 消耗
     */
    public function userConsumption()
    {
        $today = strtotime(date("Y-m-d", strtotime("0 day")));
        $yesterday = strtotime(date("Y-m-d", strtotime("-1 day")));
        for ($i = 0; $i < 24; $i++) {
            //今天
            $t_start_time = $today + ($i * 3600);
            $t_end_time = $today + (($i + 1) * 3600);
            //昨天时间段
            $y_start_time = $yesterday + ($i * 3600);
            $y_end_time = $yesterday + (($i + 1) * 3600);
            //今天时间段
            $t_date[] = date("H:i", $t_start_time);

            if (!$this->superAgentId) {
                $t_condition['statistics_timestamp'] = $t_start_time;
                $t_condition['statistics_role_type'] = self::ROLE_TYPE_ALL;
                $t_condition['statistics_mode'] = self::MODE_COIN_COST;
                $t_condition['statistics_type'] = self::TYPE_HOUR;

                $y_condition['statistics_timestamp'] = $y_start_time;
                $y_condition['statistics_role_type'] = self::ROLE_TYPE_ALL;
                $y_condition['statistics_mode'] = self::MODE_COIN_COST;
                $y_condition['statistics_type'] = self::TYPE_HOUR;

            } else {
                $t_condition['statistics_timestamp'] = $t_start_time;
                $t_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
                $t_condition['statistics_mode'] = self::MODE_COIN_COST;
                $t_condition['statistics_type'] = self::TYPE_HOUR;
                $t_condition['statistics_role_value'] = $this->superAgentId;

                $y_condition['statistics_timestamp'] = $y_start_time;
                $y_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
                $y_condition['statistics_mode'] = self::MODE_COIN_COST;
                $y_condition['statistics_type'] = self::TYPE_HOUR;
                $y_condition['statistics_role_value'] = $this->superAgentId;
            }
            //今日消耗
            $t_totalss = StatisticsTotalModel::model()->getFieldOne($t_condition);
            $t_total_up[] = (int)$t_totalss['statistics_sum'] ? $t_totalss['statistics_sum'] : 0;
            //昨日消耗
            $y_totalss = StatisticsTotalModel::model()->getFieldOne($y_condition);
            $y_total_up[] = (int)$y_totalss['statistics_sum'] ? $y_totalss['statistics_sum'] : 0;


        }


        $consumptionTime = $this->consumptionTime();
        $data = array(
            'show_date' => $consumptionTime['date'],
            't_half_total' => $consumptionTime['t_half_total'],
            't_up_total' => $consumptionTime['t_up_total'],
            'y_half_total' => $consumptionTime['y_half_total'],
            'y_up_total' => $consumptionTime['y_up_total'],
            'date' => $t_date,
            'today' => $t_total_up,
            'yesterday' => $y_total_up,
        );

        return $this->sendSuccess($data);

    }

    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     */
    public function consumptionTime()
    {

        //今日时间
        $today = strtotime(date('Y-m-d', time()));
        // 当天的24
//        $end = $today + 14 * 60 * 60;
        $end = time();
        $y_end = $today + 24 * 60 * 60;
        //昨日
        $yesterday = strtotime(date('Y-m-d', strtotime('-1 day')));
        //14点
//        $endyesterday = $yesterday + 14 * 60 * 60;
        $endyesterday = time() - 24 * 3600;
        //全天
        $throughoutyesterday = $yesterday + 24 * 60 * 60;

        //权限判断
        if (!$this->superAgentId) {
            //今天14点
            $t_condition['statistics_role_type'] = self::ROLE_TYPE_ALL;
            $t_condition['statistics_mode'] = self::MODE_COIN_COST;
            $t_condition['statistics_type'] = self::TYPE_HOUR;
            $t_condition['statistics_role_value'] = self::MODE_ALL;
            $t_condition['statistics_timestamp'][0] = array('EGT', $today);
            $t_condition['statistics_timestamp'][1] = array('LT', $end);
            // 今天
            $t_up_condition['statistics_role_type'] = self::ROLE_TYPE_ALL;
            $t_up_condition['statistics_mode'] = self::MODE_COIN_COST;
            $t_up_condition['statistics_type'] = self::TYPE_DAY;
            $t_up_condition['statistics_role_value'] = self::MODE_ALL;
            $t_up_condition['statistics_timestamp'] = $today;

            //昨天14点
            $y_condition['statistics_role_type'] = self::ROLE_TYPE_ALL;
            $y_condition['statistics_mode'] = self::MODE_COIN_COST;
            $y_condition['statistics_type'] = self::TYPE_HOUR;
            $y_condition['statistics_timestamp'][0] = array('EGT', $yesterday);
            $y_condition['statistics_timestamp'][1] = array('LT', $endyesterday);

            //昨天全天
            $y_up_condition['statistics_role_type'] = self::ROLE_TYPE_ALL;
            $y_up_condition['statistics_mode'] = self::MODE_COIN_COST;
            $y_up_condition['statistics_type'] = self::TYPE_DAY;
            $y_up_condition['statistics_timestamp'] = $yesterday;

        } else {
            //今天14点
            $t_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $t_condition['statistics_mode'] = self::MODE_COIN_COST;
            $t_condition['statistics_type'] = self::TYPE_HOUR;
            $t_condition['statistics_role_value'] = $this->superAgentId;
            $t_condition['statistics_timestamp'][0] = array('EGT', $today);
            $t_condition['statistics_timestamp'][1] = array('LT', $end);
            // 今天
            $t_up_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $t_up_condition['statistics_mode'] = self::MODE_COIN_COST;
            $t_up_condition['statistics_type'] = self::TYPE_DAY;
            $t_up_condition['statistics_role_value'] = $this->superAgentId;
            $t_up_condition['statistics_timestamp'] = $today;
            //昨天14点
            $y_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $y_condition['statistics_mode'] = self::MODE_COIN_COST;
            $y_condition['statistics_type'] = self::TYPE_HOUR;
            $y_condition['statistics_role_value'] = $this->superAgentId;
            $y_condition['statistics_timestamp'][0] = array('EGT', $yesterday);
            $y_condition['statistics_timestamp'][1] = array('LT', $endyesterday);
            //昨天全天
            $y_up_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $y_up_condition['statistics_mode'] = self::MODE_COIN_COST;
            $y_up_condition['statistics_type'] = self::TYPE_DAY;
            $y_up_condition['statistics_role_value'] = $this->superAgentId;
            $y_up_condition['statistics_timestamp'] = $yesterday;

        }
        $t_half_total = StatisticsTotalModel::model()->getPromotersSumInfo($t_condition);
        $t_up_total = StatisticsTotalModel::model()->getFieldOne($t_up_condition);
        $y_half_total = StatisticsTotalModel::model()->getPromotersSumInfo($y_condition);
        $y_up_total = StatisticsTotalModel::model()->getFieldOne($y_up_condition);

        $date = array(
            'today' => array(
                'start_time' => date('Y-m-d H:i:s', $today),
                'start_times' => date('H', $today),
                'end_time' => date('Y-m-d H:i:s', $end),
                'end_times' => date('H', $end),
            ),
            'yesterday' => array(
                'start_time' => date('Y-m-d H:i:s', $yesterday),
                'start_times' => date('H', $yesterday),
                'end_time' => date('Y-m-d H:i:s', $endyesterday),
                'end_times' => date('H', $endyesterday),
            )
        );
        $data = array(
            'date' => $date,
            't_half_total' => $t_half_total ? $t_half_total : 0,
            't_up_total' => $t_up_total['statistics_sum'] ? $t_up_total['statistics_sum'] : 0,
            'y_half_total' => $y_half_total ? $y_half_total : 0,
            'y_up_total' => $y_up_total['statistics_sum'] ? $y_up_total['statistics_sum'] : 0,
        );
        return $data;

    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     * @actionName  活跃人数对比
     * 活跃人数
     */
    public function userActive()
    {

        $today = strtotime(date("Y-m-d", strtotime("0 day")));
        $yesterday = strtotime(date("Y-m-d", strtotime("-1 day")));
        for ($i = 0; $i < 24; $i++) {
            //今天
            $t_start_time = $today + ($i * 3600);
            $t_end_time = $today + (($i + 1) * 3600);

            //昨天时间段
            $y_start_time = $yesterday + ($i * 3600);
            $y_end_time = $yesterday + (($i + 1) * 3600);

            //今天时间段
            $t_date[] = date("H:i", $t_start_time);

            if (!$this->superAgentId) {
                $t_condition['statistics_timestamp'] = $t_start_time;
                $t_condition['statistics_role_type'] = self::ROLE_TYPE_ALL;
                $t_condition['statistics_mode'] = self::MODE_LOGIN_IN;
                $t_condition['statistics_type'] = self::TYPE_HOUR;

                $y_condition['statistics_timestamp'] = $y_start_time;
                $y_condition['statistics_role_type'] = self::ROLE_TYPE_ALL;
                $y_condition['statistics_mode'] = self::MODE_LOGIN_IN;
                $y_condition['statistics_type'] = self::TYPE_HOUR;

            } else {
                $t_condition['statistics_timestamp'] = $t_start_time;
                $t_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
                $t_condition['statistics_mode'] = self::MODE_LOGIN_IN;
                $t_condition['statistics_type'] = self::TYPE_HOUR;
                $t_condition['statistics_role_value'] = $this->superAgentId;

                $y_condition['statistics_timestamp'] = $y_start_time;
                $y_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
                $y_condition['statistics_mode'] = self::MODE_LOGIN_IN;
                $y_condition['statistics_type'] = self::TYPE_HOUR;
                $y_condition['statistics_role_value'] = $this->superAgentId;
            }
            //今日消耗
            $t_totalss = StatisticsTotalModel::model()->getFieldOne($t_condition);
            $t_total_up[] = (int)$t_totalss['statistics_sum'] ? $t_totalss['statistics_sum'] : 0;
            //昨日消耗
            $y_totalss = StatisticsTotalModel::model()->getFieldOne($y_condition);
            $y_total_up[] = (int)$y_totalss['statistics_sum'] ? $y_totalss['statistics_sum'] : 0;

        }


        $consumptionTime = $this->ActiveTime();
        $data = array(
            'show_date' => $consumptionTime['date'],
            't_half_total' => $consumptionTime['t_half_total'],
            't_up_total' => $consumptionTime['t_up_total'],
            'y_half_total' => $consumptionTime['y_half_total'],
            'y_up_total' => $consumptionTime['y_up_total'],
            'date' => $t_date,
            'today' => $t_total_up,
            'yesterday' => $y_total_up,
        );

        return $this->sendSuccess($data);


    }

    /**
     * @return array
     */
    public function ActiveTime()
    {
        //今日时间
        $today = strtotime(date('Y-m-d', time()));
        // 当天的24
//        $end = $today + 14 * 60 * 60;
        $end = time();
        $y_end = $today + 24 * 60 * 60;
        //昨日
        $yesterday = strtotime(date('Y-m-d', strtotime('-1 day')));
        //14点
//        $endyesterday = $yesterday + 14 * 60 * 60;
        $endyesterday = time() - 24 * 3600;
        //全天
        $throughoutyesterday = $yesterday + 24 * 60 * 60;

        //权限判断
        if (!$this->superAgentId) {
            //今天14点
            $t_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $t_condition['statistics_mode'] = self::MODE_LOGIN_IN;
            $t_condition['statistics_type'] = self::TYPE_HOUR;
            $t_condition['statistics_timestamp'][0] = array('EGT', $today);
            $t_condition['statistics_timestamp'][1] = array('LT', $end);
            // 今天
            $t_up_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $t_up_condition['statistics_mode'] = self::MODE_LOGIN_IN;
            $t_up_condition['statistics_type'] = self::TYPE_DAY;
            $t_up_condition['statistics_timestamp'] = $today;

            //昨天14点
            $y_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $y_condition['statistics_mode'] = self::MODE_LOGIN_IN;
            $y_condition['statistics_type'] = self::TYPE_HOUR;
            $y_condition['statistics_timestamp'][0] = array('EGT', $yesterday);
            $y_condition['statistics_timestamp'][1] = array('LT', $endyesterday);

            //昨天全天
            $y_up_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $y_up_condition['statistics_mode'] = self::MODE_LOGIN_IN;
            $y_up_condition['statistics_type'] = self::TYPE_DAY;
            $y_up_condition['statistics_timestamp'] = $yesterday;

        } else {
            //今天14点
            $t_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $t_condition['statistics_mode'] = self::MODE_LOGIN_IN;
            $t_condition['statistics_type'] = self::TYPE_HOUR;
            $t_condition['statistics_role_value'] = $this->superAgentId;
            $t_condition['statistics_timestamp'][0] = array('EGT', $today);
            $t_condition['statistics_timestamp'][1] = array('LT', $end);
            // 今天
            $t_up_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $t_up_condition['statistics_mode'] = self::MODE_LOGIN_IN;
            $t_up_condition['statistics_type'] = self::TYPE_DAY;
            $t_up_condition['statistics_role_value'] = $this->superAgentId;
            $t_up_condition['statistics_timestamp'] = $today;
            //昨天14点
            $y_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $y_condition['statistics_mode'] = self::MODE_LOGIN_IN;
            $y_condition['statistics_type'] = self::TYPE_HOUR;
            $y_condition['statistics_role_value'] = $this->superAgentId;
            $y_condition['statistics_timestamp'][0] = array('EGT', $yesterday);
            $y_condition['statistics_timestamp'][1] = array('LT', $endyesterday);
            //昨天全天
            $y_up_condition['statistics_role_type'] = self::ROLE_TYPE_CHANNEL;
            $y_up_condition['statistics_mode'] = self::MODE_LOGIN_IN;
            $y_up_condition['statistics_type'] = self::TYPE_DAY;
            $y_up_condition['statistics_role_value'] = $this->superAgentId;
            $y_up_condition['statistics_timestamp'] = $yesterday;

        }
        $t_half_total = StatisticsTotalModel::model()->getPromotersSumInfo($t_condition);
        $t_up_total = StatisticsTotalModel::model()->getFieldOne($t_up_condition);
        $y_half_total = StatisticsTotalModel::model()->getPromotersSumInfo($y_condition);
        $y_up_total = StatisticsTotalModel::model()->getFieldOne($y_up_condition);

        $date = array(
            'today' => array(
                'start_time' => date('Y-m-d H:i:s', $today),
                'start_times' => date('H', $today),
                'end_time' => date('Y-m-d H:i:s', $end),
                'end_times' => date('H', $end),
            ),
            'yesterday' => array(
                'start_time' => date('Y-m-d H:i:s', $yesterday),
                'start_times' => date('H', $yesterday),
                'end_time' => date('Y-m-d H:i:s', $endyesterday),
                'end_times' => date('H', $endyesterday),
            )
        );
        $data = array(
            'date' => $date,
            't_half_total' => $t_half_total ? $t_half_total : 0,
            't_up_total' => $t_up_total['statistics_sum'] ? $t_up_total['statistics_sum'] : 0,
            'y_half_total' => $y_half_total ? $y_half_total : 0,
            'y_up_total' => $y_up_total['statistics_sum'] ? $y_up_total['statistics_sum'] : 0,
        );
        return $data;

    }


    //该数据格式用于表格
    public function allGameRoundDay()
    {
        $GameInfoModel = new GameInfoModel();
        $GameRoundDayModel = new GameRoundDayModel();
        $gameId = $total = array();

        //日期信息
        for ($i = 6; $i >= 0; $i--) {
            $date[] = date('m-d', strtotime(-$i . 'days'));
        }
        array_push($date, '游戏');

        $gameKey = array('oneday', 'twoday', 'threeday', 'fourday', 'fiveday', 'sixday', 'sevenday', 'game');
        //获取所有的游戏id,游戏名称
        $gameList = $GameInfoModel->getGameInfo('game_id,game_name', array());
        $date = array_combine($gameKey, $date);

        foreach ($gameList as $key => $value) {
            array_push($gameId, $value['game_id']);
            array_push($total, array('game_name' => $value['game_name']));
        }
        $superAgentId = $this->superAgentId ? $this->superAgentId : 0;
        //获取各个游戏指定日期的数据
        foreach ($gameId as $key => $value) {
            for ($i = 6; $i >= 0; $i--) {
                $startTime = strtotime(date('Ymd', strtotime(-($i) . 'days')));
                $endTime = strtotime(date('Ymd', strtotime(-($i - 1) . 'days')));

                //取出前七天的数据
                $total[$key]['data'][] = (int)$GameRoundDayModel->gameRoundDay($startTime, $endTime, $value, 'game_round_num', $superAgentId)['game_round_num'];
            }
        }

        $dataTmp = array();

        $data = DataArrange::dataSortDesc($gameId, $total);
        foreach ($data['total'] as $key => $value) {
            $dataTmp[$key] = $value['data'];
            array_push($dataTmp[$key], $value['game_name']);
        }
        foreach ($dataTmp as $key => $value) {
            $total[$key] = array_combine($gameKey, $value);
        }
        array_unshift($total, $date);

        return $this->sendSuccess($total);
    }
}





















