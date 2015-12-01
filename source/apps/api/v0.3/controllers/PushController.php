<?php

namespace Appserver\v3\Controllers;

use Appserver\Mdu\Modules\PushModule as Push;
use Appserver\Mdu\Modules\UserModule as User;

class PushController extends ControllerBase
{

    const SUCCESS = '1';

    /**
     * [通知宝贝的主号绑定童鞋]
     * @return [type] [description]
     */
    public function binddevAction()
    {
        $userInfo = $this->_getToken($this->_sanReq['token']);
        $this->push = new Push($this->di, $this->di['sysconfig']['pushForActive'], 0);
        $res = $this->push->pushBinddevMsg($this->_sanReq['baby_id'], $userInfo['uname'] ? $userInfo['uname'] : $userInfo['mobi']);

        if($res == self::SUCCESS)
            $this->_showMsg($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

}