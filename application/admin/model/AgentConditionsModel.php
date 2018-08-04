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
 * 代理条件表
 * Class AgentConditionsModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class AgentConditionsModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_agent_conditions';

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
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAgentConditionsOne($condition)
    {
        $data = $this->where($condition)->find();
        return $data;
    }

    /**
     * @param $condition
     * @return int|string
     */
    public function insertAgentConditions($condition)
    {
        $data = $this->insert($condition);
        return $data;
    }

    /**
     * @param $condition
     * @return false|int
     */
    public function saveAgentconditions($condition)
    {
        return $this->save($condition);

    }

    /**
     * @param $id
     * @param $condition
     * @return false|int
     */
    public function saveAgentConditionsId($id,$condition)
    {
        $this->where('agent_conditions_id', $id);
        return $this->isUpdate(true)->save($condition);
    }


    /**
     * 获取条件
     * @param $id
     * @param array $field
     * @param null $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getConditionById($id, $field = [], $condition = null)
    {
        $this->field($field);
        $this->where('agent_conditions_id', $id);
        return $this->find();
    }

    public function delData($condition)
    {
        return $this->where($condition)->delete();

    }
}