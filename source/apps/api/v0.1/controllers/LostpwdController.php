<?php

namespace Appserver\v1\Controllers;

use Appserver\Mdu\Modules\UserModule as User,
    Appserver\Utils\RedisLib,
    Appserver\Mdu\Modules\CaptchaModule as Captcha;

class LostpwdController extends ControllerBase
{
    private $user;
    private $captcha;

    public function initialize()
    {
        $this->user = new User($this->di);
        $this->captcha = new Captcha();
    }

    /**
     * 重置密码之校验验证码
     * @return [type] [description]
     */
    public function captchaAction()
    {
        $mobi = $this->_sanReq['mobi'];
        $captchaResult = $this->captcha->checkCaptcha($mobi, 7, $this->_sanReq['captcha'], $_SERVER['REQUEST_TIME']);
        if($captchaResult !== 1)
        {
            $this->_showMsg($captchaResult, $this->di['flagmsg'][$captchaResult]);
        }
            $redisObj = new RedisLib($this->di);
            $redis = $redisObj::getRedis();
            $session = md5($mobi.$_SERVER['REQUEST_TIME']);
            $redis->setex('session:'.$session, $this->di['sysconfig']['tokenTime'], $mobi);
            $this->_showMsg('1',$session);
    }

    /**
     * 密码重置
     * @return [type] [description]
     */
    public function resetAction()
    {
        $res = $this->user->resetPass($this->_sanReq['session'], $this->_sanReq['passnew']);
        if($res == '1')
            $this->_showMsg('1');
        else
            $this->_showMsg($resetRes, $this->di['flagmsg'][$resetRes]);
    }
}