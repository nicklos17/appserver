<?php

namespace Appserver\Mdu\Models;

class BabyStepsModel extends ModelBase
{

    /**
     * 获取宝贝某天的步数id
     * @param unknown $babyID
     * @param unknown $date
     */
    public function getBsidByBaby($babyId, $date)
    {
        $query = $this->db->query('SELECT `baby_id` FROM `cloud_baby_steps` WHERE  baby_id = ? AND ' .
            '`bs_date` = ?',
            array(
                $babyId,
                $date
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 设置目标步数
     * @param unknown $babyId
     * @param unknown $steps
     * @param unknown $date
     */
    public function addBabySteps($babyId, $steps, $date)
    {
        return $this->db->execute('INSERT INTO `cloud_baby_steps` SET `baby_id` = ?, `bs_date` = ? , `bs_goal` ' .
            ' = ?',
            array(
                $babyId,
                $date,
                $steps,
            )
        );
    }

    /**
     * 更新目标步数
     */
    public function updateBabySteps($babyId, $steps, $date)
    {
        return $this->db->execute('UPDATE `cloud_baby_steps` SET `bs_goal` = ? WHERE baby_id = ? AND ' .
            '`bs_date` = ?',
            array(
                $steps,
                $babyId,
                $date
            )
        );
    }

    /**
     * 获取宝贝步数列表
     * @param string $babyId 宝贝id
     * @param string $date   起始时间戳
     * @param string $count  查询条数
     */
    public function getStepsList($babyId, $count)
    {
        $query = $this->db->query('SELECT `bs_id`, `bs_steps` as steps, `bs_date` as date, `bs_active` as active,'.
            '`bs_calory` as calory, `bs_goal` as goal, `bs_mileages` as mileages '.
            'FROM `cloud_baby_steps` WHERE baby_id = ? AND (bs_steps != 0 OR bs_goal != 0) ORDER BY bs_id DESC limit ?',
            array(
                $babyId,
                $count
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 根据时间获取宝贝步数列表
     * @param string $babyId 宝贝id
     * @param string $date   起始时间戳
     * @param string $count  查询条数
     */
    public function getStepsListByDate($babyId, $date, $count)
    {
        $query = $this->db->query('SELECT `bs_id`, `bs_steps` as steps, `bs_date` as date, `bs_active` as active,'.
         '`bs_calory` as calory, `bs_goal` as goal, `bs_mileages` as mileages '.
        'FROM `cloud_baby_steps` WHERE  baby_id = ? AND `bs_date` < ? AND (bs_steps != 0 OR bs_goal != 0) ORDER BY bs_id DESC limit ?',
            array(
                $babyId,
                $date,
                $count
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 获取每个用户步行目标任务的最大进度
     * @param unknown $uid
     * @param string addtime 任务添加时间
     */
    public function getGoalCount($uid, $addtime)
    {
        $query = $this->db->query('select count(bs_id) as count from cloud_baby_steps where baby_id in(select baby_id from cloud_family where u_id = ? AND family_status = 1 AND family_relation in(1,5)) AND bs_goal != 0 AND bs_steps > bs_goal ' .
            ' AND bs_date >= ? group by baby_id order by count desc limit 1', array($uid, $addtime));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * [获取最近的目标步数]
     * @param  [type] $babyId [宝贝id]
     * @return [type]         [description]
     */
    public function getNearlyGoal($babyId)
    {
        $query = $this->db->query('select bs_goal as goal from cloud_baby_steps where baby_id = ? AND bs_goal != 0 limit 1',
            array($babyId));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }
}