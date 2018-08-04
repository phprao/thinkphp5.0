<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:41
 * @author ChangHai Zhan
 */
namespace app\api\block;

use app\api\model\DemoModel;

/**
 * 逻辑块
 * Class DemoBlock
 * @author ChangHai Zhan
 */
class DemoBlock
{
    /**
     * 获取demo block
     * @param $keyword
     * @return mixed
     */
    public static function getDemo($keyword)
    {
        return $keyword;
    }
}