<?php

namespace Appserver\v1\Controllers;

use Appserver\Mdu\Modules\RankModule as Rank;

class RankController extends ControllerBase
{

    public $rank;
    public function initialize()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $this->rank = new Rank;

        $this->_checkRelation($this->userInfo['uid'], $this->_sanReq['baby_id']);
    }

    /**
     * [今日行程排行榜]
     * @return [type] [description]
     */
    public function todayAction()
    {
        $res = $this->rank->getTodayRank(
            $this->_sanReq['baby_id'],
            $this->_sanReq['count'],
            isset($this->_sanReq['since_id']) ? $this->_sanReq['since_id'] : '',
            isset($this->_sanReq['max_id']) ? $this->_sanReq['max_id'] : ''
        );
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * [总行程排行榜]
     * @return [type] [description]
     */
    public function allAction()
    {
        $res = $this->rank->getAllRank(
            $this->_sanReq['baby_id'],
            $this->_sanReq['count'],
            isset($this->_sanReq['since_id']) ? $this->_sanReq['since_id'] : '',
            isset($this->_sanReq['max_id']) ? $this->_sanReq['max_id'] : ''
        );
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

}