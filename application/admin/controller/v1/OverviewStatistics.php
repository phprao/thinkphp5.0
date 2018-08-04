<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-03-19 10:25:48
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 数据统计
 +---------------------------------------------------------- 
 */

namespace app\admin\controller\v1;

use app\admin\block\DataArrange;
use app\admin\controller\Controller;
use app\admin\model\GameInfoModel;
use app\admin\model\GameRoundDayModel;
use app\admin\model\MoneyRateInfoModel;
use app\admin\model\PayRecordModel;
use app\admin\model\PlayerModel;
use app\admin\model\PlayerStatisticalModel;
use app\admin\model\StatisticsTotalModel;
use app\common\components\Helper;

/**
 * @controllerName 数据统计
 */

class OverviewStatistics extends Controller
{
    protected $error_code = 0;
    protected $error_msg = '';
    protected $role_type = 0;
    protected $role_value = 0;

    // 统计时间段
    const TYPE_HOUR = 1;// 小时
    const TYPE_DAY  = 2;// 天
    const TYPE_ALL  = 3;// 所有的
    const TYPE_DAY_LEN = 14;// 按天查看时间段长度，默认14天（包括今天）
    // 统计类型
    const MODE_ALL          = 0; //所有的
    const MODE_CHARGE_SUM   = 1; //1-充值金额（分）
    const MODE_NEW_REGISTER = 2; //2-注册用户数（个）
    const MODE_COIN_COST    = 3; //3-金币消耗数（个）
    const MODE_LOGIN_IN     = 4; //4-活跃玩家数（个）
    const MODE_SEND_COIN    = 5; //5-赠送金币数（个）
    const MODE_GAME_PLAYER  = 6; //6-游戏玩家数（个）
    const MODE_PRODUCE_SUM  = 7; //7-产出金币数（充值+赠送）
    const MODE_LAST_SUM     = 8; //8-玩家剩余金币数（按天统计）
    const MODE_EARN_SUM     = 100; //100-金币消耗产生的总收益
 
    /**
     * 初始化操作
     * @access protected
     */
    protected function _initialize()
    {
        parent::_initialize();
        $info = $this->isLogin($this->token);
        if(isset($info['agentInfo']) && !empty($info['agentInfo'])){
            $this->role_type = 1;
            $this->role_value = $info['agentInfo']['agent_id'];
        }
    }

    /**
     * 请求参数初始化
     * @return [type] [description]
     */
    protected function initRequestParam(){
        $time = time();
        $this->timestamp_day = strtotime(date('Y-m-d',$time));
        $input = array(
            'mode'   =>(int)$this->request->get('mode'),
            'type'   =>(int)$this->request->get('type')
        );
        
        // $input = array(
        //     'mode'    =>'1',//类型
        //     'type'    =>'2'//时间段
        // );

        $this->modeArr = array(
            self::MODE_CHARGE_SUM   =>'充值金额',
            self::MODE_NEW_REGISTER =>'注册用户数',
            self::MODE_COIN_COST    =>'金币消耗数',
            self::MODE_LOGIN_IN     =>'活跃玩家数',
            self::MODE_SEND_COIN    =>'赠送金币数',
            self::MODE_GAME_PLAYER  =>'游戏玩家数',
            self::MODE_PRODUCE_SUM  =>'产出金币数',
            self::MODE_LAST_SUM     =>'玩家剩余金币数',
            self::MODE_EARN_SUM     =>'收益总计（元）'
        );

        // 渠道商可见的数据
        if($this->role_type == 1){
            $this->modeArr = array(
                self::MODE_EARN_SUM     =>'游戏收益总额（元）',
                self::MODE_PRODUCE_SUM  =>'游戏产出金币数',
                self::MODE_COIN_COST    =>'游戏消耗数',
                self::MODE_NEW_REGISTER =>'玩家总数(人)',
                self::MODE_LOGIN_IN     =>'活跃玩家数',
                self::MODE_GAME_PLAYER  =>'游戏玩家数',
                self::MODE_LAST_SUM     =>'玩家剩余金币数'
            );
        }

        $modeArrKeys = array_keys($this->modeArr);

        $filter = array();
        if($input['type'] && in_array($input['type'],[self::TYPE_HOUR,self::TYPE_DAY,self::TYPE_ALL])){
            $filter['type'] = $input['type'];
        }else{
            $filter['type'] = self::TYPE_ALL;
        }

        if($input['mode'] && in_array($input['mode'],$modeArrKeys)){
            $filter['mode'] = $input['mode'];
        }else{
            $filter['mode'] = self::MODE_ALL;
            if($filter['type'] == self::TYPE_DAY || $filter['type'] == self::TYPE_HOUR){
                $filter['mode'] = self::MODE_CHARGE_SUM;
            }
        }

        $filter['role_type'] = isset($this->role_type) ? $this->role_type : 0;
        $filter['role_value'] = isset($this->role_value) ? $this->role_value : 0;

        $filter = (object)$filter;
        return $filter;
    }

