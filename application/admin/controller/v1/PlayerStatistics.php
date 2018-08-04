<?php
/**
 * +----------------------------------------------------------
 * date: 2018-03-19 10:33:29
 * +----------------------------------------------------------
 * author: Raoxiaoya
 * +----------------------------------------------------------
 * describe: 玩家战绩
 * +----------------------------------------------------------
 */

namespace app\admin\controller\v1;

use app\admin\controller\Controller;
use app\admin\model\GameBeatRecordModel;
use app\admin\model\GameInfoModel;
use app\admin\model\GameKindModel;
use app\admin\model\PromotersInfoModel;
use app\admin\model\AgentInfoModel;

/**
 * @controllerName 玩家战绩
 */
class PlayerStatistics extends Controller
{

    protected static $rule = [
        'page' => 'integer|between:1,10000',
        'pageSize' => 'integer|between:1,20',
    ];

    /**
     * @actionName  金币战绩
     */
    public function index()
    {
        $validation = $this->validate($this->request->param(), self::$rule);
        if ($validation !== true) {
            return $this->sendError(3001, $validation);
        }
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('pageSize', 10);
        $condition = $tCondition = array();

        $gameKind = $this->request->get('game_kind', null);
        $gameId = $this->request->get('game_id', null);
        if ($gameKind || $gameId) {
            $tCondition['game_kind'] = $gameKind;
            if ($gameId) {
                $tCondition['game_id'] = $gameId;
            }
            $gameIdArr = GameInfoModel::model()->getGameInfo('game_id', $tCondition);
            if ($gameIdArr) {
                $gameId = [];
                foreach ($gameIdArr as $key => $value) {
                    $gameId[] = $value['game_id'];
                }
            }
            //符合查询条件的游戏列表
            $condition['game_beat_game_id'] = ['in', $gameId];
        }
        $keywords = $this->request->get('keywords', null);
        if (!empty($keywords)) {
            $condition['keywords'] = $keywords;
        }

        $sTime = $this->request->get('s_time', null);
        $sTime = strtotime($sTime);
        if (!empty($sTime)) {
            $condition['game_beat_time'] = ['>=', $sTime];
        }
        $eTime = strtotime($this->request->get('e_time', null));
        if (!empty($eTime)) {
            if (!empty($sTime)) {
                $condition['game_beat_time'] = [['>=', $sTime], ['<=', $eTime + 24 * 3600], 'and'];
            } else {
                $condition['game_beat_time'] = ['<=', $eTime + 24 * 3600];
            }
        } else {
            if ($gameKind || $gameId || $keywords) {
            } else {
                $date = $this->getSearchDate();
                $condition['game_beat_time'] = [['>=', strtotime($date['startDate'])], ['<', strtotime($date['endDate']) + 24 * 3600], 'and'];
            }
        }

        //金币场战绩
        $condition['game_beat_room_no'] = 0;

        $order = 'game_beat_time desc';
        $data = GameBeatRecordModel::model()->getPlayerBeat($condition, $page, $pageSize, $order);
        $count = GameBeatRecordModel::model()->getPlayerBeatCount($condition);
        if ($data) {
            foreach ($data as $key => $value) {
                //昵称转码
                $data[$key]['game_beat_player_nick'] = urldecode($value['game_beat_player_nick']);
                //找出星级推广员
                
                $promotersParentData = PromotersInfoModel::model()->getParentIdByPlayerId($value['game_beat_player_id']);
                //判断是否是星级推广
                $agent_info = AgentInfoModel::model()->getInfo(['agent_player_id' => $promotersParentData['promoters_parent_id']]);
                $data[$key]['agent_login_status'] = $agent_info['agent_login_status'] ? $agent_info['agent_login_status'] : 0;
                //星级推广员id
                $data[$key]['promoters_parent_id'] = $promotersParentData['promoters_parent_id'] ? $promotersParentData['promoters_parent_id'] : 0;
                //星级推广员名称
                $data[$key]['promoters_parent_nickname'] = urldecode($promotersParentData['player_nickname'] ? $promotersParentData['player_nickname'] : '--');
                //时间格式
                $data[$key]['game_beat_over_time'] = date('Y-m-d H:i:s', $value['game_beat_over_time']);
                //所有id
                // $superid[] = $value['game_beat_player_id'];

                $game_kind = GameInfoModel::model()->getGameInfo('game_kind', ['game_id' => $value['game_beat_game_id']])[0]['game_kind'];
                switch ($game_kind) {
                    case 1 ://1麻将 2牌类 3字牌
                        $data[$key]['game_kind'] = '麻将';
                        break;
                    case 2 ://1麻将 2牌类 3字牌
                        $data[$key]['game_kind'] = '牌类';
                        break;
                    case 3 ://1麻将 2牌类 3字牌
                        $data[$key]['game_kind'] = '字牌';
                        break;
                    case 4 ://1麻将 2牌类 3字牌
                        $data[$key]['game_kind'] = '其他';
                        break;
                }

                //预留字段,房间名称
                $data[$key]['room_name'] = $value['game_beat_room_name'];
				$superid = $value['game_beat_player_id'];
				//找出所属渠道名称,找出所属渠道id
	            $channelInfo = AgentInfoModel::model()->getByAgentPlayerId($superid, ['agent_top_agentid,agent_name']);
	            $data[$key]['super_agent_id'] = $channelInfo['agent_top_agentid'];
				$data[$key]['super_agent_name'] = AgentInfoModel::model()->getInfo(['agent_id' => $channelInfo['agent_top_agentid']])['agent_name'];

			
				
            }
            // //找出所属渠道名称,找出所属渠道id
            // $channelInfo = AgentInfoModel::model()->getByAgentPlayerId($superid, ['agent_top_agentid,agent_name']);
            // $data[$key]['super_agent_id'] = 0;
            // $data[$key]['super_agent_name'] = '__';
            // foreach ($channelInfo as $key => $val) {
            //     $data[$key]['super_agent_id'] = $val['agent_top_agentid'] ? $val['agent_top_agentid'] : 0;
            //     $data[$key]['super_agent_name'] = AgentInfoModel::model()->getInfo(['agent_id' => $val['agent_top_agentid']])['agent_name'];
            // }
        }

        $date = $this->getSearchDate();
        return $this->sendSuccess([
            'total' => $count,
            'per_page' => $pageSize,
            'current_page' => $page,
            'last_page' => ceil($count / $pageSize),
            'date' => $date,
            'list' => $data,
        ]);
    }

