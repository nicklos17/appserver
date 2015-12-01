<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\SwooleUserClient as SwooleUserClient,
    Appserver\Utils\RedisLib,
    Appserver\Utils\RpcService;

class CaptchaModule extends ModuleBase
{

    const SUCCESS = '1';
    const FAILED = 10041;
    const GET_TOO_CAPTCHA = 10044;
    const EXIST_MOBILE = 10003;
    const NON_EXIST_MOBILE = '00001';
    const ERROR_CAPTCHA = 10005;
    const INVALID_CAPTCHA = '00002';
    const HAVE_FAMILY_RELATION = 10077;

    public $captcha;
    protected $userSwoole;

    public function __construct()
    {
        $this->captcha = $this->initModel('\Appserver\Mdu\Models\CaptchaModel');
        $this->family = $this->initModel('\Appserver\Mdu\Models\FamilyModel');
        $this->baby = $this->initModel('\Appserver\Mdu\Models\BabyModel');
        $this->userSwoole = new SwooleUserClient(
            $this->di['sysconfig']['swooleConfig']['ip'],
            $this->di['sysconfig']['swooleConfig']['port']);
    }

    /**
     * 生成短信验证码
     * 判断用户获得验证码的资格:type:
     * 1 - 只有未注册的用户才能获取注册验证码
     * 3 - 只有注册过的用户才能获取添加亲人验证码
     * 7 - 只有注册过的用户才能获取忘记密码的验证码
     * 9 - 只有注册过的用户才能获取修改密码的验证码
     * 11 - 只有注册过的用户才能获取第三方注册验证码
     */
    public function makeCaptcha($type, $mobi = '', $babyId = '')
    {
        $userInfo = $this->userSwoole->getUserInfoByMobi($mobi);
        if(($type == '1' || $type == '11') && !empty($userInfo['data']))
        {
            return self::EXIST_MOBILE;
        }
        if(($type == '3' || $type == '7' || $type == '9') && empty($userInfo['data']))
        {
            return self::NON_EXIST_MOBILE;
        }

        //如果是添加副号，则须判断是否登录以及该手机号是否已经添加
        if($type == '3')
        {
            //如果该号已经是宝贝亲人号，则不发送短信
            $checkRel = $this->family->checkRelation($userInfo['data']['u_id'], $babyId);
            if(!empty($checkRel))
                return self::HAVE_FAMILY_RELATION;

            $babyInfo = $this->baby->getBabyName($babyId);
        }

        $RedisLib = new \Appserver\Utils\RedisLib($this->di);
        $redis = $RedisLib::getRedis();
        //生成验证码ename123
        if($redis->get($mobi.$type) == FALSE)
        {
            $captchaCode = \Appserver\Utils\Common::random(4);
            $redis->setex($mobi.$type, $this->di['sysconfig']['capTime'], $captchaCode);
        }
        else
            $captchaCode = $redis->get($mobi.$type);

        switch ($type)
        {
            case 1:
                $message = sprintf($this->di['sysconfig']['regCaptchaMsg'], $captchaCode);
                break;
            case 3:
                $message = sprintf($this->di['sysconfig']['addRelCaptchaMsg'], $captchaCode, $babyInfo['baby_nick']);
                break;
            case 7:
                $message = sprintf($this->di['sysconfig']['resetCaptchaMsg'], $captchaCode);
                break;
            case 9:
                $message = sprintf($this->di['sysconfig']['changeCaptchaMsg'], $captchaCode);
                break;
            case 11:
                $message = sprintf($this->di['sysconfig']['regCaptchaMsg'], $captchaCode);
                break;
        }

        $lastCapthaInfo = $this->captcha->getLastCapthaInfo($mobi, $type);
        if($_SERVER['REQUEST_TIME'] - $lastCapthaInfo['mc_addtime'] < 60)
        {
            return self::GET_TOO_CAPTCHA;
        }

        //验证码入库
        $data = $this->captcha->addCaptcha($mobi,$type,$_SERVER['REQUEST_TIME'],$captchaCode);
        if($data)
        {
            $sendMsg = new RpcService($this->di['sysconfig']['thriftConf']['ip'], $this->di['sysconfig']['thriftConf']['port']);
            $sendMsg->smsSend($mobi, $message);
            return self::SUCCESS; 
        }
        else
        {
            return self::FAIL; 
        }
    }


    /**
     * 使用验证码，验证码失效返回提示信息，验证码可用则激活验证
     * @param  [string] $mobi    [手机号]
     * @param  [string] $type    [验证码类型：1-注册 3-添加亲人 7-找回密码 9-修改密码 11-第三方绑定]
     * @param  [string] $captcha [验证码]
     * @param  [string] $useTime [验证码使用时间]
     * @return [type]          [description]
     */
    public function checkCaptcha($mobi, $type, $captcha, $useTime)
    {
        //限制验证次数
        $redisObj = new RedisLib($this->di);
        $redis = $redisObj->getRedis();
        $redisKey = sprintf($this->di['sysconfig']['checkCaptcha']['redisKey'], $mobi);
        //获取用户验证次数
        $checkCount = $redis->get($redisKey);
        if(!$checkCount || $checkCount < $this->di['sysconfig']['checkCaptcha']['count'])
        {
            $redis->multi()->incr($redisKey)->setTimeout($redisKey, $this->di['sysconfig']['checkCaptcha']['ttl'])->exec();
        }
        elseif($checkCount >= $this->di['sysconfig']['checkCaptcha']['count'])
            return self::GET_TOO_CAPTCHA;


        $userInfo = $this->userSwoole->getUserInfoByMobi($mobi);
        if(($type == '1' || $type == '11') && !empty($userInfo['data']))
        {
            return self::EXIST_MOBILE;
        }
        if(($type == '3' || $type == '7' || $type == '9') && empty($userInfo['data']))
        {
            return self::NON_EXIST_MOBILE;
        }
        //获取验证码是否正确，以及是否被使用
        $captchaInfo = $this->captcha->getCapthaTime($mobi, $type, $captcha);
        if(empty($captchaInfo))
        {
            return self::ERROR_CAPTCHA;
        }
        elseif($captchaInfo['mc_validtime'] != 0)
        {
            return self::INVALID_CAPTCHA;
        }
        
        if($useTime - $captchaInfo['mc_addtime'] > 0 && $useTime - $captchaInfo['mc_addtime'] <= $this->di['sysconfig']['capTime'])
        {
            if($this->captcha->updateCaptcha($mobi, $captcha, $type, $useTime) == FALSE)
            {
                $this->showMsg('33333', $this->config->item('flagMsg')['33333']);
            }
            return self::SUCCESS;
        }
        else
        {
            return self::INVALID_CAPTCHA;
        }

    }

}