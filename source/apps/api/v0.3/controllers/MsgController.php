<?php

namespace Appserver\v3\Controllers;
use Appserver\Mdu\Modules\MsgModule as Msg;

class MsgController extends ControllerBase
{
    const SUCCESS = '1';
    const INVALID_OPERATE = 11111;

    private $msg;

    public function initialize()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $this->msg = new Msg;
    }

    /**
     * [显示所有的宝贝消息]
     * @return [type] [description]
     */
    public function indexAction()
    {
        $this->_checkRelation($this->userInfo['uid'], $this->_sanReq['baby_id']);
        $sinceId = isset($this->_sanReq['since_id'])?$this->_sanReq['since_id']:'';
        $maxId = isset($this->_sanReq['max_id'])?$this->_sanReq['max_id']:'';
        if($sinceId && $maxId)
            $this->_showMsg(self::INVALID_OPERATE, $this->di['flagmsg'][self::INVALID_OPERATE]);
        $this->_returnResult(
            array(
                'flag' => self::SUCCESS,
                'msglist' => $this->msg->getMsgList($this->_sanReq['baby_id'], $this->_sanReq['count'], $sinceId, $maxId, '')
            )
        );
    }

}
