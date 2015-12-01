<?php

namespace Appserver\Mdu\Modules;

class DevtagsModule extends ModuleBase
{

    const SUCCESS = '1';
    const FAILED_UPDATE = 22222;

    protected $devtagsObj;

    public function __construct()
    {
        $this->devtagsObj = $this->initModel('\Appserver\Mdu\Models\DevtagsModel');
    }

            /**
     * [记录设备的devicetoken]
     * @param  [string] $uid  [用户id]
     * @param  [string] $deviceToken [设备标签]
     * @param  [string] $plat [设备类型 1-ios 3-android]
     * @return [type]       [description]
     */
    public function saveDeviceToken($uid, $deviceToken, $plat, $cver)
    {
        //获取用户的免打扰状态
        $RedisObj = new \Appserver\Utils\RedisLib($this->di);
        $redis = $RedisObj::getRedis();
        $disturbInfo = $redis->get('disturb:'.$uid);
        if(!$disturbInfo)
        {
            $disturb = '3'; //默认为3：关闭免打扰
            $start = '';
            $end = '';
        }
        else
        {
            $disturb = $disturbInfo['disturb'];
            $start = $disturbInfo['start'];
            $end = $disturbInfo['end'];
        }

        //记录设备的标签：deviceToken,如果已经有设备标签，则更新登录时间和登录用户的id，没有则插入新数据
        $ResByDevi = $this->devtagsObj->checkToken($deviceToken);
        $ResByUid = $this->devtagsObj->checkTokenById($uid);

        if(empty($ResByDevi) && empty($ResByUid))
        {
            if($this->devtagsObj->addToken($uid, $deviceToken, $plat, $_SERVER['REQUEST_TIME'], $disturb, $start, $end, $cver))
                return array('flag' => self::SUCCESS, 'disturbMode' => $disturb);
            else
                return self::FAILED_UPDATE;
        }
        elseif(!empty($ResByDevi) && empty($ResByUid))
        {
            if($this->devtagsObj->updateToken($uid, $deviceToken, $plat, $_SERVER['REQUEST_TIME'], $disturb, $cver))
                return array('flag' => self::SUCCESS, 'disturbMode' => $disturb);
            else
                return self::FAILED_UPDATE;
        }
        elseif(empty($ResByDevi) && !empty($ResByUid))
        {
            if($this->devtagsObj->updateTokenById($uid, $deviceToken, $plat, $_SERVER['REQUEST_TIME'], $disturb, $cver))
                return array('flag' => self::SUCCESS, 'disturbMode' => $disturb);
            else
                return self::FAILED_UPDATE;
        }
        elseif(!empty($ResByDevi) && !empty($ResByUid))
        {
            if($ResByDevi['u_id'] != $ResByUid['u_id'])
                $this->devtagsObj->del($uid);

            if($this->devtagsObj->updateToken($uid, $deviceToken, $plat, $_SERVER['REQUEST_TIME'], $disturb, $cver))
                return array('flag' => self::SUCCESS, 'disturbMode' => $disturb);
            else
                return self::FAILED_UPDATE;
        }
    }
}