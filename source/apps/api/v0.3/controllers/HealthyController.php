<?php

namespace Appserver\v3\Controllers;

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
     * [宝贝健康列表]
     * @return [type] [description]
     */
    public function listAction()
    {
        $date = isset($this->_sanReq['since_date']) ? $this->_sanReq['since_date'] : 0;
        $res = $this->healthy->getHealthyListFor03($this->_sanReq['baby_id'], $this->_sanReq['count'], $date);

        if(is_array($res))
            $this->_returnResult(array('flag' => '1', 'list' => $res));
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }
}