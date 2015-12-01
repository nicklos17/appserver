<?php

namespace Appserver\Mdu\Models;

class FeedbackModel extends ModelBase
{
    public function add($uid, $uname, $content, $version, $os, $addtime)
    {
        $this->db->execute('INSERT INTO `cloud_feedback` (`u_id`, `u_name`, `fd_content`, ' .
            '`fd_version`, `fd_os`, `fd_addtime`) VALUES (?, ?, ?, ?, ?, ?)',
            array(
                $uid,
                $uname,
                $content,
                $version,
                $os,
                $addtime
            )
        );
        return $this->db->affectedRows();
    }
}