    /**
     * @actionName 数据总览
     * @return [type] [description]
     */
    public function dataList(){
        $condition = $this->initRequestParam();
        if($condition === false){
            return $this->sendError(100001, '参数错误');
        }
        $condition->modeSet = $condition->mode;
        $returnData = array();
        if($condition->mode == self::MODE_ALL){
            foreach($this->modeArr as $key => $val){
                $condition->modeSet = $key;
                $returnData[$key]['mode'] = $key;
                $returnData[$key]['desc'] = $this->modeArr[$key];
                if($key == self::MODE_EARN_SUM){
                    $returnData[$key]['data'] = $this->getStatisticsIncomeData($condition);
                }else{
                    if($condition->modeSet == self::MODE_CHARGE_SUM){
                        $returnData[$key]['data'] = Helper::fomatMoneyData( ($this->getStatisticsData($condition))/100 );// 元
                    }elseif(in_array($condition->modeSet,[self::MODE_PRODUCE_SUM,self::MODE_COIN_COST,self::MODE_SEND_COIN,self::MODE_LAST_SUM])){
                        $returnData[$key]['data'] = Helper::fomatBigData($this->getStatisticsData($condition));
                    }else{
                        $returnData[$key]['data'] = $this->getStatisticsData($condition);
                    }
                }
            }
        }else{
            if(!isset($this->modeArr[$condition->modeSet])){
                return $this->sendError(100001, '非法请求');
            }
            $returnData[0]['mode'] = $condition->modeSet;
            $returnData[0]['desc'] = $this->modeArr[$condition->modeSet];
            $returnData[0]['data'] = $this->getStatisticsData($condition);
            if($condition->type == self::TYPE_DAY){
                if(!empty($returnData[0]['data']['value'])){
                    $returnData[0]['data']['value'] = $this->format_return_data($returnData[0]['data']['value'],$condition->modeSet);
                }
            }
        }

        return $this->sendSuccess(['list' => $returnData]);

    }
    /**
     * @actionName 上周本周对比
     * @return [type] [description]
     */
    public function weekCompare(){
        $condition = $this->initRequestParam();
        if($condition === false){
            return $this->sendError(100001, '参数错误');
        }
        $condition->type = self::TYPE_DAY;
        $returnData = array();
        $modeArr = array(
            self::MODE_CHARGE_SUM   =>'充值金额',
            self::MODE_NEW_REGISTER =>'注册用户数',
            self::MODE_COIN_COST    =>'金币消耗数'
        );
        if($condition->role_type == 1){
            $modeArr = array(
                self::MODE_NEW_REGISTER =>'注册用户数',
                self::MODE_COIN_COST    =>'金币消耗数'
            );
        }
        foreach($modeArr as $key => $val){
            $condition->modeSet = $key;
            $temp['mode'] = $key;
            $temp['desc'] = $modeArr[$key];
            $temp['data'] = $this->getStatisticsWeekData($condition);
            array_push($returnData,$temp);
        }

        return $this->sendSuccess(['list' => $returnData]);
    }

