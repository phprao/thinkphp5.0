<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/17
 * Time: 15:01
 */
namespace app\admin\model;

use app\common\model\Model;

/**
 * Class PlayerModel
 * @package app\api\model
 * @author ChangHai Zhan
 */
class PlayerModel extends Model
{
    /**
     * @var string
     */
    public $table = 'dc_player';
    /**
     * 玩家状态 封号
     */
    const PLAYER_STATUS_DISABLE = 0;
    /**
     * 玩家状态 正常
     */
    const PLAYER_STATUS_NORMAL = 1;

    public static $cancel_remark = '-----[cancel]-----';

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

    public function getInfo($condition)
    {
        $data = $this->where($condition)->find();
        return $data;
    }

    /**
     * 通用统计
     * @param $condition
     * @param string $count
     * @return int|string
     */
    public function getSumData($condition, $field = '')
    {
        if($field){
            return $this->where($condition)->sum($field);
        }
    }

    /**
     * 关联 代理账户
     * @return \think\model\relation\HasOne
     */
    public function playerAgentInfo()
    {
        return $this->hasOne('AgentInfoModel',  'agent_player_id', 'player_id');
    }

    /**
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getPlayerStatusTextAttr($value, $data)
    {
        $playerStatus = [
            self::PLAYER_STATUS_DISABLE => '封号',
            self::PLAYER_STATUS_NORMAL => '正常',
        ];
        if (isset($data['player_status'], $playerStatus[$data['player_status']])) {
            return $playerStatus[$data['player_status']];
        }
        return false;
    }

    public function updateData($condition,$data){
        $this->where($condition);
        return $this->isUpdate(true)->save($data);
    }

    /**
     * 我的代理玩家列表
     * @param null $condition
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    // public function getAgentPlayerList($condition = null)
    // {
    //     $this->alias('t');
    //     $this->join('dc_agent_info t1', 't.player_id = t1.agent_player_id');
    //     if (isset($condition->keyword)) {
    //         $this->where(function ($query) use ($condition) {
    //             $query->whereOr([
    //                 't.player_id' => $condition->keyword,
    //                 't.player_name' => ['like', '%' . $condition->keyword . '%']
    //             ]);
    //         });
    //     }
    //     $this->field([
    //         't.player_id',
    //         't.player_name',
    //         't.player_status',
    //         't.player_resigter_time',
    //     ]);
    //     if (isset($condition->agent_parentid)) {
    //         $this->where('t1.agent_parentid', $condition->agent_parentid);
    //     }
    //     $this->where('t1.agent_login_status', AgentInfoModel::AGENT_LOGIN_STATUS_NO);
    //     if (!isset($condition->pageSize)) {
    //         $condition->pageSize = 10;
    //     }
    //     return $this->paginate($condition->pageSize);
    // }

    /**
     * 我的代理玩家列表 关联写法
     * @param null $condition
     * @return \think\Paginator
     */
    // public function getAgentPlayerListJoin($condition = null)
    // {
    //     $hasWhere = [];
    //     $hasWhere['agent_login_status'] = AgentInfoModel::AGENT_LOGIN_STATUS_NO;
    //     if (isset($condition->agent_parentid)) {
    //         $hasWhere['agent_parentid'] = $condition->agent_parentid;
    //     }
    //     $relation = self::hasWhere('playerAgentInfo', $hasWhere, [
    //         'player_id',
    //         'player_name',
    //         'player_status',
    //         'player_resigter_time',
    //     ]);
    //     if (isset($condition->keyword)) {
    //         $relation->where(function ($query) use ($condition) {
    //             $query->whereOr([
    //                 'player_id' => $condition->keyword,
    //                 'player_name' => ['like', '%' . $condition->keyword . '%']
    //             ]);
    //         });
    //     }
    //     if (!isset($condition->pageSize)) {
    //         $condition->pageSize = 10;
    //     }
    //     return $relation->paginate($condition->pageSize);
    // }

    /**
     * 根据玩家id查找玩家信息
     * @return [type] [description]
     */
    public function getPlayerinfoById($playerId, $field = [], $condition = null)
    {
        if (is_array($playerId)) {
            $this->whereIn('player_id',$playerId);
            return $this->select();
        }
        $this->where('player_id',$playerId);
        if (isset($field) && is_array($field)) {
            $this->field($field);
        }
        return $this->find();
    }

    /**
     * 模糊搜索玩家信息
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function getPlayerinfoByLike($condition = null){
        $this->where(function ($query) use ($condition) {
            $query->whereOr([
                'player_id' => ['like', '%' . $condition->keyword . '%'],
                'url_decode(player_nickname)' => ['like', '%' . $condition->keyword . '%']
            ]);
        });
        if(isset($condition->size) && $condition->size){
            $data = $this->paginate($condition->size);
        }else{
            $data = $this->select(); 
        }
        
        return $data;
    }

    /**
     * 统计注册
     * @param $condition
     * @return int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTotalRegister($condition)
    {
        if (isset($condition->superAgentId)) {
            $sql = PromotersInfoModel::model()->getPlayerIdBySuperAgentIdSql($condition->superAgentId);
            $this->whereExp('player_id', 'IN (' . $sql . ')');
        }
        return $this->count();
    }


    /**
     * @param $playerid
     * @param null $condition
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPlayerInformation($playerid, $condition = null)
    {
        $this->where('player_id', 'in', $playerid);
//        $this->where('player_status', 1);
        $data = $this->select();

        return $data;
    }

    /**
     * @param null $condition
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPlayerBytime($condition = null)
    {
        $this->where($condition);
        $data = $this->count();
        return $data;
    }

}