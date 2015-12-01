<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\SwooleUserClient as SwooleUserClient,
      Appserver\Utils\RedisLib,
      Appserver\Utils\UserFactory,
      Appserver\Utils\Common,
      Appserver\Utils\QQBindRpcService;

class OauthModule extends ModuleBase
{

    const SUCCESS = '1';
    const ILLEGAL = 11111;
    const FAILED_OAUTH = 11042;
    const NON_EXIST_MOBILE = '00001';
    const EXIST_MOBILE = 10003;
    const FAILED_CREATE_WALLET = 11027;
    const FAILED_REG = 10007;
    const HAVE_BINDED = 11049;
    const BINDED_SINA = 11044;
    const BINDED_QQ = 11045;
    const NO_BINDED = 11043;
    const FAILED_UNBIND = 11046;
    const FAILED_BIND = 10040;

    private $swoole;
    private $oauthFactory;  //第三方授权工厂
    private $plat;  //第三方类型，1-sina  3-tencent
    protected $redis;

    public function __construct($di, $plat)
    {
        $this->di = $di;
        $this->plat = $plat;
        //调用swoole
        $this->swoole = new SwooleUserClient(
            $this->di['sysconfig']['swooleConfig']['ip'],
            $this->di['sysconfig']['swooleConfig']['port']
        );
        //调用第三方类
        $this->oauthFactory = UserFactory::operateMethod($this->plat, $this->di);

        $redisObj = new RedisLib($this->di);
        $this->redis = $redisObj->getRedis();
    }

    /**
     * [第三方登录]
     * @param  [string] $uTags       [第三方uid]
     * @param  [string] $accessToken [第三方access_token]
     * @param  [string] $deviceToken [用户手机的设备标签]
     * @return [type]              [description]
     */
    public function thirdLogin($uTags, $accessToken, $deviceToken, $type = '')
    {
        //如果用户使用qq登录，则记录用户的accessToken
        $this->redis->setex($this->di['sysconfig']['qqaccessToken'], $this->di['sysconfig']['tokenTime'], $accessToken);
        //用户从未从第三方登录过，申请第三方授权
        $res = $this->swoole->checkThirdLogin($uTags, $this->plat);
        if(empty($res['data']))
        {
            $result = $this->oauthFactory->login($accessToken, $uTags, $_SERVER['REMOTE_ADDR']);
            if(!$result)
                return self::FAILED_OAUTH;

            //设置一个防止跳步的session，防止用户直接进入下一步进行补充资料的操作
            $session = md5($uTags . $_SERVER['REQUEST_TIME']);
            $this->redis->setex($session, $this->di['sysconfig']['tokenTime'], $uTags);
            return array(
                'flag' => (string)self::SUCCESS,
                'u_name' => Common::substrCut($result['u_name'], 12),
                'u_pic' => $result['u_pic'],
                'session' => $session,
                'u_id' => '',
                'mobi' => '',
                'coins' => '',
                'level' => '',
                'checkindays' => '',
                'token' => '',
                'wb_status' => '',
                'qq_status' => '',
              'disturb' => '3', //免打扰模式，默认为3：关闭
              'user_qr' => ''
            );
        }
        else
        {
            return $this->_showLoginData($res['data']['u_mobi'], $deviceToken, $type);
        }
    }

