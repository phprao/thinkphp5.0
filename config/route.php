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
use think\Route;
use app\common\components\Helper;

if (!defined('ENVIRONMENT')) {
    // 加载环境配置
    $config = require CONF_PATH . '/extra/environment.php';
    // 定义环境
    define('ENVIRONMENT', Helper::getEnvironment($config));
}

return Helper::mergeArray([
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
    //todo
    'api/:version/demo/index'  => 'api/:version.Demo/index',
    'api/:version/demo/create' => 'api/:version.Demo/create',
    'api/:version/demo/update' => 'api/:version.Demo/update',
    'api/:version/demo/view'   => 'api/:version.Demo/view',
    'api/:version/demo/delete' => 'api/:version.Demo/delete',
    //财务模块
    'api/:version/login/index'             => 'api/:version.Login/index',
    'api/:version/login/get_captcha'       => 'api/:version.Login/getCaptcha',
    // 'api/:version/login/checks'             => 'api/:version.Login/checks',
    // todo 外链接 login
    'api/:version/login/authorized_longin' => 'api/:version.Login/authorizedLongin',

    'api/:version/withdraw/index'       => 'api/:version.Withdraw/index',
    'api/:version/withdraw/get_list'    => 'api/:version.Withdraw/getList',
    'api/:version/withdraw/agent_money' => 'api/:version.Withdraw/agentMoney',
    // 微信体现新增
    'api/:version/withdraw/get_notice'  => 'api/:version.Withdraw/getNotice',
    'api/:version/withdraw/get_info'  => 'api/:version.Withdraw/getInfo',
    'api/:version/withdraw/unified_order'  => 'api/:version.Withdraw/unifiedOrder',
    'api/:version/withdraw/notify_action'  => 'api/:version.Withdraw/notifyAction',

    'api/:version/user/get_setting'     => 'api/:version.User/getSetting',
    'api/:version/user/setting'         => 'api/:version.User/setting',
    'api/:version/user/getinfo'         => 'api/:version.User/getinfo',
    'api/:version/user/change_mobile'   => 'api/:version.User/changeMobile',
    'api/:version/user/change_password' => 'api/:version.User/changePassword',
    //promoter
    'api/:version/promoter/index'            => 'api/:version.Promoter/index',
    'api/:version/promoter/register'         => 'api/:version.Promoter/register',
    'api/:version/promoter/erweima'          => 'api/:version.Promoter/erweima',
    'api/:version/promoter/promote_tutorial' => 'api/:version.Promoter/promoteTutorial',
    'api/:version/promoter/promote_url'      => 'api/:version.Promoter/promoteUrl',
    'api/:version/promoter/agreement'        => 'api/:version.Promoter/agreement',
    'api/:version/promoter/promote_player'   => 'api/:version.Promoter/promotePlayer',
    'api/:version/promoter/do_agree'         => 'api/:version.Promoter/doAgree',

    // todo 外链接 promoter
    'api/:version/promoter/url_erwrima'  => 'api/:version.Promoter/urlErwrima',
    'api/:version/promoter/the_link'     => 'api/:version.Promoter/theLink',

    //渠道推广处理
    'api/:version/promoter/channel_promoter'     => 'api/:version.Promoter/channelPromoter',
    'api/:version/promoter/become_promoter'     => 'api/:version.Promoter/becomePromoter',
    'api/:version/promoter/become_player'     => 'api/:version.Promoter/becomePlayer',
    'api/:version/promoter/channel_player'     => 'api/:version.Promoter/channelPlayer',
    //新手礼包
    'api/:version/promoter/channel_gift'     => 'api/:version.Promoter/channelGift',
    'api/:version/promoter/receive_gift'     => 'api/:version.Promoter/receiveGift',
    
    'api/:version/send_mobile_code/send' => 'api/:version.SendMobileCode/send',

    //todo home
    'api/:version/home/index'           => 'api/:version.Home/index',
    'api/:version/home/profit'          => 'api/:version.Home/proFit',
    'api/:version/home/agent_notice'    => 'api/:version.Home/agentNotice',
    'api/:version/home/total_interface' => 'api/:version.Home/totalInterface',
    'api/:version/home/check_token'     => 'api/:version.Home/checkToken',

    //todo Lower
    'api/:version/lower/user_list'           => 'api/:version.Lower/userList',
    'api/:version/lower/agent_list'          => 'api/:version.Lower/agentList',
    'api/:version/lower/update_agent_remark' => 'api/:version.Lower/updateAgentRemark',

    //todo income
    'api/:version/income/my_income'        => 'api/:version.Income/myIncome',
    'api/:version/income/my_income_detail' => 'api/:version.Income/myIncomeDetail',
    'api/:version/income/agent_income'     => 'api/:version.Income/agentIncome',

    'api/:version/wx_login/index'     => 'api/:version.WxLogin/index',
    'api/:version/wx_login/bind'     => 'api/:version.WxLogin/bind',

    'api/:version/sys_login/index'       => 'api/:version.SysLogin/index',
    
],
    //加载管理路由
    require CONF_PATH . '/admin/route.php',
    ENVIRONMENT ? require CONF_PATH . '/' . ENVIRONMENT . '/route.php' : []);