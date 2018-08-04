<?php
/**
 * +----------------------------------------------------------
 * date: 2018-03-13 16:16:37
 * +----------------------------------------------------------
 * author: Raoxiaoya
 * +----------------------------------------------------------
 * describe: 首页数据
 * +----------------------------------------------------------
 */

namespace app\api\controller\v1;

use app\api\controller\Controller;
use app\api\model\AgentInfoModel;
use app\api\model\AgentNoticeInfoModel;
use app\api\model\AgentIncomePromotesModel;
use app\api\model\NoticeModel;
use think\Collection;
use app\common\components\Helper;


class Home extends Controller
{

    public $data_type = 0;

    /**
     * 刷新总接口
     */
    public function totalInterface()
    {
        $agnetInfo = $this->isLogin($this->token);
        $playerId = $agnetInfo['user_info']['id'];

        if ($playerId) {
            $this->data_type = 1;
            $index = $this->index($playerId);
            $proFit = $this->proFit($playerId);
            $agentNotice = $this->agentNotice($playerId);

            $data = array(
                'data1' => $index ? $index : 0,
                'data2' => $proFit ? $proFit : 0,
                'data3' => $agentNotice ? $agentNotice : 0,
            );
            return $this->sendSuccess($data);
        } else {
            return $this->sendError(2003, '服务器繁忙-请稍后再试');
            exit;
        }

    }

