<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\UserHelper,
Appserver\Utils\RpcService,
    Appserver\Utils\RedisLib;

class HealthyModule extends ModuleBase
{
    const SUCCESS = '1';
    const NON_BABY = 10021;

    public function __construct()
    {
        $this->babyRanks = $this->initModel('\Appserver\Mdu\Models\BabyRanksModel');
        $this->baby = $this->initModel('\Appserver\Mdu\Models\BabyModel');
        $this->family = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
        $this->usertasks = $this->initModel('\Appserver\Mdu\Models\DevicesModel');
        $this->babysteps = $this->initModel('\Appserver\Mdu\Models\BabyStepsModel');
    }

    /**
     * [宝贝信息汇总]
     * @param  [string] $babyId [宝贝id]
     * @return [type]         [description]
     */
    public function summary($babyId)
    {
        $babyInfo = $this->baby->getBabyName($babyId);
        if (empty($babyInfo))
            return self::NON_BABY;

        //获取同龄宝贝的平均步数
        $avgSteps = UserHelper::getAvgSteps($this->di, date('Y', $_SERVER['REQUEST_TIME']) - date('Y', $babyInfo['baby_birthday']));
        $summary = $this->babyRanks->getSummary($babyId);
        if (!empty($summary))
        {
            return array(
                'flag' => '1',
                'miles' => $summary['miles'],
                'days' => $summary['days'],
                'total_steps' => $summary['total_steps'],
                'avg_steps' => $summary['avg_steps'],
                'max_steps' => $summary['max_steps'],
                'avg_active' => $summary['avg_active'],
            );
        }
        else
        {
            return array(
                'flag' => '1',
                'miles' => '',
                'days' => '',
                'total_steps' => '',
                'avg_steps' => '',
                'max_steps' => '',
                'avg_active' => ''
            );
        }
    }

    /**
     * 获取健康数据列表
     * @param  [string] $babyId [宝贝id]
     * @param  [string] $count  [查询条数]
     * @param  [string] $date   [起始时间]
     * @return [type]         [description]
     */
    public function getHealthyList($babyId, $count, $date)
    {

        $steplist = $this->healthyList($babyId, $count, $date);

        //获取同龄宝贝的平均步数
        $babyInfo = $this->baby->getBabyName($babyId);
        if (empty($babyInfo))
            return self::NON_BABY;

        $avgSteps = UserHelper::getAvgSteps($this->di, date('Y', $_SERVER['REQUEST_TIME']) - date('Y', $babyInfo['baby_birthday']));

        $steplist = array_reverse($steplist);
        //如果一直没有使用，则返回最近的一条数据
        if(!empty($steplist))
        {
            // 获取活跃度图片; 如果没有设定目标，则返回同龄小孩的平均步数
            foreach($steplist as $k => $v)
            {
                if (in_array($steplist[$k]['active'], $this->di['sysconfig']['active']['hardly']))
                {
                    $active = 'hardly';
                }
                elseif(in_array($steplist[$k]['active'], $this->di['sysconfig']['active']['normal']))
                {
                    $active = 'normal';
                }
                elseif(in_array($steplist[$k]['active'], $this->di['sysconfig']['active']['most']))
                {
                    $active = 'most';
                }
                if(empty($steplist[$k]['goal']))
                {
                    isset($steplist[$k-1]['goal']) ? $steplist[$k]['goal'] = $steplist[$k-1]['goal'] : $steplist[$k]['goal'] = $avgSteps;
                }
                $steplist[$k]['baby_pic'] = $this->di['sysconfig']['activePicServer'] . sprintf($this->di['sysconfig']['activePic'][$babyInfo['baby_sex']], $active);
                $steplist[$k]['advice'] = $this->di['sysconfig']['activeAdvice'][$active];
            }
        }
        
        $steplist = array_reverse($steplist);
        return array(
                'flag' => '1',
                'list' => $steplist,
                'max_steps' => (string)$this->di['sysconfig']['maxSteps'],
                'avg_steps' => (string)$avgSteps
        );
    }

    /**
     * 获取健康数据列表v0.3版本
     * @param  [string] $babyId [宝贝id]
     * @param  [string] $count  [查询条数]
     * @param  [string] $date   [起始时间]
     * @return [type]         [description]
     */
    public function getHealthyListFor03($babyId, $count, $date)
    {
        return $this->healthyList($babyId, $count, $date);
    }

    /**
     * 获取健康数据原始列表
     * @param  [string] $babyId [宝贝id]
     * @param  [string] $count  [查询条数]
     * @param  [string] $date   [起始时间]
     * @return [type]         [description]
     */
    public function healthyList($babyId, $count, $date)
    {
        //获取今天零时的时间戳
        $today = strtotime(date('Y-m-d', $_SERVER['REQUEST_TIME']));
        if ($date == 0 || $date == $today)
        {
            //获取给宝贝设置的目标步数
            $goal = $this->baby->getStepsGoal($babyId)['goal'];
            $steplist = $this->babysteps->getStepsList($babyId, $count);
            if(!empty($steplist))
            {
                //如果第一条数据不是今天的，则增加返回今天的数据
                if($steplist[0]['date'] != $today)
                {
                    $todayData = array(
                        'bs_id' => '0',
                        'date' => (string)$today,
                        'steps' => '0',
                        'calory' => '0',
                        'mileages' => '0',
                        'active' => '0',
                        'goal' => (string)$goal,
                        'advice' => '',
                        'baby_pic' => ''
                );
                    
                    unset($steplist[$count-1]);
                    array_unshift($steplist, $todayData);
                }
            }
            else
            {
                //宝贝刚创建，没有记录时，创建一条虚假的记录
                $steplist[0] = array(
                        'bs_id' => '0',
                        'date' => (string)strtotime(date('Y-m-d', $_SERVER['REQUEST_TIME'])),
                        'steps' => '0',
                        'calory' => '0',
                        'mileages' => '0',
                        'active' => '0',
                        'goal' => (string)$goal,
                        'advice' => '',
                        'baby_pic' => ''
                );
            }
        }
        else
            $steplist = $this->babysteps->getStepsListByDate($babyId, $date, $count);

        return $steplist;
    }

