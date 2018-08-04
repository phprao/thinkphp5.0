<?php

namespace app\admin\controller\v1;

use app\admin\model\NoticeModel;
use app\admin\controller\Controller;

class Notice extends Controller
{
    protected static $add_rule = [
//        'title' => 'require',
//        'content' => 'require',
//        'type' => 'require|between:1,3',
//        'agent_id' => 'require',

        ['title', 'require', '标题不能为空'],
        ['type', 'require|between:1,3', '请选择公告类型|请选择公告类型'],
        ['agent_id', 'require', '请选择发布对象'],
        ['content', 'require', '内容不能为空'],
    ];

    protected static $edit_rule = [
        'notice_id' => 'require|integer',
    ];

    protected static $get_rule = [
        'page' => 'integer|between:1,10000',
        'pageSize' => 'integer|between:1,20',
    ];

    protected function _initialize()
    {
        parent::_initialize();
        $this->isLogin($this->token);
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function index()
    {
        $agentid = '';
        $loginData = $this->isLogin($this->token);
        if (isset($loginData['agentInfo'])) {
            $agentid = $loginData['agentInfo']['agent_id'];
        }

        $validation = $this->validate($this->request->param(), self::$get_rule);
        if ($validation !== true) {
            return $this->sendError(3001, $validation);
        }
        $condition = array();
        $page = $this->request->get('page', 1);
        $pageSize = $this->request->get('pageSize', 10);
        $keywords = $this->request->get('keywords', null);
        if (!empty($keywords)) {
            $condition['keywords'] = $keywords;
        }
        if ($this->request->get('s_time', null) || $this->request->get('e_time', null)) {
            $sTime = strtotime($this->request->get('s_time', null));
            $eTime = strtotime($this->request->get('e_time', null));
            $condition['notice_create_time'] = [['>=', $sTime], ['<=', $eTime + 24 * 3600], 'and'];
        }

        if ($agentid) {
            $condition['notice_agent_id'] = $agentid;
            $condition['notice_source'] = 2;
        } else {
            $condition['notice_source'] = 1;
        }
        $condition['notice_status'] = 1;
        $model = new NoticeModel();
        $feedback = $model->getList($condition, $page, $pageSize, 'notice_id desc');
        $count = $model->getCount($condition);
        return $this->sendSuccess([
            'total' => $count,
            'per_page' => $pageSize,
            'current_page' => $page,
            'last_page' => ceil($count / $pageSize),
//            'date' => $date,
            'list' => $feedback,
        ]);


    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function noticeAdd()
    {
        $agentid = '';
        $loginData = $this->isLogin($this->request->post('token'));
        if (isset($loginData['agentInfo'])) {
            $agentid = $loginData['agentInfo']['agent_id'];
        }


        $validation = $this->validate($this->request->param(), self::$add_rule);
        if ($validation !== true) {
            return $this->sendError(3001, $validation);
        }
        if ($agentid) {
            $data['notice_source'] = 2;
            $data['notice_agent_id'] = $agentid;
        } else {
            $agent_id = $this->request->param('agent_id');
            $data['notice_source'] = 1;
            $data['notice_agent_id'] = is_array($agent_id) ? implode(',', $agent_id) : $agent_id;
        }
        $data['notice_title'] = $this->request->param('title');
        $data['notice_name'] = $this->request->param('title');
        $data['notice_content'] = $this->request->param('content');
        $data['notice_type'] = $this->request->param('type');

        $data['notice_create_time'] = time();
        $start_id = $this->request->param('start_id');
        if (!empty($start_id)) {
            $data['notice_start_id'] = is_array($start_id) ? implode(',', $start_id) : $start_id;
        } else {
            $data['notice_start_id'] = 0;
        }

        $notice = new NoticeModel();
        $res = $notice->insert($data);
        if ($res > 0) {
            return $this->sendSuccess($notice);
        } else {
            return $this->sendError(3002, '操作失败');
        }
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function noticeShow()
    {
        $this->isLogin($this->token);
        $notice_id = $this->request->param('notice_id');
        if (!$notice_id) {
            return $this->sendError(3002, '请选择公告！');
        }

        $NoticeModel = new NoticeModel();
        $res = $NoticeModel->getOne(array('notice_id' => $notice_id));

        if ($res) {
            return $this->sendSuccess($res);
        } else {
            return $this->sendError(3002, '没有数据！');
        }

    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function noticeEdit()
    {

        $validation = $this->validate($this->request->param(), self::$edit_rule);
        if ($validation !== true) {
            return $this->sendError(3001, $validation);
        }
        $where['notice_id'] = $this->request->param('notice_id');
        $notice = new NoticeModel();
        $notice = $notice::get($where);
        if (empty($notice)) {
            return $this->sendError(3003, '找不到相应的公告');
        }
        if (!empty($this->request->param('title'))) {
            $notice->notice_title = $this->request->param('title');
            $notice->notice_name = $this->request->param('title');
        }
        if (!empty($this->request->param('content'))) {
            $notice->notice_content = $this->request->param('content');
        }
        if (!empty($this->request->param('type'))) {
            $notice->notice_type = $this->request->param('type');
        }
        $agent_id = $this->request->param('agent_id');
        if (!empty($agent_id)) {
            $notice->notice_agent_id = is_array($agent_id) ? implode(',', $agent_id) : $agent_id;
        }
        $start_id = $this->request->param('start_id');
        if (!empty($start_id)) {
            $notice->notice_start_id = is_array($start_id) ? implode(',', $start_id) : $start_id;
        }
        $res = $notice->save();
        if ($res) {
            return $this->sendSuccess($notice);
        } else {
            return $this->sendError(3002, '操作失败');
        }
    }


    /**
     * 删除公告信息
     */
    public function noticeDelete()
    {
        $this->isLogin($this->token);
        $where['notice_id'] = $this->request->param('notice_id');
        if (!$where['notice_id']) {
            return $this->sendError(3002, '请选择删除的公告！');
        }
        $NoticeModel = new NoticeModel();
        $res = $NoticeModel->getUpdate($where, array('notice_status' => 0));
        if ($res) {
            return $this->sendSuccess($res);
        } else {
            return $this->sendError(3002, '操作失败');
        }

    }

    /**
     * @return array
     */
    protected function getSearchDate()
    {
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        return ['startDate' => $startDate, 'endDate' => $endDate];
    }


}
