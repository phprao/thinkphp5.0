<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:44
 * @author ChangHai Zhan
 */
namespace app\admin\controller;

use app\admin\block\AuthBlock;
use app\admin\block\LoginBlock;
use app\common\components\Send;
use \think\Session;

/**
 * Class BaseController
 * @package app\admin\controller
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
        $this->token = $this->request->param('token');
    }

    /**
     * $token
     * @param $token
     * @param bool $exit
     * @return bool|void
     */
    protected function isLogin($token, $exit = true)
    {
        // $userInfo = LoginBlock::isLogin($token);
        $userInfo = json_decode(Session::get('login_info'), true);
        if(empty($userInfo)){
            if ($exit) {
                return $this->sendMassage([], 'not login', 1000);
            }
            return false;
        }
        if (!AuthBlock::checkAccess($userInfo['userInfo']['id'], $this->request->path())) {
            return $this->sendMassage([], '无权访问', 403);
        }
        return $userInfo;
    }

    /**
     * 扫描方法 或者 扫描方法分组
     * @param array $exclude
     * @param bool $group
     * @param array $only
     * @param string $appBaseDir
     * @param string $appBaseNamespace
     * @return array
     */
    protected function getControllerActions($exclude = [], $group = true, $only = null, $appBaseDir = 'application', $appBaseNamespace = 'app')
    {
        $actions = [];
        foreach (glob(__DIR__ . '/*') as $pathFile) {
            if (is_dir($pathFile)) {
                foreach (glob($pathFile . '/*.php') as $file) {
                    $actions = array_merge($actions, $this->setAction($file, $exclude, $group, $only, $appBaseDir, $appBaseNamespace));
                }
            } else {
                $actions = array_merge($actions, $this->setAction($pathFile, $exclude, $group, $only, $appBaseDir, $appBaseNamespace));
            }
        }
        return $actions;
    }
}