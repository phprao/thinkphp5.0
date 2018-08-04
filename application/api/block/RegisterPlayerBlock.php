<?php

namespace app\api\block;

use app\api\model\AgentAccountInfoModel;
use app\api\model\AgentInfoModel;
use app\api\model\PlayerInfoModel;
use app\api\model\AgentConfigModel;
use app\api\model\AgentConditionsModel;
use app\api\model\PlayerModel;
use app\api\model\PlayerStatisticalModel;
use app\api\model\PromotersInfoModel;
use app\api\model\AgentUpgradeRecordModel;
use app\api\model\AgentAccountInfoLogModel;
use think\Db;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/31
 * Time: 11:41
 * @author ChangHai Zhan
 */
class RegisterPlayerBlock
{
    /**
     * 代理
     * @var int
     */
    public $agentId = 1;
    /**
     * 默认特代
     * @var int
     */
    public $defaultAgentId = 1;
    /**
     * 默认头像
     * @var string
     */
    public $defaultPlayerHeaderImage = 'http://192.168.1.210/46.jpg';
    /**
     * 默认性别
     * @var int
     */
    public $defaultPlayerSex = 1;
    /**
     * 默认密码
     * @var int
     */
    public $defaultPlayerPassword = 123456;


    //获取代理为0的
    public $agent_login_status_no = 0;

    /**
     * 静态实例化
     * @param string $className
     * @return static active record model instance.
     */
    public static function block($className = __CLASS__)
    {
        return new $className();
    }

    /**
     * 设置推广代理人
     * @param int $agentId
     * @return $this
     */
    public function setParentAgentId($agentId = 1)
    {
        $this->agentId = $agentId;
        return $this;
    }

    /**
     * 设置初始密码
     * @param $playerPassword
     * @return $this
     */
    public function setPlayerPassword($playerPassword)
    {
        $this->defaultPlayerPassword = $playerPassword;
        return $this;
    }

    /**
     * 微信注册
     * @param $playerPcid
     * @param $unionid
     * @param $nickname
     * @param $headimgurl
     * @param $sex
     * @param int $playerChannel
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function wechat($playerPcid, $unionid, $nickname, $headimgurl, $sex, $param = [], $playerChannel = PlayerModel::PLAYER_CHANNEL_H5_WECHAT)
    {
        $params['player_pcid'] = $playerPcid;
        $params['player_name'] = '';
        $params['player_nickname'] = $nickname;
        $params['player_header_image'] = $headimgurl;
        $params['player_sex'] = $sex;
        $params['player_guest'] = PlayerModel::PLAYER_GUEST_NO;
        $params['player_channel'] = $playerChannel;
        $params['player_openid_gzh'] = isset($param['openid']) ? $param['openid'] : '';
        $params['agent_p_parentid'] = isset($param['p_parentid']) ? $param['p_parentid'] : 0;
        $params['player_unionid'] = $unionid;
        return $this->register($params);
    }

    /**
     * @param $params
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function register($params)
    {
        $player_pcid = time() . mt_rand(1000, 9999);
        $player_name = $player_nickname = $player_channel = $player_guest = $player_header_image = $player_sex = null;
        //数组转变量
        extract($params);

        $wx_extension = [];
        $agent_extension = [];
        if (isset($player_openid_gzh)) {
            $wx_extension['player_openid_gzh'] = $player_openid_gzh;
        }
        if (isset($player_unionid)) {
            $wx_extension['player_unionid'] = $player_unionid;
        }
        if (isset($agent_p_parentid)) {
            $agent_extension['agent_p_parentid'] = $agent_p_parentid;
        }

        //事务开始
        Db::startTrans();
        //添加用户
        if (!$player_id = $this->addPlayer($player_pcid, $player_name, $player_nickname, $player_channel, $player_guest, $wx_extension)) {
            Db::rollback();
            return false;
        }

        //添加用户账户
        if (!$player_info_id = $this->addPlayerInfo($player_id, $player_header_image, $player_sex)) {
            Db::rollback();
            return false;
        }

        //父级代理
        $agent_parent_data = $this->getAgentSuperData();
        //添加代理表
        if (!$agent_id = $this->addAgentInfo($player_id, $agent_parent_data['agent_parentid'], $agent_parent_data['agent_top_agentid'], $player_name, $agent_parent_data['agent_level'], $agent_extension)) {
            Db::rollback();
            return false;
        }

        //添加代理账户表
        if (!$agent_account_id = $this->addAgentAccountInfo($agent_id)) {
            Db::rollback();
            return false;
        }

        //添加推广关系表
        if (!$promoters_id = $this->addPromoters($player_id, $agent_parent_data['agent_player_id'], $agent_id, $agent_parent_data['agent_parentid'], $agent_parent_data['agent_top_agentid'])) {
            Db::rollback();
            return false;
        }
        //添加推广数
        if (AgentInfoModel::model()->updateAgentPromoteCount($agent_parent_data['agent_parentid']) === false) {
            Db::rollback();
            return false;
        }

        if (!$agent_level = $this->agentUpgradeLevel($player_id)) {
            Db::rollback();
            return false;
        }


        Db::commit();
        return [
            'player_id' => $player_id,
            'player_info_id' => $player_info_id,
            'agent_id' => $agent_id,
            'agent_account_id' => $agent_account_id,
            'promoters_id' => $promoters_id,
        ];
    }

    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function getAgentSuperData()
    {
        $data = [
            'agent_parentid' => $this->defaultAgentId,
            'agent_top_agentid' => $this->defaultAgentId,
            'agent_player_id' => 0,
            'agent_level' => 1,
        ];
        $model = AgentInfoModel::model()->getByAgentId($this->agentId);
        if ($model) {
            $data['agent_parentid'] = $model->agent_id;
            $data['agent_top_agentid'] = $model->agent_top_agentid == 0 ? $data['agent_parentid'] : $model->agent_top_agentid;
            $data['agent_player_id'] = $model->agent_player_id;
            $data['agent_level'] = $model->agent_level;
        }
        return $data;
    }

    /**
     * @param $player_pcid
     * @param $player_name
     * @param $player_nickname
     * @param $player_channel
     * @param $player_guest
     * @param array $params
     * @return mixed
     */
    protected function addPlayer($player_pcid, $player_name, $player_nickname, $player_channel, $player_guest, $params = [])
    {
        $data['player_pcid'] = $player_pcid;
        $data['player_name'] = $player_name;
        $data['player_nickname'] = urlencode($player_nickname);
        $data['player_password'] = LoginBlock::passwordEncrypt($this->defaultPlayerPassword);
        $data['player_channel'] = $player_channel;
        $data['player_guest'] = $player_guest;
        $data['player_resigter_time'] = time();
        if (!empty($params)) {
            if (isset($params['player_openid_gzh'])) {
                $data['player_openid_gzh'] = $params['player_openid_gzh'];
            }
            if (isset($params['player_name'])) {
                $data['player_name'] = $params['player_name'];
            }
            if (isset($params['player_unionid'])) {
                $data['player_unionid'] = $params['player_unionid'];
                $data['player_password'] = '';
                $data['player_salt'] = '';
            }
        }
        return PlayerModel::model()->insertGetId($data);
    }

