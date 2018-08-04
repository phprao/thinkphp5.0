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

class AgentsPromotersStatisticsModel extends Model
{
    public $table       = 'dc_agents_promoters_statistics';
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
    public function getStarAgentIncomeList($condition = null){
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<',$condition->end);
        $this->where('statistics_my_income','>',0);
        
        // $this->where('statistics_from',1);
        $this->where('statistics_money_type',1);
        // $this->where('statistics_status', 1);
        
        // 按渠道检索 id
        if(isset($condition->channel)){
            $this->where('statistics_super_agents_id',$condition->channel);
        }
        // 可能是推广员id，昵称
        if(isset($condition->keyword)){
            $this->whereIn('statistics_agents_player_id',$condition->keyword);
        }
        $this->group('statistics_agents_id');
        $this->field([
            'statistics_agents_id',
            'statistics_agents_player_id',
            'statistics_super_agents_id',
            'SUM(statistics_data) as all_data',
            'SUM(statistics_data / statistics_money_type_rate) as all_income',
            'SUM(statistics_my_income) as my_income'
        ]);
        $this->order('all_income desc');
        return $this->paginate($condition->size);
    }

    public function getStarAgentIncomeListExt($condition = null){
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<',$condition->end);
        $this->where('statistics_my_income','>',0);
        $this->where('statistics_money_type',1);
        // 按渠道检索 id
        if(isset($condition->channel)){
            $this->where('statistics_super_agents_id',$condition->channel);
        }
        // 可能是推广员id，昵称
        if(isset($condition->keyword)){
            $this->whereIn('statistics_agents_player_id',$condition->keyword);
        }
        $this->group('statistics_agents_id');
        $this->field([
            'statistics_agents_id',
            'statistics_agents_player_id',
            'statistics_super_agents_id',
            'SUM(statistics_data) as all_data',
            'SUM(statistics_data / statistics_money_type_rate) as all_income',
            'SUM(statistics_my_income) as my_income',
            // 'MIN(statistics_share_money_low) as statistics_share_money_low',
            // 'MAX(statistics_share_money_high) as statistics_share_money_high'
        ]);
        $this->order('all_income desc');
        return $this->paginate($condition->size);
    }

    /**
     * 从一级或二级得到得到的收益
     * @param  [type]  $condition [description]
     * @param  [type]  $agent_id  [description]
     * @param  integer $level     [description]
     * @param  integer $type      [description]
     * @return [type]             [description]
     */
    public function getStarAgentSubIncome($condition = null,$agent_id,$level = 2){
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<',$condition->end);
        $this->where('statistics_agents_id',$agent_id);
        $this->where('statistics_money_type',1);
        // $this->where('statistics_status', 1);
        if($level == 1 || $level == 2 || $level == 3){
            $this->where('statistics_from',$level);
        }

        $this->field([
            'SUM(statistics_my_income) as my_income'
        ]);
        $ret = $this->find();
        return $ret['my_income'];
    }
    /**
     * 分成比
     * @param  [type]  $condition [description]
     * @param  [type]  $agent_id  [description]
     * @param  integer $level     [description]
     * @param  integer $type      [description]
     * @return [type]             [description]
     */
    public function getStarAgentIncomeShareRate($condition = null,$agent_id,$level = 1){
        // 最低分成比
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<',$condition->end);
        $this->where('statistics_agents_id',$agent_id);
        $this->where('statistics_money_type',1);
        // $this->where('statistics_status', 1);
        $this->where('statistics_from',$level);
        $this->field([
            'statistics_share_money_low'
        ]);
        $this->order('statistics_share_money_low asc');
        $ret = $this->find();
        if(!$ret){
            $low = '0%';
            return $low;
        }else{
            $low = $ret['statistics_share_money_low'];
        }
        // 最高分成比
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<',$condition->end);
        $this->where('statistics_agents_id',$agent_id);
        $this->where('statistics_money_type',1);
        // $this->where('statistics_status', 1);
        $this->where('statistics_from',$level);
        $this->field([
            'statistics_share_money_high'
        ]);
        $this->order('statistics_share_money_high desc');
        $ret = $this->find();
        $high = $ret['statistics_share_money_high'];
        if($high > 0){
            if($low == $high){
                $result = $low.'%';
            }else{
                $result = $low.'%-'.$high.'%';
            }
        }else{
            $result = $low.'%';
        }
        
        return $result;
    }

    /**
     * 星级推广贡献的收益统计
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function getStarAgentIncome($condition = null){
        $this->where('statistics_money_type', 1);
        // $this->where('statistics_status', 1);
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<',$condition->end);
        if(isset($condition->channel)){
           $this->where('statistics_super_agents_id',$condition->channel);
        }
        $this->field([
            'SUM(statistics_data) as total_cost',
            'SUM(statistics_data / statistics_money_type_rate) as total_income',
            'SUM(statistics_my_income) as star_income',
        ]);
        
        $return = $this->find();
        return $return;
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