<?php

/**
 *lionel
 *
 */

namespace app\admin\model;

use app\common\model\Model;


class NoticeReadModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_notice_read';
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
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSelect($condition)
    {
        return $this->where($condition)->select();
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
    public function saveConf($id, $condition)
    {
        $this->where('notice_read_id', $id);
        return $this->isUpdate(true)->save($condition);
    }

    /**
     * @param $condition
     * @return $this
     */
    public function getUpdate($condition, $data)
    {
        return $this->where($condition)->update($data);
    }

    /**
     * @param $notice_id
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getInselect($notice_id)
    {
        $data = $this->where('notice_read_notice_id','not in',$notice_id)->select();
        return  $data;
    }


}















