<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\Common;

class DevicesModule extends ModuleBase
{
    const EMPTY_GET = 0;
    const SUCCESS = '1';
    const FAILED_ADD_DEV = 10015;
    const NOT_USER_DEV = 10028;
    const FAILED_DEL_DEV = 10029;
    const NOT_BIND_BABY = 10030;
    const ADDED_BY_OTHER = 10031;
    const SHOE_ADDED = 10032;
    const NON_EXIST_SHOE = 10034;
    const FAILED_UNBIND = 10036;
    const SHOE_BINDED = 10037;
    const FAILED_CHANGE_MODE = 10039;
    const VALIDED_SHOE = 11077;
    const FAILED_UPDATE_DATA = 33333;
    const NO_OAUTHRITY = 99999;

    public $devices;
    public $baby;
    public $babyRanks;

    public function __construct()
    {
        $this->baby = $this->initModel('\Appserver\Mdu\Models\BabyModel');
        $this->babyRanks = $this->initModel('\Appserver\Mdu\Models\BabyRanksModel');
        $this->devices = $this->initModel('\Appserver\Mdu\Models\DevicesModel');
    }

    public function shoeListByUid($uid, $count)
    {
        return $this->devices->getShoeListByUid($uid, $count);
    }

    public function shoeListByBabyId($babyId)
    {
        return $this->devices->getShoeListByBabyId($babyId);
    }

    public function shoeListUnbind($uid)
    {
        return $this->devices->getUnBindShoesByUid($uid);
    }

    /**
     * 判断一双鞋子是否已绑定宝贝
     * @param str $shoeId
     */
    public function babyCount($uid, $shoeId)
    {
        $babyId = $this->devices->getBabyIdByShoeId($uid, $shoeId);
        if(!empty($babyId) || $babyId['baby_id'] == '0')
            return self::EMPTY_GET;
        elseif(!empty($babyId) || $babyId['baby_id'] != '0')
            return self::SHOE_BINDED;
        else
            return self::NOT_USER_DEV;
    }

    public function getShoeMode($uid, $shoeId)
    {
        return $this->devices->getShoeModeByShoeId($uid, $shoeId);
    }

    /**
     * [绑定童鞋]
     * @param  [type] $babyId [description]
     * @param  [type] $shoeId [description]
     * @return [type]         [description]
     */
    public function bindBabyShoe($babyId, $shoeId)
    {
        $this->di['db']->begin();
        if(!$this->devices->setShoeBindBabyId($babyId, $shoeId))
        {
            $this->di['db']->rollback();
            return self::FAILED_UPDATE_DATA;
        }
        if(!$this->baby->setDevNum($babyId))
        {
            $this->di['db']->rollback();
            return self::FAILED_UPDATE_DATA;
        }
        if(!$this->babyRanks->getBabyId($babyId))
        {
            if(!$this->babyRanks->addBabyId($babyId))
            {
                $this->di['db']->rollback();
                return self::FAILED_UPDATE_DATA;
            }
        }

            $this->di['db']->commit();
            return self::SUCCESS;
    }

    /**
     * [童鞋解绑]
     * @param  [type] $babyId [description]
     * @param  [type] $shoeId [description]
     * @return [type]         [description]
     */
    public function unbindBabyShoe($uid, $shoeId)
    {
        $babyId = $this->devices->getBabyIdByShoeId($uid, $shoeId);
        if(empty($babyId))
            return self::NON_EXIST_SHOE;

        if($babyId['baby_id'] == '0')
            return self::NOT_BIND_BABY;

        $this->di['db']->begin();
        if(!$this->devices->setShoeUnbindBabyId($babyId['baby_id'], $shoeId))
        {
            $this->di['db']->rollback();
            return self::FAILED_UNBIND;
        }
        if(!$this->baby->SetDevUnbind($babyId['baby_id']))
        {
            $this->di['db']->rollback();
            return self::FAILED_UNBIND;
        }

        $this->di['db']->commit();

        $devs = $this->baby->getBabyDevs($babyId['baby_id']);
        if(empty($devs))
            return self::FAILED_UPDATE_DATA;

       return array('flag' => '1', 'baby_id' => $babyId['baby_id'], 'devs' => $devs['baby_devs']);

    }

