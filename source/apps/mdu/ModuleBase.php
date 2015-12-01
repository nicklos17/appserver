<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\SwooleUserClient as SwooleUserClient,
    Appserver\Utils\RedisLib as RedisLib,
    Appserver\Utils\UserHelper;

class ModuleBase
{

    const NON_EXIST_MOBILE = '00001';
    const FAILED_CREATE_WALLET = 11027;
    const NO_OAUTH = 99999;
    const FAILED_GET = 22222;

    protected $di;

    protected function initModel($model)
    {
        $modObj = new $model();
        $this->di = $modObj->getDI();
        return $modObj;
    }

    /**
     * [判断用户是否有操作权限]
     */
    protected function _oauthrity($uid, $babyId)
    {
        $relObj = new \Appserver\Mdu\Modules\FamilyModule();
        $rel = $relObj->checkRelation($uid, $babyId);
        if($rel['family_relation'] == 1 || $rel['family_relation'] == 5)
            return true;
        else
            return false;
    }

    /**
     * [判断用户与宝贝是否有关系]
     */
    protected function _checkRelation($uid, $babyId)
    {
        $relObj = new \Appserver\Mdu\Modules\FamilyModule();
        $rel = $relObj->checkRelation($uid, $babyId);
        if(empty($rel))
            return false;
        else
            return true;
    }

    /**
     * [返回用户登录数据]
     * @param  [string] $mobi        [用户手机号]
     * @param  [string] $deviceToken [用户手机的设备标签]
     * @param  [string] $type [1-ios 3-android]
     * @return [type]              [description]
     */
    protected function _showLoginData($mobi, $deviceToken, $type = '')
    {
        //执行免登录操作，将新注册的用户信息存到redis
        $swoole = new \Appserver\Utils\SwooleUserClient(
            $this->di['sysconfig']['swooleConfig']['ip'],
            $this->di['sysconfig']['swooleConfig']['port']);
        $row = $swoole->getUserInfoByMobi($mobi);
        if(empty($row['data']))
        {
            return self::NON_EXIST_MOBILE;
        }

        if(empty($deviceToken))
            $disturb = '1';
        else
        {
            //获取用户的免打扰状态
            $RedisObj = new \Appserver\Utils\RedisLib($this->di);
            $redis = $RedisObj::getRedis();
            $disturbInfo = $redis->get('disturb:'. $row['data']['u_id']);
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
        }

        $userInfo['qqUid'] = $row['data']['u_qq_uid'];
        $userInfo['wbUid'] = $row['data']['u_wb_uid'];
        $userInfo['uid'] = $row['data']['u_id'];
        $userInfo['mobi'] = $row['data']['u_mobi'];
        $userInfo['pic'] = $row['data']['u_pic'] ? UserHelper::checkPic($this->di, $row['data']['u_pic']) : '';
        $userInfo['uname'] = $row['data']['u_name'];
        $userInfo['level'] = $row['data']['u_level'];
        $userInfo['regtime'] = $row['data']['u_regtime'];
        //设置tokenflag,表示是正常的token
        $userInfo['tokenFlag'] = '1';
        $userInfo['wb_status'] = $row['data']['u_wb_uid'] ? '1' : '';
        $userInfo['qq_status'] = $row['data']['u_qq_uid'] ? '1' : '';
        $RedisObj = new RedisLib($this->di);
        $redis = $RedisObj->getRedis();
        //获取用户连续签到次数
        $checkinCount = $redis->get(sprintf($this->di['sysconfig']['signcount'], $userInfo['uid']));
        if($checkinCount == false)
        {
            $checkinCount = '0';
        }

        //获取用户当前云币数量
        $coins = $swoole->coinsInfo($userInfo['uid']);
        //当发现用户未创建钱包时，未其创建一个
        if(empty($coins['data']))
        {
            if($swoole->createWallet($userInfo['uid'])['data'] == '1')
            {
                $coins['data']['uw_coins'] = '0';
            }
            else
            {
                return self::FAILED_CREATE_WALLET;
            }
        }

        //保存一个login:uid的redis并产生用户token，用来限制一个用户只能在一台手机上登录
        $token = UserHelper::setToken($this->di, $userInfo, $type, $deviceToken);

        return array('flag' => '1',
                'token' => $token,
                'u_id' => $userInfo['uid'],
                'mobi' => $userInfo['mobi'],
                'u_pic' => $userInfo['pic'],
                'u_name' => $userInfo['uname'],
                'coins' => $coins['data']['uw_coins'],
                'level' => $userInfo['level'],
                'checkindays' => (string)$checkinCount,
                'wb_status' => $userInfo['wb_status'],
                'qq_status' => $userInfo['qq_status'],
                'user_qr' => UserHelper::makeUserQr($userInfo['uid'], $userInfo['mobi'], $userInfo['regtime']),
                'disturb' => $disturb
        );
    }

    /**
     * 根据轨迹id获取推送对象的uid
     * @param locusId 轨迹id
     * @param relId 对这条轨迹进行评论或者点赞的人的id
     * @return array 返回推送对象的uid
     */
    public function getPushUid($locusId, $relId)
    {
        $uids = array();
        $this->locusModel = $this->initModel('\Appserver\Mdu\Models\LocusModel');
        $this->familyModel = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
        $babyId = $this->locusModel->getBabyId($locusId);
        if(empty($babyId))
        {
            return self::FAILED_GET;
        }
        else
        {
            $rel = $this->familyModel->getAuthor($relId, $babyId['baby_id']);
            if(!empty($rel))
            {
                foreach($rel as $v)
                {
                    $uids[] = $v['u_id'];
                }
            }
        }
        return $uids;
    }

}