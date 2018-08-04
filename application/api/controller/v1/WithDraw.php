<?php
/**
 * +----------------------------------------------------------
 * date: 2018-03-13 16:16:15
 * +----------------------------------------------------------
 * author: Raoxiaoya
 * +----------------------------------------------------------
 * describe: 提现相关
 * +----------------------------------------------------------
 */

namespace app\api\controller\v1;

use app\api\controller\Controller;
use app\api\model\AgentAccountInfoModel;
use app\api\model\AgentInfoModel;
use app\api\model\ConfigModel;
use app\api\model\WithDrawConfigModel;
use app\api\model\PlayerModel;
use app\api\model\WithdrawLogModel;
use app\api\model\AgentAccountInfoLogModel;
use app\api\model\WxBonusLogModel;
use app\api\block\BonusBlock;
use app\common\components\Helper;
use app\api\redis\MobileCodeRedis;
use function GuzzleHttp\Psr7\str;
use think\cache\driver\Redis;
use think\Db;
use think\Log;

class Withdraw extends Controller
{

    protected $bonusMax = 200;// 微信红包最大200元

    /**
     * 提现申请
     */
    public function index()
    {
        $withdraw_money = input('withdraw_money');
        $code = input('code');
        $mobile = input('mobile');
        $payment_password = input('payment_password');

        $dataInfo = $this->isLogin($this->token);
        if (empty($dataInfo)) {
            return $this->sendError(10000, '请从新登陆！。');
        }

        if ($withdraw_money <= 0) {
            return $this->sendError('10000', '提现金额不能小于0 !');
        }
        if (empty($code)) {
            return $this->sendError('10000', '请输入验证码！');
        }
        if (empty($payment_password)) {
            return $this->sendError('10000', '支付密码不能为空！');
        }
        $agentAccountInfo = AgentAccountInfoModel::model()->getAccountInfoByAgentId($dataInfo['agent_info']['agent_id']);
        if ($agentAccountInfo['agent_account_payment_password'] != md5($payment_password)) {
            return $this->sendError(10000, '支付密码错误！。');
        }
        //todo 验证手机验证码的问题。
        $key = 'withdraw' . $mobile;
        $codeInfo = MobileCodeRedis::get($key);
        if ($code != $codeInfo['code']) {
            return $this->sendError('10000', '手机验证码错误。');
        }
        MobileCodeRedis::delete($key);
        if ($mobile !== $agentAccountInfo['agent_account_mobile']) {
            return $this->sendError('10000', '与预留的手机号码不一致！。');
        }
        //todo 检查账户余额 与提现金额的大小比较。
        $agentInfo = AgentAccountInfoModel::model()->getAccountInfoByAgentId($dataInfo['agent_info']['agent_id']);
        if ($agentInfo['agent_account_money'] < $withdraw_money) {
            return $this->sendError(10000, '提现金额不能大于个人账号余额。');
        }
        $withDrawModel = new WithdrawLogModel();
        $agent_id = $dataInfo['agent_info']['agent_id'];
        $condition = array(
            'agent_id' => $agent_id,
            'start' => strtotime(date('Y-m-d 00:00:00')),
            'end' => strtotime(date('Y-m-d 23:59:59'))
        );
        //todo 提现申请判断 一天只能提现一次。
        $count = $withDrawModel->getWithDrawNums($condition);
        //加载提现限制
        $withdraw_config = WithDrawConfigModel::model()->getFindOne();

        if ($count >= $withdraw_config['withdraw_nums']) {
            return $this->sendError(10000, '今日提现已经上限');
        }

        //加载固定的手续费
        $config_info = ConfigModel::model()->getInfo(array('config_type' => 1));
        $config_money = json_decode($config_info['config_config'], true);

        //限制不超出三块 按三块收
        if ($withdraw_config['withdraw_poundage'] / 100 <= $config_money['money']) {
            $withdraw_poundage = $config_money['money'];
        } else {
            $withdraw_poundage = $withdraw_config['withdraw_poundage'] / 100;
        }
        //加载特代信息
        $agent_info = AgentInfoModel::model()->getInfo(array('agent_id' => $dataInfo['agent_info']['agent_id']));
        $top_agent = AgentInfoModel::model()->getInfo(array('agent_id' => $agent_info['agent_top_agentid']));

        $income_money = $withdraw_money - $withdraw_poundage;
        $withdraw_after_money = $agentInfo['agent_account_money'] - $withdraw_money;
        $data = array(
            'withdraw_player_id' => $dataInfo['agent_info']['agent_player_id'],
            'withdraw_agent_id' => $agent_id,
            'withdraw_agent_name' => $dataInfo['agent_info']['agent_name'],
            'withdraw_parent_id' => $agent_info['agent_top_agentid'],
            'withdraw_parent_name' => $top_agent['agent_name'],
            'withdraw_account_money' => $agentInfo['agent_account_money'],
            'withdraw_money' => $withdraw_money,
            'withdraw_after_money' => $withdraw_after_money,
            'withdraw_poundage' => $withdraw_poundage,
            'withdraw_income_money' => $income_money,
            'withdraw_status' => 0,
            'withdraw_time' => time()
        );

        Db::startTrans();
        $status = $withDrawModel->insert($data);
        if (!$status) {
            Db::rollback();
            return $this->sendError(10000, '申请提现失败!');
        }

        $data = array(
            'agent_account_money' => $withdraw_after_money
        );
        $agentStatus = AgentAccountInfoModel::model()->updateAgentAccountInfo($agent_id, $data);
        if (!$agentStatus) {
            Db::rollback();
            return $this->sendError(10000, '申请提现失败!');
        }
        Db::commit();

        return $this->sendSuccess();

    }

