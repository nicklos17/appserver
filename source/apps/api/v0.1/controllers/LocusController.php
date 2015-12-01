<?php

namespace Appserver\v1\Controllers;

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
    public function listAction()
    {
        //检查用户权限
        $this->_checkRelation($this->userInfo['uid'], $this->_sanReq['baby_id']);

        $res = $this->locusObj->getLocusList($this->userInfo['uid'], $this->_sanReq['baby_id'], $this->_sanReq['count'],
        isset($this->_sanReq['since_id']) ? $this->_sanReq['since_id'] : '', isset($this->_sanReq['max_id']) ? $this->_sanReq['max_id'] : '');
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }
    
    /**
     * 显示日历轨迹列表
     */
    public function callistAction()
    {
        //检查用户权限
        $this->_checkRelation($this->userInfo['uid'], $this->_sanReq['baby_id']);

        $this->_returnResult(
            $this->locusObj->getCalList($this->_sanReq['baby_id'], isset($this->_sanReq['month']) ? $month = $this->_sanReq['month'] : '')
        );
    }
    
    /**
     * 轨迹标注
     */
    public function markAction()
    {
        $res = $this->locusObj->mark($this->userInfo['uid'], $this->_sanReq['locus_id'], $this->_sanReq['mark'], $this->_sanReq['tags']);
        if($res == SELF::SUCCESS)
            $this->_showMsg($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * 轨迹消息提示列表
     */
    public function messageAction()
    {
        //检查用户权限
        $this->_checkRelation($this->userInfo['uid'], $this->_sanReq['baby_id']);

        $this->_returnResult(
            $this->locusObj->getMessList($this->userInfo['uid'], $this->_sanReq['baby_id'])
        );
    }

    /**
     * 获取轨迹最新的信息：包括赞数，评论数以及标注内容
     */
    public function freshAction()
    {
        $res = $this->locusObj->getNewInfo($this->userInfo['uid'], $this->_sanReq['locus_id']);
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }
}