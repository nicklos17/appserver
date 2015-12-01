<?php

namespace Appserver\v1\Controllers;

use Appserver\Mdu\Modules\OauthModule as Oauth,
    Appserver\Mdu\Modules\CaptchaModule as Captcha,
       Appserver\Mdu\Modules\DevtagsModule as DevTags;

class OauthController extends ControllerBase
{

    const SUCCESS = 1;

    private $oauth;

    public function initialize()
    {
        $this->oauth = new Oauth($this->di, $this->_sanReq['plat']);
    }

    /**
     * 第三方登录
     */
    public function loginAction()
    {
        $deviceToken = $this->_sanReq['deviceToken'] ? $this->_sanReq['deviceToken'] : '';
        $res = $this->oauth->thirdLogin($this->_sanReq['u_tags'], $this->_sanReq['access_token'], $deviceToken, $this->_sanReq['type']);
        if(is_array($res))
        {
            if($deviceToken)
            {
                $DevTagsObj = new DevTags();
                //记录deviceToken
                $divecesTokenInfo = $DevTagsObj->saveDeviceToken(
                    $res['u_id'],
                    $deviceToken, 
                    $this->_sanReq['type'],
                    isset($this->_sanReq['cver']) ? $this->_sanReq['cver'] : ''
                );
                if(is_array($divecesTokenInfo))
                {
                    $res['disturb'] = $divecesTokenInfo['disturbMode'];
                }
                else
                    $res['disturb'] = '1';
            }

            $this->_returnResult($res);
        }
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * 第三方注册补充资料
     */
    public function regAction()
    {
        $this->captchaObj = new Captcha();
        $captchaResult = $this->captchaObj->checkCaptcha($this->_sanReq['mobi'], '1', $this->_sanReq['captcha'], $_SERVER['REQUEST_TIME']);
        if($captchaResult != 1)
        {
            $this->_showMsg($captchaResult, $this->di['flagmsg'][$captchaResult]);
        }
        $res = $this->oauth->reg($this->_sanReq['session'], $this->_sanReq['u_tags'],
        $this->_sanReq['deviceToken'] ? $this->_sanReq['deviceToken'] : '', $this->_sanReq['mobi'],
        $this->_sanReq['name'], $this->_sanReq['pass'], $this->_sanReq['pic'], $_FILES, $this->_sanReq['type']);
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * 用户在应用内绑定第三方平台
     */
    public function bindAction()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $res = $this->oauth->bind($this->_sanReq['token'], $this->_sanReq['access_token'], $this->_sanReq['u_tags']);
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * 解绑第三方平台
     */
    public function delAction()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $res = $this->oauth->unbind($this->_sanReq['token']);
        if($res == self::SUCCESS)
            $this->_showMsg($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }
}
