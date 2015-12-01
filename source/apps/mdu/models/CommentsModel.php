<?php

namespace Appserver\Mdu\Models;

class CommentsModel extends ModelBase
{

    /**
     * 添加评论
     */
    public function addComment($uid, $uname, $locusId, $content, $addtime, $lcUid, $lcName)
    {
        if($this->db->execute('INSERT INTO `cloud_locus_comments` (`u_id`, `u_name`, `locus_id`, '.
        '`lc_content`, `lc_addtime`, `lc_uid`, `lc_uname_nick`) VALUES (?, ?, ?, ?, ?, ?, ?)',
            array(
                $uid,
                $uname,
                $locusId,
                $content,
                $addtime,
                $lcUid,
                $lcName
                )
            ))
            return $this->db->lastInsertId();
        else
            return false;
    }
    
    /**
     * 获取评论列表，按添加时间顺序排序
     * @param int $locusId
     */
    public function getCommentList($locusId, $count)
    {
        $query = $this->db->query('SELECT lc_id, u_id, u_name, lc_content, lc_uid, '.
        'lc_uname_nick as lc_name, lc_addtime as time FROM cloud_locus_comments 
        WHERE locus_id = ? ORDER BY lc_addtime ASC LIMIT ?',
            array($locusId, $count)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }
    
    /**
     * 获取评论列表，按添加时间倒序排序
     */
    public function getCommentBymaxId($locusId, $maxId, $count)
    {
        $query = $this->db->query('SELECT lc_id, u_id, u_name, lc_content, '.
        'lc_uid, lc_uname_nick as lc_name, lc_addtime as time FROM cloud_locus_comments '.
        'WHERE locus_id = ? and lc_id > ? LIMIT ?',
                array($locusId, $maxId, $count)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }
    
    /**
     * 删除评论
     * @param str $lcId 评论id
     */
    public function delComment($lcId)
    {
        $this->db->execute('DELETE FROM `cloud_locus_comments` WHERE `lc_id` = ?',
            array(
                $lcId
                )
        );
        return $this->db->affectedRows();
    }
    
    /**
     * 根据评论id获取轨迹id
     * @param str $lcId
     */
    public function getCommentByLcid($uid, $lcId)
    {
        $query = $this->db->query('SELECT `locus_id` FROM `cloud_locus_comments` '.
        'WHERE `lc_id` = ? AND `u_id` = ? LIMIT 1',
            array($lcId, $uid)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 判断该条评论是否属于该用户
     * @param str $uid 用户id
     * @param str $lcId 评论id
     */
    public function getLcidByUid($uid, $lcId)
    {
        $query = $this->db->query('SELECT `lc_id` FROM `cloud_locus_comments` WHERE `u_id` = ? ' .
        'AND `lc_id` = ?',
            array(
                $uid,
                $lcId
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 根据评论id获取轨迹id
     * @param str $lcId
     */
    public function getLocusByLcid($lcId)
    {
        $query = $this->db->query('SELECT `locus_id` FROM `cloud_locus_comments` WHERE `lc_id` ' .
        ' = ?',
            array(
                $lcId
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

}