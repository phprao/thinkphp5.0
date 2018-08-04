<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/19
 * Time: 11:41
 * @author ChangHai Zhan
 */

namespace app\common\exception;

use app\common\components\Send;
use Exception;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;


class JsonException extends Handle
{
    /**
     *
     */
    use Send;

    /**
     * 错误机制
     * @param Exception $e
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function render(Exception $e)
    {
        // 参数验证错误
        if ($e instanceof ValidateException) {
            return $this->sendError(422, $e->getError());
        }
        // 请求异常
        if ($e instanceof HttpException) {
            return $this->sendError($e->getStatusCode(), $e->getMessage());
        }
        return $this->sendError(422, $e->getMessage());
    }

}