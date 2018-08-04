<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-02-02 17:56:19
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 玩家管理
 +---------------------------------------------------------- 
 */
namespace app\admin\controller\v1;

use app\admin\controller\Controller;
use app\admin\model\AgentInfoModel;
use app\common\components\Helper;
use app\admin\model\StatisticsTotalModel;
use app\admin\model\PlayerModel;
use app\admin\model\PlayerInfoModel;
use app\admin\model\PlayerStatisticalModel;
use app\admin\model\PartnerModel;
use think\Db;

class PromotePlayer extends Controller
{
    protected $size = 10 ;
    protected $error_code = 0;
    protected $error_msg = '';
    protected $max_search_num = 50;
    protected $max_search_nitice = '请再精确一下关键词';
    
	protected function _initialize()
    {
        parent::_initialize();
        $userInfo = $this->isLogin($this->token);
        if(isset($userInfo['agentInfo'])){
            $this->login_channel_id = $userInfo['agentInfo']['agent_id'];
        }else{
            $this->login_channel_id = 0;
        }
    }

    protected function initRequestParam(){
        $input = array(
            'keyword'   =>(string)$this->request->get('keyword'),
            'page'      =>(int)$this->request->get('page'),
            'size'      =>(int)$this->request->get('size'),
            'start'     =>(string)$this->request->get('start'),// 加入时间
            'end'       =>(string)$this->request->get('end')
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
        }else{
            if($start){
                $start = strtotime(date('Y-m-d', $start));
                if ($start > $timestamp_day) {
                    $this->returnCode(10001, '请选择合适的时间区间');
                    return false;
                }
                $filter['start'] = $start;
            }
            if($end){
                if ($end > $timestamp_day) {
                    $filter['end'] = $timestamp_day + 86400;
                } elseif ($end == $timestamp_day) {
                    $filter['end'] = $end + 86400;
                } elseif ($end < $timestamp_day) {
                    $filter['end'] = $end + 86400;
                }
            }
        }

        if($input['keyword'] !== ''){
            $filter['keyword'] = $input['keyword'];
        }

        if($input['page']){
            $filter['page'] = $input['page'];
        }else{
            $filter['page'] = 1;
        }

        if($input['size']){
            $filter['size'] = $input['size'];
        }else{
            $filter['size'] = $this->size;
        }

        
        if ($this->error_code) {
            return false;
        } else {
            $filter = (object)$filter;
            return $filter;
        }
        
    }

