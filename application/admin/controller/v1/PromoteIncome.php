<?php
/**
 * +----------------------------------------------------------
 * date: 2018-03-19 10:43:13
 * +----------------------------------------------------------
 * author: Raoxiaoya
 * +----------------------------------------------------------
 * describe: 渠道收益
 * +----------------------------------------------------------
 */

namespace app\admin\controller\v1;

use app\admin\controller\Controller;
use app\admin\model\PartnerIncomeMonthModel;
use app\admin\model\PartnerModel;
use app\common\components\Helper;
use app\admin\model\UsersModel;

/**
 * @controllerName 渠道收益
 */
class PromoteIncome extends Controller
{
    protected $error_code = 0;
    protected $error_msg = '';
    protected $size = 10 ;
    protected $max_search_num = 50;
    protected $max_search_nitice = '请再精确一下关键词';

    protected function _initialize()
    {
        parent::_initialize();
        $userInfo = $this->isLogin($this->token);
        if(isset($userInfo['partnerInfo'])){
            $this->login_partner_id = $userInfo['partnerInfo']['partner_id'];
        }else{
            $this->login_partner_id = 0;
        }
    }

    protected function initRequestParam(){
        $input = array(
            'partner_id' =>(int)$this->request->get('partner_id'),
            'page'       =>(int)$this->request->get('page'),
            'size'       =>(int)$this->request->get('size'),
            'start'      =>(string)$this->request->get('start'),// 加入时间
            'end'        =>(string)$this->request->get('end'),
            'turnpage'  =>(int)$this->request->get('turnpage')
        );
        /*
        $input = array(
            'partner_id' =>'',
            'page'       =>1,
            'size'       =>10,
            'start'      =>'2017-10-1',
            'end'        =>'2018-12-2',
            'turnpage'  =>0
        );
        */
        $filter = array();

        $start = strtotime($input['start']);
        $end = strtotime($input['end']);
        $time = time();
        $timestamp_day = strtotime(date('Y-m-d', $time));
        $timestamp_month = strtotime(date('Y-m', $time));
        $filter = array();

        $start = strtotime($input['start']);
        $end = strtotime($input['end']);
        if ($start && $end) {
            if ($start > $end) {
                $this->returnCode(10001, '请选择合适的时间区间');
                return false;
            }

            $start = strtotime(date('Y-m', $start));
            $end = strtotime(date('Y-m', $end));
            if ($start >= $timestamp_month) {
                $this->returnCode(10001, '请选择合适的时间区间，只能查看上个月及之前的记录');
                return false;
            }
            $filter['start'] = $start;
            if ($end > $timestamp_month) {
                $filter['end'] = $timestamp_month;
            } elseif ($end == $timestamp_month) {
                $filter['end'] = $end;
            } elseif ($end < $timestamp_month) {
                $filter['end'] = strtotime('+1 month', $end);
            }
            
        } else {
            if (!$start && !$end) {
                $filter['start'] = $timestamp_month;
                $filter['end'] = strtotime('+1 month', $filter['start']);
            } else {
                $this->returnCode(10001, '请选择合适的时间区间');
                return false;
            }
        }

        if($input['partner_id'] !== ''){
            $filter['partner_id'] = (int)$input['partner_id'];
        }

        if($input['page']){
            $filter['page'] = $input['page'];
        }else{
            $filter['page'] = 1;
        }

        if($input['size']){
            $filter['size'] = $input['size'];
        }else{
            $filter['size'] = $this->size;
        }

        if($input['turnpage']){
            $filter['turnpage'] = $input['turnpage'];
        }else{
            $filter['turnpage'] = 0;
        }

        if ($this->error_code) {
            return false;
        } else {
            $filter = (object)$filter;
            return $filter;
        }
        
    }

    /**
     * @actionName  获取金币充值记录
     */
    public function Income()
    {
        $condition = $this->initRequestParam();
        if ($condition === false) {
            return $this->sendError($this->error_code, $this->error_msg);
        }
        $list = PartnerIncomeMonthModel::model()->getList($condition);
        if($list){
            foreach($list->items() as $item){
                $partnerInfo = PartnerModel::model()->getOne(['partner_id'=>$item->partner_id]);
                if($partnerInfo){
                    $item->partner_name = $partnerInfo['partner_name'];
                }else{
                    $item->partner_name = '---';
                }
                // 账号
                $uinfo = UsersModel::model()->getUserById($partnerInfo['login_user_id']);
                if ($uinfo) {
                    $item->partner_login = $uinfo['user_login'];
                } else {
                    $item->partner_login = '';
                }
                $item->recharge_data = Helper::fomatBigData($item->recharge_data / 100);
                $item->partner_income = Helper::fomatBigData($item->partner_income / 100);
                $item->company_income = Helper::fomatBigData($item->company_income / 100);
                $item->company_rate = 1 - $item->third_share_rate - $item->share_rate;
            }
        }
        // 时间
        $filterDate = $this->addTime($condition);
        if($condition->turnpage){
            $total = [];
        }else{
            $temp = PartnerIncomeMonthModel::model()->getIncomeSum($condition);
            $total = [
                'total_income'   =>Helper::fomatBigData($temp['total_income'] / 100),
                'partner_income' =>Helper::fomatBigData($temp['partner_income'] / 100),
                'company_income' =>Helper::fomatBigData($temp['company_income'] / 100)
            ];
        }
        
        return $this->sendSuccess(['list' => $list,'total'=>$total,'date'=>$filterDate]);
    }

    /**
     * @actionName  获取金币充值记录--渠道后台
     */
    public function PartnerIncome()
    {
        $condition = $this->initRequestParam();
        if ($condition === false) {
            return $this->sendError($this->error_code, $this->error_msg);
        }
        if($this->login_partner_id > 0){
            $condition->partner_id = $this->login_partner_id;
        }
        $list = PartnerIncomeMonthModel::model()->getPartnerList($condition);
        if($list){
            foreach($list->items() as $item){
                if($item->status == 0){
                    $item->partner_income = '---';
                    $item->company_income = '---';
                }else{
                    $item->partner_income = Helper::fomatBigData($item->income / 100);
                    $item->company_income = Helper::fomatBigData($item->recharge_data * (1 - $item->third_share_rate - $item->share_rate) / 100);
                }
                $item->recharge_data = Helper::fomatBigData($item->recharge_data / 100);
            }
        }
        // 时间
        $filterDate = $this->addTime($condition);
        if($condition->turnpage){
            $total = [];
        }else{
            $temp = PartnerIncomeMonthModel::model()->getIncomeSum($condition);
            $total = [
                'total_income'   =>Helper::fomatBigData($temp['total_income'] / 100),
                'partner_income' =>Helper::fomatBigData($temp['partner_income'] / 100),
                'company_income' =>Helper::fomatBigData($temp['company_income'] / 100)
            ];
        }
        
        return $this->sendSuccess(['list' => $list,'total'=>$total,'date'=>$filterDate]);
    }

    protected function addTime($condition = null)
    {
        $ret_time = array(
            'start_date' => '',
            'end_date' => ''
        );

        if (isset($condition->start)) {
            $ret_time['start_date'] = date('Y-m-d', $condition->start);
        }
        if (isset($condition->end)) {
            $ret_time['end_date'] = date('Y-m-d', strtotime('-1 day', $condition->end));
        }

        return $ret_time;
    }

    protected function returnCode($code, $error)
    {
        $this->error_code = $code;
        $this->error_msg = $error;
    }
}
