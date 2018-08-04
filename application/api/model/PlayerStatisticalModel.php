<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/17
 * Time: 15:01
 */

namespace app\api\model;

use app\common\model\Model;

/**
 * 玩家统计
 * Class PlayerStatisticalModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class PlayerStatisticalModel extends Model
{
    /**
     * 金币
     */
    const STATISTICAL_TYPE_MONEY = 1;
    /**
     * @var string
     */
    public $table = 'dc_player_statistical';


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
     * 获取玩家消耗记录
     * @param $playerId
     * @param null $condition
     * @param array $field
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getByPlayerId($playerId, $condition = null, $field = [])
    {
        $this->field($field);
        $this->where('statistical_player_id', $playerId);
        if (isset($condition->statistical_type)) {
            $this->where('statistical_type', $condition->statistical_type);
        }
        return $this->find();
    }

    /**
     * @param $plaer_id
     * @param null $conditino
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getFindone($plaer_id, $conditino = null)
    {
        return $this->where('statistical_player_id', $plaer_id)->find();
    }

    /**
     * @param $conditino
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getFind($conditino)
    {
        return $this->where($conditino)->find();
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