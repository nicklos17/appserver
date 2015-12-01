<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\SwooleUserClient as SwooleUserClient,
       Appserver\Mdu\Modules\OauthModule as Oauth,
       Appserver\Utils\RedisLib;

class FamilyModule extends ModuleBase
{
    const SUCCESS = '1';
    const FAILED_UPDATE = 22222;
    const FAILED_GET = 33333;
    const DELELE_BABY_FAILED = 11082;
    const FAILED_UNBIND_REL = 11083;
    const BABY_HAS_HOST = 10075;
    const FAILED_ADD_FAMILY = 10052;
    const NO_OAUTH = 99999;
    const NO_FAMILY = 00001;

    protected $family;
    protected $swoole;

    public function __construct()
    {
        $this->family = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
        $this->swoole = new SwooleUserClient(
            $this->di['sysconfig']['swooleConfig']['ip'],
            $this->di['sysconfig']['swooleConfig']['port']
        );
    }

    /**
     * 获取用户与宝贝的关系
     * @param str $uid 用户id
     * @param str $babyId 宝贝id
     * @return boolean|json 
     */
    public function checkRelation($uid, $babyId)
    {
        return $this->family->getRelationByUidBabyId($uid, $babyId);
    }

    public function delBaby($babyId, $delTime)
    {
        if($this->family->SetBabyFamilyStatus($babyId, $delTime))
            return self::SUCCESS;
        else
            return self::DELELE_BABY_FAILED;

    }

    public function cancelRel($babyId, $uid, $delTime)
    {
        if($this->family->setRelByUidBabyId($babyId, $uid, $delTime))
            return self::SUCCESS;
        else
            return self::FAILED_UNBIND_REL;
    }

    /**
     * swool 获取用户信息
     */
    public function getUserInfo($qr)
    {
        return $this->swoole->getUserInfo(explode('@', $qr)[1]);
    }

    public function getUserInfoByMobi($mobi)
    {
        return $this->swoole->getUserInfoByMobi($mobi);
    }

    public function userInfoByIds($uids)
    {
        return $this->swoole->userInfoByIds($uids);
    }

    public function issetHost($babyId)
    {
        if($this->family->getRelByBabyId($babyId))
            return self::BABY_HAS_HOST;
        else
            return self::SUCCESS;
    }

    public function addRel($babyId, $uid, $roleName, $ishost, $addtime, $status)
    {
        if($this->family->addRel($babyId, $uid, $roleName, $ishost, $addtime, $status))
            return self::SUCCESS;
        else
            return self::FAILED_ADD_FAMILY;
    }

    /**
     * 设置或取消监护号
     * @param str $relation 关系： 3-副号 5-监护号 
     * @param unknown $babyId
     * @param unknown $uid
     */
    public function guardian($relation, $babyId, $famId)
    {
        $famInfo = $this->swoole->getUserInfo($famId);
        if(empty($famInfo['data']))
           return self::NO_FAMILY;

        if(!empty($famInfo['data']['u_qq_uid']))
        {
            $oauth = new Oauth($this->di, '3');
            if($relation == '5')
            {
                $redisObj = new RedisLib($this->di);
                $redis = $redisObj->getRedis();
                if(($accessToken = $redis->get($this->di['sysconfig']['qqaccessToken'])))
                {
                    $oauth->_bindQQAndDev($famId, $famInfo['data']['u_qq_uid'], $accessToken);
                }
            }
            elseif($relation == '3')
            {
                $oauth->_unbindDevAndQQ($famId, $famInfo['data']['u_qq_uid']);
            }
        }
        return $this->family->setGuardian($relation, $babyId, $famId);
    }

    /**
     * [获取宝贝的监护号]
     * @param  [type] $babyId [description]
     * @return [type]         [description]
     */
    public function getGuaInfo($babyId)
    {
        return $this->family->getRelByBabyId($babyId);
    }

    /**
     * [获取宝贝的所有亲人]
     * @param  [type] $babyId [description]
     * @param  [type] $count  [description]
     * @return [type]         [description]
     */
    public function showFamList($babyId, $count)
    {
        return $this->family->getFamList($babyId, $count);
    }

    /**
     * [记录未注册的亲人]
     * @return [type] [description]
     */
    public function remeberUnregUser($mobi, $babyId, $uname, $relation)
    {
        $redisObj = new RedisLib($this->di);
        $redis = $redisObj->getRedis();

        $users = $redis->get(sprintf($this->di['sysconfig']['unregUser'], $mobi));
        $babys = $redis->get(sprintf($this->di['sysconfig']['unregUserByBid'], $babyId));

        //组织要保存的数据
        $saveData = array('relation' => $relation, 'uname' => $uname);

        //根据手机号存储亲人关系
        if(empty($users))
        {
            $redis->set(sprintf($this->di['sysconfig']['unregUser'], $mobi), array($babyId => $saveData));
        }
        else
        {
            $users = $users + array($babyId => $saveData);
            $redis->set(sprintf($this->di['sysconfig']['unregUser'], $mobi), $users);
        }

        //根据宝贝id存储亲人关系
        if(empty($babys))
        {
            $redis->set(sprintf($this->di['sysconfig']['unregUserByBid'], $babyId), array($mobi => $saveData));
        }
        else
        {
            $babys = $babys + array($mobi => $saveData);
            $redis->set(sprintf($this->di['sysconfig']['unregUserByBid'], $babyId), $babys);
        }

    }
}