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
    //todo demo
    'admin/:version/demo/index'  => 'admin/:version.demo/index',
    
    //todo login 登陆相关
    'admin/:version/login/index' => 'admin/:version.login/index',
    'admin/:version/login/system' => 'admin/:version.login/system',
    
    //todo users 登陆相关
    'admin/:version/users/index' => 'admin/:version.users/index',
    'admin/:version/users/change_pwd' => 'admin/:version.users/changePwd',
    'admin/:version/users/user_exit' => 'admin/:version.users/userExit',

    //todo OverviewStatistics 数据总览 - 充值统计
    // 'admin/:version/overview_statistics/recharge'     => 'admin/:version.OverviewStatistics/recharge',
    //todo OverviewStatistics 数据总览 - 收益统计
    // 'admin/:version/overview_statistics/income'       => 'admin/:version.OverviewStatistics/income',
    //todo OverviewStatistics 数据总览 - 注册统计
    // 'admin/:version/overview_statistics/register'     => 'admin/:version.OverviewStatistics/register',
    
    //数据统计--数据总览-卡片数据
    'admin/:version/overview_statistics/data_list'    => 'admin/:version.OverviewStatistics/dataList',
    //数据统计--数据总览-折线图
    'admin/:version/overview_statistics/everyday_data'    => 'admin/:version.OverviewStatistics/everydayData',
    //数据统计--数据总览-各游戏每日消耗金币堆叠图
    'admin/:version/overview_statistics/coin_cost'    => 'admin/:version.OverviewStatistics/coinCost',
    //数据统计--数据总览-各游戏每日消耗金币表格
    'admin/:version/overview_statistics/all_game_round_day'    => 'admin/:version.OverviewStatistics/allGameRoundDay',

    //数据统计--上周本周对比
    'admin/:version/overview_statistics/week_compare' => 'admin/:version.OverviewStatistics/weekCompare',


    //数据统计--今日统计 todo   DataStatistics
    'admin/:version/data_statistics/index'              => 'admin/:version.DataStatistics/index',
    'admin/:version/data_statistics/data_contrast'      => 'admin/:version.DataStatistics/dataContrast',
    'admin/:version/data_statistics/statistical_user'   => 'admin/:version.DataStatistics/statisticalUser',
    'admin/:version/data_statistics/statistical_topup'  => 'admin/:version.DataStatistics/statisticalTopUp',
    'admin/:version/data_statistics/user_onsumption'    => 'admin/:version.DataStatistics/userConsumption',
    'admin/:version/data_statistics/user_active'        => 'admin/:version.DataStatistics/userActive',
    // 'admin/:version/data_statistics/all_game_round_day' => 'admin/:version.DataStatistics/allGameRoundDay',


    //todo 特代收益
    'admin/:version/income_manage/special_agent_income'        => 'admin/:version.IncomeManage/specialAgentIncome',
    //todo 特代收益明细
    'admin/:version/income_manage/special_agent_income_detail' => 'admin/:version.IncomeManage/specialAgentIncomeDetail',
    //todo 星级推广员收益
    'admin/:version/income_manage/star_agent_income'           => 'admin/:version.IncomeManage/starAgentIncome',
    //todo 星级推广员收益明细
    'admin/:version/income_manage/star_agent_income_detail'    => 'admin/:version.IncomeManage/starAgentIncomeDetail',
    //todo 普通推广员收益
    'admin/:version/income_manage/normal_agent_income'         => 'admin/:version.IncomeManage/normalAgentIncome',
    //todo 普通推广员收益明细
    'admin/:version/income_manage/normal_agent_income_detail'  => 'admin/:version.IncomeManage/normalAgentIncomeDetail',
    //todo 特代收益当月
    'admin/:version/income_manage/special_agent_income_month'        => 'admin/:version.IncomeManage/specialAgentIncomeMonth',
    
    
    // 我的收益--针对渠道后台
    'admin/:version/income_manage/my_channel_income'              => 'admin/:version.IncomeManage/myChannelIncome',
    'admin/:version/income_manage/my_channel_income_detail'       => 'admin/:version.IncomeManage/myChannelIncomeDetail',
    'admin/:version/income_manage/my_channel_income_month'       => 'admin/:version.IncomeManage/myChannelIncomeMonth',
    
    
    //todo 金币消耗记录
    'admin/:version/pay_record/records'                     => 'admin/:version.PayRecord/records',
    'admin/:version/coin_change_log/player_change_log'      => 'admin/:version.CoinChangeLog/playerChangeLog',
    //todo 玩家战绩
    'admin/:version/playerstatistics/index'                 => 'admin/:version.PlayerStatistics/index',
    'admin/:version/playerstatistics/room_round'            => 'admin/:version.PlayerStatistics/roomRound',
    'admin/:version/playerstatistics/gamedata'              => 'admin/:version.PlayerStatistics/gameData',
    'admin/:version/playerstatistics/game_categroy'         => 'admin/:version.PlayerStatistics/gameCategroy',
    //todo overviewstatistics
    'admin/:version/overview_statistics/coin_cost'          => 'admin/:version.OverviewStatistics/coinCost',

    

    //todo 权限相关
    //todo 创建角色
    'admin/auth/create_role'        => 'admin/Auth/createRole',
    //todo 修改角色
    'admin/auth/update_role'        => 'admin/Auth/updateRole',
    //todo 角色列表
    'admin/auth/role_list'          => 'admin/Auth/roleList',
    //todo 角色的方法列表
    'admin/auth/action_list'        => 'admin/Auth/actionList',
    //todo 角色可分配方法列表
    'admin/auth/assign_action_list' => 'admin/Auth/assignActionList',
    //todo 分配方法
    'admin/auth/add_action'         => 'admin/Auth/addAction',
    //todo 移除方法
    'admin/auth/remove_action'      => 'admin/Auth/removeAction',
    //todo 管理员列表
    'admin/auth/user_list'          => 'admin/Auth/userList',
    //todo 管理员角色列表
    'admin/auth/user_role_list'     => 'admin/Auth/userRoleList',
    //todo 可分配的角色列表
    'admin/auth/assign_role_list'   => 'admin/Auth/assignRoleList',
    //todo 分配角色
    'admin/auth/add_role'           => 'admin/Auth/addRole',
    //todo 移除角色
    'admin/auth/remove_role'        => 'admin/Auth/removeRole',
    //todo 白名单列表
    'admin/auth/allow_list'         => 'admin/Auth/allowList',
    //todo 待分配白名单列表
    'admin/auth/assign_allow_list'  => 'admin/Auth/assignAllowList',
    //todo 添加白名单
    'admin/auth/add_allow'          => 'admin/Auth/addAllow',
    //todo 移除白名单
    'admin/auth/remove_allow'       => 'admin/Auth/removeAllow',
    //todo 我的操作权限列表
    'admin/auth/my_action_list'     => 'admin/Auth/myActionList',

    /***** 运营总管 *****/
    // 渠道
    'admin/:version/super_agent/add_and_update_channel'  => 'admin/:version.SuperAgent/addAndUpdateChannel',
    'admin/:version/super_agent/get_simple_channel_info' => 'admin/:version.SuperAgent/getSimpleChannelInfo',
    'admin/:version/super_agent/channel_detail_list'     => 'admin/:version.SuperAgent/channelDetailList',
    'admin/:version/super_agent/set_ajax_data'           => 'admin/:version.SuperAgent/setAjaxData',
    'admin/:version/super_agent/get_super_agent'         => 'admin/:version.SuperAgent/getSuperAgent',
    'admin/:version/super_agent/get_system_config'       => 'admin/:version.SuperAgent/getSystemConfig',
    'admin/:version/super_agent/channel_activity'        => 'admin/:version.SuperAgent/channelActivity',
    'admin/:version/super_agent/modify_channel_info'        => 'admin/:version.SuperAgent/modifyChannelInfo',

    // 星级推广员
    'admin/:version/star_agent/get_staragent_list' => 'admin/:version.StarAgent/getStarAgentList',
    'admin/:version/star_agent/star_agent_list' => 'admin/:version.StarAgent/starAgentList',
    'admin/:version/star_agent/set_config_one' => 'admin/:version.StarAgent/setConfigOne',
    'admin/:version/star_agent/get_config_one' => 'admin/:version.StarAgent/getConfigOne',
    'admin/:version/star_agent/set_config_all' => 'admin/:version.StarAgent/setConfigAll',
    'admin/:version/star_agent/get_config_channel_one' => 'admin/:version.StarAgent/getConfigChannelOne',
    // 渠道后台增加
    'admin/:version/star_agent/get_channel_config' => 'admin/:version.StarAgent/getChannelConfig',
    // 'admin/:version/star_agent/set_channel_config' => 'admin/:version.StarAgent/setChannelConfig',

    // 玩家列表
    'admin/:version/player/player_list'    => 'admin/:version.Player/PlayerList',
    'admin/:version/player/add_black_list'    => 'admin/:version.Player/addBlackList',
    'admin/:version/player/channel_player_list'    => 'admin/:version.Player/ChannelPlayerList',
    
    
    //推广员列表
    'admin/:version/promote/index'        => 'admin/:version.Promote/index',
    'admin/:version/promote/promote_list'        => 'admin/:version.Promote/promoteList',
    'admin/:version/promote/promoters_configuration'        => 'admin/:version.Promote/promoterscConfiguration',
    'admin/:version/promote/proomoters_saveinfo'        => 'admin/:version.Promote/proomotersSaveinfo',
    'admin/:version/promote/promotersc_save'        => 'admin/:version.Promote/promoterscSave',

    //财务管理
    'admin/:version/financial/index'        => 'admin/:version.Financial/index',
    'admin/:version/financial/record_list'        => 'admin/:version.Financial/recordList',
    'admin/:version/financial/managemnt_show'        => 'admin/:version.Financial/managemntShow',
    'admin/:version/financial/cash_management'        => 'admin/:version.Financial/cashManagement',
    'admin/:version/financial/examination_approval'        => 'admin/:version.Financial/examinationApproval',

    // 渠道后台--推广教程
    'admin/:version/promotion/promotion_course'        => 'admin/:version.Promotion/promotionCourse',
    'admin/:version/promotion/promotion_star'        => 'admin/:version.Promotion/promotionStar',
    'admin/:version/promotion/create_qrcode'        => 'admin/:version.Promotion/createQrcode',
    'admin/:version/promotion/promotion_gift'        => 'admin/:version.Promotion/promotionGift',
    'admin/:version/promotion/promotion_gift_list'        => 'admin/:version.Promotion/promotionGiftList',
    'admin/:version/promotion/promotion_gift_detail'        => 'admin/:version.Promotion/promotionGiftDetail',
    'admin/:version/promotion/channel_info'        => 'admin/:version.Promotion/channelInfo',
    'admin/:version/promotion/cancel_channel_gift'        => 'admin/:version.Promotion/cancelChannelGift',

    //意见反馈
    'admin/:version/feedback/index'        => 'admin/:version.Feedback/index',
    //公告信息
    'admin/:version/notice/index'        => 'admin/:version.Notice/index',
    'admin/:version/notice/notice_add'        => 'admin/:version.Notice/noticeAdd',
    'admin/:version/notice/notice_show'        => 'admin/:version.Notice/noticeShow',
    'admin/:version/notice/notice_edit'        => 'admin/:version.Notice/noticeEdit',
    'admin/:version/notice/notice_delete'        => 'admin/:version.Notice/noticeDelete',

    'admin/:version/notice_read/index'        => 'admin/:version.NoticeRead/index',
    'admin/:version/notice_read/tag_read'        => 'admin/:version.NoticeRead/tagRead',
    'admin/:version/notice_read/get_list'        => 'admin/:version.NoticeRead/getList',

    'admin/:version/system_manage/change_coin'        => 'admin/:version.SystemManage/changeCoin',
    'admin/:version/system_manage/become_star'        => 'admin/:version.SystemManage/becomeStar',
    'admin/:version/system_manage/transfer_channel'        => 'admin/:version.SystemManage/transferChannel',
    'admin/:version/system_manage/cancel_player'        => 'admin/:version.SystemManage/cancelPlayer',
    'admin/:version/system_manage/active_player'        => 'admin/:version.SystemManage/activePlayer',
    'admin/:version/system_manage/change_lottery'        => 'admin/:version.SystemManage/changeLottery',
    
    // 权限管理
    'admin/:version/auth_manage/user_list'        => 'admin/:version.AuthManage/userList',
    'admin/:version/auth_manage/role_list'        => 'admin/:version.AuthManage/roleList',
    'admin/:version/auth_manage/set_auth'        => 'admin/:version.AuthManage/setAuth',
    'admin/:version/auth_manage/get_auth'        => 'admin/:version.AuthManage/getAuth',
    'admin/:version/auth_manage/set_role'        => 'admin/:version.AuthManage/setRole',
    'admin/:version/auth_manage/add_role'        => 'admin/:version.AuthManage/addRole',
    'admin/:version/auth_manage/get_auth_config'        => 'admin/:version.AuthManage/getAuthConfig',

    //game 游戏显示
    'admin/:version/game_info/index'        => 'admin/:version.GameInfo/index',
    //agent_game 渠道游戏
    'admin/:version/agent_game/index'        => 'admin/:version.AgentGame/index',
    'admin/:version/agent_game/game_etid'        => 'admin/:version.AgentGame/gameEtid',

    // 推广渠道
    'admin/:version/promote_channel/add_and_update_channel'  => 'admin/:version.PromoteChannel/addAndUpdateChannel',
    'admin/:version/promote_channel/get_all_partner'         => 'admin/:version.PromoteChannel/getAllPartner',
    'admin/:version/promote_channel/get_simple_partner_info' => 'admin/:version.PromoteChannel/getSimplePartnerInfo',
    'admin/:version/promote_channel/partner_detail_list'     => 'admin/:version.PromoteChannel/partnerDetailList',
    'admin/:version/promote_player/player_list'              => 'admin/:version.PromotePlayer/PlayerList',
    'admin/:version/promote_recharge/records'                => 'admin/:version.PromoteRecharge/records',
    'admin/:version/promote_income/income'                => 'admin/:version.PromoteIncome/Income',

    // 推广渠道后台
    'admin/:version/login/partner'                 => 'admin/:version.Login/partner',
    'admin/:version/promote_income/partner_income' => 'admin/:version.PromoteIncome/PartnerIncome',

    // 邮件管理
    'admin/:version/mail/send_mail'                 => 'admin/:version.Mail/sendMail',
    'admin/:version/mail/mail_list'                 => 'admin/:version.Mail/MailList',

    // 活动管理
    'admin/:version/activity/activity_list'                 => 'admin/:version.Activity/ActivityList',
    'admin/:version/activity/gift_list'                 => 'admin/:version.Activity/giftList',

], ENVIRONMENT ? require CONF_PATH . '/admin/' . ENVIRONMENT . '/route.php' : []);