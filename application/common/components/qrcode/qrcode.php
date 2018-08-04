<?php
    $path = dirname(__FILE__);
    include_once($path.('/Phpqrcode/phpqrcode.php'));
    //连接数据库
    @$conn = mysqli_connect('10.66.221.95:3306','root','&5^%1tyu12RW45');
    if(!$conn){
        echo json_encode(array('code'=>0,'message'=>'连接数据库失败'));
        die;
    }
    mysqli_select_db($conn,'dcmjdc') or die('数据库不存在');
    mysqli_query($conn,"set names 'utf8'");

    /***********   分享朋友圈的二维码生成开始   ************/
    //接收参数
    $player_id = $_REQUEST['player_id'];
    //判断二维码是否存在
    $file = 'images/newqrcode/newqrcode_'.($player_id%10).'/'.$player_id.'.png';
    if(!file_exists($file)){
        $player_info = get_guild_info($conn,$player_id);
        if(!$player_info){
            echo json_encode(array('code'=>0,'message'=>'找不到玩家信息'));die;
        }
        //获取文件图片信息
        $url = 'http://wapwx.dachuanyx.com/share/index.php?day=' . $player_info[0]['reg_time'] . '&gid=' . $player_info[0]['promoters_id'] . '&playerid=' . $player_id;

        //生成二维码图片
        $logo = 'images/youjianicon.png';//准备好的logo图片
        $QR = 'images/qrcode/qrcode_' . ($player_id%10) .'/'.$player_id. '.png';//已经生成的原始二维码图
        qccode($url, $QR);

        if ($logo !== FALSE) {
            $imgs = array(
                'dst' => $QR,
                'src' => $logo
            );
            $dests = mergerImg($imgs);
        }
        //输出新的二维码图片
        $newqrcode = 'images/newqrcode/newqrcode_' . ($player_id%10).'/'.$player_id. '.png';
        imagepng($dests, $newqrcode);
    }
    $qrcode = 'http://211.159.217.210:8010/qrcode/images/newqrcode/newqrcode_'.($player_id%10).'/'.$player_id.'.png';//分享朋友圈的二维码地址
    $data = array('code'=>1,'qrcode'=>$qrcode);
    echo json_encode($data);die;

    /***********   分享朋友圈的二维码生成结束   ***********/

    function get_guild_info($conn,$player_id){
        $sql = 'select promoters_id,reg_time from playerinfo a inner join dc_guild_promoters_player b on a.uid=b.player_id where uid = '.$player_id;
        $player_info = mysqli_query($conn,$sql);

        $result = array();
        while ($row = mysqli_fetch_assoc($player_info)) {
            $result[] = $row;
        }

        return $result;
    }

    function qccode($url,$QR,$size=4){
        QRcode::png($url, $QR, 'H', $size, 0);
    }

    function mergerImg($imgs) {
        list($max_width, $max_height) = getimagesize($imgs['dst']);
        $dests = imagecreatetruecolor($max_width, $max_height);

        $dst_im = imagecreatefrompng($imgs['dst']);

        imagecopy($dests,$dst_im,0,0,0,0,$max_width,$max_height);
        imagedestroy($dst_im);

        $src_im = imagecreatefrompng($imgs['src']);
        $src_info = getimagesize($imgs['src']);
        $dst_info = getimagesize($imgs['dst']);

        $QR_width = $dst_info[0];
        $from_width = ($QR_width - ((int)$src_info[0])) / 2;
        //$logo_qr_width = $QR_width / 5;
        //$from_width = ($QR_width - $logo_qr_width) / 2.7;

        imagecopy($dests,$src_im,$from_width,$from_width,0,0,$src_info[0],$src_info[1]);
        imagedestroy($src_im);

        return $dests;
    }
?>