<?php


namespace app\api\block;


class CUrlValidationBlock
{

    public static function validation($sign, $player_id, $time, $key)
    {
        $string = $player_id . $time . $key;
        return $sign === md5($string);
    }

}
