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
 * 游戏分类
 * Class AgentInfoModel
 * @package app\api\model
 */
class GameKindModel extends Model
{

    /**
     * @var string
     */

    public $table = 'dc_game_kind';

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
     * @return array
     */
    public function getOne($condition)
    {
        $data = $this->where($condition)->find();

        return $data;
    }

    /**
     * @param $condition
     * @return array
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
     * @return boolean
     */
    public function delData($condition)
    {
        return $this->where($condition)->delete();

    }

    /**
     * 游戏分类
     * @param $condition
     * @param $field
     * @return array
     */
    public function getGameKind($field,$condition = null)
    {
        $data = $this->field($field)->where($condition)->select();
        return $data;
    }
}