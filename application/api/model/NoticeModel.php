<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 10:52
 */

namespace app\api\model;

use app\common\model\Model;

/**
 * 代理账户
 * Class AgentInfoModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class NoticeModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_notice';

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
     * @param string $count
     * @return int|string
     *
     */

    public function get_AgentAccountInsert($condition)
    {
        $data = $this->insert($condition);
        return $data;
    }

    /**
     * @param $condition
     */
    public function get_Select($condition, $order = null)
    {
        return $this->where($condition)->order($order)->select();
    }

    /**
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getInfo($condition)
    {
        $data = $this->where($condition)->find();
        return $data;
    }

    /**
     * @param $condition
     * @param $data
     * @return $this
     */
    public function updateInfo($condition, $data)
    {
        return $this->where($condition)->update($data);
    }


}