<?php

namespace Appserver\Mdu\Models;

class Locusinfomodel extends ModelBase
{

    /**
     * 定位
     * 当lasttime为空时，返回今天所有的轨迹
     * 当lasttime有值时，返回lasttime到现在为止所有的轨迹
     * @param int $uid 用户id
     * @param int $babyId           
     * @param string $lasttime          
     * @param str $endtime
     * @return array            
     */
    public function locate($babyId, $endtime)
    {
        $starttime= strtotime(date('Y-m-d')); // 当天零点
        $res = $this->db->query('SELECT li_id, li_coordinates as coordinates, li_start as timestamp,'.
        'li_title as place, li_battery as battery, li_start as start, li_end as end, li_accuracy as accur, li_type as type'.
        ' FROM cloud_locus_info WHERE baby_id = ? AND li_start >= ? AND li_start <= ?  ORDER BY li_id ASC',
            array($babyId, $starttime, $endtime)
            );
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $res->fetchAll();
    }
    
    /**
     * 定位
     * 返回starttime到当前时间所有点的轨迹
     * @param int $uid 用户id
     * @param int $babyId
     * @param string $starttime
     * @param str $endtime
     * @return array
     */
    public function locateForStarttime($babyId, $starttime, $endtime)
    {
        $res = $this->db->query('SELECT li_id, li_coordinates as coordinates, li_start as timestamp,'.
        ' li_title as place, li_battery as battery, li_start as start, li_end as end, li_accuracy as accur, li_type as type FROM '.
        'cloud_locus_info WHERE baby_id = ? AND li_start > ? AND li_start <= ?  ORDER BY li_id ASC',
            array($babyId, $starttime, $endtime)
        );
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $res->fetchAll();
    }

    /**
     * 定位,增加返回卡路里和步数
     * 当lasttime为空时，返回今天所有的轨迹
     * 当lasttime有值时，返回lasttime到现在为止所有的轨迹
     * @param int $uid 用户id
     * @param int $babyId           
     * @param string $lasttime          
     * @param str $endtime
     * @return array            
     */
    public function locateHaveSteps($babyId, $endtime)
    {
        $starttime= strtotime(date('Y-m-d')); // 当天零点
        $res = $this->db->query('SELECT li_id, li_coordinates as coordinates, li_start as timestamp,'.
        'li_title as place, li_start as start, li_end as end, li_calory as calory, li_runs as runs, li_steps as steps'.
        ' FROM cloud_locus_info WHERE baby_id = ? AND li_start >= ? AND li_start <= ? ORDER BY li_id ASC',
            array($babyId, $starttime, $endtime)
            );
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $res->fetchAll();
    }
    
    /**
     * 
     * 返回从指定id开始所有的定位信息
     * @param str $liid [起始id]
     * @return array
     */
    public function locateByStartId($babyId, $liid)
    {
        $res = $this->db->query('SELECT li_id, li_coordinates as coordinates, li_start as timestamp,'.
        ' li_title as place, li_battery as battery, li_start as start, li_end as end, li_calory as calory, li_runs as runs, li_steps as steps FROM '.
        'cloud_locus_info WHERE baby_id = ? AND li_id >= ? ORDER BY li_id ASC',
            array($babyId, $liid)
        );
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $res->fetchAll();
    }

    /**
     * 检查用户所有的宝贝是否有轨迹点
     * @param array $babys 宝贝id集合 
     */
    public function checkLocus($babys)
    {
        $res = $this->db->query('SELECT li_id FROM cloud_locus_info WHERE baby_id in(' . $babys . ')');
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $res->fetchAll();
    }
    
    /**
     * 根据宝贝id获取宝贝最近一次出现的点
     * @param unknown $babyId
     */
    public function nearlyCoor($babyId)
    {
        $res = $this->db->query('SELECT li_coordinates from cloud_locus_info where baby_id = ? LIMIT 1',
            array($babyId)
        );
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $res->fetch();
    }

