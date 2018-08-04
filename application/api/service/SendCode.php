<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/19
 * Time: 11:35
 */
namespace app\api\service;

class SendCode {

    protected $url;
    protected $app_id;
    protected $app_key;
    protected $tpl_id;

    public function __construct()
    {
        $this->url = 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms?';
        $this->app_id= '1400029497';
        $this->app_key = 'fa1e43e2cd482a25c14e6d1a02bc81a9';
        $this->tpl_id = '17312';
    }

    public function send($mobile,$code)
    {
        $time = time();
        $code_params = array($code,2);
        $sign = '';
        $url = $this->url.'sdkappid='.$this->app_id.'&random='.$code;
        $sig = $this->create_sign($code, $mobile, $time);
        $tel = array('nationcode' => '86', 'mobile' => $mobile);

        $data = array(
            'tel' => $tel,
            'sign' => $sign,
            'tpl_id' => $this->tpl_id,
            'params' => $code_params,
            'sig' => $sig,
            'time' => $time
        );

        $result = $this->curlPost($url,json_encode($data));

        if(empty($result)){
            return false;
        }

        return true;

    }

    public function create_sign($code, $mobile, $time)
    {
        $params = array(
            'appkey' => $this->app_key,
            'random' => $code,
            'time' => $time,
            'mobile' => $mobile
        );
        return hash('sha256', http_build_query($params));
    }

    public function curlPost($url,$data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//CURLOPT_SSL_VERIFYPEER
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($curl);
        curl_close($curl);

        return json_decode($result, true);
    }
}