    /**
     * [获取天气预报信息]
     * @param  [type] $babyId [description]
     * @param  [type] $city   [description]
     * @return [type]         [description]
     */
    public function getFore($babyId, $city)
    {
        $babyInfo = $this->baby->getBabyName($babyId);
        try
        {
            $rpcConnect = new \Appserver\Utils\RpcService($this->di['sysconfig']['thriftConf']['ip'], $this->di['sysconfig']['thriftConf']['port']);
            $location = $rpcConnect->GetBabyLocation($babyId);
        }
        catch(\Exception $e)
        {
            continue;
        }

        //如果从babyid找不到地理位置，则根据请求的地理位置计算天气
        if(!empty($location))
        {
            $location = json_decode($location, true);
            $city = $location['city'];
        }

        if($city != '')
        {
            //获取地区编码
            $areaid = UserHelper::getAreaCode($this->di, $city);
            if(!$areaid)
            {
                $city = substr($city, 0, -3);
                $areaid = UserHelper::getAreaCode($this->di, $city);
            }
        }

        if($city == '' || !$areaid)
        {
            return array(
                    'flag' => '1',
                    'area' => $city,
                    'aqi' => '',
                    'qlt' => '',
                    'h_temp' => '',
                    'l_temp' => '',
                    'weat_pic' => '',
                    'weather' => '',
                    'baby_pic' => ''
            );
        }

        $redisObj = new RedisLib($this->di);
        $redis = $redisObj->getRedis();
        $weatherData = $redis->get($this->di['weatherConfig']['weatherData'] . $areaid);
        if(empty($weatherData))
        {
            //缓存过期,重新获取数据
            $data = UserHelper::rollCurlRequest(array(
                            'aqi' => sprintf($this->di['weatherConfig']['pm25']['url'], urlencode($city)),
                            'fore' => sprintf($this->di['weatherConfig']['weatherConfig']['url'], urlencode($city))
                )
            );

            if(empty($data) || !empty($data[0]['error']) || !empty($data[1]['error']))
            {
                $flag = 0;
                $log = '查询天气：' . $city . '>>结果:aqi:' . $data[0]['error'] . '>>天气:' . $data[1]['error'];
            }
            else
            {
                $weather = json_decode($data['fore']['info'], true);
                $aqi = json_decode($data['aqi']['info'], true);

                if($weather['error_code'] != '0' || $aqi['error_code'] != '0')
                    $flag = 0;
                    $log = '查询天气：' . $city . '>>结果:aqi:' . $aqi['reason'] . '>>天气:' . $weather['reason'];
                    //构造天气数据
                    if($weather['error_code'] == 0)
                    {
                        $temp = explode('~', $weather['result']['today']['temperature']);
                        $ltemp = (string)$temp[0];
                        $htemp = (string)$temp[1];
                        //气象实况
                        $weath = $weather['result']['today']['fb'];
                        $forecast = $weather['result']['today']['weather'];
                    }
                    else
                    {
                        $ltemp = '';
                        $htemp = '';
                        //气象实况,99-未知天气
                        $weath = '99';
                        $forecast = '';
                        
                        $flag = 0;
                        $log = '查询天气：' . $city . '>>结果:' . $weather['reason'];
                    }

                    //构造aqi数据
                    if($aqi['error_code'] == 0)
                    {
                        $pmAqi = (string)$aqi['result']['AQI'];
                        $qlt = (string)$aqi['result']['Quality'];
                    }
                    else
                    {
                        $pmAqi = '';
                        $qlt = '';
                        
                        $flag = 0;
                        $log = '查询aqi：' . $city . '>>结果:aqi:' . $aqi['reason'];
                    }

                    $weatherData['area'] = $city;
                    $weatherData['aqi'] = $pmAqi;
                    $weatherData['qlt'] = $qlt;
                    $weatherData['h_temp'] = $htemp;
                    $weatherData['l_temp'] = $ltemp;
                    $weatherData['weather'] = $forecast;
                    $weatherData['weatherCode'] = $weath;

                    //存入缓存:如果数据不完整，则缓存半小时，若数据完整，则缓存8小时
                    if(empty($weatherData['weather']))
                        $redis->setex($this->di['weatherConfig']['weatherData'] . $areaid, $this->di['weatherConfig']['weatherShortTime'], $weatherData);
                    else
                        $redis->setex($this->di['weatherConfig']['weatherData'] . $areaid, $this->di['weatherConfig']['weatherTime'], $weatherData);
                }
            }

        if(!empty($weatherData['weatherCode']) && !empty($weatherData['aqi']) && (!empty($weatherData['l_temp']) || !empty($weatherData['h_temp'])))
        {
            $res = UserHelper::getBabyWeatherPic($this->di, $babyInfo['baby_sex'], $weatherData['weatherCode'], $weatherData['aqi'], $weatherData['l_temp'], $weatherData['h_temp']);
            $weatherData['baby_pic'] = $res['baby_pic'];
            $weatherData['weat_pic'] = $res['weat_pic'];
        }
        else
        {
            $weatherData['baby_pic'] = '';
            $weatherData['weat_pic'] = '';
        }

        unset($weatherData['weatherCode']);
        $weatherData['flag'] = '1';
        return $weatherData;
    }
}