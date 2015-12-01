<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\RedisLib as RedisLib;

class RankModule extends ModuleBase
{

    const SUCCESS = '1';
    const GET_BABY_FAILED = 10014;
    const INVALID_OPERATE = 11111;
    const NON_DATA = 22222;

    public $baby;
    public $babyRanks;
    public $family;

    public function __construct()
    {
        $this->baby = $this->initModel('\Appserver\Mdu\Models\BabyModel');
        $this->family = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
        $this->babyRanks = $this->initModel('\Appserver\Mdu\Models\BabyRanksModel');
        $this->babysteps = $this->initModel('\Appserver\Mdu\Models\BabyStepsModel');
    }

    /**
     * 1.3版本的宝贝排行榜 
     */
    public function getOldRank($babyId)
    {
        $pic = $this->baby->getBabyPic($babyId);
        if(empty($pic))
            return self::GET_BABY_FAILED;

        $redis = RedisLib::getRedis($this->di);
        $ranksInfo = $redis->get('ranksInfo:'.$babyId);
        if(!$ranksInfo)
        {
            //宝贝总数
            $total = $this->babyRanks->countBaby();
            $rankInfo = $this->babyRanks->getRankInfo($babyId);
            $rank = $this->babyRanks->getRank($rankInfo['br_mileages']);
            $ranks = ceil(($total['br_id'] - $rank['rank'])/$total['br_id']*100);
            //防止出现领先100的情况
            if($ranks >= 100)
            {
                $ranks = 99;
            }
            $mileage = ceil($rankInfo['br_mileages']/1000);

            $ranksInfo = array(
                'babyPic' => $this->di['sysconfig']['babyPicServer'].$pic['0']['baby_pic'],
                'days' => $rankInfo['br_guards'],
                'ranks' => (string)$ranks,
                'mileage' => (string)$mileage
            );
            //将数据缓存
            $redis->setex('ranksInfo:'.$babyId, 300, $ranksInfo);
        }
            return array(
                'flag' => self::SUCCESS,
                'pic' => $ranksInfo['babyPic'],
                'days' => (string)$ranksInfo['days'],
                'rank' => $ranksInfo['ranks'],
                'mileage' => (string)$ranksInfo['mileage']
            );
    }

    /**
     * [获取今日排行榜]
     * @param  [type] $babyId  [宝贝id]
     * @param  [type] $count   [排行榜个数]
     * @param  string $sinceId [下拉排行，用来获取更高的名次]
     * @param  string $maxId   [上拉排行，用来获取更低的名次]
     * @return [type]          [description]
     */
    public function getTodayRank($babyId, $count, $sinceId = '', $maxId = '')
    {
        try{
            //获取今天的时间戳
            $today = strtotime(date('Y-m-d', $_SERVER['REQUEST_TIME']));

            if($count == 0 || ($sinceId != 0 && $maxId != 0))
                return self::INVALID_OPERATE;

           $getRankFromRpc = new \Appserver\Utils\RpcService($this->di['sysconfig']['thriftConf']['ip'], $this->di['sysconfig']['thriftConf']['port']);
            if($sinceId == '' && $maxId == '')
            {
                $data = $getRankFromRpc->GetStepDayRank($babyId, strtotime(date('Y-m-d', $_SERVER['REQUEST_TIME'])), intval(($count-4)/2));
                $data = json_decode($data, true);
                if(!empty($data))
                {
                    //如果宝贝没有排行，或者排名在前三则返回前count位排名
                    if(empty($data['you']) || $data['you']['index'] <= '3')
                    {
                        $data = json_decode($getRankFromRpc->GetStepDayRankByOffset(1, $count, $today), true);
                    }
                    else
                    {
                        //去掉before数组里面存在的前三的名次
                        foreach($data['before'] as $k => $v)
                        {
                            if($v['index'] <= '3')
                            {
                                unset($data['before'][$k]);
                            }
                        }
                        $data = array_merge(array_chunk($data['top'], '3')[0], $data['before'], array($data['you']), $data['after']);
                    }
                }
            }
            elseif($sinceId != '' && $maxId == '')
            {
                $data = json_decode($getRankFromRpc->GetStepDayRankByOffset($sinceId-$count, $sinceId-1, $today), true);
            }
            elseif($sinceId == '' && $maxId != '')
            {
                $data = json_decode($getRankFromRpc->GetStepDayRankByOffset($maxId+1, $maxId+$count, $today), true);
            }
            if(!empty($data))
            {
                //获取宝贝id的集合
                $bids = array_column($data, 'baby_id');
                $babyInfo = $this->baby->getListBybid(implode(',', $bids));
                foreach($data as $m=>$val)
                {
                    $data[$m]['rank'] = $rank[$m] = (string)$val['index'];
                    if(intval($val['last_index']) == 0)
                        $data[$m]['change'] = '0';
                    else
                        $data[$m]['change'] = (string)($val['last_index'] - $val['index']);

                    foreach($babyInfo as $k=>$v)
                    {
                        if($v['baby_id'] == $val['baby_id'])
                        {
                            $data[$m]['baby_pic'] =  $this->di['sysconfig']['babyPicServer'] . $babyInfo[$k]['baby_pic'];
                            $data[$m]['nick'] = $babyInfo[$k]['nick'];
                            $data[$m]['sex'] = $babyInfo[$k]['sex'];
                        }
                    }
                    //如果匹配不到图片，返回空
                    if(!isset($data[$m]['baby_pic']))
                        $data[$m]['baby_pic'] = '';
                }
                //按名次排序
                array_multisort($rank, SORT_ASC, $data);
            }
            else
            {
                $data = array();
            }
            return array('flag' => self::SUCCESS, 'list' => $data);
        }
        catch(\Exception $e)
        {
            return self::NON_DATA;
        }

    }