    /**
     * @actionName 数据总览--折线图
     * @return [type] [description]
     */
    public function everydayData(){

    }

    /**
     * @actionName 各游戏每日金币消耗堆叠
     */
    public function coinCost(){
        $GameInfoModel = new GameInfoModel();
        $GameRoundDayModel = new GameRoundDayModel();
        $gameId = $total = array();

        //日期信息
        if($this->role_type == 1){
            for($i = 13;$i >= 0;$i --) {
                $date[] = date('m-d', strtotime(-$i.'days'));
            }
        }else{
            for($i = 13;$i >= 0;$i --) {
                $date[] = date('m-d', strtotime(-$i.'days'));
            } 
        }
        array_push($date,'游戏');
        if($this->role_type == 1){
            $gameKey = array('oneday','twoday','threeday','fourday','fiveday','sixday','sevenday','eightday','nineday','tenday','elevenday','twelveday','thirteenday','fourteenday','game');
        }else{
            // $gameKey = array('oneday','twoday','threeday','fourday','fiveday','sixday','sevenday','eightday','nineday','tenday','elevenday','twelveday','thirteenday','fourteenday','fifteen','sixteen','seventeen','eighteen','nineteen','twenty','twentyone','game');
            $gameKey = array('oneday','twoday','threeday','fourday','fiveday','sixday','sevenday','eightday','nineday','tenday','elevenday','twelveday','thirteenday','fourteenday','game');
        }
        
        //获取所有的游戏id,游戏名称
        $gameList = $GameInfoModel->getGameInfo('game_id,game_name',array());
        $date = array_combine($gameKey,$date);

        foreach ($gameList as $key => $value) {
            array_push($gameId,$value['game_id']);
            array_push($total,array('game_name'=>$value['game_name']));
        }

        //获取各个游戏指定日期的数据
        foreach ($gameId as $key => $value) {
            if($this->role_type == 1){
                for($i = 13;$i >= 0;$i --){
                    $startTime = strtotime(date('Ymd',strtotime(-($i).'days')));
                    // $endTime = strtotime(date('Ymd',strtotime(-($i-1).'days')));
                    //取出前七天的数据
                    $total[$key]['data'][] = (int)$GameRoundDayModel->gameRoundDayList($startTime,$value,'game_round_coins',$this->role_value)['game_round_coins'];
                }
            }else{
                for($i = 13;$i >= 0;$i --){
                    $startTime = strtotime(date('Ymd',strtotime(-($i).'days')));
                    // $endTime = strtotime(date('Ymd',strtotime(-($i-1).'days')));
                    //取出前七天的数据
                    $total[$key]['data'][] = (int)$GameRoundDayModel->gameRoundDayList($startTime,$value,'game_round_coins',$this->role_value)['game_round_coins'];
                }
            }
        }

        $dataTmp = array();
        $data = DataArrange::dataSortDesc($gameId,$total);
        foreach ($data['total'] as $key => $value){
            $dataTmp[$key] = $value['data'];
            array_push($dataTmp[$key],$value['game_name']);
        }
        foreach ($dataTmp as $key => $value){
            $total[$key] = array_combine($gameKey,$value);
        }
        array_unshift($total,$date);

        return $this->sendSuccess($total);
    }

    /**
     * @actionName  各游戏每日金币消耗表格
     */
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

