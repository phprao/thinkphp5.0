<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-03-23 14:36:09
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 推广管理
 +---------------------------------------------------------- 
 */
namespace app\admin\model;

use app\common\model\Model;

class ChannelPromotion extends Model{

    public $table = 'dc_channel_promotion';

    public function getList($condition)
    {
    	$this->where('promotion_status',1);
    	if(isset($condition->channel_id)){
    		$this->where('promotion_channel_id',$condition->channel_id);
    	}else{
    		return false;
    	}
    	if(isset($condition->size)){
    		return $this->paginate($condition->size);
    	}else{
    		return $this->select();
    	}
    	
    }
}