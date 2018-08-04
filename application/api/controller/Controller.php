<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:44
 * @author ChangHai Zhan
 */
namespace app\api\controller;

use app\api\block\LoginBlock;
use think\cache\driver\Redis;
use think\Request;
use app\common\components\Send;
use \think\Session;

/**
 * Class BaseController
 * @package app\api\controller
 * @author ChangHai Zhan
 */
class Controller extends \app\common\controller\Controller
{
    /**
     * 引入重用
     */
    use Send;
    /**
     * 当前资源类型
     * @var string
     */
    protected $type;
    /**
     * token
     * @var
     */
    protected $token;

    /**
     * 初始化方法
     */
    protected function _initialize()
    {
        $this->init();
        parent::_initialize();
    }

    /**
     * 初始化方法
     * 检测请求类型，数据格式等操作
     */
    protected function init()
    {
        // $this->token = $this->request->get('token');
        $this->token = Session::get('token');
    }

    /**
     * @param $token
     * @param bool $exit
     * @return bool|mixed|\think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    protected function isLogin($token, $exit = true)
    {
        $agentInfo = LoginBlock::isLogin($token);
        if(empty($agentInfo)){
            if ($exit) {
                return $this->sendMassage([],'not login', 1000);
            }
            return false;
        }
        return $agentInfo;
    }

    protected function virtualLogin(){
        $action = $this->request->post('action');
        $token = $this->request->post('token');
        if($action == 'login'){
            Session::set('token',$token);
        }else{
            $token = Session::get('token'); 
        }
        
        return $this->isLogin($token);
    }
}