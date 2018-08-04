<?php

namespace app\api\block;

use app\api\model\ChangeMoneyInfoModel;
use app\api\model\PlayerInfoModel;
use think\Db;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/31
 * Time: 11:41
 * @author ChangHai Zhan
 */
class MoneyBlock
{
    /**
     * 静态实例化
     * @param string $className
     * @return static active record model instance.
     */
    public static function block($className = __CLASS__)
    {
        return new $className();
    }

    /**
     * @param $playerId
     * @param $money
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function registerPlayerAwardMoney($playerId, $money, $param = [])
    {
        $logType = ChangeMoneyInfoModel::CHANGE_MONEY_TYPE_REGISTER_REWARD;
        $moneyType = ChangeMoneyInfoModel::CHANG_MONEY_MONEY_TYPE_GOLD;
        //事务开始
        Db::startTrans();
        if (!$model = PlayerInfoModel::model()->getPlayerInfoById($playerId)) {
            Db::rollback();
            return false;
        }
        if (!PlayerInfoModel::model()->updatePlayerCoins($playerId, $money)) {
            Db::rollback();
            return false;
        }
        //写入日志
        if (!$this->addLogByPlayerId($playerId, $model->player_coins, $money, $logType, $moneyType, $param)) {
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * 添加日志
     * @param $player_id
     * @param $befMoney
     * @param $money
     * @param $type
     * @param int $moneyType
     * @return bool
     */
    protected function addLogByPlayerId($player_id, $befMoney, $money, $type, $moneyType = ChangeMoneyInfoModel::CHANG_MONEY_MONEY_TYPE_GOLD, $param = [])
    {
        $params = [
            'change_money_player_id' => $player_id,
            'change_money_begin_value' => $befMoney,
            'change_money_money_value' => $money,
            'change_money_type' => $type,
            'change_money_money_type' => $moneyType,
            'change_money_param'=> empty($param) ? '' : json_encode($param),
        ];
        return ChangeMoneyInfoModel::model()->addLogByPlayerId($params);
    }
}