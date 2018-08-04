<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use app\common\components\Helper;

if (!defined('ENVIRONMENT')) {
    // 加载环境配置
    $config = require CONF_PATH . '/extra/environment.php';
    // 定义环境
    define('ENVIRONMENT', Helper::getEnvironment($config));
}

return Helper::mergeArray([

], ENVIRONMENT ? require CONF_PATH . '/' . ENVIRONMENT . '/redis.php' : []);