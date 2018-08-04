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
 * 玩家战绩
 * Class AgentInfoModel
 * @package app\api\model
 * @author
 */
class GameBeatRecordModel extends Model
{

    /**
     * @var string
     */
    public $table = 'dc_game_beat_record';

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
     * @param $field
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
     * @param $condition 条件
     * @param $page 页数
     * @param $pageSize 一页的记录数
     * @param $order 排序
     * @return array
     */
    public function getPlayerBeat($condition, $page = 1, $pageSize = 6,$order)
    {

        if (isset($condition['keywords'])) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    'game_beat_player_id' => ['like', '%' . $condition['keywords'] . '%'],
                    'url_decode(game_beat_player_nick)' => ['like', '%' . $condition['keywords'] . '%']
                ]);
            });
        }
        unset($condition['keywords']);
        $this->field('game_beat_id,game_beat_board_id,game_beat_player_id,game_beat_player_nick,game_beat_room_no,game_beat_over_time,game_beat_game_id,game_beat_game_name,game_beat_score_value,game_beat_room_id,game_beat_room_name');
        $data = $this->where($condition)
                    ->page($page, $pageSize)
                    ->order($order)
                    ->select();

        $data = collection($data)->toArray();

        return $data;
    }

    /**
     * 玩家战绩总记录数
     * @param $condition 条件
     * @return int
     */
    public function  getPlayerBeatCount($condition)
    {
        if (isset($condition['keywords'])) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    'game_beat_player_id' => ['like', '%' . $condition['keywords'] . '%'],
                    'url_decode(game_beat_player_nick)' => ['like', '%' . $condition['keywords'] . '%']
                ]);
            });
        }
        unset($condition['keywords']);
        return $this->where($condition)->count('*');

    }

}