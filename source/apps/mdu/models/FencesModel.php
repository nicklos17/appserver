<?php

namespace Appserver\Mdu\Models;

class FencesModel extends ModelBase
{
    /**
     * 添加围栏
     * @param int $babyId 宝贝id
     * @param str $coordinates 围栏中心点的经纬度
     * @param str $name 围栏名称
     * @param int $radius 围栏半径
     * @param str $addtime 添加时间
     * @return int
     */

    public function add($babyId, $coordinates, $name, $radius, $place, $validtime, $addTime)
    {
        return $this->db->execute('INSERT INTO `cloud_fences`(`baby_id`, `fence_coordinates`, ' .
        '`fence_name`, `fence_radius`, `fence_rangedate`, `fence_addr`, `fence_addtime`) ' .
        'VALUES(?, ?, ?, ?, ?, ?, ?)',
            array(
                $babyId,
                $coordinates,
                $name,
                $radius,
                $validtime,
                $place,
                $addTime
            )
        );
    }

    /**
     * 编辑
     * @param int $babyId 宝贝id
     * @param str $coordinates 围栏中心点的经纬度
     * @param str $name 围栏名称
     * @param int $radius 围栏半径
     * @param str $addtime 添加时间
     * @return int
     */

    public function edit($fenceId, $coordinates, $name, $radius, $place, $validtime, $addTime)
    {
        $this->db->execute('UPDATE `cloud_fences` SET `fence_coordinates` = ?, ' .
        '`fence_name` = ?, `fence_radius` = ?, `fence_rangedate` = ?, `fence_addr` = ? , `fence_addtime` ' .
        '=? WHERE `fence_id` = ?',
            array(
                $coordinates,
                $name,
                $radius,
                $validtime,
                $place,
                $addTime,
                $fenceId
            )
        );
        return $this->db->affectedRows();
    }

    /**
     * 删除围栏
     * @param int $fences_id 围栏id
     * @param str $delTime  删除时间
     */
    public function del($fencesId, $delTime)
    {
        $this->db->execute('UPDATE `cloud_fences` SET `fence_status` = 3, `fence_deltime` = ?' .
        ' WHERE `fence_id` = ?',
            array(
                $delTime,
                $fencesId
            )
        );
        return $this->db->affectedRows();
    }

    /**
     * 围栏列表显示
     * @param str $babyId
     */
    public function getFenList($babyId, $count)
    {
        $query = $this->db->query('SELECT `fence_id`, `fence_coordinates` as coordinates, ' .
        '`fence_name` as name, `fence_addr` as place, `fence_radius` as radius, `fence_checkin` ' .
        ' as checkin, `fence_rangedate` as validtime, `fence_checkin_time` as checktime FROM ' .
        '`cloud_fences` WHERE `fence_status` = 1 AND `baby_id` = ? ORDER BY `fence_checkin_time` ' .
        ' DESC LIMIT ?',
            array(
                $babyId,
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
     * 通过用户id获取宝贝签到次数
     * @param unknown $uid
     */
    public function getBabyCheckinByUid($uid)
    {
        $query = $this->db->query('select baby_id, fence_checkin from cloud_fences where baby_id in(select baby_id from cloud_family where u_id = ? AND family_status = 1 AND family_relation in(1,5)) ' .
                ' group by baby_id order by fence_checkin DESC limit 1', array($uid));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 获取用户下宝贝的签到次数
     * @param unknown $uid
     */
    public function getBabyCheckin($bids)
    {
        $query = $this->db->query('select baby_id, fence_checkin from cloud_fences where baby_id in('.$bids.') ' .
                ' group by baby_id');
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
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
