<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 10:52
 */

namespace app\admin\model;

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
     * 分页
     * @param null $condition
     * @param null $safe
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getLowerUserList($condition = null, $safe = null)
    {
        if (isset($condition->keyword)) {
            $this->where('name', 'like', '%' . $condition->keyword . '%');
        }
        $this->where('status', '=', self::STATUS_NORMAL);
        if ($safe) {
            $this->field($this->getSafe($safe));
        }
        if (!isset($condition->pageSize)) {
            $condition->pageSize = 10;
        }
        return $this->paginate($condition->pageSize);
    }

    /**
     * @param $condition
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function gte_NoticeAlliLst($condition, $order = null)
    {
        $data = $this->where($condition)->order($order)->select();

        return $data;

    }

}




















