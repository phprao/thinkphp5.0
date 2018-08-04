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
 * 游戏列表
 * Class AgentInfoModel
 * @package app\api\model
 */
class GameInfoModel extends Model
{

    /**
     * @var string
     */
    public $table = 'dc_game_info';

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

    /**
     * @param $condition
     * @param string $field
     * @param string $order
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getListField($condition, $field = '*', $order = "id DESC")
    {
        return $this->where($condition)->field($field)->order($order)->select();
    }

    public function addData($tableName, $condition)
    {

    }

    public function updateData($tableName, $condition)
    {

    }

    /**
     * @param $condition
     * @return boolean
     */
    public function delData($condition)
    {
        return $this->where($condition)->delete();

    }


    /**
     * 游戏列表
     * @param $condition
     * @param $field
     * @return array
     */
    public function getGameInfo($field, $condition = null)
    {
        $data = $this->field($field)->where($condition)->select();
        return $data;
    }

    /**
     * 游戏分类
     * @param $condition
     * @param $field
     * @return array
     */
    public function getGameKind($field, $condition = null)
    {
        $data = $this->field($field)->where($condition)->select();
        return $data;
    }

}