<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\RedisLib,
    Appserver\Utils\RpcService;

class LocateModule extends ModuleBase
{

    const NON_LOCUS = 10062;
    const NON_BABY = 10020;
    const NON_LOCATE = 10024;
    const DEV_REGULAR = '1'; //设备正常
    const DEV_SIGNAL_POOR = '3'; //设备下发失败
    const DEV_CLOSED = '5'; //设备关机
    const DEV_DORMANCY = '7'; //设备休眠

    private $locateModel;
    private $locusModel;
    private $familyModel;

    public function __construct()
    {
        $this->locateModel = $this->initModel('\Appserver\Mdu\Models\LocusInfoModel');
        $this->locusModel = $this->initModel('\Appserver\Mdu\Models\LocusModel');
        $this->familyModel = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
    }

    /**
     * 返回定位信息
     * @param  [string] $uid       [用户id]
     * @param  [string] $babyId    [宝贝id]
     * @param  [string] $starttime [开始时间戳]
     * @return [type]            [description]
     */
    public function locateInfo($uid, $babyId, $starttime)
    {
        //如果开始时间为空，则返回今天所有的定位信息;否则返回开始时间戳到当前时刻的定位信息
        if($starttime == '')
            $locusInfo = $this->locateModel->locate($babyId, $_SERVER['REQUEST_TIME']);
        else
            $locusInfo = $this->locateModel->locateForStarttime($babyId, $starttime, $_SERVER['REQUEST_TIME']);

        $redisObj = new RedisLib($this->di);
        $redis = $redisObj->getRedis();
        $redis->select(14);
        if(empty($locusInfo) && $redis->get('baby:' . $babyId . ':device-status') >= 2)
        {
           $getPoint = new RpcService($this->di['sysconfig']['thriftConf']['ip'], $this->di['sysconfig']['thriftConf']['port']);
            //从云端获得假点
           $pointInfo = json_decode($getPoint->getPoint($babyId), true);
           if(!empty($pointInfo) && array_key_exists('coordinates', $pointInfo))
               $locusInfo[0] = array(
                'coordinates' => (string)$pointInfo['coordinates'],
                'timestamp' => (string)$pointInfo['timestamp'],
                'place' => (string)$pointInfo['place'],
                'start' => (string)$pointInfo['start'],
                'end' => (string)$_SERVER['REQUEST_TIME']
            );
           else
               $locusInfo = array();
        }
        if(!empty($locusInfo))
        {
            $num = sizeof($locusInfo);
            $locusInfo[$num-1]['end'] = (string)$_SERVER['REQUEST_TIME'];
        }
        return $locusInfo;
    }

    /**
     * 返回app0.3定位信息
     * @param  [string] $uid       [用户id]
     * @param  [string] $babyId    [宝贝id]
     * @param  [string] $starttime [开始时间戳]
     * @return [type]            [description]
     */
    public function locateInfoForV03($uid, $babyId)
    {
        $locusInfo = $this->locateModel->locateHaveSteps($babyId, $_SERVER['REQUEST_TIME']);

        $redisObj = new RedisLib($this->di);
        $redis = $redisObj->getRedis();
        $redis->select(14);
        if(empty($locusInfo) && $redis->get('baby:' . $babyId . ':device-status') >= 2)
        {
           $getPoint = new RpcService($this->di['sysconfig']['thriftConf']['ip'], $this->di['sysconfig']['thriftConf']['port']);
            //从云端获得假点
           $pointInfo = json_decode($getPoint->getPoint($babyId), true);
           if(!empty($pointInfo) && array_key_exists('coordinates', $pointInfo))
           {
               $locusInfo[0] = array(
                    'coordinates' => (string)$pointInfo['coordinates'],
                    'timestamp' => (string)$pointInfo['timestamp'],
                    'place' => (string)$pointInfo['place'],
                    'start' => (string)$pointInfo['start'],
                    'end' => (string)$_SERVER['REQUEST_TIME'],
                    'steps' => '',
                    'calory' => ''
                );
            }
            else
                $locusInfo = array();
        }
        if(!empty($locusInfo))
        {
            $num = sizeof($locusInfo);
            $locusInfo[$num-1]['end'] = (string)$_SERVER['REQUEST_TIME'];
        }
        return $locusInfo;
    }

