<?php

namespace Appserver\Mdu\Models;

class RenewModel extends ModelBase
{

    /**
     * 获取套餐列表
     */
    public function showRenewList($status)
    {
        $query = $this->db->query('SELECT cr_id, cr_name as name, cr_price as price, cr_period, cr_coins as coins ' .
            'FROM cloud_renew WHERE cr_status = ?',
            array($status)
            );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 获取符合条件的套餐
     * @param unknown $where
     */
    public function getRenewByCrid($crId, $crStatus)
    {
        $query = $this->db->query(' SELECT `cr_id`, `cr_name`, `cr_price`, `cr_detail`, `cr_period` ' .
        ', `cr_real_price`, `cr_coins` FROM `cloud_renew` WHERE `cr_id` = ? AND `cr_status` = ? LIMIT 1', 
            array(
                $crId,
                $crStatus
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 创建新订单
     * @param unknown $data
     */
    public function addOrder($devId, $uid, $crId, $addTime, $roStatus, $roNo, $roPayment,
    $roPrice, $roSubject, $roPeriod, $roCoins, $roRolename, $babyId, $devImei)
    {
        if($this->db->execute('INSERT INTO `cloud_baby_ranks` (`dev_id`, `u_id`, `cr_id`, `ro_addtime`, ' .
        '`ro_status`, `ro_no`, `ro_payment`, `ro_price`, `ro_subject`, `ro_period`, `ro_coins`, ' . 
        '`ro_rolename`, `baby_id`, `dev_imei`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            array(
                $devId,
                $uid,
                $crId,
                $addTime,
                $roStatus,
                $roNo,
                $roPayment,
                $roPrice,
                $roSubject,
                $roPeriod,
                $roCoins,
                $roRolename,
                $babyId,
                $devImei
            )
        ))
            return $this->db->lastInsertId();
        else
            return false;
    }

    /**
     * 获取符合条件的套餐
     * @param unknown $where
     */
    public function getRenewByRono($Rono)
    {
        $query = $this->db->query(' SELECT `cr_id`, `cr_name`, `cr_price`, `cr_detail`, `cr_period` ' .
        ', `cr_real_price`, `cr_coins` FROM `cloud_renew` WHERE `ro_no` = ? LIMIT 1', 
            array(
                $Rono
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 增加续费日志
     * @param unknown $data
     */
    public function addRenewLog($roId, $roStatus, $rlStatustime)
    {
        return $this->db->execute('INSERT INTO `cloud_renew_logs` SET `ro_id` = ?, `ro_status` = ? , ' .
        '`rl_statustime` = ? ',
            array(
                $roId,
                $roStatus,
                $rlStatustime
            )
        );
    }

    /**
     * 更新订单状态cloud_renew_order
     */
    public function setOrderStatus($outTradeNo, $status, $tradeNo)
    {
        $this->db->execute('UPDATE `cloud_renew_order` SET `ro_status` = ?, `ro_third_no` ' .
        '= ? WHERE `ro_no` = ?',
            array(
                $status,
                $tradeNo,
                $outTradeNo,
            )
        );
        return $this->db->affectedRows();
    }
}