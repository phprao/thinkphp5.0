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
class AgentSuperStatisticsModel extends Model
{
    /**
     * @var string
     */
    public $table       = 'dc_agent_super_statistics_date';
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
    
    public function getSuperAgentStatisticsList($condition = null){
        $this->where('statistics_money_type',1);
        if(isset($condition->method) && $condition->method == 1){
            $this->where('statistics_money_status', 0);
        }else{
            $this->where('statistics_money_status', 2);
        }
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<',$condition->end);
        if(isset($condition->keyword)){
            $this->whereIn('statistics_agent_id',$condition->keyword);
        }
        $this->group('statistics_agent_id');
        $this->order('statistics_agent_id asc');
        $this->field([
            'statistics_agent_id',
            'SUM(statistics_money_data) as other_cost',
            'SUM(statistics_money_data_direct) as direct_cost',
            'SUM(statistics_money_data / statistics_money_rate_value) as other_income',
            'SUM(statistics_money_data_direct / statistics_money_rate_value) as direct_income',
            'SUM(statistics_money_data * statistics_super_share / 10000 / statistics_money_rate_value) as super_other_income',
            'SUM(statistics_money_data_direct * statistics_super_share_direct / 10000 / statistics_money_rate_value) as super_direct_income',
            'SUM(statistics_money) as super_income',
            'MAX(statistics_super_share_direct) as direct_rate_high',
            'MIN(statistics_super_share_direct) as direct_rate_low',
            'MAX(statistics_super_share) as other_rate_high',
            'MIN(statistics_super_share) as other_rate_low',
            'statistics_money_rate_value'
        ]);
        return $this->paginate($condition->size);
    }

    public function getSuperAgentStatisticsListAll($condition = null){
        $this->where('statistics_money_type',1);
        $this->where('statistics_money_status', 0);
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<',$condition->end);
        // if(isset($condition->keyword)){
        //     $this->whereIn('statistics_agent_id',$condition->keyword);
        // }
        
        return $this->select();
    }

    /**
     * 特代收益总计
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function getSuperAgentIncome($condition = null){
        $this->where('statistics_money_type', 1);
        if(isset($condition->method) && $condition->method == 1){
            $this->where('statistics_money_status', 0);
        }else{
            $this->where('statistics_money_status', 2);
        }
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<',$condition->end);
        if(isset($condition->agent_id) && $condition->agent_id){
            $this->where('statistics_agent_id',$condition->agent_id);
        }
        $this->field([
            'SUM(statistics_money_data) as other_cost',
            'SUM(statistics_money_data_direct) as direct_cost',
            'SUM(statistics_money_data / statistics_money_rate_value) as other_income',
            'SUM(statistics_money_data_direct / statistics_money_rate_value) as direct_income',
            'SUM(statistics_money) as super_income',
        ]);
        
        $return = $this->find();
        return $return;
    }

    public function getSuperAgentIncomeShareRate($condition = null,$agent_id){
        // 最低分成比
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<',$condition->end);
        $this->where('statistics_agent_id',$agent_id);
        $this->where('statistics_money_type',1);
        $this->where('statistics_money_status', 2);
        $this->field([
            'statistics_super_share'
        ]);
        $this->order('statistics_super_share asc');
        $ret = $this->find();
        if(!$ret){
            $low = '0%';
            return $low;
        }else{
            $low = $ret['statistics_super_share'] / 100;
        }
        // 最高分成比
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<',$condition->end);
        $this->where('statistics_agent_id',$agent_id);
        $this->where('statistics_money_type',1);
        $this->where('statistics_money_status', 2);
        $this->field([
            'statistics_super_share'
        ]);
        $this->order('statistics_super_share desc');
        $ret = $this->find();
        $high = $ret['statistics_super_share'] / 100;
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
     * 获取渠道月消耗数据
     * @param  [type] $channel_id [description]
     * @param  [type] $time       [description]
     * @return [type]             [description]
     */
    public function getSuperStatisticsData($channel_id,$timestamp){
        $this->where('statistics_agent_id',$channel_id);
        $this->where('statistics_time',$timestamp);
        $this->where('statistics_money_type',1);
        return $this->find();
    }

    public function getSuperAgentIncomeById($condition = null){
        $this->where('statistics_agent_id', $condition->agent_id);
        $this->where('statistics_money_type', 1);
        $this->where('statistics_month','>=',$condition->start);
        $this->where('statistics_month','<',$condition->end);
        $this->where('statistics_money_status', 2);
        $this->field([
            'SUM(statistics_money_data) as statistics_money_data',
            'SUM(statistics_money_data_direct) as statistics_money_data_direct',
            'statistics_month',
            'statistics_super_share_direct',
            'MAX(statistics_super_share) as statistics_super_share_high',
            'MIN(statistics_super_share) as statistics_super_share_low',
            'SUM(statistics_money_data * statistics_super_share/10000/statistics_money_rate_value) as other_income_super',
            'SUM(statistics_money_data_direct * statistics_super_share_direct/10000/statistics_money_rate_value) as direct_income_super',
            'statistics_money_rate_value',
            'SUM(statistics_money) as statistics_money',
        ]);
        $this->group('statistics_month');
        $this->order('statistics_month desc');
        return $this->paginate($condition->size);
    }

    public function getInfo($condition = null){
        return $this->where($condition)->find();
    }
    
}