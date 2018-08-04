<?php
/**
 * +----------------------------------------------------------
 * date: 2018-03-19 10:41:43
 * +----------------------------------------------------------
 * author: liang jun bin
 * +----------------------------------------------------------
 * describe: 推广员列表
 * +----------------------------------------------------------
 */

namespace app\admin\controller\v1;

use app\admin\controller\Controller;
use app\admin\model\AgentInfoModel;
use app\admin\model\PlayerModel;
use app\admin\model\StatisticsTotalModel;
use app\admin\model\PlayerInfoModel;
use app\admin\model\PromotersInfoModel;
use app\admin\model\PlayerStatisticalModel;
use app\admin\model\PromotersawardconfigModel;
use app\admin\model\AgentConditionsModel;
use app\admin\model\AgentConfigModel;
use think\Db;

/**
 * @controllerName 推广员列表
 */
class Promote extends Controller
{

    /**
     * @actionName 推广员列表
     */
    public function index()
    {
        $loginData = $this->isLogin($this->request->get('token'));
        if (isset($loginData['agentInfo'])) {
            $agentid = $loginData['agentInfo']['agent_id'];
        }
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('pageSize', 10);
        $keyword = $this->request->get('keyword');

        if ($keyword) {
            $condition['keyword'] = $keyword;
        }
        if ($this->request->get('start_time') || $this->request->get('end_time')) {
            $time = $this->request->get('start_time');
            $end_time = $this->request->get('end_time');
            $condition['agent_createtime'][0] = array('EGT', strtotime($time));
            $condition['agent_createtime'][1] = array('LT', strtotime($end_time));
        }
        $condition['agent_login_status'] = 0;
        $condition['agent_promote_count'] = array('GT', 0);

        $agentinfo = AgentInfoModel::model()->getTpromoteEarnings($condition, $page, $pageSize);
        //分页
        $count = AgentInfoModel::model()->getTpromoteEarningsCount($condition);
        $datalist = [];
        foreach ($agentinfo as $value) {
            $playerid = $value['agent_player_id'];
            $contrastplayerid = $value['agent_player_id'];
            $agent_top_agentid = $value['agent_top_agentid'];
            $agent_parentid = $value['agent_parentid'];
            //ID
            $res['player_id'] = $value['agent_player_id'];
            //推广员名称
            $res['player_nickname'] = urldecode($value['player_nickname']);

            $channepid['agent_id'] = $agent_parentid;
            $thevalue_info = AgentInfoModel::model()->getAgentId($channepid);
            $thevalue_player = $thevalue_info['agent_player_id'];
            $inplayer['player_id'] = $thevalue_player;
            $infoplayer = PlayerModel::model()->getInfo($inplayer);

            //推广昵名
            $res['star_nickname'] = $infoplayer['player_nickname'] ? urldecode($infoplayer['player_nickname']) : '--';
            $res['star_nickname_status'] = $thevalue_info['agent_login_status'];

            //所属渠道
            $channelid['agent_id'] = $agent_top_agentid;
            $thevalue_channel = AgentInfoModel::model()->getAgentId($channelid);
            $channel[$playerid] = $thevalue_channel['agent_name'];
            $thevalue['channel'] = '';
            if (isset($channel[$contrastplayerid])) {
                $res['channel'] = $channel[$contrastplayerid];
            }
            //推广人数
            $res['agent_promote_count'] = $value['agent_promote_count'];

            //当前金币数
            $earnings['statistics_role_type'] = 2;
            $earnings['statistics_role_value'] = $playerid;
            $earnings['statistics_mode'] = 8;
            $earnings['statistics_type'] = 3;
            $playerinfo = StatisticsTotalModel::model()->getOne($earnings);
            $pearning[$playerid] = $playerinfo['statistics_sum'];
            $res['player_coins'] = 0;
            if (isset($pearning[$contrastplayerid])) {
                $res['player_coins'] = $pearning[$contrastplayerid];
            }


            //推广累积消耗金币
            $earnings['statistics_role_type'] = 2;
            $earnings['statistics_role_value'] = $playerid;
            $earnings['statistics_mode'] = 3;
            $earnings['statistics_type'] = 3;
            $player_earnings = StatisticsTotalModel::model()->getOne($earnings);
            $pearning[$playerid] = $player_earnings['statistics_sum'];
            $res['cumulative_consumption'] = 0;
            if (isset($pearning[$contrastplayerid])) {
                $res['cumulative_consumption'] = $pearning[$contrastplayerid];
            }

            //推广一次性奖励
            $rewardinfo = PlayerStatisticalModel::model()->getOne(array('statistical_player_id'=>$playerid));
            // $reward[$playerid] = $rewardinfo['statistical_award_money'];
            // $res['promote_reward'] = 0;
            // if (isset($reward[$contrastplayerid])) {
            //     $res['promote_reward'] = $reward[$contrastplayerid]/100;
            // }
            $res['promote_reward'] = $rewardinfo['statistical_award_money']/100;   
            $res['player_resigter_time'] = date('Y-m-d H:i:s', $value['player_resigter_time']);
            $datalist[] = $res;
        }

        $data = array(
            'total' => $count,
            'per_page' => $pageSize,
            'page' => $page,
            'last_page' => ceil($count / $pageSize),
            'list' => $datalist,
        );

        return $this->sendSuccess($data);

    }

