<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:44
 * @author ChangHai Zhan
 */

namespace app\admin\model;

use app\common\model\Model;

/**
 * 关系推广
 * Class PromotersInfoModel
 * @package app\admin\model
 * @author ChangHai Zhan
 */
class PromotersInfoModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'dc_promoters_info';

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
     * 获取特代下的玩家Id Sql
     * @param $agentId
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPlayerIdBySuperAgentIdSql($agentId)
    {
        $this->where('promoters_agent_top_agentid', $agentId);
        $this->field('promoters_player_id');
        return $this->select(false);
    }

    /**
     * 代理下的玩家Id Sql
     * @param $agentId
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPlayerIdByAgentIdSql($agentId)
    {
        $this->where('promoters_agent_id', $agentId);
        $this->field('promoters_player_id');
        return $this->select(false);
    }

    /**
     * 玩家下的玩家Id Sql
     * @param $playerId
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPlayerIdByPlayerIdSql($playerId)
    {
        $this->where('promoters_parent_id', $playerId);
        $this->field('promoters_player_id');
        return $this->select();
    }


    /**
     * @param $parent_id
     * @return false|int|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPlayerIdList($parent_id)
    {

        $data = 0;
        if (is_array($parent_id)) {
            $this->where('promoters_parent_id', 'in', $parent_id);
            $data = $this->select();

        }
        return $data;

    }


    public function getParentList($parent_id)
    {

        $data = 0;
        if (is_array($parent_id)) {
            $this->where('promoters_parent_id', 'in', $parent_id);
            $data = $this->select();

        }
        return $data;

    }


    public function getParentCount($parent_id, $count = '*')
    {

        if (is_array($parent_id)) {
            $this->where('promoters_parent_id', 'in', $parent_id);
            $data = $this->count($count);
        } else {
            $this->where('promoters_parent_id', $parent_id);
            $data = $this->count($count);
        }

        return $data;

    }


    /**
     * @param $condition
     * @return int|string
     * 统计
     */
    public function getCount($condition){
        $this->where($condition);
        $data = $this->count('*');
        return $data;
    }

    /**
     * @param null $condition
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPlayerBytime($condition = null)
    {
        $this->where($condition);
        $data = $this->count();

        return $data;
    }


    /**
     * @param $condition
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getplayinfo($condition)
    {
        $this->where($condition);
        return $this->select();
    }

    /***
     * @param $playerId
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function getAssociatedPlayerId($playerId)
    {
        $this->alias('a');
        $this->where('promoters_parent_id', $playerId);
        $this->join('dc_player b', 'a.promoters_player_id = b.player_id');
        $this->field(['promoters_player_id', 'player_nickname']);

        return $this->find();
    }





    /**
     * 玩家星级推广员
     * @param $playerId
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getParentIdByPlayerId($playerId)
    {
        $this->alias('a');
        $this->where('promoters_player_id', $playerId);
        $this->join('dc_player b', 'a.promoters_parent_id = b.player_id');
        $this->field(['promoters_parent_id', 'player_nickname']);

        return $this->find();
    }


    /**
     * @param null $condition
     * @param int $page
     * @param int $pageSize
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getParentIdByPlayerInfo($condition = null, $page = 1, $pageSize = 6)
    {
        $this->alias('t');
        $this->join('dc_player t1', 't.promoters_player_id = t1.player_id');
        if (isset($condition['keyword'])) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    'url_decode(t1.player_nickname)' => ['like', '%' . $condition['keyword'] . '%'],
                    't1.player_id' => ['like', '%' . $condition['keyword'] . '%']
                ]);
            });
        }
        unset($condition['keywords']);
        $records = $this
            ->where($condition)
            ->page($page, $pageSize)
            ->order('player_id desc')
            ->select();
        $records = collection($records)->toArray();

        return $records;

    }

    /**
     * @param $condition
     * @return int|string
     */
    public function getParentIdByPlayerCount($condition)
    {
        $this->alias('t');
        $this->join('dc_player t1', 't.promoters_player_id = t1.player_id');
        if (isset($condition['keyword'])) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    'url_decode(t1.player_nickname)' => ['like','%' . $condition['keyword'] . '%'],
                    't.agent_player_id' => ['like','%' . $condition['keyword'] . '%']
                ]);
            });
        }
        unset($condition['keyword']);
        return $this->where($condition)->count('*');
    }


}


