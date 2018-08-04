<?php
/**
 +---------------------------------------------------------- 
 * date: 2018-03-23 14:22:36
 +---------------------------------------------------------- 
 * author: Raoxiaoya
 +---------------------------------------------------------- 
 * describe: 渠道后台--推广
 +---------------------------------------------------------- 
 */

namespace app\admin\controller\v1;
use app\common\components\Helper;
use app\admin\controller\Controller;
use app\admin\model\AgentInfoModel;
use app\admin\model\ChannelPromotionModel;
use app\admin\model\ConfigModel;
use app\admin\model\ChannelInfoModel;
use app\admin\model\ChannelCoinsLogModel;
use app\admin\model\ChannelGiftModel;
use app\admin\model\PlayerModel;
use app\admin\model\SysCoinChangeLog;
use think\Db;
use think\Log;

/**
 * @controllerName 渠道后台推广模块
 */

class Promotion extends Controller
{
	protected $size = 10 ;
    protected $error_code = 0;
    protected $error_msg = '';
    /**
     * 初始化操作
     * @access protected
     */
    protected function _initialize()
    {
        parent::_initialize();
        $userInfo = $this->isLogin($this->token);
        if(isset($userInfo['agentInfo'])){
            $this->channel_id = $userInfo['agentInfo']['agent_id'];
        }else{
            $this->channel_id = 0;
        }
    }

    protected function initRequestParam(){
        $input = array(
            'keyword'   =>(string)$this->request->get('keyword'),
            'page'      =>(int)$this->request->get('page'),
            'size'      =>(int)$this->request->get('size'),
        );
        /*
        $input = array(
            'keyword'   =>'',// 玩家ID  601709
            'page'      =>1,
            'size'      =>'10'
        );
        */
        $filter = array();

        if($input['keyword'] !== ''){
            $filter['keyword'] = $input['keyword'];
        }

        if($input['page']){
            $filter['page'] = $input['page'];
        }else{
            $filter['page'] = 1;
        }

        if($input['size']){
            $filter['size'] = $input['size'];
        }else{
            $filter['size'] = $this->size;
        }

        return (object)$filter;
        
    }

    /**
     * @actionName 推广教程
     */
    public function promotionCourse(){
        
    }

    /**
     * @actionName 发展星级推广员
     */
    public function promotionStar(){
        $condition = $this->initRequestParam();
    	if(!$this->channel_id){
            return $this->sendError(10000, 'channel_id参数错误');
        }else{
            $condition->channel_id = $this->channel_id;
        }
        $list = ChannelPromotionModel::model()->getList($condition);
        if($list){
            foreach($list->items() as $item){
                $item->promotion_image = request()->domain() . request()->root() . '/' . $item->promotion_image;
            }
        }
        
        return $this->sendSuccess(['list' => $list]);
    }

    /**
     * @actionName 为渠道生成推广图片--推广普通玩家
     */
    public function createQrcode(){
        $channel_id = $this->request->get('channel_id');
        if(!$channel_id || !is_numeric($channel_id)){
            return $this->sendError(10000, 'channel_id参数错误');
        }
        if(!AgentInfoModel::model()->getInfo(['agent_id'=>$channel_id,'agent_level'=>1])){
            return $this->sendError(10000, '该渠道不存在');
        }

        // 合并图片
        $conf = ConfigModel::model()->getFint(['config_name'=>'channel_promotion_pic_url','config_status'=>1]);
        if(!$conf){
            return $this->sendError(10000, '渠道的推广图背景图没有配置');
        }
        $conf = json_decode($conf['config_config'],true);
        $background = $conf['image_url'];

        //先删除记录
        $where = ['promotion_channel_id'=>$channel_id,'promotion_name'=>'king_promotion'];
        ChannelPromotionModel::model()->delInfo($where);
        if($background){
            $return_data = array();
            foreach ($background as $key => $value) {
				$encrypt = Helper::think_encrypt($channel_id, 'dc_channel');
                $encrypt_url = $encrypt.'_'.$key;
                $url     = request()->domain() . request()->root() . '/api/v1/promoter/channel_player?channel_param='.$encrypt;
                $path    = 'channel/promotion_img/qrcode/channel_'.$channel_id.'_'.$key.'.png';
                $image   = 'channel/promotion_img/promotion/'.$encrypt_url.'.png';
                // 生成二维码
                switch ($key) {
                    case 0:
                        $size = 4;
                        break;
                    case 1:
                        $size = 4;
                        break;
                    case 2:
                        $size = 3.2;
                        break;
                    case 3:
                        $size = 3.2;
                        break;
                    case 4:
                        $size = 3.2;
                        break;
                    case 5:
                        $size = 3.2;
                        break;
                }
                $qrcoderes = Helper::generateQrcodePng($url, $path, ['level'=>0,'size'=> $size,'margin'=>4]);
                
                // 写入数据库,生成三个二维码,先删除，后insert
                $re = ChannelPromotionModel::model()->isUpdate(false)->save(
                    [
                        'promotion_channel_id' =>$channel_id,
                        'promotion_url'        =>$url,
                        'promotion_image'      =>$image,
                        'promotion_desc'       =>'幸运王国渠道推广--推广普通玩家',
                        'promotion_name'       =>'king_promotion',
                        'promotion_time'       =>time()
                    ]
                );

                //合成图片，三张
                $img = $this->composeImage($path,$value,$image,$key);
                $return_data[$key] = request()->domain() . request()->root() . '/' . $img;
            }
        }

        return $this->sendSuccess(['promotion_image'=>$return_data]);
    }

