<?php

namespace Appserver\v3\Controllers;

use Appserver\Api\ImgUpload,
       Appserver\Utils\Common,
       Appserver\Utils\RpcService,
       Appserver\Mdu\Modules\DevicesModule as Devices;

class ShoeController extends ControllerBase
{
    const SUCCESS = '1';
    const INVALID_OPERATE = 11111;
    const NON_EXIST_SHOE = 10034;
    const NOT_USER_DEV = 10028;
    const NOT_BIND_BABY = 10030;
    const FAILED_OFF = 10045;
    const VALIDED_SHOE = 11077;

    private $devices;
    private $userInfo;

    public function initialize()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $this->devices = new Devices;
    }

    /**
     * 童鞋添加
     */
    public function addAction()
    {
        $res = $this->devices->addAndBind($this->userInfo['uid'], $this->_sanReq['shoe_qr'], $this->_sanReq['baby_id']);
        if($res == self::SUCCESS)
            $this->_showMsg($res);
        elseif($res == self::VALIDED_SHOE)
            $this->_returnResult(array('flag' => (string)$res, 'msg' => $this->di['flagmsg'][$res], 'tel'=> $this->di['sysconfig']['service-phone']));
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * 删除童鞋
     */
    public function delAction()
    {
        $res = $this->devices->getUidByDev($this->_sanReq['shoe_id']);
        if(empty($res))
            $this->_showMsg(self::NON_EXIST_SHOE, $this->di['flagmsg'][self::NON_EXIST_SHOE]);

            if(($ret = $this->devices->removeShoe($this->_sanReq['shoe_id'], $this->userInfo['uid'])) == self::SUCCESS)
                $this->_showMsg($ret);
            else
                $this->_showMsg($ret, $this->di['flagmsg'][$ret]);
    }
}
