<?php
/**
 * +----------------------------------------------------------
 * date: 2018-04-16 15:31:10
 * +----------------------------------------------------------
 * author: Raoxiaoya
 * +----------------------------------------------------------
 * describe: 
 * +----------------------------------------------------------
 */

namespace app\admin\model;

use app\common\model\Model;

class DailyGiftLogModel extends Model
{
    public $table = 'dc_dailygift_log';

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
            return $this->mail_id;
        } else {
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

    public function getList($condition, $field = ['*']){
        $this->field($field);

        if(isset($condition->keyword) && $condition->keyword){
            $this->where('player_id', 'like', '%'.$condition->keyword.'%');
        }

        if(isset($condition->config_id)){
            $this->where('config_id', $condition->config_id);
        }
        
        if(isset($condition->start)){
            $this->where('create_time', '>=', $condition->start);
        }
        if(isset($condition->start)){
            $this->where('create_time', '<', $condition->end);
        }
        
        $this->order('id desc');

        if(isset($condition->size)){
            $data = $this->paginate($condition->size);
        }else{
            $data = $this->select();
        }

        return $data;
    }

}



















