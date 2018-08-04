<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-02-11 14:06:34
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 数据统计数据模型
 +---------------------------------------------------------- 
 */
namespace app\admin\model;

use app\common\model\Model;


class StatisticsTotalModel extends Model{
	public $table = 'dc_statistics_total';

    // 统计类型
    const MODE_ALL          = 0; //所有的
    const MODE_CHARGE_SUM   = 1; //1-充值金额（分）
    const MODE_NEW_REGISTER = 2; //2-注册用户数（个）
    const MODE_COIN_COST    = 3; //3-金币消耗数（个）
    const MODE_LOGIN_IN     = 4; //4-活跃玩家数（个）
    const MODE_SEND_COIN    = 5; //5-赠送金币数（个）
    const MODE_GAME_PLAYER  = 6; //6-游戏玩家数（个）
    const MODE_PRODUCE_SUM  = 7; //7-产出金币数（充值+赠送）
    const MODE_LAST_SUM     = 8; //8-玩家剩余金币数（按天统计）
    const MODE_EARN_SUM     = 100; //100-金币消耗产生的总收益

    // 统计时间段
    const TYPE_HOUR = 1;// 小时
    const TYPE_DAY  = 2;// 天
    const TYPE_ALL  = 3;// 所有的

	public static function model($data = [], $className = __CLASS__)
    {
        return parent::model($data, $className);
    }

    public function getFieldOne($condition){
        $data = $this->where($condition)->field('statistics_sum')->find();
        return $data;
    }


    public function getOne($condition){
        $data = $this->where($condition)->find();

        return $data;
    }
    /**
     * 数据总览-所有
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function getStatisticsDataAll($condition){
    	$this->where('statistics_role_type', $condition->role_type);
    	$this->where('statistics_role_value', $condition->role_value);
    	$this->where('statistics_mode', $condition->modeSet);
    	$this->where('statistics_type', $condition->type);
    	$this->field([
            'SUM(statistics_sum) as statistics_sum'
        ]);
        $re = $this->find();
        return $re['statistics_sum'] ? $re['statistics_sum'] : 0;
    }
    /**
     * 数据总览-按天查看
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function getStatisticsDataDay($condition){
    	$this->where('statistics_role_type', $condition->role_type);
    	$this->where('statistics_role_value', $condition->role_value);
    	$this->where('statistics_mode', $condition->modeSet);
    	$this->where('statistics_type', $condition->type);
    	$this->where('statistics_timestamp','>=',$condition->start);
        if(isset($condition->end)){
            $this->where('statistics_timestamp','<=',$condition->end);
        }
    	$this->field([
            'statistics_sum',
            'statistics_timestamp'
        ]);
        $re = $this->select();
        return $re;
    }

    /**
     * 收益统计
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function getStatisticsDataAllIncome($condition){
        $this->where('statistics_role_type', $condition->role_type);
        $this->where('statistics_role_value', $condition->role_value);
        $this->where('statistics_mode', self::MODE_COIN_COST);
        $this->where('statistics_type', self::TYPE_ALL);
        $this->field([
            'statistics_sum',
            'statistics_money_rate'
        ]);
        $re = $this->select();
        return $re;
    }

    /**
     * @param $condition
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPromotersInfo($condition){
        $data = $this->where($condition)->select();
        return $data;
    }

    /**
     * @param $condition
     * @return float|int
     */
    public function getPromotersSumInfo($condition){
        return $this->where($condition)->Sum('statistics_sum');
    }

}







