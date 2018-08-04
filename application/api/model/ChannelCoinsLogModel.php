<?php
/**
 * +----------------------------------------------------------
 * date: 2018-04-16 15:31:10
 * +----------------------------------------------------------
 * author: Raoxiaoya
 * +----------------------------------------------------------
 * describe: dc_channel_coins_log  渠道金币变化日志
 * +----------------------------------------------------------
 */

namespace app\api\model;

use app\common\model\Model;

class ChannelCoinsLogModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_channel_coins_log';

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
            return $this->id;
        } else {
            return false;
        }
    }

    public function getOne($condition)
    {
        return  $this->where($condition)->find();
    }

}



















