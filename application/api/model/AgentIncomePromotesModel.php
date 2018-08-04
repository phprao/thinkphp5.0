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

class AgentIncomePromotesModel extends Model
{
    public $table    = 'dc_agents_promoters_statistics';
    public $table_agent = 'dc_agent_info';

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
    public function getAgentIncome($condition = null){
        $this->where('statistics_agents_id',$condition->agent_id); 
        $this->where('statistics_my_income','>',0);  
        $this->where('statistics_from',$condition->level);  
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<',$condition->end);
        if(isset($condition->from_agent_id)){
            // 玩家ID
            $this->whereIn('statistics_from_value',$condition->from_agent_id);
        }
        $this->field([
            'SUM(statistics_my_income) as my_income',
            'MIN(statistics_share_money_low) as statistics_share_money_low',
            'MAX(statistics_share_money_high) as statistics_share_money_high',
            'statistics_from_value'
        ]);
        $this->order('statistics_id desc');
        $this->group('statistics_from_value');

        return $this->paginate($condition->size);
    }

    /**
     * 下级推广贡献的收益统计
     * @param  [type]  $condition [description]
     * @param  integer $level     [description]
     * @param  integer $type      返回类型：1-人民币，2-金币数
     * @return [type]             
     */
    public function getIncomeArrSub($condition = null,$level = 1,$type = 1){
        if(in_array($level,[1,2,3])){
            $this->where('statistics_from', $level);
            if(isset($condition->from_agent_value)){
                $this->where('statistics_from_value', $condition->from_agent_value);
            }
        }else{
            return 0;
        }
        if (isset($condition->player_id)) {
            // 无法精确到玩家
        }
        if(isset($condition->agent_id)){
            $this->where('statistics_agents_id', $condition->agent_id);
        }
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<',$condition->end);
        if($type == 1){
            $this->field(['SUM(statistics_my_income) as my_income']);
        }elseif ($type == 2) {
            $this->field(['SUM(statistics_my_data) as my_income']);
        }
        
        $return = $this->find();
        return $return['my_income'];
    }



    /**
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function getAgentInfo($condition)
    {

        $data = $this->where($condition)->find();

        return $data;

    }

    /**
     * @param $condition
     * @param $field
     * @return mixed
     */
    public function gteAgentMonthInfo($condition,$field)
    {
        $data = $this->where($condition)->sum($field);
        return $data;

    }



}