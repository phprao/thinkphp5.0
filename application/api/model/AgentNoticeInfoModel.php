<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 10:52
 */

namespace app\api\model;

use app\common\model\Model;

class AgentNoticeInfoModel extends Model
{

    public $table = 'dc_agent_notice';

    /**
     * @param $condition
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getInfo($condition)
    {
        $data = $this->where($condition)->find();

        return $data;
    }

    /**
     * @param $condition
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function gteNoticeAlliLst($condition, $order = null)
    {
        $data = $this->where($condition)->order($order)->select();

        return $data;

    }

}




















