<?php

namespace app\admin\model;

use think\Model;

class NoticeModel extends Model
{
    public $table = 'dc_notice';


    /**
     * 静态调用
     * @param array $data
     * @param string $className
     * @return static active record model instance.
     */
    public static function model($data = [], $className = __CLASS__)
    {
        return parent::model($data, $className);
    }


    /**
     * @param $condition
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOne($condition)
    {
        $data = $this->where($condition)->find();
        return $data;
    }

    /**
     * @param $condition
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSelect($condition, $order = null)
    {
        return $this->where($condition)->order('notice_id desc')->select();

    }

    /**
     * @param $condition
     * @param $page
     * @param $pageSize
     * @param string $order
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($condition, $page, $pageSize, $order = '')
    {
        if (!empty($order)) {
            $this->order($order);
        }
        $this->alias('a')
            ->field('a.*, from_unixtime(a.notice_create_time) as n_create_time')
            ->page($page, $pageSize);
        if (isset($condition['keywords'])) {
            $this->where('notice_title', 'like', "%{$condition['keywords']}%");
        }
        unset($condition['keywords']);
        $list = collection($this->where($condition)->select())->toArray();
//        $list = collection($list)->toArray();
        $agent = new AgentInfoModel();
        foreach ($list as $key => $value) {
            switch ($value['notice_type']) {
                case 1:
                    $list[$key]['notice_type'] = '系统公告';
                    break;
                case 2:
                    $list[$key]['notice_type'] = '跑马灯公告';
                    break;
                case 3:
                    $list[$key]['notice_type'] = '后台公告';
                    break;
            }
            if ($value['notice_agent_id'] == 0) {
                $list[$key]['notice_agent_id'] = '全体对象';
            } else {
                $agents = $agent->where('agent_id', 'in', $value['notice_agent_id'])->column('agent_name');
                $list[$key]['notice_agent_id'] = implode(',', $agents);
            }
            if (empty($value['notice_start_id'])) {
                $list[$key]['notice_start_id'] = '';
            } else {
                $agents = $agent->where('agent_id', 'in', $value['notice_start_id'])->column('agent_name');
                $list[$key]['notice_start_id'] = implode(',', $agents);
            }
        }
        return $list;
    }

    /**
     * @param $condition
     * @return int|string
     */

    public function getCount($condition)
    {
        if (isset($condition['keywords'])) {
            $this->where('notice_title', 'like', "%{$condition['keywords']}%");
        }
        unset($condition['keywords']);
        return $this->where($condition)->count('*');
    }


    /**
     * @param $condition
     */
    public function getUpdate($condition, $data)
    {
        return $this->where($condition)->update($data);

    }

    /**
     * @param $notice_id
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getNotin($condition, $field = null)
    {
        $data = $this->field($field)->where($condition)->select();
        return $data;
    }

    /**
     * @param $condition
     * @return int|string
     */
    public function getCountNotIn($condition)
    {
        return $this->where($condition)->count('*');
    }




    public function getAgentList($condition, $page, $pageSize, $order='')
    {
        if(!empty($order)){
            $this->order($order);
        }
        $this->alias('a')
            ->page($page, $pageSize);
        if (isset($condition['keywords'])) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    'a.notice_title' => ['like', '%'.$condition['keywords'].'%'],

                ]);
            });
        }
        unset($condition['keywords']);
        $list = collection($this->where($condition)->select())->toArray();
        return $list;
    }

    public function getAgentCount($condition)
    {
        unset($condition['keywords']);
        return $this->where($condition)->count('*');
    }





















}
