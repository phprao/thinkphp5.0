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
 * 充值日志
 * Class PayRecordModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class PayRecordModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_pay_record';
    /**
     * 支付类型 微信
     */
    const RECORE_TYPE_WECHAT = 1;
    /**
     * 支付类型 支付宝
     */
    const RECORE_TYPE_ALIPAY = 2;
    //苹果支付
    const RECORE_TYPE_IPHONE = 3;
    //web支付
    const RECORE_TYPE_WEB = 4;

    /**
     * 充值钱的类型 rmb
     */
    const RECORE_STATE_RMB = 1;
    /**
     * 充值前的类型 金币
     */
    const RECORE_STATE_GOLD = 2;

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
     * 统计注册金额
     * @param null $condition
     * @param string $totalField
     * @param int $recoreState
     * @return float|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTotalMoney($condition = null, $totalField = 'recore_price', $recoreState = self::RECORE_STATE_GOLD)
    {
        if (isset($condition->superAgentId)) {
            $sql = PromotersInfoModel::model()->getPlayerIdBySuperAgentIdSql($condition->superAgentId);
            $this->whereExp('recore_player_id', 'IN (' . $sql . ')');
        }
        $this->where('recore_state', $recoreState);
        return $this->sum($totalField);
    }

    /**
     * @param $condition
     * @param null $recore_type
     * @param null $recore_state
     * @param null $sumttyp
     * @param null $today
     * @param null $endtime
     * @return float|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTotalStatisticsGold($condition, $recore_type = null, $recore_state = null, $sumtyp = null, $today = null, $endtime = null)
    {
        $data = 0;
        if (is_array($condition)) {
            if ($recore_type) {
                $this->where('recore_type', $recore_type);
            }
            $this->where('recore_player_id', 'in', $condition);
            $this->where('recore_state', $recore_state);
            $this->where('recore_create_time', '>=', $today);
            $this->where('recore_create_time', '<=', $endtime);
            $this->field(['SUM(' . $sumtyp . ') as money']);
            $results = $this->find();
            $data = $results['money'];

        } else {
            $data = $this->where($condition)->sum($sumtyp);
        }


        return $data;

    }

    public function getPayRecords($condition, $page = 1, $pageSize = 6)
    {
        $this->alias('t');
        $this->field('t.recore_id,t.recore_player_id,t.recore_type,t.recore_price,t.recore_get_type,t.recore_get_price,t.recore_before_money,t.recore_order_id,t.recore_after_money,t.recore_create_time,t1.player_nickname');
        $this->join('dc_player t1', 't.recore_player_id = t1.player_id');
        if (isset($condition['keywords'])) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    // 't.recore_player_id' => $condition['keywords'],
                    't.recore_player_id' => ['like', '%'.$condition['keywords'].'%'],
                    
                    'url_decode(t1.player_nickname)' => ['like', '%'.$condition['keywords'].'%'],
                ]);
            });
        }
        unset($condition['keywords']);
        $records = $this
            ->where($condition)
            ->page($page, $pageSize)
            ->order('recore_id desc')
            ->select();
        $records = collection($records)->toArray();
        foreach ($records as $key => $value) {
            switch ((int)$value['recore_type']) {
                case self::RECORE_TYPE_WECHAT:
                    $records[$key]['pay_type'] = '微信';
                    break;
                case self::RECORE_TYPE_ALIPAY:
                    $records[$key]['pay_type'] = '支付宝';
                    break;
                case self::RECORE_TYPE_IPHONE:
                    $records[$key]['pay_type'] = '苹果支付';
                    break;
                case self::RECORE_TYPE_WEB:
                    $records[$key]['pay_type'] = 'web支付';
                    break;
            }
            $records[$key]['recore_player_nickname'] = urldecode($value['player_nickname']);
            $records[$key]['recore_create_time'] = date('Y-m-d H:i:s', $value['recore_create_time']);
            $records[$key]['rate'] = '1:' . $value['recore_get_price'] *100 / $value['recore_price'];
            $records[$key]['recore_price'] = $value['recore_price'] /100;
        }
        return $records;
    }

    public function getRecordsCount($condition)
    {
        $this->alias('t');
        $this->join('dc_player t1', 't.recore_player_id = t1.player_id');
        if (isset($condition['keywords'])) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                   't.recore_player_id' => ['like', '%'.$condition['keywords'].'%'],
                    'url_decode(t1.player_nickname)' => ['like', '%'.$condition['keywords'].'%'],
                ]);
            });
        }
        unset($condition['keywords']);
        return $this->where($condition)->count('*');
    }

    /**
     * @param $condition
     * @return float|int
     */
    public function getPriceSum($condition){
        $this->alias('t');
        $this->join('dc_player t1', 't.recore_player_id = t1.player_id');
        if (isset($condition['keywords'])) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    't.recore_player_id' => ['like', '%'.$condition['keywords'].'%'],
                    'url_decode(t1.player_nickname)' => ['like', '%'.$condition['keywords'].'%'],
                ]);
            });
        }
        unset($condition['keywords']);
        $data = $this->where($condition)->sum('recore_price');

        return $data;
    }
    /**
     * @param null $condition
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getIncomeBytime($condition = null)
    {

        $this->where($condition);
        $data = $this->sum('recore_price');

        return $data;
    }


    public function getSumPrice($condition = null, $player_id = null, $field = null, $today = null, $endtime = null, $order_is_send = null)
    {

        $data = 0;
        if (is_array($player_id)) {
            $this->where('recore_player_id', 'in', $player_id);
            $this->where('recore_create_time', '>=', $today);
            $this->where('recore_create_time', '<=', $endtime);
            $this->where('recore_get_type', '=', $order_is_send);
            $this->field(['SUM(' . $field . ') as money']);
            $results = $this->find();
            $data = $results['money'];
        } else {
            $data = $this->where($condition)->sum($field);
        }
        return $data;

    }


}