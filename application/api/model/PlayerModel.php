<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/17
 * Time: 15:01
 */
namespace app\api\model;

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
    /**
     * 是否是游客注册 不是
     */
    const PLAYER_GUEST_NO = 0;
    /**
     * 是否是游客注册 是
     */
    const PLAYER_GUEST_YES = 1;
    /**
     * pcid 注册游客
     */
    const PLAYER_CHANNEL_PCID_ANDROID = 1;
    const PLAYER_CHANNEL_PCID_IOS = 2;
    /**
     * 注册渠道 安卓微信
     */
    const PLAYER_CHANNEL_ANDROID_WECHAT = 3;
    /**
     * 注册渠道 ios微信
     */
    const PLAYER_CHANNEL_IOS_WECHAT = 4;
    /**
     * H5微信注册的用户（微信推广）
     */
    const PLAYER_CHANNEL_H5_WECHAT = 5;
    /**
     * 注册送金币
     * @var
     */
    public static $registerAward = 30000;

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

    /**
     * 我的代理玩家列表
     * @param null $condition
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getAgentPlayerList($condition = null)
    {
        $this->alias('t');
        $this->join('dc_agent_info t1', 't.player_id = t1.agent_player_id');
        if (isset($condition->keyword)) {
            $this->where(function ($query) use ($condition) {
                $query->whereOr([
                    't.player_id'       => ['like', '%' . $condition->keyword . '%'],
                    'url_decode(t.player_nickname)' => ['like', '%' . $condition->keyword . '%'],
                ]);
            });
        }
        $this->field([
            't.player_id',
            't.player_nickname',
            't.player_status',
            't.player_resigter_time',
        ]);
        if (isset($condition->agent_parentid)) {
            $this->where('t1.agent_parentid', $condition->agent_parentid);
        }
        // $this->where('t1.agent_login_status', AgentInfoModel::AGENT_LOGIN_STATUS_NO);
        if (!isset($condition->pageSize)) {
            $condition->pageSize = 10;
        }
        return $this->paginate($condition->pageSize);
    }

    /**
     * 根据玩家id查找玩家信息
     * @return [type] [description]
     */
    public function getPlayerinfoById($playerId, $condition = null)
    {
        if (is_array($playerId)) {
            $this->whereIn('player_id',$playerId);
            return $this->select();
        }
        $this->where('player_id',$playerId);
        return $this->find();
    }

    /**
     * @param $player_id
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPlayerinfo($player_id)
    {

        $this->where('player_id',$player_id);
        return $this->find();

    }

    public function getPlayerByCondition($condition,$field = []){
        $this->where(function ($query) use ($condition) {
            $query->whereOr([
                'player_id' => ['like', '%' . $condition->keyword . '%'],
                'url_decode(player_nickname)' => ['like', '%' . $condition->keyword . '%']
            ]);
        });
        if(isset($condition->agent_id) && $condition->agent_id){

        }
        if(isset($condition->size) && $condition->size){
            $data = $this->paginate($condition->size);
        }else{
            $data = $this->select(); 
        }
        if(!empty($field)){
            $this->field($field);
        }
        
        return $data;
    }
}