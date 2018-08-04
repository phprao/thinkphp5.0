<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:41
 * @author ChangHai Zhan
 */

namespace app\api\block;

use app\api\model\AgentConditionsModel;
use app\api\model\AgentInfoModel;
use app\api\model\AgentConfigModel;
use app\api\model\AgentUpgradeRecordModel;
use app\api\model\PlayerStatisticalModel;
use Think\Db;

/**
 * 代理升级
 * Class AgentLevelBlock
 * @author ChangHai Zhan
 */
class AgentLevelBlock
{
    /**
     * @param $playerId
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function run($playerId)
    {
        //加载玩家信息
        $agentInfoModel = AgentInfoModel::model()->getByAgentPlayerId($playerId);
        if (!$agentInfoModel) {
            return true;
        }
        //特代ID
        if ($agentInfoModel->agent_parentid == $agentInfoModel->agent_top_agentid) {
            //特级代理 推广的 不用升级
            return true;
        }
        //加载上级信息
        $map = new \stdClass();
        $map->agent_login_status = AgentInfoModel::AGENT_LOGIN_STATUS_NO;
        $agentParentInfoModel = AgentInfoModel::model()->getByAgentId($agentInfoModel->agent_parentid, [], $map);
        if (!$agentParentInfoModel) {
            return true;
        }
        //加载选择条件
        $conditionAgentModel = AgentConfigModel::model()->getConfigByAgentId($agentInfoModel->agent_top_agentid);
        if (!$conditionAgentModel) {
            return true;
        }
        //加载model
        $conditionModel = AgentConditionsModel::model()->getConditionById($conditionAgentModel->agent_conditions_id);
        if (!$conditionModel) {
            return true;
        }
        $condition = json_decode($conditionModel->agent_conditions_data);
        //人数是否合格
        if ($conditionModel->agent_conditions_type == 1 || $conditionModel->agent_conditions_type == 2) {
            if ($agentParentInfoModel->agent_promote_conut < $condition->promote_number) {
                return true;
            }
        }
        //金币是否合格
        if ($conditionModel->agent_conditions_type == 1 || $conditionModel->agent_conditions_type == 3) {
            //玩家消耗
            $map = new \stdClass();
            $map->statistical_type = PlayerStatisticalModel::STATISTICAL_TYPE_MONEY;
            $playerStatisticsModel = PlayerStatisticalModel::model()->getByPlayerId($agentParentInfoModel->agent_player_id, $map);
            if (!$playerStatisticsModel) {
                return true;
            }
            if ($playerStatisticsModel->statistical_sub_total_cost < $condition->gold_consumption) {
                return true;
            }
        }
        //事务
        Db::startTrans();
        if (!AgentInfoModel::model()->updateAgentLoginStatusById($agentParentInfoModel->agent_id)) {
            Db::rollback();
            return true;
        }
        if (!AgentUpgradeRecordModel::model()->addRecord($agentParentInfoModel->agent_player_id, $agentParentInfoModel->agent_parentid)) {
            Db::rollback();
            return true;
        }
        Db::commit();
        return true;
    }
}