    /**
     * @actionName  玩家列表
     * @return [type] [description]
     */
    public function PlayerList()
    {
        $condition = $this->initRequestParam();
        if ($condition === false) {
            return $this->sendError($this->error_code, $this->error_msg);
        }

        $partner_id = (int)$this->request->get('partner_id');
        $parent_id  = (int)$this->request->get('parent_id');

        $ext = [];

        // 来源跳转
        if($parent_id && !$partner_id){
            $info = PartnerModel::model()->getOne(['partner_id'=>$parent_id]);
            if($info){
                $ext['partner_name'] = $info['partner_name'];
            }else{
                return $this->sendError(10002, '参数错误');
            }

            $partner_id = $parent_id;
        }
        if($partner_id){
            $info = PartnerModel::model()->getOne(['partner_id'=>$partner_id]);
            if(!$info){
                return $this->sendError(10002, '参数错误');
            }
            $condition->mode = 2;
            $condition->channel_id = $info['channel_id'];
        }else{
            $condition->mode = 3;
        }

        // 搜所筛选
        if (isset($condition->keyword) && $condition->keyword) {
            $ret_arr = $this->checkPlayerExists($condition);
            if (!is_bool($ret_arr) && empty($ret_arr)) {
                return $this->sendError(10002, '没有符合条件的玩家信息');
            } else {
                // player_id
                if(!$ret_arr){
                    return $this->sendError(10002, $this->error_msg ? $this->error_msg : '搜索错误'); 
                }
                $condition->keyword = $ret_arr;
            }
        }

        if(isset($condition->start) && !$condition->start){
            unset($condition->start);
        }
        if(isset($condition->end) && !$condition->end){
            unset($condition->end);
        }

        $list = AgentInfoModel::model()->getPlayerList($condition,['agent_id','agent_parentid','agent_player_id','agent_top_agentid','agent_createtime','agent_partner_id']);
        foreach($list->items() as $item){
            // 玩家昵称
            $player = PlayerModel::model()->getPlayerinfoById($item->agent_player_id);
            $item->nickname = urldecode($player['player_nickname']);
            // 玩家头像
            $playerinfo = PlayerInfoModel::model()->getOne(['player_id'=>$item->agent_player_id]);
            $item->headimgurl = $playerinfo['player_header_image'];
            $item->player_lottery = $playerinfo['player_lottery'];
            // 当前金币数
            $item->player_coins = Helper::fomatBigData($playerinfo['player_coins']);
            // 累计消耗
            $ps = playerStatisticalModel::model()->getOne(['statistical_player_id'=>$item->agent_player_id]);
            $item->cost_total = $ps ? Helper::fomatBigData($ps['statistical_value']) : 0;
            // 累计充值
            $ps = PlayerStatisticalModel::model()->getOne(['statistical_player_id'=>$item->agent_player_id]);
            $item->recharge_total = $ps ? Helper::fomatMoneyData($ps['statistical_top_up'] / 100) : 0;
            // 注册时间
            $item->agent_createtime = date('Y-m-d H:i:s',$item->agent_createtime);
            if($partner_id){
                $item->partner_name = $info['partner_name'];
            }else{
                $partner = PartnerModel::model()->getOne(['partner_id'=>$item->agent_partner_id]);
                if($partner){
                    $item->partner_name = $partner['partner_name'];
                }else{
                    $item->partner_name = '--';
                }
            }
            // 实名认证信息
            $item->identification_number = $player['player_identification_number'];
            $item->identification_name = $player['player_identification_name'];
            // 手机号
            $item->mobile = $player['player_phone'];
            // 玩家状态
            $item->player_status = $player['player_status'];
        }
        $filterDate = $this->addTime($condition);
        return $this->sendSuccess(['list' => $list,'ext'=>$ext,'date' => $filterDate]);
    }
    /**
     * @actionName  渠道玩家列表
     * @return [type] [description]
     */
    public function ChannelPlayerList(){
        $condition = $this->initRequestParam();
        if ($condition === false) {
            return $this->sendError($this->error_code, $this->error_msg);
        }
        $type = (int)$this->request->get('type');// 身份类型 0-全部，1-星级，2-普通
        if($type > 0){
            $condition->player_type = $type;
        }

        $condition->mode = 2;
        $condition->promote_id = $this->login_channel_id;

        // 搜所筛选
        if (isset($condition->keyword) && $condition->keyword) {
            $ret_arr = $this->checkPlayerExists($condition);
            if (!is_bool($ret_arr) && empty($ret_arr)) {
                return $this->sendError(10002, '没有符合条件的玩家信息');
            } else {
                // player_id
                if(!$ret_arr){
                    return $this->sendError(10002, $this->error_msg ? $this->error_msg : '搜索错误'); 
                }
                $condition->keyword = $ret_arr;
            }
        }

        if(isset($condition->start) && !$condition->start){
            unset($condition->start);
        }
        if(isset($condition->end) && !$condition->end){
            unset($condition->end);
        }

        // print_r($condition);

        $list = AgentInfoModel::model()->getPlayerList($condition,['agent_id','agent_parentid','agent_player_id','agent_top_agentid','agent_createtime','agent_login_status']);
        foreach($list->items() as $item){
            // 玩家昵称
            $player = PlayerModel::model()->getPlayerinfoById($item->agent_player_id);
            $item->nickname = urldecode($player['player_nickname']);
            // 玩家头像
            $playerinfo = PlayerInfoModel::model()->getOne(['player_id'=>$item->agent_player_id]);
            $item->headimgurl = $playerinfo['player_header_image'];
            $item->player_lottery = $playerinfo['player_lottery'];
            // 当前金币数
            $item->player_coins = Helper::fomatBigData($playerinfo['player_coins']);
            // 累计消耗
            $ps = playerStatisticalModel::model()->getOne(['statistical_player_id'=>$item->agent_player_id]);
            $item->cost_total = $ps ? Helper::fomatBigData($ps['statistical_value']) : 0;
            // 累计充值
            $ps = PlayerStatisticalModel::model()->getOne(['statistical_player_id'=>$item->agent_player_id]);
            $item->recharge_total = $ps ? Helper::fomatMoneyData($ps['statistical_top_up'] / 100) : 0;
            // 注册时间
            $item->agent_createtime = date('Y-m-d H:i:s',$item->agent_createtime);
            // 玩家状态
            $item->player_status = $player['player_status'];
            // 身份类型
            $item->player_type = $item->agent_login_status ? '星级推广员' : '普通玩家';
        }

        $filterDate = $this->addTime($condition);
        return $this->sendSuccess(['list' => $list, 'date' => $filterDate]);
    }
    /**
     * @actionName  加入黑名单
     * @return [type] [description]
     */
    public function addBlackList(){
        $player_id = (int)$this->request->post('player_id');
        $type = (int)$this->request->post('type');// 1-加入，2-解除
        if(!$player_id){
            return $this->sendError(10000,'player_id参数错误');
        }
        if(!$type || !in_array($type,[1,2])){
            return $this->sendError(10000,'type参数错误');
        }
        
        $player = PlayerModel::model()->getInfo(['player_id'=>$player_id]);
        if(!$player){
            return $this->sendError(10000,'该玩家不存在');
        }

        if($type == 1 && $player['player_status'] == 0){
            return $this->sendError(10001,'该玩家已在黑名单');
        }

        if($type == 2 && $player['player_status'] == 1){
            return $this->sendError(10001,'该玩尚不再黑名单');
        }

        if($type == 1)
            $status = 0;
        if($type == 2)
            $status = 1;

        $re = PlayerModel::model()->updateData(['player_id'=>$player_id],['player_status'=>$status]);
        if($re){
            return $this->sendSuccess();
        }else{
            return $this->sendError(10001,'操作失败');
        }
    }

    protected function returnCode($code, $error)
    {
        $this->error_code = $code;
        $this->error_msg = $error;
    }

    protected function addTime($condition = null)
    {
        $ret_time = [];
        if(isset($condition->start)){
            $ret_time['start_date'] = date('Y-m-d', $condition->start);
        }
        if(isset($condition->end)){
            $ret_time['end_date'] = date('Y-m-d', $condition->end - 86400);
        }
        return $ret_time;
    }

    /**
     * 检索玩家信息
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    protected function checkPlayerExists($condition){
        $size = $condition->size;
        unset($condition->size);
        $playerInfo = PlayerModel::model()->getPlayerinfoByLike($condition);
        if(count($playerInfo) > $this->max_search_num){
            $this->returnCode(10001, $this->max_search_nitice);
            return false;
        }
        $ids = array();
        if (count($playerInfo) > 0) {
            foreach ($playerInfo as $item) {
                array_push($ids, $item['player_id']);
            }
        }
        $condition->size = $size;
        return array_unique($ids);
    }

    
}