    /**
     * @actionName 新手礼包-渠道剩余金币数
     */
    public function channelInfo(){
        if(!$this->channel_id){
            return $this->sendError(10000, '渠道信息错误');
        }
        $ChannelInfo = ChannelInfoModel::model()->getOne(['channel_id'=>$this->channel_id]);
        if(!$ChannelInfo){
            $ChannelInfo = ['id'=>0,'channel_id'=>$this->channel_id,'channel_coins'=>0,'add_time'=>0];
        }
        return $this->sendSuccess(['list'=>$ChannelInfo]);
    }

    /**
     * @actionName 新手礼包-生成礼包
     */
    public function promotionGift(){
        $gift_value = $this->request->get('gift_value');
        $gift_count = $this->request->get('gift_count');
        if(!$this->channel_id){
            return $this->sendError(10000, '渠道信息错误');
        }
        if(!is_numeric($gift_value) || $gift_value <= 0 || !Helper::checkString($gift_value, 4)){
            return $this->sendError(10000, '礼包金币数必须大于零的整数');
        }else{
            $gift_value = (int)$gift_value;
        }
        if(!is_numeric($gift_count) || $gift_count <= 0 || !Helper::checkString($gift_value, 4)){
            return $this->sendError(10000, '礼包领取人数数必须大于零的整数');
        }else{
            $gift_count = (int)$gift_count;
        }
        if($gift_value % $gift_count != 0){
            return $this->sendError(10000, '礼包金币数与人数不匹配，需要能整除');
        }
        if($gift_count > 9999){
            return $this->sendError(10000, '礼包领取人数需在1-9999之间');
        }
        if($gift_value > 2000000){
            return $this->sendError(10000, '礼包金币数需在1-200万之间');
        }

        $ChannelInfo = ChannelInfoModel::model()->getOne(['channel_id'=>$this->channel_id]);
        if(!$ChannelInfo || $ChannelInfo['channel_coins'] <= 0){
            return $this->sendError(10002, '该渠道暂无金币可用');
        }
        if($ChannelInfo['channel_coins'] < $gift_value){
            return $this->sendError(10002, '该渠道金币不足');
        }

        $dir = ROOT_PATH . '/public/channel/promotion_gift';
        if(!is_dir($dir)){
           $res = mkdir($dir, 0777, true); 
           if(!$res){
                return $this->sendError(10002, '创建目录失败，请设置可写权限');  
           }
        }

        Db::startTrans();

        $r1 = ChannelInfoModel::model()->where(['channel_id'=>$this->channel_id])->setDec('channel_coins', $gift_value);

        $r2 = ChannelGiftModel::model()->insertOne([
            'gift_channel_id'   => $this->channel_id,
            'gift_value'        => $gift_value,
            'gift_count'        => $gift_count,
            'gift_single_value' => (int)($gift_value / $gift_count),
            'gift_time'         => time(),
            'gift_date'         => date('Y-m-d H:i:s')
        ]);

        $r3 = ChannelCoinsLogModel::model()->insertOne([
            'channel_id'                => $this->channel_id,
            'channel_coins_before'      => $ChannelInfo['channel_coins'],
            'channel_coins_change'      => $gift_count,
            'channel_coins_after'       => $ChannelInfo['channel_coins'] - $gift_count,
            'channel_coins_change_type' => 1,
            'channel_coins_gift_id'     => $r2,
            'add_time'                  => time(),
            'add_date'                  => date('Y-m-d H:i:s')
        ]);

        $encrypt = Helper::think_encrypt($r2, 'dc_channel_gift');
        $r_url   = 'api/v1/promoter/channel_gift?channel_param='.$encrypt;
        $url     = request()->domain() . request()->root() . '/' . $r_url;
        $path    = 'channel/promotion_gift/gift_' . $encrypt . '.png';
        $qrcoderes = Helper::generateQrcodePng($url, $path, ['level'=>0,'size'=> 5,'margin'=>3]);
        $r4 = ChannelGiftModel::model()->save(['gift_url'=>$r_url, 'gift_url_image'=>$path], ['gift_id'=>$r2]);

        if($r1 && $r2 && $r3 && $r4){
            Db::commit();
            return $this->sendSuccess();
        }else{
            Db::rollback();
            return $this->sendError(10003, '操作失败');
        }
    }

