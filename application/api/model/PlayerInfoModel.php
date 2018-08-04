<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/17
 * Time: 15:03
 */
namespace app\api\model;

use app\common\model\Model;

/**
 * 用户信息
 * Class PlayerInfoModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class PlayerInfoModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_player_info';

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
     * @param $player_id
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPlayerinfoOne($player_id)
    {

        $this->where('player_id',$player_id);
        return $this->find();

    }


    /**
     * 获取用户信息
     * @param $id
     * @param array $field
     * @return array|false|\PDOStatement|string|\think\Collection|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPlayerInfoById($id, $field = [])
    {
        $this->field($field);
        if (is_array($id)) {
            $this->whereIn('player_id', $id);
            return $this->select();
        } else {
            $this->where('player_id', $id);
            return $this->find();
        }
    }

    /**
     * 显示金币
     * @param $value
     * @param $data
     * @return string
     */
    public function getPlayerCoinsTextAttr($value, $data)
    {
        if($data['player_coins'] >= 100000000){
            return sprintf("%.4f", $data['player_coins'] / 100000000) . '亿';
        }elseif($data['player_coins'] >= 10000){
            return sprintf("%.2f", $data['player_coins'] / 10000) . '万';
        }else{
            return sprintf("%.2f", $data['player_coins'] / 10000) . '万';
        }
    }

    /**
     * @return \think\model\relation\HasOne
     */
    public function playerAgentInfo()
    {
        return $this->hasOne('AgentInfoModel', 'agent_player_id', 'player_id');
    }

    /**
     * @param $playerId
     * @param $money
     * @return int|true
     * @throws \think\Exception
     */
    public function updatePlayerCoins($playerId, $money)
    {
        return $this->where('player_id', $playerId)->setInc('player_coins' , $money);
    }
}