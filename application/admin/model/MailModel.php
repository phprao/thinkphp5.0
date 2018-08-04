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

class MailModel extends Model
{
    public $table = 'dc_mail';

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

        if(isset($condition->title) && $condition->title){
            $this->where('mail_title', 'like', '%'.$condition->title.'%');
        }
        if(isset($condition->keyword) && $condition->keyword){
            $this->where('mail_receiver_id', 'like', '%'.$condition->keyword.'%');
        }
        if(isset($condition->star_id)){
            $this->where('mail_star_id', $condition->star_id);
        }
        if(isset($condition->channel_id)){
            $this->where('mail_channel_id', $condition->channel_id);
        }
        if(isset($condition->start)){
            $this->where('mail_create_time', '>=', $condition->start);
        }
        if(isset($condition->start)){
            $this->where('mail_create_time', '<', $condition->end);
        }
        
        // $this->whereIn('mail_status', [1,2,3]);
        
        $this->order('mail_id desc');

        if(isset($condition->size)){
            $data = $this->paginate($condition->size);
        }else{
            $data = $this->select();
        }

        return $data;
    }

}



















