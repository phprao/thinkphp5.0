<?php
namespace app\admin\model;

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


}