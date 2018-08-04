<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:44
 * @author ChangHai Zhan
 */

namespace app\common\controller;

use app\common\components\Helper;
use app\common\components\DocParser;

/**
 * 总控制器
 * Class Controller
 * @package app\common
 * @author ChangHai Zhan
 */
class Controller extends \think\Controller
{
    /**
     * 初始化
     */
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 验证数据
     * @param $model
     * @param null $data
     * @return array|string|true
     */
    protected function validateModel($model, $data = null)
    {
        if ($data === null) {
            $data = $this->request->post();
        }
        return $this->validate($data, $model->getRule($model->scenario), $model->getMessage($model->scenario));
    }

    /**
     * @param $file
     * @param array $exclude
     * @param bool $group
     * @param array $only
     * @param string $appBaseDir
     * @param string $appBaseNamespace
     * @return array|bool
     */
    protected function setAction($file, $exclude, $group, $only, $appBaseDir, $appBaseNamespace)
    {
        $pathInfo = pathinfo($file);
        if (!isset($pathInfo['extension']) || $pathInfo['extension'] != 'php') {
            return [];
        }
        $file = str_replace('/','\\', $file);
        $namespace = explode($appBaseDir, $file);
        $modules = dirname(end($namespace));
        $controllerNamespace = $appBaseNamespace . $modules;
        $fileName = basename($file, '.php');
        $class = new \ReflectionClass($controllerNamespace . '\\' . $fileName);
        $modules =  str_replace('\\controller','', ltrim($modules, '\\'));
        $modules = Helper::getPretty(str_replace('\\','/', $modules));
        $baseController = Helper::getPretty($fileName);
        $controller =  $modules . '/' . $baseController;
        $controllerName = DocParser::getParamsValue($class->getDocComment(), 'controllerName', $baseController);
        $actions = [];
        foreach ($class->getMethods() as $method) {
            $name = $method->getName();
            if (!$method->isStatic() && $method->isPublic() && $name != '__construct') {
                $action = $controller . '/' . Helper::getPretty($name);
                if (in_array($action, $exclude)) {
                    continue;
                }
                if ($only !== null && !in_array($action, $only)) {
                    continue;
                }
                $actionName = DocParser::getParamsValue($method->getDocComment(), 'actionName', $action);
                if ($group) {
                    $actions[$controllerName . ' (' . $baseController . ')'][$action] = $actionName;
                } else {
                    $actions[$action] = $actionName;
                }
            }
        }
        return $actions;
    }
}