<?php

namespace app\admin\controller\v1;

use app\common\components\Helper;
use app\admin\controller\Controller;
use app\admin\model\MailQueueModel;
use app\admin\model\MailModel;
use app\admin\model\PlayerModel;
use app\admin\model\AgentInfoModel;

class Mail extends Controller
{
	protected $size = 10 ;
    protected $error_code = 0;
    protected $error_msg = '';
    protected $mail_title_limit = 20;
    protected $mail_content_limit = 200;
    /**
     * 初始化操作
     * @access protected
     */
    protected function _initialize()
    {
        parent::_initialize();
        $userInfo = $this->isLogin($this->token);
        if(isset($userInfo['agentInfo'])){
            $this->channel_id = $userInfo['agentInfo']['agent_id'];
        }else{
            $this->channel_id = 0;
        }
    }

    protected function initRequestParam(){
        $input = array(
			'keyword'    =>(string)$this->request->get('keyword'),// 玩家id
			'title'      =>(string)$this->request->get('title'),
			'start'      =>(string)$this->request->get('start'),
			'end'        =>(string)$this->request->get('end'),
			'star_id'    =>(int)$this->request->get('star_id'),
			'channel_id' =>(int)$this->request->get('channel_id'),
			'page'       =>(int)$this->request->get('page'),
			'size'       =>(int)$this->request->get('size'),
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

        if($input['title'] !== ''){
            $filter['title'] = $input['title'];
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

        if($input['channel_id']){
        	if($input['star_id']){
        		$filter['star_id'] = $input['star_id'];
        	}else{
        		$filter['channel_id'] = $input['channel_id'];
        	}
        }
        
        if ($this->error_code) {
            return false;
        } else {
            $filter = (object)$filter;
            return $filter;
        }
        
    }

    public function MailList(){
    	$condition = $this->initRequestParam();
        if ($condition === false) {
            return $this->sendError($this->error_code, $this->error_msg);
        }

        $list = MailModel::model()->getList($condition, [
        	'mail_create_date','mail_title','mail_receiver_id','mail_star_id','mail_channel_id', 'mail_status','mail_content']);
        foreach($list->items() as $item){
        	if($item->mail_status == 1){
        		$item->mail_status = '未读';
        	}
        	if($item->mail_status == 2){
        		$item->mail_status = '已读';
        	}
            if($item->mail_status == 3){
                $item->mail_status = '删除';
            }
            $item->mail_star_name = '---';
            $item->mail_channel_name = '---';
        	$info = AgentInfoModel::model()->getInfo(['agent_id'=>$item->mail_star_id]);
        	if($info){
        		$item->mail_star_id = $info['agent_player_id'];
                $player = PlayerModel::model()->getInfo(['player_id'=>$info['agent_player_id']]);
                if($player){
                    $item->mail_star_name = urldecode($player['player_nickname']);
                }
        	}else{
                $item->mail_star_id = '---';
            }
            $info2 = AgentInfoModel::model()->getInfo(['agent_id'=>$item->mail_channel_id]);
            if($info2){
                $item->mail_channel_name = $info2['agent_name'];
            }
        }
        $filterDate = $this->addTime($condition);
        return $this->sendSuccess(['list' => $list, 'date' => $filterDate]);
    }

    public function sendMail(){
    	$data = $this->requestData();
    	if(!$data){
    		return $this->sendError($this->error_code, $this->error_msg);
    	}
    	
    	$re = MailQueueModel::model()->insertOne($data);
    	if($re){
    		return $this->sendSuccess();
    	}else{
    		return $this->sendError(10001, '写入失败');
    	}
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

    	if(!Helper::checkString($input['mail_content'], 2) || mb_strlen($input['mail_content']) > $this->mail_content_limit){
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