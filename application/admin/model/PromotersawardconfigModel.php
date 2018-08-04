<?php


namespace app\admin\model;

use app\common\model\Model;

/**
 * 用户信息表
 * Class PromotersInfoModel
 * @package app\admin\model
 * @author
 */
class PromotersawardconfigModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'dc_promoters_award_config';

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

    /**\
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function getOne($condition)
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
    public function getList($condition)
    {
        $data = $this->where($condition)->select();

        return $data;

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
     * @param $agentId
     * @param $data
     * @return $this
     */
    public function updateAwardInfo($agentId, $data)
    {
        return $this->where('award_agent_id', $agentId)->update($data);
    }

    /**
     * @param $condition
     * @return int|string
     */
    public function insertAward($condition)
    {
        $data = $this->insert($condition);
        return $data;
    }
    /**
     * @param $condition
     * @return false|int
     */
    public function savePromoters($id,$condition)
    {
        $this->where('award_id', $id);
        return $this->isUpdate(true)->save($condition);
    }

}

