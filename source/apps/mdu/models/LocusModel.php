<?php

namespace Appserver\Mdu\Models;

class LocusModel extends ModelBase
{

    /**
     * 点赞，更新轨迹表里相关赞的个数
     * @param str $locusId
     * @return int
     */
    public function hit($locusId)
    {
        $this->db->execute('UPDATE `cloud_locus` SET `locus_praises` = `locus_praises` +1 WHERE `locus_id` = ? limit 1',
            array($locusId)
        );
        return $this->db->affectedRows();
    }

    /**
     * 取消赞，更新轨迹表里相关赞的个数
     * @param str $locusId
     * @return int
     */
    public function cancelPraises($locusId)
    {
        $this->db->execute('UPDATE `cloud_locus` SET `locus_praises` = `locus_praises` - 1 ' .
        ' WHERE `locus_id` = ? LIMIT 1',
            array($locusId)
        );
        return $this->db->affectedRows();
    }

    /**
     * 评论，更新轨迹表里相关评论的个数
     * @param str $locusId
     * @return int
     */
    public function setCommentNum($locusId)
    {
        $this->db->execute('UPDATE `cloud_locus` SET `locus_comments` = `locus_comments` +1 WHERE locus_id = ? limit 1',
            array($locusId)
        );
        return $this->db->affectedRows();
    }
    
    /**
     *删除 评论，更新轨迹表里相关评论的个数
     * @param str $locusId
     * @return int
     */
    public function cancelComment($locusId)
    {
        $this->db->execute('UPDATE cloud_locus SET locus_comments = locus_comments-1 WHERE locus_id = ? AND locus_comments != 0 limit 1',
            array($locusId)
        );
        return $this->db->affectedRows();
    }

