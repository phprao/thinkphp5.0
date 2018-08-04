<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/10
 * Time: 10:41
 * @author
 */

namespace app\admin\block;

use app\admin\model\AgentInfoModel;
use app\admin\model\UsersModel;
use app\admin\redis\LoginRedis;

/**
 * 逻辑块
 * Class DemoBlock
 * @author ChangHai Zhan
 */
class DataArrange
{
    /**
     * 堆叠图数据排序
     * @param $data
     * @param $total
     * @return array
     */
    //数据排序
    public static function dataSortDesc($data,$total){
        //将数据按当前日期倒序输出
        $tmp = array($data,$total);
        $tmp_arr = array();
        for($i = 0; $i < count($data); $i ++) {
            $tmp_arr[] = array_column($tmp,$i);
        }

        //'$tmp_arr' 数组中,键名为'data'的数组长度减去 1 作为 '$tmp_arr[$k][1]['data']' 的键名
        $tmp_final_key = count($tmp_arr[0][1]['data'])-1;
        $len = count($tmp_arr);
        //排序倒序
        for($j = 1; $j < $len; $j ++){
            for($k = 0; $k < $len-$j; $k ++){
                if($tmp_arr[$k][1]['data'][$tmp_final_key] < $tmp_arr[$k + 1][1]['data'][$tmp_final_key]){
                    $temp = $tmp_arr[$k + 1];
                    $tmp_arr[$k + 1] = $tmp_arr[$k] ;
                    $tmp_arr[$k] = $temp;
                }
            }
        }
        $gameId = $total = array();
        foreach ($tmp_arr as $key => $value) {
            foreach ($value as $k => $v) {
                if(!is_array($v)){
                    $gameId[] = $v;
                }else{
                    $total[] = $v;
                }
            }
        }

        $res['gameid'] = $gameId;
        //每天各游戏的数据
        $res['total'] = $total;
        return $res;
    }


}