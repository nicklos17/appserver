<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\SwooleUserClient,
       Appserver\Utils\Common,
       Appserver\Utils\RedisLib,
       Appserver\Utils\UserHelper;

class UserModule extends ModuleBase
{

    const SUCCESS = '1';
    const FAILED = 10041;
    const FAILED_CREATE_WALLET = 11027;
    const FAILED_REG = 10007;
    const NON_EXIST_MOBILE = '00001';
    const EXIST_MOBILE = 10003;
    const ERROR_PASS = 10006;
    const FAILED_CHANGE_PASSWORD = 10060;
    const ERROR_NAME = 10011;
    const EXIST_NAME = 10017;
    const FAILED_UPLOAD = 10093;
    const FAILED_UPDATE = 10009;
    const FAILED_GET_DATA = 22222;
    const FAILED_UPDATE_DATA = 33333;
    const NON_EXISTS = 00001;

    protected $di;
    protected $swoole;

    public function __construct($di)
    {
        $this->di = $di;
        $this->swoole = new SwooleUserClient(
            $this->di['sysconfig']['swooleConfig']['ip'],
            $this->di['sysconfig']['swooleConfig']['port']
        );
    }

    /**
     * [用户注册]
     * @param  [type] $mobi        [description]
     * @param  [type] $pass        [description]
     * @param  [type] $file        [description]
     * @param  [type] $type        [description]
     * @param  [type] $deviceToken [description]
     * @return [type]              [description]
     */
    public function reg($mobi, $pass, $file, $plat, $deviceToken, $type)
    {
        $row = $this->swoole->getUserInfoByMobi($mobi);
        if(!empty($row['data']))
            return self::EXIST_MOBILE;

        //如果有图片流，则带上头像
        if(!empty($file['file']))
        {
            $picInfo = Common::makePath($this->di['sysconfig']['userPic'], $mobi);
            $this->swoole->uploadAvatar($file['file']['tmp_name'], filesize($file['file']['tmp_name']), $picInfo['rename'], $picInfo['path']);
        }
        else
        {
            $picInfo['picUrl'] = '';
        }

        //注册得到用户id
        $uid = $this->swoole->regFromSwoole($mobi, $pass, $picInfo['picUrl'], '', (string)$_SERVER['REQUEST_TIME']);

        if($uid['data'] != '')
        {
            //为用户创建个人钱包
            if($this->swoole->createWallet($uid['data'])['data'] != '1')
            {
                return self::FAILED_CREATE_WALLET;
            }

            //检查用户在注册之前是否被添加过亲人并进行操作
            self::checkNewUser($uid['data'],$mobi);

            //执行注册后免登录操作，返回用户登录数据
            return $this->_showLoginData(
                $mobi,
                $deviceToken,
                $type
            );
        }
        else
        {
            return self::FAIL_REG;
        }
    }

    /**
     * [用户登录]
     * @param  [string] $mobi        [手机号]
     * @param  [string] $pass        [密码]
     * @param  [string] $plat        [设备类型 1-ios 3-android]
     * @param  [string] $deviceToken [手机设备标签]
     * @return [type]              [description]
     */
    public function login($mobi, $pass, $plat, $deviceToken, $type)
    {
        $row = $this->swoole->getUserInfoByMobi($mobi);

        if(empty($row['data']))
        {
            return self::NON_EXIST_MOBILE;
        }
        if(Common::makeSecert($pass, $row['data']['u_regtime']) != $row['data']['u_pass'])
        {
            return self::ERROR_PASS;
        }
        return $this->_showLoginData($mobi, $deviceToken, $type);
    }

    /**
     * 重置密码
     * @param  [string] $mobi    [手机号]
     * @param  [string] $passnew [新密码]
     * @param  [string] $regtime [注册时间]
     * @return [type]          [description]
     */
    public function resetPass($session, $passnew)
    {
        $redisObj = new RedisLib($this->di);
        $redis = $redisObj->getRedis();

        $row = $this->swoole->getUserInfoByMobi($redis->get('session:'.$session));
        if(empty($row['data']))
            return self::NON_EXISTS;

        $res = $this->swoole->reset($redis->get('session:'.$session), $passnew, $row['data']['u_regtime']);
        if($res['data'])
        {
            $redis->del('session:'.$session);
            //拿到旧的token
            $oldToken = $redis->get('login:'.$row['data']['u_id']);
            //删掉旧token
            $redis->del('token:'.$oldToken['token']);
            return self::SUCCESS;
        }
        else
        {
            $redis->del('session:'.$session);
            return self::FAILED_CHANGE_PASSWORD;
        }
    }

    /**
     * 修改密码
     * @param  [type] $mobi    [description]
     * @param  [type] $passnew [description]
     * @param  [type] $regtime [description]
     * @return [type]          [description]
     */
    public function changePass($mobi, $passnew, $regtime)
    {
        if($this->swoole->reset($mobi, $passnew, $regtime)['data'])
            return self::SUCCESS;
        else
            return self::FAILED_CHANGE_PASSWORD;
    }

