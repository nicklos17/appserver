<?php

namespace Appserver\v1\Controllers;

use Appserver\Mdu\Modules\CaptchaModule as Captcha;

class MobileController extends ControllerBase
{

    const SUCCESS = '1';
    const INVALID_OPERATE = 11111;

    /**
     * 发送验证码
     * @return [type] [description]
     */
    public function captchaAction()
    {
        $captchaObj = new Captcha();
        if($this->_sanReq['type'] == '3')
        {
            $userInfo = $this->_getToken($this->_sanReq['token']);
            if(isset($this->_sanReq['baby_id']))
                $babyId = $this->_sanReq['baby_id'];
            else
                $this->_showMsg((string)self::INVALID_OPERATE, $this->di['flagmsg'][self::INVALID_OPERATE]);
        }
        else
            $babyId = '';

        $res = $captchaObj->makeCaptcha($this->_sanReq['type'], $this->_sanReq['mobi'], $babyId);

        if($res === self::SUCCESS)
            $this->_showMsg((string)$res);
        else
            $this->_showMsg((string)$res, $this->di['flagmsg'][$res]);
    }
}
