<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:41
 * @author ChangHai Zhan
 */

namespace app\admin\block;

use app\admin\model\AuthAllowModel;
use app\admin\model\AuthUserModel;

/**
 * 权限规则 :
 * 方法、控制命名 驼峰命名法（控制器首字母大写）
 * 路由url 遇到大写字母 加下滑线 并转换成小写  例如 helloWord => hello_word
 * 控制器 非方法的函数 禁止用 public
 * 在控制器 注释模块名称或含义 例如： @controllerName 登陆模块
 * 在方法 注释可读性注释解释  例如： @actionName 后台管理登陆
 * 验证规范Url admin/auth/index
 * 逻辑块
 * Class AuthBlock
 * @author ChangHai Zhan
 */
class AuthBlock
{
    /**
     * 开启权限
     * @var bool
     */
    public static $openPermission = true;

    /**
     * 权限判断
     * @param $userId
     * @param $action
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function checkAccess($userId, $action)
    {
        if (!self::$openPermission) {
            return true;
        }
        if (AuthAllowModel::model()->isAuthAllow($action)) {
            return true;
        }
        if (AuthUserModel::model()->isAuth($userId, $action)) {
            return true;
        }
        return false;
    }
}