    /**
     * @actionName 所属推广员列表
     *
     */
    public function promoteList()
    {
        $loginData = $this->isLogin($this->request->get('token'));
        if (isset($loginData['agentInfo'])) {
            $agentid = $loginData['agentInfo']['agent_id'];
        }
        $playerid = $this->request->get('playerid');
        if (!$playerid) {
            return $this->sendError(2000, 'playerid不能为空');
        }

        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('pageSize', 10);
        $keyword = $this->request->get('keyword');

        if ($keyword) {
            $condition['keywords'] = $keyword;
        }
        if ($this->request->get('start_time') || $this->request->get('end_time')) {
            $time = $this->request->get('start_time');
            $end_time = $this->request->get('end_time');
            $condition['player_resigter_time'][0] = array('EGT', strtotime($time));
            $condition['player_resigter_time'][1] = array('LT', strtotime($end_time));
        }

        $condition['promoters_parent_id'] = $playerid;
        $playershow = PromotersInfoModel::model()->getParentIdByPlayerInfo($condition, $page, $pageSize);
        //分页
        $count = PromotersInfoModel::model()->getParentIdByPlayerCount($condition);

        $pid = [];
        foreach ($playershow as $val) {
            $pid[] = $val['player_id'];
        }
        //玩家信息
        $playerinfo = PlayerInfoModel::model()->getList(array('player_id' => ['in', $pid]));
        $player_coins = [];
        $player_header_image = [];
        foreach ($playerinfo as $va) {
            $pinfid = $va['player_id'];
            $player_coins[$pinfid] = $va['player_coins'];
            $player_header_image[$pinfid] = $va['player_header_image'];
        }
        //累计消耗
        $PlayerStatisticalinfo = PlayerStatisticalModel::model()->getList(array('statistical_player_id' => ['in', $pid], 'statistical_type' => 1));
        $statistical_value = [];
        foreach ($PlayerStatisticalinfo as $items) {
            $sid = $items['statistical_player_id'];
            $statistical_value[$sid] = $items['statistical_value'];
        }
        //累计充值
        $PlayerStatisticalinfotype = PlayerStatisticalModel::model()->getList(array('statistical_player_id' => ['in', $pid], 'statistical_type' => 3));
        $statistical_type = [];
        foreach ($PlayerStatisticalinfotype as $itemt) {
            $tid = $itemt['statistical_player_id'];
            $statistical_type[$tid] = $itemt['statistical_value'];
        }

        $datalist = [];
        foreach ($playershow as $item) {

            $playid = $item['player_id'];
            $value['player_id'] = $item['player_id'];
            $value['player_nickname'] = urldecode($item['player_nickname']);

            //头像
            $value['player_header_image'] = '';
            if (isset($player_header_image[$playid])) {
                $value['player_header_image'] = $player_header_image[$playid];
            }
            //推广员
            $value['promoters'] = '';

            //金币数
            $value['player_coins'] = 0;
            if (isset($player_coins[$playid])) {
                $value['player_coins'] = $player_coins[$playid];
            }
            //累积充值
            $value['statistical_top'] = 0;
            if (isset($statistical_type[$playid])) {
                $value['statistical_top'] = $statistical_type[$playid];
            }

            //累积消耗
            $value['statistical_value'] = 0;
            if (isset($statistical_value[$playid])) {
                $value['statistical_value'] = $statistical_value[$playid];
            }

            $value['player_resigter_time'] = date('Y-m-d H:i:s', $item['player_resigter_time']);
            $datalist[] = $value;
        }

        $data = array(
            'total' => $count,
            'per_page' => $pageSize,
            'page' => $page,
            'last_page' => ceil($count / $pageSize),
            'list' => $datalist,
        );

        return $this->sendSuccess($data);
    }

