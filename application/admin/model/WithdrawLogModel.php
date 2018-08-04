<?php
/**
 *lionel
 *
 */

namespace app\admin\model;

use app\common\model\Model;


class WithdrawLogModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_withdraw_log';
    /**/

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
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getinfo($condition)
    {
        $data = $this->where($condition)->find();
        return $data;

    }

    /**
     * @param $conditon
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSelect($conditon){

        return $this->where($conditon)->select();
    }

    /**
     * @param $id
     * @param $condition
     * @return false|int
     */
    public function saveConf($id,$condition)
    {
        $this->where('withdraw_id', $id);
        return $this->isUpdate(true)->save($condition);
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
    public function getWithdrawList($condition = null,$page = 1, $pageSize = 6)
    {
        $this->alias('t');
        $this->join('dc_player t1', 't.withdraw_player_id = t1.player_id');

        if (isset($condition['keyword'])) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    'url_decode(t1.player_nickname)' => ['like', '%' . $condition['keyword'] . '%'],
                    't.withdraw_player_id' => ['like', '%' . $condition['keyword'] . '%'],
//                    't.withdraw_parent_id' => ['like', '%' . $condition['keyword'] . '%'],
//                    't.withdraw_parent_name' => ['like', '%' . $condition['keyword'] . '%'],
//                    't.withdraw_player_id' => ['like', '%' . $condition['keyword'] . '%'],
                ]);
            });
        }
        unset($condition['keyword']);
        $records = $this
            ->where($condition)
            ->page($page, $pageSize)
            ->order('withdraw_create_time desc')
            ->select();
        $records = collection($records)->toArray();

        return $records;

    }

    /**
     * @param $condition
     * @return int|string
     */
    public function getWithdrawListCount($condition)
    {
        $this->alias('t');
        $this->join('dc_player t1', 't.withdraw_player_id = t1.player_id');
        if (isset($condition['keyword'])) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                  'url_decode(t1.player_nickname)' => ['like', '%' . $condition['keyword'] . '%'],
                    't.withdraw_player_id' => ['like', '%' . $condition['keyword'] . '%'],
//                    't.withdraw_agent_id' => $condition['keyword'],
//                    't.withdraw_agent_name' => ['like', '%' . $condition['keyword'] . '%'],
//                    't.withdraw_parent_id' => ['like', '%' . $condition['keyword'] . '%'],
//                    't.withdraw_parent_name' => ['like', '%' . $condition['keyword'] . '%'],
//                    't.withdraw_player_id' => ['like', '%' . $condition['keyword'] . '%'],
                ]);
            });
        }
        unset($condition['keyword']);
        return $this->where($condition)->count('*');
    }





}


