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
class Wxpay extends Controller
{
    /**
     *
     */
    public function index($orderNo = null)
    {
        //$openid = $this->getOpenid();
        $app = new Application($this->wechatOptions);
        $payment = $app->payment;
        $attributes = [
            'trade_type' => 'JSAPI',        // JSAPI，NATIVE，APP...
            'body' => '大川游戏-友间麻将',    // body
            'out_trade_no' => $orderNo ? $orderNo : time() . mt_rand(1000, 9999),     // 订单号
            'total_fee' => 1,               // 单位：分
            'openid' => 'oQ-bY0ZMBYhK2khiKl2bwl_bRR3M',//$openid,            // trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识];
        ];
        $order = new Order($attributes);
        $result = $payment->prepare($order); // 这里的order是上面一步得来的。 这个prepare()帮你计算了校验码，帮你获取了prepareId.省心。
        if ($result->return_code == 'SUCCESS') {
            $config = $payment->configForAppPayment($result->prepay_id);
            $js = $app->js;
        } else {
            $this->errorAction('支付错误');
        }
        return $this->fetch('index', ['money' => '0.01', 'config' => $config, 'js' => $js]);
    }

    /**
     * @param $msg
     */
    protected function errorAction($msg)
    {
        $this->error(json_encode($msg));
    }
}
