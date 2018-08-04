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
    //数据库
    'db_config_u3d' => [
        // 数据库类型
        'type'        => 'mysql',
        // 服务器地址
        'hostname'    => '192.168.1.210',
        // 数据库名
        'database'    => 'dc_u3d',
        // 数据库用户名
        'username'    => 'root',
        // 数据库密码
        'password'    => '123456',
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
    ],

    //微信配置
    'wechat_u3d' => [
        /**
         * Debug 模式，bool 值：true/false
         * 当值为 false 时，所有的日志都不会记录
         */
        'debug'  => true,
        /**
         * 账号基本信息，请从微信公众平台/开放平台获取
         */
        'app_id'  => 'wx4e4f488c23fcb48a',             // AppID
        'secret'  => '965a4f7efe988f6f1fc1524b64ff09c7',     // AppSecret
        'token'   => 'your-token',          // Token
        'aes_key' => '',     // EncodingAESKey，安全模式下请一定要填写！！！

        /**
         * 日志配置
         *
         * level: 日志级别, 可选为：
         *         debug/info/notice/warning/error/critical/alert/emergency
         * permission：日志文件权限(可选)，默认为null（若为null值,monolog会取0644）
         * file：日志文件位置(绝对路径!!!)，要求可写权限
         */
        'log' => [
            'level'      => 'debug',
            'permission' => 0777,
            'file'       => APP_PATH . '/../runtime/log/' . date('Y-m-d-H') . '/wechat_local.log',
        ],

        /**
         * OAuth 配置
         *
         * scopes：公众平台（snsapi_userinfo / snsapi_base），开放平台：snsapi_login
         * callback：OAuth授权完成后的回调页地址
         */
        'oauth' => [
            'scopes'   => ['snsapi_userinfo'],
            'callback' => '/examples/oauth_callback.php',
        ],

        /**
         * 微信支付
         */
        'payment' => [
            'merchant_id'        => '103510000245',
            'key'                => '19afdbced7c6dfdc4ce83e824bdeb3aa',
            'cert_path'          => __DIR__ . '/rsa_private_key_u3d.pem', // XXX: 绝对路径！！！！
            'key_path'           => __DIR__ . '/rsa_public_key_u3d.pem',     // XXX: 绝对路径！！！！
            'notify_url'         => 'http://test.com', // 你也可以在下单时单独设置来想覆盖它
            // 'device_info'     => '013467007045764',
            // 'sub_app_id'      => '',
            // 'sub_merchant_id' => '',
            // ...
        ],
    ],
];