    /**
     * [返回某一天的定位信息]
     * @param  [string] $uid     [用户id]
     * @param  [string] $locusId [轨迹id]
     * @param  [string] $dataTarget [数据标签：-1-上一天 0-当天 1-下一天]
     * @return [type]          [description]
     */
    public function showDayLocate($locusId)
    {
        $locusInfo = $this->locusModel->getLocateInfo($locusId);
        if(empty($locusInfo))
        {
            return self::NON_LOCUS;
        }
        if($locusInfo['praises'] != 0)
            $locusInfo['type'] = '1';
        else
            $locusInfo['type'] = '3';

        //方便客户端获取数据，先把tracks的格式反解成数组，然后再一起输出
        if(!empty($locusInfo['tracks']))
        {
            $locusInfo['tracks'] = json_decode($locusInfo['tracks']);
        }
        unset($locusInfo['baby_id']);
        unset($locusInfo['locus_date']);
        return $locusInfo;
    }

    /**
     * [返回某一天或者前后天的定位信息]
     * @param  [string] $babyId     [宝贝id]
     * @param  [string] $locusId [轨迹id]
     * @param  [string] $target [数据标签：-1-上一天 0-当天 1-下一天]
     * @return [type]          [description]
     */
    public function showDayLocateByTarget($babyId, $locusId, $target)
    {
        //如果请求的是昨天的数据，就是拿最近的一条轨迹即可
        if($locusId == '0' && $target == '-1')
        {
            $locusInfo = $this->locusModel->getLastLocateInfo($babyId);
        }
        elseif($locusId == '0' && $target == '1')
        {
            //请求明天的数据，必定为空
            $locusInfo = array();
        }
        else
        {
            switch ($target)
            {
                case '-1':
                    $locusInfo = $this->locusModel->getFrontLocateInfo($babyId, $locusId);
                    break;
                case '0':
                    $locusInfo = $this->locusModel->getLocateInfo($locusId);
                    break;
                case '1':
                    $locusInfo = $this->locusModel->getNextLocateInfo($babyId, $locusId);
                    break;
            }
        }

        if(empty($locusInfo))
            return self::NON_LOCUS;
        else
            return $locusInfo;
    }

    /**
     * [返回宝贝最近的一次定位点]
     * @param  [type] $babyId [description]
     * @return [type]         [description]
     */
    public function showNearlyLocate($babyId)
    {
        $coor = $this->locateModel->nearlyCoor($babyId);
        if(!empty($coor))
            return array('flag' => '1', 'coordinates' => $coor['li_coordinates']);
        else
            return self::NON_LOCATE;
    }

    /**
     * [点击获取新定位点]
     * @param  [type] $babyId [宝贝id]
     * @param  [type] $type   [1-主动获取 3-自动更新]
     * @return [type]         [description]
     */
    public function getNewLocate($babyId, $type = '')
    {
        if(empty($type) || $type == '1')
        {
            $uuid = $this->locateModel->getUuidByBaby($babyId);

            //如果找不到uuid，则从设备表中查找
            if(empty($uuid) || $uuid['uuid'] == '0')
            {
                $devicesModel = $this->initModel('\Appserver\Mdu\Models\DevicesModel');
                $uuid = $devicesModel->getNearlyDev($babyId);
            }   
            
            if(!empty($uuid) && $uuid['uuid'] != '0')
            {
                try{
                    //设置获取数据数据的超时时间为58秒
                    $rpcObj = new RpcService(
                        $this->di['sysconfig']['thriftConf']['ip'],
                        $this->di['sysconfig']['thriftConf']['port'],
                        58000
                    );
                    $liInfo = json_decode($rpcObj->LocateFind($uuid['uuid']), true);

                    if(!empty($liInfo['li_id']))
                    {
                        $status = self::DEV_REGULAR;
                        //如果从rpc获得了新点，则返回新点
                        $data = $this->locateModel->getLocateInfoByLiid($liInfo['li_id']);
                    }
                    else
                    {
                        //获取最近的一个点
                        $data = $this->locateModel->nearlyCoorOfToday($babyId, strtotime(date('Y-m-d', $_SERVER['REQUEST_TIME'])));

                        if(in_array($liInfo['status'], array(0, 1, 3, 9)))
                        {
                            if(!empty($data))
                                $status = self::DEV_REGULAR;
                            else
                                $status = self::DEV_SIGNAL_POOR;
                        }
                        elseif($liInfo['status'] == '7')
                        {
                            //休眠时，不通知客户端，当成正常点处理
                            $status = self::DEV_REGULAR;
                            if(empty($data))
                            {
                                //得知设备休眠，获取假点
                                $getPoint = new RpcService($this->di['sysconfig']['thriftConf']['ip'], $this->di['sysconfig']['thriftConf']['port']);
                                //从云端获得假点
                                $pointInfo = json_decode($getPoint->getPoint($babyId), true);
                                if(!empty($pointInfo) && array_key_exists('coordinates', $pointInfo))
                                {
                                   $data = array('coordinates' => (string)$pointInfo['coordinates'],
                                       'timestamp' => (string)$pointInfo['timestamp'],
                                       'place' => (string)$pointInfo['place'],
                                       'battery' => (string)$pointInfo['battery'],
                                       'start' => (string)$pointInfo['start'],
                                       'end' => (string)$_SERVER['REQUEST_TIME'],
                                       'type' => (string)$pointInfo['type'],
                                       'accur' => (string)$pointInfo['accur']
                                    );
                                }
                            }
                        }
                        elseif($liInfo['status'] == '5')
                        {
                            $status = self::DEV_CLOSED;
                        }
                    }
                }
                catch(\Exception $e)
                {
                    $status = self::DEV_SIGNAL_POOR;
                    //获取最近的一个点
                    $data = $this->locateModel->nearlyCoorOfToday($babyId, strtotime(date('Y-m-d', $_SERVER['REQUEST_TIME'])));
                }
            }
            else
            {
                $status = self::DEV_SIGNAL_POOR;
            }
        }
        elseif($type == '3')
        {
            $status = self::DEV_REGULAR;
            $data = $this->locateModel->nearlyCoorOfToday($babyId, strtotime(date('Y-m-d', $_SERVER['REQUEST_TIME'])));
        }

        if(isset($data) && !empty($data))
        {
            $data['end'] = (string)$_SERVER['REQUEST_TIME'];
            $data = array($data);
        }
        else
            $data = array();

        return array('flag' => '1', 'data' => $data, 'status' => $status, 'status_msg' => $this->di['sysconfig']['msgForDevStatus'][$status]);
    }