    /**
     * @actionName 添加推广员设置
     */
    public function promoterscConfiguration()
    {
        $loginData = $this->isLogin($this->token);
        if (isset($loginData['agentInfo'])) {
            $agentid = $loginData['agentInfo']['agent_id'];
        }

        $consumption_gold = $this->request->post('consumption_gold');
        $reward           = $this->request->post('reward');
        $cumulative       = $this->request->post('cumulative');
        $cumulative_gold  = $this->request->post('cumulative_gold');

        if (!is_numeric($consumption_gold)) {
            return $this->sendError(2000, '推广消耗金币数参数错误');
        }
        if (!is_numeric($reward)) {
            return $this->sendError(2000, '获得奖励数额参数错误');
        }else{
            $reward = $reward * 100;
        }
        if (!is_numeric($cumulative)) {
            return $this->sendError(2000, '推广人数累计门槛参数错误');
        }
        if (!is_numeric($cumulative_gold)) {
            return $this->sendError(2000, '累积消耗金币参数错误');
        }

        $plist = PromotersawardconfigModel::model()->getOne(['award_agent_id'=>0]);
        $agentconfone = AgentConfigModel::model()->getAgentConfigOne(['agent_id'=>0]);

        Db::startTrans();

        if ($plist && $agentconfone) {

            $condition = [
                'award_condition' => $consumption_gold,
                'award_money'     => $reward
            ];
            $r1 = PromotersawardconfigModel::model()->updateAwardInfo(0, $condition);
        
            $data = [
                'agent_conditions_type' => 1,
                'agent_conditions_name' => '满' . $cumulative . '人且金币消耗满' . $cumulative_gold,
                'agent_conditions_data' => json_encode(['promote_number'=>$cumulative,'gold_consumption'=>$cumulative_gold])
            ];
            $r2 = AgentConditionsModel::model()->saveAgentConditionsId($agentconfone['agent_conditions_id'],$data);
            
        }else{
            if($plist){
                PromotersawardconfigModel::model()->delData(['award_agent_id'=>0]);
            }
            if($agentconfone){
                AgentConditionsModel::model()->delData(['agent_conditions_id'=>$agentconfone['agent_conditions_id']]);
            }
            $condition = [
                'award_condition' => $consumption_gold,
                'award_money'     => $reward,
                'award_agent_id'  =>0
            ];
            $r1 = PromotersawardconfigModel::model()->insertAward($condition);

            $data = [
                'agent_conditions_type' => 1,
                'agent_conditions_name' => '满' . $cumulative . '人且金币消耗满' . $cumulative_gold,
                'agent_conditions_data' => json_encode(['promote_number'=>$cumulative,'gold_consumption'=>$cumulative_gold])
            ];
            $r2 = AgentConditionsModel::model()->insertAgentConditions($data);
        }

        if($r1 !== FALSE && $r2 !== FALSE){
            Db::commit();
            return $this->sendSuccess();
        }else{
            Db::rollback();
            return $this->sendError(10001, '写入失败！');
        }

    }