    /**
     *  显示代理的money
     */
    public function agentMoney()
    {

        $agnetInfo = $this->isLogin($this->token);
        $money = $this->request->get('money');
        //记载提现限制次数
        $withdraw_config = WithDrawConfigModel::model()->getFindOne();

        $agentAccountInfo = AgentAccountInfoModel::model()->getAccountInfoByAgentId($agnetInfo['agent_info']['agent_id']);

        if ($money) {
            $agent_account_money = $agentAccountInfo['agent_account_money'] - $money;
        } else {
            $agent_account_money = $agentAccountInfo['agent_account_money'];
        }
        $data = array(
            'agent_id' => $agnetInfo['agent_info']['agent_id'],
            'agent_account_money' => $agent_account_money,
            'number' => $withdraw_config['withdraw_nums'],
        );
        return $this->sendSuccess($data);

    }

    /**
     * 时间条件的参数
     * @return array
     */
    public function initRequestParam()
    {
        $time = time();
        $timestamp = strtotime(date('Y-m-d', $time));
        $input = array(
            'start' => input('start'),
            'end' => input('end'),
            // 'withdraw_money' => input('withdraw_money'),// 提现金额
            'range' => (int)input('range'),// 1-昨天，2-本周，3-本月
            'page' => (int)input('page'),
            'type' => (int)input('type'),
        );

        // 时间
        $filter = array();
        if ($input['range'] && $input['type'] === 1) {
            switch ($input['range']) {
                case 1:
                    $filter['end'] = $timestamp;
                    $filter['start'] = $filter['end'] - 86400;
                    break;
                case 2:
                    $weekday = date('w', $time) > 0 ? date('w', $time) : 7;//0-日，1-一
                    $filter['end'] = $timestamp + 86400;
                    $filter['start'] = $filter['end'] - 86400 * ($weekday);
                    break;
                case 3:
                    $day = date('j', $time);
                    $filter['end'] = $timestamp + 86400;
                    $filter['start'] = $filter['end'] - 86400 * ($day);
                    break;
                default:
                    $this->returnCode(10001, '请选择合适的时间区间，只能查看今天之前的记录');
                    break;
            }
        } else {
            $start = strtotime($input['start']);
            $end = strtotime($input['end']);
            if ($start && $end && $start <= $end) {
                $start = strtotime(date('Y-m-d', $start));
                $end = strtotime(date('Y-m-d', $end));
                if ($start > $timestamp) {
                    $this->returnCode(10001, '请选择合适的时间区间，只能查看今天之前的记录');
                    return false;
                }
                $filter['start'] = $start;
                if ($end > $timestamp) {
                    $filter['end'] = $timestamp + 86400;
                } elseif ($end == $timestamp) {
                    $filter['end'] = $end + 86400;
                } elseif ($end < $timestamp) {
                    $filter['end'] = $end + 86400;
                }
            } elseif (!$start && !$end) {
                $filter['start'] = $timestamp;
                $filter['end'] = $filter['start'] + 86400;
            } else {
                $this->returnCode(10002, '请选择合适的时间区间');
                return false;
            }
        }
        // if ($input['withdraw_money'] !== '') {
        //     $filter['withdraw_money'] = $input['withdraw_money'];
        // }

        if ($input['page']) {
            $filter['page'] = $input['page'];
        } else {
            $filter['page'] = 1;
        }

        $filter['error_code'] = 0;
        return $filter;

    }

