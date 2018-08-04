<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 10:52
 */

namespace app\admin\model;

use app\common\model\Model;

/**
 * 代理账户
 * Class AgentInfoModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class PartnerOrderModel extends Model
{

    /**
     * @var string
     */
    public $table = 'dc_partner_order';

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
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOne($condition)
    {
        $data = $this->where($condition)->find();

        return $data;
    }

    /**
     * @param $condition
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($condition)
    {
        $data = $this->where($condition)->select();

        return $data;

    }

    public function addData($tableName, $condition)
    {

    }

    public function updateData($tableName, $condition)
    {


    }

    /**
     * @param $condition
     */
    public function delData($condition)
    {
        return $this->where($condition)->delete();

    }


    /**
     * @param $condition
     * @param $field
     * @return float|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSumOrderPrice($condition = null, $player_id, $field, $today, $endtime, $order_is_send = null)
    {
        $data = 0;
        if (is_array($player_id)) {
            $this->where('order_player_id', 'in', $player_id);
            $this->where('order_update_time', '>=', $today);
            $this->where('order_update_time', '<=', $endtime);
            $this->where('order_is_send', '=', $order_is_send);
            $this->field(['SUM(' . $field . ') as money']);
            $results = $this->find();
            $data = $results['money'];
        } else {
            $data = $this->where($condition)->sum($field);
        }
        return $data;

    }

    /**
     * @param null $condition
     * @param $field
     * @param $today
     * @param $endtime
     * @param null $order_is_send
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderPrice($condition = null, $field, $today, $endtime, $order_is_send = null)
    {
        $this->where('order_update_time', '>=', $today);
        $this->where('order_update_time', '<=', $endtime);
        $this->where('order_is_send', '=', $order_is_send);
        $this->field(['SUM(' . $field . ') as money']);
        $results = $this->find();
        $data = $results['money'];
        return $data;

    }


}