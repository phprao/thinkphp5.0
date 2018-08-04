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
 * 权限角色授权方法
 * Class AuthActionModel
 * @package app\admin\model
 * @author ChangHai Zhan
 */
class AuthActionModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_auth_action';

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
     * 角色方法列表
     * @param $roleId
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getListByRoleId($roleId)
    {
        $this->whereIn('role_id', (array)$roleId);
        return $this->select();
    }

    /**
     * 写入方法数据
     * @param $roleId
     * @param $actions
     * @return array|false
     * @throws \Exception
     */
    public function addActionByRoleId($roleId, $actions)
    {
        $data = [];
        foreach ($actions as $action) {
            $data[] = [
                'role_id' => $roleId,
                'action' => $action,
                'add_time' => time(),
            ];
        }
        return $this->saveAll($data);
    }

    /**
     * 移除方法
     * @param $roleId
     * @param $actions
     * @return int
     */
    public function removeActionByRoleId($roleId, $actions)
    {
        $this->where('role_id', $roleId);
        $this->whereIn('action', (array)$actions);
        return $this->delete();
    }
}