    /**
     * [获取总排行榜]
     * @param  [type] $babyId  [宝贝id]
     * @param  [type] $count   [排行榜个数]
     * @param  string $sinceId [下拉排行，用来获取更高的名次]
     * @param  string $maxId   [上拉排行，用来获取更低的名次]
     * @return [type]          [description]
     */
    public function getAllRank($babyId, $count, $sinceId = '', $maxId = '')
    {
        try{
            //获取今天的时间戳
            $today = strtotime(date('Y-m-d', $_SERVER['REQUEST_TIME']));
            if($count == 0 || ($sinceId != 0 && $maxId != 0))
            {
                return self::INVALID_OPERATE;
            }

           $getRankFromRpc = new \Appserver\Utils\RpcService($this->di['sysconfig']['thriftConf']['ip'], $this->di['sysconfig']['thriftConf']['port']);
            if($sinceId == 0 && $maxId == 0)
            {
                $data = $getRankFromRpc->GetStepAllRank($babyId, intval(($count-4)/2));
                $data = json_decode($data, true);
                if(!empty($data))
                {
                    //如果宝贝没有排行，或者排名在前三则返回前count位排名
                    if(empty($data['you']) || $data['you']['index'] <= '3')
                    {
                        $data = json_decode($getRankFromRpc->GetStepAllRankByOffset(1, $count), true);
                    }
                    else
                    {
                        //去掉before数组里面存在的前三的名次
                        foreach($data['before'] as $k => $v)
                        {
                            if($v['index'] <= '3')
                            {
                                unset($data['before'][$k]);
                            }
                        }
                        //如果自己排名前三，也去掉自己
                        if($data['you']['index'] <= '3')
                        {
                            $data = array_merge(array_chunk($data['top'], '3')[0], $data['after']);
                        }
                        else
                        {
                            $data = array_merge(array_chunk($data['top'], '3')[0], $data['before'], array($data['you']), $data['after']);
                        }
                    }
                }
            }
            elseif($sinceId != 0 && $maxId == 0)
            {
                $data = json_decode($getRankFromRpc->GetStepAllRankByOffset($sinceId-$count, $sinceId-1), true);
            }
            elseif($sinceId == 0 && $maxId != 0)
            {
                $data = json_decode($getRankFromRpc->GetStepAllRankByOffset($maxId+1, $maxId+$count), true);
            }
            if(!empty($data))
            {
                //获取宝贝id的集合
                $bids = array_column($data, 'baby_id');
                $babyInfo = $this->baby->getListBybid(implode(',', $bids));
                foreach($data as $m=>$val)
                {
                    $data[$m]['rank'] = $rank[$m] = (string)$val['index'];
                    if(intval($val['last_index']) == 0)
                        $data[$m]['change'] = '0';
                    else
                        $data[$m]['change'] = (string)($val['last_index'] - $val['index']);

                    foreach($babyInfo as $k=>$v)
                    {
                        if($v['baby_id'] == $val['baby_id'])
                        {
                            $data[$m]['baby_pic'] =  $this->di['sysconfig']['babyPicServer'] . $babyInfo[$k]['baby_pic'];
                            $data[$m]['nick'] = $babyInfo[$k]['nick'];
                            $data[$m]['sex'] = $babyInfo[$k]['sex'];
                        }
                    }
                    //如果匹配不到图片，返回空
                    if(!isset($data[$m]['baby_pic']))
                        $data[$m]['baby_pic'] = '';
                }
                //按名次排序
                array_multisort($rank, SORT_ASC, $data);
            }
            else
            {
                $data = array();
            }
            return array('flag' => '1', 'list' => $data);
        }
        catch(\Exception $e)
        {
            return self::NON_DATA;
        }
    }
}