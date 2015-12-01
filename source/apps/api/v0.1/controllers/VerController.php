<?php
namespace Appserver\v1\Controllers;

use Appserver\Mdu\Modules\VerModule as Ver;

class VerController extends ControllerBase
{

    const FAILED_GET = 22222;
    const SUCCESS = '1';
    const NON_UPDATE = -1;

    public $userInfo;
    public $ver;

    public function initialize()
    {
       $this->ver = new Ver;
    }

    /**
     * 检查软件版本是否可以更新
     */
    public function indexAction()
    {
        $res = $this->ver->getSoftVerInfo($this->_sanReq['type'], $this->_sanReq['app_ver']);
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * [检查硬件版本更新]
     * @return [type] [description]
     */
    public function hardwareAction()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $this->_returnResult($this->ver->getHardInfo($this->_sanReq['time'], $this->userInfo['uid']));
    }
}