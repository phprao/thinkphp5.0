<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-03-19 10:22:28
 +---------------------------------------------------------- 
 * author: 
 +---------------------------------------------------------- 
 * describe: 弃用
 +---------------------------------------------------------- 
 */

namespace app\admin\controller\v1;

use app\admin\controller\Controller;
use app\admin\model\PlayerModel;
use app\admin\model\PayRecordModel;
use app\admin\model\AgentsStatisticsPlayerModel;
use app\admin\model\PromotersInfoModel;


class WeekStatistics extends Controller
{

    /**
     * 注册人数周统计
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private $num = 7;
    private $superAgentId;
    private $role_id;
    public function _initialize(){
        $loginData = $this->isLogin($this->request->get('token'));
        if(isset($loginData['userInfo']['role_id'])){
            $this->role_id = $loginData['userInfo']['role_id'];
        }
        
        if (isset($loginData['agentInfo'])) {
            $this->superAgentId = $loginData['agentInfo']['agent_id'];
        }
    }

    public function index()
    {
        //按周统计
        $res['date'] = $this->weekArray();//周数组
        $weekResult = $this->weekCompute('register');//上周本周注册人数数据
    
        $res['total']['lastweek'] = $weekResult['lastweek'];
        $res['total']['thisweek'] = $weekResult['thisweek'];

        return $this->sendSuccess($res);
    }

    /**
     * 充值金额周统计
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function income()
    {
        //按周统计
        $res['date'] = $this->weekArray();//周数组
        $weekRes = $this->weekCompute('income');//上周本周注册人数数据

        $res['total']['lastweek'] = $weekRes['lastweek'];
        $res['total']['thisweek'] = $weekRes['thisweek'];

        return $this->sendSuccess($res);
    }

    /**
     * 金币消耗周统计
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function gold()
    {
        //按周统计
        $res['date'] = $this->weekArray();//周数组
        $weekRes = $this->weekCompute('gold');//上周本周注册人数数据

        $res['total']['lastweek'] = $weekRes['lastweek'];
        $res['total']['thisweek'] = $weekRes['thisweek'];

        return $this->sendSuccess($res);
    }

    /**
     * @param 类型 type
     * 统计七天通用方法
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function weekCompute($type){
        $lastWeek = strtotime(date('Y-m-d',strtotime('-1 week last monday')));
        $thisWeek = strtotime(date('Y-m-d',strtotime('0 week last monday')));
        for($i = 0; $i < $this->num; $i++){
            //上周时间戳
            $y_start_time = $lastWeek + ($i * 86400);
            $y_end_time = $lastWeek + (($i+1) * 86400);
            //本周时间戳
            $t_start_time = $thisWeek + ($i * 86400);
            $t_end_time = $thisWeek + (($i+1) * 86400);

            $condition['y_start_time'] = $y_start_time;
            $condition['y_end_time'] = $y_end_time;
            $condition['t_start_time'] = $t_start_time;
            $condition['t_end_time'] = $t_end_time;

            //判断找哪种数据
            if($type == 'register'){
                $weekData = $this->weekComputePlay($condition);
            }elseif($type == 'income'){
                $weekData = $this->weekComputeIncome($condition);
            }elseif($type == 'gold'){
                $weekData = $this->weekComputeGold($condition);
            }
            $res['lastweek'][$i] = $weekData['lastweek'];
            $res['thisweek'][$i] = $weekData['thisweek'];
        }
        return $res;
    }

    //统计上周注册用户数
    private function weekComputePlay($condition){
        //返回公司总后台的数据
        if($this->role_id == 4){
            $field = $this->getCondition('player_resigter_time',$condition);  //搜索条件
            $res['lastweek'] = PlayerModel::model()->getPlayerBytime($field['y_condition']);
            $res['thisweek'] = PlayerModel::model()->getPlayerBytime($field['t_condition']);
        }elseif($this->role_id == 2 || $this->role_id == 3){
            //返回特代的数据
            $field = $this->getCondition('promoters_time',$condition);  //搜索条件
            if($this->role_id == 2){
                $field['y_condition']['promoters_parent_id'] = $field['t_condition']['promoters_parent_id'] = $this->superAgentId;
            }elseif($this->role_id == 3){
                $field['y_condition']['promoters_agent_id'] = $field['t_condition']['promoters_agent_id'] = $this->superAgentId;
            }
            
            $res['lastweek'] = PromotersInfoModel::model()->getPlayerBytime($field['y_condition']);
            $res['thisweek'] = PromotersInfoModel::model()->getPlayerBytime($field['t_condition']);
        }
        
        return $res;
    }

    //统计上周充值金额
    private function weekComputeIncome($condition){
        $field = $this->getCondition('recore_create_time',$condition);  //搜索条件
        $res['lastweek'] = PayRecordModel::model()->getIncomeBytime($field['y_condition']);
        $res['thisweek'] = PayRecordModel::model()->getIncomeBytime($field['t_condition']);
        return $res;
    }

    //统计上周金币消耗
    private function weekComputeGold($condition){
        $field = $this->getCondition('change_money_time',$condition);  //搜索条件
        $res['lastweek'] = AgentsStatisticsPlayerModel::model()->getGoldBytime($field['y_condition']);
        $res['thisweek'] = AgentsStatisticsPlayerModel::model()->getGoldBytime($field['t_condition']);
        return $res;
    }

    //获取condition
    private function getCondition($name,$condition){
        $y_condition[$name][0] = array('EGT', $condition['y_start_time']);
        $y_condition[$name][1] = array('LT', $condition['y_end_time']);
        //本周的充值金额
        $t_condition[$name][0] = array('EGT', $condition['t_start_time']);
        $t_condition[$name][1] = array('LT', $condition['t_end_time']);

        $res['y_condition'] = $y_condition;
        $res['t_condition'] = $t_condition;
        return $res;
    }

    private function weekArray(){
        return array('周一','周二','周三','周四','周五','周六','周天');
    }

//change_money_money_type 1
//change_money_type 2
//change_money_my_tax
//change_money_time



}
