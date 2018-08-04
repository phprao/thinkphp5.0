<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-03-27 17:58:31
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 渠道推广教程
 +---------------------------------------------------------- 
 */
namespace app\admin\model;

use app\common\model\Model;

class ChannelPromotionModel extends Model
{
    public $table = 'dc_channel_promotion';

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

    public function getInfo($condition)
    {
        $data = $this->where($condition)->find();
        return $data;
    }

    public function getList($condition)
    {
        $this->where('promotion_status',1);
        $this->where('promotion_channel_id',$condition->channel_id);
        if(isset($condition->keyword) && $condition->keyword){
            $this->where('promotion_desc','like','%' . $condition->keyword . '%');
        }
        return $this->paginate($condition->size);
    }

    //删除方法
    public function delInfo($condition)
    {
        $res = $this->where($condition)->delete();
        return $res;
    }


}