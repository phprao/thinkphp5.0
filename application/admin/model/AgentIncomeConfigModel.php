<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-01-26 11:37:10
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 代理收益配置表
 +---------------------------------------------------------- 
 */
namespace app\admin\model;

use app\common\model\Model;

class AgentIncomeConfigModel extends Model
{
	public $table    = 'dc_agent_income_config';

	public static function model($data = [], $className = __CLASS__)
    {
        return parent::model($data, $className);
    }

	public function getAll($condition)
    {
        $data = $this->where($condition)->select();
        return $data;
    }
    /**
     * 根据推广人数查找分成比例
     * @param  [type] $number [description]
     * @return [type]         [description]
     */
    public function getShareRate($agentinfo){
    	$return = array();
    	$this->where('income_agent_id',$agentinfo['agent_id']);
    	$this->order('income_count_level asc');
    	$re = $this->select();
    	if(!$re){
    		$this->where('income_agent_id',$agentinfo['agent_top_agentid']);
    		$this->order('income_count_level asc');
    		$re = $this->select();
    		if(!$re){
    			$this->where('income_agent_id',0);
    			$this->order('income_count_level asc');
    			$re = $this->select();
    		}
    	}

    	if($re){
    		foreach ($re as $val) {
	            if ($agentinfo['agent_promote_count'] >= $val['income_promote_count']) {
	                $return = $val;
	            }
	        }

    	}

    	return $return;
    }

    public function setIncomeConfig($configArray){
        return $this->saveAll($configArray);
    }
}