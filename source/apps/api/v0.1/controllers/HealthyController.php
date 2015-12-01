<?php

namespace Appserver\v1\Controllers;

use Appserver\Mdu\Modules\HealthyModule as Healthy;

class HealthyController extends ControllerBase
{

    public $userInfo;
    public $Healthy;

    public function initialize()
    {
        $this->healthy = new Healthy;

        //判断token，token有效则返回用户信息
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        //判断宝贝与用户是否存在关系
        $this->_checkRelation($this->userInfo['uid'], $this->_sanReq['baby_id']);
    }

    /**
     * [宝贝健康汇总]
     * @return [type] [description]
     */
    public function summaryAction()
    {
        $this->_returnResult($this->healthy->summary($this->_sanReq['baby_id']));
    }

    /**
     * [宝贝健康列表]
     * @return [type] [description]
     */
    public function listAction()
    {
        $date = isset($this->_sanReq['since_date']) ? $this->_sanReq['since_date'] : 0;
        $res = $this->healthy->getHealthyList($this->_sanReq['baby_id'], $this->_sanReq['count'], $date);

        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * [天气预报]
     * @return [type] [description]
     */
    public function foreAction()
    {
        if(isset($this->_sanReq['city']))
            $city = $this->_sanReq['city'];
        else
            $city = isset($this->_sanReq['City']) ? $this->_sanReq['City'] : '';
        $this->_returnResult($this->healthy->getFore($this->_sanReq['baby_id'], $city));
    }
}