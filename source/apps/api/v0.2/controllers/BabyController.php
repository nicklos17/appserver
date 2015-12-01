<?php

namespace Appserver\v2\Controllers;

use Appserver\Utils\ImgUpload,
       Appserver\Utils\Common,
       Appserver\Mdu\Modules\BabyModule as Baby,
       Appserver\Mdu\Modules\FamilyModule as Family;

class BabyController extends ControllerBase
{
    const NOT_BABY_FAMILY = 10079;
    const INVALID_OPERATE = 11111;
    const SUCCESS = '1';
    const BABY_BIND_SHOE = 11084;

    public $baby;
    public $family;
    public $userInfo;

    public function initialize()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $this->baby = new Baby;
        $this->family = new Family;
    }

    /**
     * 添加宝贝信息
     */
    public function addAction()
    {
        $res = $this->baby->addBaby($this->userInfo['uid'], $this->_sanReq['name'], $this->_sanReq['sex'],
        $this->_sanReq['birthday'], $_SERVER['REQUEST_TIME'], $_FILES, '');
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * 添加宝贝信息
     */
    public function addrelAction()
    {
        $res = $this->baby->addBaby($this->userInfo['uid'], $this->_sanReq['name'], $this->_sanReq['sex'],
        $this->_sanReq['birthday'], $_SERVER['REQUEST_TIME'], $_FILES, $this->_sanReq['rel'], $this->_sanReq['weight'], $this->_sanReq['height']);
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * 宝贝编辑
     */
    public function editAction()
    {
        $data = array();
        if(!empty($_FILES['file']['tmp_name']))
            $data['baby_pic'] = $_FILES;
        $data['baby_sex'] = $this->_sanReq['sex'];
        $data['baby_nick'] = $this->_sanReq['nick'];
        $data['baby_id'] = $this->_sanReq['baby_id'];
        $data['baby_birthday'] = $this->_sanReq['birthday'];
        $data['baby_weight'] = $this->_sanReq['weight'];
        $data['baby_height'] = $this->_sanReq['height'];
        $res = $this->baby->editBaby($data);
        if($res['ret'] == 1)
        {
            if($res['data'])
                $this->_returnResult(array('flag' => self::SUCCESS, 'baby_pic' => $res['data']));
            else
                $this->_returnResult(array('flag' => self::SUCCESS));
        }
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res['ret']]);
    }

}