    /**
     * [获取轨迹详情图片]
     * @param  [type] $locusid [轨迹id]
     * @param  [type] $babyId  [宝贝id]
     * @return [type]          [description]
     */
    public function getLocusPic($locusid, $babyId = '')
    {
        if(!empty($babyId))
        {
            $res = $this->locateModel->getLocusPicByBabyId($babyId);
        }
        else
        {
            $res = $this->locateModel->getLocusPicByLocusId($locusid);
        }

        if(!empty($res))
        {
            foreach($res as $val)
            {
                $result[$val['li_id']][] = array('id' => $val['lip_id'], 'name'=> $this->di['sysconfig']['qiniu']['resourceUrl'] . '/' .$val['pics']);
            }
            return $result;
        }
        else
            return $res;
    }

    /**
     * [获取从liid开始的定位信息]
     * @return [type] [description]
     */
    public function getLocateInfoByStartId($babyId, $liid)
    {
        $info = $this->locateModel->locateByStartId($babyId, $liid);
        end($info)['end'] = (string)$_SERVER['REQUEST_TIME'];
        return $info;
    }

    /**
     * [统计某个定位点上传的图片个数]
     * @param  [type] $liId [description]
     * @return [type]       [description]
     */
    public function countPicByLiid($liId)
    {
        return $this->locateModel->countPicByLiid($liId);
    }

    /**
     * [返回某个定位点的信息]
     * @param  [type] $uid  [description]
     * @param  [type] $liid [description]
     * @return [type]       [description]
     */
    public function getLocateInfoByLiid($liid)
    {
        return $this->locateModel->getLocateInfoByLiid($liid);
    }

    /**
     * [轨迹详情图片入库]
     * @param [type] $locusId   [description]
     * @param [type] $liid      [description]
     * @param [type] $uid       [description]
     * @param [type] $babyId    [description]
     * @param [type] $imageName [description]
     * @param [type] $addtime   [description]
     */
    public function addPic($locusId, $liid, $uid, $babyId, $imageName, $addtime)
    {
        return $this->locateModel->addPic($locusId, $liid, $uid, $babyId, $imageName, $addtime);
    }

    /**
     * [获取轨迹详情图片信息]
     * @param  [type] $picId [图片id]
     * @return [type]        [description]
     */
    public function getPicInfo($picId)
    {
        return $this->locateModel->getPicInfo($picId);
    }

    /**
     * [删除图片]
     * @param  [type] $picId [图片id]
     * @return [type]        [description]
     */
    public function delPic($picId, $deltime)
    {
        return $this->locateModel->delPic($picId, $deltime);
    }
}