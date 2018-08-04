<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/17
 * Time: 14:42
 */
namespace app\admin\service;

class WeiXin {
    protected $appid = 'wx70b70386db1a5571';
    protected $secret= 'a11284155a9a99dca8f4e6332c738f88';
    protected $access_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
    protected $user_url = 'https://api.weixin.qq.com/sns/userinfo?';
    protected $refresh_url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?';

    /**
     * 获取access_token的方法
     * @param $code string 获取的授权code
     * @return mixed|string
     */
    public function get_access_token($code) {
        $url = $this->access_token_url.'appid='.$this->appid.'&secret='.$this->secret.'&code='.$code.'&grant_type=authorization_code';
        // $ch = new httpclient();
        $result = file_get_contents($url);//$ch->get($url); //todo 模拟的返回数据;
        /*        $result = '{
                    "access_token":"ACCESS_TOKEN",
                    "expires_in":7200,
                    "refresh_token":"REFRESH_TOKEN",
                    "openid":"OPENID",
                    "scope":"SCOPE",
                    "unionid":"o6_bmasdasdsad6_2sgVt7hMZOPfL"
                    }';*/
        $result = json_decode($result,true);
        return $result;
    }

    /**
     * 获取用户的基本信息
     * @param $accessToken string 微信的token
     * @param $openid string 用户的openid
     * @return array
     */
    public function get_user_info($accessToken,$openid)
    {
        $url = $this->user_url.'access_token='.$accessToken.'&openid='.$openid;  // todo https://api.weixin.qq.com/sns/userinfo?access_token=ACCESS_TOKEN&openid=OPENID

        //$ch = new httpclient();
        $result =  file_get_contents($url);//$ch->get($url);

        //todo 模拟请求
        /*        $result = '{
                    "openid":"OPENID",
                    "nickname":"NICKNAME",
                    "sex":1,
                    "province":"PROVINCE",
                    "city":"CITY",
                    "country":"COUNTRY",
                    "headimgurl": "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0",
                    "privilege":[
                    "PRIVILEGE1",
                    "PRIVILEGE2"
                    ],
                    "unionid": " o6_bmasdasdsad6_2sgVt7hMZOPfL"
                    }';*/

        return json_decode($result,true);

    }

    /**
     * assess_token 过期了刷新的操作
     * @param $refresh_token
     * @return mixed
     */
    public function refresh_token($refresh_token)
    {
        $url = $this->refresh_url.'appid='.$this->appid.'&grant_type=refresh_token&refresh_token='.$refresh_token;// //https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=APPID&grant_type=refresh_token&refresh_token=REFRESH_TOKEN
        $ch = new httpclient();
        $result = $ch->get($url);

        return $result;
    }

    public function getCode($redirect_uri,$state)
    {
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_userinfo&state=$state#wechat_redirect";
        return $url;
    }

}