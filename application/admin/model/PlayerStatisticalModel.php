<?php


namespace app\admin\model;

use app\common\model\Model;

/**
 * 用户信息表
 * Class PromotersInfoModel
 * @package app\admin\model
 * @author
 */
class PlayerStatisticalModel extends Model
{
    /**
     * 统计类型 金币
     */
    const STATISTICAL_TYPE_MONEY = 1;
    /**
     * 统计类型 人数
     */
    const STATISTICAL_TYPE_NUMBER = 2;
    /**
     * @var string
     */
    protected $table = 'dc_player_statistical';
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


    public function getInLins($playerid){

        return  $this->where('statistical_player_id','in',$playerid)->select();
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
     * @param $condition
     * @param $money_type
     * @return int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getStatisticalTotalGold($condition, $money_type,$money_status = null,$statistical_type = null)
    {
        $data = 0;
        if (is_array($condition)) {
            if(!is_null($money_status)){
                $this->where('statistics_award_money_status', $money_status);
            }
            if(!is_null($statistical_type)){
                $this->where('statistical_type', $statistical_type);
            }
            $this->where('statistical_player_id', 'in', $condition);
            $this->field(['SUM(' . $money_type . ') as money']);
            $results = $this->find();
            $data = $results['money'];
        }
        return $data;
    }

    /**
     * 统计游戏消耗金币数量
     * @param null $condition
     * @return float|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTotalGold($condition = null)
    {
        if (isset($condition->superAgentId)) {
            $sql = PromotersInfoModel::model()->getPlayerIdBySuperAgentIdSql($condition->superAgentId);
            $this->whereExp('statistical_player_id', 'IN (' . $sql . ')');
        }
        $this->where('statistical_type', self::STATISTICAL_TYPE_MONEY);
        return $this->sum('statistical_value');
    }


}

