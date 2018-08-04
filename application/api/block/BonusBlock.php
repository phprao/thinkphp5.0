<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-03-19 19:45:28
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 微信红包
 +---------------------------------------------------------- 
 */
namespace app\api\block;

use app\api\model\WxBonusLogModel;
use think\Log;

/**
 * 逻辑块
 * Class BonusBlock
 */

class BonusBlock
{

    public static function sendBonus($id = 0, $limit = 1)
    {
        $bonusConfig = config('send_bonus_config');
        if(empty($bonusConfig)){
            Log::error('[ BonusBlock ] : 微信红包相关配置加载失败！');
            return false;
        }
        if(!$id){
            Log::error('[ BonusBlock ] : id参数错误');
            return false;
        }
        $bonus = WxBonusLogModel::model()->where(['id'=>$id])->find();
        if(!$bonus){
            return false;
        }

        $requestUrl = $bonusConfig['send_bonus_url'] ;
        $requestUrl .= '?order_id='. $bonus['mch_billno'] ;
        $requestUrl .= '&limit='. $limit ;
        $requestUrl .= '&total_amount='. ( $bonus['total_amount'] / 100 ) ;
        $requestUrl .= '&openid=' . $bonus['openid_gzh'] ;
        $requestUrl .= '&send_config='.$bonusConfig['send_bonus_name'] ;
        $requestUrl .= '&sceneid='.$bonusConfig['send_bonus_scanid'] ;
        $requestUrl .= '&notify_url='.$bonusConfig['send_bonus_callback'] ;
        $requestUrl .= '&descs='.json_encode($bonusConfig['send_bonus_remark']) ;

        $result = file_get_contents($requestUrl);

        //$result = '{"status":"success","errorMsg":"\u53d1\u9001\u7ea2\u5305\u6210\u529f","data":{"return_code":"SUCCESS","return_msg":"\u53d1\u653e\u6210\u529f","result_code":"SUCCESS","err_code":"SUCCESS","err_code_des":"\u53d1\u653e\u6210\u529f","mch_billno":"7d29783d1077dd77060c12be1296","mch_id":"1499523442","wxappid":"wxe282de77253c75f1","re_openid":"o7KoZ03nTP9e79HCmTQEeBxuBJvw","total_amount":"100","send_listid":"1000041701201804173000135690402"}}';

		//$result = '{"status":"error","errorMsg":"\u5df2\u8fbe\u4eca\u65e5\u7ea2\u5305\u53d1\u653e\u6b21\u6570\u8fbe\u4e0a\u9650","data":null}';

        $result = json_decode($result, true);

        return $result;

    }

}