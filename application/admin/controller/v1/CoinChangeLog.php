<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-02-06 19:55:58
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 金币变化日志
 +---------------------------------------------------------- 
 */

namespace app\admin\controller\v1;

use app\admin\controller\Controller;
use app\admin\model\AgentInfoModel;
use app\admin\model\AgentsStatisticsPlayerModel;
use app\admin\model\PlayerModel;
use app\common\components\Helper;

/**
 * @controllerName 金币变化日志
 */
class CoinChangeLog extends Controller
{
	protected $error_code = 0;
	protected $error_msg = '';
	protected $size = 10 ;
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
    }

    protected function initRequestParam(){
		$time = time();
		$timestamp_day = strtotime(date('Y-m-d',$time));
		$input = array(
			'start'     =>$this->request->get('start'),
			'end'       =>$this->request->get('end'),
			'keyword'   =>(string)$this->request->get('keyword'),// 玩家ID/昵称
			'channel'	=>(int)$this->request->get('channel'),// 渠道ID
			'starid'	=>(int)$this->request->get('starid'),//星级推广员player_id，二级联动
			'page'      =>(int)$this->request->get('page'),
			'size'		=>(int)$this->request->get('size'),
            'game_id'   =>(int)$this->request->get('game_id'),
			'type'      =>(int)$this->request->get('type'), // 0-全部，1-服务费消耗，2-游戏盈利，3-游戏失利，4-系统赠送，5-充值获得
            'turnpage'  =>(int)$this->request->get('turnpage')
    	);
        /*
    	$input = array(
			'start'     =>'2017/2/11',//2017/2/11
			'end'       =>'2018/2/11',//2018/2/11
			'keyword'   =>'',// 玩家ID  1081557
			'page'      =>1,
			'channel'	=>'1',
			'starid'	=>'',
			'size'		=>'10',
            'type'      =>0,
            'turnpage'  =>0
    	);
        */
    	// 时间
    	$filter = array();

		$start = strtotime($input['start']);
        $end = strtotime($input['end']);
        $time = time();
        $timestamp_day = strtotime(date('Y-m-d', $time));

		if($start && $end){
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
            if(!$start && !$end){
                $filter['start'] = $timestamp_day;
                $filter['end'] = $filter['start'] + 86400;
            }
		}

        if($input['turnpage']){
            $filter['turnpage'] = $input['turnpage'];
        }else{
            $filter['turnpage'] = 0;
        }
    	
    	if($input['keyword'] !== ''){
    		$filter['keyword'] = $input['keyword'];
    	}

    	if($input['channel']){
    		$filter['channel'] = $input['channel'];
    	}

        if($input['game_id']){
            $filter['game_id'] = $input['game_id'];
        }

    	if($input['starid']){
    		$filter['starid'] = $input['starid'];
    	}

        if($input['type']){
            $filter['type'] = $input['type'];
        }else{
            $filter['type'] = 0;
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

    	if($this->error_code){
    		return false;
    	}else{
	    	$filter = (object)$filter;
    		// 玩家ID 、 昵称筛选
    		if(isset($filter->keyword) && $filter->keyword){
    			$ret_arr = $this->checkPlayerExists($filter);
    			if(!is_bool($ret_arr) && empty($ret_arr)){
    				$this->returnCode(10001,'没有符合条件的玩家信息');
		        	return false;
    			}else{
                    if(!$ret_arr){
                        return false; 
                    }
    				$filter->keyword = $ret_arr;
    			}
    		}
    		// 校验该推广员是否属于该渠道
    		if(isset($filter->channel) && isset($filter->starid)){
    			$info = $this->checkStarAgentBelongs($filter);
    			if(!$info){
    				$this->returnCode(10002,'推广员不属于该渠道');
		        	return false;
    			}else{
    				$filter->starid = $info['agent_id'];
    			}
    		}
    			 
    		return $filter;
    	}
	}

    /**
     * @actionName 玩家金币变化日志
     */
	public function playerChangeLog(){
		$condition = $this->initRequestParam();
    	if($condition === false){
    		return $this->sendError($this->error_code, $this->error_msg);
    	}
        
    	$list = AgentsStatisticsPlayerModel::model()->getCoinChangeList($condition);
    	$playerid = array();
    	$superid = array();
    	$starid = array();
    	foreach($list->items() as $item){
    		array_push($playerid,$item->change_money_player_id);
    		array_push($superid,$item->change_money_super_agents_id);
    		array_push($starid,$item->change_money_parent_agents_id);
    	}
		$playerid = array_unique($playerid);
		$superid  = array_unique($superid);
		$starid   = array_unique($starid);// agent_id
		$channelInfo_init = array();
		$playerInfo_init  = array();
		$starInfo_init  = array();
		// 玩家昵称ID
		$playerInfo = PlayerModel::model()->getPlayerinfoById($playerid,['player_id,player_nickname']);
    	foreach($playerInfo as $val) {
    		$playerInfo_init[$val['player_id']] = $val;
    		$playerInfo_init[$val['player_id']]['player_nickname'] = urldecode($val['player_nickname']);
    	}
    	// 渠道商名称
    	$channelInfo = AgentInfoModel::model()->getByAgentId($superid,['agent_id,agent_name']);
		foreach($channelInfo as $val) {
    		$channelInfo_init[$val['agent_id']] = $val;
    	}
    	// 星级推广员
    	$starInfo = AgentInfoModel::model()->getStarAgentPlayerInfoById($starid,['agent_id,player_id,player_nickname,agent_login_status']);
		foreach($starInfo as $val) {
    		$starInfo_init[$val['agent_id']] = $val;
    	}

    	foreach($list->items() as $item){
    		if(isset($channelInfo_init[$item->change_money_super_agents_id])){
	    		$item->change_money_super_name = $channelInfo_init[$item->change_money_super_agents_id]['agent_name'];
	    	}else{
    			$item->change_money_super_name = '';
    		}
    		if(isset($playerInfo_init[$item->change_money_player_id])){
    			$item->change_money_player_nickname = $playerInfo_init[$item->change_money_player_id]['player_nickname'];
    		}else{
    			$item->change_money_player_nickname = '';
    		}

    		if(isset($starInfo_init[$item->change_money_parent_agents_id]) && $starInfo_init[$item->change_money_parent_agents_id]['agent_login_status']){
    			$item->change_money_parent_nickname  = urldecode($starInfo_init[$item->change_money_parent_agents_id]['player_nickname']);
                $item->change_money_parent_agents_id = $starInfo_init[$item->change_money_parent_agents_id]['player_id'];
    		}else{
    			$item->change_money_parent_nickname  = '';
                $item->change_money_parent_agents_id = '';
    		}
            if($item->change_money_parent_agents_id == $item->change_money_super_agents_id){
                $item->change_money_parent_agents_id = '';
            }
            $item->change_money_tax = (int)$item->change_money_tax;
    		switch($item->change_money_type)
    		{
    			case 1:
    				$item->change_money_type = '充值';
    				break;
    			case 2:
    				$item->change_money_type = '游戏消耗(包括服务费)';
    				break;
    			case 3:
    				$item->change_money_type = '单扣服务费';
    				break;
    			case 4:
    				$item->change_money_type = '新用户注册赠送';
    				break;
                case 8:
                    $item->change_money_type = '新手礼包';
                    break;
                case 9:
                    $item->change_money_type = '系统添加';
                    break;
    			default:
    				$item->change_money_type = '未知';
    				break;
    		}
    		if(!$item->change_money_club_desk_id) 
    			$item->change_money_club_desk_id = '--';
    		if(!$item->change_money_room_name) 
    			$item->change_money_room_name = '--';
    		if(!$item->change_money_game_name) 
    			$item->change_money_game_name = '--';
    	}

        // 时间
        $filterDate = $this->addTime($condition);
        // 服务费消耗总计
        if($condition->turnpage){
            $total_tax = 0;
        }else{
            $total_tax = [];
            $total_tax['change_money_tax'] = AgentsStatisticsPlayerModel::model()->getCoinChangeSum($condition,1);
            $total_tax['change_money_value'] = AgentsStatisticsPlayerModel::model()->getCoinChangeSum($condition,2);
        }
        
    	return $this->sendSuccess(['list' => $list,'total_tax'=>$total_tax,'date'=>$filterDate]);
	}

	protected function checkPlayerExists($condition){
        $size = $condition->size;
        unset($condition->size);
    	$playerInfo = PlayerModel::model()->getPlayerinfoByLike($condition);
        if(count($playerInfo) > $this->max_search_num){
            $this->returnCode(10001, $this->max_search_nitice);
            return false;
        }
    	$ids = array();
    	if(count($playerInfo) > 0){
    		foreach($playerInfo as $item){
    			array_push($ids,$item['player_id']);
    		}
    	}
        $condition->size = $size;
    	return array_unique($ids);
    }

	protected function checkStarAgentBelongs($filter){
		$re = AgentInfoModel::model()->isBelongsToSuperAgent($filter);
		if($re){
			return $re;
		}else{
			return false;
		}
	}

	protected function returnCode($code,$error){
		$this->error_code = $code;
		$this->error_msg  = $error;
    }

    protected function addTime($condition = null){
        $ret_time = [];
        if(isset($condition->start)){
            $ret_time['start_date'] = date('Y-m-d',$condition->start);
        }
        if(isset($condition->end)){
            $ret_time['end_date'] = date('Y-m-d',$condition->end - 86400);
        }
        return $ret_time;
    }
}