    /**
     * 添加用户账户表
     * @param $player_id
     * @param $player_header_image
     * @param $player_sex
     * @param array $params
     * @return mixed
     */
    protected function addPlayerInfo($player_id, $player_header_image, $player_sex, $params = [])
    {
        $params['player_id'] = $player_id;
        $params['player_header_image'] = $player_header_image;
        $params['player_sex'] = $player_sex;
        return PlayerInfoModel::model()->insertGetId($params);
    }

    /**
     * 添加代理表
     * @param $player_id
     * @param $agent_parentid
     * @param $agent_top_agentid
     * @param $player_name
     * @param $agent_level
     * @param array $params
     * @return mixed
     */
    protected function addAgentInfo($player_id, $agent_parentid, $agent_top_agentid, $player_name, $agent_level, $params = [])
    {
        $data['agent_player_id'] = $player_id;
        $data['agent_parentid'] = $agent_parentid;
        $data['agent_top_agentid'] = $agent_top_agentid;
        $data['agent_name'] = $player_name;
        $data['agent_level'] = $agent_level + 1;
        $data['agent_createtime'] = time();
        $data['agent_permissions'] = 0;
        $data['agent_status'] = 1;
        $data['agent_login_status'] = 0;
        if (!empty($params)) {
            if (isset($params['agent_p_parentid'])) {
                $data['agent_p_parentid'] = $params['agent_p_parentid'];
            }
        }
        return AgentInfoModel::model()->insertGetId($data);
    }

    /**
     * 添加代理账户表
     * @param $agent_id
     * @param array $params
     * @return mixed
     */
    protected function addAgentAccountInfo($agent_id, $params = [])
    {
        $params['agent_account_agent_id'] = $agent_id;
        $params['agent_account_money'] = 0;
        $params['agent_account_alipay'] = '';
        $params['agent_account_username'] = '';
        $params['agent_account_mobile'] = '';
        return AgentAccountInfoModel::model()->insertGetId($params);
    }

    /**
     * 添加推广表
     * @param $player_id
     * @param $promoters_parent_id
     * @param $promoters_agent_id
     * @param $promoters_agent_parentid
     * @param $promoters_agent_top_agentid
     * @param $params
     * @return mixed
     */
    protected function addPromoters($player_id, $promoters_parent_id, $promoters_agent_id, $promoters_agent_parentid, $promoters_agent_top_agentid, $params = [])
    {
        $params['promoters_player_id'] = $player_id;
        $params['promoters_parent_id'] = $promoters_parent_id;
        $params['promoters_agent_id'] = $promoters_agent_id;
        $params['promoters_agent_parentid'] = $promoters_agent_parentid;
        $params['promoters_agent_top_agentid'] = $promoters_agent_top_agentid;
        $params['promoters_time'] = time();
        return PromotersInfoModel::model()->insertGetId($params);
    }