    /**
     * 默认情况下显示的轨迹列表
     * @param str $babyid
     * @param str $count
     * @return array
     */
    public function locusList($babyid, $count)
    {
        $query = $this->db->query('SELECT locus_id as locusid, locus_praises as praises, '.
        'locus_comments as comments, locus_date as date, locus_title as mark, locus_tags as tags FROM '.
        'cloud_locus WHERE baby_id = ? ORDER BY locus_date DESC LIMIT ?',
            array($babyid, intval($count)),
            array(\PDO::PARAM_INT, \PDO::PARAM_INT)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 下拉刷新 获取最新的轨迹信息
     * @param str $babyid  宝贝id
     * @param str sinceid  轨迹列表每页显示的起始id
     * @param str $count    轨迹列表每页显示的总条数
     * @return array
     */
    public function locusBySinceId($babyId, $sinceId, $count)
    {
        $res = $this->db->query('SELECT locus_id as locusid, locus_praises as praises, '.
        'locus_comments as comments, locus_date as date, locus_title as mark, locus_tags as tags FROM '.
        'cloud_locus WHERE baby_id = ? AND locus_id < ? ORDER BY locus_date DESC LIMIT ?',
            array($babyId, $sinceId, intval($count)),
            array(\PDO::PARAM_INT, \PDO::PARAM_INT, \PDO::PARAM_INT)
        );
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $res->fetchAll();
    }

    /**
     * 上拉刷新 获取最新的轨迹信息
     * @param str $babyid  宝贝id
     * @param str sinceid  轨迹列表每页显示的最后id
     * @param str $count    轨迹列表每页显示的总条数
     * @return array
     */
    public function locusByMaxId($babyId, $maxId, $count)
    {
        $res = $this->db->query('SELECT locus_id as locusid, locus_praises as praises, '.
        'locus_comments as comments, locus_date as date, locus_title as mark, locus_tags as tags '.
        'FROM cloud_locus WHERE baby_id = ? AND locus_id > ? ORDER BY locus_date DESC LIMIT ?',
            array($babyId, $maxId, $count),
            array(\PDO::PARAM_INT, \PDO::PARAM_INT, \PDO::PARAM_INT)
        );
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $res->fetchAll();
    }
    
    /**
     * 返回指定月份的日历轨迹列表
     * @param str $babyId
     * @param str $firstday  指定月份的第一天
     * @param str $lastday  指定月份的最后一天
     * @return array
     */
    public function callist($babyId, $firstday, $lastday)
    {
        $res = $this->db->query('SELECT locus_id, locus_title as mark, locus_date as date, '.
        'locus_praises as praises, locus_comments as comments, locus_title FROM cloud_locus '.
        'WHERE baby_id = ? AND locus_date >= ? AND locus_date <= ?',
            array($babyId, $firstday, $lastday),
            array(\PDO::PARAM_INT, \PDO::PARAM_INT, \PDO::PARAM_INT)
        );
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $res->fetchAll();
    }
    
    /**
     * 根据轨迹id返回对应的定位信息
     * @param str $locusId
     */
    public function getLocateInfo($locusId)
    {
        $res = $this->db->query('SELECT baby_id, locus_id, locus_date, locus_coordinates as tracks, locus_title as mark, '.
        'locus_date as date, locus_praises as praises, locus_comments as comments, locus_tags as tags '.
        'FROM cloud_locus WHERE locus_id = ? limit 1',
            array($locusId)
        );
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $res->fetch();
    }

    /**
     * 根据轨迹id返回前一天的定位信息
     * @param str $locusId
     */
    public function getFrontLocateInfo($babyId, $locusId)
    {
        $res = $this->db->query('SELECT baby_id, locus_id, locus_coordinates as tracks, locus_title as mark, '.
        ' locus_tags as tags FROM cloud_locus WHERE baby_id = ? AND locus_id < ? ORDER BY locus_id DESC limit 1',
            array($babyId, $locusId)
        );
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $res->fetch();
    }

    /**
     * 根据轨迹id返回前一天的定位信息
     * @param str $locusId
     */
    public function getNextLocateInfo($babyId, $locusId)
    {
        $res = $this->db->query('SELECT baby_id, locus_id, locus_coordinates as tracks, locus_title as mark, '.
        ' locus_tags as tags FROM cloud_locus WHERE baby_id = ? AND locus_id > ? ORDER BY locus_id ASC limit 1',
            array($babyId, $locusId)
        );
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $res->fetch();
    }

    /**
     * 返回宝贝昨天的定位信息
     * @param str $locusId
     */
    public function getLastLocateInfo($babyId)
    {
        $res = $this->db->query('SELECT baby_id, locus_id, locus_coordinates as tracks, locus_title as mark, '.
        ' locus_tags as tags FROM cloud_locus WHERE baby_id = ? ORDER BY locus_id DESC limit 1',
            array($babyId)
        );
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $res->fetch();
    }

    /**
     * 根据轨迹id返回赞的总数和评论的总数
     * @param str $locusId
     */
    public function getCommentAndPraiseCount($locusId)
    {
        $res = $this->db->query('SELECT `locus_praises` as praises, `locus_comments` as comments '.
        'FROM `cloud_locus` WHERE `locus_id` = ? LIMIT 1',
            array($locusId)
        );
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $res->fetch();
    }
    
    /**
     * 添加标注
     * @param str $locusId 轨迹id
     * @param str $title  标注名称
     * @param str $tags   标签
     * @param str $addtime  添加时间
     */
    public function addMark($locusId, $title, $tags, $addtime)
    {
        $this->db->execute('UPDATE `cloud_locus` SET `locus_title` = ?,`locus_tags` = ?, '.
        '`locus_title_time` = ? WHERE `locus_id` = ?',
            array(
                $title,
                $tags,
                $addtime,
                $locusId
            )
        );
        return $this->db->affectedRows();
    }
    
    /**
     * 根据轨迹id获取标注
     * @param str $locusId 轨迹id
     */
    public function getMark($locusId)
    {
        $query = $this->db->query('SELECT locus_title FROM cloud_locus WHERE locus_id = ? LIMIT 1',
            array($locusId)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $res->fetch();
    }

    /**
     * 获取轨迹对应的babyId
     * @param str $locusId
     * @return array
     */
    public function getBabyIdByLsId($locusId)
    {
        $query = $this->db->query('SELECT `baby_id` FROM `cloud_locus` WHERE `locus_id` = ? LIMIT 1',
            array(
                $locusId
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 获取轨迹对应的babyId
     * @param str $locusId
     * @return array
     */
    public function getBabyId($locusId)
    {
        $query = $this->db->query('SELECT baby_id FROM cloud_locus WHERE locus_id = ? limit 1', array($locusId));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * [根据宝贝id和起始轨迹id返回该宝贝的轨迹id]
     * @param  [type] $babyId  [description]
     * @param  [type] $locusId [description]
     * @return [type]          [description]
     */
    public function getLocusIds($babyId, $locusId)
    {
        $query = $this->db->query('SELECT locus_id, locus_date as times FROM cloud_locus WHERE baby_id = ? AND locus_id > ? ORDER BY locus_id ASC',
            array($babyId, $locusId)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }
}