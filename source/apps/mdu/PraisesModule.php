<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\SwooleUserClient as SwooleUserClient,
    Appserver\Utils\RedisLib,
    Appserver\Utils\Common,
    Appserver\Utils\UserHelper;

class PraisesModule extends ModuleBase
{

    const SUCCESS = '1';
    const NON_LOCUS = 10062;
    const PRAISED = 10057;
    const FAILED_PRAISE = 10054;
    const GET_EMPTY_DATA = 22222;
    const NO_PRAISE = 10055;
    const FAILED_CANCLE_PRAISE = 10056;
    const FAILED_UPDATE = 33333;
    const NO_FAMILY = 99999;

    private $praisesmodel;
    private $locusmodel;
    private $babymodel;
    private $swoole;

    public function __construct()
    {
        $this->praisesmodel = $this->initModel('\Appserver\Mdu\Models\PraisesModel');
        $this->locusmodel = $this->initModel('\Appserver\Mdu\Models\LocusModel');
        $this->babymodel = $this->initModel('\Appserver\Mdu\Models\BabyModel');
        $this->familymodel = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
        $this->swoole = new SwooleUserClient(
            $this->di['sysconfig']['swooleConfig']['ip'],
            $this->di['sysconfig']['swooleConfig']['port']
        );

    }

    /**
     * [点赞]
     * @param  [type] $token [description]
     * @param  [type] $mobi  [description]
     * @return [type]        [description]
     */
    public function hit($token, $locusId)
    {
        $redis = RedisLib::getRedis($this->di);
        $userInfo = $redis->get('token:' . $token);
        $locusInfo = $this->locusmodel->getLocateInfo($locusId);
        
        if(empty($locusInfo))
            return self::NON_LOCUS;

        //检查用户是否有权限进行操作
        if(!$this->_checkRelation($userInfo['uid'], $locusInfo['baby_id']))
            return self::NO_FAMILY;

        $praisesCheck = $this->praisesmodel->getLsidByUid($userInfo['uid'], $locusId);
        if(!empty($praisesCheck))
            return self::PRAISED;

        //获取宝贝昵称
        $babyInfo = $this->babymodel->getBabyInfoById($locusInfo['baby_id']);
        if(empty($babyInfo))
            return self::NON_LOCUS;

        $familymodel = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
        //获取角色名
        $rolename = $familymodel->getRelationByUidBabyId($userInfo['uid'], $locusInfo['baby_id']);
        if(!empty($rolename))
            $relName = $rolename['family_rolename'];
        else
            $relName = $userInfo['mobi'];

        $this->di['db']->begin();
        if($this->praisesmodel->hit(
            $userInfo['uid'],
            $relName,
            $_SERVER['REQUEST_TIME'],
            $locusId))
        {
            if($this->locusmodel->hit($locusId))
            {
                if($familymodel->hit($userInfo['uid'], $locusInfo['baby_id'], $_SERVER['REQUEST_TIME']))
                {
                    $this->di['db']->commit();

                    //type = 1001到代表赞， type = 1003代表评论
                    $result = array(
                        'rel_id' => (string)$userInfo['uid'],
                        'type' => '1001',
                        'addtime' => (string)$_SERVER['REQUEST_TIME'],
                        'content' => '',
                        'rel_name' => $relName,
                        'rel_pic' => $userInfo['pic'],
                        'baby_id' => (string)$locusInfo['baby_id'],
                        'locus_id' => (string)$locusId);

                    // $uid是宝贝对应的主号，或者监护号或者是主号和监护号的集合；如果uid为空，则不执行推送的操作
                    $uid = Common::getPushUid($locusId, $userInfo['uid']);
                    if(!empty($uid))
                    {
                        for($i=0;$i<sizeof($uid);$i++)
                        {
                            if($uid[$i] != '')
                            {
                                $result['uid'] = $uid[$i];
                                $result['alert'] = sprintf(
                                    $this->di['sysconfig']['praPush'], $babyInfo['baby_nick'],
                                    $relName,
                                    $babyInfo['baby_nick'],
                                    date('m月d日', $locusInfo['locus_date']
                                    )
                                );
                                $redis->lPush($this->di['sysconfig']['pushForActive'], json_encode($result));
                            }
                        }
                    }

                    return array('flag' => '1', 'praises' => (string)($locusInfo['praises'] + 1));
                }
                $this->di['db']->rollback();
                return self::FAILED_PRAISE;
            }
            $this->di['db']->rollback();
            return self::FAILED_PRAISE;

        }
        return self::FAILED_PRAISE;
    }

