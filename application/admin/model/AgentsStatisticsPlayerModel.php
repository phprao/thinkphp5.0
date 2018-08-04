<?php
/**
 * +----------------------------------------------------------
 * date: 2018-01-29 18:03:25
 * +----------------------------------------------------------
 * author: Raoxiaoya
 * +----------------------------------------------------------
 * describe: 玩家游戏记录
 * +----------------------------------------------------------
 */

namespace app\admin\model;

use app\common\model\Model;

class AgentsStatisticsPlayerModel extends Model
{
    public $table = 'dc_agents_statistics_player';
    public $table_player = 'dc_player';

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
     * 分页查找--游戏记录
     * @param  array $filter [description]
     * @return [type]         [description]
     */
    public function getListDetailPlayerByPage($condition = null)
    {
        $this->alias('s');
        $this->join($this->table_player . ' p', 'p.player_id = s.change_money_player_id');
        if (isset($condition->player_id)) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    's.change_money_player_id' => ['like', '%' . $condition->player_id . '%'],
                    's.change_money_game_name' => ['like', '%' . $condition->player_id . '%']
                ]);
            });
        }
        $this->where('s.change_money_parent_agents_id', $condition->agent_id);
        $this->where('s.change_money_my_tax', '>', 0);
        $this->where('s.change_money_time', '>=', $condition->start);
        $this->where('s.change_money_time', '<=', $condition->end);
        $this->field([
            'p.player_nickname',
            's.change_money_player_id',
            's.change_money_tax',
            's.change_money_my_tax',
            's.change_money_game_id',
            's.change_money_game_name',
            's.change_money_share_rate',
            's.change_money_date'
        ]);

        return $this->paginate($condition->size);
    }

    /**
     * 金币变化记录
     * @return [type] [description]
     */
    public function getCoinChangeList($condition = null)
    {
        if(isset($condition->start)){
            $this->where('change_money_time', '>=', $condition->start);
        }
        if(isset($condition->end)){
            $this->where('change_money_time', '<', $condition->end);
        }
        if (isset($condition->keyword)) {
            $this->whereIn('change_money_player_id', (array)$condition->keyword);
        }
        if (isset($condition->starid)) {
            $this->where('change_money_parent_agents_id', $condition->starid);
        }
        if (!isset($condition->starid) && isset($condition->channel)) {
            $this->where('change_money_super_agents_id', $condition->channel);
        }
        if (isset($condition->game_id)) {
            $this->where('change_money_game_id', $condition->game_id);
        }
        // 0-全部，1-服务费消耗，2-游戏盈利，3-游戏失利，4-系统赠送，5-充值获得，8-新手礼包，9-系统添加
        if($condition->type > 0){
            if($condition->type == 1){
                $this->where('change_money_tax', '>', 0);
            }
            elseif($condition->type == 2){
                $this->where('change_money_money_value', '>', 0);
            }
            elseif($condition->type == 3){
                $this->where('change_money_money_value', '<', 0);
            }
            elseif($condition->type == 5){
                $this->where('change_money_type', 1);
            }
            else{
                $this->where('change_money_type', $condition->type);
            }
        }
        
        $this->field([
            'change_money_player_id',
            'change_money_parent_agents_id',
            'change_money_super_agents_id',
            'change_money_game_name',
            'change_money_room_name',
            'change_money_club_desk_id',
            'change_money_begin_value',
            'change_money_money_value as change_money_value',
            '(change_money_tax / 100) as change_money_tax',
            'change_money_after_value',
            'change_money_type',
            'change_money_date'
        ]);
        $this->order('change_money_time DESC');
        return $this->paginate($condition->size);
    }

    /**
     * 金币变化总计
     * @return [type] [description]
     */
    public function getCoinChangeSum($condition, $mode){
        if(isset($condition->start)){
            $this->where('change_money_time', '>=', $condition->start);
        }
        if(isset($condition->end)){
            $this->where('change_money_time', '<', $condition->end);
        }
        if (isset($condition->keyword)) {
            $this->whereIn('change_money_player_id', (array)$condition->keyword);
        }
        if (isset($condition->starid)) {
            $this->where('change_money_parent_agents_id', $condition->starid);
        }
        if (!isset($condition->starid) && isset($condition->channel)) {
            $this->where('change_money_super_agents_id', $condition->channel);
        }
        if (isset($condition->game_id)) {
            $this->where('change_money_game_id', $condition->game_id);
        }
        
        if($mode == 1){
            return $this->sum('change_money_tax / 100');
        }elseif($mode == 2){
            // 0-全部，1-服务费消耗，2-游戏盈利，3-游戏失利，4-系统赠送，5-充值获得
            if($condition->type == 0){
                return $this->sum('change_money_money_value');
            }
            elseif($condition->type == 1){
                return $this->sum('change_money_tax / 100');
            }
            elseif($condition->type == 2){
                $this->where('change_money_type',2);
                $this->where('change_money_money_value', '>', 0);
                return $this->sum('change_money_money_value');
            }
            elseif($condition->type == 3){
                $this->where('change_money_type',2);
                $this->where('change_money_money_value', '<', 0);
                return $this->sum('change_money_money_value');
            }
            elseif($condition->type == 5){
                $this->where('change_money_type',1);
                return $this->sum('change_money_money_value');
            }
            else{
                $this->where('change_money_type',$condition->type);
                return $this->sum('change_money_money_value');
            }
        }
    }

    /**
     * @param null $player_id
     * @param null $change_money_type
     * @param null $money_type
     * @param null $money_value
     * @param null $today
     * @param null $endtime
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getStatisticalGold($player_id = null, $change_money_type = null, $money_type = null, $money_value = null, $today = null, $endtime = null)
    {
        if(is_null($player_id)){
            $this->where('change_money_time', '>=', $today);
            $this->where('change_money_time', '<=', $endtime);
            $this->where('change_money_game_id', '=', $player_id);
            $this->where('change_money_type', '=', $change_money_type);
            $this->where('change_money_money_type', '=', $money_type);
            $this->field(['SUM(' . $money_value . ') as money']);
            $results = $this->find();
            $data = $results['money'];
            return $data;
        }else{

            $this->where('change_money_time', '>=', $today);
            $this->where('change_money_time', '<=', $endtime);
            $this->where('change_money_type', '=', $change_money_type);
            $this->where('change_money_money_type', '=', $money_type);
            $this->field(['SUM(' . $money_value . ') as money']);
            $results = $this->find();
            $data = $results['money'];
            return $data;
        }

    }

    /**
     * 上周金币的消耗情况
     * @param null $condition
     * @param change_money_money_type 1
     * @param change_money_type 2
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoldBytime($condition = null)
    {
        //附加条件
        $condition['change_money_money_type'] = 1;
        $condition['change_money_type'] = 2;

        $this->where($condition);
        $data = $this->sum('change_money_my_tax');

        return $data;
    }

    /**
     * 获取代理收益明细
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function getPlayerCostDetail($condition = null){
        if(isset($condition->start)){
            $this->where('change_money_time', '>=', $condition->start);
        }
        if(isset($condition->end)){
            $this->where('change_money_time', '<', $condition->end);
        }
        
        if(isset($condition->agent_id)) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    'change_money_parent_agents_id' => $condition->agent_id,
                    'change_money_one_agents_id'    => $condition->agent_id,
                    'change_money_two_agents_id'    => $condition->agent_id
                ]);
            });
        }

        $this->where('change_money_tax', '>', 0);
        $this->whereIn('change_money_type', [2,3]);
        
        $this->field([
            'change_money_parent_agents_id',
            'change_money_player_id',
            'change_money_game_name',
            'change_money_tax',
            'change_money_my_tax',
            'change_money_share_rate',
            'change_money_one_agents_id',
            'change_money_one_tax',
            'change_money_one_rate',
            'change_money_two_agents_id',
            'change_money_two_tax',
            'change_money_two_rate',
            'change_money_date'
        ]);
        $this->order('change_money_time DESC');
        return $this->paginate($condition->size);
    }


}














