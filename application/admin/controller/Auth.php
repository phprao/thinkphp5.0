<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-03-19 10:44:34
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 权限模块
 +---------------------------------------------------------- 
 */

namespace app\admin\controller;

use app\admin\model\AuthActionModel;
use app\admin\model\AuthAllowModel;
use app\admin\model\AuthRoleModel;
use app\admin\model\AuthUserModel;
use app\admin\model\UsersModel;

/**
 * @controllerName 权限模块
 */

class Auth extends Controller
{
    /**
     * @actionName 验证是否规范
     */
    public function index()
    {
        return $this->sendSuccess(['list' => $this->getControllerActions()]);
    }

    /**
     * @actionName 添加角色
     */
    public function createRole()
    {
        $this->isLogin($this->token);
        $name = $this->request->post('name');
        if (!$name) {
            return $this->sendError(2001, '角色名称 不可空白');
        }
        if (AuthRoleModel::model()->getRoleByName($name)) {
            return $this->sendError(2002, '角色名称 不可重复');
        }
        if (!AuthRoleModel::model()->createRole($name)) {
            return $this->sendError(2003, '服务器繁忙-请稍后再试');
        }
        return $this->sendSuccess();
    }

    /**
     * @actionName 修改角色
     */
    public function updateRole()
    {
        $this->isLogin($this->token);
        $id = $this->request->post('id');
        $name = $this->request->post('name');
        if (!$name) {
            return $this->sendError(2001, '角色名称 不可空白');
        }
        if (!AuthRoleModel::model()->updateRole($id, $name)) {
            return $this->sendError(2002, '服务器繁忙-请稍后再试');
        }
        return $this->sendSuccess();
    }

    /**
     * @actionName 角色列表
     */
    public function roleList()
    {
        $this->isLogin($this->token);
        return $this->sendSuccess(['list' => AuthRoleModel::model()->getAll()]);
    }

    /**
     * @actionName 角色已分配的方法列表
     */
    public function actionList()
    {
        $this->isLogin($this->token);
        $roleId = $this->request->get('role_id');
        $models = AuthActionModel::model()->getListByRoleId($roleId);
        $actions = [];
        foreach ($models as $model) {
            $actions[$model->action] = $model->action;
        }
        return $this->sendSuccess(['list' => $this->getControllerActions([],true, $actions)]);
    }

    /**
     * @actionName 可分配方法列表
     */
    public function assignActionList()
    {
        $this->isLogin($this->token);
        $roleId = $this->request->get('role_id');
        $models = AuthActionModel::model()->getListByRoleId($roleId);
        $exclude = [];
        foreach ($models as $model) {
            $exclude[] = $model->action;
        }
        $modelsAllow = AuthAllowModel::model()->getAll();
        foreach ($modelsAllow as $modelAllow) {
            $exclude[] = $modelAllow->action;
        }
        return $this->sendSuccess(['list' => $this->getControllerActions($exclude)]);

    }

    /**
     * @actionName 分配方法
     */
    public function addAction()
    {
        $this->isLogin($this->token);
        $roleId = (int)$this->request->post('role_id');
        $actions = (array)$this->request->post('actions/a');
        if (empty($actions)) {
            return $this->sendError(2001, '分配方法 不可空白');
        }
        if (!AuthRoleModel::model()->getRoleById($roleId)) {
            return $this->sendError(2002, '角色 不存在');
        }
        //过滤
        $actions = array_flip($actions);
        $models = AuthActionModel::model()->getListByRoleId($roleId);
        foreach ($models as $model) {
            if (isset($actions[$model->action])) {
                unset($actions[$model->action]);
            }
        }
        $saveActions = [];
        $exclude = [];
        $modelsAllow = AuthAllowModel::model()->getAll();
        foreach ($modelsAllow as $modelAllow) {
            $exclude[] = $modelAllow->action;
        }
        $actionsAll = $this->getControllerActions($exclude, false);
        $actions = array_flip($actions);
        foreach ($actions as $action) {
            if (isset($actionsAll[$action])) {
                $saveActions[] = $action;
            }
        }
        if (empty($saveActions)) {
            return $this->sendError(2003, '分配方法 是无效的');
        }
        if (!AuthActionModel::model()->addActionByRoleId($roleId, $saveActions)) {
            return $this->sendError(2004, '服务器繁忙-请稍后再试');
        }
        return $this->sendSuccess();
    }

    /**
     * @actionName 移除方法
     */
    public function removeAction()
    {
        $this->isLogin($this->token);
        $roleId = (int)$this->request->post('role_id');
        $actions = (array)$this->request->post('actions/a');
        if (empty($actions)) {
            return $this->sendError(2000, '分配方法 不可空白');
        }
        if (!AuthActionModel::model()->removeActionByRoleId($roleId, $actions)) {
            return $this->sendError(2001, '服务器繁忙-请稍后再试');
        }
        return $this->sendSuccess();
    }

    /**
     * @actionName 管理员列表
     */
    public function userList()
    {
        $this->isLogin($this->token);
        return $this->sendSuccess(['list' => UsersModel::model()->getAll('auth_list')]);
    }

