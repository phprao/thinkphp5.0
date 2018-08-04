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
 * 权限角色
 * Class AuthRoleModel
 * @package app\admin\model
 * @author ChangHai Zhan
 */
class AuthRoleModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_auth_role';

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
     * 获取角色信息
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Collection|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoleById($id)
    {
        if (is_array($id)) {
            $this->whereIn('id', $id);
            return $this->select();
        } else {
            $this->where('id', $id);
            return $this->find();
        }
    }

    /**
     * @param $name
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoleByName($name)
    {
        $this->where('name', $name);
        return $this->find();
    }

    /**
     * 添加角色
     * @param $name
     * @return false|int
     */
    public function createRole($name)
    {
        return $this->save(['name' => $name, 'add_time' => time(), 'update_time' => time()]);
    }

    /**
     * 修改角色
     * @param $id
     * @param $name
     * @return false|int
     */
    public function updateRole($id, $name)
    {
        $this->where('id', $id);
        return $this->isUpdate(true)->save(['name' => $name, 'update_time' => time()]);
    }

    /**
     * 获取所有的角色
     * @param null $condition
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAll($condition = null)
    {
        if (isset($condition->notInId)) {
            $this->whereNotIn('id', (array)$condition->notInId);
        }
        return $this->select();
    }
}