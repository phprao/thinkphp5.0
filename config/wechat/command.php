<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
use app\common\components\Helper;

if (!defined('ENVIRONMENT')) {
    // 加载环境配置
    $config = require CONF_PATH . '/extra/environment.php';
    // 定义环境
    define('ENVIRONMENT', Helper::getEnvironment($config));
}

return Helper::mergeArray([

], ENVIRONMENT ? require CONF_PATH . '/wechat/' . ENVIRONMENT . '/command.php' : []);