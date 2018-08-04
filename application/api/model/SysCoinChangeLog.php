<?php
namespace app\api\model;

use app\common\model\Model;

class SysCoinChangeLog extends Model
{
    /**
     * @var string
     */
    protected $table = 'dc_sys_coin_change_log';

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

    public function getList($where, $condition){
        $this->where($where);
        if(isset($condition->last_id) && $condition->last_id){
            $this->where('id', '>', $condition->last_id);
        }
        return $this->paginate($condition->size);
    }

    public function getLogCountByPlayerIdLock($where, $cur_day){
        $this->where($where);
        $this->where('add_time', '>=', $cur_day);
        $this->lock(true);
        return  $this->count();
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


}