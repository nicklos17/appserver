<?php

namespace Appserver\v1\Controllers;

use Appserver\Mdu\Modules\CheckinModule as Checkin;

class CheckinController extends ControllerBase
{
    public function indexAction()
    {
        $userInfo = $this->_getToken($this->_sanReq['token']);
        $checkin = new Checkin;
        $res = $checkin->userCheckin($userInfo['uid'], $userInfo['level']);
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }
}