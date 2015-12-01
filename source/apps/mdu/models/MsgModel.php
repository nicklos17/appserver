<?php

namespace Appserver\Mdu\Models;

class MsgModel extends ModelBase
{
    /**
     * 获取日常消息或者系统消息
     * @param str $babyId 宝贝id
     * @param count
     * @return array
     */
    public function getMsgInfo($babyId, $type, $count)
    {
        if($type== '')
            $typeOpt = '';
        else
            $typeOpt = 'AND `msg_type` in (' . implode(',', $type) . ')';

        $query = $this->db->query('SELECT `msg_id`, `msg_type`, `msg_addtime` as time, `msg_content` ' .
        ' as content, `msg_mark` FROM `cloud_msgs` WHERE `baby_id` = ? ' . $typeOpt .
        ' ORDER BY `msg_addtime` DESC LIMIT ?',
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
     * 上拉刷新获取更多的消息
     * @param str $babyId 宝贝id
     * @param str $sinceId 每页的最后一条消息id
     * @param str $type 消息类型
     * @param str $count
     */
    public function getMsgBySinceId($babyId, $sinceId, $type, $count)
    {
        if($type== '')
            $typeOpt = '';
        else
            $typeOpt = 'AND `msg_type` in (' . implode(',', $type) . ')';
        $query = $this->db->query('SELECT `msg_id`, `msg_type`, `msg_addtime` as time, `msg_content` ' .
        ' as content, `msg_mark` FROM `cloud_msgs` WHERE `baby_id` = ? AND `msg_id` < ? ' . $typeOpt .
        ' ORDER BY `msg_addtime` DESC LIMIT ?',
            array(
                $babyId,
                $sinceId,
                intval($count)
                ),
            array(
                \PDO::PARAM_INT,
                \PDO::PARAM_INT,
                \PDO::PARAM_INT
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }
    
    /**
     * 下拉刷新获取最新的消息
     * @param str $babyId 宝贝id
     * @param str $maxId 每页的第一一条消息id
     * @param str $type 消息类型
     * @param str $count
     */
    public function getMsgByMaxId($babyId, $maxId, $type, $count)
    {
        if($type== '')
            $typeOpt = '';
        else
            $typeOpt = 'AND `msg_type` in (' . implode(',', $type) . ')';

        $query = $this->db->query('SELECT `msg_id`, `msg_type`, `msg_addtime` as time, `msg_content` ' .
        ' as content, `msg_mark` FROM `cloud_msgs` WHERE `baby_id` = ? AND `msg_id` > ? ' . $typeOpt .
        ' ORDER BY `msg_addtime` DESC LIMIT ?',
            array(
                $babyId,
                $maxId,
                intval($count)
            ),
            array(
                \PDO::PARAM_INT,
                \PDO::PARAM_INT,
                \PDO::PARAM_INT
            )
        );
        $query->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $query->fetchAll();
    }

    /**
     * 消息入库
     * @param unknown $where
     */
    public function insertMsg($babyId, $addTime, $content, $type)
    {
        return $this->db->execute('INSERT INTO `cloud_msgs` SET `baby_id` = ?, `msg_addtime` = ? , ' .
        '`msg_content` = ? , `msg_type` = ?',
            array(
                $babyId,
                $addTime,
                $content,
                $type
            )
        );
    }

}
