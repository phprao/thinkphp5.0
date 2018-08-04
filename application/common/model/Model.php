<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:44
 * @author ChangHai Zhan
 */

namespace app\common\model;

/**
 * base model
 * Class Model
 * @package app\common\model
 * @author ChangHai Zhan
 */
class Model extends \think\Model
{
    /**
     * 验证场景ID
     * @var string
     */
    public $scenario = '';
    /**
     * @var
     */
    protected static $_models;

    /**
     * 静态实例化
     * @param string $className
     * @param array $data
     * @return mixed
     */
    public static function model($data = [], $className = __CLASS__)
    {
        //todo return new $className($data);
        if (!isset(self::$_models[$className])) {
            self::$_models[$className] = new $className($data);
        }
        return clone self::$_models[$className];
    }

    /**
     * 获取表的名字
     * @return string
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * 验证规则
     * @param $scenario
     * @return array|mixed
     */
    public function getRule($scenario)
    {
        $rules = [
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
        ];
        return isset($message[$scenario]) ? $message[$scenario] : [];
    }
}
