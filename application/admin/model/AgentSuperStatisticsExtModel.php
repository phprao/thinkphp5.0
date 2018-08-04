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
class AgentSuperStatisticsExtModel extends Model
{
    /**
     * @var string
     */
    public $table       = 'dc_agent_super_statistics_date_ext';
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

    public function getSuperAgentExtOne($condition = null){
        $this->where('statistics_money_type', 1);
        $this->where('statistics_agent_id', $condition->super_id);
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<',$condition->end);
        $this->field([
            'SUM(statistics_money_ext) as statistics_money_ext',
            'MAX(statistics_super_share_ext) as share_high',
            'MIN(statistics_super_share_ext) as share_low'
        ]);
        return $this->find();
    }

    public function getSuperAgentExtOneMonth($condition = null){
        $this->where('statistics_money_type', 1);
        $this->where('statistics_agent_id', $condition->agent_id);
        $this->where('statistics_time',$condition->statistics_month);
        $this->field([
            'statistics_money_ext',
            'statistics_super_share_ext'
        ]);
        return $this->find();
    }

    /**
     * 特代收益总计
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function getSuperAgentExt($condition = null){
        $this->where('statistics_money_type', 1);
        $this->where('statistics_time','>=',$condition->start);
        $this->where('statistics_time','<',$condition->end);
        if(isset($condition->agent_id) && $condition->agent_id){
            $this->where('statistics_agent_id',$condition->agent_id);
        }
        $this->field([
            'SUM(statistics_money_ext) as statistics_money_ext',
        ]);
        
        $return = $this->find();
        return $return;
    }

    public function getInfo($condition = null){
        return $this->where($condition)->find();
    }
    
}