<?php

namespace app\admin\controller\v1;

use app\common\components\Helper;
use app\admin\controller\Controller;
use app\admin\model\ConfigModel;
use app\admin\model\PlayerModel;
use app\admin\model\DailyGiftLogModel;

class Activity extends Controller
{
	protected $size = 10 ;
    protected $error_code = 0;
    protected $error_msg = '';
    protected $mail_title_limit = 20;
    protected $mail_content_limit = 100;
    protected $activity = null;
    /**
     * 初始化操作
     * @access protected
     */
    protected function _initialize()
    {
        // parent::_initialize();
        // $userInfo = $this->isLogin($this->token);
        // if(isset($userInfo['agentInfo'])){
        //     $this->channel_id = $userInfo['agentInfo']['agent_id'];
        // }else{
        //     $this->channel_id = 0;
        // }

        $this->activity = [
            1=>'daily_gift',
            2=>'new_player_bonus',
            3=>'lucky_treasure',
        ];
    }

    protected function initRequestParam(){
        $input = array(
            'keyword'    =>(string)$this->request->get('keyword'),// 玩家id
            'start'      =>(string)$this->request->get('start'),
            'end'        =>(string)$this->request->get('end'),
            'page'       =>(int)$this->request->get('page'),
            'size'       =>(int)$this->request->get('size'),
            'config_id'  =>(int)$this->request->get('config_id'),
            'channel_id' =>(int)$this->request->get('channel_id'),
            'star_id'    =>(int)$this->request->get('star_id'),
        );
        
        // $input = array(
        //     'keyword'   =>'',// 玩家ID  601709
        //     'page'      =>1,
        //     'size'      =>'10'
        // );
        
        $filter = array();

        $start = strtotime($input['start']);
        $end = strtotime($input['end']);
        $time = time();
        $timestamp_day = strtotime(date('Y-m-d', $time));

        if ($start && $end) {
            if ($start > $end) {
                $this->setCode(10001, '请选择合适的时间区间');
                return false;
            }
            
            $start = strtotime(date('Y-m-d', $start));
            $end = strtotime(date('Y-m-d', $end));
            // 包含今日
            if ($start > $timestamp_day) {
                $this->setCode(10001, '请选择合适的时间区间');
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
                    $this->setCode(10001, '请选择合适的时间区间');
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

        if($input['config_id']){
            $filter['config_id'] = $input['config_id'];
        }else{
            return false;
        }

        if($input['channel_id']){
            $filter['channel_id'] = $input['channel_id'];
        }

        if($input['star_id']){
            $filter['star_id'] = $input['star_id'];
        }
        
        $filter = (object)$filter;
        return $filter;
        
    }

    public function ActivityList(){
        $filter = array();
        $keyword = (string)$this->request->get('keyword');
        $page    = (int)$this->request->get('page');
        $size    = (int)$this->request->get('size');

        if($keyword){
            $filter['keyword'] = $keyword;
        }
        if($page){
            $filter['page'] = $page;
        }else{
            $filter['page'] = 1;
        }
        if($size){
            $filter['size'] = $size;
        }else{
            $filter['size'] = $this->size;
        }

        $filter = (object)$filter;

        $list = ConfigModel::model()->getListByCondition($this->activity, $filter, [
            'config_id','config_desc','config_name','config_status','config_start_time','config_end_time']);
        foreach($list->items() as $item){
        	if($item->config_start_time){
        		$item->config_start_time = date('Y-m-d H:i:s', $item->config_start_time);
        	}
        	if($item->config_end_time){
                $item->config_end_time = date('Y-m-d H:i:s', $item->config_end_time);
            }
        }

        return $this->sendSuccess(['list' => $list]);
    }

    public function giftList(){
    	$condition = $this->initRequestParam();
        $config = ConfigModel::model()->getFint(['config_id'=>$condition->config_id]);
        if(!$config){
            return $this->sendError(10000, 'config_id参数错误');
        }
        $config['config_config'] = json_decode($config['config_config'], true);
        if(array_search($config['config_name'], $this->activity) == 1){
            $list = $this->getActivityOne($condition, $config);
        }

        $filterDate = $this->addTime($condition);
        return $this->sendSuccess(['list' => $list, 'date' => $filterDate]);
    }

    protected function getActivityOne($condition, $config){
        $list = DailyGiftLogModel::model()->getList($condition, ['player_id','nickname','create_time','is_done','gift_id']);
        $config_data = $config['config_config']['gift'];
        if($list){
            foreach($list->items() as $item){
                $item->is_done = $item->is_done == 1 ? '已分享' : '未分享';
                $item->gift_name = $config_data[$item->gift_id - 1][3];
                $item->nickname = urldecode($item->nickname);
            }
        }        

        return $list;
    }

    protected function requestData(){
    	$input = array(
			'mail_type'          =>(int)$this->request->post('mail_type', 1),
			'mail_title'         =>(string)$this->request->post('mail_title', ''),// 20字
			'mail_send_time'     =>(string)$this->request->post('mail_send_time', ''),
			'mail_receiver_type' =>(int)$this->request->post('mail_receiver_type', 1),// 1-玩家，2-星级推广，3-渠道，4-所有
			'mail_receiver_id'   =>(string)$this->request->post('mail_receiver_id', ''),
			'use_model'          =>(int)$this->request->post('use_model', 0),
			'mail_model_id'      =>(int)$this->request->post('mail_model_id', 0),
			'mail_content'       =>(string)$this->request->post('mail_content', ''),// 100
        );

		// $input = array(
		// 	'mail_type'          =>1,
		// 	'mail_title'         =>'测试',// 20字
		// 	'mail_send_time'     =>'2018-7-20 1:00:00',
		// 	'mail_receiver_type' =>2,// 1-玩家，2-星级推广，3-渠道，4-所有
		// 	'mail_receiver_id'   =>'20',
		// 	'use_model'          =>0,
		// 	'mail_model_id'      =>0,
		// 	'mail_content'       =>'测试邮件',// 100
		// );

    	if(!Helper::checkString($input['mail_title'], 2) || mb_strlen($input['mail_title']) > $this->mail_title_limit){
    		$this->setCode(10000, '标题不能有空格，且在'.$this->mail_title_limit.'字以内');
    		return false;
    	}

    	if(!$input['mail_send_time'] || !strtotime($input['mail_send_time'])){
    		$this->setCode(10000, '请设置发送时间');
    		return false;
    	}else{
    		$input['mail_send_time'] = strtotime($input['mail_send_time']);
    	}

    	if(in_array($input['mail_receiver_type'], [1,2,3])){
    		if(!$input['mail_receiver_id'] || !Helper::checkString($input['mail_receiver_id'], 2)){
    			$this->setCode(10000, '收件人为必填，且不能有空格');
    			return false;
    		}
    		$player_arr = explode(',', $input['mail_receiver_id']);
    		if(!$player_arr || empty($player_arr)){
    			$this->setCode(10000, '收件人格式有误');
    			return false;
    		}
    		$player_arr = array_unique($player_arr);
    		$player_arr = array_filter($player_arr);
    		if(!$player_arr || empty($player_arr)){
    			$this->setCode(10000, '收件人格式有误');
    			return false;
    		}
    		$input['mail_receiver_id'] = join(',',$player_arr);
    	}

    	if(!Helper::checkString($input['mail_content'], 2) || mb_strlen($input['mail_content']) > 100){
    		$this->setCode(10000, '邮件内容不能有空格，且在'.$this->mail_content_limit.'字以内');
    		return false;
    	}

    	$input['mail_create_time'] = time();
    	$input['mail_create_date'] = date('Y-m-d H:i:s');
    	unset($input['use_model']);

    	return $input;

    }

    protected function setCode($code, $msg){
    	$this->error_code = $code;
    	$this->error_msg = $msg;
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

}