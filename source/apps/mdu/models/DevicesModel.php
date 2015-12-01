<?php

namespace Appserver\Mdu\Models;

class DevicesModel extends ModelBase
{
    /**
     * 返回某宝贝过期童鞋的信息
     * @param unknown $babyId
     * @param unknown $nowtime
     */
    public function getExpireDevsByBabyId($babyId, $nowtime)
    {
        $query = $this->db->query('SELECT `dev_id`, `dev_expires` FROM `cloud_devices` WHERE ' .
        ' baby_id = ? AND dev_expires < ?',
            array(
                $babyId,
                $nowtime
            )
        );

        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 根据宝贝id找到对应的童鞋id
     * @param int $babyId 宝贝id
     * @return array
     */
    public function getShoeIdByBabyId($babyId)
    {
        $query = $this->db->query('SELECT `dev_id` FROM `cloud_devices` WHERE baby_id = ? LIMIT 1',
            array(
                $babyId
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 根据宝贝id找到对应的所有童鞋id
     * @param int $babyId 宝贝id
     * @return array
     */
    public function getShoeIdsByBabyId($babyId)
    {
        $query = $this->db->query('SELECT `dev_id` FROM `cloud_devices` WHERE baby_id = ?',
            array(
                $babyId
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 获取该用户的童鞋列表
     * @param str $mobi
     */
    public function getShoeListByUid($uid, $count)
    {
        $query = $this->db->query('SELECT d.dev_id AS shoe_id, d.dev_imei AS shoe_imei, d.baby_id, b.baby_pic, ' .
        'd.dev_pic AS pic_url, d.dev_expires AS expdate, d.dev_status AS status, d.dev_battery_time ' .
        'AS activetime, d.dev_battery AS battery, d.dev_work_mode AS mode, b.baby_pic FROM ' .
        '`cloud_devices` d LEFT JOIN (SELECT baby_id, baby_pic FROM `cloud_babys`) b ON' .
        ' b.baby_id = d.baby_id WHERE d.u_id = ? ORDER BY d.baby_id DESC LIMIT ?',
            array(
                $uid,
                intval($count)
            ),
            array(
                \PDO::PARAM_INT,
                \PDO::PARAM_INT
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 返回已绑定宝贝的童鞋列表
     * @param str $mobi
     * @param str $babyId
     */
    public function getShoeListByBabyId($babyId)
    {
        $query = $this->db->query('SELECT b.dev_id AS shoe_id, b.baby_id, b.dev_imei AS shoe_imei, ' .
        'b.dev_pic AS pic_url, b.dev_expires AS expdate, b.dev_status AS status, b.dev_battery_time ' .
        'AS activetime, b.dev_battery AS battery, b.dev_work_mode AS mode, bb.baby_pic FROM `cloud_devices` ' .
        'b LEFT JOIN (SELECT baby_id, baby_pic FROM `cloud_babys`) bb ON b.baby_id = bb.baby_id ' .
        'WHERE b.baby_id = ?',
            array(
                $babyId
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 返回未绑定宝贝的童鞋列表
     * @param str $mobi
     */
    Public function getUnBindShoesByUid($uid)
    {
        $query = $this->db->query('SELECT `dev_id` AS shoe_id, `dev_imei` AS shoe_imei, `dev_pic` ' .
        ' AS pic_url, `dev_expires` AS expdate, `dev_status` AS status ,`dev_work_mode` AS mode, ' .
        '`dev_battery` AS battery FROM `cloud_devices` WHERE `u_id` = ? AND baby_id = 0',
            array(
                $uid
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 通过鞋子id获取宝贝id
     * @param str $shoeId
     */
    public function getBabyIdByShoeId($uid, $shoeId)
    {
        $query =  $this->db->query('SELECT `baby_id` from `cloud_devices` WHERE '.
        '`u_id` = ? AND `dev_id` = ?',
            array(
                $uid,
                $shoeId
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 获取童鞋工作模式
     * @param str $uid 用户id
     * @param str $shoeId 童鞋id
     */
    public function getShoeModeByShoeId($uid, $shoeId)
    {
        return $this->db->query('SELECT `dev_work_mode` FROM `cloud_devices` WHERE `u_id` ' .
        ' = ? AND `dev_id` = ?',
            array(
                $uid,
                $shoeId
            )
        )->fetch();
    }

    /**
     * 童鞋绑定
     * @param str $babyId
     * @param str $shoeId
     */
    public function setShoeBindBabyId($babyId, $shoeId)
    {
        $this->db->execute('UPDATE `cloud_devices` SET `baby_id` = ? WHERE `dev_id` = ?',
            array(
                $babyId,
                $shoeId
            )
        );
        return $this->db->affectedRows();
    }

    /**
     * 根据shoeid找到对应的信息
     * @param int $shoeId 童鞋id
     */
    public function getInfoByShoeId($shoeId)
    {
        $query = $this->db->query('SELECT `u_id`, `dev_mobi`, `dev_status`, `baby_id`, `dev_expires`, `dev_pic`, `dev_imei` FROM `cloud_devices` ' .
        ' WHERE `dev_id` = ? limit 1',
            array(
                $shoeId
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 关机
     * @param str $shoeId
     */
    public function setDevStatusByShoeId($shoeId)
    {
        return $this->db->execute('UPDATE `cloud_devices` set `dev_status` = 3 WHERE `dev_id` = ?',
            array(
                $shoeId
            )
        );
    }

    /**
     * 根据shoeid找到对应的babyid
     * @param int $shoeId
     */
    public function getBabyDevByShoeId($shoeId)
    {
        $query = $this->db->query('SELECT `baby_id`, `dev_expires`, `dev_uuid`, `dev_imei`, ' .
        '`dev_pic` FROM `cloud_devices` WHERE `dev_id` = ?',
            array(
                $shoeId
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 修改童鞋的工作模式
     * @param int $shoeId 童鞋id
     * @param int $type 工作模式类型：1-省电 3-安全 5-休眠 
     */
    public function setModeByShoeId($shoeId, $type)
    {
        $this->db->execute('UPDATE `cloud_devices` SET `dev_work_mode` = ? WHERE `dev_id` ' .
        ' = ?',
            array(
                $type,
                $shoeId
            )
        );
        return $this->db->affectedRows();
    }

    /**
     * 童鞋解绑
     * @param str $shoeId
     */
    public function setShoeUnbindBabyId($babyId, $shoeId)
    {
        $this->db->execute('UPDATE `cloud_devices` SET `baby_id` = 0 WHERE `dev_id` = ? ' .
        'AND `baby_id` = ?',
            array(
                $shoeId,
                $babyId
            )
        );
        return $this->db->affectedRows();
    }

    /**
     * 强行删除童鞋
     * @param string $shoeId
     */
    public function removeShoe($shoeId)
    {
        return $this->db->execute('DELETE FROM `cloud_devices` WHERE `dev_id` = ?',
            array(
                $shoeId
            )
        );
    }

    /**
     * 删除童鞋
     * @param int $shoeId
     * 注意：baby_id必须为空，即没有绑定宝贝的童鞋才可以被删除
     */
    public function deleteShoe($shoeId)
    {
        return $this->db->execute('DELETE FROM `cloud_devices` WHERE `dev_id` = ? AND `baby_id` = 0',
            array(
                $shoeId
            )
        );
    }

    /**
     *检查鞋子是否已被添加到表中 
     */
    public function getShoeIdByQr($qr)
    {
        $query = $this->db->query('SELECT `dev_id`, `u_id`, `baby_id` FROM `cloud_devices` WHERE `dev_qr` = ? LIMIT 1',
            array(
                $qr
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 从设备库存表查询出对应的数据
     * $qr 设备的qr号
     */
    public function getDevInfoByQr($qr)
    {
        $query = $this->db->query('SELECT `devstock_uuid` as uuid, `devstock_imei` as imei, ' .
        '`devstock_mobi` as mobi, `devstock_pic` as pic, `devstock_pass` as pass, `devstock_ver` ' .
        ' as dver, `devstock_expires` as expire, `devstock_qr` as qr FROM `cloud_dev_stock` ' .
        ' WHERE `devstock_qr` = ? LIMIT 1',
            array($qr)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 从设备库存表更新童鞋的服务期 cloud_dev_stock
     * @param unknown $uuid
     * @param unknown $expires
     */
    public function updateExpires($uuid, $expires)
    {
        return $this->db->execute('UPDATE `cloud_dev_stock` SET `devstock_expires` = ? WHERE ' .
        '`devstock_uuid` = ?',
            array(
                $expires,
                $uuid
            )
        );
    }

    /**
     * 从设备库存表更新童鞋的服务期cloud_devices
     * @param unknown $uuid
     * @param unknown $expires
     */
    public function setExpiresByDevid($devid, $expires)
    {
        $this->db->execute('UPDATE `cloud_devices` SET `dev_expires` = ? WHERE ' .
        '`dev_id` = ?',
            array(
                $expires,
                $devid
            )
        );
        return $this->db->affectedRows();
    }

    /**
     * 根据qr码获取设备信息
     * @param unknown $where
     */
    public function getDevIdByqr($qr)
    {
        $query = $this->db->query('SELECT `dev_id`, `dev_expires` FROM `cloud_devices` ' .
        ' WHERE `dev_qr` = ? LIMIT 1',
            array(
                $qr
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 添加童鞋
     */
    public function addShoe($uid, $uuid, $imei, $mobi, $pass, $dver, $expire, $qr, $pic, $addtime, $babyId = '')
    {
        $this->db->execute('INSERT INTO `cloud_devices`(`u_id`, `dev_uuid`, `dev_imei`, ' .
        '`dev_mobi`, `dev_pass`, `dev_hard_ver`, `dev_expires`, `dev_qr`, `dev_pic`, `dev_actime`, `baby_id`) ' .
        'VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            array(
                $uid,
                $uuid,
                $imei,
                $mobi,
                $pass,
                $dver,
                $expire,
                $qr,
                $pic,
                $addtime,
                $babyId
            )
        );
        return $this->db->lastInsertId();
    }

    /**
     * 获取该用户是否的设备信息
     * @param unknown $uid
     */
    public function getDevByUid($uid)
    {
        $query = $this->db->query('SELECT dev_id FROM cloud_devices WHERE u_id = ? LIMIT 1',
            array($uid)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }


    /**
     * 获取所有已绑定童鞋的宝贝id
     */
    public function getBabysBinded()
    {
        $query = $this->db->query('SELECT GROUP_CONCAT(`baby_id`) as baby_ids from `cloud_devices` WHERE `baby_id` > 0');
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 获取宝贝最近穿着的设备
     * @param unknown $babyId
     */
    public function getNearlyDev($babyId)
    {
        $query = $this->db->query('SELECT dev_uuid as uuid FROM cloud_devices WHERE baby_id = ? ORDER BY dev_status_time DESC limit 1',
            array($babyId)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 获取指定宝贝和用户下的设备
     * @param unknown $bids
     * @param unknown $uid
     */
    public function getUUidByBidUid($bids, $uid)
    {
        $query = $this->db->query('SELECT dev_uuid FROM cloud_devices WHERE baby_id in(?) AND u_id = ?', array($bids, $uid));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 根据uuids数组获取设备信息
     * @param array $uuid
     */
    public function getDevInfoByUuids($uuids)
    {
        $query = $this->db->query('SELECT devstock_sn as sn FROM cloud_dev_stock WHERE devstock_uuid in('.$uuids. ')');
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }
}