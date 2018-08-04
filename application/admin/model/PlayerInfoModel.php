<?php


namespace app\admin\model;

use app\common\model\Model;

/**
 * 用户信息表
 * Class PromotersInfoModel
 * @package app\admin\model
 * @author
 */
class PlayerInfoModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'dc_player_info';

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

    /**\
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
     * @return int
     */
    public function delData($condition)
    {
        return $this->where($condition)->delete();

    }

    /**
     * 通用统计
     * @param $condition
     * @param string $count
     * @return int|string
     */
    public function getSumData($condition, $field = '')
    {
        if($field){
            return $this->where($condition)->sum($field);
        }
    }

    /**
     * @param $condition
     * @param $money_type
     * @return int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function getStatisticalTotalGold($condition = null, $money_type)
    {
        $data = 0;
        if (is_array($condition)) {
            $this->where('player_id', 'in', $condition);
            $this->field(['SUM(' . $money_type . ') as money']);
            $results = $this->find();
            $data = $results['money'];
        }else{
            $this->field(['SUM(' . $money_type . ') as money']);
            $results = $this->find();
            $data = $results['money'];

        }
        return $data;
    }


}

