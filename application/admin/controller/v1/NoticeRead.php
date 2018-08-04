<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:44
 * @author ChangHai Zhan
 */

namespace app\admin\controller\v1;

use app\admin\controller\Controller;
use app\admin\model\DemoModel;
use app\admin\model\NoticeModel;
use app\admin\model\NoticeReadModel;

/**
 * Class Demo
 * @package app\admin\controller\v1
 * @author
 */
class NoticeRead extends Controller
{

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $agentid = '';
        $loginData = $this->isLogin($this->token);
        if (isset($loginData['agentInfo'])) {
            $agentid = $loginData['agentInfo']['agent_id'];
        }
        $playerid = $this->request->get('playerid');
        if ($playerid) {
            $conditionsid['notice_read_player_id'] = $playerid;
        } else {
            $conditionsid['notice_read_agent_id'] = $agentid;
        }
        $NoticeModel = new NoticeModel();
        $notiction['notice_status'] = 1;
        $noticeDate = $NoticeModel->getSelect($notiction);
        $data = NoticeReadModel::model()->getSelect($conditionsid);
        $nid = [];
        if ($data) {
            foreach ($data as $key => $value) {
                $nid[] = $value['notice_read_notice_id'];
            }
        }
        $conditionnid['notice_status'] = 1;
        $conditionnid['notice_id'] = ['not in', $nid];
        $conditionnid['notice_type'] = 3;
        $conditionnid['notice_agent_id'] = ['in', "0,$agentid"];
        $datainfo = $NoticeModel->getNotin($conditionnid, 'notice_id');
        $nopid = [];
        if ($datainfo) {
            foreach ($datainfo as $va) {
                $nopid[] = $va['notice_id'];
            }
        }
        $cotice['notice_id'] = ['in', $nopid];
        $data_noctice = $NoticeModel->getSelect($cotice);
        $dataCount = $NoticeModel->getCountNotIn($conditionnid);
        if ($dataCount) {
            foreach ($data_noctice as $ke => $val) {
                $data_noctice[$ke]['notice_create_time'] = date('Y-m-d H:i:s', $val['notice_create_time']);
            }
        }

        $res = array(
            'datacount' => $dataCount,
            'datalist' => array_slice($data_noctice, 0, 3),
//            'datalist' => $data_noctice,
        );
        return $this->sendSuccess($res);

    }


    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function getList()
    {
        $agentid = '';
        $loginData = $this->isLogin($this->token);
        if (isset($loginData['agentInfo'])) {
            $agentid = $loginData['agentInfo']['agent_id'];
        }
        if (!$agentid) {
            return $this->sendError(3001, '没有token！');
        }
        $NoticeModel = new NoticeModel();
//        $playerid = $this->request->get('playerid');
//        if ($playerid) {
//            $conditionsid['notice_read_player_id'] = $playerid;
//        } else {
//            $conditionsid['notice_read_agent_id'] = $agentid;
//        }
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('pageSize', 10);
        $keywords = $this->request->get('keywords', null);
        if ($keywords) {
            $conditionnid['keywords'] = $keywords;
        }
        if ($this->request->get('s_time', null) || $this->request->get('e_time', null)) {
            $sTime = strtotime($this->request->get('s_time', null));
            $eTime = strtotime($this->request->get('e_time', null));
            $conditionnid['notice_create_time'] = [['>=', $sTime], ['<=', $eTime + 24 * 3600], 'and'];
        }

        $conditionnid['notice_status'] = 1;
        $conditionnid['notice_type'] = 3;
        $conditionnid['notice_agent_id'] = ['in', "0,$agentid"];
        $datainfo = $NoticeModel->getAgentList($conditionnid, $page, $pageSize, 'notice_id desc');
        $datacout = $NoticeModel->getAgentCount($conditionnid);
        if ($datainfo) {
            foreach ($datainfo as $key => $value) {
                $datainfo[$key]['notice_create_time'] = date('Y-m-d H:i:s', $value['notice_create_time']);
                $datainfo[$key]['notice_type'] = $this->getType($value['notice_type']);
                if($value['notice_agent_id'] == 0){
                    $datainfo[$key]['notice_agent_id'] = "全部对象";
                }else{
                   $datainfo[$key]['notice_agent_id'] = "自己"; 
                }
            }
        }

        $res = array(
            'total' => $datacout,
            'per_page' => $pageSize,
            'current_page' => $page,
            'last_page' => ceil($datacout / $pageSize),
            'datalist' => $datainfo,
        );
        return $this->sendSuccess($res);
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function tagRead()
    {
        $agentid = '';
        $loginData = $this->isLogin($this->token);
        if (isset($loginData['agentInfo'])) {
            $agentid = $loginData['agentInfo']['agent_id'];
        }
        if (!$agentid) {
            return $this->sendError(3001, '没有token！');
        }

        $playerid = $this->request->get('playerid');
        if ($playerid) {
            $conditionsid['notice_read_player_id'] = $playerid;
        } else {
            $conditionsid['notice_read_agent_id'] = $agentid;
        }
        $NoticeModel = new NoticeModel();
        $notiction['notice_status'] = 1;
        $notiction['notice_agent_id'] = ['in', "0,$agentid"];
        $noticeDate = $NoticeModel->getSelect($notiction);

        $data = NoticeReadModel::model()->getSelect($conditionsid);
        if (!$data) {
            foreach ($noticeDate as $key => $value) {
                $condition['notice_read_notice_id'] = $value['notice_id'];
                $condition['notice_read_player_id'] = $playerid ? $playerid : 0;
                $condition['notice_read_agent_id'] = $agentid ? $agentid : 0;
                $condition['notice_read_time'] = time();
                $res = NoticeReadModel::model()->insertWithdrawConfig($condition);
            }
        } else {
            foreach ($data as $key => $value) {
                $nid[] = $value['notice_read_notice_id'];
            }
            $conditionnid['notice_status'] = 1;
            $conditionnid['notice_id'] = ['not in', $nid];
            $conditionnid['notice_agent_id'] = ['in', "0,$agentid"];
            $datainfo = $NoticeModel->getNotin($conditionnid, 'notice_id');
//            $dataCount = $NoticeModel->getCountNotIn($conditionnid);
            $res = [];
            if ($datainfo) {
                foreach ($datainfo as $item) {
                    $condition['notice_read_notice_id'] = $item['notice_id'];
                    $condition['notice_read_player_id'] = $playerid ? $playerid : 0;
                    $condition['notice_read_agent_id'] = $agentid ? $agentid : 0;
                    $condition['notice_read_time'] = time();
                    $res = NoticeReadModel::model()->insertWithdrawConfig($condition);
                }

            }
        }

        return $this->sendSuccess($res);
    }

    public function getType($type){

        switch ($type) {
            case '1':
                $data = "系统公告";
                break;
            case '2':
                $data = "跑马灯公告";
                break;
            case '3':
                $data = "后台公告";
                break;
            default:
               $data = "未知公告";
                break;
        }
        return $data;
    }

}










