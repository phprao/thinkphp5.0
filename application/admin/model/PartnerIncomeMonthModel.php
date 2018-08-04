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

class PartnerIncomeMonthModel extends Model
{
    public $table = 'dc_partner_income_month';

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
            $this->where('time', '>=', $condition->start);
        }
        if(isset($condition->end)){
            $this->where('time', '<', $condition->end);
        }
        if (isset($condition->partner_id)) {
            $this->where('partner_id', $condition->partner_id);
        }
        $this->field([
            'partner_id',
            'third_share_rate',
            'share_rate',
            'SUM(recharge_data) as recharge_data',
            'SUM(income) as partner_income',
            'SUM(recharge_data * (1 - third_share_rate - share_rate)) as company_income'
        ]);
        $this->group('partner_id');

        return $this->paginate($condition->size);
    }

    public function getPartnerList($condition)
    {
        if(isset($condition->start)){
            $this->where('time', '>=', $condition->start);
        }
        if(isset($condition->end)){
            $this->where('time', '<', $condition->end);
        }
        if (isset($condition->partner_id)) {
            $this->where('partner_id', $condition->partner_id);
        }

        return $this->paginate($condition->size);
    }

    /**
     * 金币变化总计
     * @return [type] [description]
     */
    public function getIncomeSum($condition){
        if(isset($condition->start)){
            $this->where('time', '>=', $condition->start);
        }
        if(isset($condition->end)){
            $this->where('time', '<', $condition->end);
        }
        if (isset($condition->partner_id)) {
            $this->where('partner_id', $condition->partner_id);
        }
        $this->field([
            'SUM(recharge_data) as total_income',
            'SUM(income) as partner_income',
            'SUM(recharge_data * (1 - third_share_rate - share_rate)) as company_income'
        ]);
        return $this->find();
    }


}