    /**
     * 获取提现记录列表
     */
    public function getList()
    {
        $dataInfo = $this->isLogin($this->token);

        if (empty($dataInfo)) {
            return $this->sendError(10000, '用户登陆的token失效。');
        }
        $condition = $this->initRequestParam();
        // print_r($condition); 
        if ($condition['error_code'] == 1) {
            return $this->sendError(10001, '请选择合适的时间区间，只能查看今天之前的记录');
        }

        if ($condition['error_code'] == 2) {
            return $this->sendError(10001, '请选择合适的时间区间，只能查看今天之前的记录');
        }

        if ($condition['error_code'] == 3) {
            return $this->sendError(10002, '请选择合适的时间区间');
        }

        if (empty($condition['start'])) {
            return $this->sendError(10002, '请选择合适的时间区间');
        }

        if (empty($condition['end'])) {
            return $this->sendError(10002, '请选择合适的时间区间');
        }

        $condition['agent_id'] = $dataInfo['agent_info']['agent_id'];
        $withDrawModel = new WithdrawLogModel();
        $list = $withDrawModel->getList($condition);
        if ($list) {
            foreach ($list as $key => $value) {
                if ($value['withdraw_method'] == 1) {
                    $wxloginfo = WxBonusLogModel::model()->getfind(array('id' => $value['withdraw_method_log_id']));
                    $list[$key]['withdraw_status_show'] = $this->wxStatusShow($wxloginfo['status']);
                    $list[$key]['withdraw_status'] = $wxloginfo['status'];
                } else if ($value['withdraw_method'] == 2) {
                    $list[$key]['withdraw_status_show'] = $this->zfStatusShow($value['withdraw_status']);
                    $list[$key]['withdraw_status'] = $value['withdraw_status'];
                }
                switch ($value['withdraw_method']) {
                    case 1;
                        $list[$key]['withdraw_type_show'] = '微信红包';
                        break;
                    case  2;
                        $list[$key]['withdraw_type_show'] = '支付宝';
                        break;
                    default;
                        $list[$key]['withdraw_type_show'] = '未知';
                }
                $list[$key]['withdraw_before_money'] = $value['withdraw_before_money'] / 100;
                $list[$key]['withdraw_money'] = $value['withdraw_money'] / 100;
                $list[$key]['withdraw_after_money'] = $value['withdraw_after_money'] / 100;

            }
        }
        $withDrawTotalMoney = $withDrawModel->getWithdrawToalMoney($condition);

        if (empty($withDrawTotalMoney['total'])) {
            $withDrawTotalMoney['total'] = 0.00;
        } else {
            $withDrawTotalMoney['total'] = $withDrawTotalMoney['total'] / 100;
        }

        $data = array(
            'list' => $list,
            'withdraw_total_money' => $withDrawTotalMoney,
            'date' => array(
                'start' => date('Y/m/d', $condition['start']),
                'end' => date('Y/m/d', $condition['end'] - 86400)
            ),
        );
        return $this->sendSuccess($data);
    }


    /* ------------------------------------------------ 微信提现 ------------------------------------------------ */