    /**
     * 根据shoeid找到对应的用户id
     * @param int $shoeId 童鞋id
     */
    public function getUidByDev($shoeId)
    {
        return $this->devices->getInfoByShoeId($shoeId);
    }

    /**
     * 关机
     * @param str $shoeId
     */
    public function devOff($shoeId)
    {
        return $this->devices->setDevStatusByShoeId($shoeId);
    }

    /**
     * 根据shoeid找到对应的babyid
     * @param int $shoeId
     */
    public function getBabyIdByShoeId($uid, $shoeId)
    {
        return $this->devices->getBabyIdByShoeId($uid, $shoeId);
    }

    public function updateShoeMode($shoeId, $type)
    {
        if($this->devices->setModeByShoeId($shoeId, $type))
            return self::SUCCESS;
        else
            return self::FAILED_CHANGE_MODE;
    }

    /**
     * [删除童鞋，只有没绑定宝贝的设备才能删除]
     * @param  [type] $shoeId [description]
     * @return [type]         [description]
     */
    public function deleteShoe($shoeId)
    {
        if($this->devices->deleteShoe($shoeId))
            return self::SUCCESS;
        else
            return self::FAILED_DEL_DEV;
    }

    /**
     * [强行删除童鞋，并把宝贝最近的电量置0]
     * @param  [type] $shoeId [description]
     * @return [type]         [description]
     */
    public function removeShoe($shoeId, $uid)
    {
        $shoeInfo = $this->devices->getBabyDevByShoeId($shoeId);
        if(!$this->_oauthrity($uid, $shoeInfo['baby_id']))
            return self::NO_OAUTHRITY;

        $this->di['db']->begin();
        //添加鞋子
        if($this->devices->removeShoe($shoeId))
        {
            if($this->baby->setBattery($shoeInfo['baby_id']))
            {
                $this->di['db']->commit();
                return self::SUCCESS;
            }
            else
            {
                $this->di['db']->rollback();
                return self::FAILED_DEL_DEV;
            }
        }
        else
        {
            $this->di['db']->rollback();
            return self::FAILED_DEL_DEV;
        }

    }

    /**
     *检查鞋子是否已被添加到表中
     */
    public function checkDevByQr($qr)
    {
        if($this->devices->getShoeIdByQr($qr))
            return self::SHOE_ADDED;
        else
            return self::EMPTY_GET;
    }

    /**
     *从设备库存表查询出对应的数据
     *$qr 设备的qr号
     */
    public function getDevInfo($qr)
    {
        return $this->devices->getDevInfoByQr($qr);
    }

    public function getDevExpireByqr($qr)
    {
        return $this->devices->getDevIdByqr($qr);
    }

    /**
     * [添加童鞋]
     * @param [string] $shoeQr     [童鞋qr码]
     */
    public function addShoe($uid, $shoeQr)
    {
        //判断鞋子是否存在
        $shoeInfo = $this->devices->getDevInfoByQr($shoeQr);
        if(!$shoeInfo)
            $this->_showMsg(self::NON_EXIST_SHOE, $this->di['flagmsg'][self::NON_EXIST_SHOE]);

        //判断鞋子是否已经添加
        $checkQr = $this->devices->getShoeIdByQr($shoeQr);
        if(!empty($checkQr))
        {
            if($checkQr['u_id'] == $uid)
                return self::SHOE_ADDED;
            else
                return self::ADDED_BY_OTHER;
        }

        //开始计算服务期,如果值为0，则第一次添加
        if($shoeInfo['expire'] == 0)
            $expires = Common::expires($_SERVER['REQUEST_TIME']);
        else
            $expires = $shoeInfo['expire'];

        $this->di['db']->begin();
        //添加鞋子
        if($this->devices->addShoe($uid, $shoeInfo['uuid'], $shoeInfo['imei'],
        $shoeInfo['mobi'], $shoeInfo['pass'], $shoeInfo['dver'], $expires, $shoeInfo['qr'],
        $shoeInfo['pic'], $_SERVER['REQUEST_TIME']))
        {
            if($this->devices->updateExpires($shoeInfo['uuid'], $expires))
            {
                $this->di['db']->commit();
                return self::SUCCESS;
            }
            else
            {
                $this->di['db']->rollback();
                return self::FAILED_ADD;
            }
        }
        else
        {
            $this->di['db']->rollback();
            return self::FAILED_ADD;
        }
    }

