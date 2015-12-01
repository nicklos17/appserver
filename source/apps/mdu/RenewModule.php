<?php

namespace Appserver\Mdu\Modules;

use Appserver\Utils\Common,
       Appserver\Utils\SwooleUserClient;
class RenewModule extends ModuleBase
{
    const SUCCESS = '1';
    const FAILED_ADD = 11071;
    const FAILED_OPERATE = 11111;

    private $renew;
    private $devices;
    private $msg;

    public function __construct()
    {
        $this->devices = $this->initModel('\Appserver\Mdu\Models\DevicesModel');
        $this->renew = $this->initModel('\Appserver\Mdu\Models\RenewModel');
        $this->msg = $this->initModel('\Appserver\Mdu\Models\MsgModel');
    }

    public function getRenewList($shoeId, $shoeInfo)
    {
        //获取所有可续费的套餐
        $renewList = $this->renew->showRenewList('1');
        if(!empty($renewList))
        {
            $nowtime = $_SERVER['REQUEST_TIME'];

            foreach($renewList as $k => $v)
            {
                $renewList[$k]['renew_pic'] = $this->di['sysconfig']['renewServer'] . sprintf($this->di['sysconfig']['renewPic'], $v['cr_period']);
                //如果续费的时候设备尚未到期，则延长到期时间
                if($shoeInfo['dev_expires'] - $nowtime > 0)
                {
                    $renewList[$k]['expires'] = Common::expires($shoeInfo['dev_expires'], $renewList[$k]['cr_period']);
                }
                else
                    //如果已经到期，则从现在开始续费
                {
                    $renewList[$k]['expires'] = Common::expires($nowtime, $renewList[$k]['cr_period']);
                }
                
                //折扣图片显示
                if($v['cr_id'] == 2)
                    $renewList[$k]['discount_pic'] = $this->di['sysconfig']['renewServer'] . $this->di['sysconfig']['renewDiscountPic']['8.9'];
                else
                    $renewList[$k]['discount_pic'] = '';
            }
        }

        return array(
            'flag' => '1',
            'imei' => (string)$shoeInfo['dev_imei'],
            'deadline' => (string)$shoeInfo['dev_expires'],
            'shoe_pic' => $this->di['sysconfig']['renewServer'] . $shoeInfo['dev_pic'],
            'servicelist' => $renewList);
    }

    /**
     * 获取符合条件的套餐
     */
    public function getRenew($crId, $crStatus)
    {
        return $this->renew->getRenewByCrid($crId, $crStatus);
    }

    /**
     * 生成续费订单，入库
     */
    public function renewUpdate ($devId, $uid, $crId, $addTime, $roStatus, $roNo, $roPayment, $roPrice,
    $roSubject, $roPeriod, $roCoins, $roRolename, $babyId, $devImei)
    {
        $this->di['db']->begin();
        $orderId = $this->renew->addOrder($devId, $uid, $crId, $addTime, $roStatus, $roNo, $roPayment,
        $roPrice, $roSubject, $roPeriod, $roCoins, $roRolename, $babyId, $devImei);
        if(!orderId)
        {
            $this->di['db']->rollback();
            return self::FAILED_ADD;
        }
        if(!$this->renew->addRenewLog($orderId, 1, $addTime))
        {
            $this->di['db']->rollback();
            return self::FAILED_ADD;
        }
        $this->di['db']->commit();
        return self::SUCCESS;
    }

    public function renewOperate($outTradeNo, $addTime, $tradeNo)
    {
       $renewInfo = $this->renew->getRenewByRono($outTradeNo);
       if(!$renewInfo)
            return self::FAILED_OPERATE;
        //获取设备原本到期时间和imei
        $shoeInfo = $this->devices->getBabyDevByShoeId($renewInfo['dev_id']);
       if(!$shoeInfo)
            return self::FAILED_OPERATE;
        //如果续费的时候设备尚未到期，则延长到期时间
        if($shoeInfo['dev_expires'] - $addTime > 0)
            $expires = Common::expires($shoeInfo['dev_expires'], $renewInfo['ro_period']);
        else //如果已经到期，则从现在开始续费
            $expires = Common::expires($addTime, $renewInfo['ro_period']);

        //现在的日期
        $today = getdate($addTime);
        //到期日
        $deaddate = getdate($expires);
        $content = sprintf($this->di['sysconfig']['renewPushMsg']['success'], $renewInfo['ro_rolename'],
        $today['year'].'年'. $today['mon'].'月'.$today['mday'].'日', $renewInfo['dev_imei'],
        $deaddate['year'].'年'. $deaddate['mon'].'月'.$deaddate['mday'].'日');

        $this->di['db']->begin();
        if(!$this->devices->updateExpires($shoeInfo['dev_uuid'], $expires))
        {
            $this->di['db']->rollback();
            return self::FAILED_OPERATE;
        }

        if(!$this->devices->setExpiresByDevid($renewInfo['dev_id'], $expires))
        {
            $this->di['db']->rollback();
            return self::FAILED_OPERATE;
        }

        if(!$this->renew->addRenewLog($renewInfo['ro_id'], 3, $addTime))
        {
            $this->di['db']->rollback();
            return self::FAILED_OPERATE;
        }

        if(!$this->msg->insertMsg($renewInfo['baby_id'], $addTime, $content, 17))
        {
            $this->di['db']->rollback();
            return self::FAILED_OPERATE;
        }

        if(!$this->renew->setOrderStatus($outTradeNo, 3, $tradeNo))
        {
            $this->di['db']->rollback();
            return self::FAILED_OPERATE;
        }

        $this->di['db']->commit();

        //赠送云币
        $swoole = new SwooleUserClient(
            $this->di['sysconfig']['swooleConfig']['ip'],
            $this->di['sysconfig']['swooleConfig']['port']
        );
        $res = $swoole->checkInReceive($renewInfo['u_id'], $renewInfo['ro_coins']);

        return self::SUCCESS;
    }

    public function wechatFailedPay($outTradeNo, $addTime, $transactionId)
    {
       $renewInfo = $this->renew->getRenewByRono($outTradeNo);
       if($renewInfo)
        {
            $this->di['db']->begin();
            if(!$this->renew->addRenewLog($renewInfo['ro_id'], 7, $addTime))
                $this->di['db']->rollback();

            if(!$this->renew->setOrderStatus($outTradeNo, 7, $transactionId))
                $this->di['db']->rollback();

            $this->di['db']->commit();
        }
    }
}