    /**
     * @actionName 新手礼包-列表
     */
    public function promotionGiftList(){
        $condition = $this->initRequestParam();
        if ($condition === false) {
            return $this->sendError($this->error_code, $this->error_msg);
        }
        if(!$this->channel_id){
            return $this->sendError(10000, '渠道信息错误');
        }
        $gift_status = (int)$this->request->get('gift_status');
        $value_start = $this->request->get('value_start');
        $value_end   = $this->request->get('value_end');

        if(!in_array($gift_status, [0,1,2,3])){
            return $this->sendError(10000, '礼包类型错误');
        }

        if($value_start){
            if($value_start < 0 || !Helper::checkString($value_start, 4)){
                return $this->sendError(10000, '金币数参数错误start');
            }else{
                $value_start = (int)$value_start;
                $condition->value_start = $value_start;
            }
        }
        
        if($value_end){
            if($value_end <= 0 || !Helper::checkString($value_end, 4)){
                return $this->sendError(10000, '金币数参数错误end');
            }else{
                $value_end = (int)$value_end;
                $condition->value_end = $value_end;
            }
        }

        if($value_start && $value_end && $value_start > $value_end){
            return $this->sendError(10000, '金币数参数错误');
        }

        if($gift_status > 0){
            $condition->gift_status = $gift_status;
        }

        $condition->gift_channel_id = $this->channel_id;
        
        $list = ChannelGiftModel::model()->getList($condition);
        if($list){
            foreach($list->items() as $item){
                switch($item->gift_status){
                    case 1:
                        $item->gift_status_msg = '未领完';
                        break;
                    case 2:
                        $item->gift_status_msg = '已领完';
                        break;
                    case 3:
                        $item->gift_status_msg = '失效';
                        break;
                }
                $item->gift_url       = request()->domain() . request()->root() . '/' . $item->gift_url;
                $item->gift_url_image = request()->domain() . request()->root() . '/' . $item->gift_url_image;
            }
        }

        return $this->sendSuccess(['list' => $list]);

    }

    /**
     * @actionName 新手礼包-礼包详情
     */
    public function promotionGiftDetail(){
        $condition = $this->initRequestParam();
        if ($condition === false) {
            return $this->sendError($this->error_code, $this->error_msg);
        }

        $gift_id = (int)$this->request->get('gift_id');
        $last_id = (int)$this->request->get('last_id');// 翻页使用
        if($last_id){
            $condition->last_id = $last_id;
        }

        $list = SysCoinChangeLog::model()->getList(['mode'=>2, 'mode_id'=>$gift_id], $condition);

        if($list){
            foreach($list->items() as $item){
                $player = PlayerModel::model()->getPlayerinfoById($item->player_id);
                $item->nickname = urldecode($player['player_nickname']);
            }
        }

        return $this->sendSuccess(['list' => $list]);
    }