    /**
     * [扫描直接添加童鞋并绑定]
     * @param [string] $shoeQr     [童鞋qr码]
     */
    public function addAndBind($uid, $shoeQr, $babyId)
    {
        //判断鞋子是否存在
        $shoeInfo = $this->devices->getDevInfoByQr($shoeQr);
        if(!$shoeInfo)
            $this->_showMsg(self::NON_EXIST_SHOE, $this->di['flagmsg'][self::NON_EXIST_SHOE]);

        //开始计算服务期,如果值为0，则第一次添加
        if($shoeInfo['expire'] == 0)
            $expires = Common::expires($_SERVER['REQUEST_TIME']);
        else
            $expires = $shoeInfo['expire'];

        $this->di['db']->begin();
        //判断鞋子是否已经添加
        $checkQr = $this->devices->getShoeIdByQr($shoeQr);
        if(!empty($checkQr))
        {
            if($checkQr['baby_id'] != '0')
                return self::SHOE_ADDED;
            else
            {
                //如果鞋子已添加但是还未绑定童鞋，执行绑定
                if(!$this->devices->setShoeBindBabyId($babyId, $checkQr['dev_id']))
                {
                    $this->di['db']->rollback();
                    return self::FAILED_ADD;
                }
                if(!$this->devices->updateExpires($shoeInfo['uuid'], $expires))
                {
                    $this->di['db']->rollback();
                    return self::FAILED_ADD;
                }
            }
        }
        else
        {
            if($_SERVER['REQUEST_TIME'] < $expires)
            {
                //添加鞋子
                if(!$this->devices->addShoe($uid, $shoeInfo['uuid'], $shoeInfo['imei'],
                    $shoeInfo['mobi'], $shoeInfo['pass'], $shoeInfo['dver'], $expires, $shoeInfo['qr'],
                    $shoeInfo['pic'], $_SERVER['REQUEST_TIME'], $babyId))
                {
                     $this->di['db']->rollback();
                     return self::FAILED_ADD;
                }
                if(!$this->devices->updateExpires($shoeInfo['uuid'], $expires))
                {
                    $this->di['db']->rollback();
                    return self::FAILED_ADD;
                }
            }
            else
            {
                return self::VALIDED_SHOE;
            }
        }
        if(!$this->baby->setDevNum($babyId))
        {
            $this->di['db']->rollback();
            return self::FAILED_UPDATE_DATA;
        }

        //往排行榜里面插入宝贝记录
        if(!$this->babyRanks->getBabyId($babyId))
        {
            if(!$this->babyRanks->addBabyId($babyId))
            {
                $this->di['db']->rollback();
                return self::FAILED_UPDATE_DATA;
            }
        }
        $this->di['db']->commit();
        return self::SUCCESS;
    }

    /**
     * 获取所有已绑定童鞋的宝贝id
     */
    public function babyBinded()
    {
        return $this->devices->getBabysBinded();
    }

    /**
     * [判断鞋子是否已添加]
     * @return [type] [description]
     */
    public function getShoeIdByQr($qr)
    {
        $checkQr = $this->devices->getShoeIdByQr($qr);
        if(!empty($checkQr))
            return self::SHOE_ADDED;
        else
            return self::SUCCESS;
    }
}
