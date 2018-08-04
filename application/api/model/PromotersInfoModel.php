<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:44
 * @author ChangHai Zhan
 */

namespace app\api\model;

use app\common\model\Model;

/**
 * demo model
 * Class DemoModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class PromotersInfoModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'dc_promoters_info';

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


