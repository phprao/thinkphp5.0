<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 10:52
 */
namespace app\api\model;

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
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getInfo($condition)
    {
        $data = $this->where($condition)->find();
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

    public function getAccountInfoByAgentIdLock($agentId)
    {
       $this->where('agent_account_agent_id',$agentId);
       $this->lock(true);
       return  $this->find();
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

    public function getAccountInfoByAgentIds($agentId,$field = [])
    {
        if(is_array($agentId)){
            $this->whereIn('agent_account_agent_id',$agentId);
        }else{
            $this->where('agent_account_agent_id',$agentId);
        }

        if(is_array($field) && !empty($field)){
            $this->field($field);
        }

        return $this->select();
    }

    /**
     * @param $condition
     * @param $data
     * @return $this
     */
    public function updateInfo($condition,$data){
        return $this->where($condition)->update($data);
    }

    public function updateMoney($agent_id, $money, $type){
        if($type == 'inc'){
            return $this->where('agent_account_agent_id', $agent_id)->setInc('agent_account_money', $money);
        }
        if($type == 'dec'){
            return $this->where('agent_account_agent_id', $agent_id)->setDec('agent_account_money', $money);
        }
    }



}