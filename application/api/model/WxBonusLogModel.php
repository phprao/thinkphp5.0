<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-04-16 15:31:10
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 微信红包
 +---------------------------------------------------------- 
 */

namespace app\api\model;

use app\common\model\Model;

class WxBonusLogModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_wx_bonus_log';

	const STATUS_UNDONE    = 0; // 未处理
	const STATUS_SENDING   = 1; // 发放中
	const STATUS_SENT      = 2; // 已发放待领取
	const STATUS_FAILED    = 3; // 发放失败
	const STATUS_RECEIVED  = 4; // 已领取
	const STATUS_RFUND_ING = 5; // 退款中
	const STATUS_REFUND    = 6; // 已退款

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

    public function insertOne($data){
        $this->data($data);
        $re = $this->save();
        if($re){
            return $this->id;
        }else{
            return false;
        }
    }

    /**
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getfind($condition)
    {
        return  $this->where($condition)->find();
    }

}