        //获取各个游戏指定日期的数据
        foreach ($gameId as $key => $value) {
            for ($i = 6; $i >= 0; $i--) {
                $startTime = strtotime(date('Ymd', strtotime(-($i) . 'days')));
                $endTime = strtotime(date('Ymd', strtotime(-($i - 1) . 'days')));

                //取出前七天的数据
                $total[$key]['data'][] = (int)$GameRoundDayModel->gameRoundDay($startTime, $endTime, $value, 'game_round_num',$this->role_value)['game_round_num'];
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

    protected function getStatisticsWeekData($condition){
        $week = date('w',time());
        if($week == 0){
            $week = 7;
        }
        $dayArr = array(
            1=>'周一',
            2=>'周二',
            3=>'周三',
            4=>'周四',
            5=>'周五',
            6=>'周六',
            0=>'周天'
        );
        $days = array_values($dayArr);
        $daykeys = array_keys($dayArr);
        $valuesthis = array();
        $valueslast = array();
        $re = null;

        // $condition->start = $this->timestamp_day - ($week - 1) * 86400;

        $condition->start = date(mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y")));
        $condition->end = date(mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y")));
        $thisweek = StatisticsTotalModel::model()->getStatisticsDataDay($condition);

        // $condition->end = $condition->start;
        // $condition->start = $condition->start - 7 * 86400;
        $condition->start = date(mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y")));
        $condition->end = date(mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y")));

        $lastweek = StatisticsTotalModel::model()->getStatisticsDataDay($condition);

        $thisweek_init = array();
        if(!empty($thisweek)){
            foreach($thisweek as $key => $val){
                $kk = date('w',$val['statistics_timestamp']);
                if($condition->modeSet == self::MODE_CHARGE_SUM){
                    $thisweek_init[$kk] = $val['statistics_sum']/100;
                }else{
                   $thisweek_init[$kk] = $val['statistics_sum']; 
                }
                
            }
        }
        $lastweek_init = array();
        if(!empty($lastweek)){
            foreach($lastweek as $key => $val){
                $kk = date('w',$val['statistics_timestamp']);
                if($condition->modeSet == self::MODE_CHARGE_SUM){
                    $lastweek_init[$kk] = $val['statistics_sum']/100;
                }else{
                   $lastweek_init[$kk] = $val['statistics_sum']; 
                }
            }
        }

        foreach($daykeys as $val){
            array_push($valuesthis,isset($thisweek_init[$val]) ? $thisweek_init[$val] : 0);
            array_push($valueslast,isset($lastweek_init[$val]) ? $lastweek_init[$val] : 0);
        }

        $re = array('day'=>$days,'value'=>[$valuesthis,$valueslast]);
        return $re;
    }

    protected function getStatisticsData($condition){
        $re = null;
        if($condition->type == self::TYPE_ALL){
            $re = StatisticsTotalModel::model()->getStatisticsDataAll($condition);
        }

        if($condition->type == self::TYPE_DAY){
            $condition->start = $this->timestamp_day - (self::TYPE_DAY_LEN - 1) * 86400 ;
            $return = StatisticsTotalModel::model()->getStatisticsDataDay($condition);
            $re_init = array();
            if(!empty($return)){
                foreach($return as $key => $val){
                    $kk = date('m-d',$val['statistics_timestamp']);
                    $re_init[$kk] = $val['statistics_sum'];
                }
            }
            $days = array();
            $values = array();
            for($i = 1 ; $i <= self::TYPE_DAY_LEN ; $i++){
                $time = date('m-d',$this->timestamp_day - (self::TYPE_DAY_LEN - $i) * 86400);
                array_push($days,$time);
                array_push($values,isset($re_init[$time]) ? $re_init[$time] : 0);
            }
            $re = array('day'=>$days,'value'=>$values);
        }

        return $re;
    }

    protected function getStatisticsIncomeData($condition){
        $list = StatisticsTotalModel::model()->getStatisticsDataAllIncome($condition);
        $total = 0;
        if(!empty($list)){
            foreach ($list as $value) {
                if($value['statistics_money_rate'] > 0){
                    $total += $value['statistics_sum'] / $value['statistics_money_rate'];
                }
            }
        }
        return Helper::fomatMoneyData( $total );
    }

    protected function format_return_data($data,$mode){
        foreach($data as $key => $val){
            if($mode == self::MODE_CHARGE_SUM){
                $data[$key] = floatval(Helper::cutDataByLen( $val / 100 ));
            }
        }

        return $data;
    }
    
}
