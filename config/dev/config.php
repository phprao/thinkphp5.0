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
    //异常处理handle类 json
    //'exception_handle'       => '\\app\\common\\exception\\JsonException',
    ///异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle'       => '',

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

     //幸运王国
    'wx_appid' => 'wxe282de77253c75f1',
    'wx_secret' =>'1693e9c883a69b5c4f9567e05dbbb074',

    //推广 promoter
    //H5后台页面跳转
    'jumpctUri' => "http://xyfx.dcyouxi.com/shareapp/?promoter_id=",
    //渠道推广页面跳转链接
    'channelHtmlUrl' => 'http://xyfx.dcyouxi.com/kingdomfenxiangqudao/index.html?channel_param=',

    // 微信登陆跳转 - 协议页面
    'wxJumpAgree'    => 'http://xycht.dcyouxi.com/index.html?action=login&token=',
    // 微信登陆跳转 - 首页
    'wxJumpIndex'    => 'http://xycht.dcyouxi.com/index1.html?action=login&token=',
    

    // 微信提现配置
    // 'send_bonus_config' => [
    //     // 微信发红包地址
    //     'send_bonus_url'      => 'http://www.dachuanyx.com/dc_service/sendwxbonus.php',
    //     // 微信发红包回调地址
    //     'send_bonus_callback' => 'http://xycht.dcyouxi.com/dc_api_u3d/public/api/v1/withdraw/notify_action',
    //     // 微信发红包名称
    //     'send_bonus_name'     => 'king',
    //     // 微信发红包应用场景
    //     'send_bonus_scanid'   => 'match',
    //     // 描述
    //     'send_bonus_remark'   => [
    //         "act_name"  => "幸运王国收益",
    //         "remark"    => "幸运王国收益",
    //         "send_name" => "幸运王国收益",
    //         "wishing"   => "恭喜发财，大吉大利！"
    //     ]
    // ],

    'send_bonus_config' => [
        // 微信发红包地址
        'send_bonus_url'      => 'http://www.dachuanyx.com/dc_service/sendwxbonus.php',
        // 微信发红包回调地址
        'send_bonus_callback' => 'http://kingdom.dcgames.cn/dc_api_u3d/public/api/v1/withdraw/notify_action',
        // 微信发红包名称
        'send_bonus_name'     => 'king',
        // 微信发红包应用场景
        'send_bonus_scanid'   => 'match',
        // 描述
        'send_bonus_remark'   => [
            "act_name"  => "幸运王国收益",
            "remark"    => "幸运王国收益",
            "send_name" => "幸运王国收益",
            "wishing"   => "恭喜发财，大吉大利！"
        ]
    ],

    //跨域设置
    'cross_domain' => [
        '*'
    ],

    // session配置
    'session' => [
        'prefix' => 'api_',
        'type' => '',
        'auto_start' => true,
    ],

    'log' => [
        // 日志记录方式，内置 file socket 支持扩展
        'type'      => 'File',
        // 日志保存目录
        'path'      => LOG_PATH,
        // 日志记录级别
        'level'     => ['error'],
        // 最多保存日志文件个数
        'max_files' => 30,
    ],
    // 应用调试模式
    'app_debug'              => true,
    // 应用Trace
    'app_trace'              => false,

];