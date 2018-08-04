<?php
/**
 * +----------------------------------------------------------
 * date: 2018-03-19 10:43:13
 * +----------------------------------------------------------
 * author: Raoxiaoya
 * +----------------------------------------------------------
 * describe: 金币充值记录
 * +----------------------------------------------------------
 */

namespace app\admin\controller\v1;

use app\admin\controller\Controller;
use app\admin\model\PayRecordModel;
use app\admin\model\StatisticsTotalModel;

/**
 * @controllerName 金币充值记录
 */
class PayRecord extends Controller
{
    protected static $rule = [
        'page' => 'integer|between:1,10000',
        'pageSize' => 'integer|between:1,20',
    ];

    protected function _initialize()
    {
        parent::_initialize();
        $this->isLogin($this->token);
    }

    /**
     * @actionName  获取金币充值记录
     */
    public function records()
    {
        $validation = $this->validate($this->request->param(), self::$rule);
        if ($validation !== true) {
            return $this->sendError(3001, $validation);
        }
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('pageSize', 10);
        $condition = [
            'recore_get_type' => 2,
        ];
        $keywords = $this->request->get('keywords', null);
        if (!empty($keywords)) {
            $condition['keywords'] = $keywords;
        }
        $top_type = $this->request->get('top_type', null);
        if ($top_type) {
            $condition['recore_type'] = $top_type;
        }


        $date = $this->getSearchDate();
        $sTime = strtotime($this->request->get('s_time', null));
        if (!empty($sTime)) {
            $sTime = $sTime;
        } else {
            $sTime = strtotime($date['startDate']);
        }
        $eTime = strtotime($this->request->get('e_time', null));
        if (!empty($eTime)) {
            $eTime = $eTime;
        } else {
            $eTime = strtotime($date['endDate']);
        }

        $condition['recore_create_time'] = [['>=', $sTime], ['<=', $eTime + 24 * 3600], 'and'];
        $conditiontotal['statistics_timestamp'] = [['>=', $sTime], ['<', $eTime + 24 * 3600], 'and'];
        $conditiontotal['statistics_role_type'] = 0;
        $conditiontotal['statistics_mode'] = 1;
        $conditiontotal['statistics_type'] = 1;
        $records = new PayRecordModel();
//        $statistcs = StatisticsTotalModel::model()->getPromotersSumInfo($conditiontotal);
        $statistcs = $records::model()->getPriceSum($condition);

        $records = $records->getPayRecords($condition, $page, $pageSize);
        $count = PayRecordModel::model()->getRecordsCount($condition);
        $date = $this->getSearchDate();
        return $this->sendSuccess([
            'total' => $count,
            'per_page' => $pageSize,
            'current_page' => $page,
            'last_page' => ceil($count / $pageSize),
            'total_amount' => $statistcs?$statistcs / 100 : 0,
            'date' => $date,
            'list' => $records,
        ]);
    }

    protected function getSearchDate()
    {
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        return ['startDate' => $startDate, 'endDate' => $endDate];
    }
}
