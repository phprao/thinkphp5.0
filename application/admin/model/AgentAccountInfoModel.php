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
 * 代理账户
 * Class AgentInfoModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class AgentAccountInfoModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_agent_account_info';

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
     * @param string $count
     * @return int|string
     *
     */

    public function get_AgentAccountInsert($condition)
    {
        $data = $this->insert($condition);
        return $data;
    }

    /**
     * 获取代理商的基本信息
     * @param $agentId
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAccountInfoByAgentId($agentId)
    {
       return  $this->where('agent_account_agent_id',$agentId)->find();
    }
    /**
     * 更新代理商的账号信息
     * @param $agentId
     * @param $data
     * @return $this
     */
    public function updateAgentAccountInfo($agentId,$data)
    {
        return $this->where('agent_account_agent_id',$agentId)->update($data);
    }

    /**
     * 新增资金账户
     */
    public function addAgentAccount($info)
    {
        $this->data([
            'agent_account_agent_id' =>$info['agent_id']
        ]);
        $this->save();
        return $this->agent_account_id;
    }


    /**
     * @param $id
     * @param $condition
     * @return false|int
     */
    public function saveAgetnsave($id,$condition)
    {
        $this->where('agent_account_agent_id', $id);
        return $this->isUpdate(true)->save($condition);
    }

}