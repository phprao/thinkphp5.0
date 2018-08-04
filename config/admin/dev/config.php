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

return [
    //redis 配置
    'redis_config' => [
        'host' => '192.168.1.210',
        'port' => '55001',
        'password' => 'zyl12345!QWEASD901',
        'select' => 0,
        'timeout' => 0,
        'expire' => 60,
        'persistent' => false,
        'prefix' => '',
    ],
    
    'redis_user_info' => [
        'host' => '192.168.1.210',
        'port' => '55001',
        'password' => 'zyl12345!QWEASD901',
        'select' => 0,
        'timeout' => 0,
        'expire' => 60,
        'persistent' => false,
        'prefix' => '',
    ],
    
    //登陆无需密码
    'login_not_password' => false,
    
    // 幸运王国 -- 总后台地址
    'admin_url' => '/totalhoutaikingdom/index1.html',
    // 幸运王国 -- 渠道后台地址
    'channel_url' => '/qudaohoutaikingdom/index1.html',
    
    'log' => [
        // 日志记录方式，内置 file socket 支持扩展
        'type'      => 'File',
        // 日志保存目录
        'path'      => LOG_PATH,
        // 日志记录级别
        'level'     => [],
        // 最多保存日志文件个数
        'max_files' => 30,
    ],
    // 应用调试模式
    'app_debug'              => true,
    // 应用Trace
    'app_trace'              => false,
];
