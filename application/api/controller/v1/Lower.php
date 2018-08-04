<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-03-13 16:16:15
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 下级管理
 +---------------------------------------------------------- 
 */

namespace app\api\controller\v1;

use app\api\controller\Controller;
use app\api\model\AgentInfoModel;
use app\api\model\PlayerInfoModel;
use app\api\model\PlayerModel;
use app\api\model\AgentAccountInfoModel;

/**
 * Class Lower
 * @package app\api\controller\v1
 * @author ChangHai Zhan
 */
class Lower extends Controller
{
    /**
     * 我推广的玩家列表,包括已经开放代理后台权限的玩家。
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\exception\DbException
     */
    public function userList()
    {
        $userInfo = $this->isLogin($this->token);
        $condition = new \stdClass();
        $condition->keyword = $this->request->get('keyword');
        $condition->agent_parentid = $userInfo['agent_info']['agent_id'];
        $models = PlayerModel::model()->getAgentPlayerList($condition);
        $playerIds = [];
        foreach ($models->items() as $v) {
            $playerIds[$v->player_id] = $v->player_id;
        }
        $modelsPlayInfo = PlayerInfoModel::model()->getPlayerInfoById($playerIds, ['player_id', 'player_coins']);
        $playerInfoData = [];

        foreach ($modelsPlayInfo as $modelPlayerInfo) {
            $playerInfoData[$modelPlayerInfo->player_id] = $modelPlayerInfo->player_coins_text;
        }
        foreach ($models->items() as $item) {
            $item->player_status = $item->player_status_text;
            $item->player_resigter_time = date('Y-m-d H:i:s',$item->player_resigter_time);
            $item->player_name = urldecode($item->player_nickname);
            $item->player_coins = 0;
            if (isset($playerInfoData[$item->player_id])) {
                $item->player_coins = $playerInfoData[$item->player_id];
            }
            unset($item->player_nickname);
        }
        return $this->sendSuccess(['list' => $models]);
    }

    /**
     * 我的代理
     */
    public function agentList()
    {
        $userInfo = $this->isLogin($this->token);
        // 我的agent_id
        $agentId = $userInfo['agent_info']['agent_id'];
        $id = input('id/d', null);
        $condition = new \stdClass();
        $condition->keyword = $this->request->get('keyword');
        //默认
        $response = ['list' => []];
        if ($id === null) {
            //我的一级推广列表
            $condition->agent_parentid = $agentId;
        } elseif ($id === 0) {
            //我的二级推广列表
            $condition->agent_p_parentid = $agentId;
        } elseif ($id) {
            // 该推广ID下级推广
            $modelAgent = AgentInfoModel::model()->isLowerAgent($id, $agentId, ['agent_id', 'agent_player_id']);
            if(!$modelAgent){
                return $this->sendError(2001, '推广ID 不是有效的');
            }else{
                $playerinfo = PlayerModel::model()->getPlayerinfoById($modelAgent->agent_player_id);
                $condition->agent_parentid = $modelAgent->agent_id; 
                $response['agentInfo']['agent_id']        = $playerinfo['player_id'];
                $response['agentInfo']['player_nickname'] = urldecode($playerinfo['player_nickname']);
            }
        } else {
            return $this->sendSuccess($response);
        }
        $models = AgentInfoModel::model()->getLowerAgentList($condition);
        /*
            'agent_id',
            'player_id',
            'agent_promote_count',
            'agent_remark',
            'agent_parentid',
            'player_nickname',
         */
        $ids = [];
        $parentIds = [];
        foreach ($models->items() as $v) {
            $ids[$v->agent_id] = $v->agent_id;
            $parentIds[$v->agent_parentid] = $v->agent_parentid;
        }
        // 统计--直属推广数
        $modelsCount = AgentInfoModel::model()->getLowerAgentCount($ids);
        $countData = [];
        foreach ($modelsCount as $value) {
            $countData[$value->agent_parentid] = $value->total;
        }
        // 直属推广的资金账号手机号
        $accounts = AgentAccountInfoModel::model()->getAccountInfoByAgentIds($ids,['agent_account_agent_id','agent_account_mobile']);
        $accountData = [];
        foreach ($accounts as $val) {
            $accountData[$val->agent_account_agent_id] = $val->agent_account_mobile;
        }
        //二级推广列表：所属一层推广昵称(ID)，使用玩家昵称和玩家id
        $playerIdArr = [];
        $playerNameArr = [];
        if ($id === 0) {
            $modelsAgent = AgentInfoModel::model()->getAgentById($parentIds, ['agent_id', 'agent_player_id']);
            foreach ($modelsAgent as $agent) {
                $playerIdArr[$agent->agent_id] = $agent->agent_player_id;
                // 查找player_nickname
                $playerinfo = PlayerModel::model()->getPlayerinfoById($agent->agent_player_id);
                if($playerinfo){
                    $playerNameArr[$agent->agent_id] = urldecode($playerinfo['player_nickname']);
                }else{
                    $playerNameArr[$agent->agent_id] = '';
                }
            }
            
            
        }
        foreach ($models->items() as $model) {
            if (isset($countData[$model->agent_id])) {
                $model->agent_count = $countData[$model->agent_id];
            }else{
                $model->agent_count = 0;
            }

            // 统计--推广的直属玩家数
            // $model->agent_promote_count = $model->agent_promote_count - $model->agent_count;

            // 账号
            if(isset($accountData[$model->agent_id])){
                $model->agent_account_mobile = $accountData[$model->agent_id];
            }else{
                $model->agent_account_mobile = '';
            }

            //二级推广列表：所属一层推广昵称(ID)
            if ($id === 0) {
                if (isset($playerIdArr[$model->agent_parentid])) {
                    $model->parent_agent_name = $playerNameArr[$model->agent_parentid];
                    $model->parent_agent_id   = $playerIdArr[$model->agent_parentid];
                }else{
                    $model->parent_agent_name = '';
                    $model->parent_agent_id = '';
                }
                
                unset($model->agent_parentid);
            }else{
                unset($model->agent_parentid);
            }
            $model->player_nickname = urldecode($model->player_nickname);
        }
        $response['list'] = $models;
        return $this->sendSuccess($response);
    }

    /**
     * 更新代理备注
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function updateAgentRemark()
    {
        $userInfo = $this->isLogin($this->token);
        $agent_id = $userInfo['agent_info']['agent_id'];
        $id = input('id/d');
        $agentRemark = input('post.agent_remark');
        if (empty($agentRemark)) {
            return $this->sendError(2000, '备注不可空白');
        }
        if (mb_strlen($agentRemark, 'UTF-8') > 120) {
            return $this->sendError(2001, '备注字符过长');
        }
        if (!$modelAgent = AgentInfoModel::model()->isLowerAgent($id, $agent_id)) {
            return $this->sendError(2002, '不是直属代理');
        }
        if (!AgentInfoModel::model()->updateAgentRemark($id, $agentRemark)) {
            return $this->sendError(2003, '服务器繁忙-请稍后再试');
        }
        return $this->sendSuccess();
    }
}
