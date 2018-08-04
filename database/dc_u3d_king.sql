/*
Navicat MySQL Data Transfer

Source Server         : 192.168.1.210
Source Server Version : 50713
Source Host           : 192.168.1.210:3306
Source Database       : dc_u3d_king

Target Server Type    : MYSQL
Target Server Version : 50713
File Encoding         : 65001

Date: 2018-08-04 12:25:42
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for dc_agents_promoters_statistics
-- ----------------------------
DROP TABLE IF EXISTS `dc_agents_promoters_statistics`;
CREATE TABLE `dc_agents_promoters_statistics` (
  `statistics_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键默认递增',
  `statistics_agents_id` int(11) DEFAULT '0' COMMENT '代理ID',
  `statistics_agents_player_id` int(11) DEFAULT '0' COMMENT '推广员的玩家id',
  `statistics_super_agents_id` int(11) DEFAULT '0' COMMENT '该推广员所属特代（渠道）id',
  `statistics_from` int(11) DEFAULT '0' COMMENT '该条收益来源：\r\n1-自己推广的玩家，\r\n2-下一级代理推广的玩家，\r\n3-下二级代理推广的玩家',
  `statistics_from_value` int(11) DEFAULT '0' COMMENT '来源agent_id',
  `statistics_type` int(1) DEFAULT '1' COMMENT '类型,1一级推广，2,二级推广',
  `statistics_money_type` tinyint(2) DEFAULT '1' COMMENT '货币类型：1-金币',
  `statistics_money_type_rate` int(11) DEFAULT '0' COMMENT '人民币换算成该货币的比例：如：10000',
  `statistics_data` bigint(15) DEFAULT '0' COMMENT '当天的消耗货币数--累加',
  `statistics_income` int(11) DEFAULT '0' COMMENT '平台收益：分',
  `statistics_my_data` bigint(15) DEFAULT '0' COMMENT '代理得到的货币数乘以 100 倍--累加',
  `statistics_my_income` int(11) DEFAULT '0' COMMENT '代理收益：分，因为statistics_my_data扩大了100倍',
  `statistics_share_money_low` int(11) DEFAULT '0' COMMENT '统计当时的分成比例',
  `statistics_share_money_high` int(11) DEFAULT NULL COMMENT '统计当时的分成比例',
  `statistics_status` tinyint(2) DEFAULT '0' COMMENT '是否计入代理账户：0-未计入，1-已计入',
  `statistics_time` int(11) DEFAULT '0' COMMENT '统计的时间',
  `statistics_date` varchar(30) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '时间',
  `statistics_add_time` int(11) DEFAULT '0' COMMENT '插入表的时间',
  PRIMARY KEY (`statistics_id`),
  KEY `statistics_agents_id` (`statistics_agents_id`) USING BTREE,
  KEY `statistics_money_type` (`statistics_money_type`) USING BTREE,
  KEY `statistics_time` (`statistics_time`) USING BTREE,
  KEY `statistics_from` (`statistics_from`) USING BTREE,
  KEY `statistics_from_value` (`statistics_from_value`) USING BTREE,
  KEY `statistics_agents_player_id` (`statistics_agents_player_id`) USING BTREE,
  KEY `statistics_super_agents_id` (`statistics_super_agents_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='代理收益表';

-- ----------------------------
-- Table structure for dc_agents_statistics_day
-- ----------------------------
DROP TABLE IF EXISTS `dc_agents_statistics_day`;
CREATE TABLE `dc_agents_statistics_day` (
  `statistics_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键默认递增',
  `statistics_parent_agents_id` int(11) DEFAULT '0' COMMENT '玩家直属代理ID',
  `statistics_super_agents_id` int(11) DEFAULT '0' COMMENT '特代（渠道）ID',
  `statistics_player_id` int(11) DEFAULT '0' COMMENT '玩家ID',
  `statistics_type` int(1) DEFAULT '0' COMMENT '类型',
  `statistics_money_type` int(1) DEFAULT '1' COMMENT '货币类型：1-金币',
  `statistics_money_type_rate` int(11) DEFAULT '0' COMMENT '人民币换算成该货币的比例：如：10000',
  `statistics_data` bigint(15) DEFAULT '0' COMMENT '当天的消耗货币数乘以 100 倍--累加',
  `statistics_income` int(11) DEFAULT '0' COMMENT '平台收益：分',
  `statistics_my_data` bigint(15) DEFAULT '0' COMMENT '直属代理得到的货币数乘以 100 倍--累加',
  `statistics_my_income` int(11) DEFAULT '0' COMMENT '直属代理收益：分，因为statistics_my_data扩大了100倍',
  `statistics_share_money_low` int(11) DEFAULT '0' COMMENT '统计当时的分成比例--当天最低值',
  `statistics_share_money_high` int(11) DEFAULT NULL COMMENT '统计当时的分成比例--当天最高值',
  `statistics_cost_detail` text COLLATE utf8_unicode_ci COMMENT '消耗的游戏详情',
  `statistics_time` int(11) DEFAULT '0' COMMENT '统计的时间，天',
  `statistics_date` varchar(30) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '统计时间,天',
  `statistics_add_time` int(11) DEFAULT '0' COMMENT '插入表的时间',
  PRIMARY KEY (`statistics_id`),
  KEY `statistics_player_id` (`statistics_player_id`) USING BTREE,
  KEY `statistics_money_type` (`statistics_money_type`) USING BTREE,
  KEY `statistics_time` (`statistics_time`) USING BTREE,
  KEY `statistics_parent_agents_id` (`statistics_parent_agents_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1101 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='玩家消耗，按小时累积';

-- ----------------------------
-- Table structure for dc_agents_statistics_hour
-- ----------------------------
DROP TABLE IF EXISTS `dc_agents_statistics_hour`;
CREATE TABLE `dc_agents_statistics_hour` (
  `statistics_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键默认递增',
  `statistics_parent_agents_id` int(11) DEFAULT '0' COMMENT '直属代理ID',
  `statistics_player_id` int(11) DEFAULT '0' COMMENT '玩家ID',
  `statistics_money_type` int(1) DEFAULT '1' COMMENT '货币类型：1-金币',
  `statistics_money_data` int(11) DEFAULT '0' COMMENT '统计的结果',
  `statistics_cost_detail` text COLLATE utf8_unicode_ci COMMENT '消耗的游戏详情：【时间，游戏id，游戏名称，货币类型，消耗，货币转换比例，直属代理分成比例】',
  `statistics_time` int(11) DEFAULT '0' COMMENT '统计的时间,hour',
  `statistics_date` varchar(30) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '统计时间',
  `statistics_add_time` int(11) DEFAULT '0' COMMENT '插入表的时间',
  PRIMARY KEY (`statistics_id`),
  KEY `statistics_player_id` (`statistics_player_id`) USING BTREE,
  KEY `statistics_money_type` (`statistics_money_type`) USING BTREE,
  KEY `statistics_time` (`statistics_time`) USING BTREE,
  KEY `statistics_parent_agents_id` (`statistics_parent_agents_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2527 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='玩家消耗，按小时累积';

-- ----------------------------
-- Table structure for dc_agents_statistics_player
-- ----------------------------
DROP TABLE IF EXISTS `dc_agents_statistics_player`;
CREATE TABLE `dc_agents_statistics_player` (
  `change_money_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `change_money_player_id` int(11) DEFAULT '0' COMMENT '玩家id',
  `change_money_parent_agents_id` int(11) DEFAULT '0' COMMENT '直属代理id',
  `change_money_super_agents_id` int(11) DEFAULT '0' COMMENT '渠道商id',
  `change_money_club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `change_money_club_room_id` int(11) DEFAULT '0' COMMENT '俱乐部房间id',
  `change_money_club_desk_no` int(11) DEFAULT '0' COMMENT '俱乐部桌子号',
  `change_money_club_desk_id` int(11) DEFAULT '0' COMMENT '桌子唯一id',
  `change_money_room_id` int(11) DEFAULT '0' COMMENT '房间id',
  `change_money_room_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '房间名称',
  `change_money_desk_no` int(11) DEFAULT '0' COMMENT '桌子号',
  `change_money_game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `change_money_game_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '游戏名称',
  `change_money_type` int(11) DEFAULT '1' COMMENT '数据改变类型(1:充值,2:游戏消耗(包括服务费),3:单扣服务费)',
  `change_money_tax` int(11) unsigned DEFAULT '0' COMMENT '服务费(税) * 100',
  `change_money_my_tax` int(11) DEFAULT '0' COMMENT '直属代理获取 * 100',
  `change_money_share_rate` int(11) DEFAULT '0' COMMENT '直属代理分成比例',
  `change_money_one_tax` int(11) DEFAULT '0' COMMENT '给一层推广的金币 * 100',
  `change_money_one_rate` int(11) DEFAULT '0' COMMENT '一层推广的分成比例',
  `change_money_one_agents_id` int(11) DEFAULT '0' COMMENT '一层推广的agentid',
  `change_money_two_tax` int(11) DEFAULT '0' COMMENT '给二层推广的金币 * 100',
  `change_money_two_rate` int(11) DEFAULT '0' COMMENT '二层推广的分成比例',
  `change_money_two_agents_id` int(11) DEFAULT '0' COMMENT '二层推广的agentid',
  `change_money_money_type` int(11) DEFAULT '0' COMMENT '货币类型：1-金币',
  `change_money_money_value` bigint(20) DEFAULT '0' COMMENT '改变货币值',
  `change_money_begin_value` bigint(20) DEFAULT '0' COMMENT '改变前数据',
  `change_money_after_value` bigint(20) DEFAULT '0' COMMENT '改变后数据',
  `change_money_time` int(11) DEFAULT '0' COMMENT '记录时间',
  `change_money_date` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `change_money_param` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '保留字段',
  UNIQUE KEY `change_money_id` (`change_money_id`) USING BTREE,
  KEY `change_money_player_id` (`change_money_player_id`) USING BTREE,
  KEY `change_money_parent_agents_id` (`change_money_parent_agents_id`) USING BTREE,
  KEY `change_money_game_id` (`change_money_game_id`) USING BTREE,
  KEY `change_money_time` (`change_money_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=53492 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='游戏货币消耗记录';

-- ----------------------------
-- Table structure for dc_agent_account_info
-- ----------------------------
DROP TABLE IF EXISTS `dc_agent_account_info`;
CREATE TABLE `dc_agent_account_info` (
  `agent_account_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `agent_account_agent_id` int(10) NOT NULL DEFAULT '0' COMMENT '代理商id',
  `agent_account_money` int(11) DEFAULT '0' COMMENT '账户余额：分',
  `agent_account_alipay` varchar(30) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '支付宝账号',
  `agent_account_username` varchar(30) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '支付宝真实姓名',
  `agent_account_mobile` varchar(20) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '支付宝手机号',
  `agent_account_payment_password` varchar(50) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '支付密码',
  PRIMARY KEY (`agent_account_id`),
  UNIQUE KEY `agent_account_agent_id` (`agent_account_agent_id`),
  KEY `agent_account_mobile` (`agent_account_mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=2028 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='代理信息表';

-- ----------------------------
-- Table structure for dc_agent_account_info_log
-- ----------------------------
DROP TABLE IF EXISTS `dc_agent_account_info_log`;
CREATE TABLE `dc_agent_account_info_log` (
  `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `log_money_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '钱的类型金币 1人民币',
  `log_agent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '代理ID',
  `log_bef_money` int(11) NOT NULL DEFAULT '0' COMMENT '变动前金额：分',
  `log_money` int(11) NOT NULL DEFAULT '0' COMMENT '变动金额：分',
  `log_aft_money` int(11) NOT NULL DEFAULT '0' COMMENT '变动后金额：分',
  `log_add_time` int(10) NOT NULL DEFAULT '0' COMMENT 'add time',
  `log_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '1-渠道收益月进账 \r\n2-推广收益进账,\r\n3-代理收益日进账,\r\n4-提现出账\r\n5-月度奖金',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='资金流水';

-- ----------------------------
-- Table structure for dc_agent_conditions
-- ----------------------------
DROP TABLE IF EXISTS `dc_agent_conditions`;
CREATE TABLE `dc_agent_conditions` (
  `agent_conditions_id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_conditions_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '条件名称',
  `agent_conditions_status` int(5) DEFAULT '1',
  `agent_conditions_type` int(5) DEFAULT '0' COMMENT '类型 1 是两个添加 金币加人数 2 是人数 3 是金币',
  `agent_conditions_data` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '条件 config ，  promote_number：人数 ，gold_consumption：金币数。',
  PRIMARY KEY (`agent_conditions_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='代理升级条件表';

-- ----------------------------
-- Table structure for dc_agent_config
-- ----------------------------
DROP TABLE IF EXISTS `dc_agent_config`;
CREATE TABLE `dc_agent_config` (
  `agentconf_id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) DEFAULT '0' COMMENT '代理id',
  `agent_conditions_id` int(11) DEFAULT '0' COMMENT '配置ID',
  `agentconf_time` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '创建时间',
  PRIMARY KEY (`agentconf_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='代理升级条件配置';

-- ----------------------------
-- Table structure for dc_agent_game
-- ----------------------------
DROP TABLE IF EXISTS `dc_agent_game`;
CREATE TABLE `dc_agent_game` (
  `agent_game_id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_agent_id` int(11) DEFAULT '0' COMMENT '渠道ID',
  `agent_game_game_id` int(11) DEFAULT '0' COMMENT '游戏ID',
  `agent_game_status` int(5) DEFAULT '1' COMMENT '状态 0 是关闭 1 是在用',
  `agent_game_time` int(11) DEFAULT '0' COMMENT '创建时间',
  `agent_game_order` int(11) DEFAULT '0' COMMENT '排序  默认是5 ',
  `agent_host` int(5) DEFAULT '0' COMMENT '5 不是主游戏 1 是主游戏',
  PRIMARY KEY (`agent_game_id`)
) ENGINE=InnoDB AUTO_INCREMENT=633 DEFAULT CHARSET=utf8 COMMENT='渠道游戏表';

-- ----------------------------
-- Table structure for dc_agent_income_config
-- ----------------------------
DROP TABLE IF EXISTS `dc_agent_income_config`;
CREATE TABLE `dc_agent_income_config` (
  `income_id` int(11) NOT NULL AUTO_INCREMENT,
  `income_agent_id` int(11) DEFAULT '0' COMMENT '代理id 全局0 ',
  `income_promote_count` int(11) DEFAULT '0' COMMENT '人数条件',
  `income_count_level` int(11) DEFAULT '0' COMMENT '分成等级',
  `income_agent` int(11) DEFAULT '0' COMMENT '直属代理分成比例',
  `income_level_one` int(11) DEFAULT '0' COMMENT '一级代理',
  `income_level_two` int(11) DEFAULT '0' COMMENT '二级代理',
  `income_level_three` int(11) DEFAULT '0' COMMENT '三级代理',
  `income_remark` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`income_id`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='代理的分成比例参照---公司 / 特代来设置';

-- ----------------------------
-- Table structure for dc_agent_income_config_bak
-- ----------------------------
DROP TABLE IF EXISTS `dc_agent_income_config_bak`;
CREATE TABLE `dc_agent_income_config_bak` (
  `income_id` int(11) NOT NULL AUTO_INCREMENT,
  `income_agent_id` int(11) DEFAULT '0' COMMENT '代理id 全局0 ',
  `income_promote_count` int(11) DEFAULT '0' COMMENT '人数条件',
  `income_count_level` int(11) DEFAULT '0' COMMENT '分成等级',
  `income_agent` int(11) DEFAULT '0' COMMENT '直属代理分成比例',
  `income_level_one` int(11) DEFAULT '0' COMMENT '一级代理',
  `income_level_two` int(11) DEFAULT '0' COMMENT '二级代理',
  `income_level_three` int(11) DEFAULT '0' COMMENT '三级代理',
  `income_remark` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`income_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='代理的分成比例参照---公司 / 特代来设置';

-- ----------------------------
-- Table structure for dc_agent_info
-- ----------------------------
DROP TABLE IF EXISTS `dc_agent_info`;
CREATE TABLE `dc_agent_info` (
  `agent_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '代理唯一id',
  `agent_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '后台登陆ID',
  `agent_player_id` int(11) DEFAULT '0' COMMENT '代理玩家id',
  `agent_parentid` int(11) DEFAULT '0' COMMENT '父类id',
  `agent_p_parentid` int(11) DEFAULT '0' COMMENT '父父级id',
  `agent_top_agentid` int(11) DEFAULT '0' COMMENT '顶级代理ID',
  `agent_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '代理昵名',
  `agent_level` int(5) DEFAULT '0' COMMENT '等级 1是特代 2是一级 一直累加',
  `agent_promote_count` int(11) DEFAULT '0' COMMENT '代理，玩家推广的人数 不断累加',
  `agent_permissions` int(5) DEFAULT '0' COMMENT '修改分成配置权限 0 否  1是',
  `agent_status` int(5) DEFAULT '1' COMMENT '特代（渠道）开启状态：0-禁用，1-开启',
  `agent_login_status` int(5) DEFAULT '0' COMMENT '登录H5后台权限 0是没有权限1是有权限',
  `agent_remark` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '备注',
  `agent_is_agree` tinyint(3) DEFAULT '0' COMMENT '是否同意推广协议：1-同意，0-未同意',
  `agent_orignal_type` tinyint(3) DEFAULT '1' COMMENT '玩家进入系统的初始身份，适用于渠道推广的下级：1-普通玩家，2-星级推广员',
  `agent_partner_id` int(11) DEFAULT '0' COMMENT '推广渠道ID',
  `agent_star_time` int(11) DEFAULT '0' COMMENT '成为星级推广员的时间',
  `agent_login_status_man` tinyint(3) DEFAULT '0' COMMENT '是否为人为开启的星级',
  `agent_createtime` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`agent_id`),
  KEY `agent_player_id` (`agent_player_id`),
  KEY `agent_login_status` (`agent_login_status`),
  KEY `agent_parentid` (`agent_parentid`),
  KEY `agent_user_id` (`agent_user_id`) USING BTREE,
  KEY `agent_top_agentid` (`agent_top_agentid`) USING BTREE,
  KEY `agent_name` (`agent_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=110437 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='代理信息表(包括账号id)';

-- ----------------------------
-- Table structure for dc_agent_notice
-- ----------------------------
DROP TABLE IF EXISTS `dc_agent_notice`;
CREATE TABLE `dc_agent_notice` (
  `agent_notice_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '公告id',
  `agent_notice_agent_id` int(11) DEFAULT '0' COMMENT '代理ID',
  `agent_notice_type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '公告类型 1= 系统公告，2=单个代理',
  `agent_notice_title` varchar(100) NOT NULL DEFAULT '' COMMENT '公告标题',
  `agent_notice_name` varchar(100) DEFAULT '' COMMENT '公告名',
  `agent_notice_content` varchar(255) NOT NULL DEFAULT '' COMMENT '公告内容',
  `agent_notice_create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '公告的创建时间',
  `agent_notice_param` varchar(255) DEFAULT '' COMMENT '保留字段',
  `agent_notice_status` int(5) DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`agent_notice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for dc_agent_notice~~
-- ----------------------------
DROP TABLE IF EXISTS `dc_agent_notice~~`;
CREATE TABLE `dc_agent_notice~~` (
  `agent_notice_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '公告id',
  `agent_notice_agent_id` int(11) DEFAULT '0' COMMENT '代理ID',
  `agent_notice_type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '公告类型 1= 系统公告，2=单个代理',
  `agent_notice_title` varchar(100) NOT NULL DEFAULT '' COMMENT '公告标题',
  `agent_notice_name` varchar(100) DEFAULT '' COMMENT '公告名',
  `agent_notice_content` varchar(255) NOT NULL DEFAULT '' COMMENT '公告内容',
  `agent_notice_create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '公告的创建时间',
  `agent_notice_param` varchar(255) DEFAULT '' COMMENT '保留字段',
  `agent_notice_status` int(5) DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`agent_notice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代理公告表';

-- ----------------------------
-- Table structure for dc_agent_super_income_config
-- ----------------------------
DROP TABLE IF EXISTS `dc_agent_super_income_config`;
CREATE TABLE `dc_agent_super_income_config` (
  `super_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `super_agent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '特代ID 默认0 全局',
  `super_condition` int(11) NOT NULL DEFAULT '0' COMMENT '条件',
  `super_condition_compare` varchar(4) COLLATE utf8_unicode_ci NOT NULL DEFAULT '<=' COMMENT '条件符号 < <=',
  `super_share` int(11) NOT NULL DEFAULT '0' COMMENT '分成比例 万分之',
  `super_share_ext` int(11) NOT NULL DEFAULT '0' COMMENT 'e额外分成比例 万分之',
  PRIMARY KEY (`super_id`)
) ENGINE=InnoDB AUTO_INCREMENT=369 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='特代分成比例配置';

-- ----------------------------
-- Table structure for dc_agent_super_income_config_bak
-- ----------------------------
DROP TABLE IF EXISTS `dc_agent_super_income_config_bak`;
CREATE TABLE `dc_agent_super_income_config_bak` (
  `super_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `super_agent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '特代ID 默认0 全局',
  `super_condition` int(11) NOT NULL DEFAULT '0' COMMENT '条件',
  `super_condition_compare` varchar(4) COLLATE utf8_unicode_ci NOT NULL DEFAULT '<=' COMMENT '条件符号 < <=',
  `super_share` int(11) NOT NULL DEFAULT '0' COMMENT '分成比例 万万分之',
  PRIMARY KEY (`super_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='特代分成比例配置';

-- ----------------------------
-- Table structure for dc_agent_super_statistics_date
-- ----------------------------
DROP TABLE IF EXISTS `dc_agent_super_statistics_date`;
CREATE TABLE `dc_agent_super_statistics_date` (
  `statistics_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `statistics_agent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '特代ID',
  `statistics_money_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '货币类型：1-金币',
  `statistics_money_data_direct` bigint(20) NOT NULL DEFAULT '0' COMMENT '旗下直属星级推广员的金币消耗，个',
  `statistics_money_data` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '除直属星级推广员之外的全部玩家的金币消耗，个',
  `statistics_date` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '统计时间 2018-01',
  `statistics_time` int(11) DEFAULT '0' COMMENT '时间戳',
  `statistics_month` int(11) DEFAULT '0',
  `statistics_super_share_direct` int(11) DEFAULT '0' COMMENT '旗下直属星级推广员的金币消耗 的提成比例  %万分之',
  `statistics_super_share` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '除直属星级推广员之外的分成比例 %万分之',
  `statistics_super_config` varchar(512) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '分成比例详情',
  `statistics_money_rate_value` int(11) NOT NULL DEFAULT '1' COMMENT '金币与人民币兑换比例',
  `statistics_money_rate_unit` int(11) NOT NULL DEFAULT '1' COMMENT '人民币',
  `statistics_money_rate_unit_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '分，角，元',
  `statistics_money` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分成计算结果：分',
  `statistics_money_status` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '状态0统计中 1 已经计算 2 已经结算入账',
  `statistics_up_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '统计更新时间',
  `statistics_add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '统计添加时间',
  PRIMARY KEY (`statistics_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='特代分成统计表';

-- ----------------------------
-- Table structure for dc_agent_super_statistics_date_ext
-- ----------------------------
DROP TABLE IF EXISTS `dc_agent_super_statistics_date_ext`;
CREATE TABLE `dc_agent_super_statistics_date_ext` (
  `statistics_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `statistics_agent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '特代ID',
  `statistics_money_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '货币类型：1-金币',
  `statistics_money_data_direct` bigint(20) NOT NULL DEFAULT '0' COMMENT '旗下直属星级推广员的金币消耗，个',
  `statistics_money_data` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '除直属星级推广员之外的全部玩家的金币消耗，个',
  `statistics_date` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '统计时间 2018-01',
  `statistics_time` int(11) DEFAULT '0' COMMENT '时间戳',
  `statistics_super_share_direct` int(11) DEFAULT '0' COMMENT '旗下直属星级推广员的金币消耗 的提成比例  %万分之',
  `statistics_super_share` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '除直属星级推广员之外的分成比例 %万分之',
  `statistics_super_share_ext` int(11) DEFAULT '0' COMMENT '额外收益比例',
  `statistics_money_rate_value` int(11) NOT NULL DEFAULT '1' COMMENT '金币与人民币兑换比例',
  `statistics_money` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '日结总计：分',
  `statistics_money_ext` int(11) DEFAULT '0' COMMENT '月度奖金：分',
  `statistics_money_all` int(11) DEFAULT '0' COMMENT '总收益：分',
  `statistics_add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '统计添加时间',
  PRIMARY KEY (`statistics_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='渠道月度奖金';

-- ----------------------------
-- Table structure for dc_agent_upgrade_record
-- ----------------------------
DROP TABLE IF EXISTS `dc_agent_upgrade_record`;
CREATE TABLE `dc_agent_upgrade_record` (
  `agent_upgrade_record_id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_upgrade_record_agent_id` int(11) DEFAULT '0' COMMENT '上级代理id',
  `agent_upgrade_record_player_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `agent_upgrade_record_time` int(11) DEFAULT '0' COMMENT '升级时间',
  PRIMARY KEY (`agent_upgrade_record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户升级成为代理记录表';

-- ----------------------------
-- Table structure for dc_auth_action
-- ----------------------------
DROP TABLE IF EXISTS `dc_auth_action`;
CREATE TABLE `dc_auth_action` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `role_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '角色ID',
  `action` varchar(256) NOT NULL DEFAULT '' COMMENT '方法URL',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=270 DEFAULT CHARSET=utf8 COMMENT='权限角色授权方法';

-- ----------------------------
-- Table structure for dc_auth_allow
-- ----------------------------
DROP TABLE IF EXISTS `dc_auth_allow`;
CREATE TABLE `dc_auth_allow` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `action` varchar(256) NOT NULL DEFAULT '' COMMENT '方法',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='权限白名单';

-- ----------------------------
-- Table structure for dc_auth_role
-- ----------------------------
DROP TABLE IF EXISTS `dc_auth_role`;
CREATE TABLE `dc_auth_role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `name` varchar(256) NOT NULL DEFAULT '' COMMENT '角色名称',
  `action_list` text,
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='权限角色名称';

-- ----------------------------
-- Table structure for dc_auth_user
-- ----------------------------
DROP TABLE IF EXISTS `dc_auth_user`;
CREATE TABLE `dc_auth_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `role_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '角色ID',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8 COMMENT='权限管理员授权权限';

-- ----------------------------
-- Table structure for dc_change_money_info
-- ----------------------------
DROP TABLE IF EXISTS `dc_change_money_info`;
CREATE TABLE `dc_change_money_info` (
  `change_money_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `change_money_player_id` int(11) DEFAULT '0' COMMENT '玩家id',
  `change_money_player_club_id` int(11) DEFAULT '0' COMMENT '玩家所在俱乐部id',
  `change_money_club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `change_money_club_room_id` int(11) DEFAULT '0' COMMENT '俱乐部房间id',
  `change_money_club_desk_no` int(11) DEFAULT '0' COMMENT '俱乐部桌子号',
  `change_money_club_desk_id` int(11) DEFAULT '0' COMMENT '桌子唯一id',
  `change_money_club_room_no` int(11) DEFAULT '0' COMMENT '包间号',
  `change_money_game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `change_money_room_id` int(11) DEFAULT '0' COMMENT '房间id',
  `change_money_desk_no` int(11) DEFAULT '0' COMMENT '桌子号',
  `change_money_type` int(11) DEFAULT '1' COMMENT '数据改变类型(1:充值,2:游戏消耗(包括服务费),3:单扣服务费、4新用户注册赠送)5.钻石对换 6.金库出入 7.道具消耗，8-新手礼包，9-系统添加，10-游戏中赠送，11-夺宝消耗，获得，12-每日福利获得',
  `change_money_tax` int(11) unsigned DEFAULT '0' COMMENT '服务费(税)',
  `change_money_money_type` int(11) DEFAULT '0' COMMENT '货币类型：1-金币 2,代币 3,奖券',
  `change_money_money_value` bigint(20) DEFAULT '0' COMMENT '改变货币值',
  `change_money_begin_value` bigint(20) DEFAULT '0' COMMENT '改变前数据',
  `change_money_after_value` bigint(20) DEFAULT '0' COMMENT '改变后数据',
  `change_money_time` int(11) DEFAULT '0' COMMENT '记录时间',
  `change_money_param` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '订单信息price_value(订单金额：分)',
  `change_money_update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  UNIQUE KEY `change_money_id` (`change_money_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=124085 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='货币币消耗数据信息队列(redis同步到database)';

-- ----------------------------
-- Table structure for dc_change_money_info_record
-- ----------------------------
DROP TABLE IF EXISTS `dc_change_money_info_record`;
CREATE TABLE `dc_change_money_info_record` (
  `change_money_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `change_money_player_id` int(11) DEFAULT '0' COMMENT '玩家id',
  `change_money_player_club_id` int(11) DEFAULT '0' COMMENT '玩家所在俱乐部id',
  `change_money_club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `change_money_club_room_id` int(11) DEFAULT '0' COMMENT '俱乐部房间id',
  `change_money_club_desk_no` int(11) DEFAULT '0' COMMENT '俱乐部桌子号',
  `change_money_club_desk_id` int(11) DEFAULT '0' COMMENT '桌子唯一id',
  `change_money_club_room_no` int(11) DEFAULT '0' COMMENT '包间号',
  `change_money_game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `change_money_room_id` int(11) DEFAULT '0' COMMENT '房间id',
  `change_money_desk_no` int(11) DEFAULT '0' COMMENT '桌子号',
  `change_money_type` int(11) DEFAULT '1' COMMENT '数据改变类型(1:充值,2:游戏消耗)',
  `change_money_tax` int(11) unsigned DEFAULT '0' COMMENT '服务费(税)',
  `change_money_money_type` int(11) DEFAULT '0' COMMENT '货币类型',
  `change_money_money_value` bigint(20) DEFAULT '0' COMMENT '改变货币值',
  `change_money_begin_value` bigint(20) DEFAULT '0' COMMENT '改变前数据',
  `change_money_after_value` bigint(20) DEFAULT '0' COMMENT '改变后数据',
  `change_money_time` int(11) DEFAULT '0' COMMENT '记录时间',
  `change_money_param` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '保留字段',
  `change_money_update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  UNIQUE KEY `change_money_id` (`change_money_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=131208 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='货币币消耗数据信息记录，数据保存3-5个月  备份表';

-- ----------------------------
-- Table structure for dc_channel_coins_log
-- ----------------------------
DROP TABLE IF EXISTS `dc_channel_coins_log`;
CREATE TABLE `dc_channel_coins_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) DEFAULT '0' COMMENT '渠道id',
  `channel_coins_before` bigint(20) DEFAULT '0' COMMENT '改变前',
  `channel_coins_change` bigint(20) DEFAULT '0' COMMENT '改变值',
  `channel_coins_after` bigint(20) DEFAULT '0' COMMENT '改变后',
  `channel_coins_change_type` tinyint(3) DEFAULT '0' COMMENT '金币变化类型：1-新手礼包，2-充值，3-返还',
  `channel_coins_gift_id` int(11) DEFAULT '0' COMMENT '如果是新手礼包支出：则为礼包id',
  `add_time` int(11) DEFAULT '0',
  `add_date` varchar(20) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COMMENT='渠道金币变化日志';

-- ----------------------------
-- Table structure for dc_channel_gift
-- ----------------------------
DROP TABLE IF EXISTS `dc_channel_gift`;
CREATE TABLE `dc_channel_gift` (
  `gift_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '礼包id',
  `gift_channel_id` int(11) DEFAULT '0' COMMENT '渠道id',
  `gift_value` bigint(20) DEFAULT '0' COMMENT '礼包总价值：金币数',
  `gift_count` int(11) DEFAULT '0' COMMENT '礼包含礼物个数',
  `gift_count_receive` int(11) DEFAULT '0' COMMENT '已领取的个数',
  `gift_single_value` bigint(20) DEFAULT '0' COMMENT '礼包中单个礼物价值：金币',
  `gift_status` tinyint(3) DEFAULT '1' COMMENT '礼包状态：1-未领完，2-已领完，3-失效',
  `gift_url` varchar(255) DEFAULT '',
  `gift_url_image` varchar(255) DEFAULT '' COMMENT '二维码地址',
  `gift_cancel_time` varchar(20) DEFAULT '' COMMENT '撤销时间',
  `gift_time` int(11) DEFAULT '0',
  `gift_date` varchar(20) DEFAULT '',
  PRIMARY KEY (`gift_id`),
  KEY `gift_channel_id` (`gift_channel_id`) USING BTREE,
  KEY `gift_value` (`gift_value`) USING BTREE,
  KEY `gift_status` (`gift_status`) USING BTREE,
  KEY `gift_time` (`gift_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COMMENT='新手礼包--渠道';

-- ----------------------------
-- Table structure for dc_channel_info
-- ----------------------------
DROP TABLE IF EXISTS `dc_channel_info`;
CREATE TABLE `dc_channel_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) DEFAULT '0' COMMENT '渠道id',
  `channel_coins` bigint(20) DEFAULT '0' COMMENT '渠道金币数',
  `add_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COMMENT='渠道附加信息';

-- ----------------------------
-- Table structure for dc_channel_promotion
-- ----------------------------
DROP TABLE IF EXISTS `dc_channel_promotion`;
CREATE TABLE `dc_channel_promotion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promotion_channel_id` int(11) DEFAULT '0' COMMENT '0-通用，渠道agent_id',
  `promotion_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '推广主题介绍',
  `promotion_desc` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `promotion_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '推广链接',
  `promotion_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '推广二维码模板',
  `promotion_status` tinyint(2) DEFAULT '1' COMMENT '是否启用',
  `promotion_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=167 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for dc_club_desk
-- ----------------------------
DROP TABLE IF EXISTS `dc_club_desk`;
CREATE TABLE `dc_club_desk` (
  `club_desk_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `club_desk_club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `club_desk_club_room_id` int(11) DEFAULT '0' COMMENT '俱乐部房间id',
  `club_desk_club_room_desk_no` int(11) DEFAULT '0' COMMENT '俱乐部桌子号',
  `club_desk_game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `club_desk_room_id` int(11) DEFAULT '0' COMMENT '房间id',
  `club_desk_desk_no` int(11) DEFAULT '0' COMMENT '桌子id',
  `club_desk_room_no` int(11) DEFAULT '0' COMMENT '包间号',
  `club_desk_player_id` int(11) DEFAULT '0' COMMENT '玩家id(创建者,备用字段)',
  `club_desk_param` varchar(1024) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '桌子参数',
  `club_desk_time` int(11) DEFAULT '0' COMMENT '时间',
  `club_desk_status` int(11) DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`club_desk_id`),
  UNIQUE KEY `club_desk_id` (`club_desk_id`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=49766 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='俱乐部信息表';

-- ----------------------------
-- Table structure for dc_club_desk_record
-- ----------------------------
DROP TABLE IF EXISTS `dc_club_desk_record`;
CREATE TABLE `dc_club_desk_record` (
  `club_desk_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `club_desk_club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `club_desk_club_room_id` int(11) DEFAULT '0' COMMENT '俱乐部房间id',
  `club_desk_club_room_desk_no` int(11) DEFAULT '0' COMMENT '俱乐部桌子号',
  `club_desk_game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `club_desk_room_id` int(11) DEFAULT '0' COMMENT '房间id',
  `club_desk_desk_no` int(11) DEFAULT '0' COMMENT '桌子id',
  `club_desk_room_no` int(11) DEFAULT '0' COMMENT '包间号',
  `club_desk_player_id` int(11) DEFAULT '0' COMMENT '创建者玩家id',
  `club_desk_param` varchar(1024) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '桌子参数',
  `club_desk_time` int(11) DEFAULT '0' COMMENT '时间',
  `club_desk_status` int(11) DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`club_desk_id`),
  KEY `club_desk_id` (`club_desk_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=49765 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='俱乐部信息表';

-- ----------------------------
-- Table structure for dc_club_game
-- ----------------------------
DROP TABLE IF EXISTS `dc_club_game`;
CREATE TABLE `dc_club_game` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE,
  KEY `club_id` (`game_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='俱乐部游戏';

-- ----------------------------
-- Table structure for dc_club_info
-- ----------------------------
DROP TABLE IF EXISTS `dc_club_info`;
CREATE TABLE `dc_club_info` (
  `club_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '电玩厅id',
  `club_name` varchar(300) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '电玩厅名称',
  `club_head_image` varchar(300) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '电玩厅头像',
  `club_status` int(5) DEFAULT '1' COMMENT '状态 0是关闭 1是正常',
  `club_time` int(11) DEFAULT '0' COMMENT '创建时间',
  `club_addr` varchar(250) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '电玩厅地址',
  `club_tel` varchar(20) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '电玩厅电话',
  `club_auth` tinyint(1) DEFAULT '0' COMMENT '是否店铺认证（0为否，1为是）',
  `club_pic` varchar(300) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '店铺实景照片',
  `club_money_rate` int(11) DEFAULT '1' COMMENT '代币比率(人民币单位为分)',
  PRIMARY KEY (`club_id`),
  UNIQUE KEY `club_id` (`club_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='俱乐部信息';

-- ----------------------------
-- Table structure for dc_club_player
-- ----------------------------
DROP TABLE IF EXISTS `dc_club_player`;
CREATE TABLE `dc_club_player` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `player_id` int(11) DEFAULT NULL,
  `player_tokens` bigint(20) DEFAULT '0' COMMENT '代币',
  `join_time` int(11) DEFAULT '0' COMMENT '加入时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=810 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='玩家和俱乐部信息表';

-- ----------------------------
-- Table structure for dc_club_room
-- ----------------------------
DROP TABLE IF EXISTS `dc_club_room`;
CREATE TABLE `dc_club_room` (
  `club_room_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `club_room_club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `club_room_game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `club_room_desk_count` int(11) DEFAULT '100' COMMENT '桌子数',
  `club_room_is_work` int(11) DEFAULT '0' COMMENT '是否开启(1是开启)',
  `club_room_is_open` int(11) DEFAULT '0' COMMENT '作为dc_room_info 字段的is_open',
  `club_room_type` int(11) DEFAULT '1' COMMENT '房间类型,1:坐下,2:搓桌,3:搓桌2,4: 创建包间',
  `club_room_level` int(11) DEFAULT '0' COMMENT '房间等级',
  `club_room_basic_points` int(11) DEFAULT '0' COMMENT '底分',
  `club_room_min_coin` int(11) DEFAULT '0' COMMENT '最低金币',
  `club_room_max_coin` int(11) DEFAULT '0' COMMENT '最大金币',
  `club_room_rule_id` int(11) DEFAULT '0' COMMENT '规则玩法id(dc_club_rule)',
  `club_room_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '房间名',
  `club_room_desk_param` varchar(1024) COLLATE utf8_unicode_ci DEFAULT '{}' COMMENT '桌子玩法',
  PRIMARY KEY (`club_room_id`),
  KEY `club_room_club_id` (`club_room_club_id`),
  KEY `club_room_game_id` (`club_room_game_id`),
  KEY `club_room_is_work` (`club_room_is_work`)
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='俱乐部房间信息 (虚拟出的房间信息)';

-- ----------------------------
-- Table structure for dc_club_rule
-- ----------------------------
DROP TABLE IF EXISTS `dc_club_rule`;
CREATE TABLE `dc_club_rule` (
  `club_room_rule_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `club_room_rule_game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `club_room_rule_club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `club_room_rule_name` varchar(11) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '名称',
  `club_room_rule_param` varchar(1024) COLLATE utf8_unicode_ci DEFAULT '{}' COMMENT '玩法规则',
  `club_room_rule_status` int(11) DEFAULT '1' COMMENT '状态0是禁用1是开启',
  UNIQUE KEY `club_room_rule_id` (`club_room_rule_id`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='游戏规则表';

-- ----------------------------
-- Table structure for dc_config
-- ----------------------------
DROP TABLE IF EXISTS `dc_config`;
CREATE TABLE `dc_config` (
  `config_id` int(11) NOT NULL AUTO_INCREMENT,
  `config_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '名称',
  `config_desc` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `config_type` tinyint(3) DEFAULT '0' COMMENT '类型：待定',
  `config_start_time` int(11) DEFAULT '0' COMMENT '开始时间',
  `config_end_time` int(11) DEFAULT '0' COMMENT '结束时间',
  `config_config` text COLLATE utf8_unicode_ci COMMENT '配置',
  `config_status` int(5) DEFAULT '1' COMMENT '状态 0 关闭 1 正常',
  `config_create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`config_id`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='综合配置表';

-- ----------------------------
-- Table structure for dc_dailygift_log
-- ----------------------------
DROP TABLE IF EXISTS `dc_dailygift_log`;
CREATE TABLE `dc_dailygift_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL DEFAULT '0',
  `nickname` varchar(350) DEFAULT '',
  `headurl` varchar(300) DEFAULT '',
  `gift_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `gift_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖品类型：1-红包，2-奖券，3-金币',
  `is_done` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否领取完成,0-未领，1-已领',
  `share_img` varchar(255) DEFAULT '' COMMENT '分享的图片地址',
  `done_time` int(10) unsigned DEFAULT '0' COMMENT '领取时间',
  `create_time` int(10) NOT NULL,
  `config_id` int(11) NOT NULL DEFAULT '2' COMMENT '配置id',
  `gift_ext` tinyint(4) DEFAULT '0' COMMENT '扩展：0-无，1-vip，2-月卡',
  PRIMARY KEY (`id`),
  KEY `index_player_id` (`player_id`) USING BTREE,
  KEY `index_create_time` (`create_time`) USING BTREE,
  KEY `index_gift_id` (`gift_id`) USING BTREE,
  KEY `index_config_id` (`config_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8 COMMENT='每日福利主表';

-- ----------------------------
-- Table structure for dc_dailygift_log_0
-- ----------------------------
DROP TABLE IF EXISTS `dc_dailygift_log_0`;
CREATE TABLE `dc_dailygift_log_0` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL DEFAULT '0',
  `nickname` varchar(350) DEFAULT '',
  `headurl` varchar(300) DEFAULT '',
  `gift_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `gift_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `is_done` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否领取完成,0-未领，1-已领',
  `share_img` varchar(255) DEFAULT '' COMMENT '分享的图片地址',
  `done_time` int(10) unsigned DEFAULT '0' COMMENT '领取时间',
  `create_time` int(10) NOT NULL,
  `config_id` int(11) NOT NULL DEFAULT '2' COMMENT '配置id',
  `gift_ext` tinyint(4) DEFAULT '0' COMMENT '扩展：0-无，1-vip，2-月卡',
  PRIMARY KEY (`id`),
  KEY `index_player_id` (`player_id`) USING BTREE,
  KEY `index_create_time` (`create_time`) USING BTREE,
  KEY `index_gift_id` (`gift_id`) USING BTREE,
  KEY `index_config_id` (`config_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_dailygift_log_1
-- ----------------------------
DROP TABLE IF EXISTS `dc_dailygift_log_1`;
CREATE TABLE `dc_dailygift_log_1` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL DEFAULT '0',
  `nickname` varchar(350) DEFAULT '',
  `headurl` varchar(300) DEFAULT '',
  `gift_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `gift_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `is_done` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否领取完成,0-未领，1-已领',
  `share_img` varchar(255) DEFAULT '' COMMENT '分享的图片地址',
  `done_time` int(10) unsigned DEFAULT '0' COMMENT '领取时间',
  `create_time` int(10) NOT NULL,
  `config_id` int(11) NOT NULL DEFAULT '2' COMMENT '配置id',
  `gift_ext` tinyint(4) DEFAULT '0' COMMENT '扩展：0-无，1-vip，2-月卡',
  PRIMARY KEY (`id`),
  KEY `index_player_id` (`player_id`) USING BTREE,
  KEY `index_create_time` (`create_time`) USING BTREE,
  KEY `index_gift_id` (`gift_id`) USING BTREE,
  KEY `index_config_id` (`config_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_dailygift_log_2
-- ----------------------------
DROP TABLE IF EXISTS `dc_dailygift_log_2`;
CREATE TABLE `dc_dailygift_log_2` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL DEFAULT '0',
  `nickname` varchar(350) DEFAULT '',
  `headurl` varchar(300) DEFAULT '',
  `gift_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `gift_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `is_done` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否领取完成,0-未领，1-已领',
  `share_img` varchar(255) DEFAULT '' COMMENT '分享的图片地址',
  `done_time` int(10) unsigned DEFAULT '0' COMMENT '领取时间',
  `create_time` int(10) NOT NULL,
  `config_id` int(11) NOT NULL DEFAULT '2' COMMENT '配置id',
  `gift_ext` tinyint(4) DEFAULT '0' COMMENT '扩展：0-无，1-vip，2-月卡',
  PRIMARY KEY (`id`),
  KEY `index_player_id` (`player_id`) USING BTREE,
  KEY `index_create_time` (`create_time`) USING BTREE,
  KEY `index_gift_id` (`gift_id`) USING BTREE,
  KEY `index_config_id` (`config_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_dailygift_log_3
-- ----------------------------
DROP TABLE IF EXISTS `dc_dailygift_log_3`;
CREATE TABLE `dc_dailygift_log_3` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL DEFAULT '0',
  `nickname` varchar(350) DEFAULT '',
  `headurl` varchar(300) DEFAULT '',
  `gift_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `gift_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `is_done` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否领取完成,0-未领，1-已领',
  `share_img` varchar(255) DEFAULT '' COMMENT '分享的图片地址',
  `done_time` int(10) unsigned DEFAULT '0' COMMENT '领取时间',
  `create_time` int(10) NOT NULL,
  `config_id` int(11) NOT NULL DEFAULT '2' COMMENT '配置id',
  `gift_ext` tinyint(4) DEFAULT '0' COMMENT '扩展：0-无，1-vip，2-月卡',
  PRIMARY KEY (`id`),
  KEY `index_player_id` (`player_id`) USING BTREE,
  KEY `index_create_time` (`create_time`) USING BTREE,
  KEY `index_gift_id` (`gift_id`) USING BTREE,
  KEY `index_config_id` (`config_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_dailygift_log_4
-- ----------------------------
DROP TABLE IF EXISTS `dc_dailygift_log_4`;
CREATE TABLE `dc_dailygift_log_4` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL DEFAULT '0',
  `nickname` varchar(350) DEFAULT '',
  `headurl` varchar(300) DEFAULT '',
  `gift_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `gift_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `is_done` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否领取完成,0-未领，1-已领',
  `share_img` varchar(255) DEFAULT '' COMMENT '分享的图片地址',
  `done_time` int(10) unsigned DEFAULT '0' COMMENT '领取时间',
  `create_time` int(10) NOT NULL,
  `config_id` int(11) NOT NULL DEFAULT '2' COMMENT '配置id',
  `gift_ext` tinyint(4) DEFAULT '0' COMMENT '扩展：0-无，1-vip，2-月卡',
  PRIMARY KEY (`id`),
  KEY `index_player_id` (`player_id`) USING BTREE,
  KEY `index_create_time` (`create_time`) USING BTREE,
  KEY `index_gift_id` (`gift_id`) USING BTREE,
  KEY `index_config_id` (`config_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_dailygift_log_5
-- ----------------------------
DROP TABLE IF EXISTS `dc_dailygift_log_5`;
CREATE TABLE `dc_dailygift_log_5` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL DEFAULT '0',
  `nickname` varchar(350) DEFAULT '',
  `headurl` varchar(300) DEFAULT '',
  `gift_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `gift_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `is_done` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否领取完成,0-未领，1-已领',
  `share_img` varchar(255) DEFAULT '' COMMENT '分享的图片地址',
  `done_time` int(10) unsigned DEFAULT '0' COMMENT '领取时间',
  `create_time` int(10) NOT NULL,
  `config_id` int(11) NOT NULL DEFAULT '2' COMMENT '配置id',
  `gift_ext` tinyint(4) DEFAULT '0' COMMENT '扩展：0-无，1-vip，2-月卡',
  PRIMARY KEY (`id`),
  KEY `index_player_id` (`player_id`) USING BTREE,
  KEY `index_create_time` (`create_time`) USING BTREE,
  KEY `index_gift_id` (`gift_id`) USING BTREE,
  KEY `index_config_id` (`config_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_dailygift_log_6
-- ----------------------------
DROP TABLE IF EXISTS `dc_dailygift_log_6`;
CREATE TABLE `dc_dailygift_log_6` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL DEFAULT '0',
  `nickname` varchar(350) DEFAULT '',
  `headurl` varchar(300) DEFAULT '',
  `gift_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `gift_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `is_done` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否领取完成,0-未领，1-已领',
  `share_img` varchar(255) DEFAULT '' COMMENT '分享的图片地址',
  `done_time` int(10) unsigned DEFAULT '0' COMMENT '领取时间',
  `create_time` int(10) NOT NULL,
  `config_id` int(11) NOT NULL DEFAULT '2' COMMENT '配置id',
  `gift_ext` tinyint(4) DEFAULT '0' COMMENT '扩展：0-无，1-vip，2-月卡',
  PRIMARY KEY (`id`),
  KEY `index_player_id` (`player_id`) USING BTREE,
  KEY `index_create_time` (`create_time`) USING BTREE,
  KEY `index_gift_id` (`gift_id`) USING BTREE,
  KEY `index_config_id` (`config_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_dailygift_log_7
-- ----------------------------
DROP TABLE IF EXISTS `dc_dailygift_log_7`;
CREATE TABLE `dc_dailygift_log_7` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL DEFAULT '0',
  `nickname` varchar(350) DEFAULT '',
  `headurl` varchar(300) DEFAULT '',
  `gift_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `gift_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `is_done` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否领取完成,0-未领，1-已领',
  `share_img` varchar(255) DEFAULT '' COMMENT '分享的图片地址',
  `done_time` int(10) unsigned DEFAULT '0' COMMENT '领取时间',
  `create_time` int(10) NOT NULL,
  `config_id` int(11) NOT NULL DEFAULT '2' COMMENT '配置id',
  `gift_ext` tinyint(4) DEFAULT '0' COMMENT '扩展：0-无，1-vip，2-月卡',
  PRIMARY KEY (`id`),
  KEY `index_player_id` (`player_id`) USING BTREE,
  KEY `index_create_time` (`create_time`) USING BTREE,
  KEY `index_gift_id` (`gift_id`) USING BTREE,
  KEY `index_config_id` (`config_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_dailygift_log_8
-- ----------------------------
DROP TABLE IF EXISTS `dc_dailygift_log_8`;
CREATE TABLE `dc_dailygift_log_8` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL DEFAULT '0',
  `nickname` varchar(350) DEFAULT '',
  `headurl` varchar(300) DEFAULT '',
  `gift_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `gift_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `is_done` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否领取完成,0-未领，1-已领',
  `share_img` varchar(255) DEFAULT '' COMMENT '分享的图片地址',
  `done_time` int(10) unsigned DEFAULT '0' COMMENT '领取时间',
  `create_time` int(10) NOT NULL,
  `config_id` int(11) NOT NULL DEFAULT '2' COMMENT '配置id',
  `gift_ext` tinyint(4) DEFAULT '0' COMMENT '扩展：0-无，1-vip，2-月卡',
  PRIMARY KEY (`id`),
  KEY `index_player_id` (`player_id`) USING BTREE,
  KEY `index_create_time` (`create_time`) USING BTREE,
  KEY `index_gift_id` (`gift_id`) USING BTREE,
  KEY `index_config_id` (`config_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_dailygift_log_9
-- ----------------------------
DROP TABLE IF EXISTS `dc_dailygift_log_9`;
CREATE TABLE `dc_dailygift_log_9` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL DEFAULT '0',
  `nickname` varchar(350) DEFAULT '',
  `headurl` varchar(300) DEFAULT '',
  `gift_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `gift_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '奖励id',
  `is_done` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否领取完成,0-未领，1-已领',
  `share_img` varchar(255) DEFAULT '' COMMENT '分享的图片地址',
  `done_time` int(10) unsigned DEFAULT '0' COMMENT '领取时间',
  `create_time` int(10) NOT NULL,
  `config_id` int(11) NOT NULL DEFAULT '2' COMMENT '配置id',
  `gift_ext` tinyint(4) DEFAULT '0' COMMENT '扩展：0-无，1-vip，2-月卡',
  PRIMARY KEY (`id`),
  KEY `index_player_id` (`player_id`) USING BTREE,
  KEY `index_create_time` (`create_time`) USING BTREE,
  KEY `index_gift_id` (`gift_id`) USING BTREE,
  KEY `index_config_id` (`config_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_feedback
-- ----------------------------
DROP TABLE IF EXISTS `dc_feedback`;
CREATE TABLE `dc_feedback` (
  `feedback_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `feedback_content` text,
  `feedback_player_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '反馈的用户id',
  `feedback_create_time` int(10) NOT NULL DEFAULT '0' COMMENT '反馈的时间',
  `feedback_phone` varchar(255) NOT NULL,
  PRIMARY KEY (`feedback_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COMMENT='反馈信息表';

-- ----------------------------
-- Table structure for dc_game_beat_record
-- ----------------------------
DROP TABLE IF EXISTS `dc_game_beat_record`;
CREATE TABLE `dc_game_beat_record` (
  `game_beat_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '战绩id',
  `game_beat_board_id` int(11) DEFAULT '0' COMMENT '对应牌局id',
  `game_beat_player_id` int(11) DEFAULT '0' COMMENT '玩家id',
  `game_beat_player_nick` varchar(300) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '玩家昵称',
  `game_beat_player_head` varchar(300) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '玩家头像',
  `game_beat_room_no` int(11) DEFAULT '0' COMMENT '包间战绩>0，金币场=0',
  `game_beat_readback` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '录像',
  `game_beat_over_time` int(11) DEFAULT '0' COMMENT '游戏结束时间',
  `game_beat_game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `game_beat_game_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '游戏名称',
  `game_beat_player_club_id` int(22) DEFAULT '0' COMMENT '玩家所在俱乐部id',
  `game_beat_club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `game_beat_win_state` tinyint(2) DEFAULT '0' COMMENT '输赢状态(0:输,1:赢)',
  `game_beat_score_type` int(11) DEFAULT '1' COMMENT '输赢分数类型(1:金币',
  `game_beat_score_value` int(11) DEFAULT '0' COMMENT '输赢分数值',
  `game_beat_time` int(11) DEFAULT '0' COMMENT '时间',
  `game_beat_room_id` int(11) DEFAULT '0' COMMENT '房间ID：使用club_room_id',
  `game_beat_room_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '房间名称',
  `game_beat_begin_time` int(11) DEFAULT '0' COMMENT '游戏开始时间（后面加的）',
  PRIMARY KEY (`game_beat_id`),
  KEY `game_beat_board_id` (`game_beat_board_id`) USING BTREE,
  KEY `game_beat_player_id` (`game_beat_player_id`) USING BTREE,
  KEY `game_beat_over_time` (`game_beat_over_time`) USING BTREE,
  KEY `game_beat_game_id` (`game_beat_game_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for dc_game_board_info
-- ----------------------------
DROP TABLE IF EXISTS `dc_game_board_info`;
CREATE TABLE `dc_game_board_info` (
  `game_board_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `game_board_room_id` int(11) DEFAULT '0' COMMENT '游戏房间id',
  `game_board_desk_no` int(11) DEFAULT '0' COMMENT '游戏桌子号',
  `game_board_game_over_time` int(11) DEFAULT '0' COMMENT '游戏一局结束时间',
  `game_board_time` int(11) DEFAULT '0' COMMENT '记录时间',
  PRIMARY KEY (`game_board_id`),
  UNIQUE KEY `game_board_id` (`game_board_id`) USING BTREE,
  KEY `game_board_room_id` (`game_board_room_id`) USING BTREE,
  KEY `game_board_desk_no` (`game_board_desk_no`) USING BTREE,
  KEY `game_board_game_over_time` (`game_board_game_over_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='生成牌局id辅助表(数据可以保留1周时间)';

-- ----------------------------
-- Table structure for dc_game_info
-- ----------------------------
DROP TABLE IF EXISTS `dc_game_info`;
CREATE TABLE `dc_game_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `game_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '游戏名字',
  `game_desk_members_count` int(11) DEFAULT '0' COMMENT '桌子人数',
  `game_kind` int(11) DEFAULT '1' COMMENT '游戏分类 1麻将 2牌类 3字牌',
  `game_version` int(11) DEFAULT '0' COMMENT '游戏服务端版本',
  `game_type` int(11) DEFAULT '0' COMMENT '游戏开房模式：1-搓桌，2-好友房，0-都有',
  `game_status` int(11) DEFAULT '0' COMMENT '状态 0为禁用 1为启用',
  `game_free` int(11) DEFAULT '0' COMMENT '0收费 1不收费',
  `game_sit_random` int(11) DEFAULT '0' COMMENT '1随机坐桌',
  `game_dissolve_time` int(11) DEFAULT '100' COMMENT '解散时间',
  `game_deduction_rate` int(11) DEFAULT '100' COMMENT '扣费率',
  `game_play_count` int(11) DEFAULT '0' COMMENT '每单位费用玩多少局',
  `game_active_time` int(11) DEFAULT '0' COMMENT '桌子有效时间(单位分)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `game_id` (`game_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='游戏信息';

-- ----------------------------
-- Table structure for dc_game_kind
-- ----------------------------
DROP TABLE IF EXISTS `dc_game_kind`;
CREATE TABLE `dc_game_kind` (
  `game_kind_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键,作为游戏分类的id',
  `game_kind_name` varchar(55) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '分类名称',
  `game_kind_type` tinyint(1) DEFAULT '1' COMMENT '扩展字段',
  `game_kind_time` int(11) DEFAULT '0' COMMENT '分类创建时间',
  PRIMARY KEY (`game_kind_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for dc_game_record
-- ----------------------------
DROP TABLE IF EXISTS `dc_game_record`;
CREATE TABLE `dc_game_record` (
  `game_record_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `game_record_player_id` int(11) DEFAULT '0' COMMENT '玩家id',
  `game_record_player_club_id` int(11) DEFAULT '0' COMMENT '玩家所在俱乐部id',
  `game_record_club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `game_record_club_room_id` int(11) DEFAULT '0' COMMENT '俱乐部房间id',
  `game_record_club_room_desk_no` int(11) DEFAULT '0' COMMENT '俱乐部桌子号',
  `game_record_club_desk_id` int(11) DEFAULT '0' COMMENT '俱乐部桌子id',
  `game_record_club_room_no` int(11) DEFAULT '0' COMMENT '包间号',
  `game_record_game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `game_record_room_id` int(11) DEFAULT '0' COMMENT '房间id',
  `game_record_desk_no` int(11) DEFAULT '0' COMMENT '桌子号',
  `game_record_win_state` int(11) DEFAULT '0' COMMENT '输赢状态(0:输,1:赢)',
  `game_record_score_type` int(11) DEFAULT '1' COMMENT '输赢分数类型(1:金币)',
  `game_record_score_value` int(11) DEFAULT '0' COMMENT '输赢分数值',
  `game_record_game_over_time` int(11) DEFAULT '0' COMMENT '游戏结束时间',
  `game_record_time` int(11) DEFAULT '0' COMMENT '记录时间',
  `game_record_desc` varchar(100) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '备注',
  `game_record_param` varchar(512) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '保留参数',
  `game_record_data_type` int(11) DEFAULT '0' COMMENT '游戏数据类型',
  `game_record_data_value` text COLLATE utf8_unicode_ci COMMENT '游戏数据值',
  `game_record_video_filename` varchar(60) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '录像文件',
  `game_record_update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `game_record_begin_time` int(11) DEFAULT '0' COMMENT '游戏开始时间(后面添加的）',
  PRIMARY KEY (`game_record_id`)
) ENGINE=InnoDB AUTO_INCREMENT=91733 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='游戏记录数据  队列';

-- ----------------------------
-- Table structure for dc_game_record_log
-- ----------------------------
DROP TABLE IF EXISTS `dc_game_record_log`;
CREATE TABLE `dc_game_record_log` (
  `game_log_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `game_log_board_id` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '牌局id',
  `game_log_player_id` int(11) DEFAULT '0' COMMENT '玩家id',
  `game_log_player_club_id` int(11) DEFAULT '0' COMMENT '玩家所在俱乐部id',
  `game_log_club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `game_log_club_room_id` int(11) DEFAULT '0' COMMENT '俱乐部房间id',
  `game_log_club_room_desk_no` int(11) DEFAULT '0' COMMENT '俱乐部桌子号',
  `game_log_club_desk_id` int(11) DEFAULT '0' COMMENT '俱乐部桌子id',
  `game_log_club_room_no` int(11) DEFAULT '0' COMMENT '包间号',
  `game_log_game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `game_log_room_id` int(11) DEFAULT '0' COMMENT '房间id',
  `game_log_desk_no` int(11) DEFAULT '0' COMMENT '桌子号',
  `game_log_win_state` int(11) DEFAULT '0' COMMENT '输赢状态(0:输,1:赢)',
  `game_log_score_type` int(11) DEFAULT '1' COMMENT '输赢分数类型(1:金币)',
  `game_log_score_value` int(11) DEFAULT '0' COMMENT '输赢分数值',
  `game_log_game_over_time` int(11) DEFAULT '0' COMMENT '游戏结束时间',
  `game_log_desc` varchar(100) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '备注',
  `game_log_param` varchar(512) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '保留参数',
  `game_log_data_type` int(11) DEFAULT '0' COMMENT '游戏数据类型',
  `game_log_data_value` text COLLATE utf8_unicode_ci COMMENT '游戏数据值',
  `game_log_game_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '游戏名称',
  `game_log_time` int(11) DEFAULT '0' COMMENT '记录时间',
  `game_log_data` varchar(30) COLLATE utf8_unicode_ci DEFAULT '',
  `game_log_video_filename` varchar(60) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '录像文件',
  `game_record_begin_time` int(11) DEFAULT '0' COMMENT '游戏开始时间（后面加的）',
  UNIQUE KEY `game_log_id` (`game_log_id`) USING BTREE,
  KEY `game_log_board_id` (`game_log_board_id`) USING BTREE,
  KEY `game_log_player_id` (`game_log_player_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='游戏记录数据 三个月数据';

-- ----------------------------
-- Table structure for dc_game_record_store
-- ----------------------------
DROP TABLE IF EXISTS `dc_game_record_store`;
CREATE TABLE `dc_game_record_store` (
  `game_log_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `game_log_board_id` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '牌局id',
  `game_log_player_id` int(11) DEFAULT '0' COMMENT '玩家id',
  `game_log_player_club_id` int(11) DEFAULT '0' COMMENT '玩家所在俱乐部id',
  `game_log_club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `game_log_club_room_id` int(11) DEFAULT '0' COMMENT '俱乐部房间id',
  `game_log_club_room_desk_no` int(11) DEFAULT '0' COMMENT '俱乐部桌子号',
  `game_log_club_desk_id` int(11) DEFAULT '0' COMMENT '俱乐部桌子id',
  `game_log_club_room_no` int(11) DEFAULT '0' COMMENT '包间号',
  `game_log_game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `game_log_room_id` int(11) DEFAULT '0' COMMENT '房间id',
  `game_log_desk_no` int(11) DEFAULT '0' COMMENT '桌子号',
  `game_log_win_state` int(11) DEFAULT '0' COMMENT '输赢状态(0:输,1:赢)',
  `game_log_score_type` int(11) DEFAULT '1' COMMENT '输赢分数类型(1:金币)',
  `game_log_score_value` int(11) DEFAULT '0' COMMENT '输赢分数值',
  `game_log_game_over_time` int(11) DEFAULT '0' COMMENT '游戏结束时间',
  `game_log_desc` varchar(100) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '备注',
  `game_log_param` varchar(512) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '保留参数',
  `game_log_data_type` int(11) DEFAULT '0' COMMENT '游戏数据类型',
  `game_log_data_value` text COLLATE utf8_unicode_ci COMMENT '游戏数据值',
  `game_log_game_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '游戏名称',
  `game_log_time` int(11) DEFAULT '0' COMMENT '记录时间',
  `game_log_data` varchar(30) COLLATE utf8_unicode_ci DEFAULT '',
  `game_log_video_filename` varchar(60) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '录像文件',
  `game_record_begin_time` int(11) DEFAULT '0' COMMENT '游戏开始时间（后面加的）',
  UNIQUE KEY `game_log_id` (`game_log_id`) USING BTREE,
  KEY `game_log_board_id` (`game_log_board_id`) USING BTREE,
  KEY `game_log_player_id` (`game_log_player_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='游戏记录数据备份';

-- ----------------------------
-- Table structure for dc_game_round_day
-- ----------------------------
DROP TABLE IF EXISTS `dc_game_round_day`;
CREATE TABLE `dc_game_round_day` (
  `game_round_id` int(11) NOT NULL AUTO_INCREMENT,
  `game_round_game_id` int(11) DEFAULT '0' COMMENT '游戏ID',
  `game_round_game_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '游戏',
  `game_round_channel_id` int(11) DEFAULT '0' COMMENT '渠道的数据：0-系统',
  `game_round_num` int(11) DEFAULT '0' COMMENT '累积局数',
  `game_round_coins` bigint(20) DEFAULT '0' COMMENT '当天消耗金币数',
  `game_round_day` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '时间',
  `game_round_timestamp` int(11) DEFAULT '0' COMMENT '时间戳',
  `game_round_createtime` int(11) DEFAULT '0' COMMENT '写入时间',
  PRIMARY KEY (`game_round_id`)
) ENGINE=InnoDB AUTO_INCREMENT=246 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='游戏局数统计/天';

-- ----------------------------
-- Table structure for dc_goods_exchange
-- ----------------------------
DROP TABLE IF EXISTS `dc_goods_exchange`;
CREATE TABLE `dc_goods_exchange` (
  `goods_exchange_id` int(255) NOT NULL AUTO_INCREMENT,
  `goods_exchange_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '名称',
  `goods_exchange_diamond` int(10) DEFAULT '0' COMMENT '钻石数量',
  `goods_exchange_type` int(5) DEFAULT '1' COMMENT '类型： 1钻石',
  `goods_exchange_get_price` bigint(10) DEFAULT '0' COMMENT '获得的金币数量',
  `goods_exchange_status` int(5) DEFAULT '1' COMMENT '状态 1是正常 2 关闭',
  `goods_exchange_desc` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '描述',
  `goods_exchange_time` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`goods_exchange_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='钻石对换列表';

-- ----------------------------
-- Table structure for dc_goods_exchange_log
-- ----------------------------
DROP TABLE IF EXISTS `dc_goods_exchange_log`;
CREATE TABLE `dc_goods_exchange_log` (
  `goods_exchange_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_exchange_log_playerid` int(11) DEFAULT '0' COMMENT '用户ID',
  `goods_exchange_log_exchange_id` int(11) DEFAULT '0' COMMENT '对换的商品ID',
  `goods_exchange_log_money_value` bigint(20) DEFAULT '0' COMMENT '改变值',
  `goods_exchange_log_begin_value` bigint(20) DEFAULT '0' COMMENT '改变前的值',
  `goods_exchange_log_after_value` bigint(20) DEFAULT '0' COMMENT '改变后的值',
  `goods_exchange_log_time` int(11) DEFAULT '0' COMMENT '创建时间',
  `goods_exchange_log_param` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '保存字段',
  PRIMARY KEY (`goods_exchange_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='钻石对换记录表';

-- ----------------------------
-- Table structure for dc_goods_info
-- ----------------------------
DROP TABLE IF EXISTS `dc_goods_info`;
CREATE TABLE `dc_goods_info` (
  `goods_id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '商品名称',
  `goods_price` int(11) DEFAULT '0' COMMENT '单位为分',
  `goods_type` int(11) DEFAULT '0' COMMENT '类型1余额2金币3彩票4代币 5钻石',
  `goods_status` int(11) DEFAULT '1' COMMENT '状态 0 是关闭 1是正常',
  `goods_card` int(11) DEFAULT '0' COMMENT '商品数量',
  `goods_club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `goods_desc` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '商品描述',
  `goods_time` int(11) DEFAULT '0' COMMENT '添加时间',
  `goods_get_price` bigint(21) DEFAULT '0' COMMENT '得到的货币值(如金币数量)',
  `goods_product_item` int(11) DEFAULT '0' COMMENT '苹果商品id',
  `goods_product_type` int(11) DEFAULT '1' COMMENT '1普通商品2苹果商品',
  `goods_product_id` varchar(100) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '苹果商品支付id',
  PRIMARY KEY (`goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='商品表';

-- ----------------------------
-- Table structure for dc_mail
-- ----------------------------
DROP TABLE IF EXISTS `dc_mail`;
CREATE TABLE `dc_mail` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_sender_type` tinyint(3) DEFAULT '0' COMMENT '发送者类型：0-系统，1-渠道',
  `mail_sender_id` int(11) DEFAULT '0' COMMENT '发送者：0-系统，渠道id',
  `mail_receiver_type` tinyint(3) DEFAULT '1' COMMENT '接受者：1-全部玩家，2-普通推广员，3-星级推广员',
  `mail_receiver_id` int(11) DEFAULT '0' COMMENT '接受者id',
  `mail_star_id` int(11) DEFAULT '0',
  `mail_channel_id` int(11) DEFAULT '0',
  `mail_type` tinyint(3) DEFAULT '1' COMMENT '邮件类型：1-普通邮件',
  `mail_model_id` int(11) DEFAULT '1' COMMENT '邮件场景：',
  `mail_title` text COMMENT '邮件标题',
  `mail_content` text,
  `mail_extension` tinyint(3) DEFAULT '0' COMMENT '是否含有附件：0-不含，1-含有',
  `mail_status` tinyint(3) DEFAULT '1' COMMENT '状态：1-未读，2-已读，3-删除',
  `mail_create_time` int(11) DEFAULT '0',
  `mail_create_date` varchar(20) DEFAULT '',
  `mail_read_time` int(11) DEFAULT '0' COMMENT '阅读时间',
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=114798 DEFAULT CHARSET=utf8 COMMENT='邮件列表';

-- ----------------------------
-- Table structure for dc_mail_0
-- ----------------------------
DROP TABLE IF EXISTS `dc_mail_0`;
CREATE TABLE `dc_mail_0` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_sender_type` tinyint(3) DEFAULT '0' COMMENT '发送者类型：0-系统，1-渠道',
  `mail_sender_id` int(11) DEFAULT '0' COMMENT '发送者：0-系统，渠道id',
  `mail_receiver_type` tinyint(3) DEFAULT '1' COMMENT '接受者：1-全部玩家，2-普通推广员，3-星级推广员',
  `mail_receiver_id` int(11) DEFAULT '0' COMMENT '接受者id',
  `mail_star_id` int(11) DEFAULT '0',
  `mail_channel_id` int(11) DEFAULT '0',
  `mail_type` tinyint(3) DEFAULT '1' COMMENT '邮件类型：1-普通邮件',
  `mail_model_id` int(11) DEFAULT '1' COMMENT '邮件场景：',
  `mail_title` text COMMENT '邮件标题',
  `mail_content` text,
  `mail_extension` tinyint(3) DEFAULT '0' COMMENT '是否含有附件：0-不含，1-含有',
  `mail_status` tinyint(3) DEFAULT '1' COMMENT '状态：1-未读，2-已读，3-删除',
  `mail_create_time` int(11) DEFAULT '0',
  `mail_create_date` varchar(20) DEFAULT '',
  `mail_read_time` int(11) DEFAULT '0' COMMENT '阅读时间',
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=510 DEFAULT CHARSET=utf8 COMMENT='邮件列表';

-- ----------------------------
-- Table structure for dc_mail_1
-- ----------------------------
DROP TABLE IF EXISTS `dc_mail_1`;
CREATE TABLE `dc_mail_1` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_sender_type` tinyint(3) DEFAULT '0' COMMENT '发送者类型：0-系统，1-渠道',
  `mail_sender_id` int(11) DEFAULT '0' COMMENT '发送者：0-系统，渠道id',
  `mail_receiver_type` tinyint(3) DEFAULT '1' COMMENT '接受者：1-全部玩家，2-普通推广员，3-星级推广员',
  `mail_receiver_id` int(11) DEFAULT '0' COMMENT '接受者id',
  `mail_star_id` int(11) DEFAULT '0',
  `mail_channel_id` int(11) DEFAULT '0',
  `mail_type` tinyint(3) DEFAULT '1' COMMENT '邮件类型：1-普通邮件',
  `mail_model_id` int(11) DEFAULT '1' COMMENT '邮件场景：',
  `mail_title` text COMMENT '邮件标题',
  `mail_content` text,
  `mail_extension` tinyint(3) DEFAULT '0' COMMENT '是否含有附件：0-不含，1-含有',
  `mail_status` tinyint(3) DEFAULT '1' COMMENT '状态：1-未读，2-已读，3-删除',
  `mail_create_time` int(11) DEFAULT '0',
  `mail_create_date` varchar(20) DEFAULT '',
  `mail_read_time` int(11) DEFAULT '0' COMMENT '阅读时间',
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=505 DEFAULT CHARSET=utf8 COMMENT='邮件列表';

-- ----------------------------
-- Table structure for dc_mail_2
-- ----------------------------
DROP TABLE IF EXISTS `dc_mail_2`;
CREATE TABLE `dc_mail_2` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_sender_type` tinyint(3) DEFAULT '0' COMMENT '发送者类型：0-系统，1-渠道',
  `mail_sender_id` int(11) DEFAULT '0' COMMENT '发送者：0-系统，渠道id',
  `mail_receiver_type` tinyint(3) DEFAULT '1' COMMENT '接受者：1-全部玩家，2-普通推广员，3-星级推广员',
  `mail_receiver_id` int(11) DEFAULT '0' COMMENT '接受者id',
  `mail_star_id` int(11) DEFAULT '0',
  `mail_channel_id` int(11) DEFAULT '0',
  `mail_type` tinyint(3) DEFAULT '1' COMMENT '邮件类型：1-普通邮件',
  `mail_model_id` int(11) DEFAULT '1' COMMENT '邮件场景：',
  `mail_title` text COMMENT '邮件标题',
  `mail_content` text,
  `mail_extension` tinyint(3) DEFAULT '0' COMMENT '是否含有附件：0-不含，1-含有',
  `mail_status` tinyint(3) DEFAULT '1' COMMENT '状态：1-未读，2-已读，3-删除',
  `mail_create_time` int(11) DEFAULT '0',
  `mail_create_date` varchar(20) DEFAULT '',
  `mail_read_time` int(11) DEFAULT '0' COMMENT '阅读时间',
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=486 DEFAULT CHARSET=utf8 COMMENT='邮件列表';

-- ----------------------------
-- Table structure for dc_mail_3
-- ----------------------------
DROP TABLE IF EXISTS `dc_mail_3`;
CREATE TABLE `dc_mail_3` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_sender_type` tinyint(3) DEFAULT '0' COMMENT '发送者类型：0-系统，1-渠道',
  `mail_sender_id` int(11) DEFAULT '0' COMMENT '发送者：0-系统，渠道id',
  `mail_receiver_type` tinyint(3) DEFAULT '1' COMMENT '接受者：1-全部玩家，2-普通推广员，3-星级推广员',
  `mail_receiver_id` int(11) DEFAULT '0' COMMENT '接受者id',
  `mail_star_id` int(11) DEFAULT '0',
  `mail_channel_id` int(11) DEFAULT '0',
  `mail_type` tinyint(3) DEFAULT '1' COMMENT '邮件类型：1-普通邮件',
  `mail_model_id` int(11) DEFAULT '1' COMMENT '邮件场景：',
  `mail_title` text COMMENT '邮件标题',
  `mail_content` text,
  `mail_extension` tinyint(3) DEFAULT '0' COMMENT '是否含有附件：0-不含，1-含有',
  `mail_status` tinyint(3) DEFAULT '1' COMMENT '状态：1-未读，2-已读，3-删除',
  `mail_create_time` int(11) DEFAULT '0',
  `mail_create_date` varchar(20) DEFAULT '',
  `mail_read_time` int(11) DEFAULT '0' COMMENT '阅读时间',
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=499 DEFAULT CHARSET=utf8 COMMENT='邮件列表';

-- ----------------------------
-- Table structure for dc_mail_4
-- ----------------------------
DROP TABLE IF EXISTS `dc_mail_4`;
CREATE TABLE `dc_mail_4` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_sender_type` tinyint(3) DEFAULT '0' COMMENT '发送者类型：0-系统，1-渠道',
  `mail_sender_id` int(11) DEFAULT '0' COMMENT '发送者：0-系统，渠道id',
  `mail_receiver_type` tinyint(3) DEFAULT '1' COMMENT '接受者：1-全部玩家，2-普通推广员，3-星级推广员',
  `mail_receiver_id` int(11) DEFAULT '0' COMMENT '接受者id',
  `mail_star_id` int(11) DEFAULT '0',
  `mail_channel_id` int(11) DEFAULT '0',
  `mail_type` tinyint(3) DEFAULT '1' COMMENT '邮件类型：1-普通邮件',
  `mail_model_id` int(11) DEFAULT '1' COMMENT '邮件场景：',
  `mail_title` text COMMENT '邮件标题',
  `mail_content` text,
  `mail_extension` tinyint(3) DEFAULT '0' COMMENT '是否含有附件：0-不含，1-含有',
  `mail_status` tinyint(3) DEFAULT '1' COMMENT '状态：1-未读，2-已读，3-删除',
  `mail_create_time` int(11) DEFAULT '0',
  `mail_create_date` varchar(20) DEFAULT '',
  `mail_read_time` int(11) DEFAULT '0' COMMENT '阅读时间',
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=520 DEFAULT CHARSET=utf8 COMMENT='邮件列表';

-- ----------------------------
-- Table structure for dc_mail_5
-- ----------------------------
DROP TABLE IF EXISTS `dc_mail_5`;
CREATE TABLE `dc_mail_5` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_sender_type` tinyint(3) DEFAULT '0' COMMENT '发送者类型：0-系统，1-渠道',
  `mail_sender_id` int(11) DEFAULT '0' COMMENT '发送者：0-系统，渠道id',
  `mail_receiver_type` tinyint(3) DEFAULT '1' COMMENT '接受者：1-全部玩家，2-普通推广员，3-星级推广员',
  `mail_receiver_id` int(11) DEFAULT '0' COMMENT '接受者id',
  `mail_star_id` int(11) DEFAULT '0',
  `mail_channel_id` int(11) DEFAULT '0',
  `mail_type` tinyint(3) DEFAULT '1' COMMENT '邮件类型：1-普通邮件',
  `mail_model_id` int(11) DEFAULT '1' COMMENT '邮件场景：',
  `mail_title` text COMMENT '邮件标题',
  `mail_content` text,
  `mail_extension` tinyint(3) DEFAULT '0' COMMENT '是否含有附件：0-不含，1-含有',
  `mail_status` tinyint(3) DEFAULT '1' COMMENT '状态：1-未读，2-已读，3-删除',
  `mail_create_time` int(11) DEFAULT '0',
  `mail_create_date` varchar(20) DEFAULT '',
  `mail_read_time` int(11) DEFAULT '0' COMMENT '阅读时间',
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=474 DEFAULT CHARSET=utf8 COMMENT='邮件列表';

-- ----------------------------
-- Table structure for dc_mail_6
-- ----------------------------
DROP TABLE IF EXISTS `dc_mail_6`;
CREATE TABLE `dc_mail_6` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_sender_type` tinyint(3) DEFAULT '0' COMMENT '发送者类型：0-系统，1-渠道',
  `mail_sender_id` int(11) DEFAULT '0' COMMENT '发送者：0-系统，渠道id',
  `mail_receiver_type` tinyint(3) DEFAULT '1' COMMENT '接受者：1-全部玩家，2-普通推广员，3-星级推广员',
  `mail_receiver_id` int(11) DEFAULT '0' COMMENT '接受者id',
  `mail_star_id` int(11) DEFAULT '0',
  `mail_channel_id` int(11) DEFAULT '0',
  `mail_type` tinyint(3) DEFAULT '1' COMMENT '邮件类型：1-普通邮件',
  `mail_model_id` int(11) DEFAULT '1' COMMENT '邮件场景：',
  `mail_title` text COMMENT '邮件标题',
  `mail_content` text,
  `mail_extension` tinyint(3) DEFAULT '0' COMMENT '是否含有附件：0-不含，1-含有',
  `mail_status` tinyint(3) DEFAULT '1' COMMENT '状态：1-未读，2-已读，3-删除',
  `mail_create_time` int(11) DEFAULT '0',
  `mail_create_date` varchar(20) DEFAULT '',
  `mail_read_time` int(11) DEFAULT '0' COMMENT '阅读时间',
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=517 DEFAULT CHARSET=utf8 COMMENT='邮件列表';

-- ----------------------------
-- Table structure for dc_mail_7
-- ----------------------------
DROP TABLE IF EXISTS `dc_mail_7`;
CREATE TABLE `dc_mail_7` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_sender_type` tinyint(3) DEFAULT '0' COMMENT '发送者类型：0-系统，1-渠道',
  `mail_sender_id` int(11) DEFAULT '0' COMMENT '发送者：0-系统，渠道id',
  `mail_receiver_type` tinyint(3) DEFAULT '1' COMMENT '接受者：1-全部玩家，2-普通推广员，3-星级推广员',
  `mail_receiver_id` int(11) DEFAULT '0' COMMENT '接受者id',
  `mail_star_id` int(11) DEFAULT '0',
  `mail_channel_id` int(11) DEFAULT '0',
  `mail_type` tinyint(3) DEFAULT '1' COMMENT '邮件类型：1-普通邮件',
  `mail_model_id` int(11) DEFAULT '1' COMMENT '邮件场景：',
  `mail_title` text COMMENT '邮件标题',
  `mail_content` text,
  `mail_extension` tinyint(3) DEFAULT '0' COMMENT '是否含有附件：0-不含，1-含有',
  `mail_status` tinyint(3) DEFAULT '1' COMMENT '状态：1-未读，2-已读，3-删除',
  `mail_create_time` int(11) DEFAULT '0',
  `mail_create_date` varchar(20) DEFAULT '',
  `mail_read_time` int(11) DEFAULT '0' COMMENT '阅读时间',
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=513 DEFAULT CHARSET=utf8 COMMENT='邮件列表';

-- ----------------------------
-- Table structure for dc_mail_8
-- ----------------------------
DROP TABLE IF EXISTS `dc_mail_8`;
CREATE TABLE `dc_mail_8` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_sender_type` tinyint(3) DEFAULT '0' COMMENT '发送者类型：0-系统，1-渠道',
  `mail_sender_id` int(11) DEFAULT '0' COMMENT '发送者：0-系统，渠道id',
  `mail_receiver_type` tinyint(3) DEFAULT '1' COMMENT '接受者：1-全部玩家，2-普通推广员，3-星级推广员',
  `mail_receiver_id` int(11) DEFAULT '0' COMMENT '接受者id',
  `mail_star_id` int(11) DEFAULT '0',
  `mail_channel_id` int(11) DEFAULT '0',
  `mail_type` tinyint(3) DEFAULT '1' COMMENT '邮件类型：1-普通邮件',
  `mail_model_id` int(11) DEFAULT '1' COMMENT '邮件场景：',
  `mail_title` text COMMENT '邮件标题',
  `mail_content` text,
  `mail_extension` tinyint(3) DEFAULT '0' COMMENT '是否含有附件：0-不含，1-含有',
  `mail_status` tinyint(3) DEFAULT '1' COMMENT '状态：1-未读，2-已读，3-删除',
  `mail_create_time` int(11) DEFAULT '0',
  `mail_create_date` varchar(20) DEFAULT '',
  `mail_read_time` int(11) DEFAULT '0' COMMENT '阅读时间',
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=498 DEFAULT CHARSET=utf8 COMMENT='邮件列表';

-- ----------------------------
-- Table structure for dc_mail_9
-- ----------------------------
DROP TABLE IF EXISTS `dc_mail_9`;
CREATE TABLE `dc_mail_9` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_sender_type` tinyint(3) DEFAULT '0' COMMENT '发送者类型：0-系统，1-渠道',
  `mail_sender_id` int(11) DEFAULT '0' COMMENT '发送者：0-系统，渠道id',
  `mail_receiver_type` tinyint(3) DEFAULT '1' COMMENT '接受者：1-全部玩家，2-普通推广员，3-星级推广员',
  `mail_receiver_id` int(11) DEFAULT '0' COMMENT '接受者id',
  `mail_star_id` int(11) DEFAULT '0',
  `mail_channel_id` int(11) DEFAULT '0',
  `mail_type` tinyint(3) DEFAULT '1' COMMENT '邮件类型：1-普通邮件',
  `mail_model_id` int(11) DEFAULT '1' COMMENT '邮件场景：',
  `mail_title` text COMMENT '邮件标题',
  `mail_content` text,
  `mail_extension` tinyint(3) DEFAULT '0' COMMENT '是否含有附件：0-不含，1-含有',
  `mail_status` tinyint(3) DEFAULT '1' COMMENT '状态：1-未读，2-已读，3-删除',
  `mail_create_time` int(11) DEFAULT '0',
  `mail_create_date` varchar(20) DEFAULT '',
  `mail_read_time` int(11) DEFAULT '0' COMMENT '阅读时间',
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=471 DEFAULT CHARSET=utf8 COMMENT='邮件列表';

-- ----------------------------
-- Table structure for dc_mail_extension
-- ----------------------------
DROP TABLE IF EXISTS `dc_mail_extension`;
CREATE TABLE `dc_mail_extension` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) DEFAULT '0',
  `gift_type` tinyint(3) DEFAULT '1' COMMENT '礼品类型：1-虚拟商品，2-实体商品',
  `gift_id` int(11) DEFAULT '1' COMMENT '礼品：1-金币，2-礼券',
  `gift_value` int(11) DEFAULT '0',
  `gift_status` tinyint(3) DEFAULT '1' COMMENT '礼物状态：1-待领，2-已领',
  `mail_id` int(11) DEFAULT '0' COMMENT '邮件id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='邮件附件';

-- ----------------------------
-- Table structure for dc_mail_model
-- ----------------------------
DROP TABLE IF EXISTS `dc_mail_model`;
CREATE TABLE `dc_mail_model` (
  `model_id` int(11) NOT NULL AUTO_INCREMENT,
  `model_status` tinyint(3) DEFAULT '1' COMMENT '1-开启，2-禁用',
  `model_content` text,
  `model_type` int(11) DEFAULT '0' COMMENT '类型：0-系统，1-渠道',
  `model_type_id` int(11) DEFAULT '0' COMMENT '0-系统，渠道id',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='邮件场景';

-- ----------------------------
-- Table structure for dc_mail_queue
-- ----------------------------
DROP TABLE IF EXISTS `dc_mail_queue`;
CREATE TABLE `dc_mail_queue` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_sender_type` tinyint(3) DEFAULT '0' COMMENT '发送者类型：0-系统，1-渠道',
  `mail_sender_id` int(11) DEFAULT '0' COMMENT '发送者：0-系统，渠道id',
  `mail_receiver_type` tinyint(3) DEFAULT '1' COMMENT '接受者：1-玩家，2-星级推广，3-渠道，4-所有',
  `mail_receiver_id` text NOT NULL COMMENT '接受者id',
  `mail_type` tinyint(3) DEFAULT '1' COMMENT '邮件类型：1-普通邮件',
  `mail_model_id` int(11) DEFAULT '1' COMMENT '邮件场景：',
  `mail_title` varchar(255) DEFAULT '',
  `mail_content` text,
  `mail_extension` tinyint(3) DEFAULT '0' COMMENT '是否含有附件：0-不含，1-含有',
  `mail_create_time` int(11) DEFAULT '0',
  `mail_create_date` varchar(20) DEFAULT '',
  `mail_send_time` int(11) DEFAULT '0' COMMENT '发送时间',
  `mail_update_time` int(11) DEFAULT '0',
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COMMENT='邮件列表';

-- ----------------------------
-- Table structure for dc_message
-- ----------------------------
DROP TABLE IF EXISTS `dc_message`;
CREATE TABLE `dc_message` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `message_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '消息类型 1.系统公告 2.BUG反馈',
  `message_apply_group` tinyint(1) NOT NULL DEFAULT '0' COMMENT '消息适用群体 1.所有玩家 2.俱乐部玩家 3.游戏玩家 4.房间玩家 5.桌子玩家 6.个人',
  `message_apply_group_params` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '消息适用群体参数配置 此字段只记录message_apply_group对应的俱乐部、游戏等等ID集合',
  `message_title` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '消息标题',
  `message_content` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '消息内容',
  `message_attach_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '消息附件类型 0.无附件 1.文字或语音 2.具体礼品 3.礼包',
  `message_attach_params` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '消息附件参数配置 对应附件类型 仅当附件类型为0以上时启用',
  `message_create_time` int(11) NOT NULL DEFAULT '0' COMMENT '消息创建时间',
  `message_remark` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '消息备注',
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='消息信息表';

-- ----------------------------
-- Table structure for dc_money_rate_info
-- ----------------------------
DROP TABLE IF EXISTS `dc_money_rate_info`;
CREATE TABLE `dc_money_rate_info` (
  `money_rate_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `money_rate_type` int(11) DEFAULT '0' COMMENT '货币类型(1:金币)',
  `money_rate_value` int(11) DEFAULT '0' COMMENT '兑率值',
  `money_rate_unit` int(11) DEFAULT '1' COMMENT '单位值',
  `money_rate_unit_type` int(11) DEFAULT '1' COMMENT '单位类型(1:元,2:角,3:分)',
  `money_rate_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '名',
  `money_rate_param` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '保留字段',
  PRIMARY KEY (`money_rate_id`),
  KEY `money_rate_id` (`money_rate_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='货币兑换比率信息表';

-- ----------------------------
-- Table structure for dc_new_bonus_num
-- ----------------------------
DROP TABLE IF EXISTS `dc_new_bonus_num`;
CREATE TABLE `dc_new_bonus_num` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` varchar(20) DEFAULT '',
  `time` int(11) DEFAULT '0' COMMENT '当天时间戳',
  `game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `num` int(11) DEFAULT '0' COMMENT '奖励领取数',
  `status` tinyint(3) DEFAULT '0' COMMENT '0-未完成，1-已完成',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_new_game_record
-- ----------------------------
DROP TABLE IF EXISTS `dc_new_game_record`;
CREATE TABLE `dc_new_game_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) DEFAULT '0' COMMENT '玩家id',
  `promoter_player_id` int(11) DEFAULT '0' COMMENT '推广人',
  `game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `win_num` int(11) DEFAULT '0' COMMENT '赢牌次数',
  `status` tinyint(3) DEFAULT '0' COMMENT '0-未达成，1-已达成，2-已发红包',
  `bonus_id` int(11) DEFAULT '0' COMMENT '红包id',
  `done_num` int(11) DEFAULT '0' COMMENT '达成数',
  `done_time` int(11) DEFAULT '0' COMMENT '达成时间',
  `done_date` varchar(20) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_new_player_detail
-- ----------------------------
DROP TABLE IF EXISTS `dc_new_player_detail`;
CREATE TABLE `dc_new_player_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promoter_player_id` int(11) DEFAULT '0' COMMENT '推广人',
  `player_id` int(11) DEFAULT '0' COMMENT '被推广人',
  `add_time` int(11) DEFAULT '0' COMMENT '推广时间',
  `add_date` varchar(20) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_new_promoter
-- ----------------------------
DROP TABLE IF EXISTS `dc_new_promoter`;
CREATE TABLE `dc_new_promoter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) DEFAULT '0' COMMENT '推广人id',
  `promote_num` int(11) DEFAULT '0' COMMENT '推广的人数',
  `promote_cost` bigint(20) DEFAULT '0' COMMENT '推广的玩家消耗金币数',
  `status` tinyint(3) DEFAULT '0' COMMENT '0-未达成，1-已达成，2-已发红包',
  `add_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0' COMMENT '达成时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_notice
-- ----------------------------
DROP TABLE IF EXISTS `dc_notice`;
CREATE TABLE `dc_notice` (
  `notice_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '公告id',
  `notice_title` varchar(100) NOT NULL DEFAULT '' COMMENT '公告标题',
  `notice_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '公告类型 1-系统公告，2-跑马灯公告, 3-后台',
  `notice_club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `notice_agent_id` varchar(255) NOT NULL DEFAULT '' COMMENT '渠道ID，多个渠道逗号分隔',
  `notice_start_id` varchar(1000) NOT NULL DEFAULT '' COMMENT '星级推广ID',
  `notice_name` varchar(100) DEFAULT '' COMMENT '公告名',
  `notice_content` text NOT NULL COMMENT '公告内容',
  `notice_create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公告的创建时间',
  `notice_param` varchar(255) DEFAULT '' COMMENT '保留字段',
  `notice_order_id` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `notice_status` int(5) DEFAULT '1' COMMENT '状态  0 是关闭 1 是正常',
  `notice_source` int(5) DEFAULT '1' COMMENT '信息来源 1是公司总后台 ，2渠道后台',
  PRIMARY KEY (`notice_id`)
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8 COMMENT='公告表';

-- ----------------------------
-- Table structure for dc_notice_read
-- ----------------------------
DROP TABLE IF EXISTS `dc_notice_read`;
CREATE TABLE `dc_notice_read` (
  `notice_read_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `notice_read_notice_id` int(11) DEFAULT NULL COMMENT '信息ID',
  `notice_read_player_id` int(11) DEFAULT '0' COMMENT '玩家ID',
  `notice_read_agent_id` int(11) DEFAULT '0' COMMENT '代理ID',
  `notice_read_status` int(5) DEFAULT '1' COMMENT '状态',
  `notice_read_time` int(11) DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`notice_read_id`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_order_log
-- ----------------------------
DROP TABLE IF EXISTS `dc_order_log`;
CREATE TABLE `dc_order_log` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_player_id` int(11) DEFAULT '0' COMMENT '下单玩家',
  `order_club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `order_goods_id` int(11) DEFAULT '0' COMMENT '商品id',
  `order_price` int(11) DEFAULT NULL COMMENT '价格:分',
  `order_get_type` int(11) DEFAULT '2' COMMENT '所得商品类型1.余额2.金币3.彩票4.代币5.钻石',
  `order_get_price` int(11) DEFAULT '0' COMMENT '得到的货币值(如金币数量)',
  `order_pay_type` int(11) DEFAULT '0' COMMENT '支付类型：1=android  2=ios',
  `order_is_send` int(11) DEFAULT '0' COMMENT '是否付款0没款1付款',
  `order_orderno` varchar(255) DEFAULT '' COMMENT '订单号',
  `order_out_transaction_id` varchar(255) DEFAULT '' COMMENT '第三方订单号',
  `order_create_time` int(11) DEFAULT '0' COMMENT '下单时间',
  `order_update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `order_extension` varchar(1000) DEFAULT '' COMMENT '订单的额外信息',
  `order_pay_channel` int(11) DEFAULT '1' COMMENT '支付渠道 1=微信支付，2=支付宝,3=苹果支付,4=web支付',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8 COMMENT='订单表';

-- ----------------------------
-- Table structure for dc_partner
-- ----------------------------
DROP TABLE IF EXISTS `dc_partner`;
CREATE TABLE `dc_partner` (
  `partner_id` int(11) NOT NULL AUTO_INCREMENT,
  `partner_name` varchar(255) DEFAULT NULL,
  `login_user_id` int(11) DEFAULT NULL,
  `channel_id` int(11) DEFAULT '0' COMMENT '对应的渠道id',
  `third_share_rate` float(11,4) DEFAULT '0.5000' COMMENT '第三方平台分成',
  `share_rate` float(11,4) DEFAULT '0.2500' COMMENT '渠道分成',
  `status` tinyint(3) DEFAULT '1' COMMENT '状态：1-开启，2-关闭',
  `remark` varchar(255) DEFAULT '',
  `create_time` int(11) DEFAULT '0',
  `create_date` varchar(20) DEFAULT '',
  PRIMARY KEY (`partner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1008 DEFAULT CHARSET=utf8 COMMENT='渠道合作商';

-- ----------------------------
-- Table structure for dc_partner_account
-- ----------------------------
DROP TABLE IF EXISTS `dc_partner_account`;
CREATE TABLE `dc_partner_account` (
  `account_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_partner_id` int(10) NOT NULL DEFAULT '0' COMMENT '代理商id',
  `account_money` int(11) DEFAULT '0' COMMENT '账户余额：分',
  `account_alipay` varchar(30) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '支付宝账号',
  `account_username` varchar(30) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '支付宝真实姓名',
  `account_mobile` varchar(20) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '支付宝手机号',
  `account_payment_password` varchar(50) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '支付密码',
  PRIMARY KEY (`account_id`),
  UNIQUE KEY `agent_account_agent_id` (`account_partner_id`) USING BTREE,
  KEY `agent_account_mobile` (`account_mobile`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='推广渠道账户';

-- ----------------------------
-- Table structure for dc_partner_account_log
-- ----------------------------
DROP TABLE IF EXISTS `dc_partner_account_log`;
CREATE TABLE `dc_partner_account_log` (
  `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `log_money_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '钱的类型金币 1人民币',
  `log_agent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '代理ID',
  `log_bef_money` int(11) NOT NULL DEFAULT '0' COMMENT '变动前金额：分',
  `log_money` int(11) NOT NULL DEFAULT '0' COMMENT '变动金额：分',
  `log_aft_money` int(11) NOT NULL DEFAULT '0' COMMENT '变动后金额：分',
  `log_add_time` int(10) NOT NULL DEFAULT '0' COMMENT 'add time',
  `log_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '1-渠道收益月进账 \r\n2-推广收益进账,\r\n3-代理收益日进账,\r\n4-提现出账',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='资金流水';

-- ----------------------------
-- Table structure for dc_partner_income_month
-- ----------------------------
DROP TABLE IF EXISTS `dc_partner_income_month`;
CREATE TABLE `dc_partner_income_month` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `partner_id` int(11) unsigned DEFAULT '0' COMMENT '渠道ID',
  `recharge_data` bigint(20) unsigned DEFAULT '0' COMMENT '充值金额：分',
  `date` varchar(20) COLLATE utf8_unicode_ci DEFAULT '0' COMMENT '统计时间 2018-01',
  `time` int(11) DEFAULT '0' COMMENT '时间戳',
  `third_share_rate` float(10,4) DEFAULT '0.0000' COMMENT '第三方平台分成',
  `share_rate` float(10,4) DEFAULT '0.0000' COMMENT '分成比例',
  `income` int(11) unsigned DEFAULT '0' COMMENT '分成计算结果：分',
  `status` tinyint(4) unsigned DEFAULT '0' COMMENT '状态0统计中 1 已经计算 2 已经结算入账',
  `update_time` int(11) unsigned DEFAULT '0' COMMENT '统计更新时间',
  `add_time` int(11) unsigned DEFAULT '0' COMMENT '统计添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='渠道分成统计表';

-- ----------------------------
-- Table structure for dc_partner_order
-- ----------------------------
DROP TABLE IF EXISTS `dc_partner_order`;
CREATE TABLE `dc_partner_order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_partner_id` int(11) DEFAULT '1' COMMENT '支付渠道 1=微信支付，2=支付宝,3=苹果支付,4=web支付',
  `order_player_id` int(11) DEFAULT '0' COMMENT '下单玩家',
  `order_third_uid` int(11) DEFAULT '0' COMMENT '第三方uid',
  `order_goods_id` int(11) DEFAULT '0' COMMENT '商品id',
  `order_price` int(11) DEFAULT NULL COMMENT '价格:分',
  `order_get_type` int(11) DEFAULT '2' COMMENT '所得商品类型1.余额2.金币3.彩票4.代币5.钻石',
  `order_get_money` int(11) DEFAULT '0' COMMENT '得到的货币值(如金币数量)',
  `order_pay_type` int(11) DEFAULT '0' COMMENT '支付(平台)类型：',
  `order_status` int(11) DEFAULT '0' COMMENT '是否付款：0-没款，1-付款',
  `order_orderno` varchar(255) DEFAULT '' COMMENT '订单号',
  `order_third_order_no` varchar(255) DEFAULT '' COMMENT '第三方订单号',
  `order_create_time` int(11) DEFAULT '0' COMMENT '下单时间',
  `order_update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `order_extension` varchar(1000) DEFAULT '' COMMENT '订单的额外信息',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='订单表';

-- ----------------------------
-- Table structure for dc_partner_pay_record
-- ----------------------------
DROP TABLE IF EXISTS `dc_partner_pay_record`;
CREATE TABLE `dc_partner_pay_record` (
  `recored_id` int(11) NOT NULL AUTO_INCREMENT,
  `recored_order_id` int(11) DEFAULT '0' COMMENT '订单id',
  `recored_player_id` int(11) NOT NULL DEFAULT '0' COMMENT '玩家id',
  `recored_partner_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `recored_type` int(11) NOT NULL DEFAULT '0' COMMENT '类型 1微信支付 2 支付宝支付,3 苹果支付,4web支付',
  `recored_price` int(11) NOT NULL DEFAULT '0' COMMENT '冲值金额(单位为分)',
  `recored_get_type` int(11) DEFAULT '2' COMMENT '所得商品类型1余额2金币3彩票',
  `recored_get_money` int(11) NOT NULL DEFAULT '0' COMMENT '得到的货币值(如金币数量)',
  `recored_before_money` bigint(21) NOT NULL DEFAULT '0' COMMENT '充值前金币',
  `recored_after_money` bigint(21) NOT NULL DEFAULT '0' COMMENT '充值后金币',
  `recored_create_time` int(11) NOT NULL DEFAULT '0' COMMENT '充值时间',
  PRIMARY KEY (`recored_id`),
  KEY `recore_player_id` (`recored_player_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='充值记录表';

-- ----------------------------
-- Table structure for dc_pay_record
-- ----------------------------
DROP TABLE IF EXISTS `dc_pay_record`;
CREATE TABLE `dc_pay_record` (
  `recore_id` int(11) NOT NULL AUTO_INCREMENT,
  `recore_player_id` int(11) NOT NULL DEFAULT '0' COMMENT '玩家id',
  `recore_club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `recore_player_nickname~~~！` varchar(40) DEFAULT '' COMMENT '玩家昵称 （待删除）',
  `recore_type` int(11) NOT NULL DEFAULT '0' COMMENT '类型 1微信支付 2 支付宝支付,3 苹果支付,4web支付',
  `recore_price` int(11) NOT NULL DEFAULT '0' COMMENT '冲值金额(单位为分)',
  `recore_get_type` int(11) DEFAULT '2' COMMENT '所得商品类型1余额2金币3彩票',
  `recore_get_price` int(11) NOT NULL DEFAULT '0' COMMENT '得到的货币值(如金币数量)',
  `recore_before_money` bigint(21) NOT NULL DEFAULT '0' COMMENT '充值前金币',
  `recore_order_id` int(11) DEFAULT '0' COMMENT '订单id',
  `recore_after_money` bigint(21) NOT NULL DEFAULT '0' COMMENT '充值后金币',
  `recore_create_time` int(11) NOT NULL DEFAULT '0' COMMENT '充值时间',
  PRIMARY KEY (`recore_id`),
  KEY `recore_player_id` (`recore_player_id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COMMENT='充值记录表';

-- ----------------------------
-- Table structure for dc_player
-- ----------------------------
DROP TABLE IF EXISTS `dc_player`;
CREATE TABLE `dc_player` (
  `player_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '玩家id',
  `player_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '玩家用户名',
  `player_nickname` varchar(350) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '玩家昵称',
  `player_password` varchar(50) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '密码',
  `player_salt` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '随机加盐',
  `player_phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '手机号码',
  `player_pcid` varchar(100) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '设备id',
  `player_openid_app` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT 'app授权',
  `player_openid_gzh` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '公众号授权',
  `player_unionid` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT 'unionid',
  `player_status` int(11) DEFAULT '1' COMMENT '玩家状态（1为正常 0为禁用）',
  `player_vip_level` int(11) DEFAULT '0' COMMENT 'vip等级',
  `player_resigter_time` int(11) DEFAULT '0' COMMENT '注册时间',
  `player_robot` int(11) DEFAULT '0' COMMENT '是否为机器人(1为机器人，0为真实用户)',
  `player_guest` int(11) DEFAULT '0' COMMENT '是否为游客玩家(1为游客，0注册用户)',
  `player_icon_id` int(11) DEFAULT '0' COMMENT '系统头像id',
  `player_identification_number` varchar(50) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '实名认证身份证号',
  `player_identification_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '实名认证姓名',
  `player_channel` tinyint(3) NOT NULL DEFAULT '0' COMMENT '1-andriod-pcid注册(游客)，2-ios-pcid注册(游客)，3-安卓微信注册 ，4-IOS微信注册 ，5-h5微信注册(微信推广)',
  PRIMARY KEY (`player_id`),
  UNIQUE KEY `player_id` (`player_id`) USING HASH,
  KEY `player_pcid` (`player_pcid`) USING BTREE,
  KEY `player_name` (`player_name`) USING HASH,
  KEY `player_resigter_time` (`player_resigter_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1114475 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='玩家基本信息';

-- ----------------------------
-- Table structure for dc_player_enjoy_long
-- ----------------------------
DROP TABLE IF EXISTS `dc_player_enjoy_long`;
CREATE TABLE `dc_player_enjoy_long` (
  `enjoy_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `enjoy_log_agent_id` int(11) DEFAULT '0' COMMENT '代理ID',
  `enjoy_player_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `enjoy_time` int(11) DEFAULT '0' COMMENT '分享时间 （时间戳）',
  `enjoy_time_day` varchar(30) COLLATE utf8_unicode_ci DEFAULT '0' COMMENT '时间显示',
  PRIMARY KEY (`enjoy_log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='分享记录表';

-- ----------------------------
-- Table structure for dc_player_enjoy_lottery_log
-- ----------------------------
DROP TABLE IF EXISTS `dc_player_enjoy_lottery_log`;
CREATE TABLE `dc_player_enjoy_lottery_log` (
  `enjoy_lottery_id` int(11) NOT NULL AUTO_INCREMENT,
  `enjoy_lottery_player_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `enjoy_lottery_agent_id` int(11) DEFAULT '0' COMMENT '代理ID',
  `enjoy_lottery` int(11) DEFAULT '0' COMMENT '赠送的礼券',
  `enjoy_begin_lottery` int(11) DEFAULT '0' COMMENT '分享前礼券',
  `enjoy_after_lottery` int(11) DEFAULT '0' COMMENT '分享后礼券',
  `enjoy_lottery_time` int(11) DEFAULT NULL COMMENT '时间戳',
  `enjoy_lottery_day` varchar(255) DEFAULT NULL COMMENT '显示时间',
  `enjoy_lottery_inday` varchar(255) DEFAULT '' COMMENT '插入时间',
  PRIMARY KEY (`enjoy_lottery_id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_player_info
-- ----------------------------
DROP TABLE IF EXISTS `dc_player_info`;
CREATE TABLE `dc_player_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `player_id` int(11) DEFAULT '0' COMMENT '玩家id',
  `player_money` bigint(20) DEFAULT '0' COMMENT '佘额',
  `player_coins` bigint(20) DEFAULT '0' COMMENT '金币',
  `player_masonry` bigint(20) DEFAULT '0' COMMENT '钻石',
  `player_safe_box` bigint(20) unsigned DEFAULT '0' COMMENT '保险箱',
  `player_safe_box_password` varchar(100) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '保险箱密码',
  `player_lottery` int(11) DEFAULT '0' COMMENT '奖券',
  `player_club_id` int(11) DEFAULT '0' COMMENT '绑定俱乐部id',
  `player_header_image` varchar(200) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '玩家图像',
  `player_sex` int(11) DEFAULT '1' COMMENT '性别（1男2女）',
  `player_signature` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '玩家个性签名',
  `player_login_time` int(11) DEFAULT '0',
  `player_login_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `player_author` int(2) DEFAULT '0' COMMENT '玩家实名认证',
  PRIMARY KEY (`id`),
  UNIQUE KEY `player_id` (`player_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=507206 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='玩家属性';

-- ----------------------------
-- Table structure for dc_player_message
-- ----------------------------
DROP TABLE IF EXISTS `dc_player_message`;
CREATE TABLE `dc_player_message` (
  `player_message_id` int(11) NOT NULL AUTO_INCREMENT,
  `player_message_message_id` int(11) NOT NULL DEFAULT '0' COMMENT '消息ID',
  `player_message_player_id` int(11) NOT NULL DEFAULT '0' COMMENT '玩家ID',
  `player_message_is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '消息是否已读  0.未读 1.已读',
  `player_message_is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '消息是否删除  0.未删除 1.已删除',
  `player_message_is_attach_receive` tinyint(1) NOT NULL DEFAULT '0' COMMENT '消息附件是否接收 0.未接收 1.已接收',
  `player_message_create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `player_message_modify_time` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`player_message_id`),
  KEY `union_message_player_time` (`player_message_message_id`,`player_message_player_id`,`player_message_create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='玩家消息信息表';

-- ----------------------------
-- Table structure for dc_player_promote_award_log
-- ----------------------------
DROP TABLE IF EXISTS `dc_player_promote_award_log`;
CREATE TABLE `dc_player_promote_award_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_promoter_id` int(11) DEFAULT '0' COMMENT '推广人player_id',
  `log_player_id` int(11) DEFAULT '0' COMMENT '被推广人player_id',
  `log_award` int(11) DEFAULT NULL COMMENT '奖励：分',
  `log_time` int(11) DEFAULT '0' COMMENT '记录时间',
  `log_date` varchar(20) COLLATE utf8_unicode_ci DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='推广奖励记录';

-- ----------------------------
-- Table structure for dc_player_propinfo
-- ----------------------------
DROP TABLE IF EXISTS `dc_player_propinfo`;
CREATE TABLE `dc_player_propinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `prop_player_id` int(11) DEFAULT '0' COMMENT '玩家id',
  `prop_id` int(11) DEFAULT '0' COMMENT '道具id',
  `prop_num` int(11) DEFAULT '0' COMMENT '道具数量',
  PRIMARY KEY (`id`),
  KEY `playerid` (`prop_player_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for dc_player_prop_log
-- ----------------------------
DROP TABLE IF EXISTS `dc_player_prop_log`;
CREATE TABLE `dc_player_prop_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_prop_club_id` int(11) NOT NULL DEFAULT '-1' COMMENT '道具所属俱乐部ID -1、适用于所有俱乐部',
  `log_prop_game_id` int(11) NOT NULL DEFAULT '-1' COMMENT '道具所属游戏ID -1、适用于俱乐部下所有游戏',
  `log_player_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '玩家ID',
  `log_prop_id` int(11) NOT NULL DEFAULT '0' COMMENT '道具ID',
  `log_prop_price` bigint(20) NOT NULL DEFAULT '0' COMMENT '道具单价 默认钱类型为金币',
  `log_action_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '道具行为类型 1、玩家非付费使用 2、玩家付费使用 3、玩家购买 4、玩家赠送 5、系统赠送',
  `log_action_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '道具行为状态 0、正常 1、无数量限制(某些道具没有数量限制或玩家为VIP)',
  `log_action_prop_num_before` int(11) NOT NULL DEFAULT '0' COMMENT '玩家道具行为前拥有数量',
  `log_action_num` int(11) NOT NULL DEFAULT '0' COMMENT '道具行为数量 发生道具行为时 所消耗或获得道具数量',
  `log_action_prop_num_after` int(11) NOT NULL DEFAULT '0' COMMENT '玩家道具行为后拥有数量',
  `log_action_total_fee` bigint(20) NOT NULL DEFAULT '0' COMMENT '道具行为总额 仅当道具行为类型为2和3时启用',
  `log_to_other_player_id` int(11) NOT NULL DEFAULT '0' COMMENT '其他玩家ID 仅当道具行为类型为4时 对其他玩家使用或赠送道具时启用',
  `log_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '记录时间',
  `log_remark` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=693 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='玩家道具使用记录表';

-- ----------------------------
-- Table structure for dc_player_prop_log_day
-- ----------------------------
DROP TABLE IF EXISTS `dc_player_prop_log_day`;
CREATE TABLE `dc_player_prop_log_day` (
  `log_day_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_day_player_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '玩家ID',
  `log_day_prop_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '道具ID',
  `log_day_prop_consumed_num` int(11) NOT NULL DEFAULT '0' COMMENT '玩家消耗的道具总数',
  `log_day_prop_get_num` int(11) NOT NULL DEFAULT '0' COMMENT '玩家获得的道具总数',
  `log_day_prop_total_fee` bigint(20) NOT NULL DEFAULT '0' COMMENT '玩家在道具上花费的金额总数 默认钱类型为金币',
  `log_day_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '日期',
  PRIMARY KEY (`log_day_id`),
  UNIQUE KEY `union_ps_pr_em_date` (`log_day_player_id`,`log_day_prop_id`,`log_day_date`)
) ENGINE=InnoDB AUTO_INCREMENT=276 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='玩家道具使用日志表(按天统计)';

-- ----------------------------
-- Table structure for dc_player_statistical
-- ----------------------------
DROP TABLE IF EXISTS `dc_player_statistical`;
CREATE TABLE `dc_player_statistical` (
  `statistical_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一ID',
  `statistical_player_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `statistical_agent_id` int(11) DEFAULT '0' COMMENT '上级代理id',
  `statistical_type` int(5) DEFAULT '0' COMMENT '统计的类型 1 金币消耗，3-累积充值（分）  (废弃）',
  `statistical_value` bigint(100) DEFAULT '0' COMMENT '个人总消耗金币',
  `statistical_top_up` bigint(100) DEFAULT '0' COMMENT '统计个人总充值 ：分',
  `statistical_sub_total_cost` int(11) DEFAULT '0' COMMENT '本人推广的所有人消耗总和（金币）',
  `statistical_award_money_status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '推广奖金状态 0条件没满足（还是推广员） 1满足条件并入账 （已是星级推广员）',
  `statistical_award_money` int(11) NOT NULL DEFAULT '0' COMMENT '奖金金额 :分',
  `statistical_award_status_time` int(11) DEFAULT '0' COMMENT '升级修改状态时间',
  `statistical_time` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '统计的时间',
  PRIMARY KEY (`statistical_id`),
  KEY `statistical_player_id` (`statistical_player_id`)
) ENGINE=InnoDB AUTO_INCREMENT=797 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='统计用户的累积消耗金币表 change_money_tax 字段的值';

-- ----------------------------
-- Table structure for dc_promoters_award_config
-- ----------------------------
DROP TABLE IF EXISTS `dc_promoters_award_config`;
CREATE TABLE `dc_promoters_award_config` (
  `award_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `award_agent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '特代ID 默认0 全局',
  `award_condition` int(11) NOT NULL DEFAULT '0' COMMENT '条件 消耗金币数',
  `award_money` int(11) NOT NULL DEFAULT '0' COMMENT '奖励金额 分',
  PRIMARY KEY (`award_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='推广奖励';

-- ----------------------------
-- Table structure for dc_promoters_info
-- ----------------------------
DROP TABLE IF EXISTS `dc_promoters_info`;
CREATE TABLE `dc_promoters_info` (
  `promoters_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `promoters_player_id` int(11) DEFAULT '0' COMMENT '玩家id',
  `promoters_parent_id` int(11) DEFAULT '0' COMMENT '玩家推广者id',
  `promoters_agent_id` int(11) DEFAULT '0' COMMENT '代理id',
  `promoters_agent_parentid` int(11) DEFAULT '0' COMMENT '上级代理ID',
  `promoters_agent_top_agentid` int(11) DEFAULT '0' COMMENT '顶级代理ID',
  `promoters_channel` varchar(200) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '用户推广的渠道',
  `promoters_time` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`promoters_id`),
  UNIQUE KEY `promoters_player_id` (`promoters_player_id`),
  KEY `promoters_parent_id` (`promoters_parent_id`),
  KEY `promoters_agent_id` (`promoters_agent_id`),
  KEY `promoters_agent_parentid` (`promoters_agent_parentid`),
  KEY `promoters_agent_top_agentid` (`promoters_agent_top_agentid`)
) ENGINE=InnoDB AUTO_INCREMENT=110394 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='玩家推广关系表';

-- ----------------------------
-- Table structure for dc_prop
-- ----------------------------
DROP TABLE IF EXISTS `dc_prop`;
CREATE TABLE `dc_prop` (
  `prop_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `prop_club_id` int(11) NOT NULL DEFAULT '-1' COMMENT '道具所属俱乐部ID -1、适用于所有俱乐部',
  `prop_game_id` int(11) NOT NULL DEFAULT '-1' COMMENT '道具所属游戏ID -1、适用于俱乐部下所有游戏',
  `prop_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '道具名称',
  `prop_category` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '道具分类 1、互动表情',
  `prop_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '道具类型(保留字段)',
  `prop_apply_group` tinyint(1) NOT NULL DEFAULT '0' COMMENT '道具适用群体 0、所有玩家 1、VIP',
  `prop_weight` smallint(5) NOT NULL DEFAULT '0' COMMENT '道具权重(用于排序 保留字段)',
  `prop_num` int(11) NOT NULL DEFAULT '0' COMMENT '道具数量(保留字段) -1、无数量限制',
  `prop_price` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '道具单价 默认单位金币',
  `prop_vip_level` tinyint(1) NOT NULL DEFAULT '0' COMMENT '哪一层级的VIP可用 当prop_apply_group为1时启用(保留字段)',
  `prop_specific_config` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '道具特有属性配置信息',
  `prop_expire_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '道具有效期(保留字段) 0、无有效期 其他、具体的有效期',
  `prop_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `prop_modified` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `prop_remark` varchar(1000) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`prop_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='道具信息表';

-- ----------------------------
-- Table structure for dc_prop_log
-- ----------------------------
DROP TABLE IF EXISTS `dc_prop_log`;
CREATE TABLE `dc_prop_log` (
  `prop_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `prop_log_player_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `prop_log_game_id` int(11) DEFAULT '0' COMMENT '游戏ID',
  `prop_log_game_name` varchar(255) DEFAULT NULL COMMENT '游戏名称',
  `prop_log_prop_id` int(11) DEFAULT '0' COMMENT '道具ID',
  `prop_log_coins` int(11) DEFAULT '0' COMMENT '消耗的金币',
  `prop_log_take_time` int(11) DEFAULT '0' COMMENT '记录时间',
  `prop_log_add_time` int(11) DEFAULT '0' COMMENT '生成时间',
  PRIMARY KEY (`prop_log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_ranking
-- ----------------------------
DROP TABLE IF EXISTS `dc_ranking`;
CREATE TABLE `dc_ranking` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ranking_player_id` int(10) unsigned NOT NULL COMMENT '玩家id',
  `ranking_number` int(11) DEFAULT '0' COMMENT '名次，1,2,3.，，，',
  `ranking_nick_name` varchar(300) NOT NULL DEFAULT '' COMMENT '玩家昵称',
  `ranking_player_image` varchar(300) NOT NULL DEFAULT '' COMMENT '玩家图像',
  `ranking_player_coins` int(10) DEFAULT '0' COMMENT '玩家的金币数量',
  `ranking_coins_type` tinyint(2) DEFAULT '1' COMMENT '类型：1-金币 2-代币',
  `ranking_time` varchar(20) DEFAULT '' COMMENT '时间',
  `ranking_clubid` int(11) DEFAULT '0' COMMENT '电玩厅id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='排行榜';

-- ----------------------------
-- Table structure for dc_robot_auto_logo
-- ----------------------------
DROP TABLE IF EXISTS `dc_robot_auto_logo`;
CREATE TABLE `dc_robot_auto_logo` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '字增长id',
  `headimage` varchar(200) DEFAULT '' COMMENT '图像路径',
  `usestate` tinyint(4) DEFAULT '0' COMMENT '使用状态',
  `gameid` int(11) DEFAULT '0' COMMENT '游戏id（0，表示可以使用所有的机器人）',
  `usetime` int(11) DEFAULT '0' COMMENT '使用时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='机器人自动更换logo';

-- ----------------------------
-- Table structure for dc_robot_auto_nickname
-- ----------------------------
DROP TABLE IF EXISTS `dc_robot_auto_nickname`;
CREATE TABLE `dc_robot_auto_nickname` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '字增长id',
  `nickname` varchar(350) DEFAULT '' COMMENT '昵称',
  `usestate` tinyint(4) DEFAULT '0' COMMENT '使用状态(0，未使用，1，使用中）',
  `gameid` int(11) DEFAULT '0' COMMENT '游戏id（如果有，则限定某些游戏使用，0，不限制）',
  `usetime` int(11) DEFAULT '0' COMMENT '使用时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='机器人自动更换nickname';

-- ----------------------------
-- Table structure for dc_room_info
-- ----------------------------
DROP TABLE IF EXISTS `dc_room_info`;
CREATE TABLE `dc_room_info` (
  `room_id` int(11) NOT NULL AUTO_INCREMENT,
  `room_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '房间名',
  `room_game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `room_rule` int(11) DEFAULT '0' COMMENT '游戏规则（1为金币场，0为练习币场）',
  `room_desk_count` int(11) DEFAULT '100' COMMENT '桌子数量',
  `room_status` int(11) DEFAULT '0' COMMENT '状态 0为禁用 1为启用',
  `room_is_open` int(11) DEFAULT '1' COMMENT '是否开启分配房间',
  `room_min_money` bigint(20) DEFAULT '0' COMMENT '进入最少金币 0为不限制',
  `room_max_money` bigint(20) DEFAULT '0' COMMENT '进入最大金币 0为不限制',
  `room_srv_host` varchar(50) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '房间服务器IP',
  `room_srv_port` int(11) DEFAULT '0' COMMENT '房间服务器端口',
  `room_srv_dll_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '加载游戏服务器组件dll模块名称',
  `room_encrypt` int(11) DEFAULT '0' COMMENT '是否加密房 1是，0不是',
  `room_password` varchar(50) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '房间密码',
  `room_tax` int(11) DEFAULT '0' COMMENT '税百分百比',
  `room_base_point` int(11) DEFAULT '0' COMMENT '房间底分',
  `room_vip` int(11) DEFAULT '0' COMMENT 'VIP等级限制（预留 默认0）',
  `room_type` int(11) DEFAULT '0' COMMENT '游戏桌类型，1百人游戏，0非百人游戏',
  PRIMARY KEY (`room_id`),
  KEY `room_id` (`room_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='房间信息';

-- ----------------------------
-- Table structure for dc_safe_box_log
-- ----------------------------
DROP TABLE IF EXISTS `dc_safe_box_log`;
CREATE TABLE `dc_safe_box_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `player_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '玩家的id',
  `trade_type` tinyint(2) unsigned DEFAULT '1' COMMENT '交易类型，1= 存入 2= 转出',
  `trade_cion` bigint(20) unsigned DEFAULT '0' COMMENT '交易的金币数量',
  `trade_time` int(10) DEFAULT '0' COMMENT '交易的时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='保险箱流水日志';

-- ----------------------------
-- Table structure for dc_statistics_total
-- ----------------------------
DROP TABLE IF EXISTS `dc_statistics_total`;
CREATE TABLE `dc_statistics_total` (
  `statistics_id` int(11) NOT NULL AUTO_INCREMENT,
  `statistics_role_type` tinyint(3) DEFAULT '0' COMMENT '基于渠道还是公司：0-公司，1-渠道商，2-推广员',
  `statistics_role_value` int(11) DEFAULT '0' COMMENT '渠道商id（agentid），0-公司/总的，推广员playerid',
  `statistics_mode` tinyint(3) DEFAULT '1' COMMENT '统计类型：1-充值金额（分），2-注册用户数（个），3-金币消耗数（个），4-活跃玩家数（个），5-赠送金币数（个），6-游戏玩家数（个），7-产出金币数（充值+赠送），8-剩余金币数（最终值）,9-所有推广员奖励（天，分）,10-所有推广员（不含星级）旗下玩家消耗金币数总计,11-道具消耗（只统计金币不计收益）',
  `statistics_type` tinyint(3) DEFAULT '0' COMMENT '统计时间段类型：1-小时，2-天，3-从开始总计',
  `statistics_sum` bigint(20) DEFAULT '0' COMMENT '累加：分',
  `statistics_money_rate` int(11) DEFAULT '0' COMMENT '金币兑换人民币比例：10000',
  `statistics_timestamp` int(11) DEFAULT '0' COMMENT '统计时间段时间戳',
  `statistics_datetime` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '统计时间段',
  `statistics_update` varchar(30) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '最新更新时间',
  `statistics_time` int(11) DEFAULT '0' COMMENT '记录时间',
  PRIMARY KEY (`statistics_id`),
  KEY `statistics_type` (`statistics_type`) USING BTREE,
  KEY `statistics_timestamp` (`statistics_timestamp`) USING BTREE,
  KEY `statistics_mode` (`statistics_mode`) USING BTREE,
  KEY `statistics_role_type` (`statistics_role_type`) USING BTREE,
  KEY `statistics_role_value` (`statistics_role_value`) USING BTREE,
  KEY `statistics_money_rate` (`statistics_money_rate`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=999999910 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='对各个指标按天，小时，总计来统计';

-- ----------------------------
-- Table structure for dc_stat_coin_result_info
-- ----------------------------
DROP TABLE IF EXISTS `dc_stat_coin_result_info`;
CREATE TABLE `dc_stat_coin_result_info` (
  `stat_data_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `stat_data_year` int(11) DEFAULT '0' COMMENT '年',
  `stat_data_month` int(11) DEFAULT '0' COMMENT '月',
  `stat_data_day` int(11) DEFAULT '0' COMMENT '天',
  `stat_data_hour` int(11) DEFAULT '0' COMMENT '小时',
  `stat_data_type` int(11) DEFAULT '0' COMMENT '数据类型',
  `stat_data_value` bigint(22) DEFAULT '0' COMMENT '数据值',
  `stat_data_club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `stat_data_game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `stat_data_player_id` int(11) DEFAULT '0' COMMENT '玩家id',
  `stat_data_agent_id` int(11) DEFAULT '0' COMMENT '代理id',
  UNIQUE KEY `stat_coin_id` (`stat_data_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='日，周，月，金币消耗  结果信息';

-- ----------------------------
-- Table structure for dc_system_config
-- ----------------------------
DROP TABLE IF EXISTS `dc_system_config`;
CREATE TABLE `dc_system_config` (
  `system_config_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `system_config_platform` int(11) DEFAULT '0' COMMENT '平台(0:所有平台,1:ios,2:android,3:windows)',
  `system_config_club_id` int(11) DEFAULT '0' COMMENT '俱乐部id',
  `system_config_type` int(11) DEFAULT '1' COMMENT '1:推送服务器',
  `system_config_data` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '保留字段',
  PRIMARY KEY (`system_config_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='系统配置信息表';

-- ----------------------------
-- Table structure for dc_sys_coin_change_log
-- ----------------------------
DROP TABLE IF EXISTS `dc_sys_coin_change_log`;
CREATE TABLE `dc_sys_coin_change_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) DEFAULT '0' COMMENT '玩家id',
  `action_user_id` int(11) DEFAULT '0' COMMENT '操作账号id',
  `action_user` varchar(255) DEFAULT '' COMMENT '操作员',
  `before_coin` bigint(20) DEFAULT '0' COMMENT '变化前金币',
  `modified_coin` bigint(20) DEFAULT '0' COMMENT '变化金币',
  `after_coin` bigint(20) DEFAULT '0' COMMENT '变化后金币',
  `type` tinyint(3) DEFAULT '0' COMMENT '类型：1-测试玩家，2-正式玩家',
  `mode` tinyint(4) DEFAULT '1' COMMENT '模式：1-总后台操作，2-新手礼包',
  `mode_id` int(11) DEFAULT '0' COMMENT 'mode为2时对应的礼包id',
  `mode_id_type` tinyint(3) DEFAULT '1' COMMENT '1-多人礼包，2-单人礼包',
  `channel_id` int(11) DEFAULT '0' COMMENT '玩家所属渠道id',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `add_time` int(11) DEFAULT '0',
  `add_date` varchar(30) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_treasure_config
-- ----------------------------
DROP TABLE IF EXISTS `dc_treasure_config`;
CREATE TABLE `dc_treasure_config` (
  `treasure_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '夺宝类别',
  `treasure_sort_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '任务类别',
  `treasure_singe_amount` int(11) NOT NULL DEFAULT '0' COMMENT '单次夺宝的下注额',
  `note_max_count` int(11) NOT NULL DEFAULT '0' COMMENT '下注最大次数（针对人满，秒杀为1）',
  `treasure_prize_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '奖励类型(0，金币，1奖券）',
  `treasure_prize_amount` int(11) NOT NULL DEFAULT '0' COMMENT '得奖数量',
  `treasure_note_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '下注类型（0，人满，1秒杀）',
  `user_note_max_count` int(11) NOT NULL DEFAULT '0' COMMENT '单个玩家针对本期类型最大投注次数',
  `treasure_show_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '显示类型0，不显示 ，1显示进行中，2即将推出',
  `treasure_explation` varchar(256) DEFAULT '',
  PRIMARY KEY (`treasure_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='一元夺宝配置表';

-- ----------------------------
-- Table structure for dc_treasure_period
-- ----------------------------
DROP TABLE IF EXISTS `dc_treasure_period`;
CREATE TABLE `dc_treasure_period` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增长',
  `treasure_period_no` int(11) NOT NULL DEFAULT '0' COMMENT '夺宝期号',
  `treasure_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '夺宝类别',
  `treasure_station` tinyint(4) NOT NULL DEFAULT '0' COMMENT '任务状态(0,初始 1，进行中 2，完成)',
  `treasure_count` int(11) NOT NULL DEFAULT '0' COMMENT '进度',
  `treasure_begin_time` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `treasure_status` int(11) DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='一元夺宝期号表，完成后会自动生成一期';

-- ----------------------------
-- Table structure for dc_treasure_prize
-- ----------------------------
DROP TABLE IF EXISTS `dc_treasure_prize`;
CREATE TABLE `dc_treasure_prize` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增长id',
  `treasure_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '玩家id',
  `treasure_period_no` int(11) NOT NULL DEFAULT '0' COMMENT '夺宝期号',
  `treasure_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '夺宝类别',
  `treasure_prize_time` int(11) NOT NULL DEFAULT '0' COMMENT '颁奖时间',
  `treasure_num_count` int(11) NOT NULL DEFAULT '0' COMMENT '参与人数数量',
  `treasure_get_prize` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否中奖',
  `treasure_user_totle_count` int(11) NOT NULL DEFAULT '0' COMMENT '玩家本轮总投注次数',
  `treasure_user_look` tinyint(4) NOT NULL DEFAULT '0' COMMENT '玩家是否已经查看',
  `treasure_prize_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '中奖玩家id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8 COMMENT='一元夺宝中奖表';

-- ----------------------------
-- Table structure for dc_treasure_user_note
-- ----------------------------
DROP TABLE IF EXISTS `dc_treasure_user_note`;
CREATE TABLE `dc_treasure_user_note` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增长ID',
  `treasure_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '下注玩家ID',
  `treasure_period_no` int(11) NOT NULL DEFAULT '0' COMMENT '夺宝期号',
  `treasure_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '夺宝类别',
  `treasure_singe_amount` int(11) NOT NULL DEFAULT '0' COMMENT '单次下注数量',
  `treasure_note_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '下注时间',
  `treasure_user_totle_count` int(11) NOT NULL DEFAULT '0' COMMENT '玩家下注总次数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_users
-- ----------------------------
DROP TABLE IF EXISTS `dc_users`;
CREATE TABLE `dc_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名',
  `user_pass` varchar(64) NOT NULL DEFAULT '' COMMENT '登录密码；sp_password加密',
  `user_email` varchar(64) DEFAULT NULL,
  `last_login_ip` varchar(16) DEFAULT NULL COMMENT '最后登录ip',
  `last_login_time` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '最后登录时间',
  `user_type` tinyint(3) DEFAULT '1' COMMENT '1-总后台，2-渠道后台，3-推广渠道后台',
  `create_time` int(11) NOT NULL COMMENT '注册时间',
  `user_status` int(11) NOT NULL DEFAULT '1' COMMENT '用户状态 0：禁用； 1：正常 ；2：未验证',
  PRIMARY KEY (`id`),
  KEY `user_login_key` (`user_login`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=154 DEFAULT CHARSET=utf8 COMMENT='用户表';

-- ----------------------------
-- Table structure for dc_users_action_log
-- ----------------------------
DROP TABLE IF EXISTS `dc_users_action_log`;
CREATE TABLE `dc_users_action_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_user_id` int(11) DEFAULT '0' COMMENT 'user_id',
  `log_action_type` tinyint(11) DEFAULT '0' COMMENT '操作类别：1-新增，2-修改，3-删除',
  `log_action_name` varchar(255) DEFAULT '' COMMENT '操作描述',
  `log_action_before` text,
  `log_action_after` text,
  `log_action_ip` varchar(255) DEFAULT '' COMMENT '操作ip',
  `log_add_time` int(11) DEFAULT '0',
  `log_add_date` varchar(30) DEFAULT '',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=175 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for dc_withdraw_log
-- ----------------------------
DROP TABLE IF EXISTS `dc_withdraw_log`;
CREATE TABLE `dc_withdraw_log` (
  `withdraw_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `withdraw_agent_id` int(11) DEFAULT '0' COMMENT '代理id',
  `withdraw_player_id` int(11) DEFAULT '0' COMMENT '用户player_id',
  `withdraw_channel_id` int(11) DEFAULT '0' COMMENT '所属渠道ID',
  `withdraw_before_money` bigint(10) DEFAULT '0' COMMENT '提现前的账号余额：分',
  `withdraw_money` bigint(10) DEFAULT '0' COMMENT '提现金额：分',
  `withdraw_after_money` bigint(10) DEFAULT '0' COMMENT '提现后的金额：分',
  `withdraw_poundage` bigint(10) DEFAULT '0' COMMENT '提现手续费：分',
  `withdraw_get_money` bigint(10) DEFAULT '0' COMMENT '实际到账金额',
  `withdraw_method` tinyint(3) DEFAULT '1' COMMENT '提现方式：1-微信红包，2-支付宝',
  `withdraw_method_log_id` varchar(255) DEFAULT '' COMMENT '如果是微信红包，则是红包id',
  `withdraw_type` tinyint(5) DEFAULT '0' COMMENT '0= 自动提现，1= 人工审批',
  `withdraw_status` tinyint(2) DEFAULT '0' COMMENT '提现状态 0申请中，1已审核，2打款中，3已打款，,4拒绝提现',
  `withdraw_approve_time` int(11) DEFAULT '0' COMMENT '审批时间',
  `withdraw_create_time` int(11) DEFAULT '0' COMMENT '提现申请时间',
  `withdraw_create_date` varchar(20) DEFAULT '',
  PRIMARY KEY (`withdraw_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代理提现表';

-- ----------------------------
-- Table structure for dc_wx_bonus_log
-- ----------------------------
DROP TABLE IF EXISTS `dc_wx_bonus_log`;
CREATE TABLE `dc_wx_bonus_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(11) unsigned DEFAULT '0' COMMENT '玩家id',
  `agent_id` int(11) DEFAULT '0' COMMENT 'agentid',
  `openid_gzh` varchar(255) DEFAULT '' COMMENT '用户对于公众号的 openid',
  `mch_billno` varchar(255) DEFAULT '' COMMENT '本地订单编号',
  `send_listid` varchar(255) DEFAULT '' COMMENT '红包订单编号，微信返回的',
  `total_amount` int(11) unsigned DEFAULT '0' COMMENT '红包总金额：分',
  `total_num` smallint(5) unsigned DEFAULT '1' COMMENT '红包发送总人数',
  `type` int(11) DEFAULT '0' COMMENT '类型：1-提现，2-邀新活动红包，3-每日活动',
  `type_name` varchar(255) DEFAULT '' COMMENT '类型名称：待定',
  `status` tinyint(4) DEFAULT '0' COMMENT '红包状态：\r\n0-未处理\r\n1-SENDING:发放中\r\n2-SENT:已发放待领取\r\n3-FAILED：发放失败\r\n4-RECEIVED:已领取\r\n5-RFUND_ING:退款中\r\n6-REFUND:已退款',
  `send_time` varchar(30) DEFAULT '' COMMENT '发送红包时间',
  `receive_time` varchar(30) DEFAULT '' COMMENT '领取时间',
  `refund_time` varchar(30) DEFAULT '' COMMENT '退回时间',
  `create_time` varchar(30) DEFAULT '' COMMENT '写入时间',
  `trace_time` int(11) DEFAULT '0' COMMENT '检查时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mch_billno` (`mch_billno`) USING BTREE COMMENT '唯一订单号'
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for jd_area
-- ----------------------------
DROP TABLE IF EXISTS `jd_area`;
CREATE TABLE `jd_area` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentid` int(12) NOT NULL COMMENT '父id',
  `cityname` varchar(180) NOT NULL DEFAULT '' COMMENT '城市名字',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53693 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for jd_banner
-- ----------------------------
DROP TABLE IF EXISTS `jd_banner`;
CREATE TABLE `jd_banner` (
  `banner_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `banner_tag_id` int(11) DEFAULT '0' COMMENT 'banner图对应一个tagID',
  `banner_goods_id` int(11) DEFAULT '0' COMMENT '商品ID',
  `banner_img_url` varchar(300) COLLATE utf8_unicode_ci DEFAULT '' COMMENT 'banner图片地址',
  `banner_status` int(1) DEFAULT '0' COMMENT '0不可用，1可用',
  `banner_type` int(11) DEFAULT '0' COMMENT '0商品类型跳珠，1商品跳转',
  `banner_order_id` int(11) DEFAULT '0' COMMENT '排序ID，越大越后',
  `banner_desc` varchar(300) COLLATE utf8_unicode_ci DEFAULT '' COMMENT 'banner描述',
  `banner_create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`banner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for jd_category
-- ----------------------------
DROP TABLE IF EXISTS `jd_category`;
CREATE TABLE `jd_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentid` int(12) NOT NULL,
  `categoryname` varchar(180) NOT NULL,
  `order_id` int(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for jd_gift
-- ----------------------------
DROP TABLE IF EXISTS `jd_gift`;
CREATE TABLE `jd_gift` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '名字',
  `img_url` varchar(255) DEFAULT '' COMMENT '图片',
  `params` varchar(255) DEFAULT '' COMMENT '参数',
  `desc` varchar(100) DEFAULT '' COMMENT '产品描述',
  `voucher` int(11) DEFAULT NULL COMMENT '兑换该商品所需的礼券',
  `order_id` int(11) unsigned DEFAULT '0' COMMENT '礼品来进行排序，ID越小排越前',
  `status` int(1) DEFAULT '0' COMMENT '状态',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `cid` int(11) NOT NULL COMMENT '所属分类的id',
  `tagid` smallint(5) unsigned DEFAULT '0' COMMENT '标签',
  `channel` varchar(4) DEFAULT '' COMMENT '默认0:全部，1：APP，2：微信公众号',
  `sku` bigint(20) DEFAULT NULL COMMENT '京东商品号',
  `is_jd` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1是京东，2是话费，3是流量',
  PRIMARY KEY (`id`),
  KEY `sku` (`sku`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=178 DEFAULT CHARSET=utf8 COMMENT='礼品表';

-- ----------------------------
-- Table structure for jd_gift_bak
-- ----------------------------
DROP TABLE IF EXISTS `jd_gift_bak`;
CREATE TABLE `jd_gift_bak` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '名字',
  `img_url` varchar(255) DEFAULT '' COMMENT '图片',
  `params` varchar(255) DEFAULT '' COMMENT '参数',
  `desc` varchar(100) DEFAULT '' COMMENT '产品描述',
  `voucher` int(11) DEFAULT NULL COMMENT '兑换该商品所需的礼券',
  `order_id` int(11) unsigned DEFAULT '0' COMMENT '礼品来进行排序，ID越小排越前',
  `status` int(1) DEFAULT '0' COMMENT '状态',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `cid` int(11) NOT NULL COMMENT '所属分类的id',
  `tagid` smallint(5) unsigned DEFAULT '0' COMMENT '标签',
  `channel` varchar(4) DEFAULT '' COMMENT '默认0:全部，1：APP，2：微信公众号',
  `sku` bigint(20) DEFAULT NULL COMMENT '京东商品号',
  `is_jd` tinyint(3) unsigned NOT NULL COMMENT '是否京东：1是，0否；',
  PRIMARY KEY (`id`),
  KEY `sku` (`sku`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='礼品表';

-- ----------------------------
-- Table structure for jd_jdorder
-- ----------------------------
DROP TABLE IF EXISTS `jd_jdorder`;
CREATE TABLE `jd_jdorder` (
  `jdOrderId` char(50) NOT NULL COMMENT '京东订单号',
  `freight` decimal(8,2) DEFAULT NULL COMMENT '订单总运费 = 基础运费 + 总的超重偏远附加运费',
  `orderPrice` decimal(10,2) DEFAULT NULL COMMENT '商品总价格',
  `orderNakedPrice` decimal(10,2) DEFAULT NULL COMMENT '订单裸价',
  `orderTaxPrice` decimal(10,2) DEFAULT NULL COMMENT '订单税额',
  `skuId` bigint(20) DEFAULT NULL COMMENT '商品ID',
  `num` int(11) NOT NULL DEFAULT '1' COMMENT '数量',
  `category` int(11) DEFAULT NULL COMMENT '分类',
  `price` decimal(10,2) DEFAULT NULL COMMENT '商品价格',
  `name` varchar(255) DEFAULT NULL COMMENT '商品名字',
  `tax` int(11) DEFAULT NULL COMMENT '税率',
  `taxPrice` decimal(8,2) DEFAULT NULL COMMENT '税额',
  `nakedPrice` decimal(8,2) DEFAULT NULL COMMENT '商品裸价',
  `type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0，普通，1，附件，2，赠品',
  `oid` int(11) NOT NULL DEFAULT '0' COMMENT '主商品ID',
  `remoteRegionFrieight` decimal(8,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`jdOrderId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for jd_msg_log
-- ----------------------------
DROP TABLE IF EXISTS `jd_msg_log`;
CREATE TABLE `jd_msg_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mobile` varchar(12) NOT NULL DEFAULT '' COMMENT '手机号码',
  `code` smallint(5) DEFAULT '0' COMMENT '验证码',
  `send_time` int(10) NOT NULL DEFAULT '0' COMMENT '发送时间',
  `expire_time` int(10) NOT NULL DEFAULT '0' COMMENT '过期时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for jd_oauth
-- ----------------------------
DROP TABLE IF EXISTS `jd_oauth`;
CREATE TABLE `jd_oauth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_key` varchar(32) DEFAULT NULL,
  `app_secret` varchar(64) DEFAULT NULL COMMENT '加密盐',
  `signature` varchar(64) DEFAULT NULL COMMENT '加密串',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for jd_order
-- ----------------------------
DROP TABLE IF EXISTS `jd_order`;
CREATE TABLE `jd_order` (
  `order_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_num` varchar(50) DEFAULT '' COMMENT '唯一流水号',
  `channel` tinyint(3) unsigned DEFAULT '1' COMMENT '渠道：1，公众号；2，APP',
  `uid` bigint(20) DEFAULT '0' COMMENT '用户ID',
  `before_voucher` int(11) DEFAULT '0' COMMENT '之前礼券',
  `voucher` int(11) DEFAULT '0' COMMENT '兑换礼券',
  `after_voucher` int(11) DEFAULT '0' COMMENT '当前礼券',
  `province` int(10) unsigned DEFAULT '0' COMMENT '省ID',
  `city` int(10) unsigned DEFAULT '0' COMMENT '市ID',
  `county` int(11) DEFAULT '0',
  `town` int(11) DEFAULT '0',
  `address` varchar(255) DEFAULT '' COMMENT '详细地址',
  `username` varchar(100) DEFAULT '' COMMENT '收货人',
  `tel` varchar(12) DEFAULT '' COMMENT '收货人手机',
  `status` tinyint(4) DEFAULT '0' COMMENT '1,已收货；0，未收货2;是拒收',
  `jd_order_id` varchar(80) DEFAULT '' COMMENT '京东订单号',
  `type` int(1) DEFAULT '0' COMMENT '0是京东商品记录，1是欧飞商品记录',
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_num` (`order_num`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=391 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for jd_order_gift
-- ----------------------------
DROP TABLE IF EXISTS `jd_order_gift`;
CREATE TABLE `jd_order_gift` (
  `order_gift_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) NOT NULL COMMENT '订单ID',
  `gift_id` bigint(20) NOT NULL COMMENT '礼品ID',
  `gift_sku` bigint(20) DEFAULT NULL COMMENT '京东商品ID',
  `gift_name` varchar(255) NOT NULL COMMENT '礼品名',
  `gift_num` int(11) NOT NULL COMMENT '数量',
  `gift_voucher` int(11) NOT NULL COMMENT '礼券',
  `gift_params` varchar(255) DEFAULT '' COMMENT '参数',
  `gift_img_url` varchar(255) DEFAULT '' COMMENT '图片',
  `gift_desc` varchar(100) DEFAULT '' COMMENT '产品描述',
  `gift_msg` varchar(100) DEFAULT '' COMMENT '第三方返回的操作信息',
  PRIMARY KEY (`order_gift_id`)
) ENGINE=InnoDB AUTO_INCREMENT=386 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for jd_product
-- ----------------------------
DROP TABLE IF EXISTS `jd_product`;
CREATE TABLE `jd_product` (
  `sku` bigint(11) NOT NULL COMMENT '商品号',
  `name` varchar(255) NOT NULL COMMENT '商品名称',
  `brandName` varchar(255) DEFAULT '' COMMENT '品牌名',
  `category` varchar(255) NOT NULL COMMENT '种类，1,2,3级以分号隔开',
  `saleUnit` char(20) DEFAULT '' COMMENT '单位',
  `weight` double unsigned DEFAULT NULL COMMENT '重量',
  `productArea` char(255) DEFAULT '' COMMENT '产地',
  `wareQD` varchar(255) DEFAULT '' COMMENT '规格与包装',
  `imagePath` varchar(255) DEFAULT '' COMMENT '图片地址',
  `param` text COMMENT '产品参数',
  `state` tinyint(4) DEFAULT NULL COMMENT '状态，0.下架，1.上架',
  `upc` varchar(255) DEFAULT '',
  `introduction` text COMMENT '产品介绍',
  `dc_state` tinyint(3) unsigned DEFAULT '1' COMMENT '默认1为可选，0为不可选',
  PRIMARY KEY (`sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for jd_product_bak
-- ----------------------------
DROP TABLE IF EXISTS `jd_product_bak`;
CREATE TABLE `jd_product_bak` (
  `sku` bigint(11) NOT NULL COMMENT '商品号',
  `name` varchar(255) NOT NULL COMMENT '商品名称',
  `brandName` varchar(255) DEFAULT '' COMMENT '品牌名',
  `category` varchar(255) NOT NULL COMMENT '种类，1,2,3级以分号隔开',
  `saleUnit` char(20) DEFAULT '' COMMENT '单位',
  `weight` double unsigned DEFAULT NULL COMMENT '重量',
  `productArea` char(255) DEFAULT '' COMMENT '产地',
  `wareQD` varchar(255) DEFAULT '' COMMENT '规格与包装',
  `imagePath` varchar(255) DEFAULT '' COMMENT '图片地址',
  `param` text COMMENT '产品参数',
  `state` tinyint(4) DEFAULT NULL COMMENT '状态，0.下架，1.上架',
  `upc` varchar(255) DEFAULT '',
  `introduction` text COMMENT '产品介绍',
  `dc_state` tinyint(3) unsigned DEFAULT '1' COMMENT '默认1为可选，0为不可选',
  PRIMARY KEY (`sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for jd_product_log
-- ----------------------------
DROP TABLE IF EXISTS `jd_product_log`;
CREATE TABLE `jd_product_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '对应playerinfo表中的uid',
  `product_id` int(11) DEFAULT NULL COMMENT '对应dc_product中的id',
  `name` varchar(30) DEFAULT NULL COMMENT '姓名',
  `mobile` varchar(30) DEFAULT NULL COMMENT '电话',
  `num` int(11) DEFAULT NULL COMMENT '兑换商品的数量',
  `redeem_voucher` int(11) DEFAULT NULL COMMENT '兑换花费的礼券数目',
  `redeem_time` int(11) DEFAULT NULL COMMENT '兑换时间',
  `status` int(11) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for jd_tag
-- ----------------------------
DROP TABLE IF EXISTS `jd_tag`;
CREATE TABLE `jd_tag` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tag_name` varchar(10) NOT NULL DEFAULT '' COMMENT '标签名称',
  `tag_image` varchar(300) DEFAULT '' COMMENT '商品分类背景图',
  `cid` int(10) unsigned NOT NULL COMMENT '分类',
  `order_id` int(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for jd_user_address
-- ----------------------------
DROP TABLE IF EXISTS `jd_user_address`;
CREATE TABLE `jd_user_address` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `username` varchar(50) NOT NULL DEFAULT '收货人',
  `province` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '省',
  `city` int(10) unsigned DEFAULT '0' COMMENT '市',
  `county` int(10) unsigned DEFAULT '0' COMMENT '区',
  `town` int(10) unsigned DEFAULT '0' COMMENT '镇',
  `address` varchar(255) NOT NULL,
  `tel` varchar(12) NOT NULL COMMENT '手机',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for of_gift
-- ----------------------------
DROP TABLE IF EXISTS `of_gift`;
CREATE TABLE `of_gift` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '' COMMENT '名字',
  `img_url` varchar(255) DEFAULT '' COMMENT '图片',
  `params` varchar(255) DEFAULT '' COMMENT '参数',
  `price` int(11) DEFAULT '0' COMMENT '面值(请按照流量产品文档中对应商品输入)',
  `value` varchar(100) DEFAULT '' COMMENT '流量值或话费值',
  `desc` varchar(100) DEFAULT '' COMMENT '产品描述',
  `voucher` int(11) DEFAULT NULL COMMENT '兑换该商品所需的礼券',
  `order_id` int(11) unsigned DEFAULT '0' COMMENT '礼品来进行排序，ID越小排越前',
  `status` int(1) DEFAULT '0' COMMENT '状态',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `cid` int(11) DEFAULT '0' COMMENT '所属分类的id',
  `tagid` smallint(5) unsigned DEFAULT '0' COMMENT '标签',
  `channel` varchar(4) DEFAULT '' COMMENT '默认0:全部，1：APP，2：微信公众号',
  `sku` bigint(20) DEFAULT NULL COMMENT '京东商品号',
  `is_jd` tinyint(3) unsigned DEFAULT '1' COMMENT '1是京东，2是话费，3是流量',
  `cardid` varchar(60) DEFAULT '0' COMMENT '欧飞提供的卡ID',
  PRIMARY KEY (`id`),
  KEY `sku` (`sku`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=188 DEFAULT CHARSET=utf8 COMMENT='礼品表';

-- ----------------------------
-- View structure for dc_view_club_game
-- ----------------------------
DROP VIEW IF EXISTS `dc_view_club_game`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `dc_view_club_game` AS select `dc_club_game`.`id` AS `id`,`dc_club_game`.`club_id` AS `club_id`,`dc_club_game`.`game_id` AS `game_id`,`dc_game_info`.`game_name` AS `game_name` from (`dc_club_game` join `dc_game_info`) where (`dc_club_game`.`game_id` = `dc_game_info`.`game_id`) ;

-- ----------------------------
-- View structure for dc_view_club_player
-- ----------------------------
DROP VIEW IF EXISTS `dc_view_club_player`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `dc_view_club_player` AS select `dc_club_info`.`club_id` AS `club_id`,`dc_club_info`.`club_name` AS `club_name`,`dc_club_info`.`club_status` AS `club_status`,`dc_club_info`.`club_time` AS `club_time`,`dc_club_player`.`player_id` AS `player_id`,`dc_club_player`.`player_tokens` AS `player_tokens` from (`dc_club_info` join `dc_club_player`) where (`dc_club_info`.`club_id` = `dc_club_player`.`club_id`) ;

-- ----------------------------
-- View structure for dc_view_club_room
-- ----------------------------
DROP VIEW IF EXISTS `dc_view_club_room`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `dc_view_club_room` AS select `dc_club_room`.`club_room_id` AS `club_room_id`,`dc_club_room`.`club_room_club_id` AS `club_room_club_id`,`dc_club_room`.`club_room_game_id` AS `club_room_game_id`,`dc_club_room`.`club_room_desk_count` AS `club_room_desk_count`,`dc_game_info`.`game_desk_members_count` AS `club_room_desk_members_count`,`dc_club_room`.`club_room_name` AS `club_room_name`,`dc_club_room`.`club_room_is_open` AS `club_room_is_open`,`dc_club_room`.`club_room_is_work` AS `club_room_is_work`,`dc_club_room`.`club_room_type` AS `club_room_type`,`dc_club_room`.`club_room_level` AS `club_room_level`,`dc_club_room`.`club_room_basic_points` AS `club_room_basic_points`,`dc_club_room`.`club_room_min_coin` AS `club_room_min_coin`,`dc_club_room`.`club_room_max_coin` AS `club_room_max_coin`,`dc_club_room`.`club_room_desk_param` AS `club_room_desk_param`,`dc_club_room`.`club_room_rule_id` AS `club_room_rule_id` from (`dc_club_room` join `dc_game_info`) where (`dc_game_info`.`game_id` = `dc_club_room`.`club_room_game_id`) ;

-- ----------------------------
-- View structure for dc_view_player_info
-- ----------------------------
DROP VIEW IF EXISTS `dc_view_player_info`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `dc_view_player_info` AS select `dc_player`.`player_id` AS `player_id`,`dc_player`.`player_name` AS `player_name`,`dc_player`.`player_nickname` AS `player_nickname`,`dc_player`.`player_password` AS `player_password`,`dc_player`.`player_phone` AS `player_phone`,`dc_player`.`player_pcid` AS `player_pcid`,`dc_player`.`player_status` AS `player_status`,`dc_player`.`player_vip_level` AS `player_vip_level`,`dc_player`.`player_resigter_time` AS `player_resigter_time`,`dc_player`.`player_robot` AS `player_robot`,`dc_player`.`player_guest` AS `player_guest`,`dc_player`.`player_icon_id` AS `player_icon_id`,`dc_player_info`.`player_money` AS `player_money`,`dc_player_info`.`player_coins` AS `player_coins`,`dc_player_info`.`player_safe_box` AS `player_safe_box`,`dc_player_info`.`player_safe_box_password` AS `player_safe_box_password`,`dc_player_info`.`player_lottery` AS `player_lottery`,`dc_player_info`.`player_club_id` AS `player_club_id`,`dc_player_info`.`player_header_image` AS `player_header_image`,`dc_player_info`.`player_sex` AS `player_sex`,`dc_player_info`.`player_signature` AS `player_signature`,`dc_player_info`.`player_author` AS `player_author` from (`dc_player` join `dc_player_info`) where (`dc_player`.`player_id` = `dc_player_info`.`player_id`) ;

-- ----------------------------
-- View structure for dc_view_treasure_info
-- ----------------------------
DROP VIEW IF EXISTS `dc_view_treasure_info`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `dc_view_treasure_info` AS select `dc_treasure_period`.`id` AS `id`,`dc_treasure_period`.`treasure_period_no` AS `treasure_period_no`,`dc_treasure_period`.`treasure_type` AS `treasure_type`,`dc_treasure_period`.`treasure_station` AS `treasure_station`,`dc_treasure_period`.`treasure_count` AS `treasure_count`,`dc_treasure_period`.`treasure_begin_time` AS `treasure_begin_time`,`dc_treasure_period`.`treasure_status` AS `treasure_status`,`dc_treasure_config`.`treasure_singe_amount` AS `treasure_singe_amount`,`dc_treasure_config`.`note_max_count` AS `note_max_count`,`dc_treasure_config`.`treasure_prize_type` AS `treasure_prize_type`,`dc_treasure_config`.`treasure_prize_amount` AS `treasure_prize_amount`,`dc_treasure_config`.`treasure_note_type` AS `treasure_note_type`,`dc_treasure_config`.`user_note_max_count` AS `user_note_max_count`,`dc_treasure_config`.`treasure_explation` AS `treasure_explation`,`dc_treasure_config`.`treasure_sort_type` AS `treasure_sort_type`,`dc_treasure_config`.`treasure_show_type` AS `treasure_show_type` from (`dc_treasure_period` join `dc_treasure_config`) where (`dc_treasure_period`.`treasure_type` = `dc_treasure_config`.`treasure_type`) ;

-- ----------------------------
-- View structure for dc_view_treasure_prize_info
-- ----------------------------
DROP VIEW IF EXISTS `dc_view_treasure_prize_info`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `dc_view_treasure_prize_info` AS select `dc_treasure_prize`.`id` AS `id`,`dc_treasure_prize`.`treasure_user_id` AS `treasure_user_id`,`dc_treasure_prize`.`treasure_period_no` AS `treasure_period_no`,`dc_treasure_prize`.`treasure_type` AS `treasure_type`,`dc_treasure_prize`.`treasure_prize_time` AS `treasure_prize_time`,`dc_treasure_prize`.`treasure_num_count` AS `treasure_num_count`,`dc_treasure_prize`.`treasure_get_prize` AS `treasure_get_prize`,`dc_treasure_prize`.`treasure_user_totle_count` AS `treasure_user_totle_count`,`dc_treasure_config`.`treasure_sort_type` AS `treasure_sort_type`,`dc_treasure_config`.`treasure_singe_amount` AS `treasure_singe_amount`,`dc_treasure_config`.`note_max_count` AS `note_max_count`,`dc_treasure_config`.`treasure_prize_type` AS `treasure_prize_type`,`dc_treasure_config`.`treasure_prize_amount` AS `treasure_prize_amount`,`dc_treasure_config`.`treasure_note_type` AS `treasure_note_type`,`dc_treasure_config`.`user_note_max_count` AS `user_note_max_count`,`dc_treasure_config`.`treasure_show_type` AS `treasure_show_type`,`dc_treasure_config`.`treasure_explation` AS `treasure_explation`,`dc_treasure_prize`.`treasure_user_look` AS `treasure_user_look`,`dc_treasure_prize`.`treasure_prize_user_id` AS `treasure_prize_user_id` from (`dc_treasure_prize` join `dc_treasure_config`) where (`dc_treasure_prize`.`treasure_type` = `dc_treasure_config`.`treasure_type`) ;

-- ----------------------------
-- Procedure structure for activeuser
-- ----------------------------
DROP PROCEDURE IF EXISTS `activeuser`;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `activeuser`()
BEGIN
	set @temptime = UNIX_TIMESTAMP('2018-6-30');
  set @iret = 0;
  while @iret <24 do
		select DISTINCT change_money_player_id,change_money_time from dc_change_money_info_record where change_money_game_id = 10000003 and change_money_time > @temptime and change_money_time < @temptime + 86400;
	set @iret = @iret + 1;
	set @temptime = @temptime + 86400;
	end while;
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for init_data
-- ----------------------------
DROP PROCEDURE IF EXISTS `init_data`;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `init_data`()
BEGIN
	DECLARE _playerid int(11) default 0;
	DECLARE _action_user varchar(350) default '';
	DECLARE	_user_id int(11) default 0;
	DECLARE	_id int(11) default 0;
	DECLARE _channel_id int(11) default 0;
	DECLARE _done int default 0;

	DECLARE _cursor cursor for select id,player_id,action_user from dc_sys_coin_change_log;
			 
	DECLARE continue handler for not found set _done=1;

	open _cursor;

	myLoop: LOOP

	fetch _cursor into _id,_playerid,_action_user;  
		if _done = 1 then   
		leave myLoop;  
		end if;  

		update dc_sys_coin_change_log set action_user_id = (select id from dc_users where user_login = _action_user limit 1) where id = _id;
		update dc_sys_coin_change_log set channel_id = (select agent_top_agentid from dc_agent_info where agent_player_id = _playerid limit 1) where id = _id;
		
	end loop myLoop;
		
	close _cursor;

END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for init_log
-- ----------------------------
DROP PROCEDURE IF EXISTS `init_log`;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `init_log`()
BEGIN
	DECLARE _playerid int(11) default 0;
	DECLARE _action_user varchar(350) default '';
	DECLARE	_user_id int(11) default 0;
	DECLARE	_id int(11) default 0;
	DECLARE _channel_id int(11) default 0;
	DECLARE _done int default 0;

	DECLARE _cursor cursor for select id,player_id,action_user from dc_sys_coin_change_log;
			 
	DECLARE continue handler for not found set _done=1;

	open _cursor;

	myLoop: LOOP

	fetch _cursor into _id,_playerid,_action_user;  
		if _done = 1 then   
		leave myLoop;  
		end if;  

		update dc_sys_coin_change_log set action_user_id = (select id from dc_users where user_login = _action_user limit 1) where id = _id;
		update dc_sys_coin_change_log set channel_id = (select agent_top_agentid from dc_agent_info where agent_player_id = _playerid limit 1) where id = _id;
		
	end loop myLoop;
		
	close _cursor;

END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for _Navicat_Temp_Stored_Proc
-- ----------------------------
DROP PROCEDURE IF EXISTS `_Navicat_Temp_Stored_Proc`;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `_Navicat_Temp_Stored_Proc`()
set @temptime = UNIX_TIMESTAMP('2018-5-1');
;;
DELIMITER ;

-- ----------------------------
-- Function structure for urldecode
-- ----------------------------
DROP FUNCTION IF EXISTS `urldecode`;
DELIMITER ;;
CREATE DEFINER=`root`@`%` FUNCTION `urldecode`(s VARCHAR(4096)) RETURNS varchar(4096) CHARSET utf8 COLLATE utf8_unicode_ci
    DETERMINISTIC
BEGIN
       DECLARE c VARCHAR(4096) DEFAULT '';
       DECLARE pointer INT DEFAULT 1;
       DECLARE h CHAR(2);
       DECLARE h1 CHAR(1);
       DECLARE h2 CHAR(1);
       DECLARE s2 VARCHAR(4096) DEFAULT '';


       IF ISNULL(s) THEN
          RETURN NULL;
       ELSE
       SET s2 = '';
       WHILE pointer <= LENGTH(s) DO
          SET c = MID(s,pointer,1);
          IF c = '+' THEN
             SET c = ' ';
          ELSEIF c = '%' AND pointer + 2 <= LENGTH(s) THEN
             SET h1 = LOWER(MID(s,pointer+1,1));
             SET h2 = LOWER(MID(s,pointer+2,1));
             IF (h1 BETWEEN '0' AND '9' OR h1 BETWEEN 'a' AND 'f')
                 AND
                 (h2 BETWEEN '0' AND '9' OR h2 BETWEEN 'a' AND 'f') 
                 THEN
                   SET h = CONCAT(h1,h2);
                   SET pointer = pointer + 2;
                   SET c = CHAR(CONV(h,16,10));
              END IF;
          END IF;
          SET s2 = CONCAT(s2,c);
          SET pointer = pointer + 1;
       END while;
       END IF;
       RETURN s2;
END
;;
DELIMITER ;

-- ----------------------------
-- Function structure for url_decode
-- ----------------------------
DROP FUNCTION IF EXISTS `url_decode`;
DELIMITER ;;
CREATE DEFINER=`root`@`%` FUNCTION `url_decode`(original_text TEXT) RETURNS text CHARSET utf8
BEGIN  
    DECLARE new_text TEXT DEFAULT NULL;  
    DECLARE pointer INT DEFAULT 1;  
    DECLARE end_pointer INT DEFAULT 1;  
    DECLARE encoded_text TEXT DEFAULT NULL;  
    DECLARE result_text TEXT DEFAULT NULL;  
   
    SET new_text = REPLACE(original_text,'+',' ');  
    SET new_text = REPLACE(new_text,'%0A','\r\n');  
   
    SET pointer = LOCATE("%", new_text);  
    while pointer <> 0 && pointer < (CHAR_LENGTH(new_text) - 2) DO  
        SET end_pointer = pointer + 3;  
        while MID(new_text, end_pointer, 1) = "%" DO  
            SET end_pointer = end_pointer+3;  
        END while;  
   
        SET encoded_text = MID(new_text, pointer, end_pointer - pointer);  
        SET result_text = CONVERT(UNHEX(REPLACE(encoded_text, "%", "")) USING utf8);  
        SET new_text = REPLACE(new_text, encoded_text, result_text);  
        SET pointer = LOCATE("%", new_text, pointer + CHAR_LENGTH(result_text));  
    END while;  
   
    return new_text;  
  
END
;;
DELIMITER ;
