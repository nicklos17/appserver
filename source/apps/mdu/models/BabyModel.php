<?php

namespace Appserver\Mdu\Models;

class BabyModel extends ModelBase
{
    /**
     * 将宝贝信息添加到数据库
     * @param int $uid
     * @param str $name
     * @param int $sex
     * @param str $birthday
    * @param str $addtime
     * @param str $pic
     */
    public function add($name, $sex, $birthday, $addtime, $pic, $weight = '', $height = '', $dev = '0')
    {
        if($this->db->execute('INSERT INTO `cloud_babys` (`baby_nick`, `baby_sex`, `baby_birthday`, ' .
        '`baby_addtime`, `baby_pic`, `baby_weight`, `baby_height`, `baby_devs`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            array(
                $name,
                $sex,
                $birthday,
                $addtime,
                $pic,
                $weight,
                $height,
                $dev
            )
        ))
            return $this->db->lastInsertId();
        else
            return false;
    }

    /**
     * 编辑宝贝，修改宝贝信息
     * @param str $name
     * @param int $sex
     * @param str $birthday
     * @param int $babyId
     */
    public function edit($data)
    {
        $conditions = '';
        foreach ($data as $key => $val)
        {
            $conditions .= ",`$key`='$val'";
        }
        return $this->db->execute('UPDATE `cloud_babys` SET ' . ltrim($conditions, ',') . ' WHERE `baby_id` = ?',
            array(
                $data['baby_id']
            )
        );
    }

    /**
     * 获取绑定童鞋的宝贝列表
    * @param array $bids 宝贝id数组
     * @return array
     */
    public function getListByUidDev($uid, $count)
    {
        $query = $this->db->query('SELECT f.baby_id, f.family_relation AS relation, b.baby_nick '.
        'AS nick, b.baby_pic, b.baby_sex AS sex, b.baby_nearbattery AS battery, b.baby_birthday AS birthday,' .
        'b.baby_height AS height, b.baby_weight AS weight, b.baby_devs AS devs FROM `cloud_babys` b RIGHT JOIN (SELECT baby_id, u_id, ' .
        'family_relation, family_status from cloud_family) f ON f.baby_id = b.baby_id WHERE b.baby_devs ' .
        '> 0 AND f.u_id = ? AND f.family_status = 1 ORDER BY b.baby_devs DESC LIMIT ?',
            array(
                $uid,
                $count
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 获取全部宝贝信息列表
     * @param array $bids 宝贝id数组
     * @return array
     */
    public function getListByUid($uid, $count)
    {
        $query = $this->db->query('SELECT f.baby_id, b.baby_pic, b.baby_nick AS nick, f.family_relation AS relation, b.baby_sex ' .
        ' AS sex, b.baby_nearbattery AS battery,  baby_nearly AS nearly, b.baby_birthday, baby_nearlytime ' .
        'AS nearlytime, b.baby_height AS height, b.baby_weight AS weight, b.baby_birthday AS birthday, b.baby_devs AS devs FROM '.
        '`cloud_babys` b RIGHT JOIN (SELECT baby_id, u_id, family_relation, family_status from cloud_family) as f ON f.baby_id = b.baby_id WHERE f.u_id = ? AND f.family_status = 1 ORDER BY b.baby_devs DESC LIMIT ?',
            array(
                $uid,
                $count
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 完成童鞋绑定时，宝贝的装备数量+1
     * @param str $babyId
     * @return int
     */
    public function setDevNum($babyId)
    {
        $this->db->execute('UPDATE `cloud_babys` SET `baby_devs` = `baby_devs` +1 ' .
        'WHERE `baby_id` = ?',
            array(
                $babyId
            )
        );
        return $this->db->affectedRows();
    }
    /**
     * 完成童鞋解绑时，宝贝的装备数量-1
     * @param str $babyId
     * @return int
     */
    public function setDevUnbind($babyId)
    {
        $this->db->execute('UPDATE `cloud_babys` SET `baby_devs` = `baby_devs` -1 ' .
        'WHERE `baby_id` = ?',
            array(
                $babyId
            )
        );
        return $this->db->affectedRows();
    }

    /**
     * 根据宝贝id获取宝贝的昵称
     * @param int babyId
     */
    public function getBabyInfoById($babyId)
    {
        $query = $this->db->query('SELECT `baby_nick`, `baby_sex`, `baby_birthday`, `baby_pic`, ' .
        '`baby_nearly`, `baby_nearlytime`, `baby_devs`, `baby_nearbattery` FROM `cloud_babys` ' .
        'WHERE `baby_id` = ? LIMIT 1',
            array(
                $babyId
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 根据宝贝id增加宝贝的守护天数
     */
    public function setGuardsByBabyId($babyIds)
    {
        $this->db->execute('UPDATE `cloud_baby_ranks` SET `br_guards` = `br_guards` + 1 ' .
            'WHERE `baby_id` IN ('. $babyIds . ')'
        );
        return $this->db->affectedRows();
    }

    /**
     * 根据宝贝id获取宝贝的昵称
     * @param int babyId
     */
    public function getBabyName($babyId)
    {
        $query = $this->db->query('SELECT baby_nick, baby_sex, baby_birthday, baby_pic, baby_nearly, baby_nearlytime,
                                    baby_devs, baby_nearbattery FROM cloud_babys WHERE baby_id = ? ORDER BY baby_id limit 1', array($babyId));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 根据宝贝id获取宝贝的绑定的童鞋数量
     * @param int babyId
     */
    public function getBabyDevs($babyId)
    {
        $query = $this->db->query('SELECT baby_devs FROM cloud_babys WHERE baby_id = ? ORDER BY baby_id limit 1', array($babyId));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 根据多个babyid获取对应的宝贝头像
     * @param array $babyIds
     * @return array
     */
    public function getBabyPic($babyIds)
    {
        $query = $this->db->query('SELECT baby_id, baby_nick, baby_sex, baby_pic FROM cloud_babys WHERE baby_id in(' . $babyIds .') ORDER BY baby_id DESC');
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 根据宝贝id获取宝贝信息
     * @param array $bids 宝贝id数组
     * @return array
     */
    public function getListBybid($bids)
    {
        $query = $this->db->query('SELECT baby_id, baby_nick as nick, baby_pic, baby_sex as sex FROM cloud_babys WHERE baby_id in('. $bids .') ORDER BY baby_devs DESC');
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    public function setBattery($babyId)
    {
        return $this->db->execute('UPDATE `cloud_babys` SET `baby_nearbattery` = 0, `baby_devs` = `baby_devs` -1 WHERE `baby_id` = ?',
            array(
                $babyId
            )
        );
    }

    /**
     * [设置宝贝目标步数]
     * @param [type] $babyId [宝贝id]
     * @param [type] $steps  [目标步数]
     */
    public function setSteps($babyId, $steps)
    {
        return $this->db->execute('UPDATE `cloud_babys` SET `baby_steps_goal` = ? WHERE `baby_id` = ?',
            array(
                $steps,
                $babyId
            )
        );
    }

    /**
     * [获取宝贝目标步数]
     * @param  [type] $babyId [description]
     * @return [type]         [description]
     */
    public function getStepsGoal($babyId)
    {
        $query = $this->db->query('SELECT baby_steps_goal as goal FROM cloud_babys WHERE baby_id = ? limit 1',
            array($babyId));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 执行sql语句返回结果
     * @param unknown $执行sql语句返回结果
     */
    public function exec($sql)
    {
        $query = $this->db->query($sql);
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

}