    /**
     * [第三方注册]
     * @param  [string] $session     [用于证明来自第三方登录的session]
     * @param  [string] $uTags       [第三方uid]
     * @param  [string] $deviceToken [用户手机的设备标签]
     * @param  [string] $mobi        [用户手机号]
     * @param  [string] $name        [用户名]
     * @param  [string] $pass        [密码]
     * @param  [string] $pic         [用户头像url]
     * @param  [string] $file        [图片二进制流]
     * @return [type]              [description]
     */
    public function reg($session, $uTags, $deviceToken, $mobi, $name, $pass, $pic, $file, $type)
    {
        //用户session，判断是否来自第三方登录
        $session = $this->redis->get($session);
        if($session == FALSE || $session != $uTags)
            return self::ILLEGAL;

        $row = $this->swoole->getUserInfoByMobi($mobi);
        if(!empty($row['data']))
                return self::EXIST_MOBILE;

        //如果有图片流，则进行存储图片的操作
        if(!empty($file['file']))
        {
            $picInfo = Common::makePath($this->di['sysconfig']['userPic'], $mobi);
            $userInfo['pic'] = $picInfo['picUrl'];
        }
        else
            //没有图片流，上传用户的第三方头像
            $userInfo['pic'] = $pic;

        //注册得到用户id
        $uid = $this->oauthFactory->register($this->plat, $mobi, Common::substrCut($name, 12),
        $pass, $uTags, $userInfo['pic'], $_SERVER['REQUEST_TIME']);

        //删除session
        $this->redis->del($session);
        //上传图片
        if(!empty($file['file']))
            $this->swoole->uploadAvatar($file['file']['tmp_name'], filesize($file['file']['tmp_name']), $picInfo['rename'], $picInfo['path']);

        //检查用户在注册之前是否被添加过亲人并进行操作
        self::checkNewUser($uid['data'],$mobi);

        return $this->_showLoginData($mobi, $deviceToken, $type);
    }

    /**
     * [第三方绑定]
     * @param  [string] $token       [登录token]
     * @param  [string] $accessToken [第三方access_token]
     * @param  [string] $uTags       [第三方uid]
     * @return [type]              [description]
     */
    public function bind($token, $accessToken, $uTags)
    {
        //通过token获取用户信息
        $userInfo = $this->redis->get('token:' . $token);

        //检查该第三方帐号是否绑定过
        $res = $this->swoole->checkThirdLogin($uTags, $this->plat);
        if(!empty($res['data']))
            return self::HAVE_BINDED;

        if($this->plat == '1')
        {
            //判断用户是否绑定微博
            if($userInfo['wb_status'] == '1')
                return self::BINDED_SINA;
        }
        elseif($this->plat == '3')
        {
            //判断用户是否绑定QQ
            if($userInfo['qq_status'] == '1')
                return self::BINDED_QQ;
        }
        else
            return self::ILLEGAL;

        $result = $this->oauthFactory->login($accessToken, $uTags, $_SERVER['REMOTE_ADDR']);
        if(!$result)
            return self::FAILED_OAUTH;
        //满足条件，执行绑定:如果无头像则添加QQ头像，如果本身有头像则不替换
        $pic = $userInfo['pic'] != '' ? $userInfo['pic'] : $result['u_pic'];
        if(!$this->oauthFactory->bind($this->plat, $userInfo['uid'], $uTags, $pic))
            return self::FAILED_BIND;

        //绑定成功，修改token状态
        switch ($this->plat)
        {
            case '1':
                $userInfo['wb_status'] = '1';
                break;
            case '3':
                $userInfo['qq_status'] = '1';
                break;
        }

        //绑定设备和qq
        if($this->plat == '3')
        {
            //如果用户使用qq登录，则记录用户的accessToken
            $this->redis->setex($this->di['sysconfig']['qqaccessToken'], $this->di['sysconfig']['tokenTime'], $accessToken);
            $this->_bindQQAndDev($userInfo['uid'], $uTags, $accessToken);
        }

        $this->redis->setex('token:' . $token, $this->di['sysconfig']['tokenTime'], $userInfo);
        return $this->_showLoginData($userInfo['mobi'], $userInfo['deviceToken'], '');
    }

    /**
     * [第三方解绑]
     * @param  [string] $token [用户登录token]
     * @return [type]        [description]
     */
    public function unbind($token)
    {
        //通过token获取用户信息
        $userInfo = $this->redis->get('token:' . $token);

        if($this->plat == 1)
        {
            if($userInfo['wb_status'] == '')
                return self::NO_BINDED;
        }
        elseif($this->plat == 3)
        {
            if($userInfo['qq_status'] == '')
                return self::NO_BINDED;
        }
        if(!$this->oauthFactory->unbind($userInfo['uid'], $this->plat))
            return self::FAILED_UNBIND;
        else
        {
            switch ($this->plat)
            {
                case '1':
                    $userInfo['wb_status'] = '';
                    break;
                case '3':
                    $userInfo['qq_status'] = '';
                    break;
            }

            if($this->plat == '3')
            {
                //解绑QQ和设备的关系
                $this->_unbindDevAndQQ($userInfo['uid'], $userInfo['qqUid']);
                //如果用户使用qq登录，则删除用户的accessToken
                $this->redis->del($this->di['sysconfig']['qqaccessToken']);
            }

            //重新设置token
            $userInfo = $this->redis->setex('token:' . $token, $this->di['sysconfig']['tokenTime'], $userInfo);
            return self::SUCCESS;
        }
    }

