<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-01-26 11:37:10
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 代理收益
 +---------------------------------------------------------- 
 */
namespace app\api\controller\v1;

use app\api\controller\Controller;
use app\api\model\AgentIncomeDetailModel;
use app\api\model\AgentIncomePromotesModel;
use app\api\model\PlayerModel;
use app\api\model\AgentIncomeDetailPlayerModel;
use app\common\components\Helper;
use app\api\model\AgentInfoModel;


class Income extends Controller
{
	protected $error_code = 0;
	protected $error_msg = '';
	protected $size = 10 ;

    /**
     * 初始化操作
     * @access protected
     */
    protected function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->isLogin($this->token);
    }

	public function initRequestParam(){
		$time = time();
		$timestamp = strtotime(date('Y-m-d',$time));
		$input = array(
			'start'     =>$this->request->get('start'),
			'end'       =>$this->request->get('end'),
			'player_id' =>(string)$this->request->get('keyword'),// 玩家ID
			'range'     =>(int)$this->request->get('range'),// 1-昨天，2-本周，3-本月
			'page'      =>(int)$this->request->get('page'),
			'level'		=>(int)$this->request->get('level'),
            'type'      =>(int)$this->request->get('type') // 1-点击区间，2-点击搜索
    	);
        /*
    	$input = array(
			'start'     =>'2018/1/20',
			'end'       =>'2018/1/29',
			'player_id' =>'',// 玩家ID  1078114
			'range'     =>'',// 1-昨天，2-本周，3-本月
			'page'      =>1,
			'level'		=>2,
            'type'      =>1
    	);
        */
    	// 时间
    	$filter = array();

    	if($input['range'] && $input['type'] === 1){
    		switch($input['range']){
    			case 1:
    				$filter['end'] = $timestamp;
    				$filter['start'] = $filter['end'] - 86400;
    				break;
    			case 2:
    				$weekday = date('w',$time) > 0 ? date('w',$time) : 7;//0-日，1-一
    				$filter['end'] = $timestamp + 86400;
    				$filter['start'] = $filter['end'] - 86400 * ($weekday);
    				break;
    			case 3:
    				$day = date('j',$time);
    				$filter['end'] = $timestamp + 86400;
    				$filter['start'] = $filter['end'] - 86400 * ($day);
    				break;
    			default:
    				$this->returnCode(10001,'请选择合适的时间区间，只能查看今天之前的记录');
    				break;
    		}
    	}else{
    		$start = strtotime($input['start']);
    		$end   = strtotime($input['end']);
			if($start && $end && $start <= $end){
				$start = strtotime(date('Y-m-d',$start));
				$end   = strtotime(date('Y-m-d',$end));
				if($start > $timestamp){
					$this->returnCode(10001,'请选择合适的时间区间，只能查看今天之前的记录');
                    return false;
				}
				$filter['start'] = $start;
				if($end > $timestamp){
					$filter['end']   = $timestamp + 86400 ;
				}elseif($end == $timestamp){
					$filter['end']   = $end + 86400;
				}elseif($end < $timestamp){
                    $filter['end']   = $end + 86400;
                }
			}elseif(!$start && !$end){
                $filter['start'] = $timestamp;
                $filter['end'] = $filter['start'] + 86400;
            }else{
				$this->returnCode(10002,'请选择合适的时间区间');
                return false;
			}
    	}
    	if($input['player_id'] !== ''){
    		$filter['player_id'] = $input['player_id'];
    	}
    	
    	if($input['page']){
    		$filter['page'] = $input['page'];
    	}else{
    		$filter['page'] = 1;
    	}
    	if($input['level'] && !in_array($input['level'],[2,3])){
    		$this->returnCode(10005,'请选择合适的代理');
            return false;
    	}else{
    		$filter['level'] = $input['level'];
    	}
    	if($this->error_code){
    		return false;
    	}else{
    		$filter['agent_id'] = $this->userInfo['agent_info']['agent_id'];
	    	$filter['size'] = $this->size;
            $filter['range'] = $input['range'] ? $input['range'] : 0;
	    	$filter = (object)$filter;
    		return $filter;
    	}
	}
	/**
	 * 代理个人收益
	 * @return [type] [description]
	 */
    public function myIncome(){
    	$condition = $this->initRequestParam();
    	if(!$condition){
    		return $this->sendError($this->error_code, $this->error_msg);
    	}

    	// 收益列表
		$list     = AgentIncomeDetailModel::model()->getListByPage($condition);
        
		foreach ($list->items() as $item) {
            $item->player_id = $item->statistics_player_id;
            // 玩家昵称
            $info = PlayerModel::model()->getPlayerinfoById($item->statistics_player_id);
            if($info){
                $item->player_nickname = urldecode($info['player_nickname']);
            }else{
                $item->player_nickname = '';
            }
            // 玩家服务费
            $item->statistics_income = Helper::cutDataByLen($item->statistics_income / 100);
            // 分成占比
            if($item->statistics_share_money_low == $item->statistics_share_money_high || $item->statistics_share_money_high == 0){
            	$item->statistics_share_money_rate = (string)$item->statistics_share_money_low . '%';
            }else{
            	$item->statistics_share_money_rate = (string)$item->statistics_share_money_low . '%-' . (string)$item->statistics_share_money_high . '%';
            }
            // 我的收益
            $item->statistics_my_income = Helper::cutDataByLen($item->statistics_my_income / 100);
            unset($item->statistics_share_money_low);
            unset($item->statistics_share_money_high);
            unset($item->statistics_player_id);
        }
        // 收益统计-个人收益,一级，二级，暂时只能按时间筛选
        $income_arr = $this->getIncomeArr($condition);
        
        // &start=2018-1-20&end=2018-3-1
        return $this->sendSuccess(['list' => $list,'income' => $income_arr , 'date' => $this->add_time($condition)]);
    }

    /**
	 * 代理个人收益明细--保留三个月
	 * @return [type] [description]
	 */
    public function myIncomeDetail(){
    	$condition = $this->initRequestParam();
    	if(!$condition){
    		return $this->sendError($this->error_code, $this->error_msg);
    	}
    	// 收益总数--以金币数表示
    	$income_total = $this->getIncomeArr($condition,1,2);

        // 玩家昵称，id 检索---暂不支持
        // if(is_numeric($condition->player_id) && $condition->player_id){
        //     $player_arr = $this->filterPlayerInfo($condition);
        //     if(!empty($player_arr)){
        //        $condition->player_arr = $player_arr;
        //     }
        // }
        
    	// 收益列表--支持 游戏名称，玩家id 检索
		$list  = AgentIncomeDetailPlayerModel::model()->getListDetailPlayerByPage($condition);

        $playerids = array();
        $players_init = array();
        foreach ($list->items() as $item) {
            array_push($playerids,$item->change_money_player_id);
        }
        $playerids = array_unique($playerids);
        $players = PlayerModel::model()->getPlayerinfoById($playerids);
        foreach($players as $val){
            $players_init[$val['player_id']] = $val['player_nickname'];
        }
		foreach ($list->items() as $item) {
            $item->player_nickname         = urldecode($players_init[$item->change_money_player_id]);
            $item->change_money_player_id  = $item->change_money_player_id;
            $item->change_money_tax        = Helper::cutDataByLen($item->change_money_tax / 100);
            $item->change_money_my_tax     = Helper::cutDataByLen($item->change_money_my_tax / 100);
            $item->change_money_game_name  = $item->change_money_game_name;
            $item->change_money_share_rate = (string)$item->change_money_share_rate . '%';
            $item->change_money_date       = $item->change_money_date;
        }
        return $this->sendSuccess(['list' => $list,'income' => $income_total, 'date' => $this->add_time($condition)]);
    }

    /**
     * 下级代理产生的收益列表，选择一级还是二级代理
     * @return [type] [description]
     */
    public function agentIncome(){
    	$condition = $this->initRequestParam();
    	if(!$condition){
    		return $this->sendError($this->error_code, $this->error_msg);
    	}
        if(!$condition->level){
            $condition->level = 2;// 2是一级，3是二级
        }

        // 检索代理的player_id，不支持昵称检索
        if(isset($condition->player_id) && $condition->player_id){
            $agents = $this->filterAgentByPlayerId($condition);
            $agentids = [];
            if($agents){
                foreach($agents as $v){
                    array_push($agentids,$v['agent_id']);
                }
            }
            $condition->from_agent_id = $agentids;
        }

    	$list = AgentIncomePromotesModel::model()->getAgentIncome($condition);

    	foreach ($list->items() as $item) {
            // 我的分成占比
            if($item->statistics_share_money_low == $item->statistics_share_money_high || $item->statistics_share_money_high == 0){
            	$item->share_money_rate = (string)$item->statistics_share_money_low . '%';
            }else{
            	$item->share_money_rate = (string)$item->statistics_share_money_low . '%-' . (string)$item->statistics_share_money_high . '%';
            }
            // 我的收益
            $item->my_income = Helper::cutDataByLen($item->my_income / 100);
            // 昵称，id
            $agentinfo = AgentInfoModel::model()->getInfo(['agent_id'=>$item->statistics_from_value]);
            if($agentinfo){
                $info = PlayerModel::model()->getPlayerinfoById($agentinfo['agent_player_id']);
                if($info){
                    $item->agent_name = urldecode($info['player_nickname']);
                }else{
                    $item->agent_name = '';
                }
                $item->agent_player_id = $agentinfo['agent_player_id'];
            }else{
                $item->agent_name = '';
                $item->agent_player_id = 0;
            }

            // 该一代下玩家服务费--时间区间
            $item->sub_player_tax_total = Helper::cutDataByLen( AgentIncomeDetailModel::model()->getSubPlayerTax($condition,$item->statistics_from_value) );
            unset($item->statistics_share_money_low);
            unset($item->statistics_share_money_high);
            unset($item->statistics_from_value);
        }
    	
    	// 收益统计-个人收益,一级，二级，暂时只能按时间筛选
        $income_arr = $this->getIncomeArr($condition);
        return $this->sendSuccess(['list' => $list,'income' => $income_arr, 'date' => $this->add_time($condition)]);
    }

    /**
     * 统计代理收益
     * @param  [type]  $condition [description]
     * @param  integer $type      货币类型：1-元，2-金币数
     * @param  integer $level     [description]
     * @return [type]             [description]
     */
    public function getIncomeArr($condition,$level = 0,$type = 1){
    	if($level && in_array($level,[1,2,3])){
    		$income = AgentIncomePromotesModel::model()->getIncomeArrSub($condition,$level,$type);
    		return Helper::cutDataByLen( $income / 100 );
    	}
    	if(!$level){
    		$incomeMy     = AgentIncomePromotesModel::model()->getIncomeArrSub($condition,1,$type);
			$incomeSubOne = AgentIncomePromotesModel::model()->getIncomeArrSub($condition,2,$type);
			$incomeSubTwo = AgentIncomePromotesModel::model()->getIncomeArrSub($condition,3,$type);
			$total        = $incomeMy + $incomeSubOne + $incomeSubTwo;
	    	return array(
				'incomeMy'     =>Helper::cutDataByLen( $incomeMy / 100 ),
				'incomeSubOne' =>Helper::cutDataByLen( $incomeSubOne / 100 ),
				'incomeSubTwo' =>Helper::cutDataByLen( $incomeSubTwo / 100 ),
				'total'        =>Helper::cutDataByLen( $total / 100 )
	    	);
    	}
    }

    public function returnCode($code,$error){
		$this->error_code = $code;
		$this->error_msg  = $error;
    }

    public function cutDataByLen($data,$len = 2){
        $pows = pow(10,$len);
        $temp = $data * $pows;
        $temp = (int)$temp;
        $data = $temp / $pows;
        $data = $data ? (string)$data : '0.00';
        return sprintf("%.2f",$data);
    }

    public function add_time($condition = null){
        $time = time();
        if($condition->range){
            $ret_time = array(
                'start_date' => date('Y-m-d',$condition->start),
                'end_date'   =>date('Y-m-d',$condition->end - 86400)
            );
            if($condition->range == 2){
                $weekday = date('w',$time) > 0 ? date('w',$time) : 7;//0-日，1-一
                if($weekday == 1){
                    $ret_time['end_date'] = $ret_time['start_date'] ;
                }
            }elseif($condition->range == 3){
                $day = date('j',$time);
                if($day == 1){
                    $ret_time['end_date'] = $ret_time['start_date'] ;
                }
            }
        }else{
            $ret_time = array(
                'start_date' =>'',
                'end_date'   =>''
            );
            if(isset($condition->start) && $condition->start){
                $ret_time['start_date'] = date('Y-m-d',$condition->start);
            }
            if(isset($condition->end) && $condition->end){
                $ret_time['end_date'] = date('Y-m-d',$condition->end - 86400);
            }
        }

        return $ret_time;
    }

    public function filterPlayerInfo($condition){
        // print_r($condition);
        $condition->keyword = $condition->player_id;
        $size = $condition->size;
        unset($condition->size);
        $info = PlayerModel::model()->getPlayerByCondition($condition,['player_id']);
        $condition->size = $size;
        return $info;
    }

    public function filterAgentByPlayerId($condition){
        $condition->keyword = $condition->player_id;
        $size = $condition->size;
        unset($condition->size);
        if($condition->level == 2){
            $condition->agent_parentid = $condition->agent_id;
        }
        if($condition->level == 3){
            $condition->agent_p_parentid = $condition->agent_id;
        }
        $condition->is_login = 1;
        $info = AgentInfoModel::model()->getAgentInfoByCondition($condition,['agent_id']);
        $condition->size = $size;
        unset($condition->agent_p_parentid);
        unset($condition->agent_parentid);
        unset($condition->is_login);
        return $info;
    }

}