<?php
namespace Appserver\v1\Controllers;

use \Phalcon\Acl\Exception as E,
    Appserver\Mdu\Modules\CaptchaModule as Captcha,
    Appserver\Mdu\Modules\UserModule as User,
    Appserver\Mdu\Modules\DevtagsModule as DevTags;

class UserController extends ControllerBase
{

    private $captchaObj;
    public $user;

    public function initialize()
    {
       $this->user = new User($this->di);
    }

    /**
     * 用户注册
     * @return [type] [description]
     */
    public function regAction()
    {
        $type = $this->_sanReq['type'] ? $this->_sanReq['type'] : '';

        $mobi = $this->_sanReq['mobi'];
        $this->captchaObj = new Captcha();
        $captchaResult = $this->captchaObj->checkCaptcha($mobi, '1', $this->_sanReq['captcha'], $_SERVER['REQUEST_TIME']);
        if($captchaResult != 1)
        {
            $this->_showMsg($captchaResult, $this->di['flagmsg'][$captchaResult]);
        }
        if(!isset($this->_sanReq['file']))
        {
            $this->_sanReq['file'] = false;
        }
        if(isset($this->_sanReq['deviceToken']))
            $deviceToken = $this->_sanReq['deviceToken'];
        else
            $deviceToken = '';
        //执行注册操作
        $regRes = $this->user->reg($mobi, $this->_sanReq['pass'], $_FILES, $this->_sanReq['type'], $deviceToken, $type);
        if(is_array($regRes))
        {
            if($deviceToken)
            {
                $DevTagsObj = new DevTags();
                //记录deviceToken
                $divecesTokenInfo = $DevTagsObj->saveDeviceToken(
                    $regRes['u_id'],
                    $deviceToken,
                    $type,
                    isset($this->_sanReq['cver']) ? $this->_sanReq['cver'] : ''
                );
                if(is_array($divecesTokenInfo))
                {
                    $regRes['disturb'] = $divecesTokenInfo['disturbMode'];
                }
            }
            else
                $regRes['disturb'] = '1';

            $this->_returnResult($regRes);
        }
        else
            $this->_showMsg($regRes, $this->di['flagmsg'][$regRes]);

    }

    /**
     *用户登陆
     * @return [type] [description]
     */
    public function loginAction()
    {
        $type = $this->_sanReq['type'] ? $this->_sanReq['type'] : '';
        if(isset($this->_sanReq['deviceToken']))
            $deviceToken = $this->_sanReq['deviceToken'];
        else
            $deviceToken = '';
        $loginRes = $this->user->login(
            $this->_sanReq['mobi'],
            $this->_sanReq['pass'],
            $this->_sanReq['type'], 
            $deviceToken,
            $type
        );

        if(is_array($loginRes))
        {
            if($deviceToken)
            {
                $DevTagsObj = new DevTags();
                //记录deviceToken
                $divecesTokenInfo = $DevTagsObj->saveDeviceToken(
                    $loginRes['u_id'],
                    $deviceToken,
                    $type,
                    isset($this->_sanReq['cver']) ? $this->_sanReq['cver'] : ''
                );
                if(is_array($divecesTokenInfo))
                {
                    $loginRes['disturb'] = $divecesTokenInfo['disturbMode'];
                }
            }
            else
                $loginRes['disturb'] = '1';

            $this->_returnResult($loginRes);
        }
        else
            $this->_showMsg($loginRes, $this->di['flagmsg'][$loginRes]);
    }

    /**
     * 修改密码
     * @return [type] [description]
     */
    public function changeAction()
    {
        $userInfo = $this->_getToken($this->_sanReq['token']);
        $captchaObj = new Captcha();
        $captchaResult = $captchaObj->checkCaptcha($userInfo['mobi'], 9, $this->_sanReq['captcha'], $_SERVER['REQUEST_TIME']);
        if($captchaResult != 1)
        {
            $this->_showMsg($captchaResult, $this->di['flagmsg'][$captchaResult]);
        }
        if(($resetRes = $this->user->changePass($userInfo['mobi'], $this->_sanReq['passnew'], $userInfo['regtime'])) == 1)
            $this->_showMsg('1');
        else
            $this->_showMsg($resetRes, $this->di['flagmsg'][$resetRes]);
    }

    /**
     * [用户编辑]
     * @return [type] [description]
     */
    public function editAction()
    {
        $userInfo = $this->_getToken($this->_sanReq['token']);
        $res = $this->user->userEdit(
            $this->_sanReq['token'],
            $userInfo,
            isset($this->_sanReq['uname']) ? $this->_sanReq['uname']: '',
            empty($_FILES) ? '' : $_FILES['file']
        );
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * [退出登录]
     * @return [type] [description]
     */
    public function logoutAction()
    {
        $userInfo = $this->_getToken($this->_sanReq['token']);
        $this->user->logout($userInfo['uid'], $this->_sanReq['token']);
        $this->_showMsg('1');
    }

    /**
     * [用户体验模式]
     * @return [type] [description]
     */
    public function trialAction()
    {
        $res = $this->user->trial($this->_sanReq['lat'], $this->_sanReq['lng']);

        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }
}