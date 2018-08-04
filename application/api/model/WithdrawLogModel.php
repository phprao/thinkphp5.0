<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 15:33
 */
namespace app\api\model;

use app\common\model\Model;

class WithdrawLogModel extends Model{

    public $table = 'dc_withdraw_log';

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
     * 获取提现列表的数据
     * @param $condition
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($condition)
    {
        if(isset($condition['withdraw_money'])){
            $this->where('withdraw_money',$condition['withdraw_money']);
        }
        if(isset($condition['agent_id'])){
            $this->where('withdraw_agent_id',$condition['agent_id']);
        }
        if(isset($condition['start'])){
            $this->where('withdraw_create_time','>=',$condition['start']);
        }
        if(isset($condition['end'])){
            $this->where('withdraw_create_time','<',$condition['end']);
        }
        $this->order('withdraw_id DESC');
        return  $this->paginate(10);
    }

    public function getWithdrawToalMoney($condition)
    {
        if(isset($condition['withdraw_money'])){
            $this->where('withdraw_money',$condition['withdraw_money']);
        }

        if(isset($condition['agent_id'])){
            $this->where('withdraw_agent_id',$condition['agent_id']);
        }

        if(isset($condition['start'])){
            $this->where('withdraw_create_time','>=',$condition['start']);
        }

        if(isset($condition['end'])){
            $this->where('withdraw_create_time','<',$condition['end']);
        }

        $this->field('SUM(withdraw_money) AS total');
        return $this->find();

    }

    /**
     * 判断用户提现的次数
     * @param $condition
     * @return int|string
     */
    public function getWithDrawNums($condition)
    {
        $this->where('withdraw_agent_id',$condition['agent_id']);
        $this->where('withdraw_create_time','>=',$condition['start']);
        $this->where('withdraw_create_time','<',$condition['end']);
        return $this->count();
    }

    public function getWithDrawNumsLock($condition)
    {
        $this->where('withdraw_agent_id',$condition['agent_id']);
        $this->where('withdraw_create_time','>=',$condition['start']);
        $this->where('withdraw_create_time','<',$condition['end']);
        $this->lock(true);
        return $this->count();
    }
    
    public function insertOne($data){
        $this->data($data);
        $re = $this->save();
        if($re){
            return $this->withdraw_id;
        }else{
            return false;
        }
    }
}