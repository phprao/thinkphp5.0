<?php
/**
 *
 * liangjunbin
 *
 */

namespace app\admin\controller\v1;

use app\admin\block\DataArrange;
use app\admin\controller\Controller;
use app\admin\model\AgentGameModel;
use app\admin\model\GameInfoModel;


class AgentGame extends Controller
{

    /**
     *
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->isLogin($this->token);

    }

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
        $field = "agent_game_id,agent_agent_id,agent_game_game_id,agent_game_order,agent_host";
        $data = AgentGameModel::model()->getList(array('agent_agent_id' => $agentid), $field, 'agent_host,agent_game_order asc');
        foreach ($data as $key => $datum) {
            $gameinfo = GameInfoModel::model()->getOne(array('game_id' => $datum['agent_game_game_id']));
            $data[$key]['game_name'] = $gameinfo['game_name'];
        }
        return $this->sendSuccess($data);
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function gameEtid()
    {

        $agentid = '';
        $loginData = $this->isLogin($this->token);
        if (isset($loginData['agentInfo'])) {
            $agentid = $loginData['agentInfo']['agent_id'];
        }

        $agent_host = $this->request->post('agent_host', 0);
        $agent_game_order = $this->request->post('agent_game_order', 0);
        $res = [];
        if ($agent_game_order) {
            $agent_game_order = \GuzzleHttp\json_decode($agent_game_order, true);

            foreach ($agent_game_order as $key => $value) {
                $condition['agent_game_order'] = $value;
                $res = AgentGameModel::model()->updateData($condition, array('agent_game_id' => $key));
            }
        }
        if ($agent_host) {
            $agent_host = \GuzzleHttp\json_decode($agent_host, true);
            foreach ($agent_host as $ke => $val) {
                $datagame = AgentGameModel::model()->getOne(array('agent_host' => 1, 'agent_agent_id' => $agentid));
                if ($datagame) {
                    AgentGameModel::model()->updateData(array('agent_host' => 5), array('agent_game_id' => $datagame['agent_game_id']));
                }
                $res = AgentGameModel::model()->updateData(array('agent_host' => 1), array('agent_game_id' => $ke));
            }
        }



        return $this->sendSuccess($res);
    }

}











