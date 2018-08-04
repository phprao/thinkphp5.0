<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-04-13 09:55:51
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 后台操作记录
 +---------------------------------------------------------- 
 */

namespace app\admin\model;
use app\common\model\Model;
use app\common\components\Helper;

class UsersActionLogModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_users_action_log';
	const  ACTION_ADD    = 1; // 新增
	const  ACTION_MODIFY = 2; // 修改
	const  ACTION_DEL    = 3; // 删除

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

    public function addActionLog($user_id, $action_type, $action_name, $action_before = '', $action_after = ''){
    	if(!$user_id){
    		return false;
    	}
    	if(!$action_type || !in_array($action_type, [self::ACTION_ADD,self::ACTION_MODIFY,self::ACTION_DEL])){
    		return false;
    	}
    	if(!$action_name){
    		return false;
    	}
    	$data = [
			'log_user_id'       =>$user_id,
			'log_action_type'   =>$action_type,
			'log_action_name'   =>$action_name,
			'log_action_before' =>$action_before,
			'log_action_after'  =>$action_after,
			'log_action_ip'		=>Helper::get_real_ip(),
			'log_add_time'      =>time(),
			'log_add_date'      =>date('Y-m-d H:i:s')
    	];

    	$this->data($data);
    	return $this->save();
    }
    

}