    public function canclePraise($uid, $locusId)
    {
        $babyId = $this->locusmodel->getBabyId($locusId);
        if(!$babyId)
            return self::GET_EMPTY_DATA;

        $praisesCheck = $this->praisesmodel->getLsidByUid($uid, $locusId);
        if(!$praisesCheck)
            return self::NO_PRAISE;
        else
        {
            $this->di['db']->begin();
            if($this->praisesmodel->delPraise($uid, $locusId))
            {
                //轨迹表赞的数量-1
                if(!$this->locusmodel->cancelPraises($locusId))
                {
                    $this->di['db']->rollback();
                    return self::FAILED_UPDATE;
                }

                //关系表赞的数量-1
                if(!$this->familymodel->cancelPraises($uid, $babyId['baby_id']))
                {
                    $this->di['db']->rollback();
                    return self::FAILED_UPDATE;
                }
                $this->di['db']->commit();
                //计算点完赞后赞的数量，返回
                $count = $this->locusmodel->getCommentAndPraiseCount($locusId);
                if(!$count)
                    $praises = '0';
                else
                    $praises = $count['praises'];
                return array(self::SUCCESS,$praises);
            }
            else
                return self::FAILED_CANCLE_PRAISE;
        }
    }

    public function showPraisesList($locusId, $count, $sinceId, $maxId)
    {
        if($sinceId == '' && $maxId == '')
            $praList = $this->praisesmodel->getPraiseList($locusId, $count);
        elseif($sinceId && $maxId == '')
            $praList = $this->praisesmodel->getPraisesBySinceId($locusId, $sinceId, $count);
        else
        {
            $result = $this->praisesmodel->getPraiseList($locusId, $count);
            //拿最新数据的最后一条赞id和请求的maxId比较，如果最后一条赞id小于maxId,则返回最新的赞到maxId这段数据，反之则返回全部最新的数据
            if(!$result)
            {
                $num = sizeof($result) -1;
                //如果最新的赞id等于请求的maxId，说明还没有新的赞生成
                if($result['0']['lp_id'] == $maxId)
                    $praList = array();
                else
                {
                    if($result[$num]['lp_id'] <= $maxId)
                        $praList = $this->praisesmodel->getPraisesByMaxId($locusId, $maxId);
                    else
                        $praList = $result;
                }
            }
        }

        if(!empty($praList))
        {
            //根据轨迹id获得宝贝id
            $babyId = $this->locusmodel->getBabyIdByLsId($locusId);
            //根据宝贝id获得宝贝昵称

            $babyName = $this->babymodel->getBabyInfoById($babyId['baby_id']);
            if($babyName)
            {
                for($i=0;$i<sizeof($praList);$i++)
                {
                    $praList[$i]['baby_name'] = $babyName['baby_nick'];
                }
            }
            $uids = array_map(function($v){return $v['u_id'];}, $praList);

            $res = $this->swoole->userInfoByIds(implode(',', array_unique(array_filter($uids))));
            $pics = $res['data'];

            $cou = sizeof($pics);
            for($i=0;$i<sizeof($praList);$i++)
            {
                for($j=0;$j<$cou;$j++)
                {
                    if(empty($pics[$i]))
                    {
                        $praList[$i]['u_pic'] = '';
                    }
                    else
                    {
                        if($praList[$i]['u_id'] == $pics[$j]['u_id'])
                        {
                            $praList[$i]['u_pic'] = UserHelper::checkPic($this->di, $pics[$j]['u_pic']);
                            break;
                        }
                    }
                }
            }
        }

        $count = $this->locusmodel->getCommentAndPraiseCount($locusId);
        //获取赞总数总数
        if($count)
            $praises = $count['praises'];
        else
            $praises = '0';

        return array('flag' => '1', 'praiseslist' => $praList, 'praises' => $praises);
    }
}