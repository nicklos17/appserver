<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\SwooleUserClient,
    Appserver\Utils\RedisLib;


class MyselfModule extends ModuleBase
{

    const SUCCESS = '1';
    const UNTOKEN = '00000';
    const NON_EXIST_MOBILE = '00001';
    const FAILED_GET = 22222;
    const FAILED_SET_DISTURB = 11062;
    const SAME_MODE = 11063;

    private $ver;

    /**
     * 返回当前用户的云币数量和等级
     */
    public function getUserLevelInfo($di, $userInfo)
    {
        $swoole = new SwooleUserClient(
            $di['sysconfig']['swooleConfig']['ip'],
            $di['sysconfig']['swooleConfig']['port']
        );
        $res = $swoole->getUserInfoByMobi($userInfo['mobi']);
        if(empty($res['data']))
            return self::NON_EXIST_MOBILE;

        //获取用户当前云币数量
        $coins = $swoole->coinsInfo($userInfo['uid']);
        if(empty($coins['data']))
            return self::NON_EXIST_MOBILE;

        return array('flag' => '1', 'coins' => $coins['data']['uw_coins'], 'level' => $res['data']['u_level']);
    }

    /**
     * [开启/关闭免打扰模式]
     * @param  [string]  $disturb [1-开启, 3-关闭]
     * @param  [string] $start   [开启时间，开启模式下有值]
     * @param  [string] $end     [截止时间，开启模式下有值]
     * @return [type]           [description]
     */
    public function disturb($uid, $disturb, $start = 0, $end = 0)
    {
        $this->devtagsObj = $this->initModel('\Appserver\Mdu\Models\DevtagsModel');
        //获取当前用户的免打扰模式是开启还是关闭
        $status = $this->devtagsObj->checkDisturb($uid);
        if(!empty($status))
        {
            if($status['dt_disturb'] == $disturb)
                return self::SAME_MODE;

                //读取redis
                $redisObj = new RedisLib($this->di);
                $redis = $redisObj->getRedis();
                $oldInfo = $redis->get('disturb:'.$uid);
            if(!empty($oldInfo) && $disturb == '3')
            {
                $start = $oldInfo['start'];
                $end = $oldInfo['end'];
            }

            if($this->devtagsObj->setDisturb($uid, $disturb, $start, $end) > 0)
            {
                //写入redis
                $redis->set('disturb:'.$uid, array('disturb' => $disturb, 'start' => $start, 'end' => $end));
                return self::SUCCESS;
            }
            else
                return self::FAILED_SET_DISTURB;
        }
            return self::UNTOKEN;
    }

}