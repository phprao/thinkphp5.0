<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:44
 * @author ChangHai Zhan
 */
namespace app\wechat\controller;

use think\Request;
use EasyWeChat\Foundation\Application;

/**
 * Class BaseController
 * @package app\api\controller
 * @author ChangHai Zhan
 */
class Controller extends \think\Controller
{
    /**
     * @var
     */
    protected $request;
    /**
     * @var array
     */
    protected $wechatType;
    /**
     * @var array
     */
    protected $wechatOptions = [];
    /**
     * @var
     */
    protected $easyWeChatApp;
    /**
     * 初始化方法
     */
    public function _initialize()
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
        $this->request = Request::instance();
        //加载配置
        $this->wechatType = $this->request->get('wechat_type', 'wechat_u3d');
        $this->wechatOptions = config($this->wechatType);
    }

    /**
     * @param null $config
     * @return Application
     */
    protected function getWechatOptions($config = null)
    {
        if (!$this->easyWeChatApp || $config !== null) {
            if ($config !== null) {
                $this->easyWeChatApp = new Application($config);
            } else {
                $this->easyWeChatApp = new Application($this->wechatOptions);
            }
        }
        return $this->easyWeChatApp;
    }

    /**
     * @return mixed|null
     */
    protected function getOpenid()
    {
        $wechatUserInfo = session('wechatUserInfo');
        if (!$wechatUserInfo) {
            $app = $this->getWechatOptions();
            $request = $app->oauth->getRequest();
            $app->oauth->setRequest($request);
            $user = $app->oauth->user();
            if ($user) {
                $wechatUserInfo = $user;
                session('wechatUserInfo', $user);
            }
        }
        return $wechatUserInfo['id'];
    }
}