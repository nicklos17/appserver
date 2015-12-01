<?php

namespace Appserver\Mdu\Models;

class Praisesmodel extends ModelBase
{

    /**
     * 点赞
     * @param str $uid 亲人id
     * @param str $uname  亲人备注名
     * @param str $addtime  赞的时间
     * @param str $locusId  轨迹id
     * @return int
     */
    public function hit($uid, $uname, $addtime, $locusId)
    {
        return $this->db->execute('INSERT INTO `cloud_locus_praises` (`u_id`, `u_name`, `lp_addtime`, '.
        '`locus_id`) VALUES (?, ?, ?, ?)',
            array($uid,
                    $uname,
                    $addtime,
                    $locusId
                )
            );
    }
    
    /**
     * 取消赞
     * @param str $lpid 赞id
     */
    public function delPraise($uid, $locusId)
    {
        $this->db->execute('DELETE FROM `cloud_locus_praises` WHERE `u_id` = ? AND `locus_id` = ?',
            array(
                $uid,
                $locusId
            )
        );
        return $this->db->affectedRows();
    }
    /**
     * 根据轨迹id获得赞列表
     * @param str $locusId
     * @return array
     */
    public function getPraiseList($locusId, $count)
    {
        $query = $this->db->query('SELECT `lp_id`, `u_id`, `u_name`, `lp_addtime` FROM '.
        '`cloud_locus_praises` WHERE `locus_id` = ? ORDER BY `lp_id` DESC LIMIT ?',
            array(
                $locusId,
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
     * 上拉获取更多的赞信息
     * @param str $locusId 轨迹id
     * @param str $sinceId 上页最后一条的赞id
     * @param str $count 每页显示的数据数量
     * @return array
     */
    public function getPraisesBySinceId($locusId, $sinceId, $count)
    {
        $query = $this->db->query('SELECT `lp_id`, `u_id`, `u_name`, `lp_addtime` FROM '.
        '`cloud_locus_praises` WHERE `locus_id` = ? AND `lp_id` < ? ORDER BY `lp_id` DESC LIMIT ?',
            array(
                $locusId,
                $sinceId,
                intval($count),
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
     * 下拉获取一段新的赞的数据
     * 注：这段数据是最新的赞到maxId这段数据
     * @param str $locusId 轨迹id
     * @param str $maxId  上一页的起始id
     */
    public function getPraisesByMaxId($locusId, $maxId)
    {
        $query = $this->db->query('SELECT `lp_id`, `u_id`, `u_name`, `lp_addtime` FROM '.
        '`cloud_locus_praises` WHERE `locus_id` = ? AND `lp_id` > ? ORDER BY `lp_id` DESC',
            array(
                $locusId,
                $maxId
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }
    /**
     * 根据轨迹id和用户id判断用户是否对该轨迹点过赞
     * @param int $uid  用户id
     *  @param int $locusid 轨迹id
     * @return array
     */

    public function getLsidByUid($uid, $locusId)
    {
        $query = $this->db->query('SELECT `locus_id`, `lp_addtime` FROM '.
        '`cloud_locus_praises` WHERE `u_id` = ? AND `locus_id` = ?',
            array(
                $uid,
                $locusId
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 根据轨迹id和用户id判断用户是否对该轨迹点过赞
     * @param int $uid  用户id
     *  @param int $locusid 轨迹id
     * @return array
     */
    
    public function praisesCheck($uid, $locusId)
    {
        $query = $this->db->query('SELECT locus_id, lp_addtime FROM cloud_locus_praises WHERE u_id = ? AND locus_id in('.$locusId.')', array($uid));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }
}