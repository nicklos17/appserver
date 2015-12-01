<?php

namespace Appserver\v1\Controllers;
use Appserver\Mdu\Modules\FencesModule as Fences;

class FencesController extends ControllerBase
{
    const SUCCESS = '1';
    const INVALID_OPERATE = 11111;
    const INVALID_FENCES_TIME = 10087;

    private $fences;

    public function initialize()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $this->fences = new Fences;
    }

    /**
     * 添加围栏
     * 检查用户是否有权限进行操作
     */
    public function addAction()
    {
        $day = explode(',', $this->_sanReq['validtime']);
        $res = array();
        foreach($day as $val)
        {
            //只能是1-7的数字，分别代表周一到周日
            if(!in_array($val, array(1,2,3,4,5,6,7)))
                $this->_showMsg(self::INVALID_FENCES_TIME, $this->di['flagmsg'][self::INVALID_FENCES_TIME]);
            $res[] = $val;
        }
        $validtime = implode(',', $res);
        if(($ret = $this->fences->addFences($this->_sanReq['baby_id'], $this->_sanReq['coordinates'], $this->_sanReq['name'],
        $this->_sanReq['radius'], $this->_sanReq['place'], $validtime, $_SERVER['REQUEST_TIME'])) == self::SUCCESS)
            $this->_showMsg($ret);
        else
            $this->_showMsg($ret, $this->di['flagmsg'][$ret]);
    }

    /**
     * 围栏编辑
     * 检查用户是否有权限进行操作
     */
    public function editAction()
    {
        $day = explode(',', $this->_sanReq['validtime']);
        $res = array();
        foreach($day as $val)
        {
            //只能是1-7的数字，分别代表周一到周日
            if(!in_array($val, array(1,2,3,4,5,6,7)))
                $this->_showMsg(self::INVALID_FENCES_TIME, $this->di['flagmsg'][self::INVALID_FENCES_TIME]);
            $res[] = $val;
        }
        $validtime = implode(',', $res);
        if(($ret = $this->fences->editFences($this->_sanReq['fence_id'], $this->_sanReq['coordinates'], $this->_sanReq['name'],
        $this->_sanReq['radius'], $this->_sanReq['place'], $validtime, $_SERVER['REQUEST_TIME'])) == self::SUCCESS)
            $this->_showMsg($ret);
        else
            $this->_showMsg($ret, $this->di['flagmsg'][$ret]);
    }

    /*
     * 删除围栏
     */
    public function delAction()
    {
        if(($res = $this->fences->delFences($this->_sanReq['fences_id'], $_SERVER['REQUEST_TIME'])) == self::SUCCESS)
            $this->_showMsg($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }
    /**
     * 围栏列表
     */
    public function listAction()
    {
        //判断用户是否是宝贝的亲人，不是就不显示围栏列表。
         $this->_checkRelation($this->userInfo['uid'], $this->_sanReq['baby_id']);
        $fenList = $this->fences->showFenList($this->_sanReq['baby_id'], $this->_sanReq['count']);
        $this->_returnResult(array('flag' => self::SUCCESS, 'fenceslist' => $fenList));
    }
}
