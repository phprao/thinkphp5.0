<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 10:52
 */

namespace app\admin\model;

use app\common\model\Model;

/**
 * 代理模型
 * Class AgentInfoModel
 * @package app\admin\model
 * @author ChangHai Zhan
 */
class AgentInfoModel extends Model
{
    public $table_player = 'dc_view_player_info';

    /**
     * @var string
     */
    public $table = 'dc_agent_info';
    /**
     * 是否是代理人 否
     */
    const  AGENT_LOGIN_STATUS_NO = 0;
    /**
     *是否是代理人 是
     */
    const  AGENT_LOGIN_STATUS_YES = 1;
    /**
     * 代理人状态 否
     */
    const  AGENT_STATUS_NO = 0;
    /**
     *代理人状态 是
     */
    const  AGENT_STATUS_YES = 1;

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
     * 获取接收参数
     * @param $scenario
     * @return array|mixed
     */
    public function getSafe($scenario)
    {
        $message = [
            'login' => [
            ],
        ];
        return isset($message[$scenario]) ? $message[$scenario] : [];
    }

    /**
     * 获取登陆特代数据
     * @param $uid
     * @param string $safe
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLoginAgentByUid($uid, $safe = 'login')
    {
        $this->field($this->getSafe($safe));
        $this->where('agent_user_id', $uid);
        return $this->find();
    }

    /**
     * @param $userID
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAgentId($userID)
    {
        $data = $this->where($userID)->find();
        return $data;
    }

    /**
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getInfo($condition)
    {
        $data = $this->where($condition)->find();
        return $data;
    }

    public function getCount($condition){
        $data = $this->where($condition)->count();
        return $data;
    }

    /**
     * 代理商的信息
     * @param $id
     * @param array $field
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAgentById($id, $field = [], $condition = null)
    {
        if(!empty($field)){
          $this->field($field);  
        }
        if ($condition === null) {
            $this->where('agent_login_status', self::AGENT_LOGIN_STATUS_YES);
        }
        if (is_array($id)) {
            $this->whereIn('agent_id', $id);
            return $this->select();
        } else {
            $this->where('agent_id', $id);
            return $this->find();
        }
    }

    /**
     * 获取信息
     * @param $agentId
     * @param array $field
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getByAgentId($agentId, $field = [], $condition = null)
    {
        $this->field($field);
        if (isset($condition->agent_login_status)) {
            $this->where('agent_login_status', $condition->agent_login_status);
        }
        if (is_array($agentId)) {
            $this->whereIn('agent_id', $agentId);
            return $this->select();
        } else {
            $this->where('agent_id', $agentId);
            return $this->find();
        }
    }

    /**
     * 根据星级推广员agentid获取其playerid,nickname
     * @param  [type] $agentId   [description]
     * @param  [type] $field     [description]
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function getStarAgentPlayerInfoById($agentId, $field = [], $condition = null)
    {
        $this->alias('a');
        $this->join($this->table_player . ' p', 'p.player_id = a.agent_player_id');
        $this->field($field);
        if (is_array($agentId)) {
            $this->whereIn('agent_id', $agentId);
            return $this->select();
        } else {
            $this->where('agent_id', $agentId);
            return $this->find();
        }
    }

    /**
     * 获取信息
     * @param $agentPlayerId
     * @param array $field
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getByAgentPlayerId($agentPlayerId, $field = [],$where = null)
    {
        if(!empty($field)){
            $this->field($field);
        }
        if (is_array($agentPlayerId)) {
            $this->whereIn('agent_player_id', $agentPlayerId);
            if($where){
                $this->where($where);
            }
            return $this->select();
        } else {
            $this->where('agent_player_id', $agentPlayerId);
            if($where){
                $this->where($where);
            }
            return $this->find();
        }
    }

    /**
     * 是否是我的下级代理
     * @param $agent_id
     * @param $agent_parentid
     * @param array $field
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isLowerAgent($agent_id, $agent_parentid, $field = [])
    {
        $this->field($field);
        $this->where('agent_id', $agent_id);
        $this->where('agent_parentid', $agent_parentid);
        $this->where('agent_login_status', self::AGENT_LOGIN_STATUS_YES);
        return $this->find();
    }

    /**
     * 玩家列表
     * @return [type] [description]
     */
    public function getPlayerList($condition = null,$field = []){
        if(!empty($field)){
           $this->field($field); 
        }
        if(isset($condition->mode) && $condition->mode == 1){
            $this->where('agent_parentid', $condition->from_agent_id);
        }
        if(isset($condition->mode) && $condition->mode == 2){
            if(isset($condition->channel_id)){
                $this->where('agent_top_agentid', $condition->channel_id);
            }
            if(isset($condition->promote_id)){
                $this->where('agent_parentid', $condition->promote_id);
            }
        }
        if(isset($condition->mode) && $condition->mode == 3){
            $this->where('agent_partner_id', '>', 0);
        }

        if(isset($condition->keyword) && $condition->keyword){
            $this->whereIn('agent_player_id', $condition->keyword);
        }

        if(isset($condition->start)){
            $this->where('agent_createtime','>=',$condition->start);
        }
        if(isset($condition->start)){
            $this->where('agent_createtime','<',$condition->end);
        }

        if(isset($condition->player_type) && $condition->player_type == 1){
            $this->where('agent_login_status', self::AGENT_LOGIN_STATUS_YES);
        }
        if(isset($condition->player_type) && $condition->player_type == 2){
            $this->where('agent_login_status', self::AGENT_LOGIN_STATUS_NO);
        }
        
        $this->where('agent_user_id', 0);
        $this->where('agent_player_id', '>',0);
        $this->order('agent_createtime desc');
        
        return $this->paginate($condition->size);
    }

