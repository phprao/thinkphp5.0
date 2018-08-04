<?php
/**
 * +----------------------------------------------------------
 * date: 2018-04-16 15:31:10
 * +----------------------------------------------------------
 * author: Raoxiaoya
 * +----------------------------------------------------------
 * describe: dc_channel_gift  新手礼包-渠道
 * +----------------------------------------------------------
 */

namespace app\api\model;

use app\common\model\Model;

class ChannelGiftModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_channel_gift';

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

    public function insertOne($data)
    {
        $this->data($data);
        $re = $this->save();
        if ($re) {
            return $this->gift_id;
        } else {
            return false;
        }
    }

    public function getOne($condition)
    {
        return  $this->where($condition)->find();
    }

    public function getList($condition = null){
        if($condition){
            if(isset($condition->gift_status)){
                $this->where('gift_status', $condition->gift_status);
            }
            if(isset($condition->value_start)){
                $this->where('gift_value', '>=', $condition->value_start);
            }
            if(isset($condition->value_end)){
                $this->where('gift_value', '<=',$condition->value_end);
            }
            if(isset($condition->gift_channel_id)){
                $this->where('gift_channel_id', $condition->gift_channel_id);
            }
            $this->order('gift_status asc');
            $this->order('gift_time asc');
            return $this->paginate($condition->size);
        }else{
            return false;
        }

    }
    public function getGiftInfoByIdLock($gift_id){
        $this->where('gift_id',$gift_id);
        $this->lock(true);
        return  $this->find();
    }

}



















