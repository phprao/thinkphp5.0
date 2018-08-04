<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-01-29 18:03:25
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 玩家游戏记录
 +---------------------------------------------------------- 
 */

namespace app\admin\model;

use app\common\model\Model;

class AgentIncomeDetailPlayerModel extends Model
{
	public $table        = 'dc_agents_statistics_player';
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
     * @param  array  $filter [description]
     * @return [type]         [description]
     */
    public function getListDetailPlayerByPage($condition = null){
        $this->alias('s');
        $this->join($this->table_player.' p', 'p.player_id = s.change_money_player_id');
        if (isset($condition->player_id)) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    's.change_money_player_id' => ['like', '%' . $condition->player_id . '%'],
                    's.change_money_game_name' => ['like', '%' . $condition->player_id . '%']
                ]);
            });
        }
        $this->where('s.change_money_parent_agents_id', $condition->agent_id);
        $this->where('s.change_money_my_tax','>', 0);
        $this->where('s.change_money_time','>=',$condition->start);
        $this->where('s.change_money_time','<=',$condition->end);
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

}