    /**
     * @param null $condition
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    // public function getLowerAgentList($condition = null)
    // {
    //     $this->alias('t');
    //     $this->join('dc_agent_account_info t1', 't.agent_id = t1.agent_account_agent_id');
    //     if (isset($condition->keyword)) {
    //         $this->where(function ($query) use ($condition) {
    //             $query->whereOr([
    //                 't1.agent_account_mobile' => ['like', '%' . $condition->keyword . '%'],
    //                 't.agent_name' => ['like', '%' . $condition->keyword . '%']
    //             ]);
    //         });
    //     }
    //     $this->field([
    //         't1.agent_account_mobile',
    //         't.agent_id',
    //         't.agent_promote_count',
    //         't.agent_remark',
    //         't.agent_parentid',
    //         't.agent_name',
    //     ]);
    //     if (isset($condition->agent_parentid)) {
    //         if (is_string($condition->agent_parentid)) {
    //             $this->whereExp('t.agent_parentid', 'IN (' . $condition->agent_parentid . ')');
    //         } else {
    //             $this->where('t.agent_parentid', $condition->agent_parentid);
    //         }
    //     }
    //     $this->where('t.agent_login_status', self::AGENT_LOGIN_STATUS_YES);
    //     if (!isset($condition->pageSize)) {
    //         $condition->pageSize = 10;
    //     }
    //     return $this->paginate($condition->pageSize);
    // }

    /**
     * 获取下一级代理SQl
     * @param $agent_id
     * @return false|int|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLowerAgentIdSql($agent_id)
    {
        $this->whereIn('agent_parentid', (array)$agent_id);
        $this->where('agent_login_status', self::AGENT_LOGIN_STATUS_YES);
        $this->field('agent_id');
        return $this->select(false);
    }

    /**
     * 我的下级代理数量
     * @param $agent_id
     * @param $lower = 1 下下级 不支持多代理查询
     * @return false|int|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLowerAgentCount($agent_id, $lower = 1)
    {
        if ($lower == 1) {
            $this->whereIn('agent_parentid', (array)$agent_id);
        } elseif ($lower == 2) {
            if (is_array($agent_id)) {
                return false;
            }
            $sql = self::model()->getLowerAgentIdSql($agent_id);
            $this->whereExp('agent_parentid', 'IN (' . $sql . ')');
        }
        $this->where('agent_login_status', self::AGENT_LOGIN_STATUS_YES);
        if (is_array($agent_id)) {
            $this->field('count(*) as total, agent_parentid');
            $this->group('agent_parentid');
            return $this->cache(60)->select();
        }
        return $this->cache(30)->count();
    }

    /**
     * 更新代理备注
     * @param $id
     * @param $agentRemark
     * @return false|int
     */
    public function updateAgentRemark($id, $agentRemark)
    {
        $this->where('agent_id', $id);
        return $this->isUpdate(true)->save(['agent_remark' => $agentRemark]);
    }

