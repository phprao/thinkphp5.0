<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:44
 * @author ChangHai Zhan
 */

namespace app\admin\controller\v1;

use app\admin\controller\Controller;
use app\admin\model\DemoModel;

/**
 * Class Demo
 * @package app\admin\controller\v1
 * @author ChangHai Zhan
 */
class Demo extends Controller
{
    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = [
            'hello ……',
            config('redis_config'),
        ];
        return $this->sendSuccess(['list' => $model]);
    }

    /**
     * @param $id
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function update($id)
    {
        if (!$model = DemoModel::getById($id)) {
            return $this->sendError(400, '数据不存在');
        }
        $model->scenario = 'update';
        if (true !== ($result = $this->validateModel($model))) {
            return $this->sendError(400, $result);
        }
        if (!$model->allowField('name')->isUpdate(true)->save($this->request->post(), ['id' => $id])) {
            return $this->sendError(400, '修改失败');
        }
        return $this->sendSuccess();
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function create()
    {
        $model = new DemoModel();
        $model->scenario = 'create';
        if (true !== ($result = $this->validateModel($model))) {
            return $this->sendError(400, $result);
        }
        if (!$model->allowField('name')->save($this->request->post())) {
            return $this->sendError();
        }
        return $this->sendSuccess();
    }

    /**
     * @param $id
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function view($id)
    {
        $model = DemoModel::model()->getDemoById($id, 'view');
        return $this->sendSuccess($model);
    }

    /**
     * @param $id
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function delete($id)
    {
        if (!DemoModel::model()->deleteDemo($id)) {
            return $this->sendError();
        }
        return $this->sendSuccess();
    }
}
