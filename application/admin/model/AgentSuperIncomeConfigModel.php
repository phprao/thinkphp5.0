<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-03-01 11:02:20
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 特代分成比例配置
 +---------------------------------------------------------- 
 */

namespace app\admin\model;

use app\common\model\Model;

class AgentSuperIncomeConfigModel extends Model
{
	public $table       = 'dc_agent_super_income_config';
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
     * 根据渠道消耗计算分成比例
     * @param  [type] $channel_id [description]
     * @param  [type] $data       [description]
     * @return [type]             [description]
     */
    public function getSuperIncomeRate($config, $total)
    {
        foreach ($config as $value) {
            $super_condition = $value['super_condition'];
            $super_condition_compare = $value['super_condition_compare'];
            $super_share = $value['super_share'];
            switch ($super_condition_compare) {
                case '<':
                    if ($super_condition < $total) {
                        return $super_share;
                    }
                    break;
                case '<=':
                    if ($super_condition <= $total) {
                        return $super_share;
                    }
                    break;
                default:
                    return 0;
                    break;
            }
        }
        return 0;
    }
    /**
     * 获取渠道分成配置
     * @param  [type] $channel_id [description]
     * @return [type]             [description]
     */
    public function getSuperIncomeConfig($channel_id, $order = 'asc'){
    	$this->order('super_condition '.$order);
    	$this->where('super_agent_id',$channel_id);
    	$re = $this->select();
    	if(!$re){
    		$this->order('super_condition '.$order);
    		$this->where('super_agent_id',0);
    		$re = $this->select();
    	}

    	return $re;
    }

    public function configObjToArray($config){
    	$config_init = [];
        foreach($config as $conf){
            array_push($config_init,['super_id'=>$conf['super_id'],'super_agent_id'=>$conf['super_agent_id'],'super_condition'=>$conf['super_condition'],'super_condition_compare'=>$conf['super_condition_compare'],'super_share'=>$conf['super_share']]);
        }
        return $config_init;
    }

    public function setSuperIncomeConfig($configArray){
        return $this->saveAll($configArray);
    }
}