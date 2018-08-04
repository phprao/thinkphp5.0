<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 10:52
 */

namespace app\api\model;

use app\common\model\Model;

/**
 *
 * Class AgentInfoModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class AgentAccountInfoLogModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_agent_account_info_log';

    const TYPE_CHANNEL_GET    = 1; // 渠道收益月进账 
    const TYPE_PROMOTION_GET  = 2; // 推广收益进账
    const TYPE_STAR_AGENT_GET = 3; // 代理收益日进账
    const TYPE_AGENT_WITHDRAW = 4; // 提现出账

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
     * @param string $count
     * @return int|string
     *
     */

    public function get_AgentAccountInsert($condition)
    {
        $data = $this->insert($condition);
        return $data;
    }


    /**
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
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
     * @return int|string
     */
    public function addAgentInfoLog($condition)
    {
       return  $data = $this->insert($condition);
    }

    /**
     * @param $condition
     * @param $data
     * @return $this
     */
    public function updateInfo($condition, $data)
    {
        return $this->where($condition)->update($data);
    }


}