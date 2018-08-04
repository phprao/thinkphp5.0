<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-02-02 17:56:19
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 系统管理
 +---------------------------------------------------------- 
 */
namespace app\admin\controller\v1;

use app\admin\controller\Controller;
use app\admin\model\PlayerModel;
use app\admin\model\PlayerInfoModel;
use app\admin\model\AgentInfoModel;
use app\admin\redis\PlayerRedis;
use app\admin\model\SysCoinChangeLog;
use app\admin\model\AgentIncomeConfigModel;
use app\admin\model\UsersActionLogModel;
use app\admin\model\PromotersInfoModel;
use app\admin\model\StatisticsTotalModel;
use app\admin\model\ChangeMoneyInfoModel;
use think\Db;
use think\Log;

/**
 * @controllerName 系统管理
 */

class SystemManage extends Controller
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
    }

    /**
     * @actionName  修改玩家金币数
     */
    public function changeCoin(){
    	// http://localhost/dc_api_u3d_king/public/admin/v1/system_manage/change_coin?token=11d18d632632eb2a3ce0fbf43e07b999&player_id=1103107&coin=-10&type=1&remark=aaa
    	
    	$playerId = $this->request->get('player_id');
    	$playerCoin = $this->request->get('coin');
    	$type = $this->request->get('type');
    	$remark = $this->request->get('remark');
    	if(!is_numeric($playerId) || !is_numeric($playerCoin) || !is_numeric($type)){
    		return $this->sendError(10000, '参数错误');
    	}

    	if(!$remark){
    		return $this->sendError(10000, '请填写备注');
    	}

    	$info = PlayerModel::model()->getInfo(['player_id'=>$playerId]);
    	$detail = PlayerInfoModel::model()->getOne(['player_id'=>$playerId]);
        $agent = AgentInfoModel::model()->getInfo(['agent_player_id'=>$playerId]);
    	if(!$info || !$detail){
    		return $this->sendError(10001, '该玩家不存在');
    	}
    	if($info['player_status'] == 0){
    		return $this->sendError(10001, '该玩家已被禁用');
    	}
        if(!$agent){
            return $this->sendError(10001, '该玩家代理信息不存在');
        }
        Log::error('[ changeCoin ] : 修改玩家金币数 | '.$playerId.','.$playerCoin.','.$remark);
    	$exist = PlayerRedis::exists($playerId);

    	if($exist){
			$before_coin = PlayerRedis::hget($playerId, 'player_coins');
			$result  = PlayerRedis::hincrby($playerId, 'player_coins', $playerCoin);
            $after_coin = $before_coin + $playerCoin;
    	}else{
            $before_coin = $detail['player_coins'];
    		$result = PlayerInfoModel::model()->where(['player_id'=>$playerId])->setInc('player_coins', $playerCoin);
            $after_coin = $before_coin + $playerCoin;
    	}

    	if($result !== false){
    		$data = [
    			'player_id'=>$playerId,
                'action_user_id'=>$this->userInfo['userInfo']['id'],
    			'action_user'=>$this->userInfo['userInfo']['user_login'],
    			'before_coin'=>$before_coin,
    			'modified_coin'=>$playerCoin,
    			'after_coin'=>$after_coin,
    			'type'=>$type,
                'channel_id'=>$agent['agent_top_agentid'],
    			'remark'=>$remark,
    			'add_time'=>time(),
    			'add_date'=>date('Y-m-d H:i:s'),
    		];
    		$re = SysCoinChangeLog::model()->save($data);
            // 添加日志
            ChangeMoneyInfoModel::model()->addLogByPlayerId([
                'change_money_player_id'   => $playerId,
                'change_money_begin_value' => $before_coin,
                'change_money_money_value' => $playerCoin,
                'change_money_after_value' => $after_coin,
                'change_money_type'        => 9,// 系统添加
                'change_money_money_type'  => ChangeMoneyInfoModel::CHANG_MONEY_MONEY_TYPE_GOLD
            ]);
    		if($re){
    			return $this->sendSuccess(['before_coin'=>(int)$before_coin, 'after_coin'=>$after_coin]);
    		}else{
    			if($exist){
    				PlayerRedis::hdecrby($playerId, 'player_coins', $playerCoin);
    			}else{
    				PlayerInfoModel::model()->where(['player_id'=>$playerId])->setDec('player_coins', $playerCoin);
    			}
    			return $this->sendError(10003, '日志写入失败');
    		}
    		
    	}else{
    		return $this->sendError(10003, '操作失败');
    	}

    	
    }

    /**
     * @actionName  升级玩家为星级
     */
    public function becomeStar(){
        $playerId = $this->request->get('player_id');
        if(!is_numeric($playerId)){
            return $this->sendError(10000, '参数错误');
        }

        $info = PlayerModel::model()->getInfo(['player_id'=>$playerId]);
        $detail = PlayerInfoModel::model()->getOne(['player_id'=>$playerId]);
        if(!$info || !$detail){
            return $this->sendError(10001, '该玩家不存在');
        }
        if($info['player_status'] == 0){
            return $this->sendError(10001, '该玩家已被禁用');
        }
        $agent = AgentInfoModel::model()->getInfo(['agent_player_id'=>$playerId]);
        if(!$agent){
            return $this->sendError(10001, '该玩家agent不存在');
        }
        if($agent['agent_login_status']){
            return $this->sendError(10001, '该玩家已是星级推广员');
        }
        $config = AgentIncomeConfigModel::model()->getAll(['income_agent_id'=>$agent['agent_id']]);
        $is_set = true;
        if(!$config){
            $is_set = false;
            $config = AgentIncomeConfigModel::model()->getAll(['income_agent_id'=>$agent['agent_top_agentid']]);
            if(!$config){
                $config = AgentIncomeConfigModel::model()->getAll(['income_agent_id'=>0]);  
            }
            if(!$config){
                return $this->sendError(10002, '星级推广员分成占比没有系统配置');
            }
        }
        Db::startTrans();
        // 改变状态
        $r1 = AgentInfoModel::model()->save(['agent_login_status'=>1,'agent_login_status_man'=>1,'agent_star_time'=>time()], ['agent_player_id'=>$playerId]);
        // 改变分成占比
        if($is_set){
            $r2 = AgentIncomeConfigModel::model()->save(
                ['income_promote_count'=>1], 
                ['income_agent_id'=>$agent['agent_id'], 'income_count_level'=>1]
            );
        }else{
            $config_init = [];
            foreach ($config as $key => $val) {
                if ($key == 0) {
                    $config[0]['income_promote_count'] = 1;
                }
                $config[$key]['income_agent_id'] = $agent['agent_id'];

                $config_init[$key]['income_agent_id']      = $config[$key]['income_agent_id'];
                $config_init[$key]['income_promote_count'] = $config[$key]['income_promote_count'];
                $config_init[$key]['income_count_level']   = $config[$key]['income_count_level'];
                $config_init[$key]['income_agent']         = $config[$key]['income_agent'];
                $config_init[$key]['income_level_one']     = $config[$key]['income_level_one'];
                $config_init[$key]['income_level_two']     = $config[$key]['income_level_two'];
                $config_init[$key]['income_level_three']   = $config[$key]['income_level_three'];
                $config_init[$key]['income_remark']        = $config[$key]['income_remark'];
            }
            $r2 = AgentIncomeConfigModel::model()->setIncomeConfig($config_init);
        }
        // 写入操作日志
        $action_type  = UsersActionLogModel::ACTION_MODIFY;
        $action_name  = '将玩家升级为星级';
        $action_after = json_encode(['player_id'=>$playerId]);
        $r3 = UsersActionLogModel::model()->addActionLog($this->userInfo['userInfo']['id'], $action_type, $action_name, '', $action_after);

        if($r1 && $r2 !== false && $r3){
            Db::commit();
            return $this->sendSuccess();
        }else{
            Db::rollback();
            return $this->sendError(10001, '写入失败！');
        }
    }

    /**
     * @actionName  转移玩家
     */
    public function transferChannel(){
        set_time_limit(0);
        $playerId  = $this->request->get('player_id');
        $channelId = $this->request->get('channel_id');
        $remark    = $this->request->get('remark');
        if(!is_numeric($playerId) || !is_numeric($channelId)){
            return $this->sendError(10000, '参数错误');
        }
        $playerId  = (int)$playerId;
        $channelId = (int)$channelId;
        if(!$remark){
            return $this->sendError(10000, '请填写备注');
        }

        $channel = AgentInfoModel::model()->getInfo(['agent_id'=>$channelId, 'agent_player_id'=>0, 'agent_level'=>1, 'agent_status'=>1]);
        if(empty($channel)){
            return $this->sendError(10001, '该渠道不存在或已禁用');
        }

        $info = PlayerModel::model()->getInfo(['player_id'=>$playerId]);
        $detail = PlayerInfoModel::model()->getOne(['player_id'=>$playerId]);
        if(!$info || !$detail){
            return $this->sendError(10001, '该玩家不存在');
        }
        if($info['player_status'] == 0){
            return $this->sendError(10001, '该玩家已被禁用');
        }
        $agent = AgentInfoModel::model()->getInfo(['agent_player_id'=>$playerId]);
        if(!$agent){
            return $this->sendError(10001, '该玩家agent不存在');
        }
        if($agent['agent_top_agentid'] == $channelId){
            return $this->sendError(10001, '该玩家已属于该渠道');
        }
        // if($agent['agent_top_agentid'] != 1){
        //     return $this->sendError(10001, '该玩家已属于其他渠道');
        // }
        if($agent['agent_parentid'] != $agent['agent_top_agentid']){
            return $this->sendError(10001, '该玩家已属于其他玩家');
        }
        Db::startTrans();
        $re = $this->doTransfer($playerId, $channelId, $remark, $agent, 1);

        if($re){
            Db::commit();
            return $this->sendSuccess();
        }else{
            Db::rollback();
            return $this->sendError(10001, '写入失败！');
        }

    }

    protected function doTransfer($playerId, $channelId, $remark, $agent, $level){
        if($level == 1){
            $agentData = ['agent_parentid'=>$channelId, 'agent_top_agentid'=>$channelId];
            $promoterData = ['promoters_agent_parentid'=>$channelId, 'promoters_agent_top_agentid'=>$channelId];
        }else{
            $agentData = ['agent_top_agentid'=>$channelId];
            $promoterData = ['promoters_agent_top_agentid'=>$channelId];
        }
        if($agent['agent_p_parentid'] == $agent['agent_top_agentid']){
            $agentData['agent_p_parentid'] = $channelId;
        }
        $re1 = AgentInfoModel::model()->save($agentData, ['agent_player_id'=>$playerId]);
        $re2 = PromotersInfoModel::model()->save($promoterData, ['promoters_player_id'=>$playerId]);
        // 写入操作日志
        $action_type  = UsersActionLogModel::ACTION_MODIFY;
        $action_name  = '将玩家转换渠道';
        $action_before = json_encode(['player_id'=>$playerId, 'channel_id'=>$agent['agent_top_agentid']]);
        $action_after  = json_encode(['player_id'=>$playerId, 'channel_id'=>$channelId, 'remark'=>$remark]);
        $re3 = UsersActionLogModel::model()->addActionLog($this->userInfo['userInfo']['id'], $action_type, $action_name, $action_before, $action_after);
        if($re1 === false || $re2 === false || !$re3){
            return false;
        }else{
            $sub = AgentInfoModel::model()->where(['agent_parentid'=>$agent['agent_id']])->select();
            if(empty($sub)){
                return true;
            }
            foreach($sub as $val){
                $re4 = $this->doTransfer($val['agent_player_id'], $channelId, $remark, $val, 2);
                if(!$re4){
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @actionName  注销玩家
     */
    public function cancelPlayer(){
        $playerId  = $this->request->get('player_id');
        if(!is_numeric($playerId)){
            return $this->sendError(10000, '参数错误');
        }
        $playerId  = (int)$playerId;
        $info = PlayerModel::model()->getInfo(['player_id'=>$playerId]);
        if(!$info){
            return $this->sendError(10001, '该玩家不存在');
        }
        if(strpos($info['player_unionid'], PlayerModel::$cancel_remark) !== false){
            return $this->sendError(10001, '该玩家已被注销');
        }
        $player_unionid = $info['player_unionid'] . PlayerModel::$cancel_remark;
        $re = PlayerModel::model()->save(['player_unionid'=>$player_unionid], ['player_id'=>$playerId]);
        if($re){
            return $this->sendSuccess();
        }else{
            return $this->sendError(10001, '操作失败');
        }
    }

    /**
     * @actionName  取消注销
     */
    public function activePlayer(){
        $playerId  = $this->request->get('player_id');
        if(!is_numeric($playerId)){
            return $this->sendError(10000, '参数错误');
        }
        $playerId  = (int)$playerId;
        $info = PlayerModel::model()->getInfo(['player_id'=>$playerId]);
        if(!$info){
            return $this->sendError(10001, '该玩家不存在');
        }
        if(strpos($info['player_unionid'], PlayerModel::$cancel_remark) === false){
            return $this->sendError(10001, '该玩家未被注销');
        }
        $player_unionid = str_replace(PlayerModel::$cancel_remark, '', $info['player_unionid']);
        $re = PlayerModel::model()->save(['player_unionid'=>$player_unionid], ['player_id'=>$playerId]);
        if($re){
            return $this->sendSuccess();
        }else{
            return $this->sendError(10001, '操作失败');
        }

    }

    /**
     * @actionName  设置用户的redis
     */
    public function changeLottery(){
        //    system_manage/change_lottery?player_id=1111101&lottery=100

        $playerId  = $this->request->get('player_id');
        $playerCoin  = $this->request->get('lottery');

        if(!is_numeric($playerId) || !is_numeric($playerCoin)){
            return $this->sendError(10000, '参数错误');
        }

        $info = PlayerModel::model()->getInfo(['player_id'=>$playerId]);
        $detail = PlayerInfoModel::model()->getOne(['player_id'=>$playerId]);
        $agent = AgentInfoModel::model()->getInfo(['agent_player_id'=>$playerId]);
        if(!$info || !$detail){
            return $this->sendError(10001, '该玩家不存在');
        }
        if($info['player_status'] == 0){
            return $this->sendError(10001, '该玩家已被禁用');
        }
        if(!$agent){
            return $this->sendError(10001, '该玩家代理信息不存在');
        }
        
        $exist = PlayerRedis::exists($playerId);

        if($exist){
            $before_coin = PlayerRedis::hget($playerId, 'player_lottery');
            $result  = PlayerRedis::hincrby($playerId, 'player_lottery', $playerCoin);
            $after_coin = $before_coin + $playerCoin;
        }else{
            $before_coin = $detail['player_lottery'];
            $result = PlayerInfoModel::model()->where(['player_id'=>$playerId])->setInc('player_lottery', $playerCoin);
            $after_coin = $before_coin + $playerCoin;
        }

        if($result !== false){
            // $data = [
            //     'player_id'=>$playerId,
            //     'action_user_id'=>$this->userInfo['userInfo']['id'],
            //     'action_user'=>$this->userInfo['userInfo']['user_login'],
            //     'before_coin'=>$before_coin,
            //     'modified_coin'=>$playerCoin,
            //     'after_coin'=>$after_coin,
            //     'type'=>$type,
            //     'channel_id'=>$agent['agent_top_agentid'],
            //     'remark'=>$remark,
            //     'add_time'=>time(),
            //     'add_date'=>date('Y-m-d H:i:s'),
            // ];
            // $re = SysCoinChangeLog::model()->save($data);
            // 添加日志
            // ChangeMoneyInfoModel::model()->addLogByPlayerId([
            //     'change_money_player_id'   => $playerId,
            //     'change_money_begin_value' => $before_coin,
            //     'change_money_money_value' => $playerCoin,
            //     'change_money_after_value' => $after_coin,
            //     'change_money_type'        => 9,// 系统添加
            //     'change_money_money_type'  => ChangeMoneyInfoModel::CHANG_MONEY_MONEY_TYPE_GOLD
            // ]);
            
            $re = true;
            if($re){
                return $this->sendSuccess(['before_lottery'=>(int)$before_coin, 'after_lottery'=>$after_coin]);
            }else{
                if($exist){
                    PlayerRedis::hdecrby($playerId, 'player_lottery', $playerCoin);
                }else{
                    PlayerInfoModel::model()->where(['player_id'=>$playerId])->setDec('player_lottery', $playerCoin);
                }
                return $this->sendError(10003, '日志写入失败');
            }
            
        }else{
            return $this->sendError(10003, '操作失败');
        }

        
    }



}