    /**
     * 提示用户关注公众号并绑定，若已绑定则不提示
     * @return [type] [description]
     */
    public function getNotice()
    {
        $info = $this->isLogin($this->token);
        if (empty($info)) {
            return $this->sendError(10000, '请重新登陆！。');
        }

        $player_id = $info['user_info']['id'];
        $playerInfo = PlayerModel::model()->getPlayerinfo($player_id);
        if (!$playerInfo) {
            return $this->sendError(10001, '该玩家不存在');
        }
        if ($playerInfo['player_openid_gzh'] == '') {
            return $this->sendError(10001, '请先在公众号进行绑定');
        } else {
            return $this->sendSuccess();
        }
    }

    /**
     * 用户账户余额，剩余提现次数，提现配置等信息
     * @return [type] [description]
     */
    public function getInfo()
    {
        $info = $this->isLogin($this->token);
        if (empty($info)) {
            return $this->sendError(10000, '请重新登陆！。');
        }
        $player_id = $info['user_info']['id'];
        $agent_id = $info['agent_info']['agent_id'];
        $timestamp = strtotime(date('Y-m-d'));
        // 账户信息
        $agentAccountInfo = AgentAccountInfoModel::model()->getAccountInfoByAgentId($agent_id);
        if (!$agentAccountInfo) {
            return $this->sendError(10001, '账户信息不存在，请联系客服');
        }
        // 星级推广员提现配置
        $config = ConfigModel::model()->getInfo(['config_name' => 'withdraw_config_star', 'config_status' => 1]);
        if (!$config) {
            return $this->sendError(10001, '暂未开放提现功能，请联系客服');
        }
        $config = json_decode($config['config_config'], true);
        $config['auto_money'] = $config['auto_money'] / 100;
        $config['limit_max'] = $config['limit_max'] / 100;
        $config['limit_min'] = $config['limit_min'] / 100;
        // 今日已提现次数
        $todayCount = WithdrawLogModel::model()->getWithDrawNums(['agent_id' => $agent_id, 'start' => $timestamp, 'end' => $timestamp + 86400]);
        $user = [
            'account_money' => Helper::cutDataByLen($agentAccountInfo['agent_account_money'] / 100),
            'today_num' => $todayCount,
            'status' => 1,
            'last_num' => ($config['limit_num'] - $todayCount) > 0 ? ($config['limit_num'] - $todayCount) : 0
        ];
        if ($todayCount >= $config['limit_num']) {
            $user['status'] = 0;
        }
        if ($agentAccountInfo['agent_account_money'] <= 0) {
            $user['status'] = 0;
        }
        // 玩家状态
        $playerInfo = PlayerModel::model()->getPlayerinfo($player_id);
        if (!$playerInfo) {
            $user['status'] = 0;
        }
        if ($playerInfo['player_openid_gzh'] == '') {
            $user['status'] = 0;
        }
        if ($playerInfo['player_status'] !== 1) {
            $user['status'] = 0;
        }

        return $this->sendSuccess(['config' => $config, 'user' => $user]);
    }

