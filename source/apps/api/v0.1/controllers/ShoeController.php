<?php

namespace Appserver\v1\Controllers;

use Appserver\Api\ImgUpload,
       Appserver\Utils\Common,
       Appserver\Utils\RpcService,
       Appserver\Mdu\Modules\DevicesModule as Devices;

class ShoeController extends ControllerBase
{
    const SUCCESS = '1';
    const INVALID_OPERATE = 11111;
    const NON_EXIST_SHOE = 10034;
    const NOT_USER_DEV = 10028;
    const NOT_BIND_BABY = 10030;
    const FAILED_OFF = 10045;

    private $devices;
    private $userInfo;

    public function initialize()
    {
        $this->userInfo = $this->_getToken($this->_sanReq['token']);
        $this->devices = new Devices;
    }

    /**
     * 童鞋添加
     */
    public function addAction()
    {
        $res = $this->devices->addShoe($this->userInfo['uid'], $this->_sanReq['shoe_qr']);
        if($res == self::SUCCESS)
            $this->_showMsg($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * 童鞋列表
     */
    public function listAction($code = '')
    {
        if($code == '')
        {
            if(empty($this->_sanReq['baby_id']))
                $result = $this->devices->shoeListByUid($this->userInfo['uid'], $this->_sanReq['count']);
            else
            {
                $this->_checkRelation($this->userInfo['uid'], $this->_sanReq['baby_id']);
                $result = $this->devices->shoeListByBabyId($this->_sanReq['baby_id']);
            }

            if(!empty($result))
            {
                foreach($result as $key => $value)
                {
                    if(!empty($value['baby_pic']))
                        $result[$key]['baby_pic'] = $this->di['sysconfig']['babyPicServer'] . $value['baby_pic'];
                    else
                    {
                        $result[$key]['baby_id'] = '';
                        $result[$key]['baby_pic'] = '';
                    }

                    if($_SERVER['REQUEST_TIME'] <= Common::expires($value['expdate']))
                    {
                        $result[$key]['target'] = '1';
                    }
                    else
                    {
                        $result[$key]['target'] = '3';
                    }
                }
            }
        }
        elseif($code == '1')
        {
            $result = $this->devices->shoeListUnbind($this->userInfo['uid'] );
            //获取截止续费的时间戳:判断设备是否可以续费 target为1可续，为3不可续
            foreach ($result as $key => $val)
            {
                if($_SERVER['REQUEST_TIME'] <= Common::expires($val['expdate']))
                {
                    $result[$key]['target'] = '1';
                }
                else
                {
                    $result[$key]['target'] = '3';
                }
            }
        }
        else
            $this->_showMsg(self::INVALID_OPERATE, $this->di['flagmsg'][self::INVALID_OPERATE]);

        $this->_returnResult(array('flag' => self::SUCCESS, 'shoelist' => $result));
    }

    /**
     * 童鞋绑定
     * 
     */
    public function bindAction()
    {
        $babyCount = $this->devices->babyCount($this->userInfo['uid'], $this->_sanReq['shoe_id']);
        if($babyCount > 0)
            $this->_showMsg($babyCount, $this->di['flagmsg'][$babyCount]);
        else
        {
            //判断是否有操作权限
            $this->_oauthrity($this->userInfo['uid'], $this->_sanReq['baby_id']);

            if($res = $this->devices->bindBabyShoe($this->_sanReq['baby_id'], $this->_sanReq['shoe_id']) == self::SUCCESS)
                $this->_showMsg($res);
            else
                $this->_showMsg($res, $this->di['flagmsg'][$res]);
        }
    }

    /**
     * 返回某童鞋的工作模式
     */
    public function getmodeAction()
    {
        $mode = $this->devices->getShoeMode($this->userInfo['uid'], $this->_sanReq['shoe_id']);
        if(!$mode)
        {
            $this->_showMsg(self::NOT_USER_DEV, $this->di['flagmsg'][self::NOT_USER_DEV]);
        }
        $this->_returnResult(array('flag' => self::SUCCESS, 'mode' => $mode['dev_work_mode']));
    }

    /**
     * 关机
     */
    public function offAction()
    {
        $row = $this->devices->getUidByDev($this->_sanReq['shoe_id']);
        if(!$row)
            $this->_showMsg(self::NON_EXIST_SHOE, $this->di['flagmsg'][self::NON_EXIST_SHOE]);
        //如果操作的鞋子不属于自己，则不让操作
        if($row['u_id'] != $this->userInfo['uid'])
            $this->_showMsg(self::NOT_USER_DEV, $this->di['flagmsg'][self::NOT_USER_DEV]);

        //设置数据库的dev_status为3，离线
        try{
            //通过thrift服务通知关机
            $rpcObj = new \Appserver\Utils\RpcService($this->di['sysconfig']['thriftConf']['ip'], $this->di['sysconfig']['thriftConf']['port']);
            $rpcObj->setDevHalt($this->_sanReq['shoe_id'], $row['dev_mobi']);

            if($this->devices->devOff($this->_sanReq['shoe_id']))
                $this->_showMsg(self::SUCCESS);
            else
                $this->_showMsg(self::FAILED_OFF, $this->di['flagmsg'][self::FAILED_OFF]);
        }
        catch(\Exception $e)
        {
            $this->_showMsg(self::FAILED_OFF, $this->di['flagmsg'][self::FAILED_OFF]);
        }
    }

    /**
     * 童鞋工作模式
     *如果宝贝id有值，则修改该宝贝所有童鞋的工作模式；如果shoeid有值，则改变对应的童鞋的工作模式；二者不能同时有值
     *备注：目前根据宝贝id修改工作模式的功能暂时屏蔽
     *mode:修改的工作模式: 1-省电 3-安全 5-休眠
     *$dev是所有需要修改工作模式的童鞋id的集合
     */
    public function modeAction()
    {
        //目前只开放针对宝贝的工作模式切换，不提供关于童鞋的
        if(!isset($this->_sanReq['baby_id']) && isset($this->_sanReq['shoe_id']))
        {
            $babyId = $this->devices->getBabyIdByShoeId($this->userInfo['uid'], $this->_sanReq['shoe_id']);;

            if(empty($babyId) || $babyId['baby_id'] == '0')
                $this->_showMsg(self::NON_EXIST_SHOE, $this->di['flagmsg'][self::NON_EXIST_SHOE]);

            $this->_oauthrity($this->userInfo['uid'], $babyId['baby_id']);
            $this->devices->updateShoeMode($this->_sanReq['shoe_id'], $this->_sanReq['mode']);
            $shoeMode = new \Appserver\Utils\RpcService($this->di['sysconfig']['thriftConf']['ip'], $this->di['sysconfig']['thriftConf']['port']);
            $shoeMode->setDevMod($this->_sanReq['shoe_id'], $this->userInfo['mobi'], $this->_sanReq['mode']);
            $this->_showMsg(self::SUCCESS);
        }
        else
            $this->_showMsg(self::INVALID_OPERATE, $this->di['flagmsg'][self::INVALID_OPERATE]);
    }

    /**
     * 鞋子解绑
     * 检查用户是否有权限进行操作
     */
    public function unbindAction()
    {
        $res = $this->devices->unbindBabyShoe($this->userInfo['uid'], $this->_sanReq['shoe_id']);
        if(is_array($res))
            $this->_returnResult($res);
        else
            $this->_showMsg($res, $this->di['flagmsg'][$res]);
    }

    /**
     * 删除童鞋
     */
    public function delAction()
    {
        $res = $this->devices->getUidByDev($this->_sanReq['shoe_id']);
        if(empty($res))
            $this->_showMsg(self::NON_EXIST_SHOE, $this->di['flagmsg'][self::NON_EXIST_SHOE]);

        if($this->userInfo['uid'] == $res['u_id'])
        {
            if(($ret = $this->devices->deleteShoe($this->_sanReq['shoe_id'])) == self::SUCCESS)
                $this->_showMsg($ret);
            else
                $this->_showMsg($ret, $this->di['flagmsg'][$ret]);
        }
        else
            $this->_showMsg(self::NOT_USER_DEV, $this->di['flagmsg'][self::NOT_USER_DEV]);
    }
}