    /**
     * 编辑用户
     * @param  [string] $userInfo [存在用户token中的信息，方便重新构造token]
     * @param  [string] $newFile  [新的用户图片]
     * @return [type]           [description]
     */
    public function userEdit($token, $userInfo, $newname, $newFile)
    {
        //对修改的名字
        if($newname != '' && $newname != $userInfo['uname'])
        {
            if(!UserHelper::nameCheck($newname))
                return self::ERROR_NAME;

            $res = $this->swoole->checkUserName($newname, $userInfo['uid']);
            if($res['data'] == '1')
                return self::EXIST_NAME;
        }
        else
            $newname = $userInfo['uname'];

        if(!empty($newFile))
        {
            $picInfo = Common::makePath($this->di['sysconfig']['userPic'], $userInfo['mobi']);
            $pic = $picInfo['picUrl'];
        }
        else
        {
            $pic = $userInfo['pic'];
        }
        $res = $this->swoole->modifyUser($newname, $userInfo['uid'], $pic);
        if($res['data'] == '1')
        {
            if(!empty($newFile))
            {
                //上传图片
                if($this->swoole->uploadAvatar($newFile['tmp_name'], filesize($newFile['tmp_name']), $picInfo['rename'], $picInfo['path']))
                    //更新用户的缓存头像
                    $userInfo['pic'] = $this->di['sysconfig']['userPicServer'] . $picInfo['picUrl'];
                else
                    return self::FAILED_UPLOAD;
            }

            $redisObj = new RedisLib($this->di);
            $redis = $redisObj->getRedis();
            $redis->setex('token:' . $token, $this->di['sysconfig']['tokenTime'], $userInfo);
            return array('flag' => (string)self::SUCCESS, 'u_pic' => $userInfo['pic']);
        }
        else
        {
            return self::FAILED_UPDATE;
        }
    }

    /**
     * [退出登录]
     * @param  [string] $uid   [description]
     * @param  [string] $token [description]
     * @return [type]        [description]
     */
    public function logout($uid, $token)
    {
        $devtags = $this->initModel('\Appserver\Mdu\Models\DevtagsModel');
        
        $devtags->del($uid);
        //删除用户相关的deviceoken
        $devtags->del($uid);
        //删除token
        $redisObj = new RedisLib($this->di);
        $redis = $redisObj->getRedis();
        $userInfo = $redis->del('token:' . $token);
        return self::SUCCESS;
    }

    /**
     * [用户体验模式数据获取]
     * @return [type] [description]
     */
    public function trial($lat, $lng)
    {
        try{
            $trial = new \Appserver\Utils\RpcService($this->di['sysconfig']['thriftConf']['ip'], $this->di['sysconfig']['thriftConf']['port']);
            $trialData = json_decode($trial->GetTrialData($lat, $lng, (string)$_SERVER['REQUEST_TIME']), true);
            if(!empty($trialData) && is_array($trialData))
            {
                $msgList = $trialData['msgList'];
                $picUrl = $this->di['sysconfig']['msgsPicServer'] . '/' . $this->di['sysconfig']['msgPic'] . '/%s.png';
                foreach($msgList as $k => $val)
                {
                    $val['title'] = $this->di['sysconfig']['msgTitle'][$val['msg_type']];
                    $val['pic'] = sprintf($picUrl, $val['msg_type']);
                    if($val['msg_type'] === '1' || $val['msg_type'] === '3')
                        $trialData['dailylist'][] = $val;
                    else
                        $trialData['syslist'][] = $val;
                }
                unset($trialData['msgList']);

                if(!isset($trialData['dailylist']))
                {
                    $trialData['dailylist'] = array();
                }
                if(!isset($trialData['syslist']))
                {
                    $trialData['syslist'] = array();
                }

                return array('flag' => '1', 'data' => $trialData);
            }
            else
                return self::FAILED_UPDATE_DATA;

        }catch(\Exception $e)
        {
            return self::FAILED_GET_DATA;
        }
    }

    /**
     * [检查刚注册的用户的状态]
     * 发现该用户在注册前已经被添加为亲人时，完成与对应宝贝的关系绑定
     * @return [type] [description]
     */
    public function checkNewUser($uid, $mobi)
    {
        $redisObj = new RedisLib($this->di);
        $redis = $redisObj->getRedis();

        $babyList = $redis->get(sprintf($this->di['sysconfig']['unregUser'], $mobi));
        if(!empty($babyList))
        {
            $query = '';
            foreach($babyList as $babyId => $val)
            {
                $bids[] = $babyId;
                $query .= '('.
                    $babyId . ',' . $uid .',"' . $val['uname'] .'",'. $val['relation'] . ',' . $_SERVER['REQUEST_TIME'] . ',' . 1 .
                '),';
            }

            $family = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
            $family->batchInsert(rtrim($query, ','));

            //该用户注册完毕，删除该用户之前未注册的缓存信息
            $redis->del(sprintf($this->di['sysconfig']['unregUser'], $mobi));

            //从宝贝列表的缓存中去掉对应的未注册用户信息
            foreach($bids as $babyId)
            {
                $data = $redis->get(sprintf($this->di['sysconfig']['unregUserByBid'], $babyId));
                unset($data[$mobi]);
                if(empty($data))
                    $redis->del(sprintf($this->di['sysconfig']['unregUserByBid'], $babyId));
                else
                    $redis->set(sprintf($this->di['sysconfig']['unregUserByBid'], $babyId), $data);
            }
        }

        return true;

    }
}