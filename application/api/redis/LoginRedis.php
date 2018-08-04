<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15
 * Time: 16:43
 * @author ChangHai Zhan
 */

namespace app\api\redis;

use think\cache\driver\Redis;

/**
 * Class LoginRedis
 * @package app\api\redis
 * @author ChangHai Zhan
 */
class LoginRedis
{
    /**
     * @var null
     */
    public static $redis = null;

    public static $prefix = 'h5:login:token:';

    /**
     * init
     */
    public static function init()
    {
        if (!self::$redis) {
            self::$redis = new Redis(config('redis_config'));
        }
        return self::$redis;
    }

    /**
     * @param $token
     * @param $data
     * @param int $time
     * @return mixed
     */
    public static function set($token, $data, $time = 86400)
    {
        self::init();
        return self::$redis->set(self::$prefix . $token, $data, $time);
    }

    /**
     * delete
     * @param $token
     * @return mixed
     */
    public static function delete($token)
    {
        self::init();
        return self::$redis->rm(self::$prefix . $token);
    }

    /**
     * @param $token
     * @return mixed
     */
    public static function get($token)
    {
        self::init();
        return self::$redis->get(self::$prefix . $token);
    }
}