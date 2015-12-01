<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\RedisLib,
    Appserver\Utils\Common,
    Appserver\Utils\UserHelper,
    Appserver\Utils\SwooleUserClient;

class CheckinModule extends ModuleBase
{

    const HAVE_CHECKINED = 11011;
    const FAILED_CHECKIN = 11012;
    const FAILED_GET_DATA = 22222;
    const FAILED_GET_COINS = 11024;

    public $checkinlogs;

    public function __construct()
    {
        $this->checkinlogs = $this->initModel('\Appserver\Mdu\Models\CheckinlogsModel');
    }

    /**
     * [用户签到]
     * @param  [type] $uid   [用户id]
     * @param  [type] $level [用户当前等级]
     * @return [type]        [description]
     */
    public function userCheckin($uid, $level)
    {
        //第二天零点
        $tomorrow = strtotime(date('Y-m-d', strtotime('+1 day')));
        //第二天连续签到的最后时刻
        $finalSign = strtotime(date('Y-m-d', strtotime('+2 day')))-1;
        $nowtime = $_SERVER['REQUEST_TIME'];

        $redisObj = new RedisLib($this->di);
        $redis = $redisObj->getRedis();

        //用redis设置签到状态值，如果这个这key存在表示今天已经签到，不存在表示尚未签到
        $checkinStatus = sprintf($this->di['sysconfig']['checkinStatus'], $uid);
        if($redis->get($checkinStatus))
            return self::HAVE_CHECKINED;

        //redis签到的key
        $signKey = sprintf($this->di['sysconfig']['signcount'], $uid);
        //multi是否处理是否连续签到，连续+1,不连续值为1
        $redis->multi()->incr($signKey)->setTimeout($signKey, $finalSign-$nowtime)->exec();
        //获得连续签到天数
        $seriesCheckin = $redis->get($signKey);
        //签到奖励的云币数
        $coins = Common::checkinCoin($this->di, $seriesCheckin);

        if($this->checkinlogs->addCheckin($uid, $nowtime, $coins) == 0)
            return self::FAILED_CHECKIN;

        //获取用户签到总天数
        $totalDays = $redis->get($uid .':'. $this->di['sysconfig']['checkinTotal']);
        if($totalDays == FALSE)
        {
            $totalDays = '0';
        }
        //计算用户升级到下一级所需总签到天数
        $needDays = UserHelper::levelCheck($level);

        $swoole = new SwooleUserClient($this->di['sysconfig']['swooleConfig']['ip'], $this->di['sysconfig']['swooleConfig']['port']);

        if($totalDays <= 1050)
        {
            if($totalDays > $needDays)
            {
                //更新用户等级
                $res = $swoole->updateLevel($uid);
                if($res['data'] == 0)
                    return self::FAILED_GET_DATA;

                $level = $this->userInfo['level']+1;
            }
        }
        //发放奖励
        $res = $swoole->checkInReceive($uid, $coins);
        if($res['data'] == 1)
        {
            //设置签到状态:置1表示已签到
            $redis->setex($checkinStatus, $tomorrow-$nowtime, '1');
            //用redis保存签到总天数
            $redis->set($uid .':'.  $this->di['sysconfig']['checkinTotal'], $totalDays+1);
            return array('flag' => '1', 'checkindays' => (string)$seriesCheckin, 'coins' => (string)$coins, 'level' => (string)$level);
        }
        else
            return self::FAILED_GET_COINS;
    }
}