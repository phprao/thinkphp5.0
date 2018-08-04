<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/17
 * Time: 15:01
 */
namespace app\admin\model;

use app\common\model\Model;

/**
 * 代理配置表
 * Class AgentConfigModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class AgentConfigModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_agent_config';

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
     * 获取
     * @param $agentId
     * @param array $field
     * @param null $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getConfigByAgentId($agentId, $field = [], $condition = null)
    {
        $this->field($field);
        $this->where('agent_id', $agentId);
        return $this->find();
    }

    /**
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAgentConfigOne($condition)
    {
        $data = $this->where($condition)->find();
        return $data;
    }


    /**
     * @param $condition
     * @return int|string
     */
    public function insertAgentConfig($condition)
    {
        $data = $this->insert($condition);
        return $data;
    }

    /**
     * @param $condition
     * @return false|int
     */
    public function saveAgentConfig($condition)
    {
        return $this->save($condition);

    }


    /**
     * @param $id
     * @param $condition
     * @return false|int
     */
    public function saveAgent($id,$condition)
    {
        $this->where('agentconf_id', $id);
        return $this->isUpdate(true)->save($condition);
    }


}