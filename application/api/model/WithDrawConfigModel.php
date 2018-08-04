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
 * 代理账户
 * Class AgentInfoModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class WithDrawConfigModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_withdraw_config';

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
   public function getFindOne($condition = null){
       $data = $this->where($condition)->find();
       return $data;
   }
}