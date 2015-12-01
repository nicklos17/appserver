<?php
namespace Appserver\v3\Controllers;

use Appserver\Mdu\Modules\LocateModule as Locate,
    Appserver\Utils\QiniuClient,
    Appserver\Utils\Common,
    Appserver\Utils\RedisLib,
    Appserver\Utils\ImgUpload;

class LocateController extends ControllerBase
{

    const NO_OAUTH = 99999;
    const FAILED_CREATE_DIR = 44444;
    const FULLED_PICS = 11020;
    const FAILED_IMG_UPLOAD = 10093;
    const NON_IMG_UPLOAD =10098;

    public $userInfo;
    public $locate;
    public $redisObj;

    public function initialize()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        //判断用户与宝贝是否有关系
        if(isset($this->_sanReq['baby_id']))
            $this->_checkRelation($this->userInfo['uid'], $this->_sanReq['baby_id']);

        $this->locate = new Locate;
        $this->redisObj = new RedisLib($this->di);
    }

    /**
     * [返回当天的轨迹点]
     * @return [type] [description]
     */
    public function indexAction()
    {
        $redis = $this->redisObj->getRedis();
        $redisData = $redis->hgetAll(sprintf($this->di['sysconfig']['todayLocate'], $this->_sanReq['baby_id']));

        //如果缓存无数据，则重新获取
        if(empty($redisData))
        {
            $data = $this->locate->locateInfoForV03(
                $this->userInfo['uid'],
                $this->_sanReq['baby_id']
            );

            if(!empty($data))
            {
                //根据宝贝id,获取今天的所有定位图片
                $pics = $this->locate->getLocusPic(0, $this->_sanReq['baby_id']);
                if(empty($pics))
                    $picInfo = array();

                //数据存入缓存
                foreach($data as $key => $locusInfo)
                {
                    foreach ($pics as $k => $value)
                    {
                        if($locusInfo['li_id'] == $k)
                            $picInfo = $value;
                        else
                            $picInfo = array();
                        break;
                    }

                    $locusInfo['pics'] = $picInfo;
                    $redisData[$locusInfo['li_id']] = $locusInfo;
                }
                //组织返回数据
                $returnData = array_values($redisData);
                $times = $_SERVER['REQUEST_TIME'];

                //记录缓存生成时间以及最后一个定位点的id
                $redisData['lastid'] = max(array_keys($redisData));
                $redisData['times'] = $_SERVER['REQUEST_TIME'];
                //将所有数据存入缓存
                $redis->hMset(
                    sprintf($this->di['sysconfig']['todayLocate'], $this->_sanReq['baby_id']),
                    $redisData
                );
                //设置缓存时间到第二天0点
                $redis->expireAt(
                    sprintf($this->di['sysconfig']['todayLocate'], $this->_sanReq['baby_id']),
                    strtotime('tomorrow')
                );
            }
            else
            {
                $returnData = array();
                $times = '';
            }
        }
        else
        {
            $redisTime = $redisData['times'];
            //获取缓存中最大的点的信息
            $lastInfo = $redis->hget(sprintf($this->di['sysconfig']['todayLocate'], $this->_sanReq['baby_id']), $redisData['lastid']);

            //获取新点
            $newInfo = $this->locate->getLocateInfoByStartId($this->_sanReq['baby_id'], $redisData['lastid']);

            //将至少返回一个最近的点，用来查看云端是否对最后一个定位点的更新
            if(!empty($newInfo))
            {
                //新点信息中和旧点重合的点，进行图片和定位信息合并
                if($lastInfo['li_id'] == $newInfo[0]['li_id'])
                {
                    $newInfo[0]['pics'] = $lastInfo['pics'];
                    $theChangeData = $redisData[$lastInfo['li_id']] = $newInfo[0];
                    unset($newInfo[0]);
                }

                foreach($newInfo as $val)
                {
                    $val['pics'] = array();
                    $redisData[$val['li_id']] = $val;
                    $newData[] = $val;
                }

                $times = (string)$_SERVER['REQUEST_TIME'];

                //重新存入内存
                $redisData['lastid'] = max(array_keys($redisData));
                $redisData['times'] = $times;

                //有时更新的点很多，所以这里没有区分点多点少，直接全部重写内存
                $redis->hMset(
                    sprintf($this->di['sysconfig']['todayLocate'], $this->_sanReq['baby_id']),
                    $redisData
                );

                //时间戳不一样，表示用户对轨迹详情进行更新，返回所有定位信息;如果相等，则只是云端定位更新，返回更新的点
                if($redisTime != $this->_sanReq['times'])
                {
                    unset($redisData['lastid']);
                    unset($redisData['times']);
                    //返回的数据就是缓存中除了times和lastid之外的所有信息
                    $returnData = array_values($redisData);
                }
                else
                {
                    //返回更新的新点
                    $returnData = isset($newData) ? array_merge($theChangeData, $newData) : $theChangeData;
                }
            }
            else
            {
                $returnData = array();
                $times = $redisTime;
            }
        }

        //延长最近的end时间
        if(!empty($returnData))
            $returnData[sizeof($returnData) - 1]['end'] = (string)$_SERVER['REQUEST_TIME'];

        //查看最新的点是否休眠，如果休眠，则延长时间
        $this->_returnResult(array('flag' => '1', 'tracks' => $returnData, 'times' => $times));
    }

    /**
     * 返回某一天的轨迹点
     * @return [type] [description]
     */
    public function dayAction()
    {
        $res = $this->locate->showDayLocateByTarget($this->_sanReq['baby_id'], $this->_sanReq['locus_id'], $this->_sanReq['type']);

        if(is_array($res))
        {
            //先读取缓存
            $redis = $this->redisObj->getRedis();
            $redisData = $redis->get(sprintf($this->di['sysconfig']['locusData'], $res['locus_id']));

            //如果没有缓存数据则重新获取
            if(empty($redisData))
            {
                if(!empty($res['tracks']))
                {
                    $baseData = json_decode($res['tracks'], true);
                    //根据详情id,获取今天的所有定位图片
                    $pics = $this->locate->getLocusPic($res['locus_id']);
                    foreach($baseData['real'] as $locusInfo)
                    {
                        foreach($pics as $k => $picInfo)
                        {
                            if($locusInfo['li_id'] == $k)
                            {
                                $locusInfo['pics'] = $picInfo;
                                break;
                            }
                        }

                        if(!isset($locusInfo['pics']))
                            $locusInfo['pics'] = array();

                        unset($locusInfo['accur']);
                        unset($locusInfo['p_type']);
                        $redisData[$locusInfo['li_id']] = $locusInfo;
                    }
                    $returnData = array_values($redisData);
                    $redisData['times'] = $_SERVER['REQUEST_TIME'];
                    //组织存入缓存数据
                    $redis->setex(
                        sprintf($this->di['sysconfig']['locusData'], $this->_sanReq['locus_id']),
                        $this->di['sysconfig']['tokenTime'],
                        $redisData
                    );

                }
                else
                    $returnData = array();

                $locusId = $res['locus_id'];
                $times = $_SERVER['REQUEST_TIME'];

            }
            else
            {
                $times = $redisData['times'];
                if($redisData['times'] == $this->_sanReq['times'] || !$redisData)
                    $returnData = array();
                else
                {
                    unset($redisData['times']);
                    $returnData = array_values($redisData);
                }

                $locusId = $res['locus_id'];
            }

        }
        else
        {
            $returnData = array();
            $locusId = '';
            $times = '';
        }

        $this->_returnResult(array('flag' => '1', 'tracks' => $returnData, 'times' => (string)$times, 'locus_id' => $locusId));
    }

    /**
     * [上传图片]
     * @return [type] [description]
     */
    public function uploadAction()
    {
        if(empty($_FILES['file']))
            $this->_showMsg(self::NON_IMG_UPLOAD, $this->di['flagmsg'][self::NON_IMG_UPLOAD]);
        //判断用户是否有上传权限
        $rel = $this->locate->getLocateInfoByLiid($this->_sanReq['li_id']);
        $this->_oauthrity($this->userInfo['uid'], $rel['baby_id']);

        //查看上传的图片是否达到上限
        if($this->locate->countPicByLiid($this->_sanReq['li_id']) >= 9)
            $this->_showMsg(self::FULLED_PICS, $this->di['flagmsg'][self::FULLED_PICS]);

        //图片名
        $imageName = substr(md5(uniqid() . $this->userInfo['uid'] . rand(0, 10000)), 8, 16);
        //用liid组成保存在本地的路径，在七牛出现问题时，方便从本地调取路径
        $str = md5($this->_sanReq['li_id']);

        //图片入库
        $upload = new ImgUpload($this->di);
        $picInfo = $upload->uploadFile($_FILES['file'], $this->di['sysconfig']['locatePic'], $imageName, substr($str, 0, 2) . '/' . substr($str, 2, 2));
        if(is_numeric($picInfo))
            $this->_showMsg($picInfo, $this->di['flagmsg'][$picInfo]);

        if(!($picId = $this->locate->addPic($this->_sanReq['locus_id'], $this->_sanReq['li_id'], $this->userInfo['uid'], $rel['baby_id'], $picInfo, $_SERVER['REQUEST_TIME'])))
            $this->_showMsg(self::FAILED_IMG_UPLOAD, $this->di['flagmsg'][self::FAILED_IMG_UPLOAD]);

        $redis = $this->redisObj->getRedis();

        //更新对应的轨迹缓存信息
        if($this->_sanReq['locus_id'] == '0')
        {
            //更新今天的定位信息
            $redisData = $redis->hgetAll(sprintf($this->di['sysconfig']['todayLocate'], $rel['baby_id']));
            if(!isset($redisData[$this->_sanReq['li_id']]['pics']) || $redisData[$this->_sanReq['li_id']]['pics'] === null)
                $redisData[$this->_sanReq['li_id']]['pics'] = array();

            array_push(
                $redisData[$this->_sanReq['li_id']]['pics'],
                array('id' => $picId, 'name' => $this->di['sysconfig']['qiniu']['resourceUrl'] . '/' .$picInfo)
            );

            //更新缓存
            $redis->hset(
                sprintf($this->di['sysconfig']['todayLocate'], $rel['baby_id']),
                $this->_sanReq['li_id'],
                $redisData[$this->_sanReq['li_id']]
            );

            //更新缓存时间
            $redis->hset(
                sprintf($this->di['sysconfig']['todayLocate'], $rel['baby_id']),
                'times',
                $_SERVER['REQUEST_TIME']
            );
        }
        else
        {
            //更新历史轨迹信息
            $redisData = $redis->get(sprintf($this->di['sysconfig']['locusData'], $this->_sanReq['locus_id']));
            if(!isset($redisData[$this->_sanReq['li_id']]['pics']) || $redisData[$this->_sanReq['li_id']]['pics'] === null)
                $redisData[$this->_sanReq['li_id']]['pics'] = array();

            array_push(
                $redisData[$this->_sanReq['li_id']]['pics'],
                array('id' => $picId, 'name' => $this->di['sysconfig']['qiniu']['resourceUrl'] . '/' . $picInfo)
            );

            $redisData['times'] = $_SERVER['REQUEST_TIME'];

            $redis->setex(
                sprintf($this->di['sysconfig']['locusData'], $this->_sanReq['locus_id']),
                $this->di['sysconfig']['tokenTime'],
                $redisData
            );

        }

        $this->_returnResult(array('flag' => '1', 'picinfo' => array('id' => $picId, 'name' => $this->di['sysconfig']['qiniu']['resourceUrl'] . '/' .$picInfo)));

    }

    /**
     * [删除轨迹详情图片]
     * @return [type] [description]
     */
    public function delpicAction()
    {
        $picInfo = $this->locate->getPicInfo($this->_sanReq['pic_id']);
        $this->_oauthrity($this->userInfo['uid'], $picInfo['baby_id']);
        if($picInfo['lip_status'] == '1')
        {
            $this->locate->delPic($this->_sanReq['pic_id'], $_SERVER['REQUEST_TIME']);
            $redis = $this->redisObj->getRedis();

            //更新对应的轨迹缓存信息
            if($picInfo['locus_id'] == '0')
            {
                //更新今天的定位信息
                $redisData = $redis->hgetAll(sprintf($this->di['sysconfig']['todayLocate'], $picInfo['baby_id']));

                foreach($redisData[$picInfo['li_id']]['pics'] as $k => $val)
                {
                    if($val['id'] == $this->_sanReq['pic_id'])
                    {
                        unset($redisData[$picInfo['li_id']]['pics'][$k]);
                        break;
                    }
                }

                $redisData[$picInfo['li_id']]['pics'] = array_values($redisData[$picInfo['li_id']]['pics']);
                //更新缓存
                $redis->hset(
                    sprintf($this->di['sysconfig']['todayLocate'], $picInfo['baby_id']),
                    $picInfo['li_id'],
                    $redisData[$picInfo['li_id']]
                );

                //更新缓存时间
                $redis->hset(
                    sprintf($this->di['sysconfig']['todayLocate'], $picInfo['baby_id']),
                    'times',
                    $_SERVER['REQUEST_TIME']
                );
            }
            else
            {
                //更新历史轨迹信息
                $redisData = $redis->get(sprintf($this->di['sysconfig']['locusData'], $picInfo['locus_id']));
                foreach($redisData[$picInfo['li_id']]['pics'] as $k => $val)
                {
                    if($val['id'] == $this->_sanReq['pic_id'])
                    {
                        unset($redisData[$picInfo['li_id']]['pics'][$k]);
                        break;
                    }
                }

                $redisData[$picInfo['li_id']]['pics'] = array_values($redisData[$picInfo['li_id']]['pics']);

                $redisData['times'] = $_SERVER['REQUEST_TIME'];

                $redis->setex(
                    sprintf($this->di['sysconfig']['locusData'], $picInfo['locus_id']),
                    $this->di['sysconfig']['tokenTime'],
                    $redisData
                );

            }
        }

        $this->_showMsg('1');
    }

}
