<?php
/**
 * +----------------------------------------------------------
 * date: 2018-01-29 18:03:25
 * +----------------------------------------------------------
 * author: Raoxiaoya
 * +----------------------------------------------------------
 * describe: 玩家游戏记录
 * +----------------------------------------------------------
 */

namespace app\admin\model;

use app\common\model\Model;

class PartnerPayRecordModel extends Model
{
    public $table = 'dc_partner_pay_record';

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
     * 分页查找--游戏记录
     * @param  array $filter [description]
     * @return [type]         [description]
     */
    public function getList($condition)
    {
        if(isset($condition->start)){
            $this->where('recored_create_time', '>=', $condition->start);
        }
        if(isset($condition->end)){
            $this->where('recored_create_time', '<', $condition->end);
        }
        if (isset($condition->partner_id)) {
            $this->where('recored_partner_id', $condition->partner_id);
        }
        if (isset($condition->pay_type)) {
            $this->where('recored_type', $condition->pay_type);
        }
        if (isset($condition->keyword)) {
            $this->where('recored_player_id', 'like', '%'.$condition->keyword.'%');
        }

        return $this->paginate($condition->size);
    }

    /**
     * 金币变化总计
     * @return [type] [description]
     */
    public function getRechargeSum($condition){
        if(isset($condition->start)){
            $this->where('recored_create_time', '>=', $condition->start);
        }
        if(isset($condition->end)){
            $this->where('recored_create_time', '<', $condition->end);
        }
        if (isset($condition->partner_id)) {
            $this->where('recored_partner_id', $condition->partner_id);
        }
        if (isset($condition->pay_type)) {
            $this->where('recored_type', $condition->pay_type);
        }
        if (isset($condition->keyword)) {
            $this->where('recored_player_id', 'like', '%'.$condition->keyword.'%');
        }
        
        return $this->sum('recored_price');
    }


}