    /**
     * 根据宝贝id获取当前宝贝设备的uuid
     * @param unknown $babyId
     */
    public function getUuidByBaby($babyId)
    {
        $query = $this->db->query('SELECT li_id, li_dev_uuid as uuid FROM cloud_locus_info WHERE baby_id = ? ORDER BY li_id DESC LIMIT 1',
            array($babyId));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 根据定位id，返回相应的点
     * @param unknown $babyId
     */
    public function getLocateInfoByLiid($liId)
    {
        $query = $this->db->query('SELECT baby_id, li_coordinates as coordinates, li_start as timestamp, li_title as place, '.
            'li_battery as battery, li_start as start, li_end as end, li_type as type, li_accuracy as accur '.
            'FROM cloud_locus_info WHERE li_id = ? ORDER BY li_id DESC LIMIT 1',
            array($liId)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 根据时间点宝贝id获取宝贝最近一次出现的点
     * @param unknown $babyId
     */
    public function nearlyCoorOfToday($babyId, $time)
    {
        $query = $this->db->query('SELECT li_coordinates as coordinates, li_start as timestamp, li_title as place, '.
            'li_battery as battery, li_start as start, li_end as end, li_type as type, li_accuracy as accur '.
            'FROM cloud_locus_info WHERE baby_id = ? AND li_addtime > ? ORDER BY li_id DESC LIMIT 1',
            array($babyId, $time)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * [根据轨迹id获取对应的详情图片]
     * @param  [type] $locusId [轨迹id集合]
     * @return [type]       [description]
     */
    public function getLocusPicByLocusId($locusId)
    {
        $query = $this->db->query('SELECT lip_id, li_id, lip_picture as pics FROM cloud_li_pictures'.
            ' WHERE locus_id = ? AND lip_status = 1',
            array($locusId)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * [根据轨迹id获取对应的详情图片]
     * @param  [type] $lids [轨迹详情id集合]
     * @return [type]       [description]
     */
    public function getLocusPicByBabyId($babyId)
    {
        $query = $this->db->query('SELECT lip_id, li_id, lip_picture as pics FROM cloud_li_pictures'.
            ' WHERE baby_id = ? AND locus_id = ? AND lip_status = 1',
            array($babyId, 0)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * [查看定位点上传的图片个数]
     * @param  [type] $liId [定位点id]
     * @return [type]       [description]
     */
    public function countPicByLiid($liId)
    {
        $query = $this->db->execute('SELECT lip_id FROM cloud_li_pictures WHERE li_id = ? AND lip_status = 1',
                array($liId)
            );
        return $this->db->affectedRows();
    }

    /**
     * [轨迹详情图片入库]
     * @param [type] $locusId   [description]
     * @param [type] $liid      [description]
     * @param [type] $uid       [description]
     * @param [type] $babyId    [description]
     * @param [type] $imageName [description]
     * @param [type] $addtime   [description]
     */
    public function addPic($locusId, $liid, $uid, $babyId, $imageName, $addtime)
    {
        if($this->db->execute('INSERT INTO `cloud_li_pictures` (`locus_id`, `li_id`, `u_id`, ' .
            '`baby_id`, `lip_picture`, `add_time`) VALUES (?, ?, ?, ?, ?, ?)',
            array(
                $locusId,
                $liid,
                $uid,
                $babyId,
                $imageName,
                $addtime
            )
        ))
            return $this->db->lastInsertId();
        else
            return false;
    }

    /**
     * [获取图片信息]
     * @param  [type] $picId [description]
     * @return [type]        [description]
     */
    public function getPicInfo($picId)
    {
        $query = $this->db->query('SELECT baby_id, locus_id, li_id, lip_status FROM cloud_li_pictures'.
            ' WHERE lip_id = ? Limit 1',
            array($picId)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * [删除轨迹详情的图片]
     * @param  [type] $picId   [图片id]
     * @param  [type] $deltime [删除时间]
     * @return [type]          [description]
     */
    public function delPic($picId, $deltime)
    {
        $this->db->execute('UPDATE `cloud_li_pictures` SET `lip_status` = 3, del_time = ?'.
        ' WHERE `lip_id` = ?',
            array(
                $deltime,
                $picId
            )
        );
        return $this->db->affectedRows();
    }
}