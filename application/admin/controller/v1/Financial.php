<?php
/**
 * +----------------------------------------------------------
 * date: 2018-03-19
 * +----------------------------------------------------------
 * author: liang jun bin
 * +----------------------------------------------------------
 * describe: 财务管理
 * +----------------------------------------------------------
 */

namespace app\admin\controller\v1;

use app\admin\controller\Controller;
use app\admin\model\AgentInfoModel;
use app\admin\model\ConfigModel;
use app\admin\model\WithdrawLogModel;
use app\admin\model\WithdrawConfigModel;
use app\admin\model\AgentAccountInfoModel;
use app\admin\model\WxBonusLogModel;

/**
 * @controllerName 财务管理
 */
class Financial extends Controller
{

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     * @actionName 提现审批列表
     */
    public function index()
    {
        $loginData = $this->isLogin($this->request->get('token'));
        if (isset($loginData['agentInfo'])) {
            $agentid = $loginData['agentInfo']['agent_id'];
        }

        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('pageSize', 10);
        $keyword = $this->request->get('keyword');
        if ($keyword) {
            $condition['keyword'] = $keyword;
        }
        if ($this->request->get('start_time') || $this->request->get('end_time')) {
            $time = $this->request->get('start_time');
            $end_time = $this->request->get('end_time');
            $condition['withdraw_create_time'][0] = array('EGT', strtotime($time));
            $condition['withdraw_create_time'][1] = array('LT', strtotime($end_time) + 24 * 3600);
        }
        $condition['withdraw_status'] = 0;
        $withlist = WithdrawLogModel::model()->getWithdrawList($condition, $page, $pageSize);
        //分页
        $count = WithdrawLogModel::model()->getWithdrawListCount($condition);
        $datalist = [];
        foreach ($withlist as $item) {
            $agentinfo = AgentInfoModel::model()->getAgentId(array('agent_id' => $item['withdraw_channel_id']));
            $value['withdraw_id'] = $item['withdraw_id'];
            $value['withdraw_status'] = $item['withdraw_status'];
            $value['withdraw_agent_id'] = $item['withdraw_agent_id'];
            $value['withdraw_agent_name'] = urldecode($item['player_nickname']) . '(' . $item['withdraw_player_id'] . ')';
            $value['withdraw_parent_name'] = $agentinfo['agent_name'] . '(' . $agentinfo['agent_id'] . ')';
            $value['withdraw_account_money'] = $item['withdraw_money'];
            $value['withdraw_money'] = $item['withdraw_money'];
            $value['withdraw_after_money'] = $item['withdraw_after_money'];
            $value['withdraw_time'] = date('Y-m-d H:i:s', $item['withdraw_create_time']);
            $datalist[] = $value;
        }

        $data = array(
            'total' => $count,
            'per_page' => $pageSize,
            'page' => $page,
            'last_page' => ceil($count / $pageSize),
            'list' => $datalist,
        );
        return $this->sendSuccess($data);

    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @actionName 提现记录
     */
    public function recordList()
    {
        $loginData = $this->isLogin($this->request->get('token'));
        if (isset($loginData['agentInfo'])) {
            $agentid = $loginData['agentInfo']['agent_id'];
        }
        $condition = [];
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('pageSize', 10);
        $keyword = $this->request->get('keyword');
        $results_type = $this->request->get('results_type');
        if ($keyword) {
            $condition['keyword'] = $keyword;
        }
        if ($results_type == 1) {
            $condition['withdraw_type'] = 0;
        } else if ($results_type == 2) {
            $condition['withdraw_type'] = 1;
        }
        if ($this->request->get('start_time') || $this->request->get('end_time')) {
            $time = $this->request->get('start_time');
            $end_time = $this->request->get('end_time');
            $condition['withdraw_create_time'][0] = array('EGT', strtotime($time));
            $condition['withdraw_create_time'][1] = array('LT', strtotime($end_time) + 24 * 3600);
        }
//        $condition['withdraw_status'] = 0;
        $withlist = WithdrawLogModel::model()->getWithdrawList($condition, $page, $pageSize);
        //分页
        $count = WithdrawLogModel::model()->getWithdrawListCount($condition);
        $datalist = [];
        foreach ($withlist as $key => $item) {

            if ($item['withdraw_method'] == 1) {
                $wxloginfo = WxBonusLogModel::model()->getfind(array('id' => $item['withdraw_method_log_id']));
                $zfStatusInfo = $this->wxStatusShow($wxloginfo['status']);
                $withdraw_status = $wxloginfo['status'];
            } else if ($item['withdraw_method'] == 2) {
                $zfStatusInfo = $this->zfStatusShow($item['withdraw_status']);
                $withdraw_status = $item['withdraw_status'];
            }
            switch ($item['withdraw_method']) {
                case 1;
                    $value['withdraw_type_show'] = '微信红包';
                    break;
                case  2;
                    $value['withdraw_type_show'] = '支付宝';
                    break;
                default;
                    $value['withdraw_type_show'] = '未知';
            }
            $agentinfo = AgentInfoModel::model()->getAgentId(array('agent_id' => $item['withdraw_channel_id']));
            $value['withdraw_status_show'] = $zfStatusInfo;
            $value['withdraw_id'] = $item['withdraw_id'];
            $value['withdraw_type'] = $item['withdraw_type'];
            // $value['withdraw_status'] = $item['withdraw_status'];
            $value['withdraw_status'] = $withdraw_status;
            $value['withdraw_agent_id'] = $item['withdraw_agent_id'];
            $value['withdraw_agent_name'] = urldecode($item['player_nickname']) . '(' . $item['withdraw_player_id'] . ')';
            $value['withdraw_parent_name'] = $agentinfo['agent_name'] . '(' . $agentinfo['agent_id'] . ')';
            $value['withdraw_account_money'] = $item['withdraw_before_money'] / 100;
            $value['withdraw_money'] = $item['withdraw_money'] / 100;
            $value['withdraw_after_money'] = $item['withdraw_after_money'] / 100;
            $value['withdraw_time'] = date('Y-m-d H:i:s', $item['withdraw_create_time']);
            $datalist[] = $value;
        }

        $data = array(
            'total' => $count,
            'per_page' => $pageSize,
            'page' => $page,
            'last_page' => ceil($count / $pageSize),
            'list' => $datalist,
        );
        return $this->sendSuccess($data);

    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @actionName 提现设置显示
     */
    public function managemntShow()
    {
        $loginData = $this->isLogin($this->request->get('token'));
        if (isset($loginData['agentInfo'])) {
            $agentid = $loginData['agentInfo']['agent_id'];
        }
        $data = [];
        $cofinfo = ConfigModel::model()->getFint(array('config_name' => 'withdraw_config_star', 'config_status' => 1));
        if ($cofinfo) {
            $datashow = json_decode($cofinfo['config_config'], true);

            $data = array(
                'withdraw_money_max' => $datashow['limit_max'] / 100,
                'withdraw_money_min' => $datashow['limit_min'] / 100,
                'withdraw_nums' => $datashow['limit_num'],
                'withdraw_poundage' => $datashow['poundage'],
                'withdraw_auto' => $datashow['auto_money'] / 100,
            );
        }

        return $this->sendSuccess($data);

    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     *
     * @actionName 提现设置
     */
    public function cashManagement()
    {
        $loginData = $this->isLogin($this->request->get('token'));
        if (isset($loginData['agentInfo'])) {
            $agentid = $loginData['agentInfo']['agent_id'];
        }

        $withdraw_money_max = $this->request->get('ceiling');
        $withdraw_money_min = $this->request->get('lower_limit');
        $withdraw_nums = $this->request->get('number');
        $withdraw_poundage = $this->request->get('poundage');
        $withdraw_auto = $this->request->get('withdraw_auto');
        $cofinfo = ConfigModel::model()->getFint(array('config_name' => 'withdraw_config_star', 'config_status' => 1));
        if ($cofinfo) {
            $datashow = json_decode($cofinfo['config_config'], true);
            if ($withdraw_money_max) {
                $limit_max = $withdraw_money_max;
            } else {
                $limit_max = $datashow['limit_max'];
            }
            if ($withdraw_money_min) {
                $limit_min = $withdraw_money_min;
            } else {
                $limit_min = $datashow['limit_min'];
            }
            if ($withdraw_nums) {
                $limit_num = $withdraw_nums;
            } else {
                $limit_num = $datashow['limit_num'];
            }
            if ($withdraw_poundage) {
                $poundage = $withdraw_poundage;
            } else {
                $poundage = $datashow['poundage'];
            }
            if ($withdraw_auto) {
                $auto_money = $withdraw_auto;
            } else {
                $auto_money = $datashow['auto_money'];
            }
            $json_encode = array(
                'limit_max' => $limit_max * 100,
                'limit_min' => $limit_min * 100,
                'limit_num' => $limit_num,
                'poundage' => $poundage,
                'auto_money' => $auto_money * 100,
            );
            $redata = json_encode($json_encode, true);
            $condition['config_config'] = $redata;
            $res = ConfigModel::model()->saveConfig($cofinfo['config_id'], $condition);
        } else {
            $json_encode = array(
                'limit_max' => $withdraw_money_max ? $withdraw_money_max : 0,
                'limit_min' => $withdraw_money_min ? $withdraw_money_min : 0,
                'limit_num' => $withdraw_nums ? $withdraw_nums : 0,
                'poundage' => $withdraw_poundage ? $withdraw_poundage : 0,
                'auto_money' => $withdraw_auto ? $withdraw_auto : 0,
            );
            $redata = json_encode($json_encode, true);
            $condition['config_name'] = 'withdraw_config_star';
            $condition['config_desc'] = '推广员收益提现配置：元';
            $condition['config_type'] = 0;
            $condition['config_config'] = $redata;
            $condition['config_create_time'] = time();
            $res = ConfigModel::model()->insertConfing($condition);
        }

        if ($res) {
            return $this->sendSuccess($res);
        } else {
            return $this->sendError(2000, '设置失败！');
        }

    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @actionName 提现审批
     */
    public function examinationApproval()
    {
        $loginData = $this->isLogin($this->request->get('token'));
        if (isset($loginData['agentInfo'])) {
            $agentid = $loginData['agentInfo']['agent_id'];
        }

        $id = $this->request->get('withdraw_id');
        $status = $this->request->get('status');

        if (!$id) {
            return $this->sendError(2000, '没有ID！');
        }
        if (!$status) {
            return $this->sendError(2000, '没有传状态！');
        }

        $condition['withdraw_id'] = $id;
        $datainfo = WithdrawLogModel::model()->getinfo($condition);
        if (!$datainfo) {
            return $this->sendError(2000, '没有这条审批！');
        }

        //查询代理信息表数据
        $agentconfinfo = AgentAccountInfoModel::model()->getAccountInfoByAgentId($datainfo['withdraw_agent_id']);
        if (!$agentconfinfo) {
            return $this->sendError(2000, '没有这个代理！');
        }

        $datainfo_money = $datainfo['withdraw_money'];
        $agent_money = $agentconfinfo['agent_account_money'];
        $agent['agent_account_money'] = $agent_money - $datainfo_money;
        $agent_res = AgentAccountInfoModel::model()->saveAgetnsave($datainfo['withdraw_agent_id'], $agent);

        $wlogtion['withdraw_status'] = $status;
        $wlogtion['withdraw_type'] = 1;
        $wlogtion['withdraw_approve_time'] = time();
        $res = WithdrawLogModel::model()->saveConf($id, $wlogtion);

        if ($res && $agent_res) {
            return $this->sendSuccess($res);
        } else {
            return $this->sendError(2000, '审核失败！');
        }

    }

    /**
     * @param $status
     * @return bool|string
     */
    public function wxStatusShow($status)
    {
//        if (!$status) {
//            return false;
//        }
        $data = '';
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

        $data = '';
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





