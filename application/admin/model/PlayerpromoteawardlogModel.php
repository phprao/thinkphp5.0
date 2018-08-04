<?php


namespace app\admin\model;

use app\common\model\Model;

/**
 *
 * Class PromotersInfoModel
 * @package app\admin\model
 * @author
 */
class PlayerpromoteawardlogModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'dc_player_promote_award_log';

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
     * @return float|int
     */
    public function getSumAward($condition)
    {
        return $this->where($condition)->Sum('log_award');
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



}