    /**
     * @actionName  包间战绩
     * @return array
     */
    public function roomRound()
    {
        $validation = $this->validate($this->request->param(), self::$rule);
        if ($validation !== true) {
            return $this->sendError(3001, $validation);
        }
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('pageSize', 10);
        $condition = $tCondition = array();
        $gameKind = $this->request->get('game_kind', null);
        $gameId = $this->request->get('game_id', null);
        if ($gameKind || $gameId) {
            $tCondition['game_kind'] = $gameKind;
            if ($gameId) {
                $tCondition['game_id'] = $gameId;
            }
            $gameIdArr = GameInfoModel::model()->getGameInfo('game_id', $tCondition);
            if ($gameIdArr) {
                $gameId = [];
                foreach ($gameIdArr as $key => $value) {
                    $gameId[] = $value['game_id'];
                }
            }
            //符合查询条件的游戏列表
            $condition['game_beat_game_id'] = ['in', $gameId];
        }
        $keywords = $this->request->get('keywords', null);
        if (!empty($keywords)) {
            $condition['keywords'] = $keywords;
        }

        $sTime = $this->request->get('s_time', null);
        $sTime = strtotime($sTime);
        if (!empty($sTime)) {
            $condition['game_beat_time'] = ['>=', $sTime];
        }
        $eTime = strtotime($this->request->get('e_time', null));
        if (!empty($eTime)) {
            if (!empty($sTime)) {
                $condition['game_beat_time'] = [['>=', $sTime], ['<=', $eTime + 24 * 3600], 'and'];
            } else {
                $condition['game_beat_time'] = ['<=', $eTime + 24 * 3600];
            }
        } else {
            if ($gameKind || $gameId || $keywords) {
            } else {
                $date = $this->getSearchDate();
                $condition['game_beat_time'] = [['>=', strtotime($date['startDate'])], ['<', strtotime($date['endDate']) + 24 * 3600], 'and'];
            }
        }
        //金币场战绩
        $condition['game_beat_room_no'] = array('>', 0);
        $order = 'game_beat_time desc';
        $data = GameBeatRecordModel::model()->getPlayerBeat($condition, $page, $pageSize, $order);
        $count = GameBeatRecordModel::model()->getPlayerBeatCount($condition);
        if ($data) {
            foreach ($data as $key => $value) {
                //昵称转码
                $data[$key]['game_beat_player_nick'] = urldecode($value['game_beat_player_nick']);
                //找出星级推广员
                $promotersParentData = PromotersInfoModel::model()->getParentIdByPlayerId($value['game_beat_player_id']);
                //判断是否是星级推广
                $agent_info = AgentInfoModel::model()->getInfo(['agent_player_id'=>$promotersParentData['promoters_parent_id']]);
                $data[$key]['agent_login_status'] = $agent_info['agent_login_status']?$agent_info['agent_login_status']:0;
                //星级推广员id
                $data[$key]['promoters_parent_id'] = $promotersParentData['promoters_parent_id'] ? $promotersParentData['promoters_parent_id'] : 0;
                //星级推广员名称
                $data[$key]['promoters_parent_nickname'] = urldecode($promotersParentData['player_nickname'] ? $promotersParentData['player_nickname'] : '');
                //时间格式
                $data[$key]['game_beat_over_time'] = date('Y-m-d H:i:s', $value['game_beat_over_time']);
                //所有id
                // $superid[] = $value['game_beat_player_id'];
                $game_kind = GameInfoModel::model()->getGameInfo('game_kind', ['game_id' => $value['game_beat_game_id']])[0]['game_kind'];
                switch ($game_kind) {
                    case 1 ://1麻将 2牌类 3字牌
                        $data[$key]['game_kind'] = '麻将';
                        break;
                    case 2 ://1麻将 2牌类 3字牌
                        $data[$key]['game_kind'] = '牌类';
                        break;
                    case 3 ://1麻将 2牌类 3字牌
                        $data[$key]['game_kind'] = '字牌';
                        break;
                    case 4 ://1麻将 2牌类 3字牌
                        $data[$key]['game_kind'] = '其他';
                        break;
                }
				$superid = $value['game_beat_player_id'];

				//找出所属渠道名称,找出所属渠道id
	   //          $channelInfo = AgentInfoModel::model()->getByAgentPlayerId($superid, ['agent_top_agentid,agent_name']);

				// $data[$key]['super_agent_name'] = AgentInfoModel::model()->getInfo(['agent_id' => $channelInfo['agent_top_agentid']])['agent_name'];
				
				$channelInfo = AgentInfoModel::model()->getByAgentPlayerId($superid, ['agent_top_agentid,agent_name']);
	            $data[$key]['super_agent_id'] = $channelInfo['agent_top_agentid'];
				$data[$key]['super_agent_name'] = AgentInfoModel::model()->getInfo(['agent_id' => $channelInfo['agent_top_agentid']])['agent_name'];	



            }
            // //找出所属渠道名称,找出所属渠道id
            // $channelInfo = AgentInfoModel::model()->getByAgentPlayerId($superid, ['agent_top_agentid,agent_name']);
            // $data[$key]['super_agent_id'] = 0;
            // $data[$key]['super_agent_name'] = '__';
            // foreach ($channelInfo as $key => $val) {
            //     $data[$key]['super_agent_id'] = $val['agent_top_agentid'] ? $val['agent_top_agentid'] : 0;
            //     $data[$key]['super_agent_name'] = AgentInfoModel::model()->getInfo(['agent_id' => $val['agent_top_agentid']])['agent_name'];
            // }
        }
        $date = $this->getSearchDate();
        return $this->sendSuccess([
            'total' => $count,
            'per_page' => $pageSize,
            'current_page' => $page,
            'last_page' => ceil($count / $pageSize),
            'date' => $date,
            'list' => $data,
        ]);
    }

    /**
     * @actionName  游戏分类的游戏列表
     */
    public function gameData()
    {
        $gameKind = $this->request->get('game_kind', null);
        $condition = [];
        if ($gameKind) {
            $condition['game_kind'] = $gameKind;
        }
        $gameInfo = GameInfoModel::model()->getGameInfo('game_id,game_name', $condition);
        return $this->sendSuccess($gameInfo);
    }

    /**
     * @actionName  游戏分类
     */
    public function gameCategroy()
    {
        $condition = array();
        //$gameKind = $this->request->get('game_kind_id', null);
        //if($gameKind){
        //$condition['game_kind_id'] = $gameKind;
        //}
        $gameInfo = GameKindModel::model()->getGameKind('game_kind_id,game_kind_name', $condition);
        return $this->sendSuccess($gameInfo);
    }

    /**
     * 日期
     * @return array
     */
    protected function getSearchDate()
    {
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        return ['startDate' => $startDate, 'endDate' => $endDate];
    }
}