    /**
     * @actionName 显示修改的配置参数
     */
    public function proomotersSaveinfo()
    {

        $loginData = $this->isLogin($this->request->get('token'));
        if (isset($loginData['agentInfo'])) {
            $agentid = $loginData['agentInfo']['agent_id'];
        }
        $listp['award_agent_id'] = 0;
        $plist = PromotersawardconfigModel::model()->getOne($listp);
        if (!$plist) {
            return $this->sendError(2000, '没有配置！');
        }
        $aconf['agent_id'] = 0;
        $agentconfing = AgentConfigModel::model()->getAgentConfigOne($aconf);
        if ($agentconfing) {
            $agentconditions['agent_conditions_id'] = $agentconfing['agent_conditions_id'];
            $agetnconinfo = AgentConditionsModel::model()->getAgentConditionsOne($agentconditions);
            $conflist = json_decode($agetnconinfo['agent_conditions_data'], true);
        }
        $data = array(
            'pid' => $plist['award_id'],
            'aid' => $agentconfing['agentconf_id'],
            'cid' => $agetnconinfo['agent_conditions_id'],
            'award_condition' => $plist['award_condition'] ? $plist['award_condition'] : 0,
            'award_money' => $plist['award_money'] ? $plist['award_money'] / 100 : 0,
            'promote_number' => $conflist['promote_number'] ? $conflist['promote_number'] : 0,
            'gold_consumption' => $conflist['gold_consumption'] ? $conflist['gold_consumption'] : 0,
        );
        return $this->sendSuccess($data);
    }

    /**
     * @actionName 修改推广员设置
     */
    public function promoterscSave()
    {
        $loginData = $this->isLogin($this->request->get('token'));
        if (isset($loginData['agentInfo'])) {
            $agentid = $loginData['agentInfo']['agent_id'];
        }
        $pid = $this->request->get('pid');
        $aid = $this->request->get('aid');
        $cid = $this->request->get('cid');

        if (!$pid || !$aid || !$cid) {
            return $this->sendError(2000, '未传ID');
        }
        $consumption_gold = $this->request->get('consumption_gold');
        $reward = $this->request->get('reward');
        $cumulative = $this->request->get('cumulative');
        $cumulative_gold = $this->request->get('cumulative_gold');

        $condition = [];
        if ($consumption_gold) {
            $condition['award_condition'] = $consumption_gold;
        }
        if ($reward) {
            $condition['award_money'] = $reward;
        }
        //修改dc_promoters_award_config表
        $datapromo = 0;
        if ($pid) {
            $datapromo = PromotersawardconfigModel::model()->savePromoters($pid, $condition);
        }
        $aconf['agent_conditions_id'] = $cid;
        $agentconfing = AgentConditionsModel::model()->getAgentConditionsOne($aconf);
        $conflist = json_decode($agentconfing['agent_conditions_data'], true);

        if ($cumulative) {
            $promote_number = $cumulative;
        } else {
            $promote_number = $conflist['promote_number'];
        }
        if ($cumulative_gold) {
            $gold_consumption = $cumulative_gold;
        } else {
            $gold_consumption = $conflist['gold_consumption'];
        }

        $json_data = array(
            'promote_number' => $promote_number,
            'gold_consumption' => $gold_consumption,
        );
        $json_encode = json_encode($json_data, true);
        //修改dc_agent_conditions 表
        $agentcondtion['agent_conditions_data'] = $json_encode;
        $acid = AgentConditionsModel::model()->saveAgentConditionsId($cid, $agentcondtion);

        if ($datapromo || $acid) {
            return $this->sendSuccess($acid);
        } else {
            return $this->sendError(2000, '设置失败！');
        }

    }


}