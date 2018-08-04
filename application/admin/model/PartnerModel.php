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

class PartnerModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_partner';

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
            return $this->partner_id;
        } else {
            return false;
        }
    }

    public function getOne($condition)
    {
        return  $this->where($condition)->find();
    }

    public function getPartnerInfoByKeyword($condition)
    {
        if (isset($condition->channel)) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    'partner_id' =>   ['like', '%' . $condition->channel . '%'],
                    'partner_name' => ['like', '%' . $condition->channel . '%']
                ]);
            });
        }
        if(isset($condition->size)){
            return $this->paginate($condition->size);
        }else{
            return $this->select();
        }
    }

    public function getAllPartnerInfo($condition = null)
    {
        $this->order('create_time desc');
        if($condition){
            if(isset($condition->start) && $condition->start){
                $this->where('create_time','>=', $condition->start);
            }
            if(isset($condition->end) && $condition->end){
                $this->where('create_time','<', $condition->end);
            }
            if (isset($condition->keyword) && !is_array($condition->keyword)) {
                $this->where(function ($query) use ($condition) {
                    $query->whereOr([
                        'partner_id' =>   ['like', '%' . $condition->keyword . '%'],
                        'partner_name' => ['like', '%' . $condition->keyword . '%']
                    ]);
                });
            }
            if (isset($condition->keyword) && is_array($condition->keyword)) {
                $this->whereIn('partner_id',$condition->keyword);
            }
            if(isset($condition->field)){
                $this->field($condition->field);
            }
            $data = $this->paginate($condition->size);
        }else{
            $data = $this->select();
        }
        
        return $data;
    }
}



















