<?php

namespace Appserver\Utils;
use \Redis;
class RedisLib
{

    private static $obj = NULL;
    protected $di;

    public function __construct($di)
    {
        $this->di=$di;
        $redisConf=$this->di->get('sysconfig')['redisConf'];
        self::$obj = new Redis();
        self::$obj->connect($redisConf['server'], $redisConf['port'], $redisConf['timeout']);
        self::$obj->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
    }

    /**
     * 获取redis对象
     * @return Redis
     */
    public static function getRedis()
    {
        if(!self::$obj)
        {
            new RedisLib($this->di);
        }
        return self::$obj;
    }

    /**
     * 自增和自减特殊操作，防止执行失败
     */
    public static function autoOption($key, $method)
    {
        $redis = self::getRedis();
        $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        if($method == 'incr')
        {
            return $redis->incr($key);
        }
        elseif($method == 'decr')
        {
            return $redis->decr($key);
        }
    }

    /**
     * 将队列中的数据全部取出放到一个数组里
     * 赞或评论
     * @param str queue
     * @return array
     */
    public static function queueIntoArray($queue, $redisBase)
    {
        $times = date('Y-m-d H:i:s', time());
        $redis = self::getRedis();
        $redis->select($redisBase);
        $num = $redis->lSize($queue);
        $res = array();
        //队列里的信息是json格式的，需要为它反json
        for($i=0;$i<$num;$i++)
        {
        $res[$i] = json_decode($redis->rPop($queue), true);
        }
        return $res;
    }
}