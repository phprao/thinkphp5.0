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
use app\admin\model\PlayerModel;
use app\admin\model\PartnerPayRecordModel;
use app\admin\model\PartnerModel;
use app\admin\model\PartnerOrderModel;
use app\common\components\Helper;

/**
 * @controllerName 金币充值记录
 */
class PromoteRecharge extends Controller
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
            'keyword'    =>(string)$this->request->get('keyword'),// player_id
            'partner_id' =>(int)$this->request->get('partner_id'),
            'pay_type'   =>(int)$this->request->get('pay_type'),
            'page'       =>(int)$this->request->get('page'),
            'size'       =>(int)$this->request->get('size'),
            'start'      =>(string)$this->request->get('start'),// 加入时间
            'end'        =>(string)$this->request->get('end'),
            'turnpage'  =>(int)$this->request->get('turnpage')
        );
        /*
        $input = array(
            'keyword'    =>'',
            'partner_id' =>'',
            'pay_type'   =>'',
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

        if ($start && $end) {
            if ($start > $end) {
                $this->returnCode(10001, '请选择合适的时间区间');
                return false;
            }
            
            $start = strtotime(date('Y-m-d', $start));
            $end = strtotime(date('Y-m-d', $end));
            // 包含今日
            if ($start > $timestamp_day) {
                $this->returnCode(10001, '请选择合适的时间区间');
                return false;
            }
            $filter['start'] = $start;
            if ($end > $timestamp_day) {
                $filter['end'] = $timestamp_day + 86400;
            } elseif ($end == $timestamp_day) {
                $filter['end'] = $end + 86400;
            } elseif ($end < $timestamp_day) {
                $filter['end'] = $end + 86400;
            }
        }else{
            if($start){
                $start = strtotime(date('Y-m-d', $start));
                if ($start > $timestamp_day) {
                    $this->returnCode(10001, '请选择合适的时间区间');
                    return false;
                }
                $filter['start'] = $start;
            }
            if($end){
                if ($end > $timestamp_day) {
                    $filter['end'] = $timestamp_day + 86400;
                } elseif ($end == $timestamp_day) {
                    $filter['end'] = $end + 86400;
                } elseif ($end < $timestamp_day) {
                    $filter['end'] = $end + 86400;
                }
            }
        }

        if($input['keyword'] !== ''){
            $filter['keyword'] = $input['keyword'];
        }

        if($input['partner_id'] !== ''){
            $filter['partner_id'] = (int)$input['partner_id'];
        }

        if($input['pay_type']){
            $filter['pay_type'] = (int)$input['pay_type'];
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
    public function records()
    {
        $condition = $this->initRequestParam();
        if ($condition === false) {
            return $this->sendError($this->error_code, $this->error_msg);
        }
        if($this->login_partner_id > 0){
            $condition->partner_id = $this->login_partner_id;
        }
        $list = PartnerPayRecordModel::model()->getList($condition);
        if($list){
            foreach($list->items() as $item){
                $playerInfo = PlayerModel::model()->getPlayerinfoById($item->recored_player_id,['player_id,player_nickname']);
                if($playerInfo){
                    $item->player_nickname = urldecode($playerInfo['player_nickname']);
                }else{
                    $item->player_nickname = '---';
                }
                $partnerInfo = PartnerModel::model()->getOne(['partner_id'=>$item->recored_partner_id]);
                if($partnerInfo){
                    $item->partner_name = $partnerInfo['partner_name'];
                }else{
                    $item->partner_name = '---';
                }
                switch($item->recored_type){
                    case 1:
                        $item->recored_type_desc = '微信';
                        break;
                    case 2:
                        $item->recored_type_desc = '支付宝';
                        break;
                    case 3:
                        $item->recored_type_desc = '苹果支付';
                        break;
                    case 4:
                        $item->recored_type_desc = 'web支付';
                        break;
                    default:
                        $item->recored_type_desc = '未知';
                        break;
                }
                $order = PartnerOrderModel::model()->getOne(['order_id'=>$item->recored_order_id]);
                $item->order_no = $order['order_orderno'];
                $item->recored_price = $item->recored_price/100;
                $item->rate = '1:' . $item->recored_get_money/$item->recored_price;
                $item->recored_create_time = date('Y-m-d H:i:s', $item->recored_create_time);
            }
        }
        // 时间
        $filterDate = $this->addTime($condition);
        if($condition->turnpage){
            $total_tax = 0;
        }else{
            $total_tax = PartnerPayRecordModel::model()->getRechargeSum($condition);
            $total_tax = Helper::fomatBigData($total_tax/100);
        }
        
        return $this->sendSuccess(['list' => $list,'total_tax'=>$total_tax,'date'=>$filterDate]);
    }

    protected function addTime($condition = null)
    {
        $ret_time = [];
        if(isset($condition->start)){
            $ret_time['start_date'] = date('Y-m-d', $condition->start);
        }
        if(isset($condition->end)){
            $ret_time['end_date'] = date('Y-m-d', $condition->end - 86400);
        }
        return $ret_time;
    }

    protected function returnCode($code, $error)
    {
        $this->error_code = $code;
        $this->error_msg = $error;
    }
}
