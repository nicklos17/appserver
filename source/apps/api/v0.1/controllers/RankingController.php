<?php

namespace Appserver\v1\Controllers;

use Appserver\Mdu\Modules\RankModule as Rank;

class RankingController extends ControllerBase
{

    public $rank;
    public function initialize()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $this->rank = new Rank;
    }

    /**
     * [显示1.3版本的排行版]
     * @return [type] [description]
     */
    public function indexAction()
    {
        $this->_checkRelation($this->userInfo['uid'], $this->_sanReq['baby_id']);

        $res = $this->rank->getOldRank($this->userInfo['uid'], $this->_sanReq['baby_id']);
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }
}