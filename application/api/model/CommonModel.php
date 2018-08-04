<?php
/**
 * 通用的数据模型
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 10:53
 */
namespace app\api\model;

use app\common\model\Model;

class CommonModel extends Model {

    public $tableName = '';

    public function getOne($tableName,$condition)
    {

    }

    public function getList($tableName,$condition)
    {

    }

    public function addData($tableName,$condition)
    {

    }

    public function updateData($tableName,$condition)
    {


    }

    public function delData($tableName,$condition)
    {
        $status = M($tableName)->delete();
    }




}
