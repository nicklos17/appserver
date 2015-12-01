<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\RedisLib;

class VerModule extends ModuleBase
{

    const SUCCESS = '1';
    const FAILED_GET = 22222;

    private $ver;

    public function __construct()
    {
        $this->ver = $this->initModel('\Appserver\Mdu\Models\VerModel');
    }

    /**
     * [软件更新]
     * @param  [string] $type [1-ios，3-android]
     * @param  [string] $ver  [当前请求的软件版本号]
     * @return [string|array]
     */
    public function getSoftVerInfo($type, $ver)
    {
        //获取数据库的最高版本记录
        $result = $this->ver->getVerByType($type);
        if(!$result)
            return self::FAILED_GET;
        else
        {
            //请求的版本
            $ver = explode('.', $ver);

            //数据库里的最高版本
            $baseVer = explode('.', $result['version']);

            if($ver[0] > $baseVer[0])
            {
                $result['version'] = '-1';
                $result['logs'] = '';
                $result['url'] = '';
            }
            elseif($ver[0] == $baseVer[0])
            {
                //比较第二段
                if($ver[1] > $baseVer[1])
                {
                    $result['version'] = '-1';
                    $result['logs'] = '';
                    $result['url'] = '';
                }
                elseif($ver[1] == $baseVer[1])
                {
                    if(!((!isset($ver[2]) && isset($baseVer[2])) || (isset($ver[2]) && isset($baseVer[2]) &&($ver[2] < $baseVer[2]))))
                    {
                        $result['version'] = '-1';
                        $result['logs'] = '';
                        $result['url'] = '';
                    }
                }
            }
        }
        return array('flag' => self::SUCCESS, 'verlist' => array($result));
    }

    /**
     * [硬件更新]
     * @return [type] [description]
     */
    public function getHardInfo($time, $uid)
    {
        $redisObj = new RedisLib($this->di);
        $redis = $redisObj->getRedis();
        $updateData = $redis->get('nearlyHardUp');

        if(empty($updateData) || $time < $updateData['time'])
        {
            $url = dirname(dirname(dirname(__FILE__))) . '/' . $this->di['sysconfig']['hardInfoUrl'];
            $files = scandir($url);
            
            if(!empty($files))
            {
                foreach($files as $val)
                {
                    if($val !== '.' && $val !== '..')
                        $res[] = substr($val, 0, strlen($val) - 4);
                }
                $hardUpTime = max($res);
                //检查该用户是否进行过新版本的推送
                if(!$redis->get('userHardUp:' . $uid . ':' . $hardUpTime))
                {
                    $redis->del('userHardUp:' . $uid . ':' . $time);
                    $redis->set('userHardUp:' . $uid . ':' . $hardUpTime, 1);
                    $display = '1';
                    
                    if(!isset($updateData['time']) || $hardUpTime > $updateData['time'])
                    {
                        $logs = file_get_contents($url . '/' . $hardUpTime . '.txt');
                        $redis->set('nearlyHardUp', array('time' => $hardUpTime, 'logs' => $logs));
                    }
                    else
                    {
                        $logs = $updateData['logs'];
                    }
                }
                else
                {
                    $logs = '';
                    $display = '3';
                }
            }
            else
            {
                $hardUpTime = $time;
                $logs = '';
                $display = '3';
            }
        }
        else
        {
            $hardUpTime = $time;
            $logs = '';
            $display = '3';
        }
        
        return array('flag' => '1', 'logs' => $logs, 'time' => (string)$hardUpTime, 'display' => $display);
    }
}