<?php
/**
 * 向客户端发送相应基类
 */

namespace app\common\components;

use think\Response;
use think\response\Redirect;

trait Send
{
    /**
     * 默认返回资源类型
     * @var string
     */
    protected $restDefaultType = 'json';

    /**
     * 设置响应类型
     * @param null $type
     * @return $this
     */
    protected function setType($type = null)
    {
        $this->type = (string)(!empty($type)) ? $type : $this->restDefaultType;
        return $this;
    }

    /**
     * 失败响应
     * @param int $error
     * @param string $message
     * @param int $code 防运营商劫持,修改为200
     * @param array $data
     * @param array $headers
     * @param array $options
     * @return Response|\think\response\Json|\think\response\Jsonp|Redirect|\think\response\View|\think\response\Xml
     */
    protected function sendError($error = 400, $message = 'error', $code = 200, $data = [], $headers = [], $options = [])
    {
        $responseData['status'] = (int)$error;
        $responseData['msg'] = (string)$message;
        if (!empty($data)) $responseData['data'] = $data;
        $responseData = array_merge($responseData, $options);
        return $this->response($responseData, $code, $headers);
    }

    /**
     * 成功响应
     * @param array $data
     * @param string $message
     * @param int $code
     * @param array $headers
     * @param array $options
     * @return Response|\think\response\Json|\think\response\Jsonp|Redirect|\think\response\View|\think\response\Xml
     */
    protected function sendSuccess($data = [], $message = 'success', $code = 200, $headers = [], $options = [])
    {
        $responseData['status'] = 0;
        $responseData['msg'] = (string)$message;
        if (!empty($data)) $responseData['data'] = $data;
        $responseData = array_merge($responseData, $options);
        return $this->response($responseData, $code, $headers);
    }

    /**
     * 重定向
     * @param $url
     * @param array $params
     * @param int $code
     * @param array $with
     * @return Redirect
     */
    protected function sendRedirect($url, $params = [], $code = 302, $with = [])
    {
        $response = new Redirect($url);
        if (is_integer($params)) {
            $code = $params;
            $params = [];
        }
        $response->code($code)->params($params)->with($with);
        return $response;
    }

    /**
     * 响应
     * @param $responseData
     * @param $code
     * @param $headers
     * @return Response|\think\response\Json|\think\response\Jsonp|Redirect|\think\response\View|\think\response\Xml
     */
    protected function response($responseData, $code, $headers)
    {
        //跨域
        Helper::headerCrossDomain(config('cross_domain'));
        if (!isset($this->type) || empty($this->type)) $this->setType();
        return Response::create($responseData, $this->type, $code, $headers);
    }

    /**
     * 直接输出数据
     * @param int $error
     * @param string $message
     * @param int $code
     * @param array $data
     * @param array $headers
     * @param array $options
     */
    protected function sendMassage($data = [], $message = 'success', $error = 200, $code = 200, $headers = [], $options = [])
    {
        $responseData['status'] = (int)$error;
        $responseData['msg'] = (string)$message;
        if (!empty($data)) $responseData['data'] = $data;
        $responseData = array_merge($responseData, $options);
        $response = $this->response($responseData, $code, $headers);
        throw new \think\exception\HttpResponseException($response);
    }
}