    public function updateAgentInfo($data)
    {
        $this->where('agent_id', $data['agent_id']);
        $info = [];
        if(isset($data['agent_remark'])){
            $info['agent_remark'] = $data['agent_remark'];
        }
        if(isset($data['agent_name'])){
            $info['agent_name'] = $data['agent_name'];
        }
        if(empty($info)){
            return true;
        }else{
            return $this->isUpdate(true)->save($info);
        }
    }

    /**
     * 更新玩家身份状态
     * @param $agentId
     * @param int $status
     * @return false|int
     */
    public function updateAgentLoginStatusById($agentId, $status = self::AGENT_LOGIN_STATUS_YES)
    {
        $this->where('agent_id', $agentId);
        return $this->isUpdate(true)->save(['agent_login_status' => $status]);
    }

    /**
     * 获取所有特代的信息
     * @param  $condition
     * @return false|int|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAllSuperAgentInfo($condition = null)
    {
        $this->where('agent_parentid', 0);
        $this->where('agent_level', 1);
        $this->order('agent_createtime desc');
        if($condition){
            if(isset($condition->start) && $condition->start){
                $this->where('agent_createtime','>=', $condition->start);
            }
            if(isset($condition->end) && $condition->end){
                $this->where('agent_createtime','<', $condition->end);
            }
            if (isset($condition->keyword) && !is_array($condition->keyword)) {
                $this->where(function ($query) use ($condition) {
                    $query->whereOr([
                        'agent_id' =>   ['like', '%' . $condition->keyword . '%'],
                        'agent_name' => ['like', '%' . $condition->keyword . '%']
                    ]);
                });
            }
            if (isset($condition->keyword) && is_array($condition->keyword)) {
                $this->whereIn('agent_id',$condition->keyword);
            }
            if(isset($condition->field)){
                $this->field($condition->field);
            }
            $data = $this->paginate($condition->size);
        }else{
            $data = $this->select();
        }
        
        return $data;
    }
    /**
     * 统计渠道下的玩家0 、星级推广人数1
     * @param  [type]  $condition [description]
     * @param  integer $type      [description]
     * @return [type]             [description]
     */
    public function getChannelPlayerCount($condition = null,$type = 0)
    {
        $this->where('agent_top_agentid', $condition->channel_id);
        if($type == 1){
            $this->where('agent_login_status', 1);
        }
        return $this->count();
    }

    /**
     * 根据关键字查找特代（渠道）信息
     * @param $condition
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getSuperAgentInfoByKeyword($condition)
    {
        $this->where('agent_parentid', 0);
        $this->where('agent_level', 1);
        if (isset($condition->channel)) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    'agent_id' =>   ['like', '%' . $condition->channel . '%'],
                    'agent_name' => ['like', '%' . $condition->channel . '%']
                ]);
            });
        }
        if(isset($condition->size)){
            return $this->paginate($condition->size);
        }else{
            return $this->select();
        }
    }

    /**
     * 根据关键字查找星级推广员信息
     * @param $condition
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getStarAgentInfoByCondition($condition,$field = [])
    {
        if(isset($condition->mode)){
            if($condition->mode == 1){
                $this->whereIn('agent_player_id',$condition->keyword);
            }
            if($condition->mode == 2){
                $this->whereIn('agent_parentid',$condition->keyword);
            }
            if($condition->mode == 3){
                $this->whereIn('agent_p_parentid',$condition->keyword);
            }
            if($condition->mode == 4){
                $this->whereIn('agent_top_agentid',$condition->keyword);
            }
            if($condition->mode == 5){
                $this->whereIn('agent_player_id',$condition->keyword);
            }
        }
        if(isset($condition->from_channel_id) && $condition->from_channel_id){
            $this->where('agent_top_agentid',$condition->from_channel_id);
            if((!isset($condition->mode) || !$condition->mode) && isset($condition->keyword) && $condition->keyword){
                $this->whereIn('agent_player_id',$condition->keyword);
            }
        }
        if(isset($condition->start)){
            $this->where('agent_star_time','>=',$condition->start);
        }
        if(isset($condition->end)){
            $this->where('agent_star_time','<',$condition->end);
        }
        if(isset($condition->channel_id)){
            $this->where('agent_top_agentid',$condition->channel_id);
        }
        $this->where('agent_login_status',self::AGENT_LOGIN_STATUS_YES);
        $this->where('agent_user_id',0);
        $this->where('agent_level','>',1);
        if(!empty($field)){
            $this->field($field);
        }
        $this->order('agent_star_time desc');
        if(isset($condition->size)){
            return $this->paginate($condition->size);
        }else{
            return $this->select();
        }
    }
    /**
     * 根据渠道名称查找信息
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function getSuperAgentByName($name)
    {
        $this->where('agent_parentid', 0);
        $this->where('agent_name', $name);
        return $this->select();
    }

    /**
     * @param $condition
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAgentinfoList($condition)
    {
        $data = $this->where($condition)->select();

        return $data;
    }

    /**
     * 通用统计
     * @param $condition
     * @param string $count
     * @return int|string
     */
    public function getCountAgent($condition, $count = '*')
    {
        return $this->where($condition)->count($count);
    }

