<?php
/*
 *
 */

namespace app\admin\model;

use app\common\model\Model;

/**
 * 资金流水
 * Class AgentInfoModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class PlayerInfoLogModel extends Model
{

    /**
     * @var string
     */
    public $table = 'dc_player_info_log';

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

    /**
     * @param $condition
     */
    public function delData($condition)
    {
        return $this->where($condition)->delete();

    }

    /**
     * @param $condition
     * @param null $money_type
     * @param null $log_type
     * @param $money
     * @param $today
     * @param $endtime
     * @return float|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getStatisticalTotalGold($condition, $money_type = null, $log_type = null, $money, $today, $endtime)
    {
        $data = 0;
        if (is_array($condition)) {
            $this->where('player_log_player_id', 'in', $condition);
            $this->where('player_log_money_type', $money_type);
            $this->where('player_log_type', $log_type);
            $this->where('player_log_add_time', '>=', $today);
            $this->where('player_log_add_time', '<=', $endtime);
            $this->field(['SUM(' . $money . ') as money']);
            $results = $this->find();
            $data = $results['money'];
        } else {
            $data = $this->where($condition)->sum($money);
        }

        return $data;
    }


}

















