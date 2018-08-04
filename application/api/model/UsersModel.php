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
 * 管理员
 * Class UsersModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class UsersModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_users';
    public $role_table = 'role_user';
    /**
     * 管理员状态 禁用
     */
    const  USER_STATUS_NO = 0;
    /**
     * 管理员状态 正常
     */
    const  USER_STATUS_YES = 1;

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
     * 获取管理员
     * @param $username
     * @param array $field
     * @param null $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUsersByUsername($username, $field = [], $condition = null)
    {
        $this->where('user_login', $username);
        return $this->find();
    }

    /**
     * @param $id
     * @param  $safe
     * @param null $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLoginById($id, $safe = 'login', $condition = null)
    {
        $this->field($this->getSafe($safe));
        $this->where('id', $id);
        return $this->find();
    }

    /**
     * 更新登陆状态
     * @param $id
     * @param $lastLoginIp
     * @param $lastLoginTime
     * @return false|int
     */
    public function updateLoginById($id, $lastLoginIp, $lastLoginTime)
    {
        $this->where('id', $id);
        return $this->isUpdate(true)->save(['last_login_ip' => $lastLoginIp, 'last_login_time' => $lastLoginTime]);
    }

    /**
     * 获取接收参数
     * @param $scenario
     * @return array|mixed
     */
    public function getSafe($scenario)
    {
        $message = [
            'login' => [
                'id',
                'user_login',
                'user_email',
                'last_login_ip',
                'last_login_time',
            ],
            'auth_list' => [
                'id',
                'user_login',
                'user_email',
            ],
        ];
        return isset($message[$scenario]) ? $message[$scenario] : [];
    }

    /**
     * @param $safe
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAll($safe = 'auth_list')
    {
        $this->field($this->getSafe($safe));
        return $this->select();
    }

    /**
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbExceptionreturn
     */
    public function getFind($condition){
        return $this->where($condition)->find();
    }

    /**
     * 获取管理员信息
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Collection|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserById($id)
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
     * 修改后台登陆信息
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function updateUserInfo($data)
    {
        $this->where('id', $data['id']);
        $info = [];
        if(isset($data['user_login'])){
            $info['user_login'] = $data['user_login'];
        }
        if(isset($data['user_pass'])){
            $info['user_pass'] = $data['user_pass'];
        }
        if(empty($info)){
            return true;
        }else{
            return $this->isUpdate(true)->save($info);
        }
    }

    /**\
     * @param $name
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserByLogin($name)
    {
        $this->where('user_login', $name);
        return $this->select();
    }
    /**
     * 新增账户
     * @param [type] $data [description]
     */
    public function addUser($info)
    {
        $this->data([
            'user_login'  =>$info['user_login'],
            'user_pass'   =>$info['user_pass'],
            'create_time' =>time()
        ]);
        $this->save();
        return $this->id;
    }

    /**
     * @param $user_id
     * @param $data
     * @return $this
     */
    public function updateUser($user_id,$data)
    {
        return $this->where('id',$user_id)->update($data);
    }
}