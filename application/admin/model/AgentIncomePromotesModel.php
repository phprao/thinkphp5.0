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

namespace app\admin\model;

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

    public function getOne($condition)
    {
        $data = $this->where($condition)->find();
        return $data;
    }

    public function getAll($condition)
    {
        $data = $this->where($condition)->select();
        return $data;
    }

    /**
     * 分页查找
     * @param  array  $filter [description]
     * @return [type]         [description]
     */
    public function getAgentIncome($condition = null){
        $this->alias('d');
        $this->join($this->table_agent.' a', 'a.agent_id = d.statistics_from_value');
        $this->where('d.statistics_agents_id',$condition->agent_id);   
        $this->where('d.statistics_from',$condition->level);  
        $this->where('d.statistics_time','>=',$condition->start);
        $this->where('d.statistics_time','<=',$condition->end);
        if(isset($condition->player_id)){
            // 可能是玩家id，代理id
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    'd.statistics_from_value' => ['=',$condition->player_id],
                    'a.agent_player_id'       => ['=',$condition->player_id]
                ]);
            });
        }
        $this->field([
            'a.agent_name',
            'a.agent_player_id',
            'SUM(d.statistics_my_income) as my_income',
            'd.statistics_share_money_low',
            'd.statistics_share_money_high',
            'd.statistics_from_value'
        ]);
        $this->order('d.statistics_id desc');
        $this->group('d.statistics_from_value');

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
            $this->where('statistics_from_value', $condition->agent_id);
        }else{
            return 0;
        }
        if (isset($condition->player_id)) {
            // 无法精确到玩家
        }
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<=',$condition->end);
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