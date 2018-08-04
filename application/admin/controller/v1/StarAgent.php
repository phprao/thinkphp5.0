<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-02-02 17:56:19
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 星级推广员管理
 +---------------------------------------------------------- 
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
use app\admin\model\PlayerModel;
use app\admin\model\PlayerInfoModel;
use app\admin\model\PlayerStatisticalModel;
use app\admin\model\AgentIncomeConfigModel;
use think\Db;
/**
 * @controllerName 星级推广员管理
 */
class StarAgent extends Controller
{
    protected $size = 10 ;
    protected $error_code = 0;
    protected $error_msg = '';
    protected $max_search_num = 50;
    protected $max_search_nitice = '请再精确一下关键词';

	protected function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->isLogin($this->token);
        if(isset($this->userInfo['agentInfo'])){
            $this->login_channel_id = $this->userInfo['agentInfo']['agent_id'];
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
            'start'     =>'',//2017-10-1
            'end'       =>'',//2018-2-2
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
     * @actionName 渠道下的星级推广员列表
     * @return [type] [description]
     */
    public function getStarAgentList(){
    	$SuperAgentId = (int)$this->request->get('channel');
        $is_promote = (int)$this->request->get('is_promote');

        $agentid = '';
        $loginData = $this->isLogin($this->request->get('token'));
        if (isset($loginData['agentInfo'])) {
            $agentid = $loginData['agentInfo']['agent_id'];
        }

        if($agentid){
            $SuperAgentId = $agentid;
        }else{
            $SuperAgentId = $SuperAgentId;
        }

        if($is_promote == 1){
            // 星级和非星级都检索
            $star = false;
        }else{
            $star = true;
        }
    	if(!$SuperAgentId){
    		return $this->sendError(10000, '非法请求');
    	}
    	$list = AgentInfoModel::model()->getStarAgentListByTopId($SuperAgentId,$star);
    	foreach($list as $val){
    		$val->player_nickname = urldecode($val->player_nickname);
    	}
    	return $this->sendSuccess(['list' => $list]);
    }

    /**
     * @actionName 星级推广员列表-详情
     * @return [type] [description]
     */
    public function starAgentList()
    {
        $condition = $this->initRequestParam();
        if ($condition === false) {
            return $this->sendError($this->error_code, $this->error_msg);
        }

        $mode       = (int)$this->request->get('mode');
        $type       = (int)$this->request->get('type');
        $channel_id = (int)$this->request->get('channel_id');

        if($this->login_channel_id > 0){
            $channel_id = $this->login_channel_id;
        }

        // $channel_id = 1;
        // $mode       = 4;
        // $type       = 1;
        // $condition->keyword = '';

        $mode_arr = [
            0, // 不选择
            1, // 星级推广员
            2, // 直接推广人
            3, // 间接推广人
            4  // 渠道
        ];
        $type_arr = [
            1, // 渠道
            2  // 星级推广员,玩家
        ];

        // 跳转来源信息
        $ext = [];

        if($channel_id && $mode != 4){
            $has_ext = true;
            $condition->from_channel_id = $channel_id;
            $superInfo = AgentInfoModel::model()->getInfo(['agent_id'=>$channel_id,'agent_parentid'=>0,'agent_level'=>1]);
            if($superInfo){
                $ext['agent_id'] = $superInfo['agent_id'];
                $ext['agent_name'] = $superInfo['agent_name'];
            }else{
                return $this->sendError(10000, '该渠道不存在');
            }
        }else{
            $has_ext = false;
        }

        if($mode && !in_array($mode,$mode_arr)){
           return $this->sendError(10003, 'mode参数错误'); 
        }
        if(in_array($mode,[2,3]) && !$type){
            return $this->sendError(10003, 'type参数错误');
        }
        if($type && !in_array($type,$type_arr)){
           return $this->sendError(10003, 'type参数错误'); 
        }

        ////////////////////////////////////////////////////////////////////////////

        $ret_arr = '';
        if(isset($condition->keyword) && $condition->keyword){
            if($mode == 1){
                // player_id
                $ret_arr = $this->checkStarAgentExists($condition);
                $msg = '没有符合条件的星级推广员信息';
            }elseif($mode == 2 || $mode == 3){
                if($type == 1){
                    // 检索渠道 agent_id
                    $ret_arr = $this->checkSuperAgentExists($condition);
                    $msg = '没有符合条件的渠道信息';
                }
                if($type == 2){
                    // player_id
                    $ret_arr = $this->checkPlayerExists($condition);
                    $msg = '没有符合条件的玩家信息';
                    // 将Player_id装换成agent_id
                    if(!empty($ret_arr)){
                        $ainfo = AgentInfoModel::model()->getByAgentPlayerId($ret_arr,['agent_id']);
                        $ret_arr = [];
                        foreach($ainfo as $val){
                            array_push($ret_arr,$val['agent_id']);
                        }
                    }
                }
            }elseif($mode == 4){
                // 渠道下的星级推广员列表  agent_id
                $ret_arr = $this->checkSuperAgentExists($condition);
                $msg = '没有符合条件的渠道信息';
            }elseif(isset($condition->from_channel_id)){
                // player_id
                $ret_arr = $this->checkStarAgentExists($condition);
                $msg = '没有符合条件的星级推广员信息';
            }else{
                // player_id   只有keyword
                $mode = 5;
                $ret_arr = $this->checkStarAgentExists($condition);
                $msg = '没有符合条件的星级推广员信息';
            }

            if (($mode || isset($condition->from_channel_id)) && empty($ret_arr) && !is_bool($ret_arr)) {
                return $this->sendError(10002, $msg);
            } else {
                if(!$ret_arr){
                    return $this->sendError(10002, $this->error_msg ? $this->error_msg : '搜索错误'); 
                }
                $condition->keyword = $ret_arr;
            }

            $condition->mode = $mode;
        }else{
            if($mode > 0){
                return $this->sendError(10002, '请输入关键字检索');
            }
        }

        ///////////////////////////////////////////////////////////////////

        if(isset($condition->start) && !$condition->start){
            unset($condition->start);
        }
        if(isset($condition->end) && !$condition->end){
            unset($condition->end);
        }

        $fields = ['agent_id','agent_player_id','agent_parentid','agent_p_parentid','agent_top_agentid','agent_createtime','agent_promote_count','agent_star_time'];
        $list = AgentInfoModel::model()->getStarAgentInfoByCondition($condition,$fields);
        if($list){
            foreach($list->items() as $item){
                // 昵称  id
                $player = PlayerModel::model()->getPlayerinfoById($item->agent_player_id);
                $item->nickname = urldecode($player['player_nickname']);    
                // 手机号
                $item->mobile = urldecode($player['player_phone']);
                // 所属渠道
                $item->channel_id = $item->agent_top_agentid;
                unset($item->agent_top_agentid);
                $channel = AgentInfoModel::model()->getInfo(['agent_id'=>$item->channel_id]);
                $item->channel_name = $channel ? $channel['agent_name'] : '';
                // 直接推广人
                if($item->agent_p_parentid == 0){
                    // 直接推广人为渠道
                    $item->dir_suggest_id = $item->channel_id;
                    $item->dir_suggest_name = $item->channel_name;
                }else{
                    // 直接推广人为玩家（包括星级和玩家）
                    $info1 = AgentInfoModel::model()->getInfo(['agent_id'=>$item->agent_parentid]);
                    if(!$info1){
                        $item->dir_suggest_id = '';
                        $item->dir_suggest_name = '';
                    }else{
                        $player1 = PlayerModel::model()->getPlayerinfoById($info1['agent_player_id']);
                        $item->dir_suggest_id = $player1['player_id'];
                        $item->dir_suggest_name = urldecode($player1['player_nickname']);
                    }
                }
                // 间接推广人
                if($item->agent_p_parentid == 0){
                    // 直接推广人为渠道，间接推广人则不存在
                    $item->indir_suggest_id = '';
                    $item->indir_suggest_name = '';
                }else{
                    $info2 = AgentInfoModel::model()->getInfo(['agent_id'=>$item->agent_p_parentid]);
                    if(!$info2){
                        $item->indir_suggest_id = '';
                        $item->indir_suggest_name = '';
                    }else{
                        // 可能是特代或其他
                        if($info2['agent_player_id'] == 0){
                            $item->indir_suggest_id = $info2['agent_id'];
                            $item->indir_suggest_name = $info2['agent_name'];
                        }else{
                            $player2 = PlayerModel::model()->getPlayerinfoById($info2['agent_player_id']);
                            $item->indir_suggest_id = $player2['player_id'];
                            $item->indir_suggest_name = urldecode($player2['player_nickname']); 
                        }
                        
                    }
                }
                // 直属玩家总数
                $item->dir_player_num = $item->agent_promote_count;
                // $item->dir_player_num =AgentInfoModel::model()->getCountAgent(['agent_parentid'=>$item->agent_id]);
                // 直属推荐总数
                $item->first_agent_num =AgentInfoModel::model()->getCountAgent(['agent_login_status'=>1,'agent_parentid'=>$item->agent_id]);
                // 从属推荐总数
                $item->second_agent_num =AgentInfoModel::model()->getCountAgent(['agent_login_status'=>1,'agent_p_parentid'=>$item->agent_id]);
                // 金币消耗分成比例-最新的比例
                $item->share_rate = $this->getIncomeRateAgent($item->agent_id);
                // 直属玩家剩余金币数--定时统计
                $param = [
                    'statistics_role_type'=>2,
                    'statistics_role_value'=>$item->agent_player_id,
                    'statistics_mode'=>8,// 8-剩余金币数
                    'statistics_type'=>3
                ];
                $last_coin = StatisticsTotalModel::model()->getOne($param);
                if($last_coin){
                    $item->dir_player_coin_sum = Helper::fomatBigData($last_coin['statistics_sum']);
                }else{
                    $item->dir_player_coin_sum = 0;
                }
                // 加入时间
                if($item->agent_star_time){
                    $item->agent_createtime = date('Y-m-d H:i:s',$item->agent_star_time);
                }else{
                    $item->agent_createtime = '--';
                }
            }
        }
        $filterDate = $this->addTime($condition);
        
        return $this->sendSuccess(['list' => $list,'ext'=>$ext,'date' => $filterDate]);
    }
    /**
     * @actionName 批量修改配置
     */
    public function setConfigAll(){
        $input = [
            'channel_id'    => $_POST['channel_id'],
            'income_remark' => (string)$this->request->post('income_remark'),
            'channel_all'   => (int)$this->request->post('channel_all')
        ];
        /*
        $input = [
            'channel_id'    => [1,2,3],
            'income_remark' => '测试-all',
            'channel_all'   => 1
        ];
        */
        
        if(empty($input['channel_id']) || !is_array($input['channel_id'])){
            return $this->sendError(10000, 'channel_id参数错误');
        }

        // 系统默认配置不能在此处修改
        if(array_search(0,$input['channel_id']) !== false){
            return $this->sendError(10000, '系统默认配置不能在此处修改');
        }
        
        $agent = [];
        foreach($input['channel_id'] as $val){
            $info = AgentInfoModel::model()->getInfo(['agent_id'=>$val]);
            if(!$info){
                return $this->sendError(10000, "channel_id={$val}不存在");
            }else{
                array_push($agent,$info['agent_id']);
            }
        }

        Db::startTrans();

        // 配置项处理
        $re = true;
        /*
        $confArrs = [
            [10,35,10,5],
            [20,37,11,6],
            [30,39,12,7],
            [50,40,13,8],
            [100,42,13,8],
            [200,44,14,9],
            [300,45,15,10]
        ];
        $_POST['config'] = $confArrs;
        */
        $confArr_init = [];
        if(isset($_POST['config'])){
            $confArr = $_POST['config'];
            foreach($agent as $v){
                foreach($confArr as $key => $val){
                    if($val[0] !== '' && $val[1] !== '' && $val[2] !== '' && $val[3] !== ''){
                        $temp = ['income_agent_id'=>$v,'income_promote_count'=>$val[0],'income_count_level'=>$key+1,'income_agent'=>$val[1],'income_level_one'=>$val[2],'income_level_two'=>$val[3],'income_level_three'=>0,'income_remark'=>$input['income_remark']];
                        array_push($confArr_init,$temp);
                    }
                }
            }
            if(!empty($confArr_init)){
                AgentIncomeConfigModel::model()->whereIn('income_agent_id',$agent)->delete();
                $re = AgentIncomeConfigModel::model()->saveAll($confArr_init);
            }
        }else{
            AgentIncomeConfigModel::model()->whereIn('income_agent_id',$agent)->delete();
        }

        if($re){
            Db::commit();
            return $this->sendSuccess();
        }else{
            Db::rollback();
            return $this->sendError(10001, '写入失败！');
        }
    }
    /**
     * @actionName 获取单个渠道配置
     */
    public function getConfigChannelOne(){
        // 渠道id
        $channel_id = (int)$this->request->get('channel_id');
        if($channel_id > 0){
            $info = AgentInfoModel::model()->getInfo(['agent_id'=>$channel_id]);
            if(!$info){
                return $this->sendError(10000, '渠道不存在');
            }
        }

        $conf = AgentIncomeConfigModel::model()->where(['income_agent_id'=>$channel_id])->select();
        if(!$conf){
            $conf = AgentIncomeConfigModel::model()->where(['income_agent_id'=>0])->select();
            if(!$conf){
               return $this->sendError(10000, '系统默认配置不存在');
            }
        }
        $config = [];
        if($conf){
            foreach($conf as $key => $val){
                $temp = [$val['income_promote_count'],$val['income_agent'],$val['income_level_one'],$val['income_level_two']];
                array_push($config,$temp);
            }
        }
        $list['income_remark'] = $conf[0]['income_remark'];
        return $this->sendSuccess(['list' => $list,'config'=>$config]);
    }
    /**
     * @actionName 修改单个配置
     */
    public function setConfigOne(){
        $input = [
            'player_id'     => (int)$this->request->post('player_id'),
            'player_phone'  => (string)$this->request->post('player_phone'),
            'agent_remark' => (string)$this->request->post('player_remark')
        ];
        /*
        $input = [
            'player_id'     => 1055015,
            'player_phone'  => '15978945612',
            'agent_remark' => '测试'
        ];
        */
        if(!$input['player_id']){
            return $this->sendError(10000, 'player_id参数错误');
        }
        
        if($input['player_phone'] && !Helper::checkString($input['player_phone'],3)){
            return $this->sendError(10000, '手机号格式错误');
        }
        
        $info = AgentInfoModel::model()->getInfo(['agent_player_id'=>$input['player_id'],'agent_login_status'=>1]);
        if(!$info){
            return $this->sendError(10000, 'player_id不是星级推广员');
        }
        $playerinfo = PlayerModel::model()->getInfo(['player_id'=>$input['player_id']]);
        if(!$playerinfo){
            return $this->sendError(10000, 'player_id存在');
        }

        $agent = $info['agent_id'];

        Db::startTrans();
        
        $re11 = PlayerModel::model()->updateData(['player_id'=>$input['player_id']],['player_phone'=>$input['player_phone']]);
        $re12 = AgentInfoModel::model()->save(['agent_remark'=>$input['agent_remark']], ['agent_player_id'=>$input['player_id']]);
        if($re11 !== false && $re12 !== false) 
            $re1 = true;
        else 
            $re1 = false;

        // 配置项处理
        if($re1){
            /*
            $confArrs = [
                [10,35,10,5],
                [20,37,11,6],
                [30,39,12,7],
                [50,40,13,8],
                [100,42,13,8],
                [200,44,14,9],
                [300,45,15,10]
            ];
            $_POST['config'] = $confArrs;
            */
            $confArr_init = [];
            if(isset($_POST['config'])){
                $confArr = $_POST['config'];
                foreach($confArr as $key => $val){
                    if($val[0] && $val[1] && $val[2] && $val[3]){
                        $temp = [
                            'income_agent_id'      =>$agent,
                            'income_promote_count' =>$val[0],
                            'income_count_level'   =>$key + 1,
                            'income_agent'         =>$val[1],
                            'income_level_one'     =>$val[2],
                            'income_level_two'     =>$val[3],
                            'income_level_three'   =>0,
                            'income_remark'        =>''
                        ];
                        array_push($confArr_init,$temp);
                    }
                }
                if(!empty($confArr_init)){
                    AgentIncomeConfigModel::model()->whereIn('income_agent_id',$agent)->delete();
                    $re2 = AgentIncomeConfigModel::model()->saveAll($confArr_init); 
                }
            }else{
                AgentIncomeConfigModel::model()->where(['income_agent_id'=>$agent])->delete();
                $re2 = true;
            }
        }else{
            $re2 = false;
        }

        if($re1 && $re2){
            Db::commit();
            return $this->sendSuccess();
        }else{
            Db::rollback();
            return $this->sendError($this->error_code, $this->error_msg);
        }
    }

    /**
     * @actionName 获取单个配置
     */
    public function getConfigOne(){
        // 星级推广员的agent_id
        $agent_id = (int)$this->request->get('agent_id');
        if(!$agent_id){
            return $this->sendError(10000, 'agent_id参数错误');
        }
        $info = AgentInfoModel::model()->getInfo(['agent_id'=>$agent_id,'agent_login_status'=>1]);
        if(!$info){
            return $this->sendError(10000, 'agent_id不是星级推广员');
        }

        $playerinfo = PlayerModel::model()->getInfo(['player_id'=>$info['agent_player_id']]);
        if(!$playerinfo){
            return $this->sendError(10000, '玩家不存在');
        }

        $agent = $agent_id;
        $list = [
            'player_phone'=>$playerinfo['player_phone'],
            'agent_remark'=>'',
            'player_id'=>$info['agent_player_id']
        ];
        $conf = AgentIncomeConfigModel::model()->where(['income_agent_id'=>$agent])->select();
        if(!$conf){
            $conf = AgentIncomeConfigModel::model()->where(['income_agent_id'=>$info['agent_top_agentid']])->select();
            if(!$conf){
               $conf = AgentIncomeConfigModel::model()->where(['income_agent_id'=>0])->select(); 
            }
        }
        $config = [];
        if($conf){
            foreach($conf as $key => $val){
                $temp = [$val['income_promote_count'],$val['income_agent'],$val['income_level_one'],$val['income_level_two']];
                array_push($config,$temp);
            }
        }
        $list['agent_remark'] = $info['agent_remark'];
        return $this->sendSuccess(['list' => $list,'config'=>$config]);
    }

    /**
     * 星级推广员当前的来自直属玩家的分成比例
     * @param  [type] $agent_id [description]
     * @return [type]           [description]
     */
    protected function getIncomeRateAgent($agent_id){
        // 查找推广的总人数
        $info = AgentInfoModel::model()->getInfo(['agent_id'=>$agent_id]);
        if(!$info){
            return 0;
        }
        $re = AgentIncomeConfigModel::model()->getShareRate($info);

        if($re){
            return $re['income_agent'] . '%';
        }else{
            return 0;
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
     * 检索星级推广员信息
     * @param  string $condition
     * @return [type]          [description]
     */
    protected function checkStarAgentExists($condition)
    {
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
                $starInfo = AgentInfoModel::model()->getByAgentPlayerId($item['player_id'],['agent_id'],['agent_login_status'=>1]);
                if($starInfo){
                    array_push($ids, $item['player_id']);
                }
            }
        }
        $condition->size = $size;
        return array_unique($ids);
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
        if(count($superInfo) > $this->max_search_num){
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

    /**
     * 渠道后台增加--获取星级推广员配置
     * @actionName 获取星级推广员配置
     */
    public function getChannelConfig(){
        $conf = AgentIncomeConfigModel::model()->where(['income_agent_id'=>$this->login_channel_id])->select();
        if(!$conf){
            $conf = AgentIncomeConfigModel::model()->where(['income_agent_id'=>0])->select();
        }
        $config = [];
        $list = [];
        if($conf){
            foreach($conf as $key => $val){
                $temp = [$val['income_promote_count'],$val['income_agent'],$val['income_level_one'],$val['income_level_two']];
                array_push($config,$temp);
            }
            $list['agent_name'] = $this->userInfo['agentInfo']['agent_name'];
        }
        return $this->sendSuccess(['list' => $list,'config'=>$config]);
    }

    /**
     * 渠道后台增加--设置星级推广员配置
     * @actionName 设置星级推广员配置
     */
    // public function setChannelConfig(){
    //     $input = [
    //         'income_remark' => (string)$this->request->post('player_remark')
    //     ];

    //     $isUpdate = true;
    //     $conf = AgentIncomeConfigModel::model()->where(['income_agent_id'=>$this->login_channel_id])->select();
    //     if(!$conf){
    //         $conf = AgentIncomeConfigModel::model()->where(['income_agent_id'=>0])->select();
    //         $isUpdate = false;
    //     }
    //     $conf_init = [];
    //     if(!$isUpdate){
    //         foreach($conf as $key => $val){
    //             $conf_init[$key]['income_remark']        = $input['income_remark'];
    //             $conf_init[$key]['income_agent_id']      = $this->login_channel_id;
    //             $conf_init[$key]['income_promote_count'] = $val['income_promote_count'];
    //             $conf_init[$key]['income_count_level']   = $val['income_count_level'];
    //             $conf_init[$key]['income_agent']         = $val['income_agent'];
    //             $conf_init[$key]['income_level_one']     = $val['income_level_one'];
    //             $conf_init[$key]['income_level_two']     = $val['income_level_two'];
    //             $conf_init[$key]['income_level_three']   = $val['income_level_three'];
    //         }
    //         $re = AgentIncomeConfigModel::model()->saveAll($conf_init);
    //     }else{
    //         $conf_init['income_remark'] = $input['income_remark'];
    //         $re = AgentIncomeConfigModel::model()->save($conf_init, ['income_agent_id'=>$this->login_channel_id]);
    //     }

    //     if($re !== false){
    //         return $this->sendSuccess();
    //     }else{
    //         return $this->sendError(10001, '写入失败');
    //     }
    // }

}