<?php

namespace Appserver\Mdu\Models;

class BabyRanksModel extends ModelBase
{
    /**
     * 绑定鞋子成功，查询宝贝id是否已经填入宝贝里程排行
     */
    public function getBabyId($babyId)
    {
        $query = $this->db->query('SELECT `baby_id` from `cloud_baby_ranks` WHERE `baby_id` ' .
        ' = ? LIMIT 1',
            array(
                $babyId
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 绑定鞋子成功，将宝贝id填入宝贝里程排行
     */
    public function addBabyId($babyId)
    {
        return $this->db->execute('INSERT INTO `cloud_baby_ranks` (`baby_id`) VALUES (?)',
            array(
                $babyId
            )
        );
    }

    /**
     * 获取宝贝行程汇总数据
     */
    public function getSummary($babyId)
    {
        $query = $this->db->query('SELECT br_mileages as miles, br_guards as days, br_steps as total_steps, br_avg_steps as avg_steps, ' .
                ' br_max_steps as max_steps, br_avg_active as avg_active from cloud_baby_ranks where baby_id = ? limit 1',
            array($babyId)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 获取宝贝总数:
     * 一个宝贝一条数据，所以br_id的最大值就是宝贝的总数
     */
    public function countBaby()
    {
        $query = $this->db->query('SELECT br_id from cloud_baby_ranks ORDER BY br_id DESC LIMIT 1');
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 获取宝贝的里程信息
     */
    public function getRankInfo($babyId)
    {
        $query = $this->db->query('SELECT br_mileages, br_guards from cloud_baby_ranks where baby_id = ? LIMIT 1', array($babyId));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 获取大于该里程数的宝贝个数:即宝贝的里程数排在第几位
     */
    public function getRank($mileages)
    {
        $query = $this->db->query('SELECT count(baby_id) as rank from cloud_baby_ranks where br_mileages > ?', array($mileages));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }
    

}
