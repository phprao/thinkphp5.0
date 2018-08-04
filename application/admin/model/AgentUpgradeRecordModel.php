<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 10:52
 */
namespace app\admin\model;

use app\common\model\Model;

/**
 * 玩家升级日志
 * Class AgentUpgradeRecordModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class AgentUpgradeRecordModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_agent_upgrade_record';

    /**
     * 静态调用
     * @param array $data
     * @param string $className
     * @return static active record model instance.
     */
    public static function model($data = [], $className = __CLASS__)
    {
        return parent::model($data, $className);
    }

    /**
     * 添加升级日志
     * @param int $playerId
     * @param int $parentAgentId
     * @return false|int
     */
    public function addRecord($playerId, $parentAgentId)
    {
        return $this->isUpdate(false)->save([
            'agent_upgrade_record_player_id' => $playerId,
            'agent_upgrade_record_agent_id' => $parentAgentId,
            'agent_upgrade_record_time' => time(),
        ]);
    }
}