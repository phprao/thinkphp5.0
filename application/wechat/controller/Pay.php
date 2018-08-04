<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:44
 * @author ChangHai Zhan
 */

namespace app\wechat\controller;

use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;

/**
 * Class Demo
 * @package app\api\controller\v1
 * @author ChangHai Zhan
 */
class Pay extends Controller
{
//    /**
//     * @param $appid
//     * @param $secret
//     * @return mixed|null
//     */
//    protected function getOpenid($appid, $secret)
//    {
//        if (isset($_GET['code'])) {
//            $code = $_GET['code'];
//            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
//            $data = file_get_contents($url);
//            $res = json_decode($data, true);
//            if (isset($res['errcode'])) {
//                $this->errorAction($res);
//            }
//            $openid = $res['openid'];
//            return $openid;
//        }
//    }


    /**
     *
     */
    public function index($orderNo = null)
    {

        var_dump($this->getOpenid());
    }

    protected function errorAction($msg)
    {
        $this->errorAction(json_encode($msg));
    }
}
