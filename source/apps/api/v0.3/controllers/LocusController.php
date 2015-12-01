<?php

namespace Appserver\v3\Controllers;

use Appserver\Mdu\Modules\LocusModule as Locus;

class LocusController extends ControllerBase
{

    const SUCCESS = '1';

    private $userInfo;
    private $locusObj;

    public function initialize()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $this->locusObj = new Locus;
    }

    /**
     * 显示轨迹列表
     */
    public function idlistAction()
    {
        //检查用户权限
        $this->_checkRelation($this->userInfo['uid'], $this->_sanReq['baby_id']);

        $this->_returnResult(array('flag' => self::SUCCESS, 'list' => $this->locusObj->getLocusIds($this->_sanReq['baby_id'], $this->_sanReq['locus_id'])));
    }
}