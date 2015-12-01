<?php
 namespace Appserver\Mdu\Modules;

use Appserver\Utils\Common,
       Appserver\Utils\RedisLib;

 class LocusModule extends ModuleBase
{

    const SUCCESS = '1';
    const ILLEGAL = 11111;
    const NON_LOCUS = 10062;
    const NON_OAUTH = 99999;
    const FAILED_MARK = 10063;

    private $locusmodel;
    private $praisesmodel;

    public function __construct()
    {
        $this->locusmodel = $this->initModel('\Appserver\Mdu\Models\LocusModel');
        $this->praisesmodel = $this->initModel('\Appserver\Mdu\Models\PraisesModel');
    }

    /**
     * [获取宝贝轨迹列表]
     * @param  [string] $uid     [用户id]
     * @param  [string] $babyId  [宝贝id]
     * @param  [string] $count   [每页展示数量]
     * @param  [string] $sinceId [每页起始轨迹id]
     * @param  [string] $maxId   [每页最后一条轨迹id]
     * @return [type]          [description]
     */
    public function getLocusList($uid, $babyId, $count, $sinceId, $maxId)
    {
        if($sinceId == '' && $maxId == '')
        {
            //获取最新的轨迹列表
            $locusList = $this->locusmodel->locusList($babyId, $count);
        }
        elseif($sinceId != '' && $maxId == '')
        {
            //上拉：获取更多之前的轨迹信息
            $locusList = $this->locusmodel->locusBySinceId($babyId, $sinceId, $count);
        }
        elseif($sinceId == '' && $maxId != '')
        {
            //下拉：获取最新的轨迹信息
            $result = $this->locusmodel->locusList($babyId, $count);
            //拿最新数据的最后一条赞id和请求的maxId比较，如果最后一条赞id小于maxId,则返回最新的赞到maxId这段数据，反之则返回全部最新的数据
            if(!empty($result))
            {
                $num = sizeof($result) -1;
                //如果最新的轨迹id等于请求的maxId，说明还没有新的轨迹生成
                if($result['0']['locusid'] == $maxId)
                    $locusList = array();
                else
                {
                    if($result[$num]['locusid'] < $maxId)
                    {
                        for($i=0;$i<$num;$i++)
                        {
                            if($result[$i]['locusid'] > $maxId)
                            {
                                $locusList[] = $result[$i];
                            }
                        }
                    }
                    else
                        $locusList = $result;
                }
            }
            else
                //如果列表为空也执行上下拉，则返回空数组
                $locusList = array();
        }
        else
            return self::ILLEGAL;

        if(!empty($locusList))
        {
            $locusIds = array_map(function($v){return $v['locusid'];}, $locusList); 
            //获取用户是否对轨迹列表点过赞
            $type = $this->praisesmodel->praisesCheck($uid, implode(',', array_unique(array_filter(($locusIds)))));
            $cou = sizeof($locusList);
            $num = sizeof($type);
            for($i=0;$i<$cou;$i++)
            {
                if(empty($type))
                {
                    $locusList[$i]['type'] = '3';
                }
                for($j=0;$j<$num;$j++)
                {
                    if($locusList[$i]['locusid'] == $type[$j]['locus_id'])
                    {
                        $locusList[$i]['type'] = '1';
                        break;
                    }
                    else
                    {
                        $locusList[$i]['type'] = '3';
                    }
                }
            }
        }
        return array('flag' => '1', 'locuslist' => $locusList);
    }

    /**
     * [获取某一月份的归集列表]
     * @param  [string] $babyId [宝贝id]
     * @param  [string] $month  [月份，如2014-7]
     * @return [type]         [description]
     */
    public function getCalList($babyId, $month)
    {
        //如果month没设置，则第一天为当月的1号，最后一天为当月最后一天
        if($month == '')
        {
            $firstday = strtotime(date('Y-m-01 00:00:00', $_SERVER['REQUEST_TIME']));
            $endday = strtotime(date('Y-m-t 23:59:59', $_SERVER['REQUEST_TIME']));
        }
        else
        {
            $res = explode('-', $month);
            $day = Common::monthToDay($res['0'], $res['1']);
            $firstday = $day['0'];
            $endday = $day['1'];
        }
        return array(
            'flag' => '1',
            'callist' =>$this->locusmodel->callist($babyId,$firstday,$endday)
        );
    }

    /**
     * [添加轨迹标注]
     * @param  [string] $uid     [description]
     * @param  [string] $locusId [description]
     * @param  [string] $mark    [description]
     * @param  [string] $tags    [description]
     * @return [type]          [description]
     */
    public function mark($uid, $locusId, $mark, $tags)
    {
        $locusInfo = $this->locusmodel->getLocateInfo($locusId);
        if(empty($locusInfo))
            return self::NON_LOCUS;

        //检查用户是否有权限进行操作
        if(!$this->_oauthrity($uid, $locusInfo['baby_id']))
            return self::NON_OAUTH;

        if($this->locusmodel->addMark($locusId, $mark, $tags, $_SERVER['REQUEST_TIME']))
            return self::SUCCESS;
        else
            return self::FAILED_MARK;
    }

    /**
     * [获取轨迹消息列表]
     * @param  [string] $uid    [用户id]
     * @param  [string] $babyId [宝贝id]
     * @return [type]         [description]
     */
    public function getMessList($uid, $babyId)
    {
        $redis = RedisLib::getRedis();
        //获取对应的轨迹消息提示列表的key
        $key = sprintf($this->di['sysconfig']['tracksMsg'], $uid, $babyId);
        $num = $redis->lSize($key);
        $locusList = array();
        for($i=0;$i<$num;$i++)
        {
            $locusList[$i] = $redis->rPop($key);
        }
        $redis->del($key);
        return array('flag' => '1', 'messagelist' => $locusList);
    }

    /**
     * [获取轨迹最新的信息，包括赞数和评论数]
     * @param  [string] $uid     [用户id]
     * @param  [string] $locusId [轨迹id]
     * @return [type]          [description]
     */
    public function getNewInfo($uid, $locusId)
    {
        $locusInfo = $this->locusmodel->getLocateInfo($locusId);
        if(empty($locusInfo))
            return self::NON_LOCUS;

        //检查用户是否有权限进行操作
        if(!$this->_checkRelation($uid, $locusInfo['baby_id']))
            return self::NON_OAUTH;

        //检查该用户是否赞过
        $praisesCheck = $this->praisesmodel->praisesCheck($uid, $locusId);
        if(empty($praisesCheck))
            $ispra = '3';
        else
            $ispra = '1';

        return array(
            'flag' => '1',
            'praises' => $locusInfo['praises'],
            'comments' => $locusInfo['comments'],
            'tags' => $locusInfo['tags'],
            'mark' => $locusInfo['mark'],
            'ispra' => $ispra
        );
    }

    /**
     * [通过轨迹id获取宝贝id]
     * @param  [type] $locusId [description]
     * @return [type]          [description]
     */
    public function getBabyId($locusId)
    {
        return $this->locusmodel->getBabyIdByLsId($locusId);
    }

    /**
     * [返回轨迹id列表]
     * @param  [type] $babyId  [description]
     * @param  [type] $locusId [description]
     * @return [type]          [description]
     */
    public function getLocusIds($babyId, $locusId)
    {
        return $this->locusmodel->getLocusIds($babyId, $locusId);
    }
}