<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13
 * Time: 10:44
 * @author ChangHai Zhan
 */

namespace app\api\controller\v1;

use app\api\controller\Controller;
use app\api\model\DemoModel;
use app\api\model\UserModel;
use think\Db;

/**
 * Class Demo
 * @package app\api\controller\v1
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
        $condition = new \stdClass();
        $condition->keyword = $this->request->get('keyword');
        $model = DemoModel::model()->getDemoList($condition, 'list');
        return $this->sendSuccess(['list' => $model]);
    }

    /**
     * 初始化数据
     */
    public function initData()
    {
        die;
        //后台用户
        $list = [];
        for ($i = 1; $i < 4; $i++) {
            $list[] = [
                'user_login'=> $i == 1 ? 'admin2018' : 'user_login' . mt_rand(100, 999999),
                'user_pass'=> '###050cacdc7884ddc3547ee7793dfe31fe',
                'user_email' => mt_rand(100, 999999) . '@' . mt_rand(100, 999999) . '.com',
                'last_login_ip' => mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255),
                'last_login_time' => date('Y-m-d H:i:s',time() - mt_rand(100, 999999)),
                'create_time' => time() +  - mt_rand(100, 999999),
                'user_status' => 1,
            ];
        }
        echo Db::name('users')->insertAll($list) . ';' . PHP_EOL;
        //特代
        $list = [];
        for ($i = 1; $i < 4; $i++) {
            $list[] = [
                'agent_user_id' => $i,
                'agent_player_id' => 0,
                'agent_parentid'  => 0,
                'agent_top_agentid' => 0,
                'agent_name' => $i == 1 ? '大川特代' : '我是特代' . $i .  mt_rand(100, 999999),
                'agent_level' => 1,
                'agent_promote_count' => 0,
                'agent_permissions' => 0,
                'agent_status' => 1,
                'agent_login_status' => 0,
                'agent_remark' => 'agent_remark' . mt_rand(100, 999999),
                'agent_createtime' => time() - mt_rand(100, 999999),
            ];
        }
        echo db('dc_agent_info')->insertAll($list) . ';' . PHP_EOL;
        //特代账户
        $list = [];
        for ($i = 1; $i < 4; $i++) {
            $list[] = [
                'agent_account_agent_id' => $i,
                'agent_account_money' => 0,
                'agent_account_alipay' => mt_rand(100, 999999) . '@' . mt_rand(100, 999999) . '.com',
                'agent_account_username' => '支付宝名字' . mt_rand(100, 999999),
                'agent_account_mobile' => '1' . mt_rand(3, 8) . mt_rand(100000000, 999999999),
            ];
        }
        echo Db::name('dc_agent_account_info')->insertAll($list) . ';' . PHP_EOL;
        //代理升级条件
        $list = [
            [
                'agent_conditions_name' => '满3人且金币消耗满2000',
                'agent_conditions_status' => 1,
                'agent_conditions_type' => 1,
                'agent_conditions_data' => '{"promote_number":5,"gold_consumption":5000}',
            ],
            [
                'agent_conditions_name' => '满5人',
                'agent_conditions_status' => 1,
                'agent_conditions_type' => 2,
                'agent_conditions_data' => '{"promote_number":5}',
            ],
            [
                'agent_conditions_name' => '金币消耗满5000',
                'agent_conditions_status' => 1,
                'agent_conditions_type' => 3,
                'agent_conditions_data' => '{"gold_consumption":5000}',
            ],
        ];
        echo Db::name('dc_agent_conditions')->insertAll($list) . ';' . PHP_EOL;
        //代理选择条件
        $list = [
            [
                'agent_id' => 1,
                'agent_conditions_id' => 1,
                'agentconf_time' => time() - mt_rand(1000, 100000),
            ],
            [
                'agent_id' => 2,
                'agent_conditions_id' => 2,
                'agentconf_time' => time() - mt_rand(1000, 100000),
            ],
            [
                'agent_id' => 3,
                'agent_conditions_id' => 3,
                'agentconf_time' => time() - mt_rand(1000, 100000),
            ],
        ];
        echo Db::name('dc_agent_config')->insertAll($list). ';' . PHP_EOL;
        //代理分成条件 dc_agent_income_config
        $list = [
            [
                'income_agent_id' => 0,
                'income_promote_conut' => 10,
                'income_count_level' => 1,
                'income_agent' => 35,
                'income_level_one' => 10,
                'income_level_two' => 5,
            ],
            [
                'income_agent_id' => 0,
                'income_promote_conut' => 20,
                'income_count_level' => 2,
                'income_agent' => 36,
                'income_level_one' => 10,
                'income_level_two' => 5,
            ],
            [
                'income_agent_id' => 0,
                'income_promote_conut' => 50,
                'income_count_level' => 3,
                'income_agent' => 40,
                'income_level_one' => 13,
                'income_level_two' => 8,
            ],
            [
                'income_agent_id' => 0,
                'income_promote_conut' => 100,
                'income_count_level' => 4,
                'income_agent' => 40,
                'income_level_one' => 13,
                'income_level_two' => 8,
            ],
            [
                'income_agent_id' => 0,
                'income_promote_conut' => 500,
                'income_count_level' => 5,
                'income_agent' => 45,
                'income_level_one' => 15,
                'income_level_two' => 10,
            ],
        ];
        echo Db::name('dc_agent_income_config')->insertAll($list). ';' . PHP_EOL;
        //特代收成表 dc_agent_super_income_config
        $list = [
            [
                'super_agent_id' => 0,
                'super_condition' => 0,
                'super_condition_compare' => '<',
                'super_share' => 500,
            ],
            [
                'super_agent_id' => 0,
                'super_condition' => 10000,
                'super_condition_compare' => '<',
                'super_share' => 1000,
            ],
            [
                'super_agent_id' => 0,
                'super_condition' => 50000,
                'super_condition_compare' => '<',
                'super_share' => 1500,
            ],
            [
                'super_agent_id' => 0,
                'super_condition' => 100000,
                'super_condition_compare' => '<',
                'super_share' => 2000,
            ],
        ];
        echo Db::name('dc_agent_super_income_config')->insertAll($list). ';' . PHP_EOL;
        //奖励金额配置 dc_promoters_award_config
        $list = [
            [
                'award_agent_id' => 0,
                'award_condition' => 10000,
                'award_money' => '350',
            ],
        ];
        echo Db::name('dc_promoters_award_config')->insertAll($list). ';' . PHP_EOL;
        //钱转换 dc_money_rate_info
        $list = [
            [
                'money_rate_type' => 1,
                'money_rate_value' => 10000,
                'money_rate_unit' => 1,
                'money_rate_unit_type' => 1,
                'money_rate_name' => '金币兑换比率',
                'money_rate_param' => '',
            ],
        ];
        echo Db::name('dc_money_rate_info')->insertAll($list). ';' . PHP_EOL;
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
