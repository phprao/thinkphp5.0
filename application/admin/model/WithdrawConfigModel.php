<?php

/**
 *lionel
 *
 */

namespace app\admin\model;

use app\common\model\Model;


class WithdrawConfigModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_withdraw_config';
    /**/

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

    /**
     * @param $condition
     * @return int|string
     */
    public function insertWithdrawConfig($condition)
    {
        $data = $this->insert($condition);
        return $data;
    }

    /**
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getinfo($condition)
    {
        return $this->where($condition)->find();

    }


    /**
     * @param array $id
     * @param array $condition
     * @return false|int
     */
    public function saveConf($id,$condition)
    {
        $this->where('id', $id);
        return $this->isUpdate(true)->save($condition);
    }

}















