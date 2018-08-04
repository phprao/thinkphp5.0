<?php
/**
 * +----------------------------------------------------------
 * date: 2018-01-29 18:03:25
 * +----------------------------------------------------------
 * author: Raoxiaoya
 * +----------------------------------------------------------
 * describe: 玩家游戏记录
 * +----------------------------------------------------------
 */

namespace app\admin\model;

use app\common\model\Model;

class AgentsStatisticsHourModel extends Model
{
    public $table = 'dc_agents_statistics_hour';


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
     * @return float|int
     */
    public function getSum($condition)
    {
        return $this->where($condition)->sum('statistics_money_data');

    }


}