    /**
     * @param $player_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 判断是否能升级为星级推广
     */
    public function agentUpgradeLevel($player_id)
    {
        if (!$player_id) {
            return true;
        }
        //查看代理信息
        $agent_info = AgentInfoModel::model()->getInfo(array('agent_player_id' => $player_id));

        if (!$agent_info) {
            return true;
        }
        //特代ID
        $agent_top_agentid = $agent_info['agent_top_agentid'];
        $agent_parentid = $agent_info['agent_parentid'];
        if ($agent_top_agentid == $agent_parentid) {
            //特级代理 推广的 不用升级
            return true;
        }

        //加载上级信息
        $agent_parent_info = AgentInfoModel::model()->getInfo(array('agent_id' => $agent_parentid, 'agent_login_status' => $this->agent_login_status_no));
        if (!$agent_parent_info) {
            return true;
        }
        //人数  加载这条消耗的上级信息
        $agent_id = $agent_parent_info['agent_id'];
        $agent_promote_conut = $agent_parent_info['agent_promote_count'];
        $agent_player_id = $agent_parent_info['agent_player_id'];
        $agent_parentid = $agent_parent_info['agent_parentid'];

        //加载顶级代理的配置如果没有就加载总公司的配置
        $condition_agent_model = AgentConfigModel::model()->getInfo(array('agent_id' => $agent_top_agentid));
        if (!$condition_agent_model) {
            $condition_agent_model = AgentConfigModel::model()->getInfo(array('agent_id' => 0));
        }
        $agent_conditions_id = $condition_agent_model['agent_conditions_id'];
        //加载选中的配置
        $condition_model = AgentConditionsModel::model()->getFind(array('agent_conditions_id' => $agent_conditions_id));
        if (!$condition_model) {
            return true;
        }
        //加载条件
        $data_condition = json_decode($condition_model['agent_conditions_data'], true);
        if (!$data_condition) {
            return true;
        }
        //人数判断
        if ($data_condition['promote_number']) {
            if ($agent_promote_conut < $data_condition['promote_number']) {
                return true;
            }
        }
        //玩家消耗
        $player_statistics_model = PlayerStatisticalModel::model()->getFind(array('statistical_player_id' => $agent_player_id));
        if (!$player_statistics_model) {
            return true;
        }
        //获取玩家消耗
        $statistical_sub_total_cost = $player_statistics_model['statistical_sub_total_cost'];
        //判断金币消耗
        if ($data_condition['gold_consumption']) {
            if ($statistical_sub_total_cost < $data_condition['gold_consumption']) {
                return true;
            }
        }

        //修改登录权限
        $conditionstatus['agent_login_status'] = 1;
        $conditionstatus['agent_star_time'] = time();
        if (!AgentInfoModel::model()->updateInfo(array('agent_id' => $agent_id), $conditionstatus)) {
            return false;
        }
        //修改领取推广奖励的状态 为 1
        $conditstat['statistical_award_money_status'] = 1;
        $conditstat['statistical_award_status_time'] = time();
        if (!PlayerStatisticalModel::model()->updateInfo(array('statistical_player_id' => $agent_player_id), $conditstat)) {
            return false;
        }

        //把奖励的前更新到dc_agent_account_info 里面的代理信息表
        $agent_accinfo = AgentAccountInfoModel::model()->getInfo(array('agent_account_agent_id' => $agent_id));
        $conditionagentacc['agent_account_money'] = $player_statistics_model['statistical_award_money'] + $agent_accinfo['agent_account_money'];
        if (!AgentAccountInfoModel::model()->updateInfo(array('agent_account_agent_id' => $agent_id), $conditionagentacc)) {
            return false;
        }
        //写日记
        $agentUpgrade['agent_upgrade_record_agent_id'] = $agent_parentid;
        $agentUpgrade['agent_upgrade_record_player_id'] = $agent_player_id;
        $agentUpgrade['agent_upgrade_record_time'] = time();
        if (AgentUpgradeRecordModel::model()->getInsert($agentUpgrade) === false) {
            return false;
        }

        //写日记 dc_agent_account_info_log
        $agentInfoLog['log_money_type'] = 1;
        $agentInfoLog['log_agent_id'] = $agent_id;
        $agentInfoLog['log_bef_money'] = $agent_accinfo['agent_account_money'];
        $agentInfoLog['log_money'] = $player_statistics_model['statistical_award_money'];
        $agentInfoLog['log_aft_money'] = $agent_accinfo['agent_account_money'] + $player_statistics_model['statistical_award_money'];
        $agentInfoLog['log_type'] = 2;
        $agentInfoLog['log_add_time'] = time();
        if (AgentAccountInfoLogModel::model()->addAgentInfoLog($agentInfoLog) === false) {
            return false;
        }
        return true;

    }
}