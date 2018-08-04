<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:44
 * @author ChangHai Zhan
 */

namespace app\admin\model;

use app\common\model\Model;

/**
 * demo model
 * Class DemoModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class DemoModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'dc_demo';
    /**
     * status状态 正常
     */
    const STATUS_NORMAL = 1;
    /**
     * status状态 删除
     */
    const STATUS_DELETE = -1;

    /**
     * 验证规则
     * @param $scenario
     * @return array|mixed
     */
    public function getRule($scenario)
    {
        $rules = [
            'create' => [
                'name'  => 'require|max:25',
            ],
            'update' => [
                'name'  => 'require|max:25',
            ],
        ];
        return isset($rules[$scenario]) ? $rules[$scenario] : [];
    }

    /**
     * 验证错误信息
     * @param $scenario
     * @return array|mixed
     */
    public function getMessage($scenario)
    {
        $message = [
            'create' => [
                'name.require' => 'name 名称必须',
            ],
            'update' => [
                'name.require' => 'name 名称必须',
            ],
        ];
        return isset($message[$scenario]) ? $message[$scenario] : [];
    }

    /**
     * 获取接收参数
     * @param $scenario
     * @return array|mixed
     */
    public function getSafe($scenario)
    {
        $message = [
            'create' => [
                'name',
            ],
            'update' => [
                'name',
            ],
            'list' => [
                'id',
                'name',
            ],
            'view' => [
                'id',
                'name',
            ],
        ];
        return isset($message[$scenario]) ? $message[$scenario] : [];
    }

    /**
     * 查询demo
     * @param $id
     * @param null $safe
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDemoById($id, $safe = null)
    {
        if ($safe) {
            $this->field($this->getSafe($safe));
        }
        return $this->where('id', '=', $id)->find();
    }

    /**
     * 分页
     * @param null $condition
     * @param null $safe
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getDemoList($condition = null, $safe = null)
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
     * @param $id
     * @return false|int
     */
    public function deleteDemo($id)
    {
        $this->where('id', '=', $id);
        $this->where('status', '=', self::STATUS_NORMAL);
        return $this->isUpdate(true)->save(['status' => self::STATUS_DELETE]);
    }

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
}