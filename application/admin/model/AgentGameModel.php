<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/17
 * Time: 10:24
 * @author liangjunbin
 */

namespace app\admin\model;

use app\common\model\Model;

/**
 * Class ClubInfoModel
 * @package app\admin\model
 */
class AgentGameModel extends Model
{

    protected $table = 'dc_agent_game';


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
     * @param $field
     * @param $condition
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($condition = null, $field = '*', $oredr = 'agent_game_id DESC')
    {
        return $this->field($field)->where($condition)->order($oredr)->select();

    }

    /**
     * @param null $condition
     * @param string $oredr
     * @param null $limit
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOneOrder($condition = null, $oredr = 'agent_game_id DESC', $limit = null)
    {
        return $this->where($condition)->order($oredr)->limit($limit)->find();
    }

    /**
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOne($condition)
    {
        return $this->where($condition)->find();

    }

    /**
     * @param $condition
     * @param array $field
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getInfo($condition, $field = [])
    {
        $this->where($condition);
        if ($field) {
            $this->field($field);
        }
        return $this->find();
    }

    /**
     * @param array $data
     * @return int|string
     */
    public function addData(array $data)
    {
        return $this->insert($data);
    }

    /**
     * @param array $data
     * @return int|string
     */
    public function insertID(array $data)
    {
        return $this->insertGetId($data);
    }

    /**
     * @param array $data
     * @param array $where
     * @return $this
     */
    public function updateData(array $data, $where)
    {
        return $this->where($where)->update($data);
    }

    /**
     * @param $condition
     * @param $data
     * @return false|int
     */
    public function updateDataStatus($condition, $data)
    {
        $this->where($condition);
        return $this->isUpdate(true)->save($data);
    }

    /**
     * @param $condition
     * @return int
     */
    public function delData($condition)
    {
        return $this->where($condition)->delete();

    }

    /**
     * @param $condition
     * @param string $field
     * @return float|int
     */
    public function getSum($condition, $field = 'to_value')
    {
        return $this->where($condition)->SUM($field);
    }

    /***
     * @param $condition
     * @param string $field
     * @return float|int
     */
    public function getAgentSum($condition,$field = 'to_value')
    {
        $this->alias('a')
            ->join('dc_game_info b', 'a.agent_game_game_id = b.game_id');
        if (isset($condition['keyword'])) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    'a.agent_game_id' => ['like', '%' . $condition['keyword'] . '%'],
                    'b.game_name' => ['like', '%' . $condition['keyword'] . '%'],

                ]);
            });
        }
        unset($condition['keyword']);
        return $this->where($condition)->sum($field);
    }

    /**
     * @param $condition
     * @param $page
     * @param $pageSize
     * @param string $order
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAgentList($condition, $page, $pageSize, $field = '*', $order = '')
    {
        if (!empty($order)) {
            $this->order($order);
        }
        $this->alias('a')
            ->join('dc_game_info b', 'a.agent_game_game_id = b.game_id')
            ->field($field)
            ->page($page, $pageSize);
        if (isset($condition['keyword'])) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    'a.agent_game_id' => ['like', '%' . $condition['keyword'] . '%'],
                    'b.game_name' => ['like', '%' . $condition['keyword'] . '%'],

                ]);
            });
        }
        unset($condition['keyword']);
        $list = collection($this->where($condition)->select())->toArray();
        return $list;
    }

    /**
     * @param $condition
     * @return int|string
     */
    public function getAgentCount($condition)
    {
        $this->alias('a')
            ->join('dc_game_info b', 'a.agent_game_game_id = b.game_id');
        if (isset($condition['keyword'])) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    'a.agent_game_id' => ['like', '%' . $condition['keyword'] . '%'],
                    'b.game_name' => ['like', '%' . $condition['keyword'] . '%'],

                ]);
            });
        }
        unset($condition['keyword']);
        return $this->where($condition)->count('*');
    }


}