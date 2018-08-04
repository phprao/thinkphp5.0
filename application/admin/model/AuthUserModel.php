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
 * 权限管理员授权角色
 * Class AuthUserModel
 * @package app\admin\model
 * @author ChangHai Zhan
 */
class AuthUserModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_auth_user';

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
     * 获取管理员角色列表
     * @param $userId
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAuthRoleListByUserId($userId)
    {
        $this->where('user_id', $userId);
        return $this->select();
    }

    /**
     * 写入角色数据
     * @param $userId
     * @param $role_ids
     * @return array|false
     * @throws \Exception
     */
    public function addRoleByUserId($userId, $role_ids)
    {
        $data = [];
        foreach ($role_ids as $role_id) {
            $data[] = [
                'role_id' => $role_id,
                'user_id' => $userId,
                'add_time' => time(),
            ];
        }
        return $this->saveAll($data);
    }

    /**
     * 移除角色
     * @param $userId
     * @param $roleId
     * @return int
     */
    public function removeRoleByUserId($userId, $roleId)
    {
        $this->where('user_id', $userId);
        $this->whereIn('role_id', (array)$roleId);
        return $this->delete();
    }

    /**
     * @param $userId
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoleToActionByUserId($userId)
    {
        $this->alias('t');
        $this->join(AuthActionModel::model()->getTableName() . ' t1', 't1.role_id = t.role_id');
        $this->where('t.user_id', $userId);
        $this->field([
            't1.action'
        ]);
        return $this->select();
    }

    /**
     * 是否有权限
     * @param $userId
     * @param $action
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isAuth($userId, $action)
    {
        $this->alias('t');
        $this->join(AuthActionModel::model()->getTableName() . ' t1', 't1.role_id = t.role_id');
        $this->where('t.user_id', $userId);
        $this->where('t1.action', $action);
        $this->field([
            't.id'
        ]);
        return $this->find();
    }
}