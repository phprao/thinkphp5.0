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
class GameRoundDayModel extends Model
{

    /**
     * @var string
     */
    public $table = 'dc_game_round_day';

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
     * 各游戏局数统计(前7天的数据)
     * @param $start_time 开始时间
     * @param $end_time 结束时间
     * @param $game_id  游戏id
     * @param $field    选取字段
     * @return float|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function gameRoundDay($start_time,$end_time,$game_id,$field,$channel_id = 0)
    {
        $this->where('game_round_createtime', '>=', $start_time);
        $this->where('game_round_createtime', '<', $end_time);
        $this->where('game_round_game_id',    '=', $game_id);
        $this->where('game_round_channel_id', '=', $channel_id);
        $this->field($field);
        $results = $this->find();

        return $results;
    }

    /**
     * @param $start_time
     * @param $game_id
     * @param $field
     * @param int $channel_id
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function gameRoundDayList($start_time,$game_id,$field,$channel_id = 0)
    {
        $this->where('game_round_timestamp', '=', $start_time);
        $this->where('game_round_game_id',    '=', $game_id);
        $this->where('game_round_channel_id', '=', $channel_id);
        $this->field($field);
        $results = $this->find();

        return $results;
    }

}