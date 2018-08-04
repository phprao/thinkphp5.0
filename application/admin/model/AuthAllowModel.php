<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 10:52
 */

namespace app\admin\model;

use app\common\model\Model;

/**
 * 权限白名单
 * Class AuthAllowModel
 * @package app\admin\model
 * @author ChangHai Zhan
 */
class AuthAllowModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_auth_allow';

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
     * 是否通过白名单
     * @param $action
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isAuthAllow($action)
    {
        $this->field('id');
        $this->where('action', $action);
        return $this->find();
    }

    /**
     * 获取所有的白名单
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAll()
    {
        return $this->select();
    }

    /**
     * 写入白名单
     * @param $actions
     * @return array|false
     * @throws \Exception
     */
    public function addAllow($actions)
    {
        $data = [];
        foreach ($actions as $action) {
            $data[] = [
                'action' => $action,
                'add_time' => time(),
            ];
        }
        return $this->saveAll($data);
    }

    /**
     * 移除白名单方法
     * @param $actions
     * @return int
     */
    public function removeAllow($actions)
    {
        $this->whereIn('action', (array)$actions);
        return $this->delete();
    }
}