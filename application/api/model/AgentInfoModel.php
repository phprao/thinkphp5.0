<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 10:52
 */
namespace app\api\model;

use app\common\model\Model;

/**
 * 代理模型
 * Class AgentInfoModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class AgentInfoModel extends Model
{
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
     * 关联 玩家
     * @return \think\model\relation\BelongsTo
     */
    public function agentInfoPlayer()
    {
        return $this->belongsTo('PlayerModel', 'agent_player_id', 'player_id');
    }

    /**
     * 关联 玩家信息
     * @return \think\model\relation\BelongsTo
     */
    public function agentInfoPlayerInfo()
    {
        return $this->belongsTo('PlayerInfoModel', 'agent_player_id', 'player_id');
    }

    /**
     * 关联 代理账户
     * @return \think\model\relation\HasOne
     */
    public function agentInfoAgentAccountInfo()
    {
        return $this->hasOne('AgentAccountInfoModel', 'agent_id', 'agent_account_agent_id');
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
     * 登陆
     * @param $playerId
     * @param string $safe
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLoginByPlayerId($playerId, $safe = 'login')
    {
        $this->field($this->getSafe($safe));
        $this->where('agent_player_id', $playerId);
        return $this->find();
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
        $this->field($field);
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
     * 获取信息
     * @param $agentPlayerId
     * @param array $field
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getByAgentPlayerId($agentPlayerId, $field = [])
    {
        $this->field($field);
        if (is_array($agentPlayerId)) {
            $this->whereIn('agent_player_id', $agentPlayerId);
            return $this->select();
        } else {
            $this->where('agent_player_id', $agentPlayerId);
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
     * @param null $condition
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getLowerAgentList($condition = null)
    {
        $this->alias('t');
        $this->join('dc_player t1', 't.agent_player_id = t1.player_id');
        if (isset($condition->keyword)) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    't1.player_id'       => ['like', '%' . $condition->keyword . '%'],
                    'url_decode(t1.player_nickname)' => ['like', '%' . $condition->keyword . '%'],
                    't1.player_phone'    => ['like', '%' . $condition->keyword . '%']
                ]);
            });
        }
        $this->field([
            't.agent_id',
            't1.player_id',
            't.agent_promote_count',
            't.agent_remark',
            't.agent_parentid',
            't1.player_nickname',
        ]);
        if (isset($condition->agent_parentid)) {
            if (is_string($condition->agent_parentid)) {
                $this->whereExp('t.agent_parentid',  'IN (' .$condition->agent_parentid . ')');
            } else {
                $this->where('t.agent_parentid', $condition->agent_parentid);
            }
        }
        if(isset($condition->agent_p_parentid)){
            $this->where('t.agent_p_parentid', $condition->agent_p_parentid);
        }
        $this->where('t.agent_login_status', self::AGENT_LOGIN_STATUS_YES);
        if (!isset($condition->pageSize)) {
            $condition->pageSize = 10;
        }
        return $this->paginate($condition->pageSize);
    }

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
            $this->whereExp('agent_parentid',  'IN (' . $sql . ')');
        }
        $this->where('agent_login_status', self::AGENT_LOGIN_STATUS_YES);
        if (is_array($agent_id)) {
            $this->field('count(*) as total, agent_parentid');
            $this->group('agent_parentid');
            return $this->select();
        }
        return $this->count();
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

    /**
     * @param $condition
     * @param $count
     * @return int|string
     */
    public function getAgentCount($condition,$count ='*')
    {
        $data = $this->where($condition)->count($count);

        return $data;
    }


    /**
     * @param $condition
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function gte_Aget_ParentidList($condition)
    {
        $data = $this->where($condition)->select();

        return $data;

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
     * @param $agentId
     * @param int $number
     * @return int|true
     * @throws \think\Exception
     */
    public function updateAgentPromoteCount($agentId, $number = 1)
    {
        $w = [
            'agent_id'        =>$agentId,
            'agent_level'     =>['>', 1]
        ];
        return $this->where($w)->setInc('agent_promote_count', $number);
    }

    public function getAgentInfoByCondition($condition,$field = []){
        if(isset($condition->keyword) && $condition->keyword){
            $this->where('agent_player_id', $condition->keyword);
        }
        if(isset($condition->agent_parentid) && $condition->agent_parentid){
            $this->where('agent_parentid', $condition->agent_parentid);
        }
        if(isset($condition->agent_p_parentid) && $condition->agent_p_parentid){
            $this->where('agent_p_parentid', $condition->agent_p_parentid);
        }
        if(isset($condition->is_login)){
            $this->where('agent_login_status', $condition->is_login);
        }

        if(!empty($field)){
            $this->field($field);
        }

        if(isset($condition->size) && $condition->size){
            $data = $this->paginate($condition->size);
        }else{
            $data = $this->select(); 
        }
        
        return $data;
    }

    public function updateInfo($condition,$data){
        return $this->where($condition)->update($data);
    }

    public function getLoginAgentByUid($uid)
    {
        $this->where('agent_user_id', $uid);
        return $this->find();
    }
}