<?php

namespace Appserver\v2\Controllers;

use Appserver\Mdu\Modules\UnionModule as Union,
    Appserver\Mdu\Modules\DevicesModule as Devices;

class UnionController extends ControllerBase
{

    public $userInfo;
    public $union;
    public $devices;

    public function initialize()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $this->union = new Union;
        $this->devices = new Devices;
    }

    /**
     * [用户刚注册，一次性添加宝贝童鞋，以及绑定童鞋法操作]
     */
    public function addAction()
    {
        if(($res = $this->devices->getShoeIdByQr($this->_sanReq['shoe_qr'])) != '1')
            $this->_showMsg($res, $this->di['flagmsg'][$res]);

        $res = $this->union->add($this->userInfo['uid'], $this->_sanReq['nick'], $this->_sanReq['sex'],
        $this->_sanReq['birthday'], $_SERVER['REQUEST_TIME'], $this->_sanReq['shoe_qr'], $this->_sanReq['name'], $_FILES, $this->_sanReq['weight'], $this->_sanReq['height']);
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }
}