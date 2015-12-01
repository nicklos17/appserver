<?php

namespace Appserver\v1\Controllers;

use Appserver\Mdu\Modules\FeedbackModule as Feedback;

class FeedbackController extends ControllerBase
{

    const SUCCESS = '1';

    protected $userInfo;
    protected $feedback;

    public function initialize()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $this->feedback = new Feedback;
    }

    public function indexAction()
    {
        $res = $this->feedback->writeForApp(
            $this->_sanReq['content'],
            $this->userInfo['uid'],
            empty($this->userInfo['uname']) ? $this->userInfo['mobi'] : $this->userInfo['uname'],
            $this->_sanReq['version'],
            $this->_sanReq['os']
        );
        if($res == self::SUCCESS)
            $this->_showMsg($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }
}