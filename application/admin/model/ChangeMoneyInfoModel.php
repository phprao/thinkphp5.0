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
 * 改变金钱日志
 * Class ChangeMoneyInfoModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class ChangeMoneyInfoModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_change_money_info';
    /**
     * 钱的类型 金币
     */
    const CHANG_MONEY_MONEY_TYPE_GOLD = 1;
    /**
     * 日志类型 新用户充值赠送
     */
    const CHANGE_MONEY_TYPE_REGISTER_REWARD = 4;
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
     * 添加日志
     * @param $params
     * @return int|string
     */
    public function addLogByPlayerId($params)
    {
        //数组转变量
        extract($params);
        if (!isset($change_money_player_id)) {
            return false;
        }
        if (!isset($change_money_player_club_id)) {
            $change_money_player_club_id = 0;
        }
        if (!isset($change_money_club_id)) {
            $change_money_club_id = 0;
        }
        if (!isset($change_money_club_room_id)) {
            $change_money_club_room_id = 0;
        }
        if (!isset($change_money_club_desk_no)) {
            $change_money_club_desk_no = 0;
        }
        if (!isset($change_money_club_desk_id)) {
            $change_money_club_desk_id = 0;
        }
        if (!isset($change_money_club_room_no)) {
            $change_money_club_room_no = 0;
        }
        if (!isset($change_money_game_id)) {
            $change_money_game_id = 0;
        }
        if (!isset($change_money_room_id)) {
            $change_money_room_id = 0;
        }
        if (!isset($change_money_desk_no)) {
            $change_money_desk_no = 0;
        }
        if (!isset($change_money_type)) {
            //没有日志类型
            return false;
        }
        if (!isset($change_money_money_type)) {
            $change_money_money_type = self::CHANG_MONEY_MONEY_TYPE_GOLD;
        }
        if (isset($change_money_money_value) && $change_money_money_value == 0) {
            //改变值 为零
            return false;
        }
        if (!isset($change_money_begin_value)) {
            $change_money_begin_value = 0;
        }
        if (!isset($change_money_param)) {
            $change_money_param = '';
        }
        $data['change_money_player_id'] = $change_money_player_id;
        $data['change_money_player_club_id'] = $change_money_player_club_id;
        $data['change_money_club_id'] = $change_money_club_id;
        $data['change_money_club_room_id'] = $change_money_club_room_id;
        $data['change_money_club_desk_no'] = $change_money_club_desk_no;
        $data['change_money_club_desk_id'] = $change_money_club_desk_id;
        $data['change_money_club_room_no'] = $change_money_club_room_no;
        $data['change_money_game_id'] = $change_money_game_id;
        $data['change_money_room_id'] = $change_money_room_id;
        $data['change_money_desk_no'] = $change_money_desk_no;
        $data['change_money_type'] = $change_money_type;
        $data['change_money_tax'] = 0;
        $data['change_money_money_type'] = $change_money_money_type;
        $data['change_money_money_value'] = $change_money_money_value;
        $data['change_money_begin_value'] = $change_money_begin_value;
        $data['change_money_after_value'] = $change_money_begin_value + $change_money_money_value;
        $data['change_money_time'] = time();
        $data['change_money_param'] = $change_money_param;
        return $this->insertGetId($data);
    }
}