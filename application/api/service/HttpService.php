<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/8
 * Time: 11:09
 * @author ChangHai Zhan
 */
namespace app\api\service;

class HttpService{
    private $ch;

    function __construct() {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.0; QQDownload 685; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C; .NET4.0E)');//UA
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 3);//超时
        //curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_ENCODING, 'UTF-8');
    }

    function __destruct() {
        //curl_close($this->ch);
    }

    final public function close() {
        curl_close($this->ch);
    }

    final public function set_proxy($proxy='http://127.0.0.1') {
        curl_setopt($this->ch, CURLOPT_PROXY, $proxy);
    }

    final public function set_referer($ref='') {
        if($ref != '') {
            curl_setopt($this->ch, CURLOPT_REFERER, $ref);//Referrer
        }
    }

    final public function set_cookie($ck='') {
        if($ck != '') {
            curl_setopt($this->ch, CURLOPT_COOKIE, $ck);//Cookie
        }
    }

    final public function get($url, $header=false, $nobody=false) {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_POST, false);//POST
        curl_setopt($this->ch, CURLOPT_HEADER, $header);//返回Header
        curl_setopt($this->ch, CURLOPT_NOBODY, $nobody);//不需要内容
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        return curl_exec($this->ch);
    }

    final public function get_with_post($url, $datastr, $header=false, $nobody=false) {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_HEADER, $header);//返回Header
        curl_setopt($this->ch, CURLOPT_NOBODY, $nobody);//不需要内容
        curl_setopt($this->ch, CURLOPT_POST, true);//POST
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $datastr);
        return curl_exec($this->ch);
    }

    final public function post($url, $data=array(), $header=false, $nobody=false) {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_HEADER, $header);//返回Header
        curl_setopt($this->ch, CURLOPT_NOBODY, $nobody);//不需要内容
        curl_setopt($this->ch, CURLOPT_POST, true);//POST
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($data));
        return curl_exec($this->ch);
    }

}