    /**
     * 获取渠道商下的星级代理
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function getStarAgentListByTopId($condition,$star = true)
    {
        $this->alias('a');
        $this->join($this->table_player . ' p', 'p.player_id = a.agent_player_id');
        $this->where('a.agent_top_agentid', $condition);
        if($star){
            $this->where('a.agent_login_status', self::AGENT_LOGIN_STATUS_YES);
        }
        $this->field([
            'p.player_id',
            'a.agent_id',
            'p.player_nickname'
        ]);

        return $this->select();
    }

    /**
     * 校验该推广员是否属于该渠道
     * @param  [type]  $condition [description]
     * @return boolean            [description]
     */
    public function isBelongsToSuperAgent($condition)
    {
        $this->where('agent_player_id', $condition->starid);
        $this->where('agent_top_agentid', $condition->channel);
        $this->where('agent_login_status', self::AGENT_LOGIN_STATUS_YES);
        $re = $this->find();
        return $re;
    }


    public function getTpromoteEarnings($condition = null,$page = 1, $pageSize = 6)
    {
        $this->alias('t');
        $this->join('dc_player t1', 't.agent_player_id = t1.player_id');

        if (isset($condition['keyword'])) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    'url_decode(t1.player_nickname)' => ['like', '%' . $condition['keyword'] . '%'],
                    't.agent_player_id' => ['like', '%' . $condition['keyword'] . '%']
                ]);
            });
        }
        unset($condition['keyword']);
        $records = $this
            ->where($condition)
            ->page($page, $pageSize)
            ->order('agent_id desc')
            ->select();
        $records = collection($records)->toArray();

        return $records;

    }


    public function getTpromoteEarningsCount($condition)
    {
        $this->alias('t');
        $this->join('dc_player t1', 't.agent_player_id = t1.player_id');
        if (isset($condition['keyword'])) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    'url_decode(t1.player_nickname)' => ['like', '%' . $condition['keyword'] . '%'],
                    't.agent_player_id' => ['like', '%' . $condition['keyword'] . '%']
                ]);
            });
        }
        unset($condition['keyword']);
        return $this->where($condition)->count('*');
    }

    public function getMaxChannelId(){
        $this->field(['max(agent_id) as maxid']);
        $this->where(['agent_player_id'=>0,'agent_parentid'=>0,'agent_level'=>1]);
        $re = $this->find();
        return $re['maxid'];
    }

    /**
     * 新增渠道信息
     * @param [type] $info [description]
     */
    public function addChannel($info)
    {
        $id = $this->getMaxChannelId();
        $this->data([
            'agent_id'           =>$id + 1 ,
            'agent_user_id'      =>$info['agent_user_id'],
            'agent_name'         =>$info['agent_name'],
            'agent_remark'       =>$info['agent_remark'],
            'agent_level'        =>1,
            'agent_partner_id'   =>isset($info['agent_partner_id']) ? $info['agent_partner_id'] : 0,
            'agent_login_status' =>1,
            'agent_createtime'   =>time()
        ]);
        $this->save();
        return $this->agent_id;
    }

}