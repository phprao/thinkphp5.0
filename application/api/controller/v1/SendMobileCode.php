<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-03-14 12:02:06
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 发送验证码的接口
 +---------------------------------------------------------- 
 */
namespace app\api\controller\v1;

use app\api\controller\Controller;
use app\api\redis\MobileCodeRedis;
use app\api\service\SendCode;
use think\cache\driver\Redis;

class SendMobileCode extends Controller {

    public function send()
    {
        $code = rand(111111,999999);
        $token = input('token');
        $mobile = input('mobile');
        $operation = input('operation');
        $captcha = input('captcha');
        $action = input('action');

        $dataInfo = $this->isLogin($this->token);
        if(empty($dataInfo)){
            return $this->sendError(10000,'用户登陆的token失效。');
        }

        if(empty($mobile)){
            return $this->sendError(10000,'用户手机号码不能为空。');
        }

        if (!preg_match("/^[1][356789][0-9]{9}$/", $mobile)) {
            return $this->sendError(10000, '请正确的输入手机号码');
        }

        if($action == 'setting' ){
            if(!captcha_check($captcha,$this->token)){
                return $this->sendError(10001,'图形验证码错误。');
            };
        }


        if(empty($operation)){
            return $this->sendError(10000,'发送验证码的操作类型不能为空。');
        }
        $key = $operation.$mobile;
        $data = MobileCodeRedis::get($key);
        if($data){
            return $this->sendError(10000,'短信发送太频繁,请稍后再发。');
        }

        $nums = MobileCodeRedis::get($mobile);
        if($nums > 8){
            return $this->sendError(100005,'今日的次数已经用完了。');
        }

        $sendCodeObject = new SendCode();
        $result = $sendCodeObject->send($mobile,$code);

        if($result['code'] == '1025' ){
            return $this->sendError(10000,'今日的次数已经用完了。');
        }

        if($result['code'] == 0 ){
            //todo 验证码发送成功
            $data = array( 'code' => $code);
            MobileCodeRedis::set($key,$data,120);

            //统计短信发送的次数
            if($nums<1){
                MobileCodeRedis::set($mobile,$nums,86400);
            }
            MobileCodeRedis::setIncr($mobile);

            return $this->sendSuccess();
        }

        return $this->sendError(10000,'短信发送失败!');
    }

}