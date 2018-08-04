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

namespace app\api\model;

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
        if (isset($condition->player_id)) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    'change_money_player_id' => ['like', '%' . $condition->player_id . '%'],
                    'change_money_game_name' => ['like', '%' . $condition->player_id . '%']
                ]);
            });
        }
        $this->where('change_money_parent_agents_id', $condition->agent_id);
        $this->where('change_money_my_tax','>', 0);
        $this->where('change_money_time','>=',$condition->start);
        $this->where('change_money_time','<',$condition->end);
        $this->order('change_money_time desc');
        $this->field([
            'change_money_player_id',
            'change_money_tax',
            'change_money_my_tax',
            'change_money_game_id',
            'change_money_game_name',
            'change_money_share_rate',
            'change_money_date'
        ]);

        return $this->paginate($condition->size);
    }






}