    /**
     * [绑定设备和QQ]
     * @param  [type] $uid         [用户id]
     * @param  [type] $qqUid       [qqUid]
     * @param  [type] $accessToken [腾讯的accessToken]
     * @return [type]              [description]
     */
    public function _bindQQAndDev($uid, $qqUid, $accessToken)
    {
        try{
            $family = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
            $devices = $this->initModel('\Appserver\Mdu\Models\DevicesModel');

            $babys = $family->getAuthBaby($uid);
            if(!empty($babys))
            {
                foreach($babys as $v)
                {
                    $bids[] = $v['baby_id'];
                }
                $uuid = $devices->getUUidByBidUid(implode(',', $bids), $uid);
                foreach($uuid as $v)
                {
                    $uuids[] = $v['dev_uuid'];
                }
                if(!empty($uuids))
                {
                    $snInfos = $devices->getDevInfoByUuids(implode(',', $uuids));
                }
            }

            if(isset($snInfos) && !empty($snInfos))
            {
                foreach($snInfos as $v)
                {
                    $sns[] = $v['sn'];
                }

           $bindDevices = new QQBindRpcService($this->di['sysconfig']['thriftConf']['ip'], $this->di['sysconfig']['thriftConf']['qqPort']);
            //绑定设备和QQ
            $bindDevices->QQBindDevices($sns, $qqUid, $accessToken);
            foreach($sns as $v)
            {
                $bindDevices->QQSendMessage($v, $qqUid, $this->di['sysconfig']['qqBindDev']['bind']);
            }
            $this->redis->lPush($this->di['sysconfig']['devBindQQ'],
                json_encode(array('uid' => $userInfo['uid'], 'content' => $this->di['sysconfig']['qqBindDev']['bind'], 'type' => '25')));
            }
        }
        catch(\Exception $e)
        {
            Common::writeLog(dirname(dirname(dirname(__FILE__))) . '/public/logs/binddevs.log', json_encode(array('action' => 'bind', 'sn' => $sns, 'qqUid' =>$qqUid, 'accessToken' => $accessToken, 'reason'=> $e->getMessage())));
        }
    }

    /**
     * [解绑QQ和设备的关系]
     * @param  [type] $uid   [description]
     * @param  [type] $qqUid [description]
     * @return [type]        [description]
     */
    public function _unbindDevAndQQ($uid, $qqUid)
    {
        try{
            $family = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
            $devices = $this->initModel('\Appserver\Mdu\Models\DevicesModel');

            $babys = $family->getAuthBaby($uid);
            if(!empty($babys))
            {
                foreach($babys as $v)
                {
                    $bids[] = $v['baby_id'];
                }
                $uuid = $devices->getUUidByBidUid(implode(',', $bids), $uid);
                foreach($uuid as $v)
                {
                    $uuids[] = $v['dev_uuid'];
                }
                if(!empty($uuids))
                {
                    $snInfos = $devices->getDevInfoByUuids(implode(',', $uuids));
                }
            }

            if(isset($snInfos) && !empty($snInfos))
            {
                foreach($snInfos as $v)
                {
                    $sns[] = $v['sn'];
                }

                   $bindDevices = new QQBindRpcService($this->di['sysconfig']['thriftConf']['ip'], $this->di['sysconfig']['thriftConf']['qqPort']);
                    //解绑设备和QQ
                    $bindDevices->QQUnbindDevices($sns, $qqUid, $this->redis->get('accessToken:' . $uid));
            }

        }
        catch(\Exception $e)
        {
            Common::writeLog(dirname(dirname(dirname(__FILE__))) . '/public/logs/binddevs.log', json_encode(array('action' => 'bind', 'sn' => $sns, 'qqUid' =>$qqUid, 'reason'=> $e->getMessage())));
        }
    }

}