    public function checkToken()
    {
        if ($this->virtualLogin()) {
            return $this->sendSuccess();
        } else {
            return $this->sendError(10001, '授权出错');
        }

    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index($playerId = null)
    {
        if (!is_null($playerId) && $this->data_type == 1) {
            $playerId = $playerId;
        } else {
            $agnetInfo = $this->isLogin($this->token);
            $playerId = $agnetInfo['user_info']['id'];
        }

        if ($playerId) {
            $AgentInfoModel = new AgentInfoModel();
            $AgentIncomeDetailModel = new AgentIncomePromotesModel();
            $data = $AgentInfoModel->getInfo(array('agent_player_id' => $playerId));
            if ($data) {
                // 一层推广人数
                $data['parentidcount'] = $AgentInfoModel->getAgentCount(['agent_parentid' => $data['agent_id'], 'agent_login_status' => 1]);
                // 二层推广人数
                $data['the_secondary'] = $AgentInfoModel->getAgentCount(['agent_p_parentid' => $data['agent_id'], 'agent_login_status' => 1]);
                $today = strtotime(date('Y-m-d', time())); //凌晨时间
                $endtime = $today + 24 * 3600;//24点
                // 今日收益
                $today_earnings = $AgentIncomeDetailModel->gteAgentMonthInfo(
                    [
                        'statistics_agents_id' => $data['agent_id'],
                        'statistics_time' => [['>=', $today], ['<', $endtime]]
                    ],
                    'statistics_my_income'
                );
                $date = date('Y-m-d H:i:s');
                $firstday = $this->getThemonth($date);
                $MonthAgo = strtotime($firstday[0]);
                $EachMonth = strtotime($firstday[1]);
                // 当月收益
                $month_earnings = $AgentIncomeDetailModel->gteAgentMonthInfo(
                    [
                        'statistics_agents_id' => $data['agent_id'],
                        'statistics_time' => [['>=', $MonthAgo], ['<', $EachMonth]]
                    ],
                    'statistics_my_income'
                );

                //在线人数
                $data['number_online'] = '--';

                $data = array(
                    'agent_id' => $data['agent_id'],
                    'player_id' => $data['agent_player_id'],
                    'agent_name' => '',
                    'agent_promote_count' => $data['agent_promote_count'],
                    'number_online' => $data['number_online'],
                    'parentidcount' => $data['parentidcount'],
                    'the_secondary' => $data['the_secondary'],
                    'today_earnings' => Helper::cutDataByLen($today_earnings / 100),// 元
                    'month_earnings' => Helper::cutDataByLen($month_earnings / 100),
                    'agent_remark' => $data['agent_remark'],
                    'agent_createtime' => date('Y-m-d H:i:s', $data['agent_createtime'])
                );
            }
            if ($this->data_type == 1) {
                return $data;
            } else {
                return $this->sendSuccess($data);
            }
        } else {
            return $this->sendError(2003, '服务器繁忙-请稍后再试');
        }

    }

    /**
     * 我的盈利
     * 首尾固定，中间均分五个点
     */
    public function proFit($playerId = null)
    {
        if (!is_null($playerId) && $this->data_type == 1) {
            $playerId = $playerId;
        } else {
            $agnetInfo = $this->isLogin($this->token);
            $playerId = $agnetInfo['user_info']['id'];
        }
        $AgentInfoModel = new AgentInfoModel();
        $AgentIncomeDetailModel = new AgentIncomePromotesModel();

        $start = strtotime(input('start_time'));
        $end = strtotime(input('end_time'));
        if ($start) {
            $start_time = strtotime(date('Y-m-d', $start));
        } else {
            $start_time = 0;
        }
        if ($end) {
            $end_time = strtotime(date('Y-m-d', $end));
        } else {
            $end_time = 0;
        }

        if ($playerId) {
            $agentinfo = $AgentInfoModel->getInfo(array('agent_player_id' => $playerId));

            $date = $this->getSevenDate($start_time, $end_time);

            if ($date) {
                $today_earnings = [];
                $datetimt = [];
                foreach ($date as $key => $value) {
                    $todayinfo = $AgentIncomeDetailModel->gteAgentMonthInfo(
                        [
                            'statistics_agents_id' => $agentinfo['agent_id'],
                            'statistics_time' => [['>=', $value], ['<=', $value]]
                        ],
                        'statistics_my_income'
                    );
                    $today_earnings[$key]['statistics_my_income'] = $todayinfo / 100;// 元
                    $today_earnings[$key]['time_info'] = date('Y-m-d', $value);
                    $today_earnings[$key]['time'] = date('m/d', $value);
                    $datetimt[] = date('m/d', $value);
                    sort($datetimt);
                }
            } else {
                return $this->sendError(2004, '请选择时间且在三个月内');
            }

            $data = array(
                'monday' => $today_earnings,
                'thedata' => $datetimt,
            );

            if ($this->data_type == 1) {
                return $data;
            } else {
                return $this->sendSuccess($data);
            }
        } else {
            return $this->sendError(2003, '服务器繁忙-请稍后再试');
            exit;
        }
    }

    /**
     *代理公告
     */
    public function agentNotice($playerId = null)
    {
        if (!is_null($playerId) && $this->data_type == 1) {
            $playerId = $playerId;
        } else {
            $agnetInfo = $this->isLogin($this->token);
            $playerId = $agnetInfo['user_info']['id'];
        }
        if ($playerId) {

//            $AgentInfoModel = new AgentInfoModel();
//            $model = new AgentNoticeInfoModel();
//            $agentInfo = $AgentInfoModel->getInfo(array('agent_player_id' => $playerId));
//            $agentTopId = $agentInfo['agent_top_agentid'];
//            $data = $model->gteNoticeAlliLst(
//                [
//                    'agent_notice_agent_id' => ['IN', "(0,$agentTopId)"],
//                    'agent_notice_status' => 1
//                ],
//                'agent_notice_id  desc'
//            );
//            foreach ($data as $key => &$value) {
//                $data[$key]['agent_notice_date'] = $this->formatDate($value['agent_notice_create_time']);
//                $data[$key]['agent_notice_create_time'] = date('Y-m-d H:i:s', $value['agent_notice_create_time']);
//            }
//            if ($this->data_type == 1) {
//                return $data;
//            } else {
//                return $this->sendSuccess($data);
//            }
            $agentInfo =  AgentInfoModel::model()->getInfo(array('agent_player_id' => $playerId));
            $agentTopId = $agentInfo['agent_top_agentid'];
            $data = NoticeModel::model()->get_Select(
                [
                    'notice_type' => 3,
                    'notice_status' => 1,
                    'notice_agent_id' => ['IN' ,"0,$agentTopId"],
                    'notice_start_id' => ['IN' ,"0,$playerId"]
                ],
                'notice_create_time desc'

            );
            foreach ($data as $key => &$value) {
                $data[$key]['notice_date'] = $this->formatDate($value['notice_create_time']);
                $data[$key]['notice_create_time'] = date('Y-m-d H:i:s', $value['notice_create_time']);
            }
            if ($this->data_type == 1) {
                return $data;
            } else {
                return $this->sendSuccess($data);
            }

        } else {
            return $this->sendError(2003, '服务器繁忙-请稍后再试');
        }

    }


    /**
     * @param $date
     * @return array
     */
    function getThemonth($date)
    {

        $firstday = date('Y-m-01', strtotime($date)); //月初
        $lastday = date('Y-m-d', strtotime("$firstday +1 month"));//月末
        return array($firstday, $lastday);

    }

    /**
     * @param $time
     * @return string
     */
    function formatDate($time)
    {
        $nowtime = time();
        $difference = $nowtime - $time;

        switch ($difference) {

            case $difference <= '60' :
                $msg = '刚刚';
                break;

            case $difference > '60' && $difference <= '3600' :
                $msg = floor($difference / 60) . '分钟前';
                break;

            case $difference > '3600' && $difference <= '86400' :
                $msg = floor($difference / 3600) . '小时前';
                break;

            case $difference > '86400' && $difference <= '2592000' :
                $msg = floor($difference / 86400) . '天前';
                break;

            case $difference > '2592000' && $difference <= '199776000':
                $msg = floor($difference / 2592000) . '个月前';
                break;
            case $difference > '199776000':
                $msg = '很久以前';
                break;
        }

        return $msg;
    }

    function getSevenDate($start_time = 0, $end_time = 0)
    {
        if (!$start_time && !$end_time) {
            // 默认显示最近一周
            $end_time = strtotime(date('Y-m-d'));
            $start_time = strtotime("-6 day", $end_time);
        }
        if (!$start_time && $end_time) {
            return false;
        }
        if ($start_time && !$end_time) {
            return false;
        }
        if ($start_time > $end_time) {
            return false;
        }

        // 最多显示前三个月的数据
        $deadStartDay = strtotime(date('Y-m-d', strtotime("-0 year -3 month -0 day")));
        $deadEndDay = strtotime(date('Y-m-d'));

        if ($start_time < $deadStartDay) {
            $start_time = $deadStartDay;
        }
        if ($start_time > $deadEndDay) {
            return false;
        }
        if ($end_time > $deadEndDay) {
            $end_time = $deadEndDay;
        }
        if ($end_time < $deadStartDay) {
            return false;
        }

        $date = [];
        $date[0] = $start_time;
        $totalDay = ($end_time - $start_time) / 86400;// 总天数
        if ($totalDay == 0) {
            return $date;
        } elseif ($totalDay > 0 && $totalDay < 7) {
            for ($j = 1; $j <= $totalDay; $j++) {
                $date[$j] = $start_time + $j * 86400;
            }
        } elseif ($totalDay >= 7) {
            // 至少是8天
            $diff = floor($totalDay / 6);// 增量数；
            $ext = $totalDay % 6;
            $date[6] = $end_time;
            // 剩余按5个点分
            for ($i = 1; $i <= $ext; $i++) {
                $ext_arr[$i] = 86400;
            }

            for ($i = 1; $i <= 5; $i++) {
                if (isset($ext_arr[$i])) {
                    $date[$i] = $date[$i - 1] + $diff * 86400 + $ext_arr[$i];
                } else {
                    $date[$i] = $date[$i - 1] + $diff * 86400;
                }
            }
        }

        return $date;
    }


}









