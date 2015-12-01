<?php

namespace Appserver\Mdu\Models;

class DevtagsModel extends ModelBase
{
    /**
     * 查询deviceToken是否存在表中
     * @param str $deviceToken
     * @return array
     */
    public function checkToken($utags)
    {
        $query = $this->db->query('SELECT dt_tags, u_id FROM cloud_dev_tags WHERE dt_tags = ? limit 1',
            array($utags)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 查询deviceToken是否存在表中
     * @param str $deviceToken
     * @return array
     */
    public function checkTokenById($uid)
    {
        $query = $this->db->query('SELECT dt_tags, u_id FROM cloud_dev_tags WHERE u_id = ? limit 1',
            array($uid)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 增加新的设备标签都表中
     * @param str $uid 用户id
     * @param str $deviceToken 用户所使用的设备标签
     * @param str $type 设备类型： 1-ios  3-android
     * @param str $addtime 设备的添加时间
     * @return int
     */
    public function addToken($uid, $deviceToken, $type, $addtime, $disturb, $start, $end, $cver)
    {
        return  $this->db->execute('INSERT INTO `cloud_dev_tags` (`u_id`, `dt_tags`,'.
        '`dt_type`, `dt_lasttime`, `dt_disturb`, `dt_start`, `dt_end`, `dt_cver`) values(?, ?, ?, ?, ?, ?, ?, ?)',
            array($uid, $deviceToken, $type, $addtime, $disturb, $start, $end, $cver)
        );
    }

    /**
     * 更新同一个设备的登录时间和登录id
     * @param str $uid 新登录的用户id
     * @param str $deviceToken
     * @param str $plat
     * @param str $addtime
     * $param str $disturb
     */
    public function updateToken($uid, $deviceToken, $plat, $addtime, $disturb, $cver)
    {
        $this->db->execute('UPDATE `cloud_dev_tags` set `u_id` = ?, `dt_lasttime` = ?,'.
        '`dt_disturb` = ?, `dt_cver` = ?, `dt_type` = ? WHERE `dt_tags` = ?',
            array($uid, $addtime, $disturb, $cver, $plat, $deviceToken)
        );
        return $this->db->affectedRows();
    }

    /**
     * 更新同一个设备的登录时间和登录id
     * @param str $uid 新登录的用户id
     * @param str $deviceToken
     * @param str $plat
     * @param str $addtime
     * $param str $disturb
     */
    public function updateTokenById($uid, $deviceToken, $plat, $addtime, $disturb, $cver)
    {
        $this->db->execute('UPDATE `cloud_dev_tags` set `dt_lasttime` = ?,'.
        '`dt_disturb` = ?, `dt_cver` = ?, `dt_type` = ?,`dt_tags` = ? WHERE u_id = ?',
            array($addtime, $disturb, $cver, $plat, $deviceToken, $uid)
        );
        return $this->db->affectedRows();
    }

    /**
     * 删除该用户所有的devicetoken
     * @param str $uid 用户id
     */
    public function del($uid)
    {
        $this->db->execute('DELETE FROM `cloud_dev_tags` WHERE `u_id` = ?',
            array(
                $uid
            )
        );
        return $this->db->affectedRows();
    }

    /**
     * 根据用户id获取用户所有的devicetoken的相关信息
     * @param str $userIds
     */
    public function getPushInfo($userIds, $disturb, $start, $end)
    {
        $query = $this->db->query('SELECT u_id, dt_tags as deviceToken, dt_type as type FROM cloud_dev_tags '.
        'WHERE u_id in('.$userIds.') AND (dt_disturb = ? OR ( dt_disturb = 1 AND dt_start > ? AND dt_end <= ?))',
            array($disturb, $start, $end));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 根据用户id获取用户适合ios推送的用户信息
     * 注：dt_type = 1是ios的推送类型
     * @param str $userIds
     */
    public function getPushInfoForIOS($userIds)
    {
        $query = $this->db->query('SELECT u_id, dt_tags as deviceToken FROM cloud_dev_tags WHERE dt_type = 1 AND u_id in('. $userIds .')');
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 获取所有ios的token
     */
    public function getIOSToken()
    {
        $query = $this->db->query('SELECT u_id, dt_tags as deviceToken FROM cloud_dev_tags WHERE dt_type = 1');
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 获取所有android的token
     */
    public function getANDToken()
    {
        $query = $this->db->query('SELECT u_id, dt_tags as deviceToken FROM cloud_dev_tags WHERE dt_type = 3');
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 判断用户的免打扰模式状态
     * @param unknown $uid
     */
    public function checkDisturb($uid)
    {
        $query = $this->db->query('SELECT `dt_disturb`, `dt_start`, `dt_end`'.
        ' FROM `cloud_dev_tags` WHERE `u_id` = ? LIMIT 1',
            array($uid)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 开启/关闭免打扰模式
     * @param str $uid
     * @param str $disturb
     * @param str $start
     * @param str $end
     */
    public function setDisturb($uid, $disturb, $start, $end)
    {
        if($start == '' || $end == '')
        {
            $string = '`dt_disturb` = ' . $disturb;
        }
        else
        {
            $string = '`dt_disturb` =' . $disturb . ', `dt_start` = ' . $start .', `dt_end` = '. $end;
        }
        $this->db->execute('UPDATE `cloud_dev_tags` set ' . $string .' WHERE `u_id` = ' . $uid);
        return $this->db->affectedRows();
    }

    /**
     * 根据用户id获取多用户所有的devicetoken的相关信息
     * @param str $deviceTokens
     */
    public function getUserByUid($uids, $disturb, $start, $end)
    {
        $query = $this->db->query('SELECT u_id, dt_tags as deviceToken, dt_type as type, dt_cver as cver FROM cloud_dev_tags
                                        WHERE u_id in('.$uids.') AND (dt_disturb = ? OR ( dt_disturb = 1 AND dt_start > ? AND dt_end <= ?))', array($disturb, $start, $end));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 根据用户id获取单用户的devicetoken的相关信息
     * @param str $deviceTokens
     */
    public function getUserInfoByUid($uid, $disturb, $start, $end)
    {
        $query = $this->db->query('SELECT u_id, dt_tags as deviceToken, dt_type as type, dt_cver as cver FROM cloud_dev_tags' .
                ' WHERE u_id = ? AND (dt_disturb = ? OR ( dt_disturb = 1 AND dt_start > ? AND dt_end <= ?))',
            array($uid, $disturb, $start, $end)
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetch();
    }

    /**
     * 获取有上传客户段版本号的devicetoken信息
     * @param unknown $uid
     * @param unknown $disturb
     * @param unknown $start
     * @param unknown $end
     */
    public function getUserHaveCver($uids, $disturb, $start, $end)
    {
        $query = $this->db->query('SELECT u_id, dt_tags as deviceToken, dt_type as type, dt_cver as cver FROM cloud_dev_tags
                                        WHERE u_id in('.$uids.') AND (dt_disturb = ? OR ( dt_disturb = 1 AND dt_start > ? AND dt_end <= ?))', array($disturb, $start, $end));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }
    
    /**
     * 获取无上传客户段版本号的devicetoken信息
     * @param unknown $uid
     * @param unknown $disturb
     * @param unknown $start
     * @param unknown $end
     */
    public function getUserNoCver($uids, $disturb, $start, $end)
    {
        $query = $this->db->query('SELECT u_id, dt_tags as deviceToken, dt_type as type FROM cloud_dev_tags
                                        WHERE u_id in('.$uids.') AND dt_cver = '.'""'.' AND (dt_disturb = ? OR ( dt_disturb = 1 AND dt_start > ? AND dt_end <= ?))', array($disturb, $start, $end));
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

}