    /**
     * @actionName 管理员已分配角色列表
     */
    public function userRoleList()
    {
        $this->isLogin($this->token);
        $userId = (int)$this->request->get('user_id');
        $models = AuthUserModel::model()->getAuthRoleListByUserId($userId);
        $ids = [];
        foreach ($models as $v) {
            $ids[$v->role_id] = $v->role_id;
        }
        $modelsRole = AuthRoleModel::model()->getRoleById($ids);
        $dataRole = [];
        foreach ($modelsRole as $role) {
            $dataRole[$role->id] = $role->name;
        }
        foreach ($models as $model) {
            $model->name = '';
            if (isset($dataRole[$model->role_id])) {
                $model->name = $dataRole[$model->role_id];
            }
        }
        return $this->sendSuccess(['list' => $models]);
    }

    /**
     * @actionName 可分配的角色列表
     */
    public function assignRoleList()
    {
        $this->isLogin($this->token);
        $userId = (int)$this->request->get('user_id');
        $modelsSelect = AuthUserModel::model()->getAuthRoleListByUserId($userId);
        $condition = new \stdClass();
        foreach ($modelsSelect as $select) {
            $condition->notInId[] = $select->role_id;
        }
        return $this->sendSuccess(['list' => AuthRoleModel::model()->getAll($condition)]);
    }

    /**
     * @actionName 分配角色
     */
    public function addRole()
    {
        $this->isLogin($this->token);
        $userId = (int)$this->request->post('user_id');
        $roleId = (array)$this->request->post('role_id/a');
        if (empty($roleId)) {
            return $this->sendError(2001, '分配角色 不可空白');
        }
        if (!UsersModel::model()->getUserById($userId)) {
            return $this->sendError(2002, '管理员 不存在');
        }
        if (!$models = AuthRoleModel::model()->getRoleById($roleId)) {
            return $this->sendError(2003, '角色 不存在');
        }
        $saveRoleId = [];
        foreach ($models as $v) {
            $saveRoleId[$v->id] = $v->id;
        }
        $modelsSelect = AuthUserModel::model()->getAuthRoleListByUserId($userId);
        foreach ($modelsSelect as $select) {
            if (isset($saveRoleId[$select->role_id])) {
                unset($saveRoleId[$select->role_id]);
            }
        }
        if (empty($saveRoleId)) {
            return $this->sendError(2004, '角色 是无效的');
        }
        if (!AuthUserModel::model()->addRoleByUserId($userId, $saveRoleId)) {
            return $this->sendError(2005, '服务器繁忙-请稍后再试');
        }
        return $this->sendSuccess();
    }

    /**
     * @actionName 移除角色
     */
    public function removeRole()
    {
        $this->isLogin($this->token);
        $user_id = (int)$this->request->post('user_id');
        $role_id = (array)$this->request->post('role_id/a');
        if (empty($role_id)) {
            return $this->sendError(2000, '角色 不可空白');
        }
        if (!AuthUserModel::model()->removeRoleByUserId($user_id, $role_id)) {
            return $this->sendError(2001, '服务器繁忙-请稍后再试');
        }
        return $this->sendSuccess();
    }

    /**
     * @actionName 白名单列表
     */
    public function allowList()
    {
        $this->isLogin($this->token);
        $models = AuthAllowModel::model()->getAll();
        $actions = [];
        foreach ($models as $model) {
            $actions[$model->action] = $model->action;
        }
        return $this->sendSuccess(['list' => $this->getControllerActions([], true, $actions)]);
    }

    /**
     * @actionName 待分配白名单列表
     */
    public function assignAllowList()
    {
        $this->isLogin($this->token);
        $models = AuthAllowModel::model()->getAll();
        $allowActions = [];
        foreach ($models as $model) {
            $allowActions[] = $model->action;
        }
        return $this->sendSuccess(['list' => $this->getControllerActions($allowActions)]);

    }

    /**
     * @actionName 添加白名单
     */
    public function addAllow()
    {
        $this->isLogin($this->token);
        $actions = (array)$this->request->post('actions/a');
        $actions = array_flip($actions);
        $models = AuthAllowModel::model()->getAll();
        foreach ($models as $model) {
            if (isset($actions[$model->action])) {
                unset($actions[$model->action]);
            }
        }
        if (empty($actions)) {
            return $this->sendError(2001, '白名单方法 无效');
        }
        $actions = array_flip($actions);
        if (!AuthAllowModel::model()->addAllow($actions)) {
            return $this->sendError(2002, '服务器繁忙-请稍后再试');
        }
        return $this->sendSuccess();
    }

    /**
     * @actionName 移除白名单
     */
    public function removeAllow()
    {
        $this->isLogin($this->token);
        $actions = (array)$this->request->post('actions/a');
        if (empty($actions)) {
            return $this->sendError(2000, '白名单方法 不可空白');
        }
        if (!AuthAllowModel::model()->removeAllow($actions)) {
            return $this->sendError(2001, '服务器繁忙-请稍后再试');
        }
        return $this->sendSuccess();
    }

    /**
     * @actionName 我的操作权限列表
     */
    public function myActionList()
    {
        $userInfo = $this->isLogin($this->token);
        $models = AuthUserModel::model()->getRoleToActionByUserId($userInfo['userInfo']['id']);
        $list = [];
        foreach ($models as $model) {
            $list[] = $model->action;
        }
        $modelsAllow = AuthAllowModel::model()->getAll();
        foreach ($modelsAllow as $modelAllow) {
            $list[] = $modelAllow->action;
        }
        return $this->sendSuccess(['list' => $list]);
    }
}