    /**
     * @actionName 新手礼包-礼包失效
     */
    public function cancelChannelGift(){
        $gift_id = $this->request->get('gift_id');
        if (!$gift_id) {
            return $this->sendError(10000, '新手礼包参数错误');
        }
        Log::error('[ cancelChannelGift ] : 礼包失效 | '.$gift_id);
        $gift_id = (int)$gift_id;
        $giftInfo = ChannelGiftModel::model()->getOne(['gift_id' => $gift_id, 'gift_status' => ['neq', 3]]);
        if (!$giftInfo) {
            return $this->sendError(10000, '该新手礼包不存在或已撤销');
        }
        if($this->channel_id != $giftInfo['gift_channel_id']){
            return $this->sendError(10000, '该新手礼包不是该渠道的');
        }
        $ChannelInfo = ChannelInfoModel::model()->getOne(['channel_id'=>$giftInfo['gift_channel_id']]);
        if(!$ChannelInfo){
            return $this->sendError(10000, '该新手礼包渠道信息不存在');
        }
        // 执行撤销
        Db::startTrans();
        $giftInfoLock = ChannelGiftModel::model()->getGiftInfoByIdLock($gift_id);
        if ($giftInfoLock['gift_status'] == 2 || $giftInfoLock['gift_count_receive'] >= $giftInfoLock['gift_count']) {
            return $this->sendError(10000, '该新手礼包已领完，无法撤销~');
        } else {
            $gift_r1 = ChannelGiftModel::model()->save(['gift_status'=>3,'gift_cancel_time'=>date('Y-m-d H:i:s')],['gift_id'=>$gift_id]);
            // 返还金币
            $coin = ($giftInfoLock['gift_count'] - $giftInfoLock['gift_count_receive']) * $giftInfoLock['gift_single_value'];
            $r2 = ChannelInfoModel::model()->where(['channel_id'=>$giftInfo['gift_channel_id']])->setInc('channel_coins', $coin);
            $r3 = ChannelCoinsLogModel::model()->insertOne([
                'channel_id'                => $giftInfo['gift_channel_id'],
                'channel_coins_before'      => $ChannelInfo['channel_coins'],
                'channel_coins_change'      => $coin,
                'channel_coins_after'       => $ChannelInfo['channel_coins'] + $coin,
                'channel_coins_change_type' => 3,
                'channel_coins_gift_id'     => $gift_id,
                'add_time'                  => time(),
                'add_date'                  => date('Y-m-d H:i:s')
            ]);
            if ($gift_r1 && $r2 && $r3) {
                Db::commit();
                return $this->sendSuccess();
            } else {
                Db::rollback();
                Log::error('[ cancelChannelGift ] : 礼包撤销失败 | gift_id = ' . $gift_id);
                return $this->sendError(10002, '系统繁忙，请稍后重试');
            }
        }
    }

    /**
     * 合成图片
     * @param  $path 二维码
     * @param  $bg 背景图
     * @param  $type 合成图片类型
     * @return string $image
     */
    protected function composeImage($path,$bg,$image,$type){
        $qrcode = $path;

        if(!file_exists($bg)){
            return $this->sendError(10000, '渠道的推广图背景图不存在'.$bg);
        }
        $bfun = $this->getFunName($bg);

        if(!$bfun){
            return $this->sendError(10000, '渠道的推广图背景图格式不对');
        }
        $bImage = $bfun($bg);// 背景图

        $ufun = $this->getFunName($qrcode);
        $uImage = $ufun($qrcode);//二维码
        switch ($type) {
            case 0:
                imagecopymerge($bImage,$uImage,454,797,0,0,196,196,100);
                break;
            case 1:
                // imagecopymerge($bImage,$uImage,200,816,0,0,343,343,100);
                imagecopymerge($bImage,$uImage,270,990,0,0,196,196,100);
                break;
            case 2:
                // imagecopymerge($bImage,$uImage,200,816,0,0,343,343,100);
                imagecopymerge($bImage,$uImage,445,970,0,0,147,147,100);
                break;
            case 3:
                // imagecopymerge($bImage,$uImage,200,816,0,0,343,343,100);
                imagecopymerge($bImage,$uImage,445,970,0,0,147,147,100);
                break;
            case 4:
                // imagecopymerge($bImage,$uImage,200,816,0,0,343,343,100);
                imagecopymerge($bImage,$uImage,445,970,0,0,147,147,100);
                break;
            case 5:
                // imagecopymerge($bImage,$uImage,200,816,0,0,343,343,100);
                imagecopymerge($bImage,$uImage,445,970,0,0,147,147,100);
                break;
            default:
                return $this->sendError(10000, '合成图片的传入参数错误');
                exit;
                break;
        }
        imagejpeg($bImage,$image);

        imagedestroy($bImage);
        imagedestroy($uImage);
        return $image;
    }


    protected function getFunName($filename){
        $upath = pathinfo($filename);
        $uext = $upath['extension'];
        if(!in_array($uext,array('png','jpg','jpeg','gif'))){
            return false;
        }else{
            if($uext == 'png') $bfun = 'imagecreatefrompng';
            if($uext == 'jpg' || $uext == 'jpeg' ) $bfun = 'imagecreatefromjpeg';
            if($uext == 'gif') $bfun = 'imagecreatefromgif';
        }
        return $bfun;
    }
}