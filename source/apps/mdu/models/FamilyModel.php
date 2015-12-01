<?php

namespace Appserver\Mdu\Models;

class FamilyModel extends ModelBase
{

    /**
     * 添加亲人
     * @param str $babyId
     * @param str $rel 用户与宝贝的关系
     * @param str $uid
     * @param str $type
     * @param str $addtime
     */
    public function addRel($babyId, $uid, $roleName, $ishost, $addtime, $status)
    {
        $this->db->execute('INSERT INTO `cloud_family` (`baby_id`, `u_id`,`family_rolename`, `family_relation`, `family_addtime`, `family_status`) VALUES (?, ?, ?, ?, ?, ?)',
                array(
                    $babyId,
                    $uid,
                    $roleName,
                    $ishost,
                    $addtime,
                    $status
                )
            );
        return $this->db->affectedRows();
    }

    /**
     * 获取宝贝与用户的关系
     * @param unknown $uid
     * @param unknown $babyId
     */
    public function getRelationByUidBabyId($uid, $babyId)
    {
        $query = $this->db->query('SELECT `u_id`, `family_relation`, `family_rolename` FROM ' .
        '`cloud_family` WHERE `u_id` = ? AND `baby_id` = ? AND `family_status` = 1 limit 1',
            array(
                $uid,
                $babyId
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 将宝贝设置为不可用
     * @param string $babyId 宝贝id
     * @param stirng $deltime 删除时间
     */
    public function setBabyFamilyStatus($babyId, $deltime)
    {
        $this->db->execute('UPDATE `cloud_family` SET `family_status` = 3, `family_deltime` ' .
        ' = ? WHERE baby_id = ?',
            array(
                $deltime,
                $babyId
            )
        );
        return $this->db->affectedRows();
    }

    /**
     * 解除宝贝与某一用户的关系
     * @param unknown $babyId
     * @param unknown $uid
     * @param unknown $deltime
     */
    public function setRelByUidBabyId($babyId, $uid, $delTime)
    {
        $this->db->execute('UPDATE `cloud_family` set `family_status` = 3, `family_deltime` '.
        '= ? WHERE `baby_id` = ? AND `u_id` = ? AND `family_status` = 1',
            array(
                $delTime,
                $babyId,
                $uid
            )
        );
        return $this->db->affectedRows();
    }

    /**
     * 获取宝贝的监护号信息
     * @param str $babyId
     */
    public function getRelByBabyId($babyId)
    {
        $query = $this->db->query('SELECT `family_relation`, `u_id` FROM `cloud_family` WHERE `baby_id` = ? ' .
        'AND `family_relation` = 5 AND `family_status` = 1',
            array(
                $babyId
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 获取宝贝的主号号信息
     * @param str $babyId
     */
    public function getHostByBabyId($babyId)
    {
        $query = $this->db->query('SELECT `family_relation`, `u_id` FROM `cloud_family` WHERE `baby_id` = ? ' .
        'AND `family_relation` = 1 AND `family_status` = 1 limit 1',
            array(
                $babyId
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 修改用户与宝贝的关系
     * @param str $relation 关系： 3-副号 5-监护号 
     * @param unknown $babyId
     * @param unknown $uid
     */
    public function setGuardian($relation, $babyId, $uid)
    {
        $this->db->execute('UPDATE `cloud_family` SET `family_relation` = ? WHERE `baby_id` = ? ' .
        ' AND `u_id` = ? AND `family_status` = 1',
            array(
                $relation,
                $babyId,
                $uid
            )
        );
        return $this->db->affectedRows();
    }

    /**
     * 亲人列表
     * @param str $babyId
     */
    public function getFamList($babyId, $count)
    {
        $query = $this->db->query('SELECT `u_id`, `family_relation` as ishost, `family_rolename` ' .
        'as name, `family_praises` as praises, `family_comments` as comments, `family_intetime` ' .
        ' as actime FROM `cloud_family` WHERE `family_status` = 1 AND `baby_id` = ? ORDER BY ' .
        '`family_intetime` DESC LIMIT ?',
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
     * 查找用户是否有宝贝，无论宝贝是否被删除
     * @param unknown $uid
     */
    public function checkRelByUid($uid)
    {
        $query = $this->db->query('SELECT baby_id FROM cloud_family WHERE u_id = ? limit 1',
            array($uid)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 查找用户是否有正常的宝贝
     * @param unknown $uid
     */
    public function getBabysByUid($uid, $status)
    {
        $query = $this->db->query('SELECT baby_id FROM cloud_family WHERE u_id = ? AND family_status = ? limit 1',
            array(
                $uid,
                $status
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 点赞 ： 赞数+1
     * @param str $babyId
     * return int
     */
    public function hit($uid, $babyId, $addtime)
    {
        $this->db->execute('UPDATE `cloud_family` SET `family_intetime` = ?, `family_praises` = `family_praises` + 1 '.
        'WHERE `u_id` = ? AND `baby_id` = ? AND family_status = 1',
            array(
                $addtime,
                $uid,
                $babyId
            )
        );
        return $this->db->affectedRows();
    }


    /**
     * 取消赞 ： 赞数-1
     * @param str $babyId
     * return int
     */
    public function cancelPraises($uid, $babyId)
    {
        $this->db->execute('UPDATE `cloud_family` SET `family_praises` = `family_praises` - 1 ' .
        ' WHERE `baby_id` = ? AND `u_id` = ? AND family_status = 1 LIMIT 1',
            array(
                $babyId,
                $uid
            )
        );
        return $this->db->affectedRows();
    }

    /**
     * 评论 ： 评论数+1
     * @param str $babyId
     * @param str $uid
     * return int
     */
    public function incCommentNum($uid, $babyId, $addtime)
    {
        $this->db->execute('UPDATE cloud_family SET `family_intetime` = ? , '.
        'family_comments = family_comments+1 WHERE u_id = ? AND baby_id = ? AND family_status = 1 limit 1',
            array(
                $addtime,
                $uid,
                $babyId
            )
        );
        return $this->db->affectedRows();
    }

    /**
     *删除评论 ： 评论数-1 cancelComment
     * @param str $babyId
     * @param str $uid
     * return int
     */
    public function subCommentNum($uid, $babyId)
    {
        $this->db->execute('UPDATE `cloud_family` SET `family_comments` = `family_comments` - 1 ' .
        'WHERE `u_id` = ? AND `baby_id` = ? AND `family_comments` > 0 AND family_status = 1 LIMIT 1',
            array(
                $uid,
                $babyId
            )
        );
        return $this->db->affectedRows();
    }

    /**
     * 获取宝贝的主号和者监护号的uid
     * @param int babyId 宝贝id
     * @return array
     */
    public function getAuthUids($babyIds)
    {
        $query = $this->db->query('SELECT u_id, baby_id, family_rolename FROM '.
        'cloud_family WHERE baby_id in (' . $babyIds . ') AND family_relation in(1,5) AND family_status = 1');
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 获取用户监护下的宝贝
     * @param unknown $uid
     */
    public function getAuthBaby($uid)
    {
        $query = $this->db->query('SELECT baby_id FROM cloud_family WHERE family_relation in(1,5) AND family_status = 1 AND u_id = ?', array($uid));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 获取宝贝的主号和监护号，自身除外
     * @param str $uid 用户id
     * @param str $babyId 宝贝id
     * @return int 如果结果是0，表示该用户是宝贝的主号或者监护号，有操作权限；结果为1表示该用户是宝贝的副号，没有权限
     */
    public function getAuthor($uid, $babyId)
    {
        $query = $this->db->query('SELECT u_id, family_relation FROM cloud_family WHERE u_id != ? AND baby_id = ? AND family_relation in(1,5) AND family_status = 1', array($uid, $babyId));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 获取宝贝与用户的关系
     * @param unknown $uid
     * @param unknown $babyId
     */
    public function checkRelation($uid, $babyId)
    {
        $query = $this->db->query('SELECT u_id, family_relation, family_rolename FROM cloud_family WHERE u_id = ? AND baby_id = ? AND family_status = 1 limit 1', array($uid, $babyId));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * [批量添加亲人]
     * @param  [type] $query [description]
     * @return [type]        [description]
     */
    public function batchInsert($query)
    {
        $this->db->execute('INSERT INTO `cloud_family` (`baby_id`, `u_id`,`family_rolename`, `family_relation`, `family_addtime`, `family_status`) VALUES ' . $query
            );
        return $this->db->affectedRows();
    }

}