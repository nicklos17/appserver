<?php
namespace Appserver\v1\Controllers;

use Appserver\Mdu\Modules\MyselfModule as Myself;

class MyselfController extends ControllerBase
{

    const FAILED_GET = 22222;
    const SUCCESS = '1';
    const NON_UPDATE = -1;

    public function initialize()
    {
       $this->Myself = new Myself;
    }

    /**
     * [返回用户的等级和云币等信息]
     * @return [type] [description]
     */
    public function indexAction()
    {
        $res = $this->Myself->getUserLevelInfo($this->di, $this->_getToken($this->_sanReq['token']));
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * [开启/关闭免打扰模式]
     * @return [type] [description]
     */
    public function disturbAction()
    {
        $uid = $this->_getToken($this->_sanReq['token'])['uid'];
        if(isset($this->_sanReq['start']) && isset($this->_sanReq['end']))
            $res = $this->Myself->disturb($uid, $this->_sanReq['disturb'], $this->_sanReq['start'], $this->_sanReq['end']);
        else
            $res = $this->Myself->disturb($uid, $this->_sanReq['disturb']);

        if($res === self::SUCCESS)
            $this->_showMsg($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }
}