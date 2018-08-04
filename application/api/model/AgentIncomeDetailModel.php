<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-01-26 11:37:10
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 代理收益数据模型
 +---------------------------------------------------------- 
 */

namespace app\api\model;

use app\common\model\Model;

class AgentIncomeDetailModel extends Model
{
    public $table        = 'dc_agents_statistics_day';
    public $table_player = 'dc_view_player_info';

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
     * 分页查找
     * @param  array  $filter [description]
     * @return [type]         [description]
     */
    public function getListByPage($condition = null){
        if (isset($condition->player_id)) {
            $this->where('statistics_player_id','like', '%' . $condition->player_id . '%');
        }
        $this->where('statistics_my_income', '>', 0);
        $this->where('statistics_parent_agents_id', $condition->agent_id);
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<',$condition->end);
        $this->field([
            'statistics_player_id',
            'SUM(statistics_income) as statistics_income',
            'MIN(statistics_share_money_low) as statistics_share_money_low',
            'MAX(statistics_share_money_high) as statistics_share_money_high',
            'SUM(statistics_my_income) as statistics_my_income'
        ]);

        $this->group('statistics_player_id');

        return $this->paginate($condition->size);
    }

    /**
     * 分页查找-明细
     * @param  array  $filter [description]
     * @return [type]         [description]
     */
    public function getListDetailByPage($condition = null){
        $this->alias('d');
        $this->join($this->table_player.' p', 'p.player_id = d.statistics_player_id');
        if (isset($condition->player_id)) {
            $this->where('d.statistics_player_id',$condition->player_id);
        }
        $this->where('d.statistics_parent_agents_id', $condition->agent_id);
        $this->where('d.statistics_time','>=',$condition->start);
        $this->where('d.statistics_time','<=',$condition->end);
        $this->field([
            'p.player_id',
            'p.player_nickname',
            'd.statistics_data',
            'd.statistics_my_data',
            'd.statistics_cost_detail',
            'd.statistics_share_money_low',
            'd.statistics_share_money_high'
        ]);

        return $this->paginate($condition->size);
    }

    /**
     * 名下玩家时间区间内总服务费
     * @param  [type] $condition [description]
     * @param  [type] $agent_id  [description]
     * @return [type]            [description]
     */
    public function getSubPlayerTax($condition = null,$agent_id){
        $this->where('statistics_parent_agents_id', $agent_id);
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<=',$condition->end);
        $this->field([
            'SUM(statistics_income) as total',
        ]);

        $return = $this->find();
        return $return['total'] / 100 ;
    }
    
}