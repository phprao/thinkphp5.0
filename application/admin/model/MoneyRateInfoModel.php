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
 * 金币比例
 * Class MoneyRateInfoModel
 * @package app\admin\model
 * @author ChangHai Zhan
 */
class MoneyRateInfoModel extends Model
{
    /**
     * 人民币单位 元
     */
    const MONEY_RATE_UNIT_TYPE_YUAN = 1;
    /**
     * 人民币单位 角
     */
    const MONEY_RATE_UNIT_TYPE_DIME = 2;
    /**
     * 人民币单位 分
     */
    const MONEY_RATE_UNIT_TYPE_CENT = 3;
    /**
     * @var string
     */
    public $table = 'dc_money_rate_info';

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
     * @return bool|float|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getToCentMoney()
    {
        $this->order('money_rate_id', 'desc');
        $model = $this->find();
        if (!$model) {
            return false;
        }
        return $this->toCondition($model);
    }

    /**
     * @param $model
     * @return float|int
     */
    public function toCondition($model)
    {
        switch ($model->money_rate_unit_type) {
            case self::MONEY_RATE_UNIT_TYPE_YUAN :
                return ($model->money_rate_value * $model->money_rate_unit) / 100;
                break;
            case self::MONEY_RATE_UNIT_TYPE_DIME :
                return ($model->money_rate_value * $model->money_rate_unit) / 10;
                break;
            case self::MONEY_RATE_UNIT_TYPE_CENT :
                return ($model->money_rate_value * $model->money_rate_unit);
                break;
            default :
                return 0;
        }
    }
}