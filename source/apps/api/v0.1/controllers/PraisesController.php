<?php

namespace Appserver\v1\Controllers;

use Appserver\Mdu\Modules\PraisesModule as Praises,
       Appserver\Mdu\Modules\LocusModule as Locus;

class PraisesController extends ControllerBase
{
    private $userInfo;
    private $praises;
    private $locus;

    const SUCCESS = '1';
    const INVALID_OPERATE = 11111;

    public function initialize()
    {
        //验证token
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $this->praises = new Praises;
        $this->locus = new Locus;
    }

    /**
     * 点赞
     */
    public function addAction()
    {
        $res = $this->praises->hit($this->_sanReq['token'], $this->_sanReq['locus_id']);
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * 赞列表
     * @return array
     */
    public function listAction()
    {
        $sinceId = isset($this->_sanReq['since_id'])?$this->_sanReq['since_id']:'';
        $maxId = isset($this->_sanReq['max_id'])?$this->_sanReq['max_id']:'';
        if($sinceId && $maxId)
            $this->_showMsg(self::INVALID_OPERATE, $this->di['flagmsg'][self::INVALID_OPERATE]);
        $this->_returnResult($this->praises->showPraisesList($this->_sanReq['locus_id'], $this->_sanReq['count'], $sinceId, $maxId));
    }

    /**
     * 取消赞
     */
    public function delAction()
    {
        $res = $this->praises->canclePraise($this->userInfo['uid'], $this->_sanReq['locus_id']);
        if(is_array($res))
        {
            $this->_returnResult(array('flag' => $res[0], 'praises' => $res[1]));
        }
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }
}