    /**
     * 发起提现申请
     * @return [type] [description]
     */
    public function unifiedOrder()
    {
        $info = $this->isLogin($this->token);
        // http://kingdom.dcgames.cn/dc_api_u3d/public/api/v1/withdraw/unified_order
        // $info = $this->isLogin('0f9454c9eccf54f4d01cf3c3088742ed');
        if (empty($info)) {
            return $this->sendError(10000, '请重新登陆！。');
        }
        if ($info['login_from'] != 1) {
            return $this->sendError(10000, '请从微信登录！');
        }
        $player_id = $info['user_info']['id'];
        $agent_id = $info['agent_info']['agent_id'];
        $timestamp = strtotime(date('Y-m-d'));

        $money = (int)$this->request->post('money');
        // $money = 1;
        if (!is_int($money) || $money <= 0) {
            return $this->sendError(10001, '参数错误');
        } else {
            $money = $money * 100; // 分
        }

        // 是否超过配置
        $config = ConfigModel::model()->getInfo(['config_name' => 'withdraw_config_star', 'config_status' => 1]);
        if (!$config) {
            return $this->sendError(10001, '暂未开放提现功能，请联系客服');
        } else {
            $config = json_decode($config['config_config'], true);
            if ($money < $config['limit_min']) {
                return $this->sendError(10001, "请输入金额大于等于" . ($config['limit_min'] / 100));
            }
            if ($money > $config['limit_max']) {
                return $this->sendError(10001, "请输入金额小于等于" . ($config['limit_max'] / 100));
            }
        }

        // 玩家状态
        $playerInfo = PlayerModel::model()->getPlayerinfo($player_id);
        if (!$playerInfo) {
            return $this->sendError(10001, '该玩家不存在');
        }
        if ($playerInfo['player_openid_gzh'] == '') {
            return $this->sendError(10001, '请先在公众号进行绑定');
        }
        if ($playerInfo['player_status'] !== 1) {
            return $this->sendError(10001, '您已被禁用');
        }

        // 是否超额
        Db::startTrans();
        $agentAccountInfo = AgentAccountInfoModel::model()->getAccountInfoByAgentIdLock($agent_id);
        if (!$agentAccountInfo) {
            Db::commit();
            return $this->sendError(10001, '账户信息不存在，请联系客服');
        } else {
            if ($money > $agentAccountInfo['agent_account_money']) {
                Db::commit();
                return $this->sendError(10001, '提现金额超出账户余额');
            }
        }
        // 账户减
        $re = AgentAccountInfoModel::model()->updateMoney($agent_id, $money, 'dec');
        if ($re) {
            Db::commit();
        } else {
            Db::rollback();
            Log::error('[ withdraw ] : 账户余额减操作失败 | agent_id = ' . $agent_id . ' | money = ' . $money);
            return $this->sendError(10002, '系统繁忙，请稍后重试');
        }

        // 是否超过次数
        Db::startTrans();
        $todayCount = WithdrawLogModel::model()->getWithDrawNumsLock(['agent_id' => $agent_id, 'start' => $timestamp, 'end' => $timestamp + 86400]);
        if ($todayCount >= $config['limit_num']) {
            Db::commit();
            return $this->sendError(10001, '您今日提现次数已达上限');
        }
        // 提现日志
        $withdrawLog = [
            'withdraw_agent_id' => $agent_id,
            'withdraw_player_id' => $player_id,
            'withdraw_channel_id' => $info['agent_info']['agent_top_agentid'],
            'withdraw_before_money' => $agentAccountInfo['agent_account_money'],
            'withdraw_money' => $money,
            'withdraw_after_money' => $agentAccountInfo['agent_account_money'] - $money,
            'withdraw_poundage' => 0,
            'withdraw_get_money' => $money,
            'withdraw_method' => 1,
            'withdraw_type' => 0,
            'withdraw_status' => 2,
            'withdraw_approve_time' => 0,
            'withdraw_create_time' => time(),
            'withdraw_create_date' => date('Y-m-d H:i:s')
        ];
        $withdrawId = WithdrawLogModel::model()->insertOne($withdrawLog);
        if ($withdrawId) {
            Db::commit();
        } else {
            Db::rollback();
            Log::error('[ withdraw ] : 提现日志写入失败 | data = ' . json_encode($withdrawLog));
            // 账户加inc
            AgentAccountInfoModel::model()->updateMoney($agent_id, $money, 'inc');
            return $this->sendError(10002, '系统繁忙，请稍后重试');
        }

        Db::startTrans();
        // 账户变动日志
        $accountLog = [
            'log_agent_id' => $agent_id,
            'log_bef_money' => $agentAccountInfo['agent_account_money'],
            'log_money' => $money,
            'log_aft_money' => $agentAccountInfo['agent_account_money'] - $money,
            'log_add_time' => time(),
            'log_type' => AgentAccountInfoLogModel::TYPE_AGENT_WITHDRAW,
        ];
        $ret1 = AgentAccountInfoLogModel::model()->data($accountLog)->save();
        // 写入红包记录
        $order_no = Helper::createOrderId(30);
        $bonusLog = [
            'player_id' => $player_id,
            'agent_id' => $agent_id,
            'openid_gzh' => $playerInfo['player_openid_gzh'],
            'mch_billno' => $order_no,
            'total_amount' => $money,
            'type' => 1,
            'type_name' => 'withdraw',
            'create_time' => time()
        ];
        $ret2 = WxBonusLogModel::model()->insertOne($bonusLog);
        // 更新提现记录
        $ret3 = WithdrawLogModel::model()->save(['withdraw_method_log_id' => $ret2], ['withdraw_id' => $withdrawId]);

        if ($ret1 && $ret2 && $ret3) {
            Db::commit();
            // 发送红包 - 防止返回信息编码有误
            $return = BonusBlock::sendBonus($ret2, $config['limit_num']);
            if ($return && $return['status'] == 'success' && $return['data']['return_code'] == 'SUCCESS' && $return['data']['result_code'] == 'SUCCESS') {
                $update = [
                    'status' => WxBonusLogModel::STATUS_SENT,
                    'send_listid' => $return['data']['send_listid'],
                    'send_time' => date('Y-m-d H:i:s')
                ];
                $ret4 = WxBonusLogModel::model()->save($update, ['id' => $ret2]);
            } else {
                $ret4 = false;
                $error = isset($return['errorMsg']) ? $return['errorMsg'] : '无信息返回';
            }
            if ($ret4) {
                return $this->sendSuccess();
            } else {
                Log::error('[ withdraw ] : 红包发送失败 | id = ' . $ret2 . ' | ' . $error);
                return $this->sendError(10002, $error);
            }
        } else {
            Db::rollback();
            // 清楚提现日志
            WithdrawLogModel::model()->where(['withdraw_id' => $withdrawId])->delete();
            // 账户加inc
            AgentAccountInfoModel::model()->updateMoney($agent_id, $money, 'inc');

            Log::error('[ withdraw ] : 提现操作失败 | ret1 = ' . $ret1 . ' | ret2 = ' . $ret2 . ' | ret3 = ' . $ret3);

            return $this->sendError(10002, '系统繁忙，请稍后重试');
        }

    }

