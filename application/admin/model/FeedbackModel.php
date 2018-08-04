<?php

namespace app\admin\model;

use think\Model;

class FeedbackModel extends Model
{
    public $table = 'dc_feedback';

    public function getList($condition, $page, $pageSize, $order='')
    {
        if(!empty($order)){
            $this->order($order);
        }
        $this->alias('a')
            ->field('a.*, url_decode(b.player_nickname) as player_nickname, from_unixtime(a.feedback_create_time) as f_create_time')
            ->join('dc_player b', 'a.feedback_player_id = b.player_id')
            ->page($page, $pageSize);
        if (isset($condition['keywords'])) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    'a.feedback_player_id' => ['like', '%'.$condition['keywords'].'%'],

                    'url_decode(b.player_nickname)' => ['like', '%'.$condition['keywords'].'%'],
                ]);
            });
        }
        unset($condition['keywords']);
        $list = collection($this->where($condition)->select())->toArray();
        return $list;
    }

    public function getCount($condition)
    {
        return $this->where($condition)->count('*');
    }
}
