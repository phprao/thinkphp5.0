<?php
/**
 * +----------------------------------------------------------
 * date: 2018-01-29 18:03:25
 * +----------------------------------------------------------
 * author: Raoxiaoya
 * +----------------------------------------------------------
 * describe: 玩家游戏记录
 * +----------------------------------------------------------
 */

namespace app\admin\model;

use app\common\model\Model;

class ConfigModel extends Model
{
    public $table = 'dc_config';


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
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSelect($condition)
    {
        return $this->where($condition)->select();

    }

    /**
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getFint($condition){
        return $this->where($condition)->find();
    }


    /**
     * @param $condition
     * @return int|string
     */
    public function insertConfing($condition)
    {
        $data = $this->insert($condition);
        return $data;
    }

    /**
     * @param $id
     * @param $condition
     * @return false|int
     */
    public function saveConfig($config_id,$condition)
    {
        $this->where('config_id', $config_id);
        return $this->isUpdate(true)->save($condition);
    }

    public function getConfigArray($condition){
        $data = $this->where($condition)->select();
        if($data){
            foreach ($data as $key => $val) {
                $data[$key]['config_config'] = json_decode($val['config_config'], true);
            }
            return $data;
        }

        return array();
    }




}