    // 回调处理   订单状态
    public function notifyAction()
    {
        $postData = $_POST;
        // Log::info('[ withdraw ] : ' . json_encode($postData));
        // {"order_id":"d602afc62fcc6129ce07e5c144af10","status":"2","send_config":"king"}
        $info = WxBonusLogModel::model()->where(['mch_billno' => $postData['order_id']])->find();
        $re = true;
        if ($info) {

            if ($postData['status'] == 2 && $info['status'] != 2) {
                // 已发放待领取 
                $re = WxBonusLogModel::model()->save(['send_time' => date('Y-m-d H:i:s'), 'status' => 2], ['id' => $info['id']]);
            }
            if ($postData['status'] == 4 && $info['status'] != 4) {
                // 已领取
                $re = WxBonusLogModel::model()->save(['receive_time' => date('Y-m-d H:i:s'), 'status' => 4], ['id' => $info['id']]);
            }
            if ($postData['status'] == 6 && $info['status'] != 6) {
                // 已退款,暂不处理
                $re = WxBonusLogModel::model()->save(['refund_time' => date('Y-m-d H:i:s'), 'status' => 6], ['id' => $info['id']]);
            }

        }
        if ($re !== false) {
            echo 'success';
        } else {
            echo 'fail';
        }
    }

    /**
     * @param $status
     * @return bool|string
     */
    public function wxStatusShow($status)
    {
        switch ($status) {
            case 0:
                $data = '未处理';
                break;
            case 1;
                $data = '发放中';
                break;
            case  2;
                $data = '已发放待领取';
                break;
            case  3;
                $data = '发放失败';
                break;
            case  4;
                $data = '已领取';
                break;
            case  5;
                $data = '退款中';
                break;
            case  6;
                $data = '已退款';
                break;
            default;
                $data = '未知';
        }
        return $data;
    }

    /**
     * @param $status
     */
    public function zfStatusShow($status)
    {
        switch ($status) {
            case 0;
                $data = '申请中';
                break;
            case 1;
                $data = '已审核';
                break;

            case 2;
                $data = '打款中';
                break;
            case 3;
                $data = '已打款';
                break;
            case 4;
                $data = '拒绝提现';
                break;
            default;
                $data = '未知';
        }

        return $data;
    }


}