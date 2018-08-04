<?php
/**
 * User: Administrator
 * Date: 2018/3/30
 * Time: 10:46
 * @author quekaihua
 */

namespace app\admin\controller\v1;

use app\admin\controller\Controller;
use app\admin\model\FeedbackModel;
use app\admin\model\PromotersInfoModel;
use app\admin\model\AgentInfoModel;

/**
 * @controllerName 金币充值记录
 */
class Feedback extends Controller
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

    public function index()
    {
        $validation = $this->validate($this->request->param(), self::$rule);
        if ($validation !== true) {
            return $this->sendError(3001, $validation);
        }
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('pageSize', 10);
        $keywords = $this->request->get('keywords', null);
        if (!empty($keywords)) {
            $condition['keywords'] = $keywords;
        }
        $date = $this->getSearchDate();

        $sTime = strtotime($this->request->get('s_time', null)) ? : strtotime($date['startDate']);
        $eTime = strtotime($this->request->get('e_time', null)) ? : strtotime($date['endDate']);

        $condition['feedback_create_time'] = [['>=', $sTime], ['<=', $eTime + 24 * 3600], 'and'];
        $model = new FeedbackModel();
        $feedback = $model->getList($condition, $page, $pageSize, 'feedback_id desc');
        foreach ($feedback as $key => $item) {
            $feedback[$key]['feedback_content'] = urldecode($item['feedback_content']);

             //所属星级推广员
            $promoters = PromotersInfoModel::model()->getParentIdByPlayerId($item['feedback_player_id']);
            // print_r($promoters);
            //判断用户是否是星级推广员
            $agent_info = AgentInfoModel::model()->getInfo(array('agent_player_id'=>$promoters['promoters_parent_id']));
            $p_nickname = urldecode($promoters['player_nickname']);
            if($agent_info['agent_login_status'] == 1){
                $feedback[$key]['promoters_nickname'] = $p_nickname.'('.$promoters['promoters_parent_id'].')';
                $feedback[$key]['promoters_playerid'] = $promoters['promoters_parent_id'];
            }else{
                $feedback[$key]['promoters_nickname'] = '--';
            }
            $agent_pid = AgentInfoModel::model()->getAgentId(array('agent_player_id'=>$item['feedback_player_id']));
            // $playerid = $item['feedback_player_id'];
            // $channelid['agent_id'] = $agent_info['agent_top_agentid'];
            $thevalue_channel = AgentInfoModel::model()->getAgentId(array('agent_id'=>$agent_pid['agent_top_agentid']));
            // print_r($thevalue_channel); die;
            // $channel[$key][$playerid] = $thevalue_channel['agent_name'];
            $feedback[$key]['channel'] = $thevalue_channel['agent_name']?$thevalue_channel['agent_name']:'--';
            // $feedback[$key]['channel'] = '';
            // if (isset($channel[$playerid])) {
            //     $feedback[$key]['channel'] = $channel[$playerid];
            // }
            // print_r($feedback);die;
        }
        $count = $model->getCount($condition);
        return $this->sendSuccess([
            'total' => $count,
            'per_page' => $pageSize,
            'current_page' => $page,
            'last_page' => ceil($count / $pageSize),
//            'total_amount' => $statistcs?$statistcs / 100 : 0,
            'date' => $date,
            'list' => $feedback,
        ]);
    }

    protected function getSearchDate()
    {
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        return ['startDate' => $startDate, 'endDate' => $endDate];
    }
}
