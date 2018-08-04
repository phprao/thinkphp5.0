<?php
/**
 *游戏列表
 * liangjunbin
 *
 */

namespace app\admin\controller\v1;

use app\admin\block\DataArrange;
use app\admin\controller\Controller;
use app\admin\model\GameInfoModel;


class GameInfo extends Controller
{

    /**
     *
     */
    public function _initialize()
    {
        parent::_initialize();
//        $this->isLogin($this->token);
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {

        $field = 'id,game_id,game_name';
        $data = GameInfoModel::model()->getListField(array('game_status' => 1), $field);

        return $